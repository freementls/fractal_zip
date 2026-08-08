#!/usr/bin/env bash
# Wait for in-flight prepass, then install @384p optimal dict before wire460 wire probe.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
LOG_DIR="$REPO/benchmarks/logs"
mkdir -p "$LOG_DIR"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$LOG_DIR/wire460_dict_swap_${STAMP}.log"
OPTIMAL="$REPO/benchmarks/.phda9_external_dict_384p_optimal.txt"
MAIN="$REPO/benchmarks/.phda9_external_dict.txt"

exec > >(tee -a "$LOG") 2>&1
echo "dict_swap_watch start $(date -Iseconds)"

if [[ ! -f "$OPTIMAL" ]]; then
  echo "building optimal @384p dict…"
  nice -n 19 php -d memory_limit=768M "$REPO/benchmarks/build_phda9_external_dict.php" \
    --pages=384 --mode=optimal --out="$OPTIMAL"
fi

# Wait for prepass (or any bench_phda9_xml_prepass) so RT decompress is not disturbed.
for i in $(seq 1 1440); do
  if ! pgrep -f 'bench_phda9_xml_prepass\.php' >/dev/null 2>&1; then
    break
  fi
  if (( i % 10 == 0 )); then
    echo "$(date -Iseconds) waiting for prepass exit (iter $i)"
  fi
  sleep 60
done

if [[ -f "$MAIN" ]]; then
  cp -f "$MAIN" "$LOG_DIR/wire460_dict_full_backup_${STAMP}.txt"
fi
cp -f "$OPTIMAL" "$MAIN"
echo "$(date -Iseconds) installed optimal dict: $(wc -c <"$OPTIMAL") B → $MAIN"
echo "dict_swap_watch done $(date -Iseconds) → $LOG"
