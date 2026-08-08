#!/usr/bin/env bash
# Bank a winsort order into new_article_order (keeps published_backup).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
W="${1:?usage: bank_winsort_order.sh WINDOW}"
DATA="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data"
PUB="$DATA/new_article_order"
SRC="$DATA/new_article_order_winsort${W}"
[[ -s "$SRC" ]] || { echo "missing $SRC"; exit 1; }
if [[ ! -e "$PUB.published_backup" ]]; then
  cp -a "$PUB" "$PUB.published_backup"
fi
cp -a "$SRC" "$PUB"
cp -a "$SRC" "$PUB.winsort${W}_banked"
# also bank pages file used by fair probes
PAGES_SRC="$DATA/new_article_order_pub_winsort${W}_pages"
PAGES_DST="$DATA/new_article_order_published_pages"
if [[ -s "$PAGES_SRC" ]]; then
  [[ -e "$PAGES_DST.published_backup" ]] || cp -a "$PAGES_DST" "$PAGES_DST.published_backup"
  cp -a "$PAGES_SRC" "$PAGES_DST"
fi
echo "banked winsort${W} -> $PUB"
ls -la "$PUB" "$PUB.published_backup" "$SRC"
# Quick S1 check
COMP="$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_winsort${W}_ascii"
if [[ -s "$COMP" ]]; then
  echo "S1_cmix=$(stat -c%s "$COMP") pub_baseline=201019 Δ=$(( $(stat -c%s "$COMP") - 201019 ))"
fi
