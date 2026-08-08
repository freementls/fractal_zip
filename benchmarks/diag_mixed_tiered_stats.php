#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_phda9_dict_mine.php';

$mode = 'mixed_tiered';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--mode=')) {
		$mode = substr($arg, 7);
	}
}

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$mined = fractal_zip_phda9_dict_mine_from_enwik($blob, array(
	'mode' => $mode,
	'pages' => 384,
	'word_budget_pct' => 0.85,
	'max_subwords' => 2048,
));
echo json_encode($mined['stats'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
echo 'selected=' . count($mined['words']) . "\n";
