<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_dict_scan.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_objects.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_stream_markers.php';

/**
 * JPEG re-pack inside /DCTDecode stream bodies:
 * - default: lossless tries (jpegtran, multi-pass up to 5 with -optimize and -progressive;
 *   override FRACTAL_ZIP_PDF_JPEG_JPEGTRAN_MAX_PASSES) + optional cjpeg quality 100 with djpeg+cmp guard
 * - opt-in semantic mode (FRACTAL_ZIP_PDF_JPEG_SEMANTIC=1): lossy cjpeg quality curve on streams where
 *   `fractal_zip_pdf_pac_cjpeg_semantic_allows_lossy_recompress` is true (default: ≥0.1 Mpix, not “tiny+crushed”
 *   BPP); per-stream `fractal_zip_pdf_pac_cjpeg_semantic_effective_min_savings_pct` replaces flat 2% (override
 *   with FRACTAL_ZIP_PDF_JPEG_SEMANTIC_MIN_SAVINGS_PCT). Decode band: djpeg → P6, max ch. diff
 *   FRACTAL_ZIP_PDF_JPEG_SEMANTIC_MAX_PPM_CHANNEL_DIFF. Knobs: LOSSY_MIN_MEGAPIXELS, MEGAPIX_TINY_BPP_FLOOR_MPX, LOSSY_BPP_CRUSHED.
 *   Benchmark with --no-verify. Display size in the PDF is not read here (raster w×h only).
 * FRACTAL_ZIP_LITERALPAC_PDF_JPEG=0 disables the whole step.
 */
function fractal_zip_pdf_jpeg_pac_enabled(): bool {
	static $c = null;
	if ( $c !== null) {
		return $c;
	}
	$e = getenv( 'FRACTAL_ZIP_LITERALPAC_PDF_JPEG' );
	if ( $e === false || trim( (string) $e) === '' ) {
		return $c = true;
	}
	$v = strtolower( trim( (string) $e) );
	return $c = ! ( $v === '0' || $v === 'off' || $v === 'false' || $v === 'no' );
}

function fractal_zip_pdf_pac_jpeg_soi_eoi_valid( string $b): bool {
	$L = strlen( $b);
	if ( $L < 4 || ord( $b[0] ) !== 0xFF || ord( $b[1] ) !== 0xD8) {
		return false;
	}
	if ( ord( $b[ $L - 2] ) !== 0xFF || ord( $b[ $L - 1] ) !== 0xD9) {
		return false;
	}
	return true;
}

/**
 * @param list<string> $argSuffix e.g. [ '-optimize' ] after "jpegtran -copy all"
 * @return string|null
 */
function fractal_zip_pdf_pac_jpeg_tran_to_out( array $argSuffix, string $in, string $ou): ?string {
	$ex = 1;
	$null = array();
	$args = 'jpegtran -copy all ' . implode( ' ', array_map( 'escapeshellarg', $argSuffix) ) . ' -outfile ' . escapeshellarg( $ou) . ' ' . escapeshellarg( $in) . ' 2>/dev/null';
	exec( $args, $null, $ex);
	if ( $ex !== 0 || ! is_file( $ou) ) {
		@unlink( $ou);
		return null;
	}
	$out = file_get_contents( $ou);
	@unlink( $ou);
	if ( ! is_string( $out) || $out === '' || ! fractal_zip_pdf_pac_jpeg_soi_eoi_valid( $out) ) {
		return null;
	}
	$inBytes = (string) @file_get_contents( $in);
	if ( $out === $inBytes) {
		return null;
	}
	return (string) $out;
}

/**
 * @return positive-int
 */
function fractal_zip_pdf_pac_jpeg_tran_max_refine_passes(): int {
	static $cached = null;
	if ( $cached !== null) {
		return $cached;
	}
	$e = getenv( 'FRACTAL_ZIP_PDF_JPEG_JPEGTRAN_MAX_PASSES' );
	if ( $e === false || trim( (string) $e) === '' || ! ctype_digit( (string) $e) ) {
		return $cached = 5;
	}
	return $cached = max( 1, min( 16, (int) $e) );
}

function fractal_zip_pdf_pac_cjpeg_extras_enabled(): bool {
	$e = getenv( 'FRACTAL_ZIP_PDF_CJPEG' );
	if ( $e === false || trim( (string) $e) === '' ) {
		return false;
	}
	$v = strtolower( trim( (string) $e) );
	return ! ( $v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_pdf_pac_cjpeg_djpeg_on_path(): bool {
	static $b = null;
	if ( $b !== null) {
		return $b;
	}
	$cd = shell_exec( 'command -v cjpeg 2>/dev/null' );
	$dd = shell_exec( 'command -v djpeg 2>/dev/null' );
	return $b = ( is_string( $cd) && trim( $cd) !== '' && is_string( $dd) && trim( $dd) !== '' );
}

function fractal_zip_pdf_pac_cjpeg_semantic_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_pdf_pac_cjpeg_semantic_quality_bias(): int {
	$e = getenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC_QUALITY_BIAS');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 0;
	}
	return max(-25, min(15, (int) $e));
}

function fractal_zip_pdf_pac_cjpeg_semantic_min_savings_pct(): float {
	$e = getenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC_MIN_SAVINGS_PCT');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 2.0;
	}
	return max(0.1, min(60.0, (float) $e));
}

/**
 * No lossy cjpeg if below w×h (megapixels) — further lossy is usually obvious. Default 0.10 (~316×316).
 * FRACTAL_ZIP_PDF_JPEG_SEMANTIC_LOSSY_MIN_MEGAPIXELS
 */
function fractal_zip_pdf_pac_cjpeg_semantic_lossy_min_megapixels(): float {
	$e = getenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC_LOSSY_MIN_MEGAPIXELS');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 0.1;
	}
	return max(0.0, min(2.0, (float) $e));
}

/**
 * 8*enc/(w·h) below = “bit-starved” raster (bits per file-byte stack per pixel). See LOSSY + tiny mp.
 * FRACTAL_ZIP_PDF_JPEG_SEMANTIC_LOSSY_BPP_CRUSHED (default 0.20)
 */
function fractal_zip_pdf_pac_cjpeg_semantic_bpp_low_crushed_threshold(): float {
	$e = getenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC_LOSSY_BPP_CRUSHED');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 0.2;
	}
	return max(0.05, min(2.0, (float) $e));
}

/**
 * When mp is below this and BPP is below CRUSHED, skip lossy. Default 0.2. MEGAPIX_TINY_BPP_FLOOR_MPX
 */
function fractal_zip_pdf_pac_cjpeg_semantic_megapix_tiny_bpp_floor_mpx(): float {
	$e = getenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC_MEGAPIX_TINY_BPP_FLOOR_MPX');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 0.2;
	}
	return max(0.05, min(1.0, (float) $e));
}

/**
 * @return bool  False: skip lossy cjpeg; lossless steps still run above.
 */
function fractal_zip_pdf_pac_cjpeg_semantic_allows_lossy_recompress( int $w, int $h, int $encBytes): bool {
	$w = max(1, $w);
	$h = max(1, $h);
	$mp = ((float) $w * (float) $h) / 1000000.0;
	$minM = fractal_zip_pdf_pac_cjpeg_semantic_lossy_min_megapixels( );
	if ($minM > 0.0 && $mp < $minM) {
		return false;
	}
	$px = (float) $w * (float) $h;
	$bpp = $px > 0.0 ? 8.0 * (float) $encBytes / $px : 0.0;
	$tinyM = fractal_zip_pdf_pac_cjpeg_semantic_megapix_tiny_bpp_floor_mpx( );
	$crBpp = fractal_zip_pdf_pac_cjpeg_semantic_bpp_low_crushed_threshold( );
	if ($mp < $tinyM && $bpp < $crBpp) {
		return false;
	}
	return true;
}

/**
 * Flat: FRACTAL_ZIP_PDF_JPEG_SEMANTIC_MIN_SAVINGS_PCT; else megapixel + (optional) BPP “crush” bump.
 * @return float
 */
function fractal_zip_pdf_pac_cjpeg_semantic_effective_min_savings_pct( int $w, int $h, int $encBytes): float {
	$e = getenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC_MIN_SAVINGS_PCT');
	if ($e !== false && trim( (string) $e) !== '' && is_numeric( $e) ) {
		return max(0.05, min(60.0, (float) $e) );
	}
	$w = max(1, $w);
	$h = max(1, $h);
	$mp = ((float) $w * (float) $h) / 1000000.0;
	$px = (float) $w * (float) $h;
	$bpp = $px > 0.0 ? 8.0 * (float) $encBytes / $px : 0.0;
	$base = 0.3 + 5.0 / (1.0 + $mp * 6.0);
	$bump = 1.0;
	if ($bpp < 0.5 && $mp < 0.4) {
		$t = (0.4 - $mp) / 0.4;
		if ($t > 0) {
			$bump += (0.5 - $bpp) * 1.2 * $t;
		}
	}
	return max(0.2, min(7.0, $base * $bump) );
}

/**
 * djpeg@ref vs djpeg@candidate P6 max |ΔR/G/B| (8-bit). Unset with semantic=1 → default 4. 0 = no PPM gate (size-only; dev only).
 */
function fractal_zip_pdf_pac_cjpeg_semantic_max_ppm_channel_diff(): int {
	$e = getenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC_MAX_PPM_CHANNEL_DIFF');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 4;
	}
	return max(0, min(20, (int) $e));
}

/**
 * @return int Max bytes 3·w·h to decode for semantic PPM check (default 96 MiB raster).
 */
function fractal_zip_pdf_pac_cjpeg_semantic_ppm_size_cap_bytes(): int {
	$e = getenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC_PPM_MAX_RASTER_BYTES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 100663296;
	}
	$n = (int) $e;
	if ($n < 1) {
		return 100663296;
	}
	return min(800 * 1024 * 1024, $n);
}

/**
 * P6: same 8-bit width×height×3, max channel abs diff or null on error.
 */
function fractal_zip_pdf_pac_ppm6_pair_max_channel_diff(string $pathA, string $pathB): ?int {
	$ba = fractal_zip_pdf_pac_ppm6_read_p6_body_from_file($pathA);
	$bb = fractal_zip_pdf_pac_ppm6_read_p6_body_from_file($pathB);
	if (!is_string($ba) || !is_string($bb) || $ba === '' || strlen($ba) !== strlen($bb)) {
		return null;
	}
	$maxD = 0;
	$ln = strlen($ba);
	for ($i = 0; $i < $ln; $i++) {
		$d = abs(ord($ba[$i]) - ord($bb[$i]));
		if ($d > $maxD) {
			$maxD = $d;
		}
	}
	return $maxD;
}

/**
 * P6: read binary body only (P6, w h, max, raw RGB8).
 * @return string|null
 */
function fractal_zip_pdf_pac_ppm6_read_p6_body_from_file(string $path) {
	$fp = @fopen($path, 'rb');
	if ($fp === false) {
		return null;
	}
	$l = fgets($fp, 8);
	if ($l === false || rtrim($l, "\r\n") !== 'P6') {
		fclose($fp);
		return null;
	}
	$w = 0;
	$h = 0;
	while (true) {
		$ln = fgets($fp, 1048576);
		if ($ln === false) {
			fclose($fp);
			return null;
		}
		$t = rtrim($ln, "\r\n");
		if ($t === '' || $t[0] === '#') {
			continue;
		}
		$tn = strlen($t);
		$wEnd = 0;
		while ($wEnd < $tn && $t[$wEnd] >= '0' && $t[$wEnd] <= '9') {
			$wEnd++;
		}
		if ($wEnd >= 1 && $wEnd < $tn && fractal_zip_pdf_dict_ws_byte_preg_s(ord($t[$wEnd]))) {
			$h0 = fractal_zip_pdf_dict_skip_ws_preg_s($t, $wEnd, $tn);
			$j = $h0;
			while ($j < $tn && $t[$j] >= '0' && $t[$j] <= '9') {
				$j++;
			}
			if ($j > $h0 && $j === $tn) {
				$w = (int) substr($t, 0, $wEnd);
				$h = (int) substr($t, $h0, $j - $h0);
				break;
			}
		}
		fclose($fp);
		return null;
	}
	$lmax = fgets($fp, 64);
	if ($lmax === false) {
		fclose($fp);
		return null;
	}
	$maxV = (int) trim($lmax, " \r\n");
	if ($maxV < 1 || $maxV > 255) {
		fclose($fp);
		return null;
	}
	$need = 3 * $w * $h;
	$out = (string) fread($fp, $need);
	fclose($fp);
	if (strlen($out) !== $need) {
		return null;
	}
	return $out;
}

/**
 * Lossy candidate: smaller JPEG must decode within max channel diff vs. reference JPEG (same djpeg; semantic proximity).
 * @return bool  true = accept, false = reject, null = cannot run check (treat as reject to stay safe)
 */
function fractal_zip_pdf_pac_jpeg_semantic_ppm_djpeg_ok( string $refJpg, string $candJpg, int $maxChannelDiff): ?bool {
	if ( $maxChannelDiff <= 0) {
		return true;
	}
	$st = getimagesizefromstring( $refJpg);
	if ( !is_array( $st) || !isset( $st[0], $st[1]) ) {
		return null;
	}
	$pix = 3 * max(1, (int) $st[0]) * max(1, (int) $st[1]);
	if ( $pix > fractal_zip_pdf_pac_cjpeg_semantic_ppm_size_cap_bytes( ) ) {
		return null;
	}
	$td = sys_get_temp_dir();
	$id = bin2hex( random_bytes(8));
	$tRef = $td . DIRECTORY_SEPARATOR . 'fzdjsd_' . $id . '_r.jpg';
	$tCand = $td . DIRECTORY_SEPARATOR . 'fzdjsd_' . $id . '_c.jpg';
	$pRef = $td . DIRECTORY_SEPARATOR . 'fzdjsd_' . $id . '_r.ppm';
	$pCand = $td . DIRECTORY_SEPARATOR . 'fzdjsd_' . $id . '_c.ppm';
	@unlink( $pRef);
	@unlink( $pCand);
	if ( file_put_contents( $tRef, $refJpg) === false || file_put_contents( $tCand, $candJpg) === false) {
		@unlink( $tRef);
		@unlink( $tCand);
		return null;
	}
	$okR = fractal_zip_pdf_pac_jpeg_djpeg_ppm_path( $tRef, $pRef);
	$okC = fractal_zip_pdf_pac_jpeg_djpeg_ppm_path( $tCand, $pCand);
	@unlink( $tRef);
	@unlink( $tCand);
	if ( ! $okR || ! $okC) {
		@unlink( $pRef);
		@unlink( $pCand);
		return null;
	}
	$md = fractal_zip_pdf_pac_ppm6_pair_max_channel_diff( $pRef, $pCand);
	@unlink( $pRef);
	@unlink( $pCand);
	if ( $md === null) {
		return null;
	}
	return $md <= $maxChannelDiff;
}

/**
 * @return array{0:int,1:int}|null
 */
function fractal_zip_pdf_pac_jpeg_dims_from_bytes(string $jpgBytes): ?array {
	$g = @getimagesizefromstring($jpgBytes);
	if (!is_array($g) || !isset($g[0], $g[1])) {
		return null;
	}
	$w = max(1, (int) $g[0]);
	$h = max(1, (int) $g[1]);
	return [$w, $h];
}

function fractal_zip_pdf_pac_semantic_quality_for_dims(int $w, int $h): int {
	$mp = ((float) $w * (float) $h) / 1000000.0;
	$base = 90 - (int) floor(max(0.0, log(max(1.0, $mp), 2.0)) * 3.0);
	$q = max(68, min(92, $base));
	$q += fractal_zip_pdf_pac_cjpeg_semantic_quality_bias();
	return max(50, min(95, $q));
}

/**
 * djpeg to PPM; returns path to temp .ppm (caller must unlink) or null.
 */
function fractal_zip_pdf_pac_jpeg_djpeg_ppm_path( string $jpgFile, string $ppmPath): bool {
	$ex = 1;
	exec( 'djpeg -fast -ppm -outfile ' . escapeshellarg( $ppmPath) . ' ' . escapeshellarg( $jpgFile) . ' 2>/dev/null', $null, $ex);
	if ( $ex !== 0 || ! is_file( $ppmPath) || filesize( $ppmPath) < 1) {
		@unlink( $ppmPath);
		return false;
	}
	return true;
}

/**
 * cjpeg; returns true if $outJpg was written.
 *
 * @param list<string> $cjpegArgList args between "cjpeg" and "-outfile -in"
 */
function fractal_zip_pdf_pac_cjpeg_run( array $cjpegArgList, string $in, string $outJpg): bool {
	$args = 'cjpeg ' . implode( ' ', array_map( 'escapeshellarg', $cjpegArgList) ) . ' -outfile ' . escapeshellarg( $outJpg) . ' ' . escapeshellarg( $in) . ' 2>/dev/null';
	$ex = 1;
	exec( $args, $null, $ex);
	if ( $ex !== 0 || ! is_file( $outJpg) || ! fractal_zip_pdf_pac_jpeg_soi_eoi_valid( (string) file_get_contents( $outJpg) ) ) {
		@unlink( $outJpg);
		return false;
	}
	return true;
}

/**
 * Tries lossless jpegtran rewrites and optional cjpeg (PPM-cmp to original); returns smallest, or null if no improvement.
 */
function fractal_zip_pdf_pac_jpeg_tran_optim( string $buf): ?string {
	if ( ! fractal_zip_pdf_pac_jpeg_soi_eoi_valid( $buf) ) {
		return null;
	}
	$td = sys_get_temp_dir();
	$id = bin2hex( random_bytes( 8) );
	$in = $td . DIRECTORY_SEPARATOR . 'fzdj_' . $id . '.jpg';
	$best = null;
	$bl = strlen( $buf);
	$cands = array( array( '-optimize' ), array( '-progressive', '-optimize' ) );
	$work = $buf;
	$maxRef = fractal_zip_pdf_pac_jpeg_tran_max_refine_passes( );
	for ( $pass = 0; $pass < $maxRef; $pass++ ) {
		if ( file_put_contents( $in, $work) === false) {
			if ( $pass === 0) {
				return null;
			}
			break;
		}
		$roundBest = null;
		$roundTh = strlen( $work);
		foreach ( $cands as $a) {
			$ou = $td . DIRECTORY_SEPARATOR . 'fzdj_' . $id . '_o_' . (string) $pass . '_' . sha1( implode( "\0", $a) ) . '.jpg';
			$o = fractal_zip_pdf_pac_jpeg_tran_to_out( $a, $in, $ou);
			if ( $o === null) {
				continue;
			}
			$l = strlen( $o);
			if ( $l < $roundTh && ( $roundBest === null || $l < strlen( $roundBest) ) ) {
				$roundBest = $o;
				$roundTh = $l;
			}
		}
		if ( $roundBest === null) {
			break;
		}
		$work = $roundBest;
	}
	if ( strlen( $work) < $bl) {
		$best = $work;
	}
	$refP = $td . DIRECTORY_SEPARATOR . 'fzdj_' . $id . '_ref.ppm';
	$okRef = false;
	if ( fractal_zip_pdf_pac_cjpeg_extras_enabled( ) && fractal_zip_pdf_pac_cjpeg_djpeg_on_path( ) && fractal_zip_pdf_pac_jpeg_djpeg_ppm_path( $in, $refP) ) {
		$okRef = is_file( $refP) && filesize( $refP) > 0;
	}
	if ( $okRef) {
		$tryCj = array(
			array( '-quality', '100', '-optimize' ),
			array( '-quality', '100', '-progressive', '-optimize' ),
		);
		$i = 0;
		foreach ( $tryCj as $a) {
			$i++;
			$cjO = $td . DIRECTORY_SEPARATOR . 'fzdj_' . $id . '_cj' . (string) $i . '.jpg';
			$cP = $td . DIRECTORY_SEPARATOR . 'fzdj_' . $id . '_cj' . (string) $i . '.ppm';
			@unlink( $cjO);
			@unlink( $cP);
			if ( ! fractal_zip_pdf_pac_cjpeg_run( $a, $in, $cjO) ) {
				continue;
			}
			$o = (string) file_get_contents( $cjO);
			@unlink( $cjO);
			if ( ! fractal_zip_pdf_pac_jpeg_soi_eoi_valid( $o) || $o === $buf) {
				continue;
			}
			$tmpC = $td . DIRECTORY_SEPARATOR . 'fzdj_' . $id . '_cand' . (string) $i . '.jpg';
			if ( file_put_contents( $tmpC, $o) === false) {
				continue;
			}
			if ( ! fractal_zip_pdf_pac_jpeg_djpeg_ppm_path( $tmpC, $cP) ) {
				@unlink( $tmpC);
				continue;
			}
			@unlink( $tmpC);
			$ex2 = 1;
			exec( 'cmp -s ' . escapeshellarg( $refP) . ' ' . escapeshellarg( $cP) . ' 2>/dev/null', $n2, $ex2);
			@unlink( $cP);
			if ( $ex2 !== 0) {
				continue;
			}
			$l = strlen( $o);
			$th = $bl;
			if ( $best !== null) {
				$th = min( $th, strlen( $best) );
			}
			if ( $l < $th) {
				$best = $o;
			}
		}
	}
	if (fractal_zip_pdf_pac_cjpeg_semantic_enabled() && fractal_zip_pdf_pac_cjpeg_djpeg_on_path()) {
		$dims = fractal_zip_pdf_pac_jpeg_dims_from_bytes($buf);
		if (is_array($dims)) {
			$wD = (int) $dims[0];
			$hD = (int) $dims[1];
			$mdMax = fractal_zip_pdf_pac_cjpeg_semantic_max_ppm_channel_diff();
			$minPct = fractal_zip_pdf_pac_cjpeg_semantic_effective_min_savings_pct($wD, $hD, $bl);
			$baseQ = fractal_zip_pdf_pac_semantic_quality_for_dims($wD, $hD);
			$qs = array_values(array_unique(array(
				max(50, min(95, $baseQ)),
				max(50, min(95, $baseQ - 4)),
				max(50, min(95, $baseQ - 8)),
			)));
			if (fractal_zip_pdf_pac_cjpeg_semantic_allows_lossy_recompress($wD, $hD, $bl) ) {
				$k = 0;
				foreach ($qs as $qv) {
					foreach (array(array('-quality', (string) $qv, '-optimize'), array('-quality', (string) $qv, '-progressive', '-optimize')) as $a) {
						$k++;
						$cjO = $td . DIRECTORY_SEPARATOR . 'fzdj_' . $id . '_sem' . (string) $k . '.jpg';
						@unlink($cjO);
						if (!fractal_zip_pdf_pac_cjpeg_run($a, $in, $cjO) ) {
							continue;
						}
						$o = (string) @file_get_contents($cjO);
						@unlink($cjO);
						if (!fractal_zip_pdf_pac_jpeg_soi_eoi_valid($o) || $o === $buf) {
							continue;
						}
						$l = strlen($o);
						$th = $bl;
						if ($best !== null) {
							$th = min($th, strlen($best) );
						}
						$savedPct = $bl > 0 ? (100.0 * ($bl - $l) / $bl) : 0.0;
						if ($l < $th && $savedPct >= $minPct) {
							$okSem = fractal_zip_pdf_pac_jpeg_semantic_ppm_djpeg_ok($buf, $o, $mdMax);
							if ($okSem === null || $okSem === false) {
								continue;
							}
							$best = $o;
						}
					}
				}
			}
		}
	}
	@unlink( $refP);
	@unlink( $in);
	if ( $best === null) {
		return null;
	}
	return (string) $best;
}

function fractal_zip_pdf_pac_dict_dct_only( string $dict): bool {
	if (substr_count($dict, '/Filter') > 1) {
		return false;
	}
	if ( str_contains( $dict, '/FlateDecode' ) || str_contains( $dict, '/LZW' ) || str_contains( $dict, '/JPX' ) || str_contains( $dict, '/CCITT' ) || str_contains( $dict, '/JBIG2' ) ) {
		return false;
	}
	return (bool) (fractal_zip_pdf_dict_has_filter_key_then_slash_name($dict, '/DCTDecode', false)
		|| fractal_zip_pdf_dict_has_filter_array_dct_decode($dict));
}

/**
 * @return string|null
 */
function fractal_zip_pdf_pac_recompress_dct_jpeg_all_passes( string $pdf, int $maxR = 8): ?string {
	if ( !fractal_zip_pdf_jpeg_pac_enabled( ) || !fractal_zip_pdf_jpeg_tran_on_path( ) ) {
		return null;
	}
	$work = $pdf;
	$any = false;
	$omap = fractal_zip_pdf_object_index_offsets( $work);
	for ( $r = 0; $r < $maxR; $r++ ) {
		$ch = false;
		$M0 = fractal_zip_pdf_pac_stream_token_offsets($work);
		$nm = count($M0);
		if ($nm < 1) {
			break;
		}
		for ( $idx = $nm - 1; $idx >= 0; $idx-- ) {
			$e = $M0[$idx] ?? null;
			if ( !is_array( $e) || $e[1] < 0) {
				continue;
			}
			$abs0 = (int) $e[1];
			$tok = (string) $e[0];
			$open = fractal_zip_pdf_stream_dict_opening_lt_lt( $work, $abs0);
			if ( $open < 0) {
				continue;
			}
			$dict = (string) substr( $work, $open, $abs0 - $open + 2);
			if ( !fractal_zip_pdf_pac_dict_dct_only( $dict) ) {
				continue;
			}
			$lenB = fractal_zip_pdf_dict_length_value( $work, $dict, $omap);
			if ( $lenB === null) {
				continue;
			}
			$dataStart = $abs0 + strlen( $tok);
			$head = (string) substr( $work, 0, $open);
			$close = $abs0;
			$lineRest = ( $close + 2 <= $dataStart) ? (string) substr( $work, $close + 2, $dataStart - ( $close + 2) ) : '';
			$oldEnc = (string) substr( $work, $dataStart, $lenB);
			$n0 = strlen( $work);
			$check = (string) substr( $work, $dataStart + $lenB, (int) min( 32, $n0 - $dataStart - $lenB) );
			if (! fractal_zip_pdf_dict_prefix_endstream_after_preg_s_ws($check)) {
				continue;
			}
			$opt = fractal_zip_pdf_pac_jpeg_tran_optim( $oldEnc);
			if ( $opt === null) {
				continue;
			}
			if ( strlen( $opt) >= strlen( $oldEnc) ) {
				continue;
			}
			if (fractal_zip_pdf_dict_has_indirect_length_ref($dict)) {
				continue;
			}
			$rep = fractal_zip_pdf_dict_replace_first_length_digits_preg_s($dict, strlen($opt));
			if (! $rep[1]) {
				continue;
			}
			$nd2 = $rep[0];
			$suffix = (string) substr( $work, $dataStart + $lenB);
			$work = $head . $nd2 . $lineRest . $opt . $suffix;
			$ch = $any = true;
		}
		if ( ! $ch) {
			break;
		}
		$omap = fractal_zip_pdf_object_index_offsets( $work);
	}
	return $any ? $work : null;
}

function fractal_zip_pdf_jpeg_tran_on_path(): bool {
	static $b = null;
	if ( $b !== null) {
		return $b;
	}
	$p = shell_exec( 'command -v jpegtran 2>/dev/null' );
	return $b = ( is_string( $p) && trim( $p) !== '' );
}

/**
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_pdf_jpeg_pac_recompress_smaller( string $pdf): ?array {
	if ( !fractal_zip_pdf_jpeg_pac_enabled( ) || !fractal_zip_pdf_jpeg_tran_on_path( ) ) {
		return null;
	}
	$n0 = strlen( $pdf);
	if ( $n0 < 64 || !str_starts_with( $pdf, '%PDF-') || $n0 > 128 * 1024 * 1024) {
		return null;
	}
	$nw = fractal_zip_pdf_pac_recompress_dct_jpeg_all_passes( $pdf);
	if ( $nw === null) {
		return null;
	}
	$s = $n0 - strlen( $nw);
	if ( $s <= 0) {
		return null;
	}
	if ( !str_starts_with( $nw, '%PDF-') ) {
		return null;
	}
	return array( (string) $nw, (int) $s);
}
