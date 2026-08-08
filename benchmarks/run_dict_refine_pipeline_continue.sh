#!/usr/bin/env bash
# Continue dict/refine pipeline from step 3 (after compare + prose done).
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$REPO/benchmarks/logs/dict_refine_pipeline_continue_$(date +%Y%m%d_%H%M%S).log"
mkdir -p "$REPO/benchmarks/logs"
PHP="php -d memory_limit=768M"
exec > >(tee -a "$LOG") 2>&1

run() { echo "=== $(date -Is) $* ==="; "$@"; }

cd "$REPO"

run $PHP benchmarks/bench_phda9_dict_compress_prune.php --pages=96 --tool=phda9_no_lstm --removals=0 --additions=12
run $PHP benchmarks/bench_phda9_dict_lstm_seed_refine.php --pages=96 --trials=24 --seed=4096p
run $PHP benchmarks/bench_phda9_dict_mixed_hunt.php --pages=96 --refine-trials=24
run $PHP benchmarks/filter_cfabb_phda9.php --pages=96 \
	--in=benchmarks/.enwik8_cfabb_table_384p_filtered.json \
	--out=benchmarks/.enwik8_cfabb_table_96p_phda9.json

run $PHP benchmarks/bench_phda9_dict_compare_lstm.php --pages=384

for json in benchmarks/.enwik8_phda9_dict_lstm_seed_refine_96p.json \
	benchmarks/.enwik8_phda9_dict_compress_prune_96p.json \
	benchmarks/.enwik8_phda9_dict_mixed_hunt.json; do
	[[ -f "$json" ]] || continue
	delta=$(python3 - <<PY
import json, sys
j=json.load(open("$json"))
for k in ("delta","delta_vs_seed"):
    if k in j and j[k] is not None:
        print(j[k]); sys.exit(0)
rows=j.get("rows") or j.get("best_rows") or []
if rows:
    base=next((r.get("fzpa") for r in rows if r.get("label")=="baseline"), None)
    best=min((r.get("fzpa") for r in rows if r.get("fzpa")), default=None)
    if base and best: print(best-base); sys.exit(0)
print(0)
PY
)
	if [[ "$delta" =~ ^- && "$delta" != "-0" ]]; then
		dict=$(python3 - <<PY
import json
j=json.load(open("$json"))
for k in ("refined_dict","trained_dict","out_path","best_path","dict_path"):
    if j.get(k): print(j[k]); break
else:
    b=j.get("best") or {}
    if b.get("path"): print(b["path"])
PY
)
		if [[ -n "${dict:-}" && -f "$dict" ]]; then
			echo "384p wire confirm: $dict (Δ96p=$delta from $json)"
			FRACTAL_ZIP_WIRE_PROBE_PHDA9_DICT="$dict" \
				run $PHP benchmarks/bench_enwik8_wire_slice_probe.php --pages=384 \
				--case=split_inner_phda9_xml_single_stream_lstm_words4096_dict \
				--out-json="benchmarks/.enwik8_wire_dict_confirm_$(basename "$dict" .txt).json"
		fi
	fi
done

echo "=== continue pipeline done $(date -Is) log=$LOG ==="
