<?php
declare(strict_types=1);

/**
 * Loads optional per-server tool paths before fractal_zip is required.
 * Create fz_fractal_local_env.php in this directory (see fz_fractal_local_env.php.example).
 *
 * When FRACTAL_ZIP_ZPAQ is still unset, use /home/freement/bin/zpaq if that binary exists
 * (freement.cloud production layout — no extra env or SSH).
 */
$__fzLocalEnv = __DIR__ . DIRECTORY_SEPARATOR . 'fz_fractal_local_env.php';
if (is_file($__fzLocalEnv)) {
	require_once $__fzLocalEnv;
}
unset($__fzLocalEnv);

$__fzZpaqEnv = getenv('FRACTAL_ZIP_ZPAQ');
if ($__fzZpaqEnv === false || trim((string) $__fzZpaqEnv) === '') {
	$__fzZpaqFreement = '/home/freement/bin/zpaq';
	if (is_executable($__fzZpaqFreement)) {
		$__fzZpaqRp = realpath($__fzZpaqFreement);
		putenv('FRACTAL_ZIP_ZPAQ=' . ($__fzZpaqRp !== false ? $__fzZpaqRp : $__fzZpaqFreement));
		unset($__fzZpaqRp);
	}
}
unset($__fzZpaqEnv, $__fzZpaqFreement);
