<?php
declare(strict_types=1);

/**
 * Sidecar index: each member path maps to the inclusive [first_byte, last_byte] range within one merged literal
 * byte stream. No path/mode/varint data lives inside the payload at those boundaries, so the outer codec sees
 * uninterrupted redundancy across former “file” joins. The index is owned by the path layer and must stay consistent
 * whenever the merged buffer changes (semantic decompress, concat order, FZ whole-buffer transforms, per-span edits).
 *
 * Update rules (callers enforce):
 * - After replacing the entire buffer, rebuild all spans or remap from a known layout.
 * - After inserting `delta` bytes at offset `from` (before existing byte `from`), shift every span with
 *   first_byte >= from by +delta and add delta to merged length. Spans that cross `from` need a split/merge policy
 *   (not handled here).
 * - After a reversible transform that rewrites the whole stream (delta/xor on blob), either re-derive spans from
 *   the same logical layout or apply the inverse layout map; trivial fixed-size whole-stream transforms may only
 *   change length() if the transform pads.
 *
 * This type is intentionally small; wire it into encode/decode once the literal container no longer embeds
 * per-member headers inside the compressed stream.
 */
final class fractal_zip_literal_stream_index {

	private int $mergedLength = 0;

	/** @var array<string, array{first: int, last: int}> relative path => inclusive byte range */
	private array $byPath = array();

	public function merged_length(): int {
		return $this->mergedLength;
	}

	public function set_merged_length(int $len): void {
		$this->mergedLength = max(0, $len);
	}

	/**
	 * Inclusive first and last byte indices (0-based) into the current merged stream.
	 */
	public function set_span(string $relPath, int $firstByte, int $lastByte): void {
		if($firstByte < 0 || $lastByte < $firstByte) {
			throw new InvalidArgumentException('fractal_zip_literal_stream_index: need 0 <= first <= last.');
		}
		if($this->mergedLength > 0 && $lastByte >= $this->mergedLength) {
			throw new InvalidArgumentException('fractal_zip_literal_stream_index: last byte out of range for merged length.');
		}
		$this->byPath[$relPath] = array('first' => $firstByte, 'last' => $lastByte);
	}

	/** @return array{first: int, last: int}|null */
	public function get_span(string $relPath): ?array {
		if(!isset($this->byPath[$relPath])) {
			return null;
		}
		return $this->byPath[$relPath];
	}

	public function remove_path(string $relPath): void {
		unset($this->byPath[$relPath]);
	}

	public function clear_all_spans(): void {
		$this->byPath = array();
	}

	/** @return list<string> */
	public function paths(): array {
		$k = array_keys($this->byPath);
		sort($k, SORT_STRING);
		return $k;
	}

	/**
	 * @return array<string, array{first: int, last: int}>
	 */
	public function all_spans(): array {
		return $this->byPath;
	}

	/**
	 * Insert `delta` bytes immediately before index `from` (pushing bytes at `from` and above rightward).
	 * Every span with first_byte >= from is shifted by +delta; merged length increases by delta.
	 */
	public function insert_bytes_before(int $from, int $delta): void {
		if($delta === 0) {
			return;
		}
		if($from < 0 || $from > $this->mergedLength) {
			throw new InvalidArgumentException('fractal_zip_literal_stream_index: insert position out of range.');
		}
		foreach($this->byPath as $p => $s) {
			if($s['first'] >= $from) {
				$this->byPath[$p] = array(
					'first' => $s['first'] + $delta,
					'last' => $s['last'] + $delta,
				);
			}
		}
		$this->mergedLength += $delta;
	}

	/**
	 * Delete `delta` bytes starting at `from` (merged stream shrinks). Spans starting at/after from+delta shift left by delta.
	 * Spans overlapping [from, from+delta) must be adjusted by the caller (truncate/split); this only shifts disjoint tails.
	 */
	public function delete_bytes_at(int $from, int $delta): void {
		if($delta === 0) {
			return;
		}
		if($from < 0 || $from + $delta > $this->mergedLength) {
			throw new InvalidArgumentException('fractal_zip_literal_stream_index: delete range out of bounds.');
		}
		$cutEnd = $from + $delta;
		foreach($this->byPath as $p => $s) {
			if($s['first'] >= $cutEnd) {
				$this->byPath[$p] = array(
					'first' => $s['first'] - $delta,
					'last' => $s['last'] - $delta,
				);
			}
		}
		$this->mergedLength -= $delta;
	}
}
