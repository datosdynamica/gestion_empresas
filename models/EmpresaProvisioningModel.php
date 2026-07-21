<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Provisionamiento base de la empresa
|--------------------------------------------------------------------------
| Despues de crear la empresa, este modelo prepara la estructura minima para
| operar: locales, depositos, caja, contadores y otras tablas heredadas desde
| una empresa plantilla.
*/

/**
 * Crea la estructura inicial necesaria para operar en Dynamica.
 */
class EmpresaProvisioningModel extends BaseModel
{
    private const TEMPLATE_CONTADORES_EMPRESA_ID = 2;
    private const TEMPLATE_MONEDAS_EMPRESA_ID = 2;
    private const TEMPLATE_IVAS_EMPRESA_ID = 0;
    private const TEMPLATE_GROUPS_EMPRESA_ID = 1;

    public function provisionBaseStructure(int $empresaId, int $localId): array
    {
        $depositoId = $this->ensureDepositoCentral($empresaId, $localId);
        $cajaCreated = $this->ensureCajaPrincipal($empresaId, $localId);
        $ivasCopied = $this->copyTemplateRowsByEmpresa('Ivas', self::TEMPLATE_IVAS_EMPRESA_ID, $empresaId);
        $contadoresCopied = $this->copyContadoresTemplate($empresaId, $localId, $depositoId);
        $groupsCopied = $this->copySecurityGroups($empresaId);
        $marcaCreated = $this->ensureNamedCatalogRow('Marcas', 'Marca', 'SIN DEFINIR', $empresaId);
        $familiaCreated = $this->ensureNamedCatalogRow('Familias', 'Familia', 'SIN DEFINIR', $empresaId);
        $fidelizacionCreated = $this->ensureNamedCatalogRow('Fidelizacion', 'Fidelizacion', 'SIN DEFINIR', $empresaId);
        $giroCreated = $this->ensureNamedCatalogRow('giros', 'Giro', 'SIN DEFINIR', $empresaId);
        $departamentoCreated = $this->ensureDefaultDepartamento($empresaId);
        $monedasCopied = $this->copyTemplateRowsByEmpresa('Monedas', self::TEMPLATE_MONEDAS_EMPRESA_ID, $empresaId);
        $this->markEmpresaCreated($empresaId);

        return [
            'local_id' => $localId,
            'deposito_id' => $depositoId,
            'caja_creada' => $cajaCreated,
            'ivas_copiados' => $ivasCopied,
            'contadores_copiados' => $contadoresCopied,
            'grupos_copiados' => $groupsCopied,
            'marca_creada' => $marcaCreated,
            'familia_creada' => $familiaCreated,
            'fidelizacion_creada' => $fidelizacionCreated,
            'giro_creado' => $giroCreated,
            'departamento_creado' => $departamentoCreated,
            'monedas_copiadas' => $monedasCopied,
        ];
    }

    private function ensureDepositoCentral(int $empresaId, int $localId): int
    {
        $existing = $this->fetchOne(
            'SELECT IdDeposito FROM Depositos WHERE IdEmpresa = ? AND IdLocal = ? ORDER BY IdDeposito ASC LIMIT 1',
            [$empresaId, $localId]
        );

        if ($existing) {
            return (int) ($existing['IdDeposito'] ?? 0);
        }

        $columns = $this->listColumns('Depositos');
        $data = [
            'IdEmpresa' => $empresaId,
            'IdLocal' => $localId,
            'Nombre' => 'CENTRAL',
        ];

        return $this->insertRow('Depositos', $data, $columns);
    }

    private function ensureCajaPrincipal(int $empresaId, int $localId): bool
    {
        $existing = $this->fetchOne(
            'SELECT 1 FROM Cajas WHERE IdLocal = ? LIMIT 1',
            [$localId]
        );

        if ($existing) {
            return false;
        }

        $columns = $this->listColumns('Cajas');
        $data = [
            'Nombre' => 'CAJA',
            'IdLocal' => $localId,
            'IdEmpresa' => $empresaId,
        ];

        $this->insertRow('Cajas', $data, $columns);
        return true;
    }

    private function copyTemplateRowsByEmpresa(string $table, int $sourceEmpresaId, int $targetEmpresaId): int
    {
        if (!$this->tableHasColumn($table, 'IdEmpresa')) {
            return 0;
        }

        $existingCount = (int) $this->fetchValue("SELECT COUNT(*) FROM {$table} WHERE IdEmpresa = ?", [$targetEmpresaId]);
        if ($existingCount > 0) {
            return 0;
        }

        $rows = $this->fetchAll("SELECT * FROM {$table} WHERE IdEmpresa = ?", [$sourceEmpresaId]);
        if ($rows === []) {
            return 0;
        }

        $columns = $this->listColumns($table);
        $autoColumns = $this->detectAutoIncrementColumns($table);
        $copied = 0;

        foreach ($rows as $row) {
            foreach ($autoColumns as $autoColumn) {
                unset($row[$autoColumn]);
            }
            $row['IdEmpresa'] = $targetEmpresaId;
            $this->insertRow($table, $row, $columns);
            $copied++;
        }

        return $copied;
    }

    private function copyContadoresTemplate(int $empresaId, int $localId, int $depositoId): int
    {
        $existingCount = (int) $this->fetchValue('SELECT COUNT(*) FROM Contadores WHERE IdEmpresa = ?', [$empresaId]);
        if ($existingCount > 0) {
            return 0;
        }

        $rows = $this->fetchAll('SELECT * FROM Contadores WHERE IdEmpresa = ?', [self::TEMPLATE_CONTADORES_EMPRESA_ID]);
        if ($rows === []) {
            return 0;
        }

        $columns = $this->listColumns('Contadores');
        $autoColumns = $this->detectAutoIncrementColumns('Contadores');
        $copied = 0;

        foreach ($rows as $row) {
            foreach ($autoColumns as $autoColumn) {
                unset($row[$autoColumn]);
            }

            $row['IdEmpresa'] = $empresaId;
            if (isset($columns['IdLocal'])) {
                $row['IdLocal'] = $localId;
            }
            if (isset($columns['IdDeposito'])) {
                $row['IdDeposito'] = $depositoId;
            }
            if (isset($columns['Serie'])) {
                $row['Serie'] = 'A';
            }
            if (isset($columns['Ultimo'])) {
                $row['Ultimo'] = 0;
            }

            $this->insertRow('Contadores', $row, $columns);
            $copied++;
        }

        return $copied;
    }

    private function copySecurityGroups(int $empresaId): int
    {
        if (!$this->tableHasColumn('sec_groups', 'IdEmpresa')) {
            return 0;
        }

        $sourceGroups = $this->fetchAll(
            'SELECT group_id, description FROM sec_groups WHERE IdEmpresa = ? ORDER BY group_id ASC',
            [self::TEMPLATE_GROUPS_EMPRESA_ID]
        );

        if ($sourceGroups === []) {
            return 0;
        }

        $groupColumns = $this->listColumns('sec_groups');
        $groupAutoColumns = $this->detectAutoIncrementColumns('sec_groups');
        $groupAppsColumns = $this->listColumns('sec_groups_apps');
        $groupAppsAutoColumns = $this->detectAutoIncrementColumns('sec_groups_apps');
        $copied = 0;

        foreach ($sourceGroups as $group) {
            $description = trim((string) ($group['description'] ?? ''));
            if ($description === '') {
                continue;
            }

            $targetGroup = $this->fetchOne(
                'SELECT group_id FROM sec_groups WHERE IdEmpresa = ? AND description = ? LIMIT 1',
                [$empresaId, $description]
            );

            if ($targetGroup) {
                $targetGroupId = (int) ($targetGroup['group_id'] ?? 0);
            } else {
                $sourceRow = $this->fetchOne(
                    'SELECT * FROM sec_groups WHERE group_id = ? LIMIT 1',
                    [(int) $group['group_id']]
                );
                if (!$sourceRow) {
                    continue;
                }

                foreach ($groupAutoColumns as $autoColumn) {
                    unset($sourceRow[$autoColumn]);
                }
                $sourceRow['IdEmpresa'] = $empresaId;
                $targetGroupId = $this->insertRow('sec_groups', $sourceRow, $groupColumns);
                $copied++;
            }

            $sourceApps = $this->fetchAll(
                'SELECT * FROM sec_groups_apps WHERE group_id = ?',
                [(int) $group['group_id']]
            );

            foreach ($sourceApps as $sourceApp) {
                $appName = trim((string) ($sourceApp['app_name'] ?? ''));
                if ($appName === '') {
                    continue;
                }

                $appExists = $this->fetchOne(
                    'SELECT 1 FROM sec_groups_apps WHERE group_id = ? AND app_name = ? LIMIT 1',
                    [$targetGroupId, $appName]
                );
                if ($appExists) {
                    continue;
                }

                foreach ($groupAppsAutoColumns as $autoColumn) {
                    unset($sourceApp[$autoColumn]);
                }
                $sourceApp['group_id'] = $targetGroupId;
                $this->insertRow('sec_groups_apps', $sourceApp, $groupAppsColumns);
            }
        }

        return $copied;
    }

    private function ensureNamedCatalogRow(string $table, string $nameColumn, string $value, int $empresaId): bool
    {
        $exists = $this->fetchOne(
            "SELECT 1 FROM {$table} WHERE IdEmpresa = ? AND {$nameColumn} = ? LIMIT 1",
            [$empresaId, $value]
        );

        if ($exists) {
            return false;
        }

        $columns = $this->listColumns($table);
        $data = [
            $nameColumn => $value,
            'IdEmpresa' => $empresaId,
        ];

        $this->insertRow($table, $data, $columns);
        return true;
    }

    private function ensureDefaultDepartamento(int $empresaId): bool
    {
        $exists = $this->fetchOne(
            'SELECT 1 FROM Departamentos WHERE IdEmpresa = ? AND Departamento = ? LIMIT 1',
            [$empresaId, 'MONTEVIDEO']
        );

        if ($exists) {
            return false;
        }

        $columns = $this->listColumns('Departamentos');
        $data = [
            'Departamento' => 'MONTEVIDEO',
            'IdEmpresa' => $empresaId,
            'IdPais' => 0,
        ];

        $this->insertRow('Departamentos', $data, $columns);
        return true;
    }

    private function markEmpresaCreated(int $empresaId): void
    {
        $stmt = $this->db->prepare('UPDATE Empresas SET creada = 1 WHERE IdEmpresa = ?');
        $stmt->execute([$empresaId]);
    }

    private function listColumns(string $table): array
    {
        $stmt = $this->db->query('SHOW COLUMNS FROM ' . $table);
        $columns = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
            $field = (string) ($column['Field'] ?? '');
            if ($field !== '') {
                $columns[$field] = $column;
            }
        }

        return $columns;
    }

    private function detectAutoIncrementColumns(string $table): array
    {
        $columns = $this->listColumns($table);
        $auto = [];

        foreach ($columns as $field => $meta) {
            if (stripos((string) ($meta['Extra'] ?? ''), 'auto_increment') !== false) {
                $auto[] = $field;
            }
        }

        return $auto;
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        $columns = $this->listColumns($table);
        return isset($columns[$column]);
    }

    private function insertRow(string $table, array $data, array $columns): int
    {
        $insertData = [];
        foreach ($data as $field => $value) {
            if (isset($columns[$field])) {
                $insertData[$field] = $value;
            }
        }

        if ($insertData === []) {
            throw new RuntimeException("No hay columnas compatibles para insertar en {$table}.");
        }

        $fieldList = array_keys($insertData);
        $placeholders = array_map(static function (string $field): string {
            return ':' . $field;
        }, $fieldList);

        $sql = 'INSERT INTO ' . $table
            . ' (' . implode(', ', $fieldList) . ')'
            . ' VALUES (' . implode(', ', $placeholders) . ')';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($insertData);

        return (int) $this->db->lastInsertId();
    }

    private function fetchValue(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
