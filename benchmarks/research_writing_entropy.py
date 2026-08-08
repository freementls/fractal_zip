#!/usr/bin/env python3
"""Writing-system entropy probe.

Premise (from the history of writing): a high-entropy alphabetic stream can be
split into channels (abjad: consonant skeleton vs. vowels; case mask; word
boundaries). Re-symbolization alone is entropy-invariant, so the ONLY lever is
conditional entropy under a *finite-order* predictor. This script measures the
achievable bits of an order-k adaptive (KT-smoothed) arithmetic coder on the raw
stream vs. on separated channels, sweeping k, with bijection roundtrip checks.

A decomposition that beats baseline at HIGH k is a genuine, stack-relevant win.
"""
import sys
import math
import argparse


def kt_codelen_orderk(symbols, k, alphabet_size):
    """Ideal code length (bits) for an order-k adaptive KT (alpha=0.5) model.

    Returns total bits to encode `symbols` given the model adapts online.
    This is a reproducible lower-bound proxy for a deterministic order-k coder.
    """
    alpha = 0.5
    denom_base = alpha * alphabet_size
    ctx_counts = {}      # context tuple -> dict(sym -> count)
    ctx_total = {}       # context tuple -> total count
    bits = 0.0
    hist = ()
    for s in symbols:
        ctx = hist
        cc = ctx_counts.get(ctx)
        if cc is None:
            c = 0
            tot = 0
        else:
            c = cc.get(s, 0)
            tot = ctx_total[ctx]
        p = (c + alpha) / (tot + denom_base)
        bits += -math.log2(p)
        # update
        if cc is None:
            cc = {}
            ctx_counts[ctx] = cc
            ctx_total[ctx] = 0
        cc[s] = c + 1
        ctx_total[ctx] = tot + 1
        # advance history
        if k == 0:
            hist = ()
        else:
            hist = (hist + (s,))[-k:]
    return bits


VOWELS = set(b"aeiouAEIOU")


def split_abjad(data: bytes):
    """Bijective abjad split.

    skeleton: bytes with ASCII vowels removed
    vowels:   the removed vowel bytes, in order
    mask:     1 byte per original position, 1 if vowel else 0
    Reconstruct: walk mask; vowel-bit -> next vowel else next skeleton byte.
    """
    skel = bytearray()
    vow = bytearray()
    mask = bytearray()
    for b in data:
        if b in VOWELS:
            vow.append(b)
            mask.append(1)
        else:
            skel.append(b)
            mask.append(0)
    return bytes(skel), bytes(vow), bytes(mask)


def unsplit_abjad(skel: bytes, vow: bytes, mask: bytes) -> bytes:
    out = bytearray()
    si = vi = 0
    for m in mask:
        if m:
            out.append(vow[vi]); vi += 1
        else:
            out.append(skel[si]); si += 1
    return bytes(out)


def split_case(data: bytes):
    """Lowercase stream + case mask (1 if originally uppercase letter)."""
    low = bytearray()
    mask = bytearray()
    for b in data:
        if 65 <= b <= 90:  # A-Z
            low.append(b + 32)
            mask.append(1)
        else:
            low.append(b)
            mask.append(0)
    return bytes(low), bytes(mask)


def unsplit_case(low: bytes, mask: bytes) -> bytes:
    out = bytearray()
    for b, m in zip(low, mask):
        out.append(b - 32 if m else b)
    return bytes(out)


def split_case_abjad(data: bytes):
    """Case-fold first, then abjad-split the lowercased stream."""
    low, cmask = split_case(data)
    skel, vow, vmask = split_abjad(low)
    return low, cmask, skel, vow, vmask


def extract_text(blob: bytes, limit: int) -> bytes:
    """Pull mostly-prose: take bytes inside <text ...>...</text> regions."""
    out = bytearray()
    i = 0
    needle = b"<text"
    while len(out) < limit:
        j = blob.find(needle, i)
        if j < 0:
            break
        gt = blob.find(b">", j)
        if gt < 0:
            break
        end = blob.find(b"</text>", gt)
        if end < 0:
            break
        out += blob[gt + 1:end]
        out += b"\n"
        i = end + 7
    return bytes(out[:limit])


def codelen_bytes(name, streams, ks):
    """streams: list of byte strings (channels). Sum order-k codelen per k."""
    res = {}
    for k in ks:
        total = 0.0
        for s in streams:
            if not s:
                continue
            asize = len(set(s)) or 1
            total += kt_codelen_orderk(s, k, asize)
        res[k] = total
    return res


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--src", default="enwik8")
    ap.add_argument("--bytes", type=int, default=300000)
    ap.add_argument("--mode", default="text", choices=["text", "raw"])
    ap.add_argument("--orders", default="0,1,2,3,4")
    args = ap.parse_args()

    ks = [int(x) for x in args.orders.split(",")]
    with open(args.src, "rb") as f:
        blob = f.read(args.bytes * 40 if args.mode == "text" else args.bytes)
    if args.mode == "text":
        data = extract_text(blob, args.bytes)
    else:
        data = blob[:args.bytes]
    n = len(data)
    print(f"# source={args.src} mode={args.mode} bytes={n}")

    # roundtrip checks
    skel, vow, mask = split_abjad(data)
    assert unsplit_abjad(skel, vow, mask) == data, "abjad roundtrip FAIL"
    low, cmask = split_case(data)
    assert unsplit_case(low, cmask) == data, "case roundtrip FAIL"
    print(f"# roundtrip: abjad OK, case OK | vowels={len(vow)} ({100*len(vow)/n:.1f}%)")

    decomps = {
        "baseline": [data],
        "case_split": [low, cmask],
        "abjad_split": [skel, vow, mask],
        "case+abjad": list(split_case_abjad(data)),
    }

    base = codelen_bytes("baseline", decomps["baseline"], ks)
    print()
    header = "order  " + "  ".join(f"{name:>14}" for name in decomps) + "   best"
    print(header)
    for k in ks:
        row = [f"k={k:<3}"]
        vals = {}
        for name, streams in decomps.items():
            r = codelen_bytes(name, streams, [k])[k]
            vals[name] = r
        best = min(vals, key=vals.get)
        for name in decomps:
            bpc = vals[name] / n
            delta = (vals[name] - base[k]) / n
            tag = "*" if name == best else " "
            row.append(f"{bpc:6.4f}{tag}({delta:+.4f})"[:16].rjust(14))
        row.append(f"  {best}")
        print("  ".join(row))
    print("\n# bpc = bits/char of original; (Δ) vs baseline at same order; lower=better; * = best at that order")


if __name__ == "__main__":
    main()
