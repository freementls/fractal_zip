#!/usr/bin/env bash
# Fair back-to-back screen @1 MiB (1 048 576 B) for 4× tax-cut arms.
# Notation: 1 MiB / 10 MiB slices (2²⁰ B / 10×2²⁰ B). Wall in seconds (s).
set -euo pipefail
ROOT=/srv/http/fractal_zip
FX2=$ROOT/tools/hutter/fx2-cmix
MID=$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_4x_tax_1MiB
CAT=$ROOT/benchmarks/lstm_speed_opt_catalog_4x_tax.json
LOG=$OUT/fair.log
mkdir -p "$OUT"
exec > >(tee -a "$LOG") 2>&1

bytes_mid=$(wc -c <"$MID")
echo "=== fair 4× tax-cut @1 MiB $(date -Is) ==="
echo "slice=$MID size=${bytes_mid} B (=$((bytes_mid/1024/1024)) MiB) loadavg=$(awk '{print $1}' /proc/loadavg)"
export FXCM_RECIPE_MIXER_BITMASK=2

# Build all candidates
python3 - <<'PY'
import json, os, subprocess
from pathlib import Path
cat=json.loads(Path("/srv/http/fractal_zip/benchmarks/lstm_speed_opt_catalog_4x_tax.json").read_text())
base=cat["base_defs"]
fx2=Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix")
for c in cat["candidates"]:
    name=c["name"]
    env=os.environ.copy()
    env["OPT_NAME"]=name
    env["OPT_EXTRA_DEFS"]=" ".join(base+c["extra_defs"])
    print(f"BUILD {name}", flush=True)
    r=subprocess.run(["make","-j",str(os.cpu_count() or 4),"match3m_fractal_lstm_opt"],
                     cwd=str(fx2), env=env, capture_output=True, text=True)
    ok=(fx2/f"run/cmix_opt_{name}").is_file() and r.returncode==0
    print(f"  {'OK' if ok else 'FAIL'} rc={r.returncode}", flush=True)
    if not ok:
        Path(f"/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_4x_tax_1MiB/build_{name}.log").write_text(r.stdout+"\n"+r.stderr)
        raise SystemExit(f"build failed {name}")
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

run1 ref320 "$FX2/run/cmix_match3m_fractalv2_lstm320"
for name in ctrl_v4_lm l128_lm_only l128_lm_fp4 l128_lm_fp2 l128_lm_fp2_cm l192_lm_fp2_cm_ao l256_lm_fp4 l96_lm_fp2_ao; do
  run1 "$name" "$FX2/run/cmix_opt_$name"
done

python3 - <<'PY'
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_4x_tax_1MiB/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    b=int(ln.split("bytes=")[1].split()[0])
    w=float(ln.split("wall_s=")[1])
    by[n]=(b,w)
ref_b, ref_w = by["ref320"]
# 4× @10 MiB historically needs ~≥3.0× @1 MiB (kitchen 2.46→3.14; lm 3.25→4.07)
print(f"ref320 @1 MiB: {ref_b} B in {ref_w:.1f} s")
print(f"{'name':22} {'×320':>6} {'ΔB':>9} {'wall_s':>8}  note")
for n,(b,w) in sorted(by.items(), key=lambda kv: (-ref_w/kv[1][1], kv[1][0]-ref_b)):
    sp=ref_w/w
    db=b-ref_b
    mark=""
    if n=="ref320": mark=""
    elif sp>=3.2 and db < 50000: mark=" ← speed+tax interest"
    elif sp>=3.0: mark=" ← ≥3×@1 MiB (4×@10 MiB candidate)"
    elif db<=10000: mark=" ← low tax"
    print(f"{n:22} {sp:6.2f} {db:+9d} {w:8.1f}{mark}")
# promote: ×≥3.0 and ΔB below control, plus best ×, plus lowest ΔB among ×≥2.8
ctrl_db = by["ctrl_v4_lm"][0]-ref_b
promote=[]
for n,(b,w) in by.items():
    if n=="ref320": continue
    sp=ref_w/w; db=b-ref_b
    if sp>=3.0 and db < ctrl_db:
        promote.append(n)
best_sp=max(by.items(), key=lambda kv: ref_w/kv[1][1] if kv[0]!="ref320" else 0)
if best_sp[0] not in promote and best_sp[0]!="ref320":
    promote.append(best_sp[0])
print("PROMOTE_10MiB", ",".join(promote))
Path("/tmp/fair_4x_tax_promote.txt").write_text(",".join(promote))
PY
echo DONE_1MiB
