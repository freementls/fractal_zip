#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Content-tailored outer sweep on phda9 LSTM wire bytes (no full .fz encode).
 *
 * Usage:
 *   php benchmarks/bench_phda9_inner_outer_sweep.php [--pages=96] [--dict=PATH] [--out=JSON]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPCACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_process_guard.php';
fractal_zip_process_guard_register_cli();
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_phda9_english.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_member_shootout.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$pageLimit = 96;
$outJson = null;
$dictPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--dict=')) {
		$dictPath = substr($arg, 7);
	} elseif (str_starts_with($arg, '--out=')) {
		$outJson = substr($arg, 6);
	}
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}
if (!is_file($dictPath)) {
	fwrite(STDERR, "Missing dict {$dictPath}\n");
	exit(1);
}
putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dictPath);

$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pageLimit, count($split['pages']));
$pageXml = '';
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$pageXml .= substr($blob, (int) $p['start'], (int) $p['len']);
}

$t0 = microtime(true);
$cr = fractal_zip_enwik_phda9_english_compress($pageXml, array(
	'wire_wrap' => true,
	'timeout_sec' => 0,
));
if (empty($cr['roundtrip_ok']) || !is_string($cr['payload']) || $cr['payload'] === '') {
	fwrite(STDERR, 'phda9 compress failed: ' . ($cr['error'] ?? '?') . "\n");
	exit(1);
}
$inner = (string) $cr['payload'];
$phda9Sec = round(microtime(true) - $t0, 2);

$rows = array();
$rows[] = array(
	'stack_id' => 'none',
	'bytes' => strlen($inner),
	'pick' => 'raw',
	'delta_vs_raw' => 0,
	'roundtrip_ok' => true,
);

foreach (fractal_zip_text_stacked_outer_catalog() as $stackId) {
	if ($stackId === 'none') {
		continue;
	}
	try {
		$r = fractal_zip_text_stacked_outer_apply($stackId, $inner);
		$payload = (string) ($r['payload'] ?? '');
		$rt = !empty($r['roundtrip_ok']);
		if (!$rt || $payload === '') {
			continue;
		}
		$rows[] = array(
			'stack_id' => $stackId,
			'bytes' => strlen($payload),
			'pick' => 'stack:' . $stackId,
			'delta_vs_raw' => strlen($payload) - strlen($inner),
			'roundtrip_ok' => true,
		);
	} catch (Throwable $e) {
		$rows[] = array(
			'stack_id' => $stackId,
			'bytes' => 0,
			'pick' => 'error',
			'delta_vs_raw' => 0,
			'roundtrip_ok' => false,
			'error' => $e->getMessage(),
		);
	}
}

putenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT=1');
putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_STACKS=' . implode(':', array_filter(
	fractal_zip_text_stacked_outer_catalog(),
	static fn(string $s): bool => $s !== 'none'
)));
$shoot = fractal_zip_enwik_member_shootout_pick($inner);
$rows[] = array(
	'stack_id' => 'shootout',
	'bytes' => (int) ($shoot['bytes'] ?? strlen($inner)),
	'pick' => (string) ($shoot['pick'] ?? 'raw'),
	'delta_vs_raw' => (int) ($shoot['bytes'] ?? strlen($inner)) - strlen($inner),
	'roundtrip_ok' => true,
	'candidates' => $shoot['candidates'] ?? array(),
);

usort($rows, static function (array $a, array $b): int {
	return ((int) ($a['bytes'] ?? PHP_INT_MAX)) <=> ((int) ($b['bytes'] ?? PHP_INT_MAX));
});

$report = array(
	'pages' => $n,
	'dict' => $dictPath,
	'phda9_raw_bytes' => strlen($inner),
	'phda9_plain_bytes' => strlen($pageXml),
	'phda9_seconds' => $phda9Sec,
	'tool' => (string) ($cr['tool'] ?? 'phda9'),
	'stacks' => $rows,
	'best' => $rows[0] ?? null,
);

$json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if (!is_string($json)) {
	exit(1);
}
if ($outJson !== null && $outJson !== '') {
	file_put_contents($outJson, $json);
}
echo $json . "\n";
