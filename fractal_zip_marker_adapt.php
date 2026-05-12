<?php
declare(strict_types=1);

/**
 * Optional folder-local tuning of fractal substring markers / limiters from a byte-frequency scan
 * of a layer-stripped sample (same deep unwrap as literal bundles when enabled).
 *
 * Enable: FRACTAL_ZIP_ADAPTIVE_MARKERS=1
 *
 * Design notes:
 * - Fractal **operator** tokens still use a leading `<` (`<g`, `<r`, …). Adaptive tuning therefore keeps
 *   **left = '<'** and **right = '>'** so the operator namespace stays valid; we may propose a different **mid**
 *   delimiter and reorder **common_limiters** / **common_limiter_pairs** by prevalence in the sample.
 * - `fractal_zip_marker_config_validate()` rejects **mid** values that collide with that namespace (e.g. `r`,`g`,`l`,`s`)
 *   or substring grammar (digits). Literals are still escaped (`escape_literal_for_storage`) so `<`/`&`/optional `>`
 *   in file bytes cannot be parsed as FZ operators.
 * - For **test_files107** (cp.html mirror), a non-default configuration is applied only when a full fractal-leg
 *   wire-size probe (same pipeline as zip_folder’s fractal branch through adaptive_compress) is **strictly**
 *   smaller than defaults — never a tie or regression on that tree.
 */

/** @return array{common_limiters: list<string>, common_limiter_pairs: list<array{0:string,1:string}>, left: string, mid: string, right: string, range_shorthand: string} */
function fractal_zip_marker_default_config(): array {
	return array(
		'common_limiters' => array(',', '.', ';', ':', '/', '\\', '|'),
		'common_limiter_pairs' => array(array('<', '>')),
		'left' => '<',
		'mid' => '"',
		'right' => '>',
		'range_shorthand' => '*',
	);
}

function fractal_zip_marker_adapt_env_enabled(): bool {
	return getenv('FRACTAL_ZIP_ADAPTIVE_MARKERS') === '1';
}

/**
 * @param array{common_limiters?: mixed, common_limiter_pairs?: mixed, left?: mixed, mid?: mixed, right?: mixed, range_shorthand?: mixed} $a
 * @param array{common_limiters?: mixed, common_limiter_pairs?: mixed, left?: mixed, mid?: mixed, right?: mixed, range_shorthand?: mixed} $b
 */
/** @param array{common_limiters: list<string>, common_limiter_pairs: list<array{0:string,1:string}>, left: string, mid: string, right: string, range_shorthand: string} $c */
function fractal_zip_marker_normalize_for_compare(array $c): array {
	$lim = $c['common_limiters'];
	sort($lim);
	$pairs = array();
	foreach ($c['common_limiter_pairs'] as $pr) {
		if (is_array($pr) && isset($pr[0], $pr[1])) {
			$pairs[] = array((string) $pr[0], (string) $pr[1]);
		}
	}
	return array(
		'left' => (string) $c['left'],
		'mid' => (string) $c['mid'],
		'right' => (string) $c['right'],
		'range_shorthand' => (string) $c['range_shorthand'],
		'common_limiters' => $lim,
		'common_limiter_pairs' => $pairs,
	);
}

function fractal_zip_marker_config_equals(array $a, array $b): bool {
	return json_encode(fractal_zip_marker_normalize_for_compare($a)) === json_encode(fractal_zip_marker_normalize_for_compare($b));
}

/** @param array{common_limiters: list<string>, common_limiter_pairs: list<array{0:string,1:string}>, left: string, mid: string, right: string, range_shorthand: string} $cfg */
function fractal_zip_marker_apply_config(fractal_zip $fz, array $cfg): void {
	$err = fractal_zip_marker_config_validate($cfg);
	if ($err !== null) {
		fractal_zip::warning_once('fractal_zip_marker_apply_config: ' . $err . ' — reverting to defaults.');
		$cfg = fractal_zip_marker_default_config();
	}
	$fz->left_fractal_zip_marker = (string) $cfg['left'];
	$fz->mid_fractal_zip_marker = (string) $cfg['mid'];
	$fz->right_fractal_zip_marker = (string) $cfg['right'];
	$fz->range_shorthand_marker = (string) $cfg['range_shorthand'];
	$fz->common_limiters = $cfg['common_limiters'];
	$fz->common_limiter_pairs = $cfg['common_limiter_pairs'];
}

/**
 * Concatenate peeled file bytes (relative path as in bundles) up to $maxTotalBytes.
 */
function fractal_zip_marker_collect_layer_stripped_sample(string $dir, int $maxTotalBytes): string {
	if ($maxTotalBytes <= 0) {
		return '';
	}
	$root = realpath($dir);
	if ($root === false) {
		return '';
	}
	$rootN = rtrim(str_replace('\\', '/', $root), '/') . '/';
	$prefixLen = strlen($rootN);
	fractal_zip_ensure_literal_pac_stack_loaded();
	$unwrap = fractal_zip::member_deep_unwrap_enabled();
	$buf = '';
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$pathN = str_replace('\\', '/', $fi->getPathname());
		if (strlen($pathN) < $prefixLen || substr($pathN, 0, $prefixLen) !== $rootN) {
			continue;
		}
		$rel = substr($pathN, $prefixLen);
		$raw = @file_get_contents($fi->getPathname());
		if ($raw === false || $raw === '') {
			continue;
		}
		$work = (string) $raw;
		if ($unwrap) {
			list($u) = fractal_zip_literal_deep_unwrap_with_layers($rel, $work);
			$work = (string) $u;
		}
		$need = $maxTotalBytes - strlen($buf);
		if ($need <= 0) {
			break;
		}
		if (strlen($work) > $need) {
			$buf .= substr($work, 0, $need);
			break;
		}
		$buf .= $work;
	}
	return $buf;
}

/**
 * preg_quote(left|mid|right) for /-delimited regexes; cached per marker context.
 *
 * @return array{0:string,1:string,2:string}
 */
function fractal_zip_marker_rx_quoted_delimiters(): array {
	static $cacheKey = null;
	/** @var array{0:string,1:string,2:string} */
	static $tri = array('', '', '');
	$L = fractal_zip::$fractal_marker_ctx_left;
	$M = fractal_zip::$fractal_marker_ctx_mid;
	$R = fractal_zip::$fractal_marker_ctx_right;
	$key = $L . "\0" . $M . "\0" . $R;
	if ($cacheKey === $key) {
		return $tri;
	}
	$cacheKey = $key;
	$tri = array(preg_quote($L, '/'), preg_quote($M, '/'), preg_quote($R, '/'));
	return $tri;
}

/**
 * Regex fragment for the core substring op `<off"len…>` with configurable mid (left/right stay angle brackets).
 */
function fractal_zip_marker_rx_substring_main(): string {
	static $cached = '';
	static $cacheKey = null;
	$L = fractal_zip::$fractal_marker_ctx_left;
	$M = fractal_zip::$fractal_marker_ctx_mid;
	$R = fractal_zip::$fractal_marker_ctx_right;
	$key = $L . "\0" . $M . "\0" . $R;
	if ($cacheKey === $key) {
		return $cached;
	}
	$cacheKey = $key;
	list($Lq, $Mq, $Rq) = fractal_zip_marker_rx_quoted_delimiters();
	$cached = $Lq . '([0-9]+)' . $Mq . '([0-9]+)' . $Mq . '*([0-9]*)\**([0-9]*)s*([0-9\.]*)' . $Rq;
	return $cached;
}

/**
 * Compact substring tag: L (digits) M (digits) [M optional_recursion] R — same delimiter roles as the main substring op,
 * used by fractal_replace() when rewriting embedded references (must track configurable mid, not a hard-coded quote).
 */
function fractal_zip_marker_rx_substring_tag_simple(): string {
	static $cached = '';
	static $cacheKey = null;
	$L = fractal_zip::$fractal_marker_ctx_left;
	$M = fractal_zip::$fractal_marker_ctx_mid;
	$R = fractal_zip::$fractal_marker_ctx_right;
	$key = $L . "\0" . $M . "\0" . $R;
	if ($cacheKey === $key) {
		return $cached;
	}
	$cacheKey = $key;
	list($Lq, $Mq, $Rq) = fractal_zip_marker_rx_quoted_delimiters();
	$cached = $Lq . '([0-9]+)' . $Mq . '([0-9]+)' . $Mq . '*([0-9]*)' . $Rq;
	return $cached;
}

/**
 * Reject marker configs that collide with the fixed &lt;op&gt; operator namespace or break substring regex assumptions.
 *
 * @param array{left?: mixed, mid?: mixed, right?: mixed, range_shorthand?: mixed} $cfg
 */
function fractal_zip_marker_config_validate(array $cfg): ?string {
	$left = isset($cfg['left']) ? (string) $cfg['left'] : '';
	$mid = isset($cfg['mid']) ? (string) $cfg['mid'] : '';
	$right = isset($cfg['right']) ? (string) $cfg['right'] : '';
	if ($left !== '<' || $right !== '>') {
		return 'left must be "<" and right ">" (operator / unzip namespace)';
	}
	if (strlen($mid) !== 1) {
		return 'mid must be exactly one byte';
	}
	$c0 = $mid[0];
	if ($c0 === '<' || $c0 === '>' || $c0 === '*' || $c0 === '&') {
		return 'mid collides with marker or shorthand delimiter';
	}
	if ($c0 >= '0' && $c0 <= '9') {
		return 'mid cannot be a digit (substring grammar)';
	}
	// Second byte of reserved &lt;op&gt; forms (fractally_process_string, unzip)
	if (strpos('rgls', $c0) !== false) {
		return 'mid collides with reserved <r/<g/<l/<s operator prefix';
	}
	return null;
}

/** @return array{common_limiters: list<string>, common_limiter_pairs: list<array{0:string,1:string}>, left: string, mid: string, right: string, range_shorthand: string} */
function fractal_zip_marker_propose_from_sample(string $sample, bool $multipass): array {
	$base = fractal_zip_marker_default_config();
	if ($sample === '') {
		return $base;
	}
	$hist = count_chars($sample, 1);
	if (!is_array($hist)) {
		return $base;
	}
	$score = static function (string $ch) use ($hist): int {
		$o = ord($ch);
		return $hist[$o] ?? 0;
	};
	$bestMid = $base['mid'];
	if ($multipass) {
		$pool = array('|', ':', ';', "'", '`', '~', '^');
		$bestScore = $score($bestMid);
		foreach ($pool as $c) {
			if ($c === '<' || $c === '>' || $c === '*' || $c === '-' || $c === '&') {
				continue;
			}
			if (strlen($c) !== 1) {
				continue;
			}
			$sc = $score($c);
			if ($sc > $bestScore) {
				$bestScore = $sc;
				$bestMid = $c;
			}
		}
		if ($bestMid === '') {
			$bestMid = $base['mid'];
		}
	}
	$limiterWhitelist = array(',', '.', ';', ':', '/', '\\', '|');
	usort($limiterWhitelist, static function (string $a, string $b) use ($score): int {
		return $score($b) <=> $score($a);
	});
	$pairsMeta = array(
		array(array('<', '>'), $score('<') + $score('>')),
		array(array('(', ')'), $score('(') + $score(')')),
		array(array('[', ']'), $score('[') + $score(']')),
		array(array('{', '}'), $score('{') + $score('}')),
	);
	usort($pairsMeta, static function (array $x, array $y): int {
		return ($y[1] <=> $x[1]);
	});
	$bestPair = $pairsMeta[0][0];
	return array(
		'common_limiters' => $limiterWhitelist,
		'common_limiter_pairs' => array($bestPair),
		'left' => '<',
		'mid' => (string) $bestMid,
		'right' => '>',
		'range_shorthand' => $base['range_shorthand'],
	);
}

/**
 * Merge `delimiter_association.recommendation` from fractal_zip_content_format_identify::identify() into a proposed
 * marker config. Type-linked pairs/limiters are **prepended** (evidence-gated by the identify module); remaining
 * entries keep frequency-based proposal. Caller still runs wire-size probes before adopting non-defaults.
 *
 * @param array{common_limiters: list<string>, common_limiter_pairs: list<array{0:string,1:string}>, left: string, mid: string, right: string, range_shorthand: string} $proposed
 * @param array{common_limiter_pairs?: list<array{0:string,1:string}>, common_limiters?: list<string>} $recommendation
 * @return array{common_limiters: list<string>, common_limiter_pairs: list<array{0:string,1:string}>, left: string, mid: string, right: string, range_shorthand: string}
 */
function fractal_zip_marker_merge_type_delimiter_evidence(array $proposed, array $recommendation): array {
	$out = $proposed;
	$pairs = isset($recommendation['common_limiter_pairs']) && is_array($recommendation['common_limiter_pairs'])
		? $recommendation['common_limiter_pairs'] : array();
	$lims = isset($recommendation['common_limiters']) && is_array($recommendation['common_limiters'])
		? $recommendation['common_limiters'] : array();
	if ($pairs !== array()) {
		$seen = array();
		$merged = array();
		foreach (array_merge($pairs, $out['common_limiter_pairs']) as $pr) {
			if (!is_array($pr) || !isset($pr[0], $pr[1])) {
				continue;
			}
			$key = (string) $pr[0] . "\0" . (string) $pr[1];
			if (isset($seen[$key])) {
				continue;
			}
			$seen[$key] = true;
			$merged[] = array((string) $pr[0], (string) $pr[1]);
		}
		$out['common_limiter_pairs'] = $merged;
	}
	if ($lims !== array()) {
		$seenC = array();
		$mergedL = array();
		foreach (array_merge($lims, $out['common_limiters']) as $c) {
			if (!is_string($c) || strlen($c) !== 1) {
				continue;
			}
			if (isset($seenC[$c])) {
				continue;
			}
			$seenC[$c] = true;
			$mergedL[] = $c;
		}
		$out['common_limiters'] = array_slice($mergedL, 0, 16);
	}
	return $out;
}

function fractal_zip_marker_copy_compress_tuning(fractal_zip $from, fractal_zip $to): void {
	$to->improvement_factor_threshold = $from->improvement_factor_threshold;
	$to->tuning_substring_top_k = $from->tuning_substring_top_k;
	$to->tuning_multipass_gate_mult = $from->tuning_multipass_gate_mult;
	$to->multipass_max_additional_passes = $from->multipass_max_additional_passes;
	$to->auto_tune_compression = false;
	$to->auto_segment_selection_enabled = false;
	$to->auto_multipass_selection_enabled = false;
}

/**
 * Bytes of the fractal branch only (encode_container_payload + peel trailer + adaptive_compress), for marker A/B.
 */
function fractal_zip_marker_probe_fractal_outer_len(fractal_zip $tuneHost, string $dir, array $cfg): int {
	fractal_zip::$fractal_marker_adaptive_probe_guard = true;
	try {
		$fz = new fractal_zip($tuneHost->segment_length, $tuneHost->multipass, false, null, false);
		fractal_zip_marker_copy_compress_tuning($tuneHost, $fz);
		fractal_zip_marker_apply_config($fz, $cfg);
		$fz->fractal_member_gzip_disk_restore = array();
		$fz->raster_canonical_hash_to_lazy_range = array();
		$fz->equivalences = array();
		$fz->lazy_equivalences = array();
		$fz->lazy_fractal_string = '';
		$fz->fractal_string = '';
		$fz->branch_counter = 0;
		$fz->strings_for_fractal_zip_markers = array();
		$fz->create_fractal_zip_markers($dir, false);
		$fz->recursive_zip_folder($dir, false);
		$fz->run_fractal_multipass_equivalence_passes(false);
		$arr = array();
		foreach ($fz->equivalences as $eq) {
			if (is_array($eq) && isset($eq[1], $eq[2])) {
				$arr[(string) $eq[1]] = (string) $eq[2];
			}
		}
		$pl = $fz->append_fractal_gzip_peel_restore_trailer($fz->encode_container_payload($arr, $fz->fractal_string), array());
		$comp = $fz->adaptive_compress($pl);
		return strlen($comp);
	} finally {
		fractal_zip::$fractal_marker_adaptive_probe_guard = false;
	}
}

function fractal_zip_maybe_apply_adaptive_markers(fractal_zip $host, string $dir): void {
	if (!fractal_zip_marker_adapt_env_enabled()) {
		return;
	}
	if (fractal_zip::$fractal_marker_adaptive_probe_guard) {
		return;
	}
	$rawMaxEnv = getenv('FRACTAL_ZIP_ADAPTIVE_MARKERS_PROBE_MAX_RAW');
	$rawMax = ($rawMaxEnv !== false && trim((string) $rawMaxEnv) !== '' && is_numeric($rawMaxEnv))
		? max(65536, min(64 * 1024 * 1024, (int) $rawMaxEnv))
		: (4 * 1024 * 1024);
	if ($rawMax > 0 && $host->folder_raw_total_bytes($dir) > $rawMax) {
		return;
	}
	$maxS = getenv('FRACTAL_ZIP_ADAPTIVE_MARKERS_SAMPLE_BYTES');
	$cap = ($maxS !== false && trim((string) $maxS) !== '' && is_numeric($maxS)) ? max(4096, min(8 * 1024 * 1024, (int) $maxS)) : 1024 * 1024;
	$sample = fractal_zip_marker_collect_layer_stripped_sample($dir, $cap);
	$proposed = fractal_zip_marker_propose_from_sample($sample, $host->multipass);
	$defaults = fractal_zip_marker_default_config();
	if (fractal_zip_marker_config_equals($proposed, $defaults)) {
		return;
	}
	$dBytes = fractal_zip_marker_probe_fractal_outer_len($host, $dir, $defaults);
	$pBytes = fractal_zip_marker_probe_fractal_outer_len($host, $dir, $proposed);
	if ($pBytes < $dBytes) {
		fractal_zip_marker_apply_config($host, $proposed);
	} else {
		fractal_zip_marker_apply_config($host, $defaults);
	}
}
