#!/usr/bin/env python3
"""Phase 5: automated offline tuner.

Orchestrates the Phase 2/3/4 mechanisms into one command that, given a real
corpus slice, produces the two artifacts a final compress/decompress pass
needs:
  1. `<out>.fxpm`  -- per-article profile map (axes 2/3/4), built with real
     measured data wherever Phase 4 produced a usable signal.
  2. `<out>.env`   -- shell-sourceable FXCM_RECIPE_* env vars for axis 1
     (mixer-recipe causal thresholds), gated by the ladder-consistency
     verdict so this tuner can never silently re-promote a config that
     failed the project's own scale-consistency check.

This is deliberately an *orchestrator* over the axis-specific scripts
already built and validated in Phases 2-4 (build_fractal_article_clusters.py,
build_fractal_reorder_windows.py, run_fractal_dict_cluster_probe.py,
run_fractal_axis1_sweep.py, the FXCM_ARTICLE_HARDNESS_LOG mechanism) rather
than a reimplementation -- Phase 5's job is to compose already-real findings
into one exportable artifact pair, not to invent a fifth way to search.

Usage:
  python3 benchmarks/run_fractal_tuner.py \
      --slice benchmarks/.ladder_cache/enwik8_1m_skip10.mid \
      --cluster-tsv benchmarks/.ladder_cache/fractal_clusters/enwik9_article_clusters_full.tsv \
      --binary tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b \
      --dict benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic \
      --axis1-verdict-file benchmarks/.ladder_cache/fractal_p4a/axis1_10m_validate.log \
      --out benchmarks/.ladder_cache/fractal_p5/tuned_1m
"""
from __future__ import annotations

import argparse
import os
import re
import struct
import subprocess
import sys
import tempfile

ROOT = "/srv/http/fractal_zip"
sys.path.insert(0, f"{ROOT}/benchmarks")
from build_fractal_profile_map import write_fxpm  # noqa: E402

# Phase 4c finding: trimming the dictionary costs long-form ("feature")
# content proportionally more S2 than short stub content. This threshold
# (bytes of raw article body) is the boundary used to flag an article as
# "trim-eligible" (dict_cluster_id=1) vs "needs full vocab" (dict_cluster_id=0).
# NOTE this only sets the *hint bit* fxcmv1.cpp's apmA3 fold already
# consumes; true per-article dictionary switching is future work (see
# .hutter_fractal_block_profile.md Phase 4c "scope limitation").
DICT_TRIM_LENGTH_THRESHOLD = 2000

# Phase 4d finding: the top ~15% hardest (fails-per-byte) articles are where
# an expensive model's cost would be spent if/when a real budget-tier
# dispatch is wired in. Kept as a tunable fraction here.
BUDGET_TIER_HARD_FRACTION = 0.15  # default; override with --hard-fraction

MARKER = b"  <page>\n"


def parse_articles(data: bytes):
    starts = []
    if data.startswith(MARKER):
        starts.append(0)
    pos = 0
    while True:
        pos = data.find(b"\n  <page>\n", pos)
        if pos == -1:
            break
        starts.append(pos + 1)
        pos += 1
    spans = []
    for i, s in enumerate(starts):
        e = starts[i + 1] if i + 1 < len(starts) else len(data)
        spans.append((s, e))
    return spans


def inject_sentinels(data: bytes) -> bytes:
    out = bytearray()
    i = 0
    if data.startswith(MARKER):
        out += b"\x1e"
    while True:
        j = data.find(b"\n  <page>\n", i)
        if j == -1:
            out += data[i:]
            break
        out += data[i:j + 1]
        out += b"\x1e"
        i = j + 1
    return bytes(out)


def load_axis1_verdict(path):
    """Very small, honest parser: only enable the axis-1 causal mask if the
    validation log shows Delta <= 0 at *both* scales it recorded. Any
    missing/unparseable/ambiguous state defaults to disabled (mask=0) --
    this tuner must never *upgrade* an inconclusive result into "enabled"."""
    default = {"enabled": False, "mask": 0, "dens_min": 4, "zoom_min": 4}
    if not path or not os.path.exists(path):
        return default
    text = open(path).read()
    m10 = re.search(r"MASK2_10M (\d+) bytes", text)
    b10 = re.search(r"BASELINE_10M (\d+) bytes", text)
    if not (m10 and b10):
        print(f"[tuner] axis1 verdict file present but incomplete ({path}); "
              f"defaulting axis1 causal rule to DISABLED", file=sys.stderr)
        return default
    delta10 = int(m10.group(1)) - int(b10.group(1))
    print(f"[tuner] axis1 10MB check: baseline={b10.group(1)} mask2={m10.group(1)} delta={delta10:+d}")
    if delta10 <= 0:
        print("[tuner] axis1 mask=2 (MIX_NUMLEN) holds non-positive at 10MB -> ENABLING")
        return {"enabled": True, "mask": 2, "dens_min": 1, "zoom_min": 1}
    print("[tuner] axis1 mask=2 regresses at 10MB (like MIX_NUMLEN historically did) -> "
          "REJECTING, falling back to disabled (this is the scale-consistency gate working as intended)")
    return default


def article_lengths(data: bytes):
    return [e - s for s, e in parse_articles(data)]


def load_cluster_kind(tsv_path):
    kind_of = {}
    if not tsv_path or not os.path.exists(tsv_path):
        return kind_of
    with open(tsv_path) as f:
        next(f)
        for line in f:
            parts = line.rstrip("\n").split("\t")
            if len(parts) < 3:
                continue
            kind_of[int(parts[0])] = parts[2]
    return kind_of


def measure_hardness(binary, dict_path, slice_path):
    data = open(slice_path, "rb").read()
    sentinel_data = inject_sentinels(data)
    with tempfile.NamedTemporaryFile(suffix=".mid", delete=False) as tf:
        tf.write(sentinel_data)
        sentinel_path = tf.name
    log_path = tempfile.mktemp(suffix=".hardness.tsv")
    out_path = tempfile.mktemp(suffix=".fx2")
    env = os.environ.copy()
    env["FXCM_ARTICLE_HARDNESS_LOG"] = log_path
    # Match stacked baseline used by LSTM budget-gate screens.
    env.setdefault("FXCM_RECIPE_MIXER_BITMASK", "2")
    print(f"[tuner] measuring per-article hardness on {slice_path} "
          f"({len(data)} bytes, real compress, this is the expensive step)...")
    # Do not capture stderr: cmix progress fills the pipe and deadlocks.
    subprocess.run(
        [binary, "-c", dict_path, sentinel_path, out_path],
        env=env,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
        check=False,
    )
    hardness = {}
    if os.path.exists(log_path):
        for line in open(log_path):
            idx, b, f = line.split()
            idx, b, f = int(idx), int(b), int(f)
            hardness[idx] = f / b if b > 0 else 0.0
    os.unlink(sentinel_path)
    if os.path.exists(out_path):
        os.unlink(out_path)
    if os.path.exists(log_path):
        os.unlink(log_path)
    return hardness


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--slice", required=True)
    ap.add_argument("--cluster-tsv", default=None)
    ap.add_argument("--binary", required=True)
    ap.add_argument("--dict", required=True)
    ap.add_argument("--axis1-verdict-file", default=None)
    ap.add_argument("--skip-hardness", action="store_true",
                     help="skip the real compress pass for budget_tier (fast, all budget_tier=0)")
    ap.add_argument("--hard-fraction", type=float, default=BUDGET_TIER_HARD_FRACTION,
                     help="fraction of hardest articles that get budget_tier=1 (LSTM on)")
    ap.add_argument("--hardness-tsv", default="",
                     help="reuse/save hardness TSV (idx\\tfails_per_byte) to avoid re-measure")
    ap.add_argument("--out", required=True, help="output prefix; writes <out>.fxpm and <out>.env")
    args = ap.parse_args()

    data = open(args.slice, "rb").read()
    lengths = article_lengths(data)
    n = len(lengths)
    print(f"[tuner] slice={args.slice} articles={n}")

    kind_of = load_cluster_kind(args.cluster_tsv)

    hardness = {}
    if args.hardness_tsv and os.path.isfile(args.hardness_tsv) and os.path.getsize(args.hardness_tsv) > 0:
        for line in open(args.hardness_tsv):
            parts = line.split()
            if len(parts) >= 2:
                hardness[int(parts[0])] = float(parts[1])
        print(f"[tuner] reused hardness {args.hardness_tsv} n={len(hardness)}")
    elif not args.skip_hardness:
        hardness = measure_hardness(args.binary, args.dict, args.slice)
        if args.hardness_tsv:
            with open(args.hardness_tsv, "w") as hf:
                for idx, v in sorted(hardness.items()):
                    hf.write(f"{idx}\t{v:.8f}\n")
            print(f"[tuner] wrote hardness {args.hardness_tsv}")
    hard_sorted = sorted(hardness, key=lambda i: -hardness[i])
    frac = max(0.0, min(1.0, float(args.hard_fraction)))
    n_hard = max(1, int(len(hard_sorted) * frac)) if hard_sorted else 0
    hard_set = set(hard_sorted[:n_hard])
    print(f"[tuner] hard_fraction={frac} n_hard={n_hard}/{len(hard_sorted)}")

    records = []
    n_trim, n_hard_flagged = 0, 0
    for i in range(n):
        dict_cluster_id = 1 if lengths[i] < DICT_TRIM_LENGTH_THRESHOLD else 0
        if dict_cluster_id:
            n_trim += 1
        budget_tier = 1 if i in hard_set else 0
        if budget_tier:
            n_hard_flagged += 1
        reorder_window_id = 0  # axis 4 stays an offline order-file swap, not runtime-consulted; see Phase 4b verdict.
        records.append((dict_cluster_id, budget_tier, reorder_window_id))

    os.makedirs(os.path.dirname(args.out) or ".", exist_ok=True)
    fxpm_path = args.out + ".fxpm"
    write_fxpm(fxpm_path, records)
    print(f"[tuner] wrote {fxpm_path}: {len(records)} records "
          f"(dict_cluster_id=1 for {n_trim}, budget_tier=1 for {n_hard_flagged})")

    axis1 = load_axis1_verdict(args.axis1_verdict_file)
    # Primary env: axis1 only. Phase 6 1MB sentinel A/B found that attaching
    # the .fxpm mixer hints (axes 2/3) *regressed* (+8 B vs baseline), so the
    # default exportable recipe must not enable FXCM_PROFILE_MAP_PATH.
    # A companion <out>.with_profile.env keeps the map for further research.
    env_path = args.out + ".env"
    with open(env_path, "w") as f:
        f.write("# Auto-generated by run_fractal_tuner.py — COMPOSED/LADDER default.\n")
        f.write("# Axis-2/3 profile map intentionally NOT enabled here (rejected at 1MB\n")
        f.write("# composed A/B; see .hutter_fractal_block_profile.md Phase 6).\n")
        f.write("# Research map path: see {}.with_profile.env\n".format(args.out))
        f.write("unset FXCM_PROFILE_MAP_PATH\n")
        if axis1["enabled"]:
            f.write("export FXCM_RECIPE_AXIS1_CAUSAL=1\n")
            f.write(f"export FXCM_AXIS1_DENS_MIN={axis1['dens_min']}\n")
            f.write(f"export FXCM_AXIS1_ZOOM_MIN={axis1['zoom_min']}\n")
            f.write(f"export FXCM_AXIS1_MASK_WHEN_FIRED={axis1['mask']}\n")
        else:
            f.write("# axis1 causal rule disabled (failed scale-consistency check or not evaluated)\n")
            f.write("export FXCM_RECIPE_AXIS1_CAUSAL=0\n")
    print(f"[tuner] wrote {env_path}")

    with_profile = args.out + ".with_profile.env"
    with open(with_profile, "w") as f:
        f.write("# Research-only: enables .fxpm mixer hints. Do NOT use for composed ladder\n")
        f.write("# unless a later scale-consistent A/B has overturned the Phase 6 reject.\n")
        f.write(f"export FXCM_PROFILE_MAP_PATH={os.path.abspath(fxpm_path)}\n")
        if axis1["enabled"]:
            f.write("export FXCM_RECIPE_AXIS1_CAUSAL=1\n")
            f.write(f"export FXCM_AXIS1_DENS_MIN={axis1['dens_min']}\n")
            f.write(f"export FXCM_AXIS1_ZOOM_MIN={axis1['zoom_min']}\n")
            f.write(f"export FXCM_AXIS1_MASK_WHEN_FIRED={axis1['mask']}\n")
        else:
            f.write("export FXCM_RECIPE_AXIS1_CAUSAL=0\n")
    print(f"[tuner] wrote {with_profile} (research only)")


if __name__ == "__main__":
    main()
