# fz dynamic-context experiments (provenance-conditioned coding)

Research track: give fz a per-symbol adaptive modeling layer that uses what fz
uniquely knows — the **provenance** of every match (tape region, recursion
depth, scale) — instead of handing interleaved marker text to generic outer
codecs. Separate from the Hutter submission; nothing here touches fx2-cmix.

**Ground truth today:** `fractally_process_string()` / `recursive_substring_replace()`
(`fractal_zip.php` ~21470/21617) expand a textual equivalence stream against the
growing `fractal_string` tape; entropy is 100% outsourced to `adaptive_compress()`
(~28017) tournament. No arithmetic coding, no online adaptation in fz.

**Rules for all experiments**
- KEEP = smaller total `.fz` + byte-exact unzip round trip.
- Ladder: micro inner fixtures → stratified sample (`benchmarks/sample_large_corpus.php`,
  see `.cursor/skills/large-corpus-sampling`) → full corpus.
- Target corpora = where fractal inner is active: `test_files29`, `test_files28`,
  `test_files2/4/52` (dense micro-trees); text corpus for E3/E4.
- Everything behind env flags (default path untouched): `FRACTAL_ZIP_EQ_*`.

---

## E0 — Byte census (headroom measurement, do first)

**Question:** on fractal-active corpora, what fraction of final wire bytes is
(a) equivalence/marker stream, (b) literal residue, (c) dictionaries/framing?
And how much does a CM-class coder (zpaq -m5 as proxy) shave off each plane vs
the current outer?

**Method:** instrumentation script that dumps each plane pre-outer, compresses
each separately with gzip/zstd/brotli/zpaq, reports shares + per-plane headroom.
No product code changes.

**Gate:** if equivalence plane < ~10% of wire bytes, E1/E2 cap out low —
prioritize E3 (literal side) instead.

## E1 — Plane separation (SoA framing)

**Hypothesis:** interleaved marker text hides plane structure from outer codecs;
splitting opcodes / offsets / lengths / literals into separate streams and
coding each through the outer tournament beats the interleaved baseline.
(Same trick as LZMA's separated models, done at framing level — no new coder.)

**Method:** new wire variant `FZEQ1`: plane-split serialize + join on decode.
Cheap, fully reversible, measurable in days.

**Kill:** < 0.3% net win on two target corpora → stop; planes aren't the issue.

## E2 — Provenance-conditioned adaptive range coder

**Hypothesis:** the planes have strong conditional structure a static codec
can't exploit: offsets are recency-skewed against the growing tape; lengths
depend on opcode + offset bucket; opcode sequences are order-1 predictable
per recursion depth.

**Method:** small binary adaptive range coder in PHP (streams are small; speed
acceptable for research). Contexts:
- opcode: previous opcode × recursion depth
- offset: distance-from-tape-end bucket (recency transform), adaptive bit models per bucket
- length: opcode × offset bucket
Replaces the textual/base94 encoding of the equivalence plane entirely.

**Baseline to beat:** E1 winner. **Kill:** loses to zstd-on-planes after context
tuning → provenance signal too weak at these stream sizes.

## E3 — Tape-as-predictor for literals (match-model fusion)

**Hypothesis:** many literals occur where a match ended *just short*; the byte
following the matched span in the tape predicts the next literal. This is
cmix's match model made explicit — fz already has the pointer, cmix has to
hash-search for it.

**Method:** after each copy, code 1 adaptive-probability flag
("literal == predicted byte?", context = match-length bucket); emit the
residue byte only on miss. Requires E2's coder infra.

**Why it matters:** this is the first point where fz stops being pure
transform and the tape becomes a *predictor*.

## E4 — Top-k mixture coding (fully dynamic context — the end state)

**Hypothesis:** dissolve the copy/literal dichotomy: at each output position,
take the top-k match candidates (weighted by length/recency), form a mixture
over next-symbol predictions + order-0 fallback, and arithmetic-code the actual
symbol against it. The context set is then chosen *per character* by the match
search — an fz-native context mixer where "contexts" are tape positions.

**Feasibility note:** decoder symmetry works because the decoder owns the same
decoded-history tape and can rerun the identical candidate search (same cost
profile as CM). Compute-heavy: prototype only on ≤1 MiB inner fixtures,
measured against the E2+E3 stack, before any corpus run.

**Kill:** beats E2+E3 by < 1% at fixture scale → mixture overhead not paying.

---

## Sequencing

1. **E0** now (pure measurement, ~1 script).
2. **E1** if census shows plane mass; else jump to **E3** prerequisites.
3. **E2** once E1/E0 show conditional structure worth a native coder.
4. **E3** after E2's coder exists (shares infra).
5. **E4** last, as a scoped research prototype.

---

## E0 RESULTS (2026-07-11, `benchmarks/fz_eq_plane_census.php` → `.fz_eq_plane_census.json`)

**Fractal-active corpora: equivalence planes are TINY.** test_files29 collapses
1.27 MB raw to equiv=23 B + ref=45 B; test_files2 total equiv=472 B across 9
members. Whole-corpus `.fz` outputs are 0.1–1.7 KB. There is no byte mass in
the equivalence language to model.

**E1 verdict: REJECTED.** Plane-split loses at these sizes — per-stream codec
overhead dominates (zpaq adds ~1 KB header *per stream*; xz ~60 B). Interleaved
wins on 4/5 codecs. Kill criterion hit at census stage.

**E2 verdict: SHELVED** unless a future path produces large equivalence
streams (e.g. text-inner emitting op streams at MB scale).

**Text corpora: fz-native transforms contribute ~nothing.** From
`.enwik8_pre_outer_decomposition.json`: enwik8 inner = 100,000,187 B (≈ raw),
final wire 19,594,333 B of which **19,538,886 B (99.7%) is the external zpaq
payload**. On text, fz today = container + codec dispatch; all modeling is
outsourced. fz-native text gains require a modeling layer (E3/E4 direction)
and/or deeper dict preprocessing to feed the external CM — see speed/phda9
memo below.

**Pivot:** E3/E4 retarget from "literals inside equivalence streams" (no mass)
to **general text literal planes** — the fz-weak / CM-strong battlefield.

---

## E3 RETARGETED (post speed/phda9 memo, 2026-07-11)

Memo verdicts (general-purpose seconds–minutes budget):
- **PHP range coder on MB literal planes: reject.** phda9's *native* CM does
  ~6.3 kS/s; PHP per-symbol would be far slower. Per-symbol modeling in the GP
  path must be a native helper or nothing.
- **fz text wins today are outer-codec selection** (zpaq/arc/brotli tournament);
  literature "ties" are byte-identical zpaq passthrough. fz-native transforms
  add ~no ratio on prose.
- **Best ROI: deeper phda9-style dictionary preprocessing in PHP** — inline
  tokenize (`fractal_zip_phda9_tokenize.php`) + cfabb phrase mining are cheap
  (sub-minute) and feed *better bytes* to the same outer codecs.
- Note: memo predates E0 results; its "plane-split next" suggestion is
  superseded by E0's rejection.

### E3a — inline tokenize + cfabb on the general_text path (ACTIVE)

Hypothesis: greedy dict tokenization + collision-free phrase substitution
before the outer tournament wins bytes on prose corpora within GP budget.
Method: `benchmarks/bench_general_text_gate.php` corpora (108/115/122/124/130),
baseline vs `FRACTAL_ZIP_PHDA9_DICT_INLINE=1 MODE=tokenize` (+`CFABB_INLINE=1`),
one corpus per invocation. KEEP per corpus = smaller `.fz`, RT-verified,
zip_seconds within ~2× baseline.

### E3a RESULTS (2026-07-11, `benchmarks/bench_tokenize_outer_isolation.php`)

Pipeline-level gate runs (test_files115/122): text-inner without the CM stage
FAILS (`fztx` +11,834 / +574; tokenize+parallel_phda9 +196,184). The existing
text-inner win (−13,681 on 115) is bought entirely with `phda9_no_lstm` at
381 s — outside GP budget.

Isolation (tokenize → generic codecs, lcet10 prose / plrabn12 poetry):

| setup | gzip9 | zstd19 | brotli11 | xz9 | zpaq5 |
|---|---|---|---|---|---|
| mined vocab (+55 KB blob), lcet10 | +117 | +11.8K | +10.5K | +8.6K | +14.8K |
| **frozen shipped dict (0 B), lcet10** | **−11,466** | **−4,525** | **−2,031** | **−6,464** | +2,199 |
| frozen dict, plrabn12 (poetry) | +3.1K | +4.2K | +2.1K | +1.2K | +3.7K |

**Verdicts:**
- Tokenization is a **fast-tier feature**: real wins on modern prose under
  gzip/zstd/brotli/xz (up to −8% at gzip tier), ~3 s PHP per 430 KB (~130 KB/s).
- **Never apply in front of CM-class outers** (zpaq models words internally;
  pre-tokenization costs +2–4 KB). If the outer tournament picks zpaq, ship raw.
- **Must ship no vocab**: per-corpus mined dicts lose their blob cost; only the
  frozen fz-side dictionary (0 wire bytes) wins.
- **Needs a content gate**: archaic/poetic text (dict misses) loses everywhere.
  Gate on tokenizer hit-rate before committing the transform.
- ~~PHP throughput ~130 KB/s caps this at a few-MB per member for GP budgets.~~
  **Lifted (2026-07-11):** native helper `tools/parallel_paq/fz_tokenize`
  (built from the existing `tokenizer.c` wire, plus an exact-token hash index)
  does **~110 MB/s** — webster 41 MB tokenizes in 0.39 s vs 44 s PHP, output
  **byte-identical** to the PHP reference, RT OK. The PHP wrapper
  (`fractal_zip_phda9_tokenize()`) auto-uses it for inputs ≥256 KB
  (`FRACTAL_ZIP_TOKENIZE_NATIVE=0` to disable; PHP remains reference +
  fallback). Tokenization is now effectively free at any member size.

### Native acceleration policy (user-approved 2026-07-11)

Transcode PHP hot paths to small C helpers **only when**: (1) a measured
bottleneck blocks a byte-win feature, (2) the helper reuses an existing wire
with a PHP reference implementation kept as fallback, (3) byte-identity vs the
PHP path is verified on real corpora. Invocation style = external binary like
zstd/brotli (consistent with fz architecture; no FFI/extension dependency).
Already-native: substring mining (`tools/gpu_substring` Rust), CM competitors
(`tools/parallel_paq`), now tokenize. Next candidates if they ever bottleneck:
cfabb phrase mining, wiki_lom entity pass.

### E3b RESULTS (2026-07-11) — cap-fold escape implemented

Headroom measurement (frozen dict): capitalization-only misses are 0.6–2.2%
of tokens (lcet10 1.1%, plrabn12 2.2%, dickens 0.6%) — small but real.

Implemented `0xFD 0x01` cap-word escape (initial-cap fold only) in **both**
the native helper (`fz_tokenize encc`, decode handles both wires) and the PHP
reference (`fractal_zip_phda9_tokenize($plain, $words, caps: true)`).
Byte-parity PHP↔native verified on 3 corpora; RT OK; native caps path 41 MB
in 1.7 s. v1 wire unchanged (caps is opt-in; `parallel_phda9` does NOT
understand 0x01 — cap wire only where fz detokenizes).

Codec deltas (tok+caps vs tok, 430–480 KB files):

| corpus | gzip9 | zstd19 | brotli11 | xz9 |
|---|---|---|---|---|
| lcet10 | −722 | −463 | −195 | −416 |
| plrabn12 | −1,431 | −652 | −524 | −600 |

Caps helps everywhere but does NOT flip poetry to a net win vs raw
(plrabn12 tok+caps gzip 195,865 vs raw 194,246) — the **hit-rate gate stays
mandatory**.

**Scale finding (webster 41 MB):** tok+caps vs raw: gzip9 **−57,611**;
zstd19 **+128,352**; brotli11 +75,287; xz9 +47,124. Large-window codecs
already capture cross-file word redundancy — tokenization only substitutes
for a *small window*. So the feature is: (a) all non-CM tiers for members
≲0.5 MB, (b) **gzip/deflate tier at any size** (fz's gzip-fast path is the
natural home), (c) never for zstd/brotli/xz on multi-MB prose, (d) never CM.

### E3-next — fast-tier pipeline gate (PENDING)

Wire into the outer path: apply tokenize(+caps) only when (a) member profile
is prose/textish, (b) outer tier per scale rule above (gzip/deflate any size;
zstd/brotli/xz only ≲0.5 MB), (c) quick hit-rate probe (first 64 KB) ≥
threshold (~85% token hit rate), (d) RT verify per member. Env-gated
`FRACTAL_ZIP_TOKENIZE_FAST_TIER=1` until gate benches pass on
108/115/122/124/130. Primary integration target: the **gzip-fast folder
path** (deflate level tuning loop), where the 32 KB window makes the frozen
dict a global-window substitute (−0.5% on webster for ~0.4 s/41 MB).

### E3b (superseded heading) — capitalization modeling port

Upstream phda9 preprocessing models capitalization (lowercase + cap-flag
stream); fz's tokenize lacks it. Cheap PHP transform, helps dict hit rate on
sentence-initial words. Gate on E3a corpora.

### E3c — native-helper per-symbol coder (GATED)

Only if E3a/E3b plateau and a measured gap remains vs CM-class on the *same
preprocessed stream* (e.g. zpaq m5 vs phda9_no_lstm on tokenized bytes).
Order-2/o3 context model + range coder as a small C helper invoked like other
external codecs (consistent with fz architecture). Budget target: ≥1 MB/s.

### E4 — top-k mixture coding: unchanged, research-only, after E3c exists.
