<?php

class KitComponentsModel extends Model
{
	private $table = 'kitcomponents';

	public $id;
	public $idProductKit;
	public $idProductComponent;
	public $quantity;
	public $idStockType;
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
			$sql = "INSERT INTO {$this->table} (idProductKit, idProductComponent, quantity, idStockType, sortOrder, createdBy, isVisible, status) 
                    VALUES (:idProductKit, :idProductComponent, :quantity, :idStockType, :sortOrder, :createdBy, :isVisible, :status)";

			$params = [
				':idProductKit' => $data['idProductKit'],
				':idProductComponent' => $data['idProductComponent'],
				':quantity' => $data['quantity'] ?? 1,
				':idStockType' => $data['idStockType'],
				':sortOrder' => $data['sortOrder'] ?? 0,
				':createdBy' => $data['user_id'],
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$componentId = $this->db->getLastInsertId();
				$this->logger->logInfo("Componente de kit creado exitosamente. ID: {$componentId}", null, 'KitComponentsModel::create');
				return $componentId;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear componente de kit: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getById($id)
	{
		try {
			$sql = "SELECT kc.*, 
                           pk.name as kit_name, 
                           pc.name as component_name,
                           st.name as stock_type_name
                    FROM {$this->table} kc
                    LEFT JOIN products pk ON kc.idProductKit = pk.id
                    LEFT JOIN products pc ON kc.idProductComponent = pc.id
                    LEFT JOIN stocktypes st ON kc.idStockType = st.id
	                    WHERE kc.id = :id AND kc.status = 1";
			$params = [':id' => $id];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Componente de kit obtenido exitosamente. ID: {$id}", null, 'KitComponentsModel::getById');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener componente de kit: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByKit($kitId)
	{
		try {
			$sql = "SELECT kc.*, 
                           pk.name as kit_name, 
                           pc.name as component_name,
                           pc.baseCost as component_cost,
                           st.name as stock_type_name,
                           st.code as stock_type_code
                    FROM {$this->table} kc
                    LEFT JOIN products pk ON kc.idProductKit = pk.id
                    LEFT JOIN products pc ON kc.idProductComponent = pc.id
                    LEFT JOIN stocktypes st ON kc.idStockType = st.id
	                    WHERE kc.idProductKit = :kitId AND kc.status = 1
                    ORDER BY kc.sortOrder ASC, pc.name ASC";

			$params = [':kitId' => $kitId];
			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Componentes de kit obtenidos por kit. Kit ID: {$kitId}, Total: " . count($result), null, 'KitComponentsModel::getByKit');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener componentes por kit: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByComponent($componentId)
	{
		try {
			$sql = "SELECT kc.*, 
                           pk.name as kit_name, 
                           pc.name as component_name,
                           st.name as stock_type_name
                    FROM {$this->table} kc
                    LEFT JOIN products pk ON kc.idProductKit = pk.id
                    LEFT JOIN products pc ON kc.idProductComponent = pc.id
                    LEFT JOIN stocktypes st ON kc.idStockType = st.id
	                    WHERE kc.idProductComponent = :componentId AND kc.status = 1
                    ORDER BY pk.name ASC";

			$params = [':componentId' => $componentId];
			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Kits que contienen el componente obtenidos. Componente ID: {$componentId}, Total: " . count($result), null, 'KitComponentsModel::getByComponent');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener kits por componente: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET quantity = :quantity, idStockType = :idStockType, sortOrder = :sortOrder, 
                        updatedBy = :updatedBy, isVisible = :isVisible
                    WHERE id = :id";

			$params = [
				':id' => $id,
				':quantity' => $data['quantity'] ?? 1,
				':idStockType' => $data['idStockType'],
				':sortOrder' => $data['sortOrder'] ?? 0,
				':updatedBy' => $data['user_id'],
				':isVisible' => $data['isVisible'] ?? 0
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Componente de kit actualizado exitosamente. ID: {$id}", null, 'KitComponentsModel::update');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar componente de kit: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Componente de kit eliminado exitosamente. ID: {$id}", null, 'KitComponentsModel::delete');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar componente de kit: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function deleteByKit($kitId, $userId)
	{
		try {
			$sql = "UPDATE {$this->table} 
					SET status = 0, deletedBy = :deletedBy, deletedAt = NOW()
					WHERE idProductKit = :kitId";

			$params = [
				':kitId' => $kitId,
				':deletedBy' => $userId
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Componentes de kit eliminados exitosamente. Kit ID: {$kitId}", null, 'KitComponentsModel::deleteByKit');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar componentes del kit: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAll($includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE kc.status = 1";
			$sql = "SELECT kc.*, 
                           pk.name as kit_name, 
                           pc.name as component_name,
                           st.name as stock_type_name
                    FROM {$this->table} kc
                    LEFT JOIN products pk ON kc.idProductKit = pk.id
                    LEFT JOIN products pc ON kc.idProductComponent = pc.id
                    LEFT JOIN stocktypes st ON kc.idStockType = st.id
                    {$whereClause}
                    ORDER BY pk.name ASC, kc.sortOrder ASC, pc.name ASC";

			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Componentes de kits obtenidos exitosamente. Total: " . count($result), null, 'KitComponentsModel::getAll');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener componentes de kits: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getKitCost($kitId)
	{
		try {
			$sql = "SELECT SUM(kc.quantity * pc.baseCost) as total_cost
                    FROM {$this->table} kc
                    LEFT JOIN products pc ON kc.idProductComponent = pc.id
	                    WHERE kc.idProductKit = :kitId AND kc.status = 1";

			$params = [':kitId' => $kitId];
			$result = $this->db->fetchAll($sql, $params);

			$totalCost = $result[0]['total_cost'] ?? 0;
			$this->logger->logInfo("Costo total del kit calculado. Kit ID: {$kitId}, Costo: {$totalCost}", null, 'KitComponentsModel::getKitCost');
			return $totalCost;
		} catch (PDOException $e) {
			$this->logger->logError("Error al calcular costo del kit: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function validateKitComponent($kitId, $componentId, $excludeId = null)
	{
		try {
			$whereConditions = ["idProductKit = :kitId", "idProductComponent = :componentId", "status = 1"];
			$params = [':kitId' => $kitId, ':componentId' => $componentId];

			if ($excludeId !== null) {
				$whereConditions[] = "id != :excludeId";
				$params[':excludeId'] = $excludeId;
			}

			$whereClause = implode(' AND ', $whereConditions);

			$sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}";
			$result = $this->db->fetchAll($sql, $params);

			$count = $result[0]['count'] ?? 0;
			return $count == 0; // Retorna true si no existe duplicado
		} catch (PDOException $e) {
			$this->logger->logError("Error al validar componente de kit: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function reorderComponents($kitId, $componentsOrder, $userId)
	{
		try {
			$this->db->beginTransaction();

			foreach ($componentsOrder as $order => $componentId) {
				$sql = "UPDATE {$this->table} 
                        SET sortOrder = :sortOrder, updatedBy = :updatedBy
                        WHERE id = :componentId AND idProductKit = :kitId";

				$params = [
					':sortOrder' => $order,
					':componentId' => $componentId,
					':kitId' => $kitId,
					':updatedBy' => $userId
				];

				$this->db->execute($sql, $params);
			}

			$this->db->commit();
			$this->logger->logInfo("Orden de componentes actualizado. Kit ID: {$kitId}", null, 'KitComponentsModel::reorderComponents');
			return true;
		} catch (PDOException $e) {
			$this->db->rollback();
			$this->logger->logError("Error al reordenar componentes: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function checkCircularReference($kitId, $componentId)
	{
		try {
			// Verificar si el componente que se quiere agregar es un kit
			$sql = "SELECT isKit FROM products WHERE id = :componentId";
			$result = $this->db->fetchAll($sql, [':componentId' => $componentId]);

			if (!$result || !$result[0]['isKit']) {
				return false; // No es un kit, no puede haber referencia circular
			}

			// Verificar recursivamente si hay referencia circular
			return $this->checkCircularReferenceRecursive($kitId, $componentId, [$kitId]);
		} catch (PDOException $e) {
			$this->logger->logError("Error al verificar referencia circular: " . $e->getMessage(), __FILE__, __LINE__);
			return true; // En caso de error, asumir que hay referencia circular
		}
	}

	private function checkCircularReferenceRecursive($originalKitId, $currentKitId, $visited)
	{
		try {
			if (in_array($currentKitId, $visited)) {
				return true; // Referencia circular detectada
			}

			$visited[] = $currentKitId;

			$sql = "SELECT idProductComponent 
                    FROM {$this->table} kc
                    LEFT JOIN products p ON kc.idProductComponent = p.id
	                    WHERE kc.idProductKit = :kitId AND kc.status = 1 AND p.isKit = 1";

			$result = $this->db->fetchAll($sql, [':kitId' => $currentKitId]);

			foreach ($result as $component) {
				if ($component['idProductComponent'] == $originalKitId) {
					return true; // Referencia circular directa
				}

				if ($this->checkCircularReferenceRecursive($originalKitId, $component['idProductComponent'], $visited)) {
					return true; // Referencia circular indirecta
				}
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error en verificación recursiva: " . $e->getMessage(), __FILE__, __LINE__);
			return true;
		}
	}
}
