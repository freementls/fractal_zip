<?php
declare(strict_types=1);

/**
 * Heuristic scoring for when to move logical files into a shared .fzc vs keep them loose.
 *
 * Models (tunable):
 * - Space benefit: approximate bytes saved vs storing each member with gzip-only baseline.
 * - User-time cost: proportional to decompressing/scanning the whole container on access
 *   (matches current fractal_zip PHP path when extracting one member still loads the container).
 */
final class CompactionCalculus
{
	public function __construct(
		private float $weightBytes = 1.0,
		private float $weightLatency = 0.001,
		private int $minLooseBytes = 512,
		private float $minScoreToCompact = 0.0
	) {
	}

	/**
	 * @param array{raw_bytes:int, member_count_hint:int, estimated_fzc_bytes:?int} $cluster
	 * @return array{score:float, recommend_compact:bool, bytes_saved_est:int, latency_units:float, notes:string}
	 */
	public function scoreCluster(array $cluster): array
	{
		$sumRaw = max(0, (int) $cluster['raw_bytes']);
		$n = max(1, (int) $cluster['member_count_hint']);
		$gzipBundle = isset($cluster['gzip_bundle_bytes'])
			? (int) $cluster['gzip_bundle_bytes']
			: (int) ($sumRaw * 0.15 + 64 * $n);
		$fzc = $cluster['estimated_fzc_bytes'] ?? (int) ($gzipBundle * 1.08);

		$bytesSaved = max(0, $sumRaw - $fzc);
		$latencyUnits = $fzc * (1.0 + log(1.0 + $n, 2));
		$score = $this->weightBytes * $bytesSaved - $this->weightLatency * $latencyUnits;

		$tooSmallLoose = $sumRaw / $n < $this->minLooseBytes;
		$recommend = $score > $this->minScoreToCompact && !$tooSmallLoose;

		$notes = $tooSmallLoose
			? 'Average member below min_loose_bytes; keep loose to avoid heavy container touch for tiny payloads.'
			: ($bytesSaved > 0 ? 'Cluster likely wins on disk.' : 'Little raw saving; prefer loose unless dedup is strong.');

		return [
			'score' => round($score, 4),
			'recommend_compact' => $recommend,
			'bytes_saved_est' => $bytesSaved,
			'latency_units' => round($latencyUnits, 2),
			'notes' => $notes,
		];
	}

	/**
	 * @param list<array{logical_path:string, raw_bytes:int, storage:string}> $files
	 * @return list<array{candidate_ids:list<string>, score:float, recommend_compact:bool, bytes_saved_est:int, latency_units:float, notes:string}>
	 */
	public function suggestClusters(array $files, int $maxClusterSize = 12): array
	{
		$loose = array_values(array_filter($files, static fn ($f) => ($f['storage'] ?? '') === 'loose'));
		usort($loose, static fn ($a, $b) => ($b['raw_bytes'] <=> $a['raw_bytes']));

		$suggestions = [];
		$bucket = [];
		$bucketBytes = 0;
		foreach ($loose as $f) {
			$bucket[] = $f;
			$bucketBytes += (int) $f['raw_bytes'];
			if (count($bucket) >= $maxClusterSize) {
				$suggestions[] = $this->clusterSuggestion($bucket, $bucketBytes);
				$bucket = [];
				$bucketBytes = 0;
			}
		}
		if ($bucket !== []) {
			$suggestions[] = $this->clusterSuggestion($bucket, $bucketBytes);
		}

		return $suggestions;
	}

	/**
	 * @param list<array{logical_path:string, raw_bytes:int, storage:string}> $bucket
	 * @return array{candidate_ids:list<string>, score:float, recommend_compact:bool, bytes_saved_est:int, latency_units:float, notes:string}
	 */
	private function clusterSuggestion(array $bucket, int $bucketBytes): array
	{
		$ids = array_map(static fn ($f) => $f['logical_path'], $bucket);
		$sc = $this->scoreCluster([
			'raw_bytes' => $bucketBytes,
			'member_count_hint' => count($bucket),
		]);
		return array_merge(['candidate_ids' => $ids], $sc);
	}
}
