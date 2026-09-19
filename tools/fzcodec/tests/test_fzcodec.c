#include "fzcodec.h"

#include <pthread.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

static int g_fail = 0;

static void expect(int ok, const char *msg)
{
	if (!ok) {
		fprintf(stderr, "FAIL: %s\n", msg);
		g_fail = 1;
	}
}

static void fill_text(uint8_t *p, size_t n)
{
	static const char *w =
		"The quick brown fox jumps over the lazy dog. "
		"Pack my box with five dozen liquor jugs.\n";
	size_t wl = strlen(w);
	for (size_t i = 0; i < n; i++) {
		p[i] = (uint8_t)w[i % wl];
	}
}

static void roundtrip(const char *name, const uint8_t *src, size_t n, fz_preset preset)
{
	size_t cap = fz_max_compressed_size(n);
	uint8_t *enc = (uint8_t *)malloc(cap ? cap : 1);
	uint8_t *dec = (uint8_t *)malloc(n ? n : 1);
	expect(enc != NULL && dec != NULL, "alloc");
	size_t used = cap;
	fz_status st = fz_compress(src, n, enc, &used, preset);
	if (st != FZ_OK) {
		fprintf(stderr, "FAIL: %s compress: %s\n", name, fz_status_string(st));
		g_fail = 1;
		free(enc);
		free(dec);
		return;
	}
	expect(used <= cap, "encoded within bound");
	expect(fz_is_compressed(enc, used), "magic");
	expect(fz_uncompressed_size(enc, used) == n, "header size");
	size_t dout = n;
	st = fz_decompress(enc, used, dec, &dout);
	if (st != FZ_OK || dout != n || (n > 0 && memcmp(dec, src, n) != 0)) {
		fprintf(stderr, "FAIL: %s roundtrip (st=%s used=%zu)\n",
		        name, fz_status_string(st), used);
		g_fail = 1;
	} else {
		fprintf(stdout, "ok  %s  %zu → %zu  %s/%s\n",
		        name, n, used, fz_class_name(fz_last_class()),
		        fz_codec_name(fz_last_codec()));
	}
	free(enc);
	free(dec);
}

static void test_sniff(void)
{
	expect(fz_sniff(NULL, 0) == FZ_CLASS_EMPTY, "empty");

	uint8_t tiny[40];
	memset(tiny, 'a', sizeof(tiny));
	expect(fz_sniff(tiny, sizeof(tiny)) == FZ_CLASS_TINY, "tiny");

	uint8_t jpeg[16];
	memset(jpeg, 0, sizeof(jpeg));
	jpeg[0] = 0xff;
	jpeg[1] = 0xd8;
	jpeg[2] = 0xff;
	jpeg[3] = 0xe0;
	expect(fz_sniff(jpeg, sizeof(jpeg)) == FZ_CLASS_JPEG, "jpeg magic");

	uint8_t gz[8] = {0x1f, 0x8b, 0x08, 0, 0, 0, 0, 0};
	expect(fz_sniff(gz, sizeof(gz)) == FZ_CLASS_PACKED, "gzip magic");

	uint8_t text[8192];
	fill_text(text, sizeof(text));
	expect(fz_sniff(text, sizeof(text)) == FZ_CLASS_TEXTLIKE, "textlike lorem");

	uint8_t high[8192];
	for (size_t i = 0; i < sizeof(high); i++) {
		high[i] = (i % 5 == 0) ? 0x01 : (uint8_t)('A' + (i % 26));
	}
	fz_class hc = fz_sniff(high, sizeof(high));
	expect(hc == FZ_CLASS_HIGH_PRINT || hc == FZ_CLASS_TEXTLIKE, "high-print band");

	uint8_t mid[8192];
	for (size_t i = 0; i < sizeof(mid); i++) {
		mid[i] = (uint8_t)((i * 17u) & 0xffu);
		if ((i % 3) == 0) {
			mid[i] = (uint8_t)('0' + (i % 10));
		}
	}
	fz_class mc = fz_sniff(mid, sizeof(mid));
	expect(mc == FZ_CLASS_MID_PRINT || mc == FZ_CLASS_GENERAL, "mid-print or general");

	/* Class must not depend on a file name — only bytes. */
	expect(fz_sniff(text, sizeof(text)) == fz_sniff(text, sizeof(text)), "deterministic");
}

static void test_garbage(void)
{
	uint8_t junk[32];
	memset(junk, 0x5a, sizeof(junk));
	uint8_t out[32];
	size_t n = sizeof(out);
	expect(fz_decompress(junk, sizeof(junk), out, &n) == FZ_ERR_DATA, "junk rejected");
	expect(fz_decompress("FZC", 3, out, &n) == FZ_ERR_DATA, "short rejected");
	expect(!fz_is_compressed(junk, sizeof(junk)), "junk not fzc1");
}

static void test_too_small_dest(void)
{
	uint8_t src[64];
	fill_text(src, sizeof(src));
	uint8_t enc[128];
	size_t used = sizeof(enc);
	expect(fz_compress(src, sizeof(src), enc, &used, FZ_PRESET_LIFESTYLE) == FZ_OK, "enc");
	uint8_t tiny[4];
	size_t tn = sizeof(tiny);
	expect(fz_decompress(enc, used, tiny, &tn) == FZ_ERR_BUF, "short dest");
}

struct thr_arg {
	const uint8_t *src;
	size_t n;
	int rc;
};

static void *thr_fn(void *vp)
{
	struct thr_arg *a = (struct thr_arg *)vp;
	size_t cap = fz_max_compressed_size(a->n);
	uint8_t *enc = (uint8_t *)malloc(cap);
	uint8_t *dec = (uint8_t *)malloc(a->n);
	size_t used = cap;
	a->rc = 1;
	if (enc == NULL || dec == NULL) {
		free(enc);
		free(dec);
		return NULL;
	}
	if (fz_compress(a->src, a->n, enc, &used, FZ_PRESET_LIFESTYLE) != FZ_OK) {
		free(enc);
		free(dec);
		return NULL;
	}
	size_t dn = a->n;
	if (fz_decompress(enc, used, dec, &dn) != FZ_OK || dn != a->n ||
	    memcmp(dec, a->src, a->n) != 0) {
		free(enc);
		free(dec);
		return NULL;
	}
	a->rc = 0;
	free(enc);
	free(dec);
	return NULL;
}

static void test_threads(void)
{
	uint8_t a[4096], b[4096], c[4096];
	fill_text(a, sizeof(a));
	memset(b, 0, sizeof(b));
	for (size_t i = 0; i < sizeof(c); i++) {
		c[i] = (uint8_t)(i * 31u);
	}
	struct thr_arg args[3] = {
		{a, sizeof(a), -1},
		{b, sizeof(b), -1},
		{c, sizeof(c), -1},
	};
	pthread_t th[3];
	for (int i = 0; i < 3; i++) {
		expect(pthread_create(&th[i], NULL, thr_fn, &args[i]) == 0, "pthread_create");
	}
	for (int i = 0; i < 3; i++) {
		pthread_join(th[i], NULL);
		expect(args[i].rc == 0, "thread roundtrip");
	}
}

int main(void)
{
	test_sniff();
	test_garbage();
	test_too_small_dest();

	roundtrip("empty", NULL, 0, FZ_PRESET_LIFESTYLE);

	uint8_t one[1] = {42};
	roundtrip("one-byte", one, 1, FZ_PRESET_LIFESTYLE);

	uint8_t zeros[4096];
	memset(zeros, 0, sizeof(zeros));
	roundtrip("zeros-4k", zeros, sizeof(zeros), FZ_PRESET_LIFESTYLE);

	uint8_t text[20000];
	fill_text(text, sizeof(text));
	roundtrip("text-20k-lifestyle", text, sizeof(text), FZ_PRESET_LIFESTYLE);
	roundtrip("text-20k-ultra", text, sizeof(text), FZ_PRESET_ULTRA);

	uint8_t rnd[8192];
	for (size_t i = 0; i < sizeof(rnd); i++) {
		rnd[i] = (uint8_t)((i * 1103515245u + 12345u) >> 16);
	}
	roundtrip("random-8k", rnd, sizeof(rnd), FZ_PRESET_LIFESTYLE);

	/* Must not fall into paq/zpaq: high-entropy, cheap zstd already loses. */
	uint8_t *rnd256 = (uint8_t *)malloc(262144);
	expect(rnd256 != NULL, "rnd256 alloc");
	if (rnd256 != NULL) {
		FILE *ur = fopen("/dev/urandom", "rb");
		expect(ur != NULL && fread(rnd256, 1, 262144, ur) == 262144, "urandom");
		if (ur != NULL) {
			fclose(ur);
		}
		roundtrip("random-256k", rnd256, 262144, FZ_PRESET_LIFESTYLE);
		expect(fz_last_codec() != FZ_CODEC_PAQ8PX && fz_last_codec() != FZ_CODEC_ZPAQ,
		       "random-256k skipped mixers");
		free(rnd256);
	}

	uint8_t jpeg[512];
	memset(jpeg, 0x80, sizeof(jpeg));
	jpeg[0] = 0xff;
	jpeg[1] = 0xd8;
	jpeg[2] = 0xff;
	jpeg[3] = 0xd9;
	roundtrip("jpeg-magic-stub", jpeg, sizeof(jpeg), FZ_PRESET_LIFESTYLE);

	test_threads();

	if (g_fail) {
		fprintf(stderr, "test_fzcodec: FAILED\n");
		return 1;
	}
	fprintf(stdout, "test_fzcodec: all passed\n");
	return 0;
}
