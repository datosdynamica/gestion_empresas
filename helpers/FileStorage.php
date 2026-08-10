<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Almacenamiento de archivos del modulo
|--------------------------------------------------------------------------
| Maneja rutas, nombres fisicos y operaciones basicas de guardado y reemplazo
| para los adjuntos del onboarding. Sirve para separar la logica de archivos
| del controlador principal.
*/

/**
 * Encapsula la persistencia fisica de archivos del modulo.
 */
class FileStorage
{
    private const ONBOARDING_LOG_FILE = 'onboarding_trace.log';
    private const AUTOMATION_LOG_DIR = 'logs';
    private const AUTOMATION_LOG_FILE = 'onboarding_auto_runtime.log';

    private static function normalizeRut(string $rut): string
    {
        return preg_replace('/[^0-9A-Za-z]/', '', strtoupper(trim($rut))) ?: 'SIN_RUT';
    }

    public static function folderNameFromRut(string $rut): string
    {
        return 'R_' . self::normalizeRut($rut);
    }

    public static function temporaryFolderName(int $nuevaEmpresaId): string
    {
        return 'TMP_ONB_' . max(1, $nuevaEmpresaId);
    }

    public static function absoluteBaseFolder(string $rut): string
    {
        return rtrim((string) UPLOAD_BASE_DIR, '\\/') . '/' . self::folderNameFromRut($rut);
    }

    public static function absoluteTemporaryFolder(int $nuevaEmpresaId): string
    {
        return rtrim((string) UPLOAD_TEMP_BASE_DIR, '\\/') . '/' . self::temporaryFolderName($nuevaEmpresaId);
    }

    public static function createTemporaryFolder(int $nuevaEmpresaId): array
    {
        $folderName = self::temporaryFolderName($nuevaEmpresaId);
        $relative = rtrim((string) UPLOAD_TEMP_BASE_RELATIVE, '\\/') . '/' . $folderName;
        $absolute = self::absoluteTemporaryFolder($nuevaEmpresaId);

        if (!is_dir($absolute) && !mkdir($absolute, 0775, true) && !is_dir($absolute)) {
            throw new RuntimeException('No fue posible crear la carpeta temporal de adjuntos.');
        }

        return [
            'relative' => $relative,
            'absolute' => $absolute,
        ];
    }

    public static function ensureFolderForRutChange(string $currentRelativePath, string $oldRut, string $newRut): array
    {
        $currentRelativePath = trim($currentRelativePath);
        if (self::isTemporaryRelative($currentRelativePath)) {
            $absolute = self::absoluteFromRelative($currentRelativePath);
            if (!is_dir($absolute) && !mkdir($absolute, 0775, true) && !is_dir($absolute)) {
                throw new RuntimeException('No fue posible asegurar la carpeta temporal de adjuntos.');
            }

            return [
                'relative' => $currentRelativePath,
                'absolute' => $absolute,
            ];
        }

        $oldAbsolute = $currentRelativePath !== ''
            ? self::absoluteFromRelative($currentRelativePath)
            : self::absoluteBaseFolder($oldRut);
        $newAbsolute = self::absoluteBaseFolder($newRut);
        $newRelative = rtrim((string) UPLOAD_BASE_RELATIVE, '\\/') . '/' . self::folderNameFromRut($newRut);

        if ($oldAbsolute === $newAbsolute) {
            if (!is_dir($newAbsolute) && !mkdir($newAbsolute, 0775, true) && !is_dir($newAbsolute)) {
                throw new RuntimeException('No fue posible asegurar la carpeta de adjuntos del nuevo RUT.');
            }

            return [
                'relative' => $newRelative,
                'absolute' => $newAbsolute,
            ];
        }

        if (is_dir($oldAbsolute)) {
            if (!is_dir($newAbsolute)) {
                if (!@rename($oldAbsolute, $newAbsolute)) {
                    throw new RuntimeException('No fue posible renombrar la carpeta del cliente al nuevo RUT.');
                }
            } else {
                self::mergeFolderContents($oldAbsolute, $newAbsolute);
                self::deleteFolderPath($oldAbsolute);
            }
        } else {
            if (!is_dir($newAbsolute) && !mkdir($newAbsolute, 0775, true) && !is_dir($newAbsolute)) {
                throw new RuntimeException('No fue posible crear la carpeta de adjuntos del nuevo RUT.');
            }
        }

        return [
            'relative' => $newRelative,
            'absolute' => $newAbsolute,
        ];
    }

    public static function storeUploadedFileInFolder(array $file, string $tipo, string $folderRelative): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException("Error al subir archivo {$tipo}.");
        }

        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $storedName = $tipo . '_' . uniqid('', true) . '.' . $ext;
        $folderRelative = rtrim($folderRelative, '\\/');
        $folderAbsolute = self::absoluteFromRelative($folderRelative);

        if (!is_dir($folderAbsolute) && !mkdir($folderAbsolute, 0775, true) && !is_dir($folderAbsolute)) {
            throw new RuntimeException("No fue posible preparar la carpeta para archivo {$tipo}.");
        }

        $relativePath = $folderRelative . '/' . $storedName;
        $absolutePath = rtrim($folderAbsolute, '\\/') . '/' . $storedName;

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

    public static function moveOnboardingFolderToFinal(string $currentRelativePath, string $rut): array
    {
        $targetRelative = rtrim((string) UPLOAD_BASE_RELATIVE, '\\/') . '/' . self::folderNameFromRut($rut);
        return self::relocateFolder($currentRelativePath, $targetRelative);
    }

    public static function relocateFolder(string $currentRelativePath, string $targetRelativePath): array
    {
        $currentRelativePath = trim($currentRelativePath);
        $targetRelativePath = trim($targetRelativePath);

        $currentAbsolute = self::absoluteFromRelative($currentRelativePath);
        $targetAbsolute = self::absoluteFromRelative($targetRelativePath);

        if ($currentAbsolute === $targetAbsolute) {
            if (!is_dir($targetAbsolute) && !mkdir($targetAbsolute, 0775, true) && !is_dir($targetAbsolute)) {
                throw new RuntimeException('No fue posible asegurar la carpeta de adjuntos.');
            }

            return [
                'old_relative' => $currentRelativePath,
                'old_absolute' => $currentAbsolute,
                'relative' => $targetRelativePath,
                'absolute' => $targetAbsolute,
            ];
        }

        if (is_dir($currentAbsolute)) {
            if (!is_dir($targetAbsolute)) {
                if (!@rename($currentAbsolute, $targetAbsolute)) {
                    throw new RuntimeException('No fue posible mover la carpeta de adjuntos al destino final.');
                }
            } else {
                self::mergeFolderContents($currentAbsolute, $targetAbsolute);
                self::deleteFolderPath($currentAbsolute);
            }
        } else {
            if (!is_dir($targetAbsolute) && !mkdir($targetAbsolute, 0775, true) && !is_dir($targetAbsolute)) {
                throw new RuntimeException('No fue posible crear la carpeta de adjuntos en el destino final.');
            }
        }

        return [
            'old_relative' => $currentRelativePath,
            'old_absolute' => $currentAbsolute,
            'relative' => $targetRelativePath,
            'absolute' => $targetAbsolute,
        ];
    }

    public static function deleteFolderByRelative(string $relativePath): void
    {
        $absolute = self::absoluteFromRelative($relativePath);
        self::deleteFolderPath($absolute);
    }

    private static function deleteFolderPath(string $absolute): void
    {
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

    public static function deleteRelativeFile(string $relativePath): void
    {
        $absolute = self::absoluteFromRelative($relativePath);
        if (is_file($absolute)) {
            unlink($absolute);
        }
    }

    public static function absoluteFromRelative(string $relativePath): string
    {
        $normalized = str_replace('\\', '/', trim($relativePath));
        if ($normalized === '') {
            return rtrim((string) UPLOAD_BASE_DIR, '\\/');
        }

        if (preg_match('#^([A-Za-z]:/|/)#', $normalized) === 1) {
            return $normalized;
        }

        return rtrim((string) UPLOAD_BASE_DIR, '\\/') . '/' . ltrim($normalized, '\\/');
    }

    public static function isTemporaryRelative(string $relativePath): bool
    {
        $normalized = str_replace('\\', '/', trim($relativePath));
        if ($normalized === '') {
            return false;
        }

        $tempBase = rtrim(str_replace('\\', '/', (string) UPLOAD_TEMP_BASE_RELATIVE), '/');
        return strpos($normalized, $tempBase . '/') === 0
            || $normalized === $tempBase
            || strpos((string) basename($normalized), 'TMP_ONB_') !== false;
    }

    public static function appendOnboardingLog(string $folderRelative, string $event, array $context = []): ?string
    {
        if ($folderRelative === '') {
            return null;
        }

        $folderAbsolute = self::absoluteFromRelative($folderRelative);
        if (!is_dir($folderAbsolute)) {
            return null;
        }

        $timestamp = date('Y-m-d H:i:s');
        $lines = [
            str_repeat('=', 90),
            '[' . $timestamp . '] ' . $event,
            str_repeat('-', 90),
        ];

        foreach ($context as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'SI' : 'NO';
            } elseif (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif ($value === null) {
                $value = 'NULL';
            }

            $lines[] = $key . ': ' . (string) $value;
        }

        $lines[] = '';
        $payload = implode(PHP_EOL, $lines) . PHP_EOL;
        $target = $folderAbsolute . DIRECTORY_SEPARATOR . self::ONBOARDING_LOG_FILE;

        // El cron de onboarding corre con otro usuario del sistema. Si el archivo
        // ya fue creado por la web, intentamos normalizar permisos para evitar que
        // la traza quede sin actualizar cuando la cola automatica agrega eventos.
        self::ensureTraceWritable($folderAbsolute, $target);

        $written = @file_put_contents($target, $payload, FILE_APPEND | LOCK_EX);
        if ($written === false) {
            return null;
        }

        return $target;
    }

    public static function appendAutomationRuntimeLog(string $event, array $context = []): ?string
    {
        $logDir = rtrim((string) BASE_PATH, '\\/') . DIRECTORY_SEPARATOR . self::AUTOMATION_LOG_DIR;
        if (!is_dir($logDir) && !@mkdir($logDir, 0775, true) && !is_dir($logDir)) {
            return null;
        }

        if (!is_writable($logDir)) {
            @chmod($logDir, 0775);
        }

        $target = $logDir . DIRECTORY_SEPARATOR . self::AUTOMATION_LOG_FILE;
        if (is_file($target) && !is_writable($target)) {
            @chmod($target, 0664);
        }

        $timestamp = date('Y-m-d H:i:s');
        $lines = [
            str_repeat('=', 90),
            '[' . $timestamp . '] ' . $event,
            str_repeat('-', 90),
        ];

        foreach ($context as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'SI' : 'NO';
            } elseif (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif ($value === null) {
                $value = 'NULL';
            }

            $lines[] = $key . ': ' . (string) $value;
        }

        $lines[] = '';
        $payload = implode(PHP_EOL, $lines) . PHP_EOL;
        $written = @file_put_contents($target, $payload, FILE_APPEND | LOCK_EX);

        return $written === false ? null : $target;
    }

    private static function ensureTraceWritable(string $folderAbsolute, string $target): void
    {
        if (!is_dir($folderAbsolute)) {
            return;
        }

        if (!is_writable($folderAbsolute)) {
            @chmod($folderAbsolute, 0775);
        }

        if (is_file($target) && !is_writable($target)) {
            @chmod($target, 0664);
        }
    }

    private static function mergeFolderContents(string $source, string $target): void
    {
        $iterator = new FilesystemIterator($source, FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $fileInfo) {
            $sourcePath = $fileInfo->getPathname();
            $targetPath = rtrim($target, '\\/') . DIRECTORY_SEPARATOR . $fileInfo->getBasename();

            if ($fileInfo->isDir()) {
                if (!is_dir($targetPath) && !mkdir($targetPath, 0775, true) && !is_dir($targetPath)) {
                    throw new RuntimeException('No fue posible crear subcarpetas al reorganizar adjuntos por RUT.');
                }
                self::mergeFolderContents($sourcePath, $targetPath);
                @rmdir($sourcePath);
                continue;
            }

            if (is_file($targetPath)) {
                $pathInfo = pathinfo($targetPath);
                $targetPath = rtrim((string) ($pathInfo['dirname'] ?? $target), '\\/')
                    . DIRECTORY_SEPARATOR
                    . ($pathInfo['filename'] ?? 'archivo')
                    . '_' . uniqid('', true)
                    . (!empty($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '');
            }

            if (!@rename($sourcePath, $targetPath)) {
                throw new RuntimeException('No fue posible mover los adjuntos al nuevo RUT.');
            }
        }
    }
}
