<?php

declare(strict_types=1);

define('APP_NAME', 'DYNAMICA ADMINISTRATIVO');
define('BASE_PATH', __DIR__ . '/..');
define('ID_EMPRESA_MASTER', 397);
define('ID_VENDEDOR_DEFAULT', 'admin');

define('UPLOAD_BASE_DIR', '/var/www/plugin/gestion_empresas/uploads/nuevas_empresas');
define('UPLOAD_BASE_RELATIVE', 'uploads/nuevas_empresas');
define('MAX_UPLOAD_BYTES', 10485760);

define('ESTADO_PENDIENTE_APROBACION', 'PENDIENTE_APROBACION');
define('ESTADO_APROBADO', 'APROBADO');
define('ESTADO_ELIMINADO', 'ELIMINADO');
define('ESTADO_ERROR_APROBACION', 'ERROR_APROBACION');

define('TABLA_EMPRESAS_NUEVAS', 'EmpresasNuevas');
define('TABLA_EMPRESAS_NUEVAS_ARCHIVOS', 'EmpresasNuevasArchivos');
define('TABLA_EMPRESAS_NUEVAS_HISTORIAL', 'EmpresasNuevasHistorial');
define('TABLA_SEC_USERS', 'sec_users');
define('AUTH_SESSION_KEY', 'gestion_empresas_auth');
define('AUTH_REMEMBER_LOGIN_COOKIE', 'gestion_empresas_login');

const TIPOS_ARCHIVO_PERMITIDOS = [
    'pfx' => ['pfx', 'p12'],
    'credito_fiscal' => ['pdf', 'doc', 'docx'],
    'contrato' => ['pdf'],
    'f6906' => ['pdf'],
    'logo' => ['jpg', 'jpeg', 'png', 'webp'],
];
