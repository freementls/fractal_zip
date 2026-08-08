#!/usr/bin/env bash
# wire460 modeling sweep: layout/siteinfo/text_pack/article on words4096 stack (no dict fold).
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
export FRACTAL_ZIP_BACKGROUND=1
export FRACTAL_ZIP_SUPPRESS_HTML=1

PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-2048M}"
LOCK="$REPO/benchmarks/logs/.phda9_encode.lock"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$REPO/benchmarks/logs/wire460_modeling_${STAMP}.log"
PAGES="${1:-96}"
mkdir -p "$REPO/benchmarks/logs"

run_phda9() {
	echo ""
	echo "=== $(date -Is) pages=${PAGES} $* ==="
	if command -v ionice >/dev/null 2>&1; then
		flock -w 86400 "$LOCK" ionice -c 3 nice -n 19 "$@" \
			|| echo "WARN exit $? from: $*"
	else
		flock -w 86400 "$LOCK" nice -n 19 "$@" \
			|| echo "WARN exit $? from: $*"
	fi
}

exec > >(tee -a "$LOG") 2>&1
echo "wire460_modeling_sweep start $(date -Is) pages=${PAGES} mem=${PHP_MEM}"

run_phda9 php -d "memory_limit=${PHP_MEM}" \
	"$REPO/benchmarks/bench_wire460_modeling_sweep.php" \
	--pages="$PAGES"

if [[ "$PAGES" == "96" ]]; then
	echo "$(date -Is) 96p done — queue 384p if any arm beats baseline (manual: $0 384)"
fi

echo "wire460_modeling_sweep done $(date -Is) log=${LOG}"
