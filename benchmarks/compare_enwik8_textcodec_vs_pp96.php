#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
$tcPath = $argv[1] ?? ($repo . '/benchmarks/.enwik8_exp_pp96_textcodec.json');
$ppPath = $argv[2] ?? ($repo . '/benchmarks/.enwik8_exp_pp96.json');

function load_case(string $path): ?array
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

$tc = load_case($tcPath);
$pp = load_case($ppPath);
if ($tc === null) {
	fwrite(STDERR, "Missing textcodec run: {$tcPath}\n");
	exit(1);
}
if ($pp === null) {
	fwrite(STDERR, "Missing pp96 baseline: {$ppPath}\n");
	exit(1);
}

$tf = (int) ($tc['fzc_bytes'] ?? 0);
$pf = (int) ($pp['fzc_bytes'] ?? 0);
echo "enwik8 textcodec vs pp96\n";
echo '  pp96:      ' . number_format($pf) . ' B  ' . number_format((float) ($pp['zip_seconds'] ?? 0), 1) . "s\n";
echo '  textcodec: ' . number_format($tf) . ' B  ' . number_format((float) ($tc['zip_seconds'] ?? 0), 1) . 's'
	. '  codec=' . ($tc['text_codec'] ?? '?') . '+' . ($tc['text_codec_transform'] ?? '?') . "\n";
if ($tf > 0 && $pf > 0) {
	$d = $pf - $tf;
	echo '  delta:     ' . ($d >= 0 ? '−' : '+') . number_format(abs($d)) . ' B (' . sprintf('%+.3f', 100.0 * $d / $pf) . "%)\n";
}
