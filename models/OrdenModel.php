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

			// Se eliminó el envío de correos tras aprobación
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
			$ok = ($rc === 1);

			// Se eliminó el envío de correos tras cancelación

			return $ok;
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

	/**
	 * Server-side processing para DataTables: órdenes pendientes
	 * Retorna array [total, filtrado, rows]
	 */
	public function sspPendientes(int $start, int $length, string $search, string $orderCol, string $orderDir): array
	{
		$pdo = $this->db->getConnection();
		// Conteo total
		$total = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$this->table} WHERE estado = 'pendiente'");

		$where = " WHERE o.estado = 'pendiente' ";
		$params = [];
		if ($search !== '') {
			$where .= " AND (o.codigo_orden LIKE :q1 OR c.nombre_completo LIKE :q2 OR c.telefono LIKE :q3 OR c.correo LIKE :q4)";
			$params[':q1'] = '%' . $search . '%';
			$params[':q2'] = '%' . $search . '%';
			$params[':q3'] = '%' . $search . '%';
			$params[':q4'] = '%' . $search . '%';
		}

		// Mapeo de columnas ordenables seguras
		$orderMap = [
			'id' => 'o.id',
			'codigo_orden' => 'o.codigo_orden',
			'cliente' => 'c.nombre_completo',
			'cantidad_boletos' => 'o.cantidad_boletos',
			'total' => 'o.total',
			'comprobante' => 'o.created_at',
			'acciones' => 'o.id'
		];
		$orderBy = $orderMap[$orderCol] ?? 'o.id';
		$orderDir = in_array(strtolower($orderDir), ['asc', 'desc'], true) ? $orderDir : 'desc';

		// Conteo filtrado
		$sqlCount = "SELECT COUNT(*)
					 FROM {$this->table} o
					 LEFT JOIN clientes c ON c.id = o.usuario_id
					 $where";
		$stmtCount = $pdo->prepare($sqlCount);
		foreach ($params as $k => $v) {
			$stmtCount->bindValue($k, $v, PDO::PARAM_STR);
		}
		$stmtCount->execute();
		$filtrado = (int)$stmtCount->fetchColumn();

		// Datos paginados
		$sql = "SELECT o.id, o.codigo_orden, o.cantidad_boletos, o.total, o.created_at,
						c.nombre_completo, c.telefono, c.correo
				FROM {$this->table} o
				LEFT JOIN clientes c ON c.id = o.usuario_id
				$where
				ORDER BY $orderBy $orderDir
				LIMIT :start, :length";
		$stmt = $pdo->prepare($sql);
		foreach ($params as $k => $v) {
			$stmt->bindValue($k, $v, PDO::PARAM_STR);
		}
		$stmt->bindValue(':start', $start, PDO::PARAM_INT);
		$stmt->bindValue(':length', $length, PDO::PARAM_INT);
		$stmt->execute();
		$rows = $stmt->fetchAll();

		return [
			'total' => $total,
			'filtrado' => $filtrado,
			'rows' => $rows,
		];
	}
}
