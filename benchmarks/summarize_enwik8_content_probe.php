#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
$path = $argv[1] ?? ($repo . '/benchmarks/.enwik8_content_probe.json');
if (!is_file($path)) {
	fwrite(STDERR, "Run: php benchmarks/bench_enwik8_content_probe.php\n");
	exit(1);
}
$j = json_decode((string) file_get_contents($path), true);
if (!is_array($j)) {
	exit(1);
}

$d = $j['decomposition'] ?? array();
echo "enwik8 content probe summary\n";
echo '  blob ' . number_format((int) ($d['blob_bytes'] ?? 0))
	. ' | text ' . ($d['text_pct_of_blob'] ?? '?') . '% | pages ' . ($d['page_count'] ?? '?') . "\n\n";

echo "Subsets (gzip-1):\n";
foreach ($j['subsets'] ?? array() as $s) {
	$line = sprintf(
		"  %-22s raw %12s  gzip1 %12s",
		$s['id'] ?? '?',
		number_format((int) ($s['raw_bytes'] ?? 0)),
		number_format((int) ($s['gzip1_bytes'] ?? 0))
	);
	if (isset($s['vs_text_gzip1_pct'])) {
		$line .= '  (' . $s['vs_text_gzip1_pct'] . '% of text gzip1)';
	}
	if (isset($s['sidecar_json_bytes'])) {
		$line .= '  sidecar_json=' . number_format((int) $s['sidecar_json_bytes']);
	}
	echo $line . "\n";
}

$refs = $j['refs'] ?? array();
if (!empty($refs['pp96_fzc_bytes']) || !empty($refs['textcodec_fzc_bytes'])) {
	echo "\nFull .fz refs:\n";
	echo '  pp96      ' . number_format((int) ($refs['pp96_fzc_bytes'] ?? 0)) . " B\n";
	echo '  textcodec ' . number_format((int) ($refs['textcodec_fzc_bytes'] ?? 0)) . " B\n";
}

if (($j['mini_fzc'] ?? array()) !== array()) {
	echo "\nMini fzc (sample5 pages folder):\n";
	foreach ($j['mini_fzc'] as $r) {
		echo '  ' . ($r['label'] ?? '?') . '  ' . number_format((int) ($r['fzc_bytes'] ?? 0))
			. ' B  ' . ($r['zip_seconds'] ?? '?') . "s\n";
	}
}

echo "\nPromising avenues (ranked):\n";
$avenues = $j['avenues'] ?? array();
usort($avenues, static fn (array $a, array $b): int => ((int) ($a['priority'] ?? 99)) <=> ((int) ($b['priority'] ?? 99)));
foreach ($avenues as $a) {
	echo '  [' . ($a['priority'] ?? '?') . '] ' . ($a['id'] ?? '') . "\n";
	echo '      ' . ($a['finding'] ?? '') . "\n";
	echo '      → ' . ($a['next'] ?? '') . "\n";
}
