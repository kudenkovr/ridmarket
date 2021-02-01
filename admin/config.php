<?php
define('SERVER_NAME', $_SERVER['HTTP_HOST']);
// HTTP
define('HTTP_SERVER', 'http://' . SERVER_NAME . '/admin/');
define('HTTP_CATALOG', 'http://' . SERVER_NAME . '/');

// HTTPS
define('HTTPS_SERVER', 'http://' . SERVER_NAME . '/admin/');
define('HTTPS_CATALOG', 'http://' . SERVER_NAME . '/');

// DIR
define('DIR_BASE', str_replace('\\', '/', dirname(dirname(__FILE__))) . '/');
define('DIR_APPLICATION', DIR_BASE . 'admin/');
define('DIR_SYSTEM', DIR_BASE . 'system/');
define('DIR_IMAGE', DIR_BASE . 'image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_CATALOG', DIR_BASE . 'catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'mysql');
define('DB_PASSWORD', 'uxUh34rk');
define('DB_DATABASE', 'ridmarket');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// OpenCart API
define('OPENCART_SERVER', 'https://www.opencart.com/');
