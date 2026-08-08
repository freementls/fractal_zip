#!/usr/bin/env bash
# Run run_benchmarks.php and always sweep stray compressors on exit (success, failure, Ctrl-C).
#
# Usage: benchmarks/run_bench_with_cleanup.sh [run_benchmarks.php args...]
# Example:
#   FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G benchmarks/run_bench_with_cleanup.sh \
#     --only=test_files78 --large --no-case-timeout --json \
#     --out-json=benchmarks/.silesia78_peel_pmb.json

set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cleanup() {
	"${REPO}/benchmarks/kill_stray_bench_procs.sh" || true
}
trap cleanup EXIT INT TERM
cd "$REPO"
exec php -d opcache.enable_cli=0 benchmarks/run_benchmarks.php "$@"
