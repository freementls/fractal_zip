#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Sweep phda9 dict token strategies vs page-count: words, phrases, subwords, optimal mix.
 * Also compares QG hybrid preprocess (subword roots in payload, not external dict).
 *
 * Δ = FZPA bytes − baseline (negative = WIN). Scores mining corpus = same page prefix.
 *
 * Usage:
 *   nice -n 19 php benchmarks/bench_phda9_dict_token_sweep.php [--pages=96,384,768] [--compress]
 *   --compress  run phda9 (slow); default mining-only score estimates
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once $repo . '/fractal_zip_phda9_dict_mine.php';
require_once $repo . '/fractal_zip_phda9_dict.php';

$pageList = array(96, 384, 768);
$doCompress = false;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageList = array();
		foreach (explode(',', substr($arg, 8)) as $p) {
			$p = (int) trim($p);
			if ($p > 0) {
				$pageList[] = $p;
			}
		}
	} elseif ($arg === '--compress') {
		$doCompress = true;
	}
}
if ($pageList === array()) {
	$pageList = array(96);
}

$src = $repo . '/test_files109/enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	fwrite(STDERR, "enwik split failed\n");
	exit(1);
}

putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');

$phda = static function (string $plain, ?string $dictPath = null): array {
	if ($dictPath !== null && is_file($dictPath)) {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dictPath);
	} else {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
	}
	$r = fractal_zip_enwik_phda9_english_compress($plain, array(
		'tool' => 'phda9_no_lstm',
		'use_dict' => $dictPath !== null,
		'timeout_sec' => 0,
		'wire_wrap' => true,
	));
	return array(
		'bytes' => $r['bytes'] ?? null,
		'rt' => !empty($r['roundtrip_ok']),
		'sec' => (float) ($r['seconds'] ?? 0.0),
	);
};

$modes = array('words', 'phrases', 'subwords', 'optimal', 'mixed', 'mixed_tiered');
$rows = array();
$outJson = $repo . '/benchmarks/.enwik8_phda9_dict_token_sweep.json';

printf("bench_phda9_dict_token_sweep | compress=%s\n\n", $doCompress ? 'yes' : 'estimate-only');
printf("%6s %-10s %10s %10s %10s %8s %s\n", 'pages', 'mode', 'dict_B', 'est_save', 'FZPA_B', 'Δ base', 'notes');
printf("%6s %-10s %10s %10s %10s %8s %s\n", '', '', '', '', '', '', '');

foreach ($pageList as $pages) {
	$n = min($pages, count($split['pages']));
	$chunk = array();
	for ($i = 0; $i < $n; $i++) {
		$chunk[] = array(
			'origIndex' => $i,
			'start' => (int) $split['pages'][$i]['start'],
			'len' => (int) $split['pages'][$i]['len'],
		);
	}
	$pageXml = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob)['sorted_page_xml'];
	$baseline = null;
	if ($doCompress) {
		$baseline = $phda($pageXml, null);
	}

	foreach ($modes as $mode) {
		try {
			$mined = fractal_zip_phda9_dict_mine_from_enwik($blob, array('pages' => $n, 'mode' => $mode));
		} catch (Throwable $e) {
			fwrite(STDERR, "  {$n}p {$mode}: mine failed: " . $e->getMessage() . "\n");
			continue;
		}
		$dictPath = sys_get_temp_dir() . '/fz_phda9_dict_' . $mode . '_' . $n . '_' . getmypid() . '.txt';
		$written = fractal_zip_phda9_dict_write_file($mined['words'], $dictPath);
		$estSave = 0;
		foreach ($mined['words'] as $w) {
			$estSave += fractal_zip_phda9_dict_count_token($pageXml, $w)['est_save'];
		}
		$fzpa = null;
		$delta = null;
		$rt = null;
		if ($doCompress) {
			$fzpa = $phda($pageXml, $dictPath);
			if ($baseline !== null && $baseline['bytes'] !== null && $fzpa['bytes'] !== null) {
				$delta = (int) $fzpa['bytes'] - (int) $baseline['bytes'];
			}
			$rt = $fzpa['rt'] ?? false;
		}
		$hist = $mined['stats']['kind_hist'] ?? array();
		$notes = $hist !== array() ? json_encode($hist) : '';
		printf("%6d %-10s %10s %10s %10s %8s %s\n",
			$n,
			$mode,
			number_format((int) $written['bytes']),
			number_format($estSave),
			$fzpa !== null && $fzpa['bytes'] !== null ? number_format((int) $fzpa['bytes']) : '-',
			$delta !== null ? sprintf('%+d', $delta) : '-',
			$notes
		);
		$rows[] = array(
			'pages' => $n,
			'mode' => $mode,
			'dict_bytes' => (int) $written['bytes'],
			'dict_words' => (int) $written['words'],
			'est_save' => $estSave,
			'kind_hist' => $hist,
			'fzpa_bytes' => $fzpa['bytes'] ?? null,
			'delta_vs_nodict' => $delta,
			'roundtrip_ok' => $rt,
			'stats' => $mined['stats'],
		);
		@unlink($dictPath);
	}

	if ($doCompress && is_file('/srv/http/quantum_grammar/src/HybridRootCodec.php')) {
		require_once '/srv/http/quantum_grammar/src/HybridRootCodec.php';
		require_once $repo . '/fractal_zip_text_dict_preprocess.php';
		$model = HybridRootCodec::mineModel($pageXml);
		$enc = fractal_zip_text_qg_hybrid_root_preprocess($pageXml, array('qg_model' => $model, 'frozen' => false));
		$sidecar = json_encode($enc['sidecar']);
		$sc = is_string($sidecar) ? strlen($sidecar) : 0;
		$hy = $phda((string) $enc['payload'], null);
		$delta = ($baseline !== null && $baseline['bytes'] !== null && $hy['bytes'] !== null)
			? (int) $hy['bytes'] - (int) $baseline['bytes'] + $sc : null;
		printf("%6d %-10s %10s %10s %10s %8s %s\n",
			$n,
			'qg_hybrid',
			'-',
			'-',
			$hy['bytes'] !== null ? number_format((int) $hy['bytes']) : 'FAIL',
			$delta !== null ? sprintf('%+d', $delta) : '-',
			'sidecar=' . number_format($sc)
		);
		$rows[] = array(
			'pages' => $n,
			'mode' => 'qg_hybrid_preprocess',
			'sidecar_bytes' => $sc,
			'fzpa_bytes' => $hy['bytes'] ?? null,
			'delta_vs_nodict' => $delta,
			'roundtrip_ok' => $hy['rt'] ?? false,
		);
	}
	printf("\n");
}

file_put_contents($outJson, json_encode(array(
	'generated' => date('c'),
	'compress' => $doCompress,
	'rows' => $rows,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
printf("→ %s\n", $outJson);
fwrite(STDERR, "OK bench_phda9_dict_token_sweep\n");
