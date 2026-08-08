#!/usr/bin/env php
<?php
/**
 * Per-cell linear color transform from a reference grid cell (grid_01.bmp).
 *
 * For each 24×24 cell, least-squares fit:  target_BGR ≈ round( ref_BGR @ M^T + b ) with M 3×3, b 1×3 (12 coeffs/channel layout: 3 outputs × 4 terms).
 * Reports match quality (exact / within-1 / within-5 / max error) and stream-style byte estimates:
 *   - template (reference cell) + 96 × 12 quantized coefficients
 *   - optional K-way codebook: 96 × 1 byte indices + K × 12 book bytes (toward a 96-byte "sidecar" of indices when K≤256)
 *
 *   php examples/grid_01_affine_stream_estimate.php [path/to/grid_01.bmp]
 */
declare(strict_types=1);

$bmpPath = $argv[1] ?? (dirname(__DIR__) . '/test_files61/01_raster_formats/grid_01.bmp');
if (!is_file($bmpPath)) {
	fwrite(STDERR, "Missing BMP: {$bmpPath}\n");
	exit(1);
}

$raw = (string) file_get_contents($bmpPath);
if (strlen($raw) < 54 || substr($raw, 0, 2) !== 'BM') {
	fwrite(STDERR, "Not a BMP.\n");
	exit(1);
}

$w = unpack('V', substr($raw, 18, 4))[1];
$hU = unpack('V', substr($raw, 22, 4))[1];
$h = (int) ($hU > 0x7FFFFFFF ? $hU - 0x100000000 : $hU);
$h = $h < 0 ? -$h : $h;
$bpp = unpack('v', substr($raw, 28, 2))[1];
$po = unpack('V', substr($raw, 10, 4))[1];
if ($bpp !== 24) {
	fwrite(STDERR, "Expected 24 bpp BI_RGB, got {$bpp}\n");
	exit(1);
}

$nc = 12;
$nr = 8;
$cw = intdiv($w, $nc);
$ch = intdiv($h, $nr);
if ($cw * $nc !== $w || $ch * $nr !== $h) {
	fwrite(STDERR, "Image {$w}×{$h} not divisible by {$nc}×{$nr} grid.\n");
	exit(1);
}

$rs = (int) (((($w * 24 + 31) >> 5) << 2));
$body = substr($raw, $po, $rs * $h);
if (strlen($body) < $rs * $h) {
	fwrite(STDERR, "Truncated pixel body.\n");
	exit(1);
}

/** One row per pixel, 3 cols B,G,R as floats 0..255 */
function extract_cell(string $body, int $hAbs, int $rowStride, int $w, int $bc, int $br, int $cw, int $ch): array
{
	$rows = $cw * $ch;
	$p = array_fill(0, $rows * 3, 0.0);
	$t = 0;
	for ($vy = $br * $ch; $vy < ($br + 1) * $ch; $vy++) {
		$sy = $hAbs - 1 - $vy;
		for ($vx = $bc * $cw; $vx < ($bc + 1) * $cw; $vx++) {
			$o = $sy * $rowStride + $vx * 3;
			$p[$t++] = (float) ord($body[$o]);
			$p[$t++] = (float) ord($body[$o + 1]);
			$p[$t++] = (float) ord($body[$o + 2]);
		}
	}
	return $p;
}

/** @param list<float> $p flat N*3 */
function cell_to_matrix(array $p, int $n): array
{
	$m = array_fill(0, $n, array_fill(0, 3, 0.0));
	for ($i = 0; $i < $n; $i++) {
		$m[$i][0] = $p[$i * 3];
		$m[$i][1] = $p[$i * 3 + 1];
		$m[$i][2] = $p[$i * 3 + 2];
	}
	return $m;
}

/**
 * Solve 4×4 linear system A x = b (A row-major flat 16, b len 4). Returns x or null.
 * @return list<float>|null
 */
function solve4(array $A, array $b): ?array
{
	// Augmented 4×5
	$m = array();
	for ($i = 0; $i < 4; $i++) {
		$row = array();
		for ($j = 0; $j < 4; $j++) {
			$row[] = $A[$i * 4 + $j];
		}
		$row[] = $b[$i];
		$m[] = $row;
	}
	for ($col = 0; $col < 4; $col++) {
		$piv = $col;
		$best = abs($m[$piv][$col]);
		for ($r = $col + 1; $r < 4; $r++) {
			$v = abs($m[$r][$col]);
			if ($v > $best) {
				$best = $v;
				$piv = $r;
			}
		}
		if ($best < 1e-12) {
			return null;
		}
		if ($piv !== $col) {
			$tmp = $m[$col];
			$m[$col] = $m[$piv];
			$m[$piv] = $tmp;
		}
		$div = $m[$col][$col];
		for ($j = $col; $j < 5; $j++) {
			$m[$col][$j] /= $div;
		}
		for ($r = 0; $r < 4; $r++) {
			if ($r === $col) {
				continue;
			}
			$f = $m[$r][$col];
			if (abs($f) < 1e-15) {
				continue;
			}
			for ($j = $col; $j < 5; $j++) {
				$m[$r][$j] -= $f * $m[$col][$j];
			}
		}
	}
	return [$m[0][4], $m[1][4], $m[2][4], $m[3][4]];
}

/**
 * Fit Q ≈ P M^T + b (rows). P,Q are n×3. Returns [M 3×3 row-major as 9 floats, b 3 floats].
 * @param list<list<float>> $P
 * @param list<list<float>> $Q
 * @return array{0: list<float>, 1: list<float>}
 */
function fit_affine_bgr(array $P, array $Q, int $n): array
{
	$XtX = array_fill(0, 16, 0.0);
	$XtY = array_fill(0, 12, 0.0);
	for ($i = 0; $i < $n; $i++) {
		$b = $P[$i][0];
		$g = $P[$i][1];
		$r = $P[$i][2];
		$x = array($b, $g, $r, 1.0);
		for ($j = 0; $j < 4; $j++) {
			for ($k = 0; $k < 4; $k++) {
				$XtX[$j * 4 + $k] += $x[$j] * $x[$k];
			}
			for ($c = 0; $c < 3; $c++) {
				$XtY[$c * 4 + $j] += $x[$j] * $Q[$i][$c];
			}
		}
	}
	$M = array_fill(0, 9, 0.0);
	$bias = array_fill(0, 3, 0.0);
	for ($c = 0; $c < 3; $c++) {
		$bcol = array(
			$XtY[$c * 4 + 0],
			$XtY[$c * 4 + 1],
			$XtY[$c * 4 + 2],
			$XtY[$c * 4 + 3],
		);
		$sol = solve4($XtX, $bcol);
		if ($sol === null) {
			$sol = array(0.0, 0.0, 0.0, 0.0);
		}
		$M[$c * 3 + 0] = $sol[0];
		$M[$c * 3 + 1] = $sol[1];
		$M[$c * 3 + 2] = $sol[2];
		$bias[$c] = $sol[3];
	}
	return array($M, $bias);
}

/** @param list<float> $M 9 @param list<float> $bias 3 */
function predict_pixel(array $pBgr, array $M, array $bias): array
{
	$b = $pBgr[0];
	$g = $pBgr[1];
	$r = $pBgr[2];
	$ob = $M[0] * $b + $M[1] * $g + $M[2] * $r + $bias[0];
	$og = $M[3] * $b + $M[4] * $g + $M[5] * $r + $bias[1];
	$orr = $M[6] * $b + $M[7] * $g + $M[8] * $r + $bias[2];
	return array(
		max(0.0, min(255.0, $ob)),
		max(0.0, min(255.0, $og)),
		max(0.0, min(255.0, $orr)),
	);
}

$nPix = $cw * $ch;
$refFlat = extract_cell($body, $h, $rs, $w, 0, 0, $cw, $ch);
$Pref = cell_to_matrix($refFlat, $nPix);

$cellsM = [];
$cellsB = [];
$allCoeffs = [];

for ($br = 0; $br < $nr; $br++) {
	for ($bc = 0; $bc < $nc; $bc++) {
		$tFlat = extract_cell($body, $h, $rs, $w, $bc, $br, $cw, $ch);
		$Q = cell_to_matrix($tFlat, $nPix);
		list($M, $bias) = fit_affine_bgr($Pref, $Q, $nPix);
		$cellsM[] = $M;
		$cellsB[] = $bias;
		for ($i = 0; $i < 9; $i++) {
			$allCoeffs[] = $M[$i];
		}
		for ($i = 0; $i < 3; $i++) {
			$allCoeffs[] = $bias[$i];
		}
	}
}

$maxAbs = 0.0;
foreach ($allCoeffs as $c) {
	$maxAbs = max($maxAbs, abs($c));
}
$scale = $maxAbs > 1e-9 ? ($maxAbs / 127.0) : 1.0;

/** @return list<int> 12 int8 */
function quantize12(array $M, array $bias, float $scale): array
{
	$out = [];
	for ($i = 0; $i < 9; $i++) {
		$v = (int) round($M[$i] / $scale);
		$out[] = max(-128, min(127, $v));
	}
	for ($i = 0; $i < 3; $i++) {
		$v = (int) round($bias[$i] / $scale);
		$out[] = max(-128, min(127, $v));
	}
	return $out;
}

/** @return array{M: list<float>, b: list<float>} */
function dequantize12(array $q, float $scale): array
{
	$M = [];
	for ($i = 0; $i < 9; $i++) {
		$M[] = $q[$i] * $scale;
	}
	$b = [];
	for ($i = 0; $i < 3; $i++) {
		$b[] = $q[9 + $i] * $scale;
	}
	return array('M' => $M, 'b' => $b);
}

/**
 * @param list<list<float>> $cellsM
 * @param list<list<float>> $cellsB
 * @return array{exact: int, within1: int, within5: int, totalPx: int, maxErr: int, sumSq: float}
 */
function eval_grid_vs_ref(
	string $body,
	int $h,
	int $rs,
	int $w,
	int $nc,
	int $nr,
	int $cw,
	int $ch,
	int $nPix,
	array $refFlat,
	array $cellsM,
	array $cellsB
): array {
	$exact = 0;
	$within1 = 0;
	$within5 = 0;
	$totalPx = 0;
	$maxErr = 0;
	$sumSq = 0.0;
	for ($br = 0; $br < $nr; $br++) {
		for ($bc = 0; $bc < $nc; $bc++) {
			$ci = $br * $nc + $bc;
			$M = $cellsM[$ci];
			$bias = $cellsB[$ci];
			$tFlat = extract_cell($body, $h, $rs, $w, $bc, $br, $cw, $ch);
			for ($i = 0; $i < $nPix; $i++) {
				$pb = array($refFlat[$i * 3], $refFlat[$i * 3 + 1], $refFlat[$i * 3 + 2]);
				$pr = predict_pixel($pb, $M, $bias);
				for ($c = 0; $c < 3; $c++) {
					$t = (int) round($tFlat[$i * 3 + $c]);
					$p = (int) round($pr[$c]);
					$e = abs($p - $t);
					$maxErr = max($maxErr, $e);
					$sumSq += (float) ($e * $e);
					if ($e === 0) {
						$exact++;
					}
					if ($e <= 1) {
						$within1++;
					}
					if ($e <= 5) {
						$within5++;
					}
					$totalPx++;
				}
			}
		}
	}
	return array(
		'exact' => $exact,
		'within1' => $within1,
		'within5' => $within5,
		'totalPx' => $totalPx,
		'maxErr' => $maxErr,
		'sumSq' => $sumSq,
	);
}

$floatStats = eval_grid_vs_ref($body, $h, $rs, $w, $nc, $nr, $cw, $ch, $nPix, $refFlat, $cellsM, $cellsB);

$qPerCell = [];
for ($ci = 0; $ci < 96; $ci++) {
	$qPerCell[] = quantize12($cellsM[$ci], $cellsB[$ci], $scale);
}

$dqM = [];
$dqB = [];
for ($ci = 0; $ci < 96; $ci++) {
	$dq = dequantize12($qPerCell[$ci], $scale);
	$dqM[] = $dq['M'];
	$dqB[] = $dq['b'];
}
$quantGlobalStats = eval_grid_vs_ref($body, $h, $rs, $w, $nc, $nr, $cw, $ch, $nPix, $refFlat, $dqM, $dqB);

$scalesPerCell = [];
$qPerCellLocal = [];
for ($ci = 0; $ci < 96; $ci++) {
	$maxC = 0.0;
	foreach ($cellsM[$ci] as $cf) {
		$maxC = max($maxC, abs($cf));
	}
	foreach ($cellsB[$ci] as $cf) {
		$maxC = max($maxC, abs($cf));
	}
	$s = $maxC > 1e-9 ? ($maxC / 127.0) : 1.0;
	$scalesPerCell[] = $s;
	$qPerCellLocal[] = quantize12($cellsM[$ci], $cellsB[$ci], $s);
}
$dqM2 = [];
$dqB2 = [];
for ($ci = 0; $ci < 96; $ci++) {
	$dq = dequantize12($qPerCellLocal[$ci], $scalesPerCell[$ci]);
	$dqM2[] = $dq['M'];
	$dqB2[] = $dq['b'];
}
$quantPerCellScaleStats = eval_grid_vs_ref($body, $h, $rs, $w, $nc, $nr, $cw, $ch, $nPix, $refFlat, $dqM2, $dqB2);

function fmt_stats(array $st, int $totalPx): array
{
	return array(
		100.0 * $st['exact'] / $totalPx,
		100.0 * $st['within1'] / $totalPx,
		100.0 * $st['within5'] / $totalPx,
		$st['maxErr'],
		sqrt($st['sumSq'] / $totalPx),
	);
}

$totalPx = $floatStats['totalPx'];
list($fEx, $f1, $f5, $fMax, $fRmse) = fmt_stats($floatStats, $totalPx);
list($qEx, $q1, $q5, $qMax, $qRmse) = fmt_stats($quantGlobalStats, $totalPx);
list($q2Ex, $q2_1, $q2_5, $q2Max, $q2Rmse) = fmt_stats($quantPerCellScaleStats, $totalPx);

$templateB = $nPix * 3;
$coef96 = 96 * 12;
$scaleB = 4;
$metaB = 14;

echo "grid_01 per-cell affine (ref = cell [0,0]): full float coeffs vs int8+scale\n";
echo "BMP: {$bmpPath} ({$w}×{$h}, cells {$nc}×{$nr} × {$cw}×{$ch} px)\n\n";
echo sprintf("  pixels compared: %d (96 cells × %d px × 3 ch)\n", $totalPx, $nPix);
echo "\n  Full-precision float (12× float32 per cell, not a 96 B sidecar):\n";
echo sprintf("    exact: %.3f%%  within ±1: %.3f%%  within ±5: %.3f%%  max|err|: %d  RMSE: %.4f\n", $fEx, $f1, $f5, $fMax, $fRmse);
echo "\n  int8×12 + one global float scale (1× float32 for all cells):\n";
echo sprintf("    exact: %.3f%%  within ±1: %.3f%%  within ±5: %.3f%%  max|err|: %d  RMSE: %.4f\n", $qEx, $q1, $q5, $qMax, $qRmse);
echo sprintf("    global scale: %.6g  max |float coef| before quant: %.6g\n", $scale, $maxAbs);
echo "\n  int8×12 + float scale per cell (96× float32; much easier on quant):\n";
echo sprintf("    exact: %.3f%%  within ±1: %.3f%%  within ±5: %.3f%%  max|err|: %d  RMSE: %.4f\n", $q2Ex, $q2_1, $q2_5, $q2Max, $q2Rmse);

echo "\n--- Stream-style payload (conceptual, not wire format) ---\n";
echo sprintf("  BMP header copy:     %d B\n", 54);
echo sprintf("  grid meta + magic:   ~%d B\n", $metaB);
echo sprintf("  template cell:       %d B\n", $templateB);
echo sprintf("  scale (float32):     %d B (global)\n", $scaleB);
echo sprintf("  96 × 12 int8 coeffs: %d B\n", $coef96);
echo sprintf("  ─────────────────────────────\n");
$directInner = 54 + $metaB + $templateB + $scaleB + $coef96;
echo sprintf("  subtotal (~inner):   %d B\n", $directInner);
$scale96B = 96 * 4;
$directInnerPerScale = 54 + $metaB + $templateB + $scale96B + $coef96;
echo sprintf("  alt: 96 × float32 scales + 1152 int8: ~%d B inner\n", $directInnerPerScale);
$blob = pack('f', $scale) . pack('N', $nc) . pack('N', $nr) . $refFlat;
foreach ($qPerCell as $q) {
	foreach ($q as $x) {
		$blob .= chr(($x + 256) & 255);
	}
}
$z = gzdeflate($blob, 1);
echo sprintf("  gzdeflate-1(blob w/o BMP header): %d B\n", $z === false ? 0 : strlen($z));

// K-means codebook on 12-dim quantized vectors
function kmeans_assign(array $points, array $centroids): array
{
	$assign = [];
	foreach ($points as $pi => $p) {
		$best = 0;
		$bestD = INF;
		foreach ($centroids as $ki => $c) {
			$d = 0.0;
			for ($d1 = 0; $d1 < 12; $d1++) {
				$t = $p[$d1] - $c[$d1];
				$d += $t * $t;
			}
			if ($d < $bestD) {
				$bestD = $d;
				$best = $ki;
			}
		}
		$assign[$pi] = $best;
	}
	return $assign;
}

function kmeans_update(array $points, array $assign, int $k): array
{
	$dim = 12;
	$sum = array_fill(0, $k, array_fill(0, $dim, 0.0));
	$cnt = array_fill(0, $k, 0);
	foreach ($points as $pi => $p) {
		$a = $assign[$pi];
		$cnt[$a]++;
		for ($d = 0; $d < $dim; $d++) {
			$sum[$a][$d] += $p[$d];
		}
	}
	$new = [];
	for ($ki = 0; $ki < $k; $ki++) {
		$row = [];
		if ($cnt[$ki] < 1) {
			$row = $points[array_rand($points)];
		} else {
			for ($d = 0; $d < $dim; $d++) {
				$row[] = $sum[$ki][$d] / $cnt[$ki];
			}
		}
		$new[] = $row;
	}
	return $new;
}

function kmeans_sse(array $points, array $centroids, array $assign): float
{
	$s = 0.0;
	foreach ($points as $pi => $p) {
		$c = $centroids[$assign[$pi]];
		for ($d = 0; $d < 12; $d++) {
			$t = $p[$d] - $c[$d];
			$s += $t * $t;
		}
	}
	return $s;
}

$pointsF = [];
foreach ($qPerCell as $q) {
	$row = [];
	foreach ($q as $v) {
		$row[] = (float) $v;
	}
	$pointsF[] = $row;
}

foreach (array(8, 16, 32, 64, 96) as $kUse) {
	$centroids = [];
	for ($i = 0; $i < $kUse; $i++) {
		$centroids[] = $pointsF[intdiv($i * 96, max(1, $kUse)) % 96];
	}
	$assign = [];
	for ($it = 0; $it < 25; $it++) {
		$assign = kmeans_assign($pointsF, $centroids);
		$centroids = kmeans_update($pointsF, $assign, $kUse);
	}
	$sse = kmeans_sse($pointsF, $centroids, $assign);
	$idxBytes = 96 * ($kUse <= 256 ? 1 : 2);
	$bookBytes = $kUse * 12;
	$sidecar = $idxBytes + $bookBytes;
	echo sprintf("\n  K=%d codebook on int8 coeff vectors:\n", $kUse);
	echo sprintf("    index payload:     %d B (96 cells)\n", $idxBytes);
	echo sprintf("    codebook:          %d B (%d × 12 int8)\n", $bookBytes, $kUse);
	echo sprintf("    coeff sidecar:     %d B  ← '96 B' target is the index row when K≤256 (%s)\n", $sidecar, $idxBytes === 96 ? '96×1 byte indices' : '');
	echo sprintf("    k-means SSE (12D): %.2f\n", $sse);

	// Reconstruction error on actual pixels if each cell used centroid coeffs
	$exactK = 0;
	$w1K = 0;
	$totK = 0;
	$maxEK = 0;
	for ($br = 0; $br < $nr; $br++) {
		for ($bc = 0; $bc < $nc; $bc++) {
			$ci = $br * $nc + $bc;
			$ck = $assign[$ci];
			$cq = [];
			for ($d = 0; $d < 12; $d++) {
				$cq[] = max(-128, min(127, (int) round($centroids[$ck][$d] ?? 0.0)));
			}
			$dq = dequantize12($cq, $scale);
			$M = $dq['M'];
			$bias = $dq['b'];
			$tFlat = extract_cell($body, $h, $rs, $w, $bc, $br, $cw, $ch);
			for ($i = 0; $i < $nPix; $i++) {
				$pb = array($refFlat[$i * 3], $refFlat[$i * 3 + 1], $refFlat[$i * 3 + 2]);
				$pr = predict_pixel($pb, $M, $bias);
				for ($c = 0; $c < 3; $c++) {
					$t = (int) round($tFlat[$i * 3 + $c]);
					$p = (int) round($pr[$c]);
					$e = abs($p - $t);
					$maxEK = max($maxEK, $e);
					if ($e === 0) {
						$exactK++;
					}
					if ($e <= 1) {
						$w1K++;
					}
					$totK++;
				}
			}
		}
	}
	echo sprintf("    pixel exact (book): %.3f%%  within±1: %.3f%%  max|err|: %d\n", 100 * $exactK / $totK, 100 * $w1K / $totK, $maxEK);
	$innerK = 54 + $metaB + $templateB + $scaleB + $sidecar;
	echo sprintf("    + template+scale:  ~%d B conceptual inner (before outer gzip)\n", $innerK);
}

echo "\nNote: lossy vs disk BMP; FZB literal modes are lossless — this script sizes an experimental grid codec.\n";
