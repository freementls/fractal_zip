#!/usr/bin/env bash
# A/B: default driver vs --adaptive-markers (FRACTAL_ZIP_ADAPTIVE_MARKERS=1, unified stream off).
# All arguments are passed through to both runs (e.g. --only=test_files2 --limit=3 --json is optional;
# this script always adds --json --no-save-last-json).
# jq verify_failures uses (.verify_ok == false) so skipped verify (--no-verify → null) is not counted.
#
#   benchmarks/bench_adaptive_markers_compare.sh --only=test_files2 --limit=1
#
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP="${PHP_BINARY:-php}"
DEF_JSON="/tmp/fractal_zip_bench_default_markers.json"
AD_JSON="/tmp/fractal_zip_bench_adaptive_markers.json"
echo "[bench_adaptive_markers_compare] default -> ${DEF_JSON}" >&2
"${PHP}" "${ROOT}/benchmarks/run_benchmarks.php" "$@" --json --no-save-last-json > "${DEF_JSON}"
echo "[bench_adaptive_markers_compare] adaptive -> ${AD_JSON}" >&2
"${PHP}" "${ROOT}/benchmarks/run_benchmarks.php" "$@" --adaptive-markers --json --no-save-last-json > "${AD_JSON}"
if command -v jq >/dev/null 2>&1; then
	echo "--- summary ---" >&2
	jq '{adaptive_markers_bench_mode, fzc_total: .totals.fzc_bytes, verify_failures: [.cases[] | select(.verify_ok == false) | .label]}' "${DEF_JSON}" >&2
	jq '{adaptive_markers_bench_mode, fzc_total: .totals.fzc_bytes, verify_failures: [.cases[] | select(.verify_ok == false) | .label]}' "${AD_JSON}" >&2
	jq -n \
		--slurpfile a "${DEF_JSON}" --slurpfile b "${AD_JSON}" \
		'{
			fzc_bytes_total_delta: (($b[0].totals.fzc_bytes // 0) - ($a[0].totals.fzc_bytes // 0)),
			by_label: [
				($a[0].cases // [])[] as $r
				| ($b[0].cases // [] | map(select(.label == $r.label))[0]) as $s
				| select($s != null)
				| {
					label: $r.label,
					fzc_delta: (($s.fzc_bytes // 0) - ($r.fzc_bytes // 0)),
					verify_default: $r.verify_ok,
					verify_adaptive: $s.verify_ok
				}
			]
		}'
else
	echo "[bench_adaptive_markers_compare] install jq for a printed delta; JSON:" >&2
	echo "  ${DEF_JSON}" >&2
	echo "  ${AD_JSON}" >&2
fi
