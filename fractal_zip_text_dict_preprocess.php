<?php
declare(strict_types=1);

/**
 * Dictionary / WRT-style preprocess on text streams (NNCP/cmix/phda9 lineage).
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_score_gate.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';

/**
 * Mine repeated lines from plain prose (text-stream preprocess).
 *
 * @return list<string>
 */
function fractal_zip_text_mine_lines_from_prose(
	string $prose,
	int $minLen = 10,
	int $maxLen = 96,
	int $minCount = 4,
	int $maxEntries = 64
): array {
	$counts = array();
	foreach (explode("\n", $prose) as $line) {
		$len = strlen($line);
		if ($len < $minLen || $len > $maxLen) {
			continue;
		}
		if (!isset($counts[$line])) {
			$counts[$line] = 0;
		}
		$counts[$line]++;
	}
	$entries = array();
	foreach ($counts as $pat => $c) {
		if ($c >= $minCount) {
			$entries[] = $pat;
		}
	}
	usort($entries, static fn (string $a, string $b): int => ($counts[$b] <=> $counts[$a]) ?: (strlen($b) <=> strlen($a)));
	return array_slice($entries, 0, $maxEntries);
}

/** @return list<string> */
function fractal_zip_text_dict_preprocess_catalog(): array
{
	return array('none', 'dict_nncp', 'dict_phda9', 'wrt_xwrt', 'isp_varint', 'isp_base94', 'corpus_phrases_text', 'word_pack_text', 'textcodec_isp', 'spiral_inner', 'cycle_inner', 'cycle_delta', 'qg_word_root', 'qg_subword_root', 'qg_hybrid_root', 'qg_text_normalize', 'wiki_lom');
}

/**
 * Mine NNCP-style word vocabulary from prose (optionally frozen for later pages).
 *
 * @return list<string>
 */
function fractal_zip_text_dict_nncp_mine_vocab(string $text, array $opts = array()): array
{
	$maxWords = (int) ($opts['max_words'] ?? 0);
	$minLen = (int) ($opts['min_word_len'] ?? 2);
	$useScoreGate = !array_key_exists('score_gate', $opts) || !empty($opts['score_gate']);
	$hardMax = $maxWords > 0 ? $maxWords : fractal_zip_phda9_dict_max_words();
	$counts = array();
	foreach (fractal_zip_enwik_text_segment_implicit_space($text) as $seg) {
		if (($seg['type'] ?? '') !== 'word') {
			continue;
		}
		$tok = (string) ($seg['text'] ?? '');
		if (strlen($tok) < $minLen) {
			continue;
		}
		if (!isset($counts[$tok])) {
			$counts[$tok] = 0;
		}
		$counts[$tok]++;
	}
	$rows = array();
	$ratioThreshold = fractal_zip_score_ratio_threshold();
	foreach ($counts as $tok => $c) {
		$len = strlen($tok);
		$benefit = (float) ($c * max(0, $len - 1));
		$lineCost = (float) max(1, $len + 2);
		if ($useScoreGate && ($benefit / $lineCost) <= $ratioThreshold) {
			continue;
		}
		$rows[] = array(
			'token' => $tok,
			'benefit' => $benefit,
			'eff' => $benefit / $lineCost,
		);
	}
	usort($rows, static function (array $a, array $b): int {
		if ($a['eff'] !== $b['eff']) {
			return $b['eff'] <=> $a['eff'];
		}
		if ($a['benefit'] !== $b['benefit']) {
			return $b['benefit'] <=> $a['benefit'];
		}
		return strlen($b['token']) <=> strlen($a['token']);
	});
	$out = array();
	foreach ($rows as $row) {
		if (count($out) >= $hardMax) {
			break;
		}
		if ($useScoreGate && $row['eff'] <= $ratioThreshold) {
			break;
		}
		$out[] = (string) $row['token'];
	}
	return $out;
}

/**
 * NNCP-style word dictionary: vocab ids for words, literal spans for gaps (lossless v2).
 *
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_text_dict_nncp_preprocess(string $text, array $opts = array()): array
{
	$maxWords = (int) ($opts['max_words'] ?? 0);
	$minLen = (int) ($opts['min_word_len'] ?? 2);
	$vocab = $opts['vocab'] ?? null;
	if (!is_array($vocab) || $vocab === array()) {
		$vocab = fractal_zip_text_dict_nncp_mine_vocab($text, array(
			'max_words' => $maxWords,
			'min_word_len' => $minLen,
		));
	}
	$wordToId = array();
	foreach ($vocab as $i => $w) {
		$wordToId[(string) $w] = (int) $i;
	}
	$parts = array();
	$wordHits = 0;
	$literalBytes = 0;
	foreach (fractal_zip_enwik_text_segment_implicit_space($text) as $seg) {
		$segText = (string) ($seg['text'] ?? '');
		if ($segText === '') {
			continue;
		}
		if (($seg['type'] ?? '') === 'word' && isset($wordToId[$segText])) {
			$parts[] = "\x01" . fractal_zip_enwik_encode_varint_u32((int) $wordToId[$segText]);
			$wordHits++;
		} else {
			$parts[] = "\x02" . fractal_zip_enwik_encode_varint_u32(strlen($segText)) . $segText;
			$literalBytes += strlen($segText);
		}
	}
	$payload = implode('', $parts);
	return array(
		'payload' => $payload,
		'sidecar' => array(
			'preprocess' => 'dict_nncp',
			'codec' => 'segment_v2',
			'vocab' => $vocab,
			'frozen' => !empty($opts['frozen']),
		),
		'meta' => array(
			'vocab_size' => count($vocab),
			'word_hits' => $wordHits,
			'literal_bytes' => $literalBytes,
		),
	);
}

function fractal_zip_text_dict_nncp_undo(string $payload, array $sidecar): string
{
	$vocab = $sidecar['vocab'] ?? array();
	if (!is_array($vocab)) {
		throw new RuntimeException('dict_nncp undo: vocab missing');
	}
	$out = array();
	$off = 0;
	$n = strlen($payload);
	while ($off < $n) {
		$tag = $payload[$off];
		$off++;
		$dv = fractal_zip_enwik_decode_varint_u32($payload, $off);
		if ($dv === null) {
			break;
		}
		if ($tag === "\x01") {
			$id = (int) $dv[0];
			$off = (int) $dv[1];
			$out[] = (string) ($vocab[$id] ?? '');
		} elseif ($tag === "\x02") {
			$len = (int) $dv[0];
			$off = (int) $dv[1];
			if ($len < 0 || $off + $len > $n) {
				throw new RuntimeException('dict_nncp undo: truncated literal');
			}
			$out[] = substr($payload, $off, $len);
			$off += $len;
		} else {
			throw new RuntimeException('dict_nncp undo: unknown tag');
		}
	}
	$codec = (string) ($sidecar['codec'] ?? 'segment_v2');
	if ($codec === 'segment_v2') {
		return implode('', $out);
	}
	return implode(' ', $out);
}

/**
 * phda9-style: run external phda9 preprocess if available, else NNCP dict fallback.
 */
function fractal_zip_text_dict_phda9_preprocess(string $text, array $opts = array()): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
	$exe = fractal_zip_paq_discover_executable('phda9');
	if ($exe !== null) {
		$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_phda9_pre_' . getmypid();
		@mkdir($tmp, 0700, true);
		$in = $tmp . DIRECTORY_SEPARATOR . 'text.in';
		$out = $tmp . DIRECTORY_SEPARATOR . 'text.pre';
		file_put_contents($in, $text);
		$ret = -1;
		$cmd = escapeshellarg($exe) . ' P ' . escapeshellarg(basename($in)) . ' ' . escapeshellarg(basename($out));
		$cwd = getcwd();
		@chdir($tmp);
		exec($cmd . ' 2>/dev/null', $xo, $ret);
		if ($cwd !== false) {
			@chdir($cwd);
		}
		if ($ret === 0 && is_file($out)) {
			$payload = (string) file_get_contents($out);
			fractal_zip_enwik_recursive_remove($tmp);
			return array(
				'payload' => $payload,
				'sidecar' => array('preprocess' => 'dict_phda9', 'tool' => 'phda9'),
				'meta' => array('external' => true),
			);
		}
		fractal_zip_enwik_recursive_remove($tmp);
	}
	$r = fractal_zip_text_dict_nncp_preprocess($text, $opts);
	$r['sidecar']['preprocess'] = 'dict_phda9';
	$r['sidecar']['fallback'] = 'dict_nncp';
	return $r;
}

function fractal_zip_text_dict_phda9_undo(string $payload, array $sidecar): string
{
	if (($sidecar['fallback'] ?? '') === 'dict_nncp') {
		return fractal_zip_text_dict_nncp_undo($payload, $sidecar);
	}
	return $payload;
}

/**
 * Build WRT code map from explicit vocab list (frozen cross-page dictionary).
 *
 * @param list<string> $vocab
 * @return array{map: array<string, int>, codes: array<int, string>}
 */
function fractal_zip_text_wrt_xwrt_build_map_from_vocab(array $vocab, int $maxCodes = 200): array
{
	$map = array();
	$codes = array();
	$code = 1;
	foreach ($vocab as $w) {
		if ($code > 254 || $code > $maxCodes) {
			break;
		}
		$w = (string) $w;
		if ($w === '' || isset($map[$w])) {
			continue;
		}
		$map[$w] = $code;
		$codes[$code] = $w;
		$code++;
	}
	return array('map' => $map, 'codes' => $codes);
}

/** @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>} */
function fractal_zip_text_wrt_xwrt_apply_with_codes(string $text, array $codes): array
{
	$map = array();
	foreach ($codes as $c => $w) {
		if (is_string($w) && $w !== '') {
			$map[$w] = (int) $c;
		}
	}
	$payload = preg_replace_callback(
		"/[A-Za-z][A-Za-z0-9_\x27]*/",
		static function (array $m) use ($map): string {
			$w = $m[0];
			if (isset($map[$w])) {
				return chr((int) $map[$w]);
			}
			return "\xFF" . $w . "\xFE";
		},
		$text
	);
	if (!is_string($payload)) {
		$payload = $text;
	}
	return array(
		'payload' => $payload,
		'sidecar' => array('preprocess' => 'wrt_xwrt', 'codes' => $codes),
		'meta' => array('coded_words' => count($codes)),
	);
}

/** xwrt/WRT: replace frequent words with single-byte codes (reversible). */
function fractal_zip_text_wrt_xwrt_preprocess(string $text, array $opts = array()): array
{
	$maxCodes = (int) ($opts['max_codes'] ?? 200);
	if (isset($opts['codes']) && is_array($opts['codes']) && $opts['codes'] !== array()) {
		return fractal_zip_text_wrt_xwrt_apply_with_codes($text, $opts['codes']);
	}
	if (isset($opts['vocab']) && is_array($opts['vocab']) && $opts['vocab'] !== array()) {
		$built = fractal_zip_text_wrt_xwrt_build_map_from_vocab($opts['vocab'], $maxCodes);
		return fractal_zip_text_wrt_xwrt_apply_with_codes($text, $built['codes']);
	}
	$tokens = fractal_zip_enwik_text_tokenize_words($text);
	$counts = array();
	foreach ($tokens as $tok) {
		if (!isset($counts[$tok])) {
			$counts[$tok] = 0;
		}
		$counts[$tok]++;
	}
	arsort($counts, SORT_NUMERIC);
	$vocab = array_slice(array_keys($counts), 0, max(1, $maxCodes));
	$built = fractal_zip_text_wrt_xwrt_build_map_from_vocab($vocab, $maxCodes);
	$map = $built['map'];
	$codes = $built['codes'];
	$payload = preg_replace_callback(
		"/[A-Za-z][A-Za-z0-9_\x27]*/",
		static function (array $m) use ($map): string {
			$w = $m[0];
			if (isset($map[$w])) {
				return chr((int) $map[$w]);
			}
			return "\xFF" . $w . "\xFE";
		},
		$text
	);
	if (!is_string($payload)) {
		$payload = $text;
	}
	return array(
		'payload' => $payload,
		'sidecar' => array('preprocess' => 'wrt_xwrt', 'codes' => $codes),
		'meta' => array('coded_words' => count($codes)),
	);
}

function fractal_zip_text_wrt_xwrt_undo(string $payload, array $sidecar): string
{
	$codes = $sidecar['codes'] ?? array();
	if (!is_array($codes)) {
		return $payload;
	}
	$out = '';
	$n = strlen($payload);
	for ($i = 0; $i < $n; $i++) {
		$c = ord($payload[$i]);
		if ($c === 0xFF) {
			$end = strpos($payload, "\xFE", $i + 1);
			if ($end === false) {
				$out .= $payload[$i];
				continue;
			}
			$out .= substr($payload, $i + 1, $end - $i - 1);
			$i = $end;
			continue;
		}
		$out .= (string) ($codes[$c] ?? chr($c));
	}
	return $out;
}

/**
 * Quantum-grammar word-root codec (lossless gap-preserving v2).
 *
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_text_qg_word_root_preprocess(string $text, array $opts = array()): array
{
	require_once '/srv/http/quantum_grammar/src/WordRootCodec.php';
	$model = $opts['qg_model'] ?? null;
	if (!is_array($model) || !isset($model['vocab'], $model['index'])) {
		$model = WordRootCodec::mineVocab($text);
	}
	$frozen = !empty($opts['frozen']);
	$enc = WordRootCodec::encode($text, $model, $frozen);
	$sidecar = $enc['sidecar'];
	if ($frozen && is_string($opts['vocab_path'] ?? null)) {
		$sidecar['vocab_path'] = (string) $opts['vocab_path'];
	} elseif (!$frozen) {
		$sidecar['vocab'] = $model['vocab'];
	}
	return array(
		'payload' => (string) $enc['payload'],
		'sidecar' => $sidecar,
		'meta' => $enc['meta'] ?? array(),
	);
}

/** @param array<string,mixed> $sidecar */
function fractal_zip_text_qg_word_root_undo(string $payload, array $sidecar): string
{
	require_once '/srv/http/quantum_grammar/src/WordRootCodec.php';
	$vocab = $sidecar['vocab'] ?? null;
	if (!is_array($vocab) && is_string($sidecar['vocab_path'] ?? null) && is_file((string) $sidecar['vocab_path'])) {
		$raw = json_decode((string) file_get_contents((string) $sidecar['vocab_path']), true);
		$vocab = is_array($raw) ? $raw : null;
	}
	if (!is_array($vocab)) {
		throw new RuntimeException('qg_word_root: vocab missing');
	}
	return WordRootCodec::decode($payload, array_merge($sidecar, array('vocab' => $vocab)));
}

/**
 * Sub-word quantum-grammar root codec (lossless gap-preserving).
 *
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_text_qg_subword_root_preprocess(string $text, array $opts = array()): array
{
	require_once '/srv/http/quantum_grammar/src/SubWordRootCodec.php';
	$model = $opts['qg_model'] ?? null;
	if (!is_array($model) || !isset($model['vocab'], $model['index'])) {
		$model = SubWordRootCodec::mineVocab($text);
	}
	$frozen = !empty($opts['frozen']);
	$enc = SubWordRootCodec::encode($text, $model, $frozen);
	$sidecar = $enc['sidecar'];
	if ($frozen && is_string($opts['vocab_path'] ?? null)) {
		$sidecar['vocab_path'] = (string) $opts['vocab_path'];
	} elseif (!$frozen) {
		$sidecar['vocab'] = $model['vocab'];
	}
	return array(
		'payload' => (string) $enc['payload'],
		'sidecar' => $sidecar,
		'meta' => $enc['meta'] ?? array(),
	);
}

/** @param array<string,mixed> $sidecar */
function fractal_zip_text_qg_subword_root_undo(string $payload, array $sidecar): string
{
	require_once '/srv/http/quantum_grammar/src/SubWordRootCodec.php';
	$vocab = $sidecar['vocab'] ?? null;
	if (!is_array($vocab) && is_string($sidecar['vocab_path'] ?? null) && is_file((string) $sidecar['vocab_path'])) {
		$raw = json_decode((string) file_get_contents((string) $sidecar['vocab_path']), true);
		$vocab = is_array($raw) ? $raw : null;
	}
	if (!is_array($vocab)) {
		throw new RuntimeException('qg_subword_root: vocab missing');
	}
	return SubWordRootCodec::decode($payload, array_merge($sidecar, array('vocab' => $vocab)));
}

/**
 * Hybrid quantum-grammar root codec (word + subword + hyphen chain picker).
 *
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_text_qg_hybrid_root_preprocess(string $text, array $opts = array()): array
{
	require_once '/srv/http/quantum_grammar/src/HybridRootCodec.php';
	$model = $opts['qg_model'] ?? null;
	if (!is_array($model) || !isset($model['word'], $model['sub'])) {
		$model = HybridRootCodec::mineModel($text);
	}
	$frozen = !empty($opts['frozen']);
	$enc = HybridRootCodec::encode($text, $model, $frozen);
	$sidecar = $enc['sidecar'];
	if ($frozen && is_string($opts['vocab_path'] ?? null)) {
		$sidecar['vocab_path'] = (string) $opts['vocab_path'];
	} elseif (!$frozen) {
		$sidecar['word_vocab'] = $model['word']['vocab'];
		$sidecar['sub_vocab'] = $model['sub']['vocab'];
	}
	return array(
		'payload' => (string) $enc['payload'],
		'sidecar' => $sidecar,
		'meta' => $enc['meta'] ?? array(),
	);
}

/** @param array<string,mixed> $sidecar */
function fractal_zip_text_qg_hybrid_root_undo(string $payload, array $sidecar): string
{
	require_once '/srv/http/quantum_grammar/src/HybridRootCodec.php';
	$wordVocab = $sidecar['word_vocab'] ?? null;
	$subVocab = $sidecar['sub_vocab'] ?? null;
	if ((!is_array($wordVocab) || !is_array($subVocab)) && is_string($sidecar['vocab_path'] ?? null) && is_file((string) $sidecar['vocab_path'])) {
		$raw = json_decode((string) file_get_contents((string) $sidecar['vocab_path']), true);
		if (is_array($raw)) {
			$wordVocab = $raw['word'] ?? null;
			$subVocab = $raw['sub'] ?? null;
		}
	}
	if (!is_array($wordVocab) || !is_array($subVocab)) {
		throw new RuntimeException('qg_hybrid_root: vocab missing');
	}
	return HybridRootCodec::decode($payload, array_merge($sidecar, array(
		'word_vocab' => $wordVocab,
		'sub_vocab' => $subVocab,
	)));
}

/**
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_text_qg_text_normalize_preprocess(string $text, array $opts = array()): array
{
	require_once '/srv/http/quantum_grammar/src/QgTextNormalize.php';
	$normOpts = array(
		'hyphen_join' => !array_key_exists('hyphen_join', $opts) || !empty($opts['hyphen_join']),
		'double_consonant' => !array_key_exists('double_consonant', $opts) || !empty($opts['double_consonant']),
		'corruption_correct' => !empty($opts['corruption_correct']),
	);
	return QgTextNormalize::normalize($text, $normOpts);
}

/** @param array<string,mixed> $sidecar */
function fractal_zip_text_qg_text_normalize_undo(string $payload, array $sidecar): string
{
	require_once '/srv/http/quantum_grammar/src/QgTextNormalize.php';
	return QgTextNormalize::denormalize($payload, $sidecar);
}

/**
 * Apply preprocess id to raw text.
 *
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_text_preprocess_apply(string $preprocessId, string $text, array $opts = array()): array
{
	$preprocessId = strtolower(trim($preprocessId));
	switch ($preprocessId) {
		case 'none':
			return array('payload' => $text, 'sidecar' => array('preprocess' => 'none'), 'meta' => array());
		case 'dict_nncp':
			return fractal_zip_text_dict_nncp_preprocess($text, $opts);
		case 'dict_inner':
			$r = fractal_zip_text_dict_nncp_preprocess($text, $opts);
			$r['sidecar']['preprocess'] = 'dict_inner';
			return $r;
		case 'dict_phda9':
			return fractal_zip_text_dict_phda9_preprocess($text, $opts);
		case 'wrt_xwrt':
		case 'stat_wrt':
			return fractal_zip_text_wrt_xwrt_preprocess($text, $opts);
		case 'stat_isp':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
			return fractal_zip_text_stat_isp_preprocess($text, $opts);
		case 'stat_pred':
		case 'stat_pred_compact_meta':
		case 'stat_pred_inner':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
			return fractal_zip_text_stat_pred_preprocess($text, $opts);
		case 'isp_varint':
			$enc = fractal_zip_enwik_text_codec_encode('words_id_varint_isp', $text, $opts);
			return array('payload' => (string) $enc['payload'], 'sidecar' => array_merge($enc['sidecar'], array('preprocess' => 'isp_varint', 'scheme' => 'words_id_varint_isp')), 'meta' => $enc['meta'] ?? array());
		case 'isp_base94':
			$enc = fractal_zip_enwik_text_codec_encode('words_base94_isp', $text, $opts);
			return array('payload' => (string) $enc['payload'], 'sidecar' => array_merge($enc['sidecar'], array('preprocess' => 'isp_base94', 'scheme' => 'words_base94_isp')), 'meta' => $enc['meta'] ?? array());
		case 'corpus_phrases_text':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
			$phrases = fractal_zip_text_mine_lines_from_prose($text, 10, 96, 4, 64);
			$shared = array();
			$packed = fractal_zip_enwik_phrase_pack_apply($text, $phrases, $shared);
			return array('payload' => (string) $packed['blob'], 'sidecar' => array('preprocess' => 'corpus_phrases_text', 'phrases' => $phrases), 'meta' => array('phrase_count' => count($phrases)));
		case 'word_pack_text':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
			$words = fractal_zip_enwik_mine_article_word_phrases($text, 3, 24, 8, 128);
			$shared = array();
			$packed = fractal_zip_enwik_phrase_pack_apply($text, $words, $shared);
			return array('payload' => (string) $packed['blob'], 'sidecar' => array('preprocess' => 'word_pack_text', 'words' => $words), 'meta' => array('word_count' => count($words)));
		case 'textcodec_isp':
			$scheme = (string) ($opts['scheme'] ?? 'words_id_varint_isp');
			$transform = (string) ($opts['transform'] ?? 'none');
			$enc = fractal_zip_enwik_text_codec_encode($scheme, $text, $opts);
			$t = fractal_zip_enwik_text_transform_apply($transform, (string) $enc['payload'], array('seed' => (int) ($opts['seed'] ?? 1)));
			return array(
				'payload' => (string) $t['payload'],
				'sidecar' => array_merge($enc['sidecar'], $t['sidecar'], array('preprocess' => 'textcodec_isp', 'scheme' => $scheme, 'transform' => $transform)),
				'meta' => $enc['meta'] ?? array(),
			);
		case 'spiral_inner':
			putenv('FRACTAL_ZIP_SPIRAL_INNER=1');
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
			$r = fractal_zip_enwik_spiral_inner_preprocess($text);
			if ($r === null) {
				throw new RuntimeException('spiral_inner unavailable');
			}
			return $r;
		case 'cycle_inner':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_cycle_preprocess.php';
			return fractal_zip_text_cycle_preprocess($text, $opts);
		case 'cycle_delta':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_cycle_preprocess.php';
			return fractal_zip_text_cycle_preprocess($text, array_merge($opts, array('transform' => 'delta')));
		case 'qg_word_root':
		case 'word_root':
			return fractal_zip_text_qg_word_root_preprocess($text, $opts);
		case 'qg_subword_root':
		case 'subword_root':
			return fractal_zip_text_qg_subword_root_preprocess($text, $opts);
		case 'qg_hybrid_root':
		case 'hybrid_root':
			return fractal_zip_text_qg_hybrid_root_preprocess($text, $opts);
		case 'qg_text_normalize':
		case 'text_normalize':
			return fractal_zip_text_qg_text_normalize_preprocess($text, $opts);
		case 'syllable_tokens':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			return fractal_zip_enwik_syllable_token_preprocess($text, $opts);
		case 'consonant_ec':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			return fractal_zip_enwik_consonant_ec_preprocess($text, $opts);
		case 'consonant_hybrid':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			$model = is_array($opts['consonant_model'] ?? null) ? $opts['consonant_model'] : null;
			if ($model === null && is_array($opts['skeleton_vocab'] ?? null)) {
				$model = fractal_zip_enwik_consonant_hybrid_shared_meta_from_model($opts);
			}
			return fractal_zip_enwik_consonant_hybrid_preprocess($text, array_merge($opts, array(
				'consonant_model' => $model,
			)));
		case 'consonant_hybrid_split':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			$model = is_array($opts['consonant_model'] ?? null) ? $opts['consonant_model'] : null;
			if ($model === null) {
				$model = fractal_zip_enwik_consonant_hybrid_mine_model($text);
			}
			return fractal_zip_enwik_consonant_hybrid_split_preprocess($text, $model, $opts);
		case 'consonant_hybrid_lossy':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			return fractal_zip_enwik_consonant_hybrid_lossy_preprocess($text, $opts);
		case 'stat_syllable_isp':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			return fractal_zip_text_stat_syllable_isp_preprocess($text, $opts);
		case 'wiki_lom':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
			return fractal_zip_wiki_lom_preprocess($text, $opts);
		default:
			throw new InvalidArgumentException('unknown preprocess: ' . $preprocessId);
	}
}

function fractal_zip_text_preprocess_undo(string $preprocessId, string $payload, array $sidecar, string $originalText = ''): string
{
	$preprocessId = strtolower(trim((string) ($sidecar['preprocess'] ?? $preprocessId)));
	switch ($preprocessId) {
		case 'none':
			return $payload;
		case 'dict_nncp':
			return fractal_zip_text_dict_nncp_undo($payload, $sidecar);
		case 'dict_inner':
		case 'dict_phda9_inner':
			return fractal_zip_text_dict_nncp_undo($payload, $sidecar);
		case 'dict_phda9':
			return fractal_zip_text_dict_phda9_undo($payload, $sidecar);
		case 'wrt_xwrt':
		case 'stat_wrt':
			return fractal_zip_text_wrt_xwrt_undo($payload, $sidecar);
		case 'stat_isp':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
			return fractal_zip_text_stat_isp_undo($payload, $sidecar);
		case 'stat_pred':
		case 'stat_pred_compact_meta':
		case 'stat_pred_inner':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
			return fractal_zip_text_stat_pred_undo($payload, $sidecar);
		case 'isp_varint':
		case 'isp_base94':
			$scheme = (string) ($sidecar['scheme'] ?? 'words_id_varint_isp');
			return fractal_zip_enwik_text_codec_decode($scheme, $payload, $sidecar);
		case 'textcodec_isp':
			$scheme = (string) ($sidecar['scheme'] ?? 'words_id_varint_isp');
			$transform = (string) ($sidecar['transform'] ?? 'none');
			$undo = fractal_zip_enwik_text_transform_undo($transform, $payload, $sidecar);
			return fractal_zip_enwik_text_codec_decode($scheme, $undo, $sidecar);
		case 'corpus_phrases_text':
		case 'word_pack_text':
			return $payload;
		case 'spiral_inner':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
			return fractal_zip_enwik_spiral_inner_restore($payload, $sidecar);
		case 'cycle_inner':
		case 'cycle_delta':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_cycle_preprocess.php';
			return fractal_zip_text_cycle_undo($payload, $sidecar);
		case 'cycle_combo':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_cycle_preprocess.php';
			return fractal_zip_text_cycle_combo_undo($payload, $sidecar);
		case 'qg_word_root':
		case 'word_root':
			return fractal_zip_text_qg_word_root_undo($payload, $sidecar);
		case 'qg_subword_root':
		case 'subword_root':
			return fractal_zip_text_qg_subword_root_undo($payload, $sidecar);
		case 'qg_hybrid_root':
		case 'hybrid_root':
			return fractal_zip_text_qg_hybrid_root_undo($payload, $sidecar);
		case 'qg_text_normalize':
		case 'text_normalize':
			return fractal_zip_text_qg_text_normalize_undo($payload, $sidecar);
		case 'syllable_tokens':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			return fractal_zip_enwik_syllable_token_undo($payload, $sidecar);
		case 'consonant_ec':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			return fractal_zip_enwik_consonant_ec_undo($payload, $sidecar);
		case 'consonant_hybrid':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			return fractal_zip_enwik_consonant_hybrid_undo($payload, $sidecar);
		case 'consonant_hybrid_split':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			return fractal_zip_enwik_consonant_hybrid_split_undo($payload, $sidecar);
		case 'consonant_hybrid_lossy':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			return fractal_zip_enwik_consonant_hybrid_lossy_undo($payload, $sidecar);
		case 'stat_syllable_isp':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			return fractal_zip_text_stat_syllable_isp_undo($payload, $sidecar);
		case 'wiki_lom':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
			return fractal_zip_wiki_lom_undo($payload, $sidecar);
		default:
			return $payload;
	}
}
