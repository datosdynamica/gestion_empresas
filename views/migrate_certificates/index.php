<?php
declare(strict_types=1);

function cert_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function cert_format_date(?string $value): string
{
    $value = trim((string) $value);
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

function cert_days_badge(?string $value): array
{
    $days = (int) trim((string) $value);
    if ($days <= 7) {
        return ['bg-rose-500 text-white', $days];
    }
    if ($days <= 15) {
        return ['bg-amber-500 text-white', $days];
    }
    if ($days <= 30) {
        return ['bg-emerald-500 text-white', $days];
    }

    return ['bg-slate-500 text-white', $days];
}

function cert_detail_value($value): string
{
    $text = trim((string) $value);
    return $text !== '' ? $text : 'Sin dato';
}

function cert_action_label(string $value): string
{
    $normalized = strtoupper(trim($value));
    if ($normalized === 'AVISO') {
        return 'Aviso';
    }
    if ($normalized === 'CERTIFICADO_RECORDATORIO') {
        return 'Recordatorio por correo';
    }
    if ($normalized === 'AVISO_CORREO') {
        return 'Aviso por correo';
    }
    if ($normalized === 'CERTIFICADO_SUBIDO') {
        return 'Certificado subido';
    }
    if ($normalized === 'CERTIFICADO_MIGRATE_OK') {
        return 'Certificado enviado a Migrate';
    }
    if ($normalized === 'CERTIFICADO_MIGRATE_ERROR') {
        return 'Error al enviar certificado a Migrate';
    }
    if ($normalized === 'CERTIFICADO_CONFIRMADO') {
        return 'Confirmacion enviada';
    }

    return $normalized !== '' ? $normalized : 'Accion';
}

$enableCreateModal = false;
$activeNav = 'certificados-migrate';
$pageSubtitle = 'Consulta manual del WS de certificados digitales de Migrate.';
require __DIR__ . '/../layout/header.php';

$filters = $filters ?? [];
$result = $result ?? null;
$items = is_array($result['items'] ?? null) ? $result['items'] : [];
$errors = is_array($result['errors'] ?? null) ? $result['errors'] : [];
$filterMode = (string) ($filters['filter_mode'] ?? 'empresas_activas');
$technicalEntries = is_array($result['technical_entries'] ?? null) ? $result['technical_entries'] : [];
$uploadCompanies = is_array($uploadCompanies ?? null) ? $uploadCompanies : [];
?>

<section class="space-y-5">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-indigo-700">Consulta tecnica</span>
                <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">Certificados Migrate</h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-500">Use esta pantalla para consultar certificados proximos a vencer o validar un RUT puntual contra el servicio oficial de Migrate. En empresas activas, esta vista lee solo el cache local; el refresco corre por tarea programada.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-100" data-open-modal="modal-cert-upload" onclick="prepareCertificateUpload()">
                    <i data-lucide="file-up" class="h-4 w-4"></i>
                    <span>Subir certificado por RUT</span>
                </button>
                <a href="<?= cert_h(app_url('configuracion')) ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    <i data-lucide="sliders-horizontal" class="h-4 w-4"></i>
                    <span>Ir a configuracion</span>
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
            <h3 class="text-base font-bold text-slate-900">Filtros de consulta</h3>
            <p class="mt-1 text-sm text-slate-500">El entorno Migrate se define en Configuracion. Aqui solo elige el tipo de consulta y sus filtros.</p>
        </div>
        <form method="post" action="<?= cert_h(app_url('certificados-migrate')) ?>" class="space-y-6 p-6" data-busy-text="Espere un momento, por favor. Estamos cargando el cache local de certificados.">
            <div class="grid gap-5 lg:grid-cols-4">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600" for="filter_mode">Tipo de filtro</label>
                    <select id="filter_mode" name="filter_mode" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="empresas_activas" <?= $filterMode === 'empresas_activas' ? 'selected' : '' ?>>Empresas activas del sistema</option>
                        <option value="rut" <?= $filterMode === 'rut' ? 'selected' : '' ?>>Por RUT puntual</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600" for="cer_status">Estado certificado</label>
                    <select id="cer_status" name="cer_status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="A" <?= ($filters['cer_status'] ?? 'A') === 'A' ? 'selected' : '' ?>>Activos</option>
                        <option value="I" <?= ($filters['cer_status'] ?? '') === 'I' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>
                <div data-cert-filter-block="empresas_activas">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600" for="cer_intervalo">Intervalo dias</label>
                    <input id="cer_intervalo" name="cer_intervalo" type="number" min="0" max="180" value="<?= cert_h((string) ($filters['cer_intervalo'] ?? '30')) ?>" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <div data-cert-filter-block="rut">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600" for="emp_ruc">RUT puntual</label>
                    <input id="emp_ruc" name="emp_ruc" type="text" inputmode="numeric" value="<?= cert_h((string) ($filters['emp_ruc'] ?? '')) ?>" placeholder="Opcional" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Comportamiento esperado</p>
                    <p class="mt-2 text-sm text-slate-700" data-cert-filter-help="empresas_activas">Consulta la lista de empresas activas registradas en la base de datos y muestra las que tienen certificados dentro del intervalo indicado.</p>
                    <p class="mt-2 text-sm text-slate-700 hidden" data-cert-filter-help="rut">Consulta un RUT puntual y trae los certificados asociados a esa empresa.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Fuente de datos</p>
                    <p class="mt-2 text-sm text-slate-700">La lista base sale de la tabla <code>Empresas</code> con registros habilitados y con RUT diligenciado.</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-indigo-700">
                    <i data-lucide="search" class="h-4 w-4"></i>
                    <span>Consultar certificados</span>
                </button>
            </div>
        </form>
    </div>

    <?php if ($result !== null): ?>
        <div class="space-y-5">
            <div class="space-y-5">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-base font-bold text-slate-900">Resumen del retorno</h3>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="<?= cert_h(app_url('index.php?action=export-certificates-excel')) ?>" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                                    <i data-lucide="file-spreadsheet" class="h-4 w-4"></i>
                                    <span>Exportar Excel</span>
                                </a>
                                <a href="<?= cert_h(app_url('index.php?action=export-certificates-pdf')) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 shadow-sm transition hover:bg-rose-100">
                                    <i data-lucide="file-output" class="h-4 w-4"></i>
                                    <span>Exportar PDF</span>
                                </a>
                                <?php if ($technicalEntries !== []): ?>
                                    <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50" data-open-modal="modal-cert-technical" data-cert-tech-index="0">
                                        <i data-lucide="file-search" class="h-4 w-4"></i>
                                        <span>Ver detalle tecnico</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Registros visibles</p>
                            <p class="mt-2 text-sm font-bold text-slate-900"><?= (int) count($items) ?></p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Estado de la consulta</p>
                            <p class="mt-2 text-sm font-bold <?= !empty($result['success']) ? 'text-emerald-700' : 'text-rose-700' ?>">
                                <?= !empty($result['success']) ? 'Consulta completada' : 'Consulta con novedades' ?>
                            </p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Origen</p>
                            <p class="mt-2 text-sm font-semibold text-slate-800">Cache de certificados actualizada</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 px-6 py-4 grid gap-3 md:grid-cols-2">
                        <p class="text-sm text-slate-600">Empresas base revisadas: <span class="font-semibold text-slate-900"><?= (int) ($result['candidate_count'] ?? 0) ?></span></p>
                        <p class="text-sm text-slate-600">Certificados recibidos del WS antes del filtro visual: <span class="font-semibold text-slate-900"><?= (int) ($result['items_total_source'] ?? count($items)) ?></span></p>
                        <p class="text-sm text-slate-600">Consultas procesadas en esta corrida: <span class="font-semibold text-slate-900"><?= (int) ($result['processed_count'] ?? 0) ?></span></p>
                        <p class="text-sm text-slate-600">Consultas con novedad: <span class="font-semibold text-slate-900"><?= (int) ($result['error_count'] ?? 0) ?></span></p>
                        <p class="text-sm text-slate-600">Empresas servidas desde cache: <span class="font-semibold text-slate-900"><?= (int) ($result['cache_hits'] ?? 0) ?></span></p>
                        <p class="text-sm text-slate-600">Empresas refrescadas desde Migrate: <span class="font-semibold text-slate-900"><?= (int) ($result['cache_refresh_count'] ?? 0) ?></span></p>
                        <p class="text-sm text-slate-600 md:col-span-2">Empresas pendientes de refresco por tarea programada: <span class="font-semibold text-slate-900"><?= (int) ($result['cache_pending_count'] ?? 0) ?></span></p>
                    </div>

                    <?php if (!empty($result['success']) && $items === []): ?>
                        <div class="border-t border-amber-100 bg-amber-50 px-6 py-4">
                            <p class="text-sm font-semibold text-amber-800">No hay certificados por vencer con los filtros usados.</p>
                            <p class="mt-1 text-sm text-amber-700">
                                La consulta termino correctamente, pero Migrate no devolvio certificados dentro del intervalo solicitado para las empresas revisadas.
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($errors !== []): ?>
                        <div class="border-t border-rose-100 bg-rose-50 px-6 py-4">
                            <p class="text-sm font-semibold text-rose-800">Novedades detectadas</p>
                            <ul class="mt-2 space-y-1 text-sm text-rose-700">
                                <?php foreach ($errors as $error): ?>
                                    <li>&bull; <?= cert_h((string) $error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <h3 class="text-base font-bold text-slate-900">Certificados encontrados</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-white">
                                <tr class="text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                    <th class="px-6 py-4 w-12 text-center">Info</th>
                                    <th class="px-6 py-4">#</th>
                                    <th class="px-6 py-4">Razon social</th>
                                    <th class="px-6 py-4">RUT</th>
                                    <th class="px-6 py-4 w-[13rem]">Email</th>
                                    <th class="px-6 py-4 w-[13rem]">Email Envio FE</th>
                                    <th class="px-6 py-4">Telefono</th>
                                    <th class="px-6 py-4">Estado</th>
                                    <th class="px-6 py-4">Dias restantes</th>
                                    <th class="px-6 py-4">Vence</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if ($items === []): ?>
                                    <tr>
                                        <td colspan="10" class="px-6 py-8 text-center text-sm text-slate-500">La consulta no devolvio certificados para los filtros usados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $index => $item): ?>
                                        <?php [$daysBadgeClass, $daysBadgeValue] = cert_days_badge((string) ($item['dias_restantes'] ?? '')); ?>
                                        <?php $detailRowId = 'cert-detail-' . $index; ?>
                                        <tr class="hover:bg-slate-50/80 cursor-pointer" onclick="toggleCertificateDetail('<?= cert_h($detailRowId) ?>')">
                                            <td class="px-6 py-4 text-center">
                                                <button type="button" class="text-slate-400 hover:text-indigo-600 transition" id="arrow-<?= cert_h($detailRowId) ?>" aria-label="Ver detalle">
                                                    <i data-lucide="chevron-right" class="h-4 w-4 transform transition-transform duration-200"></i>
                                                </button>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-semibold text-slate-500"><?= (int) $index + 1 ?></td>
                                            <td class="px-6 py-4 font-semibold text-slate-900"><?= cert_h((string) ($item['razon_social'] ?? '')) ?></td>
                                            <td class="px-6 py-4 font-semibold text-slate-900"><?= cert_h((string) ($item['emp_ruc'] ?? '')) ?></td>
                                            <td class="px-6 py-4 text-slate-700 max-w-[13rem] break-words"><?= cert_h((string) ($item['email'] ?? '')) ?></td>
                                            <td class="px-6 py-4 text-slate-700 max-w-[13rem] break-words"><?= cert_h((string) ($item['email_envio_fe'] ?? '')) ?></td>
                                            <td class="px-6 py-4 text-slate-700"><?= cert_h((string) ($item['telefono'] ?? '')) ?></td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?= (($item['cer_status'] ?? '') === 'A') ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' ?>">
                                                    <?= (($item['cer_status'] ?? '') === 'A') ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full px-2.5 py-1 text-xs font-bold <?= cert_h($daysBadgeClass) ?>">
                                                    <?= cert_h((string) $daysBadgeValue) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-slate-700"><?= cert_h(cert_format_date((string) ($item['cer_fch_vencimiento'] ?? ''))) ?></td>
                                        </tr>
                                        <tr id="<?= cert_h($detailRowId) ?>" class="hidden bg-slate-50/60">
                                            <td colspan="10" class="px-6 py-5 border-t border-slate-100">
                                                <div class="grid gap-4 xl:grid-cols-[1.1fr,0.9fr]">
                                                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                                        <div class="mb-4">
                                                            <h4 class="text-sm font-bold text-slate-900">Informacion operativa</h4>
                                                            <p class="mt-1 text-xs text-slate-500">Campos utiles para revisar y copiar durante la gestion del certificado.</p>
                                                        </div>
                                                        <div class="space-y-4">
                                                            <?php if ((int) ($item['empresa_id'] ?? 0) > 0): ?>
                                                                <?php $tipoEmpresaActual = strtoupper(trim((string) ($item['alta_tipoempresa'] ?? ''))); ?>
                                                                <?php $tipoTributarioActual = strtoupper(trim((string) ($item['alta_tributario'] ?? ''))); ?>
                                                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                                                    <div class="flex items-start justify-between gap-3">
                                                                        <div>
                                                                            <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500 font-semibold">Sincronizacion con Empresas</p>
                                                                            <p class="mt-1 text-xs text-slate-500">Estos dos campos quedan guardados directamente en la tabla Empresas para los casos historicos.</p>
                                                                        </div>
                                                                        <button
                                                                            type="button"
                                                                            class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800"
                                                                            onclick="event.stopPropagation(); saveCertificateOperational(<?= (int) $item['empresa_id'] ?>, 'alta-tipoempresa-select-<?= (int) $index ?>', 'alta-tributario-select-<?= (int) $index ?>', 'cert-operational-feedback-<?= (int) $index ?>')">
                                                                            <i data-lucide="save" class="h-4 w-4"></i>
                                                                            <span>Guardar datos</span>
                                                                        </button>
                                                                    </div>
                                                                    <div id="cert-operational-feedback-<?= (int) $index ?>" class="hidden mt-3 rounded-xl border px-3 py-2 text-xs font-semibold"></div>
                                                                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                                                                        <div>
                                                                            <label class="mb-2 block text-[11px] uppercase tracking-[0.18em] text-slate-500 font-semibold">Tipo de empresa</label>
                                                                            <select id="alta-tipoempresa-select-<?= (int) $index ?>" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                                                                <option value="UNIPERSONAL" <?= $tipoEmpresaActual === 'UNIPERSONAL' ? 'selected' : '' ?>>Unipersonal</option>
                                                                                <option value="SOCIEDAD" <?= $tipoEmpresaActual === 'SOCIEDAD' ? 'selected' : '' ?>>Sociedad</option>
                                                                            </select>
                                                                        </div>
                                                                        <div>
                                                                            <label class="mb-2 block text-[11px] uppercase tracking-[0.18em] text-slate-500 font-semibold">Tipo tributario</label>
                                                                            <select id="alta-tributario-select-<?= (int) $index ?>" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                                                                <option value="GENERAL" <?= $tipoTributarioActual === 'GENERAL' ? 'selected' : '' ?>>General</option>
                                                                                <option value="IVA MINIMO" <?= $tipoTributarioActual === 'IVA MINIMO' ? 'selected' : '' ?>>IVA minimo</option>
                                                                                <option value="MONOTRIBUTO" <?= $tipoTributarioActual === 'MONOTRIBUTO' ? 'selected' : '' ?>>Monotributo</option>
                                                                                <option value="MONOTRIBUTO MIDES" <?= $tipoTributarioActual === 'MONOTRIBUTO MIDES' ? 'selected' : '' ?>>Monotributo Mides</option>
                                                                                <option value="EXONERADO" <?= $tipoTributarioActual === 'EXONERADO' ? 'selected' : '' ?>>Exonerado</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-2">
                                                                <?php
                                                                $detailFields = [
                                                                    'Nombre completo firmante' => ['id' => 'firmante-nombre', 'value' => cert_detail_value($item['nombre_completo_firmante'] ?? '')],
                                                                    'CI firmante' => ['id' => 'firmante-ci', 'value' => cert_detail_value($item['ci_firmante'] ?? '')],
                                                                ];
                                                                ?>
                                                                <?php foreach ($detailFields as $label => $detailField): ?>
                                                                    <?php $fieldId = $detailField['id'] . '-' . $index; ?>
                                                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                                                        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500 font-semibold"><?= cert_h($label) ?></p>
                                                                        <div class="mt-2 flex items-start justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5">
                                                                            <code id="<?= cert_h($fieldId) ?>" class="block whitespace-pre-wrap break-words text-xs font-semibold text-slate-800"><?= cert_h($detailField['value']) ?></code>
                                                                            <button type="button" onclick="event.stopPropagation(); copyCertificateField('<?= cert_h($fieldId) ?>')" class="shrink-0 text-slate-400 hover:text-indigo-600 transition" title="Copiar">
                                                                                <i data-lucide="copy" class="h-4 w-4"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                                        <div class="flex items-start justify-between gap-3">
                                                            <div>
                                                                <h4 class="text-sm font-bold text-slate-900">Acciones</h4>
                                                                <p class="mt-1 text-xs text-slate-500">Registro rapido de comunicaciones y eventos del cliente.</p>
                                                            </div>
                                                            <?php if ((int) ($item['empresa_id'] ?? 0) > 0): ?>
                                                                <div class="flex flex-wrap items-center gap-2">
                                                                    <button
                                                                        type="button"
                                                                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-indigo-700"
                                                                        onclick="event.stopPropagation(); registerCertificateAction(<?= (int) $item['empresa_id'] ?>, 'aviso', '<?= cert_h('cert-action-list-' . $index) ?>')">
                                                                        <i data-lucide="bell" class="h-4 w-4"></i>
                                                                        <span>Aviso</span>
                                                                    </button>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="mt-4">
                                                            <div id="<?= cert_h('cert-action-feedback-' . $index) ?>" class="hidden mb-3 rounded-xl border px-3 py-2 text-xs font-semibold"></div>
                                                            <div id="<?= cert_h('cert-action-list-' . $index) ?>" data-cert-action-empresa-id="<?= (int) ($item['empresa_id'] ?? 0) ?>" class="space-y-2">
                                                                <?php $actionHistory = is_array($item['action_history'] ?? null) ? $item['action_history'] : []; ?>
                                                                <?php if ($actionHistory === []): ?>
                                                                    <div data-empty-state="1" class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-xs text-slate-500">
                                                                        Sin acciones registradas todavia.
                                                                    </div>
                                                                <?php else: ?>
                                                                    <?php foreach ($actionHistory as $historyEntry): ?>
                                                                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                                                            <div class="flex items-start justify-between gap-3">
                                                                                <div>
                                                                                    <p class="text-xs font-bold text-slate-900"><?= cert_h(cert_action_label((string) ($historyEntry['Accion'] ?? ''))) ?></p>
                                                                                    <p class="mt-1 text-xs text-slate-600"><?= cert_h((string) ($historyEntry['Descripcion'] ?? '')) ?></p>
                                                                                </div>
                                                                                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700"><?= cert_h(cert_format_date((string) ($historyEntry['FechaAccion'] ?? ''))) ?></span>
                                                                            </div>
                                                                            <p class="mt-2 text-[11px] text-slate-500">
                                                                                <?= cert_h((string) (($historyEntry['UsuarioNombre'] ?? '') !== '' ? $historyEntry['UsuarioNombre'] : ($historyEntry['UsuarioLogin'] ?? ''))) ?>
                                                                            </p>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<div class="modal-shell" id="modal-cert-upload" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-cert-upload"></div>
    <div class="modal-panel modal-lg">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold uppercase tracking-wider">Certificado digital</span>
                <h3 class="mt-3 text-xl font-bold text-slate-900">Cargar certificado por RUT</h3>
                <p class="mt-1 text-sm text-slate-500">Seleccione la empresa, cargue el certificado y la contrasena. El sistema ubicara el RUT, guardara el archivo en la carpeta final, intentara instalarlo en Migrate y dejara trazabilidad del resultado.</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition" data-close-modal="modal-cert-upload" aria-label="Cerrar">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="grid gap-4 md:grid-cols-1 mb-5">
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Empresa resuelta</p>
                <p class="mt-1 text-sm font-semibold text-slate-900" id="cert-upload-empresa">-</p>
            </div>
        </div>

        <form id="certificate-upload-form" action="<?= cert_h(app_url('index.php?action=certificate-upload-digital')) ?>" method="post" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="empresa_id" id="cert-upload-empresa-id" value="">
            <input type="hidden" name="history_list_id" id="cert-upload-history-list-id" value="">
            <input type="hidden" name="history_feedback_id" id="cert-upload-history-feedback-id" value="">

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500" for="certificate_rut">Empresa / RUT</label>
                <select id="certificate_rut" name="rut" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Buscar empresa o RUT...</option>
                    <?php foreach ($uploadCompanies as $uploadCompany): ?>
                        <option
                            value="<?= cert_h((string) $uploadCompany['rut']) ?>"
                            data-empresa-id="<?= (int) $uploadCompany['empresa_id'] ?>"
                            data-empresa-rut="<?= cert_h((string) $uploadCompany['rut']) ?>"
                            data-empresa-razon="<?= cert_h((string) $uploadCompany['razon_social']) ?>"
                        >
                            <?= cert_h((string) $uploadCompany['rut'] . ' - ' . $uploadCompany['razon_social']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500" for="certificate_file">Archivo del certificado</label>
                <input id="certificate_file" name="certificate_file" type="file" accept=".pfx,.p12,.zip" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <p class="mt-2 text-xs text-slate-500">Formatos permitidos: PFX, P12 o ZIP. Tamano maximo: 10 MB.</p>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500" for="certificate_password">Contrasena del certificado</label>
                <input id="certificate_password" name="certificate_password" type="text" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
            </div>

            <div id="cert-upload-form-feedback" class="hidden rounded-xl border px-3 py-2 text-xs font-semibold"></div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4">
                <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-close-modal="modal-cert-upload">Cancelar</button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-indigo-700">
                    <i data-lucide="upload" class="h-4 w-4"></i>
                    <span>Cargar certificado</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($technicalEntries !== []): ?>
<script>
window.CERT_TECH_ENTRIES = <?= json_encode($technicalEntries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<div class="modal-shell" id="modal-cert-technical" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-cert-technical"></div>
    <div class="modal-panel modal-xl">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold uppercase tracking-wider">Detalle tecnico</span>
                <h3 class="mt-3 text-xl font-bold text-slate-900">Consulta tecnica de certificados</h3>
                <p class="mt-1 text-sm text-slate-500">Puede revisar request, response y retorno puntual de cada empresa consultada.</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition" data-close-modal="modal-cert-technical" aria-label="Cerrar">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="mb-5">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600" for="cert-tech-select">Empresa consultada</label>
            <select id="cert-tech-select" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <?php foreach ($technicalEntries as $index => $entry): ?>
                    <option value="<?= (int) $index ?>"><?= cert_h(trim((string) (($entry['razon_social'] ?? '') !== '' ? $entry['razon_social'] : ($entry['rut'] ?? ('Consulta ' . $index))))) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">RUT</p>
                <p class="text-sm font-semibold text-slate-800 mt-1" id="cert-tech-rut">-</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">MsgCod</p>
                <p class="text-sm font-semibold text-slate-800 mt-1" id="cert-tech-msg-code">-</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">MsgDsc</p>
                <p class="text-sm font-semibold text-slate-800 mt-1" id="cert-tech-msg-desc">-</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Items devueltos</p>
                <p class="text-sm font-semibold text-slate-800 mt-1" id="cert-tech-items-count">0</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <div class="space-y-2">
                <h4 class="text-sm font-bold text-slate-900">Request XML</h4>
                <textarea readonly id="cert-tech-request" class="w-full min-h-[280px] rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 font-mono leading-5"></textarea>
            </div>
            <div class="space-y-2">
                <h4 class="text-sm font-bold text-slate-900">Response XML</h4>
                <textarea readonly id="cert-tech-response" class="w-full min-h-[280px] rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 font-mono leading-5"></textarea>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modeField = document.getElementById('filter_mode');
    var intervalField = document.getElementById('cer_intervalo');
    var rutField = document.getElementById('emp_ruc');
    var techEntries = Array.isArray(window.CERT_TECH_ENTRIES) ? window.CERT_TECH_ENTRIES : [];
    var techSelect = document.getElementById('cert-tech-select');
    var techRut = document.getElementById('cert-tech-rut');
    var techMsgCode = document.getElementById('cert-tech-msg-code');
    var techMsgDesc = document.getElementById('cert-tech-msg-desc');
    var techItemsCount = document.getElementById('cert-tech-items-count');
    var techRequest = document.getElementById('cert-tech-request');
    var techResponse = document.getElementById('cert-tech-response');

    function syncCertificateFilterMode() {
        if (!modeField) {
            return;
        }

        var mode = modeField.value || 'intervalo';
        if (mode !== 'rut' && mode !== 'empresas_activas') {
            mode = 'empresas_activas';
        }

        document.querySelectorAll('[data-cert-filter-block]').forEach(function (block) {
            var blockMode = block.getAttribute('data-cert-filter-block');
            block.classList.toggle('hidden', blockMode !== mode);
        });

        document.querySelectorAll('[data-cert-filter-help]').forEach(function (help) {
            var helpMode = help.getAttribute('data-cert-filter-help');
            help.classList.toggle('hidden', helpMode !== mode);
        });

        if (intervalField) {
            intervalField.required = mode === 'empresas_activas';
        }

        if (rutField) {
            rutField.required = mode === 'rut';
        }
    }

    function renderTechnicalEntry(index) {
        if (!techEntries.length) {
            return;
        }

        var safeIndex = Number(index);
        if (Number.isNaN(safeIndex) || safeIndex < 0 || safeIndex >= techEntries.length) {
            safeIndex = 0;
        }

        var entry = techEntries[safeIndex] || {};

        if (techSelect) {
            techSelect.value = String(safeIndex);
        }
        if (techRut) {
            techRut.textContent = entry.rut || '-';
        }
        if (techMsgCode) {
            techMsgCode.textContent = entry.msg_code || '-';
        }
        if (techMsgDesc) {
            techMsgDesc.textContent = entry.msg_desc || entry.error_summary || '-';
        }
        if (techItemsCount) {
            techItemsCount.textContent = String(entry.items_count || 0);
        }
        if (techRequest) {
            techRequest.value = entry.request_xml || '';
        }
        if (techResponse) {
            techResponse.value = entry.response_xml || '';
        }
    }

    if (modeField) {
        modeField.addEventListener('change', syncCertificateFilterMode);
        syncCertificateFilterMode();
    }

    if (techSelect) {
        techSelect.addEventListener('change', function () {
            renderTechnicalEntry(techSelect.value);
        });
        renderTechnicalEntry(0);
    }

    document.querySelectorAll('[data-cert-tech-index]').forEach(function (button) {
        button.addEventListener('click', function () {
            renderTechnicalEntry(button.getAttribute('data-cert-tech-index'));
        });
    });
});

function toggleCertificateDetail(rowId) {
    var detailRow = document.getElementById(rowId);
    var arrow = document.getElementById('arrow-' + rowId);
    if (!detailRow) {
        return;
    }

    detailRow.classList.toggle('hidden');
    if (arrow) {
        var icon = arrow.querySelector('svg');
        if (icon) {
            icon.classList.toggle('rotate-90', !detailRow.classList.contains('hidden'));
        }
    }
}

function copyCertificateField(fieldId) {
    var node = document.getElementById(fieldId);
    if (!node) {
        return;
    }

    var value = (node.textContent || '').trim();
    if (value === '') {
        return;
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(value);
        return;
    }

    var range = document.createRange();
    range.selectNodeContents(node);
    var selection = window.getSelection();
    if (selection) {
        selection.removeAllRanges();
        selection.addRange(range);
        document.execCommand('copy');
        selection.removeAllRanges();
    }
}

function registerCertificateAction(empresaId, actionKey, listId) {
    var feedbackId = listId.replace('cert-action-list-', 'cert-action-feedback-');
    var listNode = document.getElementById(listId);
    var feedbackNode = document.getElementById(feedbackId);
    if (!listNode) {
        return;
    }

    if (feedbackNode) {
        feedbackNode.className = 'hidden mb-3 rounded-xl border px-3 py-2 text-xs font-semibold';
        feedbackNode.textContent = '';
    }

    var payload = new FormData();
    payload.append('empresa_id', String(empresaId || 0));
    payload.append('action_key', String(actionKey || ''));

    fetch('<?= cert_h(app_url('index.php?action=certificate-log-action')) ?>', {
        method: 'POST',
        body: payload,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function (response) {
        return response.json().catch(function () {
            return { success: false, message: 'Respuesta invalida del servidor.' };
        });
    })
    .then(function (data) {
        if (!data || !data.success) {
            throw new Error((data && data.message) ? data.message : 'No fue posible registrar la accion.');
        }

        var entry = data.entry || {};
        var actor = (entry.usuario_nombre || '').trim() !== '' ? entry.usuario_nombre : (entry.usuario_login || '');
        var card = document.createElement('div');
        card.className = 'rounded-xl border border-slate-200 bg-slate-50 px-4 py-3';
        card.innerHTML =
            '<div class=\"flex items-start justify-between gap-3\">' +
                '<div>' +
                    '<p class=\"text-xs font-bold text-slate-900\">Aviso</p>' +
                    '<p class=\"mt-1 text-xs text-slate-600\">' + escapeHtml(entry.descripcion || 'Aviso al cliente registrado.') + '</p>' +
                '</div>' +
                '<span class=\"rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700\">' + escapeHtml(formatUiDate(entry.fecha_accion || '')) + '</span>' +
            '</div>' +
            '<p class=\"mt-2 text-[11px] text-slate-500\">' + escapeHtml(actor) + '</p>';

        var emptyState = listNode.querySelector('[data-empty-state=\"1\"]');
        if (emptyState) {
            emptyState.remove();
        }

        if (listNode.textContent.trim() === 'Sin acciones registradas todavia.') {
            listNode.innerHTML = '';
        }

        listNode.insertBefore(card, listNode.firstChild);

        if (feedbackNode) {
            feedbackNode.className = 'mb-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700';
            feedbackNode.textContent = data.message || 'Aviso registrado correctamente.';
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    })
    .catch(function (error) {
        if (feedbackNode) {
            feedbackNode.className = 'mb-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700';
            feedbackNode.textContent = error.message || 'No fue posible registrar la accion.';
        }
    });
}

function prepareCertificateUpload(button) {
    var empresaIdField = document.getElementById('cert-upload-empresa-id');
    var rutField = document.getElementById('certificate_rut');
    var empresaLabel = document.getElementById('cert-upload-empresa');
    var listIdField = document.getElementById('cert-upload-history-list-id');
    var feedbackIdField = document.getElementById('cert-upload-history-feedback-id');
    var uploadForm = document.getElementById('certificate-upload-form');
    var uploadFeedback = document.getElementById('cert-upload-form-feedback');

    if (uploadForm) {
        uploadForm.reset();
    }
    if (empresaIdField) {
        empresaIdField.value = button ? (button.getAttribute('data-cert-upload-empresa-id') || '') : '';
    }
    if (rutField) {
        rutField.value = button ? (button.getAttribute('data-cert-upload-rut') || '') : '';
    }
    if (empresaLabel) {
        empresaLabel.textContent = button ? (button.getAttribute('data-cert-upload-empresa') || '-') : '-';
    }
    if (listIdField) {
        listIdField.value = button ? (button.getAttribute('data-cert-upload-list-id') || '') : '';
    }
    if (feedbackIdField) {
        feedbackIdField.value = button ? (button.getAttribute('data-cert-upload-feedback-id') || '') : '';
    }
    if (uploadFeedback) {
        uploadFeedback.className = 'hidden rounded-xl border px-3 py-2 text-xs font-semibold';
        uploadFeedback.textContent = '';
    }
    syncCertificateUploadSelection();
}

function syncCertificateUploadSelection() {
    var empresaIdField = document.getElementById('cert-upload-empresa-id');
    var rutField = document.getElementById('certificate_rut');
    var empresaLabel = document.getElementById('cert-upload-empresa');
    if (!rutField) {
        return;
    }

    var selectedOption = rutField.options[rutField.selectedIndex] || null;
    var empresaId = selectedOption ? (selectedOption.getAttribute('data-empresa-id') || '') : '';
    var razon = selectedOption ? (selectedOption.getAttribute('data-empresa-razon') || '-') : '-';
    var rut = selectedOption ? (selectedOption.getAttribute('data-empresa-rut') || '') : '';

    if (empresaIdField) {
        empresaIdField.value = empresaId;
    }
    if (empresaLabel) {
        empresaLabel.textContent = rut !== '' ? (razon + ' (' + rut + ')') : '-';
    }
}

function saveCertificateOperational(empresaId, tipoEmpresaSelectId, tributarioSelectId, feedbackId) {
    var tipoEmpresaField = document.getElementById(tipoEmpresaSelectId);
    var tributarioField = document.getElementById(tributarioSelectId);
    var feedbackNode = document.getElementById(feedbackId);

    if (!tipoEmpresaField || !tributarioField || !feedbackNode) {
        return;
    }

    feedbackNode.className = 'mt-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600';
    feedbackNode.textContent = 'Guardando datos operativos...';
    feedbackNode.classList.remove('hidden');

    var payload = new FormData();
    payload.append('empresa_id', String(empresaId || 0));
    payload.append('alta_tipoempresa', String(tipoEmpresaField.value || ''));
    payload.append('alta_tributario', String(tributarioField.value || ''));

    fetch('<?= cert_h(app_url('index.php?action=certificate-update-operational')) ?>', {
        method: 'POST',
        body: payload,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function (response) {
        return response.json().catch(function () {
            return { success: false, message: 'Respuesta invalida del servidor.' };
        });
    })
    .then(function (data) {
        if (!data || !data.success) {
            throw new Error((data && data.message) ? data.message : 'No fue posible guardar los datos operativos.');
        }

        feedbackNode.className = 'mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700';
        feedbackNode.textContent = data.message || 'Datos operativos guardados correctamente.';
    })
    .catch(function (error) {
        feedbackNode.className = 'mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700';
        feedbackNode.textContent = error.message || 'No fue posible guardar los datos operativos.';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var uploadForm = document.getElementById('certificate-upload-form');
    if (!uploadForm) {
        return;
    }

    var rutField = document.getElementById('certificate_rut');
    if (rutField && window.TomSelect) {
        var companySelect = new TomSelect(rutField, {
            create: false,
            allowEmptyOption: true,
            maxOptions: 500,
            searchField: ['text', 'value'],
            placeholder: 'Buscar empresa o RUT...',
            onInitialize: function () {
                syncCertificateUploadSelection();
            },
            onChange: function () {
                syncCertificateUploadSelection();
            }
        });

        window.certificateRutSelect = companySelect;
    } else {
        syncCertificateUploadSelection();
        if (rutField) {
            rutField.addEventListener('change', syncCertificateUploadSelection);
        }
    }

    uploadForm.addEventListener('submit', function (event) {
        event.preventDefault();

        var uploadFeedback = document.getElementById('cert-upload-form-feedback');
        var listId = document.getElementById('cert-upload-history-list-id');
        var feedbackId = document.getElementById('cert-upload-history-feedback-id');
        var listNode = listId ? document.getElementById(listId.value) : null;
        var rowFeedbackNode = feedbackId ? document.getElementById(feedbackId.value) : null;
        var fileField = document.getElementById('certificate_file');
        var passwordField = document.getElementById('certificate_password');
        var rutField = document.getElementById('certificate_rut');
        var empresaLabel = document.getElementById('cert-upload-empresa');

        if (!rutField || String(rutField.value || '').replace(/\D+/g, '') === '') {
            if (uploadFeedback) {
                uploadFeedback.className = 'rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700';
                uploadFeedback.textContent = 'Debe seleccionar una empresa o un RUT del listado.';
                uploadFeedback.classList.remove('hidden');
            }
            return;
        }

        if (!fileField || !fileField.files || !fileField.files.length) {
            if (uploadFeedback) {
                uploadFeedback.className = 'rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700';
                uploadFeedback.textContent = 'Debe seleccionar un archivo de certificado digital.';
                uploadFeedback.classList.remove('hidden');
            }
            return;
        }

        if (!passwordField || String(passwordField.value || '').trim() === '') {
            if (uploadFeedback) {
                uploadFeedback.className = 'rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700';
                uploadFeedback.textContent = 'Debe digitar la contrasena del certificado digital.';
                uploadFeedback.classList.remove('hidden');
            }
            return;
        }

        if (!window.confirm('Confirme la carga del certificado digital. El archivo se guardara en la carpeta final del RUT y quedara registrado en el historial.')) {
            return;
        }

        var submitButton = uploadForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }

        if (uploadFeedback) {
            uploadFeedback.className = 'rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600';
            uploadFeedback.textContent = 'Cargando certificado digital...';
            uploadFeedback.classList.remove('hidden');
        }

        fetch(uploadForm.action, {
            method: 'POST',
            body: new FormData(uploadForm),
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            return response.json().catch(function () {
                return { success: false, message: 'Respuesta invalida del servidor.' };
            });
        })
        .then(function (data) {
            if (!data || !data.success) {
                throw new Error((data && data.message) ? data.message : 'No fue posible cargar el certificado digital.');
            }

            var empresa = data.empresa || {};
            var entry = data.entry || {};
            var actor = (entry.usuario_nombre || '').trim() !== '' ? entry.usuario_nombre : (entry.usuario_login || '');
            var targetLists = [];
            if (listNode) {
                targetLists.push(listNode);
            } else if (entry.empresa_id) {
                targetLists = Array.prototype.slice.call(document.querySelectorAll('[data-cert-action-empresa-id="' + String(entry.empresa_id) + '"]'));
            }

            targetLists.forEach(function (targetList) {
                var card = document.createElement('div');
                card.className = 'rounded-xl border border-slate-200 bg-slate-50 px-4 py-3';
                card.innerHTML =
                    '<div class=\"flex items-start justify-between gap-3\">' +
                        '<div>' +
                            '<p class=\"text-xs font-bold text-slate-900\">' + escapeHtml(certificateActionLabel(entry.accion || '')) + '</p>' +
                            '<p class=\"mt-1 text-xs text-slate-600\">' + escapeHtml(entry.descripcion || '') + '</p>' +
                        '</div>' +
                        '<span class=\"rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700\">' + escapeHtml(formatUiDate(entry.fecha_accion || '')) + '</span>' +
                    '</div>' +
                    '<p class=\"mt-2 text-[11px] text-slate-500\">' + escapeHtml(actor) + '</p>';

                var emptyState = targetList.querySelector('[data-empty-state=\"1\"]');
                if (emptyState) {
                    emptyState.remove();
                }
                targetList.insertBefore(card, targetList.firstChild);
            });

            if (empresaLabel) {
                var resolvedRut = empresa.rut || String(rutField.value || '').replace(/\D+/g, '');
                var resolvedName = empresa.razon_social || '-';
                empresaLabel.textContent = resolvedRut !== '' ? (resolvedName + ' (' + resolvedRut + ')') : resolvedName;
            }

            if (rowFeedbackNode) {
                rowFeedbackNode.className = 'mb-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700';
                rowFeedbackNode.textContent = data.message || 'Certificado digital cargado correctamente.';
                rowFeedbackNode.classList.remove('hidden');
            }

            if (uploadFeedback) {
                uploadFeedback.className = 'rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700';
                uploadFeedback.textContent = data.message || 'Certificado digital cargado correctamente.';
            }

            uploadForm.reset();
            if (window.certificateRutSelect && typeof window.certificateRutSelect.clear === 'function') {
                window.certificateRutSelect.clear(true);
            }
            syncCertificateUploadSelection();

            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        })
        .catch(function (error) {
            if (uploadFeedback) {
                uploadFeedback.className = 'rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700';
                uploadFeedback.textContent = error.message || 'No fue posible cargar el certificado digital.';
                uploadFeedback.classList.remove('hidden');
            }
        })
        .finally(function () {
            if (submitButton) {
                submitButton.disabled = false;
            }
        });
    });
});

function formatUiDate(value) {
    if (!value) {
        return '';
    }

    return value.replace(/-/g, '/').slice(0, 16);
}

function certificateActionLabel(value) {
    var normalized = String(value || '').trim().toUpperCase();
    if (normalized === 'AVISO') {
        return 'Aviso';
    }
    if (normalized === 'CERTIFICADO_RECORDATORIO') {
        return 'Recordatorio por correo';
    }
    if (normalized === 'AVISO_CORREO') {
        return 'Aviso por correo';
    }
    if (normalized === 'CERTIFICADO_SUBIDO') {
        return 'Certificado subido';
    }
    if (normalized === 'CERTIFICADO_MIGRATE_OK') {
        return 'Certificado enviado a Migrate';
    }
    if (normalized === 'CERTIFICADO_MIGRATE_ERROR') {
        return 'Error al enviar certificado a Migrate';
    }
    if (normalized === 'CERTIFICADO_CONFIRMADO') {
        return 'Confirmacion enviada';
    }
    return normalized !== '' ? normalized : 'Accion';
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
