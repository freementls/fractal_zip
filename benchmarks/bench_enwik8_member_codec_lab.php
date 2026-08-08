#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Member codec lab: zpaq vs phda9 on mono_mi text-inner inner @slice (per-chunk).
 *
 * Usage:
 *   php -d memory_limit=4096M benchmarks/bench_enwik8_member_codec_lab.php [--pages=96]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_WEB_REF=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_member_shootout.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$pageLimit = 96;
$timeoutSec = 600;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(16, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--timeout=')) {
		$timeoutSec = max(30, (int) substr($arg, 10));
	}
}

bench_world_record_apply_pp96_core_env();
putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
putenv('FRACTAL_ZIP_TEXT_INNER=1');
putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pageLimit, count($split['pages']));
$slice = (string) $split['header'];
for ($i = 0; $i < $n; $i++) {
	$slice .= substr($blob, (int) $split['pages'][$i]['start'], (int) $split['pages'][$i]['len']);
}
$slice .= (string) $split['footer'];

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_member_codec_' . getmypid();
@mkdir($tmp, 0700, true);
$work = $tmp . DIRECTORY_SEPARATOR . 'work';
@mkdir($work, 0700, true);
file_put_contents($work . DIRECTORY_SEPARATOR . 'enwik8', $slice);

// Full integrated wire baseline.
$fzc = $work . '.fz';
@unlink($fzc);
$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($work, false);
$wireMono = is_file($fzc) ? (int) filesize($fzc) : 0;
$wireSec = round(microtime(true) - $t0, 2);

// Dump pre-outer inner (whole stream).
$dumpPath = $tmp . DIRECTORY_SEPARATOR . 'inner.bin';
@unlink($dumpPath);
putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER=' . $dumpPath);
$fz2 = new fractal_zip();
$fz2->zip_folder($work, false);
putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');
$wholeInner = is_file($dumpPath) ? (string) file_get_contents($dumpPath) : '';
@unlink($dumpPath);

$fzProbe = new fractal_zip(256, false, false, null, false);
$zpaqExe = fractal_zip::zpaq_executable();
$zpaqOn = static function (string $payload) use ($fzProbe, $zpaqExe): ?int {
	if ($zpaqExe === null) {
		return null;
	}
	$b = $fzProbe->outer_zpaq_blob_with_meth_fragment($zpaqExe, $payload, ' -method 9');
	return ($b !== null && $b !== '') ? strlen($b) : null;
};

$modelRun = static function (string $modelId, string $payload) use ($timeoutSec): array {
	$t0 = microtime(true);
	$r = fractal_zip_text_compressor_run($modelId, $payload, array(
		'timeout_sec' => $timeoutSec,
		'wire_wrap' => true,
	));
	$r['wall_seconds'] = round(microtime(true) - $t0, 2);
	return $r;
};

$wholeRow = array(
	'kind' => 'whole_inner',
	'bytes' => strlen($wholeInner),
	'gzip1' => strlen((string) @gzencode($wholeInner, 1)),
	'zpaq_m9' => $zpaqOn($wholeInner),
);
foreach (array('phda9', 'zpaq9') as $mid) {
	$r = $modelRun($mid, $wholeInner);
	$wholeRow['model_' . $mid] = array(
		'bytes' => isset($r['payload']) && is_string($r['payload']) ? strlen($r['payload']) : null,
		'roundtrip_ok' => !empty($r['roundtrip_ok']),
		'seconds' => $r['wall_seconds'] ?? null,
		'status' => $r['status'] ?? null,
	);
}

// Per virtual member chunks via shootout (models only, no stacks).
putenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT=1');
putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_MODELS=phda9:zpaq9');
putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_STACKS=');
putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_PARALLEL=0');

// Re-encode with shootout enabled for wire comparison.
@unlink($fzc);
$t0 = microtime(true);
$fz3 = new fractal_zip();
$fz3->zip_folder($work, false);
$wireShootout = is_file($fzc) ? (int) filesize($fzc) : 0;
$shootSec = round(microtime(true) - $t0, 2);

$chunkRows = array();
// Mine chunk inners from sorted split pages (same as text-inner build).
$sortedChunk = array();
for ($i = 0; $i < $n; $i++) {
	$sortedChunk[] = array(
		'origIndex' => $i,
		'start' => (int) $split['pages'][$i]['start'],
		'len' => (int) $split['pages'][$i]['len'],
	);
}
$splitPages = enwik_build_text_inner_split_pages_from_refs($sortedChunk, $blob);
$pageCount = count($sortedChunk);
$pagesPerMember = $pageCount;
for ($i = 0; $i < $pageCount; $i += $pagesPerMember) {
	$chunkSplit = array_slice($splitPages, $i, $pagesPerMember);
	$layoutInput = array();
	foreach ($chunkSplit as $pg) {
		$layoutInput[] = array(
			'title' => (string) $pg['title'],
			'origIndex' => (int) $pg['origIndex'],
			'text' => (string) $pg['text'],
		);
	}
	$chunkLayout = fractal_zip_enwik_text_layout_apply($layoutInput, 'mi_reorder', array('seed' => 1));
	$innerBlob = (string) ($chunkLayout['blob'] ?? '');
	if ($innerBlob === '') {
		continue;
	}
	$shoot = fractal_zip_enwik_member_shootout_pick($innerBlob);
	$chunkRows[] = array(
		'chunk' => (int) ($i / $pagesPerMember),
		'inner_bytes' => strlen($innerBlob),
		'shootout_pick' => $shoot['pick'] ?? '?',
		'shootout_bytes' => $shoot['bytes'] ?? null,
		'zpaq_m9' => $zpaqOn($innerBlob),
		'candidates' => $shoot['candidates'] ?? array(),
	);
}

$report = array(
	'generated' => date('c'),
	'pages' => $n,
	'timeout_sec' => $timeoutSec,
	'wire_mono_mi' => $wireMono,
	'wire_shootout_models' => $wireShootout,
	'wire_delta' => $wireShootout - $wireMono,
	'encode_seconds_mono' => $wireSec,
	'encode_seconds_shootout' => $shootSec,
	'whole_inner' => $wholeRow,
	'chunks' => $chunkRows,
);
$out = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_member_codec_lab.json';
file_put_contents($out, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "pages={$n} wire_mono={$wireMono} wire_shootout={$wireShootout} delta=" . ($wireShootout - $wireMono) . "\n";
echo 'whole_inner bytes=' . $wholeRow['bytes'] . ' zpaq_m9=' . ($wholeRow['zpaq_m9'] ?? 'n/a');
if (isset($wholeRow['model_phda9']['bytes'])) {
	echo ' phda9=' . ($wholeRow['model_phda9']['bytes'] ?? 'fail');
}
echo "\n→ {$out}\n";

@unlink($fzc);
fractal_zip_enwik_recursive_remove($tmp);
