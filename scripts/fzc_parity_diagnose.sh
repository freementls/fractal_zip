#!/usr/bin/env bash
# Diagnose local vs live fractal_zip web parity (tools, PHP limits, .fz round-trip).
#
# Usage:
#   scripts/fzc_parity_diagnose.sh
#   scripts/fzc_parity_diagnose.sh --fzc /path/to/file.fz
#   scripts/fzc_parity_diagnose.sh --live-url 'https://example.com/examples/fzc_capability_report.php?json=1'
#
# Always ends with benchmarks/kill_stray_bench_procs.sh if a stray encode left zpaq running.

set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"

FZC=""
LIVE_URL=""
KEEP_OUT=0
OUT_DIR="${TMPDIR:-/tmp}/fz_cap_$$"
mkdir -p "$OUT_DIR"
cleanup() {
	benchmarks/kill_stray_bench_procs.sh 2>/dev/null || true
	if [[ "$KEEP_OUT" -eq 0 ]]; then
		rm -rf "$OUT_DIR"
	fi
}
trap cleanup EXIT

while [[ $# -gt 0 ]]; do
	case "$1" in
		--fzc) FZC="$2"; shift 2 ;;
		--live-url) LIVE_URL="$2"; shift 2 ;;
		--keep-out-dir) KEEP_OUT=1; shift ;;
		-h|--help)
			echo "Usage: $0 [--fzc PATH] [--live-url URL] [--keep-out-dir]"
			echo "  Uses Authorization: Bearer when FZC_WEB_API_SECRET is set (curl to live)."
			exit 0
			;;
		*) echo "Unknown arg: $1" >&2; exit 2 ;;
	esac
done

LOCAL_JSON="$OUT_DIR/local.json"
LOCAL_WEB_JSON="$OUT_DIR/local-web-sim.json"
LIVE_JSON="$OUT_DIR/live.json"

echo "[1/4] Local CLI capability report → $LOCAL_JSON"
php -d opcache.enable_cli=0 examples/fzc_capability_report.php --label=local-cli --json --out="$LOCAL_JSON" || true

echo "[2/4] Local web-simulation (--as-web) → $LOCAL_WEB_JSON"
php -d opcache.enable_cli=0 examples/fzc_capability_report.php --label=local-web-sim --as-web --json --out="$LOCAL_WEB_JSON" || true

FZC_ARGS=()
if [[ -n "$FZC" ]]; then
	FZC_ARGS=(--fzc="$FZC")
	echo "[3/4] Round-trip probe on $FZC"
	php -d opcache.enable_cli=0 examples/fzc_capability_report.php --label=local-roundtrip "${FZC_ARGS[@]}" --json --out="$OUT_DIR/roundtrip.json" || true
else
	echo "[3/4] Skip round-trip (pass --fzc=PATH to test a specific archive)"
fi

if [[ -n "$LIVE_URL" ]]; then
	echo "[4/4] Fetch live report → $LIVE_JSON"
	if command -v curl >/dev/null 2>&1; then
		CURL_AUTH=()
		if [[ -n "${FZC_WEB_API_SECRET:-}" ]]; then
			CURL_AUTH=(-H "Authorization: Bearer ${FZC_WEB_API_SECRET}")
		fi
		curl -fsSL "${CURL_AUTH[@]}" "$LIVE_URL" -o "$LIVE_JSON" || echo "WARN: curl failed for live URL" >&2
	else
		echo "WARN: curl not installed; set live JSON manually at $LIVE_JSON" >&2
	fi
	if [[ -f "$LIVE_JSON" ]]; then
		php -d opcache.enable_cli=0 examples/fzc_capability_compare.php "$LOCAL_WEB_JSON" "$LIVE_JSON" || true
		php -d opcache.enable_cli=0 examples/fzc_capability_compare.php --json "$LOCAL_WEB_JSON" "$LIVE_JSON" > "$OUT_DIR/compare.json" || true
	fi
else
	echo "[4/4] Skip live compare (pass --live-url='https://YOUR/site/examples/fzc_capability_report.php?json=1')"
	echo "      Or open in browser: examples/fzc_capability_report.php?json=1"
fi

echo ""
echo "Reports in $OUT_DIR"
if [[ -f "$LOCAL_JSON" ]]; then
	python3 -c "import json;d=json.load(open('$LOCAL_JSON'));print('local gaps:',len(d.get('parity_gaps',[])),'zpaq',d.get('external_tools',{}).get('zpaq',{}).get('path'))" 2>/dev/null || true
fi
if [[ -f "$LOCAL_WEB_JSON" ]]; then
	echo ""
	echo "--- local web-sim (human) ---"
	php -d opcache.enable_cli=0 examples/fzc_capability_report.php --label=local-web-sim --as-web --human 2>/dev/null | head -20 || true
fi
