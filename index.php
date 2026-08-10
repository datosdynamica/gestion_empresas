<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

// Este archivo solo resuelve la accion pedida y la deriva al controlador
// correcto. La logica de negocio queda concentrada en las clases.
$route = trim((string) ($_GET['route'] ?? ''), '/');
$authController = new AuthController();
$controller = new NuevasEmpresasController();
$action = (string) ($_GET['action'] ?? 'index');
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($route !== '') {
    // Alias cortos para rutas amigables del panel.
    switch ($route) {
        case 'login':
            $action = 'login';
            break;
        case 'logout':
            $action = 'logout';
            break;
        case 'panel':
            $action = 'index';
            break;
        case 'trazabilidad':
            $action = 'trace';
            break;
        case 'configuracion':
            $action = 'settings';
            break;
        case 'clientes':
            $action = 'clients';
            break;
        case 'certificados-migrate':
            $action = 'certificates';
            break;
        case 'show':
            $action = 'show';
            break;
    }
}

$publicActions = ['login', 'authenticate', 'logout'];

// Todo lo demas exige sesion valida.
if (!in_array($action, $publicActions, true)) {
    Auth::requireLogin();
}

if (Auth::check() && in_array($action, ['login', 'authenticate'], true)) {
    Response::redirect('panel');
}

// Ruteo final de acciones del modulo.
switch ($action) {
    case 'login':
        $authController->login();
        break;
    case 'authenticate':
        $authController->authenticate();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'create':
        $controller->create();
        break;
    case 'trace':
        $controller->trace();
        break;
    case 'settings':
        $controller->settings();
        break;
    case 'clients':
        $controller->clients();
        break;
    case 'client-show':
        $controller->clientShow($id);
        break;
    case 'client-certificates':
        $controller->clientCertificates($id);
        break;
    case 'client-update':
        $controller->clientUpdate($id);
        break;
    case 'certificates':
        $controller->certificates();
        break;
    case 'export-certificates-excel':
        $controller->exportCertificatesExcel();
        break;
    case 'export-certificates-pdf':
        $controller->exportCertificatesPdf();
        break;
    case 'certificate-log-action':
        $controller->certificateLogAction();
        break;
    case 'certificate-upload-digital':
        $controller->certificateUploadDigital();
        break;
    case 'certificate-update-operational':
        $controller->certificateUpdateOperational();
        break;
    case 'store':
        $controller->store();
        break;
    case 'save-settings':
        $controller->saveSettings();
        break;
    case 'update':
        $controller->update($id);
        break;
    case 'show':
        $controller->show($id);
        break;
    case 'download-file':
        $controller->downloadFile($id);
        break;
    case 'replace-file':
        $controller->replaceFile($id);
        break;
    case 'approve':
        $controller->approve($id);
        break;
    case 'validate-rut':
        $controller->validateRut();
        break;
    case 'change-hito':
        $controller->changeHito($id);
        break;
    case 'run-migrate':
        $controller->runMigrate($id);
        break;
    case 'delete':
        $controller->delete($id);
        break;
    default:
        $controller->index();
        break;
}
