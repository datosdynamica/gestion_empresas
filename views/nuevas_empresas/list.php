<?php declare(strict_types=1); require __DIR__ . '/../layout/header.php'; ?>
<section class="card">
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Estado</th>
            <th>Razón social</th>
            <th>RUT</th>
            <th>Fecha creación</th>
            <th>Acción</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($items)): ?>
            <tr><td colspan="6">No hay registros temporales.</td></tr>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= (int) $item['id'] ?></td>
                    <td><?= htmlspecialchars((string) $item['estado'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $item['razon_social'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $item['rut'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $item['fecha_creacion'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><a href="index.php?action=show&id=<?= (int) $item['id'] ?>">Ver</a></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
