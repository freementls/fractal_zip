#!/usr/bin/env python3
"""Profile C mid-gate: entity_decode wire+matched+sidecar vs B0 raw+entityfold."""
from __future__ import annotations

import argparse
import importlib.util
import json
import os
import time

ROOT = "/srv/http/fractal_zip"
EF_S1_BANKED = 101630


def load_lr():
    spec = importlib.util.spec_from_file_location(
        "lr", f"{ROOT}/benchmarks/run_fractal_lossy_repair_lab.py"
    )
    lr = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(lr)
    return lr


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--dir", required=True)
    ap.add_argument(
        "--cmix",
        default=f"{ROOT}/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b",
    )
    ap.add_argument(
        "--entityfold",
        default=f"{ROOT}/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic",
    )
    ap.add_argument(
        "--skip-b0",
        action="store_true",
        help="Skip raw+entityfold S2 (use --b0-s2)",
    )
    ap.add_argument("--b0-s2", type=int, default=0)
    args = ap.parse_args()
    lr = load_lr()
    os.environ["FXCM_RECIPE_MIXER_BITMASK"] = "2"
    for k in ("FXCM_RECIPE_AXIS1_CAUSAL", "FXCM_PROFILE_MAP_PATH"):
        os.environ.pop(k, None)

    meta = json.load(open(f"{args.dir}/meta.json"))
    raw = open(meta["raw_path"], "rb").read()
    wire = open(meta["wire_path"], "rb").read()

    wire_words = lr.mine_dict(wire, 10**9)
    wire_dic = f"{args.dir}/wire_matched.dic"
    open(wire_dic, "w").write("\n".join(wire_words) + "\n")

    print("S1 wire matched...", flush=True)
    blob = open(wire_dic, "rb").read()
    c = lr.cmix_n(blob, args.cmix)
    z = lr.zstd19(blob)
    fz = lr.fz_sidecar_size(blob, args.dir)
    opts = {k: v for k, v in (("cmix_n", c), ("zstd19", z), ("fz", fz)) if v is not None}
    wire_k = min(opts, key=opts.get)
    wire_s1 = opts[wire_k]
    print(f"  {wire_s1} ({wire_k}) words={len(wire_words)}", flush=True)

    man = f"{args.dir}/entity_manifests.bin"
    sc_fz = 0
    if os.path.isfile(man) and os.path.getsize(man) > 0:
        sc_fz = lr.fz_sidecar_size(open(man, "rb").read(), args.dir) or 0
    print(f"sidecar_fz={sc_fz}", flush=True)

    t0 = time.time()
    print("cmix wire+matched (Profile C)...", flush=True)
    s2_c = lr.cmix_c(args.cmix, wire_dic, wire)
    print(f"  S2={s2_c} ({time.time() - t0:.0f}s)", flush=True)

    if args.skip_b0 and args.b0_s2:
        s2_b0 = args.b0_s2
        print(f"banked B0 S2={s2_b0}", flush=True)
    else:
        t0 = time.time()
        print("cmix raw+entityfold (B0)...", flush=True)
        s2_b0 = lr.cmix_c(args.cmix, args.entityfold, raw)
        print(f"  S2={s2_b0} ({time.time() - t0:.0f}s)", flush=True)

    joint_c = (wire_s1 or 0) + (s2_c or 0) + sc_fz
    joint_b0 = EF_S1_BANKED + (s2_b0 or 0)
    out = {
        "meta": meta,
        "dicts": {
            "wire_matched": {
                "words": len(wire_words),
                "best": wire_k,
                "bytes": wire_s1,
                "opts": opts,
            },
            "entityfold": {"best": "cmix_n_banked", "bytes": EF_S1_BANKED},
        },
        "s2": {"wire_matched": s2_c, "raw_entityfold": s2_b0},
        "sidecar_fz": sc_fz,
        "joints": {"profile_c": joint_c, "b0": joint_b0},
        "delta_c_vs_b0": joint_c - joint_b0,
        "delta_s2_c_vs_b0": (s2_c or 0) - (s2_b0 or 0),
        "verdict_c_vs_b0": "KEEP" if joint_c < joint_b0 else "REJECT",
    }
    json.dump(out, open(f"{args.dir}/result.json", "w"), indent=2)
    print(
        f"C joint={joint_c} B0 joint={joint_b0} Δ={out['delta_c_vs_b0']} "
        f"→ {out['verdict_c_vs_b0']}",
        flush=True,
    )
    print("wrote", f"{args.dir}/result.json", flush=True)


if __name__ == "__main__":
    main()
