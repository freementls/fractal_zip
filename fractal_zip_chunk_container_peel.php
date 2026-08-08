<?php
declare(strict_types=1);

/**
 * Encode-time peel for chunked binary containers:
 * - IFF FORM (big-endian): .iff / .ilbm / .lbm / .8svx / …
 * - RIFF (little-endian): .wav / .avi / .webp (RIFF) / …
 * - MIDI SMF: .mid / .midi — one member per MTrk (+ header)
 *
 * Restore: CLASSIC when bit-exact rebuild exists (midi / flat or nested RIFF/IFF);
 *          else FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM.
 *
 * Kill: FRACTAL_ZIP_FOLDER_CHUNK_PEEL=0
 * Caps: FRACTAL_ZIP_FOLDER_CHUNK_MAX_MEMBERS (default 256)
 */

function fractal_zip_folder_chunk_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_CHUNK_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_folder_chunk_max_members(): int {
	$e = getenv('FRACTAL_ZIP_FOLDER_CHUNK_MAX_MEMBERS');
	if ($e !== false && trim((string) $e) !== '' && (int) $e > 0) {
		return max(2, (int) $e);
	}
	return 256;
}

/**
 * @param list<array{name: string, data: string}> $out
 */
function fractal_zip_chunk_iff_append_chunks(
	string $bytes,
	int $off,
	int $end,
	string $prefix,
	array &$out,
	int &$i,
	int $cap,
	int $depth
): void {
	if ($depth > 4 || count($out) >= $cap) {
		return;
	}
	$n = strlen($bytes);
	while ($off + 8 <= $end && count($out) < $cap) {
		$id = substr($bytes, $off, 4);
		$csz = unpack('N', substr($bytes, $off + 4, 4))[1];
		if ($csz < 0 || $off + 8 + $csz > $n) {
			break;
		}
		$data = substr($bytes, $off + 8, $csz);
		$idSafe = preg_replace('/[^A-Za-z0-9._-]/', '_', $id) ?: 'chunk';
		$name = sprintf('%s%03d_%s', $prefix, $i, $idSafe);
		if (($id === 'LIST' || $id === 'FORM' || $id === 'CAT ') && $csz >= 4 && $depth < 4) {
			$listType = substr($data, 0, 4);
			$typeSafe = preg_replace('/[^A-Za-z0-9._-]/', '_', $listType) ?: 'list';
			$out[] = array('name' => $name . '_' . $typeSafe, 'data' => $listType, 'fourcc' => $id);
			$i++;
			$subPrefix = $name . '_' . $typeSafe . '/';
			$subI = 0;
			fractal_zip_chunk_iff_append_chunks($data, 4, $csz, $subPrefix, $out, $subI, $cap, $depth + 1);
		} else {
			$out[] = array('name' => $name, 'data' => $data, 'fourcc' => $id);
			$i++;
		}
		$off += 8 + $csz + ($csz & 1);
	}
}

/**
 * @return list<array{name: string, data: string}>|null
 */
function fractal_zip_chunk_list_iff_form(string $bytes): ?array {
	$n = strlen($bytes);
	if ($n < 12 || substr($bytes, 0, 4) !== 'FORM') {
		return null;
	}
	$size = unpack('N', substr($bytes, 4, 4))[1];
	if ($size < 4 || 8 + $size < 12) {
		return null;
	}
	$formType = substr($bytes, 8, 4);
	$out = array(
		array('name' => '_FORM_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $formType), 'data' => $formType),
	);
	$end = min($n, 8 + $size);
	$cap = fractal_zip_folder_chunk_max_members();
	$i = 0;
	fractal_zip_chunk_iff_append_chunks($bytes, 12, $end, '', $out, $i, $cap, 0);
	return count($out) > 1 ? $out : null;
}

/**
 * @param list<array{name: string, data: string, fourcc?: string}> $out
 */
function fractal_zip_chunk_riff_append_chunks(
	string $bytes,
	int $off,
	int $end,
	string $prefix,
	array &$out,
	int &$i,
	int $cap,
	int $depth
): void {
	if ($depth > 4 || count($out) >= $cap) {
		return;
	}
	$n = strlen($bytes);
	while ($off + 8 <= $end && count($out) < $cap) {
		$id = substr($bytes, $off, 4);
		$csz = unpack('V', substr($bytes, $off + 4, 4))[1];
		if ($csz < 0 || $off + 8 + $csz > $n) {
			break;
		}
		$data = substr($bytes, $off + 8, $csz);
		$idSafe = preg_replace('/[^A-Za-z0-9._-]/', '_', $id) ?: 'chunk';
		$name = sprintf('%s%03d_%s', $prefix, $i, $idSafe);
		// Nested LIST: expose list type + subchunks (INFO metadata, AVI hdrl/movi).
		if ($id === 'LIST' && $csz >= 4 && $depth < 4) {
			$listType = substr($data, 0, 4);
			$typeSafe = preg_replace('/[^A-Za-z0-9._-]/', '_', $listType) ?: 'list';
			$out[] = array('name' => $name . '_' . $typeSafe, 'data' => $listType, 'fourcc' => 'LIST');
			$i++;
			$subPrefix = $name . '_' . $typeSafe . '/';
			$subI = 0;
			fractal_zip_chunk_riff_append_chunks($data, 4, $csz, $subPrefix, $out, $subI, $cap, $depth + 1);
		} else {
			$out[] = array('name' => $name, 'data' => $data, 'fourcc' => $id);
			$i++;
		}
		$off += 8 + $csz + ($csz & 1);
	}
}

/**
 * @return list<array{name: string, data: string}>|null
 */
function fractal_zip_chunk_list_riff(string $bytes): ?array {
	$n = strlen($bytes);
	if ($n < 12) {
		return null;
	}
	$magic = substr($bytes, 0, 4);
	if ($magic !== 'RIFF' && $magic !== 'RF64' && $magic !== 'BW64') {
		return null;
	}
	$size = unpack('V', substr($bytes, 4, 4))[1];
	$formType = substr($bytes, 8, 4);
	$out = array(
		array('name' => '_' . $magic . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $formType), 'data' => $formType),
	);
	$end = ($size > 4) ? min($n, 8 + $size) : $n;
	$cap = fractal_zip_folder_chunk_max_members();
	$i = 0;
	fractal_zip_chunk_riff_append_chunks($bytes, 12, $end, '', $out, $i, $cap, 0);
	return count($out) > 1 ? $out : null;
}

/**
 * @return list<array{name: string, data: string, fourcc?: string}>|null
 */
function fractal_zip_chunk_list_midi_smf(string $bytes): ?array {
	$n = strlen($bytes);
	if ($n < 14 || substr($bytes, 0, 4) !== 'MThd') {
		return null;
	}
	$hdrLen = unpack('N', substr($bytes, 4, 4))[1];
	if ($hdrLen < 6 || 8 + $hdrLen > $n) {
		return null;
	}
	$out = array(
		array('name' => '000_MThd', 'data' => substr($bytes, 8, $hdrLen), 'fourcc' => 'MThd'),
	);
	$off = 8 + $hdrLen;
	$cap = fractal_zip_folder_chunk_max_members();
	$i = 1;
	while ($off + 8 <= $n && count($out) < $cap) {
		$id = substr($bytes, $off, 4);
		if ($id !== 'MTrk') {
			break;
		}
		$tsz = unpack('N', substr($bytes, $off + 4, 4))[1];
		if ($tsz < 0 || $off + 8 + $tsz > $n) {
			break;
		}
		$out[] = array(
			'name' => sprintf('%03d_MTrk', $i),
			'data' => substr($bytes, $off + 8, $tsz),
			'fourcc' => 'MTrk',
		);
		$i++;
		$off += 8 + $tsz;
	}
	return count($out) > 1 ? $out : null;
}

/**
 * @return list<array{name: string, data: string}>|null
 */
function fractal_zip_chunk_list_members(string $bytes): ?array {
	if ($bytes === '') {
		return null;
	}
	$m = fractal_zip_chunk_list_midi_smf($bytes);
	if ($m !== null) {
		return $m;
	}
	$m = fractal_zip_chunk_list_riff($bytes);
	if ($m !== null) {
		return $m;
	}
	return fractal_zip_chunk_list_iff_form($bytes);
}

/**
 * @return list<array{name: string, data: string}>|null
 */
function fractal_zip_chunk_list_icns(string $bytes): ?array {
	$n = strlen($bytes);
	if ($n < 16 || substr($bytes, 0, 4) !== 'icns') {
		return null;
	}
	$total = unpack('N', substr($bytes, 4, 4))[1];
	if ($total !== $n) {
		// Some writers leave total as file size; require exact match for bit-exact rebuild.
		return null;
	}
	$out = array();
	$o = 8;
	$i = 0;
	$cap = fractal_zip_folder_chunk_max_members();
	while ($o + 8 <= $n && count($out) < $cap) {
		$type = substr($bytes, $o, 4);
		$sz = unpack('N', substr($bytes, $o + 4, 4))[1];
		if ($sz < 8 || $o + $sz > $n) {
			return null;
		}
		$safe = preg_replace('/[^A-Za-z0-9._-]+/', '_', $type) ?: ('t' . $i);
		$out[] = array(
			'name' => sprintf('%02d_%s', $i, $safe),
			'data' => substr($bytes, $o, $sz),
		);
		$o += $sz;
		$i++;
	}
	if ($o !== $n || count($out) < 2) {
		return null;
	}
	return $out;
}

/**
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_chunk(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_chunk_peel_enabled() || strlen($diskBytes) < 12) {
		return false;
	}

	// Apple Icon Image (.icns): type/size icon entries; shared masks across a family.
	// Floor keeps mid-size icon sets on PASSTHROUGH (PeaZip ~10–30KiB lost slightly to CLASSIC).
	if (substr($diskBytes, 0, 4) === 'icns' && strlen($diskBytes) >= 65536) {
		$list = fractal_zip_chunk_list_icns($diskBytes);
		if ($list !== null && count($list) >= 3) {
			$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
			if (is_readable($classic)) {
				require_once $classic;
			}
			if (function_exists('fractal_zip_classic_rebuild_archive')
				&& defined('FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC')
				&& function_exists('fractal_zip_folder_register_shared_payload')) {
				$names = array();
				$payloads = array();
				$addedNew = array();
				foreach ($list as $m) {
					$name = (string) ($m['name'] ?? '');
					$data = (string) ($m['data'] ?? '');
					if ($name === '') {
						continue;
					}
					$legacy = rtrim($diskPath, '/') . '/' . $name;
					$key = fractal_zip_folder_register_shared_payload($members, $data, $name, $legacy, $addedNew);
					$names[] = function_exists('fractal_zip_folder_fzhr_share_name')
						? fractal_zip_folder_fzhr_share_name($key, $name)
						: $key;
					$payloads[] = array('name' => $name, 'data' => $data);
				}
				if (count($names) >= 3) {
					$re = fractal_zip_classic_rebuild_archive('icns', '', $payloads);
					if ($re !== null && $re === $diskBytes) {
						$restore[$diskPath] = array(
							'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
							'format' => 'icns',
							'meta' => '',
							'member_names' => $names,
						);
						return true;
					}
					foreach ($addedNew as $nm) {
						unset($members[$nm]);
					}
				}
			}
		}
	}

	$list = fractal_zip_chunk_list_members($diskBytes);
	if ($list === null || count($list) < 2) {
		return false;
	}
	$names = array();
	$fzhrByIdx = array();
	$legacyByIdx = array();
	$addedNew = array();
	$useShare = function_exists('fractal_zip_folder_register_shared_payload');
	foreach ($list as $idx => $m) {
		$name = str_replace('\\', '/', (string) ($m['name'] ?? ''));
		$data = (string) ($m['data'] ?? '');
		if ($name === '' || str_contains($name, '..')) {
			continue;
		}
		$legacy = rtrim($diskPath, '/') . '/' . ltrim($name, '/');
		$legacyByIdx[$idx] = $legacy;
		if ($useShare) {
			$key = fractal_zip_folder_register_shared_payload($members, $data, $name, $legacy, $addedNew);
			$fzhr = function_exists('fractal_zip_folder_fzhr_share_name')
				? fractal_zip_folder_fzhr_share_name($key, $name)
				: $key;
		} else {
			$members[$legacy] = $data;
			$addedNew[] = $legacy;
			$fzhr = $legacy;
		}
		$fzhrByIdx[$idx] = $fzhr;
		$names[] = $fzhr;
	}
	if (count($names) < 2) {
		foreach ($addedNew as $k) {
			unset($members[$k]);
		}
		return false;
	}

	// Prefer CLASSIC rebuild (midi / flat RIFF / flat FORM) to avoid VERBATIM tax.
	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if (is_readable($classic)) {
		require_once $classic;
	}
	if (function_exists('fractal_zip_classic_rebuild_archive')) {
		$first = (string) ($list[0]['name'] ?? '');
		if (str_contains($first, 'MThd') || (($list[0]['fourcc'] ?? '') === 'MThd')) {
			$payloads = array();
			$midiNames = array();
			foreach ($list as $idx => $m) {
				if (!isset($fzhrByIdx[$idx])) {
					continue;
				}
				$payloads[] = array(
					'name' => (string) ($m['name'] ?? ''),
					'data' => (string) ($m['data'] ?? ''),
				);
				$midiNames[] = $fzhrByIdx[$idx];
			}
			$rebuilt = fractal_zip_classic_rebuild_archive('midi', '', $payloads);
			if ($rebuilt !== null && $rebuilt === $diskBytes) {
				$restore[$diskPath] = array(
					'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
					'format' => 'midi',
					'meta' => '',
					'member_names' => $midiNames,
				);
				return true;
			}
		}
		$magic4 = substr($diskBytes, 0, 4);
		if (in_array($magic4, array('RIFF', 'RF64', 'BW64', 'FORM'), true)) {
			$form = substr($diskBytes, 8, 4);
			$fmt = ($magic4 === 'FORM') ? 'iff' : 'riff';
			$chunkPayloads = array();
			$classicNames = array();
			$hexIds = '';
			$nested = false;
			$payloadBytes = 0;
			$plan = array(); // DFS: ['C', fcc] leaf or ['L', listType, nChildren]
			$stack = array(); // remaining child slots for open LIST nodes
			foreach ($list as $idx => $m) {
				$nm = (string) ($m['name'] ?? '');
				if ($idx === 0 && str_starts_with($nm, '_')) {
					$form = (string) ($m['data'] ?? $form);
					if (isset($legacyByIdx[$idx])) {
						unset($members[$legacyByIdx[$idx]]);
					}
					continue;
				}
				if (!isset($fzhrByIdx[$idx])) {
					continue;
				}
				$fcc = (string) ($m['fourcc'] ?? '');
				$data = (string) ($m['data'] ?? '');
				$depth = substr_count($nm, '/');
				while (count($stack) > $depth) {
					array_pop($stack);
				}
				if ($fcc === 'LIST' || $fcc === 'FORM' || $fcc === 'CAT ') {
					$nested = true;
					$listType = (strlen($data) === 4) ? $data : substr($data . '    ', 0, 4);
					if ($stack !== array()) {
						$parent = $stack[count($stack) - 1];
						$plan[$parent][3] = (int) $plan[$parent][3] + 1;
					}
					// [L, containerFourcc, listType, childCount]
					$plan[] = array('L', $fcc, $listType, 0);
					$stack[] = count($plan) - 1;
					// LIST type token is a payload so nest1 rebuild stays aligned 1:1 with plan.
					$chunkPayloads[] = array('name' => $nm, 'data' => $listType);
					$classicNames[] = $fzhrByIdx[$idx];
					$hexIds .= bin2hex($fcc);
					continue;
				}
				if (strlen($fcc) !== 4) {
					$chunkPayloads = array();
					break;
				}
				if ($depth > 0) {
					$nested = true;
				}
				$plan[] = array('C', $fcc);
				if ($stack !== array()) {
					$parent = $stack[count($stack) - 1];
					$plan[$parent][3] = (int) $plan[$parent][3] + 1;
				}
				$hexIds .= bin2hex($fcc);
				$chunkPayloads[] = array('name' => $nm, 'data' => $data);
				$classicNames[] = $fzhrByIdx[$idx];
				$payloadBytes += strlen($data);
			}
			$metaCost = strlen($magic4) + 1 + 4 + 1 + strlen($hexIds);
			$skipClassic = false;
			if ($magic4 === 'FORM') {
				$skipClassic = ($metaCost > $payloadBytes && $payloadBytes < 4096)
					|| ($payloadBytes < 64 && strlen($diskBytes) < 256);
			}
			if ($chunkPayloads !== [] && strlen($form) === 4 && !$skipClassic) {
				if ($nested) {
					$planBin = '';
					foreach ($plan as $node) {
						if (($node[0] ?? '') === 'L') {
							$planBin .= 'L' . (string) $node[1] . (string) $node[2] . pack('n', (int) $node[3]);
						} else {
							$planBin .= 'C' . (string) $node[1];
						}
					}
					$meta = $magic4 . ':' . $form . ':nest1:' . bin2hex($planBin);
				} else {
					$meta = $magic4 . ':' . $form . ':' . $hexIds;
				}
				$rebuilt = fractal_zip_classic_rebuild_archive($fmt, $meta, $chunkPayloads);
				if ($rebuilt !== null && $rebuilt === $diskBytes) {
					$restore[$diskPath] = array(
						'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
						'format' => $fmt,
						'meta' => $meta,
						'member_names' => $classicNames,
					);
					return true;
				}
			}
		}
	}

	$restore[$diskPath] = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM,
		'verbatim' => $diskBytes,
		'member_names' => $names,
	);
	return true;
}
