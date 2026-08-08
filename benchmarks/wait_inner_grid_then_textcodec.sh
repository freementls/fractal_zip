#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
LOG="benchmarks/logs/enwik8_textcodec_wait.log"
mkdir -p benchmarks/logs
{
  echo "[wait] $(date -Iseconds) waiting for inner_experiment_grid"
  export ENWIK8_BUSY_PATTERN='run_enwik8_inner_experiment\.php'
  export ENWIK8_BUSY_CHECK_LOCK=0
  while bash benchmarks/enwik8_encode_busy_check.sh >/dev/null 2>&1; do
    tail -1 benchmarks/logs/enwik8_inner_grid.log 2>/dev/null || true
    sleep 90
  done
  echo "[wait] $(date -Iseconds) inner grid done; starting textcodec"
  bash benchmarks/run_enwik8_textcodec_when_idle.sh
} 2>&1 | tee -a "${LOG}"
