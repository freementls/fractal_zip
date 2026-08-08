#!/usr/bin/env bash
# Fair order A/B on identical page set (HEAD bytes). Orders are rank files of page indices.
# Usage: ORDER_A=... ORDER_B=... OUTDIR=... HEAD=1048576 bash benchmarks/run_article_order_ab_fair.sh
set -euo pipefail
ROOT=/srv/http/fractal_zip
ENWIK9=${ENWIK9:-$ROOT/tools/hutter/data/enwik9}
ORDER_A=${ORDER_A:?}
ORDER_B=${ORDER_B:?}
TAG=${TAG:-ab}
OUTDIR=${OUTDIR:-$ROOT/benchmarks/.ladder_cache/article_order_probe_${TAG}}
CMIX=${CMIX:-$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_entity}
DIC=${DIC:-$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic}
HEAD=${HEAD:-1048576}
mkdir -p "$OUTDIR"
LOG=${LOG:-$ROOT/benchmarks/.hutter_logs/article_order_${TAG}_fair.log}

python3 - <<PY
from pathlib import Path
enwik = Path("$ENWIK9").read_bytes()
parts = enwik.split(b"<page>")
preamble = parts[0]
rank_a = {int(x): i for i, x in enumerate(Path("$ORDER_A").read_text().splitlines()) if x.strip()}
rank_b = {int(x): i for i, x in enumerate(Path("$ORDER_B").read_text().splitlines()) if x.strip()}
head = int("$HEAD")
chosen = []
size = len(preamble)
for i in range(len(parts) - 1):
    blob_len = 6 + len(parts[i + 1])
    if size + blob_len > head and chosen:
        break
    chosen.append(i)
    size += blob_len

def emit(order_pages, path):
    out = bytearray(preamble)
    for idx in order_pages:
        out.extend(b"<page>")
        out.extend(parts[idx + 1])
    if len(out) < head:
        out.extend(b"\0" * (head - len(out)))
    Path(path).write_bytes(bytes(out[:head]))

oa = sorted(chosen, key=lambda p: rank_a.get(p, 10**9))
ob = sorted(chosen, key=lambda p: rank_b.get(p, 10**9))
emit(oa, "$OUTDIR/fair_a.bin")
emit(ob, "$OUTDIR/fair_b.bin")
moved = sum(1 for a, b in zip(oa, ob) if a != b)
print(f"chosen_pages={len(chosen)} plain={head} order_moved={moved}")
PY

{
  echo "START_ab $TAG $(date -Is) head=$HEAD"
  bash "$ROOT/benchmarks/hutter_memory_guard.sh"
  echo "=== fair A ($ORDER_A) ==="
  if [[ -n "${REUSE_FAIR_A:-}" && -s "$REUSE_FAIR_A" ]]; then
    if [[ "$REUSE_FAIR_A" -ef "$OUTDIR/fair_a.fx2" ]]; then
      : # already in place
    else
      cp -a "$REUSE_FAIR_A" "$OUTDIR/fair_a.fx2"
    fi
    echo "reused_a=$(stat -c%s "$OUTDIR/fair_a.fx2") from $REUSE_FAIR_A" | tee "$OUTDIR/fair_a.time"
  else
    /usr/bin/time -f 'wall=%e rss=%M' "$CMIX" -c "$DIC" "$OUTDIR/fair_a.bin" "$OUTDIR/fair_a.fx2" \
      >/dev/null 2>"$OUTDIR/fair_a.time"
    echo "bytes_a=$(stat -c%s "$OUTDIR/fair_a.fx2") $(rg -o 'wall=[0-9.]+ rss=[0-9]+' "$OUTDIR/fair_a.time"|tail -1)"
  fi
  echo "=== fair B ($ORDER_B) ==="
  /usr/bin/time -f 'wall=%e rss=%M' "$CMIX" -c "$DIC" "$OUTDIR/fair_b.bin" "$OUTDIR/fair_b.fx2" \
    >/dev/null 2>"$OUTDIR/fair_b.time"
  echo "bytes_b=$(stat -c%s "$OUTDIR/fair_b.fx2") $(rg -o 'wall=[0-9.]+ rss=[0-9]+' "$OUTDIR/fair_b.time"|tail -1)"
  python3 - <<PY
import os
outdir = "$OUTDIR"
ba = os.path.getsize(f"{outdir}/fair_a.fx2")
bb = os.path.getsize(f"{outdir}/fair_b.fx2")
d = bb - ba
print(f"ΔB_vs_A={d:+d} gate={'KEEP' if d < 0 else 'reject'}")
open(f"{outdir}/fair_delta.txt", "w").write(str(d))
PY
  echo "DONE_ab $TAG $(date -Is)"
} | tee "$LOG"
