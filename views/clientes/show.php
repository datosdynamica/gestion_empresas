<?php
declare(strict_types=1);

function client_show_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function client_show_logo_mime(string $binary): string
{
    $prefix = bin2hex(substr($binary, 0, 8));
    if (strpos($prefix, '89504e47') === 0) {
        return 'image/png';
    }
    if (strpos($prefix, 'ffd8ff') === 0) {
        return 'image/jpeg';
    }
    if (strpos($prefix, '47494638') === 0) {
        return 'image/gif';
    }
    if (strpos($prefix, '424d') === 0) {
        return 'image/bmp';
    }

    return 'application/octet-stream';
}

function client_show_logo_src(array $item, ?array $logoContext): ?string
{
    $raw = $item['ImagenLogo'] ?? '';
    if (!is_string($raw) || $raw === '') {
        $relativePath = trim((string) ($logoContext['relative_path'] ?? ''));
        if ($relativePath === '') {
            return null;
        }

        $absolutePath = FileStorage::absoluteFromRelative($relativePath);
        if (!is_file($absolutePath)) {
            return null;
        }

        $raw = file_get_contents($absolutePath) ?: '';
    }

    if (substr($raw, 0, 4) === '*nm*') {
        $raw = base64_decode(substr($raw, 4), true) ?: '';
    }

    if ($raw === '') {
        return null;
    }

    return 'data:' . client_show_logo_mime($raw) . ';base64,' . base64_encode($raw);
}

function client_show_logo_text(array $item): string
{
    $base = trim((string) (($item['NombreFantasia'] ?? '') !== '' ? $item['NombreFantasia'] : ($item['RazonSocial'] ?? '')));
    if ($base === '') {
        return 'D';
    }

    $parts = preg_split('/\s+/', $base) ?: [];
    $letters = '';
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }
        $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        if (mb_strlen($letters) >= 2) {
            break;
        }
    }

    return $letters !== '' ? $letters : mb_strtoupper(mb_substr($base, 0, 2));
}

function client_show_value($value, string $fallback = 'Sin dato'): string
{
    $text = trim((string) $value);
    return $text !== '' ? $text : $fallback;
}

function client_show_datetime(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return 'Sin fecha';
    }

    try {
        return (new DateTimeImmutable($value))->format('d/m/Y H:i');
    } catch (Throwable $e) {
        return $value;
    }
}

function client_show_date(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return 'Sin fecha';
    }

    try {
        return (new DateTimeImmutable($value))->format('d/m/Y');
    } catch (Throwable $e) {
        return $value;
    }
}

function client_show_date_input(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format('Y-m-d');
    } catch (Throwable $e) {
        return '';
    }
}

function client_show_license_label($value): string
{
    $map = [
        '0' => 'DYNAMICA ERP',
        '2' => 'LITE',
        '3' => 'FACTURADOR',
        '4' => 'DYNAMICA ERP VETERINARIAS',
        '5' => 'LITE VETERINARIAS',
        '7' => 'LITE OPTICAS',
        '8' => 'FE INVOICY',
        '10' => 'TPV',
        '11' => 'CLINICA VET',
        '12' => 'CUMPLIMIENTO',
        '14' => 'INVO',
    ];

    $key = (string) $value;
    return $map[$key] ?? ($key !== '' ? ('Licencia ' . $key) : 'Sin licencia');
}

function client_show_license_options(): array
{
    return [
        ['value' => '0', 'label' => '0 - DYNAMICA ERP'],
        ['value' => '2', 'label' => '2 - LITE'],
        ['value' => '3', 'label' => '3 - FACTURADOR'],
        ['value' => '4', 'label' => '4 - DYNAMICA ERP VETERINARIAS'],
        ['value' => '5', 'label' => '5 - LITE VETERINARIAS'],
        ['value' => '7', 'label' => '7 - LITE OPTICAS'],
        ['value' => '8', 'label' => '8 - FE INVOICY'],
        ['value' => '10', 'label' => '10 - TPV'],
        ['value' => '11', 'label' => '11 - CLINICA VET'],
        ['value' => '12', 'label' => '12 - CUMPLIMIENTO'],
        ['value' => '14', 'label' => '14 - INVO'],
    ];
}

function client_show_hab_badge(string $value): array
{
    $raw = strtoupper(trim($value));
    if ($raw === '') {
        return ['Sin dato', 'bg-slate-100 text-slate-600'];
    }

    if (strpos($raw, 'SUSPEND') === 0) {
        return ['Suspendida', 'bg-amber-100 text-amber-700'];
    }

    if (strpos($raw, 'NO') === 0 && strpos($raw, 'CERTIFIC') !== false) {
        return ['No en certificacion', 'bg-amber-100 text-amber-700'];
    }

    if (strpos($raw, 'NO') === 0) {
        return ['Baja logica', 'bg-rose-100 text-rose-700'];
    }

    if (in_array($raw, ['SI', 'S', '1'], true)) {
        return ['Si', 'bg-emerald-100 text-emerald-700'];
    }

    return [$value, 'bg-slate-100 text-slate-600'];
}

function client_show_module_state($disabledFlag): array
{
    $disabled = trim((string) $disabledFlag);
    $enabled = in_array($disabled, ['0', '', 'NO', 'FALSE'], true);

    return $enabled
        ? ['Si', 'bg-emerald-100 text-emerald-700 border-emerald-200']
        : ['No', 'bg-slate-100 text-slate-600 border-slate-200'];
}

function client_show_module_enabled($rawValue, bool $inverted): bool
{
    $value = strtoupper(trim((string) $rawValue));
    if ($inverted) {
        return !in_array($value, ['1', 'SI', 'S'], true);
    }

    return in_array($value, ['1', 'SI', 'S'], true);
}

function client_show_selected(?string $left, string $right): string
{
    return trim((string) $left) === $right ? 'selected' : '';
}

function client_show_habilitada_checked(?string $current, string $option): bool
{
    $value = strtoupper(trim((string) $current));
    if ($option === 'SI') {
        return in_array($value, ['SI', 'S', '1'], true);
    }

    if ($option === 'NO (En Proc. de Certificacion)') {
        return strpos($value, 'CERTIFIC') !== false;
    }

    if ($option === 'SUSPENDIDA (Por no pago)') {
        return strpos($value, 'SUSPEND') === 0;
    }

    if ($option === 'NO') {
        return $value === 'NO' || strpos($value, 'BAJA') !== false || (strpos($value, 'NO') === 0 && strpos($value, 'CERTIFIC') === false);
    }

    if ($option === 'DEMO') {
        return $value === 'DEMO';
    }

    return $value === strtoupper($option);
}

$enableCreateModal = false;
$activeNav = 'clientes';
$embeddedView = isset($_GET['embed']) && $_GET['embed'] === '1';
$savedFromEmbeddedFlow = $embeddedView && isset($_GET['updated']) && $_GET['updated'] === '1';
$pageSubtitle = 'Consulta y actualizacion de datos principales del cliente.';
$item = $context['item'];
$onboarding = $context['onboarding'] ?? null;
$oldInput = $_SESSION['client_panel_old_input'] ?? [];
if (is_array($oldInput)) {
    unset($_SESSION['client_panel_old_input']);
} else {
    $oldInput = [];
}
$clientPanelConflicts = $_SESSION['client_panel_conflicts'] ?? [];
if (is_array($clientPanelConflicts)) {
    unset($_SESSION['client_panel_conflicts']);
} else {
    $clientPanelConflicts = [];
}
$snapshot = $context['snapshot'] ?? null;
$snapshotRows = $context['snapshot_rows'] ?? [];
$actions = $context['actions'] ?? [];
$logoContext = $context['logo'] ?? null;
$certificateFiles = $context['certificate_files'] ?? [];
$adminUsers = $context['admin_users'] ?? [];
$logoSrc = client_show_logo_src($item, is_array($logoContext) ? $logoContext : null);
$habBadge = client_show_hab_badge((string) ($item['Habilitada'] ?? ''));
$passwordValue = trim((string) ($onboarding['certificado_contrasena'] ?? ''));
$certificateMode = trim((string) ($onboarding['alta_certificado_digital'] ?? ''));
$latestSnapshot = is_array($snapshot) ? $snapshot : [];
$latestCertificateAlias = trim((string) ($latestSnapshot['Apodo'] ?? ''));
$latestCertificateExpiry = trim((string) ($latestSnapshot['CerFchVencimiento'] ?? ''));
$latestCertificateDays = isset($latestSnapshot['DiasRestantes']) && $latestSnapshot['DiasRestantes'] !== null
    ? (string) (int) $latestSnapshot['DiasRestantes']
    : '';
$moduleRows = [
    ['label' => 'Ventas', 'field' => 'module_ventas', 'raw' => $item['pNoVentas'] ?? '1', 'inverted' => true],
    ['label' => 'Compras', 'field' => 'module_compras', 'raw' => $item['pNoCompras'] ?? '1', 'inverted' => true],
    ['label' => 'Stock', 'field' => 'module_stock', 'raw' => $item['pNoStock'] ?? '1', 'inverted' => true],
    ['label' => 'Caja y bancos', 'field' => 'module_caja_bancos', 'raw' => $item['pNoCajayBancos'] ?? '1', 'inverted' => true],
    ['label' => 'CRM', 'field' => 'module_crm', 'raw' => $item['pNoCrm'] ?? '1', 'inverted' => true],
    ['label' => 'Produccion', 'field' => 'module_produccion', 'raw' => $item['pNoProduccion'] ?? '1', 'inverted' => true],
    ['label' => 'Veterinarias', 'field' => 'module_veterinarias', 'raw' => $item['pNoVeterinarias'] ?? '1', 'inverted' => true],
    ['label' => 'TPV', 'field' => 'module_tpv', 'raw' => $item['pTpvSoft'] ?? '0', 'inverted' => false],
    ['label' => 'Importaciones', 'field' => 'module_importaciones', 'raw' => $item['pImportaciones'] ?? '0', 'inverted' => false],
    ['label' => 'Gestion pedidos clientes', 'field' => 'module_pedidos_clientes', 'raw' => $item['pGestionPedidosClientes'] ?? '0', 'inverted' => false],
    ['label' => 'Facturacion masiva Excel', 'field' => 'module_fact_masiva_excel', 'raw' => $item['pFactMasivaExcel'] ?? '0', 'inverted' => false],
    ['label' => 'Agencia', 'field' => 'module_agencia', 'raw' => $item['pAgencia'] ?? '0', 'inverted' => false],
];
$featureToggleRows = [
    ['label' => 'Abonados', 'field' => 'module_abonados', 'raw' => $item['pNoAbonados'] ?? '0', 'inverted' => true],
    ['label' => 'Quitar resguardos', 'field' => 'module_quitar_resguardos', 'raw' => $item['pNoResguardo'] ?? '0', 'inverted' => true],
    ['label' => 'Funcionalidad ASU', 'field' => 'module_asu', 'raw' => $item['pAsu'] ?? '0', 'inverted' => false],
    ['label' => 'Supervisor en TPV por cambio de precios', 'field' => 'module_supervisor_tpv', 'raw' => $item['pSupervisorTPV'] ?? '0', 'inverted' => false],
    ['label' => 'Acceso adicional a funciones de Shopping', 'field' => 'module_shopping', 'raw' => $item['pLec_Shopping'] ?? '0', 'inverted' => false],
    ['label' => 'Acceso adicional al Facturador', 'field' => 'module_facturador', 'raw' => $item['pFacturador'] ?? '0', 'inverted' => false],
    ['label' => 'Notificaciones', 'field' => 'module_notificaciones', 'raw' => $item['pNotificaciones'] ?? '0', 'inverted' => false],
    ['label' => 'Medios de pago', 'field' => 'module_medios_pago', 'raw' => $item['pMediosDePago'] ?? '0', 'inverted' => false],
];
$notesValue = trim((string) ($item['Notas'] ?? ''));
$dayOptions = ['0', '10', '20', '30', '40', '50', '60', '70', '90', '100', '110', '120'];
$contabilidadOptions = [
    ['value' => '0', 'label' => 'Sin integracion'],
    ['value' => '1', 'label' => 'Integrado'],
    ['value' => '2', 'label' => 'Sistema contable'],
];
$balanzaOptions = [
    ['value' => '0', 'label' => 'Precio'],
    ['value' => '1', 'label' => 'Peso'],
];
$simpleBinaryOptions = [
    ['value' => '0', 'label' => 'No'],
    ['value' => '1', 'label' => 'Si'],
];
$habilitadaOptions = [
    ['value' => 'SI', 'label' => 'Si'],
    ['value' => 'NO (En Proc. de Certificacion)', 'label' => 'No (En proc. de certificacion)'],
    ['value' => 'SUSPENDIDA (Por no pago)', 'label' => 'Suspendida (Por no pago)'],
    ['value' => 'NO', 'label' => 'Baja logica'],
    ['value' => 'DEMO', 'label' => 'Demo'],
];
$licenseOptions = client_show_license_options();

$formValue = static function (string $field, $fallback = '') use ($oldInput) {
    if (array_key_exists($field, $oldInput)) {
        return (string) $oldInput[$field];
    }

    return (string) $fallback;
};

require __DIR__ . '/../layout/header.php';
?>

<section class="space-y-5">
    <div class="rounded-[28px] border border-[#f0d9d3] bg-white/95 px-4 py-3 shadow-[0_22px_60px_-34px_rgba(230,91,79,0.45)]">
        <div class="flex flex-col gap-2">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex min-w-0 gap-3">
                    <div class="shrink-0">
                        <?php if ($logoSrc !== null): ?>
                            <button type="button" id="client-logo-open" class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-[18px] border border-slate-200 bg-white shadow-sm transition hover:border-[#e65b4f]">
                                <img src="<?= client_show_h($logoSrc) ?>" alt="Logo <?= client_show_h((string) ($item['RazonSocial'] ?? '')) ?>" class="max-h-12 max-w-12 object-contain">
                            </button>
                        <?php else: ?>
                            <div class="flex h-16 w-16 items-center justify-center rounded-[18px] border border-dashed border-slate-300 bg-gradient-to-br from-slate-50 to-white text-xl font-black text-slate-500 shadow-sm">
                                <?= client_show_h(client_show_logo_text($item)) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-[#fff0ec] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-[#cc4f44]">Ficha cliente</span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= client_show_h($habBadge[1]) ?>"><?= client_show_h($habBadge[0]) ?></span>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">ID empresa <?= client_show_h((string) ($item['IdEmpresa'] ?? '')) ?></span>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">RUT <?= client_show_h((string) ($item['Rut'] ?? '')) ?></span>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700"><?= client_show_h(client_show_license_label($item['LicenciaCodigo'] ?? '')) ?></span>
                            <?php if ($latestCertificateDays !== ''): ?>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700"><?= client_show_h($latestCertificateDays) ?> dias cert.</span>
                            <?php endif; ?>
                        </div>
                        <h2 class="mt-1.5 text-[1.35rem] font-extrabold leading-tight tracking-tight text-slate-900"><?= client_show_h((string) ($item['RazonSocial'] ?? 'Cliente')) ?></h2>
                        <p class="mt-0.5 text-xs text-slate-500"><?= client_show_h(client_show_value((string) ($item['NombreFantasia'] ?? ''), 'Sin nombre fantasia')) ?></p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700">
                    <strong class="uppercase tracking-[0.16em] text-slate-500">EmpCodigo</strong>
                    <span class="font-bold text-slate-900"><?= client_show_h(client_show_value((string) ($item['EmpresaInvoicy'] ?? ''))) ?></span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700">
                    <strong class="uppercase tracking-[0.16em] text-slate-500">Caso</strong>
                    <span class="font-bold text-slate-900"><?= client_show_h(is_array($onboarding) ? ('#' . (int) ($onboarding['id'] ?? 0)) : 'Sin onboarding') ?></span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700">
                    <strong class="uppercase tracking-[0.16em] text-slate-500">Cert.</strong>
                    <span class="font-bold text-slate-900"><?= client_show_h(client_show_value($latestCertificateAlias, 'Sin snapshot')) ?></span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700">
                    <strong class="uppercase tracking-[0.16em] text-slate-500">Vence</strong>
                    <span class="font-bold text-slate-900"><?= client_show_h($latestCertificateExpiry !== '' ? client_show_date($latestCertificateExpiry) : 'Sin fecha') ?></span>
                </span>
            </div>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" action="<?= client_show_h(app_url('index.php?action=client-update&id=' . (int) ($item['IdEmpresa'] ?? 0) . ($embeddedView ? '&embed=1' : ''))) ?>" class="space-y-5">
        <?php if ($clientPanelConflicts !== []): ?>
            <div class="rounded-[28px] border border-rose-200 bg-gradient-to-br from-rose-50 via-white to-amber-50 p-5 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-rose-600">Conflictos detectados</p>
                        <h3 class="mt-1 text-lg font-extrabold text-slate-900">Revise las diferencias antes de guardar</h3>
                        <p class="mt-1 text-sm text-slate-600">El panel detectó valores distintos entre fuentes. No se guardó este intento para evitar sobrescribir información más reciente sin revisión.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700"><?= client_show_h((string) count($clientPanelConflicts)) ?> campo(s)</span>
                </div>

                <div class="mt-4 grid gap-3">
                    <?php foreach ($clientPanelConflicts as $conflict): ?>
                        <?php $conflictSources = is_array($conflict['sources'] ?? null) ? $conflict['sources'] : []; ?>
                        <article class="rounded-[22px] border border-rose-100 bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h4 class="text-sm font-extrabold text-slate-900"><?= client_show_h((string) ($conflict['label'] ?? 'Campo')) ?></h4>
                                    <p class="mt-1 text-xs text-slate-500">Valor que intentó guardar el panel</p>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"><?= client_show_h((string) ($conflict['panel_display'] ?? 'Sin dato')) ?></span>
                            </div>

                            <div class="mt-3 grid gap-2 md:grid-cols-3">
                                <?php foreach ($conflictSources as $sourceName => $sourceValue): ?>
                                    <?php
                                    $sourceLabel = [
                                        'empresas' => 'Empresas',
                                        'clientes' => 'Clientes',
                                        'onboarding' => 'EmpresasNuevas',
                                        'migrate' => 'Migrate',
                                    ][$sourceName] ?? (string) $sourceName;
                                    ?>
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500"><?= client_show_h($sourceLabel) ?></p>
                                        <p class="mt-1 text-sm font-semibold text-slate-800"><?= client_show_h((string) $sourceValue) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 bg-gradient-to-r from-[#fff2ef] to-white px-4 py-3">
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-client-tab="general" class="client-form-tab is-active inline-flex items-center rounded-2xl bg-[#e65b4f] px-4 py-2 text-sm font-semibold text-white">Datos empresa</button>
                    <button type="button" data-client-tab="factura" class="client-form-tab inline-flex items-center rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Datos factura electronica</button>
                    <button type="button" data-client-tab="notes" class="client-form-tab inline-flex items-center rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Notas cliente</button>
                    <button type="button" data-client-tab="modules" class="client-form-tab inline-flex items-center rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Modulos y funciones</button>
                </div>
            </div>
        </div>

        <div class="grid gap-5 2xl:grid-cols-[1.45fr_0.95fr]">
            <div class="space-y-5">
                <div id="client-tab-general" class="client-form-panel space-y-5">
                    <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-200 bg-gradient-to-r from-[#fff2ef] to-white px-5 py-3">
                            <h3 class="text-lg font-extrabold text-slate-900">Datos de la empresa</h3>
                        </div>
                        <div class="space-y-4 p-5">
                            <div class="rounded-[22px] border border-slate-200 bg-slate-50 p-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Logo actual</p>
                                    <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">
                                        <div class="sm:w-[200px]">
                                            <?php if ($logoSrc !== null): ?>
                                                <button type="button" id="client-logo-open-inline" class="flex h-16 w-full items-center justify-center overflow-hidden rounded-[18px] border border-slate-200 bg-white shadow-sm transition hover:border-[#e65b4f]">
                                                    <img src="<?= client_show_h($logoSrc) ?>" alt="Logo actual" class="max-h-12 max-w-[150px] object-contain">
                                                </button>
                                            <?php else: ?>
                                                <div class="flex h-16 w-full items-center justify-center rounded-[18px] border border-dashed border-slate-300 bg-white text-lg font-black text-slate-500">
                                                    <?= client_show_h(client_show_logo_text($item)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="min-w-0 flex-1 space-y-2">
                                            <label for="archivo_logo" class="inline-flex cursor-pointer items-center gap-2 rounded-2xl bg-[#e65b4f] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#d95044]">
                                                <i data-lucide="upload" class="h-4 w-4"></i>
                                                <span>Seleccionar logo</span>
                                            </label>
                                            <input id="archivo_logo" name="archivo_logo" type="file" accept=".png,.jpg,.jpeg,.gif,.bmp,.webp" class="hidden">
                                            <p id="archivo_logo_nombre" class="text-xs text-slate-500">PNG, JPG, GIF, BMP o WEBP. Maximo 10 MB.</p>
                                        </div>
                                    </div>
                            </div>

                            <div class="rounded-[24px] border border-slate-200 bg-white p-4">
                                <p class="mb-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Datos empresa</p>
                                <div class="grid gap-3 md:grid-cols-12">
                                    <div class="md:col-span-5">
                                        <label for="nombre_fantasia" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nombre fantasia</label>
                                        <input id="nombre_fantasia" name="nombre_fantasia" value="<?= client_show_h($formValue('nombre_fantasia', (string) ($item['NombreFantasia'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-7">
                                        <label for="razon_social" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Razon social</label>
                                        <input id="razon_social" name="razon_social" value="<?= client_show_h($formValue('razon_social', (string) ($item['RazonSocial'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="rut" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">RUT</label>
                                        <input id="rut" name="rut" value="<?= client_show_h($formValue('rut', (string) ($item['Rut'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-8">
                                        <label for="domicilio" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Domicilio</label>
                                        <input id="domicilio" name="domicilio" value="<?= client_show_h($formValue('domicilio', (string) ($item['Domicilio'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="departamento_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Departamento</label>
                                        <input id="departamento_id" name="departamento_id" list="departamento_options" value="<?= client_show_h($formValue('departamento_id', (string) ($item['Departamento'] ?? ''))) ?>" placeholder="Buscar departamento" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                        <datalist id="departamento_options">
                                            <?php foreach (($formOptions['departamentos'] ?? []) as $option): ?>
                                                <option value="<?= client_show_h((string) ($option['nombre'] ?? '')) ?>"></option>
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="ciudad_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Ciudad</label>
                                        <input id="ciudad_id" name="ciudad_id" list="ciudad_options" value="<?= client_show_h($formValue('ciudad_id', (string) (($item['Ciudad'] ?? '') !== '' ? $item['Ciudad'] : ($item['ClienteIdCiudad'] ?? '')))) ?>" placeholder="Buscar ciudad" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                        <datalist id="ciudad_options">
                                            <?php foreach (($formOptions['ciudades'] ?? []) as $option): ?>
                                                <option value="<?= client_show_h((string) ($option['nombre'] ?? '')) ?>"></option>
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="email_principal" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Email</label>
                                        <input id="email_principal" name="email_principal" value="<?= client_show_h($formValue('email_principal', (string) (($item['ClienteEmail'] ?? '') !== '' ? $item['ClienteEmail'] : ($item['EmpresaEmail'] ?? '')))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="sitio_web" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Sitio web</label>
                                        <input id="sitio_web" name="sitio_web" value="<?= client_show_h((string) ($item['SitioWeb'] ?? '')) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="hidden md:col-span-8 md:block"></div>
                                </div>
                            </div>

                            <div class="grid gap-4 xl:grid-cols-12">
                                <div class="rounded-[24px] border border-slate-200 bg-white p-4 xl:col-span-5">
                                    <p class="mb-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Estado comercial</p>
                                    <div class="space-y-3">
                                        <div class="max-w-[260px]">
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Empresa literal E</label>
                                            <label class="inline-flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 cursor-pointer">
                                                <span class="text-sm font-medium text-slate-700">Activar literal E</span>
                                                <span class="relative">
                                                    <input type="hidden" name="literal_e" value="0">
                                                    <input type="checkbox" name="literal_e" value="1" class="peer sr-only" <?= ((int) $formValue('literal_e', (string) ((int) ($item['LiteralE'] ?? 0))) === 1) ? 'checked' : '' ?>>
                                                    <span class="block h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-emerald-500"></span>
                                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                                                </span>
                                            </label>
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Habilitada</label>
                                            <div class="rounded-[22px] border border-slate-200 bg-slate-50 p-3">
                                                <div class="grid gap-2 sm:grid-cols-2">
                                                <?php foreach ($habilitadaOptions as $option): ?>
                                                    <label class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 cursor-pointer">
                                                        <input type="radio" name="habilitada" value="<?= client_show_h($option['value']) ?>" <?= client_show_habilitada_checked($formValue('habilitada', (string) ($item['Habilitada'] ?? '')), (string) $option['value']) ? 'checked' : '' ?>>
                                                        <span><?= client_show_h($option['label']) ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[24px] border border-slate-200 bg-white p-4 xl:col-span-3">
                                    <p class="mb-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Licencia y plan</p>
                                    <div class="grid gap-3 md:grid-cols-3">
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Usuarios</label>
                                            <input value="<?= client_show_h((string) ($item['UsuariosLicencia'] ?? '0')) ?>" readonly class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700">
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Plan</label>
                                            <input value="<?= client_show_h(client_show_value((string) ($item['Plan'] ?? ''))) ?>" readonly class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700">
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Licencia</label>
                                            <select name="licencia_codigo" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                                <?php foreach ($licenseOptions as $option): ?>
                                                    <option value="<?= client_show_h($option['value']) ?>" <?= $formValue('licencia_codigo', (string) ($item['LicenciaCodigo'] ?? '')) === (string) $option['value'] ? 'selected' : '' ?>><?= client_show_h($option['label']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[24px] border border-slate-200 bg-white p-4 xl:col-span-4">
                                    <p class="mb-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Alertas y corte</p>
                                    <div class="grid gap-3 md:grid-cols-4">
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Notificar por deuda</label>
                                            <select name="notificar_deuda" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                                <?php foreach ($dayOptions as $option): ?>
                                                    <option value="<?= client_show_h($option) ?>" <?= $formValue('notificar_deuda', (string) ($item['Notificar'] ?? '')) === $option ? 'selected' : '' ?>><?= client_show_h($option) ?> dias</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Notificar por suspension</label>
                                            <select name="notificar_suspension" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                                <?php foreach ($dayOptions as $option): ?>
                                                    <option value="<?= client_show_h($option) ?>" <?= $formValue('notificar_suspension', (string) ($item['NotificarSuspension'] ?? '')) === $option ? 'selected' : '' ?>><?= client_show_h($option) ?> dias</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Suspension</label>
                                            <select name="suspension_dias" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                                <?php foreach ($dayOptions as $option): ?>
                                                    <option value="<?= client_show_h($option) ?>" <?= $formValue('suspension_dias', (string) ($item['Suspension'] ?? '')) === $option ? 'selected' : '' ?>><?= client_show_h($option) ?> dias</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="fecha_ip" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Fecha IP</label>
                                            <input id="fecha_ip" name="fecha_ip" type="date" value="<?= client_show_h($formValue('fecha_ip', client_show_date_input((string) ($item['FechaIP'] ?? '')))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                            <p class="mt-1 text-[11px] text-slate-500"><?= client_show_h(client_show_date((string) ($item['FechaIP'] ?? ''))) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="client-tab-factura" class="client-form-panel hidden space-y-5">
                    <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-200 bg-gradient-to-r from-[#fff2ef] to-white px-6 py-4">
                            <h3 class="text-lg font-extrabold text-slate-900">Datos factura electronica</h3>
                        </div>
                        <div class="grid gap-4 p-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">N° empresa Invoicy</label>
                                <input value="<?= client_show_h(client_show_value((string) ($item['EmpresaInvoicy'] ?? ''))) ?>" readonly class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                            </div>
                            <div>
                                <label for="email_envio_fe" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Usuario email InvoiCy</label>
                                <input id="email_envio_fe" name="email_envio_fe" value="<?= client_show_h($formValue('email_envio_fe', (string) ($item['emailEnvioFE'] ?? ($item['cUsuarioEmailInv'] ?? '')))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Pass Invoicy</label>
                                <input value="<?= client_show_h(client_show_value((string) ($item['cPassInv'] ?? ''), 'Sin dato')) ?>" readonly class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Clave de integracion</label>
                                <input value="<?= client_show_h(client_show_value((string) ($item['Clave'] ?? ''))) ?>" readonly class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Usuario EF</label>
                                <input id="usuario_ef" name="usuario_ef" value="<?= client_show_h($formValue('usuario_ef', (string) ($item['UsuarioEF'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Clave usuario EF</label>
                                <input id="clave_usuario_ef" name="clave_usuario_ef" value="<?= client_show_h($formValue('clave_usuario_ef', (string) ($item['ClaveUsuarioEF'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                            </div>
                            <div>
                                <label for="id_usuario_ad" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Usuario administrador Dynamica</label>
                                <select id="id_usuario_ad" name="id_usuario_ad" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    <option value="">SELECCIONE USUARIO ADMINISTRADOR</option>
                                    <?php foreach ($adminUsers as $option): ?>
                                        <option value="<?= client_show_h((string) ($option['id'] ?? '')) ?>" <?= $formValue('id_usuario_ad', (string) ($item['IdUsuarioAD'] ?? '')) === (string) ($option['id'] ?? '') ? 'selected' : '' ?>><?= client_show_h((string) ($option['nombre'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                    <?php
                                    $adminUserExists = false;
                                    foreach ($adminUsers as $option) {
                                        if ((string) ($option['id'] ?? '') === (string) ($item['IdUsuarioAD'] ?? '')) {
                                            $adminUserExists = true;
                                            break;
                                        }
                                    }
                                    ?>
                                    <?php if ($formValue('id_usuario_ad', (string) ($item['IdUsuarioAD'] ?? '')) !== '' && !$adminUserExists): ?>
                                        <option value="<?= client_show_h($formValue('id_usuario_ad', (string) ($item['IdUsuarioAD'] ?? ''))) ?>" selected><?= client_show_h($formValue('id_usuario_ad', (string) ($item['IdUsuarioAD'] ?? ''))) ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <?php $altaTipoEmpresaActual = strtoupper(trim($formValue('alta_tipoempresa', (string) ($item['AltaTipoEmpresa'] ?? 'PRODUCCION')))); ?>
                                <label for="alta_tipoempresa" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Empresa en modalidad de</label>
                                <select id="alta_tipoempresa" name="alta_tipoempresa" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    <option value="PRODUCCION" <?= $altaTipoEmpresaActual === 'PRODUCCION' ? 'selected' : '' ?>>PRODUCCION</option>
                                    <option value="PRUEBA" <?= in_array($altaTipoEmpresaActual, ['PRUEBA', 'PRUEBAS', 'TEST'], true) ? 'selected' : '' ?>>PRUEBA</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Modo certificado</label>
                                <input value="<?= client_show_h(client_show_value($certificateMode)) ?>" readonly class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="client-tab-notes" class="client-form-panel hidden space-y-5">
                    <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-200 bg-gradient-to-r from-[#fff2ef] to-white px-6 py-4">
                            <h3 class="text-lg font-extrabold text-slate-900">Notas cliente</h3>
                        </div>
                        <div class="p-6">
                            <textarea id="notas_admin" name="notas_admin" rows="12" class="w-full rounded-[24px] border border-slate-200 px-4 py-4 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20"><?= client_show_h($formValue('notas_admin', $notesValue)) ?></textarea>
                            <p class="mt-2 text-xs text-slate-500">Este bloque alimenta la nota operativa de la empresa y el caso ligado cuando existe onboarding.</p>
                        </div>
                    </div>
                </div>

                <div id="client-tab-modules" class="client-form-panel hidden space-y-5">
                    <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-200 bg-gradient-to-r from-[#fff2ef] to-white px-6 py-4">
                            <h3 class="text-lg font-extrabold text-slate-900">Modulos y funciones</h3>
                        </div>
                        <div class="grid gap-5 p-6 xl:grid-cols-[1.15fr_0.85fr]">
                            <div class="rounded-[24px] border border-slate-200 bg-slate-50 overflow-hidden">
                                <div class="border-b border-slate-200 bg-slate-100 px-4 py-3">
                                    <h4 class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-700">Modulos</h4>
                                </div>
                                <div class="divide-y divide-slate-200">
                                    <?php foreach ($moduleRows as $module): ?>
                                        <?php $enabled = client_show_module_enabled($module['raw'], (bool) $module['inverted']); ?>
                                        <div class="grid items-center gap-3 px-4 py-3 md:grid-cols-[minmax(0,1fr)_170px]">
                                            <span class="text-sm font-semibold text-slate-700"><?= client_show_h((string) $module['label']) ?></span>
                                            <div class="flex items-center gap-4 text-sm font-semibold text-slate-900">
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" name="<?= client_show_h((string) $module['field']) ?>" value="NO" <?= !$enabled ? 'checked' : '' ?>>
                                                    <span>No</span>
                                                </label>
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" name="<?= client_show_h((string) $module['field']) ?>" value="SI" <?= $enabled ? 'checked' : '' ?>>
                                                    <span>Si</span>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="space-y-5">
                                <div class="rounded-[24px] border border-slate-200 bg-slate-50 overflow-hidden">
                                    <div class="border-b border-slate-200 bg-slate-100 px-4 py-3">
                                        <h4 class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-700">Funciones</h4>
                                    </div>
                                    <div class="space-y-4 p-4">
                                        <?php foreach ($featureToggleRows as $feature): ?>
                                            <?php $enabled = client_show_module_enabled($feature['raw'], (bool) $feature['inverted']); ?>
                                            <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 cursor-pointer">
                                                <span class="text-sm font-semibold text-slate-700"><?= client_show_h((string) $feature['label']) ?></span>
                                                <span class="relative shrink-0">
                                                    <input type="hidden" name="<?= client_show_h((string) $feature['field']) ?>" value="NO">
                                                    <input type="checkbox" name="<?= client_show_h((string) $feature['field']) ?>" value="SI" class="peer sr-only" <?= $enabled ? 'checked' : '' ?>>
                                                    <span class="block h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-emerald-500"></span>
                                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>

                                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                            <p class="mb-2 text-sm font-semibold text-slate-700">Contabilidad</p>
                                            <div class="space-y-2 text-sm font-semibold text-slate-900">
                                                <?php foreach ($contabilidadOptions as $option): ?>
                                                    <label class="inline-flex items-center gap-2 cursor-pointer mr-4">
                                                        <input type="radio" name="module_contabilidad" value="<?= client_show_h($option['value']) ?>" <?= client_show_selected((string) ($item['pContabilidad'] ?? '0'), $option['value']) === 'selected' ? 'checked' : '' ?>>
                                                        <span><?= client_show_h($option['label']) ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                            <p class="mb-2 text-sm font-semibold text-slate-700">Balanza en TPV</p>
                                            <div class="space-y-2 text-sm font-semibold text-slate-900">
                                                <?php foreach ($balanzaOptions as $option): ?>
                                                    <label class="inline-flex items-center gap-2 cursor-pointer mr-4">
                                                        <input type="radio" name="module_balanza" value="<?= client_show_h($option['value']) ?>" <?= client_show_selected((string) ($item['pBalanza'] ?? '0'), $option['value']) === 'selected' ? 'checked' : '' ?>>
                                                        <span><?= client_show_h($option['label']) ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">Habilitar gestionar mas de un CAE por documento</label>
                                            <select name="module_mas_de_un_cae" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                                <?php foreach ($simpleBinaryOptions as $option): ?>
                                                    <option value="<?= client_show_h($option['value']) ?>" <?= client_show_selected((string) ($item['pMasDeUnTipoCae'] ?? '0'), $option['value']) ?>><?= client_show_h($option['label']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-[#fff2ef] to-white px-6 py-4">
                        <h3 class="text-lg font-extrabold text-slate-900">Historial reciente</h3>
                    </div>
                    <div class="space-y-3 p-6">
                        <?php if ($actions !== []): ?>
                            <?php foreach ($actions as $action): ?>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <p class="text-sm font-semibold text-slate-900"><?= client_show_h((string) ($action['Descripcion'] ?? 'Accion')) ?></p>
                                    <p class="mt-1 text-xs text-slate-500"><?= client_show_h(client_show_datetime((string) ($action['FechaAccion'] ?? ''))) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-500">No hay acciones registradas todavia para este cliente.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <?php if (!$embeddedView): ?>
                        <a href="<?= client_show_h(app_url('index.php?route=clientes')) ?>" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <i data-lucide="arrow-left" class="h-4 w-4"></i>
                            <span>Volver</span>
                        </a>
                    <?php endif; ?>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-[#e65b4f] px-5 py-3 text-sm font-bold text-white shadow-md transition hover:bg-[#d95044]">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        <span>Guardar cambios</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<?php if ($logoSrc !== null): ?>
    <div id="client-logo-modal" class="fixed inset-0 z-[90] hidden bg-slate-950/65 px-4 py-6 backdrop-blur-sm">
        <div class="mx-auto flex h-full max-w-4xl items-center justify-center">
            <div class="relative w-full rounded-[28px] border border-slate-200 bg-white p-6 shadow-2xl">
                <button type="button" id="client-logo-close" class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-300 bg-white text-slate-600 transition hover:bg-slate-50">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
                <p class="pr-12 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Logo ampliado</p>
                <div class="mt-4 flex h-[70vh] items-center justify-center rounded-[24px] bg-slate-50 p-6">
                    <img src="<?= client_show_h($logoSrc) ?>" alt="Logo ampliado" class="max-h-full max-w-full object-contain">
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var logoModal = document.getElementById('client-logo-modal');
    var logoOpen = document.getElementById('client-logo-open');
    var logoOpenInline = document.getElementById('client-logo-open-inline');
    var logoClose = document.getElementById('client-logo-close');
    var logoInput = document.getElementById('archivo_logo');
    var logoInputName = document.getElementById('archivo_logo_nombre');
    var tabs = document.querySelectorAll('[data-client-tab]');
    var panels = document.querySelectorAll('.client-form-panel');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var target = tab.getAttribute('data-client-tab');

            tabs.forEach(function (button) {
                button.classList.remove('is-active', 'bg-[#e65b4f]', 'text-white');
                button.classList.add('border', 'border-slate-300', 'bg-white', 'text-slate-700');
            });
            panels.forEach(function (panel) {
                panel.classList.add('hidden');
            });

            tab.classList.add('is-active', 'bg-[#e65b4f]', 'text-white');
            tab.classList.remove('border', 'border-slate-300', 'bg-white', 'text-slate-700');

            var panel = document.getElementById('client-tab-' + target);
            if (panel) {
                panel.classList.remove('hidden');
            }
        });
    });

    function closeLogoModal() {
        if (logoModal) {
            logoModal.classList.add('hidden');
        }
    }

    if (logoOpen && logoModal) {
        logoOpen.addEventListener('click', function () {
            logoModal.classList.remove('hidden');
        });
    }

    if (logoOpenInline && logoModal) {
        logoOpenInline.addEventListener('click', function () {
            logoModal.classList.remove('hidden');
        });
    }

    if (logoInput && logoInputName) {
        logoInput.addEventListener('change', function () {
            if (logoInput.files && logoInput.files.length > 0) {
                logoInputName.textContent = logoInput.files[0].name;
            } else {
                logoInputName.textContent = 'PNG, JPG, GIF, BMP o WEBP. Maximo 10 MB.';
            }
        });
    }

    if (logoClose) {
        logoClose.addEventListener('click', closeLogoModal);
    }

    if (window.tinymce && document.getElementById('notas_admin')) {
        if (window.tinymce.get('notas_admin')) {
            window.tinymce.get('notas_admin').remove();
        }

        window.tinymce.init({
            selector: '#notas_admin',
            menubar: 'file edit insert view format table tools',
            branding: false,
            promotion: false,
            height: 320,
            plugins: 'lists link image table code fullscreen autoresize',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image table | code fullscreen',
            content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; }'
        });
    }

    if (logoModal) {
        logoModal.addEventListener('click', function (event) {
            if (event.target === logoModal) {
                closeLogoModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeLogoModal();
        }
    });

    <?php if ($savedFromEmbeddedFlow): ?>
    if (window.parent && window.parent !== window) {
        window.parent.postMessage({
            type: 'client-panel-saved',
            empresaId: <?= (int) ($item['IdEmpresa'] ?? 0) ?>
        }, '*');
    }
    <?php endif; ?>
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
