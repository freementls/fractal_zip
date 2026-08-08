#!/usr/bin/env bash
# Low-RAM enwik8 research loop: fast @96p wire arms, then gate extrapolation summary.
# One phda9 job at a time; 768M PHP cap. Safe to run while using the desktop.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
export FRACTAL_ZIP_LOW_MEMORY=1
export FRACTAL_ZIP_BACKGROUND=1
PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-768M}"
LOG_DIR="$REPO/benchmarks/logs"
mkdir -p "$LOG_DIR"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$LOG_DIR/research_probe_${STAMP}.log"

run() {
  echo ""
  echo "=== $(date -Iseconds) $* ==="
  nice -n 19 "$@"
}

exec > >(tee -a "$LOG") 2>&1
echo "research_probe start $(date -Iseconds) php_limit=${PHP_MEM} MemAvailable=$(awk '/MemAvailable:/{print int($2/1024)"MiB"}' /proc/meminfo)"

# 1) Isolated phda9 prepass (plain XML arms — minutes @96p)
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_phda9_xml_prepass.php" --pages=96 \
  | tee "$LOG_DIR/research_prepass_96p_${STAMP}.log"

# 2) Integrated wire slice @96p (production arms only)
CASES='split_inner_fztx_mono_mi,split_inner_phda9_xml_single_stream_pp96,split_inner_phda9_xml_single_stream_lstm,split_inner_phda9_xml_pp96_parallel,split_inner_phda9_article_pp96_parallel'
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
  --pages=96 --cases="$CASES" \
  | tee "$LOG_DIR/research_wire_96p_${STAMP}.log"

# 3) Gate extrapolation vs beat-14.6 total S
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/summarize_research_probe.php" \
  --json="$REPO/benchmarks/.enwik8_wire_slice_probe.json" --pages=96

echo ""
echo "research_probe done $(date -Iseconds) → $LOG"
