#!/usr/bin/env python3
"""Replicates Dictionary::Encode (dictionary.cpp) to compare dict variants on
a slice without running cmix. Reports transformed size and xz -6 size as an
entropy proxy. Byte-remap after encode is a bijection -> irrelevant for size.
"""
import re
import subprocess
import sys

kCapitalized, kUppercase, kEndUpper, kEscape = 0x40, 0x07, 0x06, 0x0C

def load_dict(path):
    entries, seen = [], set()
    with open(path, "rb") as f:
        for tok in re.split(rb"[^a-z]+", f.read()):
            if tok and tok not in seen:
                seen.add(tok)
                entries.append(tok)
    byte_map = {}
    longest = 0
    kB1, kB2, kB3 = 80, 80 + 3840, 80 + 3840 + 40960
    for i, w in enumerate(entries):
        longest = max(longest, len(w))
        if i < kB1:
            code = bytes([0x80 + i])
        elif i < kB2:
            code = bytes([0xD0 + (i - kB1) // 80, 0x80 + (i - kB1) % 80])
        elif i < kB3:
            j = i - kB2
            code = bytes([0xF0 + (j // 80) // 32, 0xD0 + (j // 80) % 32, 0x80 + j % 80])
        else:
            continue
        byte_map[w] = code
    return byte_map, longest

def encode(data, byte_map, longest):
    out = bytearray()
    def enc_byte(c):
        if c in (kEndUpper, kEscape, kUppercase, kCapitalized) or c >= 0x80:
            out.append(kEscape)
        out.append(c)
    def enc_substr(word):
        if len(word) <= 7:
            return False
        size = min(len(word) - 1, longest)
        suffix = word[len(word) - size:]
        while len(suffix) >= 7:
            code = byte_map.get(suffix)
            if code:
                out.extend(word[: len(word) - len(suffix)])
                out.extend(code)
                return True
            suffix = suffix[1:]
        prefix = word[:size]
        while len(prefix) >= 7:
            code = byte_map.get(prefix)
            if code:
                out.extend(code)
                out.extend(word[len(prefix):])
                return True
            prefix = prefix[:-1]
        return False
    def enc_word(word, num_upper, next_lower):
        if num_upper > 1:
            out.append(kUppercase)
        elif num_upper == 1:
            out.append(kCapitalized)
        code = byte_map.get(bytes(word))
        if code:
            out.extend(code)
        elif not enc_substr(bytes(word)):
            out.extend(word)
        if num_upper > 1 and next_lower:
            out.append(kEndUpper)

    word = bytearray()
    num_upper = num_lower = 0
    n = len(data)
    for pos in range(n):
        c = data[pos]
        advance = False
        if len(word) > longest:
            advance = True
        elif 0x61 <= c <= 0x7A:
            if num_upper > 1:
                advance = True
            else:
                num_lower += 1
                word.append(c)
        elif 0x41 <= c <= 0x5A:
            if num_lower > 0:
                advance = True
            else:
                num_upper += 1
                word.append(c + 32)
        else:
            advance = True
        if pos == n - 1 and not advance:
            enc_word(word, num_upper, False)
        if advance:
            if not word:
                enc_byte(c)
            else:
                next_lower = 0x61 <= c <= 0x7A
                enc_word(word, num_upper, next_lower)
                num_lower = num_upper = 0
                word = bytearray()
                if next_lower:
                    num_lower = 1
                    word.append(c)
                elif 0x41 <= c <= 0x5A:
                    num_upper = 1
                    word.append(c + 32)
                else:
                    enc_byte(c)
                if pos == n - 1 and word:
                    enc_word(word, num_upper, False)
    return bytes(out)

def xz_size(blob):
    p = subprocess.run(["xz", "-6", "-T", "4", "-c"], input=blob,
                       stdout=subprocess.PIPE, check=True)
    return len(p.stdout)

slice_path = sys.argv[1]
data = open(slice_path, "rb").read()
print(f"slice: {slice_path} ({len(data):,} B)")
base = None
for name, path in [
    ("english.dic", "/srv/http/fractal_zip/tools/hutter/fx2-cmix/dictionary/english.dic"),
    ("append365_e9", "/srv/http/fractal_zip/benchmarks/.ladder_cache/dicts/english_append365_e9.dic"),
    ("replace_low_e9", "/srv/http/fractal_zip/benchmarks/.ladder_cache/dicts/english_replace_low_e9.dic"),
]:
    bm, lg = load_dict(path)
    t = encode(data, bm, lg)
    xs = xz_size(t)
    d_t = "" if base is None else f"  Δtransform={len(t)-base[0]:+,}"
    d_x = "" if base is None else f"  Δxz={xs-base[1]:+,}"
    print(f"{name:16s} transform={len(t):,}  xz6={xs:,}{d_t}{d_x}")
    if base is None:
        base = (len(t), xs)
