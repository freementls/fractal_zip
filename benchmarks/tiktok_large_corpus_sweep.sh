#!/usr/bin/env bash
# Tik-tok sweep: report fzc_bytes + zip_seconds per profile/knob.
# Sample-first by default for huge corpora.
#
# Usage:
#   bash benchmarks/tiktok_large_corpus_sweep.sh [only_corpus]
#   bash benchmarks/tiktok_large_corpus_sweep.sh test_files133 --auto-sample --sample-target-mib=28
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
ONLY="test_files133_sample"
if [[ $# -gt 0 && "$1" != --* ]]; then
	ONLY="$1"
	shift
fi
AUTO_SAMPLE=0
SAMPLE_TARGET_MIB=28
VALIDATE_FULL=0
QUICK_WINS=0
NO_BEST_EXT=0
OUTDIR="${REPO}/benchmarks/.tiktok_sweep_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$OUTDIR"
MEM="${FRACTAL_ZIP_BENCH_MEMORY_LIMIT:-2G}"
PHP="${PHP_BINARY:-php}"
export FRACTAL_ZIP_BENCH_MEMORY_LIMIT="$MEM"

while [[ $# -gt 0 ]]; do
	case "$1" in
		--auto-sample) AUTO_SAMPLE=1; shift ;;
		--validate-full) VALIDATE_FULL=1; shift ;;
		--quick-wins) QUICK_WINS=1; shift ;;
		--no-best-ext) NO_BEST_EXT=1; shift ;;
		--sample-target-mib=*) SAMPLE_TARGET_MIB="${1#*=}"; shift ;;
		-h|--help)
			echo "Usage: bash benchmarks/tiktok_large_corpus_sweep.sh [only_corpus] [--auto-sample] [--sample-target-mib=28] [--validate-full] [--quick-wins] [--no-best-ext]"
			echo "  --quick-wins: run profile rows + wins only (skip probe/census recommendation stages)"
			echo "  --no-best-ext: skip min-ext tournament in each benchmark run"
			exit 0
			;;
		*) echo "Unknown arg: $1" >&2; exit 2 ;;
	esac
done

SWEEP_ONLY="$ONLY"
if [[ "$AUTO_SAMPLE" -eq 1 && -d "${REPO}/${ONLY}" && "$ONLY" != *_sample ]]; then
	SWEEP_ONLY="${ONLY}_sample"
	if [[ ! -d "${REPO}/${SWEEP_ONLY}" ]]; then
		echo "[tiktok] building stratified sample ${SWEEP_ONLY} from ${ONLY} (target ${SAMPLE_TARGET_MIB} MiB)" | tee -a "${OUTDIR}/summary.txt"
		"$PHP" "${REPO}/benchmarks/sample_large_corpus.php" "${ONLY}" "${SWEEP_ONLY}" "--target-mib=${SAMPLE_TARGET_MIB}" 2>&1 | tee -a "${OUTDIR}/run.log"
	fi
fi

summarize() {
	local json="$1"
	local tag="$2"
	"$PHP" -r '
		$j=json_decode(file_get_contents($argv[1]),true);
		$c=$j["cases"][0]??array();
		printf("%s\tfzc_bytes=%s\tzip_seconds=%s\touter=%s\tgzip_fast=%s\ttx=%s\tprofile=%s\n",
			$argv[2],
			$c["fzc_bytes"]??"?",
			$c["zip_seconds"]??"?",
			$c["outer_codec"]??"?",
			($c["folder_gzip_fast"]??false)?"1":"0",
			isset($c["folder_bundle_census"]["textish_ratio"])?sprintf("%.1f%%",100*(float)$c["folder_bundle_census"]["textish_ratio"]):"—",
			$j["bench_profile"]??"null"
		);
	' "$json" "$tag" | tee -a "${OUTDIR}/summary.txt"
}

refresh_wins() {
	echo "[tiktok] interim wins (rows so far)" | tee -a "${OUTDIR}/summary.txt"
	if ! "$PHP" benchmarks/tiktok_wins_report.php --out-dir="$OUTDIR" 2>&1 | tee -a "${OUTDIR}/summary.txt"; then
		echo "[tiktok] interim wins unavailable yet" | tee -a "${OUTDIR}/summary.txt"
	fi
}

run_one() {
	local tag="$1"
	shift
	local json="${OUTDIR}/${tag}.json"
	local noBestExtArg=()
	if [[ "$NO_BEST_EXT" -eq 1 ]]; then
		noBestExtArg+=(--no-best-ext)
	fi
	echo "[tiktok] === ${tag} ===" | tee -a "${OUTDIR}/summary.txt"
	cd "$REPO"
	if "$PHP" benchmarks/run_benchmarks.php --only="$SWEEP_ONLY" --large --no-case-timeout --no-verify \
		--no-save-last-json --out-json="$json" "${noBestExtArg[@]}" "$@" 2>&1 | tee -a "${OUTDIR}/run.log"; then
		summarize "$json" "$tag"
		return 0
	fi
	printf "%s\tFAILED\n" "${tag}" | tee -a "${OUTDIR}/summary.txt"
	return 1
}

SUCCESS_ROWS=0
echo "tiktok sweep corpus=${ONLY} sweep_corpus=${SWEEP_ONLY} out=$OUTDIR" | tee -a "${OUTDIR}/run.log"
if run_one "default"; then
	SUCCESS_ROWS=$((SUCCESS_ROWS + 1))
	if [[ "$QUICK_WINS" -eq 1 ]]; then refresh_wins; fi
fi
if run_one "large_fast" --bench-profile=large-fast; then
	SUCCESS_ROWS=$((SUCCESS_ROWS + 1))
	if [[ "$QUICK_WINS" -eq 1 ]]; then refresh_wins; fi
fi
if run_one "large_bytes" --bench-profile=large-bytes; then
	SUCCESS_ROWS=$((SUCCESS_ROWS + 1))
	if [[ "$QUICK_WINS" -eq 1 ]]; then refresh_wins; fi
fi
if [[ "$QUICK_WINS" -eq 0 ]]; then
	if run_one "large_balanced" --bench-profile=large-balanced; then SUCCESS_ROWS=$((SUCCESS_ROWS + 1)); fi
fi

if [[ "$QUICK_WINS" -eq 1 ]]; then
	if [[ "$SUCCESS_ROWS" -eq 0 ]]; then
		echo "[tiktok] no successful profile rows; skip wins" | tee -a "${OUTDIR}/summary.txt"
		echo "[tiktok] done (quick-wins, no rows) → ${OUTDIR}/summary.txt"
		exit 1
	fi
	echo "[tiktok] === wins ===" | tee -a "${OUTDIR}/summary.txt"
	"$PHP" benchmarks/tiktok_wins_report.php --out-dir="$OUTDIR" 2>&1 | tee -a "${OUTDIR}/summary.txt"
	echo "[tiktok] done (quick-wins) → ${OUTDIR}/summary.txt"
	exit 0
fi

for probe in 4096 8192 65536 1048576 2097152; do
	echo "[tiktok] === probe_${probe} ===" | tee -a "${OUTDIR}/summary.txt"
	json="${OUTDIR}/probe_${probe}.json"
	cd "$REPO"
	noBestExtArg=()
	if [[ "$NO_BEST_EXT" -eq 1 ]]; then
		noBestExtArg+=(--no-best-ext)
	fi
	if FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES="$probe" \
		"$PHP" benchmarks/run_benchmarks.php --only="$SWEEP_ONLY" --large --no-case-timeout --no-verify \
		--no-save-last-json --out-json="$json" "${noBestExtArg[@]}" --bench-profile=large-balanced; then
		summarize "$json" "probe_${probe}"
	else
		printf "probe_%s\tFAILED\n" "${probe}" | tee -a "${OUTDIR}/summary.txt"
	fi
done 2>&1 | tee -a "${OUTDIR}/run.log"

echo "[tiktok] === census_gate_off ===" | tee -a "${OUTDIR}/summary.txt"
json="${OUTDIR}/census_gate_off.json"
cd "$REPO"
noBestExtArg=()
if [[ "$NO_BEST_EXT" -eq 1 ]]; then
	noBestExtArg+=(--no-best-ext)
fi
if FRACTAL_ZIP_FOLDER_CENSUS_GZIP_FAST_AUTO=0 \
	"$PHP" benchmarks/run_benchmarks.php --only="$SWEEP_ONLY" --large --no-case-timeout --no-verify \
	--no-save-last-json --out-json="$json" "${noBestExtArg[@]}" --bench-profile=large-balanced; then
	summarize "$json" "census_gate_off"
else
	printf "census_gate_off\tFAILED\n" | tee -a "${OUTDIR}/summary.txt"
fi

if [[ "$VALIDATE_FULL" -eq 1 && "$SWEEP_ONLY" != "$ONLY" ]]; then
	echo "[tiktok] === validate_full_large_balanced ===" | tee -a "${OUTDIR}/summary.txt"
	json="${OUTDIR}/validate_full_large_balanced.json"
	cd "$REPO"
	if "$PHP" benchmarks/run_benchmarks.php --only="$ONLY" --large --no-case-timeout --no-verify \
		--no-save-last-json --out-json="$json" "${noBestExtArg[@]}" --bench-profile=large-balanced 2>&1 | tee -a "${OUTDIR}/run.log"; then
		summarize "$json" "validate_full_large_balanced"
	else
		printf "validate_full_large_balanced\tFAILED\n" | tee -a "${OUTDIR}/summary.txt"
	fi
fi

echo "[tiktok] === probe_recommend ===" | tee -a "${OUTDIR}/summary.txt"
cd "$REPO"
"$PHP" benchmarks/tiktok_probe_sweep.php --only="$SWEEP_ONLY" --out-dir="$OUTDIR/probe_recommend" --repeat=2 2>&1 | tee -a "${OUTDIR}/summary.txt"
echo "[tiktok] === probe_recommend_guard ===" | tee -a "${OUTDIR}/summary.txt"
"$PHP" benchmarks/guard_tiktok_probe_recommend.php \
	--recommend-json="$OUTDIR/probe_recommend/recommend.json" \
	--only="$SWEEP_ONLY" 2>&1 | tee -a "${OUTDIR}/summary.txt"
if [[ -f "$OUTDIR/probe_recommend/recommend.env" ]]; then
	echo "[tiktok] === probe_recommend_applied ===" | tee -a "${OUTDIR}/summary.txt"
	set +u
	# shellcheck disable=SC1091
	source "$OUTDIR/probe_recommend/recommend.env"
	set -u
	json="${OUTDIR}/probe_recommend_applied.json"
	"$PHP" benchmarks/run_benchmarks.php --only="$SWEEP_ONLY" --large --no-case-timeout --no-verify \
		--no-save-last-json --out-json="$json" "${noBestExtArg[@]}" --bench-profile=large-balanced 2>&1 | tee -a "${OUTDIR}/run.log"
	summarize "$json" "probe_recommend_applied"
fi
echo "[tiktok] === overall_recommend ===" | tee -a "${OUTDIR}/summary.txt"
"$PHP" benchmarks/tiktok_recommend_from_sweep.php --out-dir="$OUTDIR" 2>&1 | tee -a "${OUTDIR}/summary.txt"
echo "[tiktok] === recommend_drift ===" | tee -a "${OUTDIR}/summary.txt"
"$PHP" benchmarks/tiktok_recommend_drift.php --latest-out-dir="$OUTDIR" 2>&1 | tee -a "${OUTDIR}/summary.txt"
echo "[tiktok] === wins ===" | tee -a "${OUTDIR}/summary.txt"
"$PHP" benchmarks/tiktok_wins_report.php --out-dir="$OUTDIR" 2>&1 | tee -a "${OUTDIR}/summary.txt"

echo "[tiktok] done → ${OUTDIR}/summary.txt"
