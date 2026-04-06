<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

$base = dirname(__DIR__, 2);
require_once $base . '/storage/StorageIndex.php';
require_once $base . '/storage/CompactionCalculus.php';

$index = new StorageIndex();
$index->load();

$files = $index->getFiles();
$containers = $index->getContainers();

$totalRaw = 0;
$looseRaw = 0;
$containerBackedRaw = 0;
foreach ($files as $f) {
	$totalRaw += $f['raw_bytes'];
	if ($f['storage'] === 'loose') {
		$looseRaw += $f['raw_bytes'];
	} else {
		$containerBackedRaw += $f['raw_bytes'];
	}
}

$containerDisk = 0;
foreach ($containers as $c) {
	$containerDisk += $c['byte_size'];
}

$calc = new CompactionCalculus(
	weightBytes: 1.0,
	weightLatency: 0.0012,
	minLooseBytes: 256,
	minScoreToCompact: 100.0
);
$fileRows = array_map(static function (array $f) {
	return [
		'logical_path' => $f['logical_path'],
		'raw_bytes' => $f['raw_bytes'],
		'storage' => $f['storage'],
	];
}, $files);
$suggestions = $calc->suggestClusters($fileRows, 8);

echo json_encode([
	'files_root' => $index->getFilesRoot(),
	'index_path' => $index->getIndexPath(),
	'totals' => [
		'logical_files' => count($files),
		'containers' => count($containers),
		'raw_bytes_all' => $totalRaw,
		'raw_bytes_loose' => $looseRaw,
		'raw_bytes_in_containers' => $containerBackedRaw,
		'container_disk_bytes' => $containerDisk,
	],
	'containers' => $containers,
	'files' => $files,
	'compaction_suggestions' => $suggestions,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
