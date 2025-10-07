<?php
define('DEFAULT_CONTROLLER', 'rifa');

define('DEBUG_MODE', true);

define('DB_HOST', 'localhost');
define('DB_NAME', 'rifaslapaz');
define('DB_USER', 'root');
define('DB_PASSWORD', "");
define('DB_CHARSET', 'utf8mb4');

if (!defined('BASE_URL')) {
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
	$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
	$baseDir = ($scriptPath === '/') ? '' : $scriptPath;

	define('BASE_URL', $protocol . '://' . $host . $baseDir . '/');
}

if (!defined('URL')) {
	define('URL', BASE_URL);
}

if (!defined('ROOT_PATH')) {
	define('ROOT_PATH', dirname(__DIR__));
}

if (!defined('VIEWS_PATH')) {
	define('VIEWS_PATH', ROOT_PATH . '/views/');
}

if (!defined('ASSETS_PATH')) {
	define('ASSETS_PATH', BASE_URL . '/assets/');
}

if (!defined('MODELS_PATH')) {
	define('MODELS_PATH', ROOT_PATH . '/models/');
}

if (!defined('CONTROLLERS_PATH')) {
	define('CONTROLLERS_PATH', ROOT_PATH . '/controllers/');
}

if (!defined('LIBS_PATH')) {
	define('LIBS_PATH', ROOT_PATH . '/libs/');
}

if (!defined('APP_TIMEZONE')) {
	define('APP_TIMEZONE', 'America/Mexico_City');
}

date_default_timezone_set(APP_TIMEZONE);
