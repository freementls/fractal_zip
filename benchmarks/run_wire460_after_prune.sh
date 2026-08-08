#!/usr/bin/env bash
# Serialize post-prune wire460 tracks via phda9 flock (low priority).
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"
export FRACTAL_ZIP_NO_CLI_OPCACHE_REEXEC=1
export FRACTAL_ZIP_BACKGROUND=1
export FRACTAL_ZIP_SUPPRESS_HTML=1

PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-2048M}"
LOCK="$REPO/benchmarks/logs/.phda9_encode.lock"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$REPO/benchmarks/logs/wire460_after_prune_${STAMP}.log"
mkdir -p "$REPO/benchmarks/logs"

run_phda9() {
	echo ""
	echo "=== $(date -Is) $* ==="
	if command -v ionice >/dev/null 2>&1; then
		flock -w 86400 "$LOCK" ionice -c 3 nice -n 19 env "$@" \
			|| echo "WARN exit $? from: $*"
	else
		flock -w 86400 "$LOCK" nice -n 19 env "$@" \
			|| echo "WARN exit $? from: $*"
	fi
}

exec > >(tee -a "$LOG") 2>&1
echo "wire460_after_prune start $(date -Is) waiting for dict prune @384p …"

while pgrep -f 'bench_phda9_dict_prune_hunt.php --pages=384' >/dev/null 2>&1; do
	sleep 120
done
echo "$(date -Is) dict prune finished"

PRUNE_JSON="$REPO/benchmarks/.enwik8_phda9_dict_prune_hunt_384p.json"
BASE_FZPA=""
BEST_LABEL=""
BEST_PATH=""
if [[ -f "$PRUNE_JSON" ]]; then
	read -r BASE_FZPA BEST_LABEL BEST_PATH <<<"$(php -r '
$j=json_decode(file_get_contents($argv[1]),true);
$base=null; foreach($j["rows"]??[] as $r){ if(($r["label"]??"")==="words_4096p"){$base=(int)($r["fzpa"]??0); break;} }
$best=$j["best"]??null;
$lbl=(string)($best["label"]??"");
$path=(string)($best["path"]??"");
$fzpa=(int)($best["fzpa"]??0);
echo ($base??0)." ".$lbl." ".$path;
' "$PRUNE_JSON")"
	echo "prune best: ${BEST_LABEL:-?} path=${BEST_PATH:-?} base_fzpa=${BASE_FZPA:-?}"
fi

if [[ -n "$BEST_PATH" && -f "$BEST_PATH" && -n "$BASE_FZPA" ]]; then
	BEST_FZPA="$(php -r '$j=json_decode(file_get_contents($argv[1]),true); echo (int)($j["best"]["fzpa"]??0);' "$PRUNE_JSON")"
	if [[ "$BEST_FZPA" -lt "$BASE_FZPA" ]]; then
		echo "prune beat words_4096p FZPA — wire probe @384p"
		run_phda9 php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
			--pages=384 \
			--case=split_inner_phda9_xml_single_stream_lstm_words4096_dict \
			--verify-rt \
			--out-json="$REPO/benchmarks/.enwik8_wire460_pruned_dict_${STAMP}.json"
	else
		echo "prune did not beat words_4096p FZPA ($BEST_FZPA vs $BASE_FZPA) — skip pruned wire probe"
	fi
fi

run_phda9 FRACTAL_ZIP_CFABB_PHDA9_GATE=1 php -d "memory_limit=${PHP_MEM}" \
	"$REPO/benchmarks/filter_cfabb_phda9.php" \
	--pages=96 \
	--in="$REPO/benchmarks/.enwik8_cfabb_table_384p_filtered.json" \
	--out="$REPO/benchmarks/.enwik8_cfabb_table_96p_phda9_v3.json" \
	--pool=256 --max=128

run_phda9 FRACTAL_ZIP_CONSONANT_PHDA9_GATE=1 php -d "memory_limit=${PHP_MEM}" \
	"$REPO/benchmarks/filter_consonant_phda9.php" \
	--pages=96 --pool=256 --max=128

run_phda9 php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_phda9_dict_mixed_hunt.php" \
	--pages=384 --refine-trials=24

if [[ -f "$REPO/benchmarks/.phda9_external_dict_mixed_best.txt" ]]; then
	cp -f "$REPO/benchmarks/.phda9_external_dict_mixed_best.txt" "$REPO/benchmarks/.phda9_external_dict.txt"
fi

run_phda9 php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
	--pages=384 \
	--case=split_inner_phda9_xml_single_stream_lstm_words4096_wiki_lom_cfabb_inline \
	--verify-rt \
	--out-json="$REPO/benchmarks/.enwik8_wire460_newwork_cfabb384_${STAMP}.json"

run_phda9 php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
	--pages=384 \
	--case=split_inner_phda9_xml_single_stream_lstm_mixed_dict \
	--verify-rt \
	--out-json="$REPO/benchmarks/.enwik8_wire460_mixed_dict384_${STAMP}.json"

php "$REPO/benchmarks/wire460_status.php" || true
echo "wire460_after_prune done $(date -Is) → $LOG"
