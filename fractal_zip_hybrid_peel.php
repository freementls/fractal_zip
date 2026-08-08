<?php
declare(strict_types=1);

/**
 * Encode-time hybrid ZIP member filter for fractal_zip mode 18 + folder expand.
 *
 * Aligns with peel hub semantic filters so mode-18 / logical-bundle tournaments
 * see text/structure first and drop volatile junk — closer to entropy.
 * Folder restore prefers ZIP_MULTI (+ semantic patch) for filtered containers; VERBATIM is fallback.
 */

function fractal_zip_hybrid_peel_enabled(): bool
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_HYBRID_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_hybrid_msapp_drop_noise(): bool
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_HYBRID_MSAPP_DROP_NOISE');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_hybrid_ensure_peel(): bool
{
	static $ok = null;
	if ($ok !== null) {
		return $ok;
	}
	$peel = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'peel' . DIRECTORY_SEPARATOR . 'peel.php';
	if (!is_readable($peel)) {
		return $ok = false;
	}
	require_once $peel;
	if (function_exists('peel_bootstrap')) {
		peel_bootstrap();
	}
	return $ok = function_exists('peel_hybrid_filter_msapp');
}

/**
 * True when path/bytes should get hybrid mode-18 scoping (any listable ZIP-family peel).
 */
function fractal_zip_hybrid_is_container(?string $relPath, string $bytes = ''): bool
{
	if (!fractal_zip_hybrid_peel_enabled()) {
		return false;
	}
	$rel = str_replace('\\', '/', (string) $relPath);
	$base = strtolower(basename($rel));
	if ($base !== '' && preg_match(
		'/\.(pbix|pbit|msapp|epub|pages|numbers|key|twbx|yxzp|idml|sketch|ora|kra|xd|3mf|kmz|fcstd|cbz|cbt|usdz|pkpass|'
		. 'whl|xpi|ipa|unitypackage|accdb|onepkg|docx|docm|pptx|pptm|xlsx|xlsm|odt|ods|odp|odg|'
		. 'jar|war|ear|apk|aab|vsix|nupkg|crx|gem|egg|appx|msix|qvf|qvw|zip|pk3|pk4)$/',
		$base
	)) {
		return true;
	}
	if ($bytes !== '' && strlen($bytes) >= 4 && substr($bytes, 0, 4) === "PK\x03\x04") {
		if (fractal_zip_hybrid_ensure_peel() && function_exists('peel_strategy_for_path')) {
			$strat = peel_strategy_for_path($rel !== '' ? $rel : 'archive.zip', $bytes);
			if (!empty($strat['listable']) && ($strat['family'] ?? '') === 'zip') {
				return true;
			}
			// Opaque / sevenzip / xml_json — do not force mode-18 on bare PK.
			if (($strat['family'] ?? '') !== '' && ($strat['family'] ?? '') !== 'zip') {
				return false;
			}
		}
		fractal_zip_ensure_content_format_policy_loaded();
		if (function_exists('fractal_zip_identify_for_policy')) {
			$row = fractal_zip_identify_for_policy($rel !== '' ? $rel : 'archive.zip', $bytes);
			$p = (string) ($row['content_profile'] ?? '');
			if (str_starts_with($p, 'packaged_') || $p === 'packaged_ooxml' || $p === 'container_zip') {
				return true;
			}
			if ($p === 'container_fzc' || $p === 'opaque_binary') {
				return false;
			}
		}
		// Bare PK without a packaged/listable profile: skip (avoids false-positive tournament).
		return false;
	}
	return false;
}

function fractal_zip_hybrid_msapp_keep_member(string $name): bool
{
	if (fractal_zip_hybrid_ensure_peel() && function_exists('peel_hybrid_filter_msapp')) {
		return peel_hybrid_filter_msapp($name);
	}
	$low = strtolower(str_replace('\\', '/', $name));
	foreach (['editorstate/', 'src/editorstate/', 'entropy.json', 'checksum.json'] as $prefix) {
		if ($low === rtrim($prefix, '/') || str_starts_with($low, $prefix)) {
			return false;
		}
	}
	return true;
}

/**
 * Prefer text/XML/JSON members earlier in the tournament (shared locality / faster early exit).
 *
 * @param list<array{name: string, data: string}> $members
 * @return list<array{name: string, data: string}>
 */
function fractal_zip_hybrid_reorder_for_entropy(array $members): array
{
	$score = static function (array $m): int {
		$name = strtolower((string) ($m['name'] ?? ''));
		$data = (string) ($m['data'] ?? '');
		$n = strlen($data);
		$s = 0;
		if (preg_match('/\.(xml|rels|xhtml|html|htm|svg|css|js|json|yaml|yml|txt|csv|tsv|md|pa\.yaml)$/', $name)) {
			$s += 40;
		}
		if (preg_match('/\.(png|jpe?g|gif|webp|bmp|ico|woff2?|ttf|otf|dex|so|dll|class|pyc)$/', $name)) {
			$s -= 20;
		}
		// Prefer smaller compressible payloads first.
		if ($n > 0 && $n < 64 * 1024) {
			$s += 10;
		}
		if ($n > 1024 * 1024) {
			$s -= 15;
		}
		return $s;
	};
	usort($members, static function (array $a, array $b) use ($score): int {
		$d = $score($b) <=> $score($a);
		if ($d !== 0) {
			return $d;
		}
		return strlen((string) ($a['data'] ?? '')) <=> strlen((string) ($b['data'] ?? ''));
	});
	return $members;
}

/**
 * True when a dropped encode-tournament member is light noise (junk / thumbs / sigs),
 * not heavy media / DataModel — safe to re-include for lossless ZIP_MULTI rebuild.
 */
function fractal_zip_hybrid_member_is_light_encode_drop(string $name): bool
{
	if (function_exists('peel_hybrid_filter_zip_junk') && !peel_hybrid_filter_zip_junk($name)) {
		return true;
	}
	$low = strtolower(str_replace('\\', '/', $name));
	foreach (['docprops/thumbnail.jpeg', 'docprops/thumbnail.jpg', 'thumbnails/thumbnail.png'] as $noise) {
		if ($low === $noise) {
			return true;
		}
	}
	if (preg_match('#^meta-inf/[^/]+\.(sf|rsa|dsa|ec)$#', $low)) {
		return true;
	}
	return false;
}

/**
 * @param list<array{name: string, data: string}> $full
 * @param list<array{name: string, data: string}> $kept
 */
function fractal_zip_hybrid_dropped_is_light_noise(array $full, array $kept): bool
{
	$keptSet = array();
	foreach ($kept as $m) {
		$keptSet[(string) ($m['name'] ?? '')] = true;
	}
	$sawDrop = false;
	foreach ($full as $m) {
		$name = (string) ($m['name'] ?? '');
		if ($name === '' || isset($keptSet[$name])) {
			continue;
		}
		$sawDrop = true;
		if (!fractal_zip_hybrid_member_is_light_encode_drop($name)) {
			return false;
		}
	}
	return $sawDrop;
}

/**
 * @param list<array{name: string, data: string}> $members
 * @return list<array{name: string, data: string}>
 */
function fractal_zip_hybrid_filter_zip_members(?string $relPath, array $members): array
{
	if (!fractal_zip_hybrid_peel_enabled() || $members === []) {
		return $members;
	}
	$rel = str_replace('\\', '/', (string) $relPath);
	$base = strtolower(basename($rel));
	$ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
	$profile = '';
	// Prefer path/extension profile — never sniff first *member* payload as the archive.
	if ($ext !== '' && function_exists('file_types_content_format_hybrid_profile_for_extension')) {
		$hp = file_types_content_format_hybrid_profile_for_extension($ext);
		if (is_string($hp) && $hp !== '') {
			$profile = $hp;
		}
	}
	if ($profile === '' && fractal_zip_hybrid_ensure_peel() && function_exists('peel_strategy_for_path')) {
		$strat = peel_strategy_for_path($rel !== '' ? $rel : 'archive.zip', '');
		$profile = (string) ($strat['profile'] ?? '');
	}
	if ($profile === '' && function_exists('fractal_zip_identify_for_policy')) {
		$row = fractal_zip_identify_for_policy($rel !== '' ? $rel : 'archive.zip', '');
		$profile = (string) ($row['content_profile'] ?? '');
	}

	$isMsapp = str_ends_with($base, '.msapp') || $profile === 'packaged_msapp';
	$usePeelEncode = fractal_zip_hybrid_ensure_peel() && function_exists('peel_hybrid_filter_encode_member');

	$out = [];
	foreach ($members as $m) {
		$name = (string) ($m['name'] ?? '');
		if ($name === '') {
			continue;
		}
		$keep = true;
		if ($usePeelEncode) {
			if ($isMsapp && !fractal_zip_hybrid_msapp_drop_noise()) {
				$keep = function_exists('peel_hybrid_filter_zip_junk')
					? peel_hybrid_filter_zip_junk($name)
					: true;
			} else {
				$keep = peel_hybrid_filter_encode_member($name, $profile, $ext);
			}
		} else {
			$low = strtolower(str_replace('\\', '/', $name));
			if (str_starts_with($low, '__macosx/') || basename($low) === '.ds_store') {
				$keep = false;
			}
			if ($isMsapp && fractal_zip_hybrid_msapp_drop_noise()) {
				$keep = $keep && fractal_zip_hybrid_msapp_keep_member($name);
			}
		}
		if ($keep) {
			$out[] = $m;
		}
	}
	if (count($out) < 2) {
		$out = $members;
	}
	return fractal_zip_hybrid_reorder_for_entropy($out);
}

/**
 * Prefer mode-18 for hybrid ZIP packages (peel members toward entropy).
 * Override with FRACTAL_ZIP_HYBRID_FORCE_MODE18=0.
 */
function fractal_zip_hybrid_force_mode18(): bool
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_HYBRID_FORCE_MODE18');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_hybrid_prefer_mode18(?string $relPath, string $bytes): bool
{
	if (!fractal_zip_hybrid_force_mode18() || !fractal_zip_hybrid_peel_enabled()) {
		return false;
	}
	return fractal_zip_hybrid_is_container($relPath, $bytes);
}

function fractal_zip_hybrid_mode18_max_raw_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_HYBRID_MODE18_MAX_RAW');
	if ($e !== false && trim((string) $e) !== '' && (int) $e > 0) {
		return max(256 * 1024, (int) $e);
	}
	$default = 48 * 1024 * 1024;
	// Align with folder ZIP peel budget when larger (avoid under-peeling fat OOXML).
	if (function_exists('fractal_zip_folder_zip_peel_max_raw_bytes')) {
		$folder = fractal_zip_folder_zip_peel_max_raw_bytes();
		if ($folder > $default) {
			$default = $folder;
		}
	}
	return $default;
}

/** Cap hybrid mode-18 member explosion (env FRACTAL_ZIP_HYBRID_MODE18_MAX_MEMBERS, default 384). */
function fractal_zip_hybrid_mode18_max_members(): int
{
	$e = getenv('FRACTAL_ZIP_HYBRID_MODE18_MAX_MEMBERS');
	if ($e !== false && trim((string) $e) !== '' && (int) $e > 0) {
		return max(2, (int) $e);
	}
	return 384;
}

/**
 * List mode-18 members then apply hybrid filter when relPath is a hybrid container.
 *
 * @return list<array{name: string, data: string}>|null
 */
function fractal_zip_literal_pac_list_zip_members_for_mode18_scoped(
	string $compressed,
	?string $relPath = null,
	?int $maxTotalOverride = null
): ?array {
	$capBytes = $maxTotalOverride ?? fractal_zip_hybrid_mode18_max_raw_bytes();
	$all = fractal_zip_literal_pac_list_zip_members_for_mode18($compressed, $capBytes);
	if ($all === null) {
		return null;
	}
	if ($relPath === null || $relPath === '') {
		// Still strip universal junk + reorder when peel enabled.
		if (fractal_zip_hybrid_peel_enabled()) {
			return fractal_zip_hybrid_filter_zip_members('archive.zip', $all);
		}
		return $all;
	}
	if (!fractal_zip_hybrid_is_container($relPath, $compressed)) {
		return $all;
	}
	$filtered = fractal_zip_hybrid_filter_zip_members($relPath, $all);
	$cap = fractal_zip_hybrid_mode18_max_members();
	if (count($filtered) > $cap) {
		$filtered = array_slice($filtered, 0, $cap);
	}
	return $filtered;
}
