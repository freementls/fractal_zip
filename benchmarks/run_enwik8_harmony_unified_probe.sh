#!/usr/bin/env bash
# Harmony + unified inner probes (384p wire + 8 MiB inner unified A/B).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
export FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0
PHP=(php -d memory_limit=4096M)
LOG="$ROOT/benchmarks/logs/harmony_unified_probe.log"
mkdir -p "$(dirname "$LOG")"

{
	echo "=== $(date -Iseconds) harmony + unified probe ==="
	WIRE="no_textcodec,split_inner_fztx_mono_mi,pp96_siteinfo_no_textinner,split_inner_fztx_mono_mi_siteinfo,split_inner_fztx_mono_mi_harmony"
	echo "[1] wire 384p: $WIRE"
	"${PHP[@]}" benchmarks/bench_enwik8_wire_slice_probe.php --pages=384 --cases="$WIRE"
	cp "$ROOT/benchmarks/.enwik8_wire_slice_probe.json" "$ROOT/benchmarks/.enwik8_harmony_wire_probe.json"
	echo "[2] inner unified 8 MiB A/B"
	"${PHP[@]}" benchmarks/bench_enwik8_inner_unified_probe.php
	echo "=== done ==="
} 2>&1 | tee "$LOG"
