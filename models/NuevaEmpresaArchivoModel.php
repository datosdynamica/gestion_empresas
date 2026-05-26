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

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM nuevas_empresas_archivos WHERE id = ? LIMIT 1', [$id]);
    }

    public function updateFile(int $id, array $data): void
    {
        $sql = "UPDATE nuevas_empresas_archivos
                SET nombre_original = :nombre_original,
                    nombre_guardado = :nombre_guardado,
                    ruta_archivo = :ruta_archivo,
                    extension = :extension,
                    mime_type = :mime_type,
                    tamano_bytes = :tamano_bytes,
                    fecha_subida = NOW(),
                    usuario_subida = :usuario_subida
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);
    }

    public function deleteByNuevaEmpresaId(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM nuevas_empresas_archivos WHERE nueva_empresa_id = ?');
        $stmt->execute([$id]);
    }
}
