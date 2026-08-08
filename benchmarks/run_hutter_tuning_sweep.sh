#!/usr/bin/env bash
# UPDATE_LIMIT / SEED micro-sweep at 10MB (one heavy job at a time).
# Usage: bash benchmarks/run_hutter_tuning_sweep.sh [UPDATE_LIMIT...]
#   SEEDS=923,42 bash benchmarks/run_hutter_tuning_sweep.sh 1500 3000 6000
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
bash "$ROOT/benchmarks/hutter_memory_guard.sh"
FX2="$ROOT/tools/hutter/fx2-cmix"
RUN="$FX2/run"
CACHE="$ROOT/benchmarks/.ladder_cache"
SLICE="${SLICE:-$CACHE/enwik8_10m_skip20.mid}"
DICT="$FX2/dictionary/english.dic"
BASELINE=1751514
OUT="$ROOT/benchmarks/.hutter_tuning_sweep.jsonl"
SEEDS="${SEEDS:-923}"
PROFILE="${PROFILE:-disk}"

[[ -f "$SLICE" ]] || { echo "missing slice $SLICE"; exit 1; }
mkdir -p "$RUN" "$(dirname "$OUT")"

IFS=',' read -r -a seed_arr <<< "$SEEDS"
limits=("$@")
if ((${#limits[@]} == 0)); then
  limits=(1500 3000 6000)
fi

build_one() {
  local ul=$1 seed=$2
  local tag="ul${ul}_s${seed}"
  echo "=== build $tag ==="
  make -C "$FX2" clean >/dev/null
  if [[ "$PROFILE" == "disk" ]]; then
    make -C "$FX2" disk CFLAGS_DEFINES="-DSEED=$seed -DUPDATE_LIMIT=$ul" >/dev/null
    cp -a "$RUN/cmix" "$RUN/cmix_tune_${tag}"
  else
    make -C "$FX2" lto CFLAGS_DEFINES="-DSEED=$seed -DUPDATE_LIMIT=$ul" >/dev/null
    cp -a "$RUN/cmix" "$RUN/cmix_tune_${tag}"
  fi
  echo "$RUN/cmix_tune_${tag}"
}

run_one() {
  local bin=$1 tag=$2
  local fx2="$RUN/tune_${tag}.fx2"
  local rt="$RUN/tune_${tag}.out"
  local tlog="/tmp/tune_${tag}.time"
  rm -f "$RUN/ppm.temp" "$fx2" "$rt" "${fx2}.cmix.temp"
  echo "=== compress $tag ==="
  nice -n 19 /usr/bin/time -v "$bin" -c "$DICT" "$SLICE" "$fx2" >"/tmp/tune_${tag}_c.log" 2>"$tlog"
  local bytes rss
  bytes=$(stat -c%s "$fx2")
  rss=$(awk -F: '/Maximum resident/ {gsub(/^[ \t]+/,"",$2); print $2+0}' "$tlog")
  local delta=$((bytes - BASELINE))
  local gate=reject
  if (( bytes < BASELINE )); then gate=KEEP; fi
  rm -f "$RUN/ppm.temp"
  nice -n 19 "$bin" -d "$DICT" "$fx2" "$rt" >>"/tmp/tune_${tag}_c.log" 2>&1
  local rt_ok=FAIL
  cmp -s "$SLICE" "$rt" && rt_ok=OK
  printf '{"tag":"tuning","name":"%s","bytes":%s,"baseline":%s,"delta":%s,"gate":"%s","rt":"%s","peak_rss_kb":%s}\n' \
    "$tag" "$bytes" "$BASELINE" "$delta" "$gate" "$rt_ok" "${rss:-0}" | tee -a "$OUT"
  echo "  -> $bytes B delta=$delta gate=$gate rt=$rt_ok"
}

for ul in "${limits[@]}"; do
  for seed in "${seed_arr[@]}"; do
    tag="ul${ul}_s${seed}"
    bin=$(build_one "$ul" "$seed")
    run_one "$bin" "$tag"
  done
done

echo "=== tuning sweep summary (best first) ==="
python3 - "$OUT" "$BASELINE" <<'PY'
import json, sys
path, base = sys.argv[1], int(sys.argv[2])
rows = []
with open(path) as f:
  for line in f:
    line = line.strip()
    if line:
      rows.append(json.loads(line))
rows = [r for r in rows if r.get("rt") == "OK"]
rows.sort(key=lambda r: r["bytes"])
for r in rows[:10]:
  print(f"{r['name']:16} {r['bytes']:>10} delta={r['delta']:+8} gate={r['gate']}")
if not rows:
  print("no RT_OK rows")
else:
  best = rows[0]
  print(f"BEST={best['name']} {best['bytes']} delta={best['delta']:+} vs baseline {base}")
PY
