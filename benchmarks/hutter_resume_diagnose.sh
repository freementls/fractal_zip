#!/usr/bin/env bash
# Crash-resilient resume helper for the Hutter world-record campaign.
# Safe to run after Cursor SIGILL / session death — read-only except listing.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
echo "=== $(date -Iseconds) Hutter resume diagnose ==="
free -h | head -2
echo
echo "--- kernel (OOM vs SIGILL) ---"
journalctl -k -n 40 --no-pager 2>/dev/null | rg -i 'oom|killed process|invalid opcode|cursor|cmix' || echo '(none)'
echo
echo "--- cmix processes ---"
ps -eo pid,comm,rss,etime,pcpu,state | rg 'cmix|PID' || echo 'idle'
echo
echo "--- campaign state ---"
head -40 "$ROOT/benchmarks/.hutter_world_record_state.md"
echo
echo "--- latest screens ---"
tail -5 "$ROOT/benchmarks/.hutter_slice_screens.jsonl" 2>/dev/null || echo '(none)'
echo
echo "--- 100m gate json ---"
cat "$ROOT/benchmarks/.hutter_100m_gate.json" 2>/dev/null || echo '(not run yet)'
echo
echo "--- final gate ---"
python3 -m json.tool "$ROOT/benchmarks/.hutter_final_gate.json" 2>/dev/null | head -40
echo
echo "--- queue log (tail) ---"
tail -15 /tmp/hutter_slice_queue.log 2>/dev/null || echo '(no queue log)'
echo
echo "If Cursor crashed with SIGILL: cursor --disable-gpu"
echo "If 100m/baseline mid-run: check run/*_100m.fx2 size and /tmp/*_100m_c.time progress"
