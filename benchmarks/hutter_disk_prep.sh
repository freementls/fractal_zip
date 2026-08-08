#!/usr/bin/env bash
# Check / reclaim disk for Hutter prize runs (PPM heap + -e intermediates).
# Prize allows ≤100 GiB temp; local full -e peak is ~35–45 GiB on this fork.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WORKDIR="${HUTTER_WORKDIR:-$ROOT/tools/hutter/fx2-cmix/run}"
MIN_GIB="${HUTTER_MIN_DISK_GIB:-35}"

prune=0
while [[ $# -gt 0 ]]; do
  case "$1" in
    --prune-fz-caches) prune=1; shift ;;
    -h|--help)
      echo "usage: $0 [--prune-fz-caches]"
      echo "  HUTTER_WORKDIR=$WORKDIR  HUTTER_MIN_DISK_GIB=$MIN_GIB"
      exit 0 ;;
    *) echo "unknown arg: $1" >&2; exit 1 ;;
  esac
done

mkdir -p "$WORKDIR"
AVAIL_KB=$(df -Pk "$WORKDIR" | awk 'NR==2 {print $4}')
AVAIL_GIB=$((AVAIL_KB / 1024 / 1024))
PART=$(df -hP "$WORKDIR" | awk 'NR==2 {print $1" "$6}')

echo "Hutter workdir: $WORKDIR"
echo "Partition: $PART"
echo "Free: ${AVAIL_GIB} GiB (need ≥${MIN_GIB} GiB for disk profile enwik9 -e)"
echo "Expected peak temp in workdir:"
echo "  ppm.temp          ~14 GiB (PPM heap, cmix_disk)"
echo "  -e pipeline       ~20–25 GiB (reorder + compress scratch)"
echo "  archive9 + logs   ~1 GiB"

if (( prune )); then
  for d in \
    "$ROOT/benchmarks/.squash_corpus_cache" \
    "$ROOT/benchmarks/.web_ref_verify_extract" \
    "$ROOT/benchmarks/.web_ref_verify_extract_unlimited"; do
    if [[ -d "$d" ]]; then
      sz=$(du -sh "$d" | awk '{print $1}')
      echo "pruning $d ($sz)"
      rm -rf "$d"
    fi
  done
  AVAIL_KB=$(df -Pk "$WORKDIR" | awk 'NR==2 {print $4}')
  AVAIL_GIB=$((AVAIL_KB / 1024 / 1024))
  echo "Free after prune: ${AVAIL_GIB} GiB"
fi

if (( AVAIL_GIB < MIN_GIB )); then
  echo "BLOCKED: need ${MIN_GIB} GiB free for enwik9 disk run" >&2
  exit 2
fi
echo "OK: disk headroom sufficient"
