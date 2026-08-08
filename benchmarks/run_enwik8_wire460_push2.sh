#!/usr/bin/env bash
# Dict compress validation + QG preprocess / inner-dict wire probes @384p.
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
export FRACTAL_ZIP_LOW_MEMORY=1
PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-768M}"
LOG_DIR="$REPO/benchmarks/logs"
mkdir -p "$LOG_DIR"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$LOG_DIR/wire460_push2_${STAMP}.log"
PROBE_JSON="$REPO/benchmarks/.enwik8_wire460_preprocess_${STAMP}.json"

exec > >(tee -a "$LOG") 2>&1
echo "wire460_push2 start $(date -Iseconds)"

run() {
  echo ""
  echo "=== $(date -Iseconds) $* ==="
  nice -n 19 "$@" || echo "WARN: exit $? from: $*"
}

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_phda9_dict_token_sweep.php" \
  --pages=384 --compress \
  | tee "$LOG_DIR/wire460_dict_compress_384p_${STAMP}.log"

CASES='split_inner_phda9_xml_single_stream_lstm_qg_text_normalize,split_inner_phda9_xml_single_stream_lstm_qg_subword_root,split_inner_phda9_xml_single_stream_lstm_qg_hybrid_root,split_inner_fztx_mono_mi_dict_phda9_inner_384p'
if [[ -f "$REPO/benchmarks/.phda9_external_dict.txt" ]]; then
  mv -f "$REPO/benchmarks/.phda9_external_dict.txt" "$REPO/benchmarks/.phda9_external_dict.txt.push2_bak"
  echo "moved external dict aside for wire probe (no bleed)"
fi
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
  --pages=384 --cases="$CASES" --out-json="$PROBE_JSON" --verify-rt \
  | tee "$LOG_DIR/wire460_preprocess_probe_384p_${STAMP}.log"
if [[ -f "$REPO/benchmarks/.phda9_external_dict.txt.push2_bak" ]]; then
  mv -f "$REPO/benchmarks/.phda9_external_dict.txt.push2_bak" "$REPO/benchmarks/.phda9_external_dict.txt"
fi

echo ""
echo "=== $(date -Iseconds) gate summary ==="
php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/summarize_research_probe.php" \
  --json="$PROBE_JSON" --pages=384 || true

echo "wire460_push2 done $(date -Iseconds) → $LOG probe=$PROBE_JSON"
