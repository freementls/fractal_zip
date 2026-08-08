#!/usr/bin/env python3
"""Rebuild enwik9 article order with a local embedding substitute.

Official fx2-cmix used voyage-large-2-instruct → t-SNE → k-means.
This host has no VOYAGE_API_KEY and no GPU, so we use:
  title+snippet hashed char-ngram features → PCA-1D → k-means →
  within-cluster sort by original article index.

Output is drop-in compatible with .new_article_order (one article id per line).
Validate later with remap + cmix/-e prefix; this script also emits an xz
proxy delta on a remapped enwik9 head for cheap directional check.
"""
from __future__ import annotations

import argparse
import hashlib
import math
import os
import re
import struct
import sys
import time
from pathlib import Path

import numpy as np

REDIRECT_PREFIXES = (
    b"#REDIRECT",
    b"#redirect",
    b"#Redirect",
    b"#REdirect",
    b"{{softredirect",
)


def parse_articles(path: Path, truncate: int = 4000):
    """Yield (page_index, title, content) for non-redirect pages.

    page_index matches fx2 embeddings.ipynb: index into enwik9.split('<page>')
    (parts[0] is preamble; first page is index 0). Order files store these
    indices, then `./remap` compresses them by skipping redirect numbers.
    """
    data = path.read_bytes()
    parts = data.split(b"<page>")
    out = []
    for page_index, chunk in enumerate(parts[1:]):
        text_start = chunk.find(b'<text xml:space="preserve">')
        text_end = chunk.find(b"</text>")
        if text_start < 0 or text_end < 0:
            continue
        body = chunk[text_start + 29 : text_end]
        # Notebook skips #Redirect at start of text; keep same filter.
        if len(body) >= 2 and body[0:1] == b"#" and body[1:2] in (b"R", b"r"):
            continue
        if any(body.startswith(p) for p in REDIRECT_PREFIXES):
            continue
        title_m = re.search(br"<title>(.*?)</title>", chunk, re.DOTALL)
        if not title_m:
            continue
        title = title_m.group(1).decode("utf-8", "replace")
        content = re.sub(br"\{\{[^}]*\n[^}]*\}\}", b"", body)
        content = content[:truncate].decode("utf-8", "replace")
        content = re.sub(r"\{\{[^}]*\}\}", "", content)
        out.append((page_index, title, title + "\n" + content))
    return out


def hashed_ngrams(text: str, dim: int = 256, n: int = 3) -> np.ndarray:
    v = np.zeros(dim, dtype=np.float32)
    t = text.lower()
    if len(t) < n:
        t = t.ljust(n)
    for i in range(len(t) - n + 1):
        g = t[i : i + n]
        h = hashlib.blake2b(g.encode("utf-8", "replace"), digest_size=8).digest()
        idx = struct.unpack("<Q", h)[0] % dim
        sign = 1.0 if (h[0] & 1) else -1.0
        v[idx] += sign
    # L2 normalize
    nrm = float(np.linalg.norm(v))
    if nrm > 0:
        v /= nrm
    return v


def pca_1d(X: np.ndarray) -> np.ndarray:
    Xc = X - X.mean(axis=0, keepdims=True)
    # power iteration for top singular vector
    rng = np.random.default_rng(923)
    w = rng.standard_normal(Xc.shape[1]).astype(np.float64)
    w /= np.linalg.norm(w) + 1e-12
    for _ in range(40):
        w = Xc.T @ (Xc @ w)
        w /= np.linalg.norm(w) + 1e-12
    return (Xc @ w).astype(np.float64)


def kmeans_1d(x: np.ndarray, k: int, iters: int = 30) -> np.ndarray:
    """Return cluster id per point; clusters ordered by centroid ascending."""
    qs = np.linspace(0, 100, k + 2)[1:-1]
    cents = np.percentile(x, qs)
    labels = np.zeros(len(x), dtype=np.int32)
    for _ in range(iters):
        # assign
        d = np.abs(x[:, None] - cents[None, :])
        labels = d.argmin(axis=1).astype(np.int32)
        # update
        for j in range(k):
            m = labels == j
            if m.any():
                cents[j] = x[m].mean()
    # renumber clusters by centroid order
    order = np.argsort(cents)
    remap = {int(old): int(new) for new, old in enumerate(order)}
    return np.array([remap[int(l)] for l in labels], dtype=np.int32)


def build_order(articles, k: int = 64) -> list[int]:
    print(f"embedding {len(articles)} articles…", flush=True)
    X = np.stack([hashed_ngrams(a[2]) for a in articles])
    print("PCA-1D…", flush=True)
    z = pca_1d(X)
    print(f"k-means k={k}…", flush=True)
    labs = kmeans_1d(z, k=k)
    # within each cluster, sort by original page_index (numerically increasing)
    by_cluster: dict[int, list[int]] = {i: [] for i in range(k)}
    for (page_index, _title, _c), lab in zip(articles, labs):
        by_cluster[int(lab)].append(page_index)
    order_ids: list[int] = []
    for c in range(k):
        order_ids.extend(sorted(by_cluster[c]))
    return order_ids


def xz_proxy_bytes(data: bytes) -> int:
    import lzma

    return len(lzma.compress(data, preset=6))


def remap_head_by_order(
    enwik_path: Path, order_page_indices: list[int], head_bytes: int
) -> bytes:
    """Cheap structural proxy: concatenate <page> blocks in order, truncate.

    order_page_indices are pre-remap split indices (same as embeddings notebook).
    """
    data = enwik_path.read_bytes()
    parts = data.split(b"<page>")
    preamble = parts[0]
    out = bytearray(preamble)
    n_pages = len(parts) - 1
    for idx in order_page_indices:
        if idx < 0 or idx >= n_pages:
            continue
        out.extend(b"<page>")
        out.extend(parts[idx + 1])
        if len(out) >= head_bytes:
            break
    return bytes(out[:head_bytes])


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument(
        "--enwik9",
        default="/srv/http/fractal_zip/tools/hutter/data/enwik9",
    )
    ap.add_argument(
        "--out",
        default="/srv/http/fractal_zip/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_local",
    )
    ap.add_argument("--k", type=int, default=64)
    ap.add_argument("--truncate", type=int, default=2000)
    ap.add_argument("--proxy-head", type=int, default=10_000_000)
    ap.add_argument("--skip-proxy", action="store_true")
    args = ap.parse_args()

    t0 = time.time()
    articles = parse_articles(Path(args.enwik9), truncate=args.truncate)
    print(f"parsed non-redirect articles={len(articles)} in {time.time()-t0:.1f}s")
    order_pages = build_order(articles, k=args.k)
    raw_out = Path(str(args.out) + "_pages")
    raw_out.write_text("\n".join(map(str, order_pages)) + "\n")
    print(f"wrote page-index order {raw_out} n={len(order_pages)}")

    # Run C++ remap if available (page index → skip-redirect index for S1 size).
    remap_bin = Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix/remap")
    if not remap_bin.is_file():
        remap_bin = Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix/run/remap")
    outp = Path(args.out)
    if remap_bin.is_file():
        import subprocess

        with outp.open("w") as fout:
            subprocess.check_call(
                [str(remap_bin), str(raw_out), args.enwik9], stdout=fout
            )
        print(f"remapped -> {outp} bytes={outp.stat().st_size}")
    else:
        # Fallback: build remap in Python (same logic as article_remap.cpp)
        print("remap binary missing — python remap", flush=True)
        data_lines = Path(args.enwik9).read_text("utf-8", "replace").splitlines()
        count1 = -1
        count2 = -1
        redirect = False
        prefixes = (
            '      <text xml:space="preserve">#REDIRECT',
            '      <text xml:space="preserve">#redirect',
            '      <text xml:space="preserve">#Redirect',
            '      <text xml:space="preserve">#REdirect',
            '      <text xml:space="preserve">{{softredirect',
        )
        remap: dict[int, int] = {}
        for line in data_lines:
            for pre in prefixes:
                if line.startswith(pre):
                    redirect = True
                    break
            if line == "  <page>":
                if not redirect:
                    remap[count1] = count2
                    count2 += 1
                count1 += 1
                redirect = False
        remapped = [remap[i] for i in order_pages if i in remap]
        outp.write_text("\n".join(map(str, remapped)) + "\n")
        print(f"wrote {outp} n={len(remapped)} bytes={outp.stat().st_size}")

    baseline = Path(
        "/srv/http/fractal_zip/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order"
    )
    if baseline.exists() and outp.exists():
        b_ids = [int(x) for x in baseline.read_text().splitlines() if x.strip()]
        n_ids = [int(x) for x in outp.read_text().splitlines() if x.strip()]
        pos_b = {wid: i for i, wid in enumerate(b_ids)}
        pos_n = {wid: i for i, wid in enumerate(n_ids)}
        shared = [w for w in n_ids if w in pos_b]
        if shared:
            xb = np.array([pos_b[w] for w in shared], dtype=np.float64)
            xn = np.array([pos_n[w] for w in shared], dtype=np.float64)
            xb = (xb - xb.mean()) / (xb.std() + 1e-12)
            xn = (xn - xn.mean()) / (xn.std() + 1e-12)
            spearman = float(np.dot(xb, xn) / len(xb))
            print(
                f"spearman_vs_published≈{spearman:.4f} shared={len(shared)} "
                f"size_pub={baseline.stat().st_size} size_local={outp.stat().st_size}"
            )

    if not args.skip_proxy:
        print(f"xz proxy on remapped head {args.proxy_head}…", flush=True)
        enwik = Path(args.enwik9)
        # For proxy, use page-index orders. Invert published remapped IDs roughly
        # via the page-index file if present; else compare local vs natural only.
        head_loc = remap_head_by_order(enwik, order_pages, args.proxy_head)
        natural = enwik.read_bytes()[: args.proxy_head]
        # published page order unknown post-remap; approximate with sorted
        # published remapped→ we only compare local vs natural here.
        xz_nat = xz_proxy_bytes(natural)
        xz_loc = xz_proxy_bytes(head_loc)
        print(
            f"xz_proxy head={args.proxy_head}: natural={xz_nat} local_order={xz_loc} "
            f"Δlocal_vs_nat={xz_loc-xz_nat:+d}"
        )
        # Also compare against published order file size (S1 component)
        if baseline.exists():
            print(
                f"S1_order_bytes published={baseline.stat().st_size} "
                f"local={outp.stat().st_size} Δ={outp.stat().st_size-baseline.stat().st_size:+d}"
            )
    print(f"DONE in {time.time()-t0:.1f}s")
    return 0


if __name__ == "__main__":
    sys.exit(main())
