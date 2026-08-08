<?php
declare(strict_types=1);

/**
 * Web URL references for fractal_string (FZWR trailer + @w tokens).
 *
 * Encode: mirror via live_browser, register sht code, replace large chunks.
 * Decode: online-only fetch chain with sha256 verification.
 */

const FRACTAL_ZIP_FZWR_MAGIC = 'FZWR';
const FRACTAL_ZIP_FZWR_VERSION_WHOLE = "\x01";
const FRACTAL_ZIP_FZWR_VERSION_PIECE = "\x02";
const FRACTAL_ZIP_WEB_REF_TOKEN_RX = '/@w\{([23456789ABCDEFGHJKMNPQRSTVWXYZ]{8})\}/';

function fractal_zip_web_ref_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_WEB_REF');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
	}
	// Opt-in only. Lifestyle already forbids live_browser / network during general zip
	// (HTML corpora like test_files107 otherwise burn the 45s case budget on dead URL
	// probes). Fair-full (LIFESTYLE=0) must also stay offline by default — enabling
	// web-ref there can leave the source folder missing mid-zip (test_files13).
	return false;
}

function fractal_zip_web_ref_min_gain_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_MIN_GAIN_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) $e);
	}
	return 64;
}

function fractal_zip_web_ref_min_stability(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_MIN_STABILITY');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) $e);
	}
	return 50;
}

function fractal_zip_web_ref_url_literal_min_gain_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_URL_LITERAL_MIN_GAIN');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) $e);
	}
	return min(32, fractal_zip_web_ref_min_gain_bytes());
}

function fractal_zip_web_ref_url_literal_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_URL_LITERAL');
	return $e === false || $e === '' || $e !== '0';
}

function fractal_zip_web_ref_corpus_pieces_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_CORPUS_PIECES');
	return $e === false || $e === '' || $e !== '0';
}

function fractal_zip_web_ref_corpus_min_gain_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_CORPUS_MIN_GAIN');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) $e);
	}
	return min(32, fractal_zip_web_ref_min_gain_bytes());
}

function fractal_zip_web_ref_is_url_literal_chunk(string $chunk, string $candidateUrl): bool
{
	if (!preg_match('#^https?://#i', $chunk)) {
		return false;
	}
	$chunkNorm = rtrim($chunk, '.,;');
	$urlNorm = rtrim($candidateUrl, '.,;');
	if (strcasecmp($chunkNorm, $urlNorm) === 0) {
		return true;
	}
	if (preg_match('#^https?://([a-z]{2}\.)?wikipedia\.org/wiki/([^?#]+)#i', $urlNorm, $m)) {
		$wiki = 'https://en.wikipedia.org/w/index.php?title=' . rawurlencode(rawurldecode(str_replace('_', ' ', $m[2]))) . '&action=raw';
		return strcasecmp($chunkNorm, $wiki) === 0;
	}
	return false;
}

/**
 * Register a repeated URL string as its own piece (corpus bytes; mirror for stability metadata).
 *
 * @return array{fractal_string: string, entries: list<array<string, mixed>>, saved: int}
 */
function fractal_zip_web_ref_try_replace_url_literal(string $fractalString, string $chunk, string $candidateUrl): array
{
	$out = array('fractal_string' => $fractalString, 'entries' => array(), 'saved' => 0);
	if (!fractal_zip_web_ref_url_literal_enabled() || !fractal_zip_web_ref_is_url_literal_chunk($chunk, $candidateUrl)) {
		return $out;
	}
	if (strlen($chunk) <= 11) {
		return $out;
	}
	fractal_zip_web_ref_bootstrap_live_browser();
	fractal_zip_web_ref_bootstrap_sht();
	if (!function_exists('sht_register_piece')) {
		return $out;
	}
	$canonical = rtrim($candidateUrl, '.,;');
	if (preg_match('#^https?://([a-z]{2}\.)?wikipedia\.org/wiki/([^?#]+)#i', $canonical, $m)) {
		$canonical = 'https://en.wikipedia.org/w/index.php?title=' . rawurlencode(rawurldecode(str_replace('_', ' ', $m[2]))) . '&action=raw';
	}
	$mirrorRel = '';
	$lastModified = '';
	$etag = '';
	$pageSha = '';
	$score = 35;
	if (getenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR') === '1') {
		if (stripos($canonical, 'archive.org') !== false) {
			$score = 80;
		} elseif (preg_match('#^https?://[^/]+\.(gov|edu|org)([:/]|$)#i', $canonical)) {
			$score = 55;
		} else {
			$score = 40;
		}
	} elseif (function_exists('web_ref_ensure_mirrored')) {
		try {
			$mir = web_ref_ensure_mirrored($canonical);
			$score = (int) ($mir['stability_score'] ?? $score);
			$mirrorRel = (string) ($mir['mirror_rel_path'] ?? '');
			$lastModified = (string) ($mir['last_modified'] ?? '');
			$etag = (string) ($mir['etag'] ?? '');
			$pageSha = (string) ($mir['sha256'] ?? '');
			$canonical = (string) ($mir['canonical_url'] ?? $canonical);
		} catch (Throwable $e) {
			if (stripos($canonical, 'archive.org') === false) {
				return $out;
			}
			$score = 75;
		}
	}
	if ($score < fractal_zip_web_ref_min_stability()) {
		return $out;
	}
	$pieceBytes = $chunk;
	$sha = hash('sha256', $pieceBytes);
	$row = sht_register_piece(array(
		'canonical_url' => $canonical,
		'piece_bytes' => $pieceBytes,
		'piece_sha256' => $sha,
		'mirror_rel_path' => $mirrorRel,
		'last_modified' => $lastModified,
		'etag' => $etag,
		'piece_offset' => 0,
		'piece_length' => strlen($pieceBytes),
		'page_sha256' => $pageSha,
	));
	$code = (string) ($row['code'] ?? '');
	if ($code === '' || !function_exists('sht_alphabet_is_valid_code') || !sht_alphabet_is_valid_code($code)) {
		return $out;
	}
	$token = '@w{' . $code . '}';
	$entry = array(
		'code' => $code,
		'sha256' => $sha,
		'canonical_url' => (string) ($row['canonical_url'] ?? $canonical),
		'last_modified' => (string) ($row['last_modified'] ?? ''),
		'etag' => (string) ($row['etag'] ?? ''),
		'piece_offset' => 0,
		'piece_length' => strlen($pieceBytes),
	);
	$occurrences = max(1, substr_count($fractalString, $chunk));
	$inlineSaved = $occurrences * (strlen($chunk) - strlen($token));
	$saved = $inlineSaved - fractal_zip_web_ref_trailer_cost_per_entry($entry);
	if ($saved < fractal_zip_web_ref_url_literal_min_gain_bytes()) {
		return $out;
	}
	$out['fractal_string'] = str_replace($chunk, $token, $fractalString);
	$out['entries'][] = $entry;
	$out['saved'] = $saved;
	return $out;
}

function fractal_zip_web_ref_sht_root(): string
{
	$e = getenv('FRACTAL_ZIP_SHT_ROOT');
	if ($e !== false && trim((string) $e) !== '') {
		return rtrim((string) $e, '/\\') . DIRECTORY_SEPARATOR;
	}
	return '/srv/http/sht' . DIRECTORY_SEPARATOR;
}

function fractal_zip_web_ref_live_browser_root(): string
{
	$e = getenv('FRACTAL_ZIP_LIVE_BROWSER_ROOT');
	if ($e !== false && trim((string) $e) !== '') {
		return rtrim((string) $e, '/\\') . DIRECTORY_SEPARATOR;
	}
	return '/srv/http/live_browser' . DIRECTORY_SEPARATOR;
}

function fractal_zip_web_ref_bootstrap_sht(): void
{
	static $done = false;
	if ($done) {
		return;
	}
	$done = true;
	$boot = fractal_zip_web_ref_sht_root() . 'bootstrap.php';
	if (is_file($boot)) {
		require_once $boot;
	}
}

function fractal_zip_web_ref_bootstrap_live_browser(): void
{
	static $done = false;
	if ($done) {
		return;
	}
	$done = true;
	$path = fractal_zip_web_ref_live_browser_root() . 'lib' . DIRECTORY_SEPARATOR . 'web_ref_mirror.php';
	if (is_file($path)) {
		require_once $path;
	}
}

/** @var list<array<string, mixed>> */
$GLOBALS['fractal_zip_fzwr_entries'] = array();

/**
 * @return array<string, mixed>|null
 */
function fractal_zip_web_ref_peel_fzwr_from_blob(string $blob): ?array
{
	$magic = FRACTAL_ZIP_FZWR_MAGIC;
	$pos = strrpos($blob, $magic);
	if ($pos === false || $pos + 6 > strlen($blob)) {
		return null;
	}
	if (substr($blob, $pos, 4) !== $magic) {
		return null;
	}
	$ver = $blob[$pos + 4];
	if ($ver !== FRACTAL_ZIP_FZWR_VERSION_WHOLE && $ver !== FRACTAL_ZIP_FZWR_VERSION_PIECE) {
		return null;
	}
	$off = $pos + 5;
	$count = unpack('n', substr($blob, $off, 2));
	$off += 2;
	if (!is_array($count)) {
		return null;
	}
	$n = (int) $count[1];
	$entries = array();
	for ($i = 0; $i < $n; $i++) {
		if ($off + 8 + 32 > strlen($blob)) {
			return null;
		}
		$code = substr($blob, $off, 8);
		$off += 8;
		if (!preg_match(FRACTAL_ZIP_WEB_REF_TOKEN_RX, '@w{' . $code . '}')) {
			return null;
		}
		$sha = substr($blob, $off, 32);
		$off += 32;
		$pieceOffset = 0;
		$pieceLength = 0;
		if ($ver === FRACTAL_ZIP_FZWR_VERSION_PIECE) {
			if ($off + 8 > strlen($blob)) {
				return null;
			}
			$pieceMeta = unpack('Noffset/Nlength', substr($blob, $off, 8));
			$off += 8;
			if (!is_array($pieceMeta)) {
				return null;
			}
			$pieceOffset = (int) $pieceMeta['offset'];
			$pieceLength = (int) $pieceMeta['length'];
		}
		if ($off >= strlen($blob)) {
			return null;
		}
		$urlLen = ord($blob[$off]);
		$off++;
		if ($off + $urlLen > strlen($blob)) {
			return null;
		}
		$url = substr($blob, $off, $urlLen);
		$off += $urlLen;
		if ($off >= strlen($blob)) {
			return null;
		}
		$lmLen = ord($blob[$off]);
		$off++;
		if ($off + $lmLen > strlen($blob)) {
			return null;
		}
		$lm = substr($blob, $off, $lmLen);
		$off += $lmLen;
		if ($off >= strlen($blob)) {
			return null;
		}
		$etLen = ord($blob[$off]);
		$off++;
		if ($off + $etLen > strlen($blob)) {
			return null;
		}
		$etag = substr($blob, $off, $etLen);
		$off += $etLen;
		$entries[] = array(
			'code' => $code,
			'sha256' => bin2hex($sha),
			'canonical_url' => $url,
			'last_modified' => $lm,
			'etag' => $etag,
			'piece_offset' => $pieceOffset,
			'piece_length' => $pieceLength,
		);
	}
	return array(
		'payloadLen' => $pos,
		'entries' => $entries,
		'version' => $ver === FRACTAL_ZIP_FZWR_VERSION_PIECE ? 2 : 1,
	);
}

function fractal_zip_web_ref_strip_fzwr_suffix(string $blob): string
{
	$meta = fractal_zip_web_ref_peel_fzwr_from_blob($blob);
	if ($meta === null) {
		return $blob;
	}
	$GLOBALS['fractal_zip_fzwr_entries'] = is_array($meta['entries']) ? $meta['entries'] : array();
	return substr($blob, 0, (int) $meta['payloadLen']);
}

/**
 * @param list<array<string, mixed>> $entries
 */
function fractal_zip_web_ref_build_trailer(array $entries): string
{
	$usePiece = false;
	foreach ($entries as $e) {
		if ((int) ($e['piece_length'] ?? 0) > 0 || (int) ($e['piece_offset'] ?? 0) > 0) {
			$usePiece = true;
			break;
		}
	}
	$ver = $usePiece ? FRACTAL_ZIP_FZWR_VERSION_PIECE : FRACTAL_ZIP_FZWR_VERSION_WHOLE;
	$magic = FRACTAL_ZIP_FZWR_MAGIC . $ver;
	$body = pack('n', count($entries));
	foreach ($entries as $e) {
		$code = (string) ($e['code'] ?? '');
		if (strlen($code) !== 8) {
			continue;
		}
		$shaHex = strtolower(preg_replace('/[^a-f0-9]/', '', (string) ($e['sha256'] ?? '')) ?? '');
		$shaBin = strlen($shaHex) === 64 ? hex2bin($shaHex) : false;
		if ($shaBin === false) {
			continue;
		}
		$url = (string) ($e['canonical_url'] ?? '');
		$lm = (string) ($e['last_modified'] ?? '');
		$etag = (string) ($e['etag'] ?? '');
		$body .= $code . $shaBin;
		if ($ver === FRACTAL_ZIP_FZWR_VERSION_PIECE) {
			$body .= pack('NN', (int) ($e['piece_offset'] ?? 0), (int) ($e['piece_length'] ?? 0));
		}
		$body .= chr(strlen($url)) . $url . chr(strlen($lm)) . $lm . chr(strlen($etag)) . $etag;
	}
	return $magic . $body;
}

function fractal_zip_web_ref_trailer_cost_per_entry(array $entry): int
{
	$base = 8 + 32 + 1 + strlen((string) ($entry['canonical_url'] ?? '')) + 2;
	if ((int) ($entry['piece_length'] ?? 0) > 0 || (int) ($entry['piece_offset'] ?? 0) > 0) {
		$base += 8;
	}
	return $base;
}

/**
 * Try registering URL content and replacing matching substring in fractal_string.
 *
 * @return array{fractal_string: string, entries: list<array<string, mixed>>, saved: int}
 */
function fractal_zip_web_ref_try_replace_chunk(string $fractalString, string $chunk, string $candidateUrl): array
{
	$out = array('fractal_string' => $fractalString, 'entries' => array(), 'saved' => 0);
	if (!fractal_zip_web_ref_enabled() || $chunk === '' || strlen($chunk) < fractal_zip_web_ref_min_gain_bytes() + 20) {
		return $out;
	}
	if (strpos($fractalString, $chunk) === false) {
		return $out;
	}
	fractal_zip_web_ref_bootstrap_live_browser();
	fractal_zip_web_ref_bootstrap_sht();
	if (!function_exists('web_ref_probe_piece_at_url') || !function_exists('sht_registry_register_link')) {
		return $out;
	}
	$probe = web_ref_probe_piece_at_url($candidateUrl, $chunk);
	if ($probe === null) {
		if (fractal_zip_web_ref_url_literal_enabled() && fractal_zip_web_ref_is_url_literal_chunk($chunk, $candidateUrl)) {
			return fractal_zip_web_ref_try_replace_url_literal($fractalString, $chunk, $candidateUrl);
		}
		return $out;
	}
	$score = (int) ($probe['stability_score'] ?? 0);
	if ($score < fractal_zip_web_ref_min_stability()) {
		return $out;
	}
	$pieceBytes = (string) ($probe['piece_bytes'] ?? '');
	$sha = (string) ($probe['piece_sha256'] ?? hash('sha256', $pieceBytes));
	if ($pieceBytes === '' || hash('sha256', $pieceBytes) !== $sha) {
		return $out;
	}
	if (function_exists('sht_register_piece')) {
		$row = sht_register_piece(array(
			'canonical_url' => (string) ($probe['canonical_url'] ?? $candidateUrl),
			'piece_bytes' => $pieceBytes,
			'piece_sha256' => $sha,
			'mirror_rel_path' => (string) ($probe['mirror_rel_path'] ?? ''),
			'last_modified' => (string) ($probe['last_modified'] ?? ''),
			'etag' => (string) ($probe['etag'] ?? ''),
			'piece_offset' => (int) ($probe['piece_offset'] ?? 0),
			'piece_length' => (int) ($probe['piece_length'] ?? strlen($pieceBytes)),
			'page_sha256' => (string) ($probe['page_sha256'] ?? ''),
		));
	} else {
		$row = sht_registry_register_link(array(
			'canonical_url' => (string) ($probe['canonical_url'] ?? $candidateUrl),
			'sha256' => $sha,
			'mirror_rel' => (string) ($probe['mirror_rel_path'] ?? ''),
			'last_modified' => (string) ($probe['last_modified'] ?? ''),
			'etag' => (string) ($probe['etag'] ?? ''),
			'piece_offset' => (int) ($probe['piece_offset'] ?? 0),
			'piece_length' => (int) ($probe['piece_length'] ?? strlen($pieceBytes)),
			'page_sha256' => (string) ($probe['page_sha256'] ?? ''),
		));
	}
	$code = (string) ($row['code'] ?? '');
	if ($code === '' || !function_exists('sht_alphabet_is_valid_code') || !sht_alphabet_is_valid_code($code)) {
		return $out;
	}
	$token = '@w{' . $code . '}';
	$entry = array(
		'code' => $code,
		'sha256' => $sha,
		'canonical_url' => (string) ($row['canonical_url'] ?? $candidateUrl),
		'last_modified' => (string) ($row['last_modified'] ?? ''),
		'etag' => (string) ($row['etag'] ?? ''),
		'piece_offset' => (int) ($probe['piece_offset'] ?? 0),
		'piece_length' => (int) ($probe['piece_length'] ?? strlen($pieceBytes)),
	);
	$trailerCost = fractal_zip_web_ref_trailer_cost_per_entry($entry);
	$occurrences = max(1, substr_count($fractalString, $chunk));
	$inlineSaved = $occurrences * (strlen($chunk) - strlen($token));
	$saved = $inlineSaved - $trailerCost;
	if ($saved < fractal_zip_web_ref_min_gain_bytes()) {
		return $out;
	}
	$out['fractal_string'] = str_replace($chunk, $token, $fractalString);
	$out['entries'][] = $entry;
	$out['saved'] = $saved;
	return $out;
}

/**
 * Fetch bytes for a web ref entry (online-only decode chain).
 */
function fractal_zip_web_ref_fetch_bytes(array $entry): ?string
{
	$expect = strtolower(preg_replace('/[^a-f0-9]/', '', (string) ($entry['sha256'] ?? '')) ?? '');
	if (strlen($expect) !== 64) {
		return null;
	}
	fractal_zip_web_ref_bootstrap_sht();
	$code = (string) ($entry['code'] ?? '');
	$row = null;
	if ($code !== '' && function_exists('sht_registry_find_by_code')) {
		$row = sht_registry_find_by_code($code);
	}
	if (is_array($row)) {
		$entry = array_merge($row, $entry);
	}
	$pieceOffset = (int) ($entry['piece_offset'] ?? 0);
	$pieceLength = (int) ($entry['piece_length'] ?? 0);

	$filesRoot = getenv('FILES_ROOT') ?: '/srv/http/files/';
	$blobPath = rtrim((string) $filesRoot, '/\\') . DIRECTORY_SEPARATOR . $expect;
	if (is_file($blobPath)) {
		$bytes = file_get_contents($blobPath);
		if (is_string($bytes) && hash('sha256', $bytes) === $expect) {
			return $bytes;
		}
	}
	if (is_array($row) && ($row['mirror_rel'] ?? '') !== '') {
		$mirror = fractal_zip_web_ref_live_browser_root() . 'scrape' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $row['mirror_rel']);
		if (is_file($mirror)) {
			$pageBytes = file_get_contents($mirror);
			if (is_string($pageBytes)) {
				if ($pieceLength > 0 && function_exists('web_ref_slice_verified_piece')) {
					$slice = web_ref_slice_verified_piece($pageBytes, $pieceOffset, $pieceLength, $expect);
					if ($slice !== null) {
						return $slice;
					}
				}
				if (hash('sha256', $pageBytes) === $expect) {
					return $pageBytes;
				}
			}
		}
	}
	$url = (string) ($entry['canonical_url'] ?? ($row['canonical_url'] ?? ''));
	if ($url !== '' && function_exists('browse_fetch_url')) {
		fractal_zip_web_ref_bootstrap_live_browser();
		$pageBytes = null;
		if (function_exists('web_ref_ensure_mirrored')) {
			try {
				$mir = web_ref_ensure_mirrored($url);
				$pageBytes = (string) ($mir['bytes'] ?? '');
			} catch (Throwable $e) {
				$pageBytes = null;
			}
		}
		if ($pageBytes === null && function_exists('browse_robust_fetch')) {
			$r = browse_robust_fetch($url);
			if (is_array($r) && isset($r['body']) && is_string($r['body'])) {
				$pageBytes = $r['body'];
			}
		}
		if (is_string($pageBytes) && $pageBytes !== '') {
			if ($pieceLength > 0 && function_exists('web_ref_slice_verified_piece')) {
				$slice = web_ref_slice_verified_piece($pageBytes, $pieceOffset, $pieceLength, $expect);
				if ($slice !== null) {
					return $slice;
				}
			}
			if (hash('sha256', $pageBytes) === $expect) {
				return $pageBytes;
			}
		}
	}
	return null;
}

function fractal_zip_web_ref_expand_fractal_string(string $fractalString): string
{
	if (!preg_match_all(FRACTAL_ZIP_WEB_REF_TOKEN_RX, $fractalString, $m)) {
		return $fractalString;
	}
	$entries = is_array($GLOBALS['fractal_zip_fzwr_entries'] ?? null) ? $GLOBALS['fractal_zip_fzwr_entries'] : array();
	$byCode = array();
	foreach ($entries as $e) {
		if (isset($e['code'])) {
			$byCode[(string) $e['code']] = $e;
		}
	}
	foreach ($m[1] as $code) {
		// Only expand codes declared by this container's FZWR trailer. Plain source
		// text can legitimately contain @w{XXXXXXXX}; expanding it would corrupt the
		// byte-identical restore (or throw on fetch).
		if (!isset($byCode[$code])) {
			continue;
		}
		$token = '@w{' . $code . '}';
		$entry = $byCode[$code];
		if (!isset($entry['sha256']) && function_exists('sht_registry_find_by_code')) {
			fractal_zip_web_ref_bootstrap_sht();
			$row = sht_registry_find_by_code($code);
			if (is_array($row)) {
				$entry = array_merge($entry, $row);
			}
		}
		$bytes = fractal_zip_web_ref_fetch_bytes($entry);
		if ($bytes === null) {
			throw new RuntimeException('fractal_zip_web_ref_fetch_failed: ' . $code);
		}
		$fractalString = str_replace($token, $bytes, $fractalString);
	}
	return $fractalString;
}

function fractal_zip_web_ref_append_trailer_to_blob(string $blob, array $entries): string
{
	if ($entries === array()) {
		return $blob;
	}
	return $blob . fractal_zip_web_ref_build_trailer($entries);
}
