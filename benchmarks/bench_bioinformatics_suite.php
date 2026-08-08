#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Benchmark all bioinformatics concepts: primitives, layouts, lossy models.
 *
 * Usage: php benchmarks/bench_bioinformatics_suite.php [pages=32]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_bioinformatics.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_bio_align.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_bio_lossy.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

$pageCount = isset($argv[1]) ? max(4, (int) $argv[1]) : 32;
$enwik = $repo . DIRECTORY_SEPARATOR . 'enwik8';

$pages = array();
if (is_file($enwik)) {
	$blob = (string) file_get_contents($enwik, false, null, 0, 4_000_000);
	$split = fractal_zip_enwik_split_shell_and_text($blob, $pageCount);
	if ($split !== null) {
		$pages = $split['pages'];
	}
}
if ($pages === array()) {
	for ($i = 0; $i < $pageCount; $i++) {
		$pages[] = array(
			'title' => 'Synthetic' . $i,
			'origIndex' => $i,
			'text' => str_repeat('In computing, alignment matters. ', 20) . 'var' . $i,
		);
	}
}

$texts = array();
foreach ($pages as $pg) {
	$texts[] = (string) $pg['text'];
}
$items = array();
foreach ($pages as $i => $pg) {
	$items[] = array('id' => $i, 'text' => (string) $pg['text']);
}

printf("bioinformatics suite | %d pages | %s total text bytes\n\n",
	count($pages), number_format(array_sum(array_map(static fn (array $p): int => strlen((string) $p['text']), $pages))));

// Primitives timing + output sizes
$t0 = microtime(true);
$sk = fractal_zip_bio_minhash_sketch($texts[0]);
$minhashMs = (microtime(true) - $t0) * 1000;

$t0 = microtime(true);
$cl = fractal_zip_bio_cdhit_cluster($items, 0.35);
$cdhitMs = (microtime(true) - $t0) * 1000;

$t0 = microtime(true);
$tree = fractal_zip_bio_upgma_guide_tree($items);
$upgmaMs = (microtime(true) - $t0) * 1000;

$ref = $texts[0];
$mems = fractal_zip_bio_mems($ref, $texts[1] ?? $ref, 8);
$graph = fractal_zip_bio_pangenome_graph($items);
$packedGraph = fractal_zip_bio_pangenome_pack($graph);

printf("Primitives:\n");
printf("  minhash sketch: %d sig, %.1f ms\n", count($sk), $minhashMs);
printf("  CD-HIT: %d clusters, %.1f ms\n", count($cl), $cdhitMs);
printf("  UPGMA: order len %d, %.1f ms\n", count($tree['order']), $upgmaMs);
printf("  MEMs ref vs page1: %d matches\n", count($mems));
printf("  pangenome graph: %d nodes, packed %d B\n", count($graph['nodes']), strlen($packedGraph));

// Layouts
$layouts = fractal_zip_bio_layout_catalog();
$mono = implode('', $texts);
$gzip = static function (string $s): int {
	$z = gzdeflate($s, 9);
	return $z === false ? strlen($s) : strlen($z);
};
$baseGz = $gzip($mono);

printf("\nLayouts (gzip-9 payload vs mono baseline %d B):\n", $baseGz);
foreach ($layouts as $layoutId) {
	try {
		$layout = fractal_zip_enwik_text_layout_apply($pages, $layoutId, array('seed' => 1));
		$blob = (string) $layout['text_blob'];
		$metaBytes = strlen(json_encode($layout['meta'], JSON_UNESCAPED_UNICODE) ?: '');
		$gz = $gzip($blob);
		$chunks = fractal_zip_enwik_text_layout_undo_chunks($blob, $layout['meta'], $pages);
		$rt = true;
		foreach ($pages as $i => $pg) {
			if (($chunks[$i] ?? null) !== (string) $pg['text']) {
				$rt = false;
				break;
			}
		}
		printf("  %-22s gz=%7d meta=%5d net=%+7d RT=%s\n",
			$layoutId, $gz, $metaBytes, $baseGz - $gz - $metaBytes, $rt ? 'ok' : 'FAIL');
	} catch (Throwable $e) {
		printf("  %-22s ERROR: %s\n", $layoutId, $e->getMessage());
	}
}

// Lossy models on first two pages
printf("\nLossy models (page1 vs ref=page0):\n");
foreach (array('bigram', 'trigram', 'hmm') as $model) {
	$comp = fractal_zip_bio_lossy_compress($texts[0], $texts[1] ?? $texts[0], array('model' => $model, 'threshold' => 0.5));
	printf("  %-8s payload=%5d saved=%5d approx_len=%d\n",
		$model, strlen((string) $comp['payload']), (int) $comp['lossy_bytes_saved'], strlen((string) $comp['approx']));
}

fwrite(STDERR, "OK bench_bioinformatics_suite\n");
