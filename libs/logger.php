<?php

class Logger
{
	// Constantes para niveles de log
	const LEVEL_ERROR = 'ERROR';
	const LEVEL_WARNING = 'WARNING';
	const LEVEL_INFO = 'INFO';
	const LEVEL_DEBUG = 'DEBUG';

	private string $logDirectory;
	private string $errorLogFile;
	private string $infoLogFile;
	private string $dateFormat;
	private bool $rotateDaily;

	public function __construct(?string $logDirectory = null, bool $rotateDaily = false)
	{
		$this->dateFormat = 'Y-m-d H:i:s';
		$this->rotateDaily = $rotateDaily;

		// Establecer directorio de logs
		$this->logDirectory = $logDirectory ?? $this->getDefaultLogDirectory();

		// Configurar archivos de log
		$this->setupLogFiles();

		// Crear directorio si no existe
		$this->ensureLogDirectoryExists();
	}

	public function logError(string $message, string $file, int $line, array $context = []): bool
	{
		return $this->writeLog(
			self::LEVEL_ERROR,
			$message,
			array_merge($context, [
				'file' => basename($file),
				'line' => $line,
				'full_path' => $file
			]),
			$this->errorLogFile
		);
	}

	public function logInfo(string $message, ?string $route = null, ?string $process = null, array $context = []): bool
	{
		$logContext = $context;

		if ($route !== null) {
			$logContext['route'] = $route;
		}

		if ($process !== null) {
			$logContext['process'] = $process;
		}

		return $this->writeLog(self::LEVEL_INFO, $message, $logContext, $this->infoLogFile);
	}

	public function logWarning(string $message, array $context = []): bool
	{
		return $this->writeLog(self::LEVEL_WARNING, $message, $context, $this->errorLogFile);
	}

	public function logDebug(string $message, array $context = []): bool
	{
		// Solo registrar debug en entorno de desarrollo
		if (!$this->isDebugMode()) {
			return false;
		}

		return $this->writeLog(self::LEVEL_DEBUG, $message, $context, $this->infoLogFile);
	}

	private function writeLog(string $level, string $message, array $context, string $logFile): bool
	{
		try {
			$timestamp = date($this->dateFormat);
			$logMessage = $this->formatLogMessage($timestamp, $level, $message, $context);

			return file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX) !== false;
		} catch (Exception $e) {
			error_log("Logger Error: " . $e->getMessage());
			return false;
		}
	}

	private function formatLogMessage(string $timestamp, string $level, string $message, array $context): string
	{
		$logMessage = sprintf("[%s] %s: %s", $timestamp, $level, $message);

		if (!empty($context)) {
			$contextString = $this->formatContext($context);
			$logMessage .= " | " . $contextString;
		}

		return $logMessage . PHP_EOL;
	}

	private function formatContext(array $context): string
	{
		$parts = [];
		foreach ($context as $key => $value) {
			$parts[] = sprintf("%s: %s", $key, $this->formatContextValue($value));
		}
		return implode(" | ", $parts);
	}

	private function formatContextValue($value): string
	{
		if (is_array($value) || is_object($value)) {
			return json_encode($value, JSON_UNESCAPED_UNICODE);
		}
		return (string) $value;
	}

	private function getDefaultLogDirectory(): string
	{
		$documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
		return $documentRoot . DIRECTORY_SEPARATOR . 'logs';
	}

	private function setupLogFiles(): void
	{
		$suffix = $this->rotateDaily ? '_' . date('Y-m-d') : '';

		$this->errorLogFile = $this->logDirectory . DIRECTORY_SEPARATOR . 'error' . $suffix . '.log';
		$this->infoLogFile = $this->logDirectory . DIRECTORY_SEPARATOR . 'info' . $suffix . '.log';
	}

	private function ensureLogDirectoryExists(): void
	{
		if (!is_dir($this->logDirectory)) {
			mkdir($this->logDirectory, 0755, true);
		}

		// Crear archivos si no existen
		$this->createLogFileIfNotExists($this->errorLogFile);
		$this->createLogFileIfNotExists($this->infoLogFile);
	}

	private function createLogFileIfNotExists(string $filePath): void
	{
		if (!file_exists($filePath)) {
			touch($filePath);
			chmod($filePath, 0644);
		}
	}


	private function isDebugMode(): bool
	{
		return defined('DEBUG_MODE') && DEBUG_MODE === true;
	}

	public function cleanOldLogs(int $daysToKeep = 30): int
	{
		$deleted = 0;
		$cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);

		$files = glob($this->logDirectory . DIRECTORY_SEPARATOR . '*.log');

		foreach ($files as $file) {
			if (filemtime($file) < $cutoffTime) {
				if (unlink($file)) {
					$deleted++;
				}
			}
		}

		return $deleted;
	}
}
