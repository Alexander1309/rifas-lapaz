<?php

require_once 'libs/model.php';

class SalesModel extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function create($data, $userId)
	{
		try {
			if (empty($data['idUser']) || empty($data['idWarehouse'])) {
				return [
					'success' => false,
					'message' => 'Los campos usuario y almacén son obligatorios'
				];
			}

			$sql = "INSERT INTO sales (date, idCustomer, idUser, idWarehouse, createdBy, status) 
					VALUES (CURRENT_TIMESTAMP, :idCustomer, :idUser, :idWarehouse, :createdBy, 1)";

			$params = [
				':idCustomer' => $data['idCustomer'] ?? null,
				':idUser' => $data['idUser'],
				':idWarehouse' => $data['idWarehouse'],
				':createdBy' => $userId
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$saleId = $this->db->getLastInsertId();

				$this->logger->logInfo("Venta creada exitosamente. ID: {$saleId}", null, 'SalesModel::create');

				return [
					'success' => true,
					'message' => 'Venta creada exitosamente',
					'data' => ['id' => $saleId]
				];
			} else {
				throw new Exception("Error al ejecutar la consulta");
			}
		} catch (Exception $e) {
			$this->logger->logError("Error al crear venta: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getById($id)
	{
		try {
			$sql = "SELECT s.*, 
                           c.name as customerName, c.email as customerEmail,
                           u.username as userName,
                           w.name as warehouseName
                    FROM sales s 
                    LEFT JOIN customers c ON s.idCustomer = c.id 
                    LEFT JOIN users u ON s.idUser = u.id 
                    LEFT JOIN warehouses w ON s.idwarehouse = w.id 
                    WHERE s.id = :id AND s.deletedAt IS NULL";

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
					'message' => 'Venta no encontrada'
				];
			}
		} catch (Exception $e) {
			$this->logger->logError("Error al obtener venta por ID: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getAll($limit = 100, $offset = 0, $filters = [])
	{
		try {
			$whereConditions = ["s.deletedAt IS NULL", "s.status = 1"];
			$params = [':limit' => $limit, ':offset' => $offset];

			if (!empty($filters['date_from'])) {
				$whereConditions[] = "DATE(s.date) >= :date_from";
				$params[':date_from'] = $filters['date_from'];
			}

			if (!empty($filters['date_to'])) {
				$whereConditions[] = "DATE(s.date) <= :date_to";
				$params[':date_to'] = $filters['date_to'];
			}

			if (!empty($filters['customer_id'])) {
				$whereConditions[] = "s.idCustomer = :customer_id";
				$params[':customer_id'] = $filters['customer_id'];
			}

			if (!empty($filters['user_id'])) {
				$whereConditions[] = "s.idUser = :user_id";
				$params[':user_id'] = $filters['user_id'];
			}

			if (!empty($filters['warehouse_id'])) {
				$whereConditions[] = "s.idWarehouse = :warehouse_id";
				$params[':warehouse_id'] = $filters['warehouse_id'];
			}

			$sql = "SELECT s.*, 
                           c.name as customerName, c.email as customerEmail,
                           u.username as userName,
                           w.name as warehouseName,
                           (SELECT SUM(sd.price - COALESCE(sd.discount, 0)) 
                            FROM saleDetails sd 
                            WHERE sd.idSale = s.id AND sd.deletedAt IS NULL) as total
                    FROM sales s 
                    LEFT JOIN customers c ON s.idCustomer = c.id 
                    LEFT JOIN users u ON s.idUser = u.id 
					LEFT JOIN warehouses w ON s.idWarehouse = w.id 
                    WHERE " . implode(" AND ", $whereConditions) . "
                    ORDER BY s.date DESC 
                    LIMIT :limit OFFSET :offset";

			$result = $this->db->fetchAll($sql, $params);

			return [
				'success' => true,
				'data' => $result
			];
		} catch (Exception $e) {
			$this->logger->logError("Error al obtener todas las ventas: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getByDateRange($dateFrom, $dateTo, $limit = 100)
	{
		try {
			$sql = "SELECT s.*, 
                           c.name as customerName, c.email as customerEmail,
                           u.username as userName,
                           w.name as warehouseName,
                           (SELECT SUM(sd.price - COALESCE(sd.discount, 0)) 
                            FROM saleDetails sd 
                            WHERE sd.idSale = s.id AND sd.deletedAt IS NULL) as total
                    FROM sales s 
                    LEFT JOIN customers c ON s.idCustomer = c.id 
                    LEFT JOIN users u ON s.idUser = u.id 
					LEFT JOIN warehouses w ON s.idWarehouse = w.id 
                    WHERE s.deletedAt IS NULL 
                    AND s.status = 1 
                    AND DATE(s.date) BETWEEN :dateFrom AND :dateTo
                    ORDER BY s.date DESC 
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
			$this->logger->logError("Error al obtener ventas por rango de fechas: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getByCustomer($customerId, $limit = 50)
	{
		try {
			$sql = "SELECT s.*, 
                           c.name as customerName, c.email as customerEmail,
                           u.username as userName,
                           w.name as warehouseName,
                           (SELECT SUM(sd.price - COALESCE(sd.discount, 0)) 
                            FROM saleDetails sd 
                            WHERE sd.idSale = s.id AND sd.deletedAt IS NULL) as total
                    FROM sales s 
                    LEFT JOIN customers c ON s.idCustomer = c.id 
                    LEFT JOIN users u ON s.idUser = u.id 
					LEFT JOIN warehouses w ON s.idWarehouse = w.id 
                    WHERE s.idCustomer = :customerId 
                    AND s.deletedAt IS NULL 
                    AND s.status = 1 
                    ORDER BY s.date DESC 
                    LIMIT :limit";

			$params = [
				':customerId' => $customerId,
				':limit' => $limit
			];

			$result = $this->db->fetchAll($sql, $params);

			return [
				'success' => true,
				'data' => $result
			];
		} catch (Exception $e) {
			$this->logger->logError("Error al obtener ventas por cliente: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function update($id, $data, $userId)
	{
		try {
			$existing = $this->getById($id);
			if (!$existing['success']) {
				return $existing;
			}

			$fields = [];
			$params = [':id' => $id, ':updatedBy' => $userId];

			if (isset($data['idCustomer'])) {
				$fields[] = "idCustomer = :idCustomer";
				$params[':idCustomer'] = $data['idCustomer'];
			}

			if (isset($data['idUser'])) {
				$fields[] = "idUser = :idUser";
				$params[':idUser'] = $data['idUser'];
			}

			if (isset($data['idWarehouse'])) {
				$fields[] = "idWarehouse = :idWarehouse";
				$params[':idWarehouse'] = $data['idWarehouse'];
			}

			if (empty($fields)) {
				return [
					'success' => false,
					'message' => 'No hay campos para actualizar'
				];
			}

			$fields[] = "updatedBy = :updatedBy";
			$fields[] = "updatedAt = CURRENT_TIMESTAMP";

			$sql = "UPDATE sales SET " . implode(", ", $fields) . " WHERE id = :id AND deletedAt IS NULL";

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Venta actualizada exitosamente. ID: {$id}", null, 'SalesModel::update');

				return [
					'success' => true,
					'message' => 'Venta actualizada exitosamente'
				];
			} else {
				throw new Exception("Error al ejecutar la consulta");
			}
		} catch (Exception $e) {
			$this->logger->logError("Error al actualizar venta: " . $e->getMessage(), __FILE__, __LINE__);

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

			$this->db->beginTransaction();

			$sqlDetails = "UPDATE saleDetails SET 
                          deletedBy = :deletedBy, 
                          deletedAt = CURRENT_TIMESTAMP 
                          WHERE idSale = :saleId";

			$paramsDetails = [
				':deletedBy' => $userId,
				':saleId' => $id
			];

			$this->db->execute($sqlDetails, $paramsDetails);

			$sql = "UPDATE sales SET 
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
				$this->db->commit();

				$this->logger->logInfo("Venta eliminada exitosamente. ID: {$id}", null, 'SalesModel::delete');

				return [
					'success' => true,
					'message' => 'Venta eliminada exitosamente'
				];
			} else {
				$this->db->rollback();
				throw new Exception("Error al ejecutar la consulta");
			}
		} catch (Exception $e) {
			$this->db->rollback();

			$this->logger->logError("Error al eliminar venta: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getStatistics($period = 'month')
	{
		try {
			$dateCondition = "";
			switch ($period) {
				case 'today':
					$dateCondition = "DATE(s.date) = CURDATE()";
					break;
				case 'week':
					$dateCondition = "YEARWEEK(s.date) = YEARWEEK(CURDATE())";
					break;
				case 'month':
					$dateCondition = "YEAR(s.date) = YEAR(CURDATE()) AND MONTH(s.date) = MONTH(CURDATE())";
					break;
				case 'year':
					$dateCondition = "YEAR(s.date) = YEAR(CURDATE())";
					break;
				default:
					$dateCondition = "1=1";
			}

			$sql = "SELECT 
                        COUNT(*) as totalSales,
                        COALESCE(SUM(
                            (SELECT SUM(sd.price - COALESCE(sd.discount, 0)) 
                             FROM saleDetails sd 
                             WHERE sd.idSale = s.id AND sd.deletedAt IS NULL)
                        ), 0) as totalRevenue,
                        AVG(
                            (SELECT SUM(sd.price - COALESCE(sd.discount, 0)) 
                             FROM saleDetails sd 
                             WHERE sd.idSale = s.id AND sd.deletedAt IS NULL)
                        ) as averageSale
                    FROM sales s 
                    WHERE s.deletedAt IS NULL 
                    AND s.status = 1 
                    AND {$dateCondition}";

			$result = $this->db->fetchOne($sql);

			return [
				'success' => true,
				'data' => [
					'period' => $period,
					'totalSales' => (int)$result['totalSales'],
					'totalRevenue' => (float)$result['totalRevenue'],
					'averageSale' => (float)$result['averageSale']
				]
			];
		} catch (Exception $e) {
			$this->logger->logError("Error al obtener estadísticas de ventas: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	public function getTotalCount($filters = [])
	{
		try {
			$whereConditions = ["deletedAt IS NULL", "status = 1"];
			$params = [];

			if (!empty($filters['date_from'])) {
				$whereConditions[] = "DATE(date) >= :date_from";
				$params[':date_from'] = $filters['date_from'];
			}

			if (!empty($filters['date_to'])) {
				$whereConditions[] = "DATE(date) <= :date_to";
				$params[':date_to'] = $filters['date_to'];
			}

			if (!empty($filters['customer_id'])) {
				$whereConditions[] = "idCustomer = :customer_id";
				$params[':customer_id'] = $filters['customer_id'];
			}

			$sql = "SELECT COUNT(*) as total FROM sales WHERE " . implode(" AND ", $whereConditions);

			$result = $this->db->fetchOne($sql, $params);

			return [
				'success' => true,
				'data' => ['total' => (int)$result['total']]
			];
		} catch (Exception $e) {
			$this->logger->logError("Error al contar ventas: " . $e->getMessage(), __FILE__, __LINE__);

			return [
				'success' => false,
				'message' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}
}
