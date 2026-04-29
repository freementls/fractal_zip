<?php

/**
 * OLE compound file binary (CFB / structured storage) helpers for fractal_zip literal mode 19.
 * Bit-exact round-trip uses a zeroed template (non-stream bytes preserved) plus per-stream ranges and payloads.
 *
 * @see https://learn.microsoft.com/en-us/openspecs/windows_protocols/ms-cfb/
 */

/** @var string */
const FRACTAL_ZIP_OLE_MAGIC = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";

const FRACTAL_ZIP_OLE_ENDOFCHAIN = 0xFFFFFFFE;
const FRACTAL_ZIP_OLE_FREESECT = 0xFFFFFFFF;
const FRACTAL_ZIP_OLE_MAX_SECTORS = 0x1000000;
const FRACTAL_ZIP_OLE_MAX_STREAM_BYTES = 268435456;

function fractal_zip_ole_is_compound(string $b): bool {
	$n = strlen($b);
	return $n >= 512 && substr($b, 0, 8) === FRACTAL_ZIP_OLE_MAGIC;
}

/**
 * @param list<int> $fat
 * @return list<array{0:int,1:int}>
 */
function fractal_zip_ole_read_stream_sectors(string $b, int $ss, array $fat, int $firstSid, int $size): ?array {
	if($size === 0) {
		return array();
	}
	if($firstSid < 0 || $firstSid >= sizeof($fat)) {
		return null;
	}
	$ranges = array();
	$rem = $size;
	$sid = $firstSid;
	$guard = 0;
	while($rem > 0) {
		if($guard++ > sizeof($fat) + 8) {
			return null;
		}
		if($sid < 0 || $sid >= sizeof($fat)) {
			return null;
		}
		$fo = 512 + $sid * $ss;
		if($fo < 512 || $fo + $ss > strlen($b)) {
			return null;
		}
		$take = ($rem < $ss) ? $rem : $ss;
		$ranges[] = array($fo, $take);
		$rem -= $take;
		$nxt = (int) $fat[$sid];
		if($rem > 0 && ($nxt === FRACTAL_ZIP_OLE_FREESECT || $nxt === FRACTAL_ZIP_OLE_ENDOFCHAIN)) {
			return null;
		}
		$sid = $nxt;
	}
	return $ranges;
}

/**
 * @param list<int> $miniFat
 * @param list<array{0:int,1:int}> $miniRootRanges ordered file ranges for root mini-stream container
 * @return list<array{0:int,1:int}>|null
 */
function fractal_zip_ole_read_mini_stream_ranges(
	string $b,
	int $mss,
	int $miniStreamLen,
	array $miniFat,
	array $miniRootRanges,
	int $firstMiniSid,
	int $size
): ?array {
	if($size === 0) {
		return array();
	}
	if($mss < 1 || $firstMiniSid < 0) {
		return null;
	}
	$ranges = array();
	$rem = $size;
	$msid = $firstMiniSid;
	$guard = 0;
	while($rem > 0) {
		if($guard++ > sizeof($miniFat) + 8) {
			return null;
		}
		if($msid < 0 || $msid >= sizeof($miniFat)) {
			return null;
		}
		$logicalBase = $msid * $mss;
		if($logicalBase >= $miniStreamLen || $logicalBase + $take > $miniStreamLen) {
			return null;
		}
		$take = ($rem < $mss) ? $rem : $mss;
		$fr = fractal_zip_ole_logical_range_to_file_ranges($miniRootRanges, $logicalBase, $take);
		if($fr === null) {
			return null;
		}
		foreach($fr as $pair) {
			$ranges[] = $pair;
		}
		$rem -= $take;
		$nxt = (int) $miniFat[$msid];
		if($rem > 0 && ($nxt === FRACTAL_ZIP_OLE_FREESECT || $nxt === FRACTAL_ZIP_OLE_ENDOFCHAIN)) {
			return null;
		}
		$msid = $nxt;
	}
	return $ranges;
}

/**
 * @param list<array{0:int,1:int}> $rootRanges
 * @return list<array{0:int,1:int}>|null
 */
function fractal_zip_ole_logical_range_to_file_ranges(array $rootRanges, int $logicalOff, int $len): ?array {
	if($len < 0) {
		return null;
	}
	$out = array();
	$need = $len;
	$pos = 0;
	foreach($rootRanges as $pair) {
		$fo = (int) $pair[0];
		$ln = (int) $pair[1];
		$segEnd = $pos + $ln;
		if($segEnd <= $logicalOff) {
			$pos = $segEnd;
			continue;
		}
		if($pos >= $logicalOff + $need) {
			break;
		}
		$interStart = max($pos, $logicalOff);
		$interEnd = min($segEnd, $logicalOff + $need);
		if($interStart < $interEnd) {
			$sliceOff = $fo + ($interStart - $pos);
			$sliceLen = $interEnd - $interStart;
			$out[] = array($sliceOff, $sliceLen);
		}
		$pos = $segEnd;
	}
	$got = 0;
	foreach($out as $p) {
		$got += (int) $p[1];
	}
	return ($got === $len) ? $out : null;
}

/**
 * Read bytes from file using ordered (offset,len) ranges.
 */
function fractal_zip_ole_read_ranges(string $b, array $ranges): string {
	$acc = '';
	foreach($ranges as $pair) {
		$acc .= substr($b, (int) $pair[0], (int) $pair[1]);
	}
	return $acc;
}

/**
 * @param list<array{0:int,1:int}> $ranges
 */
function fractal_zip_ole_write_ranges(string &$buf, array $ranges, string $data): bool {
	$n = strlen($data);
	$at = 0;
	foreach($ranges as $pair) {
		$fo = (int) $pair[0];
		$ln = (int) $pair[1];
		if($at + $ln > $n) {
			return false;
		}
		for($i = 0; $i < $ln; $i++) {
			$buf[$fo + $i] = $data[$at + $i];
		}
		$at += $ln;
	}
	return $at === $n;
}

/**
 * @return list<int>|null
 */
function fractal_zip_ole_build_fat(string $b, array $h): ?array {
	$n = strlen($b);
	$ss = (int) $h['ss'];
	if($ss < 512 || ($ss & ($ss - 1)) !== 0 || $ss > 65536) {
		return null;
	}
	$spf = (int) ($ss / 4);
	$nFat = (int) $h['csectFat'];
	if($nFat < 1 || $nFat > FRACTAL_ZIP_OLE_MAX_SECTORS) {
		return null;
	}
	$fatSecList = array();
	for($i = 0; $i < 109; $i++) {
		$o = 76 + $i * 4;
		if($o + 4 > 512) {
			break;
		}
		$u = unpack('Vsid', substr($b, $o, 4));
		$sid = (int) ($u['sid'] ?? FRACTAL_ZIP_OLE_FREESECT);
		if($sid === FRACTAL_ZIP_OLE_FREESECT) {
			break;
		}
		$fatSecList[] = $sid;
	}
	if(sizeof($fatSecList) < $nFat) {
		$dif = (int) $h['firstDifat'];
		$ndif = (int) $h['nDifat'];
		$guard = 0;
		while($dif !== FRACTAL_ZIP_OLE_ENDOFCHAIN && $dif !== FRACTAL_ZIP_OLE_FREESECT && sizeof($fatSecList) < $nFat && $guard < 65536) {
			if($guard++ > 65535) {
				return null;
			}
			if($dif < 0 || 512 + $dif * $ss + $ss > $n) {
				return null;
			}
			$sec = substr($b, 512 + $dif * $ss, $ss);
			$lim = min(127, $spf - 1);
			for($i = 0; $i < $lim; $i++) {
				$u = unpack('Vsid', substr($sec, $i * 4, 4));
				$sid = (int) ($u['sid'] ?? FRACTAL_ZIP_OLE_FREESECT);
				if($sid === FRACTAL_ZIP_OLE_FREESECT) {
					break 2;
				}
				$fatSecList[] = $sid;
				if(sizeof($fatSecList) >= $nFat) {
					break 2;
				}
			}
			$u = unpack('Vnxt', substr($sec, 127 * 4, 4));
			$dif = (int) ($u['nxt'] ?? FRACTAL_ZIP_OLE_ENDOFCHAIN);
		}
	}
	if(sizeof($fatSecList) < $nFat) {
		return null;
	}
	$maxSid = (int) (max(0, ($n - 512) / $ss));
	$fatLen = min(FRACTAL_ZIP_OLE_MAX_SECTORS, ($maxSid + $nFat * $spf + 16));
	$fat = array_fill(0, $fatLen, FRACTAL_ZIP_OLE_FREESECT);
	for($fi = 0; $fi < $nFat; $fi++) {
		$fsid = (int) $fatSecList[$fi];
		if($fsid < 0 || 512 + $fsid * $ss + $ss > $n) {
			return null;
		}
		$sec = substr($b, 512 + $fsid * $ss, $ss);
		$base = $fi * $spf;
		for($j = 0; $j < $spf; $j++) {
			$u = unpack('Vv', substr($sec, $j * 4, 4));
			$idx = $base + $j;
			if($idx < $fatLen) {
				$fat[$idx] = (int) ($u['v'] ?? FRACTAL_ZIP_OLE_FREESECT);
			}
		}
	}
	return $fat;
}

/**
 * @return list<int>|null
 */
function fractal_zip_ole_build_mini_fat(string $b, int $ss, array $fat, array $h): ?array {
	$nMini = (int) $h['nMiniFat'];
	if($nMini === 0) {
		return array();
	}
	$spf = (int) ($ss / 4);
	$first = (int) $h['firstMiniFat'];
	$ranges = fractal_zip_ole_read_stream_sectors($b, $ss, $fat, $first, $nMini * $ss);
	if($ranges === null) {
		return null;
	}
	$raw = fractal_zip_ole_read_ranges($b, $ranges);
	if(strlen($raw) < $nMini * $ss) {
		return null;
	}
	$out = array();
	for($i = 0; $i < $nMini * $spf; $i++) {
		if($i * 4 + 4 > strlen($raw)) {
			break;
		}
		$u = unpack('Vv', substr($raw, $i * 4, 4));
		$out[] = (int) ($u['v'] ?? FRACTAL_ZIP_OLE_FREESECT);
	}
	return $out;
}

/**
 * Concatenate all directory sectors following the FAT chain from firstDir (MS-CFB: csectDir is often 0 for v3).
 */
function fractal_zip_ole_read_directory_raw(string $b, int $ss, array $fat, int $firstDir): ?string {
	if($firstDir === FRACTAL_ZIP_OLE_ENDOFCHAIN || $firstDir === FRACTAL_ZIP_OLE_FREESECT) {
		return null;
	}
	$raw = '';
	$sid = $firstDir;
	$guard = 0;
	while($sid !== FRACTAL_ZIP_OLE_ENDOFCHAIN && $sid !== FRACTAL_ZIP_OLE_FREESECT) {
		if($guard++ > sizeof($fat) + 4) {
			return null;
		}
		if($sid < 0 || $sid >= sizeof($fat) || 512 + $sid * $ss + $ss > strlen($b)) {
			return null;
		}
		$raw .= substr($b, 512 + $sid * $ss, $ss);
		$sid = (int) $fat[$sid];
	}
	return $raw;
}

/**
 * @return list<array<string,mixed>>|null
 */
function fractal_zip_ole_parse_directory_entries(string $raw): ?array {
	$entries = array();
	$step = 128;
	$max = strlen($raw);
	for($off = 0; $off + $step <= $max; $off += $step) {
		$slice = substr($raw, $off, $step);
		$nb = unpack('vcb', substr($slice, 64, 2));
		$cb = (int) ($nb['cb'] ?? 0);
		if($cb === 0) {
			continue;
		}
		$type = ord($slice[66]);
		$left = unpack('Vv', substr($slice, 68, 4));
		$right = unpack('Vv', substr($slice, 72, 4));
		$child = unpack('Vv', substr($slice, 76, 4));
		$start = unpack('Vv', substr($slice, 116, 4));
		$szLo = unpack('Vv', substr($slice, 120, 4));
		$szHi = unpack('Vv', substr($slice, 124, 4));
		$size = (int) (($szHi['v'] ?? 0) > 0 ? FRACTAL_ZIP_OLE_MAX_STREAM_BYTES + 1 : ($szLo['v'] ?? 0));
		if($size < 0 || $size > FRACTAL_ZIP_OLE_MAX_STREAM_BYTES) {
			return null;
		}
		$nameRaw = substr($slice, 0, min(64, $cb));
		$nameUtf8 = @iconv('UTF-16LE', 'UTF-8//IGNORE', $nameRaw);
		if(!is_string($nameUtf8)) {
			$nameUtf8 = '';
		}
		$nameUtf8 = str_replace("\0", '', $nameUtf8);
		$entries[] = array(
			'type' => $type,
			'name' => $nameUtf8,
			'left' => (int) ($left['v'] ?? FRACTAL_ZIP_OLE_FREESECT),
			'right' => (int) ($right['v'] ?? FRACTAL_ZIP_OLE_FREESECT),
			'child' => (int) ($child['v'] ?? FRACTAL_ZIP_OLE_FREESECT),
			'start' => (int) ($start['v'] ?? FRACTAL_ZIP_OLE_ENDOFCHAIN),
			'size' => $size,
		);
	}
	return $entries;
}

/**
 * @param list<array<string,mixed>> $entries
 */
function fractal_zip_ole_find_root_index(array $entries): int {
	foreach($entries as $i => $e) {
		if((int) $e['type'] === 5) {
			return (int) $i;
		}
	}
	return 0;
}

/**
 * In-order walk on the red-black directory tree rooted at $nodeIdx (direct children of a storage).
 *
 * @param list<array<string,mixed>> $entries
 * @param callable(int):bool $visit
 */
function fractal_zip_ole_dir_inorder_visit(array $entries, int $nodeIdx, $visit): bool {
	if($nodeIdx === FRACTAL_ZIP_OLE_FREESECT || $nodeIdx < 0 || $nodeIdx >= sizeof($entries)) {
		return true;
	}
	$e = $entries[$nodeIdx];
	$left = (int) $e['left'];
	$right = (int) $e['right'];
	if($left !== FRACTAL_ZIP_OLE_FREESECT) {
		if(!fractal_zip_ole_dir_inorder_visit($entries, $left, $visit)) {
			return false;
		}
	}
	if(!$visit($nodeIdx)) {
		return false;
	}
	if($right !== FRACTAL_ZIP_OLE_FREESECT) {
		if(!fractal_zip_ole_dir_inorder_visit($entries, $right, $visit)) {
			return false;
		}
	}
	return true;
}

/**
 * @param list<array<string,mixed>> $entries
 */
function fractal_zip_ole_walk_storage_children(
	string $b,
	int $ss,
	int $mss,
	int $miniCut,
	array $fat,
	array $miniFat,
	array $miniRootRanges,
	int $miniStreamLen,
	array $entries,
	int $storageIdx,
	string $pathPrefix,
	array &$streamsOut
): bool {
	if($storageIdx < 0 || $storageIdx >= sizeof($entries)) {
		return false;
	}
	$child = (int) $entries[$storageIdx]['child'];
	if($child === FRACTAL_ZIP_OLE_FREESECT) {
		return true;
	}
	return fractal_zip_ole_dir_inorder_visit($entries, $child, static function($idx) use (
		$b, $ss, $mss, $miniCut, $fat, $miniFat, $miniRootRanges, $miniStreamLen, $entries, $pathPrefix, &$streamsOut
	) {
		$e = $entries[$idx];
		$name = (string) $e['name'];
		$type = (int) $e['type'];
		$full = ($pathPrefix === '') ? $name : ($pathPrefix . '/' . $name);
		if($type === 2) {
			$sz = (int) $e['size'];
			if($sz > 0) {
				$start = (int) $e['start'];
				if($sz < $miniCut) {
					$ranges = fractal_zip_ole_read_mini_stream_ranges($b, $mss, $miniStreamLen, $miniFat, $miniRootRanges, $start, $sz);
				} else {
					$ranges = fractal_zip_ole_read_stream_sectors($b, $ss, $fat, $start, $sz);
				}
				if($ranges === null) {
					return false;
				}
				$data = fractal_zip_ole_read_ranges($b, $ranges);
				if(strlen($data) !== $sz) {
					return false;
				}
				$streamsOut[] = array('name' => $full, 'data' => $data, 'ranges' => $ranges);
			}
			return true;
		}
		if($type === 1) {
			return fractal_zip_ole_walk_storage_children(
				$b,
				$ss,
				$mss,
				$miniCut,
				$fat,
				$miniFat,
				$miniRootRanges,
				$miniStreamLen,
				$entries,
				$idx,
				$full,
				$streamsOut
			);
		}
		return true;
	});
}

/**
 * @return array{template:string,streams:list<array{name:string,data:string,ranges:list<array{0:int,1:int}>}>}|null
 */
function fractal_zip_ole_build_template_and_streams(string $b): ?array {
	if(!fractal_zip_ole_is_compound($b)) {
		return null;
	}
	$n = strlen($b);
	if($n < 512) {
		return null;
	}
	$u = unpack(
		'a8sig/a16clsid/vminor/vmajor/vorder/vss/vmss/vres/Ires2/IcsectDir/IcsectFat/IfirstDir/Itx/IminiCut/IfirstMiniFat/InMiniFat/IfirstDifat/InDifat',
		substr($b, 0, 76)
	);
	if($u === false) {
		return null;
	}
	if((int) ($u['order'] ?? 0) !== 0xFFFE) {
		return null;
	}
	$ss = 1 << (int) ($u['ss'] ?? 0);
	$mss = 1 << (int) ($u['mss'] ?? 0);
	if($ss < 512 || $mss < 1) {
		return null;
	}
	$h = array(
		'ss' => $ss,
		'mss' => $mss,
		'csectDir' => (int) ($u['csectDir'] ?? 0),
		'csectFat' => (int) ($u['csectFat'] ?? 0),
		'firstDir' => (int) ($u['firstDir'] ?? FRACTAL_ZIP_OLE_ENDOFCHAIN),
		'miniCut' => (int) ($u['miniCut'] ?? 4096),
		'firstMiniFat' => (int) ($u['firstMiniFat'] ?? FRACTAL_ZIP_OLE_ENDOFCHAIN),
		'nMiniFat' => (int) ($u['nMiniFat'] ?? 0),
		'firstDifat' => (int) ($u['firstDifat'] ?? FRACTAL_ZIP_OLE_ENDOFCHAIN),
		'nDifat' => (int) ($u['nDifat'] ?? 0),
	);
	$fat = fractal_zip_ole_build_fat($b, $h);
	if($fat === null) {
		return null;
	}
	$dirRaw = fractal_zip_ole_read_directory_raw($b, $ss, $fat, (int) $h['firstDir']);
	if($dirRaw === null || $dirRaw === '') {
		return null;
	}
	$entries = fractal_zip_ole_parse_directory_entries($dirRaw);
	if($entries === null || sizeof($entries) < 1) {
		return null;
	}
	$rootIdx = fractal_zip_ole_find_root_index($entries);
	$root = $entries[$rootIdx];
	$miniStreamLen = (int) $root['size'];
	$miniRootRanges = fractal_zip_ole_read_stream_sectors($b, $ss, $fat, (int) $root['start'], $miniStreamLen);
	if($miniRootRanges === null) {
		return null;
	}
	$miniFat = fractal_zip_ole_build_mini_fat($b, $ss, $fat, $h);
	if($miniFat === null) {
		return null;
	}
	$streamsOut = array();
	if(!fractal_zip_ole_walk_storage_children(
		$b,
		$ss,
		$mss,
		(int) $h['miniCut'],
		$fat,
		$miniFat,
		$miniRootRanges,
		$miniStreamLen,
		$entries,
		$rootIdx,
		'',
		$streamsOut
	)) {
		return null;
	}
	if(sizeof($streamsOut) < 1) {
		return null;
	}
	$template = $b;
	foreach($streamsOut as $st) {
		foreach($st['ranges'] as $pair) {
			$fo = (int) $pair[0];
			$ln = (int) $pair[1];
			if($fo + $ln > $n || $fo < 0) {
				return null;
			}
			for($i = 0; $i < $ln; $i++) {
				$template[$fo + $i] = "\0";
			}
		}
	}
	return array('template' => $template, 'streams' => $streamsOut);
}

/**
 * @param list<array{name:string,data:string,ranges:list<array{0:int,1:int}>}> $streams
 */
function fractal_zip_ole_rebuild_from_template(string $template, array $streams): ?string {
	$buf = $template;
	$n = strlen($buf);
	foreach($streams as $st) {
		$data = (string) $st['data'];
		$ranges = $st['ranges'];
		if(!fractal_zip_ole_write_ranges($buf, $ranges, $data)) {
			return null;
		}
	}
	if(strlen($buf) !== $n) {
		return null;
	}
	return $buf;
}
