<?php

declare(strict_types=1);

// Forward all incoming Vercel serverless requests to CodeIgniter's front controller
defined('FCPATH') || define('FCPATH', realpath(__DIR__ . '/../public') . DIRECTORY_SEPARATOR);

require __DIR__ . '/../public/index.php';
