#!/usr/bin/env bash
# Bytes-push driver for large site trees: fair compressor threading + parallel cases + --bench-profile=large-bytes.
# Still uses SPEED-tier defaults from the profile (bounded outers); raise caps vs large-balanced for smaller .fz on big merged inners.
#
# Typical ratio pass (full fractal on a heavy-list name — use --large):
#   bash benchmarks/run_large_corpus_bytes_push.sh --only=test_files55_sample --large --json --no-case-timeout
# Silesia twelve-file folder (~212 MiB raw; build benchmarks/build_test_files133_silesia12.php first; --dry-run checks sources):
#   bash benchmarks/run_large_corpus_bytes_push.sh --only=test_files133 --large --json --no-case-timeout --out-json=benchmarks/.silesia133_largebytes.json
#   # bare digits: --only=133
# Add --out-json=/path/bench.json to keep a copy (default benchmarks/.last_bench.json is gitignored; see LARGE_CORPUS_SPEED.md).
#
# Default per-case timeout from profile is 150s unless you pass --case-timeout=… / --no-case-timeout.
# Override parallelism: JOBS=4 bash benchmarks/run_large_corpus_bytes_push.sh …
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

export FRACTAL_ZIP_7Z_MMT="${FRACTAL_ZIP_7Z_MMT:-on}"
export FRACTAL_ZIP_BENCH_ZSTD_THREADS="${FRACTAL_ZIP_BENCH_ZSTD_THREADS:-0}"
export FRACTAL_ZIP_BENCH_ZPAQ_THREADS="${FRACTAL_ZIP_BENCH_ZPAQ_THREADS:-0}"
export FRACTAL_ZIP_ARC_MT="${FRACTAL_ZIP_ARC_MT:-auto}"
export FRACTAL_ZIP_BENCH_ARC_MT="${FRACTAL_ZIP_BENCH_ARC_MT:-auto}"

JOBS="${JOBS:-}"
if [[ -z "${JOBS}" ]]; then
	if command -v nproc >/dev/null 2>&1; then
		JOBS="$(nproc)"
	else
		JOBS=4
	fi
fi
if [[ "${JOBS}" -gt 16 ]]; then
	JOBS=16
fi

export FRACTAL_ZIP_BENCH_MEMORY_LIMIT="${FRACTAL_ZIP_BENCH_MEMORY_LIMIT:-4G}"

exec php "${ROOT}/benchmarks/run_benchmarks.php" \
	--bench-profile=large-bytes \
	--jobs="${JOBS}" \
	--repeat=1 \
	"$@"
