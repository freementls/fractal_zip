#!/usr/bin/env bash
# wire460 continue: skip dead-end prune/refine; run next tracks @ low priority (phda9 flock).
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"
export FRACTAL_ZIP_NO_CLI_OPCACHE_REEXEC=1
export FRACTAL_ZIP_BACKGROUND=1
export FRACTAL_ZIP_SUPPRESS_HTML=1

PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-2048M}"
LOCK="$REPO/benchmarks/logs/.phda9_encode.lock"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$REPO/benchmarks/logs/wire460_continue_${STAMP}.log"
TARGET=460096
SKIP_PHASE_A="${SKIP_PHASE_A:-0}"
SKIP_PHASE_B="${SKIP_PHASE_B:-0}"
SKIP_PHASE_C="${SKIP_PHASE_C:-0}"
CFABB_OUT="$REPO/benchmarks/.enwik8_cfabb_table_96p_phda9_v3.json"
CON_OUT="$REPO/benchmarks/.enwik8_consonant_table_96p_phda9_v2.json"
mkdir -p "$REPO/benchmarks/logs"

run_phda9() {
	echo ""
	echo "=== $(date -Is) $* ==="
	if command -v ionice >/dev/null 2>&1; then
		flock -w 86400 "$LOCK" ionice -c 3 nice -n 19 env "$@" \
			|| echo "WARN exit $? from: $*"
	else
		flock -w 86400 "$LOCK" nice -n 19 env "$@" \
			|| echo "WARN exit $? from: $*"
	fi
}

exec > >(tee -a "$LOG") 2>&1
echo "wire460_continue start $(date -Is) target=${TARGET} mem=${PHP_MEM} skipA=${SKIP_PHASE_A} skipB=${SKIP_PHASE_B} skipC=${SKIP_PHASE_C}"

# --- Phase A: @96p gates (serialized phda9 — parallel prep was OOM/kill-prone) ---
if [[ "$SKIP_PHASE_A" != "1" ]]; then
	if [[ -s "$CFABB_OUT" && -s "$CON_OUT" ]]; then
		echo "$(date -Is) phase A skip (cfabb + consonant outputs exist)"
	else
		run_phda9 FRACTAL_ZIP_CFABB_PHDA9_GATE=1 php -d "memory_limit=${PHP_MEM}" \
			"$REPO/benchmarks/filter_cfabb_phda9.php" \
			--pages=96 \
			--in="$REPO/benchmarks/.enwik8_cfabb_table_384p_filtered.json" \
			--out="$CFABB_OUT" \
			--pool=256 --max=128

		run_phda9 FRACTAL_ZIP_CONSONANT_PHDA9_GATE=1 php -d "memory_limit=${PHP_MEM}" \
			"$REPO/benchmarks/filter_consonant_phda9.php" \
			--pages=96 --pool=256 --max=128 \
			--out="$CON_OUT"

		run_phda9 php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_consonant_phda9_gate_sweep.php" \
			--pages=96 --quick
	fi
	echo "$(date -Is) phase A prep done"
fi

# --- Phase B: @96p wire compare (fast signal for cfabb / prize paths) ---
if [[ "$SKIP_PHASE_B" != "1" ]]; then
CASES96="split_inner_phda9_xml_single_stream_lstm_words4096_dict,split_inner_phda9_xml_single_stream_lstm_words4096_wiki_lom_cfabb_inline,split_inner_phda9_xml_single_stream_lstm_mixed_dict"
run_phda9 php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
	--pages=96 \
	--cases="$CASES96" \
	--verify-rt \
	--out-json="$REPO/benchmarks/.enwik8_wire460_continue_compare_96p_${STAMP}.json"
fi

# --- Phase C: mixed dict hunt @384p (serialized phda9) ---
if [[ "$SKIP_PHASE_C" != "1" ]]; then
run_phda9 php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_phda9_dict_mixed_hunt.php" \
	--pages=384 --refine-trials=24

if [[ -f "$REPO/benchmarks/.phda9_external_dict_mixed_best.txt" ]]; then
	cp -f "$REPO/benchmarks/.phda9_external_dict_mixed_best.txt" "$REPO/benchmarks/.phda9_external_dict.txt"
	echo "installed mixed_best dict ($(wc -c <"$REPO/benchmarks/.phda9_external_dict_mixed_best.txt") B)"
fi
fi

# --- Phase D: @384p wire probes (best-known arms + cfabb inline) ---
PROBE_CASES=(
	'split_inner_phda9_xml_single_stream_lstm_words4096_dict'
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict'
	'split_inner_phda9_xml_single_stream_lstm_words4096_wiki_lom_cfabb_inline'
	'split_inner_phda9_xml_single_stream_lstm_words4096_cfabb_plain_table'
)

for case in "${PROBE_CASES[@]}"; do
	run_phda9 php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
		--pages=384 \
		--case="$case" \
		--verify-rt \
		--out-json="$REPO/benchmarks/.enwik8_wire460_continue_${case}_${STAMP}.json" \
		|| true
done

php -d "memory_limit=256M" "$REPO/benchmarks/bench_wire460_continue_summary.php" \
	--stamp="$STAMP" --target="$TARGET" 2>/dev/null || true

php "$REPO/benchmarks/wire460_status.php" || true
echo "wire460_continue done $(date -Is) → $LOG"
