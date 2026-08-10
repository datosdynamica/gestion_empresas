<?php

declare(strict_types=1);

// Crea o sincroniza la empresa definitiva tomando como base los defaults del
// registro plantilla y los datos capturados en onboarding.
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
        // Se inserta solo sobre columnas reales de la tabla actual para que el
        // mismo codigo soporte ambientes con ligeras diferencias de esquema.
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
        // Reusa la misma transformacion del alta nueva, pero aplicada como update
        // sobre una empresa ya existente.
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
        // Estos dos campos se ajustan desde el panel de certificados para casos
        // historicos que no nacieron en la tabla temporal nueva.
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
        // Se consulta el esquema real para no asumir campos que pueden faltar en
        // algun ambiente clonado o viejo.
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

    public function updateMigrateCredentials(
        int $empresaId,
        string $empresaInvoicy,
        string $claveAcceso,
        ?string $usuarioMigrateEmail = null,
        ?string $usuarioMigratePassword = null
    ): void
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

        if ($usuarioMigrateEmail !== null && isset($columns['cUsuarioEmailInv'])) {
            $sets[] = 'cUsuarioEmailInv = ?';
            $params[] = $usuarioMigrateEmail;
        }

        if ($usuarioMigratePassword !== null && isset($columns['cPassInv'])) {
            $sets[] = 'cPassInv = ?';
            $params[] = $usuarioMigratePassword;
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

    public function countClientPanelItems(
        string $search = '',
        string $statusFilter = 'habilitadas',
        string $habFilter = '',
        string $licenseFilter = '',
        string $usersFilter = '',
        string $certificateFilter = '',
        string $debtNotificationFilter = '',
        string $suspensionNotificationFilter = '',
        string $suspensionFilter = ''
    ): int
    {
        $params = [];
        $where = $this->buildClientPanelSearchWhere(
            $search,
            $statusFilter,
            $habFilter,
            $licenseFilter,
            $usersFilter,
            $certificateFilter,
            $debtNotificationFilter,
            $suspensionNotificationFilter,
            $suspensionFilter,
            $params
        );
        $sql = 'SELECT COUNT(*) AS total FROM (' . $this->clientPanelBaseSql() . ') base ' . $where;

        $row = $this->fetchOne($sql, $params);
        return (int) ($row['total'] ?? 0);
    }

    public function listClientPanelItems(
        string $search = '',
        string $statusFilter = 'habilitadas',
        string $sortBy = 'idempresa',
        string $sortDirection = 'desc',
        string $habFilter = '',
        string $licenseFilter = '',
        string $usersFilter = '',
        string $certificateFilter = '',
        string $debtNotificationFilter = '',
        string $suspensionNotificationFilter = '',
        string $suspensionFilter = ''
    ): array
    {
        $params = [];
        $where = $this->buildClientPanelSearchWhere(
            $search,
            $statusFilter,
            $habFilter,
            $licenseFilter,
            $usersFilter,
            $certificateFilter,
            $debtNotificationFilter,
            $suspensionNotificationFilter,
            $suspensionFilter,
            $params
        );
        $orderBy = $this->buildClientPanelOrderBy($sortBy, $sortDirection);

        $sql = 'SELECT *
                FROM (' . $this->clientPanelBaseSql() . ') base '
                . $where
                . ' ORDER BY ' . $orderBy;

        return $this->fetchAll($sql, $params);
    }

    public function listClientPanelPage(
        int $limit,
        int $offset,
        string $search = '',
        string $statusFilter = 'habilitadas',
        string $sortBy = 'idempresa',
        string $sortDirection = 'desc',
        string $habFilter = '',
        string $licenseFilter = '',
        string $usersFilter = '',
        string $certificateFilter = '',
        string $debtNotificationFilter = '',
        string $suspensionNotificationFilter = '',
        string $suspensionFilter = ''
    ): array
    {
        $limit = max(1, $limit);
        $offset = max(0, $offset);
        $params = [];
        $where = $this->buildClientPanelSearchWhere(
            $search,
            $statusFilter,
            $habFilter,
            $licenseFilter,
            $usersFilter,
            $certificateFilter,
            $debtNotificationFilter,
            $suspensionNotificationFilter,
            $suspensionFilter,
            $params
        );
        $orderBy = $this->buildClientPanelOrderBy($sortBy, $sortDirection);

        $sql = 'SELECT *
                FROM (' . $this->clientPanelBaseSql() . ') base '
                . $where
                . ' ORDER BY ' . $orderBy . '
                    LIMIT ' . $limit . ' OFFSET ' . $offset;

        return $this->fetchAll($sql, $params);
    }

    public function findClientPanelItemByEmpresaId(int $empresaId): ?array
    {
        return $this->fetchOne(
            'SELECT *
             FROM (' . $this->clientPanelBaseSql() . ') base
             WHERE base.IdEmpresa = :empresa_id
             LIMIT 1',
            [':empresa_id' => $empresaId]
        );
    }

    public function updateClientPanelData(int $empresaId, array $data): void
    {
        $columns = $this->listEmpresaColumns();
        $updates = [];
        $params = [':empresa_id' => $empresaId];

        $map = [
            'Rut' => 'rut',
            'RazonSocial' => 'razon_social',
            'NombreFantasia' => 'nombre_fantasia',
            'Domicilio' => 'domicilio',
            'Email' => 'email_principal',
            'cUsuarioEmailInv' => 'email_envio_fe',
            'SitioWeb' => 'sitio_web',
            'Licencia' => 'licencia_codigo',
            'Habilitada' => 'habilitada',
            'Ciudad' => 'ciudad_nombre',
            'Departamento' => 'departamento_nombre',
            'UsuarioEF' => 'usuario_ef',
            'ClaveUsuarioEF' => 'clave_usuario_ef',
            'IdUsuarioAD' => 'id_usuario_ad',
            'FechaIP' => 'fecha_ip',
            'AltaTipoEmpresa' => 'alta_tipoempresa',
            'AltaTributario' => 'alta_tributario',
            'LiteralE' => 'literal_e',
            'Notificar' => 'notificar_deuda',
            'NotificarSuspension' => 'notificar_suspension',
            'Suspension' => 'suspension_dias',
            'Notas' => 'notas_admin',
            'pNoVentas' => 'module_ventas',
            'pNoCompras' => 'module_compras',
            'pNoStock' => 'module_stock',
            'pNoCajayBancos' => 'module_caja_bancos',
            'pNoCrm' => 'module_crm',
            'pNoProduccion' => 'module_produccion',
            'pTpvSoft' => 'module_tpv',
            'pNoVeterinarias' => 'module_veterinarias',
            'pNoResguardo' => 'module_quitar_resguardos',
            'pNoAbonados' => 'module_abonados',
            'pImportaciones' => 'module_importaciones',
            'pGestionPedidosClientes' => 'module_pedidos_clientes',
            'pFactMasivaExcel' => 'module_fact_masiva_excel',
            'pLec_Shopping' => 'module_shopping',
            'pFacturador' => 'module_facturador',
            'pNotificaciones' => 'module_notificaciones',
            'pSupervisorTPV' => 'module_supervisor_tpv',
            'pMediosDePago' => 'module_medios_pago',
            'pPedProvee' => 'module_pedidos_proveedores',
            'pAgencia' => 'module_agencia',
            'pContabilidad' => 'module_contabilidad',
            'pBalanza' => 'module_balanza',
            'pMasDeUnTipoCae' => 'module_mas_de_un_cae',
            'pAsu' => 'module_asu',
            'pSucursales' => 'module_sucursales',
        ];

        foreach ($map as $column => $inputKey) {
            if (!in_array($column, $columns, true)) {
                continue;
            }

            $placeholder = ':' . $inputKey;
            $updates[] = $column . ' = ' . $placeholder;
            $params[$placeholder] = $data[$inputKey] ?? null;
        }

        if ($updates === []) {
            return;
        }

        $sql = 'UPDATE Empresas SET ' . implode(', ', $updates) . ' WHERE IdEmpresa = :empresa_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function updateClientPanelLogo(int $empresaId, string $binary): void
    {
        $columns = $this->listEmpresaColumns();
        if (!in_array('ImagenLogo', $columns, true)) {
            return;
        }

        $stmt = $this->db->prepare('UPDATE Empresas SET ImagenLogo = :logo WHERE IdEmpresa = :empresa_id');
        $stmt->bindValue(':logo', $binary, PDO::PARAM_LOB);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();
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

    private function clientPanelBaseSql(): string
    {
        return "SELECT e.IdEmpresa,
                       e.ImagenLogo,
                       e.RazonSocial,
                       e.NombreFantasia,
                       e.Rut,
                       e.Habilitada,
                       e.Domicilio,
                       e.Ciudad,
                       e.Departamento,
                       e.SitioWeb,
                       e.Email AS EmpresaEmail,
                       e.EmpresaInvoicy,
                       e.Clave,
                       e.cUsuarioEmailInv,
                       e.cPassInv,
                       e.UsuarioEF,
                       e.ClaveUsuarioEF,
                       e.Notas,
                       e.FechaIP,
                       e.LiteralE,
                       e.Licencia AS LicenciaCodigo,
                       e.Usuarios AS UsuariosLicencia,
                       e.Plan,
                       e.Notificar,
                       e.NotificarSuspension,
                       e.Suspension,
                       e.pNoVentas,
                       e.pNoCompras,
                       e.pNoStock,
                       e.pNoCajayBancos,
                       e.pNoCrm,
                       e.pNoProduccion,
                       e.pTpvSoft,
                       e.pNoVeterinarias,
                       e.pNoResguardo,
                       e.pNoAbonados,
                       e.pImportaciones,
                       e.pGestionPedidosClientes,
                       e.pFactMasivaExcel,
                       e.pLec_Shopping,
                       e.pFacturador,
                       e.pNotificaciones,
                       e.pSupervisorTPV,
                       e.pMediosDePago,
                       e.pPedProvee,
                       e.pAgencia,
                       e.pContabilidad,
                       e.pBalanza,
                       e.pMasDeUnTipoCae,
                       e.pAsu,
                       e.pSucursales,
                       e.AltaTipoEmpresa,
                       e.AltaTributario,
                       c.IdCliente,
                       c.email AS ClienteEmail,
                       c.emailEnvioFE,
                       c.Tel,
                       c.IdGiro AS ClienteIdGiro,
                       c.IdCiudad AS ClienteIdCiudad,
                       c.IdVendedor AS ClienteIdVendedor,
                       c.IdFidelizacion AS ClienteIdFidelizacion,
                       c.idFormapago AS ClienteIdFormaPago,
                       c.abonado_IdProducto AS ClienteAbonadoIdProducto,
                       c.abonado_Importe AS ClienteAbonadoImporte,
                       c.abonado_TV AS ClienteAbonadoTV,
                       c.abonado_Moneda AS ClienteAbonadoMoneda,
                       c.abonado_periodo AS ClienteAbonadoPeriodo,
                       c.abonado_Grupo AS ClienteAbonadoGrupo,
                       c.abonado_Descuento AS ClienteAbonadoDescuento,
                       c.abonado_IdMedioPago AS ClienteIdMedioPago,
                       c.pnCreditoFiscal AS ClientePnCreditoFiscal,
                       c.pnMonto AS ClientePnMonto,
                       c.NombreCompletoFirmante,
                       c.CI_Firmante,
                       ne.Id AS OnboardingId,
                       ne.Estado AS OnboardingEstado,
                       ne.HitoActual AS OnboardingHito,
                       ne.FechaCreacion AS OnboardingFechaCreacion,
                       ne.Licencia AS OnboardingLicencia,
                       ne.SucCodSucursal AS OnboardingSucCodSucursal,
                       ne.AltaEspecialNorma AS OnboardingAltaExoneradoNorma,
                       ne.ClienteIdGiro AS OnboardingClienteIdGiro,
                       ne.ClienteIdVendedor AS OnboardingClienteIdVendedor
                FROM Empresas e
                LEFT JOIN (
                    SELECT Documento,
                           MAX(IdCliente) AS IdCliente,
                           MAX(email) AS email,
                           MAX(emailEnvioFE) AS emailEnvioFE,
                           MAX(Tel) AS Tel,
                           MAX(IdGiro) AS IdGiro,
                           MAX(IdCiudad) AS IdCiudad,
                           MAX(IdVendedor) AS IdVendedor,
                           MAX(IdFidelizacion) AS IdFidelizacion,
                           MAX(idFormapago) AS idFormapago,
                           MAX(abonado_IdProducto) AS abonado_IdProducto,
                           MAX(abonado_Importe) AS abonado_Importe,
                           MAX(abonado_TV) AS abonado_TV,
                           MAX(abonado_Moneda) AS abonado_Moneda,
                           MAX(abonado_periodo) AS abonado_periodo,
                           MAX(abonado_Grupo) AS abonado_Grupo,
                           MAX(abonado_Descuento) AS abonado_Descuento,
                           MAX(abonado_IdMedioPago) AS abonado_IdMedioPago,
                           MAX(pnCreditoFiscal) AS pnCreditoFiscal,
                           MAX(pnMonto) AS pnMonto,
                           MAX(NombreCompletoFirmante) AS NombreCompletoFirmante,
                           MAX(CI_Firmante) AS CI_Firmante
                    FROM Clientes
                    WHERE IdEmpresa = " . self::MASTER_EMPRESA_ID . "
                    GROUP BY Documento
                ) c ON c.Documento = e.Rut
                LEFT JOIN (
                    SELECT ne1.Id,
                           ne1.Rut,
                           ne1.Estado,
                           ne1.HitoActual,
                           ne1.FechaCreacion,
                           ne1.Licencia,
                           ne1.SucCodSucursal,
                           ne1.AltaEspecialNorma,
                           ne1.ClienteIdGiro,
                           ne1.ClienteIdVendedor
                    FROM EmpresasNuevas ne1
                    INNER JOIN (
                        SELECT Rut, MAX(Id) AS MaxId
                        FROM EmpresasNuevas
                        WHERE COALESCE(Rut, '') <> ''
                        GROUP BY Rut
                    ) ne2 ON ne2.MaxId = ne1.Id
                ) ne ON ne.Rut = e.Rut
                WHERE COALESCE(e.Rut, '') <> ''
                  AND e.IdEmpresa NOT IN (" . self::MASTER_EMPRESA_ID . ', ' . self::TEMPLATE_EMPRESA_ID . ')';
    }

    private function buildClientPanelSearchWhere(
        string $search,
        string $statusFilter,
        string $habFilter,
        string $licenseFilter,
        string $usersFilter,
        string $certificateFilter,
        string $debtNotificationFilter,
        string $suspensionNotificationFilter,
        string $suspensionFilter,
        array &$params
    ): string
    {
        $clauses = [];

        $statusFilter = strtolower(trim($statusFilter));
        if ($statusFilter === 'habilitadas') {
            $clauses[] = "UPPER(COALESCE(base.Habilitada, '')) IN ('SI', 'S', '1')";
        } elseif ($statusFilter === 'no_habilitadas') {
            $clauses[] = "UPPER(COALESCE(base.Habilitada, '')) NOT IN ('SI', 'S', '1')";
        }

        $search = trim($search);
        if ($search !== '') {
            $params[':search'] = '%' . $search . '%';
            $clauses[] = "(
                COALESCE(base.RazonSocial, '') LIKE :search
                OR COALESCE(base.NombreFantasia, '') LIKE :search
                OR COALESCE(base.Rut, '') LIKE :search
                OR COALESCE(base.ClienteEmail, '') LIKE :search
                OR COALESCE(base.EmpresaEmail, '') LIKE :search
            )";
        }

        $habFilter = strtolower(trim($habFilter));
        if ($habFilter === 'baja_logica') {
            $clauses[] = "UPPER(COALESCE(base.Habilitada, '')) LIKE 'NO%'";
        } elseif ($habFilter === 'en_certificacion') {
            $clauses[] = "UPPER(COALESCE(base.Habilitada, '')) LIKE '%CERTIFIC%'";
        } elseif ($habFilter === 'si') {
            $clauses[] = "UPPER(COALESCE(base.Habilitada, '')) IN ('SI', 'S', '1')";
        } elseif ($habFilter === 'suspendida') {
            $clauses[] = "UPPER(COALESCE(base.Habilitada, '')) LIKE 'SUSPEND%'";
        }

        $licenseFilter = trim($licenseFilter);
        if ($licenseFilter !== '') {
            $params[':license_filter'] = $licenseFilter;
            $clauses[] = 'CAST(COALESCE(base.LicenciaCodigo, 0) AS CHAR) = :license_filter';
        }

        $usersFilter = strtolower(trim($usersFilter));
        if ($usersFilter === '0') {
            $clauses[] = 'COALESCE(base.UsuariosLicencia, 0) = 0';
        } elseif ($usersFilter === '1') {
            $clauses[] = 'COALESCE(base.UsuariosLicencia, 0) = 1';
        } elseif ($usersFilter === '2_5') {
            $clauses[] = 'COALESCE(base.UsuariosLicencia, 0) BETWEEN 2 AND 5';
        } elseif ($usersFilter === '6_10') {
            $clauses[] = 'COALESCE(base.UsuariosLicencia, 0) BETWEEN 6 AND 10';
        } elseif ($usersFilter === '11_plus') {
            $clauses[] = 'COALESCE(base.UsuariosLicencia, 0) >= 11';
        }

        $certificateFilter = strtolower(trim($certificateFilter));
        if ($certificateFilter === 'con_empcodigo') {
            $clauses[] = "COALESCE(base.EmpresaInvoicy, '') <> '' AND COALESCE(base.EmpresaInvoicy, '0') <> '0'";
        } elseif ($certificateFilter === 'sin_empcodigo') {
            $clauses[] = "(COALESCE(base.EmpresaInvoicy, '') = '' OR COALESCE(base.EmpresaInvoicy, '0') = '0')";
        } elseif ($certificateFilter === 'con_cliente') {
            $clauses[] = 'COALESCE(base.IdCliente, 0) > 0';
        } elseif ($certificateFilter === 'sin_cliente') {
            $clauses[] = 'COALESCE(base.IdCliente, 0) = 0';
        }

        $debtNotificationFilter = trim($debtNotificationFilter);
        if ($debtNotificationFilter !== '') {
            $params[':debt_notification_filter'] = $debtNotificationFilter;
            $clauses[] = 'CAST(COALESCE(base.Notificar, 0) AS CHAR) = :debt_notification_filter';
        }

        $suspensionNotificationFilter = trim($suspensionNotificationFilter);
        if ($suspensionNotificationFilter !== '') {
            $params[':suspension_notification_filter'] = $suspensionNotificationFilter;
            $clauses[] = 'CAST(COALESCE(base.NotificarSuspension, 0) AS CHAR) = :suspension_notification_filter';
        }

        $suspensionFilter = trim($suspensionFilter);
        if ($suspensionFilter !== '') {
            $params[':suspension_filter'] = $suspensionFilter;
            $clauses[] = 'CAST(COALESCE(base.Suspension, 0) AS CHAR) = :suspension_filter';
        }

        if ($clauses === []) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $clauses);
    }

    private function buildClientPanelOrderBy(string $sortBy, string $sortDirection): string
    {
        $sortBy = strtolower(trim($sortBy));
        $sortDirection = strtolower(trim($sortDirection)) === 'asc' ? 'ASC' : 'DESC';

        $sortableColumns = [
            'idempresa' => 'base.IdEmpresa',
            'razonsocial' => 'base.RazonSocial',
            'rut' => 'base.Rut',
            'habilitada' => 'base.Habilitada',
        ];

        $column = $sortableColumns[$sortBy] ?? 'base.IdEmpresa';
        $secondaryDirection = $sortDirection === 'ASC' ? 'ASC' : 'DESC';

        if ($column === 'base.Habilitada') {
            return "base.Habilitada {$sortDirection}, base.IdEmpresa DESC";
        }

        return $column . ' ' . $sortDirection . ', base.IdEmpresa ' . $secondaryDirection;
    }
}
