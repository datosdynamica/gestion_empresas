<?php declare(strict_types=1); ?>
<?php
function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function u(string $value): string
{
    return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
}

function demoEmpresasNuevasRows(): array
{
    return [
        [
            'is_demo' => 1,
            'id' => 1001,
            'estado' => ESTADO_ERROR_APROBACION,
            'fecha_creacion' => '2026-05-20 09:10:00',
            'razon_social' => 'Alimentos del Sur S.A.',
            'nombre_fantasia' => 'Alimentos del Sur',
            'rut' => '219988440012',
            'email_principal' => 'contacto@alimentosdelsur.com',
            'domicilio' => 'Ruta 8 Km 41, Canelones',
            'telefono' => '099123456',
            'ciudad' => 'Canelones',
            'departamento' => 'Canelones',
            'usuario_ef' => 'usr_alim_sur',
            'clave_usuario_ef' => 'ClaveTemporalSur_2026!',
            'licencia' => 8,
            'licencia_texto' => 'Enterprise Cloud',
            'plan' => 'Enterprise Cloud',
            'usuarios' => 12,
            'cfe_mensuales' => 2500,
            'cliente_id_giro' => 10,
            'cliente_id_fidelizacion' => 3,
            'email_envio_fe' => 'facturas@alimentosdelsur.com',
            'cliente_abonado_importe' => 3800,
            'cliente_abonado_moneda' => 'UYU',
            'cliente_abonado_periodo' => 'MENSUAL',
            'cliente_abonado_descuento' => 0,
            'suc_cod_sucursal' => 'SUR-001',
            'suc_cod_fecha_vigencia' => '2026-05-20',
            'alta_especial' => 'NO',
            'alta_especial_norma' => '',
            'alta_es_emisor' => 'SI',
            'alta_credito_fiscal' => 'NO',
            'alta_certificado_digital' => 'GESTION 1',
            'nombre_completo_firmante' => 'Maria Lopez',
            'ci_firmante' => '45678901',
            'observaciones' => 'Demo visual basada en el panel de referencia del cliente.',
            'estado_detalle' => 'Frenado por timeout al sincronizar sucursal.',
            'aprobada' => 1,
            'empresa_creada' => 0,
            'cliente_creado' => 0,
            'notas_admin' => 'Pendiente reintento automatico de Migrate.',
            'error_proceso' => 'Timeout al conectar con servicio externo.',
        ],
        [
            'is_demo' => 1,
            'id' => 1002,
            'estado' => ESTADO_PENDIENTE_APROBACION,
            'fecha_creacion' => '2026-05-21 11:45:00',
            'razon_social' => 'Logistica Global S.A.',
            'nombre_fantasia' => 'Logistica Global',
            'rut' => '214455880018',
            'email_principal' => 'operaciones@logglobal.com',
            'domicilio' => 'Av. Italia 4455, Montevideo',
            'telefono' => '098765432',
            'ciudad' => 'Montevideo',
            'departamento' => 'Montevideo',
            'usuario_ef' => 'usr_logist_glob',
            'clave_usuario_ef' => 'ClaveProvisoria123_!',
            'licencia' => 3,
            'licencia_texto' => 'SaaS Standard',
            'plan' => 'SaaS Standard',
            'usuarios' => 5,
            'cfe_mensuales' => 800,
            'cliente_id_giro' => 20,
            'cliente_id_fidelizacion' => 2,
            'email_envio_fe' => 'facturas@logglobal.com',
            'cliente_abonado_importe' => 1600,
            'cliente_abonado_moneda' => 'UYU',
            'cliente_abonado_periodo' => 'MENSUAL',
            'cliente_abonado_descuento' => 0,
            'suc_cod_sucursal' => 'LG-002',
            'suc_cod_fecha_vigencia' => '2026-05-21',
            'alta_especial' => 'NO',
            'alta_especial_norma' => '',
            'alta_es_emisor' => 'NO',
            'alta_credito_fiscal' => 'RESGUARDO',
            'alta_certificado_digital' => 'SOLICITUD 1',
            'nombre_completo_firmante' => 'Carlos Mendez',
            'ci_firmante' => '40333444',
            'observaciones' => 'Demo visual basada en el panel de referencia del cliente.',
            'estado_detalle' => 'Esperando aprobacion manual del administrador.',
            'aprobada' => 0,
            'empresa_creada' => 0,
            'cliente_creado' => 0,
            'notas_admin' => '',
            'error_proceso' => '',
        ],
        [
            'is_demo' => 1,
            'id' => 1003,
            'estado' => ESTADO_APROBADO,
            'fecha_creacion' => '2026-05-15 08:15:00',
            'razon_social' => 'Sistemas del Norte S.R.L.',
            'nombre_fantasia' => 'Sistemas del Norte',
            'rut' => '218877660022',
            'email_principal' => 'admin@sistemasnorte.com',
            'domicilio' => 'Parque Industrial Norte 102, Salto',
            'telefono' => '097000111',
            'ciudad' => 'Salto',
            'departamento' => 'Salto',
            'usuario_ef' => 'usr_sist_norte',
            'clave_usuario_ef' => 'NorthSecure_2026!',
            'licencia' => 0,
            'licencia_texto' => 'SaaS Professional',
            'plan' => 'SaaS Professional',
            'usuarios' => 8,
            'cfe_mensuales' => 1400,
            'cliente_id_giro' => 30,
            'cliente_id_fidelizacion' => 1,
            'email_envio_fe' => 'facturas@sistemasnorte.com',
            'cliente_abonado_importe' => 2100,
            'cliente_abonado_moneda' => 'UYU',
            'cliente_abonado_periodo' => 'MENSUAL',
            'cliente_abonado_descuento' => 0,
            'suc_cod_sucursal' => 'SN-003',
            'suc_cod_fecha_vigencia' => '2026-05-15',
            'alta_especial' => 'EXONERADO',
            'alta_especial_norma' => 'Literal E',
            'alta_es_emisor' => 'SI',
            'alta_credito_fiscal' => 'LITERAL E',
            'alta_certificado_digital' => 'ADJUNTO',
            'nombre_completo_firmante' => 'Laura Pereira',
            'ci_firmante' => '38999111',
            'observaciones' => 'Demo visual basada en el panel de referencia del cliente.',
            'estado_detalle' => 'Alta base completada y cliente provisionado.',
            'aprobada' => 1,
            'empresa_creada' => 1,
            'cliente_creado' => 1,
            'notas_admin' => 'Listo para continuar con el resto del workflow.',
            'error_proceso' => '',
        ],
    ];
}

function licenciaEtiqueta(array $item): string
{
    $codigo = (int) ($item['licencia'] ?? -1);
    if ($codigo >= 0 && isset(LICENCIAS_DISPONIBLES[$codigo])) {
        return LICENCIAS_DISPONIBLES[$codigo];
    }

    return !empty($item['licencia_texto']) ? (string) $item['licencia_texto'] : 'Sin definir';
}

function workflowEventsMap(array $history): array
{
    $mapped = [];
    foreach ($history as $event) {
        $mapped[(string) ($event['evento'] ?? '')] = $event;
    }
    return $mapped;
}

function listHistoricalLicenseIssue(array $history): string
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

function listMigrateSummary(array $item): array
{
    $responseXml = trim((string) ($item['migrate_response_xml'] ?? ''));
    $summary = [
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
        return $summary;
    }

    $licNodes = $xml->xpath('//LicMsgRetorno');
    if (is_array($licNodes) && isset($licNodes[0])) {
        $summary['lic_msg_retorno'] = trim((string) $licNodes[0]);
        $summary['lic_rejected'] = listIsNegativeMigrateMessage($summary['lic_msg_retorno']);
    }

    return $summary;
}

function listIsNegativeMigrateMessage(string $message): bool
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

function workflowCurrentKey(array $item, array $history): string
{
    $estado = (string) ($item['estado'] ?? '');
    $persisted = trim((string) ($item['hito_actual'] ?? ''));
    $events = workflowEventsMap($history);
    $empresaCreada = (int) ($item['empresa_creada'] ?? 0) === 1;
    $clienteCreado = (int) ($item['cliente_creado'] ?? 0) === 1;
    $migrateSummary = listMigrateSummary($item);
    $historicalLicenseIssue = listHistoricalLicenseIssue($history);

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
        if ($persisted === 'PENDIENTE_DGI') {
            return 'HOMOLOGACION_DGI';
        }

        return $persisted;
    }

    if ($estado === ESTADO_ERROR_APROBACION) {
        return ($empresaCreada && $clienteCreado) ? 'MIGRATE' : 'APROBACION_PENDIENTE';
    }

    if ($estado === ESTADO_PENDIENTE_APROBACION) {
        return 'APROBACION_PENDIENTE';
    }

    if ($estado === ESTADO_APROBADO) {
        return ($empresaCreada && $clienteCreado) ? 'CERTIFICADO_DIGITAL' : 'DYNAMICA';
    }

    return 'APROBACION_PENDIENTE';
}

function workflowGeneralStatusMeta(array $item, string $current): array
{
    $estado = (string) ($item['estado'] ?? '');
    $empresaCreada = (int) ($item['empresa_creada'] ?? 0) === 1;
    $clienteCreado = (int) ($item['cliente_creado'] ?? 0) === 1;

    if ($estado === ESTADO_ELIMINADO) {
        return [
            'label' => 'Cancelado',
            'class' => 'bg-slate-100 text-slate-700 border border-slate-300',
            'dot' => 'bg-slate-400',
        ];
    }

    if ($estado === ESTADO_ERROR_APROBACION && $empresaCreada && $clienteCreado) {
        return [
            'label' => 'Migrate con novedad',
            'class' => 'bg-rose-50 text-rose-700 border border-rose-200',
            'dot' => 'bg-rose-500',
        ];
    }

    if ($current === 'MIGRATE_ERROR') {
        return [
            'label' => 'Migrate con novedad',
            'class' => 'bg-rose-50 text-rose-700 border border-rose-200',
            'dot' => 'bg-rose-500',
        ];
    }

    if ($current === 'CERTIFICADO_DIGITAL') {
        return [
            'label' => 'Certificado digital',
            'class' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'dot' => 'bg-amber-500',
        ];
    }

    if ($current === 'HOMOLOGACION_DGI') {
        return [
            'label' => u('Homologaci&oacute;n DGI'),
            'class' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'dot' => 'bg-amber-500',
        ];
    }

    if (in_array($current, ['ALTA_PENDIENTE', 'ENVIO_FACTURA', 'ENVIO_CREDENCIALES', 'ALTA_FINAL'], true)) {
        return [
            'label' => 'Alta pendiente',
            'class' => 'bg-orange-50 text-orange-700 border border-orange-200',
            'dot' => 'bg-orange-500',
        ];
    }

    if ($current === 'CLIENTE_ACTIVO') {
        return [
            'label' => 'Cliente activo',
            'class' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'dot' => 'bg-emerald-500',
        ];
    }

    if ($current === 'DYNAMICA') {
        return [
            'label' => 'Dynamica',
            'class' => 'bg-blue-50 text-blue-700 border border-blue-200',
            'dot' => 'bg-blue-500 animate-pulse',
        ];
    }

    if (in_array($current, ['EN_PROCESO', 'MIGRATE'], true)) {
        return [
            'label' => 'Migrate',
            'class' => 'bg-blue-50 text-blue-700 border border-blue-200',
            'dot' => 'bg-blue-500 animate-pulse',
        ];
    }

    return [
        'label' => u('Aprobaci&oacute;n pendiente'),
        'class' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
        'dot' => 'bg-indigo-500',
    ];
}

function buildListMeta(array $item, array $history): array
{
    $estado = (string) ($item['estado'] ?? '');
    $current = workflowCurrentKey($item, $history);
    $generalStatus = workflowGeneralStatusMeta($item, $current);
    $empresaCreada = (int) ($item['empresa_creada'] ?? 0) === 1;
    $clienteCreado = (int) ($item['cliente_creado'] ?? 0) === 1;

    if ($estado === ESTADO_ERROR_APROBACION && $empresaCreada && $clienteCreado) {
        return [
            'status_label' => $generalStatus['label'],
            'status_class' => $generalStatus['class'],
            'status_dot' => $generalStatus['dot'],
            'hito_key' => 'Migrate',
            'hito_text' => 'Migrate (Reintentar)',
            'hito_class' => 'bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 hover:text-rose-800',
            'hito_icon' => 'refresh-cw',
            'action_mode' => 'migrate',
            'timeline_mode' => 'error',
            'can_cancel' => true,
        ];
    }

    if ($current === 'MIGRATE_ERROR') {
        return [
            'status_label' => $generalStatus['label'],
            'status_class' => $generalStatus['class'],
            'status_dot' => $generalStatus['dot'],
            'hito_key' => 'Migrate',
            'hito_text' => 'Migrate (Reintentar)',
            'hito_class' => 'bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 hover:text-rose-800',
            'hito_icon' => 'refresh-cw',
            'action_mode' => 'migrate',
            'timeline_mode' => 'error',
            'can_cancel' => true,
        ];
    }

    if ($current === 'CLIENTE_ACTIVO') {
        return [
            'status_label' => $generalStatus['label'],
            'status_class' => $generalStatus['class'],
            'status_dot' => $generalStatus['dot'],
            'hito_key' => 'Cliente Activo',
            'hito_text' => 'Cliente Activo',
            'hito_class' => 'bg-slate-100 text-slate-700 border border-slate-300',
            'hito_icon' => 'check-circle-2',
            'action_mode' => 'done',
            'timeline_mode' => 'done',
            'can_cancel' => false,
        ];
    }

    if ($estado === ESTADO_ELIMINADO) {
        return [
            'status_label' => $generalStatus['label'],
            'status_class' => $generalStatus['class'],
            'status_dot' => $generalStatus['dot'],
            'hito_key' => 'Cancelado',
            'hito_text' => 'Onboarding Cancelado',
            'hito_class' => 'bg-slate-100 text-slate-400 border border-slate-200',
            'hito_icon' => 'x-circle',
            'action_mode' => 'disabled',
            'timeline_mode' => 'cancelled',
            'can_cancel' => false,
        ];
    }

    if ($current === 'CERTIFICADO_DIGITAL') {
        return [
            'status_label' => $generalStatus['label'],
            'status_class' => $generalStatus['class'],
            'status_dot' => $generalStatus['dot'],
            'hito_key' => 'Certificado Digital',
            'hito_text' => 'Certificado Digital',
            'hito_class' => 'bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 hover:text-indigo-800',
            'hito_icon' => 'file-badge',
            'action_mode' => 'change_hito',
            'next_hito' => 'CERTIFICADO_DIGITAL',
            'next_hito_label' => 'Marcar Certificado Digital',
            'timeline_mode' => 'progress',
            'can_cancel' => true,
        ];
    }

    if ($current === 'HOMOLOGACION_DGI') {
        return [
            'status_label' => $generalStatus['label'],
            'status_class' => $generalStatus['class'],
            'status_dot' => $generalStatus['dot'],
            'hito_key' => u('Homologaci&oacute;n DGI'),
            'hito_text' => u('Homologaci&oacute;n DGI'),
            'hito_class' => 'bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 hover:text-indigo-800',
            'hito_icon' => 'stamp',
            'action_mode' => 'change_hito',
            'next_hito' => 'HOMOLOGACION_DGI',
            'next_hito_label' => u('Marcar Homologaci&oacute;n DGI'),
            'timeline_mode' => 'progress',
            'can_cancel' => true,
        ];
    }

    if ($current === 'ENVIO_FACTURA') {
        return [
            'status_label' => $generalStatus['label'],
            'status_class' => $generalStatus['class'],
            'status_dot' => $generalStatus['dot'],
            'hito_key' => 'Alta Pendiente',
            'hito_text' => 'Envio de Factura',
            'hito_class' => 'bg-orange-50 text-orange-700 border border-orange-200',
            'hito_icon' => 'receipt',
            'action_mode' => 'disabled',
            'timeline_mode' => 'progress',
            'can_cancel' => true,
        ];
    }

    if ($current === 'ENVIO_CREDENCIALES') {
        return [
            'status_label' => $generalStatus['label'],
            'status_class' => $generalStatus['class'],
            'status_dot' => $generalStatus['dot'],
            'hito_key' => 'Alta Pendiente',
            'hito_text' => 'Envio de Credenciales',
            'hito_class' => 'bg-orange-50 text-orange-700 border border-orange-200',
            'hito_icon' => 'mail',
            'action_mode' => 'disabled',
            'timeline_mode' => 'progress',
            'can_cancel' => true,
        ];
    }

    if ($current === 'ALTA_PENDIENTE' || $current === 'ALTA_FINAL') {
        return [
            'status_label' => $generalStatus['label'],
            'status_class' => $generalStatus['class'],
            'status_dot' => $generalStatus['dot'],
            'hito_key' => 'Alta Pendiente',
            'hito_text' => 'Alta Final (Ejecutar)',
            'hito_class' => 'bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 hover:text-indigo-800',
            'hito_icon' => 'user-check',
            'action_mode' => 'change_hito',
            'next_hito' => 'ALTA_FINAL',
            'next_hito_label' => 'Ejecutar Alta Final',
            'timeline_mode' => 'progress',
            'can_cancel' => true,
        ];
    }

    if (in_array($current, ['EN_PROCESO', 'MIGRATE'], true)) {
        return [
            'status_label' => $generalStatus['label'],
            'status_class' => $generalStatus['class'],
            'status_dot' => $generalStatus['dot'],
            'hito_key' => 'Migrate',
            'hito_text' => 'Migrate (Ejecutar)',
            'hito_class' => 'bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 hover:text-indigo-800',
            'hito_icon' => 'send',
            'action_mode' => 'migrate',
            'next_hito_label' => 'Ejecutar Migrate',
            'timeline_mode' => 'progress',
            'can_cancel' => true,
        ];
    }

    return [
        'status_label' => $generalStatus['label'],
        'status_class' => $generalStatus['class'],
        'status_dot' => $generalStatus['dot'],
        'hito_key' => u('Aprobaci&oacute;n pendiente'),
        'hito_text' => u('Aprobaci&oacute;n pendiente (Aprobar)'),
        'hito_class' => 'bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 hover:text-indigo-800',
        'hito_icon' => 'shield',
        'action_mode' => 'approve',
        'timeline_mode' => 'pending',
        'can_cancel' => true,
    ];
}

function formatWorkflowDate(?string $value): string
{
    if (!$value) {
        return '';
    }

    $ts = strtotime($value);
    return $ts ? date('d/m/Y H:i', $ts) : (string) $value;
}

function deferredTaskScheduleNote(?array $task): string
{
    if (!$task) {
        return '';
    }

    $estado = strtoupper(trim((string) ($task['Estado'] ?? '')));
    if (!in_array($estado, ['PENDIENTE', 'PROCESANDO'], true)) {
        return '';
    }

    $scheduledAt = formatWorkflowDate((string) ($task['ProgramadoPara'] ?? ''));
    if ($scheduledAt === '') {
        return '';
    }

    return 'Programado para entrega el ' . $scheduledAt . '.';
}

function dynamicaLoginForItem(array $item): string
{
    $licencia = (int) ($item['licencia'] ?? 0);
    if (!WorkflowHelper::licenseCreatesDynamicaUser($licencia)) {
        return 'No aplica';
    }

    $rut = preg_replace('/\D+/', '', (string) ($item['rut'] ?? ''));
    return $rut !== '' ? $rut : '-';
}

function dynamicaPasswordForItem(array $item): string
{
    $licencia = (int) ($item['licencia'] ?? 0);
    if (!WorkflowHelper::licenseCreatesDynamicaUser($licencia)) {
        return 'No aplica';
    }

    $sourceDate = (string) (($item['fecha_aprobacion'] ?? '') ?: ($item['fecha_creacion'] ?? ''));
    $ts = strtotime($sourceDate);
    return $ts ? date('dmY', $ts) : '-';
}

function buildTimeline(array $item, array $meta, array $history, ?array $deferredTask = null): array
{
    $events = workflowEventsMap($history);
    $current = workflowCurrentKey($item, $history);
    $migrateSummary = listMigrateSummary($item);
    $historicalLicenseIssue = listHistoricalLicenseIssue($history);
    $empresaCreada = (int) ($item['empresa_creada'] ?? 0) === 1;
    $clienteCreado = (int) ($item['cliente_creado'] ?? 0) === 1;
    $fechaAprobacion = trim((string) ($item['fecha_aprobacion'] ?? ''));
    $modoCert = strtoupper(trim((string) ($item['alta_certificado_digital'] ?? '')));
$certificadoDone =
    isset($events['HITO_CERTIFICADO_DIGITAL'])
    || isset($events['HITO_HOMOLOGACION_DGI'])
    || isset($events['HITO_PENDIENTE_DGI'])
    || isset($events['HITO_ALTA_PENDIENTE'])
    || isset($events['HITO_CLIENTE_ACTIVO'])
    || $modoCert === 'ADJUNTO';
    $workflowReachedMigrate = in_array($current, ['CERTIFICADO_DIGITAL', 'HOMOLOGACION_DGI', 'ENVIO_FACTURA', 'ENVIO_CREDENCIALES', 'ALTA_FINAL', 'CLIENTE_ACTIVO', 'MIGRATE_ERROR'], true);
    $steps = [
        ['key' => 'HITO_CARPETA', 'title' => '1. Hito Carpeta', 'type' => 'Auto', 'state' => 'pending', 'desc' => u('Creaci&oacute;n autom&aacute;tica del directorio de archivos.'), 'substeps' => []],
        ['key' => 'HITO_EN_PROCESO', 'title' => u('2. Aprobaci&oacute;n pendiente'), 'type' => 'Manual', 'state' => 'pending', 'desc' => u('Pendiente de aprobaci&oacute;n administrativa.'), 'substeps' => []],
        ['key' => 'HITO_DYNAMICA_OK', 'title' => '3. Dynamica', 'type' => 'Auto', 'state' => 'pending', 'desc' => u('Creaci&oacute;n de empresa, cliente y usuario seg&uacute;n licencia.'), 'substeps' => []],
        ['key' => 'HITO_MIGRATE_OK', 'title' => '4. Migrate', 'type' => 'Auto', 'state' => 'pending', 'desc' => u('Registro de empresa, sucursal y usuario seg&uacute;n licencia.'), 'substeps' => []],
        ['key' => 'HITO_CERTIFICADO_DIGITAL', 'title' => '5. Certificado Digital', 'type' => 'Manual', 'state' => 'pending', 'desc' => 'Gestion manual del certificado digital.', 'substeps' => []],
        ['key' => 'HITO_PENDIENTE_DGI', 'title' => u('6. Homologaci&oacute;n DGI'), 'type' => 'Manual', 'state' => 'pending', 'desc' => u('Tramitaci&oacute;n gubernamental y gesti&oacute;n DGI.'), 'substeps' => []],
        ['key' => 'HITO_ENVIO_FACTURA', 'title' => u('7. Env&iacute;o de Factura'), 'type' => 'Auto', 'state' => 'pending', 'desc' => u('Primera factura o activaci&oacute;n de facturaci&oacute;n autom&aacute;tica.'), 'substeps' => []],
        ['key' => 'HITO_ENVIO_CREDENCIALES', 'title' => '8. Envio de Credenciales', 'type' => 'Auto', 'state' => 'pending', 'desc' => 'Envio de credenciales seguras al cliente.', 'substeps' => []],
        ['key' => 'HITO_CLIENTE_ACTIVO', 'title' => '9. Alta Final', 'type' => 'Auto', 'state' => 'pending', 'desc' => 'Activacion final del cliente en produccion.', 'substeps' => []],
    ];

    $currentByStep = [
        'APROBACION_PENDIENTE' => 'HITO_EN_PROCESO',
        'DYNAMICA' => 'HITO_DYNAMICA_OK',
        'MIGRATE' => 'HITO_MIGRATE_OK',
        'EN_PROCESO' => 'HITO_MIGRATE_OK',
        'CERTIFICADO_DIGITAL' => 'HITO_CERTIFICADO_DIGITAL',
        'HOMOLOGACION_DGI' => 'HITO_PENDIENTE_DGI',
        'ALTA_PENDIENTE' => 'HITO_ALTA_PENDIENTE',
        'CLIENTE_ACTIVO' => 'HITO_CLIENTE_ACTIVO',
        'ERROR_APROBACION' => 'HITO_MIGRATE_OK',
        'MIGRATE_ERROR' => 'HITO_MIGRATE_OK',
    ];
    $currentStepKey = $currentByStep[$current] ?? 'HITO_EN_PROCESO';

    foreach ($steps as &$step) {
        if ($step['key'] === 'HITO_CARPETA') {
            $step['state'] = ((int) ($item['carpeta_creada'] ?? 0) === 1) ? 'done' : 'pending';
            $step['substeps'] = [
                $step['state'] === 'done'
                    ? 'Directorio de archivos creado.'
                    : u('Pendiente creaci&oacute;n autom&aacute;tica del directorio de archivos.'),
            ];
            if ($step['state'] === 'done') {
                $date = formatWorkflowDate($item['fecha_creacion'] ?? null);
                $step['desc'] = $date !== '' ? 'Directorio de archivos creado. - ' . $date : 'Directorio de archivos creado.';
            }
            continue;
        }

        if ($step['key'] === 'HITO_EN_PROCESO') {
            $event = $events['HITO_EN_PROCESO'] ?? $events['APROBACION'] ?? null;
            $done = $event !== null || $empresaCreada || $clienteCreado || $fechaAprobacion !== '' || $current !== 'APROBACION_PENDIENTE';
            if ($done) {
                $step['state'] = 'done';
                $desc = trim((string) (($event['descripcion'] ?? '') ?: 'Aprobado por Admin. Dynamica completado; pendiente Migrate.'));
                $date = formatWorkflowDate($event['fecha_evento'] ?? ($item['fecha_aprobacion'] ?? ($item['fecha_actualizacion'] ?? null)));
                $step['desc'] = $date !== '' ? $desc . ' - ' . $date : $desc;
            } elseif ($step['key'] === $currentStepKey && $meta['timeline_mode'] !== 'done' && $meta['timeline_mode'] !== 'cancelled') {
                $step['state'] = $meta['timeline_mode'] === 'error' ? 'error' : 'current';
            }
            continue;
        }

        if ($step['key'] === 'HITO_DYNAMICA_OK') {
            $step['state'] = ($empresaCreada && $clienteCreado) ? 'done' : 'pending';
            $step['substeps'] = [
                $step['state'] === 'done'
                    ? 'Empresa y cliente creados en tablas Dynamica.'
                    : u('Pendiente creaci&oacute;n de empresa y cliente en tablas Dynamica.'),
                WorkflowHelper::licenseCreatesDynamicaUser((int) ($item['licencia'] ?? 0))
                    ? trim((string) (($events['HITO_DYNAMICA_USUARIO_OK']['descripcion'] ?? '') ?: 'Usuario Dynamica requerido.'))
                    : WorkflowHelper::dynamicaUserSubstepLabel((int) ($item['licencia'] ?? 0)),
            ];
            if ($step['state'] === 'done') {
                $event = $events['HITO_DYNAMICA_OK'] ?? $events['APROBACION'] ?? null;
                $step['desc'] = trim((string) (($event['descripcion'] ?? '') ?: 'Empresa y cliente creados en Dynamica.'));
                $userNote = WorkflowHelper::licenseCreatesDynamicaUser((int) ($item['licencia'] ?? 0))
                    ? trim((string) (($events['HITO_DYNAMICA_USUARIO_OK']['descripcion'] ?? '') ?: 'Usuario Dynamica creado.'))
                    : WorkflowHelper::dynamicaUserSubstepLabel((int) ($item['licencia'] ?? 0));
                $date = formatWorkflowDate($event['fecha_evento'] ?? ($item['fecha_aprobacion'] ?? null));
                $step['desc'] .= ' ' . $userNote;
                if ($date !== '') {
                    $step['desc'] .= ' - ' . $date;
                }
            }
            continue;
        }

        if ($step['key'] === 'HITO_MIGRATE_OK') {
            $eventOk = $events['HITO_MIGRATE_OK'] ?? null;
            $eventErr = $events['HITO_MIGRATE_ERROR'] ?? null;
            $done = $eventOk !== null || ($empresaCreada && $clienteCreado && $workflowReachedMigrate);
            $error = $eventErr !== null || $migrateSummary['lic_rejected'] || $historicalLicenseIssue !== '';

            if ($done) {
                $step['state'] = 'done';
            } elseif ($error) {
                $step['state'] = 'error';
            } elseif ($step['key'] === $currentStepKey && $meta['timeline_mode'] !== 'done' && $meta['timeline_mode'] !== 'cancelled') {
                $step['state'] = $meta['timeline_mode'] === 'error' ? 'error' : 'current';
            }

            if ($eventOk) {
                $desc = trim((string) ($eventOk['descripcion'] ?? 'Migrate OK.'));
                $date = formatWorkflowDate($eventOk['fecha_evento'] ?? null);
                $step['desc'] = $date !== '' ? $desc . ' - ' . $date : $desc;
            } elseif ($eventErr) {
                $desc = trim((string) ($eventErr['descripcion'] ?? $step['desc']));
                $date = formatWorkflowDate($eventErr['fecha_evento'] ?? null);
                $step['desc'] = $date !== '' ? $desc . ' - ' . $date : $desc;
            } elseif ($migrateSummary['lic_rejected']) {
                $step['desc'] = 'Migrate devolvio novedad en el licenciamiento.';
            } elseif ($historicalLicenseIssue !== '') {
                $step['desc'] = 'Migrate tiene una novedad historica de licenciamiento pendiente de resolver.';
            } elseif ($done) {
                $date = formatWorkflowDate($item['fecha_actualizacion'] ?? ($item['fecha_aprobacion'] ?? null));
                $step['desc'] = $date !== '' ? 'Migrate OK. - ' . $date : 'Migrate OK.';
            }

            $step['substeps'] = [
                $done
                    ? ['state' => 'done', 'text' => 'Empresa y sucursal registradas en Migrate.']
                    : ['state' => 'pending', 'text' => 'Pendiente registro de empresa y sucursal en Migrate.'],
                $migrateSummary['lic_rejected']
                    ? ['state' => 'error', 'text' => 'Licenciamiento rechazado por Migrate: ' . $migrateSummary['lic_msg_retorno']]
                    : ($historicalLicenseIssue !== ''
                        ? ['state' => 'error', 'text' => 'Licenciamiento rechazado en intento previo. Validar antes de continuar.']
                        : ($done
                            ? ['state' => 'done', 'text' => 'Licenciamiento enviado dentro del RegistroEmpresa.']
                            : ['state' => 'pending', 'text' => 'Pendiente envio de licenciamiento a Migrate.'])),
                WorkflowHelper::licenseCreatesMigrateUser((int) ($item['licencia'] ?? 0))
                    ? ['state' => $done ? 'warning' : 'pending', 'text' => trim((string) (($events['HITO_MIGRATE_USUARIO_ENVIADO']['descripcion'] ?? $events['HITO_MIGRATE_USUARIO_OK']['descripcion'] ?? '') ?: 'Usuario Migrate enviado dentro del RegistroEmpresa. Validar alta efectiva en Migrate.'))]
                    : ['state' => 'na', 'text' => WorkflowHelper::migrateUserSubstepLabel((int) ($item['licencia'] ?? 0))],
            ];
            continue;
        }

        if ($step['key'] === 'HITO_CERTIFICADO_DIGITAL') {
            if ($certificadoDone) {
                $step['state'] = 'done';
                $step['desc'] = $modoCert === 'ADJUNTO'
                    ? 'Certificado digital recibido y cargado.'
                    : 'Certificado digital gestionado manualmente.';
            } elseif ($modoCert !== '') {
                $step['desc'] = 'Modo de certificado definido: ' . trim((string) ($item['alta_certificado_digital'] ?? ''));
                if ($current === 'CERTIFICADO_DIGITAL' && $meta['timeline_mode'] !== 'done' && $meta['timeline_mode'] !== 'cancelled') {
                    $step['state'] = 'current';
                }
            } elseif ($step['key'] === $currentStepKey && $meta['timeline_mode'] !== 'done' && $meta['timeline_mode'] !== 'cancelled') {
                $step['state'] = $meta['timeline_mode'] === 'error' ? 'error' : 'current';
            }
            continue;
        }

        if ($step['key'] === 'HITO_PENDIENTE_DGI') {
            $done = isset($events['HITO_HOMOLOGACION_DGI']) || isset($events['HITO_ALTA_PENDIENTE']) || isset($events['HITO_CLIENTE_ACTIVO']);
            if ($done) {
                $step['state'] = 'done';
            } elseif ($current === 'HOMOLOGACION_DGI' && $certificadoDone && $meta['timeline_mode'] !== 'done' && $meta['timeline_mode'] !== 'cancelled') {
                $step['state'] = 'current';
            }

            $dgiEvent = $events['HITO_HOMOLOGACION_DGI'] ?? $events[$step['key']] ?? null;
            if ($dgiEvent) {
                $desc = trim((string) ($dgiEvent['descripcion'] ?? $step['desc']));
                $date = formatWorkflowDate($dgiEvent['fecha_evento'] ?? null);
                $step['desc'] = $date !== '' ? $desc . ' - ' . $date : $desc;
            }

            continue;
        }

        if ($step['key'] === 'HITO_ENVIO_CREDENCIALES' && !isset($events[$step['key']])) {
            $note = deferredTaskScheduleNote($deferredTask);
            if ($note !== '' && $current === 'ENVIO_CREDENCIALES') {
                $step['state'] = 'current';
                $step['desc'] = 'Envio de credenciales programado para el siguiente ciclo automatico.';
                $step['note'] = $note;
                continue;
            }
        }

            if (isset($events[$step['key']])) {
                $step['state'] = 'done';
                $desc = trim((string) ($events[$step['key']]['descripcion'] ?? 'Completado.'));
                if ($step['key'] === 'HITO_MIGRATE_OK') {
                    $step['substeps'] = [
                        ['state' => 'done', 'text' => 'Empresa y sucursal registradas en Migrate.'],
                        $migrateSummary['lic_rejected']
                            ? ['state' => 'error', 'text' => 'Licenciamiento rechazado por Migrate: ' . $migrateSummary['lic_msg_retorno']]
                            : ($historicalLicenseIssue !== ''
                                ? ['state' => 'error', 'text' => 'Licenciamiento rechazado en intento previo. Validar antes de continuar.']
                                : ['state' => 'done', 'text' => 'Licenciamiento enviado dentro del RegistroEmpresa.']),
                        WorkflowHelper::licenseCreatesMigrateUser((int) ($item['licencia'] ?? 0))
                        ? ['state' => 'warning', 'text' => trim((string) (($events['HITO_MIGRATE_USUARIO_ENVIADO']['descripcion'] ?? $events['HITO_MIGRATE_USUARIO_OK']['descripcion'] ?? '') ?: 'Usuario Migrate enviado dentro del RegistroEmpresa. Validar alta efectiva en Migrate.'))]
                        : ['state' => 'na', 'text' => WorkflowHelper::migrateUserSubstepLabel((int) ($item['licencia'] ?? 0))],
                    ];
                $userNote = WorkflowHelper::licenseCreatesMigrateUser((int) ($item['licencia'] ?? 0))
                    ? trim((string) (($events['HITO_MIGRATE_USUARIO_ENVIADO']['descripcion'] ?? $events['HITO_MIGRATE_USUARIO_OK']['descripcion'] ?? '') ?: 'Usuario Migrate enviado dentro del RegistroEmpresa. Validar alta efectiva en Migrate.'))
                    : WorkflowHelper::migrateUserSubstepLabel((int) ($item['licencia'] ?? 0));
                $desc .= ' ' . ($migrateSummary['lic_rejected']
                    ? 'Licenciamiento rechazado por Migrate. '
                    : ($historicalLicenseIssue !== ''
                        ? 'Licenciamiento rechazado en intento previo. '
                        : 'Licenciamiento enviado dentro del RegistroEmpresa. ')) . $userNote;
            }
            $date = formatWorkflowDate($events[$step['key']]['fecha_evento'] ?? null);
            $step['desc'] = $date !== '' ? $desc . ' - ' . $date : $desc;
        } elseif ($step['key'] === $currentStepKey && $meta['timeline_mode'] !== 'done' && $meta['timeline_mode'] !== 'cancelled') {
            $step['state'] = $meta['timeline_mode'] === 'error' ? 'error' : 'current';
            if ($meta['timeline_mode'] === 'error') {
                $step['desc'] = (string) (($item['estado_detalle'] ?? '') ?: 'Frenado por error tecnico en integracion.');
            }
            if ($step['key'] === 'HITO_MIGRATE_OK') {
                $step['substeps'] = [
                    'Pendiente registro de empresa y sucursal en Migrate.',
                    'Pendiente envio de licenciamiento a Migrate.',
                    WorkflowHelper::licenseCreatesMigrateUser((int) ($item['licencia'] ?? 0))
                        ? 'Usuario Migrate pendiente dentro del RegistroEmpresa.'
                        : WorkflowHelper::migrateUserSubstepLabel((int) ($item['licencia'] ?? 0)),
                ];
            }
        }
    }

    unset($step);

    if ($meta['timeline_mode'] === 'cancelled') {
        $steps[0]['state'] = 'error';
        $steps[0]['desc'] = (string) (($item['motivo_eliminacion'] ?? '') ?: 'Proceso cancelado por administracion.');
    }

    return $steps;
}

function substepMeta(string $stepState, string $substep): array
{
    $text = mb_strtolower(trim($substep));
    $hasText = static function (string $needle) use ($text): bool {
        return $needle !== '' && mb_strpos($text, $needle) !== false;
    };

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

    if ($stepState === 'na') {
        return [
            'wrapper' => 'bg-slate-100 border-slate-200',
            'iconWrap' => 'bg-slate-200 text-slate-500',
            'icon' => 'minus-circle',
            'text' => 'text-slate-600 line-through',
        ];
    }

    if ($stepState === 'done' || $hasText('incluido') || $hasText('creado') || $hasText('cargado') || $hasText('gestionado')) {
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

function timelineClasses(string $state): array
{
    switch ($state) {
        case 'done':
            return [
                'wrapper' => 'flex items-start gap-3 p-2 rounded-lg bg-slate-50',
                'iconWrap' => 'flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600',
                'icon' => 'check',
                'title' => 'text-xs font-bold text-slate-700',
                'desc' => 'text-[10px] text-slate-400',
                'type' => 'bg-indigo-100 text-indigo-700',
            ];
        case 'current':
            return [
                'wrapper' => 'flex items-start gap-3 p-2 rounded-lg bg-indigo-50 border border-indigo-100',
                'iconWrap' => 'flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600',
                'icon' => 'shield',
                'title' => 'text-xs font-bold text-indigo-900',
                'desc' => 'text-[10px] text-indigo-700',
                'type' => 'bg-slate-200 text-slate-600',
            ];
        case 'error':
            return [
                'wrapper' => 'flex items-start gap-3 p-2 rounded-lg bg-rose-50 border border-rose-100',
                'iconWrap' => 'flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600',
                'icon' => 'alert-circle',
                'title' => 'text-xs font-bold text-rose-900',
                'desc' => 'text-[10px] text-rose-600',
                'type' => 'bg-rose-200 text-rose-700',
            ];
        default:
            return [
                'wrapper' => 'flex items-start gap-3 p-2 opacity-50',
                'iconWrap' => 'flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400',
                'icon' => 'circle',
                'title' => 'text-xs font-bold text-slate-500',
                'desc' => 'text-[10px] text-slate-400',
                'type' => 'bg-slate-100 text-slate-500',
            ];
    }
}

$enableCreateModal = true;
$showListLink = false;
$activeNav = 'panel';
$pageTitle = $pageTitle ?? 'Altas y Automatizaciones';
$pageSubtitle = 'Aprovisionamiento, aprobaciones y seguimiento de nuevas empresas.';
$values = $_SESSION['old'] ?? [];
$sessionFormErrors = array_values($_SESSION['errors'] ?? []);
$hasActiveRealRows = count(array_filter($items ?? [], static function (array $item): bool {
    return (string) ($item['estado'] ?? '') !== ESTADO_ELIMINADO;
})) > 0;
$rows = $hasActiveRealRows ? ($items ?? []) : demoEmpresasNuevasRows();
$modalValues = $values;
$isDemoFallback = !$hasActiveRealRows;
$currentPage = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['total_pages'] ?? 1);
$totalItems = (int) ($pagination['total_items'] ?? count($rows));
require __DIR__ . '/../layout/header.php';
unset($_SESSION['old']);
unset($_SESSION['errors']);
?>

<script>
window.CREATE_FORM_OLD = <?= json_encode($modalValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.CREATE_FORM_ERRORS = <?= json_encode($sessionFormErrors, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<div id="toast-notificacion" class="hidden transform translate-y-2 opacity-0 transition-all duration-300 fixed bottom-5 right-5 z-50 bg-slate-900 text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-3">
    <div class="bg-emerald-500 p-1 rounded-full text-white" id="toast-icon-container">
        <i data-lucide="check" class="w-4 h-4"></i>
    </div>
    <div>
        <p class="text-xs text-slate-400 font-semibold" id="toast-title">Notificacion</p>
        <p class="text-sm font-medium" id="toast-msg">Operacion completada con exito.</p>
    </div>
</div>

<div id="vista-listado" class="space-y-4">
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col lg:flex-row gap-3 items-center justify-between">
        <div class="relative w-full lg:w-96">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <i data-lucide="search" class="w-5 h-5"></i>
            </span>
            <input type="text" id="filtro-busqueda" placeholder="Buscar por Razon Social o RUT..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>

        <div class="flex flex-wrap gap-3 w-full lg:w-auto">
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg text-sm w-full sm:w-auto">
                <span class="text-slate-500 font-medium text-xs uppercase tracking-wider">Estado general:</span>
                <select id="filtro-estado" class="bg-transparent border-none focus:outline-none text-slate-700 font-semibold cursor-pointer">
                    <option value="todos">Todos</option>
                    <option value="<?= h(u('Aprobaci&oacute;n pendiente')) ?>"><?= h(u('Aprobaci&oacute;n pendiente')) ?></option>
                    <option value="Dynamica">Dynamica</option>
                    <option value="Migrate">Migrate</option>
                    <option value="Certificado digital">Certificado digital</option>
                    <option value="Homologación DGI">Homologación DGI</option>
                    <option value="Alta pendiente">Alta pendiente</option>
                    <option value="Cliente activo">Cliente activo</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>

            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg text-sm w-full sm:w-auto">
                <span class="text-slate-500 font-medium text-xs uppercase tracking-wider">Hito Actual:</span>
                <select id="filtro-hito" class="bg-transparent border-none focus:outline-none text-slate-700 font-semibold cursor-pointer">
                    <option value="todos">Todos los hitos</option>
                    <option value="<?= h(u('Aprobaci&oacute;n pendiente')) ?>"><?= h(u('Aprobaci&oacute;n pendiente')) ?></option>
                    <option value="Dynamica">Dynamica</option>
                    <option value="Migrate">Migrate</option>
                    <option value="Certificado Digital">Certificado Digital</option>
                    <option value="Homologación DGI">Homologación DGI</option>
                    <option value="Alta Pendiente">Alta pendiente</option>
                    <option value="Cliente Activo">Cliente activo</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse" id="tabla-clientes">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs font-semibold tracking-wider uppercase">
                        <th class="py-4 px-5 w-12 text-center">Info</th>
                        <th class="py-4 px-4">Fecha Reg.</th>
                        <th class="py-4 px-4">Razon Social</th>
                        <th class="py-4 px-4">Estado General</th>
                        <th class="py-4 px-4">Licencia</th>
                        <th class="py-4 px-4 text-center">Es Emisor</th>
                        <th class="py-4 px-4">Hito / Accion Requerida</th>
                        <th class="py-4 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    <?php foreach ($rows as $item): ?>
                        <?php
                        $itemId = (int) $item['id'];
                        $itemHistory = $workflowHistory[$item['id']] ?? [];
                        $meta = buildListMeta($item, $itemHistory);
                        $deferredTask = $deferredTasks[$itemId] ?? null;
                        $timeline = buildTimeline($item, $meta, $itemHistory, $deferredTask);
                        $licencia = licenciaEtiqueta($item);
                        $displayDate = !empty($item['fecha_creacion']) ? date('d/m/Y', strtotime((string) $item['fecha_creacion'])) : '-';
                        $recordForJs = $item;
                        $recordForJs['current_files'] = $fileMetaByNuevaEmpresa[$itemId] ?? [];
                        $recordJson = json_encode($recordForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        ?>
                        <tr class="hover:bg-slate-50/70 transition cursor-pointer row-registro" data-id="<?= $itemId ?>" data-status="<?= h($meta['status_label']) ?>" data-hito="<?= h($meta['hito_key']) ?>" data-record='<?= h((string) $recordJson) ?>' onclick="toggleFilaExpandida(<?= $itemId ?>, event)">
                            <td class="py-4 px-5 text-center">
                                <button class="text-slate-400 hover:text-indigo-600 transition" id="arrow-<?= $itemId ?>">
                                    <i data-lucide="chevron-right" class="w-4 h-4 transform transition-transform duration-200"></i>
                                </button>
                            </td>
                            <td class="py-4 px-4 text-slate-500 whitespace-nowrap"><?= h($displayDate) ?></td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900"><?= h((string) $item['razon_social']) ?></div>
                                <span class="text-xs text-slate-400">RUT: <?= h((string) $item['rut']) ?></span>
                            </td>
                            <td class="py-4 px-4">
                                <span id="badge-estado-<?= $itemId ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold <?= $meta['status_class'] ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $meta['status_dot'] ?>"></span>
                                    <?= h($meta['status_label']) ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 font-medium"><?= h($licencia) ?></td>
                            <td class="py-4 px-4 text-center">
                                <?php $esEmisor = strtoupper((string) ($item['alta_es_emisor'] ?? 'NO')) === 'SI'; ?>
                                <span class="inline-flex items-center justify-center <?= $esEmisor ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' ?> text-xs font-bold px-2.5 py-0.5 rounded-full"><?= $esEmisor ? 'SI' : 'NO' ?></span>
                            </td>
                            <td class="py-4 px-4" onclick="event.stopPropagation();">
                                <?php if (!empty($item['is_demo']) && $meta['action_mode'] === 'migrate'): ?>
                                    <button id="btn-hito-<?= $itemId ?>" onclick="reintentarHitoAutomatico(<?= $itemId ?>)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm <?= $meta['hito_class'] ?>" title="Hito automatico fallido. Clic para reintentar">
                                        <i data-lucide="<?= $meta['hito_icon'] ?>" class="w-3.5 h-3.5 text-rose-500"></i>
                                        <span><?= h($meta['hito_text']) ?></span>
                                    </button>
                                <?php elseif ($meta['action_mode'] === 'migrate' && empty($item['is_demo'])): ?>
                                    <form method="post" action="<?= h(app_url('index.php?action=run-migrate&id=' . $itemId)) ?>" data-confirm="Confirme la ejecucion del hito Migrate para este registro." data-busy-text="Espere un momento, por favor. Estamos gestionando Migrate.">
                                        <button id="btn-hito-<?= $itemId ?>" type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm <?= $meta['hito_class'] ?>">
                                            <i data-lucide="<?= $meta['hito_icon'] ?>" class="w-3.5 h-3.5 <?= ($item['estado'] ?? '') === ESTADO_ERROR_APROBACION ? 'text-rose-500' : '' ?>"></i>
                                            <span><?= h($meta['hito_text']) ?></span>
                                        </button>
                                    </form>
                                <?php elseif ($meta['action_mode'] === 'approve' && empty($item['is_demo'])): ?>
                                    <form method="post" action="<?= h(app_url('index.php?action=approve&id=' . $itemId)) ?>" data-confirm="Esto creara registros reales en Empresas y Clientes y movera el onboarding al siguiente hito." data-busy-text="Espere un momento, por favor. Estamos aprobando el registro.">
                                        <button id="btn-hito-<?= $itemId ?>" type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm <?= $meta['hito_class'] ?>">
                                            <i data-lucide="<?= $meta['hito_icon'] ?>" class="w-3.5 h-3.5"></i>
                                            <span><?= h($meta['hito_text']) ?></span>
                                        </button>
                                    </form>
                                <?php elseif (!empty($item['is_demo']) && $meta['action_mode'] === 'approve'): ?>
                                    <button id="btn-hito-<?= $itemId ?>" onclick="avanzarHitoManual(<?= $itemId ?>)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm <?= $meta['hito_class'] ?>">
                                        <i data-lucide="<?= $meta['hito_icon'] ?>" class="w-3.5 h-3.5"></i>
                                        <span><?= h($meta['hito_text']) ?></span>
                                    </button>
                                <?php elseif ($meta['action_mode'] === 'change_hito' && empty($item['is_demo'])): ?>
                                    <form method="post" action="<?= h(app_url('index.php?action=change-hito&id=' . $itemId)) ?>" data-confirm="Confirme el cambio del hito actual a: <?= h((string) $meta['next_hito_label']) ?>." data-busy-text="Espere un momento, por favor. Estamos actualizando el hito.">
                                        <input type="hidden" name="target_hito" value="<?= h((string) $meta['next_hito']) ?>">
                                        <button id="btn-hito-<?= $itemId ?>" type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm <?= $meta['hito_class'] ?>">
                                            <i data-lucide="<?= $meta['hito_icon'] ?>" class="w-3.5 h-3.5"></i>
                                            <span><?= h($meta['next_hito_label']) ?></span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span id="btn-hito-<?= $itemId ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold <?= $meta['hito_class'] ?>">
                                        <i data-lucide="<?= $meta['hito_icon'] ?>" class="w-3.5 h-3.5 <?= $meta['action_mode'] === 'done' ? 'text-emerald-500' : 'text-slate-400' ?>"></i>
                                        <?= h($meta['hito_text']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap" onclick="event.stopPropagation();">
                                <div class="flex justify-end gap-1">
                                    <button onclick="editarRegistro(<?= $itemId ?>)" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Editar datos del cliente">
                                        <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                                    </button>
                                    <?php if ($meta['can_cancel']): ?>
                                        <button id="btn-cancelar-<?= $itemId ?>" onclick="cancelarProceso(<?= $itemId ?>)" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Cancelar Proceso">
                                            <i data-lucide="x-circle" class="w-4.5 h-4.5"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="p-1.5 text-slate-300 rounded-lg cursor-not-allowed" title="Proceso completado o cancelado">
                                            <i data-lucide="x-circle" class="w-4.5 h-4.5"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr id="detalle-<?= $itemId ?>" class="hidden bg-slate-50/50">
                            <td colspan="8" class="p-6 border-t border-slate-100">
                                <div class="space-y-4">
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" class="detalle-tab-btn inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold bg-indigo-600 text-white shadow-sm" data-target="ruta-fiscal" data-id="<?= $itemId ?>" onclick="setDetailTab(<?= $itemId ?>, 'ruta-fiscal')">
                                            <i data-lucide="git-commit" class="w-3.5 h-3.5"></i>
                                            <span>Ruta y Fiscal</span>
                                        </button>
                                        <button type="button" class="detalle-tab-btn inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-600" data-target="resumen" data-id="<?= $itemId ?>" onclick="setDetailTab(<?= $itemId ?>, 'resumen')">
                                            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                                            <span>Resumen</span>
                                        </button>
                                    </div>

                                    <div id="detalle-pane-<?= $itemId ?>-ruta-fiscal" class="detalle-pane">
                                        <div class="grid grid-cols-1 xl:grid-cols-[1.2fr,0.8fr] gap-4">
                                            <div class="space-y-4 bg-white p-4 rounded-xl border border-slate-200">
                                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                                    <i data-lucide="git-commit" class="w-4 h-4 text-indigo-500"></i>
                                                    Ruta y Fiscal
                                                </h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <?php foreach ($timeline as $index => $step): ?>
                                                        <?php $classes = timelineClasses($step['state']); ?>
                                                        <div class="<?= $classes['wrapper'] ?> <?= $index === (count($timeline) - 1) ? 'md:col-span-2' : '' ?>">
                                                            <span class="<?= $classes['iconWrap'] ?>">
                                                                <i data-lucide="<?= $classes['icon'] ?>" class="w-3.5 h-3.5"></i>
                                                            </span>
                                                            <div>
                                                                <h5 class="<?= $classes['title'] ?>">
                                                                    <?= h($step['title']) ?>
                                                                    <span class="text-[9px] px-1 py-0.2 rounded font-normal <?= $classes['type'] ?>"><?= h($step['type']) ?></span>
                                                                </h5>
                                                                <p class="<?= $classes['desc'] ?>"><?= h($step['desc']) ?></p>
                                                                <?php if (!empty($step['note'])): ?>
                                                                    <p class="mt-1 text-[10px] font-semibold text-blue-600"><?= h((string) $step['note']) ?></p>
                                                                <?php endif; ?>
                                                                <?php if (!empty($step['substeps'])): ?>
                                                                    <div class="mt-2 rounded-lg border border-slate-200 bg-white/90 p-2 space-y-1.5">
                                                                        <p class="text-[9px] uppercase tracking-wider font-bold text-slate-400">Subhitos</p>
                            <?php foreach ($step['substeps'] as $substep): ?>
                                <?php
                                $substepText = is_array($substep) ? (string) ($substep['text'] ?? '') : (string) $substep;
                                $substepState = is_array($substep) ? (string) ($substep['state'] ?? '') : '';
                                if ($substepState === '') {
                                    $substepState = $step['state'] ?? 'pending';
                                }
                                $substepMeta = substepMeta($substepState, $substepText);
                                ?>
                                <div class="flex items-start gap-2 rounded-md border px-2 py-1.5 <?= $substepMeta['wrapper'] ?>">
                                    <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full <?= $substepMeta['iconWrap'] ?>">
                                        <i data-lucide="<?= $substepMeta['icon'] ?>" class="w-3 h-3"></i>
                                    </span>
                                    <p class="text-[10px] leading-4 font-medium <?= $substepMeta['text'] ?>"><?= h($substepText) ?></p>
                                </div>
                            <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>

                                            <div class="bg-white p-5 rounded-xl border border-slate-200 space-y-4 shadow-inner">
                                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                                    <i data-lucide="database" class="w-4 h-4 text-slate-500"></i>
                                                    Credenciales de Conexi&oacute;n Fiscal
                                                </h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <span class="block text-[11px] text-slate-400 font-bold uppercase">RUT</span>
                                                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                        <code class="font-mono text-slate-800 font-semibold" id="rut-val-<?= $itemId ?>"><?= h((string) $item['rut']) ?></code>
                                                        <button onclick="copiarAlPortapapeles('rut-val-<?= $itemId ?>')" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="block text-[11px] text-slate-400 font-bold uppercase">eFactura Usuario</span>
                                                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                        <code class="font-mono text-slate-800 font-semibold" id="usr-val-<?= $itemId ?>"><?= h((string) ($item['usuario_ef'] ?: '-')) ?></code>
                                                        <button onclick="copiarAlPortapapeles('usr-val-<?= $itemId ?>')" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                                    </div>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <span class="block text-[11px] text-slate-400 font-bold uppercase">eFactura Clave</span>
                                                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                        <input type="password" value="<?= h((string) ($item['clave_usuario_ef'] ?: '')) ?>" disabled class="font-mono text-slate-800 bg-transparent border-none w-full focus:outline-none text-xs font-semibold" id="pass-val-<?= $itemId ?>">
                                                        <button onclick="revelarClave('pass-val-<?= $itemId ?>', this)" class="text-slate-400 hover:text-indigo-600 transition mr-2" title="Revelar"><i data-lucide="eye" class="w-3.5 h-3.5"></i></button>
                                                        <button onclick="copiarAlPortapapeles('pass-val-<?= $itemId ?>', true)" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="block text-[11px] text-slate-400 font-bold uppercase">Login Dynamica</span>
                                                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                        <code class="font-mono text-slate-800 font-semibold" id="dyn-user-val-<?= $itemId ?>"><?= h(dynamicaLoginForItem($item)) ?></code>
                                                        <button onclick="copiarAlPortapapeles('dyn-user-val-<?= $itemId ?>')" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="block text-[11px] text-slate-400 font-bold uppercase">Clave Dynamica</span>
                                                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                        <code class="font-mono text-slate-800 font-semibold" id="dyn-pass-val-<?= $itemId ?>"><?= h(dynamicaPasswordForItem($item)) ?></code>
                                                        <button onclick="copiarAlPortapapeles('dyn-pass-val-<?= $itemId ?>')" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="detalle-pane-<?= $itemId ?>-resumen" class="detalle-pane hidden">
                                        <div class="bg-white p-5 rounded-xl border border-slate-200 space-y-4 shadow-inner">
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                                <i data-lucide="layout-dashboard" class="w-4 h-4 text-slate-500"></i>
                                                Resumen Operativo
                                            </h4>
                                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                                    <p class="uppercase tracking-wider text-slate-400 font-semibold">Licencia</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-800"><?= h($licencia) ?></p>
                                                </div>
                                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                                    <p class="uppercase tracking-wider text-slate-400 font-semibold">Plan</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-800"><?= h((string) ($item['plan'] ?: '-')) ?></p>
                                                </div>
                                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                                    <p class="uppercase tracking-wider text-slate-400 font-semibold">Usuarios</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-800"><?= h((string) ($item['usuarios'] ?: '0')) ?></p>
                                                </div>
                                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                                    <p class="uppercase tracking-wider text-slate-400 font-semibold">CFE Mensuales</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-800"><?= h((string) ($item['cfe_mensuales'] ?: '0')) ?></p>
                                                </div>
                                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                                    <p class="uppercase tracking-wider text-slate-400 font-semibold">Ciudad</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-800"><?= h((string) (($item['ciudad_nombre'] ?? $item['ciudad']) ?: '-')) ?></p>
                                                </div>
                                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                                    <p class="uppercase tracking-wider text-slate-400 font-semibold">Sucursal</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-800"><?= h((string) ($item['suc_cod_sucursal'] ?: '-')) ?></p>
                                                </div>
                                            </div>
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 space-y-1">
                                                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Mas info</p>
                                                <p class="text-xs text-slate-600">Certificado: <span class="font-semibold text-slate-800"><?= h((string) ($item['alta_certificado_digital'] ?: '-')) ?></span></p>
                                                <p class="text-xs text-slate-600">Firmante: <span class="font-semibold text-slate-800"><?= h((string) ($item['nombre_completo_firmante'] ?: '-')) ?></span></p>
                                                <p class="text-xs text-slate-600">Estado detalle: <span class="font-semibold text-slate-800"><?= h((string) ($item['estado_detalle'] ?: '-')) ?></span></p>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <a href="<?= htmlspecialchars(app_url('index.php?action=show&id=' . $itemId), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 bg-slate-900 border border-slate-900 hover:bg-slate-800 text-white font-medium px-3 py-2 rounded-lg text-xs transition">
                                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                                    <span>Ver ficha completa</span>
                                                </a>
                                                <button type="button" onclick="mostrarMasInfo(<?= $itemId ?>)" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-3 py-2 rounded-lg text-xs transition">
                                                    <i data-lucide="panel-right-open" class="w-3.5 h-3.5"></i>
                                                    <span>Mas info</span>
                                                </button>
                                                <button onclick="editarRegistro(<?= $itemId ?>)" class="inline-flex items-center gap-2 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-indigo-700 font-medium px-3 py-2 rounded-lg text-xs transition">
                                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                                    <span>Editar</span>
                                                </button>
                                                <?php if ($meta['can_cancel']): ?>
                                                    <button onclick="cancelarProceso(<?= $itemId ?>)" class="inline-flex items-center gap-2 bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 font-medium px-3 py-2 rounded-lg text-xs transition">
                                                        <i data-lucide="archive-x" class="w-3.5 h-3.5"></i>
                                                        <span>Cancelar proceso</span>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="empty-state" class="hidden">
                        <td colspan="8" class="py-10 px-6 text-center text-sm text-slate-400">No hay registros que coincidan con el filtro seleccionado.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 grid grid-cols-1 lg:grid-cols-[1fr_auto_1fr] items-center gap-3 text-xs text-slate-500 font-medium">
            <div class="space-y-1 text-center lg:text-left">
                <p>
                    Mostrando
                    <span id="num-registros-mostrados" class="text-slate-700 font-bold"><?= count($rows) ?></span>
                    de
                    <?= $isDemoFallback ? count($rows) : $totalItems ?>
                    registros de clientes
                </p>
                <?php if (!$isDemoFallback): ?>
                    <p class="text-[11px] text-slate-400">P&aacute;gina <?= $currentPage ?> de <?= $totalPages ?></p>
                <?php else: ?>
                    <p class="text-[11px] text-slate-400">Vista demo del panel de referencia</p>
                <?php endif; ?>
            </div>
            <div class="flex items-center justify-center gap-1.5">
                <?php if ($isDemoFallback): ?>
                    <button class="bg-white border border-slate-200 text-slate-400 px-3 py-1.5 rounded-lg transition disabled:opacity-50" disabled>Anterior</button>
                    <button class="bg-white border border-slate-200 text-slate-400 px-3 py-1.5 rounded-lg transition disabled:opacity-50" disabled>Siguiente</button>
                <?php else: ?>
                    <a href="<?= htmlspecialchars(app_url('index.php?page=' . (int) ($pagination['prev_page'] ?? 1)), ENT_QUOTES, 'UTF-8') ?>" class="bg-white border border-slate-200 <?= !empty($pagination['has_prev']) ? 'text-slate-700 hover:bg-slate-100' : 'text-slate-400 pointer-events-none opacity-60' ?> px-3 py-1.5 rounded-lg transition">Anterior</a>
                    <a href="<?= htmlspecialchars(app_url('index.php?page=' . (int) ($pagination['next_page'] ?? $totalPages)), ENT_QUOTES, 'UTF-8') ?>" class="bg-white border border-slate-200 <?= !empty($pagination['has_next']) ? 'text-slate-700 hover:bg-slate-100' : 'text-slate-400 pointer-events-none opacity-60' ?> px-3 py-1.5 rounded-lg transition">Siguiente</a>
                <?php endif; ?>
            </div>
            <div class="hidden lg:block"></div>
        </div>
    </div>
</div>

<div id="modal-cancelacion" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden transform transition-all duration-300 scale-95 border border-slate-100">
        <div class="p-6">
            <div class="bg-rose-100 w-12 h-12 rounded-full flex items-center justify-center text-rose-600 mb-4">
                <i data-lucide="alert-octagon" class="w-6 h-6"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900">Confirmas la cancelacion del proceso?</h3>
            <p class="text-sm text-slate-500 mt-2">
                Estas por suspender y archivar el proceso de alta para la empresa <strong id="modal-empresa-nombre" class="text-slate-800"></strong>.
            </p>
            <div class="mt-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Motivo de cancelacion (Requerido)</label>
                <textarea id="cancel-reason" rows="3" placeholder="Ej. El cliente solicita postergar el alta..." class="w-full p-2.5 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
            </div>
        </div>
        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
            <button onclick="cerrarModalCancelacion()" class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-semibold transition">
                Mantener Activo
            </button>
            <button onclick="confirmarCancelacion()" class="bg-rose-600 text-white hover:bg-rose-700 px-4 py-2 rounded-lg text-sm font-semibold transition shadow-md">
                Si, Cancelar Onboarding
            </button>
        </div>
    </div>
</div>

<div class="modal-shell" id="modal-info" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-info"></div>
    <div class="modal-panel modal-drawer">
        <div class="flex items-start justify-between gap-4 mb-5">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold uppercase tracking-wider">Mas Info</span>
                <h3 id="modal-info-title" class="mt-3 text-xl font-bold text-slate-900">Detalle del cliente</h3>
                <p id="modal-info-subtitle" class="mt-1 text-sm text-slate-500">Consulta rapida sin salir del listado.</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition" data-close-modal="modal-info" aria-label="Cerrar">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">RUT</p>
                    <p id="modal-info-rut" class="mt-1 text-sm font-semibold text-slate-900">-</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Estado</p>
                    <p id="modal-info-estado" class="mt-1 text-sm font-semibold text-slate-900">-</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Licencia</p>
                    <p id="modal-info-licencia" class="mt-1 text-sm font-semibold text-slate-900">-</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Plan</p>
                    <p id="modal-info-plan" class="mt-1 text-sm font-semibold text-slate-900">-</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Email principal</p>
                    <p id="modal-info-email" class="mt-1 text-sm font-semibold text-slate-900 break-all">-</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Telefono</p>
                    <p id="modal-info-telefono" class="mt-1 text-sm font-semibold text-slate-900">-</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-inner">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2 mb-3">
                    <i data-lucide="map-pinned" class="w-4 h-4 text-slate-500"></i>
                    Ubicacion y Operacion
                </h4>
                <div class="grid grid-cols-1 gap-3 text-sm">
                    <div><span class="text-slate-400 font-semibold">Domicilio:</span> <span id="modal-info-domicilio" class="text-slate-800 font-medium">-</span></div>
                    <div><span class="text-slate-400 font-semibold">Ciudad:</span> <span id="modal-info-ciudad" class="text-slate-800 font-medium">-</span></div>
                    <div><span class="text-slate-400 font-semibold">Sucursal:</span> <span id="modal-info-sucursal" class="text-slate-800 font-medium">-</span></div>
                    <div><span class="text-slate-400 font-semibold">Certificado:</span> <span id="modal-info-certificado" class="text-slate-800 font-medium">-</span></div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-inner">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2 mb-3">
                    <i data-lucide="file-text" class="w-4 h-4 text-slate-500"></i>
                    Observaciones
                </h4>
                <p id="modal-info-observaciones" class="text-sm text-slate-700 whitespace-pre-line">-</p>
            </div>

            <div class="flex flex-wrap gap-2 pt-2">
                <button type="button" id="modal-info-edit" class="inline-flex items-center gap-2 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-indigo-700 font-medium px-4 py-2.5 rounded-lg text-sm transition">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                    <span>Editar</span>
                </button>
                <button type="button" id="modal-info-cancel" class="inline-flex items-center gap-2 bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 font-medium px-4 py-2.5 rounded-lg text-sm transition">
                    <i data-lucide="archive-x" class="w-4 h-4"></i>
                    <span>Cancelar proceso</span>
                </button>
                <a id="modal-info-full-link" href="index.php" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-4 py-2.5 rounded-lg text-sm transition">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    <span>Vista completa</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal-shell" id="modal-create" aria-hidden="true" data-persistent-modal="1">
    <div class="modal-backdrop"></div>
    <div class="modal-panel modal-xl">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <span id="modal-create-badge" class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold uppercase tracking-wider">Nueva Alta</span>
                <h3 id="modal-create-title" class="mt-3 text-xl font-bold text-slate-900">Nueva Empresa Cliente</h3>
                <p id="modal-create-description" class="mt-1 text-sm text-slate-500">La correcta recopilacion de estos campos deja el registro listo para aprobacion y posterior automatizacion.</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition" data-close-modal="modal-create" aria-label="Cerrar">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form id="modal-create-form" method="post" action="index.php?action=store" enctype="multipart/form-data" class="space-y-6" data-busy-text="Espere un momento, por favor. Estamos guardando el registro.">
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-indigo-800 flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5"></i>
                <div>
                    <h4 id="modal-create-info-title" class="font-bold text-sm">Informacion del Onboarding</h4>
                    <p id="modal-create-info-text" class="text-xs text-indigo-700 mt-0.5">La correcta recopilacion de estos campos deja el registro listo para aprobacion y posterior automatizacion.</p>
                </div>
            </div>
            <div id="modal-create-errors" class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-rose-800 shadow-sm">
                <div class="flex items-start gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-500 shrink-0 mt-0.5"></i>
                    <div>
                        <h4 class="font-bold text-sm">Faltan datos obligatorios</h4>
                        <p class="mt-1 text-xs text-rose-700">Revise y complete los siguientes campos antes de guardar.</p>
                        <ul id="modal-create-errors-list" class="mt-3 list-disc pl-5 space-y-1 text-sm"></ul>
                    </div>
                </div>
            </div>
            <?php $values = $modalValues; require __DIR__ . '/_form_sections.php'; ?>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" data-close-modal="modal-create" class="bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold px-5 py-2.5 rounded-lg transition text-sm">Cancelar Registro</button>
                <button id="modal-create-submit" type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2.5 rounded-lg transition text-sm shadow-md flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span id="modal-create-submit-label">Guardar e Iniciar Automatizacion</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



