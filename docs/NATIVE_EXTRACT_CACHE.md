# Native zpaq extract cache (Phase 1 hybrid)

Persistent cache for native folder zpaq extracts, integrated with the shared content-addressed blob store at `/srv/http/files/`.

## Layout under `FILES_ROOT`

| Path | Purpose |
|------|---------|
| `derived/fz-native-extract-cache/fzpa/{digest40}/members/` | Tree cache (fast `open_container` materialization) |
| `derived/fz-native-extract-cache/fzpa/{digest40}/.complete` | Flock-guarded ready marker |
| `derived/fzpa/{digest40}/members.json` | Manifest: member relpath → content sha256 |

**Digest key:** `sha256(raw zpaq arc bytes)` — the `7kSt…` payload inside the archive, **not** the outer `FZPA\x01` wrapper container bytes. Listing/reading an `.fz` file unwraps the wrapper first, then keys cache and manifest by the inner arc digest.

## Read lanes

1. **Manifest + blob** (`fzpa_manifest_blob` / `fzpa_manifest`) — read `members.json`, load `{FILES_ROOT}/{hash}` via `FileStore`. No tree extract required on warm paths when manifest exists.
2. **Tree cache** (`fzpa_extract_cache`) — fallback when manifest missing or blob unreadable; also used to populate cache on first extract.

After first zpaq extract, members are ingested with `FileStore::ingestBytes(..., record_times_stored=false)` so dedup works without inflating billable `times_stored` counts.

## Environment

| Variable | Default | Notes |
|----------|---------|-------|
| `FILES_ROOT` | `/srv/http/files/` | Set by `ffs.files.php` before fractal_zip loads |
| `FRACTAL_ZIP_NATIVE_EXTRACT_CACHE` | on | `0` / `off` / `false` disables tree cache |
| `FRACTAL_ZIP_NATIVE_EXTRACT_CACHE_DIR` | `{FILES_ROOT}derived/fz-native-extract-cache` | Tree cache root only |
| `FRACTAL_ZIP_NATIVE_EXTRACT_MANIFEST` | on | `0` disables manifest ingest/read |
| `FRACTAL_ZIP_ZPAQ_EXTRACT_MAX` | `2` | Global cap on concurrent zpaq folder `x` extracts (flock slots under cache root) |
| `FRACTAL_ZIP_ZPAQ_EXTRACT_TIMEOUT_SEC` | unset → `FRACTAL_ZIP_ZPAQ_TIMEOUT_SEC` | Kill hung zpaq extract (coreutils `timeout`, Unix) |
| `FRACTAL_ZIP_ZPAQ_EXTRACT_LOG` | off | `1` / `on` logs spawn/complete/cleanup to stderr or Apache error log |

FFS sets `FILES_ROOT` and `FRACTAL_ZIP_NATIVE_EXTRACT_CACHE_DIR` via `ffs_files_configure_environment()` on config load. Cache root falls back to `{sys_temp}/fz-native-extract-cache` when the configured dir is not writable (e.g. Apache `http` user cannot create `{FILES_ROOT}derived/`).

## Concurrency / stray zpaq prevention

- **Single-flight per digest:** per-digest `.lock` + blocking `flock(LOCK_EX)` — waiters block; only one worker runs zpaq for a given arc digest.
- **No cache bypass:** when cache is enabled, failed lock/extract waits for `.complete` instead of spawning a legacy `/tmp/fzpa_s_*` extract.
- **Global zpaq cap:** `FRACTAL_ZIP_ZPAQ_EXTRACT_MAX` (default 2) limits concurrent zpaq across all digests.
- **Temp sandbox:** zpaq still uses `/tmp/fzpa_s_{digest16}/` as a staging dir during extract; key is digest-based (not per-request). Stale dirs are removed on cache hit.
- **Timeout + kill:** extracts use `proc_open` with optional `timeout` wrapper; child is killed on PHP request shutdown or wall expiry.
- **Diagnostics:** set `FRACTAL_ZIP_ZPAQ_EXTRACT_LOG=1` to log spawn, completion, and temp cleanup.

If many zpaq processes appear under Apache (`pgrep -a zpaq`), check cache dir writability and reload PHP/Apache after deploy so workers pick up the library.

**Safe cleanup of runaway zpaq (http user only):**

```bash
pgrep -a zpaq          # inspect PIDs and /tmp/fzpa_s_* paths
pkill -TERM -u http -x zpaq   # graceful
sleep 2
pkill -KILL -u http -x zpaq   # if still stuck
```

Do not blanket-kill zpaq if other users run benchmarks concurrently.

## GC

Manifest ingest blobs may exist without `times_stored.xml` entries. Dry-run inventory:

```bash
php scripts/fzpa_manifest_orphan_gc.php
```

## Tests

```bash
php tests/zpaq_native_extract_cache_smoke.php
php tests/zpaq_native_extract_manifest_smoke.php
php tests/zpaq_native_extract_singleflight_smoke.php
```

## See also

- `ffs/WEB_REF_BRIDGE.md` — shared blob store rules
- `fractal_zip_native_extract_cache.php` — implementation
