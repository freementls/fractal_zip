#!/usr/bin/env bash
# Run pp96+textcodec encode when test_files109 is not locked by another enwik runner.
set -euo pipefail
cd "$(dirname "$0")/.."
LOCK="${PWD}/benchmarks/.enwik8_encode_test_files109.lock"
LOG="${PWD}/benchmarks/logs/enwik8_textcodec_encode.log"
PHP=(/usr/bin/php -d memory_limit=4096M -d opcache.enable_cli=0)

# Block only on high-RSS encoders or flock lock (ignore stale ~350 KiB shell parents).
if reason=$(bash benchmarks/enwik8_encode_busy_check.sh 2>&1); then
  echo "[textcodec] another enwik encode appears active — ${reason}"
  exit 0
fi

exec 9>"${LOCK}"
if ! flock -n 9; then
  echo "[textcodec] lock held: ${LOCK}"
  exit 0
fi

export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
export FRACTAL_ZIP_ENWIK_TEXT_CODEC=words_base94
export FRACTAL_ZIP_ENWIK_TEXT_CODEC_TRANSFORM=sort_lines_alpha
export FRACTAL_ZIP_ENWIK_TEXT_CODEC_SEED=1
mkdir -p benchmarks/logs
echo "[textcodec] $(date -Iseconds) start" | tee -a "${LOG}"
"${PHP[@]}" benchmarks/run_enwik8_textcodec_encode.php --name=pp96_textcodec 2>&1 | tee -a "${LOG}"
"${PHP[@]}" benchmarks/compare_enwik8_textcodec_vs_pp96.php 2>&1 | tee -a "${LOG}"
echo "[textcodec] $(date -Iseconds) done" | tee -a "${LOG}"
