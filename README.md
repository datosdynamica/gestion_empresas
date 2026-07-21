# Gestion de Empresas

> Realizado por Leonardo Navarro.
>
> Fecha: 2026-07-21

Este repositorio deja versionado el programa del modulo administrativo de altas y automatizaciones de empresas de Dynamica.

## Que hace el modulo

- registra nuevas empresas en una tabla temporal
- permite aprobacion administrativa
- crea empresa y cliente en Dynamica
- envia altas a Migrate/InvoiCy segun la licencia
- controla el flujo de onboarding por hitos
- consulta certificados digitales
- envia recordatorios y correos automaticos relacionados con onboarding y certificados

## Estructura principal

- `index.php`
  Punto de entrada del modulo.

- `bootstrap.php`
  Carga configuracion, helpers, modelos y controladores.

- `config/`
  Parametros generales y archivos ejemplo de configuracion.

- `controllers/`
  Orquestacion de flujos de pantalla y acciones.

- `models/`
  Acceso a tablas temporales, maestras y auxiliares.

- `helpers/`
  Integracion, utilidades, validaciones y servicios de apoyo.

- `views/`
  Vistas del panel, formularios, login, detalle y certificados.

- `public/`
  Assets del frontend.

- `sql/`
  Scripts de creacion o ajuste de tablas del modulo.

- `tools/`
  Scripts CLI operativos, por ejemplo cache de certificados y notificaciones.

## Archivos que hoy son especialmente importantes

- [controllers/NuevasEmpresasController.php](C:\DYNAMICA_PLUGINS\gestion_empresas\controllers\NuevasEmpresasController.php)
- [helpers/MigrateInvoicyService.php](C:\DYNAMICA_PLUGINS\gestion_empresas\helpers\MigrateInvoicyService.php)
- [models/EmpresaModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\EmpresaModel.php)
- [models/ClienteModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\ClienteModel.php)
- [models/MigrateCertificateCacheModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\MigrateCertificateCacheModel.php)
- [tools/refresh_certificate_cache.php](C:\DYNAMICA_PLUGINS\gestion_empresas\tools\refresh_certificate_cache.php)
- [tools/send_certificate_notifications.php](C:\DYNAMICA_PLUGINS\gestion_empresas\tools\send_certificate_notifications.php)

## Que no se dejo como dato real dentro del respaldo

Para no subir informacion sensible real, el repositorio deja fuera o parametriza:

- llaves privadas
- tokens
- archivos locales de conexion
- runtime con claves reales
- credenciales reales de base de datos

Por eso el archivo de referencia para configuracion es:

- [config/runtime.example.php](C:\DYNAMICA_PLUGINS\gestion_empresas\config\runtime.example.php)
- [config/database.example.php](C:\DYNAMICA_PLUGINS\gestion_empresas\config\database.example.php)
