#!/usr/bin/env bash
# Bytes-inside-4× screen @1 MiB on free cores (default 2–5), while push@10 wave2 uses 0–1.
set -uo pipefail
ROOT=/srv/http/fractal_zip
FX2=$ROOT/tools/hutter/fx2-cmix
MID=$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
CAT=$ROOT/benchmarks/lstm_speed_opt_catalog_bytes4x.json
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_bytes4x_1MiB
CORES=(${BYTES4X_CORES:-2 3 4 5})
mkdir -p "$OUT"
LOG=$OUT/fair.log
: >"$LOG"
export FXCM_RECIPE_MIXER_BITMASK=2
log() { printf '%s\n' "$*" | tee -a "$LOG"; sync; }

if [[ "${SKIP_BUILD:-0}" != "1" ]]; then
  log "=== BUILD bytes4x $(date -Is) ==="
  python3 - <<'PY'
import json, os, subprocess
from pathlib import Path
cat=json.loads(Path("/srv/http/fractal_zip/benchmarks/lstm_speed_opt_catalog_bytes4x.json").read_text())
fx2=Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix")
# Strip AGGRESSIVE when candidate asks for EXTREME
for c in cat["candidates"]:
    defs=list(cat["base_defs"])+list(c["extra_defs"])
    if any("LIGHT_MIX_EXTREME" in d for d in c["extra_defs"]):
        defs=[d for d in defs if "LIGHT_MIX_AGGRESSIVE" not in d]
        if not any("LIGHT_MIX_EXTREME" in d for d in defs):
            defs.append("-DFXCM_SPEED_LIGHT_MIX_EXTREME")
    env=os.environ.copy()
    env["OPT_NAME"]=c["name"]
    env["OPT_EXTRA_DEFS"]=" ".join(defs)
    print(f"BUILD {c['name']}", flush=True)
    r=subprocess.run(["taskset","-c","2-5","make","-j","4","match3m_fractal_lstm_opt"],
                     cwd=str(fx2), env=env, capture_output=True, text=True)
    ok=(fx2/f"run/cmix_opt_{c['name']}").is_file() and r.returncode==0
    print(f"  {'OK' if ok else 'FAIL'} rc={r.returncode}", flush=True)
    if not ok:
        Path(f"/tmp/build_{c['name']}.log").write_text(r.stdout+"\n"+r.stderr)
        raise SystemExit(c["name"])
print("BUILT_OK")
PY
  [[ $? -eq 0 ]] || exit 1
fi

log "=== fair_bytes4x @1MiB $(date -Is) cores=${CORES[*]} ==="
run1() {
  local name=$1 bin=$2 core=$3
  local fx=$OUT/${name}.fx2 err=$OUT/${name}.err
  if grep -q "^${name} bytes=" "$LOG" 2>/dev/null; then echo "SKIP $name"; return 0; fi
  [[ -x $bin ]] || { echo "MISSING $bin"; return 1; }
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

# refs serial on first core
run1 pareto "$FX2/run/cmix_opt_fp_ao16_n2_aggr" "${CORES[0]}"
run1 bytes_tip "$FX2/run/cmix_opt_c4_ao16_slim" "${CORES[0]}"
run1 kitchen "$FX2/run/cmix_opt_fp2_l64_p2k_cm_ao" "${CORES[0]}"

# parallel candidates
python3 - <<'PY'
import json
from pathlib import Path
cat=json.loads(Path("/srv/http/fractal_zip/benchmarks/lstm_speed_opt_catalog_bytes4x.json").read_text())
Path("/tmp/b4_names.txt").write_text("\n".join(c["name"] for c in cat["candidates"])+"\n")
PY

declare -a PIDS=()
i=0
ncores=${#CORES[@]}
while read -r name; do
  [[ -n $name ]] || continue
  # throttle
  while [[ ${#PIDS[@]} -ge $ncores ]]; do
    new=()
    for p in "${PIDS[@]}"; do kill -0 "$p" 2>/dev/null && new+=("$p"); done
    PIDS=("${new[@]}")
    [[ ${#PIDS[@]} -ge $ncores ]] && sleep 15
  done
  core=${CORES[$((i % ncores))]}
  i=$((i+1))
  (
    run1 "$name" "$FX2/run/cmix_opt_$name" "$core"
  ) >>"$OUT/${name}.runlog" 2>&1 &
  PIDS+=($!)
  log "LAUNCH $name core=$core pid=${PIDS[-1]}"
done < /tmp/b4_names.txt
for p in "${PIDS[@]}"; do wait "$p" || true; done

python3 - <<'PY' | tee -a "$LOG"
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_bytes4x_1MiB/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1].split()[0]))
pb,pw=by["pareto"]; bb,bw=by["bytes_tip"]; kb,kw=by["kitchen"]
scale=931.0/kw
print(f"refs pareto={pb}/{pw:.1f}s bytes_tip={bb}/{bw:.1f}s kitchen={kb}/{kw:.1f}s")
print(f"{'name':16} {'B':>10} {'dB_p':>8} {'dB_bt':>8} {'wall':>7} {'vs_p':>6} {'quiet':>7}")
promote=[]
for n,(b,w) in sorted(by.items(), key=lambda kv: (kv[1][0], kv[1][1])):
    q=w*scale
    note=""
    if n.startswith("b4_") and n!="b4_ctrl":
        if b < pb and w <= pw*1.06:
            note=" ← BYTES_IN_4x"; promote.append(n)
        elif b < bb and w <= bw*1.05:
            note=" ← BEAT_BYTES_TIP"; promote.append(n)
        elif w < pw*0.97 and b <= pb+2048:
            note=" ← FASTER"; promote.append(n)
    print(f"{n:16} {b:10d} {b-pb:+8d} {b-bb:+8d} {w:7.1f} {w/pw:6.3f} {q:7.1f}{note}")
print("PROMOTE_10MiB", ",".join(promote) if promote else "(none)")
Path("/tmp/b4_promote.txt").write_text(",".join(promote))
PY
log DONE_BYTES4X_1MiB
