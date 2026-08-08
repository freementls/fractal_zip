<?php
declare(strict_types=1);

/**
 * Content-heuristic ordering for semantic peelers (ZIP / 7z / MPQ / ustar TAR / GNU ar / cpio newc / git / bencode / netstring / FWS SWF).
 * After each successful peel the engine re-scans the new buffer so nested shells
 * (e.g. zip inside gzip, tar member that is itself compressed) are not missed.
 *
 * Scores come from buffer magic and {@see fractal_zip_identify_for_policy()} (extension is tier-1 only).
 * MPQ peel requires MPQ magic + identify container family when FRACTAL_ZIP_LITERAL_PEEL_REQUIRE_IDENTIFY=1 (default).
 *
 * Roadmap / Silesia ratio plan / domain-only transforms (e.g. stack bitstreams — not here):
 * docs/PEELING_FOLLOW.md
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_content_format_identify.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_content_format_policy.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_ar_gnu.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_cpio_newc.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_git_blob.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_bencode_str.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_netstring.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_swf_fws.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_tar_ustar.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_rom_stack.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_ole_wire.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_markup_islands.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_staroffice_wire.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_printable_wire.php';

/**
 * @return array<string, int> peeler id => score (higher = try earlier)
 */
function fractal_zip_literal_peeler_semantic_scores(string $relPath, string $pathRaster, string $work): array {
	$n = strlen($work);
	$peek = $n > 65536 ? substr($work, 0, 65536) : $work;
	$row = fractal_zip_identify_for_policy($relPath, $peek);
	$t3 = strtolower((string) ($row['tier3_label'] ?? ''));
	$fin = strtolower((string) ($row['final_label'] ?? ''));
	$prof = (string) $row['content_profile'];
	$hay = $t3 . '|' . $fin . '|' . $prof;

	$scores = [];

	$add = static function (string $id, int $delta) use (&$scores): void {
		if ($delta <= 0) {
			return;
		}
		$scores[$id] = ($scores[$id] ?? 0) + $delta;
	};

	if ($n >= 4 && substr($work, 0, 4) === "PK\x03\x04") {
		$add('zip', 50);
	}
	if ($n >= 6 && str_starts_with($work, "7z\xbc\xaf\x27\x1c")) {
		$add('7z', 50);
	}
	if ($n >= 16 && substr($work, 0, 3) === 'MPQ') {
		$add('mpq', 55);
		if ($prof === 'container_mpq') {
			$add('mpq', 12);
		}
	}
	if (fractal_zip_literal_semantic_tar_enabled() && fractal_zip_literal_tar_sniffs_single_ustar_candidate($work)) {
		$add('tar', 40);
		if (str_contains($hay, 'tar') || str_contains($prof, 'container')) {
			$add('tar', 8);
		}
	}
	if (fractal_zip_literal_semantic_ar_enabled() && fractal_zip_literal_ar_sniffs_single_gnu_candidate($work)) {
		$add('ar', 42);
		if (str_contains($hay, 'ar archive') || str_contains($hay, 'debian')) {
			$add('ar', 8);
		}
	}
	if (fractal_zip_literal_semantic_cpio_newc_enabled() && fractal_zip_literal_cpio_newc_sniffs_single_plus_trailer($work)) {
		$add('cpio', 38);
		if (str_contains($hay, 'cpio') || str_contains($hay, 'initramfs')) {
			$add('cpio', 8);
		}
	}

	if (str_contains($hay, 'zip') || str_contains($hay, 'application/zip')) {
		$add('zip', 18);
	}
	if (str_starts_with($prof, 'packaged_') || $prof === 'packaged_ooxml' || $prof === 'container_zip') {
		$add('zip', 22);
	}
	if ($prof === 'container_ole' || str_contains($hay, 'ole-storage')) {
		$add('ole', 16);
	}
	if (str_contains($hay, '7z') || str_contains($hay, '7-z')) {
		$add('7z', 18);
	}
	if (str_contains($hay, 'mpq') || str_contains($hay, 'mopaq')) {
		$add('mpq', 18);
	}
	if (str_contains($hay, 'current ar archive') || str_contains($hay, 'ar archive') || str_contains($hay, 'debian package')) {
		$add('ar', 14);
	}
	if (str_contains($hay, 'cpio') || str_contains($hay, 'initramfs')) {
		$add('cpio', 14);
	}
	if (fractal_zip_literal_semantic_git_loose_blob_enabled() && fractal_zip_literal_git_blob_loose_sniffs($work)) {
		$add('gitobj', 35);
		if (str_contains($hay, 'git')) {
			$add('gitobj', 8);
		}
	}
	if (fractal_zip_literal_semantic_bencode_str_only_enabled() && fractal_zip_literal_bencode_str_only_sniffs($work)) {
		$add('bstr', 32);
		if (str_contains($hay, 'bencode')) {
			$add('bstr', 6);
		}
	}
	if (str_contains($hay, 'bencode') && strlen($work) < 4 * 1024 * 1024) {
		$add('bstr', 4);
	}
	if (fractal_zip_literal_semantic_netstring_file_enabled() && fractal_zip_literal_netstring_file_sniffs($work)) {
		$add('nstr', 30);
	}
	if ($n >= 8 && fractal_zip_literal_semantic_swf_fws_enabled() && fractal_zip_literal_swf_fws_sniffs($work)) {
		$add('fws', 30);
		if (str_contains($hay, 'flash') || str_contains($hay, 'shockwave')) {
			$add('fws', 10);
		}
	}
	if (str_contains($hay, 'flash') || str_contains($hay, 'shockwave')) {
		$add('fws', 5);
	}

	if (str_contains($hay, 'gnu-tar') || str_contains($hay, 'tar')) {
		$add('tar', 12);
	}
	if (str_contains($hay, 'genesis') || str_contains($hay, 'megadrive')) {
		$add('rom', 10);
	}
	if (str_contains($hay, 'ole-storage') || str_contains($hay, 'ole')) {
		$add('ole', 11);
	}
	if ($n >= 8 && substr($work, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
		$add('ole', 48);
	}
	if (fractal_zip_literal_sniff_legacy_markup_compound($work)) {
		$add('markup', 9);
	}
	if (fractal_zip_literal_sniff_high_printable_opaque($work)) {
		$add('printable', 8);
	}
	if (fractal_zip_literal_sniff_dicom_text_meta($work)) {
		$add('printable', 7);
	}
	if (fractal_zip_literal_sniff_blkmed_image($work)) {
		$add('printable', 7);
	}

	$pr = ['mpq' => 0, 'zip' => 1, '7z' => 2, 'rom' => 3, 'tar' => 4, 'ole' => 5, 'markup' => 6, 'printable' => 7, 'ar' => 8, 'cpio' => 9, 'gitobj' => 10, 'bstr' => 11, 'nstr' => 12, 'fws' => 13];
	uksort($scores, static function (string $a, string $b) use ($scores, $pr): int {
		$da = $scores[$b] <=> $scores[$a];
		if ($da !== 0) {
			return $da;
		}
		return ($pr[$a] ?? 9) <=> ($pr[$b] ?? 9);
	});

	return $scores;
}

/**
 * @return list<string> ordered peeler ids (non-empty subset of zip|7z|mpq|rom|tar|ar|cpio|gitobj|bstr|nstr|fws)
 */
function fractal_zip_literal_peeler_semantic_order(string $relPath, string $pathRaster, string $work): array {
	$m = fractal_zip_literal_peeler_semantic_scores($relPath, $pathRaster, $work);
	if ($m === []) {
		return ['zip', '7z', 'mpq', 'rom', 'tar', 'ole', 'markup', 'printable', 'ar', 'cpio', 'gitobj', 'bstr', 'nstr', 'fws'];
	}
	return array_keys($m);
}

/**
 * @param list<array{0: string, 1: string}> $semanticLayers
 * @param list<array{len: int, sha1: string}> $gzipStack
 * @return array{0: string, 1: string, 2: list<array{0: string, 1: string}>, 3: list<array{len: int, sha1: string}>}
 */
function fractal_zip_literal_recursive_peel_try_one_semantic(
	string $relPath,
	string $pathRaster,
	string $work,
	array $semanticLayers,
	array $gzipStack,
	int $maxSem,
	bool $mpqSemanticProxyGate,
	bool $forBundleEncode = true
): array {
	$order = fractal_zip_literal_peeler_semantic_order($relPath, $pathRaster, $work);
	foreach ($order as $kind) {
		if (count($semanticLayers) >= $maxSem) {
			break;
		}
		if ($kind === 'zip' && fractal_zip_literal_semantic_zip_enabled()) {
			$zp = fractal_zip_literal_pac_peel_zip_single_semantic($work);
			if ($zp !== null) {
				$semanticLayers[] = ['zip', $zp[1]];
				$pathRaster = fractal_zip_literal_path_for_raster_pac_after_zip_strip($pathRaster);
				return [$zp[0], $pathRaster, $semanticLayers, $gzipStack];
			}
		} elseif ($kind === '7z' && fractal_zip_literal_semantic_7z_enabled()) {
			$sp = fractal_zip_literal_pac_peel_seven_single_semantic($work);
			if ($sp !== null) {
				$semanticLayers[] = ['7z', $sp[1]];
				$pathRaster = fractal_zip_literal_path_for_raster_pac_after_7z_strip($pathRaster);
				return [$sp[0], $pathRaster, $semanticLayers, $gzipStack];
			}
		} elseif ($kind === 'mpq' && fractal_zip_literal_semantic_mpq_enabled()) {
			if (strlen($work) >= 16 && substr($work, 0, 3) === 'MPQ') {
				if (fractal_zip_content_format_peel_require_identify_enabled()) {
					$idRow = fractal_zip_identify_for_policy($relPath, strlen($work) > 65536 ? substr($work, 0, 65536) : $work);
					$fam = fractal_zip_content_format_container_family_for_peel($idRow, $work);
					if ($fam !== 'mpq' && substr($work, 0, 3) === 'MPQ') {
						$fam = 'mpq';
					}
					if ($fam !== 'mpq') {
						continue;
					}
				}
				$mp = fractal_zip_literal_pac_peel_mpq_semantic($work);
				if ($mp !== null) {
					$innerCandidate = $mp[0];
					if ($mpqSemanticProxyGate) {
						$pb = gzdeflate($work, 1);
						$pa = gzdeflate($innerCandidate, 1);
						if ($pb === false || $pa === false || strlen($pa) >= strlen($pb)) {
							continue;
						}
					}
					$semanticLayers[] = ['mpq', $mp[1]];
					$pathRaster = fractal_zip_literal_path_for_raster_pac_after_mpq_strip($pathRaster);
					return [$innerCandidate, $pathRaster, $semanticLayers, $gzipStack];
				}
			}
		} elseif ($kind === 'tar' && fractal_zip_literal_semantic_tar_enabled()) {
			$tp = fractal_zip_literal_pac_peel_tar_any_semantic($work, $forBundleEncode);
			if ($tp !== null) {
				if ($forBundleEncode) {
					$semanticLayers[] = ['tar', $tp[1]];
				}
				$pathRaster = fractal_zip_literal_path_for_raster_pac_after_tar_strip($pathRaster);
				return [$tp[0], $pathRaster, $semanticLayers, $gzipStack];
			}
		} elseif ($kind === 'rom' && fractal_zip_literal_stack_v1_enabled()) {
			$rp = fractal_zip_literal_pac_peel_genesis_rom_semantic($work);
			if ($rp !== null) {
				$semanticLayers[] = ['rom', $rp[1]];
				return [$rp[0], $pathRaster, $semanticLayers, $gzipStack];
			}
	} elseif ($kind === 'ole' && fractal_zip_literal_semantic_ole_enabled()) {
			// Prefer stream concat peel toward entropy even during bundle encode when aggressive.
			$allowOleWire = !$forBundleEncode || fractal_zip_content_format_peel_aggressive_enabled();
			if ($allowOleWire) {
				$op = fractal_zip_literal_pac_peel_ole_wire($work);
				if ($op !== null) {
					return [$op[0], $pathRaster, $semanticLayers, $gzipStack];
				}
			}
		} elseif ($kind === 'markup' && fractal_zip_literal_sniff_legacy_markup_compound($work)) {
			$mp = fractal_zip_literal_pac_peel_staroffice_wire($work);
			if ($mp !== null) {
				return [$mp[0], $pathRaster, $semanticLayers, $gzipStack];
			}
		} elseif ($kind === 'printable' && (fractal_zip_literal_sniff_high_printable_opaque($work) || fractal_zip_literal_sniff_dicom_text_meta($work) || fractal_zip_literal_sniff_blkmed_image($work))) {
			$pr = fractal_zip_literal_pac_peel_printable_runs_wire($work);
			if ($pr !== null) {
				return [$pr[0], $pathRaster, $semanticLayers, $gzipStack];
			}
		} elseif ($kind === 'ar' && fractal_zip_literal_semantic_ar_enabled()) {
			$ap = fractal_zip_literal_pac_peel_ar_single_gnu($work);
			if ($ap !== null) {
				$semanticLayers[] = ['ar', $ap[1]];
				$mem = 'member';
				$pt = $ap[1];
				$pc = explode(':', $pt, 2);
				if (count($pc) === 2) {
					$hd = base64_decode($pc[0], true);
					if ($hd !== false && strlen($hd) === 60) {
						$mem = rtrim(substr($hd, 0, 16), ' /') ?: 'member';
					}
				}
				$pathRaster = fractal_zip_literal_path_for_raster_pac_after_ar_strip($pathRaster, $mem);
				return [$ap[0], $pathRaster, $semanticLayers, $gzipStack];
			}
		} elseif ($kind === 'cpio' && fractal_zip_literal_semantic_cpio_newc_enabled()) {
			$cp = fractal_zip_literal_pac_peel_cpio_newc($work);
			if ($cp !== null) {
				$semanticLayers[] = ['cpio', $cp[1]];
				$nm = 'member';
				$pt = $cp[1];
				$pc = explode(':', $pt, 2);
				if (count($pc) === 2) {
					$pfx = base64_decode($pc[0], true);
					if ($pfx !== false && strlen($pfx) >= 110) {
						$h = substr($pfx, 0, 110);
						$nlen = fractal_zip_literal_cpio_newc_parse_hex8($h, 11);
						if ($nlen >= 1 && 110 + $nlen <= strlen($pfx)) {
							$nm = substr($pfx, 110, $nlen);
						}
					}
				}
				$pathRaster = fractal_zip_literal_path_for_raster_pac_after_cpio_strip($pathRaster, $nm);
				return [$cp[0], $pathRaster, $semanticLayers, $gzipStack];
			}
		} elseif ($kind === 'gitobj' && fractal_zip_literal_semantic_git_loose_blob_enabled()) {
			$g = fractal_zip_literal_pac_peel_git_loose_object($work);
			if ($g !== null) {
				$tag = $g[1];
				$semanticLayers[] = ['gitobj', $tag];
				$pathRaster = fractal_zip_literal_path_for_raster_pac_after_git_loose_object_strip($pathRaster);
				return [$g[0], $pathRaster, $semanticLayers, $gzipStack];
			}
		} elseif ($kind === 'bstr' && fractal_zip_literal_semantic_bencode_str_only_enabled()) {
			$bs = fractal_zip_literal_pac_peel_bencode_str_only($work);
			if ($bs !== null) {
				$semanticLayers[] = ['bstr', ''];
				$pathRaster = fractal_zip_literal_path_for_raster_pac_after_bencode_str_only_strip($pathRaster);
				return [$bs[0], $pathRaster, $semanticLayers, $gzipStack];
			}
		} elseif ($kind === 'nstr' && fractal_zip_literal_semantic_netstring_file_enabled()) {
			$ns = fractal_zip_literal_pac_peel_netstring_file($work);
			if ($ns !== null) {
				$semanticLayers[] = ['nstr', ''];
				$pathRaster = fractal_zip_literal_path_for_raster_pac_after_netstring_file_strip($pathRaster);
				return [$ns[0], $pathRaster, $semanticLayers, $gzipStack];
			}
		} elseif ($kind === 'fws' && fractal_zip_literal_semantic_swf_fws_enabled()) {
			$sw = fractal_zip_literal_pac_peel_swf_fws($work);
			if ($sw !== null) {
				$semanticLayers[] = ['fws', $sw[1]];
				$pathRaster = fractal_zip_literal_path_for_raster_pac_after_swf_fws_strip($pathRaster);
				return [$sw[0], $pathRaster, $semanticLayers, $gzipStack];
			}
		}
	}
	return [$work, $pathRaster, $semanticLayers, $gzipStack];
}
