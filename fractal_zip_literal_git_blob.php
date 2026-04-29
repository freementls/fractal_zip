<?php
declare(strict_types=1);

/**
 * Git loose objects: header `{type} {decimal}\0` + body (FZB literal mode 23), for type
 * `blob` | `tree` | `commit` | `tag` only. Rebuild is byte-identical; FZB tag = type name, or
 * empty/`blob` (legacy) for payload blob objects.
 * Decimal size: no leading zeros (except `0` for empty body).
 */

/** @return list<string> */
function fractal_zip_literal_git_loose_object_types(): array {
	return array('blob', 'tree', 'commit', 'tag');
}

function fractal_zip_literal_git_loose_type_ok(string $t): bool {
	return in_array($t, fractal_zip_literal_git_loose_object_types(), true);
}

function fractal_zip_literal_semantic_git_loose_blob_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_GIT_BLOB');
	if($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_path_looks_git_loose_object_semantic(string $relPath): bool {
	$low = strtolower(str_replace('\\', '/', $relPath));
	if(str_contains($low, '/.git/objects/')) {
		return true;
	}
	$base = basename($low);
	return (bool) preg_match('/^[0-9a-f]{2}\/[0-9a-f]{38}$/i', $low)
		|| (bool) preg_match('/^[0-9a-f]{40}$/i', (string) $base);
}

function fractal_zip_literal_path_for_raster_pac_after_git_loose_object_strip(string $relPath): string {
	$rel = str_replace('\\', '/', (string) $relPath);
	$base = basename($rel);
	if((bool) preg_match('/^[0-9a-f]{40}$/i', (string) $base)) {
		$d = dirname($rel);
		if($d === '' || $d === '.') {
			return 'object';
		}
		return $d . '/object';
	}
	$u = (string) preg_replace('#/objects/[0-9a-f/]{2,}[^/]*$#i', '/object', $rel);
	if($u !== $rel) {
		return $u;
	}
	return rtrim($rel, '/') . '/git-object-inner';
}

/**
 * @return array{0: string, 1: string}|null [payload, type]
 */
function fractal_zip_literal_git_loose_parse_object(string $compressed, int $n): ?array {
	if($n < 6) {
		return null;
	}
	$head = $n < 64 ? $compressed : substr($compressed, 0, 64);
	$sp = strpos($head, ' ');
	if($sp === false || $sp < 3) {
		return null;
	}
	$ot = substr($compressed, 0, $sp);
	if(!fractal_zip_literal_git_loose_type_ok($ot)) {
		return null;
	}
	$p = $sp + 1;
	$nd = 0;
	$q = $p;
	while($q < $n) {
		$c = $compressed[$q];
		if($c < '0' || $c > '9') {
			break;
		}
		$nd++;
		$q++;
	}
	if($nd < 1) {
		return null;
	}
	$ds = substr($compressed, $p, $nd);
	if($ds[0] === '0' && $nd > 1) {
		return null;
	}
	if($nd > 18) {
		return null;
	}
	if((string) (int) $ds !== $ds) {
		return null;
	}
	$sz = (int) $ds;
	if($q >= $n || $compressed[$q] !== "\0") {
		return null;
	}
	$dataOff = $q + 1;
	$end = $dataOff + $sz;
	if($end !== $n) {
		return null;
	}
	$payload = $dataOff >= $n ? '' : substr($compressed, $dataOff);
	return array((string) $payload, (string) $ot);
}

/**
 * @param string $fzbTypeTag empty, `blob`, or `tree` / `commit` / `tag`
 * @return string|null
 */
function fractal_zip_literal_pac_rebuild_loose_git_object_typed(string $payload, string $fzbTypeTag): ?string {
	$t = $fzbTypeTag;
	if($t === '' || $t === 'blob') {
		$ot = 'blob';
	} else {
		if(!fractal_zip_literal_git_loose_type_ok($t)) {
			return null;
		}
		$ot = $t;
	}
	$s = (string) strlen($payload);
	return $ot . " " . $s . "\0" . $payload;
}

/**
 * Same as `fractal_zip_literal_pac_rebuild_loose_git_object_typed` (entry point name for decode / compat).
 */
function fractal_zip_literal_pac_rebuild_git_loose_object(string $payload, string $fzbTypeTag): ?string {
	return fractal_zip_literal_pac_rebuild_loose_git_object_typed($payload, $fzbTypeTag);
}

function fractal_zip_literal_git_object_loose_sniffs(string $work): bool {
	if(!fractal_zip_literal_semantic_git_loose_blob_enabled()) {
		return false;
	}
	$n = strlen($work);
	return fractal_zip_literal_git_loose_parse_object($work, $n) !== null;
}

function fractal_zip_literal_git_blob_loose_sniffs(string $work): bool {
	return fractal_zip_literal_git_object_loose_sniffs($work);
}

/**
 * @return array{0: string, 1: string}|null [payload, fzbTypeTag] tag empty = blob, else tree|commit|tag
 */
function fractal_zip_literal_pac_peel_git_loose_object(string $compressed): ?array {
	if(!fractal_zip_literal_semantic_git_loose_blob_enabled()) {
		return null;
	}
	if(!function_exists('fractal_zip_literal_pac_payload_within_limit') || !fractal_zip_literal_pac_payload_within_limit(strlen($compressed))) {
		return null;
	}
	$n = strlen($compressed);
	$got = fractal_zip_literal_git_loose_parse_object($compressed, $n);
	if($got === null) {
		return null;
	}
	$payload = (string) $got[0];
	$ot = (string) $got[1];
	$fzbTag = ($ot === 'blob') ? '' : $ot;
	$re = fractal_zip_literal_pac_rebuild_loose_git_object_typed($payload, $fzbTag);
	if($re === null || $re !== $compressed) {
		return null;
	}
	return array($payload, $fzbTag);
}
