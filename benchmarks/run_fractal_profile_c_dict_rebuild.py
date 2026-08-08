#!/usr/bin/env python3
"""Rebuild Profile C dict from entity_decode wire of enwik8; rescreen mid @10MB vs B0."""
from __future__ import annotations

import argparse
import importlib.util
import json
import os
import subprocess
import time

ROOT = "/srv/http/fractal_zip"
EF_S1 = 101630
B0_S2_10M = 1750867


def load_lr():
    spec = importlib.util.spec_from_file_location(
        "lr", f"{ROOT}/benchmarks/run_fractal_lossy_repair_lab.py"
    )
    lr = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(lr)
    return lr


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument(
        "--enwik",
        default=f"{ROOT}/test_files109/enwik8",
        help="corpus to decode+mine (default enwik8)",
    )
    ap.add_argument("--words", type=int, default=44880)
    ap.add_argument(
        "--outdir",
        default=f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab/profile_c_dict",
    )
    ap.add_argument(
        "--mid-dir",
        default=f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab/profile_c_10m",
    )
    ap.add_argument(
        "--cmix",
        default=f"{ROOT}/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b",
    )
    ap.add_argument("--skip-mine", action="store_true")
    ap.add_argument("--skip-s1", action="store_true")
    ap.add_argument("--skip-s2", action="store_true")
    args = ap.parse_args()
    lr = load_lr()
    os.makedirs(args.outdir, exist_ok=True)
    os.environ["FXCM_RECIPE_MIXER_BITMASK"] = "2"
    for k in ("FXCM_RECIPE_AXIS1_CAUSAL", "FXCM_PROFILE_MAP_PATH"):
        os.environ.pop(k, None)

    wire_path = f"{args.outdir}/enwik8_entity_decode.bin"
    dic_path = f"{args.outdir}/english_entitydecode_e8.dic"
    man_path = f"{args.outdir}/entity_manifests.bin"

    if not args.skip_mine:
        print("PHP entity_decode enwik8...", flush=True)
        subprocess.check_call(
            [
                "php",
                f"{ROOT}/benchmarks/run_fractal_profile_c_mid_gate.php",
                f"--in={args.enwik}",
                f"--outdir={args.outdir}/wire_build",
            ]
        )
        # mid_gate writes wire.mid / entity_manifests.bin
        os.replace(f"{args.outdir}/wire_build/wire.mid", wire_path)
        os.replace(f"{args.outdir}/wire_build/entity_manifests.bin", man_path)
        print(f"wire={os.path.getsize(wire_path)} sc={os.path.getsize(man_path)}", flush=True)
        print(f"mine top {args.words}...", flush=True)
        words = lr.mine_dict(open(wire_path, "rb").read(), args.words)
        open(dic_path, "w").write("\n".join(words) + "\n")
        print(f"wrote {dic_path} words={len(words)}", flush=True)
    else:
        words = [ln.strip() for ln in open(dic_path) if ln.strip()]
        print(f"reuse dict words={len(words)}", flush=True)

    s1 = None
    if not args.skip_s1:
        print("S1 cmix -n on C dict...", flush=True)
        s1 = lr.cmix_n(open(dic_path, "rb").read(), args.cmix)
        print(f"  S1={s1}", flush=True)
    else:
        s1 = json.load(open(f"{args.outdir}/dict_meta.json")).get("s1")

    # Sidecar for mid screen: use mid-dir manifests (already measured) or rebuild fz
    mid_meta = json.load(open(f"{args.mid_dir}/meta.json"))
    mid_wire = open(mid_meta["wire_path"], "rb").read()
    mid_man = f"{args.mid_dir}/entity_manifests.bin"
    sc_fz = lr.fz_sidecar_size(open(mid_man, "rb").read(), args.outdir) or 0

    s2 = None
    if not args.skip_s2:
        t0 = time.time()
        print("cmix C-dict on mid wire...", flush=True)
        s2 = lr.cmix_c(args.cmix, dic_path, mid_wire)
        print(f"  S2={s2} ({time.time() - t0:.0f}s)", flush=True)

    joint_c = (s1 or 0) + (s2 or 0) + sc_fz
    joint_b0 = EF_S1 + B0_S2_10M
    out = {
        "dict_path": dic_path,
        "dict_words": len(words),
        "s1": s1,
        "mid": mid_meta,
        "sidecar_fz_mid": sc_fz,
        "s2_mid": s2,
        "joints": {"profile_c_globaldict": joint_c, "b0": joint_b0},
        "delta_c_vs_b0": joint_c - joint_b0,
        "verdict": "KEEP" if joint_c < joint_b0 else "REJECT",
        "note": "C dict mined on full enwik8 entity_decode wire; S2 on enwik8_10m mid wire",
    }
    json.dump(out, open(f"{args.outdir}/result_10m_mid.json", "w"), indent=2)
    json.dump({"s1": s1, "words": len(words)}, open(f"{args.outdir}/dict_meta.json", "w"), indent=2)
    print(
        f"C(global) joint={joint_c} B0={joint_b0} Δ={out['delta_c_vs_b0']} → {out['verdict']}",
        flush=True,
    )


if __name__ == "__main__":
    main()
