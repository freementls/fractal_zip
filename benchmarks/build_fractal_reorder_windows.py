#!/usr/bin/env python3
"""Phase 4b: per-cluster winsort window search (axis 4).

Reuses the exact winsort refine algorithm from build_minhash_article_order.py
(`refine_windows`/`greedy_nn`/`minhash`) but makes the local window size a
function of each anchor article's Phase 2 cluster assignment instead of one
fixed global constant -- "the reorder W becomes local, not global" per the
plan. Prior *global* W sweeps (see .hutter_resource_trade_findings.md) found
a non-monotonic joint-S curve: 256->-9581, 512->-16544 (banked), 1024->-3119,
2048->+8814, 4096->+27289 -- i.e. different content wants different W, which
is exactly the motivation for this per-cluster variant instead of another
flat global choice.

Cluster -> window heuristic (informed by the prior sweep, not re-derived from
scratch -- a full per-cluster W search would need one real compress per
cluster-W combination, hours of compute; this applies the existing global
finding *locally*: near-duplicate boilerplate clusters are typically thin and
scattered across the corpus, so they need a much larger window than 512 to
even land two members in the same refinement neighborhood, while ordinary
content clusters keep the banked-optimal 512):
  - kind == "dup"      (Phase 2 LSH near-duplicate clusters) -> window 4096
  - kind == "content"  (Phase 2 k-means structural clusters) -> window 512
  - unclustered article                                       -> window 512
                                                                  (bit-identical
                                                                  to today's
                                                                  banked
                                                                  refine_windows(512)
                                                                  wherever no
                                                                  cluster fires)

Output: a page-index-space order file compatible with
run_article_order_ab_fair.sh's ORDER_A/ORDER_B convention, so the existing
fair-probe machinery can be reused unmodified for validation.
"""
from __future__ import annotations

import argparse
import os
import struct
import sys
import time

import numpy as np

ROOT = "/srv/http/fractal_zip"
ENWIK9 = f"{ROOT}/tools/hutter/data/enwik9"
PUB_ORDER = f"{ROOT}/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order"
CLUSTER_TSV = f"{ROOT}/benchmarks/.ladder_cache/fractal_clusters/enwik9_article_clusters_full.tsv"

REDIRECT_PREFIXES = (
    b"      <text xml:space=\"preserve\">#REDIRECT",
    b"      <text xml:space=\"preserve\">#redirect",
    b"      <text xml:space=\"preserve\">#Redirect",
    b"      <text xml:space=\"preserve\">#REdirect",
    b"      <text xml:space=\"preserve\">{{softredirect",
)

N_HASH = 64
SAMPLE = 4096


def log(msg):
    print(msg, flush=True)


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


# Vectorized minhash (same lane-seed formula as build_minhash_article_order.py's
# scalar minhash(), reimplemented with numpy per
# build_fractal_article_clusters.py's minhash_fast -- the earlier scalar
# per-byte-per-lane Python loop took >20 min just to reach 40k/172k articles;
# this vectorized version reuses Phase 2's proven-fast approach instead.
_SEEDS64 = np.array(
    [((i * 2654435761 + 0x9E3779B9) & 0xFFFFFFFF) for i in range(N_HASH)], dtype=np.uint64
)


def minhash(sample: bytes) -> np.ndarray:
    if len(sample) < 4:
        sample = sample + b"\0" * (4 - len(sample))
    arr = np.frombuffer(sample, dtype=np.uint8)
    windows = np.lib.stride_tricks.sliding_window_view(arr, 4)
    w = (
        windows[:, 0].astype(np.uint64)
        | (windows[:, 1].astype(np.uint64) << 8)
        | (windows[:, 2].astype(np.uint64) << 16)
        | (windows[:, 3].astype(np.uint64) << 24)
    )
    h = (w[:, None] ^ _SEEDS64[None, :]) * np.uint64(16777619) & np.uint64(0xFFFFFFFF)
    return h.min(axis=0).astype(np.uint32)


def mh_dist(a: np.ndarray, b: np.ndarray) -> int:
    return int(np.count_nonzero(a != b))


def greedy_nn(ids, fps_matrix, id_to_row, start):
    """Vectorized greedy nearest-neighbor chain within one window.

    fps_matrix: (n_non, N_HASH) uint32 array of all fingerprints.
    id_to_row: nonredirect_idx -> row in fps_matrix (identity in this script,
    kept explicit for clarity / future re-indexing).
    """
    ids = list(ids)
    rows = np.array([id_to_row[i] for i in ids], dtype=np.int64)
    local_fps = fps_matrix[rows]  # (k, N_HASH)
    k = len(ids)
    used = np.zeros(k, dtype=bool)
    start_pos = ids.index(start) if start in ids else 0
    cur_pos = start_pos
    used[cur_pos] = True
    out = [ids[cur_pos]]
    for _ in range(k - 1):
        cur_fp = local_fps[cur_pos]
        d = np.count_nonzero(local_fps != cur_fp[None, :], axis=1)
        d = np.where(used, np.iinfo(d.dtype).max, d)
        cur_pos = int(np.argmin(d))
        used[cur_pos] = True
        out.append(ids[cur_pos])
    return out


def load_clusters(path):
    """nonredirect_idx -> kind ('dup'|'content')."""
    kind_of = {}
    with open(path) as f:
        header = f.readline()
        for line in f:
            parts = line.rstrip("\n").split("\t")
            if len(parts) < 3:
                continue
            idx, cluster_id, kind = parts[0], parts[1], parts[2]
            kind_of[int(idx)] = kind
    return kind_of


def window_for(nr_idx, kind_of, w_dup, w_content, w_default):
    kind = kind_of.get(nr_idx)
    if kind == "dup":
        return w_dup
    if kind == "content":
        return w_content
    return w_default


def refine_windows_percluster(order, fps_matrix, id_to_row, kind_of, w_dup, w_content, w_default):
    out = list(order)
    n = len(out)
    start = 0
    windows_used = {"dup": 0, "content": 0, "default": 0}
    t0 = time.time()
    while start < n:
        anchor_nr = out[start]
        w = window_for(anchor_nr, kind_of, w_dup, w_content, w_default)
        kind = kind_of.get(anchor_nr, "default")
        windows_used[kind if kind in ("dup", "content") else "default"] += 1
        end = min(n, start + w)
        if end - start >= 4:
            chunk = out[start:end]
            reord = greedy_nn(chunk, fps_matrix, id_to_row, chunk[0])
            out[start:end] = reord
        start += max(1, w // 2)
        if start % 20000 < (w // 2):
            log(f"  refine progress {start}/{n} ({time.time()-t0:.1f}s)")
    return out, windows_used


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--w-dup", type=int, default=4096)
    ap.add_argument("--w-content", type=int, default=512)
    ap.add_argument("--w-default", type=int, default=512)
    ap.add_argument("--out-pages", required=True,
                     help="output order file in raw <page>-split index space "
                          "(compatible with run_article_order_ab_fair.sh)")
    ap.add_argument("--out-nonredirect", default=None,
                     help="optional: also write the nonredirect-index-space order")
    ap.add_argument("--fp-cache", default=None,
                     help="npz .npz path to load/save (fps_matrix, remap) and skip re-fingerprint")
    args = ap.parse_args()

    t0 = time.time()
    kind_of = load_clusters(CLUSTER_TSV)
    log(f"loaded cluster kinds for {len(kind_of)} articles from {CLUSTER_TSV}")

    if args.fp_cache and os.path.isfile(args.fp_cache):
        log(f"loading fingerprint cache {args.fp_cache}")
        cached = np.load(args.fp_cache)
        fps_matrix = cached["fps"]
        # dense nr→page index (same as dict remap[nr])
        remap = {i: int(v) for i, v in enumerate(cached["remap"].tolist())}
        n_non = int(fps_matrix.shape[0])
        id_to_row = {nr: nr for nr in range(n_non)}
        log(f"cache n_non={n_non} loaded in {time.time()-t0:.1f}s")
    else:
        log(f"reading {ENWIK9}")
        data = open(ENWIK9, "rb").read()
        head, spans = parse_articles(data)
        remap, n_non = build_remap(spans)
        log(f"articles={len(spans)} non_redirect={n_non}")

        log("fingerprinting (vectorized)...")
        fps_matrix = np.zeros((n_non, N_HASH), dtype=np.uint32)
        id_to_row = {}
        for nr in range(n_non):
            raw = remap[nr]
            sp = spans[raw]
            fps_matrix[nr] = minhash(text_sample(data[sp["start"]:sp["end"]]))
            id_to_row[nr] = nr
            if (nr + 1) % 40000 == 0:
                log(f"  fp {nr+1}/{n_non} ({time.time()-t0:.1f}s)")
        log(f"fingerprints done in {time.time()-t0:.1f}s")
        if args.fp_cache:
            os.makedirs(os.path.dirname(args.fp_cache) or ".", exist_ok=True)
            remap_arr = np.array([remap[i] for i in range(n_non)], dtype=np.int32)
            np.savez_compressed(args.fp_cache, fps=fps_matrix, remap=remap_arr)
            log(f"wrote fingerprint cache {args.fp_cache}")

    pub = [int(l) for l in open(PUB_ORDER) if l.strip()]
    pub = [x for x in pub if x < n_non]
    log(f"published order_len={len(pub)}")

    log(f"refining (per-cluster): w_dup={args.w_dup} w_content={args.w_content} w_default={args.w_default}")
    refined, windows_used = refine_windows_percluster(
        pub, fps_matrix, id_to_row, kind_of, args.w_dup, args.w_content, args.w_default)
    moved = sum(1 for a, b in zip(pub, refined) if a != b)
    log(f"windows_used(anchor kind counts)={windows_used} moved_slots={moved}/{len(pub)}")

    if args.out_nonredirect:
        with open(args.out_nonredirect, "w") as f:
            for i in refined:
                f.write(f"{i}\n")
        log(f"wrote nonredirect-space order to {args.out_nonredirect}")

    with open(args.out_pages, "w") as f:
        for nr in refined:
            f.write(f"{remap[nr]}\n")
    log(f"wrote page-space order to {args.out_pages}")
    log(f"total {time.time()-t0:.1f}s")


if __name__ == "__main__":
    main()
