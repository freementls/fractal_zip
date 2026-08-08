#!/usr/bin/env bash
# DISABLED — Matt Mahoney zpaq v7.05 is no longer supported in this tree.
#
# Use upstream Matt Mahoney zpaq 7.15 (https://mattmahoney.net/dc/zpaq.html, zpaq715.zip + make)
# or zpaqfranz on PATH. Smoke tests showed identical fzc vs 7.15 with slower wall time on zpaq-heavy paths.
#
# Historical build recipe (GCC 11+ patch) lived here; patch file may remain at:
#   benchmarks/patches/zpaq705-zsiz-for-gcc11plus.patch
#
echo "build_zpaq705.sh: disabled — use zpaq 7.15 or zpaqfranz (see header comments)." >&2
exit 1
