#!/usr/bin/env bash
# Inner grid → compare vs pp96 → summarize.
set -euo pipefail
cd "$(dirname "$0")/.."
LOG="benchmarks/logs/enwik8_inner_pipeline.log"
mkdir -p benchmarks/logs
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1

{
  echo "[inner-pipeline] $(date -Iseconds) start"
  bash benchmarks/run_enwik8_inner_experiment_grid.sh
  /usr/bin/php benchmarks/compare_enwik8_inner_vs_baseline.php 2>&1 || true
  /usr/bin/php benchmarks/summarize_enwik8_experiments.php 2>&1 || true
  echo "[inner-pipeline] $(date -Iseconds) done"
} 2>&1 | tee -a "${LOG}"
