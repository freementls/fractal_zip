#!/usr/bin/env bash
# Kill orphaned fractal_zip benchmark PHP, compressors, and agent wait loops.
# Safe to run anytime your CPUs are pinned after a bench/smoke was interrupted.
#
# Usage: benchmarks/kill_stray_bench_procs.sh [--work-only]
#   --work-only  only kill processes whose cwd is under benchmarks/.work

set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
WORK="${REPO}/benchmarks/.work"
WORK_ONLY=0
if [[ "${1:-}" == "--work-only" ]]; then
	WORK_ONLY=1
fi

pkill -TERM -f 'fz(arcbench|7bench|exarc|7ex|ocp_|ocin_|ocbr_|case_|brbench|pmb1_)' 2>/dev/null || true

if [[ "$WORK_ONLY" -eq 0 ]]; then
	pkill -TERM -f 'sleep 3600.*(test_files78|fractal_zip|bench78|silesia78)' 2>/dev/null || true
	# Orphan bench PHP only (reparented to init), not an in-flight run_benchmarks you started intentionally.
	for ent in /proc/[0-9]*; do
		[[ -r "$ent/cmdline" ]] || continue
		pid="${ent##*/}"
		[[ "$pid" -gt 1 ]] || continue
		ppid="$(ps -o ppid= -p "$pid" 2>/dev/null | tr -d ' ' || echo 0)"
		[[ "${ppid:-0}" -eq 1 ]] || continue
		cmd="$(tr '\0' ' ' <"$ent/cmdline" 2>/dev/null || true)"
		[[ "$cmd" == *"${REPO}/benchmarks/run_benchmarks"* ]] || continue
		kill -TERM "$pid" 2>/dev/null || true
	done
	sleep 0.2
	pkill -TERM -f "${REPO}/benchmarks/smoke_" 2>/dev/null || true
	pkill -TERM -f "${REPO}/benchmarks/roundtrip_" 2>/dev/null || true
	pkill -TERM -f "${REPO}/benchmarks/repro_" 2>/dev/null || true
	sleep 0.2
	pkill -KILL -f 'sleep 3600.*(test_files78|fractal_zip|bench78|silesia78)' 2>/dev/null || true
	for ent in /proc/[0-9]*; do
		[[ -r "$ent/cmdline" ]] || continue
		pid="${ent##*/}"
		[[ "$pid" -gt 1 ]] || continue
		ppid="$(ps -o ppid= -p "$pid" 2>/dev/null | tr -d ' ' || echo 0)"
		[[ "${ppid:-0}" -eq 1 ]] || continue
		cmd="$(tr '\0' ' ' <"$ent/cmdline" 2>/dev/null || true)"
		[[ "$cmd" == *"${REPO}/benchmarks/run_benchmarks"* ]] || continue
		kill -KILL "$pid" 2>/dev/null || true
	done
	pkill -KILL -f "${REPO}/benchmarks/smoke_" 2>/dev/null || true
	pkill -KILL -f "${REPO}/benchmarks/roundtrip_" 2>/dev/null || true
	pkill -KILL -f "${REPO}/benchmarks/repro_" 2>/dev/null || true
	pkill -KILL -f 'fz(arcbench|7bench|exarc|7ex|ocp_|ocin_|ocbr_|case_|brbench|pmb1_)' 2>/dev/null || true
fi

if [[ -d "$WORK" ]]; then
	PREFIX="$(readlink -f "$WORK")/"
	PLEN=${#PREFIX}
	for ent in /proc/[0-9]*; do
		[[ -d "$ent/cwd" ]] || continue
		pid="${ent##*/}"
		cwd="$(readlink -f "$ent/cwd" 2>/dev/null || true)"
		[[ -n "$cwd" && "$cwd" == "${PREFIX}"* ]] || continue
		comm="$(tr -d '\0' <"$ent/comm" 2>/dev/null || true)"
		case "$comm" in
			zpaq|brotli|arc|7z|7za|xz|pigz|gzip|php)
				kill -TERM "$pid" 2>/dev/null || true
				;;
		esac
	done
	sleep 0.2
	for ent in /proc/[0-9]*; do
		[[ -d "$ent/cwd" ]] || continue
		pid="${ent##*/}"
		cwd="$(readlink -f "$ent/cwd" 2>/dev/null || true)"
		[[ -n "$cwd" && "$cwd" == "${PREFIX}"* ]] || continue
		comm="$(tr -d '\0' <"$ent/comm" 2>/dev/null || true)"
		case "$comm" in
			zpaq|brotli|arc|7z|7za|xz|pigz|gzip|php)
				kill -KILL "$pid" 2>/dev/null || true
				;;
		esac
	done
fi

echo "stray bench sweep done (repo=${REPO})"
