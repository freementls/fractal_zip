#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Full enwik8 pp96 encode without textcodec (refresh test_files109.fz).
 *
 * Usage:
 *   export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
 *   php -d memory_limit=4096M benchmarks/run_enwik8_pp96_refresh.php
 *   php -d memory_limit=4096M benchmarks/run_enwik8_pp96_refresh.php --name=pp96_refresh
 */

$repo = dirname(__DIR__);

$name = 'pp96_refresh';
$entrySort = null;
$textInnerPromotion = false;
$parallelProduction = false;
$gateCheck = false;
$forceEncode = false;
$cliArgv = $_SERVER['argv'] ?? (isset($argv) && is_array($argv) ? $argv : array());
foreach ($cliArgv as $arg) {
	if (!is_string($arg)) {
		continue;
	}
	if (str_starts_with($arg, '--name=')) {
		$name = substr($arg, 7);
		continue;
	}
	if ($arg === '--no-entry-sort' || $arg === '--entry-sort=0') {
		$entrySort = '0';
		continue;
	}
	if ($arg === '--entry-sort=1' || $arg === '--entry-sort') {
		$entrySort = '1';
	}
	if ($arg === '--text-inner-promotion') {
		$textInnerPromotion = true;
		if ($name === 'pp96_refresh') {
			$name = 'text_inner_promotion';
		}
	}
	if ($arg === '--parallel-production') {
		$parallelProduction = true;
	}
	if ($arg === '--gate-check') {
		$gateCheck = true;
	}
	if ($arg === '--force') {
		$forceEncode = true;
	}
}

if ($gateCheck && !$forceEncode) {
	require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_enwik8_full_encode_gate.php';
	$gateJson = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_beat15m_fast_gate.json';
	$mono = null;
	if (is_file($gateJson)) {
		$g = json_decode((string) file_get_contents($gateJson), true);
		if (is_array($g)) {
			$mono = isset($g['mono_mi_384p_measured']) ? (int) $g['mono_mi_384p_measured'] : null;
		}
	}
	$eval = enwik8_full_encode_gate_evaluate($mono);
	if (!$eval['allow_full_encode']) {
		fwrite(STDERR, "REFUSE full encode: " . ($eval['reason'] ?? 'gate failed') . "\n");
		fwrite(STDERR, "Run: bash benchmarks/run_enwik8_beat15m_fast_gate.sh\n");
		exit(3);
	}
	fwrite(STDOUT, 'gate-check OK: ' . ($eval['reason'] ?? '') . "\n");
	exit(0);
}

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_NO_CLI_JIT=1');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
if (!$textInnerPromotion && !$parallelProduction) {
	putenv('FRACTAL_ZIP_PARALLEL_PROBE=1');
}
putenv('FRACTAL_ZIP_ALWAYS_TRY_BROTLI=0');
putenv('FRACTAL_ZIP_SPEED_TRY_BROTLI=0');
putenv('FRACTAL_ZIP_DISABLE_FZB_LITERAL_BROTLI_Q11=1');
putenv('FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

if ($entrySort !== null) {
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=' . $entrySort);
}
bench_world_record_apply_pp96_core_env();
if ($entrySort !== null) {
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=' . $entrySort);
}
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES=0');
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
putenv('FRACTAL_ZIP_WEB_REF=0');
if ($textInnerPromotion) {
	bench_world_record_apply_phda9_xml_promotion_env();
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0');
	putenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT');
	if ($entrySort === null) {
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
	}
}
if ($parallelProduction || $textInnerPromotion) {
	putenv('FRACTAL_ZIP_PARALLEL_PROBE=0');
	if ($parallelProduction) {
		bench_world_record_apply_parallel_production_env();
	}
}

fwrite(STDERR, '[pp96_refresh] SKIP_BROTLI=' . (getenv('FRACTAL_ZIP_SKIP_BROTLI') ?: 'unset')
	. ' text_codec=' . (getenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC') ?: 'unset')
	. ' FZBM_TRIES=' . (getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES') ?: 'unset')
	. ' paq_cmp=' . (getenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE') ?: 'unset')
	. ' entry_sort=' . (($es = getenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT')) !== false ? $es : 'unset')
	. ' text_inner=' . (getenv('FRACTAL_ZIP_TEXT_INNER') ?: 'unset')
	. ' text_inner_mono=' . (getenv('FRACTAL_ZIP_TEXT_INNER_MONO') ?: 'unset')
	. ' text_inner_layout=' . (getenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT') ?: 'unset')
	. ' pipeline_parallel=' . (getenv('FRACTAL_ZIP_PIPELINE_PARALLEL') ?: 'unset')
	. ' peel_jobs=' . (getenv('FRACTAL_ZIP_PEEL_JOBS') ?: 'unset')
	. ' inner_frontier_jobs=' . (getenv('FZ_INNER_FRONTIER_JOBS') ?: 'unset') . "\n");

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
	'bench_profile' => 'world-record-pp96-refresh',
	'encode_only' => true,
	'text_codec' => getenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC'),
	'text_inner' => getenv('FRACTAL_ZIP_TEXT_INNER'),
	'text_inner_format' => getenv('FRACTAL_ZIP_TEXT_INNER_FORMAT'),
	'text_inner_mono' => getenv('FRACTAL_ZIP_TEXT_INNER_MONO'),
	'text_inner_layout' => getenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT'),
	'text_inner_preprocess' => getenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS'),
);

$payload = array(
	'generated' => date('c'),
	'bench_profile' => 'world-record-pp96-refresh',
	'cases' => array($case),
);
file_put_contents($outJson, json_encode($payload, JSON_PRETTY_PRINT));

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
}

fwrite(STDERR, "[pp96_refresh] {$name}: fzc={$fzcBytes} B in " . number_format($sec, 1) . "s verify_ok=" . ($verifyOk ? '1' : 'null') . " → {$outJson}\n");
echo "fzc_bytes={$fzcBytes}\n";
if ($verifyOk) {
	echo "verify_ok=1\n";
}
