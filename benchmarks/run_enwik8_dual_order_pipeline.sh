#!/usr/bin/env bash
# Dual-order fast path: encode with squash cache, patch WR if smaller, compare, optional verify.
set -euo pipefail
cd "$(dirname "$0")/.."
PHP="${PHP:-php}"
LOG="benchmarks/logs/enwik8_dual_order_pipeline.log"
VERIFY=0
for arg in "$@"; do
  case "${arg}" in
    --verify) VERIFY=1 ;;
  esac
done
mkdir -p benchmarks/logs

if [[ ! -f benchmarks/.enwik8_paq_squash.fzpq ]]; then
  echo "[dual_order] missing benchmarks/.enwik8_paq_squash.fzpq" | tee -a "${LOG}"
  echo "[dual_order] one-time export (~7.5 h phda9):" | tee -a "${LOG}"
  echo "  FRACTAL_ZIP_PAQ_TOOLS=phda9 php benchmarks/bench_enwik8_paq_export_wire.php" | tee -a "${LOG}"
  exit 1
fi

{
  echo "=== $(date -Iseconds) dual-order pipeline ==="
  echo "=== encode + roundtrip (run_enwik8_encode_dual_order.php) ==="
  "${PHP}" benchmarks/run_enwik8_encode_dual_order.php --name=dual_order

  fzc=$("${PHP}" -r '
$j=json_decode(file_get_contents("benchmarks/.enwik8_exp_dual_order.json"),true);
foreach($j["cases"]??[] as $c){if(($c["label"]??"")==="test_files109"){echo (int)($c["fzc_bytes"]??0);exit;}}echo 0;')
  wr_fzc=$("${PHP}" -r '
$j=json_decode(file_get_contents("benchmarks/.enwik8_world_record.json"),true);
foreach($j["cases"]??[] as $c){if(($c["label"]??"")==="test_files109"){echo (int)($c["fzc_bytes"]??0);exit;}}echo 0;')

  if [[ "${fzc}" -gt 1000000 && ( "${wr_fzc}" -eq 0 || "${fzc}" -lt "${wr_fzc}" ) ]]; then
    echo "=== patch world_record fzc ${wr_fzc} → ${fzc} ==="
    "${PHP}" benchmarks/patch_enwik8_world_record_fzc.php benchmarks/.enwik8_exp_dual_order.json
  else
    echo "=== fzc=${fzc} wr_fzc=${wr_fzc} — skip patch ==="
  fi

  echo "=== compare ==="
  "${PHP}" benchmarks/compare_enwik8_world_record.php
  "${PHP}" benchmarks/compare_enwik8_four_way.php || true

  if [[ "${VERIFY}" -eq 1 ]]; then
    echo "=== verify already done in encode_dual_order (see verify_ok in JSON) ==="
  fi

  echo "=== done $(date -Iseconds) fzc=${fzc} ==="
} 2>&1 | tee -a "${LOG}"
