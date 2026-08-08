#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Report which web-census slots are present under test_files201.
 *
 *   php benchmarks/report_web_census_coverage.php
 */

$root = dirname(__DIR__);
$dst = $root . DIRECTORY_SEPARATOR . 'test_files201';

/** @return array<string, list<string>> */
function web_census_slots(): array
{
	return [
		'01_documents' => ['html', 'htm', 'xhtml', 'txt', 'md', 'rtf', 'pdf'],
		'02_markup_style' => ['xml', 'svg', 'css', 'scss', 'less', 'rss', 'atom', 'xsl'],
		'03_scripts' => ['js', 'mjs', 'ts', 'jsx', 'tsx', 'wasm', 'map', 'json', 'jsonld', 'webmanifest'],
		'04_data_exchange' => ['csv', 'tsv', 'ndjson', 'yaml', 'yml', 'toml', 'ini', 'protobuf', 'avro', 'parquet', 'arrow'],
		'05_fonts' => ['woff', 'woff2', 'ttf', 'otf', 'eot'],
		'06_images' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'heic', 'bmp', 'ico', 'tif', 'tiff', 'apng'],
		'07_audio' => ['mp3', 'aac', 'm4a', 'ogg', 'opus', 'wav', 'flac', 'weba'],
		'08_video' => ['mp4', 'webm', 'mkv', 'ogv', 'm3u8', 'vtt', 'srt'],
		'09_archives' => ['zip', 'gz', 'bz2', 'xz', 'zst', 'br', 'lz4', '7z', 'tar', 'tgz'],
		'10_office_ebook' => ['docx', 'xlsx', 'pptx', 'odt', 'ods', 'epub', 'mobi'],
		'11_code' => ['php', 'py', 'go', 'rs', 'java', 'c', 'h', 'cpp', 'rb', 'swift', 'kt', 'sql'],
		'12_databases' => ['sqlite', 'db', 'mdb'],
		'13_ml_tensors' => ['onnx', 'gguf', 'safetensors', 'npy', 'pt'],
		'14_mail_net' => ['eml', 'mbox', 'pcap', 'torrent'],
		'15_3d_cad' => ['stl', 'obj', 'gltf', 'glb', 'usdz'],
		'16_mobile_pkg' => ['apk', 'ipa', 'xapk'],
		'17_misc_web' => ['ics', 'vcf'],
	];
}

if (!is_dir($dst)) {
	fwrite(STDERR, "missing {$dst} — run: php benchmarks/build_test_files201_web_census.php\n");
	exit(2);
}

$presentExt = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dst, FilesystemIterator::SKIP_DOTS));
foreach ($it as $fi) {
	if (!$fi->isFile()) {
		continue;
	}
	$base = $fi->getFilename();
	if ($base === 'MANIFEST.json' || str_starts_with($base, '00_')) {
		continue;
	}
	// special well-known names
	if (in_array($base, ['robots.txt', 'humans.txt', 'security.txt', 'sitemap.xml', 'torrent_magnet.txt'], true)) {
		$presentExt[$base] = ($presentExt[$base] ?? 0) + 1;
	}
	$e = strtolower(pathinfo($base, PATHINFO_EXTENSION));
	if ($e !== '') {
		$presentExt[$e] = ($presentExt[$e] ?? 0) + 1;
	}
}

$slots = web_census_slots();
$have = 0;
$need = 0;
$missing = [];
foreach ($slots as $cat => $exts) {
	foreach ($exts as $ext) {
		$need++;
		if (!empty($presentExt[$ext])) {
			$have++;
		} else {
			$missing[] = "{$cat}/{$ext}";
		}
	}
}

// well-known extras
$extras = ['robots.txt', 'sitemap.xml', 'humans.txt', 'security.txt', 'torrent_magnet.txt'];
$extraHave = 0;
foreach ($extras as $x) {
	$pathOk = is_file($dst . '/17_misc_web/' . $x);
	if ($pathOk) {
		$extraHave++;
	} else {
		$missing[] = "17_misc_web/{$x}";
	}
}

echo "=== Web census coverage (test_files201) ===\n";
echo "extension slots: {$have}/{$need} (" . round(100 * $have / max(1, $need), 1) . "%)\n";
echo "well-known files: {$extraHave}/" . count($extras) . "\n";
if ($missing !== []) {
	echo "missing (" . count($missing) . "):\n";
	foreach ($missing as $m) {
		echo "  - {$m}\n";
	}
	exit(1);
}
echo "PASS: all census slots present\n";
exit(0);
