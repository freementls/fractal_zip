#!/usr/bin/env python3
"""
MoPAQ semantic helper for fractal_zip literal bundles.

Peel: mpyq extract → FMQ2 inner. By default accepts any smpq repack whose *extracted*
members match the original (semantic equivalence). Set
FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_STRICT_BYTES=1 to require byte-identical repack instead.

FMQ2: magic + uint8 mpq_version (1|2) + uint8 comp_index + uint32_be n + [nl,name,dl,data]...

Requires: python3, smpq on PATH. Vendored mpyq: tools/third_party/mpyq.py (BSD).

Usage:
  fractal_zip_mpq_semantic.py peel <input.mpq> <inner-out.bin>
  fractal_zip_mpq_semantic.py pack <inner-in.bin> <output.mpq>
  fractal_zip_mpq_semantic.py repack-smaller <input.mpq> <output.mpq>
  fractal_zip_mpq_semantic.py semantic-equal <file.a> <file.b>
"""

import os
import shutil
import struct
import subprocess
import sys
import tempfile
from typing import Dict, List, Optional, Tuple

MAGIC1 = b"FMQ1"
MAGIC2 = b"FMQ2"

# smpq -C values (StormLib); full peel order when FRACTAL_ZIP_MPQ_SMPQ_FULL_SCAN=1
_COMP_FLAGS = ["ZLIB", "BZIP2", "LZMA", "SPARSE", "HUFFMANN", "none"]

# Default repack-smaller: few tries per file (full grid is very slow on large replay sets).
_COMP_TRY_SHORT = [(1, "ZLIB"), (1, "BZIP2"), (1, "LZMA"), (2, "ZLIB"), (2, "LZMA")]


def _full_smpq_scan() -> bool:
    v = os.environ.get("FRACTAL_ZIP_MPQ_SMPQ_FULL_SCAN", "")
    return v.strip().lower() in ("1", "true", "yes", "on")


def _strict_bytes() -> bool:
    v = os.environ.get("FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_STRICT_BYTES", "")
    v = v.strip().lower()
    return v in ("1", "true", "yes", "on")


def _mpq_archive_class():
    root = os.path.dirname(os.path.abspath(__file__))
    sys.path.insert(0, os.path.join(root, "third_party"))
    from mpyq import MPQArchive  # type: ignore

    return MPQArchive


def _extract_dict_from_bytes(data: bytes) -> Dict[bytes, bytes]:
    import io

    MPQArchive = _mpq_archive_class()
    a = MPQArchive(io.BytesIO(data))
    if not a.files:
        raise RuntimeError("no listfile")
    out = {}  # type: Dict[bytes, bytes]
    for fn in a.files:
        key = fn if isinstance(fn, (bytes, bytearray)) else str(fn).encode("utf-8")
        if b"\x00" in key:
            raise RuntimeError("nul in name")
        raw = a.read_file(fn)
        out[key] = raw if isinstance(raw, (bytes, bytearray)) else b""
    return out


def _serialize_fmq2(files: Dict[bytes, bytes], mpq_ver: int, comp_index: int) -> bytes:
    if comp_index < 0 or comp_index >= len(_COMP_FLAGS):
        raise ValueError("comp_index")
    if mpq_ver not in (1, 2):
        raise ValueError("mpq_ver")
    body = [MAGIC2, bytes([mpq_ver, comp_index]), struct.pack(">I", len(files))]
    for name in sorted(files.keys()):
        data = files[name]
        body.append(struct.pack(">I", len(name)))
        body.append(name)
        body.append(struct.pack(">I", len(data)))
        body.append(data)
    return b"".join(body)


def _parse_inner(blob: bytes) -> Tuple[Dict[bytes, bytes], int, str]:
    """Returns (files, mpq_version, compression_flag_string)."""
    if len(blob) < 8:
        raise ValueError("short")
    if blob[:4] == MAGIC2:
        mpq_ver = blob[4]
        comp_index = blob[5]
        if mpq_ver not in (1, 2):
            raise ValueError("bad mpq_ver")
        if comp_index >= len(_COMP_FLAGS):
            raise ValueError("bad comp_index")
        comp = _COMP_FLAGS[comp_index]
        pos = 6
        (n,) = struct.unpack(">I", blob[pos : pos + 4])
        pos += 4
        out = {}  # type: Dict[bytes, bytes]
        for _ in range(n):
            if pos + 4 > len(blob):
                raise ValueError("truncated")
            (nl,) = struct.unpack(">I", blob[pos : pos + 4])
            pos += 4
            if pos + nl > len(blob):
                raise ValueError("truncated name")
            name = bytes(blob[pos : pos + nl])
            pos += nl
            if pos + 4 > len(blob):
                raise ValueError("truncated")
            (dl,) = struct.unpack(">I", blob[pos : pos + 4])
            pos += 4
            if pos + dl > len(blob):
                raise ValueError("truncated data")
            data = bytes(blob[pos : pos + dl])
            pos += dl
            out[name] = data
        if pos != len(blob):
            raise ValueError("trailing junk")
        return out, int(mpq_ver), comp
    if blob[:4] == MAGIC1:
        (n,) = struct.unpack(">I", blob[4:8])
        pos = 8
        out = {}  # type: Dict[bytes, bytes]
        for _ in range(n):
            if pos + 4 > len(blob):
                raise ValueError("truncated")
            (nl,) = struct.unpack(">I", blob[pos : pos + 4])
            pos += 4
            if pos + nl > len(blob):
                raise ValueError("truncated name")
            name = bytes(blob[pos : pos + nl])
            pos += nl
            if pos + 4 > len(blob):
                raise ValueError("truncated")
            (dl,) = struct.unpack(">I", blob[pos : pos + 4])
            pos += 4
            if pos + dl > len(blob):
                raise ValueError("truncated data")
            data = bytes(blob[pos : pos + dl])
            pos += dl
            out[name] = data
        if pos != len(blob):
            raise ValueError("trailing junk")
        return out, 1, "ZLIB"
    raise ValueError("bad magic")


def _smpq_pack(
    workdir: str, rel_files: List[str], out_arc: str, smpq: str, mpq_ver: int, compression: str
) -> None:
    cmd = [
        smpq,
        "-c",
        "-q",
        "-M",
        str(int(mpq_ver)),
        "-C",
        compression,
        "-f",
        out_arc,
    ] + rel_files
    subprocess.run(
        cmd,
        cwd=workdir,
        check=True,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )


def _iter_pack_attempts() -> List[Tuple[int, str, int]]:
    """Yields (mpq_ver, comp_name, comp_index_for_flags)."""
    if _full_smpq_scan():
        out = []  # type: List[Tuple[int, str, int]]
        for mpq_ver in (1, 2):
            for ci, comp in enumerate(_COMP_FLAGS):
                out.append((mpq_ver, comp, ci))
        return out
    out = []
    for mpq_ver, comp in _COMP_TRY_SHORT:
        try:
            ci = _COMP_FLAGS.index(comp)
        except ValueError:
            continue
        out.append((mpq_ver, comp, ci))
    return out


def _best_semantic_repack_params(
    files: Dict[bytes, bytes],
    w: str,
    rels: List[str],
    smpq: str,
    orig: bytes,
    strict: bool,
) -> Optional[Tuple[int, int]]:
    """Smallest (by container bytes) (mpq_ver, comp_index) with valid strict/semantic match."""
    best: Optional[Tuple[int, int]] = None
    best_len: Optional[int] = None
    for mpq_ver, comp, ci in _iter_pack_attempts():
        td = tempfile.mkdtemp(prefix="fzmqtry_")
        try:
            test_arc = os.path.join(td, "t.mpq")
            _smpq_pack(w, rels, test_arc, smpq, mpq_ver, comp)
            with open(test_arc, "rb") as f:
                rep = f.read()
            if strict:
                ok = rep == orig
            else:
                try:
                    got = _extract_dict_from_bytes(rep)
                except Exception:
                    ok = False
                else:
                    ok = got == files
            if not ok:
                continue
            ln = len(rep)
            if best_len is None or ln < best_len:
                best_len = ln
                best = (mpq_ver, ci)
        except Exception:
            continue
        finally:
            shutil.rmtree(td, ignore_errors=True)
    return best


def _smallest_semantic_repack_blob(
    files: Dict[bytes, bytes],
    w: str,
    rels: List[str],
    smpq: str,
    orig: bytes,
) -> Optional[bytes]:
    """Smallest MPQ bytes with same mpyq extract as orig; None if no improvement."""
    best_rep: Optional[bytes] = None
    best_len = len(orig)
    for mpq_ver, comp, _ci in _iter_pack_attempts():
        td = tempfile.mkdtemp(prefix="fzmqsm_")
        try:
            test_arc = os.path.join(td, "t.mpq")
            _smpq_pack(w, rels, test_arc, smpq, mpq_ver, comp)
            with open(test_arc, "rb") as f:
                rep = f.read()
            try:
                if _extract_dict_from_bytes(rep) != files:
                    continue
            except Exception:
                continue
            if len(rep) < best_len:
                best_len = len(rep)
                best_rep = rep
        except Exception:
            continue
        finally:
            shutil.rmtree(td, ignore_errors=True)
    return best_rep


def cmd_peel(inp: str, outp: str) -> int:
    with open(inp, "rb") as f:
        orig = f.read()
    if len(orig) < 4 or orig[:3] != b"MPQ":
        return 2
    smpq = shutil.which("smpq")
    if not smpq:
        return 3
    strict = _strict_bytes()
    try:
        files = _extract_dict_from_bytes(orig)
    except Exception:
        return 2
    td = tempfile.mkdtemp(prefix="fzmqp_")
    try:
        w = os.path.join(td, "w")
        os.makedirs(w, exist_ok=True)
        rels = []  # type: List[str]
        for name in sorted(files.keys()):
            rel = name.decode("utf-8")
            rels.append(rel)
            path = os.path.join(w, rel)
            parent = os.path.dirname(path)
            if parent and parent != w:
                os.makedirs(parent, exist_ok=True)
            with open(path, "wb") as fh:
                fh.write(files[name])
        params = _best_semantic_repack_params(files, w, rels, smpq, orig, strict)
        if params is None:
            return 4
        mv, ci = params
        inner = _serialize_fmq2(files, mv, ci)
        with open(outp, "wb") as f:
            f.write(inner)
        return 0
    except Exception:
        return 4
    finally:
        shutil.rmtree(td, ignore_errors=True)


def cmd_pack(inp: str, outp: str) -> int:
    smpq = shutil.which("smpq")
    if not smpq:
        return 3
    with open(inp, "rb") as f:
        blob = f.read()
    try:
        files, mpq_ver, comp = _parse_inner(blob)
    except Exception:
        return 4
    td = tempfile.mkdtemp(prefix="fzmqk_")
    try:
        w = os.path.join(td, "w")
        os.makedirs(w, exist_ok=True)
        rels = []  # type: List[str]
        for name in sorted(files.keys()):
            rel = name.decode("utf-8")
            rels.append(rel)
            path = os.path.join(w, rel)
            parent = os.path.dirname(path)
            if parent and parent != w:
                os.makedirs(parent, exist_ok=True)
            with open(path, "wb") as fh:
                fh.write(files[name])
        _smpq_pack(w, rels, outp, smpq, mpq_ver, comp)
        return 0
    except Exception:
        return 4
    finally:
        shutil.rmtree(td, ignore_errors=True)


def cmd_repack_smaller(inp: str, outp: str) -> int:
    with open(inp, "rb") as f:
        orig = f.read()
    if len(orig) < 4 or orig[:3] != b"MPQ":
        return 2
    smpq = shutil.which("smpq")
    if not smpq:
        return 3
    try:
        files = _extract_dict_from_bytes(orig)
    except Exception:
        return 2
    td = tempfile.mkdtemp(prefix="fzmqrs_")
    try:
        w = os.path.join(td, "w")
        os.makedirs(w, exist_ok=True)
        rels = []  # type: List[str]
        for name in sorted(files.keys()):
            rel = name.decode("utf-8")
            rels.append(rel)
            path = os.path.join(w, rel)
            parent = os.path.dirname(path)
            if parent and parent != w:
                os.makedirs(parent, exist_ok=True)
            with open(path, "wb") as fh:
                fh.write(files[name])
        best = _smallest_semantic_repack_blob(files, w, rels, smpq, orig)
        if best is None:
            return 1
        with open(outp, "wb") as f:
            f.write(best)
        return 0
    except Exception:
        return 4
    finally:
        shutil.rmtree(td, ignore_errors=True)


def cmd_semantic_equal(a: str, b: str) -> int:
    try:
        with open(a, "rb") as f:
            da = f.read()
        with open(b, "rb") as f:
            db = f.read()
    except Exception:
        return 1
    if da == db:
        return 0
    try:
        fa = _extract_dict_from_bytes(da)
        fb = _extract_dict_from_bytes(db)
    except Exception:
        return 1
    return 0 if fa == fb else 1


def main() -> int:
    if len(sys.argv) != 4:
        sys.stderr.write(
            "usage: fractal_zip_mpq_semantic.py peel <in.mpq> <inner.bin>\n"
            "       fractal_zip_mpq_semantic.py pack <inner.bin> <out.mpq>\n"
            "       fractal_zip_mpq_semantic.py repack-smaller <in.mpq> <out.mpq>\n"
            "       fractal_zip_mpq_semantic.py semantic-equal <a> <b>\n"
        )
        return 2
    op = sys.argv[1]
    a0, a1 = sys.argv[2], sys.argv[3]
    if op == "peel":
        return cmd_peel(a0, a1)
    if op == "pack":
        return cmd_pack(a0, a1)
    if op == "repack-smaller":
        return cmd_repack_smaller(a0, a1)
    if op == "semantic-equal":
        return cmd_semantic_equal(a0, a1)
    sys.stderr.write("unknown op\n")
    return 2


if __name__ == "__main__":
    sys.exit(main())
