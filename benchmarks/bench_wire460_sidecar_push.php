#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Sidecar compression push: codec sweep + honest dict fold + text-only article probes.
 *
 * Usage:
 *   php benchmarks/bench_wire460_sidecar_push.php [--pages=96] [--skip-wire] [--skip-sweep]
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_phda9_dict.php';
require_once $repo . '/fractal_zip_phda9_dict_mine.php';
require_once $repo . '/fractal_zip_enwik_text_inner_dict.php';
require_once $repo . '/fractal_zip_wiki_lom.php';
require_once $repo . '/fractal_zip_collision_free_abbrevs.php';

$pages = 96;
$skipWire = false;
$skipSweep = false;
$outJson = $repo . '/benchmarks/.enwik8_wire460_sidecar_push.json';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif ($arg === '--skip-wire') {
		$skipWire = true;
	} elseif ($arg === '--skip-sweep') {
		$skipSweep = true;
	} elseif (str_starts_with($arg, '--out=')) {
		$outJson = substr($arg, 6);
	}
}

putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=1');
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0');
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_DEEP=1');
putenv('FRACTAL_ZIP_INNER_FOLD_TRAILER_CODECS=zpaq9:zstd22:brotli11:gzip9');
putenv('FRACTAL_ZIP_INNER_FOLD_TRAILER_STACK_CODECS=zpaq9:zstd22');

$report = array(
	'generated' => date('c'),
	'pages' => $pages,
	'baseline_wire_384p' => 518508,
	'target_wire' => 460096,
	'sweep' => array(),
	'dict_fold_lab' => array(),
	'wire' => null,
);

$dict384 = $repo . '/benchmarks/.phda9_external_dict_words_' . $pages . 'p.txt';
$mixed = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
$src = $repo . '/test_files109/enwik8';
if (!is_file($src)) {
	$src = $repo . '/enwik8';
}
if (is_file($src) && !is_file($dict384) && $pages >= 96) {
	echo "mining words_{$pages}p dict...\n";
	$blob = (string) file_get_contents($src);
	$mined = fractal_zip_phda9_dict_mine_from_enwik($blob, array(
		'pages' => $pages,
		'mode' => 'words',
		'chunk_pages' => min(96, $pages),
	));
	fractal_zip_phda9_dict_write_file((array) $mined['words'], $dict384);
}

if (!$skipSweep) {
	$samples = array();
	if (is_file($mixed)) {
		$fold = fractal_zip_phda9_dict_build_fold_payload($mixed);
		if ($fold !== null) {
			$samples['phda9_dict_mixed'] = (string) $fold['payload'];
		}
	}
	if (is_file($dict384)) {
		$fold = fractal_zip_phda9_dict_build_fold_payload($dict384);
		if ($fold !== null) {
			$samples['phda9_dict_words_' . $pages . 'p'] = (string) $fold['payload'];
		}
	}
	if (is_file($src)) {
		$blob = (string) file_get_contents($src);
		$split = enwik_split_page_refs($blob);
		if ($split !== null) {
			$n = min($pages, count($split['pages']));
			$text = '';
			for ($i = 0; $i < $n; $i++) {
				$p = $split['pages'][$i];
				$text .= fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
			}
			require_once $repo . '/fractal_zip_text_dict_preprocess.php';
			$pre = fractal_zip_text_preprocess_apply('wiki_lom', $text, array('entity_decode' => true, 'frozen' => true));
			$tables = is_array($pre['sidecar'] ?? null) ? $pre['sidecar'] : array();
			$samples['wiki_lom_tables'] = fractal_zip_wiki_lom_inner_fold_blob($tables);
		}
	}
	foreach ($samples as $label => $innerBlob) {
		$raw = strlen($innerBlob);
		$sealed = fractal_zip_enwik_inner_fold_seal_trailer($label, $innerBlob);
		$packed = fractal_zip_enwik_inner_fold_pack_trailer($label, $innerBlob);
		$rt = fractal_zip_enwik_inner_fold_unpack_trailer((string) $sealed['wire']);
		$report['sweep'][] = array(
			'label' => $label,
			'raw_bytes' => $raw,
			'packed_bytes' => (int) ($sealed['packed_bytes'] ?? strlen($packed)),
			'wire_bytes' => (int) ($sealed['wire_bytes'] ?? 0),
			'codec' => (string) ($sealed['codec'] ?? '?'),
			'ratio' => $raw > 0 ? round((int) ($sealed['wire_bytes'] ?? 0) / $raw, 4) : null,
			'roundtrip_ok' => is_array($rt) && (string) ($rt['inner_blob'] ?? '') === $innerBlob,
		);
		printf("sweep %-28s raw=%8s wire=%8s codec=%s rt=%s\n",
			$label,
			number_format($raw),
			number_format((int) ($sealed['wire_bytes'] ?? 0)),
			(string) ($sealed['codec'] ?? '?'),
			(!empty($report['sweep'][count($report['sweep']) - 1]['roundtrip_ok'])) ? 'ok' : 'FAIL'
		);
	}
}

foreach (array($mixed, $dict384) as $dictPath) {
	if (!is_file($dictPath)) {
		continue;
	}
	$fold = fractal_zip_phda9_dict_build_fold_payload($dictPath);
	if ($fold === null) {
		continue;
	}
	$sealed = fractal_zip_enwik_inner_fold_seal_trailer('phda9_dict_fold', (string) $fold['payload']);
	$report['dict_fold_lab'][] = array(
		'dict' => basename($dictPath),
		'raw_dict_bytes' => (int) ($fold['raw_dict_bytes'] ?? 0),
		'fold_payload_bytes' => strlen((string) $fold['payload']),
		'wire_bytes' => (int) ($sealed['wire_bytes'] ?? 0),
		'codec' => (string) ($sealed['codec'] ?? '?'),
		'words' => (int) ($fold['words'] ?? 0),
	);
}

if (!$skipWire) {
	putenv('FRACTAL_ZIP_WIRE_PROBE_PAGES=' . (string) $pages);
	$cases = implode(',', array(
		'split_inner_phda9_xml_single_stream_lstm_words4096_dict',
		'split_inner_phda9_xml_single_stream_lstm_mixed_dict_fold',
		'split_inner_phda9_article_single_stream_lstm_mixed_dict',
		'split_inner_phda9_article_single_stream_lstm_mixed_dict_fold',
		'split_inner_phda9_xml_single_stream_lstm_wiki_lom_sidecar_push',
		'split_inner_phda9_xml_single_stream_lstm_wiki_lom_cfabb_inline_sidecar_push',
	));
	$wireOut = $repo . '/benchmarks/.enwik8_wire460_sidecar_push_' . $pages . 'p.json';
	$php = PHP_BINARY;
	$cmd = escapeshellarg($php) . ' -d memory_limit=2048M '
		. escapeshellarg($repo . '/benchmarks/bench_enwik8_wire_slice_probe.php')
		. ' --pages=' . $pages
		. ' --cases=' . escapeshellarg($cases)
		. ' --verify-rt'
		. ' --out-json=' . escapeshellarg($wireOut);
	echo "wire probe @{$pages}p...\n";
	exec($cmd, $xo, $ret);
	if ($ret === 0 && is_file($wireOut)) {
		$report['wire'] = json_decode((string) file_get_contents($wireOut), true);
		$report['wire_out'] = $wireOut;
		foreach (($report['wire']['rows'] ?? array()) as $row) {
			$lab = (string) ($row['label'] ?? '?');
			$wire = (int) ($row['wire_fzc'] ?? 0);
			$meta = (int) ($row['meta_bytes'] ?? 0);
			$codec = (string) ($row['inner_fold_trailer_codec'] ?? '');
			printf("  wire %-55s %8s B meta=%6s codec=%s rt=%s\n",
				$lab,
				number_format($wire),
				number_format($meta),
				$codec !== '' ? $codec : '-',
				!empty($row['roundtrip_ok']) ? 'ok' : (!empty($row['error']) ? 'ERR' : '?')
			);
		}
	} else {
		$report['wire_error'] = 'probe exit ' . $ret;
		fwrite(STDERR, implode("\n", $xo) . "\n");
	}
}

file_put_contents($outJson, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "report → {$outJson}\n";
