#!/usr/bin/env python3
"""Build Profile B4 dictionary: post B0+POSTLT_FOLD stream counts."""
from __future__ import annotations

import collections
import os
import re
import sys

ROOT = "/srv/http/fractal_zip"
ENWIK9 = f"{ROOT}/tools/hutter/data/enwik9"
DIC = f"{ROOT}/tools/hutter/fx2-cmix/dictionary/english.dic"
OUT_DIR = f"{ROOT}/benchmarks/.ladder_cache/dicts"
CACHE = f"{ROOT}/benchmarks/.ladder_cache/enwik9_postlt_word_counts.tsv"
OUT_APPEND = f"{OUT_DIR}/english_postltfold_e9.dic"
CAP = 44880
FOLD = [
    (b"&lt;!--", bytes([0x0E])),
    (b"--&gt;", bytes([0x0F])),
    (b"&lt;/ref", bytes([8])),
    (b"&lt;ref", bytes([9])),
    (b"&lt;br", bytes([0x0B])),
    (b"/&gt;", bytes([0x10])),
    (b"&quot;", bytes([1])),
    (b"&lt;", bytes([2])),
    (b"&gt;", bytes([3])),
    (b"&amp;", bytes([4])),
]
ENTITY_ONLY_BLOCKLIST = {b"quot"}


def fold_bytes(data: bytes) -> bytes:
    for pat, code in FOLD:
        data = data.replace(pat, code)
    return data


def load_stock():
    entries, seen = [], set()
    with open(DIC, "rb") as f:
        for tok in re.split(rb"[^a-z]+", f.read()):
            if tok and tok not in seen:
                seen.add(tok)
                entries.append(tok)
    return entries, seen


def count_postfold(path: str) -> dict:
    if os.path.exists(CACHE):
        counts = {}
        with open(CACHE, "rb") as f:
            for line in f:
                w, c = line.rsplit(b"\t", 1)
                counts[w] = int(c)
        print(f"loaded cache {CACHE} ({len(counts)} words)", file=sys.stderr)
        return counts
    counts: collections.Counter = collections.Counter()
    word_re = re.compile(rb"[a-zA-Z]+")
    CHUNK = 64 * 1024 * 1024
    with open(path, "rb") as f:
        carry = b""
        while True:
            block = f.read(CHUNK)
            if not block:
                break
            buf = fold_bytes(carry + block)
            m = re.search(rb"[a-zA-Z]+\Z", buf)
            carry = m.group(0) if m and len(m.group(0)) < 64 else b""
            if carry:
                buf = buf[: len(buf) - len(carry)]
            for tok in word_re.findall(buf):
                counts[tok.lower()] += 1
        if len(carry) >= 2:
            counts[carry.lower()] += 1
    with open(CACHE, "wb") as f:
        for w, c in counts.items():
            f.write(w + b"\t" + str(c).encode() + b"\n")
    print(f"wrote cache {CACHE}", file=sys.stderr)
    return counts


def main() -> None:
    os.makedirs(OUT_DIR, exist_ok=True)
    entries, seen = load_stock()
    free = CAP - len(entries)
    print(f"stock={len(entries)} free={free}", file=sys.stderr)
    counts = count_postfold(ENWIK9)
    for w in (b"quot", b"title", b"page", b"revision", b"contributor"):
        print(f"  postlt count[{w.decode()}]={counts.get(w, 0):,}", file=sys.stderr)
    cands = []
    for w, c in counts.items():
        if w in seen or w in ENTITY_ONLY_BLOCKLIST or len(w) < 4:
            continue
        cands.append((c * (len(w) - 3), c, w))
    cands.sort(reverse=True)
    chosen = [w for _, _, w in cands[:free]]
    print("top10:", [(w.decode(), c) for _, c, w in cands[:10]], file=sys.stderr)
    append = entries + chosen
    with open(OUT_APPEND, "wb") as f:
        for w in append:
            f.write(w + b"\n")
    print(f"wrote {OUT_APPEND} ({len(append)} lines)", file=sys.stderr)


if __name__ == "__main__":
    main()
