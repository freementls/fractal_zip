#!/usr/bin/env bash
# Run fractal_zip benchmarks at lowest CPU scheduling priority and (when available) idle I/O class
# so other work stays responsive. Same arguments as run_benchmarks.php.
#
# Usage:
#   ./benchmarks/run_benchmarks_low_priority.sh --only=test_files2 --json
#   ./benchmarks/run_benchmarks_low_priority.sh --bench-profile=large-bytes --jobs=4 --only=test_files30 --json
#   PHP=php8.2 ./benchmarks/run_benchmarks_low_priority.sh
# Large trees (parallel --jobs, compressor threading, tiered `--bench-profile` incl. `large-bytes` / `run_large_corpus_bytes_push.sh`): benchmarks/LARGE_CORPUS_SPEED.md
# Optional: verify fractal_zip.php vs fractal_zip-undeep-unwrap.php FZB* Brotli markers (fast) before bench:
#   CHECK_UNDEEP_SYNC=1 ./benchmarks/run_benchmarks_low_priority.sh --only=...
#   FRACTAL_ZIP_BENCH_CHECK_UNDEEP_SYNC=1 ./benchmarks/...  (alias; same as in php run_benchmarks.php)
# After a successful check, the env is cleared so the child run_benchmarks.php does not run it twice.
# Or pass through: ./benchmarks/run_benchmarks_low_priority.sh --check-undeep-sync  (runs only that check, then exits; under nice/ionice).
#
set -euo pipefail
DIR="$(cd "$(dirname "$0")" && pwd)"
PHP_BIN="${PHP:-php}"
MAIN="$DIR/run_benchmarks.php"
if [[ "${CHECK_UNDEEP_SYNC:-}" == "1" || "${FRACTAL_ZIP_BENCH_CHECK_UNDEEP_SYNC:-}" == "1" ]]; then
	"$PHP_BIN" "$DIR/check_undeep_fzb_brotli_sync.php" || exit 1
	unset CHECK_UNDEEP_SYNC FRACTAL_ZIP_BENCH_CHECK_UNDEEP_SYNC 2>/dev/null || true
fi
if command -v ionice >/dev/null 2>&1; then
	exec nice -n 19 ionice -c 3 "$PHP_BIN" "$MAIN" "$@"
else
	exec nice -n 19 "$PHP_BIN" "$MAIN" "$@"
fi
