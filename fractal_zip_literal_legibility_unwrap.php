<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_legibility.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_embedded_islands.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_content_format_policy.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_markup_islands.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_ole_wire.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_staroffice_wire.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_printable_wire.php';

/**
 * Legibility-guided unwrap: keep peeling while a candidate raises legibility (and passes gzip-1 proxy when set).
 *
 * @param list<array{0: string, 1: string}> $semanticLayers
 * @return array{0: string, 1: list<array{0: string, 1: string}>}
 */
function fractal_zip_literal_legibility_unwrap_pass(
	string $relPath,
	string $work,
	array $semanticLayers,
	bool $forBundleEncode
): array {
	if (!fractal_zip_literal_legibility_enabled()) {
		return array($work, $semanticLayers);
	}
	$maxRounds = 12;
	$trace = getenv('FRACTAL_ZIP_LITERAL_LEGIBILITY_TRACE');
	$doTrace = $trace !== false && trim((string) $trace) !== '' && strtolower(trim((string) $trace)) !== '0';
	for ($r = 0; $r < $maxRounds; $r++) {
		$before = fractal_zip_literal_legibility_score($work);
		$bestWork = $work;
		$bestLayer = null;
		$bestDelta = 0.0;
		$candidates = fractal_zip_literal_legibility_peel_candidates($relPath, $work, $forBundleEncode);
		foreach ($candidates as $c) {
			$label = (string) $c['label'];
			$cand = (string) $c['bytes'];
			$tag = (string) $c['tag'];
			if ($cand === $work || $cand === '') {
				continue;
			}
			$after = fractal_zip_literal_legibility_score($cand);
			$delta = $after['score'] - $before['score'];
			if ($delta < 0.35) {
				continue;
			}
			if (isset($c['gzip_ok']) && $c['gzip_ok'] === false) {
				continue;
			}
			$cLen = strlen($cand);
			$wLen = strlen($work);
			$minKeep = max(8192, (int) ($wLen * 0.05));
			if ($cLen < $minKeep && ($c['label'] ?? '') !== 'gzip') {
				continue;
			}
			if ($cLen < max(512, (int) ($wLen * 0.01)) && ($c['label'] ?? '') === 'embedded_island') {
				continue;
			}
			if ($delta > $bestDelta) {
				$bestDelta = $delta;
				$bestWork = $cand;
				$bestLayer = array($label, $tag);
			}
		}
		if ($bestLayer === null) {
			break;
		}
		if (!$forBundleEncode) {
			$work = $bestWork;
		} else {
			$semanticLayers[] = $bestLayer;
			$work = $bestWork;
		}
		if ($doTrace) {
			fwrite(STDERR, "legibility_peel {$relPath} round {$r}: {$bestLayer[0]} delta=" . round($bestDelta, 2)
				. " score " . round($before['score'], 1) . " -> " . round(fractal_zip_literal_legibility_score($work)['score'], 1) . "\n");
		}
	}
	return array($work, $semanticLayers);
}

/**
 * @return list<array{label: string, bytes: string, tag: string, gzip_ok?: bool}>
 */
function fractal_zip_literal_legibility_peel_candidates(string $relPath, string $work, bool $forBundleEncode): array {
	$out = array();
	if (fractal_zip_literal_expand_gzip_inner_enabled() && str_starts_with($work, "\x1f\x8b")) {
		$ex = fractal_zip_literal_expand_outer_gzip_once($work);
		if ($ex !== null) {
			$inner = (string) $ex['inner'];
			$out[] = array(
				'label' => 'gzip',
				'bytes' => $inner,
				'tag' => 'GZIP1:',
				'gzip_ok' => fractal_zip_literal_legibility_gzip_proxy_ok($work, $inner),
			);
		}
	}
	if (function_exists('fractal_zip_literal_pac_peel_tar_any_semantic')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_tar_ustar.php';
		$tp = fractal_zip_literal_pac_peel_tar_any_semantic($work, $forBundleEncode);
		if ($tp !== null) {
			$out[] = array(
				'label' => 'tar',
				'bytes' => $tp[0],
				'tag' => $tp[1],
				'gzip_ok' => fractal_zip_literal_legibility_gzip_proxy_ok($work, $tp[0]),
			);
		}
	}
	if (fractal_zip_literal_sniff_legacy_markup_compound($work)) {
		$xml = fractal_zip_literal_pac_peel_staroffice_wire($work);
		if ($xml !== null) {
			$out[] = array(
				'label' => 'staroffice',
				'bytes' => $xml[0],
				'tag' => $xml[1],
				'gzip_ok' => fractal_zip_literal_legibility_gzip_proxy_ok($work, $xml[0]),
			);
		}
	}
	if (fractal_zip_literal_sniff_high_printable_opaque($work)
		|| fractal_zip_literal_sniff_dicom_text_meta($work)
		|| fractal_zip_literal_sniff_blkmed_image($work)) {
		$pr = fractal_zip_literal_pac_peel_printable_runs_wire($work);
		if ($pr !== null) {
			$out[] = array(
				'label' => 'printable_runs',
				'bytes' => $pr[0],
				'tag' => $pr[1],
				'gzip_ok' => fractal_zip_literal_legibility_gzip_proxy_ok($work, $pr[0]),
			);
		}
	}
	$isl = fractal_zip_literal_pac_peel_embedded_island_wire($work);
	if ($isl !== null) {
		$out[] = array(
			'label' => 'embedded_island',
			'bytes' => $isl[0],
			'tag' => $isl[1],
			'gzip_ok' => fractal_zip_literal_legibility_gzip_proxy_ok($work, $isl[0]),
		);
	}
	if (fractal_zip_literal_pac_stream_enabled()) {
		$got = fractal_zip_literal_pac_try_stream_smaller_by_magic($work);
		if ($got === null && fractal_zip_content_format_policy_is_pdf_container($relPath, $work)) {
			$got = fractal_zip_literal_pac_try_stream_smaller('pdf', $work);
		}
		if ($got !== null) {
			$out[] = array(
				'label' => 'stream_pac',
				'bytes' => $got[0],
				'tag' => 'PAC:',
				'gzip_ok' => true,
			);
		}
	}
	if (fractal_zip_literal_semantic_ole_enabled()) {
		$op = fractal_zip_literal_pac_peel_ole_wire($work);
		if ($op !== null) {
			$out[] = array(
				'label' => 'ole_streams',
				'bytes' => $op[0],
				'tag' => $op[1],
				'gzip_ok' => fractal_zip_literal_legibility_gzip_proxy_ok($work, $op[0]),
			);
		}
	}
	return $out;
}

function fractal_zip_literal_legibility_gzip_proxy_ok(string $outer, string $inner): bool {
	$pb = @gzdeflate($outer, 1);
	$pa = @gzdeflate($inner, 1);
	if ($pb === false || $pa === false) {
		return true;
	}
	return strlen($pa) < strlen($pb);
}
