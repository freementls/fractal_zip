<?php
/**
 *   php fzc_one_case.php path/to/bench.json test_files72_sample_micro
 * Echo fzc_bytes for the named case, or 0.
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
$j = bench_json_decode_file_assoc_try($argv[1] ?? '', 'fzc_one_case');
$want = (string) ($argv[2] ?? '');
if ($j === null || $want === '') {
	echo "0\n";
	exit(1);
}
foreach ($j['cases'] ?? array() as $c) {
	if (($c['label'] ?? '') === $want) {
		echo (string) ((int) ($c['fzc_bytes'] ?? 0)) . "\n";
		exit(0);
	}
}
echo "0\n";
exit(1);
