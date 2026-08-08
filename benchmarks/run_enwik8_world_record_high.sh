#!/usr/bin/env bash
# ~1–2h enwik8 encode: world-record + high tier (fz only; no raw phda9 during encode).
set -euo pipefail
cd "$(dirname "$0")/.."
mkdir -p benchmarks/logs
PHP="${PHP:-php}"
LOG="benchmarks/logs/enwik8_pp96_high.log"
echo "[$(date -Iseconds)] starting high encode → ${LOG}"
"${PHP}" benchmarks/run_enwik8_encode_high.php "$@" 2>&1 | tee -a "${LOG}"
"${PHP}" benchmarks/compare_enwik8_high_vs_baseline.php 2>&1 | tee -a "${LOG}"
