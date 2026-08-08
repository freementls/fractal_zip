<?php

declare(strict_types=1);

/**
 * Bioinformatics-inspired lossy compression for fractal_zip:
 *  - Profile HMM (match/insert/delete states with emission distributions)
 *  - n-gram probabilistic predictors (bigram/trigram Markov)
 *  - PWM-style position weight scoring for substitution tolerance
 *
 * Lossy = store only correction residuals where prediction confidence exceeds
 * threshold; omitted bytes are reconstructed from the model at decode.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_bioinformatics.php';

const FRACTAL_ZIP_BIO_LOSSY_MAGIC = "FZBL\x01";
const FRACTAL_ZIP_BIO_HMM_STATE_M = 0;
const FRACTAL_ZIP_BIO_HMM_STATE_I = 1;
const FRACTAL_ZIP_BIO_HMM_STATE_D = 2;

function fractal_zip_bio_lossy_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_BIO_LOSSY');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'on' || $v === 'true' || $v === 'yes';
}

/**
 * Train byte bigram counts from reference text (deterministic Markov model).
 *
 * @return array<string, array<int, int>> prev_byte -> next_byte counts
 */
function fractal_zip_bio_lossy_train_bigram(string $ref): array
{
	$counts = array();
	$len = strlen($ref);
	if ($len < 2) {
		return $counts;
	}
	for ($i = 0; $i < $len - 1; $i++) {
		$prev = ord($ref[$i]);
		$next = ord($ref[$i + 1]);
		if (!isset($counts[$prev])) {
			$counts[$prev] = array();
		}
		$counts[$prev][$next] = ($counts[$prev][$next] ?? 0) + 1;
	}
	return $counts;
}

/**
 * Predict next byte from bigram model; returns most likely byte + probability.
 *
 * @return array{byte:int, prob:float}
 */
function fractal_zip_bio_lossy_bigram_predict(int $prevByte, array $bigram): array
{
	if (!isset($bigram[$prevByte])) {
		return array('byte' => $prevByte, 'prob' => 1.0 / 256.0);
	}
	$total = 0;
	$best = $prevByte;
	$bestC = 0;
	foreach ($bigram[$prevByte] as $b => $c) {
		$total += $c;
		if ($c > $bestC) {
			$bestC = $c;
			$best = (int) $b;
		}
	}
	return array('byte' => $best, 'prob' => $total > 0 ? $bestC / $total : 1.0 / 256.0);
}

/**
 * Train trigram counts.
 *
 * @return array<string, array<int, int>>
 */
function fractal_zip_bio_lossy_train_trigram(string $ref): array
{
	$counts = array();
	$len = strlen($ref);
	if ($len < 3) {
		return $counts;
	}
	for ($i = 0; $i < $len - 2; $i++) {
		$key = $ref[$i] . $ref[$i + 1];
		$next = ord($ref[$i + 2]);
		if (!isset($counts[$key])) {
			$counts[$key] = array();
		}
		$counts[$key][$next] = ($counts[$key][$next] ?? 0) + 1;
	}
	return $counts;
}

function fractal_zip_bio_lossy_trigram_predict(string $ctx2, array $trigram): array
{
	if (!isset($trigram[$ctx2])) {
		return array('byte' => ord($ctx2[1] ?? "\0"), 'prob' => 1.0 / 256.0);
	}
	$total = 0;
	$best = 0;
	$bestC = 0;
	foreach ($trigram[$ctx2] as $b => $c) {
		$total += $c;
		if ($c > $bestC) {
			$bestC = $c;
			$best = (int) $b;
		}
	}
	return array('byte' => $best, 'prob' => $total > 0 ? $bestC / $total : 1.0 / 256.0);
}

/**
 * Profile HMM emission log-scores (simplified profile: match emits ref byte).
 *
 * @return array{trans:array, emit:array, ref:string}
 */
function fractal_zip_bio_hmm_profile_train(string $ref): array
{
	$emit = array_fill(0, 256, 1);
	$len = strlen($ref);
	for ($i = 0; $i < $len; $i++) {
		$emit[ord($ref[$i])]++;
	}
	$total = max(1, $len);
	for ($b = 0; $b < 256; $b++) {
		$emit[$b] = $emit[$b] / $total;
	}
	$trans = array(
		FRACTAL_ZIP_BIO_HMM_STATE_M => array(FRACTAL_ZIP_BIO_HMM_STATE_M => 0.92, FRACTAL_ZIP_BIO_HMM_STATE_I => 0.04, FRACTAL_ZIP_BIO_HMM_STATE_D => 0.04),
		FRACTAL_ZIP_BIO_HMM_STATE_I => array(FRACTAL_ZIP_BIO_HMM_STATE_M => 0.1, FRACTAL_ZIP_BIO_HMM_STATE_I => 0.85, FRACTAL_ZIP_BIO_HMM_STATE_D => 0.05),
		FRACTAL_ZIP_BIO_HMM_STATE_D => array(FRACTAL_ZIP_BIO_HMM_STATE_M => 0.1, FRACTAL_ZIP_BIO_HMM_STATE_I => 0.05, FRACTAL_ZIP_BIO_HMM_STATE_D => 0.85),
	);
	return array('trans' => $trans, 'emit' => $emit, 'ref' => $ref);
}

/**
 * HMM Viterbi path predicting member from profile + ref (profile HMM alignment).
 *
 * @return array{path:list<int>, states:list<int>, score:float}
 */
function fractal_zip_bio_hmm_viterbi(array $profile, string $member): array
{
	$ref = (string) $profile['ref'];
	$emit = $profile['emit'];
	$trans = $profile['trans'];
	$n = strlen($ref);
	$m = strlen($member);
	$states = array(FRACTAL_ZIP_BIO_HMM_STATE_M, FRACTAL_ZIP_BIO_HMM_STATE_I, FRACTAL_ZIP_BIO_HMM_STATE_D);
	$inf = -1e30;
	$dp = array();
	$trace = array();
	for ($s = 0; $s < 3; $s++) {
		$dp[0][$s] = ($s === FRACTAL_ZIP_BIO_HMM_STATE_M) ? 0.0 : $inf;
	}
	for ($i = 1; $i <= $n; $i++) {
		for ($j = 0; $j <= $m; $j++) {
			foreach ($states as $s) {
				$dp[$i][$j][$s] = $inf;
				$trace[$i][$j][$s] = array(-1, -1, -1);
			}
		}
	}
	for ($i = 1; $i <= $n; $i++) {
		for ($j = 0; $j <= $m; $j++) {
			$rc = $i > 0 ? ord($ref[$i - 1]) : 0;
			$mc = $j > 0 ? ord($member[$j - 1]) : 0;
			foreach ($states as $s) {
				$best = $inf;
				$bt = array(-1, -1, -1);
				foreach ($states as $ps) {
					$pi = $i;
					$pj = $j;
					if ($s === FRACTAL_ZIP_BIO_HMM_STATE_M && $i > 0 && $j > 0) {
						$pi = $i - 1;
						$pj = $j - 1;
					} elseif ($s === FRACTAL_ZIP_BIO_HMM_STATE_I && $j > 0) {
						$pj = $j - 1;
					} elseif ($s === FRACTAL_ZIP_BIO_HMM_STATE_D && $i > 0) {
						$pi = $i - 1;
					} else {
						continue;
					}
					if (!isset($dp[$pi][$pj][$ps]) || $dp[$pi][$pj][$ps] <= $inf / 2) {
						continue;
					}
					$tprob = (float) ($trans[$ps][$s] ?? 0.01);
					$escore = 0.0;
					if ($s === FRACTAL_ZIP_BIO_HMM_STATE_M && $j > 0) {
						$escore = log(max(1e-12, (float) ($emit[$mc] ?? 1e-12)));
					} elseif ($s === FRACTAL_ZIP_BIO_HMM_STATE_I && $j > 0) {
						$escore = log(max(1e-12, (float) ($emit[$mc] ?? 1e-12)));
					}
					$sc = $dp[$pi][$pj][$ps] + log($tprob) + $escore;
					if ($sc > $best) {
						$best = $sc;
						$bt = array($pi, $pj, $ps);
					}
				}
				$dp[$i][$j][$s] = $best;
				$trace[$i][$j][$s] = $bt;
			}
		}
	}
	$ci = $n;
	$cj = $m;
	$cs = FRACTAL_ZIP_BIO_HMM_STATE_M;
	$path = array();
	$statePath = array();
	while ($ci > 0 || $cj > 0) {
		$path[] = $cj;
		$statePath[] = $cs;
		$bt = $trace[$ci][$cj][$cs] ?? array(0, 0, FRACTAL_ZIP_BIO_HMM_STATE_M);
		$ci = (int) $bt[0];
		$cj = (int) $bt[1];
		$cs = (int) $bt[2];
	}
	$path = array_reverse($path);
	$statePath = array_reverse($statePath);
	return array('path' => $path, 'states' => $statePath, 'score' => (float) ($dp[$n][$m][FRACTAL_ZIP_BIO_HMM_STATE_M] ?? 0));
}

/**
 * PWM: per-position weights from reference (higher weight = more conserved).
 *
 * @return list<float>
 */
function fractal_zip_bio_pwm_weights(string $ref): array
{
	$len = strlen($ref);
	$w = array();
	for ($i = 0; $i < $len; $i++) {
		$c = $ref[$i];
		$w[] = (preg_match('/[A-Za-z]/', $c) ? 1.0 : 0.5);
	}
	return $w;
}

/**
 * Lossy compress member against reference using probabilistic predictor.
 *
 * @param array<string,mixed> $opts threshold, model (bigram|trigram|hmm)
 * @return array{payload:string, sidecar:array, approx:string, lossy_bytes_saved:int}
 */
function fractal_zip_bio_lossy_compress(string $ref, string $member, array $opts = array()): array
{
	$model = strtolower((string) ($opts['model'] ?? 'bigram'));
	$threshold = (float) ($opts['threshold'] ?? 0.55);
	$bigram = fractal_zip_bio_lossy_train_bigram($ref);
	$trigram = fractal_zip_bio_lossy_train_trigram($ref);
	$profile = fractal_zip_bio_hmm_profile_train($ref);
	$pwm = fractal_zip_bio_pwm_weights($ref);
	$mLen = strlen($member);
	$corrections = array();
	$approx = '';
	$prev = ord($ref[0] ?? "\0");
	$ctx2 = substr($ref, 0, 2);
	for ($j = 0; $j < $mLen; $j++) {
		$actual = ord($member[$j]);
		if ($model === 'trigram' && strlen($ctx2) >= 2) {
			$pred = fractal_zip_bio_lossy_trigram_predict($ctx2, $trigram);
		} elseif ($model === 'hmm') {
			$ri = min($j, strlen($ref) - 1);
			$pred = array('byte' => ord($ref[$ri] ?? "\0"), 'prob' => (float) ($profile['emit'][$actual] ?? 0.01));
		} else {
			$pred = fractal_zip_bio_lossy_bigram_predict($prev, $bigram);
		}
		$predicted = (int) $pred['byte'];
		$prob = (float) $pred['prob'];
		$ri = min($j, count($pwm) - 1);
		$weight = $pwm[$ri] ?? 1.0;
		if ($predicted === $actual && $prob * $weight >= $threshold) {
			$approx .= chr($actual);
		} else {
			$corrections[] = array('pos' => $j, 'byte' => $actual);
			$approx .= chr($predicted);
		}
		$prev = $actual;
		if (strlen($ctx2) >= 2) {
			$ctx2 = $ctx2[1] . chr($actual);
		} else {
			$ctx2 .= chr($actual);
		}
	}
	$payload = '';
	foreach ($corrections as $c) {
		$payload .= fractal_zip_enwik_encode_varint_u32((int) $c['pos']);
		$payload .= chr((int) $c['byte']);
	}
	$sidecar = array(
		'magic' => FRACTAL_ZIP_BIO_LOSSY_MAGIC,
		'model' => $model,
		'threshold' => $threshold,
		'ref_len' => strlen($ref),
		'member_len' => $mLen,
		'correction_count' => count($corrections),
		'ref_b64' => base64_encode($ref),
		'bigram_b64' => base64_encode(json_encode($bigram) ?: '{}'),
		'trigram_b64' => base64_encode(json_encode($trigram) ?: '{}'),
		'profile_b64' => base64_encode(json_encode(array('emit' => $profile['emit'], 'trans' => $profile['trans'])) ?: '{}'),
	);
	return array(
		'payload' => $payload,
		'sidecar' => $sidecar,
		'approx' => $approx,
		'lossy_bytes_saved' => $mLen - strlen($payload),
	);
}

/**
 * Reconstruct approximate member from lossy payload + sidecar.
 */
function fractal_zip_bio_lossy_decompress(array $sidecar, string $payload): string
{
	$ref = base64_decode((string) ($sidecar['ref_b64'] ?? ''), true);
	if ($ref === false) {
		throw new RuntimeException('bio lossy: ref decode failed');
	}
	$model = (string) ($sidecar['model'] ?? 'bigram');
	$threshold = (float) ($sidecar['threshold'] ?? 0.55);
	$mLen = (int) ($sidecar['member_len'] ?? 0);
	$bigram = json_decode((string) base64_decode((string) ($sidecar['bigram_b64'] ?? ''), true), true) ?: array();
	$trigram = json_decode((string) base64_decode((string) ($sidecar['trigram_b64'] ?? ''), true), true) ?: array();
	$profileData = json_decode((string) base64_decode((string) ($sidecar['profile_b64'] ?? ''), true), true) ?: array();
	$profile = array(
		'ref' => $ref,
		'emit' => $profileData['emit'] ?? array_fill(0, 256, 1 / 256),
		'trans' => $profileData['trans'] ?? array(),
	);
	$pwm = fractal_zip_bio_pwm_weights($ref);
	$corrections = array();
	$pos = 0;
	$count = (int) ($sidecar['correction_count'] ?? 0);
	for ($c = 0; $c < $count; $c++) {
		$dv = fractal_zip_enwik_decode_varint_u32($payload, $pos);
		$p = (int) $dv[0];
		$pos = (int) $dv[1];
		if ($pos >= strlen($payload)) {
			break;
		}
		$corrections[$p] = ord($payload[$pos]);
		$pos++;
	}
	$out = '';
	$prev = ord($ref[0] ?? "\0");
	$ctx2 = substr($ref, 0, 2);
	for ($j = 0; $j < $mLen; $j++) {
		if (isset($corrections[$j])) {
			$b = $corrections[$j];
		} else {
			if ($model === 'trigram' && strlen($ctx2) >= 2) {
				$pred = fractal_zip_bio_lossy_trigram_predict($ctx2, $trigram);
			} elseif ($model === 'hmm') {
				$ri = min($j, strlen($ref) - 1);
				$b = ord($ref[$ri]);
			} else {
				$pred = fractal_zip_bio_lossy_bigram_predict($prev, $bigram);
				$b = (int) $pred['byte'];
			}
		}
		$out .= chr($b);
		$prev = $b;
		if (strlen($ctx2) >= 2) {
			$ctx2 = $ctx2[1] . chr($b);
		} else {
			$ctx2 .= chr($b);
		}
	}
	return $out;
}

/**
 * Text transform apply hook for lossy bio predictors.
 *
 * @return array{payload:string, sidecar:array}
 */
function fractal_zip_bio_lossy_transform_apply(string $text, array $sidecar = array()): array
{
	$ref = (string) ($sidecar['ref'] ?? $text);
	$member = $text;
	if ($ref === $member) {
		return array('payload' => '', 'sidecar' => array_merge($sidecar, array('transform' => 'bio_lossy', 'identity' => true)));
	}
	$opts = array(
		'model' => (string) ($sidecar['model'] ?? 'bigram'),
		'threshold' => (float) ($sidecar['threshold'] ?? 0.55),
	);
	$comp = fractal_zip_bio_lossy_compress($ref, $member, $opts);
	$sc = array_merge($sidecar, $comp['sidecar'], array('transform' => 'bio_lossy'));
	return array('payload' => (string) $comp['payload'], 'sidecar' => $sc);
}

function fractal_zip_bio_lossy_transform_undo(string $payload, array $sidecar): string
{
	if (!empty($sidecar['identity'])) {
		return (string) ($sidecar['original'] ?? '');
	}
	return fractal_zip_bio_lossy_decompress($sidecar, $payload);
}

/** @return list<string> */
function fractal_zip_bio_lossy_model_catalog(): array
{
	return array('bigram', 'trigram', 'hmm');
}

/**
 * Bridge for OOXML/image lossy path: apply bio predictor to shrink member bytes
 * when enabled via FRACTAL_ZIP_BIO_LOSSY=1.
 */
function fractal_zip_bio_lossy_maybe_shrink_member(string $memberBytes, string $referenceBytes = ''): string
{
	if (!fractal_zip_bio_lossy_enabled() || $memberBytes === '') {
		return $memberBytes;
	}
	if ($referenceBytes === '') {
		$referenceBytes = $memberBytes;
	}
	$comp = fractal_zip_bio_lossy_compress($referenceBytes, $memberBytes, array('model' => 'trigram', 'threshold' => 0.6));
	if ($comp['lossy_bytes_saved'] <= 0) {
		return $memberBytes;
	}
	return FRACTAL_ZIP_BIO_LOSSY_MAGIC . (string) $comp['payload'] . "\x00" . json_encode($comp['sidecar']);
}

function fractal_zip_bio_lossy_maybe_restore_member(string $stored): string
{
	if (!str_starts_with($stored, FRACTAL_ZIP_BIO_LOSSY_MAGIC)) {
		return $stored;
	}
	$rest = substr($stored, strlen(FRACTAL_ZIP_BIO_LOSSY_MAGIC));
	$parts = explode("\x00", $rest, 2);
	if (count($parts) !== 2) {
		return $stored;
	}
	$payload = $parts[0];
	$sidecar = json_decode($parts[1], true);
	if (!is_array($sidecar)) {
		return $stored;
	}
	return fractal_zip_bio_lossy_decompress($sidecar, $payload);
}
