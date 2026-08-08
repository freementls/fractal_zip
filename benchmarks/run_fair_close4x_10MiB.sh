#!/usr/bin/env bash
# Confirm close4x winners @10 MiB on core 0. Import dual tips for scale.
set -uo pipefail
ROOT=/srv/http/fractal_zip
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_close4x_10MiB
PRIOR=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_dual_10MiB
MID=$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
FX2=$ROOT/tools/hutter/fx2-cmix
HUTTER_CORE=${HUTTER_CORE:-0}
QUIET_K=${QUIET_K:-931.0}
mkdir -p "$OUT"
LOG=$OUT/fair.log
: >"$LOG"
export FXCM_RECIPE_MIXER_BITMASK=2
log() { printf '%s\n' "$*" | tee -a "$LOG"; }

log "=== fair close4x @10 MiB $(date -Is) core=$HUTTER_CORE ==="

# Import dual tips + kitchen
python3 - <<'PY'
from pathlib import Path
prior=Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_dual_10MiB/fair.log")
log=Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_close4x_10MiB/fair.log")
want=("kitchen bytes=","dual_lm_aggr bytes=","dual_lm_ao16 bytes=","kplus_lm bytes=")
with log.open("a") as f:
    for ln in prior.read_text().splitlines():
        if any(ln.startswith(w) for w in want):
            clean=ln.split(" load1=")[0].split(" rc=")[0].split(" #")[0]
            # normalize names
            if clean.startswith("dual_lm_aggr"):
                clean="aggr "+clean.split(" ",1)[1]
            elif clean.startswith("dual_lm_ao16 "):
                clean="ao16 "+clean.split(" ",1)[1]
            f.write(clean+"\n")
            print("imported", clean.split()[0])
PY

run1() {
  local name=$1 bin=$2
  grep -q "^${name} bytes=" "$LOG" 2>/dev/null && { log "SKIP $name"; return 0; }
  [[ -x $bin ]] || { log "SKIP $name missing"; return 0; }
  local fx=$OUT/${name}.fx2 err=$OUT/${name}.err
  rm -f "$fx" "$fx.cmix.temp"
  bash "$ROOT/benchmarks/pin_hutter_resources.sh" >/tmp/pin_hutter.log 2>&1 || true
  for p in $(pgrep -x paq8px || true); do
    cmd=$(tr '\0' ' ' </proc/$p/cmdline 2>/dev/null || true)
    case "$cmd" in *o1.paq8px*|*o2.paq8px*|*o3.paq8px*) taskset -cp 14-15 "$p" 2>/dev/null || true ;; esac
  done
  local t0 t1 wall bytes rc
  t0=$(date +%s.%N)
  set +e
  taskset -c "$HUTTER_CORE" nice -n 0 "$bin" -c "$DICT" "$MID" "$fx" >/dev/null 2>"$err"
  rc=$?
  set +e
  t1=$(date +%s.%N)
  wall=$(python3 -c "print(f'{$t1-$t0:.2f}')")
  bytes=$(stat -c%s "$fx" 2>/dev/null || echo 0)
  log "$name bytes=$bytes wall_s=$wall rc=$rc"
  # flush: also append is via tee already
}

# Priority order: pareto stars first
run1 c4_ao16_aggr "$FX2/run/cmix_opt_c4_ao16_aggr"
run1 c4_ao16_slim "$FX2/run/cmix_opt_c4_ao16_slim"
run1 c4_ao16_novel2 "$FX2/run/cmix_opt_c4_ao16_novel2"
run1 c4_aggr_novel2 "$FX2/run/cmix_opt_c4_aggr_novel2"
run1 c4_ao8_aggr "$FX2/run/cmix_opt_c4_ao8_aggr"

python3 - <<PY | tee -a "$LOG"
from pathlib import Path
quiet_k=float("$QUIET_K")
by={}
for ln in Path("$OUT/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    if n.startswith("SKIP") or n.startswith("imported"): continue
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1].split()[0]))
kb,kw=by["kitchen"]; scale=quiet_k/kw
ab,aw=by["aggr"]; ob,ow=by["ao16"]
print(f"kitchen {kw:.1f}s scale={scale:.4f} aggr={aw:.1f}s ao16={ow:.1f}s")
print(f"true 4× ≤729.5 s; prior aggr quiet was ~755 s")
print(f"{'name':22} {'B':>10} {'dB_k':>9} {'dB_ag':>9} {'dB_ao':>9} {'wall':>8} {'quiet':>8} {'xq':>6}")
for n,(b,w) in sorted(by.items(), key=lambda kv: kv[1][1]):
    q=w*scale
    mark=" ←4×" if q<=729.5 else ""
    print(f"{n:22} {b:10d} {b-kb:+9d} {b-ab:+9d} {b-ob:+9d} {w:8.1f} {q:8.1f} {2918.1/q:6.2f}{mark}")
best=min((n for n in by if n.startswith("c4_")), key=lambda n: by[n][1]*scale)
print(f"BEST_C4 {best} quiet≈{by[best][1]*scale:.1f}s gap4x={by[best][1]*scale-729.5:+.1f}s")
bb=min((n for n in by if n.startswith("c4_")), key=lambda n: by[n][0])
print(f"BEST_C4_BYTES {bb} {by[bb][0]}B quiet≈{by[bb][1]*scale:.1f}s")
PY
log DONE_CLOSE4X_10MiB
