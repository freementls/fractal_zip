#!/usr/bin/env php
<?php
declare(strict_types=1);

/** Retest mixed_tiered dict mining after candidate-collection fix. */
$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');
putenv('FRACTAL_ZIP_LOW_MEMORY=1');
putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
require_once $repo . '/benchmarks/bench_low_memory_env.php';
bench_low_memory_apply_env();
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once $repo . '/fractal_zip_phda9_dict_mine.php';
require_once $repo . '/fractal_zip_phda9_dict.php';

$pages = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
if ($split === null) {
	fwrite(STDERR, "enwik split failed\n");
	exit(1);
}
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

putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');

fwrite(STDERR, "[mixed_tiered_retest] mining @{$n}p …\n");
$mined = fractal_zip_phda9_dict_mine_from_enwik($blob, array(
	'mode' => 'mixed_tiered',
	'pages' => $n,
	'word_budget_pct' => 0.85,
	'max_subwords' => 2048,
));
$outPath = $repo . '/benchmarks/.phda9_external_dict_mixed_tiered_' . $n . 'p_retest.txt';
$written = fractal_zip_phda9_dict_write_file($mined['words'], $outPath);

fwrite(STDERR, "[mixed_tiered_retest] compress …\n");
putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $outPath);
$base = fractal_zip_enwik_phda9_english_compress($pageXml, array(
	'tool' => 'phda9_no_lstm',
	'use_dict' => false,
	'timeout_sec' => 0,
	'wire_wrap' => true,
));
$fz = fractal_zip_enwik_phda9_english_compress($pageXml, array(
	'tool' => 'phda9_no_lstm',
	'use_dict' => true,
	'timeout_sec' => 0,
	'wire_wrap' => true,
));

$baseBytes = (int) ($base['bytes'] ?? 0);
$fzBytes = isset($fz['bytes']) ? (int) $fz['bytes'] : null;
$delta = $fzBytes !== null ? $fzBytes - $baseBytes : null;

$row = array(
	'generated' => date('c'),
	'pages' => $n,
	'mode' => 'mixed_tiered',
	'dict_path' => $outPath,
	'dict_bytes' => (int) $written['bytes'],
	'selected' => (int) ($mined['stats']['selected'] ?? 0),
	'candidates' => (int) ($mined['stats']['candidates'] ?? 0),
	'kind_hist' => $mined['stats']['kind_hist'] ?? array(),
	'fzpa_baseline_bytes' => $baseBytes,
	'fzpa_bytes' => $fzBytes,
	'delta_vs_nodict' => $delta,
	'roundtrip_ok' => !empty($fz['roundtrip_ok']),
);
$jsonPath = $repo . '/benchmarks/.enwik8_phda9_mixed_tiered_retest_' . $n . 'p.json';
file_put_contents($jsonPath, json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

printf(
	"mixed_tiered @%dp: dict=%s B selected=%d kind_hist=%s FZPA=%s Δ=%s RT=%s\n",
	$n,
	number_format((int) $written['bytes']),
	(int) ($mined['stats']['selected'] ?? 0),
	json_encode($mined['stats']['kind_hist'] ?? array(), JSON_UNESCAPED_SLASHES),
	$fzBytes !== null ? number_format($fzBytes) : 'FAIL',
	$delta !== null ? sprintf('%+d', $delta) : '-',
	!empty($fz['roundtrip_ok']) ? 'ok' : 'FAIL'
);
printf("→ %s\n", $jsonPath);
