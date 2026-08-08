#!/usr/bin/env bash
# Resume wire460 dict refine @96p: skip finished steps, low priority throughout.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"
export FRACTAL_ZIP_NO_CLI_OPCACHE_REEXEC=1

PAGES="${1:-96}"
TRIALS="${2:-24}"
SKIP_REFINE="${SKIP_REFINE:-0}"
LOG="$REPO/benchmarks/logs/wire460_dict_refine_${PAGES}p_continue.log"
mkdir -p "$REPO/benchmarks/logs"

run_low() {
	if command -v ionice >/dev/null 2>&1; then
		ionice -c 3 nice -n 19 "$@"
	else
		nice -n 19 "$@"
	fi
}

exec > >(tee -a "$LOG") 2>&1
echo "=== wire460 dict refine continue @${PAGES}p trials=${TRIALS} $(date -Is) ==="

DICT="$REPO/benchmarks/.phda9_external_dict_lstm_seed_refine_${PAGES}p.txt"
REFINE_JSON="$REPO/benchmarks/.enwik8_phda9_dict_lstm_seed_refine_${PAGES}p.json"

if [[ "$SKIP_REFINE" != "1" ]]; then
	echo "--- LSTM dict refine (expanded add/remove trials) ---"
	run_low php -d memory_limit=4096M "$REPO/benchmarks/bench_phda9_dict_lstm_seed_refine.php" \
		--pages="$PAGES" \
		--trials="$TRIALS" \
		--seed=4096p
	run_low php "$REPO/benchmarks/patch_dict_refine_amort_json.php" --pages="$PAGES"
fi

if [[ ! -s "$DICT" ]]; then
	echo "FAIL: missing refined dict $DICT"
	exit 1
fi

CASES="split_inner_phda9_xml_single_stream_lstm_words4096_dict,split_inner_phda9_xml_single_stream_lstm_refined_dict"
export FRACTAL_ZIP_WIRE_PROBE_AMORT_PHDA9_DICT=1
export FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1

for OUTER in zstd zpaq auto; do
	OUT="$REPO/benchmarks/.enwik8_wire460_dict_refine_${PAGES}p_${OUTER}.json"
	if [[ -f "$OUT" ]] && [[ "$OUTER" == "zstd" ]]; then
		echo "--- skip wire probe outer=${OUTER} (exists) ---"
		continue
	fi
	echo "--- wire probe outer=${OUTER} @${PAGES}p (amort phda9 dict) ---"
	run_low php -d memory_limit=2048M "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
		--pages="$PAGES" \
		--outer="$OUTER" \
		--cases="$CASES" \
		--verify-rt \
		--out-json="$OUT"
done

run_low php "$REPO/benchmarks/bench_wire460_dict_refine_summary.php" --pages="$PAGES"

echo "--- nested outer matrix @${PAGES}p ---"
run_low bash "$REPO/benchmarks/run_wire460_nested_outer.sh" "$PAGES" 3 "$DICT" || true

echo "=== continue done $(date -Is) ==="
