#!/usr/bin/env python3
"""Inject/strip FXCM_ARTICLE_SENTINEL (0x1E) at each '  <page>\\n' start.

Production profile-map / budget-tier switching needs a marker that survives
dictionary preprocess (literal '<page>' does not — see
.hutter_fractal_block_profile.md Phase 3). The offline article-reorder /
mid-slice builders should inject this byte; decode-side restore strips it.

Usage:
  python3 inject_article_sentinels.py inject IN OUT
  python3 inject_article_sentinels.py strip IN OUT
"""
from __future__ import annotations

import sys

SENTINEL = b"\x1e"
MARKER = b"  <page>\n"
NL_MARKER = b"\n  <page>\n"


def inject(data: bytes) -> bytes:
    out = bytearray()
    i = 0
    if data.startswith(MARKER):
        out += SENTINEL
    while True:
        j = data.find(NL_MARKER, i)
        if j < 0:
            out += data[i:]
            break
        out += data[i : j + 1]  # keep the newline before the page
        out += SENTINEL
        i = j + 1
    return bytes(out)


def strip(data: bytes) -> bytes:
    return data.replace(SENTINEL, b"")


def main() -> None:
    if len(sys.argv) != 4 or sys.argv[1] not in ("inject", "strip"):
        print(__doc__, file=sys.stderr)
        sys.exit(2)
    op, inp, outp = sys.argv[1], sys.argv[2], sys.argv[3]
    data = open(inp, "rb").read()
    result = inject(data) if op == "inject" else strip(data)
    open(outp, "wb").write(result)
    n = result.count(SENTINEL) if op == "inject" else data.count(SENTINEL)
    print(f"{op}: {len(data)} -> {len(result)} bytes (sentinel_count={n})")


if __name__ == "__main__":
    main()
