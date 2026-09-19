#!/usr/bin/env bash
# Build fzcodec as a Squash in-tree plugin (the layout a quixdb PR would use).
# Uses the local clone from setup_local_squash.sh. Does not modify git remotes.
set -euo pipefail
ROOT=$(CDPATH= cd -- "$(dirname "$0")/.." && pwd)
SQ="${SQUASH_SRC:-$ROOT/.deps/squash}"
BLD="${SQUASH_INTREE_BUILD:-$ROOT/.deps/squash-intree-build}"
if [ ! -f "$SQ/plugins/CMakeLists.txt" ]; then
	echo "missing $SQ — run scripts/setup_local_squash.sh first" >&2
	exit 1
fi
ln -sfn "$ROOT" "$SQ/plugins/fzcodec"
LIST="$SQ/plugins/CMakeLists.txt"
if ! grep -q '^  fzcodec$' "$LIST"; then
	# Insert after "set (plugins_available" so ENABLE_FZCODEC works.
	python3 - "$LIST" <<'PY'
import pathlib, sys
p = pathlib.Path(sys.argv[1])
t = p.read_text()
old = "set (plugins_available\n  brieflz"
new = "set (plugins_available\n  fzcodec\n  brieflz"
if old not in t:
    sys.exit("plugins/CMakeLists.txt: expected plugins_available block not found")
p.write_text(t.replace(old, new, 1))
print("inserted fzcodec into plugins_available")
PY
fi
mkdir -p "$BLD"
# Disable every bundled codec except copy (tiny) and fzcodec.
CMAKE_ARGS=(-DCMAKE_BUILD_TYPE=Release -DCMAKE_POLICY_VERSION_MINIMUM=3.5 -DENABLE_COVERAGE=no -DENABLE_FZCODEC=ON)
for p in brieflz brotli bsc bzip2 crush csc density doboz fari fastlz gipfeli heatshrink libdeflate lz4 lzf lzfse lzg lzham lzjb lzma lzo miniz ms-compress ncompress quicklz snappy wflz yalz77 zlib zlib-ng zling zpaq zstd; do
	u=$(echo "$p" | tr '[:lower:]' '[:upper:]' | tr '-' '_')
	CMAKE_ARGS+=("-DENABLE_${u}=no")
done
cmake "${CMAKE_ARGS[@]}" -S "$SQ" -B "$BLD"
cmake --build "$BLD" --target squash0.8-plugin-fzcodec -j"$(nproc)"
# squash_plugin writes the .so + squash.ini next to plugins/fzcodec/plugins/squash
SO=$(find "$BLD" -name 'libsquash0.8-plugin-fzcodec.so' | head -1)
INI=$(find "$BLD" -path '*fzcodec*' -name squash.ini | head -1)
if [ -z "$SO" ] || [ -z "$INI" ]; then
	echo "in-tree plugin outputs missing" >&2
	exit 1
fi
STAGE="$BLD/squash-plugins/fzcodec"
mkdir -p "$STAGE"
cp -f "$SO" "$INI" "$STAGE/"
echo "staged $STAGE"
export SQUASH_PLUGINS="$BLD/squash-plugins"
export LD_LIBRARY_PATH="${BLD}/squash:${LD_LIBRARY_PATH:-}"
# Prefer the Makefile test binary if present; else skip.
TEST="$ROOT/build/test_squash_plugin"
if [ -x "$TEST" ]; then
	FZCODEC_DISABLE_PAQ=1 "$TEST"
else
	echo "built in-tree plugin; run make test-plugin from tools/fzcodec to exercise it"
fi
echo "in-tree squash plugin: ok"
