<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Sincronizacion con tabla Empresas
|--------------------------------------------------------------------------
| Traduce el registro temporal del onboarding a la estructura real de la tabla
| `Empresas`, aplicando defaults heredados, credenciales Migrate y campos
| operativos que luego usa el resto del ERP.
*/

/**
 * Modelo responsable de crear y actualizar empresas definitivas.
 */
class EmpresaModel extends BaseModel
{
    private const TEMPLATE_EMPRESA_ID = 1175;
    private const MASTER_EMPRESA_ID = 397;
    private const TEMPLATE_FALLBACK = [
        'ImagenLogo' => '',
        'ImagenInformeODS' => '',
        'NombreFantasia' => '',
        'RazonSocial' => 'JV SOLUTIONS SAS ',
        'M' => '0',
        'Domicilio' => 'CONCORDIA 4386',
        'SitioWeb' => '',
        'Email' => 'ismaelyluciana.12@hotmail.com',
        'Habilitada' => 'SI',
        'Ciudad' => 'MONTEVIDEO',
        'Departamento' => 'MONTEVIDEO',
        'creada' => '1',
        'pServicio' => '0',
        'pNoResguardo' => '0',
        'pNoAbonados' => '0',
        'pGestionAbonados' => '0',
        'pNoVentas' => '1',
        'pNoStock' => '1',
        'pNoCompras' => '1',
        'pNoCajayBancos' => '1',
        'pCheques' => '0',
        'pAgenda' => '0',
        'pNoCrm' => '1',
        'pNoProduccion' => '1',
        'pNoOpticas' => '0',
        'pNoVeterinarias' => '1',
        'pImportaciones' => '0',
        'pGestionPedidosClientes' => '0',
        'pFactMasivaExcel' => '0',
        'pAsu' => '0',
        'pLec_Shopping' => '0',
        'pFacturador' => '0',
        'pFacturadorSoft' => '0',
        'pNotificaciones' => '0',
        'pTpvSoft' => '0',
        'pSupervisorTPV' => '0',
        'ClaveSupervisor' => '0',
        'pAvisarStock' => '0',
        'pBalanza' => '0',
        'pMediosDePago' => '0',
        'pSucursales' => '0',
        'pCodBarra' => 'NO',
        'pAgencia' => '0',
        'pCodEmpresa' => '',
        'pDescuentoItem' => '0',
        'pPedProvee' => '0',
        'pRecibosElect' => '0',
        'pContabilidad' => '0',
        'pMasDeUnTipoCae' => '0',
        'pDescuentoMaximo' => '0',
        'Usuarios' => '1',
        'PP' => '1',
        'Plan' => 'FACTURADOR 10 CFE',
        'cProceso' => '',
        'cSubProceso' => '',
        'cUsuarioEmailInv' => '',
        'cPassInv' => '',
        'cPrioridad' => '',
        'UltimoDiaDgi' => null,
        'FirmaDigital' => '',
        'EnvioUyP' => '0',
        'PreF' => '0',
        'Rut' => '220893030012',
        'UsuarioEF' => '42182898',
        'ClaveUsuarioEF' => 'Luciana1',
        'Licencia' => '3',
        'IdUsuarioAD' => '220893030012',
        'Notas' => '',
        'Tilde' => '0',
        'PostularEn' => null,
        'FechaIP' => '',
        'bd' => '0',
        'Notificar' => '20',
        'NotificarSuspension' => '20',
        'Suspension' => '30',
        'LiteralE' => '0',
        'Sms_Gratuitos' => '0',
        'Sms_Comprados' => '0',
        'EmailUsuario' => '',
        'EmailPass' => '',
        'EmailEmail' => '',
        'EmailPuerto' => '0',
        'EmailConexion' => '',
        'Servidor' => '',
        'Lec_Usuario' => '',
        'Lec_Pass' => '',
        'Coneccion' => '',
        'Shopify' => 'DESACTIVADO',
        'IdMedioPagoSistarbanc' => '0',
    ];

    public function findById(int $empresaId): ?array
    {
        $stmt = $this->db->prepare('SELECT IdEmpresa, RazonSocial, Rut, EmpresaInvoicy, Clave FROM Empresas WHERE IdEmpresa = ? LIMIT 1');
        $stmt->execute([$empresaId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function findByRut(string $rut): ?array
    {
        $stmt = $this->db->prepare('SELECT IdEmpresa, RazonSocial, Rut, EmpresaInvoicy, Clave FROM Empresas WHERE Rut = ? LIMIT 1');
        $stmt->execute([$rut]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function existsByRut(string $rut): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM Empresas WHERE Rut = ? LIMIT 1');
        $stmt->execute([$rut]);
        return (bool) $stmt->fetchColumn();
    }

    public function findByEmpresaInvoicy(string $empresaInvoicy): ?array
    {
        $stmt = $this->db->prepare('SELECT IdEmpresa, RazonSocial, Rut, EmpresaInvoicy, Clave FROM Empresas WHERE EmpresaInvoicy = ? LIMIT 1');
        $stmt->execute([$empresaInvoicy]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function createFromNuevaEmpresa(array $item): int
    {
        $columns = $this->listEmpresaColumns();
        $data = $this->buildEmpresaDataFromNuevaEmpresa($item, $columns);

        $insertData = [];
        foreach ($data as $field => $value) {
            if (in_array($field, $columns, true)) {
                $insertData[$field] = $value;
            }
        }

        $placeholders = [];
        $params = [];
        $index = 1;
        foreach ($insertData as $field => $value) {
            $placeholder = ':f' . $index++;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $value;
        }

        $sql = 'INSERT INTO Empresas (' . implode(', ', array_keys($insertData)) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $this->db->lastInsertId();
    }

    public function syncExistingFromNuevaEmpresa(int $empresaId, array $item): void
    {
        $columns = $this->listEmpresaColumns();
        $data = $this->buildEmpresaDataFromNuevaEmpresa($item, $columns);

        $updates = [];
        $params = [];
        $index = 1;

        foreach ($data as $field => $value) {
            if (!in_array($field, $columns, true)) {
                continue;
            }

            $placeholder = ':f' . $index++;
            $updates[] = $field . ' = ' . $placeholder;
            $params[$placeholder] = $value;
        }

        if ($updates === []) {
            return;
        }

        $params[':empresa_id'] = $empresaId;
        $sql = 'UPDATE Empresas SET ' . implode(', ', $updates) . ' WHERE IdEmpresa = :empresa_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function updateOperationalFields(int $empresaId, string $altaTipoEmpresa, string $altaTributario): void
    {
        $columns = $this->listEmpresaColumns();
        $updates = [];
        $params = [];

        if (in_array('AltaTipoEmpresa', $columns, true)) {
            $updates[] = 'AltaTipoEmpresa = :alta_tipoempresa';
            $params[':alta_tipoempresa'] = mb_substr(trim($altaTipoEmpresa), 0, 20);
        }

        if (in_array('AltaTributario', $columns, true)) {
            $updates[] = 'AltaTributario = :alta_tributario';
            $params[':alta_tributario'] = mb_substr(trim($altaTributario), 0, 30);
        }

        if ($updates === []) {
            return;
        }

        $params[':empresa_id'] = $empresaId;
        $sql = 'UPDATE Empresas SET ' . implode(', ', $updates) . ' WHERE IdEmpresa = :empresa_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    private function listEmpresaColumns(): array
    {
        $stmt = $this->db->query('SHOW COLUMNS FROM Empresas');
        $columns = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
            $field = (string) ($column['Field'] ?? '');
            if ($field !== '') {
                $columns[] = $field;
            }
        }

        return $columns;
    }

    private function loadTemplateEmpresaDefaults(array $columns): array
    {
        if ($columns === []) {
            return [];
        }

        $quotedColumns = array_map(static function (string $column): string {
            return '`' . str_replace('`', '``', $column) . '`';
        }, $columns);

        $sql = 'SELECT ' . implode(', ', $quotedColumns) . ' FROM Empresas WHERE IdEmpresa = ? LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([self::TEMPLATE_EMPRESA_ID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : self::TEMPLATE_FALLBACK;
    }

    private function buildEmpresaDataFromNuevaEmpresa(array $item, array $columns): array
    {
        $template = $this->loadTemplateEmpresaDefaults($columns);
        $data = $this->extractTemplateBaseDefaults($template);

        return array_merge($data, [
            'NombreFantasia' => trim((string) ($item['nombre_fantasia'] ?? '')),
            'RazonSocial' => (string) $item['razon_social'],
            'Domicilio' => mb_substr((string) $item['domicilio'], 0, 50),
            'Email' => mb_substr((string) $item['email_principal'], 0, 50),
            'Habilitada' => 'NO',
            'Usuarios' => (int) ($item['usuarios'] ?? 1) ?: 1,
            'Plan' => ($item['cfe_mensuales'] ?? '') !== '' ? (int) $item['cfe_mensuales'] : null,
            'cUsuarioEmailInv' => ($item['email_principal'] ?? '') !== '' ? (string) $item['email_principal'] : null,
            'cPassInv' => '',
            'Rut' => (string) $item['rut'],
            'UsuarioEF' => mb_substr((string) $item['usuario_ef'], 0, 20),
            'ClaveUsuarioEF' => mb_substr((string) $item['clave_usuario_ef'], 0, 40),
            'Licencia' => isset($item['licencia']) ? (int) $item['licencia'] : 0,
            'Notificar' => 20,
            'NotificarSuspension' => 20,
            'Suspension' => 30,
            'LiteralE' => strtoupper((string) ($item['alta_credito_fiscal'] ?? '')) === 'LITERAL E' ? 1 : 0,
            'AltaTipoEmpresa' => mb_substr(trim((string) ($item['alta_tipoempresa'] ?? '')), 0, 20),
            'AltaTributario' => mb_substr(trim((string) ($item['alta_tributario'] ?? '')), 0, 30),
            'Ciudad' => $this->resolveCatalogName('Ciudades', 'IdCiudad', 'Ciudad', (string) ($item['ciudad'] ?? '')),
            'Departamento' => $this->resolveCatalogName('Departamentos', 'IdDepartamento', 'Departamento', (string) ($item['departamento'] ?? '')),
            'pCodBarra' => 'NO',
            'FechaIP' => !empty($item['fecha_creacion']) ? date('Y-m-d', strtotime((string) $item['fecha_creacion'])) : date('Y-m-d'),
            'Notas' => trim((string) ($item['notas_admin'] ?? '')),
            'M' => 0,
            'creada' => 0,
            'Sms_Gratuitos' => 0,
        ]);
    }

    private function extractTemplateBaseDefaults(array $template): array
    {
        if ($template === []) {
            return [];
        }

        $defaults = [];
        foreach ($template as $field => $value) {
            if (in_array($field, ['IdEmpresa', 'EmpresaInvoicy', 'Clave'], true)) {
                continue;
            }
            $defaults[$field] = $value;
        }

        return $defaults;
    }

    private function resolveCatalogName(string $table, string $idField, string $nameField, string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            $stmt = $this->db->prepare(
                "SELECT {$nameField} AS nombre
                 FROM {$table}
                 WHERE {$idField} = ? AND IdEmpresa = ?
                 LIMIT 1"
            );
            $stmt->execute([(int) $value, self::MASTER_EMPRESA_ID]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false && trim((string) ($row['nombre'] ?? '')) !== '') {
                return mb_substr((string) $row['nombre'], 0, 50);
            }
        }

        return mb_substr($value, 0, 50);
    }

    public function updateMigrateCredentials(int $empresaId, string $empresaInvoicy, string $claveAcceso): void
    {
        $stmt = $this->db->query('SHOW COLUMNS FROM Empresas');
        $columns = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
            $columns[(string) ($column['Field'] ?? '')] = true;
        }

        $sets = [];
        $params = [];

        if (isset($columns['EmpresaInvoicy'])) {
            $sets[] = 'EmpresaInvoicy = ?';
            $params[] = $empresaInvoicy;
        }

        if (isset($columns['Clave'])) {
            $sets[] = 'Clave = ?';
            $params[] = $claveAcceso;
        }

        if ($sets === []) {
            return;
        }

        $params[] = $empresaId;
        $sql = 'UPDATE Empresas SET ' . implode(', ', $sets) . ' WHERE IdEmpresa = ?';
        $update = $this->db->prepare($sql);
        $update->execute($params);
    }

    public function listActiveCertificateCandidates(): array
    {
        $sql = "SELECT e.IdEmpresa,
                       e.RazonSocial,
                       e.Rut,
                       e.Habilitada,
                       e.EmpresaInvoicy,
                       e.Clave,
                       e.Email AS EmpresaEmail,
                       c.email AS ClienteEmail,
                       c.emailEnvioFE,
                       c.Tel,
                       c.NombreCompletoFirmante,
                       c.CI_Firmante,
                       COALESCE(NULLIF(TRIM(e.AltaTipoEmpresa), ''), ne.AltaTipoEmpresa) AS AltaTipoEmpresa,
                       COALESCE(NULLIF(TRIM(e.AltaTributario), ''), ne.AltaTributario) AS AltaTributario
                FROM Empresas e
                LEFT JOIN (
                    SELECT Documento,
                           MAX(email) AS email,
                           MAX(emailEnvioFE) AS emailEnvioFE,
                           MAX(Tel) AS Tel,
                           MAX(NombreCompletoFirmante) AS NombreCompletoFirmante,
                           MAX(CI_Firmante) AS CI_Firmante
                    FROM Clientes
                    WHERE IdEmpresa = ?
                    GROUP BY Documento
                ) c ON c.Documento = e.Rut
                LEFT JOIN (
                    SELECT ne1.Rut,
                           ne1.AltaTipoEmpresa,
                           ne1.AltaEspecial AS AltaTributario
                    FROM EmpresasNuevas ne1
                    INNER JOIN (
                        SELECT Rut, MAX(Id) AS MaxId
                        FROM EmpresasNuevas
                        WHERE COALESCE(EmpresasNuevas.Rut, '') <> ''
                        GROUP BY Rut
                    ) ne2 ON ne2.MaxId = ne1.Id
                ) ne ON ne.Rut = e.Rut
                WHERE Habilitada = 'SI'
                  AND COALESCE(e.Rut, '') <> ''
                  AND COALESCE(EmpresaInvoicy, '') <> ''
                  AND COALESCE(EmpresaInvoicy, '0') <> '0'
                  AND COALESCE(Clave, '') <> ''
                  AND IdEmpresa NOT IN (?, ?)
                ORDER BY RazonSocial ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([self::MASTER_EMPRESA_ID, self::MASTER_EMPRESA_ID, self::TEMPLATE_EMPRESA_ID]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findActiveCertificateCandidate(int $empresaId = 0, string $rut = ''): ?array
    {
        $rut = preg_replace('/\D+/', '', $rut);
        foreach ($this->listActiveCertificateCandidates() as $row) {
            $rowEmpresaId = (int) ($row['IdEmpresa'] ?? 0);
            $rowRut = preg_replace('/\D+/', '', (string) ($row['Rut'] ?? ''));

            if ($empresaId > 0 && $rowEmpresaId === $empresaId) {
                return $row;
            }

            if ($rut !== '' && $rowRut === $rut) {
                return $row;
            }
        }

        return null;
    }
}
