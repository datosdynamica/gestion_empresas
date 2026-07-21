<?php

declare(strict_types=1);

$runtimeConfigFile = __DIR__ . '/runtime.php';
$runtimeConfig = [];
if (is_file($runtimeConfigFile)) {
    $loadedRuntimeConfig = require $runtimeConfigFile;
    if (is_array($loadedRuntimeConfig)) {
        $runtimeConfig = $loadedRuntimeConfig;
    }
}

define('APP_NAME', 'DYNAMICA ADMINISTRATIVO');
define('APP_BASE_URL', '/administrativo');
define('BASE_PATH', __DIR__ . '/..');
define('ID_EMPRESA_MASTER', 397);
define('ID_VENDEDOR_DEFAULT', 'admin');
define('TIPO_EMPRESA_DEFAULT', 'UNIPERSONAL');
define('REGIMEN_TRIBUTARIO_DEFAULT', 'GENERAL');
define('MONTO_CREDITO_FISCAL_ANUAL', (float) ($runtimeConfig['MONTO_CREDITO_FISCAL_ANUAL'] ?? 514.00));
define('MIGRATE_ENVIRONMENT', strtolower(trim((string) ($runtimeConfig['MIGRATE_ENVIRONMENT'] ?? 'testing'))));
define('MIGRATE_REGISTROEMPRESA_WSDL_TESTING', 'https://appuypruebas.migrate.info/InvoiCy/aws_registroempresa.aspx?WSDL');
define('MIGRATE_REGISTROEMPRESA_WSDL_PRODUCTION', 'https://appuy.migrate.info/InvoiCy/aws_registroempresa.aspx?WSDL');
define('MIGRATE_CONSULTAEMPRESAS_WSDL_TESTING', 'https://appuypruebas.migrate.info/InvoiCy/aws_consultaempresas.aspx?WSDL');
define('MIGRATE_CONSULTAEMPRESAS_WSDL_PRODUCTION', 'https://appuy.migrate.info/InvoiCy/aws_consultaempresas.aspx?WSDL');

define('UPLOAD_BASE_DIR', '/var/www/dynamica_archivos/Clientes_Doc');
define('UPLOAD_BASE_RELATIVE', '/var/www/dynamica_archivos/Clientes_Doc');
define('UPLOAD_TEMP_BASE_DIR', '/var/www/dynamica_archivos/Clientes_Doc_Tmp');
define('UPLOAD_TEMP_BASE_RELATIVE', '/var/www/dynamica_archivos/Clientes_Doc_Tmp');
define('MAX_UPLOAD_BYTES', 10485760);
define(
    'MIGRATE_REGISTROEMPRESA_WSDL',
    MIGRATE_ENVIRONMENT === 'production'
        ? MIGRATE_REGISTROEMPRESA_WSDL_PRODUCTION
        : MIGRATE_REGISTROEMPRESA_WSDL_TESTING
);
define(
    'MIGRATE_CONSULTAEMPRESAS_WSDL',
    MIGRATE_ENVIRONMENT === 'production'
        ? MIGRATE_CONSULTAEMPRESAS_WSDL_PRODUCTION
        : MIGRATE_CONSULTAEMPRESAS_WSDL_TESTING
);
define('MIGRATE_PARTNER_CODE', (int) ($runtimeConfig['MIGRATE_PARTNER_CODE'] ?? 28));
define('MIGRATE_PARTNER_KEY', (string) ($runtimeConfig['MIGRATE_PARTNER_KEY'] ?? 'RUdYwvzP62niXHmI7cfPoA=='));
define('MIGRATE_CERT_EMP_CODIGO', (int) ($runtimeConfig['MIGRATE_CERT_EMP_CODIGO'] ?? 20581));
define('MIGRATE_CERT_PUBLIC_KEY', (string) ($runtimeConfig['MIGRATE_CERT_PUBLIC_KEY'] ?? 'RUdYwvzP62niXHmI7cfPoA=='));
define('MIGRATE_LIC_AMBIENTE', 1);
define('MIGRATE_CERT_CACHE_HOURS', (int) ($runtimeConfig['MIGRATE_CERT_CACHE_HOURS'] ?? 24));
define('MIGRATE_CERT_BATCH_SIZE', (int) ($runtimeConfig['MIGRATE_CERT_BATCH_SIZE'] ?? 10));

define('ESTADO_PENDIENTE_APROBACION', 'PENDIENTE_APROBACION');
define('ESTADO_APROBADO', 'APROBADO');
define('ESTADO_ELIMINADO', 'ELIMINADO');
define('ESTADO_ERROR_APROBACION', 'ERROR_APROBACION');

define('TABLA_EMPRESAS_NUEVAS', 'EmpresasNuevas');
define('TABLA_EMPRESAS_NUEVAS_ARCHIVOS', 'EmpresasNuevasArchivos');
define('TABLA_EMPRESAS_NUEVAS_HISTORIAL', 'EmpresasNuevasHistorial');
define('TABLA_CERTIFICADOS_ACCIONES', 'CertificadosAcciones');
define('TABLA_CERTIFICADOS_NOTIFICACIONES', 'CertificadosNotificaciones');
define('TABLA_SEC_USERS', 'sec_users');
define('AUTH_SESSION_KEY', 'gestion_empresas_auth');
define('AUTH_REMEMBER_LOGIN_COOKIE', 'gestion_empresas_login');
define('CERT_NOTIFICATION_TEMPLATE_DIR', BASE_PATH . '/notificaciones_alertas_recordatorios');

const TIPOS_ARCHIVO_PERMITIDOS = [
    'pfx' => ['pfx', 'p12', 'zip'],
    'credito_fiscal' => ['pdf', 'doc', 'docx'],
    'contrato' => ['pdf'],
    'f6906' => ['pdf'],
    'logo' => ['jpg', 'jpeg', 'png', 'webp'],
];

const LICENCIAS_DISPONIBLES = [
    0 => 'Dynamica ERP',
    2 => 'Dynamica Lite',
    3 => 'Facturador',
    10 => 'TPV',
    12 => 'Cumplimiento',
    13 => 'Partner',
    14 => 'INVO',
];
