<?php
declare(strict_types=1);

/**
 * Backward-compatible wrappers — canonical implementation lives in /srv/http/peel.
 */

$__peelBridge = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'peel' . DIRECTORY_SEPARATOR . 'peel.php';
if (is_readable($__peelBridge)) {
	require_once $__peelBridge;
}

function fractal_zip_convert_decode_varint_u32(string $buf, int &$off, int $n, string $ctx): int
{
	return peel_convert_decode_varint_u32($buf, $off, $n, $ctx);
}

function fractal_zip_convert_varint_helpers_ready(): void
{
	peel_convert_varint_helpers_ready();
}

function fractal_zip_convert_root(): ?string
{
	return peel_convert_root();
}

function fractal_zip_convert_ensure_loaded(): bool
{
	return peel_convert_ensure_loaded();
}

function fractal_zip_convert_env_on(string $name): bool
{
	return peel_convert_env_on($name);
}

function fractal_zip_convert_png_to_bmp_enabled(): bool
{
	return peel_convert_env_on('FRACTAL_ZIP_CONVERT_PNG_TO_BMP');
}

function fractal_zip_convert_png_to_bmp_bytes(string $relPath, string $pngBytes): ?string
{
	return peel_convert_png_to_bmp_bytes($relPath, $pngBytes);
}

function fractal_zip_convert_bmp_to_png_bytes(string $origPngBasename, string $bmpBytes): ?string
{
	return peel_convert_bmp_to_png_bytes($origPngBasename, $bmpBytes);
}

function fractal_zip_convert_encode_mode28(string $origPngBasename, int $innerMode, string $innerStore): ?string
{
	return peel_convert_encode_mode28($origPngBasename, $innerMode, $innerStore);
}

function fractal_zip_convert_decode_mode28(string $rawStored, callable $decodeInner): ?string
{
	return peel_convert_decode_mode28($rawStored, $decodeInner);
}
