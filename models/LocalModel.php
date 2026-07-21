<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Gestion de locales
|--------------------------------------------------------------------------
| Permite consultar y asegurar el local principal de una empresa para que el
| resto del flujo de provisionamiento tenga una referencia valida.
*/

/**
 * Modelo de apoyo para la tabla de locales.
 */
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
