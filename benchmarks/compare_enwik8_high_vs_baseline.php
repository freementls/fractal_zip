#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
$highPath = $argv[1] ?? ($repo . '/benchmarks/.enwik8_exp_pp96_high.json');
$basePath = $argv[2] ?? ($repo . '/benchmarks/.enwik8_exp_pp96.json');

function load_fzc(string $path): ?array
{
	if (!is_file($path)) {
		return null;
	}
	$j = json_decode((string) file_get_contents($path), true);
	if (!is_array($j)) {
		return null;
	}
	foreach ($j['cases'] ?? array() as $c) {
		if (($c['label'] ?? '') === 'test_files109') {
			return $c;
		}
	}
	return null;
}

$high = load_fzc($highPath);
$base = load_fzc($basePath);
if ($high === null) {
	fwrite(STDERR, "Missing high run: {$highPath}\n");
	exit(1);
}
if ($base === null) {
	fwrite(STDERR, "Missing baseline: {$basePath}\n");
	exit(1);
}

$hf = (int) ($high['fzc_bytes'] ?? 0);
$bf = (int) ($base['fzc_bytes'] ?? 0);
$hs = (float) ($high['zip_seconds'] ?? 0);
$bs = (float) ($base['zip_seconds'] ?? 0);

echo "enwik8 high vs baseline (sorted fzc)\n";
echo '  high:     ' . number_format($hf) . ' B in ' . number_format($hs, 1) . "s  zpaq m" . ($high['outer_zpaq_method'] ?? '?') . "\n";
echo '  baseline: ' . number_format($bf) . ' B in ' . number_format($bs, 1) . "s  zpaq m" . ($base['outer_zpaq_method'] ?? '?') . "\n";
if ($hf > 0 && $bf > 0) {
	$d = $bf - $hf;
	echo '  delta:    ' . ($d >= 0 ? '−' : '+') . number_format(abs($d)) . ' B (' . sprintf('%+.2f', 100.0 * $d / $bf) . "%)\n";
}
