# fzcodec Plugin #

fzcodec is a general-purpose buffer compressor: it sniffs the payload,
optionally transcodes known formats (JPEG to Lepton when `lepton` is
on `PATH`), then races codecs by content class. The wire is a
self-describing FZC1 header so decode never re-runs the encoder
policy.

Upstream library: https://github.com/freementls/fractal_zip/tree/master/tools/fzcodec

## Codecs ##

- **fz-lifestyle** — default; strong ratio with a CPU bound. Large
  highly-redundant text uses `bsc` when installed. Small files may
  invoke `paq8px` when it wins the race.
- **fz-ultra** — same pipeline, bytes-first. Will run `paq8px` on
  larger inputs.

Both codecs produce and consume the same FZC1 format.

## Options ##

None. Policy is the codec name plus the buffer contents.

## Dependencies ##

Build requires system **libzstd**, **libbrotli**, and **zlib**.
libzpaq is vendored (public domain; SHA-1 disabled on encode).

Optional runtime tools on `PATH` (the plugin degrades to the
in-process codecs if they are missing):

- `lepton` — lossless JPEG transcode
- `bsc` — large redundant text
- `paq8px` — small-to-medium files where a context mixer is smaller
  than zstd/brotli/libzpaq

## License ##

The fzcodec plugin is licensed under the [MIT
License](http://opensource.org/licenses/MIT). Vendored libzpaq is
public domain.
