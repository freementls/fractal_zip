#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Skeleton-split lab: isolate low-entropy sk stream, FZ inner on sk only, beat-14.6 extrapolation.
 *
 * Usage:
 *   php benchmarks/bench_consonant_sk_split_extrap.php [--pages=64] [--sk-inner=cycle_inner|dict_inner|none]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_syllable_codec.php';
require_once $repo . '/fractal_zip_enwik_text_codec.php';
require_once $repo . '/benchmarks/enwik8_beat146_gate.php';

$pages = 64;
$skInner = 'none';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(8, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--sk-inner=')) {
		$skInner = trim(substr($arg, 11));
	}
}
putenv('FRACTAL_ZIP_CONSONANT_SK_FREQ_VOCAB=1');
putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');
if ($skInner !== 'none') {
	putenv('FRACTAL_ZIP_CONSONANT_SK_INNER=' . $skInner);
}

$src = $repo . '/test_files109/enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}

$pageTexts = array();
$n = min($pages, count($split['pages']));
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$pageXml = substr($blob, (int) $p['start'], (int) $p['len']);
	$pageTexts[] = fractal_zip_enwik_extract_page_preserve_text($pageXml);
}
$textBuf = implode('', $pageTexts);
$model = fractal_zip_enwik_consonant_hybrid_mine_model_pages($pageTexts, $textBuf);
$sharedMeta = fractal_zip_enwik_consonant_hybrid_shared_meta_from_model($model);

$schemes = array(
	'raw' => static fn (string $t): array => array('payload' => $t, 'sidecar' => array(), 'meta' => array()),
	'consonant_hybrid' => static function (string $t) use ($model): array {
		return fractal_zip_enwik_consonant_hybrid_preprocess($t, array('consonant_model' => $model, 'frozen' => true));
	},
	'consonant_hybrid_split' => static function (string $t) use ($model): array {
		return fractal_zip_enwik_consonant_hybrid_split_preprocess($t, $model, array('frozen' => true));
	},
	'consonant_hybrid_lossy' => static function (string $t) use ($model): array {
		return fractal_zip_enwik_consonant_hybrid_lossy_preprocess($t, array('consonant_model' => $model, 'frozen' => true));
	},
);

$undo = array(
	'raw' => static fn (string $p, array $s): string => $p,
	'consonant_hybrid' => static function (string $p, array $s) use ($sharedMeta): string {
		return fractal_zip_enwik_consonant_hybrid_undo($p, fractal_zip_enwik_merge_consonant_hybrid_sidecar($s, $sharedMeta));
	},
	'consonant_hybrid_split' => static function (string $p, array $s) use ($sharedMeta): string {
		return fractal_zip_enwik_consonant_hybrid_split_undo($p, fractal_zip_enwik_merge_consonant_hybrid_sidecar($s, $sharedMeta));
	},
	'consonant_hybrid_lossy' => static function (string $p, array $s) use ($sharedMeta): string {
		return fractal_zip_enwik_consonant_hybrid_lossy_undo($p, fractal_zip_enwik_merge_consonant_hybrid_sidecar($s, $sharedMeta));
	},
);

echo "bench_consonant_sk_split_extrap | @{$n}p sk_inner={$skInner} freq_vocab=1\n\n";
printf("%-24s %8s %8s %8s %8s %6s %s\n", 'scheme', 'payload', 'gzip1', 'lit_gz', 'sk_gz', 'RT', 'notes');
printf("%-24s %8s %8s %8s %8s %6s %s\n", '', '', '', '', '', '', '');

$rows = array();
$rawGz = (int) (fractal_zip_enwik_text_measure_payload($textBuf)['gzip1_bytes'] ?? 0);
foreach ($schemes as $name => $fn) {
	$r = $fn($textBuf);
	$payload = (string) $r['payload'];
	$sidecar = is_array($r['sidecar'] ?? null) ? $r['sidecar'] : array();
	$meta = is_array($r['meta'] ?? null) ? $r['meta'] : array();
	$restored = $undo[$name]($payload, $sidecar);
	$rt = $restored === $textBuf ? 'ok' : ($name === 'consonant_hybrid_lossy' ? 'lossy' : 'FAIL');
	$gz = (int) (fractal_zip_enwik_text_measure_payload($payload)['gzip1_bytes'] ?? 0);
	$litGz = 0;
	$skGz = 0;
	$note = '';
	if ($name === 'consonant_hybrid_split') {
		$sent = FRACTAL_ZIP_CONSONANT_SK_SPLIT_SENTINEL;
		$pos = strpos($payload, $sent);
		if ($pos !== false) {
			$lit = substr($payload, 0, $pos);
			$sk = substr($payload, $pos + strlen($sent));
			$litGz = (int) (fractal_zip_enwik_text_measure_payload($lit)['gzip1_bytes'] ?? 0);
			$skGz = (int) (fractal_zip_enwik_text_measure_payload($sk)['gzip1_bytes'] ?? 0);
			$note = sprintf(
				'lit=%s sk=%s sk_raw=%s meta_amort=%s',
				number_format(strlen($lit)),
				number_format(strlen($sk)),
				number_format((int) ($meta['sk_ascii_bytes'] ?? 0)),
				number_format(fractal_zip_enwik_consonant_hybrid_meta_wire_bytes($sharedMeta, $n))
			);
		}
	}
	if ($name === 'consonant_hybrid') {
		$note = 'sk=' . ($meta['encoded_skeletons'] ?? '?') . ' meta_amort='
			. number_format(fractal_zip_enwik_consonant_hybrid_meta_wire_bytes($sharedMeta, $n));
	}
	if ($name === 'consonant_hybrid_lossy') {
		$note = 'sk=' . ($meta['encoded_skeletons'] ?? '?') . ' ambig=' . ($meta['ambiguous_words'] ?? '?');
	}
	printf(
		"%-24s %8s %8s %8s %8s %6s %s\n",
		$name,
		number_format(strlen($payload)),
		number_format($gz),
		$litGz > 0 ? number_format($litGz) : '-',
		$skGz > 0 ? number_format($skGz) : '-',
		$rt,
		$note
	);
	$rows[] = array(
		'scheme' => $name,
		'payload_bytes' => strlen($payload),
		'gzip1_bytes' => $gz,
		'roundtrip' => $rt,
	);
}

$hybridGz = 0;
$splitGz = 0;
foreach ($rows as $row) {
	if ($row['scheme'] === 'consonant_hybrid') {
		$hybridGz = (int) $row['gzip1_bytes'];
	}
	if ($row['scheme'] === 'consonant_hybrid_split') {
		$splitGz = (int) $row['gzip1_bytes'];
	}
}

echo "\n--- beat-14.6 extrapolation (gzip1 lab proxy; wire uses phda9) ---\n";
echo "raw preserve-text gzip1 @{$n}p: " . number_format($rawGz) . "\n";
if ($hybridGz > 0) {
	$gateH = enwik8_beat146_gate_evaluate($hybridGz, $n);
	echo "consonant_hybrid gzip proxy extrap total S: " . number_format((int) $gateH['extrap_total_s'])
		. ' Δ=' . number_format((int) $gateH['delta_vs_target']) . "\n";
}
if ($splitGz > 0) {
	$gateS = enwik8_beat146_gate_evaluate($splitGz, $n);
	echo "consonant_hybrid_split gzip proxy extrap total S: " . number_format((int) $gateS['extrap_total_s'])
		. ' Δ=' . number_format((int) $gateS['delta_vs_target']) . "\n";
}
$budget = enwik8_beat146_wire_budget_384p();
echo "384p wire budget (archive): " . number_format((int) $budget['wire_budget_384p']) . " B\n";

$outJson = $repo . '/benchmarks/.enwik8_consonant_sk_split_' . $n . 'p.json';
file_put_contents($outJson, json_encode(array(
	'pages' => $n,
	'sk_inner' => $skInner,
	'raw_gzip1' => $rawGz,
	'rows' => $rows,
	'meta_amort_bytes' => fractal_zip_enwik_consonant_hybrid_meta_wire_bytes($sharedMeta, $n),
	'model_unique' => count($sharedMeta['skeleton_unique'] ?? array()),
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "\nWrote {$outJson}\n";
