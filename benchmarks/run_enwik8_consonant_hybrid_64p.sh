#!/usr/bin/env bash
# Validate consonant_hybrid pipeline @64p before any 384p work.
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

echo "=== consonant_hybrid @${PAGES}p validation ==="

run "$REPO/tests/enwik_syllable_roundtrip_smoke.php"

run "$REPO/benchmarks/diag_consonant_hybrid_phda9_rt.php" "$PAGES"

run "$REPO/benchmarks/bench_enwik8_syllable_lab.php" --quick --pages="$PAGES"

run "$REPO/benchmarks/build_phda9_consonant_merged_dict.php" --pages="$PAGES"

run "$REPO/benchmarks/bench_phda9_dict_consonant_hybrid_hunt.php" --pages="$PAGES" --refine-trials=0

CASES="split_inner_phda9_xml_single_stream_lstm_mixed_dict"
CASES+=",split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid"
CASES+=",split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid_merged_dict"

run "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
  --pages="$PAGES" \
  --verify-rt \
  --out-json="$REPO/benchmarks/.enwik8_wire_slice_probe_${PAGES}p_consonant.json" \
  --cases="$CASES"

run "$REPO/benchmarks/check_consonant_hybrid.php" --pages="$PAGES"

echo "done → benchmarks/.enwik8_wire_slice_probe_${PAGES}p_consonant.json"
