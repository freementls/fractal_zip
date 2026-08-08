# fractal_zip desktop (v0.1)

**Native OS window** (WebView) that runs the same compress / extract UI as the web demos. Not a browser bookmark — a real `fz-desktop` executable.

## What you run

| OS | Executable |
|----|------------|
| Linux | `./fz-desktop` |
| macOS | `./fz-desktop` |
| Windows | `fz-desktop.exe` |

Each release zip also contains `payload/` (PHP library + UI). PHP 8.1+ must be on `PATH`, or place a binary under `runtime/`.

## Quick start (from a built zip)

```bash
unzip fractal_zip-desktop-0.1.0-linux-x86_64.zip
cd fractal_zip-desktop-0.1.0-linux-x86_64
./fz-desktop
```

A window titled **fractal_zip 0.1.0** should open. Close the window to quit (that also stops the local PHP helper).

## Dev run (this machine)

```bash
chmod +x desktop/scripts/*.sh
desktop/scripts/run_dev.sh
```

## Build release zips

```bash
desktop/scripts/package.sh          # linux zip gets a real binary on Linux hosts
desktop/scripts/package.sh linux
```

Windows / macOS **native** binaries need `cargo build --release` inside `desktop/app` on those OSes (or CI). Until then those zips may only include a script stub — see `NATIVE_BINARY_MISSING.txt` in the zip.

## Status

**0.1.0** scaffold. Publish GitHub Release artifacts only after a functionality milestone (`docs/STABILITY.md`). Rebuild when `fz` changes enough to ship.

Details: [`docs/DESKTOP.md`](../docs/DESKTOP.md).
