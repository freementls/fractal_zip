#!/usr/bin/env bash
# Dual Pareto screen @1 MiB on a dedicated Hutter core (default 1; leave 0 for ref320).
# Refs: banked kitchen / kplus_lm / kplus_slim. Promote:
#   fast_bytes: fewer bytes than lm at wall ≤ 1.05× lm
#   bytes_speed: faster than slim at bytes ≤ slim+8KiB (or ≤ kitchen)
set -uo pipefail
ROOT=/srv/http/fractal_zip
FX2=$ROOT/tools/hutter/fx2-cmix
MID=$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_dual_1MiB
CAT=$ROOT/benchmarks/lstm_speed_opt_catalog_dual.json
HUTTER_CORE=${HUTTER_CORE:-1}
mkdir -p "$OUT"
LOG=$OUT/fair.log
: >"$LOG"
export FXCM_RECIPE_MIXER_BITMASK=2

log() { printf '%s\n' "$*" | tee -a "$LOG"; }

log "=== fair dual @1 MiB $(date -Is) core=$HUTTER_CORE ==="
log "slice=$MID size=$(wc -c <"$MID") B loadavg=$(awk '{print $1}' /proc/loadavg)"
log "goal A: cut tax on kplus_lm; goal B: speed up kplus_slim/kitchen"

bash "$ROOT/benchmarks/pin_hutter_resources.sh" >/tmp/pin_hutter.log 2>&1 || true

python3 - <<'PY' | tee -a "$LOG"
import json, os, subprocess
from pathlib import Path
cat=json.loads(Path("/srv/http/fractal_zip/benchmarks/lstm_speed_opt_catalog_dual.json").read_text())
fx2=Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix")
for c in cat["candidates"]:
    name=c["name"]
    env=os.environ.copy()
    env["OPT_NAME"]=name
    env["OPT_EXTRA_DEFS"]=" ".join(cat["base_defs"]+c["extra_defs"])
    print(f"BUILD {name} [{c.get('track','?')}]", flush=True)
    r=subprocess.run(["make","-j",str(os.cpu_count() or 4),"match3m_fractal_lstm_opt"],
                     cwd=str(fx2), env=env, capture_output=True, text=True)
    ok=(fx2/f"run/cmix_opt_{name}").is_file() and r.returncode==0
    print(f"  {'OK' if ok else 'FAIL'} rc={r.returncode}", flush=True)
    if not ok:
        Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_dual_1MiB/build_"+name+".log").write_text(r.stdout+"\n"+r.stderr)
        raise SystemExit(name)
PY

run1() {
  local name=$1 bin=$2
  if [[ ! -x $bin ]]; then
    log "SKIP $name (missing)"
    return 0
  fi
  if grep -q "^${name} bytes=" "$LOG" 2>/dev/null; then
    log "SKIP $name (logged)"
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

# Banked refs first (same session)
run1 kitchen "$FX2/run/cmix_opt_fp2_l64_p2k_cm_ao"
run1 kplus_lm "$FX2/run/cmix_opt_kplus_lm"
run1 kplus_slim "$FX2/run/cmix_opt_kplus_slim"
run1 ref320 "$FX2/run/cmix_match3m_fractalv2_lstm320"

for name in dual_lm_ctrl dual_lm_soft dual_lm_aggr dual_lm_slim dual_lm_ao16 dual_lm_ao16_fp4 dual_lm_soft_slim \
            dual_slim_ctrl dual_slim_soft dual_slim_aggr dual_kit_soft dual_slim_fxr16; do
  run1 "$name" "$FX2/run/cmix_opt_$name"
done

python3 - <<'PY' | tee -a "$LOG"
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_dual_1MiB/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    if n.startswith("SKIP") or n.startswith("BUILD"): continue
    try:
        by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1].split()[0]))
    except Exception:
        pass
kb,kw=by["kitchen"]
lb,lw=by["kplus_lm"]
sb,sw=by["kplus_slim"]
rb,rw=by.get("ref320",(0,0))
print(f"refs: kitchen {kb}B/{kw:.1f}s  lm {lb}B/{lw:.1f}s  slim {sb}B/{sw:.1f}s  ref320 {rb}B/{rw:.1f}s")
print(f"{'name':22} {'wall':>7} {'vs_lm':>6} {'vs_sl':>6} {'ΔB_lm':>8} {'ΔB_sl':>8} {'ΔB_k':>8}  note")
promote=[]
for n,(b,w) in sorted(by.items(), key=lambda kv: (kv[1][1], kv[1][0])):
    if n in ("ref320",): continue
    note=""
    # Track A: improve bytes on fast (vs lm)
    if n.startswith("dual_lm") or n in ("kplus_lm",):
        if n!="kplus_lm" and b<lb and w<=lw*1.05:
            note=" ← PROMOTE_fast_bytes"
            promote.append(n)
        elif n!="kplus_lm" and b<lb and w<=lw*1.12:
            note=" ~tax_cut_slow"
    # Track B: improve speed on best bytes (vs slim)
    if n.startswith("dual_slim") or n.startswith("dual_kit") or n in ("kplus_slim","kitchen"):
        if n not in ("kplus_slim","kitchen") and w<sw*0.97 and b<=sb+8192:
            note += " ← PROMOTE_bytes_speed"
            if n not in promote: promote.append(n)
        elif n not in ("kplus_slim","kitchen") and w<sw*0.97 and b<=kb+8192:
            note += " ← PROMOTE_bytes_speed"
            if n not in promote: promote.append(n)
    print(f"{n:22} {w:7.1f} {w/lw:6.3f} {w/sw:6.3f} {b-lb:+8d} {b-sb:+8d} {b-kb:+8d}{note}")
# always keep pareto tips
for tip in ("kplus_lm","kplus_slim"):
    if tip not in promote: pass
print("PROMOTE_10MiB", ",".join(promote) if promote else "(none)")
Path("/tmp/fair_dual_promote.txt").write_text(",".join(promote) if promote else "")
# quiet proj via this-session kitchen → 931
scale=931.0/kw
print("proj quiet@10MiB:")
for n,(b,w) in sorted(by.items(), key=lambda kv: kv[1][1]):
    if n=="ref320": continue
    q=w*scale
    print(f"  {n:22} ~{q:.0f}s ({2918.1/q:.2f}×) ΔB_k={b-kb:+d}")
PY
log DONE_DUAL_1MiB
