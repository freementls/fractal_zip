# Hutter Prize path — fx2-cmix baseline & winner diff

Generated: 2026-07-10. Machine: 7 GiB RAM, 16 threads, MX450 (unused; prize forbids GPU).

## Official target

| | |
|---|---|
| Corpus | enwik9 (1 GB) |
| Current L | **110,793,128** (fx2-cmix, Sep 2024) |
| Prize-eligible S | **&lt; 109,685,197** (≥1%) |
| Budget | single-core CPU, ≤10 GiB RAM, ≲50 h/phase (GB5-scaled) |

## Local baseline (this machine)

### Build

- Tree: `tools/hutter/fx2-cmix` (clone of https://github.com/kaitz/fx2-cmix)
- Also cloned: `fx-cmix`, `starlit`, `cmix` (byronknoll)
- Build: `clang++` (system 22; makefile adapted from clang++-17), `SEED=923`, `UPDATE_LIMIT=3000`
- Fix required: `#include <stdint.h>` in `src/models/fxcmv1.cpp` for `uintptr_t`
- **Critical local fix (2026-07-11):** `FXCM_PPMD_DISK` — stable `ppm.temp` mapping + `madvise(MADV_COLD)` (no remap segfault). **`make disk`** → `run/cmix_disk`. 1 MiB smoke: **198,744 B RT_OK**, peak RSS ~4.3 GiB compress / ~3.4 GiB decompress. Lean **`make submission`** → `cmix_submission` (bitstream-identical for `-e`/`-d`, strips `-c` lab modes for S1).
- **Legacy note:** earlier `mmap_to_disk=false` lazy-anonymous mmap path remains in `cmix_mmap` for lab ladders needing `-c`.
- Targets: `make CFLAGS_DEFINES='-DSEED=923 -DUPDATE_LIMIT=3000' cmix` (confirm); `make … iterate` (screens); `make … lto` (**ThinLTO + link `-O3` + lld** — ~1.17× vs mmap-only on 256 KB; was slower before because link lacked `-O3`); `make … ram7` (**≤7 GiB host**: CM_SCALE=2, PPMD=2048 MiB, BUF_BITS=23, IND3_BITS=24, LSTM=128, MATCH_MAX=1e6 — not prize-identical S2).

### Measured compress (`cmix -n`, no wiki preprocess)

| Input | Out bytes | bpc | Wall | RSS |
|---|---:|---:|---:|---:|
| 26 B tiny | 36 | — | 0.6 s | 1.6 GiB |
| **1 MiB enwik8** | **206,972** | **1.579** | ~18 min (swap) | **3.7 GiB** |

Extrapolations (rough; swap-dominated on this box):

| Scale | Est. wall | Est. RSS | Notes |
|---|---|---|---|
| enwik8 100 MB | ~30 h+ | ~4–6 GiB | feasible if left overnight; thrashy |
| enwik9 1 GB (full fx2) | ~65 h published @ GB5=1026 | ~9.5 GiB | prize-shaped; needs ≥10 GiB |
| enwik9 1 GB (`ram7`) | longer wall (smaller model, more thrash risk) | target ~4–5.5 GiB | **local S2 loops on this 7 GiB box**; not official S |

### Published fx2-cmix record (source of truth for S)

| Metric | Bytes |
|---|---:|
| S1 compressor | 441,463 |
| S2 archive | 110,351,665 |
| **S** | **110,793,128** |
| Local stripped `cmix` (no UPX/dict embed) | 308,408 |

Local S1 is smaller because PGO+UPX+embedded dict/order were not fully constructed (dict compress still validating after PPM fix).

## Winner lineage diff — unused / next tricks

Sources compared: `starlit` → `fx-cmix` → `fx2-cmix` READMEs + trees.

### Already in fx2-cmix (do not re-invent)

- STARLIT-style article reorder (embedding → t-SNE → k-means → manual; order file shipped)
- Single-pass Wikipedia / phda9-style transform
- Online reverse dictionary + english.dic
- NLP stemmer (paq8pxd) with Article/Conjunction/Adposition/ConjunctiveAdverb types
- Four word streams (undecoded / sentence / paragraph / filtered)
- Split ContextMaps (32/64/128-byte hash tables by context size)
- Sparse match model (UTF-8 escapes)
- Mixer weight-update skip when error below threshold (speed)
- Removed weight regularizer; removed 7 INP + 6 match + 3 mixers (speed/size)
- LSTM expected-byte mixer context; dictionary index mixer context
- Math/pre/nowiki/text tag detection to skip word contexts (speed)

### Highest-ROI remaining levers (for a ≥1% beat of L)

1. **Better article order** — fx2 already uses voyage-large-2-instruct embeddings; try newer embeddings / better TSP/k-means / more manual end-of-file clustering. Order file is *not* in the time-critical path (only the order bytes count in S).
2. **Dictionary quality** — grow/prune `english.dic` for enwik9 token distribution; online reverse-dict already present.
3. **Stemmer / word-type coverage** — more POS-like classes if they unlock context skips (fx2’s big win was new word types). Prefer these over gematria-style additive letter sums (`reject_dominated` in abjad-numeral probe).
3b. **Digit-run / place-plane mixer context** — WB −0.05…−0.08 bpc, but **fx2 already has `number0`/`numlen0`**. Treat as covered unless a *new* numeric detector (years/ISBN/…) beats baseline on 1 MB. See `benchmarks/.abjad_digit_causal_probe.json`.
4. **Mixer / APM context sizing** — careful growth where fx2 left headroom; watch RAM/time.
5. **S1 shrink** — UPX, LTO/PGO (official build), drop dead code paths; every byte of binary counts in S.
6. **Speed engineering** — must stay under ~50 h @ prize GB5; any byte win that blows the budget is invalid (cmix/nncp lesson).

### Explicit non-paths (from fractal_zip lab)

- External dict / sort_title / abjad / BPE skeleton / wire460 modeling — dead or harmful at scale against match+LSTM stacks.
- GPU NNCP at runtime — **illegal for prize scoring**.
- Shipping pretrained coefficients — counts in S.

## One-change improve loop (executed)

**Change:** UPX `-9` on the compressor core before embedding dict+order (same as official build’s size step; validates the KEEP rule locally).

| Artifact | Bytes |
|---|---:|
| `cmix_orig` (stripped, no UPX) | 308,408 |
| `cmix_upx` | 142,404 |
| S1 baseline (core+dict+order+hdr) | 609,644 |
| S1 improved (UPX core+dict+order+hdr) | **443,640** |
| ΔS1 | **−166,004** |
| Published S1 | 441,463 |

**enwik8 1 MiB compress check:** baseline and UPX binaries both produce **206,972** bytes (`IDENTICAL_OUTPUT`). RSS ~3.7 GiB, wall ~15–18 min (swap).

**KEEP decision:** **KEEP** for local builds (ΔS1 &lt; 0, output unchanged, time unchanged). Does **not** beat published S1 (still +2,177 B vs official PGO+UPX binary) — so this alone is not a prize win; next loops must shrink **S2** (model/order/dict), not only S1.

**Current campaign (2026-07-12):** one heavy cmix job at a time; `benchmarks/hutter_memory_guard.sh` before runs; `rm ppm.temp` after. CAVER-15 **reject** (1 MiB −2 B; 10 MiB incomplete). S1 embed (UPX+submission, no PGO): **443,572 B** (+2,109 vs published). 100 MiB `cmix_disk` validation **running**. enwik9 `-e` **blocked** until ≥1.2% S2 gain at 100 MiB (see `benchmarks/.hutter_final_gate.json`).

## enwik9 verify gate

**Full prize-profile** `-e enwik9` still needs ≥~9.5 GiB peak (≤10 GiB prize cap). On **this 7 GiB host**, use **`make ram7`** / `run/cmix_ram7` for local compress+RT loops (S2 not comparable to L).

```bash
# Local (7 GiB): ram7 profile
cd tools/hutter/fx2-cmix
make CFLAGS_DEFINES='-DSEED=923 -DUPDATE_LIMIT=3000' -j1 ram7
cp -a cmix run/cmix_ram7
# Place enwik9 (see tools/hutter/data/GET_ENWIK9.md)
./benchmarks/run_hutter_enwik9_verify.sh /path/to/enwik9   # defaults to cmix_ram7

# Prize-shaped (needs ≥10 GiB): full binary
HUTTER_PROFILE=full HUTTER_MIN_GIB=10 CMIX=./run/cmix \
  ./benchmarks/run_hutter_enwik9_verify.sh /path/to/enwik9
```

Target (official): **S &lt; 109,685,197**. Always compress and decompress with the **same** binary (LTO ≠ non-LTO bitstream).

## Hardware gate (this box)

| Requirement | This machine | Verdict |
|---|---|---|
| ≤10 GiB RAM (prize) | 7 GiB physical | **OK with `cmix_disk`** (PPM on disk; RSS ~4–5 GiB). Avoid parallel cmix jobs — OOM kills at ~37% on 10 MiB when memory contested. |
| ~50 h single-core | 16 threads available but scoring is 1-core | OK for timing math |
| Disk ≤100 GiB temp | ~23 GiB free | Tight for enwik9 (~21 GiB published) |

**IND3 note:** `FXCM_IND3_BITS` must match index masks in `fxcmv1.cpp` and `context-manager.cpp` (`IND3_MASK` / `FXCM_IND3_MASK`) — hardcoded `0x2000000` caused ram7 SIGSEGV.
