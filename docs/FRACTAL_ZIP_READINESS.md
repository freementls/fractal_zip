# fractal_zip readiness (web FS, demos, operations)

This document ties together production-oriented knobs added for **web filesystem** backends that expose logical files backed by `.fz` / `.fractalzip` blobs, plus deployment hygiene for the PHP examples.

## Semantic tiers

| Tier | Meaning | Verify |
|------|---------|--------|
| **A — bitwise** | Default FLAC path (`FRACTAL_ZIP_FLACPAC` unset), literal transforms that preserve bytes | SHA / byte compare |
| **B — semantic** | FZCD merged PCM, some PAC image paths, MPQ / SC2 replay rewrites | Domain tools — `tools/fractal_zip_mpq_semantic.py`, `tools/flac_semantic_equal.php` (ffprobe) |
| **C — experimental** | Semantic image / lossy-source lanes documented in `README.md` | Manifest + manual QA |

Document the tier you expose to users. Web FS implementations should not promise Tier A unless transforms are disabled or verified.

## Web FS integration

Pair container reads with your logical-path index (see `storage/StorageIndex.php` for an XML-backed sketch).

Primary APIs (no full-disk sibling extract for FZB4-friendly outers):

- `fractal_zip::try_list_container_members_for_web_fs($containerPath)`
- `fractal_zip::try_read_container_member_bytes_for_web_fs($containerPath, $memberRelPath)`
- **`inspect_container_for_web_fs($containerPath)`** on a **`fractal_zip`** instance — magic prefix, outer hints, member-list preview (same payload as `fractal_zip_cli.php inspect --json`).
- `fractal_zip::normalize_web_fs_member_relpath($rel)`
- `fractal_zip::container_filename_looks_like_fractal_zip($path)`

CLI wrapper for scripts:

```bash
php examples/fractal_zip_web_fs_bridge.php list /path/to/archive.fz
php examples/fractal_zip_web_fs_bridge.php read /path/to/archive.fz dir/file.txt --json
php examples/fractal_zip_web_fs_bridge.php inspect /path/to/archive.fz
```

**Limitations (by design for v1):**

- **FZCD bundles**: returns `fzcd_single_member_unsupported` — merged FLAC pipelines need full extract or a dedicated decoder pass.
- **Native outers** (raw 7z / Arc / zstd / brotli folder archives inside `.fz`): returns `native_outer_single_member_unsupported` — use `open_container` / `7z x` with member selection.
- **XZ outer** is stream-decompressed when the inner begins with `FZB4` (same as `open_container_streaming_if_applicable`).

## Runtime presets & hostile input

Loaded once per `fractal_zip` instance (first constructor call):

- `FRACTAL_ZIP_PRESET=fast|balanced|ratio` — sets conservative defaults only when specific vars are **unset** (`fractal_zip_runtime_bootstrap.php`).
- `FRACTAL_ZIP_UNTRUSTED_INPUT=1` — tightens `FRACTAL_ZIP_MAX_*` caps when those env vars are still unset (network-facing decode).

For operator-facing PHP-FPM pools, set explicit `FRACTAL_ZIP_MAX_*` instead of relying on implicit defaults.

## Web examples (`examples/fzc_*.php`)

- Keep **`web_jobs/`** (or `FRACTAL_ZIP_WEB_JOBS`) **non-web-accessible** — nginx/Caddy `deny all` equivalent to the shipped `.htaccess`.

  nginx illustration (adjust paths):

  ```nginx
  location ^~ /fractal_zip/examples/web_jobs/ { deny all; }
  ```

- Rate limits, authentication, and queue workers for long compress jobs are **deployment-specific** — prefer reverse-proxy limits in production. The PHP examples also support:
  - **`FZC_WEB_API_SECRET`** — when set, JSON POST handlers require header `Authorization: Bearer <secret>` (401 otherwise).
  - **`FZC_WEB_RATE_LIMIT_REQUESTS`** / **`FZC_WEB_RATE_LIMIT_WINDOW_SEC`** — best-effort per-IP cap using state files under `web_jobs/.fzc_rate/` (each chunked compress POST counts as one request).
- Cron GC: **`php examples/fzc_web_gc.php`** (honours **`FZC_WEB_JOB_MAX_AGE_SEC`**).
- **Local compress vs live extract:** `fzc_compress.php` / CLI may pick native outers (`zpaq`, `7z`, `arc`, …). `fzc_extract.php` needs the same binaries on the server. Diagnose with **`examples/fzc_capability_report.php`** and **`docs/WEB_LOCAL_PARITY.md`**; failed JSON responses may include **`parity_hint`**.
- **`FZC_WEB_DOWNLOAD_RATE_LIMIT_REQUESTS`** (+ **`FZC_WEB_DOWNLOAD_RATE_LIMIT_WINDOW_SEC`**) — limits **`?job=&dl=`** downloads per IP (state under **`web_jobs/.fzc_rate_dl/`**), separate from POST limits.
- **`FZC_WEB_DOWNLOAD_TOKEN`** — when set, downloads require matching **`?token=`** (bundled into JSON `download_url` / file-link URLs) or header **`X-FZC-Download-Token`**. Prefer the header for API clients to avoid **`Referer`** leakage; query params are convenient for browser `<a href>`.

**HEAD** requests against **`?job=&dl=`** receive the same headers as GET (including **`Content-Length`**) without a body — useful for web FS metadata probes.

## CLI parity with web FS

Same selective decode as `examples/fractal_zip_web_fs_bridge.php`:

```bash
php fractal_zip_cli.php member-list path/to/archive.fz
php fractal_zip_cli.php member-list --json path/to/archive.fz
php fractal_zip_cli.php member-read path/to/archive.fz relative/path.bin out.bin
php fractal_zip_cli.php inspect path/to/archive.fz
php fractal_zip_cli.php inspect --json path/to/archive.fz
```

Download URLs are built with **`fzc_web_build_download_url()`** so **`FZC_WEB_DOWNLOAD_TOKEN`** stays attached consistently everywhere links are emitted.

### CORS (separate front-end origin)

Set **`FRACTAL_ZIP_WEB_CORS_ORIGIN`** to a concrete origin (e.g. `https://app.example.com`) or `*` so compress/extract JSON POST responses, fatal JSON shutdown, and **`?job=&dl=`** downloads emit **`Access-Control-Allow-*`**. **`OPTIONS`** returns **204** when CORS is enabled. For credentialed browser requests you must use a specific origin (not `*`) and configure credentials on both sides.

### Conditional GET on downloads

Job file responses send **`ETag`** / **`Last-Modified`** and honor **`If-None-Match`** with **304 Not Modified** (no body), reducing bandwidth for repeat fetches. Matching follows **`fzc_web_if_none_match_implies_not_modified()`**: strong and weak **`W/"…"`** tokens in a comma-separated list; the field value **`*`** alone does **not** force **304** (avoids accidental unconditional short-circuit on tokenized download URLs).

**`Accept-Ranges: bytes`** is always advertised (except bare **304**). A single RFC 7233 **`Range: bytes=…`** slice returns **206** with **`Content-Range`**; overlapping multipart ranges (`bytes=a-b,c-d`) fall back to **200** full body; an unsatisfiable range → **416** with **`Content-Range: bytes */size`**.

**`If-Range`** is honored when paired with **`Range`**: entity-tag (including **`W/"…"`**) must match **`ETag`**; otherwise an HTTP-date must match file **`mtime`** within ±1s — else the response is **200** with the full file (same ETag/LM headers).

**Upload / job storage:** never execute uploaded filenames; storage is opaque bytes only. **Retention:** `FZC_WEB_JOB_MAX_AGE_SEC` (default 86400) + `FZC_WEB_GC_INTERVAL_SEC` (default 300; set `0` to disable automatic GC). Cron alternative: call `fzc_web_gc_jobs_older_than()` from ops tooling.

## Fuzz / regression

- `php benchmarks/smoke_hostile_container_inputs.php` — truncated headers + traversal guard on member paths.
- `php benchmarks/smoke_inspect_container_web_fs.php` — **`inspect_container_for_web_fs`** vs **`try_list_container_members_for_web_fs`** on edge blobs.
- `php benchmarks/smoke_fzc_web_download_range.php` — **`Range`**, **`If-Range`**, **`If-None-Match`** helpers (no HTTP server).
- `./scripts/fractal_zip_web_smoke.sh` — **`check_undeep_fzb_brotli_sync.php`** then the three smokes above (matches **`web-fs-smokes`** CI).

## Format contract

Outer wrappers and inner `FZC1`/`FZC2`/streaming literals evolve with `fractal_zip.php`. Treat container bytes as **versioned by encoder revision**; pin releases for anything beyond experimental mirrors.
