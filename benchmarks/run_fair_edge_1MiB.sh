#!/usr/bin/env bash
# Match-edge novel screen @1 MiB on core 7 (parallel with push@10MiB on 0–6).
set -uo pipefail
ROOT=/srv/http/fractal_zip
FX2=$ROOT/tools/hutter/fx2-cmix
MID=$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_edge_1MiB
CAT=$ROOT/benchmarks/lstm_speed_opt_catalog_edge.json
CORE=${HUTTER_CORE:-7}
mkdir -p "$OUT"
LOG=$OUT/fair.log
: >"$LOG"
export FXCM_RECIPE_MIXER_BITMASK=2
log() { printf '%s\n' "$*" | tee -a "$LOG"; sync; }
log "=== fair_edge @1MiB $(date -Is) core=$CORE ==="

run1() {
  local name=$1 bin=$2
  grep -q "^${name} bytes=" "$LOG" 2>/dev/null && { log "SKIP $name"; return 0; }
  [[ -x $bin ]] || { log "SKIP $name missing"; return 0; }
  local fx=$OUT/${name}.fx2 err=$OUT/${name}.err
  rm -f "$fx" "$fx.cmix.temp"
  local t0 t1 wall bytes rc
  t0=$(date +%s.%N)
  set +e
  taskset -c "$CORE" nice -n 0 "$bin" -c "$DICT" "$MID" "$fx" >/dev/null 2>"$err"
  rc=$?
  set +e
  t1=$(date +%s.%N)
  wall=$(python3 -c "print(f'{$t1-$t0:.2f}')")
  bytes=$(stat -c%s "$fx" 2>/dev/null || echo 0)
  printf '%s bytes=%s wall_s=%s rc=%s\n' "$name" "$bytes" "$wall" "$rc" | tee -a "$LOG"
  sync
}

run1 speed_tip "$FX2/run/cmix_opt_c4_aggr_novel2"
run1 pareto_4x "$FX2/run/cmix_opt_c4_ao16_novel2"
run1 kitchen "$FX2/run/cmix_opt_fp2_l64_p2k_cm_ao"

python3 - <<'PY'
import json
from pathlib import Path
cat=json.loads(Path("/srv/http/fractal_zip/benchmarks/lstm_speed_opt_catalog_edge.json").read_text())
Path("/tmp/edge_names.txt").write_text("\n".join(c["name"] for c in cat["candidates"])+"\n")
PY
while read -r name; do
  [[ -n $name ]] || continue
  run1 "$name" "$FX2/run/cmix_opt_$name"
done < /tmp/edge_names.txt

python3 - <<'PY' | tee -a "$LOG"
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_edge_1MiB/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1].split()[0]))
sb,sw=by["speed_tip"]; pb,pw=by["pareto_4x"]; kb,kw=by["kitchen"]
scale=931.0/kw
print(f"refs st={sb}/{sw:.1f}s p={pb}/{pw:.1f}s k={kb}/{kw:.1f}s")
print(f"{'name':16} {'B':>10} {'dB_st':>8} {'dB_p':>8} {'wall':>7} {'vs_st':>6} {'quiet':>7}")
promote=[]
for n,(b,w) in sorted(by.items(), key=lambda kv: (kv[1][0], kv[1][1])):
    q=w*scale
    note=""
    if n.startswith("edge_"):
        if b < sb - 512 and w <= sw*1.05:
            note=" ← TAXCUT_st"; promote.append(n)
        if b < pb - 256 and w <= pw*1.05:
            note=" ← TAXCUT_p"; promote.append(n)
        if w < sw*0.98 and b <= sb + 2048:
            note=" ← FASTER"; promote.append(n)
    print(f"{n:16} {b:10d} {b-sb:+8d} {b-pb:+8d} {w:7.1f} {w/sw:6.3f} {q:7.1f}{note}")
print("PROMOTE_10MiB", ",".join(dict.fromkeys(promote)) if promote else "(none)")
PY
log DONE_EDGE_1MiB
