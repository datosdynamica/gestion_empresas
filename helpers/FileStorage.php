<?php

declare(strict_types=1);

class FileStorage
{
    public static function createFolder(int $nuevaEmpresaId): array
    {
        $relative = UPLOAD_BASE_RELATIVE . '/' . $nuevaEmpresaId;
        $absolute = rtrim((string) BASE_PATH, '\\/') . '/uploads/nuevas_empresas/' . $nuevaEmpresaId;

        if (!is_dir($absolute) && !mkdir($absolute, 0775, true) && !is_dir($absolute)) {
            throw new RuntimeException('No fue posible crear la carpeta de adjuntos.');
        }

        return [
            'relative' => $relative,
            'absolute' => $absolute,
        ];
    }

    public static function storeUploadedFile(array $file, string $tipo, int $nuevaEmpresaId): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException("Error al subir archivo {$tipo}.");
        }

        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $storedName = $tipo . '_' . uniqid('', true) . '.' . $ext;
        $relativePath = UPLOAD_BASE_RELATIVE . '/' . $nuevaEmpresaId . '/' . $storedName;
        $absolutePath = rtrim((string) BASE_PATH, '\\/') . '/uploads/nuevas_empresas/' . $nuevaEmpresaId . '/' . $storedName;

        if (!move_uploaded_file((string) $file['tmp_name'], $absolutePath)) {
            throw new RuntimeException("No fue posible guardar archivo {$tipo}.");
        }

        return [
            'original_name' => (string) $file['name'],
            'stored_name' => $storedName,
            'relative_path' => $relativePath,
            'extension' => $ext,
            'mime_type' => mime_content_type($absolutePath) ?: null,
            'size' => filesize($absolutePath) ?: 0,
        ];
    }

    public static function deleteFolder(int $nuevaEmpresaId): void
    {
        $absolute = rtrim((string) BASE_PATH, '\\/') . '/uploads/nuevas_empresas/' . $nuevaEmpresaId;
        if (!is_dir($absolute)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($absolute, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($fileInfo->getPathname());
            } else {
                unlink($fileInfo->getPathname());
            }
        }

        rmdir($absolute);
    }
}
