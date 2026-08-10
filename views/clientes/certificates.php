<?php
declare(strict_types=1);

function client_cert_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function client_cert_value($value, string $fallback = 'Sin dato'): string
{
    $text = trim((string) $value);
    return $text !== '' ? $text : $fallback;
}

function client_cert_date(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return 'Sin fecha';
    }

    try {
        return (new DateTimeImmutable($value))->format('Y/m/d');
    } catch (Throwable $e) {
        return $value;
    }
}

function client_cert_datetime(?string $value): string
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

function client_cert_alias_candidate($value): string
{
    $text = trim((string) $value);
    if ($text === '' || $text === '0' || $text === '0000') {
        return '';
    }

    return $text;
}

function client_cert_file_alias(array $file): string
{
    $name = trim((string) (($file['nombre_original'] ?? '') !== '' ? $file['nombre_original'] : ($file['nombre_guardado'] ?? '')));
    if ($name === '') {
        return '';
    }

    $extension = pathinfo($name, PATHINFO_EXTENSION);
    if ($extension !== '') {
        $name = substr($name, 0, -1 * (strlen($extension) + 1));
    }

    return trim($name);
}

$enableCreateModal = false;
$activeNav = 'clientes';
$embeddedView = isset($_GET['embed']) && $_GET['embed'] === '1';
$pageSubtitle = 'Certificado digital y notificaciones del cliente.';
$item = $context['item'];
$snapshot = $context['snapshot'] ?? null;
$certificateHistory = is_array($context['certificate_history'] ?? null) ? $context['certificate_history'] : [];
$certificateNotifications = is_array($context['certificate_notifications'] ?? null) ? $context['certificate_notifications'] : [];
$certificateFiles = is_array($context['certificate_files'] ?? null) ? $context['certificate_files'] : [];
$onboarding = is_array($context['onboarding'] ?? null) ? $context['onboarding'] : [];
$latestHistory = $certificateHistory[0] ?? null;
$currentFile = $certificateFiles[0] ?? null;
foreach ($certificateFiles as $candidate) {
    if (!is_array($candidate)) {
        continue;
    }

    if (!is_array($currentFile)) {
        $currentFile = $candidate;
        continue;
    }

    $currentStamp = strtotime((string) ($currentFile['fecha_subida'] ?? '')) ?: 0;
    $candidateStamp = strtotime((string) ($candidate['fecha_subida'] ?? '')) ?: 0;
    if ($candidateStamp >= $currentStamp) {
        $currentFile = $candidate;
    }
}

$downloadUrl = '';
if (is_array($latestHistory)) {
    $path = trim((string) ($latestHistory['RutaArchivo'] ?? ''));
    foreach ($certificateFiles as $candidate) {
        if (trim((string) ($candidate['ruta_archivo'] ?? '')) === $path && trim((string) ($candidate['download_url'] ?? '')) !== '') {
            $downloadUrl = (string) $candidate['download_url'];
            break;
        }
    }
    if ($downloadUrl === '' && (int) ($latestHistory['NuevaEmpresaId'] ?? 0) > 0) {
        foreach (($context['files'] ?? []) as $candidate) {
            if (trim((string) ($candidate['ruta_archivo'] ?? '')) === $path && trim((string) ($candidate['download_url'] ?? '')) !== '') {
                $downloadUrl = (string) $candidate['download_url'];
                break;
            }
        }
    }
}
if ($downloadUrl === '' && is_array($currentFile) && trim((string) ($currentFile['download_url'] ?? '')) !== '') {
    $downloadUrl = (string) $currentFile['download_url'];
}

$currentAlias = client_cert_alias_candidate($latestHistory['AliasCertificado'] ?? '');
if ($currentAlias === '') {
    $currentAlias = client_cert_alias_candidate($snapshot['Apodo'] ?? '');
}
if ($currentAlias === '' && is_array($currentFile)) {
    $currentAlias = client_cert_file_alias($currentFile);
}

$currentOrigin = trim((string) ($latestHistory['OrigenCarga'] ?? ''));
if ($currentOrigin === '' && is_array($currentFile)) {
    $currentOrigin = 'ONBOARDING';
}

$currentPassword = trim((string) ($latestHistory['PasswordCertificado'] ?? ''));
if ($currentPassword === '' && !empty($onboarding['certificado_contrasena'])) {
    $currentPassword = trim((string) $onboarding['certificado_contrasena']);
}

$currentLoadDate = trim((string) ($latestHistory['FechaCarga'] ?? ''));
if ($currentLoadDate === '' && is_array($currentFile)) {
    $currentLoadDate = trim((string) ($currentFile['fecha_subida'] ?? ''));
}

$currentExpiry = trim((string) ($latestHistory['FechaVencimiento'] ?? ($snapshot['CerFchVencimiento'] ?? '')));
$currentDaysRaw = $latestHistory['DiasRestantes'] ?? ($snapshot['DiasRestantes'] ?? '');
$currentDays = ($currentDaysRaw !== '' && $currentDaysRaw !== null) ? (string) $currentDaysRaw : 'Sin dato';

require __DIR__ . '/../layout/header.php';
?>

<section class="space-y-5">
    <div class="rounded-[28px] border border-[#f0d9d3] bg-white p-6 shadow-[0_22px_60px_-34px_rgba(230,91,79,0.45)]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="inline-flex items-center rounded-full bg-[#fff0ec] px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-[#cc4f44]">Cliente activo</span>
                <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900"><?= client_cert_h((string) ($item['RazonSocial'] ?? 'Cliente')) ?></h2>
                <p class="mt-2 text-sm text-slate-500">RUT <?= client_cert_h((string) ($item['Rut'] ?? '')) ?> · EmpCodigo <?= client_cert_h(client_cert_value((string) ($item['EmpresaInvoicy'] ?? ''))) ?></p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:w-[420px]">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Certificado actual</p>
                    <p class="mt-1 text-sm font-bold text-slate-900"><?= client_cert_h(client_cert_value($currentAlias)) ?></p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Vence</p>
                    <p class="mt-1 text-sm font-bold text-slate-900"><?= client_cert_h(client_cert_date($currentExpiry)) ?></p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Dias restantes</p>
                    <p class="mt-1 text-sm font-bold text-slate-900"><?= client_cert_h($currentDays) ?></p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Ultimo estado</p>
                    <p class="mt-1 text-sm font-bold text-slate-900"><?= client_cert_h(client_cert_value((string) ($snapshot['MsgDesc'] ?? ''))) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-gradient-to-r from-[#fff2ef] to-white px-6 py-4">
            <div class="flex flex-wrap gap-2">
                <button type="button" data-cert-tab="current" class="client-cert-tab is-active inline-flex items-center rounded-2xl bg-[#e65b4f] px-4 py-2 text-sm font-semibold text-white">Certificado digital</button>
                <button type="button" data-cert-tab="notifications" class="client-cert-tab inline-flex items-center rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Historial de notificaciones</button>
            </div>
        </div>

        <div id="client-cert-tab-current" class="client-cert-panel space-y-5 p-6">
            <div class="grid gap-5 xl:grid-cols-[0.92fr_1.08fr]">
                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Resumen actual</p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Alias</p>
                                <p class="mt-1 text-sm font-bold text-slate-900"><?= client_cert_h(client_cert_value($currentAlias)) ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Origen</p>
                                <p class="mt-1 text-sm font-bold text-slate-900"><?= client_cert_h(client_cert_value($currentOrigin)) ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Contrasena</p>
                                <p class="mt-1 text-sm font-bold text-slate-900 break-all"><?= client_cert_h(client_cert_value($currentPassword, 'Sin contrasena registrada')) ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Fecha de carga</p>
                                <p class="mt-1 text-sm font-bold text-slate-900"><?= client_cert_h(client_cert_datetime($currentLoadDate)) ?></p>
                            </div>
                        </div>
                        <?php if ($downloadUrl !== ''): ?>
                            <a href="<?= client_cert_h($downloadUrl) ?>" class="mt-4 inline-flex items-center rounded-2xl bg-[#e65b4f] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#d95044]">Descargar certificado actual</a>
                        <?php endif; ?>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Snapshot Migrate</p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado WS</p>
                                <p class="mt-1 text-sm font-bold text-slate-900"><?= client_cert_h(client_cert_value((string) ($snapshot['MsgDesc'] ?? ''))) ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Consulta</p>
                                <p class="mt-1 text-sm font-bold text-slate-900"><?= client_cert_h(client_cert_datetime((string) ($snapshot['FechaConsulta'] ?? ''))) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Historial de certificados</p>
                    <?php if ($certificateHistory === []): ?>
                        <?php if (is_array($currentFile)): ?>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900"><?= client_cert_h(client_cert_value((string) ($currentFile['nombre_original'] ?? 'Archivo certificado'))) ?></p>
                                        <p class="mt-1 text-xs text-slate-500">Archivo disponible en el expediente del cliente. <?= client_cert_h(client_cert_datetime((string) ($currentFile['fecha_subida'] ?? ''))) ?></p>
                                    </div>
                                    <?php if ($downloadUrl !== ''): ?>
                                        <a href="<?= client_cert_h($downloadUrl) ?>" class="inline-flex shrink-0 items-center rounded-2xl border border-[#e65b4f] px-4 py-2 text-sm font-semibold text-[#cc4f44] transition hover:bg-[#fff2ef]">Descargar</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-500">Aun no hay historial centralizado de certificados para este cliente.</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php foreach ($certificateHistory as $row): ?>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900"><?= client_cert_h(client_cert_value((string) ($row['NombreOriginal'] ?? 'Archivo certificado'))) ?></p>
                                        <p class="mt-1 text-xs text-slate-500"><?= client_cert_h(client_cert_value((string) ($row['OrigenCarga'] ?? ''))) ?> · <?= client_cert_h(client_cert_datetime((string) ($row['FechaCarga'] ?? ''))) ?></p>
                                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                            <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Alias</p><p class="mt-1 text-sm text-slate-900"><?= client_cert_h(client_cert_value((string) ($row['AliasCertificado'] ?? ''))) ?></p></div>
                                            <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Vencimiento</p><p class="mt-1 text-sm text-slate-900"><?= client_cert_h(client_cert_date((string) ($row['FechaVencimiento'] ?? ''))) ?></p></div>
                                            <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Contrasena</p><p class="mt-1 text-sm text-slate-900 break-all"><?= client_cert_h(client_cert_value((string) ($row['PasswordCertificado'] ?? ''), 'Sin dato')) ?></p></div>
                                            <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado</p><p class="mt-1 text-sm text-slate-900"><?= client_cert_h(client_cert_value((string) ($row['EstadoCarga'] ?? ''))) ?></p></div>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"><?= client_cert_h((string) (($row['DiasRestantes'] ?? null) !== null ? ((int) $row['DiasRestantes'] . ' dias') : 'N/D')) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="client-cert-tab-notifications" class="client-cert-panel hidden space-y-3 p-6">
            <?php if ($certificateNotifications === []): ?>
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-500">Aun no hay notificaciones historicas registradas para este cliente.</div>
            <?php else: ?>
                <?php foreach ($certificateNotifications as $row): ?>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900"><?= client_cert_h(client_cert_value((string) ($row['TipoNotificacion'] ?? 'Notificacion'))) ?></p>
                                <p class="mt-1 text-sm text-slate-600"><?= client_cert_h(client_cert_value((string) ($row['Detalle'] ?? ''))) ?></p>
                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Destinatarios</p><p class="mt-1 text-sm text-slate-900 break-all"><?= client_cert_h(client_cert_value((string) ($row['Destinatarios'] ?? ''))) ?></p></div>
                                    <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Copias</p><p class="mt-1 text-sm text-slate-900 break-all"><?= client_cert_h(client_cert_value((string) ($row['Copias'] ?? ''))) ?></p></div>
                                    <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Vencimiento</p><p class="mt-1 text-sm text-slate-900"><?= client_cert_h(client_cert_date((string) ($row['FechaVencimiento'] ?? ''))) ?></p></div>
                                    <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Dias objetivo / restantes</p><p class="mt-1 text-sm text-slate-900"><?= client_cert_h((string) ((int) ($row['DiasObjetivo'] ?? 0) . ' / ' . (int) ($row['DiasRestantes'] ?? 0))) ?></p></div>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"><?= client_cert_h(client_cert_value((string) ($row['Estado'] ?? ''))) ?></span>
                                <p class="mt-2 text-xs text-slate-500"><?= client_cert_h(client_cert_datetime((string) ($row['FechaEnvio'] ?? ''))) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tabs = document.querySelectorAll('[data-cert-tab]');
    var panels = document.querySelectorAll('.client-cert-panel');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var target = tab.getAttribute('data-cert-tab');
            tabs.forEach(function (button) {
                button.classList.remove('is-active', 'bg-[#e65b4f]', 'text-white');
                button.classList.add('border', 'border-slate-300', 'bg-white', 'text-slate-700');
            });
            panels.forEach(function (panel) {
                panel.classList.add('hidden');
            });

            tab.classList.add('is-active', 'bg-[#e65b4f]', 'text-white');
            tab.classList.remove('border', 'border-slate-300', 'bg-white', 'text-slate-700');

            var panel = document.getElementById('client-cert-tab-' + target);
            if (panel) {
                panel.classList.remove('hidden');
            }
        });
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
