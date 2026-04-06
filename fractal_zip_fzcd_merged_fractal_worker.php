#!/usr/bin/env php
<?php
/**
 * Isolated child process: fractal-encode one PCM chunk for FZCD merged-fractal-chunked mode.
 * Avoids OOM killing the parent zip_folder. Exit 0 = wrote inner FZC* blob to $argv[2].
 *
 * Usage: php fractal_zip_fzcd_merged_fractal_worker.php <pcm_chunk_file> <out_inner_file> <segment_length> <multipass_0_or_1> [improvement_float]
 */
declare(strict_types=1);

if ($argc < 5) {
	fwrite(STDERR, "usage: php fractal_zip_fzcd_merged_fractal_worker.php <pcm_in> <inner_out> <segment> <0|1> [improvement]\n");
	exit(2);
}

$pcmIn = $argv[1];
$innerOut = $argv[2];
$segment = max(8, min(500000, (int) $argv[3]));
$multipass = $argv[4] === '1';
$improvement = ($argc >= 6 && is_numeric($argv[5])) ? max(0.25, min(50.0, (float) $argv[5])) : null;

if (!is_file($pcmIn)) {
	exit(3);
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$pcm = file_get_contents($pcmIn);
if ($pcm === false || $pcm === '') {
	exit(4);
}

$prevPass = getenv('FRACTAL_ZIP_FZCD_MERGED_ZIP_PASS');
putenv('FRACTAL_ZIP_FZCD_MERGED_ZIP_PASS=1');
ob_start();
try {
	$sub = new fractal_zip($segment, $multipass, false, null, false);
	if ($improvement !== null) {
		$sub->improvement_factor_threshold = $improvement;
	}
	$topK = getenv('FRACTAL_ZIP_FZCD_WORKER_TOP_K');
	if ($topK !== false && trim((string) $topK) !== '' && is_numeric($topK)) {
		$sub->tuning_substring_top_k = max(1, min(12, (int) $topK));
	}
	$gate = getenv('FRACTAL_ZIP_FZCD_WORKER_GATE_MULT');
	if ($gate !== false && trim((string) $gate) !== '' && is_numeric($gate)) {
		$sub->tuning_multipass_gate_mult = max(1.0, min(4.0, (float) $gate));
	}
	$sub->zip($pcm, FRACTAL_ZIP_FZCD_MERGED_PCM_MEMBER, false);
	$arr = array();
	foreach ($sub->equivalences as $eq) {
		$arr[$eq[1]] = $eq[2];
	}
	$inner = $sub->encode_container_payload($arr, $sub->fractal_string);
	if (!is_string($inner) || $inner === '') {
		exit(5);
	}
	if (file_put_contents($innerOut, $inner) === false) {
		exit(6);
	}
	exit(0);
} catch (Throwable $e) {
	fwrite(STDERR, $e->getMessage() . "\n");
	exit(7);
} finally {
	if ($prevPass === false) {
		putenv('FRACTAL_ZIP_FZCD_MERGED_ZIP_PASS');
	} else {
		putenv('FRACTAL_ZIP_FZCD_MERGED_ZIP_PASS=' . $prevPass);
	}
	if (ob_get_level() > 0) {
		ob_end_clean();
	}
}
