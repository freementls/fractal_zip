<?php

declare(strict_types=1);

/**
 * Regression: merged-FZCD PCM reversible pre-transforms round-trip apply → inverse
 * for every strategy returned by `fractal_zip_pcm_pretransform_strategies_for`.
 *
 * Run: php benchmarks/fzcd_pcm_pretransform_smoke.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
fractal_zip_ensure_flac_pac_loaded();

/**
 * @param non-empty-string $pcm
 */
function fzcd_pcm_pre_smoke_rt(string $pcm, int $pcmFmt, int $channels): void {
	$ids = fractal_zip_pcm_pretransform_strategies_for($pcmFmt, $channels);
	foreach ($ids as $sid) {
		$t = fractal_zip_pcm_pretransform_apply($pcm, $pcmFmt, $channels, $sid);
		if ($t === null) {
			fwrite(STDERR, "FAIL: apply returned null fmt={$pcmFmt} ch={$channels} sid={$sid}\n");
			exit(1);
		}
		$b = fractal_zip_pcm_pretransform_inverse($t, $pcmFmt, $channels, $sid);
		if ($b !== $pcm) {
			fwrite(STDERR, "FAIL: round-trip fmt={$pcmFmt} ch={$channels} sid={$sid}\n");
			exit(1);
		}
	}
}

$pcm16 = '';
for ($i = 0; $i < 80; $i++) {
	$pcm16 .= fractal_zip_pcm_pack_i16(($i * 17) % 6000 - 2000) . fractal_zip_pcm_pack_i16(($i * 31) % 8000 - 4000);
}
fzcd_pcm_pre_smoke_rt($pcm16, FRACTAL_ZIP_FPCM_FMT_S16LE, 2);

$pcm32 = '';
for ($i = 0; $i < 40; $i++) {
	$pcm32 .= fractal_zip_pcm_pack_i32($i * 111111) . fractal_zip_pcm_pack_i32(-$i * 222222);
}
fzcd_pcm_pre_smoke_rt($pcm32, FRACTAL_ZIP_FPCM_FMT_S32LE, 2);

$mono = str_repeat("\x00\x12", 100);
fzcd_pcm_pre_smoke_rt($mono, FRACTAL_ZIP_FPCM_FMT_S16LE, 1);

$pcm16_4ch = '';
for ($i = 0; $i < 40; $i++) {
	for ($c = 0; $c < 4; $c++) {
		$pcm16_4ch .= fractal_zip_pcm_pack_i16(($i + $c * 11) * 50 - 1000);
	}
}
fzcd_pcm_pre_smoke_rt($pcm16_4ch, FRACTAL_ZIP_FPCM_FMT_S16LE, 4);

fwrite(STDOUT, "OK fzcd_pcm_pretransform_smoke (stereo s16/s32, mono s16, quad s16).\n");
exit(0);
