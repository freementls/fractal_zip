#!/usr/bin/env bash
# A/B: default vs --metastruct (adaptive + identify census merge).
# Pass-through args like bench_adaptive_markers_compare.sh.
#
#   benchmarks/bench_metastruct_markers_compare.sh --only=test_files107 --limit=1
#
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP="${PHP_BINARY:-php}"
DEF_JSON="/tmp/fractal_zip_bench_default_metastruct.json"
MS_JSON="/tmp/fractal_zip_bench_metastruct.json"
echo "[bench_metastruct_markers_compare] default -> ${DEF_JSON}" >&2
"${PHP}" "${ROOT}/benchmarks/run_benchmarks.php" "$@" --json --no-save-last-json > "${DEF_JSON}"
echo "[bench_metastruct_markers_compare] metastruct -> ${MS_JSON}" >&2
"${PHP}" "${ROOT}/benchmarks/run_benchmarks.php" "$@" --metastruct --json --no-save-last-json > "${MS_JSON}"
if command -v jq >/dev/null 2>&1; then
	echo "--- summary ---" >&2
	jq '{metastruct_bench_mode, fzc_total: .totals.fzc_bytes, verify_failures: [.cases[] | select(.verify_ok == false) | .label]}' "${DEF_JSON}" >&2
	jq '{metastruct_bench_mode, fzc_total: .totals.fzc_bytes, verify_failures: [.cases[] | select(.verify_ok == false) | .label]}' "${MS_JSON}" >&2
	jq -n \
		--slurpfile a "${DEF_JSON}" --slurpfile b "${MS_JSON}" \
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
					verify_metastruct: $s.verify_ok
				}
			]
		}'
else
	echo "[bench_metastruct_markers_compare] install jq for delta; JSON: ${DEF_JSON} ${MS_JSON}" >&2
fi
