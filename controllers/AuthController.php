<?php

require_once "models/UserModel.php";

class AuthController extends Controller
{

	private $userModel;
	private $args;

	public function __construct($args)
	{
		parent::__construct();
		$this->userModel = new UserModel();
		$this->args = $args;
	}

	public function index()
	{
		if (isset($_SESSION['user_id'])) {
			header('Location: /');
			exit();
		}

		$this->view->render('Auth/login', [
			"pageTitle" => "Rifas La Paz - Iniciar Sesión",
			"useLogin" => true
		], ["auth.js"]);
	}

	public function login()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: /auth');
			exit();
		}

		$username = trim($_POST['username'] ?? '');
		$password = $_POST['password'] ?? '';
		$remember = isset($_POST['remember']);

		if (empty($username) || empty($password)) {
			$_SESSION['error'] = 'Por favor completa todos los campos';
			header('Location: /auth');
			exit();
		}

		try {
			$user = $this->userModel->getByUsernameAndPassword($username, $password);

			if ($user) {
				$_SESSION['user_id'] = $user['id'];
				$_SESSION['username'] = $user['username'];
				$_SESSION['user_idRol'] = $user['id_rol'];
				$_SESSION['user_idBranch'] = $user['id_branch'];
				$_SESSION['user_role'] = $user['role_name'] ?? 'Sin rol';
				$_SESSION['user_branch'] = $user['branch_name'] ?? 'Sin sucursal';
				$_SESSION['last_activity'] = time();

				if ($remember) {
					$token = $this->generateRememberToken();
					setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
				}

				$this->logActivity('Login exitoso', $username);
				header('Location: /dashboard');

				exit();
			} else {
				$this->logActivity('Intento de login fallido', $username);
				$_SESSION['error'] = 'Usuario o contraseña incorrectos';
				header('Location: /auth');
				exit();
			}
		} catch (Exception $e) {
			error_log('Error en login: ' . $e->getMessage());
			$_SESSION['error'] = 'Error interno del servidor. Intenta nuevamente. -> ' . $e->getMessage();
			header('Location: /auth');
			exit();
		}
	}

	public function logout()
	{
		$username = $_SESSION['username'] ?? 'Usuario desconocido';

		$this->logActivity('Logout exitoso', $username);
		session_destroy();

		if (isset($_COOKIE['remember_token'])) {
			setcookie('remember_token', '', time() - 3600, '/', '', true, true);
		}

		$_SESSION = array();
		session_start();
		$_SESSION['success'] = 'Has cerrado sesión correctamente';

		header('Location: /auth');
		exit();
	}

	public function checkAuth()
	{
		if (!isset($_SESSION['user_id'])) {
			if (isset($_COOKIE['remember_token'])) {
			} else {
				$_SESSION['error'] = 'Debes iniciar sesión para acceder';
				header('Location: /auth');
				exit();
			}
		}

		if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
			$this->logout();
		}

		$_SESSION['last_activity'] = time();
	}

	private function generateRememberToken()
	{
		return bin2hex(random_bytes(32));
	}

	private function logActivity($action, $username)
	{
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
		$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
		$timestamp = date('Y-m-d H:i:s');

		$logEntry = "[$timestamp] $action - Usuario: $username - IP: $ip - User Agent: $userAgent" . PHP_EOL;

		file_put_contents(
			__DIR__ . '/../logs/auth.log',
			$logEntry,
			FILE_APPEND | LOCK_EX
		);
	}

	public function forgot()
	{
		$this->view->render('Auth/forgot-password');
	}

	public function resetPassword()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: /auth/forgot');
			exit();
		}

		$email = trim($_POST['email'] ?? '');

		if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$_SESSION['error'] = 'Por favor ingresa un email válido';
			header('Location: /auth/forgot');
			exit();
		}

		$_SESSION['success'] = 'Si el email existe en nuestro sistema, recibirás instrucciones para restablecer tu contraseña';
		header('Location: /auth');
		exit();
	}
}
