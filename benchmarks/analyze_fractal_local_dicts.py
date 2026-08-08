#!/usr/bin/env python3
"""Post-process local_dicts.json: adaptive policy + amortization projections.

Local S1 often exceeds S2 savings on a 40-article sample. The fractal bet is
amortization: one local dict paid once across the full cluster membership.
"""
from __future__ import annotations

import argparse
import json
import sys


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("json_path")
    ap.add_argument("--cluster-tsv", default="")
    args = ap.parse_args()
    data = json.load(open(args.json_path))
    clusters = data.get("clusters") or []
    g = data.get("global_dict") or {}
    g_s1 = g.get("cmix_n") or g.get("zstd19") or 0

    full_size = {}
    if args.cluster_tsv:
        with open(args.cluster_tsv) as f:
            next(f)
            for line in f:
                parts = line.rstrip("\n").split("\t")
                if len(parts) < 2:
                    continue
                cid = int(parts[1])
                full_size[cid] = full_size.get(cid, 0) + 1

    print(f"global_s1_once={g_s1}")
    print(f"{'cid':>6} {'kind':8} {'n':>4} {'full':>7} {'s2Δ':>7} {'locS1':>7} "
          f"{'net@sample':>10} {'be_arts':>8} {'proj_net@full':>14}")

    sum_s2_g = 0
    sum_s2_adapt = 0
    sum_extra_s1 = 0
    for c in clusters:
        cid = c["cluster_id"]
        kind = c["kind"]
        n = c["n_articles"]
        s2g, s2l = c.get("s2_global"), c.get("s2_local")
        d = c.get("s2_delta")
        loc_s1 = (c.get("local_dict") or {}).get("cmix_n") or 0
        if s2g is None or s2l is None or d is None:
            continue
        sum_s2_g += s2g
        full = full_size.get(cid, n)
        per_art = d / max(1, n)
        # Break-even articles where local_s1 + n*per_art <= 0
        be = int((-loc_s1 / per_art) + 0.999) if per_art < 0 else None
        # Adaptive: use local only if projected full-cluster net < 0
        proj = loc_s1 + per_art * full
        use_local = proj < 0 and kind == "dup"
        if use_local:
            sum_s2_adapt += s2l  # sample-scale; for reporting only
            sum_extra_s1 += loc_s1
            policy = "LOCAL"
        else:
            sum_s2_adapt += s2g
            policy = "GLOBAL"
        net_sample = loc_s1 + d
        print(f"{cid:6d} {kind:8} {n:4d} {full:7d} {d:7d} {loc_s1:7d} "
              f"{net_sample:10d} {str(be) if be is not None else 'n/a':>8} "
              f"{proj:14.0f}  {policy}")

    print()
    print("Sample-scale joints (does NOT amortize; pessimistic):")
    print(f"  all-global:  s1={g_s1} + s2={sum_s2_g} = {g_s1 + sum_s2_g}")
    print(f"  all-local:   see lab aggregate")
    print(f"  adaptive(sample S2, extra local S1 on chosen): "
          f"s1={g_s1 + sum_extra_s1} + s2={sum_s2_adapt} = "
          f"{g_s1 + sum_extra_s1 + sum_s2_adapt}")
    print()
    print("Amortization note: proj_net@full = local_s1 + (s2Δ/n)*full_cluster_size.")
    print("If proj_net@full < 0, paying the local dict once for the whole cluster wins.")


if __name__ == "__main__":
    main()
