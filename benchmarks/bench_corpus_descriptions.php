<?php
declare(strict_types=1);
/**
 * Fixed-width (16) human hints for benchmark corpus dirs — same idea as Squash’s dataset id column.
 * Used by run_benchmarks.php table + JSON `short_desc`.
 */

/**
 * @return array<string, string> label => description (≤16 chars before normalize; may be padded)
 */
function bench_corpus_description_map(): array
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$squash = [];
	$pairs = [
		['test_files105', 'alice29.txt'],
		['test_files106', 'asyoulik.txt'],
		['test_files107', 'cp.html'],
		['test_files108', 'dickens'],
		['test_files109', 'enwik8'],
		['test_files110', 'fields.c'],
		['test_files111', 'fireworks.jpeg'],
		['test_files112', 'geo.protodata'],
		['test_files113', 'grammar.lsp'],
		['test_files114', 'kennedy.xls'],
		['test_files115', 'lcet10.txt'],
		['test_files116', 'mozilla'],
		['test_files117', 'mr'],
		['test_files118', 'nci'],
		['test_files119', 'ooffice'],
		['test_files120', 'osdb'],
		['test_files121', 'paper-100k.pdf'],
		['test_files122', 'plrabn12.txt'],
		['test_files123', 'ptt5'],
		['test_files124', 'reymont'],
		['test_files125', 'samba'],
		['test_files126', 'sao'],
		['test_files127', 'sum'],
		['test_files128', 'urls.10K'],
		['test_files129', 'xargs.1'],
		['test_files130', 'webster'],
		['test_files131', 'xml'],
		['test_files132', 'x-ray'],
		['test_files133', 'Silesia x12'],
		['test_files200', 'enwik9'],
	];
	foreach ($pairs as [$dir, $id]) {
		$squash[$dir] = $id;
	}
	$cached = array_merge($squash, [
		'test_files' => 'misc tree root',
		'test_files2' => 'mixed small set',
		'test_files4' => 'small mixed',
		'test_files10' => 'BMP pair stress',
		'test_files11' => 'BMP variant',
		'test_files13' => 'html samples',
		'test_files21' => '2D fractal grid',
		'test_files22' => '2D fractal ext',
		'test_files23' => 'rec substr chain',
		'test_files24' => 'tuple+rec large',
		'test_files25' => 'replace spans',
		'test_files26' => 'gradient op',
		'test_files27' => 'tuple repeat',
		'test_files28' => 'substring scale',
		'test_files29' => 'showing_off txt',
		'test_files30' => 'c29 smoke slice',
		'test_files31' => 'multifractal run',
		'test_files32' => 'hard multifract',
		'test_files33' => 'empty dir',
		'test_files34' => 'single sf.bmp',
		'test_files35' => 'BMP fractal ~4M',
		'test_files49' => 'phpinfo html',
		'test_files50' => 'synth micro fs',
		'test_files51' => 'synth micro fs2',
		'test_files52' => 'mixed corpus',
		'test_files53' => 'mixed corpus',
		'test_files54' => 'huge site tree',
		'test_files54_sample' => '54 strat slice',
		'test_files55' => 'huge html tree',
		'test_files55_sample' => '55 1/8 subset',
		'test_files55_stratified' => '55 strat slice',
		'test_files56' => 'SC2Replay flat',
		'test_files56_sample' => '56 sample',
		'test_files57' => 'php langref',
		'test_files58' => 'huge tree ~460M',
		'test_files58_sample' => '58 sample',
		'test_files59' => 'huge tree set',
		'test_files59_sample' => '59 sample',
		'test_files60' => '2 FLAC sidecars',
		'test_files61' => 'rasters+outers',
		'test_files62' => 'synth .gz parity',
		'test_files63' => 'literal stress',
		'test_files64' => 'single grid bmp',
		'test_files65' => 'ZIP literals',
		'test_files69' => 'strat ≤5MiB',
		'test_files70' => 'gzip9 wedge',
		'test_files71' => 'large PDF',
		'test_files72' => '72 corpus',
		'test_files72_sample' => '72 dup sample',
		'test_files72_sample_micro' => '72 micro dup',
		'test_files74' => 'desktop mix',
		'test_files75' => 'web static mix',
		'test_files76' => 'phone/mobile mix',
		'test_files77' => 'Calgary corpus',
		'test_files81' => 'peeler strat set',
		'test_files140' => 'fz inner basic',
		'test_files141' => 'fz inner escape',
		'test_files142' => 'fz inner grad',
		'test_files143' => 'fz inner repl',
		'test_files144' => 'fz inner grid',
		'test_files145' => 'fz inner runpair',
		'test_files146' => 'fz inner ladder',
		'test_files147' => 'fz inner mixed',
		'test_files148' => 'fz inner binary',
		'test_files149' => 'fz inner cont',
		'test_files150' => 'fz inner hardmix',
		'test_files180' => 'epi 1-circle S',
		'test_files181' => 'epi 1-circle M',
		'test_files182' => 'epi complex L',
		'test_files183' => 'epi 2-walk S',
		'test_files184' => 'epi 2-walk M',
		'test_files185' => 'epi 2-walk L',
		'test_files186' => 'epi 3-walk S',
		'test_files187' => 'epi 3-walk M',
		'test_files188' => 'epi 3-walk L',
		'test_files189' => 'epi RR multi S',
		'test_files190' => 'epi RR multi M',
		'test_files191' => 'epi deferent L',
		'test_files192' => 'epi defer w3',
		'test_files193' => 'epi complex',
		'test_files201' => 'web census mix',
		'test_files202' => 'GP game assets',
		'test_files203' => 'GP open video',
		'test_files204' => 'GP PDF govdocs',
		'test_files205' => 'GP photo COCO',
		'test_files206' => 'GP source trees',
		'test_files207' => 'GP Debian debs',
		'test_files208' => 'GP tabular data',
		'test_files209' => 'GP ML weights',
		'test_files210' => 'GP precompressed',
	]);
	return $cached;
}

function bench_normalize_desc16(string $s): string
{
	$s = trim($s);
	if (strlen($s) > 16) {
		return substr($s, 0, 16);
	}
	return str_pad($s, 16, ' ', STR_PAD_RIGHT);
}

/** Exactly 16 characters for fixed-width bench table / JSON. */
function bench_corpus_desc16(string $label): string
{
	$map = bench_corpus_description_map();
	if (isset($map[$label])) {
		return bench_normalize_desc16($map[$label]);
	}
	$tail = preg_replace('/^test_files/', '', $label);
	if ($tail === null || $tail === '') {
		$tail = $label;
	}
	return bench_normalize_desc16($tail);
}
