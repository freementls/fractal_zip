#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Smoke: web extract compatibility gate helpers are callable and stable.
 */

$root = dirname(__DIR__);
$shared = $root . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'fzc_web_shared.php';
if (!is_file($shared)) {
	fwrite(STDERR, "missing {$shared}\n");
	exit(1);
}

require_once $shared;

$origGate = getenv('FZC_WEB_ENFORCE_EXTRACT_COMPAT');
putenv('FZC_WEB_ENFORCE_EXTRACT_COMPAT=1');
if (!fzc_web_extract_compat_gate_enabled()) {
	fwrite(STDERR, "gate flag expected enabled\n");
	exit(1);
}
putenv('FZC_WEB_ENFORCE_EXTRACT_COMPAT=0');
if (fzc_web_extract_compat_gate_enabled()) {
	fwrite(STDERR, "gate flag expected disabled\n");
	exit(1);
}

fzc_web_load_fractal_zip();

$gaps = fzc_web_collect_extract_compat_gaps();
if ($origGate === false) {
	putenv('FZC_WEB_ENFORCE_EXTRACT_COMPAT');
} else {
	putenv('FZC_WEB_ENFORCE_EXTRACT_COMPAT=' . $origGate);
}

if (!is_array($gaps)) {
	fwrite(STDERR, "expected gaps array\n");
	exit(1);
}

foreach ($gaps as $g) {
	if (!is_array($g) || !isset($g['id'], $g['message'], $g['fix'])) {
		fwrite(STDERR, "gap row missing id/message/fix\n");
		exit(1);
	}
}

fwrite(STDOUT, "OK smoke_fzc_web_extract_compat_gate\n");
exit(0);
