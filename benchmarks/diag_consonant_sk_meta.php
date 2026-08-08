#!/usr/bin/env php
<?php
declare(strict_types=1);

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');
putenv('FRACTAL_ZIP_TEXT_INNER=1');
putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=consonant_hybrid');
putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';

$n = (int) ($argv[1] ?? 64);
$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$sortedChunk = array();
for ($i = 0; $i < $n; $i++) {
	$sortedChunk[] = array(
		'title' => '',
		'origIndex' => $i,
		'start' => (int) $split['pages'][$i]['start'],
		'len' => (int) $split['pages'][$i]['len'],
		'sortedIndex' => $i,
	);
}
$tmp = sys_get_temp_dir() . '/fz_sk_meta_' . getmypid();
@mkdir($tmp, 0700, true);
$vf = enwik_build_text_inner_virtual_folder_from_refs(
	$tmp,
	$sortedChunk,
	$blob,
	(string) $split['header'],
	(string) $split['footer'],
	'enwik8',
	$tmp
);
$metaDir = $tmp . '/meta';
echo "virtual meta @{$n}p:\n";
foreach (glob($metaDir . '/*') ?: array() as $f) {
	echo '  ' . basename($f) . ' ' . filesize($f) . "\n";
}
$json = (string) (@file_get_contents($metaDir . '/text_inner_preprocess.json') ?: '');
$j = json_decode($json, true);
echo 'json embedded=' . (!empty($j['embedded']) ? 'yes' : 'no') . "\n";
echo 'json sidecars=' . count($j['sidecars'] ?? array()) . "\n";
echo 'json unique_inline=' . count($j['skeleton_unique'] ?? array()) . "\n";
echo 'inner_fold_trailer=' . strlen((string) ($vf['innerFoldBlob'] ?? '')) . " B\n";
if ((string) ($vf['innerFoldBlob'] ?? '') !== '') {
	require_once $repo . '/fractal_zip_enwik_text_inner_dict.php';
	$tr = fractal_zip_enwik_inner_fold_preprocess_meta_from_trailer((string) $vf['innerFoldBlob']);
	if (is_array($tr)) {
		echo 'trailer unique=' . count($tr['skeleton_unique'] ?? array())
			. ' ctx=' . count($tr['context_unique'] ?? array())
			. ' ambig=' . count($tr['skeleton_ambig'] ?? array()) . "\n";
	}
}
fractal_zip_enwik_recursive_remove($tmp);
