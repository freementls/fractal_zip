<?php
declare(strict_types=1);

/**
 * Deterministic recipes for test_files140-150.
 *
 * These recipes are generator/verification metadata only. They must not be loaded by
 * compression search code, passed as verified hints, or used to choose fz candidates.
 */

/**
 * @return array<string,mixed>
 */
function fz_inner_fixture_runpair_recipe(string $x, string $y, string $name): array
{
	return array(
		'kind' => 'fractal',
		'name' => $name,
		'equiv' => str_repeat($x, 5) . '<20"25"30>' . str_repeat($x, 8),
		'fractal' => str_repeat($x, 5) . '<20"25>' . str_repeat($x, 8)
			. str_repeat($y, 6) . '<0"20>' . $y . '<0"20>' . str_repeat($y, 6),
	);
}

/**
 * @return array<string,mixed>
 */
function fz_inner_fixture_recipe_fractal(string $name, string $equiv, string $fractal): array
{
	return array(
		'kind' => 'fractal',
		'name' => $name,
		'equiv' => $equiv,
		'fractal' => $fractal,
	);
}

/**
 * @return array<string,mixed>
 */
function fz_inner_fixture_recipe_literal(string $name, string $bytes, string $recipe): array
{
	return array(
		'kind' => 'literal',
		'name' => $name,
		'bytes' => $bytes,
		'recipe' => $recipe,
	);
}

/**
 * @param list<array<string,mixed>> $parts
 * @return array<string,mixed>
 */
function fz_inner_fixture_recipe_composite(string $name, array $parts): array
{
	return array(
		'kind' => 'composite',
		'name' => $name,
		'parts' => $parts,
	);
}

function fz_inner_fixture_binary_grid(int $w, int $h, int $stride, int $seed): string
{
	$out = '';
	for($y = 0; $y < $h; $y++) {
		for($x = 0; $x < $w; $x++) {
			$out .= chr(($seed + $x * 3 + $y * 5) & 255);
			$out .= chr(($seed + $x * 7 + $y * 11) & 255);
			$out .= chr(($seed + $x * 13 + $y * 17) & 255);
			if($stride === 4) {
				$out .= chr(255 - (($x + $y + $seed) & 31));
			}
		}
	}
	return $out;
}

function fz_inner_fixture_fake_zip_member(string $name, string $payload): string
{
	$crc = crc32($payload);
	if($crc < 0) {
		$crc += 4294967296;
	}
	$n = strlen($name);
	$l = strlen($payload);
	return "PK\x03\x04" . pack('vvvvvVVVvv', 20, 0, 0, 0, 0, $crc, $l, $l, $n, 0)
		. $name . $payload . "PK\x05\x06" . str_repeat("\x00", 18);
}

function fz_inner_fixture_zlib_member(string $label, string $payload): string
{
	$deflated = gzdeflate($payload, 9);
	if($deflated === false) {
		$deflated = $payload;
	}
	return "FZFAKE-ZLIB:" . $label . "\n" . $deflated . "\nEND-FZFAKE-ZLIB\n";
}

/**
 * @return array<int,array{dir:string,note:string,operations:list<string>,files:list<array<string,mixed>>}>
 */
function fz_inner_fixture_recipe_specs_140_150(): array
{
	$case31Equiv = 'ss<59"39"9>sss';
	$case31Fractal = 'aaa<14"17>aaaabbbbb<0"14>bbbbbbcc<0"14>ccc<14"17>ccccccccccddd<0"14>ddddddd<31"28>dddddd<14"17>ddd';
	$case32Equiv = 'ss<66"39"16>sss';
	$case32Fractal = 'aaa<14"17>aaaabbbbb<0"14>bbbbbbcc<0"14>ccc<14"17>ccc<66"39>cccccccddd<0"14>ddddddd<31"35>dddddd<14"17>ddd';

	$xmlish = "<record id=\"140\"><value>&lt;marker-looking&gt;</value><raw>&amp;</raw></record>\n";
	$binaryNeedles = "literal marker-looking bytes: <0\"12> & <gA\"1\"Z> & </r>\n" . "\x00\x01\x02\x03\x7f\x80\xfe\xff";
	$gradientEquiv = "gradient:<gA\"2\"M*2>\nscale-half:<0\"16s0.5>\nscale-double:<0\"8s2>\n";
	$gradientFractal = "abcdefghijklmnop";
	$replaceEquiv = '<rfoo"BAR>foo-<0"6*2>-foo-<g0"3"9></r>';
	$replaceFractal = 'foobar';
	$skipEquiv = '<l9><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35>';
	$skipFractal = '<s1>abc<s1>def<s1>ghi<s1>jkl<s1>mno<s1>pqr<s1>stu<s1>vwx<s1>yz!';
	$runpairBinary = fz_inner_fixture_runpair_recipe("\x80", "\xff", 'binary-recursive-runpair');
	$runpairAscii = fz_inner_fixture_runpair_recipe('a', 'b', 'ascii-recursive-runpair');
	$ladder31 = fz_inner_fixture_recipe_fractal('multirun-ladder-31', $case31Equiv, $case31Fractal);
	$ladder32 = fz_inner_fixture_recipe_fractal('multirun-ladder-32', $case32Equiv, $case32Fractal);
	$imageLike = fz_inner_fixture_binary_grid(32, 24, 4, 17);
	$fakeZipPayload = "member-header\n" . str_repeat("alpha-beta-gamma\n", 24) . fz_inner_fixture_binary_grid(8, 8, 3, 33);
	$fakeZip = fz_inner_fixture_fake_zip_member('inner/fractal.txt', $fakeZipPayload);
	$fakeZlib = fz_inner_fixture_zlib_member('gradient-and-runpair', $fakeZipPayload . $xmlish);

	return array(
		140 => array(
			'dir' => 'test_files140',
			'note' => 'basic substring, tuple, recursion, and binary sentinels',
			'operations' => array('substring', 'tuple', 'recursion', 'binary-literal'),
			'files' => array(
				array(
					'path' => 'basic/substring_tuple_recursion.bin',
					'recipe' => fz_inner_fixture_recipe_composite('basic-substring-tuple-recursion', array(
						fz_inner_fixture_recipe_literal('sentinel-prefix', "TF140\x00\x10\x20\n", 'literal sentinel prefix'),
						fz_inner_fixture_recipe_fractal('tuple-scale-substrings', 'A<0"6*3>Z<3"8s2>', 'abcdefXYZ123'),
						fz_inner_fixture_recipe_literal('sentinel-suffix', "\n\xfe\xfdTF140-END\n", 'literal sentinel suffix'),
					)),
				),
			),
		),
		141 => array(
			'dir' => 'test_files141',
			'note' => 'escaped marker-looking literals mixed with true markers',
			'operations' => array('escaped-literal', 'substring', 'recursion', 'binary-literal'),
			'files' => array(
				array(
					'path' => 'escaped/marker_lookalikes.bin',
					'recipe' => fz_inner_fixture_recipe_composite('escaped-marker-lookalikes', array(
						fz_inner_fixture_recipe_literal('marker-looking-literals', $binaryNeedles . "\n" . $xmlish, 'literal bytes that look like markers/operators'),
						fz_inner_fixture_recipe_fractal('small-recursive-core', 'core:<0"9"3>:tail', 'xy<0"5>zpq'),
					)),
				),
			),
		),
		142 => array(
			'dir' => 'test_files142',
			'note' => 'gradient plus scale and tuple forms',
			'operations' => array('gradient', 'tuple', 'scale', 'substring'),
			'files' => array(
				array(
					'path' => 'ops/gradient_scale.txt',
					'recipe' => fz_inner_fixture_recipe_fractal('gradient-scale-mix', $gradientEquiv, $gradientFractal),
				),
			),
		),
		143 => array(
			'dir' => 'test_files143',
			'note' => 'replace operations layered over generated spans',
			'operations' => array('replace', 'substring', 'tuple', 'gradient'),
			'files' => array(
				array(
					'path' => 'ops/replace_layer.txt',
					'recipe' => fz_inner_fixture_recipe_fractal('replace-layer', $replaceEquiv, $replaceFractal),
				),
			),
		),
		144 => array(
			'dir' => 'test_files144',
			'note' => 'row-length and skip tiling with recursive sidecar',
			'operations' => array('row-length', 'skip', 'substring', 'recursion'),
			'files' => array(
				array(
					'path' => 'grid/skip_tile.txt',
					'recipe' => fz_inner_fixture_recipe_fractal('skip-grid', $skipEquiv, $skipFractal),
				),
				array(
					'path' => 'grid/recursive_sidecar.txt',
					'recipe' => $runpairAscii,
				),
			),
		),
		145 => array(
			'dir' => 'test_files145',
			'note' => 'recursive run-pair over high-byte symbols',
			'operations' => array('recursive-runpair', 'binary-byte-runs', 'recursion'),
			'files' => array(
				array(
					'path' => 'runs/high_byte_runpair.bin',
					'recipe' => $runpairBinary,
				),
			),
		),
		146 => array(
			'dir' => 'test_files146',
			'note' => 'multirun ladder grammar with prefix and suffix noise',
			'operations' => array('multirun-ladder', 'substring', 'recursion', 'literal-noise'),
			'files' => array(
				array(
					'path' => 'ladders/multirun_with_noise.txt',
					'recipe' => fz_inner_fixture_recipe_composite('multirun-noise', array(
						fz_inner_fixture_recipe_literal('prefix-noise', "BEGIN-146\n", 'literal prefix noise'),
						$ladder31,
						fz_inner_fixture_recipe_literal('suffix-noise', "\nEND-146\n", 'literal suffix noise'),
					)),
				),
			),
		),
		147 => array(
			'dir' => 'test_files147',
			'note' => 'mixed tree where each member stresses a different operation family',
			'operations' => array('folder-mix', 'gradient', 'replace', 'runpair', 'escaped-literal'),
			'files' => array(
				array('path' => 'members/gradient.txt', 'recipe' => fz_inner_fixture_recipe_fractal('gradient-member', $gradientEquiv, $gradientFractal)),
				array('path' => 'members/replace.txt', 'recipe' => fz_inner_fixture_recipe_fractal('replace-member', $replaceEquiv, $replaceFractal)),
				array('path' => 'members/runpair.bin', 'recipe' => $runpairBinary),
				array('path' => 'members/literals.bin', 'recipe' => fz_inner_fixture_recipe_literal('literal-member', $binaryNeedles . str_repeat("\nnear-match:<0\"12>\n", 6), 'literal marker-looking near matches')),
			),
		),
		148 => array(
			'dir' => 'test_files148',
			'note' => 'binary image-like data plus fractal overlay metadata',
			'operations' => array('binary-grid', 'literal-transform-stress', 'substring-overlay'),
			'files' => array(
				array('path' => 'binary/grid.rgba', 'recipe' => fz_inner_fixture_recipe_literal('rgba-grid', $imageLike, 'deterministic 32x24 RGBA-like grid')),
				array('path' => 'binary/grid_overlay.txt', 'recipe' => fz_inner_fixture_recipe_fractal('grid-overlay', 'tile:<0"16*8>|<8"16s2>', 'rgba-tile-000000')),
			),
		),
		149 => array(
			'dir' => 'test_files149',
			'note' => 'container-like bytes with embedded fractal-shaped payloads',
			'operations' => array('container-like', 'deflate-like', 'zip-like', 'fractal-payload'),
			'files' => array(
				array('path' => 'containers/fake_member.zip', 'recipe' => fz_inner_fixture_recipe_literal('fake-zip', $fakeZip, 'deterministic PK-shaped single-member bytes')),
				array('path' => 'containers/fake_stream.zlibish', 'recipe' => fz_inner_fixture_recipe_literal('fake-zlib', $fakeZlib, 'deterministic deflate payload with explicit framing')),
				array('path' => 'containers/fractal_payload.txt', 'recipe' => $ladder31),
			),
		),
		150 => array(
			'dir' => 'test_files150',
			'note' => 'hard mixed corpus with true markers, binary literals, containers, and misleading near-matches',
			'operations' => array('hard-mix', 'runpair', 'multirun-ladder', 'gradient', 'replace', 'binary', 'container-like'),
			'files' => array(
				array('path' => 'hard/runpair_high.bin', 'recipe' => $runpairBinary),
				array('path' => 'hard/multirun.txt', 'recipe' => $ladder32),
				array('path' => 'hard/gradient_replace.txt', 'recipe' => fz_inner_fixture_recipe_composite('gradient-replace-combo', array(
					fz_inner_fixture_recipe_fractal('gradient-part', $gradientEquiv, $gradientFractal),
					fz_inner_fixture_recipe_literal('separator', "\n--replace--\n", 'literal separator'),
					fz_inner_fixture_recipe_fractal('replace-part', $replaceEquiv, $replaceFractal),
				))),
				array('path' => 'hard/binary_grid.bin', 'recipe' => fz_inner_fixture_recipe_literal('hard-grid', fz_inner_fixture_binary_grid(24, 24, 4, 91), 'deterministic high-entropy-looking grid')),
				array('path' => 'hard/near_miss_markers.txt', 'recipe' => fz_inner_fixture_recipe_literal('near-miss-markers', str_repeat("not-a-hint <20\"25\"30> <rfoo\"bar>baz</r>\n", 12), 'literal misleading marker-looking text')),
				array('path' => 'hard/container_like.bin', 'recipe' => fz_inner_fixture_recipe_literal('hard-container', $fakeZip . $fakeZlib, 'concatenated deterministic container-like members')),
			),
		),
	);
}

function fz_inner_fixture_expand_recipe(fractal_zip $fz, array $recipe): string
{
	$kind = (string)($recipe['kind'] ?? '');
	if($kind === 'fractal') {
		return $fz->fractally_process_string((string)$recipe['equiv'], (string)$recipe['fractal']);
	}
	if($kind === 'literal') {
		return (string)$recipe['bytes'];
	}
	if($kind === 'composite') {
		$out = '';
		foreach(($recipe['parts'] ?? array()) as $part) {
			if(is_array($part)) {
				$out .= fz_inner_fixture_expand_recipe($fz, $part);
			}
		}
		return $out;
	}
	throw new InvalidArgumentException('unknown recipe kind: ' . $kind);
}

/**
 * @return array<int,array<string,mixed>>
 */
function fz_inner_fixture_expanded_specs_140_150(fractal_zip $fz): array
{
	$out = array();
	foreach(fz_inner_fixture_recipe_specs_140_150() as $case => $spec) {
		$files = array();
		foreach($spec['files'] as $file) {
			$bytes = fz_inner_fixture_expand_recipe($fz, $file['recipe']);
			$file['expected_size'] = strlen($bytes);
			$file['expected_xxh128'] = hash('xxh128', $bytes, false);
			$files[] = $file;
		}
		$spec['files'] = $files;
		$out[$case] = $spec;
	}
	return $out;
}
