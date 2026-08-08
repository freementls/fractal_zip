#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Bioinformatics niche probe: validate the regime where bio alignment layouts
 * should beat mono/mi_reorder — synthetic versioned documents under small-window
 * or position-sensitive consumers (mirrors bench_multishift_niche.php).
 *
 * Usage: php benchmarks/bench_bio_niche.php [scanPages=512] [bytes=16000000]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_bio_align.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';

$scanPages = isset($argv[1]) ? max(32, (int) $argv[1]) : 512;
$readBytes = isset($argv[2]) ? max(2_000_000, (int) $argv[2]) : 16_000_000;

$enwikPath = $repo . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($enwikPath)) {
	fwrite(STDERR, "enwik8 not found\n");
	exit(1);
}

function bn_pipe_size(string $cmd, string $input): ?int
{
	$d = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
	$p = @proc_open($cmd, $d, $pipes);
	if (!is_resource($p)) {
		return null;
	}
	fwrite($pipes[0], $input);
	fclose($pipes[0]);
	$o = stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	stream_get_contents($pipes[2]);
	fclose($pipes[2]);
	proc_close($p);
	return is_string($o) ? strlen($o) : null;
}

function bn_order0_bytes(string $s): int
{
	$len = strlen($s);
	if ($len === 0) {
		return 0;
	}
	$freq = count_chars($s, 1);
	$bits = 0.0;
	foreach ($freq as $c) {
		$p = $c / $len;
		$bits += -$c * log($p, 2);
	}
	return (int) ceil($bits / 8);
}

function bn_rle_order0_bytes(string $s): int
{
	$len = strlen($s);
	if ($len === 0) {
		return 0;
	}
	$out = '';
	$i = 0;
	while ($i < $len) {
		$ch = $s[$i];
		$run = 1;
		while ($i + $run < $len && $s[$i + $run] === $ch) {
			$run++;
		}
		$out .= chr($run) . $ch;
		$i += $run;
	}
	return bn_order0_bytes($out);
}

function bn_tool_exists(string $bin): bool
{
	$out = array();
	$rc = 0;
	@exec('command -v ' . escapeshellarg($bin) . ' 2>/dev/null', $out, $rc);
	return $rc === 0 && $out !== array();
}

/** @return array<string, callable(string):int> */
function bn_consumers(): array
{
	$out = array(
		'order0' => 'bn_order0_bytes',
		'rle_order0' => 'bn_rle_order0_bytes',
		'gz9' => static function (string $s): int {
			$z = gzdeflate($s, 9);
			return $z === false ? strlen($s) : strlen($z);
		},
	);
	if (bn_tool_exists('bzip2')) {
		$out['bzip2'] = static function (string $s): int {
			$n = bn_pipe_size('bzip2 -9 -c', $s);
			return $n ?? strlen($s);
		};
	}
	if (bn_tool_exists('zstd')) {
		$out['zstd_w10'] = static function (string $s): int {
			$n = bn_pipe_size('zstd -19 -q -c --zstd=wlog=10', $s);
			return $n ?? strlen($s);
		};
		$out['zstd_w27'] = static function (string $s): int {
			$n = bn_pipe_size('zstd -19 -q -c --zstd=wlog=27', $s);
			return $n ?? strlen($s);
		};
	}
	if (bn_tool_exists('xz')) {
		$out['xz9'] = static function (string $s): int {
			$n = bn_pipe_size('xz -9 -e -c -T1', $s);
			return $n ?? strlen($s);
		};
	}
	return $out;
}

function bn_bio_sidecar(string $layoutId, array $meta): int
{
	$layoutId = strtolower(trim($layoutId));
	if ($layoutId === 'bio_piecewise_shift' || $layoutId === 'bio_minhash_piecewise') {
		$raw = base64_decode((string) ($meta['sidecar_b64'] ?? ''), true);
		return is_string($raw) ? strlen($raw) : 0;
	}
	if ($layoutId === 'bio_pangenome') {
		$raw = base64_decode((string) ($meta['graph_b64'] ?? ''), true);
		return is_string($raw) ? strlen($raw) : 0;
	}
	return 0;
}

function bn_verify_rt(array $pages, string $layoutId, array $layout): bool
{
	try {
		$undo = fractal_zip_enwik_text_layout_undo_chunks((string) $layout['text_blob'], $layout['meta'], $pages);
	} catch (Throwable $e) {
		fwrite(STDERR, "RT FAIL {$layoutId}: {$e->getMessage()}\n");
		return false;
	}
	foreach ($pages as $i => $pg) {
		if (($undo[$i] ?? null) !== (string) $pg['text']) {
			fwrite(STDERR, "RT FAIL {$layoutId} page {$i}\n");
			return false;
		}
	}
	return true;
}

/** @param list<array{text:string}> $pages */
function bn_build_candidates(array $pages): array
{
	$candidates = array();
	$mono = '';
	foreach ($pages as $pg) {
		$mono .= (string) $pg['text'];
	}
	$candidates['mono_concat'] = array('blob' => $mono, 'sidecar' => 0);

	$mi = fractal_zip_enwik_text_layout_apply($pages, 'mi_reorder', array('seed' => 1));
	if (bn_verify_rt($pages, 'mi_reorder', $mi)) {
		$candidates['mi_reorder'] = array('blob' => (string) $mi['text_blob'], 'sidecar' => 0);
	}

	$bioLayouts = array(
		'bio_minhash_reorder',
		'bio_piecewise_shift',
		'bio_minhash_piecewise',
		'bio_progressive',
		'bio_pangenome',
	);
	foreach ($bioLayouts as $layoutId) {
		try {
			$layout = fractal_zip_enwik_text_layout_apply($pages, $layoutId, array('seed' => 1));
		} catch (Throwable $e) {
			fwrite(STDERR, "skip {$layoutId}: {$e->getMessage()}\n");
			continue;
		}
		if (!bn_verify_rt($pages, $layoutId, $layout)) {
			continue;
		}
		$candidates[$layoutId] = array(
			'blob' => (string) $layout['text_blob'],
			'sidecar' => bn_bio_sidecar($layoutId, $layout['meta']),
		);
	}

	$msDelta = fractal_zip_multishift_build($pages, array('jaccard_min' => 0.05, 'max_group' => 128, 'window' => 256));
	$msUndo = fractal_zip_multishift_undo((string) $msDelta['blob'], (string) $msDelta['fzms']);
	$msOk = true;
	foreach ($pages as $i => $pg) {
		if (($msUndo[$i] ?? null) !== (string) $pg['text']) {
			$msOk = false;
			break;
		}
	}
	if ($msOk) {
		$candidates['multishift_delta'] = array(
			'blob' => (string) $msDelta['blob'],
			'sidecar' => strlen((string) $msDelta['fzms']),
		);
	}

	return $candidates;
}

/** @param list<array{text:string}> $pages */
function bn_score_regime(string $title, array $pages, array $consumers): void
{
	$candidates = bn_build_candidates($pages);
	if (!isset($candidates['mono_concat'])) {
		printf("=== %s ===\n(no mono baseline)\n\n", $title);
		return;
	}

	$rawBytes = strlen((string) $candidates['mono_concat']['blob']);
	printf("=== %s ===\n", $title);
	printf("%d pages, %s B mono payload, %d candidate arms\n\n", count($pages), number_format($rawBytes), count($candidates));

	foreach ($consumers as $cn => $fn) {
		$base = $fn((string) $candidates['mono_concat']['blob']);
		printf("[%s] baseline(mono)=%s B\n", $cn, number_format($base));
		$winner = null;
		$winnerDelta = PHP_INT_MAX;
		foreach ($candidates as $name => $cand) {
			$payload = $fn((string) $cand['blob']);
			$ltcb = $payload + (int) $cand['sidecar'];
			$net = $base - $ltcb;
			$delta = $ltcb - $base;
			printf(
				"   %-24s payload=%9s  +sidecar=%6d  LTCBtotal=%9s  Δ=%+8d%s\n",
				$name,
				number_format($payload),
				(int) $cand['sidecar'],
				number_format($ltcb),
				$delta,
				$name === 'mono_concat' ? '  (baseline)' : ''
			);
			if ($name !== 'mono_concat' && $delta < $winnerDelta) {
				$winnerDelta = $delta;
				$winner = $name;
			}
		}
		printf(
			"   => best non-baseline: %s (Δ %+d) %s\n\n",
			(string) $winner,
			$winnerDelta,
			$winnerDelta < 0 ? 'WIN' : 'no win'
		);
	}
}

$blob = (string) file_get_contents($enwikPath, false, null, 0, $readBytes);
$consumers = bn_consumers();
printf("bio niche probe | consumers: %s\n\n", implode(',', array_keys($consumers)));

// Synthetic versioned-document regime (canonical beyond-window niche).
$split = fractal_zip_enwik_split_shell_and_text($blob, $scanPages);
$allPages = $split['pages'] ?? array();
$baseArticle = '';
foreach ($allPages as $p) {
	$t = (string) $p['text'];
	if (strlen($t) >= 3000 && strlen($t) <= 8000) {
		$baseArticle = $t;
		break;
	}
}
if ($baseArticle === '') {
	fwrite(STDERR, "OK bench_bio_niche (no synthetic base article found)\n");
	exit(0);
}

mt_srand(1234567);
$variants = array();
$nVariants = 40;
for ($v = 0; $v < $nVariants; $v++) {
	$chars = str_split($baseArticle);
	$len = count($chars);
	$edits = (int) ($len * 0.02);
	for ($e = 0; $e < $edits; $e++) {
		$pos = mt_rand(0, max(0, $len - 1));
		$chars[$pos] = chr(mt_rand(97, 122));
	}
	if ($v % 3 === 0) {
		$ip = mt_rand(0, max(0, $len - 1));
		array_splice($chars, $ip, 0, str_split(str_repeat('Z', mt_rand(1, 12))));
	}
	$variants[] = array('text' => implode('', $chars));
}

bn_score_regime(
	sprintf(
		'synthetic versioned-document regime (base %s B x %d variants ~2%% edits)',
		number_format(strlen($baseArticle)),
		$nVariants
	),
	$variants,
	$consumers
);

fwrite(STDERR, "OK bench_bio_niche\n");
