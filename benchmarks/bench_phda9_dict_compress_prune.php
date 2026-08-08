#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Compress-guided dict edits on words_4096p @96p:
 *   1) try removing low-est_save words (leave-one-out sample)
 *   2) try adding top phrase/subword candidates (refine)
 *
 * Usage:
 *   php -d memory_limit=768M benchmarks/bench_phda9_dict_compress_prune.php --pages=96
 *   php -d memory_limit=768M benchmarks/bench_phda9_dict_compress_prune.php --pages=96 --tool=phda9_no_lstm
 *   php -d memory_limit=768M benchmarks/bench_phda9_dict_compress_prune.php --pages=384 --removals=40 --additions=16
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');

require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once $repo . '/fractal_zip_phda9_dict_mine.php';
require_once $repo . '/fractal_zip_phda9_dict.php';

$pages = 96;
$tool = 'phda9_no_lstm';
$removals = 24;
$additions = 16;
$seedPath = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--tool=')) {
		$tool = substr($arg, 7);
	} elseif (str_starts_with($arg, '--removals=')) {
		$removals = max(0, (int) substr($arg, 11));
	} elseif (str_starts_with($arg, '--additions=')) {
		$additions = max(0, (int) substr($arg, 12));
	} elseif (str_starts_with($arg, '--seed=')) {
		$seedPath = substr($arg, 7);
	}
}
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=' . $tool);

if (!is_file($seedPath)) {
	fwrite(STDERR, "missing seed {$seedPath}\n");
	exit(1);
}

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$sorted = enwik_sort_page_refs_by_title($split['pages']);
$n = min($pages, count($sorted));
$chunk = array();
for ($i = 0; $i < $n; $i++) {
	$chunk[] = array(
		'origIndex' => $i,
		'start' => (int) $sorted[$i]['start'],
		'len' => (int) $sorted[$i]['len'],
	);
}
$pageXml = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob)['sorted_page_xml'];

$compress = static function (string $plain, array $words) use ($tool): ?array {
	$path = sys_get_temp_dir() . '/fz_prune_' . getmypid() . '_' . bin2hex(random_bytes(3)) . '.txt';
	fractal_zip_phda9_dict_write_file($words, $path);
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $path);
	$r = fractal_zip_enwik_phda9_english_compress($plain, array(
		'tool' => $tool,
		'use_dict' => true,
		'timeout_sec' => 0,
		'wire_wrap' => true,
	));
	@unlink($path);
	if (empty($r['roundtrip_ok']) || !isset($r['bytes'])) {
		return null;
	}
	return array('bytes' => (int) $r['bytes'], 'sec' => (float) ($r['seconds'] ?? 0.0));
};

$seedWords = fractal_zip_phda9_dict_read_words($seedPath);
$baseline = $compress($pageXml, $seedWords);
if ($baseline === null) {
	fwrite(STDERR, "baseline compress failed\n");
	exit(1);
}

echo "compress_prune @{$n}p tool={$tool} seed=" . count($seedWords) . " words\n";
printf("  baseline FZPA=%s B sec=%.1f\n\n", number_format($baseline['bytes']), $baseline['sec']);

$wordScored = fractal_zip_phda9_dict_score_candidates(
	$pageXml,
	array_map(static fn (string $w): array => array('token' => $w, 'kind' => 'word'), $seedWords)
);
usort($wordScored, static fn (array $a, array $b): int => ((int) ($a['est_save'] ?? 0)) <=> ((int) ($b['est_save'] ?? 0)));

$bestWords = $seedWords;
$bestBytes = $baseline['bytes'];
$removalRows = array();
$seedSet = array_fill_keys($seedWords, true);

fwrite(STDERR, "[removals] sampling up to {$removals} low-est_save words …\n");
$tried = 0;
foreach ($wordScored as $row) {
	if ($tried >= $removals) {
		break;
	}
	$tok = (string) $row['token'];
	if (!isset($seedSet[$tok])) {
		continue;
	}
	$trial = array_values(array_filter($seedWords, static fn (string $w): bool => $w !== $tok));
	$tried++;
	$cr = $compress($pageXml, $trial);
	if ($cr === null) {
		continue;
	}
	$delta = $cr['bytes'] - $bestBytes;
	$removalRows[] = array(
		'action' => 'remove',
		'token' => $tok,
		'est_save' => (int) ($row['est_save'] ?? 0),
		'fzpa' => $cr['bytes'],
		'delta' => $delta,
	);
	if ($cr['bytes'] < $bestBytes) {
		$bestBytes = $cr['bytes'];
		$bestWords = $trial;
		unset($seedSet[$tok]);
		$seedWords = $trial;
		fwrite(STDERR, "  WIN remove '{$tok}' → " . number_format($bestBytes) . " ({$delta})\n");
	}
}

$mined = fractal_zip_phda9_dict_mine_from_enwik($blob, array(
	'pages' => $n,
	'mode' => 'phrases',
));
$addCandidates = array();
$seen = array_fill_keys($bestWords, true);
foreach ($mined['words'] as $w) {
	if (!isset($seen[$w])) {
		$addCandidates[] = array('token' => $w, 'kind' => 'phrase');
	}
}
$sub = fractal_zip_phda9_dict_mine_subword_pieces(
	fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob)['sorted_article_text'],
	3,
	2048
);
foreach ($sub as $c) {
	$t = (string) $c['token'];
	if (!isset($seen[$t])) {
		$addCandidates[] = $c;
	}
}
$addScored = fractal_zip_phda9_dict_score_candidates($pageXml, $addCandidates);
$ref = fractal_zip_phda9_dict_refine_with_compress(
	$pageXml,
	$bestWords,
	$addScored,
	$additions,
	$bestBytes
);

$outPath = $repo . '/benchmarks/.phda9_external_dict_compress_prune_' . $n . 'p.txt';
fractal_zip_phda9_dict_write_file($ref['tokens'], $outPath);
$final = $compress($pageXml, $ref['tokens']);
$finalBytes = $final['bytes'] ?? $ref['best_bytes'] ?? $bestBytes;
$deltaFinal = $finalBytes - $baseline['bytes'];

printf("\n  after_removals best=%s B\n", number_format($bestBytes));
printf("  refine additions trials=%d best=%s B\n",
	(int) ($ref['trials'] ?? 0),
	number_format((int) ($ref['best_bytes'] ?? $bestBytes))
);
printf("  final FZPA=%s Δ=%s RT=%s\n",
	number_format($finalBytes),
	sprintf('%+d', $deltaFinal),
	$final !== null ? 'ok' : '?'
);

$report = array(
	'generated' => date('c'),
	'pages' => $n,
	'tool' => $tool,
	'seed_path' => $seedPath,
	'baseline_fzpa' => $baseline['bytes'],
	'removal_trials' => $removalRows,
	'refine' => $ref,
	'out_path' => $outPath,
	'final_fzpa' => $finalBytes,
	'delta' => $deltaFinal,
);
$jsonPath = $repo . '/benchmarks/.enwik8_phda9_dict_compress_prune_' . $n . 'p.json';
file_put_contents($jsonPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "\njson → {$jsonPath}\n";

if ($deltaFinal < 0 && is_file($outPath)) {
	echo "  candidate dict → {$outPath}\n";
}
