<?php

declare(strict_types=1);

@set_time_limit(0);
@ini_set('memory_limit', '512M');

require dirname(__DIR__) . '/bootstrap.php';

$limit = 20;
foreach ($argv as $arg) {
    if (strpos($arg, '--limit=') === 0) {
        $limit = max(1, (int) substr($arg, 8));
    }
}

$controller = new NuevasEmpresasController();
$result = $controller->processDeferredOnboardingQueue($limit);

fwrite(STDOUT, 'Entorno: ' . MIGRATE_ENVIRONMENT . PHP_EOL);
fwrite(STDOUT, 'Tareas leidas: ' . (int) ($result['read'] ?? 0) . PHP_EOL);
fwrite(STDOUT, 'Procesadas OK: ' . (int) ($result['success'] ?? 0) . PHP_EOL);
fwrite(STDOUT, 'Con error: ' . (int) ($result['errors'] ?? 0) . PHP_EOL);
fwrite(STDOUT, 'Omitidas: ' . (int) ($result['skipped'] ?? 0) . PHP_EOL);

foreach ((array) ($result['messages'] ?? []) as $message) {
    fwrite(STDOUT, $message . PHP_EOL);
}
