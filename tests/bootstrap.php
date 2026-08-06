<?php

/**
 * Test bootstrap
 */

define('ENVIRONMENT', 'testing');

require __DIR__ . '/../vendor/autoload.php';

// Load the framework's test bootstrap
require __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';