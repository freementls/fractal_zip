#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Bridge lab: bring fractal_zip preprocess family (Profile C / wiki_lom / CFABB)
 * into the *measurement* path of the current fx2-cmix construct.
 *
 * Does NOT integrate into cmix -e yet. Measures transform+table sizes with
 * zstd-19 / xz proxies on a page sample, so we can decide which fz modes are
 * worth a matched-dict rebuild + real cmix screen under match3m_fractalv2.
 *
 * Usage:
 *   php benchmarks/run_fractal_fz_bridge_lab.php [--pages=64] [--out=...]
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
ini_set('memory_limit', '2560M');

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';

$pages = 64;
$outPath = $repo . '/benchmarks/.ladder_cache/fractal_dictlab/fz_bridge.json';
// Default enwik8 — full enwik9 OOMs under typical 1G CLI limits when split.
$src = $repo . '/test_files109/enwik8';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(8, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--out=')) {
		$outPath = substr($arg, 6);
	} elseif (str_starts_with($arg, '--src=')) {
		$src = substr($arg, 6);
	}
}

/** Extract first N <page> bodies without building a full page index. */
function fz_bridge_first_pages(string $blob, int $n): array
{
	$out = array();
	$pos = 0;
	$len = strlen($blob);
	while (count($out) < $n) {
		$start = strpos($blob, '<page', $pos);
		if ($start === false) {
			break;
		}
		// Prefer the usual "  <page>\n" form; accept bare <page...
		$next = strpos($blob, "\n  <page", $start + 1);
		$endTag = strpos($blob, '</page>', $start);
		if ($endTag === false) {
			break;
		}
		$end = $endTag + 7;
		if ($next !== false && $next < $end) {
			// malformed overlap — fall through to endTag
		}
		// Include trailing newline up to next page if present and close
		$chunkEnd = ($next !== false) ? $next + 1 : $end;
		if ($chunkEnd < $end) {
			$chunkEnd = $end;
		}
		// Prefer exclusive end at next page start when well-formed
		if ($next !== false && $next > $end) {
			$chunkEnd = $next + 1;
		}
		$chunk = substr($blob, $start, max(0, $chunkEnd - $start));
		// Normalize to start at '  <page' when possible
		$p0 = strpos($chunk, '<page');
		if ($p0 !== false && $p0 > 0) {
			$chunk = substr($chunk, $p0);
		}
		$title = fractal_zip_enwik_extract_title(substr($chunk, 0, min(4096, strlen($chunk))));
		$out[] = array('title' => $title, 'text' => $chunk);
		$pos = $start + 5;
		if ($next !== false) {
			$pos = $next + 1;
		} else {
			$pos = $end;
		}
	}
	return $out;
}

function fz_bridge_zstd19(string $path): int
{
	$out = $path . '.zst.tmp';
	passthru('zstd -19 -f -q ' . escapeshellarg($path) . ' -o ' . escapeshellarg($out), $rc);
	if ($rc !== 0 || !is_file($out)) {
		return -1;
	}
	$n = (int) filesize($out);
	@unlink($out);
	return $n;
}

function fz_bridge_xz(string $path): int
{
	$out = $path . '.xz.tmp';
	passthru('xz -9 -f -k -c ' . escapeshellarg($path) . ' > ' . escapeshellarg($out), $rc);
	if ($rc !== 0 || !is_file($out)) {
		return -1;
	}
	$n = (int) filesize($out);
	@unlink($out);
	return $n;
}

function fz_bridge_measure(string $label, string $blob, string $workdir, array &$rows): void
{
	$path = $workdir . '/' . preg_replace('/[^A-Za-z0-9_.-]+/', '_', $label) . '.bin';
	file_put_contents($path, $blob);
	$z = fz_bridge_zstd19($path);
	$x = fz_bridge_xz($path);
	$rows[] = array(
		'label' => $label,
		'raw' => strlen($blob),
		'zstd19' => $z,
		'xz9' => $x,
	);
	fwrite(STDERR, sprintf("%s: raw=%d zstd19=%d xz9=%d\n", $label, strlen($blob), $z, $x));
}

if (!is_file($src)) {
	fwrite(STDERR, "missing {$src}\n");
	exit(1);
}

$workdir = dirname($outPath);
if (!is_dir($workdir)) {
	mkdir($workdir, 0775, true);
}

fwrite(STDERR, "reading {$src}\n");
$blob = (string) file_get_contents($src);
$pageRecs = fz_bridge_first_pages($blob, $pages);
unset($blob);
if ($pageRecs === array()) {
	fwrite(STDERR, "page extract failed\n");
	exit(1);
}

$n = count($pageRecs);
$sample = '';
$pageTexts = array();
foreach ($pageRecs as $rec) {
	$sample .= $rec['text'];
	$pageTexts[] = $rec['text'];
}
fwrite(STDERR, "sample pages={$n} bytes=" . strlen($sample) . "\n");

$rows = array();
fz_bridge_measure('raw_pages', $sample, $workdir, $rows);

// Layer A: wiki_html + entity_decode only (lightest Profile-C-ish wire)
$optsLight = array(
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
$light = '';
foreach ($pageTexts as $t) {
	$pre = fractal_zip_wiki_lom_preprocess($t, $optsLight);
	$light .= (string) ($pre['payload'] ?? '');
}
fz_bridge_measure('wiki_lom_html_entity', $light, $workdir, $rows);

// Layer B: + link_ids (needs title table mined from sample)
$titleToId = array();
foreach ($pageRecs as $i => $rec) {
	$title = (string) ($rec['title'] ?? '');
	if ($title !== '') {
		$titleToId[fractal_zip_wiki_lom_normalize_title($title)] = $i + 1;
	}
}
$optsLinks = $optsLight;
$optsLinks['link_ids'] = true;
$optsLinks['title_to_id'] = $titleToId;
$withLinks = '';
foreach ($pageTexts as $t) {
	$pre = fractal_zip_wiki_lom_preprocess($t, $optsLinks);
	$withLinks .= (string) ($pre['payload'] ?? '');
}
$titleBlob = json_encode($titleToId, JSON_UNESCAPED_SLASHES);
fz_bridge_measure('wiki_lom_html_entity_links', $withLinks, $workdir, $rows);
fz_bridge_measure('wiki_lom_title_table_json', (string) $titleBlob, $workdir, $rows);

// Layer C: CFABB acronyms if a frozen table exists
$cfabbPath = $repo . '/benchmarks/.enwik8_cfabb_table_384p.json';
$cfabbRows = array();
if (is_file($cfabbPath)) {
	putenv('FRACTAL_ZIP_CFABB_TABLE=' . $cfabbPath);
	$acronyms = fractal_zip_cfabb_load_json($cfabbPath);
	if (is_array($acronyms) && $acronyms !== array()) {
		$optsCfabb = $optsLinks;
		$optsCfabb['acronyms'] = true;
		$optsCfabb['acronyms_list'] = $acronyms;
		$withCfabb = '';
		foreach ($pageTexts as $t) {
			$pre = fractal_zip_wiki_lom_preprocess($t, $optsCfabb);
			$withCfabb .= (string) ($pre['payload'] ?? '');
		}
		fz_bridge_measure('wiki_lom_links_cfabb', $withCfabb, $workdir, $rows);
		$fold = fractal_zip_wiki_lom_inner_fold_blob(array('acronyms_list' => $acronyms));
		fz_bridge_measure('wiki_lom_cfabb_fold_blob', (string) $fold, $workdir, $rows);
		$cfabbRows = array('n_acronyms' => count($acronyms), 'table' => $cfabbPath);
	}
}

// Cheap "entityfold-shaped" proxy: collapse &quot;/&lt;/&gt;/&amp; only (matches B0 idea)
$entityMap = array(
	'&quot;' => "\x01",
	'&lt;' => "\x02",
	'&gt;' => "\x03",
	'&amp;' => "\x04",
);
$b0ish = strtr($sample, $entityMap);
fz_bridge_measure('entityfold_b0_proxy', $b0ish, $workdir, $rows);

$baseZ = $rows[0]['zstd19'] ?? null;
$baseX = $rows[0]['xz9'] ?? null;
foreach ($rows as &$r) {
	if ($baseZ !== null && ($r['zstd19'] ?? -1) >= 0) {
		$r['delta_zstd19_vs_raw'] = $r['zstd19'] - $baseZ;
	}
	if ($baseX !== null && ($r['xz9'] ?? -1) >= 0) {
		$r['delta_xz9_vs_raw'] = $r['xz9'] - $baseX;
	}
}
unset($r);

$result = array(
	'src' => $src,
	'pages' => $n,
	'sample_raw_bytes' => strlen($sample),
	'cfabb' => $cfabbRows,
	'variants' => $rows,
	'note' => (
		'Proxy only. Positive delta_zstd/xz vs raw does not kill a candidate if the ' .
		'matched dict+cmix path recovers it; negative is a green light to build a ' .
		'Profile-C fx2 binary+dict and screen @1MB/10MB under match3m_fractalv2. ' .
		'B5 STRUCT_FOLD already REJECTED at real cmix — do not re-open without new evidence.'
	),
);

file_put_contents($outPath, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
fwrite(STDERR, "wrote {$outPath}\n");
