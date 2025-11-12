<?php
// router.php — for PHP built-in server pretty URLs with CodeIgniter 3

// Serve the requested resource as-is if it exists (static files: css, js, images)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false; // let the built-in server handle the file
}

// Otherwise, route everything through index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF']    = '/index.php';
require __DIR__ . '/index.php';
