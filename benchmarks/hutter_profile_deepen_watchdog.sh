#!/usr/bin/env bash
# Restart deepen watcher if it exits before ladder DONE with B0 gate.
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$ROOT/benchmarks/.hutter_logs/profile_deepen.log"
while true; do
  if rg -q 'B0 10m gate=' "$ROOT/benchmarks/.hutter_logs/consistent_ladder.log" 2>/dev/null \
     && rg -q 'deepen done' "$LOG" 2>/dev/null; then
    echo "$(date -Iseconds) watchdog: deepen finished" >>"$LOG"
    exit 0
  fi
  # Exact deepen script only — do not match this watchdog's own argv.
  if ! pgrep -f 'benchmarks/hutter_profile_deepen\.sh' >/dev/null; then
    echo "$(date -Iseconds) watchdog: relaunch deepen" >>"$LOG"
    setsid bash "$ROOT/benchmarks/hutter_profile_deepen.sh" </dev/null >/dev/null 2>&1 &
  fi
  sleep 120
done
