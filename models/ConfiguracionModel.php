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

	public function set(string $clave, $valor, string $tipo = 'texto'): bool
	{
		// Normaliza el valor para almacenar como string
		$valorStr = $this->stringifyValor($valor, $tipo);
		$sql = "INSERT INTO {$this->table} (clave, valor, tipo) VALUES (:clave, :valor, :tipo)
			ON DUPLICATE KEY UPDATE valor = VALUES(valor), tipo = VALUES(tipo)";
		return $this->db->execute($sql, [
			':clave' => $clave,
			':valor' => $valorStr,
			':tipo' => $tipo,
		]);
	}

	private function stringifyValor($valor, string $tipo): string
	{
		switch ($tipo) {
			case 'numero':
				return (string)(int)$valor;
			case 'decimal':
				return (string)(float)$valor;
			case 'boolean':
				return ((int)$valor === 1) ? '1' : '0';
			case 'json':
				return json_encode($valor, JSON_UNESCAPED_UNICODE);
			case 'texto':
			default:
				return (string)$valor;
		}
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
