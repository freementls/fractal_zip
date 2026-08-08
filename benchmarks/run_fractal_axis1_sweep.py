#!/usr/bin/env python3
"""Phase 4a: real offline grid search for axis-1 (mixer recipe) causal
thresholds, fitted against actual measured compressed size on a real enwik8
sample (not a simulated/replayed mixer fork -- see .hutter_fractal_block_profile.md
for why a direct real-compress sweep was chosen over a full state-fork replay
harness for this phase).

Sweeps FXCM_AXIS1_DENS_MIN / FXCM_AXIS1_ZOOM_MIN / FXCM_AXIS1_MASK_WHEN_FIRED
(the causal decision-rule knobs already wired into fxcmv1.cpp's
fxcm_effective_mixer_bitmask()) against a real enwik8 slice, one real
`-c` compress per candidate, and records the resulting byte count.

Usage:
  python3 run_fractal_axis1_sweep.py --binary <path> --input <path> --out <json>
    [--dens 0,3,5] [--zoom 0,3,5] [--masks 1,2,4,8]
"""
import argparse
import json
import os
import subprocess
import sys
import time

def run_one(binary, input_path, out_path, env_extra, dict_path=None):
    env = os.environ.copy()
    env.update({k: str(v) for k, v in env_extra.items()})
    t0 = time.time()
    args = [binary, "-c"]
    if dict_path:
        args += [dict_path, input_path, out_path]
    else:
        args += [input_path, out_path]
    proc = subprocess.run(args, env=env, capture_output=True, text=True)
    dt = time.time() - t0
    sz = os.path.getsize(out_path) if os.path.exists(out_path) else None
    return sz, dt, proc.returncode


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--binary", required=True)
    ap.add_argument("--input", required=True)
    ap.add_argument("--dict", default=None)
    ap.add_argument("--out", required=True)
    ap.add_argument("--tmp-out", default="/tmp/axis1_sweep_tmp.fx2")
    ap.add_argument("--dens", default="1,3,5")
    ap.add_argument("--zoom", default="1,3,5")
    ap.add_argument("--masks", default="1,2,4,8")
    ap.add_argument("--combo-mask", default=None,
                     help="Optional single combined mask to test at the best dens/zoom of each bit")
    args = ap.parse_args()

    dens_list = [int(x) for x in args.dens.split(",")]
    zoom_list = [int(x) for x in args.zoom.split(",")]
    masks = [int(x) for x in args.masks.split(",")]

    results = []

    print("=== baseline (recipe 0, no axis1 causal rule) ===", flush=True)
    sz, dt, rc = run_one(args.binary, args.input, args.tmp_out, {}, args.dict)
    print(f"baseline: {sz} bytes in {dt:.1f}s (rc={rc})", flush=True)
    results.append({"tag": "baseline", "mask": 0, "dens_min": None,
                     "zoom_min": None, "bytes": sz, "seconds": dt})

    for mask in masks:
        for dens_min in dens_list:
            for zoom_min in zoom_list:
                env_extra = {
                    "FXCM_RECIPE_AXIS1_CAUSAL": 1,
                    "FXCM_AXIS1_DENS_MIN": dens_min,
                    "FXCM_AXIS1_ZOOM_MIN": zoom_min,
                    "FXCM_AXIS1_MASK_WHEN_FIRED": mask,
                }
                sz, dt, rc = run_one(args.binary, args.input, args.tmp_out, env_extra, args.dict)
                delta = (sz - results[0]["bytes"]) if sz is not None else None
                print(f"mask={mask} dens_min={dens_min} zoom_min={zoom_min}: "
                      f"{sz} bytes (delta {delta:+d}) in {dt:.1f}s (rc={rc})", flush=True)
                results.append({"tag": "sweep", "mask": mask, "dens_min": dens_min,
                                 "zoom_min": zoom_min, "bytes": sz, "seconds": dt,
                                 "delta_vs_baseline": delta})
                with open(args.out, "w") as f:
                    json.dump(results, f, indent=2)

    if args.combo_mask is not None:
        combo = int(args.combo_mask)
        # use tightest dens/zoom seen (largest values tested) as a conservative combo point
        dens_min = max(dens_list)
        zoom_min = min(dens_list) if False else min(zoom_list)
        env_extra = {
            "FXCM_RECIPE_AXIS1_CAUSAL": 1,
            "FXCM_AXIS1_DENS_MIN": dens_min,
            "FXCM_AXIS1_ZOOM_MIN": zoom_min,
            "FXCM_AXIS1_MASK_WHEN_FIRED": combo,
        }
        sz, dt, rc = run_one(args.binary, args.input, args.tmp_out, env_extra, args.dict)
        results.append({"tag": "combo", "mask": combo, "dens_min": dens_min,
                         "zoom_min": zoom_min, "bytes": sz, "seconds": dt,
                         "delta_vs_baseline": (sz - results[0]["bytes"]) if sz is not None else None})
        with open(args.out, "w") as f:
            json.dump(results, f, indent=2)

    print("=== done, results written to", args.out, "===", flush=True)


if __name__ == "__main__":
    main()
