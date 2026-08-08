#!/usr/bin/env bash
# Grid enwik8 encode-only experiments (world-record env, no web-ref).
set -euo pipefail
cd "$(dirname "$0")/.."
PHP="${PHP:-php}"

run_one() {
  local name="$1"
  shift
  local out="benchmarks/.enwik8_exp_${name}.json"
  if [[ -f "${out}" ]] && "${PHP}" benchmarks/enwik8_exp_has_fzc.php "${out}"; then
    echo "[grid] ${name} skip (valid fzc in ${out})"
    return 0
  fi
  export "$@"
  echo "[grid] ${name} env: $*"
  "${PHP}" benchmarks/run_enwik8_encode_only.php "--name=${name}"
}

# pp64 baseline already seeded from .enwik8_world_record.json when present
if [[ ! -f benchmarks/.enwik8_exp_pp64.json && -f benchmarks/.enwik8_world_record.json ]]; then
  cp -f benchmarks/.enwik8_world_record.json benchmarks/.enwik8_exp_pp64.json
fi

run_one pp32 FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=32 FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1
run_one pp48 FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=48 FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1
run_one pp64 FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=64 FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1
run_one pp96 FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=96 FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1
# True legacy zip_folder: no heterogeneous shootout, no unified stream, no multidiff→unified shortcut.
run_one unified0 FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=48 FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0 FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST=0 FRACTAL_ZIP_SUBSTRING_MULTIDIFF_RECURSIVE_ONLY=0
run_one unified1 FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=48 FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1

if [[ "${FRACTAL_ZIP_SKIP_SEMANTIC_EXP:-}" != "1" ]]; then
  run_one semantic1 FRACTAL_ZIP_ENWIK_SEMANTIC_PACK=1 FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=48 FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1
fi

if [[ "${FRACTAL_ZIP_RUN_HARMONY_EXP:-}" == "1" ]]; then
  "${PHP}" benchmarks/run_enwik8_harmony_encode.php --name=harmony1
fi

"${PHP}" benchmarks/summarize_enwik8_experiments.php
