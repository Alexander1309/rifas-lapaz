<?php
class Model
{
	public $db;
	public $pdo;
	public $logger;

	function __construct()
	{
		// Usar una única instancia de DB en toda la app para mayor consistencia
		$this->db = Database::getInstance();
		$this->pdo = $this->db->getConnection();
		$this->logger  = new Logger();
	}
}
