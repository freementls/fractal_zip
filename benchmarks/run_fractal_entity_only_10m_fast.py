#!/usr/bin/env python3
"""entity_only @10MB: bank raw S2 from wiki_lom_cmix_10m; measure wire arm only."""
from __future__ import annotations

import importlib.util
import json
import os
import time

ROOT = "/srv/http/fractal_zip"
OD = f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab/wiki_lom_entity_only_10m"
BIN = f"{ROOT}/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b"


def main() -> None:
    spec = importlib.util.spec_from_file_location(
        "lr", f"{ROOT}/benchmarks/run_fractal_lossy_repair_lab.py"
    )
    lr = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(lr)
    os.environ["FXCM_RECIPE_MIXER_BITMASK"] = "2"
    for k in ("FXCM_RECIPE_AXIS1_CAUSAL", "FXCM_PROFILE_MAP_PATH"):
        os.environ.pop(k, None)

    meta = json.load(open(f"{OD}/meta.json"))
    wire = open(meta["wire_path"], "rb").read()
    wire_dic = f"{OD}/wire_matched.dic"
    words = [ln.strip() for ln in open(wire_dic) if ln.strip()]
    print(f"reuse wire dict {len(words)}", flush=True)

    print("S1 wire...", flush=True)
    blob = open(wire_dic, "rb").read()
    c = lr.cmix_n(blob, BIN)
    z = lr.zstd19(blob)
    fz = lr.fz_sidecar_size(blob, OD)
    opts = {k: v for k, v in (("cmix_n", c), ("zstd19", z), ("fz", fz)) if v is not None}
    wire_k = min(opts, key=opts.get)
    wire_s1 = opts[wire_k]
    print(f"  {wire_s1} ({wire_k})", flush=True)

    raw_s1, s2_raw = 149162, 1667999
    print(f"banked raw S1={raw_s1} S2={s2_raw}", flush=True)

    t0 = time.time()
    print("cmix wire+matched...", flush=True)
    s2_wire = lr.cmix_c(BIN, wire_dic, wire)
    print(f"  S2={s2_wire} ({time.time() - t0:.0f}s)", flush=True)

    sc = open(f"{OD}/entity_manifests.bin", "rb").read()
    sc_fz = lr.fz_sidecar_size(sc, OD) or 0
    out = {
        "meta": meta,
        "dicts": {
            "raw_matched": {
                "best": "cmix_n_banked",
                "bytes": raw_s1,
                "note": "from wiki_lom_cmix_10m",
            },
            "wire_matched": {
                "words": len(words),
                "best": wire_k,
                "bytes": wire_s1,
                "opts": opts,
            },
        },
        "s2": {"raw_matched": s2_raw, "wire_matched": s2_wire},
        "joints": {
            "raw_matched": raw_s1 + s2_raw,
            "wire_matched": wire_s1 + (s2_wire or 0),
        },
        "note": "raw arm banked from wiki_lom_cmix_10m (identical raw_pages.bin)",
        "sidecar_fz": sc_fz,
    }
    out["delta_wire_vs_raw_matched"] = out["joints"]["wire_matched"] - out["joints"]["raw_matched"]
    out["delta_s2_matched"] = (s2_wire or 0) - s2_raw
    out["rate_s2_matched_vs_raw"] = out["delta_s2_matched"] / meta["raw_bytes"]
    out["verdict_vs_matched"] = (
        "KEEP" if out["delta_wire_vs_raw_matched"] < 0 else "REJECT"
    )
    out["joint_delta_with_sidecar"] = out["delta_wire_vs_raw_matched"] + sc_fz
    out["verdict_with_sidecar"] = (
        "KEEP" if out["joint_delta_with_sidecar"] < 0 else "REJECT"
    )
    json.dump(out, open(f"{OD}/result.json", "w"), indent=2)
    print(
        f"ΔS2={out['delta_s2_matched']} Δjoint={out['delta_wire_vs_raw_matched']} "
        f"sc={sc_fz} fair={out['joint_delta_with_sidecar']} → {out['verdict_with_sidecar']}",
        flush=True,
    )
    print(f"rate={out['rate_s2_matched_vs_raw'] * 100:.3f}%", flush=True)
    print("wrote", f"{OD}/result.json", flush=True)


if __name__ == "__main__":
    main()
