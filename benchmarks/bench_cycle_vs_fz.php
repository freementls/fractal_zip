#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Cycle encoding vs fractal_zip (.fz) on cycle corpora and facet reference cases.
 *
 * Compares payload-level cycle oracle / cycle_inner LTCB against zip_folder output
 * (multifractal, multidiff, BMP transforms, inner markers — whatever fz selects).
 *
 * Usage:
 *   nice -n 19 php benchmarks/bench_cycle_vs_fz.php [--json]
 */

putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0');

$root = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_recipes.php';
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_codec.php';
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_cycle_preprocess.php';

$jsonOut = in_array('--json', $argv, true);
$caseFilter = array();
foreach($argv as $arg) {
	if(str_starts_with($arg, '--cases=')) {
		foreach(explode(',', substr($arg, 8)) as $c) {
			$c = trim($c);
			if($c !== '') {
				$caseFilter[] = $c;
			}
		}
	}
}

$gz = static function (string $s): int {
	$z = @gzdeflate($s, 9);
	return $z === false ? strlen($s) : strlen($z);
};

/**
 * @return array{fzc:int,payload_fzc:int,outer:?string,seconds:float,equiv_count:int,segment:int}
 */
function cycle_vs_fz_encode_dir(string $dir, int $seg = 300): array
{
	$fzcPath = rtrim($dir, DIRECTORY_SEPARATOR) . '.fz';
	@unlink($fzcPath);
	$fz = new fractal_zip($seg, false, false, null, false);
	$t0 = microtime(true);
	$fz->zip_folder($dir, false);
	$sec = microtime(true) - $t0;
	$equivCount = is_array($fz->equivalences ?? null) ? count($fz->equivalences) : 0;
	return array(
		'fzc' => is_file($fzcPath) ? (int)filesize($fzcPath) : 0,
		'payload_fzc' => 0,
		'outer' => isset($fz->last_outer_codec_choice) ? (string)$fz->last_outer_codec_choice : null,
		'seconds' => $sec,
		'equiv_count' => $equivCount,
		'segment' => $seg,
	);
}

/**
 * @return array{fzc:int,seconds:float,equiv_count:int}
 */
function cycle_vs_fz_encode_payload_only(string $payload, string $basename, int $seg = 300): array
{
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_cyc_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$member = $tmp . DIRECTORY_SEPARATOR . $basename;
	file_put_contents($member, $payload);
	$fzcPath = $tmp . '.fz';
	@unlink($fzcPath);
	$fz = new fractal_zip($seg, false, false, null, false);
	$t0 = microtime(true);
	$fz->zip_folder($tmp, false);
	$sec = microtime(true) - $t0;
	$out = array(
		'fzc' => is_file($fzcPath) ? (int)filesize($fzcPath) : 0,
		'seconds' => $sec,
		'equiv_count' => is_array($fz->equivalences ?? null) ? count($fz->equivalences) : 0,
	);
	@unlink($fzcPath);
	@unlink($member);
	@rmdir($tmp);
	return $out;
}

/**
 * @return list<array<string,mixed>>
 */
function cycle_vs_fz_corpus_list(string $root): array
{
	$rows = array();
	foreach(cycle_encoding_recipe_specs() as $case => $spec) {
		$rows[] = array(
			'label' => (string)$spec['dir'],
			'facet' => 'cycle_' . (string)$spec['tier'],
			'dir' => $root . DIRECTORY_SEPARATOR . (string)$spec['dir'],
			'payload_rel' => (string)$spec['file'],
			'recipe' => $spec,
		);
	}
	$refs = array(
		array('test_files31', 'multifractal', 'multifractal.txt'),
		array('test_files35', 'BMP fractal', null),
		array('test_files64', 'BMP grid', 'grid_01.bmp'),
		array('test_files90', 'multidiff', 'similar01.txt'),
		array('test_files140', 'fz inner', 'basic_substr.txt'),
		array('test_files110', 'C source', 'fields.c'),
		array('test_files115', 'Calgary text', 'lcet10.txt'),
	);
	foreach($refs as $ref) {
		$dir = $root . DIRECTORY_SEPARATOR . $ref[0];
		if(!is_dir($dir)) {
			continue;
		}
		$rows[] = array(
			'label' => $ref[0],
			'facet' => $ref[1],
			'dir' => $dir,
			'payload_rel' => $ref[2],
			'recipe' => null,
		);
	}
	$hybridDir = $root . DIRECTORY_SEPARATOR . 'test_files196';
	if(is_file($hybridDir . DIRECTORY_SEPARATOR . 'hybrid.txt')) {
		$rows[] = array(
			'label' => 'test_files196',
			'facet' => 'hybrid-natural+cycle',
			'dir' => $hybridDir,
			'payload_rel' => 'hybrid.txt',
			'recipe' => null,
		);
	}
	$hybrid197Dir = $root . DIRECTORY_SEPARATOR . 'test_files197';
	if(is_file($hybrid197Dir . DIRECTORY_SEPARATOR . 'hybrid.txt')) {
		$rows[] = array(
			'label' => 'test_files197',
			'facet' => 'hybrid-1MiB-torus',
			'dir' => $hybrid197Dir,
			'payload_rel' => 'hybrid.txt',
			'recipe' => null,
		);
	}
	return $rows;
}

/**
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function cycle_vs_fz_probe(array $row): array
{
	global $gz;
	$dir = (string)$row['dir'];
	$payloadRel = $row['payload_rel'];
	$payload = '';
	$payloadBytes = 0;
	if(is_string($payloadRel) && $payloadRel !== '') {
		$p = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $payloadRel);
		if(is_file($p)) {
			$payload = (string)file_get_contents($p);
			$payloadBytes = strlen($payload);
		}
	}
	if($payloadBytes === 0) {
		$col = collectFolderFiles($dir);
		$payloadBytes = (int)$col['total'];
	}

	$recipe = $row['recipe'];
	$cycleOracle = null;
	$cycleInner = null;
	$cycleInnerRt = null;
	if(is_array($recipe)) {
		$expanded = cycle_encoding_expand_spec($recipe);
		$payload = (string)$expanded['bytes'];
		$payloadBytes = strlen($payload);
		$cycleOracle = cycle_encoding_oracle_bytes($expanded);
	} elseif($payloadBytes > 0) {
		$det = cycle_encoding_detect_whole($payload, false);
		if($det !== null) {
			$cycleOracle = cycle_encoding_oracle_bytes($det);
		}
	}
	if($payloadBytes > 0) {
		$pre = fractal_zip_text_cycle_preprocess($payload);
		$cycleInnerRt = fractal_zip_text_cycle_undo((string)$pre['payload'], $pre['sidecar']) === $payload;
		$cycleInner = $gz((string)$pre['payload']) + strlen((string)($pre['sidecar']['binary'] ?? json_encode($pre['sidecar'])));
	}

	$folderRaw = collectFolderFiles($dir);
	$fzFolder = cycle_vs_fz_encode_dir($dir);
	$fzPayload = array('fzc' => 0, 'seconds' => 0.0, 'equiv_count' => 0);
	if($payloadBytes > 0 && is_string($payloadRel) && $payloadRel !== '') {
		$fzPayload = cycle_vs_fz_encode_payload_only($payload, basename($payloadRel));
	}

	$payloadGz9 = $payloadBytes > 0 ? $gz($payload) : null;
	$bestFz = min(array_filter(array(
		$fzFolder['fzc'] > 0 ? $fzFolder['fzc'] : null,
		$fzPayload['fzc'] > 0 ? $fzPayload['fzc'] : null,
	), static fn($v) => $v !== null));

	$bestCycle = min(array_filter(array(
		$cycleOracle,
		$cycleInner,
	), static fn($v) => $v !== null));
	$winner = null;
	if($bestCycle !== null && $bestFz !== null) {
		if($bestCycle < $bestFz) {
			$winner = 'cycle';
		} elseif($bestFz < $bestCycle) {
			$winner = 'fz';
		} else {
			$winner = 'tie';
		}
	}

	return array(
		'label' => (string)$row['label'],
		'facet' => (string)$row['facet'],
		'payload_bytes' => $payloadBytes,
		'folder_raw_bytes' => (int)$folderRaw['total'],
		'payload_gz9' => $payloadGz9,
		'cycle_oracle' => $cycleOracle,
		'cycle_inner_ltcb' => $cycleInner,
		'cycle_best' => $bestCycle,
		'cycle_inner_rt' => $cycleInnerRt,
		'fzc_folder' => $fzFolder['fzc'],
		'fzc_payload_only' => $fzPayload['fzc'],
		'fzc_best' => $bestFz,
		'fzc_outer' => $fzFolder['outer'],
		'fzc_equiv_count' => $fzFolder['equiv_count'],
		'fzc_payload_equiv' => $fzPayload['equiv_count'],
		'fzc_folder_sec' => round($fzFolder['seconds'], 2),
		'winner_vs_cycle_oracle' => $winner,
		'fz_minus_cycle_oracle' => ($bestFz !== null && $cycleOracle !== null) ? ($bestFz - $cycleOracle) : null,
		'cycle_oracle_pct_payload' => ($payloadBytes > 0 && $cycleOracle !== null)
			? round(100.0 * $cycleOracle / $payloadBytes, 4) : null,
		'fzc_payload_pct' => ($payloadBytes > 0 && $bestFz !== null)
			? round(100.0 * $bestFz / $payloadBytes, 4) : null,
	);
}

/**
 * @return array{files: array<string,int>, total: int}
 */
function collectFolderFiles(string $dir): array
{
	$files = array();
	$total = 0;
	if(!is_dir($dir)) {
		return array('files' => $files, 'total' => 0);
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach($it as $fileInfo) {
		if(!$fileInfo->isFile()) {
			continue;
		}
		$path = $fileInfo->getPathname();
		$rel = substr($path, strlen($dir) + 1);
		$rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
		$n = filesize($path);
		if($n === false) {
			continue;
		}
		$files[$rel] = (int)$n;
		$total += (int)$n;
	}
	ksort($files);
	return array('files' => $files, 'total' => $total);
}

$results = array();
foreach(cycle_vs_fz_corpus_list($root) as $row) {
	if($caseFilter !== array()) {
		$label = (string)$row['label'];
		$ok = false;
		foreach($caseFilter as $f) {
			if($label === $f || str_contains($label, $f) || ($f === 'cycle' && is_array($row['recipe']))) {
				$ok = true;
				break;
			}
		}
		if(!$ok) {
			continue;
		}
	}
	fwrite(STDERR, 'encode ' . (string)$row['label'] . "\n");
	$results[] = cycle_vs_fz_probe($row);
}

$payload = array(
	'metric' => 'cycle_oracle_vs_fractal_zip_fzc',
	'note' => 'fzc = zip_folder (full fz search: multifractal/multidiff/BMP/inner as applicable). cycle_oracle = min binary/ascii recipe. cycle_inner_ltcb = gz9(FZCY payload)+sidecar.',
	'cases' => $results,
);

if($jsonOut) {
	echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
	exit(0);
}

printf("\nCycle encoding vs fractal_zip (.fz)\n");
printf("fzc = full zip_folder (multifractal / multidiff / BMP / inner search as applicable)\n\n");
printf("%-16s %-14s %10s %10s %10s %10s %8s %s\n",
	'corpus', 'facet', 'payload', 'cycle_or', 'fz_pay', 'fzc_dir', 'fz−cyc', 'winner');
printf("%s\n", str_repeat('-', 105));

$cycleWins = 0;
$fzWins = 0;
$cycleCases = 0;
foreach($results as $r) {
	if($r['cycle_oracle'] !== null) {
		$cycleCases++;
		if($r['winner_vs_cycle_oracle'] === 'cycle') {
			$cycleWins++;
		} elseif($r['winner_vs_cycle_oracle'] === 'fz') {
			$fzWins++;
		}
	}
	printf("%-16s %-14s %10s %10s %10s %10s %8s %s\n",
		$r['label'],
		$r['facet'],
		number_format((int)$r['payload_bytes']),
		$r['cycle_oracle'] === null ? '—' : (string)$r['cycle_oracle'],
		$r['fzc_payload_only'] > 0 ? (string)$r['fzc_payload_only'] : '—',
		$r['fzc_folder'] > 0 ? (string)$r['fzc_folder'] : '—',
		$r['fz_minus_cycle_oracle'] === null ? '—' : (string)$r['fz_minus_cycle_oracle'],
		(string)($r['winner_vs_cycle_oracle'] ?? '—')
	);
}

printf("%s\n", str_repeat('-', 105));
printf("On %d cycle corpora with oracle: cycle wins %d, fz wins %d\n\n", $cycleCases, $cycleWins, $fzWins);

printf("Facet reference corpora (cycle oracle N/A — fz facet specialty):\n\n");
printf("%-16s %-14s %10s %10s %10s %6s\n", 'corpus', 'facet', 'payload', 'gz9', 'fzc_pay', 'equiv');
foreach($results as $r) {
	if($r['cycle_oracle'] !== null) {
		continue;
	}
	printf("%-16s %-14s %10s %10s %10s %6d\n",
		$r['label'],
		$r['facet'],
		number_format((int)$r['payload_bytes']),
		$r['payload_gz9'] === null ? '—' : (string)$r['payload_gz9'],
		$r['fzc_payload_only'] > 0 ? (string)$r['fzc_payload_only'] : '—',
		(int)$r['fzc_payload_equiv']
	);
}

printf("\nNotes:\n");
printf("- fzc_pay = zip_folder on payload file alone (fair vs cycle oracle on same bytes).\n");
printf("- fzc_dir = whole test_files* folder (includes README on cycle cases).\n");
printf("- cycle_inner_ltcb ~17 B on 1 MiB cycles (6 B FZCI sidecar); oracle ~9-11 B with no sidecar.\n");
printf("- Negative fz−cyc = cycle oracle smaller than best fzc.\n");

fwrite(STDERR, "OK bench_cycle_vs_fz\n");
