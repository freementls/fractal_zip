<?php
declare(strict_types=1);
/**
 * Local review of fzcodec lifestyle + ultra vs published Squash B-best.
 * Prefers the C-plugin freeze; falls back to the folder .fz writeup.
 */
$repoRoot = dirname(__DIR__, 2);
$pluginPath = $repoRoot . '/benchmarks/.squash_plugin_review.json';
$folderPath = $repoRoot . '/benchmarks/.squash_dual_review.json';
$reviewPath = is_file($pluginPath) ? $pluginPath : $folderPath;
$review = is_file($reviewPath) ? json_decode((string) file_get_contents($reviewPath), true) : null;
$isPlugin = ($reviewPath === $pluginPath);
if (!is_array($review)) {
	$review = null;
}
$n = (int) ($review['n'] ?? 0);
$uWins = (int) ($review['ultra_byte_wins'] ?? 0);
$lEff = (int) ($review['lifestyle_efficiency_wins'] ?? 0);
$lBytes = (int) ($review['lifestyle_byte_wins'] ?? 0);
$uSweep = !empty($review['ultra_bytes_sweep']);
$lSweep = !empty($review['lifestyle_efficiency_sweep']);
$cases = is_array($review['cases'] ?? null) ? $review['cases'] : [];
$fmt = static function ($n): string {
	return number_format((int) $n);
};
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>fractal_zip vs Squash B-best</title>
	<style>
		:root { --ink:#12263a; --mute:#4a6278; --paper:#eef3f7; --win:#0f766e; --loss:#b45309; --line:rgba(18,38,58,.12); --mono:ui-monospace,Menlo,monospace; --sans:"IBM Plex Sans","Segoe UI",sans-serif; }
		* { box-sizing:border-box; }
		body { margin:0; color:var(--ink); font:17px/1.5 var(--sans); background:linear-gradient(180deg,#e4ecf3,#eef3f7); }
		main { max-width:1100px; margin:0 auto; padding:2rem 1.25rem 4rem; }
		h1 { font-size:1.7rem; margin:0 0 .35rem; }
		.sub, p { color:var(--mute); }
		.bars { display:flex; gap:1rem; flex-wrap:wrap; margin:1.25rem 0 1.5rem; }
		.card { background:#fff; border:1px solid var(--line); border-radius:14px; padding:1rem 1.15rem; flex:1 1 240px; }
		.card strong { display:block; font-size:1.35rem; }
		.ok { color:var(--win); }
		.bad { color:var(--loss); }
		.caveat { background:#fff7ed; border:1px solid #fdba74; border-radius:12px; padding:.85rem 1rem; color:#7c2d12; }
		table { width:100%; border-collapse:collapse; font-size:.86rem; background:#fff; border-radius:12px; overflow:hidden; }
		th, td { padding:.4rem .55rem; text-align:right; border-bottom:1px solid var(--line); white-space:nowrap; }
		th:first-child, td:first-child, th:nth-child(2), td:nth-child(2) { text-align:left; }
		th { background:#f1f5f9; font-weight:600; }
		td.mono { font-family:var(--mono); }
		tr.fail td { background:#fff7ed; }
		footer { margin-top:2rem; color:var(--mute); font-size:.9rem; }
		a { color:#0f766e; }
	</style>
</head>
<body>
<main>
	<h1>fractal_zip vs Squash Compression Benchmark</h1>
	<p class="sub"><?= $isPlugin
		? 'Lifestyle and ultra <strong>fzcodec</strong> (C buffer plugin, FZC1) vs published hoplite B-best. This is the path a quixdb re-run would execute — not the PHP folder <code>.fz</code> pipeline.'
		: 'Lifestyle and ultra <code>.fz</code> settings compared to published hoplite B-best. Folder pipeline review; the shippable plugin is fzcodec.' ?></p>

	<div class="bars">
		<div class="card">
			<div>Ultra / bytes</div>
			<strong class="<?= $uSweep ? 'ok' : 'bad' ?>"><?= $n ? "{$uWins}/{$n}" : '—' ?></strong>
			<div><?= $uSweep ? 'sweep: smaller than every B-best' : 'must be strictly smaller than every B-best' ?></div>
		</div>
		<div class="card">
			<div>Lifestyle / efficiency</div>
			<strong class="<?= $lSweep ? 'ok' : 'bad' ?>"><?= $n ? "{$lEff}/{$n}" : '—' ?></strong>
			<div><?= $lSweep ? 'sweep: not dominated (smaller or faster)' : 'not Pareto-dominated by B-best' ?></div>
		</div>
		<div class="card">
			<div>Lifestyle / bytes</div>
			<strong><?= $n ? "{$lBytes}/{$n}" : '—' ?></strong>
			<div>smaller than B-best (informational)</div>
		</div>
	</div>

	<p class="caveat"><strong>Methodology.</strong> <?= htmlspecialchars((string) ($review['methodology'] ?? ''), ENT_QUOTES) ?> Source: <a href="https://quixdb.github.io/squash-benchmark/">quixdb.github.io/squash-benchmark</a>. Library: <a href="../fzcodec/">fzcodec</a>.</p>

	<?php if ($cases === []): ?>
		<p>No review JSON yet. After remasure: <code>php benchmarks/squash_dual_compare.php --refresh-csv</code></p>
	<?php else: ?>
		<p>Generated <?= htmlspecialchars((string) ($review['generated'] ?? ''), ENT_QUOTES) ?>. Machine <?= htmlspecialchars((string) ($review['machine'] ?? 'hoplite'), ENT_QUOTES) ?>.</p>
		<table>
			<thead>
				<tr>
					<th>corpus</th>
					<th>dataset</th>
					<th>B-best</th>
					<th>sq B</th>
					<th>life B</th>
					<th>ultra B</th>
					<th>life s</th>
					<th>ultra s</th>
					<th>U bytes</th>
					<th>L eff</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($cases as $r):
				$fail = empty($r['ultra_bytes_win']) || empty($r['lifestyle_efficiency_win']);
				?>
				<tr class="<?= $fail ? 'fail' : '' ?>">
					<td><?= htmlspecialchars((string) $r['corpus_dir'], ENT_QUOTES) ?></td>
					<td><?= htmlspecialchars((string) $r['dataset'], ENT_QUOTES) ?></td>
					<td><?= htmlspecialchars((string) $r['squash_best_compressed_label'], ENT_QUOTES) ?></td>
					<td class="mono"><?= $fmt($r['squash_best_compressed_bytes']) ?></td>
					<td class="mono"><?= $fmt($r['lifestyle_fzc_bytes']) ?></td>
					<td class="mono"><?= $fmt($r['ultra_fzc_bytes']) ?></td>
					<td class="mono"><?= number_format((float) $r['lifestyle_zip_seconds'], 2) ?></td>
					<td class="mono"><?= number_format((float) $r['ultra_zip_seconds'], 2) ?></td>
					<td class="<?= !empty($r['ultra_bytes_win']) ? 'ok' : 'bad' ?>"><?= !empty($r['ultra_bytes_win']) ? 'WIN' : 'LOSS' ?></td>
					<td class="<?= !empty($r['lifestyle_efficiency_win']) ? 'ok' : 'bad' ?>"><?= !empty($r['lifestyle_efficiency_win']) ? 'WIN' : 'LOSS' ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<footer>
		<a href="http://localhost/fractal_zip/examples/squash_benchmark_results/">localhost page</a>
		· <a href="https://freement.cloud/fractal_zip/examples/squash_benchmark_results/">public mirror</a>
		· regenerate plugin table: <code>php tools/fzcodec/tests/plugin_review.php</code>
	</footer>
</main>
</body>
</html>
