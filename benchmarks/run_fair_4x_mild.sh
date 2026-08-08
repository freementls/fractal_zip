#!/usr/bin/env bash
# Fair screen: milder-AO / mem peels @1 MiB, promote survivors @10 MiB.
# Gate vs in-session ctrl: wall ≤ 1.08× ctrl AND bytes < ctrl → promote.
# At 10 MiB require ×320 ≥ 3.9 (4× band) OR (bytes < ctrl AND × ≥ ctrl×0.95).
set -euo pipefail
ROOT=/srv/http/fractal_zip
FX2=$ROOT/tools/hutter/fx2-cmix
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
CAT=$ROOT/benchmarks/lstm_speed_opt_catalog_4x_mild.json
OUT1=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_4x_mild_1MiB
OUT10=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_4x_mild_10MiB
MID1=$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid
MID10=$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid
mkdir -p "$OUT1" "$OUT10"
LOG1=$OUT1/fair.log
: >"$LOG1"
exec > >(tee -a "$LOG1") 2>&1

echo "=== fair 4× mild @1 MiB $(date -Is) ==="
echo "slice=$MID1 size=$(wc -c <"$MID1") B loadavg=$(awk '{print $1}' /proc/loadavg)"
export FXCM_RECIPE_MIXER_BITMASK=2

python3 - <<'PY'
import json, os, subprocess
from pathlib import Path
cat=json.loads(Path("/srv/http/fractal_zip/benchmarks/lstm_speed_opt_catalog_4x_mild.json").read_text())
fx2=Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix")
for c in cat["candidates"]:
    name=c["name"]
    env=os.environ.copy()
    env["OPT_NAME"]=name
    env["OPT_EXTRA_DEFS"]=" ".join(cat["base_defs"]+c["extra_defs"])
    print(f"BUILD {name}", flush=True)
    r=subprocess.run(["make","-j",str(os.cpu_count() or 4),"match3m_fractal_lstm_opt"],
                     cwd=str(fx2), env=env, capture_output=True, text=True)
    ok=(fx2/f"run/cmix_opt_{name}").is_file() and r.returncode==0
    print(f"  {'OK' if ok else 'FAIL'} rc={r.returncode}", flush=True)
    if not ok:
        Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_4x_mild_1MiB/build_"+name+".log").write_text(r.stdout+"\n"+r.stderr)
        raise SystemExit(name)
PY

run1() {
  local out=$1 name=$2 bin=$3 mid=$4
  local fx=$out/${name}.fx2 err=$out/${name}.err
  rm -f "$fx" "$fx.cmix.temp"
  local t0 t1 wall bytes
  t0=$(date +%s.%N)
  taskset -c 0 "$bin" -c "$DICT" "$mid" "$fx" >/dev/null 2>"$err"
  t1=$(date +%s.%N)
  wall=$(python3 -c "print(f'{$t1-$t0:.2f}')")
  bytes=$(stat -c%s "$fx")
  printf '%s bytes=%s wall_s=%s\n' "$name" "$bytes" "$wall"
}

run1 "$OUT1" ref320 "$FX2/run/cmix_match3m_fractalv2_lstm320" "$MID1"
run1 "$OUT1" ctrl "$FX2/run/cmix_opt_ksink_lm" "$MID1"
for name in mild_mem mild_ao8 mild_ao16_fp4 mild_ao8_mem mild_l96 mild_ao8_l96_mem; do
  run1 "$OUT1" "$name" "$FX2/run/cmix_opt_$name" "$MID1"
done

python3 - <<'PY'
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_4x_mild_1MiB/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1]))
rb,rw=by["ref320"]; cb,cw=by["ctrl"]
print(f"ref320 @1 MiB: {rb} B {rw:.2f} s | ctrl ΔB={cb-rb:+d} {cw:.2f} s ({rw/cw:.2f}×)")
print(f"{'name':18} {'×320':>6} {'wall/ctrl':>9} {'ΔB':>9} {'vs_ctrl':>9}")
promote=["ctrl"]
for n,(b,w) in sorted(by.items(), key=lambda kv: (kv[1][0]-cb, kv[1][1])):
    if n=="ref320": continue
    sp=rw/w; rel=w/cw; db=b-rb; dctrl=b-cb
    mark=""
    if n!="ctrl" and w<=cw*1.08 and b<cb:
        mark=" ← PROMOTE"
        promote.append(n)
    elif n!="ctrl" and b<cb and w<=cw*1.15:
        mark=" ← near"
    print(f"{n:18} {sp:6.2f} {rel:9.3f} {db:+9d} {dctrl:+9d}{mark}")
# always take best byte among near-speed
cands=[(n,b,w) for n,(b,w) in by.items() if n not in ("ref320","ctrl") and w<=cw*1.12]
if cands:
    best=min(cands, key=lambda t: t[1])
    if best[0] not in promote:
        promote.append(best[0])
print("PROMOTE_10MiB", ",".join(promote))
Path("/tmp/fair_4x_mild_promote.txt").write_text(",".join(promote))
PY
echo DONE_1MiB

PROMOTE=$(cat /tmp/fair_4x_mild_promote.txt)
echo "=== fair 4× mild @10 MiB $(date -Is) promote=$PROMOTE ==="
LOG10=$OUT10/fair.log
: >"$LOG10"
# also tee 10MiB into its own log
exec > >(tee -a "$LOG10") 2>&1
echo "=== fair 4× mild @10 MiB $(date -Is) ==="
echo "slice=$MID10 size=$(wc -c <"$MID10") B loadavg=$(awk '{print $1}' /proc/loadavg)"
echo "promote=$PROMOTE"

run1 "$OUT10" ref320 "$FX2/run/cmix_match3m_fractalv2_lstm320" "$MID10"
IFS=',' read -ra ARMS <<<"$PROMOTE"
for name in "${ARMS[@]}"; do
  if [[ "$name" == ctrl ]]; then
    run1 "$OUT10" ctrl "$FX2/run/cmix_opt_ksink_lm" "$MID10"
  else
    run1 "$OUT10" "$name" "$FX2/run/cmix_opt_$name" "$MID10"
  fi
done

python3 - <<'PY'
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_4x_mild_10MiB/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1]))
rb,rw=by["ref320"]; cb,cw=by.get("ctrl",(None,None))
print(f"ref320 @10 MiB: {rb} B {rw:.2f} s  (4× ≤ {rw/4:.2f} s)")
if cb:
    print(f"ctrl: {cb} B ΔB={cb-rb:+d} {cw:.2f} s ({rw/cw:.2f}×)")
print(f"{'name':18} {'×320':>6} {'ΔB':>9} {'vs_ctrl':>10} {'wall_s':>8}")
for n,(b,w) in sorted(by.items(), key=lambda kv: (kv[1][0]-rb, -rw/kv[1][1])):
    sp=rw/w
    vs=(b-cb) if cb is not None else 0
    mark=""
    if n!="ref320" and sp>=3.9 and (cb is None or b<=cb):
        mark=" ← KEEP (≥3.9×)"
    elif n!="ref320" and sp>=3.9:
        mark=" ← 4× band"
    print(f"{n:18} {sp:6.2f} {b-rb:+9d} {vs:+10d} {w:8.1f}{mark}")
PY
echo DONE_10MiB
