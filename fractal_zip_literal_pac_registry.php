<?php
declare(strict_types=1);

/**
 * Registry of single-blob (or single-member archive) formats for literal_pac.
 * Mirrors intent of ../file_types/types.php “compressed” category; extend both together when that file exists in-tree.
 *
 * @var list<array{
 *   id: string,
 *   label: string,
 *   extensions: list<string>,
 *   handler: string,
 *   magic_prefix: ?string,
 *   tools: list<string>,
 *   notes?: string
 * }>
 */
const FRACTAL_ZIP_LITERALPAC_REGISTRY = [
	[
		'id' => 'gzip',
		'label' => 'gzip (DEFLATE)',
		'extensions' => ['gz', 'svgz'],
		'handler' => 'gzip_variants',
		'magic_prefix' => "\x1F\x8B",
		'tools' => ['gzip', 'pigz', 'zopfli'],
		'notes' => 'FRACTAL_ZIP_LITERALPAC_GZIP_ENGINE=auto|gzip|pigz|zopfli; ZOPFLI tries only if tool exists and FRACTAL_ZIP_LITERALPAC_ZOPFLI=1 (slow).',
	],
	[
		'id' => 'bzip2',
		'label' => 'bzip2',
		'extensions' => ['bz2'],
		'handler' => 'bzip2',
		'magic_prefix' => 'BZ',
		'tools' => ['bzip2'],
	],
	[
		'id' => 'xz',
		'label' => 'xz (LZMA2)',
		'extensions' => ['xz'],
		'handler' => 'xz',
		'magic_prefix' => "\xFD\x37\x7A\x58\x5A\x00",
		'tools' => ['xz'],
	],
	[
		'id' => 'zstd',
		'label' => 'Zstandard',
		'extensions' => ['zst'],
		'handler' => 'zstd',
		'magic_prefix' => "\x28\xB5\x2F\xFD",
		'tools' => ['zstd'],
	],
	[
		'id' => 'lz4',
		'label' => 'LZ4 frame',
		'extensions' => ['lz4'],
		'handler' => 'lz4',
		'magic_prefix' => "\x04\x22\x4D\x18",
		'tools' => ['lz4'],
	],
	[
		'id' => 'brotli',
		'label' => 'Brotli',
		'extensions' => ['br'],
		'handler' => 'brotli',
		'magic_prefix' => null,
		'tools' => ['brotli'],
	],
	[
		'id' => 'lzip',
		'label' => 'lzip',
		'extensions' => ['lzip'],
		'handler' => 'lzip',
		'magic_prefix' => 'LZIP',
		'tools' => ['lzip'],
	],
	[
		'id' => 'lzma_raw',
		'label' => 'raw LZMA (.lz not lzip)',
		'extensions' => ['lz'],
		'handler' => 'lzma_sniff',
		'magic_prefix' => null,
		'tools' => ['lzma', 'lzip'],
		'notes' => 'If magic is LZIP, use lzip; else lzma(1) stream.',
	],
	[
		'id' => 'woff2',
		'label' => 'WOFF2 font',
		'extensions' => ['woff2'],
		'handler' => 'woff2',
		'magic_prefix' => 'wOF2',
		'tools' => ['woff2_decompress', 'woff2_compress'],
	],
	[
		'id' => 'zip_single',
		'label' => 'ZIP (one member, re-packed with max deflate if smaller)',
		'extensions' => ['zip'],
		'handler' => 'zip_single',
		'magic_prefix' => "PK\x03\x04",
		'tools' => ['php-zip'],
		'notes' => 'Any compression method libzip can decompress via getFromIndex; output always deflate L9 when setCompressionIndex exists.',
	],
	[
		'id' => '7z_single',
		'label' => '7z (exactly one file member)',
		'extensions' => ['7z'],
		'handler' => 'seven_single',
		'magic_prefix' => "7z\xBC\xAF\x27\x1C",
		'tools' => ['7z', '7za', '7zz'],
	],
	[
		'id' => 'pdf_qpdf',
		'label' => 'PDF rewrite (qpdf, lossless structure)',
		'extensions' => ['pdf'],
		'handler' => 'pdf_qpdf',
		'magic_prefix' => '%PDF',
		'tools' => ['qpdf', 'pdfinfo'],
	],
	[
		'id' => 'mpq_mopaq',
		'label' => 'MoPAQ / MPQ (Blizzard container; StarCraft II .SC2Replay, Warcraft III .w3x, etc.)',
		'extensions' => ['sc2replay', 'mpq', 'w3x'],
		'handler' => 'mpq_optional',
		'magic_prefix' => 'MPQ',
		'tools' => ['python3', 'smpq'],
		'notes' => 'Magic "MPQ" + version byte (e.g. 0x1A classic, 0x1B SC2). Stream: repack-smaller (smpq, semantic) then optional FRACTAL_ZIP_LITERALPAC_MPQ_TOOL. Semantic peel (mode 11): same script + mpyq; gzip-1 probe gates peel unless FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_ALWAYS=1. FRACTAL_ZIP_LITERALPAC_MPQ_REPACK=0 disables repack-smaller.',
	],
];
