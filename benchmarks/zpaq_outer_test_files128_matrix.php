#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

/**
 * A/B matrix for test_files128: same benchmark driver as
 *   php benchmarks/run_benchmarks.php --only=128 --no-best-ext
 * but sweeps FRACTAL_ZIP_ZPAQ (binary path) × FRACTAL_ZIP_ZPAQ_THREADS.
 *
 * Each configuration runs in a **new PHP process** so fractal_zip’s zpaq argv cache resets.
 *
 * Usage (from repo root):
 *   php benchmarks/zpaq_outer_test_files128_matrix.php
 *   php benchmarks/zpaq_outer_test_files128_matrix.php /usr/bin/zpaq /path/to/zpaqfranz
 *   php benchmarks/zpaq_outer_test_files128_matrix.php --threads=off,0,4 /opt/zpaqfranz
 *
 * Env (optional):
 *   ZPAQ_128_THREADS=off,0,4,8   — override default thread token list
 *
 * Multithreaded zpaq: install **zpaqfranz** (or symlink as {@code zpaq}) and/or set
 *   export FRACTAL_ZIP_ZPAQ=/path/to/zpaqfranz   (forwarded to each bench subprocess)
 * If your binary supports {@code -threads} but detection fails:
 *   export FRACTAL_ZIP_ZPAQ_GLOBAL_THREADS=1
 *
 * Requires directory test_files128/ under the repo root (same as run_benchmarks.php).
 */

$root = dirname(__DIR__);
$corpus = 'test_files128';
$corpusDir = $root . DIRECTORY_SEPARATOR . $corpus;
if (!is_dir($corpusDir)) {
	fwrite(STDERR, "zpaq_outer_test_files128_matrix: missing {$corpusDir}\n");
	exit(1);
}

$defaultThreads = array('off', '0', '4', '8');
$thrEnv = getenv('ZPAQ_128_THREADS');
if (is_string($thrEnv) && trim($thrEnv) !== '') {
	$defaultThreads = array_values(array_filter(array_map('trim', explode(',', $thrEnv)), static function ($s) {
		return $s !== '';
	}));
}

$bins = array();
$threadsList = $defaultThreads;
$argvRest = array_slice($argv, 1);
foreach ($argvRest as $a) {
	if ($a === '--') {
		continue;
	}
	if (strncmp($a, '--threads=', 10) === 0) {
		$rest = trim(substr($a, 10));
		$threadsList = array_values(array_filter(array_map('trim', explode(',', $rest)), static function ($s) {
			return $s !== '';
		}));
		if ($threadsList === []) {
			fwrite(STDERR, "zpaq_outer_test_files128_matrix: empty --threads=\n");
			exit(1);
		}
		continue;
	}
	if (strncmp($a, '--', 2) === 0) {
		fwrite(STDERR, "zpaq_outer_test_files128_matrix: unknown flag {$a}\n");
		exit(1);
	}
	if (is_string($a) && $a !== '') {
		$bins[] = $a;
	}
}

if ($bins === []) {
	$bins = array(null);
}

$jsonTmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_z128_' . bin2hex(random_bytes(8)) . '.json';

$rows = array();
foreach ($bins as $bin) {
	foreach ($threadsList as $tok) {
		$env = $_ENV;
		// Do not clear FRACTAL_ZIP_ZPAQ here: shell `export FRACTAL_ZIP_ZPAQ=…` must reach the bench child.
		foreach (array('FRACTAL_ZIP_ZPAQ_THREADS', 'FRACTAL_ZIP_BENCH_ZPAQ_THREADS') as $zk) {
			unset($env[$zk]);
		}
		$env['FRACTAL_ZIP_SUPPRESS_HTML'] = '1';
		if ($bin !== null) {
			$env['FRACTAL_ZIP_ZPAQ'] = $bin;
		} else {
			$zpaqEnv = getenv('FRACTAL_ZIP_ZPAQ');
			if ($zpaqEnv !== false && trim((string) $zpaqEnv) !== '') {
				$env['FRACTAL_ZIP_ZPAQ'] = trim((string) $zpaqEnv);
			}
		}
		$env['FRACTAL_ZIP_ZPAQ_THREADS'] = (string) $tok;

		if ($bin !== null) {
			$labelBin = $bin;
		} else {
			$ze = getenv('FRACTAL_ZIP_ZPAQ');
			$labelBin = (is_string($ze) && trim($ze) !== '') ? trim($ze) : '(PATH)';
		}
		$label = $labelBin . '  threads=' . $tok;

		@unlink($jsonTmp);
		$bench = $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_benchmarks.php';
		$cmd = array(
			PHP_BINARY,
			$bench,
			'--only=128',
			'--no-best-ext',
			'--json',
			'--no-save-last-json',
			'--out-json=' . $jsonTmp,
		);

		$t0 = microtime(true);
		$des = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
		$p = proc_open($cmd, $des, $pipes, $root, $env);
		if (!is_resource($p)) {
			fwrite(STDERR, "zpaq_outer_test_files128_matrix: proc_open failed for {$label}\n");
			exit(1);
		}
		fclose($pipes[0]);
		stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		stream_get_contents($pipes[2]);
		fclose($pipes[2]);
		$code = proc_close($p);
		$wall = microtime(true) - $t0;

		$fzc = null;
		$zipS = null;
		$resolved = null;
		$franz = null;
		$threadsShell = null;
		$outerMeth = null;
		if ($code === 0 && is_file($jsonTmp)) {
			$j = bench_json_decode_file_assoc_try($jsonTmp, 'zpaq_outer_test_files128_matrix', 512, 0, false);
			$cases = is_array($j) && isset($j['cases']) && is_array($j['cases']) ? $j['cases'] : array();
			$row = null;
			foreach ($cases as $c) {
				if (is_array($c) && isset($c['label']) && $c['label'] === $corpus) {
					$row = $c;
					break;
				}
			}
			if ($row !== null) {
				$fzc = isset($row['fzc_bytes']) ? (int) $row['fzc_bytes'] : null;
				$zipS = isset($row['zip_seconds']) ? (float) $row['zip_seconds'] : null;
				$resolved = isset($row['zpaq_executable']) ? (string) $row['zpaq_executable'] : null;
				$franz = isset($row['zpaq_franz_banner_probe']) ? (bool) $row['zpaq_franz_banner_probe'] : null;
				$threadsShell = isset($row['zpaq_global_threads_shell']) ? $row['zpaq_global_threads_shell'] : null;
				$outerMeth = isset($row['outer_zpaq_method']) ? (string) $row['outer_zpaq_method'] : null;
			}
		}

		$rows[] = array(
			'label' => $label,
			'exit' => $code,
			'wall_s' => $wall,
			'fzc_bytes' => $fzc,
			'zip_seconds' => $zipS,
			'resolved_zpaq' => $resolved,
			'zpaq_franz_probe' => $franz,
			'zpaq_global_threads_shell' => $threadsShell,
			'outer_zpaq_method' => $outerMeth,
		);
	}
}

@unlink($jsonTmp);

// ---- output ----
echo "test_files128 zpaq matrix (same as: php benchmarks/run_benchmarks.php --only=128 --no-best-ext)\n";
echo str_repeat('=', 120) . "\n";
printf(
	"%-52s  %10s  %12s  %10s  %8s  %-10s  %s\n",
	'config',
	'exit',
	'wall_s',
	'fzc_B',
	'zip_s',
	'outer_m',
	'resolved_zpaq / threads_ok / threads argv'
);
echo str_repeat('-', 120) . "\n";
foreach ($rows as $r) {
	$probe = $r['zpaq_franz_probe'];
	$probeS = $probe === null ? '?' : ($probe ? 'yes' : 'no');
	$ts = $r['zpaq_global_threads_shell'];
	$tsS = $ts === null || $ts === '' ? '(none)' : (string) $ts;
	printf(
		"%-52s  %10d  %12.4f  %10s  %8s  %-10s  %s | threads_ok=%s argv=%s\n",
		substr($r['label'], 0, 52),
		(int) $r['exit'],
		(float) $r['wall_s'],
		$r['fzc_bytes'] !== null ? (string) $r['fzc_bytes'] : '—',
		$r['zip_seconds'] !== null ? sprintf('%.4f', $r['zip_seconds']) : '—',
		$r['outer_zpaq_method'] !== null && $r['outer_zpaq_method'] !== '' ? $r['outer_zpaq_method'] : '—',
		$r['resolved_zpaq'] ?? '—',
		$probeS,
		$tsS
	);
}
echo str_repeat('=', 120) . "\n";
echo "Best fzc_bytes (smaller is better): ";
$best = null;
$bestL = null;
foreach ($rows as $r) {
	if ((int) $r['exit'] !== 0 || $r['fzc_bytes'] === null) {
		continue;
	}
	if ($best === null || $r['fzc_bytes'] < $bestL) {
		$best = $r['label'];
		$bestL = $r['fzc_bytes'];
	}
}
echo ($best !== null) ? "{$bestL}  ({$best})\n" : "n/a\n";

$anyThreads = false;
foreach ($rows as $r) {
	if ($r['zpaq_franz_probe'] === true) {
		$anyThreads = true;
		break;
	}
}
if (!$anyThreads) {
	fwrite(
		STDERR,
		"[zpaq 128 matrix] No row has threads_ok=yes: `command -v zpaq` is probably stock zpaq (single-threaded).\n"
		. "  Install zpaqfranz, then e.g. export FRACTAL_ZIP_ZPAQ=/path/to/zpaqfranz\n"
		. "  If your binary supports -threads but detection fails: export FRACTAL_ZIP_ZPAQ_GLOBAL_THREADS=1\n"
	);
}

$exit = 0;
foreach ($rows as $r) {
	if ((int) $r['exit'] !== 0) {
		$exit = 1;
	}
}
exit($exit);
