<?php

declare(strict_types=1);

/**
 * Multishift: a deterministic cross-page alignment transform for fractal_zip.
 *
 * For n similar pages, slide each page's offset so characters/words shared
 * between pages land at the same position, then store the residual columnar
 * (transpose) or as a delta-from-reference run stream so the outer codec sees
 * long aligned runs. Shifts are data-derived (mode of anchor offset-deltas),
 * scored by real (proxy) compression, culled top-K, and fully reversible via a
 * compact FZMS sidecar.
 *
 * Reversibility is self-contained: the FZMS sidecar plus the residual blob are
 * sufficient to reconstruct every page byte-exactly. Nothing is read from the
 * original page text at decode time (mirrors how mi_line_stripe stores its
 * line_counts in meta).
 *
 * @see fractal_zip_enwik_text_layout_apply()
 * @see fractal_zip_enwik_text_layout_undo_chunks()
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

const FRACTAL_ZIP_MULTISHIFT_MAGIC = "FZMS\x01";
const FRACTAL_ZIP_MULTISHIFT_REC_SINGLETON = 0;
const FRACTAL_ZIP_MULTISHIFT_REC_DELTA = 1;
const FRACTAL_ZIP_MULTISHIFT_REC_TRANSPOSE = 2;

/**
 * Zig-zag encode a (possibly negative) integer to an unsigned varint.
 */
function fractal_zip_multishift_zigzag_encode(int $v): string
{
	$zz = $v >= 0 ? ($v << 1) : (((-$v) << 1) - 1);
	return fractal_zip_enwik_encode_varint_u32($zz);
}

/**
 * @return array{0:int,1:int}|null [value, newOffset]
 */
function fractal_zip_multishift_zigzag_decode(string $blob, int $offset): ?array
{
	$dv = fractal_zip_enwik_decode_varint_u32($blob, $offset);
	if ($dv === null) {
		return null;
	}
	$zz = (int) $dv[0];
	$v = ($zz & 1) ? -(($zz + 1) >> 1) : ($zz >> 1);
	return array($v, (int) $dv[1]);
}

/**
 * Build token -> sorted offsets map for a string (anchorMap analogue used by
 * multidiff). Tokens are word-class runs; only tokens at least $minLen long are
 * kept so the shift vote is driven by meaningful anchors, not single letters.
 *
 * @return array<string, list<int>>
 */
function fractal_zip_multishift_anchor_map(string $text, int $minLen = 4): array
{
	$map = array();
	if ($text === '') {
		return $map;
	}
	if (preg_match_all("/[A-Za-z][A-Za-z0-9_\x27]*/", $text, $m, PREG_OFFSET_CAPTURE) < 1) {
		return $map;
	}
	foreach ($m[0] as $tok) {
		$s = (string) $tok[0];
		if (strlen($s) < $minLen) {
			continue;
		}
		$off = (int) $tok[1];
		$map[$s][] = $off;
	}
	return $map;
}

/**
 * Vote on the best rigid shift(s) to align $member onto $ref.
 *
 * Shift D means member position p aligns to ref position p + D, i.e. for shared
 * anchor token at ref offset o_r and member offset o_m, the vote is
 * D = o_r - o_m. The peak of the histogram is the dominant shift (phase
 * correlation over anchors). Returns up to $topK peaks with weighted vote mass.
 *
 * @param array<string, list<int>> $refAnchors precomputed anchor map for $ref
 * @return list<array{shift:int, votes:int, weight:int}> sorted by weight desc
 */
function fractal_zip_multishift_shift_vote(
	string $ref,
	string $member,
	array $refAnchors,
	int $topK = 3,
	int $minLen = 4
): array {
	if ($ref === '' || $member === '') {
		return array(array('shift' => 0, 'votes' => 0, 'weight' => 0));
	}
	if (preg_match_all("/[A-Za-z][A-Za-z0-9_\x27]*/", $member, $m, PREG_OFFSET_CAPTURE) < 1) {
		return array(array('shift' => 0, 'votes' => 0, 'weight' => 0));
	}
	$hist = array();
	$histVotes = array();
	foreach ($m[0] as $tok) {
		$s = (string) $tok[0];
		$tl = strlen($s);
		if ($tl < $minLen || !isset($refAnchors[$s])) {
			continue;
		}
		$om = (int) $tok[1];
		foreach ($refAnchors[$s] as $or) {
			$d = $or - $om;
			$hist[$d] = ($hist[$d] ?? 0) + $tl;
			$histVotes[$d] = ($histVotes[$d] ?? 0) + 1;
		}
	}
	if ($hist === array()) {
		return array(array('shift' => 0, 'votes' => 0, 'weight' => 0));
	}
	arsort($hist);
	$out = array();
	foreach ($hist as $shift => $weight) {
		$out[] = array(
			'shift' => (int) $shift,
			'votes' => (int) ($histVotes[$shift] ?? 0),
			'weight' => (int) $weight,
		);
		if (count($out) >= $topK) {
			break;
		}
	}
	return $out;
}

/**
 * Encode $member as a delta-from-reference run stream at rigid shift $shift.
 * Stream is a sequence of alternating (matchRun, litRun + litBytes), starting
 * with a match run (possibly zero). Self-delimiting given the member length at
 * decode time.
 */
function fractal_zip_multishift_delta_encode(string $ref, string $member, int $shift): string
{
	$out = '';
	$L = strlen($member);
	$R = strlen($ref);
	$p = 0;
	while ($p < $L) {
		$run = 0;
		while ($p + $run < $L) {
			$rp = $p + $run + $shift;
			if ($rp >= 0 && $rp < $R && $member[$p + $run] === $ref[$rp]) {
				$run++;
			} else {
				break;
			}
		}
		$out .= fractal_zip_enwik_encode_varint_u32($run);
		$p += $run;
		if ($p < $L) {
			$lit = 0;
			while ($p + $lit < $L) {
				$rp = $p + $lit + $shift;
				if ($rp >= 0 && $rp < $R && $member[$p + $lit] === $ref[$rp]) {
					break;
				}
				$lit++;
			}
			$out .= fractal_zip_enwik_encode_varint_u32($lit);
			$out .= substr($member, $p, $lit);
			$p += $lit;
		}
	}
	return $out;
}

/**
 * Decode a delta-from-reference run stream, consuming from $blob at $cursor.
 *
 * @param array{0:string,1:int} $cursor [blob, pos] passed by reference via array
 * @return array{0:string,1:int} [memberBytes, newPos]
 */
function fractal_zip_multishift_delta_decode(string $blob, int $pos, string $ref, int $shift, int $memberLen): array
{
	$R = strlen($ref);
	$m = '';
	while (strlen($m) < $memberLen) {
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $pos);
		if ($dv === null) {
			throw new RuntimeException('multishift delta decode: run varint missing');
		}
		$run = (int) $dv[0];
		$pos = (int) $dv[1];
		for ($k = 0; $k < $run; $k++) {
			$p = strlen($m);
			$rp = $p + $shift;
			if ($rp < 0 || $rp >= $R) {
				throw new RuntimeException('multishift delta decode: match out of ref range');
			}
			$m .= $ref[$rp];
		}
		if (strlen($m) >= $memberLen) {
			break;
		}
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $pos);
		if ($dv === null) {
			throw new RuntimeException('multishift delta decode: lit varint missing');
		}
		$lit = (int) $dv[0];
		$pos = (int) $dv[1];
		if ($lit > 0) {
			$m .= substr($blob, $pos, $lit);
			$pos += $lit;
		}
	}
	if (strlen($m) !== $memberLen) {
		throw new RuntimeException('multishift delta decode: length overrun');
	}
	return array($m, $pos);
}

/**
 * Column-transpose encode for a group of members each placed at a rigid shift in
 * a shared reference coordinate space. Only real cells are emitted, column-major.
 *
 * @param list<array{bytes:string, shift:int}> $members
 */
function fractal_zip_multishift_transpose_encode(array $members): string
{
	if ($members === array()) {
		return '';
	}
	$minC = PHP_INT_MAX;
	$maxC = PHP_INT_MIN;
	foreach ($members as $mem) {
		$d = (int) $mem['shift'];
		$len = strlen((string) $mem['bytes']);
		$minC = min($minC, $d);
		$maxC = max($maxC, $d + $len);
	}
	$out = '';
	for ($c = $minC; $c < $maxC; $c++) {
		foreach ($members as $mem) {
			$d = (int) $mem['shift'];
			$local = $c - $d;
			$b = (string) $mem['bytes'];
			if ($local >= 0 && $local < strlen($b)) {
				$out .= $b[$local];
			}
		}
	}
	return $out;
}

/**
 * Decode a column-transposed group.
 *
 * @param list<array{shift:int, len:int}> $members
 * @return array{0:list<string>,1:int} [memberBytesList, newPos]
 */
function fractal_zip_multishift_transpose_decode(string $blob, int $pos, array $members): array
{
	$n = count($members);
	if ($n === 0) {
		return array(array(), $pos);
	}
	$minC = PHP_INT_MAX;
	$maxC = PHP_INT_MIN;
	$bufs = array();
	foreach ($members as $i => $mem) {
		$d = (int) $mem['shift'];
		$len = (int) $mem['len'];
		$minC = min($minC, $d);
		$maxC = max($maxC, $d + $len);
		$bufs[$i] = array_fill(0, $len, '');
	}
	for ($c = $minC; $c < $maxC; $c++) {
		foreach ($members as $i => $mem) {
			$d = (int) $mem['shift'];
			$len = (int) $mem['len'];
			$local = $c - $d;
			if ($local >= 0 && $local < $len) {
				if ($pos >= strlen($blob)) {
					throw new RuntimeException('multishift transpose decode: blob underrun');
				}
				$bufs[$i][$local] = $blob[$pos];
				$pos++;
			}
		}
	}
	$out = array();
	foreach ($bufs as $i => $cells) {
		$out[$i] = implode('', $cells);
	}
	return array($out, $pos);
}

/**
 * gzip-1 proxy size: cheap stand-in for "real compression" used to score and
 * cull candidates (same proxy multidiff uses before exact scoring).
 */
function fractal_zip_multishift_proxy_size(string $s): int
{
	if ($s === '') {
		return 0;
	}
	$z = gzdeflate($s, 1);
	return $z === false ? strlen($s) : strlen($z);
}

/**
 * Word-set Jaccard similarity used to seed grouping (reuse the enwik helper if
 * present; fall back to a local minhash-free implementation).
 */
function fractal_zip_multishift_jaccard(string $a, string $b): float
{
	if (function_exists('fractal_zip_enwik_text_page_word_jaccard')) {
		return (float) fractal_zip_enwik_text_page_word_jaccard($a, $b);
	}
	$wa = array_fill_keys(fractal_zip_enwik_text_tokenize_words($a), true);
	$wb = array_fill_keys(fractal_zip_enwik_text_tokenize_words($b), true);
	if ($wa === array() && $wb === array()) {
		return 0.0;
	}
	$inter = count(array_intersect_key($wa, $wb));
	$union = count($wa + $wb);
	return $union > 0 ? $inter / $union : 0.0;
}

/**
 * Default multishift options.
 *
 * @param array<string,mixed> $opts
 * @return array<string,mixed>
 */
function fractal_zip_multishift_opts(array $opts = array()): array
{
	return array(
		'min_anchor' => (int) ($opts['min_anchor'] ?? 4),
		'jaccard_min' => (float) ($opts['jaccard_min'] ?? 0.30),
		'max_group' => max(2, (int) ($opts['max_group'] ?? 8)),
		'shift_topk' => max(1, (int) ($opts['shift_topk'] ?? 3)),
		'min_votes' => max(1, (int) ($opts['min_votes'] ?? 2)),
		'max_depth' => max(1, (int) ($opts['max_depth'] ?? 1)),
		'force_transpose' => (bool) ($opts['force_transpose'] ?? false),
		'window' => max(1, (int) ($opts['window'] ?? 64)),
	);
}

/**
 * Greedy similarity grouping seeded by mi-order + Jaccard. Returns groups of
 * original page indices; the first index in each group is its reference.
 *
 * @param list<array{text:string}> $pages
 * @param list<int> $candidateIdx subset of page indices to group (in cluster order)
 * @param array<string,mixed> $o resolved opts
 * @return list<list<int>>
 */
function fractal_zip_multishift_form_groups(array $pages, array $candidateIdx, array $o): array
{
	$used = array();
	$groups = array();
	$count = count($candidateIdx);
	for ($i = 0; $i < $count; $i++) {
		$refIdx = $candidateIdx[$i];
		if (isset($used[$refIdx])) {
			continue;
		}
		$used[$refIdx] = true;
		$group = array($refIdx);
		$refText = (string) ($pages[$refIdx]['text'] ?? '');
		$refAnchors = fractal_zip_multishift_anchor_map($refText, (int) $o['min_anchor']);
		$scanEnd = min($count, $i + 1 + (int) $o['window']);
		for ($j = $i + 1; $j < $scanEnd; $j++) {
			if (count($group) >= (int) $o['max_group']) {
				break;
			}
			$candIdx = $candidateIdx[$j];
			if (isset($used[$candIdx])) {
				continue;
			}
			$candText = (string) ($pages[$candIdx]['text'] ?? '');
			if (fractal_zip_multishift_jaccard($refText, $candText) < (float) $o['jaccard_min']) {
				continue;
			}
			$peaks = fractal_zip_multishift_shift_vote(
				$refText,
				$candText,
				$refAnchors,
				(int) $o['shift_topk'],
				(int) $o['min_anchor']
			);
			if (($peaks[0]['votes'] ?? 0) < (int) $o['min_votes']) {
				continue;
			}
			$group[] = $candIdx;
			$used[$candIdx] = true;
		}
		$groups[] = $group;
	}
	return $groups;
}

/**
 * Pick the best shift for a member against a reference: try each voted peak plus
 * 0, choose the one whose delta stream has the smallest gzip-1 proxy size.
 *
 * @param list<array{shift:int,votes:int,weight:int}> $peaks
 * @return array{shift:int, delta:string, proxy:int}
 */
function fractal_zip_multishift_best_member_shift(string $ref, string $member, array $peaks): array
{
	$candidates = array(0);
	foreach ($peaks as $pk) {
		$candidates[] = (int) $pk['shift'];
	}
	$candidates = array_values(array_unique($candidates));
	$best = null;
	foreach ($candidates as $shift) {
		$delta = fractal_zip_multishift_delta_encode($ref, $member, $shift);
		$proxy = fractal_zip_multishift_proxy_size($delta);
		if ($best === null || $proxy < $best['proxy']) {
			$best = array('shift' => $shift, 'delta' => $delta, 'proxy' => $proxy);
		}
	}
	/** @var array{shift:int, delta:string, proxy:int} $best */
	return $best;
}

/**
 * Build a multishift residual blob + FZMS sidecar from page texts.
 *
 * @param list<array{text:string}> $pages
 * @param array<string,mixed> $opts
 * @return array{blob:string, fzms:string, records:list<array<string,mixed>>, stats:array<string,mixed>}
 */
function fractal_zip_multishift_build(array $pages, array $opts = array()): array
{
	$o = fractal_zip_multishift_opts($opts);
	$n = count($pages);

	$clusterOrder = array();
	if ($n > 1 && function_exists('fractal_zip_enwik_text_mi_reorder_perm')) {
		$perm = fractal_zip_enwik_text_mi_reorder_perm($pages);
		if (is_array($perm) && count($perm) === $n) {
			foreach ($perm as $idx) {
				$clusterOrder[] = (int) $idx;
			}
		}
	}
	if ($clusterOrder === array()) {
		$clusterOrder = range(0, max(0, $n - 1));
	}

	$groups = fractal_zip_multishift_form_groups($pages, $clusterOrder, $o);

	// Recursion (sub-multishift): re-group the leftover singletons with a looser
	// threshold up to max_depth, so self-similar residual pages can still align.
	$depth = 1;
	$finalGroups = array();
	$pending = array();
	foreach ($groups as $g) {
		if (count($g) > 1) {
			$finalGroups[] = $g;
		} else {
			$pending[] = $g[0];
		}
	}
	while ($depth < (int) $o['max_depth'] && count($pending) > 1) {
		$looser = $o;
		$looser['jaccard_min'] = max(0.05, (float) $o['jaccard_min'] * 0.5);
		$looser['min_votes'] = max(1, (int) $o['min_votes'] - 1);
		$sub = fractal_zip_multishift_form_groups($pages, $pending, $looser);
		$pending = array();
		$progressed = false;
		foreach ($sub as $g) {
			if (count($g) > 1) {
				$finalGroups[] = $g;
				$progressed = true;
			} else {
				$pending[] = $g[0];
			}
		}
		$depth++;
		if (!$progressed) {
			break;
		}
	}
	foreach ($pending as $idx) {
		$finalGroups[] = array($idx);
	}

	$blob = '';
	$records = array();
	$groupsFormed = 0;
	$transposeChosen = 0;
	foreach ($finalGroups as $group) {
		if (count($group) === 1) {
			$idx = (int) $group[0];
			$t = (string) ($pages[$idx]['text'] ?? '');
			$blob .= $t;
			$records[] = array(
				'kind' => FRACTAL_ZIP_MULTISHIFT_REC_SINGLETON,
				'members' => array(array('idx' => $idx, 'shift' => 0, 'len' => strlen($t))),
			);
			continue;
		}
		$groupsFormed++;
		$refIdx = (int) $group[0];
		$refText = (string) ($pages[$refIdx]['text'] ?? '');
		$refAnchors = fractal_zip_multishift_anchor_map($refText, (int) $o['min_anchor']);

		$members = array(array('idx' => $refIdx, 'shift' => 0, 'len' => strlen($refText), 'bytes' => $refText));
		$deltas = array();
		for ($gi = 1; $gi < count($group); $gi++) {
			$idx = (int) $group[$gi];
			$mt = (string) ($pages[$idx]['text'] ?? '');
			$peaks = fractal_zip_multishift_shift_vote(
				$refText,
				$mt,
				$refAnchors,
				(int) $o['shift_topk'],
				(int) $o['min_anchor']
			);
			$best = fractal_zip_multishift_best_member_shift($refText, $mt, $peaks);
			$members[] = array('idx' => $idx, 'shift' => (int) $best['shift'], 'len' => strlen($mt), 'bytes' => $mt);
			$deltas[$idx] = $best['delta'];
		}

		// delta-mode segment: ref verbatim ++ per-member delta streams.
		$deltaSeg = $refText;
		for ($gi = 1; $gi < count($members); $gi++) {
			$deltaSeg .= $deltas[(int) $members[$gi]['idx']];
		}
		// transpose-mode segment: all members as cells in shared coordinate space.
		$transSeg = fractal_zip_multishift_transpose_encode($members);

		$useTranspose = (bool) $o['force_transpose'];
		if (!$useTranspose) {
			$useTranspose = fractal_zip_multishift_proxy_size($transSeg)
				< fractal_zip_multishift_proxy_size($deltaSeg);
		}
		if ($useTranspose) {
			$transposeChosen++;
			$blob .= $transSeg;
			$recMembers = array();
			foreach ($members as $mem) {
				$recMembers[] = array('idx' => (int) $mem['idx'], 'shift' => (int) $mem['shift'], 'len' => (int) $mem['len']);
			}
			$records[] = array('kind' => FRACTAL_ZIP_MULTISHIFT_REC_TRANSPOSE, 'members' => $recMembers);
		} else {
			$blob .= $deltaSeg;
			$recMembers = array();
			foreach ($members as $mem) {
				$recMembers[] = array('idx' => (int) $mem['idx'], 'shift' => (int) $mem['shift'], 'len' => (int) $mem['len']);
			}
			$records[] = array('kind' => FRACTAL_ZIP_MULTISHIFT_REC_DELTA, 'members' => $recMembers);
		}
	}

	$fzms = fractal_zip_multishift_pack_fzms($n, $records);
	$stats = array(
		'pages' => $n,
		'groups_formed' => $groupsFormed,
		'transpose_groups' => $transposeChosen,
		'records' => count($records),
		'blob_bytes' => strlen($blob),
		'fzms_bytes' => strlen($fzms),
	);
	return array('blob' => $blob, 'fzms' => $fzms, 'records' => $records, 'stats' => $stats);
}

/**
 * Beam over grouping regimes: {few-very-similar} (tight Jaccard, small groups)
 * vs {many-loosely} (loose Jaccard, large groups). Each candidate build is
 * scored by gzip-1 proxy of residual blob + FZMS sidecar (the cull metric); the
 * smallest scored candidate wins. Deterministic: configs are fixed and ordered.
 *
 * @param list<array{text:string}> $pages
 * @param array<string,mixed> $opts
 * @return array{blob:string, fzms:string, records:list<array<string,mixed>>, stats:array<string,mixed>, beam:list<array<string,mixed>>}
 */
function fractal_zip_multishift_build_beam(array $pages, array $opts = array()): array
{
	$configs = isset($opts['beam']) && is_array($opts['beam']) ? $opts['beam'] : array(
		array('jaccard_min' => 0.45, 'max_group' => 4),
		array('jaccard_min' => 0.30, 'max_group' => 8),
		array('jaccard_min' => 0.18, 'max_group' => 16),
	);
	$best = null;
	$beamReport = array();
	foreach ($configs as $ci => $cfg) {
		$candOpts = array_merge($opts, is_array($cfg) ? $cfg : array());
		unset($candOpts['beam']);
		$built = fractal_zip_multishift_build($pages, $candOpts);
		$score = fractal_zip_multishift_proxy_size((string) $built['blob'])
			+ fractal_zip_multishift_proxy_size((string) $built['fzms']);
		$beamReport[] = array(
			'config' => array('jaccard_min' => $candOpts['jaccard_min'] ?? null, 'max_group' => $candOpts['max_group'] ?? null),
			'score' => $score,
			'groups_formed' => $built['stats']['groups_formed'] ?? 0,
		);
		if ($best === null || $score < $best['score']) {
			$best = array('built' => $built, 'score' => $score, 'ci' => $ci);
		}
	}
	/** @var array{built:array<string,mixed>, score:int, ci:int} $best */
	$out = $best['built'];
	$out['beam'] = $beamReport;
	$out['stats']['beam_winner'] = $best['ci'];
	return $out;
}

/**
 * Pack records into the compact binary FZMS sidecar.
 *
 * @param list<array<string,mixed>> $records
 */
function fractal_zip_multishift_pack_fzms(int $n, array $records): string
{
	$out = FRACTAL_ZIP_MULTISHIFT_MAGIC;
	$out .= fractal_zip_enwik_encode_varint_u32($n);
	$out .= fractal_zip_enwik_encode_varint_u32(count($records));
	foreach ($records as $rec) {
		$kind = (int) $rec['kind'];
		$members = $rec['members'];
		$out .= chr($kind);
		if ($kind === FRACTAL_ZIP_MULTISHIFT_REC_SINGLETON) {
			$m = $members[0];
			$out .= fractal_zip_enwik_encode_varint_u32((int) $m['idx']);
			$out .= fractal_zip_enwik_encode_varint_u32((int) $m['len']);
			continue;
		}
		$out .= fractal_zip_enwik_encode_varint_u32(count($members));
		if ($kind === FRACTAL_ZIP_MULTISHIFT_REC_DELTA) {
			$ref = $members[0];
			$out .= fractal_zip_enwik_encode_varint_u32((int) $ref['idx']);
			$out .= fractal_zip_enwik_encode_varint_u32((int) $ref['len']);
			for ($i = 1; $i < count($members); $i++) {
				$m = $members[$i];
				$out .= fractal_zip_enwik_encode_varint_u32((int) $m['idx']);
				$out .= fractal_zip_multishift_zigzag_encode((int) $m['shift']);
				$out .= fractal_zip_enwik_encode_varint_u32((int) $m['len']);
			}
		} else {
			foreach ($members as $m) {
				$out .= fractal_zip_enwik_encode_varint_u32((int) $m['idx']);
				$out .= fractal_zip_multishift_zigzag_encode((int) $m['shift']);
				$out .= fractal_zip_enwik_encode_varint_u32((int) $m['len']);
			}
		}
	}
	return $out;
}

/**
 * Parse FZMS sidecar.
 *
 * @return array{n:int, records:list<array<string,mixed>>}
 */
function fractal_zip_multishift_unpack_fzms(string $fzms): array
{
	$ml = strlen(FRACTAL_ZIP_MULTISHIFT_MAGIC);
	if (strncmp($fzms, FRACTAL_ZIP_MULTISHIFT_MAGIC, $ml) !== 0) {
		throw new RuntimeException('multishift: bad FZMS magic');
	}
	$pos = $ml;
	$dv = fractal_zip_enwik_decode_varint_u32($fzms, $pos);
	if ($dv === null) {
		throw new RuntimeException('multishift: FZMS n missing');
	}
	$n = (int) $dv[0];
	$pos = (int) $dv[1];
	$dv = fractal_zip_enwik_decode_varint_u32($fzms, $pos);
	if ($dv === null) {
		throw new RuntimeException('multishift: FZMS recCount missing');
	}
	$recCount = (int) $dv[0];
	$pos = (int) $dv[1];
	$records = array();
	$readU = static function () use ($fzms, &$pos): int {
		$d = fractal_zip_enwik_decode_varint_u32($fzms, $pos);
		if ($d === null) {
			throw new RuntimeException('multishift: FZMS varint underrun');
		}
		$pos = (int) $d[1];
		return (int) $d[0];
	};
	$readZ = static function () use ($fzms, &$pos): int {
		$d = fractal_zip_multishift_zigzag_decode($fzms, $pos);
		if ($d === null) {
			throw new RuntimeException('multishift: FZMS zigzag underrun');
		}
		$pos = (int) $d[1];
		return (int) $d[0];
	};
	for ($r = 0; $r < $recCount; $r++) {
		if ($pos >= strlen($fzms)) {
			throw new RuntimeException('multishift: FZMS kind underrun');
		}
		$kind = ord($fzms[$pos]);
		$pos++;
		if ($kind === FRACTAL_ZIP_MULTISHIFT_REC_SINGLETON) {
			$idx = $readU();
			$len = $readU();
			$records[] = array('kind' => $kind, 'members' => array(array('idx' => $idx, 'shift' => 0, 'len' => $len)));
			continue;
		}
		$mc = $readU();
		$members = array();
		if ($kind === FRACTAL_ZIP_MULTISHIFT_REC_DELTA) {
			$refIdx = $readU();
			$refLen = $readU();
			$members[] = array('idx' => $refIdx, 'shift' => 0, 'len' => $refLen);
			for ($i = 1; $i < $mc; $i++) {
				$idx = $readU();
				$shift = $readZ();
				$len = $readU();
				$members[] = array('idx' => $idx, 'shift' => $shift, 'len' => $len);
			}
		} else {
			for ($i = 0; $i < $mc; $i++) {
				$idx = $readU();
				$shift = $readZ();
				$len = $readU();
				$members[] = array('idx' => $idx, 'shift' => $shift, 'len' => $len);
			}
		}
		$records[] = array('kind' => $kind, 'members' => $members);
	}
	return array('n' => $n, 'records' => $records);
}

/**
 * Reconstruct per-page texts (original page order) from residual blob + FZMS.
 *
 * @return list<string>
 */
function fractal_zip_multishift_undo(string $blob, string $fzms): array
{
	$parsed = fractal_zip_multishift_unpack_fzms($fzms);
	$n = (int) $parsed['n'];
	$out = array_fill(0, max(0, $n), '');
	$pos = 0;
	foreach ($parsed['records'] as $rec) {
		$kind = (int) $rec['kind'];
		$members = $rec['members'];
		if ($kind === FRACTAL_ZIP_MULTISHIFT_REC_SINGLETON) {
			$m = $members[0];
			$len = (int) $m['len'];
			$out[(int) $m['idx']] = substr($blob, $pos, $len);
			$pos += $len;
			continue;
		}
		if ($kind === FRACTAL_ZIP_MULTISHIFT_REC_DELTA) {
			$ref = $members[0];
			$refLen = (int) $ref['len'];
			$refBytes = substr($blob, $pos, $refLen);
			$pos += $refLen;
			$out[(int) $ref['idx']] = $refBytes;
			for ($i = 1; $i < count($members); $i++) {
				$m = $members[$i];
				$dec = fractal_zip_multishift_delta_decode($blob, $pos, $refBytes, (int) $m['shift'], (int) $m['len']);
				$out[(int) $m['idx']] = $dec[0];
				$pos = (int) $dec[1];
			}
			continue;
		}
		// transpose
		$transMembers = array();
		foreach ($members as $m) {
			$transMembers[] = array('shift' => (int) $m['shift'], 'len' => (int) $m['len']);
		}
		$dec = fractal_zip_multishift_transpose_decode($blob, $pos, $transMembers);
		$pos = (int) $dec[1];
		foreach ($members as $i => $m) {
			$out[(int) $m['idx']] = $dec[0][$i];
		}
	}
	return $out;
}

/**
 * Account net bytes for a multishift candidate against the mono-concat baseline.
 *
 * @param list<array{text:string}> $pages
 * @param callable(string):int $outerBytes maps a blob to its compressed size
 * @param array<string,mixed> $opts
 * @return array<string,mixed>
 */
function fractal_zip_multishift_account(array $pages, callable $outerBytes, array $opts = array()): array
{
	$baselineBlob = '';
	foreach ($pages as $p) {
		$baselineBlob .= (string) ($p['text'] ?? '');
	}
	$baseBytes = $outerBytes($baselineBlob);

	$built = fractal_zip_multishift_build($pages, $opts);
	$payloadBytes = $outerBytes((string) $built['blob']);
	$sidecarBytes = strlen((string) $built['fzms']);
	$sidecarOuter = $outerBytes((string) $built['fzms']);

	$ledgerNet = $baseBytes - $payloadBytes;
	$ltcbTotal = $payloadBytes + $sidecarBytes;
	$ltcbNet = $baseBytes - $ltcbTotal;

	return array(
		'baseline_bytes' => $baseBytes,
		'payload_bytes' => $payloadBytes,
		'sidecar_bytes' => $sidecarBytes,
		'sidecar_outer_bytes' => $sidecarOuter,
		'ledger_net' => $ledgerNet,
		'ltcb_total' => $ltcbTotal,
		'ltcb_net' => $ltcbNet,
		'pass' => $ltcbNet > 0,
		'stats' => $built['stats'],
	);
}
