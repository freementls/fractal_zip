#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * zpaq squash on entry-sorted pre-outer inner + reversible preprocess variants (ceiling probe).
 *
 * Usage:
 *   php -d memory_limit=2048M benchmarks/bench_enwik8_zpaq_inner_preprocess_probe.php [--pages=384]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$pageLimit = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	}
}

if (fractal_zip::zpaq_executable() === null) {
	fwrite(STDERR, "zpaq not found\n");
	exit(1);
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
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

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_enwik_zpaq_pre_' . getmypid();
@mkdir($tmp, 0700, true);
$enwikPath = $tmp . DIRECTORY_SEPARATOR . 'enwik8';
file_put_contents($enwikPath, $slice);

bench_world_record_apply_pp96_core_env();
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=96');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');

$dumpPath = $tmp . DIRECTORY_SEPARATOR . 'inner.bin';
putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER=' . $dumpPath);
putenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY=1');
$work = $tmp . DIRECTORY_SEPARATOR . 'wire';
@mkdir($work, 0700, true);
copy($enwikPath, $work . DIRECTORY_SEPARATOR . 'enwik8');
$fz = new fractal_zip();
$fz->zip_folder($work, false);
putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');
putenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY');

$sortedInner = is_file($dumpPath) ? (string) file_get_contents($dumpPath) : '';
@unlink($dumpPath);
if ($sortedInner === '') {
	fwrite(STDERR, "Failed to dump pre-outer inner\n");
	exit(1);
}

$fzProbe = new fractal_zip(256, false, false, null, false);
$zpaqExe = (string) fractal_zip::zpaq_executable();
$methods = array(' -method 9', ' -method 5', ' -method 4');

$variants = array(
	'sorted_inner' => $sortedInner,
	'raw_slice' => $slice,
	'delta' => $fzProbe->delta_encode_bytes($sortedInner),
	'xor_adjacent' => $fzProbe->xor_adjacent_encode_bytes($sortedInner),
);

$lines = explode("\n", $sortedInner);
sort($lines, SORT_STRING);
$variants['sort_lines_alpha'] = implode("\n", $lines);

foreach (array(1 => 'delta', 2 => 'xor_adjacent') as $tid => $name) {
	$body = $tid === 1 ? $fzProbe->delta_encode_bytes($sortedInner) : $fzProbe->xor_adjacent_encode_bytes($sortedInner);
	$n0 = strlen($sortedInner);
	$variants['fzws_' . $name] = 'FZWS' . chr(1) . chr($tid) . pack('N', $n0) . $body;
}

$zpaqBytes = static function (fractal_zip $host, string $exe, string $payload, string $meth) use ($methods): ?int {
	$blob = $host->outer_zpaq_blob_with_meth_fragment($exe, $payload, $meth);
	return ($blob !== null && $blob !== '') ? strlen($blob) : null;
};

$rows = array();
foreach ($variants as $vid => $payload) {
	$best = null;
	$bestMeth = null;
	$byMeth = array();
	foreach ($methods as $meth) {
		$b = $zpaqBytes($fzProbe, $zpaqExe, $payload, $meth);
		$byMeth[trim($meth)] = $b;
		if ($b !== null && ($best === null || $b < $best)) {
			$best = $b;
			$bestMeth = trim($meth);
		}
	}
	$gz1 = @gzdeflate($payload, 1);
	$rows[] = array(
		'id' => $vid,
		'payload_bytes' => strlen($payload),
		'gzip1_bytes' => is_string($gz1) ? strlen($gz1) : null,
		'zpaq_best_bytes' => $best,
		'zpaq_best_method' => $bestMeth,
		'zpaq_by_method' => $byMeth,
	);
}

fractal_zip_enwik_recursive_remove($tmp);

usort($rows, static function (array $a, array $b): int {
	$za = $a['zpaq_best_bytes'] ?? PHP_INT_MAX;
	$zb = $b['zpaq_best_bytes'] ?? PHP_INT_MAX;
	return $za <=> $zb;
});

$out = array(
	'generated' => date('c'),
	'pages' => $n,
	'sorted_inner_bytes' => strlen($sortedInner),
	'raw_slice_bytes' => strlen($slice),
	'rows' => $rows,
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_zpaq_inner_preprocess_probe.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));

echo "zpaq inner preprocess probe ({$n} pages) → {$path}\n";
$base = null;
foreach ($rows as $r) {
	if ($r['id'] === 'sorted_inner') {
		$base = $r['zpaq_best_bytes'];
		break;
	}
}
foreach ($rows as $r) {
	$z = $r['zpaq_best_bytes'];
	echo '  ' . $r['id'] . '  payload=' . number_format((int) $r['payload_bytes']);
	if ($r['gzip1_bytes'] !== null) {
		echo '  gzip1=' . number_format((int) $r['gzip1_bytes']);
	}
	if ($z !== null) {
		echo '  zpaq=' . number_format($z) . ' (' . ($r['zpaq_best_method'] ?? '?') . ')';
		if ($base !== null && $z !== $base) {
			echo '  (' . ($z < $base ? '' : '+') . number_format($z - $base) . ' vs sorted_inner)';
		}
	}
	echo "\n";
}
