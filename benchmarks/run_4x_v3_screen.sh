#!/usr/bin/env bash
# 4× v3 screen: wait for quiet machine, fresh refs, promote to 10m for real 4× gate.
set -euo pipefail
ROOT=/srv/http/fractal_zip
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt
LOG=$OUT/screen_4x_v3.log
mkdir -p "$OUT"
exec > >(tee -a "$LOG") 2>&1

echo "=== 4x v3 $(date -Is) ==="
echo "waiting for loadavg(1m) < 6 and no fractal cmix..."
while true; do
  load=$(awk '{print $1}' /proc/loadavg)
  busy=0
  if pgrep -f '/cmix_opt_|/cmix_match3m_fractalv2' >/dev/null 2>&1; then busy=1; fi
  # ignore this script's own mentions via checking argv0-like binaries only
  busy=0
  for p in /proc/[0-9]*/cmdline; do
    raw=$(tr '\0' ' ' < "$p" 2>/dev/null || true)
    case "$raw" in
      */cmix_opt_*|*/cmix_match3m_fractalv2*) busy=1; break ;;
    esac
  done
  awk -v l="$load" -v b="$busy" 'BEGIN{exit !((l+0)<6.0 && b==0)}' && break
  echo "$(date +%H:%M:%S) load=$load busy=$busy — sleep 30"
  sleep 30
done
echo "GO load=$(awk '{print $1}' /proc/loadavg)"

# 1m: loose byte tax, speed bar ~2.5× (kitchen historically 2.35×@1m / 3.14×@10m)
python3 "$ROOT/benchmarks/optimize_fxcm_lstm_speed.py" \
  --catalog "$ROOT/benchmarks/lstm_speed_opt_catalog_4x_v3.json" \
  --slice 1m --rounds 1 --tag-prefix x4v3 \
  --baseline-bin "$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm320" \
  --beat-bin "$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm320" \
  --max-tax-vs-384 100000 --min-beat-320 -100000 \
  --min-speedup-vs-384 2.5 --max-vs-base 0.85

python3 - <<'PY'
import json
from pathlib import Path
p = Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/leaderboard_1m.json")
d = json.loads(p.read_text())
w320 = float(d["ref"]["lstm320_wall"])
b320 = int(d["ref"]["lstm320_bytes"])
rows = []
for r in d.get("results", []):
    if not str(r.get("name", "")).startswith(("ksink", "l128_", "l192_", "l256_")):
        continue
    if not r.get("wall_s"):
        continue
    rows.append(r)
rows.sort(key=lambda r: -w320 / r["wall_s"])
print(f"{'name':22} {'×320':>6} {'ΔB':>8} {'wall':>8}")
promote = []
for r in rows:
    sp = w320 / r["wall_s"]
    db = r["bytes"] - b320
    print(f"{r['name']:22} {sp:6.2f} {db:+8d} {r['wall_s']:8.1f} {r.get('verdict')}")
    # promote anything ≥2.4× for 10m 4× check; also best mid-LSTM by (speed, -tax)
    if sp >= 2.4 or r.get("verdict") == "PROMOTE":
        promote.append(r["name"])
# always include best low-tax among l128/l192/l256
mids = [r for r in rows if r["name"].startswith(("l128_", "l192_", "l256_"))]
if mids:
    best_mid = min(mids, key=lambda r: (r["bytes"] - b320, -w320 / r["wall_s"]))
    if best_mid["name"] not in promote:
        promote.append(best_mid["name"])
print("PROMOTE_10M", ",".join(promote))
Path("/tmp/x4v3_promote.txt").write_text(",".join(promote))
PY

PROMOTE=$(cat /tmp/x4v3_promote.txt)
if [[ -z "$PROMOTE" ]]; then
  echo "No 10m promotees — DONE"
  echo DONE
  exit 0
fi

echo "=== 10m promote: $PROMOTE ==="
# rebuild only selected via --only; fresh 10m refs
python3 "$ROOT/benchmarks/optimize_fxcm_lstm_speed.py" \
  --catalog "$ROOT/benchmarks/lstm_speed_opt_catalog_4x_v3.json" \
  --slice 10m --rounds 1 --tag-prefix x4v3 \
  --only "$PROMOTE" --skip-build \
  --baseline-bin "$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm320" \
  --beat-bin "$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm320" \
  --max-tax-vs-384 100000 --min-beat-320 -100000 \
  --min-speedup-vs-384 3.9 --max-vs-base 0.55

python3 - <<'PY'
import json
from pathlib import Path
p = Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/leaderboard_10m.json")
d = json.loads(p.read_text())
w320 = float(d["ref"]["lstm320_wall"])
b320 = int(d["ref"]["lstm320_bytes"])
print(f"ref320 bytes={b320} wall={w320:.1f}  4× target ≤{w320/4:.1f}s")
for r in sorted(d.get("results", []), key=lambda r: -(w320 / r["wall_s"] if r.get("wall_s") else 0)):
    if not r.get("wall_s"):
        continue
    if not any(x in r["name"] for x in ("ksink", "l128_", "l192_", "l256_", "fp2_")):
        continue
    sp = w320 / r["wall_s"]
    mark = " <--4x" if sp >= 4.0 else (" <--3.5x" if sp >= 3.5 else "")
    print(f"{r['name']:22} {sp:5.2f}x ΔB={r['bytes']-b320:+7d} wall={r['wall_s']:.0f}{mark}")
PY
echo DONE
