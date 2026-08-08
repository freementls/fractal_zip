<?php
declare(strict_types=1);

/**
 * Shared bench JSON helpers (loaded once via bench_json_helpers.php).
 *
 * @see bench_json_helpers.php
 */

if (!function_exists('bench_json_encode_options')) {
	/**
	 * @param int $extraFlags Extra json_encode bitmask bits OR’d after JSON_UNESCAPED_SLASHES.
	 */
	function bench_json_encode_options(bool $pretty, int $extraFlags = 0): int
	{
		$o = JSON_UNESCAPED_SLASHES | $extraFlags;
		if ($pretty) {
			$o |= JSON_PRETTY_PRINT;
		}
		if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
			$o |= JSON_INVALID_UTF8_SUBSTITUTE;
		}

		return $o;
	}
}

if (!function_exists('bench_json_encode_try')) {
	/**
	 * @param mixed $value
	 * @return string|null UTF-8 JSON string, or null on encode failure.
	 */
	function bench_json_encode_try($value, bool $pretty, int $extraFlags = 0): ?string
	{
		$js = json_encode($value, bench_json_encode_options($pretty, $extraFlags));

		return is_string($js) ? $js : null;
	}
}

if (!function_exists('bench_json_file_put')) {
	/**
	 * @param mixed $value
	 */
	function bench_json_file_put(string $path, $value, bool $pretty, string $context, int $extraFlags = 0): void
	{
		$js = bench_json_encode_try($value, $pretty, $extraFlags);
		if ($js === null) {
			fwrite(STDERR, '[bench] json_encode failed (' . $context . '): ' . json_last_error_msg() . "\n");

			return;
		}
		if (@file_put_contents($path, $js . "\n") === false) {
			fwrite(STDERR, '[bench] failed to write ' . $context . ': ' . $path . "\n");
		}
	}
}

if (!function_exists('bench_json_encode_fingerprint_try')) {
	/**
	 * @param mixed $value
	 * @return string|null Encoded JSON, or null on failure.
	 */
	function bench_json_encode_fingerprint_try($value, int $flags = JSON_UNESCAPED_SLASHES): ?string
	{
		$js = json_encode($value, $flags);

		return is_string($js) ? $js : null;
	}
}

if (!function_exists('bench_json_decode_assoc_try')) {
	/**
	 * @param int $depth Maximum nesting depth (json_decode third argument).
	 * @param int $flags Optional json_decode fourth-argument bitmask.
	 * @return array<int|string, mixed>|null
	 */
	function bench_json_decode_assoc_try(string $json, string $context, int $depth = 512, int $flags = 0): ?array
	{
		if ($depth < 1) {
			$depth = 512;
		}
		if (defined('JSON_THROW_ON_ERROR') && ($flags & JSON_THROW_ON_ERROR) !== 0) {
			fwrite(STDERR, '[bench] json_decode refused (' . $context . '): JSON_THROW_ON_ERROR is not supported here; use json_decode in try/catch' . "\n");

			return null;
		}
		$data = json_decode($json, true, $depth, $flags);
		if (json_last_error() !== JSON_ERROR_NONE) {
			fwrite(STDERR, '[bench] json_decode failed (' . $context . '): ' . json_last_error_msg() . "\n");

			return null;
		}
		if (!is_array($data)) {
			fwrite(STDERR, '[bench] json_decode root not array (' . $context . ")\n");

			return null;
		}

		return $data;
	}
}

if (!function_exists('bench_json_decode_file_assoc_try')) {
	/**
	 * @return array<int|string, mixed>|null
	 */
	function bench_json_decode_file_assoc_try(string $path, string $context, int $depth = 512, int $flags = 0, bool $suppressReadErrors = true, bool $skipJsonDecodeIfEmptyRaw = false): ?array
	{
		$raw = $suppressReadErrors
			? (string) @file_get_contents($path)
			: (string) file_get_contents($path);

		if ($skipJsonDecodeIfEmptyRaw && $raw === '') {
			return null;
		}

		return bench_json_decode_assoc_try($raw, $context . ' ' . $path, $depth, $flags);
	}
}
