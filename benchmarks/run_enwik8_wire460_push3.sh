#!/usr/bin/env bash
# Re-probe lstm + pp96 @384p with external dict explicitly off (true baseline vs 524 KiB).
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
export FRACTAL_ZIP_LOW_MEMORY=1
PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-768M}"
LOG_DIR="$REPO/benchmarks/logs"
mkdir -p "$LOG_DIR"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$LOG_DIR/wire460_push3_${STAMP}.log"
OUT_JSON="$REPO/benchmarks/.enwik8_wire460_nodict_${STAMP}.json"
DICT="$REPO/benchmarks/.phda9_external_dict.txt"
DICT_BAK="$REPO/benchmarks/.phda9_external_dict.txt.push3_bak"

exec > >(tee -a "$LOG") 2>&1
echo "wire460_push3 start $(date -Iseconds) — lstm/pp96 no external dict"

if [[ -f "$DICT" ]]; then
  mv -f "$DICT" "$DICT_BAK"
  echo "moved external dict aside"
fi

CASES='split_inner_phda9_xml_single_stream_lstm,split_inner_phda9_xml_single_stream_pp96'
nice -n 19 php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
  --pages=384 --cases="$CASES" --out-json="$OUT_JSON" --verify-rt \
  | tee "$LOG_DIR/wire460_nodict_probe_384p_${STAMP}.log" || true

if [[ -f "$DICT_BAK" ]]; then
  mv -f "$DICT_BAK" "$DICT"
fi

echo ""
nice -n 19 php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/summarize_research_probe.php" \
  --json="$OUT_JSON" --pages=384 || true

echo "wire460_push3 done $(date -Iseconds) → $OUT_JSON"
