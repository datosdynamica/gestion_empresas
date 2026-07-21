<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Archivos adjuntos del onboarding
|--------------------------------------------------------------------------
| Maneja la metadata de los documentos cargados al registro temporal para luego
| descargarlos, reemplazarlos o moverlos cuando el flujo cambia de etapa.
*/

/**
 * Modelo de adjuntos asociados a una nueva empresa.
 */
class NuevaEmpresaArchivoModel extends BaseModel
{
    private const SELECT_ALIASES = "
        Id AS id,
        NuevaEmpresaId AS nueva_empresa_id,
        TipoArchivo AS tipo_archivo,
        NombreOriginal AS nombre_original,
        NombreGuardado AS nombre_guardado,
        RutaArchivo AS ruta_archivo,
        Extension AS extension,
        MimeType AS mime_type,
        TamanoBytes AS tamano_bytes,
        Obligatorio AS obligatorio,
        Activo AS activo,
        FechaSubida AS fecha_subida,
        UsuarioSubida AS usuario_subida
    ";

    public function create(array $data): int
    {
        $sql = "INSERT INTO " . TABLA_EMPRESAS_NUEVAS_ARCHIVOS . " (
                    NuevaEmpresaId, TipoArchivo, NombreOriginal, NombreGuardado,
                    RutaArchivo, Extension, MimeType, TamanoBytes, Obligatorio,
                    UsuarioSubida
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
        return $this->fetchAll('SELECT ' . self::SELECT_ALIASES . ' FROM ' . TABLA_EMPRESAS_NUEVAS_ARCHIVOS . ' WHERE NuevaEmpresaId = ? ORDER BY Id ASC', [$id]);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT ' . self::SELECT_ALIASES . ' FROM ' . TABLA_EMPRESAS_NUEVAS_ARCHIVOS . ' WHERE Id = ? LIMIT 1', [$id]);
    }

    public function updateFile(int $id, array $data): void
    {
        $sql = "UPDATE " . TABLA_EMPRESAS_NUEVAS_ARCHIVOS . "
                SET NombreOriginal = :nombre_original,
                    NombreGuardado = :nombre_guardado,
                    RutaArchivo = :ruta_archivo,
                    Extension = :extension,
                    MimeType = :mime_type,
                    TamanoBytes = :tamano_bytes,
                    FechaSubida = NOW(),
                    UsuarioSubida = :usuario_subida
                WHERE Id = :id";

        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);
    }

    public function rebasePathsForNuevaEmpresa(int $nuevaEmpresaId, string $baseRelativePath): void
    {
        $baseRelativePath = rtrim($baseRelativePath, '\\/');
        $sql = "UPDATE " . TABLA_EMPRESAS_NUEVAS_ARCHIVOS . "
                SET RutaArchivo = CONCAT(:base_relative_path, '/', NombreGuardado)
                WHERE NuevaEmpresaId = :nueva_empresa_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'base_relative_path' => $baseRelativePath,
            'nueva_empresa_id' => $nuevaEmpresaId,
        ]);
    }

    public function deleteByNuevaEmpresaId(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM ' . TABLA_EMPRESAS_NUEVAS_ARCHIVOS . ' WHERE NuevaEmpresaId = ?');
        $stmt->execute([$id]);
    }
}
