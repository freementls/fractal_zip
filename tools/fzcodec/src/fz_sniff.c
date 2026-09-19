#include "fz_internal.h"

void fz_stats_sample(const uint8_t *p, size_t n, size_t target, struct fz_stats *out)
{
	memset(out, 0, sizeof(*out));
	if (p == NULL || n == 0 || target == 0) {
		return;
	}
	size_t step = n / target;
	if (step < 1) {
		step = 1;
	}
	size_t printable = 0;
	size_t zeros = 0;
	size_t sn = 0;
	for (size_t i = 0; i < n; i += step) {
		unsigned b = p[i];
		if (b == 0) {
			zeros++;
		}
		if (fz_printable_byte(b)) {
			printable++;
		}
		sn++;
	}
	out->samples = sn;
	if (sn > 0) {
		out->printable = (double)printable / (double)sn;
		out->zeros = (double)zeros / (double)sn;
	}
}

int fz_is_jpeg(const uint8_t *p, size_t n)
{
	return n >= 3 && p[0] == 0xff && p[1] == 0xd8 && p[2] == 0xff;
}

int fz_is_packed_magic(const uint8_t *p, size_t n)
{
	if (p == NULL || n < 2) {
		return 0;
	}
	/* gzip */
	if (p[0] == 0x1f && p[1] == 0x8b) {
		return 1;
	}
	/* zlib deflate (common header bytes) — skip; too many false positives */
	if (n >= 4 && p[0] == 0x28 && p[1] == 0xb5 && p[2] == 0x2f && p[3] == 0xfd) {
		return 1; /* zstd */
	}
	if (n >= 6 && p[0] == 0xfd && p[1] == 0x37 && p[2] == 0x7a &&
	    p[3] == 0x58 && p[4] == 0x5a && p[5] == 0x00) {
		return 1; /* xz */
	}
	if (n >= 3 && p[0] == 0x42 && p[1] == 0x5a && p[2] == 0x68) {
		return 1; /* bzip2 */
	}
	if (n >= 4 && p[0] == 0x50 && p[1] == 0x4b &&
	    (p[2] == 0x03 || p[2] == 0x05 || p[2] == 0x07) &&
	    (p[3] == 0x04 || p[3] == 0x06 || p[3] == 0x08)) {
		return 1; /* zip */
	}
	if (n >= 6 && p[0] == 0x37 && p[1] == 0x7a && p[2] == 0xbc &&
	    p[3] == 0xaf && p[4] == 0x27 && p[5] == 0x1c) {
		return 1; /* 7z */
	}
	if (n >= 4 && p[0] == 0x52 && p[1] == 0x61 && p[2] == 0x72 && p[3] == 0x21) {
		return 1; /* rar */
	}
	if (n >= 8 && memcmp(p, "\x89PNG\r\n\x1a\n", 8) == 0) {
		return 1;
	}
	if (n >= 12 && memcmp(p, "RIFF", 4) == 0 && memcmp(p + 8, "WEBP", 4) == 0) {
		return 1;
	}
	if (n >= 4 && memcmp(p, "fLaC", 4) == 0) {
		return 1;
	}
	if (n >= 3 && p[0] == 0x49 && p[1] == 0x44 && p[2] == 0x33) {
		return 1; /* id3 / mp3 */
	}
	if (n >= 2 && p[0] == 0xff && (p[1] & 0xe0) == 0xe0 && !fz_is_jpeg(p, n)) {
		/* MPEG ADTS — weak; only if it also looks packed by entropy later */
	}
	if (n >= 4 && (memcmp(p, "OggS", 4) == 0 || memcmp(p, "wOFF", 4) == 0)) {
		return 1;
	}
	if (n >= 12 && memcmp(p + 4, "ftyp", 4) == 0) {
		return 1; /* mp4 / heif family */
	}
	if (n >= 4 && memcmp(p, "BR\xd2\x00", 4) == 0) {
		return 1; /* brotli raw? uncommon */
	}
	if (n >= 4 && memcmp(p, FZ_MAGIC, 4) == 0) {
		return 1; /* already FZC1 */
	}
	return 0;
}

fz_class fz_sniff(const void *src, size_t src_len)
{
	const uint8_t *p = (const uint8_t *)src;
	if (src_len == 0 || p == NULL) {
		return FZ_CLASS_EMPTY;
	}
	if (fz_is_jpeg(p, src_len)) {
		return FZ_CLASS_JPEG;
	}
	if (fz_is_packed_magic(p, src_len)) {
		return FZ_CLASS_PACKED;
	}
	if (src_len <= FZ_TINY_MAX) {
		return FZ_CLASS_TINY;
	}

	struct fz_stats text;
	fz_stats_sample(p, src_len, FZ_SAMPLE_TEXT_TARGET, &text);
	if (src_len < FZ_TEXTLIKE_MIN_BYTES) {
		/* Same gate as the PHP encoder: too small to reject brotli. */
		if (text.printable >= FZ_TEXTLIKE_MIN_RATIO && text.zeros <= FZ_TEXTLIKE_MAX_ZERO) {
			return FZ_CLASS_TEXTLIKE;
		}
		return FZ_CLASS_GENERAL;
	}
	if (text.samples >= 512 &&
	    text.printable >= FZ_TEXTLIKE_MIN_RATIO &&
	    text.zeros <= FZ_TEXTLIKE_MAX_ZERO) {
		return FZ_CLASS_TEXTLIKE;
	}

	struct fz_stats wide;
	fz_stats_sample(p, src_len, FZ_SAMPLE_PRINT_TARGET, &wide);
	if (wide.samples >= 512) {
		if (wide.printable >= FZ_HIGH_PRINT_RATIO) {
			return FZ_CLASS_HIGH_PRINT;
		}
		if (wide.printable >= FZ_MID_PRINT_LO && wide.printable <= FZ_MID_PRINT_HI) {
			return FZ_CLASS_MID_PRINT;
		}
	}
	return FZ_CLASS_GENERAL;
}

const char *fz_class_name(fz_class c)
{
	switch (c) {
	case FZ_CLASS_EMPTY:
		return "empty";
	case FZ_CLASS_TINY:
		return "tiny";
	case FZ_CLASS_JPEG:
		return "jpeg";
	case FZ_CLASS_PACKED:
		return "packed";
	case FZ_CLASS_TEXTLIKE:
		return "textlike";
	case FZ_CLASS_HIGH_PRINT:
		return "high_print";
	case FZ_CLASS_MID_PRINT:
		return "mid_print";
	case FZ_CLASS_GENERAL:
	default:
		return "general";
	}
}

const char *fz_codec_name(fz_codec_id id)
{
	switch (id) {
	case FZ_CODEC_STORE:
		return "store";
	case FZ_CODEC_ZSTD:
		return "zstd";
	case FZ_CODEC_BROTLI:
		return "brotli";
	case FZ_CODEC_ZLIB:
		return "zlib";
	case FZ_CODEC_BSC:
		return "bsc";
	case FZ_CODEC_ZPAQ:
		return "zpaq";
	case FZ_CODEC_PAQ8PX:
		return "paq8px";
	case FZ_CODEC_LEPTON:
		return "lepton";
	default:
		return "unknown";
	}
}

const char *fz_preset_name(fz_preset p)
{
	return p == FZ_PRESET_ULTRA ? "ultra" : "lifestyle";
}

const char *fz_status_string(fz_status s)
{
	switch (s) {
	case FZ_OK:
		return "ok";
	case FZ_ERR_ARG:
		return "invalid argument";
	case FZ_ERR_NOMEM:
		return "out of memory";
	case FZ_ERR_BUF:
		return "buffer too small";
	case FZ_ERR_DATA:
		return "corrupt or unsupported data";
	case FZ_ERR_IO:
		return "i/o error";
	case FZ_ERR_TOOL:
		return "external tool failed";
	case FZ_ERR_INTERNAL:
	default:
		return "internal error";
	}
}
