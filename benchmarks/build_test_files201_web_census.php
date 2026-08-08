#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Build test_files201 — “web census” stratified corpus.
 *
 * Goal: one small, valid-enough fixture per major content class found on the public
 * web / Common-Crawl-ish mix, so fractal_zip benches are not only Squash/Silesia +
 * a few specialty trees. Files are synthetic or tool-generated (no third-party rips).
 *
 * Size budget: keep the tree roughly ≤8 MiB so it stays in default bench discovery.
 *
 * Usage (repo root):
 *   php benchmarks/build_test_files201_web_census.php
 *   php benchmarks/build_test_files201_web_census.php --force
 *
 * Coverage report:
 *   php benchmarks/report_web_census_coverage.php
 */

$root = dirname(__DIR__);
$dst = $root . DIRECTORY_SEPARATOR . 'test_files201';
$force = in_array('--force', $argv, true);
$maxBytes = 8 * 1024 * 1024;

function wc_which(string $name): ?string
{
	$p = trim((string) shell_exec('command -v ' . escapeshellarg($name) . ' 2>/dev/null'));
	return $p !== '' ? $p : null;
}

/** @param list<string> $argv */
function wc_run(array $argv, ?string $cwd = null): void
{
	$descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
	$p = proc_open($argv, $descriptors, $pipes, $cwd, null);
	if (!is_resource($p)) {
		throw new RuntimeException('proc_open failed: ' . implode(' ', $argv));
	}
	fclose($pipes[0]);
	$out = stream_get_contents($pipes[1]);
	$err = stream_get_contents($pipes[2]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	$code = proc_close($p);
	if ($code !== 0) {
		throw new RuntimeException('Command failed (' . $code . '): ' . implode(' ', $argv) . "\n" . $err . "\n" . $out);
	}
}

function wc_rrmdir(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		$item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
	}
	@rmdir($dir);
}

function wc_mkdir(string $path): void
{
	if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
		throw new RuntimeException('mkdir failed: ' . $path);
	}
}

function wc_write(string $path, string $bytes): void
{
	wc_mkdir(dirname($path));
	if (file_put_contents($path, $bytes) === false) {
		throw new RuntimeException('write failed: ' . $path);
	}
}

function wc_copy(string $src, string $dst): void
{
	wc_mkdir(dirname($dst));
	if (!@copy($src, $dst)) {
		throw new RuntimeException("copy failed: {$src} → {$dst}");
	}
}

/** Minimal ZIP (store) with path => bytes. */
function wc_zip_store(string $zipPath, array $files): void
{
	wc_mkdir(dirname($zipPath));
	if (is_file($zipPath)) {
		@unlink($zipPath);
	}
	$zip = new ZipArchive();
	if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
		throw new RuntimeException('ZipArchive open failed: ' . $zipPath);
	}
	foreach ($files as $name => $bytes) {
		$zip->addFromString((string) $name, (string) $bytes);
	}
	$zip->close();
}

/**
 * Target census slots: category => list of extension (or logical id).
 * Keep in sync with benchmarks/report_web_census_coverage.php.
 *
 * @return array<string, list<string>>
 */
function wc_census_slots(): array
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
		'17_misc_web' => ['ics', 'vcf', 'torrent_magnet.txt', 'robots.txt', 'sitemap.xml', 'humans.txt', 'security.txt'],
	];
}

if (is_dir($dst) && !$force) {
	$manifest = $dst . DIRECTORY_SEPARATOR . 'MANIFEST.json';
	if (is_file($manifest)) {
		echo "OK test_files201 already present (use --force to rebuild).\n";
		exit(0);
	}
}

wc_rrmdir($dst);
wc_mkdir($dst);

/** @var list<array<string,mixed>> $manifestRows */
$manifestRows = [];
$skipped = [];

$note = static function (string $rel, string $ext, string $category, string $mime, string $how, string $note = '') use (&$manifestRows): void {
	$manifestRows[] = [
		'path' => $rel,
		'ext' => $ext,
		'category' => $category,
		'content_type' => $mime,
		'source' => $how,
		'note' => $note,
	];
};

$put = static function (string $rel, string $bytes, string $ext, string $category, string $mime, string $how, string $n = '') use ($dst, $note): void {
	wc_write($dst . DIRECTORY_SEPARATOR . $rel, $bytes);
	$note($rel, $ext, $category, $mime, $how, $n);
};

// --- 01 documents ---
$put(
	'01_documents/sample.html',
	"<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"utf-8\"><title>census</title></head>"
	. "<body><h1>web census</h1><p>Representative HTML5 fixture.</p></body></html>\n",
	'html',
	'01_documents',
	'text/html',
	'synthetic'
);
$put(
	'01_documents/sample.htm',
	"<html><body><p>legacy .htm</p></body></html>\n",
	'htm',
	'01_documents',
	'text/html',
	'synthetic'
);
$put(
	'01_documents/sample.xhtml',
	"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
	. "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" "
	. "\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n"
	. "<html xmlns=\"http://www.w3.org/1999/xhtml\"><head><title>x</title></head>"
	. "<body><p>xhtml</p></body></html>\n",
	'xhtml',
	'01_documents',
	'application/xhtml+xml',
	'synthetic'
);
$put('01_documents/sample.txt', "Plain text web census fixture.\nLine two.\n", 'txt', '01_documents', 'text/plain', 'synthetic');
$put('01_documents/sample.md', "# Web census\n\nMarkdown fixture with a [link](https://example.com/).\n", 'md', '01_documents', 'text/markdown', 'synthetic');
$put(
	'01_documents/sample.rtf',
	"{\\rtf1\\ansi\\deff0{\\fonttbl{\\f0 Times New Roman;}}\\f0\\fs24 Web census RTF.\\par}\n",
	'rtf',
	'01_documents',
	'application/rtf',
	'synthetic'
);

// Minimal PDF (single empty page-ish valid header + trailer pattern used by many tools)
$pdf = "%PDF-1.4\n"
	. "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n"
	. "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n"
	. "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] /Contents 4 0 R /Resources<< /Font<< /F1 5 0 R >> >> >>endobj\n"
	. "4 0 obj<< /Length 44 >>stream\nBT /F1 12 Tf 50 150 Td (census) Tj ET\nendstream\nendobj\n"
	. "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n"
	. "xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000266 00000 n \n0000000361 00000 n \n"
	. "trailer<< /Size 6 /Root 1 0 R >>\nstartxref\n429\n%%EOF\n";
$put('01_documents/sample.pdf', $pdf, 'pdf', '01_documents', 'application/pdf', 'synthetic');

// --- 02 markup/style ---
$put('02_markup_style/sample.xml', "<?xml version=\"1.0\"?><census id=\"201\"><item>ok</item></census>\n", 'xml', '02_markup_style', 'application/xml', 'synthetic');
$put(
	'02_markup_style/sample.svg',
	"<?xml version=\"1.0\"?><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"32\" height=\"32\">"
	. "<rect width=\"32\" height=\"32\" fill=\"#246\"/><circle cx=\"16\" cy=\"16\" r=\"8\" fill=\"#fff\"/></svg>\n",
	'svg',
	'02_markup_style',
	'image/svg+xml',
	'synthetic'
);
$put('02_markup_style/sample.css', "body{font-family:system-ui;color:#222}h1{letter-spacing:.02em}\n", 'css', '02_markup_style', 'text/css', 'synthetic');
$put('02_markup_style/sample.scss', "\$c:#246;body{color:\$c;a{text-decoration:none}}\n", 'scss', '02_markup_style', 'text/x-scss', 'synthetic');
$put('02_markup_style/sample.less', "@c:#246;body{color:@c;}\n", 'less', '02_markup_style', 'text/x-less', 'synthetic');
$put(
	'02_markup_style/sample.rss',
	"<?xml version=\"1.0\"?><rss version=\"2.0\"><channel><title>census</title>"
	. "<link>https://example.com/</link><item><title>one</title></item></channel></rss>\n",
	'rss',
	'02_markup_style',
	'application/rss+xml',
	'synthetic'
);
$put(
	'02_markup_style/sample.atom',
	"<?xml version=\"1.0\"?><feed xmlns=\"http://www.w3.org/2005/Atom\"><title>census</title>"
	. "<id>urn:census:201</id><updated>2026-01-01T00:00:00Z</updated></feed>\n",
	'atom',
	'02_markup_style',
	'application/atom+xml',
	'synthetic'
);
$put(
	'02_markup_style/sample.xsl',
	"<?xml version=\"1.0\"?><xsl:stylesheet version=\"1.0\" xmlns:xsl=\"http://www.w3.org/1999/XSL/Transform\">"
	. "<xsl:template match=\"/\">ok</xsl:template></xsl:stylesheet>\n",
	'xsl',
	'02_markup_style',
	'application/xslt+xml',
	'synthetic'
);

// --- 03 scripts ---
$put('03_scripts/sample.js', "export const census = () => 'web-census-201';\nconsole.log(census());\n", 'js', '03_scripts', 'text/javascript', 'synthetic');
$put('03_scripts/sample.mjs', "export default { id: 201, kind: 'web-census' };\n", 'mjs', '03_scripts', 'text/javascript', 'synthetic');
$put('03_scripts/sample.ts', "export type Census = { id: number };\nexport const c: Census = { id: 201 };\n", 'ts', '03_scripts', 'text/typescript', 'synthetic');
$put('03_scripts/sample.jsx', "export default function App(){return <div>census</div>}\n", 'jsx', '03_scripts', 'text/jsx', 'synthetic');
$put('03_scripts/sample.tsx', "export default function App(): JSX.Element { return <div>census</div>; }\n", 'tsx', '03_scripts', 'text/tsx', 'synthetic');
// Minimal valid WASM: empty module
$put('03_scripts/sample.wasm', "\0asm\x01\0\0\0", 'wasm', '03_scripts', 'application/wasm', 'synthetic', 'empty wasm module');
$put('03_scripts/sample.js.map', "{\"version\":3,\"file\":\"sample.js\",\"sources\":[\"sample.ts\"],\"mappings\":\"AAAA\"}\n", 'map', '03_scripts', 'application/json', 'synthetic');
$put('03_scripts/sample.json', "{\"census\":201,\"ok\":true,\"tags\":[\"web\",\"fixture\"]}\n", 'json', '03_scripts', 'application/json', 'synthetic');
$put(
	'03_scripts/sample.jsonld',
	"{\"@context\":\"https://schema.org\",\"@type\":\"Dataset\",\"name\":\"web-census-201\"}\n",
	'jsonld',
	'03_scripts',
	'application/ld+json',
	'synthetic'
);
$put(
	'03_scripts/sample.webmanifest',
	"{\"name\":\"Web Census\",\"short_name\":\"census\",\"start_url\":\"/\",\"display\":\"standalone\",\"icons\":[]}\n",
	'webmanifest',
	'03_scripts',
	'application/manifest+json',
	'synthetic'
);

// --- 04 data ---
$put('04_data_exchange/sample.csv', "id,name,score\n1,alpha,0.1\n2,beta,0.2\n", 'csv', '04_data_exchange', 'text/csv', 'synthetic');
$put('04_data_exchange/sample.tsv', "id\tname\tscore\n1\talpha\t0.1\n", 'tsv', '04_data_exchange', 'text/tab-separated-values', 'synthetic');
$put('04_data_exchange/sample.ndjson', "{\"i\":1}\n{\"i\":2}\n", 'ndjson', '04_data_exchange', 'application/x-ndjson', 'synthetic');
$put('04_data_exchange/sample.yaml', "census: 201\nitems:\n  - html\n  - wasm\n", 'yaml', '04_data_exchange', 'application/yaml', 'synthetic');
$put('04_data_exchange/sample.yml', "kind: web-census\nid: 201\n", 'yml', '04_data_exchange', 'application/yaml', 'synthetic');
$put('04_data_exchange/sample.toml', "id = 201\nname = \"web-census\"\n", 'toml', '04_data_exchange', 'application/toml', 'synthetic');
$put('04_data_exchange/sample.ini', "[census]\nid=201\nkind=web\n", 'ini', '04_data_exchange', 'text/plain', 'synthetic');
// protobuf wire: field 1 string "census" — just a tiny binary blob with common pb look
$put('04_data_exchange/sample.protobuf', "\x0a\x06census\x10\xc9\x01", 'protobuf', '04_data_exchange', 'application/x-protobuf', 'synthetic', 'minimal protobuf-like bytes');
// Avro object container file magic
$put(
	'04_data_exchange/sample.avro',
	"Obj\x01\x04\x16avro.schema\x2a{\"type\":\"string\"}\x14avro.codec\x08null\0"
	. str_repeat("\0", 16)
	. "\x02\x0ccensus",
	'avro',
	'04_data_exchange',
	'application/avro',
	'synthetic',
	'minimal Avro OCF-ish header'
);
// Parquet: magic + footer magic (not a full readable table, but extension + magic present)
$put(
	'04_data_exchange/sample.parquet',
	'PAR1' . str_repeat("\0", 64) . 'PAR1',
	'parquet',
	'04_data_exchange',
	'application/vnd.apache.parquet',
	'synthetic',
	'PAR1 magic stub'
);
$put(
	'04_data_exchange/sample.arrow',
	'ARROW1' . str_repeat("\0", 32),
	'arrow',
	'04_data_exchange',
	'application/vnd.apache.arrow.file',
	'synthetic',
	'Arrow IPC magic stub'
);

// --- 05 fonts (copy a system TTF; generate others when tools exist) ---
$ttfSrc = null;
foreach ([
	'/usr/share/fonts/TTF/DejaVuSans.ttf',
	'/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
	'/usr/share/fonts/TTF/AkaashNormal.ttf',
] as $cand) {
	if (is_file($cand)) {
		$ttfSrc = $cand;
		break;
	}
}
if ($ttfSrc === null) {
	$found = trim((string) shell_exec('fc-list : file | head -1 | cut -d: -f1'));
	if ($found !== '' && is_file($found) && preg_match('/\\.(ttf|otf)$/i', $found)) {
		$ttfSrc = $found;
	}
}
if ($ttfSrc !== null) {
	$rel = '05_fonts/sample.ttf';
	wc_copy($ttfSrc, $dst . DIRECTORY_SEPARATOR . $rel);
	$note($rel, 'ttf', '05_fonts', 'font/ttf', 'system-font', basename($ttfSrc));
	// Also expose as otf if source is otf; else write a second copy named .otf only when otf
	if (preg_match('/\\.otf$/i', $ttfSrc)) {
		$relO = '05_fonts/sample.otf';
		wc_copy($ttfSrc, $dst . DIRECTORY_SEPARATOR . $relO);
		$note($relO, 'otf', '05_fonts', 'font/otf', 'system-font', basename($ttfSrc));
	} else {
		// Minimal SFNT-ish stub labeled otf when no real OTF (still exercises extension)
		$put('05_fonts/sample.otf', "OTTO\0\x01\0\0" . str_repeat("\0", 60), 'otf', '05_fonts', 'font/otf', 'synthetic', 'OTTO stub if no system OTF');
	}
} else {
	$skipped[] = 'ttf/otf (no system font)';
	$put('05_fonts/sample.ttf', "\0\x01\0\0" . str_repeat("\0", 60), 'ttf', '05_fonts', 'font/ttf', 'synthetic', 'sfnt stub');
	$put('05_fonts/sample.otf', "OTTO\0\x01\0\0" . str_repeat("\0", 60), 'otf', '05_fonts', 'font/otf', 'synthetic', 'OTTO stub');
}

// woff/woff2: prefer copying from existing corpus; else minimal signatures
$woff2Found = null;
$woffFound = null;
foreach (['test_files61', 'test_files57', 'test_files55', 'test_files69', 'test_files2'] as $fontCorp) {
	$fontRoot = $root . DIRECTORY_SEPARATOR . $fontCorp;
	if (!is_dir($fontRoot)) {
		continue;
	}
	$itFonts = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fontRoot, FilesystemIterator::SKIP_DOTS));
	foreach ($itFonts as $fi) {
		if (!$fi->isFile() || $fi->getSize() >= 200000) {
			continue;
		}
		$bn = $fi->getFilename();
		if ($woff2Found === null && preg_match('/\\.woff2$/i', $bn)) {
			$woff2Found = $fi->getPathname();
		}
		if ($woffFound === null && preg_match('/\\.woff$/i', $bn)) {
			$woffFound = $fi->getPathname();
		}
		if ($woff2Found && $woffFound) {
			break 2;
		}
	}
}
if ($woff2Found) {
	wc_copy($woff2Found, $dst . '/05_fonts/sample.woff2');
	$note('05_fonts/sample.woff2', 'woff2', '05_fonts', 'font/woff2', 'reuse-corpus', $woff2Found);
} else {
	$put('05_fonts/sample.woff2', 'wOF2' . str_repeat("\0", 40), 'woff2', '05_fonts', 'font/woff2', 'synthetic', 'wOF2 signature stub');
}
if ($woffFound) {
	wc_copy($woffFound, $dst . '/05_fonts/sample.woff');
	$note('05_fonts/sample.woff', 'woff', '05_fonts', 'font/woff', 'reuse-corpus', $woffFound);
} else {
	$put('05_fonts/sample.woff', 'wOFF' . str_repeat("\0", 40), 'woff', '05_fonts', 'font/woff', 'synthetic', 'wOFF signature stub');
}
// EOT stub (embedded opentype begins with little-endian length etc.)
$put('05_fonts/sample.eot', pack('V', 100) . str_repeat("\0", 96), 'eot', '05_fonts', 'application/vnd.ms-fontobject', 'synthetic', 'EOT-like stub');

// --- 06 images via ImageMagick / encoders ---
$pngPath = $dst . '/06_images/sample.png';
wc_mkdir(dirname($pngPath));
$magick = wc_which('magick') ?? wc_which('convert');
if ($magick !== null) {
	wc_run([$magick, '-size', '32x32', 'xc:#224466', '-fill', 'white', '-draw', 'circle 16,16 16,6', $pngPath]);
	$note('06_images/sample.png', 'png', '06_images', 'image/png', 'imagemagick');
	foreach ([
		['sample.jpg', 'jpg', 'image/jpeg'],
		['sample.jpeg', 'jpeg', 'image/jpeg'],
		['sample.gif', 'gif', 'image/gif'],
		['sample.bmp', 'bmp', 'image/bmp'],
		['sample.tif', 'tif', 'image/tiff'],
		['sample.tiff', 'tiff', 'image/tiff'],
		['sample.webp', 'webp', 'image/webp'],
		['sample.ico', 'ico', 'image/x-icon'],
	] as [$name, $ext, $mime]) {
		$out = $dst . '/06_images/' . $name;
		wc_run([$magick, $pngPath, $out]);
		$note('06_images/' . $name, $ext, '06_images', $mime, 'imagemagick');
	}
	// APNG: animated gif converted or two-frame png via magick
	$apng = $dst . '/06_images/sample.apng';
	try {
		wc_run([$magick, '-delay', '50', '-size', '32x32', 'xc:red', 'xc:blue', $apng]);
		$note('06_images/sample.apng', 'apng', '06_images', 'image/apng', 'imagemagick');
	} catch (Throwable $e) {
		wc_copy($pngPath, $apng);
		$note('06_images/sample.apng', 'apng', '06_images', 'image/apng', 'copy-png', 'apng encode failed; png bytes named .apng');
	}
} else {
	$skipped[] = 'raster images (no ImageMagick)';
}

if (wc_which('avifenc') && is_file($pngPath)) {
	$avif = $dst . '/06_images/sample.avif';
	try {
		wc_run(['avifenc', '--min', '20', '--max', '28', $pngPath, $avif]);
		$note('06_images/sample.avif', 'avif', '06_images', 'image/avif', 'avifenc');
	} catch (Throwable $e) {
		$skipped[] = 'avif: ' . $e->getMessage();
	}
} elseif (is_file($pngPath) && $magick) {
	try {
		wc_run([$magick, $pngPath, $dst . '/06_images/sample.avif']);
		$note('06_images/sample.avif', 'avif', '06_images', 'image/avif', 'imagemagick');
	} catch (Throwable $e) {
		$skipped[] = 'avif';
	}
}

if (wc_which('heif-enc') && is_file($pngPath)) {
	$heic = $dst . '/06_images/sample.heic';
	try {
		wc_run(['heif-enc', $pngPath, '-o', $heic]);
		$note('06_images/sample.heic', 'heic', '06_images', 'image/heic', 'heif-enc');
	} catch (Throwable $e) {
		$put('06_images/sample.heic', 'ftypheic' . str_repeat("\0", 32), 'heic', '06_images', 'image/heic', 'synthetic', 'heif-enc failed; ftyp stub');
	}
} else {
	$put('06_images/sample.heic', "\0\0\0\x18ftypheic\0\0\0\0heicmif1", 'heic', '06_images', 'image/heic', 'synthetic', 'ftyp heic stub');
}

// --- 07/08 audio + video via ffmpeg ---
$ffmpeg = wc_which('ffmpeg');
if ($ffmpeg !== null) {
	$wav = $dst . '/07_audio/sample.wav';
	wc_mkdir(dirname($wav));
	wc_run([
		$ffmpeg, '-y', '-f', 'lavfi', '-i', 'sine=frequency=440:duration=0.25',
		'-ac', '1', '-ar', '22050', $wav,
	]);
	$note('07_audio/sample.wav', 'wav', '07_audio', 'audio/wav', 'ffmpeg');

	$audioJobs = [
		['sample.mp3', 'mp3', 'audio/mpeg', ['-c:a', 'libmp3lame', '-b:a', '64k']],
		['sample.aac', 'aac', 'audio/aac', ['-c:a', 'aac', '-b:a', '64k']],
		['sample.m4a', 'm4a', 'audio/mp4', ['-c:a', 'aac', '-b:a', '64k']],
		['sample.ogg', 'ogg', 'audio/ogg', ['-c:a', 'libvorbis', '-q:a', '2']],
		['sample.opus', 'opus', 'audio/opus', ['-c:a', 'libopus', '-b:a', '32k']],
		['sample.flac', 'flac', 'audio/flac', ['-c:a', 'flac']],
		['sample.weba', 'weba', 'audio/webm', ['-c:a', 'libopus', '-b:a', '32k', '-f', 'webm']],
	];
	foreach ($audioJobs as [$name, $ext, $mime, $extra]) {
		$out = $dst . '/07_audio/' . $name;
		try {
			wc_run(array_merge([$ffmpeg, '-y', '-i', $wav], $extra, [$out]));
			$note('07_audio/' . $name, $ext, '07_audio', $mime, 'ffmpeg');
		} catch (Throwable $e) {
			$skipped[] = "audio {$ext}: " . $e->getMessage();
		}
	}

	$mp4 = $dst . '/08_video/sample.mp4';
	wc_mkdir(dirname($mp4));
	try {
		wc_run([
			$ffmpeg, '-y',
			'-f', 'lavfi', '-i', 'color=c=blue:s=64x48:d=0.5',
			'-f', 'lavfi', '-i', 'sine=frequency=440:duration=0.5',
			'-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-c:a', 'aac', '-shortest', $mp4,
		]);
		$note('08_video/sample.mp4', 'mp4', '08_video', 'video/mp4', 'ffmpeg');
	} catch (Throwable $e) {
		$skipped[] = 'mp4: ' . $e->getMessage();
	}

	$videoJobs = [
		['sample.webm', 'webm', 'video/webm', ['-c:v', 'libvpx', '-b:v', '100k', '-c:a', 'libvorbis']],
		['sample.mkv', 'mkv', 'video/x-matroska', ['-c:v', 'libx264', '-c:a', 'aac']],
		['sample.ogv', 'ogv', 'video/ogg', ['-c:v', 'libtheora', '-c:a', 'libvorbis']],
	];
	foreach ($videoJobs as [$name, $ext, $mime, $extra]) {
		$out = $dst . '/08_video/' . $name;
		try {
			wc_run(array_merge([
				$ffmpeg, '-y',
				'-f', 'lavfi', '-i', 'color=c=green:s=64x48:d=0.4',
				'-f', 'lavfi', '-i', 'sine=frequency=520:duration=0.4',
			], $extra, ['-shortest', $out]));
			$note('08_video/' . $name, $ext, '08_video', $mime, 'ffmpeg');
		} catch (Throwable $e) {
			$skipped[] = "video {$ext}";
		}
	}
} else {
	$skipped[] = 'audio/video (no ffmpeg)';
}

$put(
	'08_video/sample.m3u8',
	"#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-TARGETDURATION:1\n#EXTINF:0.5,\nsample.mp4\n#EXT-X-ENDLIST\n",
	'm3u8',
	'08_video',
	'application/vnd.apple.mpegurl',
	'synthetic'
);
$put(
	'08_video/sample.vtt',
	"WEBVTT\n\n00:00:00.000 --> 00:00:00.400\nweb census caption\n",
	'vtt',
	'08_video',
	'text/vtt',
	'synthetic'
);
$put(
	'08_video/sample.srt',
	"1\n00:00:00,000 --> 00:00:00,400\nweb census subtitle\n",
	'srt',
	'08_video',
	'application/x-subrip',
	'synthetic'
);

// --- 09 archives ---
$payload = "web-census-201 archive payload\n";
$payloadPath = $dst . '/09_archives/_payload.txt';
wc_write($payloadPath, $payload);

wc_zip_store($dst . '/09_archives/sample.zip', ['payload.txt' => $payload]);
$note('09_archives/sample.zip', 'zip', '09_archives', 'application/zip', 'php-zip');

wc_run(['tar', 'cf', $dst . '/09_archives/sample.tar', '-C', $dst . '/09_archives', '_payload.txt']);
$note('09_archives/sample.tar', 'tar', '09_archives', 'application/x-tar', 'tar');

$gzJobs = [
	['gzip', '-c', 'sample.gz', 'gz', 'application/gzip'],
];
foreach ($gzJobs as [$bin, $flag, $name, $ext, $mime]) {
	if (!wc_which($bin)) {
		$skipped[] = $ext;
		continue;
	}
	$out = $dst . '/09_archives/' . $name;
	$cmd = $bin . ' ' . $flag . ' ' . escapeshellarg($payloadPath) . ' > ' . escapeshellarg($out);
	exec($cmd, $o, $rc);
	if ($rc === 0 && is_file($out)) {
		$note('09_archives/' . $name, $ext, '09_archives', $mime, $bin);
	} else {
		$skipped[] = $ext;
	}
}
foreach ([
	['bzip2', '-c', 'sample.bz2', 'bz2', 'application/x-bzip2'],
	['xz', '-c', 'sample.xz', 'xz', 'application/x-xz'],
	['zstd', '-c', 'sample.zst', 'zst', 'application/zstd'],
	['brotli', '-c', 'sample.br', 'br', 'application/x-br'],
	['lz4', '-c', 'sample.lz4', 'lz4', 'application/x-lz4'],
] as [$bin, $flag, $name, $ext, $mime]) {
	if (!wc_which($bin)) {
		$skipped[] = $ext;
		continue;
	}
	$out = $dst . '/09_archives/' . $name;
	$cmd = escapeshellcmd($bin) . ' ' . $flag . ' ' . escapeshellarg($payloadPath) . ' > ' . escapeshellarg($out) . ' 2>/dev/null';
	exec($cmd, $o, $rc);
	if ($rc === 0 && is_file($out) && filesize($out) > 0) {
		$note('09_archives/' . $name, $ext, '09_archives', $mime, $bin);
	} else {
		$skipped[] = $ext;
	}
}
if (wc_which('7z')) {
	$seven = $dst . '/09_archives/sample.7z';
	@unlink($seven);
	try {
		wc_run(['7z', 'a', '-t7z', '-mx=1', '-bd', $seven, $payloadPath]);
		$note('09_archives/sample.7z', '7z', '09_archives', 'application/x-7z-compressed', '7z');
	} catch (Throwable $e) {
		$skipped[] = '7z';
	}
}
if (wc_which('gzip') && is_file($dst . '/09_archives/sample.tar')) {
	exec('gzip -c ' . escapeshellarg($dst . '/09_archives/sample.tar') . ' > ' . escapeshellarg($dst . '/09_archives/sample.tgz'), $o, $rc);
	if ($rc === 0) {
		$note('09_archives/sample.tgz', 'tgz', '09_archives', 'application/gzip', 'gzip');
	}
}
@unlink($payloadPath);

// --- 10 office / ebook ---
$ooxmlContentTypes = '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
	. '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
	. '<Default Extension="xml" ContentType="application/xml"/>'
	. '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
	. '</Types>';
$ooxmlRels = '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
	. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
	. '</Relationships>';
$ooxmlDoc = '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
	. '<w:body><w:p><w:r><w:t>web census</w:t></w:r></w:p></w:body></w:document>';
wc_zip_store($dst . '/10_office_ebook/sample.docx', [
	'[Content_Types].xml' => $ooxmlContentTypes,
	'_rels/.rels' => $ooxmlRels,
	'word/document.xml' => $ooxmlDoc,
]);
$note('10_office_ebook/sample.docx', 'docx', '10_office_ebook', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'ooxml-zip');

$xlsxTypes = '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
	. '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
	. '<Default Extension="xml" ContentType="application/xml"/>'
	. '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
	. '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
	. '</Types>';
$xlsxRels = '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
	. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
	. '</Relationships>';
$xlsxBook = '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
	. '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>';
$xlsxSheet = '<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'
	. '<row r="1"><c r="A1" t="inlineStr"><is><t>census</t></is></c></row></sheetData></worksheet>';
$xlsxBookRels = '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
	. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
	. '</Relationships>';
wc_zip_store($dst . '/10_office_ebook/sample.xlsx', [
	'[Content_Types].xml' => $xlsxTypes,
	'_rels/.rels' => $xlsxRels,
	'xl/workbook.xml' => $xlsxBook,
	'xl/_rels/workbook.xml.rels' => $xlsxBookRels,
	'xl/worksheets/sheet1.xml' => $xlsxSheet,
]);
$note('10_office_ebook/sample.xlsx', 'xlsx', '10_office_ebook', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'ooxml-zip');

$pptxTypes = '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
	. '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
	. '<Default Extension="xml" ContentType="application/xml"/>'
	. '<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>'
	. '</Types>';
$pptxRels = '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
	. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/>'
	. '</Relationships>';
$pptxPres = '<?xml version="1.0"?><p:presentation xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
	. '<p:sldIdLst/></p:presentation>';
wc_zip_store($dst . '/10_office_ebook/sample.pptx', [
	'[Content_Types].xml' => $pptxTypes,
	'_rels/.rels' => $pptxRels,
	'ppt/presentation.xml' => $pptxPres,
]);
$note('10_office_ebook/sample.pptx', 'pptx', '10_office_ebook', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'ooxml-zip');

$odtMime = 'application/vnd.oasis.opendocument.text';
$odtContent = '<?xml version="1.0"?><office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" office:version="1.2">'
	. '<office:body><office:text><text:p>web census</text:p></office:text></office:body></office:document-content>';
wc_zip_store($dst . '/10_office_ebook/sample.odt', [
	'mimetype' => $odtMime,
	'content.xml' => $odtContent,
	'META-INF/manifest.xml' => '<?xml version="1.0"?><manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">'
		. '<manifest:file-entry manifest:full-path="/" manifest:media-type="' . $odtMime . '"/>'
		. '<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>'
		. '</manifest:manifest>',
]);
$note('10_office_ebook/sample.odt', 'odt', '10_office_ebook', $odtMime, 'odf-zip');

$odsMime = 'application/vnd.oasis.opendocument.spreadsheet';
wc_zip_store($dst . '/10_office_ebook/sample.ods', [
	'mimetype' => $odsMime,
	'content.xml' => '<?xml version="1.0"?><office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" office:version="1.2">'
		. '<office:body><office:spreadsheet><table:table table:name="Sheet1"><table:table-row><table:table-cell>'
		. '<text:p>census</text:p></table:table-cell></table:table-row></table:table></office:spreadsheet></office:body></office:document-content>',
	'META-INF/manifest.xml' => '<?xml version="1.0"?><manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">'
		. '<manifest:file-entry manifest:full-path="/" manifest:media-type="' . $odsMime . '"/>'
		. '</manifest:manifest>',
]);
$note('10_office_ebook/sample.ods', 'ods', '10_office_ebook', $odsMime, 'odf-zip');

$epubMime = 'application/epub+zip';
wc_zip_store($dst . '/10_office_ebook/sample.epub', [
	'mimetype' => $epubMime,
	'META-INF/container.xml' => '<?xml version="1.0"?><container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">'
		. '<rootfiles><rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/></rootfiles></container>',
	'OEBPS/content.opf' => '<?xml version="1.0"?><package xmlns="http://www.idpf.org/2007/opf" unique-identifier="u" version="3.0">'
		. '<metadata xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:identifier id="u">census-201</dc:identifier>'
		. '<dc:title>Web Census</dc:title><dc:language>en</dc:language></metadata>'
		. '<manifest><item id="n" href="nav.xhtml" media-type="application/xhtml+xml" properties="nav"/></manifest>'
		. '<spine><itemref idref="n"/></spine></package>',
	'OEBPS/nav.xhtml' => '<?xml version="1.0"?><html xmlns="http://www.w3.org/1999/xhtml"><head><title>n</title></head>'
		. '<body><nav epub:type="toc" xmlns:epub="http://www.idpf.org/2007/ops"><ol><li>census</li></ol></nav></body></html>',
]);
$note('10_office_ebook/sample.epub', 'epub', '10_office_ebook', $epubMime, 'epub-zip');
// MOBI: PalmDOC-ish stub with BOOKMOBI
$put('10_office_ebook/sample.mobi', 'BOOKMOBI' . str_repeat("\0", 64) . 'web census mobi stub', 'mobi', '10_office_ebook', 'application/x-mobipocket-ebook', 'synthetic', 'BOOKMOBI stub');

// --- 11 code ---
$codeSamples = [
	['sample.php', 'php', 'text/x-php', "<?php\necho \"web-census-201\\n\";\n"],
	['sample.py', 'py', 'text/x-python', "def main():\n    print('web-census-201')\n\nif __name__ == '__main__':\n    main()\n"],
	['sample.go', 'go', 'text/x-go', "package main\nimport \"fmt\"\nfunc main(){fmt.Println(\"web-census-201\")}\n"],
	['sample.rs', 'rs', 'text/x-rust', "fn main(){println!(\"web-census-201\");}\n"],
	['sample.java', 'java', 'text/x-java', "class Census { public static void main(String[] a){ System.out.println(\"web-census-201\"); } }\n"],
	['sample.c', 'c', 'text/x-c', "#include <stdio.h>\nint main(void){puts(\"web-census-201\");return 0;}\n"],
	['sample.h', 'h', 'text/x-c', "#pragma once\n#define WEB_CENSUS_ID 201\n"],
	['sample.cpp', 'cpp', 'text/x-c++', "#include <iostream>\nint main(){std::cout<<\"web-census-201\\n\";}\n"],
	['sample.rb', 'rb', 'text/x-ruby', "puts 'web-census-201'\n"],
	['sample.swift', 'swift', 'text/x-swift', "print(\"web-census-201\")\n"],
	['sample.kt', 'kt', 'text/x-kotlin', "fun main(){println(\"web-census-201\")}\n"],
	['sample.sql', 'sql', 'application/sql', "CREATE TABLE census(id INT PRIMARY KEY, name TEXT);\nINSERT INTO census VALUES (201, 'web');\n"],
];
foreach ($codeSamples as [$name, $ext, $mime, $body]) {
	$put('11_code/' . $name, $body, $ext, '11_code', $mime, 'synthetic');
}

// --- 12 databases ---
if (wc_which('sqlite3')) {
	$db = $dst . '/12_databases/sample.sqlite';
	wc_mkdir(dirname($db));
	@unlink($db);
	exec('sqlite3 ' . escapeshellarg($db) . ' ' . escapeshellarg("CREATE TABLE t(id INTEGER); INSERT INTO t VALUES (201);"), $o, $rc);
	if ($rc === 0 && is_file($db)) {
		$note('12_databases/sample.sqlite', 'sqlite', '12_databases', 'application/vnd.sqlite3', 'sqlite3');
		wc_copy($db, $dst . '/12_databases/sample.db');
		$note('12_databases/sample.db', 'db', '12_databases', 'application/vnd.sqlite3', 'sqlite3-copy');
	}
} else {
	$put('12_databases/sample.sqlite', "SQLite format 3\0" . str_repeat("\0", 80), 'sqlite', '12_databases', 'application/vnd.sqlite3', 'synthetic', 'header stub');
	$put('12_databases/sample.db', "SQLite format 3\0" . str_repeat("\0", 80), 'db', '12_databases', 'application/vnd.sqlite3', 'synthetic', 'header stub');
}
// Jet MDB stub
$put('12_databases/sample.mdb', "\0\x01\0\0Standard Jet DB" . str_repeat("\0", 32), 'mdb', '12_databases', 'application/x-msaccess', 'synthetic', 'Jet DB stub');

// --- 13 ML ---
$put('13_ml_tensors/sample.onnx', "\x08\x07\x12\x0cweb-census-201", 'onnx', '13_ml_tensors', 'application/octet-stream', 'synthetic', 'onnx protobuf-ish stub');
// GGUF v3 minimal: magic GGUF + version + counts
$put('13_ml_tensors/sample.gguf', 'GGUF' . pack('V', 3) . pack('P', 0) . pack('P', 0), 'gguf', '13_ml_tensors', 'application/octet-stream', 'synthetic', 'GGUF header');
// safetensors: JSON header length + header + empty
$stHeader = '{"__metadata__":{"format":"web-census"}}';
$put('13_ml_tensors/sample.safetensors', pack('P', strlen($stHeader)) . $stHeader, 'safetensors', '13_ml_tensors', 'application/octet-stream', 'synthetic');
// npy: magic + version + header dict for empty float64 array shape (0,)
$npyHeader = "{'descr': '<f8', 'fortran_order': False, 'shape': (0,), }";
$npyHeader = str_pad($npyHeader, 128 - 10, ' ', STR_PAD_RIGHT);
$npy = "\x93NUMPY\x01\0" . pack('v', strlen($npyHeader)) . $npyHeader;
$put('13_ml_tensors/sample.npy', $npy, 'npy', '13_ml_tensors', 'application/octet-stream', 'synthetic');
// PyTorch zip pickle stub (.pt often zip)
wc_zip_store($dst . '/13_ml_tensors/sample.pt', [
	'web_census.txt' => "pytorch-zip-stub\n",
]);
$note('13_ml_tensors/sample.pt', 'pt', '13_ml_tensors', 'application/octet-stream', 'zip-stub', 'zip container common for torch.save');

// --- 14 mail / net ---
$put(
	'14_mail_net/sample.eml',
	"From: census@example.com\r\nTo: you@example.com\r\nSubject: web census\r\nMIME-Version: 1.0\r\n"
	. "Content-Type: text/plain; charset=utf-8\r\n\r\nHello from test_files201.\r\n",
	'eml',
	'14_mail_net',
	'message/rfc822',
	'synthetic'
);
$put(
	'14_mail_net/sample.mbox',
	"From census@example.com Thu Jan 01 00:00:00 2026\n"
	. "From: census@example.com\nTo: you@example.com\nSubject: mbox\n\nbody\n",
	'mbox',
	'14_mail_net',
	'application/mbox',
	'synthetic'
);
// PCAP global header (little-endian magic)
$pcap = pack('V', 0xa1b2c3d4) . pack('v', 2) . pack('v', 4) . pack('V', 0) . pack('V', 0) . pack('V', 65535) . pack('V', 1);
$put('14_mail_net/sample.pcap', $pcap, 'pcap', '14_mail_net', 'application/vnd.tcpdump.pcap', 'synthetic', 'empty pcap');
// BitTorrent metainfo (bencode)
$torrent = 'd8:announce15:http://a.example4:infod6:lengthi11e4:name11:payload.txt12:piece lengthi16384e6:pieces20:'
	. str_repeat('a', 20) . 'ee';
$put('14_mail_net/sample.torrent', $torrent, 'torrent', '14_mail_net', 'application/x-bittorrent', 'synthetic');

// --- 15 3D ---
$put(
	'15_3d_cad/sample.stl',
	"solid census\n facet normal 0 0 1\n  outer loop\n   vertex 0 0 0\n   vertex 1 0 0\n   vertex 0 1 0\n  endloop\n endfacet\nendsolid census\n",
	'stl',
	'15_3d_cad',
	'model/stl',
	'synthetic'
);
$put(
	'15_3d_cad/sample.obj',
	"o census\nv 0 0 0\nv 1 0 0\nv 0 1 0\nf 1 2 3\n",
	'obj',
	'15_3d_cad',
	'model/obj',
	'synthetic'
);
$gltf = [
	'asset' => ['version' => '2.0', 'generator' => 'test_files201'],
	'scene' => 0,
	'scenes' => [['nodes' => [0]]],
	'nodes' => [['name' => 'census']],
];
$put('15_3d_cad/sample.gltf', json_encode($gltf, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n", 'gltf', '15_3d_cad', 'model/gltf+json', 'synthetic');
// GLB: 12-byte header + JSON chunk
$gltfJson = json_encode(['asset' => ['version' => '2.0']], JSON_UNESCAPED_SLASHES);
$gltfJsonPad = $gltfJson . str_repeat(' ', (4 - (strlen($gltfJson) % 4)) % 4);
$glb = 'glTF' . pack('V', 2) . pack('V', 12 + 8 + strlen($gltfJsonPad))
	. pack('V', strlen($gltfJsonPad)) . pack('V', 0x4e4f534a) . $gltfJsonPad;
$put('15_3d_cad/sample.glb', $glb, 'glb', '15_3d_cad', 'model/gltf-binary', 'synthetic');
wc_zip_store($dst . '/15_3d_cad/sample.usdz', [
	'model.gltf' => json_encode($gltf, JSON_UNESCAPED_SLASHES),
]);
$note('15_3d_cad/sample.usdz', 'usdz', '15_3d_cad', 'model/vnd.usdz+zip', 'zip-stub');

// --- 16 mobile ---
wc_zip_store($dst . '/16_mobile_pkg/sample.apk', [
	'AndroidManifest.xml' => "<?xml version=\"1.0\"?><manifest package=\"com.example.census\"/>",
	'classes.dex' => "dex\n035\0" . str_repeat("\0", 32),
	'resources.arsc' => str_repeat("\0", 16),
]);
$note('16_mobile_pkg/sample.apk', 'apk', '16_mobile_pkg', 'application/vnd.android.package-archive', 'zip-stub');
wc_zip_store($dst . '/16_mobile_pkg/sample.ipa', [
	'Payload/Census.app/Info.plist' => "<?xml version=\"1.0\"?><plist version=\"1.0\"><dict><key>CFBundleIdentifier</key><string>com.example.census</string></dict></plist>",
]);
$note('16_mobile_pkg/sample.ipa', 'ipa', '16_mobile_pkg', 'application/octet-stream', 'zip-stub');
wc_zip_store($dst . '/16_mobile_pkg/sample.xapk', [
	'manifest.json' => "{\"package_name\":\"com.example.census\",\"split_apks\":[]}\n",
	'census.apk' => "PK stub\n",
]);
$note('16_mobile_pkg/sample.xapk', 'xapk', '16_mobile_pkg', 'application/octet-stream', 'zip-stub');

// --- 17 misc web ---
$put(
	'17_misc_web/sample.ics',
	"BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\nUID:census-201\r\nDTSTAMP:20260101T000000Z\r\n"
	. "DTSTART:20260101T000000Z\r\nSUMMARY:Web Census\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n",
	'ics',
	'17_misc_web',
	'text/calendar',
	'synthetic'
);
$put(
	'17_misc_web/sample.vcf',
	"BEGIN:VCARD\r\nVERSION:3.0\r\nFN:Web Census\r\nEMAIL:census@example.com\r\nEND:VCARD\r\n",
	'vcf',
	'17_misc_web',
	'text/vcard',
	'synthetic'
);
$put('17_misc_web/torrent_magnet.txt', "magnet:?xt=urn:btih:" . str_repeat('a', 40) . "&dn=web-census-201\n", 'txt', '17_misc_web', 'text/plain', 'synthetic', 'magnet link text');
$put('17_misc_web/robots.txt', "User-agent: *\nAllow: /\nSitemap: https://example.com/sitemap.xml\n", 'txt', '17_misc_web', 'text/plain', 'synthetic');
$put(
	'17_misc_web/sitemap.xml',
	"<?xml version=\"1.0\"?><urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">"
	. "<url><loc>https://example.com/</loc></url></urlset>\n",
	'xml',
	'17_misc_web',
	'application/xml',
	'synthetic'
);
$put('17_misc_web/humans.txt', "/* TEAM */\nCoder: fractal_zip web census\n", 'txt', '17_misc_web', 'text/plain', 'synthetic');
$put(
	'17_misc_web/security.txt',
	"Contact: mailto:security@example.com\nPreferred-Languages: en\n",
	'txt',
	'17_misc_web',
	'text/plain',
	'synthetic'
);

// README + MANIFEST
$readme = <<<'TXT'
test_files201 — web census corpus
=================================

Stratified miniature fixtures for major public-web content classes (documents,
markup, scripts/WASM, data interchange, fonts, images, audio/video, archives,
office/ebook, source code, databases, ML tensor wrappers, mail/net, 3D, mobile
packages, and common well-known files).

This is a *census of types*, not a traffic-weighted Common Crawl sample: each
slot is a small synthetic or tool-generated file so encode/peel paths see the
extension/magic, not a multi-GB distribution clone.

Rebuild: php benchmarks/build_test_files201_web_census.php --force
Coverage: php benchmarks/report_web_census_coverage.php
TXT;
wc_write($dst . '/00_README_web_census.txt', $readme);
$note('00_README_web_census.txt', 'txt', '00_meta', 'text/plain', 'synthetic');

// Size check / trim note
$total = 0;
$fileCount = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dst, FilesystemIterator::SKIP_DOTS));
foreach ($it as $fi) {
	if ($fi->isFile()) {
		$total += (int) $fi->getSize();
		$fileCount++;
	}
}

$manifest = [
	'name' => 'test_files201',
	'title' => 'web census',
	'built_at' => gmdate('c'),
	'total_bytes' => $total,
	'file_count' => $fileCount,
	'max_budget_bytes' => $maxBytes,
	'skipped' => $skipped,
	'slots' => wc_census_slots(),
	'files' => $manifestRows,
];
wc_write($dst . '/MANIFEST.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "Built test_files201: files={$fileCount} bytes={$total} (" . round($total / 1048576, 2) . " MiB)\n";
if ($total > $maxBytes) {
	fwrite(STDERR, "WARNING: exceeds {$maxBytes} byte budget\n");
}
if ($skipped !== []) {
	echo "Skipped/partial (" . count($skipped) . "):\n";
	foreach (array_slice($skipped, 0, 20) as $s) {
		echo "  - {$s}\n";
	}
}
echo "Coverage: php benchmarks/report_web_census_coverage.php\n";
