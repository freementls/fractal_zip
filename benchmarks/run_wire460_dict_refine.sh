#!/usr/bin/env bash
# wire460: LSTM phda9 dict refine (real compress trials) + wire probes across outers.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1

PAGES="${1:-96}"
TRIALS="${2:-24}"
LOG="$REPO/benchmarks/logs/wire460_dict_refine_${PAGES}p.log"
mkdir -p "$REPO/benchmarks/logs"

exec > >(tee -a "$LOG") 2>&1
echo "=== wire460 dict refine @${PAGES}p trials=${TRIALS} $(date -Is) ==="

nice -n 19 php -d memory_limit=4096M "$REPO/benchmarks/bench_phda9_dict_lstm_seed_refine.php" \
	--pages="$PAGES" \
	--trials="$TRIALS" \
	--seed=4096p

DICT="$REPO/benchmarks/.phda9_external_dict_lstm_seed_refine_${PAGES}p.txt"
if [[ ! -s "$DICT" ]]; then
	echo "FAIL: missing refined dict $DICT"
	exit 1
fi

CASES="split_inner_phda9_xml_single_stream_lstm_words4096_dict,split_inner_phda9_xml_single_stream_lstm_refined_dict"
export FRACTAL_ZIP_WIRE_PROBE_AMORT_PHDA9_DICT=1
for OUTER in zstd zpaq auto; do
	echo "--- wire probe outer=${OUTER} @${PAGES}p (amort phda9 dict) ---"
	nice -n 19 php -d memory_limit=2048M "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
		--pages="$PAGES" \
		--outer="$OUTER" \
		--cases="$CASES" \
		--verify-rt \
		--out-json="$REPO/benchmarks/.enwik8_wire460_dict_refine_${PAGES}p_${OUTER}.json"
done

php "$REPO/benchmarks/bench_wire460_dict_refine_summary.php" --pages="$PAGES"

echo "--- nested outer matrix (phda9 inner + container outer) @${PAGES}p ---"
nice -n 19 bash "$REPO/benchmarks/run_wire460_nested_outer.sh" "$PAGES" 3 "$DICT" || true

echo "=== done $(date -Is) ==="
