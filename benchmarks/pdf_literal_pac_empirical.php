<?php
declare(strict_types=1);
/**
 * Empirical PDF literal-PAC: sequential handler chain (same as product multipass) per .pdf, with
 * optional per-step byte deltas, JSON, limits, and multi-mode batch (separate processes).
 *
 *   php pdf_literal_pac_empirical.php [path] [options]
 *   php pdf_literal_pac_empirical.php test_files72_sample --limit=3
 *   php pdf_literal_pac_empirical.php --per-step
 *   php pdf_literal_pac_empirical.php --json
 *   php pdf_literal_pac_empirical.php --all-modes   # default|with_qpdf|qpdf_first, separate PHP children
 *   php pdf_literal_pac_empirical.php --help
 *
 * Modes: --mode=default|with_qpdf|qpdf_first (or FRACTAL_ZIP_PDF_LITERALPAC_EMP_MODE; argv wins).
 * Default path: test_files72_sample with PDFs, else test_files71, else CWD.
 */

$base = dirname(__DIR__);
require_once $base . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
$rawArgv = array_slice($argv, 1);

$path = null;
$optMode = null;
$optLimit = null;
$optJson = false;
$optPerStep = false;
$optAllModes = false;
$optQuiet = false;
$optRecursive = false;
foreach ($rawArgv as $a) {
	if ($a === '--json') {
		$optJson = true;
	} elseif ($a === '--per-step') {
		$optPerStep = true;
	} elseif ($a === '--all-modes') {
		$optAllModes = true;
	} elseif ($a === '--quiet' || $a === '-q') {
		$optQuiet = true;
	} elseif ($a === '--recursive' || $a === '-r') {
		$optRecursive = true;
	} elseif ($a === '--help' || $a === '-h') {
		$e = <<<'TXT'
Usage: pdf_literal_pac_empirical.php [path] [--mode=default|with_qpdf|qpdf_first] [--limit=N]
       [--per-step] [--json] [--quiet] [--all-modes] [--recursive]

  --per-step   Sum bytes saved at each chain step (flate / dct / qpdf) across files; weighted % vs total raw.
  --json       Machine-readable (stdout); errors to stderr.
  --all-modes  Run default, with_qpdf, qpdf_first in separate processes (avoids static env cache). Implies --json to children.
  --recursive  List .pdf under path recursively (order: sorted by full path).
  --quiet      With --json: no human lines on stdout except JSON.
TXT;
		fwrite(STDOUT, $e);
		exit(0);
	} elseif (preg_match('/^--mode=(\w+)$/', $a, $m) === 1) {
		$optMode = strtolower((string) $m[1]);
	} elseif (preg_match('/^--limit=(\d+)$/', $a, $m) === 1) {
		$optLimit = max(0, (int) $m[1]);
	} elseif ($a[0] !== '-' && $path === null) {
		$path = $a;
	} else {
		fwrite(STDERR, "Unknown or misplaced argument: {$a}\n");
		exit(2);
	}
}

if ($optAllModes) {
	$script = $base . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'pdf_literal_pac_empirical.php';
	$modes = array('default', 'with_qpdf', 'qpdf_first');
	$pathArg = $path ?? __pdf_literal_emp_default_dir($base);
	$extra = array();
	if ($optLimit !== null) {
		$extra[] = '--limit=' . (string) $optLimit;
	}
	if ($optPerStep) {
		$extra[] = '--per-step';
	}
	if ($optRecursive) {
		$extra[] = '--recursive';
	}
	$agg = array();
	$t0 = hrtime(true);
	foreach ($modes as $m) {
		$args = array_merge(
			array(PHP_BINARY, $script, (string) $pathArg, '--mode=' . $m, '--json', '--quiet'),
			$extra
		);
		$spec = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
		$pr = @proc_open($args, $spec, $pipes, $base, null, array('bypass_shell' => true));
		if (! is_resource($pr)) {
			fwrite(STDERR, "proc_open failed for mode={$m}\n");
			exit(1);
		}
		fclose($pipes[0]);
		$out = (string) stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		$err = (string) stream_get_contents($pipes[2]);
		fclose($pipes[2]);
		$code = proc_close($pr);
		if ($err !== '') {
			fwrite(STDERR, $err);
		}
		if ($code !== 0) {
			fwrite(STDERR, "child exit {$code} mode={$m}\n");
			exit(1);
		}
		$j = bench_json_decode_assoc_try($out, 'pdf_literal_pac_empirical child mode=' . $m);
		if ($j === null) {
			fwrite(STDERR, "Invalid JSON from child mode={$m}\n");
			exit(1);
		}
		$agg[$m] = $j;
	}
	$elapsed = (hrtime(true) - $t0) / 1e9;
	if (! $optQuiet) {
		echo "pdf_literal_pac_empirical --all-modes  path=" . (string) $pathArg . "  wall_s=" . number_format($elapsed, 2) . "\n\n";
	}
	foreach ($modes as $m) {
		$sum = $agg[$m]['summary'] ?? null;
		if (! is_array($sum)) {
			continue;
		}
		$pct = (float) ($sum['pct_weighted'] ?? 0);
		$bi = (int) ($sum['bytes_in'] ?? 0);
		$bo = (int) ($sum['bytes_out'] ?? 0);
		printf(
			"%-14s  weighted %% vs raw: %5.2f  (%d -> %d B)\n",
			(string) $m,
			$pct,
			$bi,
			$bo
		);
	}
	if ($optJson) {
		$js = bench_json_encode_try(
			array('all_modes' => $agg, 'wall_seconds' => $elapsed, 'path' => (string) $pathArg),
			true
		);
		if ($js === null) {
			fwrite(STDERR, '[bench] json_encode failed (pdf_literal_pac_empirical --json all_modes): ' . json_last_error_msg() . "\n");
			exit(2);
		}
		echo $js . "\n";
	}
	exit(0);
}

$envMode = getenv('FRACTAL_ZIP_PDF_LITERALPAC_EMP_MODE');
$mode = $optMode !== null
	? $optMode
	: ($envMode !== false && trim((string) $envMode) !== '' ? strtolower(trim((string) $envMode)) : 'default');
if (! in_array($mode, array('default', 'with_qpdf', 'qpdf_first'), true)) {
	fwrite(STDERR, "Unknown mode: {$mode} (use --mode= default|with_qpdf|qpdf_first)\n");
	exit(1);
}
if ($mode === 'default') {
	putenv('FRACTAL_ZIP_LITERALPAC_PDF_QPDF=0');
} else {
	putenv('FRACTAL_ZIP_LITERALPAC_PDF_QPDF=1');
}
require_once $base . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac.php';

$defaultT = __pdf_literal_emp_default_dir($base);
$target = $path !== null && $path !== '' ? $path : $defaultT;
$paths = __pdf_literal_emp_collect_pdfs($target, $optRecursive);
sort($paths, SORT_STRING);
if ($optLimit !== null && $optLimit > 0) {
	$paths = array_slice($paths, 0, $optLimit);
}
if ($paths === array()) {
	if (! $optJson) {
		fwrite(STDERR, "No .pdf found under: {$target}\n");
	} else {
		$js = bench_json_encode_try(
			array(
				'error'   => 'no_pdf',
				'path'    => $target,
				'summary' => null,
			),
			false
		);
		if ($js === null) {
			fwrite(STDERR, '[bench] json_encode failed (pdf_literal_pac_empirical --json no_pdf): ' . json_last_error_msg() . "\n");
			exit(2);
		}
		echo $js . "\n";
	}
	exit($path !== null && $path !== '' ? 1 : 0);
}
$stepsRegistry = array('pdf_native_flate', 'pdf_dct_jpeg', 'pdf_jbig2', 'pdf_jpx', 'pdf_ccitt', 'pdf_qpdf');
$stepsQpdfFirst = array('pdf_qpdf', 'pdf_native_flate', 'pdf_dct_jpeg', 'pdf_jbig2', 'pdf_jpx', 'pdf_ccitt');
$st = $mode === 'qpdf_first' ? $stepsQpdfFirst : $stepsRegistry;
$stepKeys = array(
	'pdf_native_flate' => 'pdf_native_flate_b',
	'pdf_dct_jpeg'     => 'pdf_dct_jpeg_b',
	'pdf_jbig2'        => 'pdf_jbig2_b',
	'pdf_jpx'          => 'pdf_jpx_b',
	'pdf_ccitt'        => 'pdf_ccitt_b',
	'pdf_qpdf'         => 'pdf_qpdf_b',
);
if (! $optQuiet) {
	$q = getenv('FRACTAL_ZIP_LITERALPAC_PDF_QPDF');
	$qdis = (is_string($q) && $q !== '' ? (string) $q : 'unset; qpdf no-op unless=1');
	fwrite(
		$optJson ? STDERR : STDOUT,
		"pdf_literal_pac_empirical  mode={$mode}  n=" . count($paths) . "  path={$target}\nFRACTAL_ZIP_LITERALPAC_PDF_QPDF: {$qdis}\n"
		. ( $optPerStep ? "per_step=bytes saved per chain hop (repeated modes sum across files)\n" : "" ) . "\n"
	);
}
$rows = array();
$sumIn = 0;
$sumOut = 0;
$stepTotals = array(
	'pdf_native_flate_b' => 0,
	'pdf_dct_jpeg_b'     => 0,
	'pdf_jbig2_b'        => 0,
	'pdf_jpx_b'          => 0,
	'pdf_ccitt_b'        => 0,
	'pdf_qpdf_b'         => 0,
);
$tTotal = hrtime(true);
foreach ($paths as $fp) {
	$raw = @file_get_contents($fp);
	if (! is_string($raw) || $raw === '') {
		$rows[] = array('path' => $fp, 'n0' => 0, 'n1' => 0, 'pct' => 0.0, 'err' => 'read', 'per_step' => null);
		continue;
	}
	$n0 = strlen($raw);
	$w = $raw;
	$ps = array();
	$t0 = hrtime(true);
	foreach ($st as $h) {
		$before = strlen($w);
		$r = fractal_zip_literal_pac_run_registry_handler($h, $w);
		if (is_array($r) && isset($r[0], $r[1]) && (int) $r[1] > 0) {
			$w = (string) $r[0];
		}
		$after = strlen($w);
		$saved = $before - $after;
		if (isset($stepKeys[ $h])) {
			$sk = $stepKeys[ $h];
			$stepTotals[ $sk ] += $saved;
		}
		$ps[ (string) $h ] = $saved;
	}
	$ms = (hrtime(true) - $t0) / 1e6;
	$n1 = strlen($w);
	$pct = $n0 > 0 ? 100.0 * ($n0 - $n1) / $n0 : 0.0;
	$rows[] = array(
		'path' => $fp,
		'n0'   => $n0,
		'n1'   => $n1,
		'pct'  => $pct,
		'err'  => null,
		'per_step' => $optPerStep ? $ps : null,
		'ms'   => $ms,
	);
	$sumIn += $n0;
	$sumOut += $n1;
	$label = $fp;
	if (strlen($label) > 64) {
		$label = '…' . substr($label, -60);
	}
	if (! $optJson) {
		if ($optPerStep) {
			printf(
				"%6.2f%%  %7d  fl %5d  dct %5d  jb %5d  jx %5d  cc %5d  qp %5d  %8.0fms  %s\n",
				$pct,
				$n0 - $n1,
				$ps['pdf_native_flate'] ?? 0,
				$ps['pdf_dct_jpeg'] ?? 0,
				$ps['pdf_jbig2'] ?? 0,
				$ps['pdf_jpx'] ?? 0,
				$ps['pdf_ccitt'] ?? 0,
				$ps['pdf_qpdf'] ?? 0,
				$ms,
				$label
			);
		} else {
			printf("%6.2f%%  %10d -> %10d  %8.0fms  %s\n", $pct, $n0, $n1, $ms, $label);
		}
	}
}
$wall = (hrtime(true) - $tTotal) / 1e9;

$ok = array_values(array_filter(
	$rows,
	static fn($r) => $r['err'] === null && $r['n0'] > 0
));
$count = count($ok);
$weightedPct = $sumIn > 0 ? 100.0 * ($sumIn - $sumOut) / $sumIn : 0.0;
$pcts = $count > 0 ? array_map(static fn($r) => (float) $r['pct'], $ok) : array();
if ($pcts !== array()) {
	sort($pcts, SORT_NUMERIC);
}
$med = $count > 0 ? $pcts[(int) floor(($count - 1) / 2)] : 0.0;
$mean = $count > 0 ? array_sum($pcts) / $count : 0.0;
$mx = $count > 0 ? $pcts[ $count - 1] : 0.0;
$mn = $count > 0 ? $pcts[0] : 0.0;
$ge10 = $count > 0 ? count(array_filter($pcts, static fn($p) => $p >= 9.99)) : 0;
$stPct = $sumIn > 0
	? array(
		'pdf_native_flate' => 100.0 * $stepTotals['pdf_native_flate_b'] / $sumIn,
		'pdf_dct_jpeg'     => 100.0 * $stepTotals['pdf_dct_jpeg_b'] / $sumIn,
		'pdf_jbig2'        => 100.0 * $stepTotals['pdf_jbig2_b'] / $sumIn,
		'pdf_jpx'          => 100.0 * $stepTotals['pdf_jpx_b'] / $sumIn,
		'pdf_ccitt'        => 100.0 * $stepTotals['pdf_ccitt_b'] / $sumIn,
		'pdf_qpdf'         => 100.0 * $stepTotals['pdf_qpdf_b'] / $sumIn,
	)
	: null;

$summary = array(
	'mode'         => $mode,
	'path'         => $target,
	'file_count'   => $count,
	'files_ok'     => $count,
	'bytes_in'     => $sumIn,
	'bytes_out'    => $sumOut,
	'bytes_saved'  => $sumIn - $sumOut,
	'pct_weighted' => $weightedPct,
	'per_file_pct' => array('min' => $mn, 'mean' => $mean, 'median' => $med, 'max' => $mx, 'ge_10' => $ge10),
	'per_step_b'   => $stepTotals,
	'per_step_pct' => $stPct,
	'wall_seconds' => $wall,
);
$outJ = array(
	'summary'     => $summary,
	'files'       => $rows,
	'qpdf'        => getenv('FRACTAL_ZIP_LITERALPAC_PDF_QPDF') ?: 'unset',
);
if ($count < 1 && $rows !== array()) {
	$outJ['note'] = 'all input files failed to read or were empty';
}

if (! $optJson) {
	if ($optPerStep) {
		echo str_repeat('-', 72) . "\n";
		printf(
			"per-step totals (B):  flate %7d  dct %7d  jb %7d  jx %7d  cc %7d  qpdf %6d  |  weighted ~%% of sum(raw):  fl %.2f  dct %.2f  jb %.2f  jx %.2f  cc %.2f  qp %.2f\n",
			$stepTotals['pdf_native_flate_b'],
			$stepTotals['pdf_dct_jpeg_b'],
			$stepTotals['pdf_jbig2_b'],
			$stepTotals['pdf_jpx_b'],
			$stepTotals['pdf_ccitt_b'],
			$stepTotals['pdf_qpdf_b'],
			$stPct['pdf_native_flate'] ?? 0.0,
			$stPct['pdf_dct_jpeg'] ?? 0.0,
			$stPct['pdf_jbig2'] ?? 0.0,
			$stPct['pdf_jpx'] ?? 0.0,
			$stPct['pdf_ccitt'] ?? 0.0,
			$stPct['pdf_qpdf'] ?? 0.0
		);
	}
	echo str_repeat('-', 72) . "\n";
	printf(
		"files=%d  weighted %% vs raw: %.2f  (in %d -> out %d)  |  per-file %%-min/mean/median/max: %.2f/%.2f/%.2f/%.2f  (n with ≥10%%: %d)\n",
		$count,
		$weightedPct,
		$sumIn,
		$sumOut,
		$mn,
		$mean,
		$med,
		$mx,
		$ge10
	);
	printf("wall: %.2fs  (end-to-end literal PAC pass)\n", $wall);
	if ($ge10 < 1) {
		echo "\n10%: no file reached 10% per-file; weighted total above is the headline.\n";
	}
}
if ($optJson) {
	$js = bench_json_encode_try($outJ, false);
	if ($js === null) {
		fwrite(STDERR, '[bench] json_encode failed (pdf_literal_pac_empirical --json): ' . json_last_error_msg() . "\n");
		exit(2);
	}
	echo $js . "\n";
}
exit(0);

/**
 * @return list<string> absolute or as-given paths to .pdf
 */
function __pdf_literal_emp_collect_pdfs(string $target, bool $recursive): array {
	if (is_file($target) && str_ends_with(strtolower($target), '.pdf')) {
		return array($target);
	}
	if (! is_dir($target)) {
		return array();
	}
	if (! $recursive) {
		$g = glob($target . '/*.pdf') ?: array();
		sort($g, SORT_STRING);
		return $g;
	}
	$out = array();
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS)
	);
	$prefix = rtrim(realpath($target) ?: $target, DIRECTORY_SEPARATOR);
	foreach ($it as $f) {
		/** @var SplFileInfo $f */
		if (! $f->isFile()) {
			continue;
		}
		$bn = $f->getBasename();
		if (! str_ends_with(strtolower($bn), '.pdf')) {
			continue;
		}
		$out[] = $f->getPathname();
	}
	sort($out, SORT_STRING);
	return $out;
}

function __pdf_literal_emp_default_dir(string $base): string {
	$order = array('test_files72_sample_micro', 'test_files72_sample');
	foreach ($order as $name) {
		$p = $base . '/' . $name;
		$g = is_dir($p) ? (glob($p . '/*.pdf') ?: array()) : array();
		if ($g !== array()) {
			return $p;
		}
	}
	$first71  = (glob($base . '/test_files71/*.pdf') ?? [])[0] ?? null;
	$fallback = is_string($first71) ? dirname($first71) : ($base . '/test_files71');
	return $fallback;
}
