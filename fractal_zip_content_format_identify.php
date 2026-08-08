<?php
declare(strict_types=1);

/**
 * fractal_zip shim — tiered identification lives in ../file_types/.
 */
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'file_types' . DIRECTORY_SEPARATOR . 'content_format_sniff_layout.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'file_types' . DIRECTORY_SEPARATOR . 'content_format_identify.php';

if (!class_exists('fractal_zip_content_format_identify', false)) {
	class_alias(file_types_content_format_identify::class, 'fractal_zip_content_format_identify');
}
