#!/usr/bin/env bash
# Parallel enwik8 stat_pred_inner probes (separate PHP processes; host auto-parallelism inside each).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
LOG="$ROOT/benchmarks/logs"
mkdir -p "$LOG"
PHP=(php -d memory_limit=2048M)

MODEL="$ROOT/benchmarks/.stat_pred_inner_model.fzpm"
if [[ ! -f "$MODEL" ]]; then
	echo "[parallel] building frozen FZPM model..."
	"${PHP[@]}" benchmarks/build_stat_pred_inner_model.php
fi

run_bg() {
	local name="$1"
	shift
	echo "[parallel] starting $name"
	("$@" >"$LOG/${name}.log" 2>&1; echo "[parallel] done $name exit=$?" >>"$LOG/${name}.log") &
	echo $! >"$LOG/${name}.pid"
}

run_bg wire_384_base94_default "${PHP[@]}" benchmarks/bench_enwik8_wire_slice_probe.php \
	--pages=384 --cases=split_inner_fztx_mono_mi,split_inner_fztx_mono_mi_stat_pred_inner

run_bg wire_384_experiments "${PHP[@]}" benchmarks/bench_enwik8_wire_slice_probe.php \
	--pages=384 --cases=split_inner_fztx_mono_mi_stat_pred_inner_sparse,split_inner_fztx_mono_mi_stat_pred_inner_frozen_file,split_inner_fztx_mono_mi_stat_pred_inner_sparse_frozen,split_inner_fztx_mono_concat_stat_pred_inner

run_bg wire_768_base94 "${PHP[@]}" benchmarks/bench_enwik8_wire_slice_probe.php \
	--pages=768 --cases=split_inner_fztx_mono_mi,split_inner_fztx_mono_mi_stat_pred_inner_base94

run_bg verify_384_base94 "${PHP[@]}" benchmarks/verify_enwik_slice_roundtrip.php \
	--pages=384 --text-inner-promotion --text-inner-preprocess=stat_pred_inner

echo "parallel probes launched; logs in $LOG/{wire_384_base94_default,wire_384_experiments,wire_768_base94,verify_384_base94}.log"
wait
echo "all parallel probes finished"
