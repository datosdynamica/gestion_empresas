<?php

declare(strict_types=1);

@set_time_limit(0);
@ini_set('memory_limit', '512M');

require dirname(__DIR__) . '/bootstrap.php';

$startedAt = microtime(true);
$startedLabel = date('Y-m-d H:i:s');
fwrite(STDOUT, "[{$startedLabel}] Inicio refresh_certificate_cache" . PHP_EOL);

$force = in_array('--force', $argv, true);
$limit = 0;
$chunkSize = max(1, (int) MIGRATE_CERT_BATCH_SIZE);

foreach ($argv as $arg) {
    if (strpos($arg, '--limit=') === 0) {
        $limit = max(0, (int) substr($arg, 8));
    }
    if (strpos($arg, '--chunk=') === 0) {
        $chunkSize = max(1, (int) substr($arg, 8));
    }
}

$empresaModel = new EmpresaModel();
$cacheModel = new MigrateCertificateCacheModel();
$migrateService = new MigrateInvoicyService();
$controller = new NuevasEmpresasController();

$activeCompanies = $empresaModel->listActiveCertificateCandidates();
$companyMap = [];
foreach ($activeCompanies as $company) {
    $normalizedRut = preg_replace('/\D+/', '', (string) ($company['Rut'] ?? ''));
    if ($normalizedRut === '') {
        continue;
    }

    $companyMap[(int) $company['IdEmpresa']] = [
        'empresa_id' => (int) ($company['IdEmpresa'] ?? 0),
        'rut' => $normalizedRut,
        'razon_social' => (string) ($company['RazonSocial'] ?? ''),
        'empresa_invoicy' => (string) ($company['EmpresaInvoicy'] ?? ''),
        'clave' => (string) ($company['Clave'] ?? ''),
    ];
}

if ($companyMap === []) {
    fwrite(STDOUT, "No hay empresas activas con RUT disponible.\n");
    fwrite(STDOUT, '[' . date('Y-m-d H:i:s') . "] Fin refresh_certificate_cache (sin empresas activas)\n");
    exit(0);
}

$statusMap = $cacheModel->listStatusMap(array_keys($companyMap), (string) MIGRATE_ENVIRONMENT);
$staleCompanies = [];
$freshCutoff = (new DateTimeImmutable('now'))->modify('-' . max(1, (int) MIGRATE_CERT_CACHE_HOURS) . ' hours');
foreach ($companyMap as $empresaId => $companyData) {
    if ($force) {
        $staleCompanies[$companyData['rut']] = $companyData;
        continue;
    }

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

if ($limit > 0) {
    $staleCompanies = array_slice($staleCompanies, 0, $limit, true);
}

if ($staleCompanies === []) {
    fwrite(STDOUT, "No hay empresas pendientes de refresco.\n");
    fwrite(STDOUT, '[' . date('Y-m-d H:i:s') . "] Fin refresh_certificate_cache (sin pendientes)\n");
    exit(0);
}

$ref = new ReflectionClass($controller);
$method = $ref->getMethod('resolveCertificateServiceCredentials');
$method->setAccessible(true);
$credentials = $method->invoke($controller, $empresaModel);
$companyMethod = $ref->getMethod('resolveCertificateCredentialsForCompany');
$companyMethod->setAccessible(true);
$noDataMethod = $ref->getMethod('isCertificateNoDataResult');
$noDataMethod->setAccessible(true);

$total = count($staleCompanies);
$processed = 0;
$errors = 0;
fwrite(STDOUT, "Empresas a refrescar: {$total}\n");

foreach (array_chunk(array_keys($staleCompanies), $chunkSize) as $chunkIndex => $rutChunk) {
    foreach ($rutChunk as $rut) {
        $company = $staleCompanies[$rut];
        $companyCredentials = $companyMethod->invoke($controller, $company, $credentials);
        try {
            $result = $migrateService->queryCertificates([
                'emp_codigo' => $companyCredentials['emp_codigo'],
                'hash_key' => $companyCredentials['hash_key'],
                'emp_pk' => $companyCredentials['emp_pk'],
                'cer_status' => 'A',
                'cer_intervalo' => 0,
                'emp_ruc' => $rut,
            ]);
        } catch (Throwable $e) {
            $result = [
                'success' => false,
                'msg_code' => '',
                'msg_desc' => '',
                'errors' => [$e->getMessage()],
                'items' => [],
                'request_xml' => '',
                'response_xml' => '',
            ];
        }

        $handled = !empty($result['success']) || $noDataMethod->invoke($controller, $result);
        if (!$handled) {
            $errors++;
        }

        $snapshotMeta = [
            'environment' => (string) ($result['environment'] ?? MIGRATE_ENVIRONMENT),
            'wsdl' => (string) ($result['wsdl'] ?? MIGRATE_CONSULTAEMPRESAS_WSDL),
            'msg_code' => (string) ($result['msg_code'] ?? ''),
            'msg_desc' => (string) ($result['msg_desc'] ?? ''),
            'error_summary' => implode(' | ', (array) ($result['errors'] ?? [])),
            'request_xml' => (string) ($result['request_xml'] ?? ''),
            'response_xml' => (string) ($result['response_xml'] ?? ''),
            'fecha_consulta' => date('Y-m-d H:i:s'),
        ];

        $cacheModel->replaceCompanySnapshot(
            $company,
            (array) ($result['items'] ?? []),
            $snapshotMeta
        );
        $processed++;
    }

    fwrite(
        STDOUT,
        'Lote ' . ($chunkIndex + 1) . ': ' . count($rutChunk) . " empresas procesadas.\n"
    );
}

fwrite(STDOUT, "Refresco terminado. Procesadas={$processed}, errores={$errors}\n");
$duration = round(microtime(true) - $startedAt, 2);
fwrite(STDOUT, '[' . date('Y-m-d H:i:s') . "] Fin refresh_certificate_cache | duracion_segundos={$duration}\n");
