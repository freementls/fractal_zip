<?php
declare(strict_types=1);

/**
 * Folder metastructure (FZMS): content-aware marker proposals from identify census + byte sample.
 *
 * Enable with adaptive markers: FRACTAL_ZIP_METastruct=1 (requires FRACTAL_ZIP_ADAPTIVE_MARKERS=1).
 * Merges tiered delimiter_association evidence across members (weighted by byte mass) into the
 * frequency-based marker proposal when textish_ratio ≥ FRACTAL_ZIP_METastruct_TEXTISH_MIN (default 0.12);
 * wire-size probe in fractal_zip_maybe_apply_adaptive_markers() still gates adoption.
 *
 * FZMS v1 block (optional sidecar / debug): magic "FZMS", u8 version, u32_be json_len, UTF-8 JSON descriptor.
 *
 * Literal bias (unified stream): FRACTAL_ZIP_METastruct_LITERAL_BIAS=1 or FRACTAL_ZIP_METastruct=1 —
 * forces transform tournament on container/textish members; schedule-only inner cost_hint nudges.
 *
 * Text chain (mode 17): FRACTAL_ZIP_METastruct_TEXT_CHAIN=1 or FRACTAL_ZIP_METastruct=1 —
 * runs greedy transform chains on text-dominant folders (delta/xor/transpose stacks; BMP-only gate bypassed).
 *
 * Container peel (modes 18/19): FRACTAL_ZIP_METastruct_CONTAINER_PEEL=1 or FRACTAL_ZIP_METastruct=1 —
 * on gzip-probe ties for container members, optional adaptive_compress wire probe picks peel vs raw
 * (FRACTAL_ZIP_METastruct_CONTAINER_PEEL_WIRE_PROBE=1). No blind tie-break toward mode 18.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_content_format_policy.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_marker_adapt.php';

if (!function_exists('bench_json_encode_options')) {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
}

const FRACTAL_ZIP_METastruct_MAGIC = 'FZMS';
const FRACTAL_ZIP_METastruct_VERSION = 1;

function fractal_zip_metastruct_env_enabled(): bool {
	return getenv('FRACTAL_ZIP_METastruct') === '1';
}

/** Unified-stream literal tournament + schedule bias (default on when FRACTAL_ZIP_METastruct=1). */
function fractal_zip_metastruct_literal_bias_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_METastruct_LITERAL_BIAS');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
	}
	return fractal_zip_metastruct_env_enabled();
}

/**
 * Force zip_folder through create_fractal_zip_markers (legacy fractal tree).
 * Adaptive / metastruct marker tuning is skipped on unified-stream, bundle-only, and run-grammar early exits.
 */
function fractal_zip_metastruct_force_legacy_fractal_env(): void {
	putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0');
	putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_RECURSIVE_ONLY=0');
	putenv('FRACTAL_ZIP_INNER_RUN_GRAMMAR=0');
	putenv('FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST=0');
	putenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES=65536');
	putenv('FRACTAL_ZIP_BUNDLE_ONLY_SINGLE_FILE_MIN_BYTES=1073741824');
	putenv('FRACTAL_ZIP_WEB_REF=0');
	putenv('LIVE_BROWSER_WEB_REF_OFFLINE=1');
}

/** @param array{total_bytes: int, content_profiles: array<string,int>} $census */
function fractal_zip_metastruct_textish_ratio(array $census): float {
	$total = max(1, (int) ($census['total_bytes'] ?? 0));
	$textish = 0;
	foreach ($census['content_profiles'] ?? array() as $prof => $bytes) {
		if (fractal_zip_content_format_is_textish_profile((string) $prof)) {
			$textish += (int) $bytes;
		}
	}
	return $textish / $total;
}

/**
 * Walk a folder and aggregate identify rows + delimiter votes (byte-weighted).
 *
 * @return array{
 *   files: int,
 *   total_bytes: int,
 *   content_profiles: array<string,int>,
 *   delimiter_profiles: array<string,int>,
 *   pair_votes: array<string,int>,
 *   limiter_votes: array<string,int>,
 *   mid_votes: array<string,int>
 * }
 */
function fractal_zip_metastruct_census_from_dir(string $dir, int $peekMax = 65536): array {
	$root = realpath($dir);
	$out = array(
		'files' => 0,
		'total_bytes' => 0,
		'content_profiles' => array(),
		'delimiter_profiles' => array(),
		'pair_votes' => array(),
		'limiter_votes' => array(),
		'mid_votes' => array(),
	);
	if ($root === false) {
		return $out;
	}
	$rootN = rtrim(str_replace('\\', '/', $root), '/') . '/';
	$prefixLen = strlen($rootN);
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$pathN = str_replace('\\', '/', $fi->getPathname());
		if (strlen($pathN) < $prefixLen || substr($pathN, 0, $prefixLen) !== $rootN) {
			continue;
		}
		$rel = substr($pathN, $prefixLen);
		$sz = (int) $fi->getSize();
		if ($sz <= 0) {
			continue;
		}
		$peek = @file_get_contents($fi->getPathname(), false, null, 0, min($sz, max(256, $peekMax)));
		if ($peek === false) {
			continue;
		}
		$row = fractal_zip_identify_for_policy($rel, (string) $peek, $peekMax);
		$prof = (string) ($row['content_profile'] ?? 'unknown');
		$out['content_profiles'][$prof] = ($out['content_profiles'][$prof] ?? 0) + $sz;
		$out['files']++;
		$out['total_bytes'] += $sz;

		$da = isset($row['delimiter_association']) && is_array($row['delimiter_association'])
			? $row['delimiter_association'] : array();
		$dp = (string) ($da['profile'] ?? 'unknown');
		$out['delimiter_profiles'][$dp] = ($out['delimiter_profiles'][$dp] ?? 0) + $sz;

		$rec = isset($da['recommendation']) && is_array($da['recommendation']) ? $da['recommendation'] : array();
		foreach ($rec['common_limiter_pairs'] ?? array() as $pr) {
			if (!is_array($pr) || !isset($pr[0], $pr[1])) {
				continue;
			}
			$key = (string) $pr[0] . "\0" . (string) $pr[1];
			$out['pair_votes'][$key] = ($out['pair_votes'][$key] ?? 0) + $sz;
		}
		foreach ($rec['common_limiters'] ?? array() as $ch) {
			if (!is_string($ch) || strlen($ch) !== 1) {
				continue;
			}
			$out['limiter_votes'][$ch] = ($out['limiter_votes'][$ch] ?? 0) + $sz;
		}
		foreach ($da['mid_evidence'] ?? array() as $me) {
			if (!is_array($me) || empty($me['passed']) || !isset($me['mid'])) {
				continue;
			}
			$m = (string) $me['mid'];
			if (strlen($m) !== 1) {
				continue;
			}
			$out['mid_votes'][$m] = ($out['mid_votes'][$m] ?? 0) + $sz;
		}
	}
	return $out;
}

/** @param array{pair_votes: array<string,int>, limiter_votes: array<string,int>} $census */
function fractal_zip_metastruct_delimiter_recommendation_from_census(array $census): array {
	$pairs = array();
	if (isset($census['pair_votes']) && is_array($census['pair_votes'])) {
		$pv = $census['pair_votes'];
		arsort($pv, SORT_NUMERIC);
		foreach ($pv as $key => $_w) {
			$parts = explode("\0", (string) $key, 2);
			if (count($parts) === 2) {
				$pairs[] = array($parts[0], $parts[1]);
			}
			if (count($pairs) >= 4) {
				break;
			}
		}
	}
	$lims = array();
	if (isset($census['limiter_votes']) && is_array($census['limiter_votes'])) {
		$lv = $census['limiter_votes'];
		arsort($lv, SORT_NUMERIC);
		foreach ($lv as $ch => $_w) {
			if (is_string($ch) && strlen($ch) === 1) {
				$lims[] = $ch;
			}
			if (count($lims) >= 12) {
				break;
			}
		}
	}
	return array(
		'common_limiter_pairs' => $pairs,
		'common_limiters' => $lims,
	);
}

/** @param array{mid_votes: array<string,int>} $census */
function fractal_zip_metastruct_propose_mid(array $census, string $sample, bool $multipass, string $freqMid): string {
	$votes = isset($census['mid_votes']) && is_array($census['mid_votes']) ? $census['mid_votes'] : array();
	if ($votes === array()) {
		return $freqMid;
	}
	arsort($votes, SORT_NUMERIC);
	$censusMid = $freqMid;
	foreach ($votes as $m => $_w) {
		if (!is_string($m) || strlen($m) !== 1) {
			continue;
		}
		$err = fractal_zip_marker_config_validate(array('left' => '<', 'mid' => $m, 'right' => '>'));
		if ($err === null) {
			$censusMid = $m;
			break;
		}
	}
	if (!$multipass || $sample === '') {
		return $censusMid;
	}
	$hist = count_chars($sample, 1);
	if (!is_array($hist)) {
		return $censusMid;
	}
	$score = static function (string $ch) use ($hist): int {
		return $hist[ord($ch)] ?? 0;
	};
	return ($score($censusMid) >= $score($freqMid)) ? $censusMid : $freqMid;
}

/** @return array{0: array{common_limiters: list<string>, common_limiter_pairs: list<array{0:string,1:string}>, left: string, mid: string, right: string, range_shorthand: string}, 1: array<string,mixed>} */
function fractal_zip_metastruct_marker_propose(string $dir, string $sample, bool $multipass): array {
	$freq = fractal_zip_marker_propose_from_sample($sample, $multipass);
	$census = fractal_zip_metastruct_census_from_dir($dir);
	$textishRatio = fractal_zip_metastruct_textish_ratio($census);
	$mergeMin = getenv('FRACTAL_ZIP_METastruct_TEXTISH_MIN');
	$mergeMinF = ($mergeMin !== false && trim((string) $mergeMin) !== '' && is_numeric($mergeMin))
		? max(0.0, min(1.0, (float) $mergeMin)) : 0.12;
	$sources = array('freq_sample');
	$merged = $freq;
	if ($textishRatio >= $mergeMinF) {
		$typeRec = fractal_zip_metastruct_delimiter_recommendation_from_census($census);
		$merged = fractal_zip_marker_merge_type_delimiter_evidence($freq, $typeRec);
		$merged['mid'] = fractal_zip_metastruct_propose_mid($census, $sample, $multipass, (string) $freq['mid']);
		$sources[] = 'identify_census';
	} else {
		$sources[] = 'identify_census_skipped_binary_heavy';
	}

	$domContent = 'unknown';
	$domDelim = 'unknown';
	if ($census['content_profiles'] !== array()) {
		arsort($census['content_profiles'], SORT_NUMERIC);
		$domContent = (string) array_key_first($census['content_profiles']);
	}
	if ($census['delimiter_profiles'] !== array()) {
		arsort($census['delimiter_profiles'], SORT_NUMERIC);
		$domDelim = (string) array_key_first($census['delimiter_profiles']);
	}

	$descriptor = array(
		'v' => FRACTAL_ZIP_METastruct_VERSION,
		'files' => (int) $census['files'],
		'total_bytes' => (int) $census['total_bytes'],
		'dominant_content_profile' => $domContent,
		'dominant_delimiter_profile' => $domDelim,
		'content_profiles' => $census['content_profiles'],
		'delimiter_profiles' => $census['delimiter_profiles'],
		'marker_freq' => $freq,
		'marker_merged' => $merged,
		'textish_ratio' => $textishRatio,
		'sources' => $sources,
	);
	return array($merged, $descriptor);
}

/** @param array<string,mixed> $descriptor */
function fractal_zip_metastruct_encode_block(array $descriptor): string {
	$json = bench_json_encode_fingerprint_try($descriptor, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if ($json === null) {
		$json = '{}';
	}
	$len = strlen($json);
	if ($len > 0x7fffffff) {
		$len = 0x7fffffff;
		$json = substr($json, 0, $len);
	}
	return FRACTAL_ZIP_METastruct_MAGIC . chr(FRACTAL_ZIP_METastruct_VERSION)
		. pack('N', $len) . $json;
}

/** @return array<string,mixed>|null */
function fractal_zip_metastruct_decode_block(string $bytes): ?array {
	if (strlen($bytes) < 9 || substr($bytes, 0, 4) !== FRACTAL_ZIP_METastruct_MAGIC) {
		return null;
	}
	$ver = ord($bytes[4]);
	if ($ver !== FRACTAL_ZIP_METastruct_VERSION) {
		return null;
	}
	$len = unpack('N', substr($bytes, 5, 4));
	$jsonLen = is_array($len) ? (int) ($len[1] ?? 0) : 0;
	if ($jsonLen < 0 || 9 + $jsonLen > strlen($bytes)) {
		return null;
	}
	$json = substr($bytes, 9, $jsonLen);
	$row = json_decode($json, true);
	return is_array($row) ? $row : null;
}

/** @param array{content_profiles?: array<string,int>, total_bytes?: int} $census */
function fractal_zip_metastruct_dominant_content_profile(array $census): string {
	$profiles = isset($census['content_profiles']) && is_array($census['content_profiles'])
		? $census['content_profiles'] : array();
	if ($profiles === array()) {
		return 'unknown';
	}
	arsort($profiles, SORT_NUMERIC);
	return (string) array_key_first($profiles);
}

/** @param array{content_profiles?: array<string,int>, total_bytes?: int} $census */
function fractal_zip_metastruct_profile_byte_share(array $census, string $profile): float {
	$total = max(1, (int) ($census['total_bytes'] ?? 0));
	$profiles = isset($census['content_profiles']) && is_array($census['content_profiles'])
		? $census['content_profiles'] : array();
	return ((int) ($profiles[$profile] ?? 0)) / $total;
}

/**
 * Per-member: keep literal transform tournament from gzip-1 early exit when folder/member profiles suggest peels help.
 */
function fractal_zip_metastruct_member_force_literal_probe(string $relPath, string $rawBytes, ?array $folderCensus): bool {
	if (!fractal_zip_metastruct_literal_bias_enabled() || !is_array($folderCensus)) {
		return false;
	}
	$rel = (string) $relPath;
	$rawBytes = (string) $rawBytes;
	if ($rel === '' || $rawBytes === '') {
		return false;
	}
	$n = strlen($rawBytes);
	$peek = $n <= 65536 ? $rawBytes : substr($rawBytes, 0, 65536);
	$row = fractal_zip_identify_for_policy($rel, $peek);
	$memberProf = (string) ($row['content_profile'] ?? 'unknown');
	$dom = fractal_zip_metastruct_dominant_content_profile($folderCensus);

	$containerProfiles = array('container_zip', 'packaged_ooxml', 'container_7z', 'container_ole', 'container_tar', 'container_mpq');
	if (in_array($memberProf, $containerProfiles, true)) {
		return true;
	}
	if ($n >= 4 && substr($rawBytes, 0, 4) === "PK\x03\x04"
		&& fractal_zip_metastruct_profile_byte_share($folderCensus, 'container_zip') >= 0.15) {
		return true;
	}
	if (in_array($dom, array('text_markup', 'text_csv', 'text_plain', 'text_structured'), true)
		&& fractal_zip_content_format_is_textish_profile($memberProf)) {
		return true;
	}
	if ($dom === 'packaged_ooxml' && fractal_zip_content_format_policy_ms_office_semantic_inner($rel, $rawBytes)) {
		return true;
	}
	return false;
}

/**
 * Schedule-only cost_hint nudges from FZMS census (trial order; wire bytes unchanged when all inners are scored).
 *
 * @param list<array<string,mixed>> $innerVariants
 * @param array<string,mixed>|null $folderBundleCensus
 * @param array<string,mixed>|null $fzmsCensus
 * @return list<array<string,mixed>>
 */
function fractal_zip_metastruct_apply_literal_schedule_bias(array $innerVariants, $folderBundleCensus, $fzmsCensus): array {
	if (!fractal_zip_metastruct_literal_bias_enabled() || !is_array($fzmsCensus)) {
		return $innerVariants;
	}
	$dom = fractal_zip_metastruct_dominant_content_profile($fzmsCensus);
	$deltaLiteral = 0;
	$deltaFractal = 0;
	$tagDelta = array();
	switch ($dom) {
		case 'container_zip':
		case 'packaged_ooxml':
		case 'container_ole':
			$deltaLiteral = -3;
			$tagDelta = array('fzb' => -2, 'fzbf' => -1);
			break;
		case 'text_markup':
		case 'text_csv':
		case 'text_plain':
		case 'text_structured':
			$deltaLiteral = -2;
			$tagDelta = array('fzb' => -1);
			break;
		case 'media_image':
		case 'media_audio':
		case 'media_video':
		case 'opaque_binary':
			$deltaLiteral = 2;
			$deltaFractal = -1;
			break;
		default:
			break;
	}
	if ($deltaLiteral === 0 && $deltaFractal === 0 && $tagDelta === array()) {
		return $innerVariants;
	}
	$out = array();
	foreach ($innerVariants as $v) {
		if (!is_array($v)) {
			$out[] = $v;
			continue;
		}
		$lane = isset($v['lane']) ? (string) $v['lane'] : '';
		$tag = isset($v['tag']) ? (string) $v['tag'] : '';
		$ch = isset($v['cost_hint']) ? (int) $v['cost_hint'] : 0;
		if ($lane === 'literal') {
			$ch += $deltaLiteral;
		} elseif ($lane === 'fractal_inner') {
			$ch += $deltaFractal;
		}
		if ($tag !== '' && isset($tagDelta[$tag])) {
			$ch += (int) $tagDelta[$tag];
		}
		$v['cost_hint'] = max(0, $ch);
		$out[] = $v;
	}
	return $out;
}

/** Text mode-17 chain search (default on when FRACTAL_ZIP_METastruct=1). */
function fractal_zip_metastruct_text_chain_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_METastruct_TEXT_CHAIN');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
	}
	return fractal_zip_metastruct_literal_bias_enabled();
}

/** @return list<int> */
function fractal_zip_metastruct_literal_chain_priority_text(): array {
	return array(1, 2, 14, 3, 4, 12);
}

/**
 * FZMS: enable mode-17 greedy chain on large textish literals (not only BI_RGB BMP).
 *
 * @param array{content_profiles?: array<string,int>, total_bytes?: int} $folderCensus
 */
function fractal_zip_metastruct_want_literal_chain_search(string $relPath, string $rawBytes, ?array $folderCensus): bool {
	if (!fractal_zip_metastruct_text_chain_enabled() || !is_array($folderCensus)) {
		return false;
	}
	$rawBytes = (string) $rawBytes;
	$n = strlen($rawBytes);
	if ($n < 512) {
		return false;
	}
	$dom = fractal_zip_metastruct_dominant_content_profile($folderCensus);
	$textProfiles = array('text_csv', 'text_plain', 'text_markup', 'text_structured');
	if (!in_array($dom, $textProfiles, true)) {
		return false;
	}
	if (fractal_zip_metastruct_profile_byte_share($folderCensus, $dom) < 0.5) {
		return false;
	}
	$rel = (string) $relPath;
	if ($rel === '') {
		return true;
	}
	$peek = $n <= 65536 ? $rawBytes : substr($rawBytes, 0, 65536);
	$row = fractal_zip_identify_for_policy($rel, $peek);
	$memberProf = (string) ($row['content_profile'] ?? 'unknown');
	return fractal_zip_content_format_is_textish_profile($memberProf);
}

/** Container semantic peel priority (default on when FRACTAL_ZIP_METastruct=1). */
function fractal_zip_metastruct_container_peel_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_METastruct_CONTAINER_PEEL');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
	}
	return fractal_zip_metastruct_literal_bias_enabled();
}

/**
 * Prefer mode 18/19 peel for this member when folder or member profile is container-heavy.
 *
 * @param array{content_profiles?: array<string,int>, total_bytes?: int} $folderCensus
 */
function fractal_zip_metastruct_member_want_container_peel_priority(string $relPath, string $rawBytes, ?array $folderCensus): bool {
	if (!fractal_zip_metastruct_container_peel_enabled() || !is_array($folderCensus)) {
		return false;
	}
	$rawBytes = (string) $rawBytes;
	$n = strlen($rawBytes);
	if ($n < 4) {
		return false;
	}
	$rel = (string) $relPath;
	$peek = $n <= 65536 ? $rawBytes : substr($rawBytes, 0, 65536);
	$row = fractal_zip_identify_for_policy($rel, $peek);
	$memberProf = (string) ($row['content_profile'] ?? 'unknown');
	$containerProfiles = array('container_zip', 'packaged_ooxml', 'container_7z', 'container_ole', 'container_tar', 'container_mpq');
	if (in_array($memberProf, $containerProfiles, true)) {
		return true;
	}
	if (substr($rawBytes, 0, 4) === "PK\x03\x04") {
		return true;
	}
	if ($n >= 8 && substr($rawBytes, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
		return true;
	}
	if ($n >= 6 && str_starts_with($rawBytes, "7z\xbc\xaf\x27\x1c")) {
		return true;
	}
	$dom = fractal_zip_metastruct_dominant_content_profile($folderCensus);
	if (in_array($dom, array('container_zip', 'packaged_ooxml', 'container_ole', 'container_7z'), true)
		&& fractal_zip_metastruct_profile_byte_share($folderCensus, $dom) >= 0.25) {
		return str_contains($rel, '/') || str_ends_with(strtolower($rel), '.zip')
			|| str_ends_with(strtolower($rel), '.xlsx') || str_ends_with(strtolower($rel), '.docx')
			|| str_ends_with(strtolower($rel), '.pptx') || str_ends_with(strtolower($rel), '.ods');
	}
	return false;
}

/** On gzip-probe ties, compare adaptive_compress wire bytes for peel vs incumbent (default on with peel). */
function fractal_zip_metastruct_container_peel_wire_probe_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_METastruct_CONTAINER_PEEL_WIRE_PROBE');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
	}
	return fractal_zip_metastruct_container_peel_enabled();
}

/** Lazy FZMS census for the current zip_folder root (stored on host). */
function fractal_zip_metastruct_ensure_folder_census_for_host(fractal_zip $host): ?array {
	if (!fractal_zip_metastruct_literal_bias_enabled()) {
		return null;
	}
	if (is_array($host->folder_metastruct_census)) {
		return $host->folder_metastruct_census;
	}
	$root = (string) $host->zip_folder_root_for_members;
	if ($root === '') {
		return null;
	}
	$real = realpath($root);
	$census = fractal_zip_metastruct_census_from_dir($real !== false ? $real : $root);
	$host->folder_metastruct_census = $census;
	fractal_zip::$last_metastruct_descriptor = array(
		'v' => FRACTAL_ZIP_METastruct_VERSION,
		'role' => 'literal_bias_folder_census',
		'dominant_content_profile' => fractal_zip_metastruct_dominant_content_profile($census),
		'content_profiles' => $census['content_profiles'],
		'textish_ratio' => fractal_zip_metastruct_textish_ratio($census),
	);
	return $census;
}
