<?php

class OrdenModel extends Model
{
	private string $table = 'ordenes';

	public function crear(int $usuarioId, int $cantidad, float $total, ?string $fechaExpiracion, ?string $codigo = null): int
	{
		$codigo = $codigo ?: strtoupper(bin2hex(random_bytes(5)));
		$sql = "INSERT INTO {$this->table} (usuario_id, codigo_orden, cantidad_boletos, total, estado, fecha_expiracion)
                VALUES (:uid, :cod, :cant, :tot, 'pendiente', :exp)";
		$params = [
			':uid' => $usuarioId,
			':cod' => $codigo,
			':cant' => $cantidad,
			':tot' => $total,
			':exp' => $fechaExpiracion
		];
		if (property_exists($this, 'logger') && $this->logger) {
			$this->logger->logInfo('Creando orden', null, 'OrdenModel::crear', $params);
		}
		$this->db->execute($sql, $params);
		$id = (int)$this->db->getLastInsertId();
		if ($id <= 0) {
			// Fallback: Obtener por codigo_orden (único) y usuario_id para mayor certeza
			$row = $this->db->fetchOne(
				"SELECT id FROM {$this->table} WHERE codigo_orden = :cod AND usuario_id = :uid ORDER BY id DESC LIMIT 1",
				[':cod' => $params[':cod'], ':uid' => $params[':uid']]
			);
			if (!$row) {
				// Intento alterno solo por código (sigue siendo único)
				$row = $this->db->fetchOne(
					"SELECT id FROM {$this->table} WHERE codigo_orden = :cod ORDER BY id DESC LIMIT 1",
					[':cod' => $params[':cod']]
				);
			}
			if ($row && isset($row['id'])) {
				$id = (int)$row['id'];
				if (property_exists($this, 'logger') && $this->logger) {
					// Bajar el nivel de log para no generar ruido si el fallback funciona
					$this->logger->logInfo('Fallback id de orden por codigo_orden', null, 'OrdenModel::crear', ['id' => $id, 'codigo' => $params[':cod']]);
				}
			} else {
				if (property_exists($this, 'logger') && $this->logger) {
					$this->logger->logError('No se pudo obtener ID de orden tras INSERT', __FILE__, __LINE__, $params);
				}
			}
		}
		return $id;
	}

	public function adjuntarComprobante(int $ordenId, ?string $ruta, ?string $nombre): void
	{
		$sql = "UPDATE {$this->table} SET comprobante_ruta = :ruta, comprobante_nombre = :nom WHERE id = :id";
		$this->db->execute($sql, [':ruta' => $ruta, ':nom' => $nombre, ':id' => $ordenId]);
	}

	public function getById(int $id): ?array
	{
		$row = $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = :id", [':id' => $id]);
		return $row ?: null;
	}

	public function aprobar(int $ordenId, int $adminId): bool
	{
		$this->db->beginTransaction();
		try {
			// Asegurar que BoletoModel esté cargado
			if (!class_exists('BoletoModel')) {
				$bmPath = (defined('MODELS_PATH') ? MODELS_PATH : __DIR__ . DIRECTORY_SEPARATOR) . 'BoletoModel.php';
				if (file_exists($bmPath)) {
					require_once $bmPath;
				}
			}
			$sql = "UPDATE {$this->table} SET estado = 'aprobada', fecha_aprobacion = NOW(), admin_id = :aid WHERE id = :id AND estado = 'pendiente'";
			$rc = $this->db->execute($sql, [':aid' => $adminId, ':id' => $ordenId]);
			if ($rc !== 1) {
				$this->db->rollback();
				return false;
			}
			// boletos pasan a vendido
			(new BoletoModel())->marcarVendidosPorOrden($ordenId);
			$this->db->commit();
			return true;
		} catch (Throwable $e) {
			$this->db->rollback();
			$this->logger->logError('Error al aprobar orden: ' . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	public function denegar(int $ordenId, ?int $adminId, ?string $notas = null): bool
	{
		$this->db->beginTransaction();
		try {
			// Asegurar que BoletoModel esté cargado
			if (!class_exists('BoletoModel')) {
				$bmPath = (defined('MODELS_PATH') ? MODELS_PATH : __DIR__ . DIRECTORY_SEPARATOR) . 'BoletoModel.php';
				if (file_exists($bmPath)) {
					require_once $bmPath;
				}
			}
			$sql = "UPDATE {$this->table} SET estado = 'cancelada', fecha_cancelacion = NOW(), admin_id = :aid, notas_admin = :nota WHERE id = :id AND estado = 'pendiente'";
			$rc = $this->db->execute($sql, [':aid' => $adminId, ':nota' => $notas, ':id' => $ordenId]);
			// liberar boletos bloqueados
			(new BoletoModel())->liberarPorOrden($ordenId);
			$this->db->commit();
			return $rc === 1;
		} catch (Throwable $e) {
			$this->db->rollback();
			$this->logger->logError('Error al denegar orden: ' . $e->getMessage(), __FILE__, __LINE__);
			return false;
		}
	}

	/**
	 * Listado de órdenes pendientes con datos del cliente
	 */
	public function listarPendientes(): array
	{
		$sql = "SELECT o.*, c.nombre_completo, c.telefono, c.correo
				FROM {$this->table} o
				LEFT JOIN clientes c ON c.id = o.usuario_id
				WHERE o.estado = 'pendiente'
				ORDER BY o.created_at DESC";
		return $this->db->fetchAll($sql);
	}

	/**
	 * Listado de órdenes aprobadas con datos del cliente
	 */
	public function listarAprobadas(): array
	{
		$sql = "SELECT o.*, c.nombre_completo, c.telefono, c.correo
				FROM {$this->table} o
				LEFT JOIN clientes c ON c.id = o.usuario_id
				WHERE o.estado = 'aprobada'
				ORDER BY o.fecha_aprobacion DESC, o.created_at DESC";
		return $this->db->fetchAll($sql);
	}

	/**
	 * Obtiene información de comprobante (ruta/nombre) de una orden
	 */
	public function getComprobante(int $ordenId): ?array
	{
		$row = $this->db->fetchOne("SELECT comprobante_ruta, comprobante_nombre FROM {$this->table} WHERE id = :id", [':id' => $ordenId]);
		return $row ?: null;
	}
}
