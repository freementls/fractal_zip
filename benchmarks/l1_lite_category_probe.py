#!/usr/bin/env python3
"""Profile L1-lite conformity probe: category extraction + matched vocab.

Lossy-looking intermediate (categories lifted to an ordered sidecar) with
grammar-implied rebuild (sequential 0x0E placeholders — no position mask).

Arms (all roundtrip-verified):
  raw              — unchanged slice
  body             — categories replaced by single 0x0E each
  sidecar          — extracted [[Category:...]] blobs, length-prefixed
  pack             — sidecar || body  (naive concat; upper bound on split tax)

Scores zstd-12 --long=30 on each arm. Also builds word-count dicts from raw vs
body and reports overlap (the conformity gap).

Usage:
  python3 benchmarks/l1_lite_category_probe.py [slice] [--cmix-dict]
"""
from __future__ import annotations

import argparse
import collections
import os
import re
import struct
import subprocess
import sys
from pathlib import Path

ROOT = Path("/srv/http/fractal_zip")
OUT = ROOT / "benchmarks/.ladder_cache/l1_lite"
LOG = ROOT / "benchmarks/.hutter_logs/l1_lite_category_probe.log"
STOCK_DIC = ROOT / "tools/hutter/fx2-cmix/dictionary/english.dic"
CAT_RE = re.compile(rb"\[\[Category:[^\]]*\]\]")
MARK = bytes([0x0E])  # sequential placeholder; rebuild pops sidecar in order
WORD_RE = re.compile(rb"[a-zA-Z]+")


def log(msg: str) -> None:
    print(msg, flush=True)
    LOG.parent.mkdir(parents=True, exist_ok=True)
    with open(LOG, "a") as f:
        f.write(msg + "\n")


def extract(data: bytes) -> tuple[bytes, list[bytes]]:
    cats: list[bytes] = []
    out = bytearray()
    pos = 0
    for m in CAT_RE.finditer(data):
        out += data[pos : m.start()]
        # Escape raw MARK if any (none on ladder slices; keep correct anyway)
        chunk = data[m.start() : m.end()]
        cats.append(chunk)
        out += MARK
        pos = m.end()
    out += data[pos:]
    # Escape pre-existing MARK bytes in body (after extraction path unused)
    return bytes(out), cats


def rebuild(body: bytes, cats: list[bytes]) -> bytes:
    out = bytearray()
    i = 0
    for b in body:
        if b == 0x0E:
            if i >= len(cats):
                raise ValueError("placeholder underflow")
            out += cats[i]
            i += 1
        else:
            out.append(b)
    if i != len(cats):
        raise ValueError(f"leftover cats {len(cats) - i}")
    return bytes(out)


def pack_sidecar(cats: list[bytes]) -> bytes:
    """Length-prefixed records + count header."""
    buf = bytearray()
    buf += struct.pack(">I", len(cats))
    for c in cats:
        buf += struct.pack(">H", len(c))
        buf += c
    return bytes(buf)


def unpack_sidecar(blob: bytes) -> list[bytes]:
    n = struct.unpack_from(">I", blob, 0)[0]
    pos = 4
    cats = []
    for _ in range(n):
        ln = struct.unpack_from(">H", blob, pos)[0]
        pos += 2
        cats.append(blob[pos : pos + ln])
        pos += ln
    return cats


def zsize(path: Path) -> int:
    out = path.with_suffix(path.suffix + ".zst")
    subprocess.run(
        ["zstd", "-12", "--long=30", "-T8", "-f", "-q", str(path), "-o", str(out)],
        check=True,
    )
    n = out.stat().st_size
    out.unlink()
    return n


def word_counts(data: bytes) -> collections.Counter:
    return collections.Counter(w.lower() for w in WORD_RE.findall(data))


def load_stock() -> list[bytes]:
    entries, seen = [], set()
    for tok in re.split(rb"[^a-z]+", STOCK_DIC.read_bytes()):
        if tok and tok not in seen:
            seen.add(tok)
            entries.append(tok)
    return entries


def write_matched_dic(counts: collections.Counter, path: Path, cap: int = 44880) -> None:
    """Stock prefix + append high-count body words not already in stock (Profile-L style)."""
    stock = load_stock()
    seen = set(stock)
    # Drop words that only appear inside extracted categories (not in body counts)
    extras = []
    for w, c in counts.most_common():
        if w in seen or c < 2 or len(w) < 2:
            continue
        extras.append(w)
        seen.add(w)
        if len(stock) + len(extras) >= cap:
            break
    lines = stock + extras
    path.write_bytes(b"\n".join(lines) + b"\n")
    return len(stock), len(extras)


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument(
        "slice",
        nargs="?",
        default=str(ROOT / "benchmarks/.ladder_cache/enwik8_1m_skip10.mid"),
    )
    ap.add_argument("--tag", default="")
    args = ap.parse_args()
    OUT.mkdir(parents=True, exist_ok=True)
    if not LOG.exists():
        LOG.write_text("")

    slice_path = Path(args.slice)
    data = slice_path.read_bytes()
    tag = args.tag or slice_path.stem
    log(f"=== L1-lite category probe tag={tag} plain={len(data)} ===")

    if data.count(MARK):
        log(f"WARN: raw stream contains {data.count(MARK)} MARK bytes; escaping needed")

    body, cats = extract(data)
    rebuilt = rebuild(body, cats)
    assert rebuilt == data, "roundtrip FAIL"
    sc = pack_sidecar(cats)
    assert unpack_sidecar(sc) == cats
    pack = sc + body
    log(
        f"cats={len(cats)} cat_bytes={sum(len(c) for c in cats)} "
        f"body={len(body)} sidecar={len(sc)} pack={len(pack)} "
        f"plain_delta={len(pack) - len(data):+d}"
    )

    paths = {
        "raw": OUT / f"{tag}_raw.bin",
        "body": OUT / f"{tag}_body.bin",
        "sidecar": OUT / f"{tag}_sidecar.bin",
        "pack": OUT / f"{tag}_pack.bin",
    }
    paths["raw"].write_bytes(data)
    paths["body"].write_bytes(body)
    paths["sidecar"].write_bytes(sc)
    paths["pack"].write_bytes(pack)

    scores = {k: zsize(p) for k, p in paths.items()}
    split_sum = scores["body"] + scores["sidecar"]
    log("zstd12L30:")
    for k, v in scores.items():
        log(f"  {k:8s} {v:10,d}  Δraw={v - scores['raw']:+,d}")
    log(f"  body+sc  {split_sum:10,d}  Δraw={split_sum - scores['raw']:+,d}")

    raw_c = word_counts(data)
    body_c = word_counts(body)
    # Words that shrink a lot after extraction (category-heavy)
    shrink = []
    for w, c in raw_c.most_common(5000):
        b = body_c.get(w, 0)
        if c >= 5 and b < c:
            shrink.append((c - b, c, b, w))
    shrink.sort(reverse=True)
    log("top words depleted by category lift:")
    for d, c, b, w in shrink[:15]:
        log(f"  -{d:4d}  raw={c:5d} body={b:5d}  {w.decode()}")

    # Conformity gap: stock-append from raw vs body
    stock = set(load_stock())
    raw_extra = [w for w, c in raw_c.most_common() if w not in stock and c >= 2][:365]
    body_extra = [w for w, c in body_c.most_common() if w not in stock and c >= 2][:365]
    only_raw = set(raw_extra) - set(body_extra)
    only_body = set(body_extra) - set(raw_extra)
    log(
        f"append365 candidates: raw={len(raw_extra)} body={len(body_extra)} "
        f"only_raw={len(only_raw)} only_body={len(only_body)} "
        f"overlap={len(set(raw_extra)&set(body_extra))}"
    )
    if only_raw:
        log("  sample only_raw: " + ", ".join(w.decode() for w in list(only_raw)[:12]))

    dic_path = OUT / f"english_l1body_{tag}.dic"
    n_stock, n_extra = write_matched_dic(body_c, dic_path)
    log(f"wrote matched body dict {dic_path} stock={n_stock} extras={n_extra}")

    # Verdict heuristic on zstd proxy
    best_split = min(split_sum, scores["pack"])
    if best_split < scores["raw"]:
        log(f"VERDICT: zstd KEEP Δ={best_split - scores['raw']:+d} (split beats raw)")
    else:
        log(
            f"VERDICT: zstd reject Δ={best_split - scores['raw']:+d} "
            f"— category lift does not beat match model on zstd; "
            f"cmix ablation still needed for conformity thesis"
        )
    log("done")


if __name__ == "__main__":
    main()
