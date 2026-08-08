#!/usr/bin/env bash
# Fetch live fzc_capability_report.php JSON and compare to local web-simulation.
#
# Usage:
#   export FZC_WEB_API_SECRET='…'   # if production uses Bearer auth
#   scripts/fzc_parity_compare_live.sh 'https://YOUR_HOST/examples/fzc_capability_report.php?json=1'
#
# Exit 0 = compare --json ok:true (no material tool/PHP gaps on live vs local web-sim).

set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"

LIVE_URL="${1:-}"
if [[ -z "$LIVE_URL" ]]; then
	echo "Usage: $0 'https://HOST/examples/fzc_capability_report.php?json=1'" >&2
	exit 2
fi

OUT_DIR="${TMPDIR:-/tmp}/fz_cap_live_$$"
mkdir -p "$OUT_DIR"
trap 'rm -rf "$OUT_DIR"' EXIT

LOCAL="$OUT_DIR/local-web-sim.json"
LIVE="$OUT_DIR/live.json"

php -d opcache.enable_cli=0 examples/fzc_capability_report.php --label=local-web-sim --as-web --json --out="$LOCAL"

CURL_AUTH=()
if [[ -n "${FZC_WEB_API_SECRET:-}" ]]; then
	CURL_AUTH=(-H "Authorization: Bearer ${FZC_WEB_API_SECRET}")
fi
curl -fsSL "${CURL_AUTH[@]}" "$LIVE_URL" -o "$LIVE"

php -d opcache.enable_cli=0 examples/fzc_capability_compare.php "$LOCAL" "$LIVE"
php -d opcache.enable_cli=0 examples/fzc_capability_compare.php --json "$LOCAL" "$LIVE"
