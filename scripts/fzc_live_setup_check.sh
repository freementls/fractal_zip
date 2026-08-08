#!/usr/bin/env bash
# Pre-deploy compatibility gate for live .fz compress/extract hosts.
# Ensures the web host can handle tool-dependent outers before serving traffic.
set -euo pipefail

REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"

echo "[1/2] CLI parity gate"
bash scripts/fzc_parity_gate.sh

echo "[2/2] Web parity gate"
bash scripts/fzc_parity_gate.sh --as-web

if [[ "${FZC_WEB_ENFORCE_EXTRACT_COMPAT:-}" != "1" ]]; then
	echo ""
	echo "WARN: FZC_WEB_ENFORCE_EXTRACT_COMPAT is not enabled."
	echo "      Recommended for production to fail fast on tool drift:"
	echo "      export FZC_WEB_ENFORCE_EXTRACT_COMPAT=1"
fi

echo ""
echo "OK live setup check (host can extract tool-dependent outers)."
