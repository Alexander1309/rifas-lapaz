<?php

class BrandsModel extends Model
{
	private $table = 'brands';

	public $id;
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
			$sql = "INSERT INTO {$this->table} (name, description, sortOrder, createdBy, isVisible, status) 
                    VALUES (:name, :description, :sortOrder, :createdBy, :isVisible, :status)";

			$params = [
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':sortOrder' => $data['sortOrder'] ?? 0,
				':createdBy' => $data['user_id'],
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$brandId = $this->db->getLastInsertId();
				$this->logger->logInfo("Marca creada exitosamente. ID: {$brandId}", null, 'BrandsModel::create');
				return $brandId;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear marca: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Marca obtenida exitosamente. ID: {$id}", null, 'BrandsModel::getById');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener marca por ID: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByName($name)
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE name = :name AND status = 1";
			$params = [':name' => $name];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Marca obtenida por nombre exitosamente: {$name}", null, 'BrandsModel::getByName');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener marca por nombre: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function delete($id, $userId)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                        deletedAt = NOW(), 
                        deletedBy = :deletedBy, 
                        status = 0 
                    WHERE id = :id";
            
            $params = [
                ':id'        => $id,
                ':deletedBy' => $userId
            ];

            $rowsAffected = $this->db->execute($sql, $params);
            
            if ($rowsAffected > 0) {
                $this->logger->logInfo("Unidad eliminada (soft delete) ID: {$id}", null, 'UnitsModel::delete');
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            $errorMessage = "--- ERROR CAPTURADO EN UnitsModel::delete --- " . $e->getMessage();
            error_log($errorMessage);
            return false;
        }
    }
	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET name = :name, description = :description, sortOrder = :sortOrder, 
                        isVisible = :isVisible, updatedBy = :updatedBy
                    WHERE id = :id";

			$params = [
				':id' => $id,
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':sortOrder' => $data['sortOrder'] ?? 0,
				':isVisible' => $data['isVisible'] ?? 0,
				':updatedBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Marca actualizada exitosamente. ID: {$id}", null, 'BrandsModel::update');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar marca: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAllActive()
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE deletedBy IS NULL AND status = 1 ORDER BY sortOrder ASC, name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Marcas activas obtenidas exitosamente. Total: " . count($result), null, 'BrandsModel::getAllActive');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener marcas activas: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function getAllPaginated($limit, $offset)
    {
        try {
            $whereClause = "WHERE deletedBy IS NULL AND status = 1";

            // 1. Obtener el conteo total de registros que cumplen la condición
            $sqlCount = "SELECT COUNT(id) FROM {$this->table} {$whereClause}";
            $totalRows = $this->db->fetchColumn($sqlCount);

            // 2. Obtener los registros para la página actual
            $sql = "SELECT * FROM {$this->table} {$whereClause} 
                    ORDER BY sortOrder ASC, name ASC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->query($sql, [
                ':limit' => (int)$limit,
                ':offset' => (int)$offset
            ]);
            $result = $stmt->fetchAll();

            $this->logger->logInfo("Marcas obtenidas para paginación. Límite: $limit, Offset: $offset", null, 'BrandsModel::getAllPaginated');
            
            // Devolver tanto los datos de la página como el total
            return ['data' => $result, 'total' => (int)$totalRows];

        } catch (PDOException $e) {
            $this->logger->logError("Error al obtener marcas paginadas: " . $e->getMessage(), __FILE__, __LINE__);
            return ['data' => [], 'total' => 0];
        }
    }
	
	public function getAll($includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE status = 1";
			$sql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY sortOrder ASC, name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Marcas obtenidas exitosamente. Total: " . count($result), null, 'BrandsModel::getAll');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener todas las marcas: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function getVisible()
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE status = 1 AND isVisible = 1 ORDER BY sortOrder ASC, name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Marcas visibles obtenidas exitosamente. Total: " . count($result), null, 'BrandsModel::getVisible');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener marcas visibles: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}
}
