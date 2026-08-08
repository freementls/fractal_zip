#!/usr/bin/env bash
# Wire-gated phda9 dict refine: score add/remove by honest .fz wire (wire460 stack).
#
# Phase 1 @96p (fast screen), phase 2 @384p validate if 96p wins.
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
export FRACTAL_ZIP_BACKGROUND=1
export FRACTAL_ZIP_SUPPRESS_HTML=1

PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-2048M}"
LOCK="$REPO/benchmarks/logs/.phda9_encode.lock"
STAMP="$(date +%Y%m%d_%H%M%S)"
LOG="$REPO/benchmarks/logs/wire460_dict_wire_refine_${STAMP}.log"
MODE="${1:-screen384}"
mkdir -p "$REPO/benchmarks/logs"

run_phda9() {
	echo ""
	echo "=== $(date -Is) $* ==="
	if command -v ionice >/dev/null 2>&1; then
		flock -w 86400 "$LOCK" ionice -c 3 nice -n 19 "$@" \
			|| echo "WARN exit $? from: $*"
	else
		flock -w 86400 "$LOCK" nice -n 19 "$@" \
			|| echo "WARN exit $? from: $*"
	fi
}

exec > >(tee -a "$LOG") 2>&1
echo "wire460_dict_wire_refine start $(date -Is) mode=${MODE} mem=${PHP_MEM}"

case "$MODE" in
	screen384)
		run_phda9 php -d "memory_limit=${PHP_MEM}" \
			"$REPO/benchmarks/bench_wire460_dict_wire_refine.php" \
			--pages=96 --validate-pages=384 \
			--removals=8 --additions=12
		run_phda9 php -d "memory_limit=${PHP_MEM}" \
			"$REPO/benchmarks/bench_wire460_dict_wire_refine.php" \
			--pages=384 \
			--removals=12 --additions=16 \
			--seed="$REPO/benchmarks/.phda9_external_dict_wire_refine_96p.txt"
		;;
	384)
		run_phda9 php -d "memory_limit=${PHP_MEM}" \
			"$REPO/benchmarks/bench_wire460_dict_wire_refine.php" \
			--pages=384 --removals=12 --additions=16
		;;
	96)
		run_phda9 php -d "memory_limit=${PHP_MEM}" \
			"$REPO/benchmarks/bench_wire460_dict_wire_refine.php" \
			--pages=96 --removals=8 --additions=12
		;;
	*)
		echo "usage: $0 [screen384|384|96]"
		exit 1
		;;
esac

echo "wire460_dict_wire_refine done $(date -Is) log=${LOG}"
