# Web URL fractal_string references (FZWR)

Replace large `fractal_string` dictionary chunks with **SHT8 short URL tokens** when wire savings beat overhead. Decode is **online-only** (fetch + sha256 verify); encode guarantees a **live_browser mirror** and **FileStore** blob at registration time.

## Wire format

Trailer suffix (after compressed payload, like FZEP):

```
FZWR\x01 + u16 count + per entry:
  8-byte SHT8 code
  32-byte sha256 (piece bytes)
  u8 url_len + url
  u8 last_modified_len + last_modified
  u8 etag_len + etag
```

**FZWR v2 (piece slice):** `\x02` adds `u32 piece_offset + u32 piece_length` after sha256. Registry + FileStore always hold **piece bytes**; offset/length validate re-fetch from parent page.

In `fractal_string`, chunks become:

```
@w{K7HM3P2R}
```

## Encode hook (raw members)

For enwik entry-sort (`FRACTAL_ZIP_ENWIK_ENTRY_SORT=1`), replacements run **before any zip path** (unified stream, legacy multipass, etc.) by mutating virtual member files on disk via `fractal_zip_web_ref_apply.php`. Whole-page mode registers the full wikitext body against the Wikipedia **oldid raw URL**; large repeated chunks use corpus oldid lookup or mirrored page slices (Wayback when enabled). URL-literal mode is disabled on the web-ref track when whole-page is on.

Measured `.fz` includes the FZWR trailer appended after encode (like FZEP).

## Decode fetch order

1. FZWR trailer + sht registry (`/srv/http/sht`)
2. `/files/api/blob.php?h=` (content-addressed store)
3. `live_browser/scrape/{mirror_rel}`
4. canonical URL re-fetch
5. Fail closed on hash mismatch

## Environment

| Variable | Default | Role |
|----------|---------|------|
| `FRACTAL_ZIP_WEB_REF` | on | Master enable (`0` disables) |
| `FRACTAL_ZIP_WEB_REF_MIN_GAIN_BYTES` | `64` | Min savings to accept a ref |
| `FRACTAL_ZIP_WEB_REF_MIN_STABILITY` | `50` | Min stability score (encode) |
| `FRACTAL_ZIP_WEB_REF_PROBE_MIN_CHUNK` | `128` | Min repeated substring length |
| `FRACTAL_ZIP_WEB_REF_PROBE_MAX_CHUNKS` | `80` | Max replacements per encode |
| `FRACTAL_ZIP_WEB_REF_PROBE_MAX_URLS` | `40` | Max URL candidates to try |
| `FRACTAL_ZIP_WEB_REF_PROBE_WIKI_PAGES` | `40` | Wikipedia raw oldid URLs from XML |
| `FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR` | off | Fast URL-literal probe (no live fetch) |
| `FRACTAL_ZIP_WEB_REF_URL_LITERAL` | on | Replace repeated URL strings with `@w{}` |
| `FRACTAL_ZIP_WEB_REF_URL_LITERAL_MIN_GAIN` | `min(32, MIN_GAIN)` | Gain gate for URL literals |
| `FRACTAL_ZIP_WEB_REF_CORPUS_PIECES` | on | enwik dump oldid pieces without live HTML match |
| `FRACTAL_ZIP_WEB_REF_PROBE_CORPUS_RESERVE` | half of max chunks | Wikitext slots before URL literals |
| `FRACTAL_ZIP_WEB_REF_CORPUS_MIN_GAIN` | `min(32, MIN_GAIN)` | Gain gate for corpus pieces |
| `FRACTAL_ZIP_WEB_REF_WHOLE_PAGE` | on | Replace full `<text>…</text>` bodies with `@w{}` |
| `FRACTAL_ZIP_WEB_REF_WHOLE_PAGE_MIN_BYTES` | `2048` | Min wikitext body size for whole-page refs |
| `FRACTAL_ZIP_WEB_REF_WHOLE_PAGE_MAX` | `200` | Max whole pages registered per encode |
| `FRACTAL_ZIP_WEB_REF_WAYBACK` | off | CDX resolve dead links → archive.org playback |
| `FRACTAL_ZIP_WEB_REF_PROBE_FULL_SCAN` | off | Scan 100MB+ for repeated substrings (slow) |
| `FRACTAL_ZIP_WEB_REF_SEARCH` | **off** | Search-engine piece discovery (last resort; bot-safe) |
| `FRACTAL_ZIP_WEB_REF_SEARCH_PROVIDER` | `duckduckgo` | DDG HTML only unless `ALLOW_FALLBACK=1` |
| `FRACTAL_ZIP_WEB_REF_SEARCH_MAX` | `4` | Max searches per probe run |
| `FRACTAL_ZIP_WEB_REF_SEARCH_MIN_DELAY_SEC` | `10` | Min seconds between live searches |
| `FRACTAL_ZIP_WEB_REF_SEARCH_MIN_QUERY` | `64` | Min quoted phrase length |
| `FRACTAL_ZIP_WEB_REF_SEARCH_RESULT_LIMIT` | `2` | Top result URLs to verify per search |
| `FRACTAL_ZIP_SHT_ROOT` | `/srv/http/sht` | Shortener bootstrap |
| `FRACTAL_ZIP_LIVE_BROWSER_ROOT` | `/srv/http/live_browser` | Mirror pipeline |

## PAQ-class inner (world-record)

See [WORLD_RECORD_PRESET.md](WORLD_RECORD_PRESET.md). External tools via [fractal_zip_paq.php](../fractal_zip_paq.php):

- `FRACTAL_ZIP_PAQ_NATIVE_COMPARE=1`
- `FRACTAL_ZIP_PAQ_TOOLS=phda9:paq8px:cmix`
- `FRACTAL_ZIP_MAX_ZPAQ_INNER_BYTES=0` (world-record preset)

## Related

- [sht README](/srv/http/sht/README.md)
- [live_browser BROWSE_RECORD.md](/srv/http/live_browser/docs/BROWSE_RECORD.md)
- FFS: shared sha256 namespace (`ffs-hash:` stubs) — see [ffs/WEB_REF_BRIDGE.md](/srv/http/ffs/WEB_REF_BRIDGE.md)

## Benchmarks

```bash
php benchmarks/bench_enwik8_web_ref_probe.php   # content-based piece probe
php benchmarks/estimate_web_ref_potential.php   # raw apply + whole-page headroom (set FRACTAL_ZIP_WEB_REF_WHOLE_PAGE_MAX)
php benchmarks/compare_enwik8_four_way.php
bash benchmarks/run_enwik8_web_ref_track.sh     # full encode with WEB_REF=1
```
