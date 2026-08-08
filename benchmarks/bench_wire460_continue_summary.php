#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
$stamp = '';
$target = 460096;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--stamp=')) {
		$stamp = substr($arg, 8);
	} elseif (str_starts_with($arg, '--target=')) {
		$target = (int) substr($arg, 9);
	}
}
if ($stamp === '') {
	fwrite(STDERR, "usage: bench_wire460_continue_summary.php --stamp=STAMP [--target=460096]\n");
	exit(1);
}

$cases = array(
	'split_inner_phda9_xml_single_stream_lstm_words4096_dict',
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict',
	'split_inner_phda9_xml_single_stream_lstm_words4096_wiki_lom_cfabb_inline',
	'split_inner_phda9_xml_single_stream_lstm_words4096_cfabb_plain_table',
);
$rows = array();
foreach ($cases as $c) {
	$p = $repo . '/benchmarks/.enwik8_wire460_continue_' . $c . '_' . $stamp . '.json';
	if (!is_file($p)) {
		continue;
	}
	$j = json_decode((string) file_get_contents($p), true);
	if (!is_array($j)) {
		continue;
	}
	foreach ($j['rows'] ?? array() as $row) {
		$rows[] = $row;
	}
}
$path = $repo . '/benchmarks/.enwik8_wire460_continue_summary_' . $stamp . '.json';
$out = array(
	'generated' => date('c'),
	'pages' => 384,
	'target_wire' => $target,
	'stamp' => $stamp,
	'rows' => $rows,
);
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "summary → {$path}\n";
