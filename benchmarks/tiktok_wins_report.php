#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Print Tik/Tok winners from a tiktok sweep directory.
 *
 * tik = speed winner (min zip_seconds)
 * tok = bytes winner (min fzc_bytes)
 *
 * Usage:
 *   php benchmarks/tiktok_wins_report.php --out-dir=benchmarks/.tiktok_sweep_YYYYmmdd_HHMMSS
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$outDir = '';
foreach (array_slice($argv, 1) as $a) {
	if (!is_string($a)) {
		continue;
	}
	if (strncmp($a, '--out-dir=', 10) === 0) {
		$outDir = trim(substr($a, 10));
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php benchmarks/tiktok_wins_report.php --out-dir=PATH\n";
		exit(0);
	}
}

if ($outDir === '' || !is_dir($outDir)) {
	fwrite(STDERR, "tiktok_wins_report: pass --out-dir=existing_dir\n");
	exit(2);
}

$files = array_values(array_filter(scandir($outDir) ?: array(), static function (string $f): bool {
	return str_ends_with($f, '.json')
		&& !str_starts_with($f, 'recommend')
		&& !str_contains($f, 'probe_recommend/recommend');
}));
sort($files, SORT_STRING);

$rows = array();
foreach ($files as $fn) {
	$path = $outDir . DIRECTORY_SEPARATOR . $fn;
	$j = bench_json_decode_file_assoc_try($path, 'tiktok_wins_report');
	if ($j === null) {
		continue;
	}
	$c = $j['cases'][0] ?? array();
	$fzc = (int) ($c['fzc_bytes'] ?? 0);
	$zip = (float) ($c['zip_seconds'] ?? 0);
	if ($fzc <= 0 || $zip <= 0.0) {
		continue;
	}
	$rows[] = array(
		'tag' => preg_replace('/\.json$/', '', $fn),
		'fzc_bytes' => $fzc,
		'zip_seconds' => $zip,
		'outer' => (string) ($c['outer_codec'] ?? '?'),
	);
}

if ($rows === array()) {
	fwrite(STDERR, "tiktok_wins_report: no usable rows under {$outDir}\n");
	exit(1);
}

$speedWinner = null;
$bytesWinner = null;
foreach ($rows as $r) {
	if ($speedWinner === null || (float) $r['zip_seconds'] < (float) $speedWinner['zip_seconds']) {
		$speedWinner = $r;
	}
	if ($bytesWinner === null || (int) $r['fzc_bytes'] < (int) $bytesWinner['fzc_bytes']) {
		$bytesWinner = $r;
	}
}

$minBytes = (int) $bytesWinner['fzc_bytes'];
$eligibleMax = (int) floor((float) $minBytes * 1.01);
$balanced = null;
foreach ($rows as $r) {
	if ((int) $r['fzc_bytes'] <= $eligibleMax) {
		if ($balanced === null || (float) $r['zip_seconds'] < (float) $balanced['zip_seconds']) {
			$balanced = $r;
		}
	}
}

$payload = array(
	'out_dir' => $outDir,
	'tik_speed_winner' => $speedWinner,
	'tok_bytes_winner' => $bytesWinner,
	'balanced_winner_1pct_bytes' => $balanced,
	'rows' => $rows,
);

$js = bench_json_encode_try($payload, true);
if ($js !== null) {
	file_put_contents($outDir . DIRECTORY_SEPARATOR . 'wins.json', $js . "\n");
}

echo "tik(speed): " . (string) $speedWinner['tag']
	. " zip_s=" . sprintf('%.2f', (float) $speedWinner['zip_seconds'])
	. " bytes=" . (string) $speedWinner['fzc_bytes']
	. " outer=" . (string) $speedWinner['outer'] . "\n";
echo "tok(bytes): " . (string) $bytesWinner['tag']
	. " bytes=" . (string) $bytesWinner['fzc_bytes']
	. " zip_s=" . sprintf('%.2f', (float) $bytesWinner['zip_seconds'])
	. " outer=" . (string) $bytesWinner['outer'] . "\n";
if (is_array($balanced)) {
	echo "balanced(<=1% bytes): " . (string) $balanced['tag']
		. " bytes=" . (string) $balanced['fzc_bytes']
		. " zip_s=" . sprintf('%.2f', (float) $balanced['zip_seconds']) . "\n";
}
$banner = "WINNER tik: " . (string) $speedWinner['tag']
	. " (zip_s=" . sprintf('%.2f', (float) $speedWinner['zip_seconds']) . ")"
	. " | tok: " . (string) $bytesWinner['tag']
	. " (bytes=" . (string) $bytesWinner['fzc_bytes'] . ")";
echo $banner . "\n";
file_put_contents($outDir . DIRECTORY_SEPARATOR . 'wins_banner.txt', $banner . "\n");
echo "wrote {$outDir}/wins.json\n";
