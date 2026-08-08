#!/usr/bin/env bash
# After l2_entity_10m lands in jsonl: close or keep L2, then LSTM320 Pareto,
# then optionally queue match3m 100m (time-safe path). One cmix at a time.
set -uo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$ROOT/benchmarks/.hutter_logs/after_l2_screens.log"
JSONL="$ROOT/benchmarks/.hutter_slice_screens.jsonl"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
B0_1M=198793   # B0 entityfold @1m
B0_10M=1750933 # 1751514-581
A0_1M=198744
A0_10M=1751514
export HUTTER_MAX_SWAP_MIB="${HUTTER_MAX_SWAP_MIB:-5120}"

exec >>"$LOG" 2>&1
echo "=== after_l2 START=$(date -Iseconds) ==="

log() { echo "$(date -Iseconds) $*"; }

# Wait for l2_entity_10m result
for i in $(seq 1 120); do
  if rg -q '"name":"l2_entity_10m"' "$JSONL" 2>/dev/null; then
    break
  fi
  log "waiting for l2_entity_10m ($i/120)"
  sleep 60
done

python3 - <<'PY'
import json, re
from pathlib import Path
from datetime import datetime
ROOT=Path("/srv/http/fractal_zip")
rows=[]
for l in (ROOT/"benchmarks/.hutter_slice_screens.jsonl").read_text().splitlines():
    if not l.strip():
        continue
    # sanitize control chars that older screens embedded in elapsed_* fields
    l2=re.sub(r"[\x00-\x1f]", " ", l)
    try:
        rows.append(json.loads(l2))
    except json.JSONDecodeError:
        continue
l2=[r for r in rows if r.get("name")=="l2_entity_10m"]
assert l2, "missing l2_entity_10m"
r=l2[-1]
b0_10=1750933
a0=1751514
delta_a0=r["bytes"]-a0
delta_b0=r["bytes"]-b0_10
verdict="KEEP_vs_B0" if r["bytes"]<b0_10 else "CLOSE_vs_B0"
print(f"l2_10m bytes={r['bytes']} dA0={delta_a0:+d} dB0={delta_b0:+d} rt={r.get('rt')} -> {verdict}")
Path("/tmp/l2_10m_verdict").write_text(verdict+"\n")
PY

VERDICT=$(cat /tmp/l2_10m_verdict)
log "verdict=$VERDICT"

# Always run LSTM320 Pareto next (time/byte path) once L2 cmix is done
while pgrep -a cmix 2>/dev/null | rg -q 'cmix_'; do
  log "wait for cmix idle"
  sleep 30
done
bash "$ROOT/benchmarks/hutter_memory_guard.sh" || { log "mem guard fail"; exit 2; }

CMIX320="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_lstm320"
if [[ -x "$CMIX320" ]]; then
  log "LSTM320 1m"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" B_m3l320_1m "$CMIX320" "$DICT" "$SL1" "$A0_1M" || true
  log "LSTM320 10m"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" B_m3l320_10m "$CMIX320" "$DICT" "$SL10" "$A0_10M" || true
else
  log "skip LSTM320 — missing binary"
fi

# If L2 closed, optional wiki-only / abjad-only 1m attribution when binaries exist
if [[ "$VERDICT" == CLOSE_vs_B0 ]]; then
  for pair in "cmix_l2_wiki_only:l2_wiki_only_1m" "cmix_l2_abjad_only:l2_abjad_only_1m"; do
    bin="${pair%%:*}"; tag="${pair##*:}"
    path="$ROOT/tools/hutter/fx2-cmix/run/$bin"
    if [[ -x "$path" ]]; then
      log "attribution $tag"
      bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" "$tag" "$path" "$DICT" "$SL1" "$A0_1M" || true
    else
      log "skip $tag — build make ${bin#cmix_} first"
    fi
  done
fi

log "=== after_l2 DONE=$(date -Iseconds) ==="
echo "Next manual: bash benchmarks/run_hutter_100m_match3m.sh  # time-safe 100m"
