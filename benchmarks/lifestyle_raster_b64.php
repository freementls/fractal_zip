<?php
/**
 * Shared 1×1 PNG + tiny JPEG (wire bytes) for test_files76 / build_test_files74_75_76.php.
 * @return array{png: string, jpg: string} binary strings (may be empty on decode failure)
 */
function lifestyle_raster_blobs(): array
{
	$png1x1 = base64_decode(
		'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYPhfDwAChwGA60e6HlkAAAAASUVORK5CYII=',
		true
	);
	$jpg = base64_decode(
		'/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAT/xAAdEAACAgMAAwAAAAAAAAAAAAABAgADERASITH/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8Al1//2Q==',
		true
	);
	return array(
		'png' => is_string($png1x1) ? $png1x1 : '',
		'jpg' => is_string($jpg) ? $jpg : '',
	);
}
