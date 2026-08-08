#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Compare inner preprocess arms on the same enwik text slice (LTCB vs gz9).
 *
 * Δ = (gz9(payload) + sidecar) − gz9(raw). Δ < 0 = PASS.
 *
 * Usage: nice -n 19 php benchmarks/bench_inner_preprocess_compare.php [pages=96]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_bio_template_ifs.php';
require_once '/srv/http/spiral/src/SpiralSidecar.php';

$pages = isset($argv[1]) ? max(8, (int) $argv[1]) : 96;
$enwik = $repo . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($enwik)) {
	fwrite(STDERR, "enwik8 missing\n");
	exit(1);
}

$split = fractal_zip_enwik_split_shell_and_text(
	(string) file_get_contents($enwik, false, null, 0, min(12_000_000, $pages * 40_000)),
	$pages
);
if ($split === null) {
	fwrite(STDERR, "split failed\n");
	exit(1);
}
$text = '';
foreach ($split['pages'] as $pg) {
	$text .= (string) $pg['text'];
}

$gz = static function (string $s): int {
	$z = gzdeflate($s, 9);
	return $z === false ? strlen($s) : strlen($z);
};

$sidecarBytes = static function (array|string $sidecar): int {
	if (is_string($sidecar)) {
		return strlen($sidecar);
	}
	if (isset($sidecar['binary']) && is_string($sidecar['binary'])) {
		return strlen($sidecar['binary']);
	}
	$j = json_encode($sidecar);
	return is_string($j) ? strlen($j) : 0;
};

$base = $gz($text);
$rows = array();

$probe = static function (
	string $name,
	string $text,
	callable $encode,
	callable $decode
) use ($gz, $sidecarBytes, $base, &$rows): void {
	try {
		$pre = $encode($text);
		$payload = (string) ($pre['payload'] ?? '');
		$sidecarRaw = $pre['sidecar'] ?? array();
		$rest = $decode($payload, $sidecarRaw);
		$rt = $rest === $text;
		$payGz = $gz($payload);
		$sc = $sidecarBytes($sidecarRaw);
		$ltc = $payGz + $sc;
		$delta = $ltc - $base;
		$rows[] = array(
			'name' => $name,
			'rt' => $rt,
			'ltc' => $ltc,
			'delta' => $delta,
			'gate' => $rt && $delta < 0 ? 'PASS' : 'FAIL',
			'payload' => $payGz,
			'sidecar' => $sc,
			'extra' => is_array($pre['meta'] ?? null) ? $pre['meta'] : array(),
		);
	} catch (Throwable $e) {
		$rows[] = array(
			'name' => $name,
			'rt' => false,
			'ltc' => 0,
			'delta' => 0,
			'gate' => 'FAIL',
			'payload' => 0,
			'sidecar' => 0,
			'extra' => array('err' => $e->getMessage()),
		);
	}
};

$probe('gz9 baseline (raw)', $text,
	static fn (string $t): array => array('payload' => $t, 'sidecar' => array(), 'meta' => array()),
	static fn (string $p, array $s): string => $p
);

putenv('FRACTAL_ZIP_SPIRAL_INNER=1');
putenv('FRACTAL_ZIP_SPIRAL_REPARSE=1');
putenv('FRACTAL_ZIP_SPIRAL_CODEC=5');
$probe('spiral_inner v5', $text,
	static fn (string $t): array => fractal_zip_enwik_spiral_inner_preprocess($t) ?? throw new RuntimeException('spiral unavailable'),
	static fn (string $p, array $s): string => fractal_zip_enwik_spiral_inner_restore($p, $s)
);

$modelPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.stat_pred_inner_model.fzpm';
if (is_file($modelPath)) {
	putenv('FRACTAL_ZIP_STAT_PRED_INNER_MODEL_FILE=' . $modelPath);
	$model = fractal_zip_enwik_stat_pred_inner_frozen_model();
	$probe('stat_pred frozen FZPM', $text,
		static function (string $t) use ($model): array {
			return fractal_zip_text_stat_pred_preprocess($t, array(
				'stat_model' => $model,
				'frozen' => true,
			));
		},
		static function (string $p, array $s) use ($model): string {
			return fractal_zip_text_stat_pred_undo($p, array_merge($s, array(
				'vocab' => $model['vocab'],
				'bigram_succ' => $model['bigram_succ'],
			)));
		}
	);
	$probe('stat_isp frozen vocab', $text,
		static function (string $t) use ($model): array {
			return fractal_zip_text_stat_isp_preprocess($t, array(
				'vocab' => $model['vocab'],
				'vocab_index' => $model['vocab_index'],
				'frozen' => true,
			));
		},
		static function (string $p, array $s) use ($model): string {
			return fractal_zip_text_stat_isp_undo($p, array_merge($s, array(
				'vocab' => $model['vocab'],
			)));
		}
	);
}

$probe('template_ifs', $text,
	static fn (string $t): array => fractal_zip_bio_template_ifs_build($t),
	static fn (string $p, array|string $s): string => fractal_zip_bio_template_ifs_restore($p, is_string($s) ? $s : json_encode($s))
);

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_cycle_preprocess.php';
$probe('cycle_inner', $text,
	static fn (string $t): array => fractal_zip_text_cycle_preprocess($t),
	static fn (string $p, array $s): string => fractal_zip_text_cycle_undo($p, $s)
);

$qgModel = null;
if (is_file('/srv/http/quantum_grammar/src/WordRootCodec.php')) {
	require_once '/srv/http/quantum_grammar/src/WordRootCodec.php';
	$qgModel = WordRootCodec::mineVocab($text);
	$vocabPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qgwr_vocab_' . getmypid() . '.json';
	file_put_contents($vocabPath, json_encode($qgModel['vocab']));
	$probe('qg_word_root v2 frozen', $text,
		static function (string $t) use ($qgModel, $vocabPath): array {
			return fractal_zip_text_qg_word_root_preprocess($t, array(
				'qg_model' => $qgModel,
				'frozen' => true,
				'vocab_path' => $vocabPath,
			));
		},
		static function (string $p, array $s) use ($vocabPath): string {
			return fractal_zip_text_qg_word_root_undo($p, array_merge($s, array('vocab_path' => $vocabPath)));
		}
	);
	require_once '/srv/http/quantum_grammar/src/SubWordRootCodec.php';
	$subModel = SubWordRootCodec::mineVocab($text);
	$subVocabPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qgsr_vocab_' . getmypid() . '.json';
	file_put_contents($subVocabPath, json_encode($subModel['vocab']));
	$probe('qg_subword_root frozen', $text,
		static function (string $t) use ($subModel, $subVocabPath): array {
			return fractal_zip_text_qg_subword_root_preprocess($t, array(
				'qg_model' => $subModel,
				'frozen' => true,
				'vocab_path' => $subVocabPath,
			));
		},
		static function (string $p, array $s) use ($subVocabPath): string {
			return fractal_zip_text_qg_subword_root_undo($p, array_merge($s, array('vocab_path' => $subVocabPath)));
		}
	);
	require_once '/srv/http/quantum_grammar/src/HybridRootCodec.php';
	$hyModel = HybridRootCodec::mineModel($text);
	$hyVocabPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qghr_vocab_' . getmypid() . '.json';
	file_put_contents($hyVocabPath, json_encode(array(
		'word' => $hyModel['word']['vocab'],
		'sub' => $hyModel['sub']['vocab'],
	)));
	$probe('qg_hybrid_root frozen', $text,
		static function (string $t) use ($hyModel, $hyVocabPath): array {
			return fractal_zip_text_qg_hybrid_root_preprocess($t, array(
				'qg_model' => $hyModel,
				'frozen' => true,
				'vocab_path' => $hyVocabPath,
			));
		},
		static function (string $p, array $s) use ($hyVocabPath): string {
			return fractal_zip_text_qg_hybrid_root_undo($p, array_merge($s, array('vocab_path' => $hyVocabPath)));
		}
	);
}

usort($rows, static fn (array $a, array $b): int => $a['delta'] <=> $b['delta']);

printf("bench_inner_preprocess_compare | @%dp | %s bytes text | gz9 baseline=%s\n\n",
	$pages, number_format(strlen($text)), number_format($base));
printf("%-22s %8s %8s %8s %6s %s\n", 'ARM', 'LTCB', 'Δ', 'sidecar', 'RT', 'GATE');
printf("%'-22s %8s %8s %8s %6s %s\n", '', '', '', '', '', '');

foreach ($rows as $r) {
	if ($r['name'] === 'gz9 baseline (raw)') {
		continue;
	}
	printf("%-22s %8s %+8d %8s %6s %s\n",
		$r['name'],
		number_format((int) $r['ltc']),
		(int) $r['delta'],
		number_format((int) $r['sidecar']),
		($r['rt'] ?? false) ? 'ok' : 'FAIL',
		$r['gate']
	);
}

$best = null;
foreach ($rows as $r) {
	if ($r['name'] === 'gz9 baseline (raw)') {
		continue;
	}
	if ($best === null || $r['delta'] < $best['delta']) {
		$best = $r;
	}
}
if ($best !== null) {
	printf("\nbest arm: %s Δ=%+d (%s)\n", $best['name'], (int) $best['delta'], $best['gate']);
}

fwrite(STDERR, "OK bench_inner_preprocess_compare\n");
