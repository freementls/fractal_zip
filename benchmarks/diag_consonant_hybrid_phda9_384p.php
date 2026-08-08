#!/usr/bin/env php
<?php
declare(strict_types=1);

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');
putenv('FRACTAL_ZIP_TEXT_INNER=1');
putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=consonant_hybrid');
putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');

$repo = dirname(__DIR__);
$n = (int) ($argv[1] ?? 384);
$dict = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}

require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';

$src = $repo . '/test_files109/enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
$header = (string) $split['header'];
$footer = (string) $split['footer'];
$slice = $header;
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$slice .= substr($blob, (int) $p['start'], (int) $p['len']);
}
$slice .= $footer;

$tmp = sys_get_temp_dir() . '/fz_syl_rt_' . getmypid();
@mkdir($tmp, 0700, true);
$work = $tmp . '/in';
@mkdir($work, 0700, true);
file_put_contents($work . '/enwik8', $slice);
$fzc = $work . '.fz';

$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($work, false);
$zipSec = round(microtime(true) - $t0, 2);
if (!is_file($fzc)) {
	fwrite(STDERR, "FAIL no fzc\n");
	exit(1);
}
$fzcBytes = (int) filesize($fzc);
$stats = fractal_zip_enwik_text_inner_last_build_stats();

$rtWork = $tmp . '/rt';
@mkdir($rtWork, 0700, true);
copy($fzc, $rtWork . '/t.fz');
$fx = new fractal_zip();
$fx->open_container($rtWork . '/t.fz', false);
$got = (string) file_get_contents($rtWork . '/enwik8');
$match = ($got === $slice);
echo "pages={$n} fzc={$fzcBytes} meta=" . (int) ($stats['meta_bytes'] ?? 0)
	. " zip_sec={$zipSec} orig=" . strlen($slice) . ' got=' . strlen($got)
	. ' match=' . ($match ? 'yes' : 'no') . "\n";
if (!$match) {
	$omin = min(strlen($slice), strlen($got));
	for ($i = 0; $i < $omin; $i++) {
		if ($slice[$i] !== $got[$i]) {
			echo "first diff @{$i}\n";
			echo substr($slice, max(0, $i - 40), 120) . "\n";
			echo substr($got, max(0, $i - 40), 120) . "\n";
			break;
		}
	}
}
exit($match ? 0 : 1);
