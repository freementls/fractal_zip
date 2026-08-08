#!/usr/bin/env bash
set -euo pipefail
ROOT=/srv/http/fractal_zip
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_quiet_10MiB
MID=$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
FX2=$ROOT/tools/hutter/fx2-cmix
mkdir -p "$OUT"
LOG=$OUT/fair.log
exec > >(tee -a "$LOG") 2>&1
echo "=== wait for loadavg(1m)<2.5 $(date -Is) ==="
while awk '{exit !($1<2.5)}' /proc/loadavg; do
  : # already quiet
  break
done
while ! awk '{exit !($1<2.5)}' /proc/loadavg; do
  echo "$(date +%H:%M:%S) load=$(awk '{print $1}' /proc/loadavg) — sleep 60"
  sleep 60
done
echo "GO load=$(awk '{print $1}' /proc/loadavg) $(date -Is)"
export FXCM_RECIPE_MIXER_BITMASK=2
run1() {
  local name=$1 bin=$2
  local fx=$OUT/${name}.fx2 err=$OUT/${name}.err
  rm -f "$fx" "$fx.cmix.temp"
  local t0 t1 wall bytes
  t0=$(date +%s.%N)
  taskset -c 0 "$bin" -c "$DICT" "$MID" "$fx" >/dev/null 2>"$err"
  t1=$(date +%s.%N)
  wall=$(python3 -c "print(f'{$t1-$t0:.2f}')")
  bytes=$(stat -c%s "$fx")
  echo "$name bytes=$bytes wall_s=$wall load=$(awk '{print $1}' /proc/loadavg)"
}
run1 ref320 "$FX2/run/cmix_match3m_fractalv2_lstm320"
run1 kitchen "$FX2/run/cmix_opt_fp2_l64_p2k_cm_ao"
run1 ctrl_lm "$FX2/run/cmix_opt_iterate4x_lm"
run1 taxcut "$FX2/run/cmix_opt_iterate_taxcut"
python3 - <<'PY'
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_quiet_10MiB/fair.log").read_text().splitlines():
  if "bytes=" not in ln or "wall_s=" not in ln: continue
  n=ln.split()[0]
  by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1]))
rb,rw=by["ref320"]
print(f"QUIET ref320 @10 MiB: {rb} B {rw:.2f} s  (4× ≤ {rw/4:.2f} s)")
print(f"{'name':12} {'×320':>6} {'ΔB':>9} {'wall_s':>8}")
for n,(b,w) in sorted(by.items(), key=lambda kv: -rw/kv[1][1]):
  print(f"{n:12} {rw/w:6.2f} {b-rb:+9d} {w:8.1f}")
PY
echo DONE_QUIET
