#!/usr/bin/env bash
# Serial @384p wire460 — ONE phda9 at a time, nice -n 19, 768M PHP.
# Order: best-known arm first, then mixed_tiered hunt, baselines, scorecard.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
export FRACTAL_ZIP_LOW_MEMORY=1
export FRACTAL_ZIP_BACKGROUND=1
PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-768M}"
LOG_DIR="$REPO/benchmarks/logs"
mkdir -p "$LOG_DIR"
LOCK="$REPO/benchmarks/.enwik8_phda9_serial.lock"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$LOG_DIR/wire460_serial_${STAMP}.log"

exec 9>"$LOCK"
if ! flock -n 9; then
  echo "Another enwik8 phda9 job holds $LOCK — exit (one compress at a time)." >&2
  exit 0
fi

run() {
  echo ""
  echo "=== $(date -Iseconds) $* ==="
  nice -n 19 env FRACTAL_ZIP_LOW_MEMORY=1 FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1 "$@"
}

exec > >(tee -a "$LOG") 2>&1
echo "wire460_serial_lowprio start $(date -Iseconds) MemAvailable=$(awk '/MemAvailable:/{print int($2/1024)"MiB"}' /proc/meminfo)"

# 1) Best verified integrated wire: lstm + full words dict (~519 KiB @384p)
BEST_JSON="$REPO/benchmarks/.enwik8_wire460_best_${STAMP}.json"
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
  --pages=384 --verify-rt \
  --cases=split_inner_phda9_xml_single_stream_lstm_mixed_dict \
  --out-json="$BEST_JSON" \
  | tee "$LOG_DIR/wire460_lstm_words_${STAMP}.log" || true

# 2) mixed_tiered FZPA (dict selector fix) → wire if FZPA ≤ words-only 514336
TIERED_JSON="$REPO/benchmarks/.enwik8_phda9_mixed_tiered_retest_384p.json"
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_phda9_dict_mixed_tiered_retest.php" \
  --pages=384 \
  | tee "$LOG_DIR/mixed_tiered_retest_${STAMP}.log" || true

if [[ -f "$TIERED_JSON" ]]; then
  TIERED_FZPA=$(php -r '$j=json_decode(file_get_contents($argv[1]),true); echo (int)($j["fzpa_bytes"]??0);' "$TIERED_JSON" 2>/dev/null || echo 0)
  WORDS_FZPA=514336
  if [[ "$TIERED_FZPA" -gt 0 && "$TIERED_FZPA" -le "$WORDS_FZPA" ]]; then
    TIERED_DICT="$REPO/benchmarks/.phda9_external_dict_mixed_tiered_384p_retest.txt"
    if [[ -f "$TIERED_DICT" ]]; then
      cp -f "$TIERED_DICT" "$REPO/benchmarks/.phda9_external_dict_mixed_best.txt"
      TIERED_WIRE_JSON="$REPO/benchmarks/.enwik8_wire460_tiered_${STAMP}.json"
      run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
        --pages=384 --verify-rt \
        --cases=split_inner_phda9_xml_single_stream_lstm_mixed_dict \
        --out-json="$TIERED_WIRE_JSON" \
        | tee "$LOG_DIR/wire460_lstm_tiered_${STAMP}.log" || true
    fi
  else
    echo "mixed_tiered FZPA=${TIERED_FZPA} — skip wire (need ≤ ${WORDS_FZPA})"
  fi
fi

# 3) True no-dict baseline (lstm + pp96) for clean Δ
run "$REPO/benchmarks/run_enwik8_wire460_push3.sh" || true

# 4) qg_text_normalize RT @384p (preprocess path; secondary)
QG_JSON="$REPO/benchmarks/.enwik8_qg_normalize_rt_384p_${STAMP}.json"
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
  --pages=384 --verify-rt \
  --cases=split_inner_phda9_xml_single_stream_lstm_qg_text_normalize \
  --out-json="$QG_JSON" \
  | tee "$LOG_DIR/qg_normalize_rt_384p_${STAMP}.log" || true

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/run_enwik8_hutter_scorecard.php" || true

echo ""
echo "wire460_serial_lowprio done $(date -Iseconds) → $LOG"
