#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Smoke: FRACTAL_ZIP_CONVERT_PNG_TO_BMP mode 28 round-trip on test_files61/00_source_png.
 *
 *   php benchmarks/smoke_convert_png_mode28.php
 */
$repo = dirname(__DIR__);
$src = $repo . DIRECTORY_SEPARATOR . 'test_files61' . DIRECTORY_SEPARATOR . '00_source_png';
if (!is_dir($src)) {
	fwrite(STDERR, "missing {$src}\n");
	exit(1);
}

putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1');
putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0');
putenv('FRACTAL_ZIP_CONVERT_PNG_TO_BMP=1');

$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_cvt_smoke_' . bin2hex(random_bytes(3));
mkdir($work, 0755, true);
foreach (glob($src . DIRECTORY_SEPARATOR . '*.png') as $png) {
	copy($png, $work . DIRECTORY_SEPARATOR . basename($png));
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$fzc = $work . '.fz';
@unlink($fzc);
$fz = new fractal_zip();
$fz->zip_folder($work, false);
if (!is_file($fzc)) {
	fwrite(STDERR, "encode failed\n");
	exit(1);
}
$fzcBytes = (int) filesize($fzc);

$extract = $work . '_ex';
mkdir($extract, 0755, true);
copy($fzc, $extract . DIRECTORY_SEPARATOR . 'p.fz');
$cwd = getcwd();
chdir($extract);
try {
	(new fractal_zip())->open_container($extract . DIRECTORY_SEPARATOR . 'p.fz', false);
} finally {
	if ($cwd !== false) {
		chdir($cwd);
	}
}

$bad = 0;
foreach (glob($src . DIRECTORY_SEPARATOR . '*.png') as $orig) {
	$b = basename($orig);
	$ex = $extract . DIRECTORY_SEPARATOR . $b;
	if (!is_file($ex)) {
		echo "MISSING {$b}\n";
		$bad++;
		continue;
	}
	if (hash_file('sha256', $orig) !== hash_file('sha256', $ex)) {
		echo "MISMATCH {$b}\n";
		$bad++;
	}
}

@unlink($fzc);
echo 'fzc_bytes=' . $fzcBytes . ' verify=' . ($bad === 0 ? 'ok' : "fail({$bad})") . "\n";
exit($bad === 0 ? 0 : 1);
