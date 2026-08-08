#!/usr/bin/env bash
# Phase 4b follow-up: per-cluster fractal reorder fair A/B @10 MB.
# Tries ORDER_B ladder: v1 (w_dup=4096) → w512 → w1024. Reuses published fair_a.
# CMIX = banked LSTM320 + MIX_NUMLEN=2 wrapper.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BASE_OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/reorder_fair_10m"
LOG="$BASE_OUT/screen.log"
ORDER_A="${ORDER_A:-$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_published_pages}"
CMIX="$ROOT/benchmarks/wrappers/cmix_fractalv2_lstm320_mix2.sh"
P4B="$ROOT/benchmarks/.ladder_cache/fractal_p4b"
# Desktop/Cursor often leaves ~5 GiB swap resident; 4096 default false-blocks.
export HUTTER_MAX_SWAP_MIB="${HUTTER_MAX_SWAP_MIB:-6144}"
mkdir -p "$BASE_OUT"
echo $$ >"$BASE_OUT/run.pid"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

[[ -s "$ORDER_A" ]] || { log "missing ORDER_A"; exit 1; }
[[ -x "$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm320" ]] || { log "missing lstm320"; exit 1; }
chmod +x "$CMIX"

# Only refuse a live compress binary (not this script / wrappers).
if pgrep -f 'tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2' >/dev/null 2>&1; then
  log "REFUSE: fractalv2 cmix already running — serialize"
  exit 1
fi

# tag -> pages order file
declare -a TAGS=("v1_w4096" "w512" "w1024")
declare -A ORDER_FOR=(
  [v1_w4096]="$P4B/new_article_order_pub_fractalv1_pages"
  [w512]="$P4B/new_article_order_pub_fractal_w512_pages"
  [w1024]="$P4B/new_article_order_pub_fractal_w1024_pages"
)

# Optional: single ORDER_B overrides ladder (one arm only).
if [[ -n "${ORDER_B:-}" ]]; then
  TAGS=("custom")
  ORDER_FOR[custom]="$ORDER_B"
fi

REUSE_A=""
best_tag=""; best_d=999999999; best_bb=0; best_ba=0

for tag in "${TAGS[@]}"; do
  ob="${ORDER_FOR[$tag]}"
  if [[ ! -s "$ob" ]]; then
    log "SKIP $tag — missing $ob"
    continue
  fi
  out="$BASE_OUT/$tag"
  mkdir -p "$out"
  log "START fair @10m tag=$tag B=$ob"
  env_args=(
    ORDER_A="$ORDER_A" ORDER_B="$ob"
    TAG="fractal_${tag}_10m" OUTDIR="$out" HEAD=10485760
    CMIX="$CMIX" LOG="$LOG"
  )
  if [[ -n "$REUSE_A" ]]; then
    env_args+=(REUSE_FAIR_A="$REUSE_A")
  fi
  env "${env_args[@]}" bash "$ROOT/benchmarks/run_article_order_ab_fair.sh"

  ba=$(stat -c%s "$out/fair_a.fx2")
  bb=$(stat -c%s "$out/fair_b.fx2")
  d=$((bb - ba))
  log "RESULT $tag published=$ba fractal=$bb Δ=$d"
  # Keep published compress for next arms
  REUSE_A="$out/fair_a.fx2"
  if (( d < best_d )); then
    best_d=$d; best_tag=$tag; best_bb=$bb; best_ba=$ba
  fi
  if (( d <= -64 )); then
    log "EARLY_STOP KEEP on $tag"
    break
  fi
done

python3 - "$best_tag" "$best_ba" "$best_bb" "$best_d" <<'PY' | tee -a "$LOG" >&2
import sys
tag, ba, bb, d = sys.argv[1], int(sys.argv[2]), int(sys.argv[3]), int(sys.argv[4])
print(f"10m_fair_best tag={tag} published={ba} fractal={bb} Δ={d:+d}")
if d <= -64:
    print(f"VERDICT: KEEP_SCALE_CANDIDATE — axis-4 {tag} holds at 10m")
elif d < 0:
    print(f"VERDICT: WEAK_KEEP — {tag} small win; do not bank alone")
else:
    print("VERDICT: REJECT @10m — close axis-4 per-cluster W heuristic (tried v1/w512/w1024)")
PY
log DONE
rm -f "$BASE_OUT/run.pid"
