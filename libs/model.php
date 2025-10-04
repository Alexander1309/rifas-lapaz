<?php
class Model
{
	public $db;
	public $pdo;
	public $logger;

	function __construct()
	{
		$this->db = new Database();
		$this->pdo = $this->db->getConnection();
		$this->logger  = new Logger();
	}
}
