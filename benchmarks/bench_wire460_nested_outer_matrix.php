#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Systematic nested-outer matrix for words4096 + phda9_xml mono stack.
 *
 * Phase 1: phda9-inner stack sweep (no .fz).
 * Phase 2: full wire for top inner stacks × container outer (zstd/zpaq/auto) ± shootout.
 *
 * Usage:
 *   php benchmarks/bench_wire460_nested_outer_matrix.php [--pages=96] [--top=5] [--wire-only] [--dict=PATH]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPCACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_process_guard.php';
fractal_zip_process_guard_register_cli();

$pageLimit = 96;
$topN = 5;
$wireOnly = false;
$dictPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
$outJson = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR
	. '.enwik8_wire460_nested_outer_matrix.json';

foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--top=')) {
		$topN = max(1, (int) substr($arg, 6));
	} elseif (str_starts_with($arg, '--dict=')) {
		$dictPath = substr($arg, 7);
	} elseif (str_starts_with($arg, '--out=')) {
		$outJson = substr($arg, 6);
	} elseif ($arg === '--wire-only') {
		$wireOnly = true;
	}
}

$outJson = preg_replace('/\.json$/', '_' . $pageLimit . 'p.json', $outJson) ?? $outJson;
$sweepOut = preg_replace('/nested_outer_matrix/', 'phda9_inner_outer_sweep', $outJson) ?? $outJson;

$report = array(
	'pages' => $pageLimit,
	'dict' => $dictPath,
	'phase1' => null,
	'phase2' => array(),
);

if (!$wireOnly) {
	$cmd = 'php ' . escapeshellarg($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_phda9_inner_outer_sweep.php')
		. ' --pages=' . (int) $pageLimit
		. ' --dict=' . escapeshellarg($dictPath)
		. ' --out=' . escapeshellarg($sweepOut);
	echo "=== phase1 phda9 inner sweep @{$pageLimit}p ===\n";
	passthru($cmd, $ret);
	if ($ret !== 0) {
		exit($ret);
	}
	$phase1 = json_decode((string) file_get_contents($sweepOut), true);
	if (!is_array($phase1)) {
		fwrite(STDERR, "bad sweep json {$sweepOut}\n");
		exit(1);
	}
	$report['phase1'] = $phase1;
} else {
	$phase1 = json_decode((string) file_get_contents($sweepOut), true);
	if (!is_array($phase1)) {
		fwrite(STDERR, "missing sweep json {$sweepOut}; run without --wire-only\n");
		exit(1);
	}
	$report['phase1'] = $phase1;
}

$stacks = array('none');
$rawBytes = (int) ($phase1['phda9_raw_bytes'] ?? 0);
foreach (($phase1['stacks'] ?? array()) as $row) {
	$sid = (string) ($row['stack_id'] ?? '');
	if ($sid === '' || $sid === 'shootout' || empty($row['roundtrip_ok'])) {
		continue;
	}
	$rowBytes = (int) ($row['bytes'] ?? PHP_INT_MAX);
	if ($rawBytes > 0 && $rowBytes >= $rawBytes) {
		continue;
	}
	if (!in_array($sid, $stacks, true)) {
		$stacks[] = $sid;
	}
	if (count($stacks) >= $topN + 1) {
		break;
	}
}
if (count($stacks) === 1) {
	echo "phase1: raw phda9 inner smallest — wire matrix uses container outer only\n";
}

$outers = array('zstd', 'zpaq', 'auto');
$shootModes = array(false, true);
$baseCase = 'split_inner_phda9_xml_single_stream_lstm_words4096_dict';
$probe = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_enwik8_wire_slice_probe.php';

foreach ($stacks as $stackId) {
	foreach ($shootModes as $shootOn) {
		if ($shootOn && $stackId !== 'none') {
			continue;
		}
		foreach ($outers as $outer) {
			$label = 'stack_' . ($stackId === 'none' ? 'none' : $stackId)
				. ($shootOn ? '_shootout' : '')
				. '_outer_' . $outer;
			$env = array(
				'FRACTAL_ZIP_PAQ_PHDA9_DICT' => $dictPath,
				'FRACTAL_ZIP_TEXT_INNER_STACK' => $stackId,
				'FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT' => $shootOn ? '1' : '0',
			);
			if ($shootOn) {
				$env['FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_STACKS'] = implode(':', array_filter(
					array('zpaq9_brotli11', 'zpaq9_zstd22', 'zpaq9_gzip9', 'gzip9_zpaq9', 'brotli11_gzip9', 'zpaq9_7z')
				));
			}
			$tmpOut = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_nested_' . bin2hex(random_bytes(4)) . '.json';
			$envExport = '';
			foreach ($env as $k => $v) {
				$envExport .= escapeshellarg($k) . '=' . escapeshellarg((string) $v) . ' ';
			}
			$nice = 'nice -n 19';
			if (is_executable('/usr/bin/ionice')) {
				$nice = 'ionice -c 3 ' . $nice;
			}
			$cmd = 'env ' . $envExport
				. $nice . ' php -d memory_limit=2048M ' . escapeshellarg($probe)
				. ' --pages=' . (int) $pageLimit
				. ' --outer=' . escapeshellarg($outer)
				. ' --case=' . escapeshellarg($baseCase)
				. ' --verify-rt'
				. ' --out-json=' . escapeshellarg($tmpOut);
			echo "=== phase2 wire {$label} ===\n";
			$t0 = microtime(true);
			passthru($cmd, $ret);
			$sec = round(microtime(true) - $t0, 2);
			$row = array(
				'label' => $label,
				'inner_stack' => $stackId,
				'shootout' => $shootOn,
				'container_outer' => $outer,
				'exit' => $ret,
				'seconds' => $sec,
			);
			if ($ret === 0 && is_file($tmpOut)) {
				$probeJson = json_decode((string) file_get_contents($tmpOut), true);
				$first = null;
				if (is_array($probeJson)) {
					if (isset($probeJson['rows'][0]) && is_array($probeJson['rows'][0])) {
						$first = $probeJson['rows'][0];
					} elseif (isset($probeJson[0]) && is_array($probeJson[0])) {
						$first = $probeJson[0];
					}
				}
				if ($first !== null) {
					$row['fzc_bytes'] = (int) ($first['fzc_bytes'] ?? 0);
					$row['outer_codec'] = $first['outer_codec'] ?? null;
					$row['roundtrip_ok'] = $first['roundtrip_ok'] ?? null;
					if (!empty($first['inner_fold_trailer_codec'])) {
						$row['inner_fold_trailer_codec'] = $first['inner_fold_trailer_codec'];
					}
				}
				@unlink($tmpOut);
			}
			$report['phase2'][] = $row;
		}
	}
}

usort($report['phase2'], static function (array $a, array $b): int {
	return ((int) ($a['fzc_bytes'] ?? PHP_INT_MAX)) <=> ((int) ($b['fzc_bytes'] ?? PHP_INT_MAX));
});
$report['best_wire'] = $report['phase2'][0] ?? null;

$json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if (!is_string($json)) {
	exit(1);
}
file_put_contents($outJson, $json);
echo $json . "\n";
