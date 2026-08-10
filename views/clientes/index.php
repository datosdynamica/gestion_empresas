<?php
declare(strict_types=1);

function clients_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function clients_starts_with(string $haystack, string $needle): bool
{
    return $needle === '' || substr($haystack, 0, strlen($needle)) === $needle;
}

function clients_contains(string $haystack, string $needle): bool
{
    return $needle === '' || strpos($haystack, $needle) !== false;
}

function clients_detail_value($value): string
{
    $text = trim((string) $value);
    return $text !== '' ? $text : 'Sin dato';
}

function clients_format_datetime(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return 'Sin fecha';
    }

    try {
        return (new DateTimeImmutable($value))->format('Y/m/d H:i');
    } catch (Throwable $e) {
        return $value;
    }
}

function clients_license_label($value): string
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
    return $map[$key] ?? ('Licencia ' . $key);
}

function clients_logo_text(array $item): string
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

function clients_logo_mime(string $binary): string
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

function clients_logo_src(array $item, ?array $onboardingLogo = null): ?string
{
    $raw = $item['ImagenLogo'] ?? '';
    if (!is_string($raw) || $raw === '') {
        $relativePath = trim((string) ($onboardingLogo['relative_path'] ?? ''));
        if ($relativePath === '') {
            return null;
        }

        $absolutePath = FileStorage::absoluteFromRelative($relativePath);
        if (!is_file($absolutePath)) {
            return null;
        }

        $raw = file_get_contents($absolutePath) ?: '';
        if ($raw === '') {
            return null;
        }
    }

    if (substr($raw, 0, 4) === '*nm*') {
        $raw = base64_decode(substr($raw, 4), true) ?: '';
    }

    if ($raw === '') {
        return null;
    }

    return 'data:' . clients_logo_mime($raw) . ';base64,' . base64_encode($raw);
}

function clients_has_real_logo(array $item, ?array $onboardingLogo = null): bool
{
    return clients_logo_src($item, $onboardingLogo) !== null;
}

function clients_cert_state(array $item, ?array $snapshot): array
{
    if (!is_array($snapshot) || $snapshot === []) {
        return ['Sin consulta', 'bg-slate-100 text-slate-600'];
    }

    if ((int) ($snapshot['HasCertificate'] ?? 0) !== 1) {
        return ['Sin certificado', 'bg-amber-100 text-amber-700'];
    }

    $days = isset($snapshot['DiasRestantes']) ? (int) $snapshot['DiasRestantes'] : null;
    if ($days !== null && $days <= 7) {
        return [$days . ' dias', 'bg-rose-100 text-rose-700'];
    }

    if ($days !== null && $days <= 30) {
        return [$days . ' dias', 'bg-amber-100 text-amber-700'];
    }

    return ['Vigente', 'bg-emerald-100 text-emerald-700'];
}

function clients_hab_state(array $item): array
{
    $raw = strtoupper(trim((string) ($item['Habilitada'] ?? '')));
    if ($raw === '') {
        return ['Sin dato', 'bg-slate-100 text-slate-600', 'bg-white'];
    }

    if (clients_starts_with($raw, 'NO') && clients_contains($raw, 'CERTIFIC')) {
        return ['NO (En proc. de certificacion)', 'bg-amber-100 text-amber-700', 'bg-amber-50/70'];
    }

    if (clients_starts_with($raw, 'NO')) {
        return ['BAJA LOGICA', 'bg-rose-100 text-rose-700', 'bg-rose-50/60'];
    }

    if (clients_starts_with($raw, 'SUSPEND')) {
        return ['SUSPENDIDA', 'bg-orange-100 text-orange-700', 'bg-orange-50/60'];
    }

    if (in_array($raw, ['SI', 'S', '1'], true)) {
        return ['SI', 'bg-emerald-100 text-emerald-700', 'bg-white'];
    }

    return [trim((string) ($item['Habilitada'] ?? '')), 'bg-slate-100 text-slate-600', 'bg-white'];
}

function clients_notif_summary(array $item): string
{
    $deuda = (int) ($item['Notificar'] ?? 0);
    $susp = (int) ($item['NotificarSuspension'] ?? 0);
    $corte = (int) ($item['Suspension'] ?? 0);

    return 'Deuda ' . $deuda . 'd / Susp. ' . $susp . 'd / Corte ' . $corte . 'd';
}

function clients_payment_summary(array $item, ?array $snapshot): string
{
    if (is_array($snapshot) && isset($snapshot['DiasRestantes']) && (int) ($snapshot['HasCertificate'] ?? 0) === 1) {
        return 'Certificado: ' . (int) $snapshot['DiasRestantes'] . ' dias';
    }

    $hab = strtoupper(trim((string) ($item['Habilitada'] ?? '')));
    if (clients_starts_with($hab, 'SUSPEND')) {
        return 'Suspension visible';
    }
    if (clients_starts_with($hab, 'NO')) {
        return 'Revisar estado';
    }

    return 'Sin atraso visible';
}

function clients_services(array $item): array
{
    $services = [];
    $map = [
        'pNoVentas' => 'Ventas',
        'pNoCompras' => 'Compras',
        'pNoStock' => 'Stock',
        'pNoCajayBancos' => 'Caja/Bancos',
        'pNoCrm' => 'CRM',
        'pAsu' => 'Asu',
        'pSucursales' => 'Bd Multi-Empresas',
    ];

    foreach ($map as $field => $label) {
        $value = (string) ($item[$field] ?? '');
        if (in_array($field, ['pAsu', 'pSucursales'], true)) {
            if (in_array(strtoupper($value), ['1', 'SI', 'S'], true)) {
                $services[] = $label;
            }
            continue;
        }

        if (!in_array(strtoupper($value), ['1', 'SI', 'S'], true)) {
            $services[] = $label;
        }
    }

    return $services;
}

function clients_action_label(string $value): string
{
    $normalized = strtoupper(trim($value));
    if ($normalized === 'AVISO') {
        return 'Aviso manual';
    }

    if ($normalized === 'CERTIFICADO_RECORDATORIO') {
        return 'Recordatorio por correo';
    }

    if ($normalized === 'CERTIFICADO_SUBIDO') {
        return 'Certificado subido';
    }

    return $normalized !== '' ? str_replace('_', ' ', ucfirst(strtolower($normalized))) : 'Sin accion';
}

function clients_numeric_filter_label(string $value): string
{
    return ((int) $value) === 0 ? 'Nunca' : ((string) ((int) $value));
}

function clients_pagination_pages(int $currentPage, int $totalPages, int $window = 2): array
{
    if ($totalPages <= 1) {
        return [1];
    }

    if ($currentPage <= 3) {
        $start = 1;
        $end = min($totalPages, 5);
    } elseif ($currentPage >= ($totalPages - 2)) {
        $end = $totalPages;
        $start = max(1, $totalPages - 4);
    } else {
        $start = max(1, $currentPage - $window);
        $end = min($totalPages, $currentPage + $window);
    }

    return range($start, $end);
}

function clients_build_filter_link(array $overrides, array $state): string
{
    $query = array_merge($state, $overrides);
    foreach ($query as $key => $value) {
        if ($value === '' || $value === null) {
            unset($query[$key]);
        }
    }

    return app_url('index.php?' . http_build_query($query));
}

$enableCreateModal = false;
$activeNav = 'clientes';
$pageSubtitle = 'Consulta operativa de sus empresas con acceso a datos generales, estado comercial y certificados.';
$items = is_array($items ?? null) ? $items : [];
$pagination = $pagination ?? ['page' => 1, 'total_pages' => 1];
$search = trim((string) ($search ?? ''));
$statusFilter = trim((string) ($statusFilter ?? 'habilitadas'));
$sortBy = trim((string) ($sortBy ?? 'idempresa'));
$sortDirection = trim((string) ($sortDirection ?? 'desc'));
$habFilter = trim((string) ($habFilter ?? ''));
$licenseFilter = trim((string) ($licenseFilter ?? ''));
$usersFilter = trim((string) ($usersFilter ?? ''));
$certificateFilter = trim((string) ($certificateFilter ?? ''));
$debtNotificationFilter = trim((string) ($debtNotificationFilter ?? ''));
$suspensionNotificationFilter = trim((string) ($suspensionNotificationFilter ?? ''));
$suspensionFilter = trim((string) ($suspensionFilter ?? ''));
$facetCounts = is_array($facetCounts ?? null) ? $facetCounts : ['hab' => [], 'licenses' => [], 'users' => [], 'cert' => [], 'debt' => [], 'notif_susp' => [], 'suspension' => []];
$certificateSnapshots = is_array($certificateSnapshots ?? null) ? $certificateSnapshots : [];
$certificateActions = is_array($certificateActions ?? null) ? $certificateActions : [];
$latestOnboardingByRut = is_array($latestOnboardingByRut ?? null) ? $latestOnboardingByRut : [];
$onboardingLogoByRut = is_array($onboardingLogoByRut ?? null) ? $onboardingLogoByRut : [];

$queryState = [
    'route' => 'clientes',
    'q' => $search,
    'status' => $statusFilter,
    'per_page' => (int) ($pagination['per_page'] ?? 25),
    'sort' => $sortBy,
    'dir' => $sortDirection,
    'hab' => $habFilter,
    'license' => $licenseFilter,
    'users' => $usersFilter,
    'cert' => $certificateFilter,
    'debt' => $debtNotificationFilter,
    'notif_susp' => $suspensionNotificationFilter,
    'susp' => $suspensionFilter,
];

require __DIR__ . '/../layout/header.php';
?>
<style>
    .client-theme-accent {
        background: linear-gradient(180deg, #e65b4f 0%, #d95044 100%);
        color: #fff;
        box-shadow: 0 16px 34px rgba(230, 91, 79, 0.22);
    }

    .client-theme-accent-soft {
        background: #fff2ef;
        color: #d95044;
    }

    .client-filter-card.is-collapsed [data-client-filter-body] {
        display: none;
    }

    .client-filters-sidebar.is-collapsed #client-filters-groups {
        display: none;
    }

    .client-filters-sidebar.is-collapsed {
        width: 5.5rem;
    }

    .client-filters-sidebar.is-collapsed #client-filters-sidebar-label,
    .client-filters-sidebar.is-collapsed #client-filters-sidebar-hint {
        display: none;
    }

    .client-company-compact {
        display: none;
    }

    @media (min-width: 1280px) {
        .client-panel-layout.filters-collapsed {
            grid-template-columns: 5.5rem minmax(0, 1fr);
        }
    }

    @media (max-width: 1500px) {
        .client-grid-table .client-col-notifications,
        .client-grid-table .client-col-payments {
            display: none;
        }

        .client-company-compact {
            display: block;
        }
    }
</style>

<section class="space-y-5">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-indigo-700">Mis empresas</span>
                <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">Consulta - Empresas Software DYNAMICA</h2>
                <p class="mt-2 max-w-4xl text-sm text-slate-500">Consulte el estado general de sus empresas, filtre la informacion operativa y acceda a la ficha de cada cliente desde un solo lugar.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <p class="font-semibold text-slate-900"><?= (int) ($pagination['total_items'] ?? 0) ?></p>
                <p>empresas visibles con los filtros actuales</p>
            </div>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[300px_minmax(0,1fr)] client-panel-layout" id="client-panel-layout">
        <aside class="space-y-4 client-filters-sidebar" id="client-filters-sidebar">
            <div class="rounded-3xl border border-[#fde1db] bg-white shadow-sm overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-4">
                    <div>
                        <p id="client-filters-sidebar-label" class="text-sm font-bold uppercase tracking-[0.18em] text-slate-900">Filtros</p>
                        <p id="client-filters-sidebar-hint" class="mt-1 text-xs text-slate-500">Puede plegar todo el panel o cada bloque.</p>
                    </div>
                    <button type="button" id="client-filters-sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[#fde1db] bg-[#fff2ef] text-[#d95044] transition hover:bg-white" aria-expanded="true">
                        <i data-lucide="panel-left-close" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>

            <div id="client-filters-groups" class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden client-filter-card">
                <div class="border-b border-slate-200 client-theme-accent px-5 py-3 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold uppercase tracking-[0.18em]">(Hab.)</h3>
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-white transition hover:bg-white/20" data-client-filter-toggle aria-expanded="true">
                        <i data-lucide="chevron-up" class="h-4 w-4"></i>
                    </button>
                </div>
                <div class="divide-y divide-slate-100 text-sm" data-client-filter-body>
                    <?php
                    $habOptions = [
                        'baja_logica' => 'BAJA LOGICA',
                        'en_certificacion' => 'NO (En Proc. de Certificacion)',
                        'si' => 'SI',
                        'suspendida' => 'SUSPENDIDA (Por no pago)',
                    ];
                    foreach ($habOptions as $key => $label):
                        $count = (int) ($facetCounts['hab'][$key] ?? 0);
                        $isActive = $habFilter === $key;
                    ?>
                        <a href="<?= clients_h(clients_build_filter_link(['hab' => $isActive ? '' : $key, 'page' => 1], $queryState)) ?>" class="flex items-center justify-between px-4 py-3 transition hover:bg-slate-50 <?= $isActive ? 'client-theme-accent-soft font-semibold' : 'text-slate-700' ?>">
                            <span><?= clients_h($label) ?></span>
                            <span class="text-xs font-semibold"><?= $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden client-filter-card">
                <div class="border-b border-slate-200 client-theme-accent px-5 py-3 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold uppercase tracking-[0.18em]">Licencia</h3>
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-white transition hover:bg-white/20" data-client-filter-toggle aria-expanded="true">
                        <i data-lucide="chevron-up" class="h-4 w-4"></i>
                    </button>
                </div>
                <div class="divide-y divide-slate-100 text-sm" data-client-filter-body>
                    <?php foreach (($facetCounts['licenses'] ?? []) as $code => $count): ?>
                        <?php $isActive = $licenseFilter === (string) $code; ?>
                        <a href="<?= clients_h(clients_build_filter_link(['license' => $isActive ? '' : (string) $code, 'page' => 1], $queryState)) ?>" class="flex items-center justify-between px-4 py-3 transition hover:bg-slate-50 <?= $isActive ? 'client-theme-accent-soft font-semibold' : 'text-slate-700' ?>">
                            <span><?= clients_h($code . ' - ' . clients_license_label((string) $code)) ?></span>
                            <span class="text-xs font-semibold"><?= (int) $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden client-filter-card">
                <div class="border-b border-slate-200 client-theme-accent px-5 py-3 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold uppercase tracking-[0.18em]">Usuarios</h3>
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-white transition hover:bg-white/20" data-client-filter-toggle aria-expanded="true">
                        <i data-lucide="chevron-up" class="h-4 w-4"></i>
                    </button>
                </div>
                <div class="divide-y divide-slate-100 text-sm" data-client-filter-body>
                    <?php
                    $userOptions = [
                        '0' => '0 usuarios',
                        '1' => '1 usuario',
                        '2_5' => '2 a 5 usuarios',
                        '6_10' => '6 a 10 usuarios',
                        '11_plus' => '11 o mas',
                    ];
                    foreach ($userOptions as $key => $label):
                        $count = (int) ($facetCounts['users'][$key] ?? 0);
                        $isActive = $usersFilter === $key;
                    ?>
                        <a href="<?= clients_h(clients_build_filter_link(['users' => $isActive ? '' : $key, 'page' => 1], $queryState)) ?>" class="flex items-center justify-between px-4 py-3 transition hover:bg-slate-50 <?= $isActive ? 'client-theme-accent-soft font-semibold' : 'text-slate-700' ?>">
                            <span><?= clients_h($label) ?></span>
                            <span class="text-xs font-semibold"><?= $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden client-filter-card">
                <div class="border-b border-slate-200 client-theme-accent px-5 py-3 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold uppercase tracking-[0.18em]">Certificados</h3>
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-white transition hover:bg-white/20" data-client-filter-toggle aria-expanded="true">
                        <i data-lucide="chevron-up" class="h-4 w-4"></i>
                    </button>
                </div>
                <div class="divide-y divide-slate-100 text-sm" data-client-filter-body>
                    <?php
                    $certOptions = [
                        'con_empcodigo' => 'Con EmpCodigo',
                        'sin_empcodigo' => 'Sin EmpCodigo',
                        'con_cliente' => 'Con fila en Clientes',
                        'sin_cliente' => 'Sin fila en Clientes',
                    ];
                    foreach ($certOptions as $key => $label):
                        $count = (int) ($facetCounts['cert'][$key] ?? 0);
                        $isActive = $certificateFilter === $key;
                    ?>
                        <a href="<?= clients_h(clients_build_filter_link(['cert' => $isActive ? '' : $key, 'page' => 1], $queryState)) ?>" class="flex items-center justify-between px-4 py-3 transition hover:bg-slate-50 <?= $isActive ? 'client-theme-accent-soft font-semibold' : 'text-slate-700' ?>">
                            <span><?= clients_h($label) ?></span>
                            <span class="text-xs font-semibold"><?= $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden client-filter-card">
                <div class="border-b border-slate-200 client-theme-accent px-5 py-3 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold uppercase tracking-[0.18em]">Notificar por deuda</h3>
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-white transition hover:bg-white/20" data-client-filter-toggle aria-expanded="true">
                        <i data-lucide="chevron-up" class="h-4 w-4"></i>
                    </button>
                </div>
                <div class="divide-y divide-slate-100 text-sm" data-client-filter-body>
                    <?php foreach (($facetCounts['debt'] ?? []) as $value => $count): ?>
                        <?php $isActive = $debtNotificationFilter === (string) $value; ?>
                        <a href="<?= clients_h(clients_build_filter_link(['debt' => $isActive ? '' : (string) $value, 'page' => 1], $queryState)) ?>" class="flex items-center justify-between px-4 py-3 transition hover:bg-slate-50 <?= $isActive ? 'client-theme-accent-soft font-semibold' : 'text-slate-700' ?>">
                            <span><?= clients_h(clients_numeric_filter_label((string) $value)) ?></span>
                            <span class="text-xs font-semibold"><?= (int) $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden client-filter-card">
                <div class="border-b border-slate-200 client-theme-accent px-5 py-3 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold uppercase tracking-[0.18em]">Notificar suspension</h3>
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-white transition hover:bg-white/20" data-client-filter-toggle aria-expanded="true">
                        <i data-lucide="chevron-up" class="h-4 w-4"></i>
                    </button>
                </div>
                <div class="divide-y divide-slate-100 text-sm" data-client-filter-body>
                    <?php foreach (($facetCounts['notif_susp'] ?? []) as $value => $count): ?>
                        <?php $isActive = $suspensionNotificationFilter === (string) $value; ?>
                        <a href="<?= clients_h(clients_build_filter_link(['notif_susp' => $isActive ? '' : (string) $value, 'page' => 1], $queryState)) ?>" class="flex items-center justify-between px-4 py-3 transition hover:bg-slate-50 <?= $isActive ? 'client-theme-accent-soft font-semibold' : 'text-slate-700' ?>">
                            <span><?= clients_h(clients_numeric_filter_label((string) $value)) ?></span>
                            <span class="text-xs font-semibold"><?= (int) $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden client-filter-card">
                <div class="border-b border-slate-200 client-theme-accent px-5 py-3 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold uppercase tracking-[0.18em]">Suspension</h3>
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-white transition hover:bg-white/20" data-client-filter-toggle aria-expanded="true">
                        <i data-lucide="chevron-up" class="h-4 w-4"></i>
                    </button>
                </div>
                <div class="divide-y divide-slate-100 text-sm" data-client-filter-body>
                    <?php foreach (($facetCounts['suspension'] ?? []) as $value => $count): ?>
                        <?php $isActive = $suspensionFilter === (string) $value; ?>
                        <a href="<?= clients_h(clients_build_filter_link(['susp' => $isActive ? '' : (string) $value, 'page' => 1], $queryState)) ?>" class="flex items-center justify-between px-4 py-3 transition hover:bg-slate-50 <?= $isActive ? 'client-theme-accent-soft font-semibold' : 'text-slate-700' ?>">
                            <span><?= clients_h(clients_numeric_filter_label((string) $value)) ?></span>
                            <span class="text-xs font-semibold"><?= (int) $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            </div>
        </aside>

        <div class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-200 bg-slate-50 px-4 py-4">
                    <form id="client-filter-form" method="get" action="<?= clients_h(app_url('index.php')) ?>" class="grid gap-3 xl:grid-cols-[minmax(220px,1.1fr)_160px_160px_150px] 2xl:grid-cols-[minmax(220px,1.1fr)_160px_160px_150px_150px_auto_auto] xl:items-center">
                        <input type="hidden" name="route" value="clientes">
                        <?php if ($habFilter !== ''): ?><input type="hidden" name="hab" value="<?= clients_h($habFilter) ?>"><?php endif; ?>
                        <?php if ($licenseFilter !== ''): ?><input type="hidden" name="license" value="<?= clients_h($licenseFilter) ?>"><?php endif; ?>
                        <?php if ($usersFilter !== ''): ?><input type="hidden" name="users" value="<?= clients_h($usersFilter) ?>"><?php endif; ?>
                        <?php if ($certificateFilter !== ''): ?><input type="hidden" name="cert" value="<?= clients_h($certificateFilter) ?>"><?php endif; ?>
                        <?php if ($debtNotificationFilter !== ''): ?><input type="hidden" name="debt" value="<?= clients_h($debtNotificationFilter) ?>"><?php endif; ?>
                        <?php if ($suspensionNotificationFilter !== ''): ?><input type="hidden" name="notif_susp" value="<?= clients_h($suspensionNotificationFilter) ?>"><?php endif; ?>
                        <?php if ($suspensionFilter !== ''): ?><input type="hidden" name="susp" value="<?= clients_h($suspensionFilter) ?>"><?php endif; ?>
                        <div class="relative">
                            <input id="q" name="q" value="<?= clients_h($search) ?>" placeholder="Busqueda rapida por empresa, RUT o correo" class="w-full rounded-2xl border border-slate-200 px-4 py-3 pr-10 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <i data-lucide="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                        </div>
                        <select id="status" name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="habilitadas" <?= $statusFilter === 'habilitadas' ? 'selected' : '' ?>>Habilitadas</option>
                            <option value="no_habilitadas" <?= $statusFilter === 'no_habilitadas' ? 'selected' : '' ?>>No habilitadas</option>
                            <option value="todos" <?= $statusFilter === 'todos' ? 'selected' : '' ?>>Todas</option>
                        </select>
                        <select id="client-sort" name="sort" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="idempresa" <?= $sortBy === 'idempresa' ? 'selected' : '' ?>>Ordenar por ID</option>
                            <option value="razonsocial" <?= $sortBy === 'razonsocial' ? 'selected' : '' ?>>Ordenar por empresa</option>
                            <option value="rut" <?= $sortBy === 'rut' ? 'selected' : '' ?>>Ordenar por RUT</option>
                            <option value="habilitada" <?= $sortBy === 'habilitada' ? 'selected' : '' ?>>Ordenar por estado</option>
                        </select>
                        <select id="client-dir" name="dir" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="desc" <?= $sortDirection === 'desc' ? 'selected' : '' ?>>Desc</option>
                            <option value="asc" <?= $sortDirection === 'asc' ? 'selected' : '' ?>>Asc</option>
                        </select>
                        <select id="per_page" name="per_page" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <?php foreach (($pagination['per_page_options'] ?? [25]) as $option): ?>
                                <option value="<?= (int) $option ?>" <?= (int) ($pagination['per_page'] ?? 25) === (int) $option ? 'selected' : '' ?>><?= (int) $option ?> filas</option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-600">
                            <i data-lucide="filter" class="h-4 w-4"></i>
                            <span>Aplicar</span>
                        </button>
                        <a href="<?= clients_h(app_url('index.php?route=clientes')) ?>" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                            <span>Limpiar</span>
                        </a>
                    </form>
                </div>

                <div class="border-b border-slate-200 bg-white px-4 py-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <span class="inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-4 py-2 font-semibold text-white">
                                <i data-lucide="plus" class="h-4 w-4"></i>
                                <span>Nuevo</span>
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 font-medium text-slate-700">
                                <i data-lucide="columns-3" class="h-4 w-4"></i>
                                <span>Campos</span>
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 font-medium text-slate-700">
                                <i data-lucide="arrow-up-down" class="h-4 w-4"></i>
                                <span>Ordenar por</span>
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 font-medium text-slate-700">
                                <i data-lucide="download" class="h-4 w-4"></i>
                                <span>Exportar</span>
                            </span>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-slate-700">Pagina <?= (int) ($pagination['page'] ?? 1) ?> de <?= (int) ($pagination['total_pages'] ?? 1) ?></p>
                            <p class="text-xs text-slate-500">[ <?= (int) ($pagination['page'] ?? 1) ?> a <?= min((int) ($pagination['total_items'] ?? 0), (((int) ($pagination['page'] ?? 1) - 1) * (int) ($pagination['per_page'] ?? 25)) + count($items)) ?> de <?= (int) ($pagination['total_items'] ?? 0) ?> ]</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm client-grid-table">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-4 py-4 w-10"></th>
                                <th class="px-4 py-4 w-[110px]">Logo</th>
                                <th class="px-4 py-4 min-w-[320px]">Empresa</th>
                                <th class="px-4 py-4 min-w-[170px] client-col-notifications">Notificaciones</th>
                                <th class="px-4 py-4 min-w-[170px] client-col-payments">Atraso en pagos</th>
                                <th class="px-4 py-4 min-w-[160px]">(Hab.)</th>
                                <th class="px-4 py-4 min-w-[180px] client-col-operativo">Operativo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php if ($items === []): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">No hay empresas para mostrar con los filtros actuales.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($items as $item): ?>
                                <?php
                                $empresaId = (int) ($item['IdEmpresa'] ?? 0);
                                $rut = trim((string) ($item['Rut'] ?? ''));
                                $snapshot = $certificateSnapshots[$empresaId] ?? null;
                                [$certLabel, $certClass] = clients_cert_state($item, $snapshot);
                                [$habLabel, $habClass, $rowTone] = clients_hab_state($item);
                                $onboarding = $latestOnboardingByRut[$rut] ?? null;
                                $actions = $certificateActions[$empresaId] ?? [];
                                $services = clients_services($item);
                                $logoText = clients_logo_text($item);
                                $logoContext = $onboardingLogoByRut[$rut] ?? null;
                                $logoSrc = clients_logo_src($item, $logoContext);
                                $hasRealLogo = clients_has_real_logo($item, $logoContext);
                                ?>
                                <tr class="align-top transition hover:bg-slate-50/60 <?= $rowTone ?>">
                                    <td class="px-4 py-5 text-center">
                                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-indigo-300 hover:text-indigo-600" data-collapse-toggle="client-row-<?= $empresaId ?>">
                                            <i data-lucide="chevron-down" class="h-4 w-4"></i>
                                        </button>
                                    </td>
                                    <td class="px-4 py-5">
                                        <button type="button" class="flex h-16 w-24 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white text-lg font-black tracking-tight text-slate-700 shadow-sm transition hover:border-indigo-300 <?= $hasRealLogo ? 'cursor-zoom-in' : 'cursor-default' ?>" <?= $hasRealLogo ? 'data-logo-modal-src="' . clients_h($logoSrc ?? '') . '"' : '' ?> <?= $hasRealLogo ? 'data-logo-modal-title="' . clients_h((string) ($item['RazonSocial'] ?? '')) . '"' : '' ?>>
                                            <?php if ($logoSrc !== null): ?>
                                                <img src="<?= clients_h($logoSrc) ?>" alt="Logo <?= clients_h((string) ($item['RazonSocial'] ?? '')) ?>" class="max-h-14 max-w-[88px] object-contain">
                                            <?php else: ?>
                                                <span><?= clients_h($logoText) ?></span>
                                            <?php endif; ?>
                                        </button>
                                    </td>
                                    <td class="px-4 py-5">
                                        <p class="text-lg font-bold text-slate-900">Id: <?= $empresaId ?> - <?= clients_h((string) ($item['RazonSocial'] ?? '')) ?></p>
                                        <p class="mt-1 text-sm text-slate-700">
                                            <?= trim((string) ($item['EmpresaInvoicy'] ?? '')) !== '' ? 'En produccion' : 'Pendiente de enlace' ?>
                                            <?php if (trim((string) ($item['OnboardingFechaCreacion'] ?? '')) !== ''): ?>, desde: <span class="font-semibold"><?= clients_h(substr((string) $item['OnboardingFechaCreacion'], 0, 10)) ?></span><?php endif; ?>
                                        </p>
                                        <p class="mt-1 text-sm text-slate-700"><strong>Licencia:</strong> <?= clients_h(clients_license_label((string) ($item['LicenciaCodigo'] ?? '0'))) ?> <strong class="text-slate-900"><?= (int) ($item['UsuariosLicencia'] ?? 0) > 0 ? ((int) ($item['UsuariosLicencia'] ?? 0) . ' usuarios') : 'sin usuarios definidos' ?></strong>.</p>
                                        <p class="mt-1 text-sm text-slate-700"><strong>Plan:</strong> <?= clients_h(clients_detail_value($item['Plan'] ?? '')) ?></p>
                                        <p class="mt-1 text-sm text-slate-700"><strong>Mi Cliente:</strong> <?= clients_h(clients_detail_value(($item['ClienteEmail'] ?? '') !== '' ? $item['ClienteEmail'] : ($item['EmpresaEmail'] ?? ''))) ?></p>
                                        <div class="mt-3 flex flex-wrap items-center gap-2">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?= $certClass ?>"><?= clients_h($certLabel) ?></span>
                                            <?php if (is_array($onboarding)): ?>
                                                <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Onboarding ligado</span>
                                            <?php endif; ?>
                                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">RUT <?= clients_h(clients_detail_value($rut)) ?></span>
                                        </div>
                                        <div class="client-company-compact mt-4 space-y-3">
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Notificaciones</p>
                                                <p class="mt-2 text-sm font-semibold text-slate-900"><?= clients_h(clients_notif_summary($item)) ?></p>
                                                <p class="mt-2 text-xs text-slate-500">Avisos configurados en Empresas.</p>
                                            </div>
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Atraso en pagos</p>
                                                <p class="mt-2 text-sm font-semibold text-slate-900"><?= clients_h(clients_payment_summary($item, $snapshot)) ?></p>
                                                <p class="mt-2 text-xs text-slate-500">Resumen compacto para anchos intermedios.</p>
                                            </div>
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Operativo</p>
                                                <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-sm font-semibold text-slate-900">
                                                    <?php foreach (array_slice($services, 0, 7) as $service): ?>
                                                        <span><?= clients_h($service) ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <button type="button" data-client-modal-url="<?= clients_h(app_url('index.php?action=client-show&id=' . $empresaId . '&embed=1')) ?>" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                                                        <i data-lucide="pen-square" class="h-4 w-4"></i>
                                                        <span>Editar</span>
                                                    </button>
                                                    <button type="button" data-client-modal-url="<?= clients_h(app_url('index.php?action=client-certificates&id=' . $empresaId . '&embed=1')) ?>" class="inline-flex items-center gap-2 rounded-xl border border-[#e65b4f] bg-white px-4 py-2 text-sm font-semibold text-[#d95044] shadow-sm transition hover:bg-[#fff2ef]">
                                                        <i data-lucide="file-lock-2" class="h-4 w-4"></i>
                                                        <span>Certificados</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-5 client-col-notifications">
                                        <p class="font-semibold text-slate-900"><?= clients_h(clients_notif_summary($item)) ?></p>
                                        <p class="mt-2 text-xs text-slate-500">Avisos operativos configurados en Empresas.</p>
                                    </td>
                                    <td class="px-4 py-5 client-col-payments">
                                        <p class="font-semibold text-slate-900"><?= clients_h(clients_payment_summary($item, $snapshot)) ?></p>
                                        <p class="mt-2 text-xs text-slate-500">Base visible para deuda, suspension o vencimiento de certificado.</p>
                                    </td>
                                    <td class="px-4 py-5">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?= $habClass ?>"><?= clients_h($habLabel) ?></span>
                                    </td>
                                    <td class="px-4 py-5 client-col-operativo">
                                        <div class="space-y-1 text-sm font-semibold text-slate-900">
                                            <?php foreach (array_slice($services, 0, 7) as $service): ?>
                                                <p><?= clients_h($service) ?></p>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <button type="button" data-client-modal-url="<?= clients_h(app_url('index.php?action=client-show&id=' . $empresaId . '&embed=1')) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm transition hover:bg-indigo-700" title="Editar cliente">
                                                <i data-lucide="pen-square" class="h-4 w-4"></i>
                                            </button>
                                            <button type="button" data-client-modal-url="<?= clients_h(app_url('index.php?action=client-certificates&id=' . $empresaId . '&embed=1')) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[#e65b4f] bg-white text-[#d95044] shadow-sm transition hover:bg-[#fff2ef]" title="Certificados del cliente">
                                                <i data-lucide="file-lock-2" class="h-4 w-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr id="client-row-<?= $empresaId ?>" class="hidden bg-slate-50/80">
                                    <td colspan="7" class="px-6 pb-6">
                                        <div class="grid gap-5 lg:grid-cols-[1.15fr_0.85fr]">
                                            <div class="space-y-5">
                                                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                                                    <div class="border-b border-slate-200 px-5 py-4">
                                                        <h4 class="text-base font-bold text-slate-900">Datos de empresa y sucursal</h4>
                                                        <p class="mt-1 text-sm text-slate-500">Bloque unificado para reemplazar la lectura operativa de Mis Empresas sobre empresa real, contacto y sucursal.</p>
                                                    </div>
                                                    <div class="grid gap-4 p-5 md:grid-cols-2">
                                                        <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">RUT</p><p class="mt-2 text-sm font-medium text-slate-900"><?= clients_h(clients_detail_value($item['Rut'] ?? '')) ?></p></div>
                                                        <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Empresa Invoicy</p><p class="mt-2 text-sm font-medium text-slate-900"><?= clients_h(clients_detail_value($item['EmpresaInvoicy'] ?? '')) ?></p></div>
                                                        <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nombre fantasia</p><p class="mt-2 text-sm font-medium text-slate-900"><?= clients_h(clients_detail_value($item['NombreFantasia'] ?? '')) ?></p></div>
                                                        <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Usuarios</p><p class="mt-2 text-sm font-medium text-slate-900"><?= (int) ($item['UsuariosLicencia'] ?? 0) ?></p></div>
                                                        <div class="md:col-span-2"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Domicilio</p><p class="mt-2 text-sm font-medium text-slate-900"><?= clients_h(clients_detail_value($item['Domicilio'] ?? '')) ?></p><p class="mt-1 text-xs text-slate-500"><?= clients_h(trim(clients_detail_value($item['Ciudad'] ?? '') . ' | ' . clients_detail_value($item['Departamento'] ?? ''))) ?></p></div>
                                                        <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Email principal</p><p class="mt-2 text-sm font-medium text-slate-900"><?= clients_h(clients_detail_value(($item['ClienteEmail'] ?? '') !== '' ? $item['ClienteEmail'] : ($item['EmpresaEmail'] ?? ''))) ?></p></div>
                                                        <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Email envio FE</p><p class="mt-2 text-sm font-medium text-slate-900"><?= clients_h(clients_detail_value($item['emailEnvioFE'] ?? '')) ?></p></div>
                                                        <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Telefono</p><p class="mt-2 text-sm font-medium text-slate-900"><?= clients_h(clients_detail_value($item['Tel'] ?? '')) ?></p></div>
                                                        <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Firmante</p><p class="mt-2 text-sm font-medium text-slate-900"><?= clients_h(clients_detail_value($item['NombreCompletoFirmante'] ?? '')) ?></p></div>
                                                    </div>
                                                </div>

                                                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                                                    <div class="border-b border-slate-200 px-5 py-4">
                                                        <h4 class="text-base font-bold text-slate-900">Edicion propia del cliente</h4>
                                                        <p class="mt-1 text-sm text-slate-500">La ficha embebida sigue siendo la superficie de edicion directa sobre Empresas, Clientes, onboarding ligado y Migrate cuando corresponde.</p>
                                                    </div>
                                                    <div class="space-y-4 p-5">
                                                        <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-4 text-sm text-indigo-900">
                                                            <p class="font-semibold">Acceso directo a la ficha</p>
                                                            <p class="mt-1">Aqui se va consolidando el reemplazo del panel viejo sin depender de que exista un caso de onboarding activo.</p>
                                                        </div>
                                                        <div class="flex flex-wrap gap-2">
                                                            <button type="button" data-client-modal-url="<?= clients_h(app_url('index.php?action=client-show&id=' . $empresaId . '&embed=1')) ?>" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                                                                <i data-lucide="pen-square" class="h-4 w-4"></i>
                                                                <span>Abrir ficha del cliente</span>
                                                            </button>
                                                            <button type="button" data-client-modal-url="<?= clients_h(app_url('index.php?action=client-certificates&id=' . $empresaId . '&embed=1')) ?>" class="inline-flex items-center gap-2 rounded-xl border border-[#e65b4f] bg-white px-4 py-2.5 text-sm font-semibold text-[#d95044] shadow-sm transition hover:bg-[#fff2ef]">
                                                                <i data-lucide="file-lock-2" class="h-4 w-4"></i>
                                                                <span>Ver certificados</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="space-y-5">
                                                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                                                    <div class="border-b border-slate-200 px-5 py-4">
                                                        <h4 class="text-base font-bold text-slate-900">Certificados e historial visible</h4>
                                                        <p class="mt-1 text-sm text-slate-500">Base para el boton futuro de historial, ultimo certificado montado y datos del certificado digital.</p>
                                                    </div>
                                                    <div class="p-5 space-y-4">
                                                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                                            <div class="grid gap-3 md:grid-cols-2">
                                                                <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado certificado</p><p class="mt-2 text-sm font-semibold text-slate-900"><?= clients_h($certLabel) ?></p></div>
                                                                <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Vence</p><p class="mt-2 text-sm font-semibold text-slate-900"><?= clients_h(clients_detail_value($snapshot['CerFchVencimiento'] ?? '')) ?></p></div>
                                                                <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Ultima consulta</p><p class="mt-2 text-sm font-semibold text-slate-900"><?= clients_h(clients_format_datetime((string) ($snapshot['FechaConsulta'] ?? ''))) ?></p></div>
                                                                <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Mensaje WS</p><p class="mt-2 text-sm font-semibold text-slate-900"><?= clients_h(clients_detail_value($snapshot['MsgDesc'] ?? '')) ?></p></div>
                                                            </div>
                                                        </div>
                                                        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-4 text-sm text-slate-600">
                                                            Consulte el detalle disponible del certificado, su ultima informacion registrada y el acceso directo a la ficha correspondiente del cliente.
                                                        </div>
                                                        <div class="space-y-3">
                                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Ultimas acciones visibles</p>
                                                            <?php if ($actions === []): ?>
                                                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">Todavia no hay acciones visibles asociadas a este cliente.</div>
                                                            <?php else: ?>
                                                                <?php foreach ($actions as $action): ?>
                                                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                                                        <div class="flex items-start justify-between gap-3">
                                                                            <div>
                                                                                <p class="text-sm font-semibold text-slate-900"><?= clients_h(clients_action_label((string) ($action['Accion'] ?? ''))) ?></p>
                                                                                <p class="mt-1 text-sm text-slate-600"><?= clients_h((string) ($action['Descripcion'] ?? '')) ?></p>
                                                                            </div>
                                                                            <span class="text-xs font-medium text-slate-500"><?= clients_h(clients_format_datetime((string) ($action['FechaAccion'] ?? ''))) ?></span>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                                                    <div class="border-b border-slate-200 px-5 py-4">
                                                        <h4 class="text-base font-bold text-slate-900">Onboarding relacionado</h4>
                                                        <p class="mt-1 text-sm text-slate-500">Ultimo caso temporal encontrado por RUT.</p>
                                                    </div>
                                                    <div class="p-5 space-y-3 text-sm">
                                                        <?php if (is_array($onboarding)): ?>
                                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado</p>
                                                                <p class="mt-2 font-semibold text-slate-900"><?= clients_h(clients_detail_value($onboarding['estado_detalle'] ?? $onboarding['estado'] ?? '')) ?></p>
                                                            </div>
                                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Hito actual</p>
                                                                <p class="mt-2 font-semibold text-slate-900"><?= clients_h(clients_detail_value($onboarding['hito_actual'] ?? '')) ?></p>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-slate-500">No se encontro un registro reciente de onboarding para este RUT.</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 bg-slate-50/80 px-6 py-4 grid gap-4 xl:grid-cols-[1fr_auto_1fr] xl:items-center">
                    <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
                        <span>Ir a</span>
                        <form method="get" action="<?= clients_h(app_url('index.php')) ?>" class="flex items-center gap-2">
                            <?php foreach ($queryState as $key => $value): ?>
                                <?php if ($key === 'page' || $value === '' || $value === null): continue; endif; ?>
                                <input type="hidden" name="<?= clients_h($key) ?>" value="<?= clients_h((string) $value) ?>">
                            <?php endforeach; ?>
                            <input type="hidden" name="route" value="clientes">
                            <input type="number" name="page" min="1" max="<?= (int) ($pagination['total_pages'] ?? 1) ?>" value="<?= (int) ($pagination['page'] ?? 1) ?>" class="w-20 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-900">
                            <button type="submit" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Ver</button>
                        </form>
                        <span class="text-slate-400">|</span>
                        <span>[ <?= (((int) ($pagination['page'] ?? 1) - 1) * (int) ($pagination['per_page'] ?? 25)) + (count($items) > 0 ? 1 : 0) ?> a <?= min((int) ($pagination['total_items'] ?? 0), (((int) ($pagination['page'] ?? 1) - 1) * (int) ($pagination['per_page'] ?? 25)) + count($items)) ?> de <?= (int) ($pagination['total_items'] ?? 0) ?> ]</span>
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <?php
                        $queryPrev = http_build_query($queryState + ['page' => (int) ($pagination['prev_page'] ?? 1)]);
                        $queryNext = http_build_query($queryState + ['page' => (int) ($pagination['next_page'] ?? 1)]);
                        $pageLinks = clients_pagination_pages((int) ($pagination['page'] ?? 1), (int) ($pagination['total_pages'] ?? 1));
                        ?>
                        <a href="<?= clients_h(app_url('index.php?' . $queryPrev)) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-sm font-semibold text-slate-700 transition hover:bg-slate-100 <?= empty($pagination['has_prev']) ? 'pointer-events-none opacity-40' : '' ?>">
                            <i data-lucide="chevron-left" class="h-4 w-4"></i>
                        </a>
                        <?php if ((int) ($pagination['page'] ?? 1) > 3): ?>
                            <a href="<?= clients_h(app_url('index.php?' . http_build_query($queryState + ['page' => 1]))) ?>" class="inline-flex h-10 min-w-10 items-center justify-center rounded-full border border-slate-300 bg-white px-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100">1</a>
                            <span class="px-1 text-sm font-semibold text-slate-400">...</span>
                        <?php endif; ?>
                        <?php foreach ($pageLinks as $pageNumber): ?>
                            <?php $isCurrentPage = (int) ($pagination['page'] ?? 1) === (int) $pageNumber; ?>
                            <a href="<?= clients_h(app_url('index.php?' . http_build_query($queryState + ['page' => $pageNumber]))) ?>" class="inline-flex h-10 min-w-10 items-center justify-center rounded-full px-3 text-sm font-bold transition <?= $isCurrentPage ? 'client-theme-accent' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-100' ?>">
                                <?= (int) $pageNumber ?>
                            </a>
                        <?php endforeach; ?>
                        <?php if ((int) ($pagination['page'] ?? 1) < ((int) ($pagination['total_pages'] ?? 1) - 2)): ?>
                            <span class="px-1 text-sm font-semibold text-slate-400">...</span>
                            <a href="<?= clients_h(app_url('index.php?' . http_build_query($queryState + ['page' => (int) ($pagination['total_pages'] ?? 1)]))) ?>" class="inline-flex h-10 min-w-10 items-center justify-center rounded-full border border-slate-300 bg-white px-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100"><?= (int) ($pagination['total_pages'] ?? 1) ?></a>
                        <?php endif; ?>
                        <a href="<?= clients_h(app_url('index.php?' . $queryNext)) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-sm font-semibold text-slate-700 transition hover:bg-slate-50 <?= empty($pagination['has_next']) ? 'pointer-events-none opacity-40' : '' ?>">
                            <i data-lucide="chevron-right" class="h-4 w-4"></i>
                        </a>
                    </div>
                    <div class="flex flex-wrap items-center justify-start gap-3 text-sm text-slate-600 xl:justify-end">
                        <span>Mostrando <?= count($items) ?> fila(s)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="client-modal-overlay" class="fixed inset-0 z-[90] hidden bg-slate-950/55 px-4 py-6 backdrop-blur-sm">
    <div class="mx-auto flex h-full max-w-7xl flex-col overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Ficha del cliente</p>
                <h3 class="mt-1 text-lg font-bold text-slate-900">Datos del cliente</h3>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" id="client-modal-close" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                    <i data-lucide="x" class="h-4 w-4"></i>
                    <span>Cerrar</span>
                </button>
            </div>
        </div>
        <div class="flex-1 bg-slate-100">
            <iframe id="client-modal-frame" title="Ficha del cliente" class="h-full w-full border-0 bg-white" src="about:blank"></iframe>
        </div>
    </div>
</div>

<div id="logo-modal-overlay" class="fixed inset-0 z-[95] hidden bg-slate-950/70 px-4 py-6 backdrop-blur-sm">
    <div class="mx-auto flex h-full max-w-4xl flex-col overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Logo ampliado</p>
                <h3 id="logo-modal-title" class="mt-1 text-lg font-bold text-slate-900">Empresa</h3>
            </div>
            <button type="button" id="logo-modal-close" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                <i data-lucide="x" class="h-4 w-4"></i>
                <span>Cerrar</span>
            </button>
        </div>
        <div class="flex flex-1 items-center justify-center bg-slate-100 p-8">
            <img id="logo-modal-image" src="" alt="Logo ampliado" class="max-h-full max-w-full object-contain">
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sortSelect = document.getElementById('client-sort');
    var dirSelect = document.getElementById('client-dir');
    var statusSelect = document.getElementById('status');
    var perPageSelect = document.getElementById('per_page');
    var filterForm = document.getElementById('client-filter-form');
    var modalOverlay = document.getElementById('client-modal-overlay');
    var modalFrame = document.getElementById('client-modal-frame');
    var modalClose = document.getElementById('client-modal-close');
    var logoModalOverlay = document.getElementById('logo-modal-overlay');
    var logoModalImage = document.getElementById('logo-modal-image');
    var logoModalTitle = document.getElementById('logo-modal-title');
    var logoModalClose = document.getElementById('logo-modal-close');
    var filtersSidebar = document.getElementById('client-filters-sidebar');
    var filtersLayout = document.getElementById('client-panel-layout');
    var filtersSidebarToggle = document.getElementById('client-filters-sidebar-toggle');

    document.querySelectorAll('[data-client-filter-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var card = button.closest('.client-filter-card');
            if (!card) {
                return;
            }

            var collapsed = card.classList.toggle('is-collapsed');
            button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            var icon = button.querySelector('svg');
            if (icon) {
                icon.setAttribute('data-lucide', collapsed ? 'chevron-down' : 'chevron-up');
            }
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    });

    if (filtersSidebarToggle && filtersSidebar && filtersLayout) {
        filtersSidebarToggle.addEventListener('click', function () {
            var collapsed = filtersSidebar.classList.toggle('is-collapsed');
            filtersLayout.classList.toggle('filters-collapsed', collapsed);
            filtersSidebarToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            var icon = filtersSidebarToggle.querySelector('svg');
            if (icon) {
                icon.setAttribute('data-lucide', collapsed ? 'panel-left-open' : 'panel-left-close');
            }
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    }

    document.querySelectorAll('[data-collapse-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var targetId = button.getAttribute('data-collapse-toggle');
            if (!targetId) {
                return;
            }

            var target = document.getElementById(targetId);
            if (!target) {
                return;
            }

            target.classList.toggle('hidden');
        });
    });

    function closeClientModal() {
        if (!modalOverlay || !modalFrame) {
            return;
        }

        modalOverlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        modalFrame.src = 'about:blank';
    }

    function openClientModal(url) {
        if (!modalOverlay || !modalFrame || !url) {
            return;
        }

        modalFrame.src = url;
        modalOverlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    document.querySelectorAll('[data-client-modal-url]').forEach(function (button) {
        button.addEventListener('click', function () {
            openClientModal(button.getAttribute('data-client-modal-url'));
        });
    });

    document.querySelectorAll('[data-logo-modal-src]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!logoModalOverlay || !logoModalImage) {
                return;
            }

            logoModalImage.src = button.getAttribute('data-logo-modal-src') || '';
            if (logoModalTitle) {
                logoModalTitle.textContent = button.getAttribute('data-logo-modal-title') || 'Empresa';
            }
            logoModalOverlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });
    });

    if (modalClose) {
        modalClose.addEventListener('click', closeClientModal);
    }

    function closeLogoModal() {
        if (!logoModalOverlay || !logoModalImage) {
            return;
        }

        logoModalOverlay.classList.add('hidden');
        logoModalImage.src = '';
        document.body.classList.remove('overflow-hidden');
    }

    if (logoModalClose) {
        logoModalClose.addEventListener('click', closeLogoModal);
    }

    if (modalOverlay) {
        modalOverlay.addEventListener('click', function (event) {
            if (event.target === modalOverlay) {
                closeClientModal();
            }
        });
    }

    if (logoModalOverlay) {
        logoModalOverlay.addEventListener('click', function (event) {
            if (event.target === logoModalOverlay) {
                closeLogoModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modalOverlay && !modalOverlay.classList.contains('hidden')) {
            closeClientModal();
        }
        if (event.key === 'Escape' && logoModalOverlay && !logoModalOverlay.classList.contains('hidden')) {
            closeLogoModal();
        }
    });

    window.addEventListener('message', function (event) {
        var payload = event.data || {};
        if (!payload || payload.type !== 'client-panel-saved') {
            return;
        }

        closeClientModal();
        window.location.reload();
    });

    if (sortSelect && dirSelect && filterForm) {
        sortSelect.addEventListener('change', function () {
            filterForm.submit();
        });
        dirSelect.addEventListener('change', function () {
            filterForm.submit();
        });
    }

    if (statusSelect && filterForm) {
        statusSelect.addEventListener('change', function () {
            filterForm.submit();
        });
    }

    if (perPageSelect && filterForm) {
        perPageSelect.addEventListener('change', function () {
            filterForm.submit();
        });
    }
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
