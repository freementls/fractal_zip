<?php
declare(strict_types=1);

/**
 * FZSX v1 self-extracting archive: PHP polyglot prefix + HTML UI + binary trailer (.fz payload).
 * Extract target is always dirname(the .fzsx file).
 */
class fractal_zip_fzsx {
	public const MAGIC = "FZSX\x01";

	public const VERSION = 1;

	public const FORMAT_EXT = 'fzsx';

	/**
	 * @return array{ok: true, meta: array<string, mixed>, fzc: string, trailer_offset: int}|array{ok: false, error: string}
	 */
	public static function read_trailer(string $path): array {
		if (!is_file($path)) {
			return array('ok' => false, 'error' => 'file not found');
		}
		$size = filesize($path);
		if (!is_int($size) || $size < 16) {
			return array('ok' => false, 'error' => 'file too small');
		}
		$raw = file_get_contents($path);
		if (!is_string($raw) || $raw === '') {
			return array('ok' => false, 'error' => 'read failed');
		}
		$magic = static::MAGIC;
		$magicLen = strlen($magic);
		$pos = strrpos($raw, $magic);
		if ($pos === false) {
			return array('ok' => false, 'error' => static::format_label() . ' trailer magic not found');
		}
		$afterMagic = $pos + $magicLen;
		if ($afterMagic + 4 > $size) {
			return array('ok' => false, 'error' => 'truncated trailer');
		}
		$metaLen = unpack('N', substr($raw, $afterMagic, 4))[1];
		if (!is_int($metaLen) || $metaLen < 2 || $afterMagic + 4 + $metaLen > $size) {
			return array('ok' => false, 'error' => 'invalid meta length');
		}
		$metaJson = substr($raw, $afterMagic + 4, $metaLen);
		$meta = json_decode($metaJson, true);
		if (!is_array($meta)) {
			return array('ok' => false, 'error' => 'meta json invalid');
		}
		$fzcStart = $afterMagic + 4 + $metaLen;
		$fzc = substr($raw, $fzcStart);
		if ($fzc === '') {
			return array('ok' => false, 'error' => 'empty fzc payload');
		}
		$expectSha = isset($meta['fzc_sha256']) ? (string) $meta['fzc_sha256'] : '';
		if ($expectSha !== '' && hash('sha256', $fzc) !== $expectSha) {
			return array('ok' => false, 'error' => 'fzc sha256 mismatch');
		}
		return array(
			'ok' => true,
			'meta' => $meta,
			'fzc' => $fzc,
			'trailer_offset' => $pos,
		);
	}

	/**
	 * @param array<string, mixed> $opts label, lib_dir (repo root with fractal_zip.php), api_rel, source_rel, auto_extract
	 */
	public static function pack_from_fzc(string $outPath, string $fzcPath, array $opts = array()): void {
		if (!is_file($fzcPath)) {
			throw new InvalidArgumentException('fzc not found: ' . $fzcPath);
		}
		$fzc = file_get_contents($fzcPath);
		if (!is_string($fzc) || $fzc === '') {
			throw new RuntimeException('empty fzc: ' . $fzcPath);
		}
		$label = isset($opts['label']) && is_string($opts['label']) && $opts['label'] !== ''
			? $opts['label']
			: pathinfo($outPath, PATHINFO_FILENAME);
		$libDir = isset($opts['lib_dir']) && is_string($opts['lib_dir']) ? rtrim($opts['lib_dir'], '/\\') : (realpath(__DIR__) ?: __DIR__);
		$outDir = dirname(realpath($outPath) ?: $outPath);
		$apiRel = isset($opts['api_rel']) && is_string($opts['api_rel']) ? $opts['api_rel'] : self::default_api_rel($outDir);
		$sourceRel = isset($opts['source_rel']) && is_string($opts['source_rel']) ? $opts['source_rel'] : basename($outPath);
		$autoExtract = !empty($opts['auto_extract']);
		$memberCount = self::probe_member_count($fzcPath, $libDir);

		$meta = array(
			'version' => static::VERSION,
			'label' => $label,
			'fzc_len' => strlen($fzc),
			'fzc_sha256' => hash('sha256', $fzc),
			'member_count' => $memberCount,
			'packed_at' => gmdate('c'),
			'lib_dir' => self::relative_path($outDir, $libDir),
			'source_rel' => str_replace('\\', '/', $sourceRel),
			'api_rel' => str_replace('\\', '/', $apiRel),
			'format' => static::FORMAT_EXT,
		);
		$meta = array_merge($meta, static::pack_meta_extra($opts));
		$metaJson = json_encode($meta, JSON_UNESCAPED_SLASHES);
		if (!is_string($metaJson)) {
			throw new RuntimeException('meta json_encode failed');
		}
		$trailer = static::MAGIC . pack('N', strlen($metaJson)) . $metaJson . $fzc;

		$html = static::html_shell($meta, $autoExtract);
		$phpPrefix = static::php_polyglot_prefix($meta);
		$blob = $phpPrefix . $html . $trailer;

		$dir = dirname($outPath);
		if ($dir !== '' && !is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
			throw new RuntimeException('cannot create output directory: ' . $dir);
		}
		if (file_put_contents($outPath, $blob) === false) {
			throw new RuntimeException('write failed: ' . $outPath);
		}
		@chmod($outPath, 0755);
	}

	public static function pack_from_directory(string $outPath, string $dirPath, array $opts = array()): void {
		$abs = realpath($dirPath);
		if ($abs === false || !is_dir($abs)) {
			throw new InvalidArgumentException('not a directory: ' . $dirPath);
		}
		$tmpFzc = tempnam(sys_get_temp_dir(), 'fzsx_');
		if ($tmpFzc === false) {
			throw new RuntimeException('tempnam failed');
		}
		$fzcPath = $tmpFzc . '.fz';
		@unlink($tmpFzc);
		try {
			if (!getenv('FRACTAL_ZIP_CLI_VERBOSE')) {
				ob_start();
			}
			$fz = new fractal_zip(null, true, true, null, true);
			$fz->zip_folder($abs, false);
			if (!getenv('FRACTAL_ZIP_CLI_VERBOSE')) {
				ob_end_clean();
			}
			$sidecar = $abs . '.fz';
			if (!is_file($sidecar)) {
				throw new RuntimeException('zip_folder did not produce .fz beside ' . $abs);
			}
			if (!@rename($sidecar, $fzcPath)) {
				if (!@copy($sidecar, $fzcPath)) {
					throw new RuntimeException('could not stage temp fzc');
				}
				@unlink($sidecar);
			}
			if (!isset($opts['label'])) {
				$opts['label'] = basename($abs);
			}
			self::pack_from_fzc($outPath, $fzcPath, $opts);
		} finally {
			if (is_file($fzcPath)) {
				@unlink($fzcPath);
			}
		}
	}

	/**
	 * @return array{ok: true, dest: string, member_count: int, members: list<string>}|array{ok: false, error: string}
	 */
	public static function extract_beside_self(string $fzsxPath, bool $verbose = false): array {
		$fzsxReal = realpath($fzsxPath);
		if ($fzsxReal === false || !is_file($fzsxReal)) {
			return array('ok' => false, 'error' => 'fzsx not found');
		}
		$tr = self::read_trailer($fzsxReal);
		if (empty($tr['ok'])) {
			return array('ok' => false, 'error' => (string) ($tr['error'] ?? 'trailer read failed'));
		}
		$destRoot = dirname($fzsxReal);
		$destErr = self::extract_dest_writable_error($destRoot);
		if ($destErr !== null) {
			return array('ok' => false, 'error' => $destErr);
		}
		self::load_fractal_zip_for_fzsx($fzsxReal, $tr['meta']);

		$sandbox = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzsx_' . bin2hex(random_bytes(8));
		if (!@mkdir($sandbox, 0700, true) && !is_dir($sandbox)) {
			return array('ok' => false, 'error' => 'cannot create temp extract directory');
		}
		$tmpFzc = $sandbox . DIRECTORY_SEPARATOR . 'payload.fz';
		if (file_put_contents($tmpFzc, $tr['fzc']) === false) {
			self::rm_rf($sandbox);
			return array('ok' => false, 'error' => 'cannot write temp fzc (check PHP temp directory permissions)');
		}
		try {
			if (!$verbose) {
				ob_start();
			}
			$fz = new fractal_zip(null, true, true, null, true);
			$fz->open_container($tmpFzc, false);
			if (!$verbose) {
				ob_end_clean();
			}
			self::promote_extracted_tree($sandbox, $destRoot, 'payload.fz');
			$members = self::member_paths_from_fractal_zip($fz, $destRoot, basename($fzsxReal));
		} catch (Throwable $e) {
			self::rm_rf($sandbox);
			return array('ok' => false, 'error' => $e->getMessage());
		}
		self::rm_rf($sandbox);
		$result = array(
			'ok' => true,
			'dest' => $destRoot,
			'member_count' => count($members),
			'members' => $members,
		);
		if (static::should_destroy_after_extract($tr['meta'])) {
			static::destroy_archive($fzsxReal);
			$result['self_destructed'] = true;
		}
		return $result;
	}

	/**
	 * @param array<string, mixed> $opts
	 * @return array<string, mixed>
	 */
	protected static function pack_meta_extra(array $opts): array {
		return array();
	}

	protected static function format_label(): string {
		return strtoupper(static::FORMAT_EXT);
	}

	protected static function format_display_ext(): string {
		return '.' . static::FORMAT_EXT;
	}

	/**
	 * @param array<string, mixed> $meta
	 */
	protected static function should_destroy_after_extract(array $meta): bool {
		return !empty($meta['self_destruct']);
	}

	protected static function destroy_archive(string $path): void {
		if (!is_file($path)) {
			return;
		}
		$size = filesize($path);
		if (is_int($size) && $size > 0 && $size <= 32 * 1024 * 1024) {
			$fh = @fopen($path, 'cb');
			if ($fh !== false) {
				$chunk = random_bytes(min($size, 8192));
				@fwrite($fh, str_repeat($chunk, (int) max(1, ceil($size / strlen($chunk)))));
				@fflush($fh);
				@fclose($fh);
			}
		}
		@unlink($path);
	}

	/** @return class-string<fractal_zip_fzsx> */
	public static function reader_class_for_path(string $path): string {
		$lower = strtolower($path);
		if (self::is_fzsxsd_path($lower)) {
			return fractal_zip_fzsxsd::class;
		}
		if (str_ends_with($lower, '.fzsx')) {
			return fractal_zip_fzsx::class;
		}
		foreach (array(fractal_zip_fzsxsd::class, fractal_zip_fzsx::class) as $class) {
			$tr = $class::read_trailer($path);
			if (!empty($tr['ok'])) {
				return $class;
			}
		}
		return fractal_zip_fzsx::class;
	}

	/** True for .fzsxsd or short alias .fzsd */
	public static function is_fzsxsd_path(string $path): bool {
		$lower = strtolower($path);
		return str_ends_with($lower, '.fzsxsd') || str_ends_with($lower, '.fzsd');
	}

	/** True for any self-extracting polyglot (.fzsx / .fzsxsd / .fzsd) */
	public static function is_polyglot_path(string $path): bool {
		$lower = strtolower($path);
		return str_ends_with($lower, '.fzsx') || self::is_fzsxsd_path($lower);
	}

	public static function resolve_archive_path(string $examplesDir, string $sourceRel): ?string {
		$sourceRel = str_replace('\\', '/', trim($sourceRel));
		if ($sourceRel === '' || str_contains($sourceRel, '..')) {
			return null;
		}
		if (!self::is_polyglot_path($sourceRel)) {
			return null;
		}
		$candidate = $examplesDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $sourceRel);
		$real = realpath($candidate);
		if ($real === false || !is_file($real)) {
			return null;
		}
		$examplesReal = realpath($examplesDir);
		if ($examplesReal === false || !str_starts_with($real, $examplesReal . DIRECTORY_SEPARATOR)) {
			return null;
		}
		return $real;
	}

	private static function extract_dest_writable_error(string $destRoot): ?string {
		$real = realpath($destRoot);
		if ($real === false || !is_dir($real)) {
			return 'extract directory not found: ' . $destRoot;
		}
		if (!is_writable($real)) {
			return 'Cannot extract beside archive: ' . $real . ' is not writable by PHP (chmod/chown that directory for server-side extract).';
		}
		return null;
	}

	private static function promote_extracted_tree(string $fromRoot, string $toRoot, string $skipBasename): void {
		$fromReal = realpath($fromRoot);
		$toReal = realpath($toRoot) ?: $toRoot;
		if ($fromReal === false) {
			throw new RuntimeException('extract staging directory missing');
		}
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($fromReal, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($it as $item) {
			$rel = substr($item->getPathname(), strlen($fromReal) + 1);
			$base = basename(str_replace('\\', '/', $rel));
			if ($base === $skipBasename) {
				continue;
			}
			$dest = $toReal . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
			if ($item->isDir()) {
				if (!is_dir($dest) && !@mkdir($dest, 0755, true) && !is_dir($dest)) {
					throw new RuntimeException('cannot create directory: ' . $dest);
				}
				continue;
			}
			$destDir = dirname($dest);
			if (!is_dir($destDir) && !@mkdir($destDir, 0755, true) && !is_dir($destDir)) {
				throw new RuntimeException('cannot create directory: ' . $destDir);
			}
			if (!@rename($item->getPathname(), $dest)) {
				if (!@copy($item->getPathname(), $dest)) {
					throw new RuntimeException('cannot write extracted file: ' . $dest);
				}
				@unlink($item->getPathname());
			}
		}
	}

	private static function rm_rf(string $path): void {
		if ($path === '' || $path === '/' || !file_exists($path)) {
			return;
		}
		if (is_file($path) || is_link($path)) {
			@unlink($path);
			return;
		}
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($it as $item) {
			$p = $item->getPathname();
			if ($item->isDir() && !$item->isLink()) {
				@rmdir($p);
			} else {
				@unlink($p);
			}
		}
		@rmdir($path);
	}

	/**
	 * @return list<string>
	 */
	private static function member_paths_from_fractal_zip(fractal_zip $fz, string $destRoot, string $fzsxBase): array {
		$map = $fz->array_fractal_zipped_strings_of_files ?? null;
		if (is_array($map) && $map !== array()) {
			$out = array();
			$rootReal = realpath($destRoot) ?: $destRoot;
			foreach (array_keys($map) as $rel) {
				$rel = str_replace('\\', '/', (string) $rel);
				$full = $rootReal . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
				if (is_file($full)) {
					$out[] = $rel;
				}
			}
			sort($out, SORT_STRING);
			return $out;
		}
		return self::list_extracted_files_impl($destRoot, $fzsxBase);
	}

	public static function polyglot_cli_entry(string $selfPath): int {
		$res = static::extract_beside_self($selfPath, getenv('FRACTAL_ZIP_CLI_VERBOSE') === '1');
		if (empty($res['ok'])) {
			fwrite(STDERR, static::FORMAT_EXT . ': ' . (string) ($res['error'] ?? 'extract failed') . "\n");
			return 1;
		}
		$n = (int) ($res['member_count'] ?? 0);
		$dest = (string) ($res['dest'] ?? '');
		echo 'Extracted ' . (string) $n . ' file(s) to ' . $dest . "\n";
		if (!empty($res['self_destructed'])) {
			echo 'Archive removed (' . static::format_display_ext() . " self-destruct).\n";
		}
		$show = array_slice($res['members'] ?? array(), 0, 20);
		foreach ($show as $m) {
			echo '  ' . $m . "\n";
		}
		if ($n > count($show)) {
			echo '  … and ' . (string) ($n - count($show)) . " more\n";
		}
		return 0;
	}

	/** Emit HTML UI when the polyglot is executed under mod_php / FPM (not CLI). */
	public static function polyglot_web_entry(string $selfPath): void {
		$range = self::html_serve_range($selfPath);
		if ($range === null) {
			http_response_code(500);
			header('Content-Type: text/plain; charset=UTF-8');
			echo "Invalid FZSX\n";
			return;
		}
		header('Content-Type: text/html; charset=UTF-8');
		header('X-Content-Type-Options: nosniff');
		$fh = fopen($selfPath, 'rb');
		if ($fh === false) {
			http_response_code(500);
			return;
		}
		fseek($fh, $range['start']);
		echo fread($fh, $range['length']);
		fclose($fh);
	}

	public static function html_byte_offset(string $path): ?int {
		$raw = @file_get_contents($path, false, null, 0, 65536);
		if (!is_string($raw)) {
			return null;
		}
		$p = stripos($raw, '<!DOCTYPE html>');
		return $p === false ? null : $p;
	}

	/**
	 * Byte range of the HTML UI inside a polyglot .fzsx (excludes PHP prefix and binary trailer).
	 *
	 * @return array{start: int, length: int}|null
	 */
	public static function html_serve_range(string $path): ?array {
		$start = self::html_byte_offset($path);
		if ($start === null) {
			return null;
		}
		$tr = static::read_trailer($path);
		if (empty($tr['ok'])) {
			return null;
		}
		$end = (int) $tr['trailer_offset'];
		if ($end <= $start) {
			return null;
		}
		return array('start' => $start, 'length' => $end - $start);
	}

	/**
	 * Resolve .fzsx path from source_rel relative to examples/ API directory.
	 * @deprecated use resolve_archive_path()
	 */
	public static function resolve_fzsx_path(string $examplesDir, string $sourceRel): ?string {
		return self::resolve_archive_path($examplesDir, $sourceRel);
	}

	/**
	 * @param array<string, mixed> $meta
	 */
	private static function load_fractal_zip_for_fzsx(string $fzsxPath, array $meta): void {
		if (class_exists('fractal_zip', false)) {
			return;
		}
		$env = getenv('FRACTAL_ZIP_PHP');
		if (is_string($env) && $env !== '' && is_file($env)) {
			require_once $env;
			return;
		}
		$libDirHint = isset($meta['lib_dir']) ? (string) $meta['lib_dir'] : '';
		$candidates = array();
		if ($libDirHint !== '') {
			$candidates[] = dirname($fzsxPath) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $libDirHint) . DIRECTORY_SEPARATOR . 'fractal_zip.php';
		}
		$candidates[] = dirname($fzsxPath) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'fractal_zip.php';
		$candidates[] = dirname($fzsxPath, 2) . DIRECTORY_SEPARATOR . 'fractal_zip.php';
		$bootstrap = dirname($fzsxPath) . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';
		if (is_file($bootstrap)) {
			require_once $bootstrap;
		} else {
			$exBoot = __DIR__ . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';
			if (is_file($exBoot)) {
				require_once $exBoot;
			}
		}
		foreach ($candidates as $c) {
			if (is_file($c)) {
				require_once $c;
				return;
			}
		}
		throw new RuntimeException('fractal_zip.php not found; set FRACTAL_ZIP_PHP');
	}

	private static function probe_member_count(string $fzcPath, string $libDir): int {
		$lib = $libDir . DIRECTORY_SEPARATOR . 'fractal_zip.php';
		if (!is_file($lib)) {
			return 0;
		}
		require_once $lib;
		try {
			$fz = new fractal_zip(null, true, false, null, false);
			$r = $fz->try_list_container_members_for_web_fs($fzcPath);
			if (!empty($r['ok']) && isset($r['members']) && is_array($r['members'])) {
				return count($r['members']);
			}
		} catch (Throwable $e) {
		}
		return 0;
	}

	/**
	 * @return list<string>
	 */
	public static function list_extracted_files_impl(string $root, string $fzsxBase): array {
		$out = array();
		if (!is_dir($root)) {
			return $out;
		}
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
		$rootReal = realpath($root);
		foreach ($it as $item) {
			if (!$item->isFile()) {
				continue;
			}
			$path = $item->getPathname();
			$base = basename($path);
			if ($base === $fzsxBase) {
				continue;
			}
			if (str_starts_with($base, '.') && str_contains($base, '.fz.tmp')) {
				continue;
			}
			if (str_ends_with(strtolower($base), '.fz') || fractal_zip_fzsx::is_polyglot_path($base)) {
				continue;
			}
			$rel = $rootReal !== false ? substr($path, strlen($rootReal) + 1) : $base;
			$out[] = str_replace('\\', '/', $rel);
		}
		sort($out, SORT_STRING);
		return $out;
	}

	private static function default_api_rel(string $outDir): string {
		$examples = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'examples');
		if ($examples === false) {
			return 'fzsx_api.php';
		}
		$outReal = realpath($outDir) ?: $outDir;
		if (str_starts_with($outReal, $examples)) {
			return self::relative_path($outReal, $examples . DIRECTORY_SEPARATOR . 'fzsx_api.php');
		}
		// Web-root / sibling packs: expect fzsx_api.php beside the archive.
		return 'fzsx_api.php';
	}

	private static function relative_path(string $fromDir, string $toPath): string {
		$from = realpath($fromDir) ?: $fromDir;
		$to = realpath($toPath) ?: $toPath;
		$from = rtrim(str_replace('\\', '/', $from), '/');
		$to = str_replace('\\', '/', $to);
		if ($from === $to) {
			return basename($to);
		}
		if (str_starts_with($to, $from . '/')) {
			return substr($to, strlen($from) + 1);
		}
		$fromParts = explode('/', $from);
		$toParts = explode('/', $to);
		while ($fromParts !== array() && $toParts !== array() && $fromParts[0] === $toParts[0]) {
			array_shift($fromParts);
			array_shift($toParts);
		}
		$rel = implode('/', array_fill(0, count($fromParts), '..'));
		if ($rel !== '' && $toParts !== array()) {
			$rel .= '/';
		}
		$rel .= implode('/', $toParts);
		return $rel === '' ? '.' : $rel;
	}

	/**
	 * @param array<string, mixed> $meta
	 */
	private static function php_polyglot_prefix(array $meta): string {
		$libDir = isset($meta['lib_dir']) ? (string) $meta['lib_dir'] : '..';
		$libDirEsc = var_export(str_replace('\\', '/', $libDir), true);
		$class = static::class;
		return '<?php' . "\n"
			. '$__fzsxLibDir = ' . $libDirEsc . ";\n"
			. '$__fzsxModule = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace(\'/\', DIRECTORY_SEPARATOR, $__fzsxLibDir) . DIRECTORY_SEPARATOR . \'fractal_zip_fzsx.php\';' . "\n"
			. 'if (!is_file($__fzsxModule)) {' . "\n"
			. '	$__fzsxModule = dirname(__FILE__) . DIRECTORY_SEPARATOR . \'..\' . DIRECTORY_SEPARATOR . \'fractal_zip_fzsx.php\';' . "\n"
			. '}' . "\n"
			. 'require_once $__fzsxModule;' . "\n"
			. 'if (PHP_SAPI === \'cli\') {' . "\n"
			. '	exit(' . $class . '::polyglot_cli_entry(__FILE__));' . "\n"
			. '}' . "\n"
			. $class . '::polyglot_web_entry(__FILE__);' . "\n"
			. 'exit;' . "\n"
			. '__halt_compiler();' . "\n";
	}

	/**
	 * @param array<string, mixed> $meta
	 */
	protected static function html_shell(array $meta, bool $autoExtract): string {
		$label = htmlspecialchars((string) ($meta['label'] ?? 'archive'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$memberCount = (int) ($meta['member_count'] ?? 0);
		$formatExt = static::format_display_ext();
		$formatTitle = static::format_label();
		$config = array(
			'label' => $meta['label'] ?? '',
			'member_count' => $memberCount,
			'source_rel' => $meta['source_rel'] ?? '',
			'api_rel' => $meta['api_rel'] ?? 'fzsx_api.php',
			'auto_extract' => $autoExtract,
			'self_destruct' => static::should_destroy_after_extract($meta),
		);
		$configJson = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
		if (!is_string($configJson)) {
			$configJson = '{}';
		}
		$intro = static::html_shell_intro_html($formatExt, $memberCount);
		return '<!DOCTYPE html>' . "\n"
			. '<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . "\n"
			. '<title>' . $label . ' — ' . $formatTitle . '</title>' . "\n"
			. '<style>:root{color-scheme:dark;--bg:#0c0e12;--text:#e8eaed;--muted:#9aa3b2;--accent:#38bdf8;--border:#2a3344;font-family:system-ui,sans-serif;line-height:1.5}'
			. 'body{margin:0;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem}'
			. '.card{max-width:28rem;width:100%;background:#141820;border:1px solid var(--border);border-radius:8px;padding:1.25rem 1.5rem}'
			. 'h1{font-size:1.15rem;margin:0 0 .35rem}p{margin:.4rem 0;color:var(--muted);font-size:.92rem}'
			. 'button{background:var(--accent);color:#0c0e12;border:0;border-radius:6px;padding:.55rem 1rem;font-size:1rem;font-weight:600;cursor:pointer;margin-top:.75rem}'
			. 'button:disabled{opacity:.5;cursor:not-allowed}.ok{color:#86efac}.err{color:#fca5a5}ul{margin:.5rem 0 0;padding-left:1.2rem;font-size:.85rem;color:var(--muted)}</style></head><body>' . "\n"
			. '<div class="card"><h1>' . $label . '</h1>' . "\n"
			. $intro
			. '<button type="button" id="go">Extract files</button>' . "\n"
			. '<p id="status" role="status"></p><ul id="list"></ul></div>' . "\n"
			. '<script>window.__fzsx=' . $configJson . ';</script>' . "\n"
			. static::html_shell_script() . "\n"
			. '</body></html>' . "\n";
	}

	protected static function html_shell_intro_html(string $formatExt, int $memberCount): string {
		$html = '<p>Self-extracting fractal_zip archive (<code>' . $formatExt . '</code>).</p>' . "\n"
			. '<p>Files extract <strong>next to this archive</strong> on the server, or beside the file when run with <code>php …' . $formatExt . '</code>.</p>' . "\n";
		if ($memberCount > 0) {
			$html .= '<p>' . (string) $memberCount . " member(s) inside.</p>\n";
		}
		return $html;
	}

	protected static function html_shell_script(): string {
		return <<<'JS'
<script>
(function () {
	var cfg = window.__fzsx || {};
	var btn = document.getElementById('go');
	var status = document.getElementById('status');
	var list = document.getElementById('list');
	function apiUrl() {
		var rel = cfg.api_rel || 'fzsx_api.php';
		try { return new URL(rel, window.location.href).href; } catch (e) { return rel; }
	}
	function setStatus(msg, cls) {
		status.textContent = msg;
		status.className = cls || '';
	}
	function renderMembers(members) {
		list.innerHTML = '';
		(members || []).slice(0, 20).forEach(function (m) {
			var li = document.createElement('li');
			li.textContent = m;
			list.appendChild(li);
		});
	}
	function doExtract() {
		btn.disabled = true;
		setStatus('Extracting…', '');
		var fd = new FormData();
		fd.append('action', 'extract');
		fd.append('source_rel', cfg.source_rel || '');
		fetch(apiUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (j) {
				if (!j || !j.ok) {
					setStatus((j && j.error) ? j.error : 'Extract failed', 'err');
					btn.disabled = false;
					return;
				}
				var msg = 'Extracted ' + j.member_count + ' file(s) to ' + j.dest;
				if (j.self_destructed) {
					msg += ' — archive removed';
				}
				setStatus(msg, 'ok');
				renderMembers(j.members || []);
			})
			.catch(function (e) {
				setStatus(String(e), 'err');
				btn.disabled = false;
			});
	}
	btn.addEventListener('click', doExtract);
	if (cfg.auto_extract && location.protocol !== 'file:') { doExtract(); }
})();
</script>
JS;
	}
}

final class fractal_zip_fzsxsd extends fractal_zip_fzsx {
	public const MAGIC = "FZSXSD\x01";

	public const VERSION = 1;

	/** Canonical long extension; prefer short alias `.fzsd` for new packs. */
	public const FORMAT_EXT = 'fzsxsd';

	/** Preferred short extension going forward (same magic / self-destruct). */
	public const FORMAT_EXT_SHORT = 'fzsd';

	/**
	 * @param array<string, mixed> $opts
	 * @return array<string, mixed>
	 */
	protected static function pack_meta_extra(array $opts): array {
		return array('self_destruct' => true);
	}

	/**
	 * @param array<string, mixed> $meta
	 */
	protected static function should_destroy_after_extract(array $meta): bool {
		return true;
	}

	protected static function html_shell_intro_html(string $formatExt, int $memberCount): string {
		$html = '<p>Self-destructing fractal_zip archive (<code>' . $formatExt . '</code>).</p>' . "\n"
			. '<p>Files extract <strong>next to this archive</strong>; the archive file is <strong>removed after a successful extract</strong>.</p>' . "\n";
		if ($memberCount > 0) {
			$html .= '<p>' . (string) $memberCount . " member(s) inside.</p>\n";
		}
		return $html;
	}
}
