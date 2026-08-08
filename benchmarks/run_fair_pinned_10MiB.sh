#!/usr/bin/env bash
# Controlled-share fair @10 MiB: Hutter on core 0, fzpaq/paq on 2-3.
# Extrapolate quiet walls via kitchen scale (banked quiet kitchen=931 s).
# Avoid exec>tee + set -e (known footgun that killed kitchen+ mid-run).
set -uo pipefail
ROOT=/srv/http/fractal_zip
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_pinned_10MiB
MID=$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
FX2=$ROOT/tools/hutter/fx2-cmix
HUTTER_CORE=${HUTTER_CORE:-0}
QUIET_KITCHEN_S=${QUIET_KITCHEN_S:-931.0}
mkdir -p "$OUT"
LOG=$OUT/fair.log
: >"$LOG"

log() { printf '%s\n' "$*" | tee -a "$LOG"; }

log "=== fair pinned @10 MiB $(date -Is) ==="
log "hutter_core=$HUTTER_CORE quiet_kitchen_s=$QUIET_KITCHEN_S"
log "slice=$MID size=$(wc -c <"$MID") B loadavg=$(awk '{print $1}' /proc/loadavg)"
export FXCM_RECIPE_MIXER_BITMASK=2

bash "$ROOT/benchmarks/pin_hutter_resources.sh" >/tmp/pin_hutter.log 2>&1 || true

run1() {
  local name=$1 bin=$2
  if [[ ! -x $bin ]]; then
    log "SKIP $name (missing $bin)"
    return 0
  fi
  if grep -q "^${name} bytes=" "$LOG" 2>/dev/null; then
    log "SKIP $name (already logged)"
    return 0
  fi
  local fx=$OUT/${name}.fx2 err=$OUT/${name}.err
  rm -f "$fx" "$fx.cmix.temp"
  bash "$ROOT/benchmarks/pin_hutter_resources.sh" >/tmp/pin_hutter.log 2>&1 || true
  local t0 t1 wall bytes rc load
  t0=$(date +%s.%N)
  set +e
  taskset -c "$HUTTER_CORE" nice -n 0 "$bin" -c "$DICT" "$MID" "$fx" >/dev/null 2>"$err"
  rc=$?
  set -e
  t1=$(date +%s.%N)
  wall=$(python3 -c "print(f'{$t1-$t0:.2f}')")
  bytes=$(stat -c%s "$fx" 2>/dev/null || echo 0)
  load=$(awk '{print $1}' /proc/loadavg)
  log "$name bytes=$bytes wall_s=$wall load1=$load rc=$rc"
  [[ "$bytes" -gt 0 ]]
}

# Fast arms first (answer 4× sooner), then kitchen scale anchor, then ref320 for ΔB.
# Top kitchen+ promotes @1 MiB: all ~640 s proj, lm ~677 s, fxr8 ~801 s.
run1 kplus_all "$FX2/run/cmix_opt_kplus_all"
run1 kplus_lm "$FX2/run/cmix_opt_kplus_lm"
run1 kplus_fxr8 "$FX2/run/cmix_opt_kplus_fxr8"
run1 kitchen "$FX2/run/cmix_opt_fp2_l64_p2k_cm_ao"
run1 kplus_slim "$FX2/run/cmix_opt_kplus_slim"
run1 ref320 "$FX2/run/cmix_match3m_fractalv2_lstm320"

python3 - <<PY | tee -a "$LOG"
from pathlib import Path
quiet_k = float("$QUIET_KITCHEN_S")
quiet_ref = 2918.1
by={}
for ln in Path("$OUT/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    if n.startswith("SKIP"): continue
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1].split()[0]))
if "kitchen" not in by or "ref320" not in by:
    print("INCOMPLETE need kitchen+ref320; have", sorted(by))
else:
    rb,rw=by["ref320"]
    kb,kw=by["kitchen"]
    scale = quiet_k / kw
    print(f"pinned session: ref320 {rw:.1f} s kitchen {kw:.1f} s scale→quiet={scale:.4f}")
    print(f"true 4× vs quiet LSTM320 ≤ {quiet_ref/4:.1f} s; kitchen quiet={quiet_k:.1f} s")
    print(f"{'name':14} {'×sess':>6} {'wall_s':>8} {'quiet≈':>8} {'×quiet':>7} {'ΔB':>9}")
    for n,(b,w) in sorted(by.items(), key=lambda kv: kv[1][1]):
        q=w*scale
        print(f"{n:14} {rw/w:6.2f} {w:8.1f} {q:8.1f} {quiet_ref/q:7.2f} {b-rb:+9d}")
    best=min((n for n in by if n!="ref320"), key=lambda n: by[n][1]*scale)
    bq=by[best][1]*scale
    print(f"BEST_QUIET_PROJ {best} ~{bq:.1f} s ({quiet_ref/bq:.2f}×) need≤{quiet_ref/4:.1f} s for 4×")
    # also vs kitchen relative (no quiet assumption)
    print(f"vs kitchen wall: ", end="")
    print(", ".join(f"{n}={by[n][1]/kw:.3f}×" for n in sorted(by, key=lambda n: by[n][1]) if n!="ref320"))
PY
log DONE_PINNED_10MiB
