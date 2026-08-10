<?php

declare(strict_types=1);

class CertificateHistoryModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    public function listByEmpresaId(int $empresaId, int $limit = 50): array
    {
        $empresaId = max(0, $empresaId);
        $limit = max(1, $limit);

        return $this->fetchAll(
            'SELECT Id,
                    EmpresaId,
                    Rut,
                    NuevaEmpresaId,
                    OrigenCarga,
                    NombreOriginal,
                    NombreGuardado,
                    RutaArchivo,
                    PasswordCertificado,
                    AliasCertificado,
                    FechaVencimiento,
                    DiasRestantes,
                    UsuarioLogin,
                    UsuarioNombre,
                    EstadoCarga,
                    Detalle,
                    FechaCarga
             FROM ' . TABLA_CERTIFICADOS_HISTORIAL . '
             WHERE EmpresaId = ?
             ORDER BY FechaCarga DESC, Id DESC
             LIMIT ' . $limit,
            [$empresaId]
        );
    }

    public function findLatestByEmpresaId(int $empresaId): ?array
    {
        return $this->fetchOne(
            'SELECT Id,
                    EmpresaId,
                    Rut,
                    NuevaEmpresaId,
                    OrigenCarga,
                    NombreOriginal,
                    NombreGuardado,
                    RutaArchivo,
                    PasswordCertificado,
                    AliasCertificado,
                    FechaVencimiento,
                    DiasRestantes,
                    UsuarioLogin,
                    UsuarioNombre,
                    EstadoCarga,
                    Detalle,
                    FechaCarga
             FROM ' . TABLA_CERTIFICADOS_HISTORIAL . '
             WHERE EmpresaId = ?
             ORDER BY FechaCarga DESC, Id DESC
             LIMIT 1',
            [$empresaId]
        );
    }

    public function findByEmpresaAndPath(int $empresaId, string $rutaArchivo): ?array
    {
        return $this->fetchOne(
            'SELECT Id,
                    EmpresaId,
                    Rut,
                    NuevaEmpresaId,
                    OrigenCarga,
                    NombreOriginal,
                    NombreGuardado,
                    RutaArchivo,
                    PasswordCertificado,
                    AliasCertificado,
                    FechaVencimiento,
                    DiasRestantes,
                    UsuarioLogin,
                    UsuarioNombre,
                    EstadoCarga,
                    Detalle,
                    FechaCarga
             FROM ' . TABLA_CERTIFICADOS_HISTORIAL . '
             WHERE EmpresaId = ?
               AND RutaArchivo = ?
             LIMIT 1',
            [$empresaId, trim($rutaArchivo)]
        );
    }

    public function upsertByEmpresaAndPath(array $data): int
    {
        $empresaId = (int) ($data['empresa_id'] ?? 0);
        $rutaArchivo = trim((string) ($data['ruta_archivo'] ?? ''));

        if ($empresaId <= 0 || $rutaArchivo === '') {
            throw new RuntimeException('No fue posible guardar el historial del certificado por falta de EmpresaId o RutaArchivo.');
        }

        $existing = $this->findByEmpresaAndPath($empresaId, $rutaArchivo);
        if ($existing !== null) {
            $this->update((int) ($existing['Id'] ?? 0), $data);
            return (int) ($existing['Id'] ?? 0);
        }

        return $this->create($data);
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO ' . TABLA_CERTIFICADOS_HISTORIAL . ' (
                    EmpresaId,
                    Rut,
                    NuevaEmpresaId,
                    OrigenCarga,
                    NombreOriginal,
                    NombreGuardado,
                    RutaArchivo,
                    PasswordCertificado,
                    AliasCertificado,
                    FechaVencimiento,
                    DiasRestantes,
                    UsuarioLogin,
                    UsuarioNombre,
                    EstadoCarga,
                    Detalle,
                    FechaCarga
                ) VALUES (
                    :empresa_id,
                    :rut,
                    :nueva_empresa_id,
                    :origen_carga,
                    :nombre_original,
                    :nombre_guardado,
                    :ruta_archivo,
                    :password_certificado,
                    :alias_certificado,
                    :fecha_vencimiento,
                    :dias_restantes,
                    :usuario_login,
                    :usuario_nombre,
                    :estado_carga,
                    :detalle,
                    NOW()
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->normalizeRow($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        if ($id <= 0) {
            return;
        }

        $sql = 'UPDATE ' . TABLA_CERTIFICADOS_HISTORIAL . '
                SET Rut = :rut,
                    NuevaEmpresaId = :nueva_empresa_id,
                    OrigenCarga = :origen_carga,
                    NombreOriginal = :nombre_original,
                    NombreGuardado = :nombre_guardado,
                    RutaArchivo = :ruta_archivo,
                    PasswordCertificado = :password_certificado,
                    AliasCertificado = :alias_certificado,
                    FechaVencimiento = :fecha_vencimiento,
                    DiasRestantes = :dias_restantes,
                    UsuarioLogin = :usuario_login,
                    UsuarioNombre = :usuario_nombre,
                    EstadoCarga = :estado_carga,
                    Detalle = :detalle,
                    UpdatedAt = CURRENT_TIMESTAMP
                WHERE Id = :id';

        $row = $this->normalizeRow($data);
        $row['id'] = $id;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($row);
    }

    private function normalizeRow(array $data): array
    {
        $fechaVencimiento = trim((string) ($data['fecha_vencimiento'] ?? ''));
        return [
            'empresa_id' => (int) ($data['empresa_id'] ?? 0),
            'rut' => mb_substr(trim((string) ($data['rut'] ?? '')), 0, 20),
            'nueva_empresa_id' => !empty($data['nueva_empresa_id']) ? (int) $data['nueva_empresa_id'] : null,
            'origen_carga' => mb_substr(trim((string) ($data['origen_carga'] ?? 'CERTIFICADOS')), 0, 20),
            'nombre_original' => mb_substr(trim((string) ($data['nombre_original'] ?? '')), 0, 255),
            'nombre_guardado' => mb_substr(trim((string) ($data['nombre_guardado'] ?? '')), 0, 255),
            'ruta_archivo' => trim((string) ($data['ruta_archivo'] ?? '')),
            'password_certificado' => trim((string) ($data['password_certificado'] ?? '')),
            'alias_certificado' => mb_substr(trim((string) ($data['alias_certificado'] ?? '')), 0, 255),
            'fecha_vencimiento' => $fechaVencimiento !== '' ? $fechaVencimiento : null,
            'dias_restantes' => isset($data['dias_restantes']) && $data['dias_restantes'] !== '' ? (int) $data['dias_restantes'] : null,
            'usuario_login' => mb_substr(trim((string) ($data['usuario_login'] ?? '')), 0, 100),
            'usuario_nombre' => mb_substr(trim((string) ($data['usuario_nombre'] ?? '')), 0, 150),
            'estado_carga' => mb_substr(trim((string) ($data['estado_carga'] ?? 'DISPONIBLE')), 0, 30),
            'detalle' => trim((string) ($data['detalle'] ?? '')),
        ];
    }

    private function ensureTable(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . TABLA_CERTIFICADOS_HISTORIAL . ' (
                    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    EmpresaId INT NOT NULL,
                    Rut VARCHAR(20) NOT NULL DEFAULT \'\',
                    NuevaEmpresaId INT NULL,
                    OrigenCarga VARCHAR(20) NOT NULL DEFAULT \'CERTIFICADOS\',
                    NombreOriginal VARCHAR(255) NOT NULL DEFAULT \'\',
                    NombreGuardado VARCHAR(255) NOT NULL DEFAULT \'\',
                    RutaArchivo TEXT NOT NULL,
                    PasswordCertificado VARCHAR(255) NOT NULL DEFAULT \'\',
                    AliasCertificado VARCHAR(255) NOT NULL DEFAULT \'\',
                    FechaVencimiento DATE NULL,
                    DiasRestantes INT NULL,
                    UsuarioLogin VARCHAR(100) NOT NULL DEFAULT \'\',
                    UsuarioNombre VARCHAR(150) NOT NULL DEFAULT \'\',
                    EstadoCarga VARCHAR(30) NOT NULL DEFAULT \'DISPONIBLE\',
                    Detalle TEXT NULL,
                    FechaCarga DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    CreatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UpdatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (Id),
                    KEY idx_cert_hist_empresa_fecha (EmpresaId, FechaCarga),
                    KEY idx_cert_hist_rut_fecha (Rut, FechaCarga),
                    KEY idx_cert_hist_nueva_empresa (NuevaEmpresaId)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        $this->db->exec($sql);
    }
}
