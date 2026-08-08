#!/usr/bin/env bash
# Resume kitchen+ @1 MiB after tee/set -e death. Append to fair.log; no process-sub tee.
set -uo pipefail
ROOT=/srv/http/fractal_zip
FX2=$ROOT/tools/hutter/fx2-cmix
MID=$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid
DICT=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_kitchen_plus_1MiB
LOG=$OUT/fair.log
HUTTER_CORE=${HUTTER_CORE:-0}
mkdir -p "$OUT"
export FXCM_RECIPE_MIXER_BITMASK=2

bash "$ROOT/benchmarks/pin_hutter_resources.sh" >/tmp/pin_hutter.log 2>&1 || true

# Recover kplus_lm wall from mtime if not logged (mid_slim end → lm end)
if ! grep -q '^kplus_lm bytes=' "$LOG" 2>/dev/null && [[ -s $OUT/kplus_lm.fx2 ]]; then
  python3 - <<'PY' | tee -a "$LOG"
from pathlib import Path
p=Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_kitchen_plus_1MiB")
b=(p/"kplus_lm.fx2").stat().st_size
# prefer err last-mtime - fx start; else mid_slim→lm
w=(p/"kplus_lm.fx2").stat().st_mtime - (p/"kplus_mid_slim.fx2").stat().st_mtime
print(f"kplus_lm bytes={b} wall_s={w:.2f}  # recovered_mtime")
PY
fi

run1() {
  local name=$1 bin=$2
  if grep -q "^${name} bytes=" "$LOG" 2>/dev/null; then
    echo "SKIP $name (already logged)" | tee -a "$LOG"
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
  printf '%s bytes=%s wall_s=%s rc=%s\n' "$name" "$bytes" "$wall" "$rc" | tee -a "$LOG"
  [[ "$bytes" -gt 0 ]]
}

echo "=== resume kitchen+ @1 MiB $(date -Is) ===" | tee -a "$LOG"

for name in kplus_lm_mid kplus_fxr8 kplus_all; do
  run1 "$name" "$FX2/run/cmix_opt_$name"
done

python3 - <<'PY' | tee -a "$LOG"
from pathlib import Path
by={}
for ln in Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/fair_kitchen_plus_1MiB/fair.log").read_text().splitlines():
    if "bytes=" not in ln or "wall_s=" not in ln: continue
    n=ln.split()[0]
    if n.startswith("===") or n.startswith("SKIP"): continue
    try:
        by[n]=(int(ln.split("bytes=")[1].split()[0]), float(ln.split("wall_s=")[1].split()[0]))
    except Exception:
        pass
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
fast=min((n for n in by if n!="ref320"), key=lambda n: by[n][1])
if fast not in promote and fast not in (kname,):
    promote.append(fast)
print("PROMOTE_10MiB", ",".join(promote) if promote else kname)
Path("/tmp/fair_kitchen_plus_promote.txt").write_text(",".join(promote) if promote else kname)
scale_10 = 931.0 / kw
print(f"proj quiet@10 MiB (scale kitchen→931 s):")
for n,(b,w) in sorted(by.items(), key=lambda kv: kv[1][1]):
    if n=="ref320": continue
    pq=w*scale_10
    print(f"  {n:16} ~{pq:.0f} s  ({2918.1/pq:.2f}× quiet)  ΔB={b-rb:+d}")
PY
echo DONE_1MiB | tee -a "$LOG"
# also stamp nohup.out so chain watcher fires
echo DONE_1MiB >>"$OUT/nohup.out"
