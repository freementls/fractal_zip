#!/usr/bin/env bash
# Gold filled lifestyle table (no case timeout; GP lakes when present).
set -euo pipefail
cd "$(dirname "$0")/.."
export FRACTAL_ZIP_FATAL_THROW=1
# Avoid native-extract-cache slot/lock stalls (stale multi-uid .lock → 120 s hangs on
# squash zpaq .fz verify). Direct extract is fine for gold wall clocks.
export FRACTAL_ZIP_NATIVE_EXTRACT_CACHE="${FRACTAL_ZIP_NATIVE_EXTRACT_CACHE:-0}"
# Default 24G for GP lakes; allow override only via FRACTAL_ZIP_LIFESTYLE_FULL_TABLE_MEMORY.
export FRACTAL_ZIP_BENCH_MEMORY_LIMIT="${FRACTAL_ZIP_LIFESTYLE_FULL_TABLE_MEMORY:-24G}"
export FRACTAL_ZIP_BENCH_SKIP_SLOW_PAQ_EXT=0
# Never burn hours on cmix/paq8px in min-ext (256 KiB cap); fair-full can raise this.
export FRACTAL_ZIP_BENCH_PAQ_SQUASH_MAX_RAW_BYTES="${FRACTAL_ZIP_BENCH_PAQ_SQUASH_MAX_RAW_BYTES:-262144}"
# Parallel zlib for multi-GiB lakes (cache key stays zlib — see benchGzipBaselineCacheKey).
export FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ="${FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ:-1}"
export FRACTAL_ZIP_BENCH_PIGZ_P="${FRACTAL_ZIP_BENCH_PIGZ_P:-auto}"
# Keep .work across cases (avoids deleting active trees / hardlink copies mid-suite).
export FRACTAL_ZIP_BENCH_NO_CASE_DISK_SWEEP=1
# Do not inherit agent/probe TMPDIR (leaks into sys_get_temp_dir / compressor scratch).
unset TMPDIR TMP TEMP || true
export TMPDIR="${FRACTAL_ZIP_LIFESTYLE_FULL_TABLE_TMPDIR:-/tmp}"
export TMP="$TMPDIR"
export TEMP="$TMPDIR"
# Drop stale CLI opcache so gzip/7z lifestyle changes are live.
rm -rf "${FRACTAL_ZIP_OPCACHE_FILE_CACHE:-/tmp/fz_opcache_${UID:-1000}}" 2>/dev/null || true
# Apply memory before PHP bootstrap (env alone is not enough if a parent exported 8G).
exec php -d "memory_limit=${FRACTAL_ZIP_BENCH_MEMORY_LIMIT}" benchmarks/run_benchmarks.php --large --json \
  --no-case-disk-sweep \
  --out-json=benchmarks/.lifestyle_full_table.json \
  "$@"
