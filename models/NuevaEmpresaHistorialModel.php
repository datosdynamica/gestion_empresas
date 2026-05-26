<?php

declare(strict_types=1);

class NuevaEmpresaHistorialModel extends BaseModel
{
    public function create(array $data): int
    {
        $sql = "INSERT INTO nuevas_empresas_historial (
                    nueva_empresa_id, evento, estado_anterior, estado_nuevo, descripcion, usuario_evento
                ) VALUES (
                    :nueva_empresa_id, :evento, :estado_anterior, :estado_nuevo, :descripcion, :usuario_evento
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }
}
