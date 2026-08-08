#!/usr/bin/env bash
# End-to-end: high encode → compare → patch world-record → metrics + smokes.
set -euo pipefail
cd "$(dirname "$0")/.."
PHP="${PHP:-php}"
LOG="benchmarks/logs/enwik8_high_pipeline.log"
mkdir -p benchmarks/logs

run_encode() {
  if "${PHP}" benchmarks/enwik8_exp_has_fzc.php benchmarks/.enwik8_exp_pp96_high.json 2>/dev/null; then
    echo "[pipeline] pp96_high JSON already valid — skip encode"
    return 0
  fi
  echo "[pipeline] $(date -Iseconds) starting high encode"
  "${PHP}" benchmarks/run_enwik8_encode_high.php 2>&1 | tee -a "${LOG}"
}

run_encode | tee -a "${LOG}"

echo "[pipeline] compare high vs pp96 baseline" | tee -a "${LOG}"
"${PHP}" benchmarks/compare_enwik8_high_vs_baseline.php 2>&1 | tee -a "${LOG}"

hf=$("${PHP}" -r '
$j=json_decode(file_get_contents("benchmarks/.enwik8_exp_pp96_high.json"),true);
foreach($j["cases"]??[] as $c){if(($c["label"]??"")==="test_files109"){echo (int)($c["fzc_bytes"]??0);exit;}}echo 0;')
bf=$("${PHP}" -r '
$j=json_decode(file_get_contents("benchmarks/.enwik8_exp_pp96.json"),true);
foreach($j["cases"]??[] as $c){if(($c["label"]??"")==="test_files109"){echo (int)($c["fzc_bytes"]??0);exit;}}echo 0;')

if [[ "${hf}" -gt 1000000 && "${hf}" -lt "${bf}" ]]; then
  echo "[pipeline] high wins — patch world_record (${bf} → ${hf})" | tee -a "${LOG}"
  "${PHP}" benchmarks/patch_enwik8_world_record_fzc.php benchmarks/.enwik8_exp_pp96_high.json
else
  echo "[pipeline] high fzc=${hf} baseline pp96=${bf} — no patch" | tee -a "${LOG}"
fi

"${PHP}" benchmarks/summarize_enwik8_experiments.php 2>&1 | tee -a "${LOG}"
"${PHP}" benchmarks/compare_enwik8_world_record.php 2>&1 | tee -a "${LOG}"
"${PHP}" benchmarks/compare_enwik8_four_way.php 2>&1 | tee -a "${LOG}" || true

for s in smoke_world_record_env smoke_world_record_high_env enwik_entry_sort_roundtrip_smoke enwik_chunk_restore_smoke; do
  case "${s}" in
    enwik_*) path="tests/${s}.php" ;;
    *) path="benchmarks/${s}.php" ;;
  esac
  echo "[pipeline] ${s}" | tee -a "${LOG}"
  "${PHP}" "${path}" 2>&1 | tee -a "${LOG}"
done

echo "[pipeline] done $(date -Iseconds)" | tee -a "${LOG}"
