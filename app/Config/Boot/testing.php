<?php

/*
 * The environment testing is reserved for PHPUnit testing. It has special
 * conditions built into the framework at various places to assist with that.
 * You can’t use it for your development.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

defined('SHOW_DEBUG_BACKTRACE') || define('SHOW_DEBUG_BACKTRACE', true);
defined('CI_DEBUG') || define('CI_DEBUG', true);
