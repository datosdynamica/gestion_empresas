<?php declare(strict_types=1); $enableCreateModal = true; require __DIR__ . '/../layout/header.php'; ?>
<?php $values = $_SESSION['old'] ?? []; unset($_SESSION['old']); ?>

<section class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col lg:flex-row gap-3 items-center justify-between">
    <div class="relative w-full lg:w-96">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
            <i data-lucide="search" class="w-5 h-5"></i>
        </span>
        <input type="text" id="filtro-busqueda" placeholder="Buscar por Raz&oacute;n Social o RUT..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
    </div>
    <div class="flex flex-wrap gap-3 w-full lg:w-auto">
        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg text-sm w-full sm:w-auto">
            <span class="text-slate-500 font-medium text-xs uppercase tracking-wider">Estado:</span>
            <select id="filtro-estado" class="bg-transparent border-none focus:outline-none text-slate-700 font-semibold cursor-pointer">
                <option value="todos">Todos</option>
                <option value="PENDIENTE_APROBACION">Pendiente</option>
                <option value="APROBADO">Aprobado</option>
                <option value="ELIMINADO">Eliminado</option>
                <option value="ERROR_APROBACION">Error</option>
            </select>
        </div>
        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg text-sm w-full sm:w-auto">
            <span class="text-slate-500 font-medium text-xs uppercase tracking-wider">Hito actual:</span>
            <select id="filtro-hito" class="bg-transparent border-none focus:outline-none text-slate-700 font-semibold cursor-pointer">
                <option value="todos">Todos los hitos</option>
                <option value="Aprobacion pendiente">1. Aprobaci&oacute;n pendiente</option>
                <option value="Creacion base">2. Creaci&oacute;n base</option>
                <option value="Error de aprobacion">Error de aprobaci&oacute;n</option>
                <option value="Registro eliminado">Registro eliminado</option>
            </select>
        </div>
    </div>
</section>

<section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse table-gestiones">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs font-semibold tracking-wider uppercase">
                    <th class="py-4 px-5 w-12 text-center">Info</th>
                    <th class="py-4 px-4">Fecha Reg.</th>
                    <th class="py-4 px-4">Raz&oacute;n Social</th>
                    <th class="py-4 px-4">Estado General</th>
                    <th class="py-4 px-4">Licencia</th>
                    <th class="py-4 px-4 text-center">Es Emisor</th>
                    <th class="py-4 px-4">Hito / Acci&oacute;n Requerida</th>
                    <th class="py-4 px-6 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                <?php if (empty($items)): ?>
                    <tr><td colspan="8" class="px-6 py-8 text-center text-slate-500">No hay registros temporales.</td></tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $estado = (string) $item['estado'];
                        $badgeClass = 'bg-slate-100 text-slate-700 border-slate-300';
                        $estadoTexto = $estado;
                        $accionClass = 'bg-indigo-50 hover:bg-indigo-100 border-indigo-200 text-indigo-700';
                        $accionTexto = 'Aprobaci&oacute;n pendiente (Aprobar)';
                        $hitoActual = 'Aprobacion pendiente';
                        if ($estado === 'ELIMINADO') {
                            $badgeClass = 'bg-slate-100 text-slate-700 border-slate-300';
                            $accionClass = 'bg-slate-100 border-slate-200 text-slate-400';
                            $accionTexto = 'Registro eliminado';
                            $hitoActual = 'Registro eliminado';
                        } elseif ($estado === 'APROBADO') {
                            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                            $accionClass = 'bg-emerald-50 border-emerald-200 text-emerald-700';
                            $accionTexto = 'Alta base completada';
                            $hitoActual = 'Creacion base';
                        } elseif ($estado === 'ERROR_APROBACION') {
                            $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                            $accionClass = 'bg-rose-50 border-rose-200 text-rose-700';
                            $accionTexto = 'Revisar error';
                            $hitoActual = 'Error de aprobacion';
                        } else {
                            $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                        }
                        ?>
                        <tr class="hover:bg-slate-50/70 transition cursor-pointer row-registro" data-id="<?= (int) $item['id'] ?>" data-status="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>" data-hito="<?= htmlspecialchars($hitoActual, ENT_QUOTES, 'UTF-8') ?>" onclick="toggleFilaExpandida(<?= (int) $item['id'] ?>, event)">
                            <td class="py-4 px-5 text-center">
                                <button class="text-slate-400 hover:text-indigo-600 transition" id="arrow-<?= (int) $item['id'] ?>">
                                    <i data-lucide="chevron-right" class="w-4 h-4 transform transition-transform duration-200"></i>
                                </button>
                            </td>
                            <td class="py-4 px-4 text-slate-500 whitespace-nowrap"><?= htmlspecialchars((string) $item['fecha_creacion'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900"><?= htmlspecialchars((string) $item['razon_social'], ENT_QUOTES, 'UTF-8') ?></div>
                                <span class="text-xs text-slate-400">RUT: <?= htmlspecialchars((string) $item['rut'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border <?= $badgeClass ?>">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                    <?= htmlspecialchars($estadoTexto, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 font-medium"><?= htmlspecialchars((string) ($item['licencia_texto'] ?: $item['plan'] ?: $item['licencia']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center justify-center <?= (($item['alta_es_emisor'] ?? 'NO') === 'SI') ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' ?> text-xs font-bold px-2.5 py-0.5 rounded-full">
                                    <?= (($item['alta_es_emisor'] ?? 'NO') === 'SI') ? 'SI' : 'NO' ?>
                                </span>
                            </td>
                            <td class="py-4 px-4" onclick="event.stopPropagation();">
                                <button type="button" class="inline-flex items-center gap-1 border px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm <?= $accionClass ?>">
                                    <i data-lucide="<?= $estado === 'APROBADO' ? 'check-circle-2' : ($estado === 'ERROR_APROBACION' ? 'alert-circle' : 'shield') ?>" class="w-3.5 h-3.5"></i>
                                    <span><?= htmlspecialchars($accionTexto, ENT_QUOTES, 'UTF-8') ?></span>
                                </button>
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap" onclick="event.stopPropagation();">
                                <div class="flex justify-end gap-1">
                                    <a href="index.php?action=show&id=<?= (int) $item['id'] ?>" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Ver / editar">
                                        <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                                    </a>
                                    <?php if ($estado === ESTADO_PENDIENTE_APROBACION): ?>
                                        <a href="index.php?action=show&id=<?= (int) $item['id'] ?>#acciones" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Administrar">
                                            <i data-lucide="x-circle" class="w-4.5 h-4.5"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr id="detalle-<?= (int) $item['id'] ?>" class="hidden bg-slate-50/50">
                            <td colspan="8" class="p-6 border-t border-slate-100">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <div class="lg:col-span-2 space-y-4">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                            <i data-lucide="git-commit" class="w-4 h-4 text-indigo-500"></i>
                                            Hoja de Ruta de Onboarding
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-xl border border-slate-200">
                                            <div class="flex items-start gap-3 p-2 rounded-lg <?= $estado === 'PENDIENTE_APROBACION' ? 'bg-indigo-50 border border-indigo-100' : 'bg-slate-50' ?>">
                                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full <?= $estado === 'PENDIENTE_APROBACION' ? 'bg-indigo-100 text-indigo-600' : 'bg-emerald-100 text-emerald-600' ?>">
                                                    <i data-lucide="<?= $estado === 'PENDIENTE_APROBACION' ? 'shield' : 'check' ?>" class="w-3.5 h-3.5"></i>
                                                </span>
                                                <div>
                                                    <h5 class="text-xs font-bold text-slate-700">1. Aprobaci&oacute;n pendiente <span class="text-[9px] bg-slate-200 text-slate-600 px-1 py-0.5 rounded font-normal">Manual</span></h5>
                                                    <p class="text-[10px] text-slate-400"><?= $estado === 'PENDIENTE_APROBACION' ? 'Esperando revisi&oacute;n de admin' : 'Revisado por admin' ?></p>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3 p-2 rounded-lg <?= $estado === 'APROBADO' ? 'bg-emerald-50 border border-emerald-100' : 'opacity-60' ?>">
                                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full <?= $estado === 'APROBADO' ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' ?>">
                                                    <i data-lucide="<?= $estado === 'APROBADO' ? 'check' : 'circle' ?>" class="w-3.5 h-3.5"></i>
                                                </span>
                                                <div>
                                                    <h5 class="text-xs font-bold <?= $estado === 'APROBADO' ? 'text-slate-700' : 'text-slate-500' ?>">2. Creaci&oacute;n base <span class="text-[9px] bg-indigo-100 text-indigo-700 px-1 py-0.5 rounded font-normal">Auto</span></h5>
                                                    <p class="text-[10px] <?= $estado === 'APROBADO' ? 'text-slate-400' : 'text-slate-400' ?>">
                                                        <?= $estado === 'APROBADO' ? 'Creado en Empresas y Clientes' : 'Se ejecuta al aprobar el alta' ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                            <i data-lucide="folder-kanban" class="w-4 h-4 text-indigo-500"></i>
                                            Datos r&aacute;pidos
                                        </h4>
                                        <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-3 text-sm">
                                            <div><span class="text-slate-400">Email:</span> <span class="font-medium text-slate-700"><?= htmlspecialchars((string) $item['email_principal'], ENT_QUOTES, 'UTF-8') ?></span></div>
                                            <div><span class="text-slate-400">Plan:</span> <span class="font-medium text-slate-700"><?= htmlspecialchars((string) ($item['plan'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                                            <div><span class="text-slate-400">Sucursal:</span> <span class="font-medium text-slate-700"><?= htmlspecialchars((string) ($item['suc_cod_sucursal'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                                            <div><span class="text-slate-400">Firmante:</span> <span class="font-medium text-slate-700"><?= htmlspecialchars((string) ($item['nombre_completo_firmante'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                                            <div class="pt-2">
                                                <a href="index.php?action=show&id=<?= (int) $item['id'] ?>" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-medium px-4 py-2 rounded-lg transition text-sm">
                                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                                    <span>Abrir ficha completa</span>
                                                </a>
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
    <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
        <p>Mostrando <span class="text-slate-700 font-bold"><?= count($items) ?></span> registros temporales</p>
        <div class="flex gap-1.5">
            <button class="bg-white border border-slate-200 text-slate-400 px-3 py-1.5 rounded-lg transition disabled:opacity-50" disabled>Anterior</button>
            <button class="bg-white border border-slate-200 text-slate-400 px-3 py-1.5 rounded-lg transition disabled:opacity-50" disabled>Siguiente</button>
        </div>
    </div>
</section>

<div class="modal-shell" id="modal-create" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-create"></div>
    <div class="modal-panel modal-xl">
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-indigo-800 flex items-start gap-3 mb-6">
            <i data-lucide="info" class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5"></i>
            <div>
                <h4 class="font-bold text-sm">Informaci&oacute;n del Onboarding</h4>
                <p class="text-xs text-indigo-700 mt-0.5">La correcta recopilaci&oacute;n de estos campos deja el registro listo para aprobaci&oacute;n y posterior automatizaci&oacute;n.</p>
            </div>
        </div>
        <form method="post" action="index.php?action=store" enctype="multipart/form-data" class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-800 font-bold text-xs px-2.5 py-1 rounded-full">1</span>
                    <h3 class="font-bold text-slate-800 text-sm">Datos identificativos e imagen corporativa</h3>
                </div>
                <div class="p-6">
                    <div class="form-grid">
                        <?php require __DIR__ . '/_form_fields.php'; ?>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" data-close-modal="modal-create" class="bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold px-5 py-2.5 rounded-lg transition text-sm">Cancelar registro</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2.5 rounded-lg transition text-sm shadow-md flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Guardar e iniciar automatizaci&oacute;n</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
