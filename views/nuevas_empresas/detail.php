<?php declare(strict_types=1); $enableCreateModal = false; require __DIR__ . '/../layout/header.php'; ?>
<?php $values = $item; ?>
<section class="card detail-top">
    <div>
        <div class="eyebrow">Detalle del registro</div>
        <h2><?= htmlspecialchars((string) $item['razon_social'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p>RUT <?= htmlspecialchars((string) $item['rut'], ENT_QUOTES, 'UTF-8') ?> / Licencia <?= htmlspecialchars((string) ($item['licencia'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="detail-top-actions">
        <span class="status-badge status-<?= strtolower((string) $item['estado']) ?>"><?= htmlspecialchars((string) $item['estado'], ENT_QUOTES, 'UTF-8') ?></span>
        <button type="button" class="button button-secondary" data-open-modal="modal-edit">Editar</button>
    </div>
</section>

<section class="detail-grid">
    <article class="card">
        <h3>Resumen</h3>
        <dl class="info-grid">
            <div><dt>ID</dt><dd class="mono">#<?= (int) $item['id'] ?></dd></div>
            <div><dt>Email</dt><dd><?= htmlspecialchars((string) $item['email_principal'], ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt>Domicilio</dt><dd><?= htmlspecialchars((string) $item['domicilio'], ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt>Tel&eacute;fono</dt><dd><?= htmlspecialchars((string) ($item['telefono'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt>Plan</dt><dd><?= htmlspecialchars((string) ($item['plan'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt>Certificado</dt><dd><?= htmlspecialchars((string) ($item['alta_certificado_digital'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd></div>
        </dl>
    </article>

    <article class="card">
        <h3>Adjuntos</h3>
        <div class="file-list">
            <?php if (empty($archivos)): ?>
                <p>Sin adjuntos.</p>
            <?php else: ?>
                <?php foreach ($archivos as $archivo): ?>
                    <div class="file-item">
                        <div>
                            <strong><?= htmlspecialchars((string) $archivo['tipo_archivo'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <p><?= htmlspecialchars((string) $archivo['nombre_original'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <span class="mono"><?= htmlspecialchars((string) pathinfo((string) $archivo['ruta_archivo'], PATHINFO_EXTENSION), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </article>
</section>

<?php if (($item['estado'] ?? '') === ESTADO_PENDIENTE_APROBACION): ?>
    <section class="card actions-surface">
        <div>
            <div class="eyebrow">Acciones</div>
            <h3>Revisi&oacute;n administrativa</h3>
            <p>Puede editar, aprobar o eliminar el registro. Todas las acciones solicitan confirmaci&oacute;n.</p>
        </div>
        <div class="inline-actions">
            <form method="post" action="index.php?action=approve&id=<?= (int) $item['id'] ?>" data-confirm="Esto crear&aacute; registros reales en Empresas y Clientes.">
                <button type="submit" class="button button-primary">Aprobar</button>
            </form>
            <button type="button" class="button button-danger" data-open-modal="modal-delete">Eliminar</button>
        </div>
    </section>
<?php endif; ?>

<div class="modal-shell" id="modal-edit" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-edit"></div>
    <div class="modal-panel modal-xl">
        <div class="modal-head">
            <div>
                <div class="eyebrow">Edici&oacute;n</div>
                <h2>Actualizar alta temporal</h2>
            </div>
            <button type="button" class="icon-button" data-close-modal="modal-edit" aria-label="Cerrar">×</button>
        </div>
        <form method="post" action="index.php?action=update&id=<?= (int) $item['id'] ?>" class="smart-form">
            <div class="form-grid">
                <?php $prefix = 'edit_'; require __DIR__ . '/_form_fields.php'; ?>
            </div>
            <div class="modal-foot">
                <button type="button" class="button button-secondary" data-close-modal="modal-edit">Cancelar</button>
                <button type="submit" class="button button-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-shell" id="modal-delete" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-delete"></div>
    <div class="modal-panel modal-sm">
        <div class="modal-head">
            <div>
                <div class="eyebrow">Eliminar</div>
                <h2>Confirmar eliminaci&oacute;n</h2>
            </div>
            <button type="button" class="icon-button" data-close-modal="modal-delete" aria-label="Cerrar">×</button>
        </div>
        <form method="post" action="index.php?action=delete&id=<?= (int) $item['id'] ?>" class="smart-form" data-confirm="Se eliminar&aacute; el registro temporal y se borrar&aacute;n sus archivos del disco.">
            <div class="form-grid">
                <div class="field span-12">
                    <label for="motivo_eliminacion">Motivo de eliminaci&oacute;n</label>
                    <textarea id="motivo_eliminacion" name="motivo_eliminacion" rows="4" required></textarea>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="button button-secondary" data-close-modal="modal-delete">Cancelar</button>
                <button type="submit" class="button button-danger">Eliminar registro</button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
