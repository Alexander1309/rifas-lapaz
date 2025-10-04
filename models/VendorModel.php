<?php

class VendorModel extends Model
{
	private $table = 'vendor';

	public $id;
	public $name;
	public $description;
	public $email;
	public $phone;
	public $sortOrder;
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
			$sql = "INSERT INTO {$this->table} (name, description, email, phone, sortOrder, createdBy, isVisible, status)
                    VALUES (:name, :description, :email, :phone, :sortOrder, :createdBy, :isVisible, :status)";

			$params = [
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':email' => $data['email'],
				':phone' => $data['phone'],
				':sortOrder' => $data['sortOrder'] ?? 0,
				':createdBy' => $data['user_id'],
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1
			];

			$rows = $this->db->execute($sql, $params);
			if ($rows > 0) {
				$id = $this->db->getLastInsertId();
				$this->logger->logInfo("Proveedor creado exitosamente. ID: {$id}", null, 'VendorModel::create');
				return $id;
			}
			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear proveedor: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getById($id)
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE id = :id AND status = 1";
			$res = $this->db->fetchAll($sql, [':id' => $id]);
			return $res[0] ?? null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener proveedor: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAll($includeInactive = false)
	{
		try {
			$where = $includeInactive ? '' : 'WHERE status = 1';
			$sql = "SELECT * FROM {$this->table} {$where} ORDER BY sortOrder ASC, name ASC";
			return $this->db->fetchAll($sql);
		} catch (PDOException $e) {
			$this->logger->logError("Error al listar proveedores: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table}
                    SET name = :name, description = :description, email = :email, phone = :phone,
                        sortOrder = :sortOrder, isVisible = :isVisible, updatedBy = :updatedBy
                    WHERE id = :id";

			$params = [
				':id' => $id,
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':email' => $data['email'],
				':phone' => $data['phone'],
				':sortOrder' => $data['sortOrder'] ?? 0,
				':isVisible' => $data['isVisible'] ?? 0,
				':updatedBy' => $data['user_id']
			];

			$rows = $this->db->execute($sql, $params);
			if ($rows > 0) {
				$this->logger->logInfo("Proveedor actualizado. ID: {$id}", null, 'VendorModel::update');
				return true;
			}
			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar proveedor: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function delete($id, $userId)
	{
		try {
			$sql = "UPDATE {$this->table} SET status = 0, deletedBy = :deletedBy, deletedAt = NOW() WHERE id = :id";
			$params = [':id' => $id, ':deletedBy' => $userId];
			$rows = $this->db->execute($sql, $params);
			if ($rows > 0) {
				$this->logger->logInfo("Proveedor eliminado. ID: {$id}", null, 'VendorModel::delete');
				return true;
			}
			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar proveedor: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getVisible()
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE status = 1 AND isVisible = 1 ORDER BY sortOrder ASC, name ASC";
			return $this->db->fetchAll($sql);
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener proveedores visibles: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
}
