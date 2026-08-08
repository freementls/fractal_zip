<?php

declare(strict_types=1);

/**
 * Bioinformatics primitives for fractal_zip: deterministic alignment, clustering,
 * variation encoding, and graph representations — all scored/culled like multidiff.
 *
 * Concepts implemented (maps to genomics tooling):
 *  - MinHash sketches (Mash) + minimizers (minimap2)
 *  - CD-HIT greedy clustering, UPGMA guide tree
 *  - Suffix array + maximal exact matches (MUMmer MEMs)
 *  - Spaced seeds (PatternHunter)
 *  - Colinear anchor chaining (minimap2 chaining)
 *  - Banded Needleman–Wunsch with affine gaps
 *  - VCF-style structured variants
 *  - Partial-order / progressive consensus (POA)
 *  - Synteny / block rearrangement detection
 *  - Simplified pangenome variation graph (vg-style paths)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

const FRACTAL_ZIP_BIO_MAGIC = "FZBI\x01";

// ---------------------------------------------------------------------------
// Deterministic hashing (stable across runs/platforms for reproducible wire)
// ---------------------------------------------------------------------------

function fractal_zip_bio_hash32(string $s, int $seed = 0): int
{
	$h = (int) (2166136261 ^ ($seed & 0xFFFFFFFF));
	$n = strlen($s);
	for ($i = 0; $i < $n; $i++) {
		$h ^= ord($s[$i]);
		$h = (int) (($h * 16777619) & 0xFFFFFFFF);
	}
	return $h & 0x7FFFFFFF;
}

function fractal_zip_bio_kmers(string $text, int $k): array
{
	$out = array();
	$len = strlen($text);
	if ($len < $k || $k < 1) {
		return $out;
	}
	for ($i = 0; $i <= $len - $k; $i++) {
		$km = substr($text, $i, $k);
		$out[] = array('kmer' => $km, 'pos' => $i);
	}
	return $out;
}

// ---------------------------------------------------------------------------
// MinHash (Mash-style Jaccard sketch)
// ---------------------------------------------------------------------------

/**
 * @return list<int> minhash signature length $numHashes
 */
function fractal_zip_bio_minhash_sketch(string $text, int $k = 5, int $numHashes = 64): array
{
	$sig = array_fill(0, $numHashes, PHP_INT_MAX);
	$len = strlen($text);
	if ($len < $k) {
		return array_fill(0, $numHashes, fractal_zip_bio_hash32($text));
	}
	for ($i = 0; $i <= $len - $k; $i++) {
		$km = substr($text, $i, $k);
		for ($h = 0; $h < $numHashes; $h++) {
			$hv = fractal_zip_bio_hash32($km, 1000003 + $h * 97);
			if ($hv < $sig[$h]) {
				$sig[$h] = $hv;
			}
		}
	}
	foreach ($sig as $i => $v) {
		if ($v === PHP_INT_MAX) {
			$sig[$i] = 0;
		}
	}
	return $sig;
}

function fractal_zip_bio_minhash_jaccard(array $sigA, array $sigB): float
{
	$n = min(count($sigA), count($sigB));
	if ($n === 0) {
		return 0.0;
	}
	$eq = 0;
	for ($i = 0; $i < $n; $i++) {
		if ($sigA[$i] === $sigB[$i]) {
			$eq++;
		}
	}
	return $eq / $n;
}

// ---------------------------------------------------------------------------
// Minimizers (minimap2-style sparse anchors)
// ---------------------------------------------------------------------------

/**
 * @return list<array{pos:int, kmer:string, hash:int}>
 */
function fractal_zip_bio_minimizers(string $text, int $k = 5, int $w = 10): array
{
	$len = strlen($text);
	if ($len < $k) {
		return array();
	}
	$out = array();
	$lastPos = -1;
	for ($i = 0; $i <= $len - $k; $i++) {
		$winEnd = min($len - $k, $i + $w - 1);
		$bestH = PHP_INT_MAX;
		$bestPos = $i;
		$bestKm = '';
		for ($j = $i; $j <= $winEnd; $j++) {
			$km = substr($text, $j, $k);
			$h = fractal_zip_bio_hash32($km, 424242);
			if ($h < $bestH) {
				$bestH = $h;
				$bestPos = $j;
				$bestKm = $km;
			}
		}
		if ($bestPos !== $lastPos) {
			$out[] = array('pos' => $bestPos, 'kmer' => $bestKm, 'hash' => $bestH);
			$lastPos = $bestPos;
		}
	}
	return $out;
}

// ---------------------------------------------------------------------------
// CD-HIT greedy clustering
// ---------------------------------------------------------------------------

/**
 * @param list<array{id:int, text:string}> $items
 * @return list<list<int>> clusters of item ids
 */
function fractal_zip_bio_cdhit_cluster(array $items, float $threshold = 0.8, int $k = 5): array
{
	if ($items === array()) {
		return array();
	}
	usort($items, static fn (array $a, array $b): int => strlen((string) $b['text']) <=> strlen((string) $a['text']));
	$sketches = array();
	$textLens = array();
	foreach ($items as $it) {
		$id = (int) $it['id'];
		$sketches[$id] = fractal_zip_bio_minhash_sketch((string) $it['text'], $k);
		$textLens[$id] = strlen((string) $it['text']);
	}
	$clusters = array();
	$repSketch = array();
	$repLen = array();
	foreach ($items as $it) {
		$id = (int) $it['id'];
		$placed = false;
		foreach ($clusters as $ci => $cl) {
			$rep = $cl[0];
			$sim = fractal_zip_bio_minhash_jaccard($sketches[$id], $repSketch[$rep]);
			$lenA = $textLens[$id];
			$lenB = $repLen[$rep];
			$lenRatio = min($lenA, $lenB) / max($lenA, $lenB, 1);
			if ($sim >= $threshold && $lenRatio >= 0.75) {
				$clusters[$ci][] = $id;
				$placed = true;
				break;
			}
		}
		if (!$placed) {
			$clusters[] = array($id);
			$repSketch[$id] = $sketches[$id];
			$repLen[$id] = $textLens[$id];
		}
	}
	return $clusters;
}

// ---------------------------------------------------------------------------
// UPGMA guide tree
// ---------------------------------------------------------------------------

/**
 * @param list<array{id:int, text:string}> $items
 * @return array{order:list<int>, tree:list<array{left:int,right:int,dist:float}>}
 */
function fractal_zip_bio_upgma_guide_tree(array $items): array
{
	$n = count($items);
	if ($n <= 1) {
		$ids = array_map(static fn (array $x): int => (int) $x['id'], $items);
		return array('order' => $ids, 'tree' => array());
	}
	$ids = array();
	$texts = array();
	$sketches = array();
	foreach ($items as $it) {
		$id = (int) $it['id'];
		$ids[] = $id;
		$texts[$id] = (string) $it['text'];
		$sketches[$id] = fractal_zip_bio_minhash_sketch($texts[$id]);
	}
	$dist = array();
	for ($i = 0; $i < $n; $i++) {
		for ($j = $i + 1; $j < $n; $j++) {
			$a = $ids[$i];
			$b = $ids[$j];
			$sim = fractal_zip_bio_minhash_jaccard($sketches[$a], $sketches[$b]);
			$dist[$a][$b] = 1.0 - $sim;
			$dist[$b][$a] = 1.0 - $sim;
		}
	}
	$active = array_fill_keys($ids, true);
	$members = array();
	$size = array();
	foreach ($ids as $id) {
		$members[$id] = array($id);
		$size[$id] = 1;
	}
	$tree = array();
	$nextCluster = $n;
	$idToIdx = array();
	foreach ($ids as $i => $id) {
		$idToIdx[$id] = $i;
	}
	while (count($active) > 1) {
		$bestA = null;
		$bestB = null;
		$bestD = INF;
		foreach ($active as $a => $_) {
			foreach ($active as $b => $_) {
				if ($a >= $b) {
					continue;
				}
				$d = $dist[$a][$b] ?? 1.0;
				if ($d < $bestD) {
					$bestD = $d;
					$bestA = $a;
					$bestB = $b;
				}
			}
		}
		if ($bestA === null || $bestB === null) {
			break;
		}
		$newId = $nextCluster++;
		$tree[] = array('left' => $bestA, 'right' => $bestB, 'dist' => $bestD, 'id' => $newId);
		$members[$newId] = array_merge($members[$bestA], $members[$bestB]);
		$size[$newId] = $size[$bestA] + $size[$bestB];
		unset($active[$bestA], $active[$bestB]);
		$active[$newId] = true;
		foreach ($active as $c => $_) {
			if ($c === $newId) {
				continue;
			}
			$da = $dist[$bestA][$c] ?? 1.0;
			$db = $dist[$bestB][$c] ?? 1.0;
			$sa = $size[$bestA];
			$sb = $size[$bestB];
			$sc = $size[$c];
			$merged = ($da * $sa + $db * $sb) / ($sa + $sb);
			$dist[$newId][$c] = $merged;
			$dist[$c][$newId] = $merged;
		}
		unset($dist[$bestA], $dist[$bestB]);
	}
	$root = array_key_first($active);
	$order = $members[$root] ?? $ids;
	return array('order' => $order, 'tree' => $tree);
}

// ---------------------------------------------------------------------------
// Suffix array + MEMs (MUMmer-style maximal exact matches)
// ---------------------------------------------------------------------------

/**
 * @return list<int> suffix array (starting indices sorted lexicographically)
 */
function fractal_zip_bio_suffix_array(string $text, int $maxLen = 65536): array
{
	$n = strlen($text);
	if ($n > $maxLen) {
		$text = substr($text, 0, $maxLen);
		$n = $maxLen;
	}
	$sa = range(0, max(0, $n - 1));
	usort($sa, static function (int $a, int $b) use ($text, $n): int {
		$la = substr($text, $a);
		$lb = substr($text, $b);
		return strcmp($la, $lb);
	});
	return $sa;
}

function fractal_zip_bio_lcp_extend(string $ref, int $ra, int $qa, int $qLen): int
{
	$rLen = strlen($ref);
	$len = 0;
	while ($ra + $len < $rLen && $qa + $len < $qLen
		&& $ref[$ra + $len] === $ref[$qa + $len]) {
		$len++;
	}
	return $len;
}

/**
 * Find MEMs between $ref and $query (query searched in ref+query concatenation trick).
 *
 * @return list<array{ref_pos:int, query_pos:int, len:int}>
 */
function fractal_zip_bio_mems(string $ref, string $query, int $minLen = 8): array
{
	if ($ref === '' || $query === '') {
		return array();
	}
	$sep = "\x00";
	$combined = $ref . $sep . $query;
	$rLen = strlen($ref);
	$sa = fractal_zip_bio_suffix_array($combined, 131072);
	$mems = array();
	$qOff = $rLen + 1;
	for ($i = 0; $i < count($sa) - 1; $i++) {
		$a = $sa[$i];
		$b = $sa[$i + 1];
		$inRefA = $a < $rLen;
		$inRefB = $b < $rLen;
		if ($inRefA === $inRefB) {
			continue;
		}
		$rp = $inRefA ? $a : $b;
		$qp = ($inRefA ? $b : $a) - $qOff;
		if ($qp < 0 || $qp >= strlen($query)) {
			continue;
		}
		$l = 0;
		while ($rp + $l < $rLen && $qp + $l < strlen($query)
			&& $ref[$rp + $l] === $query[$qp + $l]) {
			$l++;
		}
		if ($l >= $minLen) {
			$mems[] = array('ref_pos' => $rp, 'query_pos' => $qp, 'len' => $l);
		}
	}
	usort($mems, static fn (array $x, array $y): int => ($x['ref_pos'] <=> $y['ref_pos']) ?: ($x['query_pos'] <=> $y['query_pos']));
	$filtered = array();
	$lastEnd = -1;
	foreach ($mems as $m) {
		$end = $m['ref_pos'] + $m['len'];
		if ($m['ref_pos'] >= $lastEnd) {
			$filtered[] = $m;
			$lastEnd = $end;
		}
	}
	return $filtered;
}

// ---------------------------------------------------------------------------
// Spaced seeds (PatternHunter)
// ---------------------------------------------------------------------------

/**
 * @param string $pattern e.g. "11*1**11" where 1=match * =wildcard
 * @return list<array{pos:int, seed:string}>
 */
function fractal_zip_bio_spaced_seed_hits(string $text, string $pattern, int $seedLen = 0): array
{
	$pat = str_split($pattern);
	$fixedPos = array();
	$fixedChars = array();
	$span = count($pat);
	if ($seedLen <= 0) {
		$seedLen = $span;
	}
	$hits = array();
	$len = strlen($text);
	if ($len < $seedLen) {
		return $hits;
	}
	for ($i = 0; $i <= $len - $seedLen; $i++) {
		$ok = true;
		$seed = '';
		for ($p = 0; $p < $span; $p++) {
			$ch = $text[$i + $p] ?? '';
			if ($pat[$p] === '1') {
				$seed .= $ch;
			} elseif ($pat[$p] !== '*') {
				$ok = false;
				break;
			}
		}
		if ($ok) {
			$hits[] = array('pos' => $i, 'seed' => $seed);
		}
	}
	return $hits;
}

/**
 * Cross-string spaced seed anchors.
 *
 * @return list<array{ref_pos:int, query_pos:int}>
 */
function fractal_zip_bio_spaced_seed_pair_anchors(string $ref, string $query, string $pattern = '11*1**11'): array
{
	$refHits = fractal_zip_bio_spaced_seed_hits($ref, $pattern);
	$qMap = array();
	foreach (fractal_zip_bio_spaced_seed_hits($query, $pattern) as $h) {
		$qMap[$h['seed']][] = (int) $h['pos'];
	}
	$anchors = array();
	foreach ($refHits as $rh) {
		$seed = (string) $rh['seed'];
		if ($seed === '' || !isset($qMap[$seed])) {
			continue;
		}
		foreach ($qMap[$seed] as $qp) {
			$anchors[] = array('ref_pos' => (int) $rh['pos'], 'query_pos' => $qp);
		}
	}
	return $anchors;
}

// ---------------------------------------------------------------------------
// Colinear anchor chaining (minimap2-style)
// ---------------------------------------------------------------------------

/**
 * Chain anchors into colinear segments with consistent shift.
 *
 * @param list<array{ref_pos:int, query_pos:int, len?:int}> $anchors
 * @return list<array{anchors:list<array>, shift:int, ref_start:int, ref_end:int, query_start:int, query_end:int}>
 */
function fractal_zip_bio_colinear_chain(array $anchors, int $maxShiftDev = 8): array
{
	if ($anchors === array()) {
		return array();
	}
	usort($anchors, static fn (array $a, array $b): int => ($a['ref_pos'] <=> $b['ref_pos']) ?: ($a['query_pos'] <=> $b['query_pos']));
	$n = count($anchors);
	$dp = array_fill(0, $n, 1);
	$prev = array_fill(0, $n, -1);
	for ($i = 1; $i < $n; $i++) {
		for ($j = 0; $j < $i; $j++) {
			if ($anchors[$i]['ref_pos'] <= $anchors[$j]['ref_pos']) {
				continue;
			}
			$shiftI = $anchors[$i]['ref_pos'] - $anchors[$i]['query_pos'];
			$shiftJ = $anchors[$j]['ref_pos'] - $anchors[$j]['query_pos'];
			if (abs($shiftI - $shiftJ) > $maxShiftDev) {
				continue;
			}
			if ($dp[$j] + 1 > $dp[$i]) {
				$dp[$i] = $dp[$j] + 1;
				$prev[$i] = $j;
			}
		}
	}
	$bestI = 0;
	for ($i = 1; $i < $n; $i++) {
		if ($dp[$i] > $dp[$bestI]) {
			$bestI = $i;
		}
	}
	$chains = array();
	$used = array_fill(0, $n, false);
	while ($bestI >= 0 && $dp[$bestI] > 0) {
		if ($used[$bestI]) {
			break;
		}
		$chainAnchors = array();
		$cur = $bestI;
		while ($cur >= 0) {
			$chainAnchors[] = $anchors[$cur];
			$used[$cur] = true;
			$cur = $prev[$cur];
		}
		$chainAnchors = array_reverse($chainAnchors);
		$shifts = array();
		foreach ($chainAnchors as $a) {
			$shifts[] = (int) $a['ref_pos'] - (int) $a['query_pos'];
		}
		sort($shifts);
		$shift = $shifts[(int) (count($shifts) / 2)];
		$first = $chainAnchors[0];
		$last = $chainAnchors[count($chainAnchors) - 1];
		$chains[] = array(
			'anchors' => $chainAnchors,
			'shift' => $shift,
			'ref_start' => (int) $first['ref_pos'],
			'ref_end' => (int) $last['ref_pos'] + (int) ($last['len'] ?? 1),
			'query_start' => (int) $first['query_pos'],
			'query_end' => (int) $last['query_pos'] + (int) ($last['len'] ?? 1),
		);
		$bestI = -1;
		$bestScore = 0;
		for ($i = 0; $i < $n; $i++) {
			if (!$used[$i] && $dp[$i] > $bestScore) {
				$bestScore = $dp[$i];
				$bestI = $i;
			}
		}
	}
	return $chains;
}

/**
 * Whether colinear self-chain seeds augment multidiff candidate discovery.
 */
function fractal_zip_bio_multidiff_chain_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_BIO_MULTIDIFF_CHAIN');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'on' || $v === 'true' || $v === 'yes';
}

/**
 * estLin multiplier for newly merged chain seeds (lower = stronger top-k preference).
 */
function fractal_zip_bio_multidiff_chain_score_scale(): float
{
	$e = getenv('FRACTAL_ZIP_BIO_MULTIDIFF_CHAIN_SCORE_SCALE');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0.01, min(1.0, (float) $e));
	}
	return 1.0;
}

/**
 * Pair spans plus full colinear chain span for one chain record.
 *
 * @return list<array{rs:int,re:int}>
 */
function fractal_zip_bio_multidiff_chain_colinear_spans(array $ch): array
{
	$chainAnchors = $ch['anchors'] ?? array();
	$spans = array();
	if (count($chainAnchors) >= 2) {
		for ($ai = 0; $ai < count($chainAnchors) - 1; $ai++) {
			$a0 = $chainAnchors[$ai];
			$a1 = $chainAnchors[$ai + 1];
			$spans[] = array(
				'rs' => (int) $a0['ref_pos'],
				're' => (int) $a1['ref_pos'] + (int) ($a1['len'] ?? 1),
			);
		}
		$first = $chainAnchors[0];
		$last = $chainAnchors[count($chainAnchors) - 1];
		$spans[] = array(
			'rs' => (int) $first['ref_pos'],
			're' => (int) $last['ref_pos'] + (int) ($last['len'] ?? 1),
		);
	} else {
		$spans[] = array('rs' => (int) $ch['ref_start'], 're' => (int) $ch['ref_end']);
	}
	return $spans;
}

/**
 * Tokenize like multidiff anchor discovery (for post-legacy chain seed merge).
 *
 * @return array{tokens:list<array{s:string,o:int,l:int,d:bool}>, anchorMap:array<string,list<int>>, tokenCount:int}
 */
function fractal_zip_bio_multidiff_build_tokens(string $string): array
{
	$maxTokens = fractal_zip::substring_multidiff_max_tokens();
	$maxAnchorOccurs = fractal_zip::substring_multidiff_max_anchor_occurs();
	$minAnchorBytes = fractal_zip::substring_multidiff_min_anchor_bytes();
	$rxToken = '/[^\r\n\t ,;:|\/\\\\\-_=\+\.\(\)\[\]\{\}<>]+|[\r\n\t ,;:|\/\\\\\-_=\+\.\(\)\[\]\{\}<>]+/s';
	$mm = array();
	if (preg_match_all($rxToken, $string, $mm, PREG_OFFSET_CAPTURE) === false || !isset($mm[0])) {
		return array('tokens' => array(), 'anchorMap' => array(), 'tokenCount' => 0);
	}
	$tokens = array();
	$anchorMap = array();
	$tokenCount = 0;
	foreach ($mm[0] as $row) {
		$tok = (string) $row[0];
		$off = (int) $row[1];
		$len = strlen($tok);
		if ($len <= 0) {
			continue;
		}
		$isDelim = strspn($tok, "\r\n\t ,;:|/\\-_=+.()[]{}<>") === $len;
		$tokens[] = array('s' => $tok, 'o' => $off, 'l' => $len, 'd' => $isDelim);
		$ti = $tokenCount;
		$tokenCount++;
		if ($tokenCount > $maxTokens) {
			return array('tokens' => array(), 'anchorMap' => array(), 'tokenCount' => 0);
		}
		if ($isDelim || $len < $minAnchorBytes) {
			continue;
		}
		if (!isset($anchorMap[$tok])) {
			$anchorMap[$tok] = array($ti);
			continue;
		}
		if (count($anchorMap[$tok]) < $maxAnchorOccurs) {
			$anchorMap[$tok][] = $ti;
		}
	}
	return array('tokens' => $tokens, 'anchorMap' => $anchorMap, 'tokenCount' => $tokenCount);
}

/**
 * Post-legacy chain seeds from raw string (mixed-profile enwik clears multidiff pre-seeds).
 *
 * @param array<string, true> $haystackByte
 * @return array<string, int>
 */
function fractal_zip_bio_multidiff_chain_seeds_from_string(
	string $string,
	int $minSubLen,
	int $maxSubLen,
	array $haystackByte
): array {
	$built = fractal_zip_bio_multidiff_build_tokens($string);
	if ($built['tokenCount'] < 2 || $built['anchorMap'] === array()) {
		return array();
	}
	return fractal_zip_bio_multidiff_self_chain_seeds(
		$string,
		$built['tokens'],
		$built['anchorMap'],
		$minSubLen,
		$maxSubLen,
		$haystackByte
	);
}

/**
 * Colinear anchor chains on repeated token anchors → longer MEM-style substring seeds.
 *
 * @param list<array{s:string,o:int,l:int,d:bool}> $tokens
 * @param array<string, list<int>> $anchorMap
 * @param array<string, true> $haystackByte
 * @return array<string, int>
 */
function fractal_zip_bio_multidiff_self_chain_seeds(
	string $string,
	array $tokens,
	array $anchorMap,
	int $minSubLen,
	int $maxSubLen,
	array $haystackByte,
	int $maxChains = 48
): array {
	$seeds = array();
	$chainsFound = 0;
	foreach ($anchorMap as $idxList) {
		if (count($idxList) < 2) {
			continue;
		}
		$anchors = array();
		foreach ($idxList as $ti) {
			$t = $tokens[(int) $ti] ?? null;
			if (!is_array($t)) {
				continue;
			}
			$anchors[] = array(
				'ref_pos' => (int) $t['o'],
				'query_pos' => (int) $t['o'],
				'len' => (int) $t['l'],
			);
		}
		if (count($anchors) < 2) {
			continue;
		}
		$chains = fractal_zip_bio_colinear_chain($anchors, 0);
		foreach ($chains as $ch) {
			if ($chainsFound >= $maxChains) {
				break 2;
			}
			$spans = fractal_zip_bio_multidiff_chain_colinear_spans($ch);
			foreach ($spans as $span) {
				if ($chainsFound >= $maxChains) {
					break 3;
				}
				$rs = (int) $span['rs'];
				$re = (int) $span['re'];
				$len = $re - $rs;
				if ($len < $minSubLen || $len > $maxSubLen) {
					continue;
				}
				$sub = substr($string, $rs, $len);
				if ($sub === '') {
					continue;
				}
				$okBytes = true;
				for ($bi = 0, $bl = strlen($sub); $bi < $bl; $bi++) {
					if (!isset($haystackByte[$sub[$bi]])) {
						$okBytes = false;
						break;
					}
				}
				if (!$okBytes) {
					continue;
				}
				$rc = substr_count($string, $sub);
				if ($rc < 2) {
					continue;
				}
				if (!isset($seeds[$sub]) || $seeds[$sub] < $rc) {
					$seeds[$sub] = $rc;
				}
				$chainsFound++;
			}
		}
	}
	$unified = fractal_zip_bio_multidiff_unified_chain_seeds(
		$string,
		$tokens,
		$anchorMap,
		$minSubLen,
		$maxSubLen,
		$haystackByte,
		max(0, $maxChains - $chainsFound)
	);
	foreach ($unified as $sub => $rc) {
		if (!isset($seeds[$sub]) || $seeds[$sub] < $rc) {
			$seeds[$sub] = $rc;
		}
	}
	return $seeds;
}

/**
 * Colinear chain across all anchor tokens (minimap2-style MEM chaining on one string).
 *
 * @param array<int, array{o:int, l:int}> $tokens
 * @param array<string, list<int>> $anchorMap
 * @param array<string, true> $haystackByte
 * @return array<string, int>
 */
function fractal_zip_bio_multidiff_unified_chain_seeds(
	string $string,
	array $tokens,
	array $anchorMap,
	int $minSubLen,
	int $maxSubLen,
	array $haystackByte,
	int $maxChains = 48,
	int $maxAnchors = 512
): array {
	$seeds = array();
	if ($maxChains <= 0) {
		return $seeds;
	}
	$anchors = array();
	foreach ($anchorMap as $idxList) {
		foreach ($idxList as $ti) {
			if (count($anchors) >= $maxAnchors) {
				break 2;
			}
			$t = $tokens[(int) $ti] ?? null;
			if (!is_array($t)) {
				continue;
			}
			$anchors[] = array(
				'ref_pos' => (int) $t['o'],
				'query_pos' => (int) $t['o'],
				'len' => (int) $t['l'],
			);
		}
	}
	if (count($anchors) < 2) {
		return $seeds;
	}
	usort($anchors, static function (array $a, array $b): int {
		return ($a['ref_pos'] <=> $b['ref_pos']) ?: ($a['query_pos'] <=> $b['query_pos']);
	});
	$chains = fractal_zip_bio_colinear_chain($anchors, 0);
	$chainsFound = 0;
	foreach ($chains as $ch) {
		if ($chainsFound >= $maxChains) {
			break;
		}
		$spans = fractal_zip_bio_multidiff_chain_colinear_spans($ch);
		foreach ($spans as $span) {
			if ($chainsFound >= $maxChains) {
				break 2;
			}
			$rs = (int) $span['rs'];
			$re = (int) $span['re'];
			$len = $re - $rs;
			if ($len < $minSubLen || $len > $maxSubLen) {
				continue;
			}
			$sub = substr($string, $rs, $len);
			if ($sub === '') {
				continue;
			}
			$okBytes = true;
			for ($bi = 0, $bl = strlen($sub); $bi < $bl; $bi++) {
				if (!isset($haystackByte[$sub[$bi]])) {
					$okBytes = false;
					break;
				}
			}
			if (!$okBytes) {
				continue;
			}
			$rc = substr_count($string, $sub);
			if ($rc < 2) {
				continue;
			}
			if (!isset($seeds[$sub]) || $seeds[$sub] < $rc) {
				$seeds[$sub] = $rc;
			}
			$chainsFound++;
		}
	}
	return $seeds;
}

// ---------------------------------------------------------------------------
// Banded Needleman–Wunsch (affine gaps simplified: single gap penalty)
// ---------------------------------------------------------------------------

/**
 * @return array{ops:string, score:int, ref_aligned:string, query_aligned:string}
 */
function fractal_zip_bio_band_nw(string $ref, string $query, int $band = 32, int $match = 2, int $mismatch = -1, int $gap = -2): array
{
	$n = strlen($ref);
	$m = strlen($query);
	$maxCells = 4_000_000;
	if ($n * min($m, 2 * $band + 1) > $maxCells || $m > 65536 || $n > 65536) {
		$lim = min($n, $m);
		$ops = '';
		$ra = '';
		$qa = '';
		for ($i = 0; $i < $lim; $i++) {
			$ops .= ($ref[$i] === $query[$i]) ? 'M' : 'X';
			$ra .= $ref[$i];
			$qa .= $query[$i];
		}
		if ($n > $m) {
			$ops .= str_repeat('D', $n - $m);
			$ra .= substr($ref, $m);
			$qa .= str_repeat('-', $n - $m);
		} elseif ($m > $n) {
			$ops .= str_repeat('I', $m - $n);
			$ra .= str_repeat('-', $m - $n);
			$qa .= substr($query, $n);
		}
		return array('ops' => $ops, 'score' => 0, 'ref_aligned' => $ra, 'query_aligned' => $qa);
	}
	if ($n === 0 && $m === 0) {
		return array('ops' => '', 'score' => 0, 'ref_aligned' => '', 'query_aligned' => '');
	}
	if ($n === 0) {
		return array('ops' => str_repeat('I', $m), 'score' => $gap * $m, 'ref_aligned' => str_repeat('-', $m), 'query_aligned' => $query);
	}
	if ($m === 0) {
		return array('ops' => str_repeat('D', $n), 'score' => $gap * $n, 'ref_aligned' => $ref, 'query_aligned' => str_repeat('-', $n));
	}
	$inf = -1000000000;
	$prev = array();
	$curr = array();
	$trace = array();
	for ($dj = -$band; $dj <= $band; $dj++) {
		$prev[$dj] = $inf;
	}
	$prev[0] = 0;
	for ($j = 1; $j <= min($m, $band); $j++) {
		$prev[$j] = $gap * $j;
	}
	for ($i = 1; $i <= $n; $i++) {
		$curr = array();
		$trace[$i] = array();
		$jMin = max(1, $i - $band);
		$jMax = min($m, $i + $band);
		for ($j = $jMin; $j <= $jMax; $j++) {
			$dj = $j - $i;
			$diag = $prev[$dj - 1] ?? $inf;
			$up = $prev[$dj] ?? $inf;
			$left = $curr[$dj - 1] ?? $inf;
			$rc = $ref[$i - 1];
			$qc = $query[$j - 1];
			$mat = $diag + ($rc === $qc ? $match : $mismatch);
			$del = $up + $gap;
			$ins = $left + $gap;
			$best = $mat;
			$t = 0;
			if ($del > $best) {
				$best = $del;
				$t = 1;
			}
			if ($ins > $best) {
				$best = $ins;
				$t = 2;
			}
			$curr[$dj] = $best;
			$trace[$i][$dj] = $t;
		}
		$prev = $curr;
	}
	$dj = $m - $n;
	if ($dj < -$band || $dj > $band) {
		$dj = max(-$band, min($band, $dj));
	}
	$i = $n;
	$j = $m;
	$ops = '';
	$ra = '';
	$qa = '';
	while ($i > 0 || $j > 0) {
		$dj = $j - $i;
		$t = $trace[$i][$dj] ?? 0;
		if ($i > 0 && $j > 0 && $t === 0) {
			$ops .= ($ref[$i - 1] === $query[$j - 1]) ? 'M' : 'X';
			$ra = $ref[$i - 1] . $ra;
			$qa = $query[$j - 1] . $qa;
			$i--;
			$j--;
		} elseif ($i > 0 && ($t === 1 || $j === 0)) {
			$ops .= 'D';
			$ra = $ref[$i - 1] . $ra;
			$qa = '-' . $qa;
			$i--;
		} else {
			$ops .= 'I';
			$ra = '-' . $ra;
			$qa = $query[$j - 1] . $qa;
			$j--;
		}
	}
	return array('ops' => $ops, 'score' => (int) ($curr[$m - $n] ?? $curr[$dj] ?? 0), 'ref_aligned' => $ra, 'query_aligned' => $qa);
}

// ---------------------------------------------------------------------------
// VCF-style structured variants
// ---------------------------------------------------------------------------

/**
 * @return list<array{type:string, pos:int, ref:string, alt:string}>
 */
function fractal_zip_bio_vcf_call(string $ref, string $alt): array
{
	$nw = fractal_zip_bio_band_nw($ref, $alt, min(64, max(strlen($ref), strlen($alt))));
	$ra = (string) $nw['ref_aligned'];
	$aa = (string) $nw['query_aligned'];
	$variants = array();
	$pos = 0;
	$i = 0;
	$rlen = strlen($ra);
	while ($i < $rlen) {
		if ($ra[$i] === '-' && $aa[$i] !== '-') {
			$ins = '';
			while ($i < $rlen && $ra[$i] === '-' && $aa[$i] !== '-') {
				$ins .= $aa[$i];
				$i++;
			}
			$variants[] = array('type' => 'INS', 'pos' => $pos, 'ref' => '', 'alt' => $ins);
			continue;
		}
		if ($ra[$i] !== '-' && $aa[$i] === '-') {
			$del = '';
			while ($i < $rlen && $ra[$i] !== '-' && $aa[$i] === '-') {
				$del .= $ra[$i];
				$i++;
			}
			$variants[] = array('type' => 'DEL', 'pos' => $pos, 'ref' => $del, 'alt' => '');
			$pos += strlen($del);
			continue;
		}
		if ($ra[$i] !== $aa[$i]) {
			$variants[] = array('type' => 'SNP', 'pos' => $pos, 'ref' => $ra[$i], 'alt' => $aa[$i]);
		}
		if ($ra[$i] !== '-') {
			$pos++;
		}
		$i++;
	}
	return $variants;
}

/**
 * Dominant rigid shift from maximal exact matches (minimap2-style).
 */
function fractal_zip_bio_pair_shift(string $ref, string $alt, int $minMem = 8): int
{
	$mems = fractal_zip_bio_mems($ref, $alt, $minMem);
	if ($mems === array()) {
		return 0;
	}
	$hist = array();
	foreach ($mems as $m) {
		$d = (int) $m['ref_pos'] - (int) $m['query_pos'];
		$hist[$d] = ($hist[$d] ?? 0) + (int) $m['len'];
	}
	arsort($hist);
	return (int) array_key_first($hist);
}

/**
 * Variant calling after applying a rigid shift between ref and member.
 *
 * @return list<array{type:string, pos:int, ref:string, alt:string}>
 */
function fractal_zip_bio_vcf_call_shifted(string $ref, string $member, int $shift): array
{
	$variants = array();
	$L = strlen($member);
	$R = strlen($ref);
	$p = 0;
	$insPrefix = '';
	while ($p < $L && $p + $shift < 0) {
		$insPrefix .= $member[$p];
		$p++;
	}
	if ($insPrefix !== '') {
		$variants[] = array('type' => 'INS', 'pos' => 0, 'ref' => '', 'alt' => $insPrefix);
	}
	while ($p < $L) {
		$rp = $p + $shift;
		if ($rp >= $R) {
			$tail = substr($member, $p);
			if ($tail !== '') {
				$variants[] = array('type' => 'INS', 'pos' => $R, 'ref' => '', 'alt' => $tail);
			}
			break;
		}
		if ($member[$p] === $ref[$rp]) {
			$p++;
			continue;
		}
		$litStartR = $rp;
		$lit = '';
		while ($p < $L) {
			$rp = $p + $shift;
			if ($rp >= $R || $member[$p] === $ref[$rp]) {
				break;
			}
			$lit .= $member[$p];
			$p++;
		}
		if ($rp >= $R && $p < $L) {
			$variants[] = array('type' => 'INS', 'pos' => $R, 'ref' => '', 'alt' => substr($member, $p));
			break;
		}
		$refRun = substr($ref, $litStartR, strlen($lit));
		if ($lit === $refRun) {
			continue;
		}
		if (strlen($lit) === 1 && strlen($refRun) === 1) {
			$variants[] = array('type' => 'SNP', 'pos' => $litStartR, 'ref' => $refRun, 'alt' => $lit);
			continue;
		}
		$sub = fractal_zip_bio_vcf_call($refRun, $lit);
		foreach ($sub as $v) {
			$v['pos'] = (int) $v['pos'] + $litStartR;
			$variants[] = $v;
		}
	}
	$endR = $L + $shift;
	if ($endR < $R) {
		$del = substr($ref, max(0, $endR));
		if ($del !== '') {
			$variants[] = array('type' => 'DEL', 'pos' => max(0, $endR), 'ref' => $del, 'alt' => '');
		}
	}
	return $variants;
}

/**
 * Encode VCF variants only when apply() round-trips alt from ref.
 *
 * @return array{var_count:int, vcf:string}|null
 */
function fractal_zip_bio_vcf_try_pack(string $ref, string $alt): ?array
{
	if ($ref === $alt) {
		return array('var_count' => 0, 'vcf' => '');
	}
	$shifts = array(0, fractal_zip_bio_pair_shift($ref, $alt));
	$shifts = array_values(array_unique($shifts));
	foreach ($shifts as $shift) {
		$vars = $shift === 0 ? fractal_zip_bio_vcf_call($ref, $alt) : fractal_zip_bio_vcf_call_shifted($ref, $alt, $shift);
		$vcf = fractal_zip_bio_vcf_encode($vars);
		$dec = fractal_zip_bio_vcf_decode($vcf, count($vars));
		$restored = fractal_zip_bio_vcf_apply($ref, $dec);
		if ($restored === $alt) {
			return array('var_count' => count($vars), 'vcf' => $vcf);
		}
	}
	return null;
}

function fractal_zip_bio_vcf_encode(array $variants): string
{
	$out = '';
	foreach ($variants as $v) {
		$t = (string) $v['type'];
		$out .= chr(strlen($t)) . $t;
		$out .= fractal_zip_enwik_encode_varint_u32((int) $v['pos']);
		$out .= fractal_zip_enwik_encode_varint_u32(strlen((string) $v['ref']));
		$out .= (string) $v['ref'];
		$out .= fractal_zip_enwik_encode_varint_u32(strlen((string) $v['alt']));
		$out .= (string) $v['alt'];
	}
	return $out;
}

/**
 * @return list<array{type:string, pos:int, ref:string, alt:string}>
 */
function fractal_zip_bio_vcf_decode(string $blob, int $count): array
{
	$pos = 0;
	$variants = array();
	for ($c = 0; $c < $count; $c++) {
		if ($pos >= strlen($blob)) {
			break;
		}
		$tl = ord($blob[$pos]);
		$pos++;
		$type = substr($blob, $pos, $tl);
		$pos += $tl;
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $pos);
		$vpos = (int) $dv[0];
		$pos = (int) $dv[1];
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $pos);
		$rl = (int) $dv[0];
		$pos = (int) $dv[1];
		$ref = substr($blob, $pos, $rl);
		$pos += $rl;
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $pos);
		$al = (int) $dv[0];
		$pos = (int) $dv[1];
		$alt = substr($blob, $pos, $al);
		$pos += $al;
		$variants[] = array('type' => $type, 'pos' => $vpos, 'ref' => $ref, 'alt' => $alt);
	}
	return $variants;
}

function fractal_zip_bio_vcf_apply(string $ref, array $variants): string
{
	$out = $ref;
	$offset = 0;
	foreach ($variants as $v) {
		$p = (int) $v['pos'] + $offset;
		$alt = (string) ($v['alt'] ?? '');
		$vref = (string) ($v['ref'] ?? '');
		if ($v['type'] === 'SNP' && $p < strlen($out) && $alt !== '') {
			$out = substr($out, 0, $p) . $alt . substr($out, $p + max(1, strlen($vref)));
			$offset += strlen($alt) - max(1, strlen($vref));
		} elseif ($v['type'] === 'INS') {
			$out = substr($out, 0, $p) . $alt . substr($out, $p);
			$offset += strlen($alt);
		} elseif ($v['type'] === 'DEL') {
			$dl = strlen($vref);
			$out = substr($out, 0, $p) . substr($out, $p + $dl);
			$offset -= $dl;
		}
	}
	return $out;
}

// ---------------------------------------------------------------------------
// POA / progressive consensus
// ---------------------------------------------------------------------------

function fractal_zip_bio_poa_consensus(array $sequences): string
{
	$seqs = array_values(array_filter($sequences, static fn (string $s): bool => $s !== ''));
	if ($seqs === array()) {
		return '';
	}
	$first = $seqs[0];
	$allSame = true;
	foreach ($seqs as $s) {
		if ($s !== $first) {
			$allSame = false;
			break;
		}
	}
	if ($allSame) {
		return $first;
	}
	usort($seqs, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
	$consensus = $seqs[0];
	for ($si = 1; $si < count($seqs); $si++) {
		$nw = fractal_zip_bio_band_nw($consensus, $seqs[$si], 64);
		$ra = (string) $nw['ref_aligned'];
		$aa = (string) $nw['query_aligned'];
		$new = '';
		$rlen = strlen($ra);
		for ($i = 0; $i < $rlen; $i++) {
			$rc = $ra[$i];
			$ac = $aa[$i];
			if ($rc === '-' && $ac !== '-') {
				$new .= $ac;
			} elseif ($ac === '-' && $rc !== '-') {
				$new .= $rc;
			} elseif ($rc === $ac) {
				$new .= $rc;
			} else {
				$new .= ord($rc) <= ord($ac) ? $rc : $ac;
			}
		}
		$consensus = $new;
	}
	return $consensus;
}

// ---------------------------------------------------------------------------
// Synteny / block rearrangement
// ---------------------------------------------------------------------------

/**
 * Detect synteny blocks from colinear chains; breaks imply rearrangements.
 *
 * @return array{blocks:list<array>, breaks:list<array>}
 */
function fractal_zip_bio_synteny_blocks(string $ref, string $query, int $minAnchorLen = 6): array
{
	$mems = fractal_zip_bio_mems($ref, $query, $minAnchorLen);
	$anchors = array();
	foreach ($mems as $m) {
		$anchors[] = array('ref_pos' => $m['ref_pos'], 'query_pos' => $m['query_pos'], 'len' => $m['len']);
	}
	$chains = fractal_zip_bio_colinear_chain($anchors, 16);
	$blocks = array();
	$breaks = array();
	$prevQ = -1;
	$prevStrand = 1;
	foreach ($chains as $ci => $ch) {
		$qs = (int) $ch['query_start'];
		$qe = (int) $ch['query_end'];
		$rs = (int) $ch['ref_start'];
		$re = (int) $ch['ref_end'];
		$strand = ($qe >= $qs) ? 1 : -1;
		$blocks[] = array(
			'ref_start' => $rs, 'ref_end' => $re,
			'query_start' => min($qs, $qe), 'query_end' => max($qs, $qe),
			'shift' => (int) $ch['shift'], 'strand' => $strand,
		);
		if ($prevQ >= 0 && $qs < $prevQ) {
			$breaks[] = array('type' => 'inversion_or_rearrangement', 'after_block' => $ci - 1, 'query_pos' => $qs);
		} elseif ($prevQ >= 0 && $strand !== $prevStrand) {
			$breaks[] = array('type' => 'strand_flip', 'after_block' => $ci - 1);
		}
		$prevQ = $qe;
		$prevStrand = $strand;
	}
	return array('blocks' => $blocks, 'breaks' => $breaks);
}

// ---------------------------------------------------------------------------
// Simplified pangenome variation graph (vg-style)
// ---------------------------------------------------------------------------

/**
 * @param list<array{id:int, text:string}> $items
 * @return array{nodes:list<array>, edges:list<array>, paths:list<array>}
 */
function fractal_zip_bio_pangenome_graph(array $items): array
{
	if ($items === array()) {
		return array('nodes' => array(), 'edges' => array(), 'paths' => array());
	}
	$refItem = $items[0];
	foreach ($items as $it) {
		if (strlen((string) $it['text']) > strlen((string) $refItem['text'])) {
			$refItem = $it;
		}
	}
	$consensus = (string) $refItem['text'];
	$nodes = array(array('id' => 0, 'seq' => $consensus, 'type' => 'core'));
	$edges = array();
	$paths = array();
	$nodeId = 1;
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';
	foreach ($items as $it) {
		$id = (int) $it['id'];
		$text = (string) $it['text'];
		if ($text === $consensus) {
			$paths[] = array('member' => $id, 'nodes' => array(0));
			continue;
		}
		$pack = fractal_zip_bio_vcf_try_pack($consensus, $text);
		$pathNodes = array(0);
		if ($pack !== null && $pack['var_count'] > 0) {
			$altBlob = (string) $pack['vcf'];
			$nodes[] = array('id' => $nodeId, 'seq' => $altBlob, 'type' => 'variation', 'member' => $id, 'var_count' => (int) $pack['var_count']);
			$edges[] = array('from' => 0, 'to' => $nodeId, 'member' => $id);
			$pathNodes[] = $nodeId;
			$nodeId++;
		} else {
			$refAnchors = fractal_zip_multishift_anchor_map($consensus);
			$peaks = fractal_zip_multishift_shift_vote($consensus, $text, $refAnchors);
			$best = fractal_zip_multishift_best_member_shift($consensus, $text, $peaks);
			$shift = (int) $best['shift'];
			$delta = (string) $best['delta'];
			$dec = fractal_zip_multishift_delta_decode($delta, 0, $consensus, $shift, strlen($text));
			if ($dec[0] === $text) {
				$nodes[] = array(
					'id' => $nodeId,
					'seq' => $delta,
					'type' => 'delta',
					'member' => $id,
					'shift' => $shift,
					'member_len' => strlen($text),
				);
				$edges[] = array('from' => 0, 'to' => $nodeId, 'member' => $id);
				$pathNodes[] = $nodeId;
				$nodeId++;
			} else {
				$nodes[] = array('id' => $nodeId, 'seq' => $text, 'type' => 'raw', 'member' => $id);
				$pathNodes = array($nodeId);
				$nodeId++;
			}
		}
		$paths[] = array('member' => $id, 'nodes' => $pathNodes);
	}
	return array('nodes' => $nodes, 'edges' => $edges, 'paths' => $paths);
}

const FRACTAL_ZIP_BIO_PANGENOME_MAGIC = "FZBI\x02";
const FRACTAL_ZIP_BIO_PANGENOME_OUTBOARD_MAGIC = "FZBI\x03";
const FRACTAL_ZIP_BIO_PANGENOME_COMPACT_MAGIC = "FZBI\x04";

/**
 * Paths are [0], [0,leaf], or [raw] only — safe to omit edge list and full path arrays.
 */
function fractal_zip_bio_pangenome_paths_compactible(array $graph): bool
{
	foreach ($graph['paths'] ?? array() as $path) {
		if (fractal_zip_bio_pangenome_path_compact_entry($path)['kind'] === 3) {
			return false;
		}
	}
	return ($graph['paths'] ?? array()) !== array();
}

/**
 * @return array{kind:int, leaf:int} kind 0=core [0], 1=[0,leaf], 2=raw-only [leaf]
 */
function fractal_zip_bio_pangenome_path_compact_entry(array $path): array
{
	$pn = $path['nodes'] ?? array();
	if (count($pn) === 1 && (int) $pn[0] === 0) {
		return array('kind' => 0, 'leaf' => 0);
	}
	if (count($pn) === 2 && (int) $pn[0] === 0) {
		return array('kind' => 1, 'leaf' => (int) $pn[1]);
	}
	if (count($pn) === 1) {
		return array('kind' => 2, 'leaf' => (int) $pn[0]);
	}
	return array('kind' => 3, 'leaf' => 0);
}

/**
 * @param list<array<string,mixed>> $nodes
 */
function fractal_zip_bio_pangenome_pack_outboard_nodes(array $nodes): string
{
	$typeToByte = static function (string $type): int {
		return match ($type) {
			'core' => 0,
			'raw' => 1,
			'variation' => 2,
			'delta' => 3,
			default => 1,
		};
	};
	$out = fractal_zip_enwik_encode_varint_u32(count($nodes));
	foreach ($nodes as $node) {
		$type = (string) ($node['type'] ?? 'raw');
		$out .= fractal_zip_enwik_encode_varint_u32((int) ($node['id'] ?? 0));
		$out .= chr($typeToByte($type));
		$out .= fractal_zip_multishift_zigzag_encode((int) ($node['member'] ?? -1));
		if ($type === 'variation') {
			$out .= fractal_zip_enwik_encode_varint_u32((int) ($node['var_count'] ?? 0));
		} elseif ($type === 'delta') {
			$out .= fractal_zip_multishift_zigzag_encode((int) ($node['shift'] ?? 0));
			$out .= fractal_zip_enwik_encode_varint_u32((int) ($node['member_len'] ?? 0));
		} else {
			$out .= fractal_zip_enwik_encode_varint_u32(0);
		}
		$out .= fractal_zip_enwik_encode_varint_u32((int) ($node['payload_off'] ?? 0));
		$out .= fractal_zip_enwik_encode_varint_u32((int) ($node['payload_len'] ?? 0));
	}
	return $out;
}

function fractal_zip_bio_pangenome_pack_outboard_compact(array $graph): string
{
	$out = FRACTAL_ZIP_BIO_PANGENOME_COMPACT_MAGIC;
	$out .= fractal_zip_bio_pangenome_pack_outboard_nodes($graph['nodes'] ?? array());
	$paths = $graph['paths'] ?? array();
	$out .= fractal_zip_enwik_encode_varint_u32(count($paths));
	foreach ($paths as $path) {
		$ent = fractal_zip_bio_pangenome_path_compact_entry($path);
		$out .= fractal_zip_enwik_encode_varint_u32((int) ($path['member'] ?? 0));
		$out .= chr((int) $ent['kind']);
		if ($ent['kind'] !== 0) {
			$out .= fractal_zip_enwik_encode_varint_u32((int) $ent['leaf']);
		}
	}
	return $out;
}

function fractal_zip_bio_pangenome_pack_outboard(array $graph): string
{
	if (fractal_zip_bio_pangenome_paths_compactible($graph)) {
		return fractal_zip_bio_pangenome_pack_outboard_compact($graph);
	}
	$out = FRACTAL_ZIP_BIO_PANGENOME_OUTBOARD_MAGIC;
	$out .= fractal_zip_bio_pangenome_pack_outboard_nodes($graph['nodes'] ?? array());
	$edges = $graph['edges'] ?? array();
	$out .= fractal_zip_enwik_encode_varint_u32(count($edges));
	foreach ($edges as $edge) {
		$out .= fractal_zip_enwik_encode_varint_u32((int) ($edge['from'] ?? 0));
		$out .= fractal_zip_enwik_encode_varint_u32((int) ($edge['to'] ?? 0));
		$out .= fractal_zip_multishift_zigzag_encode((int) ($edge['member'] ?? 0));
	}
	$paths = $graph['paths'] ?? array();
	$out .= fractal_zip_enwik_encode_varint_u32(count($paths));
	foreach ($paths as $path) {
		$out .= fractal_zip_enwik_encode_varint_u32((int) ($path['member'] ?? 0));
		$pnodes = $path['nodes'] ?? array();
		$out .= fractal_zip_enwik_encode_varint_u32(count($pnodes));
		foreach ($pnodes as $nid) {
			$out .= fractal_zip_enwik_encode_varint_u32((int) $nid);
		}
	}
	return $out;
}

/**
 * Move node payloads into a cluster blob; graph nodes get payload_off/len only.
 *
 * @return array{blob:string, graph:array{nodes:list, edges:list, paths:list}}
 */
function fractal_zip_bio_pangenome_graph_outboard(array $graph): array
{
	$blob = '';
	$nodesOut = array();
	foreach ($graph['nodes'] ?? array() as $node) {
		$type = (string) ($node['type'] ?? 'raw');
		$seq = (string) ($node['seq'] ?? '');
		$entry = $node;
		if ($type === 'core') {
			$entry['payload_off'] = 0;
			$entry['payload_len'] = strlen($seq);
			$blob = $seq;
		} else {
			$entry['payload_off'] = strlen($blob);
			$entry['payload_len'] = strlen($seq);
			$blob .= $seq;
		}
		unset($entry['seq']);
		$nodesOut[] = $entry;
	}
	return array(
		'blob' => $blob,
		'graph' => array(
			'nodes' => $nodesOut,
			'edges' => $graph['edges'] ?? array(),
			'paths' => $graph['paths'] ?? array(),
		),
	);
}

function fractal_zip_bio_pangenome_pack(array $graph): string
{
	$nodes = $graph['nodes'] ?? array();
	foreach ($nodes as $node) {
		$type = (string) ($node['type'] ?? '');
		if ($type !== 'core' && array_key_exists('payload_off', $node) && !array_key_exists('seq', $node)) {
			return fractal_zip_bio_pangenome_pack_outboard($graph);
		}
	}
	return fractal_zip_bio_pangenome_pack_inline($graph);
}

function fractal_zip_bio_pangenome_pack_inline(array $graph): string
{
	$typeToByte = static function (string $type): int {
		return match ($type) {
			'core' => 0,
			'raw' => 1,
			'variation' => 2,
			'delta' => 3,
			default => 1,
		};
	};
	$out = FRACTAL_ZIP_BIO_PANGENOME_MAGIC;
	$nodes = $graph['nodes'] ?? array();
	$out .= fractal_zip_enwik_encode_varint_u32(count($nodes));
	foreach ($nodes as $node) {
		$type = (string) ($node['type'] ?? 'raw');
		$out .= fractal_zip_enwik_encode_varint_u32((int) ($node['id'] ?? 0));
		$out .= chr($typeToByte($type));
		$member = (int) ($node['member'] ?? -1);
		$out .= fractal_zip_multishift_zigzag_encode($member);
		if ($type === 'variation') {
			$out .= fractal_zip_enwik_encode_varint_u32((int) ($node['var_count'] ?? 0));
		} elseif ($type === 'delta') {
			$out .= fractal_zip_multishift_zigzag_encode((int) ($node['shift'] ?? 0));
			$out .= fractal_zip_enwik_encode_varint_u32((int) ($node['member_len'] ?? 0));
		} else {
			$out .= fractal_zip_enwik_encode_varint_u32(0);
		}
		$seq = (string) ($node['seq'] ?? '');
		$out .= fractal_zip_enwik_encode_varint_u32(strlen($seq));
		$out .= $seq;
	}
	$edges = $graph['edges'] ?? array();
	$out .= fractal_zip_enwik_encode_varint_u32(count($edges));
	foreach ($edges as $edge) {
		$out .= fractal_zip_enwik_encode_varint_u32((int) ($edge['from'] ?? 0));
		$out .= fractal_zip_enwik_encode_varint_u32((int) ($edge['to'] ?? 0));
		$out .= fractal_zip_multishift_zigzag_encode((int) ($edge['member'] ?? 0));
	}
	$paths = $graph['paths'] ?? array();
	$out .= fractal_zip_enwik_encode_varint_u32(count($paths));
	foreach ($paths as $path) {
		$out .= fractal_zip_enwik_encode_varint_u32((int) ($path['member'] ?? 0));
		$pnodes = $path['nodes'] ?? array();
		$out .= fractal_zip_enwik_encode_varint_u32(count($pnodes));
		foreach ($pnodes as $nid) {
			$out .= fractal_zip_enwik_encode_varint_u32((int) $nid);
		}
	}
	return $out;
}

function fractal_zip_bio_pangenome_unpack_legacy_json(string $blob): array
{
	$ml = strlen(FRACTAL_ZIP_BIO_MAGIC);
	$pos = $ml;
	$dv = fractal_zip_enwik_decode_varint_u32($blob, $pos);
	$len = (int) $dv[0];
	$pos = (int) $dv[1];
	$json = substr($blob, $pos, $len);
	$g = json_decode($json, true);
	return is_array($g) ? $g : array('nodes' => array(), 'edges' => array(), 'paths' => array());
}

function fractal_zip_bio_pangenome_unpack(string $blob): array
{
	if (strncmp($blob, FRACTAL_ZIP_BIO_PANGENOME_COMPACT_MAGIC, strlen(FRACTAL_ZIP_BIO_PANGENOME_COMPACT_MAGIC)) === 0) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';
		$byteToType = array(0 => 'core', 1 => 'raw', 2 => 'variation', 3 => 'delta');
		$pos = strlen(FRACTAL_ZIP_BIO_PANGENOME_COMPACT_MAGIC);
		$readU = static function () use ($blob, &$pos): int {
			$d = fractal_zip_enwik_decode_varint_u32($blob, $pos);
			$pos = (int) $d[1];
			return (int) $d[0];
		};
		$nodeCount = $readU();
		$nodes = array();
		for ($ni = 0; $ni < $nodeCount; $ni++) {
			$id = $readU();
			$tb = ord($blob[$pos]);
			$pos++;
			$type = $byteToType[$tb] ?? 'raw';
			$zzM = fractal_zip_multishift_zigzag_decode($blob, $pos);
			$pos = (int) $zzM[1];
			$member = (int) $zzM[0];
			$node = array('id' => $id, 'type' => $type, 'payload_off' => 0, 'payload_len' => 0);
			if ($member >= 0) {
				$node['member'] = $member;
			}
			if ($type === 'variation') {
				$node['var_count'] = $readU();
			} elseif ($type === 'delta') {
				$zzS = fractal_zip_multishift_zigzag_decode($blob, $pos);
				$pos = (int) $zzS[1];
				$node['shift'] = (int) $zzS[0];
				$node['member_len'] = $readU();
			} else {
				$readU();
			}
			$node['payload_off'] = $readU();
			$node['payload_len'] = $readU();
			$nodes[] = $node;
		}
		$pathCount = $readU();
		$paths = array();
		$edges = array();
		for ($pi = 0; $pi < $pathCount; $pi++) {
			$member = $readU();
			$kind = ord($blob[$pos]);
			$pos++;
			if ($kind === 0) {
				$paths[] = array('member' => $member, 'nodes' => array(0));
				continue;
			}
			$leaf = $readU();
			if ($kind === 1) {
				$paths[] = array('member' => $member, 'nodes' => array(0, $leaf));
				$edges[] = array('from' => 0, 'to' => $leaf, 'member' => $member);
				continue;
			}
			$paths[] = array('member' => $member, 'nodes' => array($leaf));
		}
		return array('nodes' => $nodes, 'edges' => $edges, 'paths' => $paths, 'outboard' => true, 'compact' => true);
	}
	if (strncmp($blob, FRACTAL_ZIP_BIO_PANGENOME_OUTBOARD_MAGIC, strlen(FRACTAL_ZIP_BIO_PANGENOME_OUTBOARD_MAGIC)) === 0) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';
		$byteToType = array(0 => 'core', 1 => 'raw', 2 => 'variation', 3 => 'delta');
		$pos = strlen(FRACTAL_ZIP_BIO_PANGENOME_OUTBOARD_MAGIC);
		$readU = static function () use ($blob, &$pos): int {
			$d = fractal_zip_enwik_decode_varint_u32($blob, $pos);
			$pos = (int) $d[1];
			return (int) $d[0];
		};
		$nodeCount = $readU();
		$nodes = array();
		for ($ni = 0; $ni < $nodeCount; $ni++) {
			$id = $readU();
			$tb = ord($blob[$pos]);
			$pos++;
			$type = $byteToType[$tb] ?? 'raw';
			$zzM = fractal_zip_multishift_zigzag_decode($blob, $pos);
			$pos = (int) $zzM[1];
			$member = (int) $zzM[0];
			$node = array('id' => $id, 'type' => $type, 'payload_off' => 0, 'payload_len' => 0);
			if ($member >= 0) {
				$node['member'] = $member;
			}
			if ($type === 'variation') {
				$node['var_count'] = $readU();
			} elseif ($type === 'delta') {
				$zzS = fractal_zip_multishift_zigzag_decode($blob, $pos);
				$pos = (int) $zzS[1];
				$node['shift'] = (int) $zzS[0];
				$node['member_len'] = $readU();
			} else {
				$readU();
			}
			$node['payload_off'] = $readU();
			$node['payload_len'] = $readU();
			$nodes[] = $node;
		}
		$edgeCount = $readU();
		$edges = array();
		for ($ei = 0; $ei < $edgeCount; $ei++) {
			$from = $readU();
			$to = $readU();
			$zzE = fractal_zip_multishift_zigzag_decode($blob, $pos);
			$pos = (int) $zzE[1];
			$edges[] = array('from' => $from, 'to' => $to, 'member' => (int) $zzE[0]);
		}
		$pathCount = $readU();
		$paths = array();
		for ($pi = 0; $pi < $pathCount; $pi++) {
			$member = $readU();
			$nc = $readU();
			$pnodes = array();
			for ($ni = 0; $ni < $nc; $ni++) {
				$pnodes[] = $readU();
			}
			$paths[] = array('member' => $member, 'nodes' => $pnodes);
		}
		return array('nodes' => $nodes, 'edges' => $edges, 'paths' => $paths, 'outboard' => true);
	}
	if (strncmp($blob, FRACTAL_ZIP_BIO_PANGENOME_MAGIC, strlen(FRACTAL_ZIP_BIO_PANGENOME_MAGIC)) === 0) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';
		$byteToType = array(0 => 'core', 1 => 'raw', 2 => 'variation', 3 => 'delta');
		$pos = strlen(FRACTAL_ZIP_BIO_PANGENOME_MAGIC);
		$readU = static function () use ($blob, &$pos): int {
			$d = fractal_zip_enwik_decode_varint_u32($blob, $pos);
			$pos = (int) $d[1];
			return (int) $d[0];
		};
		$nodeCount = $readU();
		$nodes = array();
		for ($ni = 0; $ni < $nodeCount; $ni++) {
			$id = $readU();
			$tb = ord($blob[$pos]);
			$pos++;
			$type = $byteToType[$tb] ?? 'raw';
			$zzM = fractal_zip_multishift_zigzag_decode($blob, $pos);
			$pos = (int) $zzM[1];
			$member = (int) $zzM[0];
			$node = array('id' => $id, 'type' => $type, 'seq' => '');
			if ($member >= 0) {
				$node['member'] = $member;
			}
			if ($type === 'variation') {
				$node['var_count'] = $readU();
			} elseif ($type === 'delta') {
				$zzS = fractal_zip_multishift_zigzag_decode($blob, $pos);
				$pos = (int) $zzS[1];
				$node['shift'] = (int) $zzS[0];
				$node['member_len'] = $readU();
			} else {
				$readU();
			}
			$seqLen = $readU();
			$node['seq'] = substr($blob, $pos, $seqLen);
			$pos += $seqLen;
			$nodes[] = $node;
		}
		$edgeCount = $readU();
		$edges = array();
		for ($ei = 0; $ei < $edgeCount; $ei++) {
			$from = $readU();
			$to = $readU();
			$zzE = fractal_zip_multishift_zigzag_decode($blob, $pos);
			$pos = (int) $zzE[1];
			$edges[] = array('from' => $from, 'to' => $to, 'member' => (int) $zzE[0]);
		}
		$pathCount = $readU();
		$paths = array();
		for ($pi = 0; $pi < $pathCount; $pi++) {
			$member = $readU();
			$nc = $readU();
			$pnodes = array();
			for ($ni = 0; $ni < $nc; $ni++) {
				$pnodes[] = $readU();
			}
			$paths[] = array('member' => $member, 'nodes' => $pnodes);
		}
		return array('nodes' => $nodes, 'edges' => $edges, 'paths' => $paths);
	}
	if (strncmp($blob, FRACTAL_ZIP_BIO_MAGIC, strlen(FRACTAL_ZIP_BIO_MAGIC)) === 0) {
		return fractal_zip_bio_pangenome_unpack_legacy_json($blob);
	}
	throw new RuntimeException('bio: bad FZBI magic');
}
