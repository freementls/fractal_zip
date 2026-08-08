#!/usr/bin/env bash
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
PHP="${PHP:-/usr/bin/php}"
PAGES="${1:-96}"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$REPO/benchmarks/logs/wire460_sidecar_push_${STAMP}.log"
mkdir -p "$REPO/benchmarks/logs"
exec > >(tee -a "$LOG") 2>&1
echo "wire460_sidecar_push start $(date -Iseconds) pages=$PAGES"
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
nice -n 19 ionice -c 3 "$PHP" -d memory_limit=2048M "$REPO/benchmarks/bench_wire460_sidecar_push.php" --pages="$PAGES"
echo "wire460_sidecar_push done $(date -Iseconds) → $LOG"
