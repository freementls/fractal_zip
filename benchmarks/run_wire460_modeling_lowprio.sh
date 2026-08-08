#!/usr/bin/env bash
# Low-priority wire460 modeling continuation (384p validation subset).
#
# Skips 96p losers (layout/text_pack/corpus) and cfabb_plain (encode-time hog).
# Arms: baseline, cfabb_table, siteinfo, phda9_article prose split.
#
# Usage:
#   bash benchmarks/run_wire460_modeling_lowprio.sh          # 384p subset
#   bash benchmarks/run_wire460_modeling_lowprio.sh full     # all 11 arms @384p
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
export FRACTAL_ZIP_BACKGROUND=1
export FRACTAL_ZIP_SUPPRESS_HTML=1

PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-2048M}"
LOCK="$REPO/benchmarks/logs/.phda9_encode.lock"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$REPO/benchmarks/logs/wire460_modeling_lowprio_${STAMP}.log"
MODE="${1:-subset}"
mkdir -p "$REPO/benchmarks/logs"

SUBSET_CASES="split_inner_phda9_xml_single_stream_lstm_words4096_dict,split_inner_phda9_xml_single_stream_lstm_words4096_cfabb_plain_table,split_inner_phda9_xml_single_stream_lstm_words4096_siteinfo,split_inner_phda9_article_single_stream_lstm_words4096_dict"

run_phda9() {
	echo ""
	echo "=== $(date -Is) $* ==="
	if command -v ionice >/dev/null 2>&1; then
		flock -w 86400 "$LOCK" ionice -c 3 nice -n 19 "$@" \
			|| echo "WARN exit $? from: $*"
	else
		flock -w 86400 "$LOCK" nice -n 19 "$@" \
			|| echo "WARN exit $? from: $*"
	fi
}

exec > >(tee -a "$LOG") 2>&1
echo "wire460_modeling_lowprio start $(date -Is) mode=${MODE} mem=${PHP_MEM} target=460096 baseline384=518508"

if [[ "$MODE" == "full" ]]; then
	run_phda9 php -d "memory_limit=${PHP_MEM}" \
		"$REPO/benchmarks/bench_wire460_modeling_sweep.php" \
		--pages=384
else
	run_phda9 php -d "memory_limit=${PHP_MEM}" \
		"$REPO/benchmarks/bench_wire460_modeling_sweep.php" \
		--pages=384 \
		--cases="$SUBSET_CASES"
fi

echo "wire460_modeling_lowprio done $(date -Is) log=${LOG}"
