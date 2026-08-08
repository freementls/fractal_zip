#!/usr/bin/env python3
"""TF-IDF (pure numpy) article order — better local substitute than char-hash.

No sklearn/voyage required. Same page-index → remap pipeline as
rebuild_article_order_local.py.
"""
from __future__ import annotations

import argparse
import math
import re
import sys
import time
from collections import Counter
from pathlib import Path

import numpy as np

REDIRECT_PREFIXES = (
    b"#REDIRECT",
    b"#redirect",
    b"#Redirect",
    b"#REdirect",
    b"{{softredirect",
)
TOKEN_RE = re.compile(r"[a-z0-9]{2,}")


def parse_articles(path: Path, truncate: int = 2000):
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
        content = body[:truncate].decode("utf-8", "replace").lower()
        content = re.sub(r"\{\{[^}]*\}\}", " ", content)
        text = (title + "\n" + content).lower()
        toks = TOKEN_RE.findall(text)
        out.append((page_index, toks))
    return out


def build_tfidf_matrix(articles, vocab_size: int = 4096):
    # Global DF from sample if huge — use all for correctness at 243k (ok).
    df = Counter()
    for _pi, toks in articles:
        df.update(set(toks))
    vocab = [t for t, _ in df.most_common(vocab_size)]
    vindex = {t: i for i, t in enumerate(vocab)}
    n = len(articles)
    idf = np.zeros(vocab_size, dtype=np.float32)
    for t, i in vindex.items():
        idf[i] = math.log((1 + n) / (1 + df[t])) + 1.0
    X = np.zeros((n, vocab_size), dtype=np.float32)
    for row, (_pi, toks) in enumerate(articles):
        if not toks:
            continue
        tf = Counter(toks)
        for t, c in tf.items():
            j = vindex.get(t)
            if j is None:
                continue
            X[row, j] = (c / len(toks)) * idf[j]
        nrm = float(np.linalg.norm(X[row]))
        if nrm > 0:
            X[row] /= nrm
    return X


def pca_1d(X: np.ndarray) -> np.ndarray:
    Xc = X - X.mean(axis=0, keepdims=True)
    rng = np.random.default_rng(923)
    w = rng.standard_normal(Xc.shape[1]).astype(np.float64)
    w /= np.linalg.norm(w) + 1e-12
    for _ in range(50):
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
        default="/srv/http/fractal_zip/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_tfidf",
    )
    ap.add_argument("--k", type=int, default=64)
    ap.add_argument("--vocab", type=int, default=4096)
    ap.add_argument("--truncate", type=int, default=2000)
    args = ap.parse_args()

    t0 = time.time()
    articles = parse_articles(Path(args.enwik9), truncate=args.truncate)
    print(f"parsed={len(articles)} in {time.time()-t0:.1f}s", flush=True)
    print("tfidf…", flush=True)
    X = build_tfidf_matrix(articles, vocab_size=args.vocab)
    print("pca-1d…", flush=True)
    z = pca_1d(X)
    labs = kmeans_1d(z, k=args.k)
    by = {i: [] for i in range(args.k)}
    for (page_index, _), lab in zip(articles, labs):
        by[int(lab)].append(page_index)
    order_pages = []
    for c in range(args.k):
        order_pages.extend(sorted(by[c]))

    pages_path = Path(str(args.out) + "_pages")
    pages_path.write_text("\n".join(map(str, order_pages)) + "\n")
    print(f"wrote {pages_path} n={len(order_pages)}")

    remap_bin = Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix/run/remap")
    outp = Path(args.out)
    if remap_bin.is_file():
        import subprocess

        with outp.open("w") as fout:
            subprocess.check_call([str(remap_bin), str(pages_path), args.enwik9], stdout=fout)
        print(f"remapped {outp} bytes={outp.stat().st_size}")
    else:
        print("WARN: no remap binary; wrote pages only", file=sys.stderr)
        return 1

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
            xn = np.array([i for i, w in enumerate(n) if w in pos_b], dtype=np.float64)
            # align lengths
            xb = xb[: len(xn)]
            xb = (xb - xb.mean()) / (xb.std() + 1e-12)
            xn = (xn - xn.mean()) / (xn.std() + 1e-12)
            print(
                f"spearman≈{float(np.dot(xb, xn)/len(xb)):.4f} "
                f"S1_pub={baseline.stat().st_size} S1_tfidf={outp.stat().st_size} "
                f"Δ={outp.stat().st_size - baseline.stat().st_size:+d}"
            )
    print(f"DONE in {time.time()-t0:.1f}s")
    return 0


if __name__ == "__main__":
    sys.exit(main())
