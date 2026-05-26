<?php declare(strict_types=1); require __DIR__ . '/../layout/header.php'; ?>
<section class="card">
    <p><strong>ID:</strong> <?= (int) $item['id'] ?></p>
    <p><strong>Estado:</strong> <?= htmlspecialchars((string) $item['estado'], ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Razón social:</strong> <?= htmlspecialchars((string) $item['razon_social'], ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>RUT:</strong> <?= htmlspecialchars((string) $item['rut'], ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars((string) $item['email_principal'], ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Licencia:</strong> <?= htmlspecialchars((string) ($item['licencia'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Detalle:</strong> <?= htmlspecialchars((string) ($item['estado_detalle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
</section>

<section class="card">
    <h2>Archivos</h2>
    <ul>
        <?php foreach ($archivos as $archivo): ?>
            <li><?= htmlspecialchars((string) $archivo['tipo_archivo'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars((string) $archivo['nombre_original'], ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
        <?php if (empty($archivos)): ?>
            <li>Sin adjuntos.</li>
        <?php endif; ?>
    </ul>
</section>

<?php if (($item['estado'] ?? '') === ESTADO_PENDIENTE_APROBACION): ?>
    <section class="card actions-block">
        <form method="post" action="index.php?action=approve&id=<?= (int) $item['id'] ?>">
            <button type="submit">Aprobar</button>
        </form>
        <form method="post" action="index.php?action=delete&id=<?= (int) $item['id'] ?>">
            <input type="text" name="motivo_eliminacion" placeholder="Motivo de eliminación">
            <button type="submit">Eliminar</button>
        </form>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
