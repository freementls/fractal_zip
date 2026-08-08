#!/usr/bin/env bash
# Wait for a run_benchmarks --out-json file to appear and print cases[0] summary.
# Usage: bash benchmarks/poll_bench_json.sh /path/to/expected.json [interval_sec] [max_wait_sec]
set -euo pipefail
JSON="${1:?json path}"
INTERVAL="${2:-30}"
MAX="${3:-7200}"
PHP="${PHP_BINARY:-php}"
elapsed=0
while [[ ! -f "$JSON" ]] && [[ "$elapsed" -lt "$MAX" ]]; do
	echo "[poll] waiting for ${JSON} (${elapsed}s / ${MAX}s)"
	sleep "$INTERVAL"
	elapsed=$((elapsed + INTERVAL))
done
if [[ ! -f "$JSON" ]]; then
	echo "[poll] timeout: no ${JSON}" >&2
	exit 1
fi
"$PHP" -r '
	$j=json_decode(file_get_contents($argv[1]),true);
	$c=$j["cases"][0]??array();
	printf("corpus=%s fzc_bytes=%s zip_seconds=%s outer_codec=%s bench_profile=%s\n",
		$c["label"]??"?",
		$c["fzc_bytes"]??"?",
		$c["zip_seconds"]??"?",
		$c["outer_codec"]??"?",
		$j["bench_profile"]??"null"
	);
' "$JSON"
echo "[poll] ok ${JSON}"
