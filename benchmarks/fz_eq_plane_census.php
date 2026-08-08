#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * E0 of FZ_DYNAMIC_CONTEXT_EXPERIMENTS.md: equivalence-plane byte census.
 *
 * Two measurements per target folder:
 *  A) wire level: pre-outer unified inner (FRACTAL_ZIP_DUMP_PRE_OUTER_INNER)
 *     compressed by gzip9 / zstd19 / brotli q11 / xz9 / zpaq m5 — how much
 *     headroom a CM-class coder sees over the production outer.
 *  B) plane level: per member, best fz_inner_pass_search row split into
 *     planes (markers / offsets / params / literals / dict / fractal_ref),
 *     each plane compressed separately vs the interleaved equiv baseline —
 *     is there conditional structure that plane separation (E1) or a
 *     provenance-conditioned coder (E2) could exploit?
 *
 * Usage (repo root):
 *   php -d memory_limit=3072M benchmarks/fz_eq_plane_census.php [folders...]
 *   default folders: test_files29 test_files28 test_files2 test_files4 test_files52
 *   --wire-only | --planes-only | --json-only
 * Output: benchmarks/.fz_eq_plane_census.json + stdout table.
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'fractal_inner_passes.inc.php';

$folders = array();
$wireOnly = false;
$planesOnly = false;
$jsonOnly = false;
foreach (array_slice($argv, 1) as $a) {
	if ($a === '--wire-only') { $wireOnly = true; continue; }
	if ($a === '--planes-only') { $planesOnly = true; continue; }
	if ($a === '--json-only') { $jsonOnly = true; continue; }
	$folders[] = $a;
}
if ($folders === array()) {
	$folders = array('test_files29', 'test_files28', 'test_files2', 'test_files4', 'test_files52');
}

/** Compress $bytes with each external codec; returns [codec => bytes|null]. */
function census_codec_sizes(string $bytes): array {
	$out = array('raw' => strlen($bytes));
	$gz = @gzdeflate($bytes, 9);
	$out['gzip9'] = is_string($gz) ? strlen($gz) : null;
	if ($bytes === '') {
		return $out;
	}
	$tmp = tempnam(sys_get_temp_dir(), 'fzc0');
	file_put_contents($tmp, $bytes);
	$jobs = array(
		'zstd19' => "zstd -19 -q -c " . escapeshellarg($tmp) . " | wc -c",
		'brotli11' => "brotli -q 11 -c " . escapeshellarg($tmp) . " | wc -c",
		'xz9' => "xz -9 -q -c " . escapeshellarg($tmp) . " | wc -c",
	);
	foreach ($jobs as $codec => $cmd) {
		$r = trim((string) @shell_exec($cmd . ' 2>/dev/null'));
		$out[$codec] = ($r !== '' && ctype_digit($r)) ? (int) $r : null;
	}
	// zpaq needs an archive path; CM-class headroom proxy.
	$arch = $tmp . '.zpaq';
	@unlink($arch);
	@shell_exec('zpaq a ' . escapeshellarg($arch) . ' ' . escapeshellarg($tmp) . ' -m5 >/dev/null 2>&1');
	$out['zpaq5'] = is_file($arch) ? (int) filesize($arch) : null;
	@unlink($arch);
	@unlink($tmp);
	return $out;
}

/**
 * Split an equivalence string into planes using the active marker context.
 * Digit-start markers are substring ops <off"params>; others (gradients,
 * replaces, ...) go wholesale into the 'ops_other' plane.
 *
 * @return array{markers:string,offsets:string,params:string,literals:string,ops_other:string,op_count:int,other_count:int}
 */
function census_split_equiv_planes(fractal_zip $fz, string $equiv): array {
	$fz->fractal_marker_ctx_publish();
	list($Lq, $Mq, $Rq) = fractal_zip_marker_rx_quoted_delimiters();
	$planes = array(
		'markers' => '', 'offsets' => '', 'params' => '',
		'literals' => '', 'ops_other' => '', 'op_count' => 0, 'other_count' => 0,
	);
	$rxSub = '/' . $Lq . '([0-9]+)' . $Mq . '([^' . $Rq . ']*)' . $Rq . '/s';
	$rxAny = '/' . $Lq . '[^' . $Rq . ']*' . $Rq . '/s';
	$pos = 0;
	if (preg_match_all($rxAny, $equiv, $mAll, PREG_OFFSET_CAPTURE) > 0) {
		foreach ($mAll[0] as $hit) {
			$tok = (string) $hit[0];
			$off = (int) $hit[1];
			$planes['literals'] .= substr($equiv, $pos, $off - $pos);
			$pos = $off + strlen($tok);
			if (preg_match($rxSub, $tok, $m) === 1) {
				$planes['op_count']++;
				$planes['markers'] .= 'S';
				$planes['offsets'] .= $m[1] . "\n";
				$planes['params'] .= $m[2] . "\n";
			} else {
				$planes['other_count']++;
				$planes['markers'] .= 'O';
				$planes['ops_other'] .= $tok;
			}
		}
	}
	$planes['literals'] .= substr($equiv, $pos);
	return $planes;
}

$report = array('generated' => date('c'), 'folders' => array());

foreach ($folders as $folder) {
	$dir = $repo . DIRECTORY_SEPARATOR . $folder;
	if (!is_dir($dir)) {
		fwrite(STDERR, "skip missing folder {$folder}\n");
		continue;
	}
	$entry = array('folder' => $folder);
	fwrite(STDERR, "[census] {$folder}\n");

	// ---- A) wire level ----
	if (!$planesOnly) {
		$dump = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_census_inner_' . getmypid() . '.bin';
		@unlink($dump);
		putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER=' . $dump);
		putenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY=1');
		$t0 = microtime(true);
		$fzA = new fractal_zip();
		try {
			$fzA->zip_folder($dir, false);
		} catch (Throwable $e) {
			fwrite(STDERR, "  wire: zip_folder failed: " . $e->getMessage() . "\n");
		}
		putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');
		putenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY');
		$inner = is_file($dump) ? (string) file_get_contents($dump) : '';
		@unlink($dump);
		$fzcPath = rtrim($dir, DIRECTORY_SEPARATOR) . '.fz';
		$entry['wire'] = array(
			'inner_codecs' => census_codec_sizes($inner),
			'fzc_on_disk' => is_file($fzcPath) ? (int) filesize($fzcPath) : null,
			'zip_seconds' => round(microtime(true) - $t0, 2),
		);
	}

	// ---- B) plane level ----
	if (!$wireOnly) {
		$fz = new fractal_zip();
		$members = array();
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
		foreach ($it as $f) {
			if ($f->isFile()) {
				$members[$it->getSubPathname()] = (string) file_get_contents($f->getPathname());
			}
		}
		ksort($members, SORT_STRING);
		$agg = array(
			'members' => 0, 'members_with_rows' => 0,
			'raw' => 0, 'equiv' => 0, 'dict' => 0, 'fractal_ref' => 0,
			'markers' => 0, 'offsets' => 0, 'params' => 0, 'literals' => 0, 'ops_other' => 0,
			'op_count' => 0, 'other_count' => 0,
		);
		$equivCat = '';
		$planesCat = array('markers' => '', 'offsets' => '', 'params' => '', 'literals' => '', 'ops_other' => '');
		$dictCat = '';
		$refCat = '';
		foreach ($members as $path => $raw) {
			$agg['members']++;
			$agg['raw'] += strlen($raw);
			$sr = fz_inner_pass_search($fz, $raw, 1, 0, true, 5000000, null, true, false, false, 20.0);
			$rows = $sr['rows'] ?? null;
			if (!is_array($rows) || $rows === array()) {
				continue;
			}
			$best = null;
			$bestFin = PHP_INT_MAX;
			foreach ($rows as $row) {
				$lin = $row['linears'] ?? array();
				$fin = is_array($lin) && $lin !== array() ? (int) $lin[count($lin) - 1] : PHP_INT_MAX;
				if ($fin < $bestFin) {
					$bestFin = $fin;
					$best = $row;
				}
			}
			if ($best === null) {
				continue;
			}
			$agg['members_with_rows']++;
			$eq = (string) ($best['equiv'] ?? '');
			$dict = (string) ($best['dict'] ?? '');
			$ref = (string) ($best['fractal_ref'] ?? '');
			$agg['equiv'] += strlen($eq);
			$agg['dict'] += strlen($dict);
			$agg['fractal_ref'] += strlen($ref);
			$equivCat .= $eq;
			$dictCat .= $dict;
			$refCat .= $ref;
			$pl = census_split_equiv_planes($fz, $eq);
			foreach (array('markers', 'offsets', 'params', 'literals', 'ops_other') as $k) {
				$agg[$k] += strlen($pl[$k]);
				$planesCat[$k] .= $pl[$k];
			}
			$agg['op_count'] += $pl['op_count'];
			$agg['other_count'] += $pl['other_count'];
		}
		$entry['planes'] = array(
			'agg' => $agg,
			'equiv_interleaved_codecs' => census_codec_sizes($equivCat),
			'plane_codecs' => array(),
			'dict_codecs' => census_codec_sizes($dictCat),
			'fractal_ref_codecs' => census_codec_sizes($refCat),
		);
		$planeSplitTotal = array('gzip9' => 0, 'zstd19' => 0, 'brotli11' => 0, 'xz9' => 0, 'zpaq5' => 0);
		foreach ($planesCat as $k => $bytes) {
			$sz = census_codec_sizes($bytes);
			$entry['planes']['plane_codecs'][$k] = $sz;
			foreach ($planeSplitTotal as $codec => $_) {
				$v = $sz[$codec] ?? ($sz['raw'] === 0 ? 0 : null);
				if ($v !== null && $planeSplitTotal[$codec] !== null) {
					$planeSplitTotal[$codec] += $v;
				} else {
					$planeSplitTotal[$codec] = null;
				}
			}
		}
		$entry['planes']['plane_split_totals'] = $planeSplitTotal;
	}
	$report['folders'][] = $entry;
}

$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.fz_eq_plane_census.json';
file_put_contents($outPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "census → {$outPath}\n";

if (!$jsonOnly) {
	foreach ($report['folders'] as $entry) {
		echo "\n== {$entry['folder']} ==\n";
		if (isset($entry['wire'])) {
			$w = $entry['wire']['inner_codecs'];
			echo "  wire inner raw=" . number_format((int) $w['raw']);
			foreach (array('gzip9', 'zstd19', 'brotli11', 'xz9', 'zpaq5') as $c) {
				if (isset($w[$c]) && $w[$c] !== null) {
					echo " {$c}=" . number_format((int) $w[$c]);
				}
			}
			echo "  fzc=" . number_format((int) ($entry['wire']['fzc_on_disk'] ?? 0)) . "\n";
		}
		if (isset($entry['planes'])) {
			$a = $entry['planes']['agg'];
			echo "  members={$a['members']} with_rows={$a['members_with_rows']} raw=" . number_format((int) $a['raw'])
				. " equiv=" . number_format((int) $a['equiv'])
				. " dict=" . number_format((int) $a['dict'])
				. " ref=" . number_format((int) $a['fractal_ref']) . "\n";
			echo "    planes: markers=" . number_format((int) $a['markers'])
				. " offsets=" . number_format((int) $a['offsets'])
				. " params=" . number_format((int) $a['params'])
				. " literals=" . number_format((int) $a['literals'])
				. " ops_other=" . number_format((int) $a['ops_other'])
				. " (subst_ops={$a['op_count']} other_ops={$a['other_count']})\n";
			$il = $entry['planes']['equiv_interleaved_codecs'];
			$sp = $entry['planes']['plane_split_totals'];
			foreach (array('gzip9', 'zstd19', 'brotli11', 'xz9', 'zpaq5') as $c) {
				$a1 = $il[$c] ?? null;
				$b1 = $sp[$c] ?? null;
				if ($a1 !== null && $b1 !== null) {
					$d = $a1 - $b1;
					echo "    {$c}: interleaved=" . number_format($a1) . " plane_split=" . number_format($b1)
						. " delta=" . ($d >= 0 ? '-' : '+') . number_format(abs($d)) . "\n";
				}
			}
		}
	}
}
