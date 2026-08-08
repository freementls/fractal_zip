#!/usr/bin/env python3
"""Lossy-repair cmix screen against banked entityfold dict (consistent S2 baseline).

Unlike run_fractal_lossy_cmix_arm.py (slice-matched 8k dicts), this keeps
english_entityfold_e9.dic on both arms so ΔS2 is comparable to the promoted
static-MIX_NUMLEN baseline (~198791 @1MB). Joint delta = ΔS2 + codebook_fz
(global dict S1 unchanged).
"""
from __future__ import annotations

import argparse
import importlib.util
import json
import os
import time
from collections import Counter

ROOT = "/srv/http/fractal_zip"


def load(name, path):
    spec = importlib.util.spec_from_file_location(name, path)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--slice", default=f"{ROOT}/benchmarks/.ladder_cache/enwik8_1m_skip10.mid")
    ap.add_argument("--types", type=int, default=128)
    ap.add_argument("--out", default="")
    ap.add_argument("--cmix", default=f"{ROOT}/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b")
    ap.add_argument("--dict", default=f"{ROOT}/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic")
    ap.add_argument("--base-fx2", default=f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab/alpha_screen_base.fx2",
                    help="Reuse banked S2 if present (same binary+dict+env)")
    args = ap.parse_args()
    out = args.out or (
        f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab/lossy_entityfold_cmix_t{args.types}.json"
    )
    lr = load("lr", f"{ROOT}/benchmarks/run_fractal_lossy_repair_lab.py")
    arm = load("arm", f"{ROOT}/benchmarks/run_fractal_lossy_cmix_arm.py")
    wd = os.path.dirname(out) or "."
    os.makedirs(wd, exist_ok=True)

    os.environ["FXCM_RECIPE_MIXER_BITMASK"] = "2"
    for k in ("FXCM_RECIPE_AXIS1_CAUSAL", "FXCM_PROFILE_MAP_PATH"):
        os.environ.pop(k, None)

    data = open(args.slice, "rb").read()
    freq = Counter(m.group(1).decode().lower() for m in lr.WORD_RE.finditer(data))
    fold = arm.build_greedy_fold(lr, freq, args.types, 10, 3)
    body = lr.apply_fold(data, fold)
    rev = {s: w for w, s in fold.items()}

    def un(m):
        raw = m.group(1)
        if raw.islower() and raw.decode() in rev:
            return rev[raw.decode()].encode()
        if raw[:1].isupper() and raw[1:].islower():
            s = raw.decode().lower()
            if s in rev:
                f = rev[s]
                return (f[0].upper() + f[1:]).encode()
        return raw

    assert lr.WORD_RE.sub(un, body) == data
    pairs = sorted(((s, w) for w, s in fold.items()), key=lambda t: t[0])
    cb = lr.pack_front_coded_alpha([f"{s}:{w}" for s, w in pairs])
    cb_b = lr.fz_sidecar_size(cb, wd) or lr.zstd19(cb)
    print(f"types={len(fold)} cb_fz={cb_b} body_rawΔ={len(body) - len(data)}", flush=True)

    if os.path.isfile(args.base_fx2) and os.path.getsize(args.base_fx2) > 0:
        s2_base = os.path.getsize(args.base_fx2)
        print(f"S2 base reused {args.base_fx2}={s2_base}", flush=True)
    else:
        print("cmix -c baseline...", flush=True)
        t0 = time.time()
        s2_base = lr.cmix_c(args.cmix, args.dict, data)
        print(f"  S2 base={s2_base} ({time.time() - t0:.0f}s)", flush=True)

    print("cmix -c lossy + entityfold...", flush=True)
    t0 = time.time()
    s2_lossy = lr.cmix_c(args.cmix, args.dict, body)
    print(f"  S2 lossy={s2_lossy} ({time.time() - t0:.0f}s)", flush=True)

    d_s2 = (s2_lossy - s2_base) if (s2_base is not None and s2_lossy is not None) else None
    d_j = (d_s2 + cb_b) if d_s2 is not None else None
    verdict = "KEEP_CMIX" if (d_j is not None and d_j < 0) else "REJECT_CMIX"
    row = {
        "types": len(fold),
        "codebook_fz": cb_b,
        "s2_base": s2_base,
        "s2_lossy": s2_lossy,
        "s2_delta": d_s2,
        "delta_joint_vs_base": d_j,
        "verdict": verdict,
        "note": "Same entityfold dict both arms; only extra S1 is codebook.",
    }
    json.dump(row, open(out, "w"), indent=2)
    print(f"ΔS2={d_s2} +cb={cb_b} → jointΔ={d_j} {verdict}", flush=True)
    print("wrote", out, flush=True)


if __name__ == "__main__":
    main()
