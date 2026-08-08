#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Outer codec sweep for phda9_xml single-stream LSTM baseline (words_4096p dict).
 *
 * Pass 1 (--capture): honest wire zip_folder, dump pre-outer inner via FRACTAL_ZIP_DUMP_PRE_OUTER_INNER.
 * Pass 2 (--inner=PATH): tournament gzip/zstd/brotli/store on cached inner (seconds).
 *
 * Usage:
 *   php benchmarks/bench_enwik8_phda9_outer_sweep.php --pages=384 --capture
 *   php benchmarks/bench_enwik8_phda9_outer_sweep.php --inner=/tmp/phda9_inner.bin
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_WEB_REF=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

/**
 * @see bench_wire_probe_phda9_dict_path()
 */
function bench_phda9_outer_sweep_dict_path(string $repo): string
{
	$override = getenv('FRACTAL_ZIP_WIRE_PROBE_PHDA9_DICT');
	if ($override !== false && trim((string) $override) !== '') {
		$path = trim((string) $override);
		if (is_file($path)) {
			return $path;
		}
	}
	$words4096 = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
	if (is_file($words4096)) {
		return $words4096;
	}
	$dict = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
	if (!is_file($dict)) {
		$dict = $repo . '/benchmarks/.phda9_external_dict.txt';
	}
	return $dict;
}

$pageLimit = 384;
$innerPath = '';
$capture = false;
$outJson = $repo . '/benchmarks/.enwik8_phda9_outer_sweep_384p.json';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--inner=')) {
		$innerPath = substr($arg, 8);
	} elseif ($arg === '--capture') {
		$capture = true;
	} elseif (str_starts_with($arg, '--out-json=')) {
		$outJson = substr($arg, 11);
	}
}

/**
 * @return array{codec: string, bytes: int}
 */
function bench_phda9_outer_try(string $inner, string $label, callable $envFn): array
{
	putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');
	putenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY');
	putenv('FRACTAL_ZIP_FORCE_OUTER');
	putenv('FRACTAL_ZIP_SKIP_ZSTD');
	putenv('FRACTAL_ZIP_SKIP_BROTLI');
	putenv('FRACTAL_ZIP_SKIP_ZPAQ');
	putenv('FRACTAL_ZIP_SKIP_7Z');
	putenv('FRACTAL_ZIP_ALLOW_OUTER_EXPANSION');
	$envFn();
	$fz = new fractal_zip();
	$out = $fz->adaptive_compress($inner);
	$codec = is_string(fractal_zip::$last_outer_codec ?? null) ? (string) fractal_zip::$last_outer_codec : $label;
	return array('codec' => $codec, 'bytes' => strlen($out));
}

/**
 * @return list<array{label: string, codec: string, outer_bytes: int, wire_estimate: int}>
 */
function bench_phda9_outer_sweep_inner(string $inner): array
{
	$innerLen = strlen($inner);
	$rows = array();
	$try = static function (string $label, callable $envFn) use ($inner, $innerLen, &$rows): void {
		$r = bench_phda9_outer_try($inner, $label, $envFn);
		$rows[] = array(
			'label' => $label,
			'codec' => $r['codec'],
			'outer_bytes' => $r['bytes'],
			'wire_estimate' => $innerLen + $r['bytes'],
		);
	};
	$try('store', static function (): void {
		putenv('FRACTAL_ZIP_FORCE_OUTER=gzip');
		putenv('FRACTAL_ZIP_ALLOW_OUTER_EXPANSION=0');
		putenv('FRACTAL_ZIP_SKIP_ZSTD=1');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_SKIP_ZPAQ=1');
		putenv('FRACTAL_ZIP_SKIP_7Z=1');
	});
	$try('gzip_forced', static function (): void {
		putenv('FRACTAL_ZIP_FORCE_OUTER=gzip');
		putenv('FRACTAL_ZIP_SKIP_ZSTD=1');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_SKIP_ZPAQ=1');
		putenv('FRACTAL_ZIP_SKIP_7Z=1');
	});
	$try('zstd_only', static function (): void {
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_SKIP_ZPAQ=1');
		putenv('FRACTAL_ZIP_SKIP_7Z=1');
	});
	$try('brotli_allowed', static function (): void {
		putenv('FRACTAL_ZIP_SKIP_ZPAQ=1');
		putenv('FRACTAL_ZIP_SKIP_7Z=1');
	});
	$try('full_tournament', static function (): void {
		putenv('FRACTAL_ZIP_SKIP_ZPAQ=1');
		putenv('FRACTAL_ZIP_SKIP_7Z=1');
		putenv('FRACTAL_ZIP_SKIP_BROTLI');
		putenv('FRACTAL_ZIP_SKIP_ZSTD');
	});
	usort($rows, static fn (array $a, array $b): int => ($a['wire_estimate'] <=> $b['wire_estimate']));
	return $rows;
}

if ($capture) {
	bench_world_record_apply_pp96_core_env();
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . bench_phda9_outer_sweep_dict_path($repo));
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
	putenv('FRACTAL_ZIP_TEXT_INNER=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
	putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
	putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
	putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
	putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
	putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
	putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
	putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
	putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
	putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
	putenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=96');

	$src = $repo . '/test_files109/enwik8';
	$blob = (string) file_get_contents($src);
	$split = enwik_split_page_refs($blob);
	if ($split === null) {
		exit(1);
	}
	$n = min($pageLimit, count($split['pages']));
	$slice = (string) $split['header'];
	for ($i = 0; $i < $n; $i++) {
		$slice .= substr($blob, (int) $split['pages'][$i]['start'], (int) $split['pages'][$i]['len']);
	}
	$slice .= (string) $split['footer'];

	$tmp = sys_get_temp_dir() . '/fz_phda9_outer_' . getmypid();
	@mkdir($tmp, 0700, true);
	$work = $tmp . '/work';
	@mkdir($work, 0700, true);
	file_put_contents($work . '/enwik8', $slice);
	$dumpPath = $repo . '/benchmarks/.enwik8_phda9_pre_outer_' . $n . 'p.bin';
	@unlink($dumpPath);
	putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER=' . $dumpPath);

	$t0 = microtime(true);
	$fz = new fractal_zip();
	$fz->zip_folder($work, false);
	$elapsed = round(microtime(true) - $t0, 2);
	putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');

	$fzc = $work . '.fz';
	$wire = is_file($fzc) ? (int) filesize($fzc) : 0;
	$innerPath = $dumpPath;
	echo "capture @{$n}p inner=" . (is_file($dumpPath) ? filesize($dumpPath) : 0) . " wire={$wire} outer=" . (fractal_zip::$last_outer_codec ?? '?') . " sec={$elapsed}\n";
	@unlink($fzc);
	fractal_zip_enwik_recursive_remove($tmp);
}

if ($innerPath === '' || !is_file($innerPath)) {
	fwrite(STDERR, "missing inner blob (use --capture or --inner=PATH)\n");
	exit(1);
}

$inner = (string) file_get_contents($innerPath);
$rows = bench_phda9_outer_sweep_inner($inner);
$report = array(
	'generated' => date('c'),
	'pages' => $pageLimit,
	'inner_path' => $innerPath,
	'inner_bytes' => strlen($inner),
	'rows' => $rows,
	'best' => $rows[0] ?? null,
);
file_put_contents($outJson, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "phda9 outer sweep inner=" . number_format(strlen($inner)) . " B\n\n";
foreach ($rows as $r) {
	printf("  %-16s codec=%-8s outer=%s wire~=%s\n",
		$r['label'],
		$r['codec'],
		number_format((int) $r['outer_bytes']),
		number_format((int) $r['wire_estimate'])
	);
}
echo "\n→ {$outJson}\n";
