<?php
declare(strict_types=1);

/**
 * Compare production zip_folder wire bytes with the fractal-inner prediction scheduler
 * disabled vs default-enabled.
 *
 * This is a recipe-free production-path check: it copies each corpus, runs zip_folder(),
 * and compares the resulting .fz wire sizes. It does not load fixture recipes.
 *
 * Usage:
 *   php benchmarks/compare_inner_scheduler_wire.php
 *   php benchmarks/compare_inner_scheduler_wire.php --set=stress
 *   php benchmarks/compare_inner_scheduler_wire.php --cases=test_files140,test_files144,test_files30
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_predict_outer_encode.php';

/** @return list<string> */
function fz_inner_scheduler_wire_default_cases(string $set): array
{
	if ($set === 'golden') {
		return array('test_files24', 'test_files26', 'test_files27', 'test_files28', 'test_files29', 'test_files30', 'test_files31', 'test_files32');
	}
	if ($set === 'stress') {
		$out = array();
		for ($i = 140; $i <= 150; $i++) {
			$out[] = 'test_files' . (string)$i;
		}
		return $out;
	}
	if ($set === 'quick') {
		return array('test_files140', 'test_files144', 'test_files149', 'test_files24', 'test_files29');
	}
	$out = fz_inner_scheduler_wire_default_cases('stress');
	foreach (fz_inner_scheduler_wire_default_cases('golden') as $case) {
		$out[] = $case;
	}
	return $out;
}

/**
 * @return array{bytes:int,seconds:float,ok:bool,error:string}
 */
function fz_inner_scheduler_wire_zip_case(string $repoRoot, string $case, string $workRoot): array
{
	$src = $repoRoot . DIRECTORY_SEPARATOR . $case;
	if (!is_dir($src)) {
		return array('bytes' => 0, 'seconds' => 0.0, 'ok' => false, 'error' => 'missing corpus');
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
			return array('bytes' => 0, 'seconds' => $seconds, 'ok' => false, 'error' => 'missing fzc output');
		}
		$n = filesize($fzcPath);
		return array('bytes' => $n === false ? 0 : (int)$n, 'seconds' => $seconds, 'ok' => true, 'error' => '');
	} catch (Throwable $e) {
		return array('bytes' => 0, 'seconds' => 0.0, 'ok' => false, 'error' => $e->getMessage());
	} finally {
		bench_predict_outer_remove_dir($work);
		if (is_file($fzcPath)) {
			@unlink($fzcPath);
		}
	}
}

$set = 'all';
$casesArg = '';
foreach (array_slice($argv, 1) as $arg) {
	if (str_starts_with($arg, '--set=')) {
		$set = strtolower(trim(substr($arg, 6)));
	} elseif (str_starts_with($arg, '--cases=')) {
		$casesArg = trim(substr($arg, 8));
	} elseif ($arg === '--quick') {
		$set = 'quick';
	} elseif ($arg === '--help' || $arg === '-h') {
		fwrite(STDOUT, "Usage: php benchmarks/compare_inner_scheduler_wire.php [--quick] [--set=quick|stress|golden|all] [--cases=a,b]\n");
		exit(0);
	}
}

if ($casesArg !== '') {
	$cases = array_values(array_filter(array_map('trim', explode(',', $casesArg)), static fn(string $v): bool => $v !== ''));
} else {
	$cases = fz_inner_scheduler_wire_default_cases($set);
}
if ($cases === array()) {
	fwrite(STDERR, "compare_inner_scheduler_wire: no cases selected\n");
	exit(2);
}

$savedScheduler = getenv('FRACTAL_ZIP_INNER_PREDICT_SCHEDULER');
$workRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_inner_scheduler_wire_' . (string)getmypid();
if (!is_dir($workRoot)) {
	mkdir($workRoot, 0755, true);
}
bench_predict_outer_sweep_work_root($workRoot);

$rows = array();
$failed = false;
try {
	foreach ($cases as $case) {
		putenv('FRACTAL_ZIP_INNER_PREDICT_SCHEDULER=0');
		$off = fz_inner_scheduler_wire_zip_case($repoRoot, $case, $workRoot);
		putenv('FRACTAL_ZIP_INNER_PREDICT_SCHEDULER');
		$default = fz_inner_scheduler_wire_zip_case($repoRoot, $case, $workRoot);
		$ok = $off['ok'] && $default['ok'] && $off['bytes'] === $default['bytes'];
		if (!$ok) {
			$failed = true;
		}
		$rows[] = array($case, $off, $default, $ok);
	}
} finally {
	if ($savedScheduler === false) {
		putenv('FRACTAL_ZIP_INNER_PREDICT_SCHEDULER');
	} else {
		putenv('FRACTAL_ZIP_INNER_PREDICT_SCHEDULER=' . $savedScheduler);
	}
	bench_predict_outer_remove_dir($workRoot);
}

$sumOffBytes = 0;
$sumDefaultBytes = 0;
$sumOffSeconds = 0.0;
$sumDefaultSeconds = 0.0;
foreach ($rows as $row) {
	[$case, $off, $default, $ok] = $row;
	$sumOffBytes += (int)$off['bytes'];
	$sumDefaultBytes += (int)$default['bytes'];
	$sumOffSeconds += (float)$off['seconds'];
	$sumDefaultSeconds += (float)$default['seconds'];
	printf(
		"%s off_bytes=%d default_bytes=%d off_s=%.4f default_s=%.4f %s\n",
		(string)$case,
		(int)$off['bytes'],
		(int)$default['bytes'],
		(float)$off['seconds'],
		(float)$default['seconds'],
		$ok ? 'ok' : ('FAIL off_error=' . (string)$off['error'] . ' default_error=' . (string)$default['error'])
	);
}
printf(
	"TOTAL off_bytes=%d default_bytes=%d off_s=%.4f default_s=%.4f %s\n",
	$sumOffBytes,
	$sumDefaultBytes,
	$sumOffSeconds,
	$sumDefaultSeconds,
	(!$failed && $sumOffBytes === $sumDefaultBytes) ? 'ok' : 'FAIL'
);

exit((!$failed && $sumOffBytes === $sumDefaultBytes) ? 0 : 1);
