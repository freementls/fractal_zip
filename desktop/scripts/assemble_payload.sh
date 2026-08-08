#!/usr/bin/env bash
# Assemble payload/ from the fractal_zip repo (library + web demos + desktop hub).
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DESKTOP_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
REPO_ROOT="$(cd "$DESKTOP_ROOT/.." && pwd)"
OUT="${1:-$DESKTOP_ROOT/payload}"

rm -rf "$OUT"
mkdir -p "$OUT/examples" "$OUT/hub" "$OUT/examples/icons" "$OUT/examples/web_jobs"

# Core library peers + CLI entry (optional but useful in the same tree).
shopt -s nullglob
for f in "$REPO_ROOT"/fractal_zip.php \
	"$REPO_ROOT"/fractal_zip_*.php \
	"$REPO_ROOT"/fzc_archive_naming.php \
	"$REPO_ROOT"/fractal_zip_cli.php; do
	[[ -f "$f" ]] || continue
	cp -a "$f" "$OUT/"
done
shopt -u nullglob

# Web UI surface (compress / extract / shared / capability).
WEB_FILES=(
	fzc_compress.php
	fzc_extract.php
	fzc_web_shared.php
	fzc_deploy_bootstrap.php
	fz_local_env_bootstrap.php
	fz_fractal_local_env.php.example
	health.php
	fzc_capability_report.php
	fzc_capability_probes.php
	fzc_capability_compare.php
	fzc_capability_roundtrip_worker.php
	fzc_parity_hints.php
	bench_json_helpers.php
	bench_json_helpers.impl.php
	favicon.ico
)
for name in "${WEB_FILES[@]}"; do
	src="$REPO_ROOT/examples/$name"
	if [[ -f "$src" ]]; then
		cp -a "$src" "$OUT/examples/"
	fi
done

if [[ -d "$REPO_ROOT/examples/icons" ]]; then
	cp -a "$REPO_ROOT/examples/icons/." "$OUT/examples/icons/"
fi

# Keep web_jobs writable placeholder.
touch "$OUT/examples/web_jobs/.gitkeep"

cp -a "$DESKTOP_ROOT/hub/index.php" "$OUT/hub/index.php"
cp -a "$DESKTOP_ROOT/router.php" "$OUT/router.php"
cp -a "$DESKTOP_ROOT/VERSION" "$OUT/VERSION"

# Desktop env bootstrap: load example local env if present after package.
if [[ -f "$REPO_ROOT/examples/fz_fractal_local_env.php" ]]; then
	cp -a "$REPO_ROOT/examples/fz_fractal_local_env.php" "$OUT/examples/" || true
fi

count="$(find "$OUT" -type f | wc -l)"
size="$(du -sh "$OUT" | cut -f1)"
echo "Assembled payload: $OUT ($count files, $size)"
