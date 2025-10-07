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
		return (int)$this->db->getLastInsertId();
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
}
