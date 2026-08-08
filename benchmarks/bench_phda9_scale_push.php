#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Push phda9 below 1.2 bpc: measure the effect of sort_title clustering + external
 * dict at SCALE (raw phda9 full enwik8 = 1.2008 bpc baseline).
 *
 * For the first N pages (same content), compare phda9 on:
 *   raw   = original page order, no dict
 *   sort  = title-sorted order,  no dict   (+ page-permutation cost)
 *   sort+dict = title-sorted + external dict (+ perm; dict amortized over full enwik8)
 *
 * Usage: php benchmarks/bench_phda9_scale_push.php --pages=2000 [--dict=PATH]
 */
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';

$pages = 2000;
$dict = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
foreach ($argv as $a) {
    if (str_starts_with($a, '--pages=')) $pages = max(1, (int)substr($a, 8));
    elseif (str_starts_with($a, '--dict=')) $dict = substr($a, 7);
}
$fullEnwikBytes = 100000000; // for dict amortization projection

$src = is_file($repo . '/test_files109/enwik8') ? $repo . '/test_files109/enwik8' : $repo . '/enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) { fwrite(STDERR, "split failed\n"); exit(1); }
$all = $split['pages'];
$n = min($pages, count($all));

// first N pages (original order), then title-sorted copy of those same N
$first = array_slice($all, 0, $n);
$sorted = enwik_sort_page_refs_by_title($first);
$chunkSorted = array();
foreach ($sorted as $i => $p) {
    $chunkSorted[] = array('origIndex' => (int)$i, 'start' => (int)$p['start'], 'len' => (int)$p['len']);
}
$pay = fractal_zip_enwik_phda9_english_payloads_from_refs($chunkSorted, $blob);
$rawSlice = (string) $pay['raw_slice_xml'];     // first N, original order
$sortSlice = (string) $pay['sorted_slice_xml']; // first N, title order
$plainLen = strlen($rawSlice);
if (strlen($sortSlice) !== $plainLen) {
    fwrite(STDERR, "WARN length mismatch raw={$plainLen} sort=" . strlen($sortSlice) . "\n");
}
// honest page-permutation cost (store order to invert sort): N * ceil(log2 N) bits
$permBytes = (int) ceil($n * (log($n, 2)) / 8.0);
$dictBytes = is_file($dict) ? (int) filesize($dict) : 0;
$dictAmort = (int) round($dictBytes * $plainLen / $fullEnwikBytes); // dict cost share for this slice's scale
$dictAmortFull = $dictBytes; // full dict cost if shipped once for whole enwik8

printf("phda9 scale push: pages=%d plain=%s B (%.2f MB) perm=%d B dict=%s (%d B)\n",
    $n, number_format($plainLen), $plainLen/1048576, $permBytes, basename($dict), $dictBytes);
echo "baseline ref: raw phda9 full enwik8 = 15,010,414 B = 1.2008 bpc\n\n";

function run_phda9(string $plain, bool $useDict, string $dictPath): array {
    $t0 = microtime(true);
    $r = fractal_zip_enwik_phda9_english_compress($plain, array(
        'tool' => 'phda9',
        'use_dict' => $useDict,
        'dict_path' => $useDict ? $dictPath : '',
        'wire_wrap' => false,
        'timeout_sec' => 0,
    ));
    $sec = microtime(true) - $t0;
    $bytes = is_string($r['payload'] ?? null) ? strlen((string)$r['payload']) : (int)($r['bytes'] ?? 0);
    return array('bytes' => $bytes, 'sec' => $sec, 'rt' => !empty($r['roundtrip_ok']), 'status' => (string)($r['status'] ?? ''));
}

$bpc = fn(int $b) => $b * 8.0 / $plainLen;
$rows = array();

fwrite(STDERR, "[1/3] raw (no dict)...\n");
$raw = run_phda9($rawSlice, false, '');
$rows['raw'] = array('phda9' => $raw['bytes'], 'extra' => 0, 'sec' => $raw['sec'], 'rt' => $raw['rt']);
printf("  raw      : phda9=%s B  bpc=%.4f  rt=%s  %.0fs\n", number_format($raw['bytes']), $bpc($raw['bytes']), $raw['rt']?'ok':'FAIL', $raw['sec']);

fwrite(STDERR, "[2/3] sort_title (no dict)...\n");
$srt = run_phda9($sortSlice, false, '');
$srtTotal = $srt['bytes'] + $permBytes;
$rows['sort'] = array('phda9' => $srt['bytes'], 'extra' => $permBytes, 'sec' => $srt['sec'], 'rt' => $srt['rt']);
printf("  sort     : phda9=%s B +perm=%d => %s B  bpc=%.4f  rt=%s  %.0fs\n",
    number_format($srt['bytes']), $permBytes, number_format($srtTotal), $bpc($srtTotal), $srt['rt']?'ok':'FAIL', $srt['sec']);

fwrite(STDERR, "[3/3] sort_title + dict...\n");
$sd = run_phda9($sortSlice, true, $dict);
$sdTotalSlice = $sd['bytes'] + $permBytes + $dictAmort;       // dict amortized at this slice scale
$sdTotalFullProj = $sd['bytes'] + $permBytes;                  // dict cost folded only at full scale (see note)
$rows['sort_dict'] = array('phda9' => $sd['bytes'], 'extra' => $permBytes + $dictAmort, 'sec' => $sd['sec'], 'rt' => $sd['rt']);
printf("  sort+dict: phda9=%s B +perm=%d +dictAmort=%d => %s B  bpc=%.4f  rt=%s  %.0fs\n",
    number_format($sd['bytes']), $permBytes, $dictAmort, number_format($sdTotalSlice), $bpc($sdTotalSlice), $sd['rt']?'ok':'FAIL', $sd['sec']);
printf("           (phda9+perm only, dict folded at full-enwik8 scale: bpc=%.4f; full dict overhead=%.4f bpc)\n",
    $bpc($sdTotalFullProj), $dictAmortFull*8.0/$fullEnwikBytes);

echo "\n--- deltas (bpc) ---\n";
printf("  sort vs raw      : %+.4f bpc (%+d B w/perm)\n", $bpc($srtTotal)-$bpc($raw['bytes']), $srtTotal-$raw['bytes']);
printf("  sort+dict vs raw : %+.4f bpc (slice-amort dict)\n", $bpc($sdTotalSlice)-$bpc($raw['bytes']));
printf("  sort+dict vs sort: %+.4f bpc (dict marginal, slice-amort)\n", $bpc($sdTotalSlice)-$bpc($srtTotal));

$out = $repo . '/benchmarks/.phda9_scale_push_' . $n . 'p.json';
file_put_contents($out, json_encode(array(
    'pages' => $n, 'plain_bytes' => $plainLen, 'perm_bytes' => $permBytes,
    'dict' => basename($dict), 'dict_bytes' => $dictBytes,
    'raw' => $rows['raw'], 'sort' => $rows['sort'], 'sort_dict' => $rows['sort_dict'],
    'bpc' => array('raw' => $bpc($raw['bytes']), 'sort' => $bpc($srtTotal),
                   'sort_dict_sliceamort' => $bpc($sdTotalSlice), 'sort_dict_fullproj' => $bpc($sdTotalFullProj)),
), JSON_PRETTY_PRINT));
echo "\njson -> {$out}\nSCALEPUSH_DONE\n";
