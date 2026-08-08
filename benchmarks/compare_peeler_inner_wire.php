<?php
declare(strict_types=1);

/**
 * Compare production zip_folder wire bytes with peeler-plus-fractal-inner
 * candidates disabled vs default-enabled.
 *
 * This is recipe-free: each corpus is copied to a temp tree, compressed through
 * zip_folder(), and compared by final .fz byte size.
 *
 * Usage:
 *   php benchmarks/compare_peeler_inner_wire.php --quick
 *   php benchmarks/compare_peeler_inner_wire.php --cases=test_files49,test_files62
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_predict_outer_encode.php';

/** @return list<string> */
function fz_peeler_inner_wire_default_cases(string $set): array {
	if ($set === 'quick') {
		return array('test_files49', 'test_files50', 'test_files62', 'test_files63', 'test_files105', 'test_files110');
	}
	if ($set === 'media') {
		return array('test_files49', 'test_files50', 'test_files51', 'test_files52', 'test_files53', 'test_files54', 'test_files55', 'test_files56', 'test_files57', 'test_files58', 'test_files59', 'test_files60', 'test_files61', 'test_files62', 'test_files63', 'test_files64', 'test_files65');
	}
	if ($set === 'mixed') {
		return array('test_files69', 'test_files70', 'test_files71', 'test_files72', 'test_files74', 'test_files75', 'test_files76', 'test_files77', 'test_files78', 'test_files80', 'test_files81', 'test_files82', 'test_files133');
	}
	if ($set === 'silesia') {
		return array('test_files133');
	}
	return array_merge(fz_peeler_inner_wire_default_cases('quick'), fz_peeler_inner_wire_default_cases('mixed'));
}

/**
 * @return array{bytes:int,seconds:float,ok:bool,error:string,pick:string}
 */
function fz_peeler_inner_wire_zip_case(string $repoRoot, string $case, string $workRoot): array {
	$src = $repoRoot . DIRECTORY_SEPARATOR . $case;
	if (!is_dir($src)) {
		return array('bytes' => 0, 'seconds' => 0.0, 'ok' => false, 'error' => 'missing corpus', 'pick' => '');
	}
	$work = $workRoot . DIRECTORY_SEPARATOR . $case;
	bench_predict_outer_copy_dir($src, $work);
	$fzcPath = $work . '.fz';
	if (is_file($fzcPath)) {
		@unlink($fzcPath);
	}
	try {
		$fz = new fractal_zip(256, false, true, null, false);
		$t0 = microtime(true);
		ob_start();
		try {
			$fz->zip_folder($work, false);
		} finally {
			ob_end_clean();
		}
		$seconds = microtime(true) - $t0;
		if (!is_file($fzcPath)) {
			return array('bytes' => 0, 'seconds' => $seconds, 'ok' => false, 'error' => 'missing fzc output', 'pick' => '');
		}
		$n = filesize($fzcPath);
		return array(
			'bytes' => $n === false ? 0 : (int)$n,
			'seconds' => $seconds,
			'ok' => true,
			'error' => '',
			'pick' => (string)(fractal_zip::$folder_zip_wire_best_outer_codec ?? fractal_zip::$last_written_container_codec ?? ''),
		);
	} catch (Throwable $e) {
		return array('bytes' => 0, 'seconds' => 0.0, 'ok' => false, 'error' => $e->getMessage(), 'pick' => '');
	} finally {
		bench_predict_outer_remove_dir($work);
		if (is_file($fzcPath)) {
			@unlink($fzcPath);
		}
	}
}

$set = 'quick';
$casesArg = '';
foreach (array_slice($argv, 1) as $arg) {
	if (str_starts_with($arg, '--set=')) {
		$set = strtolower(trim(substr($arg, 6)));
	} elseif (str_starts_with($arg, '--cases=')) {
		$casesArg = trim(substr($arg, 8));
	} elseif ($arg === '--quick') {
		$set = 'quick';
	} elseif ($arg === '--help' || $arg === '-h') {
		fwrite(STDOUT, "Usage: php benchmarks/compare_peeler_inner_wire.php [--quick] [--set=quick|media|mixed|silesia|all] [--cases=a,b]\n");
		exit(0);
	}
}

if ($casesArg !== '') {
	$cases = array_values(array_filter(array_map('trim', explode(',', $casesArg)), static fn(string $v): bool => $v !== ''));
} else {
	$cases = fz_peeler_inner_wire_default_cases($set);
}
if ($cases === array()) {
	fwrite(STDERR, "compare_peeler_inner_wire: no cases selected\n");
	exit(2);
}

$savedPeeler = getenv('FRACTAL_ZIP_INNER_PEELER_CANDIDATES');
$workRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_peeler_inner_wire_' . (string)getmypid();
if (!is_dir($workRoot)) {
	mkdir($workRoot, 0755, true);
}
bench_predict_outer_sweep_work_root($workRoot);

$rows = array();
try {
	foreach ($cases as $case) {
		putenv('FRACTAL_ZIP_INNER_PEELER_CANDIDATES=0');
		$off = fz_peeler_inner_wire_zip_case($repoRoot, $case, $workRoot);
		putenv('FRACTAL_ZIP_INNER_PEELER_CANDIDATES');
		$default = fz_peeler_inner_wire_zip_case($repoRoot, $case, $workRoot);
		$rows[] = array($case, $off, $default);
	}
} finally {
	if ($savedPeeler === false) {
		putenv('FRACTAL_ZIP_INNER_PEELER_CANDIDATES');
	} else {
		putenv('FRACTAL_ZIP_INNER_PEELER_CANDIDATES=' . $savedPeeler);
	}
	bench_predict_outer_remove_dir($workRoot);
}

$sumOffBytes = 0;
$sumDefaultBytes = 0;
$sumOffSeconds = 0.0;
$sumDefaultSeconds = 0.0;
$wins = 0;
$ties = 0;
$losses = 0;
$failed = false;
foreach ($rows as $row) {
	[$case, $off, $default] = $row;
	if (!$off['ok'] || !$default['ok']) {
		$failed = true;
	}
	$delta = (int)$default['bytes'] - (int)$off['bytes'];
	if ($delta < 0) {
		$wins++;
	} elseif ($delta === 0) {
		$ties++;
	} else {
		$losses++;
	}
	$sumOffBytes += (int)$off['bytes'];
	$sumDefaultBytes += (int)$default['bytes'];
	$sumOffSeconds += (float)$off['seconds'];
	$sumDefaultSeconds += (float)$default['seconds'];
	printf(
		"%s off_bytes=%d default_bytes=%d delta=%+d off_s=%.4f default_s=%.4f pick=%s %s\n",
		(string)$case,
		(int)$off['bytes'],
		(int)$default['bytes'],
		$delta,
		(float)$off['seconds'],
		(float)$default['seconds'],
		(string)$default['pick'],
		($off['ok'] && $default['ok']) ? 'ok' : ('FAIL off_error=' . (string)$off['error'] . ' default_error=' . (string)$default['error'])
	);
}
$totalDelta = $sumDefaultBytes - $sumOffBytes;
printf(
	"TOTAL off_bytes=%d default_bytes=%d delta=%+d off_s=%.4f default_s=%.4f wins=%d ties=%d losses=%d %s\n",
	$sumOffBytes,
	$sumDefaultBytes,
	$totalDelta,
	$sumOffSeconds,
	$sumDefaultSeconds,
	$wins,
	$ties,
	$losses,
	$failed ? 'FAIL' : 'ok'
);

exit($failed ? 1 : 0);
