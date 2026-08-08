#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Report phda9 squash progress (archive size vs Hutter target).
 *
 * Usage: php benchmarks/bench_enwik8_paq_progress.php
 */

$hutter = 15284944;
$tmp = sys_get_temp_dir();
$patterns = array(
	$tmp . DIRECTORY_SEPARATOR . 'fzpaqbench_*' . DIRECTORY_SEPARATOR . '*.phda9',
	$tmp . DIRECTORY_SEPARATOR . 'fzpaqexport_*' . DIRECTORY_SEPARATOR . '*.phda9',
	$tmp . DIRECTORY_SEPARATOR . 'fzpaq_*.phda9',
);
$glob = array();
foreach ($patterns as $pat) {
	$glob = array_merge($glob, glob($pat) ?: array());
}
$best = null;
$bestPath = null;
foreach ($glob as $path) {
	if (!is_file($path)) {
		continue;
	}
	$sz = (int) filesize($path);
	if ($best === null || $sz > $best) {
		$best = $sz;
		$bestPath = $path;
	}
}

$running = trim((string) shell_exec("pgrep -f 'tools/phda9/phda9' 2>/dev/null | head -1"));
$elapsed = '';
if ($running !== '') {
	$ps = shell_exec('ps -p ' . escapeshellarg($running) . ' -o etime= 2>/dev/null');
	$elapsed = trim((string) $ps);
}

echo "phda9 progress\n";
echo '  running: ' . ($running !== '' ? "yes (pid {$running}, elapsed {$elapsed})" : 'no') . "\n";
if ($bestPath !== null && $best !== null) {
	echo '  archive: ' . $bestPath . "\n";
	echo '  bytes:   ' . number_format($best) . ' (' . number_format($best / (1024 * 1024), 2) . " MiB)\n";
	echo '  hutter:  ' . number_format($hutter) . ' (' . number_format($hutter / (1024 * 1024), 2) . " MiB)\n";
	$delta = $best - $hutter;
	echo '  delta:   ' . ($delta <= 0 ? '' : '+') . number_format($delta) . " B\n";
	if ($best > 0) {
		echo '  pct_hutter: ' . number_format(100.0 * $best / $hutter, 2) . "%\n";
	}
} else {
	echo "  archive: (none found under " . $tmp . "/fzpaq{bench,export}_* or fzpaq_*.phda9)\n";
}

$wirePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.fzpq';
if (is_file($wirePath)) {
	echo '  wire_cache: ' . $wirePath . ' (' . number_format((int) filesize($wirePath)) . " B)\n";
}

$jsonPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.json';
if (is_file($jsonPath)) {
	$j = json_decode((string) file_get_contents($jsonPath), true);
	if (is_array($j) && isset($j['bytes'])) {
		echo '  completed: ' . number_format((int) $j['bytes']) . ' B (' . ($j['tool'] ?? '?') . ")\n";
	}
}
