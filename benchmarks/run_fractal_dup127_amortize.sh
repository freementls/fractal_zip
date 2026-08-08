#!/usr/bin/env bash
# Validate local-dict ΔS2 on a larger sample of dup cluster 127.
# Hypothesis: Δ/art from 40-art sample (~−43 B/art) holds enough that
# full-cluster amortization (32 743 arts) pays for local S1 (6 391).
set -euo pipefail
ROOT=/srv/http/fractal_zip
OUTDIR=$ROOT/benchmarks/.ladder_cache/fractal_dictlab
N=${1:-400}
python3 "$ROOT/benchmarks/run_fractal_local_dict_lab.py" \
  --cluster-tsv "$ROOT/benchmarks/.ladder_cache/fractal_clusters/enwik9_article_clusters_full.tsv" \
  --binary "$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b" \
  --global-dict "$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic" \
  --global-s1-banked 101630 \
  --max-clusters 1 --kinds dup --min-cluster-size 5000 \
  --max-articles-per-cluster "$N" \
  --local-dict-words 4000 \
  --workdir "$OUTDIR/dup127_n${N}" \
  --out "$OUTDIR/dup127_amortize_n${N}.json" \
  2>&1 | tee "$OUTDIR/dup127_amortize_n${N}.log"
chmod +x "$ROOT/benchmarks/run_fractal_dup127_amortize.sh" 2>/dev/null || true
python3 "$ROOT/benchmarks/analyze_fractal_local_dicts.py" \
  "$OUTDIR/dup127_amortize_n${N}.json" \
  --cluster-tsv "$ROOT/benchmarks/.ladder_cache/fractal_clusters/enwik9_article_clusters_full.tsv"
