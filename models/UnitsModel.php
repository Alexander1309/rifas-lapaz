<?php

class UnitsModel extends Model
{
	private $table = 'units';

	public $id;
	public $name;
	public $abbreviation; // Nueva columna en la tabla
	public $createdBy;
	public $createdAt;
	public $updatedBy;
	public $updatedAt;
	public $deletedBy;
	public $deletedAt;
	public $status;

	public function __construct()
	{
		parent::__construct();
	}

	public function create($data)
    {
        try {
            // Se incluyen las columnas obligatorias de la tabla 'units'
            $sql = "INSERT INTO {$this->table} (name, abbreviation, createdBy, status) 
                    VALUES (:name, :abbreviation, :createdBy, :status)";

            $params = [
                ':name'         => $data['name'],
                ':abbreviation' => $data['abbreviation'],
                ':createdBy'    => $data['user_id'],
                ':status'       => (string)($data['status'] ?? 1)
            ];
            
            $rowsAffected = $this->db->execute($sql, $params);

            if ($rowsAffected > 0) {
                $unitId = $this->db->getLastInsertId();
                $this->logger->logInfo("Unidad creada exitosamente. ID: {$unitId}", null, 'UnitsModel::create');
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $errorMessage = "--- ERROR CAPTURADO EN UnitsModel --- " . $e->getMessage();
            error_log($errorMessage);
            return false;
        }
    }


	public function getById($id)
	{
		try {
			$sql = "SELECT id, name, abbreviation, createdBy, createdAt, updatedBy, updatedAt, deletedBy, deletedAt, status 
					FROM {$this->table} WHERE id = :id AND status = 1";
			$params = [':id' => $id];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Unidad obtenida exitosamente. ID: {$id}", null, 'UnitsModel::getById');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener unidad por ID: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByName($name)
	{
		try {
			$sql = "SELECT id, name, abbreviation, createdBy, createdAt, updatedBy, updatedAt, deletedBy, deletedAt, status 
					FROM {$this->table} WHERE name = :name AND status = 1";
			$params = [':name' => $name];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Unidad obtenida por nombre exitosamente: {$name}", null, 'UnitsModel::getByName');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener unidad por nombre: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
					SET name = :name, abbreviation = :abbreviation, updatedBy = :updatedBy
					WHERE id = :id";

			$params = [
				':id' => $id,
				':name' => $data['name'],
				':abbreviation' => $data['abbreviation'] ?? substr($data['name'], 0, 3),
				':updatedBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Unidad actualizada exitosamente. ID: {$id}", null, 'UnitsModel::update');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar unidad: " . $e->getMessage(), __FILE__, __LINE__);
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

	public function getAllActive()
	{
		try {
			$sql = "SELECT id, name, abbreviation, createdBy, createdAt, updatedBy, updatedAt, deletedBy, deletedAt, status 
					FROM {$this->table} WHERE deletedBy IS NULL AND status = 1 ORDER BY name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Unidades activas obtenidas exitosamente. Total: " . count($result), null, 'UnitsModel::getAllActive');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener unidades activas: " . $e->getMessage(), __FILE__, __LINE__);
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
            $sql = "SELECT id, name, abbreviation FROM {$this->table} {$whereClause} 
                    ORDER BY name ASC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->query($sql, [
                ':limit' => (int)$limit,
                ':offset' => (int)$offset
            ]);
            $result = $stmt->fetchAll();

            $this->logger->logInfo("Unidades obtenidas para paginación. Límite: $limit, Offset: $offset", null, 'UnitsModel::getAllPaginated');
            
            // Devolver tanto los datos de la página como el total
            return ['data' => $result, 'total' => (int)$totalRows];

        } catch (PDOException $e) {
            $this->logger->logError("Error al obtener unidades paginadas: " . $e->getMessage(), __FILE__, __LINE__);
            return ['data' => [], 'total' => 0];
        }
    }

	public function getAll($includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE status = 1";
			$sql = "SELECT id, name, abbreviation, createdBy, createdAt, updatedBy, updatedAt, deletedBy, deletedAt, status 
					FROM {$this->table} {$whereClause} ORDER BY name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Unidades obtenidas exitosamente. Total: " . count($result), null, 'UnitsModel::getAll');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener todas las unidades: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function validateName($name, $excludeId = null)
	{
		try {
			$whereConditions = ["name = :name", "status = 1"];
			$params = [':name' => $name];

			if ($excludeId !== null) {
				$whereConditions[] = "id != :excludeId";
				$params[':excludeId'] = $excludeId;
			}

			$whereClause = implode(' AND ', $whereConditions);

			$sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}";
			$result = $this->db->fetchAll($sql, $params);

			$count = $result[0]['count'] ?? 0;
			return $count == 0; // Retorna true si el nombre es único
		} catch (PDOException $e) {
			$this->logger->logError("Error al validar nombre de unidad: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByAbbreviation($abbreviation)
	{
		try {
			$sql = "SELECT id, name, abbreviation FROM {$this->table} WHERE abbreviation = :abbr AND status = 1";
			$result = $this->db->fetchAll($sql, [':abbr' => $abbreviation]);
			return $result[0] ?? null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener unidad por abreviatura: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
}
