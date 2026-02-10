<?php
/**
 * Bootstrap Script for HR Assistant
 * 
 * This file sets up the basic environment for the HR Assistant application.
 * It can be used in both web and CLI contexts.
 * 
 * Usage:
 *   require_once 'bootstrap.php';
 */

// Ensure session is started for web contexts
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Load the autoloader
require_once __DIR__ . '/autoload.php';

// Set error reporting for development
if (!defined('PRODUCTION')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Define useful constants
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__);
}

if (!defined('PUBLIC_ROOT')) {
    define('PUBLIC_ROOT', APP_ROOT . '/public');
}

if (!defined('CLI_ROOT')) {
    define('CLI_ROOT', APP_ROOT . '/cli');
}

// Setup timezone
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

/**
 * Utility function to check if running in CLI mode
 * 
 * @return bool
 */
function isCliMode(): bool
{
    return php_sapi_name() === 'cli' || php_sapi_name() === 'cli-server';
}

/**
 * Utility function for debugging (only in development)
 * 
 * @param mixed $data Data to debug
 * @param bool $die Whether to die after output
 */
function debug($data, $die = false): void
{
    if (defined('PRODUCTION') && PRODUCTION) {
        return;
    }

    if (isCliMode()) {
        echo "DEBUG: ";
        print_r($data);
        echo "\n";
    } else {
        echo "<pre>DEBUG: ";
        print_r($data);
        echo "</pre>";
    }

    if ($die) {
        die();
    }
}

/**
 * Simple logging function
 * 
 * @param string $message Log message
 * @param string $level Log level (info, warning, error)
 */
function writeLog($message, $level = 'info'): void
{
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message\n";
    
    // For now, just write to error log
    error_log($logEntry);
}