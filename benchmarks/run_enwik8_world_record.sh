#!/usr/bin/env bash
# One-liner entry for enwik8 world-record benchmark (hours-scale; needs test_files109 built).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
FRACTAL_ZIP_BENCH_MEMORY_LIMIT="${FRACTAL_ZIP_BENCH_MEMORY_LIMIT:-4G}" \
  php benchmarks/run_benchmarks.php \
    --only=test_files109 \
    --large \
    --bench-profile=world-record \
    --no-case-timeout \
    --json \
    --out-json=benchmarks/.enwik8_world_record.json \
    "$@"
