#!/usr/bin/env bash
# Low-priority 1MB archive-win hunt.
# KEEP only if bytes < current best (phda9 196550) OR, for fx2-only arms,
# bytes < best fx2 so far (218232) — those are fx2-path wins to scale later.
# Usage: nice -n 19 bash benchmarks/run_hutter_1m_win_hunt.sh
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CMIX="$ROOT/tools/hutter/fx2-cmix/run/cmix_orig"
PHDA9="$ROOT/tools/phda9/phda9"
DICT="$ROOT/tools/hutter/fx2-cmix/dictionary/english.dic"
SLICE="${SLICE:-/tmp/enwik8_1m.bin}"
OUT="$ROOT/benchmarks/.hutter_1m_wins.jsonl"
BEST_ALL=196550
BEST_FX2=218232

if [[ ! -f "$SLICE" ]] || [[ $(stat -c%s "$SLICE") -ne 1048576 ]]; then
  dd if="$ROOT/enwik8" of="$SLICE" bs=1 skip=10000000 count=1048576 status=none
fi
PLAIN=$(stat -c%s "$SLICE")
mkdir -p "$(dirname "$OUT")"

log_row() {
  local name=$1 bytes=$2 note=${3:-}
  local bpc
  bpc=$(python3 -c "print(f'{$bytes*8/$PLAIN:.4f}')")
  local gate="reject"
  if (( bytes < BEST_ALL )); then gate="KEEP_all"
  elif (( bytes < BEST_FX2 )); then gate="KEEP_fx2"
  fi
  printf '%s\n' "{\"tag\":\"1m_win\",\"name\":\"$name\",\"plain\":$PLAIN,\"bytes\":$bytes,\"bpc\":$bpc,\"gate\":\"$gate\",\"note\":\"$note\"}" | tee -a "$OUT"
  echo "  -> $bytes B  $bpc bpc  gate=$gate"
}

echo "=== 1MB win hunt plain=$PLAIN best_all=$BEST_ALL best_fx2=$BEST_FX2 ==="

# Arm A: fx2 -c with shipped english.dic (missing from 1m scoreboard)
if [[ -f "$DICT" ]]; then
  echo "[fx2_c_dict] ..."
  nice -n 19 "$CMIX" -c "$DICT" "$SLICE" /tmp/ladder_1m_fx2_c_dict.out > /tmp/ladder_1m_fx2_c_dict.log 2>&1
  log_row fx2_c_dict "$(stat -c%s /tmp/ladder_1m_fx2_c_dict.out)" "english.dic"
fi

# Arm B: slice-frequency word list as dict (top 8k tokens len>=3)
python3 - "$SLICE" /tmp/ladder_1m_slice.dic <<'PY'
import sys,re,collections
data=open(sys.argv[1],'rb').read().decode('latin1',errors='ignore')
words=re.findall(r"[A-Za-z][A-Za-z']{2,}", data)
ctr=collections.Counter(w.lower() for w in words)
# fx2 dict is newline-separated tokens
out=sys.argv[2]
with open(out,'w') as f:
  for w,_ in ctr.most_common(8000):
    f.write(w+'\n')
print(f'wrote {out} entries={min(8000,len(ctr))}')
PY
echo "[fx2_c_slice_dict] ..."
nice -n 19 "$CMIX" -c /tmp/ladder_1m_slice.dic "$SLICE" /tmp/ladder_1m_fx2_c_slice_dict.out > /tmp/ladder_1m_fx2_c_slice_dict.log 2>&1
log_row fx2_c_slice_dict "$(stat -c%s /tmp/ladder_1m_fx2_c_slice_dict.out)" "top8k_slice_freq"

# Arm C/D: phda9 with external dict (CLI supports [dictionary]; uppercase C = English preprocess)
if [[ -f "$DICT" ]]; then
  echo "[phda9_english_dic] ..."
  nice -n 19 "$PHDA9" C "$SLICE" /tmp/ladder_1m_phda9_edic.out "$DICT" > /tmp/ladder_1m_phda9_edic.log 2>&1
  log_row phda9_english_dic "$(stat -c%s /tmp/ladder_1m_phda9_edic.out)" "phda9+english.dic"
fi
echo "[phda9_slice_dic] ..."
nice -n 19 "$PHDA9" C "$SLICE" /tmp/ladder_1m_phda9_sdic.out /tmp/ladder_1m_slice.dic > /tmp/ladder_1m_phda9_sdic.log 2>&1
log_row phda9_slice_dic "$(stat -c%s /tmp/ladder_1m_phda9_sdic.out)" "phda9+top8k_slice"

echo "=== 1MB win hunt done ==="
python3 - "$OUT" <<'PY'
import sys,json
rows=[]
for l in open(sys.argv[1]):
  l=l.strip()
  if not l: continue
  r=json.loads(l)
  if r.get('tag')=='1m_win': rows.append(r)
rows.sort(key=lambda r:r['bytes'])
print(f"{'name':22} {'bytes':>10} {'gate':12}")
for r in rows:
  print(f"{r['name']:22} {r['bytes']:10} {r.get('gate','?'):12}")
keeps=[r for r in rows if str(r.get('gate','')).startswith('KEEP')]
print(f"KEEP count={len(keeps)}")
PY
