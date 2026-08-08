#!/usr/bin/env bash
# Confirm dual Pareto winners @10 MiB on core 1 (ref320 may still own core 0).
# Reuse banked kitchen/lm/slim walls from fair_pinned_10MiB if present; else remeasure.
set -uo pipefail
ROOT=/srv/http/fractal_zip
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_dual_10MiB
PRIOR=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_pinned_10MiB
MID=$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
FX2=$ROOT/tools/hutter/fx2-cmix
HUTTER_CORE=${HUTTER_CORE:-1}
QUIET_KITCHEN_S=${QUIET_KITCHEN_S:-931.0}
mkdir -p "$OUT"
LOG=$OUT/fair.log
: >"$LOG"
export FXCM_RECIPE_MIXER_BITMASK=2

log() { printf '%s\n' "$*" | tee -a "$LOG"; }

log "=== fair dual @10 MiB $(date -Is) core=$HUTTER_CORE ==="
log "slice=$MID size=$(wc -c <"$MID") B"
bash "$ROOT/benchmarks/pin_hutter_resources.sh" >/tmp/pin_hutter.log 2>&1 || true

# Import prior kitchen/lm/slim if complete
python3 - <<'PY' | tee -a "$LOG"
from pathlib import Path
prior=Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_pinned_10MiB/fair.log")
out=Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_dual_10MiB/fair.log")
if not prior.is_file():
    print("NO_PRIOR")
else:
    for ln in prior.read_text().splitlines():
        if ln.startswith(("kitchen bytes=","kplus_lm bytes=","kplus_slim bytes=")):
            print(f"IMPORT_{ln}")
            # also write without IMPORT_ prefix for analysis
            with out.open("a") as f:
                # strip already appended by tee from this print — write clean below
                pass
PY
# Cleaner import into log
python3 - <<'PY'
from pathlib import Path
prior=Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_pinned_10MiB/fair.log")
log=Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_dual_10MiB/fair.log")
if prior.is_file():
    with log.open("a") as f:
        for ln in prior.read_text().splitlines():
            if ln.startswith(("kitchen bytes=","kplus_lm bytes=","kplus_slim bytes=")):
                # normalize: drop load1/rc extras after wall_s for parser — keep as-is
                f.write(ln.split(" load1=")[0].split(" rc=")[0] + "\n")
                print("imported", ln.split()[0])
PY

run1() {
  local name=$1 bin=$2
  if grep -q "^${name} bytes=" "$LOG" 2>/dev/null; then
    log "SKIP $name (logged)"
    return 0
  fi
  if [[ ! -x $bin ]]; then
    log "SKIP $name (missing $bin)"
    return 0
  fi
  local fx=$OUT/${name}.fx2 err=$OUT/${name}.err
  rm -f "$fx" "$fx.cmix.temp"
  bash "$ROOT/benchmarks/pin_hutter_resources.sh" >/tmp/pin_hutter.log 2>&1 || true
  local t0 t1 wall bytes rc
  t0=$(date +%s.%N)
  set +e
  taskset -c "$HUTTER_CORE" nice -n 0 "$bin" -c "$DICT" "$MID" "$fx" >/dev/null 2>"$err"
  rc=$?
  set -e
  t1=$(date +%s.%N)
  wall=$(python3 -c "print(f'{$t1-$t0:.2f}')")
  bytes=$(stat -c%s "$fx" 2>/dev/null || echo 0)
  log "$name bytes=$bytes wall_s=$wall rc=$rc"
  [[ "$bytes" -gt 0 ]]
}

# Winners from @1 MiB dual screen
run1 dual_lm_ao16_fp4 "$FX2/run/cmix_opt_dual_lm_ao16_fp4"
run1 dual_lm_ao16 "$FX2/run/cmix_opt_dual_lm_ao16"
run1 dual_lm_aggr "$FX2/run/cmix_opt_dual_lm_aggr"
run1 dual_slim_aggr "$FX2/run/cmix_opt_dual_slim_aggr"
# if prior missing kitchen, measure
run1 kitchen "$FX2/run/cmix_opt_fp2_l64_p2k_cm_ao"
run1 kplus_lm "$FX2/run/cmix_opt_kplus_lm"
run1 kplus_slim "$FX2/run/cmix_opt_kplus_slim"

python3 - <<PY | tee -a "$LOG"
from pathlib import Path
quiet_k=float("$QUIET_KITCHEN_S")
by={}
for ln in Path("$OUT/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    if n.startswith("SKIP") or n.startswith("IMPORT") or n.startswith("imported"): continue
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1].split()[0]))
if "kitchen" not in by:
    print("INCOMPLETE", sorted(by)); raise SystemExit(1)
kb,kw=by["kitchen"]; scale=quiet_k/kw
lb,lw=by.get("kplus_lm",(kb,kw))
sb,sw=by.get("kplus_slim",(kb,kw))
print(f"kitchen {kw:.1f}s scale={scale:.4f} lm={lw:.1f}s slim={sw:.1f}s")
print(f"{'name':22} {'B':>10} {'dB_k':>9} {'dB_lm':>9} {'dB_sl':>9} {'wall':>8} {'quiet':>8} {'xq':>6} {'vs_lm':>6}")
for n,(b,w) in sorted(by.items(), key=lambda kv: (kv[1][1], kv[1][0])):
    q=w*scale
    print(f"{n:22} {b:10d} {b-kb:+9d} {b-lb:+9d} {b-sb:+9d} {w:8.1f} {q:8.1f} {2918.1/q:6.2f} {w/lw:6.3f}")
# pareto tips
best_fast=min((n for n in by if n!="ref320"), key=lambda n: by[n][1])
best_bytes=min(by, key=lambda n: by[n][0])
print(f"BEST_WALL {best_fast} quiet≈{by[best_fast][1]*scale:.1f}s")
print(f"BEST_BYTES {best_bytes} {by[best_bytes][0]}B quiet≈{by[best_bytes][1]*scale:.1f}s")
PY
log DONE_DUAL_10MiB
