#!/bin/bash
# Liveness/memory monitor for a detached full encode. Usage: encode_watchdog.sh <pattern> <encode_log> [out_log]
PATTERN="${1:-run_enwik8_pp96_refresh}"
ENC_LOG="${2:-benchmarks/logs/full_encode_paq8px_shootout_par2.log}"
OUT="${3:-benchmarks/logs/encode_watchdog.log}"
for i in $(seq 1 480); do
	sleep 60
	if ! pgrep -f "$PATTERN" >/dev/null; then
		echo "$(date +%H:%M:%S) ENCODE_" "DEAD" >> "$OUT"
		tail -5 "$ENC_LOG" >> "$OUT"
		break
	fi
	echo "$(date +%H:%M:%S) alive mem_avail=$(awk '/MemAvailable/{print $2}' /proc/meminfo)kB paq8px=$(pgrep -c paq8px || true)" >> "$OUT"
done
