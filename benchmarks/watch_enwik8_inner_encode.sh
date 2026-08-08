#!/usr/bin/env bash
# Poll inner experiment log / JSON until encode completes.
set -euo pipefail
cd "$(dirname "$0")/.."
LOG="${1:-benchmarks/logs/enwik8_inner_baseline.log}"
CASE="${2:-inner_baseline}"
JSON="benchmarks/.enwik8_exp_${CASE}.json"
PHP="${PHP:-/usr/bin/php}"

while true; do
  if [[ -f "${JSON}" ]] && "${PHP}" benchmarks/enwik8_exp_has_fzc.php "${JSON}" 2>/dev/null; then
    fzc=$("${PHP}" -r '$j=json_decode(file_get_contents($argv[1]),true);foreach($j["cases"]??[] as $c){if(($c["label"]??"")==="test_files109"){echo (int)($c["fzc_bytes"]??0);exit;}}echo 0;' "${JSON}")
    echo "[watch] done ${CASE}: fzc=${fzc} B"
    "${PHP}" benchmarks/compare_enwik8_inner_vs_baseline.php 2>/dev/null || true
    exit 0
  fi
  if [[ -f "${LOG}" ]]; then
    tail -1 "${LOG}" 2>/dev/null || true
  fi
  if ! pgrep -f "run_enwik8_inner_experiment.php.*--case=${CASE}" >/dev/null 2>&1; then
    echo "[watch] no running process for ${CASE}; check ${LOG}"
    exit 1
  fi
  sleep 120
done
