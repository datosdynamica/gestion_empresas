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
$rawHost = $_SERVER['HTTP_HOST'] ?? '';
$requestHost = is_string($rawHost) ? strtolower((string) preg_replace('/:\d+$/', '', $rawHost)) : '';
$isExternalOnboardingHost = strpos($requestHost, 'onboarding.') === 0;

if ($isExternalOnboardingHost && $route === '' && $action === 'index') {
    $action = 'external-form';
}

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
        case 'volumen-cfe':
            $action = 'cfe-volume';
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
        case 'alta-cliente':
            $action = 'external-form';
            break;
        case 'tyc-cliente':
            $action = 'external-terms-pdf';
            break;
    }
}

$publicActions = ['login', 'authenticate', 'logout', 'external-form', 'store-external', 'external-terms-pdf', 'external-success'];
$externalActions = ['external-form', 'store-external', 'external-terms-pdf', 'external-success'];

if ($isExternalOnboardingHost && !in_array($action, $externalActions, true)) {
    http_response_code(404);
    exit;
}

// Todo lo demas exige sesion valida.
if (!in_array($action, $publicActions, true)) {
    Auth::requireLogin();
}

// Las rutas de aprobacion y configuracion no deben depender solo del menu:
// usuarios sin ADM.SISTEMA tampoco pueden invocarlas directamente.
$administrativeActions = ['settings', 'save-settings', 'approve', 'change-hito', 'run-migrate', 'delete'];
$isApprovalsPanel = $action === 'index' && trim((string) ($_GET['estado'] ?? '')) === 'En Proceso';
if (in_array($action, $administrativeActions, true) || $isApprovalsPanel) {
    Auth::requireAdministrativeAdmin();
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
    case 'external-form':
        $controller->externalForm();
        break;
    case 'store-external':
        $controller->storeExternal();
        break;
    case 'external-terms-pdf':
        $controller->externalTermsPdf();
        break;
    case 'external-success':
        $controller->externalSuccess();
        break;
    case 'external-invitations':
        $controller->externalInvitations();
        break;
    case 'create-external-invitation':
        $controller->createExternalInvitation();
        break;
    case 'revoke-external-invitation': $controller->revokeExternalInvitation(); break;
    case 'trace':
        $controller->trace();
        break;
    case 'settings':
        $controller->settings();
        break;
    case 'cfe-volume':
        $controller->cfeVolume();
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
    case 'client-expediente-upload':
        $controller->clientExpedienteUpload($id);
        break;
    case 'client-expediente-download':
        $controller->clientExpedienteDownload($id);
        break;
    case 'client-expediente-update':
        $controller->clientExpedienteUpdateDescription($id);
        break;
    case 'client-expediente-delete':
        $controller->clientExpedienteDelete($id);
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
