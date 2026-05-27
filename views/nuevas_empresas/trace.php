<?php
declare(strict_types=1);

$enableCreateModal = false;
$activeNav = 'trazabilidad';
$pageSubtitle = 'Historial operativo de altas, cambios, aprobaciones y cancelaciones.';
require __DIR__ . '/../layout/header.php';
?>

<section class="space-y-5">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#fff1ee] text-[#bf463c] border border-[#ffd3cd] text-xs font-semibold uppercase tracking-widest">
                <i data-lucide="history" class="w-4 h-4"></i>
                Historial operativo
            </span>
            <h2 class="mt-4 text-2xl font-black tracking-tight text-slate-900">Trazabilidad del m&oacute;dulo</h2>
            <p class="mt-2 text-sm text-slate-500 max-w-3xl">Aqu&iacute; queda la secuencia de creaci&oacute;n, edici&oacute;n, aprobaci&oacute;n, reemplazo de adjuntos y cancelaci&oacute;n de cada alta.</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= htmlspecialchars(app_url('panel'), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-4 py-2.5 rounded-lg shadow-sm transition text-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Volver al panel</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs font-semibold tracking-wider uppercase">
                        <th class="py-4 px-5">Fecha</th>
                        <th class="py-4 px-4">Empresa</th>
                        <th class="py-4 px-4">Evento</th>
                        <th class="py-4 px-4">Cambio de estado</th>
                        <th class="py-4 px-4">Detalle</th>
                        <th class="py-4 px-4">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="6" class="py-12 px-6 text-center text-sm text-slate-400">Todav&iacute;a no hay eventos registrados en la trazabilidad.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <?php
                            $from = (string) ($event['estado_anterior'] ?? '');
                            $to = (string) ($event['estado_nuevo'] ?? '');
                            $change = trim($from . ($from !== '' || $to !== '' ? ' → ' : '') . $to);
                            ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-5 whitespace-nowrap text-slate-500"><?= htmlspecialchars((string) ($event['fecha_evento'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="py-4 px-4">
                                    <div class="font-semibold text-slate-900"><?= htmlspecialchars((string) (($event['razon_social'] ?? '') !== '' ? $event['razon_social'] : 'Registro sin empresa visible'), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-xs text-slate-400">RUT: <?= htmlspecialchars((string) ($event['rut'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> / ID <?= (int) ($event['nueva_empresa_id'] ?? 0) ?></div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">
                                        <?= htmlspecialchars((string) ($event['evento'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-slate-600"><?= htmlspecialchars($change !== '' ? $change : 'Sin cambio de estado', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="py-4 px-4 text-slate-600 max-w-xl"><?= htmlspecialchars((string) ($event['descripcion'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="py-4 px-4 text-slate-600"><?= htmlspecialchars((string) ($event['usuario_evento'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
