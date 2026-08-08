<?php
declare(strict_types=1);

/**
 * Desktop home — same compress/extract surface as the web demos, local only.
 */
$verFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'VERSION';
$version = is_file($verFile) ? trim((string) file_get_contents($verFile)) : '0.1.0';
$favicon = '/examples/icons/fractal-zip-favicon.svg';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="dark">
<link rel="icon" href="<?= htmlspecialchars($favicon, ENT_QUOTES, 'UTF-8') ?>" type="image/svg+xml">
<title>fractal_zip <?= htmlspecialchars($version, ENT_QUOTES, 'UTF-8') ?></title>
<style>
:root {
	color-scheme: dark;
	--bg: #0c0e12;
	--bg-elev: #141820;
	--border: #2a3344;
	--text: #e8eaed;
	--muted: #9aa3b2;
	--accent: #38bdf8;
	--link: #7dd3fc;
	font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
	line-height: 1.5;
}
*, *::before, *::after { box-sizing: border-box; }
html { background: var(--bg); }
body {
	margin: 0;
	min-height: 100vh;
	color: var(--text);
	background:
		radial-gradient(ellipse 80% 50% at 50% -10%, #1a3a4a 0%, transparent 55%),
		var(--bg);
	max-width: 36rem;
	margin-inline: auto;
	padding: 2.5rem 1.25rem 3rem;
}
.brand {
	display: flex;
	align-items: center;
	gap: 0.75rem;
	margin-bottom: 0.35rem;
}
.brand img { width: 2.25rem; height: 2.25rem; }
.brand h1 {
	font-size: 1.75rem;
	font-weight: 650;
	letter-spacing: -0.02em;
	margin: 0;
}
.ver {
	color: var(--muted);
	font-size: 0.85rem;
	margin: 0 0 1.25rem;
}
.lead {
	color: var(--muted);
	margin: 0 0 1.75rem;
	font-size: 1.05rem;
}
.actions {
	display: grid;
	gap: 0.75rem;
}
a.card {
	display: block;
	text-decoration: none;
	color: var(--text);
	background: var(--bg-elev);
	border: 1px solid var(--border);
	border-radius: 10px;
	padding: 1.1rem 1.2rem;
	transition: border-color 0.15s, background 0.15s;
}
a.card:hover, a.card:focus-visible {
	border-color: var(--accent);
	background: #1a2332;
	outline: none;
}
a.card strong {
	display: block;
	font-size: 1.1rem;
	color: var(--link);
	margin-bottom: 0.25rem;
}
a.card span { color: var(--muted); font-size: 0.92rem; }
footer {
	margin-top: 2.5rem;
	font-size: 0.85rem;
	color: var(--muted);
}
footer code { color: var(--text); font-size: 0.9em; }
</style>
</head>
<body>
<div class="brand">
	<img src="<?= htmlspecialchars($favicon, ENT_QUOTES, 'UTF-8') ?>" alt="" width="36" height="36">
	<h1>fractal_zip</h1>
</div>
<p class="ver">Desktop <?= htmlspecialchars($version, ENT_QUOTES, 'UTF-8') ?> · local compress &amp; extract</p>
<p class="lead">Same tools as the web demos, running on your machine. No upload caps; jobs stay local.</p>
<nav class="actions" aria-label="Main">
	<a class="card" href="/examples/fzc_compress.php">
		<strong>Compress to .fz</strong>
		<span>Folder or files → fractal_zip archive</span>
	</a>
	<a class="card" href="/examples/fzc_extract.php">
		<strong>Extract .fz</strong>
		<span>Open an archive and download members</span>
	</a>
	<a class="card" href="/examples/fzc_capability_report.php">
		<strong>Capability report</strong>
		<span>Which outer codecs (7z, zstd, zpaq, …) are available</span>
	</a>
</nav>
<footer>
	<p>Close this window’s terminal / launcher to stop the local server. Optional tools on <code>PATH</code> improve ratio — see the capability report.</p>
</footer>
</body>
</html>
