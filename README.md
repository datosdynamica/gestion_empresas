# Gestion de Empresas

> Realizado por Leonardo Navarro.
>
> Ultima actualizacion documental: 2026-07-02 11:19:17

Este proyecto concentra el flujo de alta de nuevas empresas, su aprobacion operativa, la provision inicial en Dynamica y la integracion con Migrate/InvoiCy. Tambien incluye el monitoreo interno de certificados digitales y las alertas por vencimiento.

La idea de este `README` es que cualquier persona que entre al proyecto entienda rapido tres cosas:

1. que resuelve el modulo
2. donde estan las piezas importantes
3. cual documento debe abrir segun lo que vaya a hacer

## Que hace hoy el modulo

- registra nuevas empresas en una tabla temporal
- guarda adjuntos y los mueve a carpeta final cuando corresponde
- permite aprobacion administrativa y trazabilidad
- crea empresa y cliente en Dynamica
- envia altas a Migrate/InvoiCy segun el tipo de licencia
- consulta certificados digitales desde Migrate
- mantiene una cache local de vencimientos
- envia recordatorios automaticos por correo

## Documentos principales

- [docs/INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\docs\INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md)
  Documento maestro del proyecto. Resume arquitectura, flujo, scripts, cron, logs, ambientes y puntos sensibles.

- [RUNBOOK-OPERATIVO.md](C:\DYNAMICA_PLUGINS\gestion_empresas\RUNBOOK-OPERATIVO.md)
  Guia practica para desplegar, validar en servidor y no romper codificacion ni `strict_types`.

- [GUARDRAILS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\GUARDRAILS.md)
  Reglas operativas y zonas sensibles. Debe revisarse antes de tocar produccion.

- [docs/wiki_instructivo_onboarding_migrate.md](C:\DYNAMICA_PLUGINS\gestion_empresas\docs\wiki_instructivo_onboarding_migrate.md)
  Base resumida para publicar o actualizar contenido en la wiki.

- [bitacora](C:\DYNAMICA_PLUGINS\gestion_empresas\bitacora)
  Evidencia de cambios, decisiones y pruebas hechas sobre el modulo.

## Estructura base del proyecto

- `index.php`
  Punto de entrada del modulo.

- `bootstrap.php`
  Carga configuracion, helpers, modelos y controladores.

- `config/`
  Parametros generales y runtime editable.

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

- `docs/`
  Documentacion consolidada.

- `bitacora/`
  Historial tecnico del proyecto.

## Archivos que hoy son especialmente importantes

- [controllers/NuevasEmpresasController.php](C:\DYNAMICA_PLUGINS\gestion_empresas\controllers\NuevasEmpresasController.php)
- [helpers/MigrateInvoicyService.php](C:\DYNAMICA_PLUGINS\gestion_empresas\helpers\MigrateInvoicyService.php)
- [models/EmpresaModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\EmpresaModel.php)
- [models/ClienteModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\ClienteModel.php)
- [models/MigrateCertificateCacheModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\MigrateCertificateCacheModel.php)
- [tools/refresh_certificate_cache.php](C:\DYNAMICA_PLUGINS\gestion_empresas\tools\refresh_certificate_cache.php)
- [tools/send_certificate_notifications.php](C:\DYNAMICA_PLUGINS\gestion_empresas\tools\send_certificate_notifications.php)

## Ambientes

- Produccion:
  - servidor `www.datosdynamica.net`
  - ruta del proyecto `/var/www/plugin/gestion_empresas`

- Desarrollo:
  - servidor `www.desarrollodynamica.net`
  - ruta del proyecto `/var/www/html/plugin/gestion_empresas`

Los detalles tecnicos de acceso y despliegue quedaron en [RUNBOOK-OPERATIVO.md](C:\DYNAMICA_PLUGINS\gestion_empresas\RUNBOOK-OPERATIVO.md).

## Recomendacion practica

Si el trabajo que va a hacer toca produccion, empiece por este orden:

1. [GUARDRAILS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\GUARDRAILS.md)
2. [RUNBOOK-OPERATIVO.md](C:\DYNAMICA_PLUGINS\gestion_empresas\RUNBOOK-OPERATIVO.md)
3. [docs/INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\docs\INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md)
4. la bitacora del tema puntual

Con eso ya no toca reconstruir el contexto desde cero.
