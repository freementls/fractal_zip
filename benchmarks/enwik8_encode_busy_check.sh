#!/usr/bin/env bash
# Return 0 if a real enwik encode holds test_files109 (high-RSS runner or flock lock).
# Stale shell parents (~350 KiB RSS) from finished inner/harmony runs are ignored.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOCK="${ROOT}/benchmarks/.enwik8_encode_test_files109.lock"
# Active encodes allocate hundreds of MiB+; stale PHP parents stay at ~350 KiB RSS.
MIN_RSS_KB="${ENWIK8_ACTIVE_MIN_RSS_KB:-51200}"
PATTERN="${ENWIK8_BUSY_PATTERN:-run_enwik8_inner_experiment\.php|run_enwik8_encode_only\.php|run_enwik8_textcodec_encode\.php}"
CHECK_LOCK="${ENWIK8_BUSY_CHECK_LOCK:-1}"

enwik8_active_encode_pids() {
  local pid rss state
  while IFS= read -r pid; do
    [[ -n "$pid" ]] || continue
    [[ -r "/proc/${pid}/status" ]] || continue
    state=$(awk '/^State:/ {print $2}' "/proc/${pid}/status" 2>/dev/null || true)
    [[ "$state" == "Z" ]] && continue
    # Finished encodes linger as sleeping (~350 KiB–60 MiB); real work stays R/D with high RSS.
    [[ "$state" != "R" && "$state" != "D" ]] && continue
    rss=$(awk '/^VmRSS:/ {print $2}' "/proc/${pid}/status" 2>/dev/null || echo 0)
    [[ -z "$rss" ]] && rss=0
    if (( rss >= MIN_RSS_KB )); then
      echo "$pid"
    fi
  done < <(pgrep -f "$PATTERN" 2>/dev/null || true)
}

if mapfile -t active < <(enwik8_active_encode_pids); ((${#active[@]} > 0)); then
  echo "active enwik encode PIDs (RSS>=${MIN_RSS_KB} KiB): ${active[*]}"
  exit 0
fi

if [[ "$CHECK_LOCK" == "1" ]]; then
  exec 9>"${LOCK}"
  if ! flock -n 9; then
    echo "lock held: ${LOCK}"
    exit 0
  fi
  flock -u 9
fi
exit 1
