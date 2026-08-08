#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Gate consonant_hybrid: RT, meta trailer, wire JSON.
 *
 * Usage:
 *   php benchmarks/check_consonant_hybrid.php [--pages=64] [--full-wire] [--skip-wire]
 */

$repo = dirname(__DIR__);
$pages = 64;
$fullWire = false;
$skipWire = false;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif ($arg === '--full-wire') {
		$fullWire = true;
	} elseif ($arg === '--skip-wire') {
		$skipWire = true;
	}
}

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');

$errors = array();
$fail = static function (string $msg) use (&$errors): void {
	$errors[] = $msg;
	fwrite(STDERR, "FAIL {$msg}\n");
};

if ($pages <= 128) {
	$smoke = shell_exec('php ' . escapeshellarg($repo . '/tests/enwik_syllable_roundtrip_smoke.php') . ' 2>&1');
	if (!is_string($smoke) || !str_contains($smoke, 'ok syllabify')) {
		$fail('syllable roundtrip smoke');
	}
}

$rtOut = shell_exec('php ' . escapeshellarg($repo . '/benchmarks/diag_consonant_hybrid_phda9_rt.php') . ' ' . $pages . ' 2>&1');
if (!is_string($rtOut)) {
	$fail('RT diag produced no output');
} else {
	if (!str_contains($rtOut, 'match=yes')) {
		$fail('FZEP roundtrip mismatch');
	}
	if (!str_contains($rtOut, 'meta_loaded=yes')) {
		$fail('preprocess meta not loaded from inner-fold trailer');
	}
	if (!preg_match('/unique=(\d+)/', $rtOut, $m) || (int) $m[1] < 1) {
		$fail('skeleton unique table empty after restore');
	}
}

$wireJson = $repo . '/benchmarks/.enwik8_wire_slice_probe_' . $pages . 'p_consonant.json';
if (!$skipWire && ($fullWire || !is_file($wireJson))) {
	$cases = 'split_inner_phda9_xml_single_stream_lstm_mixed_dict,split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid';
	if ($pages >= 64) {
		$cases .= ',split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid_merged_dict';
	}
	$wireCmd = sprintf(
		'FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0 FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=0 nice -n 19 php -d memory_limit=768M %s --pages=%d --verify-rt --out-json=%s --cases=%s 2>&1',
		escapeshellarg($repo . '/benchmarks/bench_enwik8_wire_slice_probe.php'),
		$pages,
		escapeshellarg($wireJson),
		$cases
	);
	fwrite(STDERR, "[check] wire probe @{$pages}p …\n");
	shell_exec($wireCmd);
}

$baseWire = null;
$chWire = null;
if (!$skipWire) {
	if (!is_file($wireJson)) {
		$fail('missing wire JSON: ' . $wireJson);
	} else {
		$wire = json_decode((string) file_get_contents($wireJson), true);
		$byLabel = array();
		if (is_array($wire['rows'] ?? null)) {
			foreach ($wire['rows'] as $row) {
				$byLabel[(string) ($row['label'] ?? '')] = $row;
			}
		}
		$base = $byLabel['split_inner_phda9_xml_single_stream_lstm_mixed_dict'] ?? null;
		$ch = $byLabel['split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid'] ?? null;
		if (!is_array($base) || !is_array($ch)) {
			$errMsg = (string) ($ch['error'] ?? $base['error'] ?? '');
			if ($errMsg !== '') {
				$fail('wire probe error: ' . $errMsg);
			} else {
				$fail('wire JSON missing baseline or consonant_hybrid row');
			}
		} else {
			if (empty($base['roundtrip_ok'])) {
				$fail('baseline wire RT');
			}
			if (empty($ch['roundtrip_ok'])) {
				$fail('consonant_hybrid wire RT');
			}
			if (!empty($ch['error'])) {
				$fail('consonant_hybrid wire: ' . (string) $ch['error']);
			}
			$baseWire = (int) ($base['wire_fzc'] ?? $base['fzc_bytes'] ?? 0);
			$chWire = (int) ($ch['wire_fzc'] ?? $ch['fzc_bytes'] ?? 0);
			if ($baseWire <= 0 || $chWire <= 0) {
				$fail('wire bytes missing in JSON');
			}
		}
	}
}

$summaryPath = $repo . '/benchmarks/.enwik8_consonant_hybrid_' . $pages . 'p_check.json';
file_put_contents($summaryPath, json_encode(array(
	'generated' => date('c'),
	'pages' => $pages,
	'ok' => $errors === array(),
	'errors' => $errors,
	'wire_json' => is_file($wireJson) ? $wireJson : null,
	'wire_baseline' => $baseWire,
	'wire_consonant' => $chWire,
	'wire_delta' => ($baseWire !== null && $chWire !== null) ? $chWire - $baseWire : null,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

if ($errors !== array()) {
	fwrite(STDERR, "check_consonant_hybrid @{$pages}p: " . count($errors) . " failure(s)\n");
	exit(1);
}

echo "ok consonant_hybrid @{$pages}p (RT + meta trailer";
if (!$skipWire) {
	echo ' + wire';
}
echo ")\n→ {$summaryPath}\n";
if ($baseWire !== null && $chWire !== null) {
	echo 'wire baseline=' . number_format($baseWire) . ' consonant=' . number_format($chWire)
		. ' delta=' . sprintf('%+d', $chWire - $baseWire) . "\n";
}
exit(0);
