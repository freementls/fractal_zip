#!/usr/bin/env bash
# Wire460 hunt: inline cfabb + words4096 dict + dict-inline temp (prize path).
# Target @384p: 460,096 B | current best ~518,508 B (words4096 + phda9 LSTM).
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG_DIR="$REPO/benchmarks/logs"
mkdir -p "$LOG_DIR"
TARGET=460096
PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-2048M}"
PHDA9_LOCK="$REPO/benchmarks/logs/.phda9_encode.lock"
export FRACTAL_ZIP_BACKGROUND=1
export FRACTAL_ZIP_SUPPRESS_HTML=1

run_probe() {
	local pages="$1" tag="$2"
	shift 2
	local out="$REPO/benchmarks/.enwik8_wire460_newwork_${tag}_${pages}p_${STAMP}.json"
	echo "=== wire460_newwork pages=${pages} tag=${tag} $(date -Iseconds) ===" | tee -a "$LOG_DIR/wire460_newwork_${STAMP}.log"
	if command -v ionice >/dev/null 2>&1; then
		flock -w 14400 "$PHDA9_LOCK" ionice -c 3 nice -n 19 php -d memory_limit="$PHP_MEM" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
			--pages="$pages" "$@" \
			--out-json="$out" \
			2>&1 | tee -a "$LOG_DIR/wire460_newwork_${tag}_${pages}p_${STAMP}.log"
	else
		flock -w 14400 "$PHDA9_LOCK" nice -n 19 php -d memory_limit="$PHP_MEM" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
			--pages="$pages" "$@" \
			--out-json="$out" \
			2>&1 | tee -a "$LOG_DIR/wire460_newwork_${tag}_${pages}p_${STAMP}.log"
	fi
	echo "→ $out"
	cp -f "$out" "$REPO/benchmarks/.enwik8_wire460_newwork_${tag}_${pages}p_latest.json"
}

CASES="split_inner_phda9_xml_single_stream_lstm_words4096_dict,split_inner_phda9_xml_single_stream_lstm_words4096_wiki_lom_cfabb_inline,split_inner_phda9_xml_single_stream_lstm_prize_inline"

echo "wire460_newwork start stamp=${STAMP} target=${TARGET} mem=${PHP_MEM}" | tee "$LOG_DIR/wire460_newwork_${STAMP}.log"

run_probe 96 "compare" --cases="$CASES"

BEST_96="$(php -r '
$j=json_decode(file_get_contents(getenv("JSON")),true);
$best=PHP_INT_MAX;$lbl="";
foreach($j["rows"]??[] as $r){$w=(int)($r["fzc_bytes"]??0);if($w>0&&$w<$best){$best=$w;$lbl=$r["label"];}}
echo $best===PHP_INT_MAX?"":("$best $lbl");
' JSON="$REPO/benchmarks/.enwik8_wire460_newwork_compare_96p_latest.json")"
echo "96p best: ${BEST_96:-none}" | tee -a "$LOG_DIR/wire460_newwork_${STAMP}.log"

run_probe 384 "best" \
	--case=split_inner_phda9_xml_single_stream_lstm_words4096_wiki_lom_cfabb_inline \
	--verify-rt

php "$REPO/benchmarks/wire460_status.php" | tee -a "$LOG_DIR/wire460_newwork_${STAMP}.log"
echo "wire460_newwork done $(date -Iseconds)" | tee -a "$LOG_DIR/wire460_newwork_${STAMP}.log"
