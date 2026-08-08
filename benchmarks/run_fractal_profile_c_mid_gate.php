#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Profile C mid-slice wire: entity_decode-only on a contiguous mid blob.
 * Writes wire + compact entity_manifest stream for fz sidecar accounting.
 *
 * Usage:
 *   php benchmarks/run_fractal_profile_c_mid_gate.php \
 *     --in=benchmarks/.ladder_cache/enwik8_1m_skip10.mid \
 *     --outdir=benchmarks/.ladder_cache/fractal_dictlab/profile_c_1m
 */
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
ini_set('memory_limit', '2560M');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_wiki_lom.php';

$in = '';
$outDir = '';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--in=')) {
		$in = substr($arg, 5);
	}
	if (str_starts_with($arg, '--outdir=')) {
		$outDir = substr($arg, 9);
	}
}
if ($in === '' || $outDir === '') {
	fwrite(STDERR, "usage: --in=PATH --outdir=DIR\n");
	exit(2);
}
if ($in[0] !== '/') {
	$in = $repo . '/' . $in;
}
if ($outDir[0] !== '/') {
	$outDir = $repo . '/' . $outDir;
}
@mkdir($outDir, 0775, true);

$raw = (string) file_get_contents($in);
$opts = array(
	'wiki_html' => false,
	'entity_decode' => true,
	'link_ids' => false,
	'templates' => false,
	'url_dict' => false,
	'abbrevs' => false,
	'acronyms' => false,
	'tag_ids' => false,
	'sweeper' => false,
);

// Prefer page-wise when mid contains <page>; else whole-blob.
$pages = array();
$pos = 0;
while (true) {
	$s = strpos($raw, '<page', $pos);
	if ($s === false) {
		break;
	}
	$e = strpos($raw, '</page>', $s);
	if ($e === false) {
		break;
	}
	$e += 7;
	$pages[] = substr($raw, $s, $e - $s);
	$pos = $e;
}

$wire = '';
$sc = '';
$mode = 'whole';
if (count($pages) >= 2) {
	$mode = 'pages';
	foreach ($pages as $t) {
		$pre = fractal_zip_wiki_lom_preprocess($t, $opts);
		$wire .= (string) ($pre['payload'] ?? '');
		foreach (($pre['sidecar']['entity_manifest'] ?? array()) as $ent) {
			$sc .= ($ent['wire'] ?? '') . "\0" . ($ent['decoded'] ?? '') . "\0";
		}
		$sc .= "\n";
	}
} else {
	$pre = fractal_zip_wiki_lom_preprocess($raw, $opts);
	$wire = (string) ($pre['payload'] ?? '');
	foreach (($pre['sidecar']['entity_manifest'] ?? array()) as $ent) {
		$sc .= ($ent['wire'] ?? '') . "\0" . ($ent['decoded'] ?? '') . "\0";
	}
}

file_put_contents($outDir . '/raw.mid', $raw);
file_put_contents($outDir . '/wire.mid', $wire);
file_put_contents($outDir . '/entity_manifests.bin', $sc);
$meta = array(
	'in' => $in,
	'mode' => $mode,
	'pages' => count($pages),
	'raw_bytes' => strlen($raw),
	'wire_bytes' => strlen($wire),
	'manifest_bytes' => strlen($sc),
	'flags' => 'entity_decode_only',
	'raw_path' => $outDir . '/raw.mid',
	'wire_path' => $outDir . '/wire.mid',
);
file_put_contents($outDir . '/meta.json', json_encode($meta, JSON_PRETTY_PRINT) . "\n");
fwrite(STDERR, "mode={$mode} pages={$meta['pages']} raw={$meta['raw_bytes']} wire={$meta['wire_bytes']} sc={$meta['manifest_bytes']}\n");
fwrite(STDERR, "wrote {$outDir}/meta.json\n");
