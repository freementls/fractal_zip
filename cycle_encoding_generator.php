#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Ad-hoc CLI dump for cycle-encoding recipes.
 *
 * Usage from repo root:
 *   php cycle_encoding_generator.php '<e"round_robin,direct;w:48:122:5:48;w:48:123:8:50"100>'
 *   php cycle_encoding_generator.php   # defaults to recipe 183
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'cycle_encoding.php';

$representation = $argv[1] ?? '<e"round_robin,direct;w:48:122:5:48;w:48:123:8:50"100>';
if(str_starts_with($representation, '<e')) {
	$parsed = cycle_encoding_parse_e_recipe($representation);
} else {
	$parsed = cycle_encoding_parse_recipe($representation);
}
$bytes = cycle_encoding_generate($parsed);

fwrite(STDOUT, $bytes);
