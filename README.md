# fractal_zip

**I challenge you to beat this compression code!**

**Web demos:** [Compress to `.fzc`](https://freement.cloud/fractal_zip/examples/fzc_compress.php) · [Extract `.fzc`](https://freement.cloud/fractal_zip/examples/fzc_extract.php)

These are shared, best-effort pages—**please avoid huge uploads** (multi‑GB trees, enormous archives). Compression and extraction are slow in PHP and long jobs hurt the machine for everyone. For serious or large corpora, run the **CLI** locally or host your own copy of `examples/fzc_compress.php` / `fzc_extract.php`.

Please don’t upload massive files to the public demos—server administrator is already too busy.
<img src="busy-cats.gif" width="200" alt="Please don’t upload massive files to the public demos—server administrator is already too busy." />

```bash
php benchmarks/run_benchmarks.php --only=<corpus> --json --no-baseline-cache
```

<style>
table.bench-latest {
  font-size: 0.76em;
  border-collapse: collapse;
}
table.bench-latest th,
table.bench-latest td {
  border: 1px solid #bbb;
  padding: 1px 3px;
  line-height: 1.05;
  vertical-align: top;
}
table.bench-latest td.num {
  text-align: right;
  white-space: nowrap;
}
table.bench-latest td.center {
  text-align: center;
  white-space: nowrap;
}
table.bench-latest .b {
  display: block;
  font-weight: 600;
  margin: 0;
  line-height: 1.02;
}
table.bench-latest .s {
  display: block;
  font-size: 0.74em;
  color: #555;
  margin: 0;
  line-height: 1.0;
}
</style>
<table class="bench-latest">
<thead>
<tr><th>Corpus</th><th>raw B</th><th>gzip-9 (B<br />s)</th><th>7z dir (B<br />s)</th><th>best ext (B<br />s)</th><th>.fzc (B<br />s)</th><th>winner</th><th>Notes</th></tr>
</thead>
<tbody>
<tr><td><em>Policy</em></td><td class="num"></td><td class="num"><span class="b">bytes</span><br /><span class="s">speed</span></td><td class="num"><span class="b">bytes</span><br /><span class="s">speed</span></td><td class="num"><span class="b">bytes</span><br /><span class="s">speed</span></td><td class="num"><span class="b">bytes</span><br /><span class="s">speed</span></td><td class="center"></td><td>Table keeps <strong>greatest all-time byte wins</strong> (only update when a corpus hits a new smallest <code>.fzc B</code>).</td></tr>
<tr><td>test_files</td><td class="num">1098</td><td class="num"><span class="b">89</span><br /><span class="s">0.0000s</span></td><td class="num"><span class="b">247</span><br /><span class="s">0.0051s</span></td><td class="num"><span class="b">204</span><br /><span class="s">0.0096s</span></td><td class="num"><span class="b">77</span><br /><span class="s">0.2768s</span></td><td class="center">fzc</td><td>Eight short <code>.txt</code> files.</td></tr>
<tr><td>test_files2</td><td class="num">1100</td><td class="num"><span class="b">135</span><br /><span class="s">0.0001s</span></td><td class="num"><span class="b">296</span><br /><span class="s">0.0108s</span></td><td class="num"><span class="b">254</span><br /><span class="s">0.0154s</span></td><td class="num"><span class="b">122</span><br /><span class="s">0.3047s</span></td><td class="center">fzc</td><td>Eight short <code>.txt</code> files (layout like <code>test_files</code>, different strings).</td></tr>
<tr><td>test_files4</td><td class="num">85</td><td class="num"><span class="b">113</span><br /><span class="s">0.0000s</span></td><td class="num"><span class="b">244</span><br /><span class="s">0.0043s</span></td><td class="num"><span class="b">202</span><br /><span class="s">0.0334s</span></td><td class="num"><span class="b">102</span><br /><span class="s">0.0965s</span></td><td class="center">fzc</td><td>Two tiny <code>.txt</code> files.</td></tr>
<tr><td>test_files10</td><td class="num">29076</td><td class="num"><span class="b">274</span><br /><span class="s">0.0002s</span></td><td class="num"><span class="b">331</span><br /><span class="s">0.0069s</span></td><td class="num"><span class="b">230</span><br /><span class="s">0.0346s</span></td><td class="num"><span class="b">147</span><br /><span class="s">0.1207s</span></td><td class="center">fzc</td><td>Two <code>.bmp</code> images (incl. patterned squares).</td></tr>
<tr><td>test_files11</td><td class="num">29076</td><td class="num"><span class="b">280</span><br /><span class="s">0.0002s</span></td><td class="num"><span class="b">340</span><br /><span class="s">0.0068s</span></td><td class="num"><span class="b">247</span><br /><span class="s">0.0112s</span></td><td class="num"><span class="b">158</span><br /><span class="s">0.1391s</span></td><td class="center">fzc</td><td>Two <code>.bmp</code> images (variant of <code>test_files10</code>).</td></tr>
<tr><td>test_files13</td><td class="num">62870</td><td class="num"><span class="b">7789</span><br /><span class="s">0.0008s</span></td><td class="num"><span class="b">7199</span><br /><span class="s">0.0126s</span></td><td class="num"><span class="b">6287</span><br /><span class="s">0.0631s</span></td><td class="num"><span class="b">6173</span><br /><span class="s">0.9081s</span></td><td class="center">fzc</td><td>Many <code>.html</code> pages (numeric filenames). FZBM v1 picks a merged-payload <strong>member order</strong> (exhaustive when ≤6 files, same brotli-Q11 proxy as FZB4 path order) so repeated HTML structure lines up better for the outer codec. Disable: <code>FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER=0</code>.</td></tr>
<tr><td>test_files28</td><td class="num">9498</td><td class="num"><span class="b">1102</span><br /><span class="s">0.0018s</span></td><td class="num"><span class="b">1115</span><br /><span class="s">0.0154s</span></td><td class="num"><span class="b">996</span><br /><span class="s">0.0293s</span></td><td class="num"><span class="b">921</span><br /><span class="s">0.1342s</span></td><td class="center">fzc</td><td>Single small <code>.bmp</code>; outer brotli Q11 may use lgwin 16 when it beats the encoder default.</td></tr>
<tr><td>test_files29</td><td class="num">1277926</td><td class="num"><span class="b">7970</span><br /><span class="s">—</span></td><td class="num"><span class="b">7726</span><br /><span class="s">—</span></td><td class="num"><span class="b">273</span><br /><span class="s">—</span></td><td class="num"><span class="b">209</span><br /><span class="s">—</span></td><td class="center">fzc</td><td>Single huge fractal <code>.txt</code>.</td></tr>
<tr><td>test_files35</td><td class="num">4149414</td><td class="num"><span class="b">1254372</span><br /><span class="s">—</span></td><td class="num"><span class="b">976020</span><br /><span class="s">—</span></td><td class="num"><span class="b">976568</span><br /><span class="s">—</span></td><td class="num"><span class="b">797611</span><br /><span class="s">—</span></td><td class="center">fzc</td><td>Single large <code>.bmp</code> (photo stress).</td></tr>
<tr><td>test_files49</td><td class="num">85083</td><td class="num"><span class="b">23821</span><br /><span class="s">0.0055s</span></td><td class="num"><span class="b">21849</span><br /><span class="s">0.0286s</span></td><td class="num"><span class="b">20395</span><br /><span class="s">0.1474s</span></td><td class="num"><span class="b">20336</span><br /><span class="s">0.5896s</span></td><td class="center">fzc</td><td>Single <code>phpinfo</code> <code>.html</code> (your export will not match byte-for-byte).</td></tr>
<tr><td>test_files50</td><td class="num">932</td><td class="num"><span class="b">1172</span><br /><span class="s">—</span></td><td class="num"><span class="b">997</span><br /><span class="s">—</span></td><td class="num"><span class="b">1056</span><br /><span class="s">—</span></td><td class="num"><span class="b">496</span><br /><span class="s">—</span></td><td class="center">fzc</td><td>Synthetic micro-corpus: hundreds of tiny files; marginal literal transforms (e.g. strrev) are rejected unless the gzip-probe win clears a byte threshold so tiny README-style wins do not dominate.</td></tr>
<tr><td>test_files51</td><td class="num">597</td><td class="num"><span class="b">884</span><br /><span class="s">—</span></td><td class="num"><span class="b">769</span><br /><span class="s">—</span></td><td class="num"><span class="b">872</span><br /><span class="s">—</span></td><td class="num"><span class="b">306</span><br /><span class="s">—</span></td><td class="center">fzc</td><td>Synthetic micro-corpus: hundreds of tiny files.</td></tr>
<tr><td>test_files52</td><td class="num">380</td><td class="num"><span class="b">766</span><br /><span class="s">0.0001s</span></td><td class="num"><span class="b">535</span><br /><span class="s">0.0206s</span></td><td class="num"><span class="b">582</span><br /><span class="s">0.0386s</span></td><td class="num"><span class="b">92</span><br /><span class="s">0.2103s</span></td><td class="center">fzc</td><td>Hundreds of numbered <code>.txt</code> slices.</td></tr>
<tr><td>test_files53</td><td class="num">940298</td><td class="num"><span class="b">121338</span><br /><span class="s">0.0330s</span></td><td class="num"><span class="b">93727</span><br /><span class="s">0.1319s</span></td><td class="num"><span class="b">80997</span><br /><span class="s">0.1401s</span></td><td class="num"><span class="b">80753</span><br /><span class="s">5.1851s</span></td><td class="center">fzc</td><td>Single large <code>.csv</code> (e.g. product export).</td></tr>
<tr><td>test_files55</td><td class="num">209994426</td><td class="num"><span class="b">64533561</span><br /><span class="s">—</span></td><td class="num"><span class="b">11775260</span><br /><span class="s">—</span></td><td class="num"><span class="b">11700238</span><br /><span class="s">—</span></td><td class="num"><span class="b">11647351</span><br /><span class="s">—</span></td><td class="center">fzc</td><td>Full StevieGee HTML tree (~200 MiB raw). <strong><code>--large</code></strong> + <strong><code>FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G</code></strong> + <strong><code>--no-case-timeout</code></strong>; <strong><code>verify_ok</code></strong>. <code>zip_seconds</code> ~1497 on snapshot (2026-04-17); <strong><code>outer_codec</code> <code>arc</code></strong>.</td></tr>
<tr><td>test_files60</td><td class="num">6767183</td><td class="num"><span class="b">6749927</span><br /><span class="s">—</span></td><td class="num"><span class="b">6718841</span><br /><span class="s">—</span></td><td class="num"><span class="b">6717584</span><br /><span class="s">—</span></td><td class="num"><span class="b">6717435</span><br /><span class="s">—</span></td><td class="center">fzc</td><td>Two <code>.flac</code> tracks + small sidecars (Discovery slice). Default <strong><code>FRACTAL_ZIP_FLACPAC</code> unset</strong> = container-byte-identical FLACs; <strong><code>verify_ok</code></strong> on strict bench. <code>php benchmarks/run_benchmarks.php --only=test_files60 --no-case-timeout --json</code> (2026-04-16). <code>--large</code> optional here.</td></tr>
<tr><td>test_files61</td><td class="num">4826840</td><td class="num"><span class="b">2023050</span><br /><span class="s">0.0903s</span></td><td class="num"><span class="b">1087083</span><br /><span class="s">0.3647s</span></td><td class="num"><span class="b">1078184</span><br /><span class="s">3.9722s</span></td><td class="num"><span class="b">768105</span><br /><span class="s">28.9273s</span></td><td class="center">fzc</td><td>Multi-format rasters (PNG/GIF/WebP/JPEG, etc.) and nested folders. <strong><code>.fzc</code> beats min-ext brotli</strong> on this snapshot; strict byte-identical verify may still report mismatches where PAC rewrites container bytes (semantically lossless pixels).</td></tr>
<tr><td>test_files62</td><td class="num">17364</td><td class="num"><span class="b">737</span><br /><span class="s">0.0002s</span></td><td class="num"><span class="b">735</span><br /><span class="s">0.0366s</span></td><td class="num"><span class="b">740</span><br /><span class="s">0.1102s</span></td><td class="num"><span class="b">533</span><br /><span class="s">1.0668s</span></td><td class="center">fzc</td><td>Synthetic tree of <code>.gz</code> members (gzip peel / raw-tier wire dual / FZG trailers; see <code>benchmarks/fzc_raw_tier_wire_dual_smoke.php</code>).</td></tr>
<tr><td>test_files69</td><td class="num">3534825</td><td class="num"><span class="b">3248719</span><br /><span class="s">0.1179s</span></td><td class="num"><span class="b">3242480</span><br /><span class="s">0.8277s</span></td><td class="num"><span class="b">3239432</span><br /><span class="s">12.1605s</span></td><td class="num"><span class="b">3234508</span><br /><span class="s">31.7045s</span></td><td class="center">fzc</td><td>Stratified mix: text, HTML, nested HTML, phpinfo, rasters, FLAC+m3u, SC2Replay path, gzip-peel dupes.</td></tr>
<tr><td>test_files56</td><td class="num">6242492</td><td class="num"><span class="b">5992745</span><br /><span class="s">—</span></td><td class="num"><span class="b">5912958</span><br /><span class="s">—</span></td><td class="num"><span class="b">5865028</span><br /><span class="s">—</span></td><td class="num"><span class="b">4636816</span><br /><span class="s">—</span></td><td class="center">fzc</td><td>~90 flat <code>.SC2Replay</code> (~6 MiB raw). <code>FRACTAL_ZIP_BENCH_MEMORY_LIMIT=2G php benchmarks/run_benchmarks.php --only=test_files56 --no-case-timeout --json</code> (2026-04-24); <strong><code>zip_seconds</code> ~506</strong>; <strong><code>outer_codec</code> <code>xz</code></strong>; <strong><code>verify_ok</code> false</strong> (semantic MPQ vs strict SHA). Details: <code>benchmarks/BYTES_WIN_TRACKER.md</code>.</td></tr>
<tr><td>test_files56_sample</td><td class="num">1025429</td><td class="num"><span class="b">1014361</span><br /><span class="s">—</span></td><td class="num"><span class="b">1013176</span><br /><span class="s">—</span></td><td class="num"><span class="b">1007570</span><br /><span class="s">—</span></td><td class="num"><span class="b">884184</span><br /><span class="s">—</span></td><td class="center">fzc</td><td>Nine flat <code>.SC2Replay</code> at corpus root (~1 MiB raw); fast MPQ PAC loop vs full <code>test_files56</code>. <code>php benchmarks/run_benchmarks.php --only=test_files56_sample --no-case-timeout --json</code> (2026-04-24).</td></tr>
<tr><td>test_files71</td><td class="num">3282445</td><td class="num"><span class="b">3156741</span><br /><span class="s">—</span></td><td class="num"><span class="b">3144504</span><br /><span class="s">—</span></td><td class="num"><span class="b">3117503</span><br /><span class="s">—</span></td><td class="num"><span class="b">2993777</span><br /><span class="s">—</span></td><td class="center">fzc</td><td>Single large PDF (~3.1 MiB min-ext brotli). Baseline sizes from <code>benchmarks/.baseline_cache.json</code>; <code>.fzc</code> from <code>FRACTAL_ZIP_MULTIPASS=0 php fractal_zip_cli.php zip test_files71</code> (~9.4 min wall, 2026-04-24). Literal PAC shrinks streams before outer wrap (<code>php benchmarks/pdf_literal_pac_empirical.php test_files71 --json --limit=1</code>: ~166 KiB saved, mostly DCT). Smaller optional semantic-JPEG repro: <code>benchmarks/fzc_repro_test_files71.sh</code> (~2.97 MiB <code>.fzc</code>).</td></tr>
</tbody>
</table>

Version 0.3

**fractal_zip** is a PHP library and reference CLI for a **custom lossless folder format** (`.fzc`): a fractal-style inner representation plus an **adaptive outer** wrapper (gzip, 7-Zip, zstd, brotli, …). It is aimed at **research, integration in PHP**, and **datasets where its heuristics actually win bytes**—not at replacing zip/7z as a general-purpose archiver for everyday use yet.

## Honest utility (description and review)

**Where it can help**

- On **some structured or repetitive trees**, benchmarks show **smaller archives than gzip-9 or 7z directory mode**; e.g. `test_files55`, `test_files61`, `test_files62`, `test_files69`).
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

The library prints HTML-style messages by default in web SAPIs; in **CLI** (including `benchmarks/run_benchmarks.php`), trace lines and orange/green HTML messages are **off** unless you set `FRACTAL_ZIP_CLI_VERBOSE=1`. Set `FRACTAL_ZIP_SUPPRESS_HTML=1` to force-quiet everywhere. Deep `var_dump` paths inside `fractally_process_string` need `FRACTAL_ZIP_FRACTAL_PROCESS_DEBUG=1`.

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

Tuning env vars (`FRACTAL_ZIP_SEGMENT_LENGTH`, `FRACTAL_ZIP_MULTIPASS`, `FRACTAL_ZIP_FLACPAC`, etc.) apply the same here as in PHP or benchmarks. **Lossless semantics:** for formats like FLAC we care about **semantic identity** (same decoded audio + the metadata that matters), not necessarily the same compressed `.flac` bitstream after re-encode. **Strict SHA1** benchmark verify is still **byte identity**; with **`FRACTAL_ZIP_FLACPAC` unset (default)** we keep **container-byte–identical** FLAC members so `verify_ok` passes without a FLAC semantic comparator. Set **`FRACTAL_ZIP_FLACPAC=1`** to allow merged-PCM FZCD and pursue smaller `.fzc` on album trees (expect SHA1 verify to fail until a FLAC semantic verify mode exists). On merged-fractal FZCD chunks, **`FRACTAL_ZIP_FLACPAC_PCM_PRETRANSFORM=0`** disables reversible PCM pre-transforms before fractal (default on when unset). When enabled, each chunk carries a **u8 preId** (current range **0–14**): **0** none; **1** / **12** first- / second-order per-channel temporal delta (s16/s32); **2** / **9** stereo side `R−L` (s16 / s32); **3** swap L/R; **4** stereo planar; **5** byte first-difference; **6** invert bytes; **7** XOR `0x80` per byte; **8** nibble-swap; **10** / **11** toggle sample sign (s16 / s32); **13** intra-frame channel deltas (≥2 ch); **14** per-sample endian byte swap (s16/s32). The encoder ranks strategies with **`gzdeflate` at `FRACTAL_ZIP_FLACPAC_PCM_PRE_GZIP_RANK_LEVEL`** (default **5**), optionally adds a **`FRACTAL_ZIP_FLACPAC_PCM_PRE_GZIP_DUAL_RANK=1`** tie-break (extra **gzip-1** pass), seeds **anchor** presets (temporal / delta2 / byte-diff / stereo helpers / **14** when applicable), then fills with ranked ids up to **`FRACTAL_ZIP_FLACPAC_PCM_PRE_FRACTAL_CANDIDATES`** (default **8**, max **16**). **Lossy** formats use separate, documented relaxed modes where applicable.

## Benchmark results (all corpora)

**How to reproduce / refresh:** some older snapshots used `--no-verify` and `--no-best-ext` to save time; **best ext** for `test_files61` / `62` / `69` matches `benchmarks/.baseline_cache.json` and a fresh `php benchmarks/run_benchmarks.php --only=test_files69 --json --no-verify --no-case-timeout` (see JSON `best_ext_folder_bytes`). Outer wrapper and zip time live in JSON as `outer_codec` and `zip_seconds`. For huge trees use **`--with-huge-corpora`** or **`--only=…`**; add **`--large`** / **`--no-case-timeout`** when the driver would otherwise skip or shorten a case. **Table snapshot (2026-04-16):** `test_files61` row from `php benchmarks/run_benchmarks.php --only=test_files61 --no-verify --no-case-timeout --no-baseline-cache --json` (brotli outer Q11 parity with min-ext when `FRACTAL_ZIP_BROTLI_HUGE_MODE=full`). **`test_files60`** (two FLAC tracks + small sidecars): with **`FRACTAL_ZIP_FLACPAC` unset**, encoding keeps **container-byte–identical** FLACs and **`verify_ok=true`** — e.g. `php benchmarks/run_benchmarks.php --only=test_files60 --no-case-timeout --json` produced **`fzc_bytes` 6 717 435** vs min-ext **zstd 6 717 583**, 7z **6 718 840**, gzip-9 tarball **6 749 927** (2026-04-16). With **`FRACTAL_ZIP_FLACPAC=1`** (merged PCM FZCD + expanded pre-transforms + gzip-ranked fractal candidates), the same driver can shrink further — e.g. **`fzc_bytes` 6 713 500** vs **zstd 6 717 584** (`--no-verify --no-case-timeout`, 2026-04-07); **`verify_ok`** is **false** until FLAC semantic verify exists. Use that corpus for fast FLAC iteration before scaling to `test_files59_sample` / full `test_files59`. On an older snapshot, **`test_files59_sample`** still had **7z** (dir) smaller than **`.fzc`** on bytes.

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

### Semantic image experiment lane (non-bitwise)

For "visual equivalence" experiments (downscale + lossy re-encode by display budget), use:

```bash
php benchmarks/image_semantic_repack_to_dir.php test_files61 test_files61_semimg \
  --allow-format-change=1 --max-display=1920x1080 --oversample=1.5
php benchmarks/run_benchmarks.php --only=test_files61,test_files61_semimg --no-verify --no-best-ext --no-case-timeout --json
# Or sweep several policies and auto-pick the best .fzc:
php benchmarks/image_semantic_tournament.php test_files61 --prefix=test_files61_semt
```

The script emits per-image characteristics (`orig_w/h`, `new_w/h`, format, quality, byte deltas) into
`_fzimg_semantic_manifest.json` by default, which can be kept as an extra "feature exposure" sidecar for
future transform work. This lane is explicitly **semantic / perceptual**, not SHA1-byte-identical.
Rule: semantic-lossy transforms are restricted to **lossy-original sources** (default allowlist: JPEG family), so lossless sources are copied unchanged unless you explicitly opt out.
Long-term research note: one extreme semantic lane is storing a compact generative recipe (prompt + controls + seed) instead of raster bytes, with a quality gate at decode time.

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


<style>
table.bench-legacy {
  font-size: 0.88em;
  border-collapse: collapse;
}
table.bench-legacy th,
table.bench-legacy td {
  padding: 2px 6px;
  line-height: 1.15;
}
</style>
<table class="bench-legacy" border="1">
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
<li><strong>Inner vs outer (not a second copy of the tree):</strong> Member paths, layout, and payloads are carried in the <strong>inner</strong> container (<code>FZC1</code>/<code>FZC2</code>, FZCD/FZB4, etc.). The <strong>outer</strong> step (<code>adaptive_compress</code>: gzip‑9, 7‑Zip, …) treats that inner serialization as <strong>one opaque byte string</strong> and compresses it again—it does <strong>not</strong> rebuild a separate full directory catalog of every original path the way <strong>7z in “directory” benchmark mode</strong> does on the live folder. You still pay <strong>outer framing overhead</strong> (headers, stream metadata) and a second compression pass on those bytes, which can leave redundant patterns if the inner blob stays somewhat textual, but it is misleading to say path metadata is “encoded twice” by fractal_zip and by the outer 7z layer in the same sense.</li>
<li>It's funny how compression code is effectively fractal in its development (whether known or unknown to the developers) itself. freearc uses 7-zip and RAR while fractal_zip currently uses 7-zip which uses LZMA which uses...</li>
</ul>

Version 0.1

Currently the zips this code produces are only sometimes as good as other common zipping programs, or in very specific cases a tiny
bit better (mostly dues to lower format overhead). Were this code to be truely fractal, then it would be interesting.
