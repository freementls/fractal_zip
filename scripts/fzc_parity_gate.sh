#!/usr/bin/env bash
# Fail if this host has error-level parity gaps (pre-deploy / CI gate on the machine that will serve extract).
#
# Usage:
#   scripts/fzc_parity_gate.sh
#   scripts/fzc_parity_gate.sh --as-web
#   scripts/fzc_parity_gate.sh --out /tmp/capability.json
#   FZC_PARITY_GATE_LIBRARY_ONLY=1 scripts/fzc_parity_gate.sh   # CI: PHP + library deploy only
#
# Exit 0 = no error-level gaps in fzc_capability_report parity_gaps.

set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"

AS_WEB=0
OUT=""
while [[ $# -gt 0 ]]; do
	case "$1" in
		--as-web) AS_WEB=1; shift ;;
		--out) OUT="$2"; shift 2 ;;
		--library-only)
			export FZC_PARITY_GATE_LIBRARY_ONLY=1
			shift
			;;
		-h|--help)
			echo "Usage: $0 [--as-web] [--out path.json] [--library-only]"
			echo "  --library-only  ignore missing zpaq/7z/arc (for CI runners without apt tools)"
			exit 0
			;;
		*) echo "Unknown arg: $1" >&2; exit 2 ;;
	esac
done

TMP_OUT=""
if [[ -z "$OUT" ]]; then
	TMP_OUT="$(mktemp)"
	OUT="$TMP_OUT"
fi
ARGS=(--label=parity-gate --json --out="$OUT")
if [[ "$AS_WEB" -eq 1 ]]; then
	ARGS+=(--as-web)
fi

php -d opcache.enable_cli=0 examples/fzc_capability_report.php "${ARGS[@]}"
if [[ -n "$TMP_OUT" ]]; then
	rm -f "$TMP_OUT"
fi
if [[ -n "${FZC_PARITY_GATE_LIBRARY_ONLY:-}" && "${FZC_PARITY_GATE_LIBRARY_ONLY}" != "0" ]]; then
	echo "OK fzc_parity_gate (library-only: PHP + deploy; install zpaq/7z/arc on extract host)"
else
	echo "OK fzc_parity_gate (no error-level host gaps)"
fi
