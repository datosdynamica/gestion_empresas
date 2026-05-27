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
<?php $jsVersion = @filemtime(__DIR__ . '/../../public/assets/js/app.js') ?: time(); ?>
<script src="public/assets/js/app.js?v=<?= $jsVersion ?>"></script>
<script>lucide.createIcons();</script>
</body>
</html>
