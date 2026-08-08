<?php

declare(strict_types=1);

/**
 * Content-specific CM lane for single-file opaque binary / ELF folders.
 *
 * Targets fixtures where generic outer+zpaq ties an external tool (kennedy.xls,
 * ptt5, sum / SPARC ELF, paper-100k.pdf-style mismatch corpora): paq8px's
 * record/image/exe models beat zpaq by ~30–50% on those shapes. Writes an FZpq
 * v2 wire (path + tool + arc) so extraction restores the original member name.
 * Kill switch: FRACTAL_ZIP_BINARY_CM=0.
 *
 * Depth is heuristic, not a flag: available seconds come from
 * FRACTAL_ZIP_TIME_BUDGET_MS (benches) or the lifestyle encode horizon (~45s,
 * matching the default case timeout). When estimated deep paq8px time exceeds
 * that budget, the fast tip (zpaq5 / lpaq / mcm) runs instead so general-purpose
 * zips and the 45s suite finish with competitive bytes. Bytes-first / non-lifestyle
 * (e.g. --ultra turns lifestyle off) keeps the long nominal walls.
 *
 * Override walls: FRACTAL_ZIP_BINARY_CM_WALL_SEC, _XLS_WALL_SEC, _PSEUDO_PDF_WALL_SEC,
 * FRACTAL_ZIP_BINARY_CM_LIFESTYLE_HORIZON_SEC.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';

/** @return bool */
function fractal_zip_binary_cm_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_BINARY_CM');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !in_array($v, array('0', 'off', 'false', 'no'), true);
}

function fractal_zip_binary_cm_min_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_BINARY_CM_MIN_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		return max(256, (int) trim((string) $e));
	}
	return 16384;
}

function fractal_zip_binary_cm_max_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_BINARY_CM_MAX_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) trim((string) $e));
	}
	// Bytes-first / fair-full: cover Silesia mozilla (~51 MiB tar) for deep paq
	// when the mcm tip loses. Lifestyle keeps the 4 MiB cap (tip lane skipped).
	$lifeOff = false;
	$lifeEnv = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
	if (is_string($lifeEnv) && in_array(strtolower(trim($lifeEnv)), array('0', 'off', 'false', 'no'), true)) {
		$lifeOff = true;
	} elseif (class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& !fractal_zip::lifestyle_speed_profile_enabled()) {
		$lifeOff = true;
	}
	return $lifeOff ? (56 * 1024 * 1024) : (4 * 1024 * 1024);
}

/** Default 60s: covers ptt5/sum and geo.protodata (~50 s @118 KiB); kennedy needs a higher wall. */
function fractal_zip_binary_cm_wall_sec(): int
{
	$e = getenv('FRACTAL_ZIP_BINARY_CM_WALL_SEC');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) trim((string) $e));
	}
	return 60;
}

/**
 * Small source where bare paq8px beats general-text's XML wrapper.
 * Mid-size troff (Calgary paper2/trans) stays on general-text — DRT+lpaq wins there.
 */
function fractal_zip_binary_cm_looks_source_text(string $rel, string $profile, int $size): bool
{
	if ($size < 256 || $size > 131072) {
		return false;
	}
	if ($profile === 'text_c_like') {
		return true;
	}
	// Tiny troff/man pages only (xargs.1); larger troff → general-text.
	if ($profile === 'text_troff' && $size <= 16384) {
		return true;
	}
	$lower = strtolower(str_replace('\\', '/', $rel));
	$base = basename($lower);
	// Calgary extensionless code (+ bib: paq slightly beats gtext).
	static $calgaryCode = array(
		'progc' => true, 'progl' => true, 'progp' => true, 'bib' => true,
	);
	if (isset($calgaryCode[$base]) && ($profile === 'text_plain' || $profile === 'text_refer_like' || $profile === 'text_c_like')) {
		return true;
	}
	// Classic source suffixes (not .txt prose).
	return (bool) preg_match('/\.(lsp|lisp|el|scm|c|h|cc|cpp|hpp|s|asm|1|man)$/', $lower);
}

/**
 * Medium/large English prose (Canterbury / Silesia text): zpaq method 3 is a lifestyle
 * tip that beats 7z outer (lcet10 ~102 KiB / ~60 ms vs ~120 KiB 7z) without phda9 startup.
 */
function fractal_zip_binary_cm_looks_prose_tip(string $rel, string $profile, int $size, string $bytes): bool
{
	if ($size < 65536 || $size > 524288) {
		return false;
	}
	if ($profile !== 'text_plain' && $profile !== 'text_line_oriented') {
		return false;
	}
	if (function_exists('fractal_zip_general_text_looks_url_list')) {
		// already gated in identify, but keep safe for direct callers
	} elseif (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_general_text.php')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_general_text.php';
	}
	if (function_exists('fractal_zip_general_text_looks_url_list')
		&& fractal_zip_general_text_looks_url_list($bytes)) {
		return false;
	}
	if (fractal_zip_binary_cm_looks_incompressible($bytes)) {
		return false;
	}
	$lower = strtolower(str_replace('\\', '/', $rel));
	// Prefer classic prose names / .txt; allow extensionless Calgary book text.
	if (preg_match('/\.(txt|text|md|rst)$/', $lower) === 1) {
		return true;
	}
	$base = basename($lower);
	static $calgaryBook = array(
		'alice29' => true, 'asyoulik' => true, 'lcet10' => true, 'plrabn12' => true,
		'book1' => true, 'book2' => true, 'news' => true,
	);
	return isset($calgaryBook[$base]);
}

/**
 * Per-member wall: BIFF/.xls record payloads need ~400–600 s for paq8px even at
 * level 1. Default that deep wall unless FRACTAL_ZIP_BINARY_CM_XLS_WALL_SEC is set
 * (0 = skip the lane for XLS and fall through to the zpaq tie).
 */
function fractal_zip_binary_cm_looks_pseudo_pdf(string $rel, string $plain): bool
{
	if (!str_ends_with(strtolower(str_replace('\\', '/', $rel)), '.pdf')) {
		return false;
	}
	// Real PDFs keep the literal-PAC / outer path; only mismatch corpora (e.g.
	// Squash paper-100k.pdf: RDF/ASCII pockets, no %PDF header) use this lane.
	$head = ltrim(substr($plain, 0, 1024));
	return $head === '' || !str_starts_with($head, '%PDF');
}

/** Real PDF (%PDF magic), with or without a .pdf suffix (Silesia reymont). */
function fractal_zip_binary_cm_looks_real_pdf(string $plain): bool
{
	$head = ltrim(substr($plain, 0, 1024));
	return $head !== '' && str_starts_with($head, '%PDF');
}

/** PE/COFF Windows executable or DLL (MZ header; Silesia ooffice). */
function fractal_zip_binary_cm_looks_pe(string $plain): bool
{
	return strlen($plain) >= 64 && $plain[0] === 'M' && $plain[1] === 'Z';
}

function fractal_zip_binary_cm_is_biff(string $rel, string $plain): bool
{
	return str_ends_with(strtolower($rel), '.xls')
		|| (strlen($plain) >= 8 && $plain[0] === "\x09" && $plain[1] === "\x00"
			&& $plain[2] === "\x04" && $plain[3] === "\x00");
}

/**
 * Remaining encode budget (seconds) from FRACTAL_ZIP_TIME_BUDGET_MS when set
 * (run_benchmarks passes the per-case remainder into zip_folder).
 */
function fractal_zip_binary_cm_time_budget_sec(): ?float
{
	$e = getenv('FRACTAL_ZIP_TIME_BUDGET_MS');
	if ($e === false || trim((string) $e) === '' || !is_numeric(trim((string) $e))) {
		return null;
	}
	$sec = ((float) trim((string) $e)) / 1000.0;
	return $sec > 0.0 ? $sec : null;
}

/**
 * General-purpose encode horizon (seconds) when no TIME_BUDGET_MS is set and the
 * lifestyle speed profile is on. Matches the default 45s bench case timeout so
 * CLI-default and suite-default optimize the same Pareto band.
 */
function fractal_zip_binary_cm_lifestyle_horizon_sec(): float
{
	$e = getenv('FRACTAL_ZIP_BINARY_CM_LIFESTYLE_HORIZON_SEC');
	if ($e !== false && trim((string) $e) !== '' && is_numeric(trim((string) $e))) {
		return max(0.0, (float) trim((string) $e));
	}
	return 45.0;
}

/**
 * Seconds available for this CM attempt: explicit bench budget, else lifestyle
 * horizon under the default speed profile, else null (bytes-first / unlimited).
 */
function fractal_zip_binary_cm_available_sec(): ?float
{
	$budget = fractal_zip_binary_cm_time_budget_sec();
	if ($budget !== null) {
		return $budget;
	}
	// Prefer explicit env when the fractal_zip class is not loaded yet (CLI probes).
	$lifeEnv = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
	if (is_string($lifeEnv) && in_array(strtolower(trim($lifeEnv)), array('0', 'off', 'false', 'no'), true)) {
		return null;
	}
	$lifestyle = true;
	if (class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')) {
		$lifestyle = fractal_zip::lifestyle_speed_profile_enabled();
	}
	if (!$lifestyle) {
		return null;
	}
	$h = fractal_zip_binary_cm_lifestyle_horizon_sec();
	return $h > 0.0 ? $h : null;
}

/**
 * Rough deep-paq8px wall estimate (seconds) from shape + size. Used to prefer the
 * fast tip when deep cannot finish inside the available horizon.
 */
function fractal_zip_binary_cm_estimate_deep_sec(string $rel, string $plain): float
{
	$n = max(1, strlen($plain));
	$kib = $n / 1024.0;
	if (fractal_zip_binary_cm_is_biff($rel, $plain)) {
		// BIFF records: ~0.5–0.7 s/KiB at level 1 (kennedy ~420–700 s).
		return max(180.0, $kib * 0.55);
	}
	if (fractal_zip_binary_cm_looks_pseudo_pdf($rel, $plain)) {
		// paper-100k ~40 s idle; keep a contested-host cushion.
		return max(28.0, $kib * 0.45);
	}
	// Opaque / ELF: sum (~38 KiB) finishes quickly; geo (~118 KiB) ~50 s;
	// ptt5 (~500 KiB) often ~50–70 s. Size alone under-predicts mid-band model cost.
	// Large Silesia (x-ray/osdb/mozilla): ~0.55–0.7 s/KiB at paq8px -1.
	if ($kib >= 4096.0) {
		return max(3600.0, $kib * 0.60);
	}
	if ($kib >= 90.0) {
		return max(48.0, $kib * 0.12);
	}
	return max(5.0, $kib * 0.28);
}

/** Minimum wall (s) before deep paq8px is worth attempting for this shape. */
function fractal_zip_binary_cm_min_deep_wall_sec(string $rel, string $plain): int
{
	if (fractal_zip_binary_cm_is_biff($rel, $plain)) {
		return 120;
	}
	if (fractal_zip_binary_cm_looks_pseudo_pdf($rel, $plain)) {
		return 35;
	}
	$len = strlen($plain);
	// Keep these near measured finish times so a 45s lifestyle horizon tips mid-band
	// opaques instead of starting a deep encode that will time out.
	if ($len >= 200000) {
		return 48;
	}
	if ($len >= 90000) {
		return 40;
	}
	return 8;
}

function fractal_zip_binary_cm_wall_sec_for_member(string $rel, string $plain): int
{
	if (fractal_zip_binary_cm_is_biff($rel, $plain)) {
		$e = getenv('FRACTAL_ZIP_BINARY_CM_XLS_WALL_SEC');
		if ($e !== false && trim((string) $e) !== '') {
			return max(0, (int) trim((string) $e));
		}
		// Nominal deep wall for bytes-first runs (lifestyle off / long budget).
		// Lifestyle/budget clamping below picks the fast tip when this won't finish.
		return 900;
	}
	if (fractal_zip_binary_cm_looks_pseudo_pdf($rel, $plain)) {
		$e = getenv('FRACTAL_ZIP_BINARY_CM_PSEUDO_PDF_WALL_SEC');
		if ($e !== false && trim((string) $e) !== '') {
			return max(0, (int) trim((string) $e));
		}
		// paper-100k encode ~40 s idle; 120 s covers contested hosts.
		return 120;
	}
	$len = strlen($plain);
	// Bytes-first midsize opaque/media (x-ray/osdb/sao): paq8px -1 is ~1 h class.
	$lifeOff = false;
	$lifeEnv = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
	if (is_string($lifeEnv) && in_array(strtolower(trim($lifeEnv)), array('0', 'off', 'false', 'no'), true)) {
		$lifeOff = true;
	} elseif (class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& !fractal_zip::lifestyle_speed_profile_enabled()) {
		$lifeOff = true;
	}
	if ($lifeOff && $len >= 4 * 1024 * 1024) {
		$e = getenv('FRACTAL_ZIP_BINARY_CM_LARGE_WALL_SEC');
		if ($e !== false && trim((string) $e) !== '') {
			return max(0, (int) trim((string) $e));
		}
		// mozilla-class (~51 MiB) needs ~8–10 h; midsize (osdb/sao/x-ray) ~1–2 h.
		return ($len >= 16 * 1024 * 1024) ? 36000 : 7200;
	}
	$wall = fractal_zip_binary_cm_wall_sec();
	// Mid-size opaque: geo.protodata ~50 s; Calgary obj2 ~247 KiB needs longer.
	if ($wall > 0 && $len >= 200000) {
		return max($wall, 180);
	}
	if ($wall > 0 && $len >= 100000) {
		return max($wall, 90);
	}
	return $wall;
}

/**
 * Nominal wall clamped to available seconds (TIME_BUDGET or lifestyle horizon).
 * Deep CM when the Pareto tip can pay for it; else callers use the fast tip.
 */
function fractal_zip_binary_cm_effective_wall_sec(string $rel, string $plain): int
{
	$wall = fractal_zip_binary_cm_wall_sec_for_member($rel, $plain);
	if ($wall <= 0) {
		return 0;
	}
	$avail = fractal_zip_binary_cm_available_sec();
	if ($avail === null) {
		return $wall;
	}
	// Leave ~15% headroom for wire/RT/zpaq5 guards inside the same zip_folder call.
	$capped = (int) max(0, floor($avail * 0.85));
	return min($wall, $capped);
}

/**
 * Whether deep paq8px is the better attempt given available time and shape cost.
 */
function fractal_zip_binary_cm_should_attempt_deep(string $rel, string $plain, int $wall): bool
{
	if ($wall <= 0) {
		return false;
	}
	// Lifestyle half-time: tip lpaq already sole-wins paper-100k (~80.5 KiB vs
	// soft ~80.8 KiB). Deep paq8px (~68 KiB / ~22–28 s) blows the sum-zip budget.
	if (fractal_zip_binary_cm_looks_pseudo_pdf($rel, $plain)
		&& class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& fractal_zip::lifestyle_speed_profile_enabled()) {
		return false;
	}
	// Real midsize PDFs (reymont): lpaq tip beats zpaq in ~5 s; deep paq8px is hours.
	if (fractal_zip_binary_cm_looks_real_pdf($plain) && strlen($plain) >= 1024 * 1024) {
		return false;
	}
	// Midsize PE (ooffice): mcm tip beats zpaq in ~8 s; deep paq8px is hours-scale.
	if (fractal_zip_binary_cm_looks_pe($plain) && strlen($plain) >= 1024 * 1024) {
		return false;
	}
	// Midsize under lifestyle: tip-only (half-time). Bytes-first escalates via
	// tip-first in compress_member when tip loses to zpaq5.
	if (strlen($plain) >= 4 * 1024 * 1024
		&& class_exists('fractal_zip', false)
		&& method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& fractal_zip::lifestyle_speed_profile_enabled()) {
		return false;
	}
	if ($wall < fractal_zip_binary_cm_min_deep_wall_sec($rel, $plain)) {
		return false;
	}
	$est = fractal_zip_binary_cm_estimate_deep_sec($rel, $plain);
	// Prefer tip when expected deep time exceeds what we can spend.
	return $est <= ((float) $wall) * 0.95;
}

/**
 * Auto paq8px level: small files can afford a slightly deeper model; large record
 * payloads stay at 1 (level barely moves bytes on kennedy, just costs wall).
 */
function fractal_zip_binary_cm_paq8px_level(int $plainLen): int
{
	$e = getenv('FRACTAL_ZIP_BINARY_CM_PAQ8PX_LEVEL');
	if ($e !== false && trim((string) $e) !== '' && ctype_digit(trim((string) $e))) {
		return max(1, min(12, (int) trim((string) $e)));
	}
	if ($plainLen <= 65536) {
		return 2;
	}
	if ($plainLen <= 524288) {
		return 1;
	}
	return 1;
}

/**
 * Skip already-compressed / high-entropy payloads (JPEG etc.) where paq8px only
 * adds ~0.5% and burns wall. gzip ratio ≥ 0.98 on a head sample ⇒ skip.
 */
function fractal_zip_binary_cm_looks_incompressible(string $bytes): bool
{
	$sample = strlen($bytes) > 262144 ? substr($bytes, 0, 262144) : $bytes;
	if (strlen($sample) < 4096) {
		return false;
	}
	$z = gzdeflate($sample, 1);
	if ($z === false) {
		return false;
	}
	return strlen($z) >= (int) (strlen($sample) * 0.98);
}

/**
 * @return array{path: string, rel: string, bytes: string, size: int, profile: string, lane: string}|null
 */
function fractal_zip_binary_cm_identify_single_member(string $dir): ?array
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
		// Ignore compressor scratch left in fixture dirs (probe aborts, cwd leaks).
		if (str_starts_with($base, 'fzpaq_') || str_starts_with($base, 'fzbcm_')
			|| str_ends_with(strtolower($base), '.paq8px')) {
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
	$max = fractal_zip_binary_cm_max_bytes();
	if ($max > 0 && $f['size'] > $max) {
		return null;
	}
	$bytes = (string) file_get_contents($f['path']);
	if ($bytes === '') {
		return null;
	}
	if (!function_exists('fractal_zip_identify_for_policy')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_content_format_policy.php';
	}
	$row = fractal_zip_identify_for_policy($f['rel'], $bytes);
	$profile = (string) ($row['content_profile'] ?? '');

	// URL-per-line text rejected by general-text: mcm/lpaq FZpq lane.
	if ($profile === 'text_plain' || $profile === 'text_line_oriented') {
		if (!function_exists('fractal_zip_general_text_looks_url_list')) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_general_text.php';
		}
		if (fractal_zip_general_text_looks_url_list($bytes)) {
			return array(
				'path' => $f['path'],
				'rel' => $f['rel'],
				'bytes' => $bytes,
				'size' => $f['size'],
				'profile' => $profile,
				'lane' => 'url_list',
			);
		}
		// paper-100k.pdf etc.: policy says text_plain (RDF pockets) but bytes are
		// not a real PDF — paq8px beats the zstd outer tie (~68 KB vs ~81 KB).
		if (fractal_zip_binary_cm_looks_pseudo_pdf($f['rel'], $bytes)
			&& $f['size'] >= fractal_zip_binary_cm_min_bytes()
			&& !fractal_zip_binary_cm_looks_incompressible($bytes)) {
			return array(
				'path' => $f['path'],
				'rel' => $f['rel'],
				'bytes' => $bytes,
				'size' => $f['size'],
				'profile' => $profile,
				'lane' => 'binary',
			);
		}
		// Small source-ish plaintext (grammar.lsp): bare CM beats generic outer.
		if (fractal_zip_binary_cm_looks_source_text($f['rel'], $profile, $f['size'])) {
			return array(
				'path' => $f['path'],
				'rel' => $f['rel'],
				'bytes' => $bytes,
				'size' => $f['size'],
				'profile' => $profile,
				'lane' => 'binary',
			);
		}
		// Large prose singles (lcet10 / alice / asyoulik / plrabn12): lifestyle zpaq3
		// tip beats 7z/brotli outer within the v8×1.1 gate. Cap below urls.10K (url_list).
		// Bytes-first / fair-full: leave these for general-text (DRT+lpaq soles vs zpaq5).
		$lifeOff = false;
		$lifeEnv = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
		if (is_string($lifeEnv) && in_array(strtolower(trim($lifeEnv)), array('0', 'off', 'false', 'no'), true)) {
			$lifeOff = true;
		} elseif (class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
			&& !fractal_zip::lifestyle_speed_profile_enabled()) {
			$lifeOff = true;
		}
		if (!$lifeOff && fractal_zip_binary_cm_looks_prose_tip($f['rel'], $profile, $f['size'], $bytes)) {
			return array(
				'path' => $f['path'],
				'rel' => $f['rel'],
				'bytes' => $bytes,
				'size' => $f['size'],
				'profile' => $profile,
				'lane' => 'prose',
			);
		}
		return null;
	}

	// C/troff: general-text XML wrap loses (fields.c, xargs.1).
	if (fractal_zip_binary_cm_looks_source_text($f['rel'], $profile, $f['size'])) {
		return array(
			'path' => $f['path'],
			'rel' => $f['rel'],
			'bytes' => $bytes,
			'size' => $f['size'],
			'profile' => $profile,
			'lane' => 'binary',
		);
	}

	if ($f['size'] < fractal_zip_binary_cm_min_bytes()) {
		return null;
	}
	if (fractal_zip_binary_cm_looks_incompressible($bytes)) {
		return null;
	}
	$ok = ($profile === 'opaque_binary')
		|| ($profile === 'document_pdf')
		|| ($profile === 'media_blkmed')
		|| ($profile === 'container_tar')
		|| ($profile === 'unknown' && str_starts_with($bytes, "\x7fELF"))
		|| str_starts_with($bytes, "\x7fELF")
		|| fractal_zip_binary_cm_looks_pseudo_pdf($f['rel'], $bytes)
		|| fractal_zip_binary_cm_looks_real_pdf($bytes)
		|| fractal_zip_binary_cm_looks_pe($bytes);
	if (!$ok) {
		return null;
	}
	return array(
		'path' => $f['path'],
		'rel' => $f['rel'],
		'bytes' => $bytes,
		'size' => $f['size'],
		'profile' => $profile !== '' ? $profile : 'opaque_binary',
		'lane' => 'binary',
	);
}

/**
 * Fast tip when deep paq8px is not affordable under the available horizon.
 * - paper-100k: lpaq ~80.5 KB still beats the zstd ~80.8 KB tie
 * - kennedy.xls: zpaq5 ~16.8 KB ≈ historical zpaq_raw tie (~10 s); deep paq8px when horizon allows
 * - prose (lcet10): zpaq3 ~102 KB / ~60 ms beats lifestyle 7z ~120 KB
 *
 * @return array{wire: string, tool: string, arc_bytes: int, seconds: float}|null
 */
function fractal_zip_binary_cm_compress_prose(string $rel, string $plain, float $t0): ?array
{
	$z = fractal_zip_paq_zpaq_compress_bytes($plain, 3);
	if (!is_array($z) || !is_string($z['bytes']) || $z['bytes'] === ''
		|| strlen($z['bytes']) >= strlen($plain)) {
		return null;
	}
	$wire = fractal_zip_paq_wrap_wire_v2('zpaq3', $z['bytes'], $rel);
	return array(
		'wire' => $wire,
		'tool' => 'zpaq3',
		'arc_bytes' => strlen($z['bytes']),
		'seconds' => round(microtime(true) - $t0, 4),
	);
}

/**
 * Fast tip when deep paq8px is not affordable under the available horizon.
 * - paper-100k: lpaq ~80.5 KB still beats the zstd ~80.8 KB tie
 * - kennedy.xls: zpaq5 ~16.8 KB ≈ historical zpaq_raw tie (~10 s); deep paq8px when horizon allows
 *
 * @return array{wire: string, tool: string, arc_bytes: int, seconds: float}|null
 */
function fractal_zip_binary_cm_compress_fast(string $rel, string $plain, float $t0): ?array
{
	$bestArc = null;
	$bestTool = null;
	if (fractal_zip_binary_cm_is_biff($rel, $plain)) {
		$z = fractal_zip_paq_zpaq_compress_bytes($plain, 5);
		if (is_array($z) && is_string($z['bytes']) && $z['bytes'] !== '') {
			$bestArc = $z['bytes'];
			$bestTool = 'zpaq5';
		}
	} else {
		// Whole ustar: mcm tip soles (samba); lpaq on 20 MiB+ is a long lose.
		$isUstar = (strpos($plain, "ustar\0") !== false || strpos($plain, "ustar ") !== false);
		// Lifestyle small pseudo-PDF (paper-100k / 121): lpaq alone soles soft zstd;
		// mcm probe + later zpaq5 compare were ~1 s pure wall.
		$lifeSmallPseudoPdf = false;
		if (!$isUstar && class_exists('fractal_zip', false)
			&& method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
			&& fractal_zip::lifestyle_speed_profile_enabled()
			&& fractal_zip_binary_cm_looks_pseudo_pdf($rel, $plain)
			&& strlen($plain) > 0 && strlen($plain) <= 262144) {
			$lifeSmallPseudoPdf = true;
		}
		if (!$isUstar) {
			$l = fractal_zip_paq_lpaq_compress_bytes($plain);
			if (is_array($l) && is_string($l['bytes']) && $l['bytes'] !== '') {
				$bestArc = $l['bytes'];
				$bestTool = 'lpaq9l';
			}
		}
		// mcm segfaults on some large real PDFs (Silesia reymont); skip there.
		if (!$lifeSmallPseudoPdf && !fractal_zip_binary_cm_looks_real_pdf($plain)) {
			$m = fractal_zip_paq_mcm_compress_bytes($plain);
			if (is_array($m) && is_string($m['bytes']) && $m['bytes'] !== ''
				&& ($bestArc === null || strlen($m['bytes']) < strlen($bestArc))) {
				$bestArc = $m['bytes'];
				$bestTool = 'mcm';
			}
		}
	}
	if (!is_string($bestArc) || $bestArc === '' || !is_string($bestTool)) {
		return null;
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
 * Compress with lane-appropriate CM. Returns FZpq v2 wire.
 *
 * Deep paq8px when the available horizon (bench budget or lifestyle ~45s) can
 * pay for the estimated encode; otherwise the fast tip (zpaq5 / lpaq / mcm).
 *
 * @return array{wire: string, tool: string, arc_bytes: int, seconds: float}|null
 */
function fractal_zip_binary_cm_compress_member(string $rel, string $plain, string $lane = 'binary'): ?array
{
	$t0 = microtime(true);
	if ($lane === 'url_list') {
		return fractal_zip_binary_cm_compress_url_list($rel, $plain, $t0);
	}
	if ($lane === 'prose') {
		return fractal_zip_binary_cm_compress_prose($rel, $plain, $t0);
	}
	// Tip first: mr/reymont/ooffice/samba sole in seconds. Escalate to deep
	// paq8px when the tip loses to zpaq5 (x-ray/osdb/sao/mozilla under fair-full).
	// Samba keeps tip-only via tipStrong (~mcm ≪ raw). Mozilla tip loses → deep.
	$tip = fractal_zip_binary_cm_compress_fast($rel, $plain, $t0);
	$plainLen = strlen($plain);
	$isUstar = (strpos($plain, "ustar\0") !== false || strpos($plain, "ustar ") !== false);
	// Lifestyle small pseudo-PDF: tip lpaq already owns soft (~312 B under zstd);
	// skip zpaq5 compare (tipStrong fails because tip is ~80% of raw).
	if ($tip !== null
		&& class_exists('fractal_zip', false)
		&& method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& fractal_zip::lifestyle_speed_profile_enabled()
		&& fractal_zip_binary_cm_looks_pseudo_pdf($rel, $plain)
		&& $plainLen > 0 && $plainLen <= 262144
		&& in_array((string) $tip['tool'], array('lpaq9l', 'mcm'), true)) {
		return $tip;
	}
	// Large payloads: zpaq -m5 re-encode is minutes; trust a strong tip ratio
	// (mcm/lpaq under ~16% of raw is already under typical zpaq_raw). Weak tips
	// (x-ray/osdb/sao/mozilla) escalate to deep paq8px instead of accepting a losing tip.
	$zpaq5Len = null;
	if ($plainLen < 4 * 1024 * 1024) {
		$z = fractal_zip_paq_zpaq_compress_bytes($plain, 5);
		if (is_array($z) && is_string($z['bytes']) && $z['bytes'] !== '') {
			$zpaq5Len = strlen($z['bytes']);
		}
	}
	$tipStrong = $tip !== null
		&& in_array((string) $tip['tool'], array('mcm', 'lpaq9l'), true)
		&& (int) $tip['arc_bytes'] * 6 < $plainLen;
	if ($tip !== null) {
		if ($tipStrong) {
			return $tip;
		}
		if ($zpaq5Len !== null && (int) $tip['arc_bytes'] <= $zpaq5Len) {
			return $tip;
		}
		// Large + weak tip: fall through to deep (or lifestyle ustar early-out).
		if ($plainLen < 4 * 1024 * 1024 && $zpaq5Len === null) {
			return $tip;
		}
	}
	// Lifestyle: never deep-paq whole tars. Bytes-first may escalate below.
	$lifeOn = true;
	$lifeEnv = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
	if (is_string($lifeEnv) && in_array(strtolower(trim($lifeEnv)), array('0', 'off', 'false', 'no'), true)) {
		$lifeOn = false;
	} elseif (class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')) {
		$lifeOn = fractal_zip::lifestyle_speed_profile_enabled();
	}
	if ($isUstar && $lifeOn) {
		return $tip;
	}

	$wall = fractal_zip_binary_cm_effective_wall_sec($rel, $plain);
	if (!fractal_zip_binary_cm_should_attempt_deep($rel, $plain, $wall)) {
		return $tip;
	}

	$exe = fractal_zip_paq_discover_executable('paq8px');
	if ($exe === null) {
		return $tip;
	}
	$lvl = fractal_zip_binary_cm_paq8px_level(strlen($plain));

	$prevLvl = getenv('FRACTAL_ZIP_PAQ8PX_LEVEL');
	$prevTimeout = getenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC');
	putenv('FRACTAL_ZIP_PAQ8PX_LEVEL=' . (string) $lvl);
	putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=' . (string) $wall);

	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzbcm_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$base = basename(str_replace('/', '_', $rel));
	$in = $tmp . DIRECTORY_SEPARATOR . ($base !== '' && $base !== '.' ? $base : 'in.bin');
	file_put_contents($in, $plain);

	$bestArc = null;
	$bestTool = null;

	$r = fractal_zip_paq_compress_file('paq8px', $exe, $in);
	if (is_array($r) && is_string($r['bytes']) && $r['bytes'] !== '') {
		$bestArc = $r['bytes'];
		$bestTool = 'paq8px';
	}

	// mcm: always on paq miss; also race for mid-size opaque where paq often
	// times out (Calgary obj2). Skip huge BIFF (kennedy) — paq8px wins there.
	$plainLen = strlen($plain);
	$raceMcm = fractal_zip_binary_cm_mcm_trial_enabled()
		|| $bestArc === null
		|| ($plainLen >= 128000 && $plainLen < 800000);
	if ($raceMcm && !fractal_zip_binary_cm_looks_real_pdf($plain)) {
		$m = fractal_zip_paq_mcm_compress_bytes($plain);
		if (is_array($m) && is_string($m['bytes']) && $m['bytes'] !== ''
			&& ($bestArc === null || strlen($m['bytes']) < strlen($bestArc))) {
			$bestArc = $m['bytes'];
			$bestTool = 'mcm';
		}
	}

	if ($prevLvl === false || $prevLvl === '') {
		putenv('FRACTAL_ZIP_PAQ8PX_LEVEL');
	} else {
		putenv('FRACTAL_ZIP_PAQ8PX_LEVEL=' . $prevLvl);
	}
	if ($prevTimeout === false || $prevTimeout === '') {
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC');
	} else {
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=' . $prevTimeout);
	}
	@unlink($in);
	@rmdir($tmp);

	$tipArc = ($tip !== null) ? (int) $tip['arc_bytes'] : null;
	if (is_string($bestArc) && $bestArc !== '' && is_string($bestTool)
		&& ($tipArc === null || strlen($bestArc) < $tipArc)
		&& ($zpaq5Len === null || strlen($bestArc) <= $zpaq5Len)) {
		$wire = fractal_zip_paq_wrap_wire_v2($bestTool, $bestArc, $rel);
		return array(
			'wire' => $wire,
			'tool' => $bestTool,
			'arc_bytes' => strlen($bestArc),
			'seconds' => round(microtime(true) - $t0, 4),
		);
	}
	return $tip;
}

/**
 * URL-list lane: race mcm vs lpaq9l (both ~5–7 s / 700 KB); pick smallest.
 *
 * @return array{wire: string, tool: string, arc_bytes: int, seconds: float}|null
 */
function fractal_zip_binary_cm_compress_url_list(string $rel, string $plain, float $t0): ?array
{
	$bestArc = null;
	$bestTool = null;
	$m = fractal_zip_paq_mcm_compress_bytes($plain);
	if (is_array($m) && is_string($m['bytes']) && $m['bytes'] !== '') {
		$bestArc = $m['bytes'];
		$bestTool = 'mcm';
	}
	$l = fractal_zip_paq_lpaq_compress_bytes($plain);
	if (is_array($l) && is_string($l['bytes']) && $l['bytes'] !== ''
		&& ($bestArc === null || strlen($l['bytes']) < strlen($bestArc))) {
		$bestArc = $l['bytes'];
		$bestTool = 'lpaq9l';
	}
	// Ultra: paq8px −5 reclaim ~14 KiB vs mcm on urls.10K (~2 min).
	if (class_exists('fractal_zip', false)
		&& method_exists('fractal_zip', 'ultra_compression_enabled')
		&& fractal_zip::ultra_compression_enabled()
		&& function_exists('fractal_zip_paq_compress_file')
		&& function_exists('fractal_zip_paq_discover_executable')
		&& function_exists('fractal_zip_paq_wrap_wire_v2')) {
		$exe = fractal_zip_paq_discover_executable('paq8px');
		if (is_string($exe) && $exe !== '') {
			$savedLvl = getenv('FRACTAL_ZIP_PAQ8PX_LEVEL');
			putenv('FRACTAL_ZIP_PAQ8PX_LEVEL=5');
			$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzp8url_'
				. bin2hex(random_bytes(4));
			@mkdir($box, 0700, true);
			$inPath = $box . DIRECTORY_SEPARATOR . 'in.bin';
			if (@file_put_contents($inPath, $plain) !== false) {
				$pack = fractal_zip_paq_compress_file('paq8px', $exe, $inPath);
				if (is_array($pack) && isset($pack['bytes']) && is_string($pack['bytes'])
					&& $pack['bytes'] !== ''
					&& ($bestArc === null || strlen($pack['bytes']) < strlen($bestArc))) {
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
	if (!is_string($bestArc) || $bestArc === '' || !is_string($bestTool)) {
		return null;
	}
	$wire = fractal_zip_paq_wrap_wire_v2($bestTool, $bestArc, $rel);
	return array(
		'wire' => $wire,
		'tool' => $bestTool,
		'arc_bytes' => strlen($bestArc),
		'seconds' => round(microtime(true) - $t0, 4),
	);
}

function fractal_zip_binary_cm_mcm_trial_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_BINARY_CM_MCM');
	if ($e === false || trim((string) $e) === '') {
		return false; // paq8px is the sole winner on target shapes; mcm opt-in only
	}
	$v = strtolower(trim((string) $e));
	return in_array($v, array('1', 'on', 'true', 'yes'), true);
}

/**
 * Early short-circuit for zip_folder: write FZpq v2 .fz and return true when the
 * binary CM lane wins eligibility + compress. Caller skips the rest of zip_folder.
 *
 * @param object $fz fractal_zip instance
 */
/**
 * Rough tip-lane seconds (zpaq5 / lpaq / mcm). Under lifestyle, skip the lane when
 * the tip itself would dominate a general-purpose zip (sweet spot: cheap tips only).
 */
function fractal_zip_binary_cm_estimate_tip_sec(string $rel, string $plain): float
{
	$kib = max(1.0, strlen($plain) / 1024.0);
	if (fractal_zip_binary_cm_is_biff($rel, $plain)) {
		// zpaq5 on kennedy.xls measured ~5–11 s.
		return max(4.0, $kib * 0.01);
	}
	if (fractal_zip_binary_cm_looks_pseudo_pdf($rel, $plain)) {
		return max(1.5, $kib * 0.02);
	}
	// Prose zpaq3 tip: ~0.03–0.08 s on 150–480 KiB Canterbury/Silesia text.
	if (preg_match('/\.(txt|text)$/i', $rel) === 1 || in_array(strtolower(basename(str_replace('\\', '/', $rel))), array('alice29', 'asyoulik', 'lcet10', 'plrabn12', 'book1', 'book2', 'news'), true)) {
		return max(0.08, $kib * 0.0002);
	}
	return max(0.3, $kib * 0.008);
}

function fractal_zip_binary_cm_try_write(object $fz, string $dir): bool
{
	if (!fractal_zip_binary_cm_enabled()) {
		return false;
	}
	$member = fractal_zip_binary_cm_identify_single_member($dir);
	if ($member === null) {
		return false;
	}
	$smallPdf = false;
	// Lifestyle sweet spot: only take content-specific CM when the tip is cheap.
	// Deep paq8px remains for lifestyle-off / long budgets via compress_member.
	// Default lifestyle skips most tip lanes (url_list / opaque) — tipEst under-predicts
	// and v8 used fast 7z. Exception: prose zpaq3 (lcet10-class) is both smaller and faster.
	if (class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& fractal_zip::lifestyle_speed_profile_enabled()) {
		$lane = (string) ($member['lane'] ?? 'binary');
		$e = getenv('FRACTAL_ZIP_BINARY_CM_LIFESTYLE_ALLOW_TIP');
		$allowTip = ($e !== false && in_array(strtolower(trim((string) $e)), array('1', 'on', 'true', 'yes'), true));
		// Small lifestyle pseudo-PDF (121): FZB+zstd15 soles soft tar|zstd19 in
		// ~0.05 s; tip lpaq is ~0.3 s for ~250 B. Decline CM so lifestyle early
		// FZB+zstd owns the case (write_lifestyle_speed_first).
		$smallPdf = ($lane === 'binary'
			&& fractal_zip_binary_cm_looks_pseudo_pdf((string) $member['rel'], (string) $member['bytes'])
			&& strlen((string) $member['bytes']) > 0
			&& strlen((string) $member['bytes']) <= 262144);
		if ($smallPdf) {
			return false;
		}
		if (!$allowTip && $lane !== 'prose' && $lane !== 'url_list') {
			return false;
		}
		// url_list (urls.10K / test_files128): mcm tip soles vs tar|brotli in ~1–2 s.
		// tipEst over-predicts (~5.5 s); skip the tipEst gate for this lane.
		if ($lane === 'url_list') {
			// fall through to compress_member
		} else {
			$tipEst = fractal_zip_binary_cm_estimate_tip_sec($member['rel'], $member['bytes']);
			$maxTip = ($lane === 'prose') ? 0.25 : 0.75;
			$me = getenv('FRACTAL_ZIP_BINARY_CM_LIFESTYLE_MAX_TIP_SEC');
			if ($me !== false && trim((string) $me) !== '' && is_numeric(trim((string) $me))) {
				$maxTip = max(0.0, (float) trim((string) $me));
			}
			if ($tipEst > $maxTip) {
				return false;
			}
		}
	}
	$pick = fractal_zip_binary_cm_compress_member($member['rel'], $member['bytes'], (string) ($member['lane'] ?? 'binary'));
	if ($pick === null) {
		return false;
	}
	// Prose tip is zpaq3 FZpq (~102 KiB on lcet10). FreeArc -m7 often beats that
	// by 3–7 KiB, but mcm FZpq reclaim another ~1.5–6 KiB (alice/asyoulik/lcet10/
	// plrabn12) in ~1 s — race Arc vs mcm and keep the smaller container.
	if ((string) ($member['lane'] ?? '') === 'prose'
		&& class_exists('fractal_zip', false)
		&& method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& fractal_zip::lifestyle_speed_profile_enabled()) {
		$bestProse = (string) $pick['wire'];
		$bestProseCodec = 'paq';
		$bestProseLabel = (string) ($pick['tool'] ?? 'zpaq3');
		$rpArc = realpath($dir);
		if ($rpArc !== false
			&& method_exists($fz, 'try_build_folder_freearc_native_archive_blob')
			&& fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ARC')) {
			$arcBlob = $fz->try_build_folder_freearc_native_archive_blob(
				$rpArc,
				null,
				(int) $member['size'],
				array(7)
			);
			if (is_string($arcBlob) && $arcBlob !== ''
				&& strlen($arcBlob) < strlen($bestProse)) {
				$bestProse = $arcBlob;
				$bestProseCodec = 'arc';
				$bestProseLabel = 'arc7';
			}
		}
		// Mid Calgary/Silesia prose is often <1 MiB — the shared mcm helper gates
		// at 1 MiB (nci/xml), so wrap here directly for the prose lane.
		// Parallel mcm + DRT-lpaq (wall ≈ max): DRT reclaim ~4–7 KiB vs mcm on
		// alice/asyoulik/lcet10/plrabn12 (~3 s).
		if (function_exists('fractal_zip_paq_wrap_wire_v2')
			&& strlen((string) $member['bytes']) >= 64 * 1024
			&& strlen((string) $member['bytes']) <= 8 * 1024 * 1024) {
			$cmPack = null;
			if (function_exists('fractal_zip_paq_cm_compress_bytes_parallel')) {
				$cmPack = fractal_zip_paq_cm_compress_bytes_parallel(
					(string) $member['bytes'],
					true
				);
			}
			$cmLanes = array(
				'mcm' => 'mcm',
				'drt_lpaq9lp' => 'drt_lpaq9lp',
				'drt_lpaq9l' => 'drt_lpaq9l',
				'lpaq9l' => 'lpaq9l',
			);
			if (is_array($cmPack)) {
				foreach ($cmLanes as $key => $toolId) {
					if (!isset($cmPack[$key]) || !is_string($cmPack[$key])
						|| $cmPack[$key] === '') {
						continue;
					}
					$cmWire = fractal_zip_paq_wrap_wire_v2(
						$toolId,
						$cmPack[$key],
						(string) $member['rel']
					);
					if (is_string($cmWire) && $cmWire !== ''
						&& strlen($cmWire) < strlen($bestProse)) {
						$bestProse = $cmWire;
						$bestProseCodec = 'paq';
						$bestProseLabel = $toolId;
					}
				}
			} elseif (function_exists('fractal_zip_paq_mcm_compress_bytes')) {
				$mcmPack = fractal_zip_paq_mcm_compress_bytes((string) $member['bytes']);
				if (is_array($mcmPack) && isset($mcmPack['bytes'])
					&& is_string($mcmPack['bytes']) && $mcmPack['bytes'] !== '') {
					$mcmWire = fractal_zip_paq_wrap_wire_v2(
						'mcm',
						$mcmPack['bytes'],
						(string) $member['rel']
					);
					if (is_string($mcmWire) && $mcmWire !== ''
						&& strlen($mcmWire) < strlen($bestProse)) {
						$bestProse = $mcmWire;
						$bestProseCodec = 'paq';
						$bestProseLabel = 'mcm';
					}
				}
			}
			// Ultra mid .txt (122 plrabn12): paq8px −5 reclaim ~2.7 KiB vs DRT
			// in ~50 s. alice/asyoulik stay on DRT (paq8 loses to brotli there).
			if (method_exists('fractal_zip', 'ultra_compression_enabled')
				&& fractal_zip::ultra_compression_enabled()
				&& (bool) preg_match('/\\.txt$/i', (string) $member['rel'])
				&& strlen((string) $member['bytes']) >= 256 * 1024
				&& strlen((string) $member['bytes']) <= 1024 * 1024
				&& function_exists('fractal_zip_paq_compress_file')
				&& function_exists('fractal_zip_paq_discover_executable')
				&& function_exists('fractal_zip_paq_wrap_wire_v2')) {
				$exe = fractal_zip_paq_discover_executable('paq8px');
				if (is_string($exe) && $exe !== '') {
					$savedLvl = getenv('FRACTAL_ZIP_PAQ8PX_LEVEL');
					putenv('FRACTAL_ZIP_PAQ8PX_LEVEL=5');
					$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzprose8_' . bin2hex(random_bytes(4));
					@mkdir($box, 0700, true);
					$inPath = $box . DIRECTORY_SEPARATOR . 'in.txt';
					if (@file_put_contents($inPath, (string) $member['bytes']) !== false) {
						$pack = fractal_zip_paq_compress_file('paq8px', $exe, $inPath);
						if (is_array($pack) && isset($pack['bytes']) && is_string($pack['bytes'])
							&& $pack['bytes'] !== '') {
							$p8Wire = fractal_zip_paq_wrap_wire_v2(
								'paq8px',
								$pack['bytes'],
								(string) $member['rel']
							);
							if (is_string($p8Wire) && $p8Wire !== ''
								&& strlen($p8Wire) < strlen($bestProse)) {
								$bestProse = $p8Wire;
								$bestProseCodec = 'paq';
								$bestProseLabel = 'paq8px';
							}
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
		}
		if ($bestProseLabel !== (string) ($pick['tool'] ?? 'zpaq3')
			|| $bestProseCodec !== 'paq'
			|| strlen($bestProse) < strlen((string) $pick['wire'])) {
			$outProse = method_exists($fz, 'zip_folder_fzc_output_path')
				? (string) $fz->zip_folder_fzc_output_path($dir)
				: (rtrim($dir, "/\\") . '.fz');
			$fzcAltP = preg_replace('/\.fz$/', '.fzc', $outProse);
			if (is_string($fzcAltP) && $fzcAltP !== $outProse && is_file($fzcAltP)) {
				@unlink($fzcAltP);
			}
			if (file_put_contents($outProse, $bestProse) !== false) {
				$fz->array_fractal_zipped_strings_of_files = array($member['rel'] => '');
				fractal_zip::$last_outer_codec = $bestProseCodec;
				fractal_zip::$last_written_container_codec = $bestProseCodec;
				if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
					@fwrite(STDERR, '[binary-cm] ' . $member['rel']
						. ' lane=prose tool=' . $bestProseLabel
						. ' beat_zpaq3_wire=' . number_format(strlen((string) $pick['wire']))
						. ' win=' . number_format(strlen($bestProse)) . "\n");
				}
				return true;
			}
		}
	}
	// Guard against timeout/partial CM arcs worse than native zpaq5. Compare arc
	 // payloads (not FZpq wire). Accept ties — FZpq keeps the member path, and the
	// fast tip often *is* zpaq5 under a short time budget.
	// Large tips already ratio-gated in compress_member — skip a second -m5 encode.
	// Lifestyle small pseudo-PDF: compress_member already returned the sole tip;
	// a second zpaq5 here was another ~0.5 s of wall (test_files121).
	$plainLen = strlen((string) $member['bytes']);
	$skipGuard = ($plainLen >= 4 * 1024 * 1024
			&& in_array((string) ($pick['tool'] ?? ''), array('mcm', 'lpaq9l', 'paq8px'), true))
		|| ($smallPdf && in_array((string) ($pick['tool'] ?? ''), array('mcm', 'lpaq9l'), true));
	if (!$skipGuard && ($member['lane'] ?? '') === 'binary'
		&& !preg_match('/^zpaq[1-9]$/', (string) ($pick['tool'] ?? ''))) {
		$z = fractal_zip_paq_zpaq_compress_bytes($member['bytes'], 5);
		if (is_array($z) && is_string($z['bytes']) && $z['bytes'] !== ''
			&& (int) $pick['arc_bytes'] > strlen($z['bytes'])) {
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[binary-cm] skip ' . $member['rel']
					. ': arc=' . number_format((int) $pick['arc_bytes'])
					. ' > zpaq5=' . number_format(strlen($z['bytes'])) . "\n");
			}
			return false;
		}
	}
	$out = method_exists($fz, 'zip_folder_fzc_output_path')
		? (string) $fz->zip_folder_fzc_output_path($dir)
		: (rtrim($dir, "/\\") . '.fz');
	// Drop a co-located .fzc if benches still look for it.
	$fzcAlt = preg_replace('/\.fz$/', '.fzc', $out);
	if (is_string($fzcAlt) && $fzcAlt !== $out && is_file($fzcAlt)) {
		@unlink($fzcAlt);
	}
	if (file_put_contents($out, $pick['wire']) === false) {
		return false;
	}
	if (property_exists($fz, 'array_fractal_zipped_strings_of_files') || true) {
		$fz->array_fractal_zipped_strings_of_files = array($member['rel'] => '');
	}
	if (class_exists('fractal_zip', false)) {
		fractal_zip::$last_outer_codec = 'paq';
		fractal_zip::$last_written_container_codec = 'paq';
	}
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		@fwrite(STDERR, '[binary-cm] ' . $member['rel']
			. ' lane=' . (string) ($member['lane'] ?? 'binary')
			. ' profile=' . $member['profile']
			. ' tool=' . $pick['tool']
			. ' arc=' . number_format($pick['arc_bytes'])
			. ' wire=' . number_format(strlen($pick['wire']))
			. ' sec=' . $pick['seconds'] . "\n");
	}
	return true;
}
