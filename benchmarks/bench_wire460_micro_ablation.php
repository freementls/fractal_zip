#!/usr/bin/env php
<?php

declare(strict_types=1);

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_phda9_dict.php';
require_once $repo . '/fractal_zip_phda9_dict_wire.php';

$pages = 128;
$seedPath = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
$verifyWins = true;
$tokens = array(
	'Andorra',
	'Albania',
	'Afghanistan',
	'Academy',
	'thumb',
	'REDIRECT',
	'about',
	'would',
	'had',
	'Image',
	'has',
	'who',
);

foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(8, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--seed=')) {
		$seedPath = substr($arg, 7);
	} elseif (str_starts_with($arg, '--tokens=')) {
		$tokens = array_values(array_filter(array_map(
			static fn (string $x): string => trim($x),
			explode(',', (string) substr($arg, 9))
		), static fn (string $x): bool => $x !== ''));
	} elseif ($arg === '--no-verify-wins') {
		$verifyWins = false;
	}
}

if (!is_file($seedPath)) {
	fwrite(STDERR, "missing seed dict: {$seedPath}\n");
	exit(1);
}

$seedWords = fractal_zip_phda9_dict_read_words($seedPath);
$seedSet = array_fill_keys($seedWords, true);
$sessionDir = sys_get_temp_dir() . '/fz_wire460_micro_' . getmypid() . '_' . bin2hex(random_bytes(4));
$slice = fractal_zip_phda9_dict_wire_prepare_slice($repo, $pages, $sessionDir);

$baseline = fractal_zip_phda9_dict_wire_encode(
	$repo,
	$slice['slice_path'],
	$sessionDir . '/baseline',
	$seedWords,
	false,
	$seedPath
);
if ($baseline === null) {
	fwrite(STDERR, "baseline encode failed\n");
	exit(2);
}
$baselineWire = (int) $baseline['wire_fzc'];

echo "wire460 micro ablation @{$pages}p\n";
echo "  baseline wire: " . number_format($baselineWire) . " B\n";
echo "  tokens: " . count($tokens) . "\n\n";

$wins = 0;
foreach ($tokens as $tok) {
	if (!isset($seedSet[$tok])) {
		printf("  remove %-16s SKIP (not in dict)\n", $tok);
		continue;
	}
	$trialWords = array_values(array_filter($seedWords, static fn (string $w): bool => $w !== $tok));
	$enc = fractal_zip_phda9_dict_wire_encode(
		$repo,
		$slice['slice_path'],
		$sessionDir . '/r_' . preg_replace('/[^A-Za-z0-9_]+/', '_', $tok),
		$trialWords,
		false,
		$seedPath
	);
	if ($enc === null) {
		printf("  remove %-16s FAIL (encode)\n", $tok);
		continue;
	}
	$wire = (int) $enc['wire_fzc'];
	$delta = $wire - $baselineWire;
	$mark = ($delta < 0) ? 'WIN' : 'loss/tie';
	printf("  remove %-16s wire=%8s Δ=%+6d %s\n", $tok, number_format($wire), $delta, $mark);
	if ($delta < 0) {
		$wins++;
		if ($verifyWins) {
			$rt = fractal_zip_phda9_dict_wire_encode(
				$repo,
				$slice['slice_path'],
				$sessionDir . '/v_' . preg_replace('/[^A-Za-z0-9_]+/', '_', $tok),
				$trialWords,
				true,
				$seedPath
			);
			$ok = ($rt !== null && !empty($rt['roundtrip_ok']));
			echo '    verify: ' . ($ok ? 'RT ok' : 'RT FAIL') . "\n";
		}
	}
}

echo "\nsummary: wins={$wins} (negative Δ only)\n";
exit(0);

