<?php

declare(strict_types=1);

/**
 * fractal_zip ↔ libfss bridge.
 *
 * Loads sibling/env fractal_substring bindings when available.
 * All sites default ON when available (FRACTAL_ZIP_FSS_*=0 to disable).
 */

$__fss_binding = null;
$__fss_root_env = getenv('FRACTAL_ZIP_FSS_ROOT');
$__fss_cands = array();
if(is_string($__fss_root_env) && trim($__fss_root_env) !== '') {
	$__fss_cands[] = rtrim(trim($__fss_root_env), "/\\") . '/bindings/fss.php';
}
$__fss_cands[] = dirname(__DIR__) . '/fractal_substring/bindings/fss.php';
$__fss_cands[] = __DIR__ . '/../fractal_substring/bindings/fss.php';
$__fss_cands[] = '/srv/http/fractal_substring/bindings/fss.php';
foreach($__fss_cands as $__fss_try) {
	if(is_file($__fss_try)) {
		$__fss_binding = $__fss_try;
		break;
	}
}
if(is_string($__fss_binding)) {
	require_once $__fss_binding;
}
unset($__fss_binding, $__fss_root_env, $__fss_cands, $__fss_try);

/* Auto-load native extension when available (COUNT KEEP path). */
if (function_exists('fss_php_ensure_ext')) {
	$ext = function_exists('fss_php_ext_path') ? fss_php_ext_path() : null;
	if ($ext !== null && !extension_loaded('fss')) {
		/* CLI: honor FRACTAL_ZIP_FSS_AUTOEXT=0 to skip. */
		$auto = getenv('FRACTAL_ZIP_FSS_AUTOEXT');
		$skip = is_string($auto) && in_array(strtolower(trim($auto)), array('0', 'false', 'off', 'no'), true);
		if (!$skip) {
			/* Try dl(); if disabled, suggest -d extension= via a one-time notice. */
			fss_php_ensure_ext();
			if (!extension_loaded('fss') && PHP_SAPI === 'cli'
				&& getenv('FRACTAL_ZIP_FSS_EXT_HINT') !== '0') {
				/* Silent — user can: php -d extension=/path/to/fss.so */
			}
		}
	}
}

/**
 * substr_count accelerator — falls through to PHP when fss is off/unavailable.
 */
function fractal_zip_fss_substr_count(string $haystack, string $needle): int
{
	if ($needle === '') {
		return 0;
	}
	if (function_exists('fss_php_count') && function_exists('fss_php_site_enabled')
		&& fss_php_site_enabled('COUNT')) {
		return fss_php_count($haystack, $needle);
	}
	return substr_count($haystack, $needle);
}

/**
 * strpos accelerator — falls through to PHP when fss is off/unavailable.
 * Offset>0 uses PHP strpos (avoid copying the haystack suffix).
 *
 * @return int|false
 */
function fractal_zip_fss_strpos(string $haystack, string $needle, int $offset = 0)
{
	if ($needle === '') {
		return $offset <= strlen($haystack) ? $offset : false;
	}
	if ($offset !== 0 || strlen($haystack) < 64 || strlen($needle) < 2) {
		return strpos($haystack, $needle, $offset);
	}
	if (function_exists('fss_php_find') && function_exists('fss_php_site_enabled')
		&& fss_php_site_enabled('FIND')) {
		return fss_php_find($haystack, $needle);
	}
	return strpos($haystack, $needle, $offset);
}

/**
 * Non-overlapping occurrence offsets (PHP substr_count / strpos walk).
 *
 * @return list<int>|null null when fss FIND site is off
 */
function fractal_zip_fss_find_all(string $haystack, string $needle): ?array
{
	if ($needle === '') {
		return array(0);
	}
	if (!function_exists('fss_php_site_enabled') || !fss_php_site_enabled('FIND')) {
		/* Still accelerate ASC skip-interval walks when COUNT/REPEATS are on. */
		if (!function_exists('fss_php_site_enabled') ||
			(!fss_php_site_enabled('COUNT') && !fss_php_site_enabled('REPEATS'))) {
			return null;
		}
	}
	if (function_exists('fss_php_find_all')) {
		return fss_php_find_all($haystack, $needle, false);
	}
	return null;
}

/**
 * Prefer fss hastok-compatible binary when FRACTAL_ZIP_FSS_HASTOK=1.
 */
function fractal_zip_fss_hastok_bin(): ?string
{
	if (function_exists('fss_php_hastok_bin_override')) {
		$p = fss_php_hastok_bin_override();
		if (is_string($p) && $p !== '') {
			return $p;
		}
	}
	return null;
}

/**
 * Batch substr_count via fss count-batch CLI (wins without FFI).
 *
 * @param list<string> $needles
 * @return array<string,int>|null map needle=>count, or null to fall back
 */
function fractal_zip_fss_substr_count_batch(string $haystack, array $needles): ?array
{
	if (!function_exists('fss_php_count_batch') || !function_exists('fss_php_site_enabled')
		|| !fss_php_site_enabled('COUNT')) {
		return null;
	}
	if (count($needles) < 4 || strlen($haystack) < 4096) {
		/* Multi-pattern batch wins from ~4 KiB / 4 needles upward. */
		if (!(function_exists('fss_php_ensure_ext') && fss_php_ensure_ext()
			&& count($needles) >= 4 && strlen($haystack) >= 4096)) {
			return null;
		}
	}
	$counts = fss_php_count_batch($haystack, $needles);
	if ($counts === null || count($counts) !== count($needles)) {
		return null;
	}
	$map = array();
	$i = 0;
	foreach ($needles as $nd) {
		$map[(string) $nd] = (int) $counts[$i];
		$i++;
	}
	return $map;
}

/**
 * Optional repeat discovery via libfss (ext in-process, else CLI).
 * Returns map substr => count, or null to use legacy all_substrings_count body.
 *
 * @return array<string,int>|null
 */
function fractal_zip_fss_repeats_map(string $string, int $minLen, int $maxLen, int $topK): ?array
{
	if (!function_exists('fss_php_site_enabled') || !fss_php_site_enabled('REPEATS')) {
		return null;
	}
	if (function_exists('fss_php_repeats_map')) {
		return fss_php_repeats_map($string, $minLen, $maxLen, $topK);
	}
	return null;
}
