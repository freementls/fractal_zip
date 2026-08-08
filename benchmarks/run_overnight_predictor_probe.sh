#!/usr/bin/env bash
# Overnight predictor-practicality battery (autonomous, no user input).
#   1. wait for the in-flight NNCP CPU sweep to finish (avoid CPU contention)
#   2. phda9 large-scale amortization point (10 MB) -> bound slice-domain asymptote
#   3. NNCP warm-start experiment with patched nncp_warm:
#        pretrain small models on a disjoint enwik8 chunk (--save_coefs),
#        then cold vs warm encode of a held-out slice (--load_coefs).
#   Goal: definitive go/no-go on NNCP practicality on THIS machine + whether
#   warm-start lets a CPU-runnable small model approach phda9 (1.436 bpc @3MB).
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
NN="$REPO/tools/nncp-2024-06-05"
PHDA9="$REPO/tools/phda9/phda9"
ENWIK="$REPO/enwik8"
OUT="$REPO/benchmarks/.predictor_overnight_results.txt"
PP=/tmp/predprobe
mkdir -p "$PP"
NT=$(nproc)

log(){ echo "[$(date -Is)] $*" | tee -a "$OUT"; }
bpc(){ awk -v b="$1" -v n="$2" 'BEGIN{printf "%.4f", b*8/n}'; }

echo "===== overnight predictor probe $(date -Is) =====" | tee -a "$OUT"

# ---- 1. wait for in-flight sweep (nncp encode_only) to finish ----
log "waiting for any running ./nncp sweep to finish..."
while pgrep -f 'nncp .*encode_only.*mid3m' >/dev/null 2>&1; do sleep 30; done
log "sweep clear; starting battery (threads=$NT)"

# ---- build disjoint slices ----
# pretrain corpus: [0,20MB); held-out test: [50MB,53MB); extra phda9 point [10MB,20MB)
dd if="$ENWIK" of="$PP/train20m.txt" bs=1M count=20 status=none
dd if="$ENWIK" of="$PP/test3m.txt"  bs=1 skip=50000000 count=3000000 status=none
dd if="$ENWIK" of="$PP/mid10m.txt"  bs=1 skip=10000000 count=10000000 status=none
log "slices: train20m=$(stat -c%s "$PP/train20m.txt") test3m=$(stat -c%s "$PP/test3m.txt") mid10m=$(stat -c%s "$PP/mid10m.txt")"

# ---- 2. phda9 amortization at 10 MB ----
log "[phda9] compressing mid10m (10MB)..."
t0=$(date +%s)
"$PHDA9" C "$PP/mid10m.txt" "$PP/mid10m.paq" >/dev/null 2>&1
t1=$(date +%s)
sz=$(stat -c%s "$PP/mid10m.paq")
log "[phda9] mid10m: $sz B  bpc=$(bpc $sz 10000000)  time=$((t1-t0))s"
# phda9 reference on the held-out test slice (for warm-start comparison)
t0=$(date +%s); "$PHDA9" C "$PP/test3m.txt" "$PP/test3m.paq" >/dev/null 2>&1; t1=$(date +%s)
szp=$(stat -c%s "$PP/test3m.paq")
log "[phda9] test3m REF: $szp B  bpc=$(bpc $szp 3000000)  time=$((t1-t0))s"

# ---- 3. NNCP warm-start experiment ----
run_nncp(){ # profile extra_args outfile  -> echoes csize
  local prof="$1"; shift; local extra="$1"; shift; local of="$1"; shift; local inf="$1"
  ( cd "$NN" && LD_LIBRARY_PATH=. ./nncp_warm -T "$NT" --encode_only -p "$prof" $extra c "$inf" "$of" ) >/dev/null 2>&1
  stat -c%s "$of" 2>/dev/null || echo 0
}

for prof in default lstm; do
  log "[nncp:$prof] PRETRAIN on train20m -> coefs (this is the slow step)..."
  t0=$(date +%s)
  ( cd "$NN" && LD_LIBRARY_PATH=. ./nncp_warm -T "$NT" --encode_only -p "$prof" \
      --save_coefs "$PP/coefs_$prof.bin" c "$PP/train20m.txt" "$PP/train20m.$prof.nncp" ) >"$PP/pretrain_$prof.log" 2>&1
  t1=$(date +%s)
  trsz=$(stat -c%s "$PP/train20m.$prof.nncp" 2>/dev/null || echo 0)
  cfsz=$(stat -c%s "$PP/coefs_$prof.bin" 2>/dev/null || echo 0)
  log "[nncp:$prof] pretrain done: train_csize=$trsz bpc=$(bpc ${trsz:-0} 20000000) coefs=$cfsz B time=$((t1-t0))s"
  if [ "${cfsz:-0}" -le 0 ]; then log "[nncp:$prof] WARN no coefs file; skipping warm test"; continue; fi

  log "[nncp:$prof] COLD encode test3m..."
  t0=$(date +%s); cold=$(run_nncp "$prof" "" "$PP/test3m.$prof.cold.nncp" "$PP/test3m.txt"); t1=$(date +%s)
  log "[nncp:$prof] cold test3m: $cold B bpc=$(bpc ${cold:-0} 3000000) time=$((t1-t0))s"

  log "[nncp:$prof] WARM encode test3m (--load_coefs)..."
  t0=$(date +%s); warm=$(run_nncp "$prof" "--load_coefs $PP/coefs_$prof.bin" "$PP/test3m.$prof.warm.nncp" "$PP/test3m.txt"); t1=$(date +%s)
  log "[nncp:$prof] warm test3m: $warm B bpc=$(bpc ${warm:-0} 3000000) time=$((t1-t0))s"
  awk -v c="$cold" -v w="$warm" 'BEGIN{ if(c>0&&w>0) printf "[nncp:'"$prof"'] warm-start delta = %+d B (%.4f bpc)\n", w-c, (w-c)*8/3000000 }' | tee -a "$OUT"
done

log "===== overnight predictor probe DONE ====="
echo "OVERNIGHT_DONE" | tee -a "$OUT"
