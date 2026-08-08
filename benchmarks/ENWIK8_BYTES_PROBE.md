# enwik8 bytes probe (subset-first)

After text codecs pass **bit-exact** roundtrip, shrink `.fz` toward pp96 (~19.6 MiB) using **subset probes** before long full encodes.

**Decision (2026-06-05):** Textcodec (`words_base94_isp`) is **off on wire** for bytes push — isp+none verifies OK but ~2× pp96 fzc (~41 MiB). Current best path: **pp96 core** (`FRACTAL_ZIP_ENWIK_TEXT_CODEC=0`, pp96/96, unified stream, zpaq method 9) at **19,594,333 B**. Inner cap toggles and textcodec sidecars do not beat pp96; see `ENWIK8_GRID_CONCLUSIONS.md`.

## Workflow

```bash
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1

# 1) Stratified article sample (default 5; use 20+ for stabler text stats)
php benchmarks/build_enwik8_sample_pages.php --count=20

# 2) Text codec grid on sample (fast) — lab only; not for wire
php benchmarks/bench_enwik8_text_codec_lab.php --quick
php benchmarks/summarize_enwik8_text_codec_lab.php

# 3) Content decomposition + gzip-1 on enwik8 slices
php benchmarks/bench_enwik8_content_probe.php
php benchmarks/summarize_enwik8_content_probe.php

# 4) Entry-sorted slice .fz (textcodec off / corpus / isp+none / isp+sort_lines)
php -d memory_limit=2048M benchmarks/bench_enwik8_wire_slice_probe.php --pages=384
# 4a) Amortized static-meta table from probe JSON (FZDI/FZSP + preprocess meta)
php benchmarks/bench_enwik8_amortized_meta.php --pages=384
# 4b) Entry sort on vs off wire A/B
php -d memory_limit=2048M benchmarks/bench_enwik8_entry_sort_wire_probe.php
# 4c) zpaq ceiling on pre-outer inner variants
php -d memory_limit=2048M benchmarks/bench_enwik8_zpaq_inner_preprocess_probe.php --pages=384
# 4d) Text-inner combo probes (mi_reorder + corpus/word/semantic/entry_sort_off)
php -d memory_limit=2048M benchmarks/bench_enwik8_wire_slice_probe.php --pages=384 --cases=no_textcodec,split_text_inner_mi,split_text_inner_mi_corpus,entry_sort_off_text_inner_mi
# 4e) Roundtrip on best candidate
php -d memory_limit=2048M benchmarks/verify_enwik_slice_roundtrip.php --pages=384 --text-inner --text-inner-layout=mi_reorder --entry-sort=0

# 5) Inner env on 8 MiB multi-file slice (generic unified stream; may not match entry-sorted wire)
php -d memory_limit=2048M benchmarks/bench_enwik8_inner_unified_probe.php

# 5b) Pre-zpaq inner on entry-sorted enwik slice (real wire path)
php -d memory_limit=2048M benchmarks/bench_enwik8_pre_outer_slice_probe.php --pages=384

# 6) Full pp96 refresh (no textcodec) — replace bloated test_files109.fz
php -d memory_limit=4096M benchmarks/run_enwik8_pp96_refresh.php --name=pp96_refresh
tail -f benchmarks/logs/enwik8_pp96_refresh.log

# 6b) Full encode with text-inner promotion (fztx mono mi_reorder; 768p gate cleared)
php -d memory_limit=4096M benchmarks/run_enwik8_pp96_refresh.php --text-inner-promotion
tail -f benchmarks/logs/enwik8_text_inner_promotion.log

# 7) Follow ranked avenues in .enwik8_content_probe.json → one full encode when a subset wins
php benchmarks/compare_enwik8_textcodec_vs_pp96.php
```

## Subset probes (`bench_enwik8_content_probe.php`)

| Subset | What it tells you |
|--------|-------------------|
| `header` / `footer` | Fixed XML shell cost |
| `page_shells_all` | Markup outside `<text>` (template/title/links) |
| `text_all_pages` | Total compressible article prose (gzip-1 ceiling) |
| `text_isp_payload` | Tokenized payload + **sidecar JSON size** after `words_base94_isp` + `sort_lines_alpha` |
| `sample5_*` | Same metrics on top long pages (fast loop) |

Output: `benchmarks/.enwik8_content_probe.json` with **`avenues[]`** (ranked next steps).

## Decision rules

0. **Wire push (2026-06)** — `isp` + `transform=none` verifies at ~41.5 MiB (~2× pp96). **Disable textcodec on full encodes** (`FRACTAL_ZIP_ENWIK_TEXT_CODEC=0`); keep codec work in the lab until sidecars shrink. Refresh wire with `benchmarks/run_enwik8_pp96_refresh.php`.
1. **Correctness** — only `words_base94_isp` / `words_id_varint_isp` on wire; no UNK; smokes in `tests/enwik_text_codec_isp_lossless_smoke.php`.
2. **Transform A/B** (`bench_enwik8_sidecar_probe.php` on sample5):
   - `sort_lines_alpha`: payload gzip-1 **low**, sidecar gzip-1 **~1.7 MiB / 20 pages** (dominates `.fz`).
   - `none`: payload gzip-1 higher, sidecar gzip-1 **~354 KiB / 20 pages** — prefer for **bytes push** until sidecars are binary-packed.
3. **If inner slice A/B ties but full fzc differs** — run `run_enwik8_pp96_refresh.php` or `run_enwik8_inner_experiment.php` on full `test_files109`.
4. **Textcodec on wire** — do **not** use for bytes push (2× pp96); lab/roundtrip only until sidecars shrink.
5. **Mini-fzc** on a loose `pages/*.xml` folder ties with/without textcodec — use **entry-sorted enwik8** probes for wire A/B, not multi-file folders.

### Full encode results (reference)

| Run | fzc | verify |
|-----|-----|--------|
| **pp96 / pp96_refresh** | **19,594,333 B** (~19.6 MiB) | — |
| textcodec + `sort_lines_alpha` (rerun14/15) | 62–80 MiB | OK |
| textcodec + `transform=none` (isp) | ~41 MiB | OK |
| Inner slice (2026-06-05) | `probe_fzbm2048`, `probe_zpaq4`, **`probe_staged0`** (`STAGED_LITERAL_FAST_OUTER_MIN_RAW_BYTES=0`) | **tied** 1,769,220 B — no full encode |
| Pre-outer decomp (2026-06-05) | inner **≈100 MB** (raw+187 B); inner_gzip1 **42.3 MiB**; zpaq payload **19.54 MiB** | fractal inner does not shrink unified stream before zpaq — **bytes win is outer zpaq on sorted ~100 MB blob** |
| Integrated preprocess (2026-06-05) | full-blob gzip-1: corpus phrases **−324 KiB** vs raw (blob **−2.84 MiB**); siteinfo header **−98 B** | gzip win does not survive zpaq outer |
| Wire slice 384p (2026-06-05) | `no_textcodec` **658,258 B**; `no_textcodec_corpus` **663,302 B** (+5,044, **verify fail**); `isp_none` 1,236,560 B; `isp_sort_lines` 2,374,436 B | **Do not** enable `CORPUS_PHRASES` on wire — regression + broken roundtrip |
| Wire slice 384p text-region corpus (2026-06-05) | `no_textcodec` **658,258 B**; `no_textcodec_corpus` **656,794 B** (−1,464, **verify OK**); fix: refresh `enwik_split_page_refs` after corpus pack | Below **5 KiB** wire threshold (653,138 B) — keep `CORPUS_PHRASES=0` on wire until larger win; lab/smoke OK |
| Unified inner (2026-06-05) | `choose_smallest_adaptive_literal_inner_or_raw_escaped` (~7132) picks **`raw`** (~100,000,187 B); zpaq payload **19,538,886 B** | env/cap grid exhausted |
| Pre-outer slice 384p (2026-06-05) | `pp96_baseline` / `inner_recursive0` / `inner_recursive0_grammar` → inner **2,970,916 B**, gzip1 **1,261,594** (all **tied**, ~8s) | entry-sorted wire path; `RECURSIVE_ONLY=0` + 128 MiB run-grammar/peeler caps do not shrink inner |
| Zpaq inner preprocess 384p (2026-06-05) | **raw_slice** zpaq **653,185** vs **sorted_inner** **683,272** (−30,087); delta/xor **+79–125 KiB** worse | `bench_enwik8_zpaq_inner_preprocess_probe.php` |
| Entry sort wire 384p (2026-06-05) | `entry_sort_on` **658,258 B**; `entry_sort_off` **653,192 B** (−5,066); **both verify OK** | `verify_enwik_slice_roundtrip.php --entry-sort=0`; disable `PAQ_NATIVE_COMPARE` on probe (else phda9 hours) |
| **Split-inner wire 384p GATE CLEARED** (2026-06-06) | **`split_inner_fztx_mono_mi` 651,650 B** (−**6,608** vs 658,258); `split_inner_fztx_mi` 657,122 B (−1,136); `split_inner_fztx_mi_stack` 717,022 B (+59 KiB); **verify OK** | **Promotion env:** `TEXT_INNER=1` + `FORMAT=fztx` + `MONO=1` + `LAYOUT=mi_reorder` + `PREPROCESS=none` + `STACK=none`; global mi_reorder on one FZTX member (pages/member=384); lab stacked inner 647,837 B still ~3.8 KiB lower (mono-outer zpaq vs lab zpaq+brotli final) |
| **FZSO stack+brotli outer wire 384p GATE FAIL** (2026-06-06) | **`split_inner_fztx_mono_mi` 651,650 B** (outer **zpaq**); **`split_inner_fztx_mono_mi_stack_brotli_outer` 652,500 B** (outer **zstd**, **+850** vs mono_mi); forced `FRACTAL_ZIP_FORCE_OUTER=zpaq` → **653,605 B** (+1,955 vs mono_mi, **+1,105** vs adaptive zstd) | Case: `STACK=zpaq9_brotli11`, `STACKED_OUTER=0` — inner FZSO applied in `fractal_zip_enwik.php` (~1660); unified stream runs full `adaptive_compress` (`fractal_zip.php` ~27652+) because passthrough is off; pre-compressed FZSO blob favors **zstd** over zpaq in the tournament; forcing zpaq **regresses** — **keep `STACK=none`** |
| **Split-inner wire 768p GATE CLEARED** (2026-06-06) | `no_textcodec` **1,389,809 B**; **`split_inner_fztx_mono_mi` 1,325,096 B** (−**64,713** vs baseline; gate ≤1,384,689); per-page **−84.3 B/page** vs 384p **−17.2 B/page** — win **scales up**; **verify OK** (~6 min probe + ~6 min verify) | Re-run superseded stale row (1,347,710 B regression). **Full encode launched** — see below |
| **Full encode text-inner promotion DONE** (2026-06-06) | **`test_files109.fz` 19,207,083 B** (−**387,257** vs pp96_refresh **19,594,340 B**); encode **17,252 s** (~4.8 h); verify **222 s**; **`verify_ok=1`** (100 MB restore, sha256 OK); `member_count` **3** | Env: `TEXT_INNER=1` + `FORMAT=fztx` + `MONO=1` + `LAYOUT=mi_reorder` + `PREPROCESS=none` + `STACK=none` + `ENTRY_SORT=1`; JSON: `benchmarks/.enwik8_exp_text_inner_promotion.json`; log: `benchmarks/logs/enwik8_text_inner_promotion.log` |
| Split-inner dict_nncp wire 384p (2026-06-06) | **`split_inner_fztx_mono_mi_dict` 726,612 B** (+**68,354** vs 658,258; +**74,962** vs `split_inner_fztx_mono_mi` 651,650); **verify OK** (smoke + frozen vocab roundtrip) | `PREPROCESS=dict_nncp` + mono FZTX mi_reorder; **segment_v2 lossless** + **frozen vocab** (16,384 words mined from all 384 pages in mono, stored once in `meta/text_inner_preprocess.json`); per-page sidecars omit vocab; **regresses wire** — keep `PREPROCESS=none` on promoted path |
| Text-inner wire 384p (2026-06-05) | dual `.text`/`.shell` + corpus/word/brotli: ~**657,3xx B**; pre-stack on `.text` only: no win | `FRACTAL_ZIP_TEXT_INNER_FORMAT=dual` legacy; below **5 KiB** gate (653,138 B) |
| Sub-1 inner 384p (2026-06-05) | Best **dict_nncp + mi_reorder + zpaq9** → **text_bpc 1.550** (547,851 B); **0 sub-1 rows** at 384p; sample5 paq_inner+isp **0.952 bpc** (hours-scale) | `bench_enwik8_sub1_bpc_matrix.php --pages=384`; sub-1 does not survive scale-up |
| Text-inner preprocess wire 384p (2026-06-05) | `no_textcodec` **658,258 B**; `split_text_inner` **658,325 B** (+67); `split_text_inner_mi` **657,273 B** (−985, **verify OK**); `split_text_inner_isp` **905,065 B** (+246,807, **verify OK**); `split_text_inner_mi_isp` **903,490 B** (+245,232) | `FRACTAL_ZIP_TEXT_INNER_PREPROCESS` wire allowlist (`none`/`isp_varint`/`textcodec_isp`); per-page sidecars in `meta/text_inner_preprocess.json`; isp sidecar JSON **regresses** wire bytes; mi_reorder best at −985 B — **below 5 KiB gate** (653,138 B) |
| Text-inner combo wire 384p (2026-06-05) | `no_textcodec` **658,258 B**; `split_text_inner_mi` **657,273 B** (−985); `split_text_inner_mi_corpus` **657,304 B** (−954); `split_text_inner_mi_word` **657,273 B** (−985); `split_text_inner_mi_semantic` **657,404 B** (−854); `no_textcodec_corpus` **656,794 B** (−1,464); **`entry_sort_off_text_inner_mi` / `entry_sort_off` / `entry_sort_off_corpus` / `split_text_inner_mi_brotli` all **653,192 B** (−5,066)**; **verify OK** on `entry_sort_off_text_inner_mi` | **5 KiB gate NOT MET** (target ≤653,138 B; **54 B short**). With `ENTRY_SORT=1`, text-inner mi best at −985 B; corpus/word/semantic stacks do not beat mi alone. **`ENTRY_SORT=0` dominates** (−5,066 B) — text-inner/corpus/brotli add **0 B** atop entry-sort-off. **`FRACTAL_ZIP_STACKED_OUTER` not on wire** (lab `FZSO` only); `SKIP_BROTLI=0` (`split_text_inner_mi_brotli`) ties entry-sort-off, not stacked zpaq+brotli inner. Keep `FRACTAL_ZIP_TEXT_INNER=1` **opt-in**; do **not** flip `ENTRY_SORT=0` on full wire (+30 KiB full encode) |
| Full encode `entry_sort=0` (2026-06-05) | **19,625,015 B** (+30,682 vs pp96); **no FZEP trailer**; `verify_ok=null` | 384p slice win (−5,066 B) **does not scale** — keep `ENTRY_SORT=1` on wire |

### Avenue exploration (2026-06-06)

**Baseline (full wire):** **19,594,340 B** — `benchmarks/.enwik8_exp_pp96_refresh.json` (`pp96_refresh`, `FRACTAL_ZIP_ENWIK_TEXT_CODEC=0`, entry sort on).

Ranked top-5 by expected **full-encode** impact (slice 384p/768p + lab probes; content `avenues[]` superseded for wire bytes push):

| Rank | Avenue | Evidence | Δ vs baseline | Verify | Scale | Rec |
|------|--------|----------|---------------|--------|-------|-----|
| 1 | **FZTX mono + mi_reorder** (`TEXT_INNER=1`, `FORMAT=fztx`, `MONO=1`, `LAYOUT=mi_reorder`, `PREPROCESS=none`, `STACK=none`, `ENTRY_SORT=1`) | 384p wire **651,650 B** (−6,608); 768p **1,325,096 B** (−12,967); **full 19,207,083 B** (−**387,257** vs **19,594,340 B**) | **−387,257 B** full (~**31 B/page** over 12,347 pages — **beats** ~17 B/page slice extrapolation) | Slice + full **`verify_ok=1`** | Win **scales up** slice→full | **Promoted** — fold env into pp96 wire preset |
| 2 | **Lab stacked inner** (`zpaq9_brotli11` on split inner, `FZSO` lab only) | Fresh layout probe mi_reorder/raw **647,824 B** stack vs wire mono **651,650 B** (~3.8 KiB lab edge); stacked outer probe best **652,390 B**; wire `split_inner_fztx_mi_stack` **717,028 B** (+58,770) | Not on wire — no full Δ | Lab **roundtrip OK** | `FZSO` stack/undo in `fractal_zip_text_compressor_external.php` + `fractal_zip_enwik.php`; **no stacked outer pass-through** on unified-stream wire (`FRACTAL_ZIP_STACKED_OUTER` unset) | **Lab-only** — wire pre-stack regresses |
| 3 | **dict_nncp + mono mi** (frozen vocab preprocess) | 384p `split_inner_fztx_mono_mi_dict` **726,612 B** (+68,354 vs 658,258); sub-1 matrix **text_bpc 1.550** (547,851 B) | Wire **regresses**; full not run | **OK** (segment_v2 + frozen vocab) | Strong on **sample5** (0.952 bpc hours-scale); **fails wire scale** | **Lab-only** — keep `PREPROCESS=none` on promotion path |
| 4 | **Sub-1 / paq_inner grid** | `bench_enwik8_sub1_bpc_matrix.php` 384p: **0 sub-1 rows**; best dict_nncp+zpaq9 lab ceiling only | No full win path | Lab | Does not survive 384p wire matrix | **Lab-only** |
| 5 | **entry_sort_off + text-inner mi** | 384p **653,192 B** (−5,066); **768p probe (2026-06-06)** **1,331,052 B** (−7,011 vs 1,338,063); full **`entry_sort=0`** **19,625,015 B** (**+30,682**) | **+30,682** full | Slice OK; full regresses | 768p slice win **≪** mono_mi; full **anti-scales** | **Drop** on full wire — keep `ENTRY_SORT=1` |

**Explicit drops (do not promote to full wire):**

- **Pre-stack FZTX on wire** — `split_inner_fztx_mi_stack` 384p **717,022 B** (+59 KiB); pre-stack on `.text` only: no win (`ENWIK8_BYTES_PROBE` text-inner 384p row).
- **ISP / textcodec text-inner** — `split_text_inner_isp` **905,065 B** (+246,807); `words_base94_isp` full ~41 MiB (~2× pp96); sidecar JSON dominates.
- **`entry_sort=0` full encode** — see rank 5 (+30,682 B).
- **Corpus phrases on wire** — `no_textcodec_corpus` verify fail / marginal; integrated gzip wins die in zpaq outer.
- **stat_pred / stat_isp tier-D preprocess** — combo probe @384p (2026-06-06): `mono_mi` **651,650 B**; `stat_isp` **997,318 B** (+345,668); `stat_pred` **1,108,313 B** (+456,663; stale 587,872 artifact superseded). Payload −71 KiB vs isp when bigram ranks fire, but **+625 KiB bigram_succ_ids meta** + sidecar tax; **do not promote** — keep `PREPROCESS=none` on wire.
- **Member codec shootout** — `split_inner_fztx_mono_mi_shootout` **652,504 B** (+854 vs mono_mi @384p, 2026-06-06); per-chunk zpaq/FZSO tournament **regresses** wire — lab-only.
- **FZBM path-order 2048 on text-inner mono** — `split_inner_fztx_mono_mi_fzbm2048` **651,650 B** (+0 vs mono_mi @384p, 2026-06-06); `FZBM_ORDER_RANDOM_TRIES` only affects unified-stream literal-bundle concat order, not text-inner FZTX mono (3 virtual members) — **dead end** for beat-15M 384p gate. **Env bleed fix (2026-06-06):** wire probe now calls `bench_wire_probe_reset_stat_preprocess_env()` before each case (`ENWIK_STAT_SIDECAR=0`, `TEXT_INNER_PREPROCESS=none`); stat_pred → fzbm2048 in one process was **802,685 B** before fix, **651,650 B** after.
- **inner_combo + text-inner mono on wire** — `split_inner_fztx_mono_mi_inner_combo` **651,650 B** (+0 vs mono_mi @384p, 2026-06-06); pp96 core + fztx mono mi_reorder + `inner_combo` caps (`multidiff`, `allsub`, `deep`, `RECURSIVE_ONLY=0`) — **ties** mono_mi (~95s vs ~114s encode); full inner experiments at 19,594,333 B omit text-inner — no wire win at 384p slice.
- **sort_by_len mono wire** — `split_inner_fztx_mono_sort_len` **657,890 B** (+**6,240** vs `split_inner_fztx_mono_mi` 651,650 @384p, 2026-06-06); existing layout (perm by descending text len); meta **~1.5 KiB** (same as mi_reorder); length sort **anti-correlates** with MI neighbor clustering — **abandon** on wire.
- **mi_line_stripe mono wire** — new layout: mi_reorder perm + interleave line *k* across pages; meta **~2.5 KiB** (`perm` + `line_counts`); striped blob **+383 B** vs concat (extra `\n` at page boundaries in stripe join); `split_inner_fztx_mono_mi_line_stripe` **699,662 B** (+**48,012** vs mono_mi @384p, 2026-06-06); smoke roundtrip OK — **abandon** (structural newline tax + order hurts zpaq).
- **dict_inner (FZEP trailer)** — @384p wire **733,615 B** (+81,965 vs mono_mi **651,650**); FZDI vocab **133,611 B** raw; inner-fold trailer wire **58,981 B** (`fractal_raw_gzip`); **amortized_fzc 676,515 B** (+24,865 vs mono — uses wire trailer for amort tax); **verify OK** @32p+384p.
- **dict_phda9_inner @384p (2026-06-07)** — wire **733,635 B**; amortized **676,527 B** (+24,877 vs mono_mi); **verify OK** @32p+384p after restore fix (`dict_phda9_inner` case in `fractal_zip_text_preprocess_undo()` — sidecar `preprocess` was falling through to passthrough). **parallel_paq** member wrap: in-repo codec **~3.1 MiB FZPA** regressed wire when auto-wrapped; gate now skips FZPA unless wire **< gzip(inner)** (or `FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ_FORCE=1`) — with env set, **733,635 B** ties baseline (no wrap).
- **stat_pred @384p reconfirm (2026-06-07)** — fresh probe: **`mono_mi` 651,650**; **`mono_concat` + `stat_pred_inner` 904,981 / meta 253,402 / amort **659,660** (+8,010)**; sparse4096 on `mi_reorder` **661,619** (+9,969). `BIGRAM_MAX_PREV=4096` does not shrink mono_concat wire (ranksUsed reinject). `INNER_FOLD_FRACTAL=0` gzip trailer **regresses** mono_concat to **661,651**. Wire probe env bleed fix: `bench_wire_probe_reset_stat_preprocess_env()` now clears `INNER_FOLD_FRACTAL*` between cases. **FZPM anatomy @384p:** raw **447,684 B** = bigram+vocab **331,295** + FZPS gap sidecars **116,389**; sealed trailer **253,184 B**; member+outer **651,579** (−71 vs mono_mi). **Gate blockers tried:** gzip inside FZPM (sealed meta regressed); external `meta/stat_pred_inner_sidecars.fzps` member (amort **731,623**, +72 KiB member tax). Need ~**8 KiB** amortized shrink (≈**251 KiB** sealed meta at 384/12041 scale — member −71 B alone cannot close gap).
- **stat_pred @384p FZPS v3/v4 (2026-06-08, verify OK)** — Fixed `strlen(int)` on gap keys (`array_map('strval', …)`). **FZPS gap pool:** v2 lex **122,641 B** → v3 freq-sorted **114,989 B** (−7,652) → **v4 front-coded** **94,877 B** (−20,112 vs v3 plain pool; **−27,764** vs v2). **uint16 fixed refs** rejected (pool=9302; 21,766/23,603 refs already need 2-byte varint). **Zigzag delta page refs** rejected (−1,477 B vs abs varint). **Wire @384p reconfirmed** (`bench_enwik8_wire_slice_probe.php` 2026-06-08): **`mono_concat` + `stat_pred_inner` 902,848 / meta 251,269 / amort **659,592** (+7,942 vs mono_mi)**; member+outer **651,579** (−71 vs mono_mi). **verify OK** @384p mono_concat (`lens_sum` match). **Gate NOT cleared** — amort gap ≈ sealed_meta × 384/12041 (**~8 KiB**); need **~249 KiB** sealed-trailer shrink (or **~8 KiB** extra member savings) to beat mono_mi; FZPS/front-code alone cannot close (FZPS raw **~95 KiB** → sealed contrib **~55 KiB**; bigram+vocab sealed **~197 KiB**). Lab: `benchmarks/bench_fzps_encoding_lab.php`.
- **stat_pred @384p FZPM v4 sparse succ (2026-06-08, verify OK, gate narrowed)** — **FZPM v4** ships only **emitted rank→succ pairs** per bigram row (`bigram_ranks_used` in serialize); picks over v2/v3/v5 via gz9 tournament. @384p: raw **−41,436 B** (384,738 vs 426,174); sealed meta **−39,013 B** (**212,256** vs 251,269); wire **863,835**; amort **658,348** (**+6,698** vs mono_mi, was +7,942). Member+outer **651,579** (−71 vs mono_mi). **verify OK**. Lab: `bench_stat_pred_sparse_succ_lab.php` (est. **87 KiB** raw succ save), `bench_fzpm_v3_size.php`, `bench_fzpm_v4_vocab_lab.php`. **FZPM v5 lex+front vocab** (candidate): raw **−20 KiB** vs v4 but **gz9 +8.5 KiB** (perm table); **picker keeps v4**. Vocab front on freq-order **regresses** (−14 KiB). **Gate NOT cleared** — need **~6.7 KiB** more amortized (~**210 KiB** sealed-meta at slice scale) or **~6.7 KiB** member+outer gain; meta path largely exhausted.
- **stat_pred @384p continuation (2026-06-09, verify OK)** — **FZPM v6 implicit rank-0** wins gz9 tournament (ver=6; **−537 B** raw / **−320 B** sealed vs v4 in `bench_fzpm_v4_v6_implicit0_lab.php`; vocab+sidecar roundtrip OK). **INNER_FOLD_FRACTAL_FAST=1 trap:** mono_concat seal lab (`bench_inner_fold_mono_concat_seal.php`) — FAST **259,823 B** `fractal_raw` vs full outer **212,319 B** `fractal_raw_gzip` (−**47.5 KiB**); wire probe FAST=1 had inflated amort to **659,767** (+8,117). **Fix:** stat_pred wire cases use **`INNER_FOLD_FRACTAL_FAST=0`**. **Re-probe @384p:** `mono_mi` **651,650**; `mono_concat`+`stat_pred_inner` **863,922** / meta **212,343** (`fractal_raw_gzip`) / amort **658,351** (**+6,701** vs mono_mi); member+outer **651,579** (−71). **`sealed_pick`:** **863,835** / meta **212,256** / amort **658,348** (+6,698, ~**−87 B** meta vs gz9-only v6; **2×** encode ~413 s — not promoted). **`varint` codec:** **873,848** / amort **671,294** (+19,644, dead). Gate **NOT cleared** — amort gap is meta tax (**~6.8 KiB** @384/12041); member already **−71 B** vs mono_mi; need **~6.7 KiB** more member+outer **or** **~210 KiB** sealed-meta shrink. Next: `entry_sort_off_mono_concat_stat_pred_inner` probe; stack cases still expected to regress.
- **stat_pred wire GATE reconfirm (2026-06-16)** — `bench_stat_pred_wire_gate.php` wraps wire slice probe. **@384p (authoritative):** `mono_mi` **651,650**; `mono_concat`+`stat_pred_inner` wire **863,922** / meta **212,343** / amort **658,351** (**Δ +6,701**, **FAIL**). Matches Jun-09 numbers — **gate still blocked**. **@96p trap:** same case shows amort **39,893** (**Δ −16,366 PASS**) but slice wire **81,350** (**+25,091** vs mono **56,259**) — small slices **mis-rank** amortized GATE; use **≥384p** for promotion decisions. Artifacts: `.stat_pred_wire_gate_384p.json`, `.stat_pred_wire_gate_96p.json`.
- **phda9 English lab @96p (2026-06-16)** — `bench_phda9_english_lab.php --fast`: **sorted_article_text** (185,341 B) → phda9_no_lstm **39,234 B**; fztx_inner phda9 → **SEGFAULT** (use **phda9_xml** format). **`bench_hutter_wire_gate.php` reconfirm @96p** (Δ = wire − mono_mi): mono_mi **56,259**; phda9_xml parallel / single-stream no_lstm **42,795** (**Δ −13,464 WIN**); LSTM **42,804** (**Δ −13,455**, +9 B vs no_lstm). **`bench_hutter_wire_gate.php` @384p:** mono_mi **651,650**; parallel **560,162** (**Δ −91,488**); single-stream **524,040** (**Δ −127,610 WIN**, ~809 s vs ~1177 s parallel). **Full encode gate-check:** extrap **15,157,164 B** (`allow_full_encode=true`, −3.69 MiB vs prior best). Production env already sets `FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1` + `phda9_no_lstm`.
- **stat_pred @384p FZPM v3 packed (2026-06-08, superseded by v4)** — front-coded vocab + global succ mode; **picker kept v2** on wire before v4. Lab-only.
- **stat_pred_inner (FZEP v7 + FZPS\x02 gap pool)** — Trailer-only bigram prune for encode (full model); FZPM ships used-prev rows. **Inner-fold trailer** uses `fractal_zip_enwik_inner_fold_seal_trailer()`: gzip(`FZIF\x01`) vs **FZS1 + full adaptive outer (zpaq)** in `FZIF\x02` (default `FRACTAL_ZIP_INNER_FOLD_FRACTAL=1`; probe speed: `FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=1`). Deep fractal-string tried when roundtrip-safe (`FRACTAL_ZIP_INNER_FOLD_FRACTAL_DEEP=0` to skip). Legacy encode prune: `FRACTAL_ZIP_STAT_PRED_INNER_PRUNE_ENCODE=1`. Roundtrip OK @384p (`fzc=933,859`).
- **stat_pred_inner @384p (2026-06-07, base94 default + frozen FZPM)** — JSON: `benchmarks/.enwik8_wire_slice_probe_384p_frozen.json`; frozen model: `benchmarks/.stat_pred_inner_model.fzpm` (1,540,582 B raw, 12,041p mine); **verify OK** (`verify_384_base94.log`, `lens_sum` match). **None beat `mono_mi` amortized 651,650**; best stat_pred is **sparse4096** at **660,988** (+9,338) per frozen JSON (authoritative pre-ranksUsed-trim row).
- **BIGRAM_MAX_PREV sweep @384p (2026-06-07)** — `benchmarks/logs/wire_384_prev_cap_sweep.log`. **384p emits ~10k+ distinct bigram prev words** (`bench_stat_pred_emitted_prev_count.php`); `BIGRAM_MAX_PREV=4096` only shrinks trailer when **reinject is off** (then **verify fails** @384p for caps ≤9000). **Verify-OK path** requires `ranksUsed` succ-tail trim + reinject of emitted-prev rows → sparse4096 **906,543** / **661,619** (+9,969). **Lab-only better amort:** **886,743** / **660,988** (+9,338) without reinject (32p verify OK, 384p fail). Uncapped (`MAX_PREV` unset) verify OK but larger wire. Cap-align 2-pass encode **regressed** (673k amort, 52k hits). New labs: `bench_stat_pred_emitted_prev_count.php`, `bench_stat_pred_max_prev_verify_sweep.php`. Build stats now log `trailer_bigram_rows` + `emitted_prev_count`.

| Case | wire_fzc | meta | amortized_fzc | Δ mono | bigram_hits |
|------|----------:|-----:|--------------:|-------:|------------:|
| **`mono_mi`** | **651,650** | 0 | **651,650** | 0 | 0 |
| **`mono_concat` + stat_pred_inner** (2026-06-08, **verify OK**, FZPS v4 + **FZPM v4 sparse**) | **863,835** | **212,256** | **658,348** | **+6,698** | **76,068** |
| **`mono_concat` + stat_pred_inner** (2026-06-08, superseded FZPM v2) | 902,848 | 251,269 | 659,592 | +7,942 | 76,068 |
| **`mono_concat` + stat_pred_inner** (2026-06-07d, superseded) | 904,981 | 253,402 | 659,660 | +8,010 | 76,068 |
| `mono_concat` + stat_pred_inner_sparse (BIGRAM_MAX_PREV=4096) | 904,981 | 253,402 | 659,660 | +8,010 | 76,068 |
| **`stat_pred_inner`** (mi_reorder, slice-mined, base94) | **906,543** | **252,992** | **661,619** | **+9,969** | **76,068** |
| **`stat_pred_inner_sparse4096`** (ranksUsed reinject, **verify OK @384p**) | **906,543** | **252,992** | **661,619** | **+9,969** | 76,068 |
| `stat_pred_inner_sparse` (no ranksUsed reinject; **verify fail @384p**) | 886,743 | 233,192 | 660,988 | +9,338 | 76,068 |
| `stat_pred_inner_sparse2048` (pre-fix; verify fail) | 855,970 | 202,419 | 660,006 | +8,356 | 76,068 |
| `stat_pred_inner_8k` (MAX_WORDS=8192) | 906,543 | 252,992 | 661,619 | +9,969 | 76,068 |
| `stat_pred_inner_delta` (payload codec delta) | 996,030 | 251,545 | 752,507 | +100,857 | 71,216 |
| `mono_concat` (no stat_pred; layout only) | 657,102 | 0 | 657,102 | +5,452 | 0 |
| `stat_pred_inner_frozen_file` (MODEL_FILE) | 922,345 | 268,794 | 662,123 | +10,473 | 76,068 |
| `stat_pred_inner_sparse_frozen` | 886,742 | 233,191 | 660,988 | +9,338 | 76,068 |
| `mono_concat` + stat_pred_inner (regressed batch / MODEL_FILE bleed) | 1,314,915 | 627,766 | 707,169 | +55,519 | 49,010 |

Sparse + full fractal/zpaq trailer (`INNER_FOLD_FRACTAL_FAST=0`) reconfirmed **660,988** amort — `benchmarks/logs/wire_384_sparse_zpaq_trailer.log`.

Frozen file encode matches slice-mined on wire when win-only rows align (same hits/meta). Sparse caps shipped FZPM to 4096 prev rows (−35 KiB meta, −1,135 B amort vs default) without losing bigram hits. **Caution:** a later combined probe (`wire_384_frozen_experiments.log`) regressed MODEL_FILE / sparse_frozen / mono_concat to **49,010 hits** and **384–627 KiB meta** — do not ship full 1.54 MiB corpus FZPM on 384p slice; keep slice-mined win-only path.

Legacy varint / encode-prune @384p (superseded):

| Case | wire_fzc | static_meta | amortized_fzc | Δ mono | bigram_hits |
|------|----------:|------------:|--------------:|-------:|------------:|
| `stat_pred_inner` (16k, varint, fractal+zpaq trailer) | 933,860 | 267,703 | 674,694 | +23,044 | 71,216 |
| `stat_pred_inner` (16k, fractal fast outer) | 983,907 | 317,750 | 676,290 | +24,640 | 71,216 |
| `stat_pred_inner` (16k, gzip trailer) | 1,001,031 | 334,874 | 676,836 | +25,186 | 71,216 |
| `stat_pred_inner` (16k, encode-prune) | 969,308 | 306,626 | 672,461 | +19,693 | 71,216 |

- **Compact meta lab @384p** (`bench_stat_pred_compact_meta_size.php`, 2026-06-07): `full` compact_bin=362,332 compact_gz=275,530 bigram_rows=16,225 vocab_words=16,384; `win_only` compact_bin=311,530 compact_gz=232,029 bigram_rows=14,128; `win_only_max4096` compact_bin=249,912 compact_gz=182,979 bigram_rows=4,096 — aligns with win-only FZPM prune shrinking shipped trailer meta.
- **768p stat_pred scale table** (mono_mi **1,325,096 B**, fractal trailer defaults, 2026-06-07):

| Case | wire_fzc | static_meta | amortized_fzc | Δ mono | bigram_hits |
|------|----------:|------------:|--------------:|-------:|------------:|
| **`mono_mi`** | **1,325,096** | 0 | **1,325,096** | 0 | 0 |
| **`stat_pred_inner` (16k)** | **1,835,810** | **432,126** | **1,431,246** | **+106,150** | **128,833** |

Legacy encode-prune @768p (superseded): `stat_pred_inner` amort **1,428,523** (+80,813); `stat_pred_inner_8k` **1,448,640** (+100,930); `stat_pred_inner_sparse` **1,445,914** (+98,204).

- **Payload vs mi_reorder (384p lab, `bench_stat_pred_payload_vs_mi.php`)** — varint token stream **1,793,044 B** vs raw mi **2,828,254 B** (−1,035,210); gzip-1 proxy understates zpaq: **base94** wire **922,327 B** / amort **662,123 B** (+10,473 vs mono) beats varint **933,860** / **674,694** (+23,044) by **~12.5 KiB** amortized despite larger raw payload. Sparse lab (`payload_vs_mi_sparse.log`, `BIGRAM_MAX_PREV=4096`): base94 payload **2,519,311 B**, gzip-1 **1,090,893 B**, hits **76,186** — token stream smaller than raw mi; wire gap is meta + zpaq, not payload size. **`stat_pred_inner` wire default (2026-06-07):** `FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=base94` when unset; override `varint` for legacy. FZPS `\x03` pool stores scheme for roundtrip. Wire case `split_inner_fztx_mono_mi_stat_pred_inner_sparse_zpaq_trailer` (`INNER_FOLD_FRACTAL_FAST=0`) reconfirmed **660,988** amort — gzip trailer wins over full fractal+zpaq on FZIF.
- **Byte anatomy @384p sparse** (`stat_pred_inner` base94, `BIGRAM_MAX_PREV=4096`): wire **886,742** = member+outer **653,551** (+**1,901** vs `mono_mi` **651,650**) + trailer meta **233,191**. With **ranksUsed succ-tail trim**: meta **222,948** (−10,243 B). **`mono_concat` layout**: member+outer **651,579** (−71 B vs mono), meta **253,402**, amort **659,660** (+8,010) — **best verify-safe stat_pred @384p** (`verify_384_mono_concat_stat_pred.log`).
- **Localized prediction lab** (`bench_stat_pred_localized.php`, 384p base94): **global 16k** — payload **2,519,311**, hits **76,186**, fzpm_gz **253,803**; **local 3×4096w@128p** — payload **2,783,034** (+264 KiB), hits **62,660**, fzpm_gz **200,373** (−53 KiB ship). Per-chunk vocab **shrinks trailer** but **loses cross-chunk bigram coverage** — net regresses. Fractal wire path needs **shared backbone + regional bigram extensions** (FZPM v2 multi-shard), not independent isolated dicts per chunk.
- **Parallel probes:** `bash benchmarks/run_stat_pred_parallel_probes.sh` — 384p default+mono, 768p base94, 384p verify in parallel. Wire scripts call `bench_wire_probe_apply_parallel_speed_env()` (zpaq threads, text-inner build forks, GPU/Rust substring); `FRACTAL_ZIP_PIPELINE_PARALLEL=0` preserves byte accounting. Host detect: NVIDIA+Numba → `FZ_GPU_SUBSTRING_MODE=auto` + `FZ_GPU_SUBSTRING_PREFER_CUDA=1`; else Rust/CPU parallel (AMD/CPU-only).

- **Note:** Trailer shrink helps ship cost (**370,334 → 306,626**) but encode-side prune was the regression source for 8k amortized; trailer-only prune keeps full encode model while shrinking shipped meta.

- **Legacy stat_pred variation matrix @384p** (2026-06-07 pre-prune / sidecar paths; superseded for `stat_pred_inner` promotion decisions):

| Case | wire_fzc | static_meta | amortized_fzc | Δ mono | bigram_hits |
|------|----------:|------------:|--------------:|-------:|------------:|
| `stat_pred_no_sidecar` / `cap16384` | 973,159 | 404,207 | **581,843** | **−69,807** | 71,216 |
| `stat_pred_compact_meta` | 950,617 | 346,061 | 615,592 | −36,058 | 71,216 |
| `dict_inner` | 748,615 | 133,611 | 619,265 | −32,385 | 0 |
| `dict_phda9_inner` | 748,629 | 133,611 | 619,279 | −32,371 | 0 |
| `stat_pred_cap8192` | 913,868 | 288,716 | 634,359 | −17,291 | 60,631 |
| ~~`stat_pred_inner` (pre-prune)~~ | ~~910,497~~ | ~~268,203~~ | ~~650,847~~ | ~~−803~~ | 60,631 |
| `shootout_phda9` | 652,504 | 0 | 652,504 | +854 | 0 |
| `stat_pred_cap4096` | 866,080 | 210,997 | 661,812 | +10,162 | 48,507 |
| `stat_pred` (+ FZEP stat sidecar) | 1,077,362 | 404,207 | 686,046 | +34,396 | 71,216 |

- **dict_phda9_inner** @384p: wire **748,629 B**; fold **133,611 B**; amortized **619,279 B**; phda9-scale vocab mines slice text (4699 words @32p). Roundtrip **MISMATCH** @32p (pre-existing codec gap) — compression probe OK; fix restore before promotion.
- **phda9 member shootout** @384p: wire **652,504 B** (+854 vs mono_mi, **262.97 s**); phda9 **core-dumped** on 384p mono chunk; shootout picked **`stack:zpaq9_brotli11`** inner **647,886 B**; outer **zstd**. Lab-only — not promoted.

**768p probe refresh (2026-06-06):** `bench_enwik8_wire_slice_probe.php --pages=768 --cases=entry_sort_off_text_inner_mi,split_inner_fztx_mono_mi,no_textcodec` → `no_textcodec` **1,338,063 B**, `entry_sort_off_text_inner_mi` **1,331,052 B** (−7,011), `split_inner_fztx_mono_mi` **1,325,096 B** (−12,967); **mono_mi beats entry-sort-off** by 5,956 B at 768p (gap widens vs 384p).

**384p probe refresh (2026-06-06):** same script `--pages=384 --cases=split_inner_fztx_mono_mi,entry_sort_off_text_inner_mi,split_inner_fztx_mi_stack,no_textcodec` → baseline **658,258 B**, mono_mi **651,650 B** (−6,608), entry_sort_off **653,192 B** (−5,066), pre-stack **717,028 B** (+58,770, outer **zstd**).

**384p layout interleave probe (2026-06-06):** `--cases=split_inner_fztx_mono_mi,split_inner_fztx_mono_sort_len,split_inner_fztx_mono_mi_line_stripe` → mono_mi **651,650 B** (gate ref; need ≤650,538 for full_encode_allowed); sort_len **657,890 B** (+6,240); mi_line_stripe **699,662 B** (+48,012). No case beats mono_mi; gate **112 B short** unchanged.

**pp96/world-record default (2026-06-06):** `bench_world_record_apply_pp96_core_env()` now sets text-inner promotion env (fztx mono mi_reorder, stack none). Override with `FRACTAL_ZIP_TEXT_INNER=0` before preset apply.

**FZSO stacked inner (2026-06-06, passthrough fix):** Native 7z wire shootout no longer steals stacked path (`fractal_zip_stacked_outer_skip_native_wire_compares`). Measured @384p: **mono_mi 651,650 B** (zpaq outer) still best; **stack+zpaq outer** (`STACK=zpaq9_zstd22`, `STACKED_OUTER=0`) **652,331 B** (+681); **stack+brotli outer** (`STACK=zpaq9_brotli11`, `STACKED_OUTER=0`, case `split_inner_fztx_mono_mi_stack_brotli_outer`) **652,500 B** (+850, outer **zstd**); **stack passthrough** (`STACKED_OUTER=1`) **661,649 B** (+9,999). **Why zstd not zpaq on stack case:** `STACKED_OUTER=0` disables FZSO store-passthrough (`fractal_zip_text_compressor_external.php` ~334–363); inner is already `zpaq9→brotli11` inside `FZSO\x01` (`fractal_zip_text_stacked_outer_apply` ~391–421); `adaptive_compress` re-tournaments gzip/zstd/zpaq/7z/arc on that high-entropy blob and **zstd wins** (mono_mi leaves raw mi_reorder FZTX text where **zpaq m9** wins). Re-probe `FRACTAL_ZIP_FORCE_OUTER=zpaq` on stack+brotli case → **653,605 B** (worse than adaptive zstd **652,500**). Honest FZSO inner on wire still regresses vs `STACK=none` — keep `STACK=none` on promotion path; lab **647,824 B** edge does not survive wire (~4.7 KiB wire tax vs lab).

**Post-stack Hutter gap (~4.0 MiB vs phda9):** Full wire best **19,207,083 B** vs Hutter **15,284,944 B** (~**3.92 MiB**). FZSO / outer stacks / shootouts are **exhausted on wire** (+850 B minimum). Next levers (modeling + order, not another outer codec):

1. **Integrated statistical inner** — teach sorted/unified path PAQ-class modeling (`docs/ENWIK8_INTEGRATED_COMPRESSION.md`: harmony boilerplate/siteinfo, member-level models) without FZpq whole-file wrap.
2. **Inner-first caps** — relax `RECURSIVE_ONLY`, run-grammar/peeler, FZBM path-order budget on the ~100 MiB unified inner (`benchmarks/run_enwik8_inner_experiment_grid.sh`); outer zpaq already optimal on raw concat.
3. **Lossless transforms that shrink pre-zpaq inner** — mi_reorder won (−387 KiB full); hunt transforms with **negative sidecar/meta tax** (stat_pred/isp failed); corpus phrases / entry_sort_off anti-scale on full wire.
4. **pages_per_member / virtual-member topology** — pp96 grid winner; re-grid with text-inner mono (3 members @384p probe vs ~129 @pp96 full) for scale interaction.
5. **Reference-only ceiling** — raw-order phda9 ~15.01 MiB (`ENWIK8_GRID_CONCLUSIONS.md`) confirms gap is **byte order + English model**, not zpaq method tuning.

**Beat-15M fast gate:** `bash benchmarks/run_enwik8_beat15m_fast_gate.sh` — see `docs/BEAT_15M_FAST_GATE.md`. **Verdict unchanged (2026-06-08):** `full_encode_allowed=false`; **stat_pred gate NOT cleared** — best verify-safe **659,592 B** amort (+**7,942** vs `mono_mi` **651,650**). Gap anatomy: payload win −71 B (mono_concat member+outer) vs meta amort tax ~**7,871 B** (251,269 × 384/12041). FZPS v4 front-coded pool −**27.8 KiB** raw sidecars but only −**2.4 KiB** sealed meta on wire. Next levers: bigram-table shrink without verify break; member pre-zpaq win beyond −71 B; do **not** promote `mono_concat` without stat_pred (+5.4 KiB regression).

**Encode status (mono_mi, 2026-06-06):** `test_files109.fz` **19,207,083 B**; **`verify_ok=1`**. Baseline pp96 refresh: **19,594,340 B** (−**387,257 B**, ~1.98% smaller).

**phda9_xml promotion (2026-06-14, verified):** integrated sorted page XML + `phda9_no_lstm` FZPA members (not FZTX wrap, not per-member shootout). **Full wire** **18,848,115 B** (−**358,968 B** vs mono_mi **19,207,083**; outer **zstd**); encode **14,349 s** (~4.0 h, jobs=2 RAM-capped); verify **6,534 s** (~109 min, 129 phda9 decompressions); **`verify_ok=1`** sha256 OK. **@384p gate:** wire **560,430 B** (−**91,220** vs mono_mi **651,650**); **@384p RT:** **560,286 B**, `roundtrip=ok`. **pp48 @384p rejected:** **579,231 B** (+18,801). **dict @96p lab:** no byte gain. **Runner:** `php -d memory_limit=2048M benchmarks/run_enwik8_phda9_xml_encode.php --encode-only`. **Production env:** `bench_world_record_apply_phda9_xml_promotion_env()` (default in `bench_world_record_apply_pp96_core_env()`). Artifacts: `.enwik8_exp_phda9_xml.json`, `benchmarks/logs/full_encode_phda9_xml.log`, `benchmarks/logs/verify_phda9_xml_full_r2.log`, `benchmarks/logs/wire_384p_phda9_roundtrip.log`. Gap vs Hutter ref **15,010,414 B** remains ~**3.84 MiB**.

**phda9_xml single-stream hybrid (2026-06-15, verified @384p RT):** `FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1` — one phda9 context over all sorted page XML (15M-squash-style continuity + phda9_xml integrated FZEP restore). **@384p:** pp96 parallel **560,162 B** vs single-stream **524,040 B** (−**36,122 B**, ~6.5%); inner phda9 **556,139 B** (4×) → **520,120 B** (1×); encode **1117 s → 284 s**; **`roundtrip_ok=1`** both. **@8p ppm=4:** 2-chunk **15,613 B** vs 1-chunk **15,450 B** (−163). **Gate:** `run_enwik8_phda9_xml_encode.php --gate-check` → extrap **16,209,697 B** (−**2.64 MiB** vs **18,848,115**); `allow_full_encode=true`. **Dual-order** (raw squash **15,010,414 B** FZpq body + FZEP) still best bytes when `.enwik8_paq_squash.fzpq` cache exists — export via `bench_enwik8_paq_export_wire.php`. **Hybrid compare:** `php benchmarks/run_enwik8_phda9_hybrid_compare.php --pages=384 --wire-only --roundtrip`. **Next:** full single-stream encode; then `phda9` (LSTM) on single-stream.

**Recommendation:** **phda9_xml single-stream** is the new production candidate (default in `bench_world_record_apply_phda9_xml_promotion_env()`). pp96 parallel remains fallback (`FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=0`). mono_mi fztx remains **lab baseline** for A/B probes. Ranks 2–4 remain **lab-only**; **drop** rank-5 and explicit-drop list for production wire.

**Beat-15M integrated path probe (2026-06-10)** — `php benchmarks/run_enwik8_beat15m_path.php --pages=384` → `.enwik8_beat15m_path.json`. **Chosen path:** `mono_mi` text-inner (not `stat_pred_inner`). **Candidate rejected:** `mono_mi` + `CORPUS_PHRASES=1` — **@384p** **651,668** (**+18**). **stat_pred_inner @384p:** wire **863,921** / meta **212,342** / member **651,579** (−71) / amort **658,351** (**+6,701** vs mono_mi). **stat_pred_inner @768p (scale check):** wire **1,716,553** / meta **330,723** / member **1,385,830** (**+60,734** vs mono **1,325,096**) / amort **1,406,924** (**+81,828**). Member win at 384p **flips to loss** at 768p — stat_pred **anti-scales**. **Full mono_mi:** **19,207,083 B** verified (gap **3.92 MiB** vs Hutter). **stat_pred full projection (fixed, not linear wire delta):** amort ≈ **19,213,784** (+67 KiB vs mono); honest wire ≈ **19,419,354** (+212 KiB sealed FZPM meta). **Gate:** BLOCK. **Only sub-15M in repo:** phda9 reference **15,010,414 B**. Next integrated modeling must shrink **pre-zpaq bytes without a separate sealed FZPM trailer** (payload-only stat pred).

**Continuation (2026-06-11):** **stat_pred_inner verify @384p OK** — `fzc=863,922` bit-exact restore (`INNER_FOLD_FRACTAL_FAST=0`). **Fast gate JSON** now records **stat_pred_768p** amort **+81,828**. Fresh **`stat_pred_no_sidecar` @384p** (mi_reorder, FAST=0): wire **974,674** / meta **405,544** / member **569,130** / amort **582,063** (**−69,587** amort vs mono_mi but **+323,024** honest wire) — amort win is accounting-only; **not promotable** on wire. Production: keep **mono_mi** @ **19,207,083 B**.

**`STAT_PRED_INNER_MEMBER_FOLD=1` @384p (payload-only FZPM in FZSP member, no FZEP trailer)** — wire **870,402** / meta **71,013** (sidecar JSON only) / member **799,389** / amort **801,654** (**+149,004** vs mono_mi **651,650**; **+143,303** vs trailer-path amort **658,351**). FZPM raw **287,956 B** folded into zpaq member **inflates** member+outer by **~148 KiB** vs trailer path (**651,579**) despite **−141 KiB** sealed-meta savings — cross-compression does not win; **dead end**. **verify OK** (`fzc=870,402`). Trailer path (`mono_concat` + FZEP inner-fold) remains best stat_pred packaging.

**phda9_inner + mono_concat @384p reconfirm (2026-06-11, FAST=0)** — `mono_mi` **651,650**; `mono_concat` (layout only) **657,102** (**+5,452**); `dict_phda9_inner` **733,636** / meta **58,990** / amort **676,527** (**+81,986**); `dict_phda9_inner_parallel_paq` **733,636** (same wire, +21s encode). phda9 inner-fold **dead** on wire; parallel_paq adds no bytes. Text-inner preprocess lane (stat_pred, dict, phda9, member_fold) **exhausted** — beat-15M needs unified-stream / harmony modeling, not more FZTX preprocess.

**parallel_paq member wrap @384p (2026-06-11)** — `bash benchmarks/run_enwik8_parallel_paq_probe.sh` → `.enwik8_parallel_paq_probe.json`. Tools built OK (`smoke_parallel_paq`). Raw slice (2.97 MiB sorted text): **gzip9 594,847**; **parallel_cmix 2,965,381** (99.8% of raw — expands); **parallel_phda9+dict 2,369,078** (79.7%). Integrated wire: `mono_mi` + `parallel_cmix` / `parallel_phda9` **651,650** (tie — gate skips FZPA when wire ≥ gzip(inner)); `FORCE=1` **regresses** to **2,974,955** / **2,348,474**. `stat_pred_inner` + `parallel_cmix` **863,922** / amort **658,351** (unchanged — wrap not taken on tokenized inner). **Conclusion:** parallel_paq is **speed/lab only** on enwik8 wire; use `bench_wire_probe_apply_parallel_speed_env()` for faster probes, `bench_world_record_apply_parallel_production_env()` for full encode wall time — bytes unchanged.

**PIPELINE_PARALLEL production @384p (2026-06-11)** — `bash benchmarks/run_enwik8_parallel_production_probe.sh` → `.enwik8_parallel_production_probe.json`. `split_inner_fztx_mono_mi` vs `split_inner_fztx_mono_mi_parallel` (**651,650 B** both, **verify-equivalent wire**); encode **97.5s → 57.4s** (~**41%** faster). Full encode: `php benchmarks/run_enwik8_pp96_refresh.php --text-inner-promotion --parallel-production` (bytes unchanged vs serial).

**Harmony / siteinfo @384p (2026-06-11)** — `bash benchmarks/run_enwik8_harmony_unified_probe.sh` (wire leg) → `.enwik8_harmony_wire_probe.json`. `mono_mi` **651,650**; `split_inner_fztx_mono_mi_siteinfo` / `split_inner_fztx_mono_mi_harmony` **651,764** (**+114** — near full-corpus parity +116 B); `pp96_siteinfo_no_textinner` **658,374** (**+6,724** — siteinfo without text-inner regresses). Stale combo-probe **+151 KiB** siteinfo row superseded — fresh probe with `SITEINFO_PACK=1` only is **+114 B** on text-inner. **Do not promote** — still loses vs mono_mi @384p gate.

**Inner unified 8 MiB rerun (2026-06-11)** — `bench_enwik8_inner_unified_probe.php` → `.enwik8_inner_unified_probe.json`. All six cases **1,769,220 B** (baseline, combo, recursive0, fzbm2048, zpaq4, staged0) — **zero wire delta** at slice scale; `probe_staged0` fastest (**134s** vs **257s** baseline). Confirms inner-cap toggles do not beat raw+zpaq m9 on unified stream @8 MiB; full 100 MiB + text-inner path already won at **19,207,083 B**.

**Remaining levers @384p (2026-06-11)** — `bash benchmarks/run_enwik8_remaining_levers_probe.sh` → `.enwik8_remaining_levers_probe.json` (`INNER_FOLD_FRACTAL_FAST=0`). `mono_mi` **651,797** (gate ref **651,650** in prior batch — **+147 B** run variance); `mono_mi_corpus` **651,795** (**−2** vs same-run mono, **+145** vs gate ref — noise); `split_text_inner_mi_semantic` **651,797** (tie); `mono_mi_text_pack` **652,686** (**+889**); `entry_sort_off_text_inner_mi` **653,208** (**+1,411** with `ENTRY_SORT=1` promotion path). **Gate BLOCK** @384p — no case clears **651,650** gate; corpus **−2 B** is below 32 KiB extrapolation margin. **Do not promote** text_pack, semantic, or entry_sort_off.

**corpus_phrases 768p scale check (2026-06-11)** — `bench_enwik8_wire_slice_probe.php --pages=768 --cases=split_inner_fztx_mono_mi,split_inner_fztx_mono_mi_corpus` → `mono_mi` **1,325,096**; `mono_mi_corpus` **1,323,338** (**−1,758** — win **widens** vs 384p **−2 B**, opposite of stat_pred anti-scale). `run_enwik8_beat15m_path.php --pages=768` est_full_amort ≈ **19,205,325** (**−1,758** vs mono **19,207,083**). **Gate still BLOCK** (extrapolation margin **< 32 KiB**); corpus is **watch-only** — not promoted until 384p gate ref cleared or full encode confirms **≥ ~30 KiB** savings. Prior 384p row **651,668 (+18)** superseded by fresh **651,795 (−2)** same-run batch.

**Continuation (2026-06-11 beat-15M research):**

| Case | @384p wire | @768p wire | Δ mono | Verdict |
|------|----------:|----------:|-------:|---------|
| **`mono_mi`** (gate ref, `FORCE_OUTER=zpaq`, FAST=0) | **651,650** | **1,325,096** | 0 | baseline |
| **`mono_mi_corpus`** (default mine params) | **651,668** (**+18**) | **1,323,338** (**−1,758**) | mixed | **watch-only** — anti-scales at 384p gate; 768p win **< 32 KiB** extrap margin |
| **`mono_mi_corpus`** (`MIN_COUNT=8`, `MAX=512`) | **652,075** (**+425**) | — | +425 | **dead** — looser mining regresses 384p |
| **`mono_mi_corpus`** (`MIN_LEN=8`, `MAX_LEN=160`, defaults count/max) | **651,668** (**+18**) | — | +18 | tie noise — same as defaults |
| **`mono_mi_wrt`** (`PREPROCESS=wrt_xwrt`) | **750,568** (**+98,918**) | — | +98,918 | **dead** |
| **`mono_mi_stat_wrt`** (`stat_wrt` + sidecar) | **804,189** (**+152,539**) | — | +152,539 | **dead** |
| **`mono_mi_stat_sidecar`** (`STAT_SIDECAR=1`, `PREPROCESS=none`) | **802,685** (**+151,035**) | — | +151,035 | **dead** — FZEP v6 word-freq sidecar regresses wire |
| **`mono_mi_corpus`** (no `FORCE_OUTER`; adaptive outer) | **712,505** (outer **arc**) | — | +60,855 | **arc trap** — corpus inner can lose zpaq tournament; use zpaq for fair wire compare |

**Corpus phrase param lab** — `bench_enwik8_corpus_phrases_param_sweep.php` (text-body mine, gzip-1 proxy): @384p top **9 phrases**, gzip1Δ **630 B**, net **426 B** after dict; @768p top **22 phrases**, gzip1Δ **1,871 B**, net **1,460 B**. Grid (min_count × max_entries × len) **flat** — only **9–22** phrases pass min_count on slice; tuning knobs do not widen mine without 384p regression. JSON: `.enwik8_corpus_phrases_param_sweep_384p.json`, `.enwik8_corpus_phrases_param_sweep_768p.json`.

**Beat-15M path (2026-06-11):** `run_enwik8_beat15m_path.php` @384p → **BLOCK** (corpus **+18 B**); @768p → est_full_amort **19,205,325** (**−1,758 B** vs mono) but gate **BLOCK** (< 32 KiB). **Recommendation:** keep **mono_mi** @ **19,207,083 B**; corpus remains watch-only until 384p clears **651,650** or full encode ≥ **~30 KiB**. Next levers: **pre-zpaq English modeling inside unified stream** (not FZPM/sidecar); phrase mine ceiling (~22 phrases @768p) likely exhausted — need member-level or cross-page statistical hooks without sealed meta.

**Text-inner pre-zpaq anatomy @384p (2026-06-11)** — `bench_enwik8_text_inner_pre_outer.php` → `.enwik8_text_inner_pre_outer.json`. Slice **raw_xml 2,972,258 B** → **inner 2,982,488 B** (+10,230 shell/meta) → **gzip-1 1,236,716 B** → **wire 651,650 B** (outer **zpaq**, 166 s). Integrated slice **~1.75 bpc** (651,650×8/2,972,258); full wire **~1.54 bpc** vs Hutter **~1.22 bpc**. Inner ≈ raw — beat-15M needs **stronger English model on sorted FZTX bytes** (not more layout/preprocess packaging).

**Member codec lab + PAQ fixes (2026-06-12)** — `fractal_zip_paq.php`: **paq8px** decompress now uses **`-d`** (v215 requires switch; was silently failing RT). **phda9** C-mode archives decompress via **`phda9 D`** (not `phda9dec`; fixes RT on plain English). Shootout default timeout **120→600 s** (`FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC`); paq8px on ~220 KiB inner needs **~100–190 s**/chunk.

**Isolated codec ceiling @96p** (`bench_enwik8_paq_on_inner_quick.php`) — mono_mi FZTX inner **223,877 B**; **zpaq m9 54,350**; **paq8px 46,411** (**−7,939**, **rt=ok**); **phda9 on FZTX inner** core-dump; **phda9 on raw_sorted_text 185,341 B → 39,221** (**−15,129**, **rt=ok** after D-fix). cmix unavailable.

**Wire @96p — paq8px shootout BREAKTHROUGH** — `split_inner_fztx_mono_mi_shootout_paq8px` (MODELS=paq8px, STACKS=none, TIMEOUT=900): shootout picks **`model:paq8px` 46,294 B** chunk inner → wire **48,715 B** vs mono_mi **56,259 B** (**−7,544 B**, **−13.4%**). First integrated path that beats mono_mi on honest wire at slice scale. Encode **108 s** vs **16 s** mono.

**Wire @384p — paq8px shootout GATE CLEARED (2026-06-12, single mono chunk)** — wire **558,414 B** (**−93,236 B**). **Full encode failed to extrapolate** (+522 B): `mono_inner` forced `pagesPerMember=pageCount` → one ~100 MB chunk.

**Root cause + fix (2026-06-13)** — `fractal_zip_enwik_text_inner_mono_pages_per_member()`: when shootout on and `FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=96` &lt; pageCount, mono fztx splits into ppm-sized chunks. Production mono_mi unchanged (no shootout).

**Wire @384p — paq8px + ppm96 (4 chunks, jobs=4, level 6)** — wire **613,985 B** vs mono **651,650 B** (**−37,665 B**). Gate cleared. **Verify @384p (contended):** fzc **661,059 B** (3/4 chunks fell back to `raw` — ran alongside full-encode paq pool); restore **OK**. Solo re-verify after full encode.

**Full encode ppm96 (2026-06-13, in flight)** — `member shootout parallel: **129 chunks**, jobs=4`; log `full_encode_paq8px_ppm96.log`. Prior single-chunk full run: **19,207,605 B** (+522 vs mono).

**Wire @96p — phda9:zpaq9 shootout (prior)** — phda9 cores on FZTX → fell through to **stack:zpaq9_zstd22** → wire **56,395** (**+136** vs mono **56,259**); gzip-1 proxy scoring + stack env bleed when models fail.

**stat_pred gap anatomy @384p (2026-06-12)** — comparative probe → `.enwik8_stat_pred_gap_anatomy.json`. **`mono_mi`:** inner **2,982,488** → wire **651,650**. **`mono_concat` + `stat_pred_inner`:** inner **2,663,851** (**−318,637** vs mono inner) / gzip-1 **1,096,632** (**−140,084**) → member+outer **651,579** (**−71** only!) + meta **212,343** → wire **863,922** / amort **658,351** (**+6,701**). **`stat_pred_inner` on `mi_reorder`:** wire **865,745** / member+outer **653,551** (**+1,901** vs mono — worse than mono_concat path). **`mono_concat` layout only:** inner **2,981,055** / wire **657,102** (**+5,452** vs mono_mi). **Codec ceiling** (`bench_enwik8_text_inner_codec_ceiling.php`) — isolated **zpaq m9** on inner blob: mono_mi **648,049**; stat_pred **666,014** (**+17,965** despite **−319 KiB** payload!); mono_concat **664,967**. **phda9** n/a @384p slice (timeout/fail). **Diagnosis:** rank tokens **hurt zpaq**, not just fail to help; gzip-1 shrink is a false proxy. Beat-15M needs transforms where **zpaq m9 bytes drop** on sorted English (phda9-class modeling integrated into member codec path), not FZTX rank sidecars.

**Artifacts (continuation):** `.enwik8_member_codec_lab.json`, `benchmarks/logs/paq_on_inner_96p_rtfix.log`, `benchmarks/logs/wire_96p_paq8px_shootout_v2.log`, `benchmarks/logs/wire_384p_paq8px_shootout.log`, `.enwik8_text_inner_codec_ceiling.json`, `.enwik8_text_inner_pre_outer_mono_concat.json`, `.enwik8_stat_pred_gap_anatomy.json`, `.enwik8_text_inner_pre_outer_stat_pred.json`, `.enwik8_corpus_tune_probe.json`, `.enwik8_wire_slice_probe_768p_corpus.json`, `.enwik8_unprobed_mono_mi_384p.json`, `.enwik8_stat_sidecar_384p.json`, `.enwik8_text_inner_pre_outer.json`, `.enwik8_beat15m_path.json`, `benchmarks/logs/payload_vs_mi_384p.log`, `benchmarks/logs/trailer_anatomy_384p.log`, `benchmarks/logs/pre_outer_stat_pred_384p.log`, `benchmarks/logs/corpus_tune_probe.log`, `benchmarks/logs/unprobed_mono_mi_384p.log`, `benchmarks/logs/stat_sidecar_384p.log`, `benchmarks/logs/text_inner_pre_outer_384p.log`.

**Unprobed mono_mi cases @384p (2026-06-11 continuation)** — `bench_enwik8_wire_slice_probe.php` → `.enwik8_unprobed_mono_mi_384p.json` (`FAST=0`). `mono_mi` **651,650**; `fzbm2048` **651,650** (tie); `inner_combo` **651,650** (tie); `wrt_xwrt` **750,568** (**+98,918**); `stat_wrt` **804,189** (**+152,539**). **Dead:** wrt, stat_wrt. **No win:** fzbm2048, inner_combo.

**corpus phrase env sweep @384p (2026-06-11 continuation)** — `bash benchmarks/run_enwik8_corpus_tune_probe.sh` (`INNER_FOLD_FRACTAL_FAST=0`) → `.enwik8_corpus_tune_probe.json`. Best combo **defaults** (651,668, **+18** vs mono **651,650** gate ref). `max512_min8` **+425**; `long_phrases` (MIN_LEN=8, MAX_LEN=160 only) **+18**; `full_blob` / `aggressive` **+5,906** (657,556). **Do not** leave `FRACTAL_ZIP_ENWIK_CORPUS_PHRASES_FULL_BLOB=1` exported in shell — causes **8,198 B** false wire + broken page refs. **768p** (defaults, clean env): mono **1,325,096**, corpus **1,323,338** (**−1,758**); `verify_enwik_slice_roundtrip --pages=768 --corpus-phrases` **OK** (fzc **1,323,338**). Gate **BLOCK** (< 32 KiB extrap). Keep **mono_mi** on wire.

### Unified stream: why raw ~100 MB wins (2026-06-05)

1. **Entry path** — `fractal_zip_enwik_try_prepare_virtual_folder` (`fractal_zip_enwik.php` ~1251) sorts pages → ~129 virtual members; unified stream merges them.
2. **Variant collection** — `encode_folder_unified_stream_wire_in_memory` (`fractal_zip.php` ~15984–16005) gathers peeler/run-grammar + `collect_literal_bundle_inner_variants_for_folder` (~7810). When `substring_multidiff_recursive_only_enabled()` (~7859), **FZBM merged** and **FZBF merged fractal** are skipped.
3. **Pick logic** — `choose_smallest_adaptive_literal_inner_or_raw_escaped` (~7132): for enwik8 sumRaw ≥ `staged_literal_fast_outer_min_raw_bytes` (16 MiB default, ~1959), **staged fast outer** runs (~7523–7754). Raw escaped concat → `adaptive_compress` (zpaq method 9) beats every FZB/FZBM literal on **wire bytes** after full rescoring (~7663–7748).
4. **Result** — pre-zpaq inner ≈ raw concat (~100,000,187 B); zpaq payload ≈ 19.54 MiB (`.enwik8_integrated_preprocess_probe.json`). Fractal/literal inners add overhead without shrinking what zpaq sees enough to win.


**Multishift cross-page alignment transform (2026-06-15)** — new deterministic transform `fractal_zip_multishift.php` (shift-vote via anchor offset-delta mode → similarity grouping/beam → per-group residual: delta-from-reference run-stream or column-transpose, picked by gzip-1 proxy → compact reversible **FZMS** sidecar). Registered as layouts `multishift` / `multishift_transpose` in `text_layout_catalog()`; apply/undo modeled on `mi_line_stripe` (lab/tournament layer — like `mi_line_stripe` it is **not** wired to the FZTX full-wire restore). RT-verified byte-exact: `tests/multishift_shift_vote_smoke.php` + `tests/multishift_layout_roundtrip_smoke.php` (synthetic + real enwik 8/32/64). **Slice gate (`bench_multishift_slice.php`, 8/32/96p, codecs gz9/zstd19/zstd_w15/xz9):** every multishift arm **FAILS the net-bytes gate** under the LTCB rule (sidecar counted) and is ≈break-even-to-worse even payload-only; plain `mi_reorder` (permutation, zero sidecar) wins. e.g. @96p zstd19: mono baseline 64,906 B; `mi_reorder` LTCBnet **+66**; `multishift` **−381**; `multishift_transpose` **−716**. Same verdict under the window-limited `zstd_w15` (32 KiB) beyond-window emulation. **Diagnosis (as anticipated in the plan's failure-modes):** LZ/CM-class codecs are offset-independent and already capture cross-page repetition inside their window, so rigid-shift co-location adds only sidecar overhead; the residual also disrupts byte context (transpose worst). **Decision:** GATE BLOCK — no large run justified. Transform retained as a scored, reversible tournament option; its genuine niche is position-sensitive / order-0-PPM consumers or true beyond-window (enwik9 at small effective window), not enwik8 under LZ/CM. Artifacts: `fractal_zip_multishift.php`, `tests/multishift_*`, `benchmarks/bench_multishift_slice.php`.

**Bioinformatics module suite (2026-06-15)** — full deterministic bioinformatics stack for fractal_zip, including HMM/probabilistic **lossy** compression:

| Module | Concepts |
|--------|----------|
| `fractal_zip_bioinformatics.php` | MinHash (Mash), minimizers (minimap2), CD-HIT clustering, UPGMA guide tree, suffix array + MEMs (MUMmer), spaced seeds (PatternHunter), colinear anchor chaining, banded Needleman–Wunsch, VCF variants, POA consensus, synteny blocks, pangenome variation graph (vg-style) |
| `fractal_zip_bio_align.php` | CD-HIT-clustered piecewise chained multishift, progressive/synteny/pangenome layouts, MinHash reorder |
| `fractal_zip_bio_lossy.php` | Profile HMM + bigram/trigram Markov predictors + PWM weights; lossy transforms `bio_lossy_{bigram,trigram,hmm}`; optional `FRACTAL_ZIP_BIO_LOSSY=1` bridge in `fractal_zip_ooxml_lossy.php` |

**Layouts** (in `text_layout_catalog`): `bio_minhash_reorder`, `bio_piecewise_shift`, `bio_progressive`, `bio_synteny`, `bio_pangenome`, `bio_minhash_piecewise` (MinHash reorder → piecewise align). **Transforms**: `bio_consensus`, `bio_lossy_bigram`, `bio_lossy_trigram`, `bio_lossy_hmm`. RT-verified: `tests/bioinformatics_roundtrip_smoke.php`. Harnesses: `benchmarks/bench_bioinformatics_suite.php` (@16p enwik: all layouts RT=ok; payload-only `bio_pangenome` best gzip-9 net +100 B vs mono), `benchmarks/bench_bio_slice.php` (8/32/96p gate vs mono/mi_reorder/multishift), `benchmarks/bench_bio_niche.php` (synthetic versioned-document regime boundary).

**Bio slice gate (`bench_bio_slice.php`, 8/32/96p, codecs gz9/zstd19/zstd_w15/xz9):** all layouts RT=ok after CD-HIT length-ratio guard (0.75) + VCF roundtrip verification with raw fallback. **Best bio arm is always `bio_minhash_reorder`** (zero sidecar, permutation-only) — marginal ±0–2.6 B/page, comparable to `mi_reorder`. **Alignment layouts (`bio_piecewise_shift`, `bio_progressive`, `bio_synteny`, `bio_minhash_piecewise`) FAIL the net-bytes gate** — payload inflates ~1–2 KB @32p and ~2 KB @96p under alignment+VCF overhead (disambiguation/redirect stubs resist clustering). **`bio_pangenome` FAILS catastrophically under LTCB** (JSON graph sidecar 62–222 KB ≫ payload savings) despite small payload-only wins. e.g. @96p zstd19: mono 64,906; `mi_reorder` LTCBnet **+66**; `bio_minhash_reorder` **+17**; `bio_piecewise_shift` **−2,074**; `bio_pangenome` **−222,277**. **Decision:** GATE BLOCK for alignment/pangenome arms on enwik8 under LZ/CM; retain `bio_minhash_reorder` as zero-sidecar tournament permutation. Lossy models store correction residuals only when predictor confidence exceeds threshold — approximate restore at decode.

**Bio niche boundary CONFIRMED (2026-06-16)** — `bench_bio_niche.php`. Synthetic versioned-document regime (one real 3.7 KB enwik article × 40 variants @ ~2% edits = 149 KB; RT byte-exact). **Regime split mirrors multishift:** under **`zstd_w10`** (beyond-window): `multishift_delta` **+40,838 B**; **`bio_piecewise_shift` / `bio_minhash_piecewise` +40,696 B** (after rigid-delta + shifted-VCF fix, 2026-06-16); `bio_pangenome` **+40,427 B** (outboard+compact `FZBI\x04`: payload 41,205 B + sidecar **554 B**; was 712 B before compact paths). Under **large-window LZ/CM** (`zstd_w27`): all alignment arms lose (`bio_piecewise` **−5,226**, `multishift_delta` **−5,282**). **Fixes (2026-06-16):** piecewise tries rigid shift+delta (kind 4) before colinear chains; shifted VCF via MEM pair-shift; raw fallback uses kind 3 not kind 0; pangenome **outboard payloads** (`FZBI\x03` — deltas/VCF in compressible blob, graph sidecar metadata-only ~712 B); pangenome undo no longer falls back to input pages; **multidiff colinear chain seeds** optional via `FRACTAL_ZIP_BIO_MULTIDIFF_CHAIN=1` (`fractal_zip_bio_multidiff_self_chain_seeds` in `all_substrings_count_multidiff_candidates`; harness `benchmarks/bench_bio_multidiff_chain.php` — @312 KiB enwik text: 24 chain seeds merged, final verified count unchanged at 12). **Real enwik8 vs synthetic:** heterogeneous pages still resist clustering — retain `bio_minhash_reorder` for production; alignment/pangenome for versioned-document niche only. Artifact: `benchmarks/bench_bio_niche.php`.

**Multishift niche boundary CONFIRMED (2026-06-15)** — `bench_multishift_niche.php`. Real mined cluster from 512p scan is degenerate (12 redirect stubs, 276 B — codec framing swamps it; only header-free order-0/RLE show delta win). **Synthetic versioned-document regime** (one real 3.7 KB enwik article × 40 variants @ ~2% edits = 149 KB; RT byte-exact) draws the boundary cleanly: **`zstd_w10` (1 KiB window ≪ 3.7 KB page): mono 82,186 → multishift_transpose 29,123 (−65%, net +53,063)**; `order0` 96,824 → delta 41,759 (+55,065); `rle_order0` 132,087 → transpose 33,692 (+98,395); `bzip2` 16,223 → delta 15,527 (+696). **Large-window LZ/CM loses:** `zstd_w27` 8,651 (delta 13,933, **−5,282**); `xz9` 8,272 (delta 12,976, **−4,704**). **Conclusion:** multishift pays exactly when repetition distance > codec effective window (beyond-window / enwik9-class) or the consumer is position-sensitive/weak; it is strictly redundant under large-window LZ/CM, which is the enwik8/zpaq production regime. **Not the 14.6M lever** — that remains stronger English modeling on the sorted single stream (phda9-class), per the entries above. Artifact: `benchmarks/bench_multishift_niche.php`.

### Δ sign convention (2026-06-16)

**All bio / multishift / qg-spiral harnesses use:** `Δ = (payload + sidecar) − baseline_mono`. **Δ < 0 = win** (fewer bytes than baseline). Positive Δ = regression. GATE PASS only when **Δ < 0**. Applied in `bench_bio_slice.php`, `bench_bio_niche.php`, `bench_multishift_slice.php`, `bench_bio_template_ifs.php`.

### quantum_grammar + spiral geometry probes (2026-06-16)

Standalone repos (not on wire until bpc gate passes):

| Repo | Path | Role |
|------|------|------|
| **quantum_grammar** | `/srv/http/quantum_grammar` | DWM CSSCPSG 0–9 cipher, `5>6>7` chains, corruption table |
| **spiral** | `/srv/http/spiral` | Torus/golden-angle embed, coangle/inverse predictors, lossless angular residuals |

**Smoke tests:** `syntax_matrix_smoke`, `corruption_roundtrip_smoke`, `spiral_roundtrip_smoke`, `dwm_reference_rt` — all **OK**.

**Spiral v6 codec (`SPRL\x06`):** order-3 byte context + 4-bit tags; lab only via `FRACTAL_ZIP_SPIRAL_CODEC=6`. **Wire default is v5** (`SPRL\x05`, 3-bit tags, order-2 + geo).

**Sidecar:** `FZSC\x02` for v5 (sparse used order-2); `FZSC\x03` for v6 (+ order-3 used keys, cap 4096).

**Bpc vs byte-bigram** (`bench_spiral_bpc.php`, ~403 KiB enwik text):

| Source | bigram | v5 | v6 |
|--------|-------:|---:|---:|
| enwik_text | 7.395 | 7.374 (+0.3% FAIL) | 7.355 (+0.5% FAIL) |
| multifractal.txt | 5.607 | 5.250 (+6.4% **PASS**) | 5.607 (FAIL) |

**Integrated slice** (`bench_spiral_inner_slice.php` @96p, gz9 LTCB, `SPIRAL_REPARSE=1`, **codec v5**):

| Mode | sidecar | LTCB | Δ vs baseline |
|------|--------:|-----:|--------------:|
| reparse v5 (wire default) | ~9,917 | ~103,928 | **+33,572** |
| v6 reparse (lab) | ~40,006 | ~127,970 | **+57,614** |

**Decision:** v5 remains wire-integrated default; v6 kept for standalone bpc lab. **GATE BLOCK** on wire — research track only.

**Unified gate:** `bench_qg_spiral_research_gate.php` — all smokes + wire probes in one run (~25s @96p). Latest: **7 smoke PASS**, **10 wire FAIL**, qg_corrupt **INFO** (0.02% syntax change). Includes **stat_pred**, **template_ifs**, **parallel_cmix+qg_seed**.

**Inner preprocess compare** (`bench_inner_preprocess_compare.php` @96p, gz9 LTCB, same 185,341 B text slice):

| Arm | sidecar | LTCB | Δ vs gz9(raw) | GATE |
|-----|--------:|-----:|--------------:|------|
| **template_ifs** | 689 | 71,388 | **+1,032** | FAIL |
| **stat_pred frozen FZPM** | 9,813 | 73,209 | **+2,853** | FAIL |
| **spiral_inner v5 reparse** | 9,917 | 103,928 | **+33,572** | FAIL |
| **stat_isp frozen vocab** | 44,889 | 109,478 | **+39,122** | FAIL |

RT byte-exact on all arms. **Best gz9-proxy arm:** template_ifs (+1,032 B) — still **+1 KiB** vs raw; spiral/qg pre-transform layers are **30–37 KiB worse** than template/stat_pred on this slice. Sidecar counted as plain `json_encode` (not `JSON_UNESCAPED_UNICODE`). **Decision:** none promote; Hutter path remains **in-member stat_pred/FZPM** or **phda9-class depth**, not outboard gz9 sidecar pre-transform.

**Bench:** `bench_spiral_modes.php`, `bench_qg_corrupt.php`, `bench_spiral_member_probe.php`, `bench_spiral_paq_seed.php`, `bench_spiral_paq_residual.php`, `bench_qg_paq_seed.php`, `bench_inner_preprocess_compare.php`.

**Member fold (`FZSPL\x01`, `FRACTAL_ZIP_SPIRAL_MEMBER_FOLD=1`):** sidecar+SPRL in one member blob (stat_pred-style). @96p reparse v5: split LTCB **103,928** vs gz9(fold) **103,884** — **+44 B** from cross-stream gzip only; **Δ still +33,528**. **Dead end** for wire.

**Member codec seed (`FZBG\x01`/`\x02`, `parallel_cmix --spiral-seed`):** order-1/2 bigram mixer seed from spiral (`bench_spiral_paq_seed.php`). @96p prefix: cmix **183,330**; FZBGv1 **183,774** (**−444 B**); FZBGv2 **184,431** (**−1101 B** vs cmix). **GATE flat** — seeding regresses; order-2 seed worse than order-1.

**Qg syntax mixer seed (`QgPaqSeed.php`, `bench_qg_paq_seed.php`):** FZBG blob from qg-corrected / syntax-tagged / boundary-merged tables (reuses `--spiral-seed`). @96p prefix on raw text: cmix **183,330**; raw FZBGv1 **183,774** (**−444 B**); qg-corrected FZBGv2 **184,115** (**−785 B**); syntax-tag **184,261** (**−931 B**); boundary-merge **184,432** (**−1102 B**). **Qg normalization regresses cmix harder than raw byte bigram** — same dead-end class as FZBG; do not promote.

**Member codec on SPRL residuals** (`bench_spiral_paq_residual.php`): gz9(raw) **70,356**; gz9(SPRL) **94,609**; cmix(SPRL) **166,038** (Δ **+95,682** vs gz9 raw). cmix does not beat gzip on residual stream. **GATE FAIL**.

**Cross-repo bench** (`bench_qg_spiral.php`): qg cipher gz9 Δ **+4–5 KiB** vs raw text on ~130 KiB slices.

**Artifacts:** `/srv/http/quantum_grammar/{src,tests,benchmarks,data}`, `/srv/http/spiral/{src,tests,benchmarks}`, `quantum_grammar/src/QgPaqSeed.php`, `fractal_zip/benchmarks/bench_qg_spiral.php`, `bench_spiral_inner_slice.php`, `bench_qg_paq_seed.php`.

### Template/macro IFS (2026-06-16)

`fractal_zip_bio_template_ifs.php` — mines `{{…}}`, `[[…]]`, wiki ref/thumb macros; lossless macro-id stream + JSON sidecar. Harness: `bench_bio_template_ifs.php`.

| Pages | baseline gz9 | LTCB (payload+sidecar) | Δ | GATE |
|------:|-------------:|-----------------------:|--:|------|
| 8 | 23,297 | 23,662 | **+365** | FAIL |
| 96 | 70,356 | 71,388 | **+1,032** | FAIL |

RT byte-exact @8p and @96p. Macro hits modest (34–111); sidecar + residual literals dominate. **Decision:** GATE BLOCK on enwik8 under gz9 — parallel pragmatic track only.

- `docs/ENWIK8_TEXT_CODEC_LAB.md` — codec/transform lab
- `benchmarks/ENWIK8_INNER_CAPS.md` — inner gates
- `.cursor/skills/large-corpus-sampling/SKILL.md` — stratified sampling pattern
