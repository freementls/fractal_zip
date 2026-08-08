#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Practical cycle-encoding benchmark: synthetic cycle corpora, enwik slices, natural files.
 *
 * Δ = LTCB − gz9(raw). Δ < 0 = win vs gzip-9 on raw bytes.
 *
 * Usage:
 *   nice -n 19 php benchmarks/bench_cycle_encoding_practical.php [--pages=96] [--json]
 */

$root = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_recipes.php';
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_codec.php';
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_cycle_preprocess.php';
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';

$pages = 96;
$jsonOut = false;
foreach($argv as $arg) {
	if(str_starts_with($arg, '--pages=')) {
		$pages = max(8, (int)substr($arg, 8));
	} elseif($arg === '--json') {
		$jsonOut = true;
	}
}

$gz = static function (string $s): int {
	$z = @gzdeflate($s, 9);
	return $z === false ? strlen($s) : strlen($z);
};

$sidecarBytes = static function (array|string $sidecar): int {
	if(is_string($sidecar)) {
		return strlen($sidecar);
	}
	if(isset($sidecar['binary']) && is_string($sidecar['binary'])) {
		return strlen($sidecar['binary']);
	}
	$j = json_encode($sidecar);
	return is_string($j) ? strlen($j) : 0;
};

/**
 * @return list<array{label:string, bytes:string, kind:string}>
 */
function cycle_practical_corpora(string $root, int $pages): array
{
	$out = array();
	foreach(cycle_encoding_recipe_specs() as $case => $spec) {
		$expanded = cycle_encoding_expand_spec($spec);
		$out[] = array(
			'label' => 'cycle_' . (string)$case,
			'bytes' => (string)$expanded['bytes'],
			'kind' => 'cycle_' . (string)$spec['tier'],
		);
	}
	$enwik = $root . DIRECTORY_SEPARATOR . 'enwik8';
	if(is_file($enwik)) {
		$split = fractal_zip_enwik_split_shell_and_text(
			(string)file_get_contents($enwik, false, null, 0, min(12_000_000, $pages * 40_000)),
			$pages
		);
		if($split !== null) {
			$text = '';
			foreach($split['pages'] as $pg) {
				$text .= (string)$pg['text'];
			}
			$out[] = array('label' => 'enwik_text@' . (string)$pages . 'p', 'bytes' => $text, 'kind' => 'enwik');
		}
	}
	$enwik109 = $root . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
	if(is_file($enwik109)) {
		$blob = (string)file_get_contents($enwik109, false, null, 0, min(2_000_000, $pages * 12000));
		$out[] = array('label' => 'enwik8_blob@' . (string)$pages . 'p', 'bytes' => $blob, 'kind' => 'enwik_blob');
	}
	foreach(array('test_files110/fields.c', 'test_files115/lcet10.txt', 'test_files122/plrabn12.txt') as $rel) {
		$p = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		if(is_file($p)) {
			$b = (string)file_get_contents($p);
			if(strlen($b) > 0) {
				$out[] = array('label' => basename(dirname($rel)) . '/' . basename($rel), 'bytes' => $b, 'kind' => 'natural');
			}
		}
	}
	return $out;
}

/**
 * @return list<array<string,mixed>>
 */
function cycle_practical_arms(string $bytes, ?array $recipeSpec = null, bool $withStatPred = false): array
{
	global $root;
	$rows = array();

	$gz = static function (string $s): int {
		$z = @gzdeflate($s, 9);
		return $z === false ? strlen($s) : strlen($z);
	};
	$sidecarBytes = static function (array|string $sidecar): int {
		if(is_string($sidecar)) {
			return strlen($sidecar);
		}
		if(isset($sidecar['binary']) && is_string($sidecar['binary'])) {
			return strlen($sidecar['binary']);
		}
		$j = json_encode($sidecar);
		return is_string($j) ? strlen($j) : 0;
	};

	$add = static function (
		string $name,
		callable $encode,
		callable $decode
	) use (&$rows, $bytes, $gz, $sidecarBytes): void {
		$base = $gz($bytes);
		try {
			$pre = $encode($bytes);
			$payload = (string)($pre['payload'] ?? '');
			$sidecar = $pre['sidecar'] ?? array();
			$rt = $decode($payload, is_array($sidecar) ? $sidecar : array()) === $bytes;
			$payGz = $gz($payload);
			$sc = is_array($sidecar) ? $sidecarBytes($sidecar) : 0;
			$ltc = $payGz + $sc;
			$rows[] = array(
				'arm' => $name,
				'rt' => $rt,
				'raw_gz9' => $base,
				'payload_gz9' => $payGz,
				'sidecar' => $sc,
				'ltcb' => $ltc,
				'delta' => $ltc - $base,
				'meta' => is_array($pre['meta'] ?? null) ? $pre['meta'] : array(),
			);
		} catch(Throwable $e) {
			$rows[] = array(
				'arm' => $name,
				'rt' => false,
				'raw_gz9' => $base,
				'payload_gz9' => 0,
				'sidecar' => 0,
				'ltcb' => 0,
				'delta' => 0,
				'meta' => array('err' => $e->getMessage()),
			);
		}
	};

	$add('gz9 raw (baseline)', static fn (string $t): array => array('payload' => $t, 'sidecar' => array(), 'meta' => array()),
		static fn (string $p, array $s): string => $p);

	if($recipeSpec !== null) {
		$oracle = cycle_encoding_oracle_bytes($recipeSpec);
		$rows[] = array(
			'arm' => 'cycle oracle (recipe)',
			'rt' => true,
			'raw_gz9' => $gz($bytes),
			'payload_gz9' => $oracle,
			'sidecar' => 0,
			'ltcb' => $oracle,
			'delta' => $oracle - $gz($bytes),
			'meta' => array('oracle' => true),
		);
	}

	$add('cycle_inner', static fn (string $t): array => fractal_zip_text_cycle_preprocess($t),
		static fn (string $p, array $s): string => fractal_zip_text_cycle_undo($p, $s));

	$add('cycle_delta', static fn (string $t): array => fractal_zip_text_cycle_preprocess($t, array('transform' => 'delta')),
		static fn (string $p, array $s): string => fractal_zip_text_cycle_undo($p, $s));

	$add('delta then gz9', static fn (string $t): array => array(
		'payload' => cycle_encoding_delta_forward($t),
		'sidecar' => array('preprocess' => 'none'),
		'meta' => array(),
	), static fn (string $p, array $s): string => cycle_encoding_delta_inverse($p));

	if($withStatPred) {
	$modelPath = $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.stat_pred_inner_model.fzpm';
	if(is_file($modelPath) && strlen($bytes) <= 512 * 1024) {
		putenv('FRACTAL_ZIP_STAT_PRED_INNER_MODEL_FILE=' . $modelPath);
		require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
		$model = fractal_zip_enwik_stat_pred_inner_frozen_model();
		$add('stat_pred frozen', static function (string $t) use ($model): array {
			return fractal_zip_text_stat_pred_preprocess($t, array('stat_model' => $model, 'frozen' => true));
		}, static function (string $p, array $s) use ($model): string {
			return fractal_zip_text_stat_pred_undo($p, array_merge($s, array(
				'vocab' => $model['vocab'],
				'bigram_succ' => $model['bigram_succ'],
			)));
		});
		$add('stat_pred→cycle', static function (string $t) use ($model): array {
			return fractal_zip_text_cycle_then_preprocess($t, 'stat_pred', array('stat_model' => $model, 'frozen' => true));
		}, static fn (string $p, array $s): string => fractal_zip_text_cycle_combo_undo($p, $s));
	}
	}

	return $rows;
}

$corpora = cycle_practical_corpora($root, $pages);
$report = array('pages' => $pages, 'corpora' => array());

	foreach($corpora as $corp) {
	$label = $corp['label'];
	$bytes = $corp['bytes'];
	fwrite(STDERR, 'probe ' . $label . ' raw=' . (string)strlen($bytes) . "\n");
	$recipeSpec = null;
	if(str_starts_with($label, 'cycle_')) {
		$case = (int)substr($label, 6);
		$recipeSpec = cycle_encoding_recipe_specs()[$case] ?? null;
	}
	$arms = cycle_practical_arms($bytes, $recipeSpec, in_array($corp['kind'], array('enwik', 'enwik_blob', 'natural'), true));
	usort($arms, static fn (array $a, array $b): int => ($a['ltcb'] ?? PHP_INT_MAX) <=> ($b['ltcb'] ?? PHP_INT_MAX));
	$base = null;
	foreach($arms as $a) {
		if($a['arm'] === 'gz9 raw (baseline)') {
			$base = (int)$a['raw_gz9'];
			break;
		}
	}
	$best = $arms[0] ?? null;
	$report['corpora'][] = array(
		'label' => $label,
		'kind' => $corp['kind'],
		'raw_bytes' => strlen($bytes),
		'raw_gz9' => $base,
		'best_arm' => $best['arm'] ?? null,
		'best_ltcb' => $best['ltcb'] ?? null,
		'best_delta' => $best['delta'] ?? null,
		'arms' => $arms,
	);
}

if($jsonOut) {
	echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
	exit(0);
}

printf("bench_cycle_encoding_practical | enwik pages=%d\n\n", $pages);
foreach($report['corpora'] as $block) {
	printf("=== %s (%s) raw=%s gz9=%s ===\n",
		$block['label'],
		$block['kind'],
		number_format((int)$block['raw_bytes']),
		number_format((int)($block['raw_gz9'] ?? 0))
	);
	printf("%-24s %8s %8s %8s %6s %8s\n", 'ARM', 'LTCB', 'Δ', 'sidecar', 'RT', 'GATE');
	foreach($block['arms'] as $r) {
		if($r['arm'] === 'gz9 raw (baseline)') {
			continue;
		}
		$gate = ($r['rt'] ?? false) && ($r['delta'] ?? 0) < 0 ? 'PASS' : 'FAIL';
		printf("%-24s %8s %+8d %8s %6s %8s\n",
			$r['arm'],
			number_format((int)$r['ltcb']),
			(int)$r['delta'],
			number_format((int)$r['sidecar']),
			($r['rt'] ?? false) ? 'ok' : 'NO',
			$gate
		);
		if(!empty($r['meta']) && ($block['kind'] ?? '') !== 'enwik') {
			$m = $r['meta'];
			if(isset($m['mode'])) {
				printf("    meta: mode=%s segments=%s cycle_bytes=%s literal_bytes=%s\n",
					(string)($m['mode'] ?? '?'),
					(string)($m['segments'] ?? '?'),
					(string)($m['cycle_bytes'] ?? '?'),
					(string)($m['literal_bytes'] ?? '?')
				);
			}
		}
	}
	printf("best: %s LTCB=%s Δ=%+d\n\n", (string)$block['best_arm'], number_format((int)($block['best_ltcb'] ?? 0)), (int)($block['best_delta'] ?? 0));
}

$cycleWins = 0;
$cycleCases = 0;
foreach($report['corpora'] as $block) {
	if(!str_starts_with((string)$block['kind'], 'cycle_')) {
		continue;
	}
	$cycleCases++;
	$best = (string)($block['best_arm'] ?? '');
	if(str_starts_with($best, 'cycle')) {
		$cycleWins++;
	}
}
printf("SUMMARY: cycle-family best arm on %d/%d synthetic cycle corpora\n", $cycleWins, $cycleCases);

fwrite(STDERR, "OK bench_cycle_encoding_practical\n");
