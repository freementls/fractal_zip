#!/usr/bin/env php
<?php

declare(strict_types=1);

/** Read-only summary of dict/refine benchmark JSON artifacts. */
$repo = dirname(__DIR__);
$files = glob($repo . '/benchmarks/.enwik8_phda9_dict*.json') ?: array();
$files = array_merge($files, glob($repo . '/benchmarks/.enwik8_wire_dict_confirm*.json') ?: array());
sort($files);

echo "dict/refine pipeline report (" . date('c') . ")\n\n";

$best96 = array('fzpa' => PHP_INT_MAX, 'label' => '', 'path' => '');
$best384 = array('fzpa' => PHP_INT_MAX, 'label' => '', 'path' => '');

foreach ($files as $f) {
	$j = json_decode((string) file_get_contents($f), true);
	if (!is_array($j)) {
		continue;
	}
	$name = basename($f);
	echo "── {$name}\n";
	if (isset($j['rows']) && is_array($j['rows'])) {
		foreach ($j['rows'] as $row) {
			$lab = (string) ($row['label'] ?? $row['mode'] ?? '?');
			$fz = $row['fzpa'] ?? $row['phda9_fzpa'] ?? $row['wire_fzc'] ?? null;
			if ($fz !== null) {
				printf("   %-20s %10s B\n", $lab, number_format((int) $fz));
				$pages = (int) ($j['pages'] ?? 0);
				if ($pages <= 96 && (int) $fz < $best96['fzpa']) {
					$best96 = array('fzpa' => (int) $fz, 'label' => $lab, 'path' => $name);
				}
				if ($pages >= 384 && (int) $fz < $best384['fzpa']) {
					$best384 = array('fzpa' => (int) $fz, 'label' => $lab, 'path' => $name);
				}
			}
		}
	}
	foreach (array('delta', 'delta_vs_seed', 'final_fzpa', 'refined_fzpa', 'base_fzpa', 'seed_fzpa') as $k) {
		if (array_key_exists($k, $j)) {
			printf("   %s=%s\n", $k, is_int($j[$k]) ? number_format($j[$k]) : (string) $j[$k]);
		}
	}
	if (isset($j['best']) && is_array($j['best'])) {
		printf("   best=%s fzpa=%s\n", (string) ($j['best']['label'] ?? '?'),
			isset($j['best']['fzpa']) ? number_format((int) $j['best']['fzpa']) : '?');
	}
	echo "\n";
}

if ($best96['fzpa'] < PHP_INT_MAX) {
	printf("best @96p FZPA: %s %s B (%s)\n", $best96['label'], number_format($best96['fzpa']), $best96['path']);
}
if ($best384['fzpa'] < PHP_INT_MAX) {
	printf("best @384p FZPA: %s %s B (%s)\n", $best384['label'], number_format($best384['fzpa']), $best384['path']);
}
printf("wire460 baseline: words_4096p @384p FZPA=510,814 wire=518,508 B\n");
