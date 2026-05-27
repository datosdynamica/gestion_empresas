<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="theme-color" content="#e65b4f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Gestion Empresas">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <?php $cssVersion = @filemtime(__DIR__ . '/../../public/assets/css/app.css') ?: time(); ?>
    <?php $logoVersion = @filemtime(__DIR__ . '/../../public/assets/img/logo-dynamica.jpeg') ?: time(); ?>
    <?php $manifestVersion = @filemtime(__DIR__ . '/../../manifest.webmanifest') ?: time(); ?>
    <link rel="manifest" href="manifest.webmanifest?v=<?= $manifestVersion ?>">
    <link rel="apple-touch-icon" href="public/assets/pwa/icon-192.png?v=<?= $manifestVersion ?>">
    <link rel="stylesheet" href="public/assets/css/app.css?v=<?= $cssVersion ?>">
</head>
<?php
$activeNav = $activeNav ?? 'panel';
$pageSubtitle = $pageSubtitle ?? 'Aprovisionamiento y seguimiento operativo de nuevas empresas.';
$authUser = Auth::check() ? Auth::user() : null;
$userDisplayName = 'Usuario';
if (is_array($authUser)) {
    $userDisplayName = (string) (($authUser['name'] ?? '') !== '' ? $authUser['name'] : ($authUser['login'] ?? 'Usuario'));
}
?>
<body class="app-body bg-[#fff8f6] text-slate-800 min-h-screen">
<div class="app-shell" data-app-shell>
    <aside class="app-sidebar" id="app-sidebar">
        <div class="app-sidebar__inner">
            <div class="app-brand">
                <div class="app-brand__mark">
                    <img src="public/assets/img/logo-dynamica.jpeg?v=<?= $logoVersion ?>" alt="Logo Dynamica" class="h-11 w-11 object-contain rounded-2xl bg-white p-1.5 shadow-sm">
                </div>
                <div class="app-brand__copy">
                    <p class="app-brand__eyebrow">Dynamica</p>
                    <h1 class="app-brand__title">Altas y Automatizaciones</h1>
                    <p class="app-brand__subtitle">Panel de gesti&oacute;n operativa</p>
                </div>
            </div>

            <div class="app-sidebar__section">
                <p class="app-sidebar__section-title">Navegaci&oacute;n</p>
                <nav class="app-nav">
                    <a href="panel" class="app-nav__item<?= $activeNav === 'panel' ? ' is-active' : '' ?>">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span class="app-nav__label">Lista de empresas</span>
                    </a>
                    <?php if (!empty($enableCreateModal)): ?>
                        <button type="button" class="app-nav__item" data-open-modal="modal-create">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                            <span class="app-nav__label">Nuevo registro cliente</span>
                        </button>
                    <?php else: ?>
                        <a href="index.php?action=create" class="app-nav__item<?= $activeNav === 'nuevo' ? ' is-active' : '' ?>">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                            <span class="app-nav__label">Nuevo registro cliente</span>
                        </a>
                    <?php endif; ?>
                    <button type="button" class="app-nav__item" data-nav-filter-state="En Proceso">
                        <i data-lucide="badge-check" class="w-5 h-5"></i>
                        <span class="app-nav__label">Aprobaciones</span>
                    </button>
                    <button type="button" class="app-nav__item" data-nav-scroll-target="tabla-clientes">
                        <i data-lucide="folders" class="w-5 h-5"></i>
                        <span class="app-nav__label">Trazabilidad</span>
                    </button>
                    <button type="button" class="app-nav__item" id="install-app-button" hidden>
                        <i data-lucide="download" class="w-5 h-5"></i>
                        <span class="app-nav__label">Instalar app</span>
                    </button>
                </nav>
            </div>

            <?php if (Auth::check()): ?>
                <div class="app-sidebar__footer">
                    <div class="app-user-card">
                        <div class="app-user-card__icon">
                            <i data-lucide="user-round" class="w-5 h-5"></i>
                        </div>
                        <div class="app-user-card__copy">
                            <p class="app-user-card__name"><?= htmlspecialchars($userDisplayName, ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="app-user-card__meta">Acceso interno autorizado</p>
                        </div>
                    </div>
                    <div class="app-sidebar__actions">
                        <button type="button" class="app-sidebar__mini-btn" data-app-nav-collapse aria-label="Contraer menu">
                            <i data-lucide="panel-left-close" class="w-4 h-4"></i>
                            <span class="app-nav__label">Contraer</span>
                        </button>
                        <a href="logout" class="app-sidebar__mini-btn">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span class="app-nav__label">Salir</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <div class="app-topbar__left">
                <button type="button" class="app-topbar__hamburger" data-app-nav-toggle aria-label="Abrir menu">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div>
                    <p class="app-topbar__eyebrow">Panel interno</p>
                    <h2 class="app-topbar__title"><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="app-topbar__subtitle"><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>

            <div class="app-topbar__right">
                <?php if (!empty($enableCreateModal)): ?>
                    <button type="button" class="app-topbar__primary" data-open-modal="modal-create">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Nueva Empresa Cliente</span>
                    </button>
                <?php endif; ?>

                <?php if (Auth::check()): ?>
                    <div class="app-user-menu" data-user-menu>
                        <button type="button" class="app-user-menu__trigger" data-user-menu-toggle>
                            <span class="app-user-menu__avatar">
                                <i data-lucide="user-round" class="w-4 h-4"></i>
                            </span>
                            <span class="app-user-menu__copy">
                                <strong><?= htmlspecialchars($userDisplayName, ENT_QUOTES, 'UTF-8') ?></strong>
                                <small>Sesion activa</small>
                            </span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                        </button>
                        <div class="app-user-menu__dropdown" hidden>
                            <div class="app-user-menu__header">
                                <p class="font-semibold text-slate-900"><?= htmlspecialchars($userDisplayName, ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="text-xs text-slate-500">Acceso interno del modulo</p>
                            </div>
                            <div class="app-user-menu__links">
                                <button type="button" class="app-user-menu__item" id="install-app-button-menu" hidden>
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    <span>Instalar aplicacion</span>
                                </button>
                                <a href="logout" class="app-user-menu__item">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    <span>Cerrar sesion</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <main class="app-content">
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm font-medium mb-5"><?= htmlspecialchars((string) $_SESSION['success'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="rounded-2xl border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm font-medium mb-5"><?= htmlspecialchars((string) $_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
                <div class="rounded-2xl border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm mb-5">
                    <p class="font-semibold mb-2">Revise los siguientes puntos:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <?php foreach ($_SESSION['errors'] as $errorMessage): ?>
                            <li><?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>
