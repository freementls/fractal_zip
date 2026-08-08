#!/usr/bin/env php
<?php
/**
 * Byte accounting for FZB literal mode 16 (BSS2: core template + per-cell shift + raw rims).
 *
 *   php examples/bss2_byte_math.php
 *
 * Correct pixel counts (24×24 cell):
 *   - 24×24 = 576 pixels per cell (not "464" for the core).
 *   - 22×22 = 484 pixels in the core (≈ 484/576 ≈ 84% of the cell area — often rounded as "~80%").
 *
 * Per cell (24 bpp): full cell 576×3 = 1728 B; core 22×22×3 = 1452 B; rim (576−484)×3 = 276 B.
 *
 * For 96 cells, if the core is *exactly* a constant BGR shift of one shared 22×22 template:
 *   - Template T = 1452 B (once)
 *   - Shifts D = 96 × 3 B = 288 B  (your "<s square 0 dR dG dB>" idea; we store dB,dG,dR in BMP order)
 *   - Rims R = 96 × 276 B = 26496 B (must be stored raw for lossless BMP rebuild)
 *   - Subtotal (pixel sidecar, before BMP header + BSS2 tag/varints) ≈ T + D + R = 28236 B
 *
 * Compare to naive packed body: 96 × 1728 = 165888 B → 28236/165888 ≈ 17% of naive pixel bytes
 * (i.e. ~83% reduction on that structured representation — consistent with "~80%" area intuition
 * once you add the unavoidable rim + shift overhead).
 *
 * grid_01.bmp: lossless constant BGR shift does *not* hold on the full 22×22 core (anti-aliased edges).
 * The encoder therefore picks a smaller core (e.g. margin 2 → 20×20) where the relation holds, which
 * increases rim bytes a lot → larger stored BSS2 than the 22×22 formula above.
 */
declare(strict_types=1);

$cellPx = 24 * 24;
$core22 = 22 * 22;
$rim22 = $cellPx - $core22;
$cells = 12 * 8;
$T = $core22 * 3;
$D = $cells * 3;
$R = $cells * $rim22 * 3;
$naive = $cells * $cellPx * 3;

echo "BSS2 lossless byte math (24×24 cells, 22×22 core, BGR triple shift, 96 cells)\n\n";
echo "  Core pixels: 22×22 = {$core22} (not 464; 464 would be a different geometry).\n";
echo "  Core / cell: {$core22} / {$cellPx} = " . round(100 * $core22 / $cellPx, 2) . "% of cell area.\n\n";
echo "  T (one template)     = {$T} B\n";
echo "  D (96 × 3 BGR δ)     = {$D} B   ← compact 'shift' payload; scalar kind would be 96 B\n";
echo "  R (96 cell rims)     = {$R} B   ← lossless: borders are not predicted by the shift\n";
echo "  T + D + R            = " . ($T + $D + $R) . " B  (+ ~40 B BSS2 varints after BMP header)\n";
echo "  Naive 96×576×3       = {$naive} B\n";
echo "  Ratio (T+D+R)/naive  = " . round(100 * ($T + $D + $R) / $naive, 2) . "%  (~" . round(100 * (1 - ($T + $D + $R) / $naive)) . "% reduction vs storing cores verbatim)\n\n";

echo "gzip is a separate layer: high-entropy rims hurt deflate on raw BMP; BSS2 moves rim bytes\n";
echo "into a long literal run that still costs bytes under gzip — compare gzdeflate-1 sizes in\n";
echo "examples/bss2_core_grid_sizes.php for real files.\n";
