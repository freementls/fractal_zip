<?php
declare(strict_types=1);
$readme = dirname(__DIR__, 2) . '/tools/fzcodec/README.md';
$readmeHtml = is_file($readme)
	? htmlspecialchars((string) file_get_contents($readme), ENT_QUOTES)
	: 'README missing.';
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>fzcodec — general-purpose C compressor</title>
	<style>
		:root { --ink:#12263a; --mute:#4a6278; --line:rgba(18,38,58,.12); --mono:ui-monospace,Menlo,Consolas,monospace; --sans:system-ui,-apple-system,"Segoe UI",sans-serif; }
		* { box-sizing:border-box; }
		body { margin:0; color:var(--ink); font:17px/1.55 var(--sans); background:linear-gradient(180deg,#e4ecf3,#eef3f7); }
		main { max-width:820px; margin:0 auto; padding:2rem 1.25rem 4rem; }
		h1 { font-size:1.7rem; margin:0 0 .4rem; }
		p, li { color:var(--mute); }
		.card { background:#fff; border:1px solid var(--line); border-radius:14px; padding:1.1rem 1.2rem; margin:1rem 0; }
		pre { background:#0f172a; color:#e2e8f0; padding:1rem 1.1rem; border-radius:10px; overflow:auto; font:13px/1.45 var(--mono); }
		code { font-family:var(--mono); font-size:.92em; }
		a { color:#0f766e; }
		.readme { white-space:pre-wrap; font:13px/1.45 var(--mono); background:#fff; border:1px solid var(--line); border-radius:12px; padding:1rem; overflow:auto; max-height:70vh; }
	</style>
</head>
<body>
<main>
	<h1>fzcodec</h1>
	<p>Standalone C library, CLI, and Squash plugin. Sniff → transcode (JPEG) → class-shaped codec race. No corpus-name special cases.</p>

	<div class="card">
		<strong>Use it</strong>
		<pre>cd tools/fzcodec && make && make test
./build/fzcodec compress --lifestyle photo.jpg photo.fzc
./build/fzcodec decompress photo.fzc photo.jpg</pre>
		<p>Presets: <code>lifestyle</code> (default) and <code>ultra</code>. libzpaq is compiled in. Optional tools on PATH: <code>lepton</code>, <code>bsc</code>, <code>paq8px</code>.</p>
	</div>

	<div class="card">
		<p><a href="../squash_benchmark_results/">Lifestyle / ultra vs published Squash B-best</a> — C plugin scoreboard (what quixdb would re-run).</p>
		<p>Sources: <code>tools/fzcodec/</code> in this repo.</p>
	</div>

	<h2>README</h2>
	<div class="readme"><?= $readmeHtml ?></div>
</main>
</body>
</html>
