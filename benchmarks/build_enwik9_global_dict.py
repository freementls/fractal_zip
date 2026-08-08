#!/usr/bin/env python3
"""Build enwik9-global dictionary candidates from token frequencies.

Reads enwik9 (or a prefix), counts lowercase a-z words, emits .dic files
within dictW bound (<=44515 words; tier boundaries 80/3920/44880).

Usage:
  python3 benchmarks/build_enwik9_global_dict.py \\
    --enwik9 tools/hutter/data/enwik9 \\
    --out-dir benchmarks/.ladder_cache/dicts \\
    --variants prune0,prune1,reorder
"""
from __future__ import annotations

import argparse
import re
from collections import Counter
from pathlib import Path

MAX_WORDS = 44515
BOUNDARY1 = 80
BOUNDARY2 = BOUNDARY1 + 3840  # 3920
BOUNDARY3 = BOUNDARY2 + 40960  # 44880


def tokenize_words(data: bytes) -> Counter[str]:
    text = data.decode("utf-8", errors="ignore")
    words = re.findall(r"[A-Za-z]+", text)
    ctr: Counter[str] = Counter()
    for w in words:
        lw = w.lower()
        if lw.isalpha() and lw.isascii():
            ctr[lw] += 1
    return ctr


def load_english_dic(path: Path) -> list[str]:
    lines = path.read_text().splitlines()
    return [ln.strip() for ln in lines if ln.strip()]


def write_dic(words: list[str], out: Path) -> None:
    if len(words) > MAX_WORDS:
        raise ValueError(f"dict too large: {len(words)} > {MAX_WORDS}")
    out.parent.mkdir(parents=True, exist_ok=True)
    out.write_text("\n".join(words) + "\n")


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--enwik9", type=Path, required=True)
    ap.add_argument("--english", type=Path,
                    default=Path("tools/hutter/fx2-cmix/dictionary/english.dic"))
    ap.add_argument("--out-dir", type=Path, required=True)
    ap.add_argument("--max-read", type=int, default=0,
                    help="read only first N bytes (0 = all)")
    ap.add_argument("--variants", default="prune0,prune1,reorder")
    args = ap.parse_args()

    data = args.enwik9.read_bytes()
    if args.max_read > 0:
        data = data[: args.max_read]
    freq = tokenize_words(data)
    base = load_english_dic(args.english)
    base_set = set(base)

    variants = [v.strip() for v in args.variants.split(",") if v.strip()]
    out_dir = args.out_dir
    out_dir.mkdir(parents=True, exist_ok=True)

    # prune0: drop base words with zero enwik9 hits
    if "prune0" in variants:
        pruned = [w for w in base if freq.get(w, 0) > 0]
        write_dic(pruned, out_dir / "enwik9_global_prune0.dic")
        print(f"prune0: {len(base)} -> {len(pruned)} words")

    # prune1: drop words with <=1 hit (more aggressive)
    if "prune1" in variants:
        pruned = [w for w in base if freq.get(w, 0) > 1]
        write_dic(pruned, out_dir / "enwik9_global_prune1.dic")
        print(f"prune1: {len(base)} -> {len(pruned)} words")

    # reorder: sort by descending enwik9 frequency (keep same word set as base)
    if "reorder" in variants:
        scored = sorted(base, key=lambda w: (-freq.get(w, 0), w))
        write_dic(scored, out_dir / "enwik9_global_reorder.dic")
        print(f"reorder: {len(scored)} words (freq-sorted tiers)")

    # topN fill: enwik9 top tokens not in base, append up to MAX_WORDS
    if "topfill" in variants:
        extras = [w for w, _ in freq.most_common() if w not in base_set]
        merged = base + extras
        merged = merged[:MAX_WORDS]
        write_dic(merged, out_dir / "enwik9_global_topfill.dic")
        print(f"topfill: {len(merged)} words")

    print(f"Wrote variants to {out_dir}")


if __name__ == "__main__":
    main()
