#!/usr/bin/env php
<?php
declare(strict_types=1);

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');
putenv('FRACTAL_ZIP_TEXT_INNER=1');
putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=' . (string) ($argv[2] ?? getenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS') ?: 'consonant_hybrid'));
if (($argv[2] ?? '') === 'consonant_hybrid_split') {
	putenv('FRACTAL_ZIP_CONSONANT_SK_FREQ_VOCAB=1');
}
if (($argv[3] ?? '') === 'dual' || getenv('FRACTAL_ZIP_CONSONANT_SK_DUAL') === '1') {
	putenv('FRACTAL_ZIP_CONSONANT_SK_DUAL=1');
}
$skDict = $repo . '/benchmarks/.phda9_external_dict_consonant_split_sk_best.txt';
if (($argv[4] ?? '') === 'skdict' || getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT_SK') !== false) {
	if (is_file($skDict)) {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT_SK=' . $skDict);
	}
}
putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');

$repo = dirname(__DIR__);
$n = (int) ($argv[1] ?? 64);
$dict = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');

require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';

$src = $repo . '/test_files109/enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
$n = (int) ($argv[1] ?? 64);
$header = (string) $split['header'];
$footer = (string) $split['footer'];
$slice = $header;
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$slice .= substr($blob, (int) $p['start'], (int) $p['len']);
}
$slice .= $footer;

$tmp = sys_get_temp_dir() . '/fz_syl_rt_' . getmypid();
@mkdir($tmp, 0700, true);
$work = $tmp . '/in';
@mkdir($work, 0700, true);
file_put_contents($work . '/enwik8', $slice);
$fzc = $work . '.fz';

$fz = new fractal_zip();
$fz->zip_folder($work, false);
if (!is_file($fzc)) {
	fwrite(STDERR, "FAIL no fzc\n");
	exit(1);
}
$peel = fractal_zip_enwik_peel_trailer_from_fzc($fzc);
if (is_array($peel)) {
	$rels = (array) ($peel['memberRelPaths'] ?? array());
	$metaRels = array_values(array_filter($rels, static fn($r) => str_starts_with((string) $r, 'meta/')));
	echo 'fzep_meta_members=' . implode(',', $metaRels) . "\n";
}

$rtWork = $tmp . '/rt';
@mkdir($rtWork, 0700, true);
copy($fzc, $rtWork . '/t.fz');
$fx = new fractal_zip();
$fx->open_container($rtWork . '/t.fz', false);
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rtWork, FilesystemIterator::SKIP_DOTS));
$listed = 0;
foreach ($it as $f) {
	if ($listed++ < 15) {
		echo substr($f->getPathname(), strlen($rtWork) + 1) . "\n";
	}
}
$meta = fractal_zip_enwik_load_text_inner_preprocess_meta($rtWork, $rtWork . '/t.fz');
echo 'meta_loaded=' . (is_array($meta) ? 'yes' : 'no');
if (is_array($meta)) {
	echo ' unique=' . count($meta['skeleton_unique'] ?? array())
		. ' ctx=' . count($meta['context_unique'] ?? array())
		. ' ambig=' . count($meta['skeleton_ambig'] ?? array())
		. ' sidecars=' . count($meta['sidecars'] ?? array())
		. ' payload=' . ($meta['payload_codec'] ?? '?') . "\n";
} else {
	echo "\n";
}
$got = (string) file_get_contents($rtWork . '/enwik8');
echo 'orig=' . strlen($slice) . ' got=' . strlen($got) . ' match=' . ($got === $slice ? 'yes' : 'no') . "\n";
if ($got !== $slice) {
	$omin = min(strlen($slice), strlen($got));
	for ($i = 0; $i < $omin; $i++) {
		if ($slice[$i] !== $got[$i]) {
			echo "first diff @{$i}\n";
			echo substr($slice, max(0, $i - 40), 120) . "\n";
			echo substr($got, max(0, $i - 40), 120) . "\n";
			break;
		}
	}
	if (strlen($slice) !== strlen($got)) {
		echo 'len delta=' . (strlen($got) - strlen($slice)) . "\n";
	}
}
