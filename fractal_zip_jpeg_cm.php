<?php

declare(strict_types=1);

/**
 * Deep JPEG lane: Dropbox lepton and/or Google brunsli bit-exact recompression.
 *
 * fireworks.jpeg: brunsli ~100.0 KB / lepton ~100.0 KB vs prior brotli outer ~122 KB.
 * Restores the original JPEG bytes (byte-identical RT). Kill switch:
 * FRACTAL_ZIP_JPEG_CM=0. Tool overrides: FRACTAL_ZIP_PAQ_LEPTON / FRACTAL_ZIP_PAQ_CBRUNSLI.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';

function fractal_zip_jpeg_cm_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_JPEG_CM');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !in_array($v, array('0', 'off', 'false', 'no'), true);
}

function fractal_zip_jpeg_cm_min_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_JPEG_CM_MIN_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		return max(256, (int) trim((string) $e));
	}
	return 4096;
}

function fractal_zip_jpeg_cm_max_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_JPEG_CM_MAX_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) trim((string) $e));
	}
	return 32 * 1024 * 1024;
}

function fractal_zip_jpeg_cm_looks_jpeg(string $rel, string $bytes): bool
{
	if (strlen($bytes) < 3 || $bytes[0] !== "\xFF" || $bytes[1] !== "\xD8") {
		return false;
	}
	$lower = strtolower(str_replace('\\', '/', $rel));
	if (preg_match('/\.(jpe?g|jfif)$/', $lower) === 1) {
		return true;
	}
	// Extensionless SOI is still a JPEG for this lane.
	return true;
}

/**
 * @return array{path: string, rel: string, bytes: string, size: int}|null
 */
function fractal_zip_jpeg_cm_identify_single_member(string $dir): ?array
{
	$root = realpath($dir);
	if ($root === false || !is_dir($root)) {
		return null;
	}
	$files = array();
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$base = $fi->getFilename();
		if (str_starts_with($base, 'fzpaq_') || str_starts_with($base, 'fzbcm_')
			|| str_ends_with(strtolower($base), '.paq8px')
			|| str_ends_with(strtolower($base), '.lep')) {
			continue;
		}
		$rp = $fi->getRealPath();
		$path = $rp !== false ? $rp : $fi->getPathname();
		$rel = ltrim(str_replace('\\', '/', substr($path, strlen($root))), '/');
		$files[] = array('path' => $path, 'rel' => $rel !== '' ? $rel : basename($path), 'size' => (int) $fi->getSize());
	}
	if (count($files) !== 1) {
		return null;
	}
	$f = $files[0];
	$min = fractal_zip_jpeg_cm_min_bytes();
	$max = fractal_zip_jpeg_cm_max_bytes();
	if ($f['size'] < $min || ($max > 0 && $f['size'] > $max)) {
		return null;
	}
	$bytes = (string) file_get_contents($f['path']);
	if ($bytes === '' || !fractal_zip_jpeg_cm_looks_jpeg($f['rel'], $bytes)) {
		return null;
	}
	return array(
		'path' => $f['path'],
		'rel' => $f['rel'],
		'bytes' => $bytes,
		'size' => $f['size'],
	);
}

/**
 * Compress: race lepton vs brunsli, keep smaller bit-exact arc.
 * Lifestyle: brunsli-first and skip lepton when brunsli already shrinks (fireworks.jpeg:
 * lepton ~45 ms for a larger arc than brunsli ~18 ms).
 *
 * @return array{wire: string, tool: string, arc_bytes: int, seconds: float}|null
 */
function fractal_zip_jpeg_cm_compress_member(string $rel, string $plain): ?array
{
	$t0 = microtime(true);
	$bestArc = null;
	$bestTool = null;
	$lifestyle = class_exists('fractal_zip', false)
		&& method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& fractal_zip::lifestyle_speed_profile_enabled();

	if($lifestyle) {
		$brn = fractal_zip_paq_brunsli_compress_bytes($plain);
		if(is_array($brn) && is_string($brn['bytes']) && $brn['bytes'] !== ''
			&& strlen($brn['bytes']) < strlen($plain)) {
			$bestArc = $brn['bytes'];
			$bestTool = 'brunsli';
		} else {
			$lep = fractal_zip_paq_lepton_compress_bytes($plain);
			if(is_array($lep) && is_string($lep['bytes']) && $lep['bytes'] !== ''
				&& strlen($lep['bytes']) < strlen($plain)) {
				$bestArc = $lep['bytes'];
				$bestTool = 'lepton';
			}
		}
	} else {
		$lep = fractal_zip_paq_lepton_compress_bytes($plain);
		if(is_array($lep) && is_string($lep['bytes']) && $lep['bytes'] !== ''
			&& strlen($lep['bytes']) < strlen($plain)) {
			$bestArc = $lep['bytes'];
			$bestTool = 'lepton';
		}
		$brn = fractal_zip_paq_brunsli_compress_bytes($plain);
		if(is_array($brn) && is_string($brn['bytes']) && $brn['bytes'] !== ''
			&& strlen($brn['bytes']) < strlen($plain)
			&& ($bestArc === null || strlen($brn['bytes']) < strlen($bestArc))) {
			$bestArc = $brn['bytes'];
			$bestTool = 'brunsli';
		}
	}
	if(!is_string($bestArc) || $bestArc === '' || !is_string($bestTool)) {
		return null;
	}
	// Ultra: paq8px −5 can beat brunsli/lepton on some JPEGs (111 fireworks
	// −9 KiB in ~3 s). Lifestyle keeps brunsli (wall).
	if (class_exists('fractal_zip', false)
		&& method_exists('fractal_zip', 'ultra_compression_enabled')
		&& fractal_zip::ultra_compression_enabled()
		&& function_exists('fractal_zip_paq_compress_file')
		&& function_exists('fractal_zip_paq_discover_executable')
		&& strlen($plain) >= 64 * 1024
		&& strlen($plain) <= 2 * 1024 * 1024) {
		$exe = fractal_zip_paq_discover_executable('paq8px');
		if (is_string($exe) && $exe !== '') {
			$savedLvl = getenv('FRACTAL_ZIP_PAQ8PX_LEVEL');
			putenv('FRACTAL_ZIP_PAQ8PX_LEVEL=5');
			$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzjpg8_' . bin2hex(random_bytes(4));
			@mkdir($box, 0700, true);
			$inPath = $box . DIRECTORY_SEPARATOR . 'in.jpg';
			if (@file_put_contents($inPath, $plain) !== false) {
				$pack = fractal_zip_paq_compress_file('paq8px', $exe, $inPath);
				if (is_array($pack) && isset($pack['bytes']) && is_string($pack['bytes'])
					&& $pack['bytes'] !== '' && strlen($pack['bytes']) < strlen($bestArc)) {
					$bestArc = $pack['bytes'];
					$bestTool = 'paq8px';
				}
			}
			foreach (glob($box . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
				@unlink($f);
			}
			@rmdir($box);
			if ($savedLvl === false) {
				putenv('FRACTAL_ZIP_PAQ8PX_LEVEL');
			} else {
				putenv('FRACTAL_ZIP_PAQ8PX_LEVEL=' . $savedLvl);
			}
		}
	}
	$wire = fractal_zip_paq_wrap_wire_v2($bestTool, $bestArc, $rel);
	return array(
		'wire' => $wire,
		'tool' => $bestTool,
		'arc_bytes' => strlen($bestArc),
		'seconds' => round(microtime(true) - $t0, 4),
	);
}

/**
 * Early short-circuit for zip_folder: write FZpq v2 lepton wire.
 *
 * @param object $fz fractal_zip instance
 */
function fractal_zip_jpeg_cm_try_write(object $fz, string $dir): bool
{
	if (!fractal_zip_jpeg_cm_enabled()) {
		return false;
	}
	if (fractal_zip_paq_discover_executable('lepton') === null
		&& fractal_zip_paq_discover_executable('cbrunsli') === null) {
		return false;
	}
	$member = fractal_zip_jpeg_cm_identify_single_member($dir);
	if ($member === null) {
		return false;
	}
	$pick = fractal_zip_jpeg_cm_compress_member($member['rel'], $member['bytes']);
	if ($pick === null) {
		return false;
	}
	$out = method_exists($fz, 'zip_folder_fzc_output_path')
		? (string) $fz->zip_folder_fzc_output_path($dir)
		: (rtrim($dir, "/\\") . '.fz');
	if (file_put_contents($out, $pick['wire']) === false) {
		return false;
	}
	$fz->array_fractal_zipped_strings_of_files = array($member['rel'] => '');
	if (class_exists('fractal_zip', false)) {
		fractal_zip::$last_outer_codec = 'paq';
		fractal_zip::$last_written_container_codec = 'paq';
	}
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		@fwrite(STDERR, '[jpeg-cm] ' . $member['rel']
			. ' tool=' . $pick['tool']
			. ' arc=' . number_format($pick['arc_bytes'])
			. ' wire=' . number_format(strlen($pick['wire']))
			. ' raw=' . number_format($member['size'])
			. ' sec=' . $pick['seconds'] . "\n");
	}
	return true;
}
