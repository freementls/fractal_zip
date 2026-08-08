#!/usr/bin/env bash
# Low-priority Hutter prep: @384p phda9 wire arms, gate summary, optional full verify/diagnostic.
# nice -n 19, 768M PHP, serial phda9. Safe alongside desktop use.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
export FRACTAL_ZIP_LOW_MEMORY=1
export FRACTAL_ZIP_BACKGROUND=1
PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-768M}"
LOG_DIR="$REPO/benchmarks/logs"
mkdir -p "$LOG_DIR"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$LOG_DIR/hutter_lowprio_${STAMP}.log"

run() {
  echo ""
  echo "=== $(date -Iseconds) $* ==="
  nice -n 19 "$@"
}

exec > >(tee -a "$LOG") 2>&1
echo "hutter_lowprio start $(date -Iseconds) php_limit=${PHP_MEM} MemAvailable=$(awk '/MemAvailable:/{print int($2/1024)"MiB"}' /proc/meminfo)"

# 1) @384p phda9 single-stream + mono_mi baseline (skip parallel — redundant, 4× phda9 tax)
CASES='split_inner_phda9_xml_single_stream_pp96,split_inner_fztx_mono_mi'
OUT384="$REPO/benchmarks/.enwik8_wire_slice_probe_384p_phda9.json"
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
  --pages=384 --cases="$CASES" --out-json="$OUT384" --verify-rt \
  | tee "$LOG_DIR/hutter_wire_384p_${STAMP}.log"

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/summarize_research_probe.php" \
  --json="$OUT384" --pages=384 || true

# 2) Re-summarize latest @96p with fixed wire@384p normalization
if [[ -f "$REPO/benchmarks/.enwik8_wire_slice_probe.json" ]]; then
  run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/summarize_research_probe.php" \
    --json="$REPO/benchmarks/.enwik8_wire_slice_probe.json" --pages=96 || true
fi

# 3) Full-corpus lossless verify + decompress timing (hours; single lock)
if [[ -f "$REPO/test_files109.fz" ]]; then
  run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/run_enwik8_verify_lowprio.php" \
    | tee "$LOG_DIR/hutter_verify_${STAMP}.log" || true
fi

# 4) FZPA member-level diagnostic if verify lock released
if [[ -f "$REPO/test_files109.fz" ]]; then
  run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/diagnose_phda9_fzpa_rt.php" \
    "$REPO/test_files109.fz" \
    | tee "$LOG_DIR/hutter_fzpa_diag_${STAMP}.log" || true
fi

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/run_enwik8_hutter_compliance.php" --target=beat146_hardware || true

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/run_enwik8_hutter_scorecard.php" || true

echo ""
echo "hutter_lowprio done $(date -Iseconds) → $LOG"
