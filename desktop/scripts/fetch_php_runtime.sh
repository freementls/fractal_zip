#!/usr/bin/env bash
# Optional: stage a PHP CLI binary into desktop/runtimes/<os>/ for bundling.
#
# This script does not download by default (URLs and builds vary). It documents
# the expected layout and can copy from a local path:
#
#   desktop/scripts/fetch_php_runtime.sh linux /path/to/php
#   desktop/scripts/fetch_php_runtime.sh windows /path/to/php.exe
#   desktop/scripts/fetch_php_runtime.sh macos /path/to/php
#
# After staging, re-run desktop/scripts/package.sh so the zip includes runtime/.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DESKTOP_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

OS="${1:-}"
SRC="${2:-}"

usage() {
	echo "Usage: $0 <linux|macos|windows> <path-to-php-binary>" >&2
	echo "Stages into desktop/runtimes/<os>/ for package.sh to bundle." >&2
	exit 1
}

[[ -n "$OS" && -n "$SRC" ]] || usage
[[ -f "$SRC" ]] || { echo "Not a file: $SRC" >&2; exit 1; }

case "$OS" in
	linux|macos|windows) ;;
	*) usage ;;
esac

DEST="$DESKTOP_ROOT/runtimes/$OS"
mkdir -p "$DEST"

if [[ "$OS" == "windows" ]]; then
	cp -a "$SRC" "$DEST/php.exe"
	chmod +x "$DEST/php.exe" 2>/dev/null || true
	echo "Staged $DEST/php.exe"
else
	cp -a "$SRC" "$DEST/php"
	chmod +x "$DEST/php"
	echo "Staged $DEST/php"
fi

echo "Next: desktop/scripts/package.sh $OS"
