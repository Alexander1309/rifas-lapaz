<?php

class UserModel extends Model
{
    private $table = 'users';

    // Propiedades de la tabla 'users'
    public $id;
    public $username;
    public $password;
    public $idUserType; // Clave foránea a la tabla UserType
    public $createdBy;  // ID del usuario que creó el registro
    public $createdAt;
    public $updatedBy;  // ID del usuario que actualizó el registro
    public $updatedAt;
    public $deletedBy;  // ID del usuario que eliminó el registro
    public $deletedAt;
    public $status;


    public function __construct()
    {
        parent::__construct();
    }

    public function create($data)
    {
        try {
            // Se actualiza la consulta para incluir idUserType
            $sql = "INSERT INTO {$this->table} (username, password, idUserType, createdBy, updatedBy, updatedAt, status) 
                    VALUES (:username, :password, :idUserType, :createdBy, :updatedBy, NOW(), :status)";

            $hashedPassword = hash('sha256', $data['password']);

            $params = [
                ':username'   => $data['username'],
                ':password'   => $hashedPassword,
                ':idUserType' => $data['idUserType'], // Nuevo campo
                ':createdBy'  => $data['user_id'],
                ':updatedBy'  => $data['user_id'],
                ':status'     => $data['status'],
            ];

            $rowsAffected = $this->db->execute($sql, $params);

            if ($rowsAffected > 0) {
                $userId = $this->db->getLastInsertId();
                $this->logger->logInfo("Usuario creado exitosamente. ID: {$userId}", null, 'UserModel::create');
                return $userId;
            }

            return false;
        } catch (PDOException $e) {
            $this->logger->logError("Error al crear usuario: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    public function getByUsernameAndPassword($username, $password)
    {
        try {
            $hashedPassword = hash('sha256', $password);
            // Se actualiza el JOIN a la tabla UserType
            $sql = "SELECT u.*, ut.name as user_type_name 
            FROM {$this->table} u 
            LEFT JOIN userType ut ON u.idUserType = ut.id
                    WHERE username = :username AND password = :password AND u.status = 1 AND u.deletedBy IS NULL";
            $params = [
                ':username' => $username,
                ':password' => $hashedPassword
            ];

            $result = $this->db->fetchAll($sql, $params);

            if ($result) {
                $this->logger->logInfo("Usuario autenticado exitosamente: {$username}", null, 'UserModel::getByUsernameAndPassword');
                return $result[0];
            }

            $this->logger->logInfo("Intento de autenticación fallido para: {$username}", null, 'UserModel::getByUsernameAndPassword');
            return null;
        } catch (PDOException $e) {
            $this->logger->logError("Error en autenticación: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    public function getById($id)
    {
        try {
            // Se actualiza el JOIN a la tabla UserType
            $sql = "SELECT u.*, ut.name as user_type_name 
            FROM {$this->table} u 
            LEFT JOIN userType ut ON u.idUserType = ut.id
                    WHERE u.id = :id AND u.status = 1";
            $params = [':id' => $id];
            $result = $this->db->fetchAll($sql, $params);

            if ($result) {
                $this->logger->logInfo("Usuario obtenido exitosamente. ID: {$id}", null, 'UserModel::getById');
                return $result[0];
            }

            return null;
        } catch (PDOException $e) {
            $this->logger->logError("Error al obtener usuario: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    public function getByUsername($username)
    {
        try {
            // Se actualiza el JOIN a la tabla UserType
            $sql = "SELECT u.*, ut.name as user_type_name 
            FROM {$this->table} u 
            LEFT JOIN userType ut ON u.idUserType = ut.id
                    WHERE u.username = :username AND u.status = 1";
            $params = [':username' => $username];
            $result = $this->db->fetchAll($sql, $params);

            if ($result) {
                $this->logger->logInfo("Usuario obtenido por username: {$username}", null, 'UserModel::getByUsername');
                return $result[0];
            }

            return null;
        } catch (PDOException $e) {
            $this->logger->logError("Error al obtener usuario por username: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    public function update($id, $data)
    {
        try {
            $fields = [];
            $params = [':id' => $id];

            if (isset($data['username'])) {
                $fields[] = "username = :username";
                $params[':username'] = $data['username'];
            }

            if (isset($data['password']) && !empty($data['password'])) {
                $fields[] = "password = :password";
                $params[':password'] = hash('sha256', $data['password']);
            }

            // Se actualiza para manejar idUserType
            if (isset($data['idUserType'])) {
                $fields[] = "idUserType = :idUserType";
                $params[':idUserType'] = $data['idUserType'];
            }

            if (isset($data['status'])) {
                $fields[] = "status = :status";
                $params[':status'] = $data['status'];
            }

            $fields[] = "updatedBy = :updatedBy";
            $fields[] = "updatedAt = NOW()";

            $params[':updatedBy'] = $data['user_id'] ?? null;

            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id AND deletedBy IS NULL";

            $rowsAffected = $this->db->execute($sql, $params);

            if ($rowsAffected > 0) {
                $this->logger->logInfo("Usuario actualizado exitosamente. ID: {$id}", null, 'UserModel::update');
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->logger->logError("Error al actualizar usuario: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    public function delete($id, $user_id = null)
    {
        try {
            $sql = "UPDATE {$this->table} SET deletedBy = :deletedBy, deletedAt = NOW(), status = 0 WHERE id = :id";
            $params = [
                ':id' => $id,
                ':deletedBy' => $user_id
            ];

            $rowsAffected = $this->db->execute($sql, $params);

            if ($rowsAffected > 0) {
                $this->logger->logInfo("Usuario eliminado (soft delete) exitosamente. ID: {$id}", null, 'UserModel::delete');
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->logger->logError("Error al eliminar usuario: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    public function restore($id, $user_id = null)
    {
        try {
            $sql = "UPDATE {$this->table} SET deletedBy = NULL, deletedAt = NULL, status = 1, updatedBy = :updatedBy, updatedAt = NOW() WHERE id = :id";
            $params = [
                ':id' => $id,
                ':updatedBy' => $user_id
            ];

            $rowsAffected = $this->db->execute($sql, $params);

            if ($rowsAffected > 0) {
                $this->logger->logInfo("Usuario restaurado exitosamente. ID: {$id}", null, 'UserModel::restore');
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->logger->logError("Error al restaurar usuario: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    public function getAllActive()
    {
        try {
            // Se actualiza el JOIN a la tabla UserType
            $sql = "SELECT u.*, ut.name as user_type_name 
            FROM {$this->table} u 
            LEFT JOIN userType ut ON u.idUserType = ut.id
                    WHERE u.deletedBy IS NULL AND u.status = 1 ORDER BY u.username ASC";
            $result = $this->db->fetchAll($sql);

            $this->logger->logInfo("Usuarios activos obtenidos exitosamente. Total: " . count($result), null, 'UserModel::getAllActive');
            return $result;
        } catch (PDOException $e) {
            $this->logger->logError("Error al obtener usuarios activos: " . $e->getMessage(), __FILE__, __LINE__);
            return [];
        }
    }
	public function getAllPaginated($limit, $offset)
    {
        try {
            $whereClause = "WHERE u.deletedBy IS NULL AND u.status = 1";

            // 1. Obtener el conteo total de registros que cumplen la condición
            $sqlCount = "SELECT COUNT(u.id) FROM {$this->table} u {$whereClause}";
            $totalRows = $this->db->fetchColumn($sqlCount);

            // 2. Obtener los registros para la página actual
            $sql = "SELECT u.id, u.username, ut.name as user_type_name 
                    FROM {$this->table} u 
                    LEFT JOIN userType ut ON u.idUserType = ut.id
                    {$whereClause} 
                    ORDER BY u.username ASC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->query($sql, [
                ':limit' => (int)$limit,
                ':offset' => (int)$offset
            ]);
            $result = $stmt->fetchAll();

            $this->logger->logInfo("Usuarios obtenidos para paginación. Límite: $limit, Offset: $offset", null, 'UserModel::getAllPaginated');
            
            // Devolver tanto los datos de la página como el total
            return ['data' => $result, 'total' => (int)$totalRows];

        } catch (PDOException $e) {
            $this->logger->logError("Error al obtener usuarios paginados: " . $e->getMessage(), __FILE__, __LINE__);
            return ['data' => [], 'total' => 0];
        }
    }

    public function getAll($includeInactive = false)
    {
        try {
            $whereClause = $includeInactive ? "" : "WHERE u.status = 1";
            // Se actualiza el JOIN a la tabla UserType
            $sql = "SELECT u.*, ut.name as user_type_name 
            FROM {$this->table} u 
            LEFT JOIN userType ut ON u.idUserType = ut.id
                    {$whereClause} ORDER BY u.username ASC";
            $result = $this->db->fetchAll($sql);

            $this->logger->logInfo("Usuarios obtenidos exitosamente. Total: " . count($result), null, 'UserModel::getAll');
            return $result;
        } catch (PDOException $e) {
            $this->logger->logError("Error al obtener usuarios: " . $e->getMessage(), __FILE__, __LINE__);
            return [];
        }
    }

    public function updateStatus($id, $status, $user_id = null)
    {
        try {
            $sql = "UPDATE {$this->table} SET status = :status, updatedBy = :updatedBy, updatedAt = NOW() WHERE id = :id AND deletedBy IS NULL";
            $params = [
                ':id' => $id,
                ':status' => $status,
                ':updatedBy' => $user_id
            ];

            $rowsAffected = $this->db->execute($sql, $params);

            if ($rowsAffected > 0) {
                $statusText = $status ? 'activado' : 'desactivado';
                $this->logger->logInfo("Usuario {$statusText} exitosamente. ID: {$id}", null, 'UserModel::updateStatus');
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->logger->logError("Error al actualizar estado del usuario: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    public function validateUsername($username, $excludeId = null)
    {
        try {
            $whereConditions = ["username = :username", "status = 1"];
            $params = [':username' => $username];

            if ($excludeId !== null) {
                $whereConditions[] = "id != :excludeId";
                $params[':excludeId'] = $excludeId;
            }

            $whereClause = implode(' AND ', $whereConditions);

            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}";
            $result = $this->db->fetchAll($sql, $params);

            $count = $result[0]['count'] ?? 0;
            return $count == 0; // Retorna true si el username es único
        } catch (PDOException $e) {
            $this->logger->logError("Error al validar username: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    // Método renombrado de getUsersByRole a getUsersByUserType
    public function getUsersByUserType($userTypeId)
    {
        try {
            // Se actualiza la consulta y el JOIN
            $sql = "SELECT u.*, ut.name as user_type_name 
            FROM {$this->table} u 
            LEFT JOIN userType ut ON u.idUserType = ut.id
                    WHERE u.idUserType = :userTypeId AND u.status = 1 AND u.deletedBy IS NULL
                    ORDER BY u.username ASC";
            $params = [':userTypeId' => $userTypeId];
            $result = $this->db->fetchAll($sql, $params);

            $this->logger->logInfo("Usuarios por tipo obtenidos. Tipo ID: {$userTypeId}, Total: " . count($result), null, 'UserModel::getUsersByUserType');
            return $result;
        } catch (PDOException $e) {
            $this->logger->logError("Error al obtener usuarios por tipo: " . $e->getMessage(), __FILE__, __LINE__);
            return [];
        }
    }

    public function changePassword($id, $newPassword, $userId)
    {
        try {
            $hashedPassword = hash('sha256', $newPassword);
            $sql = "UPDATE {$this->table} SET password = :password, updatedBy = :updatedBy, updatedAt = NOW() WHERE id = :id";
            $params = [
                ':id' => $id,
                ':password' => $hashedPassword,
                ':updatedBy' => $userId
            ];

            $rowsAffected = $this->db->execute($sql, $params);

            if ($rowsAffected > 0) {
                $this->logger->logInfo("Contraseña actualizada exitosamente. Usuario ID: {$id}", null, 'UserModel::changePassword');
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->logger->logError("Error al cambiar contraseña: " . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }
}
