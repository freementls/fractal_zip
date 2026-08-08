#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Multishift slice harness: 8 -> 32 -> 96 page slices of enwik8.
 *
 *  - RT-verifies every layout (apply -> undo == original pages).
 *  - Reports net bytes under our ledger (payload only) and the LTCB-total rule
 *    (payload + FZMS sidecar counted) against the mono-concat baseline.
 *  - A/B ordering: compares multishift vs plain perms (mono/sort_title/mi_reorder)
 *    through the SAME outer codec, and adds a window-limited codec to probe the
 *    beyond-window niche (enwik9-style) where co-locating duplicates can pay off.
 *  - Extrapolates per-page net savings to the full corpus (with caveats) before
 *    any large run is justified.
 *
 * Usage: php benchmarks/bench_multishift_slice.php [slices=8,32,96] [bytes=8000000]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';

$slicesArg = $argv[1] ?? '8,32,96';
$slices = array_values(array_filter(array_map('intval', explode(',', $slicesArg)), static fn (int $v): bool => $v > 0));
$readBytes = isset($argv[2]) ? max(1_000_000, (int) $argv[2]) : 12_000_000;

$enwikPath = $repo . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($enwikPath)) {
	fwrite(STDERR, "enwik8 not found at {$enwikPath}\n");
	exit(1);
}

function ms_tool_exists(string $bin): bool
{
	$out = array();
	$rc = 0;
	@exec('command -v ' . escapeshellarg($bin) . ' 2>/dev/null', $out, $rc);
	return $rc === 0 && $out !== array();
}

function ms_pipe_size(string $cmd, string $input): ?int
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

$haveZstd = ms_tool_exists('zstd');
$haveXz = ms_tool_exists('xz');

/** @var array<string, callable(string):int> $codecs */
$codecs = array();
$codecs['gz9'] = static function (string $s): int {
	$z = gzdeflate($s, 9);
	return $z === false ? strlen($s) : strlen($z);
};
if ($haveZstd) {
	$codecs['zstd19'] = static function (string $s): int {
		$n = ms_pipe_size('zstd -19 -q -c', $s);
		return $n ?? strlen($s);
	};
	// Window-limited (32 KiB) to emulate a small-window / beyond-window regime.
	$codecs['zstd_w15'] = static function (string $s): int {
		$n = ms_pipe_size('zstd -19 -q -c --zstd=wlog=15', $s);
		return $n ?? strlen($s);
	};
}
if ($haveXz) {
	$codecs['xz9'] = static function (string $s): int {
		$n = ms_pipe_size('xz -9 -e -c -T1', $s);
		return $n ?? strlen($s);
	};
}

$blob = (string) file_get_contents($enwikPath, false, null, 0, $readBytes);

printf("multishift slice harness | codecs: %s\n", implode(',', array_keys($codecs)));
printf("read %s bytes of enwik8\n\n", number_format(strlen($blob)));

$layoutPerm = array('mono_concat', 'sort_title', 'mi_reorder');
$layoutMs = array('multishift', 'multishift_transpose');

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

	// Build every candidate blob + RT verify.
	$candidates = array();
	foreach (array_merge($layoutPerm, $layoutMs) as $layoutId) {
		$layout = fractal_zip_enwik_text_layout_apply($pages, $layoutId, array('seed' => 1));
		$undo = fractal_zip_enwik_text_layout_undo_chunks((string) $layout['text_blob'], $layout['meta'], $pages);
		$ok = true;
		foreach ($pages as $i => $pg) {
			if (($undo[$i] ?? null) !== (string) $pg['text']) {
				$ok = false;
				break;
			}
		}
		if (!$ok) {
			printf("  %-22s RT FAIL — excluded\n", $layoutId);
			continue;
		}
		$sidecar = 0;
		if ($layoutId === 'multishift' || $layoutId === 'multishift_transpose') {
			$sidecar = strlen((string) base64_decode((string) ($layout['meta']['fzms_b64'] ?? ''), true));
		}
		$candidates[$layoutId] = array('blob' => (string) $layout['text_blob'], 'sidecar' => $sidecar);
	}

	// Beam build (few-similar vs many-loose) as its own candidate.
	$beam = fractal_zip_multishift_build_beam($pages, array());
	$beamUndo = fractal_zip_multishift_undo((string) $beam['blob'], (string) $beam['fzms']);
	$beamOk = true;
	foreach ($pages as $i => $pg) {
		if (($beamUndo[$i] ?? null) !== (string) $pg['text']) {
			$beamOk = false;
			break;
		}
	}
	if ($beamOk) {
		$candidates['multishift_beam'] = array('blob' => (string) $beam['blob'], 'sidecar' => strlen((string) $beam['fzms']));
		printf(
			"  beam: winner=#%d groups=%d transpose=%d fzms=%dB\n",
			$beam['stats']['beam_winner'] ?? -1,
			$beam['stats']['groups_formed'] ?? 0,
			$beam['stats']['transpose_groups'] ?? 0,
			strlen((string) $beam['fzms'])
		);
	} else {
		printf("  beam RT FAIL — excluded\n");
	}

	// Score under each codec; mono_concat is the baseline.
	foreach ($codecs as $codecName => $fn) {
		$baseBytes = $fn($candidates['mono_concat']['blob']);
		printf("  [%s] baseline(mono)=%d B\n", $codecName, $baseBytes);
		$rows = array();
		foreach ($candidates as $name => $cand) {
			$payload = $fn((string) $cand['blob']);
			$sidecar = (int) $cand['sidecar'];
			$ledgerNet = $baseBytes - $payload;            // payload-only ledger
			$ltcbTotal = $payload + $sidecar;              // LTCB rule: sidecar counts
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
				"    %-22s payload=%7d  sidecar=%6d  ledgerNet=%+7d  LTCBtotal=%7d  Δ=%+7d  (%.2f B/page)\n",
				$name,
				$r['payload'],
				$r['sidecar'],
				$r['ledger_net'],
				$r['ltcb_total'],
				$r['delta'],
				$perPage
			);
		}

		// Pick best multishift-family arm under LTCB rule and extrapolate.
		$bestMs = null;
		foreach ($rows as $name => $r) {
			if (!str_starts_with($name, 'multishift')) {
				continue;
			}
			if ($bestMs === null || $r['delta'] < $rows[$bestMs]['delta']) {
				$bestMs = $name;
			}
		}
		if ($bestMs !== null) {
			$perPage = $pageCount > 0 ? $rows[$bestMs]['delta'] / $pageCount : 0.0;
			$fullPages = 12_000; // rough enwik8 article-page magnitude; projection only
			$proj = $perPage * $fullPages;
			printf(
				"    -> best MS arm under LTCB: %s  Δ/page=%+.2f B  [projection x%d pages ~ %+d B] %s\n",
				$bestMs,
				$perPage,
				$fullPages,
				(int) round($proj),
				$rows[$bestMs]['delta'] < 0 ? 'GATE PASS' : 'GATE FAIL (sidecar >= savings)'
			);
		}
	}
	echo "\n";
}

fwrite(STDERR, "OK bench_multishift_slice\n");
