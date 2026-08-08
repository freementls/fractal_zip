#!/usr/bin/env bash
# parallel_paq: build tools, slice bench, mono_mi wire cases @384p.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
export FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0
PHP=(php -d memory_limit=4096M)
LOG="$ROOT/benchmarks/logs/parallel_paq_probe.log"
mkdir -p "$(dirname "$LOG")"

{
	echo "=== $(date -Iseconds) parallel_paq probe ==="
	echo "[1] smoke + build"
	"${PHP[@]}" benchmarks/smoke_parallel_paq.php
	echo "[2] enwik slice bench (raw parallel_paq vs gzip)"
	"${PHP[@]}" benchmarks/bench_parallel_paq_enwik_slice.php --pages=384
	CASES="split_inner_fztx_mono_mi,split_inner_fztx_mono_mi_parallel_cmix,split_inner_fztx_mono_mi_parallel_phda9,split_inner_fztx_mono_mi_parallel_cmix_force,split_inner_fztx_mono_mi_parallel_phda9_force,split_inner_fztx_mono_concat_stat_pred_inner_parallel_cmix"
	echo "[3] wire probe: $CASES"
	"${PHP[@]}" benchmarks/bench_enwik8_wire_slice_probe.php --pages=384 --cases="$CASES"
	echo "=== done ==="
} 2>&1 | tee "$LOG"
