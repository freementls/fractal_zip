# Writing-system entropy probe — findings

Tested the hypothesis: *"reduce a high-entropy alphabetic writing system to a
lower-entropy symbology (abjad/cuneiform) + prediction to get lower bpc."*

Scripts (reproducible, roundtrip-verified, small slices of `enwik8` prose):
- `benchmarks/research_writing_entropy.py`  — order-k KT adaptive coder
- `benchmarks/research_writing_entropy2.py` — Witten-Bell **backoff** coder + faithful abjad
- `benchmarks/research_writing_realcomp.py` — real compressors (bzip2/zstd/xz/PPMd)

## Result: symbology transforms cannot win against a competent predictor

| compressor | baseline bpc | case_split Δ | abjad_split Δ |
|---|---:|---:|---:|
| bzip2 | 2.394 | +0.209 | +1.476 |
| zstd  | 2.588 | +0.203 | +1.113 |
| xz    | 2.519 | +0.191 | +1.166 |
| PPMd  | 2.226 | +0.171 | +1.237 |

Backoff (Witten-Bell) order sweep, 200 KB prose:

| order | baseline | case_split | abjad_naive | abjad_faithful |
|---|---:|---:|---:|---:|
| k=2 | 3.172 | +0.171 | +0.729 | +0.626 |
| k=4 | 2.662 | +0.146 | +1.157 | +0.954 |
| k=6 | 2.809 | +0.140 | +1.269 | +0.971 |
| k=8 | 2.963 | +0.143 | +1.269 | +0.922 |

A weak order-k **KT** model showed a *fake* case-split win (−0.07 @k=4); it
vanished/reversed under the backoff model and all real compressors. Classic
"helps a weak coder, hurts a strong one."

## Why (engineered from premises)

1. **Bijection invariance.** Re-symbolization (cuneiform primitives, alphabet
   remap) preserves entropy exactly. Once you arithmetic-code with a given
   predictor, the symbol shapes are irrelevant. No lossless win is possible from
   symbology alone.
2. **The abjad's hidden tax is the position mask.** Faithful abjad cost split at
   k=8: skeleton **2.51** + **mask 0.80** + vowels **0.57**. Predicting vowel
   *identities* from real context is cheap (~0.57). Encoding *where the vowels
   go* costs **0.80 bpc** — and sinks the scheme. Human abjads avoid this by (a)
   implying positions via phonotactics and (b) tolerating lossy reconstruction.
   Lossless compression can do neither.
3. **Chain rule wall.** `H(text) = H(skeleton) + H(rest | skeleton)`. Splitting
   only re-buckets the same bits; a strong predictor on the original already
   conditions each vowel on its preceding letters, so separation can only lose
   (breaks LZ matches / BWT contexts / dilutes stats).

## Where the wins actually are (NNCP's real edge)

NNCP/cmix beat PPMd/PAQ **not via symbology but via a better predictor** —
larger capacity capturing long-range, morphological, and semantic regularity.
100% of the achievable headroom is in `P(next | context)` quality.

The writing-history framing still maps to real levers — but at the right layer:

- **Logographic → dictionary**: replace frequent units with codes. (Already in
  this project: external phda9 dict.) Lever = vocabulary, not letter shapes.
- **Abjad → "model the predictable channel away"**: the lossless analog of
  dropping vowels is letting the predictor make predictable symbols ~free. That
  is just *better prediction*, with **no position mask**.
- **Document-level skeleton (the promising on-theme idea)**: enwik8 is markup +
  templates with highly regular structure. Extract the repeated *document*
  skeleton (wiki/XML grammar) and code only the variable slots. Boundaries are
  implied by the grammar → **no per-char mask tax**. This is an abjad at the
  document layer, and it is structurally sound.
- **Long-range repeats into model range**: reorder so distant duplicates fall
  inside the match window. (Proven win in this repo: `sort_title`.)

## Document-skeleton prototype (BPE grammar) — built & measured

`benchmarks/research_doc_skeleton.py`: learn the document's own repeated
primitives by BPE pair-merging (markup/tags/templates become single symbols),
position-implicit (no mask), roundtrip-verified, merge-table cost counted.
500 KB raw enwik8, K=60 merges:

| coder | baseline bpc | BPE bpc | Δ | has match model? |
|---|---:|---:|---:|---|
| **PPMd** (context only) | 2.103 | 2.068 | **−0.035** | no |
| WB backoff (context only) | 2.654 | 2.616 | −0.038 | no |
| **xz / LZMA** (64 MB window) | 2.375 | 2.375 | **−0.0003** | yes |

WB win plateaus at ~**−0.04 bpc** by K≈120 then flat.

### The decisive diagnostic

BPE helps **only coders without a long-match model** (PPMd, backoff). LZMA's
match finder already captures the exact same globally-repeated structure, so BPE
adds nothing. **The "document-skeleton" redundancy is long-match redundancy.**

### Why it will NOT transfer to this project's stack

The best arm here is `…single_stream_lstm…`, i.e. **phda9** — a PAQ-derivative
that has **both a match model and an LSTM neural predictor**
(`tools/phda9/phda9` vs `tools/phda9/phda9_no_LSTM`). Like LZMA, its match model
already captures structural repetition. This is independently confirmed by the
project's own modeling sweep, where phrase/structure-substitution arms
(`corpus_phrases`, `cfabb_*`) **lost** on the phda9 stack. BPE document-skeleton
would lose for the same reason.

## Net strategic conclusion

- **Symbology / re-symbolization (abjad, cuneiform):** dead — bijection-invariant
  + the position-mask tax (~0.8 bpc).
- **Structure / dictionary / document-skeleton:** real only against weak
  (match-less) coders; the project's match+LSTM stack already captures it.
- **All remaining headroom (the NNCP gap, ~2.1→~1.1 bpc) is PREDICTOR quality.**
  The stack already pulls the two real levers (match model + LSTM). Beating
  518,508 → 460,096 means a **stronger predictor / better neural+model config**,
  not a new symbology. That is the only branch with headroom.

## Predictor profiling — where the real headroom is (measured)

Tools: `tools/phda9/phda9` (PAQ+LSTM), `tools/phda9/phda9_no_LSTM`,
`tools/nncp` (transformer). GPU present = GeForce MX450 (Turing, ~2 GB) →
**NNCP CUDA unsupported** (needs Ampere+/6.6 GB); NNCP runs CPU-only here.

**LSTM contribution (1 MB raw prefix):** LSTM 178,894 B (1.431 bpc, 167 s) vs
no-LSTM 179,233 B (1.434 bpc, 83 s) → LSTM saves only **0.19%** at 1 MB. The
LSTM trains *online*; its edge grows with data seen, so small slices understate
it.

**phda9 warm-up curve (homogeneous mid-file article text):**

| contiguous size | bpc |
|---|---:|
| 200 KB | 1.658 |
| 1 MB | 1.502 |
| 3 MB | 1.436 (still decreasing) |

phda9 gains **−0.22 bpc** from 200 KB→3 MB purely from online amortization.

**NNCP transformer, CPU, cold, 200 KB:** 88,116 B = **3.525 bpc** (BPS fell
3.91→3.53 as it learned). Pure-neural predictors are useless cold; NNCP's
famous **1.193 bpc** on enwik8 is amortized over the full 100 MB.

### The decisive arithmetic (wire460)

384-page slice: plain = 2,970,852 B.
- current best wire 518,508 B → **1.396 bpc**
- target 460,096 B → **1.239 bpc**
- NNCP full-enwik8 rate → **1.193 bpc**

**The target ≈ NNCP-grade compression.** The current stack already compresses
the whole slice in ONE phda9 call with sort_title clustering + external dict
(1.396 bpc) — i.e., it is already near phda9's amortized asymptote for this data.

### Conclusion: the 518,508→460,096 gap is a PREDICTOR-CAPABILITY gap

- It is **not** a symbology gap (proven dead above) and **not** a
  member-fragmentation gap (slice already encoded as one contiguous member).
- Closing it to ~1.24 bpc requires a **neural predictor that arrives warm** —
  e.g. NNCP with pretrained coefficients (`--load_coefs`, pretrain on enwik8) so
  it is not cold on the slice. That needs an **Ampere+ GPU** (this MX450 cannot
  run NNCP CUDA). On CPU, NNCP is far too slow/cold to help.
- With phda9 fixed as the predictor on this hardware, the slice is already near
  its asymptote; the remaining deterministic levers (symbology, structure, dict)
  are exhausted.

### Actionable recommendations (in priority order)

1. **Hardware/predictor:** to actually reach ~1.24 bpc, run an NNCP-class
   transformer with pretrained coefficients on a supported GPU. This is the only
   lever with real headroom; it is what makes the target achievable.
2. **Maximize phda9 amortization within reach:** keep one contiguous member +
   sort_title; invest dict budget in priming the predictor for the slice's exact
   content (the existing dict lever), accepting diminishing returns.
3. **Stop spending effort on symbology/structure transforms** — measured dead
   against a match+LSTM stack.

## NNCP practicality + warm-start on this machine (overnight battery)

Hardware: 16-thread CPU, GeForce MX450 (Turing, ~2 GB → NNCP CUDA unsupported).
Patched `tools/nncp-2024-06-05/nncp_warm` to add `--save_coefs` and re-enable
`--load_coefs` (were `#if 0`'d out) via a `param_list_ptr` in the common state.

### Small-NNCP CPU sweep, 3 MB cold (vs phda9 1.436 bpc @3MB)

| profile | bpc | time(3MB) | throughput | RAM | full-enwik8 encode (extrap.) |
|---|---:|---:|---:|---:|---:|
| `lstm` | 1.954 | 731 s | 4.1 kS/s | 128 MB | ~6.8 h |
| `lstm_fast` | 2.251 | 1477 s | 2.0 kS/s | 881 MB | ~13.7 h |
| `default` (trf) | 2.356 | 584 s | 5.1 kS/s | 71 MB | ~5.4 h |

Every CPU-runnable NNCP model is **slower and worse** than phda9 cold.

### phda9 amortization keeps improving with scale

| contiguous size | phda9 bpc |
|---|---:|
| 200 KB | 1.658 |
| 1 MB | 1.502 |
| 3 MB | 1.436 |
| 10 MB | **1.344** (still dropping) |

### Warm-start experiment (pretrain on disjoint 20 MB, test on held-out 3 MB)

phda9 reference on the same test3m: **1.416 bpc**.

| model | cold bpc | warm bpc | warm-start Δ | coefs size |
|---|---:|---:|---:|---:|
| NNCP `default` (trf) | 2.342 | **1.735** | **−0.607** | 8.98 MB |
| NNCP `lstm` | 1.939 | **1.573** | **−0.366** | 27.06 MB |

### Verdicts

1. **Warm-start is real and large** (−0.37 to −0.61 bpc). This *confirms the core
   thesis*: predictor warmth/amortization is the dominant lever — not symbology.
2. **But even warm, small NNCP loses to phda9** (1.573 warm-lstm vs 1.416 phda9)
   and costs ~6–14 h/pass on CPU. **NNCP is not practical on this machine.**
3. **The free lunch in warm-start is an illusion for lossless unless in-stream.**
   The coefs are 9–27 MB; shipping them per slice is catastrophic. NNCP/phda9 get
   warmth "for free" only because the model retrains identically during *decode*
   of one contiguous stream. The wire460 per-slice (384-page ≈ 3 MB) framing caps
   that in-stream amortization.
4. **phda9 is the right predictor here and is near its ceiling on the slice.** It
   keeps improving with span (1.344 bpc @10 MB) but the metric fixes the span.

### Bottom line for the 518,508 → 460,096 (1.396 → 1.239 bpc) target

The target is NNCP-grade. Reaching it requires the **big transformer**
(20-layer/d_model=1024, ~1.19 bpc) run with **in-stream amortization** — which
needs an **Ampere+ GPU** (this MX450 cannot). On the current CPU+phda9 stack the
slice is near the achievable ceiling; symbology/structure levers are dead. The
practical recommendation is unchanged: **the only path to the target is a
GPU-class neural predictor**; absent that, expect to stay near ~1.39 bpc.

## Overnight NNCP practicality + warm-start experiment (measured)

Patched `tools/nncp-2024-06-05/nncp.c` to re-enable `--load_coefs` (it was
`#if 0`'d out) and add `--save_coefs` (generic `param_list_ptr` in the common
model state) → `tools/nncp-2024-06-05/nncp_warm`. Original `./nncp` untouched.

**CPU sweep, 3 MB cold** (vs phda9 1.436 bpc @3MB):

| profile | bpc | time | throughput | full-enwik8 encode (extrap) |
|---|---:|---:|---:|---:|
| nncp `lstm` | 1.954 | 731 s | 4.1 kS/s | ~6.8 h |
| nncp `lstm_fast` | 2.251 | 1477 s | 2.0 kS/s | ~13.7 h |
| nncp `default`(trf) | 2.356 | 584 s | 5.1 kS/s | ~5.4 h |
| **phda9** | **1.436** | 476 s | 6.3 kS/s | ~3 h |

**phda9 amortization curve** (homogeneous mid-file): 200 KB 1.658 → 1 MB 1.502
→ 3 MB 1.436 → **10 MB 1.344** (still falling). Held-out 3 MB ref = 1.416 bpc.
Extrapolates toward phda9's known ~1.2–1.25 bpc on full enwik8 — i.e. the
wire460 target (1.239 bpc) ≈ phda9's *full-file amortized* rate.

**Warm-start (pretrain on disjoint 20 MB, encode held-out 3 MB):**

| model | cold bpc | warm bpc | warm-start Δ | coefs size |
|---|---:|---:|---:|---:|
| nncp `default`(trf) | 2.342 | **1.735** | **−0.607** | 8.98 MB |
| nncp `lstm` | 1.939 | **1.573** | **−0.366** | 27.1 MB |
| phda9 (ref, no warm) | — | 1.416 | — | — |

### Verdict

1. **Amortization is worth ~0.4–0.6 bpc** — warm-start is a large, real effect,
   confirming the headroom is predictor *warmth/quality*, exactly as predicted.
2. **But on this machine NNCP is not a win:** even *warm*, the best small model
   (1.573–1.735 bpc) still loses to phda9 (1.416), and phda9 keeps improving with
   scale (1.344 @10 MB). Small NNCP is also slower (5–14 h/pass vs ~3 h).
3. **Coef-shipping is not the legit path:** 9–27 MB of coefficients would count
   against the compressed size (Hutter counts the decompressor). NNCP's real
   1.19 bpc comes from *online* training over the full 100 MB (deterministic,
   both sides retrain identically — no coefs shipped), i.e. **amortization
   through full-corpus scale, not pretrained weights.**
4. **The good NNCP transformer (1.19 bpc) is GPU-blocked here** (needs Ampere+
   /6.6 GB; this box has a Turing MX450 ~2 GB).

### Practical recommendation (this hardware)

- Keep **phda9** as the predictor; it is the best CPU option and already near its
  amortized asymptote on the slice (sort_title + dict).
- The legit way to capture the warm-start gain is **full-corpus online training**
  (process more of enwik8 together so the predictor amortizes), not coef-shipping.
- To actually reach ~1.24 bpc faster than phda9, you need an **Ampere+ GPU** to
  run the big NNCP/transformer. That is the single highest-headroom move and is a
  hardware decision, not an algorithmic one.

## Can phda9 go below 1.2 bpc? — scale experiment (decisive)

Baseline: **raw phda9 on full enwik8 = 15,010,414 B = 1.2008 bpc** (no sort, no
dict; `benchmarks/.enwik8_world_record.json`). Goal: beat it.

Hardware wall discovered: **7 GB RAM total, ~9 GB swap already in use; one phda9
instance = 3.8–4.3 GB.** Only ONE phda9 can run at a time; a full-enwik8 run
thrashes swap. The 16 cores are unusable for parallel phda9 (RAM-bound).

A/B on identical 19 MB content (`bench_phda9_scale_push.php --pages=2560`,
honest perm + dict accounting):

| config | phda9 stream | total bpc | vs raw |
|---|---:|---:|---:|
| raw order | 3,106,260 | 1.3024 | — |
| `sort_title` (+perm) | 3,106,099 | 1.3023 | −0.0001 (noise) |
| `sort_title` + dict | 3,255,415 | 1.4018 | **+0.099** |

### Both slice-level levers FAIL at scale

- **`sort_title`: neutral at scale.** It helped at 384p (data-starved) but at
  19 MB phda9's match model already captures cross-article redundancy, so
  clustering adds nothing.
- **External dict: actively HARMFUL at scale.** Dict help went from **+7%** at
  96p to **−4.8%** at 19 MB (the dict made phda9's own stream 149 KB *bigger*),
  plus its amortized overhead. At scale phda9 learns the vocabulary better than a
  384p-optimized dict supplies; the dict just disrupts its model. Extrapolates to
  even more harm at 100 MB.

### Verdict: phda9 cannot beat 1.2008 bpc here via preprocessing

The project's entire slice-level toolkit (sort_title + external dict) is a
**384p-scale artifact that reverses at scale**. A full-enwik8 sort+dict run would
come out *worse* than raw 1.2008 — so it was not launched (it would waste ~7 h of
swap-thrashing to confirm a regression). phda9 raw 1.2008 is its ceiling on this
hardware.

Going below 1.2 bpc therefore requires a **better predictor**, not preprocessing:
the big NNCP/transformer (~1.19 bpc) — which needs an **Ampere+ GPU** this box
lacks (Turing MX450, 2 GB) — or internal modeling changes to phda9 itself. Both
are out of reach on the current machine today.

## Cardinal rule extracted



## Abjad numerals as decimal codebook (2026-07-10 probe)

Script: `benchmarks/research_abjad_numeral_probe.py`  
Raw JSON: `benchmarks/.abjad_numeral_probe.json`

Distinct from the **vowel-split** “abjad” above. This probe asks whether classical
letter↔value maps (Hebrew/Arabic/Greek Milesian) are a **decimal / ASCII-like
symbol encoding**, and what that implies for compression.

### Organization (shapes of the *code*, not the ink)

| Check | Result |
|---|---|
| Milesian formula `value = digit × 10^place` | **Exact match** on all 22 Hebrew letters |
| Place bands | 9 units + 9 tens + 4 hundreds (incomplete 100..900) |
| Zero glyph / positional place | **None** — additive bag of digit×place symbols |
| Exact powers-of-two codebook | **False** |
| Pearson(value, 2^i) | 0.95 (both rise; not evidence of binary) |
| ASCII analogy | ASCII digits = one glyph plane + **position** carries place; abjad = **place folded into glyph identity** (three decade alphabets) |

**Verdict:** Abjad numerals are a **decimal digit×place dual-use alphabet** — the
same job ASCII `0–9` + positional notation do, engineered for a world without a
zero glyph. Not a forgotten binary compressor. “Poor transmission” → gematria
mysticism / isopsephy checksums, not a recoverable bit codec.

### Glyph shapes (DroidSansHebrew ink density)

| Feature | Pearson vs value |
|---|---:|
| ink pixels | 0.39 |
| ink vs log10(value) | 0.38 |
| band mean ink | units 627 → tens 671 → hundreds 802 |

Weak positive only. Shapes behave like **phoneme glyphs reused as a codebook**,
not tallies whose ink encodes magnitude (unlike Roman I/V/X stroke logic).

### enwik8 numeric microstructure (1 MB mid-file)

- Digit bytes ≈ **2.6%**; **9358** runs; **1929** year-like tokens.
- H(next|digit) slightly below H(next|letter) (−0.04 bit) → digit-run state is a
  real but small context.
- WB order-3 **digit place-plane** side-channel: **−0.048 bpc** (`KEEP` on weak
  model). Candidate for fx2 mixer context; must re-check under cmix (strong
  predictors often already condition on digit runs).

### Follow-up: causal digit contexts (same 120 KB WB order-3)

| Arm | Δbpc | Notes |
|---|---:|---|
| noncausal place-from-right | −0.0475 | needs full run length (lookahead) |
| causal numlen-from-left | −0.0439 | = fx2-style `numlen0` |
| causal in-digit flag | −0.0479 | binary “inside number” |
| causal `number0 % 32` | **−0.0762** | = fx2-style running decimal value |

**Noncausal was not a unique cheat** — causal forms help about as much. But
**fx2-cmix already ships this**: `number0` / `number1` / `numlen0` / `numlen1` /
`numbers` bitfield in `fxcmv1.cpp`, wired into several `cmC*` contexts. So the
WB “KEEP” is mostly **rediscovering an existing fx2 feature**, not a new S2
lever. Further digit work only pays if it adds something beyond running value +
length (e.g. year/ISBN detectors, or end-of-number place histogram) and beats
fx2 on a real compress.

**Year-flag probe** (`benchmarks/.abjad_year_context_probe.json`): year alone
−0.01 bpc, but **on top of `number0` it hurts** (+0.006) → `reject_year_incremental`.
Numeric side-channels beyond fx2’s existing number state look barren on this
slice.

### Gematria sum as mixer side-channel (WB order-4, 120 KB)

| Arm | Δbpc |
|---|---:|
| gematria mod 32 | −0.312 |
| random mod 32 | **+0.938** (harms) |
| word length | −0.356 |
| first letter | **−0.681** |

Gematria beats random (real word-level signal) but is **dominated by first_letter
and wordlen** → gate `reject_dominated`. It is a weak hash of word identity.
**Prefer dict-index / stemmer / first-letter contexts** (fx2 already has the
strong form). Do not ship gematria sums.

### Actionable compression mapping

1. **Keep treating classical abjad as decimal digit×place dual-use** — informs
   how we think about numeric islands, not a text remapping transform.
2. **Do not** revive vowel-split or letter↔value remaps (bijection / mask tax).
3. **Digit-run / place-plane mixer context** — small WB win; next validate inside
   fx2/phda9 only if cheap.
4. **Word → small int side-channels** — yes, but use **dict id / stemmer /
   first-letter**, not gematria additive sums.
5. **Isopsephy** stays checksum/stego territory — not an archive-size lever.


### Ref/ISBN detectors (incremental)

`benchmarks/.abjad_ref_context_probe.json` — ISBN/DOI/ref/px flags on top of
`number0` all **hurt** (~+0.0015 bpc). `reject_all_incremental`.

### Dict prune screen (offline, pending compress)

`benchmarks/.abjad_dict_prune_screen.json` — `english.dic` 44 515 entries; only
**27.7%** hit the 1 MB mid slice. Pruned dicts ready under
`/tmp/ladder_1m_dict_{slice_hits,slice_ge2,slice_ge3}.dic`. Compress when cmix
RAM is free; KEEP iff archive &lt; 193 498 + RT_OK.
