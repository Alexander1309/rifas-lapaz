<?php

class BoletoModel extends Model
{
	private string $table = 'boletos';

	public function getVendidosNumeros(int $limit = 1000): array
	{
		$sql = "SELECT LPAD(numero, 6, '0') AS numero FROM {$this->table} WHERE estado <> 'disponible' ORDER BY numero ASC LIMIT :lim";
		$stmt = $this->db->getConnection()->prepare($sql);
		$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
		$stmt->execute();
		return array_column($stmt->fetchAll(), 'numero');
	}
	public function bloquearTemporal(array $numeros, int $ordenId): array
	{
		if (empty($numeros)) return ['ok' => false, 'bloqueados' => [], 'conflictos' => []];

		// Normalizar todos los números a 6 dígitos
		$numeros = array_values(array_unique(array_map(function ($n) {
			$n = preg_replace('/\D/', '', (string)$n);
			return str_pad($n, 6, '0', STR_PAD_LEFT);
		}, $numeros)));

		sort($numeros);
		$this->db->beginTransaction();
		try {
			$bloqueados = [];
			$conflictos = [];
			$pdo = $this->db->getConnection();
			$sqlSelect = "SELECT id, numero, estado FROM {$this->table} WHERE LPAD(numero, 6, '0') = :num FOR UPDATE";
			$sqlUpdate = "UPDATE {$this->table} SET estado = 'bloqueado_temporal', orden_id = :orden, fecha_bloqueo = NOW() WHERE id = :id AND estado = 'disponible'";
			$stmtSel = $pdo->prepare($sqlSelect);
			$stmtUpd = $pdo->prepare($sqlUpdate);

			foreach ($numeros as $num) {
				$stmtSel->execute([':num' => $num]);
				$row = $stmtSel->fetch();
				if (!$row) {
					$conflictos[] = $num;
					continue;
				}
				if ($row['estado'] !== 'disponible') {
					$conflictos[] = $num;
					continue;
				}
				$stmtUpd->execute([':orden' => $ordenId, ':id' => $row['id']]);
				if ($stmtUpd->rowCount() === 1) {
					$bloqueados[] = $num;
				} else {
					$conflictos[] = $num;
				}
			}

			if (count($bloqueados) === 0) {
				$this->db->rollback();
				return ['ok' => false, 'bloqueados' => [], 'conflictos' => $conflictos];
			}

			$this->db->commit();
			return ['ok' => true, 'bloqueados' => $bloqueados, 'conflictos' => $conflictos];
		} catch (Throwable $e) {
			$this->db->rollback();
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
}
