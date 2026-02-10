<?php

namespace HRAssistant\Controllers\Admin;

/**
 * System Administration Controller
 * Example of PSR-4 namespaced controller
 */
class SystemController
{
    public function index(): void
    {
        echo "<h1>System Administration</h1>";
        echo "<p>This is a namespaced controller: " . __CLASS__ . "</p>";
        echo "<p>File: " . __FILE__ . "</p>";
    }
    
    public function diagnostics(): void
    {
        echo "<h1>System Diagnostics</h1>";
        echo "<pre>";
        echo "Autoloader Stats: " . print_r(\HRAutoloader::getStats(), true);
        echo "</pre>";
    }
}