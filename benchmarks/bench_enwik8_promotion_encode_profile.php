#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Profile promotion-encode dominant phases on a small slice (speed iteration).
 *
 * Usage:
 *   php -d memory_limit=2048M benchmarks/bench_enwik8_promotion_encode_profile.php [--pages=96]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_PARALLEL_PROBE=1');
putenv('FRACTAL_ZIP_PIPELINE_TIMING=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

$pageLimit = 96;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(16, (int) substr($arg, 8));
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
$pages = $split['pages'];
$n = min($pageLimit, count($pages));
$slice = $header;
for ($i = 0; $i < $n; $i++) {
	$slice .= substr($blob, (int) $pages[$i]['start'], (int) $pages[$i]['len']);
}
$slice .= $footer;

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_promo_prof_' . getmypid();
@mkdir($tmp, 0700, true);
$enwikPath = $tmp . DIRECTORY_SEPARATOR . 'enwik8';
file_put_contents($enwikPath, $slice);
$work = $tmp . DIRECTORY_SEPARATOR . 'work';
@mkdir($work, 0700, true);
copy($enwikPath, $work . DIRECTORY_SEPARATOR . 'enwik8');

bench_world_record_apply_pp96_core_env();
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');

$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($work, false);
$wall = round(microtime(true) - $t0, 3);
$fzc = $work . '.fz';
$fzcBytes = is_file($fzc) ? (int) filesize($fzc) : 0;

$timingPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.pipeline_timing_last.json';
$timing = is_file($timingPath) ? json_decode((string) file_get_contents($timingPath), true) : null;

$report = array(
	'generated' => date('c'),
	'pages' => $n,
	'fzc_bytes' => $fzcBytes,
	'wall_seconds' => $wall,
	'inner_frontier_jobs' => getenv('FZ_INNER_FRONTIER_JOBS') ?: 'unset',
	'pipeline_parallel' => getenv('FRACTAL_ZIP_PIPELINE_PARALLEL') ?: 'unset',
	'timing' => is_array($timing) ? $timing : null,
);
$out = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_promotion_encode_profile.json';
file_put_contents($out, json_encode($report, JSON_PRETTY_PRINT));
fractal_zip_enwik_recursive_remove($tmp);

echo "promotion encode profile ({$n} pages) → {$out}\n";
echo '  fzc_bytes=' . number_format($fzcBytes) . "  wall={$wall}s\n";
if (is_array($timing) && isset($timing['segments']) && is_array($timing['segments'])) {
	$top = $timing['segments'];
	usort($top, static fn ($a, $b): int => ((float) ($b['wall_seconds'] ?? 0)) <=> ((float) ($a['wall_seconds'] ?? 0)));
	foreach (array_slice($top, 0, 5) as $seg) {
		echo '  ' . ($seg['id'] ?? '?') . '  ' . ($seg['wall_seconds'] ?? '?') . "s\n";
	}
}
