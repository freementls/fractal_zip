#!/usr/bin/env python3
"""Lossy-repair + matched dict rebuild vs banked entityfold baseline.

Rebuild dict from lossy body (same tokenizer ranking as local-dict lab),
size-capped to min(n_types_lossy, entityfold_n). Joint:
  base:  comp(entityfold) + cmix(raw|entityfold)
  lossy: comp(matched) + comp(codebook) + cmix(lossy|matched)
"""
from __future__ import annotations
import argparse, importlib.util, json, os, time
from collections import Counter
ROOT="/srv/http/fractal_zip"

def load(n,p):
    s=importlib.util.spec_from_file_location(n,p); m=importlib.util.module_from_spec(s); s.loader.exec_module(m); return m

def main():
    ap=argparse.ArgumentParser()
    ap.add_argument("--slice", default=f"{ROOT}/benchmarks/.ladder_cache/enwik8_1m_skip10.mid")
    ap.add_argument("--types", type=int, default=128)
    ap.add_argument("--dict-words", type=int, default=0, help="0=all types in lossy body")
    ap.add_argument("--cmix", default=f"{ROOT}/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b")
    ap.add_argument("--global-dict", default=f"{ROOT}/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic")
    ap.add_argument("--base-fx2", default=f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab/alpha_screen_base.fx2")
    ap.add_argument("--out", default="")
    args=ap.parse_args()
    out=args.out or f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab/lossy_matched_cmix_t{args.types}.json"
    lr=load("lr", f"{ROOT}/benchmarks/run_fractal_lossy_repair_lab.py")
    arm=load("arm", f"{ROOT}/benchmarks/run_fractal_lossy_cmix_arm.py")
    wd=os.path.dirname(out) or "."
    os.makedirs(wd, exist_ok=True)
    os.environ["FXCM_RECIPE_MIXER_BITMASK"]="2"
    for k in ("FXCM_RECIPE_AXIS1_CAUSAL","FXCM_PROFILE_MAP_PATH"):
        os.environ.pop(k, None)

    data=open(args.slice,"rb").read()
    freq=Counter(m.group(1).decode().lower() for m in lr.WORD_RE.finditer(data))
    fold=arm.build_greedy_fold(lr, freq, args.types, 10, 3)
    body=lr.apply_fold(data, fold)
    rev={s:w for w,s in fold.items()}
    def un(m):
        raw=m.group(1)
        if raw.islower() and raw.decode() in rev: return rev[raw.decode()].encode()
        if raw[:1].isupper() and raw[1:].islower():
            s=raw.decode().lower()
            if s in rev:
                f=rev[s]; return (f[0].upper()+f[1:]).encode()
        return raw
    assert lr.WORD_RE.sub(un,body)==data
    pairs=sorted(((s,w) for w,s in fold.items()), key=lambda t:t[0])
    cb=lr.pack_front_coded_alpha([f"{s}:{w}" for s,w in pairs])
    cb_b=lr.fz_sidecar_size(cb, wd) or lr.zstd19(cb)

    # matched dict: prefer grafting tokens into entityfold list
    gwords=[w for w in open(args.global_dict).read().splitlines() if w]
    folded=set(fold)
    tokens=list(fold.values())
    grafted=[w for w in gwords if w not in folded]
    # put tokens near the front (short codes for frequent tokens — rank by body freq)
    bfreq=Counter(m.group(1).decode().lower() for m in lr.WORD_RE.finditer(body))
    tokens.sort(key=lambda t: -bfreq.get(t,0))
    grafted=tokens + grafted
    # also mine-only arm size
    mined=lr.mine_dict(body, args.dict_words or 10**9)

    g_s1=lr.cmix_n(open(args.global_dict,"rb").read(), args.cmix)
    results={"types":len(fold),"codebook_fz":cb_b,"global_dict_cmix_n":g_s1,"arms":[]}

    if os.path.isfile(args.base_fx2) and os.path.getsize(args.base_fx2)>0:
        s2_base=os.path.getsize(args.base_fx2)
    else:
        print("baseline cmix...", flush=True)
        s2_base=lr.cmix_c(args.cmix, args.global_dict, data)
    joint_base=(g_s1 or 0)+(s2_base or 0)
    print(f"baseline S2={s2_base} S1={g_s1} joint={joint_base}", flush=True)

    for tag, words in (("graft_entityfold", grafted), ("mine_lossy", mined)):
        dic_path=os.path.join(wd, f"lossy_{tag}_t{args.types}.dic")
        open(dic_path,"w").write("\n".join(words)+"\n")
        d_s1=lr.cmix_n(open(dic_path,"rb").read(), args.cmix)
        # also fz
        d_fz=lr.fz_sidecar_size(open(dic_path,"rb").read(), wd)
        d_best=min(x for x in (d_s1, d_fz) if x is not None)
        d_best_k="cmix_n" if d_s1==d_best else "fz"
        print(f"{tag}: words={len(words)} s1_cmix={d_s1} s1_fz={d_fz} best={d_best}", flush=True)
        print(f"cmix -c {tag}...", flush=True)
        t0=time.time()
        s2=lr.cmix_c(args.cmix, dic_path, body)
        print(f"  S2={s2} ({time.time()-t0:.0f}s)", flush=True)
        joint=(d_best or 0)+cb_b+(s2 or 0)
        row={"tag":tag,"words":len(words),"dict_best":d_best,"dict_best_codec":d_best_k,
             "dict_cmix_n":d_s1,"dict_fz":d_fz,"s2":s2,"joint":joint,
             "delta_joint":joint-joint_base}
        results["arms"].append(row)
        print(f"  joint={joint} Δ={row['delta_joint']}", flush=True)
        json.dump(results, open(out,"w"), indent=2)

    results["s2_base"]=s2_base
    results["joint_base"]=joint_base
    best=min(results["arms"], key=lambda r: r["delta_joint"])
    results["best"]=best
    results["verdict"]="KEEP_CMIX" if best["delta_joint"]<0 else "REJECT_CMIX"
    json.dump(results, open(out,"w"), indent=2)
    print(f"BEST {best['tag']} Δ={best['delta_joint']} → {results['verdict']}", flush=True)

if __name__=="__main__":
    main()
