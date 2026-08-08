#!/usr/bin/env python3
"""Profile L4 sketch probe — cheap page features as side-info.

Unlike L1 (lift spans out), L4 *keeps* the full text and asks whether a tiny
per-page sketch reduces conditional entropy of the body enough to pay for
itself — the joint-conditioning thesis.

Arms:
  raw              — unchanged slice
  split            — zstd(sketch_table) + zstd(raw)   [naked split; expect lose]
  inline           — each <page> prefixed with sketch record (match-visible)
  KT|prev          — order-1 KT adaptive bpc on body bytes
  KT|prev,sketch   — order-1 KT with separate tables per sketch bucket

Roundtrip: inline ↔ raw via strip of sketch records (marked).

Usage:
  python3 benchmarks/l4_sketch_probe.py [slice]
"""
from __future__ import annotations

import argparse
import math
import re
import struct
import subprocess
import zlib
from collections import defaultdict
from pathlib import Path

ROOT = Path("/srv/http/fractal_zip")
OUT = ROOT / "benchmarks/.ladder_cache/l4_sketch"
LOG = ROOT / "benchmarks/.hutter_logs/l4_sketch_probe.log"

PAGE_RE = None  # pages found via split_pages
TITLE_RE = re.compile(rb"<title>(.*?)</title>", re.S)
CAT_RE = re.compile(rb"\[\[Category:([^\|\]]+)")
INFOBOX_RE = re.compile(rb"\{\{[Ii]nfobox\b")
HEADING_RE = re.compile(rb"\n==+\s*([^=\n]{1,40}?)\s*==+")

# Sketch record in inline stream: MAGIC + 8-byte payload
MAGIC = b"\x0eSK\x0f"  # 4 bytes; 0x0E/0x0F unused on ladder slices


def log(msg: str) -> None:
    print(msg, flush=True)
    LOG.parent.mkdir(parents=True, exist_ok=True)
    with open(LOG, "a") as f:
        f.write(msg + "\n")


def split_pages(data: bytes) -> tuple[bytes, list[bytes]]:
    """Return (head_before_first_page, list_of_page_blobs including '  <page>\\n')."""
    starts = [m.start() for m in re.finditer(rb"\n  <page>\n", data)]
    if data.startswith(b"  <page>\n"):
        starts = [0] + [s + 1 for s in starts]
    else:
        starts = [s + 1 for s in starts]
    if not starts:
        return data, []
    head = data[: starts[0]]
    pages = []
    for i, s in enumerate(starts):
        e = starts[i + 1] if i + 1 < len(starts) else len(data)
        pages.append(data[s:e])
    return head, pages


def sketch8(page: bytes) -> bytes:
    """8-byte sketch: features a mixer could take as context."""
    title = TITLE_RE.search(page)
    t = title.group(1) if title else b""
    cats = CAT_RE.findall(page)
    cat_blob = b"|".join(c.strip()[:40] for c in cats[:8])
    has_ibox = 1 if INFOBOX_RE.search(page) else 0
    n_cat = min(255, len(cats))
    lb = min(31, max(0, (len(page).bit_length() - 1)))
    h = HEADING_RE.search(page)
    head0 = h.group(1).strip()[:24] if h else b""
    crc = zlib.crc32(t + b"\0" + cat_blob + b"\0" + head0) & 0xFFFFFFFF
    return struct.pack(
        ">IBBBB",
        crc,
        (has_ibox << 7) | (lb & 0x1F),
        n_cat,
        min(255, len(t)),
        min(255, len(cats)),
    )  # 4+4 = 8


def bucket_id(sk: bytes) -> int:
    """Coarse bucket for KT tables: flags + n_cat + length bucket (not full crc)."""
    flags, n_cat = sk[4], sk[5]
    return ((flags & 0x9F) << 8) | n_cat  # ~9 bits


def zsize(data: bytes, name: str) -> int:
    OUT.mkdir(parents=True, exist_ok=True)
    p = OUT / f"_{name}.bin"
    p.write_bytes(data)
    o = p.with_suffix(".zst")
    subprocess.run(
        ["zstd", "-12", "--long=30", "-T8", "-f", "-q", str(p), "-o", str(o)],
        check=True,
    )
    n = o.stat().st_size
    o.unlink()
    p.unlink()
    return n


def kt_bpc(data: bytes, bucket_fn=None) -> float:
    """Order-1 KT adaptive coding bits/byte. Optional bucket_fn(i)->bucket for byte i."""
    # tables[bucket][prev][sym] counts; use shared if no bucket
    counts: dict = defaultdict(lambda: defaultdict(lambda: [0] * 256))
    bits = 0.0
    prev = 0
    for i, b in enumerate(data):
        buck = 0 if bucket_fn is None else bucket_fn(i)
        row = counts[buck][prev]
        total = sum(row) + 256  # KT: +1 prior per sym
        bits += -math.log2((row[b] + 1) / total)
        row[b] += 1
        prev = b
    return bits / len(data) if data else 0.0


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument(
        "slice",
        nargs="?",
        default=str(ROOT / "benchmarks/.ladder_cache/enwik8_1m_skip10.mid"),
    )
    args = ap.parse_args()
    if not LOG.exists():
        LOG.write_text("")
    data = Path(args.slice).read_bytes()
    tag = Path(args.slice).stem
    log(f"=== L4 sketch probe tag={tag} plain={len(data)} ===")

    head, pages = split_pages(data)
    if not pages:
        log("no page markers; treating whole slice as one page")
        pages = [data]
        head = b""

    sketches = [sketch8(p) for p in pages]
    assert all(len(s) == 8 for s in sketches)
    log(f"pages={len(pages)} sketch_table={len(sketches)*8} B")

    # Sidecar: count + 8*n
    sidecar = struct.pack(">I", len(sketches)) + b"".join(sketches)

    # Inline: MAGIC + sketch before each page
    inline_parts = [head]
    for sk, p in zip(sketches, pages):
        inline_parts.append(MAGIC + sk + p)
    inline = b"".join(inline_parts)

    # Strip roundtrip
    if MAGIC in head:
        log("WARN: MAGIC in head")
    rebuilt = bytearray(head)
    pos = len(head)
    tmp = inline[len(head) :]
    # parse inline
    i = 0
    got_pages = []
    while i < len(tmp):
        if tmp[i : i + 4] != MAGIC:
            raise SystemExit(f"inline sync fail at {i}")
        i += 4 + 8
        # page until next MAGIC or end
        nxt = tmp.find(MAGIC, i)
        if nxt < 0:
            got_pages.append(tmp[i:])
            break
        got_pages.append(tmp[i:nxt])
        i = nxt
    assert got_pages == pages, "inline roundtrip FAIL"
    log("inline RT_OK")

    raw_z = zsize(data, "raw")
    sc_z = zsize(sidecar, "sc")
    inline_z = zsize(inline, "inline")
    split = sc_z + raw_z
    log("zstd12L30:")
    log(f"  raw      {raw_z:10,d}")
    log(f"  sidecar  {sc_z:10,d}")
    log(f"  split    {split:10,d}  Δraw={split - raw_z:+,d}")
    log(f"  inline   {inline_z:10,d}  Δraw={inline_z - raw_z:+,d}")

    # KT conditional entropy on a sample (cap 2MB for speed)
    sample = data[: min(len(data), 2 * 1024 * 1024)]
    # Build per-byte bucket map for sample: need page alignment
    # Map each byte offset in full data to sketch bucket; then restrict to sample.
    buck_at = bytearray(len(data))  # store low 8 bits of bucket only for speed — use full int list
    buck_list = [0] * len(data)
    # locate pages in data
    if head is not None and pages and pages[0] is data:
        b = bucket_id(sketches[0])
        for i in range(len(data)):
            buck_list[i] = b
    else:
        off = len(head)
        for sk, p in zip(sketches, pages):
            b = bucket_id(sk)
            for i in range(off, off + len(p)):
                if i < len(buck_list):
                    buck_list[i] = b
            off += len(p)

    sample_buck = buck_list[: len(sample)]

    def bfn(i, _sb=sample_buck):
        return _sb[i]

    log("KT order-1 adaptive (sample %d B):" % len(sample))
    bpc0 = kt_bpc(sample, None)
    bpc1 = kt_bpc(sample, bfn)
    log(f"  KT|prev           {bpc0:.4f} bpc")
    log(f"  KT|prev,sketch    {bpc1:.4f} bpc   Δ={bpc1 - bpc0:+.4f}")
    # Cost of sketch table amortized over sample pages fraction
    n_pages_sample = max(1, sum(1 for p in pages if True))  # all sketches always stored
    sketch_bits = len(sidecar) * 8
    amort = sketch_bits / len(data)
    # Effective if KT gain holds on full stream:
    eff = (bpc1 - bpc0) + amort / 1.0  # bpc delta + sketch cost in bpc
    log(
        f"  sketch cost amortized {amort:.4f} bpc over full slice; "
        f"net if KT Δ holds: {bpc1 - bpc0 + amort:+.4f} bpc"
    )
    if bpc1 + amort < bpc0:
        log("VERDICT: KT suggests joint sketch side-info MAY pay (probe-scale)")
    else:
        log(
            "VERDICT: sketch does not pay under order-1 KT — need stronger "
            "conditioner (cmix mixer context) or richer sketch; naked split lost on zstd"
        )
    # Save sketches for later cmix wiring
    OUT.mkdir(parents=True, exist_ok=True)
    (OUT / f"{tag}_sidecar.bin").write_bytes(sidecar)
    (OUT / f"{tag}_inline.bin").write_bytes(inline)
    log("done")


if __name__ == "__main__":
    main()
