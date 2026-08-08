test_files61 — synthetic "sprite sheet" stress corpus
======================================================

Raster grids and plasma textures only (no game rips). Style reference only for
community sprite archives such as The Spriters Resource:
https://www.spriters-resource.com/

Each PNG is re-encoded to GIF, WebP, JPEG, and BMP via ImageMagick, then some
paths are wrapped again in gzip, tar, zip, 7z (store), zstd, brotli, or arc where
the build host had the tool. Optional .tar.xz / .tar.bz2 (TF61_SLOW_CODECS=1), .arc (TF61_ARC=1).
A small .fzc is produced with fractal_zip::zip_folder (single-pass).

Regenerate (light): php benchmarks/build_test_files61.php