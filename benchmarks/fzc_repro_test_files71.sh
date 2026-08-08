#!/usr/bin/env bash
# Reproduce the "best effort" .fz for test_files71 (Mutwa) built with this repo in Apr 2026.
# From repo root: bash benchmarks/fzc_repro_test_files71.sh
# One reference run: ~9.3 min wall, 2 972 392 B .fz (single PDF; YMMV with tool versions).
# Required: cjpeg+djpeg on PATH for FRACTAL_ZIP_PDF_CJPEG; jpegtran for DCT path.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
export FRACTAL_ZIP_PDF_JPEG_SEMANTIC=1
export FRACTAL_ZIP_PDF_JPEG_SEMANTIC_QUALITY_BIAS=-4
export FRACTAL_ZIP_PDF_CJPEG=1
export FRACTAL_ZIP_BROTLI_HUGE_MODE=full
# Bytes-first: do not set FRACTAL_ZIP_SPEED=1
# Adaptive min-savings + Mpix BPP policy (default in code); override only if you know what you are doing.
# FRACTAL_ZIP_LITERALPAC_PDF_QPDF=0  (Mutwa: qpdf was 0 savings in audit)
if [[ "${FRACTAL_ZIP_CLI_VERBOSE:-0}" == "1" ]]; then
  set -x
fi
export FRACTAL_ZIP_CLI_VERBOSE="${FRACTAL_ZIP_CLI_VERBOSE:-0}"
TARGET="test_files71"
if [[ ! -d "$TARGET" ]]; then
  echo "Missing $TARGET/ (put the PDF corpus there)" >&2
  exit 1
fi
rm -f "$TARGET.fz"
php "$ROOT/fractal_zip_cli.php" zip "$TARGET"
ls -la "$TARGET.fz"
sha256sum "$TARGET.fz" || shasum -a 256 "$TARGET.fz"
