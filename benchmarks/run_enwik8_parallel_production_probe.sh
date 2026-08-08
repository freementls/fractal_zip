#!/usr/bin/env bash
# Verify PIPELINE_PARALLEL=1 + production parallel env: same wire bytes, faster encode.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
export FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0
PHP=(php -d memory_limit=4096M)
LOG="$ROOT/benchmarks/logs/parallel_production_probe.log"
mkdir -p "$(dirname "$LOG")"

{
	echo "=== $(date -Iseconds) parallel production wire A/B ==="
	CASES="split_inner_fztx_mono_mi,split_inner_fztx_mono_mi_parallel"
	echo "cases: $CASES"
	"${PHP[@]}" benchmarks/bench_enwik8_wire_slice_probe.php --pages=384 --cases="$CASES"
	echo "=== done ==="
} 2>&1 | tee "$LOG"
