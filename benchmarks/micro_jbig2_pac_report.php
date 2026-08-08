<?php
declare(strict_types=1);
/**
 * Measure PDF byte impact of literal-PAC (incl. pdf_jbig2) on test_files72_sample_micro.
 *
 *   php benchmarks/micro_jbig2_pac_report.php              # fast: PDF bytes + per-handler savings only
 *   php benchmarks/micro_jbig2_pac_report.php --bench      # slow: PAC then run_benchmarks (PAC ~5+ min + bench)
 *   php benchmarks/micro_jbig2_pac_report.php --bench-only # run_benchmarks only (needs non-empty OUTPUT_DIR)
 *
 * Requires: jbig2dec + jbig2 (or jbig2enc) on PATH for JBIG2 wins. Writes benchmarks/micro_jbig2_win_result.txt
 * and creates test_files72_sample_micro_jbig2_pac/ (flat PDFs).
 */
$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$argv = $argv ?? array();
$benchOnly = in_array('--bench-only', $argv, true);
$doBench = in_array('--bench', $argv, true) || $benchOnly;
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files72_sample_micro';
$dst = $repo . DIRECTORY_SEPARATOR . 'test_files72_sample_micro_jbig2_pac';
$report = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'micro_jbig2_win_result.txt';

$lines = array();
$lines[] = 'micro_jbig2_pac_report  ' . gmdate('c');
$lines[] = 'tools: ' . trim((string) shell_exec('command -v jbig2dec jbig2 jbig2enc 2>/dev/null'));
$lines[] = '';

if (!is_dir($src)) {
	file_put_contents($report, "ERROR: missing {$src}\n");
	fwrite(STDERR, "missing {$src}\n");
	exit(1);
}

if ($benchOnly) {
	if (!is_dir($dst)) {
		file_put_contents($report, "ERROR: --bench-only needs OUTPUT_DIR {$dst}\n");
		fwrite(STDERR, "missing {$dst}; run without --bench-only first.\n");
		exit(1);
	}
	$have = glob($dst . '/*.pdf') ?: array();
	if ($have === array()) {
		file_put_contents($report, "ERROR: --bench-only needs PDFs in {$dst}\n");
		fwrite(STDERR, "empty {$dst}; run without --bench-only first.\n");
		exit(1);
	}
	$lines[] = 'PAC: skipped (--bench-only; using existing OUTPUT_DIR)';
	$lines[] = '';
} else {
	if (is_dir($dst)) {
		fractal_zip_micro_jb2_rrmdir($dst);
	}
	if (!@mkdir($dst, 0755, true)) {
		file_put_contents($report, "ERROR: mkdir {$dst}\n");
		exit(1);
	}

	$handlers = array(
		'pdf_native_flate',
		'pdf_dct_jpeg',
		'pdf_jbig2',
		'pdf_jpx',
		'pdf_ccitt',
	);
	$sumIn = 0;
	$sumOut = 0;
	$pdfs = glob($src . '/*.pdf') ?: array();
	sort($pdfs, SORT_STRING);
	foreach ($pdfs as $p) {
		$w = (string) file_get_contents($p);
		$inB = strlen($w);
		$sumIn += $inB;
		$saves = array();
		foreach ($handlers as $h) {
			$r = fractal_zip_literal_pac_run_registry_handler($h, $w);
			if (is_array($r) && isset($r[0], $r[1]) && (int) $r[1] > 0) {
				$saves[] = $h . ':' . (string) (int) $r[1];
				$w = (string) $r[0];
			}
		}
		$outB = strlen($w);
		$sumOut += $outB;
		$leaf = basename($p);
		@file_put_contents($dst . DIRECTORY_SEPARATOR . $leaf, $w);
		$lines[] = sprintf(
			'%s  in=%d out=%d pdf_saved=%d (%s)',
			$leaf,
			$inB,
			$outB,
			$inB - $outB,
			$saves === array() ? 'no PAC shrink' : implode(' ', $saves)
		);
	}
	$lines[] = '';
	$lines[] = sprintf(
		'TOTAL_PDF_BYTES  in=%d out=%d saved=%d (%.4f%%)',
		$sumIn,
		$sumOut,
		$sumIn - $sumOut,
		$sumIn > 0 ? 100.0 * ($sumIn - $sumOut) / $sumIn : 0.0
	);
	$lines[] = 'OUTPUT_DIR=' . $dst;
	$lines[] = '';
	if (!$doBench) {
		$lines[] = 'fzc: skipped (pass --bench for paired run_benchmarks; slow on micro).';
		$lines[] = '';
	}
}

if ($benchOnly) {
	$lines[] = 'OUTPUT_DIR=' . $dst;
	$lines[] = '';
}

$bench = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_benchmarks.php';
if ($doBench && is_file($bench)) {
	$t = 1700000000;
	foreach (array($src, $dst) as $d) {
		if (!is_dir($d)) {
			continue;
		}
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($d, FilesystemIterator::SKIP_DOTS)
		);
		foreach ($it as $f) {
			if ($f->isFile()) {
				@touch($f->getPathname(), $t, $t);
			}
		}
	}
	$json = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_micro_jb2_bench_' . bin2hex(random_bytes(4)) . '.json';
	$cmd = 'FRACTAL_ZIP_BENCH_NO_BASELINE_CACHE=1 php ' . escapeshellarg($bench)
		. ' --only=test_files72_sample_micro,test_files72_sample_micro_jbig2_pac'
		. ' --no-verify --no-best-ext --no-case-timeout --no-multipass --no-extract --json --out-json='
		. escapeshellarg($json);
	putenv('FRACTAL_ZIP_BENCH_NO_BASELINE_CACHE=1');
	$null = array();
	$ex = 1;
	chdir($repo);
	exec($cmd, $null, $ex);
	$lines[] = 'run_benchmarks exit=' . (string) $ex;
	if (is_file($json)) {
		$j = bench_json_decode_file_assoc_try($json, 'micro_jbig2_pac_report', 512, 0, false);
		if ($j !== null) {
			foreach (($j['cases'] ?? array()) as $c) {
				$lab = (string) ($c['label'] ?? '?');
				$fzc = (int) ($c['fzc_bytes'] ?? 0);
				$raw = (int) ($c['raw_bytes'] ?? 0);
				$lines[] = "BENCH {$lab} raw={$raw} fzc={$fzc}";
			}
		}
		@unlink($json);
	} else {
		$lines[] = 'BENCH json missing';
	}
}

file_put_contents($report, implode("\n", $lines) . "\n");
echo file_get_contents($report);

/**
 * @param non-empty-string $dir
 */
function fractal_zip_micro_jb2_rrmdir(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $f) {
		/** @var SplFileInfo $f */
		$p = $f->getPathname();
		if ($f->isDir()) {
			@rmdir($p);
		} else {
			@unlink($p);
		}
	}
	@rmdir($dir);
}
