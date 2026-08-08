#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_hybrid_peel`: synthetic Power Platform / hybrid-container corpus
 * for peel list-lane + fractal_zip ratio smoke benchmarks.
 *
 *   php benchmarks/build_test_files_hybrid_peel.php
 *   php benchmarks/build_test_files_hybrid_peel.php --dry-run
 *   rm -rf test_files_hybrid_peel && php benchmarks/build_test_files_hybrid_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$peelFixtures = realpath($repo . '/../peel/tests/php/fixtures.php');
if ($peelFixtures === false || !is_readable($peelFixtures)) {
	fwrite(STDERR, "Missing peel fixtures: {$repo}/../peel/tests/php/fixtures.php\n");
	exit(1);
}
require_once $peelFixtures;

$outName = 'test_files_hybrid_peel';
$dryRun = false;
for ($i = 1; $i < $argc; $i++) {
	if ($argv[$i] === '--dry-run') {
		$dryRun = true;
	} elseif (preg_match('/^--out=(\w+)$/', $argv[$i], $m)) {
		$outName = (string) $m[1];
	}
}

$dest = $repo . DIRECTORY_SEPARATOR . $outName;
if (!$dryRun && is_dir($dest)) {
	foreach (scandir($dest) ?: [] as $e) {
		if ($e === '.' || $e === '..') {
			continue;
		}
		$p = $dest . DIRECTORY_SEPARATOR . $e;
		is_dir($p) ? bench_hybrid_rmdir($p) : @unlink($p);
	}
} elseif (!$dryRun && !is_dir($dest)) {
	mkdir($dest, 0755, true);
}

$buildDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hybrid_peel_build_' . getmypid();
mkdir($buildDir, 0755, true);

$files = [
	'report.pbix' => peel_test_fixture_pbix($buildDir),
	'template.pbit' => (static function () use ($buildDir): string {
		$path = $buildDir . '/template.pbit';
		peel_test_make_zip($path, [
			'Version' => '1.0',
			'Metadata' => '{}',
			'Report/definition/pages/p1/page.json' => '{}',
		]);
		return $path;
	})(),
	'canvas.msapp' => peel_test_fixture_msapp($buildDir),
	'solution.zip' => peel_test_fixture_solution_zip($buildDir),
	'notes.docx' => (static function () use ($buildDir): string {
		$path = $buildDir . '/notes.docx';
		peel_test_make_zip($path, [
			'[Content_Types].xml' => '<?xml version="1.0"?><Types></Types>',
			'word/document.xml' => '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Hello</w:t></w:r></w:p></w:body></w:document>',
			'word/_rels/document.xml.rels' => '<?xml version="1.0"?><Relationships></Relationships>',
		]);
		return $path;
	})(),
	'book.epub' => (static function () use ($buildDir): string {
		$path = $buildDir . '/book.epub';
		peel_test_make_zip($path, [
			'mimetype' => 'application/epub+zip',
			'META-INF/container.xml' => '<?xml version="1.0"?><container></container>',
			'OEBPS/chapter1.xhtml' => '<html><body><p>Chapter</p></body></html>',
		]);
		return $path;
	})(),
	'analysis.ipynb' => peel_test_fixture_ipynb($buildDir),
	'message.eml' => peel_test_fixture_eml($buildDir),
	'app.apk' => (static function () use ($buildDir): string {
		$path = $buildDir . '/app.apk';
		peel_test_make_zip($path, [
			'AndroidManifest.xml' => '<manifest package="com.example.app"></manifest>',
			'classes.dex' => str_repeat("\x00", 4096),
			'resources.arsc' => str_repeat("\x01", 1024),
		]);
		return $path;
	})(),
	'pkg.whl' => (static function () use ($buildDir): string {
		$path = $buildDir . '/pkg.whl';
		peel_test_make_zip($path, [
			'pkg-1.0.dist-info/METADATA' => "Name: pkg\nVersion: 1.0\n",
			'pkg/__init__.py' => '# wheel\n',
		]);
		return $path;
	})(),
	'ext.xpi' => (static function () use ($buildDir): string {
		$path = $buildDir . '/ext.xpi';
		peel_test_make_zip($path, [
			'manifest.json' => '{"manifest_version":2,"name":"t","version":"1.0"}',
			'chrome.manifest' => 'content t chrome/\n',
		]);
		return $path;
	})(),
	'app.ipa' => (static function () use ($buildDir): string {
		$path = $buildDir . '/app.ipa';
		peel_test_make_zip($path, [
			'Payload/App.app/Info.plist' => '<?xml version="1.0"?><plist></plist>',
			'Payload/App.app/PkgInfo' => 'APPL????',
		]);
		return $path;
	})(),
	'dash.qvf' => (static function () use ($buildDir): string {
		$path = $buildDir . '/dash.qvf';
		peel_test_make_zip($path, [
			'AppContentMetadata.json' => '{}',
			'LoadModel/loadscript.txt' => 'LOAD * FROM data;',
		]);
		return $path;
	})(),
	'layer.ora' => (static function () use ($buildDir): string {
		$path = $buildDir . '/layer.ora';
		peel_test_make_zip($path, [
			'mimetype' => 'image/openraster',
			'stack.xml' => '<image><stack/></image>',
			'data/layer1.png' => str_repeat("\x89PNG", 32),
			'__MACOSX/._junk' => 'x',
		]);
		return $path;
	})(),
	'model.usdz' => (static function () use ($buildDir): string {
		$path = $buildDir . '/model.usdz';
		peel_test_make_zip($path, [
			'model.usda' => '#usda 1.0\n',
			'0/model.usda' => '#usda 1.0\n(\ndef Xform "root" {}\n)\n',
		]);
		return $path;
	})(),
	'scene.gltf' => (static function () use ($buildDir): string {
		$path = $buildDir . '/scene.gltf';
		file_put_contents($path, json_encode([
			'asset' => ['version' => '2.0'],
			'scenes' => [['nodes' => [0]]],
			'nodes' => [['mesh' => 0]],
			'meshes' => [['primitives' => []]],
		]));
		return $path;
	})(),
	'rows.csv' => (static function () use ($buildDir): string {
		$path = $buildDir . '/rows.csv';
		$lines = ["id,name,score"];
		for ($i = 1; $i <= 80; $i++) {
			$lines[] = $i . ',item' . $i . ',' . ($i * 3);
		}
		file_put_contents($path, implode("\n", $lines) . "\n");
		return $path;
	})(),
	'cfg.ini' => (static function () use ($buildDir): string {
		$path = $buildDir . '/cfg.ini';
		file_put_contents($path, "[main]\nname=peel\n\n[db]\nhost=localhost\nport=5432\n");
		return $path;
	})(),
	'sample.tar' => (static function () use ($buildDir): string {
		$path = $buildDir . '/sample.tar';
		// Minimal ustar with two small files via system tar if available.
		$dir = $buildDir . '/tar_src';
		@mkdir($dir, 0755, true);
		file_put_contents($dir . '/a.txt', "alpha\n");
		file_put_contents($dir . '/b.txt', "bravo\n");
		$tarBin = trim((string) shell_exec('command -v tar 2>/dev/null'));
		if ($tarBin !== '') {
			$cmd = 'cd ' . escapeshellarg($dir) . ' && ' . escapeshellarg($tarBin)
				. ' -cf ' . escapeshellarg($path) . ' a.txt b.txt 2>/dev/null';
			exec($cmd, $_, $code);
			if ($code === 0 && is_file($path)) {
				return $path;
			}
		}
		file_put_contents($path, '');
		return $path;
	})(),
	'sample.a' => (static function () use ($buildDir): string {
		$path = $buildDir . '/sample.a';
		$dir = $buildDir . '/ar_src';
		@mkdir($dir, 0755, true);
		file_put_contents($dir . '/x.o', "obj-x\n");
		file_put_contents($dir . '/y.o', "obj-y\n");
		$arBin = trim((string) shell_exec('command -v ar 2>/dev/null'));
		if ($arBin !== '') {
			$cmd = escapeshellarg($arBin) . ' rcs ' . escapeshellarg($path)
				. ' ' . escapeshellarg($dir . '/x.o') . ' ' . escapeshellarg($dir . '/y.o') . ' 2>/dev/null';
			exec($cmd, $_, $code);
			if ($code === 0 && is_file($path) && filesize($path) > 8) {
				return $path;
			}
		}
		// Manual two-member GNU ar stub.
		$mk = static function (string $name, string $data): string {
			$n = str_pad(substr($name, 0, 15) . '/', 16, ' ');
			$hdr = $n . str_pad('0', 12) . str_pad('0', 6) . str_pad('0', 6)
				. str_pad('100644', 8) . str_pad((string) strlen($data), 10) . "`\n";
			$pad = (strlen($data) % 2) ? "\n" : '';
			return $hdr . $data . $pad;
		};
		file_put_contents($path, "!<arch>\n" . $mk('x.o', "obj-x\n") . $mk('y.o', "obj-y\n"));
		return $path;
	})(),
	'sample.cpio' => (static function () use ($buildDir): string {
		$path = $buildDir . '/sample.cpio';
		$dir = $buildDir . '/cpio_src';
		@mkdir($dir, 0755, true);
		file_put_contents($dir . '/p.txt', "cpio-p\n");
		file_put_contents($dir . '/q.txt', "cpio-q\n");
		$cpioBin = trim((string) shell_exec('command -v cpio 2>/dev/null'));
		if ($cpioBin !== '') {
			$cmd = 'cd ' . escapeshellarg($dir) . ' && printf "%s\\n" p.txt q.txt | '
				. escapeshellarg($cpioBin) . ' -o -H newc > ' . escapeshellarg($path) . ' 2>/dev/null';
			exec($cmd, $_, $code);
			if ($code === 0 && is_file($path) && filesize($path) > 110) {
				return $path;
			}
		}
		file_put_contents($path, '');
		return $path;
	})(),
	'nav.har' => (static function () use ($buildDir): string {
		$path = $buildDir . '/nav.har';
		file_put_contents($path, json_encode([
			'log' => [
				'version' => '1.2',
				'entries' => [
					['request' => ['url' => 'https://example.com/'], 'response' => ['status' => 200]],
				],
			],
		]));
		return $path;
	})(),
	'payload.txt.gz' => (static function () use ($buildDir): string {
		$path = $buildDir . '/payload.txt.gz';
		file_put_contents($path, gzencode("Hello peel gzip entropy\n" . str_repeat("line\n", 40)));
		return $path;
	})(),
	'payload.txt.bz2' => (static function () use ($buildDir): string {
		$path = $buildDir . '/payload.txt.bz2';
		$raw = "Hello peel bzip2 entropy\n" . str_repeat("line\n", 40);
		if (function_exists('bzcompress')) {
			$c = @bzcompress($raw, 9);
			if (is_string($c) && $c !== '') {
				file_put_contents($path, $c);
				return $path;
			}
		}
		$bz = trim((string) shell_exec('command -v bzip2 2>/dev/null'));
		if ($bz !== '') {
			$src = $buildDir . '/payload_bz.txt';
			file_put_contents($src, $raw);
			exec(escapeshellarg($bz) . ' -c ' . escapeshellarg($src) . ' > ' . escapeshellarg($path) . ' 2>/dev/null', $_, $code);
			if ($code === 0 && is_file($path) && filesize($path) > 0) {
				return $path;
			}
		}
		file_put_contents($path, '');
		return $path;
	})(),
	'sample.wad' => (static function () use ($buildDir): string {
		$path = $buildDir . '/sample.wad';
		// Minimal PWAD: two lumps (DEMO1 empty marker + DATA payload).
		$lump0 = 'hello';
		$lump1 = "peel-wad\n";
		$dirOff = 12 + strlen($lump0) + strlen($lump1);
		$hdr = 'PWAD' . pack('VV', 2, $dirOff);
		$dir = pack('VV', 12, strlen($lump0)) . str_pad('DEMO1', 8, "\0")
			. pack('VV', 12 + strlen($lump0), strlen($lump1)) . str_pad('DATA', 8, "\0");
		file_put_contents($path, $hdr . $lump0 . $lump1 . $dir);
		return $path;
	})(),
	'sample.pak' => (static function () use ($buildDir): string {
		$path = $buildDir . '/sample.pak';
		// Quake PACK: header + one file entry + payload.
		$data = "pak-payload\n";
		$name = str_pad('maps/test.bsp', 56, "\0");
		$entry = $name . pack('VV', 64, strlen($data)); // offset after 12+52? PACK hdr=12, then data then dir
		// Layout: "PACK" + u32 dirofs + u32 dirlen; data at 12; dir at 12+len(data)
		$dirofs = 12 + strlen($data);
		$dirlen = 64; // one 64-byte entry
		$hdr = 'PACK' . pack('VV', $dirofs, $dirlen);
		$ent = str_pad('maps/test.bsp', 56, "\0") . pack('VV', 12, strlen($data));
		file_put_contents($path, $hdr . $data . $ent);
		return $path;
	})(),
	'score.mscz' => (static function () use ($buildDir): string {
		$path = $buildDir . '/score.mscz';
		if (!class_exists('ZipArchive')) {
			file_put_contents($path, '');
			return $path;
		}
		$za = new ZipArchive();
		$za->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		$za->addFromString('META-INF/container.xml', '<?xml version="1.0"?><container/>');
		$za->addFromString('score.mscx', '<?xml version="1.0"?><museScore version="4.00"><Score/></museScore>');
		$za->close();
		return $path;
	})(),
	'map.vmf' => (static function () use ($buildDir): string {
		$path = $buildDir . '/map.vmf';
		file_put_contents($path, "worldspawn\n{\n\t\"classname\" \"worldspawn\"\n\t\"mapversion\" \"1\"\n}\n");
		return $path;
	})(),
	'icon.xpm' => (static function () use ($buildDir): string {
		$path = $buildDir . '/icon.xpm';
		file_put_contents($path, "/* XPM */\nstatic char *icon[] = {\n\"2 2 1 1\",\n\". c #000000\",\n\"..\",\n\"..\"\n};\n");
		return $path;
	})(),
	'mark.svgz' => (static function () use ($buildDir): string {
		$path = $buildDir . '/mark.svgz';
		$svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"8\" height=\"8\"><rect width=\"8\" height=\"8\" fill=\"red\"/></svg>";
		file_put_contents($path, gzencode($svg, 9));
		return $path;
	})(),
	'icon.woff2' => (static function () use ($buildDir): string {
		$path = $buildDir . '/icon.woff2';
		$woff2 = trim((string) shell_exec('command -v woff2_compress 2>/dev/null'));
		if ($woff2 === '') {
			file_put_contents($path, '');
			return $path;
		}
		// Minimal valid-enough TTF is hard; prefer converting a system font if present.
		$candidates = [
			'/usr/share/fonts/TTF/DejaVuSans.ttf',
			'/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
			'/usr/share/fonts/TTF/LiberationSans-Regular.ttf',
		];
		$ttfSrc = null;
		foreach ($candidates as $c) {
			if (is_readable($c)) {
				$ttfSrc = $c;
				break;
			}
		}
		if ($ttfSrc === null) {
			file_put_contents($path, '');
			return $path;
		}
		$ttf = $buildDir . '/icon.ttf';
		copy($ttfSrc, $ttf);
		$cmd = 'cd ' . escapeshellarg($buildDir) . ' && ' . escapeshellarg($woff2)
			. ' icon.ttf 2>/dev/null';
		exec($cmd, $_, $code);
		if ($code === 0 && is_file($path) && filesize($path) > 8) {
			return $path;
		}
		file_put_contents($path, '');
		return $path;
	})(),
	'run.bat' => (static function () use ($buildDir): string {
		$path = $buildDir . '/run.bat';
		file_put_contents($path, "@echo off\r\necho peel\r\n");
		return $path;
	})(),
	'beep.wav' => (static function () use ($buildDir): string {
		$path = $buildDir . '/beep.wav';
		// Minimal PCM WAV: RIFF/WAVE fmt+data (silence 8 samples).
		$fmt = pack('v', 1) . pack('v', 1) . pack('V', 8000) . pack('V', 8000) . pack('v', 1) . pack('v', 8);
		$data = str_repeat("\x80", 8);
		$fmtChunk = 'fmt ' . pack('V', strlen($fmt)) . $fmt;
		$dataChunk = 'data' . pack('V', strlen($data)) . $data;
		$body = 'WAVE' . $fmtChunk . $dataChunk;
		file_put_contents($path, 'RIFF' . pack('V', strlen($body)) . $body);
		return $path;
	})(),
	'tune.mid' => (static function () use ($buildDir): string {
		$path = $buildDir . '/tune.mid';
		// MThd + one empty-ish MTrk (end-of-track meta).
		$hdr = pack('n*', 0, 1, 480); // format 0, 1 track, division
		$trk = "\x00\xFF\x2F\x00"; // delta 0, end of track
		file_put_contents(
			$path,
			'MThd' . pack('N', 6) . $hdr . 'MTrk' . pack('N', strlen($trk)) . $trk
		);
		return $path;
	})(),
	'sprite.ilbm' => (static function () use ($buildDir): string {
		$path = $buildDir . '/sprite.ilbm';
		$bmhd = str_repeat("\0", 20);
		$chunk = 'BMHD' . pack('N', 20) . $bmhd;
		$body = 'ILBM' . $chunk;
		file_put_contents($path, 'FORM' . pack('N', strlen($body)) . $body);
		return $path;
	})(),
	'legacy.doc' => peel_test_fixture_ole_stub($buildDir),
];

if (extension_loaded('pdo_sqlite')) {
	$files['data.sqlite'] = peel_test_fixture_sqlite($buildDir);
	$pdo = new PDO('sqlite:' . $files['data.sqlite']);
	$pdo->exec("INSERT INTO users (name) VALUES ('alice'), ('bob')");
} elseif (is_executable('/usr/bin/sqlite3') || trim((string) shell_exec('command -v sqlite3 2>/dev/null')) !== '') {
	$sqlite3 = is_executable('/usr/bin/sqlite3') ? '/usr/bin/sqlite3' : trim((string) shell_exec('command -v sqlite3 2>/dev/null'));
	$path = $buildDir . '/sample.sqlite';
	@unlink($path);
	$sql = "CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT);\n"
		. "CREATE VIEW active AS SELECT * FROM users;\n"
		. "INSERT INTO users (name) VALUES ('alice'), ('bob');\n";
	$cmd = 'echo ' . escapeshellarg($sql) . ' | ' . escapeshellarg($sqlite3) . ' ' . escapeshellarg($path) . ' 2>/dev/null';
	exec($cmd, $_, $code);
	if ($code === 0 && is_file($path)) {
		$files['data.sqlite'] = $path;
	}
}

$manifest = [];
foreach ($files as $name => $src) {
	if (!is_file($src)) {
		fwrite(STDERR, "skip missing build: {$name}\n");
		continue;
	}
	$manifest[] = [
		'name' => $name,
		'size' => (int) filesize($src),
		'sha1' => sha1_file($src) ?: '',
	];
	if (!$dryRun) {
		copy($src, $dest . DIRECTORY_SEPARATOR . $name);
	}
}

$manifestPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'test_files_hybrid_peel.json';
if (!$dryRun) {
	file_put_contents($manifestPath, json_encode(['files' => $manifest], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

bench_hybrid_rmdir($buildDir);

echo ($dryRun ? 'dry-run ' : '') . "built {$outName}: " . count($manifest) . " files\n";
foreach ($manifest as $row) {
	echo sprintf("  %-16s %8d bytes\n", $row['name'], $row['size']);
}
if (!$dryRun) {
	echo "manifest: benchmarks/test_files_hybrid_peel.json\n";
}

function bench_hybrid_rmdir(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}
	foreach (scandir($dir) ?: [] as $e) {
		if ($e === '.' || $e === '..') {
			continue;
		}
		$p = $dir . DIRECTORY_SEPARATOR . $e;
		is_dir($p) ? bench_hybrid_rmdir($p) : @unlink($p);
	}
	@rmdir($dir);
}
