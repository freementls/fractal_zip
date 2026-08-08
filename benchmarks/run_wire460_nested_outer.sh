#!/usr/bin/env bash
# wire460: content-tailored nested outers (phda9 inner stacks + container outer + optional shootout).
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"
export FRACTAL_ZIP_NO_CLI_OPCACHE_REEXEC=1

PAGES="${1:-96}"
TOP="${2:-5}"
DICT="${3:-$REPO/benchmarks/.phda9_external_dict_words_4096p.txt}"
WIRE_ONLY="${WIRE_ONLY:-0}"
LOG="$REPO/benchmarks/logs/wire460_nested_outer_${PAGES}p.log"
mkdir -p "$REPO/benchmarks/logs"

run_low() {
	if command -v ionice >/dev/null 2>&1; then
		ionice -c 3 nice -n 19 "$@"
	else
		nice -n 19 "$@"
	fi
}

exec > >(tee -a "$LOG") 2>&1
echo "=== wire460 nested outer matrix @${PAGES}p top=${TOP} wire_only=${WIRE_ONLY} $(date -Is) ==="

ARGS=(--pages="$PAGES" --top="$TOP" --dict="$DICT" --out="$REPO/benchmarks/.enwik8_wire460_nested_outer_matrix.json")
if [[ "$WIRE_ONLY" == "1" ]]; then
	ARGS+=(--wire-only)
fi

run_low php -d memory_limit=4096M "$REPO/benchmarks/bench_wire460_nested_outer_matrix.php" "${ARGS[@]}"

echo "=== done $(date -Is) ==="
