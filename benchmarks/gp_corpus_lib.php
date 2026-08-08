<?php
declare(strict_types=1);
/**
 * Helpers for public general-purpose corpora (test_files202–210).
 * Downloads go under benchmarks/.gp_corpus_cache/ (gitignored).
 */

function gp_repo_root(): string
{
	return dirname(__DIR__);
}

function gp_cache_dir(): string
{
	return gp_repo_root() . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.gp_corpus_cache';
}

function gp_mkdir(string $path): void
{
	if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
		throw new RuntimeException('mkdir failed: ' . $path);
	}
}

function gp_rrmdir(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		$item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
	}
	@rmdir($dir);
}

/** @param list<string> $argv */
function gp_run(array $argv, ?string $cwd = null): void
{
	$cmd = implode(' ', array_map('escapeshellarg', $argv));
	$descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
	$p = proc_open($argv, $descriptors, $pipes, $cwd);
	if (!is_resource($p)) {
		throw new RuntimeException('proc_open failed: ' . $cmd);
	}
	fclose($pipes[0]);
	$out = stream_get_contents($pipes[1]);
	$err = stream_get_contents($pipes[2]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	$code = proc_close($p);
	if ($code !== 0) {
		throw new RuntimeException("Command failed ($code): $cmd\n$err\n$out");
	}
}

function gp_which(string $name): ?string
{
	$p = trim((string) shell_exec('command -v ' . escapeshellarg($name) . ' 2>/dev/null'));
	return $p !== '' ? $p : null;
}

/**
 * Download URL to cache path (resumable). Optional sha256 hex.
 */
function gp_download(string $url, string $destPath, ?string $sha256 = null): string
{
	gp_mkdir(dirname($destPath));
	$part = $destPath . '.part';
	if (is_file($destPath) && ($sha256 === null || strtolower((string) hash_file('sha256', $destPath)) === strtolower($sha256))) {
		return $destPath;
	}
	if (is_file($destPath) && $sha256 !== null) {
		@unlink($destPath);
	}
	echo "  download " . basename($destPath) . " …\n";
	// Exclusive lock so concurrent builders/prefetchers cannot corrupt the same .part.
	$lockPath = $destPath . '.lock';
	$lockFh = fopen($lockPath, 'c');
	if ($lockFh === false || !flock($lockFh, LOCK_EX)) {
		if (is_resource($lockFh)) {
			fclose($lockFh);
		}
		throw new RuntimeException('download lock failed: ' . $lockPath);
	}
	try {
		// Another waiter may have finished the file while we blocked on the lock.
		if (is_file($destPath) && filesize($destPath) > 0
			&& ($sha256 === null || strtolower((string) hash_file('sha256', $destPath)) === strtolower($sha256))) {
			return $destPath;
		}
		$aria = gp_which('aria2c');
		$wget = gp_which('wget');
		if ($aria !== null) {
			gp_run([
				$aria, '-c', '-x', '8', '-s', '8', '-k', '1M',
				'--file-allocation=none', '--auto-file-renaming=false',
				'--allow-overwrite=true', '--console-log-level=warn',
				'-d', dirname($part), '-o', basename($part),
				$url,
			]);
		} elseif ($wget !== null) {
			gp_run([$wget, '-c', '--tries=8', '--timeout=120', '--waitretry=5', '-O', $part, $url]);
		} else {
			$argv = [
				'curl', '-fL', '--retry', '8', '--retry-delay', '5',
				'--connect-timeout', '60', '--speed-time', '120', '--speed-limit', '1024',
				'-o', $part,
				$url,
			];
			if (is_file($part) && filesize($part) > 0) {
				array_splice($argv, 1, 0, ['-C', '-']);
			}
			gp_run($argv);
		}
		if (!is_file($part) || filesize($part) <= 0) {
			throw new RuntimeException('download empty: ' . $url);
		}
		$cl = gp_http_content_length($url);
		$gotSize = (int) filesize($part);
		if ($cl > 0 && $gotSize < (int) ($cl * 0.98)) {
			@unlink($part);
			throw new RuntimeException("download truncated for $url: got $gotSize expected ~$cl");
		}
		if ($sha256 !== null) {
			$got = strtolower((string) hash_file('sha256', $part));
			if ($got !== strtolower($sha256)) {
				throw new RuntimeException("sha256 mismatch for $destPath: got $got expected $sha256");
			}
		}
		if (!@rename($part, $destPath)) {
			if (!@copy($part, $destPath)) {
				throw new RuntimeException('rename/copy failed: ' . $part);
			}
			@unlink($part);
		}
		return $destPath;
	} finally {
		flock($lockFh, LOCK_UN);
		fclose($lockFh);
		@unlink($lockPath);
	}
}

function gp_http_content_length(string $url): int
{
	$headers = [];
	exec('curl -sI -L --max-time 30 ' . escapeshellarg($url) . ' 2>/dev/null', $headers, $code);
	$len = 0;
	foreach ($headers as $h) {
		if (preg_match('/^content-length:\\s*(\\d+)/i', trim((string) $h), $m)) {
			$len = (int) $m[1];
		}
	}
	return $len;
}

function gp_hardlink_or_copy(string $src, string $dst): void
{
	gp_mkdir(dirname($dst));
	// is_file() is false for broken symlinks; those still block link()/copy().
	if (is_link($dst) || file_exists($dst)) {
		if (is_file($dst) && !is_link($dst)) {
			return;
		}
		@unlink($dst);
	}
	if (@link($src, $dst)) {
		return;
	}
	if (!@copy($src, $dst)) {
		throw new RuntimeException("hardlink/copy failed: $src → $dst");
	}
}

/**
 * Extract archive into $destDir (created). Supports .tar, .tar.gz/.tgz, .tar.xz, .tar.bz2, .zip.
 * When $maxBytes > 0, stops once extracted payload reaches the budget (tar/zip member loop).
 */
function gp_extract(string $archive, string $destDir, int $maxBytes = 0): void
{
	gp_mkdir($destDir);
	$base = strtolower(basename($archive));
	if ($maxBytes > 0 && (str_ends_with($base, '.zip') || preg_match('/\\.tar(\\.(gz|xz|bz2))?$|\\.tgz$|\\.tbz2$/i', $base))) {
		gp_extract_budgeted($archive, $destDir, $maxBytes);
		return;
	}
	if (str_ends_with($base, '.zip')) {
		// Exit 1/2 = warnings / secondary errors; keep what unzip could extract (govdocs shards).
		$cmd = 'unzip -q -o ' . escapeshellarg($archive) . ' -d ' . escapeshellarg($destDir);
		$descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
		$p = proc_open(['bash', '-lc', $cmd], $descriptors, $pipes);
		if (!is_resource($p)) {
			throw new RuntimeException('unzip proc_open failed: ' . $archive);
		}
		fclose($pipes[0]);
		stream_get_contents($pipes[1]);
		$err = (string) stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$code = proc_close($p);
		if ($code > 2) {
			throw new RuntimeException("unzip failed ($code): $archive\n$err");
		}
		return;
	}
	if (preg_match('/\\.tar\\.gz$|\\.tgz$/i', $base)) {
		gp_run(['tar', '-xzf', $archive, '-C', $destDir]);
		return;
	}
	if (preg_match('/\\.tar\\.xz$/i', $base)) {
		gp_run(['tar', '-xJf', $archive, '-C', $destDir]);
		return;
	}
	if (preg_match('/\\.tar\\.bz2$|\\.tbz2$/i', $base)) {
		gp_run(['tar', '-xjf', $archive, '-C', $destDir]);
		return;
	}
	if (str_ends_with($base, '.tar')) {
		gp_run(['tar', '-xf', $archive, '-C', $destDir]);
		return;
	}
	if (str_ends_with($base, '.gz') && !str_contains($base, '.tar')) {
		$out = $destDir . DIRECTORY_SEPARATOR . preg_replace('/\\.gz$/i', '', basename($archive));
		gp_run(['bash', '-lc', 'gzip -dc ' . escapeshellarg($archive) . ' > ' . escapeshellarg($out)]);
		return;
	}
	if (str_ends_with($base, '.deb')) {
		gp_hardlink_or_copy($archive, $destDir . DIRECTORY_SEPARATOR . basename($archive));
		$unpack = $destDir . DIRECTORY_SEPARATOR . '_unpacked_' . preg_replace('/\\.deb$/i', '', basename($archive));
		gp_mkdir($unpack);
		if (gp_which('dpkg-deb') !== null) {
			gp_run(['dpkg-deb', '-x', $archive, $unpack]);
		} else {
			gp_run(['ar', 'x', $archive, '--output', $unpack]);
		}
		return;
	}
	gp_hardlink_or_copy($archive, $destDir . DIRECTORY_SEPARATOR . basename($archive));
}

/** Extract members until ~$maxBytes of regular files are written. */
function gp_extract_budgeted(string $archive, string $destDir, int $maxBytes): void
{
	gp_mkdir($destDir);
	$base = strtolower(basename($archive));
	$used = 0;
	if (str_ends_with($base, '.zip')) {
		$zip = new ZipArchive();
		if ($zip->open($archive) !== true) {
			throw new RuntimeException('ZipArchive open failed: ' . $archive);
		}
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$stat = $zip->statIndex($i);
			if ($stat === false) {
				continue;
			}
			$name = (string) $stat['name'];
			if ($name === '' || str_ends_with($name, '/')) {
				continue;
			}
			$size = (int) ($stat['size'] ?? 0);
			if ($size <= 0) {
				continue;
			}
			if ($used >= $maxBytes) {
				break;
			}
			$dest = $destDir . DIRECTORY_SEPARATOR . $name;
			gp_mkdir(dirname($dest));
			$in = $zip->getStream($name);
			if ($in === false) {
				continue;
			}
			$out = fopen($dest, 'wb');
			if ($out === false) {
				fclose($in);
				continue;
			}
			stream_copy_to_stream($in, $out);
			fclose($in);
			fclose($out);
			$used += $size;
		}
		$zip->close();
		return;
	}
	// tar family: checkpointed extract; stop when dest hits budget (avoids listing multi‑10 GiB tars).
	$xf = 'xf';
	if (preg_match('/\\.tar\\.gz$|\\.tgz$/i', $base)) {
		$xf = 'xzf';
	} elseif (preg_match('/\\.tar\\.xz$/i', $base)) {
		$xf = 'xJf';
	} elseif (preg_match('/\\.tar\\.bz2$|\\.tbz2$/i', $base)) {
		$xf = 'xjf';
	}
	$marker = $destDir . DIRECTORY_SEPARATOR . '.gp_budget_stop';
	@unlink($marker);
	$max = (int) $maxBytes;
	$cmd = 'tar -' . $xf . ' ' . escapeshellarg($archive) . ' -C ' . escapeshellarg($destDir)
		. ' --checkpoint=200 --checkpoint-action=exec=\''
		. 'python3 -c "import os,sys; root=sys.argv[1]; lim=int(sys.argv[2]); tot=0\n'
		. 'for dp,dns,fns in os.walk(root):\n'
		. '  for fn in fns:\n'
		. '    try: tot+=os.path.getsize(os.path.join(dp,fn))\n'
		. '    except OSError: pass\n'
		. 'if tot>=lim: open(os.path.join(root,\".gp_budget_stop\"),\"w\").close(); sys.exit(1)" '
		. escapeshellarg($destDir) . ' ' . (string) $max
		. '\'';
	exec($cmd . ' 2>/dev/null', $out, $code);
	@unlink($marker);
	// tar may exit non-zero when checkpoint exec fails — treat dest size as success criteria
	if (gp_dir_total_bytes($destDir) <= 0) {
		throw new RuntimeException('budgeted tar extract produced no files: ' . $archive);
	}
}

/**
 * Copy/hardlink files from $srcRoot into $dstRoot until $maxBytes, spread by size for diversity.
 * Skips common junk. Returns bytes placed.
 *
 * @param list<string>|null $extAllow lowercase extensions without dot; null = all
 */
function gp_select_into(string $srcRoot, string $dstRoot, int $maxBytes, ?array $extAllow = null): int
{
	gp_mkdir($dstRoot);
	$files = [];
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcRoot, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$path = $fi->getPathname();
		$name = $fi->getFilename();
		if (preg_match('/^(\\.|__MACOSX)/', $name)) {
			continue;
		}
		$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
		if ($extAllow !== null && $extAllow !== [] && !in_array($ext, $extAllow, true)) {
			continue;
		}
		$sz = (int) $fi->getSize();
		if ($sz <= 0) {
			continue;
		}
		$files[] = [$sz, $path];
	}
	usort($files, static fn ($a, $b) => $b[0] <=> $a[0]);
	// Spread: take every k-th after sorting so we don't only get the largest duplicates class
	$n = count($files);
	$order = [];
	if ($n > 0) {
		$step = max(1, (int) floor($n / max(1, min($n, 4000))));
		for ($off = 0; $off < $step; $off++) {
			for ($i = $off; $i < $n; $i += $step) {
				$order[] = $files[$i];
			}
		}
	}
	$used = 0;
	$srcRootReal = realpath($srcRoot) ?: $srcRoot;
	foreach ($order as [$sz, $path]) {
		if ($used >= $maxBytes) {
			break;
		}
		// Never exceed the remaining budget (previously the first member of a stage
		// could land even when larger than $maxBytes, e.g. a 3.5 GiB GGUF).
		if ($sz > $maxBytes - $used) {
			continue;
		}
		$real = realpath($path) ?: $path;
		$rel = ltrim(str_replace('\\', '/', substr($real, strlen($srcRootReal))), '/');
		if ($rel === '' || str_contains($rel, '..')) {
			$rel = basename($path);
		}
		$dest = $dstRoot . DIRECTORY_SEPARATOR . $rel;
		try {
			gp_hardlink_or_copy($path, $dest);
			$used += $sz;
		} catch (Throwable $e) {
			// Skip awkward members (symlink cycles, odd perms); keep filling the budget.
			continue;
		}
	}
	return $used;
}

/**
 * Place a single downloaded file into corpus (optionally extract).
 *
 * @param array{url:string,name?:string,sha256?:string,extract?:bool,ext_allow?:list<string>|null} $spec
 */
function gp_materialize_artifact(array $spec, string $cacheDir, string $stagingDir): string
{
	$name = $spec['name'] ?? basename(parse_url($spec['url'], PHP_URL_PATH) ?: 'blob.bin');
	$name = preg_replace('/[^A-Za-z0-9._+-]+/', '_', $name) ?: 'blob.bin';
	$cachePath = $cacheDir . DIRECTORY_SEPARATOR . $name;
	gp_download($spec['url'], $cachePath, $spec['sha256'] ?? null);
	$extract = $spec['extract'] ?? preg_match('/\\.(zip|tar\\.(gz|xz|bz2)|tgz|tbz2|deb)$/i', $name) === 1;
	$budget = (int) ($spec['extract_budget'] ?? 0);
	$stage = $stagingDir . DIRECTORY_SEPARATOR . pathinfo($name, PATHINFO_FILENAME);
	if ($extract) {
		if (!is_dir($stage) || !gp_dir_has_files($stage)) {
			gp_rrmdir($stage);
			gp_extract($cachePath, $stage, $budget);
		}
		return $stage;
	}
	gp_mkdir($stage);
	gp_hardlink_or_copy($cachePath, $stage . DIRECTORY_SEPARATOR . $name);
	return $stage;
}

function gp_dir_has_files(string $dir): bool
{
	if (!is_dir($dir)) {
		return false;
	}
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if ($fi->isFile()) {
			return true;
		}
	}
	return false;
}

function gp_dir_total_bytes(string $dir): int
{
	$total = 0;
	if (!is_dir($dir)) {
		return 0;
	}
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if ($fi->isFile()) {
			$total += (int) $fi->getSize();
		}
	}
	return $total;
}

/**
 * @param list<array<string,mixed>> $sources
 * @param array<string,mixed> $meta
 */
function gp_write_manifest(string $dstDir, array $meta, array $sources, int $totalBytes, int $fileCount): void
{
	$manifest = [
		'name' => $meta['name'] ?? basename($dstDir),
		'title' => $meta['title'] ?? '',
		'domain' => $meta['domain'] ?? '',
		'license_notes' => $meta['license_notes'] ?? 'See upstream URLs; public open data / FOSS.',
		'built_at' => gmdate('c'),
		'total_bytes' => $totalBytes,
		'file_count' => $fileCount,
		'sources' => $sources,
		'do_not_commit' => true,
	];
	file_put_contents(
		$dstDir . DIRECTORY_SEPARATOR . 'MANIFEST.json',
		json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
	);
	$readme = ($meta['title'] ?? basename($dstDir)) . "\n"
		. str_repeat('=', 48) . "\n\n"
		. ($meta['domain'] ?? '') . "\n\n"
		. "Public general-purpose compressor corpus. Materialize locally; do NOT commit this tree to git.\n"
		. "Rebuild: php benchmarks/build_test_files202_210_gp.php --only=" . preg_replace('/\\D+/', '', basename($dstDir)) . "\n\n"
		. "Sources:\n";
	foreach ($sources as $s) {
		$readme .= ' - ' . ($s['url'] ?? '') . "\n";
	}
	file_put_contents($dstDir . DIRECTORY_SEPARATOR . '00_README.txt', $readme);
}

function gp_count_files(string $dir): int
{
	$n = 0;
	if (!is_dir($dir)) {
		return 0;
	}
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if ($fi->isFile() && $fi->getFilename() !== 'MANIFEST.json' && $fi->getFilename() !== '00_README.txt') {
			$n++;
		}
	}
	return $n;
}
