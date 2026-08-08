#!/usr/bin/env bash
# Hutter-priority CPU partition on 16-core host.
#
#   cores 0-7  → Hutter fx2-cmix (parallel fair screens; path-pinned when possible)
#   cores 8-13 → desktop / Cursor
#   cores 14-15 → lifestyle paq8px only (nice 10)
#
# Extrapolation: quiet_wall ≈ measured_wall * (931.0 / kitchen_measured_wall)
set -euo pipefail

HUTTER_POOL=${HUTTER_POOL:-0-7}
PAQ_CORES=${PAQ_CORES:-14-15}
HUTTER_NICE=${HUTTER_NICE:-0}
PAQ_NICE=${PAQ_NICE:-10}

echo "=== pin $(date -Is) hutter_pool=$HUTTER_POOL paq=$PAQ_CORES ==="

# Path-aware single-core pins when a fair dir is in the argv; else whole pool
for p in $(pgrep -f '/cmix_opt_|/cmix_match3m_fractalv2' || true); do
  cmd=$(tr '\0' ' ' < /proc/$p/cmdline 2>/dev/null || true)
  case "$cmd" in *pin_hutter*|*pgrep*) continue ;; esac
  cores=$HUTTER_POOL
  case "$cmd" in
    *fair_close4x_*|*fair_pinned_10MiB*|*fair_push_refs*) cores=0 ;;
    *fair_push_speed*) cores=1 ;;
    *fair_push_bytes*) cores=2 ;;
    *fair_push_fractal*) cores=3 ;;
    *fair_push_mix*) cores=4 ;;
    *fair_dual_*) cores=5 ;;
  esac
  # Honor explicit taskset already on a single core in the pool — don't widen
  cur=$(taskset -cp "$p" 2>/dev/null | awk -F: '{print $2}' | tr -d ' ')
  case "$cur" in
    [0-7]) cores=$cur ;;  # keep dedicated core
  esac
  taskset -cp "$cores" "$p" 2>/dev/null || true
  renice -n "$HUTTER_NICE" -p "$p" 2>/dev/null || true
  echo "pin pid=$p cores=$cores nice=$HUTTER_NICE :: ${cmd:0:140}"
done

for p in $(pgrep -f 'run_fair_|resume_c4|resume_dual|run_parallel_hutter' || true); do
  cmd=$(tr '\0' ' ' < /proc/$p/cmdline 2>/dev/null || true)
  case "$cmd" in *pin_hutter*|*pgrep*) continue ;; esac
  taskset -cp "$HUTTER_POOL" "$p" 2>/dev/null || true
  renice -n "$HUTTER_NICE" -p "$p" 2>/dev/null || true
done

# Lifestyle paq → 14-15; any other paq also parked there (Hutter owns 0-7)
for p in $(pgrep -x paq8px || true); do
  cmd=$(tr '\0' ' ' < /proc/$p/cmdline 2>/dev/null || true)
  taskset -cp "$PAQ_CORES" "$p" 2>/dev/null || true
  renice -n "$PAQ_NICE" -p "$p" 2>/dev/null || true
  echo "pin pid=$p cores=$PAQ_CORES nice=$PAQ_NICE :: $cmd"
done

ps -eo pid,pcpu,psr,nice,cmd | awk '
  /cmix_opt_|cmix_match3m_fractalv2|paq8px -/ && !/awk/ {
    printf "psr=%s nice=%s %s\n", $3, $4, substr($0, index($0,$5))
  }' | head -24
echo "DONE_PIN"
