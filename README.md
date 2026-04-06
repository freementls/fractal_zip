# fractal_zip

**Web demos:** [Compress to `.fzc`](https://freement.cloud/fractal_zip/examples/fzc_compress.php) · [Extract `.fzc`](https://freement.cloud/fractal_zip/examples/fzc_extract.php)

These are shared, best-effort pages—**please avoid huge uploads** (multi‑GB trees, enormous archives). Compression and extraction are slow in PHP and long jobs hurt the machine for everyone. For serious or large corpora, run the **CLI** locally or host your own copy of `examples/fzc_compress.php` / `fzc_extract.php`.

![Please don’t upload massive files to the public demos—this cat is already exhausted.](docs/web-demo-large-uploads-note.png)

**I challenge you to beat this compression code!**

```bash
php benchmarks/run_benchmarks.php --only=<corpus> --json --no-baseline-cache
```

| Corpus | raw B | gzip-9 B | 7z dir B | best ext B | .fzc B | winner | Notes |
|--------|------:|---------:|---------:|-----------:|-------:|:------:|--------|
| test_files | 1098 | 89 | 247 | 204 | 77 | fzc | Eight short `.txt` files. |
| test_files2 | 1100 | 135 | 296 | 254 | 122 | fzc | Eight short `.txt` files (layout like `test_files`, different strings). |
| test_files4 | 85 | 113 | 244 | 202 | 104 | fzc | Two tiny `.txt` files. |
| test_files10 | 29076 | 274 | 331 | 230 | 147 | fzc | Two `.bmp` images (incl. patterned squares). |
| test_files11 | 29076 | 280 | 340 | 244 | 154 | fzc | Two `.bmp` images (variant of `test_files10`). |
| test_files13 | 62870 | 7789 | 7199 | 6281 | 6207 | fzc | Many `.html` pages (numeric filenames). |
| test_files28 | 9498 | 1102 | 1115 | 1009 | 921 | fzc | Single small `.bmp`. |
| test_files29 | 1277926 | 7970 | 7726 | 273 | 209 | fzc | Single huge fractal `.txt` |
| test_files35 | 4149414 | 1254372 | 976020 | 976568 | 797611 | fzc | Single large `.bmp` (photo stress). |
| test_files49 | 85083 | 23821 | 21849 | 20395 | 20336 | fzc | Single `phpinfo` `.html` (your export will not match byte-for-byte). |
| test_files50 | 932 | 1172 | 997 | 1056 | 519 | fzc | Synthetic micro-corpus: hundreds of tiny files. |
| test_files51 | 597 | 884 | 769 | 872 | 313 | fzc | Synthetic micro-corpus: hundreds of tiny files. |
| test_files52 | 380 | 766 | 535 | 582 | 92 | fzc | Hundreds of numbered `.txt` slices. |
| test_files53 | 940298 | 121338 | 93727 | 80997 | 80820 | fzc | Single large `.csv` (e.g. product export). |
| test_files61 | 4826840 | 2023050 | 1087089 | — | 1080447 | fzc | Multi-format rasters (PNG/GIF/WebP/JPEG, etc.) and nested folders. |
| test_files62 | 17364 | 737 | 735 | — | 401 | fzc | Synthetic tree of `.gz` members (gzip peel / literal-bundle stress). |
| test_files69 | 3534825 | 3248719 | 3242472 | — | 3234508 | fzc | Stratified mix: text, HTML, nested HTML, phpinfo, rasters, FLAC+m3u, SC2Replay path, gzip-peel dupes. |

Version 0.3

**fractal_zip** is a PHP library and reference CLI for a **custom lossless folder format** (`.fzc`): a fractal-style inner representation plus an **adaptive outer** wrapper (gzip, 7-Zip, zstd, brotli, …). It is aimed at **research, integration in PHP**, and **datasets where its heuristics actually win bytes**—not at replacing zip/7z as a general-purpose archiver for everyday use yet.

## Honest utility (description and review)

**Where it can help**

- On **some structured or repetitive trees**, benchmarks show **smaller archives than gzip-9 or 7z directory mode**; e.g. `test_files61`, `test_files62`, `test_files69`).
- It understands **folders as first-class data**: paths, per-member encoding, optional **literal transforms**, **image PAC**, **gzip peel**, **multi-FLAC merge (FZCD)** when codecs match—features a raw “tar + gzip” stack does not replicate.
- The format is **interesting for experimentation** and for **pipelines already in PHP** where you control both sides (encode + decode).

**Where it struggles**

- **CPU time** is usually **orders of magnitude worse** than 7z, gzip, or zstd on similar hardware: the implementation is **unoptimized PHP** with heavy string work and subprocess outer trials.
- **Operational weight:** best results often assume **7z** (and optionally other tools) on `PATH`; **FZCD** needs **ffmpeg/ffprobe**. That is more moving parts than “single static `zstd` binary.”
- **Byte-identical round-trip** is not always meaningful: **FZCD** and some PAC paths are **semantically lossless** (PCM / pixels) but may **not** match original file bytes—benchmark **verify** may report failures unless those features are off.

**Bottom line:** treat fractal_zip as a **specialist tool and research artifact**. Use it when you care about **this format’s semantics** or have **measured a byte or layout win** on your data; use **7z / zstd / zip** when you want **predictable speed and tooling ubiquity**.

## Requirements

- **PHP** with typical extensions used by the library.
- **`7z`** on `PATH` (e.g. p7zip) for competitive outer wrapping.
- **ffmpeg** + **ffprobe** for **FZCD** (multi-FLAC merge).

## CLI: compress and extract

The library prints HTML-style messages by default; the CLI **suppresses** them unless you set `FRACTAL_ZIP_CLI_VERBOSE=1`.

### `fz` (wrapper)

**One line:** `fz` is a small shell wrapper that runs `php fractal_zip_cli.php` to **zip a folder to a sibling `.fzc`** or **extract a `.fzc` to files** (same options as the PHP CLI).

From anywhere, using the repo’s `scripts/fz` (make executable once: `chmod +x scripts/fz`):

```bash
./scripts/fz zip path/to/folder
# writes path/to/folder.fzc next to the folder

./scripts/fz extract path/to/archive.fzc
# extracts members next to the .fzc

./scripts/fz extract path/to/archive.fzc path/to/output_dir
# copies the .fzc into output_dir, then extracts beside that copy

./scripts/fz help
```

Add `scripts/` to your `PATH`, or symlink `fz` into a directory on `PATH`, if you want to run `fz zip …` without a prefix.

### Dedicated scripts

Same behavior as `fz`, for clarity in docs and automation:

```bash
chmod +x scripts/fzc-compress scripts/fzc-extract   # once

./scripts/fzc-compress path/to/folder               # → path/to/folder.fzc
./scripts/fzc-extract path/to/archive.fzc           # extract beside archive
./scripts/fzc-extract path/to/archive.fzc out/dir   # copy .fzc into out/dir, extract there
```

### PHP entrypoint (equivalent)

```bash
php fractal_zip_cli.php zip path/to/folder
php fractal_zip_cli.php extract path/to/archive.fzc [output_directory]
php fractal_zip_cli.php help
```

Tuning env vars (`FRACTAL_ZIP_SEGMENT_LENGTH`, `FRACTAL_ZIP_MULTIPASS`, `FRACTAL_ZIP_FLACPAC`, etc.) apply the same here as in PHP or benchmarks.

## Benchmark results (all corpora)

**How to reproduce / refresh:** snapshot runs (e.g. `59_sample`, `61`, `62`, `69`) used `--no-verify` and `--no-best-ext`; outer wrapper and zip time live in JSON as `outer_codec` and `zip_seconds`. For huge trees use **`--with-huge-corpora`** or **`--only=…`**; add **`--large`** / **`--no-case-timeout`** when the driver would otherwise skip or shorten a case. On the snapshot recorded here, **`test_files59_sample`** had **7z** (dir) smaller than **`.fzc`** on bytes; **`test_files60`** is worth re-measuring when you care about FZCD vs best-ext.

**Snapshot command:**

```bash
FRACTAL_ZIP_BENCH_MEMORY_LIMIT=2G \
FRACTAL_ZIP_MULTIPASS=0 \
FRACTAL_ZIP_FLACPAC_MERGED_FRACTAL_MULTIPASS=0 \
FRACTAL_ZIP_AUTO_TUNE=0 \
php benchmarks/run_benchmarks.php \
  --only=<corpus> --large --no-verify --no-case-timeout --no-baseline-cache --no-best-ext --json
```

**Legacy / best-ext command** (fills **best ext** column):



## Automated benchmarks (this repo)

From the project root, run:

```bash
php benchmarks/run_benchmarks.php
php benchmarks/run_benchmarks.php --json
```

This copies each corpus into `benchmarks/.work/`, runs `zip_folder`, measures **raw bytes**, **gzip9** and **7z dir** compressed sizes **and** how long each baseline takes to build, plus **`.fzc` size** and **`zip_folder` time**, and **extract time**. For the gzip / 7z / fzc triple, **`*`** marks the **smallest** size or **fastest** compress time; **`win bytes`** / **`win speed`** repeat the winner id (`gzip`, `7z`, `fzc`, or **`+`**-joined ties). **Default corpora** are every **`test_files*`** directory except **`test_files35`** (add **`--large`**) and except **`test_files50` / `test_files51`** (add **`--with-synthetic-micro`** if you maintain those optional multi-hundred-tiny-file trees yourself). The main goal for ratio work is to improve results on **realistic** folders such as **`test_files`**, **`test_files2`**, **`test_files13`**, **`test_files29`**, etc., not only on synthetic layouts. Inner containers use **`FZC2`** when every path and per-member zipped blob is ≤ **65535** bytes (else **`FZC1`**). Flags: **`--out-json=path`**, **`--only=name,name`**, **`--auto-tune`**, **`--with-synthetic-micro`**. JSON output includes a **`summary`** object (how often `.fzc` is smallest among gzip/7z/fzc and cumulative byte gap). More detail is in the script footer.

To assert **`.fzc` is among the byte winners** (gzip-9 vs 7z vs best-ext vs fzc) for one corpus or all discovered corpora, use **`php benchmarks/verify_fzc_bytes_winner_each_corpus.php`** (add **`--only=name`** for a single case; uses **`--no-baseline-cache`** so baselines are not mixed with stale cache).

**Memory (PHP):** substring ops during unzip / `silent_validate` can allocate very large strings. Caps (env, **`0`** = unlimited): **`FRACTAL_ZIP_MAX_SUBSTRING_OPERATION_SLICE_BYTES`** (default **16777216** = max bytes read from `fractal_string` per `<off"len">` op), **`FRACTAL_ZIP_MAX_SUBSTRING_TUPLE_EXPAND_BYTES`** (default **16777216** = max bytes after tuple repetition / scale expansion / before nested recurse), **`FRACTAL_ZIP_MAX_EQUIVALENCE_SUBOP_RESULT_BYTES`** (default **201326592** = max `strlen` of the working equivalence string after one splice), **`FRACTAL_ZIP_SILENT_VALIDATE_MAX_OPERAND_BYTES`** (default **67108864** per operand into `silent_validate`).

**Segment length:** `fractal_zip::DEFAULT_SEGMENT_LENGTH` is **300** (see `fractal_zip.php`). Set env **`FRACTAL_ZIP_SEGMENT_LENGTH=<number>`** to force a value, or **`FRACTAL_ZIP_SEGMENT_LENGTH=auto`** (or `FRACTAL_ZIP_AUTO_SEGMENT=1`) for automatic selection. Candidate list is **`FRACTAL_ZIP_AUTO_SEGMENTS`** (comma-separated; the built-in default is a wider ladder in code).

**Automatic compression tuning:** With **`FRACTAL_ZIP_SEGMENT_LENGTH=auto`**, `zip_folder()` runs a **full auto-tune** before the real zip unless you set **`FRACTAL_ZIP_AUTO_TUNE=0`**. It copies the folder, measures **`.fzc` size** (then **wall time** on ties) for trials over: (1) each segment candidate, (2) **`FRACTAL_ZIP_AUTO_TUNE_TOP_K`** (default `5,7,9,10,12`), (3) a grid of **`FRACTAL_ZIP_AUTO_TUNE_IMPROVEMENT`** (default `0.65,0.85,1.0,1.2`) × **`FRACTAL_ZIP_AUTO_TUNE_GATE`** (default `1.25,1.5,1.75,2.0`). Winners are stored on the instance (`segment_length`, `tuning_substring_top_k`, `improvement_factor_threshold`, `tuning_multipass_gate_mult`). With a **fixed** segment length, set **`FRACTAL_ZIP_AUTO_TUNE=1`** to run only steps (2)–(3) on that segment. Narrow the comma-lists to shrink trial count when iterating.

**Manual tuning (ratio-first defaults):** **`new fractal_zip()`** enables **multipass** by default. Set **`FRACTAL_ZIP_MULTIPASS=0`** to turn it off. **`FRACTAL_ZIP_IMPROVEMENT_THRESHOLD`** (default **1.0**) and **`FRACTAL_ZIP_MULTIPASS_GATE_MULT`** (default **1.5**, 1–4) apply when auto-tune does not override them. **`FRACTAL_ZIP_SUBSTRING_TOP_K`** (default **10**, 1–12) is the env fallback when **`tuning_substring_top_k`** is unset. Pass **`new fractal_zip(null, false)`** for single-pass without setting env.

**Large inputs:** env **`FRACTAL_ZIP_MAX_FRACTAL_BYTES`** caps how many bytes of a single member go through the heavy substring pass (default **2097152** = 2 MiB). Above the cap, that member uses **lazy range markers** only, which avoids multi‑hundred‑MB `substr` dictionaries on big binaries. Set to **`0`** for unlimited (original behavior; high RAM risk on large random-like files).

**Member paths in `.fzc`:** `zip_folder()` stores each file under a path **relative to the zipped directory** (forward slashes, e.g. `notes/a.txt`), not the full host path, so benchmark work-dir naming no longer changes container size. Extraction still creates subfolders as needed; keys from **old** containers keep working (`build_directory_structure_for` accepts `/` and `\`). New archives emit **`FZC2`** when safe (see benchmarks paragraph); **`FZC1`** and legacy **`serialize()`** payloads still decode.

**Outer `.fzc` wrapper:** when 7-Zip is used, the encoder tries several **LZMA2** `-m0` profiles (higher `fb` for larger payloads; very large payloads also try **`lzma2:d=64m:fb=273`** when the build accepts it), plus for inner payloads **≥ 4 KiB** **PPMd** (`o=10`, and **`o=12`** when inner **> 64 KiB**), then keeps the smallest 7z blob if it beats **gzip-9**. Data is fed via **stdin** (`-sifzinner.fractalzip`) when possible; if that fails, a short temp file under the program path is used. Set **`FRACTAL_ZIP_SKIP_PPMD=1`** to skip the PPMd attempts (faster, can lose ratio on text-heavy containers).

The **HTML table below** is **legacy** (WinRAR / FreeArc / app UI); digits will not match the script row‑for‑row unless you use the **same OS, same compressor builds, and the same file bytes** as that run (your new **phpinfo** will not match **test_files49** in the table).

## Online storage layout (planned integration)

Recommended siblings on disk (what `StorageIndex` assumes by default):

```text
<workspace>/
├── fractal_zip/          ← this repository
├── LOM/
│   └── O.php
└── files/                ← physical blobs and `.fzc` paths relative to this root
```

- **Physical blobs**: default root is the sibling directory `../files` (i.e. `dirname(fractal_zip)/files`), overridable with `FRACTAL_FILES_ROOT`.
- **Index**: `storage/storage_index.xml` lists logical paths, whether each file is **loose** or lives in a **`.fzc`**, and container metadata. `storage/StorageIndex.php` reads it with **LOM** (`../LOM/O.php` by default). Override the library path with `LOM_O_PHP` or the third constructor argument. Override the XML path with `STORAGE_INDEX_XML`.
- **Compaction heuristics**: `storage/CompactionCalculus.php` scores candidate clusters using estimated bytes saved vs latency cost (tuned for “whole container touch” extraction).
- **Dashboard**: `web/index.html` + `web/api/storage_stats.php` — serve the `web/` directory with PHP (e.g. `php -S localhost:8080 -t web` from the repo root, or configure your vhost docroot to `web/`).


<table border="1">
<caption>benchmarks table</caption>
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
<th scope="row">test_files35<br>rafale.bmp<br>4,149,414&nbsp;B</th>
<td>1,143,200&nbsp;B</td>
<td>784,950&nbsp;B</td>
<td>990,773&nbsp;B</td>
<td>990,925&nbsp;B</td>
<td>from <a href="https://www.maximumcompression.com/data/files/index.html">maximumcompression.com</a></td>
<tr>
<th scope="row">test_files49<br>phpinfo.html<br>106,762&nbsp;B</th>
<td>20,691&nbsp;B</td>
<td>20,123&nbsp;B</td>
<td>19,779&nbsp;B</td>
<td><span style="color: green;">19,407&nbsp;B</span><br><span style="color: red;">67.498s</span></td>
<td>fractal_zip was able to find a little more room for compression than the others; cool!</td>
</tr>
<tr>
<th scope="row">test_files29<br><a href="http://flaurora-sonora.000webhostapp.com/fractal_zip/test_files29/showing_off.txt">showing_off.txt</a><br>1,277,926&nbsp;B</th>
<td>11,651&nbsp;B</td>
<td>613&nbsp;B</td>
<td>25,180&nbsp;B</td>
<td><span style="color: green;">102&nbsp;B!<br><span style="color: red;">&infin;!</span></td>
<td>This is a file with highly fractal data; in other words a highly unlikely file to produce naturally. Nevertheless it serves to illustrate that the fractal_zip approach is valid. This file has a fractal recursion level of 30, whereas the most highly compressed things we know of (example: <abbr title="Deoxyribonucleic acid">DNA</abbr> with fractal recursion level of 7) are much less fractal.</td>
</tr>
<tr>
<th scope="row">test_files28<br><a href="http://flaurora-sonora.000webhostapp.com/fractal_zip/test_files28/sf.bmp">sf.bmp</a><br>9,498&nbsp;B</th>
<td>1,195&nbsp;B</td>
<td>1,217&nbsp;B</td>
<td>1,179&nbsp;B</td>
<td>1,192&nbsp;B<br>3.484s</td>
<td>No advantage to using fractal_zip. 7-zip wins for this single BMP.</td>
</tr>
<tr>
<th scope="row">test_files2</th>
<td>796&nbsp;B</td>
<td>496&nbsp;B</td>
<td>378&nbsp;B</td>
<td><span style="color: green;">345&nbsp;B</span><br>0.471s</td>
<td>These short strings (~100&nbsp;B) are somewhat fractal and benefit from fractal_zip.</td>
</tr>
</tbody>
</table>

general comments

<ul>
<li><strong>.fzc pipeline:</strong> per-file payloads and the shared fractal (or lazy) string are encoded as an inner container (<strong><code>FZC2</code></strong> when safe, else <strong><code>FZC1</code></strong>; legacy <code>serialize()</code> still decodes). That inner blob is passed through <code>adaptive_compress</code>: it competes <strong>gzip-9</strong> against staged <strong>7-Zip</strong> (and optional zstd/brotli/FreeArc when available), keeping the smallest outer wrapper. On Linux/macOS, install <strong>p7zip</strong> and ensure <code>7z</code> is on <code>PATH</code>. Override with env <code>FRACTAL_ZIP_FORCE_OUTER=gzip</code> or <code>FRACTAL_ZIP_SKIP_7Z=1</code>. After writing the file, <code>fractal_zip::$last_written_container_codec</code> records the chosen outer codec.</li>
<li>fractal_zip is currently very slow. Other compression code is clearly superior in speed (for the test files fractal_zip alternatives takes an insignificant amount of time ~1 second while fractal zip takes a significant amount of time). There are various reasons for this: it is unoptimized and it is written in PHP and it is doing more than the others. Outer 7z adds subprocess cost per candidate blob but often improves size versus gzip alone.</li>
<li>fractal_zip is currently very basic. The only operation it uses is substring and more operations (translation, rotation, scaling, etc.) would surely add to its compressability.</li>
<li>Currently, the file metadata (path, date modified, packed size, etc.) is wastefully encoded twice since fractal_zip does this and 7-zip does this independantly. So, currently, in order to compress more, fractal_zip has to overcome this extra obstacle.</li>
<li>It's funny how compression code is effectively fractal in its development (whether known or unknown to the developers) itself. freearc uses 7-zip and RAR while fractal_zip currently uses 7-zip which uses LZMA which uses...</li>
</ul>

Version 0.1

Currently the zips this code produces are only sometimes as good as other common zipping programs, or in very specific cases a tiny
bit better (mostly dues to lower format overhead). Were this code to be truely fractal, then it would be interesting.
