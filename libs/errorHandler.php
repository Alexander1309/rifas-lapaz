<?php

/**
 * ErrorHandler Class - Sistema avanzado de manejo de errores
 * 
 * Detecta automáticamente múltiples tipos de errores y los procesa
 * de manera uniforme con logging y respuestas apropiadas.
 * 
 * @author php-mvc-base
 * @version 1.0
 */
class ErrorHandler
{
	private static ?ErrorHandler $instance = null;
	private ?Logger $logger = null;

	private function __construct()
	{
		$this->logger = class_exists('Logger') ? new Logger() : null;
		$this->setupErrorHandlers();
	}

	/**
	 * Obtiene la instancia singleton del ErrorHandler
	 */
	public static function getInstance(): ErrorHandler
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Configura todos los manejadores de errores
	 */
	private function setupErrorHandlers(): void
	{
		// Manejador de errores PHP
		set_error_handler([$this, 'handlePhpError']);

		// Manejador de excepciones no capturadas
		set_exception_handler([$this, 'handleUncaughtException']);

		// Manejador de errores fatales
		register_shutdown_function([$this, 'handleFatalError']);
	}

	/**
	 * Maneja errores de PHP (warnings, notices, etc.)
	 */
	public function handlePhpError(int $severity, string $message, string $file, int $line): bool
	{
		// Convertir severidad a string legible
		$severityName = $this->getSeverityName($severity);

		// Log del error
		if ($this->logger) {
			$this->logger->logError(
				"PHP {$severityName}: {$message}",
				$file,
				$line,
				[
					'severity' => $severity,
					'severity_name' => $severityName
				]
			);
		}

		// Si es un error grave, mostrar página de error
		if ($this->isGraveError($severity)) {
			$this->showErrorPage([
				'error_code' => 500,
				'error_message' => 'Error interno del servidor',
				'error_description' => $this->isDebugMode()
					? "PHP {$severityName}: {$message} en {$file}:{$line}"
					: 'Ha ocurrido un error interno. Intente nuevamente más tarde.'
			]);
		}

		return true; // No mostrar el error por defecto de PHP
	}

	/**
	 * Maneja excepciones no capturadas
	 */
	public function handleUncaughtException(Throwable $exception): void
	{
		// Log de la excepción
		if ($this->logger) {
			$this->logger->logError(
				"Uncaught Exception: " . $exception->getMessage(),
				$exception->getFile(),
				$exception->getLine(),
				[
					'exception_class' => get_class($exception),
					'trace' => $exception->getTraceAsString()
				]
			);
		}

		// Mostrar página de error
		$this->showErrorPage([
			'error_code' => 500,
			'error_message' => 'Error interno del servidor',
			'error_description' => $this->isDebugMode()
				? $exception->getMessage() . " en " . $exception->getFile() . ":" . $exception->getLine()
				: 'Ha ocurrido un error inesperado. Intente nuevamente más tarde.'
		]);
	}

	/**
	 * Maneja errores fatales
	 */
	public function handleFatalError(): void
	{
		$error = error_get_last();

		if ($error && $this->isFatalError($error['type'])) {
			// Log del error fatal
			if ($this->logger) {
				$this->logger->logError(
					"Fatal Error: " . $error['message'],
					$error['file'],
					$error['line'],
					[
						'error_type' => $error['type'],
						'error_type_name' => $this->getSeverityName($error['type'])
					]
				);
			}

			// Limpiar cualquier output
			if (ob_get_level()) {
				ob_clean();
			}

			// Mostrar página de error
			$this->showErrorPage([
				'error_code' => 500,
				'error_message' => 'Error fatal del sistema',
				'error_description' => $this->isDebugMode()
					? $error['message'] . " en " . $error['file'] . ":" . $error['line']
					: 'El sistema ha encontrado un error fatal. Contacte al administrador.'
			]);
		}
	}

	/**
	 * Métodos de conveniencia para manejar errores específicos
	 */

	/**
	 * Error 404 - Página no encontrada
	 */
	public static function handle404(string $resource = ''): void
	{
		self::getInstance()->showErrorPage([
			'error_code' => 404,
			'error_message' => 'Página no encontrada',
			'error_description' => $resource
				? "El recurso '{$resource}' no existe o ha sido movido."
				: 'La página que busca no existe o ha sido movida.'
		]);
	}

	/**
	 * Error 403 - Acceso prohibido
	 */
	public static function handle403(string $reason = ''): void
	{
		self::getInstance()->showErrorPage([
			'error_code' => 403,
			'error_message' => 'Acceso prohibido',
			'error_description' => $reason ?: 'No tiene permisos para acceder a este recurso.'
		]);
	}

	/**
	 * Error 401 - No autorizado
	 */
	public static function handle401(string $reason = ''): void
	{
		self::getInstance()->showErrorPage([
			'error_code' => 401,
			'error_message' => 'No autorizado',
			'error_description' => $reason ?: 'Debe autenticarse para acceder a este recurso.'
		]);
	}

	public static function handle405(array $allowedMethods = [], string $reason = ''): void
	{
		$instance = self::getInstance();

		if (!empty($allowedMethods)) {
			header('Allow: ' . implode(', ', $allowedMethods));
		}

		if ($instance->logger) {
			$currentMethod = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
			$instance->logger->logError(
				"Method Not Allowed: {$currentMethod}. Allowed: " . implode(', ', $allowedMethods),
				$_SERVER['SCRIPT_NAME'] ?? __FILE__,
				0,
				[
					'current_method' => $currentMethod,
					'allowed_methods' => $allowedMethods,
					'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
					'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
				]
			);
		}

		if (
			!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
		) {
			header('Content-Type: application/json');
			http_response_code(405);
			echo json_encode([
				'error' => true,
				'code' => 405,
				'message' => 'Método HTTP no permitido',
				'allowed_methods' => $allowedMethods,
				'description' => $reason ?: 'El método utilizado no está permitido para esta ruta.'
			]);
			exit();
		}

		$instance->showErrorPage([
			'error_code' => 405,
			'error_message' => 'Método no permitido',
			'error_description' => $reason ?: sprintf(
				'El método %s no está permitido. Métodos permitidos: %s',
				$_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
				implode(', ', $allowedMethods)
			)
		]);
	}

	public static function handle500(string $message = '', array $context = []): void
	{
		$instance = self::getInstance();

		if ($instance->logger && $message) {
			$instance->logger->logError(
				"Manual 500 Error: " . $message,
				$_SERVER['SCRIPT_NAME'] ?? __FILE__,
				0,
				$context
			);
		}

		$instance->showErrorPage([
			'error_code' => 500,
			'error_message' => 'Error interno del servidor',
			'error_description' => $instance->isDebugMode() && $message
				? $message
				: 'Ha ocurrido un error interno. Intente nuevamente más tarde.'
		]);
	}


	public static function handleCustom(int $code, string $message, string $description = '', array $context = []): void
	{
		$instance = self::getInstance();

		if ($instance->logger) {
			$instance->logger->logError(
				"Custom Error {$code}: {$message}",
				$_SERVER['SCRIPT_NAME'] ?? __FILE__,
				0,
				array_merge($context, ['custom_error' => true])
			);
		}

		$instance->showErrorPage([
			'error_code' => $code,
			'error_message' => $message,
			'error_description' => $description ?: 'Ha ocurrido un error.'
		]);
	}

	private function showErrorPage(array $errorData): void
	{
		if (ob_get_level()) {
			ob_clean();
		}

		http_response_code($errorData['error_code'] ?? 500);

		// Generar página de error profesional
		$this->renderErrorPage($errorData);
		exit();
	}

	private function renderErrorPage(array $errorData): void
	{
		$error_code = $errorData['error_code'] ?? 500;
		$error_message = htmlspecialchars($errorData['error_message'] ?? 'Error del Sistema');
		$error_description = htmlspecialchars($errorData['error_description'] ?? 'Ha ocurrido un error inesperado.');
		$timestamp = date('Y-m-d H:i:s');
		$request_id = uniqid('req_', true);
		$current_url = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '');
		$referer = $_SERVER['HTTP_REFERER'] ?? '';
		$user_agent = htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A');
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

		echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error ' . $error_code . ' - ' . $error_message . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.6;
        }
        
        .error-container { 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1), 0 0 0 1px rgba(255,255,255,0.2); 
            max-width: 600px; 
            width: 100%;
            text-align: center;
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from { 
                opacity: 0; 
                transform: translateY(30px) scale(0.95); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }
        
        .error-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .error-code {
            font-size: 5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
            text-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
            line-height: 1;
        }
        
        .error-title {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .error-description {
            color: #5a6c7d;
            line-height: 1.7;
            margin-bottom: 35px;
            font-size: 1.1rem;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 35px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: none;
            font-size: 1rem;
            text-transform: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: #5a6c7d;
            border: 2px solid rgba(90, 108, 125, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .error-details {
            background: rgba(248, 249, 250, 0.8);
            border-radius: 12px;
            padding: 25px;
            margin-top: 25px;
            text-align: left;
            border-left: 4px solid #e74c3c;
            backdrop-filter: blur(5px);
        }
        
        .error-details h4 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.9rem;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #495057;
            min-width: 120px;
        }
        
        .detail-value {
            color: #6c757d;
            word-break: break-all;
            text-align: right;
            font-family: "SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, "Courier New", monospace;
            font-size: 0.85rem;
        }
        
        .footer-info {
            margin-top: 30px; 
            padding-top: 25px; 
            border-top: 1px solid rgba(0,0,0,0.1); 
            color: #6c757d; 
            font-size: 0.9rem;
        }
        
        .footer-info p {
            margin-bottom: 8px;
        }
        
        .error-id {
            font-family: "SF Mono", Monaco, monospace;
            background: rgba(108, 117, 125, 0.1);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .error-container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .error-code {
                font-size: 3.5rem;
            }
            
            .error-title {
                font-size: 1.6rem;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 4px;
            }
            
            .detail-value {
                text-align: left;
            }
        }
        
        @media (max-width: 480px) {
            .error-container {
                padding: 25px 15px;
            }
            
            .error-code {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">';

		// Icono dinámico según el tipo de error
		if ($error_code == 404) {
			echo '🔍';
		} elseif ($error_code == 403) {
			echo '🚫';
		} elseif ($error_code == 401) {
			echo '🔐';
		} elseif ($error_code == 500) {
			echo '⚠️';
		} elseif ($error_code >= 400 && $error_code < 500) {
			echo '❌';
		} else {
			echo '🛠️';
		}

		echo '</div>
        
        <div class="error-code">' . $error_code . '</div>
        <h1 class="error-title">' . $error_message . '</h1>
        <p class="error-description">' . $error_description . '</p>
        
        <div class="actions">
            <a href="/" class="btn btn-primary">
                🏠 Ir al Inicio
            </a>';

		if (!empty($referer) && $referer !== $current_url) {
			echo '<a href="javascript:history.back()" class="btn btn-secondary">
                ← Volver Atrás
            </a>';
		}

		if ($error_code == 404) {
			echo '<a href="/buscar" class="btn btn-secondary">
                🔍 Buscar
            </a>';
		}

		echo '</div>';

		// Mostrar detalles técnicos solo en modo debug
		if ($this->isDebugMode()) {
			echo '<div class="error-details">
                <h4>🔧 Información Técnica</h4>
                <div class="detail-row">
                    <span class="detail-label">Request ID:</span>
                    <span class="detail-value">' . $request_id . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Timestamp:</span>
                    <span class="detail-value">' . $timestamp . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">URL:</span>
                    <span class="detail-value">' . $current_url . '</span>
                </div>';

			if (!empty($referer)) {
				echo '<div class="detail-row">
                    <span class="detail-label">Referer:</span>
                    <span class="detail-value">' . htmlspecialchars($referer) . '</span>
                </div>';
			}

			echo '<div class="detail-row">
                    <span class="detail-label">IP:</span>
                    <span class="detail-value">' . $ip . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">User Agent:</span>
                    <span class="detail-value">' . $user_agent . '</span>
                </div>
            </div>';
		}

		echo '<div class="footer-info">
            <p>Si el problema persiste, contacte al administrador del sistema.</p>';

		if (!$this->isDebugMode()) {
			echo '<p><span class="error-id">ID: ' . substr($request_id, 0, 8) . '</span></p>';
		}

		echo '</div>
    </div>

    <script>
        // Auto-refresh para errores 503 (servicio no disponible)
        ';
		if ($error_code == 503) {
			echo 'setTimeout(function() {
                if (confirm("¿Desea intentar recargar la página?")) {
                    location.reload();
                }
            }, 10000);';
		}

		echo '
        
        // Evitar spam de F5 en errores
        let lastReload = localStorage.getItem("lastErrorReload");
        let now = Date.now();
        
        if (lastReload && (now - parseInt(lastReload)) < 5000) {
            // Deshabilitar F5 por 5 segundos
            document.addEventListener("keydown", function(e) {
                if (e.key === "F5" || (e.ctrlKey && e.key === "r")) {
                    e.preventDefault();
                    alert("Por favor espere unos segundos antes de recargar.");
                }
            });
        }
        
        window.addEventListener("beforeunload", function() {
            localStorage.setItem("lastErrorReload", Date.now().toString());
        });
        
        // Analytics de errores (si está disponible)
        if (typeof gtag !== "undefined") {
            gtag("event", "exception", {
                "description": "' . addslashes($error_message) . '",
                "fatal": ' . ($error_code >= 500 ? 'true' : 'false') . '
            });
        }
    </script>
</body>
</html>';
	}

	/**
	 * Métodos auxiliares
	 */

	private function getSeverityName(int $severity): string
	{
		$names = [
			E_ERROR => 'Fatal Error',
			E_WARNING => 'Warning',
			E_PARSE => 'Parse Error',
			E_NOTICE => 'Notice',
			E_CORE_ERROR => 'Core Error',
			E_CORE_WARNING => 'Core Warning',
			E_COMPILE_ERROR => 'Compile Error',
			E_COMPILE_WARNING => 'Compile Warning',
			E_USER_ERROR => 'User Error',
			E_USER_WARNING => 'User Warning',
			E_USER_NOTICE => 'User Notice',
			E_STRICT => 'Strict Notice',
			E_RECOVERABLE_ERROR => 'Recoverable Error',
			E_DEPRECATED => 'Deprecated',
			E_USER_DEPRECATED => 'User Deprecated'
		];

		return $names[$severity] ?? 'Unknown Error';
	}

	private function isGraveError(int $severity): bool
	{
		return in_array($severity, [
			E_ERROR,
			E_PARSE,
			E_CORE_ERROR,
			E_COMPILE_ERROR,
			E_USER_ERROR,
			E_RECOVERABLE_ERROR
		]);
	}

	private function isFatalError(int $type): bool
	{
		return in_array($type, [
			E_ERROR,
			E_PARSE,
			E_CORE_ERROR,
			E_COMPILE_ERROR,
			E_USER_ERROR
		]);
	}

	private function isDebugMode(): bool
	{
		return defined('DEBUG_MODE') && DEBUG_MODE === true;
	}
}
