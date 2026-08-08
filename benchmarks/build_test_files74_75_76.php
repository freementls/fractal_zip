#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Build three small “lifestyle” folder corpora for run_benchmarks.php:
 *
 *  - test_files74 — **Desktop**: notes, CSV, JSON configs, local logs, shell snippet, vector graphic.
 *  - test_files75 — **Web / internet**: page shell, CSS, JS “bundle”, API JSON, sitemap, service worker.
 *  - test_files76 — **Phone / mobile**: VCF export, chat-style log, XMP, GPX, m3u, iOS-style plist, tiny screenshot PNG, optional jpg if source exists.
 *
 * No network; all content is synthetic. Run from repo root:
 *   php benchmarks/build_test_files74_75_76.php
 */
$root = dirname(__DIR__);
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
require_once __DIR__ . '/lifestyle_raster_b64.php';
$lr = lifestyle_raster_blobs();
$png1x1 = $lr['png'];
$jpgTiny = $lr['jpg'];
function put(string $base, string $rel, string $bytes): void
{
	$p = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
	$dir = dirname($p);
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}
	if (file_put_contents($p, $bytes) === false) {
		throw new RuntimeException('write failed: ' . $p);
	}
}
function rmtree(string $dir): void
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
function build_web_repeated(string $noun, int $repeat): string
{
	$chunk = "(()=>{const t={a:" . str_repeat('1', 80) . ",b:[";
	for ($i = 0; $i < 24; $i++) {
		$chunk .= "{id:{$i},h:'" . $noun . "-{$i}',m:function(){return this.h.length}},";
	}
	$chunk .= "]};return t;})()";
	return str_repeat("/*c*/" . $chunk . ";\n", $repeat);
}

$desktopReadme = <<<'TXT'
test_files74 — “desktop work” style corpus (synthetic):
Documents, exports, app JSON, build logs, shell helpers, a tiny SVG. Typical of local PC/Mac/Linux workflows: edit + save + export to CSV + sync notes.
TXT;

$meeting = <<<'TXT'
# Standup 2026-04-22

- Blocked: waiting on API keys for staging (ETA tomorrow).
- Done: refactored backup script; added crash logs to /tmp.
- Next: run spreadsheet macros on Q1 numbers + attach PDF to drive folder.

# Parking lot
* Calendar invites for all-hands (2 reminders).
* Replace legacy fonts in the slide master.
TXT;

$csvQ1 = "sku,qty,unit_cents,region\n" .
	"A12-0,3,1999,US-W\n" .
	"A12-1,1,1999,US-W\n" .
	"B7-x,10,150,EU-N\n" .
	"B7-x,4,150,APAC\n" .
	"C-zip-9,1,0,US-E\n" .
	"(more rows in real life — header repeated for import compatibility)\n";

$editorConfig = <<<'JSON'
{
  "theme": "Gruvbox",
  "tabSize": 4,
  "insertSpaces": true,
  "rulers": [100, 120],
  "files.associations": { "*.fz": "text" },
  "search.followSymlinks": false,
  "python.analysis.stubPath": "./.stubs"
}
JSON;

$buildLog = '';
for ($d = 1; $d <= 12; $d++) {
	$buildLog .= date('Y-m-d') . " 08:0{$d}:00.000 build step {$d} OK  duration=" . (0.1 * $d) . "s\n";
}
$buildLog .= "[linter] 0 errors, 3 style warnings in legacy_import.py (ignored)\n";

$ps1 = <<<'PS1'
# Set proxy for this session only (user copied from IT wiki)
$env:HTTP_PROXY  = "http://proxy.corp.local:8080"
$env:HTTPS_PROXY = "http://proxy.corp.local:8080"
$env:NO_PROXY    = "localhost,127.0.0.1,*.internal"
Get-ChildItem -Path $HOME\Documents -Recurse -Filter *.csv | ForEach-Object { $_.Length }
PS1;

$svgIcon = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64" aria-hidden="true">
  <rect x="4" y="4" width="56" height="56" rx="8" fill="#1e1e1e" stroke="#ccc" stroke-width="2"/>
  <text x="32" y="38" font-size="20" text-anchor="middle" fill="#eaeaea" font-family="ui-sans-serif,system-ui">D</text>
</svg>
SVG;

$manifest = '{"name":"side-project","version":"0.4.2","files":["index.html","assets/"],"hashAlgo":"sha256","entries":{}}' . "\n";
$downloadedReadme = "File saved from browser — checksum not verified. Double-click to open in default app.\n";

$dst74 = $root . '/test_files74';
rmtree($dst74);
mkdir($dst74, 0755, true);
put($dst74, '00_README_corpus.txt', $desktopReadme);
put($dst74, 'Documents/notes/standup_2026-04-22.md', $meeting);
put($dst74, 'Documents/spreadsheets/q1_copy.csv', $csvQ1);
put($dst74, 'AppData/Editor/prefs.json', $editorConfig);
put($dst74, 'Projects/backup_server/build_2026Q2.log', $buildLog);
put($dst74, 'Scripts/session_proxy.ps1', $ps1);
put($dst74, 'Pictures/Icons/folder_d.svg', $svgIcon);
put($dst74, 'Downloads/invoice_1042_readme.txt', $downloadedReadme);
put($dst74, 'Projects/web/dist/.vite/manifest.json', $manifest);

$webReadme = <<<'TXT'
test_files75 — “web / internet” style corpus (synthetic):
A tiny static “site” tree: HTML shell, minified-style JS chunks, JSON API, robots + sitemap, service worker. Typical of a saved page, offline mirror, or CI artifact from `npm run build`.
TXT;

$html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Read later — example article</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/chunk-7a1k.css" />
  <link rel="alternate" type="application/rss+xml" href="/api/feed.json" />
  <script defer src="assets/bundle.app.js"></script>
</head>
<body>
  <header><h1>From the field: compression on real folders</h1></header>
  <main>
    <p>Paragraph repeated in saved pages. <span data-test="hydrate">(hydration off)</span></p>
    <p>Ad slots and social widgets are stripped; comments thread collapsed.</p>
  </main>
  <footer><a href="https://example.invalid/privacy">Privacy</a> · <a href="/sitemap.xml">Sitemap</a></footer>
</body>
</html>
HTML;

$css = "body{margin:0;font:16px/1.4 system-ui, sans-serif;max-width:42rem;padding:1rem;}\n" .
	"h1{font-size:1.5rem;}\n" .
	"[data-test]{color:#0a0;}\n" .
	'footer a{color:#15c;}' . "\n" . str_repeat("/*c*/.x" . str_repeat("9", 40) . "{display:block}\n", 20);

$js = "\"use strict\";\n" . build_web_repeated('mod', 28);

$searchJsonP = bench_json_encode_try(
	[
		'query' => 'lossless',
		'count' => 2,
		'items' => [
			['id' => 'a1', 'title' => 'Benchmark notes', 'url' => 'https://example.invalid/a1'],
			['id' => 'b2', 'title' => 'Archive format comparison', 'url' => 'https://example.invalid/b2'],
		],
		'ms' => 8,
	],
	true
);
if ($searchJsonP === null) {
	throw new RuntimeException('json_encode api/feed.json fixture: ' . json_last_error_msg());
}
$searchJson = $searchJsonP . "\n";

$robots = "User-agent: *\nDisallow: /private/\nCrawl-delay: 1\nSitemap: /sitemap.xml\n";

$sitemap = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc>https://example.invalid/</loc><lastmod>2026-04-24</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>
  <url><loc>https://example.invalid/article/compression</loc><lastmod>2026-04-20</lastmod><changefreq>monthly</changefreq><priority>0.5</priority></url>
</urlset>
XML;

$sw = <<<'JS'
// offline fallback — typical precache list from a PWA
const PRECACHE = "precache-v3";
self.addEventListener("install", (e) => {
  e.waitUntil(
    caches.open(PRECACHE).then((c) => c.addAll([
      "/assets/chunk-7a1k.css",
      "/assets/bundle.app.js"
    ]))
  );
  self.skipWaiting();
});
self.addEventListener("activate", (e) => e.waitUntil(self.clients.claim()));
self.addEventListener("fetch", (e) => {
  e.respondWith(
    fetch(e.request).catch(() => caches.match(e.request))
  );
});
JS;

$dst75 = $root . '/test_files75';
rmtree($dst75);
mkdir($dst75, 0755, true);
put($dst75, '00_README_corpus.txt', $webReadme);
put($dst75, 'cdn-mirror/index.html', $html);
put($dst75, 'cdn-mirror/assets/chunk-7a1k.css', $css);
put($dst75, 'cdn-mirror/assets/bundle.app.js', $js);
put($dst75, 'cdn-mirror/api/search.json', $searchJson);
put($dst75, 'cdn-mirror/robots.txt', $robots);
put($dst75, 'cdn-mirror/sitemap.xml', $sitemap);
put($dst75, 'service-worker/precache_v2.js', $sw);

$phoneReadme = <<<'TXT'
test_files76 — “phone / mobile” style corpus (synthetic):
Address-book export, chat log, XMP for a photo, GPX for a run, m3u playlist, plist-style prefs, a tiny screen PNG, and a minimal JPEG. Typical of what accumulates in DCIM, exports, and messaging backups.
TXT;

$vcf = <<<'VCF'
BEGIN:VCARD
VERSION:3.0
FN:Test User One
N:One;Test;;;
TEL;TYPE=CELL:(555) 010-0199
EMAIL;TYPE=INTERNET:test1@example.invalid
END:VCARD
BEGIN:VCARD
VERSION:3.0
N:User;Test Two;;;
FN:Test User Two
TEL;TYPE=HOME:555-0102
item1.EMAIL;TYPE=INTERNET:two@example.invalid
item1.X-ABLabel:work
END:VCARD
VCF;

$chat = <<<'TXT'
[10:01] +15550199: are we still on for 6?
[10:02] you: yep, grabbing coffee first
[10:03] +15550199: k — pin is 4829 for the building (side door)
[10:05] you: thx, omw
[10:22] System: 2 new photos backed up to cloud.
TXT;

$xmp = <<<'XMP'
<x:xmpmeta xmlns:x="adobe:ns:meta/">
  <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
    <rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">
      <dc:title>Beach (wire)</dc:title>
      <dc:creator>Camera app — synthetic</dc:creator>
    </rdf:Description>
  </rdf:RDF>
</x:xmpmeta>
XMP;

$gpx = <<<'GPX'
<?xml version="1.0" encoding="UTF-8"?>
<gpx version="1.1" creator="bench">
  <trk><name>morning</name>
    <trkseg>
      <trkpt lat="40.0" lon="-75.0"><time>2026-04-24T12:00:00Z</time><ele>12</ele></trkpt>
      <trkpt lat="40.0001" lon="-75.0001"><time>2026-04-24T12:00:20Z</time><ele>13</ele></trkpt>
    </trkseg>
  </trk>
</gpx>
GPX;

$m3u = <<<'M3U'
#EXTM3U
#EXTINF:123,Ringtone A
/ring/ring_a.m4a
#EXTINF:200,Notification B
/ring/notify_b.m4a
M3U;

$iosPlist = <<<'PL'
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>com.apple.something.enabled</key>
	<true/>
	<key>fractal_zip</key>
	<string>optional string value</string>
	<key>int_example</key>
	<integer>64</integer>
</dict>
</plist>
PL;

$dst76 = $root . '/test_files76';
rmtree($dst76);
mkdir($dst76, 0755, true);
put($dst76, '00_README_corpus.txt', $phoneReadme);
put($dst76, 'export/contacts_2026.vcf', $vcf);
put($dst76, 'Messages/backup/SMS_export_sample.txt', $chat);
put($dst76, 'DCIM/Camera/202604/sidecar_20260423_001.xmp', $xmp);
put($dst76, 'Android/media/com.bench.app/files/clip_2026_04_24_001.gpx', $gpx);
put($dst76, 'Music/ringtones/list.m3u', $m3u);
put($dst76, 'iOS/Preferences/Example.plist', $iosPlist);
if ($png1x1 !== '') {
	put($dst76, 'DCIM/Screenshots/Screenshot_20260424-100001.png', $png1x1);
}
$jpgCandidates = [
	$root . '/test_files61/01_raster_formats/grid_01.jpg',
	$root . '/test_files69/05_raster_multi_format/grid_01.jpg',
];
$jpgWrote = false;
foreach ($jpgCandidates as $jp) {
	if (!is_file($jp)) {
		continue;
	}
	$blob = @file_get_contents($jp);
	if (is_string($blob) && $blob !== '') {
		put($dst76, 'DCIM/Camera/IMG_20260424_100002.jpg', $blob);
		$jpgWrote = true;
	}
	break;
}
if (!$jpgWrote) {
	if ($jpgTiny !== '') {
		put($dst76, 'DCIM/Camera/IMG_20260424_100002.jpg', $jpgTiny);
	} else {
		fwrite(STDERR, "test_files76: no JPEG bytes (embedded decode failed; optional grid_01.jpg not in repo).\n");
	}
}

$bytes = function (string $d): int {
	$n = 0;
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($d, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $f) {
		$n += $f->isFile() ? (int) $f->getSize() : 0;
	}
	return $n;
};

fwrite(
	STDOUT,
	"Wrote test_files74 (raw {$bytes($dst74)} B), test_files75 ({$bytes($dst75)} B), test_files76 ({$bytes($dst76)} B)\n" .
		"  php benchmarks/run_benchmarks.php --only=test_files74,test_files75,test_files76 --json\n"
);
