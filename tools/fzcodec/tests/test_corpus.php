#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Validate fzcodec against published Squash B-best sizes.
 *
 * The library is never told a corpus id. This harness finds each single-file
 * tree under test_files105–132, compresses the bytes, and compares the
 * resulting FZC1 size to the published B-best (from the dual-review freeze).
 *
 * Usage:
 *   php tests/test_corpus.php --lifestyle
 *   php tests/test_corpus.php --ultra --max-bytes=200000
 *   php tests/test_corpus.php --lifestyle --only=alice29.txt
 *   php tests/test_corpus.php --lifestyle --min-bytes=2000000 --roundtrip-only
 *   php tests/test_corpus.php --lifestyle --min-bytes=2000000 --report-only
 *   php tests/test_corpus.php --ultra --max-bytes=40000
 */
$root = dirname(__DIR__, 3);
$bin = dirname(__DIR__) . '/build/fzcodec';
$review = $root . '/benchmarks/.squash_dual_review.json';
$preset = in_array('--ultra', $argv, true) ? 'ultra' : 'lifestyle';
$flag = $preset === 'ultra' ? '--ultra' : '--lifestyle';
$roundtripOnly = in_array('--roundtrip-only', $argv, true);
$reportOnly = in_array('--report-only', $argv, true);
$maxBytes = 0;
$minBytes = 0;
$only = null;
foreach ($argv as $a) {
	if (str_starts_with((string) $a, '--max-bytes=')) {
		$maxBytes = (int) substr((string) $a, 12);
	}
	if (str_starts_with((string) $a, '--min-bytes=')) {
		$minBytes = (int) substr((string) $a, 12);
	}
	if (str_starts_with((string) $a, '--only=')) {
		$only = substr((string) $a, 7);
	}
}
if (!is_executable($bin)) {
	fwrite(STDERR, "test_corpus: missing $bin (run make)\n");
	exit(2);
}
if (!is_file($review)) {
	fwrite(STDERR, "test_corpus: missing $review\n");
	exit(2);
}
$j = json_decode((string) file_get_contents($review), true);
if (!is_array($j) || !isset($j['cases']) || !is_array($j['cases'])) {
	fwrite(STDERR, "test_corpus: bad review json\n");
	exit(2);
}
$byName = [];
foreach ($j['cases'] as $c) {
	if (!is_array($c)) {
		continue;
	}
	$name = trim((string) ($c['dataset'] ?? ''));
	if ($name === '') {
		continue;
	}
	$byName[$name] = $c;
}

$n = 0;
$byteWins = 0;
$effWins = 0;
$failRt = 0;
$rows = [];
for ($i = 105; $i <= 132; $i++) {
	$dir = $root . '/test_files' . $i;
	if (!is_dir($dir)) {
		continue;
	}
	$files = array_values(array_filter(scandir($dir) ?: [], static function ($f) use ($dir) {
		return $f !== '.' && $f !== '..' && is_file($dir . '/' . $f);
	}));
	if (count($files) !== 1) {
		continue;
	}
	$name = $files[0];
	if ($only !== null && $only !== $name) {
		continue;
	}
	$src = $dir . '/' . $name;
	$raw = filesize($src);
	if ($raw === false) {
		continue;
	}
	if ($maxBytes > 0 && $raw > $maxBytes) {
		fwrite(STDERR, "skip {$name} ({$raw} > max-bytes)\n");
		continue;
	}
	if ($minBytes > 0 && $raw < $minBytes) {
		fwrite(STDERR, "skip {$name} ({$raw} < min-bytes)\n");
		continue;
	}
	$ref = $byName[$name] ?? null;
	$tmp = tempnam(sys_get_temp_dir(), 'fzc');
	$dec = $tmp . '.out';
	$t0 = microtime(true);
	$cmd = escapeshellarg($bin) . ' compress ' . $flag . ' ' . escapeshellarg($src) . ' ' . escapeshellarg($tmp);
	passthru($cmd, $rc);
	$sec = microtime(true) - $t0;
	if ($rc !== 0 || !is_file($tmp)) {
		fwrite(STDERR, "compress failed: {$name}\n");
		@unlink($tmp);
		$failRt++;
		continue;
	}
	$enc = filesize($tmp);
	passthru(escapeshellarg($bin) . ' decompress ' . escapeshellarg($tmp) . ' ' . escapeshellarg($dec), $rc2);
	$ok = ($rc2 === 0 && is_file($dec) && (int) filesize($dec) === (int) $raw
		&& hash_file('sha256', $dec) === hash_file('sha256', $src));
	@unlink($dec);
	@unlink($tmp);
	if (!$ok) {
		fwrite(STDERR, "roundtrip failed: {$name}\n");
		$failRt++;
		continue;
	}
	$n++;
	$bb = $ref ? (int) $ref['squash_best_compressed_bytes'] : 0;
	$bs = $ref ? (float) $ref['squash_bbest_compress_cpu'] : 0.0;
	$smaller = $bb > 0 && $enc < $bb;
	$faster = $bs > 0 && $sec < $bs;
	if ($smaller) {
		$byteWins++;
	}
	if ($smaller || $faster) {
		$effWins++;
	}
	$delta = $bb > 0 ? ($enc - $bb) : 0;
	$rows[] = [
		'name' => $name,
		'raw' => $raw,
		'enc' => $enc,
		'bbest' => $bb,
		'delta' => $delta,
		'sec' => $sec,
		'bbest_cpu' => $bs,
		'smaller' => $smaller,
		'faster' => $faster,
	];
	$mark = $smaller ? 'WIN' : (($faster) ? 'EFF' : 'LOSE');
	printf("%-18s raw=%9d  fzc=%9d  bbest=%9d  %+8d  %6.2fs  %s\n",
		$name, $raw, $enc, $bb, $delta, $sec, $mark);
}

printf("\n%s  files=%d  byte_wins=%d  efficiency_wins=%d  roundtrip_fail=%d\n",
	$preset, $n, $byteWins, $effWins, $failRt);
if ($failRt > 0 || $n === 0) {
	exit(1);
}
if ($roundtripOnly || $reportOnly) {
	exit(0);
}
/* Lifestyle bar: efficiency. Ultra bar: bytes. */
if ($preset === 'ultra' && $byteWins < $n) {
	fwrite(STDERR, "ultra did not beat B-best on every file in this slice\n");
	exit(1);
}
if ($preset === 'lifestyle' && $effWins < $n) {
	fwrite(STDERR, "lifestyle was Pareto-dominated on some files in this slice\n");
	exit(1);
}
exit(0);
