<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';

/**
 * Example: compress files/folders to `.fzc` (fractal_zip).
 *
 * - **CLI:** `php fzc_compress.php <directory>` → one folder root; writes `<directory>.fzc` beside it.
 * - **Web:** choose or drop a **folder** or **loose file(s)**; each part sends `fzc_relpath[]` plus `files[]` because many hosts basename `$_FILES['files']['name']` and would otherwise flatten the tree on disk before `zip_folder`. The suggested download `.fzc` name is a **short (≈50 character) content label** built from file/folder names: long “catalog” filenames (e.g. `Title -- Author -- hash -- Anna’s Archive`) take the first substantive segment, drop hex blocks and source tails, then word-truncate. Multi-member trees prefer a non-generic folder or the longest file title. Optional `fzc_download_stem` on the final request overrides the suggested name.
 * - **Web:** drag-and-drop upload, progress, and download link (`?job=&dl=`). Needs a writable `web_jobs/` next to these scripts (or `FRACTAL_ZIP_WEB_JOBS`).
 *
 * Deploy: put `fzc_compress.php`, `fzc_web_shared.php`, and `fractal_zip.php` in the same directory (or keep repo `examples/` with `fractal_zip.php` in the parent). Set `FRACTAL_ZIP_PHP` if the library lives elsewhere.
 *
 * **Storage roadmap:** a future storage layer can register produced `.fzc` paths in an index (see `storage/`)
 * while still using this tool for ad-hoc packing.
 */

$isCli = PHP_SAPI === 'cli';

if (!$isCli) {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_web_shared.php';
	list($handlerBase, $handlerPath) = fzc_web_handler_identity('fzc_compress.php');
	if (isset($_GET['job'], $_GET['dl']) && is_string($_GET['job']) && is_string($_GET['dl'])) {
		fzc_web_stream_download($handlerBase, $_GET['job'], $_GET['dl']);
		exit;
	}
	if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
		fzc_web_silence_php_errors_for_json_api();
		fzc_web_register_fatal_json_shutdown();
		header('X-Content-Type-Options: nosniff');
		try {
			@set_time_limit(0);
			@ignore_user_abort(true);
			@ini_set('max_file_uploads', '2000');
			fzc_web_reject_body_too_large();
			fzc_web_reject_truncated_multipart_post();
			fzc_web_load_fractal_zip();

			$chunked = isset($_POST['fzc_chunked']) && (string) $_POST['fzc_chunked'] === '1';
			$finalize = !$chunked || (isset($_POST['fzc_finalize']) && (string) $_POST['fzc_finalize'] === '1');

			$jobIdIn = isset($_POST['fzc_job']) ? (string) $_POST['fzc_job'] : '';
			if ($jobIdIn !== '' && !preg_match('/^[a-f0-9]{32}$/', $jobIdIn)) {
				fzc_web_send_json(array('ok' => false, 'error' => 'Invalid job id'), 400);
			}

			if ($jobIdIn === '') {
				$jobId = fzc_web_new_job_dir();
				$jobRoot = fzc_web_jobs_root() . DIRECTORY_SEPARATOR . $jobId;
				$staging = $jobRoot . DIRECTORY_SEPARATOR . 'staging';
				if (!@mkdir($staging, 0755, true)) {
					throw new RuntimeException('Cannot create staging directory');
				}
			} else {
				$jobId = $jobIdIn;
				$jobRoot = fzc_web_jobs_root() . DIRECTORY_SEPARATOR . $jobId;
				$staging = $jobRoot . DIRECTORY_SEPARATOR . 'staging';
				if (!is_dir($staging)) {
					fzc_web_send_json(array('ok' => false, 'error' => 'Unknown or expired job'), 400);
				}
			}

			$nf = isset($_FILES['files']) ? fzc_web_upload_files_field_count($_FILES['files']) : 0;
			$relList = null;
			if ($nf > 0) {
				if (!isset($_POST['fzc_relpath'])) {
					fzc_web_send_json(array(
						'ok' => false,
						'error' => 'Missing fzc_relpath[] (reload the compress page). Paths must be sent explicitly because some servers strip folders from upload filenames.',
					), 400);
				}
				$relList = fzc_web_post_string_list($_POST['fzc_relpath']);
				if (count($relList) !== $nf) {
					fzc_web_send_json(array(
						'ok' => false,
						'error' => 'fzc_relpath[] length must match the number of file parts in this request.',
					), 400);
				}
			}

			$saved = fzc_web_save_upload_tree($staging, $relList, $jobRoot);
			$filesStaged = fzc_web_count_files_under($staging);
			if ($nf > 0 && $saved === array() && $filesStaged === 0) {
				fzc_web_remove_tree($jobRoot);
				fzc_web_send_json(array(
					'ok' => false,
					'error' => 'File upload did not complete: ' . fzc_web_summarize_files_field_errors(isset($_FILES['files']) ? $_FILES['files'] : null) . '. Check PHP upload_max_filesize, post_max_size, disk space, and temp directory.',
				), 400);
			}
			fzc_web_enforce_staging_limit($jobRoot, $staging);

			if (!$finalize) {
				if ($saved === array()) {
					if ($jobIdIn === '') {
						fzc_web_remove_tree($jobRoot);
					}
					fzc_web_send_json(array('ok' => false, 'error' => 'No files in this batch'), 400);
				}
				fzc_web_send_json(array(
					'ok' => true,
					'partial' => true,
					'job' => $jobId,
					'batch_saved' => count($saved),
					'files_staged_total' => $filesStaged,
				));
				exit;
			}

			if ($filesStaged === 0) {
				fzc_web_remove_tree($jobRoot);
				fzc_web_send_json(array(
					'ok' => false,
					'error' => 'Nothing to compress — no files were stored. Use “Choose file(s)” or a folder, or run `php fzc_compress.php /path/to/folder` on the server. If uploads should work, check PHP post_max_size / upload_max_filesize.',
				), 400);
			}

			if ($saved === array() && $jobIdIn === '') {
				fzc_web_remove_tree($jobRoot);
				fzc_web_send_json(array('ok' => false, 'error' => 'No files received. Drag files or a folder, or use the file picker.'), 400);
			}

			$obBase = ob_get_level();
			ob_start();
			try {
				$fz = new fractal_zip(null, true, true, null, true);
				$fz->zip_folder($staging, false);
			} finally {
				while (ob_get_level() > $obBase) {
					ob_end_clean();
				}
			}
			$fzcSidecar = $staging . '.fzc';
			if (!is_file($fzcSidecar)) {
				fzc_web_remove_tree($jobRoot);
				fzc_web_send_json(array('ok' => false, 'error' => 'Compressor did not write output. Check PHP memory/time limits.'), 500);
			}
			$parsed = fzc_web_relpath_read_job_manifest_parsed($jobRoot);
			$relsForName = (array) ($parsed['rels'] ?? array());
			$byClientName = (array) ($parsed['by_relname'] ?? array());
			if ($relsForName === array()) {
				$relsForName = fzc_compress_collect_staging_relpaths($staging);
			}
			/* One-shot upload (e.g. single PDF drag): this POST is the full job; use fzc_relpath + $_FILES names if manifest/walk were empty. */
			if ($relsForName === array() && $relList !== null && $filesStaged > 0 && count($relList) === $filesStaged) {
				$relsForName = array_values($relList);
				if ($byClientName === array() && isset($_FILES['files'])) {
					$byClientName = fzc_web_relpath_map_from_post_files($relsForName, $_FILES['files']);
				}
			}
			$archiveLeaf = fzc_compress_suggest_archive_leaf($relsForName, $byClientName);
			if ($finalize) {
				$ov = isset($_POST['fzc_download_stem']) ? (string) $_POST['fzc_download_stem'] : '';
				$archiveLeaf = fzc_compress_apply_download_stem_override($archiveLeaf, $ov);
			}
			$archivePath = $jobRoot . DIRECTORY_SEPARATOR . $archiveLeaf;
			if (!@rename($fzcSidecar, $archivePath)) {
				throw new RuntimeException('Could not finalize archive path');
			}
			fzc_web_remove_tree($staging);
			clearstatcache(true, $archivePath);
			$sz = filesize($archivePath);
			$dl = $handlerPath . '?job=' . rawurlencode($jobId) . '&dl=' . rawurlencode($archiveLeaf);
			fzc_web_send_json(array(
				'ok' => true,
				'job' => $jobId,
				'bytes' => is_int($sz) ? $sz : 0,
				'download_url' => $dl,
				'download_filename' => $archiveLeaf,
				'files_packed' => $filesStaged,
			));
		} catch (fzc_web_UnsafePathException $e) {
			fzc_web_send_json(array('ok' => false, 'error' => $e->getMessage()), 400);
		} catch (Throwable $e) {
			fzc_web_send_json(array('ok' => false, 'error' => $e->getMessage()), 500);
		}
		exit;
	}
	fzc_compress_render_web_ui($handlerBase, $handlerPath);
	exit;
}

$lib = getenv('FRACTAL_ZIP_PHP');
if ($lib === false || trim((string) $lib) === '') {
	$lib = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip.php';
}
if (!is_file($lib)) {
	$lib = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';
}
if (!is_file($lib)) {
	fwrite(STDERR, "Cannot find fractal_zip.php. Copy the library beside this script or set FRACTAL_ZIP_PHP.\n");
	exit(1);
}
require_once $lib;

$argvList = array_slice($argv, 1);
if ($argvList === [] || $argvList[0] === '-h' || $argvList[0] === '--help') {
	echo "Usage: php fzc_compress.php <directory>\nWrites <directory>.fzc next to the folder.\n";
	exit($argvList === [] ? 1 : 0);
}

$dir = $argvList[0];
$abs = realpath($dir);
if ($abs === false || !is_dir($abs)) {
	fwrite(STDERR, "Not a directory: {$dir}\n");
	exit(1);
}

$fzcPath = $abs . '.fzc';
$verbose = getenv('FRACTAL_ZIP_CLI_VERBOSE') === '1';
if (!$verbose) {
	ob_start();
}
$fz = new fractal_zip(null, true, true, null, true);
$fz->zip_folder($abs, false);
if (!$verbose) {
	ob_end_clean();
}

if (!is_file($fzcPath)) {
	fwrite(STDERR, "Expected output missing: {$fzcPath}\n");
	exit(1);
}
$sz = filesize($fzcPath);
echo 'Wrote ' . $fzcPath . ' (' . ($sz !== false ? (string) $sz : '?') . " bytes)\n";

/**
 * @return list<string> relative paths under $stagingRoot using forward slashes
 */
function fzc_compress_collect_staging_relpaths(string $stagingRoot): array {
	if (!is_dir($stagingRoot)) {
		return array();
	}
	$rp = @realpath($stagingRoot);
	if ($rp === false) {
		$rp = $stagingRoot;
	}
	$base = rtrim(str_replace('\\', '/', (string) $rp), '/');
	$out = array();
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($rp, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::LEAVES_ONLY
	);
	$isWin = (defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows') || (PHP_OS === 'Windows NT');
	$pl = (string) ($base . '/');
	$plLen = strlen($pl);
	foreach ($it as $fi) {
		if (!$fi instanceof SplFileInfo || !$fi->isFile()) {
			continue;
		}
		$gr = $fi->getRealPath();
		$raw = $gr !== false ? (string) $gr : (string) $fi->getPathname();
		$full = str_replace('\\', '/', $raw);
		$ok = (strlen($full) > $plLen && 0 === strncmp($full, $pl, $plLen));
		if (!$ok && $isWin) {
			$ok = (strlen($full) > $plLen && 0 === strncasecmp($full, $pl, $plLen));
		}
		if (!$ok) {
			$raw2 = str_replace('\\', '/', (string) $fi->getPathname());
			$ok = (strlen($raw2) > $plLen && 0 === strncmp($raw2, $pl, $plLen));
			if (!$ok && $isWin) {
				$ok = (strlen($raw2) > $plLen && 0 === strncasecmp($raw2, $pl, $plLen));
			}
			if ($ok) {
				$full = $raw2;
			}
		}
		if (!$ok) {
			continue;
		}
		$out[] = (string) substr($full, $plLen);
	}
	sort($out, SORT_STRING);
	return $out;
}

function fzc_compress_str_ends_with_ci(string $haystack, string $suffix): bool {
	$len = strlen($suffix);
	if ($len === 0) {
		return true;
	}
	if (strlen($haystack) < $len) {
		return false;
	}
	return strcasecmp(substr($haystack, -$len), $suffix) === 0;
}

/**
 * File stem for naming: prefer the original multipart `$_FILES['name']` for this member relpath when present.
 *
 * @param array<string, string> $clientNameByRel
 */
function fzc_compress_stem_for_member_path(string $relpath, array $clientNameByRel): string {
	$relpath = str_replace('\\', '/', (string) $relpath);
	$fromRel = (string) pathinfo(basename($relpath), PATHINFO_FILENAME);
	$raw = (string) ($clientNameByRel[$relpath] ?? '');
	if ($raw === '') {
		$raw = (string) ($clientNameByRel[str_replace('/', '\\', $relpath)] ?? '');
	}
	if ($raw === '') {
		return $fromRel;
	}
	$raw = (string) pathinfo(basename(str_replace('\\', '/', (string) $raw)), PATHINFO_FILENAME);
	if ($raw === '' || (strlen($raw) < strlen($fromRel) && strlen($fromRel) > 3)) {
		return $fromRel;
	}
	return $raw;
}

/** Suggested .fzc base (stem) length, UTF-8 characters, excluding the ".fzc" extension. */
function fzc_compress_repr_stem_max(): int {
	return 50;
}

/**
 * Heuristic: pull a short human-meaningful label from a long file stem (Anna’s Archive, LibGen, etc.):
 * drop hex-only --segments, “Anna’s Archive” tails, and keep the first substantive “title” segment when split on --.
 */
function fzc_compress_representative_raw_stem_from_filename_stem(string $fileStem): string {
	$fileStem = trim($fileStem);
	if ($fileStem === '') {
		return '';
	}
	$parts = preg_split('/\s*--\s*+/u', $fileStem);
	$parts = is_array($parts) ? $parts : array($fileStem);
	$kept = array();
	foreach ($parts as $p) {
		$p = trim($p);
		if ($p === '') {
			continue;
		}
		$compact = preg_replace('/\s+/', '', $p);
		$isHex = $compact !== null && (bool) @preg_match('/^(?:0x)?[0-9a-f]+$/i', (string) $compact) && strlen((string) $compact) >= 8;
		if ($isHex) {
			continue;
		}
		$pl = fzc_compress_fold_for_noise_match($p);
		$norm1 = (string) preg_replace('/\s+/', ' ', (string) $pl);
		$norm0 = (string) preg_replace('/\s+/', '', (string) $pl);
		/* Common “catalog” provenance segments, not the work title (Anna’s Archive, LibGen, Z-Library, etc.) */
		$catalogTails = array('annas archive', 'annas', 'libgen', 'library genesis', 'z library', 'zlibrary', 'sci hub', 'scihub', 'pdf drive', 'pdfdrive');
		$catalog0 = array('annasarchive', 'libgen', 'librarygenesis', 'zlibrary', 'scihub', 'pdfdrive');
		$isCatalog = in_array($norm1, $catalogTails, true) || in_array($norm0, $catalog0, true);
		if ($isCatalog) {
			continue;
		}
		$kept[] = $p;
	}
	if (count($kept) > 0) {
		/* First segment is almost always the title in “Title -- Author -- hash -- Source” */
		$out = (string) $kept[0];
	} else {
		$out = fzc_compress_strip_embedded_long_hex($fileStem);
		$out = trim($out);
	}
	$out = fzc_compress_strip_embedded_long_hex($out);
	$out = fzc_compress_truncate_representative_string($out, fzc_compress_repr_stem_max());
	return (string) $out;
}

/**
 * Lowercase, strip possessive apostrophes, collapse spaces; for matching catalog tails (“Anna’s Archive” → annas archive)
 */
function fzc_compress_fold_for_noise_match(string $s): string {
	$s = trim($s);
	$s = str_replace(array("'", '’', '`', '´', '‛', '＇'), '', $s);
	$s = (string) @preg_replace('/\s+/', ' ', $s);
	$s = trim($s, ' -–—_');
	if (function_exists('mb_strtolower')) {
		$lo = @mb_strtolower($s, 'UTF-8');
		return (string) ($lo !== null && is_string($lo) ? $lo : strtolower($s));
	}
	return (string) strtolower($s);
}

/** Remove 8+ char hex “checksum” tokens anywhere in a title string. */
function fzc_compress_strip_embedded_long_hex(string $s): string {
	$t = (string) @preg_replace('/\b[0-9a-f]{8,}\b/iu', ' ', $s);
	if (!is_string($t) || preg_last_error() !== PREG_NO_ERROR) {
		$t = (string) preg_replace('/[0-9a-f]{8,}/i', ' ', $s);
	}
	$t = (string) @preg_replace('/\s+/', ' ', trim($t));
	return (string) $t;
}

/**
 * Truncate to at most $max UTF-8 characters, preferably at the last space or dash before the limit.
 */
function fzc_compress_truncate_representative_string(string $s, int $max): string {
	$s = trim($s);
	if ($max < 1) {
		return '';
	}
	$enc = 'UTF-8';
	$u = (bool) @preg_match('//u', $s) && function_exists('mb_strlen') && function_exists('mb_substr') && function_exists('mb_strrpos');
	if ($u) {
		$n = (int) mb_strlen($s, $enc);
		if ($n <= $max) {
			return $s;
		}
		$chunk = (string) mb_substr($s, 0, $max, $enc);
	} else {
		if (strlen($s) <= $max) {
			return $s;
		}
		$chunk = (string) substr($s, 0, $max);
		$u = false;
	}
	$seps = array(' ', '-', '–', '—', '_', '.', ',', '·', ':');
	$best = -1;
	for ($i = 0, $c = count($seps); $i < $c; $i++) {
		$w = $seps[$i];
		if ($u) {
			$pos = mb_strrpos($chunk, $w, 0, $enc);
		} else {
			$pos = strrpos($chunk, $w);
		}
		if ($pos !== false && (int) $pos > $best) {
			$best = (int) $pos;
		}
	}
	$minBreak = (int) max(8, (int) ($max * 0.4));
	if ($best >= $minBreak) {
		$out = $u
			? (string) mb_substr($chunk, 0, $best, $enc)
			: (string) substr($chunk, 0, $best);
		return (string) rtrim($out, " \t-–—_.,:;");
	}
	return (string) rtrim($chunk, " \t-–—_.,:;");
}

/**
 * Build a short representative stem for many member relpaths (uses folder and/or file names).
 * @param list<string>         $rels
 * @param array<string,string> $clientNameByRel optional client names from $_FILES[] keyed by relpath
 */
function fzc_compress_representative_raw_stem_for_rel_paths(array $rels, array $clientNameByRel = array()): string {
	$rels = array_values($rels);
	$out = '';
	if ($rels === array()) {
		return $out;
	}
	$longest = '';
	$maxL = 0;
	foreach ($rels as $r) {
		$r = str_replace('\\', '/', (string) $r);
		$bn = fzc_compress_stem_for_member_path($r, $clientNameByRel);
		$cand = fzc_compress_representative_raw_stem_from_filename_stem($bn);
		$l = function_exists('mb_strlen') && (bool) @preg_match('//u', $cand) ? (int) mb_strlen($cand) : strlen($cand);
		if ($l > $maxL) {
			$maxL = $l;
			$longest = $cand;
		}
	}
	$first = $rels[0];
	$root = (string) (strpos($first, '/') === false ? $first : (string) substr($first, 0, (int) strpos($first, '/')));
	$root = str_replace('\\', '/', $root);
	$rootStem = fzc_compress_representative_raw_stem_from_filename_stem($root);
	$lRoot = function_exists('mb_strlen') && (bool) @preg_match('//u', $rootStem) ? (int) mb_strlen($rootStem) : strlen($rootStem);
	$rootFold = fzc_compress_fold_for_noise_match($root);
	$generic = (bool) preg_match('/^(files?|download|uploads?|data|source|test_files\d+)$/i', (string) $rootFold) || (bool) preg_match('/^(documents?|desktop|pictures?)$/i', (string) $rootFold);
	if ($generic && $longest !== '') {
		$out = fzc_compress_truncate_representative_string($longest, fzc_compress_repr_stem_max());
	} elseif ($lRoot >= 4 && !$generic && fzc_compress_count_top_roots($rels) === 1) {
		$out = fzc_compress_truncate_representative_string($rootStem, fzc_compress_repr_stem_max());
	} else {
		$out = $longest !== '' ? fzc_compress_truncate_representative_string($longest, fzc_compress_repr_stem_max()) : fzc_compress_truncate_representative_string($rootStem, fzc_compress_repr_stem_max());
	}
	return $out;
}

/**
 * @param list<string> $rels
 */
function fzc_compress_count_top_roots(array $rels): int {
	$segs = array();
	foreach ($rels as $r) {
		$r = str_replace('\\', '/', (string) $r);
		$slash = strpos($r, '/');
		$k = $slash === false ? $r : (string) substr($r, 0, $slash);
		$segs[$k] = true;
	}
	return count($segs);
}

/**
 * Safe single path segment / archive stem (no slashes).
 * Preserves any Unicode letter or number (\p{L}\p{N}) so non-English filenames are not stripped to the generic "archive".
 *
 * @param bool   $returnEmpty If true, return '' when the name has no safe characters (caller can pick a hash-based stem).
 * @param int    $maxLen      After sanitizing, cap length (UTF-8) for download stem.
 */
function fzc_compress_sanitize_archive_stem(string $stem, bool $returnEmpty = false, int $maxLen = 50): string {
	$stem = trim($stem);
	if ($stem === '') {
		return $returnEmpty ? '' : 'archive';
	}
	$from = $stem;
	$stem = (string) @preg_replace('/[^\p{L}\p{N}._-]+/u', '-', $from);
	if (preg_last_error() !== PREG_NO_ERROR) {
		$stem = (string) preg_replace('/[^a-zA-Z0-9._-]+/', '-', $from);
	}
	$stem = (string) preg_replace('/-+/', '-', $stem);
	$stem = trim($stem, '-._');
	if ($stem !== '' && (function_exists('mb_strlen') && (bool) @preg_match('//u', $stem) ? (int) mb_strlen($stem, 'UTF-8') : strlen($stem)) > $maxLen) {
		if (function_exists('mb_substr') && (bool) @preg_match('//u', $stem)) {
			$stem = (string) mb_substr($stem, 0, $maxLen, 'UTF-8');
		} else {
			$stem = (string) substr($stem, 0, $maxLen);
		}
		$stem = rtrim($stem, '-._');
	}
	if ($stem === '') {
		return $returnEmpty ? '' : 'archive';
	}
	return $stem;
}

/**
 * @return true if leaf is a safe one-segment .fzc basename for a job + download (ASCII or UTF-8 name)
 */
function fzc_compress_is_valid_download_leaf(string $leaf): bool {
	$leaf = trim(str_replace('\\', '/', $leaf), '/');
	if ($leaf === '' || strpos($leaf, '/') !== false) {
		return false;
	}
	$n = strlen($leaf);
	if ($n < 5) {
		return false;
	}
	$l = $n - 4;
	if ($l < 1 || $l > 200) {
		return false;
	}
	$end = (string) substr($leaf, -4);
	if (strtolower($end) !== '.fzc') {
		return false;
	}
	$base = (string) substr($leaf, 0, -4);
	if ($base === '' || $base === '.' || $base === '..' || strpos($base, "\0") !== false) {
		return false;
	}
	/* Windows-unsafe; keep the single segment URL-safe and filesystem-safe */
	$forbidden = strpbrk($base, '<>:"|*?\\');
	if ($forbidden !== false) {
		return false;
	}
	/* ASCII path separators / controls (avoids PREG u issues on some malformed UTF-8) */
	if (preg_match('/[\x00-\x1F\x7F]/', $base) === 1) {
		return false;
	}
	return true;
}

/**
 * When the user passes an optional download stem, apply it if it still validates after sanitize.
 */
function fzc_compress_apply_download_stem_override(string $defaultLeaf, string $raw): string {
	$raw = trim($raw);
	if ($raw === '') {
		return $defaultLeaf;
	}
	$stem = $raw;
	if (fzc_compress_str_ends_with_ci($stem, '.fzc')) {
		$stem = substr($stem, 0, (int) strlen($stem) - 4);
	}
	$mx = fzc_compress_repr_stem_max();
	$stem = fzc_compress_sanitize_archive_stem($stem, false, $mx);
	$cand = $stem . '.fzc';
	if (fzc_compress_is_valid_download_leaf($cand)) {
		return $cand;
	}
	return $defaultLeaf;
}

/**
 * Pick a short `.fzc` leaf name from staged member paths (after chunked upload the tree is on disk).
 *
 * @param list<string>         $rels
 * @param array<string,string> $clientNameByRel original `$_FILES['files']['name']` per relpath (from upload manifest)
 */
function fzc_compress_suggest_archive_leaf(array $rels, array $clientNameByRel = array()): string {
	$rels = array_values(array_filter(array_map('strval', $rels), static function ($r) {
		return $r !== '';
	}));
	foreach ($rels as $i => $r) {
		$rels[$i] = str_replace('\\', '/', $r);
	}
	if ($rels === array()) {
		return 'archive.fzc';
	}
	sort($rels, SORT_STRING);

	$mx = fzc_compress_repr_stem_max();
	// One member path: name from the *file* (e.g. one PDF under a folder), not the parent directory.
	if (count($rels) === 1) {
		$one = $rels[0];
		$rawStem = fzc_compress_stem_for_member_path($one, $clientNameByRel);
		$label = fzc_compress_representative_raw_stem_from_filename_stem($rawStem);
		$stem = fzc_compress_sanitize_archive_stem($label, true, $mx);
		if ($stem === '') {
			$stem = 'fzc-' . substr(sha1((string) $one), 0, 10);
		}
		return fzc_compress_validate_archive_leaf($stem . '.fzc');
	}

	$firstSegs = array();
	foreach ($rels as $r) {
		$slash = strpos($r, '/');
		$firstSegs[$slash === false ? $r : substr($r, 0, $slash)] = true;
	}
	$roots = array_keys($firstSegs);
	sort($roots, SORT_STRING);

	if (count($roots) === 1) {
		$root = $roots[0];
		$allUnder = true;
		foreach ($rels as $r) {
			if ($r !== $root && strpos($r, $root . '/') !== 0) {
				$allUnder = false;
				break;
			}
		}
		if ($allUnder) {
			$hasChildPath = false;
			foreach ($rels as $r) {
				if ($r !== $root && strpos($r, $root . '/') === 0) {
					$hasChildPath = true;
					break;
				}
			}
			if ($hasChildPath) {
				$raw = fzc_compress_representative_raw_stem_for_rel_paths($rels, $clientNameByRel);
				$stem = fzc_compress_sanitize_archive_stem($raw, true, $mx);
				if ($stem === '') {
					$stem = fzc_compress_sanitize_archive_stem(
						fzc_compress_representative_raw_stem_from_filename_stem($root),
						false,
						$mx
					);
				}
				if ($stem === '') {
					$stem = 'fzc-' . substr(sha1(serialize($rels)), 0, 10);
				}
				return fzc_compress_validate_archive_leaf($stem . '.fzc');
			}
		}
	}

	$important = array('.exe', '.msi', '.msix', '.deb', '.rpm', '.dmg', '.pkg', '.appimage');
	foreach ($rels as $r) {
		$low = strtolower($r);
		foreach ($important as $ext) {
			if (fzc_compress_str_ends_with_ci($low, $ext)) {
				$st0 = fzc_compress_stem_for_member_path($r, $clientNameByRel);
				$label = fzc_compress_representative_raw_stem_from_filename_stem($st0);
				$fn = fzc_compress_sanitize_archive_stem($label, false, $mx);
				return fzc_compress_validate_archive_leaf($fn . '.fzc');
			}
		}
	}

	if (count($roots) >= 2 && count($roots) <= 4) {
		$nr0 = count($roots);
		$per = (int) max(8, (int) (floor(48 / max(1, $nr0)) - 1));
		$bits = array();
		foreach ($roots as $seg) {
			$seg = (string) $seg;
			$stSeg = fzc_compress_stem_for_member_path($seg, $clientNameByRel);
			$t = fzc_compress_representative_raw_stem_from_filename_stem($stSeg);
			$bits[] = fzc_compress_sanitize_archive_stem(
				fzc_compress_truncate_representative_string($t, $per), false, $per
			);
		}
		$joined0 = implode('-', $bits);
		$joined0 = fzc_compress_truncate_representative_string($joined0, $mx);
		$fn = fzc_compress_sanitize_archive_stem($joined0, false, $mx);
		if ($fn === '' || $fn === 'archive') {
			$fn = fzc_compress_sanitize_archive_stem('pack-' . (string) (int) $nr0 . '-items', false, $mx);
		}
		return fzc_compress_validate_archive_leaf($fn . '.fzc');
	}

	$n = count($rels);
	$nr = count($roots);
	$suffix = $nr > 1 ? (string) min(9999, $nr) . '-roots' : (string) min(99999, $n) . '-files';
	return fzc_compress_validate_archive_leaf('pack-' . $suffix . '.fzc');
}

/** @return string basename ending in .fzc safe for job dir + download */
function fzc_compress_validate_archive_leaf(string $leaf): string {
	$leaf = trim(str_replace('\\', '/', $leaf), '/');
	if (!fzc_compress_is_valid_download_leaf($leaf)) {
		return 'archive.fzc';
	}
	return $leaf;
}

/**
 * @param string $handlerBase basename of the PHP entry (e.g. fzc_compress.php) for links and footer
 * @param string $handlerPath path from site root for POST (may differ from browser URL when UI is .html)
 */
function fzc_compress_render_web_ui(string $handlerBase, string $handlerPath): void {
	header('Content-Type: text/html; charset=UTF-8');
	header('X-Content-Type-Options: nosniff');
	$h = htmlspecialchars($handlerBase, ENT_QUOTES, 'UTF-8');
	$postJson = json_encode($handlerPath, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
	$maxBytesJson = json_encode(fzc_web_max_upload_bytes(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
	echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="dark">
<title>Compress to .fzc</title>
<style>
:root {
	color-scheme: dark;
	--bg: #0c0e12;
	--bg-elev: #141820;
	--border: #2a3344;
	--text: #e8eaed;
	--muted: #9aa3b2;
	--accent: #38bdf8;
	--accent-dim: #0ea5e9;
	--link: #7dd3fc;
	--focus: #fbbf24;
	font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
	line-height: 1.5;
}
*, *::before, *::after { box-sizing: border-box; }
html { background: var(--bg); }
body {
	margin: 0;
	min-height: 100vh;
	background: var(--bg);
	color: var(--text);
	max-width: 44rem;
	margin-inline: auto;
	padding: 1.5rem 1rem 2.5rem;
}
h1 { font-size: 1.35rem; font-weight: 600; margin: 0 0 0.75rem; }
p.intro { color: var(--muted); margin: 0 0 1rem; }
.dropzone {
	border: 2px dashed var(--accent);
	border-radius: 12px;
	padding: 2rem 1.25rem;
	text-align: center;
	background: var(--bg-elev);
	color: var(--text);
	cursor: pointer;
	margin: 1rem 0;
	transition: border-color 0.15s, background 0.15s;
}
.dropzone:hover { border-color: var(--accent-dim); }
.dropzone:focus { outline: 2px solid var(--focus); outline-offset: 2px; }
.dropzone.drag { border-color: #22d3ee; background: #1a2332; }
.dropzone p { margin: 0.35rem 0; color: var(--muted); }
.dropzone strong { color: var(--text); }
button.primary {
	background: var(--accent-dim);
	color: #041018;
	border: 0;
	padding: 0.6rem 1.2rem;
	border-radius: 8px;
	cursor: pointer;
	font-size: 1rem;
	font-weight: 600;
}
button.primary:hover { filter: brightness(1.08); }
button.primary:focus-visible { outline: 2px solid var(--focus); outline-offset: 2px; }
button.primary:disabled { opacity: 0.45; cursor: not-allowed; filter: none; }
.progress-block {
	margin: 1rem 0;
	padding: 0.75rem 0;
	border-top: 1px solid var(--border);
}
.progress-block[hidden] { display: none !important; }
.progress-block label {
	display: block;
	font-size: 0.8rem;
	text-transform: uppercase;
	letter-spacing: 0.04em;
	color: var(--muted);
	margin-bottom: 0.35rem;
}
.progress-block progress {
	width: 100%;
	height: 12px;
	border-radius: 6px;
	overflow: hidden;
	accent-color: var(--accent-dim);
}
.status { min-height: 1.5rem; margin: 0.75rem 0; font-size: 0.95rem; color: var(--muted); }
.status.error { color: #f87171; }
.result { margin-top: 0.75rem; }
.result a { color: var(--link); word-break: break-all; }
.result a:hover { text-decoration: underline; }
.result code { font-size: 0.85em; }
footer {
	margin-top: 2rem;
	font-size: 0.85rem;
	color: var(--muted);
	border-top: 1px solid var(--border);
	padding-top: 1rem;
}
footer strong { color: var(--text); }
code {
	background: var(--bg-elev);
	padding: 0.12em 0.4em;
	border-radius: 4px;
	border: 1px solid var(--border);
	font-size: 0.9em;
}
.visually-hidden { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
body.page-drop-active {
	box-shadow: inset 0 0 0 3px var(--accent);
	background: #12151c;
	min-height: 100vh;
}
.warn-long {
	margin: 1rem 0;
	padding: 0.75rem 1rem;
	border-radius: 8px;
	border: 1px solid #b45309;
	background: rgba(180, 83, 9, 0.12);
	color: #fcd34d;
	font-size: 0.92rem;
}
.warn-long strong { color: #fde68a; }
.warn-long code { font-size: 0.88em; }
.compress-hint {
	margin: 0.25rem 0 0;
	font-size: 0.88rem;
	color: var(--muted);
	line-height: 1.45;
	max-width: 38rem;
}
.compress-hint kbd { font-family: inherit; font-size: 0.95em; color: var(--text); }
#workBlock[aria-busy="true"] label { color: var(--accent); }
</style>
</head>
<body>
<h1>Pack to <code>.fzc</code></h1>
<p class="intro">Member paths inside the <code>.fzc</code> are the relative paths you pack: from a <strong>folder</strong> (best — full tree), or for <strong>loose file(s)</strong> the archive uses each file’s name at the root (e.g. <code>products_export_1.csv</code>). <strong>Choose a folder</strong>, <strong>choose file(s)</strong>, or drop a folder / files below. Several top-level folders at once work best in Chromium (directory entries). <strong>Web demo:</strong> upload size cap in the footer; huge trees need the CLI.</p>
<p class="compress-hint" style="margin:0.5rem 0 0.75rem;max-width:40rem">After upload, <strong>building the <code>.fzc</code> on the server</strong> can take <strong>several minutes</strong> for PDFs and other large files (you will see a timer and status line). This is expected, not a hang.</p>
<p class="warn-long" id="sizeWarn" hidden role="status"></p>
<div class="dropzone" id="dz" tabindex="0" role="button" aria-label="Choose folder or drop files">
	<p><strong>Click here</strong> to choose a <strong>folder</strong> (full paths)</p>
	<p>or drop a <strong>folder</strong> / <strong>file(s)</strong> anywhere on the page</p>
</div>
<input type="file" id="pickDir" webkitdirectory="" directory="" multiple class="visually-hidden" aria-hidden="true" title="Choose folder to pack" />
<input type="file" id="pickLoose" multiple class="visually-hidden" aria-hidden="true" title="Choose one or more files" />
<p><button type="button" class="primary" id="go" disabled>Compress to .fzc</button></p>
<p class="optional-name" id="downloadStemRow" hidden style="margin:0.4rem 0 0;font-size:0.9rem;color:var(--muted)">
	<label for="fzcDownloadStem">Save download as (optional, without <code>.fzc</code>):</label>
	<input type="text" id="fzcDownloadStem" autocomplete="off" maxlength="120" size="28" style="margin-left:0.35rem;padding:0.3rem 0.5rem;border-radius:6px;border:1px solid var(--border);background:var(--bg-elev);color:var(--text);font:inherit" />
</p>
<p style="margin:-0.25rem 0 0;font-size:0.9rem"><button type="button" id="btnPickLoose" style="background:transparent;border:1px solid var(--border);color:var(--link);padding:0.35rem 0.75rem;border-radius:6px;cursor:pointer;font:inherit">Choose file(s)…</button> <span style="color:var(--muted)">(single file or many — names at archive root)</span></p>
<div class="progress-block" id="uploadBlock" hidden>
	<label for="uploadBar">Upload progress</label>
	<progress id="uploadBar" value="0" max="100"></progress>
</div>
<div class="progress-block" id="workBlock" hidden aria-busy="false">
	<label for="workBar">Building your <code>.fzc</code> on the server</label>
	<progress id="workBar" aria-label="Fractal compression in progress"></progress>
	<p class="compress-hint" id="compressHint" hidden>Fractal_zip is still running. For large PDFs or big trees this often takes <strong>several minutes</strong> — the bar cannot show inner steps, but work continues until a download link appears below. <kbd id="compressElapsed" hidden></kbd></p>
</div>
<p class="status" id="status" role="status"></p>
<div class="result" id="result"></div>
<footer>
	<p><strong>CLI unchanged:</strong> <code>php {$h} /path/to/folder</code></p>
	<p><strong>Jobs directory:</strong> uses <code>web_jobs/</code> next to these scripts if writable; otherwise a folder under the system temp directory. Override with env <code>FRACTAL_ZIP_WEB_JOBS</code> (absolute path). A catalogued storage layer can index <code>.fzc</code> paths separately (see repo <code>storage/</code>).</p>
	<p><strong>Many files:</strong> PHP defaults to <code>max_file_uploads = 20</code>; this page uploads in batches of 15 so large trees are not truncated.</p>
	<p><strong>Upload cap (demo):</strong> staged total is capped by <code>FZC_WEB_MAX_UPLOAD_BYTES</code> (default 8&nbsp;MiB; set <code>0</code> to disable). PHP must allow at least that per file: use <code>examples/.user.ini</code> (FPM/CGI), <code>examples/.htaccess</code> (Apache mod_php), or <code>php -d upload_max_filesize=8M -d post_max_size=16M</code> for the built-in server; nginx needs <code>client_max_body_size</code> (at least <code>8m</code>) too.</p>
</footer>
<script>
(function () {
	const dz = document.getElementById('dz');
	const pickDir = document.getElementById('pickDir');
	const pickLoose = document.getElementById('pickLoose');
	const go = document.getElementById('go');
	const uploadBar = document.getElementById('uploadBar');
	const workBar = document.getElementById('workBar');
	const uploadBlock = document.getElementById('uploadBlock');
	const workBlock = document.getElementById('workBlock');
	const status = document.getElementById('status');
	const result = document.getElementById('result');
	const sizeWarn = document.getElementById('sizeWarn');
	const compressHint = document.getElementById('compressHint');
	const compressElapsed = document.getElementById('compressElapsed');
	const downloadStemRow = document.getElementById('downloadStemRow');
	const fzcDownloadStem = document.getElementById('fzcDownloadStem');
	let queue = [];
	let compressTickTimer = null;
	let compressTickStart = 0;

	const LONG_HINT_SEC = 120;
	/* Orange banner before compress: anything above this total size (or many files) gets the long‑job notice */
	const COMPRESS_WARN_BYTES = 400 * 1024;
	const COMPRESS_WARN_FILES = 400;
	const MAX_FILES_PER_POST = 15;
	const compressPostUrl = {$postJson};
	const FZC_WEB_MAX_UPLOAD_BYTES = {$maxBytesJson};

	function human(n) { return n < 1024 ? n + ' B' : n < 1048576 ? (n/1024).toFixed(1) + ' KiB' : (n/1048576).toFixed(2) + ' MiB'; }

	function refreshCompressHeavyWarning() {
		if (!sizeWarn) return;
		let total = 0;
		for (let i = 0; i < queue.length; i++) total += queue[i].size || 0;
		const heavy = total >= COMPRESS_WARN_BYTES || queue.length >= COMPRESS_WARN_FILES;
		if (heavy) {
			sizeWarn.hidden = false;
			sizeWarn.textContent = 'Larger job (' + human(total) + ', ' + queue.length + ' item(s)): after upload, the server can spend many minutes building a smaller .fzc (literal-PAC, fractal search, outer codec). A timer and status line will show while that runs — it is not stuck. set_time_limit(0) is used; your host or proxy may still cap time or body size.';
		} else {
			sizeWarn.hidden = true;
			sizeWarn.textContent = '';
		}
		if (downloadStemRow) {
			downloadStemRow.hidden = queue.length === 0;
		}
	}

	function stopCompressReassurance() {
		if (compressTickTimer) {
			clearInterval(compressTickTimer);
			compressTickTimer = null;
		}
		compressTickStart = 0;
		if (workBlock) {
			workBlock.setAttribute('aria-busy', 'false');
		}
		if (compressHint) {
			compressHint.hidden = true;
		}
		if (compressElapsed) {
			compressElapsed.hidden = true;
			compressElapsed.textContent = '';
		}
	}

	function startCompressReassurance() {
		stopCompressReassurance();
		compressTickStart = Date.now();
		if (workBlock) {
			workBlock.setAttribute('aria-busy', 'true');
		}
		if (compressHint) {
			compressHint.hidden = false;
		}
		if (compressElapsed) {
			compressElapsed.hidden = false;
		}
		function tick() {
			const sec = Math.floor((Date.now() - compressTickStart) / 1000);
			if (status) {
				const m = Math.floor(sec / 60);
				const s = sec % 60;
				const timeStr = m > 0 ? m + 'm ' + s + 's' : sec + 's';
				status.textContent = 'Compressing on the server — still working (' + timeStr + '). Large PDFs and deep trees can take many minutes; this is normal.';
			}
			if (compressElapsed) {
				compressElapsed.textContent = 'Elapsed: ' + sec + 's';
			}
		}
		tick();
		compressTickTimer = setInterval(tick, 2000);
	}

	function hideProgress() {
		stopCompressReassurance();
		uploadBlock.hidden = true;
		workBlock.hidden = true;
	}

	function parseJsonResponse(raw, xhr) {
		const t = (raw || '').trim();
		try { return JSON.parse(t); } catch (e1) {
			const start = t.indexOf('{');
			const end = t.lastIndexOf('}');
			if (start !== -1 && end > start) {
				try { return JSON.parse(t.slice(start, end + 1)); } catch (e2) { /* fall through */ }
			}
			const hint = t.length > 160 ? t.slice(0, 160) + '…' : t;
			const err = new Error('Server did not return JSON (HTTP ' + xhr.status + ').');
			err.hint = hint;
			throw err;
		}
	}

	dz.addEventListener('click', () => pickDir.click());
	document.getElementById('btnPickLoose').addEventListener('click', () => pickLoose.click());
	pickLoose.addEventListener('change', () => {
		queue = assignLooseRelativePaths(pickLoose.files);
		go.disabled = queue.length === 0;
		status.textContent = queue.length ? queue.length + ' file(s) ready (member path = filename).' : '';
		status.classList.remove('error');
		refreshCompressHeavyWarning();
	});

	function endPageDragVisual() {
		document.body.classList.remove('page-drop-active');
		dz.classList.remove('drag');
	}
	function dataTransferLooksLikeFiles(dt) {
		if (!dt || !dt.types) return true;
		const types = dt.types;
		if (types.length === 0) return true;
		for (let i = 0; i < types.length; i++) {
			const t = types[i];
			if (t === 'Files' || t === 'application/x-moz-file') return true;
		}
		if (typeof types.contains === 'function' && types.contains('Files')) return true;
		return false;
	}
	function readDirEntries(reader) {
		return new Promise((resolve, reject) => {
			const acc = [];
			function step() {
				reader.readEntries(batch => {
					if (batch.length === 0) return resolve(acc);
					acc.push.apply(acc, batch);
					step();
				}, reject);
			}
			step();
		});
	}
	async function walkEntry(entry, prefix) {
		if (entry.isFile) {
			return new Promise((resolve, reject) => {
				entry.file(file => {
					try {
						Object.defineProperty(file, 'webkitRelativePath', {
							value: prefix + file.name,
							configurable: true,
							enumerable: true,
						});
					} catch (e2) { /* ignore */ }
					resolve([file]);
				}, reject);
			});
		}
		if (entry.isDirectory) {
			const dirPath = prefix + entry.name + '/';
			const reader = entry.createReader();
			const entries = await readDirEntries(reader);
			const out = [];
			for (let j = 0; j < entries.length; j++) {
				out.push.apply(out, await walkEntry(entries[j], dirPath));
			}
			return out;
		}
		return [];
	}
	async function filesFromDataTransferItems(items) {
		/* Snapshot roots synchronously: after any await, DataTransferItem / entry handles can go stale
		   and webkitGetAsEntry() only works for the first dropped folder. */
		const roots = [];
		for (let i = 0; i < items.length; i++) {
			const it = items[i];
			if (typeof it.webkitGetAsEntry === 'function') {
				const entry = it.webkitGetAsEntry();
				if (entry) {
					roots.push({ kind: 'entry', entry });
					continue;
				}
			}
			if (it.kind === 'file') {
				const f = it.getAsFile();
				if (f) roots.push({ kind: 'file', file: f });
			}
		}
		const out = [];
		for (let r = 0; r < roots.length; r++) {
			const root = roots[r];
			if (root.kind === 'entry') {
				out.push.apply(out, await walkEntry(root.entry, ''));
			}
		}
		const loose = roots.filter(x => x.kind === 'file');
		for (let i = 0; i < loose.length; i++) {
			const f = loose[i].file;
			const rel = loose.length === 1 ? f.name : (i + '_' + f.name);
			try {
				Object.defineProperty(f, 'webkitRelativePath', {
					value: rel,
					configurable: true,
					enumerable: true,
				});
			} catch (e2) { /* ignore */ }
			out.push(f);
		}
		return out;
	}
	/** Every file must have webkitRelativePath (folder picker, folder drop, or synthetic path for loose file(s)). */
	function relativePathsOkForQueue(q) {
		if (!q || q.length === 0) {
			return false;
		}
		return q.every(f => typeof f.webkitRelativePath === 'string' && f.webkitRelativePath.length > 0);
	}
	/** Loose file(s): synthetic path = basename, or index_basename if many (avoids collisions). */
	function assignLooseRelativePaths(fileList) {
		const arr = Array.from(fileList);
		const out = [];
		for (let i = 0; i < arr.length; i++) {
			const f = arr[i];
			const rel = arr.length === 1 ? f.name : (i + '_' + f.name);
			try {
				Object.defineProperty(f, 'webkitRelativePath', {
					value: rel,
					configurable: true,
					enumerable: true,
				});
			} catch (e2) { /* ignore */ }
			out.push(f);
		}
		return out;
	}
	async function ingestCompressDrop(dt) {
		if (!dt) return;
		if (dt.items && dt.items.length) {
			const list = await filesFromDataTransferItems(dt.items);
			if (list.length) {
				if (!relativePathsOkForQueue(list)) {
					queue = [];
					go.disabled = true;
					status.classList.add('error');
					status.textContent = 'Could not read paths for this drop. Try choosing a folder or file(s) with the buttons below.';
					refreshCompressHeavyWarning();
					return;
				}
				queue = list;
				go.disabled = false;
				status.textContent = list.length + ' item(s) ready.';
				status.classList.remove('error');
				refreshCompressHeavyWarning();
				return;
			}
		}
		if (dt.files && dt.files.length) {
			let flat = Array.from(dt.files);
			if (!relativePathsOkForQueue(flat)) {
				flat = assignLooseRelativePaths(flat);
			}
			if (!relativePathsOkForQueue(flat)) {
				queue = [];
				go.disabled = true;
				status.classList.add('error');
				status.textContent = 'Could not assign paths for this drop. Use “Choose file(s)” or a folder.';
				refreshCompressHeavyWarning();
				return;
			}
			queue = flat;
			go.disabled = false;
			status.textContent = queue.length + ' item(s) ready.';
			status.classList.remove('error');
			refreshCompressHeavyWarning();
		}
	}
	const useCapture = true;
	document.addEventListener('dragenter', (e) => {
		if (!dataTransferLooksLikeFiles(e.dataTransfer)) return;
		e.preventDefault();
		document.body.classList.add('page-drop-active');
	}, useCapture);
	document.addEventListener('dragover', (e) => {
		if (!dataTransferLooksLikeFiles(e.dataTransfer)) return;
		e.preventDefault();
		e.stopPropagation();
		if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy';
		if (dz.contains(e.target)) dz.classList.add('drag');
		else dz.classList.remove('drag');
	}, useCapture);
	document.addEventListener('drop', (e) => {
		e.preventDefault();
		e.stopPropagation();
		endPageDragVisual();
		(async () => {
			try {
				await ingestCompressDrop(e.dataTransfer);
			} catch (err) {
				status.classList.add('error');
				status.textContent = 'Could not read dropped folders: ' + (err && err.message ? err.message : String(err));
			}
		})();
	}, useCapture);
	document.addEventListener('dragend', endPageDragVisual, useCapture);
	pickDir.addEventListener('change', () => {
		queue = Array.from(pickDir.files);
		if (queue.length && !relativePathsOkForQueue(queue)) {
			queue = [];
			go.disabled = true;
			status.classList.add('error');
			status.textContent = 'Your browser did not supply relative paths for this folder. Try Chromium or another browser with folder upload support.';
			refreshCompressHeavyWarning();
			return;
		}
		go.disabled = queue.length === 0;
		status.textContent = queue.length ? queue.length + ' item(s) from folder (relative paths will be stored in the archive).' : '';
		status.classList.remove('error');
		refreshCompressHeavyWarning();
	});

	function handleCompressXhrHttpErrors(xhr) {
		if (xhr.status === 413) {
			status.classList.add('error');
			let msg = 'Upload too large for the server (PHP post_max_size / upload_max_filesize, or nginx client_max_body_size).';
			try {
				const j = parseJsonResponse(xhr.responseText, xhr);
				if (j && !j.ok && j.error) msg = j.error;
			} catch (e) { /* keep default */ }
			status.textContent = msg;
			return true;
		}
		if (xhr.status === 502 || xhr.status === 504) {
			status.classList.add('error');
			status.textContent = 'Gateway timeout while compressing — try a smaller tree or raise proxy / PHP time limits.';
			return true;
		}
		return false;
	}

	go.addEventListener('click', () => {
		if (!queue.length) return;
		if (!relativePathsOkForQueue(queue)) {
			status.classList.add('error');
			status.textContent = 'Each file needs a member path. Use “Choose folder”, “Choose file(s)”, or drop a folder.';
			return;
		}
		if (FZC_WEB_MAX_UPLOAD_BYTES > 0) {
			let sum = 0;
			for (let i = 0; i < queue.length; i++) sum += queue[i].size || 0;
			if (sum > FZC_WEB_MAX_UPLOAD_BYTES) {
				status.classList.add('error');
				status.textContent = 'Total selection exceeds the ' + FZC_WEB_MAX_UPLOAD_BYTES + '-byte web cap. Use the CLI for larger trees. On your own server, set FZC_WEB_MAX_UPLOAD_BYTES (e.g. 10485760 for 10 MiB, or 0 to disable) and raise PHP/nginx upload limits — see footer.';
				return;
			}
		}
		result.innerHTML = '';
		stopCompressReassurance();
		status.classList.remove('error');
		uploadBar.value = 0;
		workBar.removeAttribute('value');
		uploadBlock.hidden = false;
		workBlock.hidden = true;
		const totalFiles = queue.length;
		const nChunks = Math.ceil(totalFiles / MAX_FILES_PER_POST);
		status.textContent = 'Uploading batch 1 / ' + nChunks + ' (' + totalFiles + ' items)…';

		(async () => {
			let jobId = null;
			let jFinal = null;
			try {
				for (let c = 0; c < nChunks; c++) {
					const chunk = queue.slice(c * MAX_FILES_PER_POST, (c + 1) * MAX_FILES_PER_POST);
					const isLast = c === nChunks - 1;
					status.textContent = 'Uploading batch ' + (c + 1) + ' / ' + nChunks + ' (' + totalFiles + ' items)…';
					const fd = new FormData();
					fd.append('fzc_chunked', '1');
					if (jobId) fd.append('fzc_job', jobId);
					fd.append('fzc_finalize', isLast ? '1' : '0');
					if (isLast && fzcDownloadStem) {
						const s = fzcDownloadStem.value.trim();
						if (s) {
							fd.append('fzc_download_stem', s);
						}
					}
					for (const f of chunk) {
						fd.append('fzc_relpath[]', f.webkitRelativePath);
						/* Use basename for multipart filename; relative path is carried in fzc_relpath[] so PHP stacks
						   that basename() upload filenames still preserve the directory tree on disk. */
						fd.append('files[]', f, f.name);
					}
					const xhr = await new Promise((resolve, reject) => {
						const x = new XMLHttpRequest();
						x.open('POST', compressPostUrl);
						x.timeout = 0;
						x.upload.onprogress = (e) => {
							if (e.lengthComputable && nChunks > 0) {
								uploadBar.value = Math.min(99, Math.round(100 * ((c + e.loaded / e.total) / nChunks)));
							}
						};
						x.upload.onload = () => {
							if (isLast) {
								uploadBlock.hidden = true;
								workBlock.hidden = false;
								startCompressReassurance();
							}
						};
						x.onload = () => resolve(x);
						x.onerror = () => reject(new Error('Network error (connection dropped). Large uploads: increase post_max_size, upload_max_filesize, and web server body limits; allow long-running PHP and proxy timeouts.'));
						x.ontimeout = () => reject(new Error('Request timed out in the browser — try fewer/smaller files or raise timeouts.'));
						x.send(fd);
					});
					if (handleCompressXhrHttpErrors(xhr)) {
						hideProgress();
						return;
					}
					let j;
					try {
						j = parseJsonResponse(xhr.responseText, xhr);
					} catch (e) {
						hideProgress();
						status.classList.add('error');
						status.textContent = e.message + (e.hint ? ' Preview: ' + e.hint : '');
						return;
					}
					if (!j.ok) {
						hideProgress();
						status.classList.add('error');
						status.textContent = j.error || 'Error';
						return;
					}
					if (j.partial) {
						jobId = j.job;
						status.textContent = 'Staged ' + j.files_staged_total + ' / ' + totalFiles + ' item(s)…';
						uploadBlock.hidden = false;
						workBlock.hidden = true;
					}
					if (isLast) jFinal = j;
				}
				hideProgress();
				workBar.value = 1;
				if (!jFinal || !jFinal.download_url) {
					status.classList.add('error');
					status.textContent = 'Unexpected response (no archive).';
					return;
				}
				status.textContent = 'Done — ' + jFinal.files_packed + ' item(s) packed, ' + human(jFinal.bytes) + '.';
				const leaf = (jFinal.download_filename && typeof jFinal.download_filename === 'string')
					? jFinal.download_filename
					: 'archive.fzc';
				const a = document.createElement('a');
				a.href = jFinal.download_url;
				a.textContent = 'Download ' + leaf + ' (' + human(jFinal.bytes) + ')';
				a.setAttribute('download', leaf);
				result.appendChild(a);
				const p = document.createElement('p');
				p.innerHTML = '<small>Link: <code>' + jFinal.download_url + '</code></small>';
				result.appendChild(p);
			} catch (err) {
				hideProgress();
				status.classList.add('error');
				status.textContent = err.message || String(err);
			}
		})();
	});
})();
</script>
</body>
</html>
HTML;
}
