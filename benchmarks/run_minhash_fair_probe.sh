#!/usr/bin/env bash
# Fair S2: published/winsort512 pages vs MinHash content-similar order @ HEAD bytes.
# Restore remains page-ID sort on decompress — this only changes compress order.
# Usage: HEAD=1048576 bash benchmarks/run_minhash_fair_probe.sh
set -euo pipefail
ROOT=/srv/http/fractal_zip
DATA=$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data
export HUTTER_MAX_SWAP_MIB="${HUTTER_MAX_SWAP_MIB:-6144}"
HEAD=${HEAD:-1048576}
TAG=${TAG:-minhash_w64_${HEAD}}
ORDER_A=${ORDER_A:-$DATA/new_article_order_published_pages}
# Fall back to banked winsort512 pages if published_pages missing
[[ -s "$ORDER_A" ]] || ORDER_A=$DATA/new_article_order
ORDER_B=$DATA/new_article_order_pub_minhash_w64_pages
[[ -s "$ORDER_B" ]] || ORDER_B=$DATA/new_article_order_minhash_w64
OUTDIR=$ROOT/benchmarks/.ladder_cache/article_order_probe_${TAG}
LOG=$ROOT/benchmarks/.hutter_logs/minhash_fair_${TAG}.log
ORDER_A=$ORDER_A ORDER_B=$ORDER_B TAG=$TAG HEAD=$HEAD OUTDIR=$OUTDIR LOG=$LOG \
  bash "$ROOT/benchmarks/run_article_order_ab_fair.sh"
