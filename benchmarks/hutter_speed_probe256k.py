#!/usr/bin/env python3
"""Relative 256KiB compress/decompress timing: match3m stock vs speed."""
import subprocess, time, pathlib, json, sys

wd = pathlib.Path('/srv/http/fractal_zip/tools/hutter/fx2-cmix/run_speed')
dictp = '/srv/http/fractal_zip/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic'
src = wd / 'probe256k.mid'
out_json = pathlib.Path('/srv/http/fractal_zip/benchmarks/.hutter_logs/speed_probe256k.json')
results = []

def run(name, binname):
    fx2 = wd / f'probe_{name}.fx2'
    out = wd / f'probe_{name}.out'
    for p in (fx2, out):
        if p.exists():
            p.unlink()
    binp = str(wd / binname)
    t0 = time.perf_counter()
    r = subprocess.run([binp, '-c', dictp, str(src), str(fx2)], cwd=str(wd),
                       stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    tc = time.perf_counter() - t0
    if r.returncode != 0:
        print(f'{name}: COMPRESS FAIL rc={r.returncode}', flush=True)
        sys.exit(1)
    t0 = time.perf_counter()
    r = subprocess.run([binp, '-d', dictp, str(fx2), str(out)], cwd=str(wd),
                       stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    td = time.perf_counter() - t0
    if r.returncode != 0:
        print(f'{name}: DECOMPRESS FAIL rc={r.returncode}', flush=True)
        sys.exit(1)
    rt = 'OK' if out.read_bytes() == src.read_bytes() else 'FAIL'
    b = fx2.stat().st_size
    row = {'name': name, 'compress_s': round(tc, 2), 'decompress_s': round(td, 2),
           'bytes': b, 'rt': rt}
    results.append(row)
    print(f'{name}: compress={tc:.1f}s decompress={td:.1f}s bytes={b} rt={rt}', flush=True)

for name, binname in [
    ('stock', 'cmix_match3m_entity'),
    ('speed', 'cmix_match3m_speed'),
    ('stock2', 'cmix_match3m_entity'),
    ('speed2', 'cmix_match3m_speed'),
]:
    run(name, binname)

stock = sum(r['compress_s'] for r in results if r['name'].startswith('stock')) / 2
speed = sum(r['compress_s'] for r in results if r['name'].startswith('speed')) / 2
summary = {
    'results': results,
    'avg_compress_stock_s': round(stock, 2),
    'avg_compress_speed_s': round(speed, 2),
    'speedup': round(stock / speed, 3) if speed else None,
    'note': 'run under match3m 100m contention; relative ratio is the signal',
}
out_json.write_text(json.dumps(summary, indent=2) + '\n')
print(json.dumps(summary, indent=2), flush=True)
