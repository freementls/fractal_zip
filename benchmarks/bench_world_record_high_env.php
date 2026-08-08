<?php
declare(strict_types=1);

/**
 * World-record **high** tier: same enwik topology as bench_world_record_env.php, but pushes fz
 * search depth toward ultra defaults that the base preset intentionally caps for ~1h encodes.
 *
 * Insights folded in:
 * - **phda9 / Hutter:** raw-order statistical modeling wins headline (separate squash bench);
 *   sorted path must maximize outer + path-order budget on the virtual folder.
 * - **zpaq:** method 9 + full high-method ladder on 100 MiB inners (already in WR base).
 * - **Squash-style outer predict:** base WR leaves probe prefix at 8 MiB (ultra allows 8 MiB default);
 *   high tier raises probe bytes + L3 high-tier probes + longer predict timeout.
 * - **FZBM path-order:** base WR sets RANDOM_TRIES=128; ultra default is 512 — high restores 512.
 *
 * Use for ~1–2h single encode experiments (no raw phda9 compare in encode_only unless set).
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

/** Force-set keys (overrides bench_world_record_apply_enwik_tuned caps). */
function bench_world_record_apply_high_env(): void
{
	// Path-order: match ultra-scale FZBM search (world-record uses 128 for speed).
	putenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES=512');
	putenv('FRACTAL_ZIP_PATH_ORDER_LGWIN_SWEEP_MAX_CAND=32');

	// Outer prediction on 100 MiB-scale inners (probe prefix was 8 MiB by default).
	putenv('FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES=134217728');
	putenv('FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC=60');
	putenv('FRACTAL_ZIP_OUTER_PREDICT_LAYER3_HIGH=1');

	// Explicit zpaq method ladder (ensures 9/8/7 run before 6…3 on sweep paths).
	putenv('FRACTAL_ZIP_ZPAQ_OUTER_METHODS=9,8,7,6,5,4,3');

	// Deeper fractal multipass (more passes for small gains).
	putenv('FRACTAL_ZIP_IMPROVEMENT_THRESHOLD=0.005');
	putenv('FRACTAL_ZIP_MULTIPASS_GATE_MULT=1');

	// Full outer tournament (redundant with ultra but explicit for A/B logs).
	putenv('FRACTAL_ZIP_DISABLE_OUTER_PRESCREEN=1');
	putenv('FRACTAL_ZIP_OUTER_EARLY_STOP_DYNAMIC=0');
}
