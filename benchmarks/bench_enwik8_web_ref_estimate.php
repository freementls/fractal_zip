#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Fast estimate of web-ref savings on enwik8 (no full encode).
 * Writes benchmarks/.enwik8_web_ref_estimate.json for compare_enwik8_four_way.php.
 *
 * Usage: php benchmarks/bench_enwik8_web_ref_estimate.php [path/to/enwik8]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_web_ref.php';

$corpus = $argv[1] ?? ($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8');
$cacheZip = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.squash_corpus_cache' . DIRECTORY_SEPARATOR . 'enwik8.zip';

if (!is_file($corpus) && is_file($cacheZip)) {
	$dir = dirname($corpus);
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}
	fwrite(STDERR, "Materializing enwik8 from cache zip…\n");
	exec('unzip -p ' . escapeshellarg($cacheZip) . ' enwik8 > ' . escapeshellarg($corpus) . ' 2>/dev/null', $xo, $ret);
}

if (!is_file($corpus)) {
	fwrite(STDERR, "Missing enwik8 at {$corpus}\n");
	fwrite(STDERR, "Run: php benchmarks/build_test_files_squash_corpora.php\n");
	exit(1);
}

$blob = file_get_contents($corpus);
if (!is_string($blob) || strlen($blob) !== 100000000) {
	fwrite(STDERR, 'Expected 100000000 B, got ' . (is_string($blob) ? strlen($blob) : 0) . "\n");
	exit(1);
}

$worldJson = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_world_record.json';
$fzcBaseline = 22043397;
if (is_file($worldJson)) {
	$wj = json_decode((string) file_get_contents($worldJson), true);
	if (is_array($wj)) {
		foreach ($wj['cases'] ?? array() as $c) {
			if (($c['label'] ?? '') === 'test_files109') {
				$fzcBaseline = (int) ($c['fzc_bytes'] ?? $fzcBaseline);
				break;
			}
		}
	}
}

// Duplicate http(s) URL strings → @w{8} token savings (inline XML text; optimistic upper bound).
$urlRx = '#https?://[^\s<>"\'\)\]]+#i';
preg_match_all($urlRx, $blob, $m);
$byUrl = array();
foreach ($m[0] as $url) {
	$url = rtrim((string) $url, '.,;');
	if (strlen($url) < 12) {
		continue;
	}
	if (!isset($byUrl[$url])) {
		$byUrl[$url] = 0;
	}
	$byUrl[$url]++;
}

$tokenLen = 11; // @w{XXXXXXXX}
$trailerPerUnique = 80;
$urlOccurrences = 0;
$urlUnique = count($byUrl);
$urlLiteralBytes = 0;
$urlSavings = 0;
$urlsConverted = 0;
foreach ($byUrl as $url => $count) {
	$len = strlen($url);
	$urlLiteralBytes += $len * $count;
	$urlOccurrences += $count;
	$isArchive = (stripos($url, 'archive.org') !== false);
	$convert = ($count > 1) || ($isArchive && $len > $tokenLen + 40);
	if (!$convert) {
		continue;
	}
	$urlsConverted++;
	$urlSavings += ($len - $tokenLen) * $count;
}
$urlSavings = max(0, $urlSavings - $urlsConverted * $trailerPerUnique);

// Archive.org URLs (stable web-ref targets) — extra weight for long snapshot URLs.
$archiveUrls = 0;
$archiveBytes = 0;
foreach ($byUrl as $url => $count) {
	if (stripos($url, 'archive.org') === false && stripos($url, 'web.archive.org') === false) {
		continue;
	}
	$archiveUrls++;
	$archiveBytes += strlen($url) * $count;
}

// Conservative fractal_string web-ref headroom: fraction of (fzc - zpaq) gap from dictionary bloat.
$zpaqBaseline = 19625015;
$dictHeadroom = max(0, $fzcBaseline - $zpaqBaseline);
$fractalWebRefCeiling = (int) ($dictHeadroom * 0.08); // ~8% of fractal overhead, conservative

$totalSavings = min($fzcBaseline - $zpaqBaseline, $urlSavings + $fractalWebRefCeiling);
$estimatedFzc = max($zpaqBaseline, $fzcBaseline - $totalSavings);

$out = array(
	'generated' => date('c'),
	'corpus' => $corpus,
	'raw_bytes' => strlen($blob),
	'fzc_baseline_bytes' => $fzcBaseline,
	'url_unique' => $urlUnique,
	'url_converted_est' => $urlsConverted,
	'url_occurrences' => $urlOccurrences,
	'url_literal_bytes' => $urlLiteralBytes,
	'url_savings_est' => $urlSavings,
	'archive_url_unique' => $archiveUrls,
	'archive_url_literal_bytes' => $archiveBytes,
	'fractal_web_ref_ceiling_est' => $fractalWebRefCeiling,
	'total_savings_est' => $totalSavings,
	'estimated_fzc_with_web_refs' => $estimatedFzc,
	'note' => 'Heuristic estimate only; run run_enwik8_web_ref_track.sh for measured encode with FRACTAL_ZIP_WEB_REF=1',
);

$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_web_ref_estimate.json';
file_put_contents($outPath, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "enwik8 web-ref estimate\n";
echo '  fzc baseline:     ' . number_format($fzcBaseline) . " B\n";
echo '  url savings est:  ' . number_format($urlSavings) . " B ({$urlUnique} unique URLs, {$urlOccurrences} hits)\n";
echo '  archive.org URLs: ' . number_format($archiveUrls) . ' unique, ' . number_format($archiveBytes) . " B literal\n";
echo '  fractal ceiling:  ' . number_format($fractalWebRefCeiling) . " B (est. dict web-ref headroom)\n";
echo '  estimated fzc*:   ' . number_format($estimatedFzc) . ' B (' . number_format($estimatedFzc / (1024 * 1024), 2) . " MiB)\n";
echo "  written: {$outPath}\n";

exit(0);
