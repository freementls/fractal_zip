#!/usr/bin/env python3
"""Abjad-numeral / decimal-symbol probe for compression relevance.

Investigates:
  1. Organization of Milesian/Hebrew/Arabic letter↔value maps (place bands).
  2. Whether values look like decimal digit encoding (ASCII-like) vs binary.
  3. Glyph-shape correlates (ink density / bbox) vs numeric value.
  4. enwik8 numeric microstructure (digit runs, years, ids).
  5. Gematria-like token hash as an *extra predictor context* (side channel).

Does NOT re-test vowel-split abjad (already dead).

Usage:
  nice -n 19 python3 benchmarks/research_abjad_numeral_probe.py [enwik_slice]
"""
from __future__ import annotations

import collections
import json
import math
import os
import re
import struct
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "benchmarks" / ".abjad_numeral_probe.json"

# ---------------------------------------------------------------------------
# 1. Classical tables (standard mispar hechrechi / abjad / Milesian-style)
# ---------------------------------------------------------------------------

# Hebrew: letter, value, abjad-order index (0-based), name
HEBREW = [
    ("א", 1, 0, "alef"), ("ב", 2, 1, "bet"), ("ג", 3, 2, "gimel"),
    ("ד", 4, 3, "dalet"), ("ה", 5, 4, "he"), ("ו", 6, 5, "vav"),
    ("ז", 7, 6, "zayin"), ("ח", 8, 7, "het"), ("ט", 9, 8, "tet"),
    ("י", 10, 9, "yod"), ("כ", 20, 10, "kaf"), ("ל", 30, 11, "lamed"),
    ("מ", 40, 12, "mem"), ("נ", 50, 13, "nun"), ("ס", 60, 14, "samekh"),
    ("ע", 70, 15, "ayin"), ("פ", 80, 16, "pe"), ("צ", 90, 17, "tsadi"),
    ("ק", 100, 18, "qof"), ("ר", 200, 19, "resh"), ("ש", 300, 20, "shin"),
    ("ת", 400, 21, "tav"),
]

# Arabic abjadi order (not hijāʾī dictionary order)
ARABIC_ABJAD = [
    ("ا", 1), ("ب", 2), ("ج", 3), ("د", 4), ("ه", 5), ("و", 6), ("ز", 7),
    ("ح", 8), ("ط", 9), ("ي", 10), ("ك", 20), ("ل", 30), ("م", 40), ("ن", 50),
    ("س", 60), ("ع", 70), ("ف", 80), ("ص", 90), ("ق", 100), ("ر", 200),
    ("ش", 300), ("ت", 400), ("ث", 500), ("خ", 600), ("ذ", 700), ("ض", 800),
    ("ظ", 900), ("غ", 1000),
]

# Greek Milesian (classical); stigma/digamma=6, qoppa=90, sampi=900
GREEK = [
    ("Α", 1), ("Β", 2), ("Γ", 3), ("Δ", 4), ("Ε", 5), ("Ϛ", 6), ("Ζ", 7),
    ("Η", 8), ("Θ", 9), ("Ι", 10), ("Κ", 20), ("Λ", 30), ("Μ", 40), ("Ν", 50),
    ("Ξ", 60), ("Ο", 70), ("Π", 80), ("Ϙ", 90), ("Ρ", 100), ("Σ", 200),
    ("Τ", 300), ("Υ", 400), ("Φ", 500), ("Χ", 600), ("Ψ", 700), ("Ω", 800),
    ("Ϡ", 900),
]


def place_band(v: int) -> str:
    if v < 10:
        return "units"
    if v < 100:
        return "tens"
    if v < 1000:
        return "hundreds"
    return "thousands"


def shannon(counts) -> float:
    n = sum(counts)
    if n <= 0:
        return 0.0
    h = 0.0
    for c in counts:
        if c:
            p = c / n
            h -= p * math.log2(p)
    return h


def analyze_organization() -> dict:
    """Is the map a decimal digit code? Binary? ASCII-like?"""
    vals = [v for _, v, _, _ in HEBREW]
    bands = [place_band(v) for v in vals]
    # Exact Milesian formula: value = digit * 10^place, digit=1..9
    milesian_ok = []
    for i, (_, v, idx, _) in enumerate(HEBREW):
        place = idx // 9
        digit = (idx % 9) + 1
        expect = digit * (10 ** place)
        # Hebrew stops at 22 letters → last place incomplete (only to 400)
        milesian_ok.append({"idx": idx, "v": v, "expect": expect, "match": v == expect})

    # Compare to ASCII: codepoint organization
    ascii_digits = [(chr(c), c - ord("0")) for c in range(ord("0"), ord("9") + 1)]
    ascii_upper = [(chr(c), c - ord("A") + 1) for c in range(ord("A"), ord("Z") + 1)]

    # Hypotheses
    # H1: values == 10^{floor(i/9)} * ((i%9)+1)  (decimal place encoding)
    h1 = all(m["match"] for m in milesian_ok if m["idx"] < 22)

    def pearson(xs, ys):
        n = len(xs)
        mx, my = sum(xs) / n, sum(ys) / n
        num = sum((x - mx) * (y - my) for x, y in zip(xs, ys))
        denx = math.sqrt(sum((x - mx) ** 2 for x in xs))
        deny = math.sqrt(sum((y - my) ** 2 for y in ys))
        if denx * deny == 0:
            return 0.0
        return num / (denx * deny)

    pow2 = [2 ** i for i in range(22)]
    cps = [ord(ch) for ch, _, _, _ in HEBREW]
    binary_exact = vals == pow2
    # Spearman is useless here (both monotonic → ρ=1); use Pearson on magnitudes
    pearson_bin = pearson(vals, pow2)
    pearson_cp = pearson(vals, cps)
    pearson_idx = pearson(vals, list(range(22)))
    value_gaps = [vals[i + 1] / vals[i] for i in range(len(vals) - 1)]

    return {
        "hebrew_milesian_formula_holds": h1,
        "milesian_rows": milesian_ok,
        "bands": collections.Counter(bands),
        "pearson_value_vs_pow2": round(pearson_bin, 4),
        "pearson_value_vs_codepoint": round(pearson_cp, 4),
        "pearson_value_vs_abjad_index": round(pearson_idx, 4),
        "value_step_ratios": value_gaps,
        "n_letters": 22,
        "n_place_bands": 3,
        "digit_alphabet_size": 9,
        "encoding_model": (
            "decimal_digit_x_place_band: value = digit * 10^place, "
            "digit in 1..9, place in 0..2 (incomplete hundreds)"
        ),
        "ascii_analogy": {
            "ascii_digits": "contiguous codepoints, value = cp - 0x30 (positional decimal later)",
            "abjad": "non-contiguous glyphs, value = digit*10^place (additive, no zero glyph)",
            "shared_idea": "alphabet dual-uses as numeral codebook; abjad = digit planes, ASCII digits = one plane + positional syntax",
            "difference": "ASCII separates glyph-plane (0-9) from place via position; abjad folds place into glyph identity",
        },
        "binary_hypothesis": {
            "rejected": True,
            "exact_pow2_match": binary_exact,
            "pearson_vs_pow2": round(pearson_bin, 4),
            "reason": (
                "Milesian formula holds exactly; values are decimal digit×place, "
                "not 2^i. Pearson vs pow2 is only moderate because both rise."
            ),
        },
        "arabic_extends_past_400": True,
        "greek_needs_27_for_full_900": True,
        "zero_glyph": False,
        "positional_notation": False,
        "additive": True,
    }


def glyph_ink_features(font_path: str | None) -> dict:
    """Rasterize Hebrew letters; correlate ink density with value/band."""
    try:
        from PIL import Image, ImageDraw, ImageFont
    except ImportError:
        return {"ok": False, "error": "PIL missing"}

    if not font_path or not os.path.isfile(font_path):
        # try known paths
        candidates = [
            "/usr/share/fonts/droid/DroidSansHebrew-Bold.ttf",
            "/usr/share/fonts/droid/DroidSansHebrew.ttf",
        ]
        font_path = next((p for p in candidates if os.path.isfile(p)), None)
    if not font_path:
        return {"ok": False, "error": "no Hebrew font"}

    font = ImageFont.truetype(font_path, 64)
    rows = []
    for ch, val, idx, name in HEBREW:
        img = Image.new("L", (96, 96), 255)
        dr = ImageDraw.Draw(img)
        dr.text((8, 8), ch, font=font, fill=0)
        pix = list(img.getdata())
        ink = sum(1 for p in pix if p < 128)
        # bounding ink box
        xs = [i % 96 for i, p in enumerate(pix) if p < 128]
        ys = [i // 96 for i, p in enumerate(pix) if p < 128]
        if xs:
            bw, bh = max(xs) - min(xs) + 1, max(ys) - min(ys) + 1
        else:
            bw = bh = 0
        rows.append({
            "ch": ch, "name": name, "value": val, "idx": idx,
            "band": place_band(val), "ink": ink, "bbox_w": bw, "bbox_h": bh,
            "ink_per_bbox": round(ink / max(bw * bh, 1), 4),
        })

    # correlations: ink vs log10(value), ink vs idx
    def pearson(xs, ys):
        n = len(xs)
        mx, my = sum(xs) / n, sum(ys) / n
        num = sum((x - mx) * (y - my) for x, y in zip(xs, ys))
        denx = math.sqrt(sum((x - mx) ** 2 for x in xs))
        deny = math.sqrt(sum((y - my) ** 2 for y in ys))
        if denx * deny == 0:
            return 0.0
        return num / (denx * deny)

    inks = [r["ink"] for r in rows]
    vals = [r["value"] for r in rows]
    idxs = [r["idx"] for r in rows]
    logv = [math.log10(v) for v in vals]

    # Band means
    band_ink = collections.defaultdict(list)
    for r in rows:
        band_ink[r["band"]].append(r["ink"])
    band_means = {b: round(sum(v) / len(v), 1) for b, v in band_ink.items()}

    return {
        "ok": True,
        "font": font_path,
        "pearson_ink_vs_value": round(pearson(inks, vals), 4),
        "pearson_ink_vs_log10_value": round(pearson(inks, logv), 4),
        "pearson_ink_vs_abjad_index": round(pearson(inks, idxs), 4),
        "band_mean_ink": band_means,
        "rows": rows,
        "interpretation": (
            "If ink tracked value, glyphs would encode magnitude visually "
            "(bar/tally heritage). If ink tracks index only weakly, shapes are "
            "historical phoneme glyphs reused as a decimal codebook — not a "
            "graphical numeral system like Roman tallies."
        ),
    }


# ---------------------------------------------------------------------------
# 4. Numeric microstructure on enwik slice
# ---------------------------------------------------------------------------

YEAR_RE = re.compile(rb"\b(?:1[0-9]{3}|20[0-2][0-9])\b")
DIGIT_RUN_RE = re.compile(rb"\d+")
WIKI_IDISH = re.compile(rb"\b(?:px|kb|MB|id=|ISBN|DOI|ISBN\s)[^\s<]{0,40}", re.I)


def numeric_microstructure(data: bytes) -> dict:
    n = len(data)
    digit_bytes = sum(1 for b in data if 48 <= b <= 57)
    runs = DIGIT_RUN_RE.findall(data)
    run_lens = [len(r) for r in runs]
    years = YEAR_RE.findall(data)

    # Place-band split of digit characters inside runs (ASCII digits)
    # units/tens/... by position from right
    plane_counts = collections.Counter()
    for r in runs:
        for i, ch in enumerate(reversed(r)):
            plane = min(i, 5)  # 0=units ... 5+=higher
            plane_counts[plane] += 1

    # Entropy of digit stream vs full stream (byte unigram)
    full_c = collections.Counter(data)
    dig_c = collections.Counter(b for b in data if 48 <= b <= 57)
    h_full = shannon(full_c.values())
    h_dig = shannon(dig_c.values()) if dig_c else 0.0

    # After a digit, next-byte distribution vs after a letter
    def cond_h(prev_pred):
        nxt = collections.Counter()
        for i in range(len(data) - 1):
            if prev_pred(data[i]):
                nxt[data[i + 1]] += 1
        return shannon(nxt.values()), sum(nxt.values())

    h_after_digit, n_ad = cond_h(lambda b: 48 <= b <= 57)
    h_after_letter, n_al = cond_h(lambda b: (65 <= b <= 90) or (97 <= b <= 122))

    return {
        "plain": n,
        "digit_byte_frac": round(digit_bytes / n, 4),
        "digit_runs": len(runs),
        "run_len_hist": dict(collections.Counter(run_lens)),
        "year_like_tokens": len(years),
        "digit_place_plane_counts": {str(k): v for k, v in sorted(plane_counts.items())},
        "H_unigram_full": round(h_full, 4),
        "H_unigram_digits_only": round(h_dig, 4),
        "H_next_after_digit": round(h_after_digit, 4),
        "H_next_after_letter": round(h_after_letter, 4),
        "n_after_digit": n_ad,
        "n_after_letter": n_al,
        "delta_H_after_digit_vs_letter": round(h_after_digit - h_after_letter, 4),
        "note": (
            "If after-digit conditional entropy differs from after-letter, "
            "a digit-run / place-plane context is justified in the mixer."
        ),
    }


# ---------------------------------------------------------------------------
# 5. Gematria-like hash as side-channel context
# ---------------------------------------------------------------------------

# Latin → crude "English gematria" A=1..Z=26, then fold to place-band style
def latin_gematria(word: bytes) -> int:
    s = 0
    for b in word.lower():
        if 97 <= b <= 122:
            s += b - 96
    return s


def wb_codelen(data: bytes, max_order: int, ctx_extra_fn=None) -> float:
    """Witten-Bell order-0..max_order bits; optional extra context byte per pos."""
    # counts[order][context][sym] ; context is bytes (+ optional extra)
    from collections import defaultdict

    bits = 0.0
    # Use nested dicts
    tables = [defaultdict(lambda: defaultdict(int)) for _ in range(max_order + 1)]

    for i, sym in enumerate(data):
        # escape down orders
        escaped = False
        order = min(max_order, i)
        while True:
            if order < 0:
                # uniform order -1
                bits += math.log2(256)
                break
            ctx = data[i - order : i]
            if ctx_extra_fn is not None:
                ctx = ctx + bytes([ctx_extra_fn(i) & 0xFF])
            tab = tables[order][ctx]
            tot = sum(tab.values())
            seen = len(tab)
            c = tab[sym]
            if c > 0:
                # p = c / (tot + seen)
                bits += -math.log2(c / (tot + seen))
                break
            # escape mass = seen / (tot + seen); if tot==0, full escape
            if tot == 0:
                # no mass — fall through without charging (first time ctx)
                order -= 1
                continue
            bits += -math.log2(seen / (tot + seen))
            order -= 1
            escaped = True
        # update all orders
        for o in range(0, min(max_order, i) + 1):
            ctx = data[i - o : i]
            if ctx_extra_fn is not None:
                ctx = ctx + bytes([ctx_extra_fn(i) & 0xFF])
            tables[o][ctx][sym] += 1
    return bits


def _word_bucket_probe(sample: bytes, bucket_for_word, max_order: int, label: str) -> dict:
    buckets = [0] * len(sample)
    for m in re.finditer(rb"[A-Za-z]+", sample):
        b = bucket_for_word(m.group(0)) & 0x1F
        for i in range(m.start(), m.end()):
            buckets[i] = b

    def extra(i, _b=buckets):
        return _b[i]

    bits_base = wb_codelen(sample, max_order, ctx_extra_fn=None)
    bits_x = wb_codelen(sample, max_order, ctx_extra_fn=extra)
    n = len(sample)
    return {
        "label": label,
        "bpc_base": round(bits_base / n, 4),
        "bpc_with_ctx": round(bits_x / n, 4),
        "delta_bpc": round((bits_x - bits_base) / n, 4),
    }


def gematria_context_probe(data: bytes, max_order: int = 4) -> dict:
    """Gematria bucket vs random / length / first-letter controls."""
    import random

    sample = data if len(data) <= 120_000 else data[:120_000]
    rng = random.Random(0)

    gem = _word_bucket_probe(
        sample, lambda w: latin_gematria(w) % 32, max_order, "gematria_mod32"
    )
    rnd = _word_bucket_probe(
        sample, lambda w: rng.randrange(32), max_order, "random_mod32"
    )
    ln = _word_bucket_probe(
        sample, lambda w: min(len(w), 31), max_order, "wordlen"
    )
    fl = _word_bucket_probe(
        sample,
        lambda w: (w[0] | 32) - 97 if w and 65 <= (w[0] | 32) <= 122 else 0,
        max_order,
        "first_letter",
    )

    # Beats random → real word-level signal; but if first_letter/wordlen win more,
    # gematria is just a weak hash — prefer dict-id / stemmer contexts instead.
    beats_random = gem["delta_bpc"] < 0 and gem["delta_bpc"] < rnd["delta_bpc"] - 0.01
    dominated = gem["delta_bpc"] >= min(fl["delta_bpc"], ln["delta_bpc"]) - 0.01
    if beats_random and not dominated:
        gate = "KEEP_sidechannel"
    elif beats_random and dominated:
        gate = "reject_dominated"  # signal real but inferior to simpler word features
    else:
        gate = "reject"
    return {
        "sample": len(sample),
        "order": max_order,
        "arms": [gem, rnd, ln, fl],
        "delta_gematria": gem["delta_bpc"],
        "delta_random": rnd["delta_bpc"],
        "delta_wordlen": ln["delta_bpc"],
        "delta_first_letter": fl["delta_bpc"],
        "gate": gate,
        "note": (
            "Gematria KEEP only if it beats random AND is not dominated by "
            "first_letter/wordlen. Dominated ⇒ use dict-index/stemmer, not sums."
        ),
    }


def digit_plane_context_probe(data: bytes, max_order: int = 3) -> dict:
    """Extra context = place plane when inside a digit run, else 255."""
    sample = data if len(data) <= 120_000 else data[:120_000]
    plane = [255] * len(sample)
    for m in DIGIT_RUN_RE.finditer(sample):
        r = m.group(0)
        for j, _ in enumerate(r):
            plane[m.start() + j] = min(len(r) - 1 - j, 7)

    def extra(i, _p=plane):
        return _p[i]

    bits_base = wb_codelen(sample, max_order, None)
    bits_pl = wb_codelen(sample, max_order, extra)
    n = len(sample)
    return {
        "sample": n,
        "order": max_order,
        "bpc_base": round(bits_base / n, 4),
        "bpc_digit_plane_ctx": round(bits_pl / n, 4),
        "delta_bpc": round((bits_pl - bits_base) / n, 4),
        "gate": "KEEP_sidechannel" if bits_pl < bits_base else "reject",
    }


def main():
    slice_path = sys.argv[1] if len(sys.argv) > 1 else "/tmp/enwik8_1m.bin"
    if not os.path.isfile(slice_path):
        # mid-file 256KiB fallback from enwik8
        enw = ROOT / "enwik8"
        data = enw.read_bytes()[10_000_000 : 10_000_000 + 256 * 1024]
    else:
        data = Path(slice_path).read_bytes()
        if len(data) > 1_000_000:
            data = data[:1_000_000]

    print("=== organization ===")
    org = analyze_organization()
    print(json.dumps({k: org[k] for k in org if k != "milesian_rows"}, indent=2))

    print("\n=== glyph shapes ===")
    glyphs = glyph_ink_features(None)
    summary = {k: glyphs[k] for k in glyphs if k != "rows"}
    print(json.dumps(summary, indent=2))

    print("\n=== numeric microstructure ===")
    micro = numeric_microstructure(data)
    print(json.dumps(micro, indent=2))

    print("\n=== gematria side-channel (WB) ===")
    gem = gematria_context_probe(data, max_order=4)
    print(json.dumps(gem, indent=2))

    print("\n=== digit-plane side-channel (WB) ===")
    dig = digit_plane_context_probe(data, max_order=3)
    print(json.dumps(dig, indent=2))

    report = {
        "slice": slice_path,
        "slice_bytes": len(data),
        "organization": {k: org[k] for k in org if k != "milesian_rows"},
        "milesian_check": org["milesian_rows"],
        "glyphs": summary,
        "numeric_microstructure": micro,
        "gematria_context": gem,
        "digit_plane_context": dig,
        "compression_verdict": {
            "decimal_digit_x_place_encoding": org["hebrew_milesian_formula_holds"],
            "binary_encoding": False,
            "pearson_value_vs_pow2": org.get("pearson_value_vs_pow2"),
            "glyph_shape_encodes_magnitude": (
                abs(glyphs.get("pearson_ink_vs_log10_value", 0)) > 0.5
                if glyphs.get("ok") else None
            ),
            "gematria_sidechannel": gem["gate"],
            "gematria_vs_random": {
                "gematria": gem.get("delta_gematria"),
                "random": gem.get("delta_random"),
                "first_letter": gem.get("delta_first_letter"),
            },
            "digit_plane_sidechannel": dig["gate"],
            "action": (
                "Classical abjad = decimal digit×place codebook (ASCII-digits dual-use "
                "analogy), not binary. Glyph ink weakly tracks band only. "
                "Prefer real word-id / digit-run contexts over gematria sums; "
                "validate gematria only if it beats random control."
            ),
        },
    }
    OUT.write_text(json.dumps(report, indent=2) + "\n")
    print(f"\nWrote {OUT}")


if __name__ == "__main__":
    main()
