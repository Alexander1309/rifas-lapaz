<?php

class CustomersModel extends Model
{
    private $table = 'customers';

    // Propiedades basadas en la tabla 'customers'
    public $id;
    public $name;
    public $phone;
    public $email;
    public $idPriceList;
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

    public function getById($id)
	{
		try {
			$sql = "SELECT c.*, pl.name as priceListName 
                    FROM customers c 
                    LEFT JOIN priceList pl ON c.idPriceList = pl.id 
                    WHERE c.id = :id AND c.deletedAt IS NULL";

			$params = [':id' => $id];
			$result = $this->db->fetchAll($sql, $params);

			if (!empty($result)) {
				return [
					'success' => true,
					'data' => $result[0]
				];
			} else {
				return [
					'success' => false,
					'message' => 'Cliente no encontrado'
				];
			}
		} catch (Exception $e) {
			$this->logger->logError("Error al obtener cliente por ID: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getByEmail($email)
	{
		try {
			$sql = "SELECT c.*, pl.name as priceListName 
                    FROM customers c 
                    LEFT JOIN priceList pl ON c.idPriceList = pl.id 
                    WHERE c.email = :email AND c.deletedAt IS NULL";

			$params = [':email' => $email];
			$result = $this->db->fetchAll($sql, $params);

			return [
				'success' => true,
				'data' => !empty($result) ? $result[0] : null
			];
		} catch (Exception $e) {
			$this->logger->logError("Error al obtener cliente por email: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getAll($limit = 100, $offset = 0)
	{
		try {
			$sql = "SELECT c.*, pl.name as priceListName 
                    FROM customers c 
                    LEFT JOIN priceList pl ON c.idPriceList = pl.id 
                    WHERE c.deletedAt IS NULL AND c.status = 1 
                    ORDER BY c.name ASC 
                    LIMIT :limit OFFSET :offset";

			$params = [
				':limit' => $limit,
				':offset' => $offset
			];
			$result = $this->db->fetchAll($sql, $params);

			return [
				'success' => true,
				'data' => $result
			];
		} catch (Exception $e) {
			$this->logger->logError("Error al obtener todos los clientes: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}
    
    public function create($data)
    {
        try {
            // createdBy se incluye aquí
            $sql = "INSERT INTO {$this->table} (name, phone, email, idPriceList, createdBy) 
                    VALUES (:name, :phone, :email, :idPriceList, :createdBy)";

            $params = [
                ':name'        => $data['name'],
                ':phone'       => $data['phone'],
                ':email'       => $data['email'],
                ':idPriceList' => $data['idPriceList'] ?? null,
                ':createdBy'   => $data['user_id'] // Y se asigna aquí
            ];
            
            $rowsAffected = $this->db->execute($sql, $params);

            if ($rowsAffected > 0) {
                $customerId = $this->db->getLastInsertId();
                $this->logger->logInfo("Cliente creado exitosamente. ID: {$customerId}", null, 'CustomersModel::create');
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $errorMessage = "--- ERROR CAPTURADO EN CustomersModel --- " . $e->getMessage();
            error_log($errorMessage);
            return false;
        }
    }

    /**
     * Obtiene una lista paginada de clientes activos.
     */
    public function getAllPaginated($limit, $offset)
    {
        try {
            $whereClause = "WHERE deletedBy IS NULL AND status = 1";

            $sqlCount = "SELECT COUNT(id) FROM {$this->table} {$whereClause}";
            $totalRows = $this->db->fetchColumn($sqlCount);

            $sql = "SELECT id, name, phone, email FROM {$this->table} 
                    {$whereClause} 
                    ORDER BY name ASC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->query($sql, [':limit' => (int)$limit, ':offset' => (int)$offset]);
            $result = $stmt->fetchAll();

            return ['data' => $result, 'total' => (int)$totalRows];
        } catch (Exception $e) {
            $this->logger->logError("Error al obtener clientes paginados: " . $e->getMessage(), __FILE__, __LINE__);
            return ['data' => [], 'total' => 0];
        }
    }

    /**
     * Obtiene un cliente por su ID, si no está eliminado.
     */
    


    /**
     * Actualiza un cliente existente.
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                        name = :name, 
                        phone = :phone, 
                        email = :email, 
                        idPriceList = :idPriceList,
                        updatedBy = :updatedBy
                    WHERE id = :id";
            
            $params = [
                ':id'          => $id,
                ':name'        => $data['name'],
                ':phone'       => $data['phone'],
                ':email'       => $data['email'],
                ':idPriceList' => $data['idPriceList'] ?? null,
                ':updatedBy'   => $data['user_id']
            ];

            $this->db->execute($sql, $params);
            $this->logger->logInfo("Cliente actualizado ID: {$id}", null, 'CustomersModel::update');
            return true;
        } catch (PDOException $e) {
            $this->logger->logError("Error al actualizar cliente: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * Marca un cliente como eliminado (soft delete).
     */
    public function delete($id, $userId)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                        deletedAt = NOW(), 
                        deletedBy = :deletedBy, 
                        status = 0 
                    WHERE id = :id";
            
            $rowsAffected = $this->db->execute($sql, [':id' => $id, ':deletedBy' => $userId]);
            
            if ($rowsAffected > 0) {
                $this->logger->logInfo("Cliente eliminado (soft delete) ID: {$id}", null, 'CustomersModel::delete');
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            $this->logger->logError("Error en soft delete de cliente: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }
}  