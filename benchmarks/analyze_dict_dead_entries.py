#!/usr/bin/env python3
"""Coverage analysis of english.dic vs enwik9 word counts.

Code zones (dictionary.cpp): slot 0-79 -> 1-byte code, 80-3919 -> 2-byte,
3920-44879 -> 3-byte. Reports dead/low-count entries per zone and estimates
the upside of replacing dead slots in-place with top missing words.
Counts are cached to benchmarks/.ladder_cache/enwik9_word_counts.tsv.
"""
import collections
import os
import re
import sys

ROOT = "/srv/http/fractal_zip"
ENWIK9 = f"{ROOT}/tools/hutter/data/enwik9"
DIC = f"{ROOT}/tools/hutter/fx2-cmix/dictionary/english.dic"
CACHE = f"{ROOT}/benchmarks/.ladder_cache/enwik9_word_counts.tsv"

def load_counts():
    if os.path.exists(CACHE):
        counts = {}
        with open(CACHE, "rb") as f:
            for line in f:
                w, c = line.rsplit(b"\t", 1)
                counts[w] = int(c)
        return counts
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
            m = re.search(rb"[a-zA-Z]+\Z", buf)
            carry = m.group(0) if m and len(m.group(0)) < 64 else b""
            if carry:
                buf = buf[: len(buf) - len(carry)]
            for tok in word_re.findall(buf):
                counts[tok.lower()] += 1
        if carry:
            counts[carry.lower()] += 1
    with open(CACHE, "wb") as f:
        for w, c in counts.items():
            f.write(w + b"\t" + str(c).encode() + b"\n")
    return counts

counts = load_counts()
print(f"distinct words in enwik9: {len(counts)}")

entries = []
seen = set()
with open(DIC, "rb") as f:
    for tok in re.split(rb"[^a-z]+", f.read()):
        if tok and tok not in seen:
            seen.add(tok)
            entries.append(tok)

def zone(i):
    if i < 80: return 1
    if i < 3920: return 2
    return 3

stats = {1: [], 2: [], 3: []}
for i, w in enumerate(entries):
    stats[zone(i)].append((counts.get(w, 0), i, w))

for z in (1, 2, 3):
    zc = stats[z]
    dead = [x for x in zc if x[0] == 0]
    low = [x for x in zc if 0 < x[0] <= 10]
    print(f"zone{z} ({len(zc)} entries, {z}-byte codes): dead={len(dead)} low(<=10)={len(low)}")
    if dead[:10]:
        print(f"  dead sample: {[w.decode() for _,_,w in sorted(dead, key=lambda x:x[1])[:10]]}")

# Upside estimate: dead slots hosting top missing words
missing = sorted(
    ((c * (len(w) - z_cost), c, w) for w, c in counts.items()
     if w not in seen and len(w) >= 2
     for z_cost in (1,)),  # placeholder, recomputed per zone below
    reverse=True)

# Recompute properly per zone: benefit = count * (len(word) - zone_bytes)
def top_missing(zone_bytes, k, exclude):
    cands = []
    for w, c in counts.items():
        if w in seen or w in exclude or len(w) <= zone_bytes:
            continue
        cands.append((c * (len(w) - zone_bytes), c, w))
    cands.sort(reverse=True)
    return cands[:k]

used = set()
total_gain = 0
for z in (1, 2, 3):
    dead_n = len([x for x in stats[z] if x[0] == 0])
    if not dead_n:
        continue
    tm = top_missing(z, dead_n, used)
    gain = sum(g for g, _, _ in tm)
    used |= {w for _, _, w in tm}
    total_gain += gain
    print(f"zone{z}: {dead_n} dead slots; replacement upside ~{gain:,} pre-entropy bytes on enwik9")
    print(f"  best: {[(w.decode(), c) for _, c, w in tm[:8]]}")

print(f"TOTAL pre-entropy upside ~{total_gain:,} bytes on enwik9 "
      f"(entropy-coded gain will be a fraction of this)")
