#!/usr/bin/env python3
"""Confirm the 'helps weak, hurts strong' gradient on REAL compressors.

For baseline vs case_split vs abjad_split channels, compress each channel with a
ladder of compressors (weak -> strong) and sum channel sizes. Roundtrip-safe
(channels are bijective). Shows where, if anywhere, symbology transforms pay off.
"""
import os, subprocess, tempfile, argparse

VOWELS = set(b"aeiouAEIOU")

def split_abjad(data):
    skel=bytearray(); vow=bytearray(); mask=bytearray()
    for b in data:
        if b in VOWELS: vow.append(b); mask.append(1)
        else: skel.append(b); mask.append(0)
    return [bytes(skel),bytes(vow),bytes(mask)]

def split_case(data):
    low=bytearray(); mask=bytearray()
    for b in data:
        if 65<=b<=90: low.append(b+32); mask.append(1)
        else: low.append(b); mask.append(0)
    return [bytes(low),bytes(mask)]

def extract_text(blob, limit):
    out=bytearray(); i=0; needle=b"<text"
    while len(out)<limit:
        j=blob.find(needle,i)
        if j<0: break
        gt=blob.find(b">",j)
        if gt<0: break
        end=blob.find(b"</text>",gt)
        if end<0: break
        out+=blob[gt+1:end]; out+=b"\n"; i=end+7
    return bytes(out[:limit])

def csize(data, tool):
    """Return compressed size in bytes for one channel with given tool."""
    with tempfile.NamedTemporaryFile(delete=False) as f:
        f.write(data); path=f.name
    try:
        if tool=="bzip2":
            out=subprocess.run(["bzip2","-9","-c",path],capture_output=True).stdout
            return len(out)
        if tool=="xz":
            out=subprocess.run(["xz","-9","-e","-c",path],capture_output=True).stdout
            return len(out)
        if tool=="zstd":
            out=subprocess.run(["zstd","-19","--ultra","-c",path],capture_output=True).stdout
            return len(out)
        if tool=="ppmd":
            # 7z PPMd (strong text model). Write to archive, read size.
            arc=path+".7z"
            try: os.unlink(arc)
            except FileNotFoundError: pass
            subprocess.run(["7z","a","-m0=PPMd:o16:mem256m","-bso0","-bsp0",arc,path],
                           capture_output=True)
            sz=os.path.getsize(arc) if os.path.exists(arc) else 0
            try: os.unlink(arc)
            except FileNotFoundError: pass
            return sz
    finally:
        try: os.unlink(path)
        except FileNotFoundError: pass
    return 0

def main():
    ap=argparse.ArgumentParser()
    ap.add_argument("--src",default="enwik8")
    ap.add_argument("--bytes",type=int,default=500000)
    args=ap.parse_args()
    with open(args.src,"rb") as f:
        blob=f.read(args.bytes*40)
    data=extract_text(blob,args.bytes)
    n=len(data)
    print(f"# realcomp source={args.src} text bytes={n}")
    decomps={
        "baseline":[data],
        "case_split":split_case(data),
        "abjad_split":split_abjad(data),
    }
    tools=["bzip2","zstd","xz","ppmd"]
    print(f"\n{'tool':>8} | " + " | ".join(f"{name:>22}" for name in decomps))
    base={}
    for tool in tools:
        base[tool]=sum(csize(s,tool) for s in decomps["baseline"])
    for tool in tools:
        cells=[]
        for name,streams in decomps.items():
            tot=sum(csize(s,tool) for s in streams)
            bpc=8*tot/n
            d=8*(tot-base[tool])/n
            cells.append(f"{tot}B {bpc:.3f}bpc({d:+.3f})")
        print(f"{tool:>8} | " + " | ".join(f"{c:>22}" for c in cells))
    print("\n# weaker compressors (bzip2/zstd) may gain from splits; strong text models (ppmd/xz) should not")

if __name__=="__main__":
    main()
