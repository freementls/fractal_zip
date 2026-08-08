<?php

declare(strict_types=1);

/**
 * Lossless subset wikitext ↔ HTML for enwik8 preserve-text (2006-era syntax).
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

const FRACTAL_ZIP_WIKI_HTML_PROTECT = "\x1F\x57\x48\x50";

/** @return list<string> */
function fractal_zip_wiki_html_extract_wikitables(string $text): array
{
	$out = array();
	$len = strlen($text);
	$i = 0;
	while ($i + 1 < $len) {
		if ($text[$i] !== '{' || $text[$i + 1] !== '|') {
			$i++;
			continue;
		}
		$depth = 0;
		$start = $i;
		for (; $i < $len; $i++) {
			if ($i + 1 < $len && $text[$i] === '{' && $text[$i + 1] === '|') {
				$depth++;
				$i++;
			} elseif ($i + 1 < $len && $text[$i] === '|' && $text[$i + 1] === '}') {
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

/** @return list<string> */
function fractal_zip_wiki_html_extract_templates(string $text): array
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

/** @return list<string> */
function fractal_zip_wiki_html_protect_regions(string $text): array
{
	$regions = fractal_zip_wiki_html_extract_templates($text);
	foreach (fractal_zip_wiki_html_extract_wikitables($text) as $table) {
		$regions[] = $table;
	}
	$patterns = array(
		'/<nowiki>[\s\S]*?<\/nowiki>/i',
		'/<!--[\s\S]*?-->/',
		'/<wiki-t>[\s\S]*?<\/wiki-t>/',
		'/<(?:ref|sup|sub|code|math)[^>]*>[\s\S]*?<\/(?:ref|sup|sub|code|math)>/i',
		'/&lt;(?:nowiki|ref|sup|sub|code|math)[^&]*(?:\/?)&gt;/',
		'/<(?:ref|br|hr)[^>]*\/>/i',
		'/&lt;[a-zA-Z][^&]*(?:\/?)&gt;/',
	);
	foreach ($patterns as $re) {
		if (preg_match_all($re, $text, $m) < 1) {
			continue;
		}
		foreach ($m[0] as $hit) {
			$regions[] = (string) $hit;
		}
	}
	usort($regions, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
	return $regions;
}

function fractal_zip_wiki_html_stash(string $text, array &$stash): string
{
	$stash = array();
	$idx = 0;
	foreach (fractal_zip_wiki_html_protect_regions($text) as $region) {
		$key = FRACTAL_ZIP_WIKI_HTML_PROTECT . chr($idx);
		if (str_contains($text, $region) && !isset($stash[$key])) {
			$stash[$key] = $region;
			$text = str_replace($region, $key, $text);
			$idx++;
			if ($idx >= 224) {
				break;
			}
		}
	}
	return $text;
}

function fractal_zip_wiki_html_unstash(string $text, array $stash): string
{
	foreach ($stash as $key => $region) {
		$text = str_replace($key, $region, $text);
	}
	return $text;
}

function fractal_zip_wiki_html_wrap_templates(string $text): string
{
	$out = '';
	$len = strlen($text);
	$i = 0;
	while ($i < $len) {
		if ($i + 1 < $len && $text[$i] === '{' && $text[$i + 1] === '{') {
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
			$tpl = substr($text, $start, $i - $start + 1);
			$out .= '<wiki-t>' . $tpl . '</wiki-t>';
			$i++;
			continue;
		}
		$out .= $text[$i];
		$i++;
	}
	return $out;
}

function fractal_zip_wiki_html_unwrap_templates(string $text): string
{
	return (string) preg_replace('/<wiki-t>([\s\S]*?)<\/wiki-t>/', '$1', $text);
}

function fractal_zip_wiki_html_line_prefix_list(string $line): ?array
{
	if (preg_match('/^([\*#:;]+)(.*)$/', $line, $m) !== 1) {
		return null;
	}
	return array('marks' => (string) $m[1], 'body' => (string) $m[2]);
}

function fractal_zip_wiki_html_list_tag(string $mark): string
{
	return match ($mark) {
		'*' => 'ul',
		'#' => 'ol',
		':' => 'dl',
		';' => 'dl',
		default => 'ul',
	};
}

function fractal_zip_wiki_html_encode_lines(string $text): string
{
	$lines = explode("\n", $text);
	$out = array();
	$stack = array();
	foreach ($lines as $line) {
		if (preg_match('/^(=+)([^=].*?)\1(\s*)$/', $line, $hm) === 1) {
			while ($stack !== array()) {
				$out[] = array_pop($stack);
			}
			$level = min(6, max(1, strlen($hm[1]) - 1));
			$out[] = '<h' . $level . '>' . fractal_zip_wiki_html_encode_inline((string) $hm[2]) . '</h' . $level . '>'
				. (string) $hm[3];
			continue;
		}
		while ($stack !== array()) {
			$out[] = array_pop($stack);
		}
		$out[] = fractal_zip_wiki_html_encode_inline($line);
	}
	while ($stack !== array()) {
		$out[] = array_pop($stack);
	}
	return implode("\n", $out);
}

function fractal_zip_wiki_html_encode_inline(string $text): string
{
	if ($text === '') {
		return '';
	}
	$pos = 0;
	$len = strlen($text);
	$out = '';
	while ($pos < $len) {
		if ($pos + 1 < $len && $text[$pos] === '[' && $text[$pos + 1] === '[') {
			$end = strpos($text, ']]', $pos + 2);
			if ($end === false) {
				$out .= $text[$pos++];
				continue;
			}
			$inner = substr($text, $pos + 2, $end - $pos - 2);
			$pipe = strpos($inner, '|');
			if ($pipe !== false) {
				$title = substr($inner, 0, $pipe);
				$display = substr($inner, $pipe + 1);
				$out .= '<a href="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" data-fzwp="1">'
					. fractal_zip_wiki_html_encode_inline($display) . '</a>';
			} else {
				$out .= '<a href="' . htmlspecialchars($inner, ENT_QUOTES, 'UTF-8') . '" data-fzwl="1">'
					. fractal_zip_wiki_html_encode_inline($inner) . '</a>';
			}
			$pos = $end + 2;
			continue;
		}
		if ($text[$pos] === '[' && ($pos + 1 >= $len || $text[$pos + 1] !== '[')) {
			$end = strpos($text, ']', $pos + 1);
			if ($end !== false) {
				$inner = substr($text, $pos + 1, $end - $pos - 1);
				$sp = strpos($inner, ' ');
				if ($sp !== false) {
					$url = substr($inner, 0, $sp);
					if (preg_match('#^(https?://|ftp://|mailto:|//)#i', $url) === 1) {
						$label = substr($inner, $sp + 1);
						$out .= '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">'
							. fractal_zip_wiki_html_encode_inline($label) . '</a>';
						$pos = $end + 1;
						continue;
					}
				}
			}
		}
		if ($pos + 4 < $len && substr($text, $pos, 5) === "'''''") {
			$end = strpos($text, "'''''", $pos + 5);
			if ($end !== false) {
				$out .= '<b><i>' . fractal_zip_wiki_html_encode_inline(substr($text, $pos + 5, $end - $pos - 5)) . '</i></b>';
				$pos = $end + 5;
				continue;
			}
		}
		if ($pos + 2 < $len && substr($text, $pos, 3) === "'''") {
			$end = strpos($text, "'''", $pos + 3);
			if ($end !== false) {
				$out .= '<b>' . fractal_zip_wiki_html_encode_inline(substr($text, $pos + 3, $end - $pos - 3)) . '</b>';
				$pos = $end + 3;
				continue;
			}
		}
		if ($pos + 1 < $len && substr($text, $pos, 2) === "''") {
			$end = strpos($text, "''", $pos + 2);
			if ($end !== false) {
				$out .= '<i>' . fractal_zip_wiki_html_encode_inline(substr($text, $pos + 2, $end - $pos - 2)) . '</i>';
				$pos = $end + 2;
				continue;
			}
		}
		$out .= $text[$pos];
		$pos++;
	}
	return $out;
}

function fractal_zip_wiki_html_decode_inline(string $text): string
{
	$prev = '';
	while ($prev !== $text) {
		$prev = $text;
		$text = (string) preg_replace('/<b><i>([\s\S]*?)<\/i><\/b>/', "'''''$1'''''", $text);
		$text = (string) preg_replace_callback(
			'/<a\s+href="([^"]*)"(?:\s+data-fzwp="1"|\s+data-fzwl="1")?\s*>([\s\S]*?)<\/a>/',
			static function (array $m): string {
				$href = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
				$label = fractal_zip_wiki_html_decode_inline($m[2]);
				$piped = str_contains($m[0], 'data-fzwp="1"');
				$wikiLink = str_contains($m[0], 'data-fzwl="1"') || str_contains($m[0], 'data-fzwp="1"');
				if (preg_match('#^(https?://|ftp://|mailto:|//)#i', $href) === 1 && !$wikiLink) {
					return '[' . $href . ' ' . $label . ']';
				}
				if (!$piped && ($label === $href || $label === str_replace('_', ' ', $href))) {
					return '[[' . $href . ']]';
				}
				return '[[' . $href . '|' . $label . ']]';
			},
			$text
		);
		$text = (string) preg_replace('/<b>([\s\S]*?)<\/b>/', "'''$1'''", $text);
		$text = (string) preg_replace('/<i>([\s\S]*?)<\/i>/', "''$1''", $text);
	}
	return $text;
}

function fractal_zip_wiki_html_decode_blocks(string $text): string
{
	$text = (string) preg_replace_callback('/<h([1-6])>([\s\S]*?)<\/h\1>/', static function (array $m): string {
		$level = (int) $m[1] + 1;
		return str_repeat('=', $level) . fractal_zip_wiki_html_decode_inline($m[2]) . str_repeat('=', $level);
	}, $text);
	return fractal_zip_wiki_html_decode_inline($text);
}

function fractal_zip_wiki_html_encode(string $wikitext): string
{
	if ($wikitext === '') {
		return '';
	}
	$stash = array();
	$text = fractal_zip_wiki_html_stash($wikitext, $stash);
	$text = fractal_zip_wiki_html_encode_lines($text);
	return fractal_zip_wiki_html_unstash($text, $stash);
}

function fractal_zip_wiki_html_decode(string $html): string
{
	if ($html === '') {
		return '';
	}
	$stash = array();
	$text = fractal_zip_wiki_html_stash($html, $stash);
	$text = fractal_zip_wiki_html_decode_blocks($text);
	return fractal_zip_wiki_html_unstash($text, $stash);
}
