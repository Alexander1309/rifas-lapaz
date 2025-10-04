<?php

class InventoryModel extends Model
{
	private $table = 'inventory';

	public $id;
	public $idProduct;
	public $idWarehouse;
	public $idStockType;
	public $quantityOnHand;
	public $quantityReserved;
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
			$sql = "INSERT INTO {$this->table} (idProduct, idWarehouse, idStockType, quantityOnHand, quantityReserved, createdBy, isVisible, status) 
                    VALUES (:idProduct, :idWarehouse, :idStockType, :quantityOnHand, :quantityReserved, :createdBy, :isVisible, :status)";

			$params = [
				':idProduct' => $data['idProduct'],
				':idWarehouse' => $data['idWarehouse'],
				':idStockType' => $data['idStockType'],
				':quantityOnHand' => $data['quantityOnHand'] ?? 0,
				':quantityReserved' => $data['quantityReserved'] ?? 0,
				':createdBy' => $data['user_id'],
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$inventoryId = $this->db->getLastInsertId();
				$this->logger->logInfo("Inventario creado exitosamente. ID: {$inventoryId}", null, 'InventoryModel::create');
				return $inventoryId;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear inventario: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getById($id)
	{
		try {
			$sql = "SELECT i.*, p.name as product_name, w.name as warehouse_name, st.name as stock_type_name
                    FROM {$this->table} i
                    LEFT JOIN products p ON i.idProduct = p.id
                    LEFT JOIN warehouses w ON i.idWarehouse = w.id
                    LEFT JOIN stocktypes st ON i.idStockType = st.id
                    WHERE i.id = :id AND i.status = 1";
			$params = [':id' => $id];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Inventario obtenido exitosamente. ID: {$id}", null, 'InventoryModel::getById');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener inventario: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByProduct($productId, $warehouseId = null, $stockTypeId = null)
	{
		try {
			$whereConditions = ["i.idProduct = :productId", "i.status = 1"];
			$params = [':productId' => $productId];

			if ($warehouseId !== null) {
				$whereConditions[] = "i.idWarehouse = :warehouseId";
				$params[':warehouseId'] = $warehouseId;
			}

			if ($stockTypeId !== null) {
				$whereConditions[] = "i.idStockType = :stockTypeId";
				$params[':stockTypeId'] = $stockTypeId;
			}

			$whereClause = implode(' AND ', $whereConditions);

			$sql = "SELECT i.*, p.name as product_name, w.name as warehouse_name, st.name as stock_type_name
                    FROM {$this->table} i
                    LEFT JOIN products p ON i.idProduct = p.id
                    LEFT JOIN warehouses w ON i.idWarehouse = w.id
                    LEFT JOIN stocktypes st ON i.idStockType = st.id
                    WHERE {$whereClause}
                    ORDER BY w.name, st.name";

			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Inventario por producto obtenido exitosamente. Producto ID: {$productId}, Total: " . count($result), null, 'InventoryModel::getByProduct');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener inventario por producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByWarehouse($warehouseId)
	{
		try {
			$sql = "SELECT i.*, p.name as product_name, w.name as warehouse_name, st.name as stock_type_name
                    FROM {$this->table} i
                    LEFT JOIN products p ON i.idProduct = p.id
                    LEFT JOIN warehouses w ON i.idWarehouse = w.id
                    LEFT JOIN stocktypes st ON i.idStockType = st.id
                    WHERE i.idWarehouse = :warehouseId AND i.status = 1
                    ORDER BY p.name, st.name";

			$params = [':warehouseId' => $warehouseId];
			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Inventario por almacén obtenido exitosamente. Almacén ID: {$warehouseId}, Total: " . count($result), null, 'InventoryModel::getByWarehouse');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener inventario por almacén: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function updateQuantities($id, $quantityOnHand, $quantityReserved, $userId)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET quantityOnHand = :quantityOnHand, quantityReserved = :quantityReserved, updatedBy = :updatedBy
                    WHERE id = :id";

			$params = [
				':id' => $id,
				':quantityOnHand' => $quantityOnHand,
				':quantityReserved' => $quantityReserved,
				':updatedBy' => $userId
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Cantidades de inventario actualizadas exitosamente. ID: {$id}", null, 'InventoryModel::updateQuantities');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar cantidades de inventario: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET idProduct = :idProduct, idWarehouse = :idWarehouse, idStockType = :idStockType, 
                        quantityOnHand = :quantityOnHand, quantityReserved = :quantityReserved, 
                        isVisible = :isVisible, updatedBy = :updatedBy
                    WHERE id = :id";

			$params = [
				':id' => $id,
				':idProduct' => $data['idProduct'],
				':idWarehouse' => $data['idWarehouse'],
				':idStockType' => $data['idStockType'],
				':quantityOnHand' => $data['quantityOnHand'] ?? 0,
				':quantityReserved' => $data['quantityReserved'] ?? 0,
				':isVisible' => $data['isVisible'] ?? 0,
				':updatedBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Inventario actualizado exitosamente. ID: {$id}", null, 'InventoryModel::update');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar inventario: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function adjustStock($productId, $warehouseId, $stockTypeId, $adjustment, $userId)
	{
		try {
			// Primero verificar si existe el registro de inventario
			$existingRecord = $this->getInventoryRecord($productId, $warehouseId, $stockTypeId);

			if ($existingRecord) {
				// Actualizar cantidad existente
				$newQuantity = max(0, $existingRecord['quantityOnHand'] + $adjustment);
				return $this->updateQuantities($existingRecord['id'], $newQuantity, $existingRecord['quantityReserved'], $userId);
			} else {
				// Crear nuevo registro si no existe
				$data = [
					'idProduct' => $productId,
					'idWarehouse' => $warehouseId,
					'idStockType' => $stockTypeId,
					'quantityOnHand' => max(0, $adjustment),
					'quantityReserved' => 0,
					'user_id' => $userId
				];
				return $this->create($data);
			}
		} catch (PDOException $e) {
			$this->logger->logError("Error al ajustar stock: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	private function getInventoryRecord($productId, $warehouseId, $stockTypeId)
	{
		try {
			$sql = "SELECT * FROM {$this->table} 
                    WHERE idProduct = :productId AND idWarehouse = :warehouseId AND idStockType = :stockTypeId AND status = 1";

			$params = [
				':productId' => $productId,
				':warehouseId' => $warehouseId,
				':stockTypeId' => $stockTypeId
			];

			$result = $this->db->fetchAll($sql, $params);
			return $result[0] ?? null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener registro de inventario: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAll($includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE i.status = 1";
			$sql = "SELECT i.*, p.name as product_name, w.name as warehouse_name, st.name as stock_type_name
                    FROM {$this->table} i
                    LEFT JOIN products p ON i.idProduct = p.id
                    LEFT JOIN warehouses w ON i.idWarehouse = w.id
                    LEFT JOIN stocktypes st ON i.idStockType = st.id
                    {$whereClause}
                    ORDER BY p.name, w.name, st.name";

			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Inventario completo obtenido exitosamente. Total: " . count($result), null, 'InventoryModel::getAll');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener inventario completo: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Inventario eliminado exitosamente. ID: {$id}", null, 'InventoryModel::delete');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar inventario: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
}
