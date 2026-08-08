#!/usr/bin/env python3
"""Phase 4c: real trial-compress dictionary-tier comparison per content-type
sample (axis 2).

For each {dictionary tier} x {content-type sample} combination, does a real
`-c` compress and records:
  - S1 proxy: the dictionary file's own zstd-19 size (a stable, cheap proxy
    for "cost of shipping this dictionary in the archive" -- the real S1
    contribution also depends on how the *whole-corpus* dictionary is
    delta-coded against the banked one, which is out of scope for a
    per-sample probe; this proxy is enough to rank tiers by size order,
    which is monotonic with tier by construction).
  - S2: the real compressed sample size for that content type with that
    dictionary.

This reproduces the earlier-session VocabTrim finding (trimmed dicts save S1
but cost S2) *per content type*, to check the hypothesis that short/simple
("stub") samples lose little S2 from a trimmed dictionary while longer
("feature") samples need the full vocabulary -- i.e. whether dict_cluster_id
should differ by content type rather than being forced globally in one
direction.
"""
import argparse
import json
import os
import subprocess
import time

ROOT = "/srv/http/fractal_zip"
DICT_DIR = f"{ROOT}/benchmarks/.ladder_cache/dicts"
BINARY = f"{ROOT}/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2"

TIERS = [
    ("full", f"{DICT_DIR}/english_entityfold_e9.dic"),
    ("top40000", f"{DICT_DIR}/english_entityfold_top40000.dic"),
    ("top30000", f"{DICT_DIR}/english_entityfold_top30000.dic"),
    ("top20000", f"{DICT_DIR}/english_entityfold_top20000.dic"),
    ("top15000", f"{DICT_DIR}/english_entityfold_top15000.dic"),
    ("top10000", f"{DICT_DIR}/english_entityfold_top10000.dic"),
]


def zsize(path):
    out = path + ".zst.tmp"
    subprocess.run(["zstd", "-19", "-f", "-q", path, "-o", out], check=True)
    n = os.path.getsize(out)
    os.remove(out)
    return n


def compress_one(dict_path, input_path, out_path):
    t0 = time.time()
    proc = subprocess.run([BINARY, "-c", dict_path, input_path, out_path],
                           capture_output=True, text=True)
    dt = time.time() - t0
    sz = os.path.getsize(out_path) if os.path.exists(out_path) else None
    return sz, dt, proc.returncode


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--samples", required=True,
                     help="comma-separated tag=path pairs, e.g. stub=/tmp/p4c_stub.mid,feature=/tmp/p4c_feature.mid")
    ap.add_argument("--out", required=True)
    args = ap.parse_args()

    samples = {}
    for pair in args.samples.split(","):
        tag, path = pair.split("=", 1)
        samples[tag] = path

    dict_sizes = {}
    for tag, path in TIERS:
        dict_sizes[tag] = zsize(path)
        print(f"dict[{tag}] zstd19_size={dict_sizes[tag]}", flush=True)

    results = {"dict_sizes": dict_sizes, "samples": {}}
    for sample_tag, sample_path in samples.items():
        results["samples"][sample_tag] = []
        for tier_tag, dict_path in TIERS:
            out_path = f"/tmp/p4c_{sample_tag}_{tier_tag}.fx2"
            sz, dt, rc = compress_one(dict_path, sample_path, out_path)
            joint_proxy = sz + dict_sizes[tier_tag] if sz is not None else None
            print(f"sample={sample_tag} tier={tier_tag}: S2={sz} S1proxy={dict_sizes[tier_tag]} "
                  f"joint_proxy={joint_proxy} ({dt:.1f}s, rc={rc})", flush=True)
            results["samples"][sample_tag].append({
                "tier": tier_tag, "s2": sz, "s1_proxy": dict_sizes[tier_tag],
                "joint_proxy": joint_proxy, "seconds": dt,
            })
            with open(args.out, "w") as f:
                json.dump(results, f, indent=2)
    print("done ->", args.out)


if __name__ == "__main__":
    main()
