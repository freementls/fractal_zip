<?php
declare(strict_types=1);

/**
 * Raw-member web-ref apply pass (before recursive_zip_folder / marker generation).
 *
 * This applies @w tokens directly to enwik virtual member files on disk so measured
 * container bytes reflect savings, instead of probing only post-multipass fractal_string.
 */

function fractal_zip_web_ref_whole_page_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE');
	if ($e === false || trim((string) $e) === '') {
		return fractal_zip_web_ref_enabled();
	}
	$v = strtolower(trim((string) $e));
	return !in_array($v, array('0', 'false', 'no', 'off'), true);
}

function fractal_zip_web_ref_whole_page_min_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE_MIN_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		return max(128, (int) $e);
	}
	return 2048;
}

function fractal_zip_web_ref_whole_page_max_pages(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE_MAX');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) $e);
	}
	return 200;
}

/**
 * @param array<string, string> $replacements old => new
 */
function fractal_zip_web_ref_apply_to_member_file(string $path, array $replacements): int
{
	if ($replacements === array() || !is_file($path)) {
		return 0;
	}
	$raw = @file_get_contents($path);
	if (!is_string($raw) || $raw === '') {
		return 0;
	}
	$new = str_replace(array_keys($replacements), array_values($replacements), $raw, $count);
	if ($count <= 0 || $new === $raw) {
		return 0;
	}
	if (@file_put_contents($path, $new) === false) {
		return 0;
	}
	return strlen($raw) - strlen($new);
}

/**
 * @return list<string>
 */
function fractal_zip_web_ref_member_paths_from_ctx(array $ctx): array
{
	$virtualDir = (string) ($ctx['virtualDir'] ?? '');
	$out = array();
	if ($virtualDir === '' || !is_array($ctx['memberRelPaths'] ?? null)) {
		return $out;
	}
	foreach ($ctx['memberRelPaths'] as $rel) {
		$path = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $rel);
		if (is_file($path)) {
			$out[] = $path;
		}
	}
	return $out;
}

/**
 * @return list<array{
 *   title: string,
 *   oldid: int,
 *   canonical_url: string,
 *   piece_bytes: string,
 *   piece_offset: int,
 *   piece_length: int,
 *   page_sha256: string,
 *   score: int
 * }>
 */
function fractal_zip_web_ref_collect_whole_page_candidates(string $corpus): array
{
	if (!fractal_zip_web_ref_whole_page_enabled() || !fractal_zip_web_ref_is_enwik_corpus($corpus)) {
		return array();
	}
	$minBytes = fractal_zip_web_ref_whole_page_min_bytes();
	$maxPages = fractal_zip_web_ref_whole_page_max_pages();
	$out = array();
	$cursor = 0;
	$seen = 0;
	$len = strlen($corpus);
	while ($cursor < $len) {
		$pageStart = strpos($corpus, '<page', $cursor);
		if ($pageStart === false) {
			break;
		}
		$pageEnd = strpos($corpus, '</page>', $pageStart);
		if ($pageEnd === false) {
			break;
		}
		$pageEnd += 7;
		$pageXml = substr($corpus, $pageStart, $pageEnd - $pageStart);
		$cursor = $pageEnd;
		$seen++;
		if (!preg_match('/<title>([^<]+)<\/title>/', $pageXml, $tm)
			|| !preg_match('/<revision>\s*<id>(\d+)<\/id>/s', $pageXml, $rm)
			|| !preg_match('/<text\b[^>]*>(.*?)<\/text>/s', $pageXml, $xm)) {
			continue;
		}
		$textBody = (string) $xm[1];
		$textLen = strlen($textBody);
		if ($textLen < $minBytes) {
			continue;
		}
		$title = html_entity_decode((string) $tm[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$oldid = (int) $rm[1];
		if ($title === '' || $oldid <= 0) {
			continue;
		}
		$out[] = array(
			'title' => $title,
			'oldid' => $oldid,
			'canonical_url' => fractal_zip_web_ref_wikipedia_raw_url($title, $oldid),
			'piece_bytes' => $textBody,
			'piece_offset' => 0,
			'piece_length' => $textLen,
			'page_sha256' => hash('sha256', $textBody),
			'score' => $textLen,
		);
		if ($maxPages > 0 && count($out) >= $maxPages) {
			break;
		}
		if ($seen > 500000) {
			break;
		}
	}
	usort($out, static function (array $a, array $b): int {
		return (int) ($b['score'] ?? 0) <=> (int) ($a['score'] ?? 0);
	});
	if ($maxPages > 0 && count($out) > $maxPages) {
		$out = array_slice($out, 0, $maxPages);
	}
	return $out;
}

/**
 * @param array<string, mixed> $meta
 * @return array{entry: array<string, mixed>, token: string, saved: int}|null
 */
function fractal_zip_web_ref_register_and_tokenize_piece(string $pieceBytes, string $canonicalUrl, array $meta): ?array
{
	if ($pieceBytes === '' || $canonicalUrl === '') {
		return null;
	}
	fractal_zip_web_ref_bootstrap_sht();
	if (!function_exists('sht_register_piece') || !function_exists('sht_alphabet_is_valid_code')) {
		return null;
	}
	$pieceLength = (int) ($meta['piece_length'] ?? strlen($pieceBytes));
	if ($pieceLength <= 0) {
		$pieceLength = strlen($pieceBytes);
	}
	$pieceOffset = max(0, (int) ($meta['piece_offset'] ?? 0));
	$sha = hash('sha256', $pieceBytes);
	$row = sht_register_piece(array(
		'canonical_url' => $canonicalUrl,
		'piece_bytes' => $pieceBytes,
		'piece_sha256' => $sha,
		'mirror_rel_path' => (string) ($meta['mirror_rel_path'] ?? ''),
		'last_modified' => (string) ($meta['last_modified'] ?? ''),
		'etag' => (string) ($meta['etag'] ?? ''),
		'piece_offset' => $pieceOffset,
		'piece_length' => $pieceLength,
		'page_sha256' => (string) ($meta['page_sha256'] ?? ''),
	));
	$code = (string) ($row['code'] ?? '');
	if ($code === '' || !sht_alphabet_is_valid_code($code)) {
		return null;
	}
	$token = '@w{' . $code . '}';
	$entry = array(
		'code' => $code,
		'sha256' => $sha,
		'canonical_url' => (string) ($row['canonical_url'] ?? $canonicalUrl),
		'last_modified' => (string) ($row['last_modified'] ?? ''),
		'etag' => (string) ($row['etag'] ?? ''),
		'piece_offset' => $pieceOffset,
		'piece_length' => $pieceLength,
	);
	$occurrences = max(1, (int) ($meta['occurrences'] ?? 1));
	$inlineSaved = $occurrences * (strlen($pieceBytes) - strlen($token));
	$saved = $inlineSaved - fractal_zip_web_ref_trailer_cost_per_entry($entry);
	$minGain = max(0, (int) ($meta['min_gain'] ?? fractal_zip_web_ref_min_gain_bytes()));
	if ($saved < $minGain) {
		return null;
	}
	return array(
		'entry' => $entry,
		'token' => $token,
		'saved' => $saved,
	);
}

/**
 * @param list<string> $needles
 * @return array<string, int> needle => count
 */
function fractal_zip_web_ref_count_occurrences_in_corpus(string $corpus, array $needles): array
{
	$out = array();
	foreach ($needles as $n) {
		if ($n === '') {
			continue;
		}
		$out[$n] = substr_count($corpus, $n);
	}
	return $out;
}

/**
 * @return array{
 *   entries: list<array<string, mixed>>,
 *   saved: int,
 *   replacements: array<string, string>,
 *   stats: array<string, int>
 * }
 */
function fractal_zip_web_ref_apply_corpus_replacements(fractal_zip $fz, string $corpus): array
{
	$out = array(
		'entries' => array(),
		'saved' => 0,
		'replacements' => array(),
		'stats' => array(
			'whole_page_matches' => 0,
			'wayback_matches' => 0,
			'corpus_matches' => 0,
			'raw_apply_saved' => 0,
		),
	);
	if (!fractal_zip_web_ref_enabled() || $corpus === '' || !fractal_zip_web_ref_is_enwik_corpus($corpus)) {
		return $out;
	}
	if (fractal_zip_web_ref_whole_page_enabled() && getenv('FRACTAL_ZIP_WEB_REF_URL_LITERAL') === false) {
		putenv('FRACTAL_ZIP_WEB_REF_URL_LITERAL=0');
	}

	$usedByChunk = array();
	$entriesByToken = array();

	$wholePageCandidates = fractal_zip_web_ref_collect_whole_page_candidates($corpus);
	$wholeNeedles = array();
	foreach ($wholePageCandidates as $c) {
		$wholeNeedles[] = (string) ($c['piece_bytes'] ?? '');
	}
	$wholeCounts = fractal_zip_web_ref_count_occurrences_in_corpus($corpus, $wholeNeedles);
	foreach ($wholePageCandidates as $c) {
		$piece = (string) ($c['piece_bytes'] ?? '');
		if ($piece === '' || isset($usedByChunk[$piece])) {
			continue;
		}
		$occ = (int) ($wholeCounts[$piece] ?? 0);
		if ($occ <= 0) {
			continue;
		}
		$reg = fractal_zip_web_ref_register_and_tokenize_piece($piece, (string) $c['canonical_url'], array(
			'piece_offset' => 0,
			'piece_length' => strlen($piece),
			'page_sha256' => (string) ($c['page_sha256'] ?? hash('sha256', $piece)),
			'occurrences' => $occ,
			'min_gain' => fractal_zip_web_ref_min_gain_bytes(),
		));
		if ($reg === null) {
			continue;
		}
		$out['replacements'][$piece] = (string) $reg['token'];
		$entriesByToken[(string) $reg['token']] = $reg['entry'];
		$out['saved'] += (int) ($reg['saved'] ?? 0);
		$usedByChunk[$piece] = true;
		$out['stats']['whole_page_matches']++;
	}

	$maxChunks = fractal_zip_web_ref_probe_max_chunks();
	$seedChunks = fractal_zip_web_ref_find_enwik_obvious_chunks($corpus, $maxChunks * 2);
	$pending = array();
	foreach ($seedChunks as $item) {
		$chunk = (string) ($item['chunk'] ?? '');
		if ($chunk === '' || isset($usedByChunk[$chunk])) {
			continue;
		}
		$pending[$chunk] = true;
	}
	if ($pending === array()) {
		$out['entries'] = array_values($entriesByToken);
		return $out;
	}

	$skipMirror = (getenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR') === '1');
	$urlRows = fractal_zip_web_ref_collect_all_url_candidates($corpus, fractal_zip_web_ref_probe_max_urls());
	if (!$skipMirror && $urlRows !== array()) {
		fractal_zip_web_ref_bootstrap_live_browser();
		if (function_exists('web_ref_ensure_mirrored') && function_exists('web_ref_find_pieces_in_body')) {
			foreach ($urlRows as $urlRow) {
				if ($pending === array()) {
					break;
				}
				$url = fractal_zip_web_ref_normalize_probe_url((string) ($urlRow['url'] ?? ''));
				if ($url === '') {
					continue;
				}
				$needles = array_keys($pending);
				$usedWayback = false;
				try {
					$mir = web_ref_ensure_mirrored($url);
				} catch (Throwable $e) {
					if (function_exists('web_ref_wayback_resolve')) {
						$wb = web_ref_wayback_resolve($url);
						$playback = is_array($wb) ? (string) ($wb['playback_url'] ?? '') : '';
						if ($playback === '' || $playback === $url) {
							continue;
						}
						try {
							$mir = web_ref_ensure_mirrored($playback);
							$url = $playback;
							$usedWayback = true;
						} catch (Throwable $e2) {
							continue;
						}
					} else {
						continue;
					}
				}
				$score = (int) ($mir['stability_score'] ?? 0);
				if ($score < fractal_zip_web_ref_min_stability()) {
					continue;
				}
				$hits = web_ref_find_pieces_in_body((string) ($mir['bytes'] ?? ''), $needles);
				foreach ($hits as $found) {
					$chunk = (string) ($found['needle'] ?? '');
					if ($chunk === '' || !isset($pending[$chunk])) {
						continue;
					}
					$occ = substr_count($corpus, $chunk);
					if ($occ <= 0) {
						unset($pending[$chunk]);
						continue;
					}
					$reg = fractal_zip_web_ref_register_and_tokenize_piece($chunk, (string) ($mir['canonical_url'] ?? $url), array(
						'mirror_rel_path' => (string) ($mir['mirror_rel_path'] ?? ''),
						'last_modified' => (string) ($mir['last_modified'] ?? ''),
						'etag' => (string) ($mir['etag'] ?? ''),
						'piece_offset' => (int) ($found['offset'] ?? 0),
						'piece_length' => (int) ($found['length'] ?? strlen($chunk)),
						'page_sha256' => (string) ($mir['sha256'] ?? hash('sha256', (string) ($mir['bytes'] ?? ''))),
						'occurrences' => $occ,
						'min_gain' => fractal_zip_web_ref_min_gain_bytes(),
					));
					if ($reg === null) {
						continue;
					}
					$out['replacements'][$chunk] = (string) $reg['token'];
					$entriesByToken[(string) $reg['token']] = $reg['entry'];
					$out['saved'] += (int) ($reg['saved'] ?? 0);
					unset($pending[$chunk]);
					$out['stats']['corpus_matches']++;
					if ($usedWayback) {
						$out['stats']['wayback_matches']++;
					}
				}
			}
		}
	}

	foreach (array_keys($pending) as $chunk) {
		$meta = fractal_zip_web_ref_find_enwik_corpus_piece($corpus, $chunk);
		if ($meta === null) {
			continue;
		}
		$occ = substr_count($corpus, $chunk);
		if ($occ <= 0) {
			continue;
		}
		$reg = fractal_zip_web_ref_register_and_tokenize_piece($chunk, (string) ($meta['canonical_url'] ?? ''), array(
			'piece_offset' => (int) ($meta['piece_offset'] ?? 0),
			'piece_length' => (int) ($meta['piece_length'] ?? strlen($chunk)),
			'page_sha256' => (string) ($meta['page_sha256'] ?? ''),
			'occurrences' => $occ,
			'min_gain' => fractal_zip_web_ref_corpus_min_gain_bytes(),
		));
		if ($reg === null) {
			continue;
		}
		$out['replacements'][$chunk] = (string) $reg['token'];
		$entriesByToken[(string) $reg['token']] = $reg['entry'];
		$out['saved'] += (int) ($reg['saved'] ?? 0);
		$out['stats']['corpus_matches']++;
	}

	$out['entries'] = array_values($entriesByToken);
	return $out;
}

/**
 * Apply replacements to enwik virtual members; refresh FZEP v2 sortedPageLens per page slice.
 *
 * @param array<string, string> $replacements
 */
function fractal_zip_web_ref_apply_replacements_to_enwik_members(fractal_zip $fz, array $replacements): int
{
	if ($replacements === array() || !is_array($fz->enwik_zip_ctx ?? null)) {
		return 0;
	}
	$ctx = $fz->enwik_zip_ctx;
	$paths = fractal_zip_web_ref_member_paths_from_ctx($ctx);
	if ($paths === array()) {
		return 0;
	}
	$pagesPerMember = max(1, (int) ($ctx['pagesPerMember'] ?? 1));
	$oldLens = is_array($ctx['sortedPageLens'] ?? null) ? $ctx['sortedPageLens'] : array();
	$rawSaved = 0;
	$newLens = array();
	$pageIdx = 0;
	foreach ($paths as $path) {
		$raw = @file_get_contents($path);
		if (!is_string($raw) || $raw === '') {
			continue;
		}
		if ($pagesPerMember === 1) {
			$new = str_replace(array_keys($replacements), array_values($replacements), $raw, $count);
			if ($count > 0 && $new !== $raw) {
				if (@file_put_contents($path, $new) !== false) {
					$rawSaved += strlen($raw) - strlen($new);
					$newLens[] = strlen($new);
				} else {
					$newLens[] = strlen($raw);
				}
			} else {
				$newLens[] = strlen($raw);
			}
			$pageIdx++;
			continue;
		}
		if ($oldLens === array() || $pageIdx >= count($oldLens)) {
			continue;
		}
		$offset = 0;
		$out = '';
		$pagesInMember = min($pagesPerMember, count($oldLens) - $pageIdx);
		for ($j = 0; $j < $pagesInMember; $j++) {
			$len = (int) $oldLens[$pageIdx];
			if ($len < 0 || $offset + $len > strlen($raw)) {
				$out = '';
				break;
			}
			$pageBytes = substr($raw, $offset, $len);
			$newPage = str_replace(array_keys($replacements), array_values($replacements), $pageBytes);
			$out .= $newPage;
			$newLens[] = strlen($newPage);
			$pageIdx++;
			$offset += $len;
		}
		if ($out === '' || $out === $raw) {
			continue;
		}
		if (@file_put_contents($path, $out) === false) {
			continue;
		}
		$rawSaved += strlen($raw) - strlen($out);
	}
	if ($newLens !== array() && ($oldLens === array() || count($newLens) === count($oldLens))) {
		$fz->enwik_zip_ctx['sortedPageLens'] = $newLens;
	}
	return $rawSaved;
}

function fractal_zip_web_ref_refresh_sorted_page_lens(fractal_zip $fz): void
{
	if (!is_array($fz->enwik_zip_ctx ?? null)) {
		return;
	}
	$ctx = $fz->enwik_zip_ctx;
	$paths = fractal_zip_web_ref_member_paths_from_ctx($ctx);
	if ($paths === array()) {
		return;
	}
	$pagesPerMember = max(1, (int) ($ctx['pagesPerMember'] ?? 1));
	if ($pagesPerMember === 1) {
		$newLens = array();
		foreach ($paths as $path) {
			$raw = @file_get_contents($path);
			$newLens[] = is_string($raw) ? strlen($raw) : 0;
		}
		if ($newLens !== array()) {
			$fz->enwik_zip_ctx['sortedPageLens'] = $newLens;
		}
	}
}

function fractal_zip_web_ref_folder_raw_apply_enabled(): bool
{
	if (!fractal_zip_web_ref_enabled()) {
		return false;
	}
	$e = getenv('FRACTAL_ZIP_WEB_REF_FOLDER_RAW_APPLY');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		return !in_array($v, array('0', 'false', 'no', 'off'), true);
	}
	return true;
}

/**
 * @return list<string>
 */
function fractal_zip_web_ref_folder_text_member_paths(string $dir): array
{
	$extOk = array(
		'.html' => true,
		'.htm' => true,
		'.php' => true,
		'.css' => true,
		'.js' => true,
		'.txt' => true,
	);
	$resolved = realpath($dir);
	if ($resolved === false || !is_dir($resolved)) {
		return array();
	}
	$out = array();
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resolved, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fileInfo) {
		if (!$fileInfo->isFile()) {
			continue;
		}
		$ext = strtolower('.' . pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION));
		if (!isset($extOk[$ext])) {
			continue;
		}
		$out[] = $fileInfo->getPathname();
	}
	sort($out);
	return $out;
}

/**
 * @param list<string> $paths
 */
function fractal_zip_web_ref_folder_corpus_from_paths(array $paths): string
{
	$parts = array();
	foreach ($paths as $path) {
		$raw = @file_get_contents($path);
		if (is_string($raw) && $raw !== '') {
			$parts[] = $raw;
		}
	}
	return implode("\n", $parts);
}

function fractal_zip_web_ref_folder_corpus_from_dir(string $dir): string
{
	return fractal_zip_web_ref_folder_corpus_from_paths(fractal_zip_web_ref_folder_text_member_paths($dir));
}

/**
 * @return array{
 *   entries: list<array<string, mixed>>,
 *   saved: int,
 *   replacements: array<string, string>,
 *   stats: array<string, int>
 * }
 */
function fractal_zip_web_ref_apply_folder_url_literal_replacements(fractal_zip $fz, string $corpus): array
{
	$out = array(
		'entries' => array(),
		'saved' => 0,
		'replacements' => array(),
		'stats' => array(
			'url_literal_matches' => 0,
			'raw_apply_saved' => 0,
		),
	);
	if (!fractal_zip_web_ref_enabled() || !fractal_zip_web_ref_url_literal_enabled() || $corpus === '') {
		return $out;
	}
	if (!function_exists('fractal_zip_web_ref_find_repeated_url_chunks')
		|| !function_exists('fractal_zip_web_ref_try_replace_url_literal')) {
		return $out;
	}
	$rows = fractal_zip_web_ref_find_repeated_url_chunks($corpus);
	if ($rows === array()) {
		return $out;
	}
	$maxEntries = fractal_zip_web_ref_probe_max_chunks();
	$usedCodes = array();
	foreach ($rows as $row) {
		if (count($out['entries']) >= $maxEntries) {
			break;
		}
		$chunk = (string) ($row['chunk'] ?? '');
		$url = fractal_zip_web_ref_normalize_probe_url((string) ($row['url'] ?? ''));
		if ($chunk === '' || $url === '' || isset($out['replacements'][$chunk])) {
			continue;
		}
		$try = fractal_zip_web_ref_try_replace_url_literal($corpus, $chunk, $url);
		if (($try['saved'] ?? 0) <= 0 || empty($try['entries']) || !is_array($try['entries'])) {
			continue;
		}
		$entry = $try['entries'][0];
		$code = (string) ($entry['code'] ?? '');
		if ($code === '' || isset($usedCodes[$code])) {
			continue;
		}
		$token = '@w{' . $code . '}';
		$out['replacements'][$chunk] = $token;
		$out['entries'][] = $entry;
		$out['saved'] += (int) ($try['saved'] ?? 0);
		$usedCodes[$code] = true;
		$out['stats']['url_literal_matches']++;
	}
	return $out;
}

/**
 * @return array{entries: list<array<string, mixed>>, saved: int, stats: array<string, int>}|null
 */
function fractal_zip_web_ref_apply_before_folder_zip(fractal_zip $fz, string $dir): ?array
{
	if (!fractal_zip_web_ref_folder_raw_apply_enabled() || is_array($fz->enwik_zip_ctx ?? null)) {
		return null;
	}
	static $doneDirs = array();
	$resolved = realpath($dir);
	if ($resolved === false || isset($doneDirs[$resolved])) {
		return null;
	}
	$doneDirs[$resolved] = true;
	$paths = fractal_zip_web_ref_folder_text_member_paths($resolved);
	if ($paths === array()) {
		return null;
	}
	$corpus = fractal_zip_web_ref_folder_corpus_from_paths($paths);
	$plan = fractal_zip_web_ref_apply_folder_url_literal_replacements($fz, $corpus);
	if (($plan['replacements'] ?? array()) === array() || ($plan['entries'] ?? array()) === array()) {
		return null;
	}
	$rawSaved = 0;
	foreach ($paths as $path) {
		$rawSaved += fractal_zip_web_ref_apply_to_member_file($path, $plan['replacements']);
	}
	$corpusAfter = fractal_zip_web_ref_folder_corpus_from_paths($paths);
	$entries = array();
	foreach ($plan['entries'] as $entry) {
		$code = (string) ($entry['code'] ?? '');
		if ($code === '') {
			continue;
		}
		if (strpos($corpusAfter, '@w{' . $code . '}') !== false) {
			$entries[] = $entry;
		}
	}
	if ($entries === array() || $rawSaved <= 0) {
		return null;
	}
	$stats = is_array($plan['stats'] ?? null) ? $plan['stats'] : array();
	$stats['raw_apply_saved'] = $rawSaved;
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		@fwrite(STDERR, '[web-ref raw apply] entries=' . count($entries) . ' saved=' . $rawSaved . "B\n");
	}
	return array(
		'entries' => $entries,
		'saved' => $rawSaved,
		'stats' => $stats,
	);
}

/**
 * @return array{entries: list<array<string, mixed>>, saved: int, stats: array<string, int>}|null
 */
function fractal_zip_web_ref_apply_before_recursive_zip(fractal_zip $fz): ?array
{
	if (!fractal_zip_web_ref_enabled() || !is_array($fz->enwik_zip_ctx ?? null)) {
		return null;
	}
	if (!empty($fz->enwik_zip_ctx['web_ref_raw_apply_done'])) {
		return null;
	}
	$ctx = $fz->enwik_zip_ctx;
	$paths = fractal_zip_web_ref_member_paths_from_ctx($ctx);
	if ($paths === array()) {
		return null;
	}
	$corpus = fractal_zip_web_ref_probe_corpus_from_zip($fz);
	if ($corpus === '') {
		return null;
	}
	$plan = fractal_zip_web_ref_apply_corpus_replacements($fz, $corpus);
	if (($plan['replacements'] ?? array()) === array() || ($plan['entries'] ?? array()) === array()) {
		return null;
	}

	$rawSaved = fractal_zip_web_ref_apply_replacements_to_enwik_members($fz, $plan['replacements']);

	$corpusAfter = fractal_zip_web_ref_probe_corpus_from_zip($fz);
	$entries = array();
	foreach ($plan['entries'] as $entry) {
		$code = (string) ($entry['code'] ?? '');
		if ($code === '') {
			continue;
		}
		if (strpos($corpusAfter, '@w{' . $code . '}') !== false) {
			$entries[] = $entry;
		}
	}
	if ($entries === array() || $rawSaved <= 0) {
		return null;
	}
	$stats = is_array($plan['stats'] ?? null) ? $plan['stats'] : array();
	$stats['raw_apply_saved'] = $rawSaved;
	$fz->enwik_zip_ctx['web_ref_raw_apply_saved'] = $rawSaved;
	$fz->enwik_zip_ctx['web_ref_raw_apply_done'] = true;
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		@fwrite(STDERR, '[web-ref raw apply] entries=' . count($entries) . ' saved=' . $rawSaved . "B\n");
	}
	return array(
		'entries' => $entries,
		'saved' => $rawSaved,
		'stats' => $stats,
	);
}
