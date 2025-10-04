<?php
require_once 'libs/errorHandler.php';

ErrorHandler::getInstance();

// Configuración de errores PHP
if (DEBUG_MODE) {
	// En desarrollo: mostrar todos los errores
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
} else {
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');
