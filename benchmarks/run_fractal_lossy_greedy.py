#!/usr/bin/env python3
"""Greedy knapsack over dense_rank types: add longest/frequent words while
marginal analytic save exceeds estimated marginal codebook cost.

Uses fz (preferred) / zstd for codebook+dict S1 and zstd for body — same fair
joint as run_fractal_lossy_repair_lab.py. Purpose: hunt a subset with fairΔ<0
before spending cmix -c RAM.
"""
from __future__ import annotations

import argparse
import importlib.util
import json
import os
from collections import Counter

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
    ap.add_argument("--slice", required=True)
    ap.add_argument("--out", required=True)
    ap.add_argument("--dict-words", type=int, default=8000)
    ap.add_argument("--min-len", type=int, default=10)
    ap.add_argument("--min-freq", type=int, default=3)
    ap.add_argument("--max-types", type=int, default=400)
    args = ap.parse_args()
    lr = load_lr()
    wd = os.path.dirname(args.out) or "."
    os.makedirs(wd, exist_ok=True)

    data = open(args.slice, "rb").read()
    freq = Counter(m.group(1).decode().lower() for m in lr.WORD_RE.finditer(data))
    base_body = lr.zstd19(data)
    raw_dic = ("\n".join(lr.mine_dict(data, args.dict_words)) + "\n").encode()
    base_s1 = lr.fz_sidecar_size(raw_dic, wd) or lr.zstd19(raw_dic)
    print(f"base_s1={base_s1} base_body={base_body}", flush=True)

    # Rank candidates by save/len (prefer high save, compressible names later)
    cands = []
    for w, n in freq.items():
        if n < args.min_freq or len(w) < args.min_len or not w.islower():
            continue
        if len(w) <= 4:
            continue
        save = (len(w) - 4) * n
        cands.append((save, -len(w), w, n))
    cands.sort(reverse=True)

    fold: dict[str, str] = {}
    used_tok = set()
    history = []
    best = None

    def score(fold_map: dict[str, str]):
        body = lr.apply_fold(data, fold_map)
        rev = {s: w for w, s in fold_map.items()}

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

        if lr.WORD_RE.sub(un, body) != data:
            return None
        pairs = sorted(((s, w) for w, s in fold_map.items()), key=lambda t: t[0])
        cb = lr.pack_front_coded_alpha([f"{s}:{w}" for s, w in pairs])
        cb_b = lr.fz_sidecar_size(cb, wd) or lr.zstd19(cb)
        body_z = lr.zstd19(body)
        dic = ("\n".join(lr.mine_dict(body, args.dict_words)) + "\n").encode()
        dic_b = lr.fz_sidecar_size(dic, wd) or lr.zstd19(dic)
        fair = (dic_b + cb_b + body_z) - (base_s1 + base_body)
        return {
            "fair": fair,
            "body_delta": body_z - base_body,
            "cb": cb_b,
            "dic": dic_b,
            "types": len(fold_map),
            "analytic_save": sum((len(w) - len(s)) * freq[w] for w, s in fold_map.items()),
        }

    # Add in batches; rescore every batch (fz is slow — batch size 32)
    batch = []
    i_tok = 0
    for save, _, w, n in cands:
        if len(fold) >= args.max_types:
            break
        while True:
            tok = lr.dense_token(i_tok)
            i_tok += 1
            if tok not in freq and tok not in used_tok:
                break
            if i_tok > 26 * 26:
                break
        if i_tok > 26 * 26:
            break
        fold[w] = tok
        used_tok.add(tok)
        batch.append(w)
        if len(batch) < 32 and len(fold) < args.max_types:
            continue
        batch.clear()
        sc = score(fold)
        if sc is None:
            print(f"rt fail at types={len(fold)}; stopping", flush=True)
            break
        history.append(dict(sc))
        print(
            f"types={sc['types']} fairΔ={sc['fair']} bodyΔ={sc['body_delta']} "
            f"cb={sc['cb']} dic={sc['dic']} save≈{sc['analytic_save']}",
            flush=True,
        )
        if best is None or sc["fair"] < best["fair"]:
            best = dict(sc)
            best["fold_size"] = len(fold)
        # Early stop if fair rising for 3 consecutive batches after a minimum
        if len(history) >= 4:
            if all(history[-i]["fair"] > history[-i - 1]["fair"] for i in range(1, 4)):
                print("fair worsening; stop greedy", flush=True)
                break

    out = {
        "slice": args.slice,
        "base_s1": base_s1,
        "base_body": base_body,
        "history": history,
        "best": best,
    }
    json.dump(out, open(args.out, "w"), indent=2)
    print("BEST", best, flush=True)
    print("wrote", args.out, flush=True)


if __name__ == "__main__":
    main()
