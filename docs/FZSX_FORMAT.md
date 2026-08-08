# FZSX v1 — self-extracting fractal_zip archive

`.fzsx` is a **dual-face polyglot**: a PHP CLI stub, an HTML page for browser extract on a server, and a binary trailer containing a `.fz` payload.

**Rule:** files always extract to the **directory containing the `.fzsx`** — locally, on the server, and from the CLI.

## File layout

1. PHP bootstrap (CLI: extract beside self; web/mod_php: emit HTML UI byte range only)
2. `__halt_compiler()`
3. HTML UI (`Extract files` button)
4. Trailer: `FZSX\x01` + 4-byte meta length + JSON meta + raw `.fz` bytes

Pack output omits a leading shebang so mod_php does not leak `#!/usr/bin/env php` into the browser. Local double-click still uses `php archive.fzsx` via OS association.

## Local use (double-click)

Browsers cannot write beside a local `file://` archive. Local one-click extract uses **OS file association**:

1. Run the register script **once** on your machine:
   - Linux: `bash scripts/fzsx_register_linux.sh`
   - macOS: open `scripts/fzsx_register_mac.command`
   - Windows: edit `scripts/fzsx_register_windows.reg` (PHP path) and merge
2. Double-click any `.fzsx` → handler runs `php /path/to/archive.fzsx` → files appear next to the archive.

Alternative: `php file.fzsx` or `php fractal_zip_cli.php fzsx extract file.fzsx`.

Requires **PHP CLI** on PATH (or set `PHP_BIN` for `scripts/fzsx-open`).

## Server / browser

Upload `site.fzsx` beside `examples/fzsx_api.php` (or adjust `api_rel` in meta when packing).

- The directory containing the `.fzsx` must be **writable by PHP** (web server user). For local demos: `chmod ugo+w examples/fzsx_samples`.

- Apache: `examples/.htaccess` runs `.fzsx` through PHP (`AddHandler`) or rewrites to `fzsx_serve.php`; both serve **only** the HTML card (no PHP prefix, no binary trailer).
- Open the URL → click **Extract files** → POST to `fzsx_api.php` with `action=extract` and `source_rel`.
- Optional: pack with `--auto` or set `auto_extract` so hosted pages extract on load (`?auto=1` in meta).

Fallback clean HTML serve: `examples/fzsx_serve.php?f=fzsx_samples/hello.fzsx`

## Build

```bash
php fractal_zip_cli.php fzsx pack ./my-site --out my-site.fzsx
# or
bash scripts/fzsx-pack pack ./my-site --out examples/fzsx_samples/hello.fzsx
```

From an existing `.fz`:

```bash
php fractal_zip_cli.php fzsx pack bundle.fz --out bundle.fzsx
```

## API

`POST examples/fzsx_api.php`

| Field | Meaning |
|-------|---------|
| `action=extract` | Required |
| `source_rel` | Path under `examples/` (e.g. `fzsx_samples/hello.fzsx`) |
| `source` | Basename fallback when file lives directly under `examples/` |

Response JSON: `{ ok, dest, member_count, members, source }`

## Module API (`fractal_zip_fzsx.php`)

| Method | Role |
|--------|------|
| `read_trailer($path)` | Parse meta + payload |
| `pack_from_fzc($out, $fzc, $opts)` | Build polyglot |
| `pack_from_directory($out, $dir, $opts)` | Zip folder then pack |
| `extract_beside_self($path)` | Extract to `dirname($path)` |
| `polyglot_cli_entry($self)` | Used when run as `php file.fzsx` |

Library resolution: `FRACTAL_ZIP_PHP`, `meta.lib_dir`, or sibling/parent `fractal_zip.php`.

## Samples

| File | Contents |
|------|----------|
| `examples/fzsx_samples/hello.fzsx` | Single text file |
| `examples/fzsx_samples/hello.fzsxsd` | Same payload; self-destructs after extract |
| `examples/fzsx_samples/site-bundle.fzsx` | Tiny HTML + JS site |
| `examples/fzsx_samples/tree.fzsx` | Nested folders (config, src, tests, public) |
| `examples/fzsx_samples/photos.fzsx` | ~2 MiB images (favicon, Ath banner, megalith photos) |

Source trees live beside each archive under `*-src/` for rebuilding:

```bash
FRACTAL_ZIP_SPEED=1 FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY=1 php fractal_zip_cli.php zip examples/fzsx_samples/photos-src
php fractal_zip_cli.php fzsx pack examples/fzsx_samples/photos-src.fz --out examples/fzsx_samples/photos.fzsx
php fractal_zip_cli.php fzsx pack examples/fzsx_samples/tree-src --out examples/fzsx_samples/tree.fzsx
```

Avoid starting source README lines with `FZSX` (four bytes) — the packer probes members and can mis-read that prefix.

```bash
php tests/fzsx_cli_same_dir_smoke.php
php tests/fzsx_api_same_dir_smoke.php
php tests/fzsx_roundtrip_smoke.php
php tests/fzsxsd_roundtrip_smoke.php
php tests/fzsxsd_api_same_dir_smoke.php
```

## Out of scope (v1)

- PHP embedded inside each `.fzsx`
- Configurable extract root
- Zip download as default server UX

## FZSXSD — self-destruct variant

`.fzsxsd` uses the same polyglot layout as `.fzsx` but trailer magic `FZSXSD\x01` and meta `self_destruct: true`. After a **successful** extract (CLI, double-click, or server API), the archive file is overwritten (best-effort) and **unlinked**.

**Short form:** `.fzsd` is an alias for `.fzsxsd` (same magic). Prefer `.fzsd` for new packs.

```bash
php fractal_zip_cli.php fzsd pack ./my-site --out my-site.fzsd
php fractal_zip_cli.php fzsd extract my-site.fzsd   # files beside archive; archive removed
bash scripts/fzsd-pack pack ./my-site --out my-site.fzsd
# Long form still works:
php fractal_zip_cli.php fzsxsd pack ./my-site --out my-site.fzsxsd
```

Server/browser: same `fzsx_api.php` and `fzsx_serve.php`; upload or `source_rel` may use `.fzsxsd`. Response includes `self_destructed: true` when the archive was removed.

```bash
php tests/fzsxsd_roundtrip_smoke.php
php tests/fzsxsd_api_same_dir_smoke.php
php fractal_zip_cli.php fzsxsd pack examples/fzsx_samples/hello-src --out examples/fzsx_samples/hello.fzsxsd
```
