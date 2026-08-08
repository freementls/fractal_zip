<?php
declare(strict_types=1);

/**
 * Print identify() + resolved profile for Mahoney / test_files133 members.
 *
 *   php benchmarks/identify_silesia133_members.php [corpus_dir]
 */

require_once dirname(__DIR__) . '/fractal_zip_content_format_policy.php';

$dir = $argv[1] ?? (dirname(__DIR__) . '/test_files133');
if (!is_dir($dir)) {
	fwrite(STDERR, "missing dir: $dir\n");
	exit(1);
}
$names = array(
	'dickens', 'mozilla', 'mr', 'nci', 'ooffice', 'osdb', 'reymont', 'samba',
	'sao', 'sherlock', 'vcb', 'webster', 'xml', 'x-ray',
);
foreach ($names as $base) {
	$path = $dir . '/' . $base;
	if (!is_file($path)) {
		continue;
	}
	$bytes = (string) file_get_contents($path, false, null, 0, 65536);
	$row = fractal_zip_identify_for_policy($base, $bytes);
	printf(
		"%-12s profile=%-20s tier=%d final=%s\n",
		$base,
		$row['content_profile'],
		(int) ($row['tier_resolved'] ?? 0),
		$row['final_label'] ?? ''
	);
}
