#!/usr/bin/env bash
# Matches .github/workflows/php-smokes.yml: phase 1 php -l bundle; phase 2 undeep + folder census equiv; phase 2b
# run_benchmarks JSON verify_ok/--no-verify smokes; phases 3–8 substring regressions, synthetic zip repro, encode_pipeline,
# inner regression, smoke_random, outer-predict smokes, web-fs smokes. Path filters also watch benchmark shell helpers
# (e.g. search_bytes_wins_low_priority.sh, run_large_corpus_bytes_push.sh, bench_adaptive_markers_compare.sh). The workflow runs one extra step afterward:
# FZ_INNER_DISK_SIDECAR_ROUNDTRIP_ASSERT=1 php tests/case23_disk_sidecar_roundtrip_smoke.php
# No compressor apt deps.
# From repo root: bash tests/run_php_smokes.sh
#
# Exit 0 = all steps passed. Non-zero = missing SMOKE_PHP path, php -l failure, or any subsequent php … step failed.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

# Extend when this script gains a new PHP entrypoint or run_benchmarks.php adds early require_once targets.
# Phase 1: php -l on this array (includes benchmarks/report_bytes_wins.php — JSON summary / --compress-time-audit).
# Pair: fractal_zip.php + fractal_zip-undeep-unwrap.php (undeep); fractal_zip_outer_predict.php in php-smokes paths.
# Individual entrypoints document their own phase (2–7) in file headers where helpful.
SMOKE_PHP=(
	fractal_zip.php
	fractal_zip_marker_adapt.php
	fractal_zip_cli.php
	fractal_zip-undeep-unwrap.php
	fractal_zip_inner.php
	fractal_zip_inner_algorithms.php
	fractal_zip_outer_predict.php
	fractal_zip_encode_pipeline.php
	fractal_zip_content_format_identify.php
	fractal_zip_content_format_policy.php
	fractal_zip_flac_pac.php
	fractal_zip_enwik.php
	fractal_zip_paq.php
	fractal_zip_web_ref.php
	fractal_zip_cli_opcache_bootstrap.php
	perf_test.php
	tools/flac_semantic_equal.php
	benchmarks/bench_corpus_size.php
	benchmarks/bench_corpus_descriptions.php
	benchmarks/bench_default_corpus_list.php
	benchmarks/bench_folder_census.php
	benchmarks/bench_json_helpers.php
	benchmarks/build_test_files72_sample.php
	benchmarks/build_test_files74_75_76.php
	benchmarks/build_test_files133_silesia12.php
	benchmarks/silesia_sum_fzc_from_bench_json.php
	benchmarks/image_semantic_repack_to_dir.php
	benchmarks/pdf_literal_pac_empirical.php
	benchmarks/sample_large_corpus.php
	benchmarks/pick_random_perf_corpus_set.php
	benchmarks/sample_test_files54.php
	benchmarks/verify_fzc_bytes_winner_each_corpus.php
	benchmarks/xhprof_prepend.php
	benchmarks/xhprof_report_edges.php
	benchmarks/smoke_folder_bundle_census_equiv.php
	benchmarks/run_benchmarks.php
	benchmarks/report_bytes_wins.php
	benchmarks/report_sole_wins.php
	benchmarks/build_sole_win_universe.php
	benchmarks/guard_sole_wins_lifestyle.php
	benchmarks/guard_peel_admission.php
	benchmarks/guard_enwik8_strict.php
	benchmarks/smoke_win_on_all_peels.php
	fractal_zip_rpm_7z_peel.php
	fractal_zip_chunk_container_peel.php
	fractal_zip_classic_peel.php
	fractal_zip_ole_cfb.php
	benchmarks/sweep_segment_test_files29_freearc.php

	benchmarks/raw_tier_unwrap_ab.php
	benchmarks/sha1_tree_diff.php
	benchmarks/repro_folder_zip_verify.php
	benchmarks/smoke_repro_folder_zip_roundtrip.php
	benchmarks/smoke_enwik_path_order.php
	benchmarks/smoke_enwik8_corpus_parse.php
	benchmarks/compare_enwik8_world_record.php
	benchmarks/smoke_world_record_env.php
	benchmarks/smoke_logical_zip_folder.php
	benchmarks/smoke_fzhr_restore_silesia.php
	benchmarks/smoke_test_files78_equiv_133.php
	benchmarks/inspect_fzhm_extract.php
	fractal_zip_folder_logical_bundle.php
	fractal_zip_folder_per_member_best.php
	benchmarks/check_undeep_fzb_brotli_sync.php
	benchmarks/smoke_hostile_container_inputs.php
	benchmarks/smoke_inspect_container_web_fs.php
	benchmarks/fz_fzlb_decode_roundtrip_smoke.php
	benchmarks/fz_fz7_compact_folder_roundtrip_smoke.php
	benchmarks/fz_fz7_undeep_folder_roundtrip_smoke.php
	benchmarks/smoke_fzc_web_download_range.php
	examples/fractal_zip_web_fs_bridge.php
	examples/fzc_compress.php
	examples/fzc_extract.php
	examples/fzc_web_shared.php
	examples/fz_server_report.php
	examples/fzc_capability_probes.php
	examples/fzc_capability_report.php
	examples/fzc_capability_compare.php
	examples/fzc_capability_roundtrip_worker.php
	examples/fzc_parity_hints.php
	benchmarks/smoke_fzc_capability_report.php
	benchmarks/smoke_fzc_web_extract_compat_gate.php
	benchmarks/smoke_parity_hint_strings.php
	benchmarks/smoke_cli_member_list_parity_hint.php
	benchmarks/smoke_parity_gate_library_only.php
	benchmarks/compare_inner_scheduler_wire.php
	benchmarks/compare_peeler_inner_wire.php
	benchmarks/all_substrings_count_regression.php
	benchmarks/smoke_recursive_substring_roundtrip.php
	benchmarks/predict_outer_heuristic.php
	benchmarks/predict_outer_layered_probe.php
	benchmarks/squash_benchmarks.php
	benchmarks/guard_sixcase_stable_perf.php
	benchmarks/guard_lifestyle_bytes_and_time.php
	benchmarks/guard_tiktok_probe_recommend.php
	benchmarks/tiktok_recommend_from_sweep.php
	benchmarks/tiktok_recommend_drift.php
	benchmarks/tiktok_wins_report.php
	benchmarks/smoke_tiktok_recommend_drift.php
	benchmarks/fzc_one_case.php
	benchmarks/zpaq_outer_test_files128_matrix.php
	benchmarks/analyze_squash_outer_probe_signals.php
	benchmarks/micro_jbig2_pac_report.php
	benchmarks/image_semantic_tournament.php
	benchmarks/flac_lane_matrix.php
	benchmarks/sweep_test_files58_sample.php
	benchmarks/include/fractal_inner_passes.inc.php
	benchmarks/smoke_inner_pass_regression.php
	benchmarks/smoke_random.php
	benchmarks/smoke_random_test_files30.php
	benchmarks/smoke_fractal_inner_recipes_140_150.php
	tests/case23_disk_sidecar_roundtrip_smoke.php
	tests/enwik_entry_sort_roundtrip_smoke.php
	tests/enwik_chunk_restore_smoke.php
	tests/encode_pipeline_smoke.php
	tests/fractal_inner_run_grammar_bridge_smoke.php
	tests/stability_harness.php
	tests/fuzz_roundtrip_smoke.php
	tests/safe_member_path_smoke.php
	tests/malicious_container_paths_smoke.php
	tests/golden_container_compat_smoke.php
	tests/malformed_container_smoke.php
	tests/outer_predict_gzip_skip_smoke.php
	tests/predict_outer_heuristic_smoke.php
	web/api/storage_stats.php
)

# --- 1. Syntax only (SMOKE_PHP must stay in sync with php-smokes workflow expectations). ---
echo "[smoke] php -l (${#SMOKE_PHP[@]} files)" >&2
for f in "${SMOKE_PHP[@]}"; do
	if [[ ! -f "$f" ]]; then
		echo "[smoke] missing file (SMOKE_PHP): $f" >&2
		exit 1
	fi
	if ! php -l "$f" >/dev/null 2>&1; then
		echo "[smoke] php -l FAILED: $f" >&2
		php -l "$f"
		exit 1
	fi
done
echo "[smoke] php -l ok (${#SMOKE_PHP[@]} files)" >&2

# --- 2. Undeep FZB/Brotli marker sync (fractal_zip vs undeep-unwrap). ---
php benchmarks/run_benchmarks.php --check-undeep-sync
php benchmarks/smoke_folder_bundle_census_equiv.php
php benchmarks/smoke_folder_census_gzip_fast_gate.php
echo "[smoke] enwik world-record preset env + entry sort" >&2
php benchmarks/smoke_world_record_env.php
php benchmarks/smoke_enwik_path_order.php
php tests/enwik_entry_sort_roundtrip_smoke.php
php tests/enwik_chunk_restore_smoke.php
php benchmarks/smoke_enwik8_corpus_parse.php
# --- 2a. Peel-first FZHM/FZHR (skip if corpora missing; silesia restore ~60s). ---
echo "[smoke] peel-first folder smokes" >&2
php benchmarks/smoke_logical_zip_folder.php
php benchmarks/smoke_test_files78_equiv_133.php --quick
php benchmarks/smoke_fzhr_restore_silesia.php
echo "[smoke] win-on-all peel infrastructure + lifestyle sole gate" >&2
php benchmarks/smoke_win_on_all_peels.php
php benchmarks/guard_sole_wins_lifestyle.php
php benchmarks/guard_enwik8_strict.php --vs=zpaq benchmarks/.enwik8_world_record.json
# --- 2b. Bench JSON: --no-verify leaves verify_ok / verify_mismatch_files null (aggregateRepeatedRows). ---
echo "[smoke] run_benchmarks --no-verify JSON verify fields" >&2
php benchmarks/run_benchmarks.php --only=test_files2 --no-verify --json --no-case-timeout 2>/dev/null | php -r '$j=json_decode(stream_get_contents(STDIN),true);$c=$j["cases"][0]??[];if(!array_key_exists("verify_ok",$c)||$c["verify_ok"]!==null){fwrite(STDERR,"expected verify_ok null\n");exit(1);}if(!array_key_exists("verify_mismatch_files",$c)||$c["verify_mismatch_files"]!==null){fwrite(STDERR,"expected verify_mismatch_files null\n");exit(1);}'
echo "[smoke] run_benchmarks default verify JSON" >&2
php benchmarks/run_benchmarks.php --only=test_files2 --json --no-case-timeout 2>/dev/null | php -r '$j=json_decode(stream_get_contents(STDIN),true);$c=$j["cases"][0]??[];if(($c["verify_ok"]??null)!==true){fwrite(STDERR,"expected verify_ok true\n");exit(1);}if(($c["verify_mismatch_files"]??-1)!==0){fwrite(STDERR,"expected verify_mismatch_files 0\n");exit(1);}'
# --- 3. all_substrings_count snapshot (case-30 smoke slice). ---
php benchmarks/all_substrings_count_regression.php
# --- 3b. Synthetic folder + nested .zip strict SHA1 repro (repro_folder_zip_verify). ---
echo "[smoke] benchmarks/smoke_repro_folder_zip_roundtrip.php" >&2
php benchmarks/smoke_repro_folder_zip_roundtrip.php
# --- 4. Recursive substring decode round-trip (committed generator corpora). ---
php benchmarks/smoke_recursive_substring_roundtrip.php
# --- 4b. Fractal-inner stress fixture recipe integrity (no compression hints). ---
php benchmarks/smoke_fractal_inner_recipes_140_150.php
# --- 4c. Encode pipeline merge helpers + outer literal tie-break (loads fractal_zip for codec order). ---
echo "[smoke] tests/encode_pipeline_smoke.php" >&2
php tests/encode_pipeline_smoke.php
php tests/smoke_content_identify_policy.php
php scripts/lint_extension_behavior_policy.php
# --- 5. Inner linear pass regression (quick fixtures). ---
echo "[smoke] benchmarks/smoke_inner_pass_regression.php (quick)" >&2
php benchmarks/smoke_inner_pass_regression.php
# --- 6. smoke_random append+replace-only (structural off; stdout discarded). ---
echo "[smoke] benchmarks/smoke_random.php --case=23 --max-passes=1 --quiet-inner" >&2
php benchmarks/smoke_random.php --case=23 --max-passes=1 --quiet-inner >/dev/null
# --- 6b. Production inner run-grammar bridge round-trip guard. ---
php tests/fractal_inner_run_grammar_bridge_smoke.php
# --- 6c. Frozen container-shape extraction compatibility. ---
php tests/golden_container_compat_smoke.php
# --- 6d. Malicious archive member path containment. ---
php tests/malicious_container_paths_smoke.php
# --- 6e. Production wire parity for default inner scheduler vs explicit old-order fallback. ---
php benchmarks/compare_inner_scheduler_wire.php --quick
# --- 7. Outer prediction helpers (gzip skip threshold + heuristic table). ---
php tests/outer_predict_gzip_skip_smoke.php
php tests/predict_outer_heuristic_smoke.php
# --- 8. Web FS selective decode + FZLB / compact FZ+7z folder smokes (skip if tools missing) + examples/fzc_web_shared download helpers (no HTTP server). ---
php benchmarks/smoke_hostile_container_inputs.php
php benchmarks/smoke_inspect_container_web_fs.php
php benchmarks/fz_fzlb_decode_roundtrip_smoke.php
php benchmarks/fz_fz7_compact_folder_roundtrip_smoke.php
php benchmarks/fz_fz7_undeep_folder_roundtrip_smoke.php
php benchmarks/smoke_fzc_web_download_range.php
php benchmarks/smoke_fzc_capability_report.php
php benchmarks/smoke_fzc_web_extract_compat_gate.php
php benchmarks/smoke_parity_hint_strings.php
php benchmarks/smoke_cli_member_list_parity_hint.php
php benchmarks/smoke_parity_gate_library_only.php
php benchmarks/smoke_tiktok_recommend_drift.php
php benchmarks/smoke_paq_tools.php
php tests/web_ref_roundtrip_smoke.php
php tests/web_ref_piece_probe_smoke.php
php tests/web_ref_corpus_piece_smoke.php
php tests/web_ref_whole_page_smoke.php
php tests/web_ref_raw_apply_smoke.php
php ../sht/tests/smoke_alphabet_v1.php
php ../sht/tests/smoke_register.php
php ../live_browser/tests/smoke_web_ref_mirror.php
echo "php smokes: all ok"
