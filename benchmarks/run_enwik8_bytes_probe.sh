#!/usr/bin/env bash
# Subset-first enwik8 bytes probe (see benchmarks/ENWIK8_BYTES_PROBE.md)
set -euo pipefail
cd "$(dirname "$0")/.."
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
COUNT="${1:-20}"
php benchmarks/build_enwik8_sample_pages.php "--count=${COUNT}"
php -d memory_limit=2048M benchmarks/bench_enwik8_text_codec_lab.php --quick
php benchmarks/summarize_enwik8_text_codec_lab.php
php -d memory_limit=2048M benchmarks/bench_enwik8_content_probe.php --quick
php benchmarks/summarize_enwik8_content_probe.php
php benchmarks/bench_enwik8_sidecar_probe.php
echo "Next full encode: transform=none (see run_enwik8_textcodec_encode.php)"
