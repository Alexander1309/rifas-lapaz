<?php

class ProductCodesModel extends Model
{
	private $table = 'productCodes';

	public $id;
	public $idProduct;
	public $barCode;
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
			$sql = "INSERT INTO {$this->table} (idProduct, barCode, createdBy, isVisible, status) 
					VALUES (:idProduct, :barCode, :createdBy, :isVisible, :status)";

			$params = [
				':idProduct' => $data['idProduct'],
				':barCode' => $data['barCode'],
				':createdBy' => $data['user_id'],
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$codeId = $this->db->getLastInsertId();
				$this->logger->logInfo("Código de producto creado exitosamente. ID: {$codeId}", null, 'ProductCodesModel::create');
				return $codeId;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear código de producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getById($id)
	{
		try {
			$sql = "SELECT pc.*, p.name as product_name 
                    FROM {$this->table} pc
                    LEFT JOIN products p ON pc.idProduct = p.id
                    WHERE pc.id = :id AND pc.status = 1";
			$params = [':id' => $id];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Código de producto obtenido exitosamente. ID: {$id}", null, 'ProductCodesModel::getById');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener código de producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByProduct($productId)
	{
		try {
			$sql = "SELECT pc.*, p.name as product_name 
                    FROM {$this->table} pc
                    LEFT JOIN products p ON pc.idProduct = p.id
                    WHERE pc.idProduct = :productId AND pc.status = 1
                    ORDER BY pc.createdAt ASC";

			$params = [':productId' => $productId];
			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Códigos de producto obtenidos por producto. Producto ID: {$productId}, Total: " . count($result), null, 'ProductCodesModel::getByProduct');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener códigos por producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByBarCode($barCode)
	{
		try {
			$sql = "SELECT pc.*, p.name as product_name 
                    FROM {$this->table} pc
                    LEFT JOIN products p ON pc.idProduct = p.id
                    WHERE pc.barCode = :barCode AND pc.status = 1";

			$params = [':barCode' => $barCode];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Código de producto obtenido por código de barras: {$barCode}", null, 'ProductCodesModel::getByBarCode');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener producto por código de barras: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
					SET barCode = :barCode, isVisible = :isVisible, updatedBy = :updatedBy
					WHERE id = :id";

			$params = [
				':id' => $id,
				':barCode' => $data['barCode'],
				':isVisible' => $data['isVisible'] ?? 0,
				':updatedBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Código de producto actualizado exitosamente. ID: {$id}", null, 'ProductCodesModel::update');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar código de producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function delete($id, $userId)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET status = 0, deletedBy = :deletedBy, deletedAt = NOW()
                    WHERE id = :id";

			$params = [
				':id' => $id,
				':deletedBy' => $userId
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Código de producto eliminado exitosamente. ID: {$id}", null, 'ProductCodesModel::delete');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar código de producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAll($includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE pc.status = 1";
			$sql = "SELECT pc.*, p.name as product_name 
                    FROM {$this->table} pc
                    LEFT JOIN products p ON pc.idProduct = p.id
                    {$whereClause}
                    ORDER BY p.name ASC, pc.barCode ASC";

			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Códigos de productos obtenidos exitosamente. Total: " . count($result), null, 'ProductCodesModel::getAll');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener códigos de productos: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function validateBarCode($barCode, $productId = null, $excludeId = null)
	{
		try {
			$whereConditions = ["barCode = :barCode", "status = 1"];
			$params = [':barCode' => $barCode];

			if ($excludeId !== null) {
				$whereConditions[] = "id != :excludeId";
				$params[':excludeId'] = $excludeId;
			}

			$whereClause = implode(' AND ', $whereConditions);

			$sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}";
			$result = $this->db->fetchAll($sql, $params);

			$count = $result[0]['count'] ?? 0;
			return $count == 0; // Retorna true si el código es único
		} catch (PDOException $e) {
			$this->logger->logError("Error al validar código de barras: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
}
