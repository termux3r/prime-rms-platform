<?php

/**
 * Test bootstrap
 */
require __DIR__ . '/../vendor/autoload.php';

// Load the framework's test bootstrap
require __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';

// Ensure the test database schema is migrated
$db = \Config\Database::connect('tests');
$config = new \Config\Migrations();
$config->enabled = true;
$runner = \Config\Services::migrations($config, $db, false);
$runner->setNamespace('App');
$runner->latest('tests');
$db->query("SET TIME ZONE 'UTC'");