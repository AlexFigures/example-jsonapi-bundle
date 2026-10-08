<?php

declare(strict_types=1);

// PHP's development server otherwise treats dotted URLs (openapi.json) as files.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath(__DIR__.'/'.rawurldecode(is_string($path) ? $path : '/'));
if ($file !== false && str_starts_with($file, __DIR__.DIRECTORY_SEPARATOR) && is_file($file) && $file !== __FILE__) {
    return false;
}

// Symfony Runtime re-requires SCRIPT_FILENAME to obtain the application callable.
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/index.php';
require __DIR__.'/index.php';
