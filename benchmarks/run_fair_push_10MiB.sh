#!/usr/bin/env bash
# Promote fractal-push survivors @10 MiB.
# Cap parallel arms: each cmix ≈3 GiB RSS; 7-way thrashed 38 GiB host into swap.
# Default MAX_PARALLEL=4. Extrapolate quiet ≈ wall*(931/kitchen_wall); 4× ≤729.5 s.
set -uo pipefail
ROOT=/srv/http/fractal_zip
FX2=$ROOT/tools/hutter/fx2-cmix
MID=$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_push_10MiB
MAX_PARALLEL=${MAX_PARALLEL:-4}
mkdir -p "$OUT"
LOG=$OUT/fair.log
: >"$LOG"
export FXCM_RECIPE_MIXER_BITMASK=2

log() { printf '%s\n' "$*" | tee -a "$LOG"; sync; }
log "=== fair_push @10MiB $(date -Is) max_parallel=$MAX_PARALLEL ==="

# name core bin — wave1 (refs+star) then wave2 if needed
ARMS=(
  "kitchen 0 $FX2/run/cmix_opt_fp2_l64_p2k_cm_ao"
  "speed_tip 1 $FX2/run/cmix_opt_c4_aggr_novel2"
  "pareto_4x 2 $FX2/run/cmix_opt_c4_ao16_novel2"
  "fp_ao16_n2_aggr 4 $FX2/run/cmix_opt_fp_ao16_n2_aggr"
  "bytes_tip 3 $FX2/run/cmix_opt_c4_ao16_slim"
  "fp_n2_hard2 5 $FX2/run/cmix_opt_fp_n2_hard2"
  "fp_ao16_n2_slim 6 $FX2/run/cmix_opt_fp_ao16_n2_slim"
)

run_arm() {
  local name=$1 core=$2 bin=$3
  local fx=$OUT/${name}.fx2 err=$OUT/${name}.err
  if grep -q "^${name} bytes=" "$LOG" 2>/dev/null; then
    echo "SKIP $name" >>"$OUT/${name}.runlog"
    return 0
  fi
  [[ -x $bin ]] || { echo "MISSING $bin" >>"$OUT/${name}.runlog"; return 1; }
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
  # atomic-ish append
  printf '%s bytes=%s wall_s=%s rc=%s\n' "$name" "$bytes" "$wall" "$rc" >>"$LOG"
  sync
  echo "DONE $name bytes=$bytes wall_s=$wall" >>"$OUT/${name}.runlog"
}

declare -a PIDS=()
running=0
for spec in "${ARMS[@]}"; do
  while [[ $running -ge $MAX_PARALLEL ]]; do
    new_pids=()
    for p in "${PIDS[@]}"; do
      if kill -0 "$p" 2>/dev/null; then new_pids+=("$p"); fi
    done
    PIDS=("${new_pids[@]}")
    running=${#PIDS[@]}
    [[ $running -ge $MAX_PARALLEL ]] && sleep 20
  done
  set -- $spec
  name=$1; core=$2; bin=$3
  (
    run_arm "$name" "$core" "$bin"
  ) >"$OUT/${name}.runlog" 2>&1 &
  PIDS+=($!)
  running=${#PIDS[@]}
  log "LAUNCH $name core=$core pid=${PIDS[-1]} running=$running"
done

(
  while true; do
    alive=0
    for p in "${PIDS[@]}"; do kill -0 "$p" 2>/dev/null && alive=1; done
    [[ $alive -eq 1 ]] || break
    bash "$ROOT/benchmarks/pin_hutter_resources.sh" >/tmp/pin_hutter_push10.log 2>&1 || true
    for p in $(pgrep -x paq8px || true); do taskset -cp 14-15 "$p" 2>/dev/null || true; done
    sleep 60
  done
) &
PINPID=$!

for p in "${PIDS[@]}"; do wait "$p" || true; done
kill "$PINPID" 2>/dev/null || true

python3 - <<'PY' | tee -a "$LOG"
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_push_10MiB/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    try:
        b=int(ln.split("bytes=")[1].split()[0]); w=float(ln.split("wall_s=")[1].split()[0])
    except Exception:
        continue
    if w<=0: continue
    by[n]=(b,w)
if "kitchen" not in by:
    print("MISSING kitchen"); raise SystemExit(1)
kb,kw=by["kitchen"]; scale=931.0/kw
sb,sw=by.get("speed_tip",(kb,kw)); pb,pw=by.get("pareto_4x",(kb,kw)); bb,bw=by.get("bytes_tip",(10**18,kw))
print(f"kitchen {kb}/{kw:.1f}s scale={scale:.4f}")
print(f"{'name':22} {'B':>10} {'dB_k':>9} {'dB_st':>9} {'dB_p':>9} {'wall':>8} {'quiet':>8} {'xq':>6}")
for n,(b,w) in sorted(by.items(), key=lambda kv: kv[1][1]):
    q=w*scale
    print(f"{n:22} {b:10d} {b-kb:+9d} {b-sb:+9d} {b-pb:+9d} {w:8.1f} {q:8.1f} {2918.1/q:6.2f}{' ←4×' if q<=729.5 else ''}")
cands=[n for n in by if n.startswith("fp_")]
if cands:
    bw=min(cands, key=lambda n: by[n][1])
    bb2=min(cands, key=lambda n: by[n][0])
    print(f"BEST_FP_WALL {bw} quiet={by[bw][1]*scale:.1f}s vs_st={by[bw][1]/sw:.3f}")
    print(f"BEST_FP_BYTES {bb2} {by[bb2][0]} dB_p={by[bb2][0]-pb:+d} quiet={by[bb2][1]*scale:.1f}s")
    if by[bw][1] < sw:
        print(f"NEW_SPEED_TIP {bw}")
    if by[bb2][0] < bb and by[bb2][1]*scale <= 729.5:
        print(f"NEW_BYTES_IN_4x {bb2}")
    elif by[bb2][0] < pb and by[bb2][1]*scale <= 729.5:
        print(f"NEW_PARETO_4x {bb2}")
PY
log DONE_PUSH_10MiB
