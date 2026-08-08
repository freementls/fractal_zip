#!/usr/bin/env python3
"""Real cmix -c screen for the greedy dense_rank KEEP_PROXY arm.

Builds the top-N save-ranked fold (default 128 — greedy best fairΔ=-113 @1MB),
compresses codebook+dicts with fz (fallback zstd/cmix -n), runs:
  baseline: cmix -c raw_matched_dict  raw_slice
  lossy:    cmix -c lossy_matched_dict lossy_body
joint Δ = (dic_lossy + cb + s2_lossy) - (dic_raw + s2_raw)
"""
from __future__ import annotations

import argparse
import importlib.util
import json
import os
import time
from collections import Counter

ROOT = "/srv/http/fractal_zip"


def load_lr():
    spec = importlib.util.spec_from_file_location(
        "lr", f"{ROOT}/benchmarks/run_fractal_lossy_repair_lab.py"
    )
    lr = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(lr)
    return lr


def build_greedy_fold(lr, freq, n_types, min_len, min_freq):
    cands = []
    for w, n in freq.items():
        if n < min_freq or len(w) < min_len or not w.islower() or len(w) <= 4:
            continue
        cands.append(((len(w) - 4) * n, w))
    cands.sort(reverse=True)
    fold = {}
    i_tok = 0
    for _, w in cands:
        if len(fold) >= n_types:
            break
        while True:
            tok = lr.dense_token(i_tok)
            i_tok += 1
            if tok not in freq and tok not in fold.values():
                break
            if i_tok > 26 * 26:
                return fold
        fold[w] = tok
    return fold


def s1_best(lr, blob, cmix_bin, wd, try_cmix_n: bool = False):
    """Prefer fz/zstd for small sidecars; cmix -n is slow and rarely wins here."""
    fz = lr.fz_sidecar_size(blob, wd)
    z = lr.zstd19(blob)
    opts = {k: v for k, v in (("fz", fz), ("zstd19", z)) if v is not None}
    if try_cmix_n and cmix_bin:
        c = lr.cmix_n(blob, cmix_bin)
        if c is not None:
            opts["cmix_n"] = c
    best_k = min(opts, key=opts.get)
    return best_k, opts[best_k], opts


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--slice", default=f"{ROOT}/benchmarks/.ladder_cache/enwik8_1m_skip10.mid")
    ap.add_argument("--out", default=f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab/lossy_greedy_cmix.json")
    ap.add_argument("--cmix", default=f"{ROOT}/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b")
    ap.add_argument("--types", type=int, default=128)
    ap.add_argument("--dict-words", type=int, default=8000)
    ap.add_argument("--min-len", type=int, default=10)
    ap.add_argument("--min-freq", type=int, default=3)
    args = ap.parse_args()
    lr = load_lr()
    wd = os.path.dirname(args.out) or "."
    os.makedirs(wd, exist_ok=True)

    # static MIX_NUMLEN
    os.environ.setdefault("FXCM_RECIPE_MIXER_BITMASK", "2")
    for k in ("FXCM_RECIPE_AXIS1_CAUSAL", "FXCM_PROFILE_MAP_PATH"):
        os.environ.pop(k, None)

    data = open(args.slice, "rb").read()
    freq = Counter(m.group(1).decode().lower() for m in lr.WORD_RE.finditer(data))
    fold = build_greedy_fold(lr, freq, args.types, args.min_len, args.min_freq)
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

    assert lr.WORD_RE.sub(un, body) == data, "roundtrip failed"

    pairs = sorted(((s, w) for w, s in fold.items()), key=lambda t: t[0])
    cb = lr.pack_front_coded_alpha([f"{s}:{w}" for s, w in pairs])
    raw_dic_path = os.path.join(wd, "greedy_raw.dic")
    lossy_dic_path = os.path.join(wd, "greedy_lossy.dic")
    body_path = os.path.join(wd, "greedy_body.bin")
    open(raw_dic_path, "w").write("\n".join(lr.mine_dict(data, args.dict_words)) + "\n")
    open(lossy_dic_path, "w").write("\n".join(lr.mine_dict(body, args.dict_words)) + "\n")
    open(body_path, "wb").write(body)
    open(os.path.join(wd, "greedy_codebook.bin"), "wb").write(cb)

    cb_k, cb_b, cb_opts = s1_best(lr, cb, args.cmix, wd)
    raw_k, raw_b, raw_opts = s1_best(lr, open(raw_dic_path, "rb").read(), args.cmix, wd)
    lossy_k, lossy_b, lossy_opts = s1_best(lr, open(lossy_dic_path, "rb").read(), args.cmix, wd)
    print(f"fold types={len(fold)} cb={cb_b}({cb_k}) raw_dic={raw_b}({raw_k}) "
          f"lossy_dic={lossy_b}({lossy_k})", flush=True)

    t0 = time.time()
    print("cmix -c baseline...", flush=True)
    s2_base = lr.cmix_c(args.cmix, raw_dic_path, data)
    t1 = time.time()
    print(f"  S2 base={s2_base} ({t1 - t0:.0f}s)", flush=True)
    print("cmix -c lossy...", flush=True)
    s2_lossy = lr.cmix_c(args.cmix, lossy_dic_path, body)
    t2 = time.time()
    print(f"  S2 lossy={s2_lossy} ({t2 - t1:.0f}s)", flush=True)

    joint_base = raw_b + (s2_base or 0)
    joint_lossy = lossy_b + cb_b + (s2_lossy or 0)
    out = {
        "slice": args.slice,
        "types": len(fold),
        "s1": {
            "codebook": {"best": cb_k, "bytes": cb_b, "opts": cb_opts},
            "raw_dict": {"best": raw_k, "bytes": raw_b, "opts": raw_opts},
            "lossy_dict": {"best": lossy_k, "bytes": lossy_b, "opts": lossy_opts},
        },
        "s2_base": s2_base,
        "s2_lossy": s2_lossy,
        "s2_delta": (s2_lossy - s2_base) if (s2_base is not None and s2_lossy is not None) else None,
        "joint_base": joint_base,
        "joint_lossy": joint_lossy,
        "delta_joint": joint_lossy - joint_base,
        "seconds": {"base": t1 - t0, "lossy": t2 - t1},
        "verdict": (
            "KEEP_CMIX" if joint_lossy < joint_base else "REJECT_CMIX"
        ) if s2_base is not None else "FAIL",
    }
    json.dump(out, open(args.out, "w"), indent=2)
    print(f"joint base={joint_base} lossy={joint_lossy} Δ={out['delta_joint']} → {out['verdict']}", flush=True)
    print("wrote", args.out, flush=True)


if __name__ == "__main__":
    main()
