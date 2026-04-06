<?php
declare(strict_types=1);

/**
 * XML index: logical file paths ↔ loose blobs or .fzc membership.
 * Physical files live under files_root (default: sibling ../files next to fractal_zip).
 *
 * Queries use LOM (Living Object Model) instead of DOM/XPath. Default library path:
 * sibling `LOM/O.php` next to this project. Override with env `LOM_O_PHP` or constructor argument.
 */
final class StorageIndex
{
	private static bool $lomLoaded = false;

	private string $indexPath;
	private string $filesRoot;
	private string $lomPath;
	/** @var list<array{id:string,logical_path:string,storage:string,physical:string,container_id:?string,member_path:?string,raw_bytes:int,updated:string}> */
	private array $files = [];
	/** @var list<array{id:string,physical:string,byte_size:int,updated:string}> */
	private array $containers = [];

	public function __construct(?string $indexPath = null, ?string $filesRoot = null, ?string $lomPath = null)
	{
		$base = dirname(__DIR__);
		$this->indexPath = $indexPath ?? (getenv('STORAGE_INDEX_XML') ?: $base . '/storage/storage_index.xml');
		$this->filesRoot = $filesRoot ?? (getenv('FRACTAL_FILES_ROOT') ?: dirname($base) . '/files');
		$this->lomPath = $lomPath ?? (getenv('LOM_O_PHP') ?: dirname($base) . DIRECTORY_SEPARATOR . 'LOM' . DIRECTORY_SEPARATOR . 'O.php');
	}

	public function getFilesRoot(): string
	{
		return $this->filesRoot;
	}

	public function getIndexPath(): string
	{
		return $this->indexPath;
	}

	public function load(): void
	{
		$this->files = [];
		$this->containers = [];
		if (!is_readable($this->indexPath)) {
			return;
		}

		$this->ensureLom();
		$lom = new O($this->indexPath);

		foreach ($this->lomTaggedMatches($lom, 'container') as $match) {
			$attrs = $lom->get_tag_attributes_of_string($match[0]);
			if (!is_array($attrs)) {
				continue;
			}
			$this->containers[] = [
				'id' => $attrs['id'] ?? '',
				'physical' => $attrs['physical'] ?? '',
				'byte_size' => (int) ($attrs['byte_size'] ?? 0),
				'updated' => $attrs['updated'] ?? '',
			];
		}

		foreach ($this->lomTaggedMatches($lom, 'file') as $match) {
			$attrs = $lom->get_tag_attributes_of_string($match[0]);
			if (!is_array($attrs)) {
				continue;
			}
			$cid = $attrs['container_id'] ?? '';
			$this->files[] = [
				'id' => $attrs['id'] ?? '',
				'logical_path' => $attrs['logical_path'] ?? '',
				'storage' => $attrs['storage'] ?? '',
				'physical' => $attrs['physical'] ?? '',
				'container_id' => $cid !== '' ? $cid : null,
				'member_path' => ($attrs['member_path'] ?? '') !== '' ? $attrs['member_path'] : null,
				'raw_bytes' => (int) ($attrs['raw_bytes'] ?? 0),
				'updated' => $attrs['updated'] ?? '',
			];
		}
	}

	private function ensureLom(): void
	{
		if (self::$lomLoaded) {
			return;
		}
		if (!is_readable($this->lomPath)) {
			throw new RuntimeException(
				'LOM not found at ' . $this->lomPath . '. Install LOM beside fractal_zip or set LOM_O_PHP.'
			);
		}
		require_once $this->lomPath;
		self::$lomLoaded = true;
	}

	/**
	 * @param object $lom LOM instance (class O from LOM/O.php)
	 * @return list<array{0:string,1:int}>
	 */
	private function lomTaggedMatches(object $lom, string $tag): array
	{
		$raw = $lom->get($tag, false, false, true, false, true);
		if (!is_array($raw) || $raw === []) {
			return [];
		}
		if (isset($raw[0]) && is_string($raw[0]) && str_starts_with($raw[0], '<')) {
			return [$raw];
		}
		$out = [];
		foreach ($raw as $row) {
			if (is_array($row) && isset($row[0]) && is_string($row[0])) {
				$out[] = $row;
			}
		}
		return $out;
	}

	/**
	 * @return list<array<string,mixed>>
	 */
	public function getFiles(): array
	{
		return $this->files;
	}

	/**
	 * @return list<array<string,mixed>>
	 */
	public function getContainers(): array
	{
		return $this->containers;
	}

	public function absolutePhysical(string $relative): string
	{
		return rtrim($this->filesRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
			. str_replace('/', DIRECTORY_SEPARATOR, ltrim($relative, '/'));
	}
}
