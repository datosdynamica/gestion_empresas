<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

$dryRun = in_array('--dry-run', $argv, true);
$pdo = Db::conn();
$actionModel = new CertificateActionModel();

$sql = "SELECT n.Id,
               n.EmpresaId,
               n.Rut,
               n.DiasObjetivo,
               n.FechaVencimiento,
               n.FechaEnvio,
               n.Destinatarios
        FROM " . TABLA_CERTIFICADOS_NOTIFICACIONES . " n
        LEFT JOIN " . TABLA_CERTIFICADOS_ACCIONES . " a
               ON a.EmpresaId = n.EmpresaId
              AND a.Accion = 'AVISO_CORREO'
              AND a.FechaAccion = n.FechaEnvio
        WHERE n.Estado = 'ENVIADO'
          AND a.Id IS NULL
        ORDER BY n.FechaEnvio ASC, n.Id ASC";

$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];

echo 'Pendientes de reconstruir: ' . count($rows) . PHP_EOL;

$created = 0;
foreach ($rows as $row) {
    $empresaId = (int) ($row['EmpresaId'] ?? 0);
    $rut = trim((string) ($row['Rut'] ?? ''));
    $dias = (int) ($row['DiasObjetivo'] ?? 0);
    $fechaEnvio = trim((string) ($row['FechaEnvio'] ?? ''));
    $destinatarios = trim((string) ($row['Destinatarios'] ?? ''));
    $daysLabel = $dias === 1 ? '1 dia' : ($dias . ' dias');
    $descripcion = 'Recordatorio automatico de certificado enviado por correo. Ventana: ' . $daysLabel . '. Destinatarios: ' . $destinatarios . '.';

    echo '[PENDIENTE] EmpresaId=' . $empresaId . ' RUT=' . $rut . ' FechaEnvio=' . $fechaEnvio . PHP_EOL;

    if ($dryRun) {
        continue;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO ' . TABLA_CERTIFICADOS_ACCIONES . ' (
            EmpresaId,
            Rut,
            Accion,
            Descripcion,
            UsuarioLogin,
            UsuarioNombre,
            FechaAccion
        ) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $empresaId,
        $rut,
        'AVISO_CORREO',
        $descripcion,
        'sistema',
        'Sistema',
        $fechaEnvio,
    ]);
    $created++;
}

echo 'Reconstruidos: ' . $created . PHP_EOL;
