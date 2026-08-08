#!/usr/bin/env php
<?php
declare(strict_types=1);
putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_phda9_dict_mine.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
$n = (int) ($argv[1] ?? 384);
$dict = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}
$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$t0 = microtime(true);
$corp = fractal_zip_phda9_dict_build_consonant_hybrid_corpus($blob, $n);
echo 'plain_len=' . strlen($corp['page_xml']) . ' sk_model_unique='
	. count($corp['model']['skeleton_unique'] ?? array()) . ' mine_sec=' . round(microtime(true) - $t0, 1) . "\n";
$t1 = microtime(true);
$r = fractal_zip_enwik_phda9_english_compress((string) $corp['page_xml'], array(
	'wire_wrap' => true,
	'timeout_sec' => 0,
));
echo 'phda9 status=' . ($r['status'] ?? '?') . ' rt=' . (!empty($r['roundtrip_ok']) ? 'yes' : 'no')
	. ' bytes=' . ($r['bytes'] ?? 0) . ' sec=' . ($r['seconds'] ?? 0) . "\n";
exit(empty($r['roundtrip_ok']) ? 1 : 0);
