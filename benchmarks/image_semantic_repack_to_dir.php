<?php
declare(strict_types=1);

/**
 * Build a "semantic image" corpus: resize/re-encode still images using a
 * visibility-driven heuristic (pixel budget + quality curve), copy non-images as-is,
 * and emit a manifest with image characteristics.
 *
 * This is an experiment lane for "semantic equivalence" (visual usability), not a
 * byte-identical pipeline. Use together with run_benchmarks --no-verify.
 *
 * Usage:
 *   php benchmarks/image_semantic_repack_to_dir.php <src_dir> <dst_dir> [options]
 *
 * Options:
 *   --max-display=1920x1080   target display budget for perceived detail
 *   --oversample=1.5          multiply display pixels by oversample^2 (default 1.5)
 *   --min-long-edge=640       never downscale below this long edge
 *   --allow-format-change=1   permit JPEG/WebP/AVIF output competition (default 0)
 *   --avif-speed=6            AVIF encode speed (0 slow/better .. 10 fast/worse)
 *   --formats=jpg,webp,avif   optional candidate format allowlist override
 *   --quality-bias=-6         shift all format qualities by this delta
 *   --score-mode=size         pick candidate by smallest bytes (default)
 *   --score-mode=proxy_fzc    pick by proxy score: gzdeflate(1,9) + size mix
 *   --proxy-size-weight=0.2   score mix in proxy_fzc mode: score=proxy+weight*bytes
 *   --lossy-source-only=1     only transform lossy-original sources (default on)
 *   --lossy-source-ext=jpg,jpeg,jpe  lossy source extension allowlist (default jpeg family)
 *   --keep-metadata=1         keep metadata (default strips profiles/EXIF where possible)
 *   --no-manifest             do not write _fzimg_semantic_manifest.json
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$srcArg = $argv[1] ?? '';
$dstArg = $argv[2] ?? '';
if ($srcArg === '' || $dstArg === '' || str_starts_with($srcArg, '-')) {
	fwrite(
		STDERR,
		"Usage: php benchmarks/image_semantic_repack_to_dir.php <src_dir> <dst_dir> [--max-display=1920x1080] [--oversample=1.5] [--min-long-edge=640] [--allow-format-change=1] [--avif-speed=6] [--formats=jpg,webp,avif] [--quality-bias=-6] [--score-mode=size|proxy_fzc] [--proxy-size-weight=0.2] [--lossy-source-only=1] [--lossy-source-ext=jpg,jpeg,jpe] [--keep-metadata=1] [--no-manifest]\n"
	);
	exit(2);
}

$opts = [
	'max_display' => '1920x1080',
	'oversample' => 1.5,
	'min_long_edge' => 640,
	'allow_format_change' => false,
	'avif_speed' => 6,
	'formats' => '',
	'quality_bias' => 0,
	'score_mode' => 'size',
	'proxy_size_weight' => 0.2,
	'lossy_source_only' => true,
	'lossy_source_ext' => 'jpg,jpeg,jpe',
	'keep_metadata' => false,
	'write_manifest' => true,
];
for ($i = 3; $i < $argc; $i++) {
	$a = (string) $argv[$i];
	if (str_starts_with($a, '--max-display=')) {
		$opts['max_display'] = substr($a, 14);
	} elseif (str_starts_with($a, '--oversample=')) {
		$opts['oversample'] = max(1.0, (float) substr($a, 13));
	} elseif (str_starts_with($a, '--min-long-edge=')) {
		$opts['min_long_edge'] = max(64, (int) substr($a, 16));
	} elseif ($a === '--allow-format-change=1') {
		$opts['allow_format_change'] = true;
	} elseif (str_starts_with($a, '--avif-speed=')) {
		$opts['avif_speed'] = max(0, min(10, (int) substr($a, 13)));
	} elseif (str_starts_with($a, '--formats=')) {
		$opts['formats'] = trim((string) substr($a, 10));
	} elseif (str_starts_with($a, '--quality-bias=')) {
		$opts['quality_bias'] = max(-40, min(20, (int) substr($a, 15)));
	} elseif (str_starts_with($a, '--score-mode=')) {
		$opts['score_mode'] = trim((string) substr($a, 13));
	} elseif (str_starts_with($a, '--proxy-size-weight=')) {
		$opts['proxy_size_weight'] = max(0.0, min(2.0, (float) substr($a, 20)));
	} elseif ($a === '--lossy-source-only=0') {
		$opts['lossy_source_only'] = false;
	} elseif ($a === '--lossy-source-only=1') {
		$opts['lossy_source_only'] = true;
	} elseif (str_starts_with($a, '--lossy-source-ext=')) {
		$opts['lossy_source_ext'] = trim((string) substr($a, 19));
	} elseif ($a === '--keep-metadata=1') {
		$opts['keep_metadata'] = true;
	} elseif ($a === '--no-manifest') {
		$opts['write_manifest'] = false;
	}
}

[$dispW, $dispH] = parse_display((string) $opts['max_display']);
$oversample = (float) $opts['oversample'];
$targetPx = max(1, (int) round($dispW * $dispH * $oversample * $oversample));
$minLongEdge = (int) $opts['min_long_edge'];
$allowFormatChange = (bool) $opts['allow_format_change'];
$avifSpeed = (int) $opts['avif_speed'];
$qualityBias = (int) $opts['quality_bias'];
$scoreMode = parse_score_mode((string) $opts['score_mode']);
$proxySizeWeight = (float) $opts['proxy_size_weight'];
$lossySourceOnly = (bool) $opts['lossy_source_only'];
$lossySourceExt = parse_lossy_source_ext((string) $opts['lossy_source_ext']);
$keepMetadata = (bool) $opts['keep_metadata'];
$formatsOverride = parse_formats_override((string) $opts['formats']);
$writeManifest = (bool) $opts['write_manifest'];

$src = normalize_path($repo, $srcArg);
$dst = normalize_path($repo, $dstArg);
if (!is_dir($src)) {
	fwrite(STDERR, "Not a directory: {$src}\n");
	exit(1);
}
if (is_dir($dst)) {
	fwrite(STDERR, "Destination exists (remove first): {$dstArg}\n");
	exit(1);
}

$im = shell_exec('command -v magick 2>/dev/null');
if (!is_string($im) || trim($im) === '') {
	fwrite(STDERR, "magick not found on PATH.\n");
	exit(1);
}
$ffmpeg = shell_exec('command -v ffmpeg 2>/dev/null');
$ffprobe = shell_exec('command -v ffprobe 2>/dev/null');
$hasFfmpeg = is_string($ffmpeg) && trim($ffmpeg) !== '' && is_string($ffprobe) && trim($ffprobe) !== '';

mkdir($dst, 0755, true);

$manifest = [
	'created_at' => gmdate('c'),
	'source_dir' => $src,
	'dest_dir' => $dst,
	'heuristic' => [
		'max_display' => "{$dispW}x{$dispH}",
		'oversample' => $oversample,
		'target_pixels' => $targetPx,
		'min_long_edge' => $minLongEdge,
		'allow_format_change' => $allowFormatChange,
		'avif_speed' => $avifSpeed,
		'formats' => $formatsOverride !== [] ? $formatsOverride : '(auto)',
		'quality_bias' => $qualityBias,
		'score_mode' => $scoreMode,
		'proxy_size_weight' => $proxySizeWeight,
		'lossy_source_only' => $lossySourceOnly,
		'lossy_source_ext' => array_keys($lossySourceExt),
		'keep_metadata' => $keepMetadata,
		'write_manifest' => $writeManifest,
	],
	'images' => [],
	'summary' => [
		'total_files' => 0,
		'image_files' => 0,
		'animated_skipped' => 0,
		'lossless_source_skipped' => 0,
		'copied_unchanged' => 0,
		'reencoded' => 0,
		'orig_bytes' => 0,
		'new_bytes' => 0,
	],
];

$it = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
	RecursiveIteratorIterator::SELF_FIRST
);
foreach ($it as $item) {
	$rel = str_replace('\\', '/', $it->getSubPathname());
	$srcPath = $item->getPathname();
	$manifest['summary']['total_files']++;
	if ($item->isDir()) {
		$toDir = $dst . DIRECTORY_SEPARATOR . $rel;
		if (!is_dir($toDir)) {
			mkdir($toDir, 0755, true);
		}
		continue;
	}
	$ext = strtolower((string) pathinfo($rel, PATHINFO_EXTENSION));
	if ($ext === 'jpeg' || $ext === 'jpe') {
		$ext = 'jpg';
	}
	if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif', 'bmp', 'tif', 'tiff'], true)) {
		copy_file_with_dirs($srcPath, $dst . DIRECTORY_SEPARATOR . $rel);
		continue;
	}
	$info = @getimagesize($srcPath);
	if (!is_array($info) || !isset($info[0], $info[1])) {
		copy_file_with_dirs($srcPath, $dst . DIRECTORY_SEPARATOR . $rel);
		continue;
	}
	$w = max(1, (int) $info[0]);
	$h = max(1, (int) $info[1]);
	$origBytes = (int) filesize($srcPath);
	$manifest['summary']['image_files']++;
	$manifest['summary']['orig_bytes'] += $origBytes;

	$isAnimated = false;
	if ($hasFfmpeg && in_array($ext, ['gif', 'webp', 'png'], true)) {
		$isAnimated = ffprobe_frames_gt1($srcPath);
	}
	if ($isAnimated) {
		copy_file_with_dirs($srcPath, $dst . DIRECTORY_SEPARATOR . $rel);
		$manifest['summary']['animated_skipped']++;
		$manifest['summary']['copied_unchanged']++;
		$manifest['summary']['new_bytes'] += $origBytes;
		$manifest['images'][] = [
			'path' => $rel,
			'orig_ext' => $ext,
			'new_ext' => $ext,
			'orig_w' => $w,
			'orig_h' => $h,
			'new_w' => $w,
			'new_h' => $h,
			'orig_bytes' => $origBytes,
			'new_bytes' => $origBytes,
			'mode' => 'copy_animated',
		];
		continue;
	}
	if ($lossySourceOnly && !isset($lossySourceExt[$ext])) {
		copy_file_with_dirs($srcPath, $dst . DIRECTORY_SEPARATOR . $rel);
		$manifest['summary']['lossless_source_skipped']++;
		$manifest['summary']['copied_unchanged']++;
		$manifest['summary']['new_bytes'] += $origBytes;
		$manifest['images'][] = [
			'path' => $rel,
			'orig_ext' => $ext,
			'new_ext' => $ext,
			'orig_w' => $w,
			'orig_h' => $h,
			'new_w' => $w,
			'new_h' => $h,
			'orig_bytes' => $origBytes,
			'new_bytes' => $origBytes,
			'mode' => 'copy_lossless_source',
		];
		continue;
	}

	[$tw, $th] = target_dimensions($w, $h, $targetPx, $minLongEdge);
	$qualities = quality_plan($tw, $th, $qualityBias);
	$candidates = $formatsOverride !== [] ? $formatsOverride : candidates_for_ext($ext, $allowFormatChange);
	$best = null;
	$bestScore = PHP_FLOAT_MAX;
	foreach ($candidates as $fmt) {
		$q = $qualities[$fmt] ?? 82;
		$out = build_candidate_with_magick($srcPath, $fmt, $tw, $th, $q, $avifSpeed, $keepMetadata);
		if ($out === null || $out['bytes'] <= 0) {
			continue;
		}
		$score = candidate_score((string) $out['blob'], (int) $out['bytes'], $scoreMode, $proxySizeWeight);
		if ($best === null || $score < $bestScore || ($score === $bestScore && $out['bytes'] < $best['bytes'])) {
			$best = $out + ['quality' => $q, 'score' => $score];
			$bestScore = $score;
		}
	}

	if ($best === null || $best['bytes'] >= $origBytes) {
		copy_file_with_dirs($srcPath, $dst . DIRECTORY_SEPARATOR . $rel);
		$manifest['summary']['copied_unchanged']++;
		$manifest['summary']['new_bytes'] += $origBytes;
		$manifest['images'][] = [
			'path' => $rel,
			'orig_ext' => $ext,
			'new_ext' => $ext,
			'orig_w' => $w,
			'orig_h' => $h,
			'new_w' => $w,
			'new_h' => $h,
			'orig_bytes' => $origBytes,
			'new_bytes' => $origBytes,
			'mode' => 'copy_no_gain',
		];
		continue;
	}

	$newRel = $rel;
	$newExt = (string) $best['format'];
	if ($newExt !== $ext) {
		$stem = preg_replace('/\.[^.]+$/', '', $rel);
		$newRel = (string) $stem . '.' . $newExt;
	}
	$to = $dst . DIRECTORY_SEPARATOR . $newRel;
	$parent = dirname($to);
	if (!is_dir($parent)) {
		mkdir($parent, 0755, true);
	}
	if (file_put_contents($to, (string) $best['blob']) === false) {
		copy_file_with_dirs($srcPath, $dst . DIRECTORY_SEPARATOR . $rel);
		$manifest['summary']['copied_unchanged']++;
		$manifest['summary']['new_bytes'] += $origBytes;
		continue;
	}
	$manifest['summary']['reencoded']++;
	$manifest['summary']['new_bytes'] += (int) $best['bytes'];
	$manifest['images'][] = [
		'path' => $rel,
		'new_path' => $newRel,
		'orig_ext' => $ext,
		'new_ext' => $newExt,
		'orig_w' => $w,
		'orig_h' => $h,
		'new_w' => $tw,
		'new_h' => $th,
		'orig_bytes' => $origBytes,
		'new_bytes' => (int) $best['bytes'],
		'quality' => (int) $best['quality'],
		'score' => isset($best['score']) ? (float) $best['score'] : null,
		'mode' => 'reencode',
	];
}

$manifest['summary']['saved_bytes'] = $manifest['summary']['orig_bytes'] - $manifest['summary']['new_bytes'];
$manifest['summary']['saved_pct'] = $manifest['summary']['orig_bytes'] > 0
	? round(100.0 * $manifest['summary']['saved_bytes'] / $manifest['summary']['orig_bytes'], 4)
	: 0.0;

$mfPath = '(disabled)';
if ($writeManifest) {
	$mfPath = $dst . DIRECTORY_SEPARATOR . '_fzimg_semantic_manifest.json';
	$jsMf = bench_json_encode_try($manifest, true);
	if ($jsMf === null) {
		fwrite(STDERR, '[bench] json_encode failed (image_semantic manifest): ' . json_last_error_msg() . "\n");
		exit(1);
	}
	if (@file_put_contents($mfPath, $jsMf . "\n") === false) {
		fwrite(STDERR, "failed to write manifest: {$mfPath}\n");
		exit(1);
	}
}
fprintf(
	STDERR,
	"image_semantic_repack files=%d images=%d reencoded=%d orig=%d new=%d saved=%d (%.2f%%) manifest=%s\n",
	$manifest['summary']['total_files'],
	$manifest['summary']['image_files'],
	$manifest['summary']['reencoded'],
	$manifest['summary']['orig_bytes'],
	$manifest['summary']['new_bytes'],
	$manifest['summary']['saved_bytes'],
	(float) $manifest['summary']['saved_pct'],
	$mfPath
);

function normalize_path(string $repo, string $arg): string {
	if ($arg === '') {
		return $repo;
	}
	if ($arg[0] === '/') {
		return $arg;
	}
	return $repo . DIRECTORY_SEPARATOR . $arg;
}

function parse_score_mode(string $s): string {
	$s = strtolower(trim($s));
	return match ($s) {
		'proxy', 'proxy_fzc' => 'proxy_fzc',
		default => 'size',
	};
}

/**
 * @return array{0:int,1:int}
 */
function parse_display(string $s): array {
	if (preg_match('/^\s*(\d+)\s*x\s*(\d+)\s*$/i', $s, $m) !== 1) {
		return [1920, 1080];
	}
	$w = max(320, (int) $m[1]);
	$h = max(240, (int) $m[2]);
	return [$w, $h];
}

function copy_file_with_dirs(string $src, string $dst): void {
	$p = dirname($dst);
	if (!is_dir($p)) {
		mkdir($p, 0755, true);
	}
	copy($src, $dst);
}

/**
 * Simple "visible detail" heuristic:
 *  - Keep all pixels up to display budget * oversample^2
 *  - Above that, downscale while preserving aspect ratio
 *  - Never downscale long edge below minLongEdge
 *
 * @return array{0:int,1:int}
 */
function target_dimensions(int $w, int $h, int $targetPixels, int $minLongEdge): array {
	$origPixels = (float) ($w * $h);
	if ($origPixels <= (float) $targetPixels) {
		return [$w, $h];
	}
	$scale = sqrt((float) $targetPixels / $origPixels);
	$tw = max(1, (int) round($w * $scale));
	$th = max(1, (int) round($h * $scale));
	$long = max($tw, $th);
	if ($long < $minLongEdge) {
		$up = (float) $minLongEdge / (float) $long;
		$tw = max(1, (int) round($tw * $up));
		$th = max(1, (int) round($th * $up));
	}
	return [$tw, $th];
}

/**
 * Return quality targets by format from resulting megapixels.
 *
 * @return array<string,int>
 */
function quality_plan(int $w, int $h, int $bias): array {
	$mp = ((float) $w * (float) $h) / 1000000.0;
	$base = 90 - (int) floor(max(0.0, log(max(1.0, $mp), 2.0)) * 3.0);
	$q = max(68, min(92, $base));
	$q += $bias;
	return [
		'jpg' => max(45, min(95, $q)),
		'webp' => max(40, min(93, $q - 2)),
		'avif' => max(34, min(88, $q - 10)),
		'png' => 95,
	];
}

/**
 * @return list<string>
 */
function candidates_for_ext(string $ext, bool $allowFormatChange): array {
	$ext = strtolower($ext);
	$same = match ($ext) {
		'jpeg' => 'jpg',
		default => $ext,
	};
	if (!$allowFormatChange) {
		return in_array($same, ['jpg', 'png', 'webp', 'gif', 'bmp', 'tif', 'tiff'], true) ? [target_fmt($same)] : ['jpg'];
	}
	$cands = [target_fmt($same), 'webp', 'avif', 'jpg'];
	return array_values(array_unique(array_filter($cands, static fn ($x) => $x !== 'gif')));
}

function target_fmt(string $ext): string {
	return match ($ext) {
		'jpeg', 'jpg' => 'jpg',
		'png' => 'png',
		'webp' => 'webp',
		'bmp', 'tif', 'tiff', 'gif' => 'jpg',
		default => 'jpg',
	};
}

/**
 * @return list<string>
 */
function parse_formats_override(string $s): array {
	if (trim($s) === '') {
		return [];
	}
	$ok = ['jpg' => true, 'webp' => true, 'avif' => true, 'png' => true];
	$out = [];
	foreach (explode(',', $s) as $p) {
		$p = strtolower(trim($p));
		if ($p === 'jpeg') {
			$p = 'jpg';
		}
		if (isset($ok[$p])) {
			$out[] = $p;
		}
	}
	return array_values(array_unique($out));
}

/**
 * @return array<string,true>
 */
function parse_lossy_source_ext(string $s): array {
	$in = trim($s);
	if ($in === '') {
		$in = 'jpg,jpeg,jpe';
	}
	$out = [];
	foreach (explode(',', $in) as $p) {
		$p = strtolower(trim($p));
		if ($p === '') {
			continue;
		}
		if ($p === 'jpeg' || $p === 'jpe') {
			$p = 'jpg';
		}
		$out[$p] = true;
	}
	if ($out === []) {
		$out['jpg'] = true;
	}
	return $out;
}

function candidate_score(string $blob, int $bytes, string $mode, float $proxySizeWeight): float {
	if ($mode !== 'proxy_fzc') {
		return (float) $bytes;
	}
	$def1 = @gzdeflate($blob, 1);
	$def9 = @gzdeflate($blob, 9);
	$l1 = is_string($def1) ? strlen($def1) : $bytes;
	$l9 = is_string($def9) ? strlen($def9) : $bytes;
	return (float) $l1 + (0.65 * (float) $l9) + ($proxySizeWeight * (float) $bytes);
}

/**
 * @return array{format:string,bytes:int,blob:string}|null
 */
function build_candidate_with_magick(
	string $srcPath,
	string $fmt,
	int $tw,
	int $th,
	int $quality,
	int $avifSpeed,
	bool $keepMetadata
): ?array {
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzimg_sem_' . bin2hex(random_bytes(8)) . '.' . $fmt;
	$srcQ = escapeshellarg($srcPath);
	$outQ = escapeshellarg($tmp);
	$resize = escapeshellarg($tw . 'x' . $th . '>');
	$q = max(1, min(100, $quality));
	$ops = [];
	$ops[] = "-auto-orient";
	if (!$keepMetadata) {
		$ops[] = "-strip";
	}
	$ops[] = "-resize {$resize}";
	if ($fmt === 'jpg') {
		$ops[] = "-sampling-factor 4:2:0 -quality {$q} -define jpeg:optimize-coding=true -define jpeg:dct-method=float";
		$ops[] = "jpg:{$outQ}";
	} elseif ($fmt === 'webp') {
		$ops[] = "-quality {$q} -define webp:method=6";
		$ops[] = "webp:{$outQ}";
	} elseif ($fmt === 'avif') {
		$ops[] = "-quality {$q} -define heic:speed={$avifSpeed}";
		$ops[] = "avif:{$outQ}";
	} elseif ($fmt === 'png') {
		$ops[] = "-define png:compression-level=9 -define png:compression-strategy=1";
		$ops[] = "png:{$outQ}";
	} else {
		return null;
	}
	$cmd = "magick {$srcQ} " . implode(' ', $ops) . " 2>/dev/null";
	$ret = 1;
	@exec($cmd, $discard, $ret);
	if ($ret !== 0 || !is_file($tmp)) {
		@unlink($tmp);
		return null;
	}
	$blob = file_get_contents($tmp);
	@unlink($tmp);
	if (!is_string($blob) || $blob === '') {
		return null;
	}
	return ['format' => $fmt, 'bytes' => strlen($blob), 'blob' => $blob];
}

function ffprobe_frames_gt1(string $path): bool {
	$p = escapeshellarg($path);
	$cmd = "ffprobe -v error -select_streams v:0 -show_entries stream=nb_frames -of default=nw=1:nk=1 {$p} 2>/dev/null";
	$out = shell_exec($cmd);
	if (!is_string($out) || trim($out) === '' || trim($out) === 'N/A') {
		$cmd2 = "ffprobe -v error -count_frames -select_streams v:0 -show_entries stream=nb_read_frames -of default=nw=1:nk=1 {$p} 2>/dev/null";
		$out = shell_exec($cmd2);
	}
	if (!is_string($out)) {
		return false;
	}
	$n = (int) trim($out);
	return $n > 1;
}
