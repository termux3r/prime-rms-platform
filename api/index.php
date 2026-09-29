<?php

declare(strict_types=1);

// Normalize server variables so CodeIgniter treats the root as / rather than /api/
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF']    = '/index.php';

require __DIR__ . '/../public/index.php';
