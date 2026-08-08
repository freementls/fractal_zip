<?php

declare(strict_types=1);

/**
 * Offline experiment: compare redundancy **proxies** across representation lanes for the same files.
 *
 * Philosophy (see also top-of `fractal_zip.php`): decoded payloads often expose more repeatable structure than
 * opaque containers, but **nested** containers can sometimes align so outer compressors see a friendlier stream.
 * This script does **not** change encode defaults; it only reports numbers so you can tune envs or add modes later.
 *
 * Usage:
 *   php benchmarks/container_representation_experiment.php [root_dir_or_file]
 *
 * Defaults: root = `test_files60` under the repo if it exists, else repo root `.`
 *
 * Environment:
 *   CONTAINER_EXP_MAX_FILES        — max files to scan (default 80)
 *   CONTAINER_EXP_MAX_FILE_BYTES   — max bytes read per file, 0 = whole file (default 52428800 = 50 MiB)
 *   CONTAINER_EXP_EXTENSIONS       — comma list, default `flac,png,gz,zip`
 *   CONTAINER_EXP_PCM              — if `1`, probe decoded PCM size for `.flac` via ffmpeg (slow; needs tools)
 *   CONTAINER_EXP_SYNTHETIC        — if `1`, add **nest** columns: FLAC bytes re-wrapped as a gzip member (`gzencode`),
 *                                    then zlib-deflate that wrapper — a crude “container inside container” redundancy probe
 *   FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE — passed through to `choose_best_literal_bundle_transform` for FLAC rows
 *
 * Run: php benchmarks/container_representation_experiment.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$argvRoot = $argv[1] ?? null;
$defaultRoot = $repo . DIRECTORY_SEPARATOR . 'test_files60';
$root = ($argvRoot !== null && $argvRoot !== '')
	? (string) $argvRoot
	: (is_dir($defaultRoot) ? $defaultRoot : $repo);

$maxFiles = (int) (getenv('CONTAINER_EXP_MAX_FILES') !== false && trim((string) getenv('CONTAINER_EXP_MAX_FILES')) !== ''
	? getenv('CONTAINER_EXP_MAX_FILES')
	: 80);
$maxFiles = max(1, min(5000, $maxFiles));

$maxRead = (int) (getenv('CONTAINER_EXP_MAX_FILE_BYTES') !== false && trim((string) getenv('CONTAINER_EXP_MAX_FILE_BYTES')) !== ''
	? getenv('CONTAINER_EXP_MAX_FILE_BYTES')
	: 52428800);
if ($maxRead < 0) {
	$maxRead = 0;
}

$extCsv = getenv('CONTAINER_EXP_EXTENSIONS');
if ($extCsv === false || trim((string) $extCsv) === '') {
	$extCsv = 'flac,png,gz,zip';
}
$wantExt = array();
foreach (explode(',', strtolower((string) $extCsv)) as $e) {
	$e = trim($e);
	if ($e !== '') {
		$wantExt[$e] = true;
	}
}

$pcmProbe = getenv('CONTAINER_EXP_PCM') === '1';
$synthetic = getenv('CONTAINER_EXP_SYNTHETIC') === '1';

/**
 * @return list<string>
 */
function container_exp_collect_paths(string $root, int $maxFiles, array $wantExt): array {
	$out = array();
	if (is_file($root)) {
		$out[] = $root;
		return $out;
	}
	if (!is_dir($root)) {
		return $out;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS)
	);
	foreach ($it as $fi) {
		if (count($out) >= $maxFiles) {
			break;
		}
		if (!$fi->isFile()) {
			continue;
		}
		$p = $fi->getPathname();
		$ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
		if (!isset($wantExt[$ext])) {
			continue;
		}
		$out[] = $p;
	}
	sort($out);
	return $out;
}

/**
 * @return array{0: int, 1: string}|null
 */
function container_exp_deep_unwrap_pair(string $rel, string $bytes): ?array {
	fractal_zip_ensure_literal_pac_stack_loaded();
	try {
		list($work, $semanticLayers, $gzipStack) = fractal_zip_literal_deep_unwrap_with_layers($rel, $bytes);
		return array(strlen((string) $bytes), strlen((string) $work));
	} catch (Throwable $e) {
		return null;
	}
}

function container_exp_gz_lens(string $s, int $lev): array {
	$lens = array();
	foreach (array(1, $lev) as $L) {
		$z = @gzdeflate($s, $L);
		$lens[$L] = ($z === false) ? null : strlen($z);
	}
	return $lens;
}

$paths = container_exp_collect_paths($root, $maxFiles, $wantExt);
if ($paths === array()) {
	fwrite(STDERR, "No matching files under: {$root}\n");
	exit(1);
}

$fz = new fractal_zip(256, false, false, null, false);

fwrite(STDOUT, "# container_representation_experiment\n");
fwrite(STDOUT, '# root=' . $root . " max_files={$maxFiles} max_read_bytes='{$maxRead}' extensions='{$extCsv}'\n");
$hdr = "# columns: rel | raw_B | deep_same | gz1/raw | gzL/raw | lit_mode | lit_store_B | gz9(store)/store | pcm/raw";
if ($synthetic) {
	$hdr .= ' | nest_B | gz9(nest)/nest';
}
$hdr .= "\n";
fwrite(STDOUT, $hdr);

foreach ($paths as $abs) {
	$rel = ltrim(str_replace('\\', '/', substr($abs, strlen($root))), '/');
	if ($rel === '') {
		$rel = basename($abs);
	}
	$size = filesize($abs);
	if ($size === false) {
		continue;
	}
	$readLen = $maxRead === 0 ? (int) $size : min((int) $size, $maxRead);
	$raw = file_get_contents($abs, false, null, 0, $readLen);
	if ($raw === false) {
		continue;
	}
	$n = strlen($raw);
	if ($n < 1) {
		continue;
	}

	$deep = container_exp_deep_unwrap_pair($rel, $raw);
	$deepSame = ($deep === null) ? '?' : (($deep[0] === $deep[1]) ? '1' : '0');

	$probeLev = 9;
	$gz1 = container_exp_gz_lens($raw, $probeLev);
	$r1 = ($gz1[1] !== null && $n > 0) ? round($gz1[1] / $n, 6) : -1.0;
	$rL = ($gz1[$probeLev] !== null && $n > 0) ? round($gz1[$probeLev] / $n, 6) : -1.0;

	$litMode = '';
	$litStoreN = '';
	$litGz9r = '';
	if (strtolower((string) pathinfo($rel, PATHINFO_EXTENSION)) === 'flac' || strtolower((string) pathinfo($abs, PATHINFO_EXTENSION)) === 'flac') {
		ob_start();
		try {
			/** @var array{0: int, 1: string} $pick */
			$pick = $fz->choose_best_literal_bundle_transform($raw, $rel);
			$litMode = (string) (int) $pick[0];
			$st = (string) $pick[1];
			$litStoreN = (string) strlen($st);
			$z9 = @gzdeflate($st, 9);
			$litGz9r = ($z9 === false || strlen($st) === 0) ? '' : (string) round(strlen($z9) / strlen($st), 6);
		} catch (Throwable $e) {
			$litMode = 'err';
		}
		if (ob_get_level() > 0) {
			ob_end_clean();
		}
	} else {
		$litMode = '-';
		$litStoreN = '-';
		$litGz9r = '-';
	}

	$pcmRatio = '';
	if ($pcmProbe && strtolower((string) pathinfo($abs, PATHINFO_EXTENSION)) === 'flac') {
		fractal_zip_ensure_flac_pac_loaded();
		if (fractal_zip_flac_pac_tools_ok()) {
			$meta = fractal_zip_flac_pac_probe($abs);
			if ($meta !== null) {
				$pcmFmt = (int) $meta['pcm_fmt'];
				$t = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'crexp_' . bin2hex(random_bytes(6)) . '.pcm';
				if (fractal_zip_flac_pac_decode_file_to_pcm_path($abs, $pcmFmt, $t)) {
					$pn = is_file($t) ? (int) filesize($t) : 0;
					@unlink($t);
					if ($n > 0 && $pn > 0) {
						$pcmRatio = (string) round($pn / $n, 4);
					}
				} elseif (is_file($t)) {
					@unlink($t);
				}
			}
		}
		if ($pcmRatio === '') {
			$pcmRatio = '-';
		}
	} elseif ($pcmProbe) {
		$pcmRatio = '-';
	} else {
		$pcmRatio = '-';
	}

	$nestCol = '';
	if ($synthetic && strtolower((string) pathinfo($abs, PATHINFO_EXTENSION)) === 'flac') {
		$wrapped = @gzencode($raw, 9);
		if ($wrapped !== false) {
			$nb = strlen($wrapped);
			$zn = @gzdeflate($wrapped, 9);
			$nr = ($zn === false || $nb === 0) ? '' : (string) round(strlen($zn) / $nb, 6);
			$nestCol = "\t" . (string) $nb . "\t" . $nr;
		} else {
			$nestCol = "\t-\t-";
		}
	} elseif ($synthetic) {
		$nestCol = "\t-\t-";
	}

	fwrite(STDOUT, $rel . "\t" . (string) $n . "\t" . $deepSame . "\t" . (string) $r1 . "\t" . (string) $rL . "\t" . $litMode . "\t" . $litStoreN . "\t" . $litGz9r . "\t" . $pcmRatio . $nestCol . "\n");
}

fwrite(STDOUT, "# done\n");
fwrite(STDOUT, "# Interpretation: gz*/raw near 1.0 means zlib sees little headroom on that representation; pcm/raw (when probed)\n");
fwrite(STDOUT, "# shows expansion from decode. lit_* is the production literal picker on disk bytes (not default workflow change).\n");
fwrite(STDOUT, "# Synthetic nest: gz9(nest)/nest > 1 is common when the inner is already compressed — the gzip wrapper adds bytes.\n");
exit(0);
