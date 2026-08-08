#!/usr/bin/env bash
# Push wire460 hunt: optimal @384p dict → integrated wire probe → dict compress validation.
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
export FRACTAL_ZIP_LOW_MEMORY=1
PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-768M}"
TARGET_WIRE=460096
LOG_DIR="$REPO/benchmarks/logs"
mkdir -p "$LOG_DIR"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$LOG_DIR/wire460_push_${STAMP}.log"
OUT_JSON="$REPO/benchmarks/.enwik8_wire460_hunt_${STAMP}.json"
OPTIMAL_DICT="$REPO/benchmarks/.phda9_external_dict_384p_optimal.txt"
MAIN_DICT="$REPO/benchmarks/.phda9_external_dict.txt"

exec > >(tee -a "$LOG") 2>&1
echo "wire460_push start $(date -Iseconds) target=${TARGET_WIRE}"

run() {
  echo ""
  echo "=== $(date -Iseconds) $* ==="
  nice -n 19 "$@"
}

if [[ ! -f "$OPTIMAL_DICT" ]]; then
  run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/build_phda9_external_dict.php" \
    --pages=384 --mode=optimal --out="$OPTIMAL_DICT"
fi
cp -f "$OPTIMAL_DICT" "$MAIN_DICT"
echo "installed optimal dict: $(wc -c <"$OPTIMAL_DICT") B"

CASES='split_inner_phda9_xml_single_stream_pp96,split_inner_phda9_xml_single_stream_lstm,split_inner_phda9_xml_single_stream_lstm_dict'
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
  --pages=384 --cases="$CASES" --out-json="$OUT_JSON" --verify-rt \
  | tee "$LOG_DIR/wire460_probe_384p_${STAMP}.log" || true

echo ""
echo "=== $(date -Iseconds) gate summary ==="
php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/summarize_research_probe.php" \
  --json="$OUT_JSON" --pages=384 || true

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_phda9_dict_token_sweep.php" \
  --pages=384 --compress \
  | tee "$LOG_DIR/wire460_dict_compress_384p_${STAMP}.log" || true

echo "wire460_push done $(date -Iseconds) → $LOG probe=$OUT_JSON"
