#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * GPU fractal substring search spike — CPU baseline vs optional CUDA helper.
 *
 * Usage: php benchmarks/bench_fractal_substring_gpu_spike.php [--pages=sample5|enwik8|384]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$pagesArg = 'enwik8';
$sliceMode = 'shell';
$helperMode = 'auto';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pagesArg = substr($arg, 8);
	} elseif (str_starts_with($arg, '--slice=')) {
		$sliceMode = substr($arg, 8);
	} elseif (str_starts_with($arg, '--mode=')) {
		$helperMode = trim(substr($arg, 7));
	}
}

if ($sliceMode === 'raw1m') {
	ini_set('memory_limit', '8192M');
}

$shellBuf = '';
if ($sliceMode === 'raw1m') {
	$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
	if (!is_file($src)) {
		fwrite(STDERR, "Missing {$src}\n");
		exit(1);
	}
	$shellBuf = (string) file_get_contents($src, false, null, 0, 1 << 20);
} elseif ($pagesArg === 'sample5') {
	$sampleDir = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'corpus' . DIRECTORY_SEPARATOR . 'enwik8_sample5';
	$manifestPath = $sampleDir . DIRECTORY_SEPARATOR . 'manifest.json';
	if (!is_file($manifestPath)) {
		fwrite(STDERR, "Run: php benchmarks/build_enwik8_sample_pages.php\n");
		exit(1);
	}
	$manifest = json_decode((string) file_get_contents($manifestPath), true);
	foreach ($manifest['pages'] ?? array() as $p) {
		$xml = (string) file_get_contents($sampleDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $p['page_path']));
		$shellBuf .= fractal_zip_enwik_extract_page_shell_only($xml);
	}
} else {
	$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
	$blob = (string) file_get_contents($src);
	$pageCount = is_numeric($pagesArg) ? (int) $pagesArg : 384;
	$split = fractal_zip_enwik_split_shell_and_text($blob, $pageCount);
	if ($split === null) {
		exit(1);
	}
	foreach ($split['pages'] as $pg) {
		$shellBuf .= (string) $pg['shell'];
	}
}

$sliceLen = min(1 << 20, strlen($shellBuf));
$slice = substr($shellBuf, 0, $sliceLen);

/**
 * CPU baseline — same window policy as tools/gpu_substring (Rust): for each len in [min,max],
 * count every fixed-length window; tie-break top-K like Rust (count desc, len asc, first i asc).
 */
$cpuScore = static function (string $data, int $minLen, int $maxLen, int $topK): array {
	$n = strlen($data);
	$lo = min($minLen, $maxLen);
	$hi = max($minLen, $maxLen);
	/** @var array<string, array{0:int,1:int}> */
	$scores = array();
	for ($len = $lo; $len <= $hi; $len++) {
		if ($len === 0 || $len > $n) {
			continue;
		}
		$limit = $n - $len;
		for ($i = 0; $i <= $limit; $i++) {
			$sub = substr($data, $i, $len);
			if (!isset($scores[$sub])) {
				$scores[$sub] = array(1, $i);
				continue;
			}
			$scores[$sub][0]++;
			if ($i < $scores[$sub][1]) {
				$scores[$sub][1] = $i;
			}
		}
	}
	$entries = array();
	foreach ($scores as $sub => $pair) {
		$subStr = (string) $sub;
		$entries[] = array($subStr, $pair[0], strlen($subStr), $pair[1]);
	}
	usort($entries, static function (array $a, array $b): int {
		if ($a[1] !== $b[1]) {
			return $b[1] <=> $a[1];
		}
		if ($a[2] !== $b[2]) {
			return $a[2] <=> $b[2];
		}
		return $a[3] <=> $b[3];
	});
	$top = array();
	foreach (array_slice($entries, 0, $topK) as $row) {
		$top[] = $row[0];
	}
	return array('candidates_scored' => count($scores), 'top_k' => $top, 'wall_seconds' => 0.0);
};

$t0 = microtime(true);
$cpu = $cpuScore($slice, 4, 16, 50);
$cpu['wall_seconds'] = round(microtime(true) - $t0, 6);

$gpuToolDir = $repo . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'gpu_substring';
$rustHelper = $gpuToolDir . DIRECTORY_SEPARATOR . 'target' . DIRECTORY_SEPARATOR . 'release' . DIRECTORY_SEPARATOR . 'gpu_substring_score_rs';
$pyHelper = $gpuToolDir . DIRECTORY_SEPARATOR . 'gpu_substring_score';

$cudaAvailable = false;
$cudaProbe = 'python3 -c "from numba import cuda; print(1 if cuda.is_available() else 0)" 2>/dev/null';
$cudaOut = array();
exec($cudaProbe, $cudaOut, $cudaRet);
if ($cudaRet === 0 && isset($cudaOut[0]) && trim($cudaOut[0]) === '1') {
	$cudaAvailable = true;
}

$helperCandidates = array();
$wantRust = in_array($helperMode, array('auto', 'rust'), true);
$wantPy = in_array($helperMode, array('auto', 'cpu_parallel', 'cuda', 'python'), true);
if ($wantRust && is_executable($rustHelper)) {
	$helperCandidates[] = array('path' => $rustHelper, 'used' => 'gpu_substring_score_rs', 'kind' => 'rust');
}
if ($wantPy && is_executable($pyHelper) && ($helperMode !== 'rust')) {
	$helperCandidates[] = array('path' => $pyHelper, 'used' => 'gpu_substring_score', 'kind' => 'python');
}

$gpuHelper = '';
$helperUsed = '';
$gpu = array(
	'status' => 'unavailable',
	'mode' => 'unavailable',
	'candidates_scored' => 0,
	'top_k' => array(),
	'wall_seconds' => 0.0,
	'top_k_overlap_pct' => 0.0,
	'top_k_overlap_vs_cpu' => 0.0,
	'speedup' => 0.0,
	'cuda_available' => $cudaAvailable,
	'rust_available' => is_executable($rustHelper),
);
foreach ($helperCandidates as $cand) {
	$gpuHelper = (string) $cand['path'];
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_gpu_sub_' . getmypid();
	@mkdir($tmp, 0700, true);
	$in = $tmp . DIRECTORY_SEPARATOR . 'shell.bin';
	$outJson = $tmp . DIRECTORY_SEPARATOR . 'top.json';
	file_put_contents($in, $slice);
	$t1 = microtime(true);
	if ($cand['kind'] === 'rust') {
		$cmd = escapeshellarg($gpuHelper) . ' ' . escapeshellarg($in) . ' 4 16 50 ' . escapeshellarg($outJson);
	} else {
		$pyMode = ($helperMode === 'auto') ? 'cpu_parallel' : $helperMode;
		$envPrefix = 'FZ_GPU_SUBSTRING_MODE=' . escapeshellarg($pyMode) . ' ';
		$cmd = $envPrefix . escapeshellarg($gpuHelper) . ' ' . escapeshellarg($in) . ' 4 16 50 ' . escapeshellarg($outJson);
	}
	exec($cmd, $xo, $ret);
	$wall = round(microtime(true) - $t1, 6);
	if ($ret === 0 && is_file($outJson)) {
		$decoded = json_decode((string) file_get_contents($outJson), true);
		if (is_array($decoded)) {
			$gpuTop = $decoded['top_k'] ?? array();
			$overlap = count(array_intersect($cpu['top_k'], $gpuTop));
			$overlapPct = round(100.0 * $overlap / max(1, count($cpu['top_k'])), 2);
			$helperWall = isset($decoded['wall_seconds']) ? (float) $decoded['wall_seconds'] : $wall;
			$decodedCuda = isset($decoded['cuda_available']) ? (bool) $decoded['cuda_available'] : $cudaAvailable;
			$helperUsed = (string) $cand['used'];
			$gpu = array(
				'status' => 'ok',
				'helper_used' => $helperUsed,
				'mode' => (string) ($decoded['mode'] ?? 'unknown'),
				'workers' => (int) ($decoded['workers'] ?? 0),
				'candidates_scored' => (int) ($decoded['candidates_scored'] ?? 0),
				'top_k' => $gpuTop,
				'wall_seconds' => round($helperWall, 6),
				'top_k_overlap_pct' => $overlapPct,
				'top_k_overlap_vs_cpu' => $overlap / max(1, count($cpu['top_k'])),
				'speedup' => $cpu['wall_seconds'] > 0 ? round($cpu['wall_seconds'] / max(0.000001, $helperWall), 2) : 0.0,
				'cuda_available' => $decodedCuda,
				'rust_available' => is_executable($rustHelper),
			);
			break;
		}
	}
	fractal_zip_enwik_recursive_remove($tmp);
}

$out = array(
	'generated' => date('c'),
	'slice_bytes' => $sliceLen,
	'cpu' => $cpu,
	'gpu' => $gpu,
	'gpu_helper' => $gpuHelper,
	'helper_used' => $helperUsed,
	'helper_mode' => $helperMode,
	'gpu_available' => $cudaAvailable,
	'integrate_gate' => array(
		'overlap_min' => 0.90,
		'speedup_min' => 2.0,
		'pass' => ($gpu['status'] === 'ok')
			&& ($gpu['top_k_overlap_vs_cpu'] >= 0.90)
			&& ($gpu['speedup'] >= 2.0),
	),
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.fractal_substring_gpu_spike.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));
echo "fractal substring GPU spike → {$path}\n";
echo '  CPU: scored=' . $cpu['candidates_scored'] . '  ' . $cpu['wall_seconds'] . "s\n";
echo '  GPU: status=' . $gpu['status'] . '  helper=' . ($gpu['helper_used'] ?? 'n/a')
	. '  mode=' . ($gpu['mode'] ?? 'n/a')
	. '  overlap_pct=' . ($gpu['top_k_overlap_pct'] ?? 0)
	. '  speedup=' . ($gpu['speedup'] ?? 0)
	. '  cuda=' . ($cudaAvailable ? 'yes' : 'no') . "\n";
