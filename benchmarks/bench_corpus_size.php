<?php
declare(strict_types=1);

/**
 * Shared helpers for `--maximum-size` corpus filtering (run_benchmarks.php, squash_benchmarks.php).
 */

/**
 * Parse --maximum-size=… into a positive byte cap.
 * Plain digits = bytes (≥1). Suffix forms: letter K/M/G/T/P (optionally followed by i for binary 1024 steps, optional B).
 * Without `i`: decimal SI (1000 per step), e.g. 2M = 2_000_000. With `i`: binary, e.g. 2Mi = 2×1024².
 *
 * @throws InvalidArgumentException
 */
function benchParseMaximumSizeBytes(string $raw): int
{
	$s = trim($raw);
	if ($s === '') {
		throw new InvalidArgumentException('empty');
	}
	if (preg_match('/^\d+$/', $s) === 1) {
		$n = (int) $s;
		if ($n < 1) {
			throw new InvalidArgumentException('bytes must be >= 1');
		}
		return $n;
	}
	if (preg_match('/^(\d+)\s*([kmgtp])(i)?[bB]?$/i', $s, $m) !== 1) {
		throw new InvalidArgumentException('expected digits or <int><K|M|G|T|P>[i][B]');
	}
	$n = (int) $m[1];
	if ($n < 1) {
		throw new InvalidArgumentException('mantissa must be >= 1');
	}
	$letter = strtolower($m[2]);
	$binary = isset($m[3]) && strtolower($m[3]) === 'i';
	$pos = strpos('kmgtp', $letter);
	if ($pos === false) {
		throw new InvalidArgumentException('bad unit');
	}
	$base = $binary ? 1024.0 : 1000.0;
	$mult = 1.0;
	for ($i = 0; $i <= $pos; $i++) {
		$mult *= $base;
	}
	$out = (int) round($n * $mult);
	if ($out < 1) {
		throw new InvalidArgumentException('result too small');
	}
	return $out;
}

function benchCorpusRawBytes(string $repoRoot, string $basename): int
{
	$dir = $repoRoot . DIRECTORY_SEPARATOR . $basename;
	if (!is_dir($dir)) {
		return 0;
	}
	$total = 0;
	try {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
		foreach ($it as $f) {
			/** @var SplFileInfo $f */
			if ($f->isFile()) {
				$total += (int) $f->getSize();
			}
		}
	} catch (Throwable $e) {
		return 0;
	}
	return $total;
}

/**
 * @param list<string> $names
 * @return list<string>
 */
function benchFilterCorporaByMaxRawBytes(string $repoRoot, array $names, int $maxRawBytes): array
{
	$out = [];
	foreach ($names as $n) {
		if (benchCorpusRawBytes($repoRoot, $n) <= $maxRawBytes) {
			$out[] = $n;
		}
	}
	return $out;
}
