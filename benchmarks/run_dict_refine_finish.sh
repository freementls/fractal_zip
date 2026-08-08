#!/usr/bin/env bash
# Fast-finish dict/refine: phrase-only refine (4 trials), seed refine (8), mixed hunt (8), cfabb @96p, @384p compare.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$REPO/benchmarks/logs/dict_refine_finish_$(date +%Y%m%d_%H%M%S).log"
PHP="php -d memory_limit=768M"
exec > >(tee -a "$LOG") 2>&1
run() { echo "=== $(date -Is) $* ==="; "$@"; }
cd "$REPO"

run $PHP benchmarks/bench_phda9_dict_compress_prune.php --pages=96 --tool=phda9_no_lstm --removals=0 --additions=4
run $PHP benchmarks/bench_phda9_dict_lstm_seed_refine.php --pages=96 --trials=8 --seed=4096p
run $PHP benchmarks/bench_phda9_dict_mixed_hunt.php --pages=96 --refine-trials=8
run $PHP benchmarks/filter_cfabb_phda9.php --pages=96 \
	--in=benchmarks/.enwik8_cfabb_table_384p_filtered.json \
	--out=benchmarks/.enwik8_cfabb_table_96p_phda9.json
run $PHP benchmarks/bench_phda9_dict_compare_lstm.php --pages=384

# Wire confirm any @96p winner vs 4096p baseline (37536 @96p LSTM)
BASE=37536
for json in benchmarks/.enwik8_phda9_dict_lstm_seed_refine_96p.json \
	benchmarks/.enwik8_phda9_dict_compress_prune_96p.json; do
	[[ -f "$json" ]] || continue
	read -r delta dict <<< "$(python3 - <<PY
import json
j=json.load(open("$json"))
d=j.get("delta", j.get("delta_vs_seed"))
p=j.get("refined_dict") or j.get("out_path")
print(d if d is not None else 0, p or "")
PY
)"
	if [[ "$delta" =~ ^- && "$delta" != "-0" && -n "$dict" && -f "$dict" ]]; then
		run env FRACTAL_ZIP_WIRE_PROBE_PHDA9_DICT="$dict" $PHP benchmarks/bench_enwik8_wire_slice_probe.php \
			--pages=384 --case=split_inner_phda9_xml_single_stream_lstm_words4096_dict \
			--out-json="benchmarks/.enwik8_wire_dict_confirm_$(basename "$dict" .txt).json"
	fi
done

echo "=== finish log=$LOG ==="
