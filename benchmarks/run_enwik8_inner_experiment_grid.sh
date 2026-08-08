#!/usr/bin/env bash
# Inner-first enwik8 grid (no PAQ; no bench_world_record_apply_high_env unless you set it elsewhere).
set -euo pipefail
cd "$(dirname "$0")/.."
PHP="${PHP:-/usr/bin/php}"
PHP_ARGS=(-d memory_limit=4096M -d opcache.enable_cli=0)
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
LOCK="${PWD}/benchmarks/.enwik8_inner_grid.lock"

run_one() {
  local name="$1"
  local out="benchmarks/.enwik8_exp_${name}.json"
  if [[ -f "${out}" ]] && "${PHP}" "${PHP_ARGS[@]}" benchmarks/enwik8_exp_has_fzc.php "${out}"; then
    echo "[inner-grid] ${name} skip (valid fzc in ${out})"
    return 0
  fi
  echo "[inner-grid] ${name} starting $(date -Iseconds)"
  "${PHP}" "${PHP_ARGS[@]}" benchmarks/run_enwik8_inner_experiment.php "--case=${name}"
}

exec 9>"${LOCK}"
if ! flock -n 9; then
  echo "[inner-grid] another grid/encode holds ${LOCK} — exit"
  exit 0
fi

for c in inner_baseline inner_deep inner_allsub inner_multidiff_caps inner_combo inner_recursive0; do
  run_one "${c}"
done

"${PHP}" "${PHP_ARGS[@]}" benchmarks/summarize_enwik8_experiments.php 2>/dev/null || true
