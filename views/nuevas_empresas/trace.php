<?php
declare(strict_types=1);

$enableCreateModal = false;
$activeNav = 'trazabilidad';
$pageSubtitle = 'Historial operativo de altas, cambios, aprobaciones y cancelaciones.';
require __DIR__ . '/../layout/header.php';

$events = $events ?? [];

$totalEventos = count($events);
$eventosCreacion = 0;
$eventosHitos = 0;
$eventosErrores = 0;
$eventosHoy = 0;
$hoy = date('Y-m-d');

foreach ($events as $event) {
    $nombreEvento = (string) ($event['evento'] ?? '');
    $fechaEvento = (string) ($event['fecha_evento'] ?? '');

    if ($nombreEvento === 'CREACION') {
        $eventosCreacion++;
    }
    if (strpos($nombreEvento, 'HITO_') === 0) {
        $eventosHitos++;
    }
    if (strpos($nombreEvento, 'ERROR') !== false) {
        $eventosErrores++;
    }
    if ($fechaEvento !== '' && strpos($fechaEvento, $hoy) === 0) {
        $eventosHoy++;
    }
}

function traceBadgeMeta(string $evento): array
{
    if (strpos($evento, 'ERROR') !== false) {
        return [
            'class' => 'bg-rose-50 text-rose-700 border border-rose-200',
            'icon' => 'alert-triangle',
        ];
    }

    if (strpos($evento, 'HITO_') === 0) {
        return [
            'class' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
            'icon' => 'git-commit',
        ];
    }

    if ($evento === 'APROBACION') {
        return [
            'class' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'icon' => 'badge-check',
        ];
    }

    if ($evento === 'CREACION') {
        return [
            'class' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'icon' => 'sparkles',
        ];
    }

    if ($evento === 'EDICION' || $evento === 'REEMPLAZO_ARCHIVO') {
        return [
            'class' => 'bg-sky-50 text-sky-700 border border-sky-200',
            'icon' => 'pen-square',
        ];
    }

    if ($evento === 'ELIMINACION') {
        return [
            'class' => 'bg-slate-100 text-slate-700 border border-slate-300',
            'icon' => 'archive-x',
        ];
    }

    return [
        'class' => 'bg-slate-100 text-slate-700 border border-slate-200',
        'icon' => 'circle',
    ];
}

function traceChangeLabel(array $event): string
{
    $from = trim((string) ($event['estado_anterior'] ?? ''));
    $to = trim((string) ($event['estado_nuevo'] ?? ''));

    if ($from === '' && $to === '') {
        return 'Sin cambio de estado';
    }

    $from = $from !== '' ? $from : 'Sin estado';
    $to = $to !== '' ? $to : 'Sin estado';

    return $from . ' → ' . $to;
}
?>

<section class="space-y-6">
    <div class="rounded-[28px] border border-[#f2d5ce] bg-gradient-to-r from-[#fff6f3] via-white to-[#fff9f7] shadow-sm">
        <div class="flex flex-col gap-5 px-6 py-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-4xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-[#ffd3cd] bg-[#fff1ee] px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-[#bf463c]">
                    <i data-lucide="history" class="h-4 w-4"></i>
                    Historial operativo
                </span>
                <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-900">Trazabilidad del módulo</h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">
                    Aquí queda la secuencia real del onboarding: creación, edición, aprobación, hitos, errores operativos y cancelaciones.
                </p>
            </div>
            <div class="flex gap-2">
                <a href="<?= htmlspecialchars(app_url('panel'), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    <span>Volver al panel</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-amber-700">Eventos totales</p>
            <div class="mt-3 flex items-end justify-between">
                <p class="text-3xl font-black text-slate-900"><?= $totalEventos ?></p>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                    <i data-lucide="activity" class="h-5 w-5"></i>
                </span>
            </div>
        </article>

        <article class="rounded-2xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-indigo-700">Hitos registrados</p>
            <div class="mt-3 flex items-end justify-between">
                <p class="text-3xl font-black text-slate-900"><?= $eventosHitos ?></p>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                    <i data-lucide="git-commit" class="h-5 w-5"></i>
                </span>
            </div>
        </article>

        <article class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-700">Altas creadas</p>
            <div class="mt-3 flex items-end justify-between">
                <p class="text-3xl font-black text-slate-900"><?= $eventosCreacion ?></p>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                    <i data-lucide="sparkles" class="h-5 w-5"></i>
                </span>
            </div>
        </article>

        <article class="rounded-2xl border border-rose-200 bg-gradient-to-br from-rose-50 to-white p-5 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-rose-700">Errores / hoy</p>
            <div class="mt-3 flex items-end justify-between">
                <p class="text-3xl font-black text-slate-900"><?= $eventosErrores ?> <span class="text-lg font-bold text-slate-400">/ <?= $eventosHoy ?></span></p>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-100 text-rose-700">
                    <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                </span>
            </div>
        </article>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4">
            <div>
                <h3 class="text-sm font-black uppercase tracking-[0.16em] text-slate-700">Linea de eventos</h3>
                <p class="mt-1 text-sm text-slate-500">Orden cronológico para seguimiento operativo y auditoría interna.</p>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[1120px] border-collapse text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-white text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                        <th class="px-5 py-4">Fecha</th>
                        <th class="px-4 py-4">Empresa</th>
                        <th class="px-4 py-4">Evento</th>
                        <th class="px-4 py-4">Cambio de estado</th>
                        <th class="px-4 py-4">Detalle</th>
                        <th class="px-4 py-4">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center text-sm text-slate-400">Todavía no hay eventos registrados en la trazabilidad.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <?php
                            $badge = traceBadgeMeta((string) ($event['evento'] ?? ''));
                            $change = traceChangeLabel($event);
                            $detalle = trim((string) ($event['descripcion'] ?? ''));
                            $empresa = (string) (($event['razon_social'] ?? '') !== '' ? $event['razon_social'] : 'Registro sin empresa visible');
                            ?>
                            <tr class="transition hover:bg-[#fffaf8]">
                                <td class="whitespace-nowrap px-5 py-4 align-top">
                                    <div class="font-semibold text-slate-800"><?= htmlspecialchars((string) ($event['fecha_evento'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($empresa, ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="mt-1 text-xs text-slate-400">
                                        RUT: <?= htmlspecialchars((string) ($event['rut'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                        <span class="mx-1 text-slate-300">•</span>
                                        ID <?= (int) ($event['nueva_empresa_id'] ?? 0) ?>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold <?= $badge['class'] ?>">
                                        <i data-lucide="<?= htmlspecialchars($badge['icon'], ENT_QUOTES, 'UTF-8') ?>" class="h-3.5 w-3.5"></i>
                                        <?= htmlspecialchars((string) ($event['evento'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">
                                        <?= htmlspecialchars($change, ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="max-w-xl leading-relaxed text-slate-600"><?= htmlspecialchars($detalle !== '' ? $detalle : '-', ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        <?= htmlspecialchars((string) ($event['usuario_evento'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
