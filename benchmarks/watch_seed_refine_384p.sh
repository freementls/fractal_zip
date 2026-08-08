#!/usr/bin/env bash
set -euo pipefail
repo="/srv/http/fractal_zip"
log="$repo/benchmarks/logs/dict_lstm_seed_refine_384p.log"
json="$repo/benchmarks/.enwik8_phda9_dict_lstm_seed_refine_384p.json"

while pgrep -f "bench_phda9_dict_lstm_seed_refine.php --pages=384" >/dev/null 2>&1; do
	sleep 120
done

echo "=== seed-refine finished $(date -Is) ===" | tee -a "$repo/benchmarks/logs/watch_seed_refine_384p.log"
tail -25 "$log" | tee -a "$repo/benchmarks/logs/watch_seed_refine_384p.log"

if [[ ! -f "$json" ]]; then
	echo "missing $json" | tee -a "$repo/benchmarks/logs/watch_seed_refine_384p.log"
	exit 1
fi

delta=$(php -r '$j=json_decode(file_get_contents($argv[1]),true); echo (int)($j["delta"]??0);' "$json")
echo "delta=$delta" | tee -a "$repo/benchmarks/logs/watch_seed_refine_384p.log"

if [[ "$delta" -lt 0 ]]; then
	echo "running wire probe on refined dict …" | tee -a "$repo/benchmarks/logs/watch_seed_refine_384p.log"
	dict=$(php -r '$j=json_decode(file_get_contents($argv[1]),true); echo $j["refined_dict"]??"";' "$json")
	cd "$repo"
	nice -n 19 env FRACTAL_ZIP_WIRE_PROBE_PAGES=384 "FRACTAL_ZIP_WIRE_PROBE_PHDA9_DICT=$dict" \
		php benchmarks/bench_enwik8_wire_slice_probe.php --pages=384 \
		--case=split_inner_phda9_xml_single_stream_lstm_mixed_dict \
		--out-json=benchmarks/.enwik8_wire_slice_probe_384p_seed_refine.json \
		2>&1 | tee -a "$repo/benchmarks/logs/watch_seed_refine_384p.log"
fi

echo "running outer sweep on cached inner (if present) …" | tee -a "$repo/benchmarks/logs/watch_seed_refine_384p.log"
inner="$repo/benchmarks/.enwik8_phda9_pre_outer_384p.bin"
if [[ ! -f "$inner" ]]; then
	echo "capturing pre-outer inner for outer sweep …" | tee -a "$repo/benchmarks/logs/watch_seed_refine_384p.log"
	nice -n 19 php "$repo/benchmarks/bench_enwik8_phda9_outer_sweep.php" --pages=384 --capture \
		2>&1 | tee -a "$repo/benchmarks/logs/watch_seed_refine_384p.log" || true
fi
if [[ -f "$inner" ]]; then
	nice -n 19 php "$repo/benchmarks/bench_enwik8_phda9_outer_sweep.php" --inner="$inner" \
		--out-json="$repo/benchmarks/.enwik8_phda9_outer_sweep_384p.json" \
		2>&1 | tee -a "$repo/benchmarks/logs/watch_seed_refine_384p.log"
else
	echo "no inner dump at $inner — run: php benchmarks/bench_enwik8_phda9_outer_sweep.php --pages=384 --capture" \
		| tee -a "$repo/benchmarks/logs/watch_seed_refine_384p.log"
fi
