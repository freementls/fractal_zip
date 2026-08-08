#!/usr/bin/env bash
# L1-all-templates cmix ablation (run when no other cmix is active).
# Compares raw/stock vs body/stock vs body/matched on the 10m mid slice.
# Sidecar scored with zstd (tiny); totals printed for honesty.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
bash "$ROOT/benchmarks/hutter_memory_guard.sh"
RUN="$ROOT/tools/hutter/fx2-cmix/run"
L1="$ROOT/benchmarks/.ladder_cache/l1_lite"
LOG="$ROOT/benchmarks/.hutter_logs/l1_cmix_ablation.log"
CMIX="${CMIX:-$RUN/cmix_lto}"
STOCK="$ROOT/tools/hutter/fx2-cmix/dictionary/english.dic"
MATCHED="$L1/english_l1allbody_e8_10m.dic"
RAW="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
BODY="$L1/e8_10m_l1all_body.bin"
SC="$L1/e8_10m_l1all_sidecar.bin"
BASE_RAW=1751514   # A0 10m baseline (for raw arm only)
mkdir -p "$ROOT/benchmarks/.hutter_logs"
: >"$LOG"
sc_z=$(zstd -12 --long=30 -T8 -c -q "$SC" | wc -c)
echo "sidecar_zstd=$sc_z" | tee -a "$LOG"

screen() {
  local tag="$1" dict="$2" plain="$3" base="$4"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" "$tag" "$CMIX" "$dict" "$plain" "$base" | tee -a "$LOG"
}

# raw + stock
screen L1abl_raw_stock "$STOCK" "$RAW" "$BASE_RAW"
# body + stock (inconsistent)
# baseline 0 → report absolute; we compare arms manually
BYTES_BODY=$(stat -c%s "$BODY")
# use a dummy baseline = body size so delta is meaningless; log bytes from jsonl
screen L1abl_body_stock "$STOCK" "$BODY" 1
# body + matched (consistent)
screen L1abl_body_matched "$MATCHED" "$BODY" 1

python3 - <<PY
import json
from pathlib import Path
rows=[]
for line in open("$ROOT/benchmarks/.hutter_slice_screens.jsonl"):
    r=json.loads(line)
    if r.get("name","").startswith("L1abl_"):
        rows.append(r)
# last three of each name
by={}
for r in rows:
    by[r["name"]]=r
sc=$sc_z
print("=== L1 cmix ablation summary ===")
for k in ("L1abl_raw_stock","L1abl_body_stock","L1abl_body_matched"):
    r=by.get(k)
    if not r:
        print(k, "MISSING"); continue
    total = r["bytes"] + (sc if k!="L1abl_raw_stock" else 0)
    print(f"{k}: cmix={r['bytes']} +sc={0 if k=='L1abl_raw_stock' else sc} total={total} rt={r['rt']}")
PY
