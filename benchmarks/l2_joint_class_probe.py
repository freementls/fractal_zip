#!/usr/bin/env python3
"""Profile L2 joint class coding probe.

Class (markup vs prose / finer states) is derived from a deterministic walk of
the *already decoded* prefix — no position mask, no sidecar. That is the
Hebrew/abjad idea without the mask tax: grammar-implied channel.

Arms (order-1 KT adaptive bpc on a sample):
  baseline     KT | prev
  joint        KT | (prev, class)   class = wiki parser state from history
  split+mask   separate KT on each class stream + 1-bit mask cost (lossy of idea)

Also zstd on raw vs a "tagged" stream that inserts class escape bytes (proxy for
making class visible to a match model — usually loses).

Usage:
  python3 benchmarks/l2_joint_class_probe.py [slice]
"""
from __future__ import annotations

import argparse
import math
import subprocess
from collections import defaultdict
from pathlib import Path

ROOT = Path("/srv/http/fractal_zip")
OUT = ROOT / "benchmarks/.ladder_cache/l2_joint"
LOG = ROOT / "benchmarks/.hutter_logs/l2_joint_class_probe.log"

# Parser states (class ids)
PROSE = 0
TAG = 1  # inside <...>
LINK = 2  # inside [[...]]
TMPL = 3  # inside {{...}}
ENTITY = 4  # inside &...;
NCLASS = 5


def log(msg: str) -> None:
    print(msg, flush=True)
    LOG.parent.mkdir(parents=True, exist_ok=True)
    with open(LOG, "a") as f:
        f.write(msg + "\n")


def class_stream(data: bytes) -> list[int]:
    """Per-byte class from a left-to-right wiki-ish scanner (decode-time decidable).

    Mid-slice cuts can start inside [[/{{; cap runs and reset on page boundaries
    so one unclosed opener cannot paint the rest of the file as LINK/TMPL.
    """
    cls = [PROSE] * len(data)
    i = 0
    n = len(data)
    state = PROSE
    link_depth = tmpl_depth = 0
    run_start = 0
    MAX_RUN = 4096

    def force_prose(at: int) -> None:
        nonlocal state, link_depth, tmpl_depth, run_start
        state = PROSE
        link_depth = tmpl_depth = 0
        run_start = at

    while i < n:
        # page boundary / long run → reset sticky markup states
        if state != PROSE and (
            i - run_start > MAX_RUN
            or (i + 7 <= n and data[i : i + 7] == b"</page>")
            or (i + 8 <= n and data[i : i + 8] == b"\n  <page>")
        ):
            force_prose(i)

        c = data[i]
        # entity
        if state == PROSE and c == ord("&"):
            j = i
            while j < n and j < i + 16 and data[j] != ord(";"):
                cls[j] = ENTITY
                j += 1
            if j < n and data[j] == ord(";"):
                cls[j] = ENTITY
                i = j + 1
                continue
            cls[i] = PROSE
            i += 1
            continue
        if state == PROSE and c == ord("<"):
            state = TAG
            run_start = i
            cls[i] = TAG
            i += 1
            continue
        if state == TAG:
            cls[i] = TAG
            if c == ord(">") or i - run_start > 512:
                force_prose(i + 1)
            i += 1
            continue
        if state == PROSE and c == ord("[") and i + 1 < n and data[i + 1] == ord("["):
            state = LINK
            link_depth = 1
            run_start = i
            cls[i] = LINK
            cls[i + 1] = LINK
            i += 2
            continue
        if state == LINK:
            cls[i] = LINK
            if c == ord("[") and i + 1 < n and data[i + 1] == ord("["):
                link_depth += 1
                cls[i + 1] = LINK
                i += 2
                continue
            if c == ord("]") and i + 1 < n and data[i + 1] == ord("]"):
                cls[i + 1] = LINK
                link_depth -= 1
                i += 2
                if link_depth <= 0:
                    force_prose(i)
                continue
            i += 1
            continue
        if state == PROSE and c == ord("{") and i + 1 < n and data[i + 1] == ord("{"):
            state = TMPL
            tmpl_depth = 1
            run_start = i
            cls[i] = TMPL
            cls[i + 1] = TMPL
            i += 2
            continue
        if state == TMPL:
            cls[i] = TMPL
            if c == ord("{") and i + 1 < n and data[i + 1] == ord("{"):
                tmpl_depth += 1
                cls[i + 1] = TMPL
                i += 2
                continue
            if c == ord("}") and i + 1 < n and data[i + 1] == ord("}"):
                cls[i + 1] = TMPL
                tmpl_depth -= 1
                i += 2
                if tmpl_depth <= 0:
                    force_prose(i)
                continue
            i += 1
            continue
        cls[i] = PROSE
        i += 1
    return cls


def kt_bpc(data: bytes, ctx_fn) -> tuple[float, int]:
    counts = defaultdict(lambda: [0] * 256)
    bits = 0.0
    prev = 0
    for i, b in enumerate(data):
        ctx = ctx_fn(i, prev)
        row = counts[ctx]
        total = sum(row) + 256
        bits += -math.log2((row[b] + 1) / total)
        row[b] += 1
        prev = b
    return bits / len(data), len(counts)


def split_kt_bpc(data: bytes, cls: list[int]) -> tuple[float, float]:
    """Separate order-1 KT per class + mask cost (1 bit/byte worst case, or entropy of class)."""
    streams = [bytearray() for _ in range(NCLASS)]
    for i, b in enumerate(data):
        streams[cls[i]].append(b)
    bits = 0.0
    for s in streams:
        if not s:
            continue
        bpc, _ = kt_bpc(bytes(s), lambda i, p: p)
        bits += bpc * len(s)
    # class mask entropy (order-0 of class ids)
    hist = [0] * NCLASS
    for c in cls:
        hist[c] += 1
    mask_bits = 0.0
    n = len(cls)
    for h in hist:
        if h:
            p = h / n
            mask_bits += -h * math.log2(p)
    return bits / n, mask_bits / n


def zsize(data: bytes, name: str) -> int:
    OUT.mkdir(parents=True, exist_ok=True)
    p = OUT / f"_{name}.bin"
    p.write_bytes(data)
    o = p.with_suffix(".zst")
    subprocess.run(
        ["zstd", "-12", "--long=30", "-T8", "-f", "-q", str(p), "-o", str(o)],
        check=True,
    )
    n = o.stat().st_size
    o.unlink()
    p.unlink()
    return n


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument(
        "slice",
        nargs="?",
        default=str(ROOT / "benchmarks/.ladder_cache/enwik8_1m_skip10.mid"),
    )
    ap.add_argument("--sample-mb", type=int, default=2)
    args = ap.parse_args()
    if not LOG.exists():
        LOG.write_text("")

    data = Path(args.slice).read_bytes()
    tag = Path(args.slice).stem
    sample = data[: min(len(data), args.sample_mb * 1024 * 1024)]
    log(f"=== L2 joint class probe tag={tag} plain={len(data)} sample={len(sample)} ===")

    cls = class_stream(sample)
    hist = [cls.count(c) for c in range(NCLASS)]
    names = ["prose", "tag", "link", "tmpl", "entity"]
    log("class histogram:")
    for n, h in zip(names, hist):
        log(f"  {n:8s} {h:8d}  ({100*h/len(sample):5.2f}%)")

    bpc0, n0 = kt_bpc(sample, lambda i, p: p)
    bpc1, n1 = kt_bpc(sample, lambda i, p: (p << 3) | cls[i])  # prev + class
    bpc2, n2 = kt_bpc(sample, lambda i, p: ((p & 0xF0) << 3) | cls[i])  # coarse prev + class
    # joint with previous class too
    def ctx_pc(i, p, _c=cls):
        pc = _c[i - 1] if i else PROSE
        return (p << 6) | (pc << 3) | _c[i]

    bpc3, n3 = kt_bpc(sample, ctx_pc)
    split_bpc, mask_bpc = split_kt_bpc(sample, cls)

    log("KT order-1 adaptive:")
    log(f"  baseline |prev           {bpc0:.4f} bpc  ctx={n0}")
    log(f"  joint    |prev,class     {bpc1:.4f} bpc  ctx={n1}  Δ={bpc1-bpc0:+.4f}")
    log(f"  joint    |prevHi,class   {bpc2:.4f} bpc  ctx={n2}  Δ={bpc2-bpc0:+.4f}")
    log(f"  joint    |prev,pc,class  {bpc3:.4f} bpc  ctx={n3}  Δ={bpc3-bpc0:+.4f}")
    log(
        f"  split streams + mask     {split_bpc:.4f}+{mask_bpc:.4f} = "
        f"{split_bpc+mask_bpc:.4f} bpc  Δ={split_bpc+mask_bpc-bpc0:+.4f}"
    )

    # zstd raw vs tagged (insert class nibble escape — usually loses)
    raw_z = zsize(sample, "raw")
    # tagged: before each byte, no — too heavy. Instead: leave raw (class is free at decode).
    log(f"zstd sample raw={raw_z:,} (class needs no storage if grammar-derived)")

    best = min(bpc1, bpc2, bpc3)
    if best < bpc0 - 0.01:
        log(f"VERDICT: joint class HELPS under KT (best Δ={best-bpc0:+.4f}) — candidate for fx2 mixer cxt")
    elif best < bpc0:
        log(f"VERDICT: tiny joint help Δ={best-bpc0:+.4f} — noise; try on cmix later")
    else:
        log(
            f"VERDICT: joint class does not help order-1 KT (best Δ={best-bpc0:+.4f}); "
            f"split+mask worse. Class may still help deeper mixers — defer fx2 wire."
        )
    log("done")


if __name__ == "__main__":
    main()
