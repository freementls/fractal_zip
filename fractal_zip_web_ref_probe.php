<?php
declare(strict_types=1);

/**
 * Piece-level web-ref probe: find repeated substrings, match mirrored page slices, replace with @w tokens.
 */

function fractal_zip_web_ref_probe_min_chunk_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_PROBE_MIN_CHUNK');
	if ($e !== false && trim((string) $e) !== '') {
		return max(32, (int) $e);
	}
	return 128;
}

function fractal_zip_web_ref_probe_min_repeats(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_PROBE_MIN_REPEATS');
	if ($e !== false && trim((string) $e) !== '') {
		return max(2, (int) $e);
	}
	return 2;
}

function fractal_zip_web_ref_probe_max_chunks(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_PROBE_MAX_CHUNKS');
	if ($e !== false && trim((string) $e) !== '') {
		return max(1, (int) $e);
	}
	return 80;
}

function fractal_zip_web_ref_probe_max_urls(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_PROBE_MAX_URLS');
	if ($e !== false && trim((string) $e) !== '') {
		return max(1, (int) $e);
	}
	return 40;
}

function fractal_zip_web_ref_probe_wiki_pages(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_PROBE_WIKI_PAGES');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) $e);
	}
	return 40;
}

/**
 * @return array{candidates: int, matches: int, bytes: int}
 */
function fractal_zip_web_ref_probe_whole_page_estimate(string $corpus): array
{
	$out = array('candidates' => 0, 'matches' => 0, 'bytes' => 0);
	if (!function_exists('fractal_zip_web_ref_collect_whole_page_candidates')) {
		return $out;
	}
	$candidates = fractal_zip_web_ref_collect_whole_page_candidates($corpus);
	$out['candidates'] = count($candidates);
	foreach ($candidates as $c) {
		$piece = (string) ($c['piece_bytes'] ?? '');
		if ($piece === '') {
			continue;
		}
		$occ = substr_count($corpus, $piece);
		if ($occ <= 0) {
			continue;
		}
		$out['matches']++;
		$out['bytes'] += $occ * strlen($piece);
	}
	return $out;
}

function fractal_zip_web_ref_wikipedia_raw_url(string $title, ?int $oldid = null): string
{
	$title = str_replace(' ', '_', trim($title));
	$url = 'https://en.wikipedia.org/w/index.php?title=' . rawurlencode($title) . '&action=raw';
	if ($oldid !== null && $oldid > 0) {
		$url .= '&oldid=' . $oldid;
	}
	return $url;
}

function fractal_zip_web_ref_normalize_probe_url(string $url): string
{
	$url = trim($url);
	if ($url === '') {
		return $url;
	}
	if (preg_match('#^https?://([a-z]{2}\.)?wikipedia\.org/wiki/([^?#]+)#i', $url, $m)) {
		$title = rawurldecode(str_replace('_', ' ', $m[2]));
		return fractal_zip_web_ref_wikipedia_raw_url($title);
	}
	if (stripos($url, 'action=raw') !== false) {
		return $url;
	}
	return $url;
}

/**
 * @return list<array{url: string, count: int, score: int, archive: bool, source: string}>
 */
function fractal_zip_web_ref_collect_wikipedia_raw_urls(string $corpusBytes, ?int $maxPages = null): array
{
	$maxPages = $maxPages ?? fractal_zip_web_ref_probe_wiki_pages();
	if ($maxPages <= 0) {
		return array();
	}
	$rows = array();
	$seen = array();
	if (!preg_match_all(
		'/<title>([^<]+)<\/title>\s*<id>\d+<\/id>\s*<revision>\s*<id>(\d+)<\/id>/s',
		$corpusBytes,
		$m,
		PREG_SET_ORDER
	)) {
		return array();
	}
	foreach ($m as $hit) {
		if (count($rows) >= $maxPages) {
			break;
		}
		$title = html_entity_decode((string) $hit[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$oldid = (int) $hit[2];
		if ($title === '' || $oldid <= 0 || isset($seen[$title])) {
			continue;
		}
		$seen[$title] = true;
		$url = fractal_zip_web_ref_wikipedia_raw_url($title, $oldid);
		$rows[] = array(
			'url' => $url,
			'count' => 1,
			'score' => 5000 + strlen($title),
			'archive' => false,
			'source' => 'wikipedia_raw',
		);
	}
	return $rows;
}

/**
 * @return list<array{url: string, count: int, score: int, archive: bool, source: string}>
 */
function fractal_zip_web_ref_collect_all_url_candidates(string $corpusBytes, ?int $maxUrls = null): array
{
	$maxUrls = $maxUrls ?? fractal_zip_web_ref_probe_max_urls();
	$byUrl = array();
	foreach (fractal_zip_web_ref_collect_url_candidates($corpusBytes, $maxUrls) as $row) {
		$url = fractal_zip_web_ref_normalize_probe_url((string) $row['url']);
		if ($url === '') {
			continue;
		}
		$row['url'] = $url;
		$row['source'] = 'literal';
		$byUrl[$url] = $row;
	}
	$wikiBudget = min(fractal_zip_web_ref_probe_wiki_pages(), max(0, $maxUrls - count($byUrl)));
	foreach (fractal_zip_web_ref_collect_wikipedia_raw_urls($corpusBytes, $wikiBudget) as $row) {
		$url = (string) $row['url'];
		if ($url === '' || isset($byUrl[$url])) {
			continue;
		}
		$byUrl[$url] = $row;
	}
	$rows = array_values($byUrl);
	usort($rows, static function (array $a, array $b): int {
		return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
	});
	if (count($rows) > $maxUrls) {
		$rows = array_slice($rows, 0, $maxUrls);
	}
	fractal_zip_web_ref_bootstrap_live_browser();
	if (function_exists('web_ref_wayback_expand_url_rows')) {
		$rows = web_ref_wayback_expand_url_rows($rows);
		if (count($rows) > $maxUrls * 2) {
			$rows = array_slice($rows, 0, $maxUrls * 2);
		}
	}
	return $rows;
}

/**
 * Repeated URL strings in corpus (URL-as-piece candidates).
 *
 * @return list<array{chunk: string, count: int, score: int, length: int, url: string}>
 */
function fractal_zip_web_ref_find_repeated_url_chunks(string $corpusBytes, ?int $minRepeats = null): array
{
	$minRepeats = $minRepeats ?? fractal_zip_web_ref_probe_min_repeats();
	$urlRx = '#https?://[^\s<>"\'\)\]]+#i';
	if (!preg_match_all($urlRx, $corpusBytes, $m)) {
		return array();
	}
	$byUrl = array();
	foreach ($m[0] as $url) {
		$url = rtrim((string) $url, '.,;');
		if (strlen($url) < 16) {
			continue;
		}
		if (!isset($byUrl[$url])) {
			$byUrl[$url] = 0;
		}
		$byUrl[$url]++;
	}
	$rows = array();
	$tokenLen = 11;
	foreach ($byUrl as $url => $count) {
		if ($count < $minRepeats) {
			continue;
		}
		$score = ((int) strlen($url) - $tokenLen) * $count;
		if ($score <= 0) {
			continue;
		}
		$rows[] = array(
			'chunk' => $url,
			'count' => $count,
			'score' => $score + 50000,
			'length' => strlen($url),
			'url' => fractal_zip_web_ref_normalize_probe_url($url),
		);
	}
	usort($rows, static function (array $a, array $b): int {
		return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
	});
	$max = fractal_zip_web_ref_probe_max_chunks() * 2;
	if (count($rows) > $max) {
		$rows = array_slice($rows, 0, $max);
	}
	return $rows;
}

/**
 * Register + replace using an already-mirrored page hit.
 *
 * @return array{fractal_string: string, entries: list<array<string, mixed>>, saved: int}
 */
function fractal_zip_web_ref_apply_piece_hit(string $fractalString, string $chunk, array $probeMeta, array $found): array
{
	$out = array('fractal_string' => $fractalString, 'entries' => array(), 'saved' => 0);
	if (strpos($fractalString, $chunk) === false) {
		return $out;
	}
	fractal_zip_web_ref_bootstrap_sht();
	$pieceBytes = (string) ($found['piece_bytes'] ?? '');
	$sha = (string) ($found['piece_sha256'] ?? hash('sha256', $pieceBytes));
	if ($pieceBytes === '' || hash('sha256', $pieceBytes) !== $sha) {
		return $out;
	}
	$canonical = (string) ($probeMeta['canonical_url'] ?? '');
	if ($canonical === '') {
		return $out;
	}
	if (function_exists('sht_register_piece')) {
		$row = sht_register_piece(array(
			'canonical_url' => $canonical,
			'piece_bytes' => $pieceBytes,
			'piece_sha256' => $sha,
			'mirror_rel_path' => (string) ($probeMeta['mirror_rel_path'] ?? ''),
			'last_modified' => (string) ($probeMeta['last_modified'] ?? ''),
			'etag' => (string) ($probeMeta['etag'] ?? ''),
			'piece_offset' => (int) ($found['offset'] ?? 0),
			'piece_length' => (int) ($found['length'] ?? strlen($pieceBytes)),
			'page_sha256' => (string) ($probeMeta['page_sha256'] ?? hash('sha256', (string) ($probeMeta['bytes'] ?? ''))),
		));
	} else {
		return $out;
	}
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
		'piece_offset' => (int) ($found['offset'] ?? 0),
		'piece_length' => (int) ($found['length'] ?? strlen($pieceBytes)),
	);
	$occurrences = max(1, substr_count($fractalString, $chunk));
	$inlineSaved = $occurrences * (strlen($chunk) - strlen($token));
	$saved = $inlineSaved - fractal_zip_web_ref_trailer_cost_per_entry($entry);
	if ($saved < fractal_zip_web_ref_min_gain_bytes()) {
		return $out;
	}
	$out['fractal_string'] = str_replace($chunk, $token, $fractalString);
	$out['entries'][] = $entry;
	$out['saved'] = $saved;
	return $out;
}

/**
 * @param list<array{chunk: string, count: int, score: int, length: int, url?: string}> $chunks
 * @param list<array{url: string}> $urlRows
 */
function fractal_zip_web_ref_probe_url_first(string $fractalString, array $chunks, array $urlRows): array
{
	$out = array(
		'fractal_string' => $fractalString,
		'entries' => array(),
		'saved' => 0,
		'probes' => 0,
		'matches' => 0,
		'wayback_matches' => 0,
	);
	if ($chunks === array() || $urlRows === array()) {
		return $out;
	}
	fractal_zip_web_ref_bootstrap_live_browser();
	if (!function_exists('web_ref_ensure_mirrored') || !function_exists('web_ref_find_pieces_in_body')) {
		return $out;
	}

	$pending = array();
	foreach ($chunks as $item) {
		$chunk = (string) ($item['chunk'] ?? '');
		if ($chunk === '' || strpos($fractalString, $chunk) === false) {
			continue;
		}
		$pending[$chunk] = $item;
	}
	if ($pending === array()) {
		return $out;
	}

	$urlForChunk = array();
	foreach ($pending as $chunk => $item) {
		if (isset($item['url']) && is_string($item['url']) && $item['url'] !== '') {
			$urlForChunk[$chunk] = (string) $item['url'];
		}
	}

	$maxEntries = fractal_zip_web_ref_probe_max_chunks();
	foreach ($urlRows as $urlRow) {
		if ($pending === array() || count($out['entries']) >= $maxEntries) {
			break;
		}
		$url = fractal_zip_web_ref_normalize_probe_url((string) ($urlRow['url'] ?? ''));
		if ($url === '') {
			continue;
		}
		$needles = array();
		foreach ($pending as $chunk => $item) {
			if (isset($urlForChunk[$chunk])) {
				if ($urlForChunk[$chunk] === $url) {
					$needles[] = $chunk;
				}
				continue;
			}
			$needles[] = $chunk;
		}
		if ($needles === array()) {
			continue;
		}
		$out['probes']++;
		$usedWayback = false;
		try {
			$mir = web_ref_ensure_mirrored($url);
		} catch (Throwable $e) {
			if (function_exists('web_ref_wayback_resolve')) {
				$wb = web_ref_wayback_resolve($url);
				$playback = is_array($wb) ? (string) ($wb['playback_url'] ?? '') : '';
				if ($playback !== '' && $playback !== $url) {
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
			} else {
				continue;
			}
		}
		$score = (int) ($mir['stability_score'] ?? 0);
		if ($score < fractal_zip_web_ref_min_stability()) {
			continue;
		}
		$mir['page_sha256'] = (string) ($mir['sha256'] ?? hash('sha256', (string) ($mir['bytes'] ?? '')));
		$hits = web_ref_find_pieces_in_body((string) ($mir['bytes'] ?? ''), $needles);
		foreach ($hits as $found) {
			$chunk = (string) ($found['needle'] ?? '');
			if ($chunk === '' || !isset($pending[$chunk])) {
				continue;
			}
			$apply = fractal_zip_web_ref_apply_piece_hit($fractalString, $chunk, $mir, $found);
			if (($apply['saved'] ?? 0) <= 0 || ($apply['entries'] ?? array()) === array()) {
				continue;
			}
			$fractalString = (string) $apply['fractal_string'];
			$out['fractal_string'] = $fractalString;
			$out['entries'][] = $apply['entries'][0];
			$out['saved'] += (int) $apply['saved'];
			$out['matches']++;
			if ($usedWayback) {
				$out['wayback_matches']++;
			}
			unset($pending[$chunk]);
			if (count($out['entries']) >= $maxEntries) {
				break 2;
			}
		}
	}
	return $out;
}

/**
 * @return list<array{chunk: string, count: int, score: int, length: int}>
 */
function fractal_zip_web_ref_find_repeated_chunks(string $text, ?int $minLen = null, ?int $minRepeats = null, ?int $maxCandidates = null): array
{
	$minLen = $minLen ?? fractal_zip_web_ref_probe_min_chunk_bytes();
	$minRepeats = $minRepeats ?? fractal_zip_web_ref_probe_min_repeats();
	$maxCandidates = $maxCandidates ?? fractal_zip_web_ref_probe_max_chunks();
	$n = strlen($text);
	if ($n < $minLen * $minRepeats) {
		return array();
	}
	if ($n > 10_000_000 && getenv('FRACTAL_ZIP_WEB_REF_PROBE_FULL_SCAN') !== '1') {
		return array();
	}

	$tokenLen = 11;
	$seen = array();
	$candidates = array();
	$lengths = array();
	for ($win = $minLen; $win <= min(4096, (int) ($n / $minRepeats)); $win *= 2) {
		$lengths[] = $win;
	}
	if ($lengths === array()) {
		return array();
	}
	$large = $n > 5_000_000;

	foreach ($lengths as $win) {
		if ($n < $win * $minRepeats) {
			continue;
		}
		$stride = 1;
		if ($large) {
			$stride = max(1, (int) ($win / 2));
		}
		if ($n > 50_000_000) {
			$stride = max($stride, (int) ($win / 1));
		}
		$map = array();
		$mapLimit = $large ? 8000 : 50000;
		$limit = $n - $win;
		for ($i = 0; $i <= $limit; $i += $stride) {
			$h = md5(substr($text, $i, $win), true);
			if (!isset($map[$h])) {
				if (count($map) >= $mapLimit) {
					continue;
				}
				$map[$h] = $i;
			}
		}
		foreach ($map as $start) {
			$chunk = substr($text, $start, $win);
			$bestCount = substr_count($text, $chunk);
			if ($bestCount < $minRepeats) {
				continue;
			}
			while ($win < min(4096, $n - $start)) {
				$next = substr($text, $start, $win + 1);
				$nextCount = substr_count($text, $next);
				if ($nextCount < $minRepeats || $nextCount < $bestCount) {
					break;
				}
				$win++;
				$chunk = $next;
				$bestCount = $nextCount;
			}
			if (isset($seen[$chunk])) {
				continue;
			}
			$count = substr_count($text, $chunk);
			if ($count < $minRepeats) {
				continue;
			}
			$seen[$chunk] = true;
			$score = ((int) strlen($chunk) - $tokenLen) * $count;
			if ($score <= 0) {
				continue;
			}
			$candidates[] = array(
				'chunk' => $chunk,
				'count' => $count,
				'score' => $score,
				'length' => strlen($chunk),
			);
			if (count($candidates) >= $maxCandidates * 3) {
				break 2;
			}
		}
	}

	usort($candidates, static function (array $a, array $b): int {
		return $b['score'] <=> $a['score'];
	});
	if (count($candidates) > $maxCandidates) {
		$candidates = array_slice($candidates, 0, $maxCandidates);
	}
	return $candidates;
}

function fractal_zip_web_ref_chunk_is_wikitext_safe(string $chunk): bool
{
	if ($chunk === '' || strpos($chunk, "\0") !== false) {
		return false;
	}
	return !preg_match('/<\/?(?:page|revision|text|mediawiki|title|id|timestamp|contributor|comment|minor)\b/i', $chunk);
}

/** True for full enwik8 dump or sorted virtual member slices (page XML without wrapper). */
function fractal_zip_web_ref_is_enwik_corpus(string $corpus): bool
{
	if ($corpus === '') {
		return false;
	}
	if (stripos($corpus, '<mediawiki') !== false) {
		return true;
	}
	return stripos($corpus, '<page') !== false && stripos($corpus, '<revision') !== false;
}

/**
 * Locate a wikitext piece inside enwik dump XML and return oldid raw URL metadata.
 *
 * @return array{title: string, oldid: int, canonical_url: string, piece_bytes: string, piece_offset: int, piece_length: int, page_sha256: string}|null
 */
function fractal_zip_web_ref_find_enwik_corpus_piece(string $corpus, string $chunk): ?array
{
	if (!fractal_zip_web_ref_corpus_pieces_enabled() || $chunk === '' || !fractal_zip_web_ref_is_enwik_corpus($corpus)) {
		return null;
	}
	if (!fractal_zip_web_ref_chunk_is_wikitext_safe($chunk)) {
		return null;
	}
	$start = 0;
	$tries = 0;
	while ($tries < 24 && ($pos = strpos($corpus, $chunk, $start)) !== false) {
		$tries++;
		$textOpen = strrpos(substr($corpus, 0, $pos), '<text');
		if ($textOpen === false) {
			$start = $pos + 1;
			continue;
		}
		$textBodyStart = strpos($corpus, '>', $textOpen);
		if ($textBodyStart === false) {
			$start = $pos + 1;
			continue;
		}
		$textBodyStart++;
		$textClose = strpos($corpus, '</text>', $pos);
		if ($textClose === false || $pos + strlen($chunk) > $textClose) {
			$start = $pos + 1;
			continue;
		}
		$pageStart = strrpos(substr($corpus, 0, $textOpen), '<page');
		if ($pageStart === false) {
			$start = $pos + 1;
			continue;
		}
		$pageEnd = strpos($corpus, '</page>', $textClose);
		if ($pageEnd === false) {
			$start = $pos + 1;
			continue;
		}
		$pageEnd += 7;
		$pageXml = substr($corpus, $pageStart, $pageEnd - $pageStart);
		if (!preg_match('/<title>([^<]+)<\/title>/', $pageXml, $tm)
			|| !preg_match('/<revision>\s*<id>(\d+)<\/id>/s', $pageXml, $rm)) {
			$start = $pos + 1;
			continue;
		}
		$textBody = substr($corpus, $textBodyStart, $textClose - $textBodyStart);
		$offsetInText = $pos - $textBodyStart;
		if ($offsetInText < 0 || substr($textBody, $offsetInText, strlen($chunk)) !== $chunk) {
			$start = $pos + 1;
			continue;
		}
		$title = html_entity_decode($tm[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$oldid = (int) $rm[1];
		return array(
			'title' => $title,
			'oldid' => $oldid,
			'canonical_url' => fractal_zip_web_ref_wikipedia_raw_url($title, $oldid),
			'piece_bytes' => $chunk,
			'piece_offset' => (int) $offsetInText,
			'piece_length' => strlen($chunk),
			'page_sha256' => hash('sha256', $textBody),
		);
	}
	return null;
}

/**
 * Register corpus-located piece (enwik dump bytes + oldid raw URL; no live page match required).
 *
 * @return array{fractal_string: string, entries: list<array<string, mixed>>, saved: int}
 */
function fractal_zip_web_ref_try_replace_corpus_piece(string $fractalString, string $chunk, string $corpus): array
{
	$out = array('fractal_string' => $fractalString, 'entries' => array(), 'saved' => 0);
	if (strpos($fractalString, $chunk) === false) {
		return $out;
	}
	$meta = fractal_zip_web_ref_find_enwik_corpus_piece($corpus, $chunk);
	if ($meta === null) {
		return $out;
	}
	fractal_zip_web_ref_bootstrap_sht();
	if (!function_exists('sht_register_piece')) {
		return $out;
	}
	$pieceBytes = (string) ($meta['piece_bytes'] ?? '');
	$sha = hash('sha256', $pieceBytes);
	if ($sha === '' || hash('sha256', $pieceBytes) !== $sha) {
		return $out;
	}
	$row = sht_register_piece(array(
		'canonical_url' => (string) $meta['canonical_url'],
		'piece_bytes' => $pieceBytes,
		'piece_sha256' => $sha,
		'mirror_rel_path' => '',
		'last_modified' => '',
		'etag' => '',
		'piece_offset' => (int) ($meta['piece_offset'] ?? 0),
		'piece_length' => (int) ($meta['piece_length'] ?? strlen($pieceBytes)),
		'page_sha256' => (string) ($meta['page_sha256'] ?? ''),
	));
	$code = (string) ($row['code'] ?? '');
	if ($code === '' || !function_exists('sht_alphabet_is_valid_code') || !sht_alphabet_is_valid_code($code)) {
		return $out;
	}
	$token = '@w{' . $code . '}';
	$entry = array(
		'code' => $code,
		'sha256' => $sha,
		'canonical_url' => (string) ($row['canonical_url'] ?? $meta['canonical_url']),
		'last_modified' => '',
		'etag' => '',
		'piece_offset' => 0,
		'piece_length' => strlen($pieceBytes),
	);
	$occurrences = max(1, substr_count($fractalString, $chunk));
	$inlineSaved = $occurrences * (strlen($chunk) - strlen($token));
	$saved = $inlineSaved - fractal_zip_web_ref_trailer_cost_per_entry($entry);
	if ($saved < fractal_zip_web_ref_corpus_min_gain_bytes()) {
		return $out;
	}
	$out['fractal_string'] = str_replace($chunk, $token, $fractalString);
	$out['entries'][] = $entry;
	$out['saved'] = $saved;
	return $out;
}

/**
 * @return list<array{url: string, count: int, score: int, archive: bool}>
 */
function fractal_zip_web_ref_collect_url_candidates(string $corpusBytes, ?int $maxUrls = null): array
{
	$maxUrls = $maxUrls ?? fractal_zip_web_ref_probe_max_urls();
	$urlRx = '#https?://[^\s<>"\'\)\]]+#i';
	if (!preg_match_all($urlRx, $corpusBytes, $m)) {
		return array();
	}
	$byUrl = array();
	foreach ($m[0] as $url) {
		$url = rtrim((string) $url, '.,;');
		if (function_exists('web_ref_wayback_normalize_playback_url') && stripos($url, 'web.archive.org') !== false) {
			$url = web_ref_wayback_normalize_playback_url($url);
		}
		if (strlen($url) < 12) {
			continue;
		}
		if (!isset($byUrl[$url])) {
			$byUrl[$url] = 0;
		}
		$byUrl[$url]++;
	}
	$rows = array();
	foreach ($byUrl as $url => $count) {
		$archive = (stripos($url, 'archive.org') !== false);
		$rows[] = array(
			'url' => $url,
			'count' => $count,
			'score' => strlen($url) * $count + ($archive ? 10000 : 0),
			'archive' => $archive,
		);
	}
	usort($rows, static function (array $a, array $b): int {
		return $b['score'] <=> $a['score'];
	});
	if (count($rows) > $maxUrls) {
		$rows = array_slice($rows, 0, $maxUrls);
	}
	return $rows;
}

/**
 * Fast enwik8 wikitext/template candidates without scanning 100MB windows.
 *
 * @return list<array{chunk: string, count: int, score: int, length: int}>
 */
function fractal_zip_web_ref_find_enwik_obvious_chunks(string $corpus, ?int $maxCandidates = null): array
{
	if (!fractal_zip_web_ref_is_enwik_corpus($corpus)) {
		return array();
	}
	$maxCandidates = $maxCandidates ?? fractal_zip_web_ref_probe_max_chunks();
	$tokenLen = 11;
	$seeds = array(
		'[[Category:',
		'#REDIRECT [[',
		'List_of_colleges_and_universities_starting_with_',
		'&lt;TD&gt;&lt;FONT SIZE=&quot;1&quot;&gt;(NA)&lt;/TD&gt;',
		'{{Infobox',
		'[[Wikipedia:',
		'{{cite web',
		'|[[Category:',
		'{{DEFAULTSORT:',
		'[[File:',
		'{{Commonscat',
		'{{ISBN',
		'{{Sister project links',
		'{{Navbox',
		'{{Portal',
		'{{See also',
		'{{Main article',
		'{{reflist',
		'{{coord|',
		'{{GeoGroup',
	);
	$candidates = array();
	foreach ($seeds as $seed) {
		$pos = 0;
		$hits = 0;
		while (($pos = strpos($corpus, $seed, $pos)) !== false && $hits < 8) {
			$hits++;
			$maxWin = (int) (getenv('FRACTAL_ZIP_WEB_REF_PROBE_MAX_CHUNK_WIN') ?: 1536);
			$maxWin = max(512, min(4096, $maxWin));
			$win = min($maxWin, max(128, strlen($seed) + 64));
			if ($pos + $win > strlen($corpus)) {
				$pos += max(1, strlen($seed));
				continue;
			}
			$chunk = substr($corpus, $pos, $win);
			if (!fractal_zip_web_ref_chunk_is_wikitext_safe($chunk)) {
				$pos += max(1, strlen($seed));
				continue;
			}
			$bestCount = substr_count($corpus, $chunk);
			while ($win < $maxWin && $pos + $win < strlen($corpus)) {
				$next = substr($corpus, $pos, $win + 1);
				if (!fractal_zip_web_ref_chunk_is_wikitext_safe($next)) {
					break;
				}
				$nextCount = substr_count($corpus, $next);
				if ($nextCount < 2 || $nextCount < $bestCount) {
					break;
				}
				$win++;
				$chunk = $next;
				$bestCount = $nextCount;
			}
			$count = substr_count($corpus, $chunk);
			if ($count >= fractal_zip_web_ref_probe_min_repeats() && strlen($chunk) >= fractal_zip_web_ref_probe_min_chunk_bytes()) {
				$score = ((int) strlen($chunk) - $tokenLen) * $count;
				if ($score > 0) {
					$candidates[$chunk] = array(
						'chunk' => $chunk,
						'count' => $count,
						'score' => $score,
						'length' => strlen($chunk),
					);
				}
			}
			$pos += max(1, strlen($seed));
		}
	}
	$rows = array_values($candidates);
	usort($rows, static function (array $a, array $b): int {
		return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
	});
	if (count($rows) > $maxCandidates) {
		$rows = array_slice($rows, 0, $maxCandidates);
	}
	return $rows;
}

function fractal_zip_web_ref_probe_corpus_reserve(int $maxEntries): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_PROBE_CORPUS_RESERVE');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, min($maxEntries, (int) $e));
	}
	return max(8, (int) ($maxEntries / 2));
}

/**
 * @param array<string, mixed> $out
 * @param array<string, mixed> $try
 * @param array<string, bool> $usedCodes
 */
function fractal_zip_web_ref_probe_merge_try(array &$out, string &$fractalString, array $try, array &$usedCodes): bool
{
	if (($try['saved'] ?? 0) <= 0 || ($try['entries'] ?? array()) === array()) {
		return false;
	}
	$entry = $try['entries'][0];
	$code = (string) ($entry['code'] ?? '');
	if ($code === '' || isset($usedCodes[$code])) {
		return false;
	}
	$fractalString = (string) $try['fractal_string'];
	$out['fractal_string'] = $fractalString;
	$out['entries'][] = $entry;
	$out['saved'] += (int) $try['saved'];
	$out['matches']++;
	$usedCodes[$code] = true;
	return true;
}

/**
 * Probe repeated chunks against URL mirrors and compute savings on fractal_string.
 *
 * @param list<array{url: string}>|null $urlRows
 * @return array{fractal_string: string, entries: list<array<string, mixed>>, saved: int, probes: int, matches: int, chunks: int}
 */
function fractal_zip_web_ref_probe_fractal_string(string $fractalString, string $corpusBytes = '', ?array $urlRows = null): array
{
	$out = array(
		'fractal_string' => $fractalString,
		'entries' => array(),
		'saved' => 0,
		'probes' => 0,
		'matches' => 0,
		'whole_page_matches' => 0,
		'wayback_matches' => 0,
		'raw_apply_saved' => 0,
		'corpus_matches' => 0,
		'url_literal_matches' => 0,
		'search_matches' => 0,
		'chunks' => 0,
	);
	if (!fractal_zip_web_ref_enabled() || $fractalString === '') {
		return $out;
	}
	fractal_zip_web_ref_bootstrap_live_browser();
	fractal_zip_web_ref_bootstrap_sht();
	if (!function_exists('fractal_zip_web_ref_try_replace_chunk')) {
		return $out;
	}

	$corpus = $corpusBytes !== '' ? $corpusBytes : $fractalString;
	$enwikCorpus = fractal_zip_web_ref_is_enwik_corpus($corpus);
	$corpusSeedChunks = $enwikCorpus ? fractal_zip_web_ref_find_enwik_obvious_chunks($corpus) : array();
	$urlChunks = fractal_zip_web_ref_find_repeated_url_chunks($corpus);
	$chunks = fractal_zip_web_ref_find_repeated_chunks($fractalString);
	if ($chunks === array() && $corpusSeedChunks !== array()) {
		$chunks = $corpusSeedChunks;
	}
	$seenChunk = array();
	$merged = array();
	foreach (array_merge($corpusSeedChunks, $urlChunks, $chunks) as $item) {
		$chunk = (string) ($item['chunk'] ?? '');
		if ($chunk === '' || isset($seenChunk[$chunk])) {
			continue;
		}
		$seenChunk[$chunk] = true;
		$merged[] = $item;
	}
	usort($merged, static function (array $a, array $b): int {
		return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
	});
	if (count($merged) > fractal_zip_web_ref_probe_max_chunks() * 2) {
		$merged = array_slice($merged, 0, fractal_zip_web_ref_probe_max_chunks() * 2);
	}
	$out['chunks'] = count($merged);
	if ($merged === array()) {
		return $out;
	}
	if ($urlRows === null) {
		$urlRows = fractal_zip_web_ref_collect_all_url_candidates($corpus);
	}
	if ($urlRows === array()) {
		return $out;
	}

	$usedCodes = array();
	$maxEntries = fractal_zip_web_ref_probe_max_chunks();
	$corpusReserve = fractal_zip_web_ref_probe_corpus_reserve($maxEntries);

	$urlLiteralItems = array();
	$genericChunks = array();
	foreach ($merged as $item) {
		if (isset($item['url'])) {
			$urlLiteralItems[] = $item;
		} else {
			$genericChunks[] = $item;
		}
	}

	if ($enwikCorpus && function_exists('fractal_zip_web_ref_try_replace_corpus_piece')) {
		$corpusPassItems = array_merge($corpusSeedChunks, $genericChunks);
		$corpusSeen = array();
		$matchedChunks = array();
		foreach ($corpusPassItems as $item) {
			if (count($out['entries']) >= $corpusReserve) {
				break;
			}
			$chunk = (string) ($item['chunk'] ?? '');
			if ($chunk === '' || isset($corpusSeen[$chunk]) || strpos($fractalString, $chunk) === false) {
				continue;
			}
			$corpusSeen[$chunk] = true;
			$out['probes']++;
			$try = fractal_zip_web_ref_try_replace_corpus_piece($fractalString, $chunk, $corpus);
			if (fractal_zip_web_ref_probe_merge_try($out, $fractalString, $try, $usedCodes)) {
				$out['corpus_matches']++;
				$matchedChunks[$chunk] = true;
			}
		}
		if ($matchedChunks !== array()) {
			$genericChunks = array_values(array_filter($genericChunks, static function (array $item) use ($matchedChunks): bool {
				return !isset($matchedChunks[(string) ($item['chunk'] ?? '')]);
			}));
		}
	}

	foreach ($urlLiteralItems as $item) {
		if (count($out['entries']) >= $maxEntries) {
			break;
		}
		$chunk = (string) ($item['chunk'] ?? '');
		if (strpos($fractalString, $chunk) === false) {
			continue;
		}
		$url = fractal_zip_web_ref_normalize_probe_url((string) ($item['url'] ?? ''));
		$out['probes']++;
		$try = fractal_zip_web_ref_try_replace_url_literal($fractalString, $chunk, $url);
		if (($try['saved'] ?? 0) <= 0 && getenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR') !== '1') {
			$try = fractal_zip_web_ref_try_replace_chunk($fractalString, $chunk, $url);
		}
		if (fractal_zip_web_ref_probe_merge_try($out, $fractalString, $try, $usedCodes)) {
			$out['url_literal_matches']++;
		}
	}

	if ($genericChunks !== array() && count($out['entries']) < $maxEntries && getenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR') !== '1') {
		$urlFirst = fractal_zip_web_ref_probe_url_first($fractalString, $genericChunks, $urlRows);
		$fractalString = (string) $urlFirst['fractal_string'];
		$out['fractal_string'] = $fractalString;
		$matchedByUrlFirst = array();
		foreach ($urlFirst['entries'] as $entry) {
			$code = (string) ($entry['code'] ?? '');
			if ($code === '' || isset($usedCodes[$code])) {
				continue;
			}
			$out['entries'][] = $entry;
			$out['matches']++;
			$usedCodes[$code] = true;
		}
		$out['probes'] += (int) $urlFirst['probes'];
		$out['saved'] += (int) $urlFirst['saved'];
		$out['wayback_matches'] += (int) ($urlFirst['wayback_matches'] ?? 0);
		if (($urlFirst['matches'] ?? 0) > 0) {
			foreach ($genericChunks as $gc) {
				$c = (string) ($gc['chunk'] ?? '');
				if ($c !== '' && strpos($fractalString, $c) === false) {
					$matchedByUrlFirst[$c] = true;
				}
			}
			if ($matchedByUrlFirst !== array()) {
				$genericChunks = array_values(array_filter($genericChunks, static function (array $item) use ($matchedByUrlFirst): bool {
					return !isset($matchedByUrlFirst[(string) ($item['chunk'] ?? '')]);
				}));
			}
		}
	}

	if ($genericChunks !== array() && count($out['entries']) < $maxEntries && getenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR') !== '1') {
		require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fractal_zip_web_ref_search.php';
		$searchState = array('searches' => 0, 'last_search_at' => 0.0, 'cooldown_mult' => 1.0);
		$searchPass = fractal_zip_web_ref_probe_search_pass($fractalString, $genericChunks, $usedCodes, $searchState);
		$fractalString = (string) $searchPass['fractal_string'];
		$out['fractal_string'] = $fractalString;
		foreach ($searchPass['entries'] as $entry) {
			$code = (string) ($entry['code'] ?? '');
			if ($code === '' || isset($usedCodes[$code])) {
				continue;
			}
			$out['entries'][] = $entry;
			$out['matches']++;
			$usedCodes[$code] = true;
		}
		$out['probes'] += (int) $searchPass['probes'];
		$out['saved'] += (int) $searchPass['saved'];
		$out['search_matches'] += (int) ($searchPass['search_matches'] ?? 0);
		$out['wayback_matches'] += (int) ($searchPass['wayback_matches'] ?? 0);
		if (($searchPass['search_matches'] ?? 0) > 0) {
			$matchedBySearch = array();
			foreach ($genericChunks as $gc) {
				$c = (string) ($gc['chunk'] ?? '');
				if ($c !== '' && strpos($fractalString, $c) === false) {
					$matchedBySearch[$c] = true;
				}
			}
			if ($matchedBySearch !== array()) {
				$genericChunks = array_values(array_filter($genericChunks, static function (array $item) use ($matchedBySearch): bool {
					return !isset($matchedBySearch[(string) ($item['chunk'] ?? '')]);
				}));
			}
		}
	}

	foreach ($genericChunks as $item) {
		if (count($out['entries']) >= $maxEntries) {
			break;
		}
		if (getenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR') === '1') {
			break;
		}
		$chunk = (string) ($item['chunk'] ?? '');
		if (strpos($fractalString, $chunk) === false) {
			continue;
		}
		foreach ($urlRows as $urlRow) {
			$url = fractal_zip_web_ref_normalize_probe_url((string) ($urlRow['url'] ?? ''));
			if ($url === '') {
				continue;
			}
			$out['probes']++;
			$try = fractal_zip_web_ref_try_replace_chunk($fractalString, $chunk, $url);
			if (!fractal_zip_web_ref_probe_merge_try($out, $fractalString, $try, $usedCodes)) {
				continue;
			}
			break;
		}
	}
	return $out;
}

/**
 * Collect member corpus bytes from a fractal_zip instance during folder encode.
 */
function fractal_zip_web_ref_probe_corpus_from_zip(fractal_zip $fz): string
{
	if (is_array($fz->enwik_zip_ctx ?? null)) {
		$ctx = $fz->enwik_zip_ctx;
		$virtualDir = (string) ($ctx['virtualDir'] ?? '');
		$header = (string) ($ctx['header'] ?? '');
		$footer = (string) ($ctx['footer'] ?? '');
		$parts = array();
		if ($header !== '') {
			$parts[] = $header;
		}
		foreach ($ctx['memberRelPaths'] ?? array() as $rel) {
			$path = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $rel);
			$raw = @file_get_contents($path);
			if (is_string($raw) && $raw !== '') {
				$parts[] = $raw;
			}
		}
		if ($footer !== '') {
			$parts[] = $footer;
		}
		$full = implode('', $parts);
		if ($full !== '') {
			return $full;
		}
	}
	$parts = array();
	if (is_array($fz->equivalences ?? null)) {
		foreach ($fz->equivalences as $equivalence) {
			if (isset($equivalence[0]) && is_string($equivalence[0]) && $equivalence[0] !== '') {
				$parts[] = $equivalence[0];
			}
		}
	}
	if ($parts === array() && is_string($fz->zip_folder_root_for_members ?? null) && $fz->zip_folder_root_for_members !== '') {
		$root = $fz->zip_folder_root_for_members;
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
		foreach ($it as $fileInfo) {
			if (!$fileInfo->isFile()) {
				continue;
			}
			$raw = @file_get_contents($fileInfo->getPathname());
			if (is_string($raw) && $raw !== '') {
				$parts[] = $raw;
			}
		}
	}
	return implode("\n", $parts);
}

/**
 * Hook: probe fractal_string (and lazy) before container encode in zip_folder.
 *
 * @return array{fractal_string?: string, lazy_fractal_string?: string, entries: list<array<string, mixed>>, saved: int}|null
 */
function fractal_zip_web_ref_probe_apply_before_encode(fractal_zip $fz): ?array
{
	if (!fractal_zip_web_ref_enabled()) {
		return null;
	}
	$corpus = fractal_zip_web_ref_probe_corpus_from_zip($fz);
	$entries = array();
	$saved = 0;
	$stats = array(
		'whole_page_matches' => 0,
		'wayback_matches' => 0,
		'raw_apply_saved' => (int) ($fz->enwik_zip_ctx['web_ref_raw_apply_saved'] ?? 0),
	);
	$fractalString = (string) ($fz->fractal_string ?? '');
	if ($fractalString !== '') {
		$probe = fractal_zip_web_ref_probe_fractal_string($fractalString, $corpus);
		$fractalString = (string) $probe['fractal_string'];
		$entries = array_merge($entries, $probe['entries']);
		$saved += (int) $probe['saved'];
		$stats['whole_page_matches'] += (int) ($probe['whole_page_matches'] ?? 0);
		$stats['wayback_matches'] += (int) ($probe['wayback_matches'] ?? 0);
		$stats['raw_apply_saved'] += (int) ($probe['raw_apply_saved'] ?? 0);
	}
	$lazyString = (string) ($fz->lazy_fractal_string ?? '');
	$lazyOut = null;
	if ($lazyString !== '' && $lazyString !== $fractalString) {
		$lazyProbe = fractal_zip_web_ref_probe_fractal_string($lazyString, $corpus);
		$lazyString = (string) $lazyProbe['fractal_string'];
		$entries = array_merge($entries, $lazyProbe['entries']);
		$saved += (int) $lazyProbe['saved'];
		$stats['whole_page_matches'] += (int) ($lazyProbe['whole_page_matches'] ?? 0);
		$stats['wayback_matches'] += (int) ($lazyProbe['wayback_matches'] ?? 0);
		$stats['raw_apply_saved'] += (int) ($lazyProbe['raw_apply_saved'] ?? 0);
		$lazyOut = $lazyString;
	}
	if ($entries === array()) {
		return null;
	}
	$ret = array(
		'entries' => $entries,
		'saved' => $saved,
		'fractal_string' => $fractalString,
		'stats' => $stats,
	);
	if ($lazyOut !== null) {
		$ret['lazy_fractal_string'] = $lazyOut;
	}
	return $ret;
}
