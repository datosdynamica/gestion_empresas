<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/app.css">
</head>
<body>
<main class="container">
    <header class="page-header">
        <div>
            <div class="eyebrow">Gestion de empresas</div>
            <h1><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <nav class="actions">
            <a class="ghost-link" href="index.php">Listado</a>
            <?php if (!empty($enableCreateModal)): ?>
                <button type="button" class="button button-primary button-sm" data-open-modal="modal-create">Nueva alta</button>
            <?php else: ?>
                <a class="button button-primary button-sm" href="index.php?action=create">Nueva alta</a>
            <?php endif; ?>
        </nav>
    </header>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="flash flash-success"><?= htmlspecialchars((string) $_SESSION['success'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash flash-error"><?= htmlspecialchars((string) $_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
