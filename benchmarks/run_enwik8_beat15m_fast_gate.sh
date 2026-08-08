#!/usr/bin/env bash
# Sub-30min Beat-15M gate loop (see docs/BEAT_15M_FAST_GATE.md).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
php benchmarks/run_enwik8_beat15m_fast_gate.php "$@"
