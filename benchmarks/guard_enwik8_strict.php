#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * enwik8 strict sole gate.
 *
 * Milestone A (default --vs=zpaq): fzc < zpaq / zpaq_raw size from breakdown or best_ext.
 * Milestone B (--vs=all): fzc < min(gzip9, 7z, best_ext) including phda9/paq when raced.
 *
 *   php benchmarks/guard_enwik8_strict.php --vs=zpaq benchmarks/.enwik8_world_record.json
 *   php benchmarks/guard_enwik8_strict.php --vs=all --max-fzc=15300000 path.json
 *
 * World-record encode path: --bench-profile=world-record (see docs/WORLD_RECORD_PRESET.md).
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$maxFzc = null;
$vs = 'zpaq';
$path = null;
foreach (array_slice($argv, 1) as $a) {
	if (strncmp($a, '--max-fzc=', 10) === 0) {
		$maxFzc = (int) substr($a, 10);
		continue;
	}
	if (strncmp($a, '--vs=', 5) === 0) {
		$vs = strtolower(trim(substr($a, 5)));
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php guard_enwik8_strict.php [--vs=zpaq|all] [--max-fzc=N] <bench.json>\n";
		exit(0);
	}
	if ($a !== '' && $a[0] !== '-') {
		$path = $a;
	}
}

if ($path === null || !is_readable($path)) {
	fwrite(STDERR, "guard_enwik8_strict: need readable bench JSON (run with --bench-profile=world-record --only=109)\n");
	exit(2);
}

$j = bench_json_decode_file_assoc_try($path, 'guard_enwik8');
if ($j === null) {
	exit(2);
}

$row = null;
foreach (($j['cases'] ?? []) as $c) {
	if (!is_array($c)) {
		continue;
	}
	$lab = (string) ($c['label'] ?? '');
	if ($lab === 'test_files109' || $lab === '109' || str_contains($lab, 'enwik8')) {
		$row = $c;
		break;
	}
}
if ($row === null && isset($j['cases'][0]) && is_array($j['cases'][0])) {
	$row = $j['cases'][0];
}
if ($row === null) {
	fwrite(STDERR, "guard_enwik8_strict: no case row\n");
	exit(2);
}

$fzc = (int) ($row['fzc_bytes'] ?? 0);
$ext = (string) ($row['best_ext_winner'] ?? '');
$best = null;
$label = 'best_other';

if ($vs === 'zpaq') {
	$label = 'zpaq';
	$bd = $row['best_ext_breakdown'] ?? null;
	if (is_array($bd)) {
		foreach (array('zpaq_raw', 'zpaq') as $k) {
			if (!isset($bd[$k])) {
				continue;
			}
			$ent = $bd[$k];
			$b = is_array($ent) ? (int) ($ent['bytes'] ?? $ent['size'] ?? 0) : (int) $ent;
			if ($b > 0) {
				$best = $b;
				$label = $k;
				break;
			}
		}
		// breakdown may be list of {tool,bytes}
		if ($best === null) {
			foreach ($bd as $ent) {
				if (!is_array($ent)) {
					continue;
				}
				$t = strtolower((string) ($ent['tool'] ?? $ent['name'] ?? ''));
				$b = (int) ($ent['bytes'] ?? $ent['size'] ?? 0);
				if (($t === 'zpaq' || $t === 'zpaq_raw') && $b > 0) {
					$best = $b;
					$label = $t;
					break;
				}
			}
		}
	}
	if ($best === null && ($ext === 'zpaq' || $ext === 'zpaq_raw')
		&& isset($row['best_ext_folder_bytes'])) {
		$best = (int) $row['best_ext_folder_bytes'];
		$label = $ext;
	}
	// Documented lifestyle/zpaq_raw ceiling when JSON omitted the lane.
	if ($best === null) {
		$best = 19625015;
		$label = 'zpaq_raw_default';
	}
} else {
	$cands = array();
	foreach (array('gzip9_bundle_bytes', 'seven_zip_folder_bytes', 'best_ext_folder_bytes') as $k) {
		if (isset($row[$k]) && $row[$k] !== null && (int) $row[$k] > 0) {
			$cands[] = (int) $row[$k];
		}
	}
	$best = $cands !== array() ? min($cands) : null;
}

echo "enwik8 fzc={$fzc} vs={$vs} {$label}=" . ($best ?? 'null') . " ext_winner={$ext}\n";
if ($fzc <= 0 || $best === null) {
	fwrite(STDERR, "FAIL: missing sizes\n");
	exit(1);
}
if ($fzc >= $best) {
	fwrite(STDERR, "FAIL: not strict sole (fzc >= {$label})\n");
	exit(1);
}
if ($maxFzc !== null && $fzc > $maxFzc) {
	fwrite(STDERR, "FAIL: fzc={$fzc} > max-fzc={$maxFzc}\n");
	exit(1);
}
echo "OK strict sole vs {$label}\n";
exit(0);
