#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Derive a final TikTok recommendation from a sweep output directory.
 *
 * Usage:
 *   php benchmarks/tiktok_recommend_from_sweep.php --out-dir=benchmarks/.tiktok_sweep_YYYYmmdd_HHMMSS
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$outDir = '';
foreach (array_slice($argv, 1) as $a) {
	if (!is_string($a)) {
		continue;
	}
	if (strncmp($a, '--out-dir=', 10) === 0) {
		$outDir = trim(substr($a, 10));
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php benchmarks/tiktok_recommend_from_sweep.php --out-dir=PATH\n";
		exit(0);
	}
}

if ($outDir === '' || !is_dir($outDir)) {
	fwrite(STDERR, "tiktok_recommend_from_sweep: pass --out-dir=existing_dir\n");
	exit(2);
}

$profiles = array(
	'default' => $outDir . '/default.json',
	'large_fast' => $outDir . '/large_fast.json',
	'large_balanced' => $outDir . '/large_balanced.json',
	'large_bytes' => $outDir . '/large_bytes.json',
);

$rows = array();
foreach ($profiles as $tag => $path) {
	if (!is_file($path)) {
		continue;
	}
	$j = bench_json_decode_file_assoc_try($path, 'tiktok_recommend_from_sweep');
	if ($j === null) {
		continue;
	}
	$c = $j['cases'][0] ?? array();
	$fzc = (int) ($c['fzc_bytes'] ?? 0);
	$zip = (float) ($c['zip_seconds'] ?? 0);
	if ($fzc <= 0 || $zip <= 0.0) {
		continue;
	}
	$rows[] = array(
		'tag' => $tag,
		'fzc_bytes' => $fzc,
		'zip_seconds' => $zip,
		'outer' => (string) ($c['outer_codec'] ?? '?'),
	);
}

if ($rows === array()) {
	fwrite(STDERR, "tiktok_recommend_from_sweep: no usable profile rows\n");
	exit(1);
}

$bestBytes = min(array_map(static fn(array $r): int => (int) $r['fzc_bytes'], $rows));
$eligibleMax = (int) floor((float) $bestBytes * 1.01);
$bestProfile = null;
foreach ($rows as $r) {
	if ((int) $r['fzc_bytes'] <= $eligibleMax) {
		if ($bestProfile === null || (float) $r['zip_seconds'] < (float) $bestProfile['zip_seconds']) {
			$bestProfile = $r;
		}
	}
}
if (!is_array($bestProfile)) {
	fwrite(STDERR, "tiktok_recommend_from_sweep: unable to choose profile\n");
	exit(1);
}

$probeRecPath = $outDir . '/probe_recommend/recommend.json';
$probeRec = null;
if (is_file($probeRecPath)) {
	$probeRec = bench_json_decode_file_assoc_try($probeRecPath, 'tiktok_recommend_from_sweep probe');
}
$probe = is_array($probeRec) ? (int) ($probeRec['recommend_probe'] ?? 0) : 0;

$corpus = is_array($probeRec) && isset($probeRec['corpus']) && is_string($probeRec['corpus']) && $probeRec['corpus'] !== ''
	? $probeRec['corpus']
	: null;
$profileArg = str_replace('_', '-', (string) $bestProfile['tag']);
$payload = array(
	'out_dir' => $outDir,
	'corpus' => $corpus,
	'recommend_profile_tag' => (string) $bestProfile['tag'],
	'recommend_profile_arg' => $profileArg,
	'recommend_profile_bytes' => (int) $bestProfile['fzc_bytes'],
	'recommend_profile_zip_seconds' => (float) $bestProfile['zip_seconds'],
	'recommend_profile_outer' => (string) $bestProfile['outer'],
	'recommend_probe_max_bytes' => $probe > 0 ? $probe : null,
	'profiles' => $rows,
	'probe_recommend_json' => is_file($probeRecPath) ? $probeRecPath : null,
);

$json = bench_json_encode_try($payload, true);
if ($json === null) {
	fwrite(STDERR, "tiktok_recommend_from_sweep: json_encode failed\n");
	exit(1);
}
file_put_contents($outDir . '/recommend_overall.json', $json . "\n");

$env = "export FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES=" . ($probe > 0 ? (string) $probe : '') . "\n";
$env .= "# recommended bench profile tag: " . (string) $bestProfile['tag'] . "\n";
$env .= "export FZ_TIKTOK_RECOMMENDED_BENCH_PROFILE=" . $profileArg . "\n";
$env .= "# apply profile on run_benchmarks.php with --bench-profile=${FZ_TIKTOK_RECOMMENDED_BENCH_PROFILE}\n";
file_put_contents($outDir . '/recommend_overall.env', $env);

$replay = "#!/usr/bin/env bash\n";
$replay .= "set -euo pipefail\n";
$replay .= "REPO=" . escapeshellarg($repoRoot) . "\n";
$replay .= "cd \"\${REPO}\"\n";
$replay .= "source " . escapeshellarg($outDir . '/recommend_overall.env') . "\n";
$replay .= "ONLY=\"\${1:-" . ($corpus !== null ? $corpus : 'test_files133_sample') . "}\"\n";
$replay .= "OUT_JSON=\"\${2:-" . $outDir . "/replay.json}\"\n";
$replay .= "if [[ -n \"\${FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES:-}\" ]]; then\n";
$replay .= "  export FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES\n";
$replay .= "fi\n";
$replay .= "php benchmarks/run_benchmarks.php --only=\"\$ONLY\" --large --no-case-timeout --no-verify --json --no-save-last-json --out-json=\"\$OUT_JSON\" --bench-profile=\"\${FZ_TIKTOK_RECOMMENDED_BENCH_PROFILE}\"\n";
$replayPath = $outDir . '/recommend_replay.sh';
file_put_contents($replayPath, $replay);
@chmod($replayPath, 0755);

echo "recommend_profile=" . (string) $bestProfile['tag']
	. " bytes=" . (string) $bestProfile['fzc_bytes']
	. " zip_s=" . sprintf('%.2f', (float) $bestProfile['zip_seconds'])
	. " probe_max_B=" . ($probe > 0 ? (string) $probe : '?') . "\n";
echo "wrote {$outDir}/recommend_overall.json, recommend_overall.env, recommend_replay.sh\n";
