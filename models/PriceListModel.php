<?php

class PriceListModel extends Model
{
	private $table = 'priceList';

	public $id;
	public $code;
	public $name;
	public $description;
	public $isDefault;
	public $status;
	public $isVisible;
	public $validFrom;
	public $validUntil;
	public $createdBy;
	public $createdAt;
	public $updatedBy;
	public $updatedAt;
	public $deletedBy;
	public $deletedAt;

	public function __construct()
	{
		parent::__construct();
	}

	public function create($data)
	{
		try {
			$sql = "INSERT INTO {$this->table} (code, name, description, isDefault, status, isVisible, validFrom, validUntil, createdBy) 
                    VALUES (:code, :name, :description, :isDefault, :status, :isVisible, :validFrom, :validUntil, :createdBy)";

			$params = [
				':code' => $data['code'],
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':isDefault' => $data['isDefault'] ?? 0,
				':status' => $data['status'] ?? 1,
				':isVisible' => $data['isVisible'] ?? 0,
				':validFrom' => $data['validFrom'] ?? null,
				':validUntil' => $data['validUntil'] ?? null,
				':createdBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$priceListId = $this->db->getLastInsertId();
				$this->logger->logInfo("Lista de precios creada exitosamente. ID: {$priceListId}", null, 'PriceListModel::create');
				return $priceListId;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear lista de precios: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getById($id)
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE id = :id AND status = 1";
			$params = [':id' => $id];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Lista de precios obtenida exitosamente. ID: {$id}", null, 'PriceListModel::getById');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener lista de precios: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAll($includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE status = 1";
			$sql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY isDefault DESC, name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Listas de precios obtenidas exitosamente. Total: " . count($result), null, 'PriceListModel::getAll');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener listas de precios: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET code = :code, name = :name, description = :description, 
                        isDefault = :isDefault, status = :status, isVisible = :isVisible,
                        validFrom = :validFrom, validUntil = :validUntil, updatedBy = :updatedBy
                    WHERE id = :id";

			$params = [
				':id' => $id,
				':code' => $data['code'],
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':isDefault' => $data['isDefault'] ?? 0,
				':status' => $data['status'] ?? 1,
				':isVisible' => $data['isVisible'] ?? 0,
				':validFrom' => $data['validFrom'] ?? null,
				':validUntil' => $data['validUntil'] ?? null,
				':updatedBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Lista de precios actualizada exitosamente. ID: {$id}", null, 'PriceListModel::update');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar lista de precios: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Lista de precios eliminada exitosamente. ID: {$id}", null, 'PriceListModel::delete');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar lista de precios: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByCode($code)
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE code = :code AND status = 1";
			$params = [':code' => $code];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Lista de precios obtenida por código exitosamente: {$code}", null, 'PriceListModel::getByCode');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener lista de precios por código: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getDefault()
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE isDefault = 1 AND status = 1 LIMIT 1";
			$result = $this->db->fetchAll($sql);

			if ($result) {
				$this->logger->logInfo("Lista de precios por defecto obtenida exitosamente", null, 'PriceListModel::getDefault');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener lista de precios por defecto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function setAsDefault($id, $userId)
	{
		try {
			// Primero quitar el default de todas las listas
			$sql1 = "UPDATE {$this->table} SET isDefault = 0, updatedBy = :updatedBy WHERE isDefault = 1";
			$this->db->execute($sql1, [':updatedBy' => $userId]);

			// Luego establecer la nueva lista como default
			$sql2 = "UPDATE {$this->table} SET isDefault = 1, updatedBy = :updatedBy WHERE id = :id";
			$params = [':id' => $id, ':updatedBy' => $userId];
			$rowsAffected = $this->db->execute($sql2, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Lista de precios establecida como por defecto. ID: {$id}", null, 'PriceListModel::setAsDefault');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al establecer lista de precios como por defecto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getValidPriceLists($date = null)
	{
		try {
			$currentDate = $date ?? date('Y-m-d');
			$sql = "SELECT * FROM {$this->table} 
                    WHERE status = 1 
                    AND (validFrom IS NULL OR validFrom <= :currentDate)
                    AND (validUntil IS NULL OR validUntil >= :currentDate)
                    ORDER BY isDefault DESC, name ASC";

			$params = [':currentDate' => $currentDate];
			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Listas de precios válidas obtenidas exitosamente. Fecha: {$currentDate}, Total: " . count($result), null, 'PriceListModel::getValidPriceLists');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener listas de precios válidas: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
}
