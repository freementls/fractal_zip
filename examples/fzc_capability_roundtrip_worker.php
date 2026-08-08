#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Isolated open_container round-trip probe (subprocess target — fatal_error must not kill parent report).
 *
 * Usage: php fzc_capability_roundtrip_worker.php /path/to/file.fz
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_deploy_bootstrap.php';
fzc_examples_require_bench_json_helpers();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_capability_probes.php';

$fzc = $argv[1] ?? '';
if ($fzc === '') {
	fwrite(STDERR, "Usage: php fzc_capability_roundtrip_worker.php FZC_PATH\n");
	exit(2);
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';
$repoRoot = dirname(__DIR__);
$lib = getenv('FRACTAL_ZIP_PHP');
if ($lib === false || trim((string) $lib) === '') {
	$lib = $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
}
if (!is_file($lib)) {
	echo (bench_json_encode_try(array('ok' => false, 'error' => 'library_missing', 'files' => 0), false) ?? '{}') . "\n";
	exit(1);
}
require_once $lib;
$fz = new fractal_zip(256, false, true, null, false);
$rt = fzc_cap_probe_fzc_roundtrip($fz, $fzc);
$rt['path'] = realpath($fzc) ?: $fzc;
$js = bench_json_encode_try($rt, false);
echo ($js ?? '{"ok":false,"error":"json_encode_failed"}') . "\n";
exit(!empty($rt['ok']) ? 0 : 1);
