#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Codec ceiling on text-inner pre-zpaq inner blobs @slice (zpaq vs phda9 vs gzip).
 *
 * Usage:
 *   php -d memory_limit=4096M benchmarks/bench_enwik8_text_inner_codec_ceiling.php [--pages=384]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_WEB_REF=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$pageLimit = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	}
}

/** @return array{inner: string, wire: int, layout: string, preprocess: string} */
function text_inner_codec_dump_inner(
	string $repo,
	int $pageLimit,
	string $layout,
	string $preprocess
): array {
	bench_world_record_apply_pp96_core_env();
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
	putenv('FRACTAL_ZIP_TEXT_INNER=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
	putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=' . $layout);
	putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=' . $preprocess);
	putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
	if ($preprocess === 'stat_pred_inner') {
		putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=base94');
	}

	$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
	$blob = (string) file_get_contents($src);
	$split = enwik_split_page_refs($blob);
	if ($split === null) {
		throw new RuntimeException('enwik split failed');
	}
	$n = min($pageLimit, count($split['pages']));
	$slice = (string) $split['header'];
	for ($i = 0; $i < $n; $i++) {
		$slice .= substr($blob, (int) $split['pages'][$i]['start'], (int) $split['pages'][$i]['len']);
	}
	$slice .= (string) $split['footer'];

	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_ti_codec_' . getmypid() . '_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$work = $tmp . DIRECTORY_SEPARATOR . 'work';
	@mkdir($work, 0700, true);
	file_put_contents($work . DIRECTORY_SEPARATOR . 'enwik8', $slice);
	$dumpPath = $tmp . DIRECTORY_SEPARATOR . 'inner.bin';
	@unlink($dumpPath);
	putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER=' . $dumpPath);
	$GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] = array();

	$fz = new fractal_zip();
	$fz->zip_folder($work, false);
	putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');
	$fzc = $work . '.fz';
	$wire = is_file($fzc) ? (int) filesize($fzc) : 0;
	$inner = is_file($dumpPath) ? (string) file_get_contents($dumpPath) : '';
	@unlink($dumpPath);
	@unlink($fzc);
	fractal_zip_enwik_recursive_remove($tmp);
	if ($inner === '') {
		throw new RuntimeException("empty inner layout={$layout} preprocess={$preprocess}");
	}
	return array('inner' => $inner, 'wire' => $wire, 'layout' => $layout, 'preprocess' => $preprocess);
}

function text_inner_codec_measure(string $label, string $inner, string $tmpDir): array
{
	$path = $tmpDir . DIRECTORY_SEPARATOR . $label . '.bin';
	file_put_contents($path, $inner);
	$fz = new fractal_zip(256, false, false, null, false);
	$row = array(
		'label' => $label,
		'payload_bytes' => strlen($inner),
		'gzip1_bytes' => null,
		'zpaq_m9_bytes' => null,
		'phda9_bytes' => null,
		'phda9_tool' => null,
	);
	$gz = @gzencode($inner, 1);
	if (is_string($gz)) {
		$row['gzip1_bytes'] = strlen($gz);
	}
	$zpaqExe = fractal_zip::zpaq_executable();
	if ($zpaqExe !== null) {
		$blob = $fz->outer_zpaq_blob_with_meth_fragment($zpaqExe, $inner, ' -method 9');
		if ($blob !== null && $blob !== '') {
			$row['zpaq_m9_bytes'] = strlen($blob);
		}
	}
	$tools = fractal_zip_paq_discover_tools();
	foreach (array('phda9', 'parallel_phda9') as $tid) {
		if (!isset($tools[$tid])) {
			continue;
		}
		$r = fractal_zip_paq_compress_file($tid, $tools[$tid], $path);
		if (($r['ok'] ?? false) && isset($r['bytes']) && is_string($r['bytes']) && $r['bytes'] !== '') {
			$row['phda9_bytes'] = strlen($r['bytes']);
			$row['phda9_tool'] = $tid;
			break;
		}
	}
	@unlink($path);
	return $row;
}

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_ti_codec_meas_' . getmypid();
@mkdir($tmp, 0700, true);

$targets = array(
	array('mono_mi', 'mi_reorder', 'none'),
	array('mono_concat', 'mono_concat', 'none'),
	array('stat_pred_inner', 'mono_concat', 'stat_pred_inner'),
);

$report = array(
	'generated' => date('c'),
	'pages' => $pageLimit,
	'rows' => array(),
);
foreach ($targets as $t) {
	list($id, $layout, $preprocess) = $t;
	$dump = text_inner_codec_dump_inner($repo, $pageLimit, $layout, $preprocess);
	$meas = text_inner_codec_measure($id, $dump['inner'], $tmp);
	$meas['integrated_wire_fzc'] = $dump['wire'];
	$meas['layout'] = $layout;
	$meas['preprocess'] = $preprocess;
	$report['rows'][] = $meas;
	echo $id . ' payload=' . number_format($meas['payload_bytes'])
		. ' gzip1=' . number_format((int) ($meas['gzip1_bytes'] ?? 0))
		. ' zpaq_m9=' . number_format((int) ($meas['zpaq_m9_bytes'] ?? 0))
		. ' phda9=' . ($meas['phda9_bytes'] !== null ? number_format((int) $meas['phda9_bytes']) : 'n/a')
		. ' wire=' . number_format($meas['integrated_wire_fzc']) . "\n";
}

$out = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_text_inner_codec_ceiling.json';
file_put_contents($out, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "→ {$out}\n";
fractal_zip_enwik_recursive_remove($tmp);
