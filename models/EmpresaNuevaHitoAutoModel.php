<?php

declare(strict_types=1);

// Esta tabla funciona como una cola simple de tareas automaticas del
// onboarding. Se usa para separar lo que el usuario marca manualmente de lo
// que debe correr despues por proceso diferido.
class EmpresaNuevaHitoAutoModel extends BaseModel
{
    public function ensureTable(): void
    {
        // La tabla se crea a demanda para simplificar despliegues en entornos
        // donde todavia no exista este bloque.
        $sql = "
            CREATE TABLE IF NOT EXISTS " . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . " (
                Id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                NuevaEmpresaId INT UNSIGNED NOT NULL,
                TareaCodigo VARCHAR(60) NOT NULL,
                Estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE',
                ProgramadoPara DATETIME NOT NULL,
                Intentos INT UNSIGNED NOT NULL DEFAULT 0,
                PayloadJson LONGTEXT NULL,
                UltimoResultado LONGTEXT NULL,
                UltimoError LONGTEXT NULL,
                FechaProcesado DATETIME NULL,
                FechaCreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FechaActualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (Id),
                KEY idx_tarea_estado_programado (TareaCodigo, Estado, ProgramadoPara),
                KEY idx_nueva_empresa (NuevaEmpresaId)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $this->db->exec($sql);
    }

    public function scheduleCredentialsTask(int $nuevaEmpresaId, string $scheduledAt, array $payload = []): int
    {
        $this->ensureTable();

        // Si el caso ya tiene una tarea viva, se recicla en vez de duplicarla.
        $existing = $this->fetchOne(
            'SELECT Id FROM ' . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . " WHERE NuevaEmpresaId = ? AND TareaCodigo = 'ENVIO_CREDENCIALES' AND Estado IN ('PENDIENTE','PROCESANDO','ERROR') ORDER BY Id DESC LIMIT 1",
            [$nuevaEmpresaId]
        );

        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($existing !== null) {
            $taskId = (int) ($existing['Id'] ?? 0);
            $stmt = $this->db->prepare(
                'UPDATE ' . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . "
                 SET Estado = 'PENDIENTE',
                     ProgramadoPara = ?,
                     PayloadJson = ?,
                     UltimoError = NULL,
                     UltimoResultado = NULL,
                     FechaProcesado = NULL
                 WHERE Id = ?"
            );
            $stmt->execute([$scheduledAt, $payloadJson, $taskId]);
            return $taskId;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO ' . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . ' (NuevaEmpresaId, TareaCodigo, Estado, ProgramadoPara, PayloadJson) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$nuevaEmpresaId, 'ENVIO_CREDENCIALES', 'PENDIENTE', $scheduledAt, $payloadJson]);

        return (int) $this->db->lastInsertId();
    }

    public function listDueTasks(string $taskCode, int $limit = 20): array
    {
        $this->ensureTable();
        $limit = max(1, $limit);

        // Se listan solo tareas vencidas y aun pendientes, respetando orden de
        // programacion para no adelantar casos mas nuevos.
        $sql = 'SELECT *
                FROM ' . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . "
                WHERE TareaCodigo = ?
                  AND Estado = 'PENDIENTE'
                  AND ProgramadoPara <= NOW()
                ORDER BY ProgramadoPara ASC, Id ASC
                LIMIT " . $limit;

        return $this->fetchAll($sql, [$taskCode]);
    }

    public function markProcessing(int $id): bool
    {
        $this->ensureTable();

        // Este cambio de estado evita que dos procesos tomen la misma tarea al
        // mismo tiempo.
        $stmt = $this->db->prepare(
            'UPDATE ' . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . "
             SET Estado = 'PROCESANDO',
                 Intentos = Intentos + 1,
                 UltimoError = NULL
             WHERE Id = ?
               AND Estado = 'PENDIENTE'"
        );
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public function markSuccess(int $id, array $result = []): void
    {
        $this->ensureTable();

        // Se guarda un resumen del resultado para poder auditar despues que
        // paso con el envio automatico.
        $stmt = $this->db->prepare(
            'UPDATE ' . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . "
             SET Estado = 'OK',
                 FechaProcesado = NOW(),
                 UltimoResultado = ?,
                 UltimoError = NULL
             WHERE Id = ?"
        );
        $stmt->execute([
            json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $id,
        ]);
    }

    public function markError(int $id, string $error, array $context = []): void
    {
        $this->ensureTable();

        // El contexto del error ayuda a reintentar con criterio sin perder el
        // motivo exacto de la falla.
        $stmt = $this->db->prepare(
            'UPDATE ' . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . "
             SET Estado = 'ERROR',
                 UltimoError = ?,
                 UltimoResultado = ?
             WHERE Id = ?"
        );
        $stmt->execute([
            $error,
            json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $id,
        ]);
    }

    public function findLatestCredentialsTasksByNuevaEmpresaIds(array $nuevaEmpresaIds): array
    {
        $this->ensureTable();

        // En la grilla solo interesa la ultima tarea conocida de cada caso
        // para decidir si ya se envio, si quedo programada o si fallo.
        $ids = array_values(array_filter(array_map('intval', $nuevaEmpresaIds), static function (int $id): bool {
            return $id > 0;
        }));

        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = 'SELECT t.*
                FROM ' . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . ' t
                INNER JOIN (
                    SELECT NuevaEmpresaId, MAX(Id) AS MaxId
                    FROM ' . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . "
                    WHERE TareaCodigo = 'ENVIO_CREDENCIALES'
                      AND NuevaEmpresaId IN (" . $placeholders . ')
                    GROUP BY NuevaEmpresaId
                ) latest ON latest.MaxId = t.Id';

        $rows = $this->fetchAll($sql, $ids);
        $map = [];
        foreach ($rows as $row) {
            $map[(int) ($row['NuevaEmpresaId'] ?? 0)] = $row;
        }

        return $map;
    }

    public function listAutomationAlerts(int $limit = 50): array
    {
        $this->ensureTable();
        $limit = max(1, $limit);

        $alerts = [];

        $taskRows = $this->fetchAll(
            'SELECT Id, NuevaEmpresaId, TareaCodigo, Estado, ProgramadoPara, Intentos, FechaProcesado, FechaCreacion, FechaActualizacion, UltimoError
             FROM ' . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . "
             WHERE TareaCodigo = 'ENVIO_CREDENCIALES'
               AND (
                    Estado = 'ERROR'
                    OR (Estado = 'PROCESANDO' AND FechaActualizacion <= DATE_SUB(NOW(), INTERVAL 30 MINUTE))
                    OR (Estado = 'PENDIENTE' AND ProgramadoPara <= DATE_SUB(NOW(), INTERVAL 15 MINUTE))
               )
             ORDER BY FechaActualizacion DESC
             LIMIT " . $limit
        );

        foreach ($taskRows as $row) {
            $alerts[] = [
                'type' => 'TASK_' . strtoupper((string) ($row['Estado'] ?? 'UNKNOWN')),
                'nueva_empresa_id' => (int) ($row['NuevaEmpresaId'] ?? 0),
                'task_id' => (int) ($row['Id'] ?? 0),
                'task_code' => (string) ($row['TareaCodigo'] ?? ''),
                'estado' => (string) ($row['Estado'] ?? ''),
                'programado_para' => (string) ($row['ProgramadoPara'] ?? ''),
                'intentos' => (int) ($row['Intentos'] ?? 0),
                'fecha_actualizacion' => (string) ($row['FechaActualizacion'] ?? ''),
                'ultimo_error' => (string) ($row['UltimoError'] ?? ''),
            ];
        }

        $orphanRows = $this->fetchAll(
            'SELECT e.Id AS NuevaEmpresaId, e.HitoActual, e.EstadoDetalle, e.FechaActualizacion
             FROM ' . TABLA_EMPRESAS_NUEVAS . ' e
             LEFT JOIN (
                 SELECT NuevaEmpresaId, MAX(Id) AS MaxId
                 FROM ' . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . "
                 WHERE TareaCodigo = 'ENVIO_CREDENCIALES'
                 GROUP BY NuevaEmpresaId
             ) latest ON latest.NuevaEmpresaId = e.Id
             LEFT JOIN " . TABLA_EMPRESAS_NUEVAS_HITOS_AUTO . ' t ON t.Id = latest.MaxId
             WHERE e.HitoActual = ?
               AND (
                    t.Id IS NULL
                    OR t.Estado NOT IN (\'PENDIENTE\', \'PROCESANDO\', \'OK\')
               )
             ORDER BY e.FechaActualizacion DESC
             LIMIT ' . $limit,
            ['ENVIO_CREDENCIALES']
        );

        foreach ($orphanRows as $row) {
            $alerts[] = [
                'type' => 'CASE_WITHOUT_ACTIVE_TASK',
                'nueva_empresa_id' => (int) ($row['NuevaEmpresaId'] ?? 0),
                'task_id' => 0,
                'task_code' => 'ENVIO_CREDENCIALES',
                'estado' => (string) ($row['HitoActual'] ?? ''),
                'programado_para' => '',
                'intentos' => 0,
                'fecha_actualizacion' => (string) ($row['FechaActualizacion'] ?? ''),
                'ultimo_error' => (string) ($row['EstadoDetalle'] ?? ''),
            ];
        }

        return $alerts;
    }
}
