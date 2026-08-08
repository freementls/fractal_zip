<?php
declare(strict_types=1);

/**
 * Amortized static-meta accounting for enwik8 slice probes.
 *
 * wire_fzc = honest slice .fz bytes
 * amortized_fzc = wire_fzc - static_meta + static_meta * (probe_pages / 12041)
 *
 * Usage:
 *   php benchmarks/bench_enwik8_amortized_meta.php [--pages=384] [--input=.enwik8_wire_slice_probe.json]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';

$pageLimit = 384;
$inputPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wire_slice_probe.json';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--input=')) {
		$inputPath = substr($arg, 8);
	}
}

/**
 * @param array<string, mixed> $row
 */
function bench_enwik8_amortized_static_meta(array $row, int $slicePages, ?int $fullPages = null): array
{
	$fullPages = $fullPages ?? FRACTAL_ZIP_ENWIK_INNER_FOLD_FULL_PAGES;
	$wireFzc = (int) ($row['wire_fzc'] ?? $row['fzc_bytes'] ?? 0);
	$staticMeta = (int) ($row['static_meta_bytes'] ?? $row['meta_bytes'] ?? 0);
	if ($staticMeta <= 0) {
		$staticMeta = (int) ($row['fold_total_bytes'] ?? 0)
			+ (int) ($row['stat_pred_inner_bytes'] ?? 0);
	}
	$amortizedSlice = fractal_zip_enwik_inner_fold_amortized_cost($staticMeta, $slicePages, $fullPages);
	$amortizedFzc = $wireFzc - $staticMeta + $amortizedSlice;
	return array(
		'wire_fzc' => $wireFzc,
		'static_meta_bytes' => $staticMeta,
		'amortized_fzc' => $amortizedFzc,
		'amortized_slice_meta' => $amortizedSlice,
	);
}

if (!is_file($inputPath)) {
	fwrite(STDERR, "Missing probe JSON: {$inputPath}\n");
	fwrite(STDERR, "Run: php -d memory_limit=2048M benchmarks/bench_enwik8_wire_slice_probe.php --pages={$pageLimit}\n");
	exit(1);
}

$probe = json_decode((string) file_get_contents($inputPath), true);
if (!is_array($probe) || !is_array($probe['rows'] ?? null)) {
	fwrite(STDERR, "Invalid probe JSON: {$inputPath}\n");
	exit(1);
}

$slicePages = (int) ($probe['pages'] ?? $pageLimit);
$fullPages = (int) ($probe['enwik8_full_pages'] ?? FRACTAL_ZIP_ENWIK_INNER_FOLD_FULL_PAGES);
$monoMi = (int) ($probe['mono_mi_bytes'] ?? 0);
$outRows = array();
foreach ($probe['rows'] as $row) {
	if (!is_array($row)) {
		continue;
	}
	$acct = bench_enwik8_amortized_static_meta($row, $slicePages, $fullPages);
	$outRows[] = array_merge($row, $acct, array(
		'delta_vs_mono_mi' => $monoMi > 0 ? $acct['amortized_fzc'] - $monoMi : null,
	));
}

usort($outRows, static fn (array $a, array $b): int => ((int) $a['amortized_fzc']) <=> ((int) $b['amortized_fzc']));

$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_amortized_meta.json';
file_put_contents($outPath, json_encode(array(
	'generated' => date('c'),
	'source' => $inputPath,
	'pages' => $slicePages,
	'enwik8_full_pages' => $fullPages,
	'mono_mi_bytes' => $monoMi,
	'amortized_model' => 'wire_fzc - static_meta + static_meta * (slice_pages/full_pages)',
	'rows' => $outRows,
), JSON_PRETTY_PRINT));

echo "amortized meta ({$slicePages}p) → {$outPath}\n";
echo str_pad('case', 52) . str_pad('wire', 11, ' ', STR_PAD_LEFT)
	. str_pad('static', 10, ' ', STR_PAD_LEFT)
	. str_pad('amort', 11, ' ', STR_PAD_LEFT)
	. str_pad('Δ mono', 10, ' ', STR_PAD_LEFT) . "\n";
foreach ($outRows as $r) {
	if (!empty($r['error'])) {
		continue;
	}
	$d = $r['delta_vs_mono_mi'];
	echo str_pad((string) $r['label'], 52)
		. str_pad(number_format((int) $r['wire_fzc']), 11, ' ', STR_PAD_LEFT)
		. str_pad(number_format((int) $r['static_meta_bytes']), 10, ' ', STR_PAD_LEFT)
		. str_pad(number_format((int) $r['amortized_fzc']), 11, ' ', STR_PAD_LEFT)
		. str_pad($d === null ? '—' : (($d > 0 ? '+' : '') . number_format((int) $d)), 10, ' ', STR_PAD_LEFT)
		. "\n";
}
