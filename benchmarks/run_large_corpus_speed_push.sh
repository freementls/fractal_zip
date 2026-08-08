#!/usr/bin/env bash
# Speed-first large-corpus driver: same threading block as bytes-push, but --bench-profile=large-fast.
# Use for tik-tok wall-time work when Arc/large-fast outer already matches pinned fzc_bytes on samples.
#
#   bash benchmarks/run_large_corpus_speed_push.sh --only=test_files133_sample --large --no-case-timeout --json
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
	--bench-profile=large-fast \
	--jobs="${JOBS}" \
	--repeat=1 \
	"$@"
