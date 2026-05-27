<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$route = trim((string) ($_GET['route'] ?? ''), '/');
$authController = new AuthController();
$controller = new NuevasEmpresasController();
$action = (string) ($_GET['action'] ?? 'index');
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($route !== '') {
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
        case 'show':
            $action = 'show';
            break;
    }
}

$publicActions = ['login', 'authenticate', 'logout'];

if (!in_array($action, $publicActions, true)) {
    Auth::requireLogin();
}

if (Auth::check() && in_array($action, ['login', 'authenticate'], true)) {
    Response::redirect('panel');
}

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
    case 'store':
        $controller->store();
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
    case 'delete':
        $controller->delete($id);
        break;
    default:
        $controller->index();
        break;
}
