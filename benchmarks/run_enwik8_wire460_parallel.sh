#!/usr/bin/env bash
# Parallel wire460 hunt: prep tracks in parallel; phda9 encodes serialized via flock.
# Target @384p wire: 460,096 B (current best ~519,462 lstm_mixed_dict).
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
export FRACTAL_ZIP_LOW_MEMORY=1
PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-768M}"
TARGET_WIRE=460096
LOG_DIR="$REPO/benchmarks/logs"
mkdir -p "$LOG_DIR"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$LOG_DIR/wire460_parallel_${STAMP}.log"
PHDA9_LOCK="$LOG_DIR/.phda9_encode.lock"
PROBE_JSON="$REPO/benchmarks/.enwik8_wire460_parallel_${STAMP}.json"
STATUS_JSON="$REPO/benchmarks/.enwik8_wire460_parallel_status.json"

exec > >(tee -a "$LOG") 2>&1
echo "wire460_parallel start $(date -Iseconds) target=${TARGET_WIRE} php=${PHP_MEM}"

run_prep() {
	echo ""
	echo "=== PREP $(date -Iseconds) $* ==="
	if command -v ionice >/dev/null 2>&1; then
		ionice -c 3 nice -n 19 env "$@" || echo "WARN prep exit $? from: $*"
	else
		nice -n 19 env "$@" || echo "WARN prep exit $? from: $*"
	fi
}

run_phda9() {
	echo ""
	echo "=== PHDA9 $(date -Iseconds) $* ==="
	if command -v ionice >/dev/null 2>&1; then
		flock -w 14400 "$PHDA9_LOCK" ionice -c 3 nice -n 19 env "$@" || echo "WARN phda9 exit $? from: $*"
	else
		flock -w 14400 "$PHDA9_LOCK" nice -n 19 env "$@" || echo "WARN phda9 exit $? from: $*"
	fi
}

# --- Track A: dict mining (parallel, mostly CPU) ---
(
	run_prep php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/build_phda9_external_dict.php" \
		--pages=384 --mode=optimal \
		| tee "$LOG_DIR/wire460_par_dict_optimal_${STAMP}.log"
) &
PID_DICT_OPT=$!

(
	run_prep php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_phda9_dict_mixed_hunt.php" \
		--pages=384 --refine-trials=24 \
		| tee "$LOG_DIR/wire460_par_mixed_hunt_${STAMP}.log"
) &
PID_MIXED=$!

(
	run_prep php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_phda9_dict_prune_hunt.php" \
		--pages=384 \
		| tee "$LOG_DIR/wire460_par_dict_prune_${STAMP}.log"
) &
PID_PRUNE=$!

# --- Track B: consonant / cfabb artifacts @96p (fast gate signal) ---
(
	run_prep php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_consonant_phda9_gate_sweep.php" \
		--pages=96 --quick \
		| tee "$LOG_DIR/wire460_par_consonant_gate96_${STAMP}.log"
) &
PID_CON_GATE=$!

(
	run_prep FRACTAL_ZIP_CFABB_PHDA9_GATE=1 php -d "memory_limit=${PHP_MEM}" \
		"$REPO/benchmarks/filter_cfabb_phda9.php" \
		--pages=96 \
		--in="$REPO/benchmarks/.enwik8_cfabb_table_384p_filtered.json" \
		--out="$REPO/benchmarks/.enwik8_cfabb_table_96p_phda9_v2.json" \
		--pool=256 --max=128 \
		| tee "$LOG_DIR/wire460_par_cfabb_phda9_96_${STAMP}.log"
) &
PID_CFABB=$!

(
	run_prep FRACTAL_ZIP_CONSONANT_PHDA9_GATE=1 php -d "memory_limit=${PHP_MEM}" \
		"$REPO/benchmarks/filter_consonant_phda9.php" \
		--pages=96 --pool=256 --max=128 \
		| tee "$LOG_DIR/wire460_par_consonant_filter96_${STAMP}.log"
) &
PID_CON_FILT=$!

echo "prep PIDs: dict_opt=$PID_DICT_OPT mixed=$PID_MIXED prune=$PID_PRUNE con_gate=$PID_CON_GATE cfabb=$PID_CFABB con_filt=$PID_CON_FILT"
wait "$PID_DICT_OPT" "$PID_MIXED" "$PID_PRUNE" "$PID_CON_GATE" "$PID_CFABB" "$PID_CON_FILT" 2>/dev/null || true
echo "$(date -Iseconds) prep tracks done"

# Install best dict for wire probes (priority: mixed_best > optimal > existing).
BEST_DICT="$REPO/benchmarks/.phda9_external_dict_mixed_best.txt"
OPT_DICT="$REPO/benchmarks/.phda9_external_dict_384p_optimal.txt"
if [[ -f "$BEST_DICT" ]]; then
	cp -f "$BEST_DICT" "$REPO/benchmarks/.phda9_external_dict.txt"
	echo "installed mixed_best dict ($(wc -c <"$BEST_DICT") B)"
elif [[ -f "$OPT_DICT" ]]; then
	cp -f "$OPT_DICT" "$REPO/benchmarks/.phda9_external_dict.txt"
	echo "installed optimal dict ($(wc -c <"$OPT_DICT") B)"
fi

# --- Track C: wire probes (serialized phda9 via flock) ---
CASES=(
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict'
	'split_inner_phda9_xml_single_stream_lstm_words4096_dict'
	'split_inner_phda9_xml_single_stream_pp96_mixed_dict'
	'split_inner_phda9_xml_single_stream_pp96'
)

for case in "${CASES[@]}"; do
	run_phda9 php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
		--pages=384 --cases="$case" --verify-rt \
		--out-json="$REPO/benchmarks/.enwik8_wire460_par_${case}_${STAMP}.json" \
		| tee "$LOG_DIR/wire460_par_probe_${case}_${STAMP}.log" || true
done

# Merge probe JSONs into one summary file.
php -d "memory_limit=256M" -r "
\$repo = '$REPO';
\$stamp = '$STAMP';
\$cases = json_decode('$(printf '%s' "$(printf '%s,' "${CASES[@]}" | sed 's/,$//')" | jq -R 'split(",") | map(select(length>0))')', true);
\$rows = [];
foreach (\$cases as \$c) {
  \$p = \$repo . '/benchmarks/.enwik8_wire460_par_' . \$c . '_' . \$stamp . '.json';
  if (!is_file(\$p)) continue;
  \$j = json_decode((string) file_get_contents(\$p), true);
  foreach (\$j['rows'] ?? [] as \$r) { \$rows[] = \$r; }
}
\$out = ['generated' => date('c'), 'pages' => 384, 'target_wire' => $TARGET_WIRE, 'rows' => \$rows];
file_put_contents('$PROBE_JSON', json_encode(\$out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
" 2>/dev/null || true

echo ""
echo "=== $(date -Iseconds) gate summary ==="
run_prep php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/summarize_research_probe.php" \
	--json="$PROBE_JSON" --pages=384 || true

BEST=$(php -r '
$j=json_decode(@file_get_contents($argv[1]),true);
$best=PHP_INT_MAX; $label="";
foreach ($j["rows"]??[] as $r) {
  if (!empty($r["error"])) continue;
  $w=(int)($r["fzc_bytes"]??0);
  if ($w>0 && $w<$best) { $best=$w; $label=$r["label"]??"?"; }
}
echo $best===PHP_INT_MAX ? "none" : ($label." ".$best);
' "$PROBE_JSON" 2>/dev/null || echo "parse_fail")

echo ""
echo "wire460_parallel best: $BEST (target ${TARGET_WIRE})"
if [[ "$BEST" != none && "$BEST" != parse_fail ]]; then
	WIRE=$(echo "$BEST" | awk '{print $NF}')
	GAP=$((WIRE - TARGET_WIRE))
	if [[ "$WIRE" -le "$TARGET_WIRE" ]]; then
		echo "GATE PASS — wire ${WIRE} ≤ ${TARGET_WIRE}"
		cp -f "$PROBE_JSON" "$REPO/benchmarks/.enwik8_wire_slice_probe_384p_phda9.json"
	else
		echo "still short by ${GAP} B"
	fi
fi

php -r "
file_put_contents('$STATUS_JSON', json_encode([
  'updated' => date('c'),
  'stamp' => '$STAMP',
  'target_wire' => $TARGET_WIRE,
  'best' => '$BEST',
  'probe_json' => '$PROBE_JSON',
  'log' => '$LOG',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
"

echo "wire460_parallel done $(date -Iseconds) → $LOG status=$STATUS_JSON"
