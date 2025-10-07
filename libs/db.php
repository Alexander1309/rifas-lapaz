<?php

class Database
{
	private $host;
	private $db;
	private $user;
	private $password;
	private $charset;
	private static $instance = null;
	private $connection = null;

	public function __construct()
	{
		$this->host = $this->getConstant('DB_HOST');
		$this->db = $this->getConstant('DB_NAME');
		$this->user = $this->getConstant('DB_USER');
		$this->password = $this->getConstant('DB_PASSWORD');
		$this->charset = $this->getConstant('DB_CHARSET', 'utf8mb4');
	}
	public function fetchColumn($sql, $params = [])
	{
		try {
			// Usamos el método query() que ya prepara y ejecuta de forma segura
			$stmt = $this->query($sql, $params);
			return $stmt->fetchColumn();
		} catch (PDOException $e) {
			error_log("Error en fetchColumn: " . $e->getMessage());
			return false;
		}
	}

	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function getConnection()
	{
		if ($this->connection === null || !$this->isConnectionAlive()) {
			$this->connect();
		}
		return $this->connection;
	}

	private function connect()
	{
		try {
			$dsn = $this->buildDsn();
			$options = $this->getPdoOptions();

			$this->connection = new PDO($dsn, $this->user, $this->password, $options);
		} catch (PDOException $e) {
			$this->handleConnectionError($e);
			throw new RuntimeException('No se pudo establecer conexión con la base de datos');
		}
	}

	private function buildDsn()
	{
		return sprintf(
			"mysql:host=%s;dbname=%s;charset=%s",
			$this->host,
			$this->db,
			$this->charset
		);
	}

	private function getPdoOptions()
	{
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_PERSISTENT => false,
			PDO::ATTR_TIMEOUT => 30,
		];

		if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$this->charset}";
		}

		return $options;
	}

	private function isConnectionAlive()
	{
		if ($this->connection === null) {
			return false;
		}

		try {
			$this->connection->query('SELECT 1');
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	private function handleConnectionError(PDOException $e)
	{
		$errorMessage = sprintf(
			'Error de conexión DB - Host: %s, DB: %s, Error: %s',
			$this->host,
			$this->db,
			$e->getMessage()
		);

		error_log($errorMessage);

		if ($this->isDevelopmentMode()) {
			echo '<div style="background:#f8d7da;color:#721c24;padding:10px;border:1px solid #f5c6cb;margin:10px;">';
			echo '<strong>Error de Base de Datos:</strong><br>';
			echo htmlspecialchars($e->getMessage());
			echo '</div>';
		}
	}

	private function getConstant($name, $default = null)
	{
		if (!defined($name)) {
			if ($default !== null) {
				return $default;
			}
			throw new InvalidArgumentException("La constante {$name} no está definida");
		}

		$value = constant($name);
		if (empty($value) && $default !== null) {
			return $default;
		}

		return $value;
	}

	private function isDevelopmentMode()
	{
		return defined('ENVIRONMENT') && constant('ENVIRONMENT') === 'development';
	}

	public function closeConnection()
	{
		$this->connection = null;
	}

	public function query($sql, $params = [])
	{
		try {
			$stmt = $this->getConnection()->prepare($sql);
			$stmt->execute($params);
			return $stmt;
		} catch (PDOException $e) {
			// Log ampliado con SQL y parámetros
			$safeParams = $params;
			foreach ($safeParams as $k => $v) {
				if (is_string($v) && strlen($v) > 500) {
					$safeParams[$k] = substr($v, 0, 500) . '...';
				}
			}
			$logMsg = "Error en consulta SQL: " . $e->getMessage() . " | SQL: " . $sql . " | Params: " . json_encode($safeParams, JSON_UNESCAPED_UNICODE);
			error_log($logMsg);
			// Intentar escribir en logs/error.log del proyecto para facilitar el diagnóstico
			$projectLog = (isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') : dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'error.log';
			@file_put_contents($projectLog, '[' . date('Y-m-d H:i:s') . "] ERROR: " . $logMsg . PHP_EOL, FILE_APPEND | LOCK_EX);

			// En modo debug, propagar detalle
			if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
				throw new RuntimeException('Error en la consulta a la base de datos: ' . $e->getMessage());
			}
			throw new RuntimeException('Error en la consulta a la base de datos');
		}
	}

	public function fetchOne($sql, $params = [])
	{
		$stmt = $this->query($sql, $params);
		return $stmt->fetch();
	}


	public function fetchAll($sql, $params = [])
	{
		$stmt = $this->query($sql, $params);
		return $stmt->fetchAll();
	}

	public function execute($sql, $params = [])
	{
		$stmt = $this->query($sql, $params);
		return $stmt->rowCount();
	}

	public function getLastInsertId()
	{
		return $this->getConnection()->lastInsertId();
	}

	public function beginTransaction()
	{
		return $this->getConnection()->beginTransaction();
	}

	public function commit()
	{
		return $this->getConnection()->commit();
	}

	public function rollback()
	{
		return $this->getConnection()->rollBack();
	}

	public function inTransaction(): bool
	{
		return $this->getConnection()->inTransaction();
	}


	public function __destruct()
	{
		$this->closeConnection();
	}
}
