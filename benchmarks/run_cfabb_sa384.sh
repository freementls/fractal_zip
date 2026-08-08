#!/usr/bin/env bash
# After legacy phda9 gate: mine @384p with SA, gate, wire probe, cap sweep.
# Serial, ~768M for phda9 steps; mine uses 1536M when RAM allows.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$REPO/benchmarks/logs/cfabb_sa384_$(date +%Y%m%d_%H%M%S).log"
PHP768="php -d memory_limit=768M"
PHP1536="php -d memory_limit=1536M"
exec > >(tee -a "$LOG") 2>&1
run() { echo "=== $(date -Is) $* ==="; "$@"; }
cd "$REPO"

export FRACTAL_ZIP_CFABB_SA=1

MINED="$REPO/benchmarks/.enwik8_cfabb_table_384p_sa_mined.json"
OUT_384P="$REPO/benchmarks/.enwik8_cfabb_table_384p_sa_phda9.json"

# Wait for any in-flight legacy filter (serial phda9).
for pid in $(pgrep -f 'filter_cfabb_phda9.php --pages=384' || true); do
	echo "waiting for filter_cfabb_phda9 pid=$pid ..."
	while kill -0 "$pid" 2>/dev/null; do sleep 120; done
done

if [[ ! -f "$MINED" ]]; then
	run $PHP1536 benchmarks/mine_collision_free_abbrevs.php \
		--pages=384 \
		--out="$MINED" \
		--max-entries=4096
fi

run $PHP768 benchmarks/filter_cfabb_phda9.php --pages=384 \
	--in="$MINED" \
	--out="$OUT_384P" \
	--pool=512 --max=256

run $PHP768 benchmarks/bench_enwik8_wire_slice_probe.php \
	--pages=384 \
	--case=split_inner_phda9_xml_single_stream_lstm_wiki_lom \
	--out-json="$REPO/benchmarks/.enwik8_wire_slice_probe_384p_wiki_lom_cfabb_sa.json"

run $PHP768 benchmarks/bench_wiki_lom_cfabb_cap_sweep.php \
	--pages=384 \
	--table="$OUT_384P" \
	--caps=0,64,128,256

echo "=== cfabb SA384 log=$LOG ==="
