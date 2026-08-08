#!/usr/bin/env python3
"""
Verify an OCR-rebuilt PDF against heuristics (no golden truth):

  * file size (smaller than raster-heavy source is expected)
  * text span count on a chosen page (few spans ⇒ merged lines vs one span per word)
  * total character coverage (sanity)
  * pairwise bbox IoU among spans (overlaps should be rare)
  * font size min / max / stdev (should be tight if one size per paragraph)

Example (page 7 = 1-based index 7):

  python3 benchmarks/pdf_ocr_rebuild_verify.py \\
    --rebuilt benchmarks/ocr_rebuild_output/Hadland_ocr_text_v3.pdf \\
    --compare test_files72_sample_micro/Hadland_Davis_-_The_Persian_Mystics_Jami.pdf \\
    --page 7
"""

from __future__ import annotations

import argparse
import statistics
import sys
from pathlib import Path


def _require_fitz():
    try:
        import fitz  # type: ignore
    except ImportError:
        print("ERROR: pip install pymupdf", file=sys.stderr)
        sys.exit(2)
    return fitz


def _bbox_iou(a: tuple[float, float, float, float], b: tuple[float, float, float, float]) -> float:
    ax0, ay0, ax1, ay1 = a
    bx0, by0, bx1, by1 = b
    ix0, iy0 = max(ax0, bx0), max(ay0, by0)
    ix1, iy1 = min(ax1, bx1), min(ay1, by1)
    iw, ih = max(0.0, ix1 - ix0), max(0.0, iy1 - iy0)
    inter = iw * ih
    if inter <= 0:
        return 0.0
    aa = max(0.0, ax1 - ax0) * max(0.0, ay1 - ay0)
    ba = max(0.0, bx1 - bx0) * max(0.0, by1 - by0)
    union = aa + ba - inter
    return inter / union if union > 0 else 0.0


def _page_spans(page) -> list[dict]:
    d = page.get_text("dict")
    out: list[dict] = []
    for b in d.get("blocks", []):
        if b.get("type") != 0:
            continue
        for line in b.get("lines", []):
            for s in line.get("spans", []):
                bb = s.get("bbox")
                if not bb or len(bb) < 4:
                    continue
                bbox = (float(bb[0]), float(bb[1]), float(bb[2]), float(bb[3]))
                out.append(
                    {
                        "bbox": bbox,
                        "size": float(s.get("size") or 0.0),
                        "text": s.get("text") or "",
                    }
                )
    return out


def analyze_page(doc, page_index: int, iou_warn: float) -> None:
    if page_index < 0 or page_index >= doc.page_count:
        print(f"ERROR: page_index {page_index} out of range (0..{doc.page_count - 1})", file=sys.stderr)
        sys.exit(1)
    page = doc.load_page(page_index)
    spans = _page_spans(page)
    n = len(spans)
    chars = sum(len(s["text"]) for s in spans)
    sizes = [s["size"] for s in spans if s["size"] > 0.1]
    ov = 0
    for i in range(n):
        for j in range(i + 1, n):
            if _bbox_iou(spans[i]["bbox"], spans[j]["bbox"]) >= iou_warn:
                ov += 1
    print(f"  page_index={page_index} (1-based page number = {page_index + 1})")
    print(f"  text_spans={n}  total_chars_in_spans={chars}")
    if sizes:
        print(
            f"  font_size_pt: min={min(sizes):.2f} max={max(sizes):.2f} "
            f"stdev={statistics.pstdev(sizes) if len(sizes) > 1 else 0.0:.3f}"
        )
    else:
        print("  font_size_pt: (no spans)")
    print(f"  span_pairs_with_IoU>={iou_warn}: {ov}")


def main() -> None:
    fitz = _require_fitz()
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--rebuilt", type=Path, required=True, help="OCR-rebuilt PDF")
    ap.add_argument(
        "--compare",
        type=Path,
        default=None,
        help="Optional original PDF to print byte ratio",
    )
    ap.add_argument(
        "--page",
        type=int,
        default=7,
        help="1-based page number to analyze (default 7)",
    )
    ap.add_argument(
        "--iou-warn",
        type=float,
        default=0.22,
        help="Flag span pairs with axis-aligned IoU ≥ this (default 0.22)",
    )
    args = ap.parse_args()
    if not args.rebuilt.is_file():
        print(f"ERROR: missing {args.rebuilt}", file=sys.stderr)
        sys.exit(1)

    rb = args.rebuilt.stat().st_size
    print(f"rebuilt_bytes={rb}  ({args.rebuilt})")
    if args.compare is not None and args.compare.is_file():
        ob = args.compare.stat().st_size
        ratio = rb / ob if ob else 0.0
        print(f"compare_bytes={ob}  rebuilt/original_size_ratio={ratio:.4f}")

    doc = fitz.open(str(args.rebuilt))
    try:
        p0 = args.page - 1 if args.page >= 1 else args.page
        print("analysis:")
        analyze_page(doc, p0, args.iou_warn)
    finally:
        doc.close()


if __name__ == "__main__":
    main()
