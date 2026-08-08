#!/usr/bin/env python3
"""
Rebuild a PDF from raster text (e.g. JBIG2 page images): render each page, OCR with Tesseract,
emit a new PDF with native text at mapped positions (no page-bitmap images).

Goal: much smaller files than image-only scans. Semantic/layout fidelity is best-effort;
iterate on OCR language, DPI, and heuristics using reviewer feedback.

Layout: PyMuPDF ``insert_text`` uses a **top-left page origin, y downward** (aligned with
Tesseract pixels). For curved / skewed scans, a **least-squares baseline** is fit to word
**bottom** vs. center ``x`` (quadratic by default). **Lines** are formed by sorting on
residual (or ``cy``) and splitting only where consecutive keys jump by more than
``max(line_split_gap_mult×median_h, line_cluster_pct×median_h)``. A **second pass** merges
row fragments when they sit on the same scan line (tight ``cy`` / vertical gap) and read as
**horizontal continuation** (next chunk starts near the previous right edge, or the previous
chunk stopped short of the text block’s right margin). **Paragraphs** merge adjacent lines
when the vertical gap between line boxes is small. Drawn lines are **shrink-to-fit** to the
page width so text does not run off the page. Use ``benchmarks/pdf_ocr_rebuild_verify.py``
for span counts and overlap checks.

Dependencies
------------
  System: ``tesseract`` (e.g. ``pacman -S tesseract tesseract-data-eng``),
           optional language packs (``tesseract-data-fas``, etc.).
  Python: ``pip install -r benchmarks/requirements-ocr-pdf.txt``  (PyMuPDF)

Example
-------
  python3 benchmarks/pdf_rebuild_text_from_raster_ocr.py \\
    --input test_files72_sample_micro/Hadland_Davis_-_The_Persian_Mystics_Jami.pdf \\
    --output benchmarks/ocr_rebuild_output/Hadland_ocr_text_v1.pdf \\
    --dpi 300 --lang eng

  # Quick smoke (first 2 pages only):
  python3 benchmarks/pdf_rebuild_text_from_raster_ocr.py -i in.pdf -o out.pdf --max-pages 2

  # Force straight skew model only (no quadratic bend):
  python3 benchmarks/pdf_rebuild_text_from_raster_ocr.py -i in.pdf -o out.pdf --baseline-fit linear
"""

from __future__ import annotations

import argparse
import csv
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path

_OCR_PYTHON = Path(__file__).resolve().parents[1].parent / "OCR" / "python"
if _OCR_PYTHON.is_dir():
    sys.path.insert(0, str(_OCR_PYTHON))

try:
    from ocr_correction import correct_words  # type: ignore
    from ocr_tsv import parse_tsv_words as _shared_parse_tsv_words  # type: ignore
except ImportError:
    correct_words = None  # type: ignore
    _shared_parse_tsv_words = None  # type: ignore


def _require_tesseract() -> str:
    exe = shutil.which("tesseract")
    if not exe:
        print(
            "ERROR: tesseract not found on PATH.\n"
            "  Install (Arch): sudo pacman -S tesseract tesseract-data-eng\n"
            "  For Persian/Arabic text add: tesseract-data-fas / tesseract-data-ara",
            file=sys.stderr,
        )
        sys.exit(2)
    return exe


def _require_pymupdf():
    try:
        import fitz  # type: ignore  # PyMuPDF
    except ImportError:
        print(
            "ERROR: PyMuPDF not installed.\n"
            "  pip install -r benchmarks/requirements-ocr-pdf.txt",
            file=sys.stderr,
        )
        sys.exit(2)
    return fitz


def _ocr_page_png_tesseract(tsv_path: Path, png_path: Path, lang: str, tess: str) -> None:
    base = str(tsv_path.with_suffix(""))  # tesseract creates base.tsv
    cmd = [tess, str(png_path), base, "-l", lang, "--oem", "1", "--psm", "6", "tsv"]
    r = subprocess.run(
        cmd,
        capture_output=True,
        text=True,
        timeout=600,
    )
    if r.returncode != 0:
        raise RuntimeError(
            f"tesseract failed (exit {r.returncode}): {r.stderr or r.stdout}"
        )
    if not tsv_path.is_file():
        raise RuntimeError(f"expected TSV at {tsv_path}")


def _parse_tsv_words(tsv_path: Path, min_conf: float) -> list[dict]:
    if _shared_parse_tsv_words is not None:
        words, _, _ = _shared_parse_tsv_words(tsv_path, min_conf)
        if correct_words is not None:
            words = correct_words(words)
        return words
    words: list[dict] = []
    with tsv_path.open(newline="", encoding="utf-8", errors="replace") as f:
        reader = csv.DictReader(f, delimiter="\t")
        for row in reader:
            if row.get("level") != "5":
                continue
            try:
                conf = float(row.get("conf", "-1"))
            except ValueError:
                conf = -1.0
            if conf < min_conf:
                continue
            text = (row.get("text") or "").strip()
            if not text:
                continue
            try:
                left = int(row["left"])
                top = int(row["top"])
                w = int(row["width"])
                h = int(row["height"])
            except (KeyError, ValueError):
                continue
            words.append(
                {
                    "text": text,
                    "left": left,
                    "top": top,
                    "width": w,
                    "height": h,
                    "conf": conf,
                    "block": int(row.get("block_num", 0)),
                    "par": int(row.get("par_num", 0)),
                    "line": int(row.get("line_num", 0)),
                    "word": int(row.get("word_num", 0)),
                }
            )
    words.sort(key=lambda d: (d["block"], d["par"], d["line"], d["left"]))
    return words


def _baseline_y_page_top_origin(
    y0: float, top_px: int, height_px: int, sy: float, baseline_frac: float = 0.86
) -> float:
    """
    PyMuPDF ``insert_text`` uses the page's top-left origin with **y increasing downward**
    (same as Tesseract pixel coords). Map OCR word box to approximate text baseline y.
    """
    return y0 + (top_px + baseline_frac * float(height_px)) * sy


def _word_fontsize_from_height(height_px: int, page_h: float, pix_h: float) -> float:
    """Point size estimate from OCR box height (before line averaging)."""
    return height_px * page_h / float(pix_h)


def _cx_word(w: dict) -> float:
    return float(w["left"]) + 0.5 * float(w["width"])


def _bottom_word(w: dict) -> float:
    """Bottom edge of OCR box (pixel y, top-origin image)."""
    return float(w["top"]) + float(w["height"])


def _cy_word(w: dict) -> float:
    return w["top"] + 0.5 * float(w["height"])


def _median_word_height(usable: list[dict]) -> float:
    hs = sorted(w["height"] for w in usable)
    return float(hs[len(hs) // 2]) if hs else 12.0


def _line_merge_epsilon(usable: list[dict], line_cluster_pct: float) -> float:
    med_h = _median_word_height(usable)
    return max(1.5, float(line_cluster_pct) * med_h)


def _line_split_gap_px(usable: list[dict], line_split_gap_mult: float) -> float:
    """Min residual / cy gap (px) to start a new text line after sorting."""
    med_h = _median_word_height(usable)
    return max(5.0, float(line_split_gap_mult) * med_h)


def _combined_line_split_gap(
    usable: list[dict], line_split_gap_mult: float, line_cluster_pct: float
) -> float:
    """Use the larger of gap-mult and legacy cluster epsilon so small ``--line-cluster-pct`` still floors sensibly."""
    return max(
        _line_split_gap_px(usable, line_split_gap_mult),
        _line_merge_epsilon(usable, line_cluster_pct),
    )


def _split_sorted_words_by_key_gap(
    words: list[dict], key_fn, split_gap: float
) -> list[list[dict]]:
    """Sort by key_fn ascending; split when **consecutive** sorted keys differ by > split_gap."""
    if not words:
        return []
    s = sorted(words, key=key_fn)
    chunks: list[list[dict]] = []
    cur = [s[0]]
    for i in range(1, len(s)):
        w = s[i]
        prev = s[i - 1]
        if key_fn(w) - key_fn(prev) > split_gap:
            cur.sort(key=lambda x: x["left"])
            chunks.append(cur)
            cur = [w]
        else:
            cur.append(w)
    cur.sort(key=lambda x: x["left"])
    chunks.append(cur)
    return chunks


def _gaussian_solve_3(a: list[list[float]], b: list[float]) -> list[float] | None:
    """Solve 3x3 Ax=b with partial pivot; returns x or None if singular."""
    n = 3
    m = [row[:] + [b[i]] for i, row in enumerate(a)]
    for col in range(n):
        piv = max(range(col, n), key=lambda r: abs(m[r][col]))
        if abs(m[piv][col]) < 1e-12:
            return None
        if piv != col:
            m[col], m[piv] = m[piv], m[col]
        div = m[col][col]
        for j in range(col, n + 1):
            m[col][j] /= div
        for r in range(n):
            if r == col:
                continue
            f = m[r][col]
            if abs(f) < 1e-15:
                continue
            for j in range(col, n + 1):
                m[r][j] -= f * m[col][j]
    return [m[i][n] for i in range(n)]


def _gaussian_solve_2(a: list[list[float]], b: list[float]) -> list[float] | None:
    n = 2
    m = [row[:] + [b[i]] for i, row in enumerate(a)]
    for col in range(n):
        piv = max(range(col, n), key=lambda r: abs(m[r][col]))
        if abs(m[piv][col]) < 1e-12:
            return None
        if piv != col:
            m[col], m[piv] = m[piv], m[col]
        div = m[col][col]
        for j in range(col, n + 1):
            m[col][j] /= div
        for r in range(n):
            if r == col:
                continue
            f = m[r][col]
            if abs(f) < 1e-15:
                continue
            for j in range(col, n + 1):
                m[r][j] -= f * m[col][j]
    return [m[i][n] for i in range(n)]


def _polyfit_quadratic_bottom(xs: list[float], ys: list[float]) -> tuple[float, float, float] | None:
    """Return (a, b, c) with predicted bottom = a*x*x + b*x + c."""
    if len(xs) < 3:
        return None
    s1 = s2 = s3 = s4 = 0.0
    ty0 = ty1 = ty2 = 0.0
    n = 0
    for x, y in zip(xs, ys):
        x2 = x * x
        x3 = x2 * x
        x4 = x3 * x
        s1 += x
        s2 += x2
        s3 += x3
        s4 += x4
        ty0 += y
        ty1 += y * x
        ty2 += y * x2
        n += 1
    nf = float(n)
    a_mat = [[s4, s3, s2], [s3, s2, s1], [s2, s1, nf]]
    rhs = [ty2, ty1, ty0]
    sol = _gaussian_solve_3(a_mat, rhs)
    if sol is None:
        return None
    return (sol[0], sol[1], sol[2])


def _polyfit_linear_bottom(xs: list[float], ys: list[float]) -> tuple[float, float]:
    """Return (m, b) with predicted bottom = m*x + b."""
    n = len(xs)
    if n < 2:
        return (0.0, sum(ys) / float(max(1, n)))
    s1 = s2 = 0.0
    ty0 = ty1 = 0.0
    for x, y in zip(xs, ys):
        s1 += x
        s2 += x * x
        ty0 += y
        ty1 += y * x
    nf = float(n)
    sol = _gaussian_solve_2([[s2, s1], [s1, nf]], [ty1, ty0])
    if sol is None:
        return (0.0, ty0 / nf)
    return (sol[0], sol[1])


def _predict_bottom_curve(
    fit: str, coefs: tuple[float, ...], cx: float
) -> float:
    if fit == "quad":
        a, b, c = coefs[0], coefs[1], coefs[2]
        return ((a * cx) + b) * cx + c
    if fit == "linear":
        m, b0 = coefs[0], coefs[1]
        return m * cx + b0
    # const
    return float(coefs[0])


def _residual_bottom(w: dict, fit: str, coefs: tuple[float, ...]) -> float:
    return _bottom_word(w) - _predict_bottom_curve(fit, coefs, _cx_word(w))


def _cluster_words_axis_aligned(usable: list[dict], split_gap: float) -> list[list[dict]]:
    """Cluster by vertical center using gap split on sorted cy (no curved baseline)."""
    if not usable:
        return []
    lines = _split_sorted_words_by_key_gap(usable, _cy_word, split_gap)
    lines.sort(key=lambda ln: min(x["top"] for x in ln))
    return lines


def _cluster_words_along_bottom_1d(usable: list[dict], split_gap: float) -> list[list[dict]]:
    """Fallback when x spread is tiny: gap-split on bottom, then left order."""
    lines = _split_sorted_words_by_key_gap(usable, _bottom_word, split_gap)
    lines.sort(key=lambda ln: min(x["top"] for x in ln))
    return lines


def _cluster_words_curve_residual(
    usable: list[dict], split_gap: float, baseline_fit: str
) -> list[list[dict]]:
    """
    Fit bottom(cx) ≈ quadratic or linear, sort words by **residual** to that curve, then
    **split lines only where consecutive residuals jump** by more than ``split_gap``
    (~fraction of a text line). Within each line, words are ordered by ``cx``. This avoids
    the per-word regression from using a tolerance smaller than within-line residual spread.
    """
    if not usable:
        return []
    cxs = [_cx_word(w) for w in usable]
    xspan = max(cxs) - min(cxs)
    if xspan < 3.0:
        return _cluster_words_along_bottom_1d(usable, split_gap)

    bots = [_bottom_word(w) for w in usable]
    fit: str
    coefs: tuple[float, ...]

    want_quad = baseline_fit == "quadratic" and len(usable) >= 6
    q = _polyfit_quadratic_bottom(cxs, bots) if want_quad else None
    if q is not None:
        fit, coefs = "quad", q
    else:
        m, b0 = _polyfit_linear_bottom(cxs, bots)
        fit, coefs = "linear", (m, b0)

    def rkey(w: dict) -> float:
        return _residual_bottom(w, fit, coefs)

    lines = _split_sorted_words_by_key_gap(usable, rkey, split_gap)
    lines.sort(key=lambda ln: min(x["top"] for x in ln))
    return lines


def _cluster_words_into_lines(
    words: list[dict],
    line_cluster_pct: float,
    line_split_gap_mult: float,
    baseline_fit: str,
) -> list[list[dict]]:
    usable = [w for w in words if w.get("height", 0) > 0 and w.get("width", 0) > 0]
    if not usable:
        return []
    sg = _combined_line_split_gap(usable, line_split_gap_mult, line_cluster_pct)
    if baseline_fit == "none":
        return _cluster_words_axis_aligned(usable, sg)
    return _cluster_words_curve_residual(usable, sg, baseline_fit)


def _merged_line_string(line: list[dict]) -> str:
    parts = [(w["text"] or "").strip() for w in line]
    parts = [p for p in parts if p]
    return " ".join(parts)


def _line_band_top_height(line: list[dict]) -> tuple[int, int]:
    """Pixel (top, height) band covering all word boxes on the line."""
    min_top = min(w["top"] for w in line)
    max_bot = max(w["top"] + w["height"] for w in line)
    h = max(1, max_bot - min_top)
    return min_top, h


def _line_mean_fontsize(line: list[dict], page_h: float, pix_h: float) -> float:
    est = [_word_fontsize_from_height(w["height"], page_h, pix_h) for w in line if w["height"] > 0]
    if not est:
        return 11.0
    return max(4.0, min(48.0, sum(est) / float(len(est))))


def _words_flat(lines: list[list[dict]]) -> list[dict]:
    return [w for ln in lines for w in ln]


def _line_min_top(line: list[dict]) -> int:
    return min(w["top"] for w in line)


def _line_max_bottom(line: list[dict]) -> int:
    return max(w["top"] + w["height"] for w in line)


def _line_min_left(line: list[dict]) -> int:
    return min(w["left"] for w in line)


def _line_max_right(line: list[dict]) -> int:
    return max(w["left"] + w["width"] for w in line)


def _line_median_cy(line: list[dict]) -> float:
    ys = sorted(_cy_word(w) for w in line)
    return float(ys[len(ys) // 2])


def _post_merge_line_fragments(
    lines: list[list[dict]],
    med_h: float,
    pix_w: int,
) -> list[list[dict]]:
    """
    Join OCR rows that are one *visual* line split by imperfect baseline fit: same band in
    y, small vertical gap, and horizontal continuation (next line starts near previous right
    edge, or previous line ended short of the text block's right margin).
    """
    if len(lines) < 2:
        return [sorted(ln, key=lambda w: w["left"]) for ln in lines if ln]
    flat = _words_flat(lines)
    max_r = max((w["left"] + w["width"]) for w in flat) if flat else pix_w
    # Humans read “same line” as tight vertical band + next ink near previous right edge.
    cont_slop = max(22.0, 0.10 * float(pix_w))
    cy_tol = 0.26 * med_h
    v_tol = max(4.0, 0.44 * med_h)
    early_end = max(24.0, 0.09 * float(pix_w))

    cur = [
        sorted(list(ln), key=lambda w: w["left"])
        for ln in sorted(
            lines,
            key=lambda L: (_line_median_cy(L), _line_min_left(L)),
        )
        if ln
    ]
    if len(cur) < 2:
        return cur

    for _guard in range(48):
        changed = False
        out: list[list[dict]] = [cur[0]]
        for nxt in cur[1:]:
            prev = out[-1]
            vgap = float(_line_min_top(nxt) - _line_max_bottom(prev))
            c1 = _line_median_cy(prev)
            c2 = _line_median_cy(nxt)
            prev_r = float(_line_max_right(prev))
            nxt_l = float(_line_min_left(nxt))
            prev_l = float(_line_min_left(prev))
            # Horizontal gap between previous right edge and next left: small positive =
            # continuation; small negative = slight overlap (common in OCR).
            gap_x = nxt_l - prev_r
            horiz_cont = gap_x <= cont_slop and gap_x >= -min(40.0, cont_slop * 0.65)
            short_prev = prev_r < (float(max_r) - early_end)
            same_indent = nxt_l <= prev_l + max(30.0, 0.06 * float(pix_w))
            # Vertical overlap of row bands (positive ⇒ same scan line touching).
            overlap_y = float(_line_max_bottom(prev) - _line_min_top(nxt))
            touch_row = overlap_y >= -max(5.5, 0.15 * med_h)
            band = vgap <= v_tol and abs(c1 - c2) <= cy_tol
            if touch_row and horiz_cont:
                do_merge = True
            elif band and (horiz_cont or (short_prev and same_indent and nxt_l < prev_r + cont_slop * 2.5)):
                do_merge = True
            else:
                do_merge = False
            if do_merge:
                merged = sorted(prev + nxt, key=lambda w: w["left"])
                out[-1] = merged
                changed = True
            else:
                out.append(nxt)
        cur = out
        if not changed:
            break
    return cur


def _merge_lines_into_paragraphs(
    lines: list[list[dict]], med_h: float, para_gap_mult: float
) -> list[list[list[dict]]]:
    """Group consecutive text lines when inter-line gap is typographically ``small``."""
    if not lines:
        return []
    order = sorted(lines, key=_line_min_top)
    gap_px = max(6.0, float(para_gap_mult) * med_h)
    paras: list[list[list[dict]]] = [[order[0]]]
    for ln in order[1:]:
        g = _line_min_top(ln) - _line_max_bottom(paras[-1][-1])
        if g <= gap_px:
            paras[-1].append(ln)
        else:
            paras.append([ln])
    return paras


def _paragraph_mean_fontsize(para: list[list[dict]], page_h: float, pix_h: float) -> float:
    flat = [w for ln in para for w in ln]
    return _line_mean_fontsize(flat, page_h, pix_h)


def _emit_paragraphs_as_text_draws(
    new_page,
    paras: list[list[list[dict]]],
    x0: float,
    y0: float,
    x1: float,
    y1: float,
    sx: float,
    sy: float,
    page_h: float,
    ph: float,
    fontname: str,
    line_leading: float,
    fitz,
) -> None:
    """One ``insert_text`` per text line; shrink-to-fit width so lines stay on-page."""
    margin = 5.0
    max_text_w = max(20.0, (x1 - x0) - 2.0 * margin)
    y_max = y1 - margin

    for para in paras:
        if not para:
            continue
        fs_para = _paragraph_mean_fontsize(para, page_h, ph)
        fs_para = max(4.0, min(48.0, fs_para))
        min_left_px = min(w["left"] for ln in para for w in ln)
        x_pdf = x0 + float(min_left_px) * sx
        x_pdf = max(x0 + margin, min(x_pdf, x1 - margin - 20.0))

        min_top, band = _line_band_top_height(para[0])
        y = _baseline_y_page_top_origin(y0, min_top, band, sy)

        for li, line in enumerate(para):
            if li > 0:
                y += fs_para * line_leading
            if y > y_max:
                break
            merged = _merged_line_string(line)
            if not merged:
                continue
            fs_use = fs_para
            tw = float(fitz.get_text_length(merged, fontname=fontname, fontsize=fs_use))
            while tw > max_text_w and fs_use > 4.0:
                fs_use -= 0.5
                tw = float(fitz.get_text_length(merged, fontname=fontname, fontsize=fs_use))
            fs_use = max(4.0, fs_use)
            tw = float(fitz.get_text_length(merged, fontname=fontname, fontsize=fs_use))
            x_draw = x_pdf
            if x_draw + tw > x1 - margin:
                x_draw = max(x0 + margin, x1 - margin - tw)
            try:
                new_page.insert_text(
                    (x_draw, y),
                    merged,
                    fontname=fontname,
                    fontsize=fs_use,
                    render_mode=0,
                )
            except Exception:
                new_page.insert_text((x_draw, y), merged, fontsize=fs_use, render_mode=0)


def rebuild_pdf(
    input_pdf: Path,
    output_pdf: Path,
    *,
    dpi: int,
    lang: str,
    min_conf: float,
    max_pages: int | None,
    fontname: str,
    dry_run: bool,
    line_cluster_pct: float,
    line_split_gap_mult: float,
    baseline_fit: str,
    merge_paragraphs: bool,
    para_gap_mult: float,
    line_leading: float,
) -> None:
    fitz = _require_pymupdf()
    tess = _require_tesseract()

    doc = fitz.open(str(input_pdf))
    out = None if dry_run else fitz.open()

    tmp = tempfile.mkdtemp(prefix="fz_ocr_")
    try:
        n = doc.page_count
        limit = n if max_pages is None else min(n, max_pages)
        for i in range(limit):
            page = doc.load_page(i)
            mat = fitz.Matrix(dpi / 72.0, dpi / 72.0)
            pix = page.get_pixmap(matrix=mat, alpha=False)
            png_path = Path(tmp) / f"p{i:05d}.png"
            pix.save(str(png_path))

            tsv_base = Path(tmp) / f"p{i:05d}"
            tsv_path = tsv_base.with_suffix(".tsv")
            _ocr_page_png_tesseract(tsv_path, png_path, lang, tess)
            words = _parse_tsv_words(tsv_path, min_conf)

            rect = page.rect
            x0, y0, x1, y1 = rect.x0, rect.y0, rect.x1, rect.y1
            pw, ph = pix.width, pix.height
            page_h = y1 - y0
            page_w = x1 - x0
            sx = page_w / float(pw)
            sy = page_h / float(ph)

            lines = _cluster_words_into_lines(
                words, line_cluster_pct, line_split_gap_mult, baseline_fit
            )
            pre_merge_lines = len(lines)
            flatw = _words_flat(lines)
            med_h = _median_word_height(flatw) if flatw else 12.0
            lines = _post_merge_line_fragments(lines, med_h, pw)
            flatw = _words_flat(lines)
            med_h = _median_word_height(flatw) if flatw else med_h
            if merge_paragraphs and lines:
                paras = _merge_lines_into_paragraphs(lines, med_h, para_gap_mult)
            else:
                paras = [[ln] for ln in lines] if lines else []
            n_draws = sum(len(p) for p in paras)

            if dry_run:
                print(
                    f"page {i+1}/{limit}: words={len(words)} "
                    f"lines_pre_merge={pre_merge_lines} lines={len(lines)} "
                    f"paragraphs={len(paras)} text_draws≈{n_draws}",
                    flush=True,
                )
                continue

            assert out is not None
            new_page = out.new_page(width=rect.width, height=rect.height)
            _emit_paragraphs_as_text_draws(
                new_page,
                paras,
                x0,
                y0,
                x1,
                y1,
                sx,
                sy,
                page_h,
                float(ph),
                fontname,
                line_leading,
                fitz,
            )

        if dry_run:
            print(f"dry-run: would write {limit} pages to {output_pdf}")
        else:
            assert out is not None
            output_pdf.parent.mkdir(parents=True, exist_ok=True)
            out.save(str(output_pdf))
            print(
                f"Wrote {output_pdf}  pages={limit}  (input_pages={n})  "
                f"input_bytes={input_pdf.stat().st_size}  output_bytes={output_pdf.stat().st_size}"
            )
    finally:
        shutil.rmtree(tmp, ignore_errors=True)
        if out is not None:
            out.close()
        doc.close()


def main() -> None:
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--input", "-i", type=Path, required=True, help="Source PDF (scanned / JBIG2 text)")
    ap.add_argument("--output", "-o", type=Path, required=True, help="Output PDF (vector text)")
    ap.add_argument("--dpi", type=int, default=300, help="Render DPI for OCR (default 300)")
    ap.add_argument("--lang", type=str, default="eng", help="Tesseract -l (default eng); try eng+fra, fas, …")
    ap.add_argument(
        "--min-conf",
        type=float,
        default=35.0,
        help="Minimum Tesseract word confidence (0–100, default 35)",
    )
    ap.add_argument("--max-pages", type=int, default=None, help="Only first N pages (debug)")
    ap.add_argument(
        "--font",
        dest="fontname",
        default="times-roman",
        help="PDF Base-14 font (default times-roman); layout is approximate vs scan fonts",
    )
    ap.add_argument(
        "--line-cluster-pct",
        type=float,
        default=0.05,
        metavar="PCT",
        help="Floors line-split gap with max(1.5, PCT×median word height); combine with --line-split-gap-mult (default 0.05)",
    )
    ap.add_argument(
        "--line-split-gap-mult",
        type=float,
        default=0.28,
        metavar="MULT",
        help="Typical line split: gap ≥ max(5px, MULT×median word height) on sorted residual/cy (default 0.28)",
    )
    ap.add_argument(
        "--baseline-fit",
        choices=("quadratic", "linear", "none"),
        default="quadratic",
        help="Fit word-bottom vs center-x before line clustering: quadratic (default), linear, or none (axis-aligned cy)",
    )
    ap.add_argument(
        "--no-para-merge",
        action="store_true",
        help="Do not merge lines into paragraphs (more text draws; debugging)",
    )
    ap.add_argument(
        "--para-gap-mult",
        type=float,
        default=1.55,
        metavar="MULT",
        help="Merge lines into one paragraph if gap ≤ max(6px, MULT×median word height) (default 1.55)",
    )
    ap.add_argument(
        "--line-leading",
        type=float,
        default=1.18,
        metavar="MULT",
        help="PDF line spacing as multiple of paragraph font size between merged lines (default 1.18)",
    )
    ap.add_argument("--dry-run", action="store_true", help="OCR only, do not write PDF")
    args = ap.parse_args()

    if not args.input.is_file():
        print(f"ERROR: input not found: {args.input}", file=sys.stderr)
        sys.exit(1)

    rebuild_pdf(
        args.input.resolve(),
        args.output.resolve(),
        dpi=args.dpi,
        lang=args.lang,
        min_conf=args.min_conf,
        max_pages=args.max_pages,
        fontname=args.fontname,
        dry_run=args.dry_run,
        line_cluster_pct=args.line_cluster_pct,
        line_split_gap_mult=args.line_split_gap_mult,
        baseline_fit=args.baseline_fit,
        merge_paragraphs=not args.no_para_merge,
        para_gap_mult=args.para_gap_mult,
        line_leading=args.line_leading,
    )


if __name__ == "__main__":
    main()
