#!/usr/bin/env python3
"""Category/infobox-structured article order (no embeddings).

Sort key: (primary_category_hash_bucket, infobox_type, page_index).
Cheap voyage substitute for hosts without API/GPU. Fair-validate with
run_article_order_fair_probe.sh using LOCAL_PAGES=* _pages.
"""
from __future__ import annotations

import argparse
import hashlib
import re
import subprocess
import sys
import time
from pathlib import Path

REDIRECT_PREFIXES = (
    b"#REDIRECT",
    b"#redirect",
    b"#Redirect",
    b"#REdirect",
    b"{{softredirect",
)

CAT_RE = re.compile(rb"\[\[Category:([^\]|#]+)", re.I)
# {{Infobox foo ...}} or {{infobox_person}} — first template name after Infobox
INFOBOX_RE = re.compile(rb"\{\{\s*[Ii]nfobox[_\s]*([A-Za-z0-9_\- ]{0,40})", re.I)


def parse_articles(path: Path):
    data = path.read_bytes()
    parts = data.split(b"<page>")
    out = []
    for page_index, chunk in enumerate(parts[1:]):
        text_start = chunk.find(b'<text xml:space="preserve">')
        text_end = chunk.find(b"</text>")
        if text_start < 0 or text_end < 0:
            continue
        body = chunk[text_start + 29 : text_end]
        if len(body) >= 2 and body[0:1] == b"#" and body[1:2] in (b"R", b"r"):
            continue
        if any(body.startswith(p) for p in REDIRECT_PREFIXES):
            continue
        cats = [m.group(1).strip().lower() for m in CAT_RE.finditer(body)]
        primary = cats[0] if cats else b""
        ib = INFOBOX_RE.search(body[:8000])
        infobox = ib.group(1).strip().lower() if ib else b""
        # bucket primary category into 256 bins for coarse clustering
        if primary:
            h = hashlib.blake2b(primary, digest_size=2).digest()
            cat_bucket = h[0]
        else:
            cat_bucket = 255
        ib_key = (infobox.decode("utf-8", "replace")[:24] if infobox else "")
        out.append((page_index, cat_bucket, ib_key, primary.decode("utf-8", "replace")[:40]))
    return out


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--enwik9", default="/srv/http/fractal_zip/tools/hutter/data/enwik9")
    ap.add_argument(
        "--out",
        default="/srv/http/fractal_zip/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_category",
    )
    args = ap.parse_args()

    t0 = time.time()
    print("parsing…", flush=True)
    arts = parse_articles(Path(args.enwik9))
    print(f"articles={len(arts)} parse={time.time()-t0:.1f}s", flush=True)

    # Sort: category bucket → infobox type → original page index
    arts.sort(key=lambda a: (a[1], a[2], a[0]))
    order_pages = [a[0] for a in arts]

    raw_out = Path(str(args.out) + "_pages")
    raw_out.write_text("\n".join(map(str, order_pages)) + "\n")
    print(f"wrote {raw_out} n={len(order_pages)}", flush=True)

    n_with_cat = sum(1 for a in arts if a[1] != 255)
    n_with_ib = sum(1 for a in arts if a[2])
    print(f"with_category={n_with_cat} with_infobox={n_with_ib}", flush=True)

    remap_bin = Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix/remap")
    if not remap_bin.is_file():
        remap_bin = Path("/srv/http/fractal_zip/tools/hutter/fx2-cmix/run/remap")
    outp = Path(args.out)
    if remap_bin.is_file():
        with outp.open("w") as fout:
            subprocess.check_call([str(remap_bin), str(raw_out), args.enwik9], stdout=fout)
        print(f"remapped -> {outp} bytes={outp.stat().st_size}", flush=True)
    else:
        print("WARN: remap binary missing; only _pages written", flush=True)

    print(f"DONE in {time.time()-t0:.1f}s", flush=True)
    return 0


if __name__ == "__main__":
    sys.exit(main())
