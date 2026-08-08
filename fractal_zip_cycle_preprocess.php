<?php
declare(strict_types=1);

/**
 * fractal_zip text preprocess: cycle detect + FZCY wire codec.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'cycle_encoding_codec.php';

const FRACTAL_ZIP_CYCLE_SIDECAR_MAGIC = "FZCI\x01";

function fractal_zip_text_cycle_sidecar_pack(string $transform): string
{
	return FRACTAL_ZIP_CYCLE_SIDECAR_MAGIC . chr($transform === 'delta' ? 1 : 0);
}

/**
 * @param array<string,mixed> $sidecar
 */
function fractal_zip_text_cycle_sidecar_transform(array $sidecar): string
{
	if(isset($sidecar['binary']) && is_string($sidecar['binary'])
		&& str_starts_with($sidecar['binary'], FRACTAL_ZIP_CYCLE_SIDECAR_MAGIC)) {
		return (($sidecar['binary'][5] ?? "\0") === "\x01") ? 'delta' : 'none';
	}
	return (string)($sidecar['transform'] ?? 'none');
}

/**
 * @return array{payload:string, sidecar:array<string,mixed>, meta:array<string,mixed>}
 */
function fractal_zip_text_cycle_preprocess(string $text, array $opts = array()): array
{
	$minCycleLen = (int)($opts['min_cycle_len'] ?? 64);
	$transform = (string)($opts['transform'] ?? 'none');
	$src = $text;
	if($transform === 'delta') {
		$src = cycle_encoding_delta_forward($text);
	}
	$enc = cycle_encoding_codec_encode($src, $minCycleLen);
	$preprocess = $transform === 'delta' ? 'cycle_delta' : 'cycle_inner';
	$binary = fractal_zip_text_cycle_sidecar_pack($transform);
	return array(
		'payload' => (string)$enc['payload'],
		'sidecar' => array(
			'preprocess' => $preprocess,
			'transform' => $transform,
			'binary' => $binary,
		),
		'meta' => $enc['meta'],
	);
}

/**
 * @param array<string,mixed> $sidecar
 */
function fractal_zip_text_cycle_undo(string $payload, array $sidecar): string
{
	$rest = cycle_encoding_codec_decode($payload);
	$transform = fractal_zip_text_cycle_sidecar_transform($sidecar);
	if($transform === 'delta') {
		return cycle_encoding_delta_inverse($rest);
	}
	return $rest;
}

/**
 * Inner preprocess then cycle codec on its payload (combo wire).
 *
 * @return array{payload:string, sidecar:array<string,mixed>, meta:array<string,mixed>}
 */
function fractal_zip_text_cycle_then_preprocess(string $text, string $innerId, array $opts = array()): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	$inner = fractal_zip_text_preprocess_apply($innerId, $text, $opts);
	$minCycleLen = (int)($opts['min_cycle_len'] ?? 64);
	$enc = cycle_encoding_codec_encode((string)$inner['payload'], $minCycleLen);
	return array(
		'payload' => (string)$enc['payload'],
		'sidecar' => array(
			'preprocess' => 'cycle_combo',
			'inner_id' => $innerId,
			'inner' => $inner['sidecar'],
			'cycle' => array(
				'preprocess' => 'cycle_inner',
				'transform' => 'none',
				'binary' => fractal_zip_text_cycle_sidecar_pack('none'),
			),
		),
		'meta' => array_merge($inner['meta'] ?? array(), $enc['meta']),
	);
}

/**
 * @param array<string,mixed> $sidecar
 */
function fractal_zip_text_cycle_combo_undo(string $payload, array $sidecar): string
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	$innerId = (string)($sidecar['inner_id'] ?? 'none');
	$innerSide = is_array($sidecar['inner'] ?? null) ? $sidecar['inner'] : array();
	$mid = cycle_encoding_codec_decode($payload);
	if($innerId === 'stat_pred') {
		$modelPath = getenv('FRACTAL_ZIP_STAT_PRED_INNER_MODEL_FILE');
		if(is_string($modelPath) && $modelPath !== '' && is_file($modelPath)) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
			$model = fractal_zip_enwik_stat_pred_inner_frozen_model();
			$innerSide = array_merge($innerSide, array(
				'vocab' => $model['vocab'],
				'bigram_succ' => $model['bigram_succ'],
			));
		}
	}
	return fractal_zip_text_preprocess_undo($innerId, $mid, $innerSide);
}
