<?php declare(strict_types=1); ?>
<?php
function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
    if (!empty($item['licencia_texto'])) {
        return (string) $item['licencia_texto'];
    }

    switch ((string) ($item['licencia'] ?? '')) {
        case '8':
            return 'Enterprise Cloud';
        case '3':
            return 'SaaS Standard';
        case '0':
            return 'SaaS Professional';
        default:
            return 'Sin definir';
    }
}

function buildListMeta(array $item): array
{
    $estado = (string) ($item['estado'] ?? '');

    if ($estado === ESTADO_ERROR_APROBACION) {
        return [
            'status_label' => 'Frenado',
            'status_class' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'status_dot' => 'bg-amber-500',
            'hito_key' => 'Migrate',
            'hito_text' => 'Migrate (Reintentar)',
            'hito_class' => 'bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 hover:text-rose-800',
            'hito_icon' => 'refresh-cw',
            'action_mode' => 'retry',
            'timeline_mode' => 'error',
            'can_cancel' => true,
        ];
    }

    if ($estado === ESTADO_APROBADO) {
        return [
            'status_label' => 'Completado',
            'status_class' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'status_dot' => 'bg-emerald-500',
            'hito_key' => 'Alta Final',
            'hito_text' => 'Alta Final (Completado)',
            'hito_class' => 'bg-slate-100 text-slate-700 border border-slate-300',
            'hito_icon' => 'check-circle-2',
            'action_mode' => 'done',
            'timeline_mode' => 'done',
            'can_cancel' => false,
        ];
    }

    if ($estado === ESTADO_ELIMINADO) {
        return [
            'status_label' => 'Cancelado',
            'status_class' => 'bg-slate-100 text-slate-700 border border-slate-300',
            'status_dot' => 'bg-slate-400',
            'hito_key' => 'Cancelado',
            'hito_text' => 'Onboarding Cancelado',
            'hito_class' => 'bg-slate-100 text-slate-400 border border-slate-200',
            'hito_icon' => 'x-circle',
            'action_mode' => 'disabled',
            'timeline_mode' => 'cancelled',
            'can_cancel' => false,
        ];
    }

    return [
        'status_label' => 'En Proceso',
        'status_class' => 'bg-blue-50 text-blue-700 border border-blue-200',
        'status_dot' => 'bg-blue-500 animate-pulse',
        'hito_key' => 'Aprobacion pendiente',
        'hito_text' => 'Aprobacion pendiente (Aprobar)',
        'hito_class' => 'bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 hover:text-indigo-800',
        'hito_icon' => 'shield',
        'action_mode' => 'approve',
        'timeline_mode' => 'pending',
        'can_cancel' => true,
    ];
}

function buildTimeline(array $item, array $meta): array
{
    $pending = [
        ['title' => '1. Aprobacion pendiente', 'type' => 'Manual', 'state' => 'current', 'desc' => 'Esperando revision manual del admin.'],
        ['title' => '2. Hito Carpeta', 'type' => 'Auto', 'state' => 'pending', 'desc' => 'Se crea al aprobar el registro.'],
        ['title' => '3. Migrate', 'type' => 'Auto', 'state' => 'pending', 'desc' => 'En espera de paso anterior.'],
        ['title' => '4. Dynamica', 'type' => 'Auto', 'state' => 'pending', 'desc' => 'En espera de paso anterior.'],
        ['title' => '5. Certificado Digital', 'type' => 'Manual', 'state' => 'pending', 'desc' => 'Instalacion manual.'],
        ['title' => '6. Homologacion DGI', 'type' => 'Manual', 'state' => 'pending', 'desc' => 'Tramitacion gubernamental.'],
        ['title' => '7. Envio de Factura', 'type' => 'Auto', 'state' => 'pending', 'desc' => 'Primera factura de servicio.'],
        ['title' => '8. Envio de Credenciales', 'type' => 'Auto', 'state' => 'pending', 'desc' => 'Envio de accesos seguros.'],
        ['title' => '9. Alta Final', 'type' => 'Auto', 'state' => 'pending', 'desc' => 'Activacion final del cliente.'],
    ];

    if ($meta['timeline_mode'] === 'error') {
        $pending[0]['state'] = 'done';
        $pending[0]['desc'] = 'Aprobado por Admin.';
        $pending[1]['state'] = 'done';
        $pending[1]['desc'] = 'Directorio de archivos creado.';
        $pending[2]['state'] = 'error';
        $pending[2]['desc'] = (string) (($item['estado_detalle'] ?? '') ?: 'Frenado por error tecnico en integracion.');
    } elseif ($meta['timeline_mode'] === 'done') {
        foreach ($pending as &$step) {
            $step['state'] = 'done';
            $step['desc'] = 'Completado.';
        }
        unset($step);
        $pending[8]['desc'] = 'Cliente activo y provisionado en produccion.';
    } elseif ($meta['timeline_mode'] === 'cancelled') {
        $pending[0]['state'] = 'error';
        $pending[0]['desc'] = (string) (($item['motivo_eliminacion'] ?? '') ?: 'Proceso cancelado por administracion.');
    }

    return $pending;
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
?>

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
                <span class="text-slate-500 font-medium text-xs uppercase tracking-wider">Estado:</span>
                <select id="filtro-estado" class="bg-transparent border-none focus:outline-none text-slate-700 font-semibold cursor-pointer">
                    <option value="todos">Todos</option>
                    <option value="En Proceso">En Proceso</option>
                    <option value="Frenado">Frenado</option>
                    <option value="Completado">Completado</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>

            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg text-sm w-full sm:w-auto">
                <span class="text-slate-500 font-medium text-xs uppercase tracking-wider">Hito Actual:</span>
                <select id="filtro-hito" class="bg-transparent border-none focus:outline-none text-slate-700 font-semibold cursor-pointer">
                    <option value="todos">Todos los hitos</option>
                    <option value="Aprobacion pendiente">1. Aprobacion pendiente</option>
                    <option value="Migrate">3. Migrate</option>
                    <option value="Alta Final">9. Alta Final</option>
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
                        $meta = buildListMeta($item);
                        $timeline = buildTimeline($item, $meta);
                        $itemId = (int) $item['id'];
                        $licencia = licenciaEtiqueta($item);
                        $displayDate = !empty($item['fecha_creacion']) ? date('d/m/Y', strtotime((string) $item['fecha_creacion'])) : '-';
                        $recordJson = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
                                <?php if ($meta['action_mode'] === 'retry'): ?>
                                    <button id="btn-hito-<?= $itemId ?>" onclick="reintentarHitoAutomatico(<?= $itemId ?>)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm <?= $meta['hito_class'] ?>" title="Hito automatico fallido. Clic para reintentar">
                                        <i data-lucide="<?= $meta['hito_icon'] ?>" class="w-3.5 h-3.5 text-rose-500"></i>
                                        <span><?= h($meta['hito_text']) ?></span>
                                    </button>
                                <?php elseif ($meta['action_mode'] === 'approve'): ?>
                                    <button id="btn-hito-<?= $itemId ?>" onclick="avanzarHitoManual(<?= $itemId ?>)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm <?= $meta['hito_class'] ?>">
                                        <i data-lucide="<?= $meta['hito_icon'] ?>" class="w-3.5 h-3.5"></i>
                                        <span><?= h($meta['hito_text']) ?></span>
                                    </button>
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
                                        <button type="button" class="detalle-tab-btn inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold bg-indigo-600 text-white shadow-sm" data-target="ruta" data-id="<?= $itemId ?>" onclick="setDetailTab(<?= $itemId ?>, 'ruta')">
                                            <i data-lucide="git-commit" class="w-3.5 h-3.5"></i>
                                            <span>Ruta</span>
                                        </button>
                                        <button type="button" class="detalle-tab-btn inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-600" data-target="fiscal" data-id="<?= $itemId ?>" onclick="setDetailTab(<?= $itemId ?>, 'fiscal')">
                                            <i data-lucide="database" class="w-3.5 h-3.5"></i>
                                            <span>Fiscal</span>
                                        </button>
                                        <button type="button" class="detalle-tab-btn inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-600" data-target="resumen" data-id="<?= $itemId ?>" onclick="setDetailTab(<?= $itemId ?>, 'resumen')">
                                            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                                            <span>Resumen</span>
                                        </button>
                                    </div>

                                    <div id="detalle-pane-<?= $itemId ?>-ruta" class="detalle-pane">
                                        <div class="space-y-4">
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                                <i data-lucide="git-commit" class="w-4 h-4 text-indigo-500"></i>
                                                Hoja de Ruta de Onboarding (9 Hitos)
                                            </h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-xl border border-slate-200">
                                                <?php foreach ($timeline as $index => $step): ?>
                                                    <?php $classes = timelineClasses($step['state']); ?>
                                                    <div class="<?= $classes['wrapper'] ?> <?= $index === 8 ? 'md:col-span-2' : '' ?>">
                                                        <span class="<?= $classes['iconWrap'] ?>">
                                                            <i data-lucide="<?= $classes['icon'] ?>" class="w-3.5 h-3.5"></i>
                                                        </span>
                                                        <div>
                                                            <h5 class="<?= $classes['title'] ?>">
                                                                <?= h($step['title']) ?>
                                                                <span class="text-[9px] px-1 py-0.2 rounded font-normal <?= $classes['type'] ?>"><?= h($step['type']) ?></span>
                                                            </h5>
                                                            <p class="<?= $classes['desc'] ?>"><?= h($step['desc']) ?></p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="detalle-pane-<?= $itemId ?>-fiscal" class="detalle-pane hidden">
                                        <div class="bg-white p-5 rounded-xl border border-slate-200 space-y-4 shadow-inner">
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                                <i data-lucide="database" class="w-4 h-4 text-slate-500"></i>
                                                Credenciales de Conexion Fiscal
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
                                                    <p class="mt-1 text-sm font-semibold text-slate-800"><?= h((string) ($item['ciudad'] ?: '-')) ?></p>
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
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-slate-500 font-medium">
            <div class="space-y-1">
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
            <div class="flex gap-1.5">
                <?php if ($isDemoFallback): ?>
                    <button class="bg-white border border-slate-200 text-slate-400 px-3 py-1.5 rounded-lg transition disabled:opacity-50" disabled>Anterior</button>
                    <button class="bg-white border border-slate-200 text-slate-400 px-3 py-1.5 rounded-lg transition disabled:opacity-50" disabled>Siguiente</button>
                <?php else: ?>
                    <a href="<?= htmlspecialchars(app_url('index.php?page=' . (int) ($pagination['prev_page'] ?? 1)), ENT_QUOTES, 'UTF-8') ?>" class="bg-white border border-slate-200 <?= !empty($pagination['has_prev']) ? 'text-slate-700 hover:bg-slate-100' : 'text-slate-400 pointer-events-none opacity-60' ?> px-3 py-1.5 rounded-lg transition">Anterior</a>
                    <a href="<?= htmlspecialchars(app_url('index.php?page=' . (int) ($pagination['next_page'] ?? $totalPages)), ENT_QUOTES, 'UTF-8') ?>" class="bg-white border border-slate-200 <?= !empty($pagination['has_next']) ? 'text-slate-700 hover:bg-slate-100' : 'text-slate-400 pointer-events-none opacity-60' ?> px-3 py-1.5 rounded-lg transition">Siguiente</a>
                <?php endif; ?>
            </div>
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

<div class="modal-shell" id="modal-create" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-create"></div>
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
        <form id="modal-create-form" method="post" action="index.php?action=store" enctype="multipart/form-data" class="space-y-6">
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-indigo-800 flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5"></i>
                <div>
                    <h4 id="modal-create-info-title" class="font-bold text-sm">Informacion del Onboarding</h4>
                    <p id="modal-create-info-text" class="text-xs text-indigo-700 mt-0.5">La correcta recopilacion de estos campos deja el registro listo para aprobacion y posterior automatizacion.</p>
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
