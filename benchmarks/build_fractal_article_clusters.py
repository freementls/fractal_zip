#!/usr/bin/env python3
"""Fractal Block Profile System — Phase 2: article segmentation + clustering.

Parses enwik9 into article-grained blocks (reusing the same page-boundary /
redirect-remap convention as `build_minhash_article_order.py` and
`article_reorder.h`, so cluster IDs produced here line up index-for-index
with the existing `new_article_order` non-redirect numbering) and assigns
each non-redirect article a single fractal cluster ID.

This one clustering substrate is deliberately shared by three later axes:
  - Axis 1 (mixer recipe):     cluster ID -> feature for recipe selection
  - Axis 2 (dictionary):       cluster ID -> per-cluster vocab subset
  - Axis 4 (reorder window):   cluster ID -> per-cluster winsort window

Two complementary signals are combined into one cluster ID space:
  1. MinHash LSH near-duplicate groups (catches structurally-identical
     boilerplate: year pages, disambig lists, stub templates, etc.) —
     these get their own dedicated cluster IDs.
  2. K-means over cheap structural content-type features (length, digit
     density, template/link/list density, infobox/disambig flags) for
     everything else — this is the "content type" partition (prose vs
     infobox vs list vs numeric-heavy) the fractal system is meant to
     exploit.

Usage:
  python3 benchmarks/build_fractal_article_clusters.py --slice-mb 10
  python3 benchmarks/build_fractal_article_clusters.py            # full enwik9
"""
from __future__ import annotations

import argparse
import json
import os
import sys
import time

import numpy as np

ROOT = "/srv/http/fractal_zip"
ENWIK9 = f"{ROOT}/tools/hutter/data/enwik9"
OUT_DIR = f"{ROOT}/benchmarks/.ladder_cache/fractal_clusters"
LOG = f"{ROOT}/benchmarks/.hutter_logs/fractal_article_clusters.log"

REDIRECT_PREFIXES = (
    b"      <text xml:space=\"preserve\">#REDIRECT",
    b"      <text xml:space=\"preserve\">#redirect",
    b"      <text xml:space=\"preserve\">#Redirect",
    b"      <text xml:space=\"preserve\">#REdirect",
    b"      <text xml:space=\"preserve\">{{softredirect",
)

N_HASH = 64
SAMPLE = 4096  # bytes of article body sampled for minhash + features
LSH_BANDS = 8
LSH_ROWS = N_HASH // LSH_BANDS  # 8
MIN_DUP_GROUP = 3  # union-find components smaller than this fall back to k-means
K_CONTENT = 24  # k-means cluster count for the non-near-dup majority
KMEANS_ITERS = 25
SEED = 923  # match fx2-cmix SEED convention for reproducibility


def log(msg: str) -> None:
    print(msg, flush=True)
    os.makedirs(os.path.dirname(LOG), exist_ok=True)
    with open(LOG, "a") as f:
        f.write(msg + "\n")


def parse_articles(data: bytes):
    """Identical convention to build_minhash_article_order.py / article_reorder.h."""
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


def extract_title(body: bytes) -> bytes:
    i = body.find(b"<title>")
    if i < 0:
        return b""
    j = body.find(b"</title>", i)
    if j < 0:
        return b""
    return body[i + 7 : j]


_SEEDS64 = np.array(
    [((i * 2654435761 + 0x9E3779B9) & 0xFFFFFFFF) for i in range(N_HASH)], dtype=np.uint64
)


def minhash_fast(sample: bytes) -> np.ndarray:
    """Vectorized 64-lane minhash over overlapping 4-byte shingles."""
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


def struct_features(body: bytes, sample: bytes, title: bytes) -> np.ndarray:
    """Cheap structural content-type features, all O(len(sample)) or O(len(title))."""
    n = max(len(sample), 1)
    digits = sum(1 for c in sample if 48 <= c <= 57)
    tmpl = sample.count(b"{{")
    link = sample.count(b"[[")
    list_marks = sample.count(b"\n*") + sample.count(b"\n#") + sample.count(b"\n:")
    infobox = 1.0 if (b"nfobox" in body) else 0.0
    disambig = 1.0 if (b"disambiguation" in body.lower() or b"disambig" in title.lower()) else 0.0
    length_log = float(np.log2(len(body) + 1))
    return np.array(
        [
            length_log,
            digits / n,
            tmpl / n * 1000.0,
            link / n * 1000.0,
            list_marks / n * 1000.0,
            infobox,
            disambig,
        ],
        dtype=np.float64,
    )


FEATURE_NAMES = [
    "length_log2",
    "digit_ratio",
    "template_per_kchar",
    "link_per_kchar",
    "list_per_kchar",
    "infobox_flag",
    "disambig_flag",
]


class UnionFind:
    def __init__(self, n: int):
        self.parent = list(range(n))

    def find(self, x: int) -> int:
        root = x
        while self.parent[root] != root:
            root = self.parent[root]
        while self.parent[x] != root:
            self.parent[x], x = root, self.parent[x]
        return root

    def union(self, a: int, b: int) -> None:
        ra, rb = self.find(a), self.find(b)
        if ra != rb:
            self.parent[rb] = ra


def lsh_dup_groups(fps: np.ndarray) -> np.ndarray:
    """fps: (n, N_HASH) uint32. Returns component id per row via band-hash LSH."""
    n = fps.shape[0]
    uf = UnionFind(n)
    for b in range(LSH_BANDS):
        band = fps[:, b * LSH_ROWS : (b + 1) * LSH_ROWS]
        # Pack the band's rows into one hashable key per article.
        keys = band.astype(np.uint64)
        key = np.zeros(n, dtype=np.uint64)
        for r in range(LSH_ROWS):
            key = (key * np.uint64(1000003)) ^ keys[:, r]
        order = np.argsort(key, kind="stable")
        sorted_keys = key[order]
        same = sorted_keys[1:] == sorted_keys[:-1]
        idx = np.nonzero(same)[0]
        for i in idx:
            uf.union(int(order[i]), int(order[i + 1]))
    comp = np.array([uf.find(i) for i in range(n)], dtype=np.int64)
    return comp


def kmeans(x: np.ndarray, k: int, iters: int, seed: int) -> np.ndarray:
    rng = np.random.default_rng(seed)
    mu = x.std(axis=0) + 1e-9
    xn = (x - x.mean(axis=0)) / mu
    n = xn.shape[0]
    centers = xn[rng.choice(n, size=k, replace=False)]
    assign = np.zeros(n, dtype=np.int64)
    for _ in range(iters):
        d2 = ((xn[:, None, :] - centers[None, :, :]) ** 2).sum(axis=2)
        new_assign = d2.argmin(axis=1)
        if np.array_equal(new_assign, assign) and _ > 0:
            assign = new_assign
            break
        assign = new_assign
        for c in range(k):
            mask = assign == c
            if mask.any():
                centers[c] = xn[mask].mean(axis=0)
    return assign


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--slice-mb", type=int, default=0, help="0 = full enwik9")
    ap.add_argument("--k-content", type=int, default=K_CONTENT)
    ap.add_argument("--out-tag", type=str, default="")
    args = ap.parse_args()

    os.makedirs(OUT_DIR, exist_ok=True)
    open(LOG, "w").write("")
    t0 = time.time()

    log(f"reading {ENWIK9} slice_mb={args.slice_mb or 'full'}")
    with open(ENWIK9, "rb") as f:
        data = f.read(args.slice_mb * 1024 * 1024) if args.slice_mb else f.read()
    log(f"read {len(data):,} bytes in {time.time()-t0:.1f}s")

    head, spans = parse_articles(data)
    remap, n_non = build_remap(spans)
    log(f"articles={len(spans)} non_redirect={n_non}")

    fps = np.zeros((n_non, N_HASH), dtype=np.uint32)
    feats = np.zeros((n_non, len(FEATURE_NAMES)), dtype=np.float64)
    t1 = time.time()
    for nr in range(n_non):
        raw = remap[nr]
        sp = spans[raw]
        body = data[sp["start"] : sp["end"]]
        sample = text_sample(body)
        title = extract_title(body)
        fps[nr] = minhash_fast(sample)
        feats[nr] = struct_features(body, sample, title)
        if (nr + 1) % 20000 == 0:
            elapsed = time.time() - t1
            rate = (nr + 1) / max(elapsed, 1e-6)
            log(f"  fp {nr+1}/{n_non} ({rate:.0f}/s, eta {(n_non-nr-1)/max(rate,1e-6):.0f}s)")
    log(f"fingerprints+features done in {time.time()-t1:.1f}s")

    t2 = time.time()
    comp = lsh_dup_groups(fps)
    uniq, counts = np.unique(comp, return_counts=True)
    dup_components = uniq[counts >= MIN_DUP_GROUP]
    log(
        f"LSH: {len(uniq)} components, {len(dup_components)} with size>={MIN_DUP_GROUP} "
        f"({time.time()-t2:.1f}s)"
    )

    is_dup = np.isin(comp, dup_components)
    dup_id_map = {int(c): i for i, c in enumerate(dup_components)}

    cluster_id = np.full(n_non, -1, dtype=np.int64)
    for nr in range(n_non):
        if is_dup[nr]:
            cluster_id[nr] = dup_id_map[int(comp[nr])]
    n_dup_clusters = len(dup_components)

    content_mask = ~is_dup
    n_content = int(content_mask.sum())
    t3 = time.time()
    if n_content > 0:
        k = min(args.k_content, max(1, n_content))
        content_assign = kmeans(feats[content_mask], k, KMEANS_ITERS, SEED)
        cluster_id[content_mask] = n_dup_clusters + content_assign
        n_content_clusters = k
    else:
        n_content_clusters = 0
    log(f"k-means: {n_content_clusters} content clusters over {n_content} articles ({time.time()-t3:.1f}s)")

    assert (cluster_id >= 0).all()
    n_clusters = n_dup_clusters + n_content_clusters

    tag = args.out_tag or (f"slice{args.slice_mb}mb" if args.slice_mb else "full")
    tsv_path = f"{OUT_DIR}/enwik9_article_clusters_{tag}.tsv"
    with open(tsv_path, "w") as f:
        f.write("nonredirect_idx\tcluster_id\tkind\n")
        for nr in range(n_non):
            kind = "dup" if is_dup[nr] else "content"
            f.write(f"{nr}\t{int(cluster_id[nr])}\t{kind}\n")
    log(f"wrote {tsv_path}")

    sizes = np.bincount(cluster_id, minlength=n_clusters)
    summary = {
        "tag": tag,
        "bytes_read": len(data),
        "articles_total": len(spans),
        "articles_non_redirect": n_non,
        "n_dup_clusters": n_dup_clusters,
        "n_content_clusters": n_content_clusters,
        "n_clusters_total": n_clusters,
        "cluster_sizes_min": int(sizes.min()) if n_clusters else 0,
        "cluster_sizes_max": int(sizes.max()) if n_clusters else 0,
        "cluster_sizes_median": float(np.median(sizes)) if n_clusters else 0,
        "top10_cluster_sizes": sorted(sizes.tolist(), reverse=True)[:10],
        "feature_names": FEATURE_NAMES,
        "elapsed_s": time.time() - t0,
    }
    json_path = f"{OUT_DIR}/enwik9_article_clusters_{tag}.summary.json"
    with open(json_path, "w") as f:
        json.dump(summary, f, indent=2)
    log(f"wrote {json_path}")
    log(json.dumps(summary, indent=2))
    log(f"TOTAL {time.time()-t0:.1f}s")


if __name__ == "__main__":
    main()
