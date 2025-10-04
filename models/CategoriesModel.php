<?php

class CategoriesModel extends Model
{
	private $table = 'categories';

	public $id;
	public $number;
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
			$sql = "INSERT INTO {$this->table} (number, name, description, sortOrder, createdBy, isVisible, status) 
                    VALUES (:number, :name, :description, :sortOrder, :createdBy, :isVisible, :status)";

			$params = [
				':number' => $data['number'],
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':sortOrder' => $data['sortOrder'] ?? 0,
				':createdBy' => $data['user_id'],
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$categoryId = $this->db->getLastInsertId();
				$this->logger->logInfo("Categoría creada exitosamente. ID: {$categoryId}", null, 'CategoriesModel::create');
				return $categoryId;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear categoría: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Categoría obtenida exitosamente. ID: {$id}", null, 'CategoriesModel::getById');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener categoría por ID: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Categoría obtenida por nombre exitosamente: {$name}", null, 'CategoriesModel::getByName');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener categoría por nombre: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Categoría obtenida por número exitosamente: {$number}", null, 'CategoriesModel::getByNumber');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener categoría por número: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET number = :number, name = :name, description = :description, 
                        sortOrder = :sortOrder, isVisible = :isVisible, updatedBy = :updatedBy
                    WHERE id = :id";

			$params = [
				':id' => $id,
				':number' => $data['number'],
				':name' => $data['name'],
				':description' => $data['description'] ?? null,
				':sortOrder' => $data['sortOrder'] ?? 0,
				':isVisible' => $data['isVisible'] ?? 0,
				':updatedBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Categoría actualizada exitosamente. ID: {$id}", null, 'CategoriesModel::update');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar categoría: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function delete($id, $userId = null)
	{
		try {
			$sql = "UPDATE {$this->table} SET deletedBy = :deletedBy, deletedAt = NOW(), status = 0 WHERE id = :id";
			$params = [
				':id' => $id,
				':deletedBy' => $userId
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Categoría eliminada (soft delete) exitosamente. ID: {$id}", null, 'CategoriesModel::delete');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar categoría: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAllActive()
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE deletedBy IS NULL AND status = 1 ORDER BY sortOrder ASC, name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Categorías activas obtenidas exitosamente. Total: " . count($result), null, 'CategoriesModel::getAllActive');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener categorías activas: " . $e->getMessage(), __FILE__, __LINE__);
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

            $this->logger->logInfo("Categorías obtenidas para paginación. Límite: $limit, Offset: $offset", null, 'CategoriesModel::getAllPaginated');
            
            // Devolver tanto los datos de la página como el total
            return ['data' => $result, 'total' => (int)$totalRows];

        } catch (PDOException $e) {
            $this->logger->logError("Error al obtener categorías paginadas: " . $e->getMessage(), __FILE__, __LINE__);
            return ['data' => [], 'total' => 0];
        }
    }

	public function getAll($includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE status = 1";
			$sql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY sortOrder ASC, name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Categorías obtenidas exitosamente. Total: " . count($result), null, 'CategoriesModel::getAll');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener todas las categorías: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function getVisible()
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE status = 1 AND isVisible = 1 ORDER BY sortOrder ASC, name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Categorías visibles obtenidas exitosamente. Total: " . count($result), null, 'CategoriesModel::getVisible');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener categorías visibles: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function validateNumber($number, $excludeId = null)
	{
		try {
			$whereConditions = ["number = :number", "status = 1"];
			$params = [':number' => $number];

			if ($excludeId !== null) {
				$whereConditions[] = "id != :excludeId";
				$params[':excludeId'] = $excludeId;
			}

			$whereClause = implode(' AND ', $whereConditions);

			$sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}";
			$result = $this->db->fetchAll($sql, $params);

			$count = $result[0]['count'] ?? 0;
			return $count == 0; // Retorna true si el número es único
		} catch (PDOException $e) {
			$this->logger->logError("Error al validar número de categoría: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
}
