#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Cycle-encoding win scorecard: oracle / cycle_inner LTCB vs gz9 on fixtures + niche hybrids.
 *
 * Δ = LTCB − gz9(raw). Δ < 0 = WIN.
 *
 * Usage:
 *   nice -n 19 php benchmarks/bench_cycle_wins.php [--json] [--mi]
 */

$root = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_recipes.php';
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_codec.php';
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_cycle_preprocess.php';

$jsonOut = in_array('--json', $argv, true);
$withMi = in_array('--mi', $argv, true);

$gz = static function (string $s): int {
	$z = @gzdeflate($s, 9);
	return $z === false ? strlen($s) : strlen($z);
};

$sidecarBytes = static function (array $sidecar) use ($root): int {
	if(isset($sidecar['binary']) && is_string($sidecar['binary'])) {
		return strlen($sidecar['binary']);
	}
	$j = json_encode($sidecar);
	return is_string($j) ? strlen($j) : 0;
};

/**
 * @return list<array{label:string,bytes:string,tier:string}>
 */
function cycle_wins_corpora(string $root, bool $withMi): array
{
	$out = array();
	foreach(cycle_encoding_recipe_specs() as $case => $spec) {
		if(!$withMi && (int)$spec['length'] >= 500_000) {
			continue;
		}
		$expanded = cycle_encoding_expand_spec($spec);
		$out[] = array(
			'label' => 'cycle_' . (string)$case,
			'bytes' => (string)$expanded['bytes'],
			'tier' => (string)$spec['tier'],
		);
	}
	$hybridDir = $root . DIRECTORY_SEPARATOR . 'test_files196';
	$hybridFile = $hybridDir . DIRECTORY_SEPARATOR . 'hybrid.txt';
	if(is_file($hybridFile)) {
		$out[] = array(
			'label' => 'test_files196',
			'bytes' => (string)file_get_contents($hybridFile),
			'tier' => 'hybrid-natural+cycle',
		);
	}
	$hybrid197 = $root . DIRECTORY_SEPARATOR . 'test_files197' . DIRECTORY_SEPARATOR . 'hybrid.txt';
	if(is_file($hybrid197)) {
		$out[] = array(
			'label' => 'test_files197',
			'bytes' => (string)file_get_contents($hybrid197),
			'tier' => 'hybrid-1MiB-torus',
		);
	}
	$syn = $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.cycle_synthetic_versioned.bin';
	if(is_file($syn)) {
		$out[] = array(
			'label' => 'synthetic_versioned',
			'bytes' => (string)file_get_contents($syn),
			'tier' => 'versioned-doc',
		);
	}
	return $out;
}

/**
 * @return array<string,mixed>
 */
function cycle_wins_probe(string $label, string $bytes, string $tier): array
{
	global $gz, $sidecarBytes;
	$n = strlen($bytes);
	$rawGz = $gz($bytes);
	$t0 = microtime(true);
	$pre = fractal_zip_text_cycle_preprocess($bytes);
	$encMs = round((microtime(true) - $t0) * 1000, 1);
	$payloadGz = $gz((string)$pre['payload']);
	$sc = $sidecarBytes($pre['sidecar']);
	$ltcb = $payloadGz + $sc;
	$rt = fractal_zip_text_cycle_undo((string)$pre['payload'], $pre['sidecar']) === $bytes;
	$oracle = null;
	$det = cycle_encoding_detect_whole($bytes, false);
	if($det !== null) {
		$oracle = cycle_encoding_oracle_bytes($det);
	}
	$delta = $ltcb - $rawGz;
	$oracleDelta = ($oracle !== null) ? ($oracle - $rawGz) : null;
	$deltaPre = fractal_zip_text_cycle_preprocess($bytes, array('transform' => 'delta'));
	$deltaLtcb = $gz((string)$deltaPre['payload']) + $sidecarBytes($deltaPre['sidecar']);
	$deltaRt = fractal_zip_text_cycle_undo((string)$deltaPre['payload'], $deltaPre['sidecar']) === $bytes;
	$deltaDelta = $deltaLtcb - $rawGz;
	return array(
		'label' => $label,
		'tier' => $tier,
		'bytes' => $n,
		'raw_gz9' => $rawGz,
		'cycle_inner_ltcb' => $ltcb,
		'cycle_delta_ltcb' => $deltaLtcb,
		'cycle_oracle' => $oracle,
		'delta_ltcb' => $delta,
		'delta_cycle_delta' => $deltaDelta,
		'delta_oracle' => $oracleDelta,
		'mode' => (string)($pre['meta']['mode'] ?? ''),
		'sidecar_B' => $sc,
		'encode_ms' => $encMs,
		'rt' => $rt,
		'rt_delta' => $deltaRt,
		'gate_ltcb' => ($rt && $delta < 0) ? 'WIN' : 'FAIL',
		'gate_delta' => ($deltaRt && $deltaDelta < 0) ? 'WIN' : 'FAIL',
		'gate_oracle' => ($oracle !== null && $oracleDelta !== null && $oracleDelta < 0) ? 'WIN' : '—',
	);
}

$synPath = $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.cycle_synthetic_versioned.bin';
if(!is_file($synPath)) {
	require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	$enwik = $root . DIRECTORY_SEPARATOR . 'enwik8';
	if(is_file($enwik)) {
		$blob = (string)file_get_contents($enwik, false, null, 0, 4_000_000);
		$split = fractal_zip_enwik_split_shell_and_text($blob, 512);
		if($split !== null && $split['pages'] !== array()) {
			$pg = $split['pages'][0];
			$article = (string)($pg['text'] ?? substr($blob, (int)$pg['start'], (int)$pg['len']));
			if(strlen($article) >= 500) {
				$base = substr($article, 0, min(3700, strlen($article)));
				$variants = array($base);
				for($v = 1; $v < 40; $v++) {
					$cur = $base;
					$len = strlen($cur);
					$edits = max(1, (int)round($len * 0.02));
					for($e = 0; $e < $edits; $e++) {
						$pos = ($v * 997 + $e * 131) % max(1, $len);
						$cur[$pos] = chr(97 + (($v + $e) % 26));
					}
					$variants[] = $cur;
				}
				file_put_contents($synPath, implode("\n", $variants));
			}
		}
	}
}

$rows = array();
foreach(cycle_wins_corpora($root, $withMi) as $corp) {
	$rows[] = cycle_wins_probe($corp['label'], $corp['bytes'], $corp['tier']);
}

if($jsonOut) {
	echo json_encode(array('metric' => 'cycle_inner_ltcb_vs_gz9', 'cases' => $rows), JSON_PRETTY_PRINT) . "\n";
	exit(0);
}

printf("\nCycle win scorecard (Δ = LTCB − gz9; negative = WIN)\n\n");
printf("%-18s %-16s %10s %10s %10s %8s %8s %6s %s\n",
	'label', 'tier', 'raw_gz9', 'ltcb', 'oracle', 'Δ_ltcb', 'Δ_or', 'sc_B', 'gate');
printf("%s\n", str_repeat('-', 110));
$wins = 0;
foreach($rows as $r) {
	if($r['gate_ltcb'] === 'WIN') {
		$wins++;
	}
	printf("%-18s %-16s %10s %10s %10s %8s %8s %6d %s/%s\n",
		$r['label'],
		$r['tier'],
		number_format((int)$r['raw_gz9']),
		number_format((int)$r['cycle_inner_ltcb']),
		$r['cycle_oracle'] === null ? '—' : number_format((int)$r['cycle_oracle']),
		(string)(int)$r['delta_ltcb'],
		$r['delta_oracle'] === null ? '—' : (string)(int)$r['delta_oracle'],
		(int)$r['sidecar_B'],
		$r['gate_ltcb'],
		$r['gate_oracle']
	);
	if(isset($r['cycle_delta_ltcb'])) {
		printf("  cycle_delta: ltcb=%s Δ=%s gate=%s\n",
			number_format((int)$r['cycle_delta_ltcb']),
			(string)(int)$r['delta_cycle_delta'],
			(string)$r['gate_delta']
		);
	}
}
printf("%s\n", str_repeat('-', 110));
printf("cycle_inner WIN: %d / %d cases\n", $wins, count($rows));
fwrite(STDERR, "OK bench_cycle_wins\n");
