<?php

declare(strict_types=1);

date_default_timezone_set('America/Bogota');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';

require __DIR__ . '/helpers/Db.php';
require __DIR__ . '/helpers/Response.php';
require __DIR__ . '/helpers/Validator.php';
require __DIR__ . '/helpers/FileStorage.php';
require __DIR__ . '/helpers/WorkflowHelper.php';

require __DIR__ . '/models/BaseModel.php';
require __DIR__ . '/models/NuevaEmpresaModel.php';
require __DIR__ . '/models/NuevaEmpresaArchivoModel.php';
require __DIR__ . '/models/NuevaEmpresaHistorialModel.php';
require __DIR__ . '/models/EmpresaModel.php';
require __DIR__ . '/models/ClienteModel.php';

require __DIR__ . '/controllers/NuevasEmpresasController.php';
