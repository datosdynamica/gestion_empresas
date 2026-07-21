<?php

declare(strict_types=1);

class LocalModel extends BaseModel
{
    public function findFirstByEmpresa(int $empresaId): ?array
    {
        return $this->fetchOne(
            'SELECT IdLocal, Local, IdEmpresa FROM Locales WHERE IdEmpresa = ? ORDER BY IdLocal ASC LIMIT 1',
            [$empresaId]
        );
    }

    public function ensureCasaCentral(int $empresaId): int
    {
        $existing = $this->findFirstByEmpresa($empresaId);
        if ($existing) {
            return (int) ($existing['IdLocal'] ?? 0);
        }

        $stmt = $this->db->prepare('INSERT INTO Locales (Local, IdEmpresa) VALUES (?, ?)');
        $stmt->execute(['CASA CENTRAL', $empresaId]);

        return (int) $this->db->lastInsertId();
    }
}
