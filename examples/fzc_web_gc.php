#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * One-shot job GC for fzc_compress / fzc_extract web_jobs (cron-friendly).
 *
 *   php examples/fzc_web_gc.php
 *   FZC_WEB_JOB_MAX_AGE_SEC=3600 php examples/fzc_web_gc.php
 *
 * Uses the same jobs root resolution as fzc_web_shared.php (FRACTAL_ZIP_WEB_JOBS, etc.).
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_web_shared.php';

$ageEnv = getenv('FZC_WEB_JOB_MAX_AGE_SEC');
$age = (int) (($ageEnv !== false && trim((string) $ageEnv) !== '') ? $ageEnv : 86400);
$n = fzc_web_gc_jobs_older_than($age);
fwrite(STDOUT, "fzc_web_gc: removed {$n} job director(y|ies) older than {$age}s\n");
exit(0);
