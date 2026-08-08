#!/usr/bin/env bash
# Build S1 = UPX(core) + comp_dict + comp_order + header (fx2 packaging).
# Uses run/cmix_mmap (or CMIX_CORE) as the modeling core for compressing dict/order.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FX2="$ROOT/tools/hutter/fx2-cmix"
DIR="$FX2/run"
PACK_CORE="${CMIX_PACK:-${CMIX_CORE:-$DIR/cmix_submission}}"
MODEL_CORE="${CMIX_MODEL:-$DIR/cmix_disk}"
PUBLISHED_S1=441463
[[ -x "$PACK_CORE" ]] || { echo "missing pack core $PACK_CORE"; exit 1; }
[[ -x "$MODEL_CORE" ]] || { echo "missing model core $MODEL_CORE (needs -c/-h)"; exit 1; }
command -v upx >/dev/null || { echo "need upx"; exit 1; }
cd "$DIR"
rm -f cmix_s1_core cmix_s1_upx comp_dict_s1 comp_order_s1 header_s1.dat cmix_s1 ppm.temp
cp -a "$PACK_CORE" cmix_s1_core
upx -9 -o cmix_s1_upx cmix_s1_core
# Compress dict+order with full lab binary (-c stripped from lean submission build).
nice -n 19 "$MODEL_CORE" -c "$FX2/dictionary/english.dic" comp_dict_s1 > /tmp/s1_comp_dict.log 2>&1
nice -n 19 "$MODEL_CORE" -c "$FX2/src/readalike_prepr/data/new_article_order" comp_order_s1 > /tmp/s1_comp_order.log 2>&1
"$MODEL_CORE" -h "$(wc -c <comp_dict_s1)" "$(wc -c <comp_order_s1)" 0
mv -f header.dat header_s1.dat
cat cmix_s1_upx comp_dict_s1 comp_order_s1 header_s1.dat > cmix_s1
chmod +x cmix_s1
S1=$(stat -c%s cmix_s1)
python3 - <<PY
s1=$S1; pub=$PUBLISHED_S1
print(f"S1={s1} published={pub} delta={s1-pub} {'BEATS' if s1<=pub else 'ABOVE'} published")
open("$ROOT/benchmarks/.hutter_s1_embed.json","w").write(__import__('json').dumps({
  "S1": s1, "published": pub, "delta": s1-pub, "beats_published": s1<=pub,
  "pack_core": "$PACK_CORE", "model_core": "$MODEL_CORE", "artifact": "$DIR/cmix_s1"
}, indent=2)+"\n")
PY
