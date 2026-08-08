#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Prune a large word dict to byte budgets; score with lstm phda9 @384p sorted page XML.
 *
 * Usage:
 *   php benchmarks/bench_phda9_dict_prune_hunt.php [--pages=384] [--source=4096p]
 *   php benchmarks/bench_phda9_dict_prune_hunt.php --budgets=402000,500000,750000,1000000
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');

require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once $repo . '/fractal_zip_phda9_dict_mine.php';
require_once $repo . '/fractal_zip_phda9_dict.php';

$pages = 384;
$source = '4096p';
$budgets = array(401769, 500000, 750000, 1000000, 1500000);
$skipRefs = false;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--source=')) {
		$source = substr($arg, 9);
	}
	if (str_starts_with($arg, '--budgets=')) {
		$budgets = array();
		foreach (explode(',', substr($arg, 10)) as $b) {
			$b = trim($b);
			if ($b !== '' && ctype_digit($b)) {
				$budgets[] = (int) $b;
			}
		}
	}
	if ($arg === '--skip-refs') {
		$skipRefs = true;
	}
}

$srcPath = $source === '4096p'
	? $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt'
	: $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
if (!is_file($srcPath)) {
	fwrite(STDERR, "missing source dict {$srcPath}\n");
	exit(1);
}

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
$n = min($pages, count($split['pages']));
$chunk = array();
for ($i = 0; $i < $n; $i++) {
	$chunk[] = array(
		'origIndex' => $i,
		'start' => (int) $split['pages'][$i]['start'],
		'len' => (int) $split['pages'][$i]['len'],
	);
}
$pageXml = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob)['sorted_page_xml'];
$articleText = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob)['sorted_article_text'];
$pageWordSet = array();
foreach (preg_split('/\s+/u', $articleText, -1, PREG_SPLIT_NO_EMPTY) ?: array() as $w) {
	$pageWordSet[$w] = true;
}

$seedWords = fractal_zip_phda9_dict_read_words($srcPath);
$candidates = array();
$seen = array();
foreach ($seedWords as $w) {
	if ($w === '' || isset($seen[$w])) {
		continue;
	}
	if (!isset($pageWordSet[$w]) && !str_contains($pageXml, $w)) {
		continue;
	}
	$seen[$w] = true;
	$candidates[] = array('token' => $w, 'kind' => 'word');
}
fwrite(STDERR, '[prune] scoring ' . number_format(count($candidates)) . ' / ' . number_format(count($seedWords))
	. " tokens @{$n}p …\n");
fflush(STDERR);
$scored = fractal_zip_phda9_dict_score_candidates($pageXml, $candidates);

$rows = array();
if (!$skipRefs) {
	$refs = array(
		'mixed_best' => $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt',
		'words_4096p' => $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt',
	);
	foreach ($refs as $label => $path) {
	if (!is_file($path)) {
		continue;
	}
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $path);
	$t0 = microtime(true);
	$r = fractal_zip_enwik_phda9_english_compress($pageXml, array(
		'tool' => 'phda9',
		'use_dict' => true,
		'timeout_sec' => 0,
		'wire_wrap' => true,
	));
	$rows[] = array(
		'label' => $label,
		'dict_bytes' => (int) filesize($path),
		'entries' => count(fractal_zip_phda9_dict_read_words($path)),
		'fzpa' => isset($r['bytes']) ? (int) $r['bytes'] : null,
		'rt' => !empty($r['roundtrip_ok']),
		'sec' => round(microtime(true) - $t0, 1),
		'path' => $path,
	);
	}
}

$variants = array(
	'save_greedy' => static fn (array $s, int $b): array => fractal_zip_phda9_dict_select_greedy($s, null, $b),
	'eff_greedy' => static fn (array $s, int $b): array => fractal_zip_phda9_dict_select_greedy_efficiency($s, null, $b),
);
foreach ($budgets as $budget) {
	foreach ($variants as $vlabel => $picker) {
		$label = 'prune_' . $vlabel . '_' . $budget;
		$words = $picker($scored, $budget);
		$outPath = $repo . '/benchmarks/.phda9_external_dict_' . $label . '.txt';
		$written = fractal_zip_phda9_dict_write_file($words, $outPath);
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $outPath);
		fwrite(STDERR, "[prune] compress {$label} entries=" . count($words) . " …\n");
		fflush(STDERR);
		$t0 = microtime(true);
		$r = fractal_zip_enwik_phda9_english_compress($pageXml, array(
			'tool' => 'phda9',
			'use_dict' => true,
			'timeout_sec' => 0,
			'wire_wrap' => true,
		));
		$rows[] = array(
			'label' => $label,
			'dict_bytes' => (int) $written['bytes'],
			'entries' => count($words),
			'budget' => $budget,
			'variant' => $vlabel,
			'fzpa' => isset($r['bytes']) ? (int) $r['bytes'] : null,
			'rt' => !empty($r['roundtrip_ok']),
			'sec' => round(microtime(true) - $t0, 1),
			'path' => $outPath,
		);
	}
}

usort($rows, static function (array $a, array $b): int {
	$fa = $a['fzpa'] ?? PHP_INT_MAX;
	$fb = $b['fzpa'] ?? PHP_INT_MAX;
	return $fa <=> $fb;
});

echo "bench_phda9_dict_prune_hunt @{$n}p source={$source}\n\n";
printf("%-28s %10s %8s %8s %6s %s\n", 'label', 'dict_B', 'entries', 'FZPA', 'sec', 'RT');
foreach ($rows as $row) {
	printf("%-28s %10s %8s %8s %6s %s\n",
		(string) $row['label'],
		number_format((int) $row['dict_bytes']),
		number_format((int) $row['entries']),
		isset($row['fzpa']) ? number_format((int) $row['fzpa']) : 'FAIL',
		(string) ($row['sec'] ?? '?'),
		!empty($row['rt']) ? 'ok' : 'FAIL'
	);
}

$best = $rows[0] ?? null;
if ($best !== null && isset($best['fzpa'])) {
	echo "\nbest: {$best['label']} FZPA=" . number_format((int) $best['fzpa']) . "\n";
	$baseFzpa = null;
	foreach ($rows as $row) {
		if (($row['label'] ?? '') === 'mixed_best') {
			$baseFzpa = $row['fzpa'] ?? null;
			break;
		}
	}
	if ($baseFzpa !== null) {
		echo 'Δ vs mixed_best: ' . sprintf('%+d', (int) $best['fzpa'] - (int) $baseFzpa) . "\n";
	}
}

$outJson = $repo . '/benchmarks/.enwik8_phda9_dict_prune_hunt_' . $n . 'p.json';
file_put_contents($outJson, json_encode(array(
	'generated' => date('c'),
	'pages' => $n,
	'source' => $source,
	'budgets' => $budgets,
	'rows' => $rows,
	'best' => $best,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "json → {$outJson}\n";
