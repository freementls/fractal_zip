#!/usr/bin/env bash
# Corpus phrase env sweep @384p vs mono_mi baseline.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
export FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0
unset FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_COUNT FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_LEN FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX_LEN FRACTAL_ZIP_ENWIK_CORPUS_PHRASES_FULL_BLOB
PHP=(php -d memory_limit=4096M)
OUT="$ROOT/benchmarks/.enwik8_corpus_tune_probe.json"
LOG="$ROOT/benchmarks/logs/corpus_tune_probe.log"
mkdir -p "$(dirname "$LOG")"

run_combo() {
	local name="$1"
	shift
	unset FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_COUNT FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX \
		FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_LEN FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX_LEN \
		FRACTAL_ZIP_ENWIK_CORPUS_PHRASES_FULL_BLOB
	export FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_COUNT=32
	export FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX=192
	export FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_LEN=10
	export FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX_LEN=96
	export FRACTAL_ZIP_ENWIK_CORPUS_PHRASES_FULL_BLOB=0
	while [[ $# -gt 0 ]]; do
		export "$1"
		shift
	done
	echo "=== combo=$name MIN_COUNT=$FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_COUNT MAX=$FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX MIN_LEN=$FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_LEN MAX_LEN=$FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX_LEN FULL_BLOB=$FRACTAL_ZIP_ENWIK_CORPUS_PHRASES_FULL_BLOB ==="
	"${PHP[@]}" benchmarks/bench_enwik8_wire_slice_probe.php --pages=384 \
		--cases=split_inner_fztx_mono_mi,split_inner_fztx_mono_mi_corpus
	local mono corpus
	mono=$(php -r '$j=json_decode(file_get_contents("benchmarks/.enwik8_wire_slice_probe.json"),true);foreach($j["rows"]??[] as $r){if($r["label"]==="split_inner_fztx_mono_mi"){echo (int)($r["wire_fzc"]??$r["fzc_bytes"]??0);exit;}}echo 0;')
	corpus=$(php -r '$j=json_decode(file_get_contents("benchmarks/.enwik8_wire_slice_probe.json"),true);foreach($j["rows"]??[] as $r){if($r["label"]==="split_inner_fztx_mono_mi_corpus"){echo (int)($r["wire_fzc"]??$r["fzc_bytes"]??0);exit;}}echo 0;')
	echo "RESULT $name mono=$mono corpus=$corpus delta=$((corpus - mono))"
	export OUT="$OUT" COMBO="$name" MONO="$mono" CORPUS="$corpus"
	php -r '
		$out = getenv("OUT");
		$combo = getenv("COMBO");
		$mono = (int)getenv("MONO");
		$corpus = (int)getenv("CORPUS");
		$doc = file_exists($out) ? json_decode(file_get_contents($out), true) : array("generated" => date("c"), "pages" => 384, "mono_mi_gate_ref" => 651650, "combos" => array());
		$doc["combos"][] = array(
			"name" => $combo,
			"env" => array(
				"MIN_COUNT" => (int)(getenv("FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_COUNT") ?: 32),
				"MAX" => (int)(getenv("FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX") ?: 192),
				"MIN_LEN" => (int)(getenv("FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_LEN") ?: 10),
				"MAX_LEN" => (int)(getenv("FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX_LEN") ?: 96),
				"FULL_BLOB" => (int)(getenv("FRACTAL_ZIP_ENWIK_CORPUS_PHRASES_FULL_BLOB") ?: 0),
			),
			"mono_mi" => $mono,
			"corpus" => $corpus,
			"delta" => $corpus - $mono,
		);
		file_put_contents($out, json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
	'
}

{
	echo "=== $(date -Iseconds) corpus tune @384p ==="
	rm -f "$OUT"
	run_combo defaults
	run_combo max512_min8 FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX=512 FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_COUNT=8
	run_combo long_phrases FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_LEN=8 FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX_LEN=160
	run_combo full_blob FRACTAL_ZIP_ENWIK_CORPUS_PHRASES_FULL_BLOB=1
	run_combo aggressive FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_COUNT=8 FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX=512 \
		FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_LEN=8 FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX_LEN=160 \
		FRACTAL_ZIP_ENWIK_CORPUS_PHRASES_FULL_BLOB=1
	echo "=== done → $OUT ==="
} 2>&1 | tee "$LOG"
