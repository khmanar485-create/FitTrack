<?php
// Router for PHP's built-in development server: keep private JSON storage off the web.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (preg_match('#^/data(?:/|$)#', $path) || str_contains($path, "..")) {
    http_response_code(404);
    exit('Not found');
}
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file) && !str_ends_with($path, '.php')) return false;
if ($path === '/') { require __DIR__ . '/index.php'; return true; }
if (str_ends_with($path, '.php') && is_file($file)) { require $file; return true; }
http_response_code(404);
echo 'Not found';
