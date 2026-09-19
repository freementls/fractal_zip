/* SPDX-License-Identifier: MIT
 *
 * Squash plugin wrapping libfzcodec.
 *
 * Codecs:
 *   fz-lifestyle  — default, content-aware, speed-aware
 *   fz-ultra      — same pipeline, bytes-first (may invoke paq8px)
 *
 * Buffer API only. Thread-safe: each call uses private heap + mkdtemp.
 */
#include <stddef.h>
#include <stdint.h>
#include <string.h>

#include <squash.h>
#include <fzcodec.h>

SQUASH_PLUGIN_EXPORT
SquashStatus squash_plugin_init_codec(SquashCodec *codec, SquashCodecImpl *impl);

static size_t
plugin_max_compressed_size(SquashCodec *codec, size_t uncompressed_size)
{
	(void)codec;
	return fz_max_compressed_size(uncompressed_size);
}

static size_t
plugin_uncompressed_size(SquashCodec *codec,
                         size_t compressed_size,
                         const uint8_t compressed[])
{
	(void)codec;
	uint64_t n = fz_uncompressed_size(compressed, compressed_size);
	if (n == (uint64_t)-1 || n > SIZE_MAX) {
		return 0;
	}
	return (size_t)n;
}

static SquashStatus
plugin_map_status(fz_status st)
{
	switch (st) {
	case FZ_OK:
		return SQUASH_OK;
	case FZ_ERR_NOMEM:
		return squash_error(SQUASH_MEMORY);
	case FZ_ERR_BUF:
		return squash_error(SQUASH_BUFFER_FULL);
	case FZ_ERR_ARG:
		return squash_error(SQUASH_BAD_PARAM);
	case FZ_ERR_DATA:
	case FZ_ERR_IO:
	case FZ_ERR_TOOL:
	case FZ_ERR_INTERNAL:
		return squash_error(SQUASH_FAILED);
	}
	return squash_error(SQUASH_FAILED);
}

static fz_preset
preset_for_codec(SquashCodec *codec)
{
	const char *name = squash_codec_get_name(codec);
	if (name != NULL && strcmp(name, "fz-ultra") == 0) {
		return FZ_PRESET_ULTRA;
	}
	return FZ_PRESET_LIFESTYLE;
}

static SquashStatus
plugin_compress_buffer(SquashCodec *codec,
                       size_t *compressed_size,
                       uint8_t compressed[],
                       size_t uncompressed_size,
                       const uint8_t uncompressed[],
                       SquashOptions *options)
{
	(void)options;
	fz_status st = fz_compress(uncompressed, uncompressed_size,
	                           compressed, compressed_size,
	                           preset_for_codec(codec));
	return plugin_map_status(st);
}

static SquashStatus
plugin_decompress_buffer(SquashCodec *codec,
                         size_t *decompressed_size,
                         uint8_t decompressed[],
                         size_t compressed_size,
                         const uint8_t compressed[],
                         SquashOptions *options)
{
	(void)codec;
	(void)options;
	fz_status st = fz_decompress(compressed, compressed_size,
	                             decompressed, decompressed_size);
	return plugin_map_status(st);
}

SquashStatus
squash_plugin_init_codec(SquashCodec *codec, SquashCodecImpl *impl)
{
	const char *name = squash_codec_get_name(codec);
	if (name == NULL) {
		return squash_error(SQUASH_UNABLE_TO_LOAD);
	}
	if (strcmp(name, "fz-lifestyle") != 0 && strcmp(name, "fz-ultra") != 0) {
		return squash_error(SQUASH_UNABLE_TO_LOAD);
	}

	impl->get_max_compressed_size = plugin_max_compressed_size;
	impl->get_uncompressed_size = plugin_uncompressed_size;
	impl->compress_buffer = plugin_compress_buffer;
	impl->decompress_buffer = plugin_decompress_buffer;
	return SQUASH_OK;
}
