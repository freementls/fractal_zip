#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Materialize public general-purpose corpora test_files202 … test_files210.
 *
 * Sources are pinned public HTTPS URLs only (no personal /srv/http harvest).
 * Lakes + download cache are gitignored — do NOT commit them.
 *
 * Usage (repo root):
 *   php benchmarks/build_test_files202_210_gp.php
 *   php benchmarks/build_test_files202_210_gp.php --force
 *   php benchmarks/build_test_files202_210_gp.php --only=202,205 --max-mib=3072
 *
 * Coverage:
 *   php benchmarks/report_gp_corpora_coverage.php
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'gp_corpus_lib.php';

$force = in_array('--force', $argv, true);
$maxMib = 3072;
$only = null;
foreach ($argv as $a) {
	if (str_starts_with($a, '--max-mib=')) {
		$maxMib = max(64, (int) substr($a, strlen('--max-mib=')));
	}
	if (str_starts_with($a, '--only=')) {
		$only = [];
		foreach (explode(',', substr($a, strlen('--only='))) as $p) {
			$p = trim($p);
			if ($p === '') {
				continue;
			}
			if (preg_match('/^\d+$/', $p)) {
				$only[] = 'test_files' . $p;
			} elseif (preg_match('/^test_files\d+$/', $p)) {
				$only[] = $p;
			}
		}
	}
}
$maxBytes = $maxMib * 1024 * 1024;
$root = gp_repo_root();
$cache = gp_cache_dir();
gp_mkdir($cache);

/**
 * @return array<string, array{title:string,domain:string,license_notes:string,artifacts:list<array<string,mixed>>,ext_allow:?list<string>}>
 */
function gp_corpus_specs(): array
{
	return [
		'test_files202' => [
			'title' => 'GP game / interactive assets',
			'domain' => 'FOSS game data: WAD/assets, open RTS data, kart assets',
			'license_notes' => 'Freedoom (BSD), 0 A.D. data (CC BY-SA), SuperTuxKart assets (various FOSS) — see upstream.',
			'ext_allow' => null,
			'artifacts' => [
				[
					'url' => 'https://github.com/freedoom/freedoom/releases/download/v0.13.0/freedoom-0.13.0.zip',
					'name' => 'freedoom-0.13.0.zip',
					'extract' => true,
				],
				[
					'url' => 'https://releases.wildfiregames.com/0ad-0.0.26-alpha-unix-data.tar.xz',
					'name' => '0ad-0.0.26-alpha-unix-data.tar.xz',
					'extract' => true,
				],
				[
					'url' => 'https://github.com/luanti-org/luanti/archive/refs/tags/5.10.0.tar.gz',
					'name' => 'luanti-5.10.0.tar.gz',
					'extract' => true,
				],
				[
					'url' => 'https://cdn.openttd.org/opengfx-releases/7.1/opengfx-7.1-all.zip',
					'name' => 'opengfx-7.1-all.zip',
					'extract' => true,
				],
				[
					'url' => 'https://cdn.openttd.org/opensfx-releases/1.0.3/opensfx-1.0.3-all.zip',
					'name' => 'opensfx-1.0.3-all.zip',
					'extract' => true,
				],
				[
					'url' => 'https://cdn.openttd.org/openmsx-releases/0.4.2/openmsx-0.4.2-all.zip',
					'name' => 'openmsx-0.4.2-all.zip',
					'extract' => true,
				],
				[
					'url' => 'https://downloads.sourceforge.net/project/wesnoth/wesnoth-1.18/wesnoth-1.18.3/wesnoth-1.18.3.tar.bz2',
					'name' => 'wesnoth-1.18.3.tar.bz2',
					'extract' => true,
					'extract_budget' => 2 * 1024 * 1024 * 1024,
				],
			],
		],
		'test_files203' => [
			'title' => 'GP open video',
			'domain' => 'Blender open-movie masters / trailers (H.264/WebM/MKV)',
			'license_notes' => 'Blender Foundation CC BY — Sintel, Big Buck Bunny sunflower encodes.',
			'ext_allow' => null,
			'artifacts' => [
				[
					'url' => 'https://download.blender.org/durian/movies/Sintel.2010.1080p.mkv',
					'name' => 'Sintel.2010.1080p.mkv',
					'extract' => false,
				],
				[
					'url' => 'https://download.blender.org/demo/movies/BBB/bbb_sunflower_1080p_60fps_normal.mp4.zip',
					'name' => 'bbb_sunflower_1080p_60fps_normal.mp4.zip',
					'extract' => true,
				],
				[
					'url' => 'https://download.blender.org/demo/movies/BBB/bbb_sunflower_1080p_30fps_normal.mp4.zip',
					'name' => 'bbb_sunflower_1080p_30fps_normal.mp4.zip',
					'extract' => true,
				],
				[
					'url' => 'https://download.blender.org/demo/movies/BBB/bbb_sunflower_2160p_60fps_normal.mp4.zip',
					'name' => 'bbb_sunflower_2160p_60fps_normal.mp4.zip',
					'extract' => true,
				],
			],
		],
		'test_files204' => [
			'title' => 'GP office / PDF lake',
			'domain' => 'Digital Corpora govdocs1 PDF zip shards (standard forensics/PDF corpus)',
			'license_notes' => 'govdocs1 — Digital Corpora; US government documents collected for research.',
			'ext_allow' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'html', 'txt', 'rtf'],
			'artifacts' => [
				['url' => 'https://digitalcorpora.s3.amazonaws.com/corpora/files/govdocs1/zipfiles/000.zip', 'name' => 'govdocs1-000.zip', 'extract' => true],
				['url' => 'https://digitalcorpora.s3.amazonaws.com/corpora/files/govdocs1/zipfiles/001.zip', 'name' => 'govdocs1-001.zip', 'extract' => true],
				['url' => 'https://digitalcorpora.s3.amazonaws.com/corpora/files/govdocs1/zipfiles/010.zip', 'name' => 'govdocs1-010.zip', 'extract' => true],
				['url' => 'https://digitalcorpora.s3.amazonaws.com/corpora/files/govdocs1/zipfiles/050.zip', 'name' => 'govdocs1-050.zip', 'extract' => true],
				['url' => 'https://digitalcorpora.s3.amazonaws.com/corpora/files/govdocs1/zipfiles/100.zip', 'name' => 'govdocs1-100.zip', 'extract' => true],
				['url' => 'https://digitalcorpora.s3.amazonaws.com/corpora/files/govdocs1/zipfiles/200.zip', 'name' => 'govdocs1-200.zip', 'extract' => true],
				['url' => 'https://digitalcorpora.s3.amazonaws.com/corpora/files/govdocs1/zipfiles/300.zip', 'name' => 'govdocs1-300.zip', 'extract' => true],
			],
		],
		'test_files205' => [
			'title' => 'GP photography',
			'domain' => 'COCO val2017 + fast.ai imagenette/imagewoof + Oxford pets/flowers (budgeted JPEG/PNG mass)',
			'license_notes' => 'COCO (Flickr authors; cocodataset.org); imagenette/imagewoof (ImageNet subset via fast.ai); Oxford-IIIT Pets / 102 Flowers (VGG).',
			'ext_allow' => ['jpg', 'jpeg', 'png'],
			'artifacts' => [
				[
					'url' => 'http://images.cocodataset.org/zips/val2017.zip',
					'name' => 'coco-val2017.zip',
					'extract' => true,
				],
				[
					'url' => 'https://s3.amazonaws.com/fast-ai-imageclas/imagenette2.tgz',
					'name' => 'imagenette2.tgz',
					'extract' => true,
				],
				[
					'url' => 'https://s3.amazonaws.com/fast-ai-imageclas/imagewoof2.tgz',
					'name' => 'imagewoof2.tgz',
					'extract' => true,
				],
				[
					'url' => 'https://www.robots.ox.ac.uk/~vgg/data/pets/data/images.tar.gz',
					'name' => 'oxford-iiit-pets-images.tar.gz',
					'extract' => true,
				],
				[
					'url' => 'https://www.robots.ox.ac.uk/~vgg/data/flowers/102/102flowers.tgz',
					'name' => '102flowers.tgz',
					'extract' => true,
				],
			],
		],
		'test_files206' => [
			'title' => 'GP source code mega-tree',
			'domain' => 'Linux kernel + GCC + Git + PostgreSQL release tarballs',
			'license_notes' => 'GPLv2 (kernel/git), GPLv3 (gcc), PostgreSQL License — upstream tarballs.',
			'ext_allow' => null,
			'artifacts' => [
				['url' => 'https://cdn.kernel.org/pub/linux/kernel/v6.x/linux-6.12.6.tar.xz', 'name' => 'linux-6.12.6.tar.xz', 'extract' => true],
				['url' => 'https://ftp.gnu.org/gnu/gcc/gcc-14.2.0/gcc-14.2.0.tar.xz', 'name' => 'gcc-14.2.0.tar.xz', 'extract' => true],
				['url' => 'https://mirrors.edge.kernel.org/pub/software/scm/git/git-2.47.1.tar.xz', 'name' => 'git-2.47.1.tar.xz', 'extract' => true],
				['url' => 'https://ftp.postgresql.org/pub/source/v16.6/postgresql-16.6.tar.bz2', 'name' => 'postgresql-16.6.tar.bz2', 'extract' => true],
				['url' => 'https://github.com/llvm/llvm-project/archive/refs/tags/llvmorg-19.1.6.tar.gz', 'name' => 'llvmorg-19.1.6.tar.gz', 'extract' => true],
			],
		],
		'test_files207' => [
			'title' => 'GP OS packages',
			'domain' => 'Pinned Debian amd64 .deb pool slice (nested ar/tar/gz)',
			'license_notes' => 'Debian packages — DFSG; see each package copyright.',
			'ext_allow' => null,
			'artifacts' => gp_debian_deb_artifacts(),
		],
		'test_files208' => [
			'title' => 'GP structured data',
			'domain' => 'NYC TLC yellow-trip Parquet months + open CSV/SQLite dumps',
			'license_notes' => 'NYC TLC trip data (City of New York open data terms).',
			'ext_allow' => null,
			'artifacts' => array_merge(
				gp_nyc_taxi_artifacts(),
				[
					[
						'url' => 'https://github.com/lerocha/chinook-database/raw/master/ChinookDatabase/DataSources/Chinook_Sqlite.sqlite',
						'name' => 'Chinook_Sqlite.sqlite',
						'extract' => false,
					],
					[
						'url' => 'https://raw.githubusercontent.com/datasets/covid-19/main/data/countries-aggregated.csv',
						'name' => 'covid-countries-aggregated.csv',
						'extract' => false,
					],
				]
			),
		],
		'test_files209' => [
			'title' => 'GP ML weights',
			'domain' => 'Real GGUF + ONNX model weights',
			'license_notes' => 'TinyLlama (Apache-2.0) GGUF via Hugging Face; ONNX Model Zoo ResNet50.',
			'ext_allow' => null,
			'artifacts' => [
				[
					'url' => 'https://huggingface.co/TheBloke/TinyLlama-1.1B-Chat-v1.0-GGUF/resolve/main/tinyllama-1.1b-chat-v1.0.Q4_K_M.gguf',
					'name' => 'tinyllama-1.1b-chat-v1.0.Q4_K_M.gguf',
					'extract' => false,
				],
				[
					'url' => 'https://huggingface.co/TheBloke/TinyLlama-1.1B-Chat-v1.0-GGUF/resolve/main/tinyllama-1.1b-chat-v1.0.Q8_0.gguf',
					'name' => 'tinyllama-1.1b-chat-v1.0.Q8_0.gguf',
					'extract' => false,
				],
				[
					'url' => 'https://huggingface.co/TheBloke/Mistral-7B-Instruct-v0.2-GGUF/resolve/main/mistral-7b-instruct-v0.2.Q3_K_M.gguf',
					'name' => 'mistral-7b-instruct-v0.2.Q3_K_M.gguf',
					'extract' => false,
				],
				[
					'url' => 'https://media.githubusercontent.com/media/onnx/models/main/validated/vision/classification/resnet/model/resnet50-v2-7.onnx',
					'name' => 'resnet50-v2-7.onnx',
					'extract' => false,
				],
				[
					'url' => 'https://media.githubusercontent.com/media/onnx/models/main/validated/vision/classification/mobilenet/model/mobilenetv2-12.onnx',
					'name' => 'mobilenetv2-12.onnx',
					'extract' => false,
				],
			],
		],
		'test_files210' => [
			'title' => 'GP precompressed / archival',
			'domain' => 'Already-compressed FOSS release artifacts (.tar.gz/.xz/.zip)',
			'license_notes' => 'Upstream release archives (zlib/xz/zip containers) — compress-the-compressed stress.',
			'ext_allow' => null,
			'artifacts' => [
				// Keep as opaque archives (do not extract) so the lake is precompressed bytes.
				['url' => 'https://ftp.gnu.org/gnu/gzip/gzip-1.13.tar.gz', 'name' => 'gzip-1.13.tar.gz', 'extract' => false],
				['url' => 'https://ftp.gnu.org/gnu/bash/bash-5.2.37.tar.gz', 'name' => 'bash-5.2.37.tar.gz', 'extract' => false],
				['url' => 'https://ftp.gnu.org/gnu/coreutils/coreutils-9.5.tar.xz', 'name' => 'coreutils-9.5.tar.xz', 'extract' => false],
				['url' => 'https://ftp.gnu.org/gnu/binutils/binutils-2.43.tar.xz', 'name' => 'binutils-2.43.tar.xz', 'extract' => false],
				['url' => 'https://ftp.gnu.org/gnu/glibc/glibc-2.40.tar.xz', 'name' => 'glibc-2.40.tar.xz', 'extract' => false],
				['url' => 'https://ftp.gnu.org/gnu/emacs/emacs-29.4.tar.xz', 'name' => 'emacs-29.4.tar.xz', 'extract' => false],
				['url' => 'https://ftp.gnu.org/gnu/gdb/gdb-15.2.tar.xz', 'name' => 'gdb-15.2.tar.xz', 'extract' => false],
				['url' => 'https://ftp.gnu.org/gnu/wget/wget-1.24.5.tar.gz', 'name' => 'wget-1.24.5.tar.gz', 'extract' => false],
				['url' => 'https://cdn.kernel.org/pub/linux/kernel/v6.x/linux-6.11.11.tar.xz', 'name' => 'linux-6.11.11.tar.xz', 'extract' => false],
				['url' => 'https://cdn.kernel.org/pub/linux/kernel/v6.x/linux-6.10.14.tar.xz', 'name' => 'linux-6.10.14.tar.xz', 'extract' => false],
				['url' => 'https://cdn.kernel.org/pub/linux/kernel/v6.x/linux-6.9.12.tar.xz', 'name' => 'linux-6.9.12.tar.xz', 'extract' => false],
				['url' => 'https://github.com/git/git/archive/refs/tags/v2.46.2.tar.gz', 'name' => 'git-v2.46.2.tar.gz', 'extract' => false],
				['url' => 'https://github.com/python/cpython/archive/refs/tags/v3.12.8.tar.gz', 'name' => 'cpython-v3.12.8.tar.gz', 'extract' => false],
				['url' => 'https://github.com/golang/go/archive/refs/tags/go1.23.4.tar.gz', 'name' => 'go1.23.4.tar.gz', 'extract' => false],
				['url' => 'https://github.com/rust-lang/rust/archive/refs/tags/1.83.0.tar.gz', 'name' => 'rust-1.83.0.tar.gz', 'extract' => false],
				['url' => 'https://nodejs.org/dist/v22.12.0/node-v22.12.0.tar.gz', 'name' => 'node-v22.12.0.tar.gz', 'extract' => false],
				['url' => 'https://dl.google.com/go/go1.23.4.linux-amd64.tar.gz', 'name' => 'go1.23.4.linux-amd64.tar.gz', 'extract' => false],
			],
		],
	];
}

/** @return list<array<string,mixed>> */
function gp_nyc_taxi_artifacts(): array
{
	$out = [];
	foreach (['2022', '2023', '2024'] as $y) {
		for ($m = 1; $m <= 12; $m++) {
			$mm = sprintf('%02d', $m);
			$out[] = [
				'url' => "https://d37ci6vzurychx.cloudfront.net/trip-data/yellow_tripdata_{$y}-{$mm}.parquet",
				'name' => "yellow_tripdata_{$y}-{$mm}.parquet",
				'extract' => false,
			];
		}
	}
	return $out;
}

/** @return list<array<string,mixed>> */
function gp_debian_deb_artifacts(): array
{
	// Pinned via snapshot.debian.org (2025-01-15) so pool paths stay fetchable for years.
	$snap = 'https://snapshot.debian.org/archive/debian/20250115T000000Z/';
	// Prefer mid-size packages first so a slow/huge dbg download cannot block a usable lake.
	$pkgs = [
		$snap . 'pool/main/t/texlive-extra/texlive-fonts-extra_2022.20230122-4_all.deb',
		$snap . 'pool/main/t/texlive-extra/texlive-latex-extra-doc_2022.20230122-4_all.deb',
		$snap . 'pool/main/l/linux-signed-amd64/linux-image-6.1.0-29-amd64_6.1.123-1_amd64.deb',
		$snap . 'pool/main/f/firefox-esr/firefox-esr_128.5.0esr-1~deb12u1_amd64.deb',
		$snap . 'pool/main/g/gcc-12/gcc-12_12.2.0-14_amd64.deb',
		$snap . 'pool/main/g/gcc-12/g++-12_12.2.0-14_amd64.deb',
		$snap . 'pool/main/g/gcc-12/gcc-12-source_12.2.0-14_all.deb',
		$snap . 'pool/main/p/python3.11/python3.11_3.11.2-6+deb12u5_amd64.deb',
		$snap . 'pool/main/p/python3.11/libpython3.11-stdlib_3.11.2-6+deb12u5_amd64.deb',
		$snap . 'pool/main/o/openjdk-17/openjdk-17-jre-headless_17.0.13+11-2~deb12u1_amd64.deb',
		$snap . 'pool/main/o/openjdk-17/openjdk-17-jdk-headless_17.0.13+11-2~deb12u1_amd64.deb',
		$snap . 'pool/main/g/glibc/libc6_2.36-9+deb12u9_amd64.deb',
		$snap . 'pool/main/s/systemd/systemd_252.33-1~deb12u1_amd64.deb',
		$snap . 'pool/main/n/nodejs/nodejs_18.19.0+dfsg-6~deb12u2_amd64.deb',
		$snap . 'pool/main/r/ruby3.1/ruby3.1_3.1.2-7+deb12u1_amd64.deb',
		$snap . 'pool/main/p/php8.2/php8.2-cli_8.2.26-1~deb12u1_amd64.deb',
		$snap . 'pool/main/m/mariadb/mariadb-server_10.11.6-0+deb12u1_amd64.deb',
		$snap . 'pool/main/p/postgresql-15/postgresql-15_15.10-0+deb12u1_amd64.deb',
		$snap . 'pool/main/n/nginx/nginx_1.22.1-9_amd64.deb',
		$snap . 'pool/main/c/curl/curl_7.88.1-10+deb12u8_amd64.deb',
		$snap . 'pool/main/g/git/git_2.39.5-0+deb12u1_amd64.deb',
		$snap . 'pool/main/v/vim/vim_9.0.1378-2_amd64.deb',
		$snap . 'pool/main/e/emacs/emacs-gtk_28.2+1-15+deb12u3_amd64.deb',
		$snap . 'pool/main/f/ffmpeg/ffmpeg_5.1.6-0+deb12u1_amd64.deb',
		$snap . 'pool/main/i/imagemagick/imagemagick_6.9.11.60+dfsg-1.6+deb12u2_amd64.deb',
		$snap . 'pool/main/libr/libreoffice/libreoffice-core_7.4.7-1+deb12u5_amd64.deb',
		$snap . 'pool/main/libr/libreoffice/libreoffice-writer_7.4.7-1+deb12u5_amd64.deb',
		$snap . 'pool/main/libr/libreoffice/libreoffice-calc_7.4.7-1+deb12u5_amd64.deb',
		$snap . 'pool/main/t/texlive-bin/texlive-binaries_2022.20220321.62855-5.1+deb12u2_amd64.deb',
		$snap . 'pool/main/q/qtbase-opensource-src/libqt5core5a_5.15.8+dfsg-11+deb12u2_amd64.deb',
		$snap . 'pool/main/g/gtk+3.0/libgtk-3-0_3.24.38-2~deb12u3_amd64.deb',
		$snap . 'pool/main/m/mesa/libgl1-mesa-dri_22.3.6-1+deb12u1_amd64.deb',
		$snap . 'pool/main/w/webkit2gtk/libwebkit2gtk-4.0-37_2.46.5-1~deb12u1_amd64.deb',
		// Large; last so mid-size packages can fill the lake if this download is slow.
		$snap . 'pool/main/l/linux/linux-image-6.1.0-29-amd64-dbg_6.1.123-1_amd64.deb',
	];
	$out = [];
	foreach ($pkgs as $url) {
		$out[] = [
			'url' => $url,
			'name' => basename($url),
			'extract' => true,
		];
	}
	return $out;
}

/**
 * Extract large tar.gz but stop once staging exceeds soft budget (best-effort via full extract then select).
 * For open-images validation (~12 GiB), extract to cache staging once and budget-select.
 */
function gp_build_one(string $label, array $spec, int $maxBytes, string $cache, string $root, bool $force): void
{
	$dst = $root . DIRECTORY_SEPARATOR . $label;
	$manifest = $dst . DIRECTORY_SEPARATOR . 'MANIFEST.json';
	if (is_file($manifest) && !$force) {
		echo "OK $label already present (use --force to rebuild)\n";
		return;
	}
	echo "=== $label: {$spec['title']} (budget " . round($maxBytes / 1048576) . " MiB) ===\n";
	if ($force) {
		gp_rrmdir($dst);
	}
	gp_mkdir($dst);

	$stageRoot = $cache . DIRECTORY_SEPARATOR . '_stage_' . $label;
	gp_mkdir($stageRoot);
	$sourcesMeta = [];
	$extAllow = $spec['ext_allow'] ?? null;

	foreach ($spec['artifacts'] as $art) {
		if (gp_dir_total_bytes($dst) >= $maxBytes) {
			break;
		}
		try {
			$stage = gp_materialize_artifact($art, $cache, $stageRoot);
			$sourcesMeta[] = [
				'url' => $art['url'],
				'name' => $art['name'] ?? basename($art['url']),
				'extract' => (bool) ($art['extract'] ?? true),
			];
			$remain = $maxBytes - gp_dir_total_bytes($dst);
			if ($remain <= 0) {
				break;
			}
			gp_select_into($stage, $dst, $remain, $extAllow);
		} catch (Throwable $e) {
			fwrite(STDERR, "  WARN skip {$art['url']}: " . $e->getMessage() . "\n");
		}
	}

	$total = gp_dir_total_bytes($dst);
	$files = gp_count_files($dst);
	if ($total < 16 * 1024 * 1024) {
		throw new RuntimeException("$label too small after build: $total bytes — check downloads");
	}
	gp_write_manifest($dst, $spec, $sourcesMeta, $total, $files);
	echo "Wrote $label: files=$files bytes=$total (" . round($total / 1048576, 1) . " MiB)\n";
}

$specs = gp_corpus_specs();
$targets = $only ?? array_keys($specs);
foreach ($targets as $label) {
	if (!isset($specs[$label])) {
		fwrite(STDERR, "unknown corpus $label\n");
		exit(2);
	}
}

$errors = [];
foreach ($targets as $label) {
	try {
		gp_build_one($label, $specs[$label], $maxBytes, $cache, $root, $force);
	} catch (Throwable $e) {
		$errors[] = "$label: " . $e->getMessage();
		fwrite(STDERR, "ERROR $label: " . $e->getMessage() . "\n");
	}
}

echo "\nDone. Lakes are gitignored — do not git add test_files202–210 or benchmarks/.gp_corpus_cache/\n";
echo "Coverage: php benchmarks/report_gp_corpora_coverage.php\n";
if ($errors !== []) {
	fwrite(STDERR, "Completed with " . count($errors) . " error(s)\n");
	exit(1);
}
