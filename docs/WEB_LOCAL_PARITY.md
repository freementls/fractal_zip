# Web vs local extract parity

Compressing on your laptop and extracting on the live site fails when the **server** lacks the same tools and PHP capabilities as the encode host—not because the format is unsupported.

## Operator checklist

| When | Action |
|------|--------|
| After deploy | `scripts/fzc_parity_gate.sh --as-web` on the server (full gate, not `--library-only`) |
| Before go-live | `scripts/fzc_live_setup_check.sh` (runs CLI + web parity gates and warns if strict web gate is off) |
| User reports extract failed | Read `parity_hint` in `fzc_extract.php` JSON; open `fzc_capability_report.php` on the server |
| Before blaming “unsupported format” | Confirm `native_outer_single_member_unsupported` is selective API only — use full extract |
| Local vs production drift | `scripts/fzc_parity_compare_live.sh 'https://…/fzc_capability_report.php?json=1'` |
| GitHub manual compare | Actions → **fzc-live-parity** → paste live URL (set secret `FZC_WEB_API_SECRET` if needed) |

## Quick diagnose

```bash
# Local CLI + web-simulation reports
php examples/fzc_capability_report.php --label=local --human
scripts/fzc_parity_diagnose.sh

# Compare against production (set your URL)
scripts/fzc_parity_diagnose.sh \
  --live-url='https://YOUR_HOST/examples/fzc_capability_report.php?json=1'

# Prove a specific archive round-trips on both hosts
php examples/fzc_capability_report.php --label=local --fzc=/path/to/file.fz --out=local.json
curl -fsSL 'https://YOUR_HOST/examples/fzc_capability_report.php?json=1&fzc=/server/path/file.fz' -o live.json
php examples/fzc_capability_compare.php local.json live.json
```

On the server, open `examples/fzc_capability_report.php` (plain-text summary) or `?json=1` after deploy.

When **`fzc_extract.php`** fails, the JSON error may include **`parity_hint`** (e.g. missing zpaq) pointing at this doc.

## What the report checks

| Area | Why it matters |
|------|----------------|
| `external_tools` | `zpaq`, `7z`, `brotli`, `xz`, `zstd`, `arc` resolved via `fractal_zip::*_executable()` |
| `profile.functions` | `proc_open` / `shell_exec` must not be in `disable_functions` for web SAPI |
| `library.peer_files_missing` | Deploy full `fractal_zip*.php` tree, not a single file |
| `outer_wire_shapes` | Which outers need which tools for **full** extract (`fzc_extract.php` → `open_container`) |
| `fzc_roundtrip` | Optional: `open_container` on a real `.fz` you pass with `--fzc` |
| `parity_gaps` | Actionable errors (missing zpaq, no subprocess, round-trip failed) |

## Selective APIs vs full extract

`try_list_container_members_for_web_fs` / `try_read_container_member_bytes_for_web_fs` return `native_outer_single_member_unsupported` for many native folder outers (zpaq, 7z, arc, brotli, xz). That is **not** the same as “format unsupported” for end users.

**Parity surface for uploads:** `fzc_extract.php` → `open_container()` — the server must have the same external tools as were used when the archive was built.

**Web FS bridge:** `fractal_zip_web_fs_bridge.php --json` may return `parity_hint` when `code` is `native_outer_single_member_unsupported` — that means “use full extract”, not “install a new format”.

## Fixing the live host

1. Copy `examples/fz_fractal_local_env.php.example` → `fz_fractal_local_env.php` with paths to `zpaq`, `7z`, etc. (see `setup_fractal_zip_zpaq_env.sh`).
2. Ensure PHP-FPM allows `proc_open` and `shell_exec`.
3. Redeploy all `fractal_zip*.php` peers next to `fractal_zip.php`.
4. Re-run `fzc_capability_report.php?json=1` until `parity_gaps` has no `severity: error`.

Related: `examples/fz_server_report.php` (lighter PHP/host facts).

CI: `php benchmarks/smoke_fzc_capability_report.php` (included in `tests/run_php_smokes.sh` phase 8).

**Security:** On production, prefer `?json=1` without `fzc=` (server path probe). Use `--fzc` only for archives already on the server filesystem, not user-supplied paths from the public internet unless you trust the path.

When **`FZC_WEB_API_SECRET`** is set (same as compress/extract), the capability report requires `Authorization: Bearer <secret>`:

```bash
export FZC_WEB_API_SECRET='your-secret'
curl -fsSL -H "Authorization: Bearer ${FZC_WEB_API_SECRET}" \
  'https://YOUR_HOST/examples/fzc_capability_report.php?json=1' -o live.json
```

`scripts/fzc_parity_diagnose.sh` passes that header automatically when the env var is set.

**CI-style exit code:** `php examples/fzc_capability_compare.php --json local.json live.json` → `{"ok":true,...}` or exit `1`.

**Pre-deploy gate (this machine):**

```bash
scripts/fzc_parity_gate.sh              # CLI tools as PHP sees them
scripts/fzc_parity_gate.sh --as-web     # same bootstrap as fzc_extract.php
scripts/fzc_live_setup_check.sh         # recommended one-shot deploy checklist
```

Exit `1` when `parity_gaps` contains any `severity: error` (missing zpaq, no `proc_open`, incomplete library deploy, round-trip failure with `--fzc`, etc.).

GitHub Actions uses **`bash scripts/fzc_parity_gate.sh --library-only`** (PHP + library deploy only; runner may lack `zpaq`). On the **production extract host**, run the full gate without `--library-only`.

## Production hardening (recommended)

Set **`FZC_WEB_ENFORCE_EXTRACT_COMPAT=1`** in the web runtime environment.  
When enabled, `fzc_compress.php` and `fzc_extract.php` reject requests with HTTP 503 if required extract tools (`zpaq`, `7z`, `arc`, `brotli`, `xz`, `zstd`) or subprocess support are missing, so operators catch drift before users receive unusable `.fz` workflows.

**CLI:** `fractal_zip_cli.php member-list --json` on native outers includes `parity_hint` when `code` is `native_outer_single_member_unsupported` — use `extract` / `fzc_extract.php` for full decode.

## Files (repo)

| Path | Role |
|------|------|
| `examples/fzc_capability_probes.php` | Shared probe logic |
| `examples/fzc_capability_report.php` | Host report (CLI / HTTP) |
| `examples/fzc_capability_compare.php` | Diff two JSON reports (`--json` for CI) |
| `examples/fzc_parity_hints.php` | Error → fix mapping (web, CLI, bridge) |
| `scripts/fzc_parity_diagnose.sh` | Local + optional live curl |
| `scripts/fzc_parity_compare_live.sh` | Local web-sim vs one live URL |
| `scripts/fzc_parity_gate.sh` | Pass/fail gate (`--library-only` for CI) |
