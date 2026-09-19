#include "fzcodec.h"

#include <errno.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

static int read_all(const char *path, uint8_t **out, size_t *n)
{
	FILE *f = (path != NULL && strcmp(path, "-") == 0) ? stdin : fopen(path, "rb");
	if (f == NULL) {
		fprintf(stderr, "fzcodec: cannot open %s: %s\n", path, strerror(errno));
		return -1;
	}
	size_t cap = 4096;
	size_t used = 0;
	uint8_t *buf = (uint8_t *)malloc(cap);
	if (buf == NULL) {
		if (f != stdin) {
			fclose(f);
		}
		return -1;
	}
	for (;;) {
		if (used == cap) {
			if (cap > (SIZE_MAX / 2)) {
				free(buf);
				if (f != stdin) {
					fclose(f);
				}
				return -1;
			}
			size_t ncap = cap * 2;
			uint8_t *nb = (uint8_t *)realloc(buf, ncap);
			if (nb == NULL) {
				free(buf);
				if (f != stdin) {
					fclose(f);
				}
				return -1;
			}
			buf = nb;
			cap = ncap;
		}
		size_t r = fread(buf + used, 1, cap - used, f);
		used += r;
		if (r == 0) {
			break;
		}
	}
	if (f != stdin) {
		fclose(f);
	}
	*out = buf;
	*n = used;
	return 0;
}

static int write_all(const char *path, const void *buf, size_t n)
{
	FILE *f = (path != NULL && strcmp(path, "-") == 0) ? stdout : fopen(path, "wb");
	if (f == NULL) {
		fprintf(stderr, "fzcodec: cannot write %s: %s\n", path, strerror(errno));
		return -1;
	}
	if (n > 0 && fwrite(buf, 1, n, f) != n) {
		fprintf(stderr, "fzcodec: write failed: %s\n", strerror(errno));
		if (f != stdout) {
			fclose(f);
		}
		return -1;
	}
	if (f != stdout) {
		fclose(f);
	}
	return 0;
}

static void usage(FILE *out)
{
	fprintf(out,
	        "fzcodec — general-purpose compressor (lifestyle / ultra)\n"
	        "\n"
	        "Usage:\n"
	        "  fzcodec compress [--lifestyle|--ultra] IN OUT\n"
	        "  fzcodec decompress IN OUT\n"
	        "  fzcodec sniff IN\n"
	        "\n"
	        "IN/OUT may be '-' for stdin/stdout.\n"
	        "Default preset is lifestyle (speed-aware). ultra is bytes-first.\n"
	        "\n"
	        "Optional tools on PATH (or FZCODEC_BSC / FZCODEC_ZPAQ /\n"
	        "FZCODEC_PAQ8PX / FZCODEC_LEPTON): bsc, zpaq, paq8px, lepton.\n"
	        "zstd, brotli, and zlib are linked in.\n");
}

int main(int argc, char **argv)
{
	if (argc < 2) {
		usage(stderr);
		return 2;
	}
	if (strcmp(argv[1], "-h") == 0 || strcmp(argv[1], "--help") == 0) {
		usage(stdout);
		return 0;
	}

	const char *cmd = argv[1];
	if (strcmp(cmd, "sniff") == 0) {
		if (argc != 3) {
			usage(stderr);
			return 2;
		}
		uint8_t *in = NULL;
		size_t n = 0;
		if (read_all(argv[2], &in, &n) != 0) {
			return 1;
		}
		fz_class c = fz_sniff(in, n);
		printf("%s\t%zu\n", fz_class_name(c), n);
		free(in);
		return 0;
	}

	if (strcmp(cmd, "compress") == 0) {
		fz_preset preset = FZ_PRESET_LIFESTYLE;
		int i = 2;
		while (i < argc && argv[i][0] == '-') {
			if (strcmp(argv[i], "--lifestyle") == 0) {
				preset = FZ_PRESET_LIFESTYLE;
			} else if (strcmp(argv[i], "--ultra") == 0) {
				preset = FZ_PRESET_ULTRA;
			} else if (strcmp(argv[i], "--help") == 0) {
				usage(stdout);
				return 0;
			} else {
				fprintf(stderr, "fzcodec: unknown option %s\n", argv[i]);
				return 2;
			}
			i++;
		}
		if (i + 1 >= argc) {
			usage(stderr);
			return 2;
		}
		uint8_t *in = NULL;
		size_t n = 0;
		if (read_all(argv[i], &in, &n) != 0) {
			return 1;
		}
		size_t cap = fz_max_compressed_size(n);
		uint8_t *out = (uint8_t *)malloc(cap ? cap : 1);
		if (out == NULL) {
			free(in);
			return 1;
		}
		size_t used = cap;
		fz_status st = fz_compress(in, n, out, &used, preset);
		if (st != FZ_OK) {
			fprintf(stderr, "fzcodec: compress failed: %s\n", fz_status_string(st));
			free(in);
			free(out);
			return 1;
		}
		if (write_all(argv[i + 1], out, used) != 0) {
			free(in);
			free(out);
			return 1;
		}
		fprintf(stderr, "fzcodec: %s %s → %s  %zu → %zu  (%.2f%%)\n",
		        fz_preset_name(preset), fz_class_name(fz_last_class()),
		        fz_codec_name(fz_last_codec()), n, used,
		        n ? (100.0 * (double)used / (double)n) : 0.0);
		free(in);
		free(out);
		return 0;
	}

	if (strcmp(cmd, "decompress") == 0) {
		if (argc != 4) {
			usage(stderr);
			return 2;
		}
		uint8_t *in = NULL;
		size_t n = 0;
		if (read_all(argv[2], &in, &n) != 0) {
			return 1;
		}
		uint64_t want = fz_uncompressed_size(in, n);
		if (want == (uint64_t)-1) {
			fprintf(stderr, "fzcodec: not an FZC1 buffer\n");
			free(in);
			return 1;
		}
		if (want > SIZE_MAX) {
			free(in);
			return 1;
		}
		uint8_t *out = (uint8_t *)malloc(want ? (size_t)want : 1);
		if (out == NULL) {
			free(in);
			return 1;
		}
		size_t used = (size_t)want;
		fz_status st = fz_decompress(in, n, out, &used);
		if (st != FZ_OK) {
			fprintf(stderr, "fzcodec: decompress failed: %s\n", fz_status_string(st));
			free(in);
			free(out);
			return 1;
		}
		if (write_all(argv[3], out, used) != 0) {
			free(in);
			free(out);
			return 1;
		}
		free(in);
		free(out);
		return 0;
	}

	usage(stderr);
	return 2;
}
