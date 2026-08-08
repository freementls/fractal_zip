#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Fast inner-env A/B on a multi-member enwik8 slice (~8 MiB raw).
 * Exercises unified-stream folder encode without a full 100 MiB run.
 *
 * Usage:
 *   FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1 php -d memory_limit=2048M benchmarks/bench_enwik8_inner_unified_probe.php
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_inner_env.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

$memberCount = 8;
$bytesPerMember = 1 << 20;
$totalRaw = $memberCount * $bytesPerMember;
$fh = fopen($src, 'rb');
$blob = fread($fh, $totalRaw);
fclose($fh);
if (!is_string($blob) || strlen($blob) < $bytesPerMember) {
	fwrite(STDERR, "Short read from enwik8\n");
	exit(1);
}

$tmpRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_enwik_inner_probe_' . getmypid();
if (!@mkdir($tmpRoot, 0700, true) && !is_dir($tmpRoot)) {
	exit(1);
}

for ($i = 0; $i < $memberCount; $i++) {
	$chunk = substr($blob, $i * $bytesPerMember, $bytesPerMember);
	$path = $tmpRoot . DIRECTORY_SEPARATOR . sprintf('slice_%03d.xml', $i);
	if (@file_put_contents($path, $chunk) === false) {
		fwrite(STDERR, "write failed {$path}\n");
		exit(1);
	}
}

$cases = array(
	'probe_baseline' => static function (): void {
		bench_world_record_apply_inner_baseline_env();
	},
	'probe_combo' => static function (): void {
		bench_world_record_apply_inner_focus_env();
	},
	'probe_recursive0' => static function (): void {
		bench_world_record_apply_inner_focus_env();
		putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_RECURSIVE_ONLY=0');
	},
	'probe_fzbm2048' => static function (): void {
		bench_world_record_apply_inner_focus_env();
		putenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES=2048');
	},
	'probe_zpaq4' => static function (): void {
		bench_world_record_apply_inner_focus_env();
		putenv('FRACTAL_ZIP_ZPAQ_OUTER_METHOD=4');
	},
	'probe_staged0' => static function (): void {
		bench_world_record_apply_inner_focus_env();
		putenv('FRACTAL_ZIP_STAGED_LITERAL_FAST_OUTER_MIN_RAW_BYTES=0');
	},
);

$rows = array();
foreach ($cases as $label => $apply) {
	bench_world_record_apply_inner_baseline_env();
	$apply();
	putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1');
	putenv('FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST=0');
	$work = $tmpRoot . DIRECTORY_SEPARATOR . $label;
	if (is_dir($work)) {
		$fz = new fractal_zip();
		$fz->recursive_remove_directory($work);
	}
	if (!@mkdir($work, 0700, true)) {
		exit(1);
	}
	foreach (glob($tmpRoot . DIRECTORY_SEPARATOR . 'slice_*.xml') ?: array() as $srcFile) {
		$base = basename($srcFile);
		if (!@copy($srcFile, $work . DIRECTORY_SEPARATOR . $base)) {
			exit(1);
		}
	}
	$fzcPath = $work . '.fz';
	@unlink($fzcPath);
	$t0 = microtime(true);
	$fz = new fractal_zip();
	$fz->zip_folder($work, false);
	$sec = microtime(true) - $t0;
	$fzcBytes = is_file($fzcPath) ? (int) filesize($fzcPath) : 0;
	$rows[] = array(
		'label' => $label,
		'raw_bytes' => $totalRaw,
		'fzc_bytes' => $fzcBytes,
		'zip_seconds' => round($sec, 3),
		'folder_unified_stream' => fractal_zip::$used_folder_unified_stream,
		'outer_codec' => fractal_zip::$last_outer_codec ?? null,
		'env_snapshot' => bench_world_record_inner_env_snapshot(),
	);
	@unlink($fzcPath);
}

$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_inner_unified_probe.json';
file_put_contents($outPath, json_encode(array(
	'generated' => date('c'),
	'member_count' => $memberCount,
	'bytes_per_member' => $bytesPerMember,
	'cases' => $rows,
), JSON_PRETTY_PRINT));

echo "enwik8 inner unified probe ({$totalRaw} B raw, {$memberCount} members)\n";
$best = null;
foreach ($rows as $r) {
	echo '  ' . $r['label'] . ': ' . number_format((int) $r['fzc_bytes']) . ' B in ' . $r['zip_seconds'] . "s unified=" . ($r['folder_unified_stream'] ? '1' : '0') . "\n";
	if ($best === null || (int) $r['fzc_bytes'] < $best) {
		$best = (int) $r['fzc_bytes'];
	}
}
foreach ($rows as $r) {
	$fzc = (int) $r['fzc_bytes'];
	if ($fzc <= 0 || $best === null) {
		continue;
	}
	$d = $fzc - $best;
	if ($d !== 0) {
		echo '  vs best: ' . $r['label'] . ' +' . number_format($d) . " B\n";
	}
}
echo "  wrote {$outPath}\n";

$fz = new fractal_zip();
$fz->recursive_remove_directory($tmpRoot);
