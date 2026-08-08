<?php
declare(strict_types=1);

/**
 * Policy layer on top of {@see fractal_zip_content_format_identify::identify()}.
 * Extension is tier-1 input only; behavioral code must use resolved profiles + buffer magic.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_content_format_identify.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_tar_ustar.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_rom_stack.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'file_types' . DIRECTORY_SEPARATOR . 'file_types_policy.php';

/** @param string $relPath member path inside folder bundle */
/** @param string $bytes raw member bytes (may be empty for path-only weak hints) */
function fractal_zip_identify_for_policy(string $relPath, string $bytes, int $peekMax = 65536): array {
	$n = strlen($bytes);
	$peek = ($n <= $peekMax) ? $bytes : substr($bytes, 0, $peekMax);
	$row = fractal_zip_content_format_identify::identify($relPath, $peek);
	$row['content_profile'] = fractal_zip_content_format_resolved_profile($row);
	return $row;
}

/** @param array<string, mixed> $identifyRow */
function fractal_zip_content_format_resolved_profile(array $identifyRow): string {
	return file_types_content_format_resolved_profile($identifyRow);
}

function fractal_zip_content_format_is_ooxml_extension(string $ext): bool {
	return file_types_content_format_is_ooxml_extension($ext);
}

function fractal_zip_content_format_packaged_ooxml_from_labels(string $f, string $t3, string $ext): bool {
	return file_types_content_format_packaged_ooxml_from_labels($f, $t3, $ext);
}

function fractal_zip_content_format_is_textish_profile(string $profile): bool {
	return file_types_content_format_is_textish_profile($profile);
}

/**
 * Container peel family from identify row + current buffer magic (never path suffix alone).
 *
 * @param array<string, mixed> $identifyRow
 */
function fractal_zip_content_format_container_family_for_peel(array $identifyRow, string $workBytes): ?string {
	$n = strlen($workBytes);
	if ($n >= 4 && substr($workBytes, 0, 4) === "PK\x03\x04") {
		return 'zip';
	}
	if ($n >= 6 && str_starts_with($workBytes, "7z\xbc\xaf\x27\x1c")) {
		return '7z';
	}
	if ($n >= 16 && substr($workBytes, 0, 3) === 'MPQ') {
		return 'mpq';
	}
	if (fractal_zip_literal_tar_sniffs_gnu_archive($workBytes)) {
		return 'tar';
	}
	if (fractal_zip_literal_genesis_rom_sniff($workBytes)) {
		return 'rom';
	}
	if ($n >= 8 && substr($workBytes, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
		return 'ole';
	}
	// Peel strategy hub (when available) — agree with browse listable families.
	$relHint = (string) ($identifyRow['path'] ?? $identifyRow['rel_path'] ?? $identifyRow['name'] ?? '');
	if ($relHint !== '' && is_readable(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'peel' . DIRECTORY_SEPARATOR . 'peel.php')) {
		static $peelBoot = false;
		if (!$peelBoot) {
			require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'peel' . DIRECTORY_SEPARATOR . 'peel.php';
			if (function_exists('peel_bootstrap')) {
				peel_bootstrap();
			}
			$peelBoot = true;
		}
		if (function_exists('peel_strategy_for_path')) {
			$strat = peel_strategy_for_path($relHint, $workBytes);
			$fam = (string) ($strat['family'] ?? '');
			if (!empty($strat['listable'])) {
				$mapped = match ($fam) {
					'zip' => 'zip',
					'ole' => 'ole',
					'sevenzip' => '7z',
					default => null,
				};
				if ($mapped !== null) {
					return $mapped;
				}
			}
		}
	}
	$prof = (string) ($identifyRow['content_profile'] ?? fractal_zip_content_format_resolved_profile($identifyRow));
	return match ($prof) {
		'container_zip', 'packaged_ooxml',
		'packaged_power_bi', 'packaged_msapp', 'packaged_epub', 'packaged_iwork',
		'packaged_access', 'packaged_onenote', 'packaged_tableau', 'packaged_alteryx',
		'packaged_dev', 'packaged_design', 'packaged_notebook', 'packaged_qlik' => 'zip',
		'container_7z' => '7z',
		'container_mpq' => 'mpq',
		'container_tar' => 'tar',
		'container_ole' => 'ole',
		'media_rom' => 'rom',
		default => null,
	};
}

function fractal_zip_content_format_peel_require_identify_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_PEEL_REQUIRE_IDENTIFY');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_content_format_peel_aggressive_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_PEEL_AGGRESSIVE');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes');
}

function fractal_zip_content_format_peel_trace_enabled(): bool {
	return getenv('FRACTAL_ZIP_LITERAL_PEEL_TRACE') === '1';
}

/**
 * MS Office / OPC open container: bytes (PK zip) or identify packaged_ooxml / container_zip with tier ≥2.
 */
function fractal_zip_content_format_policy_is_hybrid_zip_container(string $relPath, string $bytes = ''): bool {
	$row = fractal_zip_identify_for_policy($relPath, $bytes);
	$p = (string) ($row['content_profile'] ?? '');
	if (str_starts_with($p, 'packaged_') || $p === 'packaged_ooxml' || $p === 'container_zip') {
		return true;
	}
	if ($bytes !== '' && strlen($bytes) >= 4 && substr($bytes, 0, 4) === "PK\x03\x04") {
		return true;
	}
	$base = strtolower(basename(str_replace('\\', '/', $relPath)));
	return $base !== '' && (bool) preg_match(
		'/\.(pbix|pbit|msapp|epub|pages|numbers|key|twbx|yxzp|idml|sketch|ora|kra|xd|3mf|kmz|fcstd|cbz|'
		. 'whl|xpi|ipa|unitypackage|accdb|onepkg|docx|pptx|xlsx|odt|ods|odp|jar|apk|qvf)$/',
		$base
	);
}

function fractal_zip_content_format_policy_ms_office_open_container(string $relPath, string $bytes = ''): bool {
	if ($bytes !== '' && strlen($bytes) >= 4 && substr($bytes, 0, 4) === "PK\x03\x04") {
		$row = fractal_zip_identify_for_policy($relPath, $bytes);
		$p = (string) $row['content_profile'];
		return $p === 'packaged_ooxml' || $p === 'container_zip'
			|| fractal_zip_content_format_policy_is_hybrid_zip_container($relPath, $bytes);
	}
	$row = fractal_zip_identify_for_policy($relPath, $bytes);
	$p = (string) $row['content_profile'];
	if ($p === 'packaged_ooxml' || fractal_zip_content_format_policy_is_hybrid_zip_container($relPath, $bytes)) {
		return true;
	}
	if ($p === 'container_zip' && (($row['tier_resolved'] ?? 0) >= 2 || ($row['tier2_match'] ?? null) === true)) {
		return true;
	}
	return false;
}

function fractal_zip_content_format_policy_ms_office_semantic_inner(string $relPath, string $bytes = ''): bool {
	if ($relPath === '') {
		return false;
	}
	$r = strtolower(str_replace('\\', '/', $relPath));
	$oleTpl = '#ole-template';
	if (str_contains($r, $oleTpl)) {
		$base = rtrim(substr($r, 0, strpos($r, $oleTpl)), '/');
		return $base !== '' && fractal_zip_content_format_policy_ms_office_open_container($base, $bytes);
	}
	$slash = strrpos($r, '/');
	if ($slash === false) {
		return false;
	}
	$parent = substr($r, 0, $slash);
	return fractal_zip_content_format_policy_ms_office_open_container($parent, $bytes);
}

/** VGM/VGZ/MPQ-style game containers from identify + magic hints. */
function fractal_zip_content_format_policy_game_media_container(string $relPath, string $bytes): bool {
	$row = fractal_zip_identify_for_policy($relPath, $bytes);
	$p = (string) $row['content_profile'];
	if ($p === 'container_mpq') {
		return true;
	}
	$n = strlen($bytes);
	if ($n >= 3 && substr($bytes, 0, 3) === 'MPQ') {
		return true;
	}
	$f = strtolower((string) ($row['final_label'] ?? ''));
	$t3 = strtolower((string) ($row['tier3_label'] ?? ''));
	if (str_contains($f, 'vgm') || str_contains($t3, 'vgm')) {
		return true;
	}
	return false;
}

function fractal_zip_content_format_policy_literal_skip_gzip_incompressible_fast_exit(string $relPath, string $bytes): bool {
	if ($bytes !== '' && strlen($bytes) >= 8 && substr($bytes, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
		return true;
	}
	if (fractal_zip_content_format_policy_ms_office_open_container($relPath, $bytes)) {
		return true;
	}
	if (fractal_zip_content_format_policy_ms_office_semantic_inner($relPath, $bytes)) {
		return true;
	}
	if (fractal_zip_content_format_policy_game_media_container($relPath, $bytes)) {
		return true;
	}
	$row = fractal_zip_identify_for_policy($relPath, $bytes);
	$p = (string) $row['content_profile'];
	if (fractal_zip_content_format_policy_is_hybrid_zip_container($relPath, $bytes)) {
		return true;
	}
	// Text / structured markup — never gzip-1 fast-exit (peel + literal tournament).
	if (fractal_zip_content_format_is_textish_profile($p)
		|| $p === 'text_markup' || $p === 'text_plain' || $p === 'document_email') {
		return true;
	}
	if (function_exists('peel_strategy_for_path') && $relPath !== '') {
		static $peelBoot = false;
		if (!$peelBoot) {
			$peelPhp = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'peel' . DIRECTORY_SEPARATOR . 'peel.php';
			if (is_readable($peelPhp)) {
				require_once $peelPhp;
				if (function_exists('peel_bootstrap')) {
					peel_bootstrap();
				}
			}
			$peelBoot = true;
		}
		if (function_exists('peel_strategy_for_path')) {
			$strat = peel_strategy_for_path($relPath, $bytes);
			if (in_array((string) ($strat['family'] ?? ''), array('xml_json', 'mime', 'sqlite', 'pdf'), true)) {
				return true;
			}
		}
	}
	return in_array($p, array(
		'container_zip', 'packaged_ooxml', 'container_7z', 'container_mpq', 'container_tar', 'media_rom',
		'container_ole',
		'packaged_power_bi', 'packaged_msapp', 'packaged_epub', 'packaged_dev', 'packaged_design',
		'packaged_iwork', 'packaged_access', 'packaged_tableau', 'packaged_notebook',
		'packaged_qlik', 'packaged_alteryx', 'packaged_onenote',
	), true);
}

function fractal_zip_content_format_policy_is_pdf_container(string $relPath, string $bytes): bool {
	if ($bytes !== '' && str_starts_with($bytes, '%PDF')) {
		return true;
	}
	$row = fractal_zip_identify_for_policy($relPath, $bytes);
	$t3 = strtolower((string) ($row['tier3_label'] ?? ''));
	$f = strtolower((string) ($row['final_label'] ?? ''));
	return str_contains($t3, 'pdf') || str_contains($f, 'pdf');
}

function fractal_zip_content_format_policy_is_raster_member(string $relPath, string $bytes): bool {
	$row = fractal_zip_identify_for_policy($relPath, $bytes);
	$p = (string) $row['content_profile'];
	if ($p === 'media_image') {
		return true;
	}
	$n = strlen($bytes);
	if ($n >= 3) {
		if (str_starts_with($bytes, "\xff\xd8\xff") || str_starts_with($bytes, "\x89PNG\r\n\x1a\n")
			|| str_starts_with($bytes, 'GIF8') || str_starts_with($bytes, 'BM')) {
			return true;
		}
		if ($n >= 12 && substr($bytes, 0, 4) === 'RIFF' && substr($bytes, 8, 4) === 'WEBP') {
			return true;
		}
	}
	return false;
}

/**
 * Reorder bucket key (replaces path_extension_bucket for FRACTAL_ZIP_PIPELINE_REORDER_EXT).
 */
function fractal_zip_content_format_policy_content_profile_bucket(string $relPath, string $bytes): string {
	$row = fractal_zip_identify_for_policy($relPath, $bytes);
	return (string) $row['content_profile'];
}

/**
 * Census: textish byte mass from identify (not extension list).
 */
function fractal_zip_content_format_policy_member_is_textish(string $relPath, string $raw, ?int $byteLenOverride = null): bool {
	$s = $byteLenOverride !== null ? max(0, (int) $byteLenOverride) : strlen($raw);
	if ($s === 0) {
		return false;
	}
	$peek = strlen($raw) > 65536 ? substr($raw, 0, 65536) : $raw;
	if ($peek === '' && $byteLenOverride !== null && $byteLenOverride > 0) {
		return false;
	}
	$row = fractal_zip_identify_for_policy($relPath, $peek);
	return fractal_zip_content_format_is_textish_profile((string) $row['content_profile']);
}
