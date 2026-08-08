#!/usr/bin/env php
<?php
declare(strict_types=1);

/** Backfill amortization fields on an existing refine JSON. */
$repo = dirname(__DIR__);
$pages = 96;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}
$path = $repo . '/benchmarks/.enwik8_phda9_dict_lstm_seed_refine_' . $pages . 'p.json';
$j = json_decode((string) file_get_contents($path), true);
if (!is_array($j)) {
	exit(1);
}
$fullPages = 12041;
$seedPath = (string) ($j['seed_path'] ?? ($repo . '/benchmarks/.phda9_external_dict_words_4096p.txt'));
$refPath = (string) ($j['refined_dict'] ?? ($repo . '/benchmarks/.phda9_external_dict_lstm_seed_refine_' . $pages . 'p.txt'));
$seedDict = is_file($seedPath) ? (int) filesize($seedPath) : 0;
$refinedDict = is_file($refPath) ? (int) filesize($refPath) : 0;
$dictDelta = $refinedDict - $seedDict;
$amortScale = $pages / $fullPages;
$delta = isset($j['delta']) ? (int) $j['delta'] : null;
$j['full_pages'] = $fullPages;
$j['seed_dict_bytes'] = $seedDict;
$j['refined_dict_bytes'] = $refinedDict;
$j['dict_delta_bytes'] = $dictDelta;
$j['amortized_dict_bytes_seed'] = (int) round($seedDict * $amortScale);
$j['amortized_dict_bytes_refined'] = (int) round($refinedDict * $amortScale);
$j['amortized_model'] = 'fzpa_delta + dict_delta * (slice_pages/full_pages - 1); dict ships once at full scale';
$j['net_amortized_fzpa_delta'] = $delta !== null
	? $delta + (int) round($dictDelta * ($amortScale - 1.0))
	: null;
file_put_contents($path, json_encode($j, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "patched {$path}\n";
