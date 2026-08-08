<?php
declare(strict_types=1);

/**
 * Deterministic v2 epicycle recipes for test_files180 through test_files195.
 *
 * Generator/verification metadata only — must not be used as compression hints.
 * Representation uses <e…> (epicycle) syntax, distinct from fractal_zip <g…> gradients.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'cycle_encoding.php';

/**
 * @return array<int,array<string,mixed>>
 */
function cycle_encoding_recipe_specs(): array
{
	return array(
		180 => array(
			'dir' => 'test_files180',
			'file' => 'a.txt',
			'note' => '1-circle direct walk (100 B)',
			'tier' => '1-circle',
			'version' => 2,
			'combine' => 'single',
			'byte_map' => 'direct',
			'walkers' => array(
				array('min_ord' => 48, 'max_ord' => 122, 'step' => 5, 'phase' => 48),
			),
			'length' => 100,
			'width' => 1,
			'representation' => '<e"single,direct;w:48:122:5:48"100>',
		),
		181 => array(
			'dir' => 'test_files181',
			'file' => 'b.txt',
			'note' => '1-circle torus-mapped walk (10 KiB)',
			'tier' => '1-circle',
			'version' => 2,
			'combine' => 'single',
			'byte_map' => 'torus',
			'walkers' => array(
				array('min_ord' => 48, 'max_ord' => 124, 'step' => 7, 'phase' => 48),
			),
			'length' => 10000,
			'width' => 1,
			'representation' => '<e"single,torus;w:48:124:7:48"10000>',
		),
		182 => array(
			'dir' => 'test_files182',
			'file' => 'c.txt',
			'note' => '3-epicycle complex torus (1 MiB)',
			'tier' => 'complex-torus',
			'version' => 2,
			'combine' => 'complex',
			'byte_map' => 'torus',
			'walkers' => array(
				array('min_ord' => 0, 'max_ord' => 255, 'step' => 5, 'phase' => 11, 'amplitude' => 1.0),
				array('min_ord' => 0, 'max_ord' => 255, 'step' => 8, 'phase' => 23, 'amplitude' => 0.85),
				array('min_ord' => 0, 'max_ord' => 255, 'step' => 13, 'phase' => 37, 'amplitude' => 0.7),
			),
			'length' => 1000000,
			'width' => 1,
			'representation' => '<e"complex,torus;w:0:255:5:11:1.0;w:0:255:8:23:0.85;w:0:255:13:37:0.7"1000000>',
		),
		183 => array(
			'dir' => 'test_files183',
			'file' => 'a.txt',
			'note' => '2-walker round-robin incommensurate (100 B)',
			'tier' => '2-walker',
			'version' => 2,
			'combine' => 'round_robin',
			'byte_map' => 'direct',
			'walkers' => array(
				array('min_ord' => 48, 'max_ord' => 122, 'step' => 5, 'phase' => 48),
				array('min_ord' => 48, 'max_ord' => 123, 'step' => 8, 'phase' => 50),
			),
			'length' => 100,
			'width' => 1,
			'representation' => '<e"round_robin,direct;w:48:122:5:48;w:48:123:8:50"100>',
		),
		184 => array(
			'dir' => 'test_files184',
			'file' => 'b.txt',
			'note' => '2-walker round-robin incommensurate (10 KiB)',
			'tier' => '2-walker',
			'version' => 2,
			'combine' => 'round_robin',
			'byte_map' => 'direct',
			'walkers' => array(
				array('min_ord' => 48, 'max_ord' => 124, 'step' => 7, 'phase' => 48),
				array('min_ord' => 49, 'max_ord' => 126, 'step' => 11, 'phase' => 52),
			),
			'length' => 10000,
			'width' => 1,
			'representation' => '<e"round_robin,direct;w:48:124:7:48;w:49:126:11:52"10000>',
		),
		185 => array(
			'dir' => 'test_files185',
			'file' => 'c.txt',
			'note' => '2-walker round-robin incommensurate (1 MiB)',
			'tier' => '2-walker',
			'version' => 2,
			'combine' => 'round_robin',
			'byte_map' => 'direct',
			'walkers' => array(
				array('min_ord' => 35, 'max_ord' => 129, 'step' => 22, 'phase' => 35),
				array('min_ord' => 36, 'max_ord' => 131, 'step' => 31, 'phase' => 40),
			),
			'length' => 1000000,
			'width' => 1,
			'representation' => '<e"round_robin,direct;w:35:129:22:35;w:36:131:31:40"1000000>',
		),
		186 => array(
			'dir' => 'test_files186',
			'file' => 'a.txt',
			'note' => '3-walker round-robin incommensurate (100 B)',
			'tier' => '3-walker',
			'version' => 2,
			'combine' => 'round_robin',
			'byte_map' => 'direct',
			'walkers' => array(
				array('min_ord' => 48, 'max_ord' => 122, 'step' => 5, 'phase' => 48),
				array('min_ord' => 48, 'max_ord' => 123, 'step' => 8, 'phase' => 50),
				array('min_ord' => 49, 'max_ord' => 125, 'step' => 13, 'phase' => 53),
			),
			'length' => 100,
			'width' => 1,
			'representation' => '<e"round_robin,direct;w:48:122:5:48;w:48:123:8:50;w:49:125:13:53"100>',
		),
		187 => array(
			'dir' => 'test_files187',
			'file' => 'b.txt',
			'note' => '3-walker round-robin incommensurate (10 KiB)',
			'tier' => '3-walker',
			'version' => 2,
			'combine' => 'round_robin',
			'byte_map' => 'direct',
			'walkers' => array(
				array('min_ord' => 48, 'max_ord' => 124, 'step' => 7, 'phase' => 48),
				array('min_ord' => 49, 'max_ord' => 126, 'step' => 11, 'phase' => 52),
				array('min_ord' => 50, 'max_ord' => 128, 'step' => 17, 'phase' => 55),
			),
			'length' => 10000,
			'width' => 1,
			'representation' => '<e"round_robin,direct;w:48:124:7:48;w:49:126:11:52;w:50:128:17:55"10000>',
		),
		188 => array(
			'dir' => 'test_files188',
			'file' => 'c.txt',
			'note' => '3-walker round-robin incommensurate (1 MiB)',
			'tier' => '3-walker',
			'version' => 2,
			'combine' => 'round_robin',
			'byte_map' => 'direct',
			'walkers' => array(
				array('min_ord' => 35, 'max_ord' => 129, 'step' => 22, 'phase' => 35),
				array('min_ord' => 36, 'max_ord' => 131, 'step' => 31, 'phase' => 40),
				array('min_ord' => 37, 'max_ord' => 133, 'step' => 37, 'phase' => 44),
			),
			'length' => 1000000,
			'width' => 1,
			'representation' => '<e"round_robin,direct;w:35:129:22:35;w:36:131:31:40;w:37:133:37:44"1000000>',
		),
		189 => array(
			'dir' => 'test_files189',
			'file' => 'a.txt',
			'note' => '2-walker RR multichar w=2 (100 B)',
			'tier' => 'multichar-rr',
			'version' => 2,
			'combine' => 'round_robin',
			'byte_map' => 'direct',
			'walkers' => array(
				array('min_ord' => 48, 'max_ord' => 122, 'step' => 5, 'phase' => 48),
				array('min_ord' => 48, 'max_ord' => 123, 'step' => 8, 'phase' => 50),
			),
			'length' => 100,
			'width' => 2,
			'representation' => '<e"round_robin,direct;w:48:122:5:48;w:48:123:8:50"100"w2>',
		),
		190 => array(
			'dir' => 'test_files190',
			'file' => 'b.txt',
			'note' => '3-walker RR multichar w=2 (10 KiB)',
			'tier' => 'multichar-rr',
			'version' => 2,
			'combine' => 'round_robin',
			'byte_map' => 'direct',
			'walkers' => array(
				array('min_ord' => 48, 'max_ord' => 124, 'step' => 7, 'phase' => 48),
				array('min_ord' => 49, 'max_ord' => 126, 'step' => 11, 'phase' => 52),
				array('min_ord' => 50, 'max_ord' => 128, 'step' => 17, 'phase' => 55),
			),
			'length' => 10000,
			'width' => 2,
			'representation' => '<e"round_robin,direct;w:48:124:7:48;w:49:126:11:52;w:50:128:17:55"10000"w2>',
		),
		191 => array(
			'dir' => 'test_files191',
			'file' => 'c.txt',
			'note' => 'deferent + 2-walker RR (1 MiB)',
			'tier' => 'deferent',
			'version' => 2,
			'combine' => 'deferent_rr',
			'byte_map' => 'direct',
			'walkers' => array(
				array('min_ord' => 35, 'max_ord' => 129, 'step' => 22, 'phase' => 35),
				array('min_ord' => 36, 'max_ord' => 131, 'step' => 31, 'phase' => 40),
			),
			'deferent' => array('min_ord' => 40, 'max_ord' => 120, 'step' => 9, 'phase' => 40),
			'length' => 1000000,
			'width' => 1,
			'representation' => '<e"deferent_rr,direct;d:40:120:9:40;w:35:129:22:35;w:36:131:31:40"1000000>',
		),
		192 => array(
			'dir' => 'test_files192',
			'file' => 'd.txt',
			'note' => 'deferent + 3-walker RR multichar w=3 (9999 B)',
			'tier' => 'deferent',
			'version' => 2,
			'combine' => 'deferent_rr',
			'byte_map' => 'direct',
			'walkers' => array(
				array('min_ord' => 48, 'max_ord' => 124, 'step' => 5, 'phase' => 48),
				array('min_ord' => 49, 'max_ord' => 126, 'step' => 8, 'phase' => 52),
				array('min_ord' => 50, 'max_ord' => 128, 'step' => 13, 'phase' => 55),
			),
			'deferent' => array('min_ord' => 45, 'max_ord' => 122, 'step' => 6, 'phase' => 45),
			'length' => 9999,
			'width' => 3,
			'representation' => '<e"deferent_rr,direct;d:45:122:6:45;w:48:124:5:48;w:49:126:8:52;w:50:128:13:55"9999"w3>',
		),
		193 => array(
			'dir' => 'test_files193',
			'file' => 'e.txt',
			'note' => '4-epicycle complex torus map (100 B)',
			'tier' => 'complex-torus',
			'version' => 2,
			'combine' => 'complex',
			'byte_map' => 'torus',
			'walkers' => array(
				array('min_ord' => 0, 'max_ord' => 255, 'step' => 5, 'phase' => 11, 'amplitude' => 1.0),
				array('min_ord' => 0, 'max_ord' => 255, 'step' => 8, 'phase' => 23, 'amplitude' => 0.85),
				array('min_ord' => 0, 'max_ord' => 255, 'step' => 13, 'phase' => 37, 'amplitude' => 0.7),
				array('min_ord' => 0, 'max_ord' => 255, 'step' => 21, 'phase' => 51, 'amplitude' => 0.55),
			),
			'length' => 100,
			'width' => 1,
			'representation' => '<e"complex,torus;w:0:255:5:11:1.0;w:0:255:8:23:0.85;w:0:255:13:37:0.7;w:0:255:21:51:0.55"100>',
		),
		194 => array(
			'dir' => 'test_files194',
			'file' => 'a.txt',
			'note' => '1-circle torus-mapped walk (1 MiB)',
			'tier' => '1-circle',
			'version' => 2,
			'combine' => 'single',
			'byte_map' => 'torus',
			'walkers' => array(
				array('min_ord' => 48, 'max_ord' => 124, 'step' => 7, 'phase' => 48),
			),
			'length' => 1000000,
			'width' => 1,
			'representation' => '<e"single,torus;w:48:124:7:48"1000000>',
		),
		195 => array(
			'dir' => 'test_files195',
			'file' => 'a.txt',
			'note' => '2-walker RR experiment grid (50 KiB)',
			'tier' => '2-walker',
			'version' => 2,
			'combine' => 'round_robin',
			'byte_map' => 'direct',
			'walkers' => array(
				array('min_ord' => 35, 'max_ord' => 129, 'step' => 22, 'phase' => 35),
				array('min_ord' => 36, 'max_ord' => 131, 'step' => 31, 'phase' => 40),
			),
			'length' => 50000,
			'width' => 1,
			'representation' => '<e"round_robin,direct;w:35:129:22:35;w:36:131:31:40"50000>',
		),
	);
}

/**
 * @param array<string,mixed> $spec
 * @return array<string,mixed>
 */
function cycle_encoding_expand_spec(array $spec): array
{
	$bytes = cycle_encoding_generate($spec);
	$expectedSize = (int)$spec['length'];
	if(strlen($bytes) !== $expectedSize) {
		throw new RuntimeException(
			'length mismatch for ' . (string)($spec['representation'] ?? '?')
			. ': got ' . (string)strlen($bytes) . ' expected ' . (string)$expectedSize
		);
	}
	$expanded = $spec;
	$expanded['expected_size'] = $expectedSize;
	$expanded['expected_xxh128'] = hash('xxh128', $bytes, false);
	$expanded['bytes'] = $bytes;
	return $expanded;
}

/**
 * @return array<int,array<string,mixed>>
 */
function cycle_encoding_expanded_specs(): array
{
	$out = array();
	foreach(cycle_encoding_recipe_specs() as $case => $spec) {
		$out[(int)$case] = cycle_encoding_expand_spec($spec);
	}
	return $out;
}
