#!/usr/bin/env bash
# After LSTM320 screens finish: L2 attribution 1m (wiki vs abjad) then match3m 100m.
set -uo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$ROOT/benchmarks/.hutter_logs/after_lstm320_chain.log"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
JSONL="$ROOT/benchmarks/.hutter_slice_screens.jsonl"
export HUTTER_MAX_SWAP_MIB="${HUTTER_MAX_SWAP_MIB:-5120}"
exec >>"$LOG" 2>&1
echo "=== after_lstm320_chain START=$(date -Iseconds) ==="
log(){ echo "$(date -Iseconds) $*"; }

# Wait for B_m3l320_10m (or timeout after B_m3l320_1m + idle)
for i in $(seq 1 180); do
  if rg -q '"name":"B_m3l320_10m"' "$JSONL" 2>/dev/null; then
    log "B_m3l320_10m present"
    break
  fi
  # still running?
  if ! pgrep -a cmix 2>/dev/null | rg -q 'cmix_'; then
    if rg -q '"name":"B_m3l320_1m"' "$JSONL" 2>/dev/null && (( i > 5 )); then
      log "cmix idle after 1m — 10m may have failed; continue"
      break
    fi
  fi
  log "wait LSTM320 ($i/180)"
  sleep 60
done

while pgrep -a cmix 2>/dev/null | rg -q 'cmix_'; do log "drain cmix"; sleep 20; done
bash "$ROOT/benchmarks/hutter_memory_guard.sh" || { log "mem guard fail"; exit 2; }

# Attribution 1m
for pair in "cmix_l2_wiki_only:l2_wiki_only_1m" "cmix_l2_abjad_only:l2_abjad_only_1m"; do
  bin="${pair%%:*}"; tag="${pair##*:}"
  path="$ROOT/tools/hutter/fx2-cmix/run/$bin"
  if [[ -x "$path" ]]; then
    if rg -q "\"name\":\"$tag\"" "$JSONL" 2>/dev/null; then
      log "skip $tag — already screened"
      continue
    fi
    log "attribution $tag"
    bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" "$tag" "$path" "$DICT" "$SL1" 198744 || true
  else
    log "missing $path"
  fi
done

# Summarize LSTM320 + attribution
python3 - <<'PY'
import json, re
from pathlib import Path
from datetime import datetime
ROOT=Path('/srv/http/fractal_zip')
rows=[]
for l in (ROOT/'benchmarks/.hutter_slice_screens.jsonl').read_text().splitlines():
    try: rows.append(json.loads(re.sub(r'[\x00-\x1f]',' ',l)))
    except Exception: pass
want={'B_m3l320_1m','B_m3l320_10m','l2_wiki_only_1m','l2_abjad_only_1m','l2_entity_1m','B0_entityfold_1m'}
# B0 known
known={'B0_entityfold_1m':198793,'B0_entityfold_10m':1750933,'A0_1m':198744,'A0_10m':1751514}
lines=['## Live update '+datetime.now().astimezone().strftime('%Y-%m-%d %H:%M'),
       '- L2+abjad CLOSED (@10m −440 vs A0, +141 vs B0).']
for name in ['B_m3l320_1m','B_m3l320_10m','l2_wiki_only_1m','l2_abjad_only_1m']:
    hits=[r for r in rows if r.get('name')==name]
    if not hits: continue
    r=hits[-1]
    base=198744 if '1m' in name else 1751514
    lines.append(f"- {name}: {r['bytes']} (ΔA0 {r['bytes']-base:+d}) rt={r.get('rt')}")
text='\n'.join(lines)+'\n'
(ROOT/'benchmarks/.hutter_world_record_state.md').open('a').write('\n'+text)
print(text)
PY

log "Starting match3m 100m gate (time-safe path)"
setsid -f env HUTTER_MAX_SWAP_MIB=5120 HUTTER_NICE=0 bash "$ROOT/benchmarks/run_hutter_100m_match3m.sh"
log "=== after_lstm320_chain DONE launch=$(date -Iseconds) ==="
