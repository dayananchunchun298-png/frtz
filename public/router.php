<?php

/**
 * Built-in PHP server router: serve files from public/ directly, otherwise Symfony.
 */
$path = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$file = __DIR__.$path;

if ($path !== '/' && $path !== '' && is_file($file)) {
    return false;
}

// Avoid 500s while entrypoint warms cache / compiles assets (Railway early healthcheck).
$bootFlag = dirname(__DIR__).'/var/.boot-complete';
if (!is_file($bootFlag)) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    header('Retry-After: 3');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Starting</title></head>';
    echo '<body><p>FRTZ PawCare is starting. Please refresh in a few seconds.</p></body></html>';

    return true;
}

require __DIR__.'/index.php';
