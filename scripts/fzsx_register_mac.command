#!/bin/bash
# macOS: associate .fzsx / .fzsxsd with fzsx-open (Terminal + php). Run once.
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
LAUNCHER="${SCRIPT_DIR}/fzsx-open"
chmod +x "${LAUNCHER}" 2>/dev/null || true

if command -v duti >/dev/null 2>&1; then
	# duti requires a bundle id; use a lightweight handler via Terminal
	duti -s com.apple.Terminal fzsx all 2>/dev/null || true
	duti -s com.apple.Terminal fzsxsd all 2>/dev/null || true
fi

echo "Add a custom Open With handler for .fzsx / .fzsxsd pointing to:"
echo "  ${LAUNCHER} \"%1\""
echo ""
echo "Or run from Terminal: php /path/to/file.fzsx[sd]"
echo ""
echo ".fzsxsd removes the archive after a successful extract."
echo "Optional: install duti (brew install duti) for finer-grained defaults."
