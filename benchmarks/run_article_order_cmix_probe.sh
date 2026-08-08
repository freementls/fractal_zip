#!/usr/bin/env bash
# Validate local article order vs published on an enwik9 remapped prefix via cmix -c.
# Order only affects S2 on the -e wiki path; this is a directional cmix screen on
# manually reordered page blobs (same content, different order).
set -euo pipefail
ROOT=/srv/http/fractal_zip
ENWIK9=${ENWIK9:-$ROOT/tools/hutter/data/enwik9}
PUB=$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order
# Use page-index file for local; for published we need inverse of remap.
# Simpler: build both prefixes from page-index orders.
LOCAL_PAGES=$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_local_pages
OUTDIR=$ROOT/benchmarks/.ladder_cache/article_order_probe
CMIX=${CMIX:-$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_entity}
DIC=${DIC:-$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic}
HEAD=${HEAD:-1048576}
mkdir -p "$OUTDIR"
LOG=$ROOT/benchmarks/.hutter_logs/article_order_cmix_probe.log

python3 - <<PY
from pathlib import Path
enwik = Path("$ENWIK9").read_bytes()
parts = enwik.split(b"<page>")
preamble = parts[0]
local = [int(x) for x in Path("$LOCAL_PAGES").read_text().splitlines() if x.strip()]
# Approximate published page order: invert remap by reading published remapped
# IDs is hard; use natural order as baseline and local as candidate.
# Also build a "published-like" order by sorting published compressed IDs through
# a python remap inverse if possible.
head = int("$HEAD")

def build(order_pages, path):
    out = bytearray(preamble)
    for idx in order_pages:
        if 0 <= idx < len(parts)-1:
            out.extend(b"<page>")
            out.extend(parts[idx+1])
        if len(out) >= head:
            break
    Path(path).write_bytes(bytes(out[:head]))
    return len(out[:head])

nat_order = list(range(len(parts)-1))
n1 = build(nat_order, "$OUTDIR/prefix_natural.bin")
n2 = build(local, "$OUTDIR/prefix_local.bin")
print(f"wrote natural={n1} local={n2} head={head}")
PY

{
  echo "START $(date -Is) head=$HEAD cmix=$CMIX"
  bash "$ROOT/benchmarks/hutter_memory_guard.sh"
  echo "=== natural ==="
  /usr/bin/time -f 'wall=%e rss=%M' "$CMIX" -c "$DIC" "$OUTDIR/prefix_natural.bin" "$OUTDIR/prefix_natural.fx2" \
    >/dev/null 2>"$OUTDIR/prefix_natural.time"
  echo "bytes=$(stat -c%s "$OUTDIR/prefix_natural.fx2") $(rg -o 'wall=[0-9.]+ rss=[0-9]+' "$OUTDIR/prefix_natural.time"|tail -1)"
  echo "=== local_order ==="
  /usr/bin/time -f 'wall=%e rss=%M' "$CMIX" -c "$DIC" "$OUTDIR/prefix_local.bin" "$OUTDIR/prefix_local.fx2" \
    >/dev/null 2>"$OUTDIR/prefix_local.time"
  echo "bytes=$(stat -c%s "$OUTDIR/prefix_local.fx2") $(rg -o 'wall=[0-9.]+ rss=[0-9]+' "$OUTDIR/prefix_local.time"|tail -1)"
  python3 - <<PY
bn=int(open("$OUTDIR/prefix_natural.fx2","rb").seek(0,2) or __import__('os').path.getsize("$OUTDIR/prefix_natural.fx2"))
bl=int(__import__('os').path.getsize("$OUTDIR/prefix_local.fx2"))
bn=__import__('os').path.getsize("$OUTDIR/prefix_natural.fx2")
print(f"Δlocal_vs_natural={bl-bn:+d} gate={'KEEP' if bl<bn else 'reject'}")
PY
  echo "DONE $(date -Is)"
} | tee "$LOG"
