#!/usr/bin/env bash
# After high tier merge: patch WR timing, preset verify encode, refresh metrics.
set -euo pipefail
cd "$(dirname "$0")/.."
PHP="${PHP:-php}"
LOG="benchmarks/logs/enwik8_post_merge.log"
mkdir -p benchmarks/logs

{
  echo "=== $(date -Iseconds) patch world_record zip_seconds from pp96_high ==="
  "${PHP}" benchmarks/patch_enwik8_world_record_fzc.php benchmarks/.enwik8_exp_pp96_high.json

  if ! "${PHP}" benchmarks/enwik8_exp_has_fzc.php benchmarks/.enwik8_exp_preset_verify.json 2>/dev/null; then
    echo "=== preset encode + roundtrip verify ==="
    "${PHP}" benchmarks/run_enwik8_encode_verify.php --name=preset_verify
  else
    echo "=== preset_verify JSON exists — skip encode ==="
  fi

  echo "=== metrics ==="
  "${PHP}" benchmarks/compare_enwik8_world_record.php
  "${PHP}" benchmarks/compare_enwik8_four_way.php || true

  echo "=== done $(date -Iseconds) ==="
} 2>&1 | tee -a "${LOG}"
