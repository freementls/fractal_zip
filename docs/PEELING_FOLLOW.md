# Peeling follow — roadmap, Silesia (`test_files133`), and domain transforms

Companion to **`docs/ENCODE_PIPELINE.md`** (phases 1–4) and **`benchmarks/SILESIA_BENCHMARK.md`**. This note is the working list for **peel → inner → outer**: what exists, what might help **`test_files133`**, and what belongs only in a **ROM / asset-pack** lane.

## How the pipeline thinks

```
PHASE_UNPEEL     peel containers / gzip layers → plaintext or structured inner
PHASE_STREAM_BUILD   FZB* literal bundle (per member or unified stream)
PHASE_TRANSFORMS   delta, XOR, BMP grid, transform chains, fractal_inner tournament
PHASE_OUTER_CODECS gzip / zstd / brotli / xz / 7z / arc / zpaq on the inner blob
```

**Bytes wins** on general corpora come from **exposing structure** (peel) so **standard outers** and **fractal_inner** see predictable bytes — not from bolting a game-specific bitstream codec into the main inner tournament.

**Code anchors:** `fractal_zip_literal_recursive_peel.php` (semantic peel order), `fractal_zip_literal_pac.php` (ZIP/7z/MPQ/… peel + rebuild), `choose_best_literal_bundle_transform` / mode tournament in `fractal_zip.php`, folder path `zip_folder()` + `folder_wire_after_literal_outer_try_native_folder_archives()`.

### Identification-driven typing (non-negotiable)

**Extension is tier-1 of `identify()` only** — never the sole reason to peel, skip gzip-1 fast-exit, count textish census bytes, stage outer literals, or order stream PAC. Policy API: `fractal_zip_content_format_policy.php` (`fractal_zip_identify_for_policy`, `resolvedContentProfile`, `containerFamilyForPeel`).

| Area | Module | Status |
|------|--------|--------|
| Folder census textish | `folder_bundle_census_accumulate_raw_file` | `identify` + `text_*` profiles |
| Staged outer mixed-type | `folder_staged_literal_outer_auto_heuristic` | distinct `content_profile` count |
| Stream PAC | `fractal_zip_literal_pac_preprocess_streams_multipass` | magic first, then identify ext hint |
| Pipeline reorder | `reorder_raw_members_for_locality` + `REORDER_EXT` | profile buckets + LPT |
| MS Office prescreen | `literal_bundle_rel_is_ms_office_*` | PK magic + `packaged_ooxml` / `container_zip` |
| Semantic peeler | `fractal_zip_literal_recursive_peel.php` | magic scores; MPQ gated by identify |
| Gzip fast-exit / probe-9 | `choose_best_literal_bundle_transform` | `policy_literal_skip_gzip_incompressible_fast_exit` |
| Raster / image PAC | `fractal_zip_raster_canonical.php`, `fractal_zip_image_pac.php` | magic + `media_image` profile |

**Audit:** `php benchmarks/audit_extension_behavior_sites.php` · **CI:** `php scripts/lint_extension_behavior_policy.php` · **Silesia labels:** `php benchmarks/identify_silesia133_members.php`

**Maximal peel:** `fractal_zip_literal_deep_unwrap_with_layers` — semantic peel interleaved with gzip when `FRACTAL_ZIP_LITERAL_PEEL_AGGRESSIVE=1`; trace via `FRACTAL_ZIP_LITERAL_PEEL_TRACE=1`.

**Legibility-guided peel (experimental):** `FRACTAL_ZIP_LITERAL_LEGIBILITY_UNWRAP=1` runs an extra pass after stabilize that keeps peels only when {@see fractal_zip_literal_legibility_score()} rises (printable/markup/line structure) and gzip-1 proxy does not regress. Candidates: outer gzip, tar wire, embedded gzip/zlib/PK islands in opaque blobs, PDF stream PAC, OLE stream concat. Report: `php benchmarks/legibility_peel_prospects.php`. Trace: `FRACTAL_ZIP_LITERAL_LEGIBILITY_TRACE=1`.

**Exceptions (wire / domain lanes, not type detection):** FZB5/FZB6 shared-ext packing; decode tag maps; ROM **`stack_v1`** (documented below — not global fractal-inner tournament).

---

## `test_files133` today (why peeling follow matters)

| Target | `fzc_bytes` | min-ext | Gap |
|--------|------------:|--------:|----:|
| Full folder **`test_files133`** (`--large`) | **39 112 720** (**FZHM** **store**) | **39 865 338** (**zpaq**) | **bytes win** (2026-05-19) |
| **`test_files133_sample`** (~43 MiB raw) | **7 242 118** (**FZHM** **store**) | **7 513 671** (**zpaq**) | **~265 KiB win** (2026-05-19) |
| Mahoney **x12** per-file sum | **39 112 599** | **39 112 599** | tie |

**Profiles (`large-fast` / `large-balanced` / `large-bytes`)** do not change full-tree wire size. **`FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0`** alone can **worsen** batch totals (all-**zstd** drift). Fair Mahoney sums: **`bash benchmarks/run_silesia12_per_file.sh`**.

**Corpus shape:** twelve **extensionless** Mahoney files (`dickens`, `mozilla`, `xml`, …) in one folder — **211 938 580** B raw, **`textish_ratio` ≈ 1** on members. No ZIP/TAR shells; semantic container peelers rarely fire. The loss vs **xz** is mostly **inner + outer on huge plaintext**, not “missed ZIP peel.”

---

## Practical approach to winning on `test_files133`

Work in **layers**, measure on **`test_files133_sample`** first, validate on full **133** and Mahoney **x12** per-file sums.

### Per-member heterogeneous encode (FZHM v1) — **wired**

| Lever | Notes |
|-------|--------|
| **Auto path** | Unset **`FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST`**: flat **2–16** members; extensionless **or** flat-with-extensions (**`FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST_FLAT_EXT`**, default on) → **`FZHM\x01`** store outer |
| **Baseline tie** | **`FRACTAL_ZIP_FOLDER_BASELINE_TIE=1`** (default): full native rollup (arc / 7z / brotli / xz / zpaq) with caps lifted to **512 MiB** merged raw |
| **Mahoney x12** | Per-member path matches **`run_silesia12_per_file.sh`** counting model; sum should tie isolated min-ext when native lanes win |
| **Full `test_files133`** | Picks min(FZHM sum, monolithic unified, native **tar\|xz**) — expect **xz** ~48.76 MiB when min-ext xz wins whole tree |

Smoke: **`php benchmarks/smoke_fzhm_per_member_best.php`**. Doc: **`benchmarks/SILESIA_BENCHMARK.md`** § FZHM.

### 1. Measurement (do not skip)

- **Slice:** `php benchmarks/run_benchmarks.php --only=test_files133_sample --large --no-case-timeout --json --out-json=/tmp/133s.json`
- **Full tree:** `--only=test_files133 --large` (slow; compare to **xz** column).
- **Per-file model:** `bash benchmarks/run_silesia12_per_file.sh` → `php benchmarks/silesia_sum_fzc_from_bench_json.php …`
- **Passthrough audit:** `php benchmarks/analyze_bench_native_passthrough.php <bench.json>`
- **Peel A/B:** `php benchmarks/compare_peeler_inner_wire.php --cases=test_files133_sample` (extend cases list when 133_sample exists).
- **Outer rollup:** `FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG=1` on one member to see whether time is fractal_inner vs **zstd**/xz sweep.

### 2. Packaging / bench model (cheap, ~4 MiB class)

- **Prefer per-file `.fz` or x12 sum** over one monolithic folder archive when comparing to Mahoney — same raw, folder **zstd** **60.4 MiB** was **~4.4 MiB** worse than x12 sum in one session.
- **Peel-first folder encode:** **`test_files78`** (one **`silesia.zip`**) peels to the same twelve logical members as **`test_files133`**. Validated **`fzc_bytes` 39 112 813**, **`verify_ok` true** (`benchmarks/.silesia78_peel_pmb.json`, 2026-05-20) with default baseline tie + semantic **`.zip`** verify (peel cap in **`fractal_zip_folder_container_semantic_files_equal`**). Re-bench: **`FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G benchmarks/run_bench_with_cleanup.sh --only=test_files78 --large --no-case-timeout --json`**.

### 3. Phase 1 — peeling (modest on loose Silesia, still verify)

| Action | Expectation on **133** |
|--------|-------------------------|
| Confirm **no accidental gzip-fast** on full tree | **`--large`** required; census gate already skips auto gzip-fast on text-heavy ≥128 MiB trees. |
| **Per-member** literal bundle vs unified stream | Ensure twelve members stay **separate FZB entries** (paths = `dickens`, `mozilla`, …); avoid a single opaque stream if member-wise fractal_inner is stronger. |
| **Gzip peel** (mode 6 / FZG*) | Only if a member is still gzip-wrapped; Mahoney inputs are **uncompressed** — expect **no win**, but verify with trace. |
| **Semantic ZIP/7z/tar/…** | **Off** for standard **133**; enable only for experiments (e.g. re-pack twelve files into `silesia.zip` and bench peel → per-member → rebuild). |
| **Extensionless path hints** | Census already counts extensionless as textish; optional: content sniff (XML, HTML, UTF-8) to bias **transform chain** (not new peelers). |

**Optional packaging experiment (high effort):** one ZIP containing twelve members → **mode 18** multi-member semantic peel → per-entry inners → rebuild ZIP for verify. Tests the relayer story in **`SILLESIA_BENCHMARK.md`**; not required for Mahoney parity on loose files.

### 4. Phase 3 — inner / transforms (where full **133** bytes likely move)

| Lever | Notes |
|-------|--------|
| **fractal_inner** on text-heavy members | **mozilla**, **webster**, **nci** dominate byte count; profile with **`FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG`** per member. |
| **Literal transform chain** (`FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN`) | Try on **sample** then one large member; delta/XOR on repetitive text can beat raw fractal on some Squash singles. |
| **BMP / grid modes (5, 13–16)** | Irrelevant for standard Silesia (no BMP corpus); skip unless benching **`x-ray`** as raw image (it is not — extensionless binary/text). |
| **Batch stability** | Long `--only=a,b,…,z` runs can drift outers (**zstd** vs **arc**); use **per-file** benches for goldens. |
| **Native folder passthrough** | **`FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0`** for fair fractal; do not treat passthrough **7z** as a ratio win. |

### 5. Phase 4 — outer (after inner stops shrinking)

- Full-tree pick **zstd** today; min-ext **xz** wins — closing **~11.7 MiB** needs a **smaller inner**, not only reordering outers.
- On **sample**, **arc** already wins; full tree needs inner sizes that make **xz**/**zstd** competitive or **arc** passthrough eligible on merged raw caps.
- **`--ultra`** / wider predict caps: tried on sample — same **8 435 136** in documented runs; low priority for **133**.

### Wire gzip-1 proxy wins (`deep_unwrap`, `forBundleEncode=false`)

Measured with `gzdeflate(..., 1)` on member bytes after `fractal_zip_literal_deep_unwrap_with_layers`. **`.fz` folder encode often stays flat** when the win is only on pre-bundle wire bytes.

| Member | Peel | gz_save | FZC save (when run) |
|--------|------|--------:|--------------------:|
| xml, mozilla, samba | GNU tar → FZTM v1 | 1.8–76 KiB | 0 |
| ooffice | `XMLM:` metadata (~675 B) | ~3.1 MiB | — |
| osdb | `PRUN:` printable filter | ~2.07 MiB | **0** |
| mr | DICOM-meta printable | ~3.8 MiB | — |
| dickens, webster, nci, reymont | — | 0 | 0 |
| x-ray | `PRUN:` via blkmed sniff (sparse printable in 8 MiB) | ~5.8 MiB | — |

### 6. Success criteria (ordered)

1. **`test_files133_sample`:** hold **8 435 136** (guard) while cutting **`zip_seconds`** if possible.
2. **Full `test_files133`:** `fzc_bytes` ≤ **48 760 032** (beat min-ext **xz**) — **bytes win** row in **`BYTES_WIN_TRACKER.md`**.
3. **Mahoney x12 sum:** `sum_fzc_bytes` < **48 792 760** (7zip -mx=9 reference total) with per-file isolated runs.

---

## Peeler inventory (in-tree today)

**Recursive semantic peel** (`fractal_zip_literal_recursive_peel.php`), one layer per pass, re-scans after each peel:

| Id | Format | Enable / notes |
|----|--------|----------------|
| **zip** | PKZIP single-member semantic | `FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP`; multi-member **mode 18** in `fractal_zip_literal_pac.php` |
| **7z** | 7z single-member semantic | `FRACTAL_ZIP_LITERAL_SEMANTIC_7Z` |
| **mpq** | MoPAQ / MPQ | path + magic gate |
| **tar** | ustar single | `FRACTAL_ZIP_LITERAL_SEMANTIC_TAR` |
| **ar** | GNU ar single | `FRACTAL_ZIP_LITERAL_SEMANTIC_AR` |
| **cpio** | newc + TRAILER | `FRACTAL_ZIP_LITERAL_SEMANTIC_CPIO_NEWC` |
| **gitobj** | loose git blob | `FRACTAL_ZIP_LITERAL_SEMANTIC_GIT_LOOSE_BLOB` |
| **bstr** | bencode str file | `FRACTAL_ZIP_LITERAL_SEMANTIC_BENCODE_STR_ONLY` |
| **nstr** | netstring file | `FRACTAL_ZIP_LITERAL_SEMANTIC_NETSTRING_FILE` |
| **fws** | SWF FWS | `FRACTAL_ZIP_LITERAL_SEMANTIC_SWF_FWS` |
| **tar** | POSIX/GNU ustar (single or multi `TARM1`) | `FRACTAL_ZIP_LITERAL_SEMANTIC_TAR`, `FRACTAL_ZIP_LITERAL_SEMANTIC_TAR_MULTI` |
| **rom** | Genesis cartridge (`FZROMH`; pointer-table `FZRS` peel default) | `FRACTAL_ZIP_LITERAL_STACK_V1`, `FRACTAL_ZIP_LITERAL_STACK_ROM_POINTERS` (default on), `FRACTAL_ZIP_LITERAL_STACK_ROM_SCAN` (blind scan, off) |
| **ole** | CFB compound wire (`OLEW:` stream concat); FZB mode **19** in bundle contest | `FRACTAL_ZIP_LITERAL_SEMANTIC_OLE`; wire only when `forBundleEncode=false` |
| **markup** | Legacy StarOffice/binary Office (`XMLM:` / `SOXML:`) | `fractal_zip_literal_staroffice_wire.php` + markup islands |
| **staroffice_sec** | MZ/PE section zlib/gzip/printable (`SOZ:` / `SOG:` / `SOPR:`) | `fractal_zip_literal_staroffice_streams.php` — region pass before markup wire; **ooffice** still wins via tiny `XMLM:` metadata only (~675 B) |
| **pdf_flate** | Concat inflated PDF `/FlateDecode` streams (`PDFF:`) | `fractal_zip_literal_pdf_flate_wire.php` — gated when gzip-1 smaller; **reymont** null |
| **printable** | High-printable opaque / DICOM meta (`PRUN:`) | `fractal_zip_literal_printable_wire.php` (DB dumps, `osdb`) |
| **blkmed** | BLKM18 shell (`media_blkmed`) | `sniff_blkmed_image` → printable wire (`PRUN:`) with DICOM-like gates (minRel 512) |

**Other peel / structural paths (not the same score table):**

- **Gzip stack** (mode **6**, FZG1–FZG4 peel-restore trailers) — nested `.gz` / `.svgz` / member peel.
- **OLE / CFB** (mode **19**) — compound documents.
- **FLAC PAC / FZCD / raster** — media lanes (`fractal_zip_flac_pac.php`, image PAC).
- **Deep unwrap** (`FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP`) — raw tier before literal contest.
- **Folder native passthrough** — **not** a peeler; replaces inner with bench **7z** / **arc** / **brotli** / **zpaq** blob when smaller (`maybe_folder_*_native_smaller_than_fzc`).

**Aspirational peelers (general corpora — evaluate before implement):**

- **Snappy / LZ4 frame** — peel frame → payload; rebuild if smaller.
- **Zstandard skippable frames** — strip frame headers for inner, store frame map.
- **PNG / JPEG semantic** — already partially covered by raster PAC; extend only with strict round-trip.
- **SQLite / WAL** — page-oriented semantic (heavy; niche).
- **`.git` pack / idx** — asset repos, not Silesia.

---

## LoadStackCompressedData / “stack” tile codec (domain-only)

**Do not** merge stack decompress into the main **fractal_inner** tournament for general corpora — cost, maintenance, and byte wins will be poor vs existing outers + peelers on normal text/binary.

### What it is

Sega-style **stack compression** (e.g. **LoadStackCompressedData**): bitstream **commands** push **nibbles** onto a **history stack** and **copy** from stack offsets; fixed **2048 B** output per block (typical for **4bpp** tile graphics).

### Optional domain transform (niche, modest value)

| Step | Behavior |
|------|----------|
| **Detect** | Magic / heuristic + size contract (**2048 B** out); path hints (`portrait.bin`, `sf2*.bin`, ROM asset trees). |
| **Transform** | Decompress → store **raw 2048 B** tile (or **PNG** if editing-friendly) in the literal bundle; **or** passthrough bitstream if expanded form is **larger**. |
| **Rebuild** | Labeled semantic layer (e.g. **`stack_v1`**) for round-trip editing in an **SF2 / ROM asset** pack inside `.fz`. |

**Useful for:** ROM / **SF2** asset packs archived in fractal_zip — **not** for beating **Silesia** or **`test_files133`**.

### Not a fractal generalization

A custom inner (“**nibble-stack literals + word LZ**”) is a **specialized image/tile codec** — closer to PAQ on indices than to making **fz** more general. You would rebuild something **weaker than PNG + zstd** on decoded art unless the corpus is many **identical** stack-compressed blobs and you **never** decode them.

**Practical recommendation:**

- **Do not** add stack decompress to the default literal mode tournament.
- **Do** document as **ROM-tool** semantics: if fractal_zip archives **`sf2.bin`** or **portrait `.bin`** trees, **`stack_v1` → 2048 B unpacked tiles** helps **round-trip editing**, not compression records.
- **Lesson for fz:** separate **structural transform** (decompress tiles, peel gzip, PAC semantics) then let **zstd/brotli** work on the result — same as the main pipeline (**peel → inner → outer**).

**If** you ever add an explicit lane: gate with **`FRACTAL_ZIP_LITERAL_STACK_V1=1`** (name TBD), path/magic sniff only, **no** participation in `choose_best` on paths that look like Mahoney / Squash text.

---

## Priority queue (peeling follow backlog)

| P | Item | Corpus |
|---|------|--------|
| P0 | Per-member fractal_inner + transform tuning on **mozilla** / **webster** / **nci** | **133** / x12 |
| P0 | Per-file Mahoney bench discipline (`run_silesia12_per_file.sh`, passthrough analyzer) | x12 |
| P1 | **`compare_peeler_inner_wire.php`** on **133_sample** (confirm peelers inert) | sample |
| P1 | Optional **silesia.zip** semantic-ZIP experiment (peel → 12 inners → rebuild) | packaging |
| P1 | **Legibility loop** on archaic/opaque (`ooffice`, `osdb`, `mr`, `x-ray`) — embedded islands + OLE wire | **133** opaque members |
| P2 | Snappy/LZ4 frame peel (general) | mixed corpora |
| P3 | **`stack_v1`** ROM region scan (opt-in; default off) | SF2 / ROM packs |
| — | **Nibble-stack inner in global tournament** | **reject** |

---

## Partial-peel matrix (member / stream / region)

| Level | What | Status / lever |
|-------|------|----------------|
| **Member** | Whole-file semantic shell (ZIP/7z/MPQ/tar/rom/…) | **Shipped** — `fractal_zip_literal_recursive_peel.php` + `deep_unwrap_region_pass` (tar/ROM/PDF) |
| **Stream** | gzip/bzip2/xz/zstd/… inside one member | **Shipped** — `fractal_zip_literal_pac_preprocess_streams_multipass` (magic-first) |
| **Region** | Embedded islands in HTML/PDF/markup | **Shipped (experimental)** — `fractal_zip_literal_embedded_islands.php` + legibility unwrap; StarOffice PE sections; PDF flate concat |
| **PDF container** | Extract flate/image streams as members | **Experiment** — stream PAC + `PDFF:` wire concat when smaller |
| **7z/tar recursion** | Member-of-member like mode-18 ZIP | **Backlog** — mirror ZIP relayer where rebuild is safe |
| **ROM `stack_v1`** | Genesis ROM peel (FZB mode **27**); `FZROMH` + pointer-harvest `FZRS` (≤48 tiles); `GENROM1` tag round-trip | **Shipped**; blind `STACK_ROM_SCAN` still opt-in |
| **GNU tar** | `ustar  ` magic; multi-member **`FZTM\x01`** wire (zip_folder / `deep_unwrap` with `forBundleEncode=false`) or **`FZTM\x02`** + **`TARM0:`** / **`TARM1:`** for FZB mode **20** (≤64 KiB tag ⇒ **`TARM1:`**) | **Shipped** — deep_unwrap region pass |

---

## Cross-references

- **Silesia workflow:** `benchmarks/SILESIA_BENCHMARK.md`
- **Encode phases / parallelism:** `docs/ENCODE_PIPELINE.md`, `benchmarks/LARGE_CORPUS_SPEED.md`
- **ZIP peel/rebuild:** `fractal_zip_literal_pac.php`, `SILLESIA_BENCHMARK.md` § “Relayer to .zip”
- **Peel vs inner A/B:** `benchmarks/compare_peeler_inner_wire.php`
