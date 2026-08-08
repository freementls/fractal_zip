#!/usr/bin/env python3
"""Upload slim fractal_zip → public_html/fractal_zip on freement.cloud.

Keeps small challenge corpora; skips multi‑GiB lakes, encode dumps, and tool builds.
Reads FTP password from FileZilla recentservers.xml (or FRACTAL_ZIP_FTP_PASS / CONVERT_FTP_PASS).
"""
from __future__ import annotations

import base64
import ftplib
import os
import sys
import xml.etree.ElementTree as ET
from concurrent.futures import ThreadPoolExecutor, as_completed
from pathlib import Path

LOCAL = Path("/srv/http/fractal_zip")
REMOTE = "/public_html/fractal_zip"
FTP_HOST = "ftp.freement.cloud"
FTP_USER = "upload@freement.cloud"

# Challenge corpora shipped to live (small / documented bytes wins).
KEEP_TEST_DIRS = {
    "test_files",
    "test_files2",
    "test_files4",
    "test_files10",
    "test_files11",
    "test_files13",
    "test_files28",
    "test_files29",
    "test_files35",
    "test_files49",
    "test_files50",
    "test_files51",
    "test_files52",
    "test_files53",
    "test_files56_sample",
    "test_files57",
    "test_files60",
    "test_files61",
    "test_files62",
    "test_files63",
    "test_files69",
    "test_files74",
    "test_files75",
    "test_files76",
    "test_files81",
    "test_files114",
}

SKIP_DIRS = {
    ".git",
    ".cursor",
    ".github",
    "node_modules",
    "__pycache__",
    "desktop",
    "tools",
    "tmp",
    "-o",
    ".venv_hutter",
    "vendor",
}

SKIP_NAMES = {".DS_Store", "Thumbs.db", "enwik8", "enwik9", "calgary.zip", "cc580.zip"}
SKIP_SUFFIXES = (
    ".fz",
    ".fzc",
    ".fractalzip",
    ".paq8px215",
    ".pyc",
    ".o",
)


def ftp_password() -> str:
    for key in ("FRACTAL_ZIP_FTP_PASS", "CONVERT_FTP_PASS"):
        env = os.environ.get(key)
        if env:
            return env
    recent = Path.home() / ".config/filezilla/recentservers.xml"
    root = ET.parse(recent).getroot()
    for server in root.iter("Server"):
        host = (server.findtext("Host") or "").lower()
        user = server.findtext("User") or ""
        if "freement" in host and "upload" in user:
            pw = server.find("Pass")
            if pw is not None and (pw.text or ""):
                return base64.b64decode(pw.text).decode()
    raise SystemExit("No FTP password (set FRACTAL_ZIP_FTP_PASS or use FileZilla saved site)")


def connect() -> ftplib.FTP:
    ftp = ftplib.FTP(FTP_HOST, timeout=180)
    ftp.login(FTP_USER, ftp_password())
    ftp.set_pasv(True)
    return ftp


def ensure_dir(ftp: ftplib.FTP, path: str) -> None:
    cur = ""
    for part in [p for p in path.split("/") if p]:
        cur += "/" + part
        try:
            ftp.mkd(cur)
        except ftplib.error_perm:
            pass


def collect() -> list[tuple[Path, str]]:
    items: list[tuple[Path, str]] = []
    for dirpath, dirnames, filenames in os.walk(LOCAL):
        rel_dir = Path(dirpath).relative_to(LOCAL)
        keep = []
        for d in dirnames:
            child_rel = rel_dir / d if str(rel_dir) != "." else Path(d)
            top = child_rel.parts[0]
            if d in SKIP_DIRS or d.startswith("."):
                # allow benchmarks/.gitignore-only via files; skip hidden dirs
                if not (rel_dir == Path("benchmarks") and d == ".gitignore"):
                    if d.startswith("."):
                        continue
            if d in SKIP_DIRS:
                continue
            if d.startswith(("fzarc_", "fzauto_", "fztmp_", "fzao_", "fzzpaqr_", "fzocin_", "tmp")):
                continue
            if top.startswith("test_files") and top not in KEEP_TEST_DIRS:
                continue
            if rel_dir == Path(".") and top.startswith("test_files") and top not in KEEP_TEST_DIRS:
                continue
            if d in {"tools", "desktop", "vendor"}:
                continue
            # benchmarks: ship PHP/MD/SH helpers only — prune cache dirs
            if rel_dir == Path("benchmarks") and d.startswith("."):
                continue
            keep.append(d)
        dirnames[:] = keep
        for name in filenames:
            if name in SKIP_NAMES or name.startswith(
                ("fzi_", "fztmp_", "fzocin_", "fzao_", "fzzpaqr_", "fzarc_", "fzauto_", "Shining Force")
            ):
                continue
            if any(name.endswith(suf) for suf in SKIP_SUFFIXES):
                continue
            if name.startswith(".") and name not in {".htaccess", ".user.ini", ".gitignore"}:
                continue
            if rel_dir == Path(".") and name.endswith((".gif", ".zip", ".bin")) and name != "busy-cats.gif":
                continue
            local = Path(dirpath) / name
            if not local.is_file():
                continue
            try:
                sz = local.stat().st_size
            except OSError:
                continue
            top = rel_dir.parts[0] if rel_dir.parts else ""
            # Root-level test_files* dirs already filtered; cap stray large files
            if sz > 12 * 1024 * 1024 and top not in KEEP_TEST_DIRS:
                continue
            # benchmarks: skip bulky JSON/log dumps
            if top == "benchmarks" and name.endswith((".json", ".log", ".txt", ".raw", ".mid", ".dic")):
                if name not in {
                    "convert_bench_manifest.json",
                    "fractal_bytes_floors.json",
                    "lstm_speed_opt_catalog.json",
                }:
                    continue
            rel = rel_dir / name if str(rel_dir) != "." else Path(name)
            items.append((local, f"{REMOTE}/{rel.as_posix()}"))
    return items


def upload_one(local: Path, remote: str) -> tuple[str, str | None]:
    ftp = connect()
    try:
        ensure_dir(ftp, str(Path(remote).parent).replace("\\", "/"))
        with local.open("rb") as fh:
            ftp.storbinary(f"STOR {remote}", fh)
        return remote, None
    except Exception as e:
        return remote, str(e)
    finally:
        try:
            ftp.quit()
        except Exception:
            pass


def main() -> int:
    if not LOCAL.is_dir():
        raise SystemExit(f"missing {LOCAL}")
    items = collect()
    print(f"fractal_zip: {len(items)} files -> {REMOTE}")
    ftp = connect()
    ensure_dir(ftp, REMOTE)
    # writable web job dirs
    for sub in ("examples/web_jobs", "examples/web_uploads"):
        ensure_dir(ftp, f"{REMOTE}/{sub}")
    ftp.quit()

    ok = fail = 0
    with ThreadPoolExecutor(max_workers=6) as pool:
        futs = [pool.submit(upload_one, local, remote) for local, remote in items]
        for i, fut in enumerate(as_completed(futs), 1):
            remote, err = fut.result()
            if err:
                fail += 1
                print(f"FAIL {remote}: {err}")
            else:
                ok += 1
            if i % 100 == 0 or i == len(futs):
                print(f"  … {i}/{len(futs)} (ok={ok} fail={fail})")
    print(f"fractal_zip done ok={ok} fail={fail}")
    return 0 if fail == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
