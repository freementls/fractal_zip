#!/usr/bin/env bash
# Print the latest Tik/Tok winner banner from benchmarks/.tiktok_sweep_*.
# If wins artifacts are missing, generate them from the latest sweep directory.
set -euo pipefail

REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"

DIRS="$(ls -dt benchmarks/.tiktok_sweep_* 2>/dev/null || true)"
if [[ -z "$DIRS" ]]; then
	echo "tiktok_latest_wins: no benchmarks/.tiktok_sweep_* directory found" >&2
	exit 1
fi

FOUND=""
while IFS= read -r d; do
	[[ -z "$d" ]] && continue
	if [[ -f "$d/wins_banner.txt" ]]; then
		FOUND="$d"
		break
	fi
	if php benchmarks/tiktok_wins_report.php --out-dir="$d" >/dev/null 2>&1; then
		FOUND="$d"
		break
	fi
done <<< "$DIRS"

if [[ -z "$FOUND" || ! -f "$FOUND/wins_banner.txt" ]]; then
	echo "tiktok_latest_wins: no valid sweep with winners found" >&2
	exit 1
fi

echo "latest_sweep: $FOUND"
cat "$FOUND/wins_banner.txt"
