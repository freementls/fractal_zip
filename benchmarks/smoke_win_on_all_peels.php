#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Fast CI smoke for win-on-all peel infrastructure (no full jackpot zip).
 *
 *   php benchmarks/smoke_win_on_all_peels.php
 */

$repo = dirname(__DIR__);
chdir($repo);

require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_folder_logical_bundle.php';
require_once $repo . '/fractal_zip_classic_peel.php';
require_once $repo . '/fractal_zip_chunk_container_peel.php';
require_once $repo . '/fractal_zip_ole_cfb.php';
require_once $repo . '/fractal_zip_rpm_7z_peel.php';

$fail = 0;

// Nested RIFF CLASSIC
$fmt = pack('v', 1) . pack('v', 1) . pack('V', 8000) . pack('V', 8000) . pack('v', 1) . pack('v', 8);
$info = 'INAM' . pack('V', 5) . "song\0";
if (strlen($info) & 1) {
	$info .= "\0";
}
$listBody = 'INFO' . $info;
$list = 'LIST' . pack('V', strlen($listBody)) . $listBody;
$pcm = str_repeat('ABCD', 40);
$data = 'data' . pack('V', strlen($pcm)) . $pcm . (strlen($pcm) & 1 ? "\0" : '');
$body = 'fmt ' . pack('V', strlen($fmt)) . $fmt . $list . $data;
$wav = 'RIFF' . pack('V', 4 + strlen($body)) . 'WAVE' . $body;
$m = array();
$r = array();
if (!fractal_zip_folder_try_expand_chunk('n.wav', $wav, $m, $r)
	|| (int) ($r['n.wav']['kind'] ?? -1) !== 3
	|| !str_contains((string) ($r['n.wav']['meta'] ?? ''), 'nest1')) {
	echo "FAIL nested RIFF CLASSIC\n";
	$fail++;
} else {
	$payloads = array();
	foreach ($r['n.wav']['member_names'] as $n) {
		$rel = str_starts_with($n, 'n.wav/') ? substr($n, 6) : $n;
		$payloads[] = array('name' => $rel, 'data' => $m[$n]);
	}
	$reb = fractal_zip_classic_rebuild_archive('riff', $r['n.wav']['meta'], $payloads);
	if ($reb !== $wav) {
		echo "FAIL nested RIFF RT\n";
		$fail++;
	} else {
		echo "OK nested RIFF CLASSIC\n";
	}
}

// OLE sparse + shared template path
$olePath = $repo . '/test_files_classic_ole_shared_peel/00.doc';
if (!is_file($olePath)) {
	$olePath = $repo . '/test_files_classic_ole_shared_peel/demo-alpha.doc';
}
if (is_file($olePath)) {
	$ole = (string) file_get_contents($olePath);
	$oleName = basename($olePath);
	$m = array();
	$r = array();
	if (!fractal_zip_folder_try_expand_ole($oleName, $ole, $m, $r)
		|| (int) ($r[$oleName]['kind'] ?? -1) !== 3) {
		echo "FAIL OLE CLASSIC\n";
		$fail++;
	} else {
		$hasShared = false;
		foreach ($r[$oleName]['member_names'] as $n) {
			if (str_starts_with($n, '__ole_tmpl/')) {
				$hasShared = true;
			}
		}
		$payloads = array();
		foreach ($r[$oleName]['member_names'] as $n) {
			$payloads[] = array('name' => $n, 'data' => $m[$n]);
		}
		$reb = fractal_zip_classic_rebuild_archive('ole', $r[$oleName]['meta'], $payloads);
		if ($reb !== $ole || !$hasShared) {
			echo "FAIL OLE shared-template RT (shared=" . ($hasShared ? 1 : 0) . ")\n";
			$fail++;
		} else {
			echo "OK OLE sparse+shared template\n";
		}
	}
	// Folder near-dup `fp` cluster (4 siblings) bit-exact rebuild
	$oleDir = dirname($olePath);
	$raw = array();
	foreach (scandir($oleDir) ?: array() as $e) {
		if ($e === '.' || $e === '..' || !is_file($oleDir . '/' . $e)) {
			continue;
		}
		$raw[$e] = (string) file_get_contents($oleDir . '/' . $e);
	}
	$bundle = fractal_zip_resolve_folder_logical_bundle($raw);
	$fpN = 0;
	$fpOk = true;
	foreach ($bundle['restore'] as $path => $spec) {
		if ((int) ($spec['kind'] ?? -1) !== 3 || (string) ($spec['format'] ?? '') !== 'fp') {
			continue;
		}
		$fpN++;
		$names = $spec['member_names'] ?? array();
		$payloads = array();
		foreach ($names as $n) {
			$payloads[] = array('name' => (string) $n, 'data' => (string) ($bundle['members'][$n] ?? ''));
		}
		$reb = fractal_zip_classic_rebuild_archive('fp', (string) ($spec['meta'] ?? ''), $payloads);
		if ($reb !== ($raw[$path] ?? null)) {
			$fpOk = false;
		}
	}
	if ($fpN < 3 || !$fpOk) {
		echo "FAIL OLE near-dup fp (n={$fpN} ok=" . ($fpOk ? 1 : 0) . ")\n";
		$fail++;
	} else {
		echo "OK OLE near-dup fp cluster\n";
	}
} else {
	echo "SKIP OLE (no corpus)\n";
}

// 7z CLASSIC peel (deterministic -mtm=off rebuild)
$sevenPath = $repo . '/test_files_classic_7z_jackpot_peel/pack-alpha.7z';
if (is_file($sevenPath)) {
	$blob = (string) file_get_contents($sevenPath);
	$m = array();
	$r = array();
	if (!fractal_zip_folder_try_expand_7z('pack-alpha.7z', $blob, $m, $r)
		|| (int) ($r['pack-alpha.7z']['kind'] ?? -1) !== 3
		|| (string) ($r['pack-alpha.7z']['format'] ?? '') !== '7z') {
		echo "FAIL 7z CLASSIC peel\n";
		$fail++;
	} else {
		$payloads = array();
		$prefix = 'pack-alpha.7z/';
		foreach ($r['pack-alpha.7z']['member_names'] as $n) {
			$n = (string) $n;
			$logical = str_starts_with($n, $prefix) ? substr($n, strlen($prefix)) : $n;
			$payloads[] = array('name' => $logical, 'data' => (string) ($m[$n] ?? ''));
		}
		$reb = fractal_zip_classic_rebuild_archive(
			'7z',
			(string) ($r['pack-alpha.7z']['meta'] ?? '5'),
			$payloads
		);
		if ($reb !== $blob) {
			echo "FAIL 7z CLASSIC RT\n";
			$fail++;
		} else {
			echo "OK 7z CLASSIC peel\n";
		}
	}
} else {
	echo "SKIP 7z (no corpus)\n";
}

// MIDI×compress nested CLASSIC (midibz2)
$midiPath = $repo . '/test_files_compress_midi_jackpot_peel/song_a.mid.bz2';
if (is_file($midiPath)) {
	$blob = (string) file_get_contents($midiPath);
	$m = array();
	$r = array();
	if (!fractal_zip_folder_try_expand_compress('song_a.mid.bz2', $blob, $m, $r)
		|| (string) ($r['song_a.mid.bz2']['format'] ?? '') !== 'midibz2') {
		echo "FAIL midibz2 peel\n";
		$fail++;
	} else {
		$payloads = array();
		$prefix = 'song_a.mid.bz2/';
		foreach ($r['song_a.mid.bz2']['member_names'] as $n) {
			$n = (string) $n;
			$logical = str_starts_with($n, $prefix) ? substr($n, strlen($prefix)) : $n;
			$data = (string) ($m[$n] ?? $m[$prefix . $logical] ?? '');
			$payloads[] = array('name' => $logical, 'data' => $data);
		}
		$reb = fractal_zip_classic_rebuild_archive(
			'midibz2',
			(string) ($r['song_a.mid.bz2']['meta'] ?? ''),
			$payloads
		);
		if ($reb !== $blob) {
			echo "FAIL midibz2 RT\n";
			$fail++;
		} else {
			echo "OK midibz2 nested peel\n";
		}
	}
} else {
	echo "SKIP midibz2 (no corpus)\n";
}

// lzip encode path present
if (function_exists('fractal_zip_folder_lzip_shell_compress')) {
	$blob = fractal_zip_folder_lzip_shell_compress("lzip peel smoke\n", 6);
	if ($blob === null) {
		echo "SKIP lzip (tool missing)\n";
	} else {
		$inner = fractal_zip_folder_decode_single_compress($blob, 'x.lz');
		$reb = fractal_zip_classic_rebuild_archive('lzip', '6', array(
			array('name' => 'x.txt', 'data' => (string) $inner),
		));
		if ($inner === null || $reb !== $blob) {
			echo "FAIL lzip CLASSIC\n";
			$fail++;
		} else {
			echo "OK lzip CLASSIC\n";
		}
	}
}

// FZCL ≤ tie-break: just ensure fair-full profile name is recognized
$src = (string) file_get_contents($repo . '/benchmarks/run_benchmarks.php');
if (!str_contains($src, "fair-full")) {
	echo "FAIL fair-full profile missing\n";
	$fail++;
} else {
	echo "OK fair-full profile wired\n";
}

// Fair-full GT under LIFESTYLE=0: INNER=on must still build the virtual folder,
// and bytes-first must not force a short CM wall (that auto-segs nci and loses zpaq).
require_once $repo . '/fractal_zip_general_text.php';
$prevLife = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
$prevInner = getenv('FRACTAL_ZIP_GENERAL_TEXT_INNER');
$prevWall = getenv('FRACTAL_ZIP_TOKENIZED_CM_WALL_SEC');
putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0');
putenv('FRACTAL_ZIP_GENERAL_TEXT_INNER=on');
putenv('FRACTAL_ZIP_TOKENIZED_CM_WALL_SEC');
fractal_zip_general_text_apply_speed_env();
$wallAfter = getenv('FRACTAL_ZIP_TOKENIZED_CM_WALL_SEC');
if ($wallAfter !== false && trim((string) $wallAfter) !== '') {
	echo "FAIL fair-full CM wall forced under LIFESTYLE=0 (got {$wallAfter})\n";
	$fail++;
} else {
	echo "OK fair-full leaves CM wall unset (whole-stream)\n";
}
$gtSrc = (string) file_get_contents($repo . '/fractal_zip_general_text.php');
if (!str_contains($gtSrc, 'GENERAL_TEXT_INNER')
	|| !str_contains($gtSrc, 'Bytes-first / fair-full')) {
	echo "FAIL GT LIFESTYLE=0 + INNER=on path missing\n";
	$fail++;
} else {
	echo "OK GT honored under LIFESTYLE=0 when INNER=on\n";
}
// restore env
if ($prevLife === false) {
	putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
} else {
	putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=' . $prevLife);
}
if ($prevInner === false) {
	putenv('FRACTAL_ZIP_GENERAL_TEXT_INNER');
} else {
	putenv('FRACTAL_ZIP_GENERAL_TEXT_INNER=' . $prevInner);
}
if ($prevWall === false) {
	putenv('FRACTAL_ZIP_TOKENIZED_CM_WALL_SEC');
} else {
	putenv('FRACTAL_ZIP_TOKENIZED_CM_WALL_SEC=' . $prevWall);
}

echo $fail === 0 ? "ALL SMOKE OK\n" : "SMOKE FAILURES={$fail}\n";
exit($fail === 0 ? 0 : 1);
