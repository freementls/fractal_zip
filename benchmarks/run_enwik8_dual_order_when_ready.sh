#!/usr/bin/env bash
# Wait for .enwik8_paq_squash.fzpq (or export finishing), then dual-order encode + metrics.
set -euo pipefail
cd "$(dirname "$0")/.."
PHP="${PHP:-php}"
WIRE="benchmarks/.enwik8_paq_squash.fzpq"
LOG="benchmarks/logs/enwik8_dual_order_when_ready.log"
POLL="${POLL_SEC:-300}"
mkdir -p benchmarks/logs

log() { echo "[$(date -Iseconds)] $*" | tee -a "${LOG}"; }

log "watching ${WIRE} (poll ${POLL}s)"

while true; do
  if [[ -f "${WIRE}" ]]; then
    if "${PHP}" -r 'require "fractal_zip_enwik.php"; exit(fractal_zip_enwik_try_load_paq_squash_wire(getcwd()) === null ? 1 : 0);'; then
      log "wire cache valid — starting dual-order pipeline"
      bash benchmarks/run_enwik8_dual_order_pipeline.sh
      exit $?
    fi
    log "WARN: ${WIRE} exists but failed JSON/wire validation"
  fi
  if ! pgrep -f 'bench_enwik8_paq_export_wire.php' >/dev/null 2>&1 && ! pgrep -f 'tools/phda9/phda9' >/dev/null 2>&1; then
    if [[ ! -f "${WIRE}" ]]; then
      log "export/phda9 not running and no wire — exit 1 (check benchmarks/logs/enwik8_paq_export_wire.log)"
      exit 1
    fi
  fi
  "${PHP}" benchmarks/bench_enwik8_paq_progress.php 2>&1 | tee -a "${LOG}" || true
  sleep "${POLL}"
done
