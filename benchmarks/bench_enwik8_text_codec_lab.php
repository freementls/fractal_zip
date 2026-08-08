#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Run text-representation permutations on enwik8_sample5 and rank gzip-1 + entropy.
 *
 * Usage:
 *   php benchmarks/build_enwik8_sample_pages.php
 *   php benchmarks/bench_enwik8_text_codec_lab.php
 *   php benchmarks/bench_enwik8_text_codec_lab.php --quick
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

$sampleDir = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'corpus' . DIRECTORY_SEPARATOR . 'enwik8_sample5';
$manifestPath = $sampleDir . DIRECTORY_SEPARATOR . 'manifest.json';
if (!is_file($manifestPath)) {
	fwrite(STDERR, "Run: php benchmarks/build_enwik8_sample_pages.php\n");
	exit(1);
}
$manifest = json_decode((string) file_get_contents($manifestPath), true);
if (!is_array($manifest)) {
	exit(1);
}

$quick = in_array('--quick', $argv, true);
$codecs = fractal_zip_enwik_text_codec_catalog();
$transforms = fractal_zip_enwik_text_transform_catalog();
if ($quick) {
	$codecs = array('raw', 'morse', 'bits_msb', 'words_base94', 'words_base94_isp', 'words_id_varint_isp', 'words_id_varint', 'letter_scrabble');
	$transforms = array('none', 'sort_lines_alpha', 'perm_lines');
}

$pipelines = array();
foreach ($codecs as $codec) {
	foreach ($transforms as $tr) {
		if ($tr === 'sort_tokens_alpha' && $codec !== 'raw' && !str_starts_with($codec, 'words_')) {
			continue;
		}
		$pipelines[] = array('codec' => $codec, 'transform' => $tr);
	}
}
// Chains: codec then meta-sort by title key (conceptual — per-page only here).
$pipelines[] = array('codec' => 'words_base94', 'transform' => 'perm_tokens', 'seed' => 42);
$pipelines[] = array('codec' => 'words_base94_isp', 'transform' => 'sort_lines_alpha');
$pipelines[] = array('codec' => 'bits_msb', 'transform' => 'reverse_lines');

$rows = array();
foreach ($manifest['pages'] as $pageMeta) {
	$textPath = $sampleDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $pageMeta['text_path']);
	$text = (string) file_get_contents($textPath);
	$rawTextLen = strlen($text);
	$textMeas = fractal_zip_enwik_text_measure_payload($text);
	$rows[] = array(
		'page_id' => $pageMeta['id'],
		'title' => $pageMeta['title'],
		'codec' => '_baseline_text',
		'transform' => 'none',
		'text_bytes' => $rawTextLen,
		'payload_bytes' => $rawTextLen,
		'gzip1_bytes' => $textMeas['gzip1_bytes'],
		'entropy_bpb' => $textMeas['entropy_bits_per_byte'],
		'outer_affinity' => array('fractal', 'zpaq', 'brotli'),
		'roundtrip_ok' => true,
		'text_gzip1_bytes' => $textMeas['gzip1_bytes'],
	);
	foreach ($pipelines as $pipe) {
		$codec = (string) $pipe['codec'];
		$tr = (string) $pipe['transform'];
		$sidecarPre = array('seed' => (int) ($pipe['seed'] ?? 1));
		try {
			$enc = fractal_zip_enwik_text_codec_encode($codec, $text);
			$mergedSidecar = $enc['sidecar'];
			$t = fractal_zip_enwik_text_transform_apply($tr, (string) $enc['payload'], $sidecarPre);
			$payload = (string) $t['payload'];
			$fullSidecar = array_merge($mergedSidecar, $t['sidecar']);
			$undoText = fractal_zip_enwik_text_transform_undo($tr, $payload, $fullSidecar);
			$dec = fractal_zip_enwik_text_codec_decode($codec, $undoText, $mergedSidecar);
			$strictCodec = ($codec === 'raw' || str_ends_with($codec, '_isp')) && $tr === 'none';
			$roundtripOk = $strictCodec ? ($dec === $text) : (strlen($dec) > 0);
			$meas = fractal_zip_enwik_text_measure_payload($payload);
			$tg = $textMeas['gzip1_bytes'] ?? null;
			$rows[] = array(
				'page_id' => $pageMeta['id'],
				'title' => $pageMeta['title'],
				'codec' => $codec,
				'transform' => $tr,
				'text_bytes' => $rawTextLen,
				'payload_bytes' => $meas['payload_bytes'],
				'gzip1_bytes' => $meas['gzip1_bytes'],
				'text_gzip1_bytes' => $tg,
				'vs_text_gzip1_ratio' => ($tg > 0 && isset($meas['gzip1_bytes']))
					? round(100.0 * (int) $meas['gzip1_bytes'] / (int) $tg, 2)
					: null,
				'entropy_bpb' => $meas['entropy_bits_per_byte'],
				'outer_affinity' => fractal_zip_enwik_text_outer_affinity($codec),
				'roundtrip_ok' => $roundtripOk,
				'vocab_size' => $enc['meta']['vocab_size'] ?? null,
			);
		} catch (Throwable $e) {
			$rows[] = array(
				'page_id' => $pageMeta['id'],
				'title' => $pageMeta['title'],
				'codec' => $codec,
				'transform' => $tr,
				'error' => $e->getMessage(),
			);
		}
	}
}

usort($rows, static function (array $a, array $b): int {
	$ga = $a['gzip1_bytes'] ?? PHP_INT_MAX;
	$gb = $b['gzip1_bytes'] ?? PHP_INT_MAX;
	if ($ga !== $gb) {
		return $ga <=> $gb;
	}
	return strcmp(($a['codec'] ?? '') . ($a['transform'] ?? ''), ($b['codec'] ?? '') . ($b['transform'] ?? ''));
});

$out = array(
	'generated' => date('c'),
	'sample' => $manifestPath,
	'pipeline_count' => count($pipelines),
	'rows' => $rows,
);
$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_text_codec_lab.json';
file_put_contents($outPath, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "enwik8 text codec lab (" . count($rows) . " rows)\n";
$shown = 0;
foreach ($rows as $r) {
	if (isset($r['error'])) {
		continue;
	}
	if ($shown >= 15) {
		break;
	}
	printf(
		"  gzip1=%8s  ent=%.2f  %-18s %-16s  %s\n",
		number_format((int) ($r['gzip1_bytes'] ?? 0)),
		(float) ($r['entropy_bpb'] ?? 0),
		$r['codec'],
		$r['transform'],
		substr((string) $r['title'], 0, 32)
	);
	$shown++;
}
echo "Wrote {$outPath}\n";
