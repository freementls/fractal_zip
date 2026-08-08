#!/usr/bin/env bash
# After 10MB fx2_c_dict finishes: score vs phda9, optional RT, gate next rung.
# Usage: bash benchmarks/run_hutter_ladder_after_10m.sh
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CMIX="$ROOT/tools/hutter/fx2-cmix/run/cmix_orig"
DICT="$ROOT/tools/hutter/fx2-cmix/dictionary/english.dic"
OUT="$ROOT/benchmarks/.hutter_size_ladder.jsonl"
PHDA9_10M=1767997
BEST_1M=193498
COMP=/tmp/ladder_10m_fx2_c_dict.out
LOG=/tmp/ladder_10m_fx2_c_dict.log
SLICE=/tmp/enwik8_10m.bin

if [[ ! -s "$COMP" ]]; then
  echo "missing/empty $COMP — 10MB compress not finished yet" >&2
  if [[ -f "$LOG" ]]; then
    tail -c 120 "$LOG" | tr '\r' '\n' | tail -3 >&2
  fi
  exit 2
fi

BYTES=$(stat -c%s "$COMP")
BPC=$(python3 -c "print(f'{$BYTES*8/10485760:.4f}')")
DELTA=$((BYTES - PHDA9_10M))
if (( BYTES < PHDA9_10M )); then GATE=KEEP_10m; else GATE=reject_10m; fi
echo "10m fx2_c_dict bytes=$BYTES bpc=$BPC delta_vs_phda9=$DELTA gate=$GATE"
printf '{"tag":"10m","name":"fx2_c_dict","plain":10485760,"bytes":%s,"bpc":%s,"gate":"%s","delta_vs_phda9":%s}\n' \
  "$BYTES" "$BPC" "$GATE" "$DELTA" | tee -a "$OUT"

if [[ "$GATE" != KEEP_10m ]]; then
  echo "No 10MB KEEP — stay on 1MB (best=$BEST_1M). Next: pruned-dict hunt when RAM free."
  echo "  nice -n 19 $CMIX -c /tmp/ladder_1m_dict_slice_hits.dic /tmp/enwik8_1m.bin /tmp/ladder_1m_fx2_pruned.out"
  exit 0
fi

echo "=== RT 10MB (nice 19) ==="
nice -n 19 "$CMIX" -d "$DICT" "$COMP" /tmp/ladder_10m_fx2_c_dict.rt > /tmp/ladder_10m_fx2_c_dict_rt.log 2>&1
cmp -s "$SLICE" /tmp/ladder_10m_fx2_c_dict.rt && echo RT_OK || { echo RT_FAIL; exit 1; }

echo "=== 100MB gate (full enwik8) — only after RT_OK ==="
echo "Run manually when ready (hours, ~4GiB RSS):"
echo "  nice -n 19 $CMIX -c $DICT $ROOT/enwik8 /tmp/ladder_100m_fx2_c_dict.out"
echo "  # compare to: nice -n 19 tools/phda9/phda9 C $ROOT/enwik8 /tmp/ladder_100m_phda9.out"
