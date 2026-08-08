#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * One-shot GATE summary for quantum_grammar + spiral research track.
 *
 * Δ = (payload + sidecar) − baseline; Δ < 0 = PASS.
 *
 * Usage: nice -n 19 php benchmarks/bench_qg_spiral_research_gate.php [--pages=96] [--quick]
 */

$repo = dirname(__DIR__);
$pages = 96;
$quick = in_array('--quick', $argv, true);
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(8, (int) substr($arg, 8));
	}
}

$rows = array();
$add = static function (string $probe, string $gate, string $detail) use (&$rows): void {
	$rows[] = array('probe' => $probe, 'gate' => $gate, 'detail' => $detail);
};

$run = static function (string $cmd): array {
	$lines = array();
	exec($cmd . ' 2>&1', $lines, $code);
	return array('code' => $code, 'out' => implode("\n", $lines));
};

// --- smokes ---
$smokes = array(
	'/srv/http/quantum_grammar/tests/syntax_matrix_smoke.php',
	'/srv/http/quantum_grammar/tests/corruption_roundtrip_smoke.php',
	'/srv/http/quantum_grammar/tests/dwm_reference_rt.php',
	'/srv/http/spiral/tests/spiral_roundtrip_smoke.php',
	'/srv/http/spiral/tests/spiral_member_fold_smoke.php',
	'/srv/http/spiral/tests/spiral_paq_seed_smoke.php',
	'/srv/http/quantum_grammar/tests/qg_paq_seed_smoke.php',
);
foreach ($smokes as $path) {
	$name = basename($path, '.php');
	if (!is_file($path)) {
		$add($name, 'SKIP', 'missing');
		continue;
	}
	$r = $run('php ' . escapeshellarg($path));
	$ok = $r['code'] === 0 && str_contains($r['out'], 'OK');
	$add('smoke:' . $name, $ok ? 'PASS' : 'FAIL', $ok ? 'RT ok' : trim($r['out']));
}

putenv('FRACTAL_ZIP_SPIRAL_INNER=1');
putenv('FRACTAL_ZIP_SPIRAL_REPARSE=1');
putenv('FRACTAL_ZIP_SPIRAL_CODEC=5');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

$enwik = $repo . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($enwik)) {
	$add('enwik slice', 'SKIP', 'enwik8 missing');
} else {
	$split = fractal_zip_enwik_split_shell_and_text(
		(string) file_get_contents($enwik, false, null, 0, min(12_000_000, $pages * 40_000)),
		$pages
	);
	if ($split === null) {
		$add('enwik slice', 'FAIL', 'split failed');
	} else {
		$text = '';
		foreach ($split['pages'] as $pg) {
			$text .= (string) $pg['text'];
		}
		$gz = static function (string $s): int {
			$z = gzdeflate($s, 9);
			return $z === false ? strlen($s) : strlen($z);
		};
		$base = $gz($text);

		$pre = fractal_zip_enwik_spiral_inner_preprocess($text);
		if ($pre === null) {
			$add('spiral_inner @' . $pages . 'p', 'FAIL', 'preprocess unavailable');
		} else {
			$rest = fractal_zip_enwik_spiral_inner_restore($pre['payload'], $pre['sidecar']);
			$rt = $rest === $text;
			require_once '/srv/http/spiral/src/SpiralSidecar.php';
			$sidecar = SpiralSidecar::sidecarBytes($pre['sidecar']);
			$ltc = $gz((string) $pre['payload']) + $sidecar;
			$delta = $ltc - $base;
			$add(
				'spiral_inner v5 reparse',
				$rt && $delta < 0 ? 'PASS' : ($rt ? 'FAIL' : 'FAIL'),
				sprintf('LTCB=%s Δ=%+d RT=%s', number_format($ltc), $delta, $rt ? 'ok' : 'FAIL')
			);
		}

		putenv('FRACTAL_ZIP_SPIRAL_MEMBER_FOLD=1');
		$foldPre = fractal_zip_enwik_spiral_inner_preprocess($text);
		putenv('FRACTAL_ZIP_SPIRAL_MEMBER_FOLD=0');
		if ($foldPre !== null) {
			$foldRest = fractal_zip_enwik_spiral_inner_restore($foldPre['payload'], $foldPre['sidecar']);
			$foldLtc = $gz((string) $foldPre['payload']);
			$foldDelta = $foldLtc - $base;
			$add(
				'FZSPL member fold',
				$foldRest === $text && $foldDelta < 0 ? 'PASS' : 'FAIL',
				sprintf('LTCB=%s Δ=%+d', number_format($foldLtc), $foldDelta)
			);
		}

		$modelPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.stat_pred_inner_model.fzpm';
		if (is_file($modelPath)) {
			require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
			putenv('FRACTAL_ZIP_STAT_PRED_INNER_MODEL_FILE=' . $modelPath);
			$model = fractal_zip_enwik_stat_pred_inner_frozen_model();
			$sp = fractal_zip_text_stat_pred_preprocess($text, array(
				'stat_model' => $model,
				'frozen' => true,
			));
			$spRest = fractal_zip_text_stat_pred_undo($sp['payload'], array_merge($sp['sidecar'], array(
				'vocab' => $model['vocab'],
				'bigram_succ' => $model['bigram_succ'],
			)));
			$spSc = isset($sp['sidecar']['binary']) ? strlen((string) $sp['sidecar']['binary']) : strlen(json_encode($sp['sidecar']) ?: '');
			$spLtc = $gz((string) $sp['payload']) + $spSc;
			$spDelta = $spLtc - $base;
			$add(
				'stat_pred frozen FZPM',
				$spRest === $text && $spDelta < 0 ? 'PASS' : 'FAIL',
				sprintf('LTCB=%s Δ=%+d RT=%s', number_format($spLtc), $spDelta, $spRest === $text ? 'ok' : 'FAIL')
			);
		}

		require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_bio_template_ifs.php';
		$tif = fractal_zip_bio_template_ifs_build($text);
		$tifRest = fractal_zip_bio_template_ifs_restore($tif['payload'], $tif['sidecar']);
		$tifLtc = $gz((string) $tif['payload']) + strlen($tif['sidecar']);
		$tifDelta = $tifLtc - $base;
		$add(
			'template_ifs inner',
			$tifRest === $text && $tifDelta < 0 ? 'PASS' : 'FAIL',
			sprintf('LTCB=%s Δ=%+d RT=%s', number_format($tifLtc), $tifDelta, $tifRest === $text ? 'ok' : 'FAIL')
		);

		if (!$quick && is_file('/srv/http/spiral/src/ResidualCodec.php')) {
			require_once '/srv/http/quantum_grammar/src/ParseSyntax.php';
			require_once '/srv/http/spiral/src/ResidualCodec.php';
			$pf = static fn (string $t): array => ParseSyntax::parse($t);
			putenv('FRACTAL_ZIP_SPIRAL_CODEC=6');
			$bpc6 = ResidualCodec::residualBpc($text, $pf, 'spiral');
			putenv('FRACTAL_ZIP_SPIRAL_CODEC=5');
			$bpc5 = ResidualCodec::residualBpc($text, $pf, 'spiral');
			$bpcBg = ResidualCodec::residualBpc($text, null, 'bigram');
			$pct5 = $bpcBg > 0 ? (1.0 - $bpc5 / $bpcBg) * 100.0 : 0.0;
			$add(
				'spiral bpc v5 vs bigram',
				$pct5 >= 5.0 ? 'PASS' : 'FAIL',
				sprintf('v5=%.3f bigram=%.3f reduction=%+.1f%%', $bpc5, $bpcBg, $pct5)
			);
			$pct6 = $bpcBg > 0 ? (1.0 - $bpc6 / $bpcBg) * 100.0 : 0.0;
			$add(
				'spiral bpc v6 vs bigram',
				$pct6 >= 5.0 ? 'PASS' : 'FAIL',
				sprintf('v6=%.3f reduction=%+.1f%%', $bpc6, $pct6)
			);
		}
	}
}

if (!$quick) {
	$subs = array(
		array('qg_corrupt', 'php /srv/http/quantum_grammar/benchmarks/bench_qg_corrupt.php 130000'),
		array('qg_cipher_gz9', 'nice -n 19 php ' . escapeshellarg($repo . '/benchmarks/bench_qg_spiral.php') . ' 130000'),
	);
	foreach ($subs as [$name, $cmd]) {
		$r = $run($cmd);
		$gate = $r['code'] === 0 ? 'INFO' : 'FAIL';
		if (preg_match('/Δ=([+-]?\d+)/', $r['out'], $m)) {
			$gate = ((int) $m[1]) < 0 ? 'PASS' : 'FAIL';
		}
		$add($name, $gate, $r['code'] === 0 ? trim(preg_replace('/\s+/', ' ', substr($r['out'], 0, 100))) : 'error');
	}

	$exe = null;
	require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
	$exe = fractal_zip_paq_discover_executable('parallel_cmix');
	if ($exe !== null && is_file($enwik ?? '')) {
		$r = $run('nice -n 19 php ' . escapeshellarg($repo . '/benchmarks/bench_spiral_paq_seed.php')
			. ' --pages=' . $pages . ' --mode=prefix');
		$gate = 'FAIL';
		if (preg_match('/cmix\+FZBGv2\(o1\+o2\).*Δ vs cmix=([+-]?\d+)/', $r['out'], $m)) {
			$gate = ((int) $m[1]) < 0 ? 'PASS' : 'FAIL';
		} elseif (preg_match('/FZBGv2 saves vs cmix: ([+-]?\d+)/', $r['out'], $m)) {
			$gate = ((int) $m[1]) > 0 ? 'PASS' : 'FAIL';
		}
		$add('parallel_cmix+FZBG', $r['code'] === 0 ? $gate : 'FAIL', $r['code'] === 0 ? 'see bench_spiral_paq_seed' : 'error');

		$r3 = $run('nice -n 19 php ' . escapeshellarg($repo . '/benchmarks/bench_qg_paq_seed.php')
			. ' --pages=' . $pages . ' --mode=prefix');
		$gate3 = 'FAIL';
		if (preg_match('/cmix\+qg_boundary_FZBGv2.*Δ vs cmix=([+-]?\d+)/', $r3['out'], $m)) {
			$gate3 = ((int) $m[1]) < 0 ? 'PASS' : 'FAIL';
		} elseif (preg_match('/best qg seed vs cmix: ([+-]?\d+)/', $r3['out'], $m)) {
			$gate3 = ((int) $m[1]) > 0 ? 'PASS' : 'FAIL';
		}
		$add('parallel_cmix+qg_seed', $r3['code'] === 0 ? $gate3 : 'FAIL', $r3['code'] === 0 ? 'see bench_qg_paq_seed' : 'error');

		$r2 = $run('nice -n 19 php ' . escapeshellarg($repo . '/benchmarks/bench_spiral_paq_residual.php') . ' --pages=' . $pages);
		$gate2 = 'FAIL';
		if (preg_match('/GATE (PASS|FAIL)/', $r2['out'], $m)) {
			$gate2 = $m[1];
		}
		$add('cmix on SPRL', $r2['code'] === 0 ? $gate2 : 'FAIL', $r2['code'] === 0 ? trim(substr($r2['out'], strrpos($r2['out'], 'cmix(SPRL)') ?: 0, 80)) : 'error');
	} else {
		$add('parallel_cmix probes', 'SKIP', 'binary not built');
	}
}

printf("bench_qg_spiral_research_gate | pages=%d quick=%s\n\n", $pages, $quick ? 'yes' : 'no');
printf("%-28s %-6s %s\n", 'PROBE', 'GATE', 'DETAIL');
printf("%'-28s %-6s %s\n", '', '', '');
$pass = 0;
$fail = 0;
foreach ($rows as $row) {
	printf("%-28s %-6s %s\n", $row['probe'], $row['gate'], $row['detail']);
	if ($row['gate'] === 'PASS') {
		$pass++;
	} elseif ($row['gate'] === 'FAIL') {
		$fail++;
	}
}
printf("\nwire gates: %d PASS / %d FAIL (smokes + INFO excluded from wire count)\n", $pass, $fail);
fwrite(STDERR, "OK bench_qg_spiral_research_gate\n");
