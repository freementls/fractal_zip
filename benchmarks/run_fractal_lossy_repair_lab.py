#!/usr/bin/env python3
"""Lossy-repair long-word lab for the fx2 fractal construct.

Thesis (user): long words can be *lossily* written as shorter unambiguous
stems (better mixing, shorter dicts) if a small repair codebook restores them
bit-exactly, and if compress(codebook) + cmix(lossy_body) < cmix(original).

This is NOT L1 per-occurrence sidecars (closed — tax always won). It is a
**collected injective vocabulary fold**: only fold types where
stem → full_word is unique in the type set. The codebook is paid once and
compressed with bytes-effective packers (front-code + zstd/xz/cmix; optional
fz), the same discipline fz uses for sidecars.

Dimensions swept (why "more complex than fractal process"):
  - min word length, min frequency
  - stem family: suffix_peel | prefix_keep | vowel_del
  - whether to rebuild a matched dict on the lossy wire
  - codebook packing before the outer compressor

Usage:
  python3 benchmarks/run_fractal_lossy_repair_lab.py \\
      --slice benchmarks/.ladder_cache/enwik8_1m_skip10.mid \\
      --out benchmarks/.ladder_cache/fractal_dictlab/lossy_repair.json
  # add --cmix when RAM free for real S2
"""
from __future__ import annotations

import argparse
import json
import os
import re
import struct
import subprocess
import tempfile
from collections import Counter

ROOT = "/srv/http/fractal_zip"
WORD_RE = re.compile(rb"\b([A-Za-z]{2,})\b")

# Longest-first English suffixes (Porter-ish surface forms).
SUFFIXES = (
    "ational", "ization", "fulness", "ousness", "iveness", "ements",
    "ations", "ators", "ments", "ences", "ances", "ities", "ively",
    "ously", "ally", "ated", "ating", "ation", "ments", "ness", "ment",
    "able", "ible", "ence", "ance", "ship", "tion", "sion", "ious",
    "ical", "less", "full", "ings", "ers", "ies", "ied", "ing", "ion",
    "ity", "est", "ous", "ive", "ize", "ise", "ly", "ed", "es", "er", "s",
)


def log(msg: str) -> None:
    print(msg, flush=True)


def zstd19(data: bytes) -> int:
    with tempfile.NamedTemporaryFile(delete=False) as tf:
        tf.write(data)
        inp = tf.name
    out = inp + ".zst"
    try:
        subprocess.run(["zstd", "-19", "-f", "-q", inp, "-o", out], check=True)
        return os.path.getsize(out)
    finally:
        for p in (inp, out):
            try:
                os.unlink(p)
            except OSError:
                pass


def xz9(data: bytes) -> int:
    with tempfile.NamedTemporaryFile(delete=False) as tf:
        tf.write(data)
        inp = tf.name
    out = inp + ".xz"
    try:
        subprocess.run(["xz", "-9", "-f", "-k", "-c", inp], check=True,
                       stdout=open(out, "wb"))
        return os.path.getsize(out)
    finally:
        for p in (inp, out):
            try:
                os.unlink(p)
            except OSError:
                pass


def cmix_n(data: bytes, cmix_bin: str) -> int | None:
    if not cmix_bin or not os.path.isfile(cmix_bin):
        return None
    with tempfile.TemporaryDirectory() as td:
        inp = os.path.join(td, "in")
        out = os.path.join(td, "out")
        open(inp, "wb").write(data)
        # DEVNULL: progress spam + capture_output deadlocks the pipe.
        rc = subprocess.run(
            [cmix_bin, "-n", inp, out],
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL,
        )
        if rc.returncode != 0 or not os.path.isfile(out):
            return None
        return os.path.getsize(out)


def cmix_c(cmix_bin: str, dic: str, data: bytes) -> int | None:
    with tempfile.TemporaryDirectory() as td:
        inp = os.path.join(td, "in")
        out = os.path.join(td, "out")
        open(inp, "wb").write(data)
        rc = subprocess.run(
            [cmix_bin, "-c", dic, inp, out],
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL,
        )
        if rc.returncode != 0 or not os.path.isfile(out) or os.path.getsize(out) == 0:
            return None
        return os.path.getsize(out)


def pack_front_coded_alpha(words: list[str]) -> bytes:
    words = sorted(words)
    out = bytearray()
    prev = ""
    for w in words:
        shared = 0
        lim = min(len(prev), len(w), 255)
        while shared < lim and prev[shared] == w[shared]:
            shared += 1
        out.append(shared & 0xFF)
        out += w[shared:].encode("ascii")
        out.append(0x0A)
        prev = w
    return bytes(out)


def fz_sidecar_size(data: bytes, workdir: str) -> int | None:
    """Compress a one-file tree with fractal_zip_cli (bytes-effective outer).

    CLI writes `<dir>.fz` beside the directory (not a second argv path).
    """
    cli = f"{ROOT}/fractal_zip_cli.php"
    if not os.path.isfile(cli):
        return None
    td = tempfile.mkdtemp(prefix="fz_sc_", dir=workdir)
    try:
        open(os.path.join(td, "repair.txt"), "wb").write(data)
        env = os.environ.copy()
        env["FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC"] = "1"
        env["FRACTAL_ZIP_SUPPRESS_HTML"] = "1"
        rc = subprocess.run(
            ["php", cli, "zip", td],
            capture_output=True, env=env, timeout=600,
        )
        archive = td.rstrip("/") + ".fz"
        if rc.returncode != 0 or not os.path.isfile(archive):
            return None
        return os.path.getsize(archive)
    finally:
        import shutil
        shutil.rmtree(td, ignore_errors=True)
        try:
            os.unlink(td.rstrip("/") + ".fz")
        except OSError:
            pass


def stem_suffix_peel(word: str, min_stem: int) -> str | None:
    for suf in SUFFIXES:
        if len(word) > len(suf) + min_stem and word.endswith(suf):
            return word[: -len(suf)]
    return None


def stem_prefix_keep(word: str, keep: int) -> str | None:
    if len(word) <= keep:
        return None
    return word[:keep]


def stem_vowel_del(word: str, min_stem: int) -> str | None:
    vowels = set("aeiou")
    skel = "".join(c for c in word if c not in vowels)
    if len(skel) < min_stem or skel == word:
        return None
    return skel


_TOK_ALPH = "abcdefghijklmnopqrstuvwxyz"  # lowercase only so restore sees islower()


def dense_token(i: int) -> str:
    """4-letter token zzxy in a private namespace (matches WORD_RE, rare in wiki)."""
    return "zz" + _TOK_ALPH[i // 26] + _TOK_ALPH[i % 26]


def build_injective_map(
    freq: Counter[str],
    family: str,
    min_len: int,
    min_freq: int,
    min_stem: int,
    prefix_keep: int,
    dense_top: int = 0,
) -> dict[str, str]:
    """Return full_word → stem/token for types that are injective under stem."""
    if family == "dense_rank":
        scored = []
        for w, n in freq.items():
            if n < min_freq or len(w) < min_len or not w.isalpha() or not w.islower():
                continue
            if len(w) <= 4:
                continue
            scored.append(((len(w) - 4) * n, w))
        scored.sort(reverse=True)
        fold = {}
        for i, (_, w) in enumerate(scored[: max(1, dense_top)]):
            tok = dense_token(i)
            if tok in freq:  # collision with natural type — skip slot
                continue
            fold[w] = tok
        return fold

    candidates: dict[str, str] = {}
    for w, n in freq.items():
        if n < min_freq or len(w) < min_len or not w.isalpha() or not w.islower():
            continue
        if family == "suffix_peel":
            s = stem_suffix_peel(w, min_stem)
        elif family == "prefix_keep":
            s = stem_prefix_keep(w, prefix_keep)
        elif family == "vowel_del":
            s = stem_vowel_del(w, min_stem)
        else:
            raise ValueError(family)
        if not s or s == w:
            continue
        # savings proxy must be positive per occurrence
        if len(w) - len(s) < 2:
            continue
        candidates[w] = s

    # injectivity: stem maps to exactly one full word among candidates
    by_stem: dict[str, list[str]] = {}
    for w, s in candidates.items():
        by_stem.setdefault(s, []).append(w)
    fold = {w: s for s, ws in by_stem.items() if len(ws) == 1 for w in ws}

    # Stem must be absent from the lossy stream's natural residues:
    #  - not an unfolded type still present
    #  - not itself a folded source word (else apply chains: accordingly→according→accord)
    changed = True
    while changed:
        remaining = set(freq) - set(fold)
        blocked = remaining | set(fold.keys())
        new_fold = {w: s for w, s in fold.items() if s not in blocked}
        changed = len(new_fold) != len(fold)
        fold = new_fold
    return fold


def apply_fold(data: bytes, fold: dict[str, str]) -> bytes:
    if not fold:
        return data

    def repl(m: re.Match[bytes]) -> bytes:
        raw = m.group(1)
        # case variants: only fold exact lowercase; Title → Stem if lower in map
        if raw.islower():
            key = raw.decode("ascii")
            if key in fold:
                return fold[key].encode("ascii")
        elif raw[0:1].isupper() and raw[1:].islower():
            key = raw.decode("ascii").lower()
            if key in fold:
                stem = fold[key]
                return (stem[0].upper() + stem[1:]).encode("ascii")
        return raw

    return WORD_RE.sub(repl, data)


def mine_dict(data: bytes, max_words: int) -> list[str]:
    ctr: Counter[str] = Counter()
    for m in WORD_RE.finditer(data):
        w = m.group(1).decode("ascii").lower()
        if len(w) >= 2:
            ctr[w] += 1
    ranked = sorted(ctr.items(), key=lambda kv: (-kv[1] * max(1, len(kv[0]) - 1), -kv[1], kv[0]))
    return [w for w, _ in ranked[:max_words]]


def compress_tournament(blob: bytes, label: str, cmix_bin: str | None, workdir: str, try_fz: bool) -> dict:
    row = {
        "label": label,
        "raw": len(blob),
        "zstd19": zstd19(blob),
        "xz9": xz9(blob),
    }
    if cmix_bin:
        c = cmix_n(blob, cmix_bin)
        row["cmix_n"] = c
    if try_fz:
        fz = fz_sidecar_size(blob, workdir)
        row["fz"] = fz
    # best bytes-effective among measured
    scores = {k: v for k, v in row.items() if k not in ("label", "raw") and isinstance(v, int)}
    if scores:
        best_k = min(scores, key=scores.get)
        row["best_codec"] = best_k
        row["best_bytes"] = scores[best_k]
    return row


def analytic_save(freq: Counter[str], fold: dict[str, str]) -> int:
    return sum((len(w) - len(fold[w])) * freq[w] for w in fold)


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--slice", required=True)
    ap.add_argument("--out", required=True)
    ap.add_argument("--workdir", default=f"{ROOT}/benchmarks/.ladder_cache/fractal_dictlab/lossy_repair")
    ap.add_argument("--cmix", default="")
    ap.add_argument("--global-dict", default=f"{ROOT}/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic")
    ap.add_argument("--dict-words", type=int, default=8000)
    ap.add_argument("--try-fz", action="store_true")
    ap.add_argument("--families", default="suffix_peel,vowel_del,prefix_keep,dense_rank",
                    help="Comma list of fold families to sweep")
    ap.add_argument("--run-cmix-c", action="store_true",
                    help="Real cmix -c on raw vs best lossy arm (needs RAM)")
    args = ap.parse_args()
    want_fam = {x.strip() for x in args.families.split(",") if x.strip()}
    os.makedirs(args.workdir, exist_ok=True)
    cmix_bin = args.cmix or None

    data = open(args.slice, "rb").read()
    log(f"slice={args.slice} bytes={len(data)}")
    freq: Counter[str] = Counter()
    for m in WORD_RE.finditer(data):
        w = m.group(1).decode("ascii").lower()
        freq[w] += 1

    grid = []
    for min_len in (8, 10, 12):
        for min_freq in (2, 4, 8):
            for min_stem in (3, 4):
                for family in ("suffix_peel", "vowel_del"):
                    if family not in want_fam:
                        continue
                    fold = build_injective_map(freq, family, min_len, min_freq, min_stem, 0)
                    grid.append((family, min_len, min_freq, min_stem, 0, 0, fold))
            if "prefix_keep" in want_fam:
                for keep in (5, 6, 7, 8):
                    fold = build_injective_map(freq, "prefix_keep", min_len, min_freq, 3, keep)
                    grid.append(("prefix_keep", min_len, min_freq, 3, keep, 0, fold))
    # Dense rank tokens (fractal-process-like table of long words → short ops)
    if "dense_rank" in want_fam:
        for min_len in (10, 12):
            for min_freq in (3, 6, 12):
                for top in (64, 128, 256, 512):
                    fold = build_injective_map(
                        freq, "dense_rank", min_len, min_freq, 0, 0, dense_top=top
                    )
                    grid.append(("dense_rank", min_len, min_freq, 0, 0, top, fold))

    # Dedup identical folds; keep first param set
    seen = set()
    unique = []
    for row in grid:
        key = frozenset(row[6].items())
        if not key or key in seen:
            continue
        seen.add(key)
        unique.append(row)
    log(f"unique fold maps: {len(unique)}")

    baseline_body = {
        "raw": len(data),
        "zstd19": zstd19(data),
        "xz9": xz9(data),
    }
    # Fair S1 baseline: matched dict mined from *raw* slice (same cap), not global
    raw_dict_words = mine_dict(data, args.dict_words)
    raw_dic_blob = ("\n".join(raw_dict_words) + "\n").encode("ascii")
    raw_dic_tour = compress_tournament(raw_dic_blob, "raw_matched_dict", cmix_bin, args.workdir, args.try_fz)
    log(f"baseline body zstd19={baseline_body['zstd19']} xz9={baseline_body['xz9']}")
    log(f"raw matched dict best={raw_dic_tour.get('best_bytes')} ({raw_dic_tour.get('best_codec')})")

    results = {
        "slice": args.slice,
        "slice_bytes": len(data),
        "baseline_body": baseline_body,
        "baseline_matched_dict": raw_dic_tour,
        "arms": [],
    }

    # Rank by analytic save, evaluate top-K fully; always include best dense_rank
    scored = []
    for family, min_len, min_freq, min_stem, keep, dtop, fold in unique:
        save = analytic_save(freq, fold)
        scored.append((save, family, min_len, min_freq, min_stem, keep, dtop, fold))
    scored.sort(reverse=True)

    top = scored[:10]
    # ensure a few dense_rank arms are measured even if analytic save mid-pack
    dense_extra = [r for r in scored if r[1] == "dense_rank"][:4]
    suffix_extra = [r for r in scored if r[1] == "suffix_peel"][:2]
    seen_i = {id(t[7]) for t in top}
    for r in dense_extra + suffix_extra:
        if id(r[7]) not in seen_i:
            top.append(r)
            seen_i.add(id(r[7]))

    for save, family, min_len, min_freq, min_stem, keep, dtop, fold in top:
        body = apply_fold(data, fold)
        # Codebook: stem\\tfull per line, stem-sorted (repair program)
        pairs = sorted(((s, w) for w, s in fold.items()), key=lambda t: t[0])
        cb_lines = [f"{s}\t{w}" for s, w in pairs]
        cb_raw = ("\n".join(cb_lines) + "\n").encode("ascii")
        cb_fc = pack_front_coded_alpha([f"{s}:{w}" for s, w in pairs])
        # Prefer the denser of raw TSV vs front-coded "stem:full"
        cb_pack = cb_fc if len(cb_fc) < len(cb_raw) else cb_raw
        cb_pack_name = "front_coded_stemfull" if cb_pack is cb_fc else "tsv_stem_full"

        body_z = zstd19(body)
        body_x = xz9(body)
        cb_tour = compress_tournament(cb_pack, cb_pack_name, cmix_bin, args.workdir, args.try_fz)
        # Also tournament the other pack if different
        other = cb_raw if cb_pack is cb_fc else cb_fc
        other_name = "tsv_stem_full" if cb_pack is cb_fc else "front_coded_stemfull"
        cb_other = compress_tournament(other, other_name, cmix_bin, args.workdir, False)
        cb_best = min(
            cb_tour.get("best_bytes") or 10**12,
            cb_other.get("best_bytes") or 10**12,
        )
        cb_best_label = (
            f"{cb_tour['label']}/{cb_tour.get('best_codec')}"
            if (cb_tour.get("best_bytes") or 10**12) <= (cb_other.get("best_bytes") or 10**12)
            else f"{cb_other['label']}/{cb_other.get('best_codec')}"
        )

        dict_words = mine_dict(body, args.dict_words)
        dic_path = os.path.join(args.workdir, f"dict_{family}_L{min_len}_f{min_freq}.dic")
        open(dic_path, "w").write("\n".join(dict_words) + "\n")
        dic_blob = open(dic_path, "rb").read()
        dic_tour = compress_tournament(dic_blob, "matched_dict", cmix_bin, args.workdir, False)

        proxy_body_delta = body_z - baseline_body["zstd19"]
        # Fair joint: (lossy_dict + codebook + lossy_body) - (raw_dict + raw_body)
        base_s1 = raw_dic_tour.get("best_bytes") or 0
        lossy_s1 = (dic_tour.get("best_bytes") or 0) + cb_best
        fair_joint = (lossy_s1 + body_z) - (base_s1 + baseline_body["zstd19"])
        naive_joint = proxy_body_delta + cb_best
        arm = {
            "family": family,
            "min_len": min_len,
            "min_freq": min_freq,
            "min_stem": min_stem,
            "prefix_keep": keep,
            "dense_top": dtop,
            "n_folded_types": len(fold),
            "analytic_save_bytes": save,
            "body_raw": len(body),
            "body_raw_delta": len(body) - len(data),
            "body_zstd19": body_z,
            "body_xz9": body_x,
            "body_zstd19_delta": proxy_body_delta,
            "codebook": {"primary": cb_tour, "alt": cb_other, "best_bytes": cb_best, "best": cb_best_label},
            "matched_dict": {
                "words": len(dict_words),
                "compress": dic_tour,
            },
            "proxy_net_naive_body_plus_cb": naive_joint,
            "proxy_net_fair_matched_dict": fair_joint,
            "proxy_verdict": "KEEP_PROXY" if fair_joint < 0 else "REJECT_PROXY",
            "gate": {
                "analytic_gt_codebook": save > cb_best,
                "body_zstd_improved": proxy_body_delta < 0,
                "fair_joint_negative": fair_joint < 0,
                "dict_shorter_than_8k_cap": len(dict_words) <= args.dict_words,
            },
        }
        # Roundtrip check
        # Rebuild: replace stems back using codebook (word-boundary)
        rev = {s: w for w, s in fold.items()}
        restored = body

        def unrepl(m: re.Match[bytes]) -> bytes:
            raw = m.group(1)
            if raw.islower():
                s = raw.decode("ascii")
                if s in rev:
                    return rev[s].encode("ascii")
            elif raw[0:1].isupper() and raw[1:].islower():
                s = raw.decode("ascii").lower()
                if s in rev:
                    full = rev[s]
                    return (full[0].upper() + full[1:]).encode("ascii")
            return raw

        restored = WORD_RE.sub(unrepl, restored)
        arm["roundtrip_ok"] = restored == data
        if not arm["roundtrip_ok"]:
            # count mismatches
            arm["roundtrip_delta_bytes"] = abs(len(restored) - len(data))

        results["arms"].append(arm)
        log(
            f"{family} L>={min_len} f>={min_freq} top={dtop}: types={len(fold)} save≈{save} "
            f"bodyΔz={proxy_body_delta} cb={cb_best}({cb_best_label}) "
            f"fairΔ={fair_joint} rt={arm['roundtrip_ok']} → {arm['proxy_verdict']}"
        )
        json.dump(results, open(args.out, "w"), indent=2)

    # Pick best KEEP_PROXY for optional real cmix
    keepers = [a for a in results["arms"] if a["proxy_verdict"] == "KEEP_PROXY" and a.get("roundtrip_ok")]
    keepers.sort(key=lambda a: a["proxy_net_fair_matched_dict"])
    results["best_proxy"] = keepers[0] if keepers else None
    # Also retain closest reject for cmix (mixing may flip)
    if not results["best_proxy"]:
        ok = [a for a in results["arms"] if a.get("roundtrip_ok")]
        ok.sort(key=lambda a: a["proxy_net_fair_matched_dict"])
        results["closest_proxy"] = ok[0] if ok else None

    target = results["best_proxy"] or results.get("closest_proxy")
    if args.run_cmix_c and cmix_bin and target:
        best = target
        family = best["family"]
        # rebuild fold with same params
        fold = build_injective_map(
            freq, family, best["min_len"], best["min_freq"], best["min_stem"],
            best["prefix_keep"], dense_top=best.get("dense_top") or 0,
        )
        body = apply_fold(data, fold)
        pairs = sorted(((s, w) for w, s in fold.items()), key=lambda t: t[0])
        cb_fc = pack_front_coded_alpha([f"{s}:{w}" for s, w in pairs])
        dic_path = os.path.join(args.workdir, "best_lossy.dic")
        words = mine_dict(body, args.dict_words)
        open(dic_path, "w").write("\n".join(words) + "\n")
        raw_dic_path = os.path.join(args.workdir, "raw_matched.dic")
        open(raw_dic_path, "w").write("\n".join(mine_dict(data, args.dict_words)) + "\n")
        log("cmix -c baseline (raw matched dict)...")
        s2_base = cmix_c(cmix_bin, raw_dic_path, data)
        log(f"  S2 base={s2_base}")
        log("cmix -c lossy (matched dict)...")
        s2_lossy = cmix_c(cmix_bin, dic_path, body)
        log(f"  S2 lossy={s2_lossy}")
        cb_c = cmix_n(cb_fc, cmix_bin)
        dic_c = cmix_n(open(dic_path, "rb").read(), cmix_bin)
        raw_dic_c = cmix_n(open(raw_dic_path, "rb").read(), cmix_bin)
        # Also score codebook with fz if requested
        cb_fz = fz_sidecar_size(cb_fc, args.workdir) if args.try_fz else None
        cb_best_s1 = min(x for x in (cb_c, cb_fz, zstd19(cb_fc)) if x is not None)
        results["cmix_c"] = {
            "s2_base": s2_base,
            "s2_lossy": s2_lossy,
            "codebook_cmix_n": cb_c,
            "codebook_fz": cb_fz,
            "codebook_best_s1": cb_best_s1,
            "matched_dict_cmix_n": dic_c,
            "raw_matched_dict_cmix_n": raw_dic_c,
            "joint_base": (raw_dic_c or 0) + (s2_base or 0),
            "joint_lossy": (dic_c or 0) + cb_best_s1 + (s2_lossy or 0),
        }
        if s2_base is not None and s2_lossy is not None:
            results["cmix_c"]["delta_joint"] = (
                results["cmix_c"]["joint_lossy"] - results["cmix_c"]["joint_base"]
            )
            log(f"joint base={results['cmix_c']['joint_base']} "
                f"lossy={results['cmix_c']['joint_lossy']} "
                f"Δ={results['cmix_c']['delta_joint']}")

    os.makedirs(os.path.dirname(args.out) or ".", exist_ok=True)
    json.dump(results, open(args.out, "w"), indent=2)
    log(f"wrote {args.out}")
    if results["best_proxy"]:
        bp = results["best_proxy"]
        log(f"BEST_PROXY {bp['family']} fairΔ={bp['proxy_net_fair_matched_dict']} "
            f"types={bp['n_folded_types']} save={bp['analytic_save_bytes']}")
    else:
        cp = results.get("closest_proxy")
        if cp:
            log(f"BEST_PROXY none; closest {cp['family']} fairΔ={cp['proxy_net_fair_matched_dict']}")
        else:
            log("BEST_PROXY none — all arms REJECT_PROXY at fair zstd joint")


if __name__ == "__main__":
    main()
