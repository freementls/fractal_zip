#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_web_ref_env.php';

bench_web_ref_apply_probe_fast_defaults();
putenv('FRACTAL_ZIP_WEB_REF=1');
putenv('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE=1');
putenv('FRACTAL_ZIP_WEB_REF_URL_LITERAL=0');

$maxPages = (int) (getenv('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE_MAX') ?: 500);
putenv('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE_MAX=' . $maxPages);

$dir = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'test_files109';
$prep = fractal_zip_enwik_try_prepare_virtual_folder($dir);
if ($prep === null) {
	fwrite(STDERR, "enwik prep failed\n");
	exit(1);
}
$fz = new fractal_zip();
$fz->enwik_zip_ctx = $prep;
$corpus = fractal_zip_web_ref_probe_corpus_from_zip($fz);

$cands = fractal_zip_web_ref_collect_whole_page_candidates($corpus);
$grossInline = 0;
foreach ($cands as $c) {
	$piece = (string) ($c['piece_bytes'] ?? '');
	if ($piece !== '') {
		$grossInline += substr_count($corpus, $piece) * strlen($piece);
	}
}

$wholeEst = function_exists('fractal_zip_web_ref_probe_whole_page_estimate')
	? fractal_zip_web_ref_probe_whole_page_estimate($corpus)
	: array();

$res = fractal_zip_web_ref_apply_before_recursive_zip($fz);

$baseline = 22043397;
$measured = null;
$trackPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_web_ref_track.json';
if (is_file($trackPath)) {
	$j = json_decode((string) file_get_contents($trackPath), true);
	if (is_array($j)) {
		foreach ($j['cases'] ?? array() as $c) {
			if (($c['label'] ?? '') === 'test_files109') {
				$measured = (int) ($c['fzc_bytes'] ?? 0);
				break;
			}
		}
	}
}

$out = array(
	'whole_page_max' => $maxPages,
	'whole_page_candidates' => count($cands),
	'whole_page_gross_inline_bytes' => $grossInline,
	'whole_page_estimate' => $wholeEst,
	'raw_apply_entries' => is_array($res) ? count($res['entries']) : 0,
	'raw_apply_saved' => is_array($res) ? (int) ($res['saved'] ?? 0) : 0,
	'raw_apply_stats' => is_array($res) ? ($res['stats'] ?? array()) : array(),
	'fzc_baseline_bytes' => $baseline,
	'fzc_measured_web_ref_bytes' => $measured,
	'fzc_delta_vs_baseline' => ($measured !== null && $measured > 0) ? $baseline - $measured : null,
);

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
fractal_zip_enwik_cleanup_virtual_folder($prep);
