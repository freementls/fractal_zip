#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/ablation_ledger_lib.php';

$soft = fractal_zip_ablation_evaluate_row(array(
	'arm' => 'semantic',
	'fzc_bytes' => 100,
	'gzip_bytes' => 200,
	'verify_ok' => false,
	'semantic_ok' => true,
	'verify_mismatch_files' => 0,
	'verify_file_count' => 10,
), 'full');
$hard = fractal_zip_ablation_evaluate_row(array(
	'arm' => 'broken',
	'fzc_bytes' => 100,
	'gzip_bytes' => 200,
	'verify_ok' => false,
	'semantic_ok' => false,
	'verify_mismatch_files' => 5,
	'verify_file_count' => 10,
), 'full');
$skip = fractal_zip_ablation_evaluate_row(array(
	'arm' => 'skipped',
	'fzc_bytes' => 100,
	'gzip_bytes' => 200,
	'verify_ok' => null,
), 'full');

assert($soft['promote'] === true, 'semantic soft rescue should promote');
assert($hard['promote'] === false, 'broken SHA without semantic should block');
assert($skip['promote'] === false, 'verify skipped should block tracker paste');

fwrite(STDOUT, "ablation_ledger_smoke OK\n");
exit(0);
