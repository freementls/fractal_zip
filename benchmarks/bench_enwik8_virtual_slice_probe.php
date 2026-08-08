#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Mini .fz on a real enwik8 blob slice (single extensionless member → entry sort + optional textcodec).
 *
 * Usage:
 *   php -d memory_limit=2048M benchmarks/bench_enwik8_virtual_slice_probe.php [--pages=96]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$pages = 96;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(1, (int) substr($arg, 8));
	}
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$header = (string) $split['header'];
$footer = (string) $split['footer'];
$slicePages = array_slice($split['pages'], 0, $pages);
$parts = array($header);
foreach ($slicePages as $ref) {
	$parts[] = substr($blob, (int) $ref['start'], (int) $ref['len']);
}
$parts[] = $footer;
$miniBlob = implode('', $parts);
$rawBytes = strlen($miniBlob);

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_enwik_slice_' . getmypid();
@mkdir($tmp, 0700, true);
$miniPath = $tmp . DIRECTORY_SEPARATOR . 'enwik8';
file_put_contents($miniPath, $miniBlob);

$cases = array(
	'no_textcodec' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'textcodec_isp' => static function (): void {
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

$rows = array();
foreach ($cases as $label => $apply) {
	$apply();
	$work = $tmp . DIRECTORY_SEPARATOR . $label;
	@mkdir($work, 0700, true);
	copy($miniPath, $work . DIRECTORY_SEPARATOR . 'enwik8');
	$fzc = $work . '.fz';
	@unlink($fzc);
	$t0 = microtime(true);
	$fz = new fractal_zip();
	$fz->zip_folder($work, false);
	$rows[] = array(
		'label' => $label,
		'pages' => count($slicePages),
		'raw_bytes' => $rawBytes,
		'fzc_bytes' => is_file($fzc) ? (int) filesize($fzc) : 0,
		'zip_seconds' => round(microtime(true) - $t0, 2),
		'outer_codec' => fractal_zip::$last_outer_codec ?? null,
		'member_count' => $fz->zip_folder_member_count,
	);
	@unlink($fzc);
}

fractal_zip_enwik_recursive_remove($tmp);

$out = array(
	'generated' => date('c'),
	'pages_in_slice' => count($slicePages),
	'raw_bytes' => $rawBytes,
	'rows' => $rows,
);
$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_virtual_slice_probe.json';
file_put_contents($outPath, json_encode($out, JSON_PRETTY_PRINT));

foreach ($rows as $r) {
	echo $r['label'] . ': ' . number_format((int) $r['fzc_bytes']) . ' B in ' . $r['zip_seconds'] . "s\n";
}
echo "wrote {$outPath}\n";
