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

// =====================
// Configuración de correo (PHPMailer)
// =====================
if (!defined('MAIL_ENABLED')) define('MAIL_ENABLED', true);
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USER')) define('SMTP_USER', '');
if (!defined('SMTP_PASS')) define('SMTP_PASS', '');
if (!defined('SMTP_SECURE')) define('SMTP_SECURE', 'tls'); // tls | ssl | ''
if (!defined('MAIL_FROM')) define('MAIL_FROM', 'no-reply@rifaslapaz.local');
if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', 'Rifas La Paz');
if (!defined('MAIL_REPLY_TO')) define('MAIL_REPLY_TO', '');
if (!defined('MAIL_REPLY_TO_NAME')) define('MAIL_REPLY_TO_NAME', 'Soporte Rifas La Paz');
// Opciones SSL para SMTP
if (!defined('SMTP_VERIFY_PEER')) define('SMTP_VERIFY_PEER', true);
if (!defined('SMTP_VERIFY_PEER_NAME')) define('SMTP_VERIFY_PEER_NAME', true);
if (!defined('SMTP_ALLOW_SELF_SIGNED')) define('SMTP_ALLOW_SELF_SIGNED', false);
// DKIM opcional
if (!defined('DKIM_DOMAIN')) define('DKIM_DOMAIN', '');
if (!defined('DKIM_SELECTOR')) define('DKIM_SELECTOR', '');
if (!defined('DKIM_PRIVATE_KEY_PATH')) define('DKIM_PRIVATE_KEY_PATH', '');
if (!defined('DKIM_PASSPHRASE')) define('DKIM_PASSPHRASE', '');
if (!defined('DKIM_IDENTITY')) define('DKIM_IDENTITY', '');
// List-Unsubscribe opcional
if (!defined('MAIL_LIST_UNSUBSCRIBE')) define('MAIL_LIST_UNSUBSCRIBE', '');
