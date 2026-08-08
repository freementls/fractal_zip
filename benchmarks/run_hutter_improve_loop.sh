#!/usr/bin/env bash
# One-change enwik8 improvement loop for fx2-cmix forks.
# Keep a change only if ΔS1 < 0 (or measured archive shrink) AND time still
# extrapolates under the Hutter ~50h/phase budget on enwik9.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FX2="$ROOT/tools/hutter/fx2-cmix"
RUN="$FX2/run"
SLICE="${1:-/tmp/enwik8_1m.bin}"
OUT_DIR="${2:-$ROOT/benchmarks/.hutter_improve_loop}"
mkdir -p "$OUT_DIR"

if [[ ! -x "$RUN/cmix_orig" ]]; then
  echo "missing $RUN/cmix_orig — build fx2-cmix first" >&2
  exit 1
fi
if [[ ! -f "$SLICE" ]]; then
  dd if="$ROOT/enwik8" of="$SLICE" bs=1M count=1 status=none
fi

L=110793128
PRIZE_S=$((L * 99 / 100))  # 109685196 approx
S1_PUB=441463
S2_PUB=110351665

measure() {
  local bin=$1 tag=$2
  local out="$OUT_DIR/${tag}.out"
  local log="$OUT_DIR/${tag}.log"
  /usr/bin/time -v "$bin" -n "$SLICE" "$out" >"$log" 2>&1 || true
  local bytes=0
  if [[ -f "$out" ]]; then bytes=$(stat -c%s "$out"); fi
  local rss elapsed
  rss=$(rg -o 'Maximum resident set size \(kbytes\): [0-9]+' "$log" | awk '{print $NF}')
  elapsed=$(rg -o 'Elapsed \(wall clock\) time \(h:mm:ss or m:ss\): .*' "$log" | sed 's/.*: //')
  local line
  line=$(rg 'bytes ->' "$log" | tail -1 || true)
  printf '%s\n' "$tag bytes=$bytes rss_kib=${rss:-?} wall=${elapsed:-?} line=$line"
}

echo "=== improve-loop slice=$(stat -c%s "$SLICE") ==="
measure "$RUN/cmix_orig" baseline
if [[ -x "$RUN/cmix_upx" ]]; then
  measure "$RUN/cmix_upx" upx
fi

S1_BASE=$(stat -c%s "$RUN/cmix_baseline" 2>/dev/null || echo 0)
S1_IMP=$(stat -c%s "$RUN/cmix_improved" 2>/dev/null || echo 0)
echo "S1_baseline=$S1_BASE S1_improved=$S1_IMP delta=$((S1_IMP - S1_BASE))"
echo "published S1=$S1_PUB S2=$S2_PUB S=$L prize_need_S<$PRIZE_S"
echo "KEEP rule: keep change iff S shrinks and enwik9 time still under budget"
