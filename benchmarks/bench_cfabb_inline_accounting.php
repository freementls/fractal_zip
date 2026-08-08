#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * cfabb sidecar vs inline-on-the-fly tradeoff (fold meta bytes + phda9 wire score).
 * Fast iteration without full .fz phda9 zip.
 *
 * Usage: php benchmarks/bench_cfabb_inline_accounting.php [--pages=96]
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_wiki_lom.php';
require_once $repo . '/fractal_zip_collision_free_abbrevs.php';
require_once $repo . '/fractal_zip_gpu_substring.php';

$pages = 96;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}

$dict = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$text = '';
$n = min($pages, count($split['pages']));
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$text .= fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
}
$decoded = fractal_zip_wiki_lom_entity_decode_text($text);
$gateText = fractal_zip_wiki_lom_phda9_wire_escape($decoded);
$baseScore = fractal_zip_cfabb_phda9_plain_bytes($gateText);

$run = static function (string $mode) use ($decoded, $gateText, $repo, $n): array {
	$GLOBALS['fractal_zip_cfabb_inline_cache'] = null;
	if ($mode === 'frozen') {
		putenv('FRACTAL_ZIP_CFABB_INLINE=0');
		putenv('FRACTAL_ZIP_CFABB_NO_FOLD=0');
		putenv('FRACTAL_ZIP_CFABB_SA=0');
		$table = $repo . '/benchmarks/.enwik8_cfabb_table_384p_filtered.json';
		if ($n <= 96 && is_file($repo . '/benchmarks/.substring_sa_bench_96p.json')) {
			$table = $repo . '/benchmarks/.enwik8_cfabb_table_384p_filtered.json';
		}
		putenv('FRACTAL_ZIP_CFABB_TABLE=' . (is_file($table) ? $table : ''));
	} else {
		putenv('FRACTAL_ZIP_CFABB_TABLE=');
		putenv('FRACTAL_ZIP_CFABB_INLINE=1');
		putenv('FRACTAL_ZIP_CFABB_NO_FOLD=1');
		putenv('FRACTAL_ZIP_CFABB_SA=1');
	}
	putenv('FRACTAL_ZIP_WIKI_LOM_CFABB=1');
	$t0 = microtime(true);
	$entries = fractal_zip_wiki_lom_load_cfabb_list($decoded, array(
		'max_acronyms' => 4096,
		'min_count' => 4,
		'corpus_pages' => $n,
		'phda9_gate_text' => $gateText,
	));
	$mineSec = microtime(true) - $t0;
	$foldMeta = ($mode === 'inline') ? 0 : fractal_zip_cfabb_fold_meta_bytes($entries);
	$wireScore = fractal_zip_cfabb_phda9_wire_score($gateText, $entries);
	return array(
		'mode' => $mode,
		'entries' => count($entries),
		'fold_meta_bytes' => $foldMeta,
		'phda9_wire_score' => $wireScore,
		'wire_delta_vs_base' => $wireScore - $baseScore,
		'mine_sec' => round($mineSec, 2),
	);
};

$frozen = $run('frozen');
$inline = $run('inline');

printf("cfabb accounting pages=%d preserve_len=%d base_phda9=%s\n", $n, strlen($decoded), number_format($baseScore));
foreach (array($frozen, $inline) as $r) {
	printf("  %-7s entries=%4d fold_meta=%6s phda9_score=%s delta=%+s mine=%.1fs\n",
		$r['mode'],
		$r['entries'],
		number_format($r['fold_meta_bytes']),
		number_format($r['phda9_wire_score']),
		number_format($r['wire_delta_vs_base']),
		$r['mine_sec']
	);
}
printf("  inline saves fold_meta=%s (RAM pays mine %.1fs)\n",
	number_format($frozen['fold_meta_bytes'] - $inline['fold_meta_bytes']),
	$inline['mine_sec']
);

$out = $repo . '/benchmarks/.cfabb_inline_accounting_' . $n . 'p.json';
file_put_contents($out, json_encode(array(
	'generated' => date('c'),
	'pages' => $n,
	'base_phda9' => $baseScore,
	'frozen' => $frozen,
	'inline' => $inline,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "json → {$out}\n";
