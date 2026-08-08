#!/usr/bin/env bash
# Dev run: assemble payload beside the release binary and launch the native window.
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
APP="$ROOT/app"
STAGE="$ROOT/.dev_run"

bash "$ROOT/scripts/assemble_payload.sh" "$STAGE/payload"
cp -a "$ROOT/VERSION" "$STAGE/VERSION"
(cd "$APP" && cargo build --release)
cp -a "$APP/target/release/fz-desktop" "$STAGE/fz-desktop"
chmod +x "$STAGE/fz-desktop"
mkdir -p "$STAGE/runtime"
echo "Launching $STAGE/fz-desktop (native window)…"
cd "$STAGE"
exec ./fz-desktop
