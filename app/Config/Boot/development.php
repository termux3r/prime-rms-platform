<?php

/**
 * Development Environment Boot
 *
 * This file is automatically loaded by the framework during bootstrapping
 * when the ENVIRONMENT constant is set to 'development'.
 *
 * Do NOT modify this file. Instead, copy it to Config/Boot/development.php
 * and make your changes there.
 */

// --------------------------------------------------------------------
// Error Reporting
// --------------------------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', '1');

// --------------------------------------------------------------------
// Debug Toolbar
// --------------------------------------------------------------------
if (! function_exists('enable_profiler')) {
    function enable_profiler(): bool
    {
        return true;
    }
}

// --------------------------------------------------------------------
// Caching
// --------------------------------------------------------------------
if (! function_exists('cache_ttl')) {
    function cache_ttl(): int
    {
        return 0; // Disable caching in development
    }
}