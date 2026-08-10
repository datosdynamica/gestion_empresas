<?php

declare(strict_types=1);

@set_time_limit(0);
@ini_set('memory_limit', '512M');

require dirname(__DIR__) . '/bootstrap.php';

$limit = 50;
foreach ($argv as $arg) {
    if (strpos($arg, '--limit=') === 0) {
        $limit = max(1, (int) substr($arg, 8));
    }
}

$model = new EmpresaNuevaHitoAutoModel();
$alerts = $model->listAutomationAlerts($limit);

FileStorage::appendAutomationRuntimeLog('ONBOARDING_AUTO_AUDIT_RUN', [
    'limit' => $limit,
    'alerts' => count($alerts),
    'environment' => (string) MIGRATE_ENVIRONMENT,
]);

fwrite(STDOUT, 'Entorno: ' . MIGRATE_ENVIRONMENT . PHP_EOL);
fwrite(STDOUT, 'Alertas detectadas: ' . count($alerts) . PHP_EOL);

foreach ($alerts as $alert) {
    fwrite(
        STDOUT,
        sprintf(
            '[%s] caso=%d tarea=%d estado=%s programado=%s detalle=%s',
            (string) ($alert['type'] ?? ''),
            (int) ($alert['nueva_empresa_id'] ?? 0),
            (int) ($alert['task_id'] ?? 0),
            (string) ($alert['estado'] ?? ''),
            (string) ($alert['programado_para'] ?? ''),
            (string) ($alert['ultimo_error'] ?? '')
        ) . PHP_EOL
    );
}
