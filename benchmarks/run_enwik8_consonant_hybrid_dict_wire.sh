#!/usr/bin/env bash
# Mine consonant-aware phda9 dict @64p, then wire-probe baseline vs consonant_hybrid arms.
# Run run_enwik8_consonant_hybrid_64p.sh for the full validation suite.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
PHP_MEM="${PHP_MEM:-12G}"
PAGES=64
export FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii
export FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1
export FRACTAL_ZIP_PIPELINE_PARALLEL=0

run() {
  echo "+ $*"
  nice -n 19 php -d "memory_limit=${PHP_MEM}" "$@"
}

run "$REPO/benchmarks/bench_phda9_dict_consonant_hybrid_hunt.php" --pages="$PAGES" --refine-trials=4

run "$REPO/benchmarks/build_phda9_consonant_merged_dict.php" --pages="$PAGES"

run "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
  --pages="$PAGES" \
  --verify-rt \
  --out-json="$REPO/benchmarks/.enwik8_wire_slice_probe_${PAGES}p_consonant_dict.json" \
  --cases=split_inner_phda9_xml_single_stream_lstm_mixed_dict,split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid,split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid_merged_dict

echo "done → benchmarks/.enwik8_wire_slice_probe_${PAGES}p_consonant_dict.json"
