<?php
declare(strict_types=1);

/**
 * Per-chunk codec shootout on fz-transformed text-inner bytes (384p gate / opt-in production).
 *
 * Env:
 * - FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT — unset=off; 1=on (lab + slice probes)
 * - FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_STACKS — colon list (default zpaq9_brotli11:zpaq9_zstd22:zpaq9_gzip9:gzip9_zpaq9:brotli11_gzip9)
 * - FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_MODELS — colon list (default zpaq9:phda9)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';

function fractal_zip_enwik_member_codec_shootout_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'true' || $v === 'on' || $v === 'yes';
}

/** @return list<string> */
function fractal_zip_enwik_member_shootout_stack_ids(): array
{
	$e = getenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_STACKS');
	$raw = ($e === false || trim((string) $e) === '')
		? 'zpaq9_brotli11:zpaq9_zstd22:zpaq9_gzip9:gzip9_zpaq9:brotli11_gzip9:zpaq9_7z'
		: (string) $e;
	$out = array();
	foreach (preg_split('/[:;,]/', $raw) ?: array() as $p) {
		$p = strtolower(trim((string) $p));
		if ($p !== '' && $p !== 'none') {
			$out[] = $p;
		}
	}
	return $out;
}

/** gzip-1 proxy for how zpaq outer will treat inner bytes (better than raw strlen). */
function fractal_zip_enwik_member_shootout_score_bytes(string $payload): int
{
	$gz = @gzdeflate($payload, 1);
	return is_string($gz) && $gz !== '' ? strlen($gz) : strlen($payload);
}

/** @return list<string> */
function fractal_zip_enwik_member_shootout_model_ids(): array
{
	$e = getenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_MODELS');
	$raw = ($e === false || trim((string) $e) === '') ? '' : (string) $e;
	if ($raw === '') {
		return array();
	}
	$out = array();
	foreach (preg_split('/[:;,]/', $raw) ?: array() as $p) {
		$p = strtolower(trim((string) $p));
		if ($p !== '') {
			$out[] = $p;
		}
	}
	return $out;
}

/**
 * Try shootout candidates; return smallest roundtrip-safe payload.
 *
 * @return array{payload: string, pick: string, bytes: int, candidates: list<array<string, mixed>>}
 */
function fractal_zip_enwik_member_shootout_pick(string $innerBlob): array
{
	$baseline = array(
		'payload' => $innerBlob,
		'pick' => 'raw',
		'bytes' => strlen($innerBlob),
		'candidates' => array(),
	);
	if (!fractal_zip_enwik_member_codec_shootout_enabled()) {
		return $baseline;
	}
	$candidates = array();
	$try = static function (string $pick, string $payload, bool $rt) use (&$candidates): void {
		$candidates[] = array(
			'pick' => $pick,
			'bytes' => strlen($payload),
			'roundtrip_ok' => $rt,
		);
	};
	$best = $innerBlob;
	$bestPick = 'raw';
	$bestLen = strlen($innerBlob);
	$try('raw', $innerBlob, true);

	foreach (fractal_zip_enwik_member_shootout_stack_ids() as $stackId) {
		try {
			$r = fractal_zip_text_stacked_outer_apply($stackId, $innerBlob);
			$payload = (string) ($r['payload'] ?? '');
			$rt = !empty($r['roundtrip_ok']);
			$try('stack:' . $stackId, $payload, $rt);
			if ($rt && strlen($payload) < $bestLen) {
				$bestLen = strlen($payload);
				$best = $payload;
				$bestPick = 'stack:' . $stackId;
			}
		} catch (Throwable $e) {
			$try('stack:' . $stackId, '', false);
		}
	}

	$maxModelBytes = (int) (getenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_MAX_BYTES') ?: 8388608);
	$models = fractal_zip_enwik_member_shootout_model_ids();
	if ($models !== array() && ($maxModelBytes <= 0 || strlen($innerBlob) <= $maxModelBytes)) {
		$bestModelScore = fractal_zip_enwik_member_shootout_score_bytes($best);
		$shootTimeout = (int) (getenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC') ?: 600);
		$runModel = static function (string $modelId) use ($innerBlob, $shootTimeout): array {
			return fractal_zip_text_compressor_run($modelId, $innerBlob, array(
				'timeout_sec' => max(30, $shootTimeout),
				'wire_wrap' => true,
			));
		};
		$parallelModels = count($models) >= 2 && PHP_SAPI === 'cli' && PHP_OS_FAMILY !== 'Windows'
			&& function_exists('pcntl_fork') && function_exists('pcntl_waitpid')
			&& getenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_PARALLEL') !== '0';
		$modelResults = array();
		if ($parallelModels) {
			$tmpBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_shoot_' . bin2hex(random_bytes(4));
			@mkdir($tmpBase, 0700, true);
			$children = array();
			foreach ($models as $modelId) {
				$pid = pcntl_fork();
				if ($pid === -1) {
					$parallelModels = false;
					break;
				}
				if ($pid === 0) {
					$r = $runModel($modelId);
					file_put_contents($tmpBase . DIRECTORY_SEPARATOR . $modelId . '.json', json_encode($r));
					exit(0);
				}
				$children[] = $pid;
			}
			if ($parallelModels) {
				foreach ($children as $cpid) {
					pcntl_waitpid($cpid, $st);
				}
				foreach ($models as $modelId) {
					$jsonPath = $tmpBase . DIRECTORY_SEPARATOR . $modelId . '.json';
					if (!is_file($jsonPath)) {
						continue;
					}
					$r = json_decode((string) file_get_contents($jsonPath), true);
					if (is_array($r)) {
						$modelResults[$modelId] = $r;
					}
				}
				fractal_zip_enwik_recursive_remove($tmpBase);
			}
		}
		if (!$parallelModels) {
			foreach ($models as $modelId) {
				$modelResults[$modelId] = $runModel($modelId);
			}
		}
		foreach ($models as $modelId) {
			$r = $modelResults[$modelId] ?? array();
			$payload = is_string($r['payload'] ?? null) ? (string) $r['payload'] : '';
			$rt = !empty($r['roundtrip_ok']) && $payload !== '';
			$try('model:' . $modelId, $payload, $rt);
			if ($rt) {
				$score = fractal_zip_enwik_member_shootout_score_bytes($payload);
				if ($score < $bestModelScore) {
					$bestModelScore = $score;
					$best = $payload;
					$bestPick = 'model:' . $modelId;
					$bestLen = strlen($payload);
				}
			}
		}
	}

	return array(
		'payload' => $best,
		'pick' => $bestPick,
		'bytes' => $bestLen,
		'candidates' => $candidates,
	);
}

/**
 * Post-phda9 inner bytes: member shootout when enabled, else fixed TEXT_INNER_STACK.
 *
 * @return array{payload: string, pick: string, bytes: int}
 */
function fractal_zip_enwik_phda9_inner_outer_apply(string $innerBlob, string $stackId = 'none'): array
{
	if (fractal_zip_enwik_member_codec_shootout_enabled()) {
		$shoot = fractal_zip_enwik_member_shootout_pick($innerBlob);
		return array(
			'payload' => (string) ($shoot['payload'] ?? $innerBlob),
			'pick' => (string) ($shoot['pick'] ?? 'raw'),
			'bytes' => (int) ($shoot['bytes'] ?? strlen($innerBlob)),
		);
	}
	$stackId = strtolower(trim($stackId));
	if ($stackId !== '' && $stackId !== 'none') {
		$stackR = fractal_zip_text_stacked_outer_apply($stackId, $innerBlob);
		if (empty($stackR['roundtrip_ok'])) {
			throw new RuntimeException('phda9 inner stack roundtrip failed: ' . $stackId);
		}
		$payload = (string) ($stackR['payload'] ?? $innerBlob);
		return array(
			'payload' => $payload,
			'pick' => 'stack:' . $stackId,
			'bytes' => strlen($payload),
		);
	}
	return array(
		'payload' => $innerBlob,
		'pick' => 'raw',
		'bytes' => strlen($innerBlob),
	);
}

function fractal_zip_enwik_member_shootout_jobs(): int
{
	$e = getenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_JOBS');
	if ($e === false || trim((string) $e) === '') {
		return 1;
	}
	return max(1, min(16, (int) $e));
}

/**
 * Run shootout picks for many member chunks in a forked worker pool; each
 * worker writes its picked payload to the chunk's target path.
 *
 * @param list<array{chunkIdx: int, innerFull: string, innerBlob: string}> $pending
 * @return array<int, array{pick: string, bytes: int}> keyed by chunkIdx
 */
function fractal_zip_enwik_member_shootout_parallel_apply(array $pending, int $jobs): array
{
	$results = array();
	$canFork = PHP_SAPI === 'cli' && PHP_OS_FAMILY !== 'Windows'
		&& function_exists('pcntl_fork') && function_exists('pcntl_waitpid');
	if ($jobs <= 1 || count($pending) <= 1 || !$canFork) {
		foreach ($pending as $p) {
			$shoot = fractal_zip_enwik_member_shootout_pick((string) $p['innerBlob']);
			if (file_put_contents((string) $p['innerFull'], (string) $shoot['payload']) === false) {
				throw new RuntimeException('member shootout: write failed chunk ' . $p['chunkIdx']);
			}
			$results[(int) $p['chunkIdx']] = array(
				'pick' => (string) ($shoot['pick'] ?? '?'),
				'bytes' => (int) ($shoot['bytes'] ?? 0),
			);
		}
		return $results;
	}
	$tmpBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_shoot_par_' . bin2hex(random_bytes(4));
	@mkdir($tmpBase, 0700, true);
	$children = array();
	$stripes = min($jobs, count($pending));
	for ($w = 0; $w < $stripes; $w++) {
		$pid = pcntl_fork();
		if ($pid === -1) {
			// Fork failed: finish remaining stripes serially in parent.
			for ($sw = $w; $sw < $stripes; $sw++) {
				for ($k = $sw; $k < count($pending); $k += $stripes) {
					$p = $pending[$k];
					$shoot = fractal_zip_enwik_member_shootout_pick((string) $p['innerBlob']);
					file_put_contents((string) $p['innerFull'], (string) $shoot['payload']);
					file_put_contents(
						$tmpBase . DIRECTORY_SEPARATOR . $p['chunkIdx'] . '.json',
						json_encode(array('pick' => $shoot['pick'], 'bytes' => $shoot['bytes']))
					);
				}
			}
			break;
		}
		if ($pid === 0) {
			for ($k = $w; $k < count($pending); $k += $stripes) {
				$p = $pending[$k];
				$shoot = fractal_zip_enwik_member_shootout_pick((string) $p['innerBlob']);
				$ok = file_put_contents((string) $p['innerFull'], (string) $shoot['payload']) !== false;
				file_put_contents(
					$tmpBase . DIRECTORY_SEPARATOR . $p['chunkIdx'] . '.json',
					json_encode(array(
						'pick' => $ok ? (string) ($shoot['pick'] ?? '?') : 'write_failed',
						'bytes' => (int) ($shoot['bytes'] ?? 0),
					))
				);
				if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
					@fwrite(STDERR, '[enwik] member shootout chunk ' . $p['chunkIdx'] . ': '
						. ($shoot['pick'] ?? '?') . ' ' . number_format((int) ($shoot['bytes'] ?? 0)) . " B (w{$w})\n");
					@fflush(STDERR);
				}
			}
			exit(0);
		}
		$children[] = $pid;
	}
	foreach ($children as $cpid) {
		pcntl_waitpid($cpid, $st);
	}
	foreach ($pending as $p) {
		$ci = (int) $p['chunkIdx'];
		$jsonPath = $tmpBase . DIRECTORY_SEPARATOR . $ci . '.json';
		$r = is_file($jsonPath) ? json_decode((string) file_get_contents($jsonPath), true) : null;
		if (!is_array($r) || ($r['pick'] ?? '') === 'write_failed' || !is_file((string) $p['innerFull'])) {
			throw new RuntimeException('member shootout parallel: missing result for chunk ' . $ci);
		}
		$results[$ci] = array('pick' => (string) $r['pick'], 'bytes' => (int) $r['bytes']);
	}
	fractal_zip_enwik_recursive_remove($tmpBase);
	return $results;
}
