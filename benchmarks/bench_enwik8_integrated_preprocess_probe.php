#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Fast gzip-1 probes on full enwik8: integrated pre-zpaq transforms (no encode).
 *
 * Usage: php -d memory_limit=4096M benchmarks/bench_enwik8_integrated_preprocess_probe.php
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

fwrite(STDERR, "[preprocess] reading enwik8…\n");
$blob = (string) file_get_contents($src);
$rawLen = strlen($blob);
$g = static function (string $b): int {
	$z = gzdeflate($b, 1);
	return is_string($z) ? strlen($z) : 0;
};

$rows = array();
$rows[] = array('id' => 'raw', 'label' => 'raw enwik8', 'blob_bytes' => $rawLen, 'gzip1_bytes' => $g($blob));

$bp = fractal_zip_enwik_boilerplate_pack_apply($blob);
$bBp = (string) $bp['blob'];
$rows[] = array(
	'id' => 'boilerplate',
	'label' => 'full-blob boilerplate pack',
	'blob_bytes' => strlen($bBp),
	'gzip1_bytes' => $g($bBp),
	'dict_bytes' => strlen((string) $bp['dict']),
);

$mined = fractal_zip_enwik_mine_corpus_phrases($bBp, 10, 96, 32, 192);
$cp = fractal_zip_enwik_phrase_pack_apply($bBp, $mined);
$bCp = (string) $cp['blob'];
$rows[] = array(
	'id' => 'corpus_phrases',
	'label' => 'boilerplate + corpus phrases',
	'blob_bytes' => strlen($bCp),
	'gzip1_bytes' => $g($bCp),
	'dict_bytes' => strlen((string) $cp['dict']),
	'phrases' => count($mined),
);

$hp = fractal_zip_enwik_boilerplate_pack_apply($blob);
$header = '';
$refs = enwik_split_page_refs($blob);
if ($refs !== null) {
	$hp2 = fractal_zip_enwik_boilerplate_pack_apply((string) $refs['header']);
	$header = (string) $hp2['blob'];
}
$siteinfoOnly = $header . substr($blob, strlen((string) ($refs['header'] ?? '')));
$rows[] = array(
	'id' => 'siteinfo_header_only',
	'label' => 'siteinfo pack on header only (tier B)',
	'blob_bytes' => strlen($siteinfoOnly),
	'gzip1_bytes' => $g($siteinfoOnly),
);

$preOuter = null;
$prePath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_pre_outer_decomposition.json';
if (is_file($prePath)) {
	$preOuter = json_decode((string) file_get_contents($prePath), true);
}

$out = array(
	'generated' => date('c'),
	'raw_bytes' => $rawLen,
	'rows' => $rows,
	'pre_outer_ref' => is_array($preOuter) ? array(
		'inner_bytes' => $preOuter['inner_bytes'] ?? null,
		'inner_gzip1_bytes' => $preOuter['inner_gzip1_bytes'] ?? null,
		'zpaq_payload_bytes' => $preOuter['zpaq_payload_bytes'] ?? null,
	) : null,
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_integrated_preprocess_probe.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));

$baseGz = (int) $rows[0]['gzip1_bytes'];
echo "integrated preprocess probe → {$path}\n";
foreach ($rows as $r) {
	$gz = (int) $r['gzip1_bytes'];
	$d = $baseGz - $gz;
	echo '  ' . $r['id'] . ': gzip1=' . number_format($gz)
		. ($d !== 0 ? ' (' . ($d > 0 ? '−' : '+') . number_format(abs($d)) . ' vs raw gzip1)' : '')
		. "\n";
}
