#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Measure pre-outer unified inner vs zpaq wire on current pp96 path.
 *
 * Usage:
 *   FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1 php -d memory_limit=4096M benchmarks/bench_enwik8_pre_outer_decomposition.php
 */

$repo = dirname(__DIR__);
$corpusPhrases = false;
foreach ($argv as $arg) {
	if ($arg === '--corpus-phrases' || $arg === '--corpus-phrases=1') {
		$corpusPhrases = true;
	}
}
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_NO_CLI_JIT=1');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_WEB_REF=0');
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

bench_world_record_apply_pp96_core_env();
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
if ($corpusPhrases) {
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=1');
	putenv('FRACTAL_ZIP_ENWIK_BOILERPLATE_PACK=0');
	putenv('FRACTAL_ZIP_ENWIK_HARMONY=0');
}
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');

$dir = $repo . DIRECTORY_SEPARATOR . 'test_files109';
$src = $dir . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}
$rawBytes = (int) filesize($src);

$dumpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_enwik_pre_outer_' . getmypid() . '.bin';
@unlink($dumpPath);
putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER=' . $dumpPath);
putenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY=1');

fwrite(STDERR, "[pre_outer] encode inner-only (pp96 core, textcodec=0)\n");
$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($dir, false);
$sec = microtime(true) - $t0;

putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');
putenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY');

$inner = is_file($dumpPath) ? (string) file_get_contents($dumpPath) : '';
$innerBytes = strlen($inner);
@unlink($dumpPath);

$gz1 = $inner !== '' ? @gzdeflate($inner, 1) : false;
$innerGz1 = is_string($gz1) ? strlen($gz1) : null;

$fzcPath = rtrim($dir, DIRECTORY_SEPARATOR) . '.fz';
$fzcBytes = is_file($fzcPath) ? (int) filesize($fzcPath) : 0;
$zpaqPayload = null;
$fzepTrailer = null;
if (is_file($fzcPath)) {
	require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	$blob = (string) file_get_contents($fzcPath);
	$meta = fractal_zip_enwik_peel_fzep_from_blob($blob);
	if ($meta !== null) {
		$zpaqPayload = (int) $meta['payloadLen'];
		$fzepTrailer = strlen($blob) - $zpaqPayload;
	}
}

$refs = array();
if (is_file($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_content_probe.json')) {
	$cp = json_decode((string) file_get_contents($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_content_probe.json'), true);
	if (is_array($cp)) {
		foreach ($cp['subsets'] ?? array() as $s) {
			if (($s['id'] ?? '') === 'text_all_pages') {
				$refs['text_gzip1'] = (int) ($s['gzip1_bytes'] ?? 0);
			}
		}
	}
}

$out = array(
	'generated' => date('c'),
	'raw_bytes' => $rawBytes,
	'inner_bytes' => $innerBytes,
	'inner_gzip1_bytes' => $innerGz1,
	'inner_zip_seconds' => round($sec, 3),
	'fzc_bytes_on_disk' => $fzcBytes,
	'zpaq_payload_bytes' => $zpaqPayload,
	'fzep_trailer_bytes' => $fzepTrailer,
	'refs' => $refs,
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR
	. ($corpusPhrases ? '.enwik8_pre_outer_decomposition_corpus.json' : '.enwik8_pre_outer_decomposition.json');
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));

echo "pre_outer_decomposition → {$path}\n";
echo '  raw=' . number_format($rawBytes) . ' inner=' . number_format($innerBytes);
if ($innerGz1 !== null) {
	echo ' inner_gzip1=' . number_format($innerGz1);
}
if ($zpaqPayload !== null) {
	echo ' zpaq_payload=' . number_format($zpaqPayload);
}
echo ' seconds=' . round($sec, 1) . "\n";
if (isset($refs['text_gzip1']) && $innerGz1 !== null) {
	$pct = 100.0 * $innerGz1 / max(1, $refs['text_gzip1']);
	echo '  inner_gzip1 vs text_gzip1: ' . sprintf('%.1f%%', $pct) . "\n";
}
