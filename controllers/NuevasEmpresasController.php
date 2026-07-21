<?php

declare(strict_types=1);

class NuevasEmpresasController
{
    private $model;
    private $archivoModel;
    private $historialModel;
    private $catalogoModel;
    private $migrateService;
    private $certificateCacheModel;
    private $certificateActionModel;
    private $localModel;
    private $secUserModel;
    private $provisioningModel;
    private $onboardingMailer;
    private $onboardingInvoiceService;
    private $hitoAutoModel;

    public function __construct()
    {
        $this->model = new NuevaEmpresaModel();
        $this->archivoModel = new NuevaEmpresaArchivoModel();
        $this->historialModel = new NuevaEmpresaHistorialModel();
        $this->catalogoModel = new CatalogoReferenciaModel();
        $this->migrateService = new MigrateInvoicyService();
        $this->certificateCacheModel = new MigrateCertificateCacheModel();
        $this->certificateActionModel = new CertificateActionModel();
        $this->localModel = new LocalModel();
        $this->secUserModel = new SecUserModel();
        $this->provisioningModel = new EmpresaProvisioningModel();
        $this->onboardingMailer = new OnboardingMailer();
        $this->onboardingInvoiceService = new OnboardingInvoiceService();
        $this->hitoAutoModel = new EmpresaNuevaHitoAutoModel();
        $this->hitoAutoModel->ensureTable();
    }

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $totalItems = $this->model->countAll();
        $totalPages = max(1, (int) ceil($totalItems / $perPage));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        $items = $this->model->listPage($perPage, $offset);
        $itemIds = array_values(array_filter(array_map(static function (array $item): int {
            return (int) ($item['id'] ?? 0);
        }, $items)));
        $formOptions = $this->loadFormOptions();
        $items = $this->hydrateReferenceLabelsForItems($items, $formOptions);
        $deferredTasks = $this->hitoAutoModel->findLatestCredentialsTasksByNuevaEmpresaIds($itemIds);
        $workflowHistory = $this->historialModel->listWorkflowEventsByNuevaEmpresaIds($itemIds);
        $fileMetaByNuevaEmpresa = [];
        foreach ($itemIds as $itemId) {
            foreach ($this->archivoModel->listByNuevaEmpresaId($itemId) as $archivo) {
                $tipoArchivo = (string) ($archivo['tipo_archivo'] ?? '');
                if ($tipoArchivo === '' || isset($fileMetaByNuevaEmpresa[$itemId][$tipoArchivo])) {
                    continue;
                }

                $fileMetaByNuevaEmpresa[$itemId][$tipoArchivo] = [
                    'id' => (int) ($archivo['id'] ?? 0),
                    'name' => (string) ($archivo['nombre_original'] ?? ''),
                    'download_url' => app_url('index.php?action=download-file&id=' . (int) ($archivo['id'] ?? 0)),
                ];
            }
        }
        $pagination = [
            'page' => $page,
            'per_page' => $perPage,
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
            'prev_page' => $page > 1 ? $page - 1 : 1,
            'next_page' => $page < $totalPages ? $page + 1 : $totalPages,
        ];
        $pageTitle = 'Altas y Automatizaciones';
        require __DIR__ . '/../views/nuevas_empresas/list.php';
    }

    public function create(): void
    {
        $pageTitle = 'Altas y Automatizaciones';
        $formOptions = $this->loadFormOptions();
        require __DIR__ . '/../views/nuevas_empresas/form.php';
    }

    public function trace(): void
    {
        $events = $this->historialModel->listRecent(120);
        $pageTitle = 'Trazabilidad';
        require __DIR__ . '/../views/nuevas_empresas/trace.php';
    }

    public function settings(): void
    {
        $pageTitle = 'Configuracion';
        $empresaModel = new EmpresaModel();
        $referenceCompanies = [
            'testing' => $this->resolveMigrateReferenceCompany($empresaModel, 'testing'),
            'production' => $this->resolveMigrateReferenceCompany($empresaModel, 'production'),
        ];
        $activeReferenceCompany = $referenceCompanies[(string) MIGRATE_ENVIRONMENT] ?? $referenceCompanies['production'];
        $settings = [
            'monto_credito_fiscal_anual' => (float) MONTO_CREDITO_FISCAL_ANUAL,
            'migrate_environment' => (string) MIGRATE_ENVIRONMENT,
            'migrate_partner_code' => (int) MIGRATE_PARTNER_CODE,
            'migrate_partner_key' => (string) MIGRATE_PARTNER_KEY,
            'migrate_cert_emp_codigo' => (int) MIGRATE_CERT_EMP_CODIGO,
            'migrate_cert_public_key' => (string) MIGRATE_CERT_PUBLIC_KEY,
            'master_empresa_id' => (int) ($activeReferenceCompany['id'] ?? ID_EMPRESA_MASTER),
            'master_empresa_invoicy' => (string) ($activeReferenceCompany['empresa_invoicy'] ?? ''),
            'master_empresa_clave' => (string) ($activeReferenceCompany['clave'] ?? ''),
            'reference_companies' => $referenceCompanies,
            'migrate_registroempresa_wsdl' => (string) MIGRATE_REGISTROEMPRESA_WSDL,
            'migrate_consultaempresas_wsdl' => (string) MIGRATE_CONSULTAEMPRESAS_WSDL,
            'runtime_file' => BASE_PATH . '/config/runtime.php',
            'runtime_writable' => is_writable(BASE_PATH . '/config') || is_writable(BASE_PATH . '/config/runtime.php'),
        ];

        require __DIR__ . '/../views/settings/index.php';
    }

    public function certificates(): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        $pageTitle = 'Certificados Migrate';
        $filters = [
            'filter_mode' => 'empresas_activas',
            'cer_status' => 'A',
            'cer_intervalo' => '30',
            'emp_ruc' => '',
        ];
        $result = null;
        $empresaModel = new EmpresaModel();
        $uploadCompanies = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filters = [
                'filter_mode' => trim((string) ($_POST['filter_mode'] ?? 'empresas_activas')),
                'cer_status' => strtoupper(trim((string) ($_POST['cer_status'] ?? 'A'))),
                'cer_intervalo' => trim((string) ($_POST['cer_intervalo'] ?? '30')),
                'emp_ruc' => preg_replace('/\D+/', '', (string) ($_POST['emp_ruc'] ?? '')),
            ];

            try {
                if (($filters['filter_mode'] ?? 'empresas_activas') === 'rut') {
                    $companyCredentials = $this->resolveCertificateServiceCredentials($empresaModel);
                    $query = [
                        'emp_codigo' => $companyCredentials['emp_codigo'],
                        'hash_key' => $companyCredentials['hash_key'],
                        'emp_pk' => $companyCredentials['emp_pk'],
                        'cer_status' => $filters['cer_status'],
                        'cer_intervalo' => 0,
                        'emp_ruc' => $filters['emp_ruc'],
                    ];
                    $filters['cer_intervalo'] = '0';
                    $singleResult = $this->migrateService->queryCertificates($query);
                    $singleTechEntries = [[
                        'empresa_id' => 0,
                        'razon_social' => '',
                        'rut' => $filters['emp_ruc'],
                        'success' => (bool) ($singleResult['success'] ?? false),
                        'msg_code' => (string) ($singleResult['msg_code'] ?? ''),
                        'msg_desc' => (string) ($singleResult['msg_desc'] ?? ''),
                        'error_summary' => implode(' | ', (array) ($singleResult['errors'] ?? [])),
                        'request_xml' => (string) ($singleResult['request_xml'] ?? ''),
                        'response_xml' => (string) ($singleResult['response_xml'] ?? ''),
                        'items_count' => count($singleResult['items'] ?? []),
                        'fecha_consulta' => date('Y-m-d H:i:s'),
                    ]];

                    $activeCompanies = $empresaModel->listActiveCertificateCandidates();
                    $companyByRut = [];
                    foreach ($activeCompanies as $company) {
                        $normalizedRut = preg_replace('/\D+/', '', (string) ($company['Rut'] ?? ''));
                        if ($normalizedRut === '') {
                            continue;
                        }
                        $companyByRut[$normalizedRut] = [
                            'empresa_id' => (int) ($company['IdEmpresa'] ?? 0),
                            'rut' => $normalizedRut,
                            'razon_social' => (string) ($company['RazonSocial'] ?? ''),
                            'empresa_invoicy' => (string) ($company['EmpresaInvoicy'] ?? ''),
                            'clave' => (string) ($company['Clave'] ?? ''),
                            'email' => trim((string) (($company['ClienteEmail'] ?? '') !== '' ? $company['ClienteEmail'] : ($company['EmpresaEmail'] ?? ''))),
                            'email_envio_fe' => trim((string) ($company['emailEnvioFE'] ?? '')),
                            'telefono' => trim((string) ($company['Tel'] ?? '')),
                            'alta_tipoempresa' => trim((string) ($company['AltaTipoEmpresa'] ?? '')),
                            'alta_tributario' => trim((string) ($company['AltaTributario'] ?? '')),
                            'nombre_completo_firmante' => trim((string) ($company['NombreCompletoFirmante'] ?? '')),
                            'ci_firmante' => trim((string) ($company['CI_Firmante'] ?? '')),
                        ];
                    }

                    $normalizedSingleRut = preg_replace('/\D+/', '', (string) ($filters['emp_ruc'] ?? ''));
                    if ($normalizedSingleRut !== '' && isset($companyByRut[$normalizedSingleRut])) {
                        $companyCredentials = $this->resolveCertificateCredentialsForCompany(
                            $companyByRut[$normalizedSingleRut],
                            $companyCredentials
                        );
                        $query['emp_codigo'] = $companyCredentials['emp_codigo'];
                        $query['hash_key'] = $companyCredentials['hash_key'];
                        $query['emp_pk'] = $companyCredentials['emp_pk'];
                        $singleResult = $this->migrateService->queryCertificates($query);
                    }

                    if ($normalizedSingleRut !== '' && isset($companyByRut[$normalizedSingleRut])) {
                        $this->certificateCacheModel->replaceCompanySnapshot(
                            $companyByRut[$normalizedSingleRut],
                            (array) ($singleResult['items'] ?? []),
                            [
                                'environment' => (string) ($singleResult['environment'] ?? MIGRATE_ENVIRONMENT),
                                'wsdl' => (string) ($singleResult['wsdl'] ?? MIGRATE_CONSULTAEMPRESAS_WSDL),
                                'msg_code' => (string) ($singleResult['msg_code'] ?? ''),
                                'msg_desc' => (string) ($singleResult['msg_desc'] ?? ''),
                                'error_summary' => implode(' | ', (array) ($singleResult['errors'] ?? [])),
                                'request_xml' => (string) ($singleResult['request_xml'] ?? ''),
                                'response_xml' => (string) ($singleResult['response_xml'] ?? ''),
                                'fecha_consulta' => date('Y-m-d H:i:s'),
                            ]
                        );
                    }

                    $singleResult['effective_emp_codigo'] = $companyCredentials['emp_codigo'];
                    $singleResult['filter_mode'] = $filters['filter_mode'];
                    $singleResult['items_total_source'] = count($singleResult['items'] ?? []);
                    $singleResult['candidate_count'] = 1;
                    $singleResult['processed_count'] = 1;
                    $singleHandled = !empty($singleResult['success']) || $this->isCertificateNoDataResult($singleResult);
                    $singleResult['success_count'] = $singleHandled ? 1 : 0;
                    $singleResult['error_count'] = $singleHandled ? 0 : 1;
                    $singleResult['technical_entries'] = $singleTechEntries;
                    $singleResult['cache_hits'] = 0;
                    $singleResult['cache_refresh_count'] = 1;
                    $singleResult['cache_pending_count'] = 0;
                    $singleItems = [];
                    foreach ((array) ($singleResult['items'] ?? []) as $certItem) {
                        $singleCompany = $normalizedSingleRut !== '' ? ($companyByRut[$normalizedSingleRut] ?? []) : [];
                        $certItem['empresa_id'] = 0;
                        $certItem['razon_social'] = (string) ($singleCompany['razon_social'] ?? '');
                        $certItem['empresa_invoicy'] = (string) ($singleCompany['empresa_invoicy'] ?? '');
                        $certItem['email'] = (string) ($singleCompany['email'] ?? '');
                        $certItem['email_envio_fe'] = (string) ($singleCompany['email_envio_fe'] ?? '');
                        $certItem['telefono'] = (string) ($singleCompany['telefono'] ?? '');
                        $certItem['alta_tipoempresa'] = (string) ($singleCompany['alta_tipoempresa'] ?? '');
                        $certItem['alta_tributario'] = (string) ($singleCompany['alta_tributario'] ?? '');
                        $certItem['nombre_completo_firmante'] = (string) ($singleCompany['nombre_completo_firmante'] ?? '');
                        $certItem['ci_firmante'] = (string) ($singleCompany['ci_firmante'] ?? '');
                        $certItem['tech_index'] = 0;
                        $singleItems[] = $certItem;
                    }
                    $singleResult['items'] = $singleItems;
                    $result = $singleResult;
                } else {
                    $activeCompanies = $empresaModel->listActiveCertificateCandidates();
                    $companyMap = [];
                    $rutMap = [];
                    foreach ($activeCompanies as $company) {
                        $normalizedRut = preg_replace('/\D+/', '', (string) ($company['Rut'] ?? ''));
                        if ($normalizedRut === '') {
                            continue;
                        }
                        $companyData = [
                            'empresa_id' => (int) ($company['IdEmpresa'] ?? 0),
                            'rut' => $normalizedRut,
                            'razon_social' => (string) ($company['RazonSocial'] ?? ''),
                            'empresa_invoicy' => (string) ($company['EmpresaInvoicy'] ?? ''),
                            'clave' => (string) ($company['Clave'] ?? ''),
                            'email' => trim((string) (($company['ClienteEmail'] ?? '') !== '' ? $company['ClienteEmail'] : ($company['EmpresaEmail'] ?? ''))),
                            'email_envio_fe' => trim((string) ($company['emailEnvioFE'] ?? '')),
                            'telefono' => trim((string) ($company['Tel'] ?? '')),
                            'alta_tipoempresa' => trim((string) ($company['AltaTipoEmpresa'] ?? '')),
                            'alta_tributario' => trim((string) ($company['AltaTributario'] ?? '')),
                            'nombre_completo_firmante' => trim((string) ($company['NombreCompletoFirmante'] ?? '')),
                            'ci_firmante' => trim((string) ($company['CI_Firmante'] ?? '')),
                        ];
                        $companyMap[$companyData['empresa_id']] = $companyData;
                        $rutMap[$normalizedRut] = $companyData;
                    }

                    if ($rutMap === []) {
                        throw new RuntimeException('No hay empresas activas con RUT disponible para consultar certificados.');
                    }

                    $actionHistoryByEmpresa = $this->certificateActionModel->listByEmpresaIds(array_keys($companyMap));

                    $intervalLimit = max(0, (int) $filters['cer_intervalo']);
                    $companyIds = array_keys($companyMap);
                    $statusMap = $this->certificateCacheModel->listStatusMap($companyIds, (string) MIGRATE_ENVIRONMENT);
                    $staleCompanies = [];
                    $freshCutoff = (new DateTimeImmutable('now'))->modify('-' . max(1, (int) MIGRATE_CERT_CACHE_HOURS) . ' hours');
                    foreach ($companyMap as $empresaId => $companyData) {
                        $lastCheck = trim((string) ($statusMap[$empresaId] ?? ''));
                        if ($lastCheck === '') {
                            $staleCompanies[$companyData['rut']] = $companyData;
                            continue;
                        }

                        $lastCheckDt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $lastCheck) ?: new DateTimeImmutable($lastCheck);
                        if ($lastCheckDt < $freshCutoff) {
                            $staleCompanies[$companyData['rut']] = $companyData;
                        }
                    }

                    $cachedRows = $this->certificateCacheModel->listCachedRows($companyIds, (string) MIGRATE_ENVIRONMENT, false);
                    $filteredItems = [];
                    foreach ($cachedRows as $row) {
                        $empresaId = (int) ($row['EmpresaId'] ?? 0);

                        if ((int) ($row['HasCertificate'] ?? 0) !== 1) {
                            continue;
                        }

                        $diasRestantes = $this->resolveCertificateDaysRemaining(
                            (string) ($row['CerFchVencimiento'] ?? ''),
                            (string) ($row['DiasRestantes'] ?? '')
                        );
                        if (in_array($filters['cer_status'], ['A', 'I'], true) && (string) ($row['CerStatus'] ?? '') !== $filters['cer_status']) {
                            continue;
                        }
                        if ($intervalLimit > 0 && abs($diasRestantes) > $intervalLimit) {
                            continue;
                        }

                        $filteredItems[] = [
                            'empresa_id' => $empresaId,
                            'razon_social' => (string) ($row['RazonSocial'] ?? ''),
                            'empresa_invoicy' => (string) ($row['EmpresaInvoicy'] ?? ''),
                            'emp_ruc' => (string) ($row['Rut'] ?? ''),
                            'apodo' => (string) ($row['Apodo'] ?? ''),
                            'email' => (string) ($companyMap[$empresaId]['email'] ?? ''),
                            'email_envio_fe' => (string) ($companyMap[$empresaId]['email_envio_fe'] ?? ''),
                            'telefono' => (string) ($companyMap[$empresaId]['telefono'] ?? ''),
                            'alta_tipoempresa' => (string) ($companyMap[$empresaId]['alta_tipoempresa'] ?? ''),
                            'alta_tributario' => (string) ($companyMap[$empresaId]['alta_tributario'] ?? ''),
                            'nombre_completo_firmante' => (string) ($companyMap[$empresaId]['nombre_completo_firmante'] ?? ''),
                            'ci_firmante' => (string) ($companyMap[$empresaId]['ci_firmante'] ?? ''),
                            'cer_status' => (string) ($row['CerStatus'] ?? ''),
                            'dias_restantes' => (string) $diasRestantes,
                            'cer_fch_vencimiento' => (string) ($row['CerFchVencimiento'] ?? ''),
                            'fecha_consulta' => (string) ($row['FechaConsulta'] ?? ''),
                            'action_history' => $actionHistoryByEmpresa[$empresaId] ?? [],
                        ];
                    }

                    usort($filteredItems, static function (array $a, array $b): int {
                        $daysCompare = ((int) ($a['dias_restantes'] ?? 0)) <=> ((int) ($b['dias_restantes'] ?? 0));
                        if ($daysCompare !== 0) {
                            return $daysCompare;
                        }

                        return strcmp((string) ($a['razon_social'] ?? ''), (string) ($b['razon_social'] ?? ''));
                    });

                    $result = [
                        'success' => true,
                        'request_xml' => '',
                        'response_xml' => '',
                        'wsdl' => (string) MIGRATE_CONSULTAEMPRESAS_WSDL,
                        'environment' => (string) MIGRATE_ENVIRONMENT,
                        'msg_code' => '100',
                        'msg_desc' => 'Consulta resuelta desde cache local.',
                        'items' => $filteredItems,
                        'errors' => [],
                        'effective_emp_codigo' => '',
                        'filter_mode' => $filters['filter_mode'],
                        'items_total_source' => count(array_filter($cachedRows, static function (array $row): bool {
                            return (int) ($row['HasCertificate'] ?? 0) === 1;
                        })),
                        'candidate_count' => count($rutMap),
                        'processed_count' => 0,
                        'success_count' => 0,
                        'error_count' => 0,
                        'technical_entries' => [],
                        'cache_hits' => count($rutMap),
                        'cache_refresh_count' => 0,
                        'cache_pending_count' => count($staleCompanies),
                    ];
                }

                if (($filters['filter_mode'] ?? 'empresas_activas') !== 'empresas_activas') {
                    if (!isset($result)) {
                        throw new RuntimeException('No fue posible generar el resultado de la consulta.');
                    }

                    $items = [];
                    foreach ((array) ($result['items'] ?? []) as $certItem) {
                        $certItem['empresa_id'] = 0;
                        $certItem['razon_social'] = '';
                        $certItem['empresa_invoicy'] = '';
                        $certItem['tech_index'] = 0;
                        $items[] = $certItem;
                    }
                    $result['items'] = $items;
                }

                $this->storeCertificatesExportState($filters, $result);
            } catch (Throwable $e) {
                $result = [
                    'success' => false,
                    'request_xml' => '',
                    'response_xml' => '',
                    'wsdl' => (string) MIGRATE_CONSULTAEMPRESAS_WSDL,
                    'environment' => (string) MIGRATE_ENVIRONMENT,
                    'msg_code' => '',
                    'msg_desc' => '',
                    'items' => [],
                    'errors' => [$e->getMessage()],
                    'effective_emp_codigo' => '',
                    'items_total_source' => 0,
                    'candidate_count' => 0,
                    'processed_count' => 0,
                    'success_count' => 0,
                    'error_count' => 0,
                    'filter_mode' => $filters['filter_mode'],
                    'technical_entries' => [],
                    'cache_hits' => 0,
                    'cache_refresh_count' => 0,
                    'cache_pending_count' => 0,
                ];
                $this->storeCertificatesExportState($filters, $result);
            }
        }

        foreach ($empresaModel->listActiveCertificateCandidates() as $company) {
            $normalizedRut = preg_replace('/\D+/', '', (string) ($company['Rut'] ?? ''));
            $empresaId = (int) ($company['IdEmpresa'] ?? 0);
            if ($empresaId <= 0 || $normalizedRut === '') {
                continue;
            }

            $uploadCompanies[] = [
                'empresa_id' => $empresaId,
                'rut' => $normalizedRut,
                'razon_social' => trim((string) ($company['RazonSocial'] ?? '')),
            ];
        }

        require __DIR__ . '/../views/migrate_certificates/index.php';
    }

    public function certificateLogAction(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido.']);
            return;
        }

        try {
            $empresaId = (int) ($_POST['empresa_id'] ?? 0);
            $action = trim((string) ($_POST['action_key'] ?? ''));

            if ($empresaId <= 0) {
                throw new RuntimeException('Empresa no valida para registrar la accion.');
            }

            if ($action !== 'aviso') {
                throw new RuntimeException('La accion solicitada no es valida.');
            }

            $empresaModel = new EmpresaModel();
            $empresa = $empresaModel->findById($empresaId);
            if ($empresa === null) {
                throw new RuntimeException('No se encontro la empresa indicada.');
            }

            $authUser = Auth::user() ?? ['login' => $_SESSION['usuario'] ?? 'admin', 'name' => ''];
            $usuarioLogin = trim((string) ($authUser['login'] ?? 'admin'));
            $usuarioNombre = trim((string) ($authUser['name'] ?? ''));
            $descripcion = 'Aviso al cliente registrado.';

            $actionId = $this->certificateActionModel->create([
                'empresa_id' => $empresaId,
                'rut' => (string) ($empresa['Rut'] ?? ''),
                'accion' => 'AVISO',
                'descripcion' => $descripcion,
                'usuario_login' => $usuarioLogin,
                'usuario_nombre' => $usuarioNombre,
            ]);

            $row = $this->certificateActionModel->findById($actionId);
            if ($row === null) {
                throw new RuntimeException('Se registro la accion pero no fue posible recuperarla.');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Aviso registrado correctamente.',
                'entry' => [
                    'id' => (int) ($row['Id'] ?? 0),
                    'accion' => (string) ($row['Accion'] ?? 'AVISO'),
                    'descripcion' => (string) ($row['Descripcion'] ?? ''),
                    'usuario_login' => (string) ($row['UsuarioLogin'] ?? ''),
                    'usuario_nombre' => (string) ($row['UsuarioNombre'] ?? ''),
                    'fecha_accion' => (string) ($row['FechaAccion'] ?? ''),
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    public function certificateUploadDigital(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido.']);
            return;
        }

        try {
            $empresaId = (int) ($_POST['empresa_id'] ?? 0);
            $rutInput = preg_replace('/\D+/', '', (string) ($_POST['rut'] ?? ''));
            $certificatePassword = trim((string) ($_POST['certificate_password'] ?? ''));
            $uploadedFile = $_FILES['certificate_file'] ?? null;

            if (!is_array($uploadedFile)) {
                throw new RuntimeException('Debe seleccionar un archivo de certificado digital.');
            }

            if ($certificatePassword === '') {
                throw new RuntimeException('Debe digitar la contrasena del certificado digital.');
            }

            $uploadErrors = Validator::validateCertificateDigitalUpload($uploadedFile);
            if ($uploadErrors !== []) {
                throw new RuntimeException((string) reset($uploadErrors));
            }

            $empresaModel = new EmpresaModel();
            $empresa = $empresaModel->findActiveCertificateCandidate($empresaId, $rutInput);

            if ($empresa === null) {
                throw new RuntimeException('No se encontro una empresa activa para el RUT indicado.');
            }

            $empresaId = (int) ($empresa['IdEmpresa'] ?? 0);

            $rut = preg_replace('/\D+/', '', (string) ($empresa['Rut'] ?? ''));
            if ($rut === '') {
                throw new RuntimeException('La empresa no tiene RUT valido para ubicar la carpeta del certificado.');
            }

            $folderRelative = rtrim((string) UPLOAD_BASE_RELATIVE, '\\/') . '/'
                . FileStorage::folderNameFromRut($rut)
                . '/certificados_digitales';

            $stored = FileStorage::storeUploadedFileInFolder($uploadedFile, 'pfx', $folderRelative);
            $storedAbsolute = FileStorage::absoluteFromRelative((string) ($stored['relative_path'] ?? ''));

            $authUser = Auth::user() ?? ['login' => $_SESSION['usuario'] ?? 'admin', 'name' => ''];
            $usuarioLogin = trim((string) ($authUser['login'] ?? 'admin'));
            $usuarioNombre = trim((string) ($authUser['name'] ?? ''));
            $descripcion = 'Certificado digital cargado por RUT. Archivo: ' . (string) ($stored['original_name'] ?? '') . '.';
            $warnings = [];

            $actionId = $this->certificateActionModel->create([
                'empresa_id' => $empresaId,
                'rut' => $rut,
                'accion' => 'CERTIFICADO_SUBIDO',
                'descripcion' => $descripcion,
                'usuario_login' => $usuarioLogin,
                'usuario_nombre' => $usuarioNombre,
            ]);

            $this->appendRecordLog(
                rtrim((string) UPLOAD_BASE_RELATIVE, '\\/') . '/' . FileStorage::folderNameFromRut($rut),
                'CERTIFICADO_DIGITAL_SUBIDO',
                [
                    'empresa_id' => $empresaId,
                    'rut' => $rut,
                    'usuario_login' => $usuarioLogin,
                    'usuario_nombre' => $usuarioNombre,
                    'archivo_original' => $stored['original_name'] ?? '',
                    'archivo_guardado' => $stored['stored_name'] ?? '',
                    'ruta_relativa' => $stored['relative_path'] ?? '',
                    'extension' => $stored['extension'] ?? '',
                    'peso_bytes' => $stored['size'] ?? 0,
                    'password_recibida' => 'SI',
                    'siguiente_paso' => 'Enviar certificado a Migrate e interpretar respuesta',
                ]
            );

            $certificateBinaryData = CertificateDigitalInspector::extractBinary($storedAbsolute);
            $certificateMeta = null;
            try {
                $certificateMeta = CertificateDigitalInspector::inspect($storedAbsolute, $certificatePassword);
            } catch (Throwable $e) {
                $warnings[] = 'No fue posible leer la fecha de vencimiento del certificado: ' . $e->getMessage();
            }

            $migrateCertificateResult = $this->migrateService->installCertificate(
                $empresa,
                [
                    'alias' => $certificatePassword,
                    'password' => $certificatePassword,
                    'content_base64' => base64_encode((string) ($certificateBinaryData['binary'] ?? '')),
                    'source_name' => (string) ($certificateBinaryData['source_name'] ?? ''),
                ]
            );

            $this->appendRecordLog(
                rtrim((string) UPLOAD_BASE_RELATIVE, '\\/') . '/' . FileStorage::folderNameFromRut($rut),
                'CERTIFICADO_DIGITAL_MIGRATE',
                [
                    'empresa_id' => $empresaId,
                    'rut' => $rut,
                    'alias_certificado' => $certificatePassword,
                    'archivo_enviado' => $certificateBinaryData['source_name'] ?? ($stored['stored_name'] ?? ''),
                    'origen_archivo' => $certificateBinaryData['container_name'] !== ''
                        ? (($certificateBinaryData['container_name'] ?? '') . ' -> ' . ($certificateBinaryData['entry_name'] ?? ''))
                        : ($certificateBinaryData['source_name'] ?? ''),
                    'msg_code' => $migrateCertificateResult['msg_code'] ?? '',
                    'msg_desc' => $migrateCertificateResult['msg_desc'] ?? '',
                    'errores' => $migrateCertificateResult['errors'] ?? [],
                    'request_xml' => $migrateCertificateResult['request_xml'] ?? '',
                    'response_xml' => $migrateCertificateResult['response_xml'] ?? '',
                ]
            );

            if (!$migrateCertificateResult['success']) {
                $errorMessage = implode(' | ', (array) ($migrateCertificateResult['errors'] ?? []));
                if ($errorMessage === '') {
                    $errorMessage = (string) ($migrateCertificateResult['msg_desc'] ?? 'Migrate no confirmo la instalacion del certificado.');
                }

                $this->certificateActionModel->create([
                    'empresa_id' => $empresaId,
                    'rut' => $rut,
                    'accion' => 'CERTIFICADO_MIGRATE_ERROR',
                    'descripcion' => 'Migrate rechazo la carga del certificado: ' . $errorMessage,
                    'usuario_login' => 'sistema',
                    'usuario_nombre' => 'Sistema',
                ]);

                throw new RuntimeException(
                    'El archivo se guardo localmente, pero Migrate no lo acepto: ' . $errorMessage
                );
            }

            $this->certificateActionModel->create([
                'empresa_id' => $empresaId,
                'rut' => $rut,
                'accion' => 'CERTIFICADO_MIGRATE_OK',
                'descripcion' => 'Migrate confirmo la recepcion del certificado digital.',
                'usuario_login' => 'sistema',
                'usuario_nombre' => 'Sistema',
            ]);

            if (is_array($certificateMeta)) {
                $validToDate = trim((string) ($certificateMeta['valid_to_date'] ?? ''));
                $today = new DateTimeImmutable('today');
                $expiry = DateTimeImmutable::createFromFormat('Y-m-d', $validToDate) ?: new DateTimeImmutable($validToDate);
                $daysRemaining = (int) $today->diff($expiry)->format('%r%a');

                $cacheModel = new MigrateCertificateCacheModel();
                $cacheModel->replaceCompanySnapshot(
                    [
                        'empresa_id' => $empresaId,
                        'rut' => $rut,
                        'razon_social' => (string) ($empresa['RazonSocial'] ?? ''),
                        'empresa_invoicy' => (string) ($empresa['EmpresaInvoicy'] ?? ''),
                    ],
                    [[
                        'apodo' => (string) ($certificateMeta['common_name'] ?? ($stored['original_name'] ?? 'Principal')),
                        'cer_status' => 'A',
                        'dias_restantes' => $daysRemaining,
                        'cer_fch_vencimiento' => $validToDate,
                    ]],
                    [
                        'environment' => (string) MIGRATE_ENVIRONMENT,
                        'wsdl' => '',
                        'msg_code' => 'LOCAL',
                        'msg_desc' => 'Certificado actualizado desde carga manual.',
                        'error_summary' => '',
                        'request_xml' => (string) ($migrateCertificateResult['request_xml'] ?? ''),
                        'response_xml' => (string) ($migrateCertificateResult['response_xml'] ?? ''),
                        'fecha_consulta' => date('Y-m-d H:i:s'),
                    ]
                );

                $notificationModel = new CertificateNotificationModel();
                $mailer = new CertificateNotificationMailer();

                try {
                    $sentPayload = $mailer->sendCertificateInstallationConfirmation(
                        [
                            'razon_social' => (string) ($empresa['RazonSocial'] ?? ''),
                            'rut' => $rut,
                            'email' => (string) ($empresa['ClienteEmail'] ?? $empresa['EmpresaEmail'] ?? ''),
                            'email_envio_fe' => (string) ($empresa['emailEnvioFE'] ?? ''),
                        ],
                        $certificateMeta
                    );

                    $notificationModel->create([
                        'empresa_id' => $empresaId,
                        'rut' => $rut,
                        'razon_social' => (string) ($empresa['RazonSocial'] ?? ''),
                        'apodo' => (string) ($certificateMeta['common_name'] ?? ''),
                        'cer_status' => 'A',
                        'dias_objetivo' => 0,
                        'dias_restantes' => $daysRemaining,
                        'fecha_vencimiento' => $validToDate,
                        'tipo_notificacion' => 'CERTIFICADO_INSTALADO',
                        'destinatarios' => implode('; ', (array) ($sentPayload['to'] ?? [])),
                        'copias' => implode('; ', array_filter(array_merge(
                            (array) ($sentPayload['cc'] ?? []),
                            array_filter([(string) ($sentPayload['support_cc'] ?? '')])
                        ))),
                        'asunto' => (string) ($sentPayload['subject'] ?? ''),
                        'plantilla' => CertificateNotificationMailer::installationTemplateFileName(),
                        'estado' => 'ENVIADO',
                        'detalle' => 'Correo de confirmacion de instalacion enviado correctamente.',
                        'body_html' => (string) ($sentPayload['body_html'] ?? ''),
                        'payload_json' => json_encode([
                            'empresa_id' => $empresaId,
                            'rut' => $rut,
                            'certificate_meta' => $certificateMeta,
                            'sent_payload' => $sentPayload,
                            'environment' => MIGRATE_ENVIRONMENT,
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);

                    $this->certificateActionModel->create([
                        'empresa_id' => $empresaId,
                        'rut' => $rut,
                        'accion' => 'CERTIFICADO_CONFIRMADO',
                        'descripcion' => 'Confirmacion de instalacion del certificado enviada por correo. Nueva fecha de vencimiento: ' . $validToDate . '.',
                        'usuario_login' => 'sistema',
                        'usuario_nombre' => 'Sistema',
                    ]);
                } catch (Throwable $e) {
                    $notificationModel->create([
                        'empresa_id' => $empresaId,
                        'rut' => $rut,
                        'razon_social' => (string) ($empresa['RazonSocial'] ?? ''),
                        'apodo' => (string) ($certificateMeta['common_name'] ?? ''),
                        'cer_status' => 'A',
                        'dias_objetivo' => 0,
                        'dias_restantes' => $daysRemaining,
                        'fecha_vencimiento' => $validToDate,
                        'tipo_notificacion' => 'CERTIFICADO_INSTALADO',
                        'destinatarios' => '',
                        'copias' => '',
                        'asunto' => '',
                        'plantilla' => CertificateNotificationMailer::installationTemplateFileName(),
                        'estado' => 'ERROR',
                        'detalle' => $e->getMessage(),
                        'body_html' => '',
                        'payload_json' => json_encode([
                            'empresa_id' => $empresaId,
                            'rut' => $rut,
                            'certificate_meta' => $certificateMeta,
                            'environment' => MIGRATE_ENVIRONMENT,
                            'error' => $e->getMessage(),
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);

                    $warnings[] = 'El certificado se guardo y se actualizo la fecha de vencimiento, pero el correo de confirmacion no pudo enviarse: ' . $e->getMessage();
                }

                $this->appendRecordLog(
                    rtrim((string) UPLOAD_BASE_RELATIVE, '\\/') . '/' . FileStorage::folderNameFromRut($rut),
                    'CERTIFICADO_DIGITAL_PROCESADO',
                    [
                        'empresa_id' => $empresaId,
                        'rut' => $rut,
                        'fecha_vencimiento_nueva' => $validToDate,
                        'dias_restantes' => $daysRemaining,
                        'common_name' => $certificateMeta['common_name'] ?? '',
                        'issuer' => $certificateMeta['issuer'] ?? '',
                        'serial' => $certificateMeta['serial'] ?? '',
                        'warnings' => $warnings,
                    ]
                );
            }

            $row = $this->certificateActionModel->findById($actionId);
            if ($row === null) {
                throw new RuntimeException('Se cargo el certificado, pero no fue posible recuperar el evento en historial.');
            }

            $message = 'Certificado digital cargado correctamente para ' . (string) ($empresa['RazonSocial'] ?? '') . ' (' . $rut . '). Se guardo en la carpeta final del RUT, Migrate confirmo la recepcion y quedo registrado en el historial.';
            if ($certificateMeta !== null) {
                $message .= ' Fecha de vencimiento actualizada a ' . (string) ($certificateMeta['valid_to_label'] ?? '');
            }
            if ($warnings !== []) {
                $message .= ' Avisos: ' . implode(' | ', $warnings);
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'empresa' => [
                    'id' => $empresaId,
                    'rut' => $rut,
                    'razon_social' => (string) ($empresa['RazonSocial'] ?? ''),
                ],
                'certificate_meta' => $certificateMeta,
                'warnings' => $warnings,
                'entry' => [
                    'id' => (int) ($row['Id'] ?? 0),
                    'empresa_id' => $empresaId,
                    'rut' => $rut,
                    'accion' => (string) ($row['Accion'] ?? 'CERTIFICADO_SUBIDO'),
                    'descripcion' => (string) ($row['Descripcion'] ?? ''),
                    'usuario_login' => (string) ($row['UsuarioLogin'] ?? ''),
                    'usuario_nombre' => (string) ($row['UsuarioNombre'] ?? ''),
                    'fecha_accion' => (string) ($row['FechaAccion'] ?? ''),
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    public function certificateUpdateOperational(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido.']);
            return;
        }

        try {
            $empresaId = (int) ($_POST['empresa_id'] ?? 0);
            $altaTipoEmpresa = strtoupper(trim((string) ($_POST['alta_tipoempresa'] ?? '')));
            $altaTributario = strtoupper(trim((string) ($_POST['alta_tributario'] ?? '')));

            if ($empresaId <= 0) {
                throw new RuntimeException('Empresa no valida.');
            }

            $tiposEmpresaValidos = ['UNIPERSONAL', 'SOCIEDAD'];
            $tiposTributarioValidos = ['GENERAL', 'IVA MINIMO', 'MONOTRIBUTO', 'MONOTRIBUTO MIDES', 'EXONERADO'];

            if (!in_array($altaTipoEmpresa, $tiposEmpresaValidos, true)) {
                throw new RuntimeException('Tipo de empresa invalido.');
            }

            if (!in_array($altaTributario, $tiposTributarioValidos, true)) {
                throw new RuntimeException('Tipo tributario invalido.');
            }

            $empresaModel = new EmpresaModel();
            $empresa = $empresaModel->findById($empresaId);
            if ($empresa === null) {
                throw new RuntimeException('No se encontro la empresa indicada.');
            }

            $empresaModel->updateOperationalFields($empresaId, $altaTipoEmpresa, $altaTributario);
            $rutEmpresa = trim((string) ($empresa['Rut'] ?? ''));
            $tempSynced = false;
            if ($rutEmpresa !== '') {
                $tempItem = $this->model->findLatestByRut($rutEmpresa);
                if ($tempItem !== null) {
                    $tempData = $tempItem;
                    $tempData['alta_tipoempresa'] = $altaTipoEmpresa;
                    $tempData['alta_tributario'] = $altaTributario;
                    $this->model->updateTemp((int) $tempItem['id'], $tempData);
                    $tempSynced = true;
                }
            }

            echo json_encode([
                'success' => true,
                'message' => $tempSynced
                    ? 'Datos operativos guardados en Empresas y sincronizados en EmpresasNuevas.'
                    : 'Datos operativos guardados en Empresas.',
                'item' => [
                    'alta_tipoempresa' => $altaTipoEmpresa,
                    'alta_tributario' => $altaTributario,
                    'temp_synced' => $tempSynced,
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    public function exportCertificatesExcel(): void
    {
        $state = $this->getCertificatesExportState();
        if ($state === null) {
            Response::flash('error', 'Primero ejecute una consulta de certificados para poder exportarla.');
            Response::redirect('certificados-migrate');
        }

        $items = (array) ($state['items'] ?? []);
        $filename = 'certificados_migrate_' . date('Ymd_His') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "<html><head><meta charset=\"UTF-8\"><style>"
            . "body{font-family:Arial,sans-serif;font-size:12px;color:#0f172a;}"
            . "table{border-collapse:collapse;width:100%;}"
            . "th,td{border:1px solid #cbd5e1;padding:8px;vertical-align:top;}"
            . "th{background:#e2e8f0;font-weight:bold;}"
            . ".text-cell{mso-number-format:'\\@';}"
            . ".title{font-size:18px;font-weight:bold;margin-bottom:8px;}"
            . ".meta{margin-bottom:12px;color:#475569;}"
            . "</style></head><body>";
        echo '<div class="title">Certificados Migrate</div>';
        echo '<div class="meta">Generado: ' . htmlspecialchars(date('Y/m/d H:i:s'), ENT_QUOTES, 'UTF-8') . '</div>';
        echo '<table><thead><tr>';
        $headers = ['#', 'Razon social', 'RUT', 'Email', 'Email Envio FE', 'Telefono', 'Estado', 'Dias restantes', 'Vence'];
        foreach ($headers as $header) {
            echo '<th>' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        echo '</tr></thead><tbody>';

        if ($items === []) {
            echo '<tr><td colspan="9">La consulta no devolvio certificados para los filtros usados.</td></tr>';
        } else {
            foreach ($items as $index => $item) {
                echo '<tr>';
                echo '<td>' . ($index + 1) . '</td>';
                echo '<td>' . htmlspecialchars((string) ($item['razon_social'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td class="text-cell">' . htmlspecialchars((string) ($item['emp_ruc'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars((string) ($item['email'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars((string) ($item['email_envio_fe'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars((string) ($item['telefono'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars($this->formatCertificateStatus((string) ($item['cer_status'] ?? '')), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars((string) ($item['dias_restantes'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars($this->formatCertificateDate((string) ($item['cer_fch_vencimiento'] ?? '')), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table></body></html>';
        exit;
    }

    public function exportCertificatesPdf(): void
    {
        $state = $this->getCertificatesExportState();
        if ($state === null) {
            Response::flash('error', 'Primero ejecute una consulta de certificados para poder exportarla.');
            Response::redirect('certificados-migrate');
        }

        $items = (array) ($state['items'] ?? []);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!doctype html><html lang="es"><head><meta charset="UTF-8"><title>Certificados Migrate</title><style>'
            . 'body{font-family:Arial,sans-serif;margin:24px;color:#0f172a;}'
            . 'h1{font-size:24px;margin:0 0 8px;}'
            . '.meta{margin:0 0 18px;color:#475569;font-size:12px;}'
            . 'table{width:100%;border-collapse:collapse;font-size:11px;}'
            . 'th,td{border:1px solid #cbd5e1;padding:7px 8px;vertical-align:top;}'
            . 'th{background:#f1f5f9;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.08em;}'
            . '.badge{display:inline-block;padding:2px 8px;border-radius:999px;font-weight:bold;font-size:10px;}'
            . '.ok{background:#dcfce7;color:#166534;}'
            . '.no{background:#e2e8f0;color:#334155;}'
            . '@media print{body{margin:10mm;} .no-print{display:none;}}'
            . '</style></head><body>';
        echo '<div class="no-print" style="margin-bottom:16px;"><button onclick="window.print()" style="padding:10px 16px;border:0;border-radius:8px;background:#4f46e5;color:#fff;font-weight:bold;cursor:pointer;">Imprimir / Guardar PDF</button></div>';
        echo '<h1>Certificados Migrate</h1>';
        echo '<p class="meta">Generado: ' . htmlspecialchars(date('Y/m/d H:i:s'), ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<table><thead><tr>';
        $headers = ['#', 'Razon social', 'RUT', 'Email', 'Email Envio FE', 'Telefono', 'Estado', 'Dias restantes', 'Vence'];
        foreach ($headers as $header) {
            echo '<th>' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        echo '</tr></thead><tbody>';
        if ($items === []) {
            echo '<tr><td colspan="9">La consulta no devolvio certificados para los filtros usados.</td></tr>';
        } else {
            foreach ($items as $index => $item) {
                $status = $this->formatCertificateStatus((string) ($item['cer_status'] ?? ''));
                echo '<tr>';
                echo '<td>' . ($index + 1) . '</td>';
                echo '<td>' . htmlspecialchars((string) ($item['razon_social'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars((string) ($item['emp_ruc'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars((string) ($item['email'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars((string) ($item['email_envio_fe'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars((string) ($item['telefono'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td><span class="badge ' . (((string) ($item['cer_status'] ?? '')) === 'A' ? 'ok' : 'no') . '">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span></td>';
                echo '<td>' . htmlspecialchars((string) ($item['dias_restantes'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars($this->formatCertificateDate((string) ($item['cer_fch_vencimiento'] ?? '')), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '</tr>';
            }
        }
        echo '</tbody></table></body></html>';
        exit;
    }

    public function saveSettings(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect('configuracion');
        }

        $rawMonto = trim((string) ($_POST['monto_credito_fiscal_anual'] ?? ''));
        $normalized = str_replace(',', '.', $rawMonto);

        if ($normalized === '' || !is_numeric($normalized)) {
            Response::flash('error', 'Ingrese un monto numerico valido para el credito fiscal.');
            Response::redirect('configuracion');
        }

        $monto = round((float) $normalized, 2);
        if ($monto < 0) {
            Response::flash('error', 'El monto de credito fiscal no puede ser negativo.');
            Response::redirect('configuracion');
        }

        $environment = strtolower(trim((string) ($_POST['migrate_environment'] ?? MIGRATE_ENVIRONMENT)));
        if (!in_array($environment, ['testing', 'production'], true)) {
            Response::flash('error', 'Seleccione un entorno valido para Migrate.');
            Response::redirect('configuracion');
        }

        $partnerCodeRaw = preg_replace('/\D+/', '', (string) ($_POST['migrate_partner_code'] ?? (string) MIGRATE_PARTNER_CODE));
        if ($partnerCodeRaw === '') {
            Response::flash('error', 'Ingrese un codigo de partner valido para Migrate.');
            Response::redirect('configuracion');
        }

        $partnerKey = trim((string) ($_POST['migrate_partner_key'] ?? MIGRATE_PARTNER_KEY));
        if ($partnerKey === '') {
            Response::flash('error', 'Ingrese una clave de partner valida para Migrate.');
            Response::redirect('configuracion');
        }

        $empresaModel = new EmpresaModel();
        $referenceCompany = $this->resolveMigrateReferenceCompany($empresaModel, $environment);
        $certEmpCodigoRaw = preg_replace('/\D+/', '', (string) ($referenceCompany['empresa_invoicy'] ?? ''));
        $certPublicKey = trim((string) ($referenceCompany['clave'] ?? ''));

        $runtimeFile = BASE_PATH . '/config/runtime.php';
        $runtimeConfig = [];
        if (is_file($runtimeFile)) {
            $loadedRuntimeConfig = require $runtimeFile;
            if (is_array($loadedRuntimeConfig)) {
                $runtimeConfig = $loadedRuntimeConfig;
            }
        }

        $runtimeConfig['MONTO_CREDITO_FISCAL_ANUAL'] = $monto;
        $runtimeConfig['MIGRATE_ENVIRONMENT'] = $environment;
        $runtimeConfig['MIGRATE_PARTNER_CODE'] = (int) $partnerCodeRaw;
        $runtimeConfig['MIGRATE_PARTNER_KEY'] = $partnerKey;
        $runtimeConfig['MIGRATE_CERT_EMP_CODIGO'] = (int) $certEmpCodigoRaw;
        $runtimeConfig['MIGRATE_CERT_PUBLIC_KEY'] = $certPublicKey;

        $content = "<?php\n\n"
            . "declare(strict_types=1);\n\n"
            . 'return ' . var_export($runtimeConfig, true) . ";\n";

        $result = @file_put_contents($runtimeFile, $content, LOCK_EX);
        if ($result === false) {
            Response::flash('error', 'No fue posible guardar la configuracion. Revise permisos de escritura sobre config/runtime.php.');
            Response::redirect('configuracion');
        }

        clearstatcache(true, $runtimeFile);
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($runtimeFile, true);
        }

        Response::flash('success', 'Configuracion actualizada correctamente.');
        Response::redirect('configuracion');
    }

    private function resolveMigrateReferenceCompany(EmpresaModel $empresaModel, string $environment): array
    {
        $referenceId = strtolower($environment) === 'production' ? ID_EMPRESA_MASTER : 1;
        $empresa = $empresaModel->findById($referenceId) ?? [];

        return [
            'id' => (int) ($empresa['IdEmpresa'] ?? $referenceId),
            'empresa_invoicy' => trim((string) ($empresa['EmpresaInvoicy'] ?? '')),
            'clave' => trim((string) ($empresa['Clave'] ?? '')),
            'label' => strtolower($environment) === 'production' ? 'Produccion' : 'Testing',
        ];
    }

    private function storeCertificatesExportState(array $filters, array $result): void
    {
        $_SESSION['certificates_export_state'] = [
            'generated_at' => date('Y-m-d H:i:s'),
            'filters' => $filters,
            'items' => array_values((array) ($result['items'] ?? [])),
        ];
    }

    private function getCertificatesExportState(): ?array
    {
        $state = $_SESSION['certificates_export_state'] ?? null;
        return is_array($state) ? $state : null;
    }

    private function formatCertificateDate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $formats = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'];
        foreach ($formats as $format) {
            $dt = DateTimeImmutable::createFromFormat($format, $value);
            if ($dt instanceof DateTimeImmutable) {
                return $format === 'Y-m-d' ? $dt->format('Y/m/d') : $dt->format('Y/m/d H:i');
            }
        }

        try {
            return (new DateTimeImmutable($value))->format('Y/m/d H:i');
        } catch (Throwable $e) {
            return str_replace('-', '/', $value);
        }
    }

    private function formatCertificateStatus(string $status): string
    {
        return strtoupper(trim($status)) === 'A' ? 'Activo' : 'Inactivo';
    }

    private function resolveCertificateDaysRemaining(string $expiryDate, string $fallbackValue = ''): int
    {
        $expiryDate = trim($expiryDate);
        if ($expiryDate === '') {
            return (int) $fallbackValue;
        }

        $formats = ['Y-m-d', 'Y-m-d H:i:s', 'Y-m-d H:i'];
        $expiry = null;
        foreach ($formats as $format) {
            $parsed = DateTimeImmutable::createFromFormat($format, $expiryDate);
            if ($parsed instanceof DateTimeImmutable) {
                $expiry = $parsed;
                break;
            }
        }

        if (!$expiry instanceof DateTimeImmutable) {
            try {
                $expiry = new DateTimeImmutable($expiryDate);
            } catch (Throwable $e) {
                return (int) $fallbackValue;
            }
        }

        $today = new DateTimeImmutable('today');
        return (int) $today->diff($expiry)->format('%r%a');
    }

    private function resolveCertificateServiceCredentials(EmpresaModel $empresaModel): array
    {
        $empresa = null;
        $stmt = Db::conn()->prepare('SELECT EmpresaInvoicy, Clave FROM Empresas WHERE EmpresaInvoicy = ? LIMIT 1');
        $stmt->execute([(string) MIGRATE_CERT_EMP_CODIGO]);
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        $empresaInvoicy = trim((string) ($empresa['EmpresaInvoicy'] ?? ''));
        $clave = trim((string) ($empresa['Clave'] ?? ''));
        if ($empresaInvoicy !== '' && $clave !== '') {
            return [
                'emp_codigo' => $empresaInvoicy,
                'hash_key' => $clave,
                'emp_pk' => (string) MIGRATE_CERT_PUBLIC_KEY,
            ];
        }

        return [
            'emp_codigo' => (string) MIGRATE_CERT_EMP_CODIGO,
            'hash_key' => (string) MIGRATE_PARTNER_KEY,
            'emp_pk' => (string) MIGRATE_CERT_PUBLIC_KEY,
        ];
    }

    private function resolveCertificateCredentialsForCompany(array $company, array $fallback): array
    {
        $companyEmpCodigo = trim((string) ($company['empresa_invoicy'] ?? ''));
        $companyHashKey = trim((string) ($company['hash_key'] ?? $company['clave'] ?? ''));

        return [
            'emp_codigo' => $companyEmpCodigo !== '' ? $companyEmpCodigo : (string) ($fallback['emp_codigo'] ?? ''),
            'hash_key' => $companyHashKey !== '' ? $companyHashKey : (string) ($fallback['hash_key'] ?? ''),
            'emp_pk' => (string) ($fallback['emp_pk'] ?? MIGRATE_CERT_PUBLIC_KEY),
        ];
    }

    private function isCertificateNoDataResult(array $result): bool
    {
        return trim((string) ($result['msg_code'] ?? '')) === '162';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect('index.php?action=create');
        }

        $licencia = (int) ($_POST['licencia'] ?? 0);
        $cfeMensuales = (int) ($_POST['cfe_mensuales'] ?? 0);

        $data = [
            'razon_social' => mb_strtoupper(trim($_POST['razon_social'] ?? '')),
            'nombre_fantasia' => trim($_POST['nombre_fantasia'] ?? ''),
            'domicilio' => trim($_POST['domicilio'] ?? ''),
            'email_principal' => trim($_POST['email_principal'] ?? ''),
            'rut' => trim($_POST['rut'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'ciudad' => trim($_POST['ciudad'] ?? ''),
            'departamento' => trim($_POST['departamento'] ?? ''),
            'usuario_ef' => trim($_POST['usuario_ef'] ?? ''),
            'clave_usuario_ef' => trim($_POST['clave_usuario_ef'] ?? ''),
            'licencia' => $licencia,
            'licencia_texto' => self::licenseLabel($licencia),
            'plan' => (string) $cfeMensuales,
            'usuarios' => (int) ($_POST['usuarios'] ?? 1),
            'cfe_mensuales' => $cfeMensuales,
            'cliente_id_giro' => (int) ($_POST['cliente_id_giro'] ?? 0),
            'cliente_id_vendedor' => $this->resolveSuggestedVendedorId(trim((string) ($_POST['cliente_id_vendedor'] ?? ''))),
            'cliente_id_fidelizacion' => (int) ($_POST['cliente_id_fidelizacion'] ?? 0),
            'email_envio_fe' => self::normalizeEmails($_POST['email_envio_fe'] ?? ''),
            'cliente_abonado_importe' => (float) ($_POST['cliente_abonado_importe'] ?? 0),
            'cliente_abonado_id_producto' => (int) ($_POST['cliente_abonado_id_producto'] ?? 0),
            'cliente_abonado_moneda' => trim($_POST['cliente_abonado_moneda'] ?? 'UYU'),
            'cliente_abonado_periodo' => trim($_POST['cliente_abonado_periodo'] ?? 'MENSUAL'),
            'cliente_abonado_descuento' => (float) ($_POST['cliente_abonado_descuento'] ?? 0),
            'cliente_id_formapago' => (int) ($_POST['cliente_id_formapago'] ?? 0),
            'suc_cod_sucursal' => trim($_POST['suc_cod_sucursal'] ?? ''),
            'suc_cod_fecha_vigencia' => trim($_POST['suc_cod_fecha_vigencia'] ?? ''),
            'alta_tipoempresa' => trim($_POST['alta_tipoempresa'] ?? TIPO_EMPRESA_DEFAULT),
            'alta_tributario' => trim($_POST['alta_tributario'] ?? REGIMEN_TRIBUTARIO_DEFAULT),
            'alta_exonerado_norma' => trim($_POST['alta_exonerado_norma'] ?? ''),
            'alta_es_emisor' => trim($_POST['alta_es_emisor'] ?? 'NO'),
            'alta_credito_fiscal' => trim($_POST['alta_credito_fiscal'] ?? 'NO'),
            'alta_certificado_digital' => trim($_POST['alta_certificado_digital'] ?? ''),
            'nombre_completo_firmante' => trim($_POST['nombre_completo_firmante'] ?? ''),
            'ci_firmante' => trim($_POST['ci_firmante'] ?? ''),
            'observaciones' => trim($_POST['observaciones'] ?? ''),
            'estado' => ESTADO_PENDIENTE_APROBACION,
            'hito_actual' => 'APROBACION_PENDIENTE',
            'usuario_creacion' => $_SESSION['usuario'] ?? 'admin',
        ];
        $data = self::applyConditionalBusinessRules($data);

        $errors = Validator::validateNuevaEmpresa($data, $_FILES);
        $empresaModel = new EmpresaModel();
        $clienteModel = new ClienteModel();
        $rutStatus = $this->lookupRutStatus((string) $data['rut'], 0, $empresaModel, $clienteModel);
        if (($rutStatus['empresa_exists'] ?? false) === true) {
            $errors['rut'] = 'El RUT ya esta registrado como empresa en Dynamica.';
        }
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            Response::flash('error', (string) reset($errors));
            Response::redirect('index.php?action=create');
        }

        $db = Db::conn();
        $nuevaEmpresaId = 0;
        $folderInfo = null;

        try {
            $db->beginTransaction();

            $nuevaEmpresaId = $this->model->create($data);
            $folderInfo = FileStorage::createTemporaryFolder($nuevaEmpresaId);
            $this->model->updateFolder($nuevaEmpresaId, $folderInfo['relative'], 1);

            $this->appendRecordLog($folderInfo['relative'], 'CREACION_REGISTRO_TEMPORAL', [
                'nueva_empresa_id' => $nuevaEmpresaId,
                'usuario' => $data['usuario_creacion'],
                'razon_social' => $data['razon_social'],
                'rut' => $data['rut'],
                'licencia' => $data['licencia'],
                'licencia_texto' => $data['licencia_texto'],
                'cfe_mensuales' => $data['cfe_mensuales'],
                'hito_actual' => $data['hito_actual'],
                'estado' => $data['estado'],
                'carpeta_base' => $folderInfo['relative'],
            ]);

            $filesMap = [
                'archivo_pfx' => ['tipo' => 'pfx', 'obligatorio' => (($data['alta_certificado_digital'] ?? '') === 'ADJUNTO') ? 1 : 0],
                'archivo_credito_fiscal' => ['tipo' => 'credito_fiscal', 'obligatorio' => (($data['alta_credito_fiscal'] ?? 'NO') !== 'NO') ? 1 : 0],
                'archivo_contrato' => ['tipo' => 'contrato', 'obligatorio' => ((float) ($data['cliente_abonado_importe'] ?? 0) > 1000) ? 1 : 0],
                'archivo_6906' => ['tipo' => 'f6906', 'obligatorio' => 0],
                'archivo_logo' => ['tipo' => 'logo', 'obligatorio' => 0],
            ];

            foreach ($filesMap as $inputName => $cfg) {
                if (empty($_FILES[$inputName]) || ($_FILES[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $stored = FileStorage::storeUploadedFileInFolder($_FILES[$inputName], $cfg['tipo'], (string) $folderInfo['relative']);
                $this->archivoModel->create([
                    'nueva_empresa_id' => $nuevaEmpresaId,
                    'tipo_archivo' => $cfg['tipo'],
                    'nombre_original' => $stored['original_name'],
                    'nombre_guardado' => $stored['stored_name'],
                    'ruta_archivo' => $stored['relative_path'],
                    'extension' => $stored['extension'],
                    'mime_type' => $stored['mime_type'],
                    'tamano_bytes' => $stored['size'],
                    'obligatorio' => $cfg['obligatorio'],
                    'usuario_subida' => $data['usuario_creacion'],
                ]);

                $this->appendRecordLog($folderInfo['relative'], 'ADJUNTO_CARGADO', [
                    'nueva_empresa_id' => $nuevaEmpresaId,
                    'tipo_archivo' => $cfg['tipo'],
                    'nombre_original' => $stored['original_name'],
                    'nombre_guardado' => $stored['stored_name'],
                    'ruta_archivo' => $stored['relative_path'],
                    'obligatorio' => (bool) $cfg['obligatorio'],
                    'tamano_bytes' => $stored['size'],
                ]);
            }

            $this->historialModel->create([
                'nueva_empresa_id' => $nuevaEmpresaId,
                'evento' => 'CREACION',
                'estado_anterior' => null,
                'estado_nuevo' => ESTADO_PENDIENTE_APROBACION,
                'descripcion' => 'Alta temporal creada con adjuntos',
                'usuario_evento' => $data['usuario_creacion'],
            ]);

            $db->commit();
            $this->appendRecordLog($folderInfo['relative'], 'CREACION_CONFIRMADA', [
                'nueva_empresa_id' => $nuevaEmpresaId,
                'mensaje' => 'Registro temporal creado correctamente y enviado a aprobacion.',
            ]);
            unset($_SESSION['old'], $_SESSION['errors']);
            Response::flash('success', 'Registro creado correctamente y enviado a aprobacion.');
            Response::redirect('index.php?action=show&id=' . $nuevaEmpresaId);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            if (is_array($folderInfo) && !empty($folderInfo['relative'])) {
                FileStorage::deleteFolderByRelative((string) $folderInfo['relative']);
            }

            $_SESSION['errors'] = ['general' => 'No fue posible guardar el registro: ' . $e->getMessage()];
            $_SESSION['old'] = $data;
            Response::flash('error', 'No fue posible guardar el registro: ' . $e->getMessage());
            Response::redirect('index.php?action=create');
        }
    }

    public function show(int $id): void
    {
        $item = $this->model->findById($id);
        if (!$item) {
            Response::flash('error', 'Registro no encontrado.');
            Response::redirect('index.php');
        }

        $archivos = $this->archivoModel->listByNuevaEmpresaId($id);
        $workflowHistory = $this->historialModel->listWorkflowEventsByNuevaEmpresaIds([$id]);
        $deferredTasks = $this->hitoAutoModel->findLatestCredentialsTasksByNuevaEmpresaIds([$id]);
        $deferredTask = $deferredTasks[$id] ?? null;
        $formOptions = $this->loadFormOptions();
        $item = $this->hydrateReferenceLabelsForItem($item, $formOptions);
        $pageTitle = 'Altas y Automatizaciones';
        require __DIR__ . '/../views/nuevas_empresas/detail.php';
    }

    public function downloadFile(int $id): void
    {
        $archivo = $this->archivoModel->findById($id);
        if (!$archivo) {
            Response::flash('error', 'Adjunto no encontrado.');
            Response::redirect('index.php');
        }

        $absolutePath = FileStorage::absoluteFromRelative((string) $archivo['ruta_archivo']);
        if (!is_file($absolutePath)) {
            Response::flash('error', 'El archivo no existe en disco.');
            Response::redirect('index.php?action=show&id=' . (int) $archivo['nueva_empresa_id']);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ((string) ($archivo['mime_type'] ?: 'application/octet-stream')));
        header('Content-Disposition: attachment; filename="' . basename((string) $archivo['nombre_original']) . '"');
        header('Content-Length: ' . (string) filesize($absolutePath));
        readfile($absolutePath);
        exit;
    }

    public function replaceFile(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id <= 0) {
            Response::redirect('index.php');
        }

        $archivo = $this->archivoModel->findById($id);
        if (!$archivo) {
            Response::flash('error', 'Adjunto no encontrado.');
            Response::redirect('index.php');
        }

        $errors = Validator::validateReplacementUpload($_FILES['archivo_reemplazo'] ?? [], (string) $archivo['tipo_archivo']);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            Response::flash('error', (string) reset($errors));
            Response::redirect('index.php?action=show&id=' . (int) $archivo['nueva_empresa_id']);
        }

        try {
            FileStorage::deleteRelativeFile((string) $archivo['ruta_archivo']);
            $nuevaEmpresa = $this->model->findById((int) $archivo['nueva_empresa_id']);
            if (!$nuevaEmpresa) {
                throw new RuntimeException('Registro temporal no encontrado para reemplazar adjunto.');
            }

            $stored = FileStorage::storeUploadedFileInFolder($_FILES['archivo_reemplazo'], (string) $archivo['tipo_archivo'], (string) ($nuevaEmpresa['carpeta_base'] ?? ''));
            $this->archivoModel->updateFile($id, [
                'nombre_original' => $stored['original_name'],
                'nombre_guardado' => $stored['stored_name'],
                'ruta_archivo' => $stored['relative_path'],
                'extension' => $stored['extension'],
                'mime_type' => $stored['mime_type'],
                'tamano_bytes' => $stored['size'],
                'usuario_subida' => $_SESSION['usuario'] ?? 'admin',
            ]);
            $this->historialModel->create([
                'nueva_empresa_id' => (int) $archivo['nueva_empresa_id'],
                'evento' => 'REEMPLAZO_ARCHIVO',
                'estado_anterior' => null,
                'estado_nuevo' => null,
                'descripcion' => 'Se reemplazo el adjunto tipo ' . (string) $archivo['tipo_archivo'],
                'usuario_evento' => $_SESSION['usuario'] ?? 'admin',
            ]);
            $this->appendRecordLog((string) ($nuevaEmpresa['carpeta_base'] ?? ''), 'REEMPLAZO_ADJUNTO', [
                'nueva_empresa_id' => (int) $archivo['nueva_empresa_id'],
                'usuario' => $_SESSION['usuario'] ?? 'admin',
                'tipo_archivo' => (string) $archivo['tipo_archivo'],
                'nombre_original' => $stored['original_name'],
                'nombre_guardado' => $stored['stored_name'],
                'ruta_archivo' => $stored['relative_path'],
                'tamano_bytes' => $stored['size'],
            ]);
            Response::flash('success', 'Adjunto reemplazado correctamente.');
        } catch (Throwable $e) {
            if (isset($nuevaEmpresa) && is_array($nuevaEmpresa)) {
                $this->appendRecordLog((string) ($nuevaEmpresa['carpeta_base'] ?? ''), 'ERROR_REEMPLAZO_ADJUNTO', [
                    'nueva_empresa_id' => (int) ($archivo['nueva_empresa_id'] ?? 0),
                    'usuario' => $_SESSION['usuario'] ?? 'admin',
                    'tipo_archivo' => (string) ($archivo['tipo_archivo'] ?? ''),
                    'error' => $e->getMessage(),
                ]);
            }
            Response::flash('error', 'No fue posible reemplazar el adjunto.');
        }

        Response::redirect('index.php?action=show&id=' . (int) $archivo['nueva_empresa_id']);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id <= 0) {
            Response::redirect('index.php');
        }

        $item = $this->model->findById($id);
        if (!$item) {
            Response::flash('error', 'Registro no encontrado.');
            Response::redirect('index.php');
        }

        $licencia = (int) ($_POST['licencia'] ?? 0);
        $cfeMensuales = (int) ($_POST['cfe_mensuales'] ?? 0);

        $data = [
            'razon_social' => mb_strtoupper(trim($_POST['razon_social'] ?? '')),
            'nombre_fantasia' => trim($_POST['nombre_fantasia'] ?? ''),
            'domicilio' => trim($_POST['domicilio'] ?? ''),
            'email_principal' => trim($_POST['email_principal'] ?? ''),
            'rut' => trim($_POST['rut'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'ciudad' => trim($_POST['ciudad'] ?? ''),
            'departamento' => trim($_POST['departamento'] ?? ''),
            'usuario_ef' => trim($_POST['usuario_ef'] ?? ''),
            'clave_usuario_ef' => trim($_POST['clave_usuario_ef'] ?? ''),
            'licencia' => $licencia,
            'licencia_texto' => self::licenseLabel($licencia),
            'plan' => (string) $cfeMensuales,
            'usuarios' => (int) ($_POST['usuarios'] ?? 1),
            'cfe_mensuales' => $cfeMensuales,
            'cliente_id_giro' => (int) ($_POST['cliente_id_giro'] ?? 0),
            'cliente_id_vendedor' => $this->resolveSuggestedVendedorId(trim((string) ($_POST['cliente_id_vendedor'] ?? ''))),
            'cliente_id_fidelizacion' => (int) ($_POST['cliente_id_fidelizacion'] ?? 0),
            'email_envio_fe' => self::normalizeEmails($_POST['email_envio_fe'] ?? ''),
            'cliente_abonado_importe' => (float) ($_POST['cliente_abonado_importe'] ?? 0),
            'cliente_abonado_id_producto' => (int) ($_POST['cliente_abonado_id_producto'] ?? 0),
            'cliente_abonado_moneda' => trim($_POST['cliente_abonado_moneda'] ?? 'UYU'),
            'cliente_abonado_periodo' => trim($_POST['cliente_abonado_periodo'] ?? 'MENSUAL'),
            'cliente_abonado_descuento' => (float) ($_POST['cliente_abonado_descuento'] ?? 0),
            'cliente_id_formapago' => (int) ($_POST['cliente_id_formapago'] ?? 0),
            'suc_cod_sucursal' => trim($_POST['suc_cod_sucursal'] ?? ''),
            'suc_cod_fecha_vigencia' => trim($_POST['suc_cod_fecha_vigencia'] ?? ''),
            'alta_tipoempresa' => trim($_POST['alta_tipoempresa'] ?? TIPO_EMPRESA_DEFAULT),
            'alta_tributario' => trim($_POST['alta_tributario'] ?? REGIMEN_TRIBUTARIO_DEFAULT),
            'alta_exonerado_norma' => trim($_POST['alta_exonerado_norma'] ?? ''),
            'alta_es_emisor' => trim($_POST['alta_es_emisor'] ?? 'NO'),
            'alta_credito_fiscal' => trim($_POST['alta_credito_fiscal'] ?? 'NO'),
            'alta_certificado_digital' => trim($_POST['alta_certificado_digital'] ?? ''),
            'nombre_completo_firmante' => trim($_POST['nombre_completo_firmante'] ?? ''),
            'ci_firmante' => trim($_POST['ci_firmante'] ?? ''),
            'observaciones' => trim($_POST['observaciones'] ?? ''),
            'notas_admin' => trim($_POST['notas_admin'] ?? ''),
        ];
        $data = self::applyConditionalBusinessRules($data);

        $errors = Validator::validateNuevaEmpresa(array_merge($item, $data), []);
        $empresaModel = new EmpresaModel();
        $clienteModel = new ClienteModel();
        $rutStatus = $this->lookupRutStatus((string) $data['rut'], $id, $empresaModel, $clienteModel);
        if (($rutStatus['empresa_exists'] ?? false) === true) {
            $errors['rut'] = 'El RUT ya esta registrado como empresa en Dynamica.';
        }
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = array_merge($item, $data);
            Response::flash('error', 'No fue posible actualizar el registro: ' . (string) reset($errors));
            Response::redirect('index.php?action=show&id=' . $id);
        }

        $folderInfo = FileStorage::ensureFolderForRutChange(
            (string) ($item['carpeta_base'] ?? ''),
            (string) ($item['rut'] ?? ''),
            (string) ($data['rut'] ?? '')
        );
        $this->model->updateFolder($id, $folderInfo['relative'], 1);
        $this->model->updateTemp($id, $data);

        // En edicion, cada input de adjunto debe crear el archivo si no existe
        // o reemplazar el existente del mismo tipo sin obligar al usuario a ir
        // al flujo separado de "Reemplazar".
        $filesMap = [
            'archivo_pfx' => ['tipo' => 'pfx', 'obligatorio' => (($data['alta_certificado_digital'] ?? '') === 'ADJUNTO') ? 1 : 0],
            'archivo_credito_fiscal' => ['tipo' => 'credito_fiscal', 'obligatorio' => (($data['alta_credito_fiscal'] ?? 'NO') !== 'NO') ? 1 : 0],
            'archivo_contrato' => ['tipo' => 'contrato', 'obligatorio' => ((float) ($data['cliente_abonado_importe'] ?? 0) > 1000) ? 1 : 0],
            'archivo_6906' => ['tipo' => 'f6906', 'obligatorio' => 0],
            'archivo_logo' => ['tipo' => 'logo', 'obligatorio' => 0],
        ];
        $existingFiles = [];
        foreach ($this->archivoModel->listByNuevaEmpresaId($id) as $archivoExistente) {
            $tipo = (string) ($archivoExistente['tipo_archivo'] ?? '');
            if ($tipo !== '' && !isset($existingFiles[$tipo])) {
                $existingFiles[$tipo] = $archivoExistente;
            }
        }

        foreach ($filesMap as $inputName => $cfg) {
            if (empty($_FILES[$inputName]) || ($_FILES[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $stored = FileStorage::storeUploadedFileInFolder($_FILES[$inputName], $cfg['tipo'], (string) $folderInfo['relative']);
            $existing = $existingFiles[$cfg['tipo']] ?? null;

            if (is_array($existing)) {
                if (!empty($existing['ruta_archivo']) && (string) $existing['ruta_archivo'] !== (string) $stored['relative_path']) {
                    FileStorage::deleteRelativeFile((string) $existing['ruta_archivo']);
                }
                $this->archivoModel->updateFile((int) $existing['id'], [
                    'nombre_original' => $stored['original_name'],
                    'nombre_guardado' => $stored['stored_name'],
                    'ruta_archivo' => $stored['relative_path'],
                    'extension' => $stored['extension'],
                    'mime_type' => $stored['mime_type'],
                    'tamano_bytes' => $stored['size'],
                    'usuario_subida' => $_SESSION['usuario'] ?? 'admin',
                ]);
                $this->appendRecordLog((string) $folderInfo['relative'], 'REEMPLAZO_ADJUNTO', [
                    'nueva_empresa_id' => $id,
                    'usuario' => $_SESSION['usuario'] ?? 'admin',
                    'tipo_archivo' => $cfg['tipo'],
                    'nombre_original' => $stored['original_name'],
                    'nombre_guardado' => $stored['stored_name'],
                    'ruta_archivo' => $stored['relative_path'],
                    'tamano_bytes' => $stored['size'],
                ]);
                continue;
            }

            $this->archivoModel->create([
                'nueva_empresa_id' => $id,
                'tipo_archivo' => $cfg['tipo'],
                'nombre_original' => $stored['original_name'],
                'nombre_guardado' => $stored['stored_name'],
                'ruta_archivo' => $stored['relative_path'],
                'extension' => $stored['extension'],
                'mime_type' => $stored['mime_type'],
                'tamano_bytes' => $stored['size'],
                'obligatorio' => $cfg['obligatorio'],
                'usuario_subida' => $_SESSION['usuario'] ?? 'admin',
            ]);
            $this->appendRecordLog((string) $folderInfo['relative'], 'ADJUNTO_CARGADO', [
                'nueva_empresa_id' => $id,
                'tipo_archivo' => $cfg['tipo'],
                'nombre_original' => $stored['original_name'],
                'nombre_guardado' => $stored['stored_name'],
                'ruta_archivo' => $stored['relative_path'],
                'obligatorio' => (bool) $cfg['obligatorio'],
                'tamano_bytes' => $stored['size'],
            ]);
        }

        $this->historialModel->create([
            'nueva_empresa_id' => $id,
            'evento' => 'EDICION',
            'estado_anterior' => $item['estado'],
            'estado_nuevo' => $item['estado'],
            'descripcion' => 'Registro temporal editado',
            'usuario_evento' => $_SESSION['usuario'] ?? 'admin',
        ]);

        $this->appendRecordLog((string) ($folderInfo['relative'] ?? $item['carpeta_base'] ?? ''), 'EDICION_REGISTRO_TEMPORAL', [
            'nueva_empresa_id' => $id,
            'usuario' => $_SESSION['usuario'] ?? 'admin',
            'razon_social' => $data['razon_social'],
            'rut' => $data['rut'],
            'licencia' => $data['licencia'],
            'licencia_texto' => $data['licencia_texto'],
            'cfe_mensuales' => $data['cfe_mensuales'],
            'cliente_id_giro' => $data['cliente_id_giro'],
            'cliente_id_formapago' => $data['cliente_id_formapago'],
            'cliente_abonado_id_producto' => $data['cliente_abonado_id_producto'],
            'carpeta_base' => $folderInfo['relative'],
        ]);

        Response::flash('success', 'Registro actualizado correctamente.');
        Response::redirect('index.php?action=show&id=' . $id);
    }

    private function loadFormOptions(): array
    {
        return [
            'giros' => $this->catalogoModel->listGiros(ID_EMPRESA_MASTER),
            'fidelizaciones' => $this->catalogoModel->listFidelizaciones(ID_EMPRESA_MASTER),
            'ciudades' => $this->catalogoModel->listCiudades(ID_EMPRESA_MASTER),
            'departamentos' => $this->catalogoModel->listDepartamentos(ID_EMPRESA_MASTER),
            'vendedores' => $this->catalogoModel->listVendedores(ID_EMPRESA_MASTER),
            'productos_abonado' => $this->catalogoModel->listProductosAbonado(ID_EMPRESA_MASTER),
            'formas_pago' => $this->catalogoModel->listFormasPago(ID_EMPRESA_MASTER),
        ];
    }

    private function resolveSuggestedVendedorId(string $submittedValue = ''): string
    {
        $submittedValue = trim($submittedValue);
        if ($submittedValue !== '') {
            return $submittedValue;
        }

        $authUser = Auth::user();
        $login = trim((string) ($authUser['login'] ?? ''));

        return $login !== '' ? $login : ID_VENDEDOR_DEFAULT;
    }

    private function hydrateReferenceLabelsForItems(array $items, array $formOptions): array
    {
        foreach ($items as $index => $item) {
            $items[$index] = $this->hydrateReferenceLabelsForItem($item, $formOptions);
        }

        return $items;
    }

    private function hydrateReferenceLabelsForItem(array $item, array $formOptions): array
    {
        $item['ciudad'] = $this->resolveOptionId(
            $formOptions['ciudades'] ?? [],
            'id',
            'nombre',
            (string) ($item['ciudad'] ?? '')
        );
        $item['departamento'] = $this->resolveOptionId(
            $formOptions['departamentos'] ?? [],
            'id',
            'nombre',
            (string) ($item['departamento'] ?? '')
        );
        $item['ciudad_nombre'] = $this->resolveOptionLabel(
            $formOptions['ciudades'] ?? [],
            'id',
            'nombre',
            (string) ($item['ciudad'] ?? '')
        );
        $item['departamento_nombre'] = $this->resolveOptionLabel(
            $formOptions['departamentos'] ?? [],
            'id',
            'nombre',
            (string) ($item['departamento'] ?? '')
        );

        return $item;
    }

    private function resolveOptionId(array $options, string $idKey, string $labelKey, string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        foreach ($options as $option) {
            if ((string) ($option[$idKey] ?? '') === $value) {
                return (string) ($option[$idKey] ?? '');
            }
        }

        foreach ($options as $option) {
            if (mb_strtoupper(trim((string) ($option[$labelKey] ?? ''))) === mb_strtoupper($value)) {
                return (string) ($option[$idKey] ?? '');
            }
        }

        return $value;
    }

    private static function normalizeEmails(string $value): string
    {
        $parts = array_filter(array_map('trim', explode(';', str_replace(',', ';', $value))), static function (string $part): bool {
            return $part !== '';
        });

        return implode(';', $parts);
    }

    private static function applyConditionalBusinessRules(array $data): array
    {
        $tributario = strtoupper(trim((string) ($data['alta_tributario'] ?? REGIMEN_TRIBUTARIO_DEFAULT)));
        $creditoFiscal = strtoupper(trim((string) ($data['alta_credito_fiscal'] ?? 'NO')));
        $importe = (float) ($data['cliente_abonado_importe'] ?? 0);
        $topeCreditoFiscal = (float) MONTO_CREDITO_FISCAL_ANUAL;
        $licencia = (int) ($data['licencia'] ?? 0);

        $data['alta_tributario'] = $tributario;

        if ($tributario === 'IVA MINIMO') {
            $data['alta_exonerado_norma'] = 'CONTRIBUYENTE IVA MINIMO';
            $creditoFiscal = 'LITERAL E';
        } elseif ($tributario === 'MONOTRIBUTO') {
            $data['alta_exonerado_norma'] = 'CONTRIBUYENTE MONOTRIBUTO';
        } elseif ($tributario === 'MONOTRIBUTO MIDES') {
            $data['alta_exonerado_norma'] = 'CONTRIBUYENTE MONOTRIBUTO MIDES';
        } elseif ($tributario !== 'EXONERADO') {
            $data['alta_exonerado_norma'] = '';
        }

        $data['alta_credito_fiscal'] = $creditoFiscal;
        $data['email_envio_fe'] = trim((string) ($data['email_envio_fe'] ?? '')) !== ''
            ? (string) $data['email_envio_fe']
            : (string) ($data['email_principal'] ?? '');
        $data['cliente_abonado_id_producto'] = self::resolveAbonadoProductByLicense($licencia);
        $data['cliente_abonado_tv'] = 'CREDITO';
        $periodo = trim((string) ($data['cliente_abonado_periodo'] ?? 'MENSUAL')) ?: 'MENSUAL';
        $data['cliente_abonado_grupo'] = $periodo === 'ANUAL' ? self::currentBillingMonth() : 'MENSUAL';
        $data['cliente_pn_credito_fiscal'] = 'NO';
        $data['cliente_pn_monto'] = 0;
        $data['cliente_id_medio_pago'] = 0;
        $data['cliente_id_formapago'] = 444;

        if ($creditoFiscal === 'LITERAL E') {
            $data['cliente_abonado_importe'] = $topeCreditoFiscal;
            $data['cliente_abonado_moneda'] = 'UYU';
            $data['cliente_abonado_periodo'] = 'MENSUAL';
            $data['cliente_abonado_grupo'] = 'MENSUAL';
            $data['cliente_abonado_tv'] = 'CONTADO';
            $data['cliente_id_medio_pago'] = 820;
        } elseif ($creditoFiscal === 'RESGUARDO') {
            $data['cliente_abonado_periodo'] = 'MENSUAL';
            $data['cliente_abonado_grupo'] = 'MENSUAL';
            $data['cliente_pn_credito_fiscal'] = 'SI';
            $data['cliente_pn_monto'] = min($importe, $topeCreditoFiscal);
        }

        $data['cliente_adenda'] = trim((string) ($data['cliente_adenda'] ?? ''));
        $data['genera_usuario_migrate'] = WorkflowHelper::licenseCreatesMigrateUser($licencia) ? 1 : 0;
        $data['genera_usuario_dynamica'] = WorkflowHelper::licenseCreatesDynamicaUser($licencia) ? 1 : 0;

        return $data;
    }

    private static function resolveAbonadoProductByLicense(int $licencia): int
    {
        $map = [
            0 => 333892,
            2 => 333893,
            3 => 333894,
            10 => 333895,
            12 => 235239,
            14 => 333896,
        ];

        return $map[$licencia] ?? 0;
    }

    private static function currentBillingMonth(): string
    {
        $months = [
            'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO',
            'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE',
        ];

        return $months[((int) date('n')) - 1] ?? 'MENSUAL';
    }

    private static function licenseLabel(int $licenseCode): string
    {
        return LICENCIAS_DISPONIBLES[$licenseCode] ?? '';
    }

    public function approve(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id <= 0) {
            Response::redirect('index.php');
        }

        $db = Db::conn();
        $empresaModel = new EmpresaModel();
        $clienteModel = new ClienteModel();
        $usuarioAprobacion = $_SESSION['usuario'] ?? 'admin';
        $folderMigration = null;

        try {
            $db->beginTransaction();

            $item = $this->model->findByIdForUpdate($id);
            if (!$item) {
                throw new RuntimeException('Registro temporal no encontrado.');
            }

            if (!WorkflowHelper::canApprove($item)) {
                throw new RuntimeException('El registro no esta pendiente de aprobacion.');
            }

            if ($empresaModel->existsByRut((string) $item['rut'])) {
                throw new RuntimeException('Ya existe una empresa con ese RUT.');
            }

            if ($clienteModel->existsByDocumentoAndEmpresa((string) $item['rut'], ID_EMPRESA_MASTER)) {
                throw new RuntimeException('Ya existe un cliente en la empresa 397 con ese documento.');
            }

            $licencia = (int) ($item['licencia'] ?? 0);
            $empresaId = $empresaModel->createFromNuevaEmpresa($item);
            $clienteId = $clienteModel->createFromNuevaEmpresa($item, ID_EMPRESA_MASTER);
            $localId = $this->localModel->ensureCasaCentral($empresaId);
            $provisioning = $this->provisioningModel->provisionBaseStructure($empresaId, $localId);
            $dynamicaUser = [
                'created' => false,
                'login' => '',
                'password' => '',
                'detail' => WorkflowHelper::dynamicaUserSubstepLabel($licencia),
            ];

            if (WorkflowHelper::licenseCreatesDynamicaUser($licencia)) {
                $dynamicaUser = $this->secUserModel->ensureDefaultAdminForEmpresa($item, $empresaId, $localId);
            }

            $folderMigration = FileStorage::moveOnboardingFolderToFinal((string) ($item['carpeta_base'] ?? ''), (string) ($item['rut'] ?? ''));
            $this->archivoModel->rebasePathsForNuevaEmpresa($id, (string) $folderMigration['relative']);
            $this->model->updateFolder($id, (string) $folderMigration['relative'], 1);
            $this->model->markApproved($id, $empresaId, $clienteId, $usuarioAprobacion);

            $this->historialModel->create([
                'nueva_empresa_id' => $id,
                'evento' => 'APROBACION',
                'estado_anterior' => ESTADO_PENDIENTE_APROBACION,
                'estado_nuevo' => ESTADO_APROBADO,
                'descripcion' => "Creado IdEmpresa={$empresaId}, idcliente={$clienteId}, IdLocal={$localId}, IdDeposito=" . ($provisioning['deposito_id'] ?? 0),
                'usuario_evento' => $usuarioAprobacion,
            ]);

            $this->historialModel->create([
                'nueva_empresa_id' => $id,
                'evento' => 'HITO_DYNAMICA_OK',
                'estado_anterior' => ESTADO_PENDIENTE_APROBACION,
                'estado_nuevo' => ESTADO_APROBADO,
                'descripcion' => 'Empresa, cliente y estructura base creados en Dynamica.',
                'usuario_evento' => $usuarioAprobacion,
            ]);

            $this->historialModel->create([
                'nueva_empresa_id' => $id,
                'evento' => WorkflowHelper::licenseCreatesDynamicaUser($licencia) ? 'HITO_DYNAMICA_USUARIO_OK' : 'HITO_DYNAMICA_USUARIO_NA',
                'estado_anterior' => ESTADO_APROBADO,
                'estado_nuevo' => ESTADO_APROBADO,
                'descripcion' => WorkflowHelper::licenseCreatesDynamicaUser($licencia)
                    ? ($dynamicaUser['detail'] . ' Login ' . ($dynamicaUser['login'] ?: '-'))
                    : WorkflowHelper::dynamicaUserSubstepLabel($licencia),
                'usuario_evento' => $usuarioAprobacion,
            ]);

            $this->historialModel->create([
                'nueva_empresa_id' => $id,
                'evento' => 'HITO_EN_PROCESO',
                'estado_anterior' => ESTADO_PENDIENTE_APROBACION,
                'estado_nuevo' => ESTADO_APROBADO,
                'descripcion' => 'Aprobado por Admin. Dynamica completado; pendiente Migrate.',
                'usuario_evento' => $usuarioAprobacion,
            ]);

            $db->commit();
            $this->appendRecordLog((string) (($folderMigration['relative'] ?? '') ?: ($item['carpeta_base'] ?? '')), 'APROBACION_Y_CREACION_REAL', [
                'nueva_empresa_id' => $id,
                'usuario' => $usuarioAprobacion,
                'empresa_id_creada' => $empresaId,
                'cliente_id_creado' => $clienteId,
                'estado_nuevo' => ESTADO_APROBADO,
                'hito_actual' => 'MIGRATE',
                'detalle' => 'Dynamica completado y listo para Migrate',
                'local_id_creado' => $localId,
                'provisionamiento' => $provisioning,
                'usuario_dynamica' => $dynamicaUser,
                'carpeta_final' => $folderMigration['relative'] ?? ($item['carpeta_base'] ?? ''),
            ]);
            Response::flash('success', "Registro aprobado. Dynamica listo con empresa {$empresaId}, cliente {$clienteId} y siguiente paso Migrate.");
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            if (is_array($folderMigration)
                && !empty($folderMigration['old_relative'])
                && !empty($folderMigration['relative'])
                && (string) $folderMigration['old_relative'] !== (string) $folderMigration['relative']) {
                try {
                    FileStorage::relocateFolder((string) $folderMigration['relative'], (string) $folderMigration['old_relative']);
                } catch (Throwable $folderRollbackError) {
                    // Intencional: preservar el error principal.
                }
            }

            try {
                $this->model->markError($id, $e->getMessage());
            } catch (Throwable $inner) {
                // Intencional: no ocultar el error principal si falla el registro de error.
            }

            if (isset($item) && is_array($item)) {
                $this->appendRecordLog((string) ($item['carpeta_base'] ?? ''), 'ERROR_APROBACION', [
                    'nueva_empresa_id' => $id,
                    'usuario' => $usuarioAprobacion,
                    'error' => $e->getMessage(),
                ]);
            }

            Response::flash('error', $e->getMessage());
        }

        Response::redirect('index.php?action=show&id=' . $id);
    }

    public function validateRut(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $rut = trim((string) ($_GET['rut'] ?? ''));
        $nuevaEmpresaId = (int) ($_GET['id'] ?? 0);

        if ($rut === '' || preg_match('/^[0-9]{12}$/', $rut) !== 1) {
            echo json_encode([
                'ok' => true,
                'rut' => $rut,
                'empresa_exists' => false,
                'cliente_exists' => false,
                'message' => '',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $result = $this->lookupRutStatus($rut, $nuevaEmpresaId, new EmpresaModel(), new ClienteModel());
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function changeHito(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id <= 0) {
            Response::redirect('index.php');
        }

        $item = $this->model->findById($id);
        if (!$item) {
            Response::flash('error', 'Registro no encontrado.');
            Response::redirect('index.php');
        }

        if ((string) ($item['estado'] ?? '') === ESTADO_ELIMINADO) {
            Response::flash('error', 'No es posible cambiar el hito de un registro eliminado.');
            Response::redirect('index.php?action=show&id=' . $id);
        }

        $target = trim((string) ($_POST['target_hito'] ?? ''));
        $usuario = $_SESSION['usuario'] ?? 'admin';
        $normalizedTarget = strtoupper($target);
        $currentHito = $this->resolveCurrentWorkflowForAction($item);

        if ($normalizedTarget === 'CERTIFICADO_DIGITAL') {
            if (!$this->canMarkCertificateDigital($item, $currentHito)) {
                Response::flash('error', 'El caso no esta habilitado para marcar Certificado Digital en este momento.');
                Response::redirect('index.php?action=show&id=' . $id);
            }
            $this->completeCertificateDigitalStep($id, $item, $usuario);
            Response::flash('success', 'Certificado digital marcado correctamente. El flujo quedo listo para Homologacion DGI.');
            Response::redirect('index.php?action=show&id=' . $id);
        }

        if ($normalizedTarget === 'HOMOLOGACION_DGI') {
            if (!$this->canMarkHomologacionDgi($item, $currentHito)) {
                Response::flash('error', 'El caso no esta habilitado para marcar Homologacion DGI en este momento.');
                Response::redirect('index.php?action=show&id=' . $id);
            }
            $this->completeHomologacionDgiStep($id, $item, $usuario);
            Response::flash('success', 'Homologacion DGI marcada correctamente. El caso quedo listo para Alta Final.');
            Response::redirect('index.php?action=show&id=' . $id);
        }

        if ($normalizedTarget === 'ALTA_FINAL') {
            if (!$this->canRunAltaFinal($item, $currentHito)) {
                Response::flash('error', 'El caso no esta habilitado para ejecutar Alta Final en este momento.');
                Response::redirect('index.php?action=show&id=' . $id);
            }
            try {
                $result = $this->completeAltaFinalAndAutoStages($id, $item, $usuario);
                Response::flash('success', $result['message']);
            } catch (Throwable $e) {
                try {
                    $restoreHito = 'ALTA_PENDIENTE';
                    $restoreDetail = trim((string) ($item['estado_detalle'] ?? ''));
                    if ($restoreDetail === '') {
                        $restoreDetail = 'Alta final pendiente por novedad al procesar la factura automatica.';
                    }
                    $this->model->updateHitoActual($id, $restoreHito, $restoreDetail);
                    $this->model->updateErrorProceso($id, $e->getMessage());
                    $this->appendRecordLog((string) ($item['carpeta_base'] ?? ''), 'ERROR_ALTA_FINAL', [
                        'nueva_empresa_id' => $id,
                        'usuario' => $usuario,
                        'error' => $e->getMessage(),
                        'target_hito' => $normalizedTarget,
                    ]);
                } catch (Throwable $restoreError) {
                    // Intencional: no ocultar el error principal si falla la restauracion del estado.
                }

                Response::flash('error', 'No fue posible completar el Alta Final: ' . $e->getMessage());
            }
            Response::redirect('index.php?action=show&id=' . $id);
        }

        if ($normalizedTarget === 'CLIENTE_ACTIVO') {
            if (!$this->canMarkClienteActivo($item, $currentHito)) {
                Response::flash('error', 'El caso no esta habilitado para marcar Cliente Activo en este momento.');
                Response::redirect('index.php?action=show&id=' . $id);
            }
            $this->registerWorkflowEvent(
                $id,
                'CLIENTE_ACTIVO',
                'HITO_CLIENTE_ACTIVO',
                'Cliente activo.',
                $usuario,
                (string) ($item['carpeta_base'] ?? '')
            );
            Response::flash('success', 'El cliente se marco como activo.');
            Response::redirect('index.php?action=show&id=' . $id);
        }

        Response::flash('error', 'El hito solicitado no es valido.');
        Response::redirect('index.php?action=show&id=' . $id);
    }

    private function canMarkCertificateDigital(array $item, string $currentHito): bool
    {
        if ((string) ($item['estado'] ?? '') === ESTADO_ERROR_APROBACION) {
            return false;
        }

        return $currentHito === 'CERTIFICADO_DIGITAL';
    }

    private function canMarkHomologacionDgi(array $item, string $currentHito): bool
    {
        if ((string) ($item['estado'] ?? '') === ESTADO_ERROR_APROBACION) {
            return false;
        }

        return $currentHito === 'HOMOLOGACION_DGI';
    }

    private function canRunAltaFinal(array $item, string $currentHito): bool
    {
        if ((string) ($item['estado'] ?? '') === ESTADO_ERROR_APROBACION) {
            return false;
        }

        return in_array($currentHito, ['ALTA_PENDIENTE', 'ENVIO_FACTURA', 'ENVIO_CREDENCIALES', 'ALTA_FINAL'], true);
    }

    private function canMarkClienteActivo(array $item, string $currentHito): bool
    {
        if ((string) ($item['estado'] ?? '') === ESTADO_ERROR_APROBACION) {
            return false;
        }

        return in_array($currentHito, ['ALTA_FINAL', 'CLIENTE_ACTIVO'], true);
    }

    private function resolveCurrentWorkflowForAction(array $item): string
    {
        $estado = (string) ($item['estado'] ?? '');
        $persisted = strtoupper(trim((string) ($item['hito_actual'] ?? '')));
        $empresaCreada = (int) ($item['empresa_creada'] ?? 0) === 1;
        $clienteCreado = (int) ($item['cliente_creado'] ?? 0) === 1;
        $historyByItem = $this->historialModel->listWorkflowEventsByNuevaEmpresaIds([(int) ($item['id'] ?? 0)]);
        $history = $historyByItem[(int) ($item['id'] ?? 0)] ?? [];
        $events = [];

        foreach ($history as $event) {
            $events[(string) ($event['evento'] ?? '')] = $event;
        }

        if ($estado === ESTADO_ELIMINADO) {
            return 'CANCELADO';
        }
        if (isset($events['HITO_CLIENTE_ACTIVO'])) {
            return 'CLIENTE_ACTIVO';
        }
        if (isset($events['HITO_ENVIO_CREDENCIALES'])) {
            return 'ALTA_FINAL';
        }
        if (isset($events['HITO_ENVIO_FACTURA'])) {
            return 'ENVIO_CREDENCIALES';
        }
        if (isset($events['HITO_ALTA_PENDIENTE'])) {
            return 'ALTA_PENDIENTE';
        }
        if (isset($events['HITO_HOMOLOGACION_DGI'])) {
            return 'ENVIO_FACTURA';
        }
        if (isset($events['HITO_CERTIFICADO_DIGITAL'])) {
            return 'HOMOLOGACION_DGI';
        }
        if (isset($events['HITO_PENDIENTE_DGI'])) {
            return 'HOMOLOGACION_DGI';
        }
        if (isset($events['HITO_MIGRATE_OK'])) {
            return 'CERTIFICADO_DIGITAL';
        }
        if (isset($events['HITO_DYNAMICA_OK']) || ($empresaCreada && $clienteCreado)) {
            return 'MIGRATE';
        }
        if (isset($events['HITO_EN_PROCESO'])) {
            return 'DYNAMICA';
        }
        if ($persisted === 'ERROR_APROBACION' && !$empresaCreada && !$clienteCreado) {
            return 'APROBACION_PENDIENTE';
        }
        if ($persisted !== '') {
            return $persisted === 'PENDIENTE_DGI' ? 'HOMOLOGACION_DGI' : $persisted;
        }
        if ($estado === ESTADO_ERROR_APROBACION) {
            return ($empresaCreada && $clienteCreado) ? 'MIGRATE' : 'APROBACION_PENDIENTE';
        }
        if ($estado === ESTADO_APROBADO) {
            return ($empresaCreada && $clienteCreado) ? 'CERTIFICADO_DIGITAL' : 'DYNAMICA';
        }

        return $estado === ESTADO_PENDIENTE_APROBACION ? 'APROBACION_PENDIENTE' : 'DYNAMICA';
    }

    private function completeCertificateDigitalStep(int $id, array $item, string $usuario): void
    {
        $this->registerWorkflowEvent(
            $id,
            'HOMOLOGACION_DGI',
            'HITO_CERTIFICADO_DIGITAL',
            'Certificado digital gestionado.',
            $usuario,
            (string) ($item['carpeta_base'] ?? '')
        );
    }

    private function completeHomologacionDgiStep(int $id, array $item, string $usuario): void
    {
        $folderBase = (string) ($item['carpeta_base'] ?? '');
        $this->registerWorkflowEvent(
            $id,
            'ALTA_PENDIENTE',
            'HITO_HOMOLOGACION_DGI',
            'Homologación DGI completada.',
            $usuario,
            $folderBase
        );
    }

    private function loadWorkflowEventMap(int $nuevaEmpresaId): array
    {
        $historyByItem = $this->historialModel->listWorkflowEventsByNuevaEmpresaIds([$nuevaEmpresaId]);
        $history = $historyByItem[$nuevaEmpresaId] ?? [];
        $events = [];

        foreach ($history as $event) {
            $code = (string) ($event['evento'] ?? '');
            if ($code === '') {
                continue;
            }
            $events[$code] = $event;
        }

        return $events;
    }

    private function completeAltaFinalAndAutoStages(int $id, array $item, string $usuario): array
    {
        $folderBase = (string) ($item['carpeta_base'] ?? '');
        $overrideEmail = $this->resolveOnboardingOverrideEmail();
        $events = $this->loadWorkflowEventMap($id);
        $invoiceAlreadyDone = isset($events['HITO_ENVIO_FACTURA']);

        if ($invoiceAlreadyDone) {
            $invoiceInfo = [
                'mode_label' => 'ya emitida previamente',
                'already_emitted' => true,
            ];
        } else {
            $this->model->updateHitoActual($id, 'ENVIO_FACTURA', 'Preparando hito automatico de envio de factura.');
            $invoiceInfo = $this->processInvoiceStage($item, $usuario, $folderBase, $overrideEmail);
        }

        $scheduledAt = $this->resolveCredentialsScheduledAt();
        $taskId = $this->hitoAutoModel->scheduleCredentialsTask($id, $scheduledAt, [
            'scheduled_at' => $scheduledAt,
            'created_by' => $usuario,
            'environment' => MIGRATE_ENVIRONMENT,
        ]);
        $this->model->updateHitoActual(
            $id,
            'ENVIO_CREDENCIALES',
            'Factura emitida. Credenciales programadas para envio diferido el ' . $scheduledAt . '.'
        );

        $summary = [
            $invoiceAlreadyDone
                ? 'Factura ya emitida previamente; no se genero un segundo documento.'
                : 'Factura automatizada (' . $invoiceInfo['mode_label'] . ').',
            'Credenciales programadas para el siguiente ciclo automatico.',
        ];

        return [
            'message' => implode(' ', $summary),
            'invoice' => $invoiceInfo,
            'queue' => [
                'task_id' => $taskId,
                'scheduled_at' => $scheduledAt,
            ],
        ];
    }

    private function processInvoiceStage(array $item, string $usuario, string $folderBase, string $overrideEmail): array
    {
        $periodo = strtoupper(trim((string) ($item['cliente_abonado_periodo'] ?? 'MENSUAL')));
        $clienteModel = new ClienteModel();
        $billingStartDate = $clienteModel->previewBillingStartDate($periodo);
        $today = new DateTimeImmutable('today');
        $modeLabel = $periodo === 'ANUAL'
            ? 'anual inmediata'
            : (((int) $today->format('d') <= 20) ? 'mensual inmediata' : 'mensual inmediata con fecha desde al mes subsiguiente');

        $invoiceResult = $this->onboardingInvoiceService->emitInvoice($item);
        $clienteId = (int) ($item['cliente_id_creado'] ?? 0);
        if ($clienteId > 0) {
            // La fecha base de facturacion debe quedar visible desde el hito 7,
            // aun cuando el alta final del cliente se complete en el siguiente ciclo.
            $clienteModel->persistBillingStartDateForOnboarding($clienteId, $billingStartDate);
        }
        $mailResult = [
            'sent' => false,
            'template' => '',
            'to' => [],
            'error' => '',
        ];
        $mailSummary = 'Aviso administrativo de factura pendiente.';
        try {
            $mailResult = $this->onboardingMailer->sendInvoiceNotice($item, [
                'override_email' => $overrideEmail,
            ]);
            $mailSummary = 'Aviso administrativo de factura enviado.';
        } catch (Throwable $mailError) {
            $mailSummary = 'Aviso administrativo de factura pendiente por error: ' . $mailError->getMessage();
            $this->appendRecordLog($folderBase, 'AUTO_ENVIO_FACTURA_MAIL_ERROR', [
                'nueva_empresa_id' => (int) ($item['id'] ?? 0),
                'usuario' => $usuario,
                'error' => $mailError->getMessage(),
            ]);
        }

        $description = sprintf(
            'Factura emitida y enviada a Migrate (%s). Fecha base de facturacion: %s. %s',
            $modeLabel,
            $billingStartDate,
            $mailSummary
        );

        $this->registerWorkflowEvent(
            (int) $item['id'],
            'ENVIO_CREDENCIALES',
            'HITO_ENVIO_FACTURA',
            $description,
            $usuario,
            $folderBase,
            'AUTO_ENVIO_FACTURA',
            [
                'template' => $mailResult['template'] ?? '',
                'to' => implode(';', (array) ($mailResult['to'] ?? [])),
                'mode_label' => $modeLabel,
                'billing_start_date' => $billingStartDate,
                'invoice_idventa' => (string) ($invoiceResult['idventa'] ?? ''),
                'invoice_estado' => (string) ($invoiceResult['estado_descripcion'] ?? ''),
                'invoice_request_create' => (string) ($invoiceResult['request_create'] ?? ''),
                'invoice_response_create' => (string) ($invoiceResult['response_create'] ?? ''),
                'invoice_request_send' => (string) ($invoiceResult['request_send'] ?? ''),
                'invoice_response_send' => (string) ($invoiceResult['response_send'] ?? ''),
            ]
        );

        return [
            'billing_start_date' => $billingStartDate,
            'mode_label' => $modeLabel,
            'mail' => $mailResult,
            'invoice' => $invoiceResult,
        ];
    }

    private function processCredentialsStage(array $item, string $usuario, string $folderBase, string $overrideEmail): array
    {
        $licencia = (int) ($item['licencia'] ?? 0);
        if ($licencia === 14) {
            $migrateCredentials = $this->migrateService->resolveExpectedMigrateUserCredentials($item);
            $credentials = [
                'user' => (string) ($migrateCredentials['email'] ?? ''),
                'password' => (string) ($migrateCredentials['password'] ?? ''),
            ];
        } else {
            $credentials = [
                'user' => preg_replace('/\D+/', '', (string) ($item['rut'] ?? '')),
                'password' => $this->resolveDynamicaPassword($item),
            ];
        }

        $mailResult = $this->onboardingMailer->sendCredentialsNotice($item, $credentials, [
            'override_email' => $overrideEmail,
        ]);

        if (!empty($mailResult['skipped'])) {
            $description = 'Correo de credenciales no aplica para esta licencia.';
        } else {
            $description = 'Correo de credenciales enviado correctamente.';
        }

        $this->registerWorkflowEvent(
            (int) $item['id'],
            'ALTA_FINAL',
            'HITO_ENVIO_CREDENCIALES',
            $description,
            $usuario,
            $folderBase,
            'AUTO_ENVIO_CREDENCIALES',
            [
                'template' => (string) ($mailResult['template'] ?? ''),
                'to' => implode(';', (array) ($mailResult['to'] ?? [])),
                'user' => (string) ($credentials['user'] ?? ''),
            ]
        );

        return [
            'summary' => $description,
            'credentials' => $credentials,
            'mail' => $mailResult,
        ];
    }

    private function processFinalActivationStage(array $item, string $usuario, string $folderBase): array
    {
        $clienteId = (int) ($item['cliente_id_creado'] ?? 0);
        if ($clienteId <= 0) {
            throw new RuntimeException('No existe ClienteId creado para completar el alta final.');
        }

        $activation = (new ClienteModel())->activateForOnboarding($clienteId, $item);
        $description = 'Cliente activo. Abonado=SI. FechaDesde=' . $activation['billing_start_date'];
        if ((float) ($activation['pn_monto'] ?? 0) > 0) {
            $description .= '. pnCreditoFiscal=' . $activation['pn_credito_fiscal'];
        }

        $this->registerWorkflowEvent(
            (int) $item['id'],
            'CLIENTE_ACTIVO',
            'HITO_CLIENTE_ACTIVO',
            $description,
            $usuario,
            $folderBase,
            'AUTO_ALTA_FINAL',
            $activation
        );

        return $activation;
    }

    private function registerWorkflowEvent(
        int $id,
        string $nextHito,
        string $eventCode,
        string $description,
        string $usuario,
        string $folderBase,
        string $logEvent = 'CAMBIO_HITO',
        array $extraContext = []
    ): void {
        $currentItem = $this->model->findById($id);
        $this->model->updateHitoActual($id, $nextHito, $description);
        $this->historialModel->create([
            'nueva_empresa_id' => $id,
            'evento' => $eventCode,
            'estado_anterior' => $currentItem['estado'] ?? null,
            'estado_nuevo' => $currentItem['estado'] ?? null,
            'descripcion' => $description,
            'usuario_evento' => $usuario,
        ]);

        $this->appendRecordLog($folderBase, $logEvent, array_merge([
            'nueva_empresa_id' => $id,
            'usuario' => $usuario,
            'hito_anterior' => (string) ($currentItem['hito_actual'] ?? ''),
            'hito_nuevo' => $nextHito,
            'evento' => $eventCode,
            'descripcion' => $description,
        ], $extraContext));
    }

    private function resolveOnboardingOverrideEmail(): string
    {
        return MIGRATE_ENVIRONMENT === 'testing'
            ? trim((string) ONBOARDING_TEST_EMAIL)
            : '';
    }

    private function resolveCredentialsScheduledAt(): string
    {
        $timezone = new DateTimeZone('America/Montevideo');
        $scheduledAt = (new DateTimeImmutable('now', $timezone))
            ->setTime(8, 0, 0)
            ->modify('+1 day');

        while (in_array((int) $scheduledAt->format('N'), [6, 7], true)) {
            $scheduledAt = $scheduledAt->modify('+1 day');
        }

        return $scheduledAt->format('Y-m-d H:i:s');
    }

    public function processDeferredOnboardingQueue(int $limit = 20): array
    {
        $messages = [];
        $read = 0;
        $success = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($this->hitoAutoModel->listDueTasks('ENVIO_CREDENCIALES', $limit) as $task) {
            $read++;
            $taskId = (int) ($task['Id'] ?? 0);
            $nuevaEmpresaId = (int) ($task['NuevaEmpresaId'] ?? 0);
            if ($taskId <= 0 || $nuevaEmpresaId <= 0) {
                $skipped++;
                continue;
            }

            if (!$this->hitoAutoModel->markProcessing($taskId)) {
                $skipped++;
                continue;
            }

            $db = Db::conn();
            try {
                $db->beginTransaction();

                $item = $this->model->findByIdForUpdate($nuevaEmpresaId);
                if (!$item) {
                    throw new RuntimeException('No se encontro el registro temporal asociado a la tarea diferida.');
                }

                $currentHito = strtoupper(trim((string) ($item['hito_actual'] ?? '')));
                if ($currentHito === 'CLIENTE_ACTIVO') {
                    $db->commit();
                    $this->hitoAutoModel->markSuccess($taskId, [
                        'skipped' => true,
                        'reason' => 'El cliente ya estaba activo.',
                    ]);
                    $messages[] = '[SKIP] #' . $nuevaEmpresaId . ' ya estaba en cliente activo.';
                    $skipped++;
                    continue;
                }

                if (!in_array($currentHito, ['ENVIO_CREDENCIALES', 'ALTA_FINAL'], true)) {
                    throw new RuntimeException('El caso no esta listo para procesar credenciales diferidas. Hito actual: ' . $currentHito);
                }

                $folderBase = (string) ($item['carpeta_base'] ?? '');
                $overrideEmail = $this->resolveOnboardingOverrideEmail();
                $credentialsInfo = $this->processCredentialsStage($item, 'sistema', $folderBase, $overrideEmail);

                $itemAfterCredentials = $this->model->findByIdForUpdate($nuevaEmpresaId) ?? $item;
                $activationInfo = $this->processFinalActivationStage($itemAfterCredentials, 'sistema', $folderBase);

                $db->commit();
                $this->hitoAutoModel->markSuccess($taskId, [
                    'credentials' => $credentialsInfo,
                    'activation' => $activationInfo,
                ]);

                $messages[] = '[OK] #' . $nuevaEmpresaId . ' credenciales y alta final completadas.';
                $success++;
            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }

                $this->hitoAutoModel->markError($taskId, $e->getMessage(), [
                    'nueva_empresa_id' => $nuevaEmpresaId,
                ]);
                $this->appendRecordLog('', 'AUTO_HITOS_DIFERIDOS_ERROR', [
                    'nueva_empresa_id' => $nuevaEmpresaId,
                    'usuario' => 'sistema',
                    'error' => $e->getMessage(),
                    'task_id' => $taskId,
                ]);

                $messages[] = '[ERROR] #' . $nuevaEmpresaId . ': ' . $e->getMessage();
                $errors++;
            }
        }

        return [
            'read' => $read,
            'success' => $success,
            'errors' => $errors,
            'skipped' => $skipped,
            'messages' => $messages,
        ];
    }

    private function resolveDynamicaPassword(array $item): string
    {
        $sourceDate = (string) (($item['fecha_aprobacion'] ?? '') ?: ($item['fecha_creacion'] ?? ''));
        $ts = strtotime($sourceDate);
        return $ts ? date('dmY', $ts) : '';
    }

    public function runMigrate(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id <= 0) {
            Response::redirect('index.php');
        }

        $db = Db::conn();
        $empresaModel = new EmpresaModel();
        $usuario = Auth::user() ?? ['login' => $_SESSION['usuario'] ?? 'admin'];

        try {
            $db->beginTransaction();

            $item = $this->model->findByIdForUpdate($id);
            if (!$item) {
                throw new RuntimeException('Registro no encontrado.');
            }

            if ((string) ($item['estado'] ?? '') === ESTADO_ELIMINADO) {
                throw new RuntimeException('No es posible ejecutar Migrate sobre un registro eliminado.');
            }

            if (empty($item['empresa_id_creada'])) {
                throw new RuntimeException('Primero debe aprobar el alta para crear la empresa y el cliente.');
            }

            $archivos = $this->archivoModel->listByNuevaEmpresaId($id);
            $references = $this->buildMigrateReferences($item);
            $this->appendRecordLog((string) ($item['carpeta_base'] ?? ''), 'MIGRATE_INICIO', [
                'nueva_empresa_id' => $id,
                'usuario' => (string) ($usuario['login'] ?? 'admin'),
                'empresa_id_creada' => $item['empresa_id_creada'] ?? '',
                'cliente_id_creado' => $item['cliente_id_creado'] ?? '',
                'licencia' => $item['licencia'] ?? '',
                'licencia_texto' => $references['licencia_texto'] ?? '',
                'migrate_environment' => defined('MIGRATE_ENVIRONMENT') ? (string) MIGRATE_ENVIRONMENT : 'production',
                'migrate_wsdl' => defined('MIGRATE_REGISTROEMPRESA_WSDL') ? (string) MIGRATE_REGISTROEMPRESA_WSDL : '',
                'referencias' => $references,
                'cantidad_adjuntos' => count($archivos),
            ]);
            $result = $this->migrateService->registerCompany($item, $archivos, $references, $usuario);

            $this->storeMigrateArtifacts((string) ($item['carpeta_base'] ?? ''), $result['request_xml'] ?? '', $result['response_xml'] ?? '');
            $this->model->storeMigrateExchange($id, (string) ($result['request_xml'] ?? ''), (string) ($result['response_xml'] ?? ''));

            $empresaInvoicy = trim((string) ($result['empresa_invoicy'] ?? ''));
            $claveAcceso = trim((string) ($result['suc_clave_acceso'] ?? ''));
            $migrateUserCredentials = $this->migrateService->resolveExpectedMigrateUserCredentials($item);
            $alreadyRegistered = $this->isMigrateAlreadyRegistered($result);
            $licMsgRetorno = trim((string) ($result['lic_msg_retorno'] ?? ''));
            $empresaLocal = !empty($item['empresa_id_creada'])
                ? $empresaModel->findById((int) $item['empresa_id_creada'])
                : null;

            if ($empresaInvoicy === '' && !empty($empresaLocal['EmpresaInvoicy'])) {
                $empresaInvoicy = trim((string) $empresaLocal['EmpresaInvoicy']);
            }
            if ($claveAcceso === '' && !empty($empresaLocal['Clave'])) {
                $claveAcceso = trim((string) $empresaLocal['Clave']);
            }

            if ($alreadyRegistered && ($empresaInvoicy === '' || $claveAcceso === '')) {
                $rescued = $this->recoverStoredMigrateCredentials(
                    (string) ($item['carpeta_base'] ?? ''),
                    (string) ($item['rut'] ?? '')
                );
                if ($empresaInvoicy === '' && !empty($rescued['empresa_invoicy'])) {
                    $empresaInvoicy = (string) $rescued['empresa_invoicy'];
                }
                if ($claveAcceso === '' && !empty($rescued['suc_clave_acceso'])) {
                    $claveAcceso = (string) $rescued['suc_clave_acceso'];
                }
            }

            if ($alreadyRegistered && ($empresaInvoicy === '' || $claveAcceso === '')) {
                $message = 'Empresa ya registrada en Migrate, pero no se pudieron recuperar EmpCodigo y Clave del mismo RUT. Revisar manualmente antes de continuar.';
                $this->model->markMigrateError($id, $message);
                $this->historialModel->create([
                    'nueva_empresa_id' => $id,
                    'evento' => 'HITO_MIGRATE_ERROR',
                    'estado_anterior' => $item['estado'] ?? null,
                    'estado_nuevo' => ESTADO_ERROR_APROBACION,
                    'descripcion' => $message,
                    'usuario_evento' => (string) ($usuario['login'] ?? 'admin'),
                ]);

                $db->commit();
                $this->appendRecordLog((string) ($item['carpeta_base'] ?? ''), 'MIGRATE_DUPLICADO_SIN_CREDENCIALES', [
                    'nueva_empresa_id' => $id,
                    'usuario' => (string) ($usuario['login'] ?? 'admin'),
                    'rut' => $item['rut'] ?? '',
                    'empresa_id_creada' => $item['empresa_id_creada'] ?? '',
                    'msg_code' => $result['msg_code'] ?? '',
                    'msg_desc' => $result['msg_desc'] ?? '',
                    'errors' => $result['errors'] ?? [],
                ]);
                Response::flash('error', $message);
                Response::redirect('index.php?action=show&id=' . $id);
            }

            if (
                !$alreadyRegistered
                && $empresaInvoicy !== ''
                && $claveAcceso !== ''
                && (
                    !$result['lic_success']
                    || !empty($result['licensing_errors'])
                    || !empty($result['user_errors'])
                )
            ) {
                $message = $this->normalizeMigrateErrors($result);
                $this->model->markMigrateError($id, $message);
                $this->historialModel->create([
                    'nueva_empresa_id' => $id,
                    'evento' => 'HITO_MIGRATE_ERROR',
                    'estado_anterior' => $item['estado'] ?? null,
                    'estado_nuevo' => ESTADO_ERROR_APROBACION,
                    'descripcion' => $message,
                    'usuario_evento' => (string) ($usuario['login'] ?? 'admin'),
                ]);

                $db->commit();
                $this->appendRecordLog((string) ($item['carpeta_base'] ?? ''), 'MIGRATE_LICENCIAMIENTO_ERROR', [
                    'nueva_empresa_id' => $id,
                    'usuario' => (string) ($usuario['login'] ?? 'admin'),
                    'empresa_id_creada' => $item['empresa_id_creada'] ?? '',
                    'empresa_invoicy' => $empresaInvoicy,
                    'suc_clave_acceso' => $claveAcceso,
                    'migrate_environment' => $result['environment'] ?? '',
                    'migrate_wsdl' => $result['wsdl'] ?? '',
                    'msg_code' => $result['msg_code'] ?? '',
                    'msg_desc' => $result['msg_desc'] ?? '',
                    'lic_msg_retorno' => $licMsgRetorno,
                    'licensing_errors' => $result['licensing_errors'] ?? [],
                    'user_errors' => $result['user_errors'] ?? [],
                    'errors' => $result['errors'] ?? [],
                    'request_xml_bytes' => strlen((string) ($result['request_xml'] ?? '')),
                    'response_xml_bytes' => strlen((string) ($result['response_xml'] ?? '')),
                ]);
                Response::flash('error', $message);
                Response::redirect('index.php?action=show&id=' . $id);
            }

            if (!empty($result['success']) || $alreadyRegistered) {
                $detail = !empty($result['success']) ? 'Migrate OK' : 'Empresa ya está registrada en Migrate.';
                if ($empresaInvoicy !== '') {
                    $detail .= ' - EmpCodigo ' . $empresaInvoicy;
                }

                $this->model->markMigrateSuccess($id, $detail);
                $this->historialModel->create([
                    'nueva_empresa_id' => $id,
                    'evento' => 'HITO_MIGRATE_OK',
                    'estado_anterior' => $item['estado'] ?? null,
                    'estado_nuevo' => ESTADO_APROBADO,
                    'descripcion' => $detail,
                    'usuario_evento' => (string) ($usuario['login'] ?? 'admin'),
                ]);
                $this->historialModel->create([
                    'nueva_empresa_id' => $id,
                    'evento' => WorkflowHelper::licenseCreatesMigrateUser((int) ($item['licencia'] ?? 0)) ? 'HITO_MIGRATE_USUARIO_ENVIADO' : 'HITO_MIGRATE_USUARIO_NA',
                    'estado_anterior' => ESTADO_APROBADO,
                    'estado_nuevo' => ESTADO_APROBADO,
                    'descripcion' => WorkflowHelper::licenseCreatesMigrateUser((int) ($item['licencia'] ?? 0))
                        ? 'Usuario Migrate enviado dentro del RegistroEmpresa. Validar alta efectiva en Migrate.'
                        : WorkflowHelper::migrateUserSubstepLabel((int) ($item['licencia'] ?? 0)),
                    'usuario_evento' => (string) ($usuario['login'] ?? 'admin'),
                ]);

                if ($empresaInvoicy !== '' && $claveAcceso !== '') {
                    $empresaModel->updateMigrateCredentials(
                        (int) $item['empresa_id_creada'],
                        $empresaInvoicy,
                        $claveAcceso,
                        WorkflowHelper::licenseCreatesMigrateUser((int) ($item['licencia'] ?? 0)) ? (string) ($migrateUserCredentials['email'] ?? '') : null,
                        WorkflowHelper::licenseCreatesMigrateUser((int) ($item['licencia'] ?? 0)) ? (string) ($migrateUserCredentials['password'] ?? '') : null
                    );
                }

                $db->commit();
                $this->appendRecordLog((string) ($item['carpeta_base'] ?? ''), 'MIGRATE_OK', [
                    'nueva_empresa_id' => $id,
                    'usuario' => (string) ($usuario['login'] ?? 'admin'),
                    'empresa_id_creada' => $item['empresa_id_creada'] ?? '',
                    'empresa_invoicy' => $empresaInvoicy,
                    'suc_clave_acceso' => $claveAcceso,
                    'migrate_environment' => $result['environment'] ?? '',
                    'migrate_wsdl' => $result['wsdl'] ?? '',
                    'msg_code' => $result['msg_code'] ?? '',
                    'msg_desc' => $result['msg_desc'] ?? '',
                    'errors' => $result['errors'] ?? [],
                    'already_registered' => $alreadyRegistered,
                    'request_xml_bytes' => strlen((string) ($result['request_xml'] ?? '')),
                    'response_xml_bytes' => strlen((string) ($result['response_xml'] ?? '')),
                ]);
                Response::flash('success', $alreadyRegistered
                    ? 'La empresa ya existia en Migrate. Se tomo como alta existente y el onboarding avanzo a Pendiente DGI.'
                    : 'Migrate respondio correctamente y el onboarding avanzo a Pendiente DGI.');
                Response::redirect('index.php?action=show&id=' . $id);
            }

            $message = $this->normalizeMigrateErrors($result);
            $this->model->markMigrateError($id, $message);
            $this->historialModel->create([
                'nueva_empresa_id' => $id,
                'evento' => 'HITO_MIGRATE_ERROR',
                'estado_anterior' => $item['estado'] ?? null,
                'estado_nuevo' => ESTADO_ERROR_APROBACION,
                'descripcion' => $message,
                'usuario_evento' => (string) ($usuario['login'] ?? 'admin'),
            ]);

            $db->commit();
            $this->appendRecordLog((string) ($item['carpeta_base'] ?? ''), 'MIGRATE_ERROR', [
                'nueva_empresa_id' => $id,
                'usuario' => (string) ($usuario['login'] ?? 'admin'),
                'migrate_environment' => $result['environment'] ?? '',
                'migrate_wsdl' => $result['wsdl'] ?? '',
                'msg_code' => $result['msg_code'] ?? '',
                'msg_desc' => $result['msg_desc'] ?? '',
                'errors' => $result['errors'] ?? [],
                'request_xml_bytes' => strlen((string) ($result['request_xml'] ?? '')),
                'response_xml_bytes' => strlen((string) ($result['response_xml'] ?? '')),
            ]);
            Response::flash('error', $message);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            if (isset($item) && is_array($item)) {
                $this->appendRecordLog((string) ($item['carpeta_base'] ?? ''), 'MIGRATE_EXCEPCION', [
                    'nueva_empresa_id' => $id,
                    'usuario' => (string) ($usuario['login'] ?? 'admin'),
                    'migrate_environment' => defined('MIGRATE_ENVIRONMENT') ? (string) MIGRATE_ENVIRONMENT : 'production',
                    'migrate_wsdl' => defined('MIGRATE_REGISTROEMPRESA_WSDL') ? (string) MIGRATE_REGISTROEMPRESA_WSDL : '',
                    'error' => $e->getMessage(),
                ]);
            }

            Response::flash('error', 'No fue posible ejecutar Migrate: ' . $e->getMessage());
        }

        Response::redirect('index.php?action=show&id=' . $id);
    }

    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id <= 0) {
            Response::redirect('index.php');
        }

        $item = $this->model->findById($id);
        if (!$item) {
            Response::flash('error', 'Registro no encontrado.');
            Response::redirect('index.php');
        }

        $motivo = trim($_POST['motivo_eliminacion'] ?? '');
        $usuario = $_SESSION['usuario'] ?? 'admin';

        try {
            if (!empty($item['carpeta_base'])) {
                FileStorage::deleteFolderByRelative((string) $item['carpeta_base']);
            }
            $this->archivoModel->deleteByNuevaEmpresaId($id);
            $this->model->markDeleted($id, $usuario, $motivo);
            $this->historialModel->create([
                'nueva_empresa_id' => $id,
                'evento' => 'ELIMINACION',
                'estado_anterior' => $item['estado'],
                'estado_nuevo' => ESTADO_ELIMINADO,
                'descripcion' => $motivo !== '' ? $motivo : 'Registro eliminado por admin',
                'usuario_evento' => $usuario,
            ]);

            Response::flash('success', 'Registro eliminado correctamente.');
        } catch (Throwable $e) {
            Response::flash('error', 'No fue posible eliminar el registro.');
        }

        Response::redirect('index.php');
    }

    private function buildMigrateReferences(array $item): array
    {
        return [
            'giro_nombre' => $this->resolveOptionLabel($this->catalogoModel->listGiros(ID_EMPRESA_MASTER), 'id', 'nombre', (string) ($item['cliente_id_giro'] ?? '')),
            'ciudad_nombre' => $this->resolveOptionLabel($this->catalogoModel->listCiudades(ID_EMPRESA_MASTER), 'id', 'nombre', (string) ($item['ciudad'] ?? '')),
            'departamento_nombre' => $this->resolveOptionLabel($this->catalogoModel->listDepartamentos(ID_EMPRESA_MASTER), 'id', 'nombre', (string) ($item['departamento'] ?? '')),
            'licencia_texto' => self::licenseLabel((int) ($item['licencia'] ?? 0)),
            'vendedor_nombre' => $this->resolveOptionLabel($this->catalogoModel->listVendedores(ID_EMPRESA_MASTER), 'id', 'nombre', (string) ($item['cliente_id_vendedor'] ?? '')),
        ];
    }

    private function resolveOptionLabel(array $options, string $idKey, string $labelKey, string $value): string
    {
        foreach ($options as $option) {
            if ((string) ($option[$idKey] ?? '') === $value) {
                return trim((string) ($option[$labelKey] ?? $value));
            }
        }

        return $value;
    }

    private function normalizeMigrateErrors(array $result): string
    {
        $errors = array_values(array_filter(array_map('trim', $result['errors'] ?? []), static function (string $value): bool {
            return $value !== '';
        }));

        if ($errors !== []) {
            return implode(' | ', $errors);
        }

        $msg = trim((string) ($result['msg_desc'] ?? ''));
        if ($msg !== '') {
            return $msg;
        }

        return 'Migrate no devolvio un resultado utilizable.';
    }

    private function isMigrateAlreadyRegistered(array $result): bool
    {
        $errors = array_values(array_filter(array_map('trim', $result['errors'] ?? []), static function (string $value): bool {
            return $value !== '';
        }));

        foreach ($errors as $error) {
            if (mb_stripos($error, 'Empresa ya está registrada') !== false || mb_stripos($error, 'Empresa ya esta registrada') !== false) {
                return true;
            }
        }

        return false;
    }

    private function recoverStoredMigrateCredentials(string $folderRelative, string $expectedRut): array
    {
        $result = [
            'empresa_invoicy' => '',
            'suc_clave_acceso' => '',
        ];

        if ($folderRelative === '') {
            return $result;
        }

        $folderAbsolute = FileStorage::absoluteFromRelative($folderRelative);
        if (!is_dir($folderAbsolute)) {
            return $result;
        }

        $files = glob(rtrim($folderAbsolute, '\\/') . DIRECTORY_SEPARATOR . 'migrate_registroempresa_response_*.xml');
        if (!$files) {
            return $result;
        }

        rsort($files, SORT_STRING);
        foreach ($files as $file) {
            $xmlContent = @file_get_contents($file);
            if ($xmlContent === false || trim($xmlContent) === '') {
                continue;
            }

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlContent);
            libxml_clear_errors();
            if ($xml === false) {
                continue;
            }

            $responseRut = preg_replace('/\D+/', '', trim((string) (($xml->xpath('//DatosEmpresa/EmpRUT')[0] ?? ''))));
            $normalizedExpectedRut = preg_replace('/\D+/', '', $expectedRut);
            if ($normalizedExpectedRut !== '' && $responseRut !== '' && $responseRut !== $normalizedExpectedRut) {
                continue;
            }

            $empresa = trim((string) (($xml->xpath('//DatosSucursal/EmpCodigo')[0] ?? '')));
            $clave = trim((string) (($xml->xpath('//DatosSucursal/SucClaveAcceso')[0] ?? '')));
            if ($empresa !== '' || $clave !== '') {
                $result['empresa_invoicy'] = $empresa;
                $result['suc_clave_acceso'] = $clave;
                return $result;
            }
        }

        return $result;
    }

    private function storeMigrateArtifacts(string $folderRelative, string $requestXml, string $responseXml): void
    {
        if ($folderRelative === '') {
            return;
        }

        $folderAbsolute = FileStorage::absoluteFromRelative($folderRelative);
        if (!is_dir($folderAbsolute)) {
            return;
        }

        $stamp = date('Ymd_His');
        if ($requestXml !== '') {
            @file_put_contents($folderAbsolute . DIRECTORY_SEPARATOR . 'migrate_registroempresa_request_' . $stamp . '.xml', $requestXml);
        }
        if ($responseXml !== '') {
            @file_put_contents($folderAbsolute . DIRECTORY_SEPARATOR . 'migrate_registroempresa_response_' . $stamp . '.xml', $responseXml);
        }
    }

    private function appendRecordLog(string $folderRelative, string $event, array $context = []): void
    {
        try {
            FileStorage::appendOnboardingLog($folderRelative, $event, $context);
        } catch (Throwable $e) {
            // El log nunca debe romper el flujo principal.
        }
    }

    private function lookupRutStatus(string $rut, int $nuevaEmpresaId, EmpresaModel $empresaModel, ClienteModel $clienteModel): array
    {
        $currentTemp = $nuevaEmpresaId > 0 ? $this->model->findById($nuevaEmpresaId) : null;
        $currentEmpresaId = (int) ($currentTemp['empresa_id_creada'] ?? 0);
        $empresa = $empresaModel->findByRut($rut);
        $cliente = $clienteModel->findByDocumentoAndEmpresa($rut, ID_EMPRESA_MASTER);

        $empresaExists = $empresa !== null;
        if ($empresaExists && $currentEmpresaId > 0 && (int) ($empresa['IdEmpresa'] ?? 0) === $currentEmpresaId) {
            $empresaExists = false;
        }

        $clienteExists = $cliente !== null;

        $message = '';
        $severity = 'ok';

        if ($empresaExists) {
            $severity = 'error';
            $message = 'El RUT ya esta registrado como empresa en Dynamica.';
        } elseif ($clienteExists) {
            $severity = 'warning';
            $message = 'El RUT ya existe como cliente en la empresa 397, pero no como empresa.';
        }

        return [
            'ok' => true,
            'rut' => $rut,
            'empresa_exists' => $empresaExists,
            'empresa' => $empresa,
            'cliente_exists' => $clienteExists,
            'cliente' => $cliente,
            'severity' => $severity,
            'message' => $message,
        ];
    }
}

