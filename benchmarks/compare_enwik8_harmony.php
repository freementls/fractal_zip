#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare harmony / pp96 experiment JSONs vs integrated baseline.
 */

$repo = dirname(__DIR__);
$baseline = 19594333;
$rows = array(
	'pp96' => $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_pp96.json',
	'preset_verify' => $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_preset_verify.json',
	'harmony1' => $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_harmony1.json',
	'harmony_light' => $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_harmony_light.json',
	'harmony_siteinfo' => $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_harmony_siteinfo.json',
	'harmony_text' => $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_harmony_text.json',
);

echo "enwik8 harmony compare (baseline pp96 fzc = " . number_format($baseline) . " B)\n\n";
foreach ($rows as $label => $path) {
	if (!is_file($path)) {
		echo "  {$label}: (missing {$path})\n";
		continue;
	}
	$j = json_decode((string) file_get_contents($path), true);
	$c = $j['cases'][0] ?? null;
	if (!is_array($c) || !isset($c['fzc_bytes'])) {
		echo "  {$label}: invalid JSON\n";
		continue;
	}
	$fzc = (int) $c['fzc_bytes'];
	$delta = $fzc - $baseline;
	$verify = isset($c['verify_ok']) ? ($c['verify_ok'] ? 'ok' : 'FAIL') : 'n/a';
	echo '  ' . $label . ': ' . number_format($fzc) . ' B (' . ($delta <= 0 ? '' : '+') . number_format($delta) . " vs pp96) verify={$verify}\n";
	if (isset($c['zip_seconds'])) {
		echo '    encode: ' . number_format((float) $c['zip_seconds'], 1) . "s\n";
	}
}

$pre = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_harmony_preencode_probe.json';
if (is_file($pre)) {
	$p = json_decode((string) file_get_contents($pre), true);
	if (is_array($p)) {
		echo "\npreencode gzip-1 delta: " . number_format((int) ($p['delta_bytes'] ?? 0)) . " B ({$p['pct']}%)\n";
	}
}
