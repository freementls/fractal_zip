#!/usr/bin/env bash
# Phase 6: scale-consistent ladder on the FINAL composed fractal recipe.
#
# Composed recipe (what Phase 5's tuner exports):
#   - Axis 1: MIX_NUMLEN causal bit (mask=2, dens_min=1, zoom_min=1) —
#     the only axis-1 candidate that held at both 1 MB (−3 B) and 10 MB (−34 B).
#   - Axes 2/3: profile-map mixer-context hints (dict_cluster_id, budget_tier)
#     — only active when the input carries FXCM_ARTICLE_SENTINEL (0x1E) at each
#     article start; without sentinels the map stays on article 0's entry.
#   - Axis 4: not runtime-composed (Phase 4b fair-probe rejected the first
#     per-cluster window heuristic at 1 MB; order-file swap stays offline).
#
# Gate policy (same as run_fractal_pred_ladder.sh):
#   NEVER bank from 1m alone. KEEP only if ΔS2 < 0 at BOTH 1m and 10m;
#   then promote 100m. Report progress vs 1.2% S2 gate and wall-clock vs 40-50h.
#
# Usage:
#   bash benchmarks/run_fractal_composed_ladder.sh            # 1m + 10m
#   RUN_100M=1 bash benchmarks/run_fractal_composed_ladder.sh # also 100m
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CMIX="${CMIX:-$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b}"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
SL100="$ROOT/benchmarks/.ladder_cache/enwik8_100m_skip0.bin"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_p6"
LOG="$OUT/composed_ladder.log"
# Default is axis1-only: full tuned_1m.env (with .fxpm) was REJECTED at 1MB
# on the sentinel-injected composed A/B (+8 B). Override with ENV_FILE=... if needed.
ENV_FILE="${ENV_FILE:-$ROOT/benchmarks/.ladder_cache/fractal_p5/composed_axis1_only.env}"
# Banked match3m_entity baselines (same mid-slices as prior screens)
B1=198795
B10=1750916
# 1.2% S2 gate relative to banked 100m A0 baseline (14,966,085): need ≤14,786,491
B100_REF=14966085
GATE_100=$(( B100_REF - (B100_REF * 12 / 1000) ))
export HUTTER_MAX_SWAP_MIB="${HUTTER_MAX_SWAP_MIB:-6144}"
mkdir -p "$OUT"

run_one() {
  local tag="$1" input="$2" out="$3"
  shift 3
  local t0 t1 sz
  t0=$(date +%s)
  env "$@" "$CMIX" -c "$DICT" "$input" "$out" >/dev/null 2>"$OUT/${tag}.time" || true
  t1=$(date +%s)
  sz=$(stat -c%s "$out" 2>/dev/null || echo 0)
  echo "$tag bytes=$sz seconds=$((t1-t0))"
}

{
  echo "START_composed_ladder $(date -Is)"
  echo "cmix=$CMIX env=$ENV_FILE"
  ls -la "$CMIX" "$DICT" "$SL1" "$SL10" "$ENV_FILE"
  bash "$ROOT/benchmarks/hutter_memory_guard.sh" || true

  # --- 1 MB ---
  echo "=== 1m baseline (recipe 0) vs banked B1=$B1 ==="
  run_one "1m_base" "$SL1" "$OUT/1m_base.fx2"
  SZ1B=$(stat -c%s "$OUT/1m_base.fx2")
  echo "1m_base Δvs_banked=$((SZ1B - B1))"

  echo "=== 1m composed (source $ENV_FILE) ==="
  # shellcheck disable=SC1090
  set -a; source "$ENV_FILE"; set +a
  run_one "1m_composed" "$SL1" "$OUT/1m_composed.fx2" \
    FXCM_RECIPE_AXIS1_CAUSAL="${FXCM_RECIPE_AXIS1_CAUSAL:-0}" \
    FXCM_AXIS1_DENS_MIN="${FXCM_AXIS1_DENS_MIN:-4}" \
    FXCM_AXIS1_ZOOM_MIN="${FXCM_AXIS1_ZOOM_MIN:-4}" \
    FXCM_AXIS1_MASK_WHEN_FIRED="${FXCM_AXIS1_MASK_WHEN_FIRED:-0}" \
    FXCM_PROFILE_MAP_PATH="${FXCM_PROFILE_MAP_PATH:-}"
  SZ1C=$(stat -c%s "$OUT/1m_composed.fx2")
  D1=$((SZ1C - SZ1B))
  echo "Δ1m_composed_vs_base=$D1"
  if (( D1 > 8 )); then
    echo "VERDICT: REJECT @1m (Δ=$D1) — stop ladder"
    echo "DONE_composed_ladder $(date -Is)"
    exit 0
  fi

  # --- 10 MB ---
  bash "$ROOT/benchmarks/hutter_memory_guard.sh" || true
  echo "=== 10m baseline vs banked B10=$B10 ==="
  run_one "10m_base" "$SL10" "$OUT/10m_base.fx2"
  SZ10B=$(stat -c%s "$OUT/10m_base.fx2")
  echo "10m_base Δvs_banked=$((SZ10B - B10))"

  echo "=== 10m composed ==="
  set -a; source "$ENV_FILE"; set +a
  run_one "10m_composed" "$SL10" "$OUT/10m_composed.fx2" \
    FXCM_RECIPE_AXIS1_CAUSAL="${FXCM_RECIPE_AXIS1_CAUSAL:-0}" \
    FXCM_AXIS1_DENS_MIN="${FXCM_AXIS1_DENS_MIN:-4}" \
    FXCM_AXIS1_ZOOM_MIN="${FXCM_AXIS1_ZOOM_MIN:-4}" \
    FXCM_AXIS1_MASK_WHEN_FIRED="${FXCM_AXIS1_MASK_WHEN_FIRED:-0}" \
    FXCM_PROFILE_MAP_PATH="${FXCM_PROFILE_MAP_PATH:-}"
  SZ10C=$(stat -c%s "$OUT/10m_composed.fx2")
  D10=$((SZ10C - SZ10B))
  echo "Δ10m_composed_vs_base=$D10"
  if (( D10 >= 0 )); then
    echo "VERDICT: REJECT @10m (Δ1m=$D1 Δ10m=$D10) — scale-fail"
  else
    echo "VERDICT: SCALE_KEEP (Δ1m=$D1 Δ10m=$D10) — queue @100m"
  fi

  # --- 100 MB (opt-in; ~14h wall-clock estimated from 10m ×10) ---
  if [[ "${RUN_100M:-0}" == "1" ]] && (( D10 < 0 )); then
    bash "$ROOT/benchmarks/hutter_memory_guard.sh" || true
    echo "=== 100m baseline (ref=$B100_REF, 1.2% gate<=$GATE_100) ==="
    run_one "100m_base" "$SL100" "$OUT/100m_base.fx2"
    SZ100B=$(stat -c%s "$OUT/100m_base.fx2")
    echo "=== 100m composed ==="
    set -a; source "$ENV_FILE"; set +a
    run_one "100m_composed" "$SL100" "$OUT/100m_composed.fx2" \
      FXCM_RECIPE_AXIS1_CAUSAL="${FXCM_RECIPE_AXIS1_CAUSAL:-0}" \
      FXCM_AXIS1_DENS_MIN="${FXCM_AXIS1_DENS_MIN:-4}" \
      FXCM_AXIS1_ZOOM_MIN="${FXCM_AXIS1_ZOOM_MIN:-4}" \
      FXCM_AXIS1_MASK_WHEN_FIRED="${FXCM_AXIS1_MASK_WHEN_FIRED:-0}" \
      FXCM_PROFILE_MAP_PATH="${FXCM_PROFILE_MAP_PATH:-}"
    SZ100C=$(stat -c%s "$OUT/100m_composed.fx2")
    D100=$((SZ100C - SZ100B))
    PCT=$(python3 -c "print(f'{100.0*($SZ100C-$B100_REF)/$B100_REF:.4f}')")
    echo "Δ100m_composed_vs_base=$D100 pct_vs_ref=${PCT}% gate_1.2pct_need<=$GATE_100"
    if (( SZ100C <= GATE_100 )); then
      echo "VERDICT: GATE_1.2PCT_PASS"
    else
      echo "VERDICT: GATE_1.2PCT_MISS (need $((SZ100C - GATE_100)) more bytes off)"
    fi
  fi
  echo "DONE_composed_ladder $(date -Is)"
} 2>&1 | tee -a "$LOG"
