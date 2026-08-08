#!/usr/bin/env bash
# Mixed dict hunt (FZPA) → wire probe with best mixed dict on pp96 + lstm.
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
export FRACTAL_ZIP_LOW_MEMORY=1
PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-768M}"
LOG_DIR="$REPO/benchmarks/logs"
mkdir -p "$LOG_DIR"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$LOG_DIR/wire460_mixed_${STAMP}.log"
PROBE_JSON="$REPO/benchmarks/.enwik8_wire460_mixed_${STAMP}.json"

exec > >(tee -a "$LOG") 2>&1
echo "wire460_mixed start $(date -Iseconds)"

run() {
  echo ""
  echo "=== $(date -Iseconds) $* ==="
  nice -n 19 "$@"
}

pkill -f 'run_enwik8_wire460_push3_after_push2' 2>/dev/null || true

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_phda9_dict_mixed_hunt.php" \
  --pages=384 --refine-trials=12 \
  | tee "$LOG_DIR/mixed_hunt_fzpa_${STAMP}.log" || true

BEST="$REPO/benchmarks/.phda9_external_dict_mixed_best.txt"
if [[ ! -f "$BEST" ]]; then
  echo "no mixed best dict — building mixed tiered fallback"
  run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/build_phda9_external_dict.php" \
    --pages=384 --mode=mixed --out="$BEST" || true
fi

if [[ -f "$BEST" ]]; then
  cp -f "$BEST" "$REPO/benchmarks/.phda9_external_dict.txt"
  echo "installed mixed dict: $(wc -c <"$BEST") B"
fi

CASES='split_inner_phda9_xml_single_stream_pp96_mixed_dict,split_inner_phda9_xml_single_stream_lstm_mixed_dict'
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
  --pages=384 --cases="$CASES" --out-json="$PROBE_JSON" --verify-rt \
  | tee "$LOG_DIR/mixed_wire_probe_${STAMP}.log" || true

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/summarize_research_probe.php" \
  --json="$PROBE_JSON" --pages=384 || true

echo "wire460_mixed done $(date -Iseconds) probe=$PROBE_JSON"
