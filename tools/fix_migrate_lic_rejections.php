<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script debe ejecutarse por CLI.\n");
    exit(1);
}

$db = Db::conn();
$historialModel = new NuevaEmpresaHistorialModel();
$usuarioEvento = $argv[1] ?? 'codex';

$rows = $db->query("
    SELECT
        Id,
        Estado,
        HitoActual,
        EmpresaCreada,
        ClienteCreada,
        RazonSocial,
        Rut,
        ErrorProceso,
        MigrateResponseXml
    FROM " . TABLA_EMPRESAS_NUEVAS . "
    WHERE Estado <> '" . ESTADO_ELIMINADO . "'
      AND MigrateResponseXml IS NOT NULL
      AND MigrateResponseXml <> ''
    ORDER BY Id ASC
")->fetchAll(PDO::FETCH_ASSOC);

$updated = [];
$skipped = [];

foreach ($rows as $row) {
    $responseXml = trim((string) ($row['MigrateResponseXml'] ?? ''));
    if ($responseXml === '') {
        continue;
    }

    $licMsg = extractLicMsgRetorno($responseXml);
    if ($licMsg === '' || !isNegativeMigrateMessage($licMsg)) {
        continue;
    }

    $id = (int) ($row['Id'] ?? 0);
    if ($id <= 0) {
        continue;
    }

    $targetError = 'Migrate creo la empresa, pero el licenciamiento devolvio novedad: ' . $licMsg;
    $currentEstado = (string) ($row['Estado'] ?? '');
    $currentHito = (string) ($row['HitoActual'] ?? '');
    $currentError = trim((string) ($row['ErrorProceso'] ?? ''));

    $alreadyCorrect =
        $currentEstado === ESTADO_ERROR_APROBACION
        && $currentHito === 'ERROR_APROBACION'
        && $currentError === $targetError;

    if ($alreadyCorrect) {
        $skipped[] = [
            'id' => $id,
            'rut' => (string) ($row['Rut'] ?? ''),
            'razon_social' => (string) ($row['RazonSocial'] ?? ''),
            'lic_msg' => $licMsg,
        ];
        continue;
    }

    $stmt = $db->prepare("
        UPDATE " . TABLA_EMPRESAS_NUEVAS . "
        SET Estado = ?,
            HitoActual = 'ERROR_APROBACION',
            EstadoDetalle = 'Error al ejecutar Migrate',
            ErrorProceso = ?
        WHERE Id = ?
    ");
    $stmt->execute([ESTADO_ERROR_APROBACION, $targetError, $id]);

    $historialModel->create([
        'nueva_empresa_id' => $id,
        'evento' => 'HITO_MIGRATE_ERROR',
        'estado_anterior' => $currentEstado,
        'estado_nuevo' => ESTADO_ERROR_APROBACION,
        'descripcion' => $targetError,
        'usuario_evento' => $usuarioEvento,
    ]);

    $updated[] = [
        'id' => $id,
        'rut' => (string) ($row['Rut'] ?? ''),
        'razon_social' => (string) ($row['RazonSocial'] ?? ''),
        'estado_anterior' => $currentEstado,
        'hito_anterior' => $currentHito,
        'lic_msg' => $licMsg,
    ];
}

echo json_encode([
    'updated_count' => count($updated),
    'skipped_count' => count($skipped),
    'updated' => $updated,
    'skipped' => $skipped,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

function extractLicMsgRetorno(string $responseXml): string
{
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($responseXml);
    libxml_clear_errors();

    if ($xml === false) {
        return '';
    }

    $licMsgRetornoNodes = $xml->xpath('//LicMsgRetorno') ?: [];
    if (!empty($licMsgRetornoNodes)) {
        return trim((string) $licMsgRetornoNodes[0]);
    }

    return '';
}

function isNegativeMigrateMessage(string $message): bool
{
    $normalized = mb_strtolower(trim($message));
    if ($normalized === '') {
        return false;
    }

    foreach (['rechaz', 'error', 'falla', 'fallo', 'inválid', 'invÃ¡lid', 'invalid', 'deneg', 'no autorizado'] as $needle) {
        if (mb_strpos($normalized, $needle) !== false) {
            return true;
        }
    }

    return false;
}
