#!/usr/bin/env bash
# Serial dict/refine pipeline @96p screen → LSTM @384p confirm for winners.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$REPO/benchmarks/logs/dict_refine_pipeline_$(date +%Y%m%d_%H%M%S).log"
mkdir -p "$REPO/benchmarks/logs"
PHP="php -d memory_limit=768M"
exec > >(tee -a "$LOG") 2>&1

run() {
	echo "=== $(date -Is) $* ==="
	"$@"
}

cd "$REPO"
WINNERS=()

record_win() {
	local label="$1" path="$2" delta="$3"
	if [[ "$delta" =~ ^- ]]; then
		WINNERS+=("${label}|${path}|${delta}")
		echo "WIN recorded: ${label} Δ=${delta}"
	fi
}

run $PHP benchmarks/bench_phda9_dict_compare_lstm.php --pages=96
run $PHP benchmarks/bench_phda9_dict_prose_compare_384p.php --pages=96
run $PHP benchmarks/bench_phda9_dict_compress_prune.php --pages=96 --tool=phda9_no_lstm --removals=0 --additions=12
run $PHP benchmarks/bench_phda9_dict_lstm_seed_refine.php --pages=96 --trials=24 --seed=4096p
run $PHP benchmarks/bench_phda9_dict_mixed_hunt.php --pages=96 --refine-trials=24
run $PHP benchmarks/bench_phda9_squash_train_enwik_validate.php --greedy-only --pages=96
run $PHP benchmarks/filter_cfabb_phda9.php --pages=96 \
	--in=benchmarks/.enwik8_cfabb_table_384p_filtered.json \
	--out=benchmarks/.enwik8_cfabb_table_96p_phda9.json

# @384p LSTM confirm for dict candidates with negative delta @96p
for json in \
	benchmarks/.enwik8_phda9_dict_lstm_seed_refine_96p.json \
	benchmarks/.enwik8_phda9_dict_compress_prune_96p.json \
	benchmarks/.enwik8_phda9_squash_train_96p.json; do
	if [[ -f "$json" ]]; then
		delta=$(python3 -c "import json; j=json.load(open('$json')); print(j.get('delta', j.get('delta_vs_seed', 0)))" 2>/dev/null || echo 0)
		if [[ "$delta" =~ ^- && "$delta" != "-0" ]]; then
			dict=$(python3 -c "import json; j=json.load(open('$json')); print(j.get('refined_dict', j.get('trained_dict', j.get('out_path',''))))" 2>/dev/null || true)
			if [[ -n "$dict" && -f "$dict" ]]; then
				echo "384p LSTM confirm: $dict (from $json)"
				FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9 FRACTAL_ZIP_PAQ_PHDA9_DICT="$dict" \
					$PHP benchmarks/bench_phda9_dict_compare_lstm.php --pages=384 || true
				FRACTAL_ZIP_WIRE_PROBE_PHDA9_DICT="$dict" \
					$PHP benchmarks/bench_enwik8_wire_slice_probe.php --pages=384 \
					--case=split_inner_phda9_xml_single_stream_lstm_words4096_dict \
					--out-json="benchmarks/.enwik8_wire_dict_confirm_$(basename "$dict" .txt).json" || true
			fi
		fi
	fi
done

echo "=== pipeline complete $(date -Is) log=$LOG ==="
