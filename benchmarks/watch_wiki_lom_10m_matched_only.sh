#!/usr/bin/env bash
# After fair matched arms finish on wiki_lom_cmix_10m, stop before entityfold.
set -euo pipefail
ROOT=/srv/http/fractal_zip
OUT=$ROOT/benchmarks/.ladder_cache/fractal_dictlab/wiki_lom_cmix_10m
LOG=$OUT/scale.log
WATCH=$OUT/matched_watch.log
exec >>"$WATCH" 2>&1
echo "=== $(date -Is) watching for matched arms ==="

while true; do
  if [ -s "$OUT/result.json" ] && grep -q 'delta_wire_vs_raw_matched' "$OUT/result.json" 2>/dev/null; then
    echo "result.json already present; exit"
    exit 0
  fi
  if grep -q 'joint matched' "$LOG" 2>/dev/null; then
    echo "finish completed normally"
    exit 0
  fi
  # Two S2= lines means raw+matched and wire+matched finished
  n=$(grep -cE 'S2=[0-9]+' "$LOG" 2>/dev/null || true)
  n=${n:-0}
  if [ "$n" -ge 2 ] && grep -q 'cmix wire+matched' "$LOG"; then
    break
  fi
  # Also stop if entityfold just started (race)
  if [ "$n" -ge 2 ] && grep -q 'cmix raw+entityfold' "$LOG"; then
    break
  fi
  sleep 20
done

echo "=== $(date -Is) matched arms done (S2 count=$n); stopping further arms ==="
pkill -f 'run_fractal_wiki_lom_cmix_finish.py --dir .*wiki_lom_cmix_10m' 2>/dev/null || true
sleep 2
# kill leftover cmix on this dir or entityfold on raw_pages from this job
pkill -f 'cmix_match3m_fractalv2b -c .*/wiki_lom_cmix_10m/' 2>/dev/null || true
sleep 1

python3 - <<'PY'
import json, re, os
od="/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/wiki_lom_cmix_10m"
log=open(f"{od}/scale.log").read()
def grab(pat):
    m=re.search(pat, log)
    return int(m.group(1)) if m else None
raw_s1=grab(r"S1 raw matched\.\.\.\n\s*(\d+)")
wire_s1=grab(r"S1 wire matched\.\.\.\n\s*(\d+)")
s2s=[int(x) for x in re.findall(r"S2=(\d+)", log)]
if len(s2s) < 2:
    raise SystemExit(f"need 2 S2 lines, got {s2s}")
s2_raw, s2_wire = s2s[0], s2s[1]
meta=json.load(open(f"{od}/meta.json"))
raw_w=sum(1 for _ in open(f"{od}/raw_matched.dic"))
wire_w=sum(1 for _ in open(f"{od}/wire_matched.dic"))
out={
  "meta": meta,
  "dicts": {
    "raw_matched": {"words": raw_w, "best": "cmix_n", "bytes": raw_s1},
    "wire_matched": {"words": wire_w, "best": "cmix_n", "bytes": wire_s1},
    "entityfold": {"best": "cmix_n_banked", "bytes": 101630, "note": "S2 skipped"},
  },
  "s2": {"raw_matched": s2_raw, "wire_matched": s2_wire},
  "joints": {
    "raw_matched": (raw_s1 or 0)+s2_raw,
    "wire_matched": (wire_s1 or 0)+s2_wire,
  },
  "note": "entityfold S2 skipped after matched (watch_wiki_lom_10m_matched_only.sh)",
}
out["delta_wire_vs_raw_matched"]=out["joints"]["wire_matched"]-out["joints"]["raw_matched"]
out["delta_s2_matched"]=s2_wire-s2_raw
out["rate_s2_matched_vs_raw"]=out["delta_s2_matched"]/meta["raw_bytes"]
out["verdict_vs_matched"]="KEEP" if out["delta_wire_vs_raw_matched"]<0 else "REJECT"
json.dump(out, open(f"{od}/result.json","w"), indent=2)
print(json.dumps({k:out[k] for k in ("s2","delta_wire_vs_raw_matched","delta_s2_matched","rate_s2_matched_vs_raw","verdict_vs_matched")}, indent=2))
PY
echo "=== $(date -Is) matched-only result written ==="
