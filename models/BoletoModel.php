<?php

class BoletoModel extends Model
{
	private string $table = 'boletos';

	public function getVendidosNumeros(int $limit = 1000): array
	{
		$sql = "SELECT LPAD(numero, 5,  '0') AS numero FROM {$this->table} WHERE estado <> 'disponible' ORDER BY numero ASC LIMIT :lim";
		$stmt = $this->db->getConnection()->prepare($sql);
		$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
		$stmt->execute();
		return array_column($stmt->fetchAll(), 'numero');
	}
	public function bloquearTemporal(array $numeros, int $ordenId): array
	{
		if (empty($numeros)) return ['ok' => false, 'bloqueados' => [], 'conflictos' => []];

		// Validar que la orden exista para no violar FK
		$ordenRow = $this->db->fetchOne("SELECT id FROM ordenes WHERE id = :id", [':id' => $ordenId]);
		if (!$ordenRow) {
			if (property_exists($this, 'logger') && $this->logger) {
				$this->logger->logError('Orden no existe al bloquear boletos', __FILE__, __LINE__, ['orden_id' => $ordenId, 'numeros' => $numeros]);
			}
			return ['ok' => false, 'bloqueados' => [], 'conflictos' => [], 'error' => 'orden_invalida'];
		}

		// Normalizar todos los números a 5 dígitos
		$numeros = array_values(array_unique(array_map(function ($n) {
			$n = preg_replace('/\D/', '', (string)$n);
			return str_pad($n, 5, '0', STR_PAD_LEFT);
		}, $numeros)));

		sort($numeros);
		$started = false;
		if (!$this->db->inTransaction()) {
			$this->db->beginTransaction();
			$started = true;
		}
		try {
			if (property_exists($this, 'logger') && $this->logger) {
				$this->logger->logInfo('Inicio bloqueo temporal', null, 'BoletoModel::bloquearTemporal', ['orden_id' => $ordenId, 'cantidad' => count($numeros)]);
			}
			$bloqueados = [];
			$conflictos = [];
			$pdo = $this->db->getConnection();
			// Preferir match exacto (usa índice) y fallback a LPAD si no se encuentra
			$sqlSelectExact = "SELECT id, numero, estado FROM {$this->table} WHERE numero = :num FOR UPDATE";
			$sqlSelectLpad  = "SELECT id, numero, estado FROM {$this->table} WHERE LPAD(numero, 5,  '0') = :num FOR UPDATE";
			$sqlUpdate = "UPDATE {$this->table} SET estado = 'bloqueado_temporal', orden_id = :orden, fecha_bloqueo = NOW() WHERE id = :id AND estado = 'disponible'";
			$stmtSelExact = $pdo->prepare($sqlSelectExact);
			$stmtSelLpad  = $pdo->prepare($sqlSelectLpad);
			$stmtUpd = $pdo->prepare($sqlUpdate);

			foreach ($numeros as $num) {
				// Intentar exacto primero
				$stmtSelExact->execute([':num' => $num]);
				$row = $stmtSelExact->fetch();
				if (!$row) {
					// Fallback con LPAD por si hay datos sin padding en DB
					$stmtSelLpad->execute([':num' => $num]);
					$row = $stmtSelLpad->fetch();
				}
				if (!$row) {
					if (property_exists($this, 'logger') && $this->logger) {
						$this->logger->logInfo('Bloqueo: boleto no encontrado', null, 'BoletoModel::bloquearTemporal', ['numero' => $num]);
					}
					$conflictos[] = $num;
					continue;
				}
				if ($row['estado'] !== 'disponible') {
					if (property_exists($this, 'logger') && $this->logger) {
						$this->logger->logInfo('Bloqueo: estado no disponible', null, 'BoletoModel::bloquearTemporal', ['numero' => $num, 'estado' => $row['estado']]);
					}
					$conflictos[] = $num;
					continue;
				}
				$stmtUpd->execute([':orden' => $ordenId, ':id' => $row['id']]);
				if ($stmtUpd->rowCount() === 1) {
					$bloqueados[] = $num;
				} else {
					if (property_exists($this, 'logger') && $this->logger) {
						$this->logger->logInfo('Bloqueo: UPDATE no afectó filas', null, 'BoletoModel::bloquearTemporal', ['numero' => $num, 'id' => $row['id']]);
					}
					$conflictos[] = $num;
				}
			}

			if (count($bloqueados) === 0) {
				if ($started) $this->db->rollback();
				return ['ok' => false, 'bloqueados' => [], 'conflictos' => $conflictos];
			}

			if ($started) $this->db->commit();
			return ['ok' => true, 'bloqueados' => $bloqueados, 'conflictos' => $conflictos];
		} catch (Throwable $e) {
			if ($started) $this->db->rollback();
			if (property_exists($this, 'logger') && $this->logger) {
				$this->logger->logError('Error al bloquear boletos: ' . $e->getMessage(), __FILE__, __LINE__);
			}
			return ['ok' => false, 'bloqueados' => [], 'conflictos' => $numeros];
		}
	}

	public function liberarPorOrden(int $ordenId): void
	{
		$sql = "UPDATE {$this->table} SET estado = 'disponible', orden_id = NULL, fecha_bloqueo = NULL WHERE orden_id = :orden AND estado = 'bloqueado_temporal'";
		$this->db->execute($sql, [':orden' => $ordenId]);
	}

	public function marcarVendidosPorOrden(int $ordenId): void
	{
		$sql = "UPDATE {$this->table} SET estado = 'vendido', fecha_venta = NOW() WHERE orden_id = :orden AND estado = 'bloqueado_temporal'";
		$this->db->execute($sql, [':orden' => $ordenId]);
	}

	/**
	 * Retorna los números (formato 5 dígitos) que no están disponibles de una lista dada
	 */
	public function getNoDisponiblesPorNumeros(array $numeros): array
	{
		if (empty($numeros)) return [];
		// Normalizar
		$nums = array_values(array_unique(array_map(function ($n) {
			$n = preg_replace('/\D/', '', (string)$n);
			return str_pad($n, 5, '0', STR_PAD_LEFT);
		}, $numeros)));
		$placeholders = implode(',', array_fill(0, count($nums), '?'));
		$sql = "SELECT LPAD(numero, 5,  '0') AS numero FROM {$this->table} WHERE LPAD(numero, 5,  '0') IN ($placeholders) AND estado <> 'disponible'";
		$rows = $this->db->fetchAll($sql, $nums);
		return array_column($rows, 'numero');
	}

	/**
	 * Lista boletos vendidos con información de orden y cliente
	 */
	public function listarVendidos(): array
	{
		$sql = "SELECT b.*, o.codigo_orden, o.fecha_aprobacion, c.nombre_completo, c.telefono, c.correo
				FROM {$this->table} b
				LEFT JOIN ordenes o ON o.id = b.orden_id
				LEFT JOIN clientes c ON c.id = o.usuario_id
				WHERE b.estado = 'vendido'
				ORDER BY b.fecha_venta DESC, b.updated_at DESC";
		return $this->db->fetchAll($sql);
	}

	/**
	 * Lista boletos bloqueados temporalmente (pendientes) con info de orden y cliente
	 */
	public function listarBloqueadosTemporal(): array
	{
		$sql = "SELECT b.*, o.codigo_orden, o.created_at as fecha_orden, o.fecha_expiracion, c.nombre_completo, c.telefono, c.correo
				FROM {$this->table} b
				LEFT JOIN ordenes o ON o.id = b.orden_id
				LEFT JOIN clientes c ON c.id = o.usuario_id
				WHERE b.estado = 'bloqueado_temporal'
				ORDER BY b.updated_at DESC";
		return $this->db->fetchAll($sql);
	}

	/**
	 * Obtiene los números (con padding 5 dígitos) asociados a una orden
	 */
	public function obtenerNumerosPorOrden(int $ordenId): array
	{
		$sql = "SELECT LPAD(numero, 5, '0') AS numero FROM {$this->table} WHERE orden_id = :orden ORDER BY numero ASC";
		$rows = $this->db->fetchAll($sql, [':orden' => $ordenId]);
		return array_column($rows, 'numero');
	}
}
