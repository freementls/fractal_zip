#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_collision_free_abbrevs.php';
require_once $repo . '/fractal_zip_wiki_lom.php';
require_once $repo . '/fractal_zip_enwik_text_inner_dict.php';

foreach (array(
	'384p' => '.enwik8_cfabb_table_384p.json',
	'filtered' => '.enwik8_cfabb_table_384p_filtered.json',
) as $label => $file) {
	$path = $repo . '/benchmarks/' . $file;
	if (!is_file($path)) {
		continue;
	}
	$rows = fractal_zip_cfabb_load_json($path);
	$acronyms = array();
	foreach ($rows as $r) {
		$acronyms[] = array(
			'phrase' => (string) $r['phrase'],
			'token' => (string) $r['token'],
			'acronym' => (string) $r['token'],
		);
	}
	$packRows = array();
	foreach ($rows as $r) {
		$packRows[] = array('phrase' => (string) $r['phrase'], 'token' => (string) $r['token']);
	}
	$fzcf = fractal_zip_cfabb_pack_table($packRows);
	$fold = fractal_zip_wiki_lom_inner_fold_blob(array('acronyms_list' => $acronyms));
	$sealed = fractal_zip_enwik_inner_fold_seal_trailer('wiki_lom', $fold);
	echo $label
		. ' entries=' . count($rows)
		. ' fzcf=' . strlen($fzcf)
		. ' fold_gz=' . strlen($fold)
		. ' sealed=' . (int) ($sealed['wire_bytes'] ?? 0)
		. ' codec=' . (string) ($sealed['codec'] ?? '?')
		. "\n";
}
