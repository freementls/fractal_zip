#!/usr/bin/env python3
"""Collected essence vs sidecar tax (Profile L / abjad-fx).

Thesis (high resistance):
  Per-occurrence sidecars and position masks pay tax that match+mixer already
  avoid. Wins come from COLLECTED shared vocabulary (global codebook / dict
  zones) and FREE grammar-implied state (fx-style: BrFcIdx, wiki_state) —
  never from shipping per-symbol class bits.

Arms on a wiki slice (zstd + order-1 KT):
  A  raw
  B  L1 ordered sidecar pack          — per-occurrence tax (known fail mode)
  C  unique global table + rank refs  — collected table once; body = refs
  D  top-K fold codes in one stream   — codebook in compressor (dict-like);
                                        body rewritten; tax = codebook bytes
                                        counted once (S1-style), not per hit
  E  abjad-fx FREE features only      — KT | prev + derived state (no bits shipped)
  F  split+mask class stream          — cautionary upper bound on mask tax

Also reports Zipf coverage: what fraction of category/template mass the top-K
collects (essence concentration).

Usage:
  python3 benchmarks/l_collected_essence_probe.py [slice]
"""
from __future__ import annotations

import argparse
import collections
import math
import re
import struct
import subprocess
from pathlib import Path

ROOT = Path("/srv/http/fractal_zip")
OUT = ROOT / "benchmarks/.ladder_cache/l_collected"
LOG = ROOT / "benchmarks/.hutter_logs/l_collected_essence_probe.log"
FINDINGS = ROOT / "benchmarks/.hutter_collected_essence_findings.md"

CAT_RE = re.compile(rb"\[\[Category:([^\]]*)\]\]")
TMPL_RE = re.compile(rb"\{\{([^\|\}]+)")
# first token of template, stripped
WORD_RE = re.compile(rb"[A-Za-z]+")


def log(msg: str) -> None:
    print(msg, flush=True)
    LOG.parent.mkdir(parents=True, exist_ok=True)
    with LOG.open("a") as f:
        f.write(msg + "\n")


def zstd_size(data: bytes) -> int:
    OUT.mkdir(parents=True, exist_ok=True)
    p = OUT / "_z.bin"
    o = OUT / "_z.zst"
    p.write_bytes(data)
    subprocess.run(
        ["zstd", "-12", "--long=30", "-T8", "-f", "-q", str(p), "-o", str(o)],
        check=True,
    )
    n = o.stat().st_size
    p.unlink(missing_ok=True)
    o.unlink(missing_ok=True)
    return n


def kt_bpc(data: bytes, ctx_fn) -> float:
    counts: dict = collections.defaultdict(lambda: [0] * 256)
    bits = 0.0
    prev = 0
    for i, b in enumerate(data):
        ctx = ctx_fn(i, prev)
        row = counts[ctx]
        total = sum(row) + 256
        bits += -math.log2((row[b] + 1) / total)
        row[b] += 1
        prev = b
    return bits / len(data)


# --- free abjad-fx state (prefix-only; mirrors fx BrFcIdx / L2 spirit) ---
PROSE, TAG, LINK, TMPL, ENTITY = 0, 1, 2, 3, 4


def wiki_states(data: bytes) -> list[int]:
    """Predictive state before each byte (no lookahead)."""
    st = PROSE
    prev = 0
    link_d = tmpl_d = 0
    run = 0
    ent_left = 0
    out = []
    for i, c in enumerate(data):
        out.append(st)
        if st not in (PROSE, ENTITY) and i - run > 4096:
            st = PROSE
            link_d = tmpl_d = 0
        if ent_left > 0:
            ent_left -= 1
            if c == ord(";") or ent_left == 0:
                ent_left = 0
                st = PROSE
            prev = c
            continue
        if st == PROSE:
            if c == ord("&"):
                st = ENTITY
                ent_left = 15
            elif c == ord("<"):
                st = TAG
                run = i
            elif prev == ord("[") and c == ord("["):
                st = LINK
                link_d = 1
                run = i - 1
            elif prev == ord("{") and c == ord("{"):
                st = TMPL
                tmpl_d = 1
                run = i - 1
        elif st == TAG:
            if c == ord(">") or i - run > 512:
                st = PROSE
        elif st == LINK:
            if prev == ord("[") and c == ord("["):
                link_d += 1
            elif prev == ord("]") and c == ord("]"):
                link_d -= 1
                if link_d <= 0:
                    st = PROSE
        elif st == TMPL:
            if prev == ord("{") and c == ord("{"):
                tmpl_d += 1
            elif prev == ord("}") and c == ord("}"):
                tmpl_d -= 1
                if tmpl_d <= 0:
                    st = PROSE
        prev = c
    return out


def consonant_skel_state(data: bytes) -> list[int]:
    """Running word skeleton hash (consonants only) — free abjad channel.
    Vowels are the 'predictable' channel; we do NOT ship them or a mask.
    State = fold of consonants seen so far in the current [A-Za-z] run.
    """
    vowels = set(b"aeiouAEIOU")
    out = []
    h = 0
    in_word = False
    for c in data:
        out.append(h & 31)  # 5-bit collected essence of word-so-far
        if chr(c).isalpha():
            in_word = True
            if c not in vowels:
                h = ((h * 33) ^ c) & 0x7FFFFFFF
        else:
            in_word = False
            h = 0
    return out


def mine_categories(data: bytes) -> list[tuple[int, int, bytes]]:
    """List of (start, end, inner_name) for [[Category:...]]."""
    hits = []
    for m in CAT_RE.finditer(data):
        hits.append((m.start(), m.end(), m.group(1)))
    return hits


def mine_tmpl_heads(data: bytes) -> list[tuple[int, int, bytes]]:
    hits = []
    for m in TMPL_RE.finditer(data):
        head = m.group(1).strip().split(b"|")[0].strip().lower()
        if head:
            hits.append((m.start(1), m.start(1) + len(m.group(1).split(b"|")[0].rstrip()), head))
    return hits


def l1_ordered_pack(data: bytes, spans: list[tuple[int, int, bytes]]) -> tuple[bytes, bytes]:
    """Per-occurrence sidecar + body with 0x0E marks (L1)."""
    body = bytearray()
    side = bytearray()
    side += struct.pack("<I", len(spans))
    pos = 0
    for start, end, _inner in spans:
        body += data[pos:start]
        body.append(0x0E)
        blob = data[start:end]
        side += struct.pack("<H", len(blob))
        side += blob
        pos = end
    body += data[pos:]
    return bytes(body), bytes(side)


def collected_unique_refs(data: bytes, spans: list[tuple[int, int, bytes]]) -> tuple[bytes, bytes]:
    """Global unique table (sorted) + body with varint rank refs at span sites.

    Table is COLLECTED essence: each distinct name once.
    Body replaces full span with MARK + 2-byte rank (or 1-byte if <256).
    """
    # use full span text as key for exact rebuild
    keys = [data[s:e] for s, e, _ in spans]
    uniq = sorted(set(keys))
    rank = {k: i for i, k in enumerate(uniq)}
    table = bytearray()
    table += struct.pack("<I", len(uniq))
    for k in uniq:
        table += struct.pack("<H", len(k))
        table += k
    body = bytearray()
    pos = 0
    for start, end, _ in spans:
        body += data[pos:start]
        r = rank[data[start:end]]
        body.append(0x0E)
        if len(uniq) <= 256:
            body.append(r & 0xFF)
        else:
            body += struct.pack("<H", r)
        pos = end
    body += data[pos:]
    return bytes(body), bytes(table)


def topk_fold_stream(data: bytes, spans: list[tuple[int, int, bytes]], k: int) -> tuple[bytes, bytes, dict]:
    """Fold top-K most frequent full spans into 2-byte codes 0x10.. in ONE stream.

    Codebook (collected) counted separately as compressor-resident tax (S1-like).
    Non-top spans left intact. Codes use 0x10 + hi, 0x11 + lo escape scheme:
    emit 0x10, then 1-byte index 0..K-1. Escape raw 0x10 as 0x10 0xFF.
    """
    keys = [data[s:e] for s, e, _ in spans]
    freq = collections.Counter(keys)
    top = [p for p, _ in freq.most_common(k)]
    code_of = {p: i for i, p in enumerate(top)}
    codebook = bytearray()
    codebook += struct.pack("<H", len(top))
    for p in top:
        codebook += struct.pack("<H", len(p))
        codebook += p
    # rewrite
    body = bytearray()
    pos = 0
    folded = 0
    saved_raw = 0
    for start, end, _ in spans:
        body += data[pos:start]
        key = data[start:end]
        if key in code_of:
            # escape any 0x10 in intervening? handled on full pass below
            body.append(0x10)
            body.append(code_of[key] & 0xFF)
            folded += 1
            saved_raw += len(key) - 2
        else:
            body += key
        pos = end
    body += data[pos:]
    # escape pre-existing 0x10 bytes in non-folded regions: second pass
    esc = bytearray()
    i = 0
    # Simpler: escape all 0x10 that aren't our fold markers by scanning with span map
    # Rebuild cleanly:
    body2 = bytearray()
    pos = 0
    span_i = 0
    span_set = {(s, e) for s, e, _ in spans}
    while pos < len(data):
        if span_i < len(spans) and pos == spans[span_i][0]:
            s, e, _ = spans[span_i]
            key = data[s:e]
            if key in code_of:
                body2.append(0x10)
                body2.append(code_of[key] & 0xFF)
            else:
                for b in key:
                    if b == 0x10:
                        body2.append(0x10)
                        body2.append(0xFF)
                    else:
                        body2.append(b)
            pos = e
            span_i += 1
        else:
            b = data[pos]
            if b == 0x10:
                body2.append(0x10)
                body2.append(0xFF)
            else:
                body2.append(b)
            pos += 1
    stats = {
        "k": k,
        "folded_hits": folded,
        "top_mass": sum(freq[p] for p in top),
        "total_hits": len(spans),
        "uniq": len(freq),
        "raw_bytes_in_spans": sum(len(data[s:e]) for s, e, _ in spans),
        "codebook_bytes": len(codebook),
    }
    return bytes(body2), bytes(codebook), stats


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument(
        "slice",
        nargs="?",
        default=str(ROOT / "benchmarks/.ladder_cache/enwik8_10m_skip20.mid"),
    )
    ap.add_argument("--kt-bytes", type=int, default=2_000_000, help="KT sample cap")
    args = ap.parse_args()
    data = Path(args.slice).read_bytes()
    tag = Path(args.slice).name
    log(f"=== collected essence probe tag={tag} plain={len(data)} ===")

    cats = mine_categories(data)
    # Prefer category spans for apples-to-apples with L1
    spans = [(s, e, inner) for s, e, inner in cats]
    log(f"category spans={len(spans)} uniq={len({data[s:e] for s,e,_ in spans})}")

    raw_z = zstd_size(data)
    log(f"A raw zstd={raw_z}")

    # B L1 ordered
    body_l1, side_l1 = l1_ordered_pack(data, spans)
    z_body = zstd_size(body_l1)
    z_side = zstd_size(side_l1)
    z_pack = zstd_size(side_l1 + body_l1)
    log(
        f"B L1 ordered: body_z={z_body} (Δ{z_body-raw_z:+d}) side_z={z_side} "
        f"pack_z={z_pack} (Δ{z_pack-raw_z:+d}) side_raw={len(side_l1)}"
    )

    # C collected unique table
    body_u, table_u = collected_unique_refs(data, spans)
    z_bu = zstd_size(body_u)
    z_tu = zstd_size(table_u)
    z_pu = zstd_size(table_u + body_u)
    uniq_n = struct.unpack_from("<I", table_u)[0]
    log(
        f"C unique table: uniq={uniq_n} table_raw={len(table_u)} table_z={z_tu} "
        f"body_z={z_bu} (Δ{z_bu-raw_z:+d}) pack_z={z_pu} (Δ{z_pu-raw_z:+d})"
    )
    # concentration
    keys = [data[s:e] for s, e, _ in spans]
    freq = collections.Counter(keys)
    for k in (16, 64, 256, 1024):
        mass = sum(c for _, c in freq.most_common(k))
        log(f"  Zipf top-{k}: hits={mass}/{len(spans)} ({100*mass/max(len(spans),1):.1f}%) "
            f"uniq_cov={min(k,len(freq))}/{len(freq)}")

    # D top-K fold (codebook = S1 tax)
    for k in (64, 256, 1024):
        if k > len(freq):
            continue
        body_f, cb, st = topk_fold_stream(data, spans, k)
        z_f = zstd_size(body_f)
        # total = body_z + codebook uncompressed (worst) and codebook zstd
        z_cb = zstd_size(cb)
        log(
            f"D top-{k} fold: folded={st['folded_hits']}/{st['total_hits']} "
            f"cb_raw={st['codebook_bytes']} cb_z={z_cb} body_z={z_f} "
            f"(Δbody{z_f-raw_z:+d}) total_body+cb_z={z_f+z_cb} (Δ{z_f+z_cb-raw_z:+d}) "
            f"total_body+cb_raw={z_f+st['codebook_bytes']} (Δ{z_f+st['codebook_bytes']-raw_z:+d})"
        )

    # E / F KT on sample
    sample = data[: min(len(data), args.kt_bytes)]
    ws = wiki_states(sample)
    sk = consonant_skel_state(sample)
    base = kt_bpc(sample, lambda i, p: p)
    joint_w = kt_bpc(sample, lambda i, p: (p, ws[i]))
    joint_sk = kt_bpc(sample, lambda i, p: (p, sk[i]))
    joint_wsk = kt_bpc(sample, lambda i, p: (p, ws[i], sk[i]))
    # mask tax proxy: 1 bit/byte for binary prose/markup
    mask_bits = sum(0 if w == PROSE else 1 for w in ws)  # rough
    # better: entropy of wiki_state
    hist = collections.Counter(ws)
    n = len(ws)
    mask_bpc = sum(-c / n * math.log2(c / n) for c in hist.values() if c) / 1.0
    # split streams by wiki_state + pay mask_bpc
    streams = [bytearray() for _ in range(5)]
    for i, b in enumerate(sample):
        streams[ws[i]].append(b)
    split_bits = 0.0
    for s in streams:
        if s:
            split_bits += kt_bpc(bytes(s), lambda i, p: p) * len(s)
    split_bpc = split_bits / n + mask_bpc

    log(f"E abjad-fx KT @{len(sample)}:")
    log(f"  baseline |prev              {base:.4f} bpc")
    log(f"  joint    |prev,wiki_state   {joint_w:.4f}  Δ={joint_w-base:+.4f}  (FREE)")
    log(f"  joint    |prev,skel5        {joint_sk:.4f}  Δ={joint_sk-base:+.4f}  (FREE)")
    log(f"  joint    |prev,wiki,skel    {joint_wsk:.4f}  Δ={joint_wsk-base:+.4f}  (FREE)")
    log(f"F split+mask wiki_state       {split_bpc:.4f}  Δ={split_bpc-base:+.4f}  (TAX={mask_bpc:.4f})")

    # Template head Zipf (collected dict candidate)
    th = mine_tmpl_heads(data)
    th_freq = collections.Counter(h for _, _, h in th)
    log(f"template heads: hits={len(th)} uniq={len(th_freq)}")
    for k in (64, 256, 1024):
        mass = sum(c for _, c in th_freq.most_common(k))
        log(f"  tmpl Zipf top-{k}: {mass}/{len(th)} ({100*mass/max(len(th),1):.1f}%)")

    # Write findings
    FINDINGS.write_text(
        f"""# Collected essence vs sidecar tax

Updated from `l_collected_essence_probe.py` on `{tag}` ({len(data)} B).
See `.hutter_collected_essence_findings.md` for the canonical write-up.
Raw log: `.hutter_logs/l_collected_essence_probe.log`.

Quick: raw_z={raw_z}; L1 pack Δ={z_pack-raw_z:+d}; unique pack Δ={z_pu-raw_z:+d};
KT free wiki Δ={joint_w-base:+.4f}; free skel Δ={joint_sk-base:+.4f}; split+mask Δ={split_bpc-base:+.4f}.
"""
    )
    log(f"wrote {FINDINGS}")
    log("done")


if __name__ == "__main__":
    main()
