<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cache local de certificados Migrate
|--------------------------------------------------------------------------
| Guarda una foto local del estado de certificados por empresa para no depender
| siempre de una consulta directa al Web Service al mostrar el panel.
*/

/**
 * Persistencia del cache de certificados consultados en Migrate.
 */
class MigrateCertificateCacheModel extends BaseModel
{
    private const TABLE = 'MigrateCertificadosCache';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    public function listStatusMap(array $companyIds, string $environment): array
    {
        $companyIds = array_values(array_unique(array_filter(array_map('intval', $companyIds))));
        if ($companyIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($companyIds), '?'));
        $sql = 'SELECT EmpresaId, MAX(FechaConsulta) AS FechaConsulta
                FROM ' . self::TABLE . '
                WHERE SourceEnvironment = ?
                  AND EmpresaId IN (' . $placeholders . ')
                GROUP BY EmpresaId';

        $params = array_merge([$environment], $companyIds);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $empresaId = (int) ($row['EmpresaId'] ?? 0);
            if ($empresaId > 0) {
                $map[$empresaId] = (string) ($row['FechaConsulta'] ?? '');
            }
        }

        return $map;
    }

    public function replaceCompanySnapshot(array $company, array $items, array $meta): void
    {
        $empresaId = (int) ($company['empresa_id'] ?? 0);
        $rut = preg_replace('/\D+/', '', (string) ($company['rut'] ?? ''));
        if ($empresaId <= 0 || $rut === '') {
            return;
        }

        $environment = trim((string) ($meta['environment'] ?? ''));
        $fechaConsulta = trim((string) ($meta['fecha_consulta'] ?? date('Y-m-d H:i:s')));

        $delete = $this->db->prepare(
            'DELETE FROM ' . self::TABLE . ' WHERE EmpresaId = ? AND SourceEnvironment = ?'
        );
        $delete->execute([$empresaId, $environment]);

        $requestXml = (string) ($meta['request_xml'] ?? '');
        $responseXml = (string) ($meta['response_xml'] ?? '');
        $msgCode = (string) ($meta['msg_code'] ?? '');
        $msgDesc = (string) ($meta['msg_desc'] ?? '');
        $errorSummary = trim((string) ($meta['error_summary'] ?? ''));
        $wsdl = (string) ($meta['wsdl'] ?? '');

        if ($items === []) {
            $this->insertRow([
                'EmpresaId' => $empresaId,
                'Rut' => $rut,
                'RazonSocial' => (string) ($company['razon_social'] ?? ''),
                'EmpresaInvoicy' => (string) ($company['empresa_invoicy'] ?? ''),
                'Apodo' => '',
                'CerStatus' => '',
                'DiasRestantes' => null,
                'CerFchVencimiento' => null,
                'HasCertificate' => 0,
                'MsgCode' => $msgCode,
                'MsgDesc' => $msgDesc,
                'ErrorSummary' => $errorSummary,
                'RequestXml' => $requestXml,
                'ResponseXml' => $responseXml,
                'SourceEnvironment' => $environment,
                'SourceWSDL' => $wsdl,
                'FechaConsulta' => $fechaConsulta,
            ]);
            return;
        }

        foreach ($items as $item) {
            $diasRestantes = trim((string) ($item['dias_restantes'] ?? ''));
            $fechaVencimiento = trim((string) ($item['cer_fch_vencimiento'] ?? ''));
            $this->insertRow([
                'EmpresaId' => $empresaId,
                'Rut' => $rut,
                'RazonSocial' => (string) ($company['razon_social'] ?? ''),
                'EmpresaInvoicy' => (string) ($company['empresa_invoicy'] ?? ''),
                'Apodo' => mb_substr((string) ($item['apodo'] ?? ''), 0, 120),
                'CerStatus' => mb_substr((string) ($item['cer_status'] ?? ''), 0, 5),
                'DiasRestantes' => $diasRestantes !== '' ? (int) $diasRestantes : null,
                'CerFchVencimiento' => $this->normalizeDate($fechaVencimiento),
                'HasCertificate' => 1,
                'MsgCode' => $msgCode,
                'MsgDesc' => $msgDesc,
                'ErrorSummary' => $errorSummary,
                'RequestXml' => $requestXml,
                'ResponseXml' => $responseXml,
                'SourceEnvironment' => $environment,
                'SourceWSDL' => $wsdl,
                'FechaConsulta' => $fechaConsulta,
            ]);
        }
    }

    public function listCachedRows(array $companyIds, string $environment, bool $includePayload = true): array
    {
        $companyIds = array_values(array_unique(array_filter(array_map('intval', $companyIds))));
        if ($companyIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($companyIds), '?'));
        $payloadColumns = $includePayload
            ? ', RequestXml, ResponseXml'
            : ', CAST(\'\' AS CHAR) AS RequestXml, CAST(\'\' AS CHAR) AS ResponseXml';

        $sql = 'SELECT EmpresaId, Rut, RazonSocial, EmpresaInvoicy, Apodo, CerStatus, DiasRestantes,
                       CerFchVencimiento, HasCertificate, MsgCode, MsgDesc, ErrorSummary'
                       . $payloadColumns . ',
                       SourceEnvironment, SourceWSDL, FechaConsulta
                FROM ' . self::TABLE . '
                WHERE SourceEnvironment = ?
                  AND EmpresaId IN (' . $placeholders . ')
                ORDER BY DiasRestantes ASC, RazonSocial ASC, Apodo ASC';

        $params = array_merge([$environment], $companyIds);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listNotificationCandidates(array $daysTargets, string $environment, int $limit = 0): array
    {
        $daysTargets = array_values(array_unique(array_map('intval', $daysTargets)));
        $daysTargets = array_values(array_filter($daysTargets, static function (int $day): bool {
            return $day >= 0;
        }));

        if ($daysTargets === []) {
            return [];
        }

        $dayPlaceholders = implode(', ', array_fill(0, count($daysTargets), '?'));
        $sql = 'SELECT EmpresaId,
                       Rut,
                       RazonSocial,
                       EmpresaInvoicy,
                       Apodo,
                       CerStatus,
                       DiasRestantes,
                       CerFchVencimiento,
                       FechaConsulta
                FROM ' . self::TABLE . '
                WHERE SourceEnvironment = ?
                  AND HasCertificate = 1
                  AND CerStatus = ?
                  AND DiasRestantes IN (' . $dayPlaceholders . ')
                  AND CerFchVencimiento IS NOT NULL
                ORDER BY DiasRestantes ASC, CerFchVencimiento ASC, RazonSocial ASC';

        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }

        $params = array_merge([$environment, 'A'], $daysTargets);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listLatestSnapshotMap(array $companyIds, string $environment): array
    {
        $rows = $this->listCachedRows($companyIds, $environment, false);
        $map = [];

        foreach ($rows as $row) {
            $empresaId = (int) ($row['EmpresaId'] ?? 0);
            if ($empresaId <= 0 || isset($map[$empresaId])) {
                continue;
            }

            $map[$empresaId] = $row;
        }

        return $map;
    }

    private function insertRow(array $row): void
    {
        $sql = 'INSERT INTO ' . self::TABLE . ' (
                    EmpresaId, Rut, RazonSocial, EmpresaInvoicy, Apodo, CerStatus,
                    DiasRestantes, CerFchVencimiento, HasCertificate, MsgCode, MsgDesc,
                    ErrorSummary, RequestXml, ResponseXml, SourceEnvironment, SourceWSDL, FechaConsulta
                ) VALUES (
                    :EmpresaId, :Rut, :RazonSocial, :EmpresaInvoicy, :Apodo, :CerStatus,
                    :DiasRestantes, :CerFchVencimiento, :HasCertificate, :MsgCode, :MsgDesc,
                    :ErrorSummary, :RequestXml, :ResponseXml, :SourceEnvironment, :SourceWSDL, :FechaConsulta
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($row);
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $ts = strtotime($value);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }

    private function ensureTable(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' (
            Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            EmpresaId INT NOT NULL,
            Rut VARCHAR(20) NOT NULL,
            RazonSocial VARCHAR(255) NOT NULL DEFAULT \'\',
            EmpresaInvoicy VARCHAR(50) NOT NULL DEFAULT \'\',
            Apodo VARCHAR(120) NOT NULL DEFAULT \'\',
            CerStatus VARCHAR(5) NOT NULL DEFAULT \'\',
            DiasRestantes INT NULL,
            CerFchVencimiento DATE NULL,
            HasCertificate TINYINT(1) NOT NULL DEFAULT 0,
            MsgCode VARCHAR(10) NOT NULL DEFAULT \'\',
            MsgDesc VARCHAR(255) NOT NULL DEFAULT \'\',
            ErrorSummary TEXT NULL,
            RequestXml MEDIUMTEXT NULL,
            ResponseXml MEDIUMTEXT NULL,
            SourceEnvironment VARCHAR(20) NOT NULL DEFAULT \'\',
            SourceWSDL VARCHAR(255) NOT NULL DEFAULT \'\',
            FechaConsulta DATETIME NOT NULL,
            CreatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UpdatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (Id),
            KEY idx_empresa_env (EmpresaId, SourceEnvironment),
            KEY idx_rut_env (Rut, SourceEnvironment),
            KEY idx_has_cert (HasCertificate, CerStatus, DiasRestantes),
            KEY idx_fecha_consulta (FechaConsulta)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        $this->db->exec($sql);
    }
}
