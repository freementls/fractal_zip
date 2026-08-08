#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare two fzc_capability_report.php JSON outputs (local vs live).
 *
 * Usage:
 *   php examples/fzc_capability_compare.php local.json live.json
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_deploy_bootstrap.php';
fzc_examples_require_bench_json_helpers();

$jsonOut = false;
$args = array_slice($argv ?? array(), 1);
$paths = array();
foreach ($args as $a) {
	if ($a === '--json') {
		$jsonOut = true;
	} elseif ($a === '--help' || $a === '-h') {
		fwrite(STDOUT, "Usage: php fzc_capability_compare.php [--json] <local.json> <live.json>\n");
		exit(0);
	} else {
		$paths[] = $a;
	}
}
if (count($paths) < 2) {
	fwrite(STDERR, "Usage: php fzc_capability_compare.php [--json] <local.json> <live.json>\n");
	exit(2);
}

$local = bench_json_decode_file_assoc_try($paths[0], 'fzc_capability_compare local');
$live = bench_json_decode_file_assoc_try($paths[1], 'fzc_capability_compare live');
if ($local === null || $live === null) {
	exit(2);
}

$fail = 0;
$emit = static function (string $line) use ($jsonOut): void {
	if (!$jsonOut) {
		fwrite(STDOUT, $line);
	}
};

$emit("=== fractal_zip capability diff ===\n");
$emit('local: ' . ($local['profile']['label'] ?? '?') . ' (' . ($local['profile']['sapi'] ?? '?') . ")\n");
$emit('live:  ' . ($live['profile']['label'] ?? '?') . ' (' . ($live['profile']['sapi'] ?? '?') . ")\n\n");

$toolKeys = array('zpaq', '7z', 'brotli', 'xz', 'zstd', 'arc');
$emit("[External tools]\n");
foreach ($toolKeys as $tk) {
	$lp = $local['external_tools'][$tk]['path'] ?? null;
	$rp = $live['external_tools'][$tk]['path'] ?? null;
	if ($lp === $rp) {
		continue;
	}
	$fail++;
	$emit("  {$tk}: local=" . ($lp ?? '(null)') . " live=" . ($rp ?? '(null)') . "\n");
}

$emit("\n[PHP functions]\n");
$profLocal = $local['profile'] ?? $local['php'] ?? array();
$profLive = $live['profile'] ?? $live['php'] ?? array();
foreach (array('proc_open', 'shell_exec', 'exec') as $fn) {
	$lv = !empty($profLocal['functions'][$fn]);
	$rv = !empty($profLive['functions'][$fn]);
	if ($lv !== $rv) {
		$fail++;
		$emit("  {$fn}: local=" . ($lv ? 'yes' : 'no') . ' live=' . ($rv ? 'yes' : 'no') . "\n");
	}
}

$lwl = $local['web_limits']['max_extract_archive_bytes'] ?? null;
$rwl = $live['web_limits']['max_extract_archive_bytes'] ?? null;
if ($lwl !== null && $rwl !== null && $lwl !== $rwl) {
	$fail++;
	$emit("\n[Web limits]\n");
	$emit("  max_extract_archive_bytes: local={$lwl} live={$rwl}\n");
}

$emit("\n[Live host gaps (error/warn only)]\n");
$liveGaps = $live['parity_gaps'] ?? array();
$liveActionable = array();
foreach ($liveGaps as $g) {
	$sev = (string) ($g['severity'] ?? 'info');
	if ($sev === 'error' || $sev === 'warn') {
		$liveActionable[] = $g;
	}
}
if ($liveActionable === array()) {
	$emit("  (none — install tools / PHP limits if extract still fails; see info gaps in JSON)\n");
} else {
	foreach ($liveActionable as $g) {
		$sev = (string) ($g['severity'] ?? 'info');
		$emit("  [{$sev}] " . ($g['message'] ?? '') . "\n");
		if (!empty($g['fix'])) {
			$emit('       fix: ' . $g['fix'] . "\n");
		}
		if ($sev === 'error') {
			$fail++;
		}
	}
}

$lrt = $local['fzc_roundtrip'] ?? null;
$rrt = $live['fzc_roundtrip'] ?? null;
if (is_array($lrt) && is_array($rrt) && !empty($lrt['ok']) !== !empty($rrt['ok'])) {
	$fail++;
	$emit("\n[Round-trip mismatch]\n");
	$emit('  local ok=' . (!empty($lrt['ok']) ? 'yes' : 'no') . ' live ok=' . (!empty($rrt['ok']) ? 'yes' : 'no') . "\n");
	if (!empty($rrt['error'])) {
		$emit('  live error: ' . $rrt['error'] . "\n");
	}
}

$summary = array(
	'ok' => $fail === 0,
	'material_diff_count' => $fail,
	'local_label' => $local['profile']['label'] ?? null,
	'live_label' => $live['profile']['label'] ?? null,
	'live_actionable_gaps' => $liveActionable,
);
if ($jsonOut) {
	$js = bench_json_encode_try($summary, true);
	echo ($js ?? '{}') . "\n";
	exit($fail > 0 ? 1 : 0);
}

fwrite(STDOUT, "\n" . ($fail > 0 ? "RESULT: {$fail} material difference(s) — fix live host before expecting web extract parity.\n" : "RESULT: no material tool/PHP gaps detected (still verify same .fz round-trip on live).\n"));
exit($fail > 0 ? 1 : 0);
