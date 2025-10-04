<?php

require_once 'ProductCodesModel.php';
require_once 'ProductImagesModel.php';
require_once 'ProductPricesModel.php';


class ProductsModel extends Model
{
	private $table = 'products';

	public $id;
	public $name;
	public $description;
	public $baseCost;
	public $sortOrder; // columna existente en la tabla products
	public $idBrand;
	public $idCategory;
	public $idUnit;
	public $createdBy;
	public $createdAt;
	public $updatedBy;
	public $updatedAt;
	public $deletedBy;
	public $deletedAt;
	public $isKit;
	public $isVisible;
	public $status;

	public function __construct()
	{
		parent::__construct();
	}

	public function create($data)
	{
		try {
			$this->db->beginTransaction();
			$this->logger->logInfo("Iniciando creación de producto", null, 'ProductsModel::create');

			$sql = "INSERT INTO {$this->table} (name, description, baseCost, sortOrder, idBrand, idCategory, idUnit, createdBy, isKit, isVisible, status) 
					VALUES (:name, :description, :baseCost, :sortOrder, :idBrand, :idCategory, :idUnit, :createdBy, :isKit, :isVisible, :status)";

			$params = [
				':name' => $data['name'],
				':description' => $data['description'],
				':baseCost' => $data['baseCost'] ?? 0,
				':sortOrder' => $data['sortOrder'] ?? 0,
				':idBrand' => $data['idBrand'] ?? null,
				':idCategory' => $data['idCategory'],
				':idUnit' => $data['idUnit'],
				':createdBy' => $data['user_id'],
				':isKit' => $data['isKit'] ?? 0,
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1
			];

			$this->logger->logInfo("Ejecutando INSERT principal: " . $sql, null, 'ProductsModel::create');
			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected <= 0) {
				$this->db->rollback();
				$this->logger->logError("No se pudo insertar el producto principal", __FILE__, __LINE__);
				return false;
			}

			$productId = $this->db->getLastInsertId();
			$this->logger->logInfo("Producto principal creado con ID: {$productId}", null, 'ProductsModel::create');

			// Crear códigos de barras si se proporcionaron
			if (!empty($data['barCodes'])) {
				$productCodesModel = new ProductCodesModel();
				foreach ($data['barCodes'] as $barCode) {
					if (!empty(trim($barCode))) {
						$codeData = [
							'idProduct' => $productId,
							'barCode' => trim($barCode),
							'user_id' => $data['user_id']
						];
						$productCodesModel->create($codeData);
					}
				}
			}

			// Crear precios si se proporcionaron
			if (!empty($data['prices'])) {
				$productPricesModel = new ProductPricesModel();
				foreach ($data['prices'] as $priceData) {
					$priceInfo = [
						'idProduct' => $productId,
						'idPriceList' => $priceData['idPriceList'],
						'price' => $priceData['price'],
						'user_id' => $data['user_id']
					];
					$productPricesModel->create($priceInfo);
				}
			}

			// Subir imágenes si se proporcionaron
			if (!empty($data['images'])) {
				$productImagesModel = new ProductImagesModel();
				foreach ($data['images'] as $imagePath) {
					if (!empty($imagePath)) {
						$imageData = [
							'idProduct' => $productId,
							'imagePath' => $imagePath,
							'user_id' => $data['user_id']
						];
						$productImagesModel->create($imageData);
					}
				}
			}

			$this->db->commit();
			$this->logger->logInfo("Producto creado exitosamente. ID: {$productId}", null, 'ProductsModel::create');
			return $productId;
		} catch (PDOException $e) {
			$this->db->rollback();
			$this->logger->logError("Error al crear producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getById($id)
	{
		try {
			$sql = "SELECT p.*, 
                           b.name as brand_name, 
                           c.name as category_name, 
                           u.name as unit_name,
                           p.baseCost as purchase_price,
                           p.isVisible as hidden,
                           p.createdAt as created_date,
                           p.updatedAt as updated_date
                    FROM {$this->table} p
                    LEFT JOIN brands b ON p.idBrand = b.id
                    LEFT JOIN categories c ON p.idCategory = c.id
                    LEFT JOIN units u ON p.idUnit = u.id
                    WHERE p.id = :id AND p.status = 1";

			$params = [':id' => $id];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$product = $result[0];

				// Obtener códigos de barras y mapearlos al formato que espera la vista
				$productCodesModel = new ProductCodesModel();
				$barCodes = $productCodesModel->getByProduct($id);

				// Mapear los códigos al formato esperado por la vista
				$product['codes'] = [];
				if (!empty($barCodes)) {
					foreach ($barCodes as $barCode) {
						$product['codes'][] = [
							'code_value' => $barCode['barCode']
						];
					}
				}

				// Obtener imágenes
				$productImagesModel = new ProductImagesModel();
				$images = $productImagesModel->getByProduct($id);

				// Mapear las imágenes al formato esperado
				$product['images'] = [];
				if (!empty($images)) {
					foreach ($images as $image) {
						$product['images'][] = [
							'image_name' => basename($image['imagePath'])
						];
					}
				}

				// Obtener precios y mapearlos al formato que espera la vista
				$productPricesModel = new ProductPricesModel();
				$productPrices = $productPricesModel->getByProduct($id);

				$this->logger->logInfo("Precios obtenidos para producto ID {$id}: " . count($productPrices), null, 'ProductsModel::getById');

				// Mapear los precios al formato esperado por la vista
				$product['prices'] = [];
				if (!empty($productPrices) && is_array($productPrices)) {
					foreach ($productPrices as $price) {
						$product['prices'][] = [
							'price_value' => $price['price'] ?? 0,
							'pricelist_name' => $price['pricelist_name'] ?? 'Sin nombre'
						];
					}
					$this->logger->logInfo("Precios mapeados para vista: " . count($product['prices']), null, 'ProductsModel::getById');
				} else {
					$this->logger->logInfo("No se encontraron precios para el producto ID {$id}", null, 'ProductsModel::getById');
				}

				// Mantener también las referencias originales para compatibilidad
				$product['barCodes'] = $barCodes;

				$this->logger->logInfo("Producto obtenido exitosamente. ID: {$id}", null, 'ProductsModel::getById');
				return $product;
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function getAll($limit, $offset, $includeInactive = false)
	{
		try {
			$whereClause = $includeInactive ? "" : "WHERE p.status = 1";

			// 1. Obtener el conteo total de registros
			$sqlCount = "SELECT COUNT(p.id) FROM {$this->table} p {$whereClause}";
			// ¡Ahora esto funcionará perfectamente!
			$totalRows = $this->db->fetchColumn($sqlCount);

			// 2. Obtener los registros para la página actual
			$sql = "SELECT p.*,
						b.name as brand_name,
						c.name as category_name,
						u.name as unit_name,
						p.baseCost as purchase_price,
						(SELECT pc.barCode FROM productCodes pc WHERE pc.idProduct = p.id AND pc.status = 1 ORDER BY pc.createdAt ASC LIMIT 1) as main_code,
						(SELECT pp.price FROM productPrices pp INNER JOIN priceList pl ON pp.idPriceList = pl.id WHERE pp.idProduct = p.id AND pp.status = 1 AND pl.status = 1 ORDER BY pl.isDefault DESC, pp.createdAt ASC LIMIT 1) as main_price
					FROM {$this->table} p
					LEFT JOIN brands b ON p.idBrand = b.id
					LEFT JOIN categories c ON p.idCategory = c.id
					LEFT JOIN units u ON p.idUnit = u.id
					{$whereClause}
					ORDER BY p.sortOrder ASC, p.name ASC
					LIMIT :limit OFFSET :offset";

			// Como tu método query() ya usa prepare, puedes pasarle los parámetros de forma segura
			$stmt = $this->db->query($sql, [
				':limit' => (int)$limit,
				':offset' => (int)$offset
			]);
			$result = $stmt->fetchAll();

			return ['data' => $result, 'total' => (int)$totalRows];

		} catch (Exception $e) {
			$this->logger->logError("Error al obtener productos paginados: " . $e->getMessage(), __FILE__, __LINE__);
			return ['data' => [], 'total' => 0];
		}
	}

	public function update($id, $data)
	{
		try {
			$sql = "UPDATE {$this->table} 
					SET name = :name, description = :description, baseCost = :baseCost, sortOrder = :sortOrder,
						idBrand = :idBrand, idCategory = :idCategory, idUnit = :idUnit,
						isKit = :isKit, isVisible = :isVisible, status = :status, updatedBy = :updatedBy, updatedAt = NOW()
					WHERE id = :id";

			$params = [
				':id' => $id,
				':name' => $data['name'],
				':description' => $data['description'],
				':baseCost' => $data['baseCost'] ?? 0,
				':sortOrder' => $data['sortOrder'] ?? 0,
				':idBrand' => $data['idBrand'] ?? null,
				':idCategory' => $data['idCategory'] ?? null,
				':idUnit' => $data['idUnit'] ?? null,
				':isKit' => $data['isKit'] ?? 0,
				':isVisible' => $data['isVisible'] ?? 0,
				':status' => $data['status'] ?? 1,
				':updatedBy' => $data['user_id']
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Producto actualizado exitosamente. ID: {$id}", null, 'ProductsModel::update');
				return true;
			}

			$this->logger->logError("No se actualizó ningún registro. ID: {$id}", __FILE__, __LINE__);
			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar producto: " . $e->getMessage(), __FILE__, __LINE__);
			$sqlProduct = "UPDATE {$this->table} SET name = :name, description = :description, baseCost = :baseCost, sortOrder = :sortOrder, idBrand = :idBrand, idCategory = :idCategory, idUnit = :idUnit, isKit = :isKit, isVisible = :isVisible, status = :status, updatedBy = :updatedBy, updatedAt = NOW() WHERE id = :id";
		}
	}

	public function delete($id, $userId)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                        deletedAt = NOW(), 
                        deletedBy = :deletedBy, 
                        status = 0 
                    WHERE id = :id";
            
            $params = [
                ':id'        => $id,
                ':deletedBy' => $userId
            ];

            $rowsAffected = $this->db->execute($sql, $params);
            
            if ($rowsAffected > 0) {
                $this->logger->logInfo("Unidad eliminada (soft delete) ID: {$id}", null, 'ProductsModel::delete');
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            $errorMessage = "--- ERROR CAPTURADO EN ProductsModel::delete --- " . $e->getMessage();
            error_log($errorMessage);
            return false;
        }
    }

	public function getByCategory($categoryId)
	{
		try {
			$sql = "SELECT p.*, b.name as brand_name, c.name as category_name, u.name as unit_name
                    FROM {$this->table} p
                    LEFT JOIN brands b ON p.idBrand = b.id
                    LEFT JOIN categories c ON p.idCategory = c.id
                    LEFT JOIN units u ON p.idUnit = u.id
                    WHERE p.idCategory = :categoryId AND p.status = 1
                    ORDER BY p.name ASC";

			$params = [':categoryId' => $categoryId];
			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Productos por categoría obtenidos. Categoría ID: {$categoryId}, Total: " . count($result), null, 'ProductsModel::getByCategory');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener productos por categoría: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function getByBrand($brandId)
	{
		try {
			$sql = "SELECT p.*, b.name as brand_name, c.name as category_name, u.name as unit_name
                    FROM {$this->table} p
                    LEFT JOIN brands b ON p.idBrand = b.id
                    LEFT JOIN categories c ON p.idCategory = c.id
                    LEFT JOIN units u ON p.idUnit = u.id
                    WHERE p.idBrand = :brandId AND p.status = 1
                    ORDER BY p.name ASC";

			$params = [':brandId' => $brandId];
			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Productos por marca obtenidos. Marca ID: {$brandId}, Total: " . count($result), null, 'ProductsModel::getByBrand');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener productos por marca: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function getKits()
	{
		try {
			$sql = "SELECT p.*, b.name as brand_name, c.name as category_name, u.name as unit_name
                    FROM {$this->table} p
                    LEFT JOIN brands b ON p.idBrand = b.id
                    LEFT JOIN categories c ON p.idCategory = c.id
                    LEFT JOIN units u ON p.idUnit = u.id
                    WHERE p.isKit = 1 AND p.status = 1
                    ORDER BY p.name ASC";

			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Productos kit obtenidos. Total: " . count($result), null, 'ProductsModel::getKits');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener productos kit: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function getComponents()
	{
		try {
			$sql = "SELECT p.*, b.name as brand_name, c.name as category_name, u.name as unit_name
                    FROM {$this->table} p
                    LEFT JOIN brands b ON p.idBrand = b.id
                    LEFT JOIN categories c ON p.idCategory = c.id
                    LEFT JOIN units u ON p.idUnit = u.id
                    WHERE p.isKit = 0 AND p.status = 1
                    ORDER BY p.name ASC";

			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Productos componentes obtenidos. Total: " . count($result), null, 'ProductsModel::getComponents');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener productos componentes: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function getVisible()
	{
		try {
			$sql = "SELECT p.*, b.name as brand_name, c.name as category_name, u.name as unit_name
                    FROM {$this->table} p
                    LEFT JOIN brands b ON p.idBrand = b.id
                    LEFT JOIN categories c ON p.idCategory = c.id
                    LEFT JOIN units u ON p.idUnit = u.id
                    WHERE p.status = 1 AND p.isVisible = 1
                    ORDER BY p.name ASC";

			$result = $this->db->fetchAll($sql);

			$this->logger->logInfo("Productos visibles obtenidos. Total: " . count($result), null, 'ProductsModel::getVisible');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener productos visibles: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function searchProducts($searchTerm, $categoryId = null, $brandId = null)
	{
		try {
			$whereConditions = ["p.status = 1"];
			$params = [];

			if (!empty($searchTerm)) {
				$whereConditions[] = "(p.name LIKE :searchTerm OR p.description LIKE :searchTerm)";
				$params[':searchTerm'] = "%{$searchTerm}%";
			}

			if ($categoryId !== null) {
				$whereConditions[] = "p.idCategory = :categoryId";
				$params[':categoryId'] = $categoryId;
			}

			if ($brandId !== null) {
				$whereConditions[] = "p.idBrand = :brandId";
				$params[':brandId'] = $brandId;
			}

			$whereClause = implode(' AND ', $whereConditions);

			$sql = "SELECT p.*, b.name as brand_name, c.name as category_name, u.name as unit_name
                    FROM {$this->table} p
                    LEFT JOIN brands b ON p.idBrand = b.id
                    LEFT JOIN categories c ON p.idCategory = c.id
                    LEFT JOIN units u ON p.idUnit = u.id
                    WHERE {$whereClause}
                    ORDER BY p.name ASC";

			$result = $this->db->fetchAll($sql, $params);

			$this->logger->logInfo("Búsqueda de productos realizada. Término: '{$searchTerm}', Total: " . count($result), null, 'ProductsModel::searchProducts');
			return $result;
		} catch (PDOException $e) {
			$this->logger->logError("Error en búsqueda de productos: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}

	public function getProductsByBarCode($barCode)
	{
		try {
			$sql = "SELECT p.*, b.name as brand_name, c.name as category_name, u.name as unit_name
                    FROM {$this->table} p
                    LEFT JOIN brands b ON p.idBrand = b.id
                    LEFT JOIN categories c ON p.idCategory = c.id
                    LEFT JOIN units u ON p.idUnit = u.id
                    INNER JOIN productCodes pc ON p.id = pc.idProduct
                    WHERE pc.barCode = :barCode AND p.status = 1 AND pc.status = 1";

			$params = [':barCode' => $barCode];
			$result = $this->db->fetchAll($sql, $params);

			if ($result) {
				$this->logger->logInfo("Producto obtenido por código de barras: {$barCode}", null, 'ProductsModel::getProductsByBarCode');
				return $result[0];
			}

			return null;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener producto por código de barras: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function validateProductName($name, $excludeId = null)
	{
		try {
			$whereConditions = ["name = :name", "status = 1"];
			$params = [':name' => $name];

			if ($excludeId !== null) {
				$whereConditions[] = "id != :excludeId";
				$params[':excludeId'] = $excludeId;
			}

			$whereClause = implode(' AND ', $whereConditions);

			$sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}";
			$result = $this->db->fetchAll($sql, $params);

			$count = $result[0]['count'] ?? 0;
			return $count == 0; // Retorna true si el nombre es único
		} catch (PDOException $e) {
			$this->logger->logError("Error al validar nombre de producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function updateFull($id, $data)
	{
		try {
			// Iniciar transacción
			$this->db->beginTransaction();

			// Log de datos recibidos para debug
			$this->logger->logInfo("Iniciando updateFull para ID: {$id} con datos: " . json_encode($data), null, 'ProductsModel::updateFull');

			// Datos básicos del producto
			$updateData = [
				'name' => $data['name'],
				'description' => $data['description'],
				'baseCost' => $data['baseCost'],
				'sortOrder' => $data['sortOrder'] ?? 0,
				'idBrand' => $data['idBrand'],
				'idCategory' => $data['idCategory'],
				'idUnit' => $data['idUnit'],
				'isKit' => $data['isKit'] ?? 0,
				'isVisible' => $data['isVisible'] ?? 0,
				'status' => $data['status'] ?? 1,
				'user_id' => $data['user_id']
			];

			// Actualizar producto principal
			$this->logger->logInfo("Actualizando datos básicos del producto ID: {$id}", null, 'ProductsModel::updateFull');
			$result = $this->update($id, $updateData);
			if (!$result) {
				$this->logger->logError("Fallo al actualizar datos básicos del producto ID: {$id}", __FILE__, __LINE__);
				$this->db->rollback();
				return false;
			}

			// Manejar códigos de barras
			if (isset($data['barCodes']) && is_array($data['barCodes'])) {
				try {
					$this->logger->logInfo("Procesando " . count($data['barCodes']) . " códigos para producto ID: {$id}", null, 'ProductsModel::updateFull');

					// Eliminar códigos existentes
					$this->db->execute(
						"DELETE FROM productCodes WHERE idProduct = :id",
						[':id' => $id]
					);

					// Insertar nuevos códigos
					foreach ($data['barCodes'] as $index => $code) {
						if (!empty(trim($code))) {
							$this->db->execute(
								"INSERT INTO productCodes (idProduct, barCode, createdBy, status) VALUES (:idProduct, :barCode, :createdBy, 1)",
								[':idProduct' => $id, ':barCode' => trim($code), ':createdBy' => $data['user_id']]
							);
							$this->logger->logInfo("Código insertado: " . trim($code) . " para producto ID: {$id}", null, 'ProductsModel::updateFull');
						}
					}
				} catch (Exception $e) {
					$this->logger->logError("Error en códigos de barras: " . $e->getMessage(), __FILE__, __LINE__);
					throw $e;
				}
			}

			// Manejar precios
			if (isset($data['prices']) && is_array($data['prices'])) {
				try {
					$this->logger->logInfo("Procesando " . count($data['prices']) . " precios para producto ID: {$id}", null, 'ProductsModel::updateFull');

					// Eliminar precios existentes
					$this->db->execute(
						"DELETE FROM productPrices WHERE idProduct = :id",
						[':id' => $id]
					);

					// Insertar nuevos precios
					foreach ($data['prices'] as $index => $priceData) {
						if (isset($priceData['price']) && $priceData['price'] > 0) {
							$this->db->execute(
								"INSERT INTO productPrices (idProduct, idPriceList, price, createdBy, status) VALUES (:idProduct, :idPriceList, :price, :createdBy, 1)",
								[
									':idProduct' => $id,
									':idPriceList' => $priceData['idPriceList'] ?? 1,
									':price' => $priceData['price'],
									':createdBy' => $data['user_id']
								]
							);
							$this->logger->logInfo("Precio insertado: " . $priceData['price'] . " para producto ID: {$id}", null, 'ProductsModel::updateFull');
						}
					}
				} catch (Exception $e) {
					$this->logger->logError("Error en precios: " . $e->getMessage(), __FILE__, __LINE__);
					throw $e;
				}
			}

			// Manejar imágenes con lógica de reemplazo
			if (isset($data['images']) && is_array($data['images']) && !empty($data['images'])) {
				try {
					// Obtener imágenes actuales
					$currentImages = $this->db->fetchAll(
						"SELECT imagePath FROM productImages WHERE idProduct = :id",
						[':id' => $id]
					);

					$currentImagePaths = array_column($currentImages, 'imagePath');
					$imagesToKeep = $data['imagesToKeep'] ?? [];

					$this->logger->logInfo("Imágenes actuales: " . json_encode($currentImagePaths), null, 'ProductsModel::updateFull');
					$this->logger->logInfo("Imágenes a conservar: " . json_encode($imagesToKeep), null, 'ProductsModel::updateFull');

					// Eliminar archivos físicos de las imágenes que no se van a conservar
					foreach ($currentImagePaths as $currentImagePath) {
						if (!in_array($currentImagePath, $imagesToKeep)) {
							$filePath = 'assets/images/products/' . $currentImagePath;
							if (file_exists($filePath)) {
								unlink($filePath);
								$this->logger->logInfo("Archivo eliminado: " . $filePath, null, 'ProductsModel::updateFull');
							}
						}
					}

					// Eliminar todos los registros de imágenes de la base de datos
					$this->db->execute(
						"DELETE FROM productImages WHERE idProduct = :id",
						[':id' => $id]
					);

					// Insertar todas las imágenes (las conservadas + las nuevas)
					$imageCount = 0;
					foreach ($data['images'] as $imageName) {
						if ($imageCount >= 4) break; // Máximo 4 imágenes por producto

						if (!empty($imageName)) {
							$this->db->execute(
								"INSERT INTO productImages (idProduct, imagePath) VALUES (:idProduct, :imagePath)",
								[':idProduct' => $id, ':imagePath' => $imageName]
							);
							$imageCount++;
							$this->logger->logInfo("Imagen insertada: " . $imageName . " para producto ID: {$id}", null, 'ProductsModel::updateFull');
						}
					}
				} catch (Exception $e) {
					$this->logger->logError("Error en imágenes: " . $e->getMessage(), __FILE__, __LINE__);
					throw $e;
				}
			}

			// Confirmar transacción
			$this->db->commit();
			$this->logger->logInfo("Producto actualizado completamente. ID: {$id}", null, 'ProductsModel::updateFull');

			return true;
		} catch (Exception $e) {
			$this->db->rollback();
			$this->logger->logError("Error al actualizar producto completamente: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function updateHidden($id, $hidden, $userId)
	{
		try {
			$sql = "UPDATE {$this->table} 
                    SET isVisible = :isVisible, updatedBy = :updatedBy, updatedAt = NOW()
                    WHERE id = :id AND status = 1";

			$params = [
				':id' => $id,
				':isVisible' => $hidden ? 1 : 0,
				':updatedBy' => $userId
			];

			$rowsAffected = $this->db->execute($sql, $params);

			if ($rowsAffected > 0) {
				$this->logger->logInfo("Estado de visibilidad del producto actualizado. ID: {$id}, Visible: " . ($hidden ? 'Sí' : 'No'), null, 'ProductsModel::updateHidden');
				return true;
			}

			$this->logger->logError("No se actualizó ningún registro al cambiar visibilidad. ID: {$id}", __FILE__, __LINE__);
			return false;
		} catch (PDOException $e) {
			$this->logger->logError("Error al actualizar visibilidad del producto: " . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	/**
	 * Obtiene todos los productos con su primera imagen y el precio correspondiente
	 * a una lista de precios específica (si se proporciona). Si no existe precio en esa
	 * lista o no se pasa lista, toma el precio de la lista marcada como default.
	 */
	public function getAllWithPriceForPriceList($priceListId = null, array $options = [])
	{
		try {
			$imageSub = "(SELECT pi.imagePath FROM productImages pi WHERE pi.idProduct = p.id AND pi.status = 1 ORDER BY pi.createdAt ASC LIMIT 1) AS image";
			if ($priceListId) {
				$priceSub = "(SELECT pp.price FROM productPrices pp WHERE pp.idProduct = p.id AND pp.status = 1 AND pp.idPriceList = :plist LIMIT 1) AS price";
			} else {
				$priceSub = "(SELECT pp.price FROM productPrices pp INNER JOIN priceList pl ON pl.id = pp.idPriceList AND pl.status = 1 WHERE pp.idProduct = p.id AND pp.status = 1 ORDER BY pl.isDefault DESC, pp.createdAt ASC LIMIT 1) AS price";
			}

			$conditions = ['p.status = 1'];
			$params = [];
			if ($priceListId) {
				$params[':plist'] = $priceListId;
			}

			// Filtro categoría
			if (!empty($options['category']) && ctype_digit(strval($options['category']))) {
				$conditions[] = 'p.idCategory = :cat';
				$params[':cat'] = (int)$options['category'];
			}

			// Búsqueda (nombre o código de barras)
			if (!empty($options['search'])) {
				$search = trim($options['search']);
				if (strlen($search) > 100) {
					$search = substr($search, 0, 100);
				}
				$params[':search'] = '%' . $search . '%';
				$conditions[] = '(p.name LIKE :search OR EXISTS (SELECT 1 FROM productCodes pc WHERE pc.idProduct = p.id AND pc.status = 1 AND pc.barCode LIKE :search))';
			}

			$whereSql = 'WHERE ' . implode(' AND ', $conditions);

			$orderSql = 'p.name ASC';
			if (!empty($options['sort'])) {
				switch ($options['sort']) {
					case 'price_asc':
						$orderSql = 'price IS NULL ASC, price ASC, p.name ASC';
						break;
					case 'price_desc':
						$orderSql = 'price IS NULL ASC, price DESC, p.name ASC';
						break;
				}
			}

			$sql = "SELECT p.id, p.name, p.description, p.idCategory, p.idBrand, p.idUnit, {$imageSub}, {$priceSub}
				FROM products p
				{$whereSql}
				ORDER BY {$orderSql}";

			$rows = $this->db->fetchAll($sql, $params);

			foreach ($rows as &$r) {
				$r['price'] = isset($r['price']) ? floatval($r['price']) : null;
				$r['image_url'] = $r['image'] ? '/assets/images/products/' . $r['image'] : '/assets/images/no-image.svg';
			}

			return $rows;
		} catch (PDOException $e) {
			$this->logger->logError("Error al obtener productos para ventas: " . $e->getMessage(), __FILE__, __LINE__);
			return [];
		}
	}
}
