#!/usr/bin/env bash
# Close-4× / stack-tax screen @1 MiB on core 0. Refs: banked aggr + ao16 + kitchen.
set -uo pipefail
ROOT=/srv/http/fractal_zip
FX2=$ROOT/tools/hutter/fx2-cmix
MID=$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_close4x_1MiB
CAT=$ROOT/benchmarks/lstm_speed_opt_catalog_close4x.json
HUTTER_CORE=${HUTTER_CORE:-0}
mkdir -p "$OUT"
LOG=$OUT/fair.log
: >"$LOG"
export FXCM_RECIPE_MIXER_BITMASK=2

log() { printf '%s\n' "$*" | tee -a "$LOG"; }

log "=== fair close4x @1 MiB $(date -Is) core=$HUTTER_CORE ==="
log "goal: ≤0.965× aggr wall (~true 4×) and/or ao16 bytes at ≤1.02× aggr wall"
bash "$ROOT/benchmarks/pin_hutter_resources.sh" >/tmp/pin_hutter.log 2>&1 || true

python3 - <<'PY' | tee -a "$LOG"
import json, os, subprocess
from pathlib import Path
cat=json.loads(Path("/srv/http/fractal_zip/benchmarks/lstm_speed_opt_catalog_close4x.json").read_text())
fx2=Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix")
for c in cat["candidates"]:
    name=c["name"]
    env=os.environ.copy()
    env["OPT_NAME"]=name
    env["OPT_EXTRA_DEFS"]=" ".join(cat["base_defs"]+c["extra_defs"])
    print(f"BUILD {name}", flush=True)
    r=subprocess.run(["make","-j","4","match3m_fractal_lstm_opt"],
                     cwd=str(fx2), env=env, capture_output=True, text=True)
    ok=(fx2/f"run/cmix_opt_{name}").is_file() and r.returncode==0
    print(f"  {'OK' if ok else 'FAIL'} rc={r.returncode}", flush=True)
    if not ok:
        Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_close4x_1MiB/build_"+name+".log").write_text(r.stdout+"\n"+r.stderr)
        raise SystemExit(name)
PY

run1() {
  local name=$1 bin=$2
  grep -q "^${name} bytes=" "$LOG" 2>/dev/null && { log "SKIP $name"; return 0; }
  [[ -x $bin ]] || { log "SKIP $name missing"; return 0; }
  local fx=$OUT/${name}.fx2 err=$OUT/${name}.err
  rm -f "$fx" "$fx.cmix.temp"
  bash "$ROOT/benchmarks/pin_hutter_resources.sh" >/tmp/pin_hutter.log 2>&1 || true
  # keep lifestyle paq on 14-15 if present
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
}

run1 aggr "$FX2/run/cmix_opt_dual_lm_aggr"
run1 ao16 "$FX2/run/cmix_opt_dual_lm_ao16"
run1 kitchen "$FX2/run/cmix_opt_fp2_l64_p2k_cm_ao"

for name in c4_aggr_ctrl c4_aggr_mid c4_aggr_slim c4_aggr_mid_slim c4_aggr_novel2 c4_aggr_fxr16 \
            c4_ao16_aggr c4_ao8_aggr c4_ao16_ctrl c4_ao16_mid c4_ao16_slim c4_ao16_novel2; do
  run1 "$name" "$FX2/run/cmix_opt_$name"
done

python3 - <<'PY' | tee -a "$LOG"
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_close4x_1MiB/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    if n.startswith("SKIP") or n.startswith("BUILD"): continue
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1].split()[0]))
ab,aw=by["aggr"]; ob,ow=by["ao16"]; kb,kw=by["kitchen"]
# 4× target relative to this-session aggr: need wall ≤ aw * (729.5/755.0)
target=aw*(729.5/755.0)
print(f"refs: aggr {ab}B/{aw:.1f}s  ao16 {ob}B/{ow:.1f}s  kitchen {kb}B/{kw:.1f}s")
print(f"session 4× target wall ≤ {target:.1f}s (0.965× aggr); ao16 bytes={ob}")
print(f"{'name':22} {'wall':>7} {'vs_ag':>6} {'ΔB_ag':>8} {'ΔB_ao':>8} {'ΔB_k':>8}  note")
promote=[]
for n,(b,w) in sorted(by.items(), key=lambda kv: (kv[1][1], kv[1][0])):
    note=""
    if n not in ("aggr","ao16","kitchen","c4_aggr_ctrl","c4_ao16_ctrl"):
        if w <= target:
            note=" ← HIT_4x_BAND"
            promote.append(n)
        elif w <= aw*0.98 and b <= ab+16384:
            note=" ← PROMOTE_faster"
            promote.append(n)
        elif b <= ob+4096 and w <= aw*1.02:
            note=" ← PROMOTE_bytes_at_speed"
            promote.append(n)
        elif b < ab and w <= aw*1.05:
            note=" ← PROMOTE_taxcut"
            promote.append(n)
        elif b < ob and w <= ow*1.02:
            note=" ← PROMOTE_better_bytes"
            promote.append(n)
    print(f"{n:22} {w:7.1f} {w/aw:6.3f} {b-ab:+8d} {b-ob:+8d} {b-kb:+8d}{note}")
print("PROMOTE_10MiB", ",".join(promote) if promote else "(none — keep tips)")
Path("/tmp/fair_close4x_promote.txt").write_text(",".join(promote) if promote else "")
scale=931.0/kw
print("proj quiet@10MiB (kitchen scale):")
for n,(b,w) in sorted(by.items(), key=lambda kv: kv[1][1]):
    q=w*scale
    print(f"  {n:22} ~{q:.0f}s ({2918.1/q:.2f}×) ΔB_k={b-kb:+d}")
PY
log DONE_CLOSE4X_1MiB
