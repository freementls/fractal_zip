<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';

/**
 * Example: compress files/folders to `.fz` (fractal_zip).
 *
 * - **CLI:** `php fzc_compress.php <directory>` → one folder root; writes `<directory>.fz` beside it.
 * - **Web:** choose or drop a **folder** or **loose file(s)**; each part sends `fzc_relpath[]` plus `files[]` because many hosts basename `$_FILES['files']['name']` and would otherwise flatten the tree on disk before `zip_folder`. The suggested download `.fz` name is a **short (≈50 character) content label** built from file/folder names: long “catalog” filenames (e.g. `Title -- Author -- hash -- Anna’s Archive`) take the first substantive segment, drop hex blocks and source tails, then word-truncate. Multi-member trees prefer a non-generic folder or the longest file title. Optional `fzc_download_stem` on the final request overrides the suggested name.
 * - **Web:** drag-and-drop upload, progress, and download link (`?job=&dl=`). Needs a writable `web_jobs/` next to these scripts (or `FRACTAL_ZIP_WEB_JOBS`).
 *
 * Deploy: upload the full `examples/` folder including `bench_json_helpers.php`, `fzc_deploy_bootstrap.php`, `icons/`, and `favicon.ico`, plus parent `fractal_zip.php` and peers. Open `health.php` after upload to verify. Site-root `/favicon.ico` is separate (copy `examples/favicon.ico` to the vhost document root). Set `FRACTAL_ZIP_PHP` if the library lives elsewhere.
 *
 * **Storage roadmap:** a future storage layer can register produced `.fz` paths in an index (see `storage/`)
 * while still using this tool for ad-hoc packing.
 *
 * **Extract on the web:** outers chosen here (zpaq, 7z, arc, …) must exist on the extract server — `docs/WEB_LOCAL_PARITY.md`,
 * `examples/fzc_capability_report.php`, `scripts/fzc_parity_gate.sh --as-web`.
 */

$isCli = PHP_SAPI === 'cli';

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fzc_archive_naming.php';

if (!$isCli) {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_web_shared.php';
	list($handlerBase, $handlerPath) = fzc_web_handler_identity('fzc_compress.php');
	if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) === 'OPTIONS' && fzc_web_cors_is_enabled()) {
		fzc_web_maybe_send_cors_headers();
		http_response_code(204);
		exit;
	}
	if (isset($_GET['job'], $_GET['dl']) && is_string($_GET['job']) && is_string($_GET['dl'])) {
		fzc_web_stream_download($handlerBase, $_GET['job'], $_GET['dl']);
		exit;
	}
	if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
		fzc_web_silence_php_errors_for_json_api();
		fzc_web_register_fatal_json_shutdown();
		header('X-Content-Type-Options: nosniff');
		try {
			@set_time_limit(0);
			@ignore_user_abort(true);
			@ini_set('max_file_uploads', '2000');
			fzc_web_reject_body_too_large(fzc_web_max_upload_bytes());
			fzc_web_reject_truncated_multipart_post();
			fzc_web_require_optional_post_guard();
			fzc_web_load_fractal_zip();
			fzc_web_enforce_extract_compat_or_json();

			$chunked = isset($_POST['fzc_chunked']) && (string) $_POST['fzc_chunked'] === '1';
			$finalize = !$chunked || (isset($_POST['fzc_finalize']) && (string) $_POST['fzc_finalize'] === '1');

			$jobIdIn = isset($_POST['fzc_job']) ? (string) $_POST['fzc_job'] : '';
			if ($jobIdIn !== '' && !preg_match('/^[a-f0-9]{32}$/', $jobIdIn)) {
				fzc_web_send_json(array('ok' => false, 'error' => 'Invalid job id'), 400);
			}

			if ($jobIdIn === '') {
				$jobId = fzc_web_new_job_dir();
				$jobRoot = fzc_web_jobs_root() . DIRECTORY_SEPARATOR . $jobId;
				$staging = $jobRoot . DIRECTORY_SEPARATOR . 'staging';
				if (!@mkdir($staging, 0755, true)) {
					throw new RuntimeException('Cannot create staging directory');
				}
			} else {
				$jobId = $jobIdIn;
				$jobRoot = fzc_web_jobs_root() . DIRECTORY_SEPARATOR . $jobId;
				$staging = $jobRoot . DIRECTORY_SEPARATOR . 'staging';
				if (!is_dir($staging)) {
					fzc_web_send_json(array('ok' => false, 'error' => 'Unknown or expired job'), 400);
				}
			}

			$nf = isset($_FILES['files']) ? fzc_web_upload_files_field_count($_FILES['files']) : 0;
			$relList = null;
			if ($nf > 0) {
				if (!isset($_POST['fzc_relpath'])) {
					fzc_web_send_json(array(
						'ok' => false,
						'error' => 'Missing fzc_relpath[] (reload the compress page). Paths must be sent explicitly because some servers strip folders from upload filenames.',
					), 400);
				}
				$relList = fzc_web_post_string_list($_POST['fzc_relpath']);
				if (count($relList) !== $nf) {
					fzc_web_send_json(array(
						'ok' => false,
						'error' => 'fzc_relpath[] length must match the number of file parts in this request.',
					), 400);
				}
			}

			$saved = fzc_web_save_upload_tree($staging, $relList, $jobRoot);
			$filesStaged = fzc_web_count_files_under($staging);
			if ($nf > 0 && $saved === array() && $filesStaged === 0) {
				fzc_web_remove_tree($jobRoot);
				fzc_web_send_json(array(
					'ok' => false,
					'error' => 'File upload did not complete: ' . fzc_web_summarize_files_field_errors(isset($_FILES['files']) ? $_FILES['files'] : null) . '. Check PHP upload_max_filesize, post_max_size, disk space, and temp directory.',
				), 400);
			}
			fzc_web_enforce_staging_limit($jobRoot, $staging);

			if (!$finalize) {
				if ($saved === array()) {
					if ($jobIdIn === '') {
						fzc_web_remove_tree($jobRoot);
					}
					fzc_web_send_json(array('ok' => false, 'error' => 'No files in this batch'), 400);
				}
				fzc_web_send_json(array(
					'ok' => true,
					'partial' => true,
					'job' => $jobId,
					'batch_saved' => count($saved),
					'files_staged_total' => $filesStaged,
				));
				exit;
			}

			if ($filesStaged === 0) {
				fzc_web_remove_tree($jobRoot);
				fzc_web_send_json(array(
					'ok' => false,
					'error' => 'Nothing to compress — no files were stored. Use “Choose file(s)” or a folder, or run `php fzc_compress.php /path/to/folder` on the server. If uploads should work, check PHP post_max_size / upload_max_filesize.',
				), 400);
			}

			if ($saved === array() && $jobIdIn === '') {
				fzc_web_remove_tree($jobRoot);
				fzc_web_send_json(array('ok' => false, 'error' => 'No files received. Drag files or a folder, or use the file picker.'), 400);
			}

			$obBase = ob_get_level();
			ob_start();
			try {
				$fz = new fractal_zip(null, true, true, null, true);
				$fz->zip_folder($staging, false);
			} finally {
				while (ob_get_level() > $obBase) {
					ob_end_clean();
				}
			}
			$fzcSidecar = $staging . '.fz';
			if (!is_file($fzcSidecar)) {
				fzc_web_remove_tree($jobRoot);
				fzc_web_send_json(array('ok' => false, 'error' => 'Compressor did not write output. Check PHP memory/time limits.'), 500);
			}
			$parsed = fzc_web_relpath_read_job_manifest_parsed($jobRoot);
			$relsForName = (array) ($parsed['rels'] ?? array());
			$byClientName = (array) ($parsed['by_relname'] ?? array());
			if ($relsForName === array()) {
				$relsForName = fzc_compress_collect_staging_relpaths($staging);
			}
			/* One-shot upload (e.g. single PDF drag): this POST is the full job; use fzc_relpath + $_FILES names if manifest/walk were empty. */
			if ($relsForName === array() && $relList !== null && $filesStaged > 0 && count($relList) === $filesStaged) {
				$relsForName = array_values($relList);
				if ($byClientName === array() && isset($_FILES['files'])) {
					$byClientName = fzc_web_relpath_map_from_post_files($relsForName, $_FILES['files']);
				}
			}
			$archiveLeaf = fzc_compress_suggest_archive_leaf($relsForName, $byClientName);
			if ($finalize) {
				$ov = isset($_POST['fzc_download_stem']) ? (string) $_POST['fzc_download_stem'] : '';
				$archiveLeaf = fzc_compress_apply_download_stem_override($archiveLeaf, $ov);
			}
			$archivePath = $jobRoot . DIRECTORY_SEPARATOR . $archiveLeaf;
			if (!@rename($fzcSidecar, $archivePath)) {
				throw new RuntimeException('Could not finalize archive path');
			}
			fzc_web_remove_tree($staging);
			clearstatcache(true, $archivePath);
			$sz = filesize($archivePath);
			$dl = fzc_web_build_download_url($handlerPath, $jobId, $archiveLeaf);
			fzc_web_send_json(array(
				'ok' => true,
				'job' => $jobId,
				'bytes' => is_int($sz) ? $sz : 0,
				'download_url' => $dl,
				'download_filename' => $archiveLeaf,
				'files_packed' => $filesStaged,
			));
		} catch (fzc_web_UnsafePathException $e) {
			fzc_web_send_json(array('ok' => false, 'error' => $e->getMessage()), 400);
		} catch (Throwable $e) {
			fzc_web_send_json(array('ok' => false, 'error' => $e->getMessage()), 500);
		}
		exit;
	}
	fzc_compress_render_web_ui($handlerBase, $handlerPath);
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
	echo "Usage: php fzc_compress.php <directory>\nWrites <directory>.fz next to the folder.\n";
	exit($argvList === [] ? 1 : 0);
}

$dir = $argvList[0];
$abs = realpath($dir);
if ($abs === false || !is_dir($abs)) {
	fwrite(STDERR, "Not a directory: {$dir}\n");
	exit(1);
}

$fzcPath = $abs . '.fz';
$verbose = getenv('FRACTAL_ZIP_CLI_VERBOSE') === '1';
if (!$verbose) {
	ob_start();
}
$fz = new fractal_zip(null, true, true, null, true);
$fz->zip_folder($abs, false);
if (!$verbose) {
	ob_end_clean();
}

if (!is_file($fzcPath)) {
	fwrite(STDERR, "Expected output missing: {$fzcPath}\n");
	exit(1);
}
$sz = filesize($fzcPath);
echo 'Wrote ' . $fzcPath . ' (' . ($sz !== false ? (string) $sz : '?') . " bytes)\n";

/**
 * @param string $handlerBase basename of the PHP entry (e.g. fzc_compress.php) for links and footer
 * @param string $handlerPath path from site root for POST (may differ from browser URL when UI is .html)
 */
function fzc_compress_render_web_ui(string $handlerBase, string $handlerPath): void {
	header('Content-Type: text/html; charset=UTF-8');
	header('X-Content-Type-Options: nosniff');
	$h = htmlspecialchars($handlerBase, ENT_QUOTES, 'UTF-8');
	$postJson = bench_json_encode_try($handlerPath, false, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
	if ($postJson === null) {
		$postJson = '""';
	}
	$maxBytesJson = bench_json_encode_try(fzc_web_max_upload_bytes(), false, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
	if ($maxBytesJson === null) {
		$maxBytesJson = '0';
	}
	$faviconLinks = function_exists('fzc_web_render_favicon_links') ? fzc_web_render_favicon_links() : '';
	echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
{$faviconLinks}<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="dark">
<title>Compress to .fz</title>
<style>
:root {
	color-scheme: dark;
	--bg: #0c0e12;
	--bg-elev: #141820;
	--border: #2a3344;
	--text: #e8eaed;
	--muted: #9aa3b2;
	--accent: #38bdf8;
	--accent-dim: #0ea5e9;
	--link: #7dd3fc;
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
.dropzone:hover { border-color: var(--accent-dim); }
.dropzone:focus { outline: 2px solid var(--focus); outline-offset: 2px; }
.dropzone.drag { border-color: #22d3ee; background: #1a2332; }
.dropzone p { margin: 0.35rem 0; color: var(--muted); }
.dropzone strong { color: var(--text); }
button.primary {
	background: var(--accent-dim);
	color: #041018;
	border: 0;
	padding: 0.6rem 1.2rem;
	border-radius: 8px;
	cursor: pointer;
	font-size: 1rem;
	font-weight: 600;
}
button.primary:hover { filter: brightness(1.08); }
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
	accent-color: var(--accent-dim);
}
.status { min-height: 1.5rem; margin: 0.75rem 0; font-size: 0.95rem; color: var(--muted); }
.status.error { color: #f87171; }
.result { margin-top: 0.75rem; }
.result a { color: var(--link); word-break: break-all; }
.result a:hover { text-decoration: underline; }
.result code { font-size: 0.85em; }
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
	background: #12151c;
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
.compress-hint {
	margin: 0.25rem 0 0;
	font-size: 0.88rem;
	color: var(--muted);
	line-height: 1.45;
	max-width: 38rem;
}
.compress-hint kbd { font-family: inherit; font-size: 0.95em; color: var(--text); }
#workBlock[aria-busy="true"] label { color: var(--accent); }
</style>
</head>
<body>
<h1>Pack to <code>.fz</code></h1>
<p class="intro">Member paths inside the <code>.fz</code> are the relative paths you pack: from a <strong>folder</strong> (best — full tree), or for <strong>loose file(s)</strong> the archive uses each file’s name at the root (e.g. <code>products_export_1.csv</code>). <strong>Choose a folder</strong>, <strong>choose file(s)</strong>, or drop a folder / files below. Several top-level folders at once work best in Chromium (directory entries). <strong>Web demo:</strong> upload size cap in the footer; huge trees need the CLI.</p>
<p class="compress-hint" style="margin:0.5rem 0 0.75rem;max-width:40rem">After upload, <strong>building the <code>.fz</code> on the server</strong> can take <strong>several minutes</strong> for PDFs and other large files (you will see a timer and status line). This is expected, not a hang.</p>
<p class="warn-long" id="sizeWarn" hidden role="status"></p>
<div class="dropzone" id="dz" tabindex="0" role="button" aria-label="Choose folder or drop files">
	<p><strong>Click here</strong> to choose a <strong>folder</strong> (full paths)</p>
	<p>or drop a <strong>folder</strong> / <strong>file(s)</strong> anywhere on the page</p>
</div>
<input type="file" id="pickDir" webkitdirectory="" directory="" multiple class="visually-hidden" aria-hidden="true" title="Choose folder to pack" />
<input type="file" id="pickLoose" multiple class="visually-hidden" aria-hidden="true" title="Choose one or more files" />
<p><button type="button" class="primary" id="go" disabled>Compress to .fz</button></p>
<p class="optional-name" id="downloadStemRow" hidden style="margin:0.4rem 0 0;font-size:0.9rem;color:var(--muted)">
	<label for="fzcDownloadStem">Save download as (optional, without <code>.fz</code>):</label>
	<input type="text" id="fzcDownloadStem" autocomplete="off" maxlength="120" size="28" style="margin-left:0.35rem;padding:0.3rem 0.5rem;border-radius:6px;border:1px solid var(--border);background:var(--bg-elev);color:var(--text);font:inherit" />
</p>
<p style="margin:-0.25rem 0 0;font-size:0.9rem"><button type="button" id="btnPickLoose" style="background:transparent;border:1px solid var(--border);color:var(--link);padding:0.35rem 0.75rem;border-radius:6px;cursor:pointer;font:inherit">Choose file(s)…</button> <span style="color:var(--muted)">(single file or many — names at archive root)</span></p>
<div class="progress-block" id="uploadBlock" hidden>
	<label for="uploadBar">Upload progress</label>
	<progress id="uploadBar" value="0" max="100"></progress>
</div>
<div class="progress-block" id="workBlock" hidden aria-busy="false">
	<label for="workBar">Building your <code>.fz</code> on the server</label>
	<progress id="workBar" aria-label="Fractal compression in progress"></progress>
	<p class="compress-hint" id="compressHint" hidden>Fractal_zip is still running. For large PDFs or big trees this often takes <strong>several minutes</strong> — the bar cannot show inner steps, but work continues until a download link appears below. <kbd id="compressElapsed" hidden></kbd></p>
</div>
<p class="status" id="status" role="status"></p>
<div class="result" id="result"></div>
<footer>
	<p><strong>CLI unchanged:</strong> <code>php {$h} /path/to/folder</code></p>
	<p><strong>Jobs directory:</strong> uses <code>web_jobs/</code> next to these scripts if writable; otherwise a folder under the system temp directory. Override with env <code>FRACTAL_ZIP_WEB_JOBS</code> (absolute path). A catalogued storage layer can index <code>.fz</code> paths separately (see repo <code>storage/</code>).</p>
	<p><strong>Many files:</strong> PHP defaults to <code>max_file_uploads = 20</code>; this page uploads in batches of 15 so large trees are not truncated.</p>
	<p><strong>Upload cap (demo):</strong> staged total is capped by <code>FZC_WEB_MAX_UPLOAD_BYTES</code> (default 8&nbsp;MiB; set <code>0</code> to disable). PHP must allow at least that per file: use <code>examples/.user.ini</code> (FPM/CGI), <code>examples/.htaccess</code> (Apache mod_php), or <code>php -d upload_max_filesize=8M -d post_max_size=16M</code> for the built-in server; nginx needs <code>client_max_body_size</code> (at least <code>8m</code>) too.</p>
	<p><strong>Extract elsewhere:</strong> outers chosen here (zpaq, 7z, arc, …) must exist on the extract host — <a href="fzc_capability_report.php">capability report</a> · <code>docs/WEB_LOCAL_PARITY.md</code>.</p>
</footer>
<script>
(function () {
	const dz = document.getElementById('dz');
	const pickDir = document.getElementById('pickDir');
	const pickLoose = document.getElementById('pickLoose');
	const go = document.getElementById('go');
	const uploadBar = document.getElementById('uploadBar');
	const workBar = document.getElementById('workBar');
	const uploadBlock = document.getElementById('uploadBlock');
	const workBlock = document.getElementById('workBlock');
	const status = document.getElementById('status');
	const result = document.getElementById('result');
	const sizeWarn = document.getElementById('sizeWarn');
	const compressHint = document.getElementById('compressHint');
	const compressElapsed = document.getElementById('compressElapsed');
	const downloadStemRow = document.getElementById('downloadStemRow');
	const fzcDownloadStem = document.getElementById('fzcDownloadStem');
	let queue = [];
	let compressTickTimer = null;
	let compressTickStart = 0;

	const LONG_HINT_SEC = 120;
	/* Orange banner before compress: anything above this total size (or many files) gets the long‑job notice */
	const COMPRESS_WARN_BYTES = 400 * 1024;
	const COMPRESS_WARN_FILES = 400;
	const MAX_FILES_PER_POST = 15;
	const compressPostUrl = {$postJson};
	const FZC_WEB_MAX_UPLOAD_BYTES = {$maxBytesJson};

	function human(n) { return n < 1024 ? n + ' B' : n < 1048576 ? (n/1024).toFixed(1) + ' KiB' : (n/1048576).toFixed(2) + ' MiB'; }

	function formatServerError(j) {
		let msg = (j && j.error) ? String(j.error) : 'Error';
		const h = j && j.parity_hint;
		if (h && h.message) {
			msg += ' — ' + h.message;
			if (h.fix) msg += ' Fix: ' + h.fix;
		}
		return msg;
	}

	function refreshCompressHeavyWarning() {
		if (!sizeWarn) return;
		let total = 0;
		for (let i = 0; i < queue.length; i++) total += queue[i].size || 0;
		const heavy = total >= COMPRESS_WARN_BYTES || queue.length >= COMPRESS_WARN_FILES;
		if (heavy) {
			sizeWarn.hidden = false;
			sizeWarn.textContent = 'Larger job (' + human(total) + ', ' + queue.length + ' item(s)): after upload, the server can spend many minutes building a smaller .fz (literal-PAC, fractal search, outer codec). A timer and status line will show while that runs — it is not stuck. set_time_limit(0) is used; your host or proxy may still cap time or body size.';
		} else {
			sizeWarn.hidden = true;
			sizeWarn.textContent = '';
		}
		if (downloadStemRow) {
			downloadStemRow.hidden = queue.length === 0;
		}
	}

	function stopCompressReassurance() {
		if (compressTickTimer) {
			clearInterval(compressTickTimer);
			compressTickTimer = null;
		}
		compressTickStart = 0;
		if (workBlock) {
			workBlock.setAttribute('aria-busy', 'false');
		}
		if (compressHint) {
			compressHint.hidden = true;
		}
		if (compressElapsed) {
			compressElapsed.hidden = true;
			compressElapsed.textContent = '';
		}
	}

	function startCompressReassurance() {
		stopCompressReassurance();
		compressTickStart = Date.now();
		if (workBlock) {
			workBlock.setAttribute('aria-busy', 'true');
		}
		if (compressHint) {
			compressHint.hidden = false;
		}
		if (compressElapsed) {
			compressElapsed.hidden = false;
		}
		function tick() {
			const sec = Math.floor((Date.now() - compressTickStart) / 1000);
			if (status) {
				const m = Math.floor(sec / 60);
				const s = sec % 60;
				const timeStr = m > 0 ? m + 'm ' + s + 's' : sec + 's';
				status.textContent = 'Compressing on the server — still working (' + timeStr + '). Large PDFs and deep trees can take many minutes; this is normal.';
			}
			if (compressElapsed) {
				compressElapsed.textContent = 'Elapsed: ' + sec + 's';
			}
		}
		tick();
		compressTickTimer = setInterval(tick, 2000);
	}

	function hideProgress() {
		stopCompressReassurance();
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

	dz.addEventListener('click', () => pickDir.click());
	document.getElementById('btnPickLoose').addEventListener('click', () => pickLoose.click());
	pickLoose.addEventListener('change', () => {
		queue = assignLooseRelativePaths(pickLoose.files);
		go.disabled = queue.length === 0;
		status.textContent = queue.length ? queue.length + ' file(s) ready (member path = filename).' : '';
		status.classList.remove('error');
		refreshCompressHeavyWarning();
	});

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
	function readDirEntries(reader) {
		return new Promise((resolve, reject) => {
			const acc = [];
			function step() {
				reader.readEntries(batch => {
					if (batch.length === 0) return resolve(acc);
					acc.push.apply(acc, batch);
					step();
				}, reject);
			}
			step();
		});
	}
	async function walkEntry(entry, prefix) {
		if (entry.isFile) {
			return new Promise((resolve, reject) => {
				entry.file(file => {
					try {
						Object.defineProperty(file, 'webkitRelativePath', {
							value: prefix + file.name,
							configurable: true,
							enumerable: true,
						});
					} catch (e2) { /* ignore */ }
					resolve([file]);
				}, reject);
			});
		}
		if (entry.isDirectory) {
			const dirPath = prefix + entry.name + '/';
			const reader = entry.createReader();
			const entries = await readDirEntries(reader);
			const out = [];
			for (let j = 0; j < entries.length; j++) {
				out.push.apply(out, await walkEntry(entries[j], dirPath));
			}
			return out;
		}
		return [];
	}
	async function filesFromDataTransferItems(items) {
		/* Snapshot roots synchronously: after any await, DataTransferItem / entry handles can go stale
		   and webkitGetAsEntry() only works for the first dropped folder. */
		const roots = [];
		for (let i = 0; i < items.length; i++) {
			const it = items[i];
			if (typeof it.webkitGetAsEntry === 'function') {
				const entry = it.webkitGetAsEntry();
				if (entry) {
					roots.push({ kind: 'entry', entry });
					continue;
				}
			}
			if (it.kind === 'file') {
				const f = it.getAsFile();
				if (f) roots.push({ kind: 'file', file: f });
			}
		}
		const out = [];
		for (let r = 0; r < roots.length; r++) {
			const root = roots[r];
			if (root.kind === 'entry') {
				out.push.apply(out, await walkEntry(root.entry, ''));
			}
		}
		const loose = roots.filter(x => x.kind === 'file');
		for (let i = 0; i < loose.length; i++) {
			const f = loose[i].file;
			const rel = loose.length === 1 ? f.name : (i + '_' + f.name);
			try {
				Object.defineProperty(f, 'webkitRelativePath', {
					value: rel,
					configurable: true,
					enumerable: true,
				});
			} catch (e2) { /* ignore */ }
			out.push(f);
		}
		return out;
	}
	/** Every file must have webkitRelativePath (folder picker, folder drop, or synthetic path for loose file(s)). */
	function relativePathsOkForQueue(q) {
		if (!q || q.length === 0) {
			return false;
		}
		return q.every(f => typeof f.webkitRelativePath === 'string' && f.webkitRelativePath.length > 0);
	}
	/** Loose file(s): synthetic path = basename, or index_basename if many (avoids collisions). */
	function assignLooseRelativePaths(fileList) {
		const arr = Array.from(fileList);
		const out = [];
		for (let i = 0; i < arr.length; i++) {
			const f = arr[i];
			const rel = arr.length === 1 ? f.name : (i + '_' + f.name);
			try {
				Object.defineProperty(f, 'webkitRelativePath', {
					value: rel,
					configurable: true,
					enumerable: true,
				});
			} catch (e2) { /* ignore */ }
			out.push(f);
		}
		return out;
	}
	async function ingestCompressDrop(dt) {
		if (!dt) return;
		if (dt.items && dt.items.length) {
			const list = await filesFromDataTransferItems(dt.items);
			if (list.length) {
				if (!relativePathsOkForQueue(list)) {
					queue = [];
					go.disabled = true;
					status.classList.add('error');
					status.textContent = 'Could not read paths for this drop. Try choosing a folder or file(s) with the buttons below.';
					refreshCompressHeavyWarning();
					return;
				}
				queue = list;
				go.disabled = false;
				status.textContent = list.length + ' item(s) ready.';
				status.classList.remove('error');
				refreshCompressHeavyWarning();
				return;
			}
		}
		if (dt.files && dt.files.length) {
			let flat = Array.from(dt.files);
			if (!relativePathsOkForQueue(flat)) {
				flat = assignLooseRelativePaths(flat);
			}
			if (!relativePathsOkForQueue(flat)) {
				queue = [];
				go.disabled = true;
				status.classList.add('error');
				status.textContent = 'Could not assign paths for this drop. Use “Choose file(s)” or a folder.';
				refreshCompressHeavyWarning();
				return;
			}
			queue = flat;
			go.disabled = false;
			status.textContent = queue.length + ' item(s) ready.';
			status.classList.remove('error');
			refreshCompressHeavyWarning();
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
		(async () => {
			try {
				await ingestCompressDrop(e.dataTransfer);
			} catch (err) {
				status.classList.add('error');
				status.textContent = 'Could not read dropped folders: ' + (err && err.message ? err.message : String(err));
			}
		})();
	}, useCapture);
	document.addEventListener('dragend', endPageDragVisual, useCapture);
	pickDir.addEventListener('change', () => {
		queue = Array.from(pickDir.files);
		if (queue.length && !relativePathsOkForQueue(queue)) {
			queue = [];
			go.disabled = true;
			status.classList.add('error');
			status.textContent = 'Your browser did not supply relative paths for this folder. Try Chromium or another browser with folder upload support.';
			refreshCompressHeavyWarning();
			return;
		}
		go.disabled = queue.length === 0;
		status.textContent = queue.length ? queue.length + ' item(s) from folder (relative paths will be stored in the archive).' : '';
		status.classList.remove('error');
		refreshCompressHeavyWarning();
	});

	function handleCompressXhrHttpErrors(xhr) {
		if (xhr.status === 413) {
			status.classList.add('error');
			let msg = 'Upload too large for the server (PHP post_max_size / upload_max_filesize, or nginx client_max_body_size).';
			try {
				const j = parseJsonResponse(xhr.responseText, xhr);
				if (j && !j.ok) msg = formatServerError(j);
			} catch (e) { /* keep default */ }
			status.textContent = msg;
			return true;
		}
		if (xhr.status === 502 || xhr.status === 504) {
			status.classList.add('error');
			status.textContent = 'Gateway timeout while compressing — try a smaller tree or raise proxy / PHP time limits.';
			return true;
		}
		return false;
	}

	go.addEventListener('click', () => {
		if (!queue.length) return;
		if (!relativePathsOkForQueue(queue)) {
			status.classList.add('error');
			status.textContent = 'Each file needs a member path. Use “Choose folder”, “Choose file(s)”, or drop a folder.';
			return;
		}
		if (FZC_WEB_MAX_UPLOAD_BYTES > 0) {
			let sum = 0;
			for (let i = 0; i < queue.length; i++) sum += queue[i].size || 0;
			if (sum > FZC_WEB_MAX_UPLOAD_BYTES) {
				status.classList.add('error');
				status.textContent = 'Total selection exceeds the ' + FZC_WEB_MAX_UPLOAD_BYTES + '-byte web cap. Use the CLI for larger trees. On your own server, set FZC_WEB_MAX_UPLOAD_BYTES (e.g. 10485760 for 10 MiB, or 0 to disable) and raise PHP/nginx upload limits — see footer.';
				return;
			}
		}
		result.innerHTML = '';
		stopCompressReassurance();
		status.classList.remove('error');
		uploadBar.value = 0;
		workBar.removeAttribute('value');
		uploadBlock.hidden = false;
		workBlock.hidden = true;
		const totalFiles = queue.length;
		const nChunks = Math.ceil(totalFiles / MAX_FILES_PER_POST);
		status.textContent = 'Uploading batch 1 / ' + nChunks + ' (' + totalFiles + ' items)…';

		(async () => {
			let jobId = null;
			let jFinal = null;
			try {
				for (let c = 0; c < nChunks; c++) {
					const chunk = queue.slice(c * MAX_FILES_PER_POST, (c + 1) * MAX_FILES_PER_POST);
					const isLast = c === nChunks - 1;
					status.textContent = 'Uploading batch ' + (c + 1) + ' / ' + nChunks + ' (' + totalFiles + ' items)…';
					const fd = new FormData();
					fd.append('fzc_chunked', '1');
					if (jobId) fd.append('fzc_job', jobId);
					fd.append('fzc_finalize', isLast ? '1' : '0');
					if (isLast && fzcDownloadStem) {
						const s = fzcDownloadStem.value.trim();
						if (s) {
							fd.append('fzc_download_stem', s);
						}
					}
					for (const f of chunk) {
						fd.append('fzc_relpath[]', f.webkitRelativePath);
						/* Use basename for multipart filename; relative path is carried in fzc_relpath[] so PHP stacks
						   that basename() upload filenames still preserve the directory tree on disk. */
						fd.append('files[]', f, f.name);
					}
					const xhr = await new Promise((resolve, reject) => {
						const x = new XMLHttpRequest();
						x.open('POST', compressPostUrl);
						x.timeout = 0;
						x.upload.onprogress = (e) => {
							if (e.lengthComputable && nChunks > 0) {
								uploadBar.value = Math.min(99, Math.round(100 * ((c + e.loaded / e.total) / nChunks)));
							}
						};
						x.upload.onload = () => {
							if (isLast) {
								uploadBlock.hidden = true;
								workBlock.hidden = false;
								startCompressReassurance();
							}
						};
						x.onload = () => resolve(x);
						x.onerror = () => reject(new Error('Network error (connection dropped). Large uploads: increase post_max_size, upload_max_filesize, and web server body limits; allow long-running PHP and proxy timeouts.'));
						x.ontimeout = () => reject(new Error('Request timed out in the browser — try fewer/smaller files or raise timeouts.'));
						x.send(fd);
					});
					if (handleCompressXhrHttpErrors(xhr)) {
						hideProgress();
						return;
					}
					let j;
					try {
						j = parseJsonResponse(xhr.responseText, xhr);
					} catch (e) {
						hideProgress();
						status.classList.add('error');
						status.textContent = e.message + (e.hint ? ' Preview: ' + e.hint : '');
						return;
					}
					if (!j.ok) {
						hideProgress();
						status.classList.add('error');
						status.textContent = formatServerError(j);
						return;
					}
					if (j.partial) {
						jobId = j.job;
						status.textContent = 'Staged ' + j.files_staged_total + ' / ' + totalFiles + ' item(s)…';
						uploadBlock.hidden = false;
						workBlock.hidden = true;
					}
					if (isLast) jFinal = j;
				}
				hideProgress();
				workBar.value = 1;
				if (!jFinal || !jFinal.download_url) {
					status.classList.add('error');
					status.textContent = 'Unexpected response (no archive).';
					return;
				}
				status.textContent = 'Done — ' + jFinal.files_packed + ' item(s) packed, ' + human(jFinal.bytes) + '.';
				const leaf = (jFinal.download_filename && typeof jFinal.download_filename === 'string')
					? jFinal.download_filename
					: 'archive.fz';
				const a = document.createElement('a');
				a.href = jFinal.download_url;
				a.textContent = 'Download ' + leaf + ' (' + human(jFinal.bytes) + ')';
				a.setAttribute('download', leaf);
				result.appendChild(a);
				const p = document.createElement('p');
				p.innerHTML = '<small>Link: <code>' + jFinal.download_url + '</code></small>';
				result.appendChild(p);
			} catch (err) {
				hideProgress();
				status.classList.add('error');
				status.textContent = err.message || String(err);
			}
		})();
	});
})();
</script>
</body>
</html>
HTML;
}
