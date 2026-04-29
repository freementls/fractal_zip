<?php
declare(strict_types=1);

/**
 * Tiered binary/text format identification from path + raw bytes.
 *
 * Tiers (easiest → hardest):
 * 1) File extension → cheap expected family (may be wrong if spoofed).
 * 2) Extension-correlated internal markers only (JFIF for .jpg, PK for .zip, …).
 * 3) Broad well-known magic at file start (and a few tiny offsets) when tier-2 inconclusive.
 * 4) Content heuristics (troff lines, BibTeX %, C comment, UTF-8, printable ratio, …).
 *
 * Aligns with the spirit of sibling ../file_types/files.php (extension metadata) plus magic sniffing;
 * suitable for driving unwrap / transform paths when extension alone is unreliable (e.g. Calgary corpus).
 *
 * **Marker / limiter associations (separate tiered track, evidence-gated):**
 * - Tier A: From the resolved format profile (derived from the identification tiers above — never raw extension alone),
 *   look up *hypothetical* delimiter sets commonly used in that family (HTML-ish → angle brackets; C-like → ()[]{}, …).
 * - Tier B: Verify each hypothesis against **bytes** (counts, rough balance, minimum presence). Failed hypotheses are dropped.
 * - Tier C: If all type-linked pairs fail, scan standard bracket pairs on the same bytes and keep the strongest *verified* pair.
 * - Tier D: Single-character limiters from a safe alphabet, ordered by `substr_count` in the sample (always evidence).
 * These fields are hints for callers (e.g. adaptive marker tuning); they are **not** assumed true without tier B/C checks.
 */
final class fractal_zip_content_format_identify {
	/** @var list<array{0:int,1:string,2:string,3:string}> offset, ascii prefix, mime-ish label, tier note */
	private const GLOBAL_PREFIXES = array(
		array(0, "\x89PNG\r\n\x1a\n", 'image/png', 'magic'),
		array(0, 'GIF87a', 'image/gif', 'magic'),
		array(0, 'GIF89a', 'image/gif', 'magic'),
		array(0, "\xff\xd8\xff", 'image/jpeg', 'magic'),
		array(0, 'BM', 'image/bmp', 'magic'),
		array(0, 'II*', 'image/tiff', 'magic'),
		array(0, 'MM*', 'image/tiff', 'magic'),
		array(0, '%PDF', 'application/pdf', 'magic'),
		array(0, "\x7fELF", 'application/x-elf', 'magic'),
		array(0, "PK\x03\x04", 'application/zip', 'magic'),
		array(0, "PK\x05\x06", 'application/zip', 'magic'),
		array(0, "PK\x07\x08", 'application/zip', 'magic'),
		array(0, "\x1f\x8b", 'application/gzip', 'magic'),
		array(0, "BZh", 'application/x-bzip2', 'magic'),
		array(0, "\x28\xb5\x2f\xfd", 'application/zstd', 'magic'),
		array(0, "\xfd7zXZ\x00", 'application/x-xz', 'magic'),
		array(0, "7z\xbc\xaf\x27\x1c", 'application/x-7z-compressed', 'magic'),
		array(0, 'OggS', 'application/ogg', 'magic'),
		array(0, 'ID3', 'audio/mpeg_with_id3', 'magic'),
		array(0, "\xff\xfb", 'audio/mpeg', 'magic'),
		array(0, "\xff\xf3", 'audio/mpeg', 'magic'),
		array(0, "\xff\xf2", 'audio/mpeg', 'magic'),
		array(0, 'fLaC', 'audio/flac', 'magic'),
		array(0, 'RIFF', 'audio/wav_or_webp', 'magic_riff'),
		array(0, '{\\rtf', 'application/rtf', 'magic'),
		array(0, 'MZ', 'application/x-msdos', 'magic'),
	);

	/**
	 * @return array<string, list<array{0:int,1:string,2:string}>> extension lower → list of [offset, prefix, label]
	 */
	private static function extensionMarkerExpectations(): array {
		return array(
			'jpg' => array(array(0, "\xff\xd8\xff", 'image/jpeg')),
			'jpeg' => array(array(0, "\xff\xd8\xff", 'image/jpeg')),
			'jpe' => array(array(0, "\xff\xd8\xff", 'image/jpeg')),
			'png' => array(array(0, "\x89PNG\r\n\x1a\n", 'image/png')),
			'gif' => array(array(0, 'GIF87a', 'image/gif'), array(0, 'GIF89a', 'image/gif')),
			'webp' => array(array(0, 'RIFF', 'image/webp')),
			'bmp' => array(array(0, 'BM', 'image/bmp')),
			'tif' => array(array(0, 'II*', 'image/tiff'), array(0, 'MM*', 'image/tiff')),
			'tiff' => array(array(0, 'II*', 'image/tiff'), array(0, 'MM*', 'image/tiff')),
			'gz' => array(array(0, "\x1f\x8b", 'application/gzip')),
			'svgz' => array(array(0, "\x1f\x8b", 'application/gzip')),
			'vgz' => array(array(0, "\x1f\x8b", 'application/gzip')),
			'zip' => array(array(0, "PK\x03\x04", 'application/zip')),
			'jar' => array(array(0, "PK\x03\x04", 'application/zip')),
			'docx' => array(array(0, "PK\x03\x04", 'application/zip')),
			'xlsx' => array(array(0, "PK\x03\x04", 'application/zip')),
			'pptx' => array(array(0, "PK\x03\x04", 'application/zip')),
			'odt' => array(array(0, "PK\x03\x04", 'application/zip')),
			'pdf' => array(array(0, '%PDF', 'application/pdf')),
			'html' => array(array(0, '<!DOCTYPE', 'text/html'), array(0, '<html', 'text/html'), array(0, '<HTML', 'text/html')),
			'htm' => array(array(0, '<!DOCTYPE', 'text/html'), array(0, '<html', 'text/html')),
			'xhtml' => array(array(0, '<?xml', 'application/xhtml+xml')),
			'xml' => array(array(0, '<?xml', 'application/xml')),
			'wasm' => array(array(0, "\x00asm", 'application/wasm')),
			'mp3' => array(array(0, 'ID3', 'audio/mpeg'), array(0, "\xff\xfb", 'audio/mpeg')),
			'flac' => array(array(0, 'fLaC', 'audio/flac')),
			'ogg' => array(array(0, 'OggS', 'application/ogg')),
			'ps' => array(array(0, '%!', 'application/postscript')),
			'eps' => array(array(0, '%!', 'application/postscript')),
		);
	}

	/**
	 * @return array{
	 *   path: string,
	 *   size: int,
	 *   extension: string,
	 *   tier1_label: string,
	 *   tier2_match: ?bool,
	 *   tier2_detail: string,
	 *   tier3_label: string,
	 *   tier4_label: string,
	 *   final_label: string,
	 *   confidence: string,
	 *   tier_resolved: int,
	 *   delimiter_association: array{
	 *     profile: string,
	 *     tier_a_hypothesis: array{common_limiter_pairs: list<array{0:string,1:string}>, common_limiters: list<string>, mid_candidates: list<string>, source: string},
	 *     tier_b_verified_pairs: list<array{pair: array{0:string,1:string}, open_count: int, close_count: int, passed: bool, reason: string}>,
	 *     tier_b_verified_limiters: list<array{char: string, count: int, passed: bool, reason: string}>,
	 *     tier_c_broad_pair: ?array{pair: array{0:string,1:string}, open_count: int, close_count: int, passed: bool, reason: string},
	 *     tier_d_freq_limiters: list<string>,
	 *     mid_evidence: list<array{mid: string, count: int, passed: bool, reason: string}>,
	 *     recommendation: array{common_limiter_pairs: list<array{0:string,1:string}>, common_limiters: list<string>}
	 *   }
	 * }
	 */
	public static function identify(string $relativePath, string $bytes): array {
		$bytes = (string) $bytes;
		$n = strlen($bytes);
		$ext = strtolower((string) pathinfo($relativePath, PATHINFO_EXTENSION));
		$base = strtolower((string) pathinfo($relativePath, PATHINFO_FILENAME));
		$tier1 = $ext !== '' ? ('extension.' . $ext) : ('extension.none basename.' . $base);

		$tier2Match = null;
		$tier2Detail = 'skipped_no_extension_expectations';
		$exp = self::extensionMarkerExpectations();
		if ($ext !== '' && isset($exp[$ext])) {
			$tier2Detail = 'checked_' . count($exp[$ext]) . '_markers';
			foreach ($exp[$ext] as $tri) {
				$off = (int) $tri[0];
				$pre = (string) $tri[1];
				if ($n >= $off + strlen($pre) && substr($bytes, $off, strlen($pre)) === $pre) {
					$tier2Match = true;
					$tier2Detail = 'agrees:' . $tri[2];
					break;
				}
			}
			if ($tier2Match === null) {
				$tier2Match = false;
				$tier2Detail = 'extension_marker_mismatch';
			}
		}

		$tier3Label = '';
		if ($tier2Match !== true) {
			$tier3Label = self::matchGlobalPrefixes($bytes);
		}

		$tier4Label = self::heuristicLabel($bytes, $relativePath);

		$tierResolved = 1;
		$final = $tier1;
		$conf = 'low';

		if ($tier2Match === true) {
			$tierResolved = 2;
			$final = $tier2Detail;
			$conf = 'high';
		} elseif ($tier3Label !== '') {
			$tierResolved = 3;
			$final = $tier3Label;
			$conf = 'medium';
		} elseif ($tier4Label !== '') {
			$tierResolved = 4;
			$final = $tier4Label;
			$conf = 'medium';
			if ($tier2Match === null && self::tier4IsWeakGeneric($tier4Label)) {
				$conf = 'medium-low';
			}
		}

		if ($tier2Match === false && $tier3Label === '' && $tier4Label === '') {
			$final = 'unknown_binary_or_empty';
			$conf = 'low';
			$tierResolved = max($tierResolved, 2);
		}

		$row = array(
			'path' => $relativePath,
			'size' => $n,
			'extension' => $ext,
			'tier1_label' => $tier1,
			'tier2_match' => $tier2Match,
			'tier2_detail' => $tier2Detail,
			'tier3_label' => $tier3Label,
			'tier4_label' => $tier4Label,
			'final_label' => $final,
			'confidence' => $conf,
			'tier_resolved' => $tierResolved,
		);
		$row['delimiter_association'] = self::delimiterAssociationTiered($bytes, $row);
		return $row;
	}

	/**
	 * @param array{final_label: string, tier3_label: string, extension: string, tier_resolved: int} $identifyRow
	 * @return array{
	 *   profile: string,
	 *   tier_a_hypothesis: array{common_limiter_pairs: list<array{0:string,1:string}>, common_limiters: list<string>, mid_candidates: list<string>, source: string},
	 *   tier_b_verified_pairs: list<array{pair: array{0:string,1:string}, open_count: int, close_count: int, passed: bool, reason: string}>,
	 *   tier_b_verified_limiters: list<array{char: string, count: int, passed: bool, reason: string}>,
	 *   tier_c_broad_pair: ?array{pair: array{0:string,1:string}, open_count: int, close_count: int, passed: bool, reason: string},
	 *   tier_d_freq_limiters: list<string>,
	 *   mid_evidence: list<array{mid: string, count: int, passed: bool, reason: string}>,
	 *   recommendation: array{common_limiter_pairs: list<array{0:string,1:string}>, common_limiters: list<string>}
	 * }
	 */
	public static function delimiterAssociationTiered(string $bytes, array $identifyRow): array {
		$n = strlen($bytes);
		$profile = self::delimiterProfile($identifyRow);
		if ($profile === 'opaque_binary' || $profile === 'media_binary' || $profile === 'zip_family') {
			$tierDFreq = self::freqLimitersFromBytes($bytes, $n);
			$limFallback = $tierDFreq !== array() ? $tierDFreq : array(',', '.', ';', ':');
			return array(
				'profile' => $profile,
				'tier_a_hypothesis' => array(
					'common_limiter_pairs' => array(),
					'common_limiters' => array(),
					'mid_candidates' => array(),
					'source' => 'binary_or_container_skip_bracket_pairs',
				),
				'tier_b_verified_pairs' => array(),
				'tier_b_verified_limiters' => array(),
				'tier_c_broad_pair' => null,
				'tier_d_freq_limiters' => $tierDFreq,
				'mid_evidence' => array(),
				'recommendation' => array(
					'common_limiter_pairs' => array(),
					'common_limiters' => $limFallback,
				),
			);
		}
		$tierA = self::delimiterHypothesisForProfile($profile);
		$tierA['source'] = 'type_profile:' . $profile;

		$tierBPairs = array();
		foreach ($tierA['common_limiter_pairs'] as $pr) {
			if (!is_array($pr) || !isset($pr[0], $pr[1])) {
				continue;
			}
			$tierBPairs[] = self::verifyBracketPair($bytes, (string) $pr[0], (string) $pr[1], $n);
		}

		$tierBLimiters = array();
		foreach ($tierA['common_limiters'] as $ch) {
			if (!is_string($ch) || strlen($ch) !== 1) {
				continue;
			}
			$tierBLimiters[] = self::verifyLimiterChar($bytes, $ch, $n);
		}

		$tierC = self::bestVerifiedStandardPair($bytes, $n, $tierBPairs);

		$tierDFreq = self::freqLimitersFromBytes($bytes, $n);

		$midEvidence = array();
		foreach ($tierA['mid_candidates'] as $m) {
			if (!is_string($m) || strlen($m) !== 1) {
				continue;
			}
			$c = substr_count($bytes, $m);
			$minMid = max(2, (int) floor($n * 0.00008));
			$pass = $c >= $minMid || ($n < 4096 && $c >= 1);
			$midEvidence[] = array(
				'mid' => $m,
				'count' => $c,
				'passed' => $pass,
				'reason' => $pass ? 'count_threshold' : 'below_threshold',
			);
		}

		$recPairs = array();
		foreach ($tierBPairs as $row) {
			if ($row['passed']) {
				$recPairs[] = $row['pair'];
			}
		}
		if ($recPairs === array() && $tierC !== null && $tierC['passed']) {
			$recPairs[] = $tierC['pair'];
		}

		$recLim = array();
		$recSet = array();
		foreach ($tierBLimiters as $row) {
			if ($row['passed']) {
				$c = $row['char'];
				$recLim[] = $c;
				$recSet[$c] = true;
			}
		}
		foreach ($tierDFreq as $ch) {
			if (!isset($recSet[$ch])) {
				$recLim[] = $ch;
				$recSet[$ch] = true;
			}
		}
		if ($recLim === array()) {
			$recLim = array(',', '.', ';', ':', '/', '\\', '|');
		}

		return array(
			'profile' => $profile,
			'tier_a_hypothesis' => $tierA,
			'tier_b_verified_pairs' => $tierBPairs,
			'tier_b_verified_limiters' => $tierBLimiters,
			'tier_c_broad_pair' => $tierC,
			'tier_d_freq_limiters' => $tierDFreq,
			'mid_evidence' => $midEvidence,
			'recommendation' => array(
				'common_limiter_pairs' => $recPairs,
				'common_limiters' => array_slice($recLim, 0, 12),
			),
		);
	}

	/** @param array{final_label: string, tier3_label: string, extension: string} $identifyRow */
	private static function delimiterProfile(array $identifyRow): string {
		$f = (string) $identifyRow['final_label'];
		$t3 = (string) $identifyRow['tier3_label'];
		if (str_contains($f, 'html') || str_contains($f, 'xml')) {
			return 'markup';
		}
		if (str_contains($f, 'troff')) {
			return 'troff';
		}
		if (str_contains($f, 'c-like')) {
			return 'c_like';
		}
		if (str_contains($f, 'refer') || str_contains($f, 'bibtex')) {
			return 'refer_like';
		}
		if (str_contains($f, 'cnews') || str_contains($f, 'rfc822')) {
			return 'line_oriented_text';
		}
		if (str_contains($f, 'postscript')) {
			return 'postscript';
		}
		if (str_contains($f, 'script_shebang')) {
			return 'script';
		}
		if ($t3 !== '' && str_contains($t3, 'zip')) {
			return 'zip_family';
		}
		if ($t3 !== '' && (str_contains($t3, 'image/') || str_contains($t3, 'audio/') || str_contains($t3, 'video/'))) {
			return 'media_binary';
		}
		if (str_contains($f, 'octet-stream')) {
			return 'opaque_binary';
		}
		if (str_contains($f, 'plain') || str_contains($f, 'empty')) {
			return 'generic_text';
		}
		return 'unknown';
	}

	/**
	 * Tier A only: hypotheses from format family (not read from disk extension in isolation — uses resolved labels).
	 *
	 * @return array{common_limiter_pairs: list<array{0:string,1:string}>, common_limiters: list<string>, mid_candidates: list<string>, source?: string}
	 */
	private static function delimiterHypothesisForProfile(string $profile): array {
		switch ($profile) {
			case 'markup':
				return array(
					'common_limiter_pairs' => array(array('<', '>'), array('(', ')')),
					'common_limiters' => array(',', ';', '=', '&', ':'),
					'mid_candidates' => array('"', "'", '|'),
				);
			case 'c_like':
				return array(
					'common_limiter_pairs' => array(array('(', ')'), array('{', '}'), array('[', ']')),
					'common_limiters' => array(';', ',', ':', '|', '&'),
					'mid_candidates' => array('"', "'", ':'),
				);
			case 'troff':
				return array(
					'common_limiter_pairs' => array(array('(', ')'), array('[', ']'), array('<', '>')),
					'common_limiters' => array('.', ',', ';', ':', '-'),
					'mid_candidates' => array('"', "'", '|'),
				);
			case 'refer_like':
				return array(
					'common_limiter_pairs' => array(array('(', ')')),
					'common_limiters' => array(',', ';', '-', '.', ':'),
					'mid_candidates' => array('"', '|', ':'),
				);
			case 'line_oriented_text':
				return array(
					'common_limiter_pairs' => array(array('<', '>'), array('(', ')')),
					'common_limiters' => array(':', ';', ',', '|'),
					'mid_candidates' => array('"', "'", ':'),
				);
			case 'postscript':
				return array(
					'common_limiter_pairs' => array(array('(', ')'), array('{', '}')),
					'common_limiters' => array(';', ',', ' '),
					'mid_candidates' => array('"', "'"),
				);
			case 'script':
				return array(
					'common_limiter_pairs' => array(array('(', ')'), array('{', '}')),
					'common_limiters' => array(';', ',', ':', '|'),
					'mid_candidates' => array('"', "'", '#'),
				);
			case 'zip_family':
			case 'media_binary':
			case 'opaque_binary':
				return array(
					'common_limiter_pairs' => array(),
					'common_limiters' => array(),
					'mid_candidates' => array(),
				);
			case 'generic_text':
			case 'unknown':
			default:
				return array(
					'common_limiter_pairs' => array(array('<', '>'), array('(', ')')),
					'common_limiters' => array(',', '.', ';', ':', '-', '|'),
					'mid_candidates' => array('"', "'", '|', ':'),
				);
		}
	}

	/**
	 * Core bracket heuristics: same outcomes as {substr_count, open, close} in verifyBracketPair, without scanning $bytes.
	 *
	 * @return array{pair: array{0:string,1:string}, open_count: int, close_count: int, passed: bool, reason: string}
	 */
	private static function verifyBracketPairFromCounts(int $n, int $co, int $cc, string $open, string $close): array {
		$minBoth = min($co, $cc);
		if ($open === $close) {
			return array(
				'pair' => array($open, $close),
				'open_count' => $co,
				'close_count' => $cc,
				'passed' => false,
				'reason' => 'symmetric_pair_not_supported_here',
			);
		}
		$threshold = $n < 4096 ? 1 : max(2, (int) floor($n * 0.00003));
		if ($minBoth < $threshold) {
			return array(
				'pair' => array($open, $close),
				'open_count' => $co,
				'close_count' => $cc,
				'passed' => false,
				'reason' => 'insufficient_count',
			);
		}
		$imb = abs($co - $cc) / (float) max(1, max($co, $cc));
		if ($imb > 0.35) {
			return array(
				'pair' => array($open, $close),
				'open_count' => $co,
				'close_count' => $cc,
				'passed' => false,
				'reason' => 'imbalance',
			);
		}
		return array(
			'pair' => array($open, $close),
			'open_count' => $co,
			'close_count' => $cc,
			'passed' => true,
			'reason' => 'count_and_balance_ok',
		);
	}

	/**
	 * @return array{pair: array{0:string,1:string}, open_count: int, close_count: int, passed: bool, reason: string}
	 */
	private static function verifyBracketPair(string $bytes, string $open, string $close, int $n): array {
		$co = substr_count($bytes, $open);
		$cc = substr_count($bytes, $close);
		return self::verifyBracketPairFromCounts($n, $co, $cc, $open, $close);
	}

	/** @return array{char: string, count: int, passed: bool, reason: string} */
	private static function verifyLimiterChar(string $bytes, string $ch, int $n): array {
		$c = substr_count($bytes, $ch);
		$need = max(3, (int) floor($n * 0.00012));
		if ($c < $need && !($n < 2048 && $c >= 2)) {
			return array('char' => $ch, 'count' => $c, 'passed' => false, 'reason' => 'below_threshold');
		}
		return array('char' => $ch, 'count' => $c, 'passed' => true, 'reason' => 'count_ok');
	}

	/**
	 * @param list<array{pair: array{0:string,1:string}, open_count: int, close_count: int, passed: bool, reason: string}> $already
	 * @return ?array{pair: array{0:string,1:string}, open_count: int, close_count: int, passed: bool, reason: string}
	 */
	private static function bestVerifiedStandardPair(string $bytes, int $n, array $_already): ?array {
		$candidates = array(
			array('<', '>'),
			array('(', ')'),
			array('[', ']'),
			array('{', '}'),
		);
		$best = null;
		$bestScore = -1;
		foreach ($candidates as $pr) {
			$row = self::verifyBracketPair($bytes, $pr[0], $pr[1], $n);
			if (!$row['passed']) {
				continue;
			}
			$score = min($row['open_count'], $row['close_count']);
			if ($score > $bestScore) {
				$bestScore = $score;
				$best = $row;
			}
		}
		if ($best !== null) {
			return $best;
		}
		return null;
	}

	/** @return list<string> */
	private static function freqLimitersFromBytes(string $bytes, int $n): array {
		if ($n < 1) {
			return array();
		}
		static $poolSet = null;
		if ($poolSet === null) {
			$pool = array(',', '.', ';', ':', '/', '\\', '|', '-', '_', '=', '&', '+', '*', '?', '!');
			$poolSet = array();
			foreach ($pool as $c) {
				$poolSet[$c] = true;
			}
		}
		$hist = array();
		$cap = min($n, 262144);
		for ($i = 0; $i < $cap; $i++) {
			$ch = $bytes[$i];
			if (isset($poolSet[$ch])) {
				$hist[$ch] = ($hist[$ch] ?? 0) + 1;
			}
		}
		arsort($hist, SORT_NUMERIC);
		$out = array();
		foreach (array_keys($hist) as $ch) {
			$out[] = $ch;
			if (count($out) >= 8) {
				break;
			}
		}
		return $out;
	}

	private static function matchGlobalPrefixes(string $bytes): string {
		$n = strlen($bytes);
		$best = '';
		$bestLen = 0;
		foreach (self::GLOBAL_PREFIXES as $row) {
			$off = (int) $row[0];
			$pre = (string) $row[1];
			$lab = (string) $row[2];
			$pl = strlen($pre);
			if ($n < $off + $pl) {
				continue;
			}
			if (substr($bytes, $off, $pl) !== $pre) {
				continue;
			}
			if ($lab === 'audio/wav_or_webp' && $n >= 12 && substr($bytes, 8, 4) === 'WEBP') {
				return 'image/webp';
			}
			if ($lab === 'audio/wav_or_webp' && $n >= 12 && substr($bytes, 8, 4) === 'WAVE') {
				return 'audio/wav';
			}
			if ($pl > $bestLen) {
				$bestLen = $pl;
				$best = $lab;
			}
		}
		return $best;
	}

	private static function heuristicLabel(string $bytes, string $path): string {
		if ($bytes === '') {
			return 'empty';
		}
		$n = strlen($bytes);
		$head = substr($bytes, 0, min(4096, $n));
		if (str_starts_with($bytes, "\xef\xbb\xbf")) {
			return 'text/plain_utf8_bom';
		}
		if (str_starts_with($bytes, '%!') || str_starts_with($bytes, '%!PS')) {
			return 'application/postscript';
		}
		if (preg_match('/^%PDF/s', $bytes) === 1) {
			return 'application/pdf';
		}
		if (preg_match('/^#!\s*rnews\s/i', $bytes) === 1) {
			return 'text/x-cnews-batch';
		}
		if (preg_match('/^#!\s*\S+/s', $bytes) === 1) {
			return 'text/script_shebang';
		}
		if (preg_match('/^%[A-Za-z]/', $bytes) === 1 && preg_match('/@[A-Za-z]+\s*\{/', $bytes) === 1) {
			return 'text/x-bibtex-like';
		}
		$head4k = substr($bytes, 0, 4096);
		if (preg_match_all('/^%[A-Z]\s/m', $head4k) >= 5) {
			return 'text/x-refer-bibliography';
		}
		if (preg_match('/^\.[a-zA-Z]{2}\s/m', $bytes) === 1 || preg_match('/^\.[a-zA-Z]{2}\s*$/m', $bytes) === 1) {
			return 'text/x-troff-nroff-like';
		}
		if (str_starts_with($bytes, '/*') || str_starts_with($bytes, '//')) {
			return 'text/x-c-like';
		}
		if (preg_match('/^#include\s|^\s*#include\s/m', $bytes) === 1) {
			return 'text/x-c-like';
		}
		if (preg_match('/^From \S+ .*(?:\r?\n){1,4}Subject:/s', $bytes) === 1) {
			return 'message/rfc822-like';
		}
		if (preg_match('/<(?:!DOCTYPE|html|HTML|body|BODY)\b/', $head) === 1) {
			return 'text/html-like';
		}
		if (preg_match('/^<\?xml\s/i', $bytes) === 1) {
			return 'application/xml-like';
		}
		$ctrl = 0;
		$print = 0;
		$high = 0;
		$sample = min($n, 32768);
		for ($i = 0; $i < $sample; $i++) {
			$o = ord($bytes[$i]);
			if ($o < 32 && $o !== 9 && $o !== 10 && $o !== 13) {
				$ctrl++;
			}
			if ($o >= 32 && $o <= 126) {
				$print++;
			}
			if ($o >= 128) {
				$high++;
			}
		}
		$ratioPrint = $sample > 0 ? $print / $sample : 0.0;
		$ratioCtrl = $sample > 0 ? $ctrl / $sample : 0.0;
		$ratioHigh = $sample > 0 ? $high / $sample : 0.0;
		if ($ratioPrint > 0.92 && $ratioCtrl < 0.02) {
			return 'text/plain_high_printable';
		}
		if ($ratioHigh > 0.05 && self::isValidUtf8(substr($bytes, 0, $sample))) {
			return 'text/plain_utf8_heuristic';
		}
		if ($ratioPrint > 0.85) {
			return 'text/plain_mostly_printable';
		}
		if ($ratioCtrl > 0.15 || substr_count($bytes, "\x00", 0, min(4096, $n)) > 8) {
			return 'application/octet-stream_heuristic';
		}
		return '';
	}

	private static function tier4IsWeakGeneric(string $label): bool {
		return $label === 'text/plain_high_printable'
			|| $label === 'text/plain_mostly_printable'
			|| $label === 'text/plain_utf8_heuristic'
			|| $label === 'application/octet-stream_heuristic';
	}

	private static function isValidUtf8(string $s): bool {
		if ($s === '') {
			return true;
		}
		return preg_match('//u', $s) === 1;
	}
}
