#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Estimate @384p lossy wire: 4×96p phda9 chunks + inner-fold meta (no outer fzc).
 *
 * Usage: php benchmarks/diag_consonant_hybrid_lossy_384p_chunks.php [--pages=384] [--ppm=96]
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');

$repo = dirname(__DIR__);
$dict = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');

require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_syllable_codec.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';

$pages = 384;
$ppm = 96;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--ppm=')) {
		$ppm = max(1, (int) substr($arg, 6));
	}
}

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
if ($split === null) {
	fwrite(STDERR, "enwik split failed\n");
	exit(1);
}
$n = min($pages, count($split['pages']));
$pageRefs = array();
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$pageRefs[] = array(
		'title' => (string) $p['title'],
		'origIndex' => $i,
		'start' => (int) $p['start'],
		'len' => (int) $p['len'],
	);
}
$sortedChunk = enwik_sort_page_refs_by_title($pageRefs);

$pageTexts = array();
$mineText = '';
foreach ($sortedChunk as $p) {
	$t = fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
	$pageTexts[] = $t;
	$mineText .= $t;
}
$model = fractal_zip_enwik_consonant_hybrid_mine_model_pages($pageTexts, $mineText);
$modelOpts = array('consonant_model' => $model, 'frozen' => true);

$chunkTotal = (int) ceil($n / $ppm);
$jobs = fractal_zip_enwik_phda9_english_jobs($chunkTotal);
$pending = array();
$chunkIdx = 0;
for ($i = 0; $i < $n; $i += $ppm) {
	$chunk = array_slice($sortedChunk, $i, $ppm);
	$chunkSplit = array();
	foreach ($chunk as $p) {
		$t = fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
		$lo = fractal_zip_enwik_consonant_hybrid_lossy_preprocess($t, $modelOpts);
		$chunkSplit[] = array(
			'page' => fractal_zip_enwik_inject_text_into_shell_page(
				substr($blob, (int) $p['start'], (int) $p['len']),
				(string) $lo['payload']
			),
			'wire_sk_ascii' => '',
		);
	}
	$pre = fractal_zip_enwik_phda9_english_chunk_preprocessed_page_xml($chunkSplit);
	$pending[] = array(
		'chunkIdx' => $chunkIdx,
		'innerFull' => '',
		'plainBuf' => (string) $pre['page_xml'],
		'stackId' => 'none',
	);
	$chunkIdx++;
}

$foldPayload = fractal_zip_enwik_consonant_hybrid_inner_fold_payload($model, 'lossy_ascii');
$sealed = fractal_zip_enwik_inner_fold_seal_trailer('consonant_hybrid_lossy', $foldPayload);
$foldWire = strlen((string) ($sealed['wire'] ?? ''));

echo "diag lossy @{$n}p ppm={$ppm} chunks={$chunkTotal} jobs={$jobs}\n";
echo 'inner_fold=' . number_format($foldWire) . " B\n";

if ($jobs > 1 && $chunkTotal > 1) {
	$results = fractal_zip_enwik_phda9_english_parallel_apply($pending, $jobs);
} else {
	$results = array();
	foreach ($pending as $p) {
		$t0 = microtime(true);
		$r = fractal_zip_enwik_phda9_english_compress($p['plainBuf'], array(
			'wire_wrap' => true,
			'timeout_sec' => 0,
		));
		$results[(int) $p['chunkIdx']] = array(
			'tool' => (string) ($r['tool'] ?? 'phda9'),
			'bytes' => (int) ($r['bytes'] ?? 0),
			'seconds' => round(microtime(true) - $t0, 2),
			'roundtrip_ok' => !empty($r['roundtrip_ok']),
		);
	}
}

$innerSum = 0;
foreach ($results as $idx => $r) {
	$b = (int) ($r['bytes'] ?? 0);
	$innerSum += $b;
	echo sprintf(
		"chunk %d: %s B plain=%s sec=%s rt=%s\n",
		$idx,
		number_format($b),
		number_format(strlen($pending[$idx]['plainBuf'] ?? '')),
		(string) ($r['seconds'] ?? '?'),
		!empty($r['roundtrip_ok']) ? 'ok' : 'FAIL'
	);
}
echo 'phda9_inner_sum=' . number_format($innerSum) . " B\n";
echo 'est_members_plus_fold=' . number_format($innerSum + $foldWire) . " B (baseline @384p=519,462)\n";
