#!/usr/bin/env bash
# Mahoney x12: one bench case per invocation (avoids batch encode drift vs --only=108,…,132).
# Merges cases[] into a single JSON for silesia_sum_fzc_from_bench_json.php.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
OUT="${1:-benchmarks/.silesia12_perfile_progress.json}"
PHP="${PHP_BINARY:-php}"
export FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE="${FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE:-0}"
export FRACTAL_ZIP_BENCH_MEMORY_LIMIT="${FRACTAL_ZIP_BENCH_MEMORY_LIMIT:-4G}"
LABELS=(
	test_files108 test_files116 test_files117 test_files118 test_files119 test_files120
	test_files124 test_files125 test_files126 test_files130 test_files131 test_files132
)
CHUNKS=()
for label in "${LABELS[@]}"; do
	echo "[silesia12-perfile] ${label}" >&2
	chunk="$(mktemp)"
	"$PHP" benchmarks/run_benchmarks.php --only="$label" --large --no-case-timeout --no-verify \
		--json --no-save-last-json --out-json="$chunk" >&2 || { rm -f "$chunk"; exit 1; }
	CHUNKS+=("$chunk")
done
"$PHP" -r '
	$outPath = $argv[1] ?? "";
	$chunks = array_slice($argv, 2);
	if ($outPath === "") {
		fwrite(STDERR, "merge: missing output path\n");
		exit(1);
	}
	$cases = [];
	foreach ($chunks as $p) {
		$j = json_decode((string) file_get_contents($p), true);
		if (is_array($j) && isset($j["cases"][0])) {
			$cases[] = $j["cases"][0];
		}
	}
	$out = ["generated" => date("c"), "bench_profile" => "per-file-isolated", "cases" => $cases];
	if (file_put_contents($outPath, json_encode($out, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n") === false) {
		fwrite(STDERR, "merge: cannot write {$outPath}\n");
		exit(1);
	}
' "$OUT" "${CHUNKS[@]}"
for chunk in "${CHUNKS[@]}"; do rm -f "$chunk"; done
echo "[silesia12-perfile] wrote ${OUT}" >&2
"$PHP" benchmarks/silesia_sum_fzc_from_bench_json.php "$OUT"
"$PHP" benchmarks/analyze_bench_native_passthrough.php "$OUT" >&2
