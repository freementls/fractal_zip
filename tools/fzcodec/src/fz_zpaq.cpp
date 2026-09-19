/* In-process libzpaq stream — same wire Squash's zpaq/5 plugin uses
 * (empty filename, method "5"). CLI journaling archives are a fallback
 * for decode of older FZC1 payloads only. */
#include "fz_internal.h"

#include "libzpaq.h"

#include <new>
#include <stdexcept>
#include <vector>

void libzpaq::error(const char *msg)
{
	throw std::runtime_error(msg != NULL ? msg : "libzpaq");
}

namespace {

class MemIn : public libzpaq::Reader {
public:
	MemIn(const uint8_t *p, size_t n) : p_(p), n_(n), i_(0) {}
	int get()
	{
		return i_ < n_ ? (int)p_[i_++] : -1;
	}
	int read(char *buf, int nreq)
	{
		size_t left = n_ - i_;
		int take = nreq > 0 && (size_t)nreq < left ? nreq : (int)left;
		if (take > 0) {
			memcpy(buf, p_ + i_, (size_t)take);
			i_ += (size_t)take;
		}
		return take;
	}

private:
	const uint8_t *p_;
	size_t n_;
	size_t i_;
};

class MemOut : public libzpaq::Writer {
public:
	std::vector<uint8_t> v;
	void put(int c)
	{
		v.push_back((uint8_t)c);
	}
	void write(const char *buf, int n)
	{
		if (n > 0) {
			v.insert(v.end(), buf, buf + n);
		}
	}
};

} // namespace

extern "C" {

static int zpaq_stream_method(const uint8_t *src, size_t n, uint8_t **out,
                              size_t *out_n, const char *method)
{
	try {
		MemIn in(src, n);
		MemOut ow;
		/* SHA-1 off: FZC1 already carries uncompressed size. */
		libzpaq::compress(&in, &ow, method, 0, 0, false);
		uint8_t *p = (uint8_t *)malloc(ow.v.empty() ? 1 : ow.v.size());
		if (p == NULL) {
			return -1;
		}
		if (!ow.v.empty()) {
			memcpy(p, ow.v.data(), ow.v.size());
		}
		*out = p;
		*out_n = ow.v.size();
		return 0;
	} catch (...) {
		return -1;
	}
}

int fz_comp_zpaq_stream(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n)
{
	if (out == NULL || out_n == NULL || (src == NULL && n != 0)) {
		return -1;
	}
	/* Method 5 matches Squash zpaq/5. On small buffers, method 4 sometimes
	 * wins (Squash B-best is zpaq/4 on a few Silesia files). */
	if (zpaq_stream_method(src, n, out, out_n, "5") != 0) {
		return -1;
	}
	if (n > 0 && n < (2ull * 1024 * 1024)) {
		uint8_t *alt = NULL;
		size_t alt_n = 0;
		if (zpaq_stream_method(src, n, &alt, &alt_n, "4") == 0) {
			if (alt_n < *out_n) {
				free(*out);
				*out = alt;
				*out_n = alt_n;
			} else {
				free(alt);
			}
		}
	}
	return 0;
}

int fz_decomp_zpaq_stream(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n)
{
	if (out == NULL || out_n == NULL || src == NULL) {
		return -1;
	}
	try {
		MemIn in(src, n);
		MemOut ow;
		libzpaq::decompress(&in, &ow);
		uint8_t *p = (uint8_t *)malloc(ow.v.empty() ? 1 : ow.v.size());
		if (p == NULL) {
			return -1;
		}
		if (!ow.v.empty()) {
			memcpy(p, ow.v.data(), ow.v.size());
		}
		*out = p;
		*out_n = ow.v.size();
		return 0;
	} catch (...) {
		return -1;
	}
}

} /* extern "C" */
