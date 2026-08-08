#!/usr/bin/env bash
# Size ladder: measure honest compressed bytes on enwik8 slices.
# Usage: bash benchmarks/run_hutter_size_ladder.sh [1m|10m|100m|all]
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CMIX="$ROOT/tools/hutter/fx2-cmix/run/cmix_orig"
PHDA9="$ROOT/tools/phda9/phda9"
ENWIK="$ROOT/enwik8"
OUT="$ROOT/benchmarks/.hutter_size_ladder.jsonl"
mkdir -p "$(dirname "$OUT")"

scale="${1:-1m}"
case "$scale" in
  1m) BYTES=$((1*1024*1024)); TAG=1m ;;
  10m) BYTES=$((10*1024*1024)); TAG=10m ;;
  100m) BYTES=100000000; TAG=100m ;;
  *) echo "usage: $0 1m|10m|100m"; exit 1 ;;
esac

SLICE="/tmp/enwik8_${TAG}.bin"
if [[ ! -f "$SLICE" ]] || [[ $(stat -c%s "$SLICE") -ne $BYTES ]]; then
  # Prefer mid-file for homogeneous text (skip XML header)
  if (( BYTES < 100000000 )); then
    SKIP=10000000
    dd if="$ENWIK" of="$SLICE" bs=1 skip=$SKIP count=$BYTES status=none
  else
    cp "$ENWIK" "$SLICE"
  fi
fi
PLAIN=$(stat -c%s "$SLICE")
echo "=== ladder $TAG plain=$PLAIN ===" 

run_one() {
  local name=$1; shift
  local out="/tmp/ladder_${TAG}_${name}.out"
  local log="/tmp/ladder_${TAG}_${name}.log"
  local t0 t1 sec bytes bpc
  echo "[$name] $*"
  t0=$(date +%s)
  if "$@" >"$log" 2>&1; then
    t1=$(date +%s)
    sec=$((t1-t0))
    bytes=$(stat -c%s "$out" 2>/dev/null || echo 0)
    bpc=$(python3 -c "print(f'{$bytes*8/$PLAIN:.4f}')")
    printf '%s\n' "{\"tag\":\"$TAG\",\"name\":\"$name\",\"plain\":$PLAIN,\"bytes\":$bytes,\"bpc\":$bpc,\"sec\":$sec}" | tee -a "$OUT"
    echo "  -> $bytes B  $bpc bpc  ${sec}s"
  else
    t1=$(date +%s)
    echo "  FAIL after $((t1-t0))s — see $log"
    printf '%s\n' "{\"tag\":\"$TAG\",\"name\":\"$name\",\"plain\":$PLAIN,\"bytes\":null,\"error\":true}" | tee -a "$OUT"
  fi
}

# Arms (one at a time — RAM)
run_one phda9 bash -c "$PHDA9 C '$SLICE' '/tmp/ladder_${TAG}_phda9.out'"
run_one fx2_n bash -c "$CMIX -n '$SLICE' '/tmp/ladder_${TAG}_fx2_n.out'"
run_one fx2_c bash -c "$CMIX -c '$SLICE' '/tmp/ladder_${TAG}_fx2_c.out'"
DICT="$ROOT/tools/hutter/fx2-cmix/dictionary/english.dic"
if [[ -f "$DICT" ]]; then
  run_one fx2_c_dict bash -c "$CMIX -c '$DICT' '$SLICE' '/tmp/ladder_${TAG}_fx2_c_dict.out'"
fi

echo "=== $TAG summary ==="
python3 - "$OUT" "$TAG" <<'PY'
import sys,json
path,tag=sys.argv[1],sys.argv[2]
rows=[]
with open(path) as f:
  for l in f:
    l=l.strip()
    if not l: continue
    r=json.loads(l)
    if r.get("tag")==tag and r.get("bytes"):
      rows.append(r)
rows.sort(key=lambda r: r["bytes"])
if not rows:
  print("no successful rows"); raise SystemExit
best=rows[0]
print(f"{'name':16} {'bytes':>10} {'bpc':>8} {'Δbest':>8} {'sec':>6}")
for r in rows:
  d=r["bytes"]-best["bytes"]
  print(f"{r['name']:16} {r['bytes']:10} {float(r['bpc']):8.4f} {d:+8} {r['sec']:6}")
print(f"BEST={best['name']} {best['bytes']} B ({best['bpc']} bpc)")
PY
