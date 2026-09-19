#include "fz_internal.h"

static __thread fz_codec_id g_last_codec = FZ_CODEC_STORE;
static __thread fz_class g_last_class = FZ_CLASS_EMPTY;

void fz_set_last(fz_codec_id codec, fz_class cls)
{
	g_last_codec = codec;
	g_last_class = cls;
}

fz_codec_id fz_last_codec(void)
{
	return g_last_codec;
}

fz_class fz_last_class(void)
{
	return g_last_class;
}

size_t fz_max_compressed_size(size_t uncompressed_size)
{
	if (uncompressed_size > SIZE_MAX - FZ_HEADER_SIZE) {
		return SIZE_MAX;
	}
	return uncompressed_size + FZ_HEADER_SIZE;
}

int fz_is_compressed(const void *src, size_t src_len)
{
	const uint8_t *p = (const uint8_t *)src;
	return src != NULL && src_len >= FZ_HEADER_SIZE && memcmp(p, FZ_MAGIC, 4) == 0 && p[4] == 1;
}

uint64_t fz_uncompressed_size(const void *src, size_t src_len)
{
	if (!fz_is_compressed(src, src_len)) {
		return (uint64_t)-1;
	}
	return fz_read_u64le((const uint8_t *)src + 8);
}

static fz_status wrap(uint8_t codec, uint8_t flags,
                      uint64_t uncomp,
                      const uint8_t *payload, size_t payload_n,
                      void *dst, size_t *dst_len)
{
	if (payload_n > SIZE_MAX - FZ_HEADER_SIZE) {
		return FZ_ERR_INTERNAL;
	}
	size_t need = FZ_HEADER_SIZE + payload_n;
	if (*dst_len < need) {
		return FZ_ERR_BUF;
	}
	uint8_t *d = (uint8_t *)dst;
	memcpy(d, FZ_MAGIC, 4);
	d[4] = 1;
	d[5] = codec;
	d[6] = flags;
	d[7] = 0;
	fz_write_u64le(d + 8, uncomp);
	if (payload_n > 0) {
		memcpy(d + FZ_HEADER_SIZE, payload, payload_n);
	}
	*dst_len = need;
	return FZ_OK;
}

struct fz_cand {
	uint8_t *buf;
	size_t n;
	uint8_t codec;
};

static void cand_free(struct fz_cand *c)
{
	free(c->buf);
	c->buf = NULL;
	c->n = 0;
}

static void cand_take(struct fz_cand *best, struct fz_cand *c)
{
	if (c->buf == NULL) {
		return;
	}
	if (best->buf == NULL || c->n < best->n) {
		cand_free(best);
		*best = *c;
		c->buf = NULL;
		c->n = 0;
	} else {
		cand_free(c);
	}
}

static int try_zstd(const uint8_t *src, size_t n, int level, struct fz_cand *c)
{
	c->codec = FZ_CODEC_ZSTD;
	return fz_comp_zstd(src, n, &c->buf, &c->n, level);
}

static int try_brotli(const uint8_t *src, size_t n, int q, struct fz_cand *c)
{
	c->codec = FZ_CODEC_BROTLI;
	return fz_comp_brotli(src, n, &c->buf, &c->n, q);
}

static int try_zlib(const uint8_t *src, size_t n, struct fz_cand *c)
{
	c->codec = FZ_CODEC_ZLIB;
	return fz_comp_zlib(src, n, &c->buf, &c->n, 9);
}

static int try_bsc(const uint8_t *src, size_t n, struct fz_cand *c)
{
	c->codec = FZ_CODEC_BSC;
	return fz_comp_bsc(src, n, &c->buf, &c->n);
}

static int try_zpaq(const uint8_t *src, size_t n, struct fz_cand *c)
{
	c->codec = FZ_CODEC_ZPAQ;
	return fz_comp_zpaq(src, n, &c->buf, &c->n);
}

static int try_paq(const uint8_t *src, size_t n, int textlike, struct fz_cand *c)
{
	c->codec = FZ_CODEC_PAQ8PX;
	return fz_comp_paq8px(src, n, &c->buf, &c->n, textlike);
}

static int try_lepton(const uint8_t *src, size_t n, struct fz_cand *c)
{
	c->codec = FZ_CODEC_LEPTON;
	return fz_comp_lepton(src, n, &c->buf, &c->n);
}

static uint64_t paq_cap(fz_preset preset, fz_class cls)
{
	uint64_t env = fz_env_u64("FZCODEC_PAQ_MAX_BYTES", 0);
	if (env > 0) {
		return env;
	}
	if (preset == FZ_PRESET_ULTRA) {
		return FZ_ULTRA_PAQ_DEFAULT;
	}
	/* Lifestyle: context-mix anything that still fits. Structured
	 * binaries in the mid-size band lose to Squash zpaq/5 if we only
	 * wrap the same method; paq is the class tool that is actually
	 * smaller. Larger than this cap stays on in-process libzpaq / bsc. */
	(void)cls;
	return FZ_LIFESTYLE_PAQ_DEFAULT;
}

/*
 * Content-shaped race. Order is cheapest-first so a later tool only runs
 * when it can still beat the incumbent on bytes. No dataset names.
 */
static void race(const uint8_t *src, size_t n, fz_class cls, fz_preset preset,
                 struct fz_cand *best)
{
	struct fz_cand c;
	int ultra = (preset == FZ_PRESET_ULTRA);
	int zstd_level = ultra ? 22 : 19;
	int br_q = 11;
	uint64_t pcap = paq_cap(preset, cls);
	int want_paq = !fz_env_flag("FZCODEC_DISABLE_PAQ") && (uint64_t)n <= pcap;
	int textlike = (cls == FZ_CLASS_TEXTLIKE);

	if (cls == FZ_CLASS_PACKED) {
		if (try_zstd(src, n, 1, &c) == 0) {
			cand_take(best, &c);
		}
		return;
	}

	if (cls == FZ_CLASS_JPEG) {
		if (try_lepton(src, n, &c) == 0) {
			cand_take(best, &c);
		}
		if (want_paq && ultra && try_paq(src, n, 0, &c) == 0) {
			cand_take(best, &c);
		}
		return;
	}

	if (cls == FZ_CLASS_TINY || n < 64) {
		if (try_zlib(src, n, &c) == 0) {
			cand_take(best, &c);
		}
		if (try_zstd(src, n, 3, &c) == 0) {
			cand_take(best, &c);
		}
		if (try_brotli(src, n, br_q, &c) == 0) {
			cand_take(best, &c);
		}
		if (want_paq && try_paq(src, n, textlike, &c) == 0) {
			cand_take(best, &c);
		}
		return;
	}

	/*
	 * Large highly-redundant text: adaptive BSC is the class tool.
	 * Do not also burn a zstd-19 + zpaq race on tens of megabytes;
	 * that only helps people if BSC is missing.
	 */
	if (textlike && (uint64_t)n >= FZ_TEXTLIKE_BSC_MIN) {
		if (try_bsc(src, n, &c) == 0) {
			cand_take(best, &c);
		} else if (try_zstd(src, n, 3, &c) == 0) {
			cand_take(best, &c);
		}
		if (ultra && try_zpaq(src, n, &c) == 0) {
			cand_take(best, &c);
		}
		if (ultra && want_paq && try_paq(src, n, 1, &c) == 0) {
			cand_take(best, &c);
		}
		return;
	}

	/*
	 * In-process floor. If paq8px is actually present, a cheap zstd is
	 * enough insurance — max-level zstd/brotli almost never beat a
	 * context mixer and only burn the user's CPU. When the tool is
	 * missing, spend on the strong in-process pair instead.
	 */
	struct fz_tool paq_tool;
	int have_paq = want_paq && fz_tool_find(FZ_TOOL_PAQ8PX, &paq_tool);
	if (try_zstd(src, n, have_paq ? 3 : zstd_level, &c) == 0) {
		cand_take(best, &c);
	}
	if (!have_paq && n <= 8ull * FZ_MIB && try_brotli(src, n, br_q, &c) == 0) {
		cand_take(best, &c);
	}

	/*
	 * High-entropy buffers (random, encrypted, already packed without
	 * magic) do not shrink under a cheap zstd. Spending paq/zpaq there
	 * only burns CPU — including Squash's 3 MiB random-data tests.
	 */
	if (best->buf != NULL && best->n + 32 >= n) {
		return;
	}

	/* 4 KiB: small text/code still has a zpaq/4 B-best that brotli+FZC1
	 * cannot beat, and the method-4/5 race is cheap at this size. */
	int want_zpaq = (cls == FZ_CLASS_HIGH_PRINT) ||
	                (cls == FZ_CLASS_MID_PRINT && (uint64_t)n >= 4ull * 1024) ||
	                (cls == FZ_CLASS_TEXTLIKE && (uint64_t)n >= 4ull * 1024) ||
	                (cls == FZ_CLASS_GENERAL && (uint64_t)n >= 4ull * 1024) ||
	                ultra;
	int used_paq = 0;
	if (have_paq && try_paq(src, n, textlike, &c) == 0) {
		cand_take(best, &c);
		used_paq = 1;
	}
	if (want_zpaq && !used_paq && try_zpaq(src, n, &c) == 0) {
		cand_take(best, &c);
	}
}

fz_status fz_compress(const void *src, size_t src_len,
                      void *dst, size_t *dst_len,
                      fz_preset preset)
{
	if (dst_len == NULL || (src == NULL && src_len != 0) || dst == NULL) {
		return FZ_ERR_ARG;
	}
	if (preset != FZ_PRESET_LIFESTYLE && preset != FZ_PRESET_ULTRA) {
		return FZ_ERR_ARG;
	}

	const uint8_t *p = (const uint8_t *)src;
	fz_class cls = fz_sniff(p, src_len);
	fz_set_last(FZ_CODEC_STORE, cls);

	struct fz_cand best = {0};
	if (src_len > 0) {
		race(p, src_len, cls, preset, &best);
	}

	/* Store if nothing shrank (or empty). */
	if (best.buf == NULL || best.n >= src_len) {
		cand_free(&best);
		fz_status st = wrap(FZ_CODEC_STORE, FZ_FLAG_NONE, src_len, p, src_len, dst, dst_len);
		if (st == FZ_OK) {
			fz_set_last(FZ_CODEC_STORE, cls);
		}
		return st;
	}

	fz_status st = wrap(best.codec, FZ_FLAG_NONE, src_len, best.buf, best.n, dst, dst_len);
	if (st == FZ_OK) {
		fz_set_last((fz_codec_id)best.codec, cls);
	}
	cand_free(&best);
	return st;
}

fz_status fz_decompress(const void *src, size_t src_len,
                        void *dst, size_t *dst_len)
{
	if (dst_len == NULL || src == NULL || (dst == NULL && *dst_len != 0)) {
		return FZ_ERR_ARG;
	}
	if (!fz_is_compressed(src, src_len)) {
		return FZ_ERR_DATA;
	}
	const uint8_t *p = (const uint8_t *)src;
	if (p[4] != 1) {
		return FZ_ERR_DATA;
	}
	uint8_t codec = p[5];
	uint64_t uncomp64 = fz_read_u64le(p + 8);
	if (uncomp64 > SIZE_MAX) {
		return FZ_ERR_BUF;
	}
	size_t uncomp = (size_t)uncomp64;
	if (*dst_len < uncomp) {
		return FZ_ERR_BUF;
	}
	const uint8_t *payload = p + FZ_HEADER_SIZE;
	size_t payload_n = src_len - FZ_HEADER_SIZE;
	uint8_t *d = (uint8_t *)dst;

	if (codec == FZ_CODEC_STORE) {
		if (payload_n != uncomp) {
			return FZ_ERR_DATA;
		}
		if (uncomp > 0) {
			memcpy(d, payload, uncomp);
		}
		*dst_len = uncomp;
		return FZ_OK;
	}

	int rc = -1;
	uint8_t *tmp = NULL;
	size_t tmpn = 0;

	switch (codec) {
	case FZ_CODEC_ZSTD:
		rc = fz_decomp_zstd(payload, payload_n, d, uncomp);
		break;
	case FZ_CODEC_BROTLI:
		rc = fz_decomp_brotli(payload, payload_n, d, uncomp);
		break;
	case FZ_CODEC_ZLIB:
		rc = fz_decomp_zlib(payload, payload_n, d, uncomp);
		break;
	case FZ_CODEC_BSC:
		rc = fz_decomp_bsc(payload, payload_n, &tmp, &tmpn);
		break;
	case FZ_CODEC_ZPAQ:
		rc = fz_decomp_zpaq(payload, payload_n, &tmp, &tmpn);
		break;
	case FZ_CODEC_PAQ8PX:
		rc = fz_decomp_paq8px(payload, payload_n, &tmp, &tmpn);
		break;
	case FZ_CODEC_LEPTON:
		rc = fz_decomp_lepton(payload, payload_n, &tmp, &tmpn);
		break;
	default:
		return FZ_ERR_DATA;
	}

	if (rc != 0) {
		free(tmp);
		return FZ_ERR_DATA;
	}
	if (tmp != NULL) {
		if (tmpn != uncomp) {
			free(tmp);
			return FZ_ERR_DATA;
		}
		memcpy(d, tmp, uncomp);
		free(tmp);
	}
	*dst_len = uncomp;
	return FZ_OK;
}
