#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Multishift niche probe: validate the regime the plan predicts multishift can
 * actually win, namely position-sensitive / small-window / weak consumers where
 * delta-from-reference PRE-RESOLVES long-range cross-page matches that the codec
 * itself cannot reach inside its window.
 *
 *  - Mines the most-similar page cluster from a wide enwik8 window (the only
 *    regime where rigid alignment is meaningful).
 *  - Scores mono / mi_reorder / multishift(delta) / multishift_transpose under a
 *    ladder of consumers: order-0 entropy, RLE+order0, bzip2 (BWT), zstd with a
 *    tiny 1 KiB window (wlog=10), zstd with a large window, and xz.
 *  - Sidecar is counted (LTCB rule). The point is to locate the boundary, not to
 *    claim an enwik8 production win (large-window LZ/CM already dedup in-window).
 *
 * Usage: php benchmarks/bench_multishift_niche.php [scanPages=512] [bytes=16000000]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';

$scanPages = isset($argv[1]) ? max(32, (int) $argv[1]) : 512;
$readBytes = isset($argv[2]) ? max(2_000_000, (int) $argv[2]) : 16_000_000;

$enwikPath = $repo . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($enwikPath)) {
	fwrite(STDERR, "enwik8 not found\n");
	exit(1);
}

function msn_pipe_size(string $cmd, string $input): ?int
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

/** Order-0 Shannon entropy in bytes (position-insensitive lower bound). */
function msn_order0_bytes(string $s): int
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

/** RLE (run-length) then order-0 entropy: rewards long identical-byte runs. */
function msn_rle_order0_bytes(string $s): int
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
		while ($i + $run < $len && $s[$i + $run] === $ch && $run < 255) {
			$run++;
		}
		$out .= $ch . chr($run);
		$i += $run;
	}
	return msn_order0_bytes($out);
}

$blob = (string) file_get_contents($enwikPath, false, null, 0, $readBytes);
$split = fractal_zip_enwik_split_shell_and_text($blob, $scanPages);
if ($split === null) {
	fwrite(STDERR, "split failed\n");
	exit(1);
}
$allPages = array();
foreach ($split['pages'] as $pg) {
	$allPages[] = array('text' => (string) $pg['text']);
}

// Mine the most-similar cluster: run a tight-grouping build over the whole scan
// window, then pick the largest group whose members are non-trivial in size.
$built = fractal_zip_multishift_build($allPages, array('jaccard_min' => 0.35, 'max_group' => 24, 'window' => 256));
$bestGroup = array();
foreach ($built['records'] as $rec) {
	if ((int) $rec['kind'] === FRACTAL_ZIP_MULTISHIFT_REC_SINGLETON) {
		continue;
	}
	$idxs = array();
	$totLen = 0;
	foreach ($rec['members'] as $m) {
		$idxs[] = (int) $m['idx'];
		$totLen += (int) $m['len'];
	}
	if (count($idxs) > count($bestGroup) || (count($idxs) === count($bestGroup) && $totLen > 0)) {
		$bestGroup = $idxs;
	}
}

if (count($bestGroup) < 2) {
	fwrite(STDERR, "no similar cluster found in {$scanPages}-page scan; try more pages/bytes\n");
	exit(0);
}

$clusterPages = array();
$clusterBytes = 0;
$avgPageLen = 0;
foreach ($bestGroup as $idx) {
	$t = (string) $allPages[$idx]['text'];
	$clusterPages[] = array('text' => $t);
	$clusterBytes += strlen($t);
}
$avgPageLen = $clusterBytes / max(1, count($clusterPages));

printf("scanned %d pages; mined cluster of %d similar pages, %s bytes (avg page %d B)\n\n",
	count($allPages), count($clusterPages), number_format($clusterBytes), (int) $avgPageLen);

// Build the candidate blobs over the mined cluster.
$mono = '';
foreach ($clusterPages as $p) {
	$mono .= (string) $p['text'];
}
$miLayout = fractal_zip_enwik_text_layout_apply($clusterPages, 'mi_reorder', array('seed' => 1));
$miBlob = (string) $miLayout['text_blob'];

$delta = fractal_zip_multishift_build($clusterPages, array('jaccard_min' => 0.05, 'max_group' => 64, 'window' => 256));
$trans = fractal_zip_multishift_build($clusterPages, array('jaccard_min' => 0.05, 'max_group' => 64, 'window' => 256, 'force_transpose' => true));

// RT verify both multishift builds.
foreach (array('delta' => $delta, 'transpose' => $trans) as $nm => $b) {
	$r = fractal_zip_multishift_undo((string) $b['blob'], (string) $b['fzms']);
	foreach ($clusterPages as $i => $pg) {
		if (($r[$i] ?? null) !== (string) $pg['text']) {
			fwrite(STDERR, "RT FAIL {$nm} page {$i}\n");
			exit(1);
		}
	}
}

$candidates = array(
	'mono_concat'          => array('blob' => $mono, 'sidecar' => 0),
	'mi_reorder'           => array('blob' => $miBlob, 'sidecar' => 0),
	'multishift_delta'     => array('blob' => (string) $delta['blob'], 'sidecar' => strlen((string) $delta['fzms'])),
	'multishift_transpose' => array('blob' => (string) $trans['blob'], 'sidecar' => strlen((string) $trans['fzms'])),
);

$consumers = array(
	'order0'      => static fn (string $s): int => msn_order0_bytes($s),
	'rle_order0'  => static fn (string $s): int => msn_rle_order0_bytes($s),
	'bzip2'       => static fn (string $s): int => msn_pipe_size('bzip2 -9 -c', $s) ?? strlen($s),
	'zstd_w10'    => static fn (string $s): int => msn_pipe_size('zstd -19 -q -c --zstd=wlog=10', $s) ?? strlen($s),
	'zstd_w27'    => static fn (string $s): int => msn_pipe_size('zstd -19 -q -c --long=27', $s) ?? strlen($s),
	'xz9'         => static fn (string $s): int => msn_pipe_size('xz -9 -e -c -T1', $s) ?? strlen($s),
);

printf("delta blob=%s B (fzms %d) | transpose blob=%s B (fzms %d)\n\n",
	number_format(strlen((string) $delta['blob'])), strlen((string) $delta['fzms']),
	number_format(strlen((string) $trans['blob'])), strlen((string) $trans['fzms']));

foreach ($consumers as $cn => $fn) {
	$base = $fn($mono);
	printf("[%s] baseline(mono)=%s B\n", $cn, number_format($base));
	$winner = null;
	$winnerNet = PHP_INT_MIN;
	foreach ($candidates as $name => $cand) {
		$payload = $fn((string) $cand['blob']);
		$ltcb = $payload + (int) $cand['sidecar'];
		$net = $base - $ltcb;
		printf("   %-22s payload=%8s  +sidecar=%5d  LTCBtotal=%8s  net=%+7d%s\n",
			$name, number_format($payload), (int) $cand['sidecar'], number_format($ltcb), $net,
			$name === 'mono_concat' ? '  (baseline)' : '');
		if ($name !== 'mono_concat' && $net > $winnerNet) {
			$winnerNet = $net;
			$winner = $name;
		}
	}
	printf("   => best non-baseline: %s (net %+d) %s\n\n", (string) $winner, $winnerNet,
		$winnerNet > 0 ? 'WIN' : 'no win');
}

// ---------------------------------------------------------------------------
// Synthetic versioned-document regime: a real multi-KB enwik article plus N
// small-edit variants. This is the canonical multishift niche (revision
// histories / logs / enwik9-scale repetition where duplicates exceed the
// codec window). Framing overhead is negligible here, so the boundary is clean.
// ---------------------------------------------------------------------------
$baseArticle = '';
foreach ($allPages as $p) {
	$t = (string) $p['text'];
	if (strlen($t) >= 3000 && strlen($t) <= 8000) {
		$baseArticle = $t;
		break;
	}
}
if ($baseArticle === '') {
	fwrite(STDERR, "OK bench_multishift_niche (no synthetic base article found)\n");
	exit(0);
}

mt_srand(1234567); // deterministic
$variants = array();
$nVariants = 40;
for ($v = 0; $v < $nVariants; $v++) {
	$chars = str_split($baseArticle);
	$len = count($chars);
	$edits = (int) ($len * 0.02); // ~2% substitutions
	for ($e = 0; $e < $edits; $e++) {
		$pos = mt_rand(0, $len - 1);
		$chars[$pos] = chr(mt_rand(97, 122));
	}
	// Occasionally insert/delete a small run to exercise non-zero shifts.
	if ($v % 3 === 0) {
		$ip = mt_rand(0, $len - 1);
		array_splice($chars, $ip, 0, str_split(str_repeat('Z', mt_rand(1, 12))));
	}
	$variants[] = array('text' => implode('', $chars));
}

$synMono = '';
foreach ($variants as $p) {
	$synMono .= (string) $p['text'];
}
$synMi = (string) fractal_zip_enwik_text_layout_apply($variants, 'mi_reorder', array('seed' => 1))['text_blob'];
$synDelta = fractal_zip_multishift_build($variants, array('jaccard_min' => 0.05, 'max_group' => 128, 'window' => 256));
$synTrans = fractal_zip_multishift_build($variants, array('jaccard_min' => 0.05, 'max_group' => 128, 'window' => 256, 'force_transpose' => true));
foreach (array('delta' => $synDelta, 'transpose' => $synTrans) as $nm => $b) {
	$r = fractal_zip_multishift_undo((string) $b['blob'], (string) $b['fzms']);
	foreach ($variants as $i => $pg) {
		if (($r[$i] ?? null) !== (string) $pg['text']) {
			fwrite(STDERR, "synthetic RT FAIL {$nm} page {$i}\n");
			exit(1);
		}
	}
}

$synCandidates = array(
	'mono_concat'          => array('blob' => $synMono, 'sidecar' => 0),
	'mi_reorder'           => array('blob' => $synMi, 'sidecar' => 0),
	'multishift_delta'     => array('blob' => (string) $synDelta['blob'], 'sidecar' => strlen((string) $synDelta['fzms'])),
	'multishift_transpose' => array('blob' => (string) $synTrans['blob'], 'sidecar' => strlen((string) $synTrans['fzms'])),
);

printf("=== synthetic versioned-document regime ===\n");
printf("base article %s B x %d variants (~2%% edits) = %s B; delta blob=%s B (fzms %d)\n\n",
	number_format(strlen($baseArticle)), $nVariants, number_format(strlen($synMono)),
	number_format(strlen((string) $synDelta['blob'])), strlen((string) $synDelta['fzms']));

foreach ($consumers as $cn => $fn) {
	$base = $fn($synMono);
	printf("[%s] baseline(mono)=%s B\n", $cn, number_format($base));
	$winner = null;
	$winnerNet = PHP_INT_MIN;
	foreach ($synCandidates as $name => $cand) {
		$payload = $fn((string) $cand['blob']);
		$ltcb = $payload + (int) $cand['sidecar'];
		$net = $base - $ltcb;
		printf("   %-22s payload=%9s  +sidecar=%5d  LTCBtotal=%9s  net=%+8d%s\n",
			$name, number_format($payload), (int) $cand['sidecar'], number_format($ltcb), $net,
			$name === 'mono_concat' ? '  (baseline)' : '');
		if ($name !== 'mono_concat' && $net > $winnerNet) {
			$winnerNet = $net;
			$winner = $name;
		}
	}
	printf("   => best non-baseline: %s (net %+d) %s\n\n", (string) $winner, $winnerNet,
		$winnerNet > 0 ? 'WIN' : 'no win');
}

fwrite(STDERR, "OK bench_multishift_niche\n");
