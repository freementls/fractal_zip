<?php
declare(strict_types=1);

/**
 * Corpus sampling + LZ probes for outer prediction (no fractal_zip encode).
 *
 * Used by benchmarks/predict_outer_benchmarks.php. Squash hoplite–style probes:
 * brotli Q1/Q11, xz -1/-9, zstd default; gzdeflate-9 ratio for compressibility prior.
 */

/**
 * Walk corpus leaves and concatenate capped chunks until $maxTotalBytes.
 *
 * @return array{0: string, 1: int} sample blob and total raw bytes indexed (recursive)
 */
function predict_outer_sample_dir(string $dir, int $maxTotalBytes, int $maxPerFile): array
{
	$totalRaw = 0;
	try {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
		/** @var SplFileInfo[] $files */
		$files = [];
		foreach ($it as $f) {
			if ($f->isFile()) {
				$files[] = $f;
			}
		}
		usort($files, static fn ($a, $b) => strcmp($a->getPathname(), $b->getPathname()));
		foreach ($files as $f) {
			$totalRaw += (int) $f->getSize();
		}
	} catch (Throwable $e) {
		return ['', 0];
	}

	$buf = '';
	$remain = $maxTotalBytes;
	foreach ($files as $f) {
		if ($remain <= 0) {
			break;
		}
		$path = $f->getPathname();
		$n = min($maxPerFile, $remain, (int) $f->getSize());
		if ($n <= 0) {
			continue;
		}
		$chunk = @file_get_contents($path, false, null, 0, $n);
		if (!is_string($chunk) || $chunk === '') {
			continue;
		}
		$buf .= $chunk;
		$remain -= strlen($chunk);
	}

	return [$buf, $totalRaw];
}

/** Fraction of bytes in 0x09,0x0a,0x0d,0x20–0x7e (rough text-like). */
function predict_outer_printable_ratio(string $s): float
{
	$len = strlen($s);
	if ($len === 0) {
		return 0.0;
	}
	$ok = 0;
	for ($i = 0; $i < $len; $i++) {
		$c = ord($s[$i]);
		if ($c === 9 || $c === 10 || $c === 13 || ($c >= 32 && $c <= 126)) {
			$ok++;
		}
	}

	return $ok / $len;
}

/**
 * Run compressor on stdin blob; return compressed length or null.
 *
 * @param list<string> $argvTail args after executable
 */
function predict_outer_compress_stdin(?string $exe, array $argvTail, string $stdin, float $timeoutSec): ?int
{
	if ($exe === null || $exe === '') {
		return null;
	}
	$cmd = array_merge([$exe], $argvTail);
	$des = [
		0 => ['pipe', 'r'],
		1 => ['pipe', 'w'],
		2 => ['pipe', 'w'],
	];
	$p = proc_open($cmd, $des, $pipes, null, null);
	if (!is_resource($p)) {
		return null;
	}
	// Interleave stdin writes with stdout/stderr drains. A blocking whole-blob fwrite
	// deadlocks once the child emits >64 KB before consuming all input (child blocks
	// on its full stdout pipe, PHP blocks on the full stdin pipe) — the timeout below
	// never engaged, leaking orphaned php+compressor pairs from predict fork children.
	$in = $pipes[0];
	$out = $pipes[1];
	$err = $pipes[2];
	stream_set_blocking($in, false);
	stream_set_blocking($out, false);
	stream_set_blocking($err, false);
	$t0 = microtime(true);
	$off = 0;
	$len = strlen($stdin);
	$outLen = 0;
	$fail = static function () use ($p, &$in, &$out, &$err): ?int {
		foreach ([$in, $out, $err] as $h) {
			if (is_resource($h)) {
				@fclose($h);
			}
		}
		@proc_terminate($p, 9);
		@proc_close($p);

		return null;
	};
	while ($out !== null || $err !== null || $in !== null) {
		if (microtime(true) - $t0 > $timeoutSec) {
			return $fail();
		}
		$r = [];
		if ($out !== null) {
			$r[] = $out;
		}
		if ($err !== null) {
			$r[] = $err;
		}
		$w = ($in !== null && $off < $len) ? [$in] : [];
		if ($r === [] && $w === []) {
			break;
		}
		$e = null;
		$n = @stream_select($r, $w, $e, 0, 200000);
		if ($n === false) {
			return $fail();
		}
		foreach ($r as $h) {
			$chunk = fread($h, 262144);
			if (is_string($chunk) && $chunk !== '') {
				if ($h === $out) {
					$outLen += strlen($chunk);
				}

				continue;
			}
			if (feof($h)) {
				fclose($h);
				if ($h === $out) {
					$out = null;
				} else {
					$err = null;
				}
			}
		}
		if ($w !== []) {
			$wrote = @fwrite($in, substr($stdin, $off, 262144));
			if ($wrote === false) {
				return $fail();
			}
			$off += (int) $wrote;
			if ($off >= $len) {
				fclose($in);
				$in = null;
			}
		} elseif ($in !== null && $off >= $len) {
			fclose($in);
			$in = null;
		}
	}
	foreach ([$in, $out, $err] as $h) {
		if (is_resource($h)) {
			@fclose($h);
		}
	}
	proc_close($p);

	return $outLen;
}

/**
 * Whether layered probe layer‑1 should run brotli/xz/zstd fast stdin probes concurrently (same bytes as sequential).
 * {@code FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER1_FAST_PIPE=0} disables; unset defaults on under CLI.
 */
function predict_outer_parallel_layer1_fast_pipe_enabled(): bool
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER1_FAST_PIPE');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));

		return $cached = !($v === '0' || $v === 'false' || $v === 'no' || $v === 'off');
	}

	return $cached = PHP_SAPI === 'cli';
}

/**
 * Run brotli (q1), xz (-1), zstd (-1) stdin→stdout probes in parallel; output lengths match {@see predict_outer_compress_stdin}.
 *
 * @return array{brotli: int|null, xz: int|null, zstd: int|null}
 */
function predict_outer_compress_stdin_parallel_fast_batch(
	?string $brotliExe,
	?string $xzExe,
	?string $zstdExe,
	string $stdin,
	float $timeoutSec
): array {
	$results = ['brotli' => null, 'xz' => null, 'zstd' => null];
	/** @var array<string, array{exe: string, tail: list<string>}> $want */
	$want = [];
	if ($brotliExe !== null && $brotliExe !== '') {
		$want['brotli'] = ['exe' => $brotliExe, 'tail' => ['-c', '-q', '1']];
	}
	if ($xzExe !== null && $xzExe !== '') {
		$want['xz'] = ['exe' => $xzExe, 'tail' => ['-1', '-c']];
	}
	if ($zstdExe !== null && $zstdExe !== '') {
		$want['zstd'] = ['exe' => $zstdExe, 'tail' => ['-1', '-c']];
	}
	if ($want === []) {
		return $results;
	}
	$nullSink = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
	/** @var array<string, array{proc: resource, pipe: resource, len: int}> $active */
	$active = [];
	foreach ($want as $key => $spec) {
		$cmd = array_merge([$spec['exe']], $spec['tail']);
		$des = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['file', $nullSink, 'w'],
		];
		$p = proc_open($cmd, $des, $pipes, null, null);
		if (!is_resource($p)) {
			foreach ($active as $st) {
				if (is_resource($st['pipe'])) {
					fclose($st['pipe']);
				}
				if (is_resource($st['in'])) {
					fclose($st['in']);
				}
				proc_terminate($st['proc'], 9);
				proc_close($st['proc']);
			}

			return $results;
		}
		// Do NOT write the whole payload here: a blocking fwrite deadlocks once the
		// child emits >64 KB before consuming all stdin (child blocks on its full
		// stdout pipe, PHP blocks on the full stdin pipe). Interleave writes with
		// reads in the select loop below instead.
		stream_set_blocking($pipes[0], false);
		stream_set_blocking($pipes[1], false);
		$active[$key] = ['proc' => $p, 'pipe' => $pipes[1], 'in' => $pipes[0], 'off' => 0, 'len' => 0];
	}
	$t0 = microtime(true);
	$stdinLen = strlen($stdin);
	$finishOne = function (string $key) use (&$active, &$results): void {
		if (!isset($active[$key])) {
			return;
		}
		$st = $active[$key];
		$hp = $st['pipe'];
		$len = $st['len'];
		while (is_resource($hp)) {
			$chunk = fread($hp, 65536);
			if ($chunk === false || $chunk === '') {
				break;
			}
			$len += strlen($chunk);
		}
		if (is_resource($hp)) {
			fclose($hp);
		}
		if (is_resource($st['in'])) {
			fclose($st['in']);
		}
		proc_close($st['proc']);
		$results[$key] = $len > 0 ? $len : null;
		unset($active[$key]);
	};
	while ($active !== []) {
		if (microtime(true) - $t0 > $timeoutSec) {
			break;
		}
		foreach (array_keys($active) as $key) {
			if (!isset($active[$key])) {
				continue;
			}
			if (!proc_get_status($active[$key]['proc'])['running']) {
				$finishOne($key);
			}
		}
		$read = [];
		$write = [];
		foreach ($active as $st) {
			if (is_resource($st['pipe'])) {
				$read[] = $st['pipe'];
			}
			if (is_resource($st['in']) && $st['off'] < $stdinLen) {
				$write[] = $st['in'];
			}
		}
		if ($read === [] && $write === []) {
			break;
		}
		$w = $write === [] ? null : $write;
		$ex = null;
		$n = @stream_select($read, $w, $ex, 0, 50000);
		if ($n === false) {
			usleep(5000);

			continue;
		}
		if ($n > 0) {
			foreach ($active as $key => $st) {
				$hp = $st['pipe'];
				if (is_resource($hp) && in_array($hp, $read, true)) {
					$chunk = fread($hp, 65536);
					if ($chunk !== false && $chunk !== '') {
						$active[$key]['len'] += strlen($chunk);
					}
				}
				$hi = $st['in'];
				if (is_array($w) && is_resource($hi) && in_array($hi, $w, true)) {
					$wrote = @fwrite($hi, substr($stdin, $st['off'], 262144));
					if ($wrote === false) {
						fclose($hi);
						$active[$key]['in'] = null;
					} else {
						$active[$key]['off'] += (int) $wrote;
						if ($active[$key]['off'] >= $stdinLen) {
							fclose($hi);
							$active[$key]['in'] = null;
						}
					}
				}
			}
		}
	}
	foreach ($active as $key => $st) {
		proc_terminate($st['proc'], 9);
		if (is_resource($st['pipe'])) {
			fclose($st['pipe']);
		}
		if (is_resource($st['in'])) {
			fclose($st['in']);
		}
		proc_close($st['proc']);
		$results[$key] = null;
	}

	return $results;
}

/**
 * @return array{
 *   gzip9_len?: int,
 *   gzip_ratio?: float,
 *   brotli_q1?: int|null,
 *   brotli_q11?: int|null,
 *   xz1?: int|null,
 *   xz9?: int|null,
 *   zstd?: int|null,
 *   printable_ratio: float,
 *   sample_bytes: int,
 * }
 */
function predict_outer_run_probes(
	string $sample,
	?string $brotliExe,
	?string $xzExe,
	?string $zstdExe,
	float $timeoutSec
): array {
	$out = [
		'printable_ratio' => predict_outer_printable_ratio($sample),
		'sample_bytes' => strlen($sample),
	];
	if ($sample === '') {
		return $out;
	}
	$gz = gzdeflate($sample, 9);
	if ($gz !== false) {
		$out['gzip9_len'] = strlen($gz);
		$out['gzip_ratio'] = strlen($gz) / max(1, strlen($sample));
	}
	// Match fractal_zip outer CLI: brotli -c -q N (stdin → stdout)
	if ($brotliExe !== null) {
		$out['brotli_q1'] = predict_outer_compress_stdin($brotliExe, ['-c', '-q', '1'], $sample, $timeoutSec);
		$out['brotli_q11'] = predict_outer_compress_stdin($brotliExe, ['-c', '-q', '11'], $sample, $timeoutSec);
	}
	if ($xzExe !== null) {
		$out['xz1'] = predict_outer_compress_stdin($xzExe, ['-1', '-c'], $sample, $timeoutSec);
		$out['xz9'] = predict_outer_compress_stdin($xzExe, ['-9', '-c'], $sample, $timeoutSec);
	}
	if ($zstdExe !== null) {
		$out['zstd'] = predict_outer_compress_stdin($zstdExe, ['-c'], $sample, $timeoutSec);
	}

	return $out;
}

/**
 * Decide predicted outer codec name for fz tuning.
 *
 * @param array<string, mixed> $probes from predict_outer_run_probes + zpaq_available bool + speed_first bool
 *
 * @return array{outer: string, confidence: string, reasons: list<string>, lz_best?: string|null}
 */
function predict_outer_decide(array $probes): array
{
	$reasons = [];
	if (($probes['sample_bytes'] ?? 0) < 32) {
		return ['outer' => 'gzip', 'confidence' => 'low', 'reasons' => ['sample empty or tiny — fallback gzip'], 'lz_best' => null];
	}
	$speed = !empty($probes['speed_first']);
	$zpaqOk = !empty($probes['zpaq_available']);
	$gr = isset($probes['gzip_ratio']) ? (float) $probes['gzip_ratio'] : null;
	$pr = isset($probes['printable_ratio']) ? (float) $probes['printable_ratio'] : 0.0;
	$br11 = isset($probes['brotli_q11']) ? $probes['brotli_q11'] : null;
	$xz9 = isset($probes['xz9']) ? $probes['xz9'] : null;
	$zstd = isset($probes['zstd']) ? $probes['zstd'] : null;
	$br1 = isset($probes['brotli_q1']) ? $probes['brotli_q1'] : null;
	$xz1 = isset($probes['xz1']) ? $probes['xz1'] : null;

	$lzCandidates = [];
	if (is_int($br11) && $br11 > 0) {
		$lzCandidates['brotli'] = $br11;
	}
	if (is_int($xz9) && $xz9 > 0) {
		$lzCandidates['xz'] = $xz9;
	}
	if (is_int($zstd) && $zstd > 0) {
		$lzCandidates['zstd'] = $zstd;
	}
	$lzBest = null;
	$lzBestName = null;
	if ($lzCandidates !== []) {
		$lzBest = min($lzCandidates);
		foreach ($lzCandidates as $nm => $sz) {
			if ($sz === $lzBest) {
				$lzBestName = $nm;
				break;
			}
		}
	}

	if (!$zpaqOk) {
		$reasons[] = 'zpaq not available on PATH (set FRACTAL_ZIP_ZPAQ); using best LZ probe.';
		if ($lzBestName !== null) {
			return ['outer' => $lzBestName, 'confidence' => 'medium', 'reasons' => $reasons, 'lz_best' => $lzBestName];
		}

		return ['outer' => 'gzip', 'confidence' => 'low', 'reasons' => array_merge($reasons, ['no LZ CLI probes; fallback gzip']), 'lz_best' => null];
	}

	if ($speed && $gr !== null && $gr >= 0.94) {
		$reasons[] = 'speed mode: high gzip ratio — prefer fast outer';
		if (is_int($zstd) && $zstd > 0) {
			return ['outer' => 'zstd', 'confidence' => 'medium', 'reasons' => $reasons, 'lz_best' => $lzBestName];
		}

		return ['outer' => 'gzip', 'confidence' => 'low', 'reasons' => $reasons, 'lz_best' => $lzBestName];
	}

	// Strong TEXT signal + brotli wins max-quality LZ trio (Squash-style): optional brotli focus
	if ($pr >= 0.88 && $lzBestName === 'brotli' && $br11 !== null && $xz9 !== null && $br11 < $xz9 * 0.995) {
		if (!empty($probes['prefer_lz_over_zpaq'])) {
			$reasons[] = 'text-like + brotli Q11 < xz -9 on probe — FRACTAL_ZIP_PREDICT_OUTER_LZ=1';

			return ['outer' => 'brotli', 'confidence' => 'medium', 'reasons' => $reasons, 'lz_best' => 'brotli'];
		}
	}

	// xz wins both probe tiers vs brotli (rare); favor xz for tournament shaping
	if (
		is_int($br1) && is_int($xz1) && is_int($br11) && is_int($xz9)
		&& $xz1 < $br1 && $xz9 < $br11 && $xz9 <= $br11 * 0.98
	) {
		if (!empty($probes['prefer_lz_over_zpaq'])) {
			$reasons[] = 'xz beats brotli at Q1 and max on sample — FRACTAL_ZIP_PREDICT_OUTER_LZ=1';

			return ['outer' => 'xz', 'confidence' => 'medium', 'reasons' => $reasons, 'lz_best' => 'xz'];
		}
	}

	// Default bytes-first prior (Squash hoplite: zpaq rows often smallest)
	$reasons[] = 'default bytes-first prior: zpaq (use --prefer-lz or FRACTAL_ZIP_PREDICT_OUTER_LZ=1 to bias brotli/xz from probes)';

	return ['outer' => 'zpaq', 'confidence' => 'high', 'reasons' => $reasons, 'lz_best' => $lzBestName];
}

/**
 * Env hints to steer fz toward predicted outer (best-effort).
 *
 * @return array<string, string>
 */
function predict_outer_env_hints(string $outer): array
{
	switch ($outer) {
		case 'zpaq':
			return ['FRACTAL_ZIP_FORCE_OUTER' => 'zpaq'];
		case 'gzip':
			return ['FRACTAL_ZIP_FORCE_OUTER' => 'gzip'];
		case 'brotli':
			return [
				'FRACTAL_ZIP_SKIP_ZPAQ' => '1',
				'FRACTAL_ZIP_SKIP_ZSTD' => '1',
				'FRACTAL_ZIP_SKIP_XZ' => '1',
				'FRACTAL_ZIP_SKIP_ARC' => '1',
				'FRACTAL_ZIP_SKIP_7Z' => '1',
				'FRACTAL_ZIP_FORCE_BROTLI' => '1',
			];
		case 'xz':
			return [
				'FRACTAL_ZIP_SKIP_ZPAQ' => '1',
				'FRACTAL_ZIP_SKIP_ZSTD' => '1',
				'FRACTAL_ZIP_SKIP_BROTLI' => '1',
				'FRACTAL_ZIP_SKIP_ARC' => '1',
				'FRACTAL_ZIP_SKIP_7Z' => '1',
			];
		case 'zstd':
			return [
				'FRACTAL_ZIP_SKIP_ZPAQ' => '1',
				'FRACTAL_ZIP_SKIP_BROTLI' => '1',
				'FRACTAL_ZIP_SKIP_XZ' => '1',
				'FRACTAL_ZIP_SKIP_ARC' => '1',
				'FRACTAL_ZIP_SKIP_7Z' => '1',
			];
		case 'bsc':
			return [];
		case '7z':
			return [];
		case 'arc':
			return ['FRACTAL_ZIP_FORCE_ARC' => '1'];
		default:
			return [];
	}
}

/**
 * Resolve BSC “bsc” CLI (Squash plugin). FRACTAL_ZIP_BSC overrides; else bundled benchmarks/.tools/bin/bsc; else PATH.
 */
function predict_outer_resolve_bsc_exe(): ?string
{
	static $memoDone = false;
	static $memo = null;
	if ($memoDone) {
		return $memo;
	}
	$memoDone = true;
	$e = getenv('FRACTAL_ZIP_BSC');
	if (is_string($e) && trim($e) !== '') {
		$p = trim($e);
		if (is_file($p) && @is_executable($p)) {
			return $memo = $p;
		}
	}
	$bundled = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.tools' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'bsc';
	if (is_file($bundled) && @is_executable($bundled)) {
		return $memo = $bundled;
	}
	$which = @shell_exec('command -v bsc 2>/dev/null');
	if (is_string($which)) {
		$w = trim($which);
		if ($w !== '' && is_file($w)) {
			return $memo = $w;
		}
	}

	return $memo = null;
}

/**
 * Run zpaq CLI on a file (same spirit as Squash zpaq rows); returns .zpaq archive size or null.
 *
 * @param int $method zpaq compression level 0–5 (layered probes use 1 / 3 / 5).
 */
function predict_outer_zpaq_archive_bytes(?string $zpaqExe, string $innerFile, float $timeoutSec, int $method = 5): ?int
{
	if ($zpaqExe === null || $zpaqExe === '' || !is_file($innerFile)) {
		return null;
	}
	$method = max(0, min(5, $method));
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_zpaq_probe_' . bin2hex(random_bytes(8));
	if (!@mkdir($tmp, 0700, true)) {
		return null;
	}
	$arc = $tmp . DIRECTORY_SEPARATOR . 'p.zpaq';
	$cmd = array_merge([$zpaqExe, 'a', $arc, $innerFile, '-method', (string) $method]);
	$des = [
		0 => ['pipe', 'r'],
		1 => ['pipe', 'w'],
		2 => ['pipe', 'w'],
	];
	$p = proc_open($cmd, $des, $pipes, $tmp);
	if (!is_resource($p)) {
		predict_outer_cleanup_probe_tmp($tmp);

		return null;
	}
	fclose($pipes[0]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	$t0 = microtime(true);
	while (true) {
		$st = proc_get_status($p);
		if (!$st['running']) {
			break;
		}
		if (microtime(true) - $t0 > $timeoutSec) {
			proc_terminate($p, 9);
			proc_close($p);
			predict_outer_cleanup_probe_tmp($tmp);

			return null;
		}
		usleep(50000);
	}
	proc_close($p);
	$sz = is_file($arc) ? filesize($arc) : false;
	predict_outer_cleanup_probe_tmp($tmp);

	return $sz !== false ? (int) $sz : null;
}

function predict_outer_cleanup_probe_tmp(string $tmpDir): void
{
	if (!is_dir($tmpDir)) {
		return;
	}
	try {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($tmpDir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($it as $item) {
			$p = $item->getPathname();
			if ($item->isDir()) {
				@rmdir($p);
			} else {
				@unlink($p);
			}
		}
		@rmdir($tmpDir);
	} catch (Throwable $e) {
		@rmdir($tmpDir);
	}
}

