#!/usr/bin/env bash
# After push2 exits, run push3 lstm/pp96 no-dict baseline reprobe.
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$REPO/benchmarks/logs/wire460_push3_watch_$(date +%Y%m%d_%H%M%S).log"
exec >>"$LOG" 2>&1
echo "push3_watch start $(date -Iseconds)"

for i in $(seq 1 2880); do
  if ! pgrep -f 'run_enwik8_wire460_push2\.sh' >/dev/null 2>&1 \
    && ! pgrep -f 'bench_phda9_dict_token_sweep\.php' >/dev/null 2>&1 \
    && ! pgrep -f 'bench_enwik8_wire_slice_probe\.php.*wire460_preprocess' >/dev/null 2>&1; then
    break
  fi
  if (( i % 30 == 0 )); then
    echo "$(date -Iseconds) waiting for push2 (iter $i)"
  fi
  sleep 60
done

echo "$(date -Iseconds) push2 clear — launching push3"
exec "$REPO/benchmarks/run_enwik8_wire460_push3.sh"
