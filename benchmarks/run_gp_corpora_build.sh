#!/usr/bin/env bash
# Detached-friendly wrapper: retries truncated downloads until GP lakes are green.
set -uo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
LOG="${ROOT}/benchmarks/.gp_corpus_cache/build_daemon.log"
mkdir -p "${ROOT}/benchmarks/.gp_corpus_cache"
exec >>"$LOG" 2>&1
echo "==== $(date -Is) start pid=$$ ===="
# Keep retrying until coverage passes (downloads resume via .part / wget -c).
for attempt in $(seq 1 50); do
  echo "---- attempt $attempt $(date -Is) ----"
  php benchmarks/build_test_files202_210_gp.php "$@" || true
  if php benchmarks/report_gp_corpora_coverage.php; then
    echo "==== $(date -Is) PASS ===="
    exit 0
  fi
  echo "coverage not green; sleeping 15s before retry"
  sleep 15
done
echo "==== $(date -Is) FAILED after retries ===="
exit 1
