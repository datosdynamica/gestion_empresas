<?php

declare(strict_types=1);

class CertificateActionModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO ' . TABLA_CERTIFICADOS_ACCIONES . ' (
                    EmpresaId,
                    Rut,
                    Accion,
                    Descripcion,
                    UsuarioLogin,
                    UsuarioNombre,
                    FechaAccion
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            (int) ($data['empresa_id'] ?? 0),
            trim((string) ($data['rut'] ?? '')),
            trim((string) ($data['accion'] ?? '')),
            trim((string) ($data['descripcion'] ?? '')),
            trim((string) ($data['usuario_login'] ?? '')),
            trim((string) ($data['usuario_nombre'] ?? '')),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function listByEmpresaIds(array $empresaIds, int $limitPerCompany = 10): array
    {
        $empresaIds = array_values(array_unique(array_map('intval', $empresaIds)));
        $empresaIds = array_values(array_filter($empresaIds, static function (int $id): bool {
            return $id > 0;
        }));

        if ($empresaIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($empresaIds), '?'));
        $sql = 'SELECT Id,
                       EmpresaId,
                       Rut,
                       Accion,
                       Descripcion,
                       UsuarioLogin,
                       UsuarioNombre,
                       FechaAccion
                FROM ' . TABLA_CERTIFICADOS_ACCIONES . '
                WHERE EmpresaId IN (' . $placeholders . ')
                ORDER BY FechaAccion DESC, Id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($empresaIds);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $grouped = [];
        foreach ($rows as $row) {
            $empresaId = (int) ($row['EmpresaId'] ?? 0);
            if ($empresaId <= 0) {
                continue;
            }

            if (!isset($grouped[$empresaId])) {
                $grouped[$empresaId] = [];
            }

            if (count($grouped[$empresaId]) >= $limitPerCompany) {
                continue;
            }

            $grouped[$empresaId][] = $row;
        }

        return $grouped;
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT Id, EmpresaId, Rut, Accion, Descripcion, UsuarioLogin, UsuarioNombre, FechaAccion
             FROM ' . TABLA_CERTIFICADOS_ACCIONES . '
             WHERE Id = ? LIMIT 1',
            [$id]
        );
    }

    private function ensureTable(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . TABLA_CERTIFICADOS_ACCIONES . ' (
                    Id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    EmpresaId INT NOT NULL,
                    Rut VARCHAR(20) NOT NULL DEFAULT \'\',
                    Accion VARCHAR(40) NOT NULL,
                    Descripcion VARCHAR(255) NOT NULL DEFAULT \'\',
                    UsuarioLogin VARCHAR(100) NOT NULL DEFAULT \'\',
                    UsuarioNombre VARCHAR(150) NOT NULL DEFAULT \'\',
                    FechaAccion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (Id),
                    KEY idx_cert_acciones_empresa_fecha (EmpresaId, FechaAccion),
                    KEY idx_cert_acciones_rut (Rut)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        $this->db->exec($sql);
    }
}
