#!/usr/bin/env bash
# Run benchmarks at nice 19 + ionice idle, write JSON, then print bytes-win table.
# Passes through all arguments to run_benchmarks.php (e.g. --bench-profile=…, --jobs=…, --only=…, --limit=…, --no-case-timeout).
#
# Examples:
#   ./benchmarks/search_bytes_wins_low_priority.sh
#   ./benchmarks/search_bytes_wins_low_priority.sh --limit=20
#   ./benchmarks/search_bytes_wins_low_priority.sh --only=test_files52,test_files62 --json-out=/tmp/b.json
# Large-tree bytes-push preset (see benchmarks/LARGE_CORPUS_SPEED.md):
#   ./benchmarks/search_bytes_wins_low_priority.sh --bench-profile=large-bytes --jobs=4 --only=test_files30 --json-out=/tmp/lb.json
# Big wins only (>33% smaller than best gzip/7z/ext):
#   ./benchmarks/search_bytes_wins_low_priority.sh --min-pct=33
# Compress-time audit (zip_s vs min baseline compress seconds); optional margin in seconds:
#   ./benchmarks/search_bytes_wins_low_priority.sh --compress-time-audit
#   ./benchmarks/search_bytes_wins_low_priority.sh --compress-time-audit --zip-time-margin=0.25
# Undeep sync preflight (optional): CHECK_UNDEEP_SYNC=1 or FRACTAL_ZIP_BENCH_CHECK_UNDEEP_SYNC=1 (see run_benchmarks.php).
#
set -euo pipefail
DIR="$(cd "$(dirname "$0")" && pwd)"
REPO="$(cd "$DIR/.." && pwd)"
OUT="${FRACTAL_ZIP_BENCH_JSON_OUT:-$DIR/.bytes_win_scan_last.json}"
MINPCT=""
COMPRESS_AUDIT=false
ZIP_MARGIN=""
PASS=()
while [[ $# -gt 0 ]]; do
	case "$1" in
		--min-pct=*)
			MINPCT="${1#--min-pct=}"
			shift
			;;
		--compress-time-audit)
			COMPRESS_AUDIT=true
			shift
			;;
		--zip-time-margin=*)
			ZIP_MARGIN="${1#--zip-time-margin=}"
			shift
			;;
		--json-out=*)
			OUT="${1#--json-out=}"
			shift
			;;
		*)
			PASS+=("$1")
			shift
			;;
	esac
done
# --zip-time-margin only affects --compress-time-audit output; enable audit when margin is set.
[[ -n "${ZIP_MARGIN}" ]] && COMPRESS_AUDIT=true
"$DIR/run_benchmarks_low_priority.sh" --json "${PASS[@]}" > "$OUT"
REPORT_ARGS=()
[[ -n "${MINPCT}" ]] && REPORT_ARGS+=(--min-pct="${MINPCT}")
[[ "${COMPRESS_AUDIT}" == true ]] && REPORT_ARGS+=(--compress-time-audit)
[[ -n "${ZIP_MARGIN}" ]] && REPORT_ARGS+=(--zip-time-margin="${ZIP_MARGIN}")
php "$DIR/report_bytes_wins.php" "${REPORT_ARGS[@]}" "$OUT"
