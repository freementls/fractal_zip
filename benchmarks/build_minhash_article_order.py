#!/usr/bin/env python3
"""Local MinHash article-order refine (no Voyage API).

Fingerprint each non-redirect article (text shingles), then locally reorder
windows of the published order by greedy nearest-neighbor on Hamming/MinHash
distance. Score with zstd-long vs published.

Usage:
  python3 benchmarks/build_minhash_article_order.py [--score] [--window 64]
"""
from __future__ import annotations

import argparse
import os
import struct
import subprocess
import sys
import time

ROOT = "/srv/http/fractal_zip"
ENWIK9 = f"{ROOT}/tools/hutter/data/enwik9"
PUB_ORDER = f"{ROOT}/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order"
OUT_DIR = f"{ROOT}/benchmarks/.ladder_cache/article_orders"
LOG = f"{ROOT}/benchmarks/.hutter_logs/article_order_minhash.log"

REDIRECT_PREFIXES = (
    b"      <text xml:space=\"preserve\">#REDIRECT",
    b"      <text xml:space=\"preserve\">#redirect",
    b"      <text xml:space=\"preserve\">#Redirect",
    b"      <text xml:space=\"preserve\">#REdirect",
    b"      <text xml:space=\"preserve\">{{softredirect",
)

N_HASH = 64
SAMPLE = 4096  # bytes of article body for shingles


def log(msg: str) -> None:
    print(msg, flush=True)
    with open(LOG, "a") as f:
        f.write(msg + "\n")


def parse_articles(data: bytes):
    marker = b"  <page>\n"
    starts = []
    if data.startswith(marker):
        starts.append(0)
    pos = 0
    while True:
        pos = data.find(b"\n  <page>\n", pos)
        if pos == -1:
            break
        starts.append(pos + 1)
        pos += 1
    head = data[: starts[0]] if starts else b""
    spans = []
    for i, s in enumerate(starts):
        e = starts[i + 1] if i + 1 < len(starts) else len(data)
        body = data[s:e]
        is_redir = any((b"\n" + p) in body or body.startswith(p) for p in REDIRECT_PREFIXES)
        spans.append({"start": s, "end": e, "redirect": is_redir})
    return head, spans


def build_remap(spans):
    remap = {}
    count2 = 0
    for count1, sp in enumerate(spans):
        if not sp["redirect"]:
            remap[count2] = count1
            count2 += 1
    return remap, count2


def text_sample(body: bytes) -> bytes:
    key = b"<text"
    i = body.find(key)
    if i < 0:
        return body[:SAMPLE]
    j = body.find(b">", i)
    if j < 0:
        return body[:SAMPLE]
    return body[j + 1 : j + 1 + SAMPLE]


def minhash(sample: bytes) -> bytes:
    """64 x uint32 minhash over overlapping 4-byte shingles; packed big-endian."""
    if len(sample) < 4:
        sample = sample + b"\0" * (4 - len(sample))
    mins = [0xFFFFFFFF] * N_HASH
    # Seeded FNV-ish rolling hashes
    seeds = [(i * 2654435761 + 0x9E3779B9) & 0xFFFFFFFF for i in range(N_HASH)]
    for i in range(len(sample) - 3):
        sh = sample[i : i + 4]
        h0 = (sh[0] | (sh[1] << 8) | (sh[2] << 16) | (sh[3] << 24)) & 0xFFFFFFFF
        for k in range(N_HASH):
            h = (h0 ^ seeds[k]) * 16777619 & 0xFFFFFFFF
            if h < mins[k]:
                mins[k] = h
    return struct.pack(">" + "I" * N_HASH, *mins)


def mh_dist(a: bytes, b: bytes) -> int:
    """Count differing uint32 lanes (0..N_HASH)."""
    d = 0
    for i in range(0, N_HASH * 4, 4):
        if a[i : i + 4] != b[i : i + 4]:
            d += 1
    return d


def greedy_nn(ids: list[int], fps: dict[int, bytes], start: int) -> list[int]:
    remaining = set(ids)
    cur = start if start in remaining else ids[0]
    remaining.remove(cur)
    out = [cur]
    while remaining:
        best, bd = None, 10**9
        fp = fps[cur]
        for x in remaining:
            d = mh_dist(fp, fps[x])
            if d < bd:
                bd = d
                best = x
        cur = best
        remaining.remove(cur)
        out.append(cur)
    return out


def refine_windows(order: list[int], fps: dict[int, bytes], window: int) -> list[int]:
    out = list(order)
    n = len(out)
    for start in range(0, n, window // 2):
        end = min(n, start + window)
        if end - start < 4:
            continue
        chunk = out[start:end]
        # Keep first of window as anchor (preserves published seam)
        reord = greedy_nn(chunk, fps, chunk[0])
        out[start:end] = reord
    return out


def write_order(path: str, ids: list[int]) -> None:
    with open(path, "w") as f:
        for i in ids:
            f.write(f"{i}\n")


def emit_reordered(path, head, spans, positions, data):
    with open(path, "wb") as f:
        f.write(head)
        for p in positions:
            sp = spans[p]
            f.write(data[sp["start"] : sp["end"]])


def zsize(path: str) -> int:
    out = path + ".zst"
    subprocess.run(
        ["zstd", "-12", "--long=30", "-T8", "-f", "-q", path, "-o", out], check=True
    )
    n = int(subprocess.run(["stat", "-c%s", out], capture_output=True, text=True).stdout)
    os.remove(out)
    return n


def positions_from_order(order_ids, remap, n_spans):
    positions, used = [], [0] * n_spans
    for o in order_ids:
        r = remap[o]
        positions.append(r)
        used[r] = 1
    for i in range(n_spans):
        if not used[i]:
            positions.append(i)
    return positions


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--score", action="store_true")
    ap.add_argument("--window", type=int, default=64)
    args = ap.parse_args()
    os.makedirs(OUT_DIR, exist_ok=True)
    open(LOG, "w").write("")

    t0 = time.time()
    log(f"reading {ENWIK9}")
    data = open(ENWIK9, "rb").read()
    head, spans = parse_articles(data)
    remap, n_non = build_remap(spans)
    log(f"articles={len(spans)} non_redirect={n_non}")

    pub = [int(l) for l in open(PUB_ORDER) if l.strip()]
    pub = [x for x in pub if x < n_non]
    log(f"published order_len={len(pub)}")

    log("fingerprinting...")
    fps: dict[int, bytes] = {}
    for nr in range(n_non):
        raw = remap[nr]
        sp = spans[raw]
        fps[nr] = minhash(text_sample(data[sp["start"] : sp["end"]]))
        if (nr + 1) % 20000 == 0:
            log(f"  fp {nr+1}/{n_non}")
    log(f"fingerprints done in {time.time()-t0:.1f}s")

    log(f"refining windows={args.window}")
    refined = refine_windows(pub, fps, args.window)
    path = f"{OUT_DIR}/minhash_w{args.window}.order"
    write_order(path, refined)
    moved = sum(1 for a, b in zip(pub, refined) if a != b)
    log(f"wrote {path} moved_slots={moved}/{len(pub)}")

    if not args.score:
        log("done (no --score)")
        return

    for name, ids in (("published", pub), (f"minhash_w{args.window}", refined)):
        positions = positions_from_order(ids, remap, len(spans))
        tmp = f"{OUT_DIR}/_{name}.tmp"
        emit_reordered(tmp, head, spans, positions, data)
        zs = zsize(tmp)
        os.remove(tmp)
        log(f"SCORE {name}: zstd12L30={zs:,}")

    log(f"total {time.time()-t0:.1f}s")


if __name__ == "__main__":
    main()
