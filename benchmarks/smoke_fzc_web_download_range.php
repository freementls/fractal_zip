#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Unit-ish checks for Range / If-Range helpers (no HTTP server).
 */

$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'fzc_web_shared.php';

$fails = 0;

$saved = array();
foreach (array('HTTP_RANGE', 'HTTP_IF_RANGE') as $k) {
	$saved[$k] = $_SERVER[$k] ?? null;
}

$run = static function (string $label, callable $fn) use (&$fails): void {
	try {
		$fn();
	} catch (Throwable $e) {
		fwrite(STDERR, "FAIL {$label}: " . $e->getMessage() . "\n");
		$fails++;
	}
};

$run('suffix range', static function (): void {
	$_SERVER['HTTP_RANGE'] = 'bytes=-10';
	$err = null;
	$p = fzc_web_parse_download_range_for_get(100, $err);
	if ($err !== null || $p === null || $p[0] !== 90 || $p[1] !== 99) {
		throw new RuntimeException('expected 90-99');
	}
	unset($_SERVER['HTTP_RANGE']);
});

$run('416 past end', static function (): void {
	$_SERVER['HTTP_RANGE'] = 'bytes=1000-';
	$err = null;
	$p = fzc_web_parse_download_range_for_get(50, $err);
	if ($err !== 'range_not_satisfiable' || $p !== null) {
		throw new RuntimeException('expected unsatisfiable');
	}
	unset($_SERVER['HTTP_RANGE']);
});

$run('if-range etag match', static function (): void {
	$ok = fzc_web_download_if_range_matches('"abc"', 1234567890, '"abc"');
	if (!$ok) {
		throw new RuntimeException('strong etag should match');
	}
});

$run('if-range weak etag', static function (): void {
	$ok = fzc_web_download_if_range_matches('"xyz"', 0, 'W/"xyz"');
	if (!$ok) {
		throw new RuntimeException('weak etag should match');
	}
});

$run('if-range etag mismatch', static function (): void {
	$ok = fzc_web_download_if_range_matches('"a"', 0, '"b"');
	if ($ok) {
		throw new RuntimeException('should not match');
	}
});

$run('if-range http-date match', static function (): void {
	$mtime = 1700000000;
	$httpDate = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
	$ok = fzc_web_download_if_range_matches('"ir"', $mtime, $httpDate);
	if (!$ok) {
		throw new RuntimeException('HTTP-date within ±1s should match mtime');
	}
});

$run('if-range http-date far mismatch', static function (): void {
	$mtime = 1700000000;
	$far = gmdate('D, d M Y H:i:s', $mtime + 10) . ' GMT';
	$ok = fzc_web_download_if_range_matches('"ir"', $mtime, $far);
	if ($ok) {
		throw new RuntimeException('HTTP-date >1s off should not match');
	}
});

$run('if-none-match weak list', static function (): void {
	if (!fzc_web_if_none_match_implies_not_modified('"abc"', 'W/"abc"')) {
		throw new RuntimeException('weak token should match');
	}
});

$run('if-none-match star alone does not short-circuit', static function (): void {
	if (fzc_web_if_none_match_implies_not_modified('"abc"', '*')) {
		throw new RuntimeException('* must not force 304');
	}
});

$run('if-none-match comma list', static function (): void {
	if (!fzc_web_if_none_match_implies_not_modified('"mid"', '"a", W/"mid", "z"')) {
		throw new RuntimeException('middle weak token should match');
	}
});

foreach ($saved as $k => $v) {
	if ($v === null) {
		unset($_SERVER[$k]);
	} else {
		$_SERVER[$k] = $v;
	}
}

if ($fails > 0) {
	echo "FAIL ({$fails})\n";
	exit(1);
}
echo "OK fzc_web_download_range\n";
exit(0);
