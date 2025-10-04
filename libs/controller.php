<?php
class Controller
{
	public $view;
	protected $homeRedirect = '/';
	protected $currentRoute;

	function __construct()
	{
		$this->view = new View();
		$this->setCurrentRoute();
		$this->view->currentRoute = $this->currentRoute;
	}

	protected function setCurrentRoute()
	{
		$url = $_GET['url'] ?? '';
		$this->currentRoute = trim($url, '/');

		// Si está vacía, es la ruta raíz
		if (empty($this->currentRoute)) {
			$this->currentRoute = 'dashboard';
		}
	}

	protected function isRouteActive($route)
	{
		return $this->currentRoute === $route;
	}

	protected function routeContains($pattern)
	{
		return strpos($this->currentRoute, $pattern) !== false;
	}

	protected function getActiveClass($route)
	{
		return $this->isRouteActive($route) ? 'active' : '';
	}

	protected function getOpenClass($pattern)
	{
		return $this->routeContains($pattern) ? 'open' : '';
	}

	protected function getShowClass($pattern)
	{
		return $this->routeContains($pattern) ? 'show' : '';
	}

	protected function requireRoles(array $allowedRoles): void
	{
		if (empty($_SESSION['user_id'])) {
			$_SESSION['error'] = 'Debes iniciar sesión, para acceder a esta sección';
			header('Location: ' . $this->homeRedirect);
			exit();
		}

		$userRole = $_SESSION['user_role'] ?? $_SESSION['id_rol'] ?? null;

		if ($userRole === null) {
			$_SESSION['error'] = 'No puedes acceder, si no tienes un rol definido';
			header('Location: ' . $this->homeRedirect);
			exit();
		}

		if (!in_array((int)$userRole, array_map('intval', $allowedRoles), true)) {
			$_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
			header('Location: ' . $this->homeRedirect);
			exit();
		}
	}

	protected function requireLogin(): void
	{
		if (empty($_SESSION['user_id'])) {
			$_SESSION['error'] = 'Debes iniciar sesión, para acceder a esta sección';
			header('Location: ' . $this->homeRedirect);
			exit();
		}
	}

	protected function requireMethod($allowedMethods): void
	{
		$currentMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

		if (is_string($allowedMethods)) {
			$allowedMethods = [$allowedMethods];
		}

		$allowedMethods = array_map('strtoupper', $allowedMethods);
		$currentMethod = strtoupper($currentMethod);

		if (!in_array($currentMethod, $allowedMethods)) {
			ErrorHandler::handle405(
				$allowedMethods,
				"El método {$currentMethod} no está permitido para esta ruta."
			);
		}
	}

	protected function setError(string $message): void
	{
		$_SESSION['error'] = $message;
	}

	protected function setSuccess(string $message): void
	{
		$_SESSION['success'] = $message;
	}

	protected function getError(): ?string
	{
		$error = $_SESSION['error'] ?? null;
		unset($_SESSION['error']);
		return $error;
	}

	protected function getSuccess(): ?string
	{
		$success = $_SESSION['success'] ?? null;
		unset($_SESSION['success']);
		return $success;
	}

	protected function hasError(): bool
	{
		return isset($_SESSION['error']) && !empty($_SESSION['error']);
	}


	protected function hasSuccess(): bool
	{
		return isset($_SESSION['success']) && !empty($_SESSION['success']);
	}

	protected function setMessageAndRedirect(string $type, string $message, string $redirect): void
	{
		if ($type === 'error') {
			$this->setError($message);
		} elseif ($type === 'success') {
			$this->setSuccess($message);
		}

		header('Location: ' . $redirect);
		exit();
	}
}
