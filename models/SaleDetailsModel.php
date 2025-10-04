<?php

require_once 'libs/model.php';

class SaleDetailsModel extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function create($data, $userId)
	{
		try {
			if (
				empty($data['idSale']) || empty($data['idProduct']) || empty($data['idWarehouse']) ||
				empty($data['idStockType']) || !isset($data['price']) || !isset($data['quantity'])
			) {
				return [
					'success' => false,
					'message' => 'Los campos venta, producto, almacén, tipo de stock, cantidad y precio son obligatorios'
				];
			}

			if ($data['price'] <= 0) {
				return [
					'success' => false,
					'message' => 'El precio debe ser mayor a cero'
				];
			}

			if ($data['quantity'] <= 0) {
				return [
					'success' => false,
					'message' => 'La cantidad debe ser mayor a cero'
				];
			}

			$sql = "INSERT INTO saleDetails (idSale, idProduct, idWarehouse, idStockType, quantity, price, discount, createdBy, status) 
					VALUES (:idSale, :idProduct, :idWarehouse, :idStockType, :quantity, :price, :discount, :createdBy, 1)";

			$discount = isset($data['discount']) ? $data['discount'] : 0;

			$params = [
				':idSale' => $data['idSale'],
				':idProduct' => $data['idProduct'],
				':idWarehouse' => $data['idWarehouse'],
				':idStockType' => $data['idStockType'],
				':quantity' => $data['quantity'],
				':price' => $data['price'],
				':discount' => $discount,
				':createdBy' => $userId
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$saleDetailId = $this->db->getLastInsertId();

				$this->logger->logInfo("Detalle de venta creado exitosamente. ID: {$saleDetailId}", null, 'SaleDetailsModel::create');

				return [
					'success' => true,
					'message' => 'Detalle de venta creado exitosamente',
					'data' => ['id' => $saleDetailId]
				];
			} else {
				throw new Exception("Error al ejecutar la consulta");
			}
		} catch (Exception $e) {
			$this->logger->logError("Error al crear detalle de venta: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getById($id)
	{
		try {
			$sql = "SELECT sd.*, 
                           p.name as productName, p.description as productDescription,
                           s.date as saleDate,
                           w.name as warehouseName,
                           st.name as stockTypeName,
                           (sd.price - COALESCE(sd.discount, 0)) as subtotal
                    FROM saleDetails sd 
                    LEFT JOIN products p ON sd.idProduct = p.id 
                    LEFT JOIN sales s ON sd.idSale = s.id 
                    LEFT JOIN warehouses w ON sd.idWarehouse = w.id 
                    LEFT JOIN stocktypes st ON sd.idStockType = st.id 
                    WHERE sd.id = :id AND sd.deletedAt IS NULL";

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
					'message' => 'Detalle de venta no encontrado'
				];
			}
		} catch (Exception $e) {
			$this->logger->logError("Error al obtener detalle de venta por ID: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getBySaleId($saleId, $limit = 100)
	{
		try {
			$sql = "SELECT sd.*, 
                           p.name as productName, p.description as productDescription,
                           w.name as warehouseName,
                           st.name as stockTypeName,
                           (sd.price - COALESCE(sd.discount, 0)) as subtotal
                    FROM saleDetails sd 
                    LEFT JOIN products p ON sd.idProduct = p.id 
                    LEFT JOIN warehouses w ON sd.idWarehouse = w.id 
                    LEFT JOIN stocktypes st ON sd.idStockType = st.id 
                    WHERE sd.idSale = :saleId AND sd.deletedAt IS NULL AND sd.status = 1
                    ORDER BY sd.id ASC 
                    LIMIT :limit";

			$params = [
				':saleId' => $saleId,
				':limit' => $limit
			];
			$result = $this->db->fetchAll($sql, $params);

			$details = [];
			$total = 0;
			foreach ($result as $row) {
				$details[] = $row;
				$total += $row['subtotal'];
			}

			return [
				'success' => true,
				'data' => [
					'details' => $details,
					'total' => $total,
					'count' => count($details)
				]
			];
		} catch (Exception $e) {
			$this->logger->logError("Error al obtener detalles por ID de venta: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getByProductId($productId, $limit = 100, $dateFrom = null, $dateTo = null)
	{
		try {
			$whereConditions = ["sd.idProduct = :productId", "sd.deletedAt IS NULL", "sd.status = 1"];
			$params = [':productId' => $productId];

			if ($dateFrom) {
				$whereConditions[] = "DATE(s.date) >= :dateFrom";
				$params[':dateFrom'] = $dateFrom;
			}

			if ($dateTo) {
				$whereConditions[] = "DATE(s.date) <= :dateTo";
				$params[':dateTo'] = $dateTo;
			}

			$sql = "SELECT sd.*, 
                           p.name as productName, p.description as productDescription,
                           s.date as saleDate, s.id as saleId,
                           w.name as warehouseName,
                           st.name as stockTypeName,
                           c.name as customerName,
                           (sd.price - COALESCE(sd.discount, 0)) as subtotal
                    FROM saleDetails sd 
                    LEFT JOIN products p ON sd.idProduct = p.id 
                    LEFT JOIN sales s ON sd.idSale = s.id 
                    LEFT JOIN customers c ON s.idCustomer = c.id 
                    LEFT JOIN warehouses w ON sd.idWarehouse = w.id 
                    LEFT JOIN stocktypes st ON sd.idStockType = st.id 
                    WHERE " . implode(" AND ", $whereConditions) . "
                    ORDER BY s.date DESC 
                    LIMIT :limit";

			$params[':limit'] = $limit;
			$result = $this->db->fetchAll($sql, $params);

			return [
				'success' => true,
				'data' => $result
			];
		} catch (Exception $e) {
			$this->logger->logError("Error al obtener detalles por producto: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function update($id, $data, $userId)
	{
		try {
			// Validaciones básicas
			if (!isset($data['quantity']) || $data['quantity'] <= 0) {
				$this->logger->logError("Cantidad inválida o ausente para detalle de venta", __FILE__, __LINE__);
				return false;
			}

			$existing = $this->getById($id);
			if (!$existing['success']) {
				return $existing;
			}

			$fields = [];
			$params = [':id' => $id, ':updatedBy' => $userId];

			if (isset($data['price']) && $data['price'] > 0) {
				$fields[] = "price = :price";
				$params[':price'] = $data['price'];
			}

			if (isset($data['discount'])) {
				$fields[] = "discount = :discount";
				$params[':discount'] = $data['discount'];
			}

			if (isset($data['idWarehouse'])) {
				$fields[] = "idWarehouse = :idWarehouse";
				$params[':idWarehouse'] = $data['idWarehouse'];
			}

			if (isset($data['idStockType'])) {
				$fields[] = "idStockType = :idStockType";
				$params[':idStockType'] = $data['idStockType'];
			}

			if (empty($fields) && !isset($data['quantity'])) {
				return [
					'success' => false,
					'message' => 'No hay campos para actualizar'
				];
			}

			if (isset($data['quantity']) && $data['quantity'] > 0) {
				$fields[] = "quantity = :quantity";
				$params[':quantity'] = $data['quantity'];
			}

			$fields[] = "updatedBy = :updatedBy";
			$fields[] = "updatedAt = CURRENT_TIMESTAMP";

			$sql = "UPDATE saleDetails SET " . implode(", ", $fields) . " WHERE id = :id AND deletedAt IS NULL";

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Detalle de venta actualizado exitosamente. ID: {$id}", null, 'SaleDetailsModel::update');

				return [
					'success' => true,
					'message' => 'Detalle de venta actualizado exitosamente'
				];
			} else {
				throw new Exception("Error al ejecutar la consulta");
			}
		} catch (Exception $e) {
			$this->logger->logError("Error al actualizar detalle de venta: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function delete($id, $userId)
	{
		try {
			$existing = $this->getById($id);
			if (!$existing['success']) {
				return $existing;
			}

			$sql = "UPDATE saleDetails SET 
                    deletedBy = :deletedBy, 
                    deletedAt = CURRENT_TIMESTAMP, 
                    status = 0 
                    WHERE id = :id";

			$params = [
				':deletedBy' => $userId,
				':id' => $id
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Detalle de venta eliminado exitosamente. ID: {$id}", null, 'SaleDetailsModel::delete');

				return [
					'success' => true,
					'message' => 'Detalle de venta eliminado exitosamente'
				];
			} else {
				throw new Exception("Error al ejecutar la consulta");
			}
		} catch (Exception $e) {
			$this->logger->logError("Error al eliminar detalle de venta: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function deleteBySaleId($saleId, $userId)
	{
		try {
			$sql = "UPDATE saleDetails SET 
                    deletedBy = :deletedBy, 
                    deletedAt = CURRENT_TIMESTAMP, 
                    status = 0 
                    WHERE idSale = :saleId AND deletedAt IS NULL";

			$params = [
				':deletedBy' => $userId,
				':saleId' => $saleId
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Detalles de venta eliminados exitosamente. Sale ID: {$saleId}, Affected: {$rowsAffected}", null, 'SaleDetailsModel::deleteBySaleId');

				return [
					'success' => true,
					'message' => "Se eliminaron {$rowsAffected} detalles de venta exitosamente",
					'data' => ['affected_rows' => $rowsAffected]
				];
			} else {
				throw new Exception("Error al ejecutar la consulta");
			}
		} catch (Exception $e) {
			$this->logger->logError("Error al eliminar detalles de venta por sale ID: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getProductSalesSummary($dateFrom, $dateTo, $limit = 50)
	{
		try {
			$sql = "SELECT sd.idProduct,
                           p.name as productName,
                           p.description as productDescription,
                           COUNT(sd.id) as totalItems,
                           AVG(sd.price) as averagePrice,
                           SUM(sd.price - COALESCE(sd.discount, 0)) as totalRevenue,
                           COUNT(DISTINCT sd.idSale) as totalSales
                    FROM saleDetails sd 
                    INNER JOIN products p ON sd.idProduct = p.id 
                    INNER JOIN sales s ON sd.idSale = s.id 
                    WHERE sd.deletedAt IS NULL 
                    AND sd.status = 1 
                    AND s.deletedAt IS NULL 
                    AND s.status = 1 
                    AND DATE(s.date) BETWEEN :dateFrom AND :dateTo
                    GROUP BY sd.idProduct, p.name, p.description 
                    ORDER BY totalRevenue DESC 
                    LIMIT :limit";

			$params = [
				':dateFrom' => $dateFrom,
				':dateTo' => $dateTo,
				':limit' => $limit
			];

			$result = $this->db->fetchAll($sql, $params);

			return [
				'success' => true,
				'data' => $result
			];
		} catch (Exception $e) {
			$this->logger->logError("Error al obtener resumen de ventas por producto: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getTotalCountBySale($saleId)
	{
		try {
			$sql = "SELECT COUNT(*) as total FROM saleDetails WHERE idSale = :saleId AND deletedAt IS NULL AND status = 1";

			$params = [':saleId' => $saleId];
			$result = $this->db->fetchOne($sql, $params);

			return [
				'success' => true,
				'data' => ['total' => (int)$result['total']]
			];
		} catch (Exception $e) {
			$this->logger->logError("Error al contar detalles de venta: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}
}
