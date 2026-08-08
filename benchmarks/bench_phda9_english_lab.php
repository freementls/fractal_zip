#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Small phda9 English payload lab — find what input phda9 models best (bytes + RT + speed).
 *
 * Usage:
 *   php benchmarks/bench_phda9_english_lab.php [--pages=32] [--timeout=300]
 *   FRACTAL_ZIP_PAQ_PHDA9_DICT=benchmarks/.phda9_external_dict.txt php benchmarks/bench_phda9_english_lab.php
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_phda9_english.php';

$pageLimit = 32;
$timeoutSec = 300;
$fastTool = false;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(4, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--timeout=')) {
		$timeoutSec = max(30, (int) substr($arg, 10));
	} elseif ($arg === '--fast') {
		$fastTool = true;
	}
}

putenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT');
putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_MODELS');
putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_JOBS');

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pageLimit, count($split['pages']));

$sortedChunk = array();
for ($i = 0; $i < $n; $i++) {
	$sortedChunk[] = array(
		'origIndex' => $i,
		'start' => (int) $split['pages'][$i]['start'],
		'len' => (int) $split['pages'][$i]['len'],
	);
}
$payloads = fractal_zip_enwik_phda9_english_payloads_from_refs($sortedChunk, $blob);

// mono_mi fztx inner baseline @slice
$slice = (string) $split['header'];
for ($i = 0; $i < $n; $i++) {
	$slice .= substr($blob, (int) $split['pages'][$i]['start'], (int) $split['pages'][$i]['len']);
}
$slice .= (string) $split['footer'];
$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_phda9_lab_' . getmypid();
@mkdir($tmp, 0700, true);
$work = $tmp . DIRECTORY_SEPARATOR . 'w';
@mkdir($work, 0700, true);
file_put_contents($work . DIRECTORY_SEPARATOR . 'enwik8', $slice);
putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_TEXT_INNER=1');
putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
$dump = $tmp . DIRECTORY_SEPARATOR . 'inner.bin';
putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER=' . $dump);
(new fractal_zip())->zip_folder($work, false);
putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');
if (is_file($dump)) {
	$payloads['fztx_inner'] = (string) file_get_contents($dump);
}

$fz = new fractal_zip(256, false, false, null, false);
$zpaqExe = fractal_zip::zpaq_executable();
$tools = $fastTool ? array('phda9_no_lstm') : array('phda9', 'phda9_no_lstm');
$dictPath = fractal_zip_paq_phda9_dict_path();
$rows = array();

echo "pages={$n} dict=" . ($dictPath ?? 'none') . "\n";
printf("%-22s %8s %10s %6s %6s %s\n", 'payload', 'raw', 'tool', 'bytes', 'sec', 'rt');

foreach ($payloads as $label => $plain) {
	$rawLen = strlen($plain);
	if ($rawLen === 0) {
		continue;
	}
	$zpaqB = null;
	if ($zpaqExe !== null && $label === 'sorted_page_xml') {
		$zpaqBlob = $fz->outer_zpaq_blob_with_meth_fragment($zpaqExe, $plain, ' -method 9');
		$zpaqB = $zpaqBlob !== null ? strlen($zpaqBlob) : null;
		printf("%-22s %8d %10s %10s %6s %s\n", $label, $rawLen, 'zpaq_m9', $zpaqB ?? 'fail', '-', '-');
	}
	foreach ($tools as $tool) {
		foreach (array(false, true) as $useDict) {
			if ($useDict && $dictPath === null) {
				continue;
			}
			$tag = $tool . ($useDict ? '+dict' : '');
			$t0 = microtime(true);
			$r = fractal_zip_enwik_phda9_english_compress($plain, array(
				'tool' => $tool,
				'use_dict' => $useDict,
				'timeout_sec' => $timeoutSec,
			));
			$sec = round(microtime(true) - $t0, 2);
			$bytes = $r['bytes'] ?? null;
			$rt = !empty($r['roundtrip_ok']) ? 'ok' : 'no';
			printf("%-22s %8d %10s %10s %6s %s\n", $label, $rawLen, $tag, $bytes ?? 'fail', $sec, $rt);
			$rows[] = array(
				'payload' => $label,
				'raw_bytes' => $rawLen,
				'tool' => $tag,
				'compressed_bytes' => $bytes,
				'seconds' => $sec,
				'roundtrip_ok' => $rt === 'ok',
				'ratio' => ($bytes !== null && $rawLen > 0) ? round($bytes / $rawLen, 4) : null,
			);
		}
	}
}

$out = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_english_lab.json';
file_put_contents($out, json_encode(array(
	'generated' => date('c'),
	'pages' => $n,
	'dict' => $dictPath,
	'rows' => $rows,
), JSON_PRETTY_PRINT));
echo "→ {$out}\n";

fractal_zip_enwik_recursive_remove($tmp);
