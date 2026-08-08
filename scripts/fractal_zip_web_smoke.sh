#!/usr/bin/env bash
# Aggregate smoke: undeep FZB/Brotli marker sync + web-download helpers + hostile container paths (no HTTP server).
# Native-folder FZLB / compact 7z round-trips follow; each skips with exit 0 when tar/brotli/7z are missing (same as tests/run_php_smokes.sh phase 8).
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
php "$ROOT/benchmarks/check_undeep_fzb_brotli_sync.php"
php "$ROOT/benchmarks/smoke_hostile_container_inputs.php"
php "$ROOT/benchmarks/smoke_inspect_container_web_fs.php"
php "$ROOT/benchmarks/fz_fzlb_decode_roundtrip_smoke.php"
php "$ROOT/benchmarks/fz_fz7_compact_folder_roundtrip_smoke.php"
php "$ROOT/benchmarks/fz_fz7_undeep_folder_roundtrip_smoke.php"
php "$ROOT/benchmarks/smoke_fzc_web_download_range.php"
php "$ROOT/benchmarks/smoke_fzc_capability_report.php"
php "$ROOT/benchmarks/smoke_parity_hint_strings.php"
php "$ROOT/benchmarks/smoke_cli_member_list_parity_hint.php"
echo "OK fractal_zip_web_smoke (all steps)"
