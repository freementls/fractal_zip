# In-container member edit (.fz)

Replace one logical member inside an existing `.fz` without extracting the full tree to disk and running `zip_folder` again.

## Supported lanes

| Lane | Container on disk | Strategy |
|------|-------------------|----------|
| `fzhm_store` | `FZHM\x01` … | Re-encode one per-member wire; rebuild FZHM (+ FZHR trailer if present) |
| `fzb4_plain` | `FZB4` … | Rebuild FZB4; unchanged members keep prior mode/store blobs |
| `fzb4_gzip` | gzip → FZB4 | Peel gzip, rebuild inner, re-wrap with gzip |
| `fzb4_xz` | xz → FZB4 | Peel xz, rebuild inner, re-wrap with xz (needs `xz` on PATH) |

Unsupported (returns `needs_repack`): native 7z/Arc/zpaq passthrough, zstd/brotli outers, legacy FZC fractal inners. Schedule a full agent repack instead.

## CLI

```bash
php fractal_zip_cli.php member-write [--json] archive.fz path/inside/archive.txt new-bytes.txt
```

## PHP API (fractal_zip)

```php
$fz = new fractal_zip();
$built = $fz->try_build_container_with_replaced_member_for_web_fs($fzcPath, 'dir/file.txt', $newBytes);
// $built['bytes'] = full new .fz blob

$fz->try_commit_container_member_replace_for_web_fs($fzcPath, 'dir/file.txt', $newBytes);
// same, but overwrites $fzcPath atomically
```

## ffs integration

`ffs-fzc:{bundle_hash}:{member}` stubs use `ffs_storage_save_logical_bytes()` → `ffs_storage_save_logical_fzc_member()`:

1. Splice/rebuild bundle in memory via fractal_zip
2. Store new bundle at `sha256(new_container_bytes)` (content-addressed versioning)
3. Update stub to the new bundle hash; release old bundle when unreferenced
4. Other files in the same bundle keep pointing at the old hash until repacked

When edit is not supported, catalog row is marked `fractal_state=pending` for agent repack.

## Tests

```bash
php tests/fzc_member_edit_smoke.php
```
