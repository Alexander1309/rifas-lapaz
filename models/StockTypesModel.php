<?php

class StockTypesModel extends Model
{
	private $table = 'stocktypes';

	public $id;
	public $code;
	public $name;
	public $description;
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
			$sql = "INSERT INTO {$this->table} (code, name, description, sortOrder, createdBy, isVisible, status) 
                    VALUES (:code, :name, :description, :sortOrder, :createdBy, :isVisible, :status)";

			$params = [
				':code' => $data['code'],
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':sortOrder' => $data['sortOrder'] ?? 0,
				':createdBy' => $data['user_id'],
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$stockTypeId = $this->db->getLastInsertId();
				$this->logger->logInfo("Tipo de stock creado exitosamente. ID: {$stockTypeId}", null, 'StockTypesModel::create');
				return $stockTypeId;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear tipo de stock: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Tipo de stock obtenido exitosamente. ID: {$id}", null, 'StockTypesModel::getById');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener tipo de stock: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAll($includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE status = 1";
			$sql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY sortOrder ASC, name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Tipos de stock obtenidos exitosamente. Total: " . count($result), null, 'StockTypesModel::getAll');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener tipos de stock: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
	public function getAllPaginated($limit, $offset, $includeInactive = false)
    {
        try {
            $whereClause = $includeInactive ? "" : "WHERE status = 1";

            // 1. Obtener el conteo total de registros
            $sqlCount = "SELECT COUNT(id) FROM {$this->table} {$whereClause}";
            $totalRows = $this->db->fetchColumn($sqlCount);

            // 2. Obtener los registros para la página actual
            $sql = "SELECT * FROM {$this->table} 
                    {$whereClause} 
                    ORDER BY sortOrder ASC, name ASC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->query($sql, [
                ':limit' => (int)$limit,
                ':offset' => (int)$offset
            ]);
            $result = $stmt->fetchAll();

            $this->logger->logInfo("Tipos de stock paginados obtenidos. Límite: $limit, Offset: $offset.", null, 'StockTypesModel::getAllPaginated');

            return ['data' => $result, 'total' => (int)$totalRows];
        } catch (Exception $e) {
            $this->logger->logError("Error al obtener tipos de stock paginados: " . $e->getMessage(), __FILE__, __LINE__);
            return ['data' => [], 'total' => 0];
        }
    }

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET code = :code, name = :name, description = :description, 
                        sortOrder = :sortOrder, updatedBy = :updatedBy, isVisible = :isVisible, status = :status
                    WHERE id = :id";

			$params = [
				':id' => $id,
				':code' => $data['code'],
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':sortOrder' => $data['sortOrder'] ?? 0,
				':updatedBy' => $data['user_id'],
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Tipo de stock actualizado exitosamente. ID: {$id}", null, 'StockTypesModel::update');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar tipo de stock: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Tipo de stock eliminado exitosamente. ID: {$id}", null, 'StockTypesModel::delete');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar tipo de stock: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Tipo de stock obtenido por código exitosamente: {$code}", null, 'StockTypesModel::getByCode');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener tipo de stock por código: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getVisible()
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE status = 1 AND isVisible = 1 ORDER BY sortOrder ASC, name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Tipos de stock visibles obtenidos exitosamente. Total: " . count($result), null, 'StockTypesModel::getVisible');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener tipos de stock visibles: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
}
