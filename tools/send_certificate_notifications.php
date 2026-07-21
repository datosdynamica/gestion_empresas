<?php

declare(strict_types=1);

@set_time_limit(0);
@ini_set('memory_limit', '512M');

require dirname(__DIR__) . '/bootstrap.php';

$daysTargets = [30, 20, 10, 5, 1];
$limit = 0;
$dryRun = in_array('--dry-run', $argv, true);
$force = in_array('--force', $argv, true);
$overrideEmail = '';
$sampleEmpresaId = 0;
$overrideDaysRemaining = null;

foreach ($argv as $arg) {
    if (strpos($arg, '--limit=') === 0) {
        $limit = max(0, (int) substr($arg, 8));
    }
    if (strpos($arg, '--days=') === 0) {
        $parsedDays = array_map('intval', array_filter(array_map('trim', explode(',', substr($arg, 7))), static function (string $value): bool {
            return $value !== '';
        }));
        if ($parsedDays !== []) {
            $daysTargets = array_values(array_unique($parsedDays));
        }
    }
    if (strpos($arg, '--override-email=') === 0) {
        $overrideEmail = trim((string) substr($arg, strlen('--override-email=')));
    }
    if (strpos($arg, '--empresa-id=') === 0) {
        $sampleEmpresaId = max(0, (int) substr($arg, strlen('--empresa-id=')));
    }
    if (strpos($arg, '--override-days=') === 0) {
        $overrideDaysRemaining = max(0, (int) substr($arg, strlen('--override-days=')));
    }
}

$empresaModel = new EmpresaModel();
$cacheModel = new MigrateCertificateCacheModel();
$notificationModel = new CertificateNotificationModel();
$actionModel = new CertificateActionModel();
$mailer = new CertificateNotificationMailer();

$companyMap = [];
foreach ($empresaModel->listActiveCertificateCandidates() as $company) {
    $empresaId = (int) ($company['IdEmpresa'] ?? 0);
    $rut = preg_replace('/\D+/', '', (string) ($company['Rut'] ?? ''));
    if ($empresaId <= 0 || $rut === '') {
        continue;
    }

    $companyMap[$empresaId] = [
        'empresa_id' => $empresaId,
        'rut' => $rut,
        'razon_social' => (string) ($company['RazonSocial'] ?? ''),
        'email' => trim((string) (($company['ClienteEmail'] ?? '') !== '' ? $company['ClienteEmail'] : ($company['EmpresaEmail'] ?? ''))),
        'email_envio_fe' => trim((string) ($company['emailEnvioFE'] ?? '')),
        'telefono' => trim((string) ($company['Tel'] ?? '')),
    ];
}

$candidates = $cacheModel->listNotificationCandidates($daysTargets, (string) MIGRATE_ENVIRONMENT, $limit);

if ($overrideEmail !== '' && ($sampleEmpresaId <= 0 || $overrideDaysRemaining === null)) {
    fwrite(STDERR, "El modo de prueba con override_email exige --empresa-id y --override-days.\n");
    exit(1);
}

if ($sampleEmpresaId > 0) {
    $sampleCompany = $companyMap[$sampleEmpresaId] ?? null;
    if ($sampleCompany === null) {
        fwrite(STDERR, "No se encontro la empresa indicada para prueba.\n");
        exit(1);
    }

    if ($overrideDaysRemaining === null) {
        fwrite(STDERR, "Para modo empresa-id debe indicar --override-days=N.\n");
        exit(1);
    }

    $overrideExpiry = (new DateTimeImmutable('today'))->modify('+' . $overrideDaysRemaining . ' days')->format('Y-m-d');
    $candidates = [[
        'EmpresaId' => $sampleEmpresaId,
        'Rut' => $sampleCompany['rut'],
        'RazonSocial' => $sampleCompany['razon_social'],
        'Apodo' => 'Prueba controlada',
        'CerStatus' => 'A',
        'DiasRestantes' => $overrideDaysRemaining,
        'CerFchVencimiento' => $overrideExpiry,
        'FechaConsulta' => date('Y-m-d H:i:s'),
    ]];
}

$processed = 0;
$sent = 0;
$skipped = 0;
$errors = 0;

fwrite(STDOUT, 'Entorno: ' . MIGRATE_ENVIRONMENT . PHP_EOL);
fwrite(STDOUT, 'Candidatos encontrados: ' . count($candidates) . PHP_EOL);

foreach ($candidates as $row) {
    $processed++;
    $empresaId = (int) ($row['EmpresaId'] ?? 0);
    $diasRestantes = (int) ($row['DiasRestantes'] ?? 0);
    $fechaVencimiento = trim((string) ($row['CerFchVencimiento'] ?? ''));
    $company = $companyMap[$empresaId] ?? null;

    if ($company === null) {
        $errors++;
        fwrite(STDOUT, "[ERROR] Empresa {$empresaId}: no se encontro informacion base para notificar." . PHP_EOL);
        continue;
    }

    if (!$force && $notificationModel->wasSent($empresaId, $company['rut'], $fechaVencimiento, $diasRestantes)) {
        $skipped++;
        fwrite(STDOUT, "[SKIP] {$company['razon_social']} ({$company['rut']}): ya existe envio para {$diasRestantes} dia(s)." . PHP_EOL);
        continue;
    }

    try {
        if ($dryRun) {
            $sentPayload = [
                'subject' => 'DRY RUN',
                'body_html' => '',
                'to' => [],
                'cc' => [],
            ];
        } else {
            $sentPayload = $mailer->sendCertificateReminder(
                $company,
                [
                    'apodo' => (string) ($row['Apodo'] ?? ''),
                    'cer_status' => (string) ($row['CerStatus'] ?? ''),
                    'dias_restantes' => $diasRestantes,
                    'cer_fch_vencimiento' => $fechaVencimiento,
                ],
                $diasRestantes,
                [
                    'override_email' => $overrideEmail,
                ]
            );
        }

        $notificationModel->create([
            'empresa_id' => $empresaId,
            'rut' => $company['rut'],
            'razon_social' => $company['razon_social'],
            'apodo' => (string) ($row['Apodo'] ?? ''),
            'cer_status' => (string) ($row['CerStatus'] ?? ''),
            'dias_objetivo' => $diasRestantes,
            'dias_restantes' => $diasRestantes,
            'fecha_vencimiento' => $fechaVencimiento,
            'tipo_notificacion' => 'CERTIFICADO_VENCIMIENTO',
            'destinatarios' => implode('; ', (array) ($sentPayload['to'] ?? [])),
            'copias' => implode('; ', array_filter(array_merge(
                (array) ($sentPayload['cc'] ?? []),
                array_filter([(string) ($sentPayload['support_cc'] ?? '')])
            ))),
            'asunto' => (string) ($sentPayload['subject'] ?? ''),
            'plantilla' => CertificateNotificationMailer::templateFileName(),
            'estado' => $dryRun ? 'DRY_RUN' : 'ENVIADO',
            'detalle' => $dryRun ? 'Simulacion sin envio real.' : 'Correo enviado correctamente.',
            'body_html' => (string) ($sentPayload['body_html'] ?? ''),
            'payload_json' => json_encode([
                'company' => $company,
                'cache_row' => $row,
                'sent_payload' => $sentPayload,
                'environment' => MIGRATE_ENVIRONMENT,
                'dry_run' => $dryRun,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        if (!$dryRun) {
            $daysLabel = $diasRestantes === 1 ? '1 dia' : ($diasRestantes . ' dias');
            $destinatarios = implode('; ', (array) ($sentPayload['to'] ?? []));
            $actionModel->create([
                'empresa_id' => $empresaId,
                'rut' => $company['rut'],
                'accion' => 'AVISO_CORREO',
                'descripcion' => 'Recordatorio automatico de certificado enviado por correo. Ventana: ' . $daysLabel . '. Destinatarios: ' . $destinatarios . '.',
                'usuario_login' => 'sistema',
                'usuario_nombre' => 'Sistema',
            ]);
        }

        $sent++;
        fwrite(STDOUT, '[OK] ' . $company['razon_social'] . ' (' . $company['rut'] . ') - ' . $diasRestantes . " dia(s)." . PHP_EOL);
    } catch (Throwable $e) {
        $errors++;
        $notificationModel->create([
            'empresa_id' => $empresaId,
            'rut' => $company['rut'],
            'razon_social' => $company['razon_social'],
            'apodo' => (string) ($row['Apodo'] ?? ''),
            'cer_status' => (string) ($row['CerStatus'] ?? ''),
            'dias_objetivo' => $diasRestantes,
            'dias_restantes' => $diasRestantes,
            'fecha_vencimiento' => $fechaVencimiento,
            'tipo_notificacion' => 'CERTIFICADO_VENCIMIENTO',
            'destinatarios' => '',
            'copias' => '',
            'asunto' => '',
            'plantilla' => CertificateNotificationMailer::templateFileName(),
            'estado' => 'ERROR',
            'detalle' => $e->getMessage(),
            'body_html' => '',
            'payload_json' => json_encode([
                'company' => $company,
                'cache_row' => $row,
                'environment' => MIGRATE_ENVIRONMENT,
                'dry_run' => $dryRun,
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        fwrite(STDOUT, '[ERROR] ' . $company['razon_social'] . ' (' . $company['rut'] . '): ' . $e->getMessage() . PHP_EOL);
    }
}

fwrite(STDOUT, PHP_EOL . 'Resumen:' . PHP_EOL);
fwrite(STDOUT, 'Procesados=' . $processed . PHP_EOL);
fwrite(STDOUT, 'Enviados=' . $sent . PHP_EOL);
fwrite(STDOUT, 'Omitidos=' . $skipped . PHP_EOL);
fwrite(STDOUT, 'Errores=' . $errors . PHP_EOL);
