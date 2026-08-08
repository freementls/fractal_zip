#!/usr/bin/env python3
"""Resume dup127 N=2000 S2 arms from existing blob+local dic (skip enwik9 reread)."""
from __future__ import annotations

import json
import os
import subprocess
import time

ROOT = "/srv/http/fractal_zip"
WD = f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab/dup127_n2000"
BIN = f"{ROOT}/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b"
GDICT = f"{ROOT}/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
OUT = f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab/dup127_amortize_n2000.json"
BLOB = f"{WD}/cluster_127.bin"
LDIC = f"{WD}/cluster_127.dic"
LOCAL_S1 = 11615  # measured this run
GLOBAL_S1 = 101630
FULL_CLUSTER = 32743
N = 2000


def cmix_c(dic: str, inp: str, out: str) -> tuple[int, float]:
    t0 = time.time()
    rc = subprocess.run(
        [BIN, "-c", dic, inp, out],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    dt = time.time() - t0
    if rc.returncode != 0 or not os.path.isfile(out) or os.path.getsize(out) == 0:
        raise SystemExit(f"cmix failed rc={rc.returncode} out={out}")
    return os.path.getsize(out), dt


def main() -> None:
    os.environ["FXCM_RECIPE_MIXER_BITMASK"] = "2"
    for k in ("FXCM_RECIPE_AXIS1_CAUSAL", "FXCM_PROFILE_MAP_PATH"):
        os.environ.pop(k, None)

    assert os.path.getsize(BLOB) > 0, BLOB
    assert os.path.getsize(LDIC) > 0, LDIC
    # clear partial
    for p in (f"{WD}/cluster_127_global.fx2", f"{WD}/cluster_127_local.fx2"):
        if os.path.isfile(p):
            os.remove(p)
        t = p + ".cmix.temp"
        if os.path.isfile(t):
            os.remove(t)

    print(f"blob={os.path.getsize(BLOB)} local_s1={LOCAL_S1}", flush=True)
    print("cmix global...", flush=True)
    s2_g, t_g = cmix_c(GDICT, BLOB, f"{WD}/cluster_127_global.fx2")
    print(f"  S2_global={s2_g} ({t_g:.0f}s)", flush=True)
    print("cmix local...", flush=True)
    s2_l, t_l = cmix_c(LDIC, BLOB, f"{WD}/cluster_127_local.fx2")
    print(f"  S2_local={s2_l} ({t_l:.0f}s)", flush=True)

    d = s2_l - s2_g
    d_per = d / N
    be = int((-LOCAL_S1 / d_per) + 0.999) if d_per < 0 else None
    proj = LOCAL_S1 + d_per * FULL_CLUSTER
    row = {
        "global_dict": {"path": GDICT, "cmix_n": GLOBAL_S1, "note": "banked"},
        "clusters": [
            {
                "cluster_id": 127,
                "kind": "dup",
                "n_articles": N,
                "blob_bytes": os.path.getsize(BLOB),
                "local_dict": {"words": 3982, "cmix_n": LOCAL_S1},
                "s2_global": s2_g,
                "s2_local": s2_l,
                "s2_delta": d,
                "s2_delta_per_art": d_per,
                "break_even_arts": be,
                "proj_net_at_full": proj,
                "seconds_global": t_g,
                "seconds_local": t_l,
                "note": "resumed after capture_output deadlock fix",
            }
        ],
        "aggregate": {
            "sum_local_s1_proxy": LOCAL_S1,
            "sum_local_s2": s2_l,
            "sum_global_s2": s2_g,
            "global_s1_once": GLOBAL_S1,
            "adaptive_delta_vs_global_joint": LOCAL_S1 + d,
            "verdict_sample": "LOCAL" if (LOCAL_S1 + d) < 0 else "GLOBAL",
            "verdict_proj_full": "LOCAL" if proj < 0 else "GLOBAL",
        },
    }
    json.dump(row, open(OUT, "w"), indent=2)
    print(
        f"ΔS2={d} Δ/art={d_per:.3f} be={be} proj@full={proj:.0f} "
        f"sample_net={LOCAL_S1+d} → {row['aggregate']['verdict_proj_full']}",
        flush=True,
    )
    print("wrote", OUT, flush=True)
    # run analyzer
    subprocess.run(
        [
            "python3",
            f"{ROOT}/benchmarks/analyze_fractal_local_dicts.py",
            OUT,
            "--cluster-tsv",
            f"{ROOT}/benchmarks/.ladder_cache/fractal_clusters/enwik9_article_clusters_full.tsv",
        ],
        check=False,
    )


if __name__ == "__main__":
    main()
