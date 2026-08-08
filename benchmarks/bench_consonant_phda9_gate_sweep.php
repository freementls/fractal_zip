#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Compare consonant hybrid phda9 scores: plain vs collision-free vs phda9-gated.
 *
 * Usage:
 *   php benchmarks/bench_consonant_phda9_gate_sweep.php [--pages=96] [--quick]
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');
putenv('FRACTAL_ZIP_CONSONANT_SK_BARE=1');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_syllable_codec.php';

$pages = 96;
$quick = false;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif ($arg === '--quick') {
		$quick = true;
	}
}

$dict = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}

$src = $repo . '/test_files109/enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pages, count($split['pages']));
$pageTexts = array();
$mineText = '';
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$pt = fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
	$pageTexts[] = $pt;
	$mineText .= $pt;
}

$pool = $quick ? 64 : 256;
$maxAccept = $quick ? 24 : 128;

$score = static function (string $label, array $model) use ($mineText): void {
	$wire = fractal_zip_enwik_consonant_hybrid_apply_word_sk_wire($mineText, $model);
	$meta = fractal_zip_enwik_consonant_hybrid_meta_wire_bytes(
		fractal_zip_enwik_consonant_hybrid_shared_meta_from_model($model),
		12041,
		12041
	);
	$ph = fractal_zip_cfabb_phda9_plain_bytes($wire);
	$total = $ph + $meta;
	echo str_pad($label, 22)
		. str_pad('plain=' . number_format(strlen($mineText)), 16)
		. str_pad('wire=' . number_format(strlen($wire)), 14)
		. str_pad('phda9=' . number_format($ph), 12)
		. str_pad('meta=' . number_format($meta), 10)
		. str_pad('total=' . number_format($total), 14)
		. ' sk=' . count($model['skeleton_unique'] ?? array()) . "\n";
};

require_once $repo . '/fractal_zip_collision_free_abbrevs.php';

echo "consonant phda9 gate sweep pages={$n} pool={$pool} max={$maxAccept}\n";
echo str_pad('label', 22) . str_pad('preserve', 16) . str_pad('wire_len', 14)
	. str_pad('phda9', 12) . str_pad('meta', 10) . str_pad('total', 14) . "entries\n";

$empty = array('skeleton_unique' => array(), 'skeleton_ambig' => array(), 'word_to_sk' => array());
$score('plain (no sk)', $empty);

putenv('FRACTAL_ZIP_CONSONANT_SK_COLLISION_FREE=1');
putenv('FRACTAL_ZIP_CONSONANT_MODEL_JSON');
$cfModel = fractal_zip_enwik_consonant_hybrid_mine_model_pages($pageTexts, $mineText);
$score('collision-free', $cfModel);

$gated = fractal_zip_enwik_consonant_hybrid_filter_phda9_greedy($pageTexts, $cfModel, array(
	'force_phda9_gate' => true,
	'phda9_greedy_pool' => $pool,
	'phda9_greedy_max' => $maxAccept,
));
$cfSk = count($cfModel['skeleton_unique'] ?? array());
$gatedSk = count($gated['skeleton_unique'] ?? array());
fwrite(STDERR, "gate: cf_sk={$cfSk} gated_sk={$gatedSk}\n");
$score('cf + phda9 gate', $gated);

putenv('FRACTAL_ZIP_CONSONANT_SK_COLLISION_FREE=0');
$legacyModel = fractal_zip_enwik_consonant_hybrid_mine_model_pages($pageTexts, $mineText);
$score('legacy ambig', $legacyModel);
