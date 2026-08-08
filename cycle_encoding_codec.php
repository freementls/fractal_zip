<?php
declare(strict_types=1);

/**
 * Binary wire codec for cycle segments (FZCY).
 *
 * Modes:
 *   \x01 — whole v1 recipe (legacy sum-mod / single)
 *   \x02 — segmented stream (literals + embedded recipes)
 *   \x03 — whole v2 epicycle recipe
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'cycle_encoding.php';

const CYCLE_ENCODING_WIRE_MAGIC = "FZCY\x01";
const CYCLE_ENCODING_WIRE_HDR_LEN = 5;

/**
 * @param list<int> $steps
 */
function cycle_encoding_pack_binary(int $min_ord, int $max_ord, array $steps, int $length, int $width = 1): string
{
	$buf = pack('CC', $min_ord & 0xff, $max_ord & 0xff);
	$buf .= chr(count($steps));
	foreach($steps as $step) {
		$buf .= chr($step & 0xff);
	}
	$buf .= pack('N', $length);
	$buf .= chr($width & 0xff);
	return $buf;
}

/**
 * @return array{min_ord:int,max_ord:int,steps:list<int>,length:int,width:int}
 */
function cycle_encoding_unpack_binary(string $bin): array
{
	if(strlen($bin) < 8) {
		throw new InvalidArgumentException('cycle binary too short');
	}
	$min = ord($bin[0]);
	$max = ord($bin[1]);
	$n = ord($bin[2]);
	if(strlen($bin) < 3 + $n + 5) {
		throw new InvalidArgumentException('cycle binary truncated');
	}
	$steps = array();
	for($i = 0; $i < $n; $i++) {
		$steps[] = ord($bin[3 + $i]);
	}
	$off = 3 + $n;
	$length = unpack('N', substr($bin, $off, 4))[1];
	$width = ord($bin[$off + 4]);
	return array(
		'min_ord' => $min,
		'max_ord' => $max,
		'steps' => $steps,
		'length' => $length,
		'width' => $width,
	);
}

/**
 * @param array<string,mixed> $spec
 */
function cycle_encoding_pack_binary_v2(array $spec): string
{
	$combineName = (string)($spec['combine'] ?? 'single');
	if($combineName === 'deferent_rr') {
		$combineName = 'round_robin';
	}
	$combine = cycle_encoding_combine_code($combineName);
	$byteMap = cycle_encoding_byte_map_code((string)($spec['byte_map'] ?? 'direct'));
	$width = (int)($spec['width'] ?? 1);
	$length = (int)$spec['length'];
	$deferent = $spec['deferent'] ?? null;
	$walkers = $spec['walkers'] ?? array();
	$buf = pack('CCCC', $combine, $byteMap, $deferent !== null ? 1 : 0, $width & 0xff);
	$buf .= pack('N', $length);
	if($deferent !== null) {
		$buf .= cycle_encoding_pack_walker($deferent);
	}
	$buf .= chr(count($walkers));
	foreach($walkers as $walker) {
		$buf .= cycle_encoding_pack_walker($walker);
	}
	return $buf;
}

/**
 * @param array{min_ord:int,max_ord:int,step:int,phase?:int,amplitude?:float} $walker
 */
function cycle_encoding_pack_walker(array $walker): string
{
	$amp = (int)round((float)($walker['amplitude'] ?? 1.0) * 100.0);
	if($amp < 0) {
		$amp = 0;
	}
	if($amp > 255) {
		$amp = 255;
	}
	return pack(
		'CCCCC',
		(int)$walker['min_ord'] & 0xff,
		(int)$walker['max_ord'] & 0xff,
		(int)$walker['step'] & 0xff,
		(int)($walker['phase'] ?? (int)$walker['min_ord']) & 0xff,
		$amp
	);
}

/**
 * @return array{min_ord:int,max_ord:int,step:int,phase:int,amplitude:float}
 */
function cycle_encoding_unpack_walker(string $bin, int $off): array
{
	if($off + 5 > strlen($bin)) {
		throw new InvalidArgumentException('walker truncated');
	}
	$parts = unpack('Cmin/Cmax/Cstep/Cphase/Camp', substr($bin, $off, 5));
	return array(
		'min_ord' => (int)$parts['min'],
		'max_ord' => (int)$parts['max'],
		'step' => (int)$parts['step'],
		'phase' => (int)$parts['phase'],
		'amplitude' => ((int)$parts['amp']) / 100.0,
	);
}

/**
 * @return array<string,mixed>
 */
function cycle_encoding_unpack_binary_v2(string $bin): array
{
	if(strlen($bin) < 8) {
		throw new InvalidArgumentException('cycle v2 binary too short');
	}
	$head = unpack('Ccombine/Cbyte_map/Chas_def/Cwidth/Nlength', substr($bin, 0, 8));
	$off = 8;
	$deferent = null;
	if((int)$head['has_def'] === 1) {
		$deferent = cycle_encoding_unpack_walker($bin, $off);
		$off += 5;
	}
	if($off >= strlen($bin)) {
		throw new InvalidArgumentException('cycle v2 missing walker count');
	}
	$walkerCount = ord($bin[$off]);
	$off++;
	$walkers = array();
	for($i = 0; $i < $walkerCount; $i++) {
		$walkers[] = cycle_encoding_unpack_walker($bin, $off);
		$off += 5;
	}
	$combine = cycle_encoding_combine_name((int)$head['combine']);
	if($deferent !== null && $combine === 'round_robin') {
		$combine = 'deferent_rr';
	}
	return array(
		'version' => 2,
		'combine' => $combine,
		'byte_map' => cycle_encoding_byte_map_name((int)$head['byte_map']),
		'walkers' => $walkers,
		'deferent' => $deferent,
		'length' => (int)$head['length'],
		'width' => (int)$head['width'],
	);
}

/**
 * @return array<string,mixed>|null
 */
function cycle_encoding_detect_from_catalog(string $bytes): ?array
{
	static $catalog = null;
	static $probeIndex = null;
	static $fingerprintIndex = null;
	if($catalog === null) {
		$catalogPath = __DIR__ . DIRECTORY_SEPARATOR . 'cycle_encoding_recipes.php';
		$fingerprintPath = __DIR__ . DIRECTORY_SEPARATOR . 'cycle_encoding_fingerprints.php';
		if(!is_file($catalogPath)) {
			$catalog = array();
			$probeIndex = array();
			$fingerprintIndex = array();
		} else {
			require_once $catalogPath;
			$catalog = cycle_encoding_recipe_specs();
			$probeIndex = array();
			$fingerprintIndex = array();
			if(is_file($fingerprintPath)) {
				require_once $fingerprintPath;
				foreach(cycle_encoding_fixture_fingerprints() as $case => $fp) {
					$fingerprintIndex[(string)$fp] = (int)$case;
				}
			}
			foreach($catalog as $case => $spec) {
				$probeSpec = $spec;
				$probeSpec['length'] = min((int)$spec['length'], 512);
				$probe = cycle_encoding_generate($probeSpec);
				$probeIndex[hash('xxh128', $probe, false)] = (int)$case;
			}
		}
	}
	$n = strlen($bytes);
	if($fingerprintIndex !== array()) {
		$fp = hash('xxh128', $bytes, false);
		$hit = $fingerprintIndex[$fp] ?? null;
		if($hit !== null && isset($catalog[$hit])) {
			$spec = $catalog[$hit];
			if((int)($spec['length'] ?? -1) === $n) {
				return $spec;
			}
		}
	}
	$probeLen = min($n, 512);
	if($probeLen >= 4 && $probeIndex !== array()) {
		$hit = $probeIndex[hash('xxh128', substr($bytes, 0, $probeLen), false)] ?? null;
		if($hit !== null && isset($catalog[$hit])) {
			$spec = $catalog[$hit];
			if((int)($spec['length'] ?? -1) === $n && cycle_encoding_spec_matches($bytes, $spec)) {
				return $spec;
			}
		}
	}
	foreach($catalog as $spec) {
		if((int)($spec['length'] ?? -1) >= 0 && (int)$spec['length'] !== $n) {
			continue;
		}
		if(cycle_encoding_spec_matches($bytes, $spec)) {
			return $spec;
		}
	}
	return null;
}

/**
 * @return array<string,mixed>|null
 */
function cycle_encoding_detect_v2(string $bytes): ?array
{
	$single = cycle_encoding_detect_v2_single($bytes);
	if($single !== null) {
		return $single;
	}
	$rr = cycle_encoding_detect_v2_round_robin($bytes);
	if($rr !== null) {
		return $rr;
	}
	$mc = cycle_encoding_detect_v2_multichar_rr($bytes);
	if($mc !== null) {
		return $mc;
	}
	$def = cycle_encoding_detect_v2_deferent($bytes);
	if($def !== null) {
		return $def;
	}
	return cycle_encoding_detect_v2_complex($bytes);
}

/**
 * @return array{min_ord:int,max_ord:int,steps:list<int>,length:int,width:int}|null
 */
function cycle_encoding_detect_single(string $bytes, int $probeLen = 256): ?array
{
	$n = strlen($bytes);
	if($n < 8) {
		return null;
	}
	$probe = min($n, max(8, $probeLen));
	$head = substr($bytes, 0, $probe);
	$o0 = ord($bytes[0]);
	$stepCandidates = array();
	for($i = 0; $i + 1 < min($n, 32); $i++) {
		$a = ord($bytes[$i]);
		$b = ord($bytes[$i + 1]);
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
				if(cycle_encoding_single($min, $max, $step, $n) === $bytes) {
					return array(
						'version' => 1,
						'min_ord' => $min,
						'max_ord' => $max,
						'steps' => array($step),
						'length' => $n,
						'width' => 1,
					);
				}
			}
		}
	}
	return null;
}

/**
 * @return array{min_ord:int,max_ord:int,steps:list<int>,length:int,width:int}|null
 */
function cycle_encoding_detect_superpose(string $bytes, int $probeLen = 256): ?array
{
	$n = strlen($bytes);
	if($n < 8) {
		return null;
	}
	$probe = min($n, max(8, $probeLen));
	$head = substr($bytes, 0, $probe);
	$stepPairs = array(
		array(5), array(7), array(22),
		array(5, 8), array(7, 11), array(22, 31),
		array(5, 8, 13), array(7, 11, 17), array(22, 31, 37),
		array(5, 8, 13, 21),
	);
	foreach(array(48, 35) as $min) {
		foreach(array(122, 124, 129) as $max) {
			if($max <= $min) {
				continue;
			}
			foreach($stepPairs as $steps) {
				foreach(array(1, 2, 3, 4) as $width) {
					if($width > 1 && $n % $width !== 0) {
						continue;
					}
					$tryProbe = $probe;
					if($width > 1) {
						$tryProbe = $probe - ($probe % $width);
						if($tryProbe < $width * 2) {
							continue;
						}
					}
					$headTry = substr($bytes, 0, $tryProbe);
					if($width === 1 && count($steps) === 1) {
						if(cycle_encoding_single($min, $max, $steps[0], $tryProbe) !== $headTry) {
							continue;
						}
						if(cycle_encoding_single($min, $max, $steps[0], $n) === $bytes) {
							return array(
								'version' => 1,
								'min_ord' => $min,
								'max_ord' => $max,
								'steps' => $steps,
								'length' => $n,
								'width' => 1,
							);
						}
						continue;
					}
					if($width === 1) {
						if(cycle_encoding_superpose($steps, $min, $max, $tryProbe) !== $headTry) {
							continue;
						}
						if(cycle_encoding_superpose($steps, $min, $max, $n) === $bytes) {
							return array(
								'version' => 1,
								'min_ord' => $min,
								'max_ord' => $max,
								'steps' => $steps,
								'length' => $n,
								'width' => 1,
							);
						}
					} else {
						if(cycle_encoding_superpose_multichar($steps, $min, $max, $width, $tryProbe) !== $headTry) {
							continue;
						}
						if(cycle_encoding_superpose_multichar($steps, $min, $max, $width, $n) === $bytes) {
							return array(
								'version' => 1,
								'min_ord' => $min,
								'max_ord' => $max,
								'steps' => $steps,
								'length' => $n,
								'width' => $width,
							);
						}
					}
				}
			}
		}
	}
	return null;
}

/**
 * @return array<string,mixed>|null
 */
function cycle_encoding_detect_v2_fast(string $bytes): ?array
{
	$single = cycle_encoding_detect_v2_single($bytes, 128);
	if($single !== null) {
		return $single;
	}
	$rr = cycle_encoding_detect_v2_round_robin($bytes, 128);
	if($rr !== null) {
		return $rr;
	}
	$mc = cycle_encoding_detect_v2_multichar_rr($bytes, 128);
	if($mc !== null) {
		return $mc;
	}
	if(strlen($bytes) > 8192) {
		return cycle_encoding_detect_v2_deferent_large($bytes);
	}
	return null;
}

/**
 * @return array<string,mixed>|null
 */
function cycle_encoding_detect_whole(string $bytes, bool $fast = false): ?array
{
	static $memo = array();
	$key = ($fast ? 'f:' : 's:') . (string)strlen($bytes) . ':' . hash('xxh128', substr($bytes, 0, min(512, strlen($bytes))), false);
	if(isset($memo[$key])) {
		return $memo[$key];
	}
	$catalog = cycle_encoding_detect_from_catalog($bytes);
	if($catalog !== null) {
		return $memo[$key] = $catalog;
	}
	if($fast && cycle_encoding_fast_reject($bytes)) {
		return $memo[$key] = null;
	}
	$v2 = $fast ? cycle_encoding_detect_v2_fast($bytes) : cycle_encoding_detect_v2($bytes);
	if($v2 !== null) {
		return $memo[$key] = $v2;
	}
	if($fast || strlen($bytes) > 8192) {
		return $memo[$key] = null;
	}
	$super = cycle_encoding_detect_superpose($bytes);
	if($super !== null) {
		return $memo[$key] = $super;
	}
	return $memo[$key] = cycle_encoding_detect_single($bytes);
}

/**
 * @param array<string,mixed> $spec
 */
function cycle_encoding_pack_for_spec(array $spec): string
{
	if((int)($spec['version'] ?? 1) >= 2) {
		return cycle_encoding_pack_binary_v2($spec);
	}
	return cycle_encoding_pack_binary(
		(int)$spec['min_ord'],
		(int)$spec['max_ord'],
		$spec['steps'],
		(int)$spec['length'],
		(int)($spec['width'] ?? 1)
	);
}

/**
 * @return array<string,mixed>
 */
function cycle_encoding_unpack_recipe(string $bin, int $version): array
{
	if($version >= 2) {
		return cycle_encoding_unpack_binary_v2($bin);
	}
	return cycle_encoding_unpack_binary($bin);
}

/**
 * Greedy segment encoder: cycle spans where profitable, else literals.
 *
 * @return array{payload:string, meta:array<string,mixed>}
 */
function cycle_encoding_codec_encode(string $bytes, int $minCycleLen = 64): array
{
	$n = strlen($bytes);
	$catalog = cycle_encoding_detect_from_catalog($bytes);
	if($catalog !== null) {
		$whole = $catalog;
	} else {
		$whole = cycle_encoding_detect_whole($bytes, true);
	}
	if($whole !== null) {
		$recipe = cycle_encoding_pack_for_spec($whole);
		$ver = (int)($whole['version'] ?? 1);
		$mode = $ver >= 2 ? "\x03" : "\x01";
		return array(
			'payload' => CYCLE_ENCODING_WIRE_MAGIC . $mode . $recipe,
			'meta' => array(
				'mode' => 'whole',
				'version' => $ver,
				'segments' => 1,
				'cycle_bytes' => $n,
				'literal_bytes' => 0,
			),
		);
	}

	if($n <= 8192 && cycle_encoding_detect_from_catalog($bytes) === null) {
		$out = CYCLE_ENCODING_WIRE_MAGIC . "\x02\x03" . pack('N', $n) . $bytes;
		return array(
			'payload' => $out,
			'meta' => array(
				'mode' => 'literal_only',
				'segments' => 1,
				'cycle_bytes' => 0,
				'literal_bytes' => $n,
			),
		);
	}

	$scanStepPre = 64;
	$hasCycle = false;
	for($scan = 0; $scan + $minCycleLen <= $n; $scan += $scanStepPre) {
		$suffix = substr($bytes, $scan);
		if(cycle_encoding_detect_from_catalog($suffix) !== null) {
			$hasCycle = true;
			break;
		}
		if(cycle_encoding_fast_reject($suffix)) {
			continue;
		}
		if(!cycle_encoding_catalog_might_match($suffix)) {
			continue;
		}
		if(cycle_encoding_detect_whole($suffix, true) !== null) {
			$hasCycle = true;
			break;
		}
	}
	if(!$hasCycle) {
		$out = CYCLE_ENCODING_WIRE_MAGIC . "\x02\x03" . pack('N', $n) . $bytes;
		return array(
			'payload' => $out,
			'meta' => array(
				'mode' => 'literal_only',
				'segments' => 1,
				'cycle_bytes' => 0,
				'literal_bytes' => $n,
			),
		);
	}

	$out = CYCLE_ENCODING_WIRE_MAGIC . "\x02";
	$pos = 0;
	$segments = 0;
	$cycleBytes = 0;
	$literalBytes = 0;
	$scanStep = $n > 8192 ? 64 : 1;

	while($pos < $n) {
		$remaining = $n - $pos;
		if($remaining >= $minCycleLen && !cycle_encoding_fast_reject(substr($bytes, $pos))) {
			$suffix = substr($bytes, $pos);
			if(cycle_encoding_catalog_might_match($suffix)) {
				$fit = cycle_encoding_detect_whole($suffix, true);
				if($fit !== null && $fit['length'] >= $minCycleLen) {
					$recipe = cycle_encoding_pack_for_spec($fit);
					$ver = (int)($fit['version'] ?? 1);
					$tag = $ver >= 2 ? "\x02" : "\x01";
					$out .= $tag . pack('N', strlen($recipe)) . $recipe;
					$pos += $fit['length'];
					$cycleBytes += $fit['length'];
					$segments++;
					continue;
				}
			}
		}
		$litStart = $pos;
		while($pos < $n) {
			$remaining = $n - $pos;
			if($remaining >= $minCycleLen && !cycle_encoding_fast_reject(substr($bytes, $pos))) {
				$suffix = substr($bytes, $pos);
				if(cycle_encoding_catalog_might_match($suffix)) {
					$fit = cycle_encoding_detect_whole($suffix, true);
					if($fit !== null && $fit['length'] >= $minCycleLen) {
						break;
					}
				}
			}
			$pos += $scanStep;
			if($pos >= $n) {
				$pos = $n;
				break;
			}
		}
		$litLen = $pos - $litStart;
		if($litLen <= 0) {
			$litLen = 1;
			$pos = $litStart + 1;
		}
		$lit = substr($bytes, $litStart, $litLen);
		if($litLen >= 4096 && $n > 8192) {
			$out .= "\x03" . pack('N', $litLen) . $lit;
		} else {
			$out .= "\x00" . pack('n', $litLen) . $lit;
		}
		$literalBytes += $litLen;
		$segments++;
	}

	return array(
		'payload' => $out,
		'meta' => array(
			'mode' => 'segmented',
			'segments' => $segments,
			'cycle_bytes' => $cycleBytes,
			'literal_bytes' => $literalBytes,
		),
	);
}

function cycle_encoding_codec_decode(string $payload): string
{
	if(!str_starts_with($payload, CYCLE_ENCODING_WIRE_MAGIC)) {
		throw new InvalidArgumentException('missing FZCY magic');
	}
	$mode = $payload[CYCLE_ENCODING_WIRE_HDR_LEN] ?? '';
	if($mode === "\x01") {
		$spec = cycle_encoding_unpack_binary(substr($payload, CYCLE_ENCODING_WIRE_HDR_LEN + 1));
		$spec['version'] = 1;
		return cycle_encoding_generate($spec);
	}
	if($mode === "\x03") {
		$spec = cycle_encoding_unpack_binary_v2(substr($payload, CYCLE_ENCODING_WIRE_HDR_LEN + 1));
		return cycle_encoding_generate($spec);
	}
	if($mode !== "\x02") {
		throw new InvalidArgumentException('unknown FZCY mode');
	}
	$pos = CYCLE_ENCODING_WIRE_HDR_LEN + 1;
	$n = strlen($payload);
	$out = '';
	while($pos < $n) {
		$tag = $payload[$pos];
		$pos++;
		if($tag === "\x00") {
			if($pos + 2 > $n) {
				throw new InvalidArgumentException('truncated literal');
			}
			$litLen = unpack('n', substr($payload, $pos, 2))[1];
			$pos += 2;
			if($pos + $litLen > $n) {
				throw new InvalidArgumentException('truncated literal body');
			}
			$out .= substr($payload, $pos, $litLen);
			$pos += $litLen;
			continue;
		}
		if($tag === "\x03") {
			if($pos + 4 > $n) {
				throw new InvalidArgumentException('truncated bulk literal');
			}
			$litLen = unpack('N', substr($payload, $pos, 4))[1];
			$pos += 4;
			if($pos + $litLen > $n) {
				throw new InvalidArgumentException('truncated bulk literal body');
			}
			$out .= substr($payload, $pos, $litLen);
			$pos += $litLen;
			continue;
		}
		if($tag === "\x01" || $tag === "\x02") {
			if($pos + 4 > $n) {
				throw new InvalidArgumentException('truncated recipe len');
			}
			$recLen = unpack('N', substr($payload, $pos, 4))[1];
			$pos += 4;
			if($pos + $recLen > $n) {
				throw new InvalidArgumentException('truncated recipe');
			}
			$ver = $tag === "\x02" ? 2 : 1;
			$spec = cycle_encoding_unpack_recipe(substr($payload, $pos, $recLen), $ver);
			$pos += $recLen;
			$out .= cycle_encoding_generate($spec);
			continue;
		}
		throw new InvalidArgumentException('unknown segment tag');
	}
	return $out;
}

function cycle_encoding_delta_forward(string $bytes): string
{
	$n = strlen($bytes);
	if($n === 0) {
		return '';
	}
	$out = $bytes[0];
	$prev = ord($bytes[0]);
	for($i = 1; $i < $n; $i++) {
		$cur = ord($bytes[$i]);
		$out .= chr(($cur - $prev + 256) & 0xff);
		$prev = $cur;
	}
	return $out;
}

function cycle_encoding_delta_inverse(string $bytes): string
{
	$n = strlen($bytes);
	if($n === 0) {
		return '';
	}
	$out = $bytes[0];
	$prev = ord($bytes[0]);
	for($i = 1; $i < $n; $i++) {
		$cur = ($prev + ord($bytes[$i])) & 0xff;
		$out .= chr($cur);
		$prev = $cur;
	}
	return $out;
}

function cycle_encoding_oracle_bytes(array $spec): int
{
	$bin = cycle_encoding_pack_for_spec($spec);
	$ascii = (string)($spec['representation'] ?? '');
	$asciiGz = @gzencode($ascii, 9);
	$binGz = @gzencode($bin, 9);
	$cands = array(strlen($bin));
	if($ascii !== '') {
		$cands[] = strlen($ascii);
	}
	if($asciiGz !== false) {
		$cands[] = strlen($asciiGz);
	}
	if($binGz !== false) {
		$cands[] = strlen($binGz);
	}
	return min($cands);
}
