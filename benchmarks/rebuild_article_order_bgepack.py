#!/usr/bin/env python3
"""BGE article order with voyage-like text packing into model token budget.

BGE-small max_seq_length=512 — packing title + categories + infobox + lead
into ~2000 chars beats naive truncate=400 and avoids wasted truncate=4000.
Then PCA→t-SNE 1D (voyage pipeline) + optional k-means.
"""
from __future__ import annotations

import argparse
import os
import re
import subprocess
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
CAT_RE = re.compile(rb"\[\[Category:([^\]|#]+)", re.I)
INFOBOX_RE = re.compile(rb"\{\{\s*[Ii]nfobox[_\s]*([A-Za-z0-9_\- ]{0,40})", re.I)


def parse_packed(path: Path, char_budget: int = 2000):
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
        cats = [m.group(1).strip().decode("utf-8", "replace") for m in CAT_RE.finditer(body)]
        ib = INFOBOX_RE.search(body[:12000])
        infobox = ib.group(1).strip().decode("utf-8", "replace") if ib else ""
        # voyage-like: strip multiline templates, keep lead
        content = re.sub(br"\{\{[^}]*\n[^}]*\}\}", b"", body)
        content = content.decode("utf-8", "replace")
        content = re.sub(r"\{\{[^}]*\}\}", " ", content)
        content = re.sub(r"\s+", " ", content).strip()
        head = [title]
        if cats:
            head.append("Categories: " + "; ".join(cats[:8]))
        if infobox:
            head.append("Infobox: " + infobox)
        packed = "\n".join(head) + "\n" + content
        packed = packed[:char_budget]
        out.append((page_index, packed))
    return out


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--enwik9", default="/srv/http/fractal_zip/tools/hutter/data/enwik9")
    ap.add_argument(
        "--out",
        default="/srv/http/fractal_zip/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_bgepack",
    )
    ap.add_argument("--model", default="BAAI/bge-small-en-v1.5")
    ap.add_argument("--char-budget", type=int, default=2000)
    ap.add_argument("--batch", type=int, default=128)
    ap.add_argument(
        "--cache",
        default="/srv/http/fractal_zip/benchmarks/.ladder_cache/bge_pack_embeds.npy",
    )
    ap.add_argument("--tsne", action="store_true", help="also write t-SNE 1D order")
    ap.add_argument("--k", type=int, default=64)
    args = ap.parse_args()

    t0 = time.time()
    print("parsing packed texts…", flush=True)
    articles = parse_packed(Path(args.enwik9), char_budget=args.char_budget)
    print(f"articles={len(articles)} parse={time.time()-t0:.1f}s", flush=True)

    cache = Path(args.cache)
    X = None
    if cache.is_file() and cache.stat().st_size > 1000:
        X = np.load(cache)
        if X.shape[0] != len(articles):
            print(f"cache mismatch {X.shape[0]} != {len(articles)}; re-embed", flush=True)
            X = None
        else:
            print(f"loaded cache {cache}", flush=True)

    if X is None:
        from sentence_transformers import SentenceTransformer

        print(f"loading {args.model}…", flush=True)
        model = SentenceTransformer(args.model, device="cpu")
        print(f"max_seq_length={model.max_seq_length} batch={args.batch}", flush=True)
        texts = [t for _, t in articles]
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

    # Default: PCA-1D + k-means (same as minilm script)
    from numpy.linalg import norm

    Xc = X.astype(np.float64) - X.mean(axis=0, keepdims=True)
    rng = np.random.default_rng(923)
    w = rng.standard_normal(Xc.shape[1])
    w /= norm(w) + 1e-12
    for _ in range(60):
        w = Xc.T @ (Xc @ w)
        w /= norm(w) + 1e-12
    z = Xc @ w

    def kmeans_1d(x, k, iters=40):
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
        remap = {int(o): int(n) for n, o in enumerate(order)}
        return np.array([remap[int(l)] for l in labels], dtype=np.int32)

    labs = kmeans_1d(z, args.k)
    by = {i: [] for i in range(args.k)}
    for (page_index, _), lab in zip(articles, labs):
        by[int(lab)].append(page_index)
    order_pages = []
    for c in range(args.k):
        order_pages.extend(sorted(by[c]))

    pages_path = Path(str(args.out) + "_pages")
    pages_path.write_text("\n".join(map(str, order_pages)) + "\n")
    print(f"wrote {pages_path}", flush=True)

    if args.tsne:
        from sklearn.decomposition import PCA
        from sklearn.manifold import TSNE

        cache_1d = Path(str(args.cache).replace(".npy", "_tsne1d.npy"))
        if cache_1d.is_file() and cache_1d.stat().st_size > 1000:
            y = np.load(cache_1d)
            print(f"loaded {cache_1d}", flush=True)
        else:
            print("PCA50 + TSNE1D…", flush=True)
            Xp = PCA(n_components=50, random_state=923).fit_transform(X)
            y = TSNE(
                n_components=1,
                method="barnes_hut",
                perplexity=30,
                max_iter=2000,
                learning_rate="auto",
                init="pca",
                random_state=923,
                verbose=1,
            ).fit_transform(Xp).reshape(-1)
            np.save(cache_1d, y)
            print(f"saved {cache_1d}", flush=True)
        idx = np.argsort(y, kind="stable")
        tsne_pages = [articles[i][0] for i in idx]
        tp = Path(str(args.out) + "_tsne_pages")
        tp.write_text("\n".join(map(str, tsne_pages)) + "\n")
        print(f"wrote {tp}", flush=True)

    remap_bin = Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix/run/remap")
    outp = Path(args.out)
    if remap_bin.is_file():
        with outp.open("w") as fout:
            subprocess.check_call([str(remap_bin), str(pages_path), args.enwik9], stdout=fout)
        print(f"remapped {outp} bytes={outp.stat().st_size}", flush=True)

    print(f"DONE in {time.time()-t0:.1f}s", flush=True)
    return 0


if __name__ == "__main__":
    sys.exit(main())
