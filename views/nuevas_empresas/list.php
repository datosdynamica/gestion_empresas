<?php declare(strict_types=1); $enableCreateModal = true; require __DIR__ . '/../layout/header.php'; ?>
<?php $values = $_SESSION['old'] ?? []; unset($_SESSION['old']); ?>
<section class="card intro-card">
    <div>
        <div class="eyebrow">Panel operativo</div>
        <h2>Altas temporales</h2>
        <p>Revise registros pendientes, abra el detalle, edite informaci&oacute;n y apruebe o elimine con confirmaci&oacute;n visual.</p>
    </div>
    <button type="button" class="button button-primary" data-open-modal="modal-create">Nueva alta</button>
</section>

<section class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Estado</th>
                <th>Raz&oacute;n social</th>
                <th>RUT</th>
                <th>Fecha de creaci&oacute;n</th>
                <th>Acci&oacute;n</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="6">No hay registros temporales.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><span class="mono">#<?= (int) $item['id'] ?></span></td>
                        <td><span class="status-badge status-<?= strtolower((string) $item['estado']) ?>"><?= htmlspecialchars((string) $item['estado'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars((string) $item['razon_social'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $item['rut'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $item['fecha_creacion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><a class="button button-secondary button-sm" href="index.php?action=show&id=<?= (int) $item['id'] ?>">Ver</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="modal-shell" id="modal-create" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-create"></div>
    <div class="modal-panel modal-xl">
        <div class="modal-head">
            <div>
                <div class="eyebrow">Nueva carga</div>
                <h2>Registrar alta temporal</h2>
            </div>
            <button type="button" class="icon-button" data-close-modal="modal-create" aria-label="Cerrar">×</button>
        </div>
        <form method="post" action="index.php?action=store" enctype="multipart/form-data" class="smart-form">
            <div class="form-grid">
                <?php require __DIR__ . '/_form_fields.php'; ?>
            </div>
            <div class="modal-foot">
                <button type="button" class="button button-secondary" data-close-modal="modal-create">Cancelar</button>
                <button type="submit" class="button button-primary">Guardar y enviar a aprobaci&oacute;n</button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
