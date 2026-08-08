#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Find minimum BIGRAM_MAX_PREV with verify OK (no reinject bloat study).
 *
 * Usage: php benchmarks/bench_stat_pred_max_prev_verify_sweep.php [--pages=32]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$pageLimit = 32;
$caps = array(4096, 5000, 6000, 7000, 8000, 9000, 10000, 11000, 12000, 0);
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(1, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--caps=')) {
		$caps = array();
		foreach (explode(',', substr($arg, 7)) as $p) {
			$p = trim($p);
			if ($p !== '' && ctype_digit($p)) {
				$caps[] = (int) $p;
			}
		}
	}
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pageLimit, count($split['pages']));
$rawBytes = 0;
for ($i = 0; $i < $n; $i++) {
	$rawBytes += (int) $split['pages'][$i]['len'];
}

echo "verify sweep pages={$n}\n";
foreach ($caps as $cap) {
	if ($cap > 0) {
		putenv('FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV=' . $cap);
	} else {
		putenv('FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV');
	}
	putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=base94');
	putenv('FRACTAL_ZIP_TEXT_INNER=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
	putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
	putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_pred_inner');
	putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');

	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_cap_sweep_' . getmypid() . '_' . $cap;
	@mkdir($tmp, 0700, true);
	$work = $tmp . DIRECTORY_SEPARATOR . 'work';
	@mkdir($work, 0700, true);
	$slice = $tmp . DIRECTORY_SEPARATOR . 'enwik8';
	file_put_contents($slice, substr($blob, 0, (int) $split['pages'][$n - 1]['start'] + (int) $split['pages'][$n - 1]['len']));
	copy($slice, $work . DIRECTORY_SEPARATOR . 'enwik8');
	$fzc = $tmp . DIRECTORY_SEPARATOR . 'out.fz';
	@unlink($fzc);

	$ok = false;
	$fzcBytes = 0;
	$err = '';
	try {
		$fz = new fractal_zip();
		$fz->zip_folder($work, false);
		if (!is_file($fzc)) {
			$fzc = $work . '.fz';
		}
		if (!is_file($fzc)) {
			throw new RuntimeException('no fzc');
		}
		$fzcBytes = (int) filesize($fzc);
		$extract = $tmp . DIRECTORY_SEPARATOR . 'extract';
		@mkdir($extract, 0700, true);
		$fz2 = new fractal_zip();
		$fz2->open_container($fzc, $extract);
		$restored = $extract . DIRECTORY_SEPARATOR . 'enwik8';
		$ok = is_file($restored) && filesize($restored) === filesize($slice);
	} catch (Throwable $e) {
		$err = $e->getMessage();
	}
	$stats = fractal_zip_enwik_text_inner_last_build_stats();
	$meta = (int) ($stats['meta_bytes'] ?? 0);
	$hits = (int) ($stats['bigram_hits'] ?? 0);
	$fullPages = 12041;
	$amort = $fzcBytes;
	if ($meta > 0) {
		$amort = (int) round($fzcBytes - $meta + ($meta * $n / $fullPages));
	}
	$capLabel = $cap > 0 ? (string) $cap : 'none';
	echo 'cap=' . $capLabel
		. ' verify=' . ($ok ? 'OK' : 'FAIL')
		. ' fzc=' . $fzcBytes
		. ' meta=' . $meta
		. ' amort=' . $amort
		. ' hits=' . $hits;
	if ($err !== '') {
		echo ' err=' . substr($err, 0, 60);
	}
	echo "\n";
	fractal_zip_enwik_recursive_remove($tmp);
}
