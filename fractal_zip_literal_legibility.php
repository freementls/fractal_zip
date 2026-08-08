<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_tar_ustar.php';

/**
 * Legibility scoring: how much of a buffer looks like text/markup vs opaque container bytes.
 * Used to drive peel-until-legible loops (see fractal_zip_literal_legibility_unwrap.php).
 *
 * Score is 0..100 (higher = more legible). Components are weighted; see {@see fractal_zip_literal_legibility_score}.
 */

/**
 * @return array{
 *   score: float,
 *   printable_ratio: float,
 *   markup_hits: int,
 *   line_oriented: float,
 *   container_penalty: float,
 *   high_byte_ratio: float,
 *   magic_label: string
 * }
 */
function fractal_zip_literal_legibility_score(string $bytes, int $sampleMax = 262144): array {
	$n = strlen($bytes);
	if ($n === 0) {
		return array(
			'score' => 0.0,
			'printable_ratio' => 0.0,
			'markup_hits' => 0,
			'line_oriented' => 0.0,
			'container_penalty' => 0.0,
			'high_byte_ratio' => 0.0,
			'magic_label' => 'empty',
		);
	}
	$sample = $n <= $sampleMax ? $bytes : (substr($bytes, 0, (int) ($sampleMax * 0.75)) . substr($bytes, -(int) ($sampleMax * 0.25)));
	$sn = strlen($sample);
	$print = 0;
	$ctrl = 0;
	$high = 0;
	$lines = 0;
	$longLines = 0;
	for ($i = 0; $i < $sn; $i++) {
		$o = ord($sample[$i]);
		if ($o === 10) {
			$lines++;
		}
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
	$ratioPrint = $sn > 0 ? $print / $sn : 0.0;
	$ratioCtrl = $sn > 0 ? $ctrl / $sn : 0.0;
	$ratioHigh = $sn > 0 ? $high / $sn : 0.0;
	$head = substr($sample, 0, min(4096, $sn));
	$markup = 0;
	foreach (array('<?xml', '<html', '<HTML', '<!DOCTYPE', '%PDF', '{\\rtf', 'ustar', 'PK\x03\x04', '/Type', '/FlateDecode', 'BEGIN ', 'function ', '#include', '.TH ', '.SH ') as $needle) {
		if (str_contains($head, $needle)) {
			$markup++;
		}
	}
	$lineOriented = $sn > 0 && $lines > 0 ? min(1.0, $lines / max(1, $sn / 80)) : 0.0;
	$magicLabel = fractal_zip_literal_legibility_magic_label($bytes);
	$containerPenalty = fractal_zip_literal_legibility_container_penalty($magicLabel);
	$score = 100.0 * (
		0.52 * $ratioPrint
		+ 0.12 * min(1.0, $markup / 3.0)
		+ 0.10 * min(1.0, $lineOriented)
		+ 0.06 * max(0.0, 1.0 - $ratioCtrl * 8.0)
		+ 0.04 * max(0.0, 1.0 - $ratioHigh * 4.0)
	) - $containerPenalty;
	if ($score < 0.0) {
		$score = 0.0;
	}
	if ($score > 100.0) {
		$score = 100.0;
	}
	return array(
		'score' => $score,
		'printable_ratio' => $ratioPrint,
		'markup_hits' => $markup,
		'line_oriented' => $lineOriented,
		'container_penalty' => $containerPenalty,
		'high_byte_ratio' => $ratioHigh,
		'magic_label' => $magicLabel,
	);
}

function fractal_zip_literal_legibility_magic_label(string $bytes): string {
	if ($bytes === '') {
		return 'empty';
	}
	if (str_starts_with($bytes, "\x1f\x8b")) {
		return 'gzip';
	}
	if (str_starts_with($bytes, 'PK\x03\x04')) {
		return 'zip';
	}
	if (str_starts_with($bytes, "7z\xbc\xaf\x27\x1c")) {
		return '7z';
	}
	if (fractal_zip_literal_tar_sniffs_gnu_archive($bytes)) {
		return 'tar';
	}
	if (str_starts_with($bytes, '%PDF')) {
		return 'pdf';
	}
	if (strlen($bytes) >= 8 && substr($bytes, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
		return 'ole';
	}
	if (strlen($bytes) >= 72 && substr($bytes, 64, 6) === 'BLKM18') {
		return 'blkmed';
	}
	if (strlen($bytes) >= 4 && substr($bytes, 0, 4) === "\xd0\x01\x00\x10") {
		return 'dicom';
	}
	if (strlen($bytes) >= 0x104 && substr($bytes, 0x100, 4) === 'SEGA') {
		return 'genesis_rom';
	}
	if (str_starts_with($bytes, 'MZ')) {
		return 'mz_stub';
	}
	if (str_starts_with($bytes, "\x7fELF")) {
		return 'elf';
	}
	return 'opaque';
}

function fractal_zip_literal_legibility_container_penalty(string $magicLabel): float {
	return match ($magicLabel) {
		'tar', 'zip', '7z', 'gzip', 'ole', 'genesis_rom', 'dicom', 'elf', 'mz_stub' => 28.0,
		'pdf' => 8.0,
		default => 0.0,
	};
}

/** True when candidate is strictly more legible than parent (min delta avoids noise). */
function fractal_zip_literal_legibility_improved(array $parent, array $child, float $minDelta = 0.35): bool {
	return ($child['score'] - $parent['score']) >= $minDelta;
}

function fractal_zip_literal_legibility_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_LEGIBILITY_UNWRAP');
	if ($e === false || trim((string) $e) === '') {
		return $cached = false;
	}
	$v = strtolower(trim((string) $e));
	return $cached = ($v !== '0' && $v !== 'off' && $v !== 'false' && $v !== 'no');
}
