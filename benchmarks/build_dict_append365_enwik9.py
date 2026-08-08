#!/usr/bin/env python3
"""Same as build_dict_append365.py but counts words on enwik9 (the prize
target). Streams in 64MB chunks to keep memory modest. Writes a second
candidate dict + an overlap report vs the enwik8-derived one."""
import collections
import re
import os
import sys

ROOT = "/srv/http/fractal_zip"
ENWIK9 = f"{ROOT}/tools/hutter/data/enwik9"
DIC = f"{ROOT}/tools/hutter/fx2-cmix/dictionary/english.dic"
OUT = f"{ROOT}/benchmarks/.ladder_cache/dicts/english_append365_e9.dic"
OUT8 = f"{ROOT}/benchmarks/.ladder_cache/dicts/english_append365.dic"
CAP = 44880

existing, seen = [], set()
with open(DIC, "rb") as f:
    for tok in re.split(rb"[^a-z]+", f.read()):
        if tok and tok not in seen:
            seen.add(tok)
            existing.append(tok)
free = CAP - len(existing)

counts = collections.Counter()
word_re = re.compile(rb"[a-zA-Z]+")
CHUNK = 64 * 1024 * 1024
with open(ENWIK9, "rb") as f:
    carry = b""
    while True:
        block = f.read(CHUNK)
        if not block:
            break
        buf = carry + block
        # keep a possible split word for next chunk
        m = re.search(rb"[a-zA-Z]+\Z", buf)
        carry = m.group(0) if m and len(m.group(0)) < 64 else b""
        if carry:
            buf = buf[: len(buf) - len(carry)]
        for tok in word_re.findall(buf):
            t = tok.lower()
            if len(t) >= 4:
                counts[t] += 1
    if len(carry) >= 4:
        counts[carry.lower()] += 1

cands = []
for w, c in counts.items():
    if w in seen:
        continue
    cands.append((c * (len(w) - 3), c, w))
cands.sort(reverse=True)
chosen = [w for _, _, w in cands[:free]]

with open(OUT, "wb") as f:
    for w in existing + chosen:
        f.write(w + b"\n")

# Overlap vs enwik8-derived candidate
e8 = set()
with open(OUT8, "rb") as f:
    lines = [l.strip() for l in f if l.strip()]
    e8 = set(lines[len(existing):])
ov = len(e8 & set(chosen))
print(f"free={free} chosen={len(chosen)} overlap_with_e8_set={ov}/{len(e8)}")
print("top15_e9:", [(w.decode(), c) for _, c, w in cands[:15]])
print(f"wrote {OUT}")
