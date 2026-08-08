#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Offline tree compare used by run_benchmarks verify: relative path => SHA1 of file bytes.
 * Skips any path whose basename ends in `.fz` (same rule as collectFolderHashes in run_benchmarks.php).
 *
 * When a case runs verify, `php benchmarks/run_benchmarks.php --keep-verify-extract=/parent` copies the scratch tree to
 * `/parent/<label>/` (see run_benchmarks.php). Then:
 *   php benchmarks/sha1_tree_diff.php <left_dir> <right_dir> [--limit=N]
 *
 * Exit status: 0 iff trees match (same rel keys, same hashes); 1 on usage error; 2 when mismatches remain after --limit listing.
 */

/**
 * @return array<string, string> relative path (/) => sha1 hex
 */
function sha1_tree_diff_collect_hashes(string $dir): array
{
	$out = [];
	if (!is_dir($dir)) {
		return $out;
	}
	$real = realpath($dir);
	if ($real === false) {
		return $out;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $fileInfo) {
		if (!$fileInfo->isFile()) {
			continue;
		}
		$path = $fileInfo->getPathname();
		$rel = substr($path, strlen($real));
		if ($rel !== '' && ($rel[0] === '/' || $rel[0] === '\\')) {
			$rel = substr($rel, 1);
		}
		$rel = str_replace('\\', '/', $rel);
		if (str_ends_with(strtolower($rel), '.fz')) {
			continue;
		}
		$h = sha1_file($path);
		if ($h === false) {
			continue;
		}
		$out[$rel] = $h;
	}
	ksort($out);

	return $out;
}

$argvRest = array_slice($argv, 1);
$limit = 50;
$positional = [];
foreach ($argvRest as $a) {
	if (preg_match('/^--limit=(\d+)$/', $a, $m)) {
		$limit = max(1, min(5000, (int) $m[1]));
	} elseif ($a === '--help' || $a === '-h') {
		fwrite(STDOUT, "Usage: php benchmarks/sha1_tree_diff.php <left_dir> <right_dir> [--limit=N]\n");
		exit(0);
	} else {
		$positional[] = $a;
	}
}
if (count($positional) < 2) {
	fwrite(STDERR, "Usage: php benchmarks/sha1_tree_diff.php <left_dir> <right_dir> [--limit=N]\n");
	exit(1);
}
$left = $positional[0];
$right = $positional[1];
if (!is_dir($left) || !is_dir($right)) {
	fwrite(STDERR, "Both arguments must be existing directories.\n");
	exit(1);
}

$l = sha1_tree_diff_collect_hashes($left);
$r = sha1_tree_diff_collect_hashes($right);
$keys = array_fill_keys(array_merge(array_keys($l), array_keys($r)), true);
$mismatches = [];
foreach (array_keys($keys) as $k) {
	$a = $l[$k] ?? null;
	$b = $r[$k] ?? null;
	if ($a === $b) {
		continue;
	}
	if ($a === null) {
		$mismatches[] = ['k' => $k, 'kind' => 'only_right', 'left' => null, 'right' => $b];
	} elseif ($b === null) {
		$mismatches[] = ['k' => $k, 'kind' => 'only_left', 'left' => $a, 'right' => null];
	} else {
		$mismatches[] = ['k' => $k, 'kind' => 'hash_diff', 'left' => $a, 'right' => $b];
	}
}

$n = count($mismatches);
if ($n === 0) {
	fwrite(STDOUT, "OK: trees match (" . (string) count($l) . " files compared, .fz skipped).\n");
	exit(0);
}

fwrite(STDOUT, "MISMATCH: {$n} path(s) differ; showing up to {$limit}:\n");
$shown = 0;
foreach ($mismatches as $row) {
	if ($shown >= $limit) {
		break;
	}
	$k = $row['k'];
	$kind = $row['kind'];
	if ($kind === 'hash_diff') {
		fwrite(STDOUT, "{$kind}\t{$k}\n  left:  {$row['left']}\n  right: {$row['right']}\n");
	} else {
		fwrite(STDOUT, "{$kind}\t{$k}\n");
	}
	$shown++;
}
if ($n > $limit) {
	fwrite(STDOUT, "... (" . (string) ($n - $limit) . " more not shown; raise --limit=)\n");
}
exit(2);
