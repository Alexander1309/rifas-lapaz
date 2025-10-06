<?php

class UserModel extends Model
{
	private $table = 'user';

	public $id;
	public $username;
	public $password;
	public $idRol;
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
			$sql = "INSERT INTO {$this->table} (username, password, idRol, createdBy, status) 
                    VALUES (:username, :password, :idRol, :createdBy, :status)";

			$hashedPassword = hash('sha256', $data['password']);

			$params = [
				':username' => $data['username'],
				':password' => $hashedPassword,
				':idRol' => $data['idRol'],
				':createdBy' => $data['user_id'],
				':status' => $data['status'] ?? 1
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
			$sql = "SELECT u.*, r.name as role_name 
                    FROM {$this->table} u 
                    LEFT JOIN role r ON u.idRol = r.id
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
			$sql = "SELECT u.*, r.name as role_name 
                    FROM {$this->table} u 
                    LEFT JOIN role r ON u.idRol = r.id
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
			$sql = "SELECT u.*, r.name as role_name 
                    FROM {$this->table} u 
                    LEFT JOIN role r ON u.idRol = r.id
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

			if (isset($data['idRol'])) {
				$fields[] = "idRol = :idRol";
				$params[':idRol'] = $data['idRol'];
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
			$sql = "SELECT u.*, r.name as role_name 
                    FROM {$this->table} u 
                    LEFT JOIN role r ON u.idRol = r.id
                    WHERE u.deletedBy IS NULL AND u.status = 1 ORDER BY u.username ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Usuarios activos obtenidos exitosamente. Total: " . count($result), null, 'UserModel::getAllActive');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener usuarios activos: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function getAll($includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE u.status = 1";
			$sql = "SELECT u.*, r.name as role_name 
                    FROM {$this->table} u 
                    LEFT JOIN role r ON u.idRol = r.id
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

	public function getUsersByRole($roleId)
	{
		try {
			$sql = "SELECT u.*, r.name as role_name 
                    FROM {$this->table} u 
                    LEFT JOIN role r ON u.idRol = r.id
                    WHERE u.idRol = :roleId AND u.status = 1 AND u.deletedBy IS NULL
                    ORDER BY u.username ASC";
			$params = [':roleId' => $roleId];
			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Usuarios por rol obtenidos. Rol ID: {$roleId}, Total: " . count($result), null, 'UserModel::getUsersByRole');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener usuarios por rol: " . $e->getMessage(), __FILE__, __LINE__);
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

	public function countActive()
	{
		try {
			$sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 1 AND deletedBy IS NULL";
			$result = $this->db->fetchOne($sql);
			$total = (int)$result['total'];
			$this->logger->logInfo("Conteo de usuarios activos: {$total}", null, 'UserModel::countActive');
			return $total;
		} catch (PDOException $e) {
			$this->logger->logError("Error al contar usuarios activos: " . $e->getMessage(), __FILE__, __LINE__);
			return 0;
		}
	}
}
