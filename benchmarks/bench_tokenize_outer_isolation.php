#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * E3a isolation: does phda9-style dict tokenization help *generic* codecs on
 * prose, without pipeline wrapping? Compares raw vs tokenized bytes under
 * gzip9/zstd19/brotli11/xz9/zpaq5 on given text files.
 *
 * Usage: php benchmarks/bench_tokenize_outer_isolation.php file [file...]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_phda9_tokenize.php';
require_once $repo . '/fractal_zip_phda9_dict.php';

function iso_codecs(string $bytes): array {
	$out = array('raw' => strlen($bytes));
	$gz = gzdeflate($bytes, 9);
	$out['gzip9'] = is_string($gz) ? strlen($gz) : null;
	$tmp = tempnam(sys_get_temp_dir(), 'fziso');
	file_put_contents($tmp, $bytes);
	foreach (array(
		'zstd19' => "zstd -19 -q -c " . escapeshellarg($tmp) . " | wc -c",
		'brotli11' => "brotli -q 11 -c " . escapeshellarg($tmp) . " | wc -c",
		'xz9' => "xz -9e -q -c " . escapeshellarg($tmp) . " | wc -c",
	) as $codec => $cmd) {
		$r = trim((string) shell_exec($cmd . ' 2>/dev/null'));
		$out[$codec] = ($r !== '' && ctype_digit($r)) ? (int) $r : null;
	}
	$arch = $tmp . '.zpaq';
	@unlink($arch);
	$t0 = microtime(true);
	shell_exec('zpaq a ' . escapeshellarg($arch) . ' ' . escapeshellarg($tmp) . ' -m5 >/dev/null 2>&1');
	$out['zpaq5'] = is_file($arch) ? (int) filesize($arch) : null;
	$out['zpaq5_sec'] = round(microtime(true) - $t0, 2);
	@unlink($arch);
	@unlink($tmp);
	return $out;
}

$files = array();
$frozenDict = null;
foreach (array_slice($argv, 1) as $a) {
	if (str_starts_with($a, '--dict=')) {
		$frozenDict = substr($a, 7);
	} else {
		$files[] = $a;
	}
}
if ($files === array()) {
	$files = array($repo . '/test_files115/lcet10.txt');
}
$frozenVocab = null;
if ($frozenDict !== null) {
	$frozenVocab = fractal_zip_phda9_dict_read_words($frozenDict);
	echo "frozen dict: {$frozenDict} words=" . count($frozenVocab) . " (0 wire bytes — ships with fz)\n";
}

foreach ($files as $f) {
	if (!is_file($f)) {
		fwrite(STDERR, "missing {$f}\n");
		continue;
	}
	$raw = (string) file_get_contents($f);
	echo "== " . basename($f) . " (" . number_format(strlen($raw)) . " B) ==\n";

	$t0 = microtime(true);
	if ($frozenVocab !== null) {
		$vocab = $frozenVocab;
	} else {
		require_once $repo . '/fractal_zip_enwik_dict_phda9_inner.php';
		$vocab = fractal_zip_enwik_phda9_inner_frozen_vocab($raw);
	}
	$mineSec = round(microtime(true) - $t0, 2);
	if (!is_array($vocab) || $vocab === array()) {
		echo "  vocab mine failed, skipping\n";
		continue;
	}
	$t0 = microtime(true);
	$tok = fractal_zip_phda9_tokenize($raw, $vocab);
	$tokSec = round(microtime(true) - $t0, 2);
	if (!is_string($tok) || $tok === '') {
		echo "  tokenize failed, skipping\n";
		continue;
	}
	$rt = fractal_zip_phda9_detokenize($tok, $vocab);
	$rtOk = ($rt === $raw);
	$vocabBlob = ($frozenVocab !== null) ? '' : fractal_zip_phda9_dict_inline_vocab_blob($vocab);

	echo "  vocab_words=" . count($vocab) . " vocab_blob=" . number_format(strlen($vocabBlob))
		. " B mine_sec={$mineSec} tok_sec={$tokSec} rt=" . ($rtOk ? 'OK' : 'FAIL') . "\n";
	if (!$rtOk) {
		continue;
	}
	$a = iso_codecs($raw);
	$b = iso_codecs($tok);
	$v = iso_codecs($vocabBlob);
	printf("  %-9s %12s %12s %12s %12s\n", 'codec', 'raw', 'tokenized', 'tok+vocab', 'delta');
	foreach (array('gzip9', 'zstd19', 'brotli11', 'xz9', 'zpaq5') as $c) {
		if ($a[$c] === null || $b[$c] === null || $v[$c] === null) {
			continue;
		}
		$tv = $b[$c] + $v[$c];
		printf("  %-9s %12s %12s %12s %+12d\n", $c,
			number_format($a[$c]), number_format($b[$c]), number_format($tv), $tv - $a[$c]);
	}
	echo "  zpaq5_sec raw={$a['zpaq5_sec']} tok={$b['zpaq5_sec']}\n";
}
