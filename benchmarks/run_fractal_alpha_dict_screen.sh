#!/usr/bin/env bash
# Screen alpha-sorted entityfold dict vs banked freq-order @1MB (S2 only).
set -euo pipefail
ROOT=/srv/http/fractal_zip
BIN=$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b
MID=$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid
BASE_DIC=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic
ALPHA_DIC=$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9_alpha.dic
OUTDIR=$ROOT/benchmarks/.ladder_cache/fractal_dictlab
source "$ROOT/benchmarks/.ladder_cache/fractal_p5/composed_axis1_only.env"
export FXCM_RECIPE_MIXER_BITMASK=2
echo "base dict..."
"$BIN" -c "$BASE_DIC" "$MID" "$OUTDIR/alpha_screen_base.fx2"
echo "alpha dict..."
"$BIN" -c "$ALPHA_DIC" "$MID" "$OUTDIR/alpha_screen_alpha.fx2"
python3 - <<'PY'
import os
od="/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab"
b=os.path.getsize(f"{od}/alpha_screen_base.fx2")
a=os.path.getsize(f"{od}/alpha_screen_alpha.fx2")
print(f"S2 base={b} alpha={a} delta={a-b}")
open(f"{od}/alpha_dict_screen.json","w").write(
  __import__("json").dumps({"s2_base":b,"s2_alpha":a,"delta":a-b},indent=2)+"\n")
PY
