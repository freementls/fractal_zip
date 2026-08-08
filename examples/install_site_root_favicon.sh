#!/usr/bin/env sh
# Copy fractal_zip favicon to the vhost document root so GET /favicon.ico stops 404ing.
# Usage: ./install_site_root_favicon.sh /path/to/vhost/document/root
set -e
SRC="$(cd "$(dirname "$0")" && pwd)/favicon.ico"
DEST="${1:?document root required}"
if [ ! -f "$SRC" ]; then
	echo "missing $SRC" >&2
	exit 1
fi
cp -a "$SRC" "$DEST/favicon.ico"
echo "installed $DEST/favicon.ico ($(wc -c < "$SRC") bytes)"
