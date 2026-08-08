#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * enwik8 encode with words_base94 text codec on article &lt;text&gt; regions (lab winner).
 *
 * Usage:
 *   export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
 *   php -d memory_limit=4096M benchmarks/run_enwik8_textcodec_encode.php --name=pp96_textcodec
 *   php -d memory_limit=4096M benchmarks/run_enwik8_textcodec_encode.php --inner   # inner_focus caps (no high-tier outer predict)
 */

$repo = dirname(__DIR__);
$innerFocus = false;
$parallelProduction = false;
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_NO_CLI_JIT=1');
// Must be set before fractal_zip.php load: outer_skip_env_allows() caches on first read.
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_ALWAYS_TRY_BROTLI=0');
putenv('FRACTAL_ZIP_SPEED_TRY_BROTLI=0');
putenv('FRACTAL_ZIP_DISABLE_FZB_LITERAL_BROTLI_Q11=1');
putenv('FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES=0');
putenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_ORDER=0');
putenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER=0');
putenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ARC_COMPARE=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_BROTLI_COMPARE=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
putenv('FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER_FORK=0');
putenv('FZ_INNER_FRONTIER_JOBS=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0'); // pp96 sets predict max=0 (uncapped); layered probes spawn brotli -q 1
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$name = 'pp96_textcodec';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--name=')) {
		$name = substr($arg, 7);
	}
	if ($arg === '--inner') {
		$innerFocus = true;
	}
}

// pp96 core or inner_focus (skip apply_high_env — stalls on textcodec wire).
if ($innerFocus) {
	require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_inner_env.php';
	bench_world_record_apply_inner_focus_env();
} else {
	bench_world_record_apply_pp96_core_env();
}
// Re-apply after pp96 (apply_enwik_tuned sets 16 MiB path-order brotli cap).
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES=0');
putenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER=0');
putenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ARC_COMPARE=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_BROTLI_COMPARE=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
putenv('FRACTAL_ZIP_WEB_REF=0');
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=words_base94_isp'); // lossless preserve-text; literals l:… for OOV (not UNK)
// sort_lines_alpha shrinks payload gzip but inflates EZTC sidecars (~3.5 MiB / 20 pages); use none for bytes push.
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_TRANSFORM=none');
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_SEED=1');
if ($parallelProduction) {
	bench_world_record_apply_parallel_production_env();
}

fwrite(STDERR, '[textcodec] profile=' . ($innerFocus ? 'inner_focus' : 'pp96_core')
	. ' SKIP_BROTLI=' . (getenv('FRACTAL_ZIP_SKIP_BROTLI') ?: 'unset')
	. ' brotli_skipped=' . (getenv('FRACTAL_ZIP_SKIP_BROTLI') === '1' ? 'yes' : 'no')
	. ' PIPELINE_PARALLEL=' . (getenv('FRACTAL_ZIP_PIPELINE_PARALLEL') !== false ? getenv('FRACTAL_ZIP_PIPELINE_PARALLEL') : 'unset')
	. ' native_zpaq_cmp=' . ((getenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE') !== false) ? getenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE') : 'unset') . "\n");

$dir = $repo . DIRECTORY_SEPARATOR . 'test_files109';
if (!is_dir($dir)) {
	fwrite(STDERR, "Missing {$dir}\n");
	exit(1);
}

$outJson = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_' . $name . '.json';
$fzcPath = rtrim($dir, DIRECTORY_SEPARATOR) . '.fz';
@unlink($fzcPath);

$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($dir, false);
$sec = microtime(true) - $t0;

$fzcBytes = is_file($fzcPath) ? (int) filesize($fzcPath) : 0;
$rawBytes = is_file($dir . DIRECTORY_SEPARATOR . 'enwik8') ? (int) filesize($dir . DIRECTORY_SEPARATOR . 'enwik8') : 100000000;

$memberCount = $fz->zip_folder_member_count > 0 ? $fz->zip_folder_member_count : null;
if ($memberCount === null && isset($fz->folder_bundle_census['files'])) {
	$memberCount = (int) $fz->folder_bundle_census['files'];
}

$case = array(
	'label' => 'test_files109',
	'raw_bytes' => $rawBytes,
	'fzc_bytes' => $fzcBytes,
	'zip_seconds' => round($sec, 4),
	'outer_codec' => fractal_zip::$last_outer_codec ?? null,
	'outer_zpaq_method' => fractal_zip::$last_zpaq_method ?? null,
	'folder_unified_stream' => fractal_zip::$used_folder_unified_stream,
	'member_count' => $memberCount,
	'verify_ok' => null,
	'bench_profile' => 'world-record-textcodec',
	'encode_only' => true,
	'text_codec' => getenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC'),
	'text_codec_transform' => getenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_TRANSFORM'),
);

$payload = array(
	'generated' => date('c'),
	'bench_profile' => 'world-record-textcodec',
	'cases' => array($case),
);
file_put_contents($outJson, json_encode($payload, JSON_PRETTY_PRINT));

if ($fzcBytes > 25_000_000 || $sec < 600 || $fzcBytes < 8_000_000) {
	fwrite(STDERR, "[enwik8_textcodec] WARNING: suspicious result {$fzcBytes} B in " . number_format($sec, 1)
		. "s (pp96 ~19.6 MiB / ~20–70 min) — check native passthrough or wire\n");
}

$verifyOk = null;
$verifyScript = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'verify_enwik8_textcodec_fzc.php';
if (is_file($verifyScript) && $fzcBytes > 0) {
	$verifyOut = array();
	$verifyRet = 0;
	exec(
		escapeshellarg(PHP_BINARY) . ' -d memory_limit=4096M '
		. escapeshellarg($verifyScript) . ' ' . escapeshellarg($fzcPath) . ' 2>&1',
		$verifyOut,
		$verifyRet
	);
	foreach ($verifyOut as $line) {
		fwrite(STDERR, $line . "\n");
	}
	$verifyOk = ($verifyRet === 0);
	$case['verify_ok'] = $verifyOk;
	file_put_contents($outJson, json_encode($payload, JSON_PRETTY_PRINT));
	if (!$verifyOk) {
		fwrite(STDERR, "[enwik8_textcodec] FAIL post-encode verify (exit {$verifyRet})\n");
		exit(1);
	}
}

fwrite(STDERR, "[enwik8_textcodec] {$name}: fzc={$fzcBytes} B in " . number_format($sec, 1) . "s verify_ok=" . ($verifyOk ? '1' : 'null') . " → {$outJson}\n");
echo "fzc_bytes={$fzcBytes}\n";
if ($verifyOk) {
	echo "verify_ok=1\n";
}
