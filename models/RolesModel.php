<?php

class RolesModel extends Model
{
	private $table = 'roles';

	public $id;
	public $name;
	public $description;

	public function __construct()
	{
		parent::__construct();
	}

	public function create($data)
	{
		try {
			$sql = "INSERT INTO {$this->table} (name, description) 
                    VALUES (:name, :description)";

			$params = [
				':name' => $data['name'],
				':description' => $data['description']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$roleId = $this->db->getLastInsertId();
				$this->logger->logInfo("Rol creado exitosamente. ID: {$roleId}", null, 'RolesModel::create');
				return $roleId;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear rol: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getById($id)
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE id = :id";
			$params = [':id' => $id];
			$result = $this->db->fetchOne($sql, $params);

			if ($result) {
				$this->logger->logInfo("Rol obtenido exitosamente. ID: {$id}", null, 'RolesModel::getById');
			}

			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener rol: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAll()
	{
		try {
			$sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Roles obtenidos exitosamente. Total: " . count($result), null, 'RolesModel::getAll');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener roles: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET name = :name, description = :description
                    WHERE id = :id";

			$params = [
				':id' => $id,
				':name' => $data['name'],
				':description' => $data['description']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Rol actualizado exitosamente. ID: {$id}", null, 'RolesModel::update');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar rol: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function delete($id)
	{
		try {
			$sql = "DELETE FROM {$this->table} WHERE id = :id";
			$params = [':id' => $id];
			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Rol eliminado exitosamente. ID: {$id}", null, 'RolesModel::delete');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar rol: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByName($name)
	{
		try {
			$sql = "SELECT * FROM {$this->table} WHERE name = :name";
			$params = [':name' => $name];
			$result = $this->db->fetchOne($sql, $params);

			if ($result) {
				$this->logger->logInfo("Rol obtenido por nombre exitosamente: {$name}", null, 'RolesModel::getByName');
			}

			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener rol por nombre: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
}
