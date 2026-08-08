#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
PHP="${PHP:-php}"

echo "[watch] $(date -Iseconds) waiting for pp96..."
while ! "${PHP}" benchmarks/enwik8_exp_has_fzc.php benchmarks/.enwik8_exp_pp96.json 2>/dev/null; do
	sleep 90
done

fzc="$("${PHP}" -r '
$j = json_decode((string) file_get_contents("benchmarks/.enwik8_exp_pp96.json"), true);
foreach ($j["cases"] ?? [] as $c) {
	if (($c["label"] ?? "") === "test_files109") {
		echo (int) ($c["fzc_bytes"] ?? 0);
		exit(0);
	}
}
echo 0;
')"
echo "[watch] pp96 fzc=${fzc}"
if [[ "${fzc}" -gt 0 && "${fzc}" -lt 19620079 ]]; then
	"${PHP}" benchmarks/patch_enwik8_world_record_fzc.php benchmarks/.enwik8_exp_pp96.json
	echo "[watch] pp96 beat pp48 — set FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=96 in bench_world_record_env.php"
fi

echo "[watch] waiting for experiment grid..."
while pgrep -f "run_enwik8_experiment_grid.sh" >/dev/null 2>&1; do
	sleep 90
done

"${PHP}" benchmarks/summarize_enwik8_experiments.php
"${PHP}" benchmarks/compare_enwik8_world_record.php
echo "[watch] done $(date -Iseconds)"
