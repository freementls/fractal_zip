#!/usr/bin/env php
<?php
/**
 * Build test_files62: many .gz members that share the same uncompressed payload but differ on-disk
 * (gzip level / headers) so fractal-path gzip peeling can dedupe the inner plaintext while a compact
 * peel trailer (FZG1–FZG4 — smallest encoding wins) preserves each file’s exact .gz bytes on extract.
 * FZG2 dedupes identical full blobs; FZG3 shares one gzip trailer when CRC/ISIZE match; FZG4 dedupes
 * gzip headers and deflate bodies separately (strong when many files share inner payload).
 *
 * Benchmarks: run_benchmarks sets FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES high for this corpus so zip_folder
 * uses the recursive fractal tree (under 256 files). Default peeling follows
 * FRACTAL_ZIP_LITERAL_EXPAND_GZIP_INNER (same switch as literal-bundle nested gzip).
 *
 * Size story: the winning container mode is still chosen among fractal/lazy/raw/bundle; opaque .gz
 * often wins via lazy + outer brotli. To stress fractal substring on gzip bytes, try
 * FRACTAL_ZIP_FORCE_LAZY_BINARY=0. Literal-bundle peeling wins are easier to see at 256+ files
 * (lower FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES) where encode_literal_bundle_payload runs choose_best.
 *
 * Env: TF62_FILE_COUNT (default 96, max 512).
 *
 * Usage: php benchmarks/build_test_files62.php
 */
declare(strict_types=1);

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$root = $repo . DIRECTORY_SEPARATOR . 'test_files62' . DIRECTORY_SEPARATOR . 'peel_gz_dupes';

$inner = str_repeat(
	"PEEL_BENCH shared payload line abcdefghijklmnopqrstuvwxyz 0123456789\n",
	220
);

if (!is_dir($root)) {
	mkdir($root, 0755, true);
}

$count = (int) (getenv('TF62_FILE_COUNT') ?: '96');
$count = max(8, min(512, $count));

for ($i = 0; $i < $count; $i++) {
	$level = ($i % 9) + 1;
	$blob = gzencode($inner, $level);
	$name = sprintf('%03d.gz', $i);
	$path = $root . DIRECTORY_SEPARATOR . $name;
	if (file_put_contents($path, $blob) === false) {
		fwrite(STDERR, "write failed: $path\n");
		exit(1);
	}
}

fwrite(STDOUT, "Wrote $count .gz files under peel_gz_dupes/ (same payload, levels 1–9 cycling).\n");
