#!/usr/bin/env bash
# Next attacks after Phase 6 SCALE_KEEP (−224@100MB, still 174KB short of 1.2% gate):
#   A) Static MIX_NUMLEN (FXCM_RECIPE_MIXER_BITMASK=2, no causal dens/zoom gate)
#      vs the banked causal recipe — historical mixnum was −49@10MB always-on;
#      causal is −34@10MB / −224@100MB. Check whether always-on beats causal.
#   B) Real axis-3 LSTM budget gate: build match3m_fractalv2_lstm256, then on the
#      sentinel-injected 1MB slice compare full-LSTM (no map) vs gated (.fxpm with
#      top-15% hard = budget_tier=1). Records bytes AND wall time.
#
# Waits for MemAvailable>=12GiB and no other cmix before each heavy step.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_p6b"
LOG="$OUT/next_attacks.log"
CMIX="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
SENT1="${SENT1:-/tmp/enwik_1m_sentinel.mid}"
FXPM="$ROOT/benchmarks/.ladder_cache/fractal_p5/tuned_1m.fxpm"
mkdir -p "$OUT"

# Log to the file and to stderr only — never stdout (stdout is captured by $(run_c)).
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

wait_slot() {
  local tag="$1" i
  for i in $(seq 1 360); do
    avail=$(awk '/MemAvailable/{print int($2/1024)}' /proc/meminfo)
    n=$(ps -eo comm= | rg -c '^cmix' || true)
    n=${n:-0}
    # Alone: need ≥12GiB. With one other cmix (e.g. user fzpaqbench): need ≥18GiB
    # so our ~6GiB job still fits. Never pile on when ≥2 cmix already running.
    if (( n == 0 && avail >= 12288 )) || (( n == 1 && avail >= 18432 )); then
      log "slot_ok $tag avail=${avail}MiB cmix=$n"
      return 0
    fi
    log "wait_slot $tag avail=${avail}MiB cmix=$n"
    sleep 60
  done
  log "wait_slot TIMEOUT $tag"
  return 2
}

run_c() {
  local tag="$1" bin="$2" input="$3" out="$4"
  shift 4
  local t0 t1 sz
  t0=$(date +%s)
  env "$@" "$bin" -c "$DICT" "$input" "$out" >/dev/null 2>"$OUT/${tag}.time" || true
  t1=$(date +%s)
  sz=$(stat -c%s "$out" 2>/dev/null || echo 0)
  log "$tag bytes=$sz seconds=$((t1-t0))"
  printf '%s\n' "$sz"
}

{
  log "START_next_attacks"

  # ----- A: static vs causal MIX_NUMLEN @1MB -----
  wait_slot A1m
  B1=$(run_c A_1m_base "$CMIX" "$SL1" "$OUT/A_1m_base.fx2")
  C1=$(run_c A_1m_causal "$CMIX" "$SL1" "$OUT/A_1m_causal.fx2" \
    FXCM_RECIPE_AXIS1_CAUSAL=1 FXCM_AXIS1_DENS_MIN=1 FXCM_AXIS1_ZOOM_MIN=1 FXCM_AXIS1_MASK_WHEN_FIRED=2)
  S1=$(run_c A_1m_static "$CMIX" "$SL1" "$OUT/A_1m_static.fx2" \
    FXCM_RECIPE_MIXER_BITMASK=2)
  log "A_1m summary base=$B1 causal=$C1 (Δ=$((C1-B1))) static=$S1 (Δ=$((S1-B1)))"

  # Promote static to 10MB only if it beats causal at 1MB (or ties within 8B and we want scale check)
  if (( S1 <= C1 )); then
    wait_slot A10m
    B10=$(run_c A_10m_base "$CMIX" "$SL10" "$OUT/A_10m_base.fx2")
    C10=$(run_c A_10m_causal "$CMIX" "$SL10" "$OUT/A_10m_causal.fx2" \
      FXCM_RECIPE_AXIS1_CAUSAL=1 FXCM_AXIS1_DENS_MIN=1 FXCM_AXIS1_ZOOM_MIN=1 FXCM_AXIS1_MASK_WHEN_FIRED=2)
    S10=$(run_c A_10m_static "$CMIX" "$SL10" "$OUT/A_10m_static.fx2" \
      FXCM_RECIPE_MIXER_BITMASK=2)
    log "A_10m summary base=$B10 causal=$C10 (Δ=$((C10-B10))) static=$S10 (Δ=$((S10-B10)))"
    if (( S10 < C10 )); then
      log "A_VERDICT: STATIC_BEATS_CAUSAL @10m — promote static MIX_NUMLEN"
    else
      log "A_VERDICT: keep causal (static Δ=$((S10-B10)) causal Δ=$((C10-B10)))"
    fi
  else
    log "A_VERDICT: static worse @1m (Δ=$((S1-B1)) vs causal $((C1-B1))); skip 10m"
  fi

  # ----- B: LSTM budget gate -----
  wait_slot Bbuild
  log "building match3m_fractalv2_lstm256"
  make -C "$ROOT/tools/hutter/fx2-cmix" -j"$(nproc)" match3m_fractalv2_lstm256 \
    >"$OUT/build_lstm256.log" 2>&1 || { log "BUILD_FAIL"; exit 1; }
  LSTM="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm256"
  [[ -x "$LSTM" ]] || { log "missing $LSTM"; exit 1; }
  [[ -s "$SENT1" ]] || { log "missing sentinel slice $SENT1"; exit 1; }
  [[ -s "$FXPM" ]] || { log "missing $FXPM"; exit 1; }

  wait_slot B1m
  # no-map = full LSTM (gate off)
  Lfull=$(run_c B_1m_lstm_full "$LSTM" "$SENT1" "$OUT/B_1m_lstm_full.fx2")
  # with map = gated (easy articles skip LSTM)
  Lgated=$(run_c B_1m_lstm_gated "$LSTM" "$SENT1" "$OUT/B_1m_lstm_gated.fx2" \
    FXCM_PROFILE_MAP_PATH="$FXPM")
  # recipe-0 fractalv2b on same sentinel for reference
  Lbase=$(run_c B_1m_base_sent "$CMIX" "$SENT1" "$OUT/B_1m_base_sent.fx2")
  log "B_1m summary base_sent=$Lbase lstm_full=$Lfull (Δ=$((Lfull-Lbase))) lstm_gated=$Lgated (Δ=$((Lgated-Lbase)))"
  full_t=$(rg -o '^[0-9.]+' "$OUT/B_1m_lstm_full.time" 2>/dev/null | head -1 || true)
  gated_t=$(rg -o '^[0-9.]+' "$OUT/B_1m_lstm_gated.time" 2>/dev/null | head -1 || true)
  log "B_1m times (from .time files if present) full=${full_t:-?} gated=${gated_t:-?}"
  if (( Lgated <= Lfull )); then
    log "B_VERDICT: gated ≤ full bytes — KEEP candidate (check time speedup in log lines above)"
  else
    log "B_VERDICT: gated costs $((Lgated-Lfull)) B vs full — only keep if wall speedup justifies for 40h budget"
  fi

  log "DONE_next_attacks"
} 2>&1 | tee -a "$LOG"
