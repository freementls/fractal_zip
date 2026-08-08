<?php
declare(strict_types=1);

/**
 * Mine phda9 external-dictionary tokens beyond whitespace words:
 * phrases (XML, lines, n-grams), subword pieces (roots, affixes, char n-grams).
 *
 * phda9 accepts arbitrary CRLF-separated lines (≤188240, ≤1930550 B header included).
 * Selection uses greedy estimated-bytes-saved per entry cost.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_score_gate.php';

/** @return array{count: int, est_save: int} */
function fractal_zip_phda9_dict_count_token(string $haystack, string $token): array
{
	if ($token === '') {
		return array('count' => 0, 'est_save' => 0);
	}
	$len = strlen($token);
	$count = 0;
	$pos = 0;
	while (($i = strpos($haystack, $token, $pos)) !== false) {
		$count++;
		$pos = $i + $len;
	}
	$est = $count * max(0, $len - 1);
	return array('count' => $count, 'est_save' => $est);
}

/**
 * @return list<array{token: string, kind: string, count: int, est_save: int}>
 */
function fractal_zip_phda9_dict_score_candidates(string $corpus, array $candidates): array
{
	$rows = array();
	foreach ($candidates as $c) {
		$token = trim((string) ($c['token'] ?? $c));
		if ($token === '' || str_contains($token, "\n") || str_contains($token, "\r")) {
			continue;
		}
		$kind = (string) ($c['kind'] ?? 'token');
		$stats = fractal_zip_phda9_dict_count_token($corpus, $token);
		if ($stats['count'] <= 0) {
			continue;
		}
		$rows[] = array(
			'token' => $token,
			'kind' => $kind,
			'count' => $stats['count'],
			'est_save' => $stats['est_save'],
		);
	}
	return $rows;
}

/**
 * Greedy knapsack: maximize estimated save under phda9 dict byte/word caps.
 *
 * @param list<array{token: string, kind?: string, count?: int, est_save: int}> $scored
 * @return list<string>
 */
function fractal_zip_phda9_dict_select_greedy(array $scored, ?int $maxWords = null, ?int $maxBytes = null): array
{
	$maxWords = $maxWords ?? fractal_zip_phda9_dict_max_words();
	$maxBytes = $maxBytes ?? fractal_zip_phda9_dict_max_bytes();
	$header = fractal_zip_phda9_dict_header_bytes();
	$used = array();
	foreach ($scored as $row) {
		$t = (string) $row['token'];
		if ($t === '' || isset($used[$t])) {
			continue;
		}
		$used[$t] = (int) $row['est_save'];
	}
	$tokens = array_keys($used);
	usort($tokens, static function (string $a, string $b) use ($used): int {
		$sa = $used[$a];
		$sb = $used[$b];
		if ($sa !== $sb) {
			return $sb <=> $sa;
		}
		return strlen($b) <=> strlen($a);
	});
	$out = array();
	$body = $header;
	$ratioThreshold = fractal_zip_score_ratio_threshold();
	foreach ($tokens as $t) {
		if (count($out) >= $maxWords) {
			break;
		}
		$lineCost = (float) max(1, strlen($t) + 2);
		$benefit = (float) ($used[$t] ?? 0);
		if (($benefit / $lineCost) <= $ratioThreshold) {
			continue;
		}
		$candidate = $header . implode("\r\n", array_merge($out, array($t))) . "\r\n";
		if (strlen($candidate) > $maxBytes) {
			continue;
		}
		$out[] = $t;
		$body = $candidate;
	}
	return $out;
}

/**
 * Greedy knapsack sorted by est_save / line cost (better small byte budgets).
 *
 * @param list<array{token: string, kind?: string, count?: int, est_save: int}> $scored
 * @return list<string>
 */
function fractal_zip_phda9_dict_select_greedy_efficiency(array $scored, ?int $maxWords = null, ?int $maxBytes = null): array
{
	$maxWords = $maxWords ?? fractal_zip_phda9_dict_max_words();
	$maxBytes = $maxBytes ?? fractal_zip_phda9_dict_max_bytes();
	$header = fractal_zip_phda9_dict_header_bytes();
	$rows = array();
	$seen = array();
	foreach ($scored as $row) {
		$t = (string) $row['token'];
		if ($t === '' || isset($seen[$t])) {
			continue;
		}
		$seen[$t] = true;
		$lineCost = strlen($t) + 2;
		$rows[] = array(
			'token' => $t,
			'est_save' => (int) $row['est_save'],
			'eff' => $lineCost > 0 ? (int) $row['est_save'] / $lineCost : 0,
		);
	}
	usort($rows, static function (array $a, array $b): int {
		if ($a['eff'] !== $b['eff']) {
			return $b['eff'] <=> $a['eff'];
		}
		if ($a['est_save'] !== $b['est_save']) {
			return $b['est_save'] <=> $a['est_save'];
		}
		return strlen($b['token']) <=> strlen($a['token']);
	});
	$out = array();
	$ratioThreshold = fractal_zip_score_ratio_threshold();
	foreach ($rows as $row) {
		if (count($out) >= $maxWords) {
			break;
		}
		if ($row['eff'] <= $ratioThreshold) {
			break;
		}
		$t = (string) $row['token'];
		$candidate = $header . implode("\r\n", array_merge($out, array($t))) . "\r\n";
		if (strlen($candidate) > $maxBytes) {
			continue;
		}
		$out[] = $t;
	}
	return $out;
}

/** @param list<string> $selected */
function fractal_zip_phda9_dict_token_covered_by(string $token, array $selected): bool
{
	foreach ($selected as $s) {
		if ($token === $s) {
			return true;
		}
		if (strlen($token) < strlen($s) && str_contains($s, $token)) {
			return true;
		}
	}
	return false;
}

/**
 * Tiered mix: words fill most of byte budget, then phrases, then subwords (no substring dupes).
 *
 * @param list<array{token: string, kind: string, est_save: int}> $scored
 * @param array{word_budget_pct?: float, max_subwords?: int, max_phrase_entries?: int} $opts
 * @return list<string>
 */
function fractal_zip_phda9_dict_select_mixed_tiered(array $scored, array $opts = array()): array
{
	$maxWords = fractal_zip_phda9_dict_max_words();
	$maxBytes = fractal_zip_phda9_dict_max_bytes();
	$header = fractal_zip_phda9_dict_header_bytes();
	$wordBudgetPct = (float) ($opts['word_budget_pct'] ?? 0.88);
	$maxSubwords = (int) ($opts['max_subwords'] ?? 4096);
	$maxPhrases = (int) ($opts['max_phrase_entries'] ?? 8192);
	if ($maxSubwords <= 0) {
		$maxSubwords = PHP_INT_MAX;
	} else {
		$maxSubwords = max(64, $maxSubwords);
	}
	if ($maxPhrases <= 0) {
		$maxPhrases = PHP_INT_MAX;
	} else {
		$maxPhrases = max(64, $maxPhrases);
	}
	$ratioThreshold = fractal_zip_score_ratio_threshold();
	$wordByteCap = (int) floor($maxBytes * max(0.5, min(0.98, $wordBudgetPct)));

	$byKind = array('word' => array(), 'phrase' => array(), 'subword' => array());
	$phraseKinds = array('boilerplate', 'line_phrase', 'article_word', 'xml_tag', 'word_ngram');
	foreach ($scored as $row) {
		$kind = (string) ($row['kind'] ?? 'token');
		$bucket = 'phrase';
		if ($row['kind'] === 'word') {
			$bucket = 'word';
		} elseif ($kind === 'skel_word') {
			$bucket = 'word';
		} elseif ($kind === 'subword') {
			$bucket = 'subword';
		} elseif (!in_array($kind, $phraseKinds, true)) {
			continue;
		}
		$byKind[$bucket][] = $row;
	}
	$sortRows = static function (array &$rows): void {
		usort($rows, static function (array $a, array $b): int {
			$ea = (int) ($a['est_save'] ?? 0);
			$eb = (int) ($b['est_save'] ?? 0);
			if ($ea !== $eb) {
				return $eb <=> $ea;
			}
			return strlen((string) $b['token']) <=> strlen((string) $a['token']);
		});
	};
	foreach (array_keys($byKind) as $k) {
		$sortRows($byKind[$k]);
	}

	$out = array();
	$body = $header;
	$add = static function (string $t) use (&$out, &$body, $header, $maxWords, $maxBytes): bool {
		if ($t === '' || in_array($t, $out, true) || count($out) >= $maxWords) {
			return false;
		}
		$candidate = $header . implode("\r\n", array_merge($out, array($t))) . "\r\n";
		if (strlen($candidate) > $maxBytes) {
			return false;
		}
		$out[] = $t;
		$body = $candidate;
		return true;
	};

	foreach ($byKind['word'] as $row) {
		if (strlen($body) >= $wordByteCap) {
			break;
		}
		$add((string) $row['token']);
	}
	$phraseAdded = 0;
	foreach ($byKind['phrase'] as $row) {
		if ($phraseAdded >= $maxPhrases) {
			break;
		}
		$lineCost = (float) max(1, strlen((string) $row['token']) + 2);
		if (((int) ($row['est_save'] ?? 0) / $lineCost) <= $ratioThreshold) {
			break;
		}
		$t = (string) $row['token'];
		if (fractal_zip_phda9_dict_token_covered_by($t, $out)) {
			continue;
		}
		if ($add($t)) {
			$phraseAdded++;
		}
	}
	$subAdded = 0;
	foreach ($byKind['subword'] as $row) {
		if ($subAdded >= $maxSubwords) {
			break;
		}
		$lineCost = (float) max(1, strlen((string) $row['token']) + 2);
		if (((int) ($row['est_save'] ?? 0) / $lineCost) <= $ratioThreshold) {
			break;
		}
		$t = (string) $row['token'];
		if (fractal_zip_phda9_dict_token_covered_by($t, $out)) {
			continue;
		}
		if ($add($t)) {
			$subAdded++;
		}
	}
	return $out;
}

/**
 * @param list<string> $tokens
 * @param list<array{token: string, kind: string, est_save: int}> $scored
 * @return list<array{token: string, kind: string, eff: int}>
 */
function fractal_zip_phda9_dict_refine_pick_candidates(array $tokens, array $scored, int $maxTrials): array
{
	$selected = array_fill_keys($tokens, true);
	$cands = array();
	$relax = getenv('FRACTAL_ZIP_PHDA9_DICT_REFINE_RELAX');
	$relaxSubword = $relax === false || trim((string) $relax) === ''
		|| !in_array(strtolower(trim((string) $relax)), array('0', 'off', 'false', 'no'), true);
	foreach ($scored as $row) {
		$t = (string) $row['token'];
		$kind = (string) ($row['kind'] ?? '');
		if (isset($selected[$t])) {
			continue;
		}
		if ($kind === 'word') {
			$lineCost = strlen($t) + 2;
			$eff = $lineCost > 0 ? (int) $row['est_save'] / $lineCost : 0;
			$cands[] = array('token' => $t, 'kind' => $kind, 'eff' => $eff, 'op' => 'add');
			continue;
		}
		if ($kind === 'subword' && !$relaxSubword && fractal_zip_phda9_dict_token_covered_by($t, $tokens)) {
			continue;
		}
		$lineCost = strlen($t) + 2;
		$eff = $lineCost > 0 ? (int) $row['est_save'] / $lineCost : 0;
		$cands[] = array('token' => $t, 'kind' => $kind, 'eff' => $eff, 'op' => 'add');
	}
	usort($cands, static fn (array $a, array $b): int => ($b['eff'] <=> $a['eff']) ?: (strlen($b['token']) <=> strlen($a['token'])));
	$addCands = array_slice($cands, 0, max(1, (int) floor($maxTrials * 0.75)));

	$removeCands = array();
	$byToken = array();
	foreach ($scored as $row) {
		$byToken[(string) $row['token']] = $row;
	}
	foreach ($tokens as $t) {
		if (!isset($byToken[$t])) {
			continue;
		}
		$row = $byToken[$t];
		$lineCost = strlen($t) + 2;
		$eff = $lineCost > 0 ? (int) ($row['est_save'] ?? 0) / $lineCost : 0;
		$removeCands[] = array('token' => $t, 'kind' => (string) ($row['kind'] ?? 'word'), 'eff' => $eff, 'op' => 'remove');
	}
	usort($removeCands, static fn (array $a, array $b): int => ($a['eff'] <=> $b['eff']) ?: (strlen($a['token']) <=> strlen($b['token'])));
	$removeMax = max(0, min(8, $maxTrials - count($addCands)));
	$removeCands = array_slice($removeCands, 0, $removeMax);

	return array_merge($addCands, $removeCands);
}

/**
 * After tiered mix, try adding top phrase/subword candidates when real phda9 compress improves.
 *
 * @param list<string> $tokens
 * @param list<array{token: string, kind: string, est_save: int}> $scored
 * @return array{tokens: list<string>, trials: int, best_bytes: ?int, baseline_bytes: ?int}
 */
function fractal_zip_phda9_dict_refine_with_compress(
	string $pageXml,
	array $tokens,
	array $scored,
	int $maxTrials = 24,
	?int $knownBaselineBytes = null
): array {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_phda9_english.php';
	putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
	$tool = fractal_zip_enwik_phda9_english_tool_id();

	$tryDict = static function (array $words) use ($pageXml, $tool): ?int {
		$path = sys_get_temp_dir() . '/fz_phda9_refine_' . getmypid() . '_' . bin2hex(random_bytes(3)) . '.txt';
		fractal_zip_phda9_dict_write_file($words, $path);
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $path);
		$r = fractal_zip_enwik_phda9_english_compress($pageXml, array(
			'tool' => $tool,
			'use_dict' => true,
			'timeout_sec' => 0,
			'wire_wrap' => true,
		));
		@unlink($path);
		if (empty($r['roundtrip_ok']) || !isset($r['bytes'])) {
			return null;
		}
		return (int) $r['bytes'];
	};

	$best = $tokens;
	$baselineBytes = $knownBaselineBytes;
	if ($baselineBytes === null) {
		$baselineBytes = $tryDict($best);
	}
	if ($baselineBytes === null) {
		return array('tokens' => $tokens, 'trials' => 0, 'best_bytes' => null, 'baseline_bytes' => null);
	}
	$bestBytes = $baselineBytes;
	$cands = fractal_zip_phda9_dict_refine_pick_candidates($best, $scored, $maxTrials);
	$selected = array_fill_keys($best, true);

	$trials = 0;
	foreach ($cands as $c) {
		$t = (string) $c['token'];
		$op = (string) ($c['op'] ?? 'add');
		$trials++;
		if ($op === 'remove') {
			$trial = array_values(array_filter($best, static fn (string $w): bool => $w !== $t));
		} else {
			$trial = array_merge($best, array($t));
		}
		$bytes = $tryDict($trial);
		if ($bytes !== null && $bytes < $bestBytes) {
			$bestBytes = $bytes;
			$best = $trial;
			if ($op === 'add') {
				$selected[$t] = true;
			} else {
				unset($selected[$t]);
			}
		}
	}
	return array('tokens' => $best, 'trials' => $trials, 'best_bytes' => $bestBytes, 'baseline_bytes' => $baselineBytes);
}

/**
 * Frequent word bigrams / trigrams from whitespace tokens.
 *
 * @return list<array{token: string, kind: string}>
 */
function fractal_zip_phda9_dict_mine_word_ngrams(string $text, int $minCount, int $maxEntries, int $maxGram = 3): array
{
	$words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: array();
	$n = count($words);
	if ($n < 2) {
		return array();
	}
	$counts = array();
	for ($g = 2; $g <= min($maxGram, 4); $g++) {
		for ($i = 0; $i + $g <= $n; $i++) {
			$slice = array_slice($words, $i, $g);
			$tok = implode(' ', $slice);
			if (strlen($tok) < 3 || strlen($tok) > 48) {
				continue;
			}
			$counts[$tok] = ($counts[$tok] ?? 0) + 1;
		}
	}
	$out = array();
	arsort($counts, SORT_NUMERIC);
	foreach ($counts as $tok => $c) {
		if ($c < $minCount) {
			break;
		}
		$out[] = array('token' => $tok, 'kind' => 'word_ngram');
		if (count($out) >= $maxEntries) {
			break;
		}
	}
	return $out;
}

/**
 * Char n-grams and affixes from frequent tokens (subword pieces for phda9 line dict).
 *
 * @return list<array{token: string, kind: string}>
 */
function fractal_zip_phda9_dict_mine_subword_pieces(string $text, int $minCount, int $maxEntries): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	$counts = array();
	foreach (fractal_zip_enwik_text_segment_implicit_space($text) as $seg) {
		if (($seg['type'] ?? '') !== 'word') {
			continue;
		}
		$w = (string) ($seg['text'] ?? '');
		$wl = strlen($w);
		if ($wl < 4) {
			continue;
		}
		for ($len = 3; $len <= min(8, $wl - 1); $len++) {
			$pre = substr($w, 0, $len);
			$suf = substr($w, -$len);
			if ($pre !== '') {
				$counts[$pre] = ($counts[$pre] ?? 0) + 1;
			}
			if ($suf !== '' && $suf !== $pre) {
				$counts[$suf] = ($counts[$suf] ?? 0) + 1;
			}
		}
		for ($len = 4; $len <= 12; $len++) {
			if ($wl < $len) {
				break;
			}
			for ($i = 0; $i + $len <= $wl; $i++) {
				$ng = substr($w, $i, $len);
				if (strspn($ng, " \t\r\n") === strlen($ng)) {
					continue;
				}
				$counts[$ng] = ($counts[$ng] ?? 0) + 1;
			}
		}
	}
	if (is_file('/srv/http/quantum_grammar/src/SubWordRootCodec.php')) {
		require_once '/srv/http/quantum_grammar/src/SubWordRootCodec.php';
		$model = SubWordRootCodec::mineVocab($text, min(8192, $maxEntries), max(2, $minCount));
		foreach ($model['vocab'] ?? array() as $root) {
			$root = (string) $root;
			if ($root !== '') {
				$counts[$root] = ($counts[$root] ?? 0) + max($minCount, 2);
			}
		}
	}
	$out = array();
	arsort($counts, SORT_NUMERIC);
	foreach ($counts as $tok => $c) {
		if ($c < $minCount || strlen($tok) < 3) {
			continue;
		}
		$out[] = array('token' => $tok, 'kind' => 'subword');
		if (count($out) >= $maxEntries) {
			break;
		}
	}
	return $out;
}

/**
 * XML / line phrases (outside word tokenizer).
 *
 * @return list<array{token: string, kind: string}>
 */
function fractal_zip_phda9_dict_mine_phrases(string $blob, int $pages, int $minCount, int $maxEntries): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	$out = array();
	$seen = array();
	$add = static function (string $tok, string $kind) use (&$out, &$seen): void {
		if ($tok === '' || isset($seen[$tok])) {
			return;
		}
		$seen[$tok] = true;
		$out[] = array('token' => $tok, 'kind' => $kind);
	};
	foreach (fractal_zip_enwik_boilerplate_phrases() as $p) {
		$add((string) $p, 'boilerplate');
	}
	$minePages = min($pages, 4096);
	$phrases = fractal_zip_enwik_mine_corpus_phrases($blob, 8, 80, max(4, (int) floor($minCount / 2)), min(256, $maxEntries));
	foreach ($phrases as $p) {
		$add((string) $p, 'line_phrase');
	}
	$words = fractal_zip_enwik_mine_article_word_phrases($blob, max(8, (int) floor($minCount / 4)), 96, 6, 64);
	foreach ($words as $p) {
		$add((string) $p, 'article_word');
	}
	if (preg_match_all('/<[^>\r\n]{3,48}>/', $blob, $m)) {
		$tagCounts = array_count_values($m[0]);
		arsort($tagCounts, SORT_NUMERIC);
		foreach ($tagCounts as $tag => $c) {
			if ($c < $minCount) {
				break;
			}
			$add((string) $tag, 'xml_tag');
			if (count($out) >= $maxEntries) {
				break;
			}
		}
	}
	return array_slice($out, 0, $maxEntries);
}

/**
 * All z_* skeleton stream tokens in a consonant_hybrid payload (no min_count gate).
 *
 * @return list<array{token: string, kind: string, count: int}>
 */
function fractal_zip_phda9_dict_mine_skeleton_stream_tokens(string $text): array
{
	if ($text === '') {
		return array();
	}
	$counts = array();
	if (preg_match_all('/z_[a-z]+(?:_r\d+)?/', $text, $m)) {
		foreach ($m[0] as $tok) {
			$counts[$tok] = ($counts[$tok] ?? 0) + 1;
		}
	}
	$out = array();
	foreach ($counts as $tok => $c) {
		$out[] = array(
			'token' => (string) $tok,
			'kind' => 'skel_word',
			'count' => (int) $c,
		);
	}
	usort($out, static function (array $a, array $b): int {
		$ca = (int) ($a['count'] ?? 0);
		$cb = (int) ($b['count'] ?? 0);
		if ($ca !== $cb) {
			return $cb <=> $ca;
		}
		return strlen((string) $b['token']) <=> strlen((string) $a['token']);
	});
	return $out;
}

/**
 * Frequent segmented word tokens (consonant_hybrid z_* stream, literals, CamelCase).
 *
 * @return list<array{token: string, kind: string}>
 */
function fractal_zip_phda9_dict_mine_segment_word_tokens(
	string $text,
	int $minCount,
	int $maxEntries,
	bool $allSkeletonTokens = false
): array {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
	if ($text === '') {
		return array();
	}
	if ($allSkeletonTokens) {
		$out = array();
		foreach (fractal_zip_phda9_dict_mine_skeleton_stream_tokens($text) as $row) {
			$out[] = array('token' => (string) $row['token'], 'kind' => 'skel_word');
			if (count($out) >= $maxEntries) {
				return $out;
			}
		}
		return $out;
	}
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$counts = array();
	foreach ($segments as $seg) {
		if (($seg['type'] ?? '') !== 'word') {
			continue;
		}
		$w = trim((string) ($seg['text'] ?? ''));
		if ($w === '' || strlen($w) < 2) {
			continue;
		}
		$counts[$w] = ($counts[$w] ?? 0) + 1;
	}
	$skel = array();
	$literal = array();
	foreach ($counts as $tok => $c) {
		if (str_starts_with($tok, 'z_')) {
			$skel[$tok] = $c;
		} else {
			$literal[$tok] = $c;
		}
	}
	arsort($skel, SORT_NUMERIC);
	arsort($literal, SORT_NUMERIC);
	$out = array();
	foreach ($skel as $tok => $c) {
		$out[] = array('token' => $tok, 'kind' => 'skel_word');
		if (count($out) >= $maxEntries) {
			return $out;
		}
	}
	foreach ($literal as $tok => $c) {
		if ($c < $minCount) {
			break;
		}
		$out[] = array('token' => $tok, 'kind' => 'word');
		if (count($out) >= $maxEntries) {
			break;
		}
	}
	return $out;
}

/**
 * Build sorted preprocessed page XML + encoded preserve-text corpus for dict mining.
 *
 * @return array{
 *   page_xml: string,
 *   slice_blob: string,
 *   encoded_text: string,
 *   model: array<string, mixed>,
 *   payload_codec: string
 * }
 */
function fractal_zip_phda9_dict_build_consonant_hybrid_corpus(
	string $blob,
	int $nPages,
	array $opts = array()
): array {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	$split = enwik_split_page_refs($blob);
	if ($split === null) {
		throw new RuntimeException('phda9_dict: enwik split failed');
	}
	$nPages = min(max(1, $nPages), count($split['pages']));
	$payloadCodec = (string) ($opts['payload_codec'] ?? '');
	if ($payloadCodec === '') {
		$env = getenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC');
		$payloadCodec = ($env !== false && trim((string) $env) !== '') ? trim((string) $env) : 'ascii';
	}
	$mineText = '';
	$pageTexts = array();
	for ($i = 0; $i < $nPages; $i++) {
		$p = $split['pages'][$i];
		$pageXml = substr($blob, (int) $p['start'], (int) $p['len']);
		$pt = fractal_zip_enwik_extract_page_preserve_text($pageXml);
		$pageTexts[] = $pt;
		$mineText .= $pt;
	}
	$model = is_array($opts['consonant_model'] ?? null)
		? $opts['consonant_model']
		: fractal_zip_enwik_consonant_hybrid_mine_model_pages($pageTexts, $mineText);
	$preOpts = array(
		'consonant_model' => $model,
		'frozen' => true,
		'payload_codec' => $payloadCodec,
	);
	$pageXmlBuf = '';
	$encodedText = '';
	$sliceBlob = (string) $split['header'];
	for ($i = 0; $i < $nPages; $i++) {
		$p = $split['pages'][$i];
		$pageXml = substr($blob, (int) $p['start'], (int) $p['len']);
		$parts = fractal_zip_enwik_page_markup_text_split($pageXml);
		$pre = fractal_zip_text_preprocess_apply('consonant_hybrid', (string) $parts['text'], $preOpts);
		$wire = (string) $pre['payload'];
		$injected = fractal_zip_enwik_inject_text_into_shell_page((string) $parts['shell'], $wire);
		$pageXmlBuf .= $injected;
		$sliceBlob .= $injected;
		$encodedText .= $wire;
	}
	$sliceBlob .= (string) $split['footer'];
	return array(
		'page_xml' => $pageXmlBuf,
		'slice_blob' => $sliceBlob,
		'encoded_text' => $encodedText,
		'model' => $model,
		'payload_codec' => $payloadCodec,
	);
}

/**
 * @param array{mode?: string, pages?: int, min_count?: int, max_candidates?: int, chunk_pages?: int, preprocess?: string, payload_codec?: string} $opts
 * @return array{words: list<string>, stats: array<string, mixed>}
 */
function fractal_zip_phda9_dict_mine_from_enwik(string $blob, array $opts = array()): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	$split = enwik_split_page_refs($blob);
	if ($split === null) {
		throw new RuntimeException('phda9_dict_mine: enwik split failed');
	}
	$pages = max(32, (int) ($opts['pages'] ?? 384));
	$nPages = min($pages, count($split['pages']));
	$mode = strtolower(trim((string) ($opts['mode'] ?? 'optimal')));
	$chunkPages = max(32, (int) ($opts['chunk_pages'] ?? ($nPages > 2048 ? 96 : ($nPages > 768 ? 192 : 384))));
	if ($nPages > 96) {
		$chunkPages = min($chunkPages, 96);
	}
	$minCount = max(2, (int) ($opts['min_count'] ?? max(3, (int) floor(24_000 / max(1, $nPages)))));
	$maxCand = max(4096, (int) ($opts['max_candidates'] ?? 65536));
	$preprocessId = strtolower(trim((string) ($opts['preprocess'] ?? '')));
	$encodedCorpus = '';
	$scoreCorpus = '';
	if ($preprocessId === 'consonant_hybrid') {
		$corp = fractal_zip_phda9_dict_build_consonant_hybrid_corpus($blob, $nPages, $opts);
		$sliceBlob = (string) $corp['slice_blob'];
		$mineXml = (string) $corp['page_xml'];
		$encodedCorpus = (string) $corp['encoded_text'];
		$scoreCorpus = $mineXml;
		$opts['_consonant_model'] = $corp['model'];
		$opts['_payload_codec'] = $corp['payload_codec'];
	} else {
		$sliceBlob = (string) $split['header'];
		$mineXml = '';
		for ($i = 0; $i < $nPages; $i++) {
			$p = $split['pages'][$i];
			$pageXml = substr($blob, (int) $p['start'], (int) $p['len']);
			$sliceBlob .= $pageXml;
			$mineXml .= $pageXml;
		}
		$sliceBlob .= (string) $split['footer'];
	}
	$mineWords = in_array($mode, array('words', 'words_prose_only', 'optimal', 'mixed', 'mixed_tiered', 'mixed_refine'), true);
	$minePhrases = in_array($mode, array('phrases', 'optimal', 'mixed', 'mixed_tiered', 'mixed_refine'), true);
	$mineSubwords = in_array($mode, array('subwords', 'optimal', 'mixed', 'mixed_tiered', 'mixed_refine'), true);
	$proseOnly = ($mode === 'words_prose_only');
	$candidates = array();
	if ($mineWords) {
		$maxWords = min(fractal_zip_phda9_dict_max_words(), 65536);
		$seenWords = array();
		if ($preprocessId === 'consonant_hybrid' && $encodedCorpus !== '') {
			foreach (fractal_zip_phda9_dict_mine_skeleton_stream_tokens($scoreCorpus !== '' ? $scoreCorpus : $encodedCorpus) as $c) {
				$t = (string) $c['token'];
				if (!isset($seenWords[$t])) {
					$seenWords[$t] = true;
					$candidates[] = array('token' => $t, 'kind' => 'skel_word');
				}
			}
			$literalMin = 1;
			foreach (fractal_zip_phda9_dict_mine_segment_word_tokens(
				$encodedCorpus,
				$literalMin,
				min($maxWords, $maxCand),
				false
			) as $c) {
				$t = (string) $c['token'];
				if (str_starts_with($t, 'z_') || isset($seenWords[$t])) {
					continue;
				}
				$seenWords[$t] = true;
				$candidates[] = $c;
			}
		} else {
			for ($start = 0; $start < $nPages; $start += $chunkPages) {
				$chunkText = '';
				$end = min($start + $chunkPages, $nPages);
				for ($i = $start; $i < $end; $i++) {
					$p = $split['pages'][$i];
					$pageXml = substr($blob, (int) $p['start'], (int) $p['len']);
					$text = fractal_zip_enwik_extract_page_preserve_text($pageXml);
					if (is_string($text)) {
						$chunkText .= $text;
					}
				}
				if ($chunkText === '') {
					continue;
				}
				$batch = fractal_zip_text_dict_nncp_mine_vocab($chunkText, array(
					'max_words' => $maxWords,
					'min_word_len' => 2,
				));
				foreach ($batch as $w) {
					if (!isset($seenWords[$w])) {
						$seenWords[$w] = true;
						$candidates[] = array('token' => $w, 'kind' => 'word');
					}
				}
			}
		}
	}
	if ($minePhrases) {
		$candidates = array_merge(
			$candidates,
			fractal_zip_phda9_dict_mine_phrases($sliceBlob, $nPages, $minCount, min(8192, $maxCand))
		);
		$ngramSeen = array();
		for ($start = 0; $start < $nPages; $start += $chunkPages) {
			$chunkText = '';
			$end = min($start + $chunkPages, $nPages);
			for ($i = $start; $i < $end; $i++) {
				$p = $split['pages'][$i];
				if ($preprocessId === 'consonant_hybrid' && $encodedCorpus !== '') {
					$pageXml = substr($blob, (int) $p['start'], (int) $p['len']);
					$parts = fractal_zip_enwik_page_markup_text_split($pageXml);
					$preOpts = array(
						'consonant_model' => $opts['_consonant_model'] ?? null,
						'frozen' => true,
						'payload_codec' => $opts['_payload_codec'] ?? 'ascii',
					);
					$pre = fractal_zip_text_preprocess_apply('consonant_hybrid', (string) $parts['text'], $preOpts);
					$chunkText .= (string) $pre['payload'];
				} else {
					$text = fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
					if (is_string($text)) {
						if ($proseOnly) {
							require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
							$text = fractal_zip_wiki_lom_extract_prose(
								fractal_zip_wiki_html_encode($text)
							);
						}
						$chunkText .= $text;
					}
				}
			}
			if ($chunkText === '') {
				continue;
			}
			foreach (fractal_zip_phda9_dict_mine_word_ngrams($chunkText, $minCount, min(4096, $maxCand)) as $c) {
				$t = (string) $c['token'];
				if (!isset($ngramSeen[$t])) {
					$ngramSeen[$t] = true;
					$candidates[] = $c;
				}
			}
		}
	}
	if ($mineSubwords) {
		$subSeen = array();
		for ($start = 0; $start < $nPages; $start += $chunkPages) {
			$chunkText = '';
			$end = min($start + $chunkPages, $nPages);
			for ($i = $start; $i < $end; $i++) {
				$p = $split['pages'][$i];
				if ($preprocessId === 'consonant_hybrid' && $encodedCorpus !== '') {
					$pageXml = substr($blob, (int) $p['start'], (int) $p['len']);
					$parts = fractal_zip_enwik_page_markup_text_split($pageXml);
					$preOpts = array(
						'consonant_model' => $opts['_consonant_model'] ?? null,
						'frozen' => true,
						'payload_codec' => $opts['_payload_codec'] ?? 'ascii',
					);
					$pre = fractal_zip_text_preprocess_apply('consonant_hybrid', (string) $parts['text'], $preOpts);
					$chunkText .= (string) $pre['payload'];
				} else {
					$text = fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
					if (is_string($text)) {
						if ($proseOnly) {
							require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
							$text = fractal_zip_wiki_lom_extract_prose(
								fractal_zip_wiki_html_encode($text)
							);
						}
						$chunkText .= $text;
					}
				}
			}
			if ($chunkText === '') {
				continue;
			}
			foreach (fractal_zip_phda9_dict_mine_subword_pieces($chunkText, $minCount, min(8192, $maxCand)) as $c) {
				$t = (string) $c['token'];
				if (!isset($subSeen[$t])) {
					$subSeen[$t] = true;
					$candidates[] = $c;
				}
			}
		}
	}
	$corpus = $scoreCorpus !== '' ? $scoreCorpus : ($mineXml !== '' ? $mineXml : $sliceBlob);
	$scored = fractal_zip_phda9_dict_score_candidates($corpus, $candidates);
	$refinePlain = (string) ($opts['refine_plain'] ?? '');
	$refineTrials = max(0, (int) ($opts['refine_trials'] ?? 0));
	if ($mode === 'words') {
		$selected = array();
		foreach ($scored as $row) {
			if ($row['kind'] === 'word' || $row['kind'] === 'skel_word') {
				$selected[] = $row['token'];
			}
		}
	} elseif ($mode === 'phrases') {
		$phraseKinds = array('boilerplate', 'line_phrase', 'article_word', 'xml_tag', 'word_ngram');
		$filtered = array();
		foreach ($scored as $row) {
			if (in_array($row['kind'], $phraseKinds, true)) {
				$filtered[] = $row;
			}
		}
		$selected = fractal_zip_phda9_dict_select_greedy($filtered);
	} elseif ($mode === 'subwords') {
		$filtered = array();
		foreach ($scored as $row) {
			if ($row['kind'] === 'subword') {
				$filtered[] = $row;
			}
		}
		$selected = fractal_zip_phda9_dict_select_greedy($filtered);
	} elseif ($mode === 'mixed' || $mode === 'mixed_tiered') {
		$selected = fractal_zip_phda9_dict_select_mixed_tiered($scored, array(
			'word_budget_pct' => (float) ($opts['word_budget_pct'] ?? 0.88),
			'max_subwords' => (int) ($opts['max_subwords'] ?? 4096),
			'max_phrase_entries' => (int) ($opts['max_phrase_entries'] ?? 8192),
		));
	} elseif ($mode === 'mixed_refine') {
		$seedWords = $opts['seed_words'] ?? null;
		if (is_array($seedWords) && $seedWords !== array()) {
			$selected = array_values($seedWords);
		} else {
			$selected = fractal_zip_phda9_dict_select_mixed_tiered($scored, array(
				'word_budget_pct' => (float) ($opts['word_budget_pct'] ?? 0.90),
				'max_subwords' => (int) ($opts['max_subwords'] ?? 2048),
				'max_phrase_entries' => (int) ($opts['max_phrase_entries'] ?? 4096),
			));
		}
		if ($refinePlain !== '' && $refineTrials > 0) {
			$knownBaseline = isset($opts['refine_baseline_bytes']) ? (int) $opts['refine_baseline_bytes'] : null;
			if ($knownBaseline !== null && $knownBaseline <= 0) {
				$knownBaseline = null;
			}
			$ref = fractal_zip_phda9_dict_refine_with_compress(
				$refinePlain,
				$selected,
				$scored,
				$refineTrials,
				$knownBaseline
			);
			$selected = $ref['tokens'];
			$opts['_refine_trials'] = $ref['trials'];
			$opts['_refine_best_bytes'] = $ref['best_bytes'];
			$opts['_refine_baseline_bytes'] = $ref['baseline_bytes'];
		}
	} else {
		$selected = fractal_zip_phda9_dict_select_greedy($scored);
	}
	$kindHist = array();
	foreach ($scored as $row) {
		if (!in_array($row['token'], $selected, true)) {
			continue;
		}
		$k = (string) $row['kind'];
		$kindHist[$k] = ($kindHist[$k] ?? 0) + 1;
	}
	return array(
		'words' => $selected,
		'stats' => array(
			'pages' => $nPages,
			'mode' => $mode,
			'min_count' => $minCount,
			'candidates' => count($candidates),
			'scored' => count($scored),
			'selected' => count($selected),
			'kind_hist' => $kindHist,
			'mine_text_bytes' => $encodedCorpus !== '' ? strlen($encodedCorpus) : strlen($sliceBlob),
			'mine_xml_bytes' => strlen($mineXml),
			'preprocess' => $preprocessId !== '' ? $preprocessId : null,
			'skel_candidates' => $preprocessId === 'consonant_hybrid'
				? count(array_filter($candidates, static fn(array $c): bool => ($c['kind'] ?? '') === 'skel_word'))
				: null,
			'refine_trials' => $opts['_refine_trials'] ?? null,
			'refine_best_bytes' => $opts['_refine_best_bytes'] ?? null,
			'refine_baseline_bytes' => $opts['_refine_baseline_bytes'] ?? null,
		),
	);
}

/**
 * English mixed dict + z_* skeleton tokens scored on consonant_hybrid page XML.
 *
 * @param array{payload_codec?: string, consonant_model?: array<string, mixed>, min_est_save?: int, skel_placement?: string} $opts
 * @return array{words: list<string>, stats: array<string, mixed>, written: array<string, mixed>}
 */
function fractal_zip_phda9_dict_build_consonant_merged_dict(
	string $blob,
	int $nPages,
	string $baseDictPath,
	string $outPath,
	array $opts = array()
): array {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';
	$corp = fractal_zip_phda9_dict_build_consonant_hybrid_corpus($blob, $nPages, $opts);
	$pageXml = (string) $corp['page_xml'];
	$minSave = max(0, (int) ($opts['min_est_save'] ?? 1));
	$candidates = array();
	foreach (fractal_zip_phda9_dict_mine_skeleton_stream_tokens($pageXml) as $row) {
		$candidates[] = array(
			'token' => (string) $row['token'],
			'kind' => 'skel_word',
		);
	}
	$scored = fractal_zip_phda9_dict_score_candidates($pageXml, $candidates);
	usort($scored, static function (array $a, array $b): int {
		$ea = (int) ($a['est_save'] ?? 0);
		$eb = (int) ($b['est_save'] ?? 0);
		if ($ea !== $eb) {
			return $eb <=> $ea;
		}
		return strlen((string) $b['token']) <=> strlen((string) $a['token']);
	});
	$skelPick = array();
	foreach ($scored as $row) {
		if ((int) ($row['est_save'] ?? 0) < $minSave) {
			continue;
		}
		$skelPick[] = (string) $row['token'];
	}
	$placement = strtolower(trim((string) ($opts['skel_placement'] ?? 'append')));
	if ($placement !== 'prepend' && $placement !== 'append') {
		$placement = 'append';
	}
	$written = fractal_zip_phda9_dict_write_merged_consonant($baseDictPath, $skelPick, $outPath, $placement);
	return array(
		'words' => fractal_zip_phda9_dict_read_words($outPath),
		'stats' => array(
			'pages' => $nPages,
			'preprocess' => 'consonant_hybrid',
			'merge_base' => $baseDictPath,
			'skel_candidates' => count($candidates),
			'skel_selected' => count($skelPick),
			'skel_scored' => count($scored),
			'base_words' => (int) ($written['base_words'] ?? 0),
			'dict_words' => (int) ($written['words'] ?? 0),
			'dict_bytes' => (int) ($written['bytes'] ?? 0),
		),
		'written' => $written,
	);
}

/**
 * Build literal + skeleton streams from consonant_hybrid_split preprocess.
 *
 * @return array{
 *   literal_text: string,
 *   sk_text: string,
 *   model: array<string, mixed>,
 *   sk_rows: int
 * }
 */
function fractal_zip_phda9_dict_build_consonant_split_streams(string $blob, int $nPages, array $opts = array()): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	$split = enwik_split_page_refs($blob);
	if ($split === null) {
		throw new RuntimeException('phda9_dict: enwik split failed');
	}
	$nPages = min(max(1, $nPages), count($split['pages']));
	$pageTexts = array();
	$mineText = '';
	for ($i = 0; $i < $nPages; $i++) {
		$p = $split['pages'][$i];
		$pageXml = substr($blob, (int) $p['start'], (int) $p['len']);
		$pt = fractal_zip_enwik_extract_page_preserve_text($pageXml);
		$pageTexts[] = $pt;
		$mineText .= $pt;
	}
	$model = is_array($opts['consonant_model'] ?? null)
		? $opts['consonant_model']
		: fractal_zip_enwik_consonant_hybrid_mine_model_pages($pageTexts, $mineText);
	$preOpts = array('consonant_model' => $model, 'frozen' => true);
	$litBuf = '';
	$skBuf = '';
	$sent = FRACTAL_ZIP_CONSONANT_SK_SPLIT_SENTINEL;
	foreach ($pageTexts as $t) {
		$sp = fractal_zip_enwik_consonant_hybrid_split_preprocess($t, $model, $preOpts);
		$payload = (string) $sp['payload'];
		$pos = strpos($payload, $sent);
		if ($pos === false) {
			$litBuf .= $payload;
			continue;
		}
		$litBuf .= substr($payload, 0, $pos);
		$skBuf .= substr($payload, $pos + strlen($sent)) . "\n";
	}
	return array(
		'literal_text' => $litBuf,
		'sk_text' => rtrim($skBuf, "\n"),
		'model' => $model,
		'sk_rows' => count($model['skeleton_unique'] ?? array()),
	);
}

/**
 * phda9 dictionary tuned for isolated z_* skeleton streams (dual sk members).
 *
 * @param array{consonant_model?: array<string, mixed>, include_subwords?: bool} $opts
 * @return array{words: list<string>, stats: array<string, mixed>, written: array<string, mixed>}
 */
function fractal_zip_phda9_dict_build_consonant_split_sk_dict(
	string $blob,
	int $nPages,
	string $outPath,
	array $opts = array()
): array {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';
	$streams = fractal_zip_phda9_dict_build_consonant_split_streams($blob, $nPages, $opts);
	$skText = (string) $streams['sk_text'];
	$candidates = array();
	foreach (fractal_zip_phda9_dict_mine_skeleton_stream_tokens($skText) as $row) {
		$candidates[] = array(
			'token' => (string) $row['token'],
			'kind' => 'skel_word',
		);
	}
	if (!empty($opts['include_subwords'])) {
		foreach (fractal_zip_phda9_dict_mine_subword_pieces($skText, 2, 512) as $row) {
			$candidates[] = $row;
		}
	}
	$scored = fractal_zip_phda9_dict_score_candidates($skText, $candidates);
	$selected = fractal_zip_phda9_dict_select_mixed_tiered($scored, array(
		'word_budget_pct' => 0.98,
		'max_subwords' => (int) ($opts['max_subwords'] ?? 256),
		'max_phrase_entries' => 0,
	));
	if ($selected === array()) {
		$selected = array_map(static fn(array $row): string => (string) $row['token'], $candidates);
	}
	$written = fractal_zip_phda9_dict_write_file($selected, $outPath);
	return array(
		'words' => fractal_zip_phda9_dict_read_words($outPath),
		'stats' => array(
			'pages' => $nPages,
			'preprocess' => 'consonant_hybrid_split',
			'sk_plain_bytes' => strlen($skText),
			'sk_rows' => (int) ($streams['sk_rows'] ?? 0),
			'skel_candidates' => count($candidates),
			'skel_selected' => count($selected),
			'dict_words' => (int) ($written['words'] ?? 0),
			'dict_bytes' => (int) ($written['bytes'] ?? 0),
		),
		'written' => $written,
	);
}
