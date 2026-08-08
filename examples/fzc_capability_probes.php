<?php
declare(strict_types=1);

/**
 * Shared probes for local vs web fractal_zip capability parity (no CLI output on include).
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_deploy_bootstrap.php';
fzc_examples_require_bench_json_helpers();

/**
 * @return array<string, mixed>
 */
function fzc_cap_probe_php_runtime(string $label, bool $asWeb): array {
	$funcs = array('proc_open', 'proc_close', 'proc_get_status', 'exec', 'shell_exec', 'escapeshellarg', 'escapeshellcmd', 'popen', 'pcntl_fork');
	$fn = array();
	foreach ($funcs as $f) {
		$fn[$f] = function_exists($f);
	}
	$exts = array('zlib', 'mbstring', 'openssl', 'fileinfo', 'json', 'pcntl', 'zip');
	$ex = array();
	foreach ($exts as $e) {
		$ex[$e] = extension_loaded($e);
	}
	return array(
		'label' => $label,
		'as_web_simulation' => $asWeb,
		'sapi' => PHP_SAPI,
		'php_version' => PHP_VERSION,
		'os' => PHP_OS_FAMILY,
		'uname' => function_exists('php_uname') ? php_uname('a') : null,
		'memory_limit' => ini_get('memory_limit'),
		'max_execution_time' => ini_get('max_execution_time'),
		'upload_max_filesize' => ini_get('upload_max_filesize'),
		'post_max_size' => ini_get('post_max_size'),
		'disable_functions' => ini_get('disable_functions'),
		'open_basedir' => ini_get('open_basedir'),
		'functions' => $fn,
		'extensions' => $ex,
	);
}

/**
 * @return array{path: ?string, exists: bool, executable: bool, probe_ok: ?bool, probe_detail: ?string}
 */
function fzc_cap_probe_binary(?string $resolvedPath, ?callable $probe = null): array {
	$path = is_string($resolvedPath) && $resolvedPath !== '' ? $resolvedPath : null;
	$out = array(
		'path' => $path,
		'exists' => $path !== null && file_exists($path),
		'executable' => $path !== null && is_executable($path),
		'probe_ok' => null,
		'probe_detail' => null,
	);
	if ($path === null || !$out['executable'] || $probe === null) {
		return $out;
	}
	try {
		($probe)($path, $out);
	} catch (Throwable $e) {
		$out['probe_ok'] = false;
		$out['probe_detail'] = $e->getMessage();
	}
	return $out;
}

/**
 * @param list<string> $bins
 * @return array<string, ?string>
 */
function fzc_cap_path_which_many(array $bins): array {
	$which = array();
	if (!function_exists('shell_exec')) {
		foreach ($bins as $b) {
			$which[$b] = null;
		}
		return $which;
	}
	foreach ($bins as $bin) {
		$which[$bin] = null;
		$cmd = DIRECTORY_SEPARATOR === '\\'
			? 'where ' . escapeshellarg($bin) . ' 2>nul'
			: 'command -v ' . escapeshellarg($bin) . ' 2>/dev/null';
		$line = shell_exec($cmd);
		if (is_string($line)) {
			$t = trim(explode("\n", $line)[0]);
			$which[$bin] = $t !== '' ? $t : null;
		}
	}
	return $which;
}

/**
 * Magic-prefix catalog for inspect probes (64-byte head is enough for classification).
 *
 * @return array<string, array{label: string, head: string, requires_tool: ?string}>
 */
function fzc_cap_outer_wire_catalog(): array {
	return array(
		'fzb4_plain' => array('label' => 'FZB4 plain bundle', 'head' => 'FZB4', 'requires_tool' => null),
		'fzhm_store' => array('label' => 'FZHM per-member (store outer)', 'head' => "FZHM\x01", 'requires_tool' => null),
		'fzpa_zpaq' => array('label' => 'FZPA native folder zpaq', 'head' => "FZPA\x01", 'requires_tool' => 'zpaq'),
		'fzlb_brotli' => array('label' => 'FZLB tar|brotli folder', 'head' => "FZLB\x01", 'requires_tool' => 'brotli'),
		'raw_7z' => array('label' => 'Raw 7z archive', 'head' => "7z\xBC\xAF\x27\x1C", 'requires_tool' => '7z'),
		'arc_native' => array('label' => 'ARC native folder', 'head' => 'ARC' . "\x01", 'requires_tool' => 'arc'),
		'zstd_frame' => array('label' => 'Zstd framed outer', 'head' => "\x28\xB5\x2F\xFD", 'requires_tool' => 'zstd'),
		'xz_frame' => array('label' => 'XZ framed outer', 'head' => "\xFD7zXZ\x00", 'requires_tool' => 'xz'),
		'fzpaq_magic' => array('label' => 'FZzq zpaq outer wrapper', 'head' => 'FZzq', 'requires_tool' => 'zpaq'),
	);
}

/**
 * Head-only classification (no adaptive_decompress on dummy magic bytes — that would fatal_error in CLI).
 *
 * @return array<string, array<string, mixed>>
 */
function fzc_cap_probe_outer_wire_shapes(fractal_zip $fz): array {
	$rows = array();
	foreach (fzc_cap_outer_wire_catalog() as $id => $meta) {
		$head = (string) $meta['head'];
		$pad = str_pad($head, 64, "\0");
		$legacy = $fz->container_outer_needs_legacy_full_read($pad);
		$kind = fractal_zip::native_folder_wire_outer_kind_from_head($pad);
		$req = $meta['requires_tool'] ?? null;
		$selectiveBlocked = $legacy || $kind !== null;
		if ($id === 'fzb4_plain') {
			$selectiveBlocked = false;
		}
		if ($id === 'fzhm_store') {
			$selectiveBlocked = false;
		}
		$rows[$id] = array(
			'label' => $meta['label'],
			'requires_tool' => $req,
			'magic_prefix_hex' => bin2hex(substr($head, 0, min(16, strlen($head)))),
			'folder_native_wire_kind' => $kind,
			'outer_needs_legacy_full_read' => $legacy,
			'probe_mode' => 'head_only',
			'web_full_extract_via_open_container' => true,
			'web_selective_list_ok' => !$selectiveBlocked,
			'web_selective_read_ok' => !$selectiveBlocked,
			'selective_api_note' => $selectiveBlocked
				? 'Selective list/read return native_outer_single_member_unsupported; fzc_extract.php uses open_container (needs tools on host).'
				: 'Selective APIs may work when the container is a valid bundle (FZHM/FZB4).',
		);
	}
	return $rows;
}

/**
 * @return array{ok: bool, error: ?string, files: int, outer_kind: ?string, inspect: ?array}
 */
function fzc_cap_probe_fzc_roundtrip(fractal_zip $fz, string $fzcPath): array {
	$fzcPath = realpath($fzcPath) ?: $fzcPath;
	if (!is_file($fzcPath)) {
		return array('ok' => false, 'error' => 'file_not_found', 'files' => 0, 'outer_kind' => null, 'inspect' => null);
	}
	$insp = $fz->inspect_container_for_web_fs($fzcPath);
	$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzcap_rt_' . bin2hex(random_bytes(4));
	@mkdir($td, 0700, true);
	$copy = $td . DIRECTORY_SEPARATOR . basename($fzcPath);
	copy($fzcPath, $copy);
	$ob = ob_get_level();
	ob_start();
	try {
		$fz->open_container($copy, false);
	} catch (Throwable $e) {
		while (ob_get_level() > $ob) {
			ob_end_clean();
		}
		fzc_cap_remove_tree($td);
		return array(
			'ok' => false,
			'error' => $e->getMessage(),
			'files' => 0,
			'outer_kind' => $insp['folder_native_wire_kind'] ?? null,
			'inspect' => $insp,
		);
	}
	while (ob_get_level() > $ob) {
		ob_end_clean();
	}
	$n = 0;
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($td, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if ($fi->isFile() && !str_ends_with(strtolower($fi->getFilename()), '.fz')) {
			$n++;
		}
	}
	fzc_cap_remove_tree($td);
	return array(
		'ok' => true,
		'error' => null,
		'files' => $n,
		'outer_kind' => $insp['folder_native_wire_kind'] ?? null,
		'inspect' => $insp,
	);
}

/**
 * Run round-trip in a child PHP so fractal_zip::fatal_error() cannot exit the parent report.
 *
 * @return array{ok: bool, error: ?string, files: int, outer_kind: ?string, inspect: ?array, path?: string, subprocess_exit?: int}
 */
function fzc_cap_probe_fzc_roundtrip_subprocess(string $fzcPath): array {
	$fzcPath = realpath($fzcPath) ?: $fzcPath;
	if (!is_file($fzcPath)) {
		return array('ok' => false, 'error' => 'file_not_found', 'files' => 0, 'outer_kind' => null, 'inspect' => null);
	}
	$worker = __DIR__ . DIRECTORY_SEPARATOR . 'fzc_capability_roundtrip_worker.php';
	if (!is_file($worker)) {
		return array('ok' => false, 'error' => 'roundtrip_worker_missing', 'files' => 0, 'outer_kind' => null, 'inspect' => null);
	}
	if (!function_exists('proc_open')) {
		return array('ok' => false, 'error' => 'proc_open_disabled', 'files' => 0, 'outer_kind' => null, 'inspect' => null);
	}
	$php = defined('PHP_BINARY') && is_string(PHP_BINARY) && PHP_BINARY !== '' ? PHP_BINARY : 'php';
	$cmd = escapeshellarg($php) . ' -d opcache.enable_cli=0 ' . escapeshellarg($worker) . ' ' . escapeshellarg($fzcPath);
	$desc = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
	$p = @proc_open($cmd, $desc, $pipes, null, null);
	if (!is_resource($p)) {
		return array('ok' => false, 'error' => 'proc_open_failed', 'files' => 0, 'outer_kind' => null, 'inspect' => null);
	}
	$stdout = stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	$code = proc_close($p);
	$decoded = is_string($stdout) ? json_decode(trim($stdout), true) : null;
	if (!is_array($decoded)) {
		return array(
			'ok' => false,
			'error' => 'roundtrip_worker_bad_json',
			'files' => 0,
			'outer_kind' => null,
			'inspect' => null,
			'subprocess_exit' => $code,
		);
	}
	$decoded['subprocess_exit'] = $code;
	return $decoded;
}

/**
 * @param list<array<string, mixed>> $gaps
 */
function fzc_cap_parity_gap_error_count(array $gaps, bool $libraryOnly = false): int {
	$libraryIds = array(
		'no_subprocess',
		'incomplete_library_deploy',
		'fzc_roundtrip_failed',
		'fzc_path_rejected',
	);
	$n = 0;
	foreach ($gaps as $g) {
		if (($g['severity'] ?? '') !== 'error') {
			continue;
		}
		$id = (string) ($g['id'] ?? '');
		if ($libraryOnly) {
			if (!in_array($id, $libraryIds, true) && !str_ends_with($id, '_not_executable')) {
				continue;
			}
			if (str_ends_with($id, '_missing')) {
				continue;
			}
		}
		$n++;
	}
	return $n;
}

function fzc_cap_parity_gate_library_only(): bool {
	$e = getenv('FZC_PARITY_GATE_LIBRARY_ONLY');
	return $e !== false && trim((string) $e) !== '' && trim((string) $e) !== '0';
}

/**
 * Restrict ?fzc= on public HTTP to paths under repo or FRACTAL_ZIP_WEB_JOBS.
 */
function fzc_cap_sanitize_web_fzc_path(?string $path): ?string {
	if ($path === null || trim($path) === '') {
		return null;
	}
	$path = trim($path);
	if (str_contains($path, "\0") || preg_match('/\.\.(\/|\\\\)/', $path) === 1) {
		return null;
	}
	$real = realpath($path);
	if ($real === false || !is_file($real) || !is_readable($real)) {
		return null;
	}
	$repo = realpath(dirname(__DIR__));
	$roots = $repo !== false ? array($repo) : array();
	$jobsEnv = getenv('FRACTAL_ZIP_WEB_JOBS');
	if ($jobsEnv !== false && trim((string) $jobsEnv) !== '') {
		$jr = realpath(trim((string) $jobsEnv));
		if ($jr !== false) {
			$roots[] = $jr;
		}
	}
	foreach ($roots as $base) {
		$prefix = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		if (str_starts_with($real, $prefix)) {
			return $real;
		}
	}
	return null;
}

/**
 * Plain-text summary for operators (CLI --human or browser without json=1).
 */
function fzc_cap_render_human_summary(array $report): string {
	$lines = array();
	$prof = $report['profile'] ?? array();
	$lines[] = '=== fractal_zip capability: ' . ($prof['label'] ?? '?') . ' ===';
	$lines[] = 'SAPI: ' . ($prof['sapi'] ?? '?') . '  PHP: ' . ($prof['php_version'] ?? '?');
	$tools = $report['external_tools'] ?? array();
	foreach (array('zpaq', '7z', 'brotli', 'xz', 'zstd', 'arc') as $tk) {
		$row = $tools[$tk] ?? null;
		$p = is_array($row) ? ($row['path'] ?? null) : (is_string($row) ? $row : null);
		$ok = is_array($row) && !empty($row['executable']);
		$lines[] = sprintf('  %-7s %s', $tk . ':', $p === null ? '(not resolved)' : ($ok ? $p : $p . ' [not executable]'));
	}
	$fn = $prof['functions'] ?? array();
	if (empty($fn['proc_open'])) {
		$lines[] = '  PHP: proc_open DISABLED — native outers will fail on web extract';
	}
	$errs = array();
	$infos = 0;
	foreach ($report['parity_gaps'] ?? array() as $g) {
		$sev = (string) ($g['severity'] ?? 'info');
		if ($sev === 'error') {
			$errs[] = '  [error] ' . ($g['message'] ?? '');
		} elseif ($sev === 'warn') {
			$errs[] = '  [warn]  ' . ($g['message'] ?? '');
		} else {
			$infos++;
		}
	}
	$rt = $report['fzc_roundtrip'] ?? null;
	if (is_array($rt) && array_key_exists('ok', $rt)) {
		$lines[] = '  round-trip: ' . (!empty($rt['ok']) ? 'OK (' . (string) ($rt['files'] ?? 0) . ' files)' : 'FAILED — ' . (string) ($rt['error'] ?? '?'));
	}
	if ($errs !== array()) {
		$lines[] = '';
		$lines[] = 'Action required:';
		$lines = array_merge($lines, $errs);
	} else {
		$lines[] = '';
		$lines[] = 'No error-level host gaps (install same tools as encode host if extract still fails).';
	}
	if ($infos > 0) {
		$lines[] = "({$infos} info note(s) about selective APIs — see JSON or docs/WEB_LOCAL_PARITY.md)";
	}
	if (fzc_cap_parity_gate_library_only()) {
		$lines[] = '(library-only gate: external tool gaps ignored — set full gate on the deploy host before serving extract)';
	}
	$lines[] = '';
	$lines[] = 'Full matrix: examples/fzc_capability_report.php?json=1';
	$lines[] = 'Compare hosts: php examples/fzc_capability_compare.php local.json live.json';
	return implode("\n", $lines) . "\n";
}

/**
 * @return list<array{id: string, severity: string, message: string, fix: string}>
 */
function fzc_cap_build_parity_gaps(array $report): array {
	$gaps = array();
	$php = $report['profile'] ?? $report['php'] ?? array();
	$tools = $report['external_tools'] ?? array();
	$which = $report['path_which'] ?? array();

	if (empty($php['functions']['proc_open']) && empty($php['functions']['shell_exec'])) {
		$gaps[] = array(
			'id' => 'no_subprocess',
			'severity' => 'error',
			'message' => 'PHP cannot spawn subprocesses (proc_open and shell_exec unavailable). Native zpaq/7z/arc/brotli/xz outers will fail on full extract.',
			'fix' => 'Enable proc_open (and usually shell_exec) in php.ini disable_functions for the web SAPI.',
		);
	}

	foreach (array('zpaq' => 'FRACTAL_ZIP_ZPAQ', '7z' => 'FRACTAL_ZIP_7Z', 'arc' => 'freearc_arc') as $tool => $envHint) {
		$res = $tools[$tool] ?? null;
		$path = is_array($res) ? ($res['path'] ?? null) : (is_string($res) ? $res : null);
		if ($path === null) {
			$whichPath = $which[$tool] ?? ($tool === '7z' ? ($which['7za'] ?? $which['p7zip'] ?? null) : null);
			$gaps[] = array(
				'id' => $tool . '_missing',
				'severity' => 'error',
				'message' => "Library did not resolve `{$tool}` (compress locally with {$tool} outer → web extract fails with unsupported/missing tool).",
				'fix' => "Install {$tool} on the server and set {$envHint} in examples/fz_fractal_local_env.php (see setup_fractal_zip_zpaq_env.sh).",
			);
			continue;
		}
		if (is_array($res) && empty($res['executable'])) {
			$gaps[] = array(
				'id' => $tool . '_not_executable',
				'severity' => 'error',
				'message' => "`{$tool}` path is set but not executable: {$path}",
				'fix' => 'chmod +x or fix path in fz_fractal_local_env.php.',
			);
		}
	}

	$wires = $report['outer_wire_shapes'] ?? array();
	foreach ($wires as $id => $row) {
		$req = $row['requires_tool'] ?? null;
		if ($req === null) {
			continue;
		}
		$t = $tools[$req] ?? null;
		$resolved = is_array($t) ? ($t['path'] ?? null) : (is_string($t) ? $t : null);
		if ($req !== null && $resolved === null) {
			$gaps[] = array(
				'id' => 'outer_' . $id . '_needs_' . $req,
				'severity' => 'error',
				'message' => ($row['label'] ?? $id) . " archives need `{$req}` on the extract host (fzc_extract / open_container).",
				'fix' => "Install {$req} and set FRACTAL_ZIP_* in examples/fz_fractal_local_env.php.",
			);
		}
		if (!empty($row['outer_needs_legacy_full_read']) || (($row['folder_native_wire_kind'] ?? null) !== null && $id !== 'fzhm_store')) {
			$gaps[] = array(
				'id' => 'selective_api_' . $id,
				'severity' => 'info',
				'message' => ($row['label'] ?? $id) . ': selective list/read APIs are limited; full extract via fzc_extract.php is the parity surface.',
				'fix' => 'Do not treat native_outer_single_member_unsupported as “format unsupported” if open_container works with tools installed.',
			);
		}
	}

	$rt = $report['fzc_roundtrip'] ?? null;
	if (is_array($rt) && array_key_exists('ok', $rt) && empty($rt['ok'])) {
		$gaps[] = array(
			'id' => 'fzc_roundtrip_failed',
			'severity' => 'error',
			'message' => 'Full extract round-trip failed: ' . (string) ($rt['error'] ?? 'unknown'),
			'fix' => 'Match missing tools and php.ini limits between compress and extract hosts.',
		);
	}

	$peers = $report['library']['peer_files_missing'] ?? array();
	if (is_array($peers) && $peers !== array()) {
		$gaps[] = array(
			'id' => 'incomplete_library_deploy',
			'severity' => 'error',
			'message' => 'fractal_zip.php peer files missing on this host: ' . implode(', ', $peers),
			'fix' => 'Deploy the full repository root (all fractal_zip*.php next to fractal_zip.php), not a single-file copy.',
		);
	}

	return $gaps;
}

function fzc_cap_remove_tree(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		$item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
	}
	@rmdir($dir);
}

/**
 * @return array<string, mixed>
 */
function fzc_cap_build_report(string $label, bool $asWeb, ?string $fzcPath): array {
	$examplesDir = __DIR__;
	$repoRoot = dirname($examplesDir);

	if (PHP_SAPI !== 'cli' && $fzcPath !== null && $fzcPath !== '') {
		$fzcPath = fzc_cap_sanitize_web_fzc_path($fzcPath);
	}

	if ($asWeb) {
		require_once $examplesDir . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';
		require_once $examplesDir . DIRECTORY_SEPARATOR . 'fzc_web_shared.php';
		fzc_web_load_fractal_zip();
	} else {
		require_once $examplesDir . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';
	}

	$lib = getenv('FRACTAL_ZIP_PHP');
	if ($lib === false || trim((string) $lib) === '') {
		$lib = $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
	}
	if (!is_file($lib)) {
		$lib = $examplesDir . DIRECTORY_SEPARATOR . 'fractal_zip.php';
	}

	$peerList = array(
		'fractal_zip_literal_pac.php',
		'fractal_zip_folder_logical_bundle.php',
		'fractal_zip_folder_per_member_best.php',
	);
	$libDir = is_file($lib) ? dirname(realpath($lib) ?: $lib) : $repoRoot;
	$peerMissing = array();
	foreach ($peerList as $bn) {
		if (!is_file($libDir . DIRECTORY_SEPARATOR . $bn)) {
			$peerMissing[] = $bn;
		}
	}

	$report = array(
		'generated_at' => gmdate('c'),
		'parity_gate_mode' => fzc_cap_parity_gate_library_only() ? 'library_only' : 'full',
		'profile' => fzc_cap_probe_php_runtime($label, $asWeb),
		'paths' => array(
			'repo_root' => $repoRoot,
			'examples_dir' => $examplesDir,
			'fractal_zip_php' => $lib,
			'fz_fractal_local_env_present' => is_file($examplesDir . DIRECTORY_SEPARATOR . 'fz_fractal_local_env.php'),
		),
		'library' => array(
			'loadable' => is_file($lib),
			'peer_files_missing' => $peerMissing,
			'load_error' => null,
		),
		'web_limits' => array(),
		'external_tools' => array(),
		'path_which' => fzc_cap_path_which_many(array('zpaq', '7z', '7za', 'p7zip', 'arc', 'brotli', 'xz', 'zstd', 'ffmpeg', 'ffprobe', 'gzip')),
		'env_fractal_zip' => array(),
		'outer_wire_shapes' => array(),
		'fzc_roundtrip' => null,
		'parity_gaps' => array(),
	);

	if (function_exists('fzc_web_max_upload_bytes')) {
		$report['web_limits'] = array(
			'max_upload_bytes' => fzc_web_max_upload_bytes(),
			'max_extract_archive_bytes' => fzc_web_max_extract_archive_bytes(),
			'max_list_files' => fzc_web_max_list_files(),
		);
	} else {
		$report['web_limits'] = array('note' => 'fzc_web_shared not loaded (CLI-only report)');
	}

	$envKeys = array(
		'FRACTAL_ZIP_ZPAQ', 'FRACTAL_ZIP_7Z', 'FRACTAL_ZIP_ARC', 'FRACTAL_ZIP_BROTLI', 'FRACTAL_ZIP_XZ', 'FRACTAL_ZIP_ZSTD',
		'FRACTAL_ZIP_SKIP_ZPAQ', 'FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST', 'FRACTAL_ZIP_FOLDER_BASELINE_TIE',
		'FRACTAL_ZIP_BENCH_MEMORY_LIMIT', 'FZC_WEB_MAX_UPLOAD_BYTES', 'FZC_WEB_MAX_EXTRACT_ARCHIVE_BYTES',
	);
	foreach ($envKeys as $ek) {
		$ev = getenv($ek);
		$report['env_fractal_zip'][$ek] = $ev === false ? null : (string) $ev;
	}

	if (!is_file($lib) || $peerMissing !== array()) {
		$report['parity_gaps'] = fzc_cap_build_parity_gaps($report);
		return $report;
	}

	try {
		require_once $lib;
	} catch (Throwable $e) {
		$report['library']['load_error'] = $e->getMessage();
		$report['parity_gaps'] = fzc_cap_build_parity_gaps($report);
		return $report;
	}

	$zpaqProbe = static function (string $path, array &$out): void {
		if (!function_exists('proc_open')) {
			$out['probe_detail'] = 'proc_open disabled';
			return;
		}
		$cmd = escapeshellarg($path) . ' 2>&1';
		$desc = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
		$p = @proc_open($cmd, $desc, $pipes);
		if (!is_resource($p)) {
			$out['probe_ok'] = false;
			$out['probe_detail'] = 'proc_open failed';
			return;
		}
		fclose($pipes[0]);
		$banner = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$code = proc_close($p);
		$out['probe_ok'] = $code === 0 || $code === 1;
		$out['probe_detail'] = 'exit=' . (string) $code . ' len=' . (string) strlen($banner);
	};

	$report['external_tools'] = array(
		'zpaq' => fzc_cap_probe_binary(fractal_zip::zpaq_executable(), $zpaqProbe),
		'7z' => fzc_cap_probe_binary(fractal_zip::seven_zip_executable(), null),
		'brotli' => fzc_cap_probe_binary(fractal_zip::brotli_executable(), null),
		'xz' => fzc_cap_probe_binary(fractal_zip::xz_executable(), null),
		'zstd' => fzc_cap_probe_binary(fractal_zip::zstd_executable(), null),
		'arc' => fzc_cap_probe_binary(fractal_zip::freearc_executable(), null),
	);

	$fz = new fractal_zip(256, false, true, null, false);
	$report['outer_wire_shapes'] = fzc_cap_probe_outer_wire_shapes($fz);

	if ($fzcPath !== null && $fzcPath !== '') {
		$rt = fzc_cap_probe_fzc_roundtrip_subprocess($fzcPath);
		if (!isset($rt['path'])) {
			$rt['path'] = realpath($fzcPath) ?: $fzcPath;
		}
		if (empty($rt['ok']) && !empty($rt['error'])) {
			require_once $examplesDir . DIRECTORY_SEPARATOR . 'fzc_parity_hints.php';
			$hint = fzc_parity_hint_for_extract_error((string) $rt['error']);
			if ($hint !== null) {
				$rt['parity_hint'] = $hint;
			}
		}
		$report['fzc_roundtrip'] = $rt;
	}

	$report['parity_gaps'] = fzc_cap_build_parity_gaps($report);
	return $report;
}
