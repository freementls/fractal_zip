#!/usr/bin/env php
<?php
declare(strict_types=1);

/** Quick integrated phda9_xml wire encode (+ optional roundtrip). Low-RAM: serial chunks (jobs=1). */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT');
putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS');
putenv('FRACTAL_ZIP_PROCESS_GUARD_ORPHAN_GRACE_SEC=600');

$pages = 96;
$roundtrip = false;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(4, (int) substr($arg, 8));
	} elseif ($arg === '--roundtrip') {
		$roundtrip = true;
	}
}

require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';

$src = $repo . '/test_files109/enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pages, count($split['pages']));
$slice = (string) $split['header'];
for ($i = 0; $i < $n; $i++) {
	$slice .= substr($blob, (int) $split['pages'][$i]['start'], (int) $split['pages'][$i]['len']);
}
$slice .= (string) $split['footer'];

$tmp = sys_get_temp_dir() . '/fz_phda9_wq_' . getmypid();
@mkdir($tmp, 0700, true);
$work = $tmp . '/w';
@mkdir($work, 0700, true);
file_put_contents($work . '/enwik8', $slice);

putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_TEXT_INNER=1');
putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
if (getenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER') === false) {
	putenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=96');
}
if (getenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM') === false) {
	putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=0');
}
putenv('FRACTAL_ZIP_SPEED=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');

$t0 = microtime(true);
$fz = new fractal_zip(256, false, true, null, false);
ob_start();
try {
	$fz->zip_folder($work, false);
} finally {
	ob_end_clean();
}
$fzc = $work . '.fz';
$wire = is_file($fzc) ? (int) filesize($fzc) : 0;
$sec = round(microtime(true) - $t0, 2);
echo "pages={$n} wire_bytes={$wire} encode_sec={$sec}\n";

if ($roundtrip && $wire > 0) {
	$extract = $tmp . '/ex';
	@mkdir($extract, 0700, true);
	copy($fzc, $extract . '/enwik8.fz');
	$fx = new fractal_zip(256, false, true, null, false);
	ob_start();
	try {
		$fx->open_container($extract . '/enwik8.fz', false);
	} finally {
		ob_end_clean();
	}
	$got = is_file($extract . '/enwik8') ? (string) file_get_contents($extract . '/enwik8') : '';
	$ok = hash_equals($slice, $got);
	echo 'roundtrip=' . ($ok ? 'ok' : 'FAIL') . "\n";
	if (!$ok) {
		exit(1);
	}
}

fractal_zip_enwik_recursive_remove($tmp);
exit($wire > 0 ? 0 : 1);
