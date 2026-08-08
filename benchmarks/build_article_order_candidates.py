#!/usr/bin/env python3
"""Build candidate article orders and score with zstd-long proxy.

Produces remapped new_article_order files (non-redirect indices, same format
as published). Candidates:
  - published (baseline)
  - title_prefix: sort non-redirects by normalized title, then length
  - length_desc: longest first (stress test)
  - mix: title first-char buckets, within-bucket by length

Usage: python3 benchmarks/build_article_order_candidates.py [--score]
"""
from __future__ import annotations

import argparse
import os
import random
import re
import subprocess
import sys

ROOT = "/srv/http/fractal_zip"
ENWIK9 = f"{ROOT}/tools/hutter/data/enwik9"
PUB_ORDER = f"{ROOT}/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order"
OUT_DIR = f"{ROOT}/benchmarks/.ladder_cache/article_orders"
LOG = f"{ROOT}/benchmarks/.hutter_logs/article_order_candidates.log"

REDIRECT_PREFIXES = (
    b"      <text xml:space=\"preserve\">#REDIRECT",
    b"      <text xml:space=\"preserve\">#redirect",
    b"      <text xml:space=\"preserve\">#Redirect",
    b"      <text xml:space=\"preserve\">#REdirect",
    b"      <text xml:space=\"preserve\">{{softredirect",
)
TITLE_RE = re.compile(rb"<title>(.*?)</title>", re.DOTALL)


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
        m = TITLE_RE.search(body)
        title = m.group(1).decode("utf-8", "replace") if m else ""
        spans.append({"start": s, "end": e, "redirect": is_redir, "title": title, "len": e - s})
    return head, spans


def build_remap(spans):
    """remap[non_redirect_index] = raw_article_index  (article_reorder.h semantics)"""
    remap = {}
    count2 = 0
    for count1, sp in enumerate(spans):
        if not sp["redirect"]:
            remap[count2] = count1
            count2 += 1
    return remap, count2


def write_order(path: str, non_redir_ids: list[int]) -> None:
    with open(path, "w") as f:
        for i in non_redir_ids:
            f.write(f"{i}\n")


def emit_reordered(path: str, head: bytes, spans, positions: list[int], data: bytes) -> None:
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
    ap.add_argument("--limit-mb", type=int, default=0, help="if >0, only first N MB (debug)")
    args = ap.parse_args()
    os.makedirs(OUT_DIR, exist_ok=True)
    open(LOG, "w").write("")

    log(f"reading {ENWIK9}")
    data = open(ENWIK9, "rb").read()
    if args.limit_mb:
        data = data[: args.limit_mb * 1024 * 1024]
    head, spans = parse_articles(data)
    remap, n_non = build_remap(spans)
    log(f"articles={len(spans)} non_redirect={n_non}")

    # non-redirect list with features
    non = []
    for nr_id in range(n_non):
        raw = remap[nr_id]
        sp = spans[raw]
        t = sp["title"].casefold()
        non.append({"nr": nr_id, "title": t, "len": sp["len"], "raw": raw})

    candidates = {}

    # published (truncated to available if limit)
    pub = [int(l) for l in open(PUB_ORDER) if l.strip()]
    pub = [x for x in pub if x < n_non]
    candidates["published"] = pub

    # title_prefix: sort by title then length
    s = sorted(non, key=lambda x: (x["title"], -x["len"]))
    candidates["title_prefix"] = [x["nr"] for x in s]

    # length_desc
    s = sorted(non, key=lambda x: (-x["len"], x["title"]))
    candidates["length_desc"] = [x["nr"] for x in s]

    # first-char buckets (a-z / other), within by title
    def bucket(t: str) -> str:
        if t and "a" <= t[0] <= "z":
            return t[0]
        return "~"

    s = sorted(non, key=lambda x: (bucket(x["title"]), x["title"], -x["len"]))
    candidates["title_bucket"] = [x["nr"] for x in s]

    # random control
    rnd = [x["nr"] for x in non]
    random.Random(42).shuffle(rnd)
    candidates["random"] = rnd

    for name, ids in candidates.items():
        path = f"{OUT_DIR}/{name}.order"
        write_order(path, ids)
        log(f"wrote {path} ({len(ids)} entries)")

    if not args.score:
        log("done (no --score)")
        return

    results = {}
    for name, ids in candidates.items():
        positions = positions_from_order(ids, remap, len(spans))
        tmp = f"{OUT_DIR}/_{name}.tmp"
        emit_reordered(tmp, head, spans, positions, data)
        zs = zsize(tmp)
        os.remove(tmp)
        results[name] = zs
        log(f"SCORE {name}: zstd12L30={zs:,}")

    base = results.get("published")
    if base:
        for name, zs in sorted(results.items(), key=lambda x: x[1]):
            log(f"  {name:16s} {zs:,}  Δpub={zs - base:+,}")


if __name__ == "__main__":
    main()
