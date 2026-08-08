#!/bin/bash
# Solo 384p roundtrip verify for paq8px shootout + ppm96 (no competing paq workers).
set -euo pipefail
cd "$(dirname "$0")/.."
export FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0
export FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT=1
export FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_MODELS=paq8px
export FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_STACKS=none
export FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_JOBS=4
export FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=900
export FRACTAL_ZIP_PAQ8PX_LEVEL=6
export FRACTAL_ZIP_PAQ_SWEEP=0
php -d memory_limit=4096M benchmarks/verify_enwik_slice_roundtrip.php \
	--pages=384 --text-inner-promotion \
	2>&1 | tee benchmarks/logs/verify_384p_paq8px_ppm96_solo.log
