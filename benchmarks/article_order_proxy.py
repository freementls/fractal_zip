#!/usr/bin/env python3
"""Article-order sensitivity proxy (no cmix needed).

Replicates article_reorder.h semantics: enwik9 split at '  <page>' lines,
non-redirect renumbering (article_remap), new_article_order applied, unused
articles appended in natural order. Emits reordered streams and compares
zstd --long compressed sizes: natural vs published order vs random control.
"""
import random
import subprocess
import sys

ROOT = "/srv/http/fractal_zip"
ENWIK9 = f"{ROOT}/tools/hutter/data/enwik9"
ORDER = f"{ROOT}/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order"
OUTDIR = f"{ROOT}/benchmarks/.hutter_logs"

data = open(ENWIK9, "rb").read()
print(f"read enwik9: {len(data):,}")

# Article spans: from each '  <page>\n' line to just before the next one.
marker = b"  <page>\n"
starts = []
pos = 0
if data.startswith(marker):
    starts.append(0)
while True:
    pos = data.find(b"\n  <page>\n", pos)
    if pos == -1:
        break
    starts.append(pos + 1)
    pos += 1
print(f"articles found: {len(starts):,}")
head = data[: starts[0]]
spans = [(s, starts[i + 1] if i + 1 < len(starts) else len(data))
         for i, s in enumerate(starts)]
# Trailer (</mediawiki>) stays glued to the last article span; head kept up front.

# Redirect detection per article (same prefixes as article_remap.cpp).
prefixes = (b"      <text xml:space=\"preserve\">#REDIRECT",
            b"      <text xml:space=\"preserve\">#redirect",
            b"      <text xml:space=\"preserve\">#Redirect",
            b"      <text xml:space=\"preserve\">#REdirect",
            b"      <text xml:space=\"preserve\">{{softredirect")
# remap[count2] = count1 semantics from article_reorder.h reorder()
remap = {}
count2 = 0
for count1, (s, e) in enumerate(spans):
    body = data[s:e]
    is_redirect = any((b"\n" + p) in body or body.startswith(p) for p in prefixes)
    remap[count2 - 0] = count1  # placeholder; fixed below
    if not is_redirect:
        remap[count2] = count1
        count2 += 1
print(f"non-redirect articles: {count2:,}")

order = [int(l) for l in open(ORDER)]
positions = []
used = [0] * len(spans)
for o in order:
    r = remap[o]
    positions.append(r)
    used[r] = 1
for i in range(len(spans)):
    if not used[i]:
        positions.append(i)
print(f"order entries: {len(order):,}; total positions: {len(positions):,}")

def emit(path, poss):
    with open(path, "wb") as f:
        f.write(head)
        for p in poss:
            s, e = spans[p]
            f.write(data[s:e])

def zsize(path):
    out = path + ".zst"
    subprocess.run(["zstd", "-12", "--long=30", "-T8", "-f", "-q", path, "-o", out],
                   check=True)
    n = int(subprocess.run(["stat", "-c%s", out], capture_output=True,
                           text=True).stdout)
    subprocess.run(["rm", "-f", out])
    return n

mode = sys.argv[1] if len(sys.argv) > 1 else "all"
results = {}
if mode in ("all", "natural"):
    results["natural"] = zsize(ENWIK9)
    print(f"natural zstd12L30: {results['natural']:,}")
if mode in ("all", "published"):
    p = f"{OUTDIR}/enwik9_reordered_published.tmp"
    emit(p, positions)
    results["published"] = zsize(p)
    subprocess.run(["rm", "-f", p])
    print(f"published-order zstd12L30: {results['published']:,}")
if mode in ("all", "random"):
    rnd = list(range(len(spans)))
    random.Random(42).shuffle(rnd)
    p = f"{OUTDIR}/enwik9_reordered_random.tmp"
    emit(p, rnd)
    results["random"] = zsize(p)
    subprocess.run(["rm", "-f", p])
    print(f"random-order zstd12L30: {results['random']:,}")

if len(results) > 1 and "natural" in results:
    for k, v in results.items():
        if k != "natural":
            print(f"{k} vs natural: {v - results['natural']:+,} bytes")
