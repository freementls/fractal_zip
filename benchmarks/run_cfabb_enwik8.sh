#!/usr/bin/env bash
# cfabb substring pipeline for enwik8: mine @384p, phda9-gate @384p, wire confirm @384p.
# Serial, 768M — do not run parallel phda9 batches.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$REPO/benchmarks/logs/cfabb_enwik8_$(date +%Y%m%d_%H%M%S).log"
PHP="php -d memory_limit=768M"
exec > >(tee -a "$LOG") 2>&1
run() { echo "=== $(date -Is) $* ==="; "$@"; }
cd "$REPO"

TABLE_384="$REPO/benchmarks/.enwik8_cfabb_table_384p.json"
TABLE_384F="$REPO/benchmarks/.enwik8_cfabb_table_384p_filtered.json"
OUT_384P="$REPO/benchmarks/.enwik8_cfabb_table_384p_phda9.json"

if [[ ! -f "$TABLE_384F" ]]; then
	run $PHP benchmarks/mine_collision_free_abbrevs.php \
		--pages=384 \
		--out="$TABLE_384F" \
		--max-entries=4096
fi

run $PHP benchmarks/filter_cfabb_phda9.php --pages=384 \
	--in="$TABLE_384F" \
	--out="$OUT_384P" \
	--pool=512 --max=256

run $PHP benchmarks/bench_enwik8_wire_slice_probe.php \
	--pages=384 \
	--case=split_inner_phda9_xml_single_stream_lstm_wiki_lom \
	--out-json="$REPO/benchmarks/.enwik8_wire_slice_probe_384p_wiki_lom_cfabb.json"

run $PHP benchmarks/bench_wiki_lom_cfabb_cap_sweep.php \
	--pages=384 \
	--table="$OUT_384P" \
	--caps=0,64,128,256

echo "=== cfabb enwik8 log=$LOG ==="
