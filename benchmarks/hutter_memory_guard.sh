#!/usr/bin/env bash
# Pre-flight for Hutter heavy jobs. Exit 2 if unsafe to start.
# Defaults sized for 38 GiB hosts running full prize profile (~9.5 GiB peak).
# Override: HUTTER_MIN_AVAIL_MIB=1200 for small-RAM disk-PPM screens.
set -euo pipefail
MIN_AVAIL_MIB="${HUTTER_MIN_AVAIL_MIB:-12288}"
# 38 GiB host: Cursor/desktop can leave a few GiB of swap even when MemAvailable is fine.
MAX_SWAP_MIB="${HUTTER_MAX_SWAP_MIB:-4096}"
WORKDIR="${HUTTER_WORKDIR:-/srv/http/fractal_zip/tools/hutter/fx2-cmix/run}"

avail=$(awk '/MemAvailable/ {print int($2/1024)}' /proc/meminfo)
swap=$(awk '/SwapTotal/ {t=$2} /SwapFree/ {f=$2} END {print int((t-f)/1024)}' /proc/meminfo)
cmix_n=$(ps -eo comm= 2>/dev/null | rg -c '^cmix' || true)
cmix_n=${cmix_n:-0}

echo "mem_available=${avail}MiB swap_used=${swap}MiB cmix_procs=${cmix_n}"
if (( cmix_n > 0 )) && [[ "${HUTTER_ALLOW_PARALLEL:-0}" != "1" ]]; then
  echo "BLOCK: another cmix already running" >&2
  exit 2
fi
if (( cmix_n > 0 )); then
  echo "WARN: parallel cmix allowed (HUTTER_ALLOW_PARALLEL=1), existing=${cmix_n}"
fi
# Parallel 1m screens need less free RAM than a full prize job.
if [[ "${HUTTER_ALLOW_PARALLEL:-0}" == "1" ]]; then
  MIN_AVAIL_MIB="${HUTTER_MIN_AVAIL_MIB:-6144}"
fi
if (( avail < MIN_AVAIL_MIB )); then
  echo "BLOCK: need >=${MIN_AVAIL_MIB}MiB MemAvailable (have ${avail})" >&2
  exit 2
fi
if (( swap > MAX_SWAP_MIB )); then
  echo "BLOCK: swap used ${swap}MiB > ${MAX_SWAP_MIB}MiB — wait for pressure to drop" >&2
  exit 2
fi
# Only wipe ppm.temp in workdir when not racing another job there.
if (( cmix_n == 0 )) || [[ "${HUTTER_ALLOW_PARALLEL:-0}" == "1" ]]; then
  rm -f "$WORKDIR/ppm.temp"
fi
echo "OK to start one Hutter job"
