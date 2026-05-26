<?php

declare(strict_types=1);

class NuevaEmpresaArchivoModel extends BaseModel
{
    public function create(array $data): int
    {
        $sql = "INSERT INTO nuevas_empresas_archivos (
                    nueva_empresa_id, tipo_archivo, nombre_original, nombre_guardado,
                    ruta_archivo, extension, mime_type, tamano_bytes, obligatorio,
                    usuario_subida
                ) VALUES (
                    :nueva_empresa_id, :tipo_archivo, :nombre_original, :nombre_guardado,
                    :ruta_archivo, :extension, :mime_type, :tamano_bytes, :obligatorio,
                    :usuario_subida
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function listByNuevaEmpresaId(int $id): array
    {
        return $this->fetchAll('SELECT * FROM nuevas_empresas_archivos WHERE nueva_empresa_id = ? ORDER BY id ASC', [$id]);
    }
}
