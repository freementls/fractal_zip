#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * @deprecated Use benchmarks/smoke_random.php --case=30 (this wrapper forwards argv with --case=30).
 * Listed in tests/run_php_smokes.sh SMOKE_PHP for phase 1 php -l (CI php-smokes workflow).
 */
if (!isset($argv)) {
	exit(1);
}
array_splice($argv, 1, 0, array('--case=30'));
require __DIR__ . DIRECTORY_SEPARATOR . 'smoke_random.php';
