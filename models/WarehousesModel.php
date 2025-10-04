<?php

class WarehousesModel extends Model
{
	private $table = 'warehouses';

	public $id;
	public $number;
	public $name;
	public $description;
	public $address;
	public $status;
	public $isVisible;
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
			$sql = "INSERT INTO {$this->table} (number, name, description, address, status, isVisible, createdBy) 
                    VALUES (:number, :name, :description, :address, :status, :isVisible, :createdBy)";

			$params = [
				':number' => $data['number'],
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':address' => $data['address'] ?? null,
				':status' => $data['status'] ?? 1,
				':isVisible' => $data['isVisible'] ?? 0,
				':createdBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$warehouseId = $this->db->getLastInsertId();
				$this->logger->logInfo("Almacén creado exitosamente. ID: {$warehouseId}", null, 'WarehousesModel::create');
				return $warehouseId;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear almacén: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Almacén obtenido exitosamente. ID: {$id}", null, 'WarehousesModel::getById');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener almacén: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAll($includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE status = 1";
			$sql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Almacenes obtenidos exitosamente. Total: " . count($result), null, 'WarehousesModel::getAll');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener almacenes: " . $e->getMessage(), __FILE__, __LINE__);
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
                    ORDER BY name ASC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->query($sql, [
                ':limit' => (int)$limit,
                ':offset' => (int)$offset
            ]);
            $result = $stmt->fetchAll();

            $this->logger->logInfo("Almacenes paginados obtenidos. Límite: $limit, Offset: $offset.", null, 'WarehousesModel::getAllPaginated');

            return ['data' => $result, 'total' => (int)$totalRows];
        } catch (Exception $e) {
            $this->logger->logError("Error al obtener almacenes paginados: " . $e->getMessage(), __FILE__, __LINE__);
            return ['data' => [], 'total' => 0];
        }
    }

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET number = :number, name = :name, description = :description, 
                        address = :address, status = :status, isVisible = :isVisible,
                        updatedBy = :updatedBy
                    WHERE id = :id";

			$params = [
				':id' => $id,
				':number' => $data['number'],
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':address' => $data['address'] ?? null,
				':status' => $data['status'] ?? 1,
				':isVisible' => $data['isVisible'] ?? 0,
				':updatedBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Almacén actualizado exitosamente. ID: {$id}", null, 'WarehousesModel::update');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar almacén: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Almacén eliminado exitosamente. ID: {$id}", null, 'WarehousesModel::delete');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar almacén: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByNumber($number)
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE number = :number AND status = 1";
			$params = [':number' => $number];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Almacén obtenido por número exitosamente: {$number}", null, 'WarehousesModel::getByNumber');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener almacén por número: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getVisible()
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE status = 1 AND isVisible = 1 ORDER BY name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Almacenes visibles obtenidos exitosamente. Total: " . count($result), null, 'WarehousesModel::getVisible');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener almacenes visibles: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
}
