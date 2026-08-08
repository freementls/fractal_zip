#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Hybrid text-inner layout × codec × outer probe.
 *
 * Usage:
 *   php benchmarks/build_enwik8_sample_pages.php
 *   php benchmarks/bench_enwik8_text_inner_layout_probe.php --pages=sample5
 *   php benchmarks/bench_enwik8_text_inner_layout_probe.php --pages=384 --quick
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';

$pagesArg = 'sample5';
$layoutsArg = '';
$codecsArg = '';
$quick = in_array('--quick', $argv, true);
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pagesArg = substr($arg, 8);
	}
	if (str_starts_with($arg, '--layouts=')) {
		$layoutsArg = substr($arg, 10);
	}
	if (str_starts_with($arg, '--codecs=')) {
		$codecsArg = substr($arg, 9);
	}
}

$layouts = $layoutsArg !== ''
	? array_map('trim', explode(',', $layoutsArg))
	: ($quick
		? array('mono_concat', 'sort_title', 'mi_reorder')
		: fractal_zip_enwik_text_layout_catalog());
$codecs = $codecsArg !== ''
	? array_map('trim', explode(',', $codecsArg))
	: array('words_id_varint_isp', 'words_base94_isp', 'raw');

$blob = '';
$pageLimit = null;
$label = '';
if ($pagesArg === 'sample5') {
	$sampleDir = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'corpus' . DIRECTORY_SEPARATOR . 'enwik8_sample5';
	$manifestPath = $sampleDir . DIRECTORY_SEPARATOR . 'manifest.json';
	if (!is_file($manifestPath)) {
		fwrite(STDERR, "Run: php benchmarks/build_enwik8_sample_pages.php\n");
		exit(1);
	}
	$manifest = json_decode((string) file_get_contents($manifestPath), true);
	$pages = array();
	foreach ($manifest['pages'] ?? array() as $p) {
		$xml = (string) file_get_contents($sampleDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $p['page_path']));
		$parts = fractal_zip_enwik_split_page_shell_and_text($xml);
		$pages[] = array(
			'title' => (string) ($p['title'] ?? ''),
			'origIndex' => (int) ($p['orig_index'] ?? count($pages)),
			'shell' => $parts['shell'],
			'text' => $parts['text'],
			'text_chars' => strlen($parts['text']),
		);
	}
	$split = array('header' => '', 'footer' => '', 'pages' => $pages, 'text_total_chars' => 0, 'shell_total_bytes' => 0);
	foreach ($pages as $pg) {
		$split['text_total_chars'] += (int) $pg['text_chars'];
		$split['shell_total_bytes'] += strlen((string) $pg['shell']);
	}
	$label = 'sample5';
} else {
	$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
	if (!is_file($src)) {
		fwrite(STDERR, "Missing {$src}\n");
		exit(1);
	}
	$blob = (string) file_get_contents($src);
	$pageLimit = $pagesArg === 'full' ? null : (int) $pagesArg;
	if ($pageLimit <= 0) {
		$pageLimit = 384;
	}
	$split = fractal_zip_enwik_split_shell_and_text($blob, $pageLimit);
	if ($split === null) {
		exit(1);
	}
	$label = (string) $pageLimit . 'p';
}

$gzip1 = static function (string $s): int {
	$g = @gzdeflate($s, 1);
	return is_string($g) ? strlen($g) : 0;
};

$shellBuf = '';
foreach ($split['pages'] as $pg) {
	$shellBuf .= (string) $pg['shell'];
}

$rows = array();
$anchors = fractal_zip_enwik_ltcb_anchor_table();
foreach ($layouts as $layoutId) {
	$layout = fractal_zip_enwik_text_layout_apply($split['pages'], $layoutId, array('seed' => 1));
	$textBlob = (string) $layout['text_blob'];
	$textChars = (int) $layout['text_chars'];
	$layoutMeta = $layout['meta'];

	foreach ($codecs as $codec) {
		$rowBase = array(
			'layout' => $layoutId,
			'text_codec' => $codec,
			'text_raw_chars' => $textChars,
			'shell_raw_bytes' => strlen($shellBuf),
			'shell_gzip1' => $gzip1($shellBuf),
		);
		try {
			if ($codec === 'raw') {
				$payload = $textBlob;
				$sidecar = array();
				$roundtripOk = true;
			} else {
				$enc = fractal_zip_enwik_text_codec_encode($codec, $textBlob, array());
				$t = fractal_zip_enwik_text_transform_apply('none', (string) $enc['payload'], array());
				$payload = (string) $t['payload'];
				$sidecar = array_merge($enc['sidecar'], $t['sidecar']);
				$undo = fractal_zip_enwik_text_transform_undo('none', $payload, $sidecar);
				$dec = fractal_zip_enwik_text_codec_decode($codec, $undo, $enc['sidecar']);
				$roundtripOk = str_ends_with($codec, '_isp') ? ($dec === $textBlob) : (strlen($dec) > 0);
			}
			$textPayloadBytes = strlen($payload);
			$metaBytes = strlen(json_encode($layoutMeta, JSON_UNESCAPED_UNICODE) ?: '');
			$splitInner = fractal_zip_enwik_build_split_inner_blob($payload, $shellBuf, $layoutMeta);
			$zpaq = fractal_zip_text_compressor_zpaq9($splitInner, array());
			$stack = fractal_zip_text_stacked_outer_apply('zpaq9_brotli11', $splitInner);
			$singleBest = fractal_zip_text_stacked_outer_apply('none', $splitInner);
			$rows[] = array_merge($rowBase, array(
				'text_payload_bytes' => $textPayloadBytes,
				'text_sidecar_bytes' => strlen(json_encode($sidecar, JSON_UNESCAPED_UNICODE) ?: ''),
				'text_bpc' => fractal_zip_enwik_text_bpc($textPayloadBytes, $textChars),
				'text_gzip1' => $gzip1($payload),
				'meta_bytes' => $metaBytes,
				'split_inner_bytes' => strlen($splitInner),
				'split_zpaq_m9' => $zpaq['compressed_bytes'] ?? null,
				'split_single_best' => $singleBest['total_bytes'],
				'split_best_stack' => $stack['total_bytes'],
				'split_best_stack_id' => 'zpaq9_brotli11',
				'split_stack_delta_vs_single' => (int) $singleBest['total_bytes'] - (int) $stack['total_bytes'],
				'roundtrip_ok' => $roundtripOk,
				'ltcb_anchor' => $anchors,
			));
		} catch (Throwable $e) {
			$rows[] = array_merge($rowBase, array('error' => $e->getMessage()));
		}
	}
}

$out = array(
	'generated' => date('c'),
	'pages' => $label,
	'page_count' => count($split['pages']),
	'text_total_chars' => (int) $split['text_total_chars'],
	'shell_total_bytes' => (int) $split['shell_total_bytes'],
	'layouts' => $layouts,
	'codecs' => $codecs,
	'rows' => $rows,
	'baseline_refs' => array(
		'wire_slice_no_textcodec' => 658258,
		'pp96_bpc' => 1.57,
		'phda9_bpc' => 1.20,
	),
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_text_inner_layout_probe.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));
echo "text inner layout probe ({$label}) → {$path}\n";
foreach ($rows as $r) {
	if (isset($r['error'])) {
		echo '  ERR ' . $r['layout'] . '/' . $r['text_codec'] . ': ' . $r['error'] . "\n";
		continue;
	}
	echo '  ' . $r['layout'] . '/' . $r['text_codec']
		. '  text_bpc=' . ($r['text_bpc'] ?? '?')
		. '  split_inner=' . number_format((int) ($r['split_inner_bytes'] ?? 0))
		. '  stack=' . number_format((int) ($r['split_best_stack'] ?? 0))
		. "\n";
}
