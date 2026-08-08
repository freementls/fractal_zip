#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_web_ref.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_web_ref_probe.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_web_ref_env.php';

bench_web_ref_apply_probe_fast_defaults();
putenv('FRACTAL_ZIP_WEB_REF=1');
putenv('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE=0');
putenv('FRACTAL_ZIP_WEB_REF_URL_LITERAL=1');
putenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR=1');
putenv('FRACTAL_ZIP_WEB_REF_PROBE_MAX_CHUNKS=80');

function corpus_from_dir(string $dir, int $maxBytes = 12_000_000): string
{
	$files = array();
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if ($fi->isFile()) {
			$files[] = $fi->getPathname();
		}
	}
	usort($files, static fn (string $a, string $b): int => filesize($b) <=> filesize($a));
	$blob = '';
	foreach ($files as $f) {
		if (strlen($blob) >= $maxBytes) {
			break;
		}
		$blob .= file_get_contents($f, false, null, 0, min(400_000, $maxBytes - strlen($blob)));
	}
	return $blob;
}

$only = $argv[1] ?? '';
$labels = $only !== '' ? array($only) : array('test_files13', 'test_files57', 'test_files58_sample');

foreach ($labels as $label) {
	$dir = $repo . DIRECTORY_SEPARATOR . $label;
	if (!is_dir($dir)) {
		fwrite(STDERR, "missing $dir\n");
		continue;
	}
	$corpus = corpus_from_dir($dir);
	$probe = fractal_zip_web_ref_probe_fractal_string($corpus, $corpus);
	$trailer = 6;
	foreach ($probe['entries'] as $e) {
		$trailer += fractal_zip_web_ref_trailer_cost_per_entry($e);
	}
	$out = array(
		'label' => $label,
		'corpus_bytes' => strlen($corpus),
		'matches' => (int) ($probe['matches'] ?? 0),
		'entries' => count($probe['entries'] ?? array()),
		'inline_saved' => (int) ($probe['saved'] ?? 0),
		'trailer_est' => $trailer,
		'net_est' => (int) ($probe['saved'] ?? 0) - $trailer,
		'sample_urls' => array(),
	);
	foreach (array_slice($probe['entries'] ?? array(), 0, 5) as $e) {
		$out['sample_urls'][] = html_entity_decode((string) ($e['canonical_url'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
	echo json_encode($out, JSON_UNESCAPED_SLASHES) . "\n";
}
