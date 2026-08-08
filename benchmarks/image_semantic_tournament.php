<?php
declare(strict_types=1);
/**
 * Run multiple semantic-image repack policies, benchmark each output corpus, and
 * report which policy gives the smallest .fz bytes.
 *
 * This is an experiment helper for perceptual/semantic (non-bitwise) image lanes.
 *
 * Usage:
 *   php benchmarks/image_semantic_tournament.php <src_dir> [--prefix=semt61] [--keep-all=1] [--no-baseline]
 *
 * Notes:
 * - Uses image_semantic_repack_to_dir.php with --no-manifest for fair .fz comparison.
 * - Benchmarks with run_benchmarks.php using:
 *     --no-verify --no-best-ext --no-case-timeout --no-multipass
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
$srcArg = $argv[1] ?? '';
if ($srcArg === '' || str_starts_with($srcArg, '-')) {
	fwrite(STDERR, "Usage: php benchmarks/image_semantic_tournament.php <src_dir> [--prefix=semt61] [--keep-all=1] [--no-baseline]\n");
	exit(2);
}

$prefix = 'semimgt';
$keepAll = false;
$includeBaseline = true;
for ($i = 2; $i < $argc; $i++) {
	$a = (string) $argv[$i];
	if (str_starts_with($a, '--prefix=')) {
		$prefix = preg_replace('/[^a-zA-Z0-9_-]+/', '_', (string) substr($a, 9)) ?: 'semimgt';
	} elseif ($a === '--keep-all=1') {
		$keepAll = true;
	} elseif ($a === '--no-baseline') {
		$includeBaseline = false;
	}
}

$src = normalize_path($repo, $srcArg);
if (!is_dir($src)) {
	fwrite(STDERR, "Not a directory: {$src}\n");
	exit(1);
}
$srcLabel = basename($src);

$policies = [
	[
		'name' => 'samefmt_conservative',
		'args' => ['--allow-format-change=0', '--max-display=2560x1440', '--oversample=2.5', '--min-long-edge=900', '--quality-bias=6'],
	],
	[
		'name' => 'samefmt_balanced',
		'args' => ['--allow-format-change=0', '--max-display=1920x1080', '--oversample=1.8', '--min-long-edge=800', '--quality-bias=0'],
	],
	[
		'name' => 'mixed_balanced',
		'args' => ['--allow-format-change=1', '--max-display=1920x1080', '--oversample=1.5', '--min-long-edge=700', '--quality-bias=0', '--score-mode=size'],
	],
	[
		'name' => 'mixed_balanced_proxy',
		'args' => ['--allow-format-change=1', '--max-display=1920x1080', '--oversample=1.5', '--min-long-edge=700', '--quality-bias=0', '--score-mode=proxy_fzc', '--proxy-size-weight=0.2'],
	],
	[
		'name' => 'mixed_aggressive',
		'args' => ['--allow-format-change=1', '--max-display=1600x900', '--oversample=1.3', '--min-long-edge=640', '--quality-bias=-8', '--score-mode=size'],
	],
	[
		'name' => 'mixed_aggressive_proxy',
		'args' => ['--allow-format-change=1', '--max-display=1600x900', '--oversample=1.3', '--min-long-edge=640', '--quality-bias=-8', '--score-mode=proxy_fzc', '--proxy-size-weight=0.2'],
	],
	[
		'name' => 'jpg_aggressive',
		'args' => ['--allow-format-change=1', '--formats=jpg', '--max-display=1600x900', '--oversample=1.35', '--min-long-edge=640', '--quality-bias=-10'],
	],
];

$results = [];
$dirsToMaybeDelete = [];

foreach ($policies as $p) {
	$name = (string) $p['name'];
	$dirLabel = $prefix . '_' . $name;
	$dst = $repo . DIRECTORY_SEPARATOR . $dirLabel;
	if (is_dir($dst)) {
		rrm($dst);
	}
	$cmd = array_merge(
		[
			'php',
			$repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'image_semantic_repack_to_dir.php',
			$src,
			$dst,
			'--no-manifest',
			'--lossy-source-only=1',
		],
		(array) $p['args']
	);
	$code = run_cmd($cmd, $out);
	if ($code !== 0) {
		fwrite(STDERR, "[policy {$name}] repack failed:\n{$out}\n");
		$results[] = ['name' => $name, 'label' => $dirLabel, 'ok' => false, 'error' => trim($out)];
		continue;
	}

	$benchJson = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_semt_' . bin2hex(random_bytes(6)) . '.json';
	$benchCmd = [
		'php',
		$repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_benchmarks.php',
		'--only=' . $dirLabel,
		'--no-verify',
		'--no-best-ext',
		'--no-case-timeout',
		'--no-multipass',
		'--json',
		'--out-json=' . $benchJson,
	];
	$code2 = run_cmd($benchCmd, $benchOut, ['FRACTAL_ZIP_BENCH_NO_BASELINE_CACHE' => '1']);
	if ($code2 !== 0 || !is_file($benchJson)) {
		fwrite(STDERR, "[policy {$name}] benchmark failed:\n{$benchOut}\n");
		$results[] = ['name' => $name, 'label' => $dirLabel, 'ok' => false, 'error' => 'benchmark_failed'];
		@unlink($benchJson);
		continue;
	}
	$j = bench_json_decode_file_assoc_try($benchJson, 'image_semantic_tournament', 512, 0, false);
	@unlink($benchJson);
	$row = null;
	if ($j !== null) {
		foreach (($j['cases'] ?? []) as $c) {
			if (($c['label'] ?? '') === $dirLabel) {
				$row = $c;
				break;
			}
		}
	}
	if (!is_array($row)) {
		$results[] = ['name' => $name, 'label' => $dirLabel, 'ok' => false, 'error' => 'missing_row'];
		continue;
	}
	$results[] = [
		'name' => $name,
		'label' => $dirLabel,
		'ok' => true,
		'raw' => (int) ($row['raw_bytes'] ?? 0),
		'fzc' => (int) ($row['fzc_bytes'] ?? 0),
		'outer' => (string) ($row['outer_codec'] ?? ''),
		'repack_log' => trim($out),
	];
	$dirsToMaybeDelete[] = $dst;
}

$okRows = array_values(array_filter($results, static fn ($r) => ($r['ok'] ?? false) === true));
if ($okRows === []) {
	fwrite(STDERR, "No successful policies.\n");
	exit(1);
}
usort(
	$okRows,
	static fn ($a, $b) => ((int) $a['fzc'] <=> (int) $b['fzc']) ?: ((int) $a['raw'] <=> (int) $b['raw'])
);
$best = $okRows[0];
$baselineFzc = null;
if ($includeBaseline) {
	$baselineFzc = benchmark_dir_label($repo, $srcLabel);
}

echo "semantic_image_tournament source={$srcLabel}\n";
if (is_int($baselineFzc)) {
	echo "  baseline_fzc={$baselineFzc} (source_dir)\n";
}
foreach ($okRows as $r) {
	$delta = is_int($baselineFzc) ? ((int) $r['fzc'] - $baselineFzc) : 0;
	$deltaS = is_int($baselineFzc) ? (($delta >= 0 ? '+' : '') . (string) $delta) : 'n/a';
	echo sprintf(
		"  %-20s dir=%-28s raw=%10d fzc=%10d delta_vs_src=%8s outer=%s\n",
		(string) $r['name'],
		(string) $r['label'],
		(int) $r['raw'],
		(int) $r['fzc'],
		$deltaS,
		(string) $r['outer']
	);
}
echo "best_policy=" . $best['name'] . " best_dir=" . $best['label'] . " best_fzc=" . $best['fzc'] . "\n";

if (!$keepAll) {
	foreach ($dirsToMaybeDelete as $d) {
		if (basename($d) === (string) $best['label']) {
			continue;
		}
		rrm($d);
	}
	echo "cleanup=kept_best_only\n";
} else {
	echo "cleanup=kept_all\n";
}

function normalize_path(string $repo, string $arg): string {
	if ($arg === '') {
		return $repo;
	}
	if ($arg[0] === '/') {
		return $arg;
	}
	return $repo . DIRECTORY_SEPARATOR . $arg;
}

/**
 * @param list<string> $cmd
 * @param array<string,string> $env
 */
function run_cmd(array $cmd, ?string &$output, array $env = []): int {
	$saved = [];
	foreach ($env as $k => $v) {
		$old = getenv($k);
		$saved[$k] = ($old === false) ? null : (string) $old;
		putenv($k . '=' . $v);
	}
	$spec = [
		0 => ['pipe', 'r'],
		1 => ['pipe', 'w'],
		2 => ['pipe', 'w'],
	];
	$p = proc_open($cmd, $spec, $pipes, null, null);
	if (!is_resource($p)) {
		restore_env($saved);
		$output = 'proc_open failed';
		return 1;
	}
	fclose($pipes[0]);
	$stdout = stream_get_contents($pipes[1]);
	$stderr = stream_get_contents($pipes[2]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	$code = proc_close($p);
	restore_env($saved);
	$output = (string) $stdout . (string) $stderr;
	return is_int($code) ? $code : 1;
}

/**
 * @param array<string, string|null> $saved
 */
function restore_env(array $saved): void {
	foreach ($saved as $k => $old) {
		if ($old === null) {
			putenv($k);
		} else {
			putenv($k . '=' . $old);
		}
	}
}

function rrm(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		if ($item->isDir()) {
			@rmdir($item->getPathname());
		} else {
			@unlink($item->getPathname());
		}
	}
	@rmdir($dir);
}

function benchmark_dir_label(string $repo, string $label): ?int {
	$benchJson = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_semt_base_' . bin2hex(random_bytes(6)) . '.json';
	$cmd = [
		'php',
		$repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_benchmarks.php',
		'--only=' . $label,
		'--no-verify',
		'--no-best-ext',
		'--no-case-timeout',
		'--no-multipass',
		'--json',
		'--out-json=' . $benchJson,
	];
	$code = run_cmd($cmd, $out, ['FRACTAL_ZIP_BENCH_NO_BASELINE_CACHE' => '1']);
	if ($code !== 0 || !is_file($benchJson)) {
		@unlink($benchJson);
		return null;
	}
	$j = bench_json_decode_file_assoc_try($benchJson, 'image_semantic_tournament fzc', 512, 0, false);
	@unlink($benchJson);
	if ($j !== null) {
		foreach (($j['cases'] ?? []) as $c) {
			if (($c['label'] ?? '') === $label) {
				return (int) ($c['fzc_bytes'] ?? 0);
			}
		}
	}
	return null;
}
