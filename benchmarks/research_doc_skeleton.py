#!/usr/bin/env python3
"""Document-skeleton factoring via BPE grammar learning (position-implicit).

Idea (from the writing-history note): learn the document's OWN repeated
primitives -- not letter shapes, but markup/template structure -- and replace
each with a single symbol. This is position-implicit (the symbol marks its own
boundary; no per-char "where" mask), bijective, and brings globally-frequent
structure into a finite predictor's reach.

We BPE-merge the K most frequent adjacent symbol pairs, then measure achievable
bits (Witten-Bell backoff) AND real PPMd/xz bytes on original vs merged stream,
normalized per ORIGINAL byte, counting the merge table as overhead.
Win condition: total (stream + table) bpc < baseline under the STRONG coder.
"""
import os, sys, math, argparse, subprocess, tempfile
from collections import Counter


def bpe_learn(seq, k):
    """Return (new_seq, merges). merges: list of (a,b)->newid."""
    seq = list(seq)
    next_id = max(seq) + 1 if seq else 256
    merges = []
    for _ in range(k):
        if len(seq) < 2:
            break
        pairs = Counter()
        prev = seq[0]
        for cur in seq[1:]:
            pairs[(prev, cur)] += 1
            prev = cur
        (a, b), cnt = pairs.most_common(1)[0]
        if cnt < 3:
            break
        nid = next_id; next_id += 1
        merges.append((a, b, nid))
        out = []
        i = 0; n = len(seq)
        while i < n:
            if i + 1 < n and seq[i] == a and seq[i + 1] == b:
                out.append(nid); i += 2
            else:
                out.append(seq[i]); i += 1
        seq = out
    return seq, merges


def bpe_expand(seq, merges):
    table = {nid: (a, b) for (a, b, nid) in merges}
    out = []
    stack = []
    for s in reversed(seq):
        stack.append(s)
    # iterative expansion
    res = []
    work = list(seq)
    # expand repeatedly
    changed = True
    # do it with a recursive-ish stack per symbol
    def emit(sym, acc):
        st = [sym]
        while st:
            x = st.pop()
            if x in table:
                a, b = table[x]
                st.append(b); st.append(a)
            else:
                acc.append(x)
    acc = []
    for s in seq:
        emit(s, acc)
    return bytes(acc)


def to_bytes_stream(seq):
    """Map present symbols to distinct bytes if <=256 distinct; else None."""
    syms = sorted(set(seq))
    if len(syms) > 256:
        return None
    m = {s: i for i, s in enumerate(syms)}
    return bytes(m[s] for s in seq)


# ---- Witten-Bell backoff achievable bits (arbitrary alphabet) ----
def wb_codelen(seq, max_order):
    if not seq:
        return 0.0
    A = len(set(seq)) or 1
    tables = [dict() for _ in range(max_order + 1)]
    bits = 0.0
    hist = ()
    for s in seq:
        p = 1.0 / A
        for m in range(0, max_order + 1):
            ctx = () if m == 0 else (hist[-m:] if len(hist) >= m else None)
            if ctx is None:
                break
            tbl = tables[m].get(ctx)
            if tbl is None:
                continue
            n = tbl["_n"]; u = len(tbl) - 1
            f = tbl.get(s, 0) / n if n else 0.0
            lam = n / (n + u) if (n + u) else 0.0
            p = lam * f + (1 - lam) * p
        if p <= 0:
            p = 1e-12
        bits += -math.log2(p)
        for m in range(0, max_order + 1):
            ctx = () if m == 0 else (hist[-m:] if len(hist) >= m else None)
            if ctx is None:
                break
            tbl = tables[m].get(ctx)
            if tbl is None:
                tbl = {"_n": 0}; tables[m][ctx] = tbl
            tbl[s] = tbl.get(s, 0) + 1; tbl["_n"] += 1
        hist = (hist + (s,))[-max_order:] if max_order else ()
    return bits


def real_size(data, tool):
    with tempfile.NamedTemporaryFile(delete=False) as f:
        f.write(data); path = f.name
    try:
        if tool == "xz":
            return len(subprocess.run(["xz","-9","-e","-c",path],capture_output=True).stdout)
        if tool == "bzip2":
            return len(subprocess.run(["bzip2","-9","-c",path],capture_output=True).stdout)
        if tool == "ppmd":
            arc = path + ".7z"
            try: os.unlink(arc)
            except FileNotFoundError: pass
            subprocess.run(["7z","a","-m0=PPMd:o16:mem256m","-bso0","-bsp0",arc,path],capture_output=True)
            sz = os.path.getsize(arc) if os.path.exists(arc) else 0
            try: os.unlink(arc)
            except FileNotFoundError: pass
            return sz
    finally:
        try: os.unlink(path)
        except FileNotFoundError: pass
    return 0


def merge_table_bytes(merges):
    """Serialize merges as varint-ish: 2 bytes a, 2 bytes b each (id<65536)."""
    out = bytearray()
    for a, b, nid in merges:
        out += a.to_bytes(2, "little") + b.to_bytes(2, "little")
    return bytes(out)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--src", default="enwik8")
    ap.add_argument("--bytes", type=int, default=500000)
    ap.add_argument("--mode", default="raw", choices=["raw", "text"])
    ap.add_argument("--ks", default="0,60,120,180")
    ap.add_argument("--wb-order", type=int, default=6)
    ap.add_argument("--wb-bytes", type=int, default=150000)
    args = ap.parse_args()

    with open(args.src, "rb") as f:
        blob = f.read(args.bytes)
    data = blob[:args.bytes]
    n = len(data)
    free = 256 - len(set(data))
    print(f"# doc_skeleton src={args.src} mode={args.mode} bytes={n} distinct={len(set(data))} free_codes={free}")

    base_real = {t: real_size(data, t) for t in ("bzip2","xz","ppmd")}
    print("# baseline real:", {t: f"{base_real[t]}B {8*base_real[t]/n:.4f}bpc" for t in base_real})
    # achievable (backoff) on a smaller window for speed
    wb_data = data[:args.wb_bytes]
    wb_n = len(wb_data)
    base_wb = wb_codelen(list(wb_data), args.wb_order)
    print(f"# baseline WB(order={args.wb_order}, {wb_n}B): {base_wb/wb_n:.4f} bpc")
    print()

    Ks = [int(x) for x in args.ks.split(",")]
    print(f"{'merges':>6} {'stream':>10} {'+table':>8} {'ppmd_bpc(Δ)':>16} {'xz_bpc(Δ)':>16} {'wb_bpc(Δ)':>16}")
    for K in Ks:
        if K == 0:
            print(f"{'0':>6} {n:>10} {0:>8} "
                  f"{8*base_real['ppmd']/n:7.4f}(+.0000)".rjust(16)
                  + " " + f"{8*base_real['xz']/n:7.4f}(+.0000)".rjust(16)
                  + " " + f"{base_wb/wb_n:7.4f}(+.0000)".rjust(16))
            continue
        seq, merges = bpe_learn(data, K)
        assert bpe_expand(seq, merges) == data, f"BPE roundtrip FAIL at K={K}"
        bstream = to_bytes_stream(seq)
        tbl = merge_table_bytes(merges)
        # real: PPMd/xz on byte stream + table appended (count table bytes raw)
        # byte output requires <=256 distinct present symbols (free-code budget)
        if bstream is not None:
            ppmd = real_size(bstream, "ppmd") + len(tbl)
            xz = real_size(bstream, "xz") + len(tbl)
            pstr = f"{8*ppmd/n:7.4f}({8*ppmd/n-8*base_real['ppmd']/n:+.4f})".rjust(16)
            xstr = f"{8*xz/n:7.4f}({8*xz/n-8*base_real['xz']/n:+.4f})".rjust(16)
        else:
            pstr = "n/a(>256sym)".rjust(16)
            xstr = "n/a(>256sym)".rjust(16)
        # achievable WB on merged version of wb window (any alphabet)
        wbseq, wbm = bpe_learn(wb_data, K)
        wb = wb_codelen(wbseq, args.wb_order) + 8*len(merge_table_bytes(wbm))
        dw = wb/wb_n - base_wb/wb_n
        wstr = f"{wb/wb_n:7.4f}({dw:+.4f})".rjust(16)
        print(f"{K:>6} {len(seq):>10} {len(tbl):>8} {pstr} {xstr} {wstr}")
    print("\n# bpc per ORIGINAL byte, incl. merge-table overhead. Negative Δ under ppmd = real win.")


if __name__ == "__main__":
    main()
