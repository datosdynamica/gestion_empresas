<?php declare(strict_types=1); ?>
        </main>
    </div>
</div>
<div class="confirm-overlay" id="confirm-overlay" hidden>
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden border border-slate-100">
        <div class="p-6">
            <div class="bg-rose-100 w-12 h-12 rounded-full flex items-center justify-center text-rose-600 mb-4">
                <i data-lucide="alert-octagon" class="w-6 h-6"></i>
            </div>
            <p class="text-lg font-bold text-slate-900">Confirmar accion</p>
            <p class="text-sm text-slate-500 mt-2" id="confirm-message">Esta accion requiere confirmacion.</p>
        </div>
        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
            <button type="button" class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-semibold transition" id="confirm-cancel">Cancelar</button>
            <button type="button" class="bg-rose-600 text-white hover:bg-rose-700 px-4 py-2 rounded-lg text-sm font-semibold transition shadow-md" id="confirm-accept">Continuar</button>
        </div>
    </div>
</div>
<div class="modal-shell" id="modal-install-help" aria-hidden="true">
    <div class="modal-backdrop"></div>
    <div class="modal-panel modal-sm">
        <div class="flex items-start justify-between gap-4 mb-5">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 text-xs font-semibold">Instalacion</span>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-3">Instale DYNAMICA ADMINISTRATIVO</h3>
                <p class="text-sm text-slate-500 mt-2">Puede usar la aplicacion como acceso directo en Android y como app de escritorio en Windows.</p>
            </div>
            <button type="button" class="w-10 h-10 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50" data-close-modal="modal-install-help" aria-label="Cerrar ayuda de instalacion">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="grid gap-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 inline-flex items-center justify-center">
                        <i data-lucide="smartphone" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">Android</p>
                        <p class="text-xs text-slate-500">Chrome o Edge</p>
                    </div>
                </div>
                <ol class="text-sm text-slate-600 space-y-1 pl-5 list-decimal">
                    <li>Abra el menu del navegador.</li>
                    <li>Seleccione <strong>Instalar app</strong> o <strong>Agregar a pantalla de inicio</strong>.</li>
                    <li>Confirme para dejar el acceso directo en el celular.</li>
                </ol>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 inline-flex items-center justify-center">
                        <i data-lucide="monitor-smartphone" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">Windows</p>
                        <p class="text-xs text-slate-500">Edge o Chrome</p>
                    </div>
                </div>
                <ol class="text-sm text-slate-600 space-y-1 pl-5 list-decimal">
                    <li>Busque el icono de instalacion en la barra de direcciones.</li>
                    <li>O abra el menu del navegador y elija <strong>Instalar esta aplicacion</strong>.</li>
                    <li>Confirme para tenerla como app independiente en Windows.</li>
                </ol>
            </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-3 sm:justify-end">
            <button type="button" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50" data-close-modal="modal-install-help">Cerrar</button>
            <button type="button" class="px-4 py-2.5 rounded-xl bg-[#e65b4f] text-white font-semibold shadow-md hover:bg-[#d95044]" id="install-app-button-modal">
                Intentar instalacion directa
            </button>
        </div>
    </div>
</div>
<div id="busy-overlay" class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-950/40 backdrop-blur-sm px-4">
    <div class="w-full max-w-sm rounded-2xl border border-white/20 bg-white p-6 shadow-2xl">
        <div class="flex items-center gap-4">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                <svg class="h-6 w-6 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" class="opacity-20" stroke="currentColor" stroke-width="3"></circle>
                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                </svg>
            </div>
            <div>
                <p class="text-base font-bold text-slate-900">Espere un momento</p>
                <p id="busy-overlay-message" class="mt-1 text-sm text-slate-500">Estamos gestionando la solicitud.</p>
            </div>
        </div>
    </div>
</div>
<?php $jsVersion = @filemtime(__DIR__ . '/../../public/assets/js/app.js') ?: time(); ?>
<script>
    window.APP_BASE_URL = <?= json_encode(rtrim(APP_BASE_URL, '/'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= htmlspecialchars(asset_url('public/assets/js/app.js?v=' . $jsVersion), ENT_QUOTES, 'UTF-8') ?>"></script>
<script>lucide.createIcons();</script>
</body>
</html>
