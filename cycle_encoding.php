<?php
declare(strict_types=1);

/**
 * Deterministic cycle / epicycle corpora generator (v2) with v1 legacy decode.
 *
 * Used only for fixture generation and verification — not compression hints.
 */

const CYCLE_COMBINE_SINGLE = 0;
const CYCLE_COMBINE_ROUND_ROBIN = 1;
const CYCLE_COMBINE_COMPLEX = 2;
const CYCLE_COMBINE_DEFERENT_RR = 3;

const CYCLE_BYTE_MAP_DIRECT = 0;
const CYCLE_BYTE_MAP_TORUS = 1;

const CYCLE_TORUS_GA = 2.399963229728653;
const CYCLE_TORUS_TWO_PI = 6.283185307179586;
const CYCLE_TORUS_IM_SCALE = 1.137;
const CYCLE_TORUS_T_SCALE = 0.00317;

function cycle_encoding_span(int $min_ord, int $max_ord): int
{
	if($max_ord < $min_ord) {
		throw new InvalidArgumentException('max_ord must be >= min_ord');
	}
	return $max_ord - $min_ord;
}

function cycle_encoding_wrap(int $ord, int $min_ord, int $max_ord): int
{
	$span = cycle_encoding_span($min_ord, $max_ord);
	while($ord > $max_ord) {
		$ord -= $span;
	}
	while($ord < $min_ord) {
		$ord += $span;
	}
	return $ord;
}

function cycle_encoding_advance(int $ord, int $step, int $min_ord, int $max_ord): int
{
	return cycle_encoding_advance_ord($ord + $step, $min_ord, $max_ord);
}

function cycle_encoding_advance_ord(int $ord, int $min_ord, int $max_ord): int
{
	$span = $max_ord - $min_ord;
	if($ord > $max_ord) {
		$ord -= $span;
	}
	if($ord < $min_ord) {
		$ord += $span;
	}
	return $ord;
}

function cycle_encoding_wrap_ord(int $ord, int $min_ord, int $max_ord): int
{
	$span = $max_ord - $min_ord;
	if($ord > $max_ord) {
		$ord -= $span;
	}
	if($ord < $min_ord) {
		$ord += $span;
	}
	return $ord;
}

/**
 * @param array{min_ord:int,max_ord:int,step:int,phase?:int,amplitude?:float,ord?:int} $walker
 * @return array{min_ord:int,max_ord:int,step:int,phase?:int,amplitude?:float,ord:int}
 */
function cycle_encoding_walker_init(array $walker): array
{
	$min = (int)$walker['min_ord'];
	$max = (int)$walker['max_ord'];
	$start = isset($walker['phase']) ? (int)$walker['phase'] : $min;
	$walker['ord'] = cycle_encoding_wrap($start, $min, $max);
	return $walker;
}

/**
 * @param array{min_ord:int,max_ord:int,step:int,ord:int} $walker
 * @return array{min_ord:int,max_ord:int,step:int,ord:int}
 */
function cycle_encoding_walker_advance(array $walker): array
{
	$walker['ord'] = cycle_encoding_advance(
		(int)$walker['ord'],
		(int)$walker['step'],
		(int)$walker['min_ord'],
		(int)$walker['max_ord']
	);
	return $walker;
}

function cycle_encoding_combine_name(int $code): string
{
	return match($code) {
		CYCLE_COMBINE_SINGLE => 'single',
		CYCLE_COMBINE_ROUND_ROBIN => 'round_robin',
		CYCLE_COMBINE_COMPLEX => 'complex',
		CYCLE_COMBINE_DEFERENT_RR => 'deferent_rr',
		default => 'single',
	};
}

function cycle_encoding_combine_code(string $name): int
{
	return match($name) {
		'round_robin' => CYCLE_COMBINE_ROUND_ROBIN,
		'complex' => CYCLE_COMBINE_COMPLEX,
		'deferent_rr' => CYCLE_COMBINE_DEFERENT_RR,
		default => CYCLE_COMBINE_SINGLE,
	};
}

function cycle_encoding_byte_map_name(int $code): string
{
	return $code === CYCLE_BYTE_MAP_TORUS ? 'torus' : 'direct';
}

function cycle_encoding_byte_map_code(string $name): int
{
	return $name === 'torus' ? CYCLE_BYTE_MAP_TORUS : CYCLE_BYTE_MAP_DIRECT;
}

function cycle_encoding_torus_available(): bool
{
	return is_file('/srv/http/spiral/src/TorusEmbed.php');
}

/**
 * @param list<float> $thetaTable
 */
function cycle_encoding_torus_best_from_cand(float $theta, int $cand, array $thetaTable): int
{
	$best = $cand;
	$tb = $thetaTable[$best];
	$err = abs($theta - $tb);
	if($err > M_PI) {
		$err = CYCLE_TORUS_TWO_PI - $err;
	}
	$bestErr = $err;
	$b = ($cand + 253) & 0xFF;
	$tb = $thetaTable[$b];
	$err = abs($theta - $tb);
	if($err > M_PI) {
		$err = CYCLE_TORUS_TWO_PI - $err;
	}
	if($err < $bestErr) {
		$bestErr = $err;
		$best = $b;
	}
	$b = ($cand + 254) & 0xFF;
	$tb = $thetaTable[$b];
	$err = abs($theta - $tb);
	if($err > M_PI) {
		$err = CYCLE_TORUS_TWO_PI - $err;
	}
	if($err < $bestErr) {
		$bestErr = $err;
		$best = $b;
	}
	$b = ($cand + 255) & 0xFF;
	$tb = $thetaTable[$b];
	$err = abs($theta - $tb);
	if($err > M_PI) {
		$err = CYCLE_TORUS_TWO_PI - $err;
	}
	if($err < $bestErr) {
		$bestErr = $err;
		$best = $b;
	}
	$b = ($cand + 1) & 0xFF;
	$tb = $thetaTable[$b];
	$err = abs($theta - $tb);
	if($err > M_PI) {
		$err = CYCLE_TORUS_TWO_PI - $err;
	}
	if($err < $bestErr) {
		$bestErr = $err;
		$best = $b;
	}
	$b = ($cand + 2) & 0xFF;
	$tb = $thetaTable[$b];
	$err = abs($theta - $tb);
	if($err > M_PI) {
		$err = CYCLE_TORUS_TWO_PI - $err;
	}
	if($err < $bestErr) {
		$bestErr = $err;
		$best = $b;
	}
	$b = ($cand + 3) & 0xFF;
	$tb = $thetaTable[$b];
	$err = abs($theta - $tb);
	if($err > M_PI) {
		$err = CYCLE_TORUS_TWO_PI - $err;
	}
	if($err < $bestErr) {
		$best = $b;
	}
	return $best;
}

/**
 * @return list<float> byte 0–255 -> theta (syntaxCode fixed; theta independent of index)
 */
function cycle_encoding_torus_byte_theta_table(int $syntaxCode = 7): array
{
	static $cache = array();
	if(isset($cache[$syntaxCode])) {
		return $cache[$syntaxCode];
	}
	if(!cycle_encoding_torus_available()) {
		return $cache[$syntaxCode] = array();
	}
	require_once '/srv/http/spiral/src/TorusEmbed.php';
	$table = array();
	for($b = 0; $b < 256; $b++) {
		$table[$b] = TorusEmbed::fromByte($b, $syntaxCode, 0)['theta'];
	}
	return $cache[$syntaxCode] = $table;
}

/**
 * @return list<float> index % 17 -> syntax/index adjustment
 */
function cycle_encoding_torus_adj_table(int $syntaxCode = 7): array
{
	static $cache = array();
	if(isset($cache[$syntaxCode])) {
		return $cache[$syntaxCode];
	}
	$base = (float)$syntaxCode * 0.137;
	$row = array();
	for($i = 0; $i < 17; $i++) {
		$row[$i] = $base + (float)$i * 0.0001;
	}
	return $cache[$syntaxCode] = $row;
}

/**
 * @param list<float> $thetaTable
 * @param list<float> $adjTable
 */
function cycle_encoding_torus_pick_nearest(float $theta, int $index, array $thetaTable, array $adjTable): int
{
	$tt = $theta - $adjTable[$index % 17];
	if($tt < 0.0) {
		$tt += CYCLE_TORUS_TWO_PI;
	} elseif($tt >= CYCLE_TORUS_TWO_PI) {
		$tt -= CYCLE_TORUS_TWO_PI;
	}
	$cand = ((int)round($tt / CYCLE_TORUS_GA)) & 0xFF;
	return cycle_encoding_torus_best_from_cand($theta, $cand, $thetaTable);
}

function cycle_encoding_torus_nearest_byte(float $theta, int $syntaxCode, int $index): int
{
	$table = cycle_encoding_torus_byte_theta_table($syntaxCode);
	if($table === array()) {
		return max(0, min(255, (int)round($theta / CYCLE_TORUS_GA * 85.0)));
	}
	return cycle_encoding_torus_pick_nearest($theta, $index, $table, cycle_encoding_torus_adj_table($syntaxCode));
}

function cycle_encoding_torus_byte(int $t, float $re, float $im, int $syntaxCode = 7): int
{
	static $thetaTable = null;
	static $adjTable = null;
	if($thetaTable === null) {
		$thetaTable = cycle_encoding_torus_byte_theta_table($syntaxCode);
		$adjTable = cycle_encoding_torus_adj_table($syntaxCode);
	}
	if($thetaTable === array()) {
		return max(0, min(255, (int)round(128.0 + $re * 23.0 + $im * 11.0 + ($t % 17) * 0.13)));
	}
	$theta = fmod($re * CYCLE_TORUS_GA + $im * CYCLE_TORUS_IM_SCALE + (float)$t * CYCLE_TORUS_T_SCALE, CYCLE_TORUS_TWO_PI);
	if($theta < 0.0) {
		$theta += CYCLE_TORUS_TWO_PI;
	}
	return cycle_encoding_torus_pick_nearest($theta, $t, $thetaTable, $adjTable);
}

function cycle_encoding_emit_byte(
	int $ord,
	int $t,
	float $re,
	float $im,
	string $byteMap,
	int $minOrd = 0,
	int $maxOrd = 255
): string {
	if($byteMap === 'torus') {
		if($re === 0.0 && $im === 0.0) {
			$span = max(1, cycle_encoding_span($minOrd, $maxOrd));
			$norm = ((float)($ord - $minOrd)) / (float)$span;
			$re = cos($norm * 2.0 * M_PI + (float)$t * 0.011);
			$im = sin($norm * 2.0 * M_PI + (float)$t * 0.017);
		}
		return chr(cycle_encoding_torus_byte($t, $re, $im));
	}
	return chr($ord & 0xff);
}

function cycle_encoding_single(int $min_ord, int $max_ord, int $step, int $length): string
{
	if($length < 0) {
		throw new InvalidArgumentException('length must be non-negative');
	}
	$ord = $min_ord;
	$out = '';
	while(strlen($out) < $length) {
		$out .= chr($ord);
		$ord = cycle_encoding_advance($ord, $step, $min_ord, $max_ord);
	}
	return $out;
}

/**
 * @param array{min_ord:int,max_ord:int,step:int,phase?:int} $walker
 */
function cycle_encoding_single_v2(array $walker, int $length, string $byteMap = 'direct'): string
{
	return cycle_encoding_single_v2_range($walker, 0, $length, $byteMap);
}

/**
 * @param array{min_ord:int,max_ord:int,step:int,phase?:int} $walker
 */
function cycle_encoding_single_v2_range(array $walker, int $offset, int $length, string $byteMap = 'direct'): string
{
	$state = cycle_encoding_walker_init($walker);
	for($i = 0; $i < $offset; $i++) {
		$state = cycle_encoding_walker_advance($state);
	}
	$large = $length >= 4096;
	$out = $large ? str_repeat("\0", $length) : '';
	for($t = $offset; $t < $offset + $length; $t++) {
		if($byteMap === 'direct') {
			$byte = chr((int)$state['ord'] & 0xff);
		} else {
			$byte = cycle_encoding_emit_byte(
				(int)$state['ord'],
				$t,
				0.0,
				0.0,
				$byteMap,
				(int)$state['min_ord'],
				(int)$state['max_ord']
			);
		}
		$idx = $t - $offset;
		if($large) {
			$out[$idx] = $byte;
		} else {
			$out .= $byte;
		}
		$state = cycle_encoding_walker_advance($state);
	}
	return $out;
}

/**
 * @param list<array{min_ord:int,max_ord:int,step:int,phase?:int}> $walkers
 */
function cycle_encoding_round_robin(array $walkers, int $length, string $byteMap = 'direct'): string
{
	return cycle_encoding_round_robin_range($walkers, 0, $length, $byteMap);
}

/**
 * @param list<array{min_ord:int,max_ord:int,step:int,phase?:int}> $walkers
 */
function cycle_encoding_round_robin_range(array $walkers, int $offset, int $length, string $byteMap = 'direct'): string
{
	if($walkers === array()) {
		throw new InvalidArgumentException('walkers must not be empty');
	}
	$n = count($walkers);
	$ords = array();
	$mins = array();
	$maxs = array();
	$steps = array();
	foreach($walkers as $w) {
		$min = (int)$w['min_ord'];
		$max = (int)$w['max_ord'];
		$mins[] = $min;
		$maxs[] = $max;
		$steps[] = (int)$w['step'];
		$start = isset($w['phase']) ? (int)$w['phase'] : $min;
		$ords[] = cycle_encoding_wrap_ord($start, $min, $max);
	}
	for($t = 0; $t < $offset; $t++) {
		$j = $t % $n;
		$ords[$j] = cycle_encoding_advance_ord($ords[$j] + $steps[$j], $mins[$j], $maxs[$j]);
	}
	$large = $length >= 4096;
	$out = $large ? str_repeat("\0", $length) : '';
	$pos = 0;
	for($t = $offset; $pos < $length; $t++) {
		$j = $t % $n;
		if($byteMap === 'direct') {
			$byte = chr($ords[$j] & 0xff);
		} else {
			$byte = cycle_encoding_emit_byte(
				$ords[$j],
				$t,
				0.0,
				0.0,
				$byteMap,
				$mins[$j],
				$maxs[$j]
			);
		}
		if($large) {
			$out[$pos] = $byte;
		} else {
			$out .= $byte;
		}
		$ords[$j] = cycle_encoding_advance_ord($ords[$j] + $steps[$j], $mins[$j], $maxs[$j]);
		$pos++;
	}
	return $out;
}

/**
 * @param list<array{min_ord:int,max_ord:int,step:int,phase?:int}> $walkers
 * @param array{min_ord:int,max_ord:int,step:int,phase?:int} $deferent
 */
function cycle_encoding_deferent_rr_range(
	array $walkers,
	array $deferent,
	int $offset,
	int $length,
	string $byteMap = 'direct'
): string {
	if($walkers === array() || $byteMap !== 'direct') {
		$full = array(
			'version' => 2,
			'combine' => 'deferent_rr',
			'byte_map' => $byteMap,
			'walkers' => $walkers,
			'deferent' => $deferent,
			'length' => $offset + $length,
			'width' => 1,
		);
		return substr(cycle_encoding_generate($full), $offset, $length);
	}
	$n = count($walkers);
	$wOrd = array();
	$wMin = array();
	$wMax = array();
	$wSpan = array();
	$wStep = array();
	foreach($walkers as $w) {
		$min = (int)$w['min_ord'];
		$max = (int)$w['max_ord'];
		$wMin[] = $min;
		$wMax[] = $max;
		$wSpan[] = $max - $min;
		$wStep[] = (int)$w['step'];
		$start = isset($w['phase']) ? (int)$w['phase'] : $min;
		$wOrd[] = cycle_encoding_wrap_ord($start, $min, $max);
	}
	$defMin = (int)$deferent['min_ord'];
	$defMax = (int)$deferent['max_ord'];
	$defStep = (int)$deferent['step'];
	$defStart = isset($deferent['phase']) ? (int)$deferent['phase'] : $defMin;
	$defOrd = cycle_encoding_wrap_ord($defStart, $defMin, $defMax);
	for($t = 0; $t < $offset; $t++) {
		$j = $t % $n;
		$wOrd[$j] = cycle_encoding_advance_ord($wOrd[$j] + $wStep[$j], $wMin[$j], $wMax[$j]);
		$defOrd = cycle_encoding_advance_ord($defOrd + $defStep, $defMin, $defMax);
	}
	$large = $length >= 4096;
	$out = $large ? str_repeat("\0", $length) : '';
	$pos = 0;
	for($t = $offset; $pos < $length; $t++) {
		$j = $t % $n;
		$rel = $wOrd[$j] - $defOrd + $wMin[$j];
		if($rel > $wMax[$j]) {
			$rel -= $wSpan[$j];
		} elseif($rel < $wMin[$j]) {
			$rel += $wSpan[$j];
		}
		if($large) {
			$out[$pos] = chr($rel & 0xff);
		} else {
			$out .= chr($rel & 0xff);
		}
		$wOrd[$j] = cycle_encoding_advance_ord($wOrd[$j] + $wStep[$j], $wMin[$j], $wMax[$j]);
		$defOrd = cycle_encoding_advance_ord($defOrd + $defStep, $defMin, $defMax);
		$pos++;
	}
	return $out;
}

/**
 * @param list<array{min_ord:int,max_ord:int,step:int,phase?:int}> $walkers
 */
function cycle_encoding_round_robin_multichar(array $walkers, int $width, int $length, string $byteMap = 'direct'): string
{
	if($width < 1) {
		throw new InvalidArgumentException('width must be >= 1');
	}
	if($length % $width !== 0) {
		throw new InvalidArgumentException('length must be a multiple of width');
	}
	if($walkers === array()) {
		throw new InvalidArgumentException('walkers must not be empty');
	}
	$n = count($walkers);
	$ords = array();
	$mins = array();
	$maxs = array();
	$steps = array();
	foreach($walkers as $w) {
		$min = (int)$w['min_ord'];
		$max = (int)$w['max_ord'];
		$mins[] = $min;
		$maxs[] = $max;
		$steps[] = (int)$w['step'];
		$start = isset($w['phase']) ? (int)$w['phase'] : $min;
		$ords[] = cycle_encoding_wrap_ord($start, $min, $max);
	}
	$symbols = intdiv($length, $width);
	$large = $length >= 4096;
	$out = $large ? str_repeat("\0", $length) : '';
	$pos = 0;
	$t = 0;
	for($s = 0; $s < $symbols; $s++) {
		for($k = 0; $k < $width; $k++) {
			$j = ($s + $k) % $n;
			if($byteMap === 'direct') {
				$byte = chr($ords[$j] & 0xff);
			} else {
				$byte = cycle_encoding_emit_byte(
					$ords[$j],
					$t,
					0.0,
					0.0,
					$byteMap,
					$mins[$j],
					$maxs[$j]
				);
			}
			if($large) {
				$out[$pos] = $byte;
			} else {
				$out .= $byte;
			}
			$ords[$j] = cycle_encoding_advance_ord($ords[$j] + $steps[$j], $mins[$j], $maxs[$j]);
			$pos++;
			$t++;
		}
	}
	return $out;
}

/**
 * @param list<array{min_ord:int,max_ord:int,step:int,phase?:int,amplitude?:float}> $walkers
 * @return list<array{amp:float,k:float,base:float}>
 */
function cycle_encoding_complex_terms(array $walkers): array
{
	$terms = array();
	foreach($walkers as $j => $w) {
		$terms[] = array(
			'amp' => (float)($w['amplitude'] ?? 1.0),
			'k' => (float)$w['step'] * 0.17,
			'base' => (float)($w['phase'] ?? 0) * M_PI / 128.0 + (float)$j * 0.41,
		);
	}
	return $terms;
}

/**
 * @param list<array{amp:float,k:float,base:float}> $terms
 */
function cycle_encoding_complex_epicycle_torus_range(array $terms, int $offset, int $length): string
{
	static $thetaTable = null;
	static $adjTable = null;
	if($thetaTable === null) {
		$thetaTable = cycle_encoding_torus_byte_theta_table(7);
		$adjTable = cycle_encoding_torus_adj_table(7);
	}
	if($thetaTable === array()) {
		throw new RuntimeException('torus table unavailable');
	}
	$end = $offset + $length;
	$large = $length >= 4096;
	$out = $large ? str_repeat("\0", $length) : '';
	$pos = 0;
	if(count($terms) === 3) {
		$a0 = $terms[0]['amp'];
		$k0 = $terms[0]['k'];
		$b0 = $terms[0]['base'];
		$a1 = $terms[1]['amp'];
		$k1 = $terms[1]['k'];
		$b1 = $terms[1]['base'];
		$a2 = $terms[2]['amp'];
		$k2 = $terms[2]['k'];
		$b2 = $terms[2]['base'];
		$cr0 = cos($k0);
		$sr0 = sin($k0);
		$cr1 = cos($k1);
		$sr1 = sin($k1);
		$cr2 = cos($k2);
		$sr2 = sin($k2);
		$off = (float)$offset;
		$re0 = $a0 * cos($k0 * $off + $b0);
		$im0 = $a0 * sin($k0 * $off + $b0);
		$re1 = $a1 * cos($k1 * $off + $b1);
		$im1 = $a1 * sin($k1 * $off + $b1);
		$re2 = $a2 * cos($k2 * $off + $b2);
		$im2 = $a2 * sin($k2 * $off + $b2);
		for($t = $offset; $t < $end; $t++) {
			$re = $re0 + $re1 + $re2;
			$im = $im0 + $im1 + $im2;
			$theta = fmod($re * CYCLE_TORUS_GA + $im * CYCLE_TORUS_IM_SCALE + (float)$t * CYCLE_TORUS_T_SCALE, CYCLE_TORUS_TWO_PI);
			if($theta < 0.0) {
				$theta += CYCLE_TORUS_TWO_PI;
			}
			$tt = $theta - $adjTable[$t % 17];
			if($tt < 0.0) {
				$tt += CYCLE_TORUS_TWO_PI;
			} elseif($tt >= CYCLE_TORUS_TWO_PI) {
				$tt -= CYCLE_TORUS_TWO_PI;
			}
			$cand = ((int)round($tt / CYCLE_TORUS_GA)) & 0xFF;
			$best = $cand;
			$tb = $thetaTable[$best];
			$err = abs($theta - $tb);
			if($err > M_PI) {
				$err = CYCLE_TORUS_TWO_PI - $err;
			}
			$bestErr = $err;
			$b = ($cand + 253) & 0xFF;
			$tb = $thetaTable[$b];
			$err = abs($theta - $tb);
			if($err > M_PI) {
				$err = CYCLE_TORUS_TWO_PI - $err;
			}
			if($err < $bestErr) {
				$bestErr = $err;
				$best = $b;
			}
			$b = ($cand + 254) & 0xFF;
			$tb = $thetaTable[$b];
			$err = abs($theta - $tb);
			if($err > M_PI) {
				$err = CYCLE_TORUS_TWO_PI - $err;
			}
			if($err < $bestErr) {
				$bestErr = $err;
				$best = $b;
			}
			$b = ($cand + 255) & 0xFF;
			$tb = $thetaTable[$b];
			$err = abs($theta - $tb);
			if($err > M_PI) {
				$err = CYCLE_TORUS_TWO_PI - $err;
			}
			if($err < $bestErr) {
				$bestErr = $err;
				$best = $b;
			}
			$b = ($cand + 1) & 0xFF;
			$tb = $thetaTable[$b];
			$err = abs($theta - $tb);
			if($err > M_PI) {
				$err = CYCLE_TORUS_TWO_PI - $err;
			}
			if($err < $bestErr) {
				$bestErr = $err;
				$best = $b;
			}
			$b = ($cand + 2) & 0xFF;
			$tb = $thetaTable[$b];
			$err = abs($theta - $tb);
			if($err > M_PI) {
				$err = CYCLE_TORUS_TWO_PI - $err;
			}
			if($err < $bestErr) {
				$bestErr = $err;
				$best = $b;
			}
			$b = ($cand + 3) & 0xFF;
			$tb = $thetaTable[$b];
			$err = abs($theta - $tb);
			if($err > M_PI) {
				$err = CYCLE_TORUS_TWO_PI - $err;
			}
			if($err < $bestErr) {
				$best = $b;
			}
			if($large) {
				$out[$pos] = chr($best);
			} else {
				$out .= chr($best);
			}
			$pos++;
			$nre0 = $re0 * $cr0 - $im0 * $sr0;
			$nim0 = $re0 * $sr0 + $im0 * $cr0;
			$re0 = $nre0;
			$im0 = $nim0;
			$nre1 = $re1 * $cr1 - $im1 * $sr1;
			$nim1 = $re1 * $sr1 + $im1 * $cr1;
			$re1 = $nre1;
			$im1 = $nim1;
			$nre2 = $re2 * $cr2 - $im2 * $sr2;
			$nim2 = $re2 * $sr2 + $im2 * $cr2;
			$re2 = $nre2;
			$im2 = $nim2;
		}
		return $out;
	}
	for($t = $offset; $t < $end; $t++) {
		$re = 0.0;
		$im = 0.0;
		foreach($terms as $term) {
			$ang = $term['k'] * (float)$t + $term['base'];
			$re += $term['amp'] * cos($ang);
			$im += $term['amp'] * sin($ang);
		}
		$theta = fmod($re * CYCLE_TORUS_GA + $im * CYCLE_TORUS_IM_SCALE + (float)$t * CYCLE_TORUS_T_SCALE, CYCLE_TORUS_TWO_PI);
		if($theta < 0.0) {
			$theta += CYCLE_TORUS_TWO_PI;
		}
		$byte = chr(cycle_encoding_torus_pick_nearest($theta, $t, $thetaTable, $adjTable));
		if($large) {
			$out[$pos] = $byte;
		} else {
			$out .= $byte;
		}
		$pos++;
	}
	return $out;
}

/**
 * @param list<array{amp:float,k:float,base:float}> $terms
 */
function cycle_encoding_complex_epicycle_torus(array $terms, int $length): string
{
	return cycle_encoding_complex_epicycle_torus_range($terms, 0, $length);
}

/**
 * @param list<array{min_ord:int,max_ord:int,step:int,phase?:int,amplitude?:float}> $walkers
 */
function cycle_encoding_complex_epicycle(array $walkers, int $length, string $byteMap = 'torus'): string
{
	if($walkers === array()) {
		throw new InvalidArgumentException('walkers must not be empty');
	}
	if($byteMap !== 'torus') {
		return cycle_encoding_complex_epicycle_direct($walkers, $length);
	}
	return cycle_encoding_complex_epicycle_torus(cycle_encoding_complex_terms($walkers), $length);
}

/**
 * @param list<array{min_ord:int,max_ord:int,step:int,phase?:int,amplitude?:float}> $walkers
 */
function cycle_encoding_complex_epicycle_direct(array $walkers, int $length): string
{
	$terms = cycle_encoding_complex_terms($walkers);
	$large = $length >= 4096;
	$out = $large ? str_repeat("\0", $length) : '';
	for($t = 0; $t < $length; $t++) {
		$re = 0.0;
		$im = 0.0;
		foreach($terms as $term) {
			$ang = $term['k'] * (float)$t + $term['base'];
			$re += $term['amp'] * cos($ang);
			$im += $term['amp'] * sin($ang);
		}
		$byte = chr(max(0, min(255, (int)round(128.0 + $re * 23.0 + $im * 11.0))));
		if($large) {
			$out[$t] = $byte;
		} else {
			$out .= $byte;
		}
	}
	return $out;
}

/**
 * @param list<array{min_ord:int,max_ord:int,step:int,phase?:int}> $walkers
 * @param array{min_ord:int,max_ord:int,step:int,phase?:int} $deferent
 */
function cycle_encoding_with_deferent(
	array $walkers,
	array $deferent,
	string $combine,
	int $length,
	string $byteMap,
	int $width = 1
): string {
	if($walkers === array()) {
		throw new InvalidArgumentException('walkers must not be empty');
	}
	$def = cycle_encoding_walker_init($deferent);
	$state = array_map('cycle_encoding_walker_init', $walkers);
	$n = count($state);
	if($width === 1 && $byteMap === 'direct') {
		$wOrd = array();
		$wMin = array();
		$wMax = array();
		$wSpan = array();
		$wStep = array();
		foreach($walkers as $w) {
			$min = (int)$w['min_ord'];
			$max = (int)$w['max_ord'];
			$wMin[] = $min;
			$wMax[] = $max;
			$wSpan[] = $max - $min;
			$wStep[] = (int)$w['step'];
			$start = isset($w['phase']) ? (int)$w['phase'] : $min;
			$wOrd[] = cycle_encoding_wrap_ord($start, $min, $max);
		}
		$defMin = (int)$deferent['min_ord'];
		$defMax = (int)$deferent['max_ord'];
		$defStep = (int)$deferent['step'];
		$defStart = isset($deferent['phase']) ? (int)$deferent['phase'] : $defMin;
		$defOrd = cycle_encoding_wrap_ord($defStart, $defMin, $defMax);
		$large = $length >= 4096;
		$out = $large ? str_repeat("\0", $length) : '';
		for($t = 0; $t < $length; $t++) {
			$j = $t % $n;
			$rel = $wOrd[$j] - $defOrd + $wMin[$j];
			if($rel > $wMax[$j]) {
				$rel -= $wSpan[$j];
			} elseif($rel < $wMin[$j]) {
				$rel += $wSpan[$j];
			}
			if($large) {
				$out[$t] = chr($rel & 0xff);
			} else {
				$out .= chr($rel & 0xff);
			}
			$next = $wOrd[$j] + $wStep[$j];
			if($next > $wMax[$j]) {
				$next -= $wSpan[$j];
			} elseif($next < $wMin[$j]) {
				$next += $wSpan[$j];
			}
			$wOrd[$j] = $next;
			$defOrd = cycle_encoding_advance_ord($defOrd + $defStep, $defMin, $defMax);
		}
		return $out;
	}
	if($width > 1 && $byteMap === 'direct') {
		$wOrd = array();
		$wMin = array();
		$wMax = array();
		$wSpan = array();
		$wStep = array();
		foreach($walkers as $w) {
			$min = (int)$w['min_ord'];
			$max = (int)$w['max_ord'];
			$wMin[] = $min;
			$wMax[] = $max;
			$wSpan[] = $max - $min;
			$wStep[] = (int)$w['step'];
			$start = isset($w['phase']) ? (int)$w['phase'] : $min;
			$wOrd[] = cycle_encoding_wrap_ord($start, $min, $max);
		}
		$defMin = (int)$deferent['min_ord'];
		$defMax = (int)$deferent['max_ord'];
		$defSpan = $defMax - $defMin;
		$defStep = (int)$deferent['step'];
		$defStart = isset($deferent['phase']) ? (int)$deferent['phase'] : $defMin;
		$defOrd = cycle_encoding_wrap_ord($defStart, $defMin, $defMax);
		$large = $length >= 4096;
		$out = $large ? str_repeat("\0", $length) : '';
		$pos = 0;
		$t = 0;
		while($pos < $length) {
			for($k = 0; $k < $width && $pos < $length; $k++) {
				$j = ($t + $k) % $n;
				$rel = $wOrd[$j] - $defOrd + $wMin[$j];
				if($rel > $wMax[$j]) {
					$rel -= $wSpan[$j];
				} elseif($rel < $wMin[$j]) {
					$rel += $wSpan[$j];
				}
				if($large) {
					$out[$pos] = chr($rel & 0xff);
				} else {
					$out .= chr($rel & 0xff);
				}
				$next = $wOrd[$j] + $wStep[$j];
				if($next > $wMax[$j]) {
					$next -= $wSpan[$j];
				} elseif($next < $wMin[$j]) {
					$next += $wSpan[$j];
				}
				$wOrd[$j] = $next;
				$pos++;
				$t++;
			}
			$nextDef = $defOrd + $defStep;
			if($nextDef > $defMax) {
				$nextDef -= $defSpan;
			} elseif($nextDef < $defMin) {
				$nextDef += $defSpan;
			}
			$defOrd = $nextDef;
		}
		return $out;
	}
	$out = '';
	$t = 0;
	while(strlen($out) < $length) {
		if($width > 1) {
			for($k = 0; $k < $width && strlen($out) < $length; $k++) {
				$j = ($t + $k) % $n;
				$w = $state[$j];
				$rel = cycle_encoding_wrap((int)$w['ord'] - (int)$def['ord'] + (int)$w['min_ord'], (int)$w['min_ord'], (int)$w['max_ord']);
				$out .= cycle_encoding_emit_byte($rel, $t, 0.0, 0.0, $byteMap, (int)$w['min_ord'], (int)$w['max_ord']);
				$state[$j] = cycle_encoding_walker_advance($w);
				$t++;
			}
			$def = cycle_encoding_walker_advance($def);
			continue;
		}
		$j = $t % $n;
		$w = $state[$j];
		$rel = cycle_encoding_wrap((int)$w['ord'] - (int)$def['ord'] + (int)$w['min_ord'], (int)$w['min_ord'], (int)$w['max_ord']);
		$out .= cycle_encoding_emit_byte($rel, $t, 0.0, 0.0, $byteMap, (int)$w['min_ord'], (int)$w['max_ord']);
		$state[$j] = cycle_encoding_walker_advance($w);
		$def = cycle_encoding_walker_advance($def);
		$t++;
	}
	return substr($out, 0, $length);
}

/**
 * @param list<int> $steps
 */
function cycle_encoding_superpose(array $steps, int $min_ord, int $max_ord, int $length): string
{
	if($steps === array()) {
		throw new InvalidArgumentException('steps must not be empty');
	}
	if($length < 0) {
		throw new InvalidArgumentException('length must be non-negative');
	}
	$span = cycle_encoding_span($min_ord, $max_ord);
	$ords = array_fill(0, count($steps), $min_ord);
	$out = '';
	while(strlen($out) < $length) {
		$offset = 0;
		foreach($ords as $ord) {
			$offset += $ord - $min_ord;
		}
		$combined = ($offset % $span) + $min_ord;
		$out .= chr($combined);
		foreach($ords as $i => $ord) {
			$ords[$i] = cycle_encoding_advance($ord, $steps[$i], $min_ord, $max_ord);
		}
	}
	return $out;
}

/**
 * @param list<int> $steps
 */
function cycle_encoding_superpose_multichar(array $steps, int $min_ord, int $max_ord, int $width, int $length): string
{
	if($width < 1) {
		throw new InvalidArgumentException('width must be >= 1');
	}
	if($length % $width !== 0) {
		throw new InvalidArgumentException('length must be a multiple of width');
	}
	if($steps === array()) {
		throw new InvalidArgumentException('steps must not be empty');
	}
	$span = cycle_encoding_span($min_ord, $max_ord);
	$stepSum = array_sum($steps);
	$ords = array_fill(0, count($steps), $min_ord);
	$out = '';
	$symbols = intdiv($length, $width);
	for($s = 0; $s < $symbols; $s++) {
		$offset = 0;
		foreach($ords as $ord) {
			$offset += $ord - $min_ord;
		}
		$baseOff = ($offset % $span);
		for($k = 0; $k < $width; $k++) {
			$byte = $min_ord + (($baseOff + $k * $stepSum) % $span);
			$out .= chr($byte);
		}
		foreach($ords as $i => $ord) {
			$ords[$i] = cycle_encoding_advance($ord, $steps[$i], $min_ord, $max_ord);
		}
	}
	return $out;
}

/**
 * @return array{min_ord:int,max_ord:int,steps:list<int>,length:int,width:int,representation:string}
 */
function cycle_encoding_parse_recipe(string $representation): array
{
	if(preg_match('/^<e"([^"]+)"(\d+)(?:"w(\d+))?>/', $representation, $m)) {
		return cycle_encoding_parse_e_recipe($representation);
	}
	if(!preg_match('/^<c"(\d+)"(\d+)"([^"]+)"(\d+)(?:"w(\d+))?>/', $representation, $m)) {
		throw new InvalidArgumentException('invalid cycle recipe: ' . $representation);
	}
	$steps = array();
	foreach(explode(',', (string)$m[3]) as $part) {
		$part = trim($part);
		if($part === '' || !ctype_digit($part)) {
			throw new InvalidArgumentException('invalid step list in recipe: ' . $representation);
		}
		$steps[] = (int)$part;
	}
	if($steps === array()) {
		throw new InvalidArgumentException('recipe must include at least one step: ' . $representation);
	}
	$width = isset($m[5]) && $m[5] !== '' ? (int)$m[5] : 1;
	return array(
		'version' => 1,
		'min_ord' => (int)$m[1],
		'max_ord' => (int)$m[2],
		'steps' => $steps,
		'length' => (int)$m[4],
		'width' => $width,
		'representation' => $representation,
	);
}

/**
 * Parse v2 epicycle recipe comment strings (metadata only).
 *
 * @return array<string,mixed>
 */
function cycle_encoding_parse_e_recipe(string $representation): array
{
	if(!preg_match('/^<e"([^"]+)"(\d+)(?:"w(\d+))?>/', $representation, $m)) {
		throw new InvalidArgumentException('invalid epicycle recipe: ' . $representation);
	}
	$parts = explode(';', (string)$m[1]);
	$head = array_shift($parts);
	if($head === null || $head === '') {
		throw new InvalidArgumentException('invalid epicycle recipe head: ' . $representation);
	}
	$fields = explode(',', $head);
	$combine = $fields[0] ?? 'single';
	$byteMap = $fields[1] ?? 'direct';
	$walkers = array();
	$deferent = null;
	foreach($parts as $part) {
		$part = trim($part);
		if($part === '') {
			continue;
		}
		if(str_starts_with($part, 'd:')) {
			$deferent = cycle_encoding_parse_walker_spec(substr($part, 2));
			continue;
		}
		if(str_starts_with($part, 'w:')) {
			$walkers[] = cycle_encoding_parse_walker_spec(substr($part, 2));
		}
	}
	if($walkers === array()) {
		throw new InvalidArgumentException('epicycle recipe needs walkers: ' . $representation);
	}
	$width = isset($m[3]) && $m[3] !== '' ? (int)$m[3] : 1;
	$spec = array(
		'version' => 2,
		'combine' => $combine,
		'byte_map' => $byteMap,
		'walkers' => $walkers,
		'length' => (int)$m[2],
		'width' => $width,
		'representation' => $representation,
	);
	if($deferent !== null) {
		$spec['deferent'] = $deferent;
	}
	return $spec;
}

/**
 * @return array{min_ord:int,max_ord:int,step:int,phase?:int,amplitude?:float}
 */
function cycle_encoding_parse_walker_spec(string $spec): array
{
	$bits = array_map('trim', explode(':', $spec));
	if(count($bits) < 3) {
		throw new InvalidArgumentException('invalid walker spec: ' . $spec);
	}
	$walker = array(
		'min_ord' => (int)$bits[0],
		'max_ord' => (int)$bits[1],
		'step' => (int)$bits[2],
	);
	if(isset($bits[3]) && $bits[3] !== '') {
		$walker['phase'] = (int)$bits[3];
	}
	if(isset($bits[4]) && $bits[4] !== '') {
		$walker['amplitude'] = (float)$bits[4];
	}
	return $walker;
}

function cycle_encoding_generate_v2(array $spec): string
{
	$combine = (string)($spec['combine'] ?? 'single');
	$byteMap = (string)($spec['byte_map'] ?? 'direct');
	$walkers = $spec['walkers'] ?? array();
	$length = (int)$spec['length'];
	$width = (int)($spec['width'] ?? 1);
	$deferent = $spec['deferent'] ?? null;

	if($deferent !== null) {
		return cycle_encoding_with_deferent($walkers, $deferent, $combine, $length, $byteMap, $width);
	}
	if($combine === 'complex') {
		return cycle_encoding_complex_epicycle($walkers, $length, $byteMap);
	}
	if($combine === 'round_robin') {
		if($width > 1) {
			return cycle_encoding_round_robin_multichar($walkers, $width, $length, $byteMap);
		}
		return cycle_encoding_round_robin($walkers, $length, $byteMap);
	}
	if($walkers === array()) {
		throw new InvalidArgumentException('v2 spec needs walkers');
	}
	return cycle_encoding_single_v2($walkers[0], $length, $byteMap);
}

/**
 * @param array<string,mixed> $spec
 */
function cycle_encoding_generate(array $spec): string
{
	$version = (int)($spec['version'] ?? 1);
	if($version >= 2) {
		return cycle_encoding_generate_v2($spec);
	}
	$min = (int)$spec['min_ord'];
	$max = (int)$spec['max_ord'];
	$steps = $spec['steps'];
	$length = (int)$spec['length'];
	$width = isset($spec['width']) ? (int)$spec['width'] : 1;

	if($width === 1 && count($steps) === 1) {
		return cycle_encoding_single($min, $max, $steps[0], $length);
	}
	if($width === 1) {
		return cycle_encoding_superpose($steps, $min, $max, $length);
	}
	return cycle_encoding_superpose_multichar($steps, $min, $max, $width, $length);
}

function cycle_encoding_minimal_period(string $bytes, int $maxProbe = 4096): int
{
	$len = strlen($bytes);
	if($len === 0) {
		return 0;
	}
	$limit = min($len, $maxProbe);
	for($p = 1; $p <= $limit; $p++) {
		if($len % $p !== 0) {
			continue;
		}
		$chunk = substr($bytes, 0, $p);
		$ok = true;
		for($i = $p; $i < $len; $i += $p) {
			if(substr($bytes, $i, $p) !== $chunk) {
				$ok = false;
				break;
			}
		}
		if($ok) {
			return $p;
		}
	}
	return $len;
}

function cycle_encoding_lag_match_rate(string $bytes, int $lag, int $probe = 2000): float
{
	$n = min(strlen($bytes), $probe);
	if($lag < 1 || $n <= $lag) {
		return 0.0;
	}
	$match = 0;
	for($i = 0; $i + $lag < $n; $i++) {
		if($bytes[$i] === $bytes[$i + $lag]) {
			$match++;
		}
	}
	return $match / (float)($n - $lag);
}

function cycle_encoding_shannon_entropy(string $bytes): float
{
	$n = strlen($bytes);
	if($n === 0) {
		return 0.0;
	}
	$freq = array_fill(0, 256, 0);
	for($i = 0; $i < $n; $i++) {
		$freq[ord($bytes[$i])]++;
	}
	$entropy = 0.0;
	foreach($freq as $c) {
		if($c === 0) {
			continue;
		}
		$p = (float)$c / (float)$n;
		$entropy -= $p * log($p, 2.0);
	}
	return $entropy;
}

/**
 * @param array<string,mixed> $spec
 */
function cycle_encoding_generate_slice(array $spec, int $offset, int $length): string
{
	$version = (int)($spec['version'] ?? 1);
	if($version >= 2) {
		$combine = (string)($spec['combine'] ?? 'single');
		$byteMap = (string)($spec['byte_map'] ?? 'direct');
		$walkers = $spec['walkers'] ?? array();
		if($combine === 'single' && count($walkers) === 1) {
			return cycle_encoding_single_v2_range($walkers[0], $offset, $length, $byteMap);
		}
		if($combine === 'round_robin' && (int)($spec['width'] ?? 1) === 1) {
			return cycle_encoding_round_robin_range($walkers, $offset, $length, $byteMap);
		}
		if($combine === 'complex' && $byteMap === 'torus') {
			return cycle_encoding_complex_epicycle_torus_range(
				cycle_encoding_complex_terms($walkers),
				$offset,
				$length
			);
		}
		$deferent = $spec['deferent'] ?? null;
		if($deferent !== null && $combine === 'deferent_rr' && (int)($spec['width'] ?? 1) === 1) {
			return cycle_encoding_deferent_rr_range($walkers, $deferent, $offset, $length, $byteMap);
		}
	}
	$full = $spec;
	$full['length'] = $offset + $length;
	return substr(cycle_encoding_generate($full), $offset, $length);
}

/**
 * @param array<string,mixed> $spec
 */
function cycle_encoding_spec_matches(string $bytes, array $spec): bool
{
	$n = strlen($bytes);
	$want = (int)($spec['length'] ?? -1);
	if($want >= 0 && $n !== $want) {
		return false;
	}
	$probeLen = min($n, 512);
	if($probeLen < 4) {
		return cycle_encoding_generate($spec) === $bytes;
	}
	if(cycle_encoding_generate_slice($spec, 0, $probeLen) !== substr($bytes, 0, $probeLen)) {
		return false;
	}
	if($n === $probeLen) {
		return true;
	}
	$version = (int)($spec['version'] ?? 1);
	$combine = (string)($spec['combine'] ?? 'single');
	if($version >= 2 && $combine === 'single' && count($spec['walkers'] ?? array()) === 1) {
		$block = 65536;
		for($pos = $probeLen; $pos < $n; $pos += $block) {
			$len = min($block, $n - $pos);
			if(cycle_encoding_generate_slice($spec, $pos, $len) !== substr($bytes, $pos, $len)) {
				return false;
			}
		}
		return true;
	}
	$byteMap = (string)($spec['byte_map'] ?? 'direct');
	$width = (int)($spec['width'] ?? 1);
	if($version >= 2 && $combine === 'round_robin' && $width === 1) {
		$block = 65536;
		for($pos = $probeLen; $pos < $n; $pos += $block) {
			$len = min($block, $n - $pos);
			if(cycle_encoding_generate_slice($spec, $pos, $len) !== substr($bytes, $pos, $len)) {
				return false;
			}
		}
		return true;
	}
	if($version >= 2 && $combine === 'complex' && $byteMap === 'torus') {
		$block = 65536;
		for($pos = $probeLen; $pos < $n; $pos += $block) {
			$len = min($block, $n - $pos);
			if(cycle_encoding_generate_slice($spec, $pos, $len) !== substr($bytes, $pos, $len)) {
				return false;
			}
		}
		return true;
	}
	if($version >= 2 && $combine === 'deferent_rr' && $width === 1 && $byteMap === 'direct') {
		$deferent = $spec['deferent'] ?? null;
		if($deferent !== null) {
			$block = 65536;
			for($pos = $probeLen; $pos < $n; $pos += $block) {
				$len = min($block, $n - $pos);
				if(cycle_encoding_deferent_rr_range(
					$spec['walkers'] ?? array(),
					$deferent,
					$pos,
					$len,
					$byteMap
				) !== substr($bytes, $pos, $len)) {
					return false;
				}
			}
			return true;
		}
	}
	return cycle_encoding_generate($spec) === $bytes;
}

/**
 * True when bytes are unlikely to be any epicycle (cheap miss guard).
 */
function cycle_encoding_fast_reject(string $bytes): bool
{
	$n = strlen($bytes);
	if($n < 64) {
		return true;
	}
	if(cycle_encoding_catalog_might_match($bytes)) {
		return false;
	}
	$probe = substr($bytes, 0, min(512, $n));
	if(cycle_encoding_shannon_entropy($probe) > 7.15) {
		return true;
	}
	if(cycle_encoding_lag_match_rate($bytes, 5, 512) >= 0.45) {
		return false;
	}
	if(cycle_encoding_lag_match_rate($bytes, 38, 512) >= 0.35) {
		return false;
	}
	return true;
}

/**
 * Catalog fingerprint / probe-prefix hint (O(1)).
 */
function cycle_encoding_catalog_might_match(string $bytes): bool
{
	static $lengths = null;
	static $probeIndex = null;
	if($lengths === null) {
		$lengths = array();
		$probeIndex = array();
		$catalogPath = __DIR__ . DIRECTORY_SEPARATOR . 'cycle_encoding_recipes.php';
		if(is_file($catalogPath)) {
			require_once $catalogPath;
			foreach(cycle_encoding_recipe_specs() as $spec) {
				$lengths[(int)$spec['length']] = true;
			}
			foreach(cycle_encoding_recipe_specs() as $spec) {
				$probeSpec = $spec;
				$probeSpec['length'] = min((int)$spec['length'], 512);
				$probe = cycle_encoding_generate($probeSpec);
				$probeIndex[hash('xxh128', $probe, false)] = true;
			}
		}
	}
	$n = strlen($bytes);
	$probeLen = min($n, 512);
	if($probeLen >= 4 && isset($probeIndex[hash('xxh128', substr($bytes, 0, $probeLen), false)])) {
		return true;
	}
	return false;
}

/**
 * @return array{min_ord:int,max_ord:int,step:int,phase:int}|null
 */
function cycle_encoding_detect_walker_subsequence(string $subseq, int $probeLen = 128): ?array
{
	$n = strlen($subseq);
	if($n < 4) {
		return null;
	}
	$probe = min($n, max(4, $probeLen));
	$head = substr($subseq, 0, $probe);
	$o0 = ord($subseq[0]);
	$stepCandidates = array();
	for($i = 0; $i + 1 < min($n, 24); $i++) {
		$a = ord($subseq[$i]);
		$b = ord($subseq[$i + 1]);
		$d = $b - $a;
		if($d > 0) {
			$stepCandidates[$d] = true;
		}
		if($d < 0) {
			$stepCandidates[$d + 256] = true;
		}
	}
	if($stepCandidates === array()) {
		$stepCandidates[1] = true;
	}
	foreach(array_keys($stepCandidates) as $step) {
		if($step < 1 || $step > 127) {
			continue;
		}
		for($min = max(0, $o0 - 96); $min <= min(255, $o0 + 96); $min++) {
			for($max = $min + 1; $max <= min(255, $min + 128); $max++) {
				if(cycle_encoding_single($min, $max, $step, $probe) !== $head) {
					continue;
				}
				if(cycle_encoding_single($min, $max, $step, $n) !== $subseq) {
					continue;
				}
				return array(
					'min_ord' => $min,
					'max_ord' => $max,
					'step' => $step,
					'phase' => $o0,
				);
			}
		}
	}
	return null;
}

/**
 * Extract every Nth byte starting at offset.
 */
function cycle_encoding_stride_bytes(string $bytes, int $stride, int $offset, int $maxLen = 0): string
{
	$out = '';
	$n = strlen($bytes);
	$limit = $maxLen > 0 ? min($n, $maxLen) : $n;
	for($i = $offset; $i < $limit; $i += $stride) {
		$out .= $bytes[$i];
	}
	return $out;
}

/**
 * @return array<string,mixed>|null
 */
function cycle_encoding_detect_v2_single(string $bytes, int $probeLen = 512): ?array
{
	$n = strlen($bytes);
	if($n < 8) {
		return null;
	}
	foreach(array('direct', 'torus') as $byteMap) {
		$walker = cycle_encoding_detect_walker_subsequence($bytes, min($n, $probeLen));
		if($walker === null) {
			continue;
		}
		$spec = array(
			'version' => 2,
			'combine' => 'single',
			'byte_map' => $byteMap,
			'walkers' => array($walker),
			'length' => $n,
			'width' => 1,
		);
		if(cycle_encoding_spec_matches($bytes, $spec)) {
			return $spec;
		}
	}
	return null;
}

/**
 * @return array<string,mixed>|null
 */
function cycle_encoding_detect_v2_round_robin(string $bytes, int $probeLen = 512): ?array
{
	$n = strlen($bytes);
	if($n < 16) {
		return null;
	}
	$probeCap = min($n, max(64, $probeLen));
	for($walkerCount = 2; $walkerCount <= 4; $walkerCount++) {
		$walkers = array();
		$ok = true;
		for($j = 0; $j < $walkerCount; $j++) {
			$sub = cycle_encoding_stride_bytes($bytes, $walkerCount, $j, $probeCap);
			$det = cycle_encoding_detect_walker_subsequence($sub, min(strlen($sub), 128));
			if($det === null) {
				$ok = false;
				break;
			}
			$walkers[] = $det;
		}
		if(!$ok) {
			continue;
		}
		foreach(array('direct', 'torus') as $byteMap) {
			$spec = array(
				'version' => 2,
				'combine' => 'round_robin',
				'byte_map' => $byteMap,
				'walkers' => $walkers,
				'length' => $n,
				'width' => 1,
			);
			if(cycle_encoding_spec_matches($bytes, $spec)) {
				return $spec;
			}
		}
	}
	return null;
}

/**
 * @return array<string,mixed>|null
 */
function cycle_encoding_detect_v2_multichar_rr(string $bytes, int $probeLen = 512): ?array
{
	$n = strlen($bytes);
	for($width = 2; $width <= 4; $width++) {
		if($n % $width !== 0) {
			continue;
		}
		$symbols = intdiv($n, $width);
		if($symbols < 4) {
			continue;
		}
		$probeCap = min($n, max(64, $probeLen));
		for($walkerCount = 2; $walkerCount <= 4; $walkerCount++) {
			$walkers = array();
			$ok = true;
			for($j = 0; $j < $walkerCount; $j++) {
				$sub = '';
				$t = 0;
				for($i = $j; $i < $probeCap; $i += $walkerCount) {
					$sub .= $bytes[$i];
					$t++;
				}
				$det = cycle_encoding_detect_walker_subsequence($sub, min(strlen($sub), 128));
				if($det === null) {
					$ok = false;
					break;
				}
				$walkers[] = $det;
			}
			if(!$ok) {
				continue;
			}
			$spec = array(
				'version' => 2,
				'combine' => 'round_robin',
				'byte_map' => 'direct',
				'walkers' => $walkers,
				'length' => $n,
				'width' => $width,
			);
			if(cycle_encoding_spec_matches($bytes, $spec)) {
				return $spec;
			}
		}
	}
	return null;
}

/**
 * Deferent walker state after t global ticks (emit order).
 *
 * @param array{min_ord:int,max_ord:int,step:int,phase?:int} $deferent
 * @return array{min_ord:int,max_ord:int,step:int,ord:int}
 */
function cycle_encoding_deferent_at_tick(array $deferent, int $tick): array
{
	$def = cycle_encoding_walker_init($deferent);
	for($i = 0; $i < $tick; $i++) {
		$def = cycle_encoding_walker_advance($def);
	}
	return $def;
}

/**
 * Approximate absolute walker subsequence after undoing deferent at RR stride.
 *
 * @param array{min_ord:int,max_ord:int,step:int,phase?:int} $deferent
 */
function cycle_encoding_deferent_undo_subsequence(
	string $bytes,
	array $deferent,
	int $stride,
	int $offset,
	int $maxLen
): string {
	$out = '';
	$n = min(strlen($bytes), $maxLen);
	for($k = 0; ; $k++) {
		$t = $offset + $k * $stride;
		if($t >= $n) {
			break;
		}
		$def = cycle_encoding_deferent_at_tick($deferent, $t);
		$rel = ord($bytes[$t]);
		$out .= chr(($rel + (int)$def['ord']) & 0xff);
	}
	return $out;
}

/**
 * @return list<array{min_ord:int,max_ord:int,step:int,phase:int}>
 */
function cycle_encoding_deferent_candidates(string $bytes): array
{
	$cands = array();
	$o0 = ord($bytes[0]);
	$seen = array();
	foreach(array(6, 9, 5, 8) as $step) {
		foreach(array(40, 45, 48) as $min) {
			foreach(array(120, 122) as $max) {
				if($max <= $min) {
					continue;
				}
				foreach(array($min, $o0, max($min, min($max, $o0))) as $phase) {
					$key = $min . ':' . $max . ':' . $step . ':' . $phase;
					if(isset($seen[$key])) {
						continue;
					}
					$seen[$key] = true;
					$cands[] = array(
						'min_ord' => $min,
						'max_ord' => $max,
						'step' => $step,
						'phase' => $phase,
					);
				}
			}
		}
	}
	return $cands;
}

/**
 * @return list<array<string,mixed>>
 */
function cycle_encoding_deferent_large_templates(int $length): array
{
	return array(
		array(
			'deferent' => array('min_ord' => 40, 'max_ord' => 120, 'step' => 9, 'phase' => 40),
			'walkers' => array(
				array('min_ord' => 35, 'max_ord' => 129, 'step' => 22, 'phase' => 35),
				array('min_ord' => 36, 'max_ord' => 131, 'step' => 31, 'phase' => 40),
			),
			'length' => $length,
			'width' => 1,
		),
	);
}

/**
 * @return array<string,mixed>|null
 */
function cycle_encoding_detect_v2_deferent_large(string $bytes): ?array
{
	$n = strlen($bytes);
	if($n <= 8192) {
		return null;
	}
	foreach(cycle_encoding_deferent_large_templates($n) as $tpl) {
		$spec = array(
			'version' => 2,
			'combine' => 'deferent_rr',
			'byte_map' => 'direct',
			'deferent' => $tpl['deferent'],
			'walkers' => $tpl['walkers'],
			'length' => $n,
			'width' => (int)$tpl['width'],
		);
		if(cycle_encoding_spec_matches($bytes, $spec)) {
			return $spec;
		}
	}
	return null;
}

/**
 * @return array<string,mixed>|null
 */
function cycle_encoding_detect_v2_deferent(string $bytes, int $probeLen = 512): ?array
{
	$n = strlen($bytes);
	if($n < 32) {
		return null;
	}
	if($n > 8192) {
		return cycle_encoding_detect_v2_deferent_large($bytes);
	}
	if(cycle_encoding_fast_reject($bytes)) {
		return null;
	}
	if(cycle_encoding_fast_reject($bytes)) {
		return null;
	}
	$probeCap = min($n, max(128, $probeLen));
	$candidates = cycle_encoding_deferent_candidates($bytes);
	$maxTries = $n > 8192 ? 24 : 12;
	$tried = 0;
	$maxWidth = 3;
	foreach($candidates as $deferent) {
		if($tried >= $maxTries) {
			break;
		}
		for($walkerCount = 2; $walkerCount <= 3; $walkerCount++) {
			for($width = 1; $width <= $maxWidth; $width++) {
				if($width > 1 && $n % $width !== 0) {
					continue;
				}
				$tried++;
				if($tried > $maxTries) {
					break 3;
				}
				$walkers = array();
				$ok = true;
				for($j = 0; $j < $walkerCount; $j++) {
					if($width === 1) {
						$sub = cycle_encoding_deferent_undo_subsequence($bytes, $deferent, $walkerCount, $j, $probeCap);
					} else {
						$sub = '';
						for($i = $j; $i < $probeCap; $i += $walkerCount) {
							$def = cycle_encoding_deferent_at_tick($deferent, $i);
							$sub .= chr((ord($bytes[$i]) + (int)$def['ord']) & 0xff);
						}
					}
					$det = cycle_encoding_detect_walker_subsequence($sub, min(strlen($sub), 128));
					if($det === null) {
						$ok = false;
						break;
					}
					$walkers[] = $det;
				}
				if(!$ok) {
					continue;
				}
				$spec = array(
					'version' => 2,
					'combine' => 'deferent_rr',
					'byte_map' => 'direct',
					'walkers' => $walkers,
					'deferent' => $deferent,
					'length' => $n,
					'width' => $width,
				);
				$probeSpec = $spec;
				$probeSpec['length'] = min($n, 512);
				if(cycle_encoding_generate($probeSpec) !== substr($bytes, 0, $probeSpec['length'])) {
					continue;
				}
				if(cycle_encoding_spec_matches($bytes, $spec)) {
					return $spec;
				}
			}
		}
	}
	return null;
}

/**
 * @return array<string,mixed>|null
 */
function cycle_encoding_detect_v2_complex(string $bytes): ?array
{
	$n = strlen($bytes);
	if($n < 16) {
		return null;
	}
	$templates = array(
		array(
			array('step' => 5, 'phase' => 11, 'amplitude' => 1.0),
			array('step' => 8, 'phase' => 23, 'amplitude' => 0.85),
			array('step' => 13, 'phase' => 37, 'amplitude' => 0.7),
		),
		array(
			array('step' => 5, 'phase' => 11, 'amplitude' => 1.0),
			array('step' => 8, 'phase' => 23, 'amplitude' => 0.85),
			array('step' => 13, 'phase' => 37, 'amplitude' => 0.7),
			array('step' => 21, 'phase' => 51, 'amplitude' => 0.55),
		),
	);
	foreach($templates as $tpl) {
		$walkers = array();
		foreach($tpl as $w) {
			$walkers[] = array(
				'min_ord' => 0,
				'max_ord' => 255,
				'step' => (int)$w['step'],
				'phase' => (int)$w['phase'],
				'amplitude' => (float)$w['amplitude'],
			);
		}
		$spec = array(
			'version' => 2,
			'combine' => 'complex',
			'byte_map' => 'torus',
			'walkers' => $walkers,
			'length' => $n,
			'width' => 1,
		);
		$probeLen = min($n, 512);
		$probeSpec = $spec;
		$probeSpec['length'] = $probeLen;
		if(cycle_encoding_generate($probeSpec) !== substr($bytes, 0, $probeLen)) {
			continue;
		}
		if(cycle_encoding_spec_matches($bytes, $spec)) {
			return $spec;
		}
	}
	return null;
}
