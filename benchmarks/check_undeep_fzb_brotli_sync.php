#!/usr/bin/env php
<?php
/**
 * Fail fast if fractal_zip.php and fractal_zip-undeep-unwrap.php drift on FZB* outer Brotli + path-order logic.
 *
 *   php benchmarks/check_undeep_fzb_brotli_sync.php
 *   php benchmarks/check_undeep_fzb_brotli_sync.php --json   # one line: {"ok":true,"marker_count":N} or {"ok":false,...}
 * Invoked automatically when CHECK_UNDEEP_SYNC=1 or FRACTAL_ZIP_BENCH_CHECK_UNDEEP_SYNC=1 (see run_benchmarks.php),
 * or the sole flag: php benchmarks/run_benchmarks.php --check-undeep-sync. The low-priority shell accepts the env
 * names and unsets them after a successful run so the PHP child does not re-run the check.
 *
 * Markers after path-order / adaptive Brotli rows also pin native folder wire + zip_folder pipeline parity between the two entrypoints.
 *
 * php-smokes CI: invoked from tests/run_php_smokes.sh phase 2 via `php benchmarks/run_benchmarks.php --check-undeep-sync`.
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$json = in_array('--json', $argv, true);
$root = dirname(__DIR__);
$main = $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
$und = $root . DIRECTORY_SEPARATOR . 'fractal_zip-undeep-unwrap.php';
foreach (array('fractal_zip.php' => $main, 'fractal_zip-undeep-unwrap.php' => $und) as $label => $p) {
	if (!is_file($p)) {
		if ($json) {
			$js = bench_json_encode_try(
				array('ok' => false, 'error' => 'missing_file', 'file' => $label, 'path' => $p),
				false
			);
			if ($js === null) {
				fwrite(STDERR, '[bench] json_encode failed (check_undeep): ' . json_last_error_msg() . "\n");
				exit(2);
			}
			echo $js . "\n";
		} else {
			fwrite(STDERR, 'check_undeep_fzb_brotli_sync: missing ' . $p . "\n");
		}
		exit(1);
	}
}
$mainH = (string) file_get_contents($main);
$undH = (string) file_get_contents($und);
/** @var array<string, string> id => source substring expected in both files */
$needles = array(
	'path_order_proxy_fn' => 'function literal_bundle_path_order_brotli_q11_proxy_len(',
	'path_order_sweep_max_cand' => 'public static function literal_bundle_path_order_lgwin_sweep_max_candidates()',
	'proxy_foreach_lgwin' => 'foreach(array(20, 22, 24) as $xLw)',
	// adaptive: unique trial-append inside Q11 lgwin sweep (foreach syntax may be array() or [] across edits).
	'adaptive_foreach_lgwin' => '$lgwinTrials[] = array(\'q\' => $lgwinQual, \'w\' => $xLw);',
	'path_order_sweep_env' => 'FRACTAL_ZIP_PATH_ORDER_LGWIN_SWEEP_MAX_CAND',
	'fzb4_path_order_max_n' => '$maxN = fractal_zip::literal_bundle_fzb4_path_order_max_members();',
	'fzb4_path_order_guard' => 'if($n < 2 || $n > $maxN) {',
	'path_order_scorer_call' => '$this->literal_bundle_path_order_brotli_q11_proxy_len($brotliExe, $toScore, $uniqN, $bestScore);',
	'adaptive_fzb_nontext_lgwin_assign' => '$fzbNonTextLgwinExtra = $bundleInnerFzws && !$outerTextlike && $innerLen >= 64 && $innerLen <= 524288;',
	'adaptive_dense_lgwin_assign' => '$denseLgwinSweep = ($jpegEmbeddedBrotliLgwinSweep || $fzbNonTextLgwinExtra);',
	'adaptive_dense_lgwin_foreach' => 'foreach(($denseLgwinSweep ? array(17, 18, 19, 20, 21, 22, 23, 24) : array(20, 22, 24)) as $xLw)',
	'adaptive_textlike_lgwin_if' => 'if(($outerTextlike || $ultraSingleTxtRawSweep || $jpegEmbeddedBrotliLgwinSweep || $fzbNonTextLgwinExtra) && !$speedMode && !fractal_zip::disable_brotli_q11_textlike_lgwin_extra_sweep()',
	// Native folder wire + zip_folder timing / rollup (must stay aligned across fractal_zip.php and undeep).
	'folder_wire_fn_sig' => 'function folder_wire_after_literal_outer_try_native_folder_archives($rootDir, $fzcBody, array $rawFilesByPath, string $pipelineSegmentPrefix): array {',
	'native_wire_early_out' => 'if(fractal_zip::folder_native_wire_compare_fully_disabled()) {',
	'folder_bundle_scan_reset_fn' => 'function reset_folder_bundle_structure_scan_state() {',
	'rollup_zip_folder_wall_timer_begin' => 'fractal_zip_encode_pipeline::pipeline_wall_timer_zip_folder_begin();',
	'rollup_native_freearc_segment' => 'fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment($pipelineSegmentPrefix . \'_maybe_freearc_native_compare\');',
	'rollup_native_zpaq_segment' => 'fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment($pipelineSegmentPrefix . \'_maybe_zpaq_native_compare\');',
	'trace_opening_container' => 'fractal_zip::html_trace_print(\'Opening fractal zip container: \' . $filename . \'<br>\');',
	'trace_extract_file_link' => 'fractal_zip::html_trace_print(\'<a href="do.php?action=extract_file&path=\' . fs::query_encode($filename) . \'&file_to_extract=\' . $index . \'">Extract: \' . $index . \'</a><br>\');',
	'trace_time_taken_extracting_files' => 'fractal_zip::html_trace_print(\'Time taken extracting files from fractal_zip container: \' . $micro_time_taken . \' seconds.<br>\');',
	'trace_time_taken_extracting_single' => 'fractal_zip::html_trace_print(\'Time taken extracting: \' . $micro_time_taken . \' seconds.<br>\');',
	'trace_file_created' => 'fractal_zip::html_trace_print($filename . \' was created.<br>\');',
	'trace_zipping_entry' => 'fractal_zip::html_trace_print(\'zipping: \' . $entry_filename . \'<br>\');',
	'trace_auto_tuned_compression_summary' => 'fractal_zip::html_trace_print(\'Auto-tuned compression: segment=\' . $this->segment_length . \', substring_top_k=\' . $kStr . \', improvement=\' . $this->improvement_factor_threshold . \', gate_mult=\' . $gStr . \', best_trial .fz=\' . $best_size . \' B<br>\');',
	// Native folder decode before adaptive_decompress (open_container parity).
	'freearc_try_extract_fn' => 'function freearc_try_extract_native_folder_arc_to_directory($fullContents, $destRootDir, $debug) {',
	'open_container_try_native_freearc' => 'if($this->freearc_try_extract_native_folder_arc_to_directory($contents, $root_directory_of_container_file, $debug)) {',
	'zpaq_try_extract_fn' => 'function zpaq_try_extract_native_folder_archive_to_directory($fullContents, $destRootDir, $debug) {',
	'open_container_try_native_zpaq' => 'if($this->zpaq_try_extract_native_folder_archive_to_directory($contents, $root_directory_of_container_file, $debug)) {',
	'extract_freearc_native_blob_fn' => 'function extract_freearc_native_archive_blob_to_directory($arcBlob, $destRootDir, $debug) {',
	// adaptive_decompress: zpaq outer prefix + raw stream detection (must match across entrypoints).
	'adaptive_decompress_fn_sig' => 'function adaptive_decompress($string) {',
	'adaptive_decompress_outer_zpaq_magic' => '$zpaqMagic = fractal_zip::OUTER_ZPAQ_MAGIC;',
	'adaptive_decompress_raw_zpaq_stream_gate' => 'if($this->zpaq_native_archive_stream_magic_p($string)) {',
	'adaptive_decompress_outer_brotli_magic_assign' => '$bMagic = fractal_zip::OUTER_BROTLI_MAGIC;',
	'adaptive_decompress_outer_brotli_pipe_rest' => '$inner = $this->outer_brotli_decompress_pipe($brotliExe, $rest);',
	'adaptive_decompress_outer_zstd_pipe_fullblob' => '$inner = $this->outer_zstd_decompress_pipe($zstdExe, $string);',
	'adaptive_decompress_xz_outer_codec_temp_base' => '$tmpBase = fractal_zip::outer_codec_temp_dir();',
	'adaptive_decompress_seven_zip_magic_gate' => 'if(strlen($string) >= 2 && $string[0] === \'7\' && $string[1] === \'z\') {',
	'adaptive_decompress_freearc_magic_gate' => 'if(strlen($string) >= 4 && $string[0] === \'A\' && $string[1] === \'r\' && $string[2] === \'C\' && $string[3] === chr(1)) {',
	'adaptive_decompress_zlib_gzuncompress_fallback' => '$unz = gzuncompress($string);',
	'adaptive_decompress_zlib_gzinflate_fallback' => '$inf = gzinflate($string);',
	'fractal_substring_oversize_marker_return' => 'return array($fractal_string . $string, $this->left_fractal_zip_marker . strlen($fractal_string) . $this->mid_fractal_zip_marker . $strLen . $this->right_fractal_zip_marker);',
	'var_dump_full_unhandled_type_banner' => 'fractal_zip::html_trace_print(\'<span style="color: orange;">Unhandled data type in var_dump_full: \' . gettype($value) . \'</span><br>\');',
	// Marker context + adaptive markers (encode/decode parity with fractal_zip.php).
	'fractal_marker_ctx_publish_fn' => 'function fractal_marker_ctx_publish(): void {',
	'create_fractal_zip_markers_maybe_apply' => 'fractal_zip_maybe_apply_adaptive_markers($this, $dir);',
	'unzip_marker_ctx_publish' => '$this->fractal_marker_ctx_publish();',
	'fractal_replace_marker_rx' => '$rxTag = \'/\' . fractal_zip_marker_rx_substring_tag_simple() . \'/is\';',
	'escape_literal_publish_and_gt_env' => 'FRACTAL_ZIP_LITERAL_ESCAPE_GT',
	'escape_literal_mimic_close_comment' => '// So literals in the fractal blob cannot mimic the closing bracket of real substring/range ops (differentiation from FZ syntax vs payload).',
	'simple_substring_rx_main_assign' => '$rxSubstringMainSimple = \'/\' . fractal_zip_marker_rx_substring_main() . \'/is\';',
	'simple_substring_rx_main' => 'preg_match_all($rxSubstringMainSimple, $string, $matches, PREG_OFFSET_CAPTURE); // would a parser be faster? optimize later',
	'tuples_ctx_range_tuple' => 'str_replace($R, fractal_zip::$fractal_marker_ctx_range . $tuple . $R, $operation)',
	'fractal_substring_operator_ctx' => 'return fractal_zip::$fractal_marker_ctx_left . $start_offset . fractal_zip::$fractal_marker_ctx_mid . $end_offset . fractal_zip::$fractal_marker_ctx_right;',
	// Lazy equivalence + recursive_fractal_substring marker assembly (instance left/mid/right vs hardcoded <">).
	'lazy_zipped_string_instance_markers' => '$lazy_zipped_string = $this->left_fractal_zip_marker . $roff . $this->mid_fractal_zip_marker . $rlen . $this->right_fractal_zip_marker;',
	'rfs_badInterior_concat' => '$badInterior = $this->left_fractal_zip_marker . $this->mid_fractal_zip_marker;',
	'rfs_new_operation_instance_markers' => '$new_operation = $this->left_fractal_zip_marker . $new_offset . $this->mid_fractal_zip_marker . $new_length . $recursion_part . $tuple_part . $scale_part . $this->right_fractal_zip_marker;',
	// Literal round-trip (adaptive right marker + optional > escape).
	'unescape_literal_htmlspecialchars_decode_ent' => 'return htmlspecialchars_decode((string) $string, ENT_QUOTES | ENT_SUBSTITUTE);',
	'escape_literal_gt_conditional_single_gt_marker' => 'if(!$skipGt && $this->right_fractal_zip_marker === \'>\' && strlen($this->right_fractal_zip_marker) === 1) {',
	// recursive_fractal_substring resource gates (encode-side fractal search).
	'recursive_fractal_substring_fn_sig' => 'function recursive_fractal_substring($string, $fractal_string, $fractal_paths = array(), $path = array(), $recursion_counter = 0, $last_score = false) {',
	'recursive_fractal_effective_max_depth_assign' => '$this->recursive_fractal_effective_max_depth = max(2, min(32, $effective + $profileDepthAdjust));',
	'unzip_fractally_gate_left_marker' => 'if(strpos($string, $this->left_fractal_zip_marker) !== false) {',
	// Range shorthand unzip scan (left/mid/right + fractal_zipping_pass) before main unzip parser.
	'unzip_fractal_zipped_ranges_rx_assign' => '$rxFractalZippedRanges = \'/\' . fractal_zip::preg_escape($this->left_fractal_zip_marker) . $this->fractal_zipping_pass . fractal_zip::preg_escape($this->mid_fractal_zip_marker) . \'([0-9]+)\\-([0-9]+)\' . fractal_zip::preg_escape($this->mid_fractal_zip_marker) . $this->fractal_zipping_pass . fractal_zip::preg_escape($this->right_fractal_zip_marker) . \'/is\';',
	'unzip_fractal_zipped_ranges_preg_match_all' => 'preg_match_all($rxFractalZippedRanges, $string, $fractal_zipped_ranges);',
	'marker_rx_quoted_delims_unpack' => 'list($Lq, $Mq, $Rq) = fractal_zip_marker_rx_quoted_delimiters();',
	'recursive_substring_while_Lrx_digit_assign' => '$rxRecursiveSubstringLrxDigit = \'/\' . $Lq . \'[0-9]/is\';',
	'recursive_substring_early_digit_gate' => 'if(preg_match($rxRecursiveSubstringLrxDigit, $equivalence_string) !== 1) {',
	'recursive_substring_while_Lrx_digit' => 'while(preg_match($rxRecursiveSubstringLrxDigit, $equivalence_string) === 1) {',
	'recursive_substring_max_iters_cap' => '$maxSubstrIters = max(2000, min(200000, strlen($equivalence_string) * 16 + 4096));',
	'recursive_substring_rx_main_assign' => '$rxSubstringMainRecursiveReplace = \'/\' . fractal_zip_marker_rx_substring_main() . \'/is\';',
	'recursive_substring_rx_nested_strip_assign' => '$rxSub = \'/\' . $Lq . \'([0-9]+)\' . $Mq . \'([0-9]+)\' . $Rq . \'/is\';',
	'recursive_substring_rx_main_match' => 'if(preg_match($rxSubstringMainRecursiveReplace, $equivalence_string, $substring_operation_matches, PREG_OFFSET_CAPTURE) !== 1) {',
	'recursive_substring_preg_replace_recursion_counter' => '$substring = preg_replace($rxSub, fractal_zip::$fractal_marker_ctx_left . \'$1\' . fractal_zip::$fractal_marker_ctx_mid . \'$2\' . fractal_zip::$fractal_marker_ctx_mid . ($substring_recursion_counter - 1) . fractal_zip::$fractal_marker_ctx_right, $substring);',
	'rfs_replace_loop_rx_substring_main_assign' => '$rxSubstringMainReplace = \'/\' . fractal_zip_marker_rx_substring_main() . \'/is\';',
	'rfs_replace_loop_marker_rx_main' => 'if(preg_match($rxSubstringMainReplace, $replace, $matches, PREG_OFFSET_CAPTURE, $replace_offset)) { // would a parser be faster? optimize later',
	'rfs_rxRecDig_assign' => '$rxRecDig = \'/\' . $Lq . \'([0-9]+)\' . $Mq . \'([0-9]+)\' . $Mq . \'([0-9])/s\';',
	'emit_fractal_process_string_dumps_fn' => 'public static function emit_fractal_process_string_dumps(): bool {',
	'fractally_process_row_length_dump_guard' => 'if(fractal_zip::emit_fractal_process_string_dumps()) {',
	'fractally_skip_tile_scan_lop_rop' => '$lopLen > 0 && substr($equivalence_string, $position, $lopLen) === $lop',
	'fractally_process_fq_left_assign' => '$fqFractallyLeft = preg_quote($this->left_fractal_zip_marker, \'/\');',
	'fractally_process_fq_right_assign' => '$fqFractallyRight = preg_quote($this->right_fractal_zip_marker, \'/\');',
	'fractally_process_main_while_rx_assign' => '$rxFractallyMainPass = \'/\' . $fqFractallyLeft . \'[^r]/is\';',
	'fractally_gradient_rx_assign' => '$rxFractallyGradient = \'/\' . $fqFractallyLeft . \'g([^>]+)"([0-9]+)"([^\*>]+)\**([0-9]{0,})\' . $fqFractallyRight . \'/is\';',
	'fractally_row_length_rx_assign' => '$rxFractallyRowLength = \'/\' . $fqFractallyLeft . \'l([0-9]+)\' . $fqFractallyRight . \'/is\';',
	'fractally_skip_span_rx_assign' => '$rxFractallySkipSpan = \'/\' . $fqFractallyLeft . \'s[^<>]+\' . $fqFractallyRight . \'/is\';',
	'fractally_skip_uniform_rx_assign' => '$rxFractallySkipUniform = \'/\' . $fqFractallyLeft . \'s([0-9]+)\' . $fqFractallyRight . \'/is\';',
	'fractally_process_main_while_preg_match' => 'while(preg_match($rxFractallyMainPass, $equivalence_string) === 1) {',
	'fractally_process_pass_cap_max' => '$maxFractalProcessPasses = max(256, min(50000, strlen($equivalence_string) * 8 + 512));',
	'fractally_process_stagnation_break_eq' => 'if($equivalence_string === $eqBeforeThisPass) {',
	'fractally_row_length_op_left_right' => '$fqFractallyLeft . \'l([0-9]+)\' . $fqFractallyRight',
	'fractally_row_length_if_preg_match' => 'if(preg_match($rxFractallyRowLength, $equivalence_string, $row_length_operation_matches)) {',
	'fractally_skip_ops_preg_left_s_right' => '$fqFractallyLeft . \'s[^<>]+\' . $fqFractallyRight',
	'fractally_skip_span_preg_match_all' => 'preg_match_all($rxFractallySkipSpan, $equivalence_string, $operation_matches, PREG_OFFSET_CAPTURE); // only skip; substring is handled above?',
	'fractally_skip_uniform_preg_match' => 'preg_match($rxFractallySkipUniform, $equivalence_string, $skip_operation_matches); // would a parser be faster? optimize later',
	// Gradient ops in fractally_process_string (legacy " inside <g…>; outer markers from instance).
	'fractally_gradient_strpos_gate' => 'if(strpos($equivalence_string, $this->left_fractal_zip_marker . \'g\') !== false) {',
	'fractally_gradient_preg_match_all' => 'preg_match_all($rxFractallyGradient, $equivalence_string, $gradient_operation_matches, PREG_OFFSET_CAPTURE);',
	'fractally_replace_phase_left_r' => 'strpos($equivalence_string, $this->left_fractal_zip_marker . \'r\')',
	// Legacy quoted delimiter between search/replace segments in <r…> blocks (must stay aligned in fractally_process_string).
	'fractally_replace_rx_assign' => '$rxFractallyReplace = \'/\' . $fqFractallyLeft . \'r([^"]+)"([^>]+)>(.*?)<\\/r>/is\';',
	'fractally_replace_preg_match_all' => 'preg_match_all($rxFractallyReplace, $equivalence_string, $replace_operation_matches, PREG_OFFSET_CAPTURE); // do we need LOM since there is nesting structure?',
	'zip_tile_grid_skip_markers' => '$skipping_string .= $this->left_fractal_zip_marker . \'s\' . ($tile_width * $tile_height) . $this->right_fractal_zip_marker;',
	'zip_tile_grid_row_length_prefix' => '$zipped_string = $this->left_fractal_zip_marker . \'l\' . $row_length . $this->right_fractal_zip_marker . $zipped_string;',
	'fractally_skip_op_kind_after_left' => 'substr($operation_string, strlen($this->left_fractal_zip_marker), 1) === \'s\'',
	// 7z `-mmt` env parity (undeep native folder build/extract uses same shell fragment as main).
	'seven_zip_mmt_argv_from_env_sig' => 'public static function seven_zip_mmt_argv_from_env(): array {',
	'seven_zip_mmt_shell_fragment_sig' => 'public static function seven_zip_mmt_shell_fragment_for_exec(): string {',
	// Native folder 7z: whole-file raw archive extract branch (bench-identical .7z when decode-only prefix absent).
	'seven_zip_try_extract_raw_sig_branch' => '} elseif($n >= 6 && fractal_zip::payload_has_raw_seven_zip_signature($fullContents)) {',
	// Web-fs inspect: raw .fz whose entire head is a 7z signature (native-folder encode default when it wins).
	'native_folder_wire_outer_raw_7z_head_sig' => "\tif(strlen(\$head) >= 6 && fractal_zip::payload_has_raw_seven_zip_signature(\$head)) {",
);
$failures = array();
foreach ($needles as $id => $needle) {
	$inMain = strpos($mainH, $needle) !== false;
	$inUnd = strpos($undH, $needle) !== false;
	if (!$inMain) {
		if (!$json) {
			fwrite(STDERR, "fractal_zip.php: missing [" . $id . "]\n  " . $needle . "\n");
		}
		$failures[] = array('which' => 'fractal_zip.php', 'id' => $id, 'needle' => $needle);
	}
	if (!$inUnd) {
		if (!$json) {
			fwrite(STDERR, "fractal_zip-undeep-unwrap.php: missing [" . $id . "]\n  " . $needle . "\n");
		}
		$failures[] = array('which' => 'fractal_zip-undeep-unwrap.php', 'id' => $id, 'needle' => $needle);
	}
}
if ($failures !== array()) {
	if ($json) {
		$js = bench_json_encode_try(
			array(
				'ok' => false,
				'failure_count' => count($failures),
				'failures' => $failures,
			),
			false
		);
		if ($js === null) {
			fwrite(STDERR, '[bench] json_encode failed (check_undeep failures): ' . json_last_error_msg() . "\n");
			exit(2);
		}
		echo $js . "\n";
	} else {
		fwrite(
			STDERR,
			'check_undeep_fzb_brotli_sync: ' . (string) count($failures) . " failure(s); fix drift or update this script if intentional.\n"
		);
	}
	exit(1);
}
if ($json) {
	$js = bench_json_encode_try(
		array('ok' => true, 'marker_count' => count($needles)),
		false
	);
	if ($js === null) {
		fwrite(STDERR, '[bench] json_encode failed (check_undeep ok): ' . json_last_error_msg() . "\n");
		exit(2);
	}
	echo $js . "\n";
} else {
	fwrite(STDOUT, 'check_undeep_fzb_brotli_sync: ok (' . (string) count($needles) . " markers, both files)\n");
}
exit(0);
