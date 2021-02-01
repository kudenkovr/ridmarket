<?php
// Version
define('VERSION', '3.0.3.2');

// Configuration
if (is_file($_SERVER['DOCUMENT_ROOT'] . '/config.php')) {
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
}

$_GET['store_id'] = 1;

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('catalog');