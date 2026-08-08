#!/usr/bin/env bash
# Continue axis-4 fair@10m after an in-flight v1_w4096 arm finishes.
# Reuses fair_a from v1 for w512/w1024.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BASE_OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/reorder_fair_10m"
LOG="$BASE_OUT/screen.log"
ORDER_A="${ORDER_A:-$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_published_pages}"
CMIX="$ROOT/benchmarks/wrappers/cmix_fractalv2_lstm320_mix2.sh"
P4B="$ROOT/benchmarks/.ladder_cache/fractal_p4b"
export HUTTER_MAX_SWAP_MIB="${HUTTER_MAX_SWAP_MIB:-6144}"
mkdir -p "$BASE_OUT"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

V1="$BASE_OUT/v1_w4096"
# Skip wait if v1 already has a delta (resume after A/B done).
if [[ ! -s "$V1/fair_delta.txt" ]]; then
  log "WAIT for in-flight v1_w4096 fair A/B"
  while true; do
    # Match live cmix argv0 only — not wrapper bash -c strings that embed paths.
    if pgrep -f '/run/cmix_match3m_fractalv2_lstm320 .*/reorder_fair_10m/v1_w4096/fair_' >/dev/null 2>&1; then
      sleep 60
      continue
    fi
    break
  done
fi
[[ -s "$V1/fair_delta.txt" && -s "$V1/fair_a.fx2" ]] || {
  log "v1 incomplete — missing fair_delta/fair_a; abort"
  exit 1
}
d=$(cat "$V1/fair_delta.txt")
ba=$(stat -c%s "$V1/fair_a.fx2")
bb=$(stat -c%s "$V1/fair_b.fx2")
log "v1_w4096 done Δ=$d A=$ba B=$bb"
REUSE_A="$V1/fair_a.fx2"

declare -a TAGS=("w512" "w1024")
declare -A ORDER_FOR=(
  [w512]="$P4B/new_article_order_pub_fractal_w512_pages"
  [w1024]="$P4B/new_article_order_pub_fractal_w1024_pages"
)

best_tag="v1_w4096"; best_d=$d; best_bb=$bb; best_ba=$ba
for tag in "${TAGS[@]}"; do
  ob="${ORDER_FOR[$tag]}"
  out="$BASE_OUT/$tag"
  if [[ -s "$out/fair_delta.txt" ]]; then
    log "SKIP $tag — already has fair_delta"
    dd=$(cat "$out/fair_delta.txt")
    if (( dd < best_d )); then best_d=$dd; best_tag=$tag; best_bb=$(stat -c%s "$out/fair_b.fx2"); best_ba=$(stat -c%s "$out/fair_a.fx2"); fi
    continue
  fi
  mkdir -p "$out"
  log "START fair @10m tag=$tag B=$ob"
  env ORDER_A="$ORDER_A" ORDER_B="$ob" TAG="fractal_${tag}_10m" OUTDIR="$out" HEAD=10485760 \
    CMIX="$CMIX" LOG="$LOG" REUSE_FAIR_A="$REUSE_A" \
    bash "$ROOT/benchmarks/run_article_order_ab_fair.sh"
  dd=$(cat "$out/fair_delta.txt")
  bb=$(stat -c%s "$out/fair_b.fx2")
  ba=$(stat -c%s "$out/fair_a.fx2")
  log "RESULT $tag ΔB_vs_A=$dd A=$ba B=$bb"
  if (( dd < best_d )); then best_d=$dd; best_tag=$tag; best_bb=$bb; best_ba=$ba; fi
done

log "BEST tag=$best_tag Δ=$best_d A=$best_ba B=$best_bb"
if (( best_d < 0 )); then
  log "VERDICT: KEEP_FAIR — promote $best_tag (Δ=$best_d)"
else
  log "VERDICT: REJECT_FAIR — no order beat published @10m"
fi
