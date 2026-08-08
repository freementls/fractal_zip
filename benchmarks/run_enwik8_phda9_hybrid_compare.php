#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare 15M raw phda9 squash vs integrated phda9_xml (chunked vs single-stream).
 *
 * Hybrid axes:
 *   - raw squash: one phda9 context on raw byte order (~15.01 MiB ref)
 *   - phda9_xml pp96: sorted XML, parallel chunks, verified FZEP restore (~18.85 MiB prod)
 *   - phda9_xml single-stream: sorted XML, one phda9 context (combines both advantages)
 *   - dual-order: min(sorted wire, cached raw FZpq) when .enwik8_paq_squash.fzpq exists
 *
 * Usage:
 *   php benchmarks/run_enwik8_phda9_hybrid_compare.php [--pages=384] [--roundtrip]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_PROCESS_GUARD_ORPHAN_GRACE_SEC=600');

$pages = 384;
$roundtrip = false;
$wireOnly = false;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif ($arg === '--roundtrip') {
		$roundtrip = true;
	} elseif ($arg === '--wire-only') {
		$wireOnly = true;
	}
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_phda9_english.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pages, count($split['pages']));
$slice = (string) $split['header'];
for ($i = 0; $i < $n; $i++) {
	$slice .= substr($blob, (int) $split['pages'][$i]['start'], (int) $split['pages'][$i]['len']);
}
$slice .= (string) $split['footer'];

$sortedChunk = array();
for ($i = 0; $i < $n; $i++) {
	$sortedChunk[] = array(
		'origIndex' => $i,
		'start' => (int) $split['pages'][$i]['start'],
		'len' => (int) $split['pages'][$i]['len'],
	);
}
$payloads = fractal_zip_enwik_phda9_english_payloads_from_refs($sortedChunk, $blob);
$tool = 'phda9_no_lstm';
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=' . $tool);
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');

$isolated = array();
if (!$wireOnly) {
	foreach (array('raw_slice' => $slice, 'sorted_page_xml' => $payloads['sorted_page_xml']) as $label => $plain) {
		$t0 = microtime(true);
		$r = fractal_zip_enwik_phda9_english_compress($plain, array(
			'tool' => $tool,
			'timeout_sec' => 0,
		));
		$bytes = $r['bytes'] ?? null;
		$isolated[$label] = array(
			'plain_bytes' => strlen($plain),
			'phda9_bytes' => $bytes,
			'bpc' => ($bytes !== null && strlen($plain) > 0)
				? round(8 * $bytes / strlen($plain), 4)
				: null,
			'seconds' => round(microtime(true) - $t0, 2),
			'roundtrip_ok' => !empty($r['roundtrip_ok']),
			'status' => (string) ($r['status'] ?? ''),
		);
	}
}

/**
 * @return array{wire_bytes: int, encode_sec: float, roundtrip_ok: ?bool, members: ?int}
 */
function phda9_hybrid_wire_encode(string $repo, string $slice, int $n, bool $singleStream, bool $roundtrip): array
{
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_hybrid_' . getmypid() . '_' . ($singleStream ? '1' : '0');
	@mkdir($tmp, 0700, true);
	$work = $tmp . DIRECTORY_SEPARATOR . 'w';
	@mkdir($work, 0700, true);
	file_put_contents($work . DIRECTORY_SEPARATOR . 'enwik8', $slice);

	bench_world_record_apply_pp96_core_env();
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
	putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
	putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=' . ($singleStream ? '1' : '0'));
	if ($singleStream) {
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
	} else {
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS');
	}

	$t0 = microtime(true);
	$fz = new fractal_zip(256, false, true, null, false);
	ob_start();
	try {
		$fz->zip_folder($work, false);
	} finally {
		ob_end_clean();
	}
	$fzc = $work . '.fz';
	$wire = is_file($fzc) ? (int) filesize($fzc) : 0;
	$sec = round(microtime(true) - $t0, 2);
	$rtOk = null;
	if ($roundtrip && $wire > 0) {
		$extract = $tmp . DIRECTORY_SEPARATOR . 'ex';
		@mkdir($extract, 0700, true);
		copy($fzc, $extract . DIRECTORY_SEPARATOR . 'enwik8.fz');
		$fx = new fractal_zip(256, false, true, null, false);
		ob_start();
		try {
			$fx->open_container($extract . DIRECTORY_SEPARATOR . 'enwik8.fz', false);
		} finally {
			ob_end_clean();
		}
		$got = is_file($extract . DIRECTORY_SEPARATOR . 'enwik8')
			? (string) file_get_contents($extract . DIRECTORY_SEPARATOR . 'enwik8')
			: '';
		$rtOk = hash_equals($slice, $got);
	}
	$members = $fz->zip_folder_member_count > 0 ? $fz->zip_folder_member_count : null;
	fractal_zip_enwik_recursive_remove($tmp);
	return array(
		'wire_bytes' => $wire,
		'encode_sec' => $sec,
		'roundtrip_ok' => $rtOk,
		'members' => $members,
	);
}

$wireChunked = phda9_hybrid_wire_encode($repo, $slice, $n, false, $roundtrip);
$wireSingle = phda9_hybrid_wire_encode($repo, $slice, $n, true, $roundtrip);

$squash = fractal_zip_enwik_try_load_paq_squash_wire($repo);
$squashMeta = null;
if (is_file($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.json')) {
	$squashMeta = json_decode(
		(string) file_get_contents($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.json'),
		true
	);
}

$report = array(
	'generated' => date('c'),
	'pages' => $n,
	'isolated_phda9' => $isolated,
	'wire_pp96_parallel' => $wireChunked,
	'wire_single_stream' => $wireSingle,
	'raw_squash_ref' => array(
		'full_corpus_bytes' => is_array($squashMeta) ? ($squashMeta['bytes'] ?? null) : null,
		'full_corpus_tool' => is_array($squashMeta) ? ($squashMeta['tool'] ?? null) : null,
		'wire_cache_present' => $squash !== null,
		'wire_cache_bytes' => $squash !== null ? strlen($squash['wire']) : null,
	),
	'hybrid_notes' => array(
		'raw_squash' => 'One phda9 on raw enwik8; best bytes; no sorted-member integration.',
		'phda9_xml_pp96' => 'Sorted page XML + parallel chunks + FZEP; production path.',
		'phda9_xml_single_stream' => 'Sorted page XML + one phda9 context; bridges squash context with integrated restore.',
		'dual_order' => 'After sorted encode, replace .fz body with cached raw FZpq if smaller (FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=1).',
	),
);

$outJson = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_phda9_hybrid_compare.json';
file_put_contents($outJson, json_encode($report, JSON_PRETTY_PRINT));

$deltaSingle = $wireSingle['wire_bytes'] - $wireChunked['wire_bytes'];
if ($isolated !== array()) {
	fwrite(STDERR, "[hybrid] pages={$n} isolated raw_slice bpc={$isolated['raw_slice']['bpc']} sorted_xml bpc={$isolated['sorted_page_xml']['bpc']}\n");
}
fwrite(STDERR, "[hybrid] wire pp96={$wireChunked['wire_bytes']} B single={$wireSingle['wire_bytes']} B delta={$deltaSingle}\n");
if ($squash !== null) {
	fwrite(STDERR, '[hybrid] full squash cache=' . number_format(strlen($squash['wire'])) . " B (dual-order eligible)\n");
} else {
	fwrite(STDERR, "[hybrid] squash cache missing — export via bench_enwik8_paq_export_wire.php for dual-order\n");
}
echo json_encode($report, JSON_PRETTY_PRINT) . "\n";
echo "out_json={$outJson}\n";

if ($roundtrip) {
	foreach (array($wireChunked, $wireSingle) as $w) {
		if ($w['roundtrip_ok'] === false) {
			exit(1);
		}
	}
	if (!$wireOnly) {
		foreach ($isolated as $row) {
			if (!$row['roundtrip_ok']) {
				exit(1);
			}
		}
	}
}
