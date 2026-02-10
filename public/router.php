<?php
/**
 * Router script for PHP built-in server
 * This handles URL rewriting for the development server
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// All other requests go to index.php
require_once __DIR__ . '/index.php';