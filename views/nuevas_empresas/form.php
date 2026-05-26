<?php declare(strict_types=1); $enableCreateModal = true; require __DIR__ . '/../layout/header.php'; ?>
<?php $values = $_SESSION['old'] ?? []; unset($_SESSION['old']); ?>
<section class="card hero-card">
    <div class="hero-copy">
        <span class="pill">Fase 1</span>
        <h2>Nueva alta temporal</h2>
        <p>Este flujo registra la empresa, adjunta documentaci&oacute;n y la deja lista para revisi&oacute;n manual antes de crear registros en <strong>Empresas</strong> y <strong>Clientes</strong>.</p>
    </div>
    <div class="hero-actions">
        <button type="button" class="button button-primary" data-open-modal="modal-create">Abrir formulario</button>
        <a class="button button-secondary" href="index.php">Volver al listado</a>
    </div>
</section>

<div class="modal-shell is-open" id="modal-create" aria-hidden="false">
    <div class="modal-backdrop" data-close-modal="modal-create"></div>
    <div class="modal-panel modal-xl">
        <div class="modal-head">
            <div>
                <div class="eyebrow">Carga inicial</div>
                <h2>Alta temporal de empresa</h2>
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
