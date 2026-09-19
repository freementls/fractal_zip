/* SPDX-License-Identifier: MIT
 *
 * fzcodec — general-purpose buffer compressor.
 *
 * Sniff the payload, optionally transcode known formats (JPEG → Lepton),
 * then pick a codec by content class and preset. The wire is
 * self-describing so decode never depends on the encoder policy.
 *
 * This is not a list of Squash-corpus special cases. The same rules apply
 * to any buffer: printable-ratio classes, magic signatures, and size bands.
 */
#ifndef FZCODEC_H
#define FZCODEC_H

#include <stddef.h>
#include <stdint.h>

#ifdef __cplusplus
extern "C" {
#endif

#define FZCODEC_VERSION_MAJOR 1
#define FZCODEC_VERSION_MINOR 0
#define FZCODEC_VERSION_PATCH 0

#define FZ_HEADER_SIZE 16
#define FZ_MAGIC "FZC1"

typedef enum fz_preset {
	FZ_PRESET_LIFESTYLE = 0, /* default: strong bytes, bounded CPU */
	FZ_PRESET_ULTRA = 1      /* bytes-first; may run context mixers */
} fz_preset;

typedef enum fz_status {
	FZ_OK = 0,
	FZ_ERR_ARG = 1,
	FZ_ERR_NOMEM = 2,
	FZ_ERR_BUF = 3,
	FZ_ERR_DATA = 4,
	FZ_ERR_IO = 5,
	FZ_ERR_TOOL = 6,
	FZ_ERR_INTERNAL = 7
} fz_status;

typedef enum fz_class {
	FZ_CLASS_EMPTY = 0,
	FZ_CLASS_TINY,
	FZ_CLASS_JPEG,
	FZ_CLASS_PACKED,      /* already-compressed container / entropy */
	FZ_CLASS_TEXTLIKE,    /* ≥90% printable, almost no NULs */
	FZ_CLASS_HIGH_PRINT,  /* ≥75% printable (tables, mixed text) */
	FZ_CLASS_MID_PRINT,   /* ~28–40% printable (structured binaries) */
	FZ_CLASS_GENERAL
} fz_class;

typedef enum fz_codec_id {
	FZ_CODEC_STORE = 0,
	FZ_CODEC_ZSTD = 1,
	FZ_CODEC_BROTLI = 2,
	FZ_CODEC_ZLIB = 3,
	FZ_CODEC_BSC = 4,
	FZ_CODEC_ZPAQ = 5,
	FZ_CODEC_PAQ8PX = 6,
	FZ_CODEC_LEPTON = 7
} fz_codec_id;

/* Worst-case encoded size: store + header. Codecs that expand lose the race. */
size_t fz_max_compressed_size(size_t uncompressed_size);

/*
 * Compress src[0, src_len) into dst.
 * *dst_len is the destination capacity on entry and the used size on success.
 */
fz_status fz_compress(const void *src, size_t src_len,
                      void *dst, size_t *dst_len,
                      fz_preset preset);

/* Decode an FZC1 buffer. *dst_len is capacity on entry, used size on success. */
fz_status fz_decompress(const void *src, size_t src_len,
                        void *dst, size_t *dst_len);

int fz_is_compressed(const void *src, size_t src_len);

/* Uncompressed size from a valid header, or (uint64_t)-1 if invalid. */
uint64_t fz_uncompressed_size(const void *src, size_t src_len);

fz_class fz_sniff(const void *src, size_t src_len);
const char *fz_class_name(fz_class c);
const char *fz_codec_name(fz_codec_id id);
const char *fz_status_string(fz_status s);
const char *fz_preset_name(fz_preset p);

/* Winner of the last successful fz_compress on this thread (for tests/CLI). */
fz_codec_id fz_last_codec(void);
fz_class fz_last_class(void);

#ifdef __cplusplus
}
#endif

#endif /* FZCODEC_H */
