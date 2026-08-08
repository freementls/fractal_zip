# Desktop app (fractal_zip)

Version **0.1** ships a **native executable** for Windows, macOS, and Linux: an OS window (system WebView) that loads the same compress / extract UI as the web demos.

## What it is (and is not)

| | |
|--|--|
| **Is** | A real binary (`fz-desktop` / `fz-desktop.exe`) that opens its **own window** |
| **Is** | The existing PHP compress/extract pages, served locally next to the binary |
| **Is not** | “Open Chrome to a random localhost port and hope a server is still running” |
| **Is not** | A full rewrite of the compressor outside PHP |

The earlier browser tab on port **17999** was only a temporary smoke test; that process was stopped on purpose. Use `./fz-desktop` from a built package (or `desktop/scripts/run_dev.sh`) instead.

## Layout of a release zip

```
fractal_zip-desktop-0.1.0-linux-x86_64/
  fz-desktop          ← native window app (Rust + WebView)
  VERSION
  README.txt
  payload/            ← fractal_zip*.php + examples web UI + hub
  runtime/            ← optional bundled php
```

## Build

```bash
# Native binary + zips (Linux host produces a real Linux binary)
desktop/scripts/package.sh

# Day-to-day on this machine
desktop/scripts/run_dev.sh
```

Source: `desktop/app/` (Rust, `tao` + `wry`). Linux needs WebKitGTK (`webkit2gtk-4.1`).

**Cross-OS:** build `desktop/app` on each OS (or add CI matrix jobs) so Windows/macOS zips contain `fz-desktop.exe` / macOS `fz-desktop`. Packaging from Linux alone cannot produce those binaries without a cross toolchain.

## Versioning / publish

| File | Purpose |
|------|---------|
| `VERSION` | Product **0.1.0** |
| `desktop/VERSION` | Keep in sync |

Publish GitHub Release zips only after a **functionality milestone** passes `docs/STABILITY.md`. Then periodically rebuild and re-attach as `fz` improves.

## Requirements for end users

- The `fz-desktop` binary for their OS  
- **PHP 8.1+** on `PATH`, or `runtime/php` (or `php.exe`) inside the zip  
- Optional: `7z`, `zstd`, `zpaq`, … on `PATH` for better ratios (same as web/CLI)

## Limits vs public web demos

| | Public demo | Desktop 0.1 |
|--|-------------|-------------|
| UI host | Shared server | Local process inside the app |
| Upload cap | ~8 MiB | Unlimited by default |
| Jobs | Server disk | User data dir |

## Not in 0.1

- Auto-update  
- Shipping the full `tools/` codec tree inside the zip  
- Bundled PHP in every zip by default (optional via `desktop/scripts/fetch_php_runtime.sh`)
