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

    if ($raw === 'SU' || strpos($raw, 'SUSPEND') === 0) {
        return ['Suspendida', 'bg-amber-100 text-amber-700'];
    }

    if (strpos($raw, 'NO') === 0 && strpos($raw, 'CERTIFIC') !== false) {
        return ['No en certificacion', 'bg-amber-100 text-amber-700'];
    }

    if ($raw === 'BA' || strpos($raw, 'BAJA') === 0) {
        return ['Baja logica', 'bg-rose-100 text-rose-700'];
    }

    if (in_array($raw, ['SI', 'S', '1'], true)) {
        return ['Si', 'bg-emerald-100 text-emerald-700'];
    }

    if ($raw === 'DE' || strpos($raw, 'DEMO') === 0) {
        return ['Demo', 'bg-slate-100 text-slate-600'];
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

function client_show_option_input_value(?string $rawValue, ?string $fallbackLabel): string
{
    $rawValue = trim((string) $rawValue);
    if ($rawValue !== '' && !ctype_digit($rawValue)) {
        return $rawValue;
    }

    return trim((string) $fallbackLabel);
}

function client_show_select_option_value(array $options, $rawValue, $fallbackLabel = ''): string
{
    $rawValue = trim((string) $rawValue);
    $fallbackLabel = trim((string) $fallbackLabel);

    foreach ($options as $option) {
        $value = trim((string) ($option['id'] ?? ''));
        $label = trim((string) ($option['nombre'] ?? ($option['label'] ?? '')));

        if ($value !== '' && $rawValue !== '' && strcasecmp($value, $rawValue) === 0) {
            return $value;
        }

        if ($label !== '' && $rawValue !== '' && strcasecmp($label, $rawValue) === 0) {
            return $value;
        }

        if ($label !== '' && $fallbackLabel !== '' && strcasecmp($label, $fallbackLabel) === 0) {
            return $value;
        }
    }

    return $rawValue;
}

function client_show_prefer_nonzero($preferred, $fallback = ''): string
{
    $preferred = trim((string) $preferred);
    if ($preferred !== '' && $preferred !== '0') {
        return $preferred;
    }

    return trim((string) $fallback);
}

function client_show_prefer_nonempty($preferred, $fallback = ''): string
{
    $preferred = trim((string) $preferred);
    if ($preferred !== '') {
        return $preferred;
    }

    return trim((string) $fallback);
}

function client_show_history_value(?string $fieldKey, $value): string
{
    $fieldKey = trim((string) $fieldKey);
    $text = trim((string) $value);
    if ($text === '') {
        return 'Sin dato';
    }

    if (in_array($fieldKey, ['cliente_abonado_importe', 'cliente_abonado_descuento'], true) && is_numeric(str_replace(',', '.', $text))) {
        return number_format((float) str_replace(',', '.', $text), 2, '.', '');
    }

    if (in_array($fieldKey, ['fecha_ip', 'suc_cod_fecha_vigencia'], true)) {
        return client_show_date($text);
    }

    if ($fieldKey === 'notas_admin') {
        $plain = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = preg_replace('/\s+/u', ' ', $plain) ?? $plain;
        $plain = trim($plain);
        return $plain !== '' ? $plain : 'Sin dato';
    }

    return $text;
}

function client_show_habilitada_checked(?string $current, string $option): bool
{
    $value = strtoupper(trim((string) $current));
    if ($option === 'SI') {
        return in_array($value, ['SI', 'S', '1'], true);
    }

    if ($option === 'NO') {
        return $value === 'NO' || (strpos($value, 'NO') === 0 && strpos($value, 'CERTIFIC') !== false);
    }

    if ($option === 'SU') {
        return $value === 'SU' || strpos($value, 'SUSPEND') === 0;
    }

    if ($option === 'BA') {
        return $value === 'BA' || strpos($value, 'BAJA') === 0;
    }

    if ($option === 'DE') {
        return $value === 'DE' || strpos($value, 'DEMO') === 0;
    }

    return $value === strtoupper($option);
}

$enableCreateModal = false;
$activeNav = 'clientes';
$embeddedView = isset($_GET['embed']) && $_GET['embed'] === '1';
$savedFromEmbeddedFlow = $embeddedView && isset($_GET['updated']) && $_GET['updated'] === '1';
$pageSubtitle = 'Consulta y actualizacion de datos principales del cliente.';
$embeddedSuccessMessage = trim((string) ($_SESSION['success'] ?? ''));
$embeddedErrorMessage = trim((string) ($_SESSION['error'] ?? ''));
if ($embeddedSuccessMessage !== '') {
    unset($_SESSION['success']);
}
if ($embeddedErrorMessage !== '') {
    unset($_SESSION['error']);
}
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
$clientPanelSyncStatus = $_SESSION['client_panel_sync_status'] ?? [];
if (is_array($clientPanelSyncStatus)) {
    unset($_SESSION['client_panel_sync_status']);
} else {
    $clientPanelSyncStatus = [];
}
$snapshot = $context['snapshot'] ?? null;
$snapshotRows = $context['snapshot_rows'] ?? [];
$actions = $context['actions'] ?? [];
$clientPanelHistory = $context['client_panel_history'] ?? [];
$certificateHistory = $context['certificate_history'] ?? [];
$certificateNotifications = $context['certificate_notifications'] ?? [];
$files = $context['files'] ?? [];
$logoContext = $context['logo'] ?? null;
$certificateFiles = $context['certificate_files'] ?? [];
$adminUsers = $context['admin_users'] ?? [];
$logoSrc = client_show_logo_src($item, is_array($logoContext) ? $logoContext : null);
$habBadge = client_show_hab_badge((string) ($item['Habilitada'] ?? ''));
$adminUserCurrentRaw = trim((string) (array_key_exists('id_usuario_ad', $oldInput)
    ? $oldInput['id_usuario_ad']
    : ($item['IdUsuarioAD'] ?? '')));
$passwordValue = trim((string) client_show_prefer_nonempty($onboarding['certificado_contrasena'] ?? null, (string) ($item['CertificadoContrasena'] ?? '')));
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
    ['value' => 'NO', 'label' => 'No (En proc. de certificacion)'],
    ['value' => 'SU', 'label' => 'Suspendida (Por no pago)'],
    ['value' => 'BA', 'label' => 'Baja logica'],
    ['value' => 'DE', 'label' => 'Demo'],
];
$licenseOptions = client_show_license_options();

$formValue = static function (string $field, $fallback = '') use ($oldInput) {
    if (array_key_exists($field, $oldInput)) {
        return (string) $oldInput[$field];
    }

    return (string) $fallback;
};
$creditoFiscalActual = strtoupper(trim((string) $formValue(
    'alta_credito_fiscal',
    (string) (($onboarding['alta_credito_fiscal'] ?? '') !== ''
        ? ($onboarding['alta_credito_fiscal'] ?? '')
        : ((int) ($item['LiteralE'] ?? 0) === 1 ? 'LITERAL E' : 'NO'))
)));
$creditoFiscalOptions = [
    'NO' => 'No',
    'LITERAL E' => 'Literal E',
    'RESGUARDO' => 'Resguardo',
];
$selectedGiro = client_show_prefer_nonzero($onboarding['cliente_id_giro'] ?? null, $item['ClienteIdGiro'] ?? '0');
$selectedOrigen = client_show_prefer_nonzero($onboarding['cliente_id_fidelizacion'] ?? null, $item['ClienteIdFidelizacion'] ?? '0');
$selectedVendedor = client_show_prefer_nonzero($onboarding['cliente_id_vendedor'] ?? null, $item['ClienteIdVendedor'] ?? '0');
$selectedTipoEmpresa = strtoupper(client_show_prefer_nonempty($onboarding['alta_tipoempresa'] ?? null, (string) ($item['AltaTipoEmpresa'] ?? '')));
$selectedDepartamentoId = client_show_select_option_value(
    $formOptions['departamentos'] ?? [],
    $formValue('departamento_id', ''),
    (string) (($item['departamento_nombre'] ?? '') !== '' ? ($item['departamento_nombre'] ?? '') : ($item['Departamento'] ?? ''))
);
$selectedCiudadId = client_show_select_option_value(
    $formOptions['ciudades'] ?? [],
    $formValue('ciudad_id', ''),
    (string) (($item['ciudad_nombre'] ?? '') !== '' ? ($item['ciudad_nombre'] ?? '') : ($item['Ciudad'] ?? ''))
);
$selectedSucursalCodigo = client_show_prefer_nonempty($onboarding['suc_cod_sucursal'] ?? null, (string) (($item['SucCodSucursal'] ?? '') !== '' ? ($item['SucCodSucursal'] ?? '') : ($item['OnboardingSucCodSucursal'] ?? '')));
require __DIR__ . '/../layout/header.php';
?>

<section class="space-y-4">
            <div class="rounded-[28px] border border-[#f0d9d3] bg-white/95 px-4 py-2.5 shadow-[0_22px_60px_-34px_rgba(230,91,79,0.45)]">
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
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center rounded-full bg-[#fff0ec] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-[#cc4f44]">Ficha cliente</span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= client_show_h($habBadge[1]) ?>"><?= client_show_h($habBadge[0]) ?></span>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">ID empresa <?= client_show_h((string) ($item['IdEmpresa'] ?? '')) ?></span>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">RUT <?= client_show_h((string) ($item['Rut'] ?? '')) ?></span>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700"><?= client_show_h(client_show_license_label($item['LicenciaCodigo'] ?? '')) ?></span>
                            <?php if ($latestCertificateDays !== ''): ?>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700"><?= client_show_h($latestCertificateDays) ?> dias cert.</span>
                            <?php endif; ?>
                        </div>
                        <h2 class="mt-1 text-[1.28rem] font-extrabold leading-tight tracking-tight text-slate-900"><?= client_show_h((string) ($item['RazonSocial'] ?? 'Cliente')) ?></h2>
                        <p class="mt-0.5 text-xs text-slate-500"><?= client_show_h(client_show_value((string) ($item['NombreFantasia'] ?? ''), 'Sin nombre fantasia')) ?></p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-1.5">
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

    <form id="client-conflict-overwrite-form" method="post" action="<?= client_show_h(app_url('index.php?action=client-update&id=' . (int) ($item['IdEmpresa'] ?? 0) . ($embeddedView ? '&embed=1' : ''))) ?>" class="hidden">
        <input type="hidden" name="client_conflict_resolution" value="overwrite">
    </form>
    <form id="client-conflict-discard-form" method="post" action="<?= client_show_h(app_url('index.php?action=client-update&id=' . (int) ($item['IdEmpresa'] ?? 0) . ($embeddedView ? '&embed=1' : ''))) ?>" class="hidden">
        <input type="hidden" name="client_conflict_resolution" value="discard">
    </form>

    <style>
        .client-onboarding-theme {
            --client-theme-accent: #4f46e5;
            --client-theme-accent-soft: rgba(79, 70, 229, 0.18);
            --client-theme-border: #dbe3ef;
        }

        .client-onboarding-theme input:not([type="radio"]):not([type="checkbox"]):not([type="file"]):not([type="hidden"]),
        .client-onboarding-theme select,
        .client-onboarding-theme textarea {
            border-radius: 0.75rem;
            border-color: var(--client-theme-border);
            background: #ffffff;
            padding: 0.68rem 0.95rem;
            font-size: 0.92rem;
            line-height: 1.35rem;
            color: #0f172a;
            box-shadow: none;
        }

        .client-onboarding-theme input:not([type="radio"]):not([type="checkbox"]):not([type="file"]):not([type="hidden"]):focus,
        .client-onboarding-theme select:focus,
        .client-onboarding-theme textarea:focus {
            border-color: var(--client-theme-accent);
            box-shadow: 0 0 0 3px var(--client-theme-accent-soft);
        }

        .client-onboarding-theme .searchable-select-wrapper {
            border-radius: 0.75rem;
            border-color: var(--client-theme-border);
            min-height: 44px;
        }

        .client-onboarding-theme .searchable-select-wrapper .ts-control,
        .client-onboarding-theme .searchable-select-wrapper.single .ts-control {
            min-height: 44px;
            border-radius: 0.75rem;
            border-color: var(--client-theme-border);
            padding: 0.4rem 0.85rem;
            box-shadow: none;
        }

        .client-onboarding-theme .searchable-select-wrapper.focus .ts-control,
        .client-onboarding-theme .searchable-select-wrapper .ts-control:focus-within {
            border-color: var(--client-theme-accent);
            box-shadow: 0 0 0 3px var(--client-theme-accent-soft);
        }

        .client-onboarding-theme .searchable-select-wrapper .ts-dropdown {
            border-radius: 0.75rem;
            border-color: var(--client-theme-border);
            overflow: hidden;
        }

        .client-onboarding-theme .searchable-select-wrapper .ts-dropdown .active {
            background: #eef2ff;
            color: #312e81;
        }

        .client-onboarding-theme .searchable-select-wrapper .ts-dropdown .option.active,
        .client-onboarding-theme .searchable-select-wrapper .ts-dropdown .option:hover {
            background: #eef2ff;
            color: #312e81;
        }

        .client-onboarding-theme .searchable-select-wrapper .ts-control > input::placeholder,
        .client-onboarding-theme input::placeholder,
        .client-onboarding-theme textarea::placeholder {
            color: #94a3b8;
        }

        .client-onboarding-theme input:not([type="radio"]):not([type="checkbox"]):not([type="file"]):not([type="hidden"])[disabled],
        .client-onboarding-theme select[disabled],
        .client-onboarding-theme textarea[disabled] {
            background: #f8fafc;
            color: #94a3b8;
        }

        .client-onboarding-theme input:not([type="radio"]):not([type="checkbox"]):not([type="file"]):not([type="hidden"]).focus\:border-\[\#e65b4f\]:focus,
        .client-onboarding-theme select.focus\:border-\[\#e65b4f\]:focus,
        .client-onboarding-theme textarea.focus\:border-\[\#e65b4f\]:focus {
            border-color: var(--client-theme-accent);
        }

        .client-onboarding-theme input:not([type="radio"]):not([type="checkbox"]):not([type="file"]):not([type="hidden"]).focus\:ring-\[\#e65b4f\]\/20:focus,
        .client-onboarding-theme select.focus\:ring-\[\#e65b4f\]\/20:focus,
        .client-onboarding-theme textarea.focus\:ring-\[\#e65b4f\]\/20:focus {
            --tw-ring-color: var(--client-theme-accent-soft);
        }

        .client-onboarding-theme .tox .tox-edit-area__iframe,
        .client-onboarding-theme .tox .tox-toolbar,
        .client-onboarding-theme .tox .tox-menubar {
            background: #ffffff;
        }

        .client-onboarding-theme .tox.tox-tinymce {
            border-radius: 0.75rem;
            border-color: var(--client-theme-border);
            overflow: hidden;
        }

        .client-onboarding-theme .tox.tox-tinymce:focus-within {
            border-color: var(--client-theme-accent);
            box-shadow: 0 0 0 3px var(--client-theme-accent-soft);
        }
    </style>

    <form method="post" enctype="multipart/form-data" action="<?= client_show_h(app_url('index.php?action=client-update&id=' . (int) ($item['IdEmpresa'] ?? 0) . ($embeddedView ? '&embed=1' : ''))) ?>" class="client-onboarding-theme space-y-5">
        <input type="hidden" name="client_dirty_fields" id="client_dirty_fields" value="">
        <?php if ($embeddedErrorMessage !== ''): ?>
            <div class="rounded-[22px] border border-rose-200 bg-gradient-to-br from-rose-50 via-white to-rose-50 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-rose-700">Guardado con observaciones</p>
                <p class="mt-1 text-sm font-semibold text-slate-900"><?= client_show_h($embeddedErrorMessage) ?></p>
            </div>
        <?php elseif ($savedFromEmbeddedFlow || $embeddedSuccessMessage !== ''): ?>
            <div class="rounded-[22px] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-emerald-50 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-700">Cambios guardados</p>
                <p class="mt-1 text-sm font-semibold text-slate-900"><?= client_show_h($embeddedSuccessMessage !== '' ? $embeddedSuccessMessage : 'La ficha se actualizo y puede revisar el resultado sin cerrar esta ventana.') ?></p>
            </div>
        <?php endif; ?>

        <?php if ($clientPanelSyncStatus !== []): ?>
            <div class="rounded-[24px] border border-amber-200 bg-gradient-to-br from-amber-50 via-white to-rose-50 p-3.5 shadow-sm">
                <div class="flex flex-col gap-1.5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-700">Resultado de sincronizacion</p>
                        <h3 class="mt-0.5 text-base font-extrabold text-slate-900">Revise qu&eacute; pas&oacute; en cada destino</h3>
                        <p class="mt-0.5 text-[13px] leading-5 text-slate-600">El cambio local se guard&oacute;, pero hubo observaciones en una o m&aacute;s etapas de la sincronizaci&oacute;n.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700"><?= client_show_h((string) count($clientPanelSyncStatus)) ?> etapa(s)</span>
                </div>

                <div class="mt-3 grid gap-2 lg:grid-cols-2">
                    <?php foreach ($clientPanelSyncStatus as $syncRow): ?>
                        <?php
                        $syncState = trim((string) ($syncRow['status'] ?? ''));
                        $syncCardClass = 'border-slate-200 bg-slate-50';
                        $syncBadgeClass = 'bg-slate-200 text-slate-700';
                        $syncBadgeLabel = 'Pendiente';
                        if ($syncState === 'ok') {
                            $syncCardClass = 'border-emerald-200 bg-emerald-50';
                            $syncBadgeClass = 'bg-emerald-100 text-emerald-700';
                            $syncBadgeLabel = 'OK';
                        } elseif ($syncState === 'error') {
                            $syncCardClass = 'border-rose-200 bg-rose-50';
                            $syncBadgeClass = 'bg-rose-100 text-rose-700';
                            $syncBadgeLabel = 'Error';
                        } elseif ($syncState === 'skip') {
                            $syncCardClass = 'border-amber-200 bg-amber-50';
                            $syncBadgeClass = 'bg-amber-100 text-amber-700';
                            $syncBadgeLabel = 'Sin envio';
                        }
                        ?>
                        <article class="rounded-[18px] border px-3.5 py-3 shadow-sm <?= client_show_h($syncCardClass) ?>">
                            <div class="flex flex-col gap-1.5 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h4 class="text-sm font-extrabold text-slate-900"><?= client_show_h((string) ($syncRow['label'] ?? 'Destino')) ?></h4>
                                    <p class="mt-0.5 text-[13px] leading-5 text-slate-600"><?= client_show_h((string) ($syncRow['detail'] ?? 'Sin detalle adicional.')) ?></p>
                                    <?php if (!empty($syncRow['summary']) && is_array($syncRow['summary'])): ?>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <?php foreach ($syncRow['summary'] as $summaryItem): ?>
                                                <?php if (trim((string) $summaryItem) === '') { continue; } ?>
                                                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white/80 px-2.5 py-1 text-[11px] font-semibold text-slate-700"><?= client_show_h((string) $summaryItem) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.18em] <?= client_show_h($syncBadgeClass) ?>"><?= client_show_h($syncBadgeLabel) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($clientPanelConflicts !== []): ?>
            <div class="rounded-[24px] border border-rose-200 bg-gradient-to-br from-rose-50 via-white to-amber-50 p-4 shadow-sm">
                <div class="flex flex-col gap-1.5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-rose-600">Conflictos detectados</p>
                        <h3 class="mt-1 text-lg font-extrabold text-slate-900">Revise las diferencias antes de guardar</h3>
                        <p class="mt-1 text-sm text-slate-600">El panel detectó valores distintos entre fuentes. No se guardó este intento para evitar sobrescribir información más reciente sin revisión.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700"><?= client_show_h((string) count($clientPanelConflicts)) ?> campo(s)</span>
                </div>

                <div class="mt-3 flex flex-col gap-2 rounded-[18px] border border-amber-200 bg-amber-50/80 p-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Elija c&oacute;mo resolver este intento</p>
                        <p class="mt-1 text-xs text-slate-600">Puede descartar este cambio y conservar los valores actuales, o confirmar conscientemente que lo escrito en el panel debe sobrescribir Empresas, Clientes, onboarding ligado y Migrate si aplica.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" form="client-conflict-overwrite-form" class="inline-flex items-center rounded-2xl bg-[#e65b4f] px-3.5 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#d95044]">
                            Sobrescribir con el valor del panel
                        </button>
                        <button type="submit" form="client-conflict-discard-form" class="inline-flex items-center rounded-2xl border border-slate-300 bg-white px-3.5 py-1.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            Conservar valores actuales
                        </button>
                    </div>
                </div>

                <div class="mt-3 grid gap-2">
                    <?php foreach ($clientPanelConflicts as $conflict): ?>
                        <?php $conflictSources = is_array($conflict['sources'] ?? null) ? $conflict['sources'] : []; ?>
                        <article class="rounded-[18px] border border-rose-100 bg-white px-3.5 py-3 shadow-sm">
                            <div class="flex flex-col gap-1.5 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h4 class="text-sm font-extrabold text-slate-900"><?= client_show_h((string) ($conflict['label'] ?? 'Campo')) ?></h4>
                                    <p class="mt-1 text-xs text-slate-500">Valor que intentó guardar el panel</p>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700"><?= client_show_h((string) ($conflict['panel_display'] ?? 'Sin dato')) ?></span>
                            </div>

                            <div class="mt-2.5 grid gap-2 md:grid-cols-3">
                                <?php foreach ($conflictSources as $sourceName => $sourceValue): ?>
                                    <?php
                                    $sourceLabel = [
                                        'empresas' => 'Empresas',
                                        'clientes' => 'Clientes',
                                        'onboarding' => 'EmpresasNuevas',
                                        'migrate' => 'Migrate',
                                    ][$sourceName] ?? (string) $sourceName;
                                    ?>
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-1.5">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500"><?= client_show_h($sourceLabel) ?></p>
                                        <p class="mt-0.5 text-sm font-semibold text-slate-800"><?= client_show_h((string) $sourceValue) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 bg-gradient-to-r from-[#fff2ef] to-white px-4 py-2">
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-client-tab="general" class="client-form-tab is-active inline-flex items-center rounded-2xl bg-[#e65b4f] px-3 py-1.5 text-[12px] font-semibold text-white">Ficha principal</button>
                    <button type="button" data-client-tab="modules" class="client-form-tab inline-flex items-center rounded-2xl border border-slate-300 bg-white px-3 py-1.5 text-[12px] font-semibold text-slate-700">Modulos y funciones</button>
                    <button type="button" data-client-tab="expediente" class="client-form-tab inline-flex items-center rounded-2xl border border-slate-300 bg-white px-3 py-1.5 text-[12px] font-semibold text-slate-700">Archivos en expediente</button>
                </div>
            </div>
        </div>

        <div id="client-main-layout" class="grid gap-4 lg:grid-cols-[minmax(0,1.55fr)_260px] xl:grid-cols-[minmax(0,1.42fr)_290px] 2xl:grid-cols-[minmax(0,1.42fr)_320px]">
            <div class="space-y-5">
                <div id="client-tab-general" class="client-form-panel space-y-5">
                    <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-200 bg-gradient-to-r from-[#fff2ef] to-white px-5 py-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-extrabold text-slate-900">Ficha principal del cliente</h3>
                                    <p class="mt-0.5 text-xs text-slate-500">Bloque unificado con datos de empresa, factura electronica y notas operativas.</p>
                                </div>
                                <button
                                    type="button"
                                    id="client-history-toggle"
                                    title="Ver historial de cambios"
                                    aria-label="Ver historial de cambios"
                                    aria-expanded="true"
                                    aria-controls="client-history-sidebar"
                                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl border border-slate-300 bg-white text-slate-600 transition hover:bg-slate-50"
                                >
                                    <i data-lucide="panel-right-open" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-4 p-4">
                            <div class="rounded-[24px] border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-4 shadow-[0_14px_32px_-28px_rgba(15,23,42,0.45)]">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-base font-bold text-slate-900">Identificacion y ubicacion</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Datos principales de la empresa, domicilio fiscal y referencias geograficas.</p>
                                    </div>
                                    <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500 shadow-sm">Base operativa</span>
                                </div>
                                <div class="grid gap-3 md:grid-cols-12">
                                    <div class="md:col-span-8">
                                        <label for="razon_social" class="mb-1.5 block text-xs font-semibold text-slate-700">Razon Social</label>
                                        <input id="razon_social" name="razon_social" value="<?= client_show_h($formValue('razon_social', (string) ($item['RazonSocial'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="nombre_fantasia" class="mb-1.5 block text-xs font-semibold text-slate-700">Nombre Comercial</label>
                                        <input id="nombre_fantasia" name="nombre_fantasia" value="<?= client_show_h($formValue('nombre_fantasia', (string) ($item['NombreFantasia'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="rut" class="mb-1.5 block text-xs font-semibold text-slate-700">RUT</label>
                                        <input id="rut" name="rut" value="<?= client_show_h($formValue('rut', (string) ($item['Rut'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-8">
                                        <label for="domicilio" class="mb-1.5 block text-xs font-semibold text-slate-700">Domicilio Fiscal</label>
                                        <input id="domicilio" name="domicilio" value="<?= client_show_h($formValue('domicilio', (string) ($item['Domicilio'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="departamento_id" class="mb-1.5 block text-xs font-semibold text-slate-700">Departamento</label>
                                        <select
                                            id="departamento_id"
                                            name="departamento_id"
                                            data-searchable-select="1"
                                            data-searchable-placeholder="Buscar departamento..."
                                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20"
                                        >
                                            <option value="">Seleccionar departamento</option>
                                            <?php foreach (($formOptions['departamentos'] ?? []) as $option): ?>
                                                <?php $optionValue = trim((string) ($option['id'] ?? '')); ?>
                                                <?php $optionLabel = trim((string) ($option['nombre'] ?? '')); ?>
                                                <?php if ($optionValue === '' || $optionLabel === '') { continue; } ?>
                                                <option value="<?= client_show_h($optionValue) ?>" <?= $selectedDepartamentoId === $optionValue ? 'selected' : '' ?>>
                                                    <?= client_show_h($optionLabel) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="ciudad_id" class="mb-1.5 block text-xs font-semibold text-slate-700">Ciudad</label>
                                        <select
                                            id="ciudad_id"
                                            name="ciudad_id"
                                            data-searchable-select="1"
                                            data-searchable-placeholder="Buscar ciudad..."
                                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20"
                                        >
                                            <option value="">Seleccionar ciudad</option>
                                            <?php foreach (($formOptions['ciudades'] ?? []) as $option): ?>
                                                <?php $optionValue = trim((string) ($option['id'] ?? '')); ?>
                                                <?php $optionLabel = trim((string) ($option['nombre'] ?? '')); ?>
                                                <?php if ($optionValue === '' || $optionLabel === '') { continue; } ?>
                                                <option value="<?= client_show_h($optionValue) ?>" <?= $selectedCiudadId === $optionValue ? 'selected' : '' ?>>
                                                    <?= client_show_h($optionLabel) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="email_principal" class="mb-1.5 block text-xs font-semibold text-slate-700">Email Principal</label>
                                        <input id="email_principal" name="email_principal" value="<?= client_show_h($formValue('email_principal', (string) (($item['ClienteEmail'] ?? '') !== '' ? $item['ClienteEmail'] : ($item['EmpresaEmail'] ?? '')))) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="email_envio_fe" class="mb-1.5 block text-xs font-semibold text-slate-700">Email de Facturas</label>
                                        <input id="email_envio_fe" name="email_envio_fe" value="<?= client_show_h($formValue('email_envio_fe', (string) ($item['emailEnvioFE'] ?? ($item['cUsuarioEmailInv'] ?? '')))) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="telefono" class="mb-1.5 block text-xs font-semibold text-slate-700">Telefono</label>
                                        <input id="telefono" name="telefono" value="<?= client_show_h($formValue('telefono', (string) ($item['Tel'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-4">
                                        <label for="sitio_web" class="mb-1.5 block text-xs font-semibold text-slate-700">Sitio Web</label>
                                        <input id="sitio_web" name="sitio_web" value="<?= client_show_h((string) ($item['SitioWeb'] ?? '')) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div class="md:col-span-8">
                                        <p class="mb-1.5 block text-xs font-semibold text-slate-700">Logo actual</p>
                                        <div class="rounded-[20px] border border-slate-200 bg-white px-3 py-2.5">
                                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                                <div class="sm:w-[180px]">
                                                    <?php if ($logoSrc !== null): ?>
                                                        <button type="button" id="client-logo-open-inline" class="flex h-14 w-full items-center justify-center overflow-hidden rounded-[16px] border border-slate-200 bg-white shadow-sm transition hover:border-[#e65b4f]">
                                                            <img src="<?= client_show_h($logoSrc) ?>" alt="Logo actual" class="max-h-10 max-w-[140px] object-contain">
                                                        </button>
                                                    <?php else: ?>
                                                        <div class="flex h-14 w-full items-center justify-center rounded-[16px] border border-dashed border-slate-300 bg-white text-base font-black text-slate-500">
                                                            <?= client_show_h(client_show_logo_text($item)) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <label for="archivo_logo" class="inline-flex cursor-pointer items-center gap-2 rounded-2xl bg-[#e65b4f] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#d95044]">
                                                            <i data-lucide="upload" class="h-4 w-4"></i>
                                                            <span>Seleccionar logo</span>
                                                        </label>
                                                        <p id="archivo_logo_nombre" class="text-xs text-slate-500">PNG, JPG, GIF, BMP o WEBP. Maximo 10 MB.</p>
                                                    </div>
                                                    <input id="archivo_logo" name="archivo_logo" type="file" accept=".png,.jpg,.jpeg,.gif,.bmp,.webp" class="hidden">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 xl:grid-cols-3">
                                <div class="rounded-[24px] border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-4 shadow-[0_14px_32px_-28px_rgba(15,23,42,0.45)]">
                                    <div class="mb-3">
                                        <p class="text-base font-bold text-slate-900">Estado comercial y status</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Credito fiscal, abonado y estado operativo vigente.</p>
                                    </div>
                                    <div class="grid gap-3 lg:grid-cols-[260px_minmax(0,1fr)] lg:items-start">
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Credito fiscal</label>
                                            <div class="rounded-[22px] border border-slate-200 bg-slate-50 p-3">
                                                <div class="grid gap-2">
                                                    <?php foreach ($creditoFiscalOptions as $optionValue => $optionLabel): ?>
                                                        <label class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 cursor-pointer">
                                                            <input type="radio" name="alta_credito_fiscal" value="<?= client_show_h($optionValue) ?>" <?= $creditoFiscalActual === $optionValue ? 'checked' : '' ?>>
                                                            <span><?= client_show_h($optionLabel) ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <label for="cliente_abonado" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Abonado</label>
                                                <label class="inline-flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 cursor-pointer">
                                                    <span class="text-sm font-medium text-slate-700">Cliente abonado</span>
                                                    <span class="relative">
                                                        <input type="hidden" name="cliente_abonado" value="NO">
                                                        <input id="cliente_abonado" type="checkbox" name="cliente_abonado" value="SI" class="peer sr-only" <?= strtoupper(trim($formValue('cliente_abonado', (string) ($item['ClienteAbonado'] ?? 'NO')))) === 'SI' ? 'checked' : '' ?>>
                                                        <span class="block h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-emerald-500"></span>
                                                        <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                                                    </span>
                                                </label>
                                            </div>
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
                                            <div class="mt-3">
                                                <label for="fecha_ip" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Fecha IP</label>
                                                <input id="fecha_ip" name="fecha_ip" type="date" value="<?= client_show_h($formValue('fecha_ip', client_show_date_input((string) ($item['FechaIP'] ?? '')))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[24px] border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-4 shadow-[0_14px_32px_-28px_rgba(15,23,42,0.45)]">
                                    <div class="mb-3">
                                        <p class="text-base font-bold text-slate-900">Referencia comercial</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Clasificacion, origen y operador asignado para el cliente.</p>
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <div class="md:col-span-2">
                                            <label for="cliente_id_giro" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Rubro</label>
                                            <select id="cliente_id_giro" name="cliente_id_giro" data-searchable-select="1" data-searchable-placeholder="Buscar rubro..." class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                                <option value="0">SELECCIONE</option>
                                                <?php foreach (($formOptions['giros'] ?? []) as $option): ?>
                                                    <option value="<?= client_show_h((string) ($option['id'] ?? '0')) ?>" <?= $formValue('cliente_id_giro', $selectedGiro) === (string) ($option['id'] ?? '0') ? 'selected' : '' ?>><?= client_show_h((string) ($option['nombre'] ?? '')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="cliente_id_fidelizacion" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Origen</label>
                                            <select id="cliente_id_fidelizacion" name="cliente_id_fidelizacion" data-searchable-select="1" data-searchable-placeholder="Buscar origen..." class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                                <option value="0">SELECCIONE</option>
                                                <?php foreach (($formOptions['fidelizaciones'] ?? []) as $option): ?>
                                                    <option value="<?= client_show_h((string) ($option['id'] ?? '0')) ?>" <?= $formValue('cliente_id_fidelizacion', $selectedOrigen) === (string) ($option['id'] ?? '0') ? 'selected' : '' ?>><?= client_show_h((string) ($option['nombre'] ?? '')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="cliente_id_vendedor" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Vendedor / Operador</label>
                                            <select id="cliente_id_vendedor" name="cliente_id_vendedor" data-searchable-select="1" data-searchable-placeholder="Buscar operador..." class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                                <option value="0">SELECCIONE</option>
                                                <?php foreach (($formOptions['vendedores'] ?? []) as $option): ?>
                                                    <option value="<?= client_show_h((string) ($option['id'] ?? '0')) ?>" <?= $formValue('cliente_id_vendedor', $selectedVendedor) === (string) ($option['id'] ?? '0') ? 'selected' : '' ?>><?= client_show_h((string) ($option['nombre'] ?? '')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="nombre_completo_firmante" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nombre del firmante</label>
                                            <input id="nombre_completo_firmante" name="nombre_completo_firmante" value="<?= client_show_h($formValue('nombre_completo_firmante', (string) ($item['NombreCompletoFirmante'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                        </div>
                                        <div>
                                            <label for="ci_firmante" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">CI del firmante</label>
                                            <input id="ci_firmante" name="ci_firmante" value="<?= client_show_h($formValue('ci_firmante', (string) ($item['CI_Firmante'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[24px] border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-4 shadow-[0_14px_32px_-28px_rgba(15,23,42,0.45)]">
                                    <div class="mb-3">
                                        <p class="text-base font-bold text-slate-900">Licenciamiento y plan</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Licencia contratada, usuarios, CFE mensuales y condiciones del abonado.</p>
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-4">
                                        <div>
                                            <label for="usuarios" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Usuarios</label>
                                            <input
                                                id="usuarios"
                                                name="usuarios"
                                                type="number"
                                                min="0"
                                                step="1"
                                                value="<?= client_show_h($formValue('usuarios', (string) (($onboarding['usuarios'] ?? '') !== '' ? ($onboarding['usuarios'] ?? '') : ($item['UsuariosLicencia'] ?? '0')))) ?>"
                                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20"
                                            >
                                        </div>
                                        <div>
                                            <label for="cfe_mensuales" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">CFE mensuales</label>
                                            <input
                                                id="cfe_mensuales"
                                                name="cfe_mensuales"
                                                type="number"
                                                min="0"
                                                step="1"
                                                value="<?= client_show_h($formValue('cfe_mensuales', (string) (($onboarding['cfe_mensuales'] ?? '') !== '' ? ($onboarding['cfe_mensuales'] ?? '') : ($item['Plan'] ?? '0')))) ?>"
                                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20"
                                            >
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Licencia</label>
                                            <select name="licencia_codigo" data-searchable-select="1" data-searchable-placeholder="Buscar licencia..." class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                                <?php foreach ($licenseOptions as $option): ?>
                                                    <option value="<?= client_show_h($option['value']) ?>" <?= $formValue('licencia_codigo', (string) ($item['LicenciaCodigo'] ?? '')) === (string) $option['value'] ? 'selected' : '' ?>><?= client_show_h($option['label']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="cliente_abonado_importe" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Monto</label>
                                            <input id="cliente_abonado_importe" name="cliente_abonado_importe" type="number" min="0" step="0.01" inputmode="decimal" value="<?= client_show_h($formValue('cliente_abonado_importe', (string) (($onboarding['cliente_abonado_importe'] ?? '') !== '' ? ($onboarding['cliente_abonado_importe'] ?? '') : ($item['ClienteAbonadoImporte'] ?? '0')))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                        </div>
                                        <div>
                                            <label for="cliente_abonado_moneda" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Moneda</label>
                                            <select id="cliente_abonado_moneda" name="cliente_abonado_moneda" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                                <?php $monedaActual = strtoupper(trim($formValue('cliente_abonado_moneda', (string) (($onboarding['cliente_abonado_moneda'] ?? '') !== '' ? ($onboarding['cliente_abonado_moneda'] ?? '') : (($item['ClienteAbonadoMoneda'] ?? '') !== '' ? ($item['ClienteAbonadoMoneda'] ?? '') : 'UYU'))))); ?>
                                                <option value="UYU" <?= $monedaActual === 'UYU' ? 'selected' : '' ?>>UYU</option>
                                                <option value="USD" <?= $monedaActual === 'USD' ? 'selected' : '' ?>>USD</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="cliente_abonado_periodo" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Periodo de pago</label>
                                            <?php $periodoActual = strtoupper(trim($formValue('cliente_abonado_periodo', (string) (($onboarding['cliente_abonado_periodo'] ?? '') !== '' ? ($onboarding['cliente_abonado_periodo'] ?? '') : (($item['ClienteAbonadoPeriodo'] ?? '') !== '' ? ($item['ClienteAbonadoPeriodo'] ?? '') : 'MENSUAL'))))); ?>
                                            <select id="cliente_abonado_periodo" name="cliente_abonado_periodo" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                                <option value="MENSUAL" <?= $periodoActual === 'MENSUAL' ? 'selected' : '' ?>>MENSUAL</option>
                                                <option value="ANUAL" <?= $periodoActual === 'ANUAL' ? 'selected' : '' ?>>ANUAL</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="cliente_abonado_descuento" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Descuento</label>
                                            <input id="cliente_abonado_descuento" name="cliente_abonado_descuento" type="number" min="0" step="0.01" inputmode="decimal" value="<?= client_show_h($formValue('cliente_abonado_descuento', (string) (($onboarding['cliente_abonado_descuento'] ?? '') !== '' ? ($onboarding['cliente_abonado_descuento'] ?? '') : ($item['ClienteAbonadoDescuento'] ?? '0')))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label for="cliente_adenda" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Adenda</label>
                                        <textarea id="cliente_adenda" name="cliente_adenda" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20"><?= client_show_h($formValue('cliente_adenda', (string) ($onboarding['cliente_adenda'] ?? ''))) ?></textarea>
                                    </div>
                                </div>

                                <input type="hidden" name="notificar_deuda" value="<?= client_show_h($formValue('notificar_deuda', (string) ($item['Notificar'] ?? ''))) ?>">
                                <input type="hidden" name="notificar_suspension" value="<?= client_show_h($formValue('notificar_suspension', (string) ($item['NotificarSuspension'] ?? ''))) ?>">
                                <input type="hidden" name="suspension_dias" value="<?= client_show_h($formValue('suspension_dias', (string) ($item['Suspension'] ?? ''))) ?>">
                            </div>

                            <div class="rounded-[24px] border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-4 shadow-[0_14px_32px_-28px_rgba(15,23,42,0.45)]">
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-base font-bold text-slate-900">Configuracion fiscal y emision</p>
                                            <p class="mt-0.5 text-xs text-slate-500">Accesos de facturacion, tipo de empresa, sucursal y datos del firmante.</p>
                                        </div>
                                    <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500 shadow-sm">Facturacion y accesos</span>
                                </div>
                                    <div class="grid gap-3 md:grid-cols-3">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nro empresa Invoicy</label>
                                        <input value="<?= client_show_h(client_show_value((string) ($item['EmpresaInvoicy'] ?? ''))) ?>" readonly class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
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
                                        <label for="alta_tributario" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Regimen tributario</label>
                                        <input id="alta_tributario" name="alta_tributario" value="<?= client_show_h($formValue('alta_tributario', (string) ($item['AltaTributario'] ?? ''))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div>
                                        <label for="alta_exonerado_norma" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Norma de exoneracion</label>
                                        <input id="alta_exonerado_norma" name="alta_exonerado_norma" value="<?= client_show_h($formValue('alta_exonerado_norma', (string) client_show_prefer_nonempty($onboarding['alta_exonerado_norma'] ?? null, (string) ($item['AltaExoneradoNorma'] ?? '')))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div>
                                        <label for="suc_cod_sucursal" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Codigo sucursal</label>
                                        <input id="suc_cod_sucursal" name="suc_cod_sucursal" value="<?= client_show_h($formValue('suc_cod_sucursal', $selectedSucursalCodigo)) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div>
                                        <label for="suc_cod_fecha_vigencia" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Fecha del codigo</label>
                                        <input id="suc_cod_fecha_vigencia" name="suc_cod_fecha_vigencia" type="date" value="<?= client_show_h(client_show_date_input($formValue('suc_cod_fecha_vigencia', (string) client_show_prefer_nonempty($onboarding['suc_cod_fecha_vigencia'] ?? null, (string) ($item['SucCodFechaVigencia'] ?? ''))))) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                    </div>
                                    <div>
                                        <label for="id_usuario_ad" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Administrador Dynamica</label>
                                        <select id="id_usuario_ad" name="id_usuario_ad" data-searchable-select="1" data-searchable-placeholder="Buscar administrador..." class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                            <option value="">SELECCIONE</option>
                                            <?php
                                            $adminUserSelectedValue = $adminUserCurrentRaw;
                                            foreach ($adminUsers as $option) {
                                                $optionId = trim((string) ($option['id'] ?? ''));
                                                $optionName = trim((string) ($option['nombre'] ?? ''));
                                                if ($adminUserCurrentRaw !== '' && (
                                                    $adminUserCurrentRaw === $optionId
                                                    || mb_strtoupper($adminUserCurrentRaw) === mb_strtoupper($optionName)
                                                )) {
                                                    $adminUserSelectedValue = $optionId;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <?php foreach ($adminUsers as $option): ?>
                                                <option value="<?= client_show_h((string) ($option['id'] ?? '')) ?>" <?= $adminUserSelectedValue === (string) ($option['id'] ?? '') ? 'selected' : '' ?>><?= client_show_h((string) ($option['nombre'] ?? '')) ?></option>
                                            <?php endforeach; ?>
                                            <?php
                                            $adminUserExists = false;
                                            foreach ($adminUsers as $option) {
                                                if ((string) ($option['id'] ?? '') === $adminUserSelectedValue) {
                                                    $adminUserExists = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <?php if ($adminUserSelectedValue !== '' && !$adminUserExists): ?>
                                                <option value="<?= client_show_h($adminUserSelectedValue) ?>" selected><?= client_show_h($adminUserCurrentRaw) ?></option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <?php
                                        $altaTipoEmpresaActual = strtoupper(trim($formValue('alta_tipoempresa', $selectedTipoEmpresa)));
                                        $tiposEmpresaCliente = [
                                            'UNIPERSONAL' => 'UNIPERSONAL',
                                            'SOCIEDAD' => 'SOCIEDAD',
                                        ];
                                        $tipoEmpresaLegacy = $altaTipoEmpresaActual !== '' && !isset($tiposEmpresaCliente[$altaTipoEmpresaActual])
                                            ? $altaTipoEmpresaActual
                                            : '';
                                        ?>
                                        <label for="alta_tipoempresa" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Tipo de empresa</label>
                                        <select id="alta_tipoempresa" name="alta_tipoempresa" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20">
                                            <option value="" <?= $altaTipoEmpresaActual === '' ? 'selected' : '' ?>>SELECCIONE</option>
                                            <?php foreach ($tiposEmpresaCliente as $tipoEmpresaValue => $tipoEmpresaLabel): ?>
                                                <option value="<?= client_show_h($tipoEmpresaValue) ?>" <?= $altaTipoEmpresaActual === $tipoEmpresaValue ? 'selected' : '' ?>><?= client_show_h($tipoEmpresaLabel) ?></option>
                                            <?php endforeach; ?>
                                            <?php if ($tipoEmpresaLegacy !== ''): ?>
                                                <option value="<?= client_show_h($tipoEmpresaLegacy) ?>" selected><?= client_show_h($tipoEmpresaLegacy) ?> (valor actual)</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-[24px] border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-4 shadow-[0_14px_32px_-28px_rgba(15,23,42,0.45)]">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-base font-bold text-slate-900">Notas del cliente</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Seguimiento operativo consolidado para empresa y caso ligado.</p>
                                    </div>
                                    <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500 shadow-sm">Seguimiento</span>
                                </div>
                                <textarea id="notas_admin" name="notas_admin" rows="12" class="w-full rounded-[24px] border border-slate-200 px-4 py-4 text-sm text-slate-700 transition focus:border-[#e65b4f] focus:outline-none focus:ring-2 focus:ring-[#e65b4f]/20"><?= client_show_h($formValue('notas_admin', $notesValue)) ?></textarea>
                                <p class="mt-2 text-xs text-slate-500">Este bloque alimenta la nota operativa de la empresa y el caso ligado cuando existe onboarding.</p>
                            </div>
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

                                        <div class="md:col-span-2 grid gap-3 lg:grid-cols-2">
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
                                        </div>

                                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">Habilitar gestionar mas de un CAE por documento</label>
                                            <label class="inline-flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer">
                                                <span class="text-sm font-medium text-slate-700">Permitir mas de un CAE por documento</span>
                                                <span class="relative">
                                                    <input type="hidden" name="module_mas_de_un_cae" value="0">
                                                    <input type="checkbox" name="module_mas_de_un_cae" value="1" class="peer sr-only" <?= (string) ($item['pMasDeUnTipoCae'] ?? '0') === '1' ? 'checked' : '' ?>>
                                                    <span class="block h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-emerald-500"></span>
                                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="client-tab-expediente" class="client-form-panel hidden space-y-5">
                    <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-200 bg-gradient-to-r from-[#fff2ef] to-white px-6 py-4">
                            <h3 class="text-lg font-extrabold text-slate-900">Archivos en expediente</h3>
                            <p class="mt-1 text-sm text-slate-500">Acceso al certificado actual, historial detectado y archivos asociados al cliente o al caso ligado.</p>
                        </div>
                        <div class="space-y-5 p-6">
                            <div class="grid gap-4 xl:grid-cols-2">
                                <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Certificado actual</p>
                                    <div class="mt-3 space-y-2">
                                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                            <p class="text-sm font-semibold text-slate-900"><?= client_show_h(client_show_value($latestCertificateAlias, 'Sin snapshot')) ?></p>
                                            <p class="mt-1 text-xs text-slate-500">Vence: <?= client_show_h($latestCertificateExpiry !== '' ? client_show_date($latestCertificateExpiry) : 'Sin fecha') ?></p>
                                            <p class="mt-1 text-xs text-slate-500">Contrasena: <?= client_show_h($passwordValue !== '' ? $passwordValue : 'Sin dato') ?></p>
                                        </div>
                                        <?php if ($certificateFiles !== []): ?>
                                            <?php foreach ($certificateFiles as $fileRow): ?>
                                                <div class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-slate-900"><?= client_show_h(client_show_value((string) ($fileRow['nombre_original'] ?? ''), 'Archivo certificado')) ?></p>
                                                        <p class="mt-1 text-xs text-slate-500"><?= client_show_h(client_show_datetime((string) ($fileRow['fecha_subida'] ?? ''))) ?></p>
                                                    </div>
                                                    <?php if (trim((string) ($fileRow['download_url'] ?? '')) !== ''): ?>
                                                        <a href="<?= client_show_h((string) ($fileRow['download_url'] ?? '')) ?>" class="inline-flex items-center rounded-2xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Descargar</a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-500">No hay archivo de certificado descargable identificado en el expediente.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Notificaciones de vencimiento</p>
                                    <div class="mt-3 space-y-2">
                                        <?php if ($certificateNotifications !== []): ?>
                                            <?php foreach ($certificateNotifications as $notification): ?>
                                                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                                    <p class="text-sm font-semibold text-slate-900"><?= client_show_h(client_show_value((string) ($notification['titulo'] ?? ''), 'Notificacion')) ?></p>
                                                    <p class="mt-1 text-xs text-slate-500"><?= client_show_h(client_show_datetime((string) ($notification['fecha_creacion'] ?? ''))) ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-500">No hay notificaciones historicas registradas para este cliente.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 xl:grid-cols-2">
                                <div class="rounded-[24px] border border-slate-200 bg-white p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Historial de certificados</p>
                                    <div class="mt-3 space-y-2">
                                        <?php if ($certificateHistory !== []): ?>
                                            <?php foreach ($certificateHistory as $historyRow): ?>
                                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                                    <p class="text-sm font-semibold text-slate-900"><?= client_show_h(client_show_value((string) ($historyRow['alias_certificado'] ?? ''), 'Certificado')) ?></p>
                                                    <div class="mt-1 grid gap-1 text-xs text-slate-500">
                                                        <p>Carga: <?= client_show_h(client_show_datetime((string) ($historyRow['fecha_carga'] ?? ''))) ?></p>
                                                        <p>Vence: <?= client_show_h(client_show_date((string) ($historyRow['fecha_vencimiento'] ?? ''))) ?></p>
                                                        <p>Contrasena: <?= client_show_h(client_show_value((string) ($historyRow['password_certificado'] ?? ''), 'Sin dato')) ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm text-slate-500">No hay historial centralizado de certificados para esta empresa.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="rounded-[24px] border border-slate-200 bg-white p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Otros archivos vinculados</p>
                                    <div class="mt-3 space-y-2">
                                        <?php if ($files !== []): ?>
                                            <?php foreach ($files as $fileRow): ?>
                                                <div class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-slate-900"><?= client_show_h(client_show_value((string) ($fileRow['nombre_original'] ?? ''), 'Archivo')) ?></p>
                                                        <p class="mt-1 text-xs text-slate-500"><?= client_show_h(client_show_value((string) ($fileRow['tipo_archivo'] ?? ''), 'Sin tipo')) ?> · <?= client_show_h(client_show_datetime((string) ($fileRow['fecha_subida'] ?? ''))) ?></p>
                                                    </div>
                                                    <?php if (trim((string) ($fileRow['download_url'] ?? '')) !== ''): ?>
                                                        <a href="<?= client_show_h((string) ($fileRow['download_url'] ?? '')) ?>" class="inline-flex items-center rounded-2xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Descargar</a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm text-slate-500">No hay archivos adicionales vinculados al expediente.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="client-history-sidebar" class="space-y-4 lg:sticky lg:top-4 self-start">
                <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-[#fff2ef] to-white px-4 py-2.5">
                        <h3 class="text-base font-extrabold text-slate-900">Historial de cambios</h3>
                        <p class="mt-0.5 text-[11px] leading-4 text-slate-500">Cambios hechos desde esta ficha, con fecha, usuario y valores antes y despues.</p>
                    </div>
                    <div class="space-y-2 p-2.5">
                        <?php if ($clientPanelHistory !== []): ?>
                            <div class="space-y-1.5">
                                <?php foreach ($clientPanelHistory as $historyRow): ?>
                                    <div class="rounded-[14px] border border-slate-200 bg-white px-2 py-1.5 shadow-[0_14px_28px_-28px_rgba(15,23,42,0.6)]">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="truncate text-[10px] font-extrabold leading-tight text-slate-900"><?= client_show_h((string) ($historyRow['CampoLabel'] ?? 'Campo')) ?></p>
                                                <p class="mt-0.5 text-[8px] leading-tight text-slate-500"><?= client_show_h(client_show_datetime((string) ($historyRow['FechaCambio'] ?? ''))) ?></p>
                                            </div>
                                            <span class="shrink-0 rounded-full bg-slate-100 px-1.5 py-0.5 text-[8px] font-semibold uppercase tracking-[0.1em] text-slate-600"><?= client_show_h(client_show_value((string) (($historyRow['UsuarioNombre'] ?? '') !== '' ? ($historyRow['UsuarioNombre'] ?? '') : ($historyRow['UsuarioLogin'] ?? '')), 'Sistema')) ?></span>
                                        </div>
                                        <div class="mt-1.5 grid gap-1">
                                            <div class="rounded-[12px] border border-slate-200 bg-slate-50 px-2 py-1">
                                                <p class="text-[8px] font-semibold uppercase tracking-[0.14em] text-slate-500">Valor anterior</p>
                                                <p class="mt-0.5 text-[10px] font-semibold leading-tight text-slate-800"><?= client_show_h(client_show_history_value((string) ($historyRow['CampoClave'] ?? ''), (string) ($historyRow['ValorAnterior'] ?? ''))) ?></p>
                                            </div>
                                            <div class="rounded-[12px] border border-emerald-200 bg-emerald-50 px-2 py-1">
                                                <p class="text-[8px] font-semibold uppercase tracking-[0.14em] text-emerald-700">Valor nuevo</p>
                                                <p class="mt-0.5 text-[10px] font-semibold leading-tight text-emerald-900"><?= client_show_h(client_show_history_value((string) ($historyRow['CampoClave'] ?? ''), (string) ($historyRow['ValorNuevo'] ?? ''))) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="rounded-[18px] border border-dashed border-slate-300 bg-gradient-to-br from-slate-50 to-white px-3 py-3 text-xs text-slate-500">Aun no hay cambios registrados desde esta ficha.</div>
                        <?php endif; ?>

                        <?php if ($actions !== []): ?>
                            <div class="rounded-[20px] border border-slate-200 bg-slate-50 p-3">
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Actividad operativa disponible hoy</p>
                                    <span class="rounded-full bg-white px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-500"><?= client_show_h((string) count($actions)) ?> evento(s)</span>
                                </div>
                                <div class="space-y-1.5">
                                    <?php foreach ($actions as $action): ?>
                                        <div class="rounded-[14px] border border-slate-200 bg-white px-3 py-2 shadow-[0_12px_26px_-26px_rgba(15,23,42,0.55)]">
                                            <p class="text-[11px] font-semibold leading-tight text-slate-900"><?= client_show_h((string) ($action['Descripcion'] ?? 'Accion')) ?></p>
                                            <p class="mt-0.5 text-[9px] leading-tight text-slate-500"><?= client_show_h(client_show_datetime((string) ($action['FechaAccion'] ?? ''))) ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
        <div class="sticky bottom-0 z-20 mt-5 border-t border-slate-200 bg-white/95 px-4 py-4 backdrop-blur sm:px-0">
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
    var clientForm = document.querySelector('form.client-onboarding-theme[action*="action=client-update"]');
    var dirtyFieldsInput = document.getElementById('client_dirty_fields');
    var logoModal = document.getElementById('client-logo-modal');
    var logoOpen = document.getElementById('client-logo-open');
    var logoOpenInline = document.getElementById('client-logo-open-inline');
    var logoClose = document.getElementById('client-logo-close');
    var logoInput = document.getElementById('archivo_logo');
    var logoInputName = document.getElementById('archivo_logo_nombre');
    var tabs = document.querySelectorAll('[data-client-tab]');
    var panels = document.querySelectorAll('.client-form-panel');
    var conflictButtons = document.querySelectorAll('[data-client-conflict-action]');
    var mainLayout = document.getElementById('client-main-layout');
    var historyToggle = document.getElementById('client-history-toggle');
    var historySidebar = document.getElementById('client-history-sidebar');
    function setHistoryExpanded(expanded) {
        if (!historyToggle || !historySidebar || !mainLayout) {
            return;
        }

        historySidebar.classList.toggle('hidden', !expanded);
        historyToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        historyToggle.setAttribute('title', expanded ? 'Ocultar historial de cambios' : 'Ver historial de cambios');
        historyToggle.setAttribute('aria-label', expanded ? 'Ocultar historial de cambios' : 'Ver historial de cambios');

        var icon = historyToggle.querySelector('i');
        if (icon) {
            icon.setAttribute('data-lucide', expanded ? 'panel-right-close' : 'panel-right-open');
        }

        if (window.innerWidth >= 1024) {
            mainLayout.style.gridTemplateColumns = expanded ? '' : 'minmax(0,1fr)';
        } else {
            mainLayout.style.gridTemplateColumns = '';
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    if (historyToggle && historySidebar) {
        setHistoryExpanded(window.innerWidth >= 1024);

        historyToggle.addEventListener('click', function () {
            var expanded = historyToggle.getAttribute('aria-expanded') === 'true';
            setHistoryExpanded(!expanded);
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024 && historyToggle.getAttribute('aria-expanded') === 'false') {
                mainLayout.style.gridTemplateColumns = 'minmax(0,1fr)';
            } else if (window.innerWidth >= 1024) {
                mainLayout.style.gridTemplateColumns = '';
            } else {
                mainLayout.style.gridTemplateColumns = '';
            }
        });
    }

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

    conflictButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!clientForm) {
                return;
            }

            var resolution = button.getAttribute('data-client-conflict-action') || '';
            if (resolution === '') {
                return;
            }

            var hiddenField = clientForm.querySelector('input[name="client_conflict_resolution"]');
            if (!hiddenField) {
                hiddenField = document.createElement('input');
                hiddenField.type = 'hidden';
                hiddenField.name = 'client_conflict_resolution';
                clientForm.appendChild(hiddenField);
            }

            hiddenField.value = resolution;
            clientForm.submit();
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

    function readFieldValue(field) {
        if (!field || !field.name) {
            return '';
        }

        if (field.type === 'checkbox') {
            return field.checked ? (field.value || '1') : '';
        }

        if (field.type === 'radio') {
            if (!clientForm) {
                return '';
            }
            var checkedRadio = clientForm.querySelector('input[type="radio"][name="' + CSS.escape(field.name) + '"]:checked');
            return checkedRadio ? (checkedRadio.value || '') : '';
        }

        if (field.type === 'file') {
            return field.files && field.files.length > 0 ? '__FILE_SELECTED__' : '';
        }

        return field.value || '';
    }

    function collectTrackedFieldNames() {
        if (!clientForm) {
            return [];
        }

        var ignoredNames = {
            client_dirty_fields: true,
            client_conflict_resolution: true
        };
        var names = [];
        var seen = {};

        clientForm.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (field) {
            var name = field.name || '';
            if (name === '' || ignoredNames[name]) {
                return;
            }

            if (field.type === 'hidden' && name !== 'archivo_logo') {
                return;
            }

            if (seen[name]) {
                return;
            }

            seen[name] = true;
            names.push(name);
        });

        return names;
    }

    function resolveTrackedField(name) {
        if (!clientForm || !name) {
            return null;
        }

        var checkbox = clientForm.querySelector('input[type="checkbox"][name="' + CSS.escape(name) + '"]');
        if (checkbox) {
            return checkbox;
        }

        var radio = clientForm.querySelector('input[type="radio"][name="' + CSS.escape(name) + '"]');
        if (radio) {
            return radio;
        }

        return clientForm.querySelector('[name="' + CSS.escape(name) + '"]');
    }

    var initialFieldValues = {};
    collectTrackedFieldNames().forEach(function (name) {
        var field = resolveTrackedField(name);
        if (name === 'notas_admin' && window.tinymce && window.tinymce.get('notas_admin')) {
            initialFieldValues[name] = window.tinymce.get('notas_admin').getContent();
            return;
        }
        initialFieldValues[name] = readFieldValue(field);
    });

    if (clientForm && dirtyFieldsInput) {
        clientForm.addEventListener('submit', function () {
            var dirtyNames = [];

            collectTrackedFieldNames().forEach(function (name) {
                var currentValue = '';
                if (name === 'notas_admin' && window.tinymce && window.tinymce.get('notas_admin')) {
                    currentValue = window.tinymce.get('notas_admin').getContent();
                } else {
                    var field = resolveTrackedField(name);
                    currentValue = readFieldValue(field);
                }

                var initialValue = Object.prototype.hasOwnProperty.call(initialFieldValues, name)
                    ? initialFieldValues[name]
                    : '';

                if (String(currentValue) !== String(initialValue)) {
                    dirtyNames.push(name);
                }
            });

            dirtyFieldsInput.value = dirtyNames.join(',');
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

});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
