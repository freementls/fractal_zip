#!/usr/bin/env bash
# After m3l384 100m gate finishes: record % vs A0, update gate JSON, run L2 screens.
# Safe to start while 100m is still compressing (polls; one cmix at a time).
# Usage: setsid bash benchmarks/hutter_after_m3l384_100m.sh
set -uo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$ROOT/benchmarks/.hutter_logs/after_m3l384_100m.log"
PROGRESS="$ROOT/benchmarks/.hutter_100m_progress.json"
GATEJSON="$ROOT/benchmarks/.hutter_100m_gate.json"
FINAL="$ROOT/benchmarks/.hutter_final_gate.json"
A0=14966085
NEED=14786491  # A0 * (1 - 0.012)

mkdir -p "$(dirname "$LOG")"
exec >>"$LOG" 2>&1
echo "=== after_m3l384 START=$(date -Iseconds) pid=$$ ==="

log() { echo "$(date -Iseconds) $*"; }

# --- Phase 1: babysit compress/decompress ---
while pgrep -f 'run_hutter_100m_gate.sh m3l384' >/dev/null 2>&1 || \
      pgrep -f 'cmix_match3m_lstm384.*m3l384_100m' >/dev/null 2>&1; do
  pct="?"
  outsz="?"
  if [[ -f "$ROOT/tools/hutter/fx2-cmix/run/progress.log" ]]; then
    last=$(tail -1 "$ROOT/tools/hutter/fx2-cmix/run/progress.log" 2>/dev/null || true)
    pct=$(awk '{print $1}' <<<"$last")
    outsz=$(awk '{print $2}' <<<"$last")
  fi
  # Decompress does not update progress.log — use plaintext temp size instead.
  dout="$ROOT/tools/hutter/fx2-cmix/run/m3l384_100m.out.cmix.temp"
  if [[ -f "$dout" ]]; then
    dbytes=$(stat -c%s "$dout")
    dpct=$(python3 -c "print(round($dbytes/100000000*100,2))")
    pct="dec:$dpct"
    outsz="$dbytes"
  fi
  rss=$(ps -C cmix_match3m_lstm384 -o rss= 2>/dev/null | awk '{s+=$1} END{print s+0}')
  phase=$(python3 -c "import json;print(json.load(open('$PROGRESS')).get('phase','?'))" 2>/dev/null || echo '?')
  log "heartbeat phase=$phase progress%=$pct out=$outsz rss_kb=$rss"
  # lightweight progress enrich (do not clobber start fields)
  python3 - <<PY 2>/dev/null || true
import json
from pathlib import Path
from datetime import datetime
p=Path("$PROGRESS")
d={}
if p.exists():
  try: d=json.loads(p.read_text())
  except Exception: d={}
d.update({
  "heartbeat": datetime.now().astimezone().isoformat(timespec="seconds"),
  "progress_pct": "$pct",
  "output_size_est": "$outsz",
  "rss_kb": $rss,
})
p.write_text(json.dumps(d, indent=2)+"\n")
PY
  sleep 300
done

log "m3l384 100m processes gone — collecting gate result"
sleep 3

# --- Phase 2: normalize gate JSON + relative % ---
if [[ ! -f "$GATEJSON" ]]; then
  log "ERROR: missing $GATEJSON — abort (manual recover)"
  exit 1
fi

python3 - <<'PY'
import json
from pathlib import Path
from datetime import datetime

ROOT = Path("/srv/http/fractal_zip")
gate = json.loads((ROOT / "benchmarks/.hutter_100m_gate.json").read_text())
A0 = 14966085
NEED = 14786491
bytes_ = int(gate["bytes"])
delta = bytes_ - A0
rel = 100.0 * (A0 - bytes_) / A0
passed = bytes_ <= NEED and gate.get("rt") == "OK"
gate["delta"] = delta
gate["relative_gain_pct"] = round(rel, 4)
gate["gate_1_2pct"] = "PASS" if passed else "FAIL"
gate["gate_100m_bytes_max"] = NEED
gate["recorded_at"] = datetime.now().astimezone().isoformat(timespec="seconds")
(ROOT / "benchmarks/.hutter_100m_gate.json").write_text(json.dumps(gate, indent=2) + "\n")
print(json.dumps({
  "bytes": bytes_, "delta": delta, "relative_gain_pct": round(rel, 4),
  "gate_1_2pct": gate["gate_1_2pct"], "rt": gate.get("rt"),
  "elapsed_c": gate.get("elapsed_compress"), "elapsed_d": gate.get("elapsed_decompress"),
  "peak_c": gate.get("peak_rss_kb_compress"), "peak_d": gate.get("peak_rss_kb_decompress"),
}, indent=2))

final_path = ROOT / "benchmarks/.hutter_final_gate.json"
final = json.loads(final_path.read_text())
final["updated"] = datetime.now().astimezone().isoformat(timespec="minutes")
final["confirmed_100m_gain_pct"] = round(rel, 4)
final["m3l384_100m"] = {
  "bytes": bytes_,
  "delta": delta,
  "relative_gain_pct": round(rel, 4),
  "gate_1_2pct": gate["gate_1_2pct"],
  "rt": gate.get("rt"),
  "peak_rss_kb_compress": gate.get("peak_rss_kb_compress"),
  "peak_rss_kb_decompress": gate.get("peak_rss_kb_decompress"),
  "elapsed_compress": gate.get("elapsed_compress"),
  "elapsed_decompress": gate.get("elapsed_decompress"),
}
final["gate"] = "OPEN" if passed else "BLOCKED"
final["enwik9_run"] = "NOT_STARTED"  # still require explicit start even if OPEN
final["next"] = (
  ["L2_screens", "consider_enwik9_if_OPEN"] if passed
  else ["L2_screens", "time_safer_package_100m", "enwik9_blocked"]
)
final_path.write_text(json.dumps(final, indent=2) + "\n")

# append state note
state = ROOT / "benchmarks/.hutter_world_record_state.md"
note = (
  f"\n## Live update {datetime.now().astimezone().strftime('%Y-%m-%d %H:%M')}\n"
  f"- **m3l384 @100m:** {bytes_} B (Δ {delta:+d} vs A0, **{rel:.3f}%** relative). "
  f"1.2% gate: **{gate['gate_1_2pct']}**. RT={gate.get('rt')}.\n"
  f"- Next: L2 screens (`run_l2_screen_queue.sh`); enwik9 "
  f"{'still needs explicit start' if passed else 'BLOCKED'}.\n"
)
with state.open("a") as f:
  f.write(note)
PY

log "gate recorded; starting L2 screen queue"
# Desktop residue can leave swap slightly over the floor while MemAvailable is fine.
for attempt in 1 2 3 4 5 6; do
  if HUTTER_MAX_SWAP_MIB="${HUTTER_MAX_SWAP_MIB:-5120}" bash "$ROOT/benchmarks/hutter_memory_guard.sh"; then
    break
  fi
  log "memory guard retry $attempt/6 — sleep 60"
  if (( attempt == 6 )); then log "memory guard failed"; exit 2; fi
  sleep 60
done
bash "$ROOT/benchmarks/run_l2_screen_queue.sh"
log "L2 screen queue exit=$?"

# Optional: LSTM320 1m/10m Pareto if L2 queue left machine free
if ! pgrep -f 'cmix_' >/dev/null 2>&1; then
  log "starting LSTM320 1m+10m Pareto screens"
  CMIX320="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_lstm320"
  DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
  if [[ -x "$CMIX320" ]]; then
    bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" B_m3l320_1m \
      "$CMIX320" "$DICT" \
      "$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid" 198744 || true
    bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" B_m3l320_10m \
      "$CMIX320" "$DICT" \
      "$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid" 1751514 || true
  else
    log "skip LSTM320 — binary missing"
  fi
else
  log "skip LSTM320 — another cmix still present"
fi

log "=== after_m3l384 DONE=$(date -Iseconds) ==="
