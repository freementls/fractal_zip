#!/usr/bin/env bash
# Wait for full verify, then scorecard + compliance.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
export FRACTAL_ZIP_LOW_MEMORY=1
PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-768M}"
LOG="$REPO/benchmarks/logs/hutter_after_verify_$(date +%Y%m%d_%H%M%S).log"
exec > >(tee -a "$LOG") 2>&1

echo "after_verify wait start $(date -Iseconds)"

LOCK="$REPO/benchmarks/logs/.lock_enwik8_verify.pid"
for i in $(seq 1 720); do
  if [[ ! -f "$LOCK" ]]; then
    break
  fi
  if ! fuser "$LOCK" >/dev/null 2>&1; then
    break
  fi
  if (( i % 10 == 0 )); then
    echo "$(date -Iseconds) still waiting for verify (iter $i)"
  fi
  sleep 60
done

echo "=== $(date -Iseconds) scorecard ==="
php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/run_enwik8_hutter_scorecard.php" || true

echo "=== $(date -Iseconds) compliance ==="
php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/run_enwik8_hutter_compliance.php" \
  --target=beat146_hardware || true

echo "after_verify done $(date -Iseconds) → $LOG"
