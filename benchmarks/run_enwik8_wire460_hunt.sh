#!/usr/bin/env bash
# Hunt @384p wire ≤ 460,096 B (beat146 gate budget). One phda9 at a time.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
export FRACTAL_ZIP_LOW_MEMORY=1
PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-768M}"
TARGET_WIRE=460096
LOG_DIR="$REPO/benchmarks/logs"
mkdir -p "$LOG_DIR"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$LOG_DIR/wire460_hunt_${STAMP}.log"
OUT_JSON="$REPO/benchmarks/.enwik8_wire460_hunt_${STAMP}.json"
LOCK="$LOG_DIR/.lock_enwik8_verify.pid"

exec > >(tee -a "$LOG") 2>&1
echo "wire460_hunt start $(date -Iseconds) target=${TARGET_WIRE} php_limit=${PHP_MEM}"

# Wait for full verify (phda9 ~3.5 GiB) before starting encode probes.
for i in $(seq 1 720); do
  if [[ ! -f "$LOCK" ]]; then
    break
  fi
  if ! fuser "$LOCK" >/dev/null 2>&1; then
    break
  fi
  if (( i % 5 == 0 )); then
    echo "$(date -Iseconds) waiting for verify lock (iter $i)"
  fi
  sleep 60
done
echo "$(date -Iseconds) verify lock clear — starting hunt"

run() {
  echo ""
  echo "=== $(date -Iseconds) $* ==="
  "$@"
}

# Refresh external dict (chunked mine; optimal token mix for page prefix).
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/build_phda9_external_dict.php" --pages=384 --mode=optimal \
  | tee "$LOG_DIR/wire460_dict_optimal_384p_${STAMP}.log" || \
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/build_phda9_external_dict.php" --pages=384 --mode=words \
  | tee "$LOG_DIR/wire460_dict_384p_${STAMP}.log" || true

# Quick isolated FZPA + integrated wire profile (dict on/off).
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_phda9_single_stream_profile.php" --pages=384 \
  | tee "$LOG_DIR/wire460_profile_384p_${STAMP}.log" || true

# Isolated phda9 prepass signal @384p (minutes; no full .fz fold).
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_phda9_xml_prepass.php" --pages=384 \
  | tee "$LOG_DIR/wire460_prepass_384p_${STAMP}.log" || true

# Wire probe arms read benchmarks/.phda9_external_dict.txt via pp96_core — use @384p optimal mix.
OPTIMAL_DICT="$REPO/benchmarks/.phda9_external_dict_384p_optimal.txt"
if [[ -f "$OPTIMAL_DICT" ]]; then
  if [[ -f "$REPO/benchmarks/.phda9_external_dict.txt" ]]; then
    cp -f "$REPO/benchmarks/.phda9_external_dict.txt" "$LOG_DIR/wire460_dict_before_probe_${STAMP}.txt" || true
  fi
  cp -f "$OPTIMAL_DICT" "$REPO/benchmarks/.phda9_external_dict.txt"
  echo "$(date -Iseconds) installed optimal @384p dict ($(wc -c <"$OPTIMAL_DICT") B) for wire probe"
fi

# Integrated wire arms: dict fix + LSTM + combined.
CASES='split_inner_phda9_xml_single_stream_pp96,split_inner_phda9_xml_single_stream_lstm,split_inner_phda9_xml_single_stream_lstm_dict'
run php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
  --pages=384 --cases="$CASES" --out-json="$OUT_JSON" --verify-rt \
  | tee "$LOG_DIR/wire460_probe_384p_${STAMP}.log" || true

echo ""
echo "=== $(date -Iseconds) gate summary ==="
php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/summarize_research_probe.php" \
  --json="$OUT_JSON" --pages=384 || true

BEST=$(php -r '
$j=json_decode(file_get_contents($argv[1]),true);
$best=PHP_INT_MAX; $label="";
foreach ($j["rows"]??[] as $r) {
  if (!empty($r["error"])) continue;
  $w=(int)($r["fzc_bytes"]??0);
  if ($w>0 && $w<$best) { $best=$w; $label=$r["label"]??"?"; }
}
echo $best===PHP_INT_MAX ? "none" : ($label." ".$best);
' "$OUT_JSON" 2>/dev/null || echo "parse_fail")

echo ""
echo "wire460_hunt best: $BEST (target ${TARGET_WIRE})"
if [[ "$BEST" != none && "$BEST" != parse_fail ]]; then
  WIRE=$(echo "$BEST" | awk '{print $NF}')
  if [[ "$WIRE" -le "$TARGET_WIRE" ]]; then
    echo "GATE PASS — wire ${WIRE} ≤ ${TARGET_WIRE}"
    cp -f "$OUT_JSON" "$REPO/benchmarks/.enwik8_wire_slice_probe_384p_phda9.json"
  else
    GAP=$((WIRE - TARGET_WIRE))
    echo "still short by ${GAP} B — see $OUT_JSON"
  fi
fi
echo "wire460_hunt done $(date -Iseconds) → $LOG"
