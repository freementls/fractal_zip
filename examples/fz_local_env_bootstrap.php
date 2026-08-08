<?php
declare(strict_types=1);

/**
 * Loads optional per-server tool paths before fractal_zip is required.
 * Create fz_fractal_local_env.php in this directory (see fz_fractal_local_env.php.example),
 * or run examples/setup_fractal_zip_extract_tools_env.sh on the host.
 *
 * When FRACTAL_ZIP_* is unset, probes /home/freement/bin/ and common system paths
 * (freement.cloud production layout — works even when PHP's PATH/shell_exec differ from CLI).
 */
$__fzLocalEnv = __DIR__ . DIRECTORY_SEPARATOR . 'fz_fractal_local_env.php';
if (is_file($__fzLocalEnv)) {
	require_once $__fzLocalEnv;
}
unset($__fzLocalEnv);

/**
 * @param list<string> $candidates
 */
$__fzPickExe = static function (array $candidates): ?string {
	foreach ($candidates as $p) {
		if (!is_string($p) || $p === '') {
			continue;
		}
		if (is_file($p) && is_executable($p)) {
			$rp = realpath($p);
			return $rp !== false ? $rp : $p;
		}
	}
	return null;
};

/**
 * Set putenv only when the variable is empty and a binary was found.
 */
$__fzSetToolEnv = static function (string $envVar, ?string $path) use ($__fzPickExe): void {
	if ($path === null) {
		return;
	}
	$cur = getenv($envVar);
	if ($cur !== false && trim((string) $cur) !== '') {
		return;
	}
	putenv($envVar . '=' . $path);
};

$__fzHome = getenv('HOME');
if (!is_string($__fzHome) || trim($__fzHome) === '') {
	$__fzHome = '/home/freement';
}
$__fzHome = rtrim((string) $__fzHome, '/\\');
$__fzFreementBin = '/home/freement/bin';

$__fzToolCandidates = array(
	'FRACTAL_ZIP_ZPAQ' => array(
		$__fzFreementBin . '/zpaq',
		$__fzHome . '/bin/zpaq',
		'/usr/bin/zpaq',
		'/usr/local/bin/zpaq',
	),
	'FRACTAL_ZIP_7Z' => array(
		$__fzFreementBin . '/7zz',
		$__fzFreementBin . '/7z',
		$__fzFreementBin . '/7za',
		$__fzHome . '/bin/7zz',
		$__fzHome . '/bin/7z',
		'/usr/bin/7zz',
		'/usr/bin/7z',
		'/usr/bin/7za',
		'/usr/local/bin/7zz',
		'/usr/local/bin/7z',
	),
	'FRACTAL_ZIP_ARC' => array(
		$__fzFreementBin . '/arc',
		$__fzHome . '/.local/bin/arc',
		$__fzHome . '/bin/arc',
		'/usr/bin/arc',
		'/usr/local/bin/arc',
	),
	'FRACTAL_ZIP_BROTLI' => array(
		$__fzFreementBin . '/brotli',
		'/usr/bin/brotli',
		'/usr/local/bin/brotli',
	),
	'FRACTAL_ZIP_XZ' => array(
		$__fzFreementBin . '/xz',
		'/usr/bin/xz',
		'/usr/local/bin/xz',
	),
	'FRACTAL_ZIP_ZSTD' => array(
		$__fzFreementBin . '/zstd',
		$__fzHome . '/bin/zstd',
		'/usr/bin/zstd',
		'/usr/local/bin/zstd',
	),
);

foreach ($__fzToolCandidates as $__fzEnv => $__fzPaths) {
	$__fzSetToolEnv($__fzEnv, $__fzPickExe($__fzPaths));
}

unset($__fzPickExe, $__fzSetToolEnv, $__fzHome, $__fzFreementBin, $__fzToolCandidates, $__fzEnv, $__fzPaths);
