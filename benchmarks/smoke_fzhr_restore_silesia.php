#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
fractal_zip_ensure_folder_logical_bundle_loaded();

$zipPath = $repo . DIRECTORY_SEPARATOR . 'test_files78' . DIRECTORY_SEPARATOR . 'silesia.zip';
if (!is_file($zipPath)) {
	fwrite(STDERR, "SKIP: no silesia.zip\n");
	exit(0);
}
$zip = (string) file_get_contents($zipPath);
$logical = fractal_zip_resolve_folder_logical_bundle(array('silesia.zip' => $zip));
$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzhr_sm_' . getmypid();
@mkdir($td, 0700, true);
$memberFiles = array();
foreach ($logical['members'] as $name => $bytes) {
	$p = $td . DIRECTORY_SEPARATOR . $name;
	file_put_contents($p, $bytes);
	$memberFiles[$name] = $p;
}
$dest = $td . DIRECTORY_SEPARATOR . 'out';
@mkdir($dest, 0700, true);
$fz = new fractal_zip(256, false, true, null, false);
$ok = fractal_zip_apply_folder_disk_restore($dest, $memberFiles, $logical['restore'], $fz);
$rebuilt = $dest . DIRECTORY_SEPARATOR . 'silesia.zip';
$sem = is_file($rebuilt) && fractal_zip_folder_container_semantic_files_equal($zipPath, $rebuilt);
$sha = is_file($rebuilt) && sha1_file($rebuilt) === sha1_file($zipPath);
$kind = $logical['restore']['silesia.zip']['kind'] ?? null;
fwrite(STDOUT, "restore_ok=" . ($ok ? '1' : '0') . " kind={$kind} semantic=" . ($sem ? '1' : '0') . " sha1=" . ($sha ? '1' : '0') . "\n");
// ~267 MB of extracted members per run; leaked runs fill tmpfs /tmp.
$rmTree = static function (string $dir) use (&$rmTree): void {
	foreach (glob($dir . DIRECTORY_SEPARATOR . '{,.}[!.,!..]*', GLOB_BRACE) ?: array() as $p) {
		is_dir($p) && !is_link($p) ? $rmTree($p) : @unlink($p);
	}
	@rmdir($dir);
};
$rmTree($td);
exit($ok && $sem ? 0 : 1);
