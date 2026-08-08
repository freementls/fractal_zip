#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Flag cases where .fz size equals the 7z baseline column (native 7z passthrough).
 *
 *   php benchmarks/analyze_bench_native_passthrough.php path/to/bench.json
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$path = $argv[1] ?? '';
if ($path === '' || !is_readable($path)) {
	fwrite(STDERR, "Usage: php analyze_bench_native_passthrough.php <bench.json>\n");
	exit(2);
}

$data = bench_json_decode_file_assoc_try($path, 'analyze_bench_native_passthrough', 512, 0, false);
if ($data === null) {
	exit(1);
}
$cases = $data['cases'] ?? [];
if (!is_array($cases)) {
	fwrite(STDERR, "JSON missing cases[]\n");
	exit(1);
}

$pt = 0;
$sumFzc = $sum7z = 0;
echo "label                  fzc_bytes  seven_zip  passthrough  outer\n";
foreach ($cases as $row) {
	if (!is_array($row)) {
		continue;
	}
	$label = (string) ($row['label'] ?? '');
	$fzc = (int) ($row['fzc_bytes'] ?? 0);
	$sz = (int) ($row['seven_zip_folder_bytes'] ?? 0);
	$isPt = ($fzc > 0 && $fzc === $sz);
	if ($isPt) {
		$pt++;
	}
	$sumFzc += $fzc;
	$sum7z += $sz;
	printf("%-22s %10d %10d %s           %s\n", $label, $fzc, $sz, $isPt ? 'Y' : 'N', (string) ($row['outer_codec'] ?? ''));
}
$n = count($cases);
echo "\ncases={$n} passthrough={$pt} sum_fzc={$sumFzc} sum_7z={$sum7z}\n";
if ($pt > 0) {
	fwrite(STDERR, "hint: native 7z passthrough inflates sum_fzc when fractal+arc beats 7z; retry with FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0\n");
}
