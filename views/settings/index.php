<?php
declare(strict_types=1);
$enableCreateModal = false;
$activeNav = 'configuracion';
$pageSubtitle = 'Parametros operativos del modulo que pueden cambiarse sin tocar base de datos.';
require __DIR__ . '/../layout/header.php';
$referenceCompanies = is_array($settings['reference_companies'] ?? null) ? $settings['reference_companies'] : [];
$testingReference = is_array($referenceCompanies['testing'] ?? null) ? $referenceCompanies['testing'] : [];
$productionReference = is_array($referenceCompanies['production'] ?? null) ? $referenceCompanies['production'] : [];
?>

<section class="space-y-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold uppercase tracking-wider">Configuracion</span>
            <h2 class="mt-3 text-2xl font-bold text-slate-900 tracking-tight">Parametros del modulo</h2>
            <p class="mt-2 text-sm text-slate-500 max-w-3xl">Aqui puede ajustar valores operativos que cambian con poca frecuencia y deben persistir en archivo.</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= htmlspecialchars(app_url('panel'), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-4 py-2.5 rounded-lg shadow-sm transition duration-150 text-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Volver al panel</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <h3 class="font-bold text-slate-900 text-base">Parametros de negocio e integracion</h3>
                <p class="text-sm text-slate-500 mt-1">Aqui se definen los valores operativos persistidos en <code>config/runtime.php</code>.</p>
            </div>
            <form method="post" action="<?= htmlspecialchars(app_url('index.php?action=save-settings'), ENT_QUOTES, 'UTF-8') ?>" class="p-6 space-y-6" data-busy-text="Espere un momento, por favor. Estamos guardando la configuracion.">
                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="max-w-sm">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="monto_credito_fiscal_anual">Monto credito fiscal anual</label>
                        <input id="monto_credito_fiscal_anual" type="number" step="0.01" min="0" name="monto_credito_fiscal_anual" value="<?= htmlspecialchars(number_format((float) ($settings['monto_credito_fiscal_anual'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                        <p class="mt-1 text-[11px] text-slate-500">Ejemplo: <code>514.00</code></p>
                    </div>

                    <div class="max-w-sm">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="migrate_environment">Entorno Migrate</label>
                        <select id="migrate_environment" name="migrate_environment" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                            <option value="testing" <?= ($settings['migrate_environment'] ?? '') === 'testing' ? 'selected' : '' ?>>Testing</option>
                            <option value="production" <?= ($settings['migrate_environment'] ?? '') === 'production' ? 'selected' : '' ?>>Produccion</option>
                        </select>
                        <p class="mt-1 text-[11px] text-slate-500">Define tanto el alta de empresa como la consulta de certificados.</p>
                    </div>

                    <div class="max-w-sm">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="migrate_cert_base_code">EmpresaInvoicy</label>
                        <input id="migrate_cert_base_code" type="text" value="<?= htmlspecialchars((string) ($settings['master_empresa_invoicy'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-700">
                        <p class="mt-1 text-[11px] text-slate-500" id="migrate_cert_base_code_help">Corresponde a la empresa <?= htmlspecialchars((string) ($settings['master_empresa_id'] ?? 397), ENT_QUOTES, 'UTF-8') ?> segun el entorno seleccionado.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="migrate_cert_base_key">Clave certificados</label>
                        <input id="migrate_cert_base_key" type="text" value="<?= htmlspecialchars((string) ($settings['master_empresa_clave'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-700 font-mono">
                        <p class="mt-1 text-[11px] text-slate-500">Clave tomada desde la tabla <code>Empresas</code> para el mismo entorno activo.</p>
                    </div>

                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">WSDL alta empresas</p>
                        <p class="mt-2 break-all text-sm text-slate-700 font-mono"><?= htmlspecialchars((string) ($settings['migrate_registroempresa_wsdl'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">WSDL consulta certificados</p>
                        <p class="mt-2 break-all text-sm text-slate-700 font-mono"><?= htmlspecialchars((string) ($settings['migrate_consultaempresas_wsdl'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>

                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    La consulta de certificados por empresas activas usa el <code>EmpresaInvoicy</code> y la <code>Clave</code> de cada empresa.
                    En esta pantalla se muestra la referencia base del entorno activo para pruebas puntuales y validaciones operativas.
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="<?= htmlspecialchars(app_url('panel'), ENT_QUOTES, 'UTF-8') ?>" class="bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold px-5 py-2.5 rounded-lg transition text-sm">Cancelar</a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2.5 rounded-lg transition text-sm shadow-md flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Guardar configuracion</span>
                    </button>
                </div>
            </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var environmentField = document.getElementById('migrate_environment');
    var empresaField = document.getElementById('migrate_cert_base_code');
    var claveField = document.getElementById('migrate_cert_base_key');
    var helpField = document.getElementById('migrate_cert_base_code_help');
    var references = {
        testing: {
            id: <?= json_encode((int) ($testingReference['id'] ?? 1)) ?>,
            empresa_invoicy: <?= json_encode((string) ($testingReference['empresa_invoicy'] ?? '')) ?>,
            clave: <?= json_encode((string) ($testingReference['clave'] ?? '')) ?>
        },
        production: {
            id: <?= json_encode((int) ($productionReference['id'] ?? 397)) ?>,
            empresa_invoicy: <?= json_encode((string) ($productionReference['empresa_invoicy'] ?? '')) ?>,
            clave: <?= json_encode((string) ($productionReference['clave'] ?? '')) ?>
        }
    };

    function syncReferenceFields() {
        if (!environmentField || !empresaField || !claveField || !helpField) {
            return;
        }

        var selected = environmentField.value === 'production' ? 'production' : 'testing';
        var reference = references[selected] || references.production;
        empresaField.value = reference.empresa_invoicy || '';
        claveField.value = reference.clave || '';
        helpField.textContent = 'Corresponde a la empresa ' + String(reference.id || '') + ' segun el entorno seleccionado.';
    }

    if (environmentField) {
        environmentField.addEventListener('change', syncReferenceFields);
        syncReferenceFields();
    }
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
