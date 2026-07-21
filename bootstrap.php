<?php

declare(strict_types=1);

date_default_timezone_set('America/Bogota');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';

$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
}

require __DIR__ . '/helpers/Db.php';
require __DIR__ . '/helpers/Response.php';
require __DIR__ . '/helpers/Auth.php';
require __DIR__ . '/helpers/Url.php';
require __DIR__ . '/helpers/Validator.php';
require __DIR__ . '/helpers/FileStorage.php';
require __DIR__ . '/helpers/WorkflowHelper.php';
require __DIR__ . '/helpers/MigrateInvoicyService.php';
require __DIR__ . '/helpers/OnboardingInvoiceService.php';
require __DIR__ . '/helpers/CertificateDigitalInspector.php';
require __DIR__ . '/helpers/CertificateNotificationMailer.php';
require __DIR__ . '/helpers/OnboardingMailer.php';

require __DIR__ . '/models/BaseModel.php';
require __DIR__ . '/models/CatalogoReferenciaModel.php';
require __DIR__ . '/models/LocalModel.php';
require __DIR__ . '/models/SecUserModel.php';
require __DIR__ . '/models/NuevaEmpresaModel.php';
require __DIR__ . '/models/NuevaEmpresaArchivoModel.php';
require __DIR__ . '/models/NuevaEmpresaHistorialModel.php';
require __DIR__ . '/models/EmpresaNuevaHitoAutoModel.php';
require __DIR__ . '/models/EmpresaModel.php';
require __DIR__ . '/models/MigrateCertificateCacheModel.php';
require __DIR__ . '/models/CertificateActionModel.php';
require __DIR__ . '/models/CertificateNotificationModel.php';
require __DIR__ . '/models/EmpresaProvisioningModel.php';
require __DIR__ . '/models/ClienteModel.php';

require __DIR__ . '/controllers/AuthController.php';
require __DIR__ . '/controllers/NuevasEmpresasController.php';
