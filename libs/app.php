<?php

class App
{
	private string $defaultController;
	private string $defaultMethod = 'index';
	private string $controllersPath = 'controllers/';
	private string $controllerSuffix = 'Controller';

	public function __construct(string $defaultController = 'home')
	{
		$this->defaultController = defined('DEFAULT_CONTROLLER')
			? DEFAULT_CONTROLLER
			: $defaultController;

		$this->route();
	}

	private function route(): void
	{
		$urlParts = $this->parseUrl();

		$controllerName = $urlParts['controller'];
		$methodName = $urlParts['method'];
		$arguments = $urlParts['arguments'];

		if (!$this->loadController($controllerName)) {
			$this->handleError("Controlador '$controllerName' no encontrado");
			return;
		}

		$this->executeController($controllerName, $methodName, $arguments);
	}

	private function parseUrl(): array
	{
		$url = $_GET['url'] ?? $this->defaultController;

		$url = $this->sanitizeUrl($url);

		$urlParts = !empty($url) ? explode('/', $url) : [];

		return [
			'controller' => !empty($urlParts[0]) ? $urlParts[0] : $this->defaultController,
			'method' => $urlParts[1] ?? $this->defaultMethod,
			'arguments' => count($urlParts) > 2 ? array_slice($urlParts, 2) : []
		];
	}

	private function sanitizeUrl(string $url): string
	{
		$url = trim($url, '/ ');

		$url = preg_replace('/[^a-zA-Z0-9\/_-]/', '', $url);

		$url = preg_replace('/\/+/', '/', $url);

		return $url;
	}

	private function loadController(string $controllerName): bool
	{
		$controllerFile = $this->controllersPath . ucwords($controllerName) . $this->controllerSuffix . '.php';

		if (!file_exists($controllerFile) || !is_readable($controllerFile)) {
			return false;
		}

		$realPath = realpath($controllerFile);
		$expectedPath = realpath($this->controllersPath);

		if ($realPath === false || strpos($realPath, $expectedPath) !== 0) {
			return false;
		}

		require_once $controllerFile;
		return true;
	}

	private function executeController(string $controllerName, string $methodName, array $arguments): void
	{
		$className = $this->formatControllerClassName($controllerName);

		if (!class_exists($className)) {
			$this->handleError("Clase '$className' no encontrada");
			return;
		}

		try {
			$controller = new $className($arguments);

			if (!$this->isMethodCallable($controller, $methodName)) {
				$this->handleError("Método '$methodName' no encontrado o no es accesible en '$className'");
				return;
			}

			$controller->$methodName();
		} catch (\Exception $e) {
			$this->handleError("Error al ejecutar el controlador: " . $e->getMessage());
		}
	}

	private function formatControllerClassName(string $controllerName): string
	{
		$parts = explode('-', $controllerName);
		$formatted = implode('', array_map('ucfirst', $parts));

		return $formatted . $this->controllerSuffix;
	}

	private function isMethodCallable(object $controller, string $methodName): bool
	{
		if (!method_exists($controller, $methodName)) {
			return false;
		}

		try {
			$reflection = new ReflectionMethod($controller, $methodName);
			return $reflection->isPublic() && !$reflection->isStatic();
		} catch (\ReflectionException $e) {
			return false;
		}
	}

	private function handleError(string $errorMessage): void
	{
		error_log("[App Router Error] " . $errorMessage);

		// Usar el sistema automático de errores
		if (class_exists('ErrorHandler')) {
			// Determinar el tipo de error basado en el mensaje
			if (
				strpos($errorMessage, 'no encontrado') !== false ||
				strpos($errorMessage, 'not found') !== false
			) {
				ErrorHandler::handle404($errorMessage);
			} else {
				ErrorHandler::handle500($errorMessage);
			}
		} else {
			http_response_code(404);
			echo "Error: " . htmlspecialchars($errorMessage);
		}
	}
}
