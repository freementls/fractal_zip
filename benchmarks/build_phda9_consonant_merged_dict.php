#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Merge z_* skeleton stream tokens into an English phda9 dict for consonant_hybrid wire arms.
 *
 * Usage:
 *   FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii \
 *     php benchmarks/build_phda9_consonant_merged_dict.php [--pages=384] [--base=PATH] [--out=PATH]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');
putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');

require_once $repo . '/fractal_zip_phda9_dict_mine.php';
require_once $repo . '/fractal_zip_enwik_syllable_codec.php';

$pages = 384;
$base = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
$out = $repo . '/benchmarks/.phda9_external_dict_consonant_merged_best.txt';

foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--base=')) {
		$base = substr($arg, 7);
	} elseif (str_starts_with($arg, '--out=')) {
		$out = substr($arg, 6);
	}
}

if (!is_file($base)) {
	$base = $repo . '/benchmarks/.phda9_external_dict.txt';
}
if (!is_file($base)) {
	fwrite(STDERR, "Missing base dict: {$base}\n");
	exit(1);
}

$src = $repo . '/test_files109/enwik8';
$blob = (string) file_get_contents($src);
$modelJson = $repo . '/benchmarks/.enwik8_consonant_model_' . $pages . 'p_phda9.json';
if (!is_file($modelJson)) {
	$modelJson = fractal_zip_enwik_consonant_hybrid_default_model_json_path($pages);
}
$modelOpts = array();
if (is_file($modelJson)) {
	$loaded = fractal_zip_enwik_consonant_hybrid_load_model_json($modelJson);
	if ($loaded !== null) {
		$modelOpts['consonant_model'] = $loaded;
	}
}
$built = fractal_zip_phda9_dict_build_consonant_merged_dict($blob, $pages, $base, $out, $modelOpts);
$s = $built['stats'];
echo 'pages=' . $pages
	. ' skel=' . ($s['skel_selected'] ?? '?') . '/' . ($s['skel_candidates'] ?? '?')
	. ' base=' . ($s['base_words'] ?? '?')
	. ' dict_words=' . ($s['dict_words'] ?? '?')
	. ' dict_bytes=' . ($s['dict_bytes'] ?? '?')
	. ' out=' . $out . "\n";
