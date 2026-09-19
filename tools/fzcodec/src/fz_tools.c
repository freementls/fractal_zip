#define _GNU_SOURCE
#include "fz_internal.h"

#include <dirent.h>
#include <fcntl.h>
#include <limits.h>
#include <pthread.h>
#include <signal.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <sys/wait.h>
#include <time.h>
#include <unistd.h>

#include <brotli/decode.h>
#include <brotli/encode.h>
#include <zlib.h>
#include <zstd.h>

static pthread_mutex_t g_tool_mu = PTHREAD_MUTEX_INITIALIZER;
static struct fz_tool g_tools[FZ_TOOL_COUNT];
static int g_tools_ready = 0;

static const char *tool_env_name(enum fz_tool_id id)
{
	switch (id) {
	case FZ_TOOL_BSC:
		return "FZCODEC_BSC";
	case FZ_TOOL_ZPAQ:
		return "FZCODEC_ZPAQ";
	case FZ_TOOL_PAQ8PX:
		return "FZCODEC_PAQ8PX";
	case FZ_TOOL_LEPTON:
		return "FZCODEC_LEPTON";
	default:
		return NULL;
	}
}

static const char *tool_basename(enum fz_tool_id id)
{
	switch (id) {
	case FZ_TOOL_BSC:
		return "bsc";
	case FZ_TOOL_ZPAQ:
		return "zpaq";
	case FZ_TOOL_PAQ8PX:
		return "paq8px";
	case FZ_TOOL_LEPTON:
		return "lepton";
	default:
		return NULL;
	}
}


static int file_is_exec(const char *path)
{
	struct stat st;
	if (path == NULL || path[0] == '\0') {
		return 0;
	}
	if (stat(path, &st) != 0 || !S_ISREG(st.st_mode)) {
		return 0;
	}
	return access(path, X_OK) == 0;
}

static int join2(char *dst, size_t dstn, const char *a, const char *b)
{
	int w = snprintf(dst, dstn, "%s/%s", a, b);
	return w > 0 && (size_t)w < dstn;
}

static void consider(struct fz_tool *t, const char *path)
{
	if (t->found || !file_is_exec(path)) {
		return;
	}
	snprintf(t->path, sizeof(t->path), "%s", path);
	t->found = 1;
}

static void search_path_env(struct fz_tool *t, const char *name)
{
	const char *path = getenv("PATH");
	if (path == NULL) {
		return;
	}
	char buf[4096];
	snprintf(buf, sizeof(buf), "%s", path);
	char *save = NULL;
	for (char *tok = strtok_r(buf, ":", &save); tok != NULL; tok = strtok_r(NULL, ":", &save)) {
		char cand[512];
		if (join2(cand, sizeof(cand), tok, name)) {
			consider(t, cand);
		}
		if (t->found) {
			return;
		}
	}
}

/* Prefer zpaqfranz (encode.su / PATH) over stock zpaq 7.15. */
static void search_zpaq(struct fz_tool *t)
{
	search_path_env(t, "zpaqfranz");
	if (!t->found) {
		search_path_env(t, "zpaq");
	}
}

static void search_hints(struct fz_tool *t, enum fz_tool_id id)
{
	static const char *roots[] = {
#ifdef FZCODEC_HINT_ROOT
		FZCODEC_HINT_ROOT,
#endif
		NULL
	};
	const char *rel = NULL;
	switch (id) {
	case FZ_TOOL_BSC:
		rel = "benchmarks/.tools/bin/bsc";
		break;
	case FZ_TOOL_ZPAQ:
		rel = "benchmarks/.tools/bin/zpaqfranz";
		break;
	case FZ_TOOL_LEPTON:
		rel = "tools/jpeg/lepton/build/lepton";
		break;
	default:
		rel = NULL;
		break;
	}
	if (rel == NULL) {
		return;
	}
	for (int i = 0; roots[i] != NULL; i++) {
		char cand[512];
		if (join2(cand, sizeof(cand), roots[i], rel)) {
			consider(t, cand);
		}
		if (t->found) {
			return;
		}
	}
}

static void tools_init_locked(void)
{
	if (g_tools_ready) {
		return;
	}
	memset(g_tools, 0, sizeof(g_tools));
	for (int id = 0; id < FZ_TOOL_COUNT; id++) {
		const char *envn = tool_env_name((enum fz_tool_id)id);
		const char *e = envn != NULL ? getenv(envn) : NULL;
		if (e != NULL && e[0] != '\0') {
			consider(&g_tools[id], e);
		}
		if ((enum fz_tool_id)id == FZ_TOOL_ZPAQ) {
			/* Bundled encode.su build, then PATH zpaqfranz, then stock zpaq. */
			if (!g_tools[id].found) {
				search_hints(&g_tools[id], (enum fz_tool_id)id);
			}
			if (!g_tools[id].found) {
				search_zpaq(&g_tools[id]);
			}
		} else {
			if (!g_tools[id].found) {
				search_path_env(&g_tools[id], tool_basename((enum fz_tool_id)id));
			}
			if (!g_tools[id].found) {
				search_hints(&g_tools[id], (enum fz_tool_id)id);
			}
		}
	}
	g_tools_ready = 1;
}

int fz_tool_find(enum fz_tool_id id, struct fz_tool *out)
{
	if (id < 0 || id >= FZ_TOOL_COUNT || out == NULL) {
		return 0;
	}
	pthread_mutex_lock(&g_tool_mu);
	tools_init_locked();
	*out = g_tools[id];
	pthread_mutex_unlock(&g_tool_mu);
	return out->found;
}

uint64_t fz_env_u64(const char *name, uint64_t fallback)
{
	const char *e = getenv(name);
	if (e == NULL || e[0] == '\0') {
		return fallback;
	}
	char *end = NULL;
	unsigned long long v = strtoull(e, &end, 10);
	if (end == e) {
		return fallback;
	}
	return (uint64_t)v;
}

int fz_env_flag(const char *name)
{
	const char *e = getenv(name);
	return e != NULL && e[0] == '1' && e[1] == '\0';
}

int fz_write_file(const char *path, const void *buf, size_t n)
{
	int fd = open(path, O_WRONLY | O_CREAT | O_TRUNC | O_CLOEXEC, 0600);
	if (fd < 0) {
		return -1;
	}
	const uint8_t *p = (const uint8_t *)buf;
	size_t left = n;
	while (left > 0) {
		ssize_t w = write(fd, p, left);
		if (w < 0) {
			if (errno == EINTR) {
				continue;
			}
			close(fd);
			return -1;
		}
		p += (size_t)w;
		left -= (size_t)w;
	}
	if (close(fd) != 0) {
		return -1;
	}
	return 0;
}

int fz_read_file(const char *path, uint8_t **out, size_t *n)
{
	*out = NULL;
	*n = 0;
	int fd = open(path, O_RDONLY | O_CLOEXEC);
	if (fd < 0) {
		return -1;
	}
	struct stat st;
	if (fstat(fd, &st) != 0 || !S_ISREG(st.st_mode) || st.st_size < 0) {
		close(fd);
		return -1;
	}
	size_t sz = (size_t)st.st_size;
	uint8_t *buf = (uint8_t *)malloc(sz ? sz : 1);
	if (buf == NULL) {
		close(fd);
		return -1;
	}
	size_t got = 0;
	while (got < sz) {
		ssize_t r = read(fd, buf + got, sz - got);
		if (r < 0) {
			if (errno == EINTR) {
				continue;
			}
			free(buf);
			close(fd);
			return -1;
		}
		if (r == 0) {
			break;
		}
		got += (size_t)r;
	}
	close(fd);
	*out = buf;
	*n = got;
	return 0;
}

int fz_mkdtemp_dir(char *buf, size_t buflen)
{
	const char *tmpdir = getenv("TMPDIR");
	if (tmpdir == NULL || tmpdir[0] == '\0') {
		tmpdir = "/tmp";
	}
	int w = snprintf(buf, buflen, "%s/fzcodec.XXXXXX", tmpdir);
	if (w < 0 || (size_t)w >= buflen) {
		return -1;
	}
	if (mkdtemp(buf) == NULL) {
		return -1;
	}
	return 0;
}

static void rm_rf_inner(const char *path)
{
	struct stat st;
	if (lstat(path, &st) != 0) {
		return;
	}
	if (S_ISDIR(st.st_mode)) {
		DIR *d = opendir(path);
		if (d != NULL) {
			struct dirent *e;
			while ((e = readdir(d)) != NULL) {
				if (strcmp(e->d_name, ".") == 0 || strcmp(e->d_name, "..") == 0) {
					continue;
				}
				char child[1024];
				if (snprintf(child, sizeof(child), "%s/%s", path, e->d_name) < (int)sizeof(child)) {
					rm_rf_inner(child);
				}
			}
			closedir(d);
		}
		rmdir(path);
	} else {
		unlink(path);
	}
}

void fz_rm_rf(const char *dir)
{
	if (dir != NULL && dir[0] != '\0') {
		rm_rf_inner(dir);
	}
}

int fz_run_in(const char *cwd, char *const argv[], int timeout_sec)
{
	if (argv == NULL || argv[0] == NULL) {
		return -1;
	}
	pid_t pid = fork();
	if (pid < 0) {
		return -1;
	}
	if (pid == 0) {
		if (cwd != NULL && cwd[0] != '\0' && chdir(cwd) != 0) {
			_exit(127);
		}
		int devnull = open("/dev/null", O_RDWR | O_CLOEXEC);
		if (devnull >= 0) {
			dup2(devnull, STDIN_FILENO);
			dup2(devnull, STDOUT_FILENO);
			dup2(devnull, STDERR_FILENO);
			if (devnull > 2) {
				close(devnull);
			}
		}
		execv(argv[0], argv);
		_exit(127);
	}
	int status = 0;
	if (timeout_sec <= 0) {
		if (waitpid(pid, &status, 0) < 0) {
			return -1;
		}
	} else {
		time_t deadline = time(NULL) + timeout_sec;
		for (;;) {
			pid_t w = waitpid(pid, &status, WNOHANG);
			if (w == pid) {
				break;
			}
			if (w < 0) {
				return -1;
			}
			if (time(NULL) >= deadline) {
				kill(pid, SIGKILL);
				waitpid(pid, &status, 0);
				return -1;
			}
			struct timespec ts = {0, 50 * 1000 * 1000};
			nanosleep(&ts, NULL);
		}
	}
	if (WIFEXITED(status) && WEXITSTATUS(status) == 0) {
		return 0;
	}
	return -1;
}

int fz_run(char *const argv[], int timeout_sec)
{
	return fz_run_in(NULL, argv, timeout_sec);
}

int fz_comp_zstd(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n, int level)
{
	size_t bound = ZSTD_compressBound(n);
	uint8_t *buf = (uint8_t *)malloc(bound ? bound : 1);
	if (buf == NULL) {
		return -1;
	}
	size_t z = ZSTD_compress(buf, bound, src, n, level);
	if (ZSTD_isError(z)) {
		free(buf);
		return -1;
	}
	*out = buf;
	*out_n = z;
	return 0;
}

int fz_decomp_zstd(const uint8_t *src, size_t n, uint8_t *dst, size_t dst_n)
{
	size_t z = ZSTD_decompress(dst, dst_n, src, n);
	if (ZSTD_isError(z) || z != dst_n) {
		return -1;
	}
	return 0;
}

int fz_comp_brotli(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n, int quality)
{
	size_t bound = BrotliEncoderMaxCompressedSize(n);
	if (bound == 0) {
		bound = n + 1024;
	}
	uint8_t *buf = (uint8_t *)malloc(bound);
	if (buf == NULL) {
		return -1;
	}
	size_t used = bound;
	int lgwin = n >= (1u << 20) ? 24 : 22;
	if (!BrotliEncoderCompress(quality, lgwin, BROTLI_MODE_GENERIC, n, src, &used, buf)) {
		free(buf);
		return -1;
	}
	*out = buf;
	*out_n = used;
	return 0;
}

int fz_decomp_brotli(const uint8_t *src, size_t n, uint8_t *dst, size_t dst_n)
{
	size_t used = dst_n;
	if (BrotliDecoderDecompress(n, src, &used, dst) != BROTLI_DECODER_RESULT_SUCCESS ||
	    used != dst_n) {
		return -1;
	}
	return 0;
}

int fz_comp_zlib(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n, int level)
{
	uLong bound = compressBound((uLong)n);
	uint8_t *buf = (uint8_t *)malloc(bound ? bound : 1);
	if (buf == NULL) {
		return -1;
	}
	uLongf used = bound;
	if (compress2(buf, &used, src, (uLong)n, level) != Z_OK) {
		free(buf);
		return -1;
	}
	*out = buf;
	*out_n = (size_t)used;
	return 0;
}

int fz_decomp_zlib(const uint8_t *src, size_t n, uint8_t *dst, size_t dst_n)
{
	uLongf used = (uLongf)dst_n;
	if (uncompress(dst, &used, src, (uLong)n) != Z_OK || used != dst_n) {
		return -1;
	}
	return 0;
}

static int run_inout(enum fz_tool_id tid, char *const argv[],
                     const uint8_t *src, size_t n,
                     const char *out_path, uint8_t **out, size_t *out_n,
                     int timeout)
{
	(void)tid;
	if (fz_run(argv, timeout) != 0) {
		return -1;
	}
	if (fz_read_file(out_path, out, out_n) != 0) {
		return -1;
	}
	(void)src;
	(void)n;
	return 0;
}

int fz_comp_bsc(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n)
{
	struct fz_tool t;
	if (!fz_tool_find(FZ_TOOL_BSC, &t)) {
		return -1;
	}
	char dir[512], in[600], ot[600], bflag[32];
	if (fz_mkdtemp_dir(dir, sizeof(dir)) != 0) {
		return -1;
	}
	snprintf(in, sizeof(in), "%s/in", dir);
	snprintf(ot, sizeof(ot), "%s/out", dir);
	if (n >= 10000) {
		snprintf(bflag, sizeof(bflag), "-b%zu", n);
	} else {
		snprintf(bflag, sizeof(bflag), "-b1");
	}
	if (fz_write_file(in, src, n) != 0) {
		fz_rm_rf(dir);
		return -1;
	}
	char *argv[] = {t.path, "e", in, ot, "-e2", bflag, "-H16", "-M128", NULL};
	int rc = run_inout(FZ_TOOL_BSC, argv, src, n, ot, out, out_n, 0);
	fz_rm_rf(dir);
	return rc;
}

int fz_decomp_bsc(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n)
{
	struct fz_tool t;
	if (!fz_tool_find(FZ_TOOL_BSC, &t)) {
		return -1;
	}
	char dir[512], in[600], ot[600];
	if (fz_mkdtemp_dir(dir, sizeof(dir)) != 0) {
		return -1;
	}
	snprintf(in, sizeof(in), "%s/in", dir);
	snprintf(ot, sizeof(ot), "%s/out", dir);
	if (fz_write_file(in, src, n) != 0) {
		fz_rm_rf(dir);
		return -1;
	}
	char *argv[] = {t.path, "d", in, ot, NULL};
	int rc = run_inout(FZ_TOOL_BSC, argv, src, n, ot, out, out_n, 0);
	fz_rm_rf(dir);
	return rc;
}

int fz_comp_zpaq(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n)
{
	if (fz_comp_zpaq_stream(src, n, out, out_n) == 0) {
		return 0;
	}
	struct fz_tool t;
	if (!fz_tool_find(FZ_TOOL_ZPAQ, &t)) {
		return -1;
	}
	char dir[512], payload[600], archive[600];
	if (fz_mkdtemp_dir(dir, sizeof(dir)) != 0) {
		return -1;
	}
	snprintf(payload, sizeof(payload), "%s/p", dir);
	snprintf(archive, sizeof(archive), "%s/a.zpaq", dir);
	if (fz_write_file(payload, src, n) != 0) {
		fz_rm_rf(dir);
		return -1;
	}
	/* Relative names so the archive does not bake in the temp path. */
	char *argv[] = {t.path, "a", "a.zpaq", "p", "-m5", "-noattributes", "-force", NULL};
	int rc = -1;
	if (fz_run_in(dir, argv, 0) == 0) {
		rc = fz_read_file(archive, out, out_n);
	}
	fz_rm_rf(dir);
	return rc;
}

int fz_decomp_zpaq(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n)
{
	if (fz_decomp_zpaq_stream(src, n, out, out_n) == 0) {
		return 0;
	}
	struct fz_tool t;
	if (!fz_tool_find(FZ_TOOL_ZPAQ, &t)) {
		return -1;
	}
	char dir[512], archive[600], payload[600];
	if (fz_mkdtemp_dir(dir, sizeof(dir)) != 0) {
		return -1;
	}
	snprintf(archive, sizeof(archive), "%s/a.zpaq", dir);
	snprintf(payload, sizeof(payload), "%s/p", dir);
	if (fz_write_file(archive, src, n) != 0) {
		fz_rm_rf(dir);
		return -1;
	}
	char *argv[] = {t.path, "x", "a.zpaq", "-force", NULL};
	int rc = -1;
	if (fz_run_in(dir, argv, 0) == 0) {
		rc = fz_read_file(payload, out, out_n);
	}
	fz_rm_rf(dir);
	return rc;
}

int fz_comp_paq8px(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n, int textlike)
{
	struct fz_tool t;
	if (!fz_tool_find(FZ_TOOL_PAQ8PX, &t) || fz_env_flag("FZCODEC_DISABLE_PAQ")) {
		return -1;
	}
	char dir[512], in[600], ot[600], level[16];
	if (fz_mkdtemp_dir(dir, sizeof(dir)) != 0) {
		return -1;
	}
	snprintf(in, sizeof(in), "%s/in", dir);
	snprintf(ot, sizeof(ot), "%s/out", dir);
	snprintf(level, sizeof(level), "%s", textlike ? "-5T" : "-5");
	if (fz_write_file(in, src, n) != 0) {
		fz_rm_rf(dir);
		return -1;
	}
	int timeout = (int)fz_env_u64("FZCODEC_PAQ_TIMEOUT_SEC", 0);
	char *argv[] = {t.path, level, in, ot, NULL};
	int rc = -1;
	if (fz_run(argv, timeout) == 0) {
		rc = fz_read_file(ot, out, out_n);
	}
	fz_rm_rf(dir);
	return rc;
}

int fz_decomp_paq8px(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n)
{
	struct fz_tool t;
	if (!fz_tool_find(FZ_TOOL_PAQ8PX, &t)) {
		return -1;
	}
	char dir[512], in[600], ot[600];
	if (fz_mkdtemp_dir(dir, sizeof(dir)) != 0) {
		return -1;
	}
	snprintf(in, sizeof(in), "%s/in.paq8px215", dir);
	snprintf(ot, sizeof(ot), "%s/out", dir);
	if (fz_write_file(in, src, n) != 0) {
		fz_rm_rf(dir);
		return -1;
	}
	char *argv[] = {t.path, "-d", in, ot, NULL};
	int rc = -1;
	if (fz_run(argv, 0) == 0) {
		rc = fz_read_file(ot, out, out_n);
	}
	fz_rm_rf(dir);
	return rc;
}

int fz_comp_lepton(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n)
{
	struct fz_tool t;
	if (!fz_tool_find(FZ_TOOL_LEPTON, &t)) {
		return -1;
	}
	char dir[512], in[600], ot[600];
	if (fz_mkdtemp_dir(dir, sizeof(dir)) != 0) {
		return -1;
	}
	snprintf(in, sizeof(in), "%s/in.jpg", dir);
	snprintf(ot, sizeof(ot), "%s/out.lep", dir);
	if (fz_write_file(in, src, n) != 0) {
		fz_rm_rf(dir);
		return -1;
	}
	char *argv[] = {
		t.path, "-memory=256M", "-threadmemory=64M", "-allowprogressive",
		in, ot, NULL
	};
	int rc = -1;
	if (fz_run(argv, 120) == 0) {
		rc = fz_read_file(ot, out, out_n);
		if (rc == 0 && *out_n == 0) {
			free(*out);
			*out = NULL;
			rc = -1;
		}
	}
	fz_rm_rf(dir);
	return rc;
}

int fz_decomp_lepton(const uint8_t *src, size_t n, uint8_t **out, size_t *out_n)
{
	struct fz_tool t;
	if (!fz_tool_find(FZ_TOOL_LEPTON, &t)) {
		return -1;
	}
	char dir[512], in[600], ot[600];
	if (fz_mkdtemp_dir(dir, sizeof(dir)) != 0) {
		return -1;
	}
	snprintf(in, sizeof(in), "%s/in.lep", dir);
	snprintf(ot, sizeof(ot), "%s/out.jpg", dir);
	if (fz_write_file(in, src, n) != 0) {
		fz_rm_rf(dir);
		return -1;
	}
	char *argv[] = {
		t.path, "-memory=256M", "-threadmemory=64M", "-allowprogressive",
		in, ot, NULL
	};
	int rc = -1;
	if (fz_run(argv, 120) == 0) {
		rc = fz_read_file(ot, out, out_n);
	}
	fz_rm_rf(dir);
	return rc;
}
