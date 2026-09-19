#!/bin/sh
# Build a local Squash 0.8 (library only) into tools/fzcodec/.deps/squash-prefix
# so `make plugin` and `make test-plugin` work without a system libsquash-dev.
set -eu
ROOT=$(CDPATH= cd -- "$(dirname "$0")/.." && pwd)
DEPS="$ROOT/.deps"
SRC="$DEPS/squash"
BLD="$DEPS/squash-build"
PRE="$DEPS/squash-prefix"
mkdir -p "$DEPS"
if [ ! -d "$SRC/.git" ]; then
	git clone --depth 1 https://github.com/quixdb/squash.git "$SRC"
fi
cd "$SRC"
git submodule update --init --depth 1 squash/tinycthread squash/hedley utils/parg tests/munit
# glibc C11 once_flag conflicts with bundled tinycthread (cannot #define
# once_flag as pthread_once_t). Keep C11's type and call pthread_once.
TT="$SRC/squash/tinycthread/source/tinycthread.h"
if grep -q 'define once_flag pthread_once_t' "$TT" 2>/dev/null; then
	python3 - "$TT" <<'PY'
import pathlib, sys
p = pathlib.Path(sys.argv[1])
t = p.read_text()
old = """#else
  #define once_flag pthread_once_t
  #define ONCE_FLAG_INIT PTHREAD_ONCE_INIT
#endif

/** Invoke a callback exactly once
 * @param flag Flag used to ensure the callback is invoked exactly
 *        once.
 * @param func Callback to invoke.
 */
#if defined(_TTHREAD_WIN32_)
  void call_once(once_flag *flag, void (*func)(void));
#else
  #define call_once(flag,func) pthread_once(flag,func)
#endif"""
new = """#else
  /* glibc C11 typedefs once_flag in <stdlib.h>; do not alias pthread_once_t. */
  #include <stdlib.h>
  #ifndef ONCE_FLAG_INIT
  typedef pthread_once_t once_flag;
  #define ONCE_FLAG_INIT PTHREAD_ONCE_INIT
  #endif
  static inline void squash_tt_call_once(once_flag *flag, void (*func)(void)) {
    pthread_once((pthread_once_t *)(void *)flag, func);
  }
  #define call_once(flag,func) squash_tt_call_once((flag),(func))
#endif"""
if old not in t:
    sys.exit("tinycthread.h: expected once_flag block not found")
p.write_text(t.replace(old, new, 1))
print("patched tinycthread.h for glibc C11 once_flag")
PY
fi
rm -rf "$BLD"
mkdir -p "$BLD" "$PRE"
PLUGINS="brieflz brotli bsc bzip2 copy crush csc density doboz fari fastlz gipfeli heatshrink libdeflate lz4 lzf lzfse lzg lzham lzjb lzma lzo miniz ms-compress ncompress quicklz snappy wflz yalz77 zlib zlib-ng zling zpaq zstd"
cd "$BLD"
set -- -DCMAKE_BUILD_TYPE=Release -DCMAKE_INSTALL_PREFIX="$PRE" -DCMAKE_POLICY_VERSION_MINIMUM=3.5 -DENABLE_COVERAGE=no
for p in $PLUGINS; do
	u=$(echo "$p" | tr '[:lower:]' '[:upper:]' | tr '-' '_')
	set -- "$@" -DENABLE_$u=no
done
cmake "$@" "$SRC"
cmake --build . --target squash0.8 -j"$(nproc)"
INC="$PRE/include/squash-0.8"
LIB="$PRE/lib"
mkdir -p "$INC/squash/hedley" "$LIB/pkgconfig"
cp "$SRC/squash/squash.h" "$INC/"
cp "$SRC/squash"/squash-*.h "$INC/squash/"
cp "$BLD/squash/squash-version.h" "$INC/squash/"
cp "$SRC/squash/hedley/hedley.h" "$INC/squash/hedley/"
cp -a "$BLD/squash/libsquash0.8.so"* "$LIB/"
cat > "$LIB/pkgconfig/squash-0.8.pc" <<EOF
prefix=$PRE
libdir=\${prefix}/lib
includedir=\${prefix}/include/squash-0.8
Name: Squash
Description: Compression abstraction library
Version: 0.8.0
Libs: -L\${libdir} -lsquash0.8
Cflags: -I\${includedir}
EOF
echo "Installed $PRE — PKG_CONFIG_PATH=$LIB/pkgconfig"
