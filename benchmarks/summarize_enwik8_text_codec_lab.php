#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
$path = $argv[1] ?? ($repo . '/benchmarks/.enwik8_text_codec_lab.json');
if (!is_file($path)) {
	fwrite(STDERR, "Missing {$path}\n");
	exit(1);
}
$j = json_decode((string) file_get_contents($path), true);
if (!is_array($j)) {
	exit(1);
}

$byPage = array();
foreach ($j['rows'] ?? array() as $r) {
	if (isset($r['error'])) {
		continue;
	}
	$pid = (int) ($r['page_id'] ?? -1);
	if (!isset($byPage[$pid])) {
		$byPage[$pid] = array(
			'title' => $r['title'] ?? '',
			'text_bytes' => (int) ($r['text_bytes'] ?? 0),
			'text_gzip1' => 0,
			'best' => null,
		);
	}
	if (($r['codec'] ?? '') === '_baseline_text') {
		$byPage[$pid]['text_gzip1'] = (int) ($r['gzip1_bytes'] ?? $r['text_gzip1_bytes'] ?? 0);
		continue;
	}
	$g = (int) ($r['gzip1_bytes'] ?? PHP_INT_MAX);
	if ($byPage[$pid]['best'] === null || $g < (int) $byPage[$pid]['best']['gzip1_bytes']) {
		$byPage[$pid]['best'] = $r;
	}
}

echo "enwik8 text codec lab summary\n";
foreach ($byPage as $pid => $info) {
	$tb = (int) $info['text_bytes'];
	$tg = (int) $info['text_gzip1'];
	$b = $info['best'];
	if ($b === null) {
		continue;
	}
	$bg = (int) ($b['gzip1_bytes'] ?? 0);
	$ratio = $tg > 0 ? round(100.0 * $bg / $tg, 1) : 0;
	printf(
		"  [%d] %s\n    text gzip1=%s  best=%s (%s+%s) ratio=%s%%\n",
		$pid,
		substr((string) $info['title'], 0, 48),
		number_format($tg),
		number_format($bg),
		$b['codec'] ?? '?',
		$b['transform'] ?? '?',
		(string) $ratio
	);
}
