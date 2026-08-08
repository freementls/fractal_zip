#!/usr/bin/env bash
# Wire probe + cap sweep after legacy phda9 gate completes (no SA re-mine).
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$REPO/benchmarks/logs/cfabb_enwik8_wire_$(date +%Y%m%d_%H%M%S).log"
PHP="php -d memory_limit=768M"
exec > >(tee -a "$LOG") 2>&1
run() { echo "=== $(date -Is) $* ==="; "$@"; }
cd "$REPO"

OUT_384P="$REPO/benchmarks/.enwik8_cfabb_table_384p_phda9.json"
for pid in $(pgrep -f 'filter_cfabb_phda9.php --pages=384.*384p_filtered' || true); do
	echo "waiting for legacy phda9 gate pid=$pid ..."
	while kill -0 "$pid" 2>/dev/null; do sleep 120; done
done

if [[ ! -f "$OUT_384P" ]]; then
	echo "missing $OUT_384P — legacy phda9 gate not finished"
	exit 1
fi

run $PHP benchmarks/bench_enwik8_wire_slice_probe.php \
	--pages=384 \
	--case=split_inner_phda9_xml_single_stream_lstm_wiki_lom \
	--out-json="$REPO/benchmarks/.enwik8_wire_slice_probe_384p_wiki_lom_cfabb.json"

run $PHP benchmarks/bench_wiki_lom_cfabb_cap_sweep.php \
	--pages=384 \
	--table="$OUT_384P" \
	--caps=0,64,128,256

echo "=== cfabb legacy wire log=$LOG ==="
