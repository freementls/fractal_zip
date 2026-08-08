#!/usr/bin/env bash
# Build fractal_zip desktop release packages (native window app + PHP payload).
#
# Usage:
#   desktop/scripts/package.sh              # build current-OS binary + all payload zips
#   desktop/scripts/package.sh linux        # linux zip only (builds Rust binary here)
#   desktop/scripts/package.sh windows      # windows zip (payload + .cmd stub until CI binary)
#
# Real OS window binary is built with: cargo build --release (desktop/app)
# Cross-OS .exe / .app need builds on those hosts or GitHub Actions — see docs/DESKTOP.md.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DESKTOP_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
APP_DIR="$DESKTOP_ROOT/app"
DIST="$DESKTOP_ROOT/dist"
VERSION="$(tr -d '[:space:]' < "$DESKTOP_ROOT/VERSION")"
HOST_OS="$(uname -s | tr '[:upper:]' '[:lower:]')"
case "$HOST_OS" in
	linux*) HOST_OS=linux ;;
	darwin*) HOST_OS=macos ;;
	mingw*|msys*|cygwin*) HOST_OS=windows ;;
esac

TARGETS=("$@")
if [[ ${#TARGETS[@]} -eq 0 ]]; then
	TARGETS=(linux macos windows)
fi

bash "$SCRIPT_DIR/assemble_payload.sh" "$DESKTOP_ROOT/.payload_build"

# Build native binary for this host when any requested target matches.
NEED_NATIVE=0
for t in "${TARGETS[@]}"; do
	if [[ "$t" == "$HOST_OS" ]]; then
		NEED_NATIVE=1
		break
	fi
done

NATIVE_BIN=""
if [[ "$NEED_NATIVE" -eq 1 ]]; then
	echo "Building native fz-desktop ($HOST_OS)…"
	(cd "$APP_DIR" && cargo build --release)
	if [[ "$HOST_OS" == "windows" ]]; then
		NATIVE_BIN="$APP_DIR/target/release/fz-desktop.exe"
	else
		NATIVE_BIN="$APP_DIR/target/release/fz-desktop"
	fi
	[[ -x "$NATIVE_BIN" || -f "$NATIVE_BIN" ]] || {
		echo "Native binary missing: $NATIVE_BIN" >&2
		exit 1
	}
fi

mkdir -p "$DIST"
README_TXT="$DESKTOP_ROOT/.payload_build/README.txt"
cat > "$README_TXT" <<EOF
fractal_zip desktop ${VERSION}
===========================

Native window app that runs the same compress / extract UI as the web demos.

Start
-----
  Linux / macOS:  ./fz-desktop
  Windows:        fz-desktop.exe   (or fz-desktop.cmd if only the stub is present)

Requires PHP 8.1+ on PATH, or a binary under runtime/ (php / php.exe).

Close the window to stop. Jobs are stored under your user data directory.

Rebuild after fz changes: bump VERSION, run desktop/scripts/package.sh, attach
zips to a GitHub Release when the milestone is ready (docs/DESKTOP.md).
EOF

package_one() {
	local os="$1"
	local name="fractal_zip-desktop-${VERSION}-${os}-x86_64"
	local stage="$DIST/$name"
	rm -rf "$stage"
	mkdir -p "$stage/payload" "$stage/runtime"

	cp -a "$DESKTOP_ROOT/.payload_build/." "$stage/payload/"
	# VERSION at zip root (binary reads it) and inside payload (hub page).
	cp -a "$DESKTOP_ROOT/VERSION" "$stage/VERSION"
	cp -a "$README_TXT" "$stage/README.txt"

	local have_native=0
	if [[ "$os" == "$HOST_OS" && -n "$NATIVE_BIN" ]]; then
		if [[ "$os" == "windows" ]]; then
			cp -a "$NATIVE_BIN" "$stage/fz-desktop.exe"
		else
			cp -a "$NATIVE_BIN" "$stage/fz-desktop"
			chmod +x "$stage/fz-desktop"
		fi
		have_native=1
	fi

	# Fallback launchers when this host cannot produce that OS's binary yet.
	case "$os" in
		linux|macos)
			if [[ "$have_native" -eq 0 ]]; then
				cp -a "$DESKTOP_ROOT/launchers/fz-desktop" "$stage/fz-desktop"
				chmod +x "$stage/fz-desktop"
				echo "NOTE: This zip has the shell launcher only. Build on $os (or CI) for the native window binary." > "$stage/NATIVE_BINARY_MISSING.txt"
			fi
			;;
		windows)
			if [[ "$have_native" -eq 0 ]]; then
				cp -a "$DESKTOP_ROOT/launchers/fz-desktop.cmd" "$stage/fz-desktop.cmd"
				echo "NOTE: Build on Windows (cargo build --release in desktop/app) for fz-desktop.exe." > "$stage/NATIVE_BINARY_MISSING.txt"
			fi
			# Always ship .cmd as emergency fallback.
			cp -a "$DESKTOP_ROOT/launchers/fz-desktop.cmd" "$stage/fz-desktop.cmd"
			;;
		*)
			echo "Unknown OS target: $os" >&2
			exit 1
			;;
	esac

	local rt="$DESKTOP_ROOT/runtimes/$os"
	if [[ -d "$rt" ]]; then
		cp -a "$rt/." "$stage/runtime/"
		if [[ "$os" != "windows" && -x "$stage/runtime/bin/php" && ! -e "$stage/runtime/php" ]]; then
			ln -sfn bin/php "$stage/runtime/php" 2>/dev/null || cp -a "$stage/runtime/bin/php" "$stage/runtime/php"
		fi
	fi
	if [[ ! "$(ls -A "$stage/runtime" 2>/dev/null || true)" ]]; then
		echo "No bundled PHP — app uses system php on PATH." > "$stage/runtime/README.txt"
	fi

	(
		cd "$DIST"
		rm -f "${name}.zip" "${name}.tar.gz"
		if command -v zip >/dev/null 2>&1; then
			zip -qr "${name}.zip" "$name"
		else
			tar -czf "${name}.tar.gz" "$name"
		fi
	)
	echo "Built $DIST/${name}.zip"
	rm -rf "$stage"
}

for t in "${TARGETS[@]}"; do
	package_one "$t"
done

rm -rf "$DESKTOP_ROOT/.payload_build"
echo "Done. Artifacts in $DIST/"
ls -lh "$DIST"/fractal_zip-desktop-"${VERSION}"-* 2>/dev/null || true
