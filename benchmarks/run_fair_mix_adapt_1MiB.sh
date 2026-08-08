#!/usr/bin/env bash
# LIGHT_MIX_ADAPT screen @1 MiB on cores 4–7.
set -uo pipefail
ROOT=/srv/http/fractal_zip
FX2=$ROOT/tools/hutter/fx2-cmix
MID=$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_mix_adapt_1MiB
CAT=$ROOT/benchmarks/lstm_speed_opt_catalog_mix_adapt.json
CORES=(4 5 6 7)
mkdir -p "$OUT"
LOG=$OUT/fair.log
: >"$LOG"
export FXCM_RECIPE_MIXER_BITMASK=2
log() { printf '%s\n' "$*" | tee -a "$LOG"; sync; }
log "=== fair_mix_adapt @1MiB $(date -Is) ==="

run1() {
  local name=$1 bin=$2 core=$3
  grep -q "^${name} bytes=" "$LOG" 2>/dev/null && return 0
  [[ -x $bin ]] || { log "MISSING $bin"; return 1; }
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

run1 pareto "$FX2/run/cmix_opt_fp_ao16_n2_slim" 4
run1 b4_slim "$FX2/run/cmix_opt_b4_slim" 4
run1 speed_tip "$FX2/run/cmix_opt_c4_aggr_novel2" 4
run1 kitchen "$FX2/run/cmix_opt_fp2_l64_p2k_cm_ao" 4

python3 - <<'PY'
import json
from pathlib import Path
cat=json.loads(Path("/srv/http/fractal_zip/benchmarks/lstm_speed_opt_catalog_mix_adapt.json").read_text())
Path("/tmp/ma_names.txt").write_text("\n".join(c["name"] for c in cat["candidates"])+"\n")
PY

declare -a PIDS=()
i=0
while read -r name; do
  [[ -n $name ]] || continue
  while [[ ${#PIDS[@]} -ge 4 ]]; do
    new=()
    for p in "${PIDS[@]}"; do kill -0 "$p" 2>/dev/null && new+=("$p"); done
    PIDS=("${new[@]}")
    [[ ${#PIDS[@]} -ge 4 ]] && sleep 10
  done
  core=${CORES[$((i % 4))]}
  i=$((i+1))
  ( run1 "$name" "$FX2/run/cmix_opt_$name" "$core" ) >>"$OUT/${name}.runlog" 2>&1 &
  PIDS+=($!)
  log "LAUNCH $name core=$core"
done < /tmp/ma_names.txt
for p in "${PIDS[@]}"; do wait "$p" || true; done

python3 - <<'PY' | tee -a "$LOG"
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_mix_adapt_1MiB/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1].split()[0]))
pb,pw=by["pareto"]; bb,bw=by["b4_slim"]; sb,sw=by["speed_tip"]; kb,kw=by["kitchen"]
scale=931.0/kw
print(f"refs p={pb}/{pw:.1f} b4={bb}/{bw:.1f} st={sb}/{sw:.1f} k={kw:.1f}")
print(f"{'name':14} {'B':>10} {'dB_p':>8} {'dB_b4':>8} {'wall':>7} {'vs_p':>6} {'quiet':>7}")
promote=[]
for n,(b,w) in sorted(by.items(), key=lambda kv: (kv[1][0], kv[1][1])):
    q=w*scale
    note=""
    if n.startswith("ma_"):
        if b < pb and w <= pw*1.06:
            note=" ← BEAT_PARETO"; promote.append(n)
        elif b < bb and w <= bw*1.06:
            note=" ← BEAT_B4SLIM"; promote.append(n)
        elif n=="ma_st" and b < sb and w <= sw*1.05:
            note=" ← BEAT_SPEED"; promote.append(n)
        elif w < pw*0.97 and b <= pb+2048:
            note=" ← FASTER"; promote.append(n)
    print(f"{n:14} {b:10d} {b-pb:+8d} {b-bb:+8d} {w:7.1f} {w/pw:6.3f} {q:7.1f}{note}")
print("PROMOTE_10MiB", ",".join(promote) if promote else "(none)")
PY
log DONE_MIX_ADAPT_1MiB
