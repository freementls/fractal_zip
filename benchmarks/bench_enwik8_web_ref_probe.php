#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Content-based web-ref probe on enwik8 (piece-level, real mirror matching).
 * Writes benchmarks/.enwik8_web_ref_probe.json
 *
 * Usage: php benchmarks/bench_enwik8_web_ref_probe.php [path/to/enwik8]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_web_ref.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_web_ref_probe.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_web_ref_env.php';

@ini_set('memory_limit', getenv('FRACTAL_ZIP_BENCH_MEMORY_LIMIT') ?: '2G');

$skipMirrorEnv = getenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR');
if ($skipMirrorEnv === false || trim((string) $skipMirrorEnv) === '') {
	bench_web_ref_apply_probe_fast_defaults();
} else {
	bench_web_ref_apply_env_defaults();
	if (trim((string) $skipMirrorEnv) === '1') {
		putenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR=1');
	}
}

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

putenv('FRACTAL_ZIP_WEB_REF=1');

$urlRows = fractal_zip_web_ref_collect_all_url_candidates($blob);
$urlChunks = fractal_zip_web_ref_find_repeated_url_chunks($blob);
$wholePageEstimate = function_exists('fractal_zip_web_ref_probe_whole_page_estimate')
	? fractal_zip_web_ref_probe_whole_page_estimate($blob)
	: array('candidates' => 0, 'matches' => 0, 'bytes' => 0);

$probe = fractal_zip_web_ref_probe_fractal_string($blob, $blob, $urlRows);

$trailerCost = 0;
foreach ($probe['entries'] as $entry) {
	$trailerCost += fractal_zip_web_ref_trailer_cost_per_entry($entry);
}
$trailerCost += 6;

$netSavings = max(0, (int) $probe['saved'] - $trailerCost);
$zpaqBaseline = 19625015;
$estimatedFzc = max($zpaqBaseline, $fzcBaseline - $netSavings);

$out = array(
	'generated' => date('c'),
	'corpus' => $corpus,
	'raw_bytes' => strlen($blob),
	'fzc_baseline_bytes' => $fzcBaseline,
	'zpaq_baseline_bytes' => $zpaqBaseline,
	'url_candidates' => count($urlRows),
	'url_literal_chunks' => count($urlChunks),
	'whole_page_candidates' => (int) ($wholePageEstimate['candidates'] ?? 0),
	'whole_page_estimate_matches' => (int) ($wholePageEstimate['matches'] ?? 0),
	'whole_page_estimate_bytes' => (int) ($wholePageEstimate['bytes'] ?? 0),
	'repeated_chunks_scanned' => (int) ($probe['chunks'] ?? 0),
	'probe_attempts' => (int) ($probe['probes'] ?? 0),
	'piece_matches' => (int) ($probe['matches'] ?? 0),
	'whole_page_matches' => (int) ($probe['whole_page_matches'] ?? 0),
	'wayback_matches' => (int) ($probe['wayback_matches'] ?? 0),
	'raw_apply_saved' => (int) ($probe['raw_apply_saved'] ?? 0),
	'corpus_matches' => (int) ($probe['corpus_matches'] ?? 0),
	'url_literal_matches' => (int) ($probe['url_literal_matches'] ?? 0),
	'search_matches' => (int) ($probe['search_matches'] ?? 0),
	'fzwr_entries' => count($probe['entries']),
	'inline_saved_bytes' => (int) ($probe['saved'] ?? 0),
	'trailer_bytes' => $trailerCost,
	'net_savings_bytes' => $netSavings,
	'estimated_fzc_with_web_refs' => $estimatedFzc,
	'entries' => array_map(static function (array $e): array {
		return array(
			'code' => $e['code'] ?? '',
			'sha256' => $e['sha256'] ?? '',
			'canonical_url' => $e['canonical_url'] ?? '',
			'piece_offset' => (int) ($e['piece_offset'] ?? 0),
			'piece_length' => (int) ($e['piece_length'] ?? 0),
		);
	}, $probe['entries']),
	'note' => 'Content-based piece probe on raw enwik8; set FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR=1 for fast URL-literal estimate',
);

$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_web_ref_probe.json';
file_put_contents($outPath, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "enwik8 web-ref piece probe\n";
echo '  fzc baseline:        ' . number_format($fzcBaseline) . " B\n";
echo '  url candidates:      ' . number_format(count($urlRows)) . "\n";
echo '  repeated chunks:     ' . number_format((int) ($probe['chunks'] ?? 0)) . "\n";
echo '  whole-page est:      ' . number_format((int) ($wholePageEstimate['matches'] ?? 0)) . ' matches, '
	. number_format((int) ($wholePageEstimate['bytes'] ?? 0)) . " B\n";
echo '  probe attempts:      ' . number_format((int) ($probe['probes'] ?? 0)) . "\n";
echo '  piece matches:       ' . number_format((int) ($probe['matches'] ?? 0))
	. ' (whole ' . number_format((int) ($probe['whole_page_matches'] ?? 0))
	. ', wayback ' . number_format((int) ($probe['wayback_matches'] ?? 0))
	. ' (corpus ' . number_format((int) ($probe['corpus_matches'] ?? 0))
	. ', url ' . number_format((int) ($probe['url_literal_matches'] ?? 0))
	. ', search ' . number_format((int) ($probe['search_matches'] ?? 0)) . ")\n";
echo '  inline saved:        ' . number_format((int) ($probe['saved'] ?? 0)) . " B\n";
echo '  trailer est:         ' . number_format($trailerCost) . " B\n";
echo '  net savings:         ' . number_format($netSavings) . " B\n";
echo '  estimated fzc*:      ' . number_format($estimatedFzc) . ' B (' . number_format($estimatedFzc / (1024 * 1024), 2) . " MiB)\n";
echo "  written: {$outPath}\n";

exit(0);
