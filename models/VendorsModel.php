<?php

class VendorsModel extends Model
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
            // SQL ACTUALIZADO: Se añaden las columnas faltantes
            $sql = "INSERT INTO {$this->table} (name, description, email, phone, sortOrder, createdBy, isVisible, status) 
                    VALUES (:name, :description, :email, :phone, :sortOrder, :createdBy, :isVisible, :status)";

            // PARÁMETROS ACTUALIZADOS: Se añaden los valores para las nuevas columnas
            $params = [
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null,
                ':email' => $data['email'],
                ':phone' => $data['phone'] ?? null,
                ':sortOrder' => $data['sortOrder'] ?? 0,      // <-- AÑADIDO
                ':createdBy' => $data['user_id'],
                ':isVisible' => $data['isVisible'] ?? 0,      // <-- AÑADIDO
                ':status' => $data['status'] ?? 1               // <-- AÑADIDO
            ];

            $rowsAffected = $this->db->execute($sql, $params);

            if ($rowsAffected > 0) {
                $vendorId = $this->db->getLastInsertId();
                $this->logger->logInfo("Proveedor creado exitosamente. ID: {$vendorId}", null, 'VendorsModel::create');
                return true; // <-- LA SOLUCIÓN
            }

            return false;
        } catch (PDOException $e) {
            $this->logger->logError("Error al crear proveedor: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * Obtiene los proveedores de forma paginada para la vista de lista.
     * Solo obtiene los registros no eliminados (soft delete).
     */
    public function getAllPaginated($limit, $offset)
    {
        try {
            $whereClause = "WHERE deletedBy IS NULL AND status = 1";

            // 1. Contar el total de proveedores activos
            $sqlCount = "SELECT COUNT(id) FROM {$this->table} {$whereClause}";
            $totalRows = $this->db->fetchColumn($sqlCount);

            // 2. Obtener los proveedores para la página actual
            $sql = "SELECT id, name, email, phone, description FROM {$this->table} 
                    {$whereClause} 
                    ORDER BY name ASC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->query($sql, [
                ':limit' => (int)$limit,
                ':offset' => (int)$offset
            ]);
            $result = $stmt->fetchAll();

            $this->logger->logInfo("Proveedores paginados obtenidos. Límite: $limit, Offset: $offset.", null, 'VendorsModel::getAllPaginated');

            return ['data' => $result, 'total' => (int)$totalRows];
        } catch (Exception $e) {
            $this->logger->logError("Error al obtener proveedores paginados: " . $e->getMessage(), __FILE__, __LINE__);
            return ['data' => [], 'total' => 0];
        }
    }
   

    /**
     * Elimina un proveedor (soft delete).
     */
    public function delete($id, $userId)
    {
        try {
            // Usamos 'vendor' como el nombre correcto de la tabla
            $sql = "UPDATE vendor SET 
                        deletedAt = NOW(), 
                        deletedBy = :deletedBy, 
                        status = 0 
                    WHERE id = :id";
            
            $params = [
                ':id' => $id,
                ':deletedBy' => $userId
            ];

            $rowsAffected = $this->db->execute($sql, $params);
            
            if ($rowsAffected > 0) {
                $this->logger->logInfo("Proveedor eliminado (soft delete) ID: {$id}", null, 'VendorsModel::delete');
                return true;
            }
            
            return false;

        } catch (PDOException $e) {
            $this->logger->logError("Error en soft delete de proveedor: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM vendor WHERE id = :id AND deletedAt IS NULL";
            $params = [':id' => $id];
            $result = $this->db->fetchAll($sql, $params);

            return $result[0] ?? null; // Devuelve la primera fila o null

        } catch (PDOException $e) {
            $this->logger->logError("Error al obtener proveedor por ID: " . $e->getMessage(), __FILE__, __LINE__);
            return null;
        }
    }


    public function update($id, $data)
    {
        try {
            $sql = "UPDATE vendor SET 
                        name = :name, 
                        description = :description, 
                        email = :email, 
                        phone = :phone,
                        updatedBy = :updatedBy
                    WHERE id = :id";
            
            $params = [
                ':id'          => $id,
                ':name'        => $data['name'],
                ':description' => $data['description'] ?? null,
                ':email'       => $data['email'],
                ':phone'       => $data['phone'] ?? null,
                ':updatedBy'   => $data['user_id']
            ];

            $rowsAffected = $this->db->execute($sql, $params);
            
            if ($rowsAffected > 0) {
                 $this->logger->logInfo("Proveedor actualizado ID: {$id}", null, 'VendorsModel::update');
                return true;
            }

            // Si no afectó filas (quizás no cambió nada), no lo contamos como error fatal.
            // La lógica del controlador decidirá si esto es un "éxito" o no.
            return true; 

        } catch (PDOException $e) {
            $this->logger->logError("Error al actualizar proveedor: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

}