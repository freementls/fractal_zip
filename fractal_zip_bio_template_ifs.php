<?php

declare(strict_types=1);

/**
 * Template/macro IFS: mine repeated enwik macro spans, replace with macro-id refs + residuals.
 *
 * Lossless v1 — sidecar holds template table; payload is varint macro ids + literal gaps.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

const FRACTAL_ZIP_BIO_TEMPLATE_IFS_MAGIC = "FZTI\x01";

/**
 * @return list<string>
 */
function fractal_zip_bio_template_ifs_mine(string $text, array $opts = array()): array
{
	$minLen = (int) ($opts['min_len'] ?? 12);
	$maxLen = (int) ($opts['max_len'] ?? 96);
	$minCount = (int) ($opts['min_count'] ?? 3);
	$maxEntries = (int) ($opts['max_entries'] ?? 48);
	$patterns = array(
		'/\{\{[^}]+\}\}/',
		'/\[\[[^\]]+\]\]/',
		'/<ref[^>]*\/>/',
		'/<\/ref>/',
		'/\|thumb\|/',
		'/\|right\|/',
		'/\|left\|/',
	);
	$counts = array();
	foreach ($patterns as $re) {
		if (preg_match_all($re, $text, $m) < 1) {
			continue;
		}
		foreach ($m[0] as $hit) {
			$hit = (string) $hit;
			$len = strlen($hit);
			if ($len < $minLen || $len > $maxLen) {
				continue;
			}
			if (!isset($counts[$hit])) {
				$counts[$hit] = 0;
			}
			$counts[$hit]++;
		}
	}
	$rows = array();
	foreach ($counts as $pat => $c) {
		if ($c >= $minCount) {
			$rows[] = array('pat' => $pat, 'c' => $c);
		}
	}
	usort($rows, static fn (array $a, array $b): int => ($b['c'] <=> $a['c']) ?: (strlen($b['pat']) <=> strlen($a['pat'])));
	$out = array();
	foreach (array_slice($rows, 0, $maxEntries) as $row) {
		$out[] = (string) $row['pat'];
	}
	return $out;
}

/**
 * @return array{payload:string, sidecar:string, stats:array<string,mixed>}
 */
function fractal_zip_bio_template_ifs_build(string $text, array $opts = array()): array
{
	$templates = $opts['templates'] ?? null;
	if (!is_array($templates)) {
		$templates = fractal_zip_bio_template_ifs_mine($text, $opts);
	}
	usort($templates, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
	$payload = FRACTAL_ZIP_BIO_TEMPLATE_IFS_MAGIC;
	$pos = 0;
	$len = strlen($text);
	$macroHits = 0;
	while ($pos < $len) {
		$bestId = -1;
		$bestLen = 0;
		foreach ($templates as $id => $tpl) {
			$tl = strlen($tpl);
			if ($tl <= $bestLen || $pos + $tl > $len) {
				continue;
			}
			if (substr($text, $pos, $tl) === $tpl) {
				$bestId = (int) $id;
				$bestLen = $tl;
			}
		}
		if ($bestId >= 0) {
			$payload .= chr(1) . fractal_zip_enwik_encode_varint_u32($bestId);
			$pos += $bestLen;
			$macroHits++;
			continue;
		}
		$run = '';
		while ($pos < $len) {
			$matched = false;
			foreach ($templates as $tpl) {
				$tl = strlen($tpl);
				if ($tl > 0 && $pos + $tl <= $len && substr($text, $pos, $tl) === $tpl) {
					$matched = true;
					break;
				}
			}
			if ($matched) {
				break;
			}
			$run .= $text[$pos];
			$pos++;
		}
		$payload .= chr(0) . fractal_zip_enwik_encode_varint_u32(strlen($run)) . $run;
	}
	$sidecar = json_encode(array('templates' => $templates), JSON_UNESCAPED_UNICODE);
	if ($sidecar === false) {
		throw new RuntimeException('template_ifs sidecar json failed');
	}
	return array(
		'payload' => $payload,
		'sidecar' => $sidecar,
		'stats' => array(
			'templates' => count($templates),
			'macro_hits' => $macroHits,
			'raw_len' => strlen($text),
		),
	);
}

/**
 * Extract balanced {{...}} spans including nested templates.
 *
 * @return list<string>
 */
function fractal_zip_bio_template_ifs_extract_balanced(string $text): array
{
	$out = array();
	$len = strlen($text);
	$i = 0;
	while ($i + 1 < $len) {
		if ($text[$i] !== '{' || $text[$i + 1] !== '{') {
			$i++;
			continue;
		}
		$depth = 0;
		$start = $i;
		for (; $i < $len; $i++) {
			if ($i + 1 < $len && $text[$i] === '{' && $text[$i + 1] === '{') {
				$depth++;
				$i++;
			} elseif ($i + 1 < $len && $text[$i] === '}' && $text[$i + 1] === '}') {
				$depth--;
				$i++;
				if ($depth <= 0) {
					break;
				}
			}
		}
		$out[] = substr($text, $start, $i - $start + 1);
		$i++;
	}
	return $out;
}

/**
 * @return list<string>
 */
function fractal_zip_bio_template_ifs_v2_mine(string $text, array $opts = array()): array
{
	$minLen = (int) ($opts['min_len'] ?? 12);
	$maxLen = (int) ($opts['max_len'] ?? 512);
	$minCount = (int) ($opts['min_count'] ?? 3);
	$maxEntries = (int) ($opts['max_entries'] ?? 96);
	$counts = array();
	foreach (fractal_zip_bio_template_ifs_extract_balanced($text) as $hit) {
		$len = strlen($hit);
		if ($len < $minLen || $len > $maxLen) {
			continue;
		}
		if (!isset($counts[$hit])) {
			$counts[$hit] = 0;
		}
		$counts[$hit]++;
	}
	if (preg_match_all('/\[\[[^\]]+\]\]/', $text, $m) > 0) {
		foreach ($m[0] as $hit) {
			$hit = (string) $hit;
			$len = strlen($hit);
			if ($len < $minLen || $len > $maxLen) {
				continue;
			}
			if (!isset($counts[$hit])) {
				$counts[$hit] = 0;
			}
			$counts[$hit]++;
		}
	}
	$rows = array();
	foreach ($counts as $pat => $c) {
		if ($c >= $minCount) {
			$rows[] = array('pat' => $pat, 'c' => $c);
		}
	}
	usort($rows, static fn (array $a, array $b): int => ($b['c'] <=> $a['c']) ?: (strlen($b['pat']) <=> strlen($a['pat'])));
	$out = array();
	foreach (array_slice($rows, 0, $maxEntries) as $row) {
		$out[] = (string) $row['pat'];
	}
	return $out;
}

/**
 * @return array{payload:string, sidecar:string, stats:array<string,mixed>}
 */
function fractal_zip_bio_template_ifs_v2_build(string $text, array $opts = array()): array
{
	$templates = $opts['templates'] ?? null;
	if (!is_array($templates)) {
		$templates = fractal_zip_bio_template_ifs_v2_mine($text, $opts);
	}
	usort($templates, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
	$payload = FRACTAL_ZIP_BIO_TEMPLATE_IFS_MAGIC;
	$pos = 0;
	$len = strlen($text);
	$macroHits = 0;
	while ($pos < $len) {
		$bestId = -1;
		$bestLen = 0;
		foreach ($templates as $id => $tpl) {
			$tl = strlen($tpl);
			if ($tl <= $bestLen || $pos + $tl > $len) {
				continue;
			}
			if (substr($text, $pos, $tl) === $tpl) {
				$bestId = (int) $id;
				$bestLen = $tl;
			}
		}
		if ($bestId >= 0) {
			$payload .= chr(1) . fractal_zip_enwik_encode_varint_u32($bestId);
			$pos += $bestLen;
			$macroHits++;
			continue;
		}
		$run = '';
		while ($pos < $len) {
			$matched = false;
			foreach ($templates as $tpl) {
				$tl = strlen($tpl);
				if ($tl > 0 && $pos + $tl <= $len && substr($text, $pos, $tl) === $tpl) {
					$matched = true;
					break;
				}
			}
			if ($matched) {
				break;
			}
			$run .= $text[$pos];
			$pos++;
		}
		$payload .= chr(0) . fractal_zip_enwik_encode_varint_u32(strlen($run)) . $run;
	}
	$sidecar = json_encode(array('templates' => $templates), JSON_UNESCAPED_UNICODE);
	if ($sidecar === false) {
		throw new RuntimeException('template_ifs v2 sidecar json failed');
	}
	return array(
		'payload' => $payload,
		'sidecar' => $sidecar,
		'stats' => array(
			'templates' => $templates,
			'macro_hits' => $macroHits,
			'raw_len' => strlen($text),
		),
	);
}

function fractal_zip_bio_template_ifs_restore(string $payload, string $sidecarJson): string
{
	if (strncmp($payload, FRACTAL_ZIP_BIO_TEMPLATE_IFS_MAGIC, 5) !== 0) {
		throw new RuntimeException('template_ifs bad magic');
	}
	$meta = json_decode($sidecarJson, true);
	if (!is_array($meta) || !isset($meta['templates']) || !is_array($meta['templates'])) {
		throw new RuntimeException('template_ifs sidecar invalid');
	}
	$templates = $meta['templates'];
	$pos = 5;
	$out = '';
	$n = strlen($payload);
	while ($pos < $n) {
		$tag = ord($payload[$pos++]);
		$dv = fractal_zip_enwik_decode_varint_u32($payload, $pos);
		if ($dv === null) {
			throw new RuntimeException('template_ifs varint missing');
		}
		$val = (int) $dv[0];
		$pos = (int) $dv[1];
		if ($tag === 1) {
			if (!isset($templates[$val])) {
				throw new RuntimeException('template_ifs macro id ' . $val);
			}
			$out .= (string) $templates[$val];
		} else {
			if ($pos + $val > $n) {
				throw new RuntimeException('template_ifs literal truncated');
			}
			$out .= substr($payload, $pos, $val);
			$pos += $val;
		}
	}
	return $out;
}
