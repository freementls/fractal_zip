<?php

declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';

$n = (int) ($argv[1] ?? 64);
$corp = fractal_zip_enwik_load_corpus_slice($n);
$text = (string) ($corp['page_xml'] ?? '');
if ($text === '') {
	fwrite(STDERR, "empty corpus @{$n}p\n");
	exit(1);
}

$decoded = fractal_zip_wiki_lom_entity_decode_text($text);
$t0 = microtime(true);

// Acronyms
$acronyms = fractal_zip_wiki_lom_mine_acronyms($decoded, 256, 4);
$acrWire = fractal_zip_wiki_lom_apply_acronyms($decoded, $acronyms);
$acrSave = strlen($decoded) - strlen($acrWire);
$acrRt = fractal_zip_wiki_lom_undo_acronyms($acrWire, $acronyms) === $decoded;

// Consonant bare skeleton
$model = fractal_zip_enwik_consonant_hybrid_mine_model_pages(
	is_array($corp['page_texts'] ?? null) ? $corp['page_texts'] : array($text),
	$text
);
$bare = is_array($model['skeleton_bare'] ?? null) ? $model['skeleton_bare'] : array();
$preZ = fractal_zip_enwik_consonant_hybrid_preprocess($text, array(
	'consonant_model' => array_merge($model, array('skeleton_bare' => array())),
));
putenv('FRACTAL_ZIP_CONSONANT_SK_BARE=1');
$preBare = fractal_zip_enwik_consonant_hybrid_preprocess($text, array('consonant_model' => $model));
$skRt = fractal_zip_enwik_consonant_hybrid_undo(
	(string) $preBare['payload'],
	$preBare['sidecar']
) === $text;
$zBytes = strlen((string) $preZ['payload']);
$bareBytes = strlen((string) $preBare['payload']);
$zCount = substr_count((string) $preZ['payload'], 'z_');
$bareCount = substr_count((string) $preBare['payload'], 'z_');

$sec = round(microtime(true) - $t0, 2);
echo "diag_collision_free_tokens @{$n}p sec={$sec}\n";
echo 'acronyms: count=' . count($acronyms) . ' save=' . $acrSave . 'B rt=' . ($acrRt ? 'ok' : 'FAIL') . "\n";
if ($acronyms !== array()) {
	$top = $acronyms[0];
	echo '  top: "' . ($top['phrase'] ?? '') . '" -> ' . ($top['acronym'] ?? '')
		. ' x' . ($top['count'] ?? 0) . "\n";
}
echo 'skeleton_bare: eligible=' . count($bare) . ' encoded_unique=' . count($model['skeleton_unique'] ?? array())
	. ' rt=' . ($skRt ? 'ok' : 'FAIL') . "\n";
echo "  payload: z_marked={$zBytes}B (z_ count={$zCount}) bare={$bareBytes}B (z_ count={$bareCount})"
	. ' delta=' . ($zBytes - $bareBytes) . "B\n";
