<?php

class ConfiguracionModel extends Model
{
	private string $table = 'configuracion';

	public function get(string $clave, $default = null)
	{
		$sql = "SELECT valor, tipo FROM {$this->table} WHERE clave = :clave LIMIT 1";
		$row = $this->db->fetchOne($sql, [':clave' => $clave]);
		if (!$row) return $default;
		return $this->castValor($row['valor'] ?? null, $row['tipo'] ?? 'texto', $default);
	}

	public function getMany(array $claves): array
	{
		if (empty($claves)) return [];
		$placeholders = implode(',', array_fill(0, count($claves), '?'));
		$sql = "SELECT clave, valor, tipo FROM {$this->table} WHERE clave IN ($placeholders)";
		$rows = $this->db->fetchAll($sql, $claves);
		$out = [];
		foreach ($rows as $r) {
			$out[$r['clave']] = $this->castValor($r['valor'] ?? null, $r['tipo'] ?? 'texto');
		}
		return $out;
	}

	private function castValor($valor, string $tipo, $default = null)
	{
		if ($valor === null) return $default;
		switch ($tipo) {
			case 'numero':
				return (int)$valor;
			case 'decimal':
				return (float)$valor;
			case 'boolean':
				return (int)$valor === 1 || $valor === '1' || $valor === true;
			case 'json':
				$decoded = json_decode($valor, true);
				return $decoded ?? $default;
			case 'texto':
			default:
				return (string)$valor;
		}
	}
}
