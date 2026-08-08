#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Print a BYTES_WIN_TRACKER table row from a test_files133 bench JSON (stdout only).
 *
 *   php benchmarks/apply_test_files133_row_from_json.php benchmarks/.silesia133_speed_push.json
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$path = $argv[1] ?? '';
if ($path === '' || !is_readable($path)) {
	fwrite(STDERR, "Usage: php apply_test_files133_row_from_json.php <bench.json>\n");
	exit(2);
}

$j = bench_json_decode_file_assoc_try($path, 'apply_test_files133_row');
if ($j === null) {
	exit(1);
}

$c = null;
foreach ($j['cases'] ?? array() as $row) {
	if (is_array($row) && (string) ($row['label'] ?? '') === 'test_files133') {
		$c = $row;
		break;
	}
}
if ($c === null) {
	$c = $j['cases'][0] ?? null;
}
if (!is_array($c)) {
	fwrite(STDERR, "no cases[0]\n");
	exit(1);
}

$raw = (int) ($c['raw_bytes'] ?? 0);
$fzc = (int) ($c['fzc_bytes'] ?? 0);
$gz = (int) ($c['gzip9_bundle_bytes'] ?? 0);
$z7 = (int) ($c['seven_zip_folder_bytes'] ?? 0);
$ext = isset($c['best_ext_folder_bytes']) && $c['best_ext_folder_bytes'] !== null
	? (int) $c['best_ext_folder_bytes'] : null;
$extW = (string) ($c['best_ext_winner'] ?? '—');
$outer = (string) ($c['outer_codec'] ?? '—');
$verify = $c['verify_ok'] ?? null;
$verifyStr = $verify === null ? '—' : ($verify ? 'yes' : 'no');

$cands = array_filter(array($gz, $z7, $ext), static fn ($x) => $x !== null && $x > 0);
$bestOther = $cands !== [] ? min($cands) : null;
$bytesWin = ($bestOther !== null && $fzc > 0 && $fzc <= $bestOther) ? 'yes' : 'no';

$fmt = static fn (int $n) => number_format($n, 0, '.', ' ');

$extCol = $ext !== null ? $fmt($ext) : '—';
$notes = 'Paste into BYTES_WIN_TRACKER.md. JSON: ' . basename($path)
	. '; profile=' . (string) ($j['bench_profile'] ?? 'null')
	. '; zip_s≈' . (string) ($c['zip_seconds'] ?? '?');

echo "| test_files133 | {$fmt($raw)} | {$fmt($gz)} | {$fmt($z7)} | {$extCol} | {$extW} | {$fmt($fzc)} | {$bytesWin} | {$verifyStr} | {$notes} |\n";
