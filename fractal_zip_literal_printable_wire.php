<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_legibility.php';

/**
 * Extract contiguous printable-ASCII runs from opaque / DB / DICOM-meta blobs (wire peel).
 *
 * @return array{0: string, 1: string}|null
 */
function fractal_zip_literal_pac_peel_printable_runs_wire(string $bytes, int $minRun = 32, int $maxRuns = 65536): ?array {
	$n = strlen($bytes);
	if ($n < 4096) {
		return null;
	}
	$blkmed = fractal_zip_literal_sniff_blkmed_image($bytes);
	$dicomMeta = fractal_zip_literal_sniff_dicom_text_meta($bytes);
	if ($blkmed || $dicomMeta) {
		$minRun = 8;
	}
	$inner = fractal_zip_literal_printable_filter_buffer($bytes, $minRun);
	if ($inner === null) {
		return null;
	}
	$ilen = strlen($inner);
	$minRel = ($blkmed || $dicomMeta) ? 512 : max(8192, (int) ($n * 0.15));
	if ($ilen < $minRel) {
		return null;
	}
	$parent = fractal_zip_literal_legibility_score($bytes);
	$child = fractal_zip_literal_legibility_score($inner);
	$minDelta = ($blkmed || $dicomMeta) ? 2.0 : 5.0;
	if ($child['score'] - $parent['score'] < $minDelta) {
		return null;
	}
	$pb = @gzdeflate($bytes, 1);
	$pa = @gzdeflate($inner, 1);
	if ($pb !== false && $pa !== false && strlen($pa) >= strlen($pb)) {
		return null;
	}
	$runCount = substr_count($inner, "\n") + 1;
	return array($inner, 'PRUN:' . $runCount);
}

/**
 * Replace non-printable bytes with newlines; drop short lines.
 */
function fractal_zip_literal_printable_filter_buffer(string $bytes, int $minRun = 32): ?string {
	$n = strlen($bytes);
	$maxLine = max(4096, (int) (getenv('FRACTAL_ZIP_LITERAL_PRINTABLE_MAX_LINE') ?: 65536));
	$out = '';
	$line = '';
	$flush = static function () use (&$line, &$out, $minRun, $maxLine): void {
		if (strlen($line) >= $minRun) {
			while (strlen($line) > $maxLine) {
				$out .= substr($line, 0, $maxLine) . "\n";
				$line = substr($line, $maxLine);
			}
			$out .= $line . "\n";
		}
		$line = '';
	};
	for ($i = 0; $i < $n; $i++) {
		$o = ord($bytes[$i]);
		if ($o === 9 || $o === 10 || $o === 13 || ($o >= 32 && $o <= 126)) {
			$line .= $bytes[$i];
			while (strlen($line) >= $maxLine) {
				$out .= substr($line, 0, $maxLine) . "\n";
				$line = substr($line, $maxLine);
			}
		} else {
			$flush();
		}
	}
	$flush();
	return $out !== '' ? rtrim($out) : null;
}

/** DICOM / PACS dumps with text meta (ISO_IR, patient names, UIDs). */
/** Opaque blob with mostly printable text (DB dumps, logs) — not tar/FZTM/XML containers.
 * Pure text (≥99.5% printable) is excluded: callers should treat that as text_plain, not opaque.
 */
function fractal_zip_literal_sniff_high_printable_opaque(string $bytes): bool {
	if ($bytes === '' || str_starts_with($bytes, 'FZTM') || fractal_zip_literal_tar_sniffs_gnu_archive($bytes)) {
		return false;
	}
	if (str_starts_with($bytes, '%PDF')) {
		return false;
	}
	if (str_starts_with($bytes, 'MZ') && str_contains($bytes, 'WordDocument')) {
		return false;
	}
	$sample = strlen($bytes) > 131072 ? (substr($bytes, 0, 65536) . substr($bytes, -65536)) : $bytes;
	if (str_contains($sample, '<mediawiki') || str_contains($sample, '<page>')
		|| str_contains($sample, '<revision>') || str_contains($sample, '<text xml:space="preserve">')) {
		return false;
	}
	if (function_exists('fractal_zip_enwik_entry_sort_enabled') && fractal_zip_enwik_entry_sort_enabled()) {
		return false;
	}
	$sn = strlen($sample);
	$print = 0;
	for ($i = 0; $i < $sn; $i++) {
		$o = ord($sample[$i]);
		if ($o === 9 || $o === 10 || $o === 13 || ($o >= 32 && $o <= 126)) {
			$print++;
		}
	}
	if ($sn <= 0) {
		return false;
	}
	$ratio = (float) $print / (float) $sn;
	// Need some non-printable so this is "opaque with text islands", not a plain .txt.
	return $ratio >= 0.65 && $ratio < 0.995;
}

/** BLKM18 proprietary shell (Silesia x-ray): DICOM-like prefix + block at 0x40. */
function fractal_zip_literal_sniff_blkmed_image(string $bytes): bool {
	return strlen($bytes) >= 72 && substr($bytes, 64, 6) === 'BLKM18';
}

function fractal_zip_literal_sniff_dicom_text_meta(string $bytes): bool {
	if ($bytes === '') {
		return false;
	}
	$head = strlen($bytes) > 4096 ? substr($bytes, 0, 4096) : $bytes;
	return str_contains($head, 'ISO_IR')
		|| str_contains($head, 'DICM')
		|| str_contains($head, 'DICOM')
		|| (strlen($bytes) >= 132 && substr($bytes, 128, 4) === "DICM");
}
