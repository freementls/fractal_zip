<?php
declare(strict_types=1);

/**
 * Hutter Prize rules per http://prize.hutter1.net/ and http://prize.hutter1.net/hrules.htm
 *
 * enwik8 record (Rhatushnyak, Nov 2017): archive 15_242_496 + decomp 42_448 = 15_284_944 B
 *
 * hrules.htm (enwik9 template — use for enwik8 stretch / enwik9 submission):
 *   Combined:  S = length(comp) + length(archive)   (self-extracting archive)
 *   Split:     S = length(comp) + 2×length(decomp) + length(archive)
 *                (if comp = decomp, 2× reduces to 1×)
 *   CLI options required for run/compile count toward S.
 *   Linux/Windows x86 32/64-bit; no runtime external input; ≤10 GiB RAM; ≤100 GiB temp disk;
 *   wall time ≤ 70_000/Geekbench5 hours; no GPU; single-core scoring.
 *   Source zip + makefile may replace binaries (C++/Python/Assembler); OSI license for payout.
 *   Minimum record improvement ~1 MiB for award eligibility (~1% of fund).
 */

const ENWIK8_HUTTER_CORPUS_BYTES = 100_000_000;
const ENWIK8_HUTTER_RECORD_TOTAL_L = 15_284_944;
const ENWIK8_HUTTER_RECORD_ARCHIVE = 15_242_496;
const ENWIK8_HUTTER_RECORD_DECOMP = 42_448;
const ENWIK8_HUTTER_RAM_MB_ENWIK8 = 1048;
const ENWIK8_HUTTER_DECOMP_SECONDS_REF = 18_000;

/** cmix-class stretch target (sub1 matrix archive); beat146_hardware uses this as total S bar. */
const ENWIK8_CMIX_ARCHIVE_LAB = 14_623_723;
/** phda9 raw-enwik8 squash archive in our bench (archive only). */
const ENWIK8_HUTTER_PAQ_SQUASH_ARCHIVE = 15_010_414;

/** enwik9 rules (http://prize.hutter1.net/hrules.htm) — same limits for enwik8 stretch prep. */
const ENWIK8_HUTTER_RAM_MB_MAX = 10_240;
const ENWIK8_HUTTER_TEMP_DISK_GB_MAX = 100;
const ENWIK8_HUTTER_GEEKBENCH5_REF_SINGLE = 1427;
const ENWIK8_HUTTER_TIME_BUDGET_HOURS_NUM = 70_000;
/** ~1 MiB minimum improvement for award (hrules: 1% of fund). */
const ENWIK8_HUTTER_MIN_RECORD_IMPROVEMENT_BYTES = 1_048_576;
/** Award ≈ 1 € per 230 B improvement (hrules “More Information”). */
const ENWIK8_HUTTER_AWARD_EUROS_PER_BYTE = 1.0 / 230.0;

/** S = archive + decompressor (published enwik8 record shape). */
const ENWIK8_HUTTER_ACCOUNTING_ENWIK8_RECORD = 'enwik8_record';
/** S = comp + archive (self-extracting; hrules combined). */
const ENWIK8_HUTTER_ACCOUNTING_HRULES_COMBINED = 'hrules_combined';
/** S = comp + 2×decomp + archive (+ bundled dict/cli); hrules split — default for enwik9 prep. */
const ENWIK8_HUTTER_ACCOUNTING_HRULES_SPLIT = 'hrules_split';
/**
 * LTCB table (https://mattmahoney.net/dc/text.html): enwik8 archive + decompresser zip.
 * Ranking column uses enwik9 archive + decompresser zip; same shape.
 */
const ENWIK8_HUTTER_ACCOUNTING_LTCB = 'ltcb';

/** enwik8 is exactly 100_000_000 B; enwik9 is 1_000_000_000 B (decimal). */
const ENWIK8_CORPUS_BYTES = 100_000_000;
const ENWIK9_CORPUS_BYTES = 1_000_000_000;

/** cmix v21 decompresser zip on Mahoney LTCB (Jun 2026 table). */
const ENWIK8_CMIX_DECOMP_ZIP_LTCB = 281_387;
/** phda9 1.8 decompresser zip on Mahoney LTCB. */
const ENWIK8_PHDA9_DECOMP_ZIP_LTCB = 42_944;

const ENWIK8_HUTTER_TARGET_HUTTER_TOTAL = 'hutter_total';
const ENWIK8_HUTTER_TARGET_LTCB_RANK = 'ltcb_rank';
const ENWIK8_HUTTER_TARGET_HUTTER_ARCHIVE = 'hutter_archive';
const ENWIK8_HUTTER_TARGET_CMIX_LAB = 'cmix_lab';
/** Primary stretch: total S ≤ 14.6 MiB under Hutter hardware rules (enwik9 prep). */
const ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE = 'beat146_hardware';

/**
 * Reference test machines (hrules.htm, 2021 — may change without notice).
 *
 * @return list<array{label: string, os: string, gb5_single: float, gb5_multi: float}>
 */
function enwik8_hutter_reference_test_machines(): array
{
	return array(
		array('label' => 'Lenovo 82HT i7-1165G7', 'os' => 'Windows', 'gb5_single' => 1427.0, 'gb5_multi' => 4667.0),
		array('label' => 'AMD Ryzen 7 3.6GHz', 'os' => 'Linux', 'gb5_single' => 1310.0, 'gb5_multi' => 8228.0),
	);
}

/**
 * Which S accounting formula applies per gate target.
 */
function enwik8_hutter_accounting_mode_for_target(string $targetMode): string
{
	return match ($targetMode) {
		ENWIK8_HUTTER_TARGET_HUTTER_TOTAL => ENWIK8_HUTTER_ACCOUNTING_ENWIK8_RECORD,
		ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE => ENWIK8_HUTTER_ACCOUNTING_HRULES_SPLIT,
		ENWIK8_HUTTER_TARGET_LTCB_RANK => ENWIK8_HUTTER_ACCOUNTING_LTCB,
		default => ENWIK8_HUTTER_ACCOUNTING_HRULES_COMBINED,
	};
}

/**
 * @param array{
 *   archive_bytes: int,
 *   comp_bytes?: int,
 *   decomp_bytes?: int,
 *   decomp_count?: int,
 *   dict_bytes?: int,
 *   cli_option_bytes?: int,
 *   comp_equals_decomp?: bool
 * } $parts
 * @return array{
 *   mode: string,
 *   total_s: int,
 *   comp_bytes: int,
 *   decomp_bytes: int,
 *   decomp_multiplier: int,
 *   archive_bytes: int,
 *   dict_bytes: int,
 *   cli_option_bytes: int,
 *   formula: string
 * }
 */
function enwik8_hutter_compute_total_s(string $mode, array $parts): array
{
	$archive = max(0, (int) ($parts['archive_bytes'] ?? 0));
	$comp = max(0, (int) ($parts['comp_bytes'] ?? 0));
	$decomp = max(0, (int) ($parts['decomp_bytes'] ?? 0));
	$dict = max(0, (int) ($parts['dict_bytes'] ?? 0));
	$cli = max(0, (int) ($parts['cli_option_bytes'] ?? 0));
	$decompMult = (int) ($parts['decomp_count'] ?? 1);
	$compEqDecomp = !empty($parts['comp_equals_decomp']);

	switch ($mode) {
		case ENWIK8_HUTTER_ACCOUNTING_HRULES_SPLIT:
			$mult = $compEqDecomp ? 1 : 2;
			$total = $comp + $mult * $decomp + $archive + $dict + $cli;
			$formula = 'comp + ' . $mult . '×decomp + archive + dict + cli';
			return array(
				'mode' => $mode,
				'total_s' => $total,
				'comp_bytes' => $comp,
				'decomp_bytes' => $decomp,
				'decomp_multiplier' => $mult,
				'archive_bytes' => $archive,
				'dict_bytes' => $dict,
				'cli_option_bytes' => $cli,
				'formula' => $formula,
			);
		case ENWIK8_HUTTER_ACCOUNTING_HRULES_COMBINED:
			$total = $comp + $archive + $dict + $cli;
			return array(
				'mode' => $mode,
				'total_s' => $total,
				'comp_bytes' => $comp,
				'decomp_bytes' => $decomp,
				'decomp_multiplier' => 0,
				'archive_bytes' => $archive,
				'dict_bytes' => $dict,
				'cli_option_bytes' => $cli,
				'formula' => 'comp + archive + dict + cli',
			);
		case ENWIK8_HUTTER_ACCOUNTING_LTCB:
			// Mahoney zips decompresser (+ dict); lab uses raw decomp bytes as zip proxy when unset.
			$total = $archive + $decomp + $dict + $cli;
			return array(
				'mode' => ENWIK8_HUTTER_ACCOUNTING_LTCB,
				'total_s' => $total,
				'comp_bytes' => $comp,
				'decomp_bytes' => $decomp,
				'decomp_multiplier' => 1,
				'archive_bytes' => $archive,
				'dict_bytes' => $dict,
				'cli_option_bytes' => $cli,
				'formula' => 'archive + decompresser_zip (LTCB; dict in zip)',
			);
		case ENWIK8_HUTTER_ACCOUNTING_ENWIK8_RECORD:
		default:
			$total = $archive + $decomp + $dict + $cli;
			return array(
				'mode' => ENWIK8_HUTTER_ACCOUNTING_ENWIK8_RECORD,
				'total_s' => $total,
				'comp_bytes' => $comp,
				'decomp_bytes' => $decomp,
				'decomp_multiplier' => 1,
				'archive_bytes' => $archive,
				'dict_bytes' => $dict,
				'cli_option_bytes' => $cli,
				'formula' => 'archive + decomp + dict + cli (enwik8 record)',
			);
	}
}

/**
 * Participation / submission checklist from hrules.htm (manual items marked pending).
 *
 * @return list<array{id: string, rule: string, lab_status: string, ok: bool}>
 */
function enwik8_hutter_submission_checklist(?bool $verifyOk = null, ?bool $losslessDecompress = null): array
{
	$lossless = $verifyOk === true || $losslessDecompress === true;
	return array(
		array('id' => 'lossless', 'rule' => 'Decompress reproduces corpus byte-identical; no external input at runtime', 'lab_status' => $lossless ? 'verify passed' : 'pending verify', 'ok' => $lossless),
		array('id' => 'platform', 'rule' => 'Linux or Windows x86 32/64-bit executable (or source zip + makefile)', 'lab_status' => 'PHP research path — needs C strip', 'ok' => false),
		array('id' => 'self_contained', 'rule' => 'No files/network/dicts at decompress unless counted in S', 'lab_status' => 'external phda9 dict must embed in archive', 'ok' => false),
		array('id' => 'hardware', 'rule' => '≤10 GiB RAM, ≤100 GiB temp disk, ≤70k/GB5 h per compress AND per decompress, no GPU, single-core', 'lab_status' => 'track on full run', 'ok' => false),
		array('id' => 'osi_source', 'rule' => 'Public OSI-licensed source before payout', 'lab_status' => 'repo license TBD for submission', 'ok' => false),
		array('id' => 'download', 'rule' => 'Free public download of archive + (de)compressor', 'lab_status' => 'n/a until submission', 'ok' => false),
		array('id' => 'one_line_run', 'rule' => 'Single-line execution instruction', 'lab_status' => 'n/a until submission', 'ok' => false),
		array('id' => 'algo_doc', 'rule' => 'Document explaining algorithmic ideas', 'lab_status' => 'partial (docs/)', 'ok' => false),
		array('id' => 'benchmark_specific', 'rule' => 'May be tuned to enwik8/enwik9 only (allowed)', 'lab_status' => 'yes — enwik-specific path', 'ok' => true),
		array('id' => 'min_improvement', 'rule' => '≥~1 MiB below current L for award (~1% fund)', 'lab_status' => 'track Δ vs L', 'ok' => false),
	);
}

/**
 * @return array{total: int, archive: int, decomp: int, label: string, notes: string}
 */
function enwik8_hutter_target_for_mode(string $mode): array
{
	switch ($mode) {
		case ENWIK8_HUTTER_TARGET_HUTTER_ARCHIVE:
			return array(
				'total' => ENWIK8_HUTTER_RECORD_ARCHIVE,
				'archive' => ENWIK8_HUTTER_RECORD_ARCHIVE,
				'decomp' => 0,
				'label' => 'Hutter enwik8 archive (Rhatushnyak 2017)',
				'notes' => 'Lab: compare .fz wire to published archive only; ignores decompressor tax.',
			);
		case ENWIK8_HUTTER_TARGET_CMIX_LAB:
			return array(
				'total' => ENWIK8_CMIX_ARCHIVE_LAB,
				'archive' => ENWIK8_CMIX_ARCHIVE_LAB,
				'decomp' => 0,
				'label' => 'cmix v21 archive (lab matrix)',
				'notes' => 'Archive-only wire comparison; ignores decompressor tax.',
			);
		case ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE:
			return array(
				'total' => ENWIK8_CMIX_ARCHIVE_LAB,
				'archive' => ENWIK8_CMIX_ARCHIVE_LAB,
				'decomp' => 0,
				'label' => 'beat 14.6 MiB total S (Hutter hardware rules)',
				'notes' => 'Stretch: prize-shaped total S ≤ cmix bar + RAM/time/single-core limits. enwik8 proving ground before enwik9.',
			);
		case ENWIK8_HUTTER_TARGET_LTCB_RANK:
			return array(
				'total' => ENWIK8_CMIX_ARCHIVE_LAB + ENWIK8_CMIX_DECOMP_ZIP_LTCB,
				'archive' => ENWIK8_CMIX_ARCHIVE_LAB,
				'decomp' => ENWIK8_CMIX_DECOMP_ZIP_LTCB,
				'label' => 'LTCB #1 enwik8 (cmix v21 archive + decomp zip)',
				'notes' => 'Mahoney table ranks enwik9+decompresser zip; enwik8 uses same decompresser zip column. Time not in ranking.',
			);
		case ENWIK8_HUTTER_TARGET_HUTTER_TOTAL:
		default:
			return array(
				'total' => ENWIK8_HUTTER_RECORD_TOTAL_L,
				'archive' => ENWIK8_HUTTER_RECORD_ARCHIVE,
				'decomp' => ENWIK8_HUTTER_RECORD_DECOMP,
				'label' => 'Hutter enwik8 prize total S (archive+decomp)',
				'notes' => 'Official record L on http://prize.hutter1.net/',
			);
	}
}

/**
 * Measured phda9_no_lstm binary used by integrated path, else published record decomp size.
 */
function enwik8_hutter_decomp_bytes_measured(?string $repoRoot = null): int
{
	$repo = $repoRoot ?? dirname(__DIR__);
	$candidates = array(
		$repo . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phda9' . DIRECTORY_SEPARATOR . 'phda9_no_LSTM',
		$repo . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phda9' . DIRECTORY_SEPARATOR . 'phda9',
	);
	foreach ($candidates as $path) {
		if (is_file($path)) {
			return (int) filesize($path);
		}
	}
	return ENWIK8_HUTTER_RECORD_DECOMP;
}

/**
 * Per-phase wall budget (compress OR decompress): 70_000 / Geekbench5 hours × 3600.
 * hrules.htm and starlit submission text require each program separately under budget.
 */
function enwik8_hutter_hardware_time_budget_seconds_per_phase(?float $geekbench5Single = null): float
{
	$t = $geekbench5Single ?? (float) ENWIK8_HUTTER_GEEKBENCH5_REF_SINGLE;
	$t = max(1.0, $t);
	return (ENWIK8_HUTTER_TIME_BUDGET_HOURS_NUM / $t) * 3600.0;
}

/** @deprecated alias — budget is per phase, not combined compress+decompress */
function enwik8_hutter_hardware_time_budget_seconds(?float $geekbench5Single = null): float
{
	return enwik8_hutter_hardware_time_budget_seconds_per_phase($geekbench5Single);
}

function enwik8_hutter_seconds_to_ns_per_byte(float $seconds, int $corpusBytes = ENWIK8_CORPUS_BYTES): int
{
	if ($corpusBytes <= 0) {
		return 0;
	}
	return (int) round(($seconds * 1e9) / $corpusBytes);
}

function enwik8_hutter_ns_per_byte_to_seconds(int $nsPerByte, int $corpusBytes = ENWIK8_CORPUS_BYTES): float
{
	return ($nsPerByte * $corpusBytes) / 1e9;
}

/**
 * Extrapolate enwik9 hours from measured enwik8 seconds (linear ns/byte scaling).
 *
 * @return array{enwik8_hours: float, enwik9_hours: float, ns_per_byte: int, within_budget: bool, budget_hours: float}
 */
function enwik8_hutter_extrapolate_corpus_hours(
	float $seconds,
	int $fromCorpusBytes = ENWIK8_CORPUS_BYTES,
	int $toCorpusBytes = ENWIK9_CORPUS_BYTES,
	?float $geekbench5Single = null
): array {
	$ns = enwik8_hutter_seconds_to_ns_per_byte($seconds, $fromCorpusBytes);
	$enwik9Sec = enwik8_hutter_ns_per_byte_to_seconds($ns, $toCorpusBytes);
	$budgetH = enwik8_hutter_hardware_time_budget_seconds_per_phase($geekbench5Single) / 3600.0;
	return array(
		'enwik8_hours' => $seconds / 3600.0,
		'enwik9_hours' => $enwik9Sec / 3600.0,
		'ns_per_byte' => $ns,
		'within_budget' => ($enwik9Sec / 3600.0) <= $budgetH,
		'budget_hours' => $budgetH,
	);
}

/**
 * LTCB leaders vs Hutter prize outcome (Mahoney text.html, Jun 2026).
 * Top LTCB rows often beat on size but fail 70k/GB5 h per phase on enwik9.
 *
 * @return list<array<string, mixed>>
 */
function enwik8_ltcb_reference_rows(): array
{
	$budgetH = enwik8_hutter_hardware_time_budget_seconds_per_phase() / 3600.0;
	$rows = array(
		array(
			'program' => 'cmix v21',
			'enwik8_archive' => 14_623_723,
			'decomp_zip' => 281_387,
			'comp_ns_b' => 622_949,
			'decomp_ns_b' => 638_442,
			'ltcb_prize' => 'table #1; no LTCB prize money',
			'hutter_enwik9' => 'too slow (~173 h comp & decomp each @ enwik9)',
			'hutter_won' => false,
		),
		array(
			'program' => 'nncp v3.2',
			'enwik8_archive' => 14_915_298,
			'decomp_zip' => 628_955,
			'comp_ns_b' => 241_871,
			'decomp_ns_b' => 238_670,
			'ltcb_prize' => 'table #2; no LTCB prize money',
			'hutter_enwik9' => 'too slow (~67 h each @ enwik9)',
			'hutter_won' => false,
		),
		array(
			'program' => 'fast-cmix-hp',
			'enwik8_archive' => null,
			'decomp_zip' => 0,
			'comp_ns_b' => null,
			'decomp_ns_b' => 121_971,
			'ltcb_prize' => 'enwik9 SFX; Mahoney: ~53 h extract failed margin',
			'hutter_enwik9' => 'won €5187 (2023) — speed-optimized cmix-hp',
			'hutter_won' => true,
		),
		array(
			'program' => 'starlit',
			'enwik8_archive' => 15_215_107,
			'decomp_zip' => 0,
			'comp_ns_b' => 173_953,
			'decomp_ns_b' => 171_682,
			'ltcb_prize' => 'SFX decomp zip=0; ~48 h comp+decomp each on ref i7',
			'hutter_enwik9' => 'won €9000 (2021)',
			'hutter_won' => true,
		),
		array(
			'program' => 'phda9 1.8',
			'enwik8_archive' => 15_010_414,
			'decomp_zip' => 42_944,
			'comp_ns_b' => 86_182,
			'decomp_ns_b' => 86_305,
			'ltcb_prize' => 'PAQ-class: comp ≈ decomp ns/b (Mahoney note 5)',
			'hutter_enwik9' => 'baseline; enwik8 record ~5 h total',
			'hutter_won' => true,
		),
		array(
			'program' => 'fx-cmix',
			'enwik8_archive' => null,
			'decomp_zip' => 0,
			'comp_ns_b' => null,
			'decomp_ns_b' => null,
			'ltcb_prize' => 'Mahoney: ~60 h enwik9 extract — over budget',
			'hutter_enwik9' => 'won €6911 (2024) on committee machine',
			'hutter_won' => true,
		),
	);
	foreach ($rows as &$row) {
		if (isset($row['comp_ns_b']) && $row['comp_ns_b'] !== null) {
			$row['enwik9_comp_h'] = round(
				enwik8_hutter_ns_per_byte_to_seconds((int) $row['comp_ns_b'], ENWIK9_CORPUS_BYTES) / 3600.0,
				1
			);
			$row['enwik9_decomp_h'] = round(
				enwik8_hutter_ns_per_byte_to_seconds((int) $row['decomp_ns_b'], ENWIK9_CORPUS_BYTES) / 3600.0,
				1
			);
			$row['enwik9_comp_ok'] = $row['enwik9_comp_h'] <= $budgetH;
			$row['enwik9_decomp_ok'] = $row['enwik9_decomp_h'] <= $budgetH;
		}
	}
	unset($row);
	return $rows;
}

/**
 * Archive bytes allowed so total S ≤ target given fixed decomp + dict tax.
 */
function enwik8_hutter_archive_budget_for_total_s(
	int $totalTarget,
	?string $repoRoot = null,
	?int $decompBytes = null,
	string $accountingMode = ENWIK8_HUTTER_ACCOUNTING_HRULES_SPLIT
): array {
	$submission = enwik8_hutter_submission_total(0, $repoRoot, $decompBytes, true, $accountingMode);
	$tax = $submission['total_s'];
	$budget = $totalTarget - $tax;
	return array(
		'archive_budget' => $budget,
		'decomp_bytes' => $submission['decomp_bytes'],
		'dict_bytes' => $submission['dict_bytes'],
		'tax_bytes' => $tax,
		'total_target' => $totalTarget,
		'accounting_mode' => $submission['accounting_mode'],
	);
}

/**
 * @param array{
 *   peak_ram_mb?: ?float,
 *   temp_disk_mb?: ?float,
 *   wall_seconds?: ?float,
 *   compress_seconds?: ?float,
 *   decompress_seconds?: ?float,
 *   geekbench5_single?: ?float,
 *   single_core?: ?bool
 * } $metrics
 * @return array{
 *   ok: bool,
 *   issues: list<string>,
 *   time_budget_s: float,
 *   time_budget_hours: float,
 *   compress_ok: ?bool,
 *   decompress_ok: ?bool,
 *   compress_extrap_enwik9_h: ?float,
 *   decompress_extrap_enwik9_h: ?float,
 *   ram_mb_max: int,
 *   temp_disk_gb_max: int
 * }
 */
function enwik8_hutter_hardware_audit(array $metrics): array
{
	$gb5 = isset($metrics['geekbench5_single']) ? (float) $metrics['geekbench5_single'] : (float) ENWIK8_HUTTER_GEEKBENCH5_REF_SINGLE;
	$budget = enwik8_hutter_hardware_time_budget_seconds_per_phase($gb5);
	$budgetH = $budget / 3600.0;
	$issues = array();
	$compressOk = null;
	$decompressOk = null;
	$compressExtrap = null;
	$decompressExtrap = null;

	if (isset($metrics['peak_ram_mb']) && $metrics['peak_ram_mb'] !== null
		&& $metrics['peak_ram_mb'] > ENWIK8_HUTTER_RAM_MB_MAX) {
		$issues[] = sprintf('peak RAM %.0f MiB > %s MiB limit', $metrics['peak_ram_mb'], number_format(ENWIK8_HUTTER_RAM_MB_MAX));
	}
	if (isset($metrics['temp_disk_mb']) && $metrics['temp_disk_mb'] !== null
		&& $metrics['temp_disk_mb'] > ENWIK8_HUTTER_TEMP_DISK_GB_MAX * 1024) {
		$issues[] = sprintf('temp disk %.1f GiB > %d GiB limit', $metrics['temp_disk_mb'] / 1024, ENWIK8_HUTTER_TEMP_DISK_GB_MAX);
	}

	$compressSec = $metrics['compress_seconds'] ?? $metrics['wall_seconds'] ?? null;
	$decompressSec = $metrics['decompress_seconds'] ?? null;

	if ($compressSec !== null) {
		$extrap = enwik8_hutter_extrapolate_corpus_hours((float) $compressSec);
		$compressExtrap = $extrap['enwik9_hours'];
		$compressOk = (float) $compressSec <= $budget;
		if (!$compressOk) {
			$issues[] = sprintf(
				'compress %.1f h > per-phase budget %.1f h (GB5=%.0f)',
				(float) $compressSec / 3600,
				$budgetH,
				$gb5
			);
		}
	}
	if ($decompressSec !== null) {
		$extrap = enwik8_hutter_extrapolate_corpus_hours((float) $decompressSec);
		$decompressExtrap = $extrap['enwik9_hours'];
		$decompressOk = (float) $decompressSec <= $budget;
		if (!$decompressOk) {
			$issues[] = sprintf(
				'decompress %.1f h > per-phase budget %.1f h (GB5=%.0f)',
				(float) $decompressSec / 3600,
				$budgetH,
				$gb5
			);
		}
	}
	if ($compressExtrap !== null && $compressExtrap > $budgetH) {
		$issues[] = sprintf(
			'compress extrapolated enwik9 %.1f h > budget %.1f h (linear ns/byte)',
			$compressExtrap,
			$budgetH
		);
		$compressOk = false;
	}
	if ($decompressExtrap !== null && $decompressExtrap > $budgetH) {
		$issues[] = sprintf(
			'decompress extrapolated enwik9 %.1f h > budget %.1f h (linear ns/byte)',
			$decompressExtrap,
			$budgetH
		);
		$decompressOk = false;
	}

	if (isset($metrics['single_core']) && $metrics['single_core'] === false) {
		$issues[] = 'GPU or multi-core-only path — prize rules require single-core CPU';
	}
	return array(
		'ok' => $issues === array(),
		'issues' => $issues,
		'time_budget_s' => $budget,
		'time_budget_hours' => $budgetH,
		'compress_ok' => $compressOk,
		'decompress_ok' => $decompressOk,
		'compress_extrap_enwik9_h' => $compressExtrap,
		'decompress_extrap_enwik9_h' => $decompressExtrap,
		'ram_mb_max' => ENWIK8_HUTTER_RAM_MB_MAX,
		'temp_disk_gb_max' => ENWIK8_HUTTER_TEMP_DISK_GB_MAX,
	);
}

function enwik8_hutter_uses_total_s_accounting(string $targetMode): bool
{
	return $targetMode === ENWIK8_HUTTER_TARGET_HUTTER_TOTAL
		|| $targetMode === ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE
		|| $targetMode === ENWIK8_HUTTER_TARGET_LTCB_RANK;
}

/**
 * External phda9 dictionary bytes (must be bundled in S if used at decompress).
 *
 * @return array{bytes: int, path: ?string, embedded_in_archive: bool}
 */
function enwik8_hutter_dict_accounting(?string $repoRoot = null): array
{
	$repo = $repoRoot ?? dirname(__DIR__);
	require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';
	$default = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
	$env = getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
	if ($env === '') {
		return array('bytes' => 0, 'path' => null, 'embedded_in_archive' => false);
	}
	$path = fractal_zip_phda9_dict_path_from_env();
	if ($path === null && is_file($default)) {
		$path = $default;
	}
	if ($path === null || !is_file($path)) {
		return array('bytes' => 0, 'path' => null, 'embedded_in_archive' => false);
	}
	$embedded = getenv('FRACTAL_ZIP_HUTTER_EMBED_DICT') === '1';
	return array(
		'bytes' => (int) filesize($path),
		'path' => $path,
		'embedded_in_archive' => $embedded,
	);
}

/**
 * Prize-shaped size accounting for lab integrated path.
 *
 * @return array{
 *   archive_bytes: int,
 *   comp_bytes: int,
 *   decomp_bytes: int,
 *   dict_bytes: int,
 *   cli_option_bytes: int,
 *   total_s: int,
 *   accounting: array<string, mixed>,
 *   accounting_mode: string,
 *   decomp_measured: bool,
 *   dict_path: ?string
 * }
 */
function enwik8_hutter_submission_total(
	int $archiveBytes,
	?string $repoRoot = null,
	?int $decompBytes = null,
	bool $countDictUnlessEmbedded = true,
	string $accountingMode = ENWIK8_HUTTER_ACCOUNTING_HRULES_SPLIT,
	int $compBytes = 0,
	int $cliOptionBytes = 0,
	bool $compEqualsDecomp = false
): array {
	$repo = $repoRoot ?? dirname(__DIR__);
	$decomp = $decompBytes ?? enwik8_hutter_decomp_bytes_measured($repo);
	$dictInfo = enwik8_hutter_dict_accounting($repo);
	$dictBytes = ($countDictUnlessEmbedded && !$dictInfo['embedded_in_archive']) ? $dictInfo['bytes'] : 0;
	$accounting = enwik8_hutter_compute_total_s($accountingMode, array(
		'archive_bytes' => $archiveBytes,
		'comp_bytes' => $compBytes,
		'decomp_bytes' => $decomp,
		'dict_bytes' => $dictBytes,
		'cli_option_bytes' => $cliOptionBytes,
		'comp_equals_decomp' => $compEqualsDecomp,
	));
	return array(
		'archive_bytes' => $archiveBytes,
		'comp_bytes' => $compBytes,
		'decomp_bytes' => $decomp,
		'dict_bytes' => $dictBytes,
		'cli_option_bytes' => $cliOptionBytes,
		'total_s' => (int) $accounting['total_s'],
		'accounting' => $accounting,
		'accounting_mode' => $accountingMode,
		'decomp_measured' => $decompBytes === null,
		'dict_path' => $dictInfo['path'],
	);
}

/**
 * @return array{
 *   compliant: bool,
 *   issues: list<string>,
 *   verify_ok: ?bool,
 *   lossless: bool,
 *   external_dict_bytes: int,
 *   external_dict_path: ?string,
 *   submission: array<string, mixed>,
 *   target_mode: string,
 *   delta_vs_target: int,
 *   beats_record: bool
 * }
 */
function enwik8_hutter_compliance_audit(
	int $archiveBytes,
	?bool $verifyOk = null,
	string $targetMode = ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE,
	?string $repoRoot = null,
	array $metrics = array()
): array {
	$repo = $repoRoot ?? dirname(__DIR__);
	$target = enwik8_hutter_target_for_mode($targetMode);
	$acctMode = enwik8_hutter_accounting_mode_for_target($targetMode);
	$submission = enwik8_hutter_submission_total($archiveBytes, $repo, null, true, $acctMode);
	$compareBytes = enwik8_hutter_uses_total_s_accounting($targetMode)
		? $submission['total_s']
		: $archiveBytes;
	$targetBytes = $target['total'];
	$delta = $compareBytes - $targetBytes;
	$issues = array();
	if ($verifyOk !== true) {
		$issues[] = 'lossless verify not passed (must reproduce enwik8 byte-identical)';
	}
	$dict = enwik8_hutter_dict_accounting($repo);
	if ($dict['bytes'] > 0 && !$dict['embedded_in_archive']) {
		$issues[] = sprintf(
			'external phda9 dict %s B at decompress (%s) — must be bundled in S or embedded in archive',
			number_format($dict['bytes']),
			$dict['path'] ?? '?'
		);
	}
	$decompMeasured = enwik8_hutter_decomp_bytes_measured($repo);
	if ($decompMeasured > ENWIK8_HUTTER_RECORD_DECOMP * 2) {
		$issues[] = sprintf(
			'measured decompressor %s B >> Rhatushnyak %s B — integrated path is not prize-shaped without a minimal decomp stub',
			number_format($decompMeasured),
			number_format(ENWIK8_HUTTER_RECORD_DECOMP)
		);
	}
	if ($archiveBytes <= 0) {
		$issues[] = 'missing archive / .fz bytes';
	}
	$issues[] = 'submission must be Linux/Windows x86 executable or hrules source zip (not PHP pipeline)';
	if ($acctMode === ENWIK8_HUTTER_ACCOUNTING_HRULES_SPLIT && ($submission['comp_bytes'] ?? 0) === 0) {
		$issues[] = 'split-mode S includes compressor bytes — plan minimal C comp for enwik9';
	}
	$deltaVsL = $compareBytes - $targetBytes;
	if ($deltaVsL >= 0) {
		$issues[] = sprintf('does not beat target L (%s%s B)', $deltaVsL > 0 ? '+' : '', number_format($deltaVsL));
	} elseif (-$deltaVsL < ENWIK8_HUTTER_MIN_RECORD_IMPROVEMENT_BYTES) {
		$issues[] = sprintf(
			'improvement %s B < ~1 MiB minimum for award eligibility',
			number_format(-$deltaVsL)
		);
	}
	$hw = enwik8_hutter_hardware_audit(array(
		'peak_ram_mb' => $metrics['peak_ram_mb'] ?? null,
		'temp_disk_mb' => $metrics['temp_disk_mb'] ?? null,
		'wall_seconds' => $metrics['wall_seconds'] ?? null,
		'compress_seconds' => $metrics['compress_seconds'] ?? $metrics['wall_seconds'] ?? null,
		'decompress_seconds' => $metrics['decompress_seconds'] ?? null,
		'geekbench5_single' => $metrics['geekbench5_single'] ?? null,
		'single_core' => $metrics['single_core'] ?? null,
	));
	if ($targetMode === ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE && !$hw['ok']) {
		$issues = array_merge($issues, $hw['issues']);
	}
	$checklist = enwik8_hutter_submission_checklist($verifyOk);
	return array(
		'compliant' => $issues === array() && $delta < 0,
		'issues' => $issues,
		'verify_ok' => $verifyOk,
		'lossless' => $verifyOk === true,
		'external_dict_bytes' => $dict['bytes'],
		'external_dict_path' => $dict['path'],
		'submission' => $submission,
		'accounting_mode' => $acctMode,
		'accounting_formula' => $submission['accounting']['formula'] ?? '',
		'target_mode' => $targetMode,
		'target' => $target,
		'compare_bytes' => $compareBytes,
		'target_bytes' => $targetBytes,
		'delta_vs_target' => $delta,
		'beats_record' => $verifyOk === true && $delta < 0,
		'hardware' => $hw,
		'submission_checklist' => $checklist,
		'award_eligible_improvement' => -$deltaVsL >= ENWIK8_HUTTER_MIN_RECORD_IMPROVEMENT_BYTES,
	);
}
