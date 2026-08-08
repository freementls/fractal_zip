#!/usr/bin/env bash
# Resume wire460 phase D @384p probes (skip completed outputs).
set -uo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"
export FRACTAL_ZIP_NO_CLI_OPCACHE_REEXEC=1
export FRACTAL_ZIP_BACKGROUND=1
export FRACTAL_ZIP_SUPPRESS_HTML=1

PHP_MEM="${FRACTAL_ZIP_PHP_MEMORY_LIMIT:-2048M}"
LOCK="$REPO/benchmarks/logs/.phda9_encode.lock"
STAMP="${RESUME_STAMP:-20260622_185410}"
LOG="$REPO/benchmarks/logs/wire460_resume_d_${STAMP}.log"
TARGET=460096
mkdir -p "$REPO/benchmarks/logs"

run_phda9() {
	echo ""
	echo "=== $(date -Is) $* ==="
	if command -v ionice >/dev/null 2>&1; then
		flock -w 86400 "$LOCK" ionice -c 3 nice -n 19 env "$@" \
			|| echo "WARN exit $? from: $*"
	else
		flock -w 86400 "$LOCK" nice -n 19 env "$@" \
			|| echo "WARN exit $? from: $*"
	fi
}

probe_done() {
	local out="$1"
	[[ -f "$out" ]] || return 1
	php -r '
$j = json_decode((string) file_get_contents($argv[1]), true);
foreach ($j["rows"] ?? [] as $r) {
  if (empty($r["error"]) && (int) ($r["fzc_bytes"] ?? 0) > 0) exit(0);
}
exit(1);
' "$out" 2>/dev/null
}

PROBE_CASES=(
	'split_inner_phda9_xml_single_stream_lstm_words4096_dict'
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict'
	'split_inner_phda9_xml_single_stream_lstm_words4096_wiki_lom_cfabb_inline'
	'split_inner_phda9_xml_single_stream_lstm_words4096_cfabb_plain_table'
)

exec > >(tee -a "$LOG") 2>&1
echo "wire460_resume_d start $(date -Is) stamp=${STAMP} target=${TARGET}"

for case in "${PROBE_CASES[@]}"; do
	out="$REPO/benchmarks/.enwik8_wire460_continue_${case}_${STAMP}.json"
	if probe_done "$out"; then
		echo "skip (done): $case"
		continue
	fi
	run_phda9 php -d "memory_limit=${PHP_MEM}" "$REPO/benchmarks/bench_enwik8_wire_slice_probe.php" \
		--pages=384 \
		--case="$case" \
		--verify-rt \
		--out-json="$out" \
		|| true
done

php -d "memory_limit=256M" "$REPO/benchmarks/bench_wire460_continue_summary.php" \
	--stamp="$STAMP" --target="$TARGET" 2>/dev/null || true

php "$REPO/benchmarks/wire460_status.php" || true
echo "wire460_resume_d done $(date -Is) → $LOG"
