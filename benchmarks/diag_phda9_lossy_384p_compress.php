#!/usr/bin/env php
<?php
declare(strict_types=1);

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
$repo = dirname(__DIR__);
$dict = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}

require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_syllable_codec.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once $repo . '/fractal_zip_phda9_dict_mine.php';

$n = max(32, (int) ($argv[1] ?? 384));
$tool = (string) ($argv[2] ?? 'phda9');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=' . $tool);

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
$n = min($n, count($split['pages']));
$pageTexts = array();
$mineText = '';
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$pageTexts[] = fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
	$mineText .= $pageTexts[$i];
}
$model = fractal_zip_enwik_consonant_hybrid_mine_model_pages($pageTexts, $mineText);

$lossyPlain = '';
$pageXml = '';
foreach ($pageTexts as $i => $t) {
	$lo = fractal_zip_enwik_consonant_hybrid_lossy_preprocess($t, array('consonant_model' => $model, 'frozen' => true));
	$lossyPlain .= (string) $lo['payload'];
	$p = $split['pages'][$i];
	$pageXml .= fractal_zip_enwik_inject_text_into_shell_page(
		substr($blob, (int) $p['start'], (int) $p['len']),
		(string) $lo['payload']
	);
}

echo "diag_phda9_lossy @{$n}p tool={$tool}\n";
echo 'lossy_preserve_plain=' . number_format(strlen($lossyPlain)) . "\n";
echo 'lossy_page_xml=' . number_format(strlen($pageXml)) . "\n";

foreach (array('preserve' => $lossyPlain, 'page_xml' => $pageXml) as $label => $plain) {
	$t0 = microtime(true);
	$r = fractal_zip_enwik_phda9_english_compress($plain, array(
		'tool' => $tool,
		'wire_wrap' => false,
		'timeout_sec' => 0,
	));
	$sec = round(microtime(true) - $t0, 1);
	echo sprintf(
		"%s: phda9=%s rt=%s status=%s sec=%s plain=%s\n",
		$label,
		isset($r['bytes']) ? number_format((int) $r['bytes']) : 'FAIL',
		!empty($r['roundtrip_ok']) ? 'ok' : 'FAIL',
		(string) ($r['status'] ?? '?'),
		$sec,
		number_format(strlen($plain))
	);
}
