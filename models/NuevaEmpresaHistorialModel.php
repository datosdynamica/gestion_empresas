<?php

declare(strict_types=1);

class NuevaEmpresaHistorialModel extends BaseModel
{
    public function listRecent(int $limit = 100): array
    {
        $limit = max(1, $limit);

        $sql = "
            SELECT
                h.Id AS id,
                h.NuevaEmpresaId AS nueva_empresa_id,
                h.Evento AS evento,
                h.EstadoAnterior AS estado_anterior,
                h.EstadoNuevo AS estado_nuevo,
                h.Descripcion AS descripcion,
                h.UsuarioEvento AS usuario_evento,
                h.FechaEvento AS fecha_evento,
                e.RazonSocial AS razon_social,
                e.Rut AS rut
            FROM " . TABLA_EMPRESAS_NUEVAS_HISTORIAL . " h
            LEFT JOIN " . TABLA_EMPRESAS_NUEVAS . " e ON e.Id = h.NuevaEmpresaId
            ORDER BY h.FechaEvento DESC
            LIMIT " . $limit;

        return $this->fetchAll($sql);
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO " . TABLA_EMPRESAS_NUEVAS_HISTORIAL . " (
                    NuevaEmpresaId, Evento, EstadoAnterior, EstadoNuevo, Descripcion, UsuarioEvento
                ) VALUES (
                    :nueva_empresa_id, :evento, :estado_anterior, :estado_nuevo, :descripcion, :usuario_evento
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }
}
