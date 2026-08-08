#!/usr/bin/env bash
# Encode enwik8 with world-record env + FRACTAL_ZIP_WEB_REF=1 (separate from world-record preset).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
CORPUS="$ROOT/benchmarks/test_files109/enwik8"
if [[ ! -f "$CORPUS" ]]; then
  php benchmarks/bench_enwik8_web_ref_probe.php || true
fi
PROBE_JSON="$ROOT/benchmarks/.enwik8_web_ref_probe.json"
if [[ ! -f "$PROBE_JSON" ]]; then
  echo "[web-ref track] running fast probe (no existing JSON)…" >&2
  FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR=1 \
  FRACTAL_ZIP_BENCH_MEMORY_LIMIT="${FRACTAL_ZIP_BENCH_MEMORY_LIMIT:-4G}" \
    php benchmarks/bench_enwik8_web_ref_probe.php || true
else
  echo "[web-ref track] using existing probe JSON; skipping FULL_SCAN" >&2
fi
# Apply web-ref env after world-record defaults (bench_web_ref wins on WEB_REF=1).
export FRACTAL_ZIP_WEB_REF=1
export FRACTAL_ZIP_WEB_REF_WHOLE_PAGE=1
export FRACTAL_ZIP_WEB_REF_CORPUS_PIECES=1
export FRACTAL_ZIP_WEB_REF_WAYBACK=1
export FRACTAL_ZIP_WEB_REF_URL_LITERAL=0
export FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR=1
export FRACTAL_ZIP_WEB_REF_PROBE_MAX_CHUNKS=30
export FRACTAL_ZIP_WEB_REF_PROBE_MAX_URLS=40
export FRACTAL_ZIP_WEB_REF_WHOLE_PAGE_MAX="${FRACTAL_ZIP_WEB_REF_WHOLE_PAGE_MAX:-200}"
export FRACTAL_ZIP_WEB_REF_URL_LITERAL_MIN_GAIN=16
export FRACTAL_ZIP_WEB_REF_MIN_STABILITY=40
export FRACTAL_ZIP_BENCH_MEMORY_LIMIT="${FRACTAL_ZIP_BENCH_MEMORY_LIMIT:-4G}"
php -r 'require "benchmarks/bench_web_ref_env.php"; bench_web_ref_apply_probe_fast_defaults();' >/dev/null
php benchmarks/run_benchmarks.php \
    --only=test_files109 \
    --bench-profile=world-record \
    --no-case-timeout \
    --json \
    --out-json=benchmarks/.enwik8_web_ref_track.json \
    "$@"
