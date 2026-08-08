#!/usr/bin/env python3
"""Article order via sentence-transformers (CPU) — voyage substitute.

Model default: all-MiniLM-L6-v2 (384-d). Pipeline mirrors fx2:
  embed → PCA-1D → k-means → within-cluster original-index sort → remap.
"""
from __future__ import annotations

import argparse
import os
import re
import sys
import time
from pathlib import Path

import numpy as np

os.environ.setdefault("CUDA_VISIBLE_DEVICES", "")
os.environ.setdefault("TOKENIZERS_PARALLELISM", "false")

REDIRECT_PREFIXES = (
    b"#REDIRECT",
    b"#redirect",
    b"#Redirect",
    b"#REdirect",
    b"{{softredirect",
)


def parse_articles(path: Path, truncate: int = 400):
    data = path.read_bytes()
    parts = data.split(b"<page>")
    out = []
    for page_index, chunk in enumerate(parts[1:]):
        text_start = chunk.find(b'<text xml:space="preserve">')
        text_end = chunk.find(b"</text>")
        if text_start < 0 or text_end < 0:
            continue
        body = chunk[text_start + 29 : text_end]
        if len(body) >= 2 and body[0:1] == b"#" and body[1:2] in (b"R", b"r"):
            continue
        if any(body.startswith(p) for p in REDIRECT_PREFIXES):
            continue
        title_m = re.search(br"<title>(.*?)</title>", chunk, re.DOTALL)
        if not title_m:
            continue
        title = title_m.group(1).decode("utf-8", "replace")
        content = body[:truncate].decode("utf-8", "replace")
        content = re.sub(r"\{\{[^}]*\}\}", " ", content)
        content = re.sub(r"\s+", " ", content).strip()
        out.append((page_index, title + "\n" + content))
    return out


def pca_1d(X: np.ndarray) -> np.ndarray:
    Xc = X - X.mean(axis=0, keepdims=True)
    rng = np.random.default_rng(923)
    w = rng.standard_normal(Xc.shape[1]).astype(np.float64)
    w /= np.linalg.norm(w) + 1e-12
    for _ in range(60):
        w = Xc.T @ (Xc @ w)
        w /= np.linalg.norm(w) + 1e-12
    return (Xc @ w).astype(np.float64)


def kmeans_1d(x: np.ndarray, k: int, iters: int = 40) -> np.ndarray:
    qs = np.linspace(0, 100, k + 2)[1:-1]
    cents = np.percentile(x, qs)
    labels = np.zeros(len(x), dtype=np.int32)
    for _ in range(iters):
        d = np.abs(x[:, None] - cents[None, :])
        labels = d.argmin(axis=1).astype(np.int32)
        for j in range(k):
            m = labels == j
            if m.any():
                cents[j] = x[m].mean()
    order = np.argsort(cents)
    remap = {int(old): int(new) for new, old in enumerate(order)}
    return np.array([remap[int(l)] for l in labels], dtype=np.int32)


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--enwik9", default="/srv/http/fractal_zip/tools/hutter/data/enwik9")
    ap.add_argument(
        "--out",
        default="/srv/http/fractal_zip/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_minilm",
    )
    ap.add_argument("--model", default="sentence-transformers/all-MiniLM-L6-v2")
    ap.add_argument("--k", type=int, default=64)
    ap.add_argument("--truncate", type=int, default=400)
    ap.add_argument("--batch", type=int, default=256)
    ap.add_argument("--cache", default="/srv/http/fractal_zip/benchmarks/.ladder_cache/minilm_embeds.npy")
    ap.add_argument("--limit", type=int, default=0, help="debug: only first N articles")
    args = ap.parse_args()

    t0 = time.time()
    print("parsing…", flush=True)
    articles = parse_articles(Path(args.enwik9), truncate=args.truncate)
    if args.limit > 0:
        articles = articles[: args.limit]
    print(f"articles={len(articles)} parse={time.time()-t0:.1f}s", flush=True)

    cache = Path(args.cache)
    if cache.is_file() and cache.stat().st_size > 1000:
        print(f"loading cache {cache}", flush=True)
        X = np.load(cache)
        if X.shape[0] != len(articles):
            print(f"cache size {X.shape[0]} != {len(articles)}; re-embed", flush=True)
            X = None
    else:
        X = None

    if X is None:
        from sentence_transformers import SentenceTransformer

        print(f"loading model {args.model}…", flush=True)
        model = SentenceTransformer(args.model, device="cpu")
        texts = [t for _, t in articles]
        print(f"encoding batch={args.batch}…", flush=True)
        X = model.encode(
            texts,
            batch_size=args.batch,
            show_progress_bar=True,
            convert_to_numpy=True,
            normalize_embeddings=True,
        )
        cache.parent.mkdir(parents=True, exist_ok=True)
        np.save(cache, X)
        print(f"saved {cache} shape={X.shape}", flush=True)

    print("pca-1d + kmeans…", flush=True)
    z = pca_1d(X.astype(np.float64))
    labs = kmeans_1d(z, k=args.k)
    by = {i: [] for i in range(args.k)}
    for (page_index, _), lab in zip(articles, labs):
        by[int(lab)].append(page_index)
    order_pages = []
    for c in range(args.k):
        order_pages.extend(sorted(by[c]))

    pages_path = Path(str(args.out) + "_pages")
    pages_path.write_text("\n".join(map(str, order_pages)) + "\n")
    print(f"wrote {pages_path}")

    remap_bin = Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix/run/remap")
    outp = Path(args.out)
    if not remap_bin.is_file():
        print("missing remap", file=sys.stderr)
        return 1
    import subprocess

    with outp.open("w") as fout:
        subprocess.check_call([str(remap_bin), str(pages_path), args.enwik9], stdout=fout)
    print(f"remapped {outp} bytes={outp.stat().st_size}")

    baseline = Path(
        "/srv/http/fractal_zip/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order"
    )
    if baseline.exists():
        b = [int(x) for x in baseline.read_text().splitlines() if x.strip()]
        n = [int(x) for x in outp.read_text().splitlines() if x.strip()]
        pos_b = {w: i for i, w in enumerate(b)}
        shared = [w for w in n if w in pos_b]
        if shared:
            xb = np.array([pos_b[w] for w in shared], dtype=np.float64)
            xn = np.arange(len(shared), dtype=np.float64)
            xb = (xb - xb.mean()) / (xb.std() + 1e-12)
            xn = (xn - xn.mean()) / (xn.std() + 1e-12)
            # shared in n-order
            xb = np.array([pos_b[w] for w in shared], dtype=np.float64)
            xn = np.array([i for i, w in enumerate(n) if w in pos_b], dtype=np.float64)
            xb = (xb - xb.mean()) / (xb.std() + 1e-12)
            xn = (xn - xn.mean()) / (xn.std() + 1e-12)
            print(
                f"spearman≈{float(np.dot(xb, xn)/len(xb)):.4f} "
                f"S1 Δ={outp.stat().st_size - baseline.stat().st_size:+d}"
            )
    print(f"DONE in {time.time()-t0:.1f}s")
    return 0


if __name__ == "__main__":
    sys.exit(main())
