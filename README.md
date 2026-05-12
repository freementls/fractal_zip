# fractal_zip

**Try to beat this compressor on its sweet-spot folders.**

**Web demos:** [Compress to `.fzc`](https://freement.cloud/fractal_zip/examples/fzc_compress.php) · [Extract `.fzc`](https://freement.cloud/fractal_zip/examples/fzc_extract.php)

Please avoid enormous uploads on the shared demos; run serious workloads locally. **Web FS / ops:** `docs/FRACTAL_ZIP_READINESS.md` (reads, presets, job GC, **`ETag`**, **`Range`**, CORS). Bridge: `examples/fractal_zip_web_fs_bridge.php`. GC: `php examples/fzc_web_gc.php`. Regression: `./scripts/fractal_zip_web_smoke.sh`.

Please don’t upload massive files to the public demos—server administrator is already too busy.  
<img src="busy-cats.gif" width="200" alt="Please don’t upload massive files to the public demos—server administrator is already too busy." />

```bash
php benchmarks/run_benchmarks.php --only=<corpus> --json --no-case-timeout
```

To **cap discovery** by raw size (skip huge trees while iterating): **`--maximum-size=2M`** / **`--max-size=2M`**.

---

## What it is

**fractal_zip** is a **PHP library** and **CLI** for a **lossless folder archive** format (`.fzc`): a fractal-style **inner** container (`FZC1` / `FZC2`, literal bundles, optional PAC lanes, optional FZCD merged FLAC, …) plus an **adaptive outer** wrapper that competes **gzip‑9**, **7‑Zip**, **zstd**, **brotli**, **Arc**, **zpaq**, etc., keeping the smallest outer blob. It treats **directory trees as first-class data**, not only concatenated tar streams.

---

## Documented bytes wins (vs strong baselines)

Unless noted, a **bytes win** means **`.fzc` is ≤ the smallest of**: gzip‑9 **tarball**, **7z directory** archive, and the **min‑ext** tournament (Arc / zstd / brotli / xz / zpaq / …) from `benchmarks/run_benchmarks.php`. Full rows, dates, and commands live in **`benchmarks/BYTES_WIN_TRACKER.md`** (source of truth for public claims). That ledger currently includes pasted **`verify_ok: yes`** wins for **`test_files2, 4, 10, 11, 13, 28, 29, 35, 49, 52, 53, 55_sample, 55, 60, 56_sample, 57`** (plus **`test_files56`** with semantic **`verify_ok: false`** — see tracker notes). The stratified **`test_files69`** corpus is exercised in the driver; **paste a pinned JSON row** into the tracker when you publish fresh numbers.

### Standout results (representative)

| Area | Corpus | Why it matters |
|------|--------|----------------|
| **Huge real site tree** | **`test_files55`** (~200 MiB raw HTML) | `.fzc` **beats** gzip‑9, 7z dir, and **min‑ext Arc** on recorded snapshot (`verify_ok` **yes** in tracker). Encoding is **slow** (benchmark order ~25 min `.fzc` vs ~9 s gzip component — see tracker JSON). |
| **Arc‑native integration** | **`test_files57`** (~9 MiB PHP/HTML mirror) | When Arc wins min‑ext, `.fzc` can store **raw FreeArc bytes** (passthrough) so bytes **match** the best external Arc archive (`verify_ok` **yes**). |
| **Mixed rasters + folders** | **`test_files61`** (~4.8 MiB raw) | **`run_benchmarks.php`** snapshots often show `.fzc` **far below** naive gzip‑9 / strong min‑ext brotli on the same corpus (outer codec varies). Add a row to **`benchmarks/BYTES_WIN_TRACKER.md`** when you lock a reproducible command. PAC / semantic image modes may trade **SHA1** for **pixel** equality — check **`verify_ok`**. |
| **FLAC + sidecars (fast loop)** | **`test_files60`** | With **`FRACTAL_ZIP_FLACPAC` unset** (default), FLAC members stay **bit‑identical**; `.fzc` edges min‑ext **zstd** on recorded snapshot. **`FRACTAL_ZIP_FLACPAC=1`** enables merged PCM FZCD for smaller `.fzc` at the cost of SHA1 verify until a semantic FLAC comparator exists. |
| **Stratified ≤5 MiB mix** | **`test_files69`** | Text, HTML, phpinfo, rasters, FLAC+m3u, SC2 path, gzip‑peel dupes — README grid shows `.fzc` vs strong baselines (refresh row in tracker when you publish new JSON). |
| **Synthetic / peel stress** | **`test_files62`** | Tree of `.gz` members exercises gzip peel + raw‑tier wire dual (`verify_ok` **yes** in tracker). |
| **Large single BMP** | **`test_files35`** | Photo‑stress raster; tracker lists `.fzc` vs **xz** min‑ext (`verify_ok` **yes**). |
| **Micro‑many‑files** | **`test_files50`–`53`, etc.** | Hundreds of tiny files / CSV — documented wins or ties in tracker and grid. |
| **HTML subset of `test_files55`** | **`test_files55_sample`** (~26 MiB raw, every‑8th slice) | Full fractal path + unified stream; `.fzc` beats baselines on tracker snapshot. |
| **Small SC2 slice** | **`test_files56_sample`** (~1 MiB, nine `.SC2Replay`) | Fast MPQ / PAC loop vs full tree (`verify_ok` **yes**). |
| **Single‑file absurd ratio** | **`test_files29`** | Artificial 1.28 MiB fractal `.txt` where `.fzc` collapses to **hundreds of bytes** — demonstrates the inner machinery, **not** typical files. |

### Squash benchmark mirrors

Corpora **`test_files105`–`test_files132`** mirror the [Squash Compression Benchmark](https://quixdb.github.io/squash-benchmark/) file set. Build: `php benchmarks/build_test_files_squash_corpora.php`. **`test_files107`** (`cp.html`): **`benchmarks/squash_benchmarks.php`** often reports **`.fzc` smaller than the published Squash CSV best** for that row (pipeline differs from single‑file Squash plugins — treat as an honest apples‑to‑oranges sanity check, not a tournament trophy).

### Caveats that still matter

- **`test_files56`** (full ~90‑replay SC2 tree): strong **bytes** story on tracker snapshot but **`verify_ok` false** — strict SHA vs **semantic** MPQ rewrites; use when you accept semantic‑equal replay semantics.
- **Semantic image / FLAC PAC lanes** can improve ratio while breaking byte‑identical verify — tune **`FRACTAL_ZIP_IMAGEPAC`**, **`FRACTAL_ZIP_FLACPAC`**, docs in `fractal_zip_flac_pac.php`.
- **Wall time:** `.fzc` encode is usually **far slower** than gzip / zstd / often 7z on directory baselines; sometimes **competitive with or faster than the full min‑ext tournament wall** on a given corpus — see **`benchmarks/LARGE_CORPUS_SPEED.md`** and **`benchmarks/PARALLELISM.md`** (`--jobs`, threading env, **`--bench-profile`**).

---

## Is it still only a “specialized research tool”?

**Partially — but the label is too narrow.**

- **Still “research‑heavy”:** the inner fractal machinery, hundreds of env knobs, auto‑tune grids, and **PHP** as the hot path mean this is **not** a generic “replace zip everywhere” tool. Ratio work remains experimental on many corpora.
- **No longer *just* a lab curiosity:** there is a **stable benchmark harness** (`run_benchmarks.php`, guards, CI smokes), **documented bytes‑win ledger** (`BYTES_WIN_TRACKER.md`), **CLI + shell wrappers**, **web FS bridge + readiness checklist**, **native Arc passthrough**, **semantic MPQ path** for StarCraft II archives, and reproducible workflows for **large corpora** (parallel jobs, baseline cache). That is **engineering you can ship behind measured SLAs**, not only a paper concept.

**Fair summary:** fractal_zip is best described as a **specialized, integration‑first compressor**: strongest where **PHP owns the pipeline**, **folders are structured**, and you have **measured a byte win** (or need Arc / MPQ / FLAC semantics). For **ubiquitous interchange**, **predictable latency**, and **single‑binary deployment**, keep **zip / zstd / 7z** as defaults.

---

## Requirements

- **PHP** with extensions the library expects (`mbstring`, `pcntl` for parallel benchmarks, etc.).
- **`7z`** on `PATH` (p7zip) for competitive outer wrapping.
- **ffmpeg + ffprobe** for **FZCD** merged‑FLAC exploration (`FRACTAL_ZIP_FLACPAC=1`).
- Optional: **Arc**, **zstd**, **brotli**, **xz**, **zpaq** for min‑ext baselines and outer lanes (see `benchmarks/run_benchmarks.php` footer).

---

## CLI quick reference

CLI suppresses HTML chatter unless **`FRACTAL_ZIP_CLI_VERBOSE=1`**; **`FRACTAL_ZIP_SUPPRESS_HTML=1`** forces quiet in web SAPIs.

### `fz` wrapper

```bash
chmod +x scripts/fz   # once
./scripts/fz zip path/to/folder          # → path/to/folder.fzc
./scripts/fz extract path/to/archive.fzc [output_dir]
./scripts/fz member-list path/to/archive.fzc
./scripts/fz member-read path/to/archive.fzc inner/path.txt -
./scripts/fz inspect path/to/archive.fzc
./scripts/fz help
```

### PHP entrypoint (same commands)

```bash
php fractal_zip_cli.php zip path/to/folder
php fractal_zip_cli.php extract path/to/archive.fzc [output_directory]
php fractal_zip_cli.php member-list path/to/archive.fzc
php fractal_zip_cli.php member-read path/to/archive.fzc path/inside.bin [out]
php fractal_zip_cli.php inspect [--json] path/to/archive.fzc
php fractal_zip_cli.php help
```

Tuning: **`FRACTAL_ZIP_SEGMENT_LENGTH`**, **`FRACTAL_ZIP_MULTIPASS`**, **`FRACTAL_ZIP_AUTO_TUNE`**, **`FRACTAL_ZIP_FLACPAC`**, … — see `fractal_zip.php` and benchmark docblocks.

---

## Benchmarking & reproducibility

```bash
php benchmarks/run_benchmarks.php --json
php benchmarks/run_benchmarks.php --only=test_files55 --large --no-case-timeout --json
```

- **`benchmarks/BYTES_WIN_TRACKER.md`** — protocol + pasted JSON rows for public wins.  
- **`benchmarks/LARGE_CORPUS_SPEED.md`** — `--jobs`, threading, **`large-fast` / `large-balanced`**, gzip‑fast vs **`--large`**, baseline cache hygiene.  
- **`benchmarks/PARALLELISM.md`** — env reference for fair compressor threading.  
- **`php benchmarks/report_bytes_wins.php`** — summarize wins from JSON; **`--compress-time-audit`** flags slow `.fzc` encode vs baselines.  
- **`php benchmarks/verify_fzc_bytes_winner_each_corpus.php`** — assert `.fzc` among byte winners across discovery.

**Inner stress fixtures:** **`test_files140`–`test_files150`** — build `php benchmarks/build_test_files140_150.php`, verify `php benchmarks/smoke_fractal_inner_recipes_140_150.php`.

### Semantic image lane (non‑SHA1)

See **`php benchmarks/image_semantic_repack_to_dir.php`** and **`image_semantic_tournament.php`** — perceptual / budgeted re‑encode experiments; not bitwise‑identical.

---

## Operational limits (memory / tuning)

- **Substring / validation caps:** `FRACTAL_ZIP_MAX_SUBSTRING_OPERATION_SLICE_BYTES`, `FRACTAL_ZIP_MAX_SUBSTRING_TUPLE_EXPAND_BYTES`, `FRACTAL_ZIP_MAX_EQUIVALENCE_SUBOP_RESULT_BYTES`, `FRACTAL_ZIP_SILENT_VALIDATE_MAX_OPERAND_BYTES` — defaults in `fractal_zip.php`; raise carefully on huge inputs.
- **Segment length:** default **300**; **`FRACTAL_ZIP_SEGMENT_LENGTH=auto`** triggers auto‑tune grids (expensive).
- **Large literals:** **`FRACTAL_ZIP_MAX_FRACTAL_BYTES`** (default 2 MiB) caps heavy substring work per member; **`0`** = unlimited (RAM risk).

---

## Online storage layout (planned integration)

Recommended siblings on disk (what `StorageIndex` assumes by default):

```text
<workspace>/
├── fractal_zip/          ← this repository
├── LOM/
│   └── O.php
└── files/                ← physical blobs and `.fzc` paths relative to this root
```

- **Physical blobs:** default `../files` (override **`FRACTAL_FILES_ROOT`**).
- **Index:** `storage/storage_index.xml` — **`storage/StorageIndex.php`** + LOM (`LOM_O_PHP`).
- **Dashboard:** `web/` + `php -S localhost:8080 -t web`.

---

## Legacy single‑file vendor comparison

Different methodology than the **folder + min‑ext** grid above (old WinRAR / FreeArc / 7‑Zip vs early `.fzc` on a few single files). Digits are **not** comparable to current `run_benchmarks.php` rows unless you reproduce the **same OS, builds, and bytes**.

<table border="1" cellspacing="0" cellpadding="2">
<caption>Legacy vendor comparison (single-file archives)</caption>
<thead>
<tr>
<th scope="col">test</th>
<th scope="col"><a href="https://rarlab.com/">WinRAR</a></th>
<th scope="col"><a href="https://sourceforge.net/projects/freearc/">FreeArc</a></th>
<th scope="col"><a href="https://www.7-zip.org/">7-Zip</a></th>
<th scope="col">fractal_zip</th>
<th scope="col">comments</th>
</tr>
</thead>
<tbody>
<tr>
<th scope="row">35<br>rafale.bmp<br>4,149,414&nbsp;B</th>
<td>1,143,200&nbsp;B</td>
<td>784,950&nbsp;B</td>
<td>990,773&nbsp;B</td>
<td>990,925&nbsp;B</td>
<td>from <a href="https://www.maximumcompression.com/data/files/index.html">maximumcompression.com</a></td>
</tr>
<tr>
<th scope="row">49<br>phpinfo.html<br>106,762&nbsp;B</th>
<td>20,691&nbsp;B</td>
<td>20,123&nbsp;B</td>
<td>19,779&nbsp;B</td>
<td><span>19,407&nbsp;B</span><br><span>67.498s</span></td>
<td>Slightly smaller than peers on this snapshot; slow encode.</td>
</tr>
<tr>
<th scope="row">29<br><a href="http://flaurora-sonora.000webhostapp.com/fractal_zip/test_files29/showing_off.txt">showing_off.txt</a><br>1,277,926&nbsp;B</th>
<td>11,651&nbsp;B</td>
<td>613&nbsp;B</td>
<td>25,180&nbsp;B</td>
<td><span>102&nbsp;B!</span></td>
<td>Deliberately extreme fractal text — not representative of natural data.</td>
</tr>
<tr>
<th scope="row">28<br><a href="http://flaurora-sonora.000webhostapp.com/fractal_zip/test_files28/sf.bmp">sf.bmp</a><br>9,498&nbsp;B</th>
<td>1,195&nbsp;B</td>
<td>1,217&nbsp;B</td>
<td>1,179&nbsp;B</td>
<td>1,192&nbsp;B<br>3.484s</td>
<td>7‑Zip wins this BMP on that run.</td>
</tr>
<tr>
<th scope="row">2</th>
<td>796&nbsp;B</td>
<td>496&nbsp;B</td>
<td>378&nbsp;B</td>
<td><span>345&nbsp;B</span><br>0.471s</td>
<td>Short fractal‑friendly strings.</td>
</tr>
</tbody>
</table>

---

## Concept: inner vs outer

The **inner** container holds paths, member payloads, and fractal markers. **`adaptive_compress`** wraps that inner serialization as **one byte string** with gzip / 7z / … — it does **not** rebuild a full external directory catalog like **7z “directory” mode** on the live tree. You still pay outer framing and a second compression pass on the inner blob.

---

**README refreshed:** May 2026 — wins summarized from **`benchmarks/BYTES_WIN_TRACKER.md`** and the maintained benchmark grid; refresh numbers after changing encode defaults.
