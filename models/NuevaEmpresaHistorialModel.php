<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Historial del workflow
|--------------------------------------------------------------------------
| Registra el camino del onboarding paso a paso para reconstruir que paso, quien
| lo ejecuto y en que momento quedo cada evento del flujo.
*/

/**
 * Modelo del historial visible y tecnico del onboarding.
 */
class NuevaEmpresaHistorialModel extends BaseModel
{
    public function listWorkflowEventsByNuevaEmpresaIds(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), static function (int $id): bool {
            return $id > 0;
        }));

        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
            SELECT
                NuevaEmpresaId AS nueva_empresa_id,
                Evento AS evento,
                Descripcion AS descripcion,
                FechaEvento AS fecha_evento
            FROM " . TABLA_EMPRESAS_NUEVAS_HISTORIAL . "
            WHERE NuevaEmpresaId IN ($placeholders)
              AND (Evento = 'CREACION' OR Evento = 'APROBACION' OR Evento LIKE 'HITO_%')
            ORDER BY FechaEvento ASC, Id ASC
        ";

        $rows = $this->fetchAll($sql, $ids);
        $grouped = [];
        foreach ($rows as $row) {
            $empresaId = (int) ($row['nueva_empresa_id'] ?? 0);
            if ($empresaId <= 0) {
                continue;
            }
            $grouped[$empresaId][] = $row;
        }

        return $grouped;
    }

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
