#!/usr/bin/env bash
# Low-priority beat-14.6M research sweep (background / low RAM). Logs under benchmarks/logs/.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$REPO/benchmarks/logs/beat146_sweep.log"
export FRACTAL_ZIP_LOW_MEMORY=1
PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-768M}"
mkdir -p "$REPO/benchmarks/logs"
exec > >(tee -a "$LOG") 2>&1

echo "=== beat146 sweep $(date -Iseconds) low_memory=1 php_limit=${PHP_MEM} ==="

run() {
  echo ""
  echo "--- $* ---"
  nice -n 19 "$@"
}

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/diagnose_phda9_fzpa_rt.php" \
  "$REPO/test_files109.fz" \
  | tee "$REPO/benchmarks/logs/beat146_fzpa_rt_diag.log"

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/run_enwik8_beat146_probe.php" --with-768 \
  | tee "$REPO/benchmarks/logs/beat146_probe.log"

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_hutter_wire_gate.php" --pages=384 --with-lstm \
  | tee "$REPO/benchmarks/logs/beat146_hutter_gate_384p.log"

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_phda9_xml_prepass.php" --pages=384 \
  | tee "$REPO/benchmarks/logs/beat146_prepass_384p.log"

run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/run_enwik8_phda9_hybrid_compare.php" --pages=384 --wire-only \
  | tee "$REPO/benchmarks/logs/beat146_hybrid_384p.log"

echo ""
echo "=== beat146 sweep done $(date -Iseconds) ==="
