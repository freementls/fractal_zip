<?php
declare(strict_types=1);

/**
 * Built-in server router for the desktop payload.
 * Docroot is the payload root (library + examples/ + hub/).
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$file = __DIR__ . $uri;

if ($uri === '/' || $uri === '') {
	require __DIR__ . '/hub/index.php';
	return true;
}

if ($uri === '/hub' || $uri === '/hub/') {
	require __DIR__ . '/hub/index.php';
	return true;
}

if (is_file($file)) {
	return false; // serve / run as-is
}

http_response_code(404);
header('Content-Type: text/plain; charset=UTF-8');
echo "Not found: {$uri}\n";
return true;
