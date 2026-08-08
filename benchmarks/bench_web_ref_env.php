<?php
declare(strict_types=1);

/**
 * Web-ref track env defaults (separate from world-record preset).
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

function bench_web_ref_putenv_if_unset(string $k, string $v): void
{
	$e = getenv($k);
	if ($e === false || trim((string) $e) === '') {
		putenv($k . '=' . $v);
	}
}

function bench_web_ref_apply_env_defaults(): void
{
	bench_world_record_apply_env_defaults();
	bench_web_ref_putenv_if_unset('FRACTAL_ZIP_WEB_REF', '1');
	bench_web_ref_putenv_if_unset('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE', '1');
	bench_web_ref_putenv_if_unset('FRACTAL_ZIP_WEB_REF_URL_LITERAL', '0');
	bench_web_ref_putenv_if_unset('FRACTAL_ZIP_WEB_REF_CORPUS_PIECES', '1');
	bench_web_ref_putenv_if_unset('FRACTAL_ZIP_WEB_REF_WAYBACK', '1');
	bench_web_ref_putenv_if_unset('FRACTAL_ZIP_WEB_REF_PROBE_MAX_CHUNKS', '30');
	bench_web_ref_putenv_if_unset('FRACTAL_ZIP_WEB_REF_PROBE_CORPUS_RESERVE', '15');
	bench_web_ref_putenv_if_unset('FRACTAL_ZIP_WEB_REF_CORPUS_MIN_GAIN', '32');
	bench_web_ref_putenv_if_unset('FRACTAL_ZIP_WEB_REF_PROBE_MAX_URLS', '40');
	bench_web_ref_putenv_if_unset('FRACTAL_ZIP_WEB_REF_PROBE_WIKI_PAGES', '40');
	bench_web_ref_putenv_if_unset('FRACTAL_ZIP_WEB_REF_URL_LITERAL_MIN_GAIN', '16');
	bench_web_ref_putenv_if_unset('FRACTAL_ZIP_WEB_REF_MIN_STABILITY', '40');
	putenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR=0');
}

function bench_web_ref_apply_probe_fast_defaults(): void
{
	bench_web_ref_apply_env_defaults();
	putenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR=1');
}
