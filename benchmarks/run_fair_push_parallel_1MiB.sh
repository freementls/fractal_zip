#!/usr/bin/env bash
# Parallel post-4× fractal push @1 MiB.
# Shared refs on core 0, then candidate tracks on cores 1/2/3 (Hutter pool 0–7).
set -uo pipefail
ROOT=/srv/http/fractal_zip
FX2=$ROOT/tools/hutter/fx2-cmix
MID=$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
CAT=$ROOT/benchmarks/lstm_speed_opt_catalog_fractal_push.json
BASE=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt
REFDIR=$BASE/fair_push_refs
export FXCM_RECIPE_MIXER_BITMASK=2

bash "$ROOT/benchmarks/pin_hutter_resources.sh" >/tmp/pin_hutter_push.log 2>&1 || true

if [[ "${SKIP_BUILD:-0}" == "1" ]]; then
  echo "=== SKIP_BUILD fractal_push $(date -Is) ==="
else
  echo "=== BUILD fractal_push $(date -Is) ==="
  python3 - <<'PY'
import json, os, subprocess
from pathlib import Path
cat=json.loads(Path("/srv/http/fractal_zip/benchmarks/lstm_speed_opt_catalog_fractal_push.json").read_text())
fx2=Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix")
base=cat["base_defs"]
names=[]
for track, meta in cat["tracks"].items():
    for c in meta["candidates"]:
        names.append(c["name"])
        env=os.environ.copy()
        env["OPT_NAME"]=c["name"]
        env["OPT_EXTRA_DEFS"]=" ".join(base+c["extra_defs"])
        print(f"BUILD {c['name']} [{track}]", flush=True)
        r=subprocess.run(["make","-j","8","match3m_fractal_lstm_opt"],
                         cwd=str(fx2), env=env, capture_output=True, text=True)
        ok=(fx2/f"run/cmix_opt_{c['name']}").is_file() and r.returncode==0
        print(f"  {'OK' if ok else 'FAIL'} rc={r.returncode}", flush=True)
        if not ok:
            Path(f"/tmp/build_{c['name']}.log").write_text(r.stdout+"\n"+r.stderr)
            raise SystemExit(c["name"])
print("BUILT", ",".join(names))
PY
  rc=$?
  [[ $rc -eq 0 ]] || { echo "BUILD_FAIL rc=$rc"; exit $rc; }
fi

run1() {
  local name=$1 bin=$2 core=$3 OUT=$4 LOG=$5
  grep -q "^${name} bytes=" "$LOG" 2>/dev/null && { echo "SKIP $name" | tee -a "$LOG"; return 0; }
  [[ -x $bin ]] || { echo "SKIP $name missing" | tee -a "$LOG"; return 0; }
  local fx=$OUT/${name}.fx2 err=$OUT/${name}.err
  rm -f "$fx" "$fx.cmix.temp"
  local t0 t1 wall bytes rc
  t0=$(date +%s.%N)
  set +e
  taskset -c "$core" nice -n 0 "$bin" -c "$DICT" "$MID" "$fx" >/dev/null 2>"$err"
  rc=$?
  set +e
  t1=$(date +%s.%N)
  wall=$(python3 -c "print(f'{$t1-$t0:.2f}')")
  bytes=$(stat -c%s "$fx" 2>/dev/null || echo 0)
  printf '%s bytes=%s wall_s=%s rc=%s\n' "$name" "$bytes" "$wall" "$rc" | tee -a "$LOG"
  sync
}

# --- shared refs on core 0 ---
mkdir -p "$REFDIR"
REFLOG=$REFDIR/fair.log
: >"$REFLOG"
echo "=== fair_push refs @1MiB $(date -Is) core=0 ===" | tee -a "$REFLOG"
run1 kitchen "$FX2/run/cmix_opt_fp2_l64_p2k_cm_ao" 0 "$REFDIR" "$REFLOG"
run1 speed_tip "$FX2/run/cmix_opt_c4_aggr_novel2" 0 "$REFDIR" "$REFLOG"
run1 pareto_4x "$FX2/run/cmix_opt_c4_ao16_novel2" 0 "$REFDIR" "$REFLOG"
run1 bytes_tip "$FX2/run/cmix_opt_c4_ao16_slim" 0 "$REFDIR" "$REFLOG"
echo "DONE_PUSH_REFS" | tee -a "$REFLOG"

run_track() {
  local track=$1 core=$2 outdir=$3
  local OUT=$BASE/$outdir
  mkdir -p "$OUT"
  local LOG=$OUT/fair.log
  {
    echo "=== fair_push $track @1MiB $(date -Is) core=$core ==="
    grep -E '^(kitchen|speed_tip|pareto_4x|bytes_tip) bytes=' "$REFLOG"
  } >"$LOG"

  python3 - <<PY
import json
from pathlib import Path
cat=json.loads(Path("$CAT").read_text())
names=[c["name"] for c in cat["tracks"]["$track"]["candidates"]]
Path("/tmp/push_${track}_names.txt").write_text("\n".join(names)+"\n")
print("CANDS $track", ",".join(names), flush=True)
PY
  while read -r name; do
    [[ -n $name ]] || continue
    run1 "$name" "$FX2/run/cmix_opt_$name" "$core" "$OUT" "$LOG"
  done < "/tmp/push_${track}_names.txt"

  python3 - <<PY | tee -a "$LOG"
from pathlib import Path
by={}
for ln in Path("$LOG").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1].split()[0]))
kb,kw=by["kitchen"]; scale=931.0/kw
sb,sw=by["speed_tip"]; pb,pw=by["pareto_4x"]; bb,bw=by["bytes_tip"]
t4=729.5/scale
print(f"refs kitchen={kb}/{kw:.1f}s scale={scale:.4f} 4x_wall≤{t4:.1f}s")
print(f"  speed_tip {sb}/{sw:.1f}s  pareto {pb}/{pw:.1f}s  bytes {bb}/{bw:.1f}s")
print(f"{'name':22} {'B':>10} {'dB_k':>8} {'dB_st':>8} {'dB_p':>8} {'wall':>7} {'quiet':>7} {'xq':>5} note")
promote=[]
for n,(b,w) in sorted(by.items(), key=lambda kv: (kv[1][1], kv[1][0])):
    q=w*scale
    note=""
    if n not in ("kitchen","speed_tip","pareto_4x","bytes_tip"):
        if w < sw*0.97:
            note=" ← FASTER"; promote.append(n)
        elif b < pb and w <= pw*1.03:
            note=" ← BYTES_IN_4x"; promote.append(n)
        elif b < bb and w <= bw*1.02:
            note=" ← BYTES_TIP"; promote.append(n)
        elif q <= 729.5 and b < sb:
            note=" ← 4x_TAXCUT"; promote.append(n)
        elif w <= sw*1.02 and b < sb - 4096:
            note=" ← PARETO"; promote.append(n)
    if q <= 729.5 and n not in ("kitchen",):
        if "←4×" not in note:
            note = (note + " ←4×").strip()
    print(f"{n:22} {b:10d} {b-kb:+8d} {b-sb:+8d} {b-pb:+8d} {w:7.1f} {q:7.1f} {2918.1/q:5.2f}{note}")
print("PROMOTE_10MiB", ",".join(promote) if promote else "(none)")
Path("/tmp/push_${track}_promote.txt").write_text(",".join(promote))
PY
  echo "DONE_PUSH_${track}_1MiB" | tee -a "$LOG"
}

declare -a PIDS=()
while read -r track core outdir; do
  (
    run_track "$track" "$core" "$outdir"
  ) >"$BASE/${outdir}_runner.out" 2>&1 &
  PIDS+=($!)
  echo "LAUNCH track=$track core=$core pid=${PIDS[-1]}"
done < <(python3 - <<'PY'
import json
from pathlib import Path
cat=json.loads(Path("/srv/http/fractal_zip/benchmarks/lstm_speed_opt_catalog_fractal_push.json").read_text())
for t,m in cat["tracks"].items():
    print(t, m["core"], m["outdir"])
PY
)

# Mixer-bitmask fractal axis on banked pareto (no rebuild) — core 4
(
  OUT=$BASE/fair_push_mix
  mkdir -p "$OUT"
  LOG=$OUT/fair.log
  {
    echo "=== fair_push mix bitmask @1MiB $(date -Is) core=4 ==="
    grep -E '^(kitchen|speed_tip|pareto_4x|bytes_tip) bytes=' "$REFLOG"
  } >"$LOG"
  bin=$FX2/run/cmix_opt_c4_ao16_novel2
  for bm in 0 1 2 3 4 5 6 7; do
    name=mix_bm$bm
    grep -q "^${name} bytes=" "$LOG" 2>/dev/null && continue
    fx=$OUT/${name}.fx2; err=$OUT/${name}.err
    rm -f "$fx" "$fx.cmix.temp"
    t0=$(date +%s.%N)
    set +e
    env FXCM_RECIPE_MIXER_BITMASK=$bm taskset -c 4 nice -n 0 "$bin" -c "$DICT" "$MID" "$fx" >/dev/null 2>"$err"
    rc=$?
    set +e
    t1=$(date +%s.%N)
    wall=$(python3 -c "print(f'{$t1-$t0:.2f}')")
    bytes=$(stat -c%s "$fx" 2>/dev/null || echo 0)
    printf '%s bytes=%s wall_s=%s rc=%s\n' "$name" "$bytes" "$wall" "$rc" | tee -a "$LOG"
    sync
  done
  python3 - <<'PY' | tee -a "$LOG"
from pathlib import Path
by={}
logp=Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_push_mix/fair.log")
for ln in logp.read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1].split()[0]))
kb,kw=by["kitchen"]; scale=931.0/kw
pb,pw=by["pareto_4x"]
print(f"{'name':14} {'B':>10} {'dB_p':>8} {'wall':>7} {'quiet':>7}")
best=None
for n,(b,w) in sorted(by.items(), key=lambda kv: kv[1][0]):
    if not n.startswith("mix_"): continue
    q=w*scale
    print(f"{n:14} {b:10d} {b-pb:+8d} {w:7.1f} {q:7.1f}")
    if best is None or b<best[1]: best=(n,b,w)
print(f"BEST_MIX_BYTES {best[0]} {best[1]} dB_p={best[1]-pb:+d}")
PY
  echo "DONE_PUSH_mix_1MiB" | tee -a "$LOG"
) >"$BASE/fair_push_mix_runner.out" 2>&1 &
PIDS+=($!)
echo "LAUNCH track=mix core=4 pid=${PIDS[-1]}"

(
  while true; do
    alive=0
    for p in "${PIDS[@]}"; do kill -0 "$p" 2>/dev/null && alive=1; done
    [[ $alive -eq 1 ]] || break
    bash "$ROOT/benchmarks/pin_hutter_resources.sh" >/tmp/pin_hutter_push.log 2>&1 || true
    # keep lifestyle paq off Hutter pool
    for p in $(pgrep -x paq8px || true); do
      taskset -cp 14-15 "$p" 2>/dev/null || true
    done
    sleep 40
  done
) &
PINPID=$!

ec=0
for p in "${PIDS[@]}"; do
  wait "$p" || ec=1
done
kill "$PINPID" 2>/dev/null || true

echo "=== SUMMARY $(date -Is) ==="
for d in fair_push_speed fair_push_bytes fair_push_fractal fair_push_mix; do
  echo "--- $d ---"
  grep -E '^(PROMOTE_|DONE_|BEST_|refs |[a-z0-9_]+ bytes=)' "$BASE/$d/fair.log" 2>/dev/null | tail -50
done
python3 - <<'PY'
from pathlib import Path
prom=set()
for t in ("speed","bytes","fractal"):
    p=Path(f"/tmp/push_{t}_promote.txt")
    if p.is_file() and p.read_text().strip():
        prom.update(x for x in p.read_text().strip().split(",") if x)
print("UNION_PROMOTE_10MiB", ",".join(sorted(prom)) if prom else "(none)")
Path("/tmp/push_union_promote.txt").write_text(",".join(sorted(prom)))
PY
echo "DONE_PUSH_PARALLEL_1MiB ec=$ec"
exit $ec
