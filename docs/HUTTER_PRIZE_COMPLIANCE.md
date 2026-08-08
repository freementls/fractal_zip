# Hutter Prize compliance (enwik8 lab)

Official rules: [prize.hutter1.net](http://prize.hutter1.net/) and **[detailed rules (hrules.htm)](http://prize.hutter1.net/hrules.htm)**.

Benchmark context: [Matt Mahoney LTCB (text.html)](https://mattmahoney.net/dc/text.html) — ranks by **compressed size + decompresser zip** only. **There is no prize money for a top LTCB ranking.** Hutter Prize adds RAM, disk, lossless verify, OSI source, and **per-phase time** limits.

## Prize submission path (canonical)

**Do not submit the PHP `fractal_zip` / `.fz` pipeline as a Hutter entry.** The winning lineage is cmix/fx2-cmix-shaped C binaries.

- Strategy + local baseline: [`benchmarks/HUTTER_PRIZE_WIN_PATH.md`](../benchmarks/HUTTER_PRIZE_WIN_PATH.md)
- Fork tree: `tools/hutter/fx2-cmix` (plus `fx-cmix`, `starlit`, `cmix` for diffs)
- Improve loop: `bash benchmarks/run_hutter_improve_loop.sh`
- enwik9 verify (needs ≥10 GiB RAM): `bash benchmarks/run_hutter_enwik9_verify.sh /path/to/enwik9`
- Package scaffold: `bash benchmarks/package_hutter_submission.sh`

Current official L (enwik9): **110,793,128** (fx2-cmix). Prize floor: **S &lt; 109,685,197**.

## Primary stretch goal (enwik8 lab / historical)

**Beat 14.6 MiB total S under full hrules.htm constraints** on enwik8 before enwik9 (~110 MiB current record).

### Size S — three accounting shapes

| Mode | Formula | When |
|------|---------|------|
| **LTCB** | `archive + decompresser_zip` | Mahoney table ranking (enwik9+prog column) |
| **Combined** | `S = comp + archive` | Self-extracting `archive9.exe` |
| **Split** | `S = comp + 2×decomp + archive` | Separate compressor + decompressor + `.bhm` archive |
| **enwik8 record** | `S = archive + decomp` | Rhatushnyak 2017 published shape |

Also counted toward S: **CLI options** required to run or compile; **external dict/data** unless embedded in the archive.

Lab default for beat-14.6 gate: **split mode** (`comp + 2×decomp + archive + dict`).

### Runtime rules (hrules.htm + LTCB time column)

| Rule | Requirement |
|------|-------------|
| **Lossless** | Decompress reproduces corpus with **no input** (no files, network, dictionaries at runtime) |
| **Platform** | Windows or Linux **x86 32/64-bit** executable, or **source zip + makefile** |
| **RAM** | ≤ **10 GiB** |
| **Temp disk** | ≤ **100 GiB** |
| **Time** | **Each** of compress and decompress ≤ **70 000 / Geekbench5** hours (single-core GB5) |
| **CPU** | **No GPU**; prize scoring is single-core |
| **Tuning** | Compressor may be **benchmark-specific** — allowed |

Reference test machines (2021): Lenovo i7-1165G7 (GB5≈**1427** single) → **~49.1 h per phase**. At GB5=1427, starlit used ~48 h comp and ~48 h decomp on enwik9 (won 2021).

### Why LTCB #1 ≠ Hutter winner

| Program | LTCB enwik8 archive | enwik9 comp/decomp (extrap.) | Hutter |
|---------|--------------------:|-----------------------------:|--------|
| **cmix v21** | 14 623 723 | ~173 h / ~177 h each | Too slow for prize |
| **nncp v3.2** | 14 915 298 | ~67 h each | Too slow |
| **phda9 1.8** | 15 010 414 | ~2.4 h each (symmetric ns/B) | enwik8 record; baseline |
| **fast-cmix-hp** | (SFX) | decomp ~34 h @ enwik9 | Won 2023 (speed opt.) |
| **starlit** | 15 215 107 | ~48 h each | Won 2021 |

Mahoney note 5: PAQ-class compressors normally have **compression ≈ decompression** speed (same ns/byte). **fractal_zip** is asymmetric: outer tournament + detection dominate **compress**; **decompress** is mostly phda9 peel (faster). A winning submission still needs **both** phases under budget on enwik9.

LTCB lists Comp and Decomp as **ns/byte**; lab converts with `enwik8_hutter_seconds_to_ns_per_byte()` and extrapolates enwik8→enwik9 linearly.

### Award (hrules.htm)

- `Award = Z × (L − S) / L` (fund Z; record L updates to new S)
- Minimum improvement **~1 MiB** (~1% of fund) for eligibility
- ~**1 € per 230 B** improvement (informal)
- **30-day** public comment before award

## Reference sizes (enwik8)

| Label | Bytes | Accounting |
|-------|------:|------------|
| **beat146 target** | 14 623 723 | split-mode total S |
| **LTCB cmix v21** | 14 905 110 | archive + decomp zip (281 387) |
| Hutter enwik8 L | 15 284 944 | archive + decomp |
| Hutter archive | 15 242 496 | archive only |

With measured split tax (2× phda9 ~556 KiB + dict ~59 KiB), **archive budget ≈ 13.45 MiB** for 14.6 MiB split-mode S.

## Lab vs submission

| Today (research) | Prize submission (later) |
|------------------|--------------------------|
| PHP `fractal_zip` + phda9 binary | Minimal **C** compressor + decompressor |
| `.fz` wire as archive stand-in | `.bhm` or self-extracting archive |
| External `.phda9_external_dict.txt` | Dict **embedded** in archive (counted in S) |
| Slice probes + extrapolation gate | Full verify + hardware metrics on ref machine |

## enwik8 → enwik9 path

1. Fix **lossless RT** (byte-identical corpus).
2. Shrink **split-mode S** toward 14.6 MiB (archive wins + tiny comp/decomp you control).
3. Prove **hardware** compliance (RAM, disk, time, single-core, no GPU).
4. Port to **enwik9** under the same hrules split formula.

## Background / low memory

```bash
export FRACTAL_ZIP_LOW_MEMORY=1
php benchmarks/run_enwik8_research_probe.sh
```

One phda9 job at a time (~1.5 GiB RSS). Full-speed: `--full-memory`.

## Tools

```bash
php benchmarks/run_enwik8_hutter_compliance.php --target=beat146_hardware
php benchmarks/run_enwik8_hutter_compliance.php --target=ltcb_rank
FRACTAL_ZIP_LOW_MEMORY=1 benchmarks/run_enwik8_hutter_lowprio.sh   # @384p phda9 + verify (background)
php benchmarks/summarize_research_probe.php
php benchmarks/run_enwik8_beat146_probe.php
php benchmarks/run_enwik8_phda9_xml_encode.php --gate-check-beat146
```

Constants: `benchmarks/enwik8_hutter_prize.php`.

## Submission checklist (hrules participation)

Tracked in compliance audit output (`submission_checklist`):

1. Public download link for archive (+ decompressor if split)
2. Single-line run instruction
3. Program names, versions, options (options count in S)
4. Sizes of comp, decomp, archive
5. Time + peak RAM + temp disk
6. Test machine + Geekbench5
7. OSI source + binaries
8. Algorithm write-up
9. Build/usage instructions
10. Lossless verify on official corpus

## Current gap

| Metric | split-mode S (est.) | vs 14.6 MiB | time (enwik8) |
|--------|--------------------:|------------:|---------------|
| Archive | 15 285 304 B | — | compress ~11.5 h |
| Total S | ~16.5 MiB (2× decomp) | **+~1.9 MiB** | — |
| enwik9 extrap compress | — | — | ~115 h (**over** 49 h budget) |
| Lossless verify | fail | blocks claim | decompress ? |

Full encode blocked until extrapolation gate passes on **split-mode S**.
