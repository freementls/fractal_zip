#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Profile C screen: wiki_lom wire + matched dict vs raw + entityfold, then
 * hand off to cmix via a small Python helper.
 *
 * Usage:
 *   php benchmarks/run_fractal_wiki_lom_cmix_screen.php [--pages=128]
 */
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
ini_set('memory_limit', '2560M');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_wiki_lom.php';

$pages = 128;
$outDir = $repo . '/benchmarks/.ladder_cache/fractal_dictlab/wiki_lom_cmix';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(16, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--outdir=')) {
		$outDir = substr($arg, 9);
	}
}

$src = $repo . '/test_files109/enwik8';
@mkdir($outDir, 0775, true);

$blob = (string) file_get_contents($src);
// first N pages
$out = array();
$pos = 0;
while (count($out) < $pages) {
	$start = strpos($blob, '<page', $pos);
	if ($start === false) {
		break;
	}
	$next = strpos($blob, "\n  <page", $start + 1);
	$endTag = strpos($blob, '</page>', $start);
	if ($endTag === false) {
		break;
	}
	$end = $endTag + 7;
	$chunkEnd = ($next !== false && $next > $end) ? $next + 1 : $end;
	$chunk = substr($blob, $start, $chunkEnd - $start);
	$p0 = strpos($chunk, '<page');
	if ($p0 !== false && $p0 > 0) {
		$chunk = substr($chunk, $p0);
	}
	$out[] = $chunk;
	$pos = ($next !== false) ? $next + 1 : $end;
}
$raw = implode('', $out);
file_put_contents($outDir . '/raw_pages.bin', $raw);

$opts = array(
	'wiki_html' => true,
	'entity_decode' => true,
	'link_ids' => false,
	'templates' => false,
	'url_dict' => false,
	'abbrevs' => false,
	'acronyms' => false,
	'tag_ids' => false,
	'sweeper' => false,
);
$wire = '';
foreach ($out as $t) {
	$pre = fractal_zip_wiki_lom_preprocess($t, $opts);
	$wire .= (string) ($pre['payload'] ?? '');
}
file_put_contents($outDir . '/wiki_lom_wire.bin', $wire);

$meta = array(
	'pages' => count($out),
	'raw_bytes' => strlen($raw),
	'wire_bytes' => strlen($wire),
	'raw_path' => $outDir . '/raw_pages.bin',
	'wire_path' => $outDir . '/wiki_lom_wire.bin',
);
file_put_contents($outDir . '/meta.json', json_encode($meta, JSON_PRETTY_PRINT) . "\n");
fwrite(STDERR, "pages={$meta['pages']} raw={$meta['raw_bytes']} wire={$meta['wire_bytes']}\n");
fwrite(STDERR, "wrote {$outDir}/meta.json — run run_fractal_wiki_lom_cmix_finish.py next\n");
