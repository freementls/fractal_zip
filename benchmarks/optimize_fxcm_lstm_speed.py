#!/usr/bin/env python3
"""Small-set optimizer: legalize large fractal LSTM via compile-time speed opts.

Mirrors the fz/LOM tune loop — screen candidates on a tiny mid first, gate on
bytes+wall, promote survivors — instead of accepting LSTM384's 1.74× tax.

  python3 benchmarks/optimize_fxcm_lstm_speed.py \
      --catalog benchmarks/lstm_speed_opt_catalog.json \
      --slice 256k --rounds 2

Gates (defaults tuned for reclaiming LSTM384 over banked LSTM320):
  * bytes ≤ baseline_384 + max_tax_vs_384  (default +200)
  * bytes ≤ beat_320 - min_beat_320         (default still ≥8B better than 320)
  * wall  ≤ baseline_384 / min_speedup      (default ≥1.15× vs full 384)
  * wall  ≤ fractalv2b_base * max_vs_base   (default ≤1.35×)

Refuses to run compress while another fractalv2 cmix is live (serialize).
Build step uses `make clean cmix` into run/cmix_opt_<name> (does not overwrite
banked run/cmix_match3m_fractalv2_lstm{320,384}).
"""
from __future__ import annotations

import argparse
import json
import os
import statistics
import subprocess
import sys
import time
from pathlib import Path

ROOT = Path("/srv/http/fractal_zip")
FX2 = ROOT / "tools/hutter/fx2-cmix"
RUN = FX2 / "run"
DICT = ROOT / "benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
OUT_DIR = ROOT / "benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt"
SLICES = {
    "256k": ROOT / "tools/hutter/fx2-cmix/run_speed/probe256k.mid",
    "1m": ROOT / "benchmarks/.ladder_cache/enwik8_1m_skip10.mid",
    "10m": ROOT / "benchmarks/.ladder_cache/enwik8_10m_skip20.mid",
}


def log(msg: str) -> None:
    print(msg, flush=True)


def fractal_cmix_busy() -> bool:
    """True if a live fractalv2/opt *binary* (argv0) is compressing."""
    try:
        for p in Path("/proc").iterdir():
            if not p.name.isdigit():
                continue
            try:
                raw = (p / "cmdline").read_bytes()
            except OSError:
                continue
            if not raw:
                continue
            argv0 = raw.split(b"\0", 1)[0].decode(errors="replace")
            # Match executable path only — not shells/scripts that mention it.
            if argv0.endswith("/cmix_match3m_fractalv2") or "/cmix_match3m_fractalv2_" in argv0:
                return True
            if "/cmix_opt_" in argv0 and argv0.rsplit("/", 1)[-1].startswith("cmix_opt_"):
                return True
        return False
    except FileNotFoundError:
        return False


def ensure_slice(name: str) -> Path:
    p = SLICES[name]
    if name == "256k" and not p.is_file():
        src = SLICES["1m"]
        p.parent.mkdir(parents=True, exist_ok=True)
        p.write_bytes(src.read_bytes()[:262144])
        log(f"wrote {p} from 1m head")
    if not p.is_file():
        raise SystemExit(f"missing slice {p}")
    return p


def build_candidate(name: str, base_defs: list[str], extra: list[str]) -> Path:
    out_bin = RUN / f"cmix_opt_{name}"
    defs = " ".join(base_defs + extra)
    env = os.environ.copy()
    env["OPT_NAME"] = name
    env["OPT_EXTRA_DEFS"] = defs
    cmd = ["make", "-j", str(os.cpu_count() or 4), "match3m_fractal_lstm_opt"]
    log(f"BUILD {name}: OPT_EXTRA_DEFS={defs}")
    r = subprocess.run(cmd, cwd=str(FX2), capture_output=True, text=True, env=env)
    (OUT_DIR / f"build_{name}.log").write_text(r.stdout + "\n" + r.stderr)
    if r.returncode != 0 or not out_bin.is_file():
        raise SystemExit(f"build failed for {name}; see {OUT_DIR}/build_{name}.log")
    return out_bin


def run_compress(bin_path: Path, mid: Path, tag: str, retries: int = 1) -> tuple[int, float]:
    out = OUT_DIR / f"{tag}.fx2"
    err = OUT_DIR / f"{tag}.err"
    env = os.environ.copy()
    env["FXCM_RECIPE_MIXER_BITMASK"] = "2"
    last_rc = None
    for attempt in range(retries + 1):
        if out.exists():
            out.unlink()
        temp = Path(str(out) + ".cmix.temp")
        if temp.exists():
            temp.unlink()
        t0 = time.perf_counter()
        r = subprocess.run(
            [str(bin_path), "-c", str(DICT), str(mid), str(out)],
            stdout=subprocess.DEVNULL, stderr=err.open("w"), env=env,
        )
        wall = time.perf_counter() - t0
        last_rc = r.returncode
        if r.returncode == 0 and out.is_file() and out.stat().st_size > 0:
            return out.stat().st_size, wall
        # -9 = SIGKILL (OOM / session reaper); retry once after a short pause
        if attempt < retries and r.returncode in (-9, 9, 137):
            log(f"  RETRY {tag} after rc={r.returncode}")
            time.sleep(5)
            continue
        break
    raise SystemExit(f"compress failed {tag} rc={last_rc}")


def _write_summary(args, b0, w0, b320, w320, b384, w384, results) -> Path:
    out_json = OUT_DIR / f"leaderboard_{args.slice}.json"
    # Merge by name so --only runs don't wipe prior candidates on this slice.
    merged = list(results)
    if out_json.is_file():
        try:
            prev = json.loads(out_json.read_text())
            by_name = {r["name"]: r for r in prev.get("results", []) if "name" in r}
            for r in results:
                by_name[r["name"]] = r
            # Preserve prior order, append new names at end.
            ordered = []
            seen = set()
            for r in prev.get("results", []):
                n = r.get("name")
                if n in by_name:
                    ordered.append(by_name[n]); seen.add(n)
            for r in results:
                if r["name"] not in seen:
                    ordered.append(r)
            merged = ordered
        except (json.JSONDecodeError, KeyError, TypeError):
            pass
    summary = {
        "slice": args.slice,
        "rounds": args.rounds,
        "ref": {
            "base_bytes": b0, "base_wall": round(w0, 2),
            "lstm320_bytes": b320, "lstm320_wall": round(w320, 2),
            "lstm384_bytes": b384, "lstm384_wall": round(w384, 2),
        },
        "gates": {
            "max_tax_vs_384": args.max_tax_vs_384,
            "min_beat_320": args.min_beat_320,
            "min_speedup_vs_384": args.min_speedup_vs_384,
            "max_vs_base": args.max_vs_base,
        },
        "results": merged,
    }
    out_json.write_text(json.dumps(summary, indent=2) + "\n")
    return out_json


def median_pair(bin_path: Path, mid: Path, tag: str, rounds: int) -> tuple[int, float]:
    sizes, walls = [], []
    for i in range(rounds):
        b, w = run_compress(bin_path, mid, f"{tag}_r{i}")
        sizes.append(b)
        walls.append(w)
        log(f"  {tag} r{i}: bytes={b} wall={w:.1f}s")
    return int(statistics.median(sizes)), float(statistics.median(walls))


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--catalog", default=str(ROOT / "benchmarks/lstm_speed_opt_catalog.json"))
    ap.add_argument("--slice", choices=list(SLICES), default="256k")
    ap.add_argument("--rounds", type=int, default=2)
    ap.add_argument("--only", default="", help="comma names to run (default: all)")
    ap.add_argument("--skip-build", action="store_true")
    ap.add_argument("--allow-busy", action="store_true",
                    help="run even if fractalv2 cmix already live (not recommended)")
    ap.add_argument("--max-tax-vs-384", type=int, default=200)
    ap.add_argument("--min-beat-320", type=int, default=8)
    ap.add_argument("--min-speedup-vs-384", type=float, default=1.15)
    ap.add_argument("--max-vs-base", type=float, default=1.35)
    ap.add_argument("--baseline-bin", default=str(RUN / "cmix_match3m_fractalv2_lstm384"),
                    help="speed reference binary (labeled lstm384 in gates)")
    ap.add_argument("--beat-bin", default=str(RUN / "cmix_match3m_fractalv2_lstm320"),
                    help="byte reference to beat (labeled lstm320 in gates)")
    ap.add_argument("--base-bin", default=str(RUN / "cmix_match3m_fractalv2b"))
    ap.add_argument("--tag-prefix", default="cand",
                    help="artifact tag prefix (avoid collisions across catalogs)")
    ap.add_argument(
        "--reuse-refs",
        default="",
        help="skip ref compress: b0,w0,b320,w320,b384,w384 (from banked ladder)",
    )
    args = ap.parse_args()

    OUT_DIR.mkdir(parents=True, exist_ok=True)
    cat = json.loads(Path(args.catalog).read_text())
    mid = ensure_slice(args.slice)
    only = {x.strip() for x in args.only.split(",") if x.strip()}

    if fractal_cmix_busy() and not args.allow_busy:
        raise SystemExit(
            "REFUSE: fractalv2 cmix already running — wait for 100m/other job, "
            "or pass --allow-busy"
        )

    baseline = Path(args.baseline_bin)
    beat = Path(args.beat_bin)
    base = Path(args.base_bin)
    for p in (baseline, beat, base):
        if not p.is_file():
            raise SystemExit(f"missing binary {p}")

    log(f"SLICE {args.slice}={mid} rounds={args.rounds}")
    log("REF baseline=lstm384 beat=lstm320 base=fractalv2b")
    if args.reuse_refs:
        parts = [float(x) for x in args.reuse_refs.split(",")]
        if len(parts) != 6:
            raise SystemExit("--reuse-refs needs b0,w0,b320,w320,b384,w384")
        b0, w0, b320, w320, b384, w384 = parts
        b0, b320, b384 = int(b0), int(b320), int(b384)
        log(f"REF REUSE bytes base={b0} 320={b320} 384={b384} | wall {w0:.1f}/{w320:.1f}/{w384:.1f}")
    else:
        b384, w384 = median_pair(baseline, mid, "ref_lstm384", args.rounds)
        if beat.resolve() == baseline.resolve():
            b320, w320 = b384, w384
            log("REF beat==baseline — reuse wall/bytes for lstm320")
        else:
            b320, w320 = median_pair(beat, mid, "ref_lstm320", args.rounds)
        b0, w0 = median_pair(base, mid, "ref_base", args.rounds)
        log(f"REF bytes base={b0} 320={b320} 384={b384} | wall {w0:.1f}/{w320:.1f}/{w384:.1f}")

    results = []
    for cand in cat["candidates"]:
        name = cand["name"]
        if only and name not in only:
            continue
        bin_path = RUN / f"cmix_opt_{name}"
        if not args.skip_build:
            bin_path = build_candidate(name, cat["base_defs"], cand["extra_defs"])
        elif not bin_path.is_file():
            log(f"SKIP {name}: not built")
            continue

        try:
            sz, wall = median_pair(
                bin_path, mid, f"{args.tag_prefix}_{name}", args.rounds
            )
        except SystemExit as e:
            log(f"FAIL {name}: {e}")
            results.append({"name": name, "error": str(e)})
            continue

        tax384 = sz - b384
        vs320 = sz - b320
        speedup384 = (w384 / wall) if wall else 0.0
        vs_base = (wall / w0) if w0 else 999.0
        # At short slices 320 can beat 384 on bytes (seen @256k/@1m); only require
        # beating 320 when 384 is already the byte champ on this slice.
        inverted = b384 > b320
        if inverted:
            keep_bytes = tax384 <= args.max_tax_vs_384
        else:
            keep_bytes = (
                tax384 <= args.max_tax_vs_384 and vs320 <= -args.min_beat_320
            )
        keep_time = (
            speedup384 >= args.min_speedup_vs_384 and vs_base <= args.max_vs_base
        )
        # Hygiene: near-384 bytes + faster, but still respect max_vs_base
        hygiene = (
            tax384 <= 8
            and speedup384 >= 1.05
            and vs_base <= args.max_vs_base
        )
        # Bytes win vs 384 + some speedup, but wall still above legalization:
        weak = (
            tax384 <= 0
            and speedup384 >= 1.08
            and vs_base > args.max_vs_base
            and vs320 <= -args.min_beat_320
        )
        if (keep_bytes and keep_time) or hygiene:
            verdict = "PROMOTE"
        elif weak:
            verdict = "WEAK_PROMOTE"
        else:
            verdict = "REJECT"
        if inverted and verdict == "PROMOTE":
            row_note = "inverted_slice: 320 beats 384 — promote on speed+near384 only"
        elif verdict == "WEAK_PROMOTE":
            row_note = "bytes≤384 + faster, but wall×base above legalization cap"
        else:
            row_note = cand.get("note", "")
        row = {
            "name": name,
            "note": row_note,
            "bytes": sz,
            "wall_s": round(wall, 2),
            "Δ384": tax384,
            "Δ320": vs320,
            "speedup_vs_384": round(speedup384, 3),
            "wall×_vs_base": round(vs_base, 3),
            "inverted_slice": inverted,
            "verdict": verdict,
        }
        results.append(row)
        log(
            f"CAND {name}: bytes={sz} Δ384={tax384:+d} Δ320={vs320:+d} "
            f"wall={wall:.1f}s ({speedup384:.2f}× vs384, {vs_base:.2f}× base) → {verdict}"
        )
        # Incremental save so a crash mid-catalog still leaves a leaderboard.
        _write_summary(args, b0, w0, b320, w320, b384, w384, results)

    out_json = _write_summary(args, b0, w0, b320, w320, b384, w384, results)
    log(f"wrote {out_json}")

    promote = [r for r in results if r.get("verdict") == "PROMOTE"]
    if promote:
        log("PROMOTE to --slice 1m (then 10m): " + ", ".join(r["name"] for r in promote))
    else:
        log("No PROMOTE at this slice — tighten opts or add new code levers")


if __name__ == "__main__":
    main()
