#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Patch test_files109 fzc row in .enwik8_world_record.json from an encode-only experiment JSON.
 *
 * Usage:
 *   php benchmarks/patch_enwik8_world_record_fzc.php benchmarks/.enwik8_exp_pp48.json
 */

$repo = dirname(__DIR__);
$expPath = $argv[1] ?? ($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_pp48.json');
$wrPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_world_record.json';

if (!is_file($expPath) || !is_file($wrPath)) {
	fwrite(STDERR, "Missing experiment or world-record JSON\n");
	exit(1);
}

$exp = json_decode((string) file_get_contents($expPath), true);
$wr = json_decode((string) file_get_contents($wrPath), true);
if (!is_array($exp) || !is_array($wr)) {
	exit(1);
}

$src = null;
foreach ($exp['cases'] ?? array() as $c) {
	if (($c['label'] ?? '') === 'test_files109') {
		$src = $c;
		break;
	}
}
if ($src === null || !isset($src['fzc_bytes'])) {
	fwrite(STDERR, "No test_files109 case in {$expPath}\n");
	exit(1);
}

$fzc = (int) $src['fzc_bytes'];
if ($fzc < 1000000) {
	fwrite(STDERR, "Refusing patch: fzc_bytes={$fzc} looks invalid\n");
	exit(1);
}

$idx = null;
foreach ($wr['cases'] ?? array() as $i => $c) {
	if (($c['label'] ?? '') === 'test_files109') {
		$idx = $i;
		break;
	}
}
if ($idx === null) {
	exit(1);
}

$case = $wr['cases'][$idx];
$raw = (int) ($case['raw_bytes'] ?? 100000000);
$bestExt = (int) ($case['best_ext_folder_bytes'] ?? 0);

$case['fzc_bytes'] = $fzc;
$case['zip_seconds'] = (float) ($src['zip_seconds'] ?? $case['zip_seconds'] ?? 0);
if (isset($src['outer_codec'])) {
	$case['outer_codec'] = $src['outer_codec'];
}
if (isset($src['outer_zpaq_method'])) {
	$case['outer_zpaq_method'] = (string) $src['outer_zpaq_method'];
}
if (isset($src['folder_unified_stream'])) {
	$case['folder_unified_stream'] = (bool) $src['folder_unified_stream'];
}
if (isset($src['member_count']) && $src['member_count'] !== null) {
	if (!isset($case['folder_bundle_census']) || !is_array($case['folder_bundle_census'])) {
		$case['folder_bundle_census'] = array();
	}
	$case['folder_bundle_census']['files'] = (int) $src['member_count'];
}

$case['pct_of_raw_fzc'] = round(100.0 * $fzc / max(1, $raw), 2);
$case['winner_compression'] = array($bestExt > 0 && $bestExt < $fzc ? 'ext' : 'fzc');

$wr['cases'][$idx] = $case;
if (isset($wr['totals']) && is_array($wr['totals'])) {
	$wr['totals']['fzc_bytes'] = $fzc;
	$wr['totals']['zip_seconds'] = $case['zip_seconds'];
	if ($bestExt > 0) {
		$wr['totals']['total_bytes_fzc_minus_best'] = max(0, $fzc - $bestExt);
	}
}

$wr['generated'] = date('c');
file_put_contents($wrPath, json_encode($wr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "Patched {$wrPath} fzc_bytes → " . number_format($fzc) . " B\n";
echo "  source: {$expPath}\n";
echo "  best_ext unchanged: " . number_format($bestExt) . " B\n";
