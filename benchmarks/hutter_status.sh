#!/usr/bin/env bash
# Quick campaign status. Usage: bash benchmarks/hutter_status.sh
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
echo "=== $(date -Iseconds) ==="
free -m | awk 'NR==2{printf "mem: avail=%sMiB used=%sMiB\n",$7,$3}'
echo
echo "--- cmix ---"
ps -eo pid,etime,rss,%cpu,cmd | rg '[c]mix_' || echo '(idle)'
echo
echo "--- ladder log (tail) ---"
tail -6 "$ROOT/benchmarks/.hutter_logs/consistent_ladder.log" 2>/dev/null || echo '(none)'
echo
echo "--- A/B screen results ---"
python3 - <<'PY'
import json
from pathlib import Path
p=Path("/srv/http/fractal_zip/benchmarks/.hutter_slice_screens.jsonl")
if not p.exists():
    print("(no jsonl)"); raise SystemExit
for l in p.read_text().splitlines():
    if not l.strip(): continue
    try: r=json.loads(l)
    except: continue
    if r["name"].startswith(("A1_","A2_","B0_","B1_","tuning_ul6000")):
        print(f"{r['name']:28s} Δ={r.get('delta'):>+8} gate={r.get('gate'):8s} rt={r.get('rt')}")
PY
echo
echo "--- 100m baseline ---"
python3 -c "import json;d=json.load(open('$ROOT/benchmarks/.hutter_final_gate.json'));b=d.get('baseline_100m',{});print(b.get('bytes'), 'gate_need<=',d.get('gate_100m_bytes_max'))"
echo
# live progress if any
for f in "$ROOT"/benchmarks/.hutter_logs/screen_*_c.time; do
  [[ -f "$f" ]] || continue
  # only if corresponding cmix running / recent
  prog=$(tr '\r' '\n' <"$f" 2>/dev/null | rg -o 'progress: [0-9.]+%' | tail -1 || true)
  [[ -n "$prog" ]] && echo "$(basename "$f"): $prog"
done
echo
echo "watchers: resume=$(pgrep -cf 'run_hutter_consistent_ladder_resume\.sh' || true) deepen=$(pgrep -cf 'benchmarks/hutter_profile_deepen\.sh' || true) deepen_wd=$(pgrep -cf 'hutter_profile_deepen_watchdog\.sh' || true)"
echo "consistency: $ROOT/benchmarks/.hutter_preprocess_consistency.md"
