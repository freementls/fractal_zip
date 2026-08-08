<?php
declare(strict_types=1);

/**
 * Conservative search-engine piece discovery for web-ref probe (default OFF).
 * DuckDuckGo HTML only unless explicitly configured; bot-safe rate limits + disk cache.
 */

function fractal_zip_web_ref_search_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_WEB_REF_SEARCH');
	return $v !== false && trim((string) $v) !== '' && trim((string) $v) !== '0';
}

function fractal_zip_web_ref_search_min_query_len(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_SEARCH_MIN_QUERY');
	if ($e !== false && trim((string) $e) !== '') {
		return max(32, (int) $e);
	}
	return 64;
}

function fractal_zip_web_ref_search_max_per_run(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_SEARCH_MAX');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) $e);
	}
	return 4;
}

function fractal_zip_web_ref_search_result_limit(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_SEARCH_RESULT_LIMIT');
	if ($e !== false && trim((string) $e) !== '') {
		return max(1, min(5, (int) $e));
	}
	return 2;
}

function fractal_zip_web_ref_search_min_delay_us(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_SEARCH_MIN_DELAY_SEC');
	$sec = 10.0;
	if ($e !== false && trim((string) $e) !== '') {
		$sec = max(1.0, (float) $e);
	}
	return (int) round($sec * 1_000_000);
}

function fractal_zip_web_ref_search_cache_ttl_sec(): int
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_SEARCH_CACHE_TTL_DAYS');
	$days = 7;
	if ($e !== false && trim((string) $e) !== '') {
		$days = max(1, (int) $e);
	}
	return $days * 86400;
}

function fractal_zip_web_ref_search_provider(): string
{
	$v = getenv('FRACTAL_ZIP_WEB_REF_SEARCH_PROVIDER');
	if ($v !== false && trim((string) $v) !== '') {
		return strtolower(trim((string) $v));
	}
	return 'duckduckgo';
}

function fractal_zip_web_ref_search_allow_fallback(): bool
{
	$v = getenv('FRACTAL_ZIP_WEB_REF_SEARCH_ALLOW_FALLBACK');
	return $v !== false && trim((string) $v) !== '' && trim((string) $v) !== '0';
}

function fractal_zip_web_ref_search_user_agent(): string
{
	$v = getenv('FRACTAL_ZIP_WEB_REF_SEARCH_USER_AGENT');
	if ($v !== false && trim((string) $v) !== '') {
		return trim((string) $v);
	}
	return 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36';
}

function fractal_zip_web_ref_search_cache_dir(): string
{
	$e = getenv('FRACTAL_ZIP_WEB_REF_SEARCH_CACHE_DIR');
	if ($e !== false && trim((string) $e) !== '') {
		$dir = rtrim(trim((string) $e), '/\\');
	} else {
		$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.web_ref_search_cache';
	}
	if (!is_dir($dir)) {
		@mkdir($dir, 0755, true);
	}
	return $dir;
}

/** @return list<string> */
function fractal_zip_web_ref_search_common_words(): array
{
	return array(
		'the', 'and', 'for', 'that', 'with', 'this', 'from', 'have', 'were', 'which',
		'their', 'would', 'there', 'could', 'other', 'into', 'more', 'some', 'such',
		'only', 'also', 'than', 'then', 'when', 'what', 'your', 'about', 'after',
		'before', 'between', 'been', 'being', 'will', 'shall', 'should', 'these',
		'those', 'them', 'they', 'were', 'where', 'while', 'whose', 'upon', 'under',
	);
}

function fractal_zip_web_ref_search_chunk_eligible(array $item): bool
{
	$chunk = (string) ($item['chunk'] ?? '');
	$count = (int) ($item['count'] ?? 0);
	$len = strlen($chunk);
	if ($len < 128 || $count < 3) {
		return false;
	}
	if (preg_match('#^https?://#i', $chunk)) {
		return false;
	}
	return true;
}

function fractal_zip_web_ref_search_query_distinctive(string $phrase): bool
{
	if (preg_match('/\d/', $phrase)) {
		return true;
	}
	if (preg_match('/[^\w\s]/', $phrase)) {
		return true;
	}
	foreach (preg_split('/\s+/', $phrase, -1, PREG_SPLIT_NO_EMPTY) as $tok) {
		if (strlen($tok) >= 10) {
			return true;
		}
	}
	$lower = strtolower($phrase);
	$common = fractal_zip_web_ref_search_common_words();
	$words = preg_split('/\s+/', $lower, -1, PREG_SPLIT_NO_EMPTY);
	if (!is_array($words) || $words === array()) {
		return false;
	}
	$commonHits = 0;
	foreach ($words as $w) {
		if (in_array($w, $common, true)) {
			$commonHits++;
		}
	}
	if ($commonHits >= (int) ceil(count($words) * 0.55)) {
		return false;
	}
	return count($words) >= 4;
}

/**
 * Extract a quoted exact-phrase search query from a repeated chunk (middle-biased).
 */
function fractal_zip_web_ref_search_extract_query(string $chunk, int $repeatCount = 1): ?string
{
	unset($repeatCount);
	$chunk = trim($chunk);
	if ($chunk === '') {
		return null;
	}
	$minLen = fractal_zip_web_ref_search_min_query_len();
	$len = strlen($chunk);
	if ($len < $minLen) {
		return null;
	}
	$genericPrefixes = array('{{', '[[', '|', '<', '&lt;', '#REDIRECT', '{{cite', '{{Infobox');
	foreach ($genericPrefixes as $pfx) {
		if (str_starts_with($chunk, $pfx)) {
			return null;
		}
	}
	$targetLen = min(160, max($minLen, (int) ($len * 0.45)));
	$start = (int) max(0, ($len - $targetLen) / 2);
	$phrase = substr($chunk, $start, $targetLen);
	$phrase = trim($phrase);
	if (strlen($phrase) < $minLen) {
		$phrase = substr($chunk, 0, min($len, $minLen + 32));
		$phrase = trim($phrase);
	}
	if (strlen($phrase) < $minLen || !fractal_zip_web_ref_search_query_distinctive($phrase)) {
		return null;
	}
	return '"' . str_replace('"', '', $phrase) . '"';
}

function fractal_zip_web_ref_search_is_captcha_html(string $html): bool
{
	if ($html === '') {
		return false;
	}
	$markers = array(
		'captcha', 'challenge-form', 'unusual traffic', 'detected unusual',
		'verify you are human', 'bot detection', 'anubis', 'cf-challenge',
	);
	$lower = strtolower($html);
	foreach ($markers as $m) {
		if (strpos($lower, $m) !== false) {
			return true;
		}
	}
	return false;
}

/** @return list<array{url: string, title: string, score: float, source: string}>|null */
function fractal_zip_web_ref_search_cache_get(string $query): ?array
{
	$key = hash('sha256', $query);
	$path = fractal_zip_web_ref_search_cache_dir() . DIRECTORY_SEPARATOR . $key . '.json';
	if (!is_readable($path)) {
		return null;
	}
	$raw = file_get_contents($path);
	if (!is_string($raw)) {
		return null;
	}
	$j = json_decode($raw, true);
	if (!is_array($j) || !isset($j['saved_at'], $j['results'])) {
		return null;
	}
	if (time() - (int) $j['saved_at'] > fractal_zip_web_ref_search_cache_ttl_sec()) {
		return null;
	}
	return is_array($j['results']) ? $j['results'] : array();
}

/** @param list<array{url: string, title: string, score: float, source: string}> $results */
function fractal_zip_web_ref_search_cache_put(string $query, array $results): void
{
	$key = hash('sha256', $query);
	$path = fractal_zip_web_ref_search_cache_dir() . DIRECTORY_SEPARATOR . $key . '.json';
	@file_put_contents($path, json_encode(array(
		'query' => $query,
		'saved_at' => time(),
		'results' => $results,
	), JSON_UNESCAPED_SLASHES));
}

function fractal_zip_web_ref_search_http_get(string $url): string
{
	if (!empty($GLOBALS['FRACTAL_ZIP_WEB_REF_SEARCH_FIXTURE_HTML'])) {
		return (string) $GLOBALS['FRACTAL_ZIP_WEB_REF_SEARCH_FIXTURE_HTML'];
	}
	$fixture = getenv('FRACTAL_ZIP_WEB_REF_SEARCH_FIXTURE');
	if ($fixture !== false && trim((string) $fixture) !== '' && is_readable((string) $fixture)) {
		$html = file_get_contents((string) $fixture);
		return is_string($html) ? $html : '';
	}
	$html = @file_get_contents($url, false, stream_context_create(array(
		'http' => array(
			'timeout' => 15,
			'header' => 'User-Agent: ' . fractal_zip_web_ref_search_user_agent() . "\r\n"
				. "Accept: text/html,application/xhtml+xml\r\n"
				. "Accept-Language: en-US,en;q=0.9\r\n",
		),
	)));
	return is_string($html) ? $html : '';
}

/** @return list<array{url: string, title: string, score: float, source: string}> */
function fractal_zip_web_ref_search_parse_ddg(string $html, int $limit): array
{
	$found = array();
	if (!preg_match_all('#<a[^>]+class="[^"]*result__a[^"]*"[^>]+href="([^"]+)"#i', $html, $m)) {
		return array();
	}
	foreach ($m[1] as $href) {
		$href = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$target = $href;
		if (preg_match('#uddg=([^&]+)#', $href, $u)) {
			$target = rawurldecode($u[1]);
		}
		if ($target === '' || strpos($target, '://') === false) {
			continue;
		}
		$found[] = array(
			'url' => $target,
			'title' => '',
			'score' => 0.5,
			'source' => 'duckduckgo',
		);
		if (count($found) >= $limit) {
			break;
		}
	}
	return $found;
}

/**
 * @param array{searches?: int, last_search_at?: float, cooldown_mult?: float} $state
 * @return array{results: list<array{url: string, title: string, score: float, source: string}>, error: bool, captcha: bool}
 */
function fractal_zip_web_ref_search_external(string $query, int $limit, array &$state): array
{
	$out = array('results' => array(), 'error' => false, 'captcha' => false);
	if ($query === '') {
		return $out;
	}
	$cached = fractal_zip_web_ref_search_cache_get($query);
	if ($cached !== null) {
		$out['results'] = array_slice($cached, 0, $limit);
		return $out;
	}

	$now = microtime(true);
	$last = (float) ($state['last_search_at'] ?? 0.0);
	$mult = max(1.0, (float) ($state['cooldown_mult'] ?? 1.0));
	$delayUs = (int) round(fractal_zip_web_ref_search_min_delay_us() * $mult);
	if ($last > 0.0) {
		$elapsedUs = (int) round(($now - $last) * 1_000_000);
		if ($elapsedUs < $delayUs) {
			usleep($delayUs - $elapsedUs);
		}
	}

	$provider = fractal_zip_web_ref_search_provider();
	$q = rawurlencode($query);
	$html = '';
	if ($provider === 'duckduckgo') {
		$html = fractal_zip_web_ref_search_http_get('https://html.duckduckgo.com/html/?q=' . $q);
		$results = fractal_zip_web_ref_search_parse_ddg($html, $limit);
	} elseif ($provider === 'google' && fractal_zip_web_ref_search_allow_fallback()) {
		$html = fractal_zip_web_ref_search_http_get('https://www.google.com/search?q=' . $q . '&num=' . min(10, $limit));
		$results = function_exists('live_browse_parse_search_links')
			? live_browse_parse_search_links($html, $limit)
			: array();
	} elseif ($provider === 'yandex' && fractal_zip_web_ref_search_allow_fallback()) {
		$html = fractal_zip_web_ref_search_http_get('https://yandex.com/search/?text=' . $q);
		$results = function_exists('live_browse_parse_search_links')
			? live_browse_parse_search_links($html, $limit)
			: array();
	} else {
		return $out;
	}

	$state['searches'] = (int) ($state['searches'] ?? 0) + 1;
	$state['last_search_at'] = microtime(true);

	if ($html === '' || fractal_zip_web_ref_search_is_captcha_html($html)) {
		$out['error'] = true;
		$out['captcha'] = fractal_zip_web_ref_search_is_captcha_html($html);
		$state['cooldown_mult'] = min(8.0, $mult * 2.0);
		return $out;
	}
	if ($results === array()) {
		$out['error'] = true;
		$state['cooldown_mult'] = min(6.0, $mult * 1.5);
		fractal_zip_web_ref_search_cache_put($query, array());
		return $out;
	}

	$state['cooldown_mult'] = max(1.0, $mult * 0.9);
	fractal_zip_web_ref_search_cache_put($query, $results);
	$out['results'] = array_slice($results, 0, $limit);
	return $out;
}

/**
 * Last-resort search pass for unmatched generic chunks.
 *
 * @param list<array{chunk: string, count: int, score: int, length: int}> $genericChunks
 * @param array<string, bool> $usedCodes
 * @param array{searches?: int, last_search_at?: float, cooldown_mult?: float} $searchState
 */
function fractal_zip_web_ref_probe_search_pass(
	string $fractalString,
	array $genericChunks,
	array &$usedCodes,
	array &$searchState
): array {
	$out = array(
		'fractal_string' => $fractalString,
		'entries' => array(),
		'saved' => 0,
		'probes' => 0,
		'search_matches' => 0,
		'wayback_matches' => 0,
		'searches' => 0,
	);
	if (!fractal_zip_web_ref_search_enabled()
		|| getenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR') === '1'
		|| $genericChunks === array()) {
		return $out;
	}
	fractal_zip_web_ref_bootstrap_live_browser();
	if (!function_exists('web_ref_probe_piece_at_url') || !function_exists('fractal_zip_web_ref_try_replace_chunk')) {
		return $out;
	}

	$maxEntries = fractal_zip_web_ref_probe_max_chunks();
	$maxSearches = fractal_zip_web_ref_search_max_per_run();
	$resultLimit = fractal_zip_web_ref_search_result_limit();

	foreach ($genericChunks as $item) {
		if (count($out['entries']) + count($usedCodes) >= $maxEntries) {
			break;
		}
		if ((int) ($searchState['searches'] ?? 0) >= $maxSearches) {
			break;
		}
		if (!fractal_zip_web_ref_search_chunk_eligible($item)) {
			continue;
		}
		$chunk = (string) ($item['chunk'] ?? '');
		if ($chunk === '' || strpos($fractalString, $chunk) === false) {
			continue;
		}
		$query = fractal_zip_web_ref_search_extract_query($chunk, (int) ($item['count'] ?? 1));
		if ($query === null) {
			continue;
		}

		$search = fractal_zip_web_ref_search_external($query, $resultLimit, $searchState);
		$out['searches']++;
		if ($search['error'] ?? false) {
			continue;
		}
		foreach ($search['results'] as $row) {
			$url = trim((string) ($row['url'] ?? ''));
			if ($url === '' || strpos($url, '://') === false) {
				continue;
			}
			$out['probes']++;
			$try = fractal_zip_web_ref_try_replace_chunk($fractalString, $chunk, $url);
			if (($try['saved'] ?? 0) <= 0 || ($try['entries'] ?? array()) === array()) {
				continue;
			}
			$entry = $try['entries'][0];
			$code = (string) ($entry['code'] ?? '');
			if ($code === '' || isset($usedCodes[$code])) {
				continue;
			}
			$fractalString = (string) $try['fractal_string'];
			$out['fractal_string'] = $fractalString;
			$out['entries'][] = $entry;
			$out['saved'] += (int) $try['saved'];
			$out['search_matches']++;
			$resolved = (string) ($entry['canonical_url'] ?? '');
			if (stripos($resolved, 'web.archive.org') !== false || stripos($resolved, 'archive.org') !== false) {
				$out['wayback_matches']++;
			}
			$usedCodes[$code] = true;
			break;
		}
	}
	return $out;
}
