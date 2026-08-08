#!/usr/bin/env python3
"""Fractal Block Profile System — profile-map (.fxpm) file builder.

Writes the binary auxiliary side-channel fx2-cmix's FXCM_RECIPE_RUNTIME
build reads via FXCM_PROFILE_MAP_PATH (see fxcmv1.cpp
fxcm_profile_map_load()): axes 2/3/4 (dict_cluster_id, budget_tier,
reorder_window_id), one record per article in physical stream order.

Format: b"FXPM" + u8 version(=1) + u8 reserved(=0) + u32 record_count (LE),
then record_count x (u16 dict_cluster_id, u16 budget_tier, u16 reorder_window_id).

Two ways to build one:
  --pattern LIST   Repeat a small explicit list of (dict,budget,reorder)
                    triples --count times (for synthetic RT tests).
  --from-clusters PATH --count N
                    Read a Phase 2 cluster TSV (nonredirect_idx, cluster_id,
                    kind) and use cluster_id as dict_cluster_id for the
                    first N articles (budget_tier/reorder_window_id left 0
                    until Phase 4c/4d assign them for real).
"""
from __future__ import annotations

import argparse
import struct
import sys


def write_fxpm(path: str, records: list[tuple[int, int, int]]) -> None:
    with open(path, "wb") as f:
        f.write(b"FXPM")
        f.write(struct.pack("<BBI", 1, 0, len(records)))
        for d, b, r in records:
            f.write(struct.pack("<HHH", d & 0xFFFF, b & 0xFFFF, r & 0xFFFF))


def read_fxpm(path: str) -> list[tuple[int, int, int]]:
    with open(path, "rb") as f:
        magic = f.read(4)
        if magic != b"FXPM":
            raise ValueError(f"bad magic {magic!r}")
        ver, rsv, count = struct.unpack("<BBI", f.read(6))
        out = []
        for _ in range(count):
            d, b, r = struct.unpack("<HHH", f.read(6))
            out.append((d, b, r))
        return out


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--out")
    ap.add_argument("--pattern", type=str, help="e.g. '0,0,0;5,1,0;9,2,0'")
    ap.add_argument("--count", type=int, default=0, help="repeat pattern / truncate clusters to this many records")
    ap.add_argument("--from-clusters", type=str, help="Phase 2 cluster TSV path")
    ap.add_argument("--dump", type=str, help="print an existing .fxpm file instead of writing one")
    args = ap.parse_args()

    if args.dump:
        for i, rec in enumerate(read_fxpm(args.dump)):
            print(i, rec)
        return

    if not args.out:
        print("need --out (unless using --dump)", file=sys.stderr)
        sys.exit(1)

    if args.pattern:
        triples = []
        for chunk in args.pattern.split(";"):
            d, b, r = (int(x) for x in chunk.split(","))
            triples.append((d, b, r))
        n = args.count or len(triples)
        records = [triples[i % len(triples)] for i in range(n)]
    elif args.from_clusters:
        records = []
        with open(args.from_clusters) as f:
            next(f)  # header
            for line in f:
                idx, cid, kind = line.strip().split("\t")
                records.append((int(cid), 0, 0))
                if args.count and len(records) >= args.count:
                    break
    else:
        print("need --pattern or --from-clusters", file=sys.stderr)
        sys.exit(1)

    write_fxpm(args.out, records)
    print(f"wrote {args.out}: {len(records)} records")


if __name__ == "__main__":
    main()
