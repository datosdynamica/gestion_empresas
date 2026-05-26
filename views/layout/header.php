<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="public/assets/css/app.css">
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
<main class="p-6 max-w-7xl mx-auto space-y-6">
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight"><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="text-sm text-slate-500">Aprovisionamiento y seguimiento tecnico de nuevos clientes.</p>
        </div>
        <nav class="flex gap-2">
            <a href="index.php" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-4 py-2.5 rounded-lg shadow-sm transition duration-150 text-sm">Listado</a>
            <?php if (!empty($enableCreateModal)): ?>
                <button type="button" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition duration-150 text-sm" data-open-modal="modal-create">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Nueva Empresa Cliente</span>
                </button>
            <?php else: ?>
                <a href="index.php?action=create" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition duration-150 text-sm">Nueva Empresa Cliente</a>
            <?php endif; ?>
        </nav>
    </header>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm font-medium"><?= htmlspecialchars((string) $_SESSION['success'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="rounded-xl border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm font-medium"><?= htmlspecialchars((string) $_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
        <div class="rounded-xl border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm">
            <p class="font-semibold mb-2">Revise los siguientes puntos:</p>
            <ul class="list-disc pl-5 space-y-1">
                <?php foreach ($_SESSION['errors'] as $errorMessage): ?>
                    <li><?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>
