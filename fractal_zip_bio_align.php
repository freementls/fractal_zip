<?php

declare(strict_types=1);

/**
 * Bioinformatics alignment transforms for fractal_zip: piecewise chained multishift,
 * progressive alignment layouts, VCF encoding, synteny blocks, pangenome graph wire.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_bioinformatics.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';

const FRACTAL_ZIP_BIO_ALIGN_MAGIC = "FZBA\x01";

function fractal_zip_bio_align_store_raw(string &$blob, array &$records, int $idx, string $text): void
{
	$blob .= $text;
	$records[] = array('kind' => 3, 'idx' => $idx, 'len' => strlen($text));
}

function fractal_zip_bio_align_append_vcf_or_raw(string &$blob, array &$records, string $ref, int $refIdx, int $idx, string $member): void
{
	$pack = fractal_zip_bio_vcf_try_pack($ref, $member);
	if ($pack === null) {
		fractal_zip_bio_align_store_raw($blob, $records, $idx, $member);
		return;
	}
	if ($pack['var_count'] === 0) {
		$records[] = array('kind' => 5, 'idx' => $idx);
		return;
	}
	$vcf = (string) $pack['vcf'];
	$blob .= $vcf;
	$records[] = array('kind' => 2, 'idx' => $idx, 'var_count' => (int) $pack['var_count'], 'vcf_len' => strlen($vcf));
}

/**
 * Try rigid shift + delta-from-reference (multishift-style) for one member.
 *
 * @return array{payload:string, record:array<string,mixed>}|null
 */
function fractal_zip_bio_rigid_delta_try_pack(string $ref, string $member): ?array
{
	if ($ref === $member) {
		return null;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';
	$refAnchors = fractal_zip_multishift_anchor_map($ref);
	$peaks = fractal_zip_multishift_shift_vote($ref, $member, $refAnchors);
	$best = fractal_zip_multishift_best_member_shift($ref, $member, $peaks);
	$shift = (int) $best['shift'];
	$delta = (string) $best['delta'];
	$dec = fractal_zip_multishift_delta_decode($delta, 0, $ref, $shift, strlen($member));
	if ($dec[0] !== $member) {
		return null;
	}
	return array(
		'payload' => $delta,
		'record' => array(
			'kind' => 4,
			'idx' => 0,
			'shift' => $shift,
			'member_len' => strlen($member),
			'payload_len' => strlen($delta),
		),
	);
}

/**
 * Piecewise multishift: colinear anchor chains → per-segment shifts + delta residuals.
 *
 * @param list<array{text:string}> $pages
 * @return array{blob:string, sidecar:string, stats:array<string,mixed>}
 */
function fractal_zip_bio_piecewise_align_build(array $pages, array $opts = array()): array
{
	$n = count($pages);
	if ($n === 0) {
		return array('blob' => '', 'sidecar' => '', 'stats' => array('pages' => 0));
	}
	if ($n === 1) {
		$t = (string) $pages[0]['text'];
		return array('blob' => $t, 'sidecar' => '', 'stats' => array('pages' => 1, 'singleton' => true));
	}
	$items = array();
	foreach ($pages as $i => $pg) {
		$items[] = array('id' => $i, 'text' => (string) $pg['text']);
	}
	$clusters = fractal_zip_bio_cdhit_cluster($items, (float) ($opts['jaccard_min'] ?? 0.35));
	$blob = '';
	$records = array();
	foreach ($clusters as $cluster) {
		$clusterRecords = fractal_zip_bio_piecewise_align_cluster($pages, $cluster, $opts);
		$blob .= (string) $clusterRecords['blob'];
		foreach ($clusterRecords['records'] as $rec) {
			$records[] = $rec;
		}
	}
	$refIdx = 0;
	foreach ($records as $rec) {
		if ((int) $rec['kind'] === 0) {
			$refIdx = (int) $rec['idx'];
			break;
		}
	}
	$sidecar = fractal_zip_bio_align_pack_sidecar($n, $refIdx, $records);
	return array(
		'blob' => $blob,
		'sidecar' => $sidecar,
		'stats' => array('pages' => $n, 'clusters' => count($clusters), 'records' => count($records)),
	);
}

/**
 * @param list<int> $cluster page indices in one CD-HIT cluster
 * @return array{blob:string, records:list<array<string,mixed>>}
 */
function fractal_zip_bio_piecewise_align_cluster(array $pages, array $cluster, array $opts = array()): array
{
	if (count($cluster) === 1) {
		$idx = (int) $cluster[0];
		$t = (string) $pages[$idx]['text'];
		return array('blob' => $t, 'records' => array(array('kind' => 0, 'idx' => $idx, 'len' => strlen($t))));
	}
	$refIdx = (int) $cluster[0];
	$maxLen = -1;
	foreach ($cluster as $idx) {
		$l = strlen((string) $pages[$idx]['text']);
		if ($l > $maxLen) {
			$maxLen = $l;
			$refIdx = (int) $idx;
		}
	}
	$ref = (string) $pages[$refIdx]['text'];
	$blob = $ref;
	$records = array(array('kind' => 0, 'idx' => $refIdx, 'len' => strlen($ref)));
	$n = count($pages);
	foreach ($cluster as $i) {
		$i = (int) $i;
		if ($i === $refIdx) {
			continue;
		}
		$member = (string) $pages[$i]['text'];
		$rigid = fractal_zip_bio_rigid_delta_try_pack($ref, $member);
		if ($rigid !== null) {
			$rec = $rigid['record'];
			$rec['idx'] = $i;
			$blob .= (string) $rigid['payload'];
			$records[] = $rec;
			continue;
		}
		$mems = fractal_zip_bio_mems($ref, $member, (int) ($opts['min_mem'] ?? 6));
		$anchors = array();
		foreach ($mems as $m) {
			$anchors[] = array('ref_pos' => $m['ref_pos'], 'query_pos' => $m['query_pos'], 'len' => $m['len']);
		}
		$spaced = fractal_zip_bio_spaced_seed_pair_anchors($ref, $member);
		foreach ($spaced as $a) {
			$anchors[] = array('ref_pos' => $a['ref_pos'], 'query_pos' => $a['query_pos'], 'len' => 1);
		}
		$chains = fractal_zip_bio_colinear_chain($anchors, (int) ($opts['max_shift_dev'] ?? 12));
		if ($chains === array()) {
			fractal_zip_bio_align_append_vcf_or_raw($blob, $records, $ref, $refIdx, $i, $member);
			continue;
		}
		$covered = 0;
		foreach ($chains as $ch) {
			$covered += max(0, (int) $ch['query_end'] - (int) $ch['query_start']);
		}
		if ($covered < (int) (strlen($member) * 0.85)) {
			fractal_zip_bio_align_append_vcf_or_raw($blob, $records, $ref, $refIdx, $i, $member);
			continue;
		}
		$segPayload = '';
		$segMeta = array();
		$reconstructed = str_repeat("\0", strlen($member));
		foreach ($chains as $ch) {
			$shift = (int) $ch['shift'];
			$qs = (int) $ch['query_start'];
			$qe = (int) $ch['query_end'];
			$rs = (int) $ch['ref_start'];
			$re = (int) $ch['ref_end'];
			$segMeta[] = array(
				'shift' => $shift,
				'ref_start' => $rs,
				'ref_end' => $re,
				'query_start' => $qs,
				'query_end' => $qe,
			);
			$qSlice = substr($member, $qs, $qe - $qs);
			$rSlice = substr($ref, $rs, $re - $rs);
			$segPayload .= fractal_zip_multishift_delta_encode($rSlice, $qSlice, 0);
			for ($k = 0; $k < strlen($qSlice); $k++) {
				$reconstructed[$qs + $k] = $qSlice[$k];
			}
		}
		if ($reconstructed !== $member || strpos($reconstructed, "\0") !== false) {
			fractal_zip_bio_align_append_vcf_or_raw($blob, $records, $ref, $refIdx, $i, $member);
			continue;
		}
		$blob .= $segPayload;
		$records[] = array('kind' => 1, 'idx' => $i, 'segments' => $segMeta, 'payload_len' => strlen($segPayload));
	}
	return array('blob' => $blob, 'records' => $records);
}

function fractal_zip_bio_align_pack_sidecar(int $n, int $refIdx, array $records): string
{
	$out = FRACTAL_ZIP_BIO_ALIGN_MAGIC;
	$out .= fractal_zip_enwik_encode_varint_u32($n);
	$out .= fractal_zip_enwik_encode_varint_u32($refIdx);
	$out .= fractal_zip_enwik_encode_varint_u32(count($records));
	foreach ($records as $rec) {
		$kind = (int) $rec['kind'];
		$out .= chr($kind);
		$out .= fractal_zip_enwik_encode_varint_u32((int) $rec['idx']);
		if ($kind === 0) {
			$out .= fractal_zip_enwik_encode_varint_u32((int) $rec['len']);
		} elseif ($kind === 1) {
			$segs = $rec['segments'];
			$out .= fractal_zip_enwik_encode_varint_u32(count($segs));
			foreach ($segs as $s) {
				$out .= fractal_zip_multishift_zigzag_encode((int) $s['shift']);
				$out .= fractal_zip_enwik_encode_varint_u32((int) $s['ref_start']);
				$out .= fractal_zip_enwik_encode_varint_u32((int) $s['ref_end']);
				$out .= fractal_zip_enwik_encode_varint_u32((int) $s['query_start']);
				$out .= fractal_zip_enwik_encode_varint_u32((int) $s['query_end']);
			}
			$out .= fractal_zip_enwik_encode_varint_u32((int) $rec['payload_len']);
		} elseif ($kind === 2) {
			$out .= fractal_zip_enwik_encode_varint_u32((int) $rec['var_count']);
			$out .= fractal_zip_enwik_encode_varint_u32((int) ($rec['vcf_len'] ?? 0));
		} elseif ($kind === 3) {
			$out .= fractal_zip_enwik_encode_varint_u32((int) $rec['len']);
		} elseif ($kind === 4) {
			$out .= fractal_zip_multishift_zigzag_encode((int) $rec['shift']);
			$out .= fractal_zip_enwik_encode_varint_u32((int) $rec['member_len']);
			$out .= fractal_zip_enwik_encode_varint_u32((int) $rec['payload_len']);
		}
		// kind 5 (ref alias): idx only, no payload fields
	}
	return $out;
}

/**
 * @return list<string> restored page texts
 */
function fractal_zip_bio_piecewise_align_undo(string $blob, string $sidecar): array
{
	$ml = strlen(FRACTAL_ZIP_BIO_ALIGN_MAGIC);
	if (strncmp($sidecar, FRACTAL_ZIP_BIO_ALIGN_MAGIC, $ml) !== 0) {
		throw new RuntimeException('bio align: bad magic');
	}
	$pos = $ml;
	$readU = static function () use ($sidecar, &$pos): int {
		$d = fractal_zip_enwik_decode_varint_u32($sidecar, $pos);
		$pos = (int) $d[1];
		return (int) $d[0];
	};
	$n = $readU();
	$refIdx = $readU();
	$recCount = $readU();
	$records = array();
	for ($r = 0; $r < $recCount; $r++) {
		$kind = ord($sidecar[$pos]);
		$pos++;
		$idx = $readU();
		if ($kind === 0) {
			$len = $readU();
			$records[] = array('kind' => 0, 'idx' => $idx, 'len' => $len);
		} elseif ($kind === 1) {
			$sc = $readU();
			$segs = array();
			for ($s = 0; $s < $sc; $s++) {
				$zz = fractal_zip_multishift_zigzag_decode($sidecar, $pos);
				$pos = (int) $zz[1];
				$segs[] = array(
					'shift' => (int) $zz[0],
					'ref_start' => $readU(),
					'ref_end' => $readU(),
					'query_start' => $readU(),
					'query_end' => $readU(),
				);
			}
			$plen = $readU();
			$records[] = array('kind' => 1, 'idx' => $idx, 'segments' => $segs, 'payload_len' => $plen);
		} elseif ($kind === 2) {
			$vc = $readU();
			$vcfLen = $readU();
			$records[] = array('kind' => 2, 'idx' => $idx, 'var_count' => $vc, 'vcf_len' => $vcfLen);
		} elseif ($kind === 3) {
			$len = $readU();
			$records[] = array('kind' => 3, 'idx' => $idx, 'len' => $len);
		} elseif ($kind === 4) {
			$zz = fractal_zip_multishift_zigzag_decode($sidecar, $pos);
			$pos = (int) $zz[1];
			$memberLen = $readU();
			$plen = $readU();
			$records[] = array(
				'kind' => 4,
				'idx' => $idx,
				'shift' => (int) $zz[0],
				'member_len' => $memberLen,
				'payload_len' => $plen,
			);
		} elseif ($kind === 5) {
			$records[] = array('kind' => 5, 'idx' => $idx);
		}
	}
	$out = array_fill(0, $n, '');
	$bpos = 0;
	$ref = '';
	foreach ($records as $rec) {
		if ((int) $rec['kind'] === 0) {
			$ref = substr($blob, $bpos, (int) $rec['len']);
			break;
		}
	}
	foreach ($records as $rec) {
		$kind = (int) $rec['kind'];
		$idx = (int) $rec['idx'];
		if ($kind === 0) {
			$len = (int) $rec['len'];
			$out[$idx] = substr($blob, $bpos, $len);
			$bpos += $len;
			$ref = $out[$idx];
			continue;
		}
		if ($kind === 2) {
			$vc = (int) $rec['var_count'];
			$vcfLen = (int) ($rec['vcf_len'] ?? 0);
			$vcfBlob = substr($blob, $bpos, $vcfLen);
			$bpos += $vcfLen;
			$vars = fractal_zip_bio_vcf_decode($vcfBlob, $vc);
			$out[$idx] = fractal_zip_bio_vcf_apply($ref, $vars);
			continue;
		}
		if ($kind === 3) {
			$len = (int) $rec['len'];
			$out[$idx] = substr($blob, $bpos, $len);
			$bpos += $len;
			continue;
		}
		if ($kind === 4) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';
			$plen = (int) $rec['payload_len'];
			$payload = substr($blob, $bpos, $plen);
			$bpos += $plen;
			$dec = fractal_zip_multishift_delta_decode(
				$payload,
				0,
				$ref,
				(int) $rec['shift'],
				(int) $rec['member_len']
			);
			$out[$idx] = $dec[0];
			continue;
		}
		if ($kind === 5) {
			$out[$idx] = $ref;
			continue;
		}
		$plen = (int) $rec['payload_len'];
		$payload = substr($blob, $bpos, $plen);
		$bpos += $plen;
		$maxEnd = 0;
		foreach ($rec['segments'] as $seg) {
			$maxEnd = max($maxEnd, (int) $seg['query_end']);
		}
		$member = str_repeat("\0", $maxEnd);
		$ppos = 0;
		foreach ($rec['segments'] as $seg) {
			$rs = (int) $seg['ref_start'];
			$re = (int) $seg['ref_end'];
			$qs = (int) $seg['query_start'];
			$qe = (int) $seg['query_end'];
			$rSlice = substr($ref, $rs, $re - $rs);
			$dec = fractal_zip_multishift_delta_decode($payload, $ppos, $rSlice, 0, $qe - $qs);
			$slice = $dec[0];
			$ppos = (int) $dec[1];
			for ($k = 0; $k < strlen($slice); $k++) {
				$member[$qs + $k] = $slice[$k];
			}
		}
		$out[$idx] = rtrim($member, "\0");
	}
	return $out;
}

/**
 * Progressive alignment layout: UPGMA order + POA consensus + per-member VCF deltas.
 *
 * @param list<array{text:string}> $pages
 */
function fractal_zip_bio_progressive_layout_build(array $pages): array
{
	$items = array();
	foreach ($pages as $i => $pg) {
		$items[] = array('id' => $i, 'text' => (string) $pg['text']);
	}
	$clusters = fractal_zip_bio_cdhit_cluster($items, 0.35);
	$blob = '';
	$meta = array(
		'layout' => 'bio_progressive',
		'variants' => array(),
		'vcf_lens' => array(),
		'clusters' => array(),
		'raw_pages' => array(),
	);
	foreach ($clusters as $ci => $cluster) {
		$subItems = array();
		foreach ($cluster as $idx) {
			$subItems[] = array('id' => (int) $idx, 'text' => (string) $pages[(int) $idx]['text']);
		}
		$tree = fractal_zip_bio_upgma_guide_tree($subItems);
		$refIdx = (int) $cluster[0];
		$maxLen = -1;
		foreach ($cluster as $idx) {
			$l = strlen((string) $pages[(int) $idx]['text']);
			if ($l > $maxLen) {
				$maxLen = $l;
				$refIdx = (int) $idx;
			}
		}
		$consensus = (string) $pages[$refIdx]['text'];
		$clusterOff = strlen($blob);
		$blob .= $consensus;
		$meta['clusters'][$ci] = array(
			'off' => $clusterOff,
			'consensus_len' => strlen($consensus),
			'order' => $tree['order'],
		);
		foreach ($cluster as $idx) {
			$idx = (int) $idx;
			$meta['cluster_of'][$idx] = $ci;
			$text = (string) $pages[$idx]['text'];
			if ($text === $consensus) {
				$meta['variants'][$idx] = 0;
				continue;
			}
			$rigid = fractal_zip_bio_rigid_delta_try_pack($consensus, $text);
			if ($rigid !== null) {
				$payload = (string) $rigid['payload'];
				$rec = $rigid['record'];
				$meta['variants'][$idx] = -2;
				$meta['delta_meta'][$idx] = array(
					'shift' => (int) $rec['shift'],
					'member_len' => (int) $rec['member_len'],
					'off' => strlen($blob),
					'len' => strlen($payload),
				);
				$blob .= $payload;
				continue;
			}
			$pack = fractal_zip_bio_vcf_try_pack($consensus, $text);
			if ($pack === null) {
				$meta['variants'][$idx] = -1;
				$meta['raw_pages'][$idx] = array('off' => strlen($blob), 'len' => strlen($text));
				$blob .= $text;
				continue;
			}
			$vcf = (string) $pack['vcf'];
			$meta['variants'][$idx] = (int) $pack['var_count'];
			$meta['vcf_lens'][$idx] = strlen($vcf);
			$meta['vcf_off'][$idx] = strlen($blob);
			$blob .= $vcf;
		}
	}
	return array('blob' => $blob, 'meta' => $meta);
}

/**
 * MinHash-guided MI reorder (scalable replacement for O(n²) Jaccard).
 *
 * @param list<array{text:string}> $pages
 * @return list<int>
 */
function fractal_zip_bio_minhash_reorder_perm(array $pages): array
{
	$n = count($pages);
	if ($n <= 1) {
		return range(0, max(0, $n - 1));
	}
	$items = array();
	$sketches = array();
	foreach ($pages as $i => $pg) {
		$items[] = array('id' => $i, 'text' => (string) $pg['text']);
		$sketches[$i] = fractal_zip_bio_minhash_sketch((string) $pg['text']);
	}
	$clusters = fractal_zip_bio_cdhit_cluster($items, 0.25, 5);
	$order = array();
	foreach ($clusters as $cl) {
		$subItems = array();
		foreach ($cl as $id) {
			$subItems[] = array('id' => $id, 'text' => (string) $pages[$id]['text']);
		}
		$subTree = fractal_zip_bio_upgma_guide_tree($subItems);
		foreach ($subTree['order'] as $id) {
			$order[] = $id;
		}
	}
	if (count($order) !== $n) {
		foreach (range(0, $n - 1) as $i) {
			if (!in_array($i, $order, true)) {
				$order[] = $i;
			}
		}
	}
	return $order;
}

/**
 * Synteny-encoded layout: reference + block permutation sidecar + per-block deltas.
 *
 * @param list<array{text:string}> $pages
 */
function fractal_zip_bio_synteny_layout_build(array $pages): array
{
	if ($pages === array()) {
		return array('blob' => '', 'meta' => array());
	}
	$items = array();
	foreach ($pages as $i => $pg) {
		$items[] = array('id' => $i, 'text' => (string) $pg['text']);
	}
	$clusters = fractal_zip_bio_cdhit_cluster($items, 0.35);
	$blob = '';
	$meta = array('layout' => 'bio_synteny', 'clusters' => array(), 'vcf_lens' => array(), 'var_counts' => array(), 'raw_pages' => array());
	foreach ($clusters as $ci => $cluster) {
		$refIdx = (int) $cluster[0];
		$maxLen = -1;
		foreach ($cluster as $idx) {
			$l = strlen((string) $pages[(int) $idx]['text']);
			if ($l > $maxLen) {
				$maxLen = $l;
				$refIdx = (int) $idx;
			}
		}
		$ref = (string) $pages[$refIdx]['text'];
		$clusterOff = strlen($blob);
		$blob .= $ref;
		$meta['clusters'][$ci] = array('off' => $clusterOff, 'ref_len' => strlen($ref), 'ref_idx' => $refIdx);
		foreach ($cluster as $idx) {
			$idx = (int) $idx;
			$meta['cluster_of'][$idx] = $ci;
			if ($idx === $refIdx) {
				continue;
			}
			$member = (string) $pages[$idx]['text'];
			$syn = fractal_zip_bio_synteny_blocks($ref, $member);
			$meta['members'][$idx] = $syn;
			$rigid = fractal_zip_bio_rigid_delta_try_pack($ref, $member);
			if ($rigid !== null) {
				$payload = (string) $rigid['payload'];
				$rec = $rigid['record'];
				$meta['var_counts'][$idx] = -2;
				$meta['delta_meta'][$idx] = array(
					'shift' => (int) $rec['shift'],
					'member_len' => (int) $rec['member_len'],
				);
				$meta['vcf_lens'][$idx] = strlen($payload);
				$meta['vcf_off'][$idx] = strlen($blob);
				$blob .= $payload;
				continue;
			}
			$pack = fractal_zip_bio_vcf_try_pack($ref, $member);
			if ($pack === null) {
				$meta['raw_pages'][$idx] = array('off' => strlen($blob), 'len' => strlen($member));
				$meta['vcf_lens'][$idx] = 0;
				$meta['var_counts'][$idx] = -1;
				$blob .= $member;
				continue;
			}
			$vcf = (string) $pack['vcf'];
			$meta['vcf_lens'][$idx] = strlen($vcf);
			$meta['var_counts'][$idx] = (int) $pack['var_count'];
			$meta['vcf_off'][$idx] = strlen($blob);
			$blob .= $vcf;
		}
	}
	return array('blob' => $blob, 'meta' => $meta);
}

/**
 * Pangenome graph layout: consensus core + variation nodes packed.
 *
 * @param list<array{text:string}> $pages
 */
function fractal_zip_bio_pangenome_layout_build(array $pages): array
{
	$items = array();
	foreach ($pages as $i => $pg) {
		$items[] = array('id' => $i, 'text' => (string) $pg['text']);
	}
	$clusters = fractal_zip_bio_cdhit_cluster($items, 0.35);
	$blob = '';
	$meta = array('layout' => 'bio_pangenome', 'clusters' => array(), 'graph_b64' => '');
	$packedAll = '';
	foreach ($clusters as $ci => $cluster) {
		$subItems = array();
		foreach ($cluster as $idx) {
			$subItems[] = array('id' => (int) $idx, 'text' => (string) $pages[(int) $idx]['text']);
		}
		$graph = fractal_zip_bio_pangenome_graph($subItems);
		$outboard = fractal_zip_bio_pangenome_graph_outboard($graph);
		$clusterBlob = (string) $outboard['blob'];
		$packed = fractal_zip_bio_pangenome_pack($outboard['graph']);
		$consensusLen = 0;
		foreach ($outboard['graph']['nodes'] as $node) {
			if (($node['type'] ?? '') === 'core') {
				$consensusLen = (int) ($node['payload_len'] ?? 0);
				break;
			}
		}
		$meta['clusters'][$ci] = array(
			'off' => strlen($blob),
			'consensus_len' => $consensusLen,
			'cluster_len' => strlen($clusterBlob),
			'graph_off' => strlen($packedAll),
			'graph_len' => strlen($packed),
			'members' => $cluster,
		);
		$blob .= $clusterBlob;
		$packedAll .= $packed;
	}
	$meta['graph_b64'] = base64_encode($packedAll);
	$meta['consensus_len'] = strlen($blob);
	return array('blob' => $blob, 'meta' => $meta);
}

/**
 * Apply bio layout by id.
 *
 * @param list<array{text:string}> $pages
 */
function fractal_zip_bio_layout_apply(array $pages, string $layoutId, array $opts = array()): array
{
	$layoutId = strtolower(trim($layoutId));
	$n = count($pages);
	switch ($layoutId) {
		case 'bio_minhash_reorder':
			$perm = fractal_zip_bio_minhash_reorder_perm($pages);
			$parts = array();
			foreach ($perm as $idx) {
				$parts[] = (string) $pages[$idx]['text'];
			}
			return array(
				'text_blob' => implode('', $parts),
				'meta' => array('layout' => $layoutId, 'perm' => $perm),
			);
		case 'bio_piecewise_shift':
			$built = fractal_zip_bio_piecewise_align_build($pages, $opts);
			return array(
				'text_blob' => (string) $built['blob'],
				'meta' => array('layout' => $layoutId, 'sidecar_b64' => base64_encode((string) $built['sidecar'])),
			);
		case 'bio_progressive':
			$built = fractal_zip_bio_progressive_layout_build($pages);
			return array(
				'text_blob' => (string) $built['blob'],
				'meta' => array_merge(array('layout' => $layoutId), $built['meta']),
			);
		case 'bio_synteny':
			$built = fractal_zip_bio_synteny_layout_build($pages);
			return array(
				'text_blob' => (string) $built['blob'],
				'meta' => array_merge(array('layout' => $layoutId), $built['meta']),
			);
		case 'bio_pangenome':
			$built = fractal_zip_bio_pangenome_layout_build($pages);
			return array(
				'text_blob' => (string) $built['blob'],
				'meta' => array_merge(array('layout' => $layoutId), $built['meta']),
			);
		case 'bio_minhash_piecewise':
			$built = fractal_zip_bio_minhash_piecewise_build($pages, $opts);
			return array(
				'text_blob' => (string) $built['blob'],
				'meta' => $built['meta'],
			);
		default:
			throw new InvalidArgumentException('unknown bio layout: ' . $layoutId);
	}
}

/**
 * @return list<string>
 */
function fractal_zip_bio_layout_undo_chunks(string $textBlob, array $meta, array $pages): array
{
	$layout = strtolower(trim((string) ($meta['layout'] ?? '')));
	$n = count($pages);
	$out = array_fill(0, $n, '');
	switch ($layout) {
		case 'bio_minhash_reorder':
			$perm = $meta['perm'] ?? null;
			if (!is_array($perm) || count($perm) !== $n) {
				throw new RuntimeException('bio undo: perm missing');
			}
			$lens = array();
			foreach ($perm as $idx) {
				$lens[] = strlen((string) ($pages[(int) $idx]['text'] ?? ''));
			}
			$off = 0;
			$chunks = array();
			foreach ($lens as $len) {
				$chunks[] = substr($textBlob, $off, $len);
				$off += $len;
			}
			$inv = array_fill(0, $n, 0);
			foreach ($perm as $newPos => $origIdx) {
				$inv[(int) $origIdx] = (int) $newPos;
			}
			for ($i = 0; $i < $n; $i++) {
				$out[$i] = $chunks[$inv[$i]] ?? '';
			}
			return $out;
		case 'bio_piecewise_shift':
		case 'bio_minhash_piecewise':
			$b64 = (string) ($meta['sidecar_b64'] ?? '');
			$sidecar = base64_decode($b64, true);
			if ($sidecar === false) {
				throw new RuntimeException('bio undo: sidecar_b64 invalid');
			}
			$restored = fractal_zip_bio_piecewise_align_undo($textBlob, $sidecar);
			if (count($restored) !== $n) {
				throw new RuntimeException('bio undo: piecewise page count mismatch');
			}
			for ($i = 0; $i < $n; $i++) {
				$out[$i] = (string) ($restored[$i] ?? '');
			}
			return $out;
		case 'bio_progressive':
			$clusters = $meta['clusters'] ?? array();
			$rawPages = $meta['raw_pages'] ?? array();
			for ($i = 0; $i < $n; $i++) {
				if (isset($rawPages[$i]) && is_array($rawPages[$i])) {
					$rp = $rawPages[$i];
					$out[$i] = substr($textBlob, (int) $rp['off'], (int) $rp['len']);
					continue;
				}
				$ci = (int) ($meta['cluster_of'][$i] ?? 0);
				$cl = $clusters[$ci] ?? null;
				if (!is_array($cl)) {
					$out[$i] = (string) ($pages[$i]['text'] ?? '');
					continue;
				}
				$consensus = substr($textBlob, (int) $cl['off'], (int) $cl['consensus_len']);
				$vc = (int) ($meta['variants'][$i] ?? 0);
				if ($vc === -2) {
					$dm = $meta['delta_meta'][$i] ?? null;
					if (!is_array($dm)) {
						$out[$i] = (string) ($pages[$i]['text'] ?? '');
						continue;
					}
					require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';
					$payload = substr($textBlob, (int) $dm['off'], (int) $dm['len']);
					$dec = fractal_zip_multishift_delta_decode(
						$payload,
						0,
						$consensus,
						(int) $dm['shift'],
						(int) $dm['member_len']
					);
					$out[$i] = (string) $dec[0];
				} elseif ($vc > 0) {
					$vlen = (int) ($meta['vcf_lens'][$i] ?? 0);
					$pos = (int) ($meta['vcf_off'][$i] ?? 0);
					$vars = fractal_zip_bio_vcf_decode(substr($textBlob, $pos, $vlen), $vc);
					$out[$i] = fractal_zip_bio_vcf_apply($consensus, $vars);
				} else {
					$out[$i] = $consensus;
				}
			}
			return $out;
		case 'bio_synteny':
			$clusters = $meta['clusters'] ?? array();
			$rawPages = $meta['raw_pages'] ?? array();
			for ($i = 0; $i < $n; $i++) {
				if (isset($rawPages[$i]) && is_array($rawPages[$i])) {
					$rp = $rawPages[$i];
					$out[$i] = substr($textBlob, (int) $rp['off'], (int) $rp['len']);
					continue;
				}
				$ci = (int) ($meta['cluster_of'][$i] ?? -1);
				if ($ci < 0 || !isset($clusters[$ci])) {
					$out[$i] = (string) ($pages[$i]['text'] ?? '');
					continue;
				}
				$cl = $clusters[$ci];
				$ref = substr($textBlob, (int) $cl['off'], (int) $cl['ref_len']);
				if ((int) ($cl['ref_idx'] ?? -1) === $i) {
					$out[$i] = $ref;
					continue;
				}
				$vc = (int) ($meta['var_counts'][$i] ?? 0);
				$vlen = (int) ($meta['vcf_lens'][$i] ?? 0);
				if ($vc === -2 && $vlen > 0) {
					$dm = $meta['delta_meta'][$i] ?? null;
					if (!is_array($dm)) {
						$out[$i] = (string) ($pages[$i]['text'] ?? '');
						continue;
					}
					require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';
					$pos = (int) ($meta['vcf_off'][$i] ?? 0);
					$payload = substr($textBlob, $pos, $vlen);
					$dec = fractal_zip_multishift_delta_decode(
						$payload,
						0,
						$ref,
						(int) $dm['shift'],
						(int) $dm['member_len']
					);
					$out[$i] = (string) $dec[0];
				} elseif ($vc > 0 && $vlen > 0) {
					$pos = (int) ($meta['vcf_off'][$i] ?? 0);
					$vars = fractal_zip_bio_vcf_decode(substr($textBlob, $pos, $vlen), $vc);
					$out[$i] = fractal_zip_bio_vcf_apply($ref, $vars);
				} else {
					$out[$i] = (string) ($pages[$i]['text'] ?? '');
				}
			}
			return $out;
		case 'bio_pangenome':
			$clusters = $meta['clusters'] ?? array();
			$packedAll = base64_decode((string) ($meta['graph_b64'] ?? ''), true);
			if ($packedAll === false) {
				throw new RuntimeException('bio undo: graph_b64 invalid');
			}
			foreach ($clusters as $ci => $cl) {
				$clusterBase = (int) $cl['off'];
				$consensus = substr($textBlob, $clusterBase, (int) $cl['consensus_len']);
				$graphBlob = substr($packedAll, (int) $cl['graph_off'], (int) $cl['graph_len']);
				$graph = fractal_zip_bio_pangenome_unpack($graphBlob);
				$outboard = !empty($graph['outboard']);
				foreach ($graph['paths'] ?? array() as $path) {
					$member = (int) ($path['member'] ?? -1);
					if ($member < 0 || $member >= $n) {
						continue;
					}
					$text = $consensus;
					foreach ($path['nodes'] ?? array() as $nid) {
						if ((int) $nid === 0) {
							continue;
						}
						foreach ($graph['nodes'] ?? array() as $node) {
							if ((int) ($node['id'] ?? -1) !== (int) $nid) {
								continue;
							}
							$payload = (string) ($node['seq'] ?? '');
							if ($outboard) {
								$poff = (int) ($node['payload_off'] ?? 0);
								$plen = (int) ($node['payload_len'] ?? 0);
								$payload = substr($textBlob, $clusterBase + $poff, $plen);
							}
							if (($node['type'] ?? '') === 'raw') {
								$text = $payload;
								break 2;
							}
							if (($node['type'] ?? '') === 'delta') {
								require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';
								$dec = fractal_zip_multishift_delta_decode(
									$payload,
									0,
									$consensus,
									(int) ($node['shift'] ?? 0),
									(int) ($node['member_len'] ?? 0)
								);
								$text = $dec[0];
								break 2;
							}
							if (($node['type'] ?? '') !== 'variation') {
								continue;
							}
							$vc = (int) ($node['var_count'] ?? 0);
							$vars = fractal_zip_bio_vcf_decode($payload, $vc);
							$text = fractal_zip_bio_vcf_apply($text, $vars);
						}
					}
					$out[$member] = $text;
				}
			}
			for ($i = 0; $i < $n; $i++) {
				if ($out[$i] === '') {
					throw new RuntimeException('bio undo: pangenome missing page ' . $i);
				}
			}
			return $out;
		default:
			throw new InvalidArgumentException('unknown bio layout undo: ' . $layout);
	}
}

/** @return list<string> */
function fractal_zip_bio_layout_catalog(): array
{
	return array(
		'bio_minhash_reorder',
		'bio_piecewise_shift',
		'bio_progressive',
		'bio_synteny',
		'bio_pangenome',
		'bio_minhash_piecewise',
	);
}

/**
 * Compose MinHash reorder then piecewise chained alignment (tournament ordering).
 *
 * @param list<array{text:string}> $pages
 */
function fractal_zip_bio_minhash_piecewise_build(array $pages, array $opts = array()): array
{
	$perm = fractal_zip_bio_minhash_reorder_perm($pages);
	$ordered = array();
	foreach ($perm as $idx) {
		$ordered[] = $pages[(int) $idx];
	}
	$built = fractal_zip_bio_piecewise_align_build($ordered, $opts);
	// Remap record indices from ordered positions back to original page indices.
	$sidecar = (string) $built['sidecar'];
	$ml = strlen(FRACTAL_ZIP_BIO_ALIGN_MAGIC);
	if (strncmp($sidecar, FRACTAL_ZIP_BIO_ALIGN_MAGIC, $ml) === 0) {
		// Rebuild sidecar with remapped indices via unpack/repack.
		$pos = $ml;
		$readU = static function () use ($sidecar, &$pos): int {
			$d = fractal_zip_enwik_decode_varint_u32($sidecar, $pos);
			$pos = (int) $d[1];
			return (int) $d[0];
		};
		$n = $readU();
		$refIdxOrdered = $readU();
		$refIdx = (int) $perm[$refIdxOrdered];
		$recCount = $readU();
		$records = array();
		for ($r = 0; $r < $recCount; $r++) {
			$kind = ord($sidecar[$pos]);
			$pos++;
			$idxOrdered = $readU();
			$idx = (int) $perm[$idxOrdered];
			if ($kind === 0) {
				$records[] = array('kind' => 0, 'idx' => $idx, 'len' => $readU());
			} elseif ($kind === 1) {
				$sc = $readU();
				$segs = array();
				for ($s = 0; $s < $sc; $s++) {
					$zz = fractal_zip_multishift_zigzag_decode($sidecar, $pos);
					$pos = (int) $zz[1];
					$segs[] = array(
						'shift' => (int) $zz[0],
						'ref_start' => $readU(),
						'ref_end' => $readU(),
						'query_start' => $readU(),
						'query_end' => $readU(),
					);
				}
				$records[] = array('kind' => 1, 'idx' => $idx, 'segments' => $segs, 'payload_len' => $readU());
			} elseif ($kind === 2) {
				$records[] = array('kind' => 2, 'idx' => $idx, 'var_count' => $readU(), 'vcf_len' => $readU());
			} elseif ($kind === 3) {
				$records[] = array('kind' => 3, 'idx' => $idx, 'len' => $readU());
			} elseif ($kind === 4) {
				$zz = fractal_zip_multishift_zigzag_decode($sidecar, $pos);
				$pos = (int) $zz[1];
				$records[] = array(
					'kind' => 4,
					'idx' => $idx,
					'shift' => (int) $zz[0],
					'member_len' => $readU(),
					'payload_len' => $readU(),
				);
			} elseif ($kind === 5) {
				$records[] = array('kind' => 5, 'idx' => $idx);
			}
		}
		$sidecar = fractal_zip_bio_align_pack_sidecar($n, $refIdx, $records);
	}
	return array(
		'blob' => (string) $built['blob'],
		'sidecar' => $sidecar,
		'meta' => array('layout' => 'bio_minhash_piecewise', 'perm' => $perm, 'sidecar_b64' => base64_encode($sidecar)),
	);
}
