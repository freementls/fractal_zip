#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Syllable / consonant-skeleton experiments on enwik8 preserve-text ONLY (markup split out).
 *
 * Usage:
 *   php benchmarks/bench_enwik8_syllable_lab.php [--pages=384] [--quick]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_syllable_codec.php';
require_once $repo . '/fractal_zip_enwik_text_codec.php';

$pages = 384;
$quick = in_array('--quick', $argv, true);
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(8, (int) substr($arg, 8));
	}
}
if ($quick) {
	$pages = min($pages, 64);
}

$src = $repo . '/test_files109/enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}

echo "bench_enwik8_syllable_lab | @{$pages}p preserve-text only (markup excluded)\n\n";

foreach (array('daddy', 'upper', 'camel') as $w) {
	$syls = fractal_zip_enwik_syllabify_word($w);
	echo sprintf("  syllabify %-8s → %s\n", $w, implode('-', $syls));
}
echo "\n";

$textBuf = '';
$markupBuf = '';
$fullPageBuf = '';
$pageTexts = array();
$n = min($pages, count($split['pages']));
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$pageXml = substr($blob, (int) $p['start'], (int) $p['len']);
	$fullPageBuf .= $pageXml;
	$parts = fractal_zip_enwik_page_markup_text_split($pageXml);
	$pageTexts[] = (string) $parts['text'];
	$textBuf .= $parts['text'];
	$markupBuf .= $parts['shell'];
}
$consonantModel = fractal_zip_enwik_consonant_hybrid_mine_model_pages($pageTexts, $textBuf);

$schemes = array(
	'raw_text' => static function (string $t): array {
		return array('payload' => $t, 'sidecar' => array(), 'meta' => array());
	},
	'syllable_tokens' => 'fractal_zip_enwik_syllable_token_preprocess',
	'consonant_ec' => 'fractal_zip_enwik_consonant_ec_preprocess',
	'words_id_varint_isp' => static function (string $t): array {
		$enc = fractal_zip_enwik_text_codec_encode('words_id_varint_isp', $t);
		return array('payload' => (string) $enc['payload'], 'sidecar' => $enc['sidecar'], 'meta' => $enc['meta'] ?? array());
	},
	'syllables_id_varint_isp' => 'fractal_zip_enwik_syllables_id_varint_isp_preprocess',
	'consonant_id_varint_isp' => 'fractal_zip_enwik_consonant_id_varint_isp_preprocess',
	'consonant_hybrid' => static function (string $t) use ($consonantModel): array {
		return fractal_zip_enwik_consonant_hybrid_preprocess($t, array(
			'consonant_model' => $consonantModel,
			'frozen' => true,
		));
	},
	'stat_syllable_isp' => static function (string $t) use ($textBuf): array {
		$vocab = fractal_zip_enwik_stat_syllable_isp_mine_vocab($textBuf);
		return fractal_zip_text_stat_syllable_isp_preprocess($t, array('vocab' => $vocab, 'frozen' => true));
	},
);
$heavySchemes = array('consonant_ec', 'syllables_id_varint_isp', 'consonant_id_varint_isp', 'words_id_varint_isp', 'consonant_hybrid', 'stat_syllable_isp');

$undo = array(
	'raw_text' => static fn (string $p, array $s): string => $p,
	'syllable_tokens' => 'fractal_zip_enwik_syllable_token_undo',
	'consonant_ec' => static function (string $p, array $s) use ($textBuf): string {
		return fractal_zip_enwik_consonant_ec_undo($p, $s, $textBuf);
	},
	'words_id_varint_isp' => static function (string $p, array $s): string {
		return fractal_zip_enwik_text_codec_decode('words_id_varint_isp', $p, $s);
	},
	'syllables_id_varint_isp' => 'fractal_zip_enwik_syllables_id_varint_isp_undo',
	'consonant_id_varint_isp' => 'fractal_zip_enwik_consonant_id_varint_isp_undo',
	'consonant_hybrid' => static function (string $p, array $s) use ($consonantModel): string {
		$s = fractal_zip_enwik_merge_consonant_hybrid_sidecar(
			$s,
			fractal_zip_enwik_consonant_hybrid_shared_meta_from_model($consonantModel)
		);
		return fractal_zip_enwik_consonant_hybrid_undo($p, $s);
	},
	'stat_syllable_isp' => static function (string $p, array $s) use ($textBuf): string {
		$vocab = fractal_zip_enwik_stat_syllable_isp_mine_vocab($textBuf);
		return fractal_zip_text_stat_syllable_isp_undo($p, array_merge($s, array('vocab' => $vocab)));
	},
);

$inputs = array(
	'preserve_text' => $textBuf,
	'markup_shell' => $markupBuf,
	'full_page_xml_WRONG' => $fullPageBuf,
);

printf("%-22s %-18s %10s %10s %8s %s\n", 'input', 'scheme', 'payload_B', 'gzip1_B', 'RT', 'notes');
printf("%-22s %-18s %10s %10s %8s %s\n", '', '', '', '', '', '');

$rows = array();
foreach ($inputs as $inputLabel => $inputText) {
	$baseGz = fractal_zip_enwik_text_measure_payload($inputText)['gzip1_bytes'] ?? 0;
	foreach ($schemes as $name => $fn) {
		if ($inputLabel !== 'preserve_text' && !in_array($name, array('raw_text', 'consonant_ec'), true)) {
			continue;
		}
		if ($inputLabel === 'preserve_text' && in_array($name, $heavySchemes, true) && $n > 128) {
			if (!in_array($name, array('words_id_varint_isp'), true)) {
				continue;
			}
		}
		try {
			$r = is_string($fn) ? $fn($inputText) : $fn($inputText);
			$payload = (string) $r['payload'];
			$sidecar = is_array($r['sidecar'] ?? null) ? $r['sidecar'] : array();
			$meta = is_array($r['meta'] ?? null) ? $r['meta'] : array();
			$undoFn = $undo[$name];
			$restored = is_string($undoFn) ? $undoFn($payload, $sidecar) : $undoFn($payload, $sidecar);
			$rt = $restored === $inputText ? 'ok' : ($name === 'consonant_ec' || $name === 'consonant_id_varint_isp' ? 'lossy' : 'FAIL');
			$meas = fractal_zip_enwik_text_measure_payload($payload);
			$gz = (int) ($meas['gzip1_bytes'] ?? 0);
			$ratio = $baseGz > 0 ? round(100.0 * $gz / $baseGz, 1) : null;
			$note = '';
			if ($name === 'consonant_ec' && isset($meta['ambiguous_words'])) {
				$note = 'ambig=' . $meta['ambiguous_words'] . ' sk=' . ($meta['skeleton_bytes'] ?? '?');
			}
			if ($name === 'consonant_hybrid' && isset($meta['literal_words'])) {
				$note = 'lit=' . $meta['literal_words'] . ' sk=' . ($meta['encoded_skeletons'] ?? '?');
				$sharedMeta = fractal_zip_enwik_consonant_hybrid_shared_meta_from_model($consonantModel);
				$amort = fractal_zip_enwik_consonant_hybrid_meta_wire_bytes($sharedMeta, $n);
				$note .= ' sk_rows=' . count($sharedMeta['skeleton_unique'] ?? array());
				$note .= ' meta_amort=' . number_format($amort);
			}
			if ($name === 'stat_syllable_isp' && isset($meta['vocab_size'])) {
				$note = 'vocab=' . $meta['vocab_size'];
			}
			if ($name === 'syllable_tokens' && isset($meta['syllables'], $meta['words'])) {
				$note = 'syl/w=' . round($meta['syllables'] / max(1, $meta['words']), 2);
			}
			if ($inputLabel === 'full_page_xml_WRONG') {
				$note = 'do not use on XML';
			}
			printf(
				"%-22s %-18s %10s %10s %8s %s\n",
				$inputLabel,
				$name,
				number_format(strlen($payload)),
				number_format($gz),
				$rt,
				$note !== '' ? $note : ($ratio !== null ? "gz%={$ratio}" : '')
			);
			$rows[] = array(
				'input' => $inputLabel,
				'scheme' => $name,
				'payload_bytes' => strlen($payload),
				'gzip1_bytes' => $gz,
				'roundtrip' => $rt,
				'meta' => $meta,
			);
		} catch (Throwable $e) {
			printf("%-22s %-18s %10s %10s %8s %s\n", $inputLabel, $name, '-', '-', 'ERR', $e->getMessage());
		}
	}
}

$outJson = $repo . '/benchmarks/.enwik8_syllable_lab_' . $n . 'p.json';
file_put_contents($outJson, json_encode(array(
	'generated' => date('c'),
	'pages' => $n,
	'rows' => $rows,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "\n→ {$outJson}\n";
