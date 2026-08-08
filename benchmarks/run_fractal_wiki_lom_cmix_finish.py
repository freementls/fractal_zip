#!/usr/bin/env python3
"""Finish wiki_lom cmix screen: mine matched dicts, cmix -c raw vs wire, fz S1."""
from __future__ import annotations

import argparse
import importlib.util
import json
import os
import time

ROOT = "/srv/http/fractal_zip"


def load_lr():
    spec = importlib.util.spec_from_file_location(
        "lr", f"{ROOT}/benchmarks/run_fractal_lossy_repair_lab.py"
    )
    lr = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(lr)
    return lr


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--dir", default=f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab/wiki_lom_cmix")
    ap.add_argument("--cmix", default=f"{ROOT}/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b")
    ap.add_argument("--entityfold", default=f"{ROOT}/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic")
    ap.add_argument("--dict-words", type=int, default=0, help="0 = all types")
    ap.add_argument(
        "--skip-entityfold",
        action="store_true",
        help="Skip raw+entityfold S2 (Profile B0 dict is info-only for Profile C)",
    )
    ap.add_argument(
        "--reuse-dicts",
        action="store_true",
        help="Reuse existing raw_matched.dic / wire_matched.dic and skip S1 if result partial",
    )
    args = ap.parse_args()
    lr = load_lr()
    os.environ["FXCM_RECIPE_MIXER_BITMASK"] = "2"
    for k in ("FXCM_RECIPE_AXIS1_CAUSAL", "FXCM_PROFILE_MAP_PATH"):
        os.environ.pop(k, None)

    meta = json.load(open(f"{args.dir}/meta.json"))
    raw = open(meta["raw_path"], "rb").read()
    wire = open(meta["wire_path"], "rb").read()
    raw_dic = f"{args.dir}/raw_matched.dic"
    wire_dic = f"{args.dir}/wire_matched.dic"
    if args.reuse_dicts and os.path.isfile(raw_dic) and os.path.isfile(wire_dic):
        raw_dic_w = [ln.strip() for ln in open(raw_dic) if ln.strip()]
        wire_dic_w = [ln.strip() for ln in open(wire_dic) if ln.strip()]
        print(f"reused dicts raw={len(raw_dic_w)} wire={len(wire_dic_w)}", flush=True)
    else:
        n = args.dict_words or 10**9
        raw_dic_w = lr.mine_dict(raw, n)
        wire_dic_w = lr.mine_dict(wire, n)
        open(raw_dic, "w").write("\n".join(raw_dic_w) + "\n")
        open(wire_dic, "w").write("\n".join(wire_dic_w) + "\n")

    def s1(path):
        blob = open(path, "rb").read()
        c = lr.cmix_n(blob, args.cmix)
        z = lr.zstd19(blob)
        fz = lr.fz_sidecar_size(blob, args.dir)
        opts = {k: v for k, v in (("cmix_n", c), ("zstd19", z), ("fz", fz)) if v is not None}
        k = min(opts, key=opts.get)
        return k, opts[k], opts

    print("S1 raw matched...", flush=True)
    raw_k, raw_s1, raw_opts = s1(raw_dic)
    print(f"  {raw_s1} ({raw_k}) words={len(raw_dic_w)}", flush=True)
    print("S1 wire matched...", flush=True)
    wire_k, wire_s1, wire_opts = s1(wire_dic)
    print(f"  {wire_s1} ({wire_k}) words={len(wire_dic_w)}", flush=True)
    # Banked entityfold cmix -n (dict_compress lab); skip re-running ~100KB -n.
    ef_k, ef_s1, ef_opts = "cmix_n_banked", 101630, {"cmix_n_banked": 101630}
    print(f"S1 entityfold banked {ef_s1}", flush=True)

    t0 = time.time()
    print("cmix raw+matched...", flush=True)
    s2_raw = lr.cmix_c(args.cmix, raw_dic, raw)
    print(f"  S2={s2_raw} ({time.time()-t0:.0f}s)", flush=True)
    t0 = time.time()
    print("cmix wire+matched...", flush=True)
    s2_wire = lr.cmix_c(args.cmix, wire_dic, wire)
    print(f"  S2={s2_wire} ({time.time()-t0:.0f}s)", flush=True)
    s2_ef = None
    if not args.skip_entityfold:
        t0 = time.time()
        print("cmix raw+entityfold...", flush=True)
        s2_ef = lr.cmix_c(args.cmix, args.entityfold, raw)
        print(f"  S2={s2_ef} ({time.time()-t0:.0f}s)", flush=True)

    out = {
        "meta": meta,
        "dicts": {
            "raw_matched": {"words": len(raw_dic_w), "best": raw_k, "bytes": raw_s1, "opts": raw_opts},
            "wire_matched": {"words": len(wire_dic_w), "best": wire_k, "bytes": wire_s1, "opts": wire_opts},
            "entityfold": {"best": ef_k, "bytes": ef_s1, "opts": ef_opts},
        },
        "s2": {"raw_matched": s2_raw, "wire_matched": s2_wire},
        "joints": {
            "raw_matched": (raw_s1 or 0) + (s2_raw or 0),
            "wire_matched": (wire_s1 or 0) + (s2_wire or 0),
        },
    }
    if s2_ef is not None:
        out["s2"]["raw_entityfold"] = s2_ef
        out["joints"]["raw_entityfold"] = (ef_s1 or 0) + (s2_ef or 0)
        out["delta_wire_vs_entityfold"] = out["joints"]["wire_matched"] - out["joints"]["raw_entityfold"]
    out["delta_wire_vs_raw_matched"] = out["joints"]["wire_matched"] - out["joints"]["raw_matched"]
    out["delta_s2_matched"] = (s2_wire or 0) - (s2_raw or 0)
    out["verdict_vs_matched"] = (
        "KEEP" if out["delta_wire_vs_raw_matched"] < 0 else "REJECT"
    )
    # Compact entity-manifest sidecar if present beside the run dir.
    man = f"{args.dir}/entity_manifests.bin"
    if os.path.isfile(man):
        blob = open(man, "rb").read()
        fz = lr.fz_sidecar_size(blob, args.dir) or 0
        out["sidecar_fz"] = fz
        out["joint_delta_with_sidecar"] = out["delta_wire_vs_raw_matched"] + fz
        out["verdict_with_sidecar"] = (
            "KEEP" if out["joint_delta_with_sidecar"] < 0 else "REJECT"
        )
    json.dump(out, open(f"{args.dir}/result.json", "w"), indent=2)
    print(
        f"joint matched raw={out['joints']['raw_matched']} wire={out['joints']['wire_matched']} "
        f"Δ={out['delta_wire_vs_raw_matched']} → {out['verdict_vs_matched']}",
        flush=True,
    )
    if "delta_wire_vs_entityfold" in out:
        print(
            f"joint vs entityfold: wire={out['joints']['wire_matched']} "
            f"ef={out['joints']['raw_entityfold']} Δ={out['delta_wire_vs_entityfold']}",
            flush=True,
        )
    if "sidecar_fz" in out:
        print(
            f"sidecar_fz={out['sidecar_fz']} fairΔ={out['joint_delta_with_sidecar']} "
            f"→ {out['verdict_with_sidecar']}",
            flush=True,
        )
    print("wrote", f"{args.dir}/result.json", flush=True)


if __name__ == "__main__":
    main()
