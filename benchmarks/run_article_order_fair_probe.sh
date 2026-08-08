#!/usr/bin/env bash
# Fair article-order probe: SAME page set, two orders.
# 1) Pick the first N pages that fill HEAD bytes in natural order.
# 2) Emit that set in natural order vs sorted by local global rank.
# Content-identical payloads → cmix Δ is attributable to order only.
set -euo pipefail
ROOT=/srv/http/fractal_zip
ENWIK9=${ENWIK9:-$ROOT/tools/hutter/data/enwik9}
LOCAL_PAGES=${LOCAL_PAGES:-$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_local_pages}
OUTDIR=${OUTDIR:-$ROOT/benchmarks/.ladder_cache/article_order_probe}
CMIX=${CMIX:-$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_entity}
DIC=${DIC:-$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic}
HEAD=${HEAD:-1048576}
mkdir -p "$OUTDIR"
LOG=${LOG:-$ROOT/benchmarks/.hutter_logs/article_order_fair_probe.log}

python3 - <<PY
from pathlib import Path
enwik = Path("$ENWIK9").read_bytes()
parts = enwik.split(b"<page>")
preamble = parts[0]
local = [int(x) for x in Path("$LOCAL_PAGES").read_text().splitlines() if x.strip()]
rank = {pid: i for i, pid in enumerate(local)}
head = int("$HEAD")

# Fixed set: pages that fill HEAD in natural order (same content either way).
chosen = []
size = len(preamble)
for i in range(len(parts) - 1):
    blob_len = 6 + len(parts[i + 1])  # b"<page>"
    if size + blob_len > head and chosen:
        break
    chosen.append(i)
    size += blob_len

def emit(order_pages, path):
    out = bytearray(preamble)
    for idx in order_pages:
        out.extend(b"<page>")
        out.extend(parts[idx + 1])
    # pad/truncate to exactly head for equal plain size
    if len(out) < head:
        out.extend(b"\0" * (head - len(out)))
    Path(path).write_bytes(bytes(out[:head]))

nat = chosen
loc = sorted(chosen, key=lambda p: rank.get(p, 10**9))
emit(nat, "$OUTDIR/fair_natural.bin")
emit(loc, "$OUTDIR/fair_local.bin")
print(f"chosen_pages={len(chosen)} plain={head} "
      f"spearman_proxy_moved={sum(1 for a,b in zip(nat,loc) if a!=b)}")
# Also compare against published remapped order if we can invert via pages file absence:
# published is remapped IDs — skip; natural vs local is the fair test.
PY

{
  echo "START_fair $(date -Is) head=$HEAD"
  bash "$ROOT/benchmarks/hutter_memory_guard.sh"
  echo "=== fair natural ==="
  /usr/bin/time -f 'wall=%e rss=%M' "$CMIX" -c "$DIC" "$OUTDIR/fair_natural.bin" "$OUTDIR/fair_natural.fx2" \
    >/dev/null 2>"$OUTDIR/fair_natural.time"
  echo "bytes=$(stat -c%s "$OUTDIR/fair_natural.fx2") $(rg -o 'wall=[0-9.]+ rss=[0-9]+' "$OUTDIR/fair_natural.time"|tail -1)"
  echo "=== fair local_order ==="
  /usr/bin/time -f 'wall=%e rss=%M' "$CMIX" -c "$DIC" "$OUTDIR/fair_local.bin" "$OUTDIR/fair_local.fx2" \
    >/dev/null 2>"$OUTDIR/fair_local.time"
  echo "bytes=$(stat -c%s "$OUTDIR/fair_local.fx2") $(rg -o 'wall=[0-9.]+ rss=[0-9]+' "$OUTDIR/fair_local.time"|tail -1)"
  python3 - <<PY
import os
outdir = "$OUTDIR"
bn = os.path.getsize(f"{outdir}/fair_natural.fx2")
bl = os.path.getsize(f"{outdir}/fair_local.fx2")
d = bl - bn
print(f"Δlocal_vs_natural={d:+d} gate={'KEEP' if d < 0 else 'reject'}")
open(f"{outdir}/fair_delta.txt", "w").write(str(d))
PY
  echo "DONE_fair $(date -Is)"
} | tee "$LOG"
