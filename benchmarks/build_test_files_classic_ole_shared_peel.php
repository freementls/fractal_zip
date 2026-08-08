#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_ole_shared_peel`: ≥4 near-identical OLE/CFB docs
 * (tiny unique WordDocument tail) so folder OLE near-dup CLASSIC `fp` peel
 * beats opaque unified FZb1.
 *
 *   php benchmarks/build_test_files_classic_ole_shared_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_ole_shared_peel';
$basePath = $repo . '/test_files72/converting files to pdf-20070424.doc';

if (!is_file($basePath)) {
	fwrite(STDERR, "missing base OLE: {$basePath}\n");
	exit(1);
}

if (!is_dir($dest)) {
	mkdir($dest, 0755, true);
}
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	@unlink($dest . DIRECTORY_SEPARATOR . $e);
}

require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_folder_logical_bundle.php';
require_once $repo . '/fractal_zip_classic_peel.php';
require_once $repo . '/fractal_zip_ole_cfb.php';

$base = file_get_contents($basePath);
$parsed = fractal_zip_ole_build_template_and_streams($base);
if ($parsed === null) {
	fwrite(STDERR, "OLE parse failed\n");
	exit(1);
}

// Shared prose overwrites WordDocument only; other streams stay identical across docs.
$shared = '';
for ($i = 0; $i < 60; $i++) {
	$shared .= "Shared OLE peel prose {$i}: tables notes summaries repeat across sibling docs.\n";
}
$shared = str_repeat($shared, 12);

// Four short-named siblings: near-dup `fp` peel beats opaque (3-file packs still lose ~20 B).
$tags = array('00', '01', '02', '03');
$classic = 0;
foreach ($tags as $tag) {
	$streams = [];
	foreach ($parsed['streams'] as $st) {
		$name = (string) $st['name'];
		$need = strlen((string) $st['data']);
		$data = (string) $st['data'];
		if (str_contains($name, 'WordDocument') || strcasecmp($name, 'WordDocument') === 0) {
			$fill = $shared;
			if (strlen($fill) < $need) {
				$fill = str_pad($fill, $need, "\0");
			}
			$data = substr($fill, 0, $need);
			$tagPad = str_pad($tag, 16, "\0");
			if ($need >= 16) {
				for ($i = 0; $i < 16; $i++) {
					$data[$need - 16 + $i] = $tagPad[$i];
				}
			}
		}
		$streams[] = [
			'name' => $name,
			'data' => $data,
			'ranges' => $st['ranges'],
		];
	}
	$blob = fractal_zip_ole_rebuild_from_template((string) $parsed['template'], $streams);
	if ($blob === null || strlen($blob) !== strlen($base)) {
		fwrite(STDERR, "OLE rebuild failed for {$tag}\n");
		exit(1);
	}
	$bn = "{$tag}.doc";
	file_put_contents("{$dest}/{$bn}", $blob);
	$m = [];
	$r = [];
	if (fractal_zip_folder_try_expand_ole($bn, $blob, $m, $r)
		&& (int) ($r[$bn]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
		$classic++;
	}
}

// Keep readme tiny: longer prose can help opaque more than FZCL (measured).
file_put_contents("$dest/r.txt", "x");

$total = 0;
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	$sz = (int) filesize("$dest/$e");
	$total += $sz;
	echo sprintf("%8d  %s\n", $sz, $e);
}
echo "total_raw={$total} stream_classic={$classic}\n";

// Sanity: folder near-dup cluster + shared stream content-addressing.
$raw = [];
foreach ($tags as $tag) {
	$raw["{$tag}.doc"] = (string) file_get_contents("{$dest}/{$tag}.doc");
}
$raw['r.txt'] = (string) file_get_contents("{$dest}/r.txt");
$bundle = fractal_zip_resolve_folder_logical_bundle($raw);
$fp = 0;
foreach ($bundle['restore'] as $spec) {
	if ((int) ($spec['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC
		&& (string) ($spec['format'] ?? '') === 'fp') {
		$fp++;
	}
}
echo "near_dup_fp={$fp} members=" . count($bundle['members']) . "\n";
if ($fp < 3) {
	fwrite(STDERR, "expected ≥3 fp restores from near-dup cluster\n");
	exit(1);
}
