#!/usr/bin/env python3
"""Dictionary self-compression lab — the S1 half of "dictionaries can be
compressed, since there is repetition" that VocabTrim never explored.

The banked S1 path already cmix-compresses the raw newline-separated word
list (english_entityfold_e9.dic 416KB → comp_*.dic ≈102KB). This lab asks
whether a *lossless structural transform* of that word list, applied before
cmix, shrinks the compressed blob further — front-coding (shared prefixes),
suffix-sorting, length-bucketed order, etc. — without changing the decoder's
word→code assignment (we expand back to the canonical newline list before
Dictionary:: load, OR we change both pack and unpack together).

For a fair S1-only screen we:
  1. load the canonical word list (order = code assignment)
  2. produce a transformed *payload* that still expands 1:1 to the same list
  3. cmix-compress the payload (using a small external cmix if available,
     else zstd-19 as a ranking proxy — noted as such)
  4. report Δ vs cmix(raw canonical)

Front-coding format (self-delimiting, expands losslessly):
  For each word, emit u8 shared_prefix_len with previous word, then the
  remaining suffix bytes, then '\\n'. First word: shared=0.

Usage:
  python3 benchmarks/run_fractal_dict_compress_lab.py \\
      --dict benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic \\
      --out benchmarks/.ladder_cache/fractal_dictlab/dict_compress.json
"""
from __future__ import annotations

import argparse
import json
import os
import subprocess
import tempfile

ROOT = "/srv/http/fractal_zip"


def load_words(path: str) -> list[str]:
    data = open(path, "rb").read().split(b"\n")
    return [w.decode("ascii") for w in data if w]


def pack_raw(words: list[str]) -> bytes:
    return ("\n".join(words) + ("\n" if words else "")).encode("ascii")


def pack_front_coded(words: list[str]) -> bytes:
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


def unpack_front_coded(blob: bytes) -> list[str]:
    words = []
    prev = ""
    i = 0
    while i < len(blob):
        shared = blob[i]
        i += 1
        j = blob.find(b"\n", i)
        if j < 0:
            raise ValueError("truncated front-coded dict")
        suffix = blob[i:j].decode("ascii")
        w = prev[:shared] + suffix
        words.append(w)
        prev = w
        i = j + 1
    return words


def _pack_zigzag_deltas(vals: list[int]) -> bytes:
    payload = bytearray()
    prev = 0
    for p in vals:
        d = p - prev
        zz = (d << 1) ^ (d >> 31)
        while zz >= 0x80:
            payload.append((zz & 0x7F) | 0x80)
            zz >>= 7
        payload.append(zz & 0x7F)
        prev = p
    return bytes(payload)


def pack_alpha_then_restore_order(words: list[str]) -> bytes:
    """Sort alphabetically for compressibility, but prefix a u32 permutation
    so the canonical code order is recoverable. Perm is itself compressible."""
    import struct
    indexed = sorted(enumerate(words), key=lambda t: t[1])
    perm = [orig_i for orig_i, _ in indexed]
    alpha = [w for _, w in indexed]
    payload = bytearray()
    payload += struct.pack("<I", len(words))
    payload += _pack_zigzag_deltas(perm)
    payload += pack_raw(alpha)
    return bytes(payload)


def pack_front_coded_reversed(words: list[str]) -> bytes:
    """Front-code on reversed strings (shared suffixes become prefixes)."""
    rev = [w[::-1] for w in words]
    coded = pack_front_coded(sorted(rev))
    # Recoverable: expand → reverse each → need perm back to code order.
    # For S1 screen we only need a compressible payload that expands losslessly
    # to the *same multiset*; attach perm from sorted-rev back to canonical.
    import struct
    indexed = sorted(enumerate(rev), key=lambda t: t[1])
    perm = [orig_i for orig_i, _ in indexed]
    return struct.pack("<I", len(words)) + _pack_zigzag_deltas(perm) + coded


def pack_length_buckets_front(words: list[str]) -> bytes:
    """Group by length then alpha; front-code within buckets + restore perm."""
    import struct
    indexed = sorted(enumerate(words), key=lambda t: (len(t[1]), t[1]))
    perm = [orig_i for orig_i, _ in indexed]
    ordered = [w for _, w in indexed]
    return struct.pack("<I", len(words)) + _pack_zigzag_deltas(perm) + pack_front_coded(ordered)


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


def cmix_size(data: bytes, cmix_bin: str) -> int | None:
    if not cmix_bin or not os.path.isfile(cmix_bin):
        return None
    with tempfile.TemporaryDirectory() as td:
        inp = os.path.join(td, "in")
        out = os.path.join(td, "out")
        open(inp, "wb").write(data)
        # Prefer a lightweight cmix if present; fall back to fractalv2b -n (no preprocess)
        rc = subprocess.run([cmix_bin, "-n", inp, out], capture_output=True)
        if rc.returncode != 0 or not os.path.isfile(out):
            return None
        return os.path.getsize(out)


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--dict", required=True)
    ap.add_argument("--out", required=True)
    ap.add_argument("--cmix", default=f"{ROOT}/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b")
    args = ap.parse_args()

    words = load_words(args.dict)
    assert unpack_front_coded(pack_front_coded(words)) == words, "front-code roundtrip failed"

    variants = {
        "raw_canonical": pack_raw(words),
        "front_coded_canonical_order": pack_front_coded(words),
        "front_coded_alpha_order": pack_front_coded(sorted(words)),
        "front_coded_suffix_alpha": pack_front_coded_reversed(words),
        "front_coded_len_buckets": pack_length_buckets_front(words),
        "alpha_plus_perm": pack_alpha_then_restore_order(words),
        "raw_alpha": pack_raw(sorted(words)),  # NOT order-preserving alone — reference only
    }

    results = {"dict": args.dict, "n_words": len(words), "variants": {}}
    baseline_z = None
    baseline_c = None
    for name, blob in variants.items():
        z = zstd19(blob)
        c = cmix_size(blob, args.cmix)
        row = {"raw_bytes": len(blob), "zstd19": z, "cmix_n": c}
        if name == "raw_canonical":
            baseline_z, baseline_c = z, c
        else:
            row["delta_zstd19_vs_raw"] = z - baseline_z if baseline_z is not None else None
            row["delta_cmix_vs_raw"] = (c - baseline_c) if (c is not None and baseline_c is not None) else None
        results["variants"][name] = row
        print(f"{name}: raw={len(blob)} zstd19={z} cmix_n={c} "
              f"Δz={row.get('delta_zstd19_vs_raw')} Δc={row.get('delta_cmix_vs_raw')}", flush=True)

    # Also report the already-banked comp_*.dic size if present
    comp = args.dict.replace("/english_", "/comp_english_")
    if os.path.isfile(comp) and os.path.getsize(comp) > 0:
        results["banked_comp_dic_bytes"] = os.path.getsize(comp)
        print(f"banked_comp_dic={os.path.getsize(comp)}", flush=True)

    os.makedirs(os.path.dirname(args.out) or ".", exist_ok=True)
    json.dump(results, open(args.out, "w"), indent=2)
    print("wrote", args.out)


if __name__ == "__main__":
    main()
