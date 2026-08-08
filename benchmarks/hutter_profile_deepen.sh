#!/usr/bin/env bash
# After consistent ladder finishes, deepen WITHIN the winning profile only.
# Order: dict replace (same profile) → WT_AUX (same profile binary+dict).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
RUN="$ROOT/tools/hutter/fx2-cmix/run"
LOG="$ROOT/benchmarks/.hutter_logs/consistent_ladder.log"
DEEP="$ROOT/benchmarks/.hutter_logs/profile_deepen.log"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
B1=198744
B10=1751514
JSONL="$ROOT/benchmarks/.hutter_slice_screens.jsonl"

log() { echo "$(date -Iseconds) $*" >>"$DEEP"; }

# Wait for RESUME's B0 10m jsonl row (log may contain a stale prior DONE).
log "deepen watcher: waiting for B0_entityfold_10m in jsonl after RESUME"
for i in $(seq 1 2400); do
  if (( i % 10 == 1 )); then log "still waiting (minute ~$i)"; fi
  if rg -q 'ladder RESUME' "$LOG" 2>/dev/null \
     && rg -q '"name":"B0_entityfold_10m"' "$JSONL" 2>/dev/null \
     && rg -q 'B0 10m gate=' "$LOG" 2>/dev/null; then
    log "RESUME+B0 complete detected"
    break
  fi
  if (( i == 2400 )); then log "TIMEOUT"; exit 1; fi
  sleep 60
done

python3 - <<'PY' >>"$DEEP"
import json
from pathlib import Path
rows={}
for l in Path("/srv/http/fractal_zip/benchmarks/.hutter_slice_screens.jsonl").read_text().splitlines():
    if not l.strip(): continue
    try: r=json.loads(l)
    except: continue
    rows[r["name"]]=r
for k in ("A1_dict365e9_1m","A1_dict365e9_10m","B0_entityfold_1m","B0_entityfold_10m"):
    r=rows.get(k)
    if r: print(f"{k}: delta={r.get('delta')} gate={r.get('gate')} bytes={r.get('bytes')}")
    else: print(f"{k}: MISSING")

def score(name):
    r=rows.get(name)
    if not r or r.get("rt")!="OK": return 999999
    d=r.get("delta")
    return 999999 if d is None else d

a1=score("A1_dict365e9_10m")
b0=score("B0_entityfold_10m")
print(f"COMPARE A1_10m_delta={a1} B0_10m_delta={b0}")
if a1>=0 and b0>=0:
    print("WINNER=none")
    open("/tmp/hutter_winner","w").write("none\n")
elif a1<=b0:
    print("WINNER=A")
    open("/tmp/hutter_winner","w").write("A\n")
else:
    print("WINNER=B")
    open("/tmp/hutter_winner","w").write("B\n")
PY

WIN=$(cat /tmp/hutter_winner 2>/dev/null || echo none)
log "winner=$WIN"

guard() {
  HUTTER_MAX_SWAP_MIB=4096 bash "$ROOT/benchmarks/hutter_memory_guard.sh" >>"$DEEP" 2>&1
}
wait_guard() {
  for i in $(seq 1 120); do guard && return 0; sleep 30; done; return 2
}
screen() {
  local tag="$1" cmix="$2" dict="$3" slice="$4" base="$5"
  wait_guard || { log "guard timeout before $tag"; return 2; }
  log "SCREEN $tag cmix=$(basename "$cmix") dict=$(basename "$dict")"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" "$tag" "$cmix" "$dict" "$slice" "$base" >>"$DEEP" 2>&1
  rm -f "$RUN/ppm.temp"
  log "RESULT $tag: $(tail -1 "$JSONL")"
}

DICT_A="$ROOT/benchmarks/.ladder_cache/dicts/english_append365_e9.dic"
DICT_A2="$ROOT/benchmarks/.ladder_cache/dicts/english_replace_low_e9.dic"
DICT_B="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
DICT_B1="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_replace_e9.dic"
DICT_STOCK="$ROOT/tools/hutter/fx2-cmix/dictionary/english.dic"

DICT_QUOT="$ROOT/benchmarks/.ladder_cache/dicts/english_quot_only.dic"

if [[ "$WIN" == "A" ]]; then
  # Attribution: stock+quot alone vs A1's full append365 (1m is enough).
  if [[ -f "$DICT_QUOT" ]]; then
    screen A_quot_only_1m "$RUN/cmix_lto" "$DICT_QUOT" "$SL1" "$B1" || true
  fi
  screen A2_replace_1m "$RUN/cmix_lto" "$DICT_A2" "$SL1" "$B1" || true
  screen A2_replace_10m "$RUN/cmix_lto" "$DICT_A2" "$SL10" "$B10" || true
  # Prefer replace dict for WT_AUX if A2 10m was negative; else append365
  USE="$DICT_A"
  if python3 - <<'PY'
import json
from pathlib import Path
rows={}
for l in Path("/srv/http/fractal_zip/benchmarks/.hutter_slice_screens.jsonl").read_text().splitlines():
    try: r=json.loads(l)
    except Exception: continue
    rows[r["name"]]=r
a1=rows.get("A1_dict365e9_10m",{}).get("delta")
a2=rows.get("A2_replace_10m",{}).get("delta")
raise SystemExit(0 if (a2 is not None and a1 is not None and a2 < a1) else 1)
PY
  then
    USE="$DICT_A2"
  fi
  log "WT_AUX on Profile A with $(basename "$USE") (replace only if better than A1)"
  screen A_wt_aux_1m "$RUN/cmix_wt_aux" "$USE" "$SL1" "$B1" || true
  screen A_wt_aux_10m "$RUN/cmix_wt_aux" "$USE" "$SL10" "$B10" || true

elif [[ "$WIN" == "B" ]]; then
  screen B1_entityfold_replace_1m "$RUN/cmix_entity" "$DICT_B1" "$SL1" "$B1" || true
  screen B1_entityfold_replace_10m "$RUN/cmix_entity" "$DICT_B1" "$SL10" "$B10" || true
  # Prefer replace dict only if it BEATS the profile base (more negative than B0/A1),
  # not merely if it beats the A0 baseline.
  USE="$DICT_B"
  if python3 - <<'PY'
import json
from pathlib import Path
rows={}
for l in Path("/srv/http/fractal_zip/benchmarks/.hutter_slice_screens.jsonl").read_text().splitlines():
    try: r=json.loads(l)
    except Exception: continue
    rows[r["name"]]=r
b0=rows.get("B0_entityfold_10m",{}).get("delta")
b1=rows.get("B1_entityfold_replace_10m",{}).get("delta")
raise SystemExit(0 if (b1 is not None and b0 is not None and b1 < b0) else 1)
PY
  then
    USE="$DICT_B1"
  fi
  log "WT_AUX on Profile B with $(basename "$USE") (replace only if better than B0)"
  screen B_wt_aux_1m "$RUN/cmix_wt_aux_entity" "$USE" "$SL1" "$B1" || true
  screen B_wt_aux_10m "$RUN/cmix_wt_aux_entity" "$USE" "$SL10" "$B10" || true

else
  log "neither A1 nor B0 negative at 10m — still probe WT_AUX on stock A0 (consistent)"
  screen A0_wt_aux_1m "$RUN/cmix_wt_aux" "$DICT_STOCK" "$SL1" "$B1" || true
  screen A0_wt_aux_10m "$RUN/cmix_wt_aux" "$DICT_STOCK" "$SL10" "$B10" || true
fi
log "deepen done"
