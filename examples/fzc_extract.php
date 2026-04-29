<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';

/**
 * Example: extract all members from a `.fzc` archive (fractal_zip `open_container`).
 *
 * - **CLI:** `php fzc_extract.php <file.fzc> [output_directory]` — same behavior as before.
 * - **Web:** drag-and-drop or pick a `.fzc`, upload progress, then links under `web_jobs/{job}/staging/`. Download via `?job=&dl=staging/...`.
 *
 * Deploy with `fractal_zip.php` in the same directory as this script (or repo `examples/` with the library in the parent). Set `FRACTAL_ZIP_PHP` if needed.
 *
 * **Storage roadmap:** extracted paths can be registered in a future index while `.fzc` blobs
 * stay the canonical packed form (see `storage/`).
 */

$isCli = PHP_SAPI === 'cli';

if (!$isCli) {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_web_shared.php';
	list($handlerBase, $handlerPath) = fzc_web_handler_identity('fzc_extract.php');
	if (isset($_GET['job'], $_GET['dl']) && is_string($_GET['job']) && is_string($_GET['dl'])) {
		fzc_web_stream_download($handlerBase, $_GET['job'], $_GET['dl']);
		exit;
	}
	if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
		fzc_web_silence_php_errors_for_json_api();
		fzc_web_register_fatal_json_shutdown();
		fzc_web_load_fractal_zip();
		header('X-Content-Type-Options: nosniff');
		try {
			@set_time_limit(0);
			@ignore_user_abort(true);
			fzc_web_reject_body_too_large();
			if (!isset($_FILES['archive'])) {
				fzc_web_send_json(array('ok' => false, 'error' => 'No archive field: send multipart field `archive` with the .fzc file.'), 400);
			}
			$maxUp = fzc_web_max_upload_bytes();
			$reported = (int) ($_FILES['archive']['size'] ?? 0);
			if ($maxUp > 0 && $reported > $maxUp) {
				fzc_web_send_json(array(
					'ok' => false,
					'error' => 'Archive exceeds the ' . (string) $maxUp . '-byte web cap. Use the CLI, or on your own server set FZC_WEB_MAX_UPLOAD_BYTES (0 = disable) and raise PHP/nginx upload limits.',
				), 413);
			}
			$origName = (string) ($_FILES['archive']['name'] ?? '');
			$leaf = $origName !== '' ? basename(str_replace('\\', '/', $origName)) : 'archive.fzc';
			if (strlen($leaf) < 4 || strtolower(substr($leaf, -4)) !== '.fzc') {
				fzc_web_send_json(array('ok' => false, 'error' => 'File must have a .fzc extension.'), 400);
			}
			$jobId = fzc_web_new_job_dir();
			$jobRoot = fzc_web_jobs_root() . DIRECTORY_SEPARATOR . $jobId;
			$staging = $jobRoot . DIRECTORY_SEPARATOR . 'staging';
			if (!@mkdir($staging, 0755, true)) {
				throw new RuntimeException('Cannot create staging directory');
			}
			$containerPath = $staging . DIRECTORY_SEPARATOR . $leaf;
			$t0 = microtime(true);
			fzc_web_save_single_upload('archive', $containerPath);
			$tAfterUpload = microtime(true);
			if ($maxUp > 0) {
				$onDisk = filesize($containerPath);
				if (is_int($onDisk) && $onDisk > $maxUp) {
					fzc_web_remove_tree($jobRoot);
					fzc_web_send_json(array(
						'ok' => false,
						'error' => 'Archive exceeds the ' . (string) $maxUp . '-byte web cap. Use the CLI, or on your own server set FZC_WEB_MAX_UPLOAD_BYTES (0 = disable) and raise PHP/nginx upload limits.',
					), 413);
				}
			}
			$obBase = ob_get_level();
			ob_start();
			try {
				// Decode-only: disable auto segment / multipass *selection* probes (zip_folder); keep multipass decode semantics.
				$fz = new fractal_zip(null, true, false, null, false);
				$fz->open_container($containerPath, false);
			} finally {
				while (ob_get_level() > $obBase) {
					ob_end_clean();
				}
			}
			$tAfterOpen = microtime(true);
			$maxList = fzc_web_max_list_files();
			$post = fzc_web_extract_file_rows_after_open_container($fz, $staging, $handlerPath, $jobId, 'staging', $leaf, $maxList);
			$tAfterPost = microtime(true);
			$leafNorm = str_replace('\\', '/', $leaf);
			$files = array_values(array_filter($post['files'], static function (array $row) use ($leaf, $leafNorm): bool {
				$p = (string) ($row['member_path'] ?? $row['path'] ?? $row['rel'] ?? '');
				return $p !== $leafNorm && ($row['rel'] ?? '') !== $leaf;
			}));
			$listedCount = count($files);
			$truncated = $post['list_truncated'];
			$uploadMs = (int) round(($tAfterUpload - $t0) * 1000.0);
			$openMs = (int) round(($tAfterOpen - $tAfterUpload) * 1000.0);
			$postMs = (int) round(($tAfterPost - $tAfterOpen) * 1000.0);
			fzc_web_send_json(array(
				'ok' => true,
				'job' => $jobId,
				'archive' => $leaf,
				'files' => $files,
				'files_listed' => $listedCount,
				'files_truncated' => $truncated,
				'max_list_files' => $maxList,
				'safe_files_total' => $post['safe_files_total'],
				'unsafe_files_scrubbed' => $post['scrubbed'],
				'paths_from_container' => $post['paths_from_container'],
				'timing_ms' => array(
					'upload' => $uploadMs,
					'open_container' => $openMs,
					'postprocess' => $postMs,
					'total' => (int) round(($tAfterPost - $t0) * 1000.0),
				),
			));
		} catch (fzc_web_UnsafePathException $e) {
			fzc_web_send_json(array('ok' => false, 'error' => $e->getMessage()), 400);
		} catch (Throwable $e) {
			fzc_web_send_json(array('ok' => false, 'error' => $e->getMessage()), 500);
		}
		exit;
	}
	fzc_extract_render_web_ui($handlerBase, $handlerPath);
	exit;
}

$lib = getenv('FRACTAL_ZIP_PHP');
if ($lib === false || trim((string) $lib) === '') {
	$lib = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip.php';
}
if (!is_file($lib)) {
	$lib = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';
}
if (!is_file($lib)) {
	fwrite(STDERR, "Cannot find fractal_zip.php. Copy the library beside this script or set FRACTAL_ZIP_PHP.\n");
	exit(1);
}
require_once $lib;

$argvList = array_slice($argv, 1);
if ($argvList === [] || $argvList[0] === '-h' || $argvList[0] === '--help') {
	echo "Usage: php fzc_extract.php <file.fzc> [output_directory]\n";
	exit($argvList === [] ? 1 : 0);
}

$fzcIn = $argvList[0];
$fzcAbs = realpath($fzcIn);
if ($fzcAbs === false || !is_file($fzcAbs)) {
	fwrite(STDERR, "File not found: {$fzcIn}\n");
	exit(1);
}

$targetFzc = $fzcAbs;
if (isset($argvList[1]) && $argvList[1] !== '') {
	$outDir = $argvList[1];
	if (!is_dir($outDir)) {
		if (!@mkdir($outDir, 0755, true) && !is_dir($outDir)) {
			fwrite(STDERR, "Cannot create directory: {$outDir}\n");
			exit(1);
		}
	}
	$outAbs = realpath($outDir);
	if ($outAbs === false) {
		fwrite(STDERR, "Bad output directory: {$outDir}\n");
		exit(1);
	}
	$base = basename($fzcAbs);
	$targetFzc = $outAbs . DIRECTORY_SEPARATOR . $base;
	if (!@copy($fzcAbs, $targetFzc)) {
		fwrite(STDERR, "Copy failed to {$targetFzc}\n");
		exit(1);
	}
}

$verbose = getenv('FRACTAL_ZIP_CLI_VERBOSE') === '1';
if (!$verbose) {
	ob_start();
}
$fz = new fractal_zip(null, true, false, null, false);
$fz->open_container($targetFzc, false);
if (!$verbose) {
	ob_end_clean();
}

$root = dirname($targetFzc);
echo "Extracted members under {$root}\n";

/**
 * @param string $handlerBase basename of the PHP entry for footer text
 * @param string $handlerPath path from site root for POST and download links
 */
function fzc_extract_render_web_ui(string $handlerBase, string $handlerPath): void {
	header('Content-Type: text/html; charset=UTF-8');
	header('X-Content-Type-Options: nosniff');
	$h = htmlspecialchars($handlerBase, ENT_QUOTES, 'UTF-8');
	$postJson = json_encode($handlerPath, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
	$maxBytesJson = json_encode(fzc_web_max_upload_bytes(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
	echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="dark">
<title>Extract .fzc</title>
<style>
:root {
	color-scheme: dark;
	--bg: #0c0e12;
	--bg-elev: #161022;
	--border: #3b2f55;
	--text: #ece8f5;
	--muted: #a89bb8;
	--accent: #a78bfa;
	--accent-btn: #7c3aed;
	--link: #c4b5fd;
	--focus: #fbbf24;
	font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
	line-height: 1.5;
}
*, *::before, *::after { box-sizing: border-box; }
html { background: var(--bg); }
body {
	margin: 0;
	min-height: 100vh;
	background: var(--bg);
	color: var(--text);
	max-width: 44rem;
	margin-inline: auto;
	padding: 1.5rem 1rem 2.5rem;
}
h1 { font-size: 1.35rem; font-weight: 600; margin: 0 0 0.75rem; }
p.intro { color: var(--muted); margin: 0 0 1rem; }
.dropzone {
	border: 2px dashed var(--accent);
	border-radius: 12px;
	padding: 2rem 1.25rem;
	text-align: center;
	background: var(--bg-elev);
	color: var(--text);
	cursor: pointer;
	margin: 1rem 0;
	transition: border-color 0.15s, background 0.15s;
}
.dropzone:hover { border-color: #c4b5fd; }
.dropzone:focus { outline: 2px solid var(--focus); outline-offset: 2px; }
.dropzone.drag { border-color: #ddd6fe; background: #221830; }
.dropzone p { margin: 0.35rem 0; color: var(--muted); }
.dropzone strong { color: var(--text); }
button.primary {
	background: var(--accent-btn);
	color: #faf5ff;
	border: 0;
	padding: 0.6rem 1.2rem;
	border-radius: 8px;
	cursor: pointer;
	font-size: 1rem;
	font-weight: 600;
}
button.primary:hover { filter: brightness(1.1); }
button.primary:focus-visible { outline: 2px solid var(--focus); outline-offset: 2px; }
button.primary:disabled { opacity: 0.45; cursor: not-allowed; filter: none; }
.progress-block {
	margin: 1rem 0;
	padding: 0.75rem 0;
	border-top: 1px solid var(--border);
}
.progress-block[hidden] { display: none !important; }
.progress-block label {
	display: block;
	font-size: 0.8rem;
	text-transform: uppercase;
	letter-spacing: 0.04em;
	color: var(--muted);
	margin-bottom: 0.35rem;
}
.progress-block progress {
	width: 100%;
	height: 12px;
	border-radius: 6px;
	overflow: hidden;
	accent-color: var(--accent-btn);
}
.status { min-height: 1.5rem; margin: 0.75rem 0; font-size: 0.95rem; color: var(--muted); }
.status.error { color: #f0abfc; }
.filelist { margin: 0.75rem 0; padding-left: 0; list-style: none; max-height: 22rem; overflow: auto; }
.filelist li { margin: 0.5rem 0; padding: 0.35rem 0.5rem; border-radius: 6px; background: var(--bg-elev); border: 1px solid var(--border); }
/* Path must stay high-contrast: nested text inside <a> can inherit link color and look “missing” in some themes. */
.filelist a.filelist-dl {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	gap: 0.2rem;
	text-decoration: none;
	word-break: break-all;
	color: var(--muted);
}
.filelist a.filelist-dl:hover { text-decoration: none; }
.filelist a.filelist-dl:hover .dl-path-text { text-decoration: underline; color: var(--link); }
.filelist .dl-path-text {
	display: block;
	width: 100%;
	font-family: ui-monospace, "Cascadia Code", "Consolas", monospace;
	font-size: 0.95rem;
	font-weight: 500;
	line-height: 1.45;
	color: #ece8f5;
}
.filelist .dl-meta { font-size: 0.85rem; color: var(--muted); }
footer {
	margin-top: 2rem;
	font-size: 0.85rem;
	color: var(--muted);
	border-top: 1px solid var(--border);
	padding-top: 1rem;
}
footer strong { color: var(--text); }
code {
	background: var(--bg-elev);
	padding: 0.12em 0.4em;
	border-radius: 4px;
	border: 1px solid var(--border);
	font-size: 0.9em;
}
.visually-hidden { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
body.page-drop-active {
	box-shadow: inset 0 0 0 3px var(--accent);
	background: #141018;
	min-height: 100vh;
}
.warn-long {
	margin: 1rem 0;
	padding: 0.75rem 1rem;
	border-radius: 8px;
	border: 1px solid #b45309;
	background: rgba(180, 83, 9, 0.12);
	color: #fcd34d;
	font-size: 0.92rem;
}
.warn-long strong { color: #fde68a; }
.warn-long code { font-size: 0.88em; }
.info-flat-paths {
	margin: 0.75rem 0;
	padding: 0.65rem 0.9rem;
	border-radius: 8px;
	border: 1px solid #4c6fa8;
	background: rgba(76, 111, 168, 0.12);
	color: #bfdbfe;
	font-size: 0.92rem;
}
.info-flat-paths code { font-size: 0.88em; }
</style>
</head>
<body>
<h1>Extract <code>.fzc</code></h1>
<p class="intro">Drop a <code>.fzc</code> archive <strong>anywhere on this page</strong>, or click the zone below. Progress appears only while uploading and while the server is extracting. Each line shows the member’s <strong>path as stored in the archive</strong> (whatever was used when it was compressed, e.g. <code>includes/sidebar.php</code> or a single-level name like <code>sidebar.php</code>), not the server job folder. <strong>Web demo:</strong> archive size is capped (see footer); larger files need the CLI.</p>
<p class="warn-long" id="sizeWarn" hidden role="status"></p>
<div class="dropzone" id="dz" tabindex="0" role="button" aria-label="Drop .fzc here">
	<p><strong>Drop <code>.fzc</code> here</strong> (or anywhere on the page)</p>
	<p>or click to select</p>
</div>
<input type="file" id="pick" accept=".fzc,application/octet-stream" class="visually-hidden" aria-hidden="true" />
<p><button type="button" class="primary" id="go" disabled>Extract</button></p>
<div class="progress-block" id="uploadBlock" hidden>
	<label for="uploadBar">Upload progress</label>
	<progress id="uploadBar" value="0" max="100"></progress>
</div>
<div class="progress-block" id="workBlock" hidden>
	<label for="workBar">Server extracting</label>
	<progress id="workBar"></progress>
</div>
<p class="status" id="status" role="status"></p>
<div id="result"></div>
<footer>
	<p><strong>CLI unchanged:</strong> <code>php {$h} /path/to/archive.fzc</code></p>
	<p><strong>Limits:</strong> PHP must allow the upload (copy <code>examples/.user.ini</code> beside these scripts, use <code>examples/.htaccess</code> on Apache, or set <code>8M</code>/<code>16M</code> in <code>php.ini</code>; built-in server: <code>php -d upload_max_filesize=8M -d post_max_size=16M …</code>). This demo also rejects archives over <code>FZC_WEB_MAX_UPLOAD_BYTES</code> (default 8&nbsp;MiB; set to <code>0</code> to disable). Jobs use <code>web_jobs/</code> next to these scripts if writable, else a temp subfolder; set <code>FRACTAL_ZIP_WEB_JOBS</code> to override.</p>
</footer>
<script>
(function () {
	const dz = document.getElementById('dz');
	const pick = document.getElementById('pick');
	const go = document.getElementById('go');
	const uploadBar = document.getElementById('uploadBar');
	const workBar = document.getElementById('workBar');
	const uploadBlock = document.getElementById('uploadBlock');
	const workBlock = document.getElementById('workBlock');
	const status = document.getElementById('status');
	const result = document.getElementById('result');
	const sizeWarn = document.getElementById('sizeWarn');
	let file = null;

	const LONG_HINT_SEC = 120;
	const EXTRACT_WARN_BYTES = 3 * 1024 * 1024;
	const FZC_WEB_MAX_UPLOAD_BYTES = {$maxBytesJson};

	function human(n) { return n < 1024 ? n + ' B' : n < 1048576 ? (n/1024).toFixed(1) + ' KiB' : (n/1048576).toFixed(2) + ' MiB'; }

	function memberPathStringFlat(s) {
		const p = String(s || '');
		return p.indexOf('/') === -1 && p.indexOf('\\\\') === -1;
	}

	/** True when the server used container keys and every listed member path has no directory segments (typical of a flat pack). */
	function archiveMemberPathsLookFlat(files, pathsFromContainer) {
		if (!pathsFromContainer || !files || files.length < 2) {
			return false;
		}
		for (let i = 0; i < files.length; i++) {
			const row = files[i];
			const raw = row.member_path != null ? row.member_path : (row.path != null ? row.path : row.rel);
			if (!memberPathStringFlat(raw)) {
				return false;
			}
		}
		return true;
	}

	function extractFileOverDemoLimit(f) {
		return !!(f && FZC_WEB_MAX_UPLOAD_BYTES > 0 && (f.size || 0) > FZC_WEB_MAX_UPLOAD_BYTES);
	}

	function refreshExtractHeavyWarning() {
		if (!sizeWarn) return;
		if (file && (file.size || 0) >= EXTRACT_WARN_BYTES) {
			sizeWarn.hidden = false;
			sizeWarn.textContent = 'Large archive (' + human(file.size) + '): extraction may take well over ' + LONG_HINT_SEC + ' seconds. This script uses set_time_limit(0); your host may still cap time or upload size.';
		} else {
			sizeWarn.hidden = true;
			sizeWarn.textContent = '';
		}
	}

	function hideProgress() {
		uploadBlock.hidden = true;
		workBlock.hidden = true;
	}

	function parseJsonResponse(raw, xhr) {
		const t = (raw || '').trim();
		try { return JSON.parse(t); } catch (e1) {
			const start = t.indexOf('{');
			const end = t.lastIndexOf('}');
			if (start !== -1 && end > start) {
				try { return JSON.parse(t.slice(start, end + 1)); } catch (e2) { /* fall through */ }
			}
			const hint = t.length > 160 ? t.slice(0, 160) + '…' : t;
			const err = new Error('Server did not return JSON (HTTP ' + xhr.status + ').');
			err.hint = hint;
			throw err;
		}
	}

	dz.addEventListener('click', () => pick.click());

	function endPageDragVisual() {
		document.body.classList.remove('page-drop-active');
		dz.classList.remove('drag');
	}
	function dataTransferLooksLikeFiles(dt) {
		if (!dt || !dt.types) return true;
		const types = dt.types;
		if (types.length === 0) return true;
		for (let i = 0; i < types.length; i++) {
			const t = types[i];
			if (t === 'Files' || t === 'application/x-moz-file') return true;
		}
		if (typeof types.contains === 'function' && types.contains('Files')) return true;
		return false;
	}
	function ingestExtractDrop(dt) {
		if (!dt || !dt.files || !dt.files.length) return;
		let found = null;
		for (let i = 0; i < dt.files.length; i++) {
			if (/\.fzc$/i.test(dt.files[i].name)) {
				found = dt.files[i];
				break;
			}
		}
		if (found) {
			if (extractFileOverDemoLimit(found)) {
				file = null;
				go.disabled = true;
				status.classList.add('error');
				status.textContent = 'Archive exceeds the ' + FZC_WEB_MAX_UPLOAD_BYTES + '-byte web cap. Use the CLI for larger archives. On your own server, set FZC_WEB_MAX_UPLOAD_BYTES (e.g. 10485760 for 10 MiB, or 0 to disable) and raise PHP/nginx upload limits — see footer.';
				refreshExtractHeavyWarning();
				return;
			}
			file = found;
			go.disabled = false;
			status.textContent = 'Ready: ' + found.name;
			status.classList.remove('error');
			refreshExtractHeavyWarning();
		} else {
			file = null;
			status.classList.add('error');
			status.textContent = 'Need a .fzc file (none found in drop).';
			refreshExtractHeavyWarning();
		}
	}
	const useCapture = true;
	document.addEventListener('dragenter', (e) => {
		if (!dataTransferLooksLikeFiles(e.dataTransfer)) return;
		e.preventDefault();
		document.body.classList.add('page-drop-active');
	}, useCapture);
	document.addEventListener('dragover', (e) => {
		if (!dataTransferLooksLikeFiles(e.dataTransfer)) return;
		e.preventDefault();
		e.stopPropagation();
		if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy';
		if (dz.contains(e.target)) dz.classList.add('drag');
		else dz.classList.remove('drag');
	}, useCapture);
	document.addEventListener('drop', (e) => {
		e.preventDefault();
		e.stopPropagation();
		endPageDragVisual();
		ingestExtractDrop(e.dataTransfer);
	}, useCapture);
	document.addEventListener('dragend', endPageDragVisual, useCapture);
	pick.addEventListener('change', () => {
		const f = pick.files && pick.files[0];
		if (f && /\.fzc$/i.test(f.name)) {
			if (extractFileOverDemoLimit(f)) {
				file = null;
				go.disabled = true;
				status.classList.add('error');
				status.textContent = 'Archive exceeds the ' + FZC_WEB_MAX_UPLOAD_BYTES + '-byte web cap. Use the CLI for larger archives. On your own server, set FZC_WEB_MAX_UPLOAD_BYTES (e.g. 10485760 for 10 MiB, or 0 to disable) and raise PHP/nginx upload limits — see footer.';
			} else {
				file = f;
				go.disabled = false;
				status.textContent = 'Ready: ' + f.name;
				status.classList.remove('error');
			}
		} else { file = null; go.disabled = true; status.classList.toggle('error', !!f); status.textContent = f ? 'File must end in .fzc' : ''; }
		refreshExtractHeavyWarning();
	});

	go.addEventListener('click', () => {
		if (!file) return;
		if (extractFileOverDemoLimit(file)) {
			status.classList.add('error');
			status.textContent = 'Archive exceeds the ' + FZC_WEB_MAX_UPLOAD_BYTES + '-byte web cap. Use the CLI for larger archives. On your own server, set FZC_WEB_MAX_UPLOAD_BYTES (e.g. 10485760 for 10 MiB, or 0 to disable) and raise PHP/nginx upload limits — see footer.';
			return;
		}
		result.innerHTML = '';
		status.classList.remove('error');
		status.textContent = 'Uploading…';
		uploadBar.value = 0;
		workBar.removeAttribute('value');
		uploadBlock.hidden = false;
		workBlock.hidden = true;
		const fd = new FormData();
		fd.append('archive', file, file.name);
		const xhr = new XMLHttpRequest();
		let extractTick = null;
		function clearExtractTick() {
			if (extractTick !== null) {
				clearInterval(extractTick);
				extractTick = null;
			}
		}
		xhr.open('POST', {$postJson});
		xhr.timeout = 0;
		xhr.upload.onprogress = (e) => {
			if (e.lengthComputable) uploadBar.value = Math.round(100 * e.loaded / e.total);
		};
		xhr.upload.onload = () => {
			uploadBlock.hidden = true;
			workBlock.hidden = false;
			const t0 = Date.now();
			clearExtractTick();
			extractTick = setInterval(() => {
				if (xhr.readyState === 4) {
					clearExtractTick();
					return;
				}
				const sec = Math.floor((Date.now() - t0) / 1000);
				status.textContent = 'Extracting on server… ' + sec + 's (very large or FZCD archives can take several minutes)';
			}, 1000);
		};
		xhr.onload = () => {
			clearExtractTick();
			hideProgress();
			workBar.value = 1;
			if (xhr.status === 413) {
				status.classList.add('error');
				let msg = 'Upload too large for the server (PHP post_max_size / upload_max_filesize, or nginx client_max_body_size).';
				try {
					const j = parseJsonResponse(xhr.responseText, xhr);
					if (j && !j.ok && j.error) msg = j.error;
				} catch (e) { /* keep default */ }
				status.textContent = msg;
				return;
			}
			if (xhr.status === 502 || xhr.status === 504) {
				status.classList.add('error');
				status.textContent = 'Gateway timeout while extracting — try a smaller archive or raise proxy / PHP time limits.';
				return;
			}
			let j;
			try {
				j = parseJsonResponse(xhr.responseText, xhr);
			} catch (e) {
				status.classList.add('error');
				status.textContent = e.message + (e.hint ? ' Preview: ' + e.hint : '');
				return;
			}
			if (!j.ok) { status.classList.add('error'); status.textContent = j.error || 'Error'; return; }
			const listed = (typeof j.files_listed === 'number') ? j.files_listed : j.files.length;
			if (j.files_truncated) {
				status.textContent = 'Extracted archive ' + j.archive + '. Showing first ' + listed + ' member link(s); use the CLI or raise the list cap for the full tree.';
			} else {
				status.textContent = 'Extracted ' + listed + ' file(s) from ' + j.archive + '.';
			}
			if (archiveMemberPathsLookFlat(j.files, j.paths_from_container)) {
				const hint = document.createElement('p');
				hint.className = 'info-flat-paths';
				hint.setAttribute('role', 'status');
				hint.textContent = 'This archive stores every member at one level (file names only—no includes/ or styles/ prefixes). That is what is inside the .fzc; the extractor is not hiding folders. To keep a tree layout, compress the parent directory of your project again (web: folder picker on the site root; CLI: php fzc_compress.php /path/to/site).';
				result.appendChild(hint);
			}
			const ul = document.createElement('ul');
			ul.className = 'filelist';
			j.files.forEach(function (row) {
				const li = document.createElement('li');
				const a = document.createElement('a');
				a.className = 'filelist-dl';
				a.href = row.url;
				const raw = row.member_path != null ? row.member_path : (row.path != null ? row.path : row.rel);
				const pathInFzc = (raw != null && String(raw) !== '') ? String(raw) : '';
				const slash = pathInFzc.lastIndexOf('/');
				const baseName = slash >= 0 ? pathInFzc.slice(slash + 1) : pathInFzc;
				if (baseName) {
					a.setAttribute('download', baseName);
				}
				a.title = 'Member path in archive: ' + (pathInFzc || row.url) + ' — click to download';
				const pathEl = document.createElement('span');
				pathEl.className = 'dl-path-text';
				pathEl.textContent = pathInFzc || '(path missing — see tooltip / report bug)';
				a.appendChild(pathEl);
				const meta = document.createElement('span');
				meta.className = 'dl-meta';
				meta.textContent = human(row.size || 0);
				a.appendChild(meta);
				li.appendChild(a);
				ul.appendChild(li);
			});
			result.appendChild(ul);
		};
		xhr.onerror = () => {
			clearExtractTick();
			hideProgress();
			status.classList.add('error');
			status.textContent = 'Network error (connection dropped). Large uploads: increase post_max_size, upload_max_filesize, and web server body limits; allow long-running PHP and proxy timeouts.';
		};
		xhr.ontimeout = () => {
			clearExtractTick();
			hideProgress();
			status.classList.add('error');
			status.textContent = 'Request timed out in the browser — try a smaller file or raise timeouts.';
		};
		xhr.send(fd);
	});
})();
</script>
</body>
</html>
HTML;
}
