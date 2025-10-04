<?php

class ProductPricesModel extends Model
{
	private $table = 'productPrices';

	public $id;
	public $idProduct;
	public $idPriceList;
	public $price;
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
			$sql = "INSERT INTO {$this->table} (idProduct, idPriceList, price, createdBy, isVisible, status) 
                    VALUES (:idProduct, :idPriceList, :price, :createdBy, :isVisible, :status)";

			$params = [
				':idProduct' => $data['idProduct'],
				':idPriceList' => $data['idPriceList'],
				':price' => $data['price'],
				':createdBy' => $data['user_id'],
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$priceId = $this->db->getLastInsertId();
				$this->logger->logInfo("Precio de producto creado exitosamente. ID: {$priceId}", null, 'ProductPricesModel::create');
				return $priceId;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear precio de producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getById($id)
	{
		try {
			$sql = "SELECT pp.*, p.name as product_name, pl.name as pricelist_name 
                    FROM {$this->table} pp
                    LEFT JOIN products p ON pp.idProduct = p.id
                    LEFT JOIN priceList pl ON pp.idPriceList = pl.id
                    WHERE pp.id = :id AND pp.status = 1";
			$params = [':id' => $id];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Precio de producto obtenido exitosamente. ID: {$id}", null, 'ProductPricesModel::getById');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener precio de producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByProduct($productId, $priceListId = null)
	{
		try {
			$whereConditions = ["pp.idProduct = :productId", "pp.status = 1"];
			$params = [':productId' => $productId];

			if ($priceListId !== null) {
				$whereConditions[] = "pp.idPriceList = :priceListId";
				$params[':priceListId'] = $priceListId;
			}

			$whereClause = implode(' AND ', $whereConditions);

			$sql = "SELECT pp.*, p.name as product_name, pl.name as pricelist_name, pl.code as pricelist_code
                    FROM {$this->table} pp
                    LEFT JOIN products p ON pp.idProduct = p.id
                    LEFT JOIN priceList pl ON pp.idPriceList = pl.id
                    WHERE {$whereClause}
                    ORDER BY pl.isDefault DESC, pl.name ASC";

			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Precios de producto obtenidos por producto. Producto ID: {$productId}, Total: " . count($result), null, 'ProductPricesModel::getByProduct');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener precios por producto: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function getByPriceList($priceListId)
	{
		try {
			$sql = "SELECT pp.*, p.name as product_name, pl.name as pricelist_name 
                    FROM {$this->table} pp
                    LEFT JOIN products p ON pp.idProduct = p.id
                    LEFT JOIN priceList pl ON pp.idPriceList = pl.id
                    WHERE pp.idPriceList = :priceListId AND pp.status = 1
                    ORDER BY p.name ASC";

			$params = [':priceListId' => $priceListId];
			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Precios obtenidos por lista de precios. Lista ID: {$priceListId}, Total: " . count($result), null, 'ProductPricesModel::getByPriceList');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener precios por lista: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getPrice($productId, $priceListId = null)
	{
		try {
			if ($priceListId === null) {
				// Obtener precio de la lista por defecto
				$sql = "SELECT pp.price 
                        FROM {$this->table} pp
                        LEFT JOIN priceList pl ON pp.idPriceList = pl.id
                        WHERE pp.idProduct = :productId AND pp.status = 1 AND pl.isDefault = 1
                        LIMIT 1";
				$params = [':productId' => $productId];
			} else {
				$sql = "SELECT pp.price 
                        FROM {$this->table} pp
                        WHERE pp.idProduct = :productId AND pp.idPriceList = :priceListId AND pp.status = 1
                        LIMIT 1";
				$params = [':productId' => $productId, ':priceListId' => $priceListId];
			}

			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				return $result[0]['price'] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener precio específico: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET price = :price, isVisible = :isVisible, updatedBy = :updatedBy
                    WHERE id = :id";

			$params = [
				':id' => $id,
				':price' => $data['price'],
				':isVisible' => $data['isVisible'] ?? 0,
				':updatedBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Precio de producto actualizado exitosamente. ID: {$id}", null, 'ProductPricesModel::update');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar precio de producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function updatePrice($productId, $priceListId, $price, $userId)
	{
		try {
			// Verificar si ya existe el precio
			$existing = $this->getExistingPrice($productId, $priceListId);

			if ($existing) {
				// Actualizar precio existente
				$sql = "UPDATE {$this->table} 
                        SET price = :price, updatedBy = :updatedBy
                        WHERE idProduct = :productId AND idPriceList = :priceListId";

				$params = [
					':price' => $price,
					':productId' => $productId,
					':priceListId' => $priceListId,
					':updatedBy' => $userId
				];

				$rowsAffected = $this->db->execute($sql, $params);
				return $rowsAffected > 0;
			} else {
				// Crear nuevo precio
				$data = [
					'idProduct' => $productId,
					'idPriceList' => $priceListId,
					'price' => $price,
					'user_id' => $userId
				];
				return $this->create($data);
			}
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar precio específico: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	private function getExistingPrice($productId, $priceListId)
	{
		try {
			$sql = "SELECT * FROM {$this->table} 
                    WHERE idProduct = :productId AND idPriceList = :priceListId AND status = 1";

			$params = [':productId' => $productId, ':priceListId' => $priceListId];
			$result = $this->db->fetchAll($sql, $params);

			return $result[0] ?? null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al verificar precio existente: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Precio de producto eliminado exitosamente. ID: {$id}", null, 'ProductPricesModel::delete');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar precio de producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function deleteByProduct($productId, $userId)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET status = 0, deletedBy = :deletedBy, deletedAt = NOW()
                    WHERE idProduct = :productId";

			$params = [
				':productId' => $productId,
				':deletedBy' => $userId
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Precios de producto eliminados exitosamente. Producto ID: {$productId}", null, 'ProductPricesModel::deleteByProduct');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar precios del producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAll($includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE pp.status = 1";
			$sql = "SELECT pp.*, p.name as product_name, pl.name as pricelist_name, pl.code as pricelist_code
                    FROM {$this->table} pp
                    LEFT JOIN products p ON pp.idProduct = p.id
                    LEFT JOIN priceList pl ON pp.idPriceList = pl.id
                    {$whereClause}
                    ORDER BY p.name ASC, pl.name ASC";

			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Precios de productos obtenidos exitosamente. Total: " . count($result), null, 'ProductPricesModel::getAll');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener precios de productos: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function bulkUpdatePrices($priceListId, $pricesData, $userId)
	{
		try {
			$this->db->beginTransaction();

			foreach ($pricesData as $priceData) {
				$success = $this->updatePrice(
					$priceData['productId'],
					$priceListId,
					$priceData['price'],
					$userId
				);

				if (!$success) {
					$this->db->rollback();
					return false;
				}
			}

			$this->db->commit();
			$this->logger->logInfo("Actualización masiva de precios completada. Lista ID: {$priceListId}", null, 'ProductPricesModel::bulkUpdatePrices');
			return true;
		} catch (PDOException $e) {
			$this->db->rollback();
			$this->logger->logError("Error en actualización masiva de precios: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
}
