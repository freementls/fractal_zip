#ifndef FZ_INTERNAL_H
#define FZ_INTERNAL_H

#include "fzcodec.h"

#ifdef __cplusplus
extern "C" {
#endif

#include <errno.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#define FZ_SAMPLE_PRINT_TARGET 65536u
#define FZ_SAMPLE_TEXT_TARGET 4096u
#define FZ_TEXTLIKE_MIN_BYTES 4096u
#define FZ_TEXTLIKE_MIN_RATIO 0.90
#define FZ_TEXTLIKE_MAX_ZERO 0.01
#define FZ_HIGH_PRINT_RATIO 0.75
#define FZ_MID_PRINT_LO 0.28
#define FZ_MID_PRINT_HI 0.40
#define FZ_TINY_MAX 255u

#define FZ_MIB (1048576ull)
#define FZ_TEXTLIKE_BSC_MIN (16ull * FZ_MIB)
#define FZ_TEXTLIKE_BSC_MAX (64ull * FZ_MIB)
#define FZ_LIFESTYLE_PAQ_DEFAULT (12ull * FZ_MIB)
#define FZ_ULTRA_PAQ_DEFAULT (48ull * FZ_MIB)
#define FZ_MID_PRINT_MIN (4ull * FZ_MIB)

#define FZ_FLAG_NONE 0u

struct fz_stats {
	double printable;
	double zeros;
	size_t samples;
};

struct fz_tool {
	char path[512];
	int found;
};

enum fz_tool_id {
	FZ_TOOL_BSC = 0,
	FZ_TOOL_ZPAQ,
	FZ_TOOL_PAQ8PX,
	FZ_TOOL_LEPTON,
	FZ_TOOL_COUNT
};

void fz_stats_sample(const uint8_t *p, size_t n, size_t target, struct fz_stats *out);
int fz_is_jpeg(const uint8_t *p, size_t n);
int fz_is_packed_magic(const uint8_t *p, size_t n);

int fz_tool_find(enum fz_tool_id id, struct fz_tool *out);
int fz_write_file(const char *path, const void *buf, size_t n);
int fz_read_file(const char *path, uint8_t **out, size_t *n);
int fz_mkdtemp_dir(char *buf, size_t buflen);
void fz_rm_rf(const char *dir);
int fz_run(char *const argv[], int timeout_sec);
int fz_run_in(const char *cwd, char *const argv[], int timeout_sec);

int fz_comp_zstd(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n, int level);
int fz_comp_brotli(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n, int quality);
int fz_comp_zlib(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n, int level);
int fz_decomp_zstd(const uint8_t *src, size_t n, uint8_t *dst, size_t dst_n);
int fz_decomp_brotli(const uint8_t *src, size_t n, uint8_t *dst, size_t dst_n);
int fz_decomp_zlib(const uint8_t *src, size_t n, uint8_t *dst, size_t dst_n);

int fz_comp_bsc(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n);
int fz_decomp_bsc(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n);
int fz_comp_zpaq(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n);
int fz_decomp_zpaq(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n);
int fz_comp_zpaq_stream(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n);
int fz_decomp_zpaq_stream(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n);
int fz_comp_paq8px(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n, int textlike);
int fz_decomp_paq8px(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n);
int fz_comp_lepton(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n);
int fz_decomp_lepton(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n);

uint64_t fz_env_u64(const char *name, uint64_t fallback);
int fz_env_flag(const char *name);
void fz_set_last(fz_codec_id codec, fz_class cls);

static inline void fz_write_u64le(uint8_t *p, uint64_t v)
{
	for (int i = 0; i < 8; i++) {
		p[i] = (uint8_t)(v & 0xffu);
		v >>= 8;
	}
}

static inline uint64_t fz_read_u64le(const uint8_t *p)
{
	uint64_t v = 0;
	for (int i = 7; i >= 0; i--) {
		v = (v << 8) | p[i];
	}
	return v;
}

static inline int fz_printable_byte(unsigned b)
{
	return b == 9 || b == 10 || b == 13 || (b >= 32 && b <= 126);
}

#ifdef __cplusplus
}
#endif

#endif /* FZ_INTERNAL_H */
