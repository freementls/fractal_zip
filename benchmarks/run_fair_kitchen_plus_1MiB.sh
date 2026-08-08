#!/usr/bin/env bash
# Same-session fair @1 MiB vs kitchen. Promote arms with wall ≤ 0.95× kitchen
# (faster) or (wall ≤ 1.02× kitchen AND fewer bytes). Then note for 10 MiB.
set -euo pipefail
ROOT=/srv/http/fractal_zip
FX2=$ROOT/tools/hutter/fx2-cmix
MID=$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_kitchen_plus_1MiB
CAT=$ROOT/benchmarks/lstm_speed_opt_catalog_kitchen_plus.json
mkdir -p "$OUT"
LOG=$OUT/fair.log
: >"$LOG"
exec > >(tee -a "$LOG") 2>&1

echo "=== fair kitchen+ @1 MiB $(date -Is) ==="
echo "slice=$MID size=$(wc -c <"$MID") B loadavg=$(awk '{print $1}' /proc/loadavg)"
echo "goal: beat kitchen toward true 4× (quiet 10 MiB wall ≤729.5 s; kitchen quiet=931 s)"
export FXCM_RECIPE_MIXER_BITMASK=2

python3 - <<'PY'
import json, os, subprocess
from pathlib import Path
cat=json.loads(Path("/srv/http/fractal_zip/benchmarks/lstm_speed_opt_catalog_kitchen_plus.json").read_text())
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
        Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_kitchen_plus_1MiB/build_"+name+".log").write_text(r.stdout+"\n"+r.stderr)
        raise SystemExit(name)
PY

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
  printf '%s bytes=%s wall_s=%s\n' "$name" "$bytes" "$wall"
}

# Prefer banked kitchen bin if present; also run rebuilt kplus_ctrl
run1 ref320 "$FX2/run/cmix_match3m_fractalv2_lstm320"
if [[ -x $FX2/run/cmix_opt_fp2_l64_p2k_cm_ao ]]; then
  run1 kitchen "$FX2/run/cmix_opt_fp2_l64_p2k_cm_ao"
fi
for name in kplus_ctrl kplus_mid kplus_slim kplus_mid_slim kplus_lm kplus_lm_mid kplus_fxr8 kplus_all; do
  run1 "$name" "$FX2/run/cmix_opt_$name"
done

python3 - <<'PY'
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_kitchen_plus_1MiB/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1]))
# kitchen reference: prefer banked 'kitchen', else kplus_ctrl
kname="kitchen" if "kitchen" in by else "kplus_ctrl"
kb,kw=by[kname]
rb,rw=by["ref320"]
print(f"ref320 @1 MiB: {rb} B {rw:.2f} s ({rw/kw:.2f}× vs {kname})")
print(f"{kname}: {kb} B {kw:.2f} s ΔB={kb-rb:+d}")
print(f"{'name':16} {'×320':>6} {'vs_k':>6} {'ΔB':>9} {'vs_k_B':>9} {'wall_s':>8}")
promote=[]
for n,(b,w) in sorted(by.items(), key=lambda kv: (kv[1][1]/kw, kv[1][0]-kb)):
    if n=="ref320": continue
    sp=rw/w; rel=w/kw; db=b-rb; dk=b-kb
    mark=""
    if n not in (kname,"kplus_ctrl") and (w<=kw*0.95 or (w<=kw*1.02 and b<kb)):
        mark=" ← PROMOTE"
        promote.append(n)
    print(f"{n:16} {sp:6.2f} {rel:6.3f} {db:+9d} {dk:+9d} {w:8.1f}{mark}")
# always promote fastest
fast=min((n for n in by if n!="ref320"), key=lambda n: by[n][1])
if fast not in promote and fast not in (kname,):
    promote.append(fast)
print("PROMOTE_10MiB", ",".join(promote) if promote else kname)
Path("/tmp/fair_kitchen_plus_promote.txt").write_text(",".join(promote) if promote else kname)
# projected quiet 10 MiB wall if scales like kitchen 124s@1M→931s@10M (from prior 1m leaderboard kitchen 124.4s)
# use this session kitchen wall as scale base
scale_10 = 931.0 / kw  # map this-session kitchen → historical quiet 10 MiB
print(f"proj quiet@10 MiB (scale kitchen→931 s):")
for n,(b,w) in sorted(by.items(), key=lambda kv: kv[1][1]):
    if n=="ref320": continue
    pq=w*scale_10
    print(f"  {n:16} ~{pq:.0f} s  ({2918.1/pq:.2f}× quiet)  ΔB={b-rb:+d}")
PY
echo DONE_1MiB
