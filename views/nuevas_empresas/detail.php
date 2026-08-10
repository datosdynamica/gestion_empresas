<?php
declare(strict_types=1);
$enableCreateModal = false;
$activeNav = 'panel';
$embeddedView = isset($_GET['embed']) && $_GET['embed'] === '1';
$pageSubtitle = 'Consulta, documentos y acciones administrativas del registro.';
require __DIR__ . '/../layout/header.php';
?>
<?php $values = $item; ?>
<?php
function u(string $value): string
{
    return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
}

function detailWorkflowEventsMap(array $history): array
{
    $mapped = [];
    foreach ($history as $event) {
        $mapped[(string) ($event['evento'] ?? '')] = $event;
    }
    return $mapped;
}

function detailHistoricalLicenseIssue(array $history): string
{
    foreach ($history as $event) {
        $evento = (string) ($event['evento'] ?? '');
        $descripcion = trim((string) ($event['descripcion'] ?? ''));
        if ($evento !== 'HITO_MIGRATE_ERROR' || $descripcion === '') {
            continue;
        }

        $normalized = mb_strtolower($descripcion);
        if (mb_strpos($normalized, 'licenciamiento devolvio novedad') !== false
            || mb_strpos($normalized, 'licencia rechazada') !== false
            || mb_strpos($normalized, 'solicitud de licencia rechazada') !== false) {
            return $descripcion;
        }
    }

    return '';
}

function detailMigrateSummary(array $item): array
{
    $requestXml = trim((string) ($item['migrate_request_xml'] ?? ''));
    $responseXml = trim((string) ($item['migrate_response_xml'] ?? ''));
    $summary = [
        'has_data' => $requestXml !== '' || $responseXml !== '',
        'msg_code' => '',
        'msg_desc' => '',
        'errors' => [],
        'empresa_invoicy' => '',
        'suc_clave_acceso' => '',
        'lic_msg_retorno' => '',
        'lic_rejected' => false,
    ];

    if ($responseXml === '') {
        return $summary;
    }

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($responseXml);
    libxml_clear_errors();

    if ($xml === false) {
        $summary['errors'][] = 'No fue posible interpretar el XML de respuesta.';
        return $summary;
    }

    $summary['msg_code'] = trim((string) ($xml->Encabezado->MsgCod ?? ''));
    $summary['msg_desc'] = trim((string) ($xml->Encabezado->MsgDsc ?? ''));

    $empresaNodes = $xml->xpath('//DatosSucursal/EmpCodigo');
    if (is_array($empresaNodes) && isset($empresaNodes[0])) {
        $summary['empresa_invoicy'] = trim((string) $empresaNodes[0]);
    }

    $claveNodes = $xml->xpath('//DatosSucursal/SucClaveAcceso');
    if (is_array($claveNodes) && isset($claveNodes[0])) {
        $summary['suc_clave_acceso'] = trim((string) $claveNodes[0]);
    }

    $licNodes = $xml->xpath('//LicMsgRetorno');
    if (is_array($licNodes) && isset($licNodes[0])) {
        $summary['lic_msg_retorno'] = trim((string) $licNodes[0]);
        $summary['lic_rejected'] = detailIsNegativeMigrateMessage($summary['lic_msg_retorno']);
        if ($summary['lic_msg_retorno'] !== '') {
            $summary['errors'][] = 'Licenciamiento: ' . $summary['lic_msg_retorno'];
        }
    }

    $errorNodes = $xml->xpath('//ErrosItem/*[contains(local-name(), "Desc")]');
    if (is_array($errorNodes)) {
        foreach ($errorNodes as $node) {
            $value = trim((string) $node);
            if ($value !== '') {
                $summary['errors'][] = $value;
            }
        }
    }

    $summary['errors'] = array_values(array_unique($summary['errors']));
    return $summary;
}

function detailIsNegativeMigrateMessage(string $message): bool
{
    $normalized = mb_strtolower(trim($message));
    if ($normalized === '') {
        return false;
    }

    foreach (['rechaz', 'error', 'falla', 'fallo', 'inválid', 'invÃ¡lid', 'invalid', 'deneg', 'no autorizado'] as $needle) {
        if (mb_strpos($normalized, $needle) !== false) {
            return true;
        }
    }

    return false;
}

function detailWorkflowCurrentKey(array $item, array $history): string
{
    $estado = (string) ($item['estado'] ?? '');
    $persisted = trim((string) ($item['hito_actual'] ?? ''));
    $events = detailWorkflowEventsMap($history);
    $empresaCreada = (int) ($item['empresa_creada'] ?? 0) === 1;
    $clienteCreado = (int) ($item['cliente_creado'] ?? 0) === 1;
    $migrateSummary = detailMigrateSummary($item);
    $historicalLicenseIssue = detailHistoricalLicenseIssue($history);

    if ($estado === ESTADO_ELIMINADO) {
        return 'CANCELADO';
    }
    if ($empresaCreada && $clienteCreado && ($migrateSummary['lic_rejected'] || $historicalLicenseIssue !== '')) {
        return 'MIGRATE_ERROR';
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
        if ($persisted === 'PENDIENTE_DGI') {
            return 'HOMOLOGACION_DGI';
        }

        return $persisted;
    }
    if ($estado === ESTADO_ERROR_APROBACION) {
        return ($empresaCreada && $clienteCreado) ? 'MIGRATE' : 'APROBACION_PENDIENTE';
    }
    if ($estado === ESTADO_APROBADO) {
        return ($empresaCreada && $clienteCreado) ? 'CERTIFICADO_DIGITAL' : 'DYNAMICA';
    }

    return $estado === ESTADO_PENDIENTE_APROBACION ? 'APROBACION_PENDIENTE' : 'DYNAMICA';
}

function detailGeneralStatusMeta(array $item, string $currentWorkflow): array
{
    $estado = (string) ($item['estado'] ?? '');
    $empresaCreada = (int) ($item['empresa_creada'] ?? 0) === 1;
    $clienteCreado = (int) ($item['cliente_creado'] ?? 0) === 1;

    if ($estado === ESTADO_ELIMINADO) {
        return [
            'label' => 'Cancelado',
            'class' => 'bg-slate-100 text-slate-700 border-slate-300',
        ];
    }

    if ($estado === ESTADO_ERROR_APROBACION && $empresaCreada && $clienteCreado) {
        return [
            'label' => 'Migrate con novedad',
            'class' => 'bg-rose-50 text-rose-700 border-rose-200',
        ];
    }

    if ($currentWorkflow === 'MIGRATE_ERROR') {
        return [
            'label' => 'Migrate con novedad',
            'class' => 'bg-rose-50 text-rose-700 border-rose-200',
        ];
    }

    if ($currentWorkflow === 'CERTIFICADO_DIGITAL') {
        return [
            'label' => 'Certificado digital',
            'class' => 'bg-amber-50 text-amber-700 border-amber-200',
        ];
    }

    if ($currentWorkflow === 'HOMOLOGACION_DGI') {
        return [
            'label' => 'Homologación DGI',
            'class' => 'bg-amber-50 text-amber-700 border-amber-200',
        ];
    }

    if (in_array($currentWorkflow, ['ENVIO_FACTURA', 'ENVIO_CREDENCIALES', 'ALTA_FINAL'], true)) {
        return [
            'label' => 'Alta pendiente',
            'class' => 'bg-orange-50 text-orange-700 border-orange-200',
        ];
    }

    if ($currentWorkflow === 'ALTA_PENDIENTE') {
        return [
            'label' => 'Alta pendiente',
            'class' => 'bg-orange-50 text-orange-700 border-orange-200',
        ];
    }

    if ($currentWorkflow === 'CLIENTE_ACTIVO') {
        return [
            'label' => 'Cliente activo',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        ];
    }

    if ($currentWorkflow === 'DYNAMICA') {
        return [
            'label' => 'Dynamica',
            'class' => 'bg-blue-50 text-blue-700 border-blue-200',
        ];
    }

    if (in_array($currentWorkflow, ['EN_PROCESO', 'MIGRATE'], true)) {
        return [
            'label' => 'Migrate',
            'class' => 'bg-blue-50 text-blue-700 border-blue-200',
        ];
    }

    return [
        'label' => u('Aprobaci&oacute;n pendiente'),
        'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
    ];
}

function detailLicenseLabel(array $item): string
{
    $codigo = (int) ($item['licencia'] ?? -1);
    if ($codigo >= 0 && isset(LICENCIAS_DISPONIBLES[$codigo])) {
        return LICENCIAS_DISPONIBLES[$codigo];
    }

    return (string) ($item['licencia_texto'] ?: $item['licencia'] ?: 'Sin definir');
}

function detailWorkflowDate(?string $value): string
{
    if (!$value) {
        return '';
    }
    $ts = strtotime($value);
    return $ts ? date('d/m/Y H:i', $ts) : (string) $value;
}

function detailDeferredTaskScheduleNote(?array $task): string
{
    if (!$task) {
        return '';
    }

    $estado = strtoupper(trim((string) ($task['Estado'] ?? '')));
    if (!in_array($estado, ['PENDIENTE', 'PROCESANDO'], true)) {
        return '';
    }

    $scheduledAt = detailWorkflowDate((string) ($task['ProgramadoPara'] ?? ''));
    if ($scheduledAt === '') {
        return '';
    }

    return 'Programado para entrega el ' . $scheduledAt . '.';
}

function detailDynamicaLogin(array $item): string
{
    $licencia = (int) ($item['licencia'] ?? 0);
    if (!WorkflowHelper::licenseCreatesDynamicaUser($licencia)) {
        return 'No aplica';
    }

    $rut = preg_replace('/\D+/', '', (string) ($item['rut'] ?? ''));
    return $rut !== '' ? $rut : '-';
}

function detailDynamicaPassword(array $item): string
{
    $licencia = (int) ($item['licencia'] ?? 0);
    if (!WorkflowHelper::licenseCreatesDynamicaUser($licencia)) {
        return 'No aplica';
    }

    $sourceDate = (string) (($item['fecha_aprobacion'] ?? '') ?: ($item['fecha_creacion'] ?? ''));
    $ts = strtotime($sourceDate);
    return $ts ? date('dmY', $ts) : '-';
}

function detailWorkflowResolveStep(array $step, array $item, array $events, string $currentWorkflow, ?array $deferredTask = null): array
{
    $empresaCreada = (int) ($item['empresa_creada'] ?? 0) === 1;
    $clienteCreado = (int) ($item['cliente_creado'] ?? 0) === 1;
    $estado = (string) ($item['estado'] ?? '');
    $fechaAprobacion = trim((string) ($item['fecha_aprobacion'] ?? ''));
    $modoCert = strtoupper(trim((string) ($item['alta_certificado_digital'] ?? '')));
$certificadoDone =
    isset($events['HITO_CERTIFICADO_DIGITAL'])
    || isset($events['HITO_HOMOLOGACION_DGI'])
    || isset($events['HITO_ENVIO_FACTURA'])
    || isset($events['HITO_ENVIO_CREDENCIALES'])
    || isset($events['HITO_CLIENTE_ACTIVO'])
    || $modoCert === 'ADJUNTO';

    $done = false;
    $current = false;
    $error = false;
    $desc = $step['fallback'];
    $date = '';
    $note = '';
    $substeps = [];
    $licencia = (int) ($item['licencia'] ?? 0);
    $migrateSummary = detailMigrateSummary($item);
    $historicalLicenseIssue = detailHistoricalLicenseIssue(array_values($events));
    $workflowReachedMigrate = in_array($currentWorkflow, ['CERTIFICADO_DIGITAL', 'HOMOLOGACION_DGI', 'ENVIO_FACTURA', 'ENVIO_CREDENCIALES', 'ALTA_FINAL', 'CLIENTE_ACTIVO', 'MIGRATE_ERROR'], true);

    switch ($step['key']) {
        case 'APROBACION_PENDIENTE':
            $event = $events['HITO_EN_PROCESO'] ?? $events['APROBACION'] ?? null;
            $done = $event !== null || $empresaCreada || $clienteCreado || $fechaAprobacion !== '' || $currentWorkflow !== 'APROBACION_PENDIENTE';
            $current = !$done
                && $currentWorkflow === 'APROBACION_PENDIENTE'
                && (int) ($item['carpeta_creada'] ?? 0) === 1;
            if ($event) {
                $desc = trim((string) ($event['descripcion'] ?? $desc));
                $date = detailWorkflowDate($event['fecha_evento'] ?? null);
            } elseif ($done) {
                $desc = 'Aprobado por Admin. Dynamica completado; pendiente Migrate.';
                $date = detailWorkflowDate($item['fecha_aprobacion'] ?? ($item['fecha_actualizacion'] ?? null));
            }
            break;

        case 'HITO_CARPETA':
            $done = (int) ($item['carpeta_creada'] ?? 0) === 1;
            $current = !$done && $currentWorkflow === 'APROBACION_PENDIENTE';
            if ($done) {
                $desc = 'Directorio de archivos creado.';
                $date = detailWorkflowDate((string) ($item['fecha_creacion'] ?? ''));
            }
            break;

        case 'DYNAMICA':
            $event = $events['HITO_DYNAMICA_OK'] ?? $events['APROBACION'] ?? null;
            $done = $empresaCreada && $clienteCreado;
            $current = !$done && $currentWorkflow === 'DYNAMICA';
            if ($done) {
                $desc = trim((string) (($event['descripcion'] ?? '') ?: 'Empresa y cliente creados en Dynamica.'));
                $date = detailWorkflowDate($event['fecha_evento'] ?? ($item['fecha_aprobacion'] ?? null));
            }
            $substeps[] = $done
                ? 'Empresa y cliente creados en tablas Dynamica.'
                : 'Pendiente creacion de empresa y cliente en tablas Dynamica.';
            if (WorkflowHelper::licenseCreatesDynamicaUser($licencia)) {
                $substeps[] = trim((string) (($events['HITO_DYNAMICA_USUARIO_OK']['descripcion'] ?? '') ?: 'Usuario Dynamica requerido.'));
            } else {
                $substeps[] = ['state' => 'na', 'text' => WorkflowHelper::dynamicaUserSubstepLabel($licencia)];
            }
            break;

        case 'MIGRATE':
            $eventOk = $events['HITO_MIGRATE_OK'] ?? null;
            $eventErr = $events['HITO_MIGRATE_ERROR'] ?? null;
            $done = $eventOk !== null || ($empresaCreada && $clienteCreado && $workflowReachedMigrate);
            $error = $eventErr !== null || $migrateSummary['lic_rejected'] || $historicalLicenseIssue !== '';
            $current = !$done && !$error && in_array($currentWorkflow, ['MIGRATE', 'EN_PROCESO'], true);
            if ($eventOk) {
                $desc = trim((string) ($eventOk['descripcion'] ?? $desc));
                $date = detailWorkflowDate($eventOk['fecha_evento'] ?? null);
            } elseif ($eventErr) {
                $desc = trim((string) ($eventErr['descripcion'] ?? $desc));
                $date = detailWorkflowDate($eventErr['fecha_evento'] ?? null);
            } elseif ($migrateSummary['lic_rejected']) {
                $desc = 'Migrate devolvio novedad en el licenciamiento.';
            } elseif ($historicalLicenseIssue !== '') {
                $desc = 'Migrate tiene una novedad historica de licenciamiento pendiente de resolver.';
            } elseif ($done) {
                $desc = 'Migrate OK.';
                $date = detailWorkflowDate($item['fecha_actualizacion'] ?? ($item['fecha_aprobacion'] ?? null));
            }
            $substeps[] = $done
                ? ['state' => 'done', 'text' => 'Empresa y sucursal registradas en Migrate.']
                : ['state' => 'pending', 'text' => 'Pendiente registro de empresa y sucursal en Migrate.'];
            $substeps[] = $migrateSummary['lic_rejected']
                ? ['state' => 'error', 'text' => 'Licenciamiento rechazado por Migrate: ' . $migrateSummary['lic_msg_retorno']]
                : ($historicalLicenseIssue !== ''
                    ? ['state' => 'error', 'text' => 'Licenciamiento rechazado en intento previo. Validar antes de continuar.']
                    : ($done
                        ? ['state' => 'done', 'text' => 'Licenciamiento enviado dentro del RegistroEmpresa.']
                        : ['state' => 'pending', 'text' => 'Pendiente envio de licenciamiento a Migrate.']));
            if (WorkflowHelper::licenseCreatesMigrateUser($licencia)) {
                $substeps[] = [
                    'state' => $done ? 'warning' : 'pending',
                    'text' => trim((string) (($events['HITO_MIGRATE_USUARIO_ENVIADO']['descripcion'] ?? $events['HITO_MIGRATE_USUARIO_OK']['descripcion'] ?? '') ?: 'Usuario Migrate enviado dentro del RegistroEmpresa. Validar alta efectiva en Migrate.')),
                ];
            } else {
                $substeps[] = ['state' => 'na', 'text' => WorkflowHelper::migrateUserSubstepLabel($licencia)];
            }
            break;

        case 'CERTIFICADO_DIGITAL':
            $event = $events['HITO_CERTIFICADO_DIGITAL'] ?? null;
            $done = $certificadoDone;
            $current = !$done && $currentWorkflow === 'CERTIFICADO_DIGITAL';
            if ($done) {
                $desc = $modoCert === 'ADJUNTO'
                    ? 'Certificado digital recibido y cargado.'
                    : 'Certificado digital gestionado manualmente.';
                $date = detailWorkflowDate($event['fecha_evento'] ?? null);
            } elseif ($modoCert !== '') {
                $desc = 'Modo de certificado definido: ' . (string) ($item['alta_certificado_digital'] ?? '');
            }
            break;

        case 'HOMOLOGACION_DGI':
            $event = $events['HITO_HOMOLOGACION_DGI'] ?? $events['HITO_PENDIENTE_DGI'] ?? null;
            $done = isset($events['HITO_HOMOLOGACION_DGI']) || isset($events['HITO_ENVIO_FACTURA']) || isset($events['HITO_ENVIO_CREDENCIALES']) || isset($events['HITO_CLIENTE_ACTIVO']);
            $current = !$done && $currentWorkflow === 'HOMOLOGACION_DGI' && $certificadoDone;
            if ($done && $event) {
                $desc = 'Gestion DGI registrada.';
                $date = detailWorkflowDate($event['fecha_evento'] ?? null);
            } elseif ($event) {
                $desc = trim((string) ($event['descripcion'] ?? $desc));
                $date = detailWorkflowDate($event['fecha_evento'] ?? null);
            }
            break;

        case 'ENVIO_FACTURA':
            $event = $events['HITO_ENVIO_FACTURA'] ?? null;
            $done = $event !== null || isset($events['HITO_ENVIO_CREDENCIALES']) || isset($events['HITO_CLIENTE_ACTIVO']);
            $current = !$done && $currentWorkflow === 'ENVIO_FACTURA';
            if ($event) {
                $desc = trim((string) ($event['descripcion'] ?? $desc));
                $date = detailWorkflowDate($event['fecha_evento'] ?? null);
            }
            break;

        case 'ENVIO_CREDENCIALES':
            $event = $events['HITO_ENVIO_CREDENCIALES'] ?? null;
            $done = $event !== null || isset($events['HITO_CLIENTE_ACTIVO']);
            $current = !$done && $currentWorkflow === 'ENVIO_CREDENCIALES';
            if ($event) {
                $desc = trim((string) ($event['descripcion'] ?? $desc));
                $date = detailWorkflowDate($event['fecha_evento'] ?? null);
            } elseif ($current) {
                $note = detailDeferredTaskScheduleNote($deferredTask);
                if ($note !== '') {
                    $desc = 'Envio de credenciales programado para el siguiente ciclo automatico.';
                }
            }
            break;

        case 'ALTA_FINAL':
            $event = $events['HITO_CLIENTE_ACTIVO'] ?? null;
            $done = $event !== null || $currentWorkflow === 'CLIENTE_ACTIVO';
            $current = !$done && $currentWorkflow === 'ALTA_FINAL';
            if ($event) {
                $desc = trim((string) ($event['descripcion'] ?? $desc));
                $date = detailWorkflowDate($event['fecha_evento'] ?? null);
            }
            break;
    }

    if ($estado === ESTADO_ELIMINADO && !$done) {
        $current = false;
    }

    return [
        'done' => $done,
        'current' => $current,
        'error' => $error,
        'desc' => $desc,
        'date' => $date,
        'note' => $note,
        'substeps' => $substeps,
    ];
}

function detailSubstepMeta(string $stepState, string $substep): array
{
    $text = mb_strtolower(trim($substep));
    $hasText = static function (string $needle) use ($text): bool {
        return $needle !== '' && mb_strpos($text, $needle) !== false;
    };

    if ($stepState === 'na') {
        return [
            'wrapper' => 'bg-slate-100 border-slate-200',
            'iconWrap' => 'bg-slate-200 text-slate-500',
            'icon' => 'minus-circle',
            'text' => 'text-slate-600 line-through',
        ];
    }

    if ($hasText('rechazad')) {
        return [
            'wrapper' => 'bg-rose-50 border-rose-100',
            'iconWrap' => 'bg-rose-100 text-rose-600',
            'icon' => 'alert-circle',
            'text' => 'text-rose-700',
        ];
    }

    if ($hasText('validar alta efectiva') || $hasText('usuario migrate enviado')) {
        return [
            'wrapper' => 'bg-amber-50 border-amber-100',
            'iconWrap' => 'bg-amber-100 text-amber-600',
            'icon' => 'clock-3',
            'text' => 'text-amber-800',
        ];
    }

    if ($stepState === 'done' || $hasText('no aplica') || $hasText('incluido') || $hasText('creado') || $hasText('cargado') || $hasText('gestionado')) {
        return [
            'wrapper' => 'bg-emerald-50 border-emerald-100',
            'iconWrap' => 'bg-emerald-100 text-emerald-600',
            'icon' => 'check',
            'text' => 'text-emerald-800',
        ];
    }

    if ($stepState === 'error') {
        return [
            'wrapper' => 'bg-rose-50 border-rose-100',
            'iconWrap' => 'bg-rose-100 text-rose-600',
            'icon' => 'alert-circle',
            'text' => 'text-rose-700',
        ];
    }

    if ($stepState === 'current') {
        return [
            'wrapper' => 'bg-indigo-50 border-indigo-100',
            'iconWrap' => 'bg-indigo-100 text-indigo-600',
            'icon' => 'clock-3',
            'text' => 'text-indigo-800',
        ];
    }

    return [
        'wrapper' => 'bg-slate-50 border-slate-200',
        'iconWrap' => 'bg-slate-100 text-slate-400',
        'icon' => 'circle',
        'text' => 'text-slate-600',
    ];
}

$itemHistory = $workflowHistory[$item['id']] ?? [];
$events = detailWorkflowEventsMap($itemHistory);
$currentWorkflow = detailWorkflowCurrentKey($item, $itemHistory);
$generalStatus = detailGeneralStatusMeta($item, $currentWorkflow);
$migrateSummary = detailMigrateSummary($item);
$estado = (string) ($item['estado'] ?? '');
$estadoDetalle = trim((string) ($item['estado_detalle'] ?? ''));
$badgeClass = $generalStatus['class'];
$badgeLabel = $generalStatus['label'];
$empresaCreada = (int) ($item['empresa_creada'] ?? 0) === 1;
$clienteCreado = (int) ($item['cliente_creado'] ?? 0) === 1;
$migrateAlreadyExists = stripos($estadoDetalle, 'ya est') !== false && stripos($estadoDetalle, 'migrate') !== false;
$migrateAlreadyExists = $migrateAlreadyExists
    || (stripos((string) ($migrateSummary['msg_desc'] ?? ''), 'ya est') !== false && stripos((string) ($migrateSummary['msg_desc'] ?? ''), 'registrad') !== false)
    || (stripos(trim((string) ($item['error_proceso'] ?? '')), 'ya registrada en migrate') !== false);
$hitoTexto = u('Aprobaci&oacute;n pendiente');
$hitoClass = 'bg-indigo-50 border-indigo-200 text-indigo-700';
if ($currentWorkflow === 'CLIENTE_ACTIVO') {
    $hitoTexto = 'Cliente activo';
    $hitoClass = 'bg-emerald-50 border-emerald-200 text-emerald-700';
} elseif ($estado === ESTADO_ELIMINADO) {
    $hitoTexto = 'Registro eliminado';
    $hitoClass = 'bg-slate-100 border-slate-200 text-slate-500';
} elseif ($currentWorkflow === 'MIGRATE_ERROR') {
    $hitoTexto = 'Migrate con novedad';
    $hitoClass = 'bg-rose-50 border-rose-200 text-rose-700';
} elseif ($estado === ESTADO_ERROR_APROBACION && $empresaCreada && $clienteCreado) {
    $hitoTexto = 'Migrate';
    $hitoClass = 'bg-rose-50 border-rose-200 text-rose-700';
} elseif ($currentWorkflow === 'CERTIFICADO_DIGITAL') {
    $hitoTexto = 'Certificado digital';
} elseif ($currentWorkflow === 'HOMOLOGACION_DGI') {
    $hitoTexto = 'Homologación DGI';
} elseif (in_array($currentWorkflow, ['ALTA_PENDIENTE', 'ENVIO_FACTURA', 'ENVIO_CREDENCIALES', 'ALTA_FINAL'], true)) {
    $hitoTexto = 'Alta pendiente';
} elseif (in_array($currentWorkflow, ['EN_PROCESO', 'MIGRATE'], true)) {
    $hitoTexto = 'Migrate';
}
?>

<section class="space-y-6">
    <?php if ($migrateAlreadyExists): ?>
        <div class="rounded-2xl border border-amber-300 bg-amber-50 px-5 py-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1">
                    <p class="text-sm font-extrabold uppercase tracking-wider text-amber-800">Empresa ya registrada en Migrate</p>
                    <p class="text-sm font-medium text-amber-900">
                        Este RUT ya existe en Migrate. Revise cuidadosamente el c&oacute;digo de empresa, la clave recuperada y el detalle t&eacute;cnico antes de continuar.
                    </p>
                    <?php if ($estadoDetalle !== ''): ?>
                        <p class="text-xs text-amber-800"><?= htmlspecialchars($estadoDetalle, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border <?= $badgeClass ?>" title="Estado general">
                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                        <span class="uppercase tracking-wider opacity-75">Estado general</span>
                        <span class="opacity-40">/</span>
                        <?= htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border <?= $hitoClass ?>" title="Hito actual">
                        <i data-lucide="git-commit" class="w-3.5 h-3.5"></i>
                        <span class="uppercase tracking-wider opacity-75">Hito actual</span>
                        <span class="opacity-40">/</span>
                        <span><?= htmlspecialchars($hitoTexto, ENT_QUOTES, 'UTF-8') ?></span>
                    </span>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight"><?= htmlspecialchars((string) $item['razon_social'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="text-sm text-slate-500 mt-1">RUT: <?= htmlspecialchars((string) $item['rut'], ENT_QUOTES, 'UTF-8') ?> / Licencia: <?= htmlspecialchars(detailLicenseLabel($item), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 max-w-4xl">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Fecha registro</p>
                        <p class="text-sm font-semibold text-slate-800 mt-1"><?= htmlspecialchars((string) $item['fecha_creacion'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Email principal</p>
                        <p class="text-sm font-semibold text-slate-800 mt-1"><?= htmlspecialchars((string) $item['email_principal'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Es emisor</p>
                        <p class="text-sm font-semibold text-slate-800 mt-1"><?= htmlspecialchars((string) ($item['alta_es_emisor'] ?? 'NO'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            </div>

            <?php if (!$embeddedView): ?>
                <div class="flex flex-wrap gap-2">
                    <a href="<?= htmlspecialchars(app_url('panel'), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-4 py-2.5 rounded-lg shadow-sm transition duration-150 text-sm">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Volver</span>
                    </a>
                    <button type="button" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition duration-150 text-sm" data-open-modal="modal-edit">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                        <span>Editar</span>
                    </button>
                    <?php if ($migrateSummary['has_data']): ?>
                        <button type="button" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-4 py-2.5 rounded-lg shadow-sm transition duration-150 text-sm" data-open-modal="modal-migrate-xml">
                            <i data-lucide="file-code-2" class="w-4 h-4"></i>
                            <span>Ver XML Migrate</span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[1.25fr,0.75fr] gap-6">
        <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900 text-sm">Hoja de ruta de onboarding y fiscal</h3>
                    <p class="text-sm text-slate-500 mt-1">Seguimiento del hito actual junto con los datos fiscales del cliente.</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 xl:grid-cols-[1.15fr,0.85fr] gap-4">
                    <div class="space-y-3">
                        <?php
                        $routeSteps = [
                            ['key' => 'HITO_CARPETA', 'label' => 'Hito Carpeta', 'mode' => 'Auto', 'fallback' => u('Creaci&oacute;n autom&aacute;tica del directorio de archivos.')],
                            ['key' => 'APROBACION_PENDIENTE', 'label' => u('Aprobaci&oacute;n pendiente'), 'mode' => 'Manual', 'fallback' => u('Pendiente de aprobaci&oacute;n administrativa.')],
                            ['key' => 'DYNAMICA', 'label' => 'Dynamica', 'mode' => 'Auto', 'fallback' => u('Creaci&oacute;n de empresa, cliente y usuario seg&uacute;n licencia.')],
                            ['key' => 'MIGRATE', 'label' => 'Migrate', 'mode' => 'Auto', 'fallback' => u('Registro de empresa, sucursal y usuario seg&uacute;n licencia.')],
                            ['key' => 'CERTIFICADO_DIGITAL', 'label' => 'Certificado Digital', 'mode' => 'Manual', 'fallback' => u('Gesti&oacute;n manual del certificado digital.')],
                            ['key' => 'HOMOLOGACION_DGI', 'label' => u('Homologaci&oacute;n DGI'), 'mode' => 'Manual', 'fallback' => u('Tramitaci&oacute;n gubernamental y gesti&oacute;n DGI.')],
                            ['key' => 'ENVIO_FACTURA', 'label' => u('Env&iacute;o de Factura'), 'mode' => 'Auto', 'fallback' => u('Primera factura o activaci&oacute;n de facturaci&oacute;n autom&aacute;tica.')],
                            ['key' => 'ENVIO_CREDENCIALES', 'label' => u('Env&iacute;o de Credenciales'), 'mode' => 'Auto', 'fallback' => u('Env&iacute;o de credenciales seguras al cliente.')],
                            ['key' => 'ALTA_FINAL', 'label' => 'Alta Final', 'mode' => 'Auto', 'fallback' => u('Activaci&oacute;n final del cliente en producci&oacute;n.')],
                        ];
                        ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php foreach ($routeSteps as $index => $step): ?>
                            <?php
                            $state = detailWorkflowResolveStep($step, $item, $events, $currentWorkflow, $deferredTask ?? null);
                            $isDone = $state['done'];
                            $isCurrent = $state['current'];
                            $isError = $state['error'];
                            $boxClass = $isDone
                                ? 'bg-emerald-50 border-emerald-100'
                                : ($isError
                                    ? 'bg-rose-50 border-rose-100'
                                    : ($isCurrent ? 'bg-indigo-50 border-indigo-100' : 'bg-slate-50 border-slate-200'));
                            $iconClass = $isDone
                                ? 'bg-emerald-100 text-emerald-600'
                                : ($isError
                                    ? 'bg-rose-100 text-rose-600'
                                    : ($isCurrent ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-400'));
                            $icon = $isDone ? 'check' : ($isError ? 'alert-circle' : ($isCurrent ? 'clock-3' : 'circle'));
                            $desc = $state['desc'];
                            $dateText = $state['date'];
                            ?>
                            <div class="flex items-start gap-3 p-3 rounded-xl border <?= $boxClass ?>">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full <?= $iconClass ?>">
                                    <i data-lucide="<?= $icon ?>" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">
                                        <?= (int) ($index + 1) ?>. <?= htmlspecialchars($step['label'], ENT_QUOTES, 'UTF-8') ?>
                                        <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold <?= $step['mode'] === 'Auto' ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' ?>">
                                            <?= htmlspecialchars($step['mode'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </h4>
                                    <p class="text-xs text-slate-500 mt-1">
                                        <?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?>
                                        <?php if ($dateText !== ''): ?>
                                            - <?= htmlspecialchars($dateText, ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!empty($state['note'])): ?>
                                        <p class="mt-1 text-[11px] font-semibold text-blue-600">
                                            <?= htmlspecialchars((string) $state['note'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($state['substeps'])): ?>
                                        <div class="mt-3 rounded-lg border border-slate-200 bg-white/80 p-2.5 space-y-2">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Subhitos</p>
                                            <?php foreach ($state['substeps'] as $substep): ?>
                                                <?php
                                                $substepText = is_array($substep) ? (string) ($substep['text'] ?? '') : (string) $substep;
                                                $substepState = is_array($substep) ? (string) ($substep['state'] ?? '') : '';
                                                if ($substepState === '') {
                                                    $substepState = ($state['error'] ? 'error' : ($state['done'] ? 'done' : ($state['current'] ? 'current' : 'pending')));
                                                }
                                                $substepMeta = detailSubstepMeta($substepState, $substepText);
                                                ?>
                                                <div class="flex items-start gap-2 rounded-lg px-2.5 py-2 border <?= $substepMeta['wrapper'] ?>">
                                                    <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full <?= $substepMeta['iconWrap'] ?>">
                                                        <i data-lucide="<?= $substepMeta['icon'] ?>" class="w-3 h-3"></i>
                                                    </span>
                                                    <p class="text-[11px] leading-4 font-medium <?= $substepMeta['text'] ?>"><?= htmlspecialchars($substepText, ENT_QUOTES, 'UTF-8') ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 text-sm">
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Domicilio fiscal</p>
                            <p class="mt-2 text-slate-800 font-medium"><?= htmlspecialchars((string) $item['domicilio'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">RUT</p>
                            <p class="mt-2 text-slate-800 font-medium"><?= htmlspecialchars((string) $item['rut'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Certificado digital</p>
                            <p class="mt-2 text-slate-800 font-medium"><?= htmlspecialchars((string) ($item['alta_certificado_digital'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">eFactura usuario</p>
                            <p class="mt-2 text-slate-800 font-medium"><?= htmlspecialchars((string) ($item['usuario_ef'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Login Dynamica</p>
                            <p class="mt-2 text-slate-800 font-medium"><?= htmlspecialchars(detailDynamicaLogin($item), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Clave Dynamica</p>
                            <p class="mt-2 text-slate-800 font-medium"><?= htmlspecialchars(detailDynamicaPassword($item), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">C&oacute;digo sucursal</p>
                            <p class="mt-2 text-slate-800 font-medium"><?= htmlspecialchars((string) ($item['suc_cod_sucursal'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Firmante</p>
                            <p class="mt-2 text-slate-800 font-medium"><?= htmlspecialchars((string) ($item['nombre_completo_firmante'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($item['observaciones']) || !empty($item['notas_admin']) || !empty($item['error_proceso'])): ?>
                    <div class="space-y-3">
                        <?php if (!empty($item['observaciones'])): ?>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Observaciones</p>
                                <p class="mt-2 text-sm text-slate-700 whitespace-pre-line"><?= htmlspecialchars((string) $item['observaciones'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($item['notas_admin'])): ?>
                            <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                                <p class="text-[11px] uppercase tracking-wider text-indigo-500 font-semibold">Notas admin</p>
                                <p class="mt-2 text-sm text-indigo-900 whitespace-pre-line"><?= htmlspecialchars((string) $item['notas_admin'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($item['error_proceso'])): ?>
                            <div class="rounded-xl border border-rose-100 bg-rose-50 p-4">
                                <p class="text-[11px] uppercase tracking-wider text-rose-500 font-semibold">Error de proceso</p>
                                <p class="mt-2 text-sm text-rose-900 whitespace-pre-line"><?= htmlspecialchars((string) $item['error_proceso'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900 text-sm">Adjuntos</h3>
                </div>
                <div class="p-5 space-y-3">
                    <?php if (empty($archivos)): ?>
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-sm text-slate-500 text-center">Sin adjuntos registrados.</div>
                    <?php else: ?>
                        <?php foreach ($archivos as $archivo): ?>
                            <div class="rounded-xl border border-slate-200 p-4 space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars((string) $archivo['tipo_archivo'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars((string) $archivo['nombre_original'], ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold"><?= htmlspecialchars((string) pathinfo((string) $archivo['ruta_archivo'], PATHINFO_EXTENSION), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= htmlspecialchars(app_url('archivo/' . (int) $archivo['id'] . '/descargar'), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-3 py-2 rounded-lg text-xs transition">
                                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                        <span>Descargar</span>
                                    </a>
                                    <?php if ($estado !== ESTADO_ELIMINADO): ?>
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-indigo-700 font-medium px-3 py-2 rounded-lg text-xs transition"
                                            data-open-modal="modal-replace-file"
                                            data-replace-file
                                            data-file-id="<?= (int) $archivo['id'] ?>"
                                            data-file-type="<?= htmlspecialchars((string) $archivo['tipo_archivo'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-file-name="<?= htmlspecialchars((string) $archivo['nombre_original'], ENT_QUOTES, 'UTF-8') ?>"
                                        >
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                            <span>Reemplazar</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section id="acciones" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900 text-sm">Acciones administrativas</h3>
                    <p class="text-sm text-slate-500 mt-1">Toda acci&oacute;n pide confirmaci&oacute;n antes de continuar.</p>
                </div>
                <div class="p-5 space-y-3">
                    <?php if ($migrateSummary['has_data']): ?>
                        <button type="button" class="w-full inline-flex items-center justify-center gap-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold px-4 py-3 rounded-xl transition shadow-sm" data-open-modal="modal-migrate-xml">
                            <i data-lucide="file-code-2" class="w-4 h-4"></i>
                            <span>Ver detalle t&eacute;cnico de Migrate</span>
                        </button>
                    <?php endif; ?>
                    <?php if ($estado === ESTADO_PENDIENTE_APROBACION): ?>
                        <form method="post" action="<?= htmlspecialchars(app_url('index.php?action=approve&id=' . (int) $item['id']), ENT_QUOTES, 'UTF-8') ?>" data-confirm="Esto creara registros reales en Empresas y Clientes y movera el onboarding al siguiente hito." data-busy-text="Espere un momento, por favor. Estamos aprobando el registro.">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-3 rounded-xl transition shadow-sm">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                                <span>Aprobar alta</span>
                            </button>
                        </form>
                    <?php elseif (in_array($currentWorkflow, ['EN_PROCESO', 'MIGRATE', 'MIGRATE_ERROR'], true) || ($estado === ESTADO_ERROR_APROBACION && $empresaCreada && $clienteCreado)): ?>
                        <form method="post" action="<?= htmlspecialchars(app_url('index.php?action=run-migrate&id=' . (int) $item['id']), ENT_QUOTES, 'UTF-8') ?>" data-confirm="Confirme la ejecucion del hito Migrate para este registro." data-busy-text="Espere un momento, por favor. Estamos gestionando Migrate.">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-3 rounded-xl transition shadow-sm">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                <span><?= ($estado === ESTADO_ERROR_APROBACION && $empresaCreada && $clienteCreado) ? 'Reintentar Migrate' : 'Ejecutar Migrate' ?></span>
                            </button>
                        </form>
                    <?php elseif ($estado === ESTADO_PENDIENTE_APROBACION || ($estado === ESTADO_ERROR_APROBACION && (!$empresaCreada || !$clienteCreado))): ?>
                        <form method="post" action="<?= htmlspecialchars(app_url('index.php?action=approve&id=' . (int) $item['id']), ENT_QUOTES, 'UTF-8') ?>" data-confirm="Esto creara registros reales en Empresas y Clientes y movera el onboarding al siguiente hito." data-busy-text="Espere un momento, por favor. Estamos aprobando el registro.">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-3 rounded-xl transition shadow-sm">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                                <span><?= $estado === ESTADO_ERROR_APROBACION ? u('Reintentar aprobaci&oacute;n') : 'Aprobar alta' ?></span>
                            </button>
                        </form>
                    <?php elseif ($currentWorkflow === 'CERTIFICADO_DIGITAL'): ?>
                        <form method="post" action="<?= htmlspecialchars(app_url('index.php?action=change-hito&id=' . (int) $item['id']), ENT_QUOTES, 'UTF-8') ?>" data-confirm="Confirme que el hito Certificado Digital fue completado." data-busy-text="Espere un momento, por favor. Estamos ejecutando el alta final.">
                            <input type="hidden" name="target_hito" value="CERTIFICADO_DIGITAL">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-3 rounded-xl transition shadow-sm">
                                <i data-lucide="forward" class="w-4 h-4"></i>
                                <span>Marcar Certificado Digital</span>
                            </button>
                        </form>
                    <?php elseif ($currentWorkflow === 'HOMOLOGACION_DGI'): ?>
                        <form method="post" action="<?= htmlspecialchars(app_url('index.php?action=change-hito&id=' . (int) $item['id']), ENT_QUOTES, 'UTF-8') ?>" data-confirm="Confirme que el hito Homologación DGI fue completado." data-busy-text="Espere un momento, por favor. Estamos actualizando el hito.">
                            <input type="hidden" name="target_hito" value="HOMOLOGACION_DGI">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-3 rounded-xl transition shadow-sm">
                                <i data-lucide="forward" class="w-4 h-4"></i>
                                <span>Marcar Homologación DGI</span>
                            </button>
                        </form>
                    <?php elseif ($currentWorkflow === 'ENVIO_FACTURA' || $currentWorkflow === 'ALTA_FINAL'): ?>
                        <form method="post" action="<?= htmlspecialchars(app_url('index.php?action=change-hito&id=' . (int) $item['id']), ENT_QUOTES, 'UTF-8') ?>" data-confirm="Confirme el reintento del cierre automático del onboarding." data-busy-text="Espere un momento, por favor. Estamos reintentando el cierre automático del onboarding.">
                            <input type="hidden" name="target_hito" value="ALTA_FINAL">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-3 rounded-xl transition shadow-sm">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                <span>Reintentar Alta Final</span>
                            </button>
                        </form>
                    <?php elseif ($currentWorkflow === 'ENVIO_CREDENCIALES'): ?>
                        <div class="w-full rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-left shadow-sm">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                    <i data-lucide="clock-3" class="w-4 h-4"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-amber-900">Env&iacute;o de credenciales programado</p>
                                    <p class="mt-1 text-sm text-amber-800">
                                        <?= htmlspecialchars((string) ($item['estado_detalle'] ?: 'La factura ya fue emitida. El sistema est&aacute; esperando el siguiente ciclo autom&aacute;tico para enviar las credenciales.'), ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($currentWorkflow === 'ALTA_PENDIENTE'): ?>
                        <form method="post" action="<?= htmlspecialchars(app_url('index.php?action=change-hito&id=' . (int) $item['id']), ENT_QUOTES, 'UTF-8') ?>" data-confirm="Confirme la ejecucion del hito Alta Final." data-busy-text="Espere un momento, por favor. Estamos ejecutando el alta final.">
                            <input type="hidden" name="target_hito" value="ALTA_FINAL">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-3 rounded-xl transition shadow-sm">
                                <i data-lucide="user-check" class="w-4 h-4"></i>
                                <span>Ejecutar Alta Final</span>
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($estado !== ESTADO_ELIMINADO && $currentWorkflow !== 'CLIENTE_ACTIVO'): ?>
                        <button type="button" class="w-full inline-flex items-center justify-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold px-4 py-3 rounded-xl border border-rose-200 transition" data-open-modal="modal-delete">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            <span>Eliminar registro</span>
                        </button>
                    <?php endif; ?>
                </div>
            </section>
        </aside>
    </div>
</section>

<div class="modal-shell" id="modal-edit" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-edit"></div>
    <div class="modal-panel modal-xl">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold uppercase tracking-wider">Edicion</span>
                        <h3 class="mt-3 text-xl font-bold text-slate-900">Actualizar alta temporal</h3>
                        <p class="mt-1 text-sm text-slate-500">Ajuste los datos del onboarding antes de aprobar el registro.</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition" data-close-modal="modal-edit" aria-label="Cerrar">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form method="post" action="<?= htmlspecialchars(app_url('index.php?action=update&id=' . (int) $item['id']), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" class="space-y-6" data-busy-text="Espere un momento, por favor. Estamos guardando los cambios.">
            <div class="bg-slate-50/70 rounded-2xl border border-slate-200 p-5">
                <div class="form-grid">
                    <?php $prefix = 'edit_'; require __DIR__ . '/_form_fields.php'; ?>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" data-close-modal="modal-edit" class="bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold px-5 py-2.5 rounded-lg transition text-sm">Cancelar</button>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-2.5 rounded-lg transition text-sm shadow-md flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Guardar cambios</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-shell" id="modal-delete" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-delete"></div>
    <div class="modal-panel modal-sm">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-semibold uppercase tracking-wider">Eliminar</span>
                <h3 class="mt-3 text-xl font-bold text-slate-900">Confirmar eliminaci&oacute;n</h3>
                <p class="mt-1 text-sm text-slate-500">Se borrar&aacute;n los adjuntos en disco y el registro quedar&aacute; como eliminado.</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition" data-close-modal="modal-delete" aria-label="Cerrar">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form method="post" action="<?= htmlspecialchars(app_url('index.php?action=delete&id=' . (int) $item['id']), ENT_QUOTES, 'UTF-8') ?>" data-confirm="Se eliminara el registro temporal y se borraran sus archivos del disco." class="space-y-5">
            <div>
                <label for="motivo_eliminacion" class="block text-xs font-semibold text-slate-600 mb-1.5">Motivo de eliminaci&oacute;n</label>
                <textarea id="motivo_eliminacion" name="motivo_eliminacion" rows="4" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 text-sm transition"></textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" data-close-modal="modal-delete" class="bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold px-5 py-2.5 rounded-lg transition text-sm">Cancelar</button>
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-6 py-2.5 rounded-lg transition text-sm shadow-md">Eliminar registro</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-shell" id="modal-replace-file" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-replace-file"></div>
    <div class="modal-panel modal-sm">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold uppercase tracking-wider">Adjunto</span>
                <h3 class="mt-3 text-xl font-bold text-slate-900">Reemplazar archivo</h3>
                <p class="mt-1 text-sm text-slate-500">Actualice el adjunto manteniendo su tipo funcional dentro del onboarding.</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition" data-close-modal="modal-replace-file" aria-label="Cerrar">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form method="post" action="<?= htmlspecialchars(app_url('index.php?action=replace-file&id=0'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" class="space-y-5" id="replace-file-form">
            <input type="hidden" name="archivo_id" id="replace-file-id" value="">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-2">
                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Tipo</p>
                <p class="text-sm font-semibold text-slate-900" id="replace-file-type">-</p>
                <p class="text-xs text-slate-500" id="replace-file-name">-</p>
            </div>
            <div>
                <label for="archivo_reemplazo" class="block text-xs font-semibold text-slate-600 mb-1.5">Nuevo archivo</label>
                <input id="archivo_reemplazo" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_reemplazo" required>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" data-close-modal="modal-replace-file" class="bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold px-5 py-2.5 rounded-lg transition text-sm">Cancelar</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2.5 rounded-lg transition text-sm shadow-md">Guardar reemplazo</button>
            </div>
        </form>
    </div>
</div>

<?php if ($migrateSummary['has_data']): ?>
<div class="modal-shell" id="modal-migrate-xml" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-migrate-xml"></div>
    <div class="modal-panel modal-xl">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold uppercase tracking-wider">Migrate XML</span>
                <h3 class="mt-3 text-xl font-bold text-slate-900">Detalle t&eacute;cnico del &uacute;ltimo env&iacute;o</h3>
                <p class="mt-1 text-sm text-slate-500">Aqu&iacute; puede revisar el request, el response y el resumen del retorno de Migrate.</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition" data-close-modal="modal-migrate-xml" aria-label="Cerrar">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">MsgCod</p>
                <p class="text-sm font-semibold text-slate-800 mt-1"><?= htmlspecialchars($migrateSummary['msg_code'] !== '' ? $migrateSummary['msg_code'] : '-', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">MsgDsc</p>
                <p class="text-sm font-semibold text-slate-800 mt-1"><?= htmlspecialchars($migrateSummary['msg_desc'] !== '' ? $migrateSummary['msg_desc'] : '-', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">EmpCodigo</p>
                <p class="text-sm font-semibold text-slate-800 mt-1"><?= htmlspecialchars($migrateSummary['empresa_invoicy'] !== '' ? $migrateSummary['empresa_invoicy'] : '-', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Clave acceso</p>
                <p class="text-sm font-semibold text-slate-800 mt-1 break-all"><?= htmlspecialchars($migrateSummary['suc_clave_acceso'] !== '' ? $migrateSummary['suc_clave_acceso'] : '-', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>

        <?php if ($migrateSummary['lic_msg_retorno'] !== ''): ?>
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 mb-5">
                <p class="text-[11px] uppercase tracking-wider text-rose-600 font-semibold">Licenciamiento</p>
                <p class="mt-2 text-sm font-semibold text-rose-900"><?= htmlspecialchars($migrateSummary['lic_msg_retorno'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endif; ?>

        <?php if ($migrateSummary['errors'] !== []): ?>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 mb-5">
                <p class="text-[11px] uppercase tracking-wider text-amber-600 font-semibold">Resumen t&eacute;cnico</p>
                <ul class="mt-2 space-y-1 text-sm text-amber-900">
                    <?php foreach ($migrateSummary['errors'] as $error): ?>
                        <li>&bull; <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <h4 class="text-sm font-bold text-slate-900">&Uacute;ltimo request XML</h4>
                    <span class="text-xs text-slate-400"><?= number_format(strlen((string) ($item['migrate_request_xml'] ?? ''))) ?> bytes</span>
                </div>
                <textarea readonly class="w-full min-h-[360px] rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 font-mono leading-5"><?= htmlspecialchars((string) ($item['migrate_request_xml'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <h4 class="text-sm font-bold text-slate-900">&Uacute;ltimo response XML</h4>
                    <span class="text-xs text-slate-400"><?= number_format(strlen((string) ($item['migrate_response_xml'] ?? ''))) ?> bytes</span>
                </div>
                <textarea readonly class="w-full min-h-[360px] rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 font-mono leading-5"><?= htmlspecialchars((string) ($item['migrate_response_xml'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>



