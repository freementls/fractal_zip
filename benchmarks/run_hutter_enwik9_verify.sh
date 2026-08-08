#!/usr/bin/env bash
# Full enwik9 verify for fx2-cmix fork.
# Default profile: disk (cmix_disk — full model, PPM heap on ppm.temp). Override with
# HUTTER_PROFILE=ram7 for the reduced model, or CMIX=... for an explicit binary.
# Prize limits: ≤10 GiB RAM, ≤100 GiB temp disk; on 7 GiB hosts we trade disk for RAM.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FX2="$ROOT/tools/hutter/fx2-cmix/run"
ENWIK9="${1:-$ROOT/tools/hutter/data/enwik9}"
OUT_JSON="$ROOT/benchmarks/.hutter_enwik9_verify.json"
PROFILE="${HUTTER_PROFILE:-disk}"
MIN_GIB="${HUTTER_MIN_GIB:-6}"
MIN_DISK_GIB="${HUTTER_MIN_DISK_GIB:-35}"

MEM_KB=$(awk '/MemTotal/ {print $2}' /proc/meminfo)
MEM_GB=$((MEM_KB / 1024 / 1024))
if (( MEM_GB < MIN_GIB )); then
  echo "ABORT: MemTotal=${MEM_GB}GiB < ${MIN_GIB}GiB required for profile=$PROFILE" >&2
  echo "See benchmarks/HUTTER_PRIZE_WIN_PATH.md and tools/hutter/data/GET_ENWIK9.md" >&2
  cat >"$OUT_JSON" <<EOF
{"status":"blocked_ram","mem_gib":$MEM_GB,"required_gib":$MIN_GIB,"profile":"$PROFILE","enwik9":"$ENWIK9"}
EOF
  exit 2
fi

WORKDIR="${HUTTER_WORKDIR:-$FX2}"
mkdir -p "$WORKDIR"
DISK_AVAIL_KB=$(df -Pk "$WORKDIR" | awk 'NR==2 {print $4}')
DISK_GB=$((DISK_AVAIL_KB / 1024 / 1024))
if [[ "$PROFILE" == "disk" ]] && (( DISK_GB < MIN_DISK_GIB )); then
  echo "ABORT: ${DISK_GB}GiB free on $(df -Pk "$WORKDIR" | awk 'NR==2 {print $1}') < ${MIN_DISK_GIB}GiB for disk profile" >&2
  echo "Run: benchmarks/hutter_disk_prep.sh --prune-fz-caches  (or free space elsewhere)" >&2
  cat >"$OUT_JSON" <<EOF
{"status":"blocked_disk","disk_gib_free":$DISK_GB,"required_gib":$MIN_DISK_GIB,"profile":"$PROFILE","workdir":"$WORKDIR","enwik9":"$ENWIK9"}
EOF
  exit 2
fi
if [[ ! -f "$ENWIK9" ]]; then
  echo "missing enwik9 at $ENWIK9" >&2
  exit 1
fi
SZ=$(stat -c%s "$ENWIK9")
if [[ "$SZ" != "1000000000" ]]; then
  echo "WARN: enwik9 size=$SZ (expected 1000000000)" >&2
fi

# Prefer matching profile binary; full `cmix` only when explicitly requested.
if [[ -n "${CMIX:-}" ]]; then
  :
elif [[ "$PROFILE" == "disk" && -x "$FX2/cmix_disk" ]]; then
  CMIX="$FX2/cmix_disk"
elif [[ "$PROFILE" == "ram7" && -x "$FX2/cmix_ram7" ]]; then
  CMIX="$FX2/cmix_ram7"
elif [[ -x "$FX2/cmix_disk" ]]; then
  CMIX="$FX2/cmix_disk"
elif [[ -x "$FX2/cmix" ]]; then
  CMIX="$FX2/cmix"
elif [[ -x "$FX2/cmix_improved" ]]; then
  CMIX="$FX2/cmix_improved"
else
  echo "no cmix binary in $FX2 (build: make disk or make ram7)" >&2
  exit 1
fi

cd "$WORKDIR"
rm -f archive9 enwik9_restored enwik9.comp
echo "compressing with $CMIX (profile=$PROFILE mem=${MEM_GB}GiB disk_free=${DISK_GB}GiB workdir=$WORKDIR) ..."
/usr/bin/time -v "$CMIX" -e "$ENWIK9" enwik9.comp 2>compress.time
echo "decompressing archive9 ..."
/usr/bin/time -v ./archive9 2>decompress.time
cmp -s enwik9_restored "$ENWIK9"
S1=$(stat -c%s "$CMIX")
S2=$(stat -c%s archive9)
PEAK_C=$(awk -F: '/Maximum resident/ {gsub(/^[ \t]+/,"",$2); print $2+0}' compress.time)
PEAK_D=$(awk -F: '/Maximum resident/ {gsub(/^[ \t]+/,"",$2); print $2+0}' decompress.time)
python3 - <<PY
import json
L=110793128
S1=$S1; S2=$S2; S=S1+S2
peak_c=$PEAK_C; peak_d=$PEAK_D
payload={
  "status":"ok",
  "profile":"$PROFILE",
  "cmix":"$CMIX",
  "mem_gib":$MEM_GB,
  "disk_gib_free":$DISK_GB,
  "workdir":"$WORKDIR",
  "S1":S1,"S2":S2,"S":S,
  "L":L,
  "prize_eligible": S < int(L*0.99),
  "improvement": 1-S/L,
  "peak_rss_kb_compress": peak_c,
  "peak_rss_kb_decompress": peak_d,
  "note": "disk profile uses ppm.temp (~14GiB peak); prize temp cap is 100GiB",
}
print(json.dumps(payload, indent=2))
open("$OUT_JSON","w").write(json.dumps(payload, indent=2)+"\n")
PY
