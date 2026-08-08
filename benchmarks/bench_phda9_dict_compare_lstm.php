#!/usr/bin/env php
<?php
declare(strict_types=1);

/** Compare phda9 LSTM FZPA @384p sorted page XML across dict paths. */
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';

$pages = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
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
echo "dict_compare @{$n}p plain=" . number_format(strlen($pageXml)) . " tool=phda9\n";

$dicts = array(
	'mixed_best' => $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt',
	'words_384p' => $repo . '/benchmarks/.phda9_external_dict_words_384p.txt',
	'words_4096p' => $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt',
	'words_8192p' => $repo . '/benchmarks/.phda9_external_dict_words_8192p.txt',
);
$rows = array();
foreach ($dicts as $label => $path) {
	if (!is_file($path)) {
		echo "  {$label}: missing {$path}\n";
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
	$sec = round(microtime(true) - $t0, 1);
	$b = isset($r['bytes']) ? (int) $r['bytes'] : null;
	printf(
		"  %-12s dict=%s B FZPA=%s RT=%s sec=%s\n",
		$label,
		number_format((int) filesize($path)),
		$b !== null ? number_format($b) : 'FAIL',
		!empty($r['roundtrip_ok']) ? 'ok' : 'FAIL',
		$sec
	);
	$rows[] = array('label' => $label, 'path' => $path, 'fzpa' => $b, 'rt' => !empty($r['roundtrip_ok']));
}

$out = $repo . '/benchmarks/.enwik8_phda9_dict_compare_' . $n . 'p_lstm.json';
file_put_contents($out, json_encode(array(
	'generated' => date('c'),
	'pages' => $n,
	'tool' => 'phda9',
	'plain_bytes' => strlen($pageXml),
	'rows' => $rows,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "  json → {$out}\n";
