/* Load fz-lifestyle / fz-ultra through libsquash and roundtrip a buffer. */
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include <squash.h>
#include <fzcodec.h>

static int
roundtrip(const char *codec_name, const uint8_t *src, size_t n)
{
	SquashCodec *codec = squash_get_codec(codec_name);
	if (codec == NULL) {
		fprintf(stderr, "FAIL: squash_get_codec(%s) returned NULL\n", codec_name);
		return 1;
	}
	size_t cap = squash_codec_get_max_compressed_size(codec, n);
	uint8_t *enc = (uint8_t *)malloc(cap ? cap : 1);
	uint8_t *dec = (uint8_t *)malloc(n ? n : 1);
	if (enc == NULL || dec == NULL) {
		free(enc);
		free(dec);
		return 1;
	}
	size_t used = cap;
	SquashStatus st = squash_codec_compress(codec, &used, enc, n, src, NULL);
	if (st != SQUASH_OK) {
		fprintf(stderr, "FAIL: %s compress: %s\n", codec_name, squash_status_to_string(st));
		free(enc);
		free(dec);
		return 1;
	}
	if (!fz_is_compressed(enc, used)) {
		fprintf(stderr, "FAIL: %s output is not FZC1\n", codec_name);
		free(enc);
		free(dec);
		return 1;
	}
	size_t hinted = squash_codec_get_uncompressed_size(codec, used, enc);
	if (hinted != n) {
		fprintf(stderr, "FAIL: %s get_uncompressed_size %zu != %zu\n", codec_name, hinted, n);
		free(enc);
		free(dec);
		return 1;
	}
	size_t dout = n;
	st = squash_codec_decompress(codec, &dout, dec, used, enc, NULL);
	if (st != SQUASH_OK || dout != n || (n > 0 && memcmp(dec, src, n) != 0)) {
		fprintf(stderr, "FAIL: %s decompress\n", codec_name);
		free(enc);
		free(dec);
		return 1;
	}
	printf("ok  squash %s  %zu → %zu\n", codec_name, n, used);
	free(enc);
	free(dec);
	return 0;
}

static int
read_all(const char *path, uint8_t **out, size_t *n)
{
	FILE *f = fopen(path, "rb");
	if (f == NULL) {
		perror(path);
		return -1;
	}
	if (fseek(f, 0, SEEK_END) != 0) {
		fclose(f);
		return -1;
	}
	long sz = ftell(f);
	if (sz < 0) {
		fclose(f);
		return -1;
	}
	rewind(f);
	uint8_t *buf = (uint8_t *)malloc(sz ? (size_t)sz : 1);
	if (buf == NULL) {
		fclose(f);
		return -1;
	}
	if (sz > 0 && fread(buf, 1, (size_t)sz, f) != (size_t)sz) {
		free(buf);
		fclose(f);
		return -1;
	}
	fclose(f);
	*out = buf;
	*n = (size_t)sz;
	return 0;
}

int
main(int argc, char **argv)
{
	const char *plugins = getenv("SQUASH_PLUGINS");
	if (plugins == NULL || plugins[0] == '\0') {
		fprintf(stderr, "test_squash_plugin: set SQUASH_PLUGINS to the plugin parent dir\n");
		return 2;
	}
	squash_set_default_search_path(plugins);

	const char *codec = "fz-lifestyle";
	const char *file = NULL;
	for (int i = 1; i < argc; i++) {
		if (strncmp(argv[i], "--codec=", 8) == 0) {
			codec = argv[i] + 8;
		} else if (argv[i][0] != '-') {
			file = argv[i];
		}
	}

	if (file != NULL) {
		uint8_t *buf = NULL;
		size_t n = 0;
		if (read_all(file, &buf, &n) != 0) {
			return 1;
		}
		int rc = roundtrip(codec, buf, n);
		free(buf);
		return rc;
	}

	static const char *text =
		"The quick brown fox jumps over the lazy dog. "
		"Pack my box with five dozen liquor jugs.\n";
	uint8_t buf[4096];
	for (size_t i = 0; i < sizeof(buf); i++) {
		buf[i] = (uint8_t)text[i % strlen(text)];
	}

	int rc = 0;
	rc |= roundtrip("fz-lifestyle", buf, sizeof(buf));
	rc |= roundtrip("fz-ultra", buf, 64);
	uint8_t one[1] = {42};
	rc |= roundtrip("fz-lifestyle", one, 1);
	if (rc == 0) {
		printf("test_squash_plugin: all passed\n");
	}
	return rc;
}
