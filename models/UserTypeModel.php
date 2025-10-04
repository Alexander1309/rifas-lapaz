<?php

class UserTypeModel extends Model
{
	private $table = 'userType';

	public $id;
	public $name;
	public $description;
	public $createdBy;
	public $createdAt;
	public $updatedBy;
	public $updatedAt;
	public $deletedBy;
	public $deletedAt;
	public $isVisible;
	public $status;

	public function __construct()
	{
		parent::__construct();
	}

	public function create($data)
	{
		try {
			if (empty($data['name'])) {
				$this->logger->logError("Nombre de tipo de usuario requerido", __FILE__, __LINE__);
				return false;
			}

			$sql = "INSERT INTO {$this->table} (name, description, createdBy, isVisible, status) VALUES (:name, :description, :createdBy, :isVisible, :status)";

			$params = [
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':createdBy' => $data['user_id'],
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$id = $this->db->getLastInsertId();
				$this->logger->logInfo("Tipo de usuario creado. ID: {$id}", null, 'UserTypeModel::create');
				return $id;
			}
			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear tipo de usuario: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} SET name = :name, description = :description, isVisible = :isVisible, status = :status, updatedBy = :updatedBy, updatedAt = NOW() WHERE id = :id";
			$params = [
				':id' => $id,
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1,
				':updatedBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);
			if ($rowsAffected > 0) {
				$this->logger->logInfo("Tipo de usuario actualizado. ID: {$id}", null, 'UserTypeModel::update');
				return true;
			}
			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar tipo de usuario: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getById($id)
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE id = :id AND status = 1";
			$result = $this->db->fetchAll($sql, [':id' => $id]);
			return $result[0] ?? null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener tipo de usuario: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAll($includeInactive = false)
	{
		try {
			$where = $includeInactive ? '' : 'WHERE status = 1';
			$sql = "SELECT * FROM {$this->table} {$where} ORDER BY name ASC";
			return $this->db->fetchAll($sql);
		} catch (PDOException $e) {
			$this->logger->logError("Error al listar tipos de usuario: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function delete($id, $userId)
	{
		try {
			$sql = "UPDATE {$this->table} SET status = 0, deletedBy = :deletedBy, deletedAt = NOW() WHERE id = :id";
			$params = [':id' => $id, ':deletedBy' => $userId];
			$rowsAffected = $this->db->execute($sql, $params);
			if ($rowsAffected > 0) {
				$this->logger->logInfo("Tipo de usuario eliminado. ID: {$id}", null, 'UserTypeModel::delete');
				return true;
			}
			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar tipo de usuario: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
}
