#!/usr/bin/env python3
"""Per-cluster local dictionary lab — the fractal answer to VocabTrim.

Global VocabTrim was REJECTED because one trimmed dict hurts long-form S2 more
than it saves S1. The fractal hypothesis (user): *local* dictionaries for
*collocated, similar* content are smaller, compress better (more repetition
inside the dict itself), and match the local vocabulary so S2 does not swamp
the S1 win.

For each selected Phase-2 cluster this script:
  1. Extracts the cluster's articles from enwik9 (physical <page> spans)
  2. Mines a frequency-ranked local word list (same lowercase-alpha tokenizer
     Dictionary::Encode uses)
  3. Writes a local .dic (newline-separated, code-order = freq rank)
  4. Measures dict S1 proxies: raw / zstd-19 / cmix -n
  5. Runs real fx2-cmix -c with (a) global entityfold dict (b) local dict on
     the same cluster blob — records S2
  6. Reports joint = S1_proxy + S2 for each, and the delta vs global

Also emits a concatenated "multi-local" joint estimate:
  sum_i (cmix(local_dict_i) + cmix(cluster_i | local_dict_i))
vs
  cmix(global_dict)  [counted once] + sum_i cmix(cluster_i | global_dict)

Usage:
  python3 benchmarks/run_fractal_local_dict_lab.py \\
      --cluster-tsv benchmarks/.ladder_cache/fractal_clusters/enwik9_article_clusters_full.tsv \\
      --max-clusters 6 --min-cluster-size 200 --max-articles-per-cluster 80 \\
      --binary tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b \\
      --global-dict benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic \\
      --out benchmarks/.ladder_cache/fractal_dictlab/local_dicts.json
"""
from __future__ import annotations

import argparse
import json
import os
import re
import subprocess
import tempfile
import time
from collections import Counter

ROOT = "/srv/http/fractal_zip"
ENWIK9 = f"{ROOT}/tools/hutter/data/enwik9"

REDIRECT_PREFIXES = (
    b"      <text xml:space=\"preserve\">#REDIRECT",
    b"      <text xml:space=\"preserve\">#redirect",
    b"      <text xml:space=\"preserve\">#Redirect",
    b"      <text xml:space=\"preserve\">#REdirect",
    b"      <text xml:space=\"preserve\">{{softredirect",
)
WORD_RE = re.compile(rb"[A-Za-z]+")


def log(msg: str) -> None:
    print(msg, flush=True)


def parse_articles(data: bytes):
    starts = []
    if data.startswith(b"  <page>\n"):
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
        body = data[s:e]
        is_redir = any((b"\n" + p) in body or body.startswith(p) for p in REDIRECT_PREFIXES)
        spans.append((s, e, is_redir))
    return spans


def build_remap(spans):
    remap, n = {}, 0
    for i, (_, _, redir) in enumerate(spans):
        if not redir:
            remap[n] = i
            n += 1
    return remap, n


def tokenize_words(body: bytes) -> list[str]:
    return [m.group(0).lower().decode("ascii") for m in WORD_RE.finditer(body)]


def mine_local_dict(bodies: list[bytes], max_words: int) -> list[str]:
    ctr: Counter[str] = Counter()
    for b in bodies:
        ctr.update(tokenize_words(b))
    # Rank by (freq * len) savings proxy — longer common words save more code bytes
    ranked = sorted(ctr.items(), key=lambda kv: (-kv[1] * max(1, len(kv[0]) - 1), -kv[1], kv[0]))
    words = [w for w, _ in ranked[:max_words] if len(w) >= 2]
    return words


def write_dic(path: str, words: list[str]) -> None:
    with open(path, "wb") as f:
        for w in words:
            f.write(w.encode("ascii") + b"\n")


def zstd19_size(path: str) -> int:
    out = path + ".zst.tmp"
    subprocess.run(["zstd", "-19", "-f", "-q", path, "-o", out], check=True)
    n = os.path.getsize(out)
    os.remove(out)
    return n


def cmix_n_size(cmix: str, path: str) -> int | None:
    out = path + ".cmix_n"
    # Do not capture_output: cmix progress spam fills the pipe and deadlocks.
    rc = subprocess.run(
        [cmix, "-n", path, out],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    if rc.returncode != 0 or not os.path.isfile(out):
        return None
    n = os.path.getsize(out)
    os.remove(out)
    return n


def cmix_c_size(cmix: str, dic: str, inp: str, out: str) -> tuple[int | None, float]:
    t0 = time.time()
    # DEVNULL stderr — progress spam + capture_output deadlocks after ~64KB.
    rc = subprocess.run(
        [cmix, "-c", dic, inp, out],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    dt = time.time() - t0
    if rc.returncode != 0 or not os.path.isfile(out) or os.path.getsize(out) == 0:
        return None, dt
    return os.path.getsize(out), dt


def load_clusters(tsv: str):
    by_cluster: dict[int, list[int]] = {}
    kind_of: dict[int, str] = {}
    with open(tsv) as f:
        next(f)
        for line in f:
            parts = line.rstrip("\n").split("\t")
            if len(parts) < 3:
                continue
            nr, cid, kind = int(parts[0]), int(parts[1]), parts[2]
            by_cluster.setdefault(cid, []).append(nr)
            kind_of[cid] = kind
    return by_cluster, kind_of


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--cluster-tsv", required=True)
    ap.add_argument("--binary", required=True)
    ap.add_argument("--global-dict", required=True)
    ap.add_argument("--out", required=True)
    ap.add_argument("--max-clusters", type=int, default=6)
    ap.add_argument("--min-cluster-size", type=int, default=200)
    ap.add_argument("--max-articles-per-cluster", type=int, default=80)
    ap.add_argument("--local-dict-words", type=int, default=4000)
    ap.add_argument("--kinds", default="dup,content",
                    help="Comma list of cluster kinds to include (dup,content)")
    ap.add_argument("--workdir", default=f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab")
    ap.add_argument(
        "--global-s1-banked",
        type=int,
        default=0,
        help="Skip cmix -n on global dict; use this banked S1 (e.g. 101630)",
    )
    args = ap.parse_args()
    os.makedirs(args.workdir, exist_ok=True)

    log(f"reading {ENWIK9}")
    data = open(ENWIK9, "rb").read()
    spans = parse_articles(data)
    remap, n_non = build_remap(spans)
    log(f"spans={len(spans)} non_redirect={n_non}")

    by_cluster, kind_of = load_clusters(args.cluster_tsv)
    want_kinds = {k.strip() for k in args.kinds.split(",") if k.strip()}
    # Prefer a mix: largest content clusters + largest dup clusters
    candidates = []
    for cid, members in by_cluster.items():
        if len(members) < args.min_cluster_size:
            continue
        kind = kind_of.get(cid, "?")
        if kind not in want_kinds:
            continue
        candidates.append((kind, len(members), cid))
    candidates.sort(key=lambda t: (0 if t[0] == "dup" else 1, -t[1]))
    if want_kinds == {"dup"} or want_kinds == {"content"}:
        selected = candidates[: args.max_clusters]
    else:
        # take half dup, half content
        dups = [c for c in candidates if c[0] == "dup"][: max(1, args.max_clusters // 2)]
        contents = [c for c in candidates if c[0] == "content"][
            : max(1, args.max_clusters - len(dups))
        ]
        selected = dups + contents
    log(f"selected clusters: {[(k, n, cid) for k, n, cid in selected]}")

    g_raw = os.path.getsize(args.global_dict)
    g_z = zstd19_size(args.global_dict)
    if args.global_s1_banked > 0:
        g_c = args.global_s1_banked
        log(f"global_dict raw={g_raw} zstd19={g_z} cmix_n={g_c} (banked)")
    else:
        g_c = cmix_n_size(args.binary, args.global_dict)
        log(f"global_dict raw={g_raw} zstd19={g_z} cmix_n={g_c}")

    results = {
        "global_dict": {"path": args.global_dict, "raw": g_raw, "zstd19": g_z, "cmix_n": g_c},
        "clusters": [],
    }
    sum_local_s1 = 0
    sum_local_s2 = 0
    sum_global_s2 = 0

    for kind, n_memb, cid in selected:
        members = by_cluster[cid][: args.max_articles_per_cluster]
        bodies = []
        blob = bytearray()
        for nr in members:
            raw_i = remap[nr]
            s, e, _ = spans[raw_i]
            body = data[s:e]
            bodies.append(body)
            blob += body
        blob_path = os.path.join(args.workdir, f"cluster_{cid}.bin")
        open(blob_path, "wb").write(blob)
        words = mine_local_dict(bodies, args.local_dict_words)
        dic_path = os.path.join(args.workdir, f"cluster_{cid}.dic")
        write_dic(dic_path, words)
        d_raw = os.path.getsize(dic_path)
        d_z = zstd19_size(dic_path)
        d_c = cmix_n_size(args.binary, dic_path)
        log(f"cluster {cid} kind={kind} arts={len(members)} blob={len(blob)} "
            f"local_dict words={len(words)} raw={d_raw} zstd19={d_z} cmix_n={d_c}")

        out_g = os.path.join(args.workdir, f"cluster_{cid}_global.fx2")
        out_l = os.path.join(args.workdir, f"cluster_{cid}_local.fx2")
        s2_g, t_g = cmix_c_size(args.binary, args.global_dict, blob_path, out_g)
        s2_l, t_l = cmix_c_size(args.binary, dic_path, blob_path, out_l)
        s1_proxy = d_c if d_c is not None else d_z
        joint_l = (s1_proxy or 0) + (s2_l or 0)
        # For per-cluster comparison, charge a *share* of global dict? No —
        # report both: S2-only delta, and joint with full local S1 vs zero
        # incremental S1 for global (global already paid once).
        row = {
            "cluster_id": cid,
            "kind": kind,
            "n_articles": len(members),
            "blob_bytes": len(blob),
            "local_dict": {"words": len(words), "raw": d_raw, "zstd19": d_z, "cmix_n": d_c},
            "s2_global": s2_g,
            "s2_local": s2_l,
            "s2_delta": (s2_l - s2_g) if (s2_l is not None and s2_g is not None) else None,
            "joint_local_s1proxy_plus_s2": joint_l,
            "seconds_global": t_g,
            "seconds_local": t_l,
        }
        results["clusters"].append(row)
        log(f"  S2 global={s2_g} local={s2_l} Δ={row['s2_delta']} "
            f"joint_local≈{joint_l} ({t_g:.0f}s/{t_l:.0f}s)")
        if s2_l is not None and s1_proxy is not None:
            sum_local_s1 += s1_proxy
            sum_local_s2 += s2_l
        if s2_g is not None:
            sum_global_s2 += s2_g
        json.dump(results, open(args.out, "w"), indent=2)

    g_s1 = g_c if g_c is not None else g_z
    # Adaptive: local only when sample net (local_s1 + s2_delta) < 0, else
    # prefer dup-kind with negative s2_delta (amortization candidate).
    adapt_extra_s1 = 0
    adapt_s2 = 0
    adapt_choices = []
    for row in results["clusters"]:
        d = row.get("s2_delta")
        s2g, s2l = row.get("s2_global"), row.get("s2_local")
        loc = (row.get("local_dict") or {}).get("cmix_n")
        if d is None or s2g is None or s2l is None or loc is None:
            continue
        net = loc + d
        # Prefer local when S2 improves on dup clusters (amortize later), or
        # when sample net already negative.
        use = (net < 0) or (row.get("kind") == "dup" and d < 0)
        if use:
            adapt_extra_s1 += loc
            adapt_s2 += s2l
            adapt_choices.append({"cluster_id": row["cluster_id"], "policy": "local", "net_sample": net})
        else:
            adapt_s2 += s2g
            adapt_choices.append({"cluster_id": row["cluster_id"], "policy": "global", "net_sample": net})

    results["aggregate"] = {
        "sum_local_s1_proxy": sum_local_s1,
        "sum_local_s2": sum_local_s2,
        "sum_local_joint": sum_local_s1 + sum_local_s2,
        "global_s1_once": g_s1,
        "sum_global_s2": sum_global_s2,
        "global_joint_on_same_blobs": (g_s1 or 0) + sum_global_s2,
        "delta_multi_local_vs_global_joint":
            (sum_local_s1 + sum_local_s2) - ((g_s1 or 0) + sum_global_s2),
        "adaptive_joint_sample": (g_s1 or 0) + adapt_extra_s1 + adapt_s2,
        "adaptive_delta_vs_global_joint":
            ((g_s1 or 0) + adapt_extra_s1 + adapt_s2) - ((g_s1 or 0) + sum_global_s2),
        "adaptive_choices": adapt_choices,
        "note": (
            "multi-local joint ships one cmix'd local dict PER cluster; "
            "global joint ships the ONE global dict once + S2 on each blob. "
            "adaptive uses local on dup clusters with negative s2_delta (or "
            "any cluster with sample net<0). Sample-scale adaptive still "
            "charges full local S1 — see analyze_fractal_local_dicts.py for "
            "full-cluster amortization projections."
        ),
    }
    log(f"AGGREGATE multi-local joint={results['aggregate']['sum_local_joint']} "
        f"global joint={results['aggregate']['global_joint_on_same_blobs']} "
        f"Δ={results['aggregate']['delta_multi_local_vs_global_joint']}")
    json.dump(results, open(args.out, "w"), indent=2)
    log(f"wrote {args.out}")


if __name__ == "__main__":
    main()
