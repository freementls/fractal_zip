#!/usr/bin/env python3
"""Append up to 365 high-value missing words to english.dic (fills code space
to the 44,880-entry cap without touching existing entry order/codes).

Value model per word: count * (len - 3)  [word costs len bytes spelled out vs
~3 bytes as a late dict entry], len >= 4, lowercase a-z only, must appear in
enwik8 tokenization the same way Dictionary::Encode splits words.
"""
import collections
import re
import sys

ROOT = "/srv/http/fractal_zip"
ENWIK8 = f"{ROOT}/enwik8"
DIC = f"{ROOT}/tools/hutter/fx2-cmix/dictionary/english.dic"
OUT = f"{ROOT}/benchmarks/.ladder_cache/dicts/english_append365.dic"
CAP = 44880  # kBoundary3 = 80 + 3840 + 40960

existing = []
seen = set()
with open(DIC, "rb") as f:
    for tok in re.split(rb"[^a-z]+", f.read()):
        if tok and tok not in seen:
            seen.add(tok)
            existing.append(tok)
print(f"existing entries: {len(existing)}", file=sys.stderr)
free = CAP - len(existing)
print(f"free slots: {free}", file=sys.stderr)

counts = collections.Counter()
with open(ENWIK8, "rb") as f:
    data = f.read()
# Match Dictionary::Encode: runs of lowercase (case-folded); we approximate
# with case-insensitive word runs, folded to lowercase.
for tok in re.findall(rb"[a-zA-Z]+", data):
    t = tok.lower()
    if len(t) >= 4:
        counts[t] += 1

cands = []
for w, c in counts.items():
    if w in seen:
        continue
    gain = c * (len(w) - 3)
    # subtract rough S1 cost of carrying the entry (compressed ~2.5B) - negligible
    cands.append((gain, c, w))
cands.sort(reverse=True)

chosen = [w for _, _, w in cands[:free]]
print("top10:", [(w.decode(), c) for g, c, w in cands[:10]], file=sys.stderr)
print(f"chosen: {len(chosen)}, min count in set: {cands[len(chosen)-1][1] if chosen else 0}",
      file=sys.stderr)

import os
os.makedirs(os.path.dirname(OUT), exist_ok=True)
with open(OUT, "wb") as f:
    for w in existing:
        f.write(w + b"\n")
    for w in chosen:
        f.write(w + b"\n")
print(f"wrote {OUT}", file=sys.stderr)
