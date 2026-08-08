#!/usr/bin/env php
<?php

declare(strict_types=1);

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once $repo . '/fractal_zip_phda9_dict.php';
require_once $repo . '/fractal_zip_phda9_dict_mine.php';
require_once $repo . '/fractal_zip_phda9_dict_wire.php';

/**
 * @return array{
 *   seed: string,
 *   recovered_rejects: string,
 *   out_json: string,
 *   out_md: string,
 *   pages_32: int,
 *   pages_64: int,
 *   max_trials: int
 * }
 */
function wire460_smallcase_parse_args(array $argv, string $repo): array
{
	$out = array(
		'seed' => $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt',
		'recovered_rejects' => $repo . '/benchmarks/.wire460_recovered_rejects.json',
		'out_json' => $repo . '/benchmarks/.wire460_smallcase_wins.json',
		'out_md' => $repo . '/benchmarks/.wire460_smallcase_wins.md',
		'pages_32' => 32,
		'pages_64' => 64,
		'max_trials' => 18,
	);
	foreach ($argv as $arg) {
		if (str_starts_with($arg, '--seed=')) {
			$out['seed'] = substr($arg, 7);
		} elseif (str_starts_with($arg, '--recovered-rejects=')) {
			$out['recovered_rejects'] = substr($arg, 20);
		} elseif (str_starts_with($arg, '--out-json=')) {
			$out['out_json'] = substr($arg, 11);
		} elseif (str_starts_with($arg, '--out-md=')) {
			$out['out_md'] = substr($arg, 9);
		} elseif (str_starts_with($arg, '--pages32=')) {
			$out['pages_32'] = max(16, (int) substr($arg, 10));
		} elseif (str_starts_with($arg, '--pages64=')) {
			$out['pages_64'] = max(32, (int) substr($arg, 10));
		} elseif (str_starts_with($arg, '--max-trials=')) {
			$out['max_trials'] = max(1, (int) substr($arg, 13));
		}
	}
	return $out;
}

function wire460_smallcase_candidate_key(string $op, string $token): string
{
	return $op . "\t" . $token;
}

/**
 * @return array{reject_keys: array<string, true>, rows: list<array<string, mixed>>}
 */
function wire460_smallcase_load_recovered_rejects(string $path): array
{
	$out = array('reject_keys' => array(), 'rows' => array());
	if (!is_file($path)) {
		return $out;
	}
	$raw = (string) file_get_contents($path);
	$data = json_decode($raw, true);
	if (!is_array($data)) {
		return $out;
	}
	$rows = $data['rejects'] ?? array();
	if (!is_array($rows)) {
		return $out;
	}
	foreach ($rows as $row) {
		if (!is_array($row)) {
			continue;
		}
		$op = (string) ($row['op'] ?? '');
		$token = (string) ($row['token'] ?? '');
		if (($op !== 'add' && $op !== 'remove') || $token === '') {
			continue;
		}
		$out['rows'][] = $row;
		$out['reject_keys'][wire460_smallcase_candidate_key($op, $token)] = true;
	}
	return $out;
}

/**
 * @param list<string> $paths
 * @return array{reject_keys: array<string, true>, rows: list<array<string, mixed>>}
 */
function wire460_smallcase_load_secondary_rejects(array $paths): array
{
	$out = array('reject_keys' => array(), 'rows' => array());
	foreach ($paths as $path) {
		if (!is_file($path)) {
			continue;
		}
		$raw = (string) file_get_contents($path);
		$data = json_decode($raw, true);
		if (!is_array($data)) {
			continue;
		}
		$trials = $data['trials'] ?? array();
		if (!is_array($trials)) {
			continue;
		}
		foreach ($trials as $row) {
			if (!is_array($row)) {
				continue;
			}
			$op = (string) ($row['op'] ?? '');
			$token = (string) ($row['token'] ?? '');
			$accepted = !empty($row['accepted']);
			if (($op !== 'add' && $op !== 'remove') || $token === '' || $accepted) {
				continue;
			}
			$key = wire460_smallcase_candidate_key($op, $token);
			$out['reject_keys'][$key] = true;
			$out['rows'][] = array(
				'op' => $op,
				'token' => $token,
				'source' => $path,
				'delta_wire' => (int) ($row['delta_wire'] ?? 0),
			);
		}
	}
	return $out;
}

/**
 * @return array{page_xml: string, page_count: int}
 */
function wire460_smallcase_page_xml(string $repo, int $pages): array
{
	$src = $repo . '/test_files109/enwik8';
	if (!is_file($src)) {
		$src = $repo . '/enwik8';
	}
	if (!is_file($src)) {
		throw new RuntimeException('missing enwik8 source');
	}
	$blob = (string) file_get_contents($src);
	$split = enwik_split_page_refs($blob);
	if ($split === null) {
		throw new RuntimeException('enwik split failed');
	}
	$n = min($pages, count($split['pages']));
	$chunk = array();
	for ($i = 0; $i < $n; $i++) {
		$chunk[] = array(
			'origIndex' => $i,
			'start' => (int) $split['pages'][$i]['start'],
			'len' => (int) $split['pages'][$i]['len'],
		);
	}
	$payloads = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob);
	return array(
		'page_xml' => (string) ($payloads['sorted_page_xml'] ?? ''),
		'page_count' => $n,
	);
}

/**
 * @return list<array{op: string, token: string, category: string, note: string, force: bool}>
 */
function wire460_smallcase_raw_candidates(): array
{
	$rows = array();
	$add = static function (string $op, string $token, string $category, string $note, bool $force = false) use (&$rows): void {
		$rows[] = array(
			'op' => $op,
			'token' => $token,
			'category' => $category,
			'note' => $note,
			'force' => $force,
		);
	};

	$add('remove', 'would', 'grammar', 'previously interesting 384p removal', true);
	$add('remove', 'will', 'grammar', 'modal frequency near would');
	$add('remove', 'when', 'grammar', 'high-frequency connective');
	$add('remove', 'where', 'grammar', 'high-frequency connective');
	$add('remove', 'while', 'grammar', 'high-frequency connective');
	$add('remove', 'after', 'grammar', 'time connective');
	$add('remove', 'before', 'grammar', 'time connective');
	$add('remove', 'during', 'grammar', 'time connective');
	$add('remove', 'between', 'grammar', 'relational connective');
	$add('remove', 'through', 'grammar', 'relational connective');
	$add('remove', 'without', 'grammar', 'relational connective');
	$add('remove', 'within', 'grammar', 'relational connective');
	$add('remove', 'there', 'grammar', 'high-frequency glue');
	$add('remove', 'into', 'grammar', 'high-frequency glue');

	$add('add', '<page>', 'xml', 'page skeleton token');
	$add('add', '</page>', 'xml', 'page skeleton token');
	$add('add', '<revision>', 'xml', 'revision skeleton token');
	$add('add', '</revision>', 'xml', 'revision skeleton token');
	$add('add', '<title>', 'xml', 'title skeleton token');
	$add('add', '</title>', 'xml', 'title skeleton token');
	$add('add', '<timestamp>', 'xml', 'timestamp skeleton token');
	$add('add', '</timestamp>', 'xml', 'timestamp skeleton token');
	$add('add', '<comment>', 'xml', 'comment skeleton token');
	$add('add', '</comment>', 'xml', 'comment skeleton token');
	$add('add', '<username>', 'xml', 'username skeleton token');
	$add('add', '</username>', 'xml', 'username skeleton token');
	$add('add', '<sha1>', 'xml', 'sha1 skeleton token');
	$add('add', '</sha1>', 'xml', 'sha1 skeleton token');
	$add('add', '<model>', 'xml', 'text format skeleton token');
	$add('add', '</model>', 'xml', 'text format skeleton token');
	$add('add', '<format>', 'xml', 'text format skeleton token');
	$add('add', '</format>', 'xml', 'text format skeleton token');
	$add('add', '</text>', 'xml', 'text close skeleton token');
	$add('add', '[[Category:', 'wiki', 'high-frequency wiki structure');
	$add('add', '<ref name=', 'xml', 'reference opener fragment');
	$add('add', '</ref>', 'xml', 'reference close token');
	return $rows;
}

/**
 * @param list<array{op: string, token: string, category: string, note: string, force: bool}> $raw
 * @param array<string, true> $seedSet
 * @param array<string, true> $primaryRejects
 * @param array<string, true> $secondaryRejects
 * @return array{
 *   selected: list<array<string, mixed>>,
 *   skipped: list<array<string, mixed>>
 * }
 */
function wire460_smallcase_filter_candidates(
	array $raw,
	array $seedSet,
	array $primaryRejects,
	array $secondaryRejects,
	string $corpus,
	int $maxTrials
): array {
	$scoreInput = array();
	foreach ($raw as $row) {
		$scoreInput[] = array(
			'token' => $row['token'],
			'kind' => $row['category'],
		);
	}
	$scored = fractal_zip_phda9_dict_score_candidates($corpus, $scoreInput);
	$scoreByToken = array();
	foreach ($scored as $row) {
		$scoreByToken[(string) ($row['token'] ?? '')] = array(
			'count' => (int) ($row['count'] ?? 0),
			'est_save' => (int) ($row['est_save'] ?? 0),
		);
	}

	$selected = array();
	$skipped = array();
	$seen = array();
	foreach ($raw as $row) {
		$op = (string) $row['op'];
		$token = (string) $row['token'];
		$key = wire460_smallcase_candidate_key($op, $token);
		$force = !empty($row['force']);
		if (isset($seen[$key])) {
			continue;
		}
		$seen[$key] = true;

		if (isset($primaryRejects[$key]) && !$force) {
			$skipped[] = array_merge($row, array('skip_reason' => 'recovered_384_reject'));
			continue;
		}
		if ($op === 'remove' && !isset($seedSet[$token])) {
			$skipped[] = array_merge($row, array('skip_reason' => 'remove_token_missing_from_seed'));
			continue;
		}
		if ($op === 'add' && isset($seedSet[$token])) {
			$skipped[] = array_merge($row, array('skip_reason' => 'add_token_already_in_seed'));
			continue;
		}
		if (isset($secondaryRejects[$key]) && !$force) {
			$skipped[] = array_merge($row, array('skip_reason' => 'prior_smallslice_reject'));
			continue;
		}

		$stats = $scoreByToken[$token] ?? array('count' => 0, 'est_save' => 0);
		if ((int) $stats['count'] <= 0) {
			$skipped[] = array_merge($row, array(
				'skip_reason' => 'zero_occurrence_on_32p_corpus',
				'count_32p' => 0,
				'est_save_32p' => 0,
			));
			continue;
		}
		$selected[] = array_merge($row, array(
			'count_32p' => (int) $stats['count'],
			'est_save_32p' => (int) $stats['est_save'],
		));
	}

	if (count($selected) > $maxTrials) {
		$extra = array_slice($selected, $maxTrials);
		$selected = array_slice($selected, 0, $maxTrials);
		foreach ($extra as $row) {
			$skipped[] = array_merge($row, array('skip_reason' => 'max_trials_cap'));
		}
	}

	return array('selected' => $selected, 'skipped' => $skipped);
}

/**
 * @param list<string> $seedWords
 * @param list<array<string, mixed>> $selected
 * @return array{
 *   baseline_wire: int,
 *   baseline_zip_seconds: float,
 *   baseline_rt_ok: bool,
 *   trials: list<array<string, mixed>>,
 *   winners32: list<array<string, mixed>>
 * }
 */
function wire460_smallcase_run_32p(
	string $repo,
	string $slicePath,
	string $runBaseDir,
	array $seedWords,
	string $seedPath,
	array $selected
): array {
	$baseline = fractal_zip_phda9_dict_wire_encode(
		$repo,
		$slicePath,
		$runBaseDir . '/baseline',
		$seedWords,
		true,
		$seedPath
	);
	if ($baseline === null) {
		throw new RuntimeException('32p baseline encode failed');
	}
	$baselineWire = (int) $baseline['wire_fzc'];
	$trials = array();
	$winners = array();

	foreach ($selected as $idx => $cand) {
		$op = (string) ($cand['op'] ?? '');
		$token = (string) ($cand['token'] ?? '');
		$trialWords = $seedWords;
		if ($op === 'remove') {
			$trialWords = array_values(array_filter($seedWords, static fn (string $w): bool => $w !== $token));
		} elseif ($op === 'add') {
			$trialWords = array_values(array_merge($seedWords, array($token)));
		}
		$enc = fractal_zip_phda9_dict_wire_encode(
			$repo,
			$slicePath,
			$runBaseDir . '/cand_' . sprintf('%03d', $idx + 1),
			$trialWords,
			false,
			$seedPath
		);
		$row = $cand;
		$row['id'] = sprintf('%s:%s', $op, $token);
		$row['trial_index'] = $idx + 1;
		$row['trial_32'] = array(
			'tested' => $enc !== null,
			'baseline_wire_fzc' => $baselineWire,
			'wire_fzc' => $enc !== null ? (int) $enc['wire_fzc'] : null,
			'delta_wire' => $enc !== null ? ((int) $enc['wire_fzc'] - $baselineWire) : null,
			'zip_seconds' => $enc !== null ? (float) ($enc['zip_seconds'] ?? 0.0) : null,
			'roundtrip_ok' => null,
		);
		$row['win_32'] = ($enc !== null && ((int) $enc['wire_fzc'] - $baselineWire) < 0);
		$row['trial_64'] = array(
			'attempted' => false,
			'baseline_wire_fzc' => null,
			'wire_fzc' => null,
			'delta_wire' => null,
			'zip_seconds' => null,
			'roundtrip_ok' => null,
			'surviving_win' => false,
		);
		$trials[] = $row;
		if (!empty($row['win_32'])) {
			$winners[] = $row;
		}
		$status = $row['win_32'] ? 'WIN32' : 'loss ';
		$delta = $row['trial_32']['delta_wire'];
		$wire = $row['trial_32']['wire_fzc'];
		printf(
			"32p %-5s %-7s %-22s wire=%8s Δ=%+5d count=%5d\n",
			$status,
			$op,
			substr($token, 0, 22),
			number_format((int) $wire),
			(int) $delta,
			(int) ($row['count_32p'] ?? 0)
		);
	}

	return array(
		'baseline_wire' => $baselineWire,
		'baseline_zip_seconds' => (float) ($baseline['zip_seconds'] ?? 0.0),
		'baseline_rt_ok' => !empty($baseline['roundtrip_ok']),
		'trials' => $trials,
		'winners32' => $winners,
	);
}

/**
 * @param list<string> $seedWords
 * @param list<array<string, mixed>> $rows
 * @return array{
 *   baseline_wire: int,
 *   baseline_zip_seconds: float,
 *   baseline_rt_ok: bool,
 *   rows: list<array<string, mixed>>
 * }
 */
function wire460_smallcase_run_64p_confirms(
	string $repo,
	string $slicePath,
	string $runBaseDir,
	array $seedWords,
	string $seedPath,
	array $rows
): array {
	$baseline = fractal_zip_phda9_dict_wire_encode(
		$repo,
		$slicePath,
		$runBaseDir . '/baseline',
		$seedWords,
		true,
		$seedPath
	);
	if ($baseline === null) {
		throw new RuntimeException('64p baseline encode failed');
	}
	$baselineWire = (int) $baseline['wire_fzc'];
	$out = $rows;
	foreach ($out as $idx => $row) {
		if (empty($row['win_32'])) {
			continue;
		}
		$op = (string) ($row['op'] ?? '');
		$token = (string) ($row['token'] ?? '');
		$trialWords = $seedWords;
		if ($op === 'remove') {
			$trialWords = array_values(array_filter($seedWords, static fn (string $w): bool => $w !== $token));
		} elseif ($op === 'add') {
			$trialWords = array_values(array_merge($seedWords, array($token)));
		}
		$enc = fractal_zip_phda9_dict_wire_encode(
			$repo,
			$slicePath,
			$runBaseDir . '/confirm_' . sprintf('%03d', $idx + 1),
			$trialWords,
			true,
			$seedPath
		);
		$delta = $enc !== null ? ((int) $enc['wire_fzc'] - $baselineWire) : null;
		$rtOk = ($enc !== null) ? !empty($enc['roundtrip_ok']) : false;
		$survivingWin = ($enc !== null && $delta !== null && $delta < 0 && $rtOk);
		$out[$idx]['trial_64'] = array(
			'attempted' => true,
			'baseline_wire_fzc' => $baselineWire,
			'wire_fzc' => $enc !== null ? (int) $enc['wire_fzc'] : null,
			'delta_wire' => $delta,
			'zip_seconds' => $enc !== null ? (float) ($enc['zip_seconds'] ?? 0.0) : null,
			'roundtrip_ok' => $enc !== null ? $rtOk : false,
			'surviving_win' => $survivingWin,
		);
		$status = $survivingWin ? 'WIN64' : 'fail ';
		printf(
			"64p %-5s %-7s %-22s wire=%8s Δ=%+5d RT=%s\n",
			$status,
			$op,
			substr($token, 0, 22),
			number_format((int) ($out[$idx]['trial_64']['wire_fzc'] ?? 0)),
			(int) ($delta ?? 0),
			$rtOk ? 'ok' : 'FAIL'
		);
	}
	return array(
		'baseline_wire' => $baselineWire,
		'baseline_zip_seconds' => (float) ($baseline['zip_seconds'] ?? 0.0),
		'baseline_rt_ok' => !empty($baseline['roundtrip_ok']),
		'rows' => $out,
	);
}

/**
 * @param list<array<string, mixed>> $trials
 * @return list<array<string, mixed>>
 */
function wire460_smallcase_surviving_wins(array $trials): array
{
	$out = array();
	foreach ($trials as $row) {
		if (!empty($row['trial_64']['surviving_win'])) {
			$out[] = $row;
		}
	}
	return $out;
}

/**
 * @param list<array<string, mixed>> $trials
 * @return list<array<string, mixed>>
 */
function wire460_smallcase_top_near_wins(array $trials, int $limit = 5): array
{
	$rows = array();
	foreach ($trials as $row) {
		$delta = $row['trial_32']['delta_wire'] ?? null;
		if (!is_int($delta)) {
			continue;
		}
		$rows[] = $row;
	}
	usort($rows, static function (array $a, array $b): int {
		$da = (int) ($a['trial_32']['delta_wire'] ?? PHP_INT_MAX);
		$db = (int) ($b['trial_32']['delta_wire'] ?? PHP_INT_MAX);
		$aa = abs($da);
		$ab = abs($db);
		if ($aa !== $ab) {
			return $aa <=> $ab;
		}
		return $da <=> $db;
	});
	return array_slice($rows, 0, max(1, $limit));
}

/**
 * @param list<array<string, mixed>> $surviving
 * @param list<array<string, mixed>> $near
 */
function wire460_smallcase_write_md(
	string $path,
	array $opts,
	array $selected,
	array $skipped,
	array $trials,
	array $surviving,
	array $near,
	int $baseline32,
	int $baseline64
): void {
	$lines = array();
	$lines[] = '# Wire460 Small-Case Wins';
	$lines[] = '';
	$lines[] = '- Generated: ' . date('c');
	$lines[] = '- 32p baseline wire_fzc: ' . number_format($baseline32);
	$lines[] = '- 64p baseline wire_fzc: ' . number_format($baseline64);
	$lines[] = '- Candidates selected: ' . count($selected) . ' (skipped: ' . count($skipped) . ')';
	$lines[] = '- Trial cap (`--max-trials`): ' . (int) $opts['max_trials'];
	$lines[] = '';
	$lines[] = '## 32p winners';
	$wins32 = array_values(array_filter($trials, static fn (array $r): bool => !empty($r['win_32'])));
	if ($wins32 === array()) {
		$lines[] = '- None';
	} else {
		foreach ($wins32 as $row) {
			$lines[] = sprintf(
				'- `%s %s`: Δ32=%+d (wire=%s)',
				(string) $row['op'],
				(string) $row['token'],
				(int) ($row['trial_32']['delta_wire'] ?? 0),
				number_format((int) ($row['trial_32']['wire_fzc'] ?? 0))
			);
		}
	}
	$lines[] = '';
	$lines[] = '## 64p confirmations';
	if ($surviving === array()) {
		$lines[] = '- No candidate survived 64p confirm with RT.';
	} else {
		foreach ($surviving as $row) {
			$lines[] = sprintf(
				'- `%s %s`: Δ64=%+d, RT=%s',
				(string) $row['op'],
				(string) $row['token'],
				(int) ($row['trial_64']['delta_wire'] ?? 0),
				!empty($row['trial_64']['roundtrip_ok']) ? 'ok' : 'FAIL'
			);
		}
	}
	$lines[] = '';
	$lines[] = '## Top near-wins (32p)';
	foreach ($near as $row) {
		$lines[] = sprintf(
			'- `%s %s`: Δ32=%+d (count32=%d)',
			(string) $row['op'],
			(string) $row['token'],
			(int) ($row['trial_32']['delta_wire'] ?? 0),
			(int) ($row['count_32p'] ?? 0)
		);
	}
	$lines[] = '';
	$lines[] = '## Notes';
	$lines[] = '- 384p recovered rejects were hard-skipped from candidate selection.';
	$lines[] = '- Prior small-slice rejects were also skipped, except forced `remove would` retest.';
	file_put_contents($path, implode("\n", $lines) . "\n");
}

$opts = wire460_smallcase_parse_args($argv, $repo);
if (!is_file($opts['seed'])) {
	fwrite(STDERR, "missing seed dict: {$opts['seed']}\n");
	exit(1);
}
if (!is_file($opts['recovered_rejects'])) {
	fwrite(STDERR, "missing recovered rejects: {$opts['recovered_rejects']}\n");
	exit(1);
}

$seedWords = fractal_zip_phda9_dict_read_words($opts['seed']);
$seedSet = array_fill_keys($seedWords, true);

$recovered = wire460_smallcase_load_recovered_rejects($opts['recovered_rejects']);
$secondary = wire460_smallcase_load_secondary_rejects(array(
	$repo . '/benchmarks/.enwik8_wire460_dict_wire_refine_96p.json',
	'/tmp/fz_wire_refine_32p.json',
	'/tmp/fz_wire_refine_32p_a.json',
	'/tmp/fz_wire_refine_64p_remove_all.json',
));

$xml32 = wire460_smallcase_page_xml($repo, (int) $opts['pages_32']);
$rawCandidates = wire460_smallcase_raw_candidates();
$filtered = wire460_smallcase_filter_candidates(
	$rawCandidates,
	$seedSet,
	$recovered['reject_keys'],
	$secondary['reject_keys'],
	(string) $xml32['page_xml'],
	(int) $opts['max_trials']
);

$session = sys_get_temp_dir() . '/fz_wire460_smallcase_' . getmypid() . '_' . bin2hex(random_bytes(4));
$slice32 = fractal_zip_phda9_dict_wire_prepare_slice($repo, (int) $opts['pages_32'], $session . '/slice32');
$run32 = wire460_smallcase_run_32p(
	$repo,
	$slice32['slice_path'],
	$session . '/run32',
	$seedWords,
	$opts['seed'],
	$filtered['selected']
);

$slice64 = fractal_zip_phda9_dict_wire_prepare_slice($repo, (int) $opts['pages_64'], $session . '/slice64');
$run64 = wire460_smallcase_run_64p_confirms(
	$repo,
	$slice64['slice_path'],
	$session . '/run64',
	$seedWords,
	$opts['seed'],
	$run32['trials']
);

$surviving = wire460_smallcase_surviving_wins($run64['rows']);
$near = wire460_smallcase_top_near_wins($run64['rows'], 5);

$report = array(
	'generated' => date('c'),
	'session_dir' => $session,
	'seed_path' => $opts['seed'],
	'seed_words' => count($seedWords),
	'pages_32' => (int) $opts['pages_32'],
	'pages_64' => (int) $opts['pages_64'],
	'max_trials' => (int) $opts['max_trials'],
	'recovered_384_reject_path' => $opts['recovered_rejects'],
	'recovered_384_reject_count' => count($recovered['rows']),
	'secondary_reject_count' => count($secondary['rows']),
	'candidate_pool_total' => count($rawCandidates),
	'candidate_selected_count' => count($filtered['selected']),
	'candidate_skipped_count' => count($filtered['skipped']),
	'candidate_selected' => $filtered['selected'],
	'candidate_skipped' => $filtered['skipped'],
	'baseline' => array(
		'wire_32_fzc' => (int) $run32['baseline_wire'],
		'wire_32_rt_ok' => !empty($run32['baseline_rt_ok']),
		'wire_64_fzc' => (int) $run64['baseline_wire'],
		'wire_64_rt_ok' => !empty($run64['baseline_rt_ok']),
	),
	'trials' => $run64['rows'],
	'wins_32' => array_values(array_filter($run64['rows'], static fn (array $r): bool => !empty($r['win_32']))),
	'surviving_wins_64' => $surviving,
	'near_wins_32' => $near,
);
file_put_contents($opts['out_json'], json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
wire460_smallcase_write_md(
	$opts['out_md'],
	$opts,
	$filtered['selected'],
	$filtered['skipped'],
	$run64['rows'],
	$surviving,
	$near,
	(int) $run32['baseline_wire'],
	(int) $run64['baseline_wire']
);

echo "json => {$opts['out_json']}\n";
echo "md   => {$opts['out_md']}\n";
echo "selected candidates: " . count($filtered['selected']) . "\n";
echo "32p winners: " . count($report['wins_32']) . "\n";
echo "64p surviving wins (RT+delta): " . count($surviving) . "\n";
exit(0);
