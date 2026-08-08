#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Full sub-1 bpc path matrix: preprocess × layout × model × stack.
 *
 * Usage:
 *   php benchmarks/bench_enwik8_sub1_bpc_matrix.php --pages=sample5
 *   php benchmarks/bench_enwik8_sub1_bpc_matrix.php --pages=384 --paths=top5_from_sample5
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';

$pagesArg = 'sample5';
$pathsFilter = '';
$quick = in_array('--quick', $argv, true);
$preprocessArg = '';
$modelsArg = '';
$stacksArg = '';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pagesArg = substr($arg, 8);
	}
	if (str_starts_with($arg, '--paths=')) {
		$pathsFilter = substr($arg, 8);
	}
	if (str_starts_with($arg, '--preprocess=')) {
		$preprocessArg = substr($arg, 13);
	}
	if (str_starts_with($arg, '--models=')) {
		$modelsArg = substr($arg, 9);
	}
	if (str_starts_with($arg, '--stacks=')) {
		$stacksArg = substr($arg, 9);
	}
}

$preprocessList = $preprocessArg !== ''
	? array_map('trim', explode(',', $preprocessArg))
	: ($quick
		? array('none', 'dict_nncp', 'isp_varint', 'wrt_xwrt')
		: fractal_zip_text_dict_preprocess_catalog());
$layoutList = $quick
	? array('mono_concat', 'sort_title', 'mi_reorder')
	: fractal_zip_enwik_text_layout_catalog();
$modelList = $modelsArg !== ''
	? array_map('trim', explode(',', $modelsArg))
	: ($quick
		? array('zpaq9', 'gpt2tc', 'nncp32', 'llmzip_llama7b', 'b64pack')
		: fractal_zip_text_compressor_model_catalog());
$stackList = $stacksArg !== ''
	? array_map('trim', explode(',', $stacksArg))
	: ($quick
		? array('none', 'zpaq9_brotli11')
		: fractal_zip_text_stacked_outer_catalog());

$topPaths = array();
if ($pathsFilter === 'top5_from_sample5') {
	$prev = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_sub1_bpc_matrix.json';
	if (is_file($prev)) {
		$prevData = json_decode((string) file_get_contents($prev), true);
		$prevRows = $prevData['rows'] ?? array();
		usort($prevRows, static fn (array $a, array $b): int => ((float) ($a['text_bpc'] ?? 999)) <=> ((float) ($b['text_bpc'] ?? 999)));
		foreach (array_slice($prevRows, 0, 5) as $pr) {
			$topPaths[] = array(
				'preprocess' => $pr['preprocess'] ?? 'none',
				'layout' => $pr['layout'] ?? 'mono_concat',
				'model' => $pr['model'] ?? 'zpaq9',
				'stack' => $pr['stack'] ?? 'none',
			);
		}
	}
}

$split = null;
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
	$split = array('pages' => $pages, 'text_total_chars' => 0);
	foreach ($pages as $pg) {
		$split['text_total_chars'] += (int) $pg['text_chars'];
	}
	$label = 'sample5';
} else {
	$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
	$pageLimit = (int) $pagesArg;
	if ($pageLimit <= 0) {
		$pageLimit = 384;
	}
	$blob = (string) file_get_contents($src);
	$split = fractal_zip_enwik_split_shell_and_text($blob, $pageLimit);
	if ($split === null) {
		exit(1);
	}
	$label = $pageLimit . 'p';
}

$paqTimeout = $quick ? 45 : 0;
if ($quick) {
	putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=45');
}
$rows = array();
$pathNum = 0;
$combos = $topPaths !== array()
	? $topPaths
	: array();
if ($combos === array()) {
	foreach ($preprocessList as $pre) {
		foreach ($layoutList as $layout) {
			foreach ($modelList as $model) {
				foreach ($stackList as $stack) {
					$combos[] = array('preprocess' => $pre, 'layout' => $layout, 'model' => $model, 'stack' => $stack);
				}
			}
		}
	}
}

foreach ($combos as $combo) {
	$pre = (string) $combo['preprocess'];
	$layout = (string) $combo['layout'];
	$model = (string) $combo['model'];
	$stack = (string) $combo['stack'];
	$pathId = $pre . '__' . $layout . '__' . $model . '__' . $stack;
	$pathNum++;
	fwrite(STDERR, "[{$pathNum}/" . count($combos) . "] {$pathId}\n");
	try {
		$layoutR = fractal_zip_enwik_text_layout_apply($split['pages'], $layout, array('seed' => 1));
		$textBlob = (string) $layoutR['text_blob'];
		$textChars = (int) $layoutR['text_chars'];
		$preR = fractal_zip_text_preprocess_apply($pre, $textBlob);
		$payload = (string) $preR['payload'];
		$modelR = fractal_zip_text_compressor_run($model, $payload, array(
			'timeout_sec' => $paqTimeout,
			'cpu_fallback' => $quick,
		));
		$modelPayload = is_string($modelR['payload'] ?? null) ? (string) $modelR['payload'] : $payload;
		$stackR = fractal_zip_text_stacked_outer_apply($stack, $modelPayload);
		$compressed = (int) ($stackR['total_bytes'] ?? strlen($modelPayload));
		$textBpc = fractal_zip_enwik_text_bpc($compressed, $textChars);
		$rows[] = array(
			'path_id' => $pathId,
			'preprocess' => $pre,
			'layout' => $layout,
			'model' => $model,
			'stack' => $stack,
			'text_raw_chars' => $textChars,
			'text_payload_bytes' => strlen($payload),
			'model_bytes' => $modelR['compressed_bytes'] ?? null,
			'stack_bytes' => $compressed,
			'text_bpc' => $textBpc,
			'full_projected_bpc' => round(8.0 * $compressed / 100000000, 6),
			'status' => (string) ($modelR['status'] ?? 'unknown'),
			'roundtrip_ok' => (bool) ($modelR['roundtrip_ok'] ?? false) && (bool) ($stackR['roundtrip_ok'] ?? false),
			'gpu_device' => $modelR['gpu_device'] ?? fractal_zip_text_compressor_gpu_name(),
			'gpu_used' => !empty($modelR['gpu_device']),
			'gpu_required' => (bool) ($modelR['gpu_required'] ?? false),
			'wall_seconds' => round((float) ($modelR['wall_seconds'] ?? 0), 3),
			'decompresser_bytes' => $modelR['decompresser_bytes'] ?? null,
			'unavailable_reason' => $modelR['unavailable_reason'] ?? null,
			'tool' => $modelR['tool'] ?? null,
		);
	} catch (Throwable $e) {
		$rows[] = array(
			'path_id' => $pathId,
			'preprocess' => $pre,
			'layout' => $layout,
			'model' => $model,
			'stack' => $stack,
			'status' => 'failed',
			'error' => $e->getMessage(),
		);
	}
}

usort($rows, static fn (array $a, array $b): int => ((float) ($a['text_bpc'] ?? 999)) <=> ((float) ($b['text_bpc'] ?? 999)));
$out = array(
	'generated' => date('c'),
	'pages' => $label,
	'page_count' => count($split['pages']),
	'text_total_chars' => (int) ($split['text_total_chars'] ?? 0),
	'gpu_available' => fractal_zip_text_compressor_gpu_available(),
	'gpu_device' => fractal_zip_text_compressor_gpu_name(),
	'ltcb_anchors' => fractal_zip_enwik_ltcb_anchor_table(),
	'rows' => $rows,
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_sub1_bpc_matrix.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));
echo "sub-1 bpc matrix ({$label}) → {$path}\n";
$sub1 = array_filter($rows, static fn (array $r): bool => isset($r['text_bpc']) && (float) $r['text_bpc'] < 1.0 && ($r['status'] ?? '') === 'ok');
echo '  rows: ' . count($rows) . '  sub-1 ok: ' . count($sub1) . "\n";
if ($rows !== array()) {
	$best = $rows[0];
	echo '  best: ' . ($best['path_id'] ?? '?') . ' text_bpc=' . ($best['text_bpc'] ?? '?') . ' status=' . ($best['status'] ?? '?') . "\n";
}
