<?php
declare(strict_types=1);

/**
 * FZSX server extract API — unpack beside the .fzsx on disk (same directory as the archive).
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_deploy_bootstrap.php';
fzc_examples_require_bench_json_helpers();
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip_fzsx.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
	header('Content-Type: application/json; charset=UTF-8');
	http_response_code(405);
	echo bench_json_encode_try(array('ok' => false, 'error' => 'POST required'), false) ?: '{"ok":false}';
	exit;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_web_shared.php';
fzc_web_silence_php_errors_for_json_api();
fzc_web_register_fatal_json_shutdown();
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

try {
	@set_time_limit(0);
	fzc_web_require_optional_post_guard();
	fzc_web_load_fractal_zip();
	fzc_web_enforce_extract_compat_or_json();

	$action = isset($_POST['action']) ? (string) $_POST['action'] : 'extract';
	if ($action !== 'extract') {
		fzc_web_send_json(array('ok' => false, 'error' => 'Unknown action'), 400);
	}

	$examplesDir = __DIR__;
	$fzsxPath = null;

	$sourceRel = isset($_POST['source_rel']) ? trim(str_replace('\\', '/', (string) $_POST['source_rel'])) : '';
	if ($sourceRel !== '') {
		$fzsxPath = fractal_zip_fzsx::resolve_archive_path($examplesDir, $sourceRel);
	}

	if ($fzsxPath === null && isset($_POST['source'])) {
		$base = basename(str_replace('\\', '/', (string) $_POST['source']));
		if ($base !== '' && !str_contains($base, '..')
			&& (str_ends_with(strtolower($base), '.fzsx')
				|| str_ends_with(strtolower($base), '.fzsxsd')
				|| str_ends_with(strtolower($base), '.fzsd'))) {
			$candidate = $examplesDir . DIRECTORY_SEPARATOR . $base;
			if (is_file($candidate)) {
				$fzsxPath = realpath($candidate) ?: null;
			}
		}
	}

	if ($fzsxPath === null && isset($_FILES['fzsx']) && is_uploaded_file((string) ($_FILES['fzsx']['tmp_name'] ?? ''))) {
		$orig = basename(str_replace('\\', '/', (string) ($_FILES['fzsx']['name'] ?? 'upload.fzsx')));
		$lower = strtolower($orig);
		if ($orig === '' || (!str_ends_with($lower, '.fzsx') && !str_ends_with($lower, '.fzsxsd') && !str_ends_with($lower, '.fzsd'))) {
			fzc_web_send_json(array('ok' => false, 'error' => 'Upload must be a .fzsx, .fzsxsd, or .fzsd file'), 400);
		}
		$dest = $examplesDir . DIRECTORY_SEPARATOR . $orig;
		if (!move_uploaded_file((string) $_FILES['fzsx']['tmp_name'], $dest)) {
			fzc_web_send_json(array('ok' => false, 'error' => 'Could not save uploaded .fzsx'), 500);
		}
		$fzsxPath = realpath($dest) ?: null;
	}

	if ($fzsxPath === null) {
		fzc_web_send_json(array('ok' => false, 'error' => 'Missing or unknown source_rel / source (.fzsx/.fzsxsd/.fzsd not found under examples/)'), 400);
	}

	$readerClass = fractal_zip_fzsx::reader_class_for_path($fzsxPath);
	$res = $readerClass::extract_beside_self($fzsxPath, false);
	if (empty($res['ok'])) {
		fzc_web_send_json(array(
			'ok' => false,
			'error' => (string) ($res['error'] ?? 'extract failed'),
		), 500);
	}

	fzc_web_send_json(array(
		'ok' => true,
		'dest' => (string) ($res['dest'] ?? ''),
		'member_count' => (int) ($res['member_count'] ?? 0),
		'members' => $res['members'] ?? array(),
		'source' => str_replace('\\', '/', substr($fzsxPath, strlen(realpath($examplesDir) ?: $examplesDir) + 1)),
		'self_destructed' => !empty($res['self_destructed']),
	));
} catch (Throwable $e) {
	fzc_web_send_json(array('ok' => false, 'error' => $e->getMessage()), 500);
}
