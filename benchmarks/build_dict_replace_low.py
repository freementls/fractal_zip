#!/usr/bin/env python3
"""Build dict variant: replace low-value zone-3 entries in-place with
high-frequency missing enwik9 words, PRESERVING substring-helper entries.

EncodeSubstring uses byte_map lookups of suffixes/prefixes >=7 chars of words
>7 chars. So an entry can matter beyond exact hits iff len(entry)>=7 and it is
a suffix or prefix of longer corpus words. We credit each entry with that
substring traffic before declaring it replaceable.

Output: benchmarks/.ladder_cache/dicts/english_replace_low_e9.dic
        (stacked on top of the append365_e9 fill)
"""
import re
import sys

ROOT = "/srv/http/fractal_zip"
DIC = f"{ROOT}/tools/hutter/fx2-cmix/dictionary/english.dic"
CACHE = f"{ROOT}/benchmarks/.ladder_cache/enwik9_word_counts.tsv"
APPEND_E9 = f"{ROOT}/benchmarks/.ladder_cache/dicts/english_append365_e9.dic"
OUT = f"{ROOT}/benchmarks/.ladder_cache/dicts/english_replace_low_e9.dic"
LOW_MAX = 10          # exact-hit threshold
SUB_MAX = 200         # substring-traffic threshold (occurrences via longer words)

counts = {}
with open(CACHE, "rb") as f:
    for line in f:
        w, c = line.rsplit(b"\t", 1)
        counts[w] = int(c)

entries, seen = [], set()
with open(DIC, "rb") as f:
    for tok in re.split(rb"[^a-z]+", f.read()):
        if tok and tok not in seen:
            seen.add(tok)
            entries.append(tok)

longest = max(len(w) for w in entries)

# Substring traffic: for every corpus word >7 chars, credit dict entries that
# are prefixes/suffixes >=7 chars (as EncodeSubstring would find).
sub_traffic = {w: 0 for w in entries if len(w) >= 7}
entry_set = set(sub_traffic)
for w, c in counts.items():
    lw = len(w)
    if lw <= 7:
        continue
    max_k = min(lw - 1, longest)
    for k in range(7, max_k + 1):
        suf = w[lw - k:]
        if suf in entry_set and suf != w:
            sub_traffic[suf] += c
        pre = w[:k]
        if pre in entry_set and pre != w:
            sub_traffic[pre] += c

replaceable = []
for i, w in enumerate(entries):
    if i < 3920:
        continue  # never touch 1/2-byte zones
    exact = counts.get(w, 0)
    sub = sub_traffic.get(w, 0)
    if exact <= LOW_MAX and sub <= SUB_MAX:
        replaceable.append((exact + sub, i, w))
replaceable.sort()
print(f"replaceable zone3 slots (exact<={LOW_MAX}, substr<={SUB_MAX}): {len(replaceable)}")
print("sample:", [(w.decode(), v) for v, _, w in replaceable[:12]])

# Candidate missing words: skip ones already used by append365_e9.
with open(APPEND_E9, "rb") as f:
    append_lines = [l.strip() for l in f if l.strip()]
used = set(append_lines)  # includes original entries + appended 365

cands = []
for w, c in counts.items():
    if w in used or len(w) < 4:
        continue
    cands.append((c * (len(w) - 3), c, w))
cands.sort(reverse=True)
picks = cands[: len(replaceable)]
print("replacement picks top10:", [(w.decode(), c) for _, c, w in picks[:10]])
print(f"pre-entropy upside from replacements ~{sum(g for g,_,_ in picks):,} bytes")
print(f"pre-entropy loss from removed entries ~{sum(v for v,_,_ in replaceable[:len(picks)]):,} bytes")

new_entries = list(append_lines)  # start from append365_e9 (stacked variant)
for (v, idx, old), (_, _, new) in zip(replaceable, picks):
    assert new_entries[idx] == old
    new_entries[idx] = new

with open(OUT, "wb") as f:
    for w in new_entries:
        f.write(w + b"\n")
print(f"wrote {OUT} ({len(new_entries)} lines)")
