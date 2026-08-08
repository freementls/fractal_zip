#!/usr/bin/env bash
# Final 384p sweep: harmony text-pack, semantic, entry_sort_off, corpus vs mono_mi.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
export FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0
PHP=(php -d memory_limit=4096M)
LOG="$ROOT/benchmarks/logs/remaining_levers_probe.log"
mkdir -p "$(dirname "$LOG")"
CASES="split_inner_fztx_mono_mi,split_inner_fztx_mono_mi_text_pack,split_text_inner_mi_semantic,entry_sort_off_text_inner_mi,split_inner_fztx_mono_mi_corpus"
{
	echo "=== $(date -Iseconds) remaining levers @384p ==="
	echo "cases: $CASES"
	"${PHP[@]}" benchmarks/bench_enwik8_wire_slice_probe.php --pages=384 --cases="$CASES"
	cp "$ROOT/benchmarks/.enwik8_wire_slice_probe.json" "$ROOT/benchmarks/.enwik8_remaining_levers_probe.json"
	echo "=== done ==="
} 2>&1 | tee "$LOG"
