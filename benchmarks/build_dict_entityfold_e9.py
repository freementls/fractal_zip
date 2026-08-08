#!/usr/bin/env python3
"""Build Profile B dictionaries: counted on the post-entity-fold stream.

Matches FXCM_ENTITY_FOLD: &quot; &lt; &gt; &amp; → 0x01..0x04 before dict tokenization.
Starts from stock english.dic order (codes for existing entries stable), fills the
365 free slots with highest-value *post-fold* missing words, and never spends a
slot on entity-only bodies (quot is the main case).
"""
from __future__ import annotations

import collections
import os
import re
import sys

ROOT = "/srv/http/fractal_zip"
ENWIK9 = f"{ROOT}/tools/hutter/data/enwik9"
DIC = f"{ROOT}/tools/hutter/fx2-cmix/dictionary/english.dic"
OUT_DIR = f"{ROOT}/benchmarks/.ladder_cache/dicts"
CACHE = f"{ROOT}/benchmarks/.ladder_cache/enwik9_postfold_word_counts.tsv"
OUT_APPEND = f"{OUT_DIR}/english_entityfold_e9.dic"
OUT_REPLACE = f"{OUT_DIR}/english_entityfold_replace_e9.dic"
CAP = 44880
FOLD = [
    (b"&quot;", bytes([1])),
    (b"&lt;", bytes([2])),
    (b"&gt;", bytes([3])),
    (b"&amp;", bytes([4])),
]
# Words that are primarily entity bodies; never append as new slots under Profile B.
ENTITY_ONLY_BLOCKLIST = {b"quot"}  # amp/lt/gt may still appear as prose


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
            # fold within chunk; entities rarely straddle 64MB boundaries
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

    # Report what fold does to entity-ish words
    for w in (b"quot", b"amp", b"lt", b"gt", b"nbsp", b"ndash", b"mdash"):
        print(f"  postfold count[{w.decode()}]={counts.get(w, 0):,}", file=sys.stderr)

    cands = []
    for w, c in counts.items():
        if w in seen or w in ENTITY_ONLY_BLOCKLIST or len(w) < 4:
            continue
        cands.append((c * (len(w) - 3), c, w))
    cands.sort(reverse=True)
    chosen = [w for _, _, w in cands[:free]]
    print("top15 postfold append:", [(w.decode(), c) for _, c, w in cands[:15]], file=sys.stderr)
    if b"quot" in chosen:
        raise SystemExit("BUG: quot leaked into append despite blocklist")

    append = entries + chosen
    with open(OUT_APPEND, "wb") as f:
        for w in append:
            f.write(w + b"\n")
    print(f"wrote {OUT_APPEND} ({len(append)} lines)", file=sys.stderr)

    # replace_low on post-fold: zone3 only, exact+substring traffic
    longest = max(len(w) for w in append)
    sub = {w: 0 for w in append if len(w) >= 7}
    es = set(sub)
    for w, c in counts.items():
        lw = len(w)
        if lw <= 7:
            continue
        mk = min(lw - 1, longest)
        for k in range(7, mk + 1):
            s = w[lw - k :]
            if s in es and s != w:
                sub[s] += c
            p = w[:k]
            if p in es and p != w:
                sub[p] += c

    LOW, SUBMAX = 10, 200
    replaceable = []
    for i, w in enumerate(append):
        if i < 3920:
            continue
        exact = counts.get(w, 0)
        st = sub.get(w, 0)
        if exact <= LOW and st <= SUBMAX:
            replaceable.append((exact + st, i, w))
    replaceable.sort()

    used = set(append)
    picks = []
    for g, c, w in cands:
        if w in used or w in ENTITY_ONLY_BLOCKLIST:
            continue
        picks.append((g, c, w))
        if len(picks) >= len(replaceable):
            break

    newe = list(append)
    for (_, idx, old), (_, _, new) in zip(replaceable, picks):
        assert newe[idx] == old
        newe[idx] = new
        used.add(new)

    with open(OUT_REPLACE, "wb") as f:
        for w in newe:
            f.write(w + b"\n")
    print(
        f"replaceable={len(replaceable)} wrote {OUT_REPLACE} ({len(newe)} lines)",
        file=sys.stderr,
    )


if __name__ == "__main__":
    main()
