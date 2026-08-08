<?php
declare(strict_types=1);

/**
 * A/B: single-member folder encode with deep_unwrap on vs off (gzip-proxy and optional .fz).
 *
 * Usage:
 *   php benchmarks/compare_peel_deep_unwrap_silesia.php
 *   php benchmarks/compare_peel_deep_unwrap_silesia.php --fzc
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$runFzc = in_array('--fzc', $argv, true);
$members = array(
	'test_files133/xml',
	'test_files133/mozilla',
	'test_files133/samba',
	'test_files83/SF2-US-caravan-expanded.bin',
);
foreach (array_slice($argv, 1) as $arg) {
	if (str_starts_with($arg, '--members=')) {
		$members = array_values(array_filter(array_map('trim', explode(',', substr($arg, 10)))));
	}
}

/**
 * @return array{gz1: int, fzc: int, ok: bool, err: string}
 */
function peel_ab_encode_member(string $repo, string $relPath, bool $deepOn, bool $runFzc): array {
	$src = $repo . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
	if (!is_file($src)) {
		return array('gz1' => 0, 'fzc' => 0, 'ok' => false, 'err' => 'missing');
	}
	$workRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_peel_ab_' . getmypid() . '_' . bin2hex(random_bytes(4));
	$caseDir = $workRoot . DIRECTORY_SEPARATOR . 'case';
	mkdir($caseDir, 0755, true);
	$base = basename($relPath);
	copy($src, $caseDir . DIRECTORY_SEPARATOR . $base);
	$raw = file_get_contents($src);
	if ($raw === false) {
		peel_ab_rmdir($workRoot);
		return array('gz1' => 0, 'fzc' => 0, 'ok' => false, 'err' => 'read fail');
	}
	if ($deepOn) {
		putenv('FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP=1');
		putenv('FRACTAL_ZIP_MEMBER_DEEP_UNWRAP=1');
		fractal_zip_ensure_literal_pac_stack_loaded();
		list($bytes) = fractal_zip_literal_deep_unwrap_with_layers($base, $raw, false);
	} else {
		putenv('FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP=0');
		putenv('FRACTAL_ZIP_MEMBER_DEEP_UNWRAP=0');
		$bytes = $raw;
	}
	$gz = @gzdeflate($bytes, 1);
	$gz1 = ($gz !== false) ? strlen($gz) : 0;
	$fzcN = 0;
	if ($runFzc) {
		$fzcPath = $caseDir . '.fz';
		try {
			$fz = new fractal_zip(256, false, true, null, false);
			ob_start();
			try {
				$fz->zip_folder($caseDir, false);
			} finally {
				ob_end_clean();
			}
			if (is_file($fzcPath)) {
				$fzcN = (int) filesize($fzcPath);
				@unlink($fzcPath);
			}
		} catch (Throwable $e) {
			peel_ab_rmdir($workRoot);
			return array('gz1' => $gz1, 'fzc' => 0, 'ok' => false, 'err' => $e->getMessage());
		}
	}
	peel_ab_rmdir($workRoot);
	return array('gz1' => $gz1, 'fzc' => $fzcN, 'ok' => true, 'err' => '');
}

function peel_ab_rmdir(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $fi) {
		$fi->isDir() ? @rmdir($fi->getPathname()) : @unlink($fi->getPathname());
	}
	@rmdir($dir);
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_literal_deep_unwrap.php';

echo "# deep_unwrap A/B (gzdeflate level 1 on member bytes";
if ($runFzc) {
	echo " + zip_folder .fz";
}
echo ")\n";
echo str_pad('member', 42) . str_pad('gz_off', 10) . str_pad('gz_on', 10) . str_pad('gz_save', 10);
if ($runFzc) {
	echo str_pad('fzc_off', 10) . str_pad('fzc_on', 10) . str_pad('fzc_save', 10);
}
echo "\n";

$jsonRows = array();
foreach ($members as $rel) {
	$off = peel_ab_encode_member($repo, $rel, false, $runFzc);
	$on = peel_ab_encode_member($repo, $rel, true, $runFzc);
	if (!$off['ok'] || !$on['ok']) {
		fwrite(STDERR, "skip {$rel}: " . ($off['err'] ?: $on['err']) . "\n");
		continue;
	}
	$gzSave = $off['gz1'] - $on['gz1'];
	$line = str_pad($rel, 42) . str_pad((string) $off['gz1'], 10) . str_pad((string) $on['gz1'], 10)
		. str_pad(($gzSave >= 0 ? (string) $gzSave : '+' . abs($gzSave)), 10);
	if ($runFzc) {
		$fzcSave = $off['fzc'] - $on['fzc'];
		$line .= str_pad((string) $off['fzc'], 10) . str_pad((string) $on['fzc'], 10)
			. str_pad(($fzcSave >= 0 ? (string) $fzcSave : '+' . abs($fzcSave)), 10);
	}
	echo $line . "\n";
	$jsonRows[] = array(
		'member' => $rel,
		'gz_off' => $off['gz1'],
		'gz_on' => $on['gz1'],
		'fzc_off' => $off['fzc'],
		'fzc_on' => $on['fzc'],
	);
}

$outJson = getenv('PEEL_SILESIA_AB_JSON');
if (is_string($outJson) && $outJson !== '' && $jsonRows !== array()) {
	file_put_contents($outJson, json_encode(array('generated' => date('c'), 'rows' => $jsonRows), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}
