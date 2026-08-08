#!/usr/bin/env bash
# Overnight GPU sub-1 matrix: LLMZip + nncp on best preprocess/layout combos.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
PAGES="${1:-sample5}"
if [[ "$PAGES" == --pages=* ]]; then
  PAGES="${PAGES#--pages=}"
fi
php benchmarks/build_enwik8_sample_pages.php 2>/dev/null || true
echo "[gpu-matrix] phase 1: full matrix on ${PAGES} (neural models)"
FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0 \
  php -d memory_limit=4096M benchmarks/bench_enwik8_sub1_bpc_matrix.php \
    --pages="${PAGES}" \
    --models=nncp32,llmzip_llama7b,tensorflow_compress,jax_compress,gpt2tc \
    --preprocess=dict_nncp,dict_phda9,wrt_xwrt \
    --stacks=none,zpaq9_brotli11
php benchmarks/summarize_enwik8_sub1_bpc_matrix.php
echo "[gpu-matrix] phase 2: stacked outers on best payload"
BEST_INNER="$(php -r '
$j=json_decode(file_get_contents("benchmarks/.enwik8_sub1_bpc_matrix.json"),true);
$rows=$j["rows"]??[];
foreach($rows as $r){if(($r["status"]??"")==="ok"&&isset($r["stack_bytes"])){file_put_contents(sys_get_temp_dir()."/fz_best_sub1.bin", "x"); break;}}
')"
# Re-run stacked probe on split inner from layout probe if matrix has no ok payload
php benchmarks/bench_enwik8_stacked_outer_probe.php 2>/dev/null || true
echo "[gpu-matrix] done"
