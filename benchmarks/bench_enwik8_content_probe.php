#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Probe enwik8 byte budget by content subset before full 100 MiB encodes.
 *
 * Measures gzip-1 on: header, footer, page shells, article text (samples), codec payloads,
 * and optional mini .fz on sample5 pages (textcodec on vs pp96-core off).
 *
 * Usage:
 *   php benchmarks/build_enwik8_sample_pages.php [--count=20]
 *   php benchmarks/bench_enwik8_content_probe.php
 *   php benchmarks/bench_enwik8_content_probe.php --mini-fzc
 *   php benchmarks/summarize_enwik8_content_probe.php
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
$miniFzc = in_array('--mini-fzc', $argv, true);
$quick = in_array('--quick', $argv, true);

if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

$blob = (string) file_get_contents($src);
$blobLen = strlen($blob);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	fwrite(STDERR, "enwik split failed\n");
	exit(1);
}

$gzip1 = static function (string $s): int {
	$g = @gzdeflate($s, 1);
	return is_string($g) ? strlen($g) : 0;
};

$header = (string) $split['header'];
$footer = (string) $split['footer'];
$pages = $split['pages'];
$pageCount = count($pages);

$sumPageLen = 0;
$sumTextLen = 0;
$sumShellLen = 0;
foreach ($pages as $ref) {
	$page = substr($blob, (int) $ref['start'], (int) $ref['len']);
	$text = fractal_zip_enwik_extract_page_preserve_text($page);
	$sumPageLen += (int) $ref['len'];
	$sumTextLen += strlen($text);
	$sumShellLen += (int) $ref['len'] - strlen($text);
}

$codecScheme = 'words_base94_isp';
$codecTransform = 'sort_lines_alpha';
$codecCfg = array('scheme' => $codecScheme, 'transform' => $codecTransform, 'seed' => 1);

$subsets = array(
	array(
		'id' => 'header',
		'label' => 'XML header (pre-first-page)',
		'raw_bytes' => strlen($header),
		'gzip1_bytes' => $gzip1($header),
	),
	array(
		'id' => 'footer',
		'label' => 'XML footer (</mediawiki>)',
		'raw_bytes' => strlen($footer),
		'gzip1_bytes' => $gzip1($footer),
	),
	array(
		'id' => 'page_shells_all',
		'label' => 'Page markup minus <text> bodies (all pages)',
		'raw_bytes' => $sumShellLen,
		'gzip1_bytes' => $gzip1(str_repeat("\0", 1)), // placeholder; recompute below
	),
);

// Shell bytes: concatenate shell-only slices (cap for quick mode)
$shellBuf = '';
$shellCap = $quick ? (2 << 20) : PHP_INT_MAX;
$shellTaken = 0;
$marker = '<text xml:space="preserve">';
$openLen = strlen($marker);
foreach ($pages as $ref) {
	if ($shellTaken >= $shellCap) {
		break;
	}
	$page = substr($blob, (int) $ref['start'], (int) $ref['len']);
	$open = stripos($page, $marker);
	if ($open === false) {
		$shellBuf .= $page;
	} else {
		$close = stripos($page, '</text>', $open + $openLen);
		if ($close === false) {
			$shellBuf .= $page;
		} else {
			$shellBuf .= substr($page, 0, $open + $openLen) . substr($page, $close);
		}
	}
	$shellTaken = strlen($shellBuf);
}
$subsets[2]['raw_bytes'] = strlen($shellBuf);
$subsets[2]['gzip1_bytes'] = $gzip1($shellBuf);
$subsets[2]['note'] = $quick ? 'capped_2mib_pages' : 'all_pages';

$subsets[] = array(
	'id' => 'text_all_pages',
	'label' => 'All article <text> bodies (concat)',
	'raw_bytes' => $sumTextLen,
	'gzip1_bytes' => 0,
);
$textAll = '';
$textCap = $quick ? (8 << 20) : PHP_INT_MAX;
foreach ($pages as $ref) {
	if (strlen($textAll) >= $textCap) {
		break;
	}
	$page = substr($blob, (int) $ref['start'], (int) $ref['len']);
	$textAll .= fractal_zip_enwik_extract_page_preserve_text($page);
}
$subsets[3]['raw_bytes'] = strlen($textAll);
$subsets[3]['gzip1_bytes'] = $gzip1($textAll);
$subsets[3]['note'] = strlen($textAll) < $sumTextLen ? 'partial_cap' : 'full';

// Layout-variant text streams (fast sample5-style screening on slice)
$layoutPages = array();
$layoutCap = $quick ? min(20, $pageCount) : min(200, $pageCount);
for ($li = 0; $li < $layoutCap; $li++) {
	$ref = $pages[$li];
	$page = substr($blob, (int) $ref['start'], (int) $ref['len']);
	$parts = fractal_zip_enwik_split_page_shell_and_text($page);
	$layoutPages[] = array(
		'title' => (string) $ref['title'],
		'origIndex' => (int) $ref['origIndex'],
		'text' => $parts['text'],
		'text_chars' => strlen($parts['text']),
	);
}
foreach (array('mono_concat', 'sort_title', 'perm_shuffle', 'mi_reorder') as $layoutId) {
	try {
		$layout = fractal_zip_enwik_text_layout_apply($layoutPages, $layoutId, array('seed' => 1));
		$tb = (string) $layout['text_blob'];
		$subsets[] = array(
			'id' => 'layout_' . $layoutId,
			'label' => 'Text stream layout ' . $layoutId,
			'raw_bytes' => strlen($tb),
			'gzip1_bytes' => $gzip1($tb),
			'pages' => $layoutCap,
			'note' => $layoutCap < $pageCount ? 'partial_cap' : 'full',
		);
	} catch (Throwable $e) {
		$subsets[] = array(
			'id' => 'layout_' . $layoutId,
			'label' => 'Text stream layout ' . $layoutId,
			'error' => $e->getMessage(),
		);
	}
}

// Per-page codec sum (avoids OOM on full-corpus concat segment)
$ispPayloadBytes = 0;
$ispPayloadGzip1 = 0;
$ispSidecarJson = 0;
$ispPages = 0;
$ispCap = $quick ? 20 : 200;
foreach ($pages as $ref) {
	if ($ispPages >= $ispCap) {
		break;
	}
	$page = substr($blob, (int) $ref['start'], (int) $ref['len']);
	$text = fractal_zip_enwik_extract_page_preserve_text($page);
	if ($text === '') {
		continue;
	}
	$enc = fractal_zip_enwik_text_codec_encode($codecScheme, $text);
	$t = fractal_zip_enwik_text_transform_apply($codecTransform, (string) $enc['payload'], array('seed' => 1));
	$payload = (string) $t['payload'];
	$ispPayloadBytes += strlen($payload);
	$ispPayloadGzip1 += $gzip1($payload);
	$ispSidecarJson += strlen(json_encode($t['sidecar'], JSON_UNESCAPED_UNICODE) ?: '');
	$ispPages++;
}
$subsets[] = array(
	'id' => 'text_isp_payload',
	'label' => $codecScheme . '+' . $codecTransform . ' payload (per-page sum)',
	'raw_bytes' => $ispPayloadBytes,
	'gzip1_bytes' => $ispPayloadGzip1,
	'sidecar_json_bytes' => $ispSidecarJson,
	'pages_encoded' => $ispPages,
	'vs_text_gzip1_pct' => $subsets[3]['gzip1_bytes'] > 0
		? round(100.0 * $ispPayloadGzip1 / (int) $subsets[3]['gzip1_bytes'], 2) : null,
);

// sample5 corpus if present
$sampleDir = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'corpus'
	. DIRECTORY_SEPARATOR . 'enwik8_sample5';
$manifestPath = $sampleDir . DIRECTORY_SEPARATOR . 'manifest.json';
if (is_file($manifestPath)) {
	$manifest = json_decode((string) file_get_contents($manifestPath), true);
	$sumText = 0;
	$sumXml = 0;
	$sumPayloadG = 0;
	$sumTextG = 0;
	foreach ($manifest['pages'] ?? array() as $p) {
		$text = (string) file_get_contents($sampleDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $p['text_path']));
		$xml = (string) file_get_contents($sampleDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $p['page_path']));
		$enc = fractal_zip_enwik_text_codec_encode($codecScheme, $text);
		$tr = fractal_zip_enwik_text_transform_apply($codecTransform, (string) $enc['payload'], array('seed' => 1));
		$sumText += strlen($text);
		$sumXml += strlen($xml);
		$sumTextG += $gzip1($text);
		$sumPayloadG += $gzip1((string) $tr['payload']);
	}
	$subsets[] = array(
		'id' => 'sample5_text',
		'label' => 'sample5 article text',
		'raw_bytes' => $sumText,
		'gzip1_bytes' => $sumTextG,
		'pages' => count($manifest['pages'] ?? array()),
	);
	$subsets[] = array(
		'id' => 'sample5_page_xml',
		'label' => 'sample5 full page XML',
		'raw_bytes' => $sumXml,
		'gzip1_bytes' => $gzip1(implode('', array_map(static function (array $p) use ($sampleDir): string {
			return (string) file_get_contents($sampleDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $p['page_path']));
		}, $manifest['pages'] ?? array()))),
		'pages' => count($manifest['pages'] ?? array()),
	);
	$subsets[] = array(
		'id' => 'sample5_isp_payload',
		'label' => 'sample5 ' . $codecScheme . ' payload gzip-1',
		'raw_bytes' => 0,
		'gzip1_bytes' => $sumPayloadG,
		'vs_text_gzip1_pct' => $sumTextG > 0 ? round(100.0 * $sumPayloadG / $sumTextG, 2) : null,
	);
}

$decomposition = array(
	'blob_bytes' => $blobLen,
	'header_bytes' => strlen($header),
	'footer_bytes' => strlen($footer),
	'page_count' => $pageCount,
	'pages_total_bytes' => $sumPageLen,
	'text_total_bytes' => $sumTextLen,
	'shell_total_bytes' => $sumShellLen,
	'text_pct_of_blob' => $blobLen > 0 ? round(100.0 * $sumTextLen / $blobLen, 3) : 0,
	'shell_pct_of_blob' => $blobLen > 0 ? round(100.0 * $sumShellLen / $blobLen, 3) : 0,
);

$miniFzcRows = array();
if ($miniFzc && is_file($manifestPath)) {
	require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
	require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
	$manifest = json_decode((string) file_get_contents($manifestPath), true);
	$cases = array(
		'pp96_core_no_textcodec' => static function (): void {
			bench_world_record_apply_pp96_core_env();
			putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
			putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
			putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
			putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
			putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		},
		'pp96_core_textcodec_isp' => static function (): void {
			bench_world_record_apply_pp96_core_env();
			putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=words_base94_isp');
			putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_TRANSFORM=sort_lines_alpha');
			putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_SEED=1');
			putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
			putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
			putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
			putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
			putenv('FRACTAL_ZIP_WEB_REF=0');
		},
	);
	$probeDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_enwik_probe5_' . getmypid();
	@mkdir($probeDir . DIRECTORY_SEPARATOR . 'pages', 0755, true);
	foreach ($manifest['pages'] ?? array() as $p) {
		$from = $sampleDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $p['page_path']);
		$base = basename($from);
		copy($from, $probeDir . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $base);
	}
	$workSrc = $probeDir . DIRECTORY_SEPARATOR . 'pages';
	foreach ($cases as $label => $applyEnv) {
		$applyEnv();
		$work = $probeDir . DIRECTORY_SEPARATOR . 'run_' . $label;
		fractal_zip_enwik_recursive_remove($work);
		@mkdir($work, 0755, true);
		foreach (glob($workSrc . DIRECTORY_SEPARATOR . '*.xml') ?: array() as $f) {
			copy($f, $work . DIRECTORY_SEPARATOR . basename($f));
		}
		$fzc = $work . '.fz';
		@unlink($fzc);
		$t0 = microtime(true);
		$fz = new fractal_zip();
		$fz->zip_folder($work, false);
		$miniFzcRows[] = array(
			'label' => $label,
			'raw_bytes' => $sumXml,
			'fzc_bytes' => is_file($fzc) ? (int) filesize($fzc) : 0,
			'zip_seconds' => round(microtime(true) - $t0, 2),
			'outer_codec' => fractal_zip::$last_outer_codec ?? null,
		);
		@unlink($fzc);
	}
	fractal_zip_enwik_recursive_remove($probeDir);
}

$pp96Ref = 19594333;
$textcodecRef = null;
$expPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_pp96_textcodec.json';
if (is_file($expPath)) {
	$exp = json_decode((string) file_get_contents($expPath), true);
	foreach ($exp['cases'] ?? array() as $c) {
		if (($c['label'] ?? '') === 'test_files109') {
			$textcodecRef = (int) ($c['fzc_bytes'] ?? 0);
			break;
		}
	}
}

$avenues = array();
$avenues[] = array(
	'priority' => 1,
	'id' => 'sidecar_bloat',
	'finding' => 'Full .fz was ~62 MiB with verify OK while text gzip-1 on sample is ~0.6–24% of raw — wire overhead (EZTB, sidecars, unified inner) dominates.',
	'next' => 'Compare sort_lines_from perm sidecar size vs payload; rerun encode after perm-only sidecar (rerun15).',
);
$avenues[] = array(
	'priority' => 2,
	'id' => 'inner_at_scale',
	'finding' => '8 MiB inner slice: inner_focus tied baseline; full 100 MiB may differ.',
	'next' => 'php benchmarks/run_enwik8_inner_experiment.php --case=inner_recursive0 (and inner_combo) on full test_files109.',
);
$avenues[] = array(
	'priority' => 3,
	'id' => 'textcodec_on_off',
	'finding' => 'Text codec shrinks article text strongly; full archive size depends on outer+sidecar path.',
	'next' => $miniFzc ? 'See mini_fzc rows in this JSON' : 'Re-run with --mini-fzc on sample5 pages',
);
if ($textcodecRef !== null && $textcodecRef > $pp96Ref) {
	$avenues[] = array(
		'priority' => 4,
		'id' => 'beat_pp96',
		'finding' => sprintf('textcodec fzc %s B vs pp96 %s B (+%s B)', number_format($textcodecRef), number_format($pp96Ref), number_format($textcodecRef - $pp96Ref)),
		'next' => 'Do not enable high_env until inner+sidecar probes show win on subset; then one full encode.',
	);
}

$out = array(
	'generated' => date('c'),
	'decomposition' => $decomposition,
	'subsets' => $subsets,
	'mini_fzc' => $miniFzcRows,
	'refs' => array(
		'pp96_fzc_bytes' => $pp96Ref,
		'textcodec_fzc_bytes' => $textcodecRef,
	),
	'codec_wire' => $codecCfg,
	'avenues' => $avenues,
);
$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_content_probe.json';
file_put_contents($outPath, json_encode($out, JSON_PRETTY_PRINT));

echo "enwik8 content probe → {$outPath}\n";
echo '  blob=' . number_format($blobLen) . ' text=' . number_format($sumTextLen)
	. ' (' . $decomposition['text_pct_of_blob'] . "%) shell=" . number_format($sumShellLen) . "\n";
foreach ($subsets as $s) {
	echo '  [' . $s['id'] . '] raw=' . number_format((int) $s['raw_bytes'])
		. ' gzip1=' . number_format((int) $s['gzip1_bytes']) . "\n";
}
if ($miniFzcRows !== array()) {
	foreach ($miniFzcRows as $r) {
		echo '  mini_fzc ' . $r['label'] . '=' . number_format((int) $r['fzc_bytes']) . ' B in ' . $r['zip_seconds'] . "s\n";
	}
}
