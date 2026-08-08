<?php
declare(strict_types=1);

/**
 * Production web limits + strict extract gate for freement.cloud (and similar hosts).
 *
 * Deploy: upload this file to the server as:
 *   examples/fz_fractal_local_env.php
 * (same directory as fzc_compress.php; that name is gitignored — copy/rename on upload.)
 *
 * Tool paths (zpaq, 7z, arc, …) are still set by fz_local_env_bootstrap.php and/or
 * setup_fractal_zip_extract_tools_env.sh — this file only sets caps + enforce flag.
 */
putenv('FZC_WEB_ENFORCE_EXTRACT_COMPAT=1');

/** Staged folder/file total for fzc_compress (32 MiB). Use 0 to disable. */
putenv('FZC_WEB_MAX_UPLOAD_BYTES=33554432');

/** Single .fz upload on fzc_extract (320 MiB). Unset would default to 10× upload cap. */
putenv('FZC_WEB_MAX_EXTRACT_ARCHIVE_BYTES=335544320');
