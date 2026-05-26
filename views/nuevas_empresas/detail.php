<?php declare(strict_types=1); $enableCreateModal = false; require __DIR__ . '/../layout/header.php'; ?>
<?php $values = $item; ?>
<?php
$estado = (string) ($item['estado'] ?? '');
$badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
$hitoTexto = 'Aprobacion pendiente';
$hitoClass = 'bg-indigo-50 border-indigo-200 text-indigo-700';
if ($estado === ESTADO_APROBADO) {
    $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
    $hitoTexto = 'Creacion base completada';
    $hitoClass = 'bg-emerald-50 border-emerald-200 text-emerald-700';
} elseif ($estado === ESTADO_ELIMINADO) {
    $badgeClass = 'bg-slate-100 text-slate-700 border-slate-300';
    $hitoTexto = 'Registro eliminado';
    $hitoClass = 'bg-slate-100 border-slate-200 text-slate-500';
} elseif ($estado === ESTADO_ERROR_APROBACION) {
    $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
    $hitoTexto = 'Error de aprobacion';
    $hitoClass = 'bg-rose-50 border-rose-200 text-rose-700';
}
?>

<section class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border <?= $badgeClass ?>">
                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                        <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border <?= $hitoClass ?>">
                        <i data-lucide="git-commit" class="w-3.5 h-3.5"></i>
                        <span><?= htmlspecialchars($hitoTexto, ENT_QUOTES, 'UTF-8') ?></span>
                    </span>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight"><?= htmlspecialchars((string) $item['razon_social'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="text-sm text-slate-500 mt-1">RUT: <?= htmlspecialchars((string) $item['rut'], ENT_QUOTES, 'UTF-8') ?> / Licencia: <?= htmlspecialchars((string) ($item['licencia_texto'] ?: $item['licencia']), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 max-w-4xl">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Fecha registro</p>
                        <p class="text-sm font-semibold text-slate-800 mt-1"><?= htmlspecialchars((string) $item['fecha_creacion'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Email principal</p>
                        <p class="text-sm font-semibold text-slate-800 mt-1"><?= htmlspecialchars((string) $item['email_principal'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Es emisor</p>
                        <p class="text-sm font-semibold text-slate-800 mt-1"><?= htmlspecialchars((string) ($item['alta_es_emisor'] ?? 'NO'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="index.php" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-4 py-2.5 rounded-lg shadow-sm transition duration-150 text-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Volver</span>
                </a>
                <button type="button" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition duration-150 text-sm" data-open-modal="modal-edit">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                    <span>Editar</span>
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[1.25fr,0.75fr] gap-6">
        <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <h3 class="font-bold text-slate-900 text-sm">Hoja de ruta del onboarding</h3>
                <p class="text-sm text-slate-500 mt-1">Resumen operativo del tramo inicial hasta la aprobacion manual.</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3 p-3 rounded-xl <?= $estado === ESTADO_PENDIENTE_APROBACION ? 'bg-indigo-50 border border-indigo-100' : 'bg-slate-50 border border-slate-200' ?>">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full <?= $estado === ESTADO_PENDIENTE_APROBACION ? 'bg-indigo-100 text-indigo-600' : 'bg-emerald-100 text-emerald-600' ?>">
                            <i data-lucide="<?= $estado === ESTADO_PENDIENTE_APROBACION ? 'shield' : 'check' ?>" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">1. Aprobacion pendiente</h4>
                            <p class="text-xs text-slate-500 mt-1">Validacion manual del registro y sus adjuntos.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 rounded-xl <?= $estado === ESTADO_APROBADO ? 'bg-emerald-50 border border-emerald-100' : 'bg-slate-50 border border-slate-200 opacity-75' ?>">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full <?= $estado === ESTADO_APROBADO ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' ?>">
                            <i data-lucide="<?= $estado === ESTADO_APROBADO ? 'check' : 'circle' ?>" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">2. Creacion base</h4>
                            <p class="text-xs text-slate-500 mt-1">Insercion en Empresas y Clientes bajo IdEmpresa 397.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Domicilio fiscal</p>
                        <p class="mt-2 text-slate-800 font-medium"><?= htmlspecialchars((string) $item['domicilio'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Certificado digital</p>
                        <p class="mt-2 text-slate-800 font-medium"><?= htmlspecialchars((string) ($item['alta_certificado_digital'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Codigo sucursal</p>
                        <p class="mt-2 text-slate-800 font-medium"><?= htmlspecialchars((string) ($item['suc_cod_sucursal'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Firmante</p>
                        <p class="mt-2 text-slate-800 font-medium"><?= htmlspecialchars((string) ($item['nombre_completo_firmante'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>

                <?php if (!empty($item['observaciones']) || !empty($item['notas_admin']) || !empty($item['error_proceso'])): ?>
                    <div class="space-y-3">
                        <?php if (!empty($item['observaciones'])): ?>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Observaciones</p>
                                <p class="mt-2 text-sm text-slate-700 whitespace-pre-line"><?= htmlspecialchars((string) $item['observaciones'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($item['notas_admin'])): ?>
                            <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                                <p class="text-[11px] uppercase tracking-wider text-indigo-500 font-semibold">Notas admin</p>
                                <p class="mt-2 text-sm text-indigo-900 whitespace-pre-line"><?= htmlspecialchars((string) $item['notas_admin'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($item['error_proceso'])): ?>
                            <div class="rounded-xl border border-rose-100 bg-rose-50 p-4">
                                <p class="text-[11px] uppercase tracking-wider text-rose-500 font-semibold">Error de proceso</p>
                                <p class="mt-2 text-sm text-rose-900 whitespace-pre-line"><?= htmlspecialchars((string) $item['error_proceso'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900 text-sm">Adjuntos</h3>
                </div>
                <div class="p-5 space-y-3">
                    <?php if (empty($archivos)): ?>
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-sm text-slate-500 text-center">Sin adjuntos registrados.</div>
                    <?php else: ?>
                        <?php foreach ($archivos as $archivo): ?>
                            <div class="rounded-xl border border-slate-200 p-4 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars((string) $archivo['tipo_archivo'], ENT_QUOTES, 'UTF-8') ?></p>
                                    <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars((string) $archivo['nombre_original'], ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold"><?= htmlspecialchars((string) pathinfo((string) $archivo['ruta_archivo'], PATHINFO_EXTENSION), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <?php if ($estado === ESTADO_PENDIENTE_APROBACION): ?>
                <section id="acciones" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h3 class="font-bold text-slate-900 text-sm">Acciones administrativas</h3>
                        <p class="text-sm text-slate-500 mt-1">Toda accion pide confirmacion antes de continuar.</p>
                    </div>
                    <div class="p-5 space-y-3">
                        <form method="post" action="index.php?action=approve&id=<?= (int) $item['id'] ?>" data-confirm="Esto creara registros reales en Empresas y Clientes.">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-3 rounded-xl transition shadow-sm">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                                <span>Aprobar alta</span>
                            </button>
                        </form>
                        <button type="button" class="w-full inline-flex items-center justify-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold px-4 py-3 rounded-xl border border-rose-200 transition" data-open-modal="modal-delete">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            <span>Eliminar registro</span>
                        </button>
                    </div>
                </section>
            <?php endif; ?>
        </aside>
    </div>
</section>

<div class="modal-shell" id="modal-edit" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-edit"></div>
    <div class="modal-panel modal-xl">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold uppercase tracking-wider">Edicion</span>
                <h3 class="mt-3 text-xl font-bold text-slate-900">Actualizar alta temporal</h3>
                <p class="mt-1 text-sm text-slate-500">Ajuste los datos del onboarding antes de aprobar el registro.</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition" data-close-modal="modal-edit" aria-label="Cerrar">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form method="post" action="index.php?action=update&id=<?= (int) $item['id'] ?>" class="space-y-6">
            <div class="bg-slate-50/70 rounded-2xl border border-slate-200 p-5">
                <div class="form-grid">
                    <?php $prefix = 'edit_'; require __DIR__ . '/_form_fields.php'; ?>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" data-close-modal="modal-edit" class="bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold px-5 py-2.5 rounded-lg transition text-sm">Cancelar</button>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-2.5 rounded-lg transition text-sm shadow-md flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Guardar cambios</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-shell" id="modal-delete" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-delete"></div>
    <div class="modal-panel modal-sm">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-semibold uppercase tracking-wider">Eliminar</span>
                <h3 class="mt-3 text-xl font-bold text-slate-900">Confirmar eliminacion</h3>
                <p class="mt-1 text-sm text-slate-500">Se borraran los adjuntos en disco y el registro quedara como eliminado.</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition" data-close-modal="modal-delete" aria-label="Cerrar">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form method="post" action="index.php?action=delete&id=<?= (int) $item['id'] ?>" data-confirm="Se eliminara el registro temporal y se borraran sus archivos del disco." class="space-y-5">
            <div>
                <label for="motivo_eliminacion" class="block text-xs font-semibold text-slate-600 mb-1.5">Motivo de eliminacion</label>
                <textarea id="motivo_eliminacion" name="motivo_eliminacion" rows="4" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 text-sm transition"></textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" data-close-modal="modal-delete" class="bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold px-5 py-2.5 rounded-lg transition text-sm">Cancelar</button>
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-6 py-2.5 rounded-lg transition text-sm shadow-md">Eliminar registro</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
