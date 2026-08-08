#!/usr/bin/env bash
# One-change dict screen: ram7 then optional full confirm.
# Usage: PROFILE=ram7 DIC=... bash benchmarks/run_hutter_dict_screen.sh
#        PROFILE=mmap  DIC=... bash benchmarks/run_hutter_dict_screen.sh
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CACHE="$ROOT/benchmarks/.ladder_cache"
PROFILE="${PROFILE:-ram7}"
DIC="${DIC:?set DIC=path/to.dic}"
SLICE="${SLICE:-$CACHE/enwik8_1m_skip10.mid}"
BEST_FULL=193498
BEST_RAM7=198920
NAME=$(basename "$DIC" .dic)
case "$PROFILE" in
  ram7) CMIX="$ROOT/tools/hutter/fx2-cmix/run/cmix_ram7"; BEST=$BEST_RAM7 ;;
  mmap) CMIX="$ROOT/tools/hutter/fx2-cmix/run/cmix_mmap"; BEST=$BEST_FULL ;;
  *) echo "PROFILE=ram7|mmap"; exit 1 ;;
esac
[[ -x "$CMIX" ]] || { echo "missing $CMIX"; exit 1; }
OUT="$CACHE/dict_${PROFILE}_${NAME}.fx2"
RT="$CACHE/dict_${PROFILE}_${NAME}.out"
LOG="$CACHE/dict_${PROFILE}_${NAME}.log"
rm -f "$OUT" "$OUT".cmix.temp "$RT"
echo "=== dict screen profile=$PROFILE dic=$DIC best=$BEST ==="
nice -n 19 "$CMIX" -c "$DIC" "$SLICE" "$OUT" >"$LOG" 2>&1
BYTES=$(stat -c%s "$OUT")
GATE=reject
if (( BYTES < BEST )); then GATE=KEEP; fi
echo "bytes=$BYTES gate=$GATE (best=$BEST)"
printf '{"tag":"dict_screen","profile":"%s","dic":"%s","bytes":%s,"best":%s,"gate":"%s"}\n' \
  "$PROFILE" "$NAME" "$BYTES" "$BEST" "$GATE" | tee -a "$ROOT/benchmarks/.hutter_dict_screens.jsonl"
if [[ "$GATE" == KEEP ]]; then
  nice -n 19 "$CMIX" -d "$DIC" "$OUT" "$RT" >>"$LOG" 2>&1
  cmp -s "$SLICE" "$RT" && echo RT_OK || { echo RT_FAIL; exit 1; }
fi
