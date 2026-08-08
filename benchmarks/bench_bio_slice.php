#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Bioinformatics layout slice harness: 8 -> 32 -> 96 page slices of enwik8.
 *
 *  - RT-verifies every bio layout (apply -> undo == original pages).
 *  - Compares bio arms vs mono / mi_reorder / multishift under the same codecs.
 *  - LTCB rule: payload + external sidecar (align sidecar, pangenome graph) counted.
 *
 * Usage: php benchmarks/bench_bio_slice.php [slices=8,32,96] [bytes=12000000]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_bio_align.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';

$slicesArg = $argv[1] ?? '8,32,96';
$slices = array_values(array_filter(array_map('intval', explode(',', $slicesArg)), static fn (int $v): bool => $v > 0));
$readBytes = isset($argv[2]) ? max(1_000_000, (int) $argv[2]) : 12_000_000;

$enwikPath = $repo . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($enwikPath)) {
	fwrite(STDERR, "enwik8 not found at {$enwikPath}\n");
	exit(1);
}

function bio_tool_exists(string $bin): bool
{
	$out = array();
	$rc = 0;
	@exec('command -v ' . escapeshellarg($bin) . ' 2>/dev/null', $out, $rc);
	return $rc === 0 && $out !== array();
}

function bio_pipe_size(string $cmd, string $input): ?int
{
	$descriptors = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
	$proc = @proc_open($cmd, $descriptors, $pipes);
	if (!is_resource($proc)) {
		return null;
	}
	fwrite($pipes[0], $input);
	fclose($pipes[0]);
	$out = stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	stream_get_contents($pipes[2]);
	fclose($pipes[2]);
	proc_close($proc);
	return is_string($out) ? strlen($out) : null;
}

/** External sidecar bytes required for lossless restore (not in compressible payload). */
function bio_layout_sidecar_bytes(string $layoutId, array $meta): int
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

$haveZstd = bio_tool_exists('zstd');
$haveXz = bio_tool_exists('xz');

/** @var array<string, callable(string):int> $codecs */
$codecs = array();
$codecs['gz9'] = static function (string $s): int {
	$z = gzdeflate($s, 9);
	return $z === false ? strlen($s) : strlen($z);
};
if ($haveZstd) {
	$codecs['zstd19'] = static function (string $s): int {
		$n = bio_pipe_size('zstd -19 -q -c', $s);
		return $n ?? strlen($s);
	};
	$codecs['zstd_w15'] = static function (string $s): int {
		$n = bio_pipe_size('zstd -19 -q -c --zstd=wlog=15', $s);
		return $n ?? strlen($s);
	};
}
if ($haveXz) {
	$codecs['xz9'] = static function (string $s): int {
		$n = bio_pipe_size('xz -9 -e -c -T1', $s);
		return $n ?? strlen($s);
	};
}

$blob = (string) file_get_contents($enwikPath, false, null, 0, $readBytes);

printf("bio slice harness | codecs: %s\n", implode(',', array_keys($codecs)));
printf("read %s bytes of enwik8\n\n", number_format(strlen($blob)));

$layoutBaseline = array('mono_concat', 'mi_reorder', 'multishift');
$layoutBio = fractal_zip_bio_layout_catalog();

foreach ($slices as $pageCount) {
	$split = fractal_zip_enwik_split_shell_and_text($blob, $pageCount);
	if ($split === null || count($split['pages']) < $pageCount) {
		printf("[slice %d] not enough pages in window; skipping\n", $pageCount);
		continue;
	}
	$pages = $split['pages'];
	$rawTextBytes = 0;
	foreach ($pages as $pg) {
		$rawTextBytes += strlen((string) $pg['text']);
	}

	printf("=== slice %d pages (%s text bytes) ===\n", $pageCount, number_format($rawTextBytes));

	$candidates = array();
	foreach (array_merge($layoutBaseline, $layoutBio) as $layoutId) {
		try {
			$layout = fractal_zip_enwik_text_layout_apply($pages, $layoutId, array('seed' => 1));
		} catch (Throwable $e) {
			printf("  %-24s APPLY FAIL — %s\n", $layoutId, $e->getMessage());
			continue;
		}
		$undo = fractal_zip_enwik_text_layout_undo_chunks((string) $layout['text_blob'], $layout['meta'], $pages);
		$ok = true;
		foreach ($pages as $i => $pg) {
			if (($undo[$i] ?? null) !== (string) $pg['text']) {
				$ok = false;
				break;
			}
		}
		if (!$ok) {
			printf("  %-24s RT FAIL — excluded\n", $layoutId);
			continue;
		}
		$sidecar = 0;
		if ($layoutId === 'multishift') {
			$sidecar = strlen((string) base64_decode((string) ($layout['meta']['fzms_b64'] ?? ''), true));
		} elseif (str_starts_with($layoutId, 'bio_')) {
			$sidecar = bio_layout_sidecar_bytes($layoutId, $layout['meta']);
		}
		$candidates[$layoutId] = array('blob' => (string) $layout['text_blob'], 'sidecar' => $sidecar);
	}

	foreach ($codecs as $codecName => $fn) {
		if (!isset($candidates['mono_concat'])) {
			printf("  [%s] mono baseline missing — skip\n", $codecName);
			continue;
		}
		$baseBytes = $fn($candidates['mono_concat']['blob']);
		printf("  [%s] baseline(mono)=%d B\n", $codecName, $baseBytes);
		$rows = array();
		foreach ($candidates as $name => $cand) {
			$payload = $fn((string) $cand['blob']);
			$sidecar = (int) $cand['sidecar'];
			$ledgerNet = $baseBytes - $payload;
			$ltcbTotal = $payload + $sidecar;
			$ltcbNet = $baseBytes - $ltcbTotal;
			$delta = $ltcbTotal - $baseBytes;
			$rows[$name] = array(
				'payload' => $payload,
				'sidecar' => $sidecar,
				'ledger_net' => $ledgerNet,
				'ltcb_total' => $ltcbTotal,
				'ltcb_net' => $ltcbNet,
				'delta' => $delta,
			);
		}
		foreach ($rows as $name => $r) {
			$perPage = $pageCount > 0 ? $r['delta'] / $pageCount : 0.0;
			printf(
				"    %-24s payload=%7d  sidecar=%6d  ledgerNet=%+7d  LTCBtotal=%7d  Δ=%+7d  (%.2f B/page)\n",
				$name,
				$r['payload'],
				$r['sidecar'],
				$r['ledger_net'],
				$r['ltcb_total'],
				$r['delta'],
				$perPage
			);
		}

		$bestBio = null;
		foreach ($rows as $name => $r) {
			if (!str_starts_with($name, 'bio_')) {
				continue;
			}
			if ($bestBio === null || $r['delta'] < $rows[$bestBio]['delta']) {
				$bestBio = $name;
			}
		}
		if ($bestBio !== null) {
			$perPage = $pageCount > 0 ? $rows[$bestBio]['delta'] / $pageCount : 0.0;
			$fullPages = 12_000;
			$proj = $perPage * $fullPages;
			printf(
				"    -> best bio arm under LTCB: %s  Δ/page=%+.2f B  [projection x%d pages ~ %+d B] %s\n",
				$bestBio,
				$perPage,
				$fullPages,
				(int) round($proj),
				$rows[$bestBio]['delta'] < 0 ? 'GATE PASS' : 'GATE FAIL'
			);
		}
	}
	echo "\n";
}

fwrite(STDERR, "OK bench_bio_slice\n");
