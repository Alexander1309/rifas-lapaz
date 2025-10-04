<?php

class ProductImagesModel extends Model
{
	private $table = 'productImages';

	public $id;
	public $idProduct;
	public $imagePath;
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
			$sql = "INSERT INTO {$this->table} (idProduct, imagePath, createdBy, isVisible, status) 
					VALUES (:idProduct, :imagePath, :createdBy, :isVisible, :status)";

			$params = [
				':idProduct' => $data['idProduct'],
				':imagePath' => $data['imagePath'],
				':createdBy' => $data['user_id'],
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$imageId = $this->db->getLastInsertId();
				$this->logger->logInfo("Imagen de producto creada exitosamente. ID: {$imageId}", null, 'ProductImagesModel::create');
				return $imageId;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al crear imagen de producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getById($id)
	{
		try {
			$sql = "SELECT pi.*, p.name as product_name 
                    FROM {$this->table} pi
                    LEFT JOIN products p ON pi.idProduct = p.id
                    WHERE pi.id = :id AND pi.status = 1";
			$params = [':id' => $id];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Imagen de producto obtenida exitosamente. ID: {$id}", null, 'ProductImagesModel::getById');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener imagen de producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getByProduct($productId)
	{
		try {
			$sql = "SELECT pi.*, p.name as product_name 
                    FROM {$this->table} pi
                    LEFT JOIN products p ON pi.idProduct = p.id
                    WHERE pi.idProduct = :productId AND pi.status = 1
                    ORDER BY pi.createdAt ASC";

			$params = [':productId' => $productId];
			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Imágenes de producto obtenidas por producto. Producto ID: {$productId}, Total: " . count($result), null, 'ProductImagesModel::getByProduct');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener imágenes por producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getFirstImageByProduct($productId)
	{
		try {
			$sql = "SELECT pi.*, p.name as product_name 
                    FROM {$this->table} pi
                    LEFT JOIN products p ON pi.idProduct = p.id
                    WHERE pi.idProduct = :productId AND pi.status = 1
                    ORDER BY pi.createdAt ASC
                    LIMIT 1";

			$params = [':productId' => $productId];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Primera imagen de producto obtenida. Producto ID: {$productId}", null, 'ProductImagesModel::getFirstImageByProduct');
				return $result[0] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener primera imagen del producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
					SET imagePath = :imagePath, isVisible = :isVisible, updatedBy = :updatedBy
					WHERE id = :id";

			$params = [
				':id' => $id,
				':imagePath' => $data['imagePath'],
				':isVisible' => $data['isVisible'] ?? 0,
				':updatedBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Imagen de producto actualizada exitosamente. ID: {$id}", null, 'ProductImagesModel::update');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar imagen de producto: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Imagen de producto eliminada exitosamente. ID: {$id}", null, 'ProductImagesModel::delete');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar imagen de producto: " . $e->getMessage(), __FILE__, __LINE__);
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
				$this->logger->logInfo("Imágenes de producto eliminadas exitosamente. Producto ID: {$productId}", null, 'ProductImagesModel::deleteByProduct');
				return true;
			}

			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al eliminar imágenes del producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAll($includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE pi.status = 1";
			$sql = "SELECT pi.*, p.name as product_name 
                    FROM {$this->table} pi
                    LEFT JOIN products p ON pi.idProduct = p.id
                    {$whereClause}
                    ORDER BY p.name ASC, pi.createdAt ASC";

			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Imágenes de productos obtenidas exitosamente. Total: " . count($result), null, 'ProductImagesModel::getAll');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener imágenes de productos: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getImagePath($id)
	{
		try {
			$sql = "SELECT imagePath FROM {$this->table} WHERE id = :id AND status = 1";
			$params = [':id' => $id];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				return $result[0]['imagePath'] ?? null;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener ruta de imagen: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getProductsWithImages()
	{
		try {
			$sql = "SELECT DISTINCT p.id, p.name, 
                           (SELECT pi.imagePath FROM {$this->table} pi 
                            WHERE pi.idProduct = p.id AND pi.status = 1 
                            ORDER BY pi.createdAt ASC LIMIT 1) as first_image
                    FROM products p
                    WHERE p.status = 1 
                    AND EXISTS (SELECT 1 FROM {$this->table} pi 
                                WHERE pi.idProduct = p.id AND pi.status = 1)
                    ORDER BY p.name ASC";

			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Productos con imágenes obtenidos exitosamente. Total: " . count($result), null, 'ProductImagesModel::getProductsWithImages');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener productos con imágenes: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}
}
