<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$controller = new NuevasEmpresasController();
$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

switch ($action) {
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
