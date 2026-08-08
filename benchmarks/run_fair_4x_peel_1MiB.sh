#!/usr/bin/env bash
# Peel kitchen tax @1 MiB. Gate vs in-session ctrl (not absolute × — load varies).
# Keep wall ≤ 1.05× ctrl and beat ctrl on bytes → promote to 10 MiB.
set -euo pipefail
ROOT=/srv/http/fractal_zip
FX2=$ROOT/tools/hutter/fx2-cmix
MID=$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_4x_peel_1MiB
CAT=$ROOT/benchmarks/lstm_speed_opt_catalog_4x_peel.json
LOG=$OUT/fair.log
mkdir -p "$OUT"
: >"$LOG"
exec > >(tee -a "$LOG") 2>&1

echo "=== fair 4× peel @1 MiB $(date -Is) ==="
echo "slice=${MID} size=$(wc -c <"$MID") B loadavg=$(awk '{print $1}' /proc/loadavg)"
export FXCM_RECIPE_MIXER_BITMASK=2

python3 - <<'PY'
import json, os, subprocess
from pathlib import Path
cat=json.loads(Path("/srv/http/fractal_zip/benchmarks/lstm_speed_opt_catalog_4x_peel.json").read_text())
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
        Path(f"/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_4x_peel_1MiB/build_{name}.log").write_text(r.stdout+"\n"+r.stderr)
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

# ctrl = banked iterate4x (same as ksink_lm / ctrl_v4_lm)
run1 ref320 "$FX2/run/cmix_match3m_fractalv2_lstm320"
run1 ctrl "$FX2/run/cmix_opt_ksink_lm"
for name in peel_ppmd8k peel_match3m peel_cmscale1 peel_no_ao peel_fp4 peel_lm_soft peel_l96 peel_all_mem; do
  run1 "$name" "$FX2/run/cmix_opt_$name"
done

python3 - <<'PY'
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_4x_peel_1MiB/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1]))
rb,rw=by["ref320"]
cb,cw=by["ctrl"]
print(f"ref320 {rb} B {rw:.2f} s | ctrl {cb} B {cw:.2f} s ({rw/cw:.2f}×) ΔB={cb-rb:+d}")
print(f"{'name':16} {'×320':>6} {'×ctrl':>6} {'ΔB':>9} {'ΔB_vs_ctrl':>11} {'wall_s':>8}")
promote=[]
for n,(b,w) in sorted(by.items(), key=lambda kv: (kv[1][0]-rb, -rw/kv[1][1])):
    if n in ("ref320","ctrl"): 
        sp=rw/w; print(f"{n:16} {sp:6.2f} {'1.00' if n=='ctrl' else '—':>6} {b-rb:+9d} {'0' if n=='ctrl' else '—':>11} {w:8.1f}")
        continue
    sp=rw/w; rel=cw/w; db=b-rb; dctrl=b-cb
    keep_speed = w <= cw*1.05
    keep_bytes = b < cb
    mark=""
    if keep_speed and keep_bytes:
        mark=" ← PROMOTE"
        promote.append(n)
    elif keep_bytes and w <= cw*1.15:
        mark=" ← near"
    print(f"{n:16} {sp:6.2f} {rel:6.2f} {db:+9d} {dctrl:+11d} {w:8.1f}{mark}")
print("PROMOTE_10MiB", ",".join(promote) if promote else "(none)")
Path("/tmp/fair_4x_peel_promote.txt").write_text(",".join(promote))
PY
echo DONE_1MiB
