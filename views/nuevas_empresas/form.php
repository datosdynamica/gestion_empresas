<?php declare(strict_types=1); $enableCreateModal = false; require __DIR__ . '/../layout/header.php'; ?>
<?php $values = $_SESSION['old'] ?? []; unset($_SESSION['old']); ?>

<section class="space-y-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold uppercase tracking-wider">Fase 1</span>
            <h2 class="mt-3 text-2xl font-bold text-slate-900 tracking-tight">Nueva alta temporal</h2>
            <p class="mt-2 text-sm text-slate-500 max-w-3xl">Este flujo registra la empresa, adjunta documentacion y la deja lista para revision manual antes de crear registros en <strong>Empresas</strong> y <strong>Clientes</strong>.</p>
        </div>
        <div class="flex gap-2">
            <a href="index.php" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-4 py-2.5 rounded-lg shadow-sm transition duration-150 text-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Volver al listado</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-start gap-3">
            <div class="bg-indigo-100 text-indigo-700 rounded-xl p-2">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-base">Formulario de onboarding</h3>
                <p class="text-sm text-slate-500 mt-1">Use la misma estructura visual del panel de gestion. Los datos quedan en tabla temporal hasta su aprobacion manual.</p>
            </div>
        </div>

        <form method="post" action="index.php?action=store" enctype="multipart/form-data" class="space-y-6 p-6">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2 bg-slate-50/70 rounded-2xl border border-slate-200 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="bg-indigo-600 text-white font-bold text-xs px-2.5 py-1 rounded-full">1</span>
                        <h4 class="font-bold text-slate-800 text-sm">Datos identificativos e informacion comercial</h4>
                    </div>
                    <div class="form-grid">
                        <?php require __DIR__ . '/_form_fields.php'; ?>
                    </div>
                </div>

                <aside class="space-y-4">
                    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5">
                        <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                            Estado inicial
                        </h4>
                        <p class="text-sm text-slate-600 mt-2">Todo nuevo registro nace como <strong>PENDIENTE_APROBACION</strong>. Un administrador revisa el alta y decide si la aprueba o la elimina.</p>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-2xl p-5">
                        <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i data-lucide="folder-open" class="w-4 h-4 text-indigo-600"></i>
                            Adjuntos esperados
                        </h4>
                        <ul class="mt-3 space-y-2 text-sm text-slate-600">
                            <li class="flex items-center justify-between"><span>PFX</span><span class="text-rose-600 font-semibold">Obligatorio</span></li>
                            <li class="flex items-center justify-between"><span>Contrato</span><span class="text-rose-600 font-semibold">Obligatorio</span></li>
                            <li class="flex items-center justify-between"><span>6906</span><span class="text-rose-600 font-semibold">Obligatorio</span></li>
                            <li class="flex items-center justify-between"><span>Credito fiscal</span><span class="text-slate-400 font-semibold">Opcional</span></li>
                            <li class="flex items-center justify-between"><span>Logo</span><span class="text-slate-400 font-semibold">Opcional</span></li>
                        </ul>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-2xl p-5">
                        <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i data-lucide="workflow" class="w-4 h-4 text-indigo-600"></i>
                            Siguiente paso
                        </h4>
                        <p class="text-sm text-slate-600 mt-2">Al aprobar, el sistema creara la empresa en <strong>Empresas</strong> y el cliente en <strong>Clientes</strong> con <strong>IdEmpresa = 397</strong>.</p>
                    </div>
                </aside>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="index.php" class="bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold px-5 py-2.5 rounded-lg transition text-sm">Cancelar registro</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2.5 rounded-lg transition text-sm shadow-md flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Guardar e iniciar automatizacion</span>
                </button>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
