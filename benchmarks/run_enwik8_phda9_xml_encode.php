#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Full enwik8 encode with integrated phda9_xml member path (sorted English XML).
 *
 * Usage:
 *   php -d memory_limit=2048M benchmarks/run_enwik8_phda9_xml_encode.php --gate-check
 *   php -d memory_limit=2048M benchmarks/run_enwik8_phda9_xml_encode.php --gate-check-beat146
 *   php -d memory_limit=2048M benchmarks/run_enwik8_phda9_xml_encode.php --gate-check-hutter
 *   php benchmarks/run_enwik8_phda9_xml_encode.php --encode-only
 *   php benchmarks/run_enwik8_phda9_xml_encode.php --full-memory   # 2048M, full FZBM search
 *   php -d memory_limit=2048M benchmarks/run_enwik8_phda9_xml_encode.php --verify
 *
 * Env (optional):
 *   FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS — parallel chunk workers (default: auto from RAM)
 *   FRACTAL_ZIP_PHDA9_ENGLISH_TOOL — phda9_no_lstm (default) | phda9
 *   FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM — 1 (default) one phda9 context over all pages
 *   FRACTAL_ZIP_PAQ_PHDA9_DICT — external dictionary path
 */

$repo = dirname(__DIR__);

$gateCheck = false;
$encodeOnly = false;
$doVerify = false;
$force = false;
$wire384 = 524040;
$name = 'phda9_xml';
$beat146Check = false;
$hutterCheck = false;
foreach ($argv as $arg) {
	if ($arg === '--gate-check') {
		$gateCheck = true;
	}
	if ($arg === '--gate-check-beat146') {
		$beat146Check = true;
	}
	if ($arg === '--gate-check-hutter') {
		$hutterCheck = true;
	}
	if ($arg === '--encode-only') {
		$encodeOnly = true;
	}
	if ($arg === '--verify') {
		$doVerify = true;
	}
	if ($arg === '--force') {
		$force = true;
	}
	if (str_starts_with($arg, '--name=')) {
		$name = substr($arg, 7);
	}
	if (str_starts_with($arg, '--wire384=')) {
		$wire384 = (int) substr($arg, 10);
	}
}

if ($gateCheck && !$force) {
	require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_enwik8_full_encode_gate.php';
	$eval = enwik8_full_encode_gate_evaluate($wire384);
	echo json_encode($eval, JSON_PRETTY_PRINT) . "\n";
	exit($eval['allow_full_encode'] ? 0 : 1);
}

if ($beat146Check && !$force) {
	require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'enwik8_beat146_gate.php';
	$eval = enwik8_beat146_gate_evaluate($wire384, 384, null, ENWIK8_BEAT146_BEST_INTEGRATED, ENWIK8_BEAT146_BASELINE_384P, null, ENWIK8_BEAT146_EXTRAP_MARGIN, ENWIK8_BEAT146_FULL_PAGES, ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE, $repo);
	echo json_encode($eval, JSON_PRETTY_PRINT) . "\n";
	exit(!empty($eval['allow_full_encode']) ? 0 : 1);
}

if ($hutterCheck && !$force) {
	require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'enwik8_beat146_gate.php';
	$eval = enwik8_hutter_gate_evaluate($wire384, 384, null, ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE, null, $repo);
	echo json_encode($eval, JSON_PRETTY_PRINT) . "\n";
	exit(!empty($eval['allow_full_encode']) ? 0 : 1);
}

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_NO_CLI_JIT=1');
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_ALWAYS_TRY_BROTLI=0');
putenv('FRACTAL_ZIP_SPEED_TRY_BROTLI=0');
putenv('FRACTAL_ZIP_DISABLE_FZB_LITERAL_BROTLI_Q11=1');
putenv('FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
putenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');

require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_low_memory_env.php';
if (!in_array('--full-memory', $argv, true)) {
	putenv('FRACTAL_ZIP_LOW_MEMORY=1');
	bench_low_memory_apply_env();
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_process_guard.php';
fractal_zip_process_guard_register_cli();

bench_world_record_apply_pp96_core_env();
if (bench_low_memory_enabled()) {
	putenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES=64');
	putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
}
putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
putenv('FRACTAL_ZIP_WEB_REF=0');
putenv('FRACTAL_ZIP_TEXT_INNER=1');
putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=0');
if (getenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL') === false) {
	putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
}

$dir = $repo . DIRECTORY_SEPARATOR . 'test_files109';
if (!is_dir($dir)) {
	fwrite(STDERR, "Missing {$dir}\n");
	exit(1);
}

$fzcPath = rtrim($dir, DIRECTORY_SEPARATOR) . '.fz';
$outJson = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_' . $name . '.json';
@unlink($fzcPath);

fwrite(STDERR, '[phda9_xml_encode] low_memory=' . (bench_low_memory_enabled() ? '1' : '0')
	. ' php_limit=' . bench_low_memory_php_memory_limit() . "\n");
fwrite(STDERR, '[phda9_xml_encode] tool=' . (getenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL') ?: '?')
	. ' single_stream=' . (getenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM') ?: '0')
	. ' jobs=' . (getenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS') ?: 'auto')
	. ' dict=' . (getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT') ?: 'none') . "\n");

$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($dir, false);
$sec = round(microtime(true) - $t0, 2);

$fzcBytes = is_file($fzcPath) ? (int) filesize($fzcPath) : 0;
$rawBytes = is_file($dir . DIRECTORY_SEPARATOR . 'enwik8') ? (int) filesize($dir . DIRECTORY_SEPARATOR . 'enwik8') : 0;

$case = array(
	'label' => 'test_files109',
	'raw_bytes' => $rawBytes,
	'fzc_bytes' => $fzcBytes,
	'zip_seconds' => $sec,
	'outer_codec' => fractal_zip::$last_outer_codec ?? null,
	'member_format' => 'phda9_xml',
	'phda9_single_stream' => getenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM') === '1',
	'phda9_tool' => getenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL'),
	'verify_ok' => null,
	'bench_profile' => 'phda9_xml_full',
);
$payload = array('generated' => date('c'), 'cases' => array($case));
file_put_contents($outJson, json_encode($payload, JSON_PRETTY_PRINT));

fwrite(STDERR, "[phda9_xml_encode] fzc={$fzcBytes} B in {$sec}s → {$outJson}\n");
echo "fzc_bytes={$fzcBytes}\n";

if ($doVerify && !$encodeOnly && $fzcBytes > 0) {
	$verifyScript = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'verify_enwik8_textcodec_fzc.php';
	if (is_file($verifyScript)) {
		fwrite(STDERR, "[phda9_xml_encode] verify start (phda9 decompress per member — slow)\n");
		$verifyOut = array();
		$verifyRet = 0;
		$verifyIni = bench_low_memory_php_ini_args();
		exec(
			escapeshellarg(PHP_BINARY) . ' ' . implode(' ', array_map('escapeshellarg', $verifyIni)) . ' '
			. escapeshellarg($verifyScript) . ' ' . escapeshellarg($fzcPath) . ' 2>&1',
			$verifyOut,
			$verifyRet
		);
		foreach ($verifyOut as $line) {
			fwrite(STDERR, $line . "\n");
		}
		$case['verify_ok'] = ($verifyRet === 0);
		file_put_contents($outJson, json_encode(array('generated' => date('c'), 'cases' => array($case)), JSON_PRETTY_PRINT));
		echo $case['verify_ok'] ? "verify_ok=1\n" : "verify_ok=0\n";
		exit($case['verify_ok'] ? 0 : 1);
	}
}

exit($fzcBytes > 0 ? 0 : 1);
