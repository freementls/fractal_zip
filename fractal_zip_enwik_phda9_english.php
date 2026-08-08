<?php
declare(strict_types=1);

/**
 * phda9 English member codec — compress plain sorted Wikipedia XML/text the way phda9 expects.
 *
 * phda9 models English in page/XML/plain-text form, not FZTX binary inner. Use this module
 * to build payloads and roundtrip-safe FZPA wire blobs for member integration.
 *
 * Env:
 * - FRACTAL_ZIP_PAQ_PHDA9_DICT — external dictionary (see fractal_zip_phda9_dict.php)
 * - FRACTAL_ZIP_PAQ_PHDA9_DICT_SK — optional override dict for sk-only dual phda9 members
 * - FRACTAL_ZIP_PHDA9_ENGLISH_TOOL — phda9 | phda9_no_lstm (default phda9)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_tokenize.php';

function fractal_zip_enwik_phda9_english_tool_id(): string
{
	$e = getenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL');
	if ($e === false || trim((string) $e) === '') {
		return 'phda9';
	}
	$id = strtolower(trim((string) $e));
	return in_array($id, array('phda9', 'phda9_no_lstm', 'parallel_phda9'), true) ? $id : 'phda9';
}

/** phda9 / phda9_no_lstm: ~1 GiB RSS each; never fork N copies concurrently. */
function fractal_zip_enwik_phda9_english_heavy_tool(): bool
{
	$tool = fractal_zip_enwik_phda9_english_tool_id();
	return $tool === 'phda9' || $tool === 'phda9_no_lstm';
}

/**
 * Whether enwik phda9_xml chunk encode may pcntl_fork workers.
 * Heavy tools stay serial (one process). parallel_phda9 may fork when RAM allows.
 * FRACTAL_ZIP_PHDA9_ENGLISH_FORK_POOL=0 forces serial for all tools.
 */
function fractal_zip_enwik_phda9_english_fork_pool_allowed(): bool
{
	if (fractal_zip_enwik_phda9_english_heavy_tool()) {
		return false;
	}
	$v = getenv('FRACTAL_ZIP_PHDA9_ENGLISH_FORK_POOL');
	if ($v !== false && in_array(strtolower(trim((string) $v)), array('0', 'off', 'false', 'no'), true)) {
		return false;
	}
	return true;
}

/**
 * Build candidate English payloads from entry-sorted page refs.
 *
 * @param list<array{origIndex: int, start: int, len: int}> $sortedChunk
 * @return array<string, string>
 */
function fractal_zip_enwik_phda9_english_payloads_from_refs(array $sortedChunk, string $blob): array
{
	$pageXml = '';
	$articleText = '';
	foreach ($sortedChunk as $p) {
		$page = substr($blob, (int) $p['start'], (int) $p['len']);
		$pageXml .= $page;
		$articleText .= fractal_zip_enwik_extract_page_preserve_text($page);
	}
	$split = enwik_split_page_refs($blob);
	$header = is_array($split) ? (string) $split['header'] : '';
	$footer = is_array($split) ? (string) $split['footer'] : '';
	$sortedSlice = $header . $pageXml . $footer;

	// Raw-order slice for reference (what phda9 squash uses).
	$rawPageXml = '';
	if (is_array($split)) {
		$pages = $split['pages'];
		$limit = count($sortedChunk);
		for ($i = 0; $i < $limit; $i++) {
			$rawPageXml .= substr($blob, (int) $pages[$i]['start'], (int) $pages[$i]['len']);
		}
	}
	$rawSlice = $header . $rawPageXml . $footer;

	return array(
		'sorted_page_xml' => $pageXml,
		'sorted_article_text' => $articleText,
		'sorted_slice_xml' => $sortedSlice,
		'raw_slice_xml' => $rawSlice,
	);
}

/**
 * @param array{tool?: string, use_dict?: bool, dict_path?: string, timeout_sec?: int, wire_wrap?: bool} $opts
 * @return array{bytes: ?int, payload: ?string, roundtrip_ok: bool, seconds: float, tool: string, status: string}
 */
function fractal_zip_enwik_phda9_english_compress(string $plain, array $opts = array()): array
{
	$tool = (string) ($opts['tool'] ?? fractal_zip_enwik_phda9_english_tool_id());
	$useDict = !empty($opts['use_dict']);
	$timeout = (int) ($opts['timeout_sec'] ?? 0);
	$wireWrap = array_key_exists('wire_wrap', $opts) ? (bool) $opts['wire_wrap'] : true;
	$prevDictEnv = getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
	$dictOverride = isset($opts['dict_path']) ? trim((string) $opts['dict_path']) : '';

	if ($plain === '') {
		return array('bytes' => 0, 'payload' => '', 'roundtrip_ok' => true, 'seconds' => 0.0, 'tool' => $tool, 'status' => 'empty');
	}

	if (!function_exists('fractal_zip_enwik_recursive_remove')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	}

	$exe = fractal_zip_paq_discover_executable($tool);
	if ($exe === null) {
		return array('bytes' => null, 'payload' => null, 'roundtrip_ok' => false, 'seconds' => 0.0, 'tool' => $tool, 'status' => 'unavailable');
	}

	if ($dictOverride !== '') {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dictOverride);
		$useDict = true;
	} elseif ($useDict) {
		$dict = fractal_zip_paq_phda9_dict_path();
		if ($dict !== null) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		}
	} elseif (getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT') !== false && trim((string) getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT')) !== '') {
		$useDict = true;
	}

	if ($timeout > 0) {
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=' . (string) $timeout);
	}

	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_phda9_eng_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$in = $tmp . DIRECTORY_SEPARATOR . 'plain.in';
	$prep = fractal_zip_phda9_dict_inline_prepare_plain($plain);
	$plainForPaq = (string) $prep['plain'];
	$inlineTok = !empty($prep['tokenized']);
	$inlineDict = !empty($prep['temp_dict']) && !empty($prep['dict_path']);
	if ($inlineTok) {
		$tool = fractal_zip_phda9_dict_inline_resolve_tool($tool);
		$exe = fractal_zip_paq_discover_executable($tool);
		if ($exe === null) {
			return array('bytes' => null, 'payload' => null, 'roundtrip_ok' => false, 'seconds' => 0.0, 'tool' => $tool, 'status' => 'unavailable');
		}
	}
	$prevInlineActive = getenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_ACTIVE');
	if ($inlineTok || $inlineDict) {
		putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_ACTIVE=1');
	}
	if ($inlineTok) {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
	} elseif ($inlineDict) {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . (string) $prep['dict_path']);
	}
	file_put_contents($in, $plainForPaq);

	$t0 = microtime(true);
	$r = fractal_zip_paq_compress_file($tool, $exe, $in);
	$sec = round(microtime(true) - $t0, 3);

	$arc = $r['bytes'] ?? null;
	$rt = false;
	if (is_string($arc) && $arc !== '') {
		$out = $tmp . DIRECTORY_SEPARATOR . 'plain.out';
		$decOk = fractal_zip_enwik_phda9_english_decompress_to_file($tool, $exe, $arc, $out);
		$got = is_file($out) ? (string) file_get_contents($out) : '';
		if ($inlineTok && $decOk) {
			$got = fractal_zip_phda9_dict_inline_restore_plain($got);
		}
		$rt = $decOk && hash_equals($plain, $got);
		if (!$rt && PHP_SAPI === 'cli' && is_resource(STDERR)) {
			@fwrite(STDERR, '[phda9] RT fail tool=' . $tool . ' arc=' . strlen($arc)
				. ' dec=' . ($decOk ? 'ok' : 'no') . ' out_len=' . strlen($got)
				. ' plain_len=' . strlen($plain) . ' inline_tok=' . ($inlineTok ? '1' : '0') . "\n");
		}
	} elseif (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		@fwrite(STDERR, '[phda9] compress failed tool=' . $tool . ' plain_len=' . strlen($plain) . "\n");
	}

	$payload = $arc;
	if ($rt && $wireWrap && is_string($arc)) {
		$payload = fractal_zip_text_paq_wire_wrap($tool, $arc, strlen($plainForPaq));
	}

	fractal_zip_enwik_recursive_remove($tmp);

	if ($inlineTok || $inlineDict) {
		if ($prevInlineActive === false) {
			putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_ACTIVE');
		} else {
			putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_ACTIVE=' . (string) $prevInlineActive);
		}
	}
	if ($inlineTok || $inlineDict) {
		if ($prevDictEnv === false) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
		} else {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . (string) $prevDictEnv);
		}
	}

	if ($dictOverride !== '') {
		if ($prevDictEnv === false) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
		} else {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . (string) $prevDictEnv);
		}
	}

	$out = array(
		'bytes' => is_string($payload) ? strlen($payload) : null,
		'payload' => is_string($payload) ? $payload : null,
		'roundtrip_ok' => $rt,
		'seconds' => $sec,
		'tool' => $tool,
		'status' => is_string($arc) ? 'ok' : 'failed',
	);
	if (!$rt && $tool === 'phda9' && empty($opts['no_fallback'])
		&& fractal_zip_paq_discover_executable('phda9_no_lstm') !== null) {
		if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
			@fwrite(STDERR, '[phda9] fallback phda9_no_lstm plain_len=' . strlen($plain) . "\n");
		}
		$retry = fractal_zip_enwik_phda9_english_compress($plain, $opts + array(
			'tool' => 'phda9_no_lstm',
			'no_fallback' => true,
		));
		if (!empty($retry['roundtrip_ok'])) {
			return $retry;
		}
	}
	return $out;
}

function fractal_zip_enwik_phda9_english_decompress_to_file(string $tool, string $exe, string $arcBytes, string $outPath): bool
{
	if (fractal_zip_phda9_dict_inline_enabled() && fractal_zip_phda9_dict_inline_mode() === 'tokenize') {
		$tool = fractal_zip_phda9_dict_inline_resolve_tool($tool);
		$exe = fractal_zip_paq_discover_executable($tool) ?? $exe;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_phda9_dec_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$arcPath = $tmp . DIRECTORY_SEPARATOR . 'arc.paq';
	$localOut = $tmp . DIRECTORY_SEPARATOR . 'plain.out';
	file_put_contents($arcPath, $arcBytes);
	$prevDictEnvDec = getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
	if (fractal_zip_phda9_dict_inline_enabled()) {
		putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_ACTIVE=1');
		if (fractal_zip_phda9_dict_inline_mode() === 'temp') {
			$words = fractal_zip_phda9_dict_inline_cache_load();
			if ($words === array()) {
				$words = fractal_zip_phda9_dict_inline_vocab('');
			}
			if ($words !== array()) {
				putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . fractal_zip_phda9_dict_inline_temp_path($words));
			}
		} elseif (fractal_zip_phda9_dict_inline_mode() === 'tokenize') {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
		}
	}
	$ok = fractal_zip_paq_decompress_to_file($tool, $exe, $arcPath, $localOut);
	if ($ok && is_file($localOut)) {
		$raw = (string) file_get_contents($localOut);
		if (fractal_zip_phda9_dict_inline_active() || fractal_zip_phda9_dict_inline_enabled()) {
			$raw = fractal_zip_phda9_dict_inline_restore_plain($raw);
			file_put_contents($localOut, $raw);
		}
		$outDir = dirname($outPath);
		if ($outDir !== '' && $outDir !== '.' && !is_dir($outDir)) {
			@mkdir($outDir, 0700, true);
		}
		if ($localOut !== $outPath) {
			$ok = @rename($localOut, $outPath);
			if (!$ok) {
				$ok = @copy($localOut, $outPath);
				if ($ok) {
					@unlink($localOut);
				}
			}
		}
	}
	if ($prevDictEnvDec === false) {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
	} else {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . (string) $prevDictEnvDec);
	}
	if (is_dir($tmp)) {
		if (!function_exists('fractal_zip_enwik_recursive_remove')) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
		}
		fractal_zip_enwik_recursive_remove($tmp);
	}
	return $ok && is_file($outPath) && filesize($outPath) > 0;
}

/** Undo FZPA wire blob produced by {@see fractal_zip_enwik_phda9_english_compress()}. */
function fractal_zip_enwik_phda9_english_wire_undo(string $wire): string
{
	return fractal_zip_text_paq_wire_undo($wire);
}

function fractal_zip_enwik_phda9_english_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_MEMBER');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'true' || $v === 'on' || $v === 'yes';
}

/**
 * Build sorted page-XML payload for one text-inner chunk (pages already layout-ordered).
 *
 * @param list<array{start: int, len: int}> $pageRefs
 */
function fractal_zip_enwik_phda9_english_chunk_page_xml(array $pageRefs, string $blob): string
{
	$out = '';
	foreach ($pageRefs as $p) {
		$out .= substr($blob, (int) $p['start'], (int) $p['len']);
	}
	return $out;
}

/**
 * Full page XML with preprocessed wire text injected, in sorted chunk order (phda9_xml + preprocess).
 *
 * @param list<array{shell?: string, text?: string, wire_text?: string}> $chunkSplit
 * @return array{page_xml: string, page_lens: list<int>}
 */
function fractal_zip_enwik_phda9_english_chunk_preprocessed_page_xml(array $chunkSplit): array
{
	$pageXmlBuf = '';
	$pageLens = array();
	foreach ($chunkSplit as $pg) {
		if (!is_array($pg)) {
			continue;
		}
		$pageXml = fractal_zip_enwik_inject_text_into_shell_page(
			(string) ($pg['shell'] ?? ''),
			(string) ($pg['wire_text'] ?? $pg['text'] ?? '')
		);
		$pageXmlBuf .= $pageXml;
		$pageLens[] = strlen($pageXml);
	}
	return array('page_xml' => $pageXmlBuf, 'page_lens' => $pageLens);
}

/**
 * Build sorted preserve-text payload for one text-inner chunk (layout-ordered article bodies).
 *
 * @param list<array{title: string, origIndex: int, shell: string, text: string, text_chars: int, wire_text?: string}> $chunkSplit
 */
function fractal_zip_enwik_phda9_english_chunk_article_text(array $chunkSplit, string $layoutTextBlob): string
{
	if ($layoutTextBlob !== '') {
		return $layoutTextBlob;
	}
	$out = '';
	foreach ($chunkSplit as $pg) {
		$out .= (string) ($pg['wire_text'] ?? $pg['text'] ?? '');
	}
	return $out;
}

/** Estimated RSS per phda9_no_lstm worker (MiB). Override: FRACTAL_ZIP_PHDA9_ENGLISH_WORKER_MB */
function fractal_zip_enwik_phda9_english_worker_rss_mb(): int
{
	$e = getenv('FRACTAL_ZIP_PHDA9_ENGLISH_WORKER_MB');
	if ($e !== false && trim((string) $e) !== '') {
		return max(800, (int) $e);
	}
	return 1000;
}

function fractal_zip_enwik_phda9_english_cpu_count(): int
{
	$e = getenv('FRACTAL_ZIP_PHDA9_ENGLISH_CPU_CAP');
	if ($e !== false && trim((string) $e) !== '') {
		return max(1, (int) $e);
	}
	if (is_readable('/proc/cpuinfo')) {
		$n = (int) shell_exec('nproc 2>/dev/null') ?: 0;
		if ($n > 0) {
			return $n;
		}
	}
	return 4;
}

/** Linux MemAvailable in MiB, or null when unknown. */
function fractal_zip_enwik_phda9_english_mem_available_mb(): ?int
{
	if (!is_readable('/proc/meminfo')) {
		return null;
	}
	$raw = @file_get_contents('/proc/meminfo');
	if (!is_string($raw) || $raw === '') {
		return null;
	}
	foreach (explode("\n", $raw) as $line) {
		if (str_starts_with($line, 'MemAvailable:')) {
			$kb = (int) preg_replace('/\D/', '', $line);
			return $kb > 0 ? (int) floor($kb / 1024) : null;
		}
	}
	return null;
}

/**
 * Parallel phda9 chunk workers — explicit env capped by free RAM (never inherit shootout jobs).
 *
 * Env:
 * - FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS — requested workers (default: auto from MemAvailable)
 * - FRACTAL_ZIP_PHDA9_ENGLISH_RESERVE_MB — headroom for PHP/outer (default 768)
 * - FRACTAL_ZIP_PHDA9_ENGLISH_CPU_CAP — max CPU workers (default: nproc)
 */
function fractal_zip_enwik_phda9_english_jobs(int $chunkCount = 1): int
{
	$chunkCount = max(1, $chunkCount);
	if (!fractal_zip_enwik_phda9_english_fork_pool_allowed()) {
		return 1;
	}
	$workerMb = fractal_zip_enwik_phda9_english_worker_rss_mb();
	$reserveMb = (int) (getenv('FRACTAL_ZIP_PHDA9_ENGLISH_RESERVE_MB') ?: 768);
	$cpuCap = fractal_zip_enwik_phda9_english_cpu_count();
	$memCap = 1;
	$avail = fractal_zip_enwik_phda9_english_mem_available_mb();
	if ($avail !== null && $avail > $reserveMb + $workerMb) {
		$memCap = max(1, (int) floor(($avail - $reserveMb) / $workerMb));
	}
	$e = getenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS');
	$requested = ($e === false || trim((string) $e) === '') ? min($memCap, $cpuCap) : max(1, (int) $e);
	$jobs = min($requested, $memCap, $cpuCap, $chunkCount);
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		@fwrite(STDERR, '[phda9] jobs=' . $jobs . ' chunks=' . $chunkCount
			. ' avail=' . ($avail ?? '?') . 'MiB worker=' . $workerMb
			. 'MiB reserve=' . $reserveMb . 'MiB cpu=' . $cpuCap . "\n");
	}
	return max(1, min(16, $jobs));
}

/**
 * Compress deferred phda9_xml chunks in a fork pool (one phda9 process per worker).
 *
 * @param list<array{chunkIdx: int, innerFull: string, plainBuf: string, stackId?: string}> $pending
 * @return array<int, array{tool: string, bytes: int, seconds: float}>
 */
function fractal_zip_enwik_phda9_english_parallel_apply(array $pending, int $jobs): array
{
	// Re-read MemAvailable — chunk payloads may have consumed RAM since encode began.
	$jobs = fractal_zip_enwik_phda9_english_jobs(count($pending));
	if (!fractal_zip_enwik_phda9_english_fork_pool_allowed()) {
		$jobs = 1;
	}
	$results = array();
	$canFork = PHP_SAPI === 'cli' && PHP_OS_FAMILY !== 'Windows'
		&& function_exists('pcntl_fork') && function_exists('pcntl_waitpid');
	$runOne = static function (array $p): array {
		$shootTimeout = (int) (getenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC') ?: 0);
		if ($shootTimeout <= 0) {
			$shootTimeout = (int) (getenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC') ?: 0);
		}
		$cr = fractal_zip_enwik_phda9_english_compress_auto((string) $p['plainBuf'], array(
			'wire_wrap' => true,
			'timeout_sec' => $shootTimeout > 0 ? $shootTimeout : 0,
		));
		if (empty($cr['roundtrip_ok']) || !is_string($cr['payload']) || $cr['payload'] === '') {
			throw new RuntimeException('phda9_xml parallel compress failed chunk ' . $p['chunkIdx']);
		}
		$innerBlob = (string) $cr['payload'];
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_member_shootout.php';
		$stackId = (string) ($p['stackId'] ?? 'none');
		$outerR = fractal_zip_enwik_phda9_inner_outer_apply($innerBlob, $stackId);
		$innerBlob = (string) $outerR['payload'];
		if (file_put_contents((string) $p['innerFull'], $innerBlob) === false) {
			throw new RuntimeException('phda9_xml parallel write failed chunk ' . $p['chunkIdx']);
		}
		return array(
			'tool' => (string) ($cr['tool'] ?? 'phda9'),
			'bytes' => strlen($innerBlob),
			'seconds' => (float) ($cr['seconds'] ?? 0.0),
		);
	};
	if ($jobs <= 1 || count($pending) <= 1 || !$canFork) {
		foreach ($pending as $p) {
			$r = $runOne($p);
			$results[(int) $p['chunkIdx']] = $r;
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] phda9_xml chunk ' . $p['chunkIdx'] . ': ' . $r['tool']
					. ' ' . number_format($r['bytes']) . ' B plain='
					. number_format(strlen((string) $p['plainBuf'])) . ' sec=' . $r['seconds'] . "\n");
				@fflush(STDERR);
			}
		}
		return $results;
	}
	$tmpBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_phda9_par_' . bin2hex(random_bytes(4));
	@mkdir($tmpBase, 0700, true);
	$children = array();
	$stripes = min($jobs, count($pending));
	for ($w = 0; $w < $stripes; $w++) {
		$pid = pcntl_fork();
		if ($pid === -1) {
			for ($sw = $w; $sw < $stripes; $sw++) {
				for ($k = $sw; $k < count($pending); $k += $stripes) {
					$p = $pending[$k];
					$r = $runOne($p);
					file_put_contents(
						$tmpBase . DIRECTORY_SEPARATOR . $p['chunkIdx'] . '.json',
						json_encode($r)
					);
				}
			}
			break;
		}
		if ($pid === 0) {
			for ($k = $w; $k < count($pending); $k += $stripes) {
				$p = $pending[$k];
				$r = $runOne($p);
				file_put_contents(
					$tmpBase . DIRECTORY_SEPARATOR . $p['chunkIdx'] . '.json',
					json_encode($r)
				);
				if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
					@fwrite(STDERR, '[enwik] phda9_xml chunk ' . $p['chunkIdx'] . ': ' . $r['tool']
						. ' ' . number_format($r['bytes']) . ' B (w' . $w . ")\n");
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
		if (!is_array($r) || !is_file((string) $p['innerFull'])) {
			throw new RuntimeException('phda9_xml parallel: missing result for chunk ' . $ci);
		}
		$results[$ci] = array(
			'tool' => (string) ($r['tool'] ?? 'phda9'),
			'bytes' => (int) ($r['bytes'] ?? 0),
			'seconds' => (float) ($r['seconds'] ?? 0.0),
		);
	}
	if (!function_exists('fractal_zip_enwik_recursive_remove')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	}
	fractal_zip_enwik_recursive_remove($tmpBase);
	return $results;
}

/** General-usage fast inner: mined tokenize + zpaq (no phda9 process startup). */
function fractal_zip_enwik_phda9_general_fast_inner_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_PHDA9_GENERAL_FAST');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	return !in_array(strtolower(trim((string) $e)), array('0', 'off', 'false', 'no'), true);
}

function fractal_zip_enwik_phda9_general_zpaq_method(): int
{
	$e = getenv('FRACTAL_ZIP_GENERAL_TEXT_ZPAQ_METHOD');
	if ($e !== false && trim((string) $e) !== '' && ctype_digit(trim((string) $e))) {
		return max(1, min(9, (int) trim((string) $e)));
	}
	return 5;
}

/**
 * Compress plain prose via shipped-dict tokenize + zpaq (fast; no phda9 startup, no mined vocab blob).
 *
 * @param array{wire_wrap?: bool} $opts
 * @return array{bytes: ?int, payload: ?string, roundtrip_ok: bool, seconds: float, tool: string, status: string}
 */
function fractal_zip_enwik_phda9_english_compress_tokenized_zpaq(string $plain, array $opts = array()): array
{
	$wireWrap = !array_key_exists('wire_wrap', $opts) || (bool) $opts['wire_wrap'];
	if ($plain === '') {
		return array('bytes' => 0, 'payload' => '', 'roundtrip_ok' => true, 'seconds' => 0.0, 'tool' => 'tokenized_zpaq', 'status' => 'empty');
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_tokenize.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
	if (!function_exists('fractal_zip_enwik_recursive_remove')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	}
	$t0 = microtime(true);
	$zpaqMethod = fractal_zip_enwik_phda9_general_zpaq_method();
	$pick = fractal_zip_tokenized_zpaq_pick_compress($plain, $zpaqMethod);
	if ($pick['arc_bytes'] === '') {
		return array('bytes' => null, 'payload' => null, 'roundtrip_ok' => false, 'seconds' => round(microtime(true) - $t0, 3), 'tool' => 'tokenized_zpaq', 'status' => (string) $pick['vocab_source']);
	}
	// Arc codec picked by the shootout (zpaqN or lpaq9l); drives wire tool id and RT decode.
	$toolId = (string) ($pick['tool_id'] ?? ('zpaq' . (string) $zpaqMethod));
	$words = $pick['words'];
	$tokenized = (bool) $pick['tokenized'];
	$compressPlain = (string) $pick['compress_plain'];
	if ($tokenized && $words !== array()) {
		fractal_zip_phda9_dict_inline_cache_store($words);
	}
	unset($GLOBALS['fractal_zip_general_text_token_vocab_blob']);
	unset($GLOBALS['fractal_zip_general_text_vocab_source']);
	unset($GLOBALS['fractal_zip_general_text_vocab_delta']);
	$src = (string) $pick['vocab_source'];
	$GLOBALS['fractal_zip_general_text_vocab_source'] = $src;
	if ($src === 'frozen' || str_starts_with($src, 'frozen')) {
		$GLOBALS['fractal_zip_general_text_vocab_frozen'] = true;
	}
	if ($src === 'frozen+delta') {
		$GLOBALS['fractal_zip_general_text_vocab_delta'] = true;
	}
	$rt = false;
	$rtDeferred = false;
	$isCm = in_array($toolId, array('lpaq9l', 'mcm', 'drt_lpaq9l', 'drt_lpaq9lp', 'drt_lpaq9l_seg', 'drt_lpaq9lp_seg'), true);
	$skipZpaqRt = !$isCm && fractal_zip_tokenized_zpaq_skip_zpaq_rt($plain, $tokenized);
	// lpaq/mcm/drt archives carry no trusted internal checksum (zpaq does), so always decode-verify them.
	$tokRtOnly = $tokenized && !$isCm && fractal_zip_tokenized_zpaq_tok_rt_only();
	$dec = null;
	if (!$skipZpaqRt && !$tokRtOnly && $isCm
		&& fractal_zip_paq_deferred_rt_begin($toolId, (string) $pick['arc_bytes'], hash('sha256', $compressPlain), strlen($plain))) {
		// Large-payload CM verify: the arc→stream decode runs in a background
		// worker (joined before the archive is accepted; see
		// fractal_zip_paq_deferred_rt_join_or_fail) so it overlaps the outer
		// wrap. The tokenize layer — when present — is still verified here:
		// the worker only proves arc bytes decode back to $compressPlain.
		$rt = $tokenized ? hash_equals($plain, fractal_zip_phda9_detokenize($compressPlain, $words)) : true;
		$rtDeferred = $rt;
	} elseif (!$skipZpaqRt && !$tokRtOnly) {
		if ($toolId === 'lpaq9l') {
			$dec = fractal_zip_paq_lpaq_decompress_bytes((string) $pick['arc_bytes']);
		} elseif ($toolId === 'drt_lpaq9l' || $toolId === 'drt_lpaq9lp') {
			$dec = fractal_zip_paq_drt_lpaq_decompress_bytes((string) $pick['arc_bytes'], $toolId === 'drt_lpaq9lp');
		} elseif ($toolId === 'drt_lpaq9l_seg' || $toolId === 'drt_lpaq9lp_seg') {
			$dec = fractal_zip_paq_drt_lpaq_seg_decompress_bytes((string) $pick['arc_bytes'], $toolId === 'drt_lpaq9lp_seg');
		} elseif ($toolId === 'mcm') {
			$dec = fractal_zip_paq_mcm_decompress_bytes((string) $pick['arc_bytes']);
		} else {
			$dec = fractal_zip_paq_zpaq_decompress_bytes((string) $pick['arc_bytes'], $toolId);
		}
	}
	if ($tokRtOnly) {
		// Detokenize the pre-compression token stream: tokenization is the only
		// lossy-risk step (zpaq decode is checksummed); skips a full zpaq decompress.
		$rest = fractal_zip_phda9_detokenize($compressPlain, $words);
		$rt = hash_equals($plain, $rest);
	} elseif ($skipZpaqRt) {
		$rt = true;
	} elseif (is_string($dec) && $dec !== '') {
		if ($tokenized) {
			$rest = fractal_zip_phda9_detokenize($dec, $words);
			$rt = hash_equals($plain, $rest);
		} else {
			$rt = hash_equals($plain, $dec);
		}
	}
	$payload = (string) $pick['arc_bytes'];
	$vocabTail = is_string($pick['vocab_tail'] ?? null) && $pick['vocab_tail'] !== '' ? $pick['vocab_tail'] : null;
	if ($rt && $wireWrap) {
		$payload = fractal_zip_text_paq_wire_wrap($toolId, $payload, strlen($compressPlain), $vocabTail);
	}
	$sec = round(microtime(true) - $t0, 3);
	$status = $rt ? ($tokenized ? ($src === 'raw_zpaq' ? 'raw_zpaq' : 'ok') : 'raw_zpaq') : 'rt_failed';
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		$rtTag = $skipZpaqRt ? 'skipped_large_raw' : ($rtDeferred ? 'deferred' : ($rt ? 'ok' : 'fail'));
		@fwrite(STDERR, '[general-text] tokenized_zpaq vocab=' . $src
			. ' words=' . ($tokenized ? count($words) : 0)
			. ($pick['delta_words'] > 0 ? ' delta=' . (int) $pick['delta_words'] : '')
			. ' tok=' . number_format(strlen($compressPlain))
			. ' arc=' . number_format(strlen((string) $pick['arc_bytes']))
			. ' codec=' . $toolId
			. ($vocabTail !== null ? ' vocab_tail=' . number_format(strlen((string) $vocabTail)) : '')
			. ' sec=' . $sec . ' rt=' . $rtTag . "\n");
	}
	return array(
		'bytes' => is_string($payload) ? strlen($payload) : null,
		'payload' => is_string($payload) ? $payload : null,
		'roundtrip_ok' => $rt,
		'seconds' => $sec,
		'tool' => 'tokenized_zpaq',
		'status' => $status,
	);
}

/**
 * Bytes-first phda9 pick: optional fast parallel_phda9 probe, then phda9_no_lstm when parallel is larger.
 *
 * @param array{tool?: string, use_dict?: bool, dict_path?: string, timeout_sec?: int, wire_wrap?: bool} $opts
 * @return array{bytes: ?int, payload: ?string, roundtrip_ok: bool, seconds: float, tool: string, status: string}
 */
function fractal_zip_enwik_phda9_english_compress_auto(string $plain, array $opts = array()): array
{
	if (fractal_zip_enwik_phda9_general_fast_inner_enabled()) {
		return fractal_zip_enwik_phda9_english_compress_tokenized_zpaq($plain, $opts);
	}
	$tool = (string) ($opts['tool'] ?? fractal_zip_enwik_phda9_english_tool_id());
	$probe = getenv('FRACTAL_ZIP_PHDA9_FAST_PROBE');
	if ($probe !== false && in_array(strtolower(trim((string) $probe)), array('0', 'off', 'false', 'no'), true)) {
		return fractal_zip_enwik_phda9_english_compress($plain, $opts);
	}
	if ($tool !== 'phda9_no_lstm' && $tool !== 'phda9') {
		return fractal_zip_enwik_phda9_english_compress($plain, $opts);
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
	if (fractal_zip_paq_discover_executable('parallel_phda9') === null) {
		return fractal_zip_enwik_phda9_english_compress($plain, $opts);
	}
	$par = fractal_zip_enwik_phda9_english_compress($plain, $opts + array(
		'tool' => 'parallel_phda9',
		'no_fallback' => true,
	));
	if (!empty($par['roundtrip_ok']) && is_string($par['payload']) && $par['payload'] !== '') {
		$gz = @gzdeflate($plain, 9);
		$gzFloor = is_string($gz) && $gz !== '' ? strlen($gz) : strlen($plain);
		$parBytes = strlen($par['payload']);
		$margin = getenv('FRACTAL_ZIP_PHDA9_PARALLEL_MAX_BYTES_MARGIN');
		$maxMul = ($margin !== false && trim((string) $margin) !== '' && is_numeric(trim((string) $margin)))
			? max(1.0, (float) trim((string) $margin))
			: 1.08;
		if ($parBytes <= (int) ceil($gzFloor * $maxMul)) {
			return $par;
		}
	}
	return fractal_zip_enwik_phda9_english_compress($plain, $opts + array(
		'tool' => 'phda9_no_lstm',
		'no_fallback' => true,
	));
}
