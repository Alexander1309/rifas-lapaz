<?php

class ClienteModel extends Model
{
	private string $table = 'clientes';

	public function getOrCreate(string $nombre, string $telefono, string $correo): int
	{
		// Buscar por correo
		$row = $this->db->fetchOne("SELECT id FROM {$this->table} WHERE correo = :correo", [':correo' => $correo]);
		if ($row && isset($row['id'])) return (int)$row['id'];

		$this->db->execute(
			"INSERT INTO {$this->table} (nombre_completo, telefono, correo) VALUES (:n, :t, :c)",
			[':n' => $nombre, ':t' => $telefono, ':c' => $correo]
		);
		$cid = (int)$this->db->getLastInsertId();
		if ($cid <= 0) {
			// Fallback: reconsultar por correo para obtener el ID
			$row2 = $this->db->fetchOne("SELECT id FROM {$this->table} WHERE correo = :correo ORDER BY id DESC LIMIT 1", [':correo' => $correo]);
			if ($row2 && isset($row2['id'])) {
				$cid = (int)$row2['id'];
			}
		}
		if (property_exists($this, 'logger') && $this->logger) {
			$this->logger->logInfo('Cliente creado', null, 'ClienteModel::getOrCreate', ['cliente_id' => $cid, 'correo' => $correo]);
		}
		return $cid;
	}

	public function getById(int $id): ?array
	{
		$row = $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = :id", [':id' => $id]);
		return $row ?: null;
	}
}
